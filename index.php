<?php
// Initialize the session
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'plugins/PHPMailer/src/Exception.php';
require 'plugins/PHPMailer/src/PHPMailer.php';
require 'plugins/PHPMailer/src/SMTP.php';

// Check if the user is already logged in, if yes then redirect him to welcome page
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: inicio.php");
    exit;
}


// Include config file
require_once "cfg/conexion.php";

// Define variables and initialize with empty values
$email = $password = "";
$email_err = $password_err = $login_err = "";

// --- NUEVOS PARÁMETROS DE SEGURIDAD ---
$MAX_ATTEMPTS = 3; // Límite de intentos fallidos.

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check if email is empty
    if (empty(trim($_POST["email"]))) {
        $email_err = "Por favor ingrese su correo electronico.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Check if password is empty
    if (empty(trim($_POST["password"]))) {
        $password_err = "Por favor ingrese su contraseña.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate credentials
    if (empty($email_err) && empty($password_err)) {
        // Prepare a select statement
        // Se añaden login_attempts y last_login_attempt
        $sql = "SELECT r.Id_rol, r.nombre_rol, p.id, p.email, p.password, p.nombre, p.estatus, p.login_attempts, p.last_login_attempt 
        FROM persona p 
        JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona 
        JOIN rol r ON dpr.Id_rol = r.Id_rol WHERE p.email = ? AND p.password IS NOT NULL AND p.password != ''";
        

        if ($stmt = mysqli_prepare($conexion, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_email);

            // Set parameters
            $param_email = $email;

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Store result
                mysqli_stmt_store_result($stmt);

                // Check if email exists, if yes then verify password
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    // Bind result variables (Se añaden las nuevas variables)
                    mysqli_stmt_bind_result($stmt, $id_rol, $nombre_rol, $id, $email, $hashed_password, $nombre, $estatus, $login_attempts, $last_login_attempt);
                    if (mysqli_stmt_fetch($stmt)) {

                        // --- 1. CHEQUEAR SI LA CUENTA ESTÁ BLOQUEADA ---
                        if ($login_attempts >= $MAX_ATTEMPTS) {
                            $login_err = "Su cuenta ha sido bloqueada por exceder el límite de intentos fallidos. Por favor, notifique a soporte tecnico.";
                        } else {
                            // --- 2. VERIFICAR CONTRASEÑA ---
                            if (password_verify($password, $hashed_password)) {

                                // Contraseña correcta. Reiniciar contador de intentos fallidos.
                                $reset_attempts_sql = "UPDATE persona SET estatus = 1, login_attempts = 0 WHERE id = ?";

                                if ($reset_stmt = mysqli_prepare($conexion, $reset_attempts_sql)) {
                                    mysqli_stmt_bind_param($reset_stmt, "i", $id);
                                    mysqli_stmt_execute($reset_stmt);
                                    mysqli_stmt_close($reset_stmt);
                                }

                                // Almacenar datos en variables de sesión (session_start() ya se llamó arriba)
                                $_SESSION["loggedin"] = true;
                                $_SESSION["id"] = $id;
                                $_SESSION["nombre"] = $nombre;
                                $_SESSION["estatus"] = 1;
                                $_SESSION["rol"] = $id_rol;
                                $_SESSION["nombre_rol"] = $nombre_rol; // Asegúrate de haber traído el id_rol en tu SELECT anterior

                                // --- NUEVA LÓGICA DE PERMISOS ---
                                $sql_permisos = "SELECT p.nombre_permiso 
                                FROM permiso p 
                                INNER JOIN rol_permiso rp ON p.Id_permiso = rp.Id_permiso 
                                WHERE rp.Id_rol = ?";

                                if ($stmt_p = $conexion->prepare($sql_permisos)) {
                                    $stmt_p->bind_param("i", $id_rol);
                                    $stmt_p->execute();
                                    $res_p = $stmt_p->get_result();

                                    $lista_permisos = [];
                                    while ($row_p = $res_p->fetch_assoc()) {
                                        $lista_permisos[] = $row_p['nombre_permiso'];
                                    }
                                    // Guardamos el array de nombres de permisos en la sesión
                                    $_SESSION["permisos"] = $lista_permisos;
                                    $stmt_p->close();
                                }

                                // Redirigir al usuario a la página de bienvenida
                                header("location: inicio.php");
                                exit;
                            } else {
                                // Contraseña incorrecta. Incrementar contador de intentos fallidos.
                                $new_attempts = $login_attempts + 1;
                                $restant_attempts = $MAX_ATTEMPTS - $new_attempts;
                                $increment_attempts_sql = "UPDATE persona SET login_attempts = ?, last_login_attempt = NOW() WHERE id = ?";

                                if ($increment_stmt = mysqli_prepare($conexion, $increment_attempts_sql)) {
                                    mysqli_stmt_bind_param($increment_stmt, "ii", $new_attempts, $id);
                                    mysqli_stmt_execute($increment_stmt);
                                    mysqli_stmt_close($increment_stmt);
                                }

                                if ($new_attempts >= $MAX_ATTEMPTS) {
                                    $login_err = "Contraseña inválida. Su cuenta ha sido bloqueada por exceder el límite de intentos fallidos. Por favor, revise su correo para desbloquearla.";

                                    // --- LÓGICA DE ENVÍO DE CORREO POR BLOQUEO ---
                                    $mail = new PHPMailer(true);
                                    try {
                                        $mail->isSMTP();
                                        $mail->Host = 'smtp.gmail.com';
                                        $mail->SMTPAuth = true;
                                        $mail->Username = 'cpt3sistema@gmail.com'; // Tu correo real
                                        $mail->Password = 'Jrjjfgomexsyyxqg'; // Tu clave de aplicación
                                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                                        $mail->Port = 587;

                                        $mail->setFrom('seguridad@cpt3.com', 'Sistema de Seguridad CPT3');
                                        $mail->addAddress($email); // El correo que el usuario ingresó en el login

                                        $mail->isHTML(true);
                                        $mail->Subject = 'AVISO: Cuenta Bloqueada por Seguridad';
                                        $mail->Body    = "
                                            <div style='font-family: sans-serif; border: 1px solid #ddd; padding: 20px; border-radius: 10px;'>
                                                <h2 style='color: crimson;'>Acceso Restringido</h2>
                                                <p>Hola, hemos bloqueado el acceso a tu cuenta debido a que se alcanzó el límite de <b>$MAX_ATTEMPTS intentos fallidos</b>.</p>
                                                <p>Si has sido tú y deseas recuperar el acceso, puedes asesorarte con el administrador o bien, desbloquear tu cuenta haciendo clic en el siguiente botón:</p>
                                                <a href='http://localhost/proyecto-cpt3/recuperacion_bloqueo.php' 
                                                   style='background: crimson; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;'>
                                                   Desbloquear mi cuenta
                                                </a>
                                                <br><br>
                                                <small style='color: #888;'>Si no has intentado iniciar sesión, por favor ignora este mensaje o contacta a soporte.</small>
                                            </div>";

                                        $mail->send();
                                    } catch (Exception $e) {
                                        // Si falla el envío, al menos el usuario ya está bloqueado en la DB
                                    }
                                    // ---------------------------------------------

                                } else {
                                    // Mensaje genérico de error por seguridad.
                                    $login_err = "Correo o Contraseña Inválido, Tiene Solo" . "&nbsp" . $restant_attempts . "&nbsp" . "intentos restantes";
                                }
                            }
                        } // Fin del chequeo de bloqueo

                    }
                } else {
                    // Email no existe, mensaje genérico de error.
                    $login_err = "Correo o Contraseña Inválido.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }

    // Close connection
    mysqli_close($conexion);
}
?>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1,">
    <meta name="description" content="">
    <meta name="author" content="Deyber Silva">
    <title>CPT3 | Iniciar Sesion</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background-image: url('recursos/imagenes/fondo_cpt3_2.jpg');
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

        /* Contenedor principal de la cinta */
        .marquee-container {
            width: 100%;
            background-color: #112; /* Color acorde a tu diseño */
            color: white;
            white-space: nowrap;
            overflow: hidden;
            position: fixed; /* Se queda arriba aunque bajes la página */
            top: 0;
            left: 0;
            z-index: 1000;
            padding: 10px 0;
            font-size: 14px;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        /* Animación del texto */
        .marquee-text {
            display: inline-block;
            padding-left: 100%;
            animation: marquee-animation 25s linear infinite;
        }

        @keyframes marquee-animation {
            0% { transform: translateX(0); }
            100% { transform: translateX(-100%); }
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
            animation: fadeInUp 0.8s ease-out;
        }

        .form-group input:focus {
            border-color: #00ffff; /* Color cian que ya usas */
            box-shadow: 0 0 10px rgba(0, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .form-group label {
            transition: all 0.3s ease;
        }

        .form-group input:focus + label {
            color: #00ffff;
        }

        .alert-danger {
            animation: pulseError 2s infinite;
        }

        @keyframes pulseError {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }
    </style>
    <link rel="stylesheet" href="recursos/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="recursos/css/style.css">
    <link rel="icon" type="image/x-ico" href="recursos/imagenes/cpt3.ico">
</head>

<body class="text-center">
    <div id="full_loader">
        <div id="loader"></div>
    </div>
    <div class="marquee-container">
        <div class="marquee-text">
            ¡Bienvenido al Sistema CPT3! - Por favor, ingresa tus credenciales para acceder al panel administrativo del Sistema. &nbsp;&nbsp;&nbsp;&nbsp; • &nbsp;&nbsp;&nbsp;&nbsp; ⚠️ AVISO IMPORTANTE: Si tu cuenta ha sido bloqueada por intentos fallidos, por favor revisa tu correo electrónico para obtener el código de desbloqueo. &nbsp;&nbsp;&nbsp;&nbsp; • &nbsp;&nbsp;&nbsp;&nbsp; Soporte Técnico: cpt3sistema@gmail.com
        </div>
    </div>
    <div class="login-container">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <img class="mb-4" src="recursos/imagenes/iconos/usuario2.png" alt="" width="72" height="72">
            <h1 class="h3 mb-3 fw-normal">Bienvenid@!</h1>
            <hr>
            <?php
            if (!empty($login_err)) {
                // Se agrega el estilo para el error de bloqueo.
                echo '<div class="alert alert-danger" style="font-weight: bold;">' . $login_err . '</div>';
            }
            ?>
            <div class="form-group">
                <label>Email</label>
                <input type="text" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>">
                <span class="invalid-feedback" style="font-size: 10px; color:crimson;"><?php echo $email_err; ?></span>
            </div>
            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                <span class="invalid-feedback" style="font-size: 10px; color:crimson;"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Ingresar">
            </div class="login-footer">
                <p>¿Olvido Su Contraseña? <a href="recuperacion.php" class="recovery" style="color: cyan;">Recuperar</a>.</p>
                 <p>¿Su Usuario Esta Bloqueado? <a href="recuperacion_bloqueo.php" class="recovery" style="color: crimson;">Desbloquear</a>.</p>
            </div>
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