<?php
session_start();
include('conexion.php'); // Ajusta la ruta según tu estructura de carpetas

// IMPORTAR PHPMAILER PARA NOTIFICACIONES
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../plugins/PHPMailer/src/Exception.php';
require '../plugins/PHPMailer/src/PHPMailer.php';
require '../plugins/PHPMailer/src/SMTP.php';

require '../../plugins/vendor/autoload.php';

if (isset($_GET['Id'])) {
    $Id = mysqli_real_escape_string($conexion, $_GET['Id']);

    // 1. OBTENER DATOS DEL USUARIO ANTES DE DESBLOQUEAR
    $query_user = "SELECT nombre, email FROM persona WHERE id = '$Id' LIMIT 1";
    $resultado = mysqli_query($conexion, $query_user);
    $datos_usuario = mysqli_fetch_assoc($resultado);

    if ($datos_usuario) {
        $nombre_user = $datos_usuario['nombre'];
        $email_user  = $datos_usuario['email'];

        // 2. RESETEAR EL CONTADOR DE INTENTOS A 0
        $sql = "UPDATE persona SET login_attempts = 0 WHERE id = '$Id'";

        if (mysqli_query($conexion, $sql)) {
            
            // --- BLOQUE DE ENVÍO DE CORREO POR DESBLOQUEO ---
            $mail = new PHPMailer(true);
            $info_correo = "";

            try {
                // Configuración del Servidor SMTP (Misma que archivos anteriores)
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'cpt3sistema@gmail.com';
                $mail->Password   = 'rqgltslfvazhjqix'; // Tu clave de aplicación
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // Destinatarios
                $mail->setFrom('no-reply@cpt3.com', 'Sistema CPT3');
                $mail->addAddress($email_user, $nombre_user);

                // Contenido del Correo
                $mail->isHTML(true);
                $mail->Subject = 'Cuenta Desbloqueada - Sistema CPT3';
                $mail->Body    = "<h3>¡Hola, $nombre_user!</h3>
                                  <p>Te informamos que tu cuenta en el sistema ha sido <b>desbloqueada exitosamente</b> por un administrador.</p>
                                  <p>Tus intentos de inicio de sesión han sido restablecidos. Ya puedes intentar acceder nuevamente con tus credenciales.</p>
                                  <p>Si no fuiste tú quien solicitó este cambio o tienes problemas para ingresar, por favor contacta al soporte.</p>";
                
                $mail->send();
                $info_correo = " y se envió el correo de notificación.";
            } catch (Exception $e) {
                $info_correo = ", pero no se pudo enviar el correo informativo.";
            }
            // --- FIN BLOQUE DE CORREO ---

            $_SESSION['mensaje_user_exito'] = "✅ Usuario desbloqueado correctamente" . $info_correo;
        } else {
            $_SESSION['mensaje_user_error'] = "❌ Error al desbloquear en la base de datos: " . mysqli_error($conexion);
        }
    } else {
        $_SESSION['mensaje_user_error'] = "❌ Error: Usuario no encontrado.";
    }
} else {
    $_SESSION['mensaje_user_error'] = "❌ Error: No se recibió el ID del usuario.";
}

// Redirigir de vuelta al listado
header("Location: ../pages/php/cfg_usuario_listado.php");
exit();
?>