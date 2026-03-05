<?php
session_start();
include('../conexion.php');

// IMPORTAR PHPMAILER PARA NOTIFICACIONES
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../plugins/PHPMailer/src/Exception.php';
require '../../plugins/PHPMailer/src/PHPMailer.php';
require '../../plugins/PHPMailer/src/SMTP.php';

if (isset($_GET['Id'])) {
    $Id = mysqli_real_escape_string($conexion, $_GET['Id']);

    // 1. OBTENER DATOS DEL USUARIO ANTES DE REACTIVAR
    $query_user = "SELECT nombre, email FROM persona WHERE id = '$Id' LIMIT 1";
    $resultado = mysqli_query($conexion, $query_user);
    $datos_usuario = mysqli_fetch_assoc($resultado);

    if ($datos_usuario) {
        $nombre_user = $datos_usuario['nombre'];
        $email_user  = $datos_usuario['email'];

        // 2. CAMBIAR ESTATUS A 2 (Activo)
        $sql = "UPDATE persona SET estatus = 2 WHERE id = '$Id'";

        if (mysqli_query($conexion, $sql)) {
            
            // --- BLOQUE DE ENVÍO DE CORREO POR REACTIVACIÓN ---
            $mail = new PHPMailer(true);
            $info_correo = "";

            try {
                // Configuración del Servidor SMTP
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'cpt3sistema@gmail.com'; // Tu cuenta de Gmail
                $mail->Password   = 'rqgltslfvazhjqix'; // Tu clave de aplicación
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // Destinatarios
                $mail->setFrom('no-reply@cpt3.com', 'Sistema CPT3');
                $mail->addAddress($email_user, $nombre_user);

                // Contenido del Correo
                $mail->isHTML(true);
                $mail->Subject = 'Cuenta Reactivada - Sistema CPT3';
                $mail->Body    = "<h3>¡Hola, $nombre_user!</h3>
                                  <p>Te informamos que tu cuenta en el sistema ha sido <b>reactivada exitosamente</b>.</p>
                                  <p>Ya puedes iniciar sesión con tus credenciales habituales.</p>
                                  <p>Si tienes problemas para ingresar, contacta al administrador.</p>";
                
                $mail->send();
                $info_correo = " y se notificó al usuario.";
            } catch (Exception $e) {
                $info_correo = ", pero no se pudo enviar el correo de notificación.";
            }
            // --- FIN BLOQUE DE CORREO ---

            $_SESSION['mensaje_user_exito'] = "✅ Éxito: el usuario fue activado correctamente" . $info_correo;
        } else {
            $_SESSION['mensaje_user_error'] = "❌ Error de Activación: " . mysqli_error($conexion);
        }
    } else {
        $_SESSION['mensaje_user_error'] = "❌ Error: Usuario no encontrado.";
    }

    header("Location: ../../pages/php/papelera/cfg_usuario_papelera_listado.php");
    exit;
}
?>