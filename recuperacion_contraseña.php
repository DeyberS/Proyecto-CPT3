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
    <title>CPT | Recuperación de Contraseña</title>
    <link rel="stylesheet" href="recursos/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="recursos/css/style.css?v=<?php echo filemtime('recursos/css/style.css'); ?>">
    <link rel="stylesheet" href="recursos/css/login_style.css?v=<?php echo filemtime('recursos/css/login_style.css'); ?>">
    <link rel="icon" type="image/x-ico" href="recursos/imagenes/cpt3.ico">
</head>

<body class="text-center">
    <div id="full_loader">
        <div id="loader"></div>
    </div>
    <div class="marquee-container">
        <div class="marquee-text">
            ¡Bienvenido al Sistema CPT! - Estás en el módulo de recuperación de contraseña. Ingresa tu correo para recibir un código de seguridad. &nbsp;&nbsp;&nbsp;&nbsp; • &nbsp;&nbsp;&nbsp;&nbsp; ⚠️ AVISO IMPORTANTE: Si no recibes el correo, por favor revisa tu carpeta de Spam o correo no deseado. &nbsp;&nbsp;&nbsp;&nbsp; • &nbsp;&nbsp;&nbsp;&nbsp; Soporte Técnico: cpt3sistema@gmail.com
        </div>
    </div>
    <div class="login-container animate-page-enter">
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
                    <br>
                    <div class="input-group-icon">
                        <span class="input-icon" aria-hidden="true">
                            <img src="recursos/imagenes/iconos/correo.png" alt="">
                        </span>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                </div>
                <div class="form-group mt-4">
                    <button type="submit" name="btn_enviar_codigo" class="btn btn-danger">Enviar Código</button>
                </div>
            <?php endif; ?>

            <?php if ($step == 2) : ?>
                <p class="text-muted small">Revise su bandeja de entrada (o Spam) e ingrese el código.</p>

                <div id="contador_box" class="alert alert-warning py-2 small mb-3">
                    Tiempo restante: <strong id="timer">--:--</strong>
                </div>

                <input type="hidden" name="email_hidden" value="<?php echo htmlspecialchars($email); ?>">

                <!-- El código de 6 dígitos se deja sin icono para que el text-center quede perfecto -->
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
                    <br>
                    <div class="input-group-icon">
                        <span class="input-icon" aria-hidden="true">
                            <img src="recursos/imagenes/iconos/pass_b.png" alt="">
                        </span>
                        <input type="password" name="nueva_pass" id="nueva_pass" class="form-control" required>
                        <button type="button" class="toggle-password" data-target="nueva_pass" aria-label="Mostrar contraseña" title="Mostrar / ocultar contraseña">
                            <img class="eye-open" src="recursos/imagenes/iconos/filled/body/eye@2x.png" alt="Mostrar contraseña">
                            <img class="eye-closed" src="recursos/imagenes/iconos/filled/body/eye_close@2x.svg" alt="Ocultar contraseña">
                        </button>
                    </div>
                </div>

                <div class="form-group text-start mt-4">
                    <label>Confirmar Contraseña</label>
                    <br>
                    <div class="input-group-icon">
                        <span class="input-icon" aria-hidden="true">
                            <img src="recursos/imagenes/iconos/pass_b.png" alt="">
                        </span>
                        <input type="password" name="confirm_pass" id="confirm_pass" class="form-control" required>
                        <button type="button" class="toggle-password" data-target="confirm_pass" aria-label="Mostrar contraseña" title="Mostrar / ocultar contraseña">
                            <img class="eye-open" src="recursos/imagenes/iconos/filled/body/eye@2x.png" alt="Mostrar contraseña">
                            <img class="eye-closed" src="recursos/imagenes/iconos/filled/body/eye_close@2x.svg" alt="Ocultar contraseña">
                        </button>
                    </div>
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

    <div class="login-clock-fixed" aria-label="Fecha y hora actual">
        <span class="login-clock-item">
            <span class="clock-icon" aria-hidden="true">📅</span>
            <span id="fecha-actual"></span>
        </span>
        <span class="login-clock-divider" aria-hidden="true"></span>
        <span class="login-clock-item">
            <span class="clock-icon" aria-hidden="true">🕒</span>
            <span id="reloj-actual"></span>
        </span>
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
    (function() {
        const loader = document.getElementById('full_loader');

        // 1. Mostrar el loader al entrar a la página (evita el flash)
        if (loader) {
            loader.style.display = 'flex';
            void loader.offsetWidth; // Forzar reflow para que la transición funcione
            loader.classList.add('is-visible');
        }

        // 2. Ocultar el loader y mostrar la página suavemente cuando cargue
        window.addEventListener('load', () => {
            if (!loader) return;
            setTimeout(() => {
                loader.classList.remove('is-visible');
                setTimeout(() => {
                    loader.style.display = 'none';
                }, 400); // Espera a que termine la transición de opacidad
            }, 300); // Pequeño delay para que se aprecie la animación
        });

        // 3. Interceptar clics en enlaces para animar la salida
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (!link) return;

            const href = link.getAttribute('href');
            // Ignorar enlaces que no son de navegación
            if (!href || href.startsWith('#') || href.startsWith('http') || href.startsWith('javascript:') || link.target === '_blank') {
                return;
            }

            e.preventDefault();
            document.body.classList.add('page-leaving');

            if (loader) {
                loader.style.display = 'flex';
                requestAnimationFrame(() => loader.classList.add('is-visible'));
            }

            setTimeout(() => {
                window.location.href = href;
            }, 300);
        });

        // 4. Manejar el botón "Atrás" del navegador (BFCache)
        window.addEventListener('pageshow', (e) => {
            if (e.persisted) {
                document.body.classList.remove('page-leaving');
                if (loader) {
                    loader.classList.remove('is-visible');
                    setTimeout(() => {
                        loader.style.display = 'none';
                    }, 400);
                }
            }
        });
    })();
</script>

<script>
    function actualizarRelojLogin() {
        const ahora = new Date();

        const fechaTexto = ahora.toLocaleDateString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });

        const horaTexto = ahora.toLocaleTimeString('es-ES', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        });

        const fechaEl = document.getElementById('fecha-actual');
        const relojEl = document.getElementById('reloj-actual');

        if (fechaEl) fechaEl.textContent = fechaTexto;
        if (relojEl) relojEl.textContent = horaTexto;
    }

    actualizarRelojLogin();
    setInterval(actualizarRelojLogin, 1000);

    /* ============================================================
       Validación de fecha/hora contra el servidor
       ============================================================ */
    (function() {
        const SERVER_TIME_URL = 'cfg/server_time.php';
        const THRESHOLD_MIN = 2;
        const SYNC_INTERVAL_MS = 60000;
        const JUMP_THRESHOLD_MS = 2000;
        const CRITICAL_MIN = 60;
        const CATASTROPHIC_MIN = 60 * 24;

        const fechaEl = document.getElementById('fecha-actual');
        const relojEl = document.getElementById('reloj-actual');
        if (!fechaEl || !relojEl) return;

        const dateItem = fechaEl.closest('.login-clock-item');
        const timeItem = relojEl.closest('.login-clock-item');

        let serverAtSync = null;
        let monoAtSync = null;
        let lastSyncOk = false;
        let lastSyncError = null;
        let everSyncedOk = false;

        let lastSys = Date.now();
        let lastMono = performance.now();

        async function syncServerTime() {
            const t0 = Date.now();
            const mono0 = performance.now();
            try {
                const res = await fetch(
                    SERVER_TIME_URL + '?_=' + Date.now(), {
                        cache: 'no-store',
                        credentials: 'same-origin'
                    }
                );
                const t1 = Date.now();
                if (!res.ok) throw new Error('HTTP ' + res.status);

                const data = await res.json();
                if (!data || typeof data.time !== 'number') {
                    throw new Error('respuesta inválida del servidor');
                }

                const rtt = t1 - t0;
                serverAtSync = data.time + rtt / 2;
                monoAtSync = mono0 + rtt / 2;
                lastSyncOk = true;
                lastSyncError = null;
                everSyncedOk = true;
            } catch (e) {
                lastSyncOk = false;
                lastSyncError = e.message || 'sin conexión';
            }
            evaluate();
        }

        function applyState(el, item, kind, tooltip) {
            if (!el || !item) return;
            el.classList.remove('clock-invalid', 'clock-unknown');

            if (kind === 'invalid') {
                el.classList.add('clock-invalid');
                item.classList.add('has-error-tip');
                item.setAttribute('data-tooltip', tooltip || '');
            } else if (kind === 'unknown') {
                el.classList.add('clock-unknown');
                item.classList.add('has-error-tip');
                item.setAttribute('data-tooltip', tooltip || '');
            } else {
                item.classList.remove('has-error-tip');
                item.removeAttribute('data-tooltip');
            }
        }

        function evaluate() {
            if (!lastSyncOk || serverAtSync === null) {
                let motivo;
                if (!navigator.onLine) {
                    motivo = 'Sin conexión a internet. La hora mostrada es la de tu equipo y puede no ser exacta.';
                } else if (lastSyncError) {
                    motivo = 'No se pudo verificar la hora con el servidor (' + lastSyncError + '). La hora mostrada es la de tu equipo.';
                } else {
                    motivo = 'Verificando la hora con el servidor…';
                }
                applyState(fechaEl, dateItem, 'unknown', motivo);
                applyState(relojEl, timeItem, 'unknown', motivo);
                return;
            }

            const monoElapsed = performance.now() - monoAtSync;
            const serverNow = serverAtSync + monoElapsed;
            const clientNow = Date.now();

            const diffMs = clientNow - serverNow;
            const diffMin = Math.round(diffMs / 60000);
            const absMin = Math.abs(diffMin);

            const clienteDate = new Date(clientNow);
            const serverDate = new Date(serverNow);
            const fechaOk =
                clienteDate.getFullYear() === serverDate.getFullYear() &&
                clienteDate.getMonth() === serverDate.getMonth() &&
                clienteDate.getDate() === serverDate.getDate();

            const horaOk = absMin < THRESHOLD_MIN;

            if (fechaOk) {
                applyState(fechaEl, dateItem, 'ok', null);
            } else {
                applyState(fechaEl, dateItem, 'invalid', 'La fecha de tu equipo no coincide con la del servidor. Se actualizará automáticamente al corregir la hora.');
            }

            if (horaOk) {
                applyState(relojEl, timeItem, 'ok', null);
            } else {
                let tip;
                if (absMin >= CATASTROPHIC_MIN) {
                    tip = 'La hora de tu equipo difiere más de un día respecto al servidor (diferencia: ' + (diffMin > 0 ? '+' : '') + diffMin + ' min). Es muy probable que la pila de la BIOS esté agotada.';
                } else if (absMin >= CRITICAL_MIN) {
                    tip = 'La hora de tu equipo difiere más de 1 hora respecto al servidor (diferencia: ' + (diffMin > 0 ? '+' : '') + diffMin + ' min). Revisa la configuración de fecha/hora.';
                } else {
                    tip = 'La hora de tu equipo no coincide con la del servidor (diferencia: ' + (diffMin > 0 ? '+' : '') + diffMin + ' min).';
                }
                applyState(relojEl, timeItem, 'invalid', tip);
            }
        }

        function detectJump() {
            const nowSys = Date.now();
            const nowMono = performance.now();
            const drift = Math.abs((nowSys - lastSys) - (nowMono - lastMono));

            if (drift > JUMP_THRESHOLD_MS) {
                evaluate();
                syncServerTime();
            } else {
                evaluate();
            }

            lastSys = nowSys;
            lastMono = nowMono;
        }

        syncServerTime();
        setInterval(syncServerTime, SYNC_INTERVAL_MS);
        setInterval(detectJump, 1000);

        window.addEventListener('online', () => syncServerTime());
        window.addEventListener('offline', () => {
            lastSyncOk = false;
            lastSyncError = 'sin conexión';
            evaluate();
        });
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) evaluate();
        });
    })();

    /* ============================================================
       Mostrar / Ocultar Contraseña (Ojo)
       ============================================================ */
    document.querySelectorAll('.toggle-password').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const input = document.getElementById(this.dataset.target);
            if (!input) return;
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            this.classList.toggle('is-visible', show);
            const len = input.value.length;
            input.focus();
            try {
                input.setSelectionRange(len, len);
            } catch (e) {}
        });
    });
</script>

</html>