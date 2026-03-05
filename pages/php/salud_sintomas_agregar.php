<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Sintomas | Añadir</title>
  <?php
  include('includes/headerNav2.php');
  ?>
  <style>
    /* ---------------------------------------------------------------------- */
    /* ANIMACIONES Y ESTILOS DE MODALES */
    /* ---------------------------------------------------------------------- */
    @keyframes pulse-opacity {
      0% {
        opacity: 0;
      }

      100% {
        opacity: 1;
      }
    }

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

    .modal.in .modal-dialog, #avisoModal, #modalGuardar {
      animation: fadeIn 0.4s ease-out;
    }

    .modal.out .modal-dialog {
      animation: fadeOut 0.4s ease-in;
    }

    .modal-open .modal-backdrop {
      opacity: 0.7 !important;
      animation: pulse-opacity 0.3s forwards;
    }

    /* ESTILOS DE VALIDACIÓN */
    .has-error input[type="text"],
    .has-error select,
    .input-error {
      border: 2px solid crimson !important;
      box-shadow: 0 0 5px crimson;
    }

    #display_sintomas_seleccionados.input-error {
      border: 2px solid crimson !important;
      box-shadow: 0 0 5px crimson;
    }

    /* Modales por encima */
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
    }
  </style>
</head>

<body>

  <div class="content-wrapper">
    <section class="content-header">
      <h1>Añadir Sintoma</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-users"></i>Sintomas</a></li>
        <li class="active"><a href="#"><i class="fa fa-user-plus"></i>Añadir</a></li>
      </ol>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#tab_1" data-toggle="tab">Informacion del Sintoma</a></li>
            </ul>
            <div class="tab-content" style="height:170px;">
              <div class="tab-pane active" id="tab_1">
                <div class="box-body">
                  <form action="../../cfg/agregar/agregar_sintoma.php" id="formularioSintoma" class="form-group" method="POST" novalidate>

                    <div class="col-sm-4 form-group" id="group_nombre">
                      <p>Nombre del sintoma (*):</p>
                      <input type="text" class="form-control" name="nombre_sintoma" id="nombre_sintoma" required>
                    </div>

                    <br><br><br><br><br><br>

                    <div style="float:right; margin-top: 0%;">
                      <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#modalRegresar">Regresar</button>
                      <button type="submit" class="btn btn-success" id="btnGuardar">Guardar</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <?php include('includes/footer.php'); ?>
          </div>
        </div>
      </div>
    </section>

    <div class="modal" id="avisoModal" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header bg-crimson" style="color: white;">
            <h5 class="modal-title">Aviso de Validación</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          </div>
          <div class="modal-body">
            <p id="avisoTexto"></p>
          </div>
          <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button></div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="modalRegresar" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header bg-crimson" style="color: white;">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Confirmación de Regreso</h4>
          </div>
          <div class="modal-body">
          <p>Al hacer clic en "Abandonar Formulario", perderá todos los datos no guardados. ¿Desea continuar?</p>          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <a href="salud_sintomas_listado.php" class="btn btn-danger">Abandonar Formulario</a>
          </div>
        </div>
      </div>
    </div>

    <div class="modal" id="modalGuardar" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #00a65a; color: white;">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Confirmación de Guardado</h4>
          </div>
          <div class="modal-body">
            <p>¿Está seguro de que desea guardar la nueva sintoma?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-success" id="confirmarGuardar">Guardar</button>
          </div>
        </div>
      </div>
    </div>

    <script>
      $(document).ready(function() {
        // =====================================================================
        // FUNCIONES DE VISUALIZACIÓN
        // =====================================================================

        function mostrarAviso(mensaje) {
          $('#avisoTexto').html(mensaje);
          $('#avisoModal').modal('show');
        }

        function limpiarErrores() {
          $('input, select').removeClass('input-error');
          $('.form-group').removeClass('has-error');
        }

        // Función que bloquea números (solo texto)
        function bloquearNumeros(e) {
          const teclasPermitidas = ["Backspace", "Tab", "ArrowLeft", "ArrowRight", "Delete", "Shift"];
          if (teclasPermitidas.includes(e.key)) return;
          if (e.key >= "0" && e.key <= "9") {
            e.preventDefault();
          }
        }
        // Función que limpia números pegados (solo texto)
        function limpiarNumeros(e) {
          e.target.value = e.target.value.replace(/[0-9]/g, "");
        }

        // =====================================================================
        // LÓGICA DE VERIFICACIÓN AJAX (CONEXIÓN A BD REAL)
        // =====================================================================
        function verificarSintomaYEnviar() {
          const nombre = $('#nombre_sintoma').val().trim();
          const btnGuardar = $('#confirmarGuardar');

          // Estado de carga
          const textoOriginal = btnGuardar.text();
          btnGuardar.text('Verificando...').attr('disabled', true);

          $.ajax({
            url: 'get/verificar_existencia_sintoma.php',
            method: 'POST',
            dataType: 'json',
            data: {
              nombre: nombre
            },
            success: function(response) {
              let errores_ajax = [];
              limpiarErrores();
              btnGuardar.text(textoOriginal).attr('disabled', false);

              if (response.existe_nombre) {
                errores_ajax.push(`⚠️ Ya existe un sintoma con el nombre: ${nombre}`);
                $('#group_nombre').addClass('has-error');
                $('#nombre_sintoma').addClass('input-error');
              }

              if (errores_ajax.length > 0) {
                mostrarAviso('🛑 Error de Duplicidad:' + '<ul><li>' + errores_ajax.join('</li><li>') + '</li></ul>');
              } else {
                // Si no hay errores, ENVIAR FORMULARIO
                $('#formularioSintoma').off('submit').submit();
              }
            },
            error: function(xhr, status, error) {
              btnGuardar.text(textoOriginal).attr('disabled', false);
              // Fallback visual en caso de error de red (opcional) o mostrar alerta
              mostrarAviso('🛑 Error de Servidor: No se pudo verificar la base de datos. <br>Detalle: ' + error);
            }
          });
        }

        // 3. ENVÍO DEL FORMULARIO
        $('#formularioSintoma').on('submit', function(e) {
          e.preventDefault();
          limpiarErrores();
          let errores = [];

          if ($('#nombre_sintoma').val().trim() === "") {
            errores.push("Falta el nombre del sintoma.");
            $('#group_nombre').addClass('has-error');
          }
          if (errores.length > 0) {
            mostrarAviso('⚠️ Errores: <ul><li>' + errores.join('</li><li>') + '</li></ul>');
          } else {
            $('#modalGuardar').modal('show');
          }
        });

        $('#confirmarGuardar').on('click', function() {
          $('#modalGuardar').modal('hide');

          verificarSintomaYEnviar() 
        });

        // --- Aplicar validaciones a campos de solo texto ---
        const campos = [document.getElementById("nombre_sintoma")];
        campos.forEach(campo => {
          if (campo) {
            campo.addEventListener("keydown", bloquearNumeros);
            campo.addEventListener("input", limpiarNumeros);
          }
        });

        // FIX DE MODALES (Cierre suave)
        $('.modal').on('click', '[data-dismiss="modal"]', function(e) {
          e.stopPropagation();
          var $modal = $(this).closest('.modal');
          if ($modal.hasClass('in')) {
            $modal.removeClass('in').addClass('out');
            setTimeout(function() {
              $modal.modal('hide');
              $modal.removeClass('out');
            }, 400);
          } else {
            $modal.modal('hide');
          }
        });

        $('.modal').on('hidden.bs.modal', function() {
          if (!$('.modal.in').length) {
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();
          } else {
            $('body').addClass('modal-open');
          }
        });
      });
    </script>
</body>
</html>