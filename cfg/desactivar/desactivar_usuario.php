<?php
session_start();
include('../conexion.php');

// IMPORTAR PHPMAILER
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../plugins/PHPMailer/src/Exception.php';
require '../../plugins/PHPMailer/src/PHPMailer.php';
require '../../plugins/PHPMailer/src/SMTP.php';

if (isset($_GET['Id'])) {
    $Id = mysqli_real_escape_string($conexion, $_GET['Id']);

    // 1. OBTENER DATOS DEL USUARIO ANTES DE DESACTIVAR
    $query_user = "SELECT nombre, email FROM persona WHERE id = '$Id' LIMIT 1";
    $resultado = mysqli_query($conexion, $query_user);
    $datos_usuario = mysqli_fetch_assoc($resultado);

    if ($datos_usuario) {
        $nombre_user = $datos_usuario['nombre'];
        $email_user  = $datos_usuario['email'];

        // 2. CAMBIAR ESTATUS A 0 (Inactivo)
        $sql = "UPDATE persona SET estatus = 0 WHERE id = '$Id'";

        if (mysqli_query($conexion, $sql)) {
            
            // --- BLOQUE DE ENVÍO DE CORREO POR DESACTIVACIÓN ---
            $mail = new PHPMailer(true);
            $info_correo = "";

            try {
                // Configuración del Servidor
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'cpt3sistema@gmail.com';
                $mail->Password   = 'rqgltslfvazhjqix'; // Tu clave de aplicación
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // Destinatarios
                $mail->setFrom('cpt3sistema@gmail.com', 'Sistema CPT3');
                $mail->addAddress($email_user, $nombre_user);

                // Contenido
                $mail->isHTML(true);
                $mail->Subject = 'Cuenta Desactivada - Sistema CPT3';
                $mail->Body    = "<h3>Hola, $nombre_user</h3>
                                  <p>Te informamos que tu cuenta en el sistema ha sido <b>desactivada</b>.</p>
                                  <p>Si crees que esto es un error, por favor contacta al administrador del sistema.</p>";
                
                $mail->send();
                $info_correo = " y se notificó al usuario.";
            } catch (Exception $e) {
                $info_correo = ", pero no se pudo enviar el correo de notificación.";
            }
            // --- FIN BLOQUE DE CORREO ---

            $_SESSION['mensaje_user_exito'] = "✅ Éxito: el usuario fue desactivado correctamente" . $info_correo;
        } else {
            $_SESSION['mensaje_user_error'] = "❌ Error de Desactivación: " . mysqli_error($conexion);
        }
    } else {
        $_SESSION['mensaje_user_error'] = "❌ Error: Usuario no encontrado.";
    }

    header("Location: ../../pages/php/cfg_usuario_listado.php");
    exit;
}
?>