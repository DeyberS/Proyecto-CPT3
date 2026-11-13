<?php
include("cfg/login/verificacion_desbloqueo.php");
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1,">
    <meta name="description" content="">
    <meta name="author" content="Deyber Silva">
    <title>CPT3 - Desbloqueo de Usuario</title>
    <link rel="stylesheet" href="recursos/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="recursos/css/style.css?v=<?php echo filemtime('recursos/css/style.css'); ?>">
    <link rel="stylesheet" href="recursos/css/login_style.css?v=<?php echo filemtime('recursos/css/login_style.css'); ?>">
    <link rel="icon" type="image/x-ico" href="recursos/imagenes/cpt3.ico">
</head>

<body>
    <div id="full_loader">
        <div id="loader"></div>
    </div>

    <div class="login-container text-center">
        <img class="mb-4" src="recursos/imagenes/iconos/pass_b.png" alt="" width="72" height="72" style="filter: invert();">
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
                    <input type="email" name="email" class="form-control" placeholder="ejemplo@correo.com" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
                <button type="submit" name="btn_enviar_codigo" class="btn btn-danger">Enviar Código</button>
            <?php else : ?>
                <p class="text-muted small">Ingrese el código de 6 dígitos enviado a su correo.</p>

                <div id="contador_box" class="alert alert-warning py-2 small mb-3">
                    Tiempo restante: <strong id="timer">--:--</strong>
                </div>

                <input type="hidden" name="email_hidden" value="<?php echo htmlspecialchars($email); ?>">
                <div class="form-group mb-4">
                    <input type="text" id="input_codigo" name="codigo" class="form-control text-center" placeholder="XXXXXX" maxlength="6" style="font-size: 24px; letter-spacing: 5px;" required>
                </div>
                <button type="submit" id="btn_validar" name="btn_validar_codigo" class="btn btn-danger">Verificar y Desbloquear</button>
            <?php endif; ?>
            <br><br>
            <p><a href="index.php" style="color: crimson; text-decoration: none;">Volver al Inicio</a></p>
        </form>
    </div>

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
</body>

</html>