<?php
include("../../cfg/conexion.php");
$operacion_actual = isset($_GET['op']) ? $_GET['op'] : 'despacho';

// SIMULACIÓN: Cuando tengas la BD real, descomenta esto.
/*
$sql_ultimo_id = "SELECT MAX(Id_detalle_inventario) AS ultimo FROM detalle_inventario";
$resultado_id = $conexion->query($sql_ultimo_id);
$row_id = $resultado_id->fetch_assoc();
$proximo_id = ($row_id['ultimo'] ? $row_id['ultimo'] : 0) + 1;
$numero_proyectado = str_pad($proximo_id, 6, "0", STR_PAD_LEFT);
*/
$numero_proyectado = "000025"; // Simulado

include('includes/headerNav2.php');
verificarAcceso('Generar Despacho de Inventario');
?>

<head>
  <title>Inventario | Despacho de Vacunas</title>
</head>

<style>
  #avisoModal { z-index: 1000000 !important; }
  .table-responsive { max-height: 220px; overflow-y: auto; }
  .table-detalle thead th { position: sticky; top: 0; background-color: #f4f4f4; z-index: 1; }
  .input-group { display: flex !important; width: 100% !important; align-items: stretch; }
  .input-group .form-control { flex: 1 1 auto; width: 1% !important; height: 34px !important; }
  .input-group-btn { width: auto !important; display: flex !important; align-items: stretch; }
  .input-group-btn .btn { height: 34px !important; display: flex; align-items: center; justify-content: center; }
  .input-group-btn style[width="25%"], .input-group-btn[style*="width: 25%"] { width: 65px !important; flex: 0 0 65px !important; }
  .tags-input-container { display: flex; flex-wrap: wrap; gap: 5px; padding: 4px; border: 1px solid #d2d6de; background-color: #fff; min-height: 34px; cursor: text; }
  .tags-input-container .tag-badge { display: inline-flex; align-items: center; padding: 4px 8px; border-radius: 4px; font-size: 12px; color: white; }
  .tags-input-container .tag-badge i { margin-left: 6px; cursor: pointer; font-size: 10px; }
  .tags-input-fake { border: none; outline: none; flex-grow: 1; min-width: 150px; padding: 2px; }
  .table-detalle th { background-color: #f4f4f4; text-align: center; }
  .table-detalle td { text-align: center; vertical-align: middle !important; }
  .area-trabajo-blanca { background-color: #ffffff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08); margin-bottom: 20px; overflow: hidden; }
  input[type="date"][readonly], input[type="date"][disabled] { background-color: #eeeeee !important; cursor: not-allowed; }
</style>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Despachar Vacunas (Biológicos)</h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="#"><i class="fa fa-medkit"></i>Inmunización</a></li>
      <li class="active"><a href="#"><i class="fa fa-arrow-up"></i>Despacho</a></li>
    </ol>
  </section>

  <section class="content">
    <form id="formularioDespacho" method="POST" action="../../cfg/movimientos_inventario.php" novalidate autocomplete="off">
      <input type="hidden" name="op" id="op" value="<?php echo $operacion_actual; ?>">
      <input type="hidden" name="detalle_medicamentos" id="detalle_medicamentos" value="[]">

      <div class="row">
        <div class="col-md-12">
          <div class="area-trabajo-blanca">

            <div class="box-tools pull-left" style="margin-top: 2px;">
              <span style="font-size: 16px; font-weight: bold; color: #555;">
                Operación N°: <span id="badge_numero_operacion" class="badge bg-blue" style="font-size: 15px; padding: 5px 10px; letter-spacing: 1px;">#<?php echo $numero_proyectado; ?></span>
              </span>
            </div>
            <br>

            <!-- DATOS DEL DESTINO (AMBULATORIO / CENTRO DE SALUD) -->
            <div class="box box-primary">
              <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-hospital-o"></i> Datos del Destino:</h3>
              </div>
              <div class="box-body">
                <div class="row">
                  <div class="col-sm-3 form-group">
                    <label>Tipo de Despacho (*):</label>
                    <select id="tipo_despacho" name="tipo_despacho" class="form-control" required>
                      <option value="ambulatorio">Ambulatorio / Centro de Salud</option>
                      <option value="jornada">Jornada de Vacunación</option>
                    </select>
                  </div>

                  <div class="col-sm-4 form-group">
                    <label>Ambulatorio / Destino (*):</label>
                    <div class="input-group">
                      <select id="destino_ambulatorio" name="destino_ambulatorio" class="form-control" required>
                        <option value="">--- Seleccione el Destino ---</option>
                        <!-- SIMULACIÓN: Aquí irían los ambulatorios de la BD -->
                        <option value="1">Ambulatorio Urbano II - Agua Blanca</option>
                        <option value="2">Ambulatorio Rural I - Guanarito</option>
                        <option value="3">Hospital Central - Guanare</option>
                        <option value="4">Jornada - Comunidad La Manguera</option>
                      </select>
                      <span class="input-group-btn">
                        <button class="btn btn-info" type="button" id="btnAgregarDestino" title="Agregar Nuevo Destino" style="height: 34px;">
                          <i><img src="../../recursos/imagenes/iconos/agregar.png" style="width:10px; height:10px;"></i>
                        </button>
                      </span>
                    </div>
                  </div>

                  <div class="col-sm-2 form-group">
                    <label>N° Guía de Despacho (*):</label>
                    <input type="text" id="numero_guia" name="numero_guia" class="form-control" placeholder="Ej: 000123" required>
                  </div>

                  <div class="col-sm-3 form-group">
                    <label>Responsable de Recibir (*):</label>
                    <input type="text" id="responsable_receptor" name="responsable_receptor" class="form-control" placeholder="Nombre y Cédula" required>
                  </div>

                  <div class="col-sm-3 form-group">
                    <label>Temperatura de Transporte (*):</label>
                    <select id="temperatura_transporte" name="temperatura_transporte" class="form-control" required>
                      <option value="">--- Seleccione ---</option>
                      <option value="2-8">Entre 2°C y 8°C (Refrigeración)</option>
                      <option value="-25-10">Entre -25°C y -10°C (Congelación)</option>
                      <option value="otro">Otro (Especificar)</option>
                    </select>
                  </div>

                  <div class="col-sm-3 form-group">
                    <label>Observaciones:</label>
                    <input type="text" id="observaciones_despacho" name="observaciones_despacho" class="form-control" placeholder="Ej: Caja fría con hielo seco">
                  </div>
                </div>
              </div>
            </div>

            <!-- VACUNAS A DESPACHAR -->
            <div class="box box-success">
              <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-syringe"></i> Vacunas a Despachar:</h3>
                <div class="box-tools pull-right" style="margin-left: 5px;">
                  <button type="button" class="btn btn-primary btn-sm" id="btnAbrirModalAgregar" style="width:200px;">
                    <i class="fa fa-plus"></i> Añadir Vacuna
                  </button>
                </div>
              </div>
              <div class="box-body">
                <div class="table-responsive">
                  <table class="table table-bordered table-striped table-hover table-detalle" id="tablaMedicamentos">
                    <thead>
                      <tr>
                        <th>Vacuna / Biológico</th>
                        <th>Lote</th>
                        <th style="width: 100px;">Cantidad (Dosis)</th>
                        <th style="width: 100px;">Acciones</th>
                      </tr>
                    </thead>
                    <tbody id="cuerpoTablaMedicamentos">
                      <tr id="filaVacia">
                        <td colspan="4" class="text-center text-muted">Aún no se han añadido vacunas a este despacho.</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- EVIDENCIA (FOTO DE LA GUÍA / CAJA FRÍA) -->
              <div class="box box-default">
                <div class="box-header with-border">
                  <h3 class="box-title"><i class="fa fa-camera"></i> Evidencia del Despacho (Guía firmada, Caja Fría):</h3>
                </div>
                <div class="box-body">
                  <div class="btn-group" style="width: 100%; display: flex;">
                    <button type="button" class="btn btn-info" style="flex: 1;" data-toggle="modal" data-target="#modalEvidencia" onclick="prepararCaptura('camara')">
                      <i class="fa fa-camera"></i> Añadir con Cámara
                    </button>
                    <button type="button" class="btn btn-warning" style="flex: 1;" data-toggle="modal" data-target="#modalEvidencia" onclick="prepararCaptura('archivo')">
                      <i class="fa fa-upload"></i> Añadir Archivo
                    </button>
                  </div>
                  <div id="contenedor-miniaturas" style="display:flex; flex-wrap:wrap; gap:10px; margin-top:15px;"></div>
                  <input type="hidden" name="fotos_base64_array" id="fotos_base64_array" value="[]">
                </div>
              </div>

              <div class="box-footer text-right" style="background-color: transparent; border-top: 1px solid #f4f4f4; padding-top: 15px;">
                <button type="button" class="btn btn-second" id="abrirModalRegresar">Regresar</button>
                <button type="submit" class="btn btn-success" id="btnPrepararGuardado"><i class="fa fa-save"></i>Despachar</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </section>
</div>

<!-- MODAL AGREGAR VACUNA AL DESPACHO -->
<div class="modal" id="modalAgregarMedicamento" role="dialog" aria-hidden="true" data-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h4 class="modal-title"><i class="fa fa-share-square-o"></i> Despachar Vacuna</h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <label for="filtro_busqueda_rapida">Busqueda Rapida:</label>
              <input type="text" id="filtro_busqueda_rapida" name="filtro_busqueda_rapida" class="form-control" placeholder="Escriba nombre de la vacuna...">
            </div>
          </div>
          <br><br>
          <div class="col-sm-12 form-group">
            <label>Vacuna del Catálogo (*):</label>
            <div class="input-group">
              <select id="Id_descripcion_medicamento" name="Id_descripcion_medicamento" class="form-control select2" style="width: 100%;">
                <option value="">--- Seleccione una Vacuna ---</option>
                <!-- SIMULACIÓN -->
                <option value="1" data-nombre="Antipoliomielítica (VPI) [1 Dosis]" data-componentes="Virus Inactivados">Antipoliomielítica (VPI) - [1 Dosis]</option>
                <option value="2" data-nombre="Antisarampión (SRP) [10 Dosis]" data-componentes="Virus Atenuados">Antisarampión (SRP) - [10 Dosis]</option>
                <option value="3" data-nombre="Vacuna BCG [20 Dosis]" data-componentes="Bacteria Atenuada">Vacuna BCG - [20 Dosis]</option>
              </select>
              <span class="input-group-btn">
                <button class="btn btn-info" type="button" data-toggle="modal" data-target="#modalBúsquedaAvanzadaMedicamento" title="Búsqueda Avanzada" id="btnBusquedaAvanzada">
                  <i><img src="../../recursos/imagenes/iconos/buscar.png" style="width:10px; height:10px;"></i>
                </button>
              </span>
            </div>
          </div>

          <div class="col-sm-4 form-group mt-3">
            <label>Lote Disponible (*):</label>
            <div class="input-group">
              <select id="lote_seleccionado" class="form-control">
                <option value="">Seleccione la vacuna primero</option>
              </select>
            </div>
          </div>

          <div class="col-sm-4 form-group mt-3">
            <label>Existencia Actual:</label>
            <div class="input-group">
              <input type="text" id="existencia_actual" class="form-control" value="0" readonly disabled style="background-color: #f9f9f9;">
              <span class="input-group-btn">
                <button class="btn btn-info" type="button" id="infoLote" title="Informacion del lote" style="height: 34px;">
                  <i><img src="../../recursos/imagenes/iconos/info.png" style="width:10px; height:10px;"></i>
                </button>
              </span>
            </div>
          </div>

          <div class="col-sm-4 form-group mt-3">
            <label>Dosis a Despachar (*):</label>
            <input type="text" id="cantidad" class="form-control" placeholder="1" oninput="this.value = this.value.replace(/[^0-9]/g, ''); if(this.value === '0') this.value = '1';">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-success" id="btnConfirmarAgregarMedicamento"><i class="fa fa-check"></i> Añadir a la Lista</button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL BÚSQUEDA AVANZADA -->
<div class="modal" id="modalBúsquedaAvanzadaMedicamento" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title">Filtros de Búsqueda Avanzada</h5>
      </div>
      <div class="modal-body">
        <form id="formFiltroModal">
          <p class="text-muted">Complete uno o varios campos para refinar su búsqueda.</p>
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="filtro_nombre">Nombre (o ID):</label>
                <input type="text" id="filtro_nombre" name="filtro_nombre" class="form-control" placeholder="Escriba nombre...">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="filtro_presentacion">Presentación:</label>
                <select id="filtro_presentacion" name="filtro_presentacion" class="form-control">
                  <option value="">-- Todas --</option>
                  <option value="1">1 Dosis</option>
                  <option value="2">10 Dosis</option>
                  <option value="3">20 Dosis</option>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="filtro_principios">Componentes (contiene):</label>
                <input type="text" id="filtro_principios" name="filtro_principios" class="form-control" placeholder="Ej: Virus...">
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-second" id="btnLimpiarFiltros">Limpiar Filtros</button>
        <button type="button" class="btn btn-primary" id="btnAplicarFiltros">Aplicar Filtros</button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL EVIDENCIA -->
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
        <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btn-aceptar-evidencia" style="display:none;">Confirmar y Usar</button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL VISUALIZAR FOTO -->
<div class="modal fade" id="modalVisualizarFoto" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h4 class="modal-title" style="color:white;"><i class="fa fa-eye"></i> Vista Previa de Evidencia</h4>
      </div>
      <div class="modal-body text-center">
        <img id="img-vista-grande" src="" style="width: 100%; border-radius: 5px; box-shadow: 0 0 15px rgba(0,0,0,0.2);">
      </div>
    </div>
  </div>
</div>

<!-- MODAL AVISO -->
<div class="modal fade" id="avisoModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div id="headerDespachoAviso" class="modal-header bg-crimson">
        <h5 class="modal-title">Aviso Importante</h5>
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

<!-- MODAL REGRESAR -->
<div class="modal fade" id="modalRegresarInventario" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-crimson">
        <h5 class="modal-title">Confirmación de Regreso</h5>
      </div>
      <div class="modal-body">
        <p>Al hacer clic en "Abandonar Formulario", perderá todos los datos no guardados. ¿Desea continuar?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
        <a href="vacunacion_inventario_listado.php" class="btn btn-danger">Abandonar Formulario</a>
      </div>
    </div>
  </div>
</div>

<!-- MODAL GUARDAR -->
<div class="modal fade" id="modalDespachoGuardar" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-green">
        <h5 class="modal-title">Confirmación de Despacho</h5>
      </div>
      <div class="modal-body">
        <p>¿Está seguro de que desea procesar este despacho de vacunas al centro de salud seleccionado?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" id="confirmarGuardadoFinal">Aceptar</button>
      </div>
    </div>
  </div>
</div>

<?php
// include('modales/inventario/medicamentos_agregar_modal.php'); // COMENTADO
if (isset($conexion)) { $conexion->close(); }
include('includes/footer.php');
?>

<script>
  $(document).ready(function() {
    $.fn.modal.Constructor.prototype.enforceFocus = function() {};

    $('#infoLote').tooltip({ html: true, placement: 'right', title: 'Seleccione un lote primero' });

    if (typeof $.fn.select2 !== 'undefined') { $('.select2').select2(); }

    var listaDetalles = [];
    var editandoIndex = -1;
    const medicamentoSelectPrincipal = $('#Id_descripcion_medicamento');

    $(document).on('show.bs.modal', '.modal', function(event) {
      var zIndex = 1040 + (10 * $('.modal:visible').length);
      $(this).css('z-index', zIndex);
      setTimeout(function() { $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack'); }, 0);
    });

    $('.modal').on('click', '[data-dismiss="modal"]', function(e) {
      e.preventDefault();
      var $modal = $(this).closest('.modal');
      $modal.removeClass('in').addClass('out');
      if ($modal.data('timerCierre')) { clearTimeout($modal.data('timerCierre')); }
      var timerId = setTimeout(function() { $modal.modal('hide'); $modal.removeClass('out'); }, 400);
      $modal.data('timerCierre', timerId);
    });

    $('.modal').on('show.bs.modal', function() {
      if ($(this).data('timerCierre')) { clearTimeout($(this).data('timerCierre')); }
      $(this).removeClass('out');
    });

    $('.modal').on('hidden.bs.modal', function() {
      if ($('.modal:visible').length) { $('body').addClass('modal-open'); } else { $('body').removeClass('modal-open'); $('.modal-backdrop').remove(); }
    });

    window.mostrarAviso = function(mensaje) { $('#avisoTexto').html(mensaje); $('#avisoModal').modal('show'); }
    window.mostrarExito = function(mensaje) { $('#headerDespachoAviso').removeClass('bg-crimson').addClass('bg-green'); $('#avisoTexto').html(mensaje); $('#avisoModal').modal('show'); }

    function limpiarFormularioModal() {
      $('#Id_descripcion_medicamento').prop('disabled', false).val('').trigger('change');
      if (typeof $.fn.select2 !== 'undefined') { $('#Id_descripcion_medicamento').trigger('change.select2'); }
      $('#btnBusquedaAvanzada').prop('disabled', false);
      $('#lote_seleccionado').html('<option value="">Seleccione la vacuna primero</option>');
      $('#cantidad, #existencia_actual').val('').removeClass('input-error');
      $('#lote_seleccionado').removeClass('input-error');
    }

    $('#abrirModalRegresar').on('click', function() { $('#modalRegresarInventario').modal('show'); });

    // LÓGICA DE CAPTURA DE EVIDENCIA (Mantenida de tu original)
    let streamModal = null;
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

    let evidenciasArray = [];
    $('#btn-aceptar-evidencia').off('click').on('click', function() {
      const base64Str = $('#foto-previa-modal').attr('src');
      if (base64Str) { evidenciasArray.push(base64Str); actualizarMiniaturasUI(); }
      $('#modalEvidencia').modal('hide');
    });

    function actualizarMiniaturasUI() {
      const contenedor = $('#contenedor-miniaturas');
      contenedor.empty();
      evidenciasArray.forEach((foto, index) => {
        contenedor.append(`
          <div style="position: relative; width: 90px; height: 90px; border: 2px solid #ddd; border-radius: 6px; overflow: hidden; background: #f9f9f9;">
            <img src="${foto}" style="width: 100%; height: 100%; object-fit: cover; cursor: pointer;" class="miniatura-img" data-index="${index}" title="Clic para ver">
            <button type="button" class="btn btn-xs btn-danger btn-quitar-evidencia" data-index="${index}" style="position: absolute; top: 2px; right: 2px; padding: 2px 6px;">
              <i><img src="../../recursos/imagenes/iconos/Delete.png" style="width:20px; height:20px;"></i>
            </button>
          </div>
        `);
      });
      $('#fotos_base64_array').val(JSON.stringify(evidenciasArray));
    }

    $(document).on('click', '.btn-quitar-evidencia', function() {
      const index = $(this).data('index');
      evidenciasArray.splice(index, 1);
      actualizarMiniaturasUI();
    });

    $(document).on('click', '.miniatura-img', function() {
      const index = $(this).data('index');
      $('#img-vista-grande').attr('src', evidenciasArray[index]);
      $('#modalVisualizarFoto').modal('show');
    });

    $('#modalEvidencia').on('hidden.bs.modal', function() {
      if (streamModal) { streamModal.getTracks().forEach(t => t.stop()); streamModal = null; }
    });

    // LÓGICA DE FILTROS AVANZADOS (SIMULADA)
    const opcionesOriginalesMedicamentos = medicamentoSelectPrincipal.html();
    $('#btnLimpiarFiltros').on('click', function() {
      $('#formFiltroModal')[0].reset();
      medicamentoSelectPrincipal.html(opcionesOriginalesMedicamentos);
    });

    $('#btnAplicarFiltros').on('click', function(e) {
      e.preventDefault();
      const busquedaRapida = $('#filtro_busqueda_rapida').val().trim();
      const nombre_med = $('#filtro_nombre').val().trim();
      const principios = $('#filtro_principios').val().trim();

      if (busquedaRapida === "" && nombre_med === "" && principios === "") {
        mostrarAviso("Los campos de filtro están vacíos. Escriba alguna etiqueta para buscar.");
        return;
      }

      let opcionesFiltradas = '<option value="">--- Seleccione una Vacuna ---</option>';
      const $opciones = $(opcionesOriginalesMedicamentos).filter('option[value!=""]');
      let hayResultados = false;

      $opciones.each(function() {
        let texto = $(this).text().toLowerCase();
        let match = true;
        if (busquedaRapida && !texto.includes(busquedaRapida.toLowerCase())) match = false;
        if (nombre_med && !texto.includes(nombre_med.toLowerCase())) match = false;
        if (principios && !texto.includes(principios.toLowerCase())) match = false;
        if (match) { opcionesFiltradas += $(this)[0].outerHTML; hayResultados = true; }
      });

      if (hayResultados) {
        medicamentoSelectPrincipal.html(opcionesFiltradas);
        $('#modalBúsquedaAvanzadaMedicamento').modal('hide');
      } else {
        mostrarAviso("⚠️ No se encontraron vacunas que coincidan con los filtros aplicados.");
      }
    });

    // LÓGICA DE TAGS
    function inicializarTags(selector) {
      const $inputOriginal = $(selector);
      $inputOriginal.hide();
      const $contenedor = $('<div class="tags-input-container"></div>');
      const $inputFalso = $('<input type="text" class="tags-input-fake" placeholder="' + ($inputOriginal.attr('placeholder') || 'Escriba y presione Enter...') + '">');
      $contenedor.append($inputFalso);
      $inputOriginal.after($contenedor);
      let tagsArray = [];

      function renderizarTags() {
        $contenedor.find('.tag-badge').remove();
        tagsArray.forEach((tag, index) => {
          const $tag = $(`<span class="tag-badge bg-primary">${tag} <i class="fa fa-times remove-tag" data-index="${index}"></i></span>`);
          $inputFalso.before($tag);
        });
        $inputOriginal.val(tagsArray.join(' '));
      }

      $inputFalso.on('keypress', function(e) {
        if (e.which === 13) {
          e.preventDefault();
          let valor = $(this).val().trim();
          if (valor !== '' && !tagsArray.includes(valor)) {
            tagsArray.push(valor);
            $(this).val('');
            renderizarTags();
            $('#btnAplicarFiltros').click();
          } else if (valor === '') {
            $('#btnAplicarFiltros').click();
          }
        }
      });

      $inputFalso.on('keydown', function(e) {
        if (e.which === 8 && $(this).val() === '') {
          e.preventDefault();
          if (tagsArray.length > 0) {
            let ultimaEtiqueta = tagsArray.pop();
            $(this).val(ultimaEtiqueta);
            renderizarTags();
            if (tagsArray.length === 0) { $('#btnLimpiarFiltros').click(); } else { $('#btnAplicarFiltros').click(); }
          }
        }
      });

      $contenedor.on('click', '.remove-tag', function() {
        let index = $(this).data('index');
        tagsArray.splice(index, 1);
        renderizarTags();
        if (tagsArray.length === 0) { $('#btnLimpiarFiltros').click(); } else { $('#btnAplicarFiltros').click(); }
      });

      $contenedor.on('click', function() { $inputFalso.focus(); });
      $('#btnLimpiarFiltros').on('click', function() { tagsArray = []; renderizarTags(); });
    }

    inicializarTags('#filtro_busqueda_rapida');
    inicializarTags('#filtro_principios');

    // LÓGICA DEL MODAL AGREGAR VACUNA
    $('#btnAbrirModalAgregar').on('click', function() {
      editandoIndex = -1;
      limpiarFormularioModal();
      $('#btnConfirmarAgregarMedicamento').html('<i class="fa fa-check"></i> Añadir a la Lista');
      $('#modalAgregarMedicamento').modal('show');
    });

    // SIMULACIÓN DE CARGA DE LOTES AL SELECCIONAR VACUNA
    $('#Id_descripcion_medicamento').on('change', function(e, fromEdit) {
      var medicamentoId = $(this).val();

      if (!fromEdit) {
        $('#cantidad').val('').removeClass('input-error');
      }

      $('#lote_seleccionado').empty().append('<option value=""> Seleccione la vacuna </option>');
      $('#existencia_actual').val('');

      if (medicamentoId) {
        // SIMULACIÓN: En lugar de AJAX, quemamos los datos de los lotes
        let data = { lotes: [] };
        if (medicamentoId == '1') { // VPI
          data.lotes = [
            { lote: 'VPI-001', cantidad_actual: 150, fecha_fabricacion: '2025-01-01', fecha_vencimiento: '2026-01-01', nombre_proveedor: 'SUAF' },
            { lote: 'VPI-002', cantidad_actual: 50, fecha_fabricacion: '2024-06-01', fecha_vencimiento: '2024-12-01', nombre_proveedor: 'Ministerio' }
          ];
        } else if (medicamentoId == '2') { // SRP
          data.lotes = [
            { lote: 'SRP-991', cantidad_actual: 80, fecha_fabricacion: '2025-05-10', fecha_vencimiento: '2026-05-10', nombre_proveedor: 'Ministerio' }
          ];
        } else if (medicamentoId == '3') { // BCG
          data.lotes = [
            { lote: 'BCG-003', cantidad_actual: 200, fecha_fabricacion: '2025-01-01', fecha_vencimiento: '2026-11-15', nombre_proveedor: 'SUAF' }
          ];
        }

        if (data.lotes.length > 0) {
          $('#lote_seleccionado').empty().append('<option value="">-- Seleccione Lote --</option>');
          var existenciaTotalCalculada = 0;
          var primerLoteValido = null;

          $.each(data.lotes, function(index, item) {
            var diasFaltantes = "";
            var diffDays = 0;
            existenciaTotalCalculada += parseInt(item.cantidad_actual) || 0;

            if (item.fecha_vencimiento) {
              var fVenc = new Date(item.fecha_vencimiento);
              var hoy = new Date();
              var diffTime = fVenc - hoy;
              diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
              if (diffDays > 0) {
                diasFaltantes = " (Faltan " + diffDays + " días)";
                if (!primerLoteValido) primerLoteValido = item.lote;
              } else if (diffDays === 0) {
                diasFaltantes = " (Vence Hoy)";
              } else {
                diasFaltantes = " (Vencido hace " + Math.abs(diffDays) + " días)";
              }
            }

            $('#lote_seleccionado').append('<option value="' + item.lote + '" ' +
              'data-stock="' + item.cantidad_actual + '" ' +
              'data-vencido="' + (diffDays <= 0 ? 'si' : 'no') + '" ' +
              'data-fab="' + (item.fecha_fabricacion ? item.fecha_fabricacion : 'N/A') + '" ' +
              'data-venc="' + (item.fecha_vencimiento ? item.fecha_vencimiento : 'N/A') + '" ' +
              'data-prov="' + (item.nombre_proveedor ? item.nombre_proveedor : 'Sin Proveedor') + '">' +
              'Lote: ' + item.lote + ' - Stock: ' + item.cantidad_actual + diasFaltantes +
              '</option>');
          });

          $('#existencia_actual').val(existenciaTotalCalculada);

          if (!fromEdit && primerLoteValido) {
            $('#lote_seleccionado').val(primerLoteValido).trigger('change');
          }
        } else {
          $('#existencia_actual').val('0');
          $('#lote_seleccionado').append('<option value="">Sin stock disponible</option>');
        }
      }
    });

    // TOOLTIP DEL LOTE
    $('#lote_seleccionado').on('change', function() {
      var option = $(this).find(':selected');
      var stockLote = option.data('stock');
      var nombreLote = option.val();

      if ($(this).val() !== "") {
        var fab = option.data('fab');
        var venc = option.data('venc');
        var prov = option.data('prov');

        var infoHtml = "<div style='text-align:left; font-size: 13px;'>" +
          "<b style='color:#00ffff;'>Lote N°: " + nombreLote + "</b><br>" +
          "<b>Proveedor:</b> " + prov + "<br>" +
          "<b>F. Fabricación:</b> " + fab + "<br>" +
          "<b>F. Vencimiento:</b> " + venc + "<br>" +
          "<b>Stock Físico (Este lote):</b> <span style='color:#00ff00; font-weight:bold;'>" + stockLote + " Dosis</span>" +
          "</div>";

        $('#infoLote').attr('data-original-title', infoHtml).tooltip('fixTitle');
      } else {
        $('#infoLote').attr('data-original-title', 'Seleccione un lote primero').tooltip('fixTitle');
      }
    });

    // VALIDAR ANTES DE AÑADIR A LA TABLA
    $('#btnConfirmarAgregarMedicamento').on('click', function() {
      var id_med = $('#Id_descripcion_medicamento').val();
      var nombre_med = $('#Id_descripcion_medicamento option:selected').data('nombre');
      var componentes = $('#Id_descripcion_medicamento option:selected').data('componentes');
      var lote = $('#lote_seleccionado').val();
      var existencia = parseInt($('#existencia_actual').val());
      var cant = parseInt($('#cantidad').val());

      $('.modal-body input, .modal-body select').removeClass('input-error');

      if (!id_med) { $('#Id_descripcion_medicamento').addClass('input-error'); mostrarAviso('Seleccione una vacuna.'); return; }
      if (!lote) { $('#lote_seleccionado').addClass('input-error'); mostrarAviso('Debe seleccionar un lote con disponibilidad.'); return; }
      if (isNaN(cant) || cant <= 0) { $('#cantidad').addClass('input-error'); mostrarAviso('La cantidad a despachar debe ser mayor a 0.'); return; }
      if (cant > existencia) { $('#cantidad').addClass('input-error'); mostrarAviso('La cantidad supera la existencia actual en este lote (' + existencia + ').'); return; }

      var esVencido = $('#lote_seleccionado option:selected').data('vencido');
      if (esVencido === 'si') {
        $('#lote_seleccionado').addClass('input-error');
        mostrarAviso('No puede despachar una vacuna de un lote vencido a un centro de salud.');
        return;
      }

      var existeIndex = -1;
      for (var i = 0; i < listaDetalles.length; i++) {
        if (listaDetalles[i].id_medicamento === id_med && listaDetalles[i].lote === lote) {
          existeIndex = i; break;
        }
      }

      if (existeIndex !== -1 && existeIndex !== editandoIndex) {
        mostrarAviso('Esta vacuna de este Lote ya está en la lista de despacho.');
        return;
      }

      var nuevoItem = {
        id_medicamento: id_med,
        nombre_medicamento: nombre_med,
        componentes: componentes,
        lote: lote,
        cantidad: cant
      };

      if (editandoIndex !== -1) {
        listaDetalles[editandoIndex] = nuevoItem;
        editandoIndex = -1;
      } else {
        listaDetalles.push(nuevoItem);
      }

      actualizarTablaDetalles();
      $('#modalAgregarMedicamento').modal('hide');
    });

    function actualizarTablaDetalles() {
      var tbody = $('#cuerpoTablaMedicamentos');
      tbody.empty();

      if (listaDetalles.length === 0) {
        tbody.append('<tr><td colspan="4" class="text-center text-muted">Aún no se han añadido vacunas.</td></tr>');
      } else {
        $.each(listaDetalles, function(index, item) {
          var infoComponentes = "";
          if (item.componentes && item.componentes !== 'Sin principios activos') {
            infoComponentes = '<br><small class="text-muted"><i>(' + item.componentes + ')</i></small>';
          }

          tbody.append(
            '<tr>' +
            '<td><span style="font-size:15px;"><strong>' + item.nombre_medicamento + '</strong></span>' + infoComponentes + '</td>' +
            '<td>' + item.lote + '</td>' +
            '<td><span class="badge bg-green" style="font-size:14px;">' + item.cantidad + '</span></td>' +
            '<td>' +
            '<button type="button" class="btn btn-warning btn-xs btn-editar-fila" data-index="' + index + '"><i><img src="../../recursos/imagenes/iconos/editar.png" style="width:10px; height:10px;"></i></button> ' +
            '<button type="button" class="btn btn-danger btn-xs btn-eliminar-fila" data-index="' + index + '"><i><img src="../../recursos/imagenes/iconos/Delete.png" style="width:10px; height:10px;"></i></button>' +
            '</td>' +
            '</tr>'
          );
        });
      }
      $('#detalle_medicamentos').val(JSON.stringify(listaDetalles));
    }

    $('#cuerpoTablaMedicamentos').off('click', '.btn-editar-fila').on('click', '.btn-editar-fila', function() {
      var index = $(this).data('index');
      var item = listaDetalles[index];
      editandoIndex = index;

      $('#Id_descripcion_medicamento').removeClass('input-error');
      $('#lote_seleccionado').removeClass('input-error').empty();
      $('#cantidad, #existencia_actual').val('').removeClass('input-error');
      $('#Id_descripcion_medicamento').prop('disabled', false);
      if (typeof $.fn.select2 !== 'undefined') { $('#Id_descripcion_medicamento').trigger('change.select2'); }

      $('#cantidad').val(item.cantidad);
      $('#Id_descripcion_medicamento').val(item.id_medicamento).trigger('change', [true]);

      var intentos = 0;
      var checkLotes = setInterval(function() {
        if ($('#lote_seleccionado option[value="' + item.lote + '"]').length > 0) {
          $('#lote_seleccionado').val(item.lote).trigger('change');
          $('#lote_seleccionado').prop('disabled', true);
          if (typeof $.fn.select2 !== 'undefined') { $('#lote_seleccionado').trigger('change.select2'); }
          clearInterval(checkLotes);
        }
        intentos++;
        if (intentos > 30) clearInterval(checkLotes);
      }, 200);

      $('#btnConfirmarAgregarMedicamento').html('<i class="fa fa-save"></i> Guardar Cambios');
      $('#modalAgregarMedicamento').modal('show');
    });

    $('#cuerpoTablaMedicamentos').on('click', '.btn-eliminar-fila', function() {
      var index = $(this).data('index');
      listaDetalles.splice(index, 1);
      actualizarTablaDetalles();
    });

    // VALIDACIÓN FINAL DEL FORMULARIO
    $('#formularioDespacho').on('submit', function(e) {
      e.preventDefault();
      $('.form-control').removeClass('input-error');
      var hayErrores = false;

      if (!$('#destino_ambulatorio').val()) { $('#destino_ambulatorio').addClass('input-error'); hayErrores = true; }
      if (!$('#numero_guia').val().trim()) { $('#numero_guia').addClass('input-error'); hayErrores = true; }
      if (!$('#responsable_receptor').val().trim()) { $('#responsable_receptor').addClass('input-error'); hayErrores = true; }
      if (!$('#temperatura_transporte').val()) { $('#temperatura_transporte').addClass('input-error'); hayErrores = true; }

      if (hayErrores) {
        mostrarAviso("Faltan datos requeridos. Complete todos los campos marcados en rojo en la sección de Datos del Destino.");
        return;
      }

      if (listaDetalles.length === 0) {
        $('#btnAbrirModalAgregar').removeClass('btn-primary').addClass('btn-error-sombreado');
        mostrarAviso("Debe añadir al menos una vacuna a la lista para procesar el despacho.");
        return;
      }

      $('#Id_descripcion_medicamento').prop('disabled', false);
      $('#modalDespachoGuardar').modal('show');
    });

    $('#confirmarGuardadoFinal').on('click', function() {
      $('#modalDespachoGuardar').modal('hide');
      // SIMULACIÓN: Aquí se enviaría el formulario real
      // $('#formularioDespacho').off('submit').submit();
      mostrarExito("✅ <b>Despacho de Vacunas registrado (Simulado).</b><br><br>En la versión real, esto restaría el stock y generaría la guía de despacho.");
      setTimeout(function() {
        window.location.href = 'vacunacion_inventario_listado.php';
      }, 2500);
    });

    // Simular que el formulario se cargó con un tipo por defecto
    $('#tipo_despacho').trigger('change');
  });
</script>