<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'plugins/PHPMailer/src/Exception.php';
require 'plugins/PHPMailer/src/PHPMailer.php';
require 'plugins/PHPMailer/src/SMTP.php';

require_once "cfg/conexion.php";

$email = "";
$email_err = $success_msg = "";
$step = 1; // 1 para pedir correo, 2 para pedir código

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // PASO 1: ENVIAR CÓDIGO AL CORREO
    if (isset($_POST["btn_enviar_codigo"])) {
        $email = trim($_POST["email"]);

        $sql = "SELECT id FROM persona WHERE email = ? AND password IS NOT NULL AND password != ''";
        if ($stmt = mysqli_prepare($conexion, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) {

                    // Generar código de 6 dígitos
                    $codigo = rand(100000, 999999);
                    $expiracion = date("Y-m-d H:i:s", strtotime('+20 minutes'));

                    // Guardar código en la tabla persona (asumiendo que tienes estos campos)
                    // Si no los tienes, puedes usar el campo 'token' que tenías antes
                    $sql_token = "UPDATE persona SET reset_token = ?, token_expiry = ? WHERE email = ?";
                    $stmt_t = mysqli_prepare($conexion, $sql_token);
                    mysqli_stmt_bind_param($stmt_t, "sss", $codigo, $expiracion, $email);
                    mysqli_stmt_execute($stmt_t);

                    // Enviar Correo
                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com'; // Cambia esto por tu config
                        $mail->SMTPAuth = true;
                        $mail->Username = 'cpt3sistema@gmail.com';
                        $mail->Password = 'Jrjjfgomexsyyxqg';
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = 587;

                        $mail->setFrom('no-reply@tuapp.com', 'Sistema de Seguridad');
                        $mail->addAddress($email);
                        $mail->isHTML(true);
                        $mail->Subject = 'Codigo de Desbloqueo de Cuenta';
                        $mail->Body    = "Su código de seguridad para desbloquear su cuenta es: <b>$codigo</b>.<br>Expira en 20 minutos.";

                        $mail->send();
                        $success_msg = "Se ha enviado un código de seguridad a su correo.";
                        $step = 2; // Pasar al siguiente paso
                    } catch (Exception $e) {
                        $email_err = "Error al enviar el correo: {$mail->ErrorInfo}";
                    }
                } else {
                    $email_err = "No se encontró una cuenta con ese correo.";
                }
            }
        }
    }

    // PASO 2: VALIDAR CÓDIGO Y DESBLOQUEAR
    if (isset($_POST["btn_validar_codigo"])) {
        $email = $_POST["email_hidden"];
        $codigo_ingresado = $_POST["codigo"];

        $sql = "SELECT id FROM persona WHERE email = ? AND reset_token = ? AND token_expiry > NOW()";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $email, $codigo_ingresado);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) == 1) {
            mysqli_stmt_bind_result($stmt, $id_usuario);
            mysqli_stmt_fetch($stmt);

            // DESBLOQUEO FINAL
            $sql_unlock = "UPDATE persona SET login_attempts = 0, estatus = '1', reset_token = NULL WHERE id = ?";
            $stmt_up = mysqli_prepare($conexion, $sql_unlock);
            mysqli_stmt_bind_param($stmt_up, "i", $id_usuario);

            if (mysqli_stmt_execute($stmt_up)) {
                $success_msg = "¡Cuenta desbloqueada con éxito! Ya puede iniciar sesión.";
                $step = 1; // Reiniciar
            }
        } else {
            $email_err = "Código incorrecto o expirado.";
            $step = 2; // Mantener en el paso del código
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Desbloqueo Seguro</title>
    <link rel="stylesheet" href="recursos/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="recursos/css/style.css">
    <link rel="icon" type="image/x-ico" href="recursos/imagenes/cpt3.ico">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background-image: url('recursos/imagenes/fondo_cpt3_5.jpg');
            background-repeat: no-repeat;
            background-size: cover;
            background-attachment: fixed;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .form-group label {
            position: absolute;
            top: -10px;
            left: 16px;
            background: #112;
            padding: 0 6px;
            font-size: 14px;
            color: white;
        }

        .btn-primary {
            border: none;
            width: 100%;
            padding: 10px;
            font-weight: bold;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-container {
            background-color: #112;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            color: white;
            transition: transform 0.3s ease;

            /* --- CÓDIGO PARA QUITAR LO BORROSO --- */
            backface-visibility: hidden;
            transform: translateZ(0);
            -webkit-font-smoothing: subpixel-antialiased;
        }

        .form-group input:focus {
            border-color: #00ffff;
            /* Color cian que ya usas */
            box-shadow: 0 0 10px rgba(0, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .form-group label {
            transition: all 0.3s ease;
        }

        .form-group input:focus+label {
            color: #00ffff;
        }

        .alert-danger {
            animation: pulseError 2s infinite;
        }

        @keyframes pulseError {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.02);
            }

            100% {
                transform: scale(1);
            }
        }
    </style>
</head>

<body>
    <div id="full_loader">
        <div id="loader"></div>
    </div>

    <div class="login-container text-center">
        <h2 class="fw-bold mb-4">Desbloqueo de Usuario</h2>

        <?php
        if (!empty($success_msg)) echo '<div class="alert alert-success small">' . $success_msg . '</div>';
        if (!empty($email_err)) echo '<div class="alert alert-danger small">' . $email_err . '</div>';
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

            <?php if ($step == 1) : ?>
                <p class="text-muted small">Enviaremos un código a su correo para verificar su identidad.</p>
                <div class="form-group mb-4 text-start">
                    <label class="form-label">Correo Electrónico</label>
                    <input type="email" name="email" class="form-control" placeholder="ejemplo@correo.com" required>
                </div>
                <button type="submit" name="btn_enviar_codigo" class="btn btn-primary">Enviar Código</button>
            <?php else : ?>
                <p class="text-muted small">Ingrese el código de 6 dígitos enviado a su correo.</p>
                <input type="hidden" name="email_hidden" value="<?php echo $email; ?>">
                <div class="form-group mb-4">
                    <input type="text" name="codigo" class="form-control text-center" placeholder="000000" maxlength="6" style="font-size: 24px; letter-spacing: 5px;" required>
                </div>
                <button type="submit" name="btn_validar_codigo" class="btn btn-primary">Verificar y Desbloquear</button>
            <?php endif; ?>
            <br><br>
            <p><a href="index.php" style="color: crimson; text-decoration: none;">Volver al Inicio</a></p>
        </form>
    </div>

    <script>
        window.onload = function() {
            const full_loader = document.getElementById('full_loader');
            if (full_loader) {
                full_loader.style.transition = 'opacity 0.5s ease';
                full_loader.style.opacity = '0';
                setTimeout(function() {
                    full_loader.style.display = 'none';
                }, 500);
            }
        };
    </script>
</body>

</html>