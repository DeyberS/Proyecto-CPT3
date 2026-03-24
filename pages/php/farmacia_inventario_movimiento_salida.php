<?php
include("../../cfg/conexion.php");
$operacion_actual = isset($_GET['op']) ? $_GET['op'] : 'salida';
$id_pres_auto = isset($_GET['id_pres']) ? $_GET['id_pres'] : '';
$id_med_auto  = isset($_GET['id_med']) ? $_GET['id_med'] : '';
$cedula_auto  = isset($_GET['pac']) ? $_GET['pac'] : '';
$es_menor_auto = isset($_GET['menor']) ? $_GET['menor'] : 0;
$origen = isset($_GET['from']) ? $_GET['from'] : 'inventario';
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Inventario | Salida</title>
  <?php include('includes/headerNav2.php'); ?>

  <style>
    /* ANIMACIONES EXACTAS DEL FORMULARIO DE ENTRADA */
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

    .modal.in .modal-dialog,
    #avisoModal,
    #modalSalidaGuardar,
    #modalRegresarInventario {
      animation: fadeIn 0.4s ease-out;
    }

    .modal.out .modal-dialog {
      animation: fadeOut 0.4s ease-out;
    }

    .modal-open .modal-backdrop {
      opacity: 0.7 !important;
      animation: pulse-opacity 0.3s forwards;
    }

    /* ESTILO DE LA X (CLOSE BUTTON) */
    .modal-header .close {
      color: #000;
      filter: alpha(opacity=50);
      opacity: .5;
      text-shadow: 0 1px 0 #fff;
    }

    .modal-header .close:hover {
      opacity: .8;
    }

    .input-error {
      border: 2px solid crimson !important;
      box-shadow: 0 0 5px crimson;
    }

    .bg-crimson {
      background-color: #dc3545 !important;
      color: white !important;
    }

    .bg-success-custom {
      background-color: #00a65a !important;
      color: white !important;
    }

    /* Evitar que el contenido empuje elementos del sidebar */
    .content-wrapper {
      min-height: 100vh !important;
      /* Asegura que el contenedor tenga altura completa */
      overflow-y: auto;
    }

    /* Si el reloj está en el sidebar, aseguramos que el sidebar sea fijo */
    .main-sidebar {
      position: fixed !important;
      height: 100%;
    }

    #detalles_vinculo input[readonly] {
      box-shadow: none;
      cursor: default;
    }

    /* Ajuste para que las secciones dinámicas no afecten el layout global */
    .seccion-enfasis {
      clear: both;
      /* Evita interferencia con floats */
      display: block;
      width: 100%;
    }

    .bg-externo {
      background-color: #fff9eb;
      border: 1px solid #f39c12;
    }

    .text-green-bold {
      color: #00a65a;
      font-weight: bold;
      font-size: 1.2em;
    }

    /* Contenedor de botones interno */
    .form-actions {
      padding: 20px 0;
      border-top: 1px solid #f4f4f4;
      margin-top: 20px;
      text-align: right;
    }
  </style>
</head>

<body class="hold-transition skin-blue sidebar-mini">
  <div class="content-wrapper">
    <section class="content-header">
      <h1>Salida de Medicamentos</h1>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#tab_1" data-toggle="tab">Detalle de La Operación</a></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane active" id="tab_1" style="padding: 10px;">

                <form id="formularioSalida" method="POST" action="../../cfg/movimientos_inventario.php" novalidate autocomplete="off">
                  <input type="hidden" name="op" id="op" value="<?php echo $operacion_actual; ?>">

                  <div class="row">
                    <div class="col-sm-4">
                      <label>Motivo de Salida (*):</label>
                      <select name="observaciones" id="observaciones" class="form-control" required>
                        <option value="">-- Seleccione --</option>
                        <option value="Entrega a Paciente">Entrega a Paciente Interno</option>
                        <option value="Entrega a Representante">Entrega a Representante (Menor)</option>
                        <option value="Récipe Externo">Récipe Externo (Otro Hospital)</option>
                        <option value="Medicamento Vencido">Medicamento Vencido</option>
                        <option value="Desecho/Dañado">Desecho / Dañado</option>
                      </select>
                    </div>

                    <div class="col-sm-8">
                      <label>Seleccione el Medicamento (*):</label>
                      <select id="Id_descripcion_medicamento" name="Id_descripcion_medicamento" class="form-control select2" style="width: 100%;" required>
                        <option value="">--- Buscar Medicamento ---</option>
                        <?php
                        $sql = "SELECT dm.Id, m.nombre_medicamento, t.tipo_presentacion FROM descripcion_medicamento dm 
                                INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento 
                                INNER JOIN presentacion t ON dm.Id_presentacion = t.Id_presentacion 
                                WHERE m.estatus = 1 AND dm.estatus = 1 ORDER BY m.nombre_medicamento ASC";
                        $res = $conexion->query($sql);
                        while ($row = $res->fetch_assoc()) {
                          echo "<option value='{$row['Id']}'>{$row['nombre_medicamento']} ({$row['tipo_presentacion']})</option>";
                        }
                        ?>
                      </select>
                    </div>
                  </div>

                  <div class="row" style="margin-top:15px;">
                    <div class="col-sm-3">
                      <label>Existencia Total:</label>
                      <input type="text" id="existencia_actual" class="form-control" readonly disabled>
                    </div>
                    <div class="col-sm-3">
                      <label>Seleccione Lote (*):</label>
                      <select id="lista_lotes" name="lote" class="form-control" required>
                        <option value="">-- Seleccione --</option>
                      </select>
                    </div>
                    <div class="col-sm-3">
                      <label>Cantidad a Retirar (*):</label>
                      <input type="number" id="cantidad" name="cantidad" class="form-control" required min="1" onkeypress="return isNumberKey(event)">
                    </div>
                    <div class="col-sm-3">
                      <label>Niveles (Mín/Máx):</label>
                      <input type="text" id="stock_info" class="form-control" readonly disabled>
                    </div>
                  </div>

                  <div id="seccion_interna" class="seccion-enfasis" style="display:none;">
                    <h4><i class="fa fa-search"></i> Vincular Receta del Sistema</h4>

                    <div class="row">
                      <div class="col-sm-3">
                        <label>Buscar por:</label>
                        <select id="metodo_busqueda" class="form-control">
                          <option value="cedula">Cédula</option>
                          <option value="nombre">Nombre / Apellido</option>
                        </select>
                      </div>
                      <div class="col-sm-4">
                        <label id="label_busqueda">Ingrese Los Datos:</label>
                        <input type="text" id="input_busqueda_paciente" class="form-control" onkeypress="return validarEntradaDinamica(event)" required>
                      </div>
                      <div class="col-sm-5">
                        <label>Resultados de Receta (*):</label>
                        <select name="id_prescripcion" id="id_prescripcion" class="form-control" required>
                          <option value="">-- Seleccione --</option>
                        </select>
                      </div>
                    </div>

                    <div id="detalles_vinculo" class="row" style="margin-top: 20px; display:none;">
                      <div class="col-sm-6">
                        <div class="well well-sm" style="border-left: 5px solid #00a65a; background: #fff; padding: 15px;">
                          <h5 style="margin-top:0; color:#00a65a; font-weight:bold;"><i class="fa fa-user"></i> DATOS DEL PACIENTE</h5>
                          <input type="text" id="info_paciente_nom" class="form-control" readonly style="border:none; background:transparent; font-weight:bold; box-shadow:none; font-size:1.1em;">
                          <input type="text" id="info_paciente_ced" class="form-control" readonly style="border:none; background:transparent; box-shadow:none; color:#555;">
                        </div>
                      </div>

                      <div id="col_info_rep" class="col-sm-6" style="display:none;">
                        <div class="well well-sm" style="border-left: 5px solid #3c8dbc; background: #f4f8ff; padding: 15px;">
                          <h5 style="margin-top:0; color:#3c8dbc; font-weight:bold;"><i class="fa fa-users"></i> REPRESENTANTE LEGAL</h5>
                          <input type="text" id="info_rep_nom" class="form-control" readonly style="border:none; background:transparent; font-weight:bold; box-shadow:none; font-size:1.1em;">
                          <input type="text" id="info_rep_ced" class="form-control" readonly style="border:none; background:transparent; box-shadow:none; color:#555;">
                        </div>
                      </div>
                    </div>
                  </div>


                  <div id="seccion_externa" class="seccion-enfasis bg-externo" style="display:none; border-top: 3px solid #f39c12; margin-top: 30px; padding: 20px;">
                    <h4 style="margin-bottom: 20px;"><i class="fa fa-external-link"></i> Datos de Récipe Externo</h4>
                    <div class="row">
                      <div class="col-sm-3">
                        <label>Médico Externo (*):</label>
                        <input type="text" name="medico_externo" id="medico_externo" class="form-control" onkeypress="return isTextKey(event)" placeholder="Ej. Dr. Pérez">
                      </div>

                      <div class="col-sm-3">
                        <label>Nombre del Paciente (*):</label>
                        <input type="text" name="paciente_externo_nombre" id="paciente_externo_nombre" class="form-control" placeholder="Nombre completo" required>
                      </div>

                      <div class="col-sm-4">
                        <label>Cédula del Paciente (*):</label>
                        <div class="input-group">
                          <span class="input-group-btn" style="width: 20%;">
                            <select name="tipo_cedula_ext" class="form-control" style="margin-right: 10px;" required>
                              <option value="V-">V-</option>
                              <option value="E-">E-</option>
                              <option value="PN-">PN-</option>
                            </select>
                          </span>
                          <input type="number" name="paciente_externo_cedula" id="paciente_externo_cedula" class="form-control" min="1" max="20" placeholder="Solo números" required>
                        </div>
                      </div>

                      <div class="col-sm-2">
                        <label>N° Récipe (*):</label>
                        <input type="number" name="numero_recipe" id="numero_recipe" class="form-control" placeholder="000" min="1">
                      </div>
                    </div>
                  </div>

                  <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="abrirModalRegresar">Regresar</button>
                    <button type="submit" class="btn btn-success">Guardar</button>
                  </div>
                </form>

              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <div class="modal" id="avisoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h5 class="modal-title" style="color:white;">Aviso de Validación</h5>
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

  <div class="modal" id="modalSalidaGuardar" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-success-custom">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h5 class="modal-title" style="color:white;">Confirmación de Guardado</h5>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea guardar la información para esta salida del inventario?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-success" id="confirmarGuardadoFinal">Guardar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalRegresarInventario" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h5 class="modal-title" style="color:white;">Confirmación de Regreso</h5>
        </div>
        <div class="modal-body">
          <p>Al hacer clic en "Abandonar Formulario", perderá todos los datos no guardados. ¿Desea continuar?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <a href="<?php echo ($origen === 'prescripciones') ? 'farmacia_prescripciones_listado.php' : 'farmacia_inventario_listado.php'; ?>" class="btn btn-danger">Abandonar Formulario</a>
        </div>
      </div>
    </div>
  </div>

  <?php include('includes/footer.php'); ?>

  <script>
    function isNumberKey(evt) {
      var charCode = (evt.which) ? evt.which : evt.keyCode;
      return !(charCode > 31 && (charCode < 48 || charCode > 57));
    }

    function isTextKey(evt) {
      var charCode = (evt.which) ? evt.which : evt.keyCode;
      return ((charCode > 64 && charCode < 91) || (charCode > 96 && charCode < 123) || charCode == 32 || charCode == 241 || charCode == 209);
    }

    function validarEntradaDinamica(evt) {
      return ($('#metodo_busqueda').val() === 'cedula') ? isNumberKey(evt) : isTextKey(evt);
    }

    $(document).ready(function() {
      function cargarLotesMedicamento(id, callback = null) {

        $.ajax({
          url: '../../cfg/ajax/obtener_descripcion_medicamento.php',
          type: 'POST',
          data: {
            id: id
          },
          dataType: 'json',
          success: function(data) {

            $('#existencia_actual').val(data.existencia_actual);
            $('#stock_info').val(data.stock_minimo + ' / ' + data.stock_maximo);

            let $lotes = $('#lista_lotes')
              .empty()
              .append('<option value="">-- Seleccione --</option>');

            data.lotes.forEach(l => {
              $lotes.append(
                `<option value="${l.Id}" data-cant="${l.cantidad_actual}">
          ${l.lote} (Disp: ${l.cantidad_actual})
        </option>`
              );
            });

            if (callback) callback();
          }
        });
      }

      const idPresAuto = "<?php echo $id_pres_auto; ?>";
      const idMedAuto = "<?php echo $id_med_auto; ?>";
      const cedulaAuto = "<?php echo $cedula_auto; ?>";
      const esMenorAuto = "<?php echo $es_menor_auto; ?>";

      // Localiza la línea 245 y reemplaza ese bloque hasta antes de $('#id_prescripcion').on('change'...)
      if (idPresAuto !== "") {

        // 1. DETERMINAR Y FIJAR EL MOTIVO PRIMERO
        if (esMenorAuto == 1 || esMenorAuto === "1") {
          $('#observaciones').val('Entrega a Representante').trigger('change');
        } else {
          $('#observaciones').val('Entrega a Paciente').trigger('change');
        }

        // 2. CARGAR EL MEDICAMENTO
        $('#Id_descripcion_medicamento').val(idMedAuto).trigger('change');

        // 3. ESPERAR CARGA DE LOTES Y LUEGO BUSCAR AL PACIENTE
        cargarLotesMedicamento(idMedAuto, function() {
          // Forzamos la visibilidad de la sección de búsqueda
          $('#seccion_interna').show();

          // Escribimos la cédula y disparamos la búsqueda
          $('#input_busqueda_paciente').val(cedulaAuto).trigger('keyup');

          // 4. INTERVALO PARA SELECCIONAR LA RECETA CUANDO EL AJAX TERMINE
          const esperarPrescripcion = setInterval(function() {
            if ($('#id_prescripcion option').length > 1) {
              clearInterval(esperarPrescripcion);

              // Seleccionamos la receta y disparamos su evento change
              $('#id_prescripcion').val(idPresAuto).trigger('change');

              // FORZAR DESPLIEGUE DE LOS CUADROS DE DATOS (IMPORTANTE)
              $('#detalles_vinculo').stop().slideDown();
              if (esMenorAuto == 1) {
                $('#col_info_rep').show();
              }

              // 5. SELECCIONAR LOTE DISPONIBLE
              $('#lista_lotes option').each(function() {
                if (parseFloat($(this).data('cant')) > 0) {
                  $(this).prop('selected', true).trigger('change');
                  return false;
                }
              });

              $('#input_busqueda_paciente').prop('readonly', true);

              // 2. Para los <select>, readonly no funciona igual, así que bloqueamos clics y cambiamos estilo
              $('#observaciones, #Id_descripcion_medicamento, #metodo_busqueda, #id_prescripcion, #cantidad')
                .css({
                  'pointer-events': 'none',
                  'background-color': '#eee',
                  'cursor': 'not-allowed'
                });
            }
          }, 100); // Un poco más de tiempo para estabilidad
        });
      }

      // 1. ASIGNACIÓN AUTOMÁTICA DE CANTIDAD 1 AL ELEGIR RECETA
      $('#id_prescripcion').on('change', function() {
        if ($(this).val() !== "") {
          $('#cantidad').val(1);
        }
      });

      // 2. ANIMACIÓN DE SALIDA DE MODALES
      $('.modal').on('click', '[data-dismiss="modal"]', function() {
        var $modal = $(this).closest('.modal');
        $modal.removeClass('in').addClass('out');
        setTimeout(function() {
          $modal.modal('hide').removeClass('out');
        }, 400);
      });

      $('#abrirModalRegresar').on('click', function() {
        $('#modalRegresarInventario').modal('show');
      });

      $('#id_prescripcion').on('change', function() {
        const selected = $(this).find('option:selected');
        const motivo = $('#observaciones').val();

        if ($(this).val() !== "" && $(this).val() !== null) {
          // Llenar datos y mostrar
          $('#info_paciente_nom').val("Nombre: " + selected.data('paciente'));
          $('#info_paciente_ced').val("Cédula: " + selected.data('cedula-p'));
          $('#cantidad').val(1);

          $('#detalles_vinculo').slideDown();

          if (motivo === 'Entrega a Representante') {
            $('#info_rep_nom').val("Resp: " + selected.data('representante'));
            $('#info_rep_ced').val("CI Resp: " + selected.data('cedula-r'));
            $('#col_info_rep').show();
          } else {
            $('#col_info_rep').hide();
          }
        } else {
          // Si no hay nada seleccionado, ocultar todo
          $('#detalles_vinculo').slideUp();
          $('#cantidad').val('');
        }
      });

      // 3. LÓGICA DE SECCIONES Y VALIDACIÓN OBLIGATORIA
      $('#observaciones').on('change', function() {
        const val = $(this).val();

        // --- BLOQUE DE LIMPIEZA TOTAL ---
        $('.seccion-enfasis').slideUp(); // Oculta secciones de búsqueda
        $('#detalles_vinculo').hide(); // Oculta los recuadros de datos del paciente/representante
        $('#col_info_rep').hide(); // Asegura que la columna de representante se oculte

        // Limpiar valores de los inputs informativos
        $('#info_paciente_nom, #info_paciente_ced, #info_rep_nom, #info_rep_ced').val('');

        // Reset de campos obligatorios y cantidad
        $('#id_prescripcion, #medico_externo, #paciente_externo, #numero_recipe').prop('required', false);
        $('#cantidad').prop('readonly', false).val('').css('background-color', '#fff');

        // Limpiar busqueda y resultados previos
        $('#input_busqueda_paciente').val('');
        $('#id_prescripcion').html('<option value="">-- Seleccione --</option>');
        // ---------------------------------

        if (val === 'Entrega a Paciente' || val === 'Entrega a Representante') {
          if (val === 'Entrega a Representante') {
            $('#seccion_interna h4').html('<i class="fa fa-users"></i> Buscar por Datos del Representante');
            $('#label_busqueda').text('Ingrese Datos del Representante:'); // <-- Indicación clara
          } else {
            $('#seccion_interna h4').html('<i class="fa fa-user"></i> Vincular Receta del Sistema');
            $('#label_busqueda').text('Ingrese Los Datos del Paciente:'); // <-- Indicación original
          }

          $('#seccion_interna').slideDown();
          $('#id_prescripcion').prop('required', true);
          $('#cantidad').prop('readonly', true).css('background-color', '#eee');

        } else if (val === 'Récipe Externo') {
          $('#seccion_externa').slideDown();
          $('#medico_externo, #paciente_externo, #numero_recipe').prop('required', true);
        }
      });

      // 4. VALIDACIÓN DE FORMULARIO
      $('#formularioSalida').on('submit', function(e) {
        e.preventDefault();
        $('.form-control').removeClass('input-error');
        let error = false;

        // Verifica todos los campos con required (incluyendo los dinámicos)
        $(this).find('[required]:visible').each(function() {
          if (!$(this).val()) {
            $(this).addClass('input-error');
            error = true;
          }
        });

        if (error) {
          $('#avisoTexto').text('⚠️ Por favor complete todos los campos marcados como obligatorios.');
          $('#avisoModal').modal('show');
          return;
        }

        const cant = parseFloat($('#cantidad').val());
        const disp = parseFloat($('#lista_lotes option:selected').data('cant') || 0);
        if (cant > disp) {
          $('#avisoTexto').text('🛑 Error: La cantidad solicitada supera el stock disponible en este lote.');
          $('#avisoModal').modal('show');
          return;
        }

        $('#modalSalidaGuardar').modal('show');
      });

      $('#confirmarGuardadoFinal').on('click', function() {
        $('#formularioSalida').off('submit').submit();
      });

      // AJAX - Obtener Lotes
      $('#Id_descripcion_medicamento').on('change', function() {
        const id = $(this).val();
        if (!id) return;

        cargarLotesMedicamento(id);
      });

      $('#observaciones').on('change', function() {
        const busquedaActual = $('#input_busqueda_paciente').val();
        if (busquedaActual.length > 0) {
          // Disparamos el evento keyup manualmente para actualizar los resultados con el nuevo filtro
          $('#input_busqueda_paciente').trigger('keyup');
        }
      });

      // AJAX - Búsqueda de pacientes/recetas
      $('#input_busqueda_paciente').on('keyup', function() {
        const query = $(this).val();
        const medId = $('#Id_descripcion_medicamento').val();

        // Detectamos si la opción es específicamente la de representante
        const esRepresentante = ($('#observaciones').val() === 'Entrega a Representante');

        if (query.length > 0 && medId) {
          $.ajax({
            url: '../../cfg/ajax/obtener_prescripciones.php',
            type: 'POST',
            data: {
              busqueda: query,
              id_medicamento: medId,
              metodo: $('#metodo_busqueda').val(),
              es_menor: ($('#observaciones').val() === 'Entrega a Representante') ? 1 : 0
            },
            success: function(res) {
              $('#id_prescripcion').html(res);
            }
          });
        }
      });
    });
  </script>
</body>

</html>