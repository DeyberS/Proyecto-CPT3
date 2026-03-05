<?php
require_once "cfg/conexion.php";

$token = "";
$nueva_pass_err = $confirm_pass_err = $success_msg = "";
$mostrar_formulario = false;

// 1. VALIDAR EL TOKEN AL CARGAR LA PÁGINA
if(isset($_GET["token"])){
    $token = trim($_GET["token"]);
    $ahora = date("Y-m-d H:i:s");

    // Verificar si el token existe en la tabla persona y no ha expirado
    $sql = "SELECT id FROM persona WHERE reset_token = ? AND token_expiry > ?";
    if($stmt = mysqli_prepare($conexion, $sql)){
        mysqli_stmt_bind_param($stmt, "ss", $param_token, $param_ahora);
        $param_token = $token;
        $param_ahora = $ahora;

        if(mysqli_stmt_execute($stmt)){
            mysqli_stmt_store_result($stmt);
            if(mysqli_stmt_num_rows($stmt) == 1){
                $mostrar_formulario = true;
            } else {
                $nueva_pass_err = "El enlace es inválido o ha expirado. Por favor, solicite uno nuevo.";
            }
        }
        mysqli_stmt_close($stmt);
    }
}

// 2. PROCESAR LA NUEVA CONTRASEÑA
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["token"])){
    $token = $_POST["token"];
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);

    // Validaciones
    if(empty($password)){
        $nueva_pass_err = "Ingrese una nueva contraseña.";
    } elseif(strlen($password) < 6){
        $nueva_pass_err = "Debe tener al menos 6 caracteres.";
    }

    if(empty($confirm_password)){
        $confirm_pass_err = "Confirme la contraseña.";
    } else {
        if($password != $confirm_password){
            $confirm_pass_err = "Las contraseñas no coinciden.";
        }
    }

    // Si no hay errores, actualizar
    if(empty($nueva_pass_err) && empty($confirm_pass_err)){
        // Encriptar la nueva clave (importante: tu login debe usar password_verify)
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Limpiamos el token y la expiración para que no se use de nuevo
        $sql = "UPDATE persona SET password = ?, reset_token = NULL, token_expiry = NULL WHERE reset_token = ?";
        
        if($stmt = mysqli_prepare($conexion, $sql)){
            mysqli_stmt_bind_param($stmt, "ss", $param_password, $param_token);
            $param_password = $hashed_password;
            $param_token = $token;

            if(mysqli_stmt_execute($stmt)){
                $success_msg = "¡Contraseña actualizada! Ya puede iniciar sesión.";
                $mostrar_formulario = false;
            } else {
                $nueva_pass_err = "Error al actualizar. Intente más tarde.";
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $mostrar_formulario = true;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Contraseña | CPT3</title>
    <link rel="icon" type="image/x-ico" href="recursos/imagenes/cpt3.ico">
    <link rel="stylesheet" href="recursos/css/style.css">
    <link rel="stylesheet" href="recursos/bootstrap/css/bootstrap.css">
    <style>
        body {
            background-image: url('recursos/imagenes/fondo_cpt3_6.jpg');
            background-repeat: no-repeat;
            background-size: cover;
            background-attachment: fixed; /* Fondo oscuro igual a tu index */
            color: white;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            background: #112;
            padding: 40px;
            border-radius: 20px;
            backdrop-filter: blur(15px);
            border: 1px solid #112;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
        }
        .form-group {
            position: relative;
            margin-bottom: 30px;
            text-align: left;
        }
        .form-control {
            background: transparent;
            border: none;
            border-bottom: 2px solid #444;
            border-radius: 0;
            color: white;
            padding: 10px 5px;
            box-shadow: none !important;
        }
        .form-control:focus {
            background: transparent;
            color: white;
            border-color: crimson;
        }
        .form-group label {
            position: absolute;
            top: -10px;
            left: 10px;
            background: #112;
            padding: 0 5px;
            font-size: 13px;
            color: crimson;
        }
        .btn-primary {
            background: crimson;
            border: none;
            width: 100%;
            padding: 12px;
            font-weight: bold;
            letter-spacing: 1px;
            transition: 0.3s;
        }
        .btn-primary:hover {
            background: #b30000;
            transform: scale(1.02);
        }
        .alert {
            font-size: 14px;
            border-radius: 10px;
        }
    </style>
</head>
<body class="text-center">
    <div id="full_loader">
        <div id="loader"></div>
    </div>
    <div class="login-container">
        <h2 style="margin-bottom: 10px;">Nueva Contraseña</h2>
        <p style="color: #888; font-size: 14px; margin-bottom: 25px;">Establezca su nueva clave de acceso</p>

        <?php 
            if(!empty($success_msg)){ echo '<div class="alert alert-success">' . $success_msg . '</div>'; }
            if(!empty($nueva_pass_err) && !$mostrar_formulario){ echo '<div class="alert alert-danger">' . $nueva_pass_err . '</div>'; }
        ?>

        <?php if($mostrar_formulario): ?>
        <form action="<?php echo htmlspecialchars($_SERVER["REQUEST_METHOD"] == "POST" ? $_SERVER["PHP_SELF"] : $_SERVER["REQUEST_URI"]); ?>" method="post">
            <input type="hidden" name="token" value="<?php echo $token; ?>">
            
            <div class="form-group">
                <label>Nueva Contraseña</label>
                <input type="password" name="password" class="form-control" required>
                <small class="text-danger"><?php echo $nueva_pass_err; ?></small>
            </div>

            <div class="form-group">
                <label>Confirmar Contraseña</label>
                <input type="password" name="confirm_password" class="form-control" required>
                <small class="text-danger"><?php echo $confirm_pass_err; ?></small>
            </div>

            <button type="submit" class="btn btn-primary">ACTUALIZAR CLAVE</button>
        </form>
        <?php endif; ?>

        <div style="margin-top: 25px;">
            <a href="index.php" style="color: crimson; text-decoration: none; font-size: 14px;">Ir al Inicio</a>
        </div>
    </div>
</body>
<script>
    window.onload = function() {
        const full_loader = document.getElementById('full_loader'); 
        
        if (full_loader) {
            setTimeout(function(){
                full_loader.style.display = 'none';
            },500);
        }
    };
</script>
</html>


