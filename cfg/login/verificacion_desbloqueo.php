<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'plugins/PHPMailer/src/Exception.php';
require 'plugins/PHPMailer/src/PHPMailer.php';
require 'plugins/PHPMailer/src/SMTP.php';

require_once "cfg/conexion.php";

mysqli_query($conexion, "UPDATE persona SET reset_token = NULL, token_expiry = NULL WHERE token_expiry <= NOW()");

$email = "";
$email_err = $success_msg = "";
$step = 1;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST["btn_enviar_codigo"])) {
        $email = trim($_POST["email"]);
        $dominio = substr(strrchr($email, "@"), 1);
        $sin_internet = true;
        $hora_mala = false;

        if ($fp = @fsockopen("www.google.com", 80, $errno, $errstr, 2)) {
            $sin_internet = false;

            fwrite($fp, "HEAD / HTTP/1.1\r\nHost: www.google.com\r\nConnection: Close\r\n\r\n");
            $respuesta = fread($fp, 1024);
            fclose($fp);

            if (preg_match('/Date:\s+(.*)/i', $respuesta, $coincidencias)) {
                $hora_google = strtotime(trim($coincidencias[1]));
                $hora_local = time();

                if (abs($hora_local - $hora_google) > 300) {
                    $hora_mala = true;
                }
            }
        }

        if (empty($email)) {
            $email_err = "Por favor ingrese su correo electrónico.";
        } elseif ($sin_internet) {
            $email_err = "Error de red: El sistema no tiene conexión a internet en este momento.";
        } elseif ($hora_mala) {
            $email_err = "Error de seguridad: La fecha y hora de su sistema están incorrectas. Por favor, actualice el reloj.";
        } elseif (!checkdnsrr($dominio, 'MX')) {
            $email_err = "El dominio del correo no existe o no es válido.";
        } else {
            $sql = "SELECT id, estatus, login_attempts FROM persona WHERE email = ? AND password IS NOT NULL AND password != ''";

            if ($stmt = mysqli_prepare($conexion, $sql)) {
                mysqli_stmt_bind_param($stmt, "s", $email);

                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_store_result($stmt);

                    if (mysqli_stmt_num_rows($stmt) == 1) {
                        mysqli_stmt_bind_result($stmt, $id_usuario, $estatus_actual, $login_attempts);
                        mysqli_stmt_fetch($stmt);

                        if ($estatus_actual == '2' && $login_attempts >= 3) {

                            $codigo = rand(100000, 999999);
                            $expiracion = date("Y-m-d H:i:s", strtotime('+3 minutes'));

                            $sql_token = "UPDATE persona SET reset_token = ?, token_expiry = ? WHERE email = ?";
                            $stmt_t = mysqli_prepare($conexion, $sql_token);
                            mysqli_stmt_bind_param($stmt_t, "sss", $codigo, $expiracion, $email);
                            mysqli_stmt_execute($stmt_t);
                            mysqli_stmt_close($stmt_t);

                            // Enviar Correo
                            $mail = new PHPMailer(true);
                            try {
                                $mail->isSMTP();
                                $mail->Host       = 'smtp.gmail.com';
                                $mail->SMTPAuth   = true;
                                $mail->Username   = 'cpt3sistema@gmail.com';
                                $mail->Password   = 'Jrjjfgomexsyyxqg';
                                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                                $mail->Port       = 587;

                                $mail->setFrom('cpt3sistema@gmail.com', 'Sistema de Seguridad CPT3');
                                $mail->addAddress($email);
                                $mail->isHTML(true);
                                $mail->Subject = 'Codigo de Desbloqueo de Cuenta';
                                $mail->Body    = "Su código de seguridad para desbloquear su cuenta es: <b>$codigo</b>.<br>Expira en 3 minutos.";

                                $mail->send();

                                $_SESSION['email_desbloqueo'] = $email;
                                $success_msg = "Se ha enviado un código de seguridad a su correo.";
                                $step = 2;
                            } catch (Exception $e) {
                                $error_tecnico = $mail->ErrorInfo . " " . $e->getMessage();

                                if (stripos($error_tecnico, 'certificate') !== false || stripos($error_tecnico, 'ssl') !== false || stripos($error_tecnico, 'verify') !== false) {
                                    $email_err = "Error de seguridad. Verifica que la fecha y hora de tu dispositivo estén correctas.";
                                } elseif (stripos($error_tecnico, 'connect') !== false || stripos($error_tecnico, 'host') !== false || stripos($error_tecnico, 'network') !== false) {
                                    $email_err = "No pudimos conectar al servidor. Por favor, verifica tu conexión a internet.";
                                } else {
                                    $email_err = "Ocurrió un problema al enviar el correo. Intenta nuevamente más tarde.";
                                }

                                error_log("Error de envío de correo en CPT3: " . $error_tecnico);
                            }
                        } else {
                            $email_err = "Esta cuenta no se encuentra bloqueada. Puedes iniciar sesión normalmente.";
                        }
                    } else {
                        $email_err = "No se encontró una cuenta válida registrada con ese correo.";
                    }
                }
                mysqli_stmt_close($stmt);
            }
        }
    }

    if (isset($_POST["btn_validar_codigo"])) {
        $email = $_POST["email_hidden"];
        $codigo_ingresado = $_POST["codigo"];

        $sql = "SELECT id FROM persona WHERE email = ? AND reset_token = ? AND token_expiry > NOW() AND login_attempts >= 3";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $email, $codigo_ingresado);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) == 1) {
            mysqli_stmt_bind_result($stmt, $id_usuario);
            mysqli_stmt_fetch($stmt);

            $sql_unlock = "UPDATE persona SET login_attempts = 0, estatus = '2', reset_token = NULL WHERE id = ?";
            $stmt_up = mysqli_prepare($conexion, $sql_unlock);
            mysqli_stmt_bind_param($stmt_up, "i", $id_usuario);

            if (mysqli_stmt_execute($stmt_up)) {

                $mailConf = new PHPMailer(true);
                try {
                    $mailConf->isSMTP();
                    $mailConf->Host = 'smtp.gmail.com';
                    $mailConf->SMTPAuth = true;
                    $mailConf->Username = 'cpt3sistema@gmail.com';
                    $mailConf->Password = 'Jrjjfgomexsyyxqg';
                    $mailConf->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mailConf->Port = 587;

                    $mailConf->setFrom('cpt3sistema@gmail.com', 'Sistema de Seguridad CPT3');
                    $mailConf->addAddress($email);
                    $mailConf->isHTML(true);
                    $mailConf->Subject = 'Cuenta Desbloqueada Exitosamente';
                    $mailConf->Body    = "Su cuenta ha sido desbloqueada de manera exitosa. Ya puede iniciar sesion nuevamente en el sistema.";

                    $mailConf->send();
                } catch (Exception $e) {
                    error_log("Error enviando correo de confirmación: " . $mailConf->ErrorInfo);
                }

                $success_msg = "¡Cuenta desbloqueada con éxito! Ya puede iniciar sesión.";
                $step = 1;
            }
        } else {
            $email_err = "Código incorrecto, expirado o la cuenta no se encuentra bloqueada.";
            $step = 2;
        }
    }
}

$exp_timestamp = 0;
if ($step == 2 && !empty($email)) {
    $sql_exp = "SELECT token_expiry FROM persona WHERE email = ?";
    if ($stmt_exp = mysqli_prepare($conexion, $sql_exp)) {
        mysqli_stmt_bind_param($stmt_exp, "s", $email);
        mysqli_stmt_execute($stmt_exp);
        mysqli_stmt_bind_result($stmt_exp, $fecha_expiracion);
        if (mysqli_stmt_fetch($stmt_exp) && !empty($fecha_expiracion)) {
            $exp_timestamp = strtotime($fecha_expiracion);
        }
        mysqli_stmt_close($stmt_exp);
    }
}

?>