<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Usuarios | Editar</title>
  <?php
  include('includes/headerNav2.php');
  ?>
  <style>
    /* ---------------------------------------------------------------------- */
    /* ANIMACIONES Y ESTILOS DE MODALES */
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

    /* ---------------------------------------------------------------------- */
    /* NUEVOS ESTILOS PARA EL BOTÓN DE VER CONTRASEÑA */
    /* ---------------------------------------------------------------------- */
    .password-container {
      position: relative;
    }

    .toggle-password {
      position: absolute;
      top: 50%;
      right: 10px;
      transform: translateY(-50%);
      cursor: pointer;
      color: #555;
      z-index: 10;
      padding: 5px;
    }

    /* Estilo del input dentro del contenedor para hacer espacio al icono */
    .password-container input {
      padding-right: 40px;
    }
  </style>
  <div class="content-wrapper">
    <section class="content-header">
      <h1>
        Editar Usuario
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-user"></i>Usuarios</a></li>
        <li class="active"><a href="#"><i class="fa fa-pencil"></i>Editar</a></li>
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
                  <form action="../../cfg/editar/editar_usuario.php" class="form-group" id="formularioUsuario" method="POST" novalidate>
                    <?php
                    // Incluimos la conexión y obtenemos los datos del usuario
                    include("../../cfg/conexion.php");

                    $sql = "SELECT r.Id_rol, p.id, p.email, p.nombre, p.apellido
                    FROM persona p 
                    JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona 
                    JOIN rol r ON dpr.Id_rol = r.Id_rol WHERE id =" . $_GET['Id'];
                    $resultado = $conexion->query($sql);

                    $row = $resultado->fetch_assoc();

                    $id_usuario_actual = $row['id'];
                    $rol_actual = $row['Id_rol'];

                    ?>
                    <input type="hidden" name="Id" id="id_usuario_hidden" value="<?= $id_usuario_actual; ?>">

                    <label class="control-label"></label>
                    <div class="col-sm-4">
                      <p>Nombre de Usuario (*):</p>
                      <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['nombre']); ?>" name="nombre" id="nombre" placeholder="Nombre Del Usuario" minlength="2" maxlength="20" required>
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-4">
                      <p>Email (*):</p>
                      <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['email']); ?>" name="email" id="email" placeholder="Correo Electronico" minlength="2" maxlength="100" required>
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-3">
                      <p>Contraseña (Opcional):</p>
                      <div class="password-container">
                        <input type="password" class="form-control" value="" name="password" id="password" placeholder="Dejar vacío para no cambiar" minlength="6" maxlength="16">
                        <span class="fa fa-eye toggle-password" data-target="password"></span>
                      </div>
                    </div>
                    <br><br><br><br>
                    <label class="control-label"></label>
                    <div class="col-sm-4">
                      <p>Confirmar Contraseña (Opcional):</p>
                      <div class="password-container">
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" value="" minlength="6" maxlength="16">
                        <span class="fa fa-eye toggle-password" data-target="confirm_password"></span>
                      </div>
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-4">
                      <p>Rol (*):</p>
                      <?php
                      // 1. Verificamos si el usuario actual en sesión es Administrador (Id_rol = 1)
                      // Asegúrate de que el nombre de tu variable de sesión sea el correcto ('Id_rol', 'rol', etc.)
                      $es_admin = (isset($_SESSION['rol']) && $_SESSION['rol'] == 1);
                      
                      // 2. Si no es admin, preparamos el atributo disabled
                      $disabled_attr = $es_admin ? '' : 'disabled';
                      ?>
                      
                      <select name="<?php echo $es_admin ? 'rol' : 'rol_deshabilitado'; ?>" id="rol" class="form-control" required <?php echo $disabled_attr; ?>>
                        <option value="" selected="disabled">--- Seleccione Un Rol ---</option>
                        <?php
                        include("../../cfg/conexion.php");

                        $sql_roles = $conexion->query("SELECT * FROM rol HAVING Id_rol IN (1, 2, 6, 7, 8, 9)");
                        while ($row_rol = $sql_roles->fetch_assoc()) {
                          $selected = ($row_rol['Id_rol'] == $rol_actual) ? 'selected' : '';
                          echo "<option value='" . $row_rol['Id_rol'] . "' " . $selected . ">" . $row_rol['nombre_rol'] . "</option>";
                        }
                        ?>
                      </select>

                      <?php 
                      // 3. Si NO es admin y el select está deshabilitado, usamos un input oculto 
                      // para enviar el rol original de vuelta al servidor y no dañar la actualización.
                      if (!$es_admin): 
                      ?>
                        <input type="hidden" name="rol" value="<?php echo $rol_actual; ?>">
                        <small class="text-muted">No tienes permisos para modificar el rol.</small>
                      <?php endif; ?>
                    </div>
                    <br><br><br><br>
                    <div style="float:right;">
                      <button type="button" class="btn btn-secondary regresar" id="abrirModalRegresar">Regresar</button>
                      <button type="button" class="btn btn-success guardar" id="abrirModalGuardar">Guardar</button>
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
            <p>¿Está seguro de que desea actualizar la información del usuario?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-success" id="confirmarGuardadoFinal">Actualizar</button>
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
        // NUEVA FUNCIONALIDAD: MOSTRAR/OCULTAR CONTRASEÑA
        // =====================================================================
        $('.toggle-password').on('click', function() {
          var targetId = $(this).data('target');
          var input = $('#' + targetId);
          var type = input.attr('type') === 'password' ? 'text' : 'password';
          input.attr('type', type);
          // Cambia el icono (fa-eye <-> fa-eye-slash)
          $(this).toggleClass('fa-eye fa-eye-slash');
        });

        // =====================================================================
        // FUNCIONES AUXILIARES
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

        function verificarUsuarioYMostrarModal() {
          var nombreUsuario = $('#nombre').val().trim();
          var emailUsuario = $('#email').val().trim(); // Capturamos el email
          var idUsuario = $('input[name="Id"]').length ? $('input[name="Id"]').val() : 0;
          var btnSubmit = $('#abrirModalGuardar');
          var textoOriginal = btnSubmit.text();
          
          btnSubmit.text('Verificando...').attr('disabled', true);

          $.ajax({
            url: 'get/verificar_existencia_usuario.php',
            type: 'POST',
            data: { 
                nombre: nombreUsuario, 
                email: emailUsuario, // Enviamos el email al servidor
                id: idUsuario 
            },
            dataType: 'json',
            success: function(response) {
              btnSubmit.text(textoOriginal).attr('disabled', false);
              
              // Verificamos las diferentes combinaciones de duplicados
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
                // Si ninguno existe, abrimos el modal de confirmación
                $('#modalGuardarUsuario').modal('show');
              }
            },
            error: function() {
              btnSubmit.text(textoOriginal).attr('disabled', false);
              mostrarAviso('🛑 Error de Servidor: No se pudo verificar la base de datos.');
            }
          });
        }

        // Interceptamos el envío por Enter o botón submit
        $('#formularioUsuario').on('submit', function(e) {
          e.preventDefault();
        });

        // Evento principal del botón guardar
        $('#abrirModalGuardar').off('click').on('click', function(e) {
          e.preventDefault();
          limpiarErrores();
          var formularioValido = true;

          // 1. Verificación de campos obligatorios
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

          // 2. Verificación de Contraseñas
          var password = $('#password').val() ? $('#password').val().trim() : "";
          var confirmPassword = $('#confirm_password').val() ? $('#confirm_password').val().trim() : "";
          var cambiandoPassword = (password !== "" || confirmPassword !== "");
          var esNuevoUsuario = !$('input[name="Id"]').length; // True en agregar, false en editar

          if (esNuevoUsuario || cambiandoPassword) {
            if (password === "" || confirmPassword === "") {
              $('#password').addClass('input-error');
              $('#confirm_password').addClass('input-error');
              mostrarAviso('❌ Error: Debe llenar ambos campos de contraseña.');
              return;
            }
            if (password.length < 6 || password.length > 16) {
              $('#password').addClass('input-error');
              mostrarAviso('🔒 Error de Contraseña: La longitud debe ser entre 6 y 16 dígitos.');
              return;
            }
            if (password !== confirmPassword) {
              $('#password').addClass('input-error');
              $('#confirm_password').addClass('input-error');
              mostrarAviso('❌ Error: Las contraseñas no coinciden.');
              return;
            }
          }

          // 3. Validar existencia de usuario antes del modal
          verificarUsuarioYMostrarModal();
        });

        $('#confirmarGuardadoFinal').off('click').on('click', function(e) {
          $('#modalGuardarUsuario').modal('hide');
          $('#formularioUsuario')[0].submit();
        });

        $('#abrirModalRegresar').on('click', function() {
          $('#modalRegresarUsuario').modal('show');
        });

        // =====================================================================
        // FIX CLAVE: CERRAR MODALES CON data-dismiss (Mantiene la animación)
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

</html>