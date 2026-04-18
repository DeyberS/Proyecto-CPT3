<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Usuarios | Añadir</title>
  <?php
  include('includes/headerNav2.php');
  ?>
  <style>
    /* ---------------------------------------------------------------------- */
    /* ANIMACIONES Y ESTILOS DE MODALES (Solicitados) */
    /* ---------------------------------------------------------------------- */

    /* Animación para el fondo al abrir el modal */
    @keyframes pulse-opacity {
      0% {
        opacity: 0;
      }

      100% {
        opacity: 1;
      }
    }

    /* Animación para el modal de Bootstrap (reemplaza la clase 'fade') */
    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(-50px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes fadeOut {
      from {
        opacity: 1;
        transform: translateY(0);
      }

      to {
        opacity: 0;
        transform: translateY(-50px);
      }
    }

    /* Aplica la animación al modal que se está mostrando */
    .modal.in .modal-dialog,
    #avisoModal,
    #modalGuardarUsuario,
    #modalRegresarUsuario {
      animation: fadeIn 0.4s ease-out;
    }

    /* Aplica la animación de salida cuando el modal tiene la clase de cierre */
    .modal.out .modal-dialog {
      animation: fadeOut 0.4s ease-in;
    }

    /* Estilo para el body cuando un modal está abierto (fondo animado) */
    .modal-open .modal-backdrop {
      opacity: 0.7 !important;
      animation: pulse-opacity 0.3s forwards;
      /* Aplica la animación al backdrop */
    }

    /* ---------------------------------------------------------------------- */
    /* ESTILOS DE VALIDACIÓN Y LAYOUT */
    /* ---------------------------------------------------------------------- */
    .input-error {
      border: 2px solid crimson !important;
      box-shadow: 0 0 5px crimson;
    }

    .modal {
      position: fixed !important;
      z-index: 99999 !important;
    }

    .modal-backdrop {
      z-index: 99998 !important;
      transition: .5s;
    }

    .modal.in {
      display: block;
      /* Asegura que se muestre para la animación */
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
                      <button type="button" class="btn btn-seconday regresar" id="abrirModalRegresar">Regresar</button>
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

    <div class="modal" id="avisoModal" tabindex="-1" role="dialog" aria-labelledby="avisoModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header bg-crimson">
            <h5 class="modal-title" id="avisoModalLabel" style="color: white;">Aviso de Validación</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <p id="avisoTexto"></p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal" id="modalGuardarUsuario" tabindex="-1" role="dialog" aria-labelledby="modalGuardarUsuarioLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #00a65a; color: white;">
            <h5 class="modal-title" id="modalGuardarUsuarioLabel" style="color: white;">Confirmacion de Guardado</h5>
          </div>
          <div class="modal-body">
            <p>¿Está seguro de que desea guardar la información del usuario?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-success" id="confirmarGuardadoFinal">Guardar</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal" id="modalRegresarUsuario" tabindex="-1" role="dialog" aria-labelledby="modalRegresarUsuarioLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header bg-crimson">
            <h5 class="modal-title" id="modalRegresarUsuarioLabel" style="color: white;">Confirmacion de Regreso</h5>
          </div>
          <div class="modal-body">
            <p>Al hacer clic en "Abandonar Formulario", perderá todos los datos no guardados. ¿Desea continuar?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
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
        // =====================================================================
        // FUNCIONES AUXILIARES (EXISTENTES)
        // =====================================================================

        // Función de ayuda para mostrar el modal de aviso
        function mostrarAviso(mensaje) {
          clearTimeout($('#avisoModal').data('timer'));
          $('#avisoTexto').html(mensaje);
          $('#avisoModal').modal('show');
        }

        // Limpia el estado de error de todos los inputs
        function limpiarErrores() {
          $('input, select').removeClass('input-error');
        }

        // Función para abrir el modal de Guardar
        function abrirModalGuardar() {
          clearTimeout($('#modalGuardarUsuario').data('timer'));
          $('#modalGuardarUsuario').modal('show');
        }

        // =====================================================================
        // 2. FUNCIÓN PARA VERIFICAR NOMBRE DE USUARIO (PHP/AJAX)
        // =====================================================================
        // Busca y reemplaza tu función por esta:
        function verificarUsuarioYEnviar() {
          var nombreUsuario = $('#nombre').val();
          // IMPORTANTE: Capturamos el ID del input hidden que ya tienes en el form
          var idUsuario = $('input[name="Id"]').val();

          $.ajax({
            url: 'get/get_verificar_usuario.php',
            type: 'POST',
            data: {
              nombre: nombreUsuario,
              id: idUsuario
            },
            dataType: 'json',
            success: function(response) {
              if (response.existe) {
                // Si el nombre existe en otro ID, mostramos el aviso y NO enviamos
                $('#avisoModal .modal-body p').text('Error: El nombre de usuario "' + nombreUsuario + '" ya está siendo usado por otra persona.');
                $('#avisoModal').modal('show');
              } else {
                // Si está libre o es el propio nombre del usuario actual, enviamos el form
                // Usamos [0].submit() para ignorar bloqueos de JQuery y enviar directo
                $('#formularioUsuario')[0].submit();
              }
            },
            error: function() {
              // En caso de error de red, enviamos para no bloquear al administrador
              $('#formularioUsuario')[0].submit();
            }
          });
        }

        // =====================================================================
        // 1. VALIDACIÓN AL INTENTAR GUARDAR (FLUJO SEGURO)
        // =====================================================================
        $('#formularioUsuario').on('submit', function(e) {
          e.preventDefault();

          limpiarErrores();
          var formularioValido = true;

          // 1.1. Verificación de campos obligatorios vacíos
          $('input[required], select[required]').each(function() {
            var $input = $(this);

            if (($input.is('select') && ($input.val() === null || $input.val() === "")) ||
              (!$input.is('select') && $input.val().trim() === "")) {
              $input.addClass('input-error');
              formularioValido = false;
            }
          });

          if (!formularioValido) {
            mostrarAviso('⚠️ Error: Todos los campos obligatorios (*) deben estar llenos.');
            return;
          }

          // 1.2. Verificación de Contraseñas y LONGITUD 
          var password = $('#password').val();
          var confirmPassword = $('#confirm_password').val();

          // VALIDACIÓN DE LONGITUD DE CONTRASEÑA
          if (password.length < 6 || password.length > 16) {
            $('#password').addClass('input-error');
            mostrarAviso('🔒 Error de Contraseña: La longitud debe ser mínimo 6 y máximo 16 dígitos.');
            return;
          }

          if (confirmPassword.length < 6 || confirmPassword.length > 16) {
            $('#confirm_password').addClass('input-error');
            mostrarAviso('🔒 Error al confirmar la contraseña: La longitud debe ser mínimo 6 y máximo 16 dígitos.');
            return;
          }

          // Verificación de Coincidencia de Contraseñas
          if (password !== confirmPassword) {
            $('#password').addClass('input-error');
            $('#confirm_password').addClass('input-error');
            mostrarAviso('❌ Error: Las contraseñas no coinciden. Por favor, verifíquelas.');
            return;
          }

          abrirModalGuardar();
        });

        // BUSCA ESTA PARTE AL FINAL Y REEMPLÁZALA:
        $('#confirmarGuardadoFinal').off('click').on('click', function(e) {
          $('#modalGuardarUsuario').modal('hide');
          // Llamamos a la validación
          verificarUsuarioYEnviar();
        });

        // 1.5. Lógica para el botón Regresar (Abre el modal)
        $('#abrirModalRegresar').on('click', function() {
          $('#modalRegresarUsuario').modal('show');
        });

        // =====================================================================
        // FIX CLAVE: CERRAR MODALES CON data-dismiss (Para la animación de salida)
        // =====================================================================
        $('.modal').on('click', '[data-dismiss="modal"]', function() {
          var $modal = $(this).closest('.modal');
          $modal.removeClass('in').addClass('out');

          setTimeout(function() {
            $modal.modal('hide');
            $modal.removeClass('out');
          }, 400);
        });

        // =====================================================================
        // LIMPIEZA ADICIONAL PARA MODALES 
        // =====================================================================
        $('.modal').on('hidden.bs.modal', function() {
          if ($('.modal:visible').length) {
            $('body').addClass('modal-open');
          } else {
            $('body').removeClass('modal-open');
          }
          $('.modal-backdrop').remove();
        });
      });
    </script>
    </body>

</html>