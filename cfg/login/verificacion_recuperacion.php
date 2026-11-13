<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'plugins/PHPMailer/src/Exception.php';
require 'plugins/PHPMailer/src/PHPMailer.php';
require 'plugins/PHPMailer/src/SMTP.php';

require_once "cfg/conexion.php";
mysqli_query($conexion, "UPDATE persona SET reset_token = NULL, token_expiry = NULL WHERE token_expiry <= NOW()");

$email = "";
$codigo_validado = "";
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
            $sql = "SELECT id FROM persona WHERE email = ? AND password IS NOT NULL AND password != '' AND estatus IN (1, 2)";
            if ($stmt = mysqli_prepare($conexion, $sql)) {
                mysqli_stmt_bind_param($stmt, "s", $email);

                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_store_result($stmt);

                    if (mysqli_stmt_num_rows($stmt) == 1) {
                        $codigo = sprintf("%06d", rand(0, 999999));
                        $expiracion = date("Y-m-d H:i:s", strtotime('+3 minutes'));

                        $sql_update = "UPDATE persona SET reset_token = ?, token_expiry = ? WHERE email = ?";
                        if ($stmt_up = mysqli_prepare($conexion, $sql_update)) {
                            mysqli_stmt_bind_param($stmt_up, "sss", $codigo, $expiracion, $email);
                            mysqli_stmt_execute($stmt_up);
                            mysqli_stmt_close($stmt_up);
                        }

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
                            $mail->Subject = 'Codigo de Recuperacion de Contrasena - CPT3';
                            $mail->Body    = "
                                <div style='font-family: Arial; border: 1px solid #ddd; padding: 20px;'>
                                    <h2>Restablecer Contraseña</h2>
                                    <p>Has solicitado restablecer tu contraseña en el sistema CPT3.</p>
                                    <p>Tu código de verificación es:</p>
                                    <h1 style='color: crimson; letter-spacing: 5px; text-align: center;'>$codigo</h1>
                                    <p>Este código expira en 20 minutos.</p>
                                </div>";

                            $mail->send();
                            $success_msg = "Se ha enviado un código de confirmación a su correo.";
                            $step = 2;
                        } catch (Exception $e) {
                            $email_err = "Ocurrió un problema al enviar el correo. Intenta nuevamente.";
                            error_log("Error de envío de correo: " . $mail->ErrorInfo);
                        }
                    } else {
                        $email_err = "No se encontró ninguna cuenta con ese correo.";
                    }
                }
                mysqli_stmt_close($stmt);
            }
        }
    }

    elseif (isset($_POST["btn_validar_codigo"])) {
        $email = trim($_POST["email_hidden"]);
        $codigo_ingresado = trim($_POST["codigo"]);

        if (empty($codigo_ingresado)) {
            $email_err = "Por favor ingrese el código de 6 dígitos.";
            $step = 2;
        } else {
            $sql = "SELECT id FROM persona WHERE email = ? AND reset_token = ? AND token_expiry > NOW()";
            if ($stmt = mysqli_prepare($conexion, $sql)) {
                mysqli_stmt_bind_param($stmt, "ss", $email, $codigo_ingresado);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $success_msg = "Código validado correctamente. Ingrese su nueva contraseña.";
                    $codigo_validado = $codigo_ingresado;
                    $step = 3;
                } else {
                    $email_err = "Código incorrecto o ha expirado.";
                    $step = 2;
                }
                mysqli_stmt_close($stmt);
            }
        }
    }

    elseif (isset($_POST["btn_cambiar_clave"])) {
        $email = trim($_POST["email_hidden"]);
        $codigo_validado = trim($_POST["codigo_hidden"]);
        $nueva_pass = $_POST["nueva_pass"];
        $confirm_pass = $_POST["confirm_pass"];

        if (empty($nueva_pass) || empty($confirm_pass)) {
            $email_err = "Por favor complete las contraseñas.";
            $step = 3;
        } elseif ($nueva_pass !== $confirm_pass) {
            $email_err = "Las contraseñas no coinciden.";
            $step = 3;
        } elseif (strlen($nueva_pass) < 6) {
            $email_err = "La contraseña debe tener al menos 6 caracteres.";
            $step = 3;
        } else {
            $sql = "SELECT id FROM persona WHERE email = ? AND reset_token = ? AND token_expiry > NOW()";
            if ($stmt = mysqli_prepare($conexion, $sql)) {
                mysqli_stmt_bind_param(
                    $stmt,
                    "ss",
                    $email,
                    $codigo_validado
                );
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result(
                        $stmt,
                        $id_usuario
                    );
                    mysqli_stmt_fetch($stmt);

                    $pass_hashed = password_hash($nueva_pass, PASSWORD_DEFAULT);

                    $sql_pass = "UPDATE persona SET password = ?, reset_token = NULL, token_expiry = NULL, login_attempts = 0 WHERE id = ?";
                    if ($stmt_pass = mysqli_prepare($conexion, $sql_pass)) {
                        mysqli_stmt_bind_param($stmt_pass, "si", $pass_hashed, $id_usuario);
                        if (mysqli_stmt_execute($stmt_pass)) {

                            $success_msg = "¡Contraseña actualizada con éxito! Ya puede iniciar sesión.";
                            $step = 4;

                            $mail_aviso = new PHPMailer(true);
                            try {
                                $mail_aviso->isSMTP();
                                $mail_aviso->Host       = 'smtp.gmail.com';
                                $mail_aviso->SMTPAuth   = true;
                                $mail_aviso->Username   = 'cpt3sistema@gmail.com';
                                $mail_aviso->Password   = 'Jrjjfgomexsyyxqg';
                                $mail_aviso->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                                $mail_aviso->Port       = 587;

                                $mail_aviso->setFrom('cpt3sistema@gmail.com', 'Sistema de Seguridad CPT3');
                                $mail_aviso->addAddress($email);

                                $mail_aviso->isHTML(true);
                                $mail_aviso->Subject = 'Alerta de Seguridad: Contrasena Actualizada - CPT3';
                                $mail_aviso->Body    = "
                                    <div style='font-family: Arial; border: 1px solid #ddd; padding: 20px;'>
                                        <h2 style='color: #112;'>Cambio de Contraseña Exitoso</h2>
                                        <p>Hola,</p>
                                        <p>Te informamos que la contraseña de tu cuenta en el sistema CPT3 acaba de ser modificada.</p>
                                        <p>Si fuiste tú, puedes ignorar este mensaje.</p>
                                        <p style='color: crimson; font-weight: bold;'>Si no solicitaste este cambio, por favor contacta al administrador del sistema de inmediato.</p>
                                    </div>";

                                $mail_aviso->send();
                            } catch (Exception $e) {                               
                                error_log("Error al enviar alerta de cambio de clave: " . $mail_aviso->ErrorInfo);
                            }
                        } else {
                            $email_err = "Error al intentar actualizar la contraseña.";
                            $step = 3;
                        }
                        mysqli_stmt_close($stmt_pass);
                    }
                } else {
                    $email_err = "El tiempo ha expirado. Debe solicitar un nuevo código.";
                    $step = 1;
                }
                mysqli_stmt_close($stmt);
            }
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