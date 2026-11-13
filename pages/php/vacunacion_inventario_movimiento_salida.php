<?php
include("../../cfg/conexion.php");
$operacion_actual = isset($_GET['op']) ? $_GET['op'] : 'ajuste_salida';

// SIMULACIÓN: Cuando tengas la BD real, descomenta esto.
/*
$sql_ultimo_id = "SELECT MAX(Id_detalle_inventario) AS ultimo FROM detalle_inventario";
$resultado_id = $conexion->query($sql_ultimo_id);
$row_id = $resultado_id->fetch_assoc();
$proximo_id = ($row_id['ultimo'] ? $row_id['ultimo'] : 0) + 1;
$numero_proyectado = str_pad($proximo_id, 6, "0", STR_PAD_LEFT);
*/
$numero_proyectado = "000026"; // Simulado

include('includes/headerNav2.php');
verificarAcceso('Generar Salidas de Inventario');
?>

<head>
  <title>Inventario | Registrar Pérdida / Merma de Vacunas</title>
</head>

<style>
  .table-responsive { max-height: 220px; overflow-y: auto; }
  .table-detalle thead th { position: sticky; top: 0; background-color: #f4f4f4; z-index: 1; }
  .tags-input-container { display: flex; flex-wrap: wrap; gap: 5px; padding: 4px; border: 1px solid #d2d6de; background-color: #fff; min-height: 34px; cursor: text; }
  .tags-input-container .tag-badge { display: inline-flex; align-items: center; padding: 4px 8px; border-radius: 4px; font-size: 12px; color: white; }
  .tags-input-container .tag-badge i { margin-left: 6px; cursor: pointer; font-size: 10px; }
  .tags-input-fake { border: none; outline: none; flex-grow: 1; min-width: 150px; padding: 2px; }
  .content-wrapper { min-height: 100vh !important; overflow-y: auto; }
  .main-sidebar { position: fixed !important; height: 100%; }
  .table-detalle th { background-color: #f4f4f4; text-align: center; }
  .table-detalle td { text-align: center; vertical-align: middle !important; }
  .row-vence-pronto { background-color: #fff3cd !important; }
  .area-trabajo-blanca { background-color: #ffffff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08); margin-bottom: 20px; }
  input[readonly], input[disabled] { background-color: #eeeeee !important; cursor: not-allowed; }
  .text-green-bold { color: #00a65a; font-weight: bold; font-size: 1.2em; }
</style>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Registrar Pérdida / Merma de Vacunas</h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="#"><i class="fa fa-medkit"></i>Inmunización</a></li>
      <li class="active"><a href="#"><i class="fa fa-minus-circle"></i>Ajuste de Inventario</a></li>
    </ol>
  </section>

  <section class="content">
    <form id="formularioSalida" method="POST" action="../../cfg/movimientos_inventario.php" novalidate autocomplete="off">
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

            <!-- DATOS DEL AJUSTE -->
            <div class="box box-warning">
              <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-file-text-o"></i> Datos del ajuste:</h3>
              </div>
              <div class="box-body">
                <div class="row">
                  <div class="col-sm-3 form-group">
                    <label>Motivo del ajuste (*):</label>
                    <select name="id_tipo_movimiento" id="id_tipo_movimiento" class="form-control" required>
                      <option value="">-- Seleccione un motivo --</option>
                      <!-- IDs de tu tabla tipo_movimiento -->
                      <option value="3" data-tipo="resta">Salida por Vencimiento</option>
                      <option value="4" data-tipo="resta">Salida por Dañado (Cadena de Frío)</option>
                      <option value="5" data-tipo="resta">Salida por Pérdida o Robo</option>
                      <option value="7" data-tipo="resta">Ajuste por Cuadre (Salida / Faltante)</option>
                      <option value="6" data-tipo="suma">Ajuste por Cuadre (Entrada / Sobrante)</option>
                    </select>
                  </div>

                  <input type="hidden" name="tipo_ajuste" id="tipo_ajuste" value="resta">

                  <div class="col-sm-3">
                    <label>Encargado (*):</label>
                    <select name="receptor" id="receptor" class="form-control" required>
                      <option value="">--- Seleccione el encargado ---</option>
                      <!-- SIMULACIÓN: Aquí irían los usuarios de la BD -->
                      <option value="1" selected>Lic. María Rodríguez</option>
                      <option value="2">Enf. Carlos Pérez</option>
                    </select>
                  </div>

                  <div class="col-sm-3 form-group" id="med_group_fecha_recepcion">
                    <label>Fecha y hora de ajuste (*):</label>
                    <div class="input-group">
                      <input type="date" id="fecha_recepcion" name="fecha_recepcion" class="form-control" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" onkeydown="return false;" style="width: 100%;">
                      <div class="input-group-btn" style="width: 100%;">
                        <input type="time" id="hora_recepcion" name="hora_recepcion" class="form-control" value="<?php date_default_timezone_set('America/Caracas'); echo date('H:i'); ?>">
                      </div>
                    </div>
                  </div>

                  <div class="col-sm-3 form-group">
                    <label>Observaciones / Justificación (*):</label>
                    <input type="text" name="observaciones_generales" id="observaciones_generales" class="form-control" placeholder="Ej: Frascos rotos, falla eléctrica en la nevera..." required>
                  </div>
                </div>
              </div>
            </div>

            <!-- VACUNAS A DAR DE BAJA -->
            <div class="box box-danger">
              <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-syringe"></i> Vacunas a dar de baja:</h3>
                <div class="box-tools pull-right" style="margin-left: 5px;">
                  <button type="button" class="btn btn-primary btn-sm" id="btnAbrirModalAgregar" style="width:200px;">
                    <i class="fa fa-plus"></i> Añadir Vacuna
                  </button>
                  <p></p>
                  <button type="button" class="btn btn-danger btn-sm" id="btnAñadirVencidos" style="display:none; margin-right: 5px; width:200px;">
                    <i class="fa fa-exclamation-triangle"></i> Añadir Vacunas Vencidas
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
                        <th>F. Vencimiento</th>
                        <th>Cantidad (Dosis)</th>
                        <th>Observaciones</th>
                        <th>Acciones</th>
                      </tr>
                    </thead>
                    <tbody id="cuerpoTablaMedicamentos">
                      <tr id="filaVacia">
                        <td colspan="6" class="text-center text-muted">Aún no se han añadido vacunas para dar de baja.</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- EVIDENCIA -->
            <div class="box box-default">
              <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-camera"></i> Evidencia del ajuste (Acta, Foto del daño, Cadena de Frío):</h3>
              </div>
              <div class="box-body">
                <div class="btn-group" style="width: 100%; display: flex;">
                  <button type="button" class="btn btn-info" style="flex: 1;" data-toggle="modal" data-target="#modalEvidencia" onclick="prepararCaptura('camara')">
                    <i class="fa fa-camera"></i> <span id="txt-btn-camara">Añadir con Cámara</span>
                  </button>
                  <button type="button" class="btn btn-warning" style="flex: 1;" data-toggle="modal" data-target="#modalEvidencia" onclick="prepararCaptura('archivo')">
                    <i class="fa fa-upload"></i> <span id="txt-btn-subir">Añadir Archivo</span>
                  </button>
                </div>
                <div id="contenedor-miniaturas" style="display:flex; flex-wrap:wrap; gap:10px; margin-top:15px;"></div>
                <input type="hidden" name="fotos_base64_array" id="fotos_base64_array" value="[]">
              </div>

              <div class="box-footer text-right" style="background-color: transparent; border-top: 1px solid #f4f4f4; padding-top: 15px;">
                <button type="button" class="btn btn-second" id="abrirModalRegresar">Regresar</button>
                <button type="submit" class="btn btn-danger" id="btnPrepararGuardado"><i class="fa fa-trash"></i>Procesar</button>
              </div>
            </div>

          </div>
        </div>
      </div>
    </form>
  </section>
</div>

<!-- MODAL AGREGAR VACUNA A DAR DE BAJA -->
<div class="modal fade" id="modalAgregarMedicamento" role="dialog" aria-hidden="true" data-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h4 class="modal-title"><i class="fa fa-minus-circle"></i> Seleccionar Lote a Dar de Baja</h4>
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
              <select id="Id_descripcion_medicamento" class="form-control">
                <option value="">--- Seleccione una Vacuna ---</option>
                <!-- SIMULACIÓN -->
                <option value="1" data-nombre="Antipoliomielítica (VPI) [1 Dosis]" data-componentes="Virus Inactivados">Antipoliomielítica (VPI) - [1 Dosis]</option>
                <option value="2" data-nombre="Antisarampión (SRP) [10 Dosis]" data-componentes="Virus Atenuados">Antisarampión (SRP) - [10 Dosis]</option>
                <option value="3" data-nombre="Vacuna BCG [20 Dosis]" data-componentes="Bacteria Atenuada">Vacuna BCG - [20 Dosis]</option>
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
            <div class="input-group">
              <select id="lista_lotes" class="form-control">
                <option value="">-- Seleccione un lote --</option>
              </select>
              <span class="input-group-btn">
                <button class="btn btn-info" type="button" id="infoLote" title="Informacion del lote" style="height: 34px;">
                  <i><img src="../../recursos/imagenes/iconos/info.png" style="width:10px; height:10px;"></i>
                </button>
              </span>
            </div>
          </div>

          <div class="col-sm-4 form-group mt-3">
            <label>F. Vencimiento:</label>
            <input type="text" id="fecha_vencimiento_readonly" class="form-control" readonly disabled>
          </div>

          <div class="col-sm-4 form-group mt-3">
            <label>Dosis a Dar de Baja (*):</label>
            <input type="text" id="cantidad_baja" class="form-control" placeholder="Solo números">
            <small id="max_stock_help" class="form-text text-muted" style="color:#d9534f; font-weight:bold;"></small>
          </div>

          <div class="col-sm-12 form-group mt-3">
            <label>Observación:</label>
            <textarea id="observaciones" name="observaciones" class="form-control" rows="2" placeholder="Ej: Se rompió la cadena de frío, frascos vencidos..."></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="btnConfirmarAgregarMedicamento"><i class="fa fa-check"></i> Añadir a la Lista</button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL BÚSQUEDA AVANZADA (SIMPLIFICADO) -->
<div class="modal fade" id="modalBúsquedaAvanzadaMedicamento" tabindex="-1" role="dialog" aria-labelledby="modalBúsquedaAvanzadaMedicamentoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" id="modalBúsquedaAvanzadaMedicamentoLabel">Filtros de Búsqueda Avanzada</h5>
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
        <h4 class="modal-title"><i class="fa fa-file-image-o"></i> Gestionar Evidencia</h4>
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
        <button type="button" class="btn btn-primary" id="btn-aceptar-evidencia" style="display:none;">Confirmar</button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL VISUALIZAR FOTO -->
<div class="modal fade" id="modalVisualizarFoto" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-navy">
        <h4 class="modal-title"><i class="fa fa-eye"></i> Vista Previa de Evidencia</h4>
      </div>
      <div class="modal-body text-center">
        <img id="img-vista-grande" src="" style="width: 100%; border-radius: 5px; box-shadow: 0 0 15px rgba(0,0,0,0.2);">
      </div>
    </div>
  </div>
</div>

<!-- MODAL CONFIRMAR VENCIDOS -->
<div class="modal fade" id="modalConfirmarVencidos" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-crimson">
        <h5 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Confirmación de Vencidos</h5>
      </div>
      <div class="modal-body">
        <p>¿Está seguro de querer añadir <strong>TODAS</strong> las vacunas vencidas (con stock mayor a 0) a la lista?</p>
        <p class="text-danger"><small><i class="fa fa-info-circle"></i> Nota: Esta acción sobrescribirá cualquier vacuna que ya haya añadido a la lista actual.</small></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="btnConfirmarVencidosAction"><i class="fa fa-check"></i>Aceptar</button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL AVISO -->
<div class="modal fade" id="avisoModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div id="headerAviso" class="modal-header bg-crimson">
        <h5 class="modal-title">Aviso Importante</h5>
      </div>
      <div class="modal-body">
        <p id="avisoTexto"></p>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button></div>
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
<div class="modal fade" id="modalSalidaGuardar" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div id="headerSalidaGuardar" class="modal-header bg-green">
        <h5 class="modal-title">Confirmación de Guardado</h5>
      </div>
      <div class="modal-body">
        <p>¿Está seguro de procesar este ajuste de inventario? Esta acción modificará las dosis disponibles en el sistema.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="confirmarGuardadoFinal">Aceptar</button>
      </div>
    </div>
  </div>
</div>

<?php include('includes/footer.php'); ?>

<script>
  let listaDetalles = [];
  let editandoIndex = -1;
  let streamModal = null;
  const hoy = new Date().toISOString().split('T')[0];

  window.mostrarAviso = function(mensaje) {
    $('#headerAviso').removeClass('bg-green').addClass('bg-crimson');
    $('#avisoTexto').html(mensaje);
    $('#avisoModal').modal('show');
  }

  window.mostrarExito = function(mensaje) {
    $('#headerAviso').removeClass('bg-crimson').addClass('bg-green');
    $('#avisoTexto').html(mensaje);
    $('#avisoModal').modal('show');
  }

  $(document).ready(function() {
    // LÓGICA DE EVIDENCIA (Mantenida de tu original)
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

    // LÓGICA DE MOTIVO
    $('#id_tipo_movimiento').on('change', function() {
      if (listaDetalles.length > 0) {
        listaDetalles = [];
        actualizarTablaDetalles();
      }

      if ($(this).val() === "3") {
        $('#btnAñadirVencidos').fadeIn();
      } else {
        $('#btnAñadirVencidos').fadeOut();
      }

      const tipo = $(this).find(':selected').data('tipo');
      $('#tipo_ajuste').val(tipo);

      if (tipo === 'suma') {
        $('#confirmarGuardadoFinal').removeClass('btn-danger').addClass('btn-success').text('Procesar Entrada');
        $('#btnPrepararGuardado').removeClass('btn-danger').addClass('btn-success').html('<i class="fa fa-plus"></i> Procesar Entrada');
      } else {
        $('#confirmarGuardadoFinal').removeClass('btn-success').addClass('btn-danger').text('Procesar Baja');
        $('#btnPrepararGuardado').removeClass('btn-success').addClass('btn-danger').html('<i class="fa fa-trash"></i> Procesar Baja');
        $('#headerSalidaGuardar').removeClass('bg-green').addClass('bg-crimson');
      }
    });

    // SIMULACIÓN DE AÑADIR VENCIDOS
    $('#btnAñadirVencidos').on('click', function() {
      $('#modalConfirmarVencidos').modal('show');
    });

    $('#btnConfirmarVencidosAction').on('click', function() {
      $('#modalConfirmarVencidos').modal('hide');
      
      // SIMULACIÓN: Aquí se cargarían los lotes vencidos de la BD
      listaDetalles = [
        { id_medicamento: '1', nombre_medicamento: 'Antipoliomielítica (VPI)', componentes: 'Virus Inactivados', lote_id: '2', lote: 'VPI-002', fecha_vencimiento: '2024-12-01', cantidad: 50, observacion: 'Lote vencido automáticamente' },
        { id_medicamento: '2', nombre_medicamento: 'Antisarampión (SRP)', componentes: 'Virus Atenuados', lote_id: '5', lote: 'SRP-881', fecha_vencimiento: '2024-11-15', cantidad: 30, observacion: 'Lote vencido automáticamente' }
      ];
      actualizarTablaDetalles();
      mostrarExito("Éxito: Se añadieron 2 lotes vencidos a la lista (Simulado).");
    });

    // LÓGICA DE FILTROS AVANZADOS (SIMULADA)
    const medicamentoSelectPrincipal = $('#Id_descripcion_medicamento');
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

    // LÓGICA DEL MODAL
    function limpiarFormularioModal() {
      $('#Id_descripcion_medicamento').val('').trigger('change');
      $('#lista_lotes').empty().append('<option value="">-- Seleccione un lote --</option>');
      $('#fecha_vencimiento_readonly').val('');
      $('#cantidad_baja').val('').removeClass('input-error');
      $('#max_stock_help').text('');
      $('#observaciones').val('');
    }

    $('#btnAbrirModalAgregar').on('click', function() {
      editandoIndex = -1;
      limpiarFormularioModal();
      $('#btnConfirmarAgregarMedicamento').html('<i class="fa fa-check"></i> Añadir a la Lista');
      $('#modalAgregarMedicamento').modal('show');
    });

    $('#cantidad_baja').on('input', function() {
      this.value = this.value.replace(/[^0-9]/g, '');
      if (this.value === '0') this.value = '1';
    });

    $('#infoLote').tooltip({ html: true, placement: 'right', title: 'Seleccione un lote primero' });

    // SIMULACIÓN DE CARGA DE LOTES
    $('#Id_descripcion_medicamento').on('change', function() {
      const medId = $(this).val();
      let $lotes = $('#lista_lotes').empty().append('<option value="">-- Cargando... --</option>');
      $('#fecha_vencimiento_readonly').val('');
      $('#max_stock_help').text('');

      if (medId) {
        // SIMULACIÓN: En lugar de AJAX, quemamos los datos
        let data = { lotes: [] };
        if (medId == '1') { // VPI
          data.lotes = [
            { Id: '1', lote: 'VPI-001', cantidad_actual: 150, fecha_fabricacion: '2025-01-01', fecha_vencimiento: '2026-01-01', dias_restantes: 60, nombre_proveedor: 'SUAF' },
            { Id: '2', lote: 'VPI-002', cantidad_actual: 50, fecha_fabricacion: '2024-06-01', fecha_vencimiento: '2024-12-01', dias_restantes: -5, nombre_proveedor: 'Ministerio' }
          ];
        } else if (medId == '2') { // SRP
          data.lotes = [
            { Id: '3', lote: 'SRP-991', cantidad_actual: 80, fecha_fabricacion: '2025-05-10', fecha_vencimiento: '2026-05-10', dias_restantes: 180, nombre_proveedor: 'Ministerio' }
          ];
        } else if (medId == '3') { // BCG
          data.lotes = [
            { Id: '4', lote: 'BCG-003', cantidad_actual: 200, fecha_fabricacion: '2025-01-01', fecha_vencimiento: '2026-11-15', dias_restantes: 365, nombre_proveedor: 'SUAF' }
          ];
        }

        $lotes.empty().append('<option value="">-- Seleccione un lote --</option>');
        if (data.lotes.length > 0) {
          data.lotes.forEach(l => {
            let estilo = l.dias_restantes <= 0 ? "color:red;" : "";
            let textoDisp = "";
            if (l.dias_restantes < 0) { textoDisp = " (VENCIDO)"; } 
            else if (l.dias_restantes === 0) { textoDisp = " (VENCE HOY)"; } 
            else { textoDisp = ` (Faltan ${l.dias_restantes} días)`; }

            let prov = l.nombre_proveedor ? l.nombre_proveedor : 'Sin Proveedor';
            let fab = l.fecha_fabricacion ? l.fecha_fabricacion : 'N/A';

            $lotes.append(`<option value="${l.Id}" data-lote="${l.lote}" data-venc="${l.fecha_vencimiento}" data-fab="${fab}" data-prov="${prov}" data-cant="${l.cantidad_actual}" data-dias="${l.dias_restantes}" style="${estilo}">${l.lote} - Stock: ${l.cantidad_actual} ${textoDisp}</option>`);
          });
        } else {
          $lotes.empty().append('<option value="">No hay stock de esta vacuna</option>');
        }
      }
    });

    $('#lista_lotes').on('change', function() {
      const option = $(this).find('option:selected');
      if (option.val() !== "") {
        let diasRestantesData = option.data('dias');
        let textoDias = diasRestantesData < 0 ? ' (VENCIDO)' : (diasRestantesData === 0 ? ' (VENCE HOY)' : ` (Faltan ${diasRestantesData} días)`);

        $('#fecha_vencimiento_readonly').val(option.data('venc'));
        const tipoActual = $('#id_tipo_movimiento').find(':selected').data('tipo') || 'resta';
        
        if (tipoActual === 'suma') {
          $('#max_stock_help').text(`Stock actual: ${option.data('cant')} dosis. (Ingrese cantidad a sumar)`);
        } else {
          $('#max_stock_help').text(`Máximo disponible para baja: ${option.data('cant')} dosis.`);
        }

        var infoHtml = "<div style='text-align:left; font-size: 12px;'>" +
          "<b>Proveedor:</b> " + option.data('prov') + "<br>" +
          "<b>F. Fabricación:</b> " + option.data('fab') + "<br>" +
          "<b>F. Vencimiento:</b> " + option.data('venc') + "<br>" +
          "<b>Stock Físico:</b> " + option.data('cant') + " dosis" +
          "</div>";

        $('#infoLote').attr('data-original-title', infoHtml).tooltip('fixTitle');
      } else {
        $('#fecha_vencimiento_readonly').val('');
        $('#max_stock_help').text('');
        $('#infoLote').attr('data-original-title', 'Seleccione un lote primero').tooltip('fixTitle');
      }
    });

    // CONFIRMAR AGREGAR A LA LISTA
    $('#btnConfirmarAgregarMedicamento').on('click', function() {
      const id_med = $('#Id_descripcion_medicamento').val();
      const nombre_med = $('#Id_descripcion_medicamento option:selected').data('nombre') || $('#Id_descripcion_medicamento option:selected').text();
      const componentes = $('#Id_descripcion_medicamento option:selected').data('componentes') || '';

      const lote_id = $('#lista_lotes').val();
      const lote_nombre = $('#lista_lotes option:selected').data('lote');
      const f_venc = $('#lista_lotes option:selected').data('venc');
      const max_cant = parseInt($('#lista_lotes option:selected').data('cant') || 0);

      const cant_baja = parseInt($('#cantidad_baja').val());
      const observaciones = $('#observaciones').val();

      $('.modal-body .form-control').removeClass('input-error');

      if (!id_med) { $('#Id_descripcion_medicamento').addClass('input-error'); mostrarAviso('Seleccione una vacuna.'); return; }
      if (!lote_id) { $('#lista_lotes').addClass('input-error'); mostrarAviso('Seleccione un lote.'); return; }
      if (isNaN(cant_baja) || cant_baja <= 0) { $('#cantidad_baja').addClass('input-error'); mostrarAviso('Ingrese una cantidad válida.'); return; }

      const tipoActual = $('#id_tipo_movimiento').find(':selected').data('tipo') || 'resta';
      if (tipoActual === 'resta' && cant_baja > max_cant) {
        $('#cantidad_baja').addClass('input-error');
        mostrarAviso(`La cantidad supera el stock físico de este lote (${max_cant}).`);
        return;
      }

      const existeIndex = listaDetalles.findIndex(item => item.id_medicamento === id_med && item.lote_id === lote_id);
      if (existeIndex !== -1 && existeIndex !== editandoIndex) {
        mostrarAviso('Esta vacuna de este Lote ya está en la lista de bajas. Modifíquela directamente usando el botón de editar.');
        return;
      }

      const nuevoItem = {
        id_medicamento: id_med,
        nombre_medicamento: nombre_med,
        componentes: componentes,
        lote_id: lote_id,
        lote: lote_nombre,
        fecha_vencimiento: f_venc,
        cantidad: cant_baja,
        observacion: observaciones
      };

      if (editandoIndex !== -1) {
        listaDetalles[editandoIndex] = nuevoItem;
      } else {
        listaDetalles.push(nuevoItem);
      }

      actualizarTablaDetalles();
      $('#modalAgregarMedicamento').modal('hide');
    });

    function actualizarTablaDetalles() {
      const tbody = $('#cuerpoTablaMedicamentos');
      tbody.empty();

      if (listaDetalles.length === 0) {
        tbody.append('<tr id="filaVacia"><td colspan="6" class="text-center text-muted">Aún no se han añadido vacunas.</td></tr>');
      } else {
        const tipoAjuste = $('#tipo_ajuste').val() || 'resta';
        const signo = tipoAjuste === 'suma' ? '+' : '-';
        const claseBadge = tipoAjuste === 'suma' ? 'bg-green' : 'bg-crimson';

        listaDetalles.forEach((item, index) => {
          let rowClass = "";
          let diffDias = (new Date(item.fecha_vencimiento) - new Date(hoy)) / (1000 * 60 * 60 * 24);
          if (diffDias <= 0) rowClass = "row-vence-pronto";

          tbody.append(`
              <tr class="${rowClass}">
                <td style="text-align: left;">
                  ${item.nombre_medicamento}
                  <br><small class="text-muted" style="font-size: 11px;"><i>${item.componentes}</i></small>
                </td>
                <td><strong>${item.lote}</strong></td>
                <td>${item.fecha_vencimiento} ${diffDias <= 0 ? ' <i class="fa fa-exclamation-triangle text-danger" title="Lote Vencido"></i>' : ''}</td>
                <td><span class="badge ${claseBadge}" style="font-size:14px;">${signo} ${item.cantidad}</span></td>
                <td style="text-align: left;">${item.observacion}</td>
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

      $('#Id_descripcion_medicamento').val(item.id_medicamento).trigger('change');
      $('#observaciones').val(item.observacion);

      setTimeout(() => {
        $('#lista_lotes').val(item.lote_id).trigger('change');
        setTimeout(() => {
          $('#cantidad_baja').val(item.cantidad);
        }, 100);
      }, 600);

      $('#btnConfirmarAgregarMedicamento').html('<i class="fa fa-save"></i> Guardar Cambios');
      $('#modalAgregarMedicamento').modal('show');
    });

    // VALIDACIÓN FINAL
    $('#abrirModalRegresar').on('click', function() {
      $('#modalRegresarInventario').modal('show');
    });

    $('#formularioSalida').on('submit', function(e) {
      e.preventDefault();

      $('#id_tipo_movimiento, #observaciones_generales, #receptor, #fecha_recepcion, #hora_recepcion').removeClass('input-error');

      if ($('#id_tipo_movimiento').val() === "") {
        $('#id_tipo_movimiento').addClass('input-error');
        mostrarAviso("Debe seleccionar un Motivo para el Ajuste.");
        return;
      }

      if ($('#fecha_recepcion').val() === "") {
        $('#fecha_recepcion').addClass('input-error');
        mostrarAviso("La fecha de la recepcion no puede estar vacia.");
        return;
      }

      if ($('#hora_recepcion').val() === "") {
        $('#hora_recepcion').addClass('input-error');
        mostrarAviso("La hora de la recepcion no puede estar vacia.");
        return;
      }

      if ($('#receptor').val() === "") {
        $('#receptor').addClass('input-error');
        mostrarAviso("Debe seleccionar el Encargado.");
        return;
      }

      if ($('#observaciones_generales').val().trim() === "") {
        $('#observaciones_generales').addClass('input-error');
        mostrarAviso("Debe ingresar una justificación / observación obligatoria.");
        return;
      }

      if (listaDetalles.length === 0) {
        $('#btnAbrirModalAgregar').removeClass('btn-primary').addClass('btn-error-sombreado');
        mostrarAviso("La lista está vacía. Debe añadir al menos una vacuna para procesar la baja.");
        return;
      }

      const motivo = parseInt($('#id_tipo_movimiento').val());
      const evidencia = $('#fotos_base64_array').val();
      if ((motivo === 4 || motivo === 5) && (!evidencia || evidencia.trim() === "")) {
        mostrarAviso('<strong>📸 Evidencia Sugerida/Obligatoria:</strong><br>Para registrar daños, pérdidas o robos, es altamente recomendable (o requerido por su política) adjuntar un comprobante o foto.');
        return;
      }

      $('#modalSalidaGuardar').modal('show');
    });

    $('#id_tipo_movimiento').on('change', function() {
      $(this).removeClass('input-error');
    });

    $('#observaciones_generales').on('input', function() {
      $(this).removeClass('input-error');
    });

    $('#btnAbrirModalAgregar').on('click', function() {
      $(this).removeClass('btn-error-sombreado').addClass('btn-primary');
      editandoIndex = -1;
      limpiarFormularioModal();
      $('#btnConfirmarAgregarMedicamento').html('<i class="fa fa-check"></i> Añadir a la Lista');
      $('#modalAgregarMedicamento').modal('show');
    });

    $('#confirmarGuardadoFinal').on('click', function() {
      $('#modalSalidaGuardar').modal('hide');
      // SIMULACIÓN: Aquí se enviaría el formulario real
      // $('#formularioSalida').off('submit').submit();
      mostrarExito("✅ <b>Ajuste de Inventario registrado (Simulado).</b><br><br>En la versión real, esto restaría las dosis del stock y generaría el movimiento.");
      setTimeout(function() {
        window.location.href = 'vacunacion_inventario_listado.php';
      }, 2500);
    });

  });
</script>