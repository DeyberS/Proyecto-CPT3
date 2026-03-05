<?php
include("../../cfg/conexion.php");

if (!isset($_GET['id'])) {
  die("ID no recibido");
}

$id_salida = intval($_GET['id']);
$operacion_actual = "editar_salida";

/* ==============================================
  1. CARGAR DATOS DE CABECERA Y DETALLE
============================================== */
// Consulta analizando la cadena: Detalle -> Prescripción -> Consulta -> Persona
$qCab = mysqli_query($conexion, "
    SELECT 
        di.*, 
        p.nombre, 
        p.apellido, 
        p.cedula,
        pm.Id as id_prescripcion_vinculada
    FROM detalle_inventario di
    LEFT JOIN prescripcion_medicamentos pm ON di.Id_prescripcion = pm.Id
    LEFT JOIN consulta c ON pm.Id_consulta = c.Id_consulta
    LEFT JOIN persona p ON c.Id_paciente = p.id
    WHERE di.Id_detalle_inventario = $id_salida
");
$cabecera = mysqli_fetch_assoc($qCab);

// Formateamos la cadena para el input de búsqueda
$identificador_paciente_db = "";
if ($cabecera && !empty($cabecera['cedula'])) {
  $identificador_paciente_db = $cabecera['cedula'] . " - " . $cabecera['nombre'] . " " . $cabecera['apellido'];
}

if (!$cabecera) die("Salida no encontrada");

$qDet = mysqli_query($conexion, "SELECT * FROM medicamentos_detalle_inventario WHERE Id_detalle_inventario = $id_salida LIMIT 1");
$detalle = mysqli_fetch_assoc($qDet);

// Variables para control de carga en JS
$id_prescripcion_db = $cabecera['Id_prescripcion'] ?? '';
$observacion_db = $cabecera['observaciones'] ?? '';
$id_lote_db = $detalle['Id_lote'] ?? '';

// Estructura para datos externos (se llenará si aplica)
$datos_externo = [
  'medico' => '',
  'paciente' => '',
  'cedula' => '',
  'receta' => ''
];

// Si la observación contiene el formato de Récipe Externo, lo desglosamos
if (strpos($observacion_db, 'Récipe Externo |') !== false) {
  preg_match('/Médico: (.*?) \|/', $observacion_db, $matchMedico);
  preg_match('/Paciente: (.*?) \|/', $observacion_db, $matchPaciente);
  preg_match('/Cedula: (.*?) \|/', $observacion_db, $matchCedula);
  preg_match('/Receta Ext: (.*)$/', $observacion_db, $matchReceta);

  $datos_externo['medico'] = $matchMedico[1] ?? '';
  $datos_externo['paciente'] = $matchPaciente[1] ?? '';
  $datos_externo['cedula'] = $matchCedula[1] ?? '';
  $datos_externo['receta'] = $matchReceta[1] ?? '';
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Inventario | Editar Salida</title>
  <?php include('includes/headerNav2.php'); ?>

  <style>
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

    .modal.in .modal-dialog,
    #avisoModal,
    #modalSalidaGuardar,
    #modalRegresarInventario {
      animation: fadeIn 0.4s ease-out;
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

    .form-actions {
      padding: 20px 0;
      border-top: 1px solid #f4f4f4;
      margin-top: 20px;
      text-align: right;
    }

    .bg-externo {
      background-color: #fff9eb;
      border: 1px solid #f39c12;
      padding: 15px;
      border-radius: 5px;
    }
  </style>
</head>

<body class="hold-transition skin-blue sidebar-mini">
  <div class="content-wrapper">
    <section class="content-header">
      <h1>Editar Movimiento de Salida</h1>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#tab_1" data-toggle="tab">Datos de la Salida</a></li>
            </ul>

            <div class="tab-content">
              <div class="tab-pane active" id="tab_1" style="padding:10px;">
                <form id="formularioSalida" method="POST" action="../../cfg/movimientos_inventario.php" novalidate autocomplete="off">
                  <input type="hidden" name="op" value="<?= $operacion_actual ?>">
                  <input type="hidden" name="id_salida" value="<?= $id_salida ?>">

                  <div class="row">
                    <div class="col-sm-4">
                      <label>Motivo de Salida (*):</label>
                      <select name="observaciones" id="observaciones" class="form-control" required>
                        <option value="">-- Seleccione --</option>
                        <option value="Entrega a Paciente">Entrega a Paciente</option>
                        <option value="Entrega a Representante">Entrega a Representante</option>
                        <option value="Récipe Externo">Récipe Externo</option>
                        <option value="Medicamento Vencido">Medicamento Vencido</option>
                        <option value="Desecho/Dañado">Desecho/Dañado</option>
                      </select>
                    </div>

                    <div class="col-sm-8">
                      <label>Seleccione el Medicamento (*):</label>
                      <select id="Id_descripcion_medicamento" name="Id_descripcion_medicamento" class="form-control select2" style="width:100%" required>
                        <?php
                        $sqlMeds = "SELECT dm.Id, m.nombre_medicamento, p.tipo_presentacion FROM descripcion_medicamento dm 
                                                            INNER JOIN medicamento m ON dm.Id_medicamento=m.Id_medicamento 
                                                            INNER JOIN presentacion p ON dm.Id_presentacion=p.Id_presentacion WHERE dm.estatus=1";
                        $resMeds = $conexion->query($sqlMeds);
                        while ($m = $resMeds->fetch_assoc()) {
                          $sel = ($detalle['Id_descripcion_medicamento'] == $m['Id']) ? "selected" : "";
                          echo "<option $sel value='{$m['Id']}'>{$m['nombre_medicamento']} ({$m['tipo_presentacion']})</option>";
                        }
                        ?>
                      </select>
                    </div>
                  </div>

                  <div class="row" style="margin-top:15px;">
                    <div class="col-sm-3">
                      <label>Existencia Actual:</label>
                      <input type="text" id="existencia_actual" class="form-control" readonly disabled>
                    </div>
                    <div class="col-sm-3">
                      <label>Lote Seleccionado (*):</label>
                      <select id="lista_lotes" name="lote" class="form-control" required></select>
                    </div>
                    <div class="col-sm-3">
                      <label>Cantidad a Retirar (*):</label>
                      <input type="number" id="cantidad" name="cantidad" class="form-control" value="<?= $detalle['cantidad'] ?>" required>
                    </div>
                    <div class="col-sm-3">
                      <label>Niveles (Mín/Máx):</label>
                      <input type="text" id="stock_info" class="form-control" readonly disabled>
                    </div>
                  </div>

                  <div id="seccion_interna" style="display:none; margin-top:20px; border-top: 1px solid #f4f4f4; padding-top:15px;">
                    <h4 class="text-green"><i class="fa fa-user"></i> Datos de la Entrega</h4>
                    <div class="row">
                      <div class="col-sm-3">
                        <label>Buscar por:</label>
                        <select id="metodo_busqueda" class="form-control">
                          <option value="cedula">Cédula</option>
                          <option value="nombre">Nombre</option>
                        </select>
                      </div>
                      <div class="col-sm-4">
                        <label>Dato a buscar:</label>
                        <input type="text" id="input_busqueda_paciente" class="form-control" placeholder="Escriba para buscar...">
                      </div>
                      <div class="col-sm-5">
                        <label>Prescripción Vinculada (*):</label>
                        <select name="id_prescripcion" id="id_prescripcion" class="form-control"></select>
                      </div>
                    </div>
                  </div>

                  <div id="seccion_externa" class="bg-externo" style="display:none; margin-top:20px;">
                    <h4 class="text-yellow"><i class="fa fa-external-link"></i> Datos de Récipe Externo</h4>
                    <div class="row">
                      <div class="col-sm-3">
                        <label>Médico Externo:</label>
                        <input type="text" name="medico_externo" id="medico_externo" class="form-control" value="<?= $datos_externo['medico'] ?>">
                      </div>
                      <div class="col-sm-3">
                        <label>Paciente:</label>
                        <input type="text" name="paciente_externo_nombre" id="paciente_externo_nombre" class="form-control" value="<?= $datos_externo['paciente'] ?>">
                      </div>
                      <div class="col-sm-3">
                        <label>Cédula:</label>
                        <input type="text" name="paciente_externo_cedula" id="paciente_externo_cedula" class="form-control" value="<?= $datos_externo['cedula'] ?>">
                      </div>
                      <div class="col-sm-3">
                        <label>N° Récipe:</label>
                        <input type="text" name="numero_recipe" id="numero_recipe" class="form-control" value="<?= $datos_externo['receta'] ?>">
                      </div>
                    </div>
                  </div>

                  <div class="form-actions">
                    <button type="button" class="btn btn-second" id="abrirModalRegresar">Cancelar</button>
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
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h4 class="modal-title" style="color:white;">Aviso</h4>
        </div>
        <div class="modal-body" id="avisoTexto"></div>
        <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button></div>
      </div>
    </div>
  </div>

  <?php include('includes/footer.php'); ?>

  <script>
    $(document).ready(function() {

      // --- 1. FUNCIÓN PARA CARGAR LOTES ---
      function cargarLotes(idMed, lotePrecargado = null) {
        if (!idMed) return;
        $.ajax({
          url: '../../cfg/ajax/obtener_descripcion_medicamento.php',
          type: 'POST',
          data: {
            id: idMed
          },
          dataType: 'json',
          success: function(data) {
            $('#existencia_actual').val(data.existencia_actual);
            $('#stock_info').val(data.stock_minimo + ' / ' + data.stock_maximo);
            let $lotes = $('#lista_lotes').empty().append('<option value="">-- Seleccione --</option>');
            data.lotes.forEach(l => {
              let selected = (lotePrecargado == l.Id) ? 'selected' : '';
              $lotes.append('<option value="' + l.Id + '" ' + selected + '>' + l.lote + ' (Disp: ' + l.cantidad_actual + ')</option>');
            });
          }
        });
      }

      // --- 2. FUNCIÓN PARA BUSCAR PRESCRIPCIÓN ---
      function buscarPrescripcion(query, idMedicamento, idPrescripcionSeleccionada = null) {
        if ((query.length > 0 || idPrescripcionSeleccionada) && idMedicamento) {
          $.ajax({
            url: '../../cfg/ajax/obtener_prescripciones.php',
            type: 'POST',
            data: {
              busqueda: query,
              id_medicamento: idMedicamento,
              id_prescripcion_fijo: idPrescripcionSeleccionada, // Enviamos el ID guardado
              metodo: $('#metodo_busqueda').val()
            },
            success: function(res) {
              $('#id_prescripcion').html(res);
              if (idPrescripcionSeleccionada) {
                $('#id_prescripcion').val(idPrescripcionSeleccionada);
              }
            }
          });
        }
      }

      // --- 3. LÓGICA DE INICIALIZACIÓN (AL CARGAR) ---

      // Determinar el Motivo de Salida guardado
      const motivoGuardado = "<?= $observacion_db ?>";
      $("#observaciones option").each(function() {
        if (motivoGuardado.includes($(this).val()) && $(this).val() !== "") {
          $('#observaciones').val($(this).val());
        }
      });

      // Mostrar sección correspondiente y cargar datos
      const tipoActual = $('#observaciones').val();
      if (tipoActual === 'Entrega a Paciente' || tipoActual === 'Entrega a Representante') {
        $('#seccion_interna').show();
        <?php if ($id_prescripcion_db) : ?>
          // Forzamos búsqueda por el ID para traer el nombre del paciente desde la tabla prescripción
          buscarPrescripcion('<?= $id_prescripcion_db ?>', $('#Id_descripcion_medicamento').val(), '<?= $id_prescripcion_db ?>');
        <?php endif; ?>
      } else if (tipoActual === 'Récipe Externo') {
        $('#seccion_externa').show();
      }

      // Cargar lotes guardados
      cargarLotes($('#Id_descripcion_medicamento').val(), '<?= $id_lote_db ?>');

      // --- 4. EVENTOS DE CAMBIO MANUAL ---

      $('#observaciones').on('change', function() {
        const val = $(this).val();
        $('#seccion_interna, #seccion_externa').hide();
        if (val === 'Entrega a Paciente' || val === 'Entrega a Representante') {
          $('#seccion_interna').fadeIn();
        } else if (val === 'Récipe Externo') {
          $('#seccion_externa').fadeIn();
        }
      });

      $('#input_busqueda_paciente').on('keyup', function() {
        buscarPrescripcion($(this).val(), $('#Id_descripcion_medicamento').val());
      });

      $('#Id_descripcion_medicamento').change(function() {
        cargarLotes($(this).val());
        $('#id_prescripcion').empty();
        $('#input_busqueda_paciente').val('');
      });

      // Manejo de Modales y Envío
      $('#formularioSalida').on('submit', function(e) {
        e.preventDefault();
        let error = false;
        $(this).find('[required]:visible').each(function() {
          if (!$(this).val()) {
            $(this).addClass('input-error');
            error = true;
          } else {
            $(this).removeClass('input-error');
          }
        });
        if (error) {
          $('#avisoTexto').text('Por favor complete los campos obligatorios antes de continuar.');
          $('#avisoModal').modal('show');
          return;
        }
        $('#modalSalidaGuardar').modal('show');
      });

      $('#confirmarGuardadoFinal').click(function() {
        $('#formularioSalida').off('submit').submit();
      });
      $('#abrirModalRegresar').click(function() {
        $('#modalRegresarInventario').modal('show');
      });

      // 1. Obtener valores desde PHP
      let identificador = "<?php echo $identificador_paciente_db; ?>";
      let idPrescripcion = "<?php echo $id_prescripcion_db; ?>";
      let idMed = "<?php echo $detalle['Id_descripcion_medicamento'] ?? ''; ?>";

      // 2. Colocar automáticamente en el input de búsqueda
      if (identificador !== "") {
        $('#input_busqueda_paciente').val(identificador);
         $('#id_prescripcion').val(idPrescripcion);

        // 3. Disparar la búsqueda para llenar el select de prescripciones
        // Pasamos el ID almacenado para que aparezca seleccionado
        buscarPrescripcion(identificador, idMed, idPrescripcion);
      }
    });
  </script>
</body>

</html>