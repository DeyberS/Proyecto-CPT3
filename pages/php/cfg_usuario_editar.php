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
                      <select name="rol" id="rol" class="form-control" required>
                        <option value="" selected="disabled">--- Seleccione Un Rol ---</option>
                        <?php
                        include("../../cfg/conexion.php");

                        $sql_roles = $conexion->query("SELECT * FROM rol HAVING Id_rol IN (1, 2, 6, 7, 8)");
                        while ($row_rol = $sql_roles->fetch_assoc()) {
                          $selected = ($row_rol['Id_rol'] == $rol_actual) ? 'selected' : '';
                          echo "<option value='" . $row_rol['Id_rol'] . "' " . $selected . ">" . $row_rol['nombre_rol'] . "</option>";
                        }
                        ?>
                      </select>
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

        // =====================================================================
        // 1. VALIDACIÓN AL INTENTAR GUARDAR (FLUJO SEGURO)
        //    Ahora usando el evento 'click' del nuevo botón
        // =====================================================================
        $('#abrirModalGuardar').on('click', function(e) {
          e.preventDefault(); // Prevenimos cualquier acción si el tipo fuera submit

          limpiarErrores();
          var formularioValido = true;

          // 1.1. Verificación de campos obligatorios (*)
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

          // 1.2. Verificación de Contraseñas (Condicional)
          var password = $('#password').val().trim();
          var confirmPassword = $('#confirm_password').val().trim();

          // Detectar si el usuario INTENTA cambiar la contraseña (al llenar al menos uno)
          var cambiandoPassword = (password !== "" || confirmPassword !== "");

          if (cambiandoPassword) {

            // 1.2.1. Validar que ambos campos de contraseña estén llenos
            if (password === "" || confirmPassword === "") {
              $('#password').addClass('input-error');
              $('#confirm_password').addClass('input-error');
              mostrarAviso('❌ Error: Si desea cambiar la contraseña, debe llenar ambos campos. De lo contrario, déjelos vacíos.');
              return;
            }

            // 1.2.2. Validación de Longitud (6 a 16 dígitos)
            if (password.length < 6 || password.length > 16) {
              $('#password').addClass('input-error');
              mostrarAviso('🔒 Error de Contraseña: La longitud debe ser mínimo 6 y máximo 16 dígitos.');
              return;
            }

            // 1.2.3. Verificación de Coincidencia de Contraseñas
            if (password !== confirmPassword) {
              $('#password').addClass('input-error');
              $('#confirm_password').addClass('input-error');
              mostrarAviso('❌ Error: Las contraseñas no coinciden. Por favor, verifíquelas.');
              return;
            }
          }

          // Si todo es válido (campos obligatorios y, opcionalmente, contraseñas), abrimos el modal
          abrirModalGuardar();
        });

        function verificarUsuarioYEnviar() {
          var nombreUsuario = $('#nombre').val();
          // Capturamos el ID del input hidden (asegúrate de que el input tenga name="Id")
          var idUsuario = $('input[name="Id"]').val();

          console.log("Validando usuario:", nombreUsuario, "ID excluido:", idUsuario);

          $.ajax({
            url: 'get/get_verificar_usuario.php',
            type: 'POST',
            data: {
              nombre: nombreUsuario,
              id: idUsuario
            },
            dataType: 'json',
            // Importante: async false puede trabar el navegador, 
            // pero garantiza que el código espere la respuesta.
            success: function(response) {
              if (response.existe) {
                console.log("El usuario ya existe.");
                // Si existe, mostramos modal de aviso y NO hacemos nada más
                $('#avisoModal .modal-body p').text('Error: El nombre de usuario "' + nombreUsuario + '" ya está siendo usado por otra persona.');
                $('#avisoModal').modal('show');
              } else {
                console.log("Usuario disponible o es el mismo. Enviando...");
                // SI NO EXISTE: Quitamos cualquier evento y enviamos manualmente
                $('#formularioUsuario')[0].submit();
              }
            },
            error: function(jqXHR, textStatus, errorThrown) {
              console.log("Error en AJAX:", textStatus, errorThrown);
              // Si falla la red, enviamos para no bloquear el sistema
              $('#formularioUsuario')[0].submit();
            }
          });
        }

        // 1.4. Lógica para el botón 'Guardar' dentro del modal de confirmación
        $('#confirmarGuardadoFinal').off('click').on('click', function(e) {
          e.preventDefault(); // Detiene cualquier acción por defecto
          e.stopPropagation(); // Evita que el evento suba

          $('#modalGuardarUsuario').modal('hide');

          // Llamamos a la validación
          verificarUsuarioYEnviar();

          return false; // Refuerzo para no enviar el form
        });

        // 1.5. Lógica para el botón Regresar (Abre el modal)
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