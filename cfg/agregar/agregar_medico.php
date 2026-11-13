<?php
 session_start();
 require_once "../conexion.php";

 use PHPMailer\PHPMailer\PHPMailer;
 use PHPMailer\PHPMailer\Exception;

 require '../../plugins/PHPMailer/src/Exception.php';
 require '../../plugins/PHPMailer/src/PHPMailer.php';
 require '../../plugins/PHPMailer/src/SMTP.php';
 require '../../plugins/vendor/autoload.php';

 function validarEmailReal($email) {
     if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
     $dominio = substr(strrchr($email, "@"), 1);
     return checkdnsrr($dominio, "MX");
 }

 $tipo_cedula = $_POST['tipo_cedula'] ?? '';
 $cedula = $_POST['cedula'] ?? '';
 $nombre = $_POST['nombre'] ?? '';
 $apellido = $_POST['apellido'] ?? '';
 $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
 $genero = $_POST['genero'] ?? '';
 $email = $_POST['correo'] ?? null;
 $codigo_colegiatura = $_POST['cod_colegiatura'] ?? '';
 $fecha_ingreso = $_POST['fecha_ingreso'] ?? '';
 $prefijo = $_POST['prefijo'] ?? '';
 $telefono = $_POST['telefono'] ?? '';
 $areas_seleccionadas = $_POST['areas_seleccionadas'] ?? '';
 $especialidades_seleccionadas = $_POST['especialidades_seleccionadas'] ?? '';

 $crear_usuario = $_POST['crear_usuario'] ?? '0';
 $password_plana = $_POST['password'] ?? '';
 $confirm_password = $_POST['confirm_password'] ?? '';

 $token = null;

 $estado = ($crear_usuario == '1' && !empty($password_plana)) ? 3 : 2;
 $rol = 7;

 if ($crear_usuario == '1') {
     if (empty($email)) {
         $_SESSION['mensaje_user_error'] = '⚠️ Error: El correo electrónico es obligatorio para crear el acceso al sistema.';
         header("location: ../../pages/php/rh_medico_agregar.php");
         exit;
     }

     if (!validarEmailReal($email)) {
         $_SESSION['mensaje_user_error'] = '⚠️ El dominio del correo no parece ser válido o no puede recibir mensajes.';
         header("location: ../../pages/php/rh_medico_agregar.php");
         exit;
     }

     if ($password_plana !== $confirm_password || empty($password_plana)) {
         $_SESSION['mensaje_user_error'] = '⚠️ Error de Contraseña: Las contraseñas no coinciden o están vacías.';
         header("location: ../../pages/php/rh_medico_agregar.php");
         exit;
     }

     $token = sprintf("%06d", mt_rand(1, 999999));
 }

 $password_hash = "";
 if ($crear_usuario == '1' && !empty($password_plana)) {
     $password_hash = password_hash($password_plana, PASSWORD_DEFAULT);
 }

 $conexion->begin_transaction();

 try {
    $id_medico = 0;
    
    $sql_medico = "INSERT INTO persona(nombre, apellido, tipo_cedula, cedula, fecha_nacimiento, genero, email, password, estatus, token_activacion) 
                   VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_medico = $conexion->prepare($sql_medico);
    
    if (!$stmt_medico) {
        throw new Exception("⚠️ Error al preparar INSERT en persona: " . $conexion->error);
    }
    
    $stmt_medico->bind_param("ssssssssis", $nombre, $apellido, $tipo_cedula, $cedula, $fecha_nacimiento, $genero, $email, $password_hash, $estado, $token);
    
    if (!$stmt_medico->execute()) {
        throw new Exception("⚠️ Error al insertar persona (Médico): " . $stmt_medico->error);
    }
    $id_medico = $conexion->insert_id;
    $stmt_medico->close();

    $sql_detalle_medico = "INSERT INTO detalle_medico(cod_colegiatura, fecha_ingreso, Id_persona)
                           VALUES(?, ?, ?)";
    $stmt_detalle_medico = $conexion->prepare($sql_detalle_medico);
    
    if (!$stmt_detalle_medico) {
        throw new Exception("⚠️ Error al preparar INSERT en detalle_medico: " . $conexion->error);
    }
    
    $stmt_detalle_medico->bind_param("isi", $codigo_colegiatura, $fecha_ingreso, $id_medico);
    
    if (!$stmt_detalle_medico->execute()) {
        throw new Exception("⚠️ Error al insertar detalle médico: " . $stmt_detalle_medico->error);
    }
    
    $id_detalle_medico = $conexion->insert_id;
    $stmt_detalle_medico->close();

    if (!empty($areas_seleccionadas)) {
        $areas_array = explode('|', $areas_seleccionadas);
        
        $sql_medicos_departamentos = "INSERT INTO medicos_departamentos(Id_departamento, Id_detalle_medico) VALUES(?, ?)";
        $stmt_medicos_departamentos = $conexion->prepare($sql_medicos_departamentos);

        if (!$stmt_medicos_departamentos) {
            throw new Exception("⚠️ Error al preparar INSERT en medicos_departamentos: " . $conexion->error);
        }

        foreach ($areas_array as $id_area) {
            $id_area = (int) $id_area;
            if ($id_area > 0) {
                $stmt_medicos_departamentos->bind_param("ii", $id_area, $id_detalle_medico);
                if (!$stmt_medicos_departamentos->execute()) {
                    throw new Exception("⚠️ Error al insertar médicos/departamentos: " . $stmt_medicos_departamentos->error);
                }
            }
        }
        $stmt_medicos_departamentos->close();
    }

    if (!empty($especialidades_seleccionadas)) {
        $especialidades_array = explode('|', $especialidades_seleccionadas);
        
        $sql_especialidades_medicos = "INSERT INTO especialidades_medicos(Id_especialidad, Id_detalle_medico) VALUES(?, ?)";
        $stmt_especialidades_medicos = $conexion->prepare($sql_especialidades_medicos);

        if (!$stmt_especialidades_medicos) {
            throw new Exception("⚠️ Error al preparar INSERT en especialidades_medicos: " . $conexion->error);
        }

        foreach ($especialidades_array as $id_esp) {
            $id_esp = (int) $id_esp;
            if ($id_esp > 0) {
                $stmt_especialidades_medicos->bind_param("ii", $id_esp, $id_detalle_medico);
                if (!$stmt_especialidades_medicos->execute()) {
                    throw new Exception("⚠️ Error al insertar especialidad: " . $stmt_especialidades_medicos->error);
                }
            }
        }
        $stmt_especialidades_medicos->close();
    }

    $sql_telefono = "INSERT INTO telefonos_personas(Id_prefijo, telefono, Id_persona, estatus) VALUES(?, ?, ?, ?)";
    $stmt_telefono = $conexion->prepare($sql_telefono);

    if (!$stmt_telefono) {
        throw new Exception("⚠️ Error al preparar INSERT en telefonos_personas: " . $conexion->error);
    }

    $stmt_telefono->bind_param("sisi", $prefijo, $telefono, $id_medico, $estado);
    
    if (!$stmt_telefono->execute()) {
        throw new Exception("⚠️ Error al insertar teléfono: " . $stmt_telefono->error);
    }
    $stmt_telefono->close();

    $sql_rol = "INSERT INTO detalle_persona_rol(Id_persona, Id_rol, estatus) VALUES(?, ?, ?)";
    $stmt_rol = $conexion->prepare($sql_rol);

    if (!$stmt_rol) {
        throw new Exception("⚠️ Error al preparar INSERT en detalle_persona_rol: " . $conexion->error);
    }

    $estado_rol_defecto = 2;
    $stmt_rol->bind_param("iii", $id_medico, $rol, $estado_rol_defecto);

    if (!$stmt_rol->execute()) {
        throw new Exception("⚠️ Error al insertar rol: " . $stmt_rol->error);
    }
    $stmt_rol->close();

    $conexion->commit();

    $mensaje_correo = "";
    
    if ($crear_usuario == '1' && !empty($email) && !empty($password_plana) && !empty($token)) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'cpt3sistema@gmail.com';
            $mail->Password   = 'Jrjjfgomexsyyxqg';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('cpt3sistema@gmail.com', 'Sistema CPT3');
            $mail->addAddress($email, $nombre . ' ' . $apellido);

            $mail->isHTML(true);
            $mail->Subject = 'Codigo de verificacion - Registro de Medico';
            $mail->Body    = "<h3>¡Hola, Dr./Dra. $nombre $apellido!</h3>
                              <p>Se ha registrado exitosamente su perfil médico en el sistema CPT3.</p>
                              <p>Su código de verificación es:</p>
                              <h2 style='letter-spacing: 5px; color: #00a65a; font-size: 28px; background: #f4f4f4; padding: 10px; display: inline-block; border-radius: 5px;'>$token</h2>
                              <p>Ingrese este código en la ventana de confirmación del sistema para activar su cuenta.</p>
                              <p><b>Datos de acceso provisionales:</b></p>
                              <ul>
                                <li><b>Usuario (Correo):</b> $email</li>
                              </ul>
                              <p>Si no solicitó este registro, por favor ignore este mensaje.</p>";

            $mail->send();
            $mensaje_correo = " Se ha enviado un código de activación al correo electrónico.";
            
            $_SESSION['requiere_verificacion'] = true;
            $_SESSION['email_verificacion']    = $email;

        } catch (Exception $e) {
            $mensaje_correo = ", pero no se pudo enviar el correo de activación.";
        }
    }
    
    $cedula_info = $tipo_cedula . '-' . $cedula;
    $_SESSION['mensaje_user_exito'] = '✅ Éxito: El Médico ' . $nombre . ' ' . $apellido . ' (' . $cedula_info . ') fue registrado correctamente.' . $mensaje_correo;

    header('location: ../../pages/php/rh_medico_listado.php');
    exit();

 } catch (Exception $e) {
    $conexion->rollback();

    error_log("⚠️ Error de transacción al agregar el médico: " . $e->getMessage());
    $_SESSION['mensaje_user_error'] = '⚠️ Error de Registro: No se pudo registrar el médico. Detalle: ' . $e->getMessage();

    header('location: ../../pages/php/rh_medico_listado.php');
    exit();
 }

 $conexion->close();
?>