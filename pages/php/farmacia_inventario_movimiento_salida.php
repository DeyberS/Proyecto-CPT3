<?php
include("../../cfg/conexion.php");
$operacion_actual = isset($_GET['op']) ? $_GET['op'] : 'ajuste_salida';
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Inventario | Ajuste de Inventario</title>
  <?php include('includes/headerNav2.php'); ?>

  <style>
    /* ANIMACIONES Y ESTILOS GLOBALES */
    @keyframes pulse-opacity { 0% { opacity: 0; } 100% { opacity: 1; } }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-50px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes fadeOut { from { opacity: 1; transform: translateY(0); } to { opacity: 0; transform: translateY(-50px); } }

    .modal.in .modal-dialog, #avisoModal, #modalSalidaGuardar, #modalBúsquedaAvanzadaMedicamento, #modalRegresarInventario, #modalAgregarMedicamento {
      animation: fadeIn 0.4s ease-out;
    }
    .modal.out .modal-dialog { animation: fadeOut 0.4s ease-out; }
    .modal-open .modal-backdrop { opacity: 0.7 !important; animation: pulse-opacity 0.3s forwards; }
    
    .modal-header .close { color: #fff; filter: alpha(opacity=80); opacity: .8; text-shadow: none; }
    .modal-header .close:hover { opacity: 1; }

    .input-error { border: 2px solid crimson !important; box-shadow: 0 0 5px crimson; }
    .bg-crimson { background-color: #dc3545 !important; color: white !important; }
    .bg-warning-custom { background-color: #f39c12 !important; color: white !important; }

    .content-wrapper { min-height: 100vh !important; overflow-y: auto; }
    .main-sidebar { position: fixed !important; height: 100%; }

    /* ESTILOS DE TABLA (Traídos de la Entrada) */
    .table-detalle th { background-color: #f4f4f4; text-align: center; }
    .table-detalle td { text-align: center; vertical-align: middle !important; }
    .row-vence-pronto { background-color: #fff3cd !important; }

    .area-trabajo-blanca {
      background-color: #ffffff; padding: 25px; border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08); margin-bottom: 20px;
    }

    input[readonly], input[disabled] { background-color: #eeeeee !important; cursor: not-allowed; }
    .text-green-bold { color: #00a65a; font-weight: bold; font-size: 1.2em; }
  </style>
</head>

<body class="hold-transition skin-blue sidebar-mini">
  <div class="content-wrapper">
    <section class="content-header">
      <h1>Salidas Especiales y Ajustes <small>Módulo de Control</small></h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-users"></i>Inventario</a></li>
        <li class="active"><a href="#"><i class="fa fa-minus-circle"></i>Ajustes</a></li>
      </ol>
    </section>

    <section class="content">
      <form id="formularioSalida" method="POST" action="../../cfg/movimientos_inventario.php" novalidate autocomplete="off">
        <input type="hidden" name="op" id="op" value="<?php echo $operacion_actual; ?>">
        <input type="hidden" name="detalle_medicamentos" id="detalle_medicamentos" value="[]">

        <div class="row">
          <div class="col-md-12">
            <div class="area-trabajo-blanca">

              <div class="box box-warning">
                <div class="box-header with-border">
                  <h3 class="box-title"><i class="fa fa-file-text-o"></i> Datos del Ajuste:</h3>
                </div>
                <div class="box-body">
                  <div class="row">
                    <div class="col-sm-4 form-group">
                      <label>Motivo del Ajuste (*):</label>
                      <select name="id_tipo_movimiento" id="id_tipo_movimiento" class="form-control" required>
                        <option value="">-- Seleccione un motivo --</option>
                        <option value="3">Salida por Vencimiento</option>
                        <option value="4">Salida por Dañado</option>
                        <option value="5">Salida por Pérdida o Robo</option>
                        <option value="7">Ajuste por Cuadre (Salida)</option>
                      </select>
                    </div>

                    <div class="col-sm-3 form-group">
                      <label>Fecha de Registro:</label>
                      <input type="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" readonly>
                    </div>

                    <div class="col-sm-5 form-group">
                      <label>Observaciones / Justificación (*):</label>
                      <input type="text" name="observaciones_generales" id="observaciones_generales" class="form-control" placeholder="Ej: Frascos rotos durante el traslado..." required>
                    </div>
                  </div>
                </div>
              </div>

              <div class="box box-danger">
                <div class="box-header with-border">
                  <h3 class="box-title"><i class="fa fa-medkit"></i> Medicamentos a Dar de Baja:</h3>
                  <div class="box-tools pull-right">
                    <button type="button" class="btn btn-primary btn-sm" id="btnAbrirModalAgregar">
                      <i class="fa fa-plus"></i> Añadir Medicamento
                    </button>
                  </div>
                </div>
                <div class="box-body">
                  <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-detalle" id="tablaMedicamentos">
                      <thead>
                        <tr>
                          <th>Medicamento</th>
                          <th>Lote</th>
                          <th>F. Vencimiento</th>
                          <th>Cant. a Bajar</th>
                          <th>Acciones</th>
                        </tr>
                      </thead>
                      <tbody id="cuerpoTablaMedicamentos">
                        <tr id="filaVacia">
                          <td colspan="5" class="text-center text-muted">Aún no se han añadido medicamentos para dar de baja.</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <div class="box box-default">
                <div class="box-header with-border">
                  <h3 class="box-title"><i class="fa fa-camera"></i> Evidencia del Ajuste (Acta, Foto del daño, etc.):</h3>
                </div>
                <div class="box-body">
                  <div class="btn-group" style="width: 100%; display: flex;">
                    <button type="button" class="btn btn-info" style="flex: 1;" data-toggle="modal" data-target="#modalEvidencia" onclick="prepararCaptura('camara')">
                      <i class="fa fa-camera"></i> <span id="txt-btn-camara">Usar Cámara</span>
                    </button>
                    <button type="button" class="btn btn-warning" style="flex: 1;" data-toggle="modal" data-target="#modalEvidencia" onclick="prepararCaptura('archivo')">
                      <i class="fa fa-upload"></i> <span id="txt-btn-subir">Subir Archivo</span>
                    </button>
                  </div>
                  <div id="miniatura-evidencia" style="display:none; margin-top:10px; border: 1px solid #ddd; padding: 5px; border-radius: 4px; background: #f9f9f9; text-align: center; cursor: pointer;" title="Haga clic para ampliar">
                    <span class="text-green-bold" style="font-size: 0.9em;"><i class="fa fa-check-circle"></i> Evidencia cargada correctamente</span>
                    <button type="button" class="btn btn-xs btn-danger pull-right" onclick="quitarImagenGlobal()"><i><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></i></button>
                  </div>
                  <input type="hidden" name="foto_base64" id="foto_base64">
                </div>
                
                <div class="box-footer text-right" style="background-color: transparent; border-top: 1px solid #f4f4f4; padding-top: 15px;">
                  <button type="button" class="btn btn-secondary" id="abrirModalRegresar">Regresar</button>
                  <button type="submit" class="btn btn-danger" id="btnPrepararGuardado"><i class="fa fa-trash"></i> Procesar Baja</button>
                </div>
              </div>

            </div>
          </div>
        </div>
      </form>
    </section>
  </div>

  <div class="modal" id="modalAgregarMedicamento" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title" style="color: white;"><i class="fa fa-minus-circle"></i> Seleccionar Lote a Dar de Baja</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-sm-12 form-group">
              <label>Medicamento del Catálogo (*):</label>
              <div class="input-group">
                <select id="Id_descripcion_medicamento" class="form-control">
                  <option value="">--- Seleccione un Medicamento ---</option>
                  <?php
                  $sql_meds = "SELECT dm.Id AS id_desc, m.nombre_medicamento, p.nombre_presentacion,
                    GROUP_CONCAT(CONCAT(IFNULL(pa.nombre,''), ' ', IFNULL(dpm.cantidad_unidad_medida,''), ' ', IFNULL(um.unidad,'')) SEPARATOR ' + ') AS componentes
                    FROM descripcion_medicamento dm
                    INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
                    INNER JOIN presentacion p ON dm.Id_presentacion = p.Id_presentacion
                    LEFT JOIN detalle_principio_medicamento dpm ON dm.Id = dpm.id_medicamento
                    LEFT JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida
                    LEFT JOIN principio_activo pa ON dpm.id_principio_activo = pa.Id_principio_activo
                    WHERE m.estatus = 1 AND dm.estatus = 1 
                    GROUP BY dm.Id ORDER BY m.nombre_medicamento ASC";
                  $res_meds = $conexion->query($sql_meds);
                  while ($row_med = $res_meds->fetch_assoc()) {
                    $comp = trim($row_med['componentes']) ? " (" . htmlspecialchars($row_med['componentes']) . ")" : "";
                    echo '<option value="' . $row_med['id_desc'] . '" data-nombre="' . htmlspecialchars($row_med['nombre_medicamento'] . " [" . $row_med['nombre_presentacion'] . "]") . '" data-componentes="' . htmlspecialchars($row_med['componentes']) . '">' . htmlspecialchars($row_med['nombre_medicamento']) . $comp . " - [" . htmlspecialchars($row_med['nombre_presentacion']) . "]" . '</option>';
                  }
                  ?>
                </select>
                <span class="input-group-btn">
                  <button class="btn btn-info" type="button" data-toggle="modal" data-target="#modalBúsquedaAvanzadaMedicamento" style="height: 34px;">
                    <i><img src="../../recursos/imagenes/iconos/buscar.png" style="width:10px; height:10px;"></i>
                  </button>
                </span>
              </div>
            </div>

            <div class="col-sm-4 form-group mt-3">
              <label>Seleccione Lote (*):</label>
              <select id="lista_lotes" class="form-control">
                <option value="">-- Seleccione un lote --</option>
              </select>
            </div>
            
            <div class="col-sm-4 form-group mt-3">
              <label>F. Vencimiento:</label>
              <input type="text" id="fecha_vencimiento_readonly" class="form-control" readonly disabled>
            </div>

            <div class="col-sm-4 form-group mt-3">
              <label>Cantidad a Dar de Baja (*):</label>
              <input type="text" id="cantidad_baja" class="form-control" placeholder="Solo números">
              <small id="max_stock_help" class="form-text text-muted" style="color:#d9534f; font-weight:bold;"></small>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-danger" id="btnConfirmarAgregarMedicamento"><i class="fa fa-check"></i> Añadir a la Lista</button>
        </div>
      </div>
    </div>
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
                  <label for="filtro_presentacion">Presentación:</label>
                  <select id="filtro_presentacion" name="filtro_presentacion" class="form-control">
                    <option value="">-- Todos --</option>
                    <?php
                    // Cargar tipos de medicamento dinámicamente
                    include("../../cfg/conexion.php"); // Asegura la conexión
                    $sql_tipos = "SELECT Id_presentacion, nombre_presentacion FROM presentacion WHERE estatus = 1 ORDER BY nombre_presentacion DESC";
                    $res_tipos = $conexion->query($sql_tipos);
                    while ($row_t = $res_tipos->fetch_assoc()) {
                      echo '<option value="' . $row_t['Id_presentacion'] . '">' . $row_t['nombre_presentacion'] . '</option>';
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
                  <label for="filtro_contenido_neto">Contenido neto:</label>
                  <input type="text" id="filtro_contenido_neto" name="filtro_contenido_neto" class="form-control" placeholder="Ej: 20 Capsulas">
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
                  <label for="filtro_excipientes">Excipientes (contiene):</label>
                  <input type="text" id="filtro_excipientes" name="filtro_excipientes" class="form-control" placeholder="Escriba texto de excipientes...">
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

  <div class="modal fade" id="modalEvidencia" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h4 class="modal-title" style="color:white;"><i class="fa fa-file-image-o"></i> Gestionar Evidencia</h4>
        </div>
        <div class="modal-body text-center">
          <div id="modal-placeholder" style="padding: 40px 20px; border: 2px dashed #ddd; border-radius: 10px; color: #999;">
            <i class="fa fa-cloud-upload" style="font-size: 50px; margin-bottom: 15px;"></i>
            <h4 id="placeholder-texto">Cargando interfaz...</h4>
          </div>
          <div id="modal-seccion-camara" style="display:none;">
            <video id="video-modal" width="100%" style="max-width: 450px; border-radius: 8px; background: #000;" autoplay></video>
            <canvas id="canvas-modal" style="display:none;"></canvas>
            <div style="margin-top:10px;">
              <button type="button" id="btn-capturar-modal" class="btn btn-success"><i class="fa fa-camera"></i> Tomar Foto</button>
            </div>
          </div>
          <input type="file" id="input-archivo-modal" accept="image/*" style="display:none;">
          <div id="modal-contenedor-previa" style="display:none; margin-top:10px;">
            <img id="foto-previa-modal" src="" style="max-width: 100%; max-height: 400px; border: 3px solid #3c8dbc; border-radius: 5px;">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary" id="btn-aceptar-evidencia" style="display:none;">Confirmar y Usar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalVisualizarFoto" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header bg-navy">
          <button type="button" class="close" data-dismiss="modal" style="color:white; opacity:1;">&times;</button>
          <h4 class="modal-title" style="color:white;"><i class="fa fa-eye"></i> Vista Previa de Evidencia</h4>
        </div>
        <div class="modal-body text-center">
          <img id="img-vista-grande" src="" style="width: 100%; border-radius: 5px; box-shadow: 0 0 15px rgba(0,0,0,0.2);">
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="avisoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
          <h5 class="modal-title" style="color: white;">Aviso de Validación</h5>
        </div>
        <div class="modal-body"><p id="avisoTexto"></p></div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button></div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalRegresarInventario" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h5 class="modal-title" style="color: white;">Confirmacion de Regreso</h5>
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

  <div class="modal" id="modalSalidaGuardar" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-warning-custom">
          <h5 class="modal-title" style="color: white;">Confirmar Ajuste</h5>
        </div>
        <div class="modal-body"><p>¿Está seguro de procesar esta baja de inventario? Esta acción restará unidades del sistema.</p></div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-danger" id="confirmarGuardadoFinal">Confirmar Baja</button>
        </div>
      </div>
    </div>
  </div>

  <?php include('includes/footer.php'); ?>

  <script>
    // VARIABLE GLOBAL PARA LA TABLA DINÁMICA
    let listaDetalles = [];
    let editandoIndex = -1;
    let streamModal = null;
    const hoy = new Date().toISOString().split('T')[0];

    function mostrarAviso(mensaje) {
      $('#avisoTexto').html(mensaje);
      $('#avisoModal').modal('show');
    }

    // CIERRE ANIMADO DE MODALES
    $('.modal').on('click', '[data-dismiss="modal"]', function() {
      var $modal = $(this).closest('.modal');
      $modal.removeClass('in').addClass('out');
      setTimeout(function() { $modal.modal('hide').removeClass('out'); }, 400);
    });

    $(document).ready(function() {

      // =====================================================================
      // LIMPIEZA ADICIONAL PARA MODALES (Soluciona el problema de fondo oscuro)
      // =====================================================================
      $('.modal').on('hidden.bs.modal', function() {
        if ($('.modal:visible').length) {
          $('body').addClass('modal-open');
          // NO eliminamos el backdrop si aún hay modales abiertos superpuestos
        } else {
          $('body').removeClass('modal-open');
          $('.modal-backdrop').remove(); // Solo destruimos el backdrop si es el último modal
        }
      });

      // -------------------------------------------------------------
      // LÓGICA DE EVIDENCIA (CÁMARA / ARCHIVO)
      // -------------------------------------------------------------
      window.prepararCaptura = function(tipo) {
        $('#modal-seccion-camara, #modal-contenedor-previa, #btn-aceptar-evidencia').hide();
        $('#modal-placeholder').show();
        $('#foto-previa-modal').attr('src', '');

        if (tipo === 'camara') {
          $('#placeholder-texto').text('Iniciando Cámara...');
          const video = document.getElementById('video-modal');
          if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
              .then(stream => {
                streamModal = stream;
                video.srcObject = stream;
                $('#modal-placeholder').hide();
                $('#modal-seccion-camara').show();
              }).catch(err => {
                $('#placeholder-texto').text('Error: Cámara no disponible');
              });
          }
        } else {
          $('#placeholder-texto').text('Seleccionando archivo...');
          $('#input-archivo-modal').click();
          setTimeout(() => { $('#placeholder-texto').text('Esperando archivo...'); }, 1000);
        }
      };

      $('#input-archivo-modal').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = function(event) {
            $('#modal-placeholder').hide();
            $('#foto-previa-modal').attr('src', event.target.result);
            $('#modal-contenedor-previa, #btn-aceptar-evidencia').show();
          };
          reader.readAsDataURL(file);
        }
      });

      $('#btn-capturar-modal').on('click', function() {
        const video = document.getElementById('video-modal');
        const canvas = document.getElementById('canvas-modal');
        const context = canvas.getContext('2d');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        $('#foto-previa-modal').attr('src', canvas.toDataURL('image/jpeg', 0.8));
        $('#modal-seccion-camara').hide();
        $('#modal-contenedor-previa, #btn-aceptar-evidencia').show();
        if (streamModal) streamModal.getTracks().forEach(t => t.stop());
      });

      $('#btn-aceptar-evidencia').on('click', function() {
        $('#foto_base64').val($('#foto-previa-modal').attr('src'));
        $('#txt-btn-camara').text('Volver a Tomar');
        $('#txt-btn-subir').text('Cambiar Archivo');
        $('#miniatura-evidencia').fadeIn();
        $('#modalEvidencia').modal('hide');
      });

      window.quitarImagenGlobal = function() {
        $('#foto_base64').val('');
        $('#txt-btn-camara').text('Usar Cámara');
        $('#txt-btn-subir').text('Subir Archivo');
        $('#miniatura-evidencia').hide();
        $('#input-archivo-modal').val('');
      };

      $(document).on('click', '#miniatura-evidencia', function(e) {
        if ($(e.target).closest('.btn-danger').length) return;
        const foto = $('#foto_base64').val();
        if (foto) {
          $('#img-vista-grande').attr('src', foto);
          $('#modalVisualizarFoto').modal('show');
        }
      });

      $('#modalEvidencia').on('hidden.bs.modal', function() {
        if (streamModal) { streamModal.getTracks().forEach(t => t.stop()); streamModal = null; }
      });

      // -------------------------------------------------------------
      // FILTRADO AVANZADO EXACTO DEL ARCHIVO AJUSTES
      // -------------------------------------------------------------
      const medicamentoSelectPrincipal = $('#Id_descripcion_medicamento');

      $('#btnLimpiarFiltros').on('click', function() {
        $('#formFiltroModal')[0].reset();
      });

      $('#btnAplicarFiltros').on('click', function() {
        const datosFiltro = $('#formFiltroModal').serialize();

        $.ajax({
          url: '../../cfg/ajax/filtrar_medicamentos_completo.php',
          type: 'POST',
          data: datosFiltro,
          dataType: 'json',
          success: function(response) {
            medicamentoSelectPrincipal.empty();
            medicamentoSelectPrincipal.append('<option value="">--- Seleccione un Medicamento ---</option>');
            if (response.length > 0) {
              response.forEach(function(item) {
                // Se agregan los data-atributos vacíos o con el nombre completo para evitar que la tabla de abajo se quede sin texto
                medicamentoSelectPrincipal.append('<option value="' + item.id_desc + '" data-nombre="' + item.nombre_completo + '" data-componentes="">' + item.nombre_completo + '</option>');
              });
            } else {
              medicamentoSelectPrincipal.append('<option value="" disabled>🛑 No se encontraron medicamentos que coincidan con los filtros aplicados.</option>');
            }
            
            // SE ELIMINÓ EL CÓDIGO QUE CERRABA EL MODAL AUTOMÁTICAMENTE
            // Ahora el modal se queda abierto para que puedas seguir filtrando.

          },
          error: function(jqXHR, textStatus, errorThrown) {
            console.error("Error en AJAX de filtrado avanzado: ", textStatus, errorThrown);
          }
        });
      });

      // -------------------------------------------------------------
      // LÓGICA DEL MODAL: AGREGAR A LA LISTA
      // -------------------------------------------------------------
      function limpiarFormularioModal() {
        $('#Id_descripcion_medicamento').val('').trigger('change');
        $('#lista_lotes').empty().append('<option value="">-- Seleccione un lote --</option>');
        $('#fecha_vencimiento_readonly').val('');
        $('#cantidad_baja').val('').removeClass('input-error');
        $('#max_stock_help').text('');
      }

      $('#btnAbrirModalAgregar').on('click', function() {
        editandoIndex = -1;
        limpiarFormularioModal();
        $('#btnConfirmarAgregarMedicamento').html('<i class="fa fa-check"></i> Añadir a la Lista');
        $('#modalAgregarMedicamento').modal('show');
      });

      // Validar entrada numérica en cantidad
      $('#cantidad_baja').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value === '0') this.value = '1';
      });

      // Cargar lotes al seleccionar medicamento
      $('#Id_descripcion_medicamento').on('change', function() {
        const medId = $(this).val();
        let $lotes = $('#lista_lotes').empty().append('<option value="">-- Cargando... --</option>');
        $('#fecha_vencimiento_readonly').val('');
        $('#max_stock_help').text('');

        if (medId) {
          $.ajax({
            url: '../../cfg/ajax/obtener_descripcion_medicamento.php',
            type: 'POST',
            data: { id: medId }, // Usa el mismo AJAX de despacho que trae existencias
            dataType: 'json',
            success: function(data) {
              $lotes.empty().append('<option value="">-- Seleccione un lote --</option>');
              if (data.lotes && data.lotes.length > 0) {
                data.lotes.forEach(l => {
                  let estilo = l.dias_restantes <= 0 ? "color:red;" : "";
                  
                  // Agregado para mostrar los días restantes
                  let textoDisp = "";
                  if (l.dias_restantes <= 0) {
                      textoDisp = " (VENCIDO)";
                  } else {
                      textoDisp = ` (Faltan ${l.dias_restantes} días)`;
                  }

                  $lotes.append(`<option value="${l.Id}" data-lote="${l.lote}" data-venc="${l.fecha_vencimiento}" data-cant="${l.cantidad_actual}" data-dias="${l.dias_restantes}" style="${estilo}">${l.lote} - Stock: ${l.cantidad_actual} ${textoDisp}</option>`);
                });
              } else {
                $lotes.empty().append('<option value="">No hay stock de este medicamento</option>');
              }
            }
          });
        }
      });

      // Al seleccionar un lote, mostrar info y límite de stock
      $('#lista_lotes').on('change', function() {
        const option = $(this).find('option:selected');
        if (option.val() !== "") {
          // Mostrar los días restantes también en el input de solo lectura
          let textoDias = option.data('dias') <= 0 ? ' (VENCIDO)' : ` (Faltan ${option.data('dias')} días)`;
          
          $('#fecha_vencimiento_readonly').val(option.data('venc'));
          $('#max_stock_help').text(`Máximo disponible para baja: ${option.data('cant')} unid.`);
        } else {
          $('#fecha_vencimiento_readonly').val('');
          $('#max_stock_help').text('');
        }
      });

      // Bóton Añadir al array
      $('#btnConfirmarAgregarMedicamento').on('click', function() {
        const id_med = $('#Id_descripcion_medicamento').val();
        const nombre_med = $('#Id_descripcion_medicamento option:selected').data('nombre') || $('#Id_descripcion_medicamento option:selected').text();
        const componentes = $('#Id_descripcion_medicamento option:selected').data('componentes') || '';
        
        const lote_id = $('#lista_lotes').val(); // ID del lote en BD (necesario para el backend)
        const lote_nombre = $('#lista_lotes option:selected').data('lote');
        const f_venc = $('#lista_lotes option:selected').data('venc');
        const max_cant = parseInt($('#lista_lotes option:selected').data('cant') || 0);
        
        const cant_baja = parseInt($('#cantidad_baja').val());

        $('.modal-body .form-control').removeClass('input-error');

        if (!id_med) { $('#Id_descripcion_medicamento').addClass('input-error'); mostrarAviso('Seleccione un medicamento.'); return; }
        if (!lote_id) { $('#lista_lotes').addClass('input-error'); mostrarAviso('Seleccione un lote.'); return; }
        if (isNaN(cant_baja) || cant_baja <= 0) { $('#cantidad_baja').addClass('input-error'); mostrarAviso('Ingrese una cantidad válida.'); return; }
        if (cant_baja > max_cant) { $('#cantidad_baja').addClass('input-error'); mostrarAviso(`La cantidad supera el stock físico de este lote (${max_cant}).`); return; }

        // Evitar duplicados en la lista visual (a menos que se esté editando ese mismo registro)
        const existeIndex = listaDetalles.findIndex(item => item.id_medicamento === id_med && item.lote_id === lote_id);
        if (existeIndex !== -1 && existeIndex !== editandoIndex) {
          mostrarAviso('Este Lote ya está en la lista de bajas. Modifíquelo directamente usando el botón de editar.');
          return;
        }

        const nuevoItem = {
          id_medicamento: id_med,
          nombre_medicamento: nombre_med,
          componentes: componentes,
          lote_id: lote_id, // Enviamos el ID real para restarlo
          lote: lote_nombre, // Para mostrarlo en la tabla
          fecha_vencimiento: f_venc,
          cantidad: cant_baja
        };

        if (editandoIndex !== -1) {
          listaDetalles[editandoIndex] = nuevoItem;
        } else {
          listaDetalles.push(nuevoItem);
        }

        actualizarTablaDetalles();
        $('#modalAgregarMedicamento').modal('hide');
      });

      // -------------------------------------------------------------
      // DIBUJAR LA TABLA Y ACCIONES (Eliminar/Editar)
      // -------------------------------------------------------------
      function actualizarTablaDetalles() {
        const tbody = $('#cuerpoTablaMedicamentos');
        tbody.empty();

        if (listaDetalles.length === 0) {
          tbody.append('<tr id="filaVacia"><td colspan="5" class="text-center text-muted">Aún no se han añadido medicamentos para dar de baja.</td></tr>');
        } else {
          listaDetalles.forEach((item, index) => {
            let rowClass = "";
            let diffDias = (new Date(item.fecha_vencimiento) - new Date(hoy)) / (1000 * 60 * 60 * 24);
            if (diffDias <= 0) rowClass = "row-vence-pronto"; // Destacar si está vencido

            tbody.append(`
              <tr class="${rowClass}">
                <td style="text-align: left;">
                  ${item.nombre_medicamento}
                  <br><small class="text-muted" style="font-size: 11px;"><i>${item.componentes}</i></small>
                </td>
                <td><strong>${item.lote}</strong></td>
                <td>${item.fecha_vencimiento} ${diffDias <= 0 ? ' <i class="fa fa-exclamation-triangle text-danger" title="Lote Vencido"></i>' : ''}</td>
                <td><span class="badge bg-red" style="font-size:14px;">- ${item.cantidad}</span></td>
                <td>
                  <button type="button" class="btn btn-warning btn-xs btn-editar-fila" data-index="${index}"><img src="../../recursos/imagenes/iconos/editar.png" style="width:10px;"></button>
                  <button type="button" class="btn btn-danger btn-xs btn-eliminar-fila" data-index="${index}"><img src="../../recursos/imagenes/iconos/Delete.png" style="width:10px;"></button>
                </td>
              </tr>
            `);
          });
        }
        $('#detalle_medicamentos').val(JSON.stringify(listaDetalles));
      }

      $('#cuerpoTablaMedicamentos').on('click', '.btn-eliminar-fila', function() {
        listaDetalles.splice($(this).data('index'), 1);
        actualizarTablaDetalles();
      });

      $('#cuerpoTablaMedicamentos').on('click', '.btn-editar-fila', function() {
        const index = $(this).data('index');
        const item = listaDetalles[index];
        editandoIndex = index; 

        limpiarFormularioModal(); 
        
        // Cargar datos al modal
        $('#Id_descripcion_medicamento').val(item.id_medicamento).trigger('change');
        
        // Dar tiempo al AJAX de lotes para cargar y luego seleccionarlo
        setTimeout(() => {
          $('#lista_lotes').val(item.lote_id).trigger('change');
          $('#cantidad_baja').val(item.cantidad);
        }, 500);

        $('#btnConfirmarAgregarMedicamento').html('<i class="fa fa-save"></i> Guardar Cambios');
        $('#modalAgregarMedicamento').modal('show');
      });

      // -------------------------------------------------------------
      // SUBMIT DEL FORMULARIO PRINCIPAL
      // -------------------------------------------------------------
      $('#abrirModalRegresar').on('click', function() { $('#modalRegresarInventario').modal('show'); });

      $('#formularioSalida').on('submit', function(e) {
        e.preventDefault();

        if ($('#id_tipo_movimiento').val() === "") {
          mostrarAviso("Debe seleccionar un Motivo para el Ajuste.");
          return;
        }

        if ($('#observaciones_generales').val().trim() === "") {
          mostrarAviso("Debe ingresar una justificación / observación obligatoria.");
          return;
        }

        if (listaDetalles.length === 0) {
          mostrarAviso("La lista está vacía. Debe añadir al menos un medicamento para procesar la baja.");
          return;
        }

        // Validación opcional: Evidencia obligatoria si es "Dañado" o "Robo"
        const motivo = parseInt($('#id_tipo_movimiento').val());
        const evidencia = $('#foto_base64').val();
        if ((motivo === 4 || motivo === 5) && (!evidencia || evidencia.trim() === "")) {
          mostrarAviso('<strong>📸 Evidencia Sugerida/Obligatoria:</strong><br>Para registrar daños, pérdidas o robos, es altamente recomendable (o requerido por su política) adjuntar un comprobante o foto.');
          return; // Puedes quitar el "return" si quieres que sea solo un aviso y no obligatorio
        }

        $('#modalSalidaGuardar').modal('show');
      });

      $('#confirmarGuardadoFinal').on('click', function() {
        $('#formularioSalida').off('submit').submit();
      });

    });
  </script>
</body>
</html>