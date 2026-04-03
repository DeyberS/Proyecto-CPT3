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
    #modalBúsquedaAvanzadaMedicamento,
    #modalRegresarInventario {
      animation: fadeIn 0.4s ease-out;
    }

    .modal.out .modal-dialog {
      animation: fadeOut 0.4s ease-in;
    }

    .modal-open .modal-backdrop {
      opacity: 0.7 !important;
      animation: pulse-opacity 0.3s forwards;
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
                      <label>Medicamento (*):</label>
                      <div class="input-group">
                        <select id="Id_descripcion_medicamento" name="Id_descripcion_medicamento" class="form-control" required>
                          <option value="">--- Seleccione un Medicamento ---</option>
                          <?php
                          $sqlMeds = "SELECT dm.Id, 
                          m.nombre_medicamento, 
                          tm.nombre_tipo,
                          GROUP_CONCAT(CONCAT(IFNULL(pa.nombre,''), ' ', IFNULL(dpm.cantidad_unidad_medida,''), IFNULL(um.unidad,'')) SEPARATOR ' + ') AS componentes
                          FROM descripcion_medicamento dm 
                          INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento 
                          INNER JOIN tipo_medicamento tm ON dm.Id_tipo = tm.Id_tipo 
                          INNER JOIN detalle_principio_medicamento dpm ON dm.Id = dpm.id_medicamento
                          INNER JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida
                          INNER JOIN principio_activo pa ON dpm.id_principio_activo = pa.Id_principio_activo
                          WHERE dm.estatus = 1
                          GROUP BY dm.Id
                          ORDER BY m.nombre_medicamento ASC";
                          $resMeds = $conexion->query($sqlMeds);
                          while ($m = $resMeds->fetch_assoc()) {
                            // Verificamos si es la opción seleccionada
                            $sel = ($detalle['Id_descripcion_medicamento'] == $m['Id']) ? "selected" : "";

                            // Limpiamos los datos para evitar errores de HTML
                            $nombre = htmlspecialchars($m['nombre_medicamento']);
                            $componentes = htmlspecialchars($m['componentes']);
                            $tipo = htmlspecialchars($m['nombre_tipo']);

                            // Imprimimos la línea unificada
                            echo "<option $sel value='{$m['Id']}'>$nombre ($componentes) - [$tipo]</option>";
                          }
                          ?>
                        </select>

                        <span class="input-group-btn">
                          <button class="btn btn-info" type="button" id="btnBuscarFiltrar" data-toggle="modal" data-target="#modalBúsquedaAvanzadaMedicamento" title="Búsqueda Avanzada de Medicamentos" style="height: 34px;">
                            <i><img src="../../recursos/imagenes/iconos/buscar.png" style="width:10px; height:10px;"></i>
                          </button>
                        </span>
                      </div>
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

  <div class="modal" id="modalBúsquedaAvanzadaMedicamento" tabindex="-1" role="dialog" aria-labelledby="modalBúsquedaAvanzadaMedicamentoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h5 class="modal-title" id="modalBúsquedaAvanzadaMedicamentoLabel" style="color: white;">Filtros de Búsqueda Avanzada</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 1;">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="formFiltroModal">
            <p class="text-muted">Complete uno o varios campos para refinar su búsqueda.</p>
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="filtro_nombre">Nombre (o ID):</label>
                  <input type="text" id="filtro_nombre" name="filtro_nombre" class="form-control" placeholder="Escriba nombre o ID...">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="filtro_tipo">Tipo:</label>
                  <select id="filtro_tipo" name="filtro_tipo" class="form-control">
                    <option value="">-- Todos --</option>
                    <?php
                    // Cargar tipos de medicamento dinámicamente
                    include("../../cfg/conexion.php"); // Asegura la conexión
                    $sql_tipos = "SELECT Id_tipo, nombre_tipo FROM tipo_medicamento WHERE estatus = 1 ORDER BY nombre_tipo DESC";
                    $res_tipos = $conexion->query($sql_tipos);
                    while ($row_t = $res_tipos->fetch_assoc()) {
                      echo '<option value="' . $row_t['Id_tipo'] . '">' . $row_t['nombre_tipo'] . '</option>';
                    }
                    ?>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="filtro_principios">Principios activos (contiene):</label>
                  <input type="text" id="filtro_principios" name="filtro_principios" class="form-control" placeholder="Ej: Ibuprofeno">
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="filtro_presentacion">Presentación:</label>
                  <input type="text" id="filtro_presentacion" name="filtro_presentacion" class="form-control" placeholder="Ej: 20 Capsulas">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="filtro_via">Vía de aplicación:</label>
                  <select id="filtro_via" name="filtro_via" class="form-control">
                    <option value="">-- Todas --</option>
                    <option value="Oral">Oral</option>
                    <option value="Sublingual">Sublingual</option>
                    <option value="Rectal">Rectal</option>
                    <option value="Intravenosa">Intravenosa</option>
                    <option value="Intramuscular">Intramuscular</option>
                    <option value="Subcutanea">Subcutanea</option>
                    <option value="Intradermica">Intradermica</option>
                    <option value="Topica">Topica</option>
                    <option value="Transdermica">Transdermica</option>
                    <option value="Inhalatoria">Inhalatoria</option>
                    <option value="Oftalmica">Oftalmica</option>
                    <option value="Otica">Otica</option>
                    <option value="Nasal">Nasal</option>
                    <option value="Vaginal">Vaginal</option>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="filtro_almacenamiento">C. de almacenamiento:</label>
                  <select id="filtro_almacenamiento" name="filtro_almacenamiento" class="form-control">
                    <option value="">-- Todas --</option>
                    <option value="-25_a_-10">Congelacion (-25°C a -10°C)</option>
                    <option value="2_a_8">Refrigeracion (2°C a 8°C)</option>
                    <option value="8_a_15">Lugar Fresco (8°C a 15°C)</option>
                    <option value="15_a_25">Temperatura Ambiente (15°C a 25°C)</option>
                    <option value="max_30">Temperatura Maxima (30°C)</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="filtro_laboratorio">Laboratorio:</label>
                  <select id="filtro_laboratorio" name="filtro_laboratorio" class="form-control">
                    <option value="">-- Todos --</option>
                    <?php
                    // Cargar laboratorios dinámicamente
                    $sql_labs = "SELECT Id_laboratorio, nombre_laboratorio FROM laboratorio WHERE estatus = 1 ORDER BY nombre_laboratorio ASC";
                    $res_labs = $conexion->query($sql_labs);
                    while ($row_l = $res_labs->fetch_assoc()) {
                      echo '<option value="' . $row_l['Id_laboratorio'] . '">' . $row_l['nombre_laboratorio'] . '</option>';
                    }
                    ?>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="filtro_composicion">Composición (contiene):</label>
                  <input type="text" id="filtro_composicion" name="filtro_composicion" class="form-control" placeholder="Escriba texto de composición...">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="filtro_barcode">Código de barras:</label>
                  <input type="text" id="filtro_barcode" name="filtro_barcode" class="form-control" placeholder="Escriba código de barras exacto...">
                </div>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-warning" id="btnLimpiarFiltros">Limpiar Filtros</button>
          <button type="button" class="btn btn-primary" id="btnAplicarFiltros">Aplicar Filtros</button>
        </div>
      </div>
    </div>
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

  <div class="modal" id="modalSalidaGuardar" tabindex="-1" role="dialog" aria-labelledby="modalSalidaGuardarLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: #00a65a; color: white;">
          <h5 class="modal-title" id="modalSalidaGuardarLabel" style="color: white;">Confirmacion de Guardado</h5>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea guardar la información para esta salida del inventario?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-success" id="confirmarGuardadoFinal">Guardar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalRegresarInventario" tabindex="-1" role="dialog" aria-labelledby="modalRegresarInventarioLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h5 class="modal-title" id="modalRegresarInventarioLabel" style="color: white;">Confirmacion de Regreso</h5>
        </div>
        <div class="modal-body">
          <p>Al hacer clic en "Abandonar Formulario", perderá todos los datos no guardados. ¿Desea continuar?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <a href="farmacia_inventario_listado.php" class="btn btn-danger">Abandonar Formulario</a>
        </div>
      </div>
    </div>
  </div>

  <?php include('includes/footer.php'); ?>

  <script>
    $(document).ready(function() {

      const medicamentoSelectPrincipal = $('#Id_descripcion_medicamento');

      // 1. Lógica para botón Limpiar Filtros dentro del Modal
      $('#btnLimpiarFiltros').on('click', function() {
        // Resetea el formulario del modal (formFiltroModal)
        $('#formFiltroModal')[0].reset();
        // Opcionalmente, podrías ejecutar el filtrado vacío para cargar todos
      });

      // 2. Lógica principal: Clic en "Aplicar Filtros" dentro del Modal
      $('#btnAplicarFiltros').on('click', function() {
        // Serializar los datos del formulario del modal
        const datosFiltro = $('#formFiltroModal').serialize();

        // Mostrar un indicador de carga en el botón si es necesario

        // Realizar la llamada AJAX al nuevo backend
        $.ajax({
          url: '../../cfg/ajax/filtrar_medicamentos_completo.php', // El nuevo backend
          type: 'POST',
          data: datosFiltro, // Envía los valores de los filtros del modal
          dataType: 'json',
          success: function(response) {
            // 1. Limpiar el select principal
            medicamentoSelectPrincipal.empty();

            // 2. Añadir la opción inicial por defecto
            medicamentoSelectPrincipal.append('<option value="">--- Seleccione un Medicamento ---</option>');

            // 3. Repoblar el select con los nuevos resultados filtrados
            if (response.length > 0) {
              // Iterar sobre el array de medicamentos devuelto por PHP
              response.forEach(function(item) {
                // Crear el nuevo <option value="id_desc">nombre completo descriptivo</option>
                medicamentoSelectPrincipal.append('<option value="' + item.id_desc + '">' + item.nombre_completo + '</option>');
              });

              // Opcionalmente: Si hay un solo resultado, seleccionarlo automáticamente
              /* if (response.length === 1) {
                  medicamentoSelectPrincipal.val(response[0].id_desc).trigger('change');
              } */
            } else {
              // Si no hay resultados
              medicamentoSelectPrincipal.append('<option value="" disabled>🛑 No se encontraron medicamentos que coincidan con los filtros aplicados.</option>');
            }

            // 4. Cerrar el modal animadamente usando tu lógica existente
            $('#modalBúsquedaAvanzadaMedicamento').removeClass('in').addClass('out');
            setTimeout(function() {
              $('#modalBúsquedaAvanzadaMedicamento').modal('hide');
              $('#modalBúsquedaAvanzadaMedicamento').removeClass('out');
            }, 400);

            // 5. Opcional: Mostrar un aviso si hay resultados
            /* if (response.length > 0) {
               // alert('Se encontraron ' + response.length + ' medicamentos. Busque en la lista principal.');
            } */
          },
          error: function(jqXHR, textStatus, errorThrown) {
            console.error("Error en AJAX de filtrado avanzado: ", textStatus, errorThrown);
            // Mostrar aviso de error si tienes una función para ello
            // mostrarAviso('🛑 Error al intentar filtrar los medicamentos desde el modal.');
          }
        });
      });

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