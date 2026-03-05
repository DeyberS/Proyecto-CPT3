<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Lotes | Añadir</title>
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
    .has-error input[type="date"],
    .has-error #medicamento,
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
      <h1>Añadir Lote</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-users"></i>Lotes</a></li>
        <li class="active"><a href="#"><i class="fa fa-user-plus"></i>Añadir</a></li>
      </ol>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#tab_1" data-toggle="tab">Informacion del Lote</a></li>
            </ul>
            <div class="tab-content" style="padding-bottom:5%;">
              <div class="tab-pane active" id="tab_1">
                <div class="box-body">
                  <form action="../../cfg/agregar/agregar_lote.php" id="formularioLote" class="form-group" method="POST" novalidate>

                    <div class="col-sm-4 form-group" id="group_nombre">
                      <label>Nombre del lote (*):</label>
                      <input type="text" class="form-control" name="nombre_lote" id="nombre_lote" required>
                    </div>

                    <div class="col-sm-4 form-group" id="group_fabricacion">
                      <label>F. Fabricacion (*):</label>
                      <input type="date" class="form-control" id="fecha_fabricacion" name="fecha_fabricacion" required>
                    </div>

                    <div class="col-sm-4 form-group" id="group_vencimiento">
                      <label>F. Vencimiento (*):</label>
                      <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento" required>
                    </div>

                    <br><br><br><br><br>

                    <div class="col-sm-4">
                      <label>Estado del Lote (*):</label>
                      <select name="estado_lote" id="estado_lote" class="form-control" disabled readonly>
                        <option value="Disponible" selected> Disponible</option>
                      </select>
                    </div>

                    <div class="col-sm-4 form-group" id="group_medicamento">
                      <label>Medicamento (*):</label>
                      <select id="medicamento" name="medicamento" class="form-control" required>
                        <option value="">--- Seleccione un Medicamento ---</option>
                        <?php
                        // 2. Cargar Medicamentos
                        include("../../cfg/conexion.php");
                        $sql_medicamentos = "SELECT 
                          dm.Id AS id_desc, 
                          m.nombre_medicamento
                          FROM descripcion_medicamento dm
                          INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
                          WHERE m.estatus = 1 AND dm.estatus = 1
                          ORDER BY m.nombre_medicamento ASC";
                        $resultado_medicamentos = $conexion->query($sql_medicamentos);

                        if ($resultado_medicamentos && $resultado_medicamentos->num_rows > 0) {
                          while ($row_med = $resultado_medicamentos->fetch_assoc()) {
                            // Se usa Id_medicamento como value
                            echo '<option value="' . $row_med['id_desc'] . '">' . htmlspecialchars($row_med['nombre_medicamento']) . '</option>';
                          }
                        }
                        ?>
                      </select>
                    </div>

                    <br>

                    <div style="float:right; margin-top: 2%;">
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
            <p>Al hacer clic en "Abandonar Formulario", perderá todos los datos no guardados. ¿Desea continuar?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <a href="farmacia_lotes_listado.php" class="btn btn-danger">Abandonar Formulario</a>
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
            <p>¿Está seguro de que desea guardar la informacion del nuevo lote?</p>
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
        const formulario = document.getElementById('formularioLote');
        const fechaFabricacionInput = $('#fecha_fabricacion');
        const fechaVencimientoInput = $('#fecha_vencimiento');
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

        function checkFormValidity() {
          let allRequiredFieldsFilled = true;
          const requiredFields = fechaVencimientoInput;

          requiredFields.forEach(field => {
            if (field && field.id === 'fecha_vencimiento') {
              // No bloquea si está deshabilitado
            } else if (!field.value.trim()) {
              allRequiredFieldsFilled = false;
            }
          });
        }

        // --- VALIDACIÓN DE FECHAS (No futuras para fabricación) ---
        const today = new Date().toISOString().split('T')[0];
        fechaFabricacionInput.attr('max', today);

        // --- EVENTOS DE VALIDACIÓN ---
        $(formulario).on('input change', checkFormValidity);

        fechaFabricacionInput.on('change', function() {
          const fabricacionDate = $(this).val();
          if (fabricacionDate) {
            fechaVencimientoInput.prop('disabled', false).attr('min', fabricacionDate);
          } else {
            fechaVencimientoInput.prop('disabled', true).val('').removeAttr('min');
          }
          checkFormValidity();
        });

        fechaVencimientoInput.on('change', function() {
          const fabricacionDate = fechaFabricacionInput.val();
          const selectedDate = $(this).val();

          if (fabricacionDate && selectedDate < fabricacionDate) {
            mostrarAviso('La fecha de vencimiento no puede ser anterior a la de fabricación.');
            $(this).val('');
          } else if (selectedDate && selectedDate <= today) {
            mostrarAviso('La fecha de vencimiento debe ser una fecha futura.');
            $(this).val('');
          }
          checkFormValidity();
        });

        // =====================================================================
        // LÓGICA DE VERIFICACIÓN AJAX (CONEXIÓN A BD REAL)
        // =====================================================================

        function verificarLoteYEnviar() {
          const nombre = $('#nombre_lote').val().trim();
          const btnGuardar = $('#confirmarGuardar');

          // Estado de carga
          const textoOriginal = btnGuardar.text();
          btnGuardar.text('Verificando...').attr('disabled', true);

          $.ajax({
            url: 'get/verificar_existencia_lote.php',
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
                errores_ajax.push(`⚠️ Ya existe un lote con el nombre: ${nombre}`);
                $('#group_nombre').addClass('has-error');
                $('#nombre_lote').addClass('input-error');
              }

              if (errores_ajax.length > 0) {
                mostrarAviso('🛑 Error de Duplicidad:' + '<ul><li>' + errores_ajax.join('</li><li>') + '</li></ul>');
              } else {
                // Si no hay errores, ENVIAR FORMULARIO
                $('#formularioLote').off('submit').submit();
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
        $('#formularioLote').on('submit', function(e) {
          e.preventDefault();
          limpiarErrores();
          let errores = [];

          if ($('#nombre_lote').val().trim() === "") {
            errores.push("El nombre del lote no puede estar vacio.");
            $('#group_nombre').addClass('has-error');
          }
          if ($('#fecha_fabricacion').val().trim() === "") {
            errores.push("La fecha de fabricacion no puede estar vacia.");
            $('#group_fabricacion').addClass('has-error');
          }
          if ($('#fecha_vencimiento').val().trim() === "") {
            errores.push("La fecha de vencimiento no puede estar vacia.");
            $('#group_vencimiento').addClass('has-error');
          }
          if ($('#medicamento').val().trim() === "") {
            errores.push("Debe asignar un medicamento a este lote.");
            $('#group_medicamento').addClass('has-error');
          }

          if (errores.length > 0) {
            mostrarAviso('⚠️ Errores: <ul><li>' + errores.join('</li><li>') + '</li></ul>');
          } else {
            $('#modalGuardar').modal('show');
          }
        });

        $('#confirmarGuardar').on('click', function() {
          $('#modalGuardar').modal('hide');

          verificarLoteYEnviar()
        });

        // --- Aplicar validaciones a campos de solo texto ---
        const campos = [];
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