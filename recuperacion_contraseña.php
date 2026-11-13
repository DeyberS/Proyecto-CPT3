<?php
include("cfg/login/verificacion_recuperacion.php");
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1,">
    <meta name="description" content="">
    <meta name="author" content="Deyber Silva">
    <title>CPT3 | Recuperacion de Contraseña</title>
    <link rel="stylesheet" href="recursos/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="recursos/css/style.css?v=<?php echo filemtime('recursos/css/style.css'); ?>">
    <link rel="stylesheet" href="recursos/css/login_style.css?v=<?php echo filemtime('recursos/css/login_style.css'); ?>">
    <link rel="icon" type="image/x-ico" href="recursos/imagenes/cpt3.ico">
</head>

<body class="text-center">
    <div id="full_loader">
        <div id="loader"></div>
    </div>
    <div class="login-container">
        <img class="mb-4" src="recursos/imagenes/iconos/usuario.png" alt="" width="72" height="72">
        <h1 class="h3 mb-3 fw-normal">Recuperar Contraseña</h1>
        <hr>

        <?php
        if (!empty($success_msg)) echo '<div class="alert alert-success" style="font-size: 13px;">' . $success_msg . '</div>';
        if (!empty($email_err)) echo '<div class="alert alert-danger" style="font-size: 13px;">' . $email_err . '</div>';
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <?php if ($step == 1) : ?>
                <p class="text-muted small">Ingrese su correo para recibir un código de seguridad.</p>
                <div class="form-group text-start">
                    <label>Ingrese su Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
                <div class="form-group">
                    <button type="submit" name="btn_enviar_codigo" class="btn btn-danger">Enviar Código</button>
                </div>
            <?php endif; ?>

            <?php if ($step == 2) : ?>
                <p class="text-muted small">Revise su bandeja de entrada (o Spam) e ingrese el código.</p>

                <div id="contador_box" class="alert alert-warning py-2 small mb-3">
                    Tiempo restante: <strong id="timer">--:--</strong>
                </div>

                <input type="hidden" name="email_hidden" value="<?php echo htmlspecialchars($email); ?>">
                <div class="form-group">
                    <input type="text" id="input_codigo" name="codigo" class="form-control text-center" placeholder="XXXXXX" maxlength="6" style="font-size: 24px; letter-spacing: 10px;" required>
                </div>
                <div class="form-group">
                    <button type="submit" id="btn_validar" name="btn_validar_codigo" class="btn btn-danger">Validar Código</button>
                </div>
            <?php endif; ?>

            <?php if ($step == 3) : ?>
                <input type="hidden" name="email_hidden" value="<?php echo htmlspecialchars($email); ?>">
                <input type="hidden" name="codigo_hidden" value="<?php echo htmlspecialchars($codigo_validado); ?>">

                <div class="form-group text-start mt-4">
                    <label>Nueva Contraseña</label>
                    <input type="password" name="nueva_pass" class="form-control" required>
                </div>
                <div class="form-group text-start mt-4">
                    <label>Confirmar Contraseña</label>
                    <input type="password" name="confirm_pass" class="form-control" required>
                </div>
                <div class="form-group mt-4">
                    <button type="submit" name="btn_cambiar_clave" class="btn btn-danger">Guardar Contraseña</button>
                </div>
            <?php endif; ?>

            <?php if ($step == 4) : ?>
                <div class="form-group mt-3">
                    <a href="index.php" class="btn btn-danger">Ir al Login</a>
                </div>
            <?php endif; ?>

            <?php if ($step != 4) : ?>
                <p class="mt-3"><a href="index.php" style="color: crimson; text-decoration: none;">Cancelar y Volver al Inicio</a></p>
            <?php endif; ?>

        </form>
    </div>
</body>

<?php if ($step == 2 && !empty($exp_timestamp)) : ?>
    <script>
        const expiryTime = <?php echo $exp_timestamp; ?> * 1000;
        const timerElement = document.getElementById('timer');
        const inputCodigo = document.getElementById('input_codigo');
        const btnValidar = document.getElementById('btn_validar');
        const contadorBox = document.getElementById('contador_box');

        function updateTimer() {
            const now = new Date().getTime();
            const distance = expiryTime - now;

            if (distance <= 0) {
                clearInterval(timerInterval);
                timerElement.innerText = "00:00";
                contadorBox.className = "alert alert-danger py-2 small mb-3";
                contadorBox.innerHTML = "<strong>El código ha expirado.</strong><br><br>" +
                    "<input type='hidden' name='email' value='<?php echo htmlspecialchars($email); ?>'>" +
                    "<button type='submit' name='btn_enviar_codigo' class='btn btn-success btn-sm w-100'>Solicitar nuevo código</button>";

                if (inputCodigo) inputCodigo.disabled = true;
                if (btnValidar) btnValidar.disabled = true;
                return;
            }

            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            timerElement.innerText =
                (minutes < 10 ? "0" + minutes : minutes) + ":" +
                (seconds < 10 ? "0" + seconds : seconds);
        }

        updateTimer();
        const timerInterval = setInterval(updateTimer, 1000);
    </script>
<?php endif; ?>

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