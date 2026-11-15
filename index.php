<?php
include("cfg/login/verificacion_index.php");
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1,">
    <meta name="description" content="">
    <meta name="author" content="Deyber Silva">
    <title>CPT | Iniciar Sesion</title>
    <link rel="stylesheet" href="recursos/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="recursos/css/style.css?v=<?php echo filemtime('recursos/css/style.css'); ?>">
    <link rel="stylesheet" href="recursos/css/login_style.css?v=<?php echo filemtime('recursos/css/login_style.css'); ?>">
    <link rel="icon" type="image/x-ico" href="recursos/imagenes/cpt3.ico">
</head>

<body class="text-center">
    <div id="splash_screen">
        <img src="recursos/imagenes/cpt3.ico" alt="CPT" class="splash-logo">
    </div>
    <div id="full_loader">
        <div id="loader"></div>
    </div>
    <div class="marquee-container">
        <div class="marquee-text">
            ¡Bienvenido al Sistema CPT! - Por favor, ingresa tus credenciales para acceder al panel administrativo del Sistema. &nbsp;&nbsp;&nbsp;&nbsp; • &nbsp;&nbsp;&nbsp;&nbsp; ⚠️ AVISO IMPORTANTE: Si tu cuenta ha sido bloqueada por intentos fallidos, por favor revisa tu correo electrónico para obtener el código de desbloqueo. &nbsp;&nbsp;&nbsp;&nbsp; • &nbsp;&nbsp;&nbsp;&nbsp; Soporte Técnico: cpt3sistema@gmail.com
        </div>
    </div>
    <div class="login-container animate-page-enter">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <img class="mb-4" src="recursos/imagenes/iconos/usuario_medico_logo.png" alt="" width="72" height="72">
            <h1 class="h3 mb-3 fw-normal">¡Bienvenido!</h1>
            <hr>
            <?php
            if (!empty($login_err)) {
                echo '<div class="alert alert-danger" style="font-weight: bold;">' . $login_err . '</div>';
            }
            ?>
            <!-- ============ EMAIL ============ -->
            <div class="form-group">
                <label>Email</label>
                <br>
                <div class="input-group-icon">
                    <span class="input-icon" aria-hidden="true">
                        <img src="recursos/imagenes/iconos/correo.png" alt="">
                    </span>
                    <input type="text" name="email" id="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>">
                </div>
                <span class="invalid-feedback <?php echo (!empty($email_err)) ? 'd-block' : ''; ?>" style="font-size: 10px; color:crimson;">
                    <?php echo $email_err; ?>
                </span>
            </div>

            <!-- ============ CONTRASEÑA ============ -->
            <div class="form-group">
                <label>Contraseña</label>
                <br>
                <div class="input-group-icon">
                    <span class="input-icon" aria-hidden="true">
                        <img src="recursos/imagenes/iconos/pass_b.png" alt="">
                    </span>
                    <input type="password" name="password" id="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                    <button type="button" class="toggle-password" data-target="password" aria-label="Mostrar contraseña" title="Mostrar / ocultar contraseña">
                        <img class="eye-open" src="recursos/imagenes/iconos/filled/body/eye@2x.png" alt="Mostrar contraseña">
                        <img class="eye-closed" src="recursos/imagenes/iconos/filled/body/eye_close@2x.svg" alt="Ocultar contraseña">
                    </button>
                </div>
                <span class="invalid-feedback <?php echo (!empty($password_err)) ? 'd-block' : ''; ?>" style="font-size: 10px; color:crimson;">
                    <?php echo $password_err; ?>
                </span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Ingresar">
            </div>
            <div class="login-footer">
                <?php if (!$is_first_admin_setup) : ?>
                    <p>¿Olvido Su Contraseña? <a href="recuperacion_contraseña.php" class="recovery" style="color: royalblue;">Recuperar</a>.</p>
                    <p>¿Su Usuario Esta Bloqueado? <a href="recuperacion_bloqueo.php" class="recovery" style="color: crimson;">Desbloquear</a>.</p>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <?php if ($is_first_admin_setup) : ?>
        <div id="firstSetupModal" class="modal-overlay-animated" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.85); z-index: 9999; display: flex; justify-content: center; align-items: center;">
            <div class="modal-content-animated" style="background: #112; padding: 40px; border-radius: 12px; color: white; text-align: center; max-width: 450px; border: 1px solid #00ffff; box-shadow: 0 0 20px rgba(0,255,255,0.4);">
                <img src="recursos/imagenes/iconos/usuario2.png" width="60" style="margin-bottom: 15px;">
                <h2 style="color: #00ffff; font-weight: bold;">¡Configuración Inicial!</h2>
                <p style="margin-top: 15px; font-size: 16px; line-height: 1.5;">
                    Al ser esta la primera vez que ingresas al sistema, debes iniciar sesión con tus credenciales por defecto.
                    <br><br>
                    <b>Obligatorio:</b> Cambia tu correo electrónico (Admin@gmail.com) y contraseña inmediatamente al ingresar por motivos de seguridad.
                </p>
                <button onclick="document.getElementById('firstSetupModal').style.display='none'" style="margin-top: 25px; padding: 12px 25px; background: #00ffff; color: #112; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; width: 100%;">Comprendido, iniciar sesión</button>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($mensaje_exito)) : ?>
        <div id="successModal" class="modal-overlay-animated" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.85); z-index: 9999; display: flex; justify-content: center; align-items: center;">
            <div class="modal-content-animated" style="background: #112; padding: 40px; border-radius: 12px; color: white; text-align: center; max-width: 450px; border: 1px solid #00ff88; box-shadow: 0 0 20px rgba(0,255,136,0.4);">
                <div style="font-size: 50px; margin-bottom: 10px;">📧</div>
                <h2 style="color: #00ff88; font-weight: bold;">¡Aviso de Seguridad!</h2>
                <p style="margin-top: 15px; font-size: 16px; line-height: 1.5;">
                    <?php echo $mensaje_exito; ?>
                </p>
                <button onclick="cerrarExito()" style="margin-top: 25px; padding: 12px 25px; background: #00ff88; color: #112; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; width: 100%;">Entendido</button>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($requiere_verificacion) : ?>
        <div id="verificacionModal" class="modal-overlay-animated" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.85); z-index: 9999; display: none; justify-content: center; align-items: center;">
            <div class="modal-content-animated" style="background: #112; padding: 40px; border-radius: 12px; color: white; text-align: center; max-width: 450px; border: 1px solid #00ffff; box-shadow: 0 0 20px rgba(0,255,255,0.4);">
                <h2 style="color: #00ffff; font-weight: bold;">Verificación de Cuenta</h2>
                <p style="margin-top: 15px; font-size: 16px;">Se ha enviado un código de 6 dígitos a: <b><?php echo htmlspecialchars($email_verificacion); ?></b></p>
                <div class="form-group" style="width: 70%; margin: 20px auto;">
                    <input type="text" id="inputCodigo" class="form-control text-center" maxlength="6" placeholder="XXXXXX" style="font-size: 24px; letter-spacing: 10px; background: #223; color: white; border: 1px solid #00ffff;">
                </div>
                <p class="text-danger" style="color: crimson;">Tiempo restante: <b id="temporizador">05:00</b></p>
                <p id="mensajeVerificacion" style="display: none; margin-top: 15px; font-weight: bold;"></p>
                <div style="margin-top: 25px; display: flex; gap: 10px; flex-direction: column;">
                    <button id="btnVerificarCodigo" style="padding: 12px; background: #00ffff; color: #112; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; width: 100%;">Verificar Código</button>
                    <button id="btnReenviarCodigo" style="display: none; padding: 12px; background: #f39c12; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; width: 100%;">Reenviar Código</button>
                </div>
            </div>
        </div>
    <?php endif; ?>

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

<script>
    (function() {
        const splash = document.getElementById('splash_screen');
        const loader = document.getElementById('full_loader');

        // 1. LÓGICA DEL SPLASH (Solo primera vez)
        if (splash) {
            // Verificamos si ya se mostró en esta sesión
            if (!sessionStorage.getItem('splash_mostrado')) {
                // Es la primera vez: Lo mostramos por 2.5 segundos
                sessionStorage.setItem('splash_mostrado', 'true');

                setTimeout(() => {
                    splash.classList.add('fade-out');
                    // Lo quitamos del DOM después de la animación
                    setTimeout(() => splash.style.display = 'none', 800);
                }, 2500); // <-- Aquí controlas cuánto dura el Splash en milisegundos
            } else {
                // Ya se mostró antes: Lo ocultamos de inmediato
                splash.style.display = 'none';
            }
        }

        // 2. LÓGICA DEL LOADER (Transiciones normales)
        if (loader) {
            loader.style.display = 'flex';
            void loader.offsetWidth;
            loader.classList.add('is-visible');
        }

        // Ocultar el loader cuando la página termine de cargar
        window.addEventListener('load', () => {
            if (!loader) return;
            // Tiempo mínimo para que la ruedita gire y se vea fluido (ej. 400ms)
            setTimeout(() => {
                loader.classList.remove('is-visible');
                setTimeout(() => {
                    loader.style.display = 'none';
                }, 400);
            }, 400);
        });

        // 3. Interceptar clics para animación de salida
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (!link) return;

            const href = link.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('http') || href.startsWith('javascript:') || link.target === '_blank') {
                return;
            }

            e.preventDefault();
            document.body.classList.add('page-leaving');

            if (loader) {
                loader.style.display = 'flex';
                requestAnimationFrame(() => loader.classList.add('is-visible'));
            }

            // Esperar a que la animación de salida termine
            setTimeout(() => {
                window.location.href = href;
            }, 300);
        });

        // 4. Manejar el botón "Atrás"
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

    function cerrarExito() {
        document.getElementById('successModal').style.display = 'none';
        <?php if ($requiere_verificacion) : ?>
            document.getElementById('verificacionModal').style.display = 'flex';
            iniciarTemporizador(300);
        <?php endif; ?>
    }

    <?php if ($requiere_verificacion) : ?>
        let intervalo;

        function iniciarTemporizador(duracion) {
            let tiempo = duracion;
            let display = document.getElementById('temporizador');

            if (intervalo) clearInterval(intervalo);

            intervalo = setInterval(function() {
                let minutos = parseInt(tiempo / 60, 10);
                let segundos = parseInt(tiempo % 60, 10);
                minutos = minutos < 10 ? "0" + minutos : minutos;
                segundos = segundos < 10 ? "0" + segundos : segundos;
                display.textContent = minutos + ":" + segundos;

                if (--tiempo < 0) {
                    clearInterval(intervalo);
                    document.getElementById('inputCodigo').disabled = true;
                    let btnVerificar = document.getElementById('btnVerificarCodigo');
                    btnVerificar.disabled = true;
                    btnVerificar.style.display = 'none';

                    document.getElementById('btnReenviarCodigo').style.display = 'block';

                    let msg = document.getElementById('mensajeVerificacion');
                    msg.textContent = 'El tiempo ha expirado. Solicite un nuevo código.';
                    msg.style.color = 'crimson';
                    msg.style.display = 'block';
                }
            }, 1000);
        }

        document.getElementById('btnVerificarCodigo').addEventListener('click', function() {
            let codigo = document.getElementById('inputCodigo').value.trim();
            let email = '<?php echo htmlspecialchars($email_verificacion); ?>';
            let msg = document.getElementById('mensajeVerificacion');
            let btn = this;

            if (codigo.length !== 6) {
                msg.textContent = 'Debe ingresar los 6 dígitos.';
                msg.style.color = 'crimson';
                msg.style.display = 'block';
                return;
            }

            btn.textContent = 'Verificando...';
            btn.disabled = true;

            let formData = new URLSearchParams();
            formData.append('codigo', codigo);
            formData.append('email', email);

            fetch('cfg/verificar_codigo.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: formData.toString()
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        clearInterval(intervalo);
                        msg.textContent = data.message;
                        msg.style.color = '#00ff88';
                        msg.style.display = 'block';
                        setTimeout(() => {
                            document.getElementById('verificacionModal').style.display = 'none';
                            window.location.href = 'index.php';
                        }, 1500);
                    } else {
                        btn.textContent = 'Verificar Código';
                        btn.disabled = false;
                        msg.textContent = data.message;
                        msg.style.color = 'crimson';
                        msg.style.display = 'block';
                    }
                })
                .catch(error => {
                    btn.textContent = 'Verificar Código';
                    btn.disabled = false;
                    msg.textContent = 'Error al conectar con el servidor.';
                    msg.style.color = 'crimson';
                    msg.style.display = 'block';
                });
        });

        document.getElementById('btnReenviarCodigo').addEventListener('click', function() {
            let email = '<?php echo htmlspecialchars($email_verificacion); ?>';
            let msg = document.getElementById('mensajeVerificacion');
            let btn = this;

            btn.textContent = 'Enviando...';
            btn.disabled = true;
            msg.textContent = 'Generando nuevo código...';
            msg.style.color = '#f39c12';
            msg.style.display = 'block';

            let formData = new URLSearchParams();
            formData.append('email', email);

            fetch('cfg/reenviar_codigo.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: formData.toString()
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        btn.textContent = 'Reenviar Código';
                        btn.disabled = false;
                        btn.style.display = 'none';

                        let input = document.getElementById('inputCodigo');
                        input.value = '';
                        input.disabled = false;
                        input.focus();

                        let btnVerificar = document.getElementById('btnVerificarCodigo');
                        btnVerificar.style.display = 'block';
                        btnVerificar.textContent = 'Verificar Código';
                        btnVerificar.disabled = false;

                        msg.textContent = data.message;
                        msg.style.color = '#00ff88';

                        iniciarTemporizador(300); // Reset a 5 mins
                    } else {
                        btn.textContent = 'Reenviar Código';
                        btn.disabled = false;
                        msg.textContent = data.message;
                        msg.style.color = 'crimson';
                    }
                })
                .catch(error => {
                    btn.textContent = 'Reenviar Código';
                    btn.disabled = false;
                    msg.textContent = 'Error al conectar con el servidor.';
                    msg.style.color = 'crimson';
                });
        });
    <?php endif; ?>

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

    /* ============================================================
   Reloj del login — versión vanilla (sin jQuery)
   ============================================================ */
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

    // Pintar inmediatamente y luego cada segundo
    actualizarRelojLogin();
    setInterval(actualizarRelojLogin, 1000);

    (function() {
        // ⚠️ Ajusta esta ruta si tu endpoint está en cfg/ajax/server_time.php
        const SERVER_TIME_URL = 'cfg/server_time.php';

        const THRESHOLD_MIN = 2; // margen tolerado (min)
        const SYNC_INTERVAL_MS = 60000; // re-sync cada 60 s
        const JUMP_THRESHOLD_MS = 2000; // salto > 2 s = reloj cambió
        const CRITICAL_MIN = 60; // > 1 h = grave
        const CATASTROPHIC_MIN = 60 * 24; // > 1 día = BIOS muerta

        const fechaEl = document.getElementById('fecha-actual');
        const relojEl = document.getElementById('reloj-actual');
        if (!fechaEl || !relojEl) return;

        const dateItem = fechaEl.closest('.login-clock-item');
        const timeItem = relojEl.closest('.login-clock-item');

        // Estado del sync
        let serverAtSync = null; // hora del servidor (ms) en el último sync OK
        let monoAtSync = null; // performance.now() en ese mismo instante
        let lastSyncOk = false;
        let lastSyncError = null;
        let everSyncedOk = false;

        // Detección de saltos
        let lastSys = Date.now();
        let lastMono = performance.now();

        /* ---------- Fetch de la hora del servidor ---------- */
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
                const mono1 = performance.now();

                if (!res.ok) throw new Error('HTTP ' + res.status);

                const data = await res.json();
                if (!data || typeof data.time !== 'number') {
                    throw new Error('respuesta inválida del servidor');
                }

                const rtt = t1 - t0;
                serverAtSync = data.time + rtt / 2; // estimación al punto medio del RTT
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

        /* ---------- Aplicar estado visual ---------- */
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

        /* ---------- Evaluar estado actual ---------- */
        function evaluate() {
            /* Caso 1: no hay sync → estado "unknown" (gris itálico, NO rojo) */
            if (!lastSyncOk || serverAtSync === null) {
                let motivo;
                if (!navigator.onLine) {
                    motivo = 'Sin conexión a internet. La hora mostrada es la de tu equipo y puede no ser exacta.';
                } else if (lastSyncError) {
                    motivo = 'No se pudo verificar la hora con el servidor (' + lastSyncError +
                        '). La hora mostrada es la de tu equipo.';
                } else {
                    motivo = 'Verificando la hora con el servidor…';
                }
                applyState(fechaEl, dateItem, 'unknown', motivo);
                applyState(relojEl, timeItem, 'unknown', motivo);
                return;
            }

            /* Caso 2: hay sync → estimar serverNow con reloj MONOTÓNICO */
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

            /* ---- Fecha ---- */
            if (fechaOk) {
                applyState(fechaEl, dateItem, 'ok', null);
            } else {
                applyState(fechaEl, dateItem, 'invalid',
                    'La fecha de tu equipo no coincide con la del servidor. ' +
                    'Puede deberse a la pila de la BIOS o a que el reloj no está sincronizado. ' +
                    'Se actualizará automáticamente al corregir la hora.');
            }

            /* ---- Hora ---- */
            if (horaOk) {
                applyState(relojEl, timeItem, 'ok', null);
            } else {
                let tip;
                if (absMin >= CATASTROPHIC_MIN) {
                    tip = 'La hora de tu equipo difiere más de un día respecto al servidor ' +
                        '(diferencia: ' + (diffMin > 0 ? '+' : '') + diffMin + ' min). ' +
                        'Es muy probable que la pila de la BIOS esté agotada. ' +
                        'Se actualizará automáticamente al corregir la hora.';
                } else if (absMin >= CRITICAL_MIN) {
                    tip = 'La hora de tu equipo difiere más de 1 hora respecto al servidor ' +
                        '(diferencia: ' + (diffMin > 0 ? '+' : '') + diffMin + ' min). ' +
                        'Revisa la configuración de fecha/hora o la pila de la BIOS.';
                } else {
                    tip = 'La hora de tu equipo no coincide con la del servidor ' +
                        '(diferencia: ' + (diffMin > 0 ? '+' : '') + diffMin + ' min). ' +
                        'Se actualizará automáticamente al corregir la hora.';
                }
                applyState(relojEl, timeItem, 'invalid', tip);
            }
        }

        /* ---------- Detector de saltos del reloj ---------- */
        function detectJump() {
            const nowSys = Date.now();
            const nowMono = performance.now();
            const drift = Math.abs((nowSys - lastSys) - (nowMono - lastMono));

            if (drift > JUMP_THRESHOLD_MS) {
                // 1) Re-evaluar YA con los datos actuales → limpia el rojo al instante
                evaluate();
                // 2) En segundo plano, refrescar offset contra el servidor
                syncServerTime();
            } else {
                // Re-evaluar cada segundo (barato) para detectar cruces del umbral
                evaluate();
            }

            lastSys = nowSys;
            lastMono = nowMono;
        }

        /* ---------- Ciclos ---------- */
        syncServerTime(); // sync inicial
        setInterval(syncServerTime, SYNC_INTERVAL_MS); // sync periódico
        setInterval(detectJump, 1000); // chequeo cada segundo

        window.addEventListener('online', () => {
            console.log('[reloj] online → re-sync');
            syncServerTime();
        });

        window.addEventListener('offline', () => {
            lastSyncOk = false;
            lastSyncError = 'sin conexión';
            evaluate();
        });

        // Si el usuario corrige la hora en otra pestaña y vuelve → re-evaluar
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) evaluate();
        });
    })();
</script>

</html>