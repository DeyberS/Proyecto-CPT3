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
 $id_medico = (int) ($_POST['Id'] ?? 0); // ID principal para el UPDATE

 // Datos Personales
 $tipo_cedula = $_POST['tipo_cedula'] ?? '';
 $cedula = $_POST['cedula'] ?? '';
 $nombre = $_POST['nombre'] ?? '';
 $apellido = $_POST['apellido'] ?? '';
 $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
 $genero = $_POST['genero'] ?? '';
 $email = $_POST['correo'] ?? null;
 $estado = 2;

 // Datos del Médico, Departamento y Especialidad (NUEVO CAMPO)
 $codigo_colegiatura = $_POST['cod_colegiatura'] ?? '';
 $fecha_ingreso = $_POST['fecha_ingreso'] ?? '';
 $areas_seleccionadas = $_POST['areas_seleccionadas'] ?? '';
 $especialidades_seleccionadas = $_POST['especialidades_seleccionadas'] ?? '';

 // Convertir los strings en arreglos (arrays) e ignorar valores vacíos
 $areas_array = array_filter(explode('|', $areas_seleccionadas));
 $especialidades_array = array_filter(explode('|', $especialidades_seleccionadas));
 
 // Teléfonos
 $prefijo = $_POST['prefijo'] ?? '';
 $telefono = $_POST['telefono'] ?? '';

$password_plana = $_POST['password'] ?? '';
$sql_password_part = ""; // Parte extra de la consulta SQL

if (!empty($password_plana)) {
    // Si el usuario escribió una clave, la encriptamos
    $password_hash = password_hash($password_plana, PASSWORD_DEFAULT);
    $sql_password_part = ", password = '$password_hash'";
}

 if ($id_medico <= 0) {
    // Usar mensaje de sesión para error
    $_SESSION['mensaje_user_error'] = '❌ Error: Se requiere una ID de médico válida para la actualización.';
    header('location: ../../pages/php/rh_medico_listado.php');
    exit;
 }

 // --- 2. Inicio de la Transacción ---
 $conexion->begin_transaction();

 try {
    // 2.1. UPDATE en PERSONA (Datos personales)
    $sql_medico = "UPDATE persona 
                   SET nombre = ?, apellido = ?, tipo_cedula = ?, cedula = ?, fecha_nacimiento = ?, genero = ?, email = ? $sql_password_part
                   WHERE id = ?";
    
    $stmt_medico = $conexion->prepare($sql_medico);
    
    if (!$stmt_medico) {
        throw new Exception("Error al preparar UPDATE en persona: " . $conexion->error);
    }
    
    $stmt_medico->bind_param("sssssssi", $nombre, $apellido, $tipo_cedula, $cedula, $fecha_nacimiento, $genero, $email, $id_medico);
    
    if (!$stmt_medico->execute()) {
        throw new Exception("Error al actualizar persona: " . $stmt_medico->error);
    }
    $stmt_medico->close();

    // 2.2. UPDATE en DETALLE_MEDICO (Fecha de ingreso)
    $sql_detalle_medico = "UPDATE detalle_medico SET cod_colegiatura = ?, fecha_ingreso = ? WHERE Id_persona = ?";
    $stmt_detalle_medico = $conexion->prepare($sql_detalle_medico);
    
    if (!$stmt_detalle_medico) {
        throw new Exception("Error al preparar UPDATE en detalle_medico: " . $conexion->error);
    }
    
    $stmt_detalle_medico->bind_param("isi", $codigo_colegiatura, $fecha_ingreso, $id_medico);
    
    if (!$stmt_detalle_medico->execute()) {
        throw new Exception("Error al actualizar detalle médico: " . $stmt_detalle_medico->error);
    }
    $stmt_detalle_medico->close();


    // --- 2.3. Obtener el Id_detalle_medico (NECESARIO PARA DEPARTAMENTOS Y ESPECIALIDADES) ---
    $id_detalle_medico = 0;
    $sql_get_detalle_id = "SELECT Id_detalle_medico FROM detalle_medico WHERE Id_persona = ?";
    $stmt_get_detalle_id = $conexion->prepare($sql_get_detalle_id);
    
    if (!$stmt_get_detalle_id) {
        throw new Exception("Error al preparar SELECT Id_detalle_medico: " . $conexion->error);
    }
    
    $stmt_get_detalle_id->bind_param("i", $id_medico);
    $stmt_get_detalle_id->execute();
    $res_detalle = $stmt_get_detalle_id->get_result();
    
    if ($res_detalle->num_rows > 0) {
        $id_detalle_medico = $res_detalle->fetch_assoc()['Id_detalle_medico'];
    } else {
        throw new Exception("No se encontró Id_detalle_medico para el médico $id_medico.");
    }
    $stmt_get_detalle_id->close();

    // --- 2.4. Gestión del Departamento (MEDICOS_DEPARTAMENTOS) ---
    if ($id_detalle_medico > 0) {
        // Primero BORRAR todas las áreas anteriores del médico
        $sql_delete_areas = "DELETE FROM medicos_departamentos WHERE Id_detalle_medico = ?";
        $stmt_delete_areas = $conexion->prepare($sql_delete_areas);
        $stmt_delete_areas->bind_param("i", $id_detalle_medico);
        $stmt_delete_areas->execute();
        $stmt_delete_areas->close();

        // Luego INSERTAR las nuevas áreas recorriendo el arreglo
        if (!empty($areas_array)) {
            $sql_insert_area = "INSERT INTO medicos_departamentos(Id_departamento, Id_detalle_medico) VALUES(?, ?)";
            $stmt_insert_area = $conexion->prepare($sql_insert_area);
            foreach ($areas_array as $area_id) {
                $area_id_int = (int) $area_id;
                $stmt_insert_area->bind_param("ii", $area_id_int, $id_detalle_medico);
                $stmt_insert_area->execute();
            }
            $stmt_insert_area->close();
        }
    }
    
    // --- 2.5. Gestión de la Especialidad (ESPECIALIDADES_MEDICOS) ---
    if ($id_detalle_medico > 0) {
        // Primero BORRAR todas las especialidades anteriores del médico
        $sql_delete_esp = "DELETE FROM especialidades_medicos WHERE Id_detalle_medico = ?";
        $stmt_delete_esp = $conexion->prepare($sql_delete_esp);
        $stmt_delete_esp->bind_param("i", $id_detalle_medico);
        $stmt_delete_esp->execute();
        $stmt_delete_esp->close();
        
        // Luego INSERTAR las nuevas especialidades recorriendo el arreglo
        if (!empty($especialidades_array)) {
            $sql_insert_esp = "INSERT INTO especialidades_medicos(Id_especialidad, Id_detalle_medico) VALUES(?, ?)";
            $stmt_insert_esp = $conexion->prepare($sql_insert_esp);
            
            foreach ($especialidades_array as $esp_id) {
                $esp_id_int = (int) $esp_id;
                $stmt_insert_esp->bind_param("ii", $esp_id_int, $id_detalle_medico);
                $stmt_insert_esp->execute();
            }
            $stmt_insert_esp->close();
        }
    }


    // 2.6. UPDATE/REPLACE en TELEFONOS_PERSONAS
    $sql_telefono = "UPDATE telefonos_personas 
    SET Id_prefijo = ?, telefono = ?, estatus = ? 
    WHERE Id_persona = ?";
    $stmt_telefono = $conexion->prepare($sql_telefono);
    $stmt_telefono->bind_param("isii", $prefijo, $telefono,$estado,$id_medico);
    
    if (!$stmt_telefono->execute()) {
        throw new Exception("Error al actualizar teléfono: " . $stmt_telefono->error);
    }
    $stmt_telefono->close();

    // --- 3. Commit y Redirección Final ---
    $conexion->commit();

    $mensaje_correo = "."; // Mensaje por defecto si no hay envío de correo

    // Verificar si se introdujo una nueva contraseña y existe un correo
    if (!empty($password_plana) && !empty($email)) {
        $mail = new PHPMailer(true);
        try {
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; 
            $mail->SMTPAuth = true;
            $mail->Username = 'cpt3sistema@gmail.com'; 
            $mail->Password = 'rqgltslfvazhjqix'; // Contraseña de aplicación
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Destinatarios
            $mail->setFrom('cpt3sistema@gmail.com', 'Sistema CPT3');
            $mail->addAddress($email, $nombre . ' ' . $apellido);

            // Contenido del correo adaptado para ACTUALIZACIÓN
            $mail->isHTML(true);
            $mail->Subject = 'Actualizacion de Credenciales - Sistema CPT3';
            $mail->Body    = "<h3>Actualización de Credenciales, Dr./Dra. $apellido</h3>
                              <p>Su contraseña de acceso al <b>Sistema CPT3</b> ha sido modificada exitosamente.</p>
                              <p><b>Tus nuevos datos de acceso:</b></p>
                              <ul>
                                <li><b>Usuario (Correo):</b> $email</li>
                                <li><b>Nueva Contraseña:</b> $password_plana</li>
                              </ul>
                              <p>Por seguridad, te recomendamos resguardar esta información.</p>";

            $mail->send();
            $mensaje_correo = " y las nuevas credenciales fueron enviadas por correo.";
        } catch (Exception $e) {
            $mensaje_correo = ", pero hubo un problema al enviar el correo de notificación.";
        }
    }
    
    // Mensaje de Éxito
    $_SESSION['mensaje_user_exito'] = '✅ Éxito: El médico ' . $nombre . ' ' . $apellido . ' ha sido actualizado correctamente.';

    header('location: ../../pages/php/rh_medico_listado.php');
    exit();

 } catch (Exception $e) {
    // 4. Rollback y Mensaje de Error
    $conexion->rollback();

    error_log("Error de transacción al actualizar el médico: " . $e->getMessage()); 
    // Mensaje de Error
    $_SESSION['mensaje_user_error'] = '❌ Error de Actualización: No se pudo actualizar el médico. Detalle: ' . $e->getMessage();

    header('location: ../../pages/php/rh_medico_listado.php');
    exit();
 }

 $conexion->close();
?>