<?php
// 1. Cargar librerías de PHPMailer (Asegúrate de que la ruta sea correcta)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'plugins/PHPMailer/src/Exception.php';
require 'plugins/PHPMailer/src/PHPMailer.php';
require 'plugins/PHPMailer/src/SMTP.php';

require_once "cfg/conexion.php";

$email = "";
$email_err = $success_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);

    if (empty($email)) {
        $email_err = "Por favor ingrese su correo electrónico.";
    } else {
        // 2. Verificar si el email existe en la tabla persona
        $sql = "SELECT id FROM persona WHERE email = ? AND password IS NOT NULL AND password != ''";
        if ($stmt = mysqli_prepare($conexion, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            $param_email = $email;

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) == 1) {
                    // 3. Generar Token y Expiración (1 hora)
                    $token = bin2hex(random_bytes(32));
                    $expiry = date("Y-m-d H:i:s", strtotime('+1 hour'));

                    // 4. Actualizar la base de datos con el token
                    $sql_update = "UPDATE persona SET reset_token = ?, token_expiry = ? WHERE email = ?";
                    if ($stmt_up = mysqli_prepare($conexion, $sql_update)) {
                        mysqli_stmt_bind_param($stmt_up, "sss", $token, $expiry, $email);
                        mysqli_stmt_execute($stmt_up);
                    }

                    // 5. Enviar el correo con PHPMailer
                    $mail = new PHPMailer(true);
                    try {
                        // Configuración del servidor (Ejemplo con Gmail)
                        $mail->isSMTP();
                        $mail->Host       = 'smtp.gmail.com';
                        $mail->SMTPAuth   = true;
                        $mail->Username   = 'cpt3sistema@gmail.com'; // TU CORREO
                        $mail->Password   = 'Jrjjfgomexsyyxqg'; // TU CLAVE DE APLICACIÓN
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port       = 587;

                        $mail->setFrom('no-reply@cpt3.com', 'Sistema CPT3');
                        $mail->addAddress($email);

                        // Contenido del mensaje
                        $url = "http://" . $_SERVER['HTTP_HOST'] . "/proyecto-cpt3/cambiar_contraseña.php?token=" . $token;
                        $mail->isHTML(true);
                        $mail->Subject = 'Recuperar Contraseña - CPT3';
                        $mail->Body    = "
                            <div style='font-family: Arial; border: 1px solid #ddd; padding: 20px;'>
                                <h2>Solicitud de cambio de contraseña</h2>
                                <p>Has solicitado restablecer tu contraseña en el sistema CPT3.</p>
                                <p>Haz clic en el siguiente botón para continuar (expira en 1 hora):</p>
                                <a href='$url' style='background: crimson; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Restablecer Contraseña</a>
                                <p>Si no solicitaste esto, puedes ignorar este correo.</p>
                            </div>";

                        $mail->send();
                        $success_msg = "Se ha enviado un enlace de recuperación a su correo.";
                    } catch (Exception $e) {
                        $email_err = "Error al enviar el correo: {$mail->ErrorInfo}";
                    }
                } else {
                    $email_err = "No se encontró ninguna cuenta con ese correo.";
                }
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Recuperar Contraseña | CPT3</title>
    <link rel="stylesheet" href="recursos/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="recursos/css/style.css">
    <link rel="icon" type="image/x-ico" href="recursos/imagenes/cpt3.ico">
    <style>
        body {
            background-image: url('recursos/imagenes/fondo_cpt3_4.jpg');
            background-repeat: no-repeat;
            background-size: cover;
            background-attachment: fixed;
            /* Fondo oscuro igual a tu index */
            color: white;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .form-group {
            position: relative;
            margin-bottom: 25px;
        }

        .form-control {
            background: transparent;
            border: none;
            border-bottom: 2px solid #444;
            border-radius: 0;
            color: white;
            padding: 10px 5px;
        }

        .form-control:focus {
            background: transparent;
            color: white;
            border-color: crimson;
            box-shadow: none;
        }

        .form-group label {
            position: absolute;
            top: -10px;
            left: 16px;
            background: #112;
            padding: 0 6px;
            font-size: 14px;
        }

        .btn-primary {
            background: crimson;
            border: none;
            width: 100%;
            padding: 10px;
            font-weight: bold;
        }

        .btn-primary:hover {
            background: darkred;
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

<body class="text-center">
    <div id="full_loader">
        <div id="loader"></div>
    </div>
    <div class="login-container">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <h1 class="h3 mb-3 fw-normal">Recuperar Contraseña</h1>
            <hr>

            <?php
            if (!empty($success_msg)) echo '<div class="alert alert-success" style="font-size: 12px;">' . $success_msg . '</div>';
            if (!empty($email_err)) echo '<div class="alert alert-danger" style="font-size: 12px;">' . $email_err . '</div>';
            ?>

            <div class="form-group">
                <label>Ingrese su Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo $email; ?>" required>
            </div>

            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Enviar Enlace">
            </div>

            <p><a href="index.php" style="color: crimson; text-decoration: none;">Volver al Inicio</a></p>
        </form>
    </div>
</body>
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

</html>