<?php
 session_start();
 // Include config file
 require_once "../conexion.php";

 use PHPMailer\PHPMailer\PHPMailer;
 use PHPMailer\PHPMailer\Exception;

 require '../../plugins/PHPMailer/src/Exception.php';
 require '../../plugins/PHPMailer/src/PHPMailer.php';
 require '../../plugins/PHPMailer/src/SMTP.php';
 require '../../plugins/vendor/autoload.php';

 // --- 1. Recolección y Sanitización de Datos ---
 // Los datos se recogen de $_POST.
 $tipo_cedula = $_POST['tipo_cedula'] ?? '';
 $cedula = $_POST['cedula'] ?? '';
 $nombre = $_POST['nombre'] ?? '';
 $apellido = $_POST['apellido'] ?? '';
 $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
 $genero = $_POST['genero'] ?? '';
 $email = $_POST['correo'] ?? null; // Puede ser NULL
 $codigo_colegiatura = $_POST['cod_colegiatura'] ?? '';
 $fecha_ingreso = $_POST['fecha_ingreso'] ?? '';
 $prefijo = $_POST['prefijo'] ?? '';
 $telefono = $_POST['telefono'] ?? '';
 $areas_seleccionadas = $_POST['areas_seleccionadas'] ?? '';
 $especialidades_seleccionadas = $_POST['especialidades_seleccionadas'] ?? '';

 $crear_usuario = $_POST['crear_usuario'] ?? '0';
 $password_plana = $_POST['password'] ?? '';
 $confirm_password = $_POST['confirm_password'] ?? '';

 // === 3. VALIDACIONES BACKEND ===
 if ($crear_usuario == '1') {
     // Verificar que el correo exista si se va a crear usuario
     if (empty($email)) {
         $_SESSION['mensaje_user_error'] = '⚠️ Error: El correo electrónico es obligatorio para crear el acceso al sistema.';
         header("location: ../../pages/php/rh_medico_agregar.php");
         exit;
     }
     // Verificar coincidencia de claves en el servidor
     if ($password_plana !== $confirm_password || empty($password_plana)) {
         $_SESSION['mensaje_user_error'] = '⚠️ Error de Contraseña: Las contraseñas no coinciden o están vacías.';
         header("location: ../../pages/php/rh_medico_agregar.php");
         exit;
     }
 }

 $password_hash = ""; 
 if ($crear_usuario == '1') {
     $password_hash = password_hash($password_plana, PASSWORD_DEFAULT);
 }

 $estado = 2;
 $rol = 7;
 
 // --- 2. Inicio de la Transacción ---
 $conexion->begin_transaction();

 try {
    $id_medico = 0;
    
    // 2.1. INSERT en PERSONA (Médico)
    // Usa sentencias preparadas para mayor seguridad (Inyección SQL)
    $sql_medico = "INSERT INTO persona(nombre, apellido, tipo_cedula, cedula, fecha_nacimiento, genero, email, password, estatus) 
                   VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_medico = $conexion->prepare($sql_medico);
    
    if (!$stmt_medico) {
        throw new Exception("Error al preparar INSERT en persona: " . $conexion->error);
    }
    
    // Se añade una 's' extra para el string del password y la variable $password_hash
    $stmt_medico->bind_param("ssssssssi", $nombre, $apellido, $tipo_cedula, $cedula, $fecha_nacimiento, $genero, $email, $password_hash, $estado);
    
    if (!$stmt_medico->execute()) {
        throw new Exception("Error al insertar persona (Médico): " . $stmt_medico->error);
    }
    $id_medico = $conexion->insert_id;
    $stmt_medico->close();


    // 2.2. INSERT en DETALLE_MEDICO
    $sql_detalle_medico = "INSERT INTO detalle_medico(cod_colegiatura, fecha_ingreso, Id_persona)
                           VALUES(?, ?, ?)";
    $stmt_detalle_medico = $conexion->prepare($sql_detalle_medico);
    
    if (!$stmt_detalle_medico) {
        throw new Exception("Error al preparar INSERT en detalle_medico: " . $conexion->error);
    }
    
    $stmt_detalle_medico->bind_param("isi", $codigo_colegiatura, $fecha_ingreso, $id_medico);
    
    if (!$stmt_detalle_medico->execute()) {
        throw new Exception("Error al insertar detalle médico: " . $stmt_detalle_medico->error);
    }
    
    // **OBTENER LA ID DEL DETALLE_MEDICO** (Punto solicitado)
    $id_detalle_medico = $conexion->insert_id;
    $stmt_detalle_medico->close();

    // 2.3. INSERT en MEDICOS_DEPARTAMENTOS (Múltiples registros)
    if (!empty($areas_seleccionadas)) {
        // Convertimos el string "1|3|5" en un arreglo [1, 3, 5]
        $areas_array = explode('|', $areas_seleccionadas);
        
        $sql_medicos_departamentos = "INSERT INTO medicos_departamentos(Id_departamento, Id_detalle_medico) VALUES(?, ?)";
        $stmt_medicos_departamentos = $conexion->prepare($sql_medicos_departamentos);

        if (!$stmt_medicos_departamentos) {
            throw new Exception("Error al preparar INSERT en medicos_departamentos: " . $conexion->error);
        }

        // Recorremos el arreglo e insertamos fila por fila
        foreach ($areas_array as $id_area) {
            $id_area = (int) $id_area; // Casteo a entero por seguridad
            if ($id_area > 0) {
                $stmt_medicos_departamentos->bind_param("ii", $id_area, $id_detalle_medico);
                if (!$stmt_medicos_departamentos->execute()) {
                    throw new Exception("Error al insertar médicos/departamentos: " . $stmt_medicos_departamentos->error);
                }
            }
        }
        $stmt_medicos_departamentos->close();
    }

    // 2.4. INSERT en ESPECIALIDADES_MEDICOS (Múltiples registros)
    if (!empty($especialidades_seleccionadas)) {
        // Convertimos el string "2|4" en un arreglo [2, 4]
        $especialidades_array = explode('|', $especialidades_seleccionadas);
        
        $sql_especialidades_medicos = "INSERT INTO especialidades_medicos(Id_especialidad, Id_detalle_medico) VALUES(?, ?)";
        $stmt_especialidades_medicos = $conexion->prepare($sql_especialidades_medicos);

        if (!$stmt_especialidades_medicos) {
            throw new Exception("Error al preparar INSERT en especialidades_medicos: " . $conexion->error);
        }

        // Recorremos el arreglo e insertamos fila por fila
        foreach ($especialidades_array as $id_esp) {
            $id_esp = (int) $id_esp; // Casteo a entero por seguridad
            if ($id_esp > 0) {
                $stmt_especialidades_medicos->bind_param("ii", $id_esp, $id_detalle_medico);
                if (!$stmt_especialidades_medicos->execute()) {
                    throw new Exception("Error al insertar especialidad: " . $stmt_especialidades_medicos->error);
                }
            }
        }
        $stmt_especialidades_medicos->close();
    }


    // 2.4.2 INSERT en TELEFONOS_PERSONAS
    $sql_telefono = "INSERT INTO telefonos_personas(Id_prefijo, telefono, Id_persona, estatus)
                     VALUES(?, ?, ?, ?)";
    $stmt_telefono = $conexion->prepare($sql_telefono);

    if (!$stmt_telefono) {
        throw new Exception("Error al preparar INSERT en telefonos_personas: " . $conexion->error);
    }

    $stmt_telefono->bind_param("sisi", $prefijo, $telefono, $id_medico, $estado);
    
    if (!$stmt_telefono->execute()) {
        throw new Exception("Error al insertar teléfono: " . $stmt_telefono->error);
    }
    $stmt_telefono->close();


    // 2.5. INSERT en DETALLE_PERSONA_ROL
    $sql_rol = "INSERT INTO detalle_persona_rol(Id_persona, Id_rol, estatus)
                VALUES(?, ?, ?)";
    $stmt_rol = $conexion->prepare($sql_rol);

    if (!$stmt_rol) {
        throw new Exception("Error al preparar INSERT en detalle_persona_rol: " . $conexion->error);
    }

    $stmt_rol->bind_param("iii", $id_medico, $rol, $estado);

    if (!$stmt_rol->execute()) {
        throw new Exception("Error al insertar rol: " . $stmt_rol->error);
    }
    $stmt_rol->close();


    // 3. Commit y Redirección Final
    $conexion->commit();

    $mensaje_correo = "";
    if ($crear_usuario == '1' && !empty($email)) {
        $mail = new PHPMailer(true);
        try {
            // Configuración del servidor SMTP (Igual que en agregar_usuarios.php)
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; 
            $mail->SMTPAuth = true;
            $mail->Username = 'cpt3sistema@gmail.com'; 
            $mail->Password = 'rqgltslfvazhjqix'; // Tu contraseña de aplicación
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Destinatarios
            $mail->setFrom('cpt3sistema@gmail.com', 'Sistema CPT3');
            $mail->addAddress($email, $nombre . ' ' . $apellido);

            // Contenido del correo
            $mail->isHTML(true);
            $mail->Subject = 'Credenciales de Acceso - Sistema CPT3';
            $mail->Body    = "<h3>Bienvenido/a al Sistema CPT3, Dr./Dra. $apellido</h3>
                              <p>Se ha registrado exitosamente su perfil médico y sus accesos al sistema.</p>
                              <p><b>Tus datos de acceso:</b></p>
                              <ul>
                                <li><b>Usuario (Correo):</b> $email</li>
                                <li><b>Contraseña:</b> $password_plana</li>
                              </ul>
                              <p>Por seguridad, se recomienda cambiar esta contraseña una vez ingrese al sistema.</p>";

            $mail->send();
            $mensaje_correo = " y credenciales enviadas.";
        } catch (Exception $e) {
            $mensaje_correo = ", pero no se pudo enviar el correo.";
        }
    }
    
    // Mensaje de Éxito
    $cedula_info = $tipo_cedula . '-' . $cedula; 
    $_SESSION['mensaje_user_exito'] = '✅ Éxito: El Médico ' . $nombre . ' ' . $apellido . ' (' . $cedula_info . ') fue agregado correctamente.';

    header('location: ../../pages/php/rh_medico_listado.php');
    exit();

 } catch (Exception $e) {
    // 4. Rollback y Mensaje de Error
    $conexion->rollback();

    error_log("Error de transacción al agregar el médico: " . $e->getMessage()); 
    // Mensaje de Error
    $_SESSION['mensaje_user_error'] = '❌ Error de Registro: No se pudo registrar el médico. Detalle: ' . $e->getMessage();

    header('location: ../../pages/php/rh_medico_listado.php');
    exit();
 }

 $conexion->close();
?>