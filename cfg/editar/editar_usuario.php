<?php
include('../conexion.php'); 
session_start();

$es_admin = (isset($_SESSION['rol']) && $_SESSION['rol'] == 1);
$ruta_redireccion = $es_admin ? '../../pages/php/cfg_usuario_listado.php' : '../../inicio.php';

require '../../plugins/PHPMailer/src/Exception.php';
require '../../plugins/PHPMailer/src/PHPMailer.php';
require '../../plugins/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../../plugins/vendor/autoload.php';

function validarEmailReal($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    $dominio = substr(strrchr($email, "@"), 1);
    return checkdnsrr($dominio, "MX");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_usuario = isset($_POST['Id']) ? (int) $_POST['Id'] : 0;
    $nombre     = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $email      = isset($_POST['email']) ? trim($_POST['email']) : '';
    $rol        = isset($_POST['rol']) ? (int) $_POST['rol'] : 0;
    $password   = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    if ($id_usuario === 0 || empty($nombre) || empty($email) || $rol === 0) {
        $_SESSION['mensaje_user_error'] = '⚠️ Error: Faltan datos esenciales (ID, Nombre, Email o Rol) para la actualización.';
        header("Location: $ruta_redireccion");
        exit;
    }

    if (!validarEmailReal($email)) {
        $_SESSION['mensaje_user_error'] = "⚠️ El dominio del correo no parece ser válido o no puede recibir mensajes.";
        header("Location: $ruta_redireccion"); 
        exit();
    }

    $sql_verificar = "SELECT email FROM persona WHERE id = ?";
    $stmt_verificar = $conexion->prepare($sql_verificar);
    $stmt_verificar->bind_param("i", $id_usuario);
    $stmt_verificar->execute();
    $resultado_verificar = $stmt_verificar->get_result();
    $fila_verificar = $resultado_verificar->fetch_assoc();
    $correo_actual = $fila_verificar['email'];

    $cambio_correo = ($correo_actual !== $email);

    $cambio_password = false;
    if (!empty($password)) {
        if ($password === $confirm_password) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $cambio_password = true;
        } else {
            $_SESSION['mensaje_user_error'] = '⚠️ Error: Las contraseñas nuevas no coinciden.';
            header("Location: $ruta_redireccion");
            exit;
        }
    }

    if ($cambio_correo) {
        $token = sprintf("%06d", mt_rand(1, 999999));
        $estado_usuario = 3; 

        if ($cambio_password) {
            $sql = "UPDATE persona SET nombre = ?, email = ?, password = ?, estatus = ?, token_activacion = ? WHERE id = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("sssisi", $nombre, $email, $hashed_password, $estado_usuario, $token, $id_usuario);
        } else {
            $sql = "UPDATE persona SET nombre = ?, email = ?, estatus = ?, token_activacion = ? WHERE id = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("ssisi", $nombre, $email, $estado_usuario, $token, $id_usuario);
        }
    } else {
        if ($cambio_password) {
            $sql = "UPDATE persona SET nombre = ?, password = ? WHERE id = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("ssi", $nombre, $hashed_password, $id_usuario);
        } else {
            $sql = "UPDATE persona SET nombre = ? WHERE id = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("si", $nombre, $id_usuario);
        }
    }

    if ($stmt->execute()) {
        
        $sql_rol = "UPDATE detalle_persona_rol SET Id_rol = ? WHERE Id_persona = ?";
        $stmt_rol = $conexion->prepare($sql_rol);
        $stmt_rol->bind_param("ii", $rol, $id_usuario);
        
        if ($stmt_rol->execute()) {

            $info_correo = "";
            
            if ($cambio_password) {
                $mail_pwd = new PHPMailer(true);
                try {
                    $mail_pwd->isSMTP();
                    $mail_pwd->Host       = 'smtp.gmail.com'; 
                    $mail_pwd->SMTPAuth   = true;
                    $mail_pwd->Username   = 'cpt3sistema@gmail.com';
                    $mail_pwd->Password   = 'Jrjjfgomexsyyxqg';
                    $mail_pwd->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail_pwd->Port       = 587;

                    $mail_pwd->setFrom('no-reply@cpt3.com', 'Sistema CPT3');
                    $mail_pwd->addAddress($email, $nombre);

                    $mail_pwd->isHTML(true);
                    $mail_pwd->Subject = 'Notificacion de Seguridad - Cambio de Clave';
                    $mail_pwd->Body    = "<h3>Hola, $nombre</h3>
                                      <p>Se ha realizado un cambio en tu contraseña de acceso al sistema.</p>
                                      <p>Tu nueva clave es: <b>$password</b></p>
                                      <p>Si no realizaste este cambio, contacta a soporte inmediatamente.</p>";
                    $mail_pwd->send();
                    $info_correo .= " Se notificó el cambio de clave.";
                } catch (Exception $e) {
                    $info_correo .= " (Error al notificar clave).";
                }
            }

            if ($cambio_correo) {
                $mail_verif = new PHPMailer(true);
                try {
                    $mail_verif->isSMTP();
                    $mail_verif->Host       = 'smtp.gmail.com'; 
                    $mail_verif->SMTPAuth   = true;
                    $mail_verif->Username   = 'cpt3sistema@gmail.com';
                    $mail_verif->Password   = 'Jrjjfgomexsyyxqg';
                    $mail_verif->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail_verif->Port       = 587;

                    $mail_verif->setFrom('no-reply@cpt3.com', 'Sistema CPT3');
                    $mail_verif->addAddress($email, $nombre);

                    $mail_verif->isHTML(true);
                    $mail_verif->Subject = 'Codigo de verificacion de nuevo correo';
                    $mail_verif->Body    = "<h3>¡Hola, $nombre!</h3>
                                      <p>Has actualizado tu dirección de correo electrónico en el sistema.</p>
                                      <p>Tu código de verificación es:</p>
                                      <h2 style='letter-spacing: 5px; color: #00a65a; font-size: 28px; background: #f4f4f4; padding: 10px; display: inline-block; border-radius: 5px;'>$token</h2>
                                      <p>Ingresa este código en el sistema antes de que se agote el tiempo.</p>";
                    $mail_verif->send();
                    $info_correo .= " ⚠️ ATENCIÓN: Se envió un código de verificación al nuevo correo.";
                    
                    $_SESSION['requiere_verificacion'] = true;
                    $_SESSION['email_verificacion'] = $email;
                    
                } catch (Exception $e) {
                    $info_correo .= " (Error al enviar enlace de verificación).";
                }
            }

        if (($cambio_correo || $cambio_password) && isset($_SESSION['id']) && $_SESSION['id'] == $id_usuario) {
            
            unset($_SESSION['loggedin']);
            unset($_SESSION['id']);
            unset($_SESSION['nombre']);
            unset($_SESSION['estatus']);
            unset($_SESSION['rol']);
            unset($_SESSION['nombre_rol']);
            unset($_SESSION['permisos']);
            
            if ($cambio_correo) {
                $_SESSION['mensaje_user_exito'] = '✅ Has cambiado tu correo. Por seguridad, hemos cerrado tu sesión. A continuación deberás verificar tu nueva dirección antes de volver a ingresar.';
            } else {
                $_SESSION['mensaje_user_exito'] = '✅ Has actualizado tu contraseña. Por seguridad, hemos cerrado tu sesión. Por favor, vuelve a ingresar con tu nueva clave.';
            }
            
            header("Location: ../../index.php");
            exit;
        }

            $_SESSION['mensaje_user_exito'] = '✅ Éxito: El usuario ' . $nombre . ' ha sido actualizado correctamente.' . $info_correo;
            header("Location: $ruta_redireccion");
            exit;

        } else {
            $_SESSION['mensaje_user_error'] = '⚠️ Error: Los datos personales se actualizaron, pero falló la actualización del rol.';
            header("Location: $ruta_redireccion");
            exit;
        }

    } else {
        $_SESSION['mensaje_user_error'] = '⚠️ Error de Ejecución: No se pudieron actualizar los datos personales.';
        header("Location: $ruta_redireccion");
        exit;
    }

} else {
    $_SESSION['mensaje_user_error'] = '⚠️ Error de Petición: Acceso no autorizado al script de edición.';
    header("Location: $ruta_redireccion");
    exit;
}
?>