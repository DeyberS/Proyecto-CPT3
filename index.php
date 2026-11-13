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
    <title>CPT3 | Iniciar Sesion</title>
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
            </div>
            <div class="login-footer">
                <?php if (!$is_first_admin_setup) : ?>
                    <p>¿Olvido Su Contraseña? <a href="recuperacion_contraseña.php" class="recovery" style="color: cyan;">Recuperar</a>.</p>
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
</body>

<script>
    (function() {
        const loader = document.getElementById('full_loader');

        if (loader) {
            requestAnimationFrame(() => {
                loader.classList.add('is-visible');
            });
        }

        window.addEventListener('load', () => {
            if (!loader) return;
            setTimeout(() => {
                loader.classList.remove('is-visible');
                setTimeout(() => {
                    loader.style.display = 'none';
                }, 400);
            }, 350);
        });

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
            setTimeout(() => {
                window.location.href = href;
            }, 320);
        });

        window.addEventListener('pageshow', (e) => {
            if (e.persisted) {
                document.body.classList.remove('page-leaving');
                if (loader) {
                    loader.classList.remove('is-visible');
                    loader.style.display = 'none';
                }
            }
        });
    })();

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
</script>

</html>