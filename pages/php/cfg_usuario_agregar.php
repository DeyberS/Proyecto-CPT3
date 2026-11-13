<?php
include('includes/headerNav2.php');
verificarAcceso('Crear Usuarios');
?>

<head>
  <title>Usuarios | Añadir</title>
</head>

<style>
  .input-error input[type="text"],
  .input-error select .input-error {
    border: 2px solid crimson !important;
    box-shadow: 0 0 5px crimson !important;
    background-color: #fff5f5 !important;
  }
</style>

<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Añadir Usuario
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="#"><i class="fa fa-user"></i>Usuarios</a></li>
      <li class="active"><a href="#"><i class="fa fa-user-plus"></i>Añadir</a></li>
    </ol>
  </section>

  <section class="content">

    <div class="row">
      <div class="col-md-12">
        <div class="nav-tabs-custom">
          <ul class="nav nav-tabs">
            <li class="active"><a href="#tab_1" data-toggle="tab">Datos del Usuario</a></li>
          </ul>
          <div class="tab-content">
            <div class="tab-pane active" id="tab_1">
              <div class="box-body">
                <form action="../../cfg/agregar/agregar_usuarios.php" id="formularioUsuario" class="form-group" method="POST" novalidate>
                  <label class="control-label"></label>
                  <div class="col-sm-4">
                    <p>Nombre de Usuario (*):</p>
                    <input type="text" class="form-control" value="" name="nombre" id="nombre" placeholder="Nombre Del Usuario" minlength="2" maxlength="20" required>
                  </div>
                  <label class="control-label"></label>
                  <div class="col-sm-4">
                    <p>Email (*):</p>
                    <input type="text" class="form-control" value="" name="email" id="email" placeholder="nombreapellido2@gmail.com" minlength="2" maxlength="100" required>
                  </div>
                  <label class="control-label"></label>
                  <div class="col-sm-3">
                    <p>Contraseña (*):</p>
                    <input type="password" class="form-control" value="" name="password" id="password" placeholder="Contraseña De Ingreso" minlength="6" maxlength="16" required>
                  </div>
                  <br><br><br><br>
                  <label class="control-label"></label>
                  <div class="col-sm-4">
                    <p>Confirmar Contraseña (*):</p>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" value="" minlength="6" maxlength="16" required>
                  </div>
                  <label class="control-label"></label>
                  <div class="col-sm-4">
                    <p>Rol (*):</p>
                    <select class="form-control" name="rol" id="rol" required>
                      <option value="" selected="disabled">--- Seleccione un Rol ---</option>
                      <?php
                      include('../../cfg/conexion.php');

                      $sql = $conexion->query("SELECT * FROM rol HAVING Id_rol IN (1, 2, 6, 7, 8, 9)");
                      while ($resultado = $sql->fetch_assoc()) {
                        echo "<option value='" . $resultado['Id_rol'] . "'>" . $resultado['nombre_rol'] . "</option>";
                      }
                      ?>
                    </select>
                  </div>
                  <br><br><br><br><br>
                  <div style="float:right;">
                    <button type="button" class="btn btn-second regresar" id="abrirModalRegresar">Regresar</button>
                    <button type="submit" class="btn btn-success guardar" id="abrirModalGuardar">Guardar</button>
                  </div>
                  <br>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
  </section>

  <div class="modal fade" id="avisoModal" tabindex="-1" role="dialog" aria-labelledby="avisoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h5 class="modal-title" id="avisoModalLabel" style="color: white;">Aviso de Validación</h5>
        </div>
        <div class="modal-body">
          <p id="avisoTexto"></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalGuardarUsuario" tabindex="-1" role="dialog" aria-labelledby="modalGuardarUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-green">
          <h5 class="modal-title" id="modalGuardarUsuarioLabel" style="color: white;">Confirmación de Guardado</h5>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea guardar la información del usuario?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-success" id="confirmarGuardadoFinal">Aceptar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalRegresarUsuario" tabindex="-1" role="dialog" aria-labelledby="modalRegresarUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h5 class="modal-title" id="modalRegresarUsuarioLabel" style="color: white;">Confirmación de Regreso</h5>
        </div>
        <div class="modal-body">
          <p>Al hacer clic en "Abandonar Formulario", perderá todos los datos no guardados. ¿Desea continuar?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <a href="cfg_usuario_listado.php" class="btn btn-danger">Abandonar Formulario</a>
        </div>
      </div>
    </div>
  </div>

  <?php
  include('includes/footer.php');
  ?>

  <script>
    $(document).ready(function() {
      function mostrarAviso(mensaje) {
        clearTimeout($('#avisoModal').data('timer'));
        $('#avisoTexto').html(mensaje);
        $('#avisoModal').modal('show');
      }

      function limpiarErrores() {
        $('input, select').removeClass('input-error');
      }

      async function validarConexionYHora() {
        if (!navigator.onLine) {
          mostrarAviso('⚠️ Error: Adaptador de red desconectado. Verifique su Wi-Fi o cable.');
          return false;
        }

        try {
          const urlSinCache = window.location.href.split('?')[0] + '?_nocache=' + new Date().getTime();

          const response = await fetch(urlSinCache, {
            method: 'HEAD',
            cache: 'no-store'
          });

          const dateHeader = response.headers.get('Date');
          if (dateHeader) {
            const serverTime = new Date(dateHeader).getTime();
            const localTime = new Date().getTime();

            if (Math.abs(serverTime - localTime) > 300000) {
              mostrarAviso('⚠️ Error: La hora de su sistema es incorrecta. Por favor, ajuste el reloj de su equipo para poder guardar.');
              return false;
            }
          } else {
            console.warn("⚠️ Aviso: El servidor no está enviando la cabecera de hora.");
          }

          return true;

        } catch (error) {
          mostrarAviso('⚠️ Error: Sin conexión a internet. Está conectado a una red, pero no tiene salida hacia el servidor.');
          return false;
        }
      }

      function abrirModalGuardar() {
        clearTimeout($('#modalGuardarUsuario').data('timer'));
        $('#modalGuardarUsuario').modal('show');
      }

      function verificarUsuarioYMostrarModal() {
        var nombreUsuario = $('#nombre').val().trim();
        var emailUsuario = $('#email').val().trim();
        var idUsuario = $('input[name="Id"]').length ? $('input[name="Id"]').val() : 0;
        var btnSubmit = $('#abrirModalGuardar');
        var textoOriginal = btnSubmit.text();

        btnSubmit.text('Verificando...').attr('disabled', true);

        $.ajax({
          url: '../../cfg/ajax/verificar_existencia_usuario.php',
          type: 'POST',
          data: {
            nombre: nombreUsuario,
            email: emailUsuario,
            id: idUsuario
          },
          dataType: 'json',
          success: function(response) {
            btnSubmit.text(textoOriginal).attr('disabled', false);

            if (response.existe_nombre && response.existe_email) {
              $('#nombre').addClass('input-error');
              $('#email').addClass('input-error');
              mostrarAviso('⚠️ Error: El nombre de usuario y el correo electrónico ya están en uso.');
            } else if (response.existe_nombre) {
              $('#nombre').addClass('input-error');
              mostrarAviso('⚠️ Error: El nombre de usuario "' + nombreUsuario + '" ya está en uso.');
            } else if (response.existe_email) {
              $('#email').addClass('input-error');
              mostrarAviso('⚠️ Error: El correo electrónico "' + emailUsuario + '" ya está en uso.');
            } else {
              $('#modalGuardarUsuario').modal('show');
            }
          },
          error: function() {
            btnSubmit.text(textoOriginal).attr('disabled', false);
            mostrarAviso('⚠️ Error de Servidor: No se pudo verificar la base de datos.');
          }
        });
      }

      $('#formularioUsuario').on('submit', function(e) {
        e.preventDefault();
      });

      $('#abrirModalGuardar').off('click').on('click', async function(e) {
        e.preventDefault();

        var entornoValido = await validarConexionYHora();
        if (!entornoValido) return;

        limpiarErrores();
        var formularioValido = true;

        $('input[required], select[required]').each(function() {
          var $input = $(this);
          if (($input.is('select') && ($input.val() === null || $input.val() === "")) ||
            (!$input.is('select') && $input.val().trim() === "")) {
            $input.addClass('input-error');
            formularioValido = false;
          }
        });

        if (!formularioValido) {
          mostrarAviso('⚠️ Error: Los campos obligatorios (*) deben estar llenos.');
          return;
        }

        var password = $('#password').val() ? $('#password').val().trim() : "";
        var confirmPassword = $('#confirm_password').val() ? $('#confirm_password').val().trim() : "";
        var cambiandoPassword = (password !== "" || confirmPassword !== "");
        var esNuevoUsuario = !$('input[name="Id"]').length;

        if (esNuevoUsuario || cambiandoPassword) {
          if (password === "" || confirmPassword === "") {
            $('#password').addClass('input-error');
            $('#confirm_password').addClass('input-error');
            mostrarAviso('⚠️ Error: Debe llenar ambos campos de contraseña.');
            return;
          }
          if (password.length < 6 || password.length > 16) {
            $('#password').addClass('input-error');
            mostrarAviso('⚠️ Error de Contraseña: La longitud debe ser entre 6 y 16 dígitos.');
            return;
          }
          if (password !== confirmPassword) {
            $('#password').addClass('input-error');
            $('#confirm_password').addClass('input-error');
            mostrarAviso('⚠️ Error: Las contraseñas no coinciden.');
            return;
          }
        }
        verificarUsuarioYMostrarModal();
      });

      $('#confirmarGuardadoFinal').off('click').on('click', function(e) {
        $('#modalGuardarUsuario').modal('hide');
        $('#formularioUsuario')[0].submit();
      });

      $('#abrirModalRegresar').on('click', function() {
        $('#modalRegresarUsuario').modal('show');
      });
    });
  </script>
  </body>

  </html>