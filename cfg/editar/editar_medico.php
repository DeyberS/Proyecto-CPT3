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

$id_medico = (int) ($_POST['Id'] ?? 0);
$id_usuario_sesion = (int) ($_SESSION['id'] ?? 0);

if ($id_medico <= 0) {
    $_SESSION['mensaje_user_error'] = '⚠️ Error: Se requiere un ID de médico válido para la actualización.';
    header('location: ../../pages/php/rh_medico_listado.php');
    exit;
}

$es_mismo_usuario = ($id_medico === $id_usuario_sesion);

$tipo_cedula = $_POST['tipo_cedula'] ?? '';
$cedula = $_POST['cedula'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$apellido = $_POST['apellido'] ?? '';
$fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
$genero = $_POST['genero'] ?? '';
$email = $_POST['correo'] ?? null;
$estado = 2;

$codigo_colegiatura = $_POST['cod_colegiatura'] ?? '';
$fecha_ingreso = $_POST['fecha_ingreso'] ?? '';
$areas_seleccionadas = $_POST['areas_seleccionadas'] ?? '';
$especialidades_seleccionadas = $_POST['especialidades_seleccionadas'] ?? '';

$areas_array = array_filter(explode('|', $areas_seleccionadas));
$especialidades_array = array_filter(explode('|', $especialidades_seleccionadas));

$prefijo = $_POST['prefijo'] ?? '';
$telefono = $_POST['telefono'] ?? '';

$password_plana = $_POST['password'] ?? '';
$sql_password_part = ""; 

if (!empty($password_plana)) {
    $password_hash = password_hash($password_plana, PASSWORD_DEFAULT);
    $sql_password_part = ", password = '$password_hash'";
}

$email_actual = "";
$sql_get_email = "SELECT email FROM persona WHERE id = ?";
$stmt_get_email = $conexion->prepare($sql_get_email);
if ($stmt_get_email) {
    $stmt_get_email->bind_param("i", $id_medico);
    $stmt_get_email->execute();
    $res_email = $stmt_get_email->get_result();
    if ($res_email->num_rows > 0) {
        $email_actual = $res_email->fetch_assoc()['email'];
    }
    $stmt_get_email->close();
}

$sql_token_part = "";
$token = "";
$cambio_email = (!empty($email) && $email !== $email_actual);

if ($cambio_email) {
    if (!validarEmailReal($email)) {
        $_SESSION['mensaje_user_error'] = '⚠️ El dominio del nuevo correo electrónico no parece ser válido o no puede recibir mensajes.';
        header("location: ../../pages/php/rh_medico_listado.php");
        exit;
    }
    
    $token = sprintf("%06d", mt_rand(1, 999999));
    $sql_token_part = ", estatus = '3', token_activacion = '$token'";
}

$conexion->begin_transaction();

try {
    $sql_medico = "UPDATE persona 
                   SET nombre = ?, apellido = ?, tipo_cedula = ?, cedula = ?, fecha_nacimiento = ?, genero = ?, email = ? $sql_password_part $sql_token_part
                   WHERE id = ?";
    
    $stmt_medico = $conexion->prepare($sql_medico);
    if (!$stmt_medico) {
        throw new Exception("⚠️ Error al preparar UPDATE en persona: " . $conexion->error);
    }
    
    $stmt_medico->bind_param("sssssssi", $nombre, $apellido, $tipo_cedula, $cedula, $fecha_nacimiento, $genero, $email, $id_medico);
    
    if (!$stmt_medico->execute()) {
        throw new Exception("⚠️ Error al actualizar persona: " . $stmt_medico->error);
    }
    $stmt_medico->close();

    $sql_detalle_medico = "UPDATE detalle_medico SET cod_colegiatura = ?, fecha_ingreso = ? WHERE Id_persona = ?";
    $stmt_detalle_medico = $conexion->prepare($sql_detalle_medico);
    
    if (!$stmt_detalle_medico) {
        throw new Exception("⚠️ Error al preparar UPDATE en detalle_medico: " . $conexion->error);
    }
    
    $stmt_detalle_medico->bind_param("isi", $codigo_colegiatura, $fecha_ingreso, $id_medico);
    
    if (!$stmt_detalle_medico->execute()) {
        throw new Exception("⚠️ Error al actualizar detalle médico: " . $stmt_detalle_medico->error);
    }
    $stmt_detalle_medico->close();

    $id_detalle_medico = 0;
    $sql_get_detalle_id = "SELECT Id_detalle_medico FROM detalle_medico WHERE Id_persona = ?";
    $stmt_get_detalle_id = $conexion->prepare($sql_get_detalle_id);
    
    if (!$stmt_get_detalle_id) {
        throw new Exception("⚠️ Error al preparar SELECT Id_detalle_medico: " . $conexion->error);
    }
    
    $stmt_get_detalle_id->bind_param("i", $id_medico);
    $stmt_get_detalle_id->execute();
    $res_detalle = $stmt_get_detalle_id->get_result();
    
    if ($res_detalle->num_rows > 0) {
        $id_detalle_medico = $res_detalle->fetch_assoc()['Id_detalle_medico'];
    } else {
        throw new Exception("⚠️ No se encontró Id_detalle_medico para el médico $id_medico.");
    }
    $stmt_get_detalle_id->close();

    if ($id_detalle_medico > 0) {
        $sql_delete_areas = "DELETE FROM medicos_departamentos WHERE Id_detalle_medico = ?";
        $stmt_delete_areas = $conexion->prepare($sql_delete_areas);
        $stmt_delete_areas->bind_param("i", $id_detalle_medico);
        $stmt_delete_areas->execute();
        $stmt_delete_areas->close();

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

    if ($id_detalle_medico > 0) {
        $sql_delete_esp = "DELETE FROM especialidades_medicos WHERE Id_detalle_medico = ?";
        $stmt_delete_esp = $conexion->prepare($sql_delete_esp);
        $stmt_delete_esp->bind_param("i", $id_detalle_medico);
        $stmt_delete_esp->execute();
        $stmt_delete_esp->close();
        
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

    $sql_telefono = "UPDATE telefonos_personas SET Id_prefijo = ?, telefono = ?, estatus = ? WHERE Id_persona = ?";
    $stmt_telefono = $conexion->prepare($sql_telefono);
    $stmt_telefono->bind_param("isii", $prefijo, $telefono, $estado, $id_medico);
    
    if (!$stmt_telefono->execute()) {
        throw new Exception("⚠️ Error al actualizar teléfono: " . $stmt_telefono->error);
    }
    $stmt_telefono->close();

    $conexion->commit();

    $mensaje_correo = ".";

    if (!empty($token) || (!empty($password_plana) && !empty($email))) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; 
            $mail->SMTPAuth = true;
            $mail->Username = 'cpt3sistema@gmail.com'; 
            $mail->Password = 'Jrjjfgomexsyyxqg'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('cpt3sistema@gmail.com', 'Sistema CPT3');
            $mail->addAddress($email, $nombre . ' ' . $apellido);
            $mail->isHTML(true);

            if (!empty($token)) {
                $mail->Subject = 'Codigo de verificacion de nuevo correo - Sistema CPT3';
                $cuerpo = "<h3>¡Hola, Dr./Dra. $nombre $apellido!</h3>
                           <p>Has actualizado tu dirección de correo electrónico en el sistema.</p>";
                
                if (!empty($password_plana)) {
                    $cuerpo .= "<p>También se ha actualizado tu contraseña. Tus nuevos datos son:</p>
                                <ul>
                                  <li><b>Usuario (Nuevo Correo):</b> $email</li>
                                  <li><b>Nueva Contraseña:</b> $password_plana</li>
                                </ul>";
                }
                
                $cuerpo .= "<p>Tu código de verificación es:</p>
                            <h2 style='letter-spacing: 5px; color: #00a65a; font-size: 28px; background: #f4f4f4; padding: 10px; display: inline-block; border-radius: 5px;'>$token</h2>
                            <p>Ingresa este código en el sistema antes de que se agote el tiempo.</p>";
                
                $mail->Body = $cuerpo;
                $mensaje_correo = " ⚠️ ATENCIÓN: Se envió un código de verificación al nuevo correo.";

                $_SESSION['requiere_verificacion'] = true;
                $_SESSION['email_verificacion'] = $email;

            } else {
                $mail->Subject = 'Actualización de Credenciales - Sistema CPT3';
                $mail->Body    = "<h3>Actualización de Credenciales, Dr./Dra. $nombre $apellido</h3>
                                  <p>Su contraseña de acceso al <b>Sistema CPT3</b> ha sido modificada exitosamente.</p>
                                  <p><b>Tus nuevos datos de acceso:</b></p>
                                  <ul>
                                    <li><b>Usuario (Correo):</b> $email</li>
                                    <li><b>Nueva Contraseña:</b> $password_plana</li>
                                  </ul>
                                  <p>Por seguridad, te recomendamos resguardar esta información.</p>";
                $mensaje_correo = " y las nuevas credenciales fueron enviadas por correo.";
            }

            $mail->send();
        } catch (Exception $e) {
            $mensaje_correo = ", pero hubo un problema al enviar el correo de notificación: " . $mail->ErrorInfo;
        }
    }

    if ($es_mismo_usuario && ($cambio_email || !empty($password_plana))) {
        unset($_SESSION["loggedin"]);
        unset($_SESSION["id"]);
        unset($_SESSION["nombre"]);
        unset($_SESSION["estatus"]);
        unset($_SESSION["rol"]);
        unset($_SESSION["nombre_rol"]);
        unset($_SESSION["permisos"]);

        if ($cambio_email) {
            $_SESSION['mensaje_user_exito'] = '✅ Su correo electrónico ha sido modificado. Por seguridad, su sesión ha sido cerrada; a continuación deberás verificar tu nueva dirección antes de volver a ingresar.';
        } else {
            $_SESSION['mensaje_user_exito'] = '✅ Su contraseña ha sido actualizada exitosamente. Por seguridad, inicie sesión con sus nuevas credenciales.';
        }

        header('location: ../../index.php');
        exit();
    } else {
        $_SESSION['mensaje_user_exito'] = '✅ Éxito: El médico ' . $nombre . ' ' . $apellido . ' ha sido actualizado correctamente.' . $mensaje_correo;

        header('location: ../../pages/php/rh_medico_listado.php');
        exit();
    }

 } catch (Exception $e) {
    $conexion->rollback();

    error_log("⚠️ Error de transacción al actualizar el médico: " . $e->getMessage()); 
    
    $_SESSION['mensaje_user_error'] = '⚠️ Error de Actualización: No se pudo actualizar el médico. Detalle: ' . $e->getMessage();

    header('location: ../../pages/php/rh_medico_listado.php');
    exit();
 }

 $conexion->close();
?>