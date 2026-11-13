<?php
include("../../cfg/conexion.php");
$operacion_actual = isset($_GET['op']) ? $_GET['op'] : 'entrada';

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
verificarAcceso('Generar Entradas de Inventario');
?>

<head>
  <title>Inventario | Entrada de Vacunas</title>
</head>

<style>
  #avisoModal { z-index: 1000000 !important; }
  .tags-input-container { display: flex; flex-wrap: wrap; gap: 5px; padding: 4px; border: 1px solid #d2d6de; background-color: #fff; min-height: 34px; cursor: text; }
  .tags-input-container .tag-badge { display: inline-flex; align-items: center; padding: 4px 8px; border-radius: 4px; font-size: 12px; color: white; }
  .tags-input-container .tag-badge i { margin-left: 6px; cursor: pointer; font-size: 10px; }
  .tags-input-fake { border: none; outline: none; flex-grow: 1; min-width: 150px; padding: 2px; }
  .table-responsive { max-height: 220px; overflow-y: auto; }
  .table-detalle thead th { position: sticky; top: 0; background-color: #f4f4f4; z-index: 1; }
  .table-detalle th { background-color: #f4f4f4; text-align: center; }
  .table-detalle td { text-align: center; vertical-align: middle !important; }
  .row-vence-pronto { background-color: #fff3cd !important; }
  .area-trabajo-blanca { background-color: #ffffff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08); margin-bottom: 20px; overflow: hidden; }
  input[type="date"][readonly], input[type="date"][disabled] { background-color: #eeeeee !important; cursor: not-allowed; }
</style>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Entrada de Vacunas (Biológicos)</h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="#"><i class="fa fa-medkit"></i>Inmunización</a></li>
      <li class="active"><a href="#"><i class="fa fa-arrow-down"></i>Entrada de Vacunas</a></li>
    </ol>
  </section>

  <div id="info_pedido_cargado" class="alert alert-info" style="display: none; margin-bottom: 15px;">
    <h4 style="margin-bottom: 5px;"><i class="icon fa fa-info-circle"></i> Pedido Vinculado: #<span id="txt_id_pedido_cargado"></span></h4>
    <p style="margin: 0;">Se ha cargado un pedido. Por favor, use el botón amarillo de Editar (<i class="fa fa-pencil"></i>) en cada fila inferior para asignar el Lote y las Fechas antes de guardar. <b>No se puede cambiar la vacuna ni la cantidad solicitada.</b></p>
  </div>

  <section class="content">
    <form id="formularioEntrada" method="POST" action="../../cfg/movimientos_inventario.php" novalidate autocomplete="off">
      <input type="hidden" name="op" id="op" value="<?php echo $operacion_actual; ?>">
      <input type="hidden" name="detalle_medicamentos" id="detalle_medicamentos" value="[]">
      <input type="hidden" name="id_pedido" id="id_pedido_oculto" value="0">

      <div class="row">
        <div class="col-md-12">
          <div class="area-trabajo-blanca">
            <div class="box-tools pull-left" style="margin-top: 2px;">
              <span style="font-size: 16px; font-weight: bold; color: #555;">
                Operación N°: <span id="badge_numero_operacion" class="badge bg-blue" style="font-size: 15px; padding: 5px 10px; letter-spacing: 1px;">#<?php echo $numero_proyectado; ?></span>
              </span>
            </div>

            <div class="box-tools pull-right" style="margin-top: -5px;">
              <button type="button" class="btn btn-danger btn-sm" id="btnAbrirModalPedidos">
                <i class="fa fa-download"></i> Cargar Pedido
              </button>
            </div>
            <br>

            <!-- DATOS DE RECEPCIÓN -->
            <div class="box box-primary">
              <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-truck"></i> Datos de la recepción:</h3>
              </div>
              <div class="box-body">
                <div class="row">
                  <div class="col-sm-3">
                    <label>Receptor (*):</label>
                    <select name="receptor" id="receptor" class="form-control" required>
                      <option value="">--- Seleccione el receptor ---</option>
                      <!-- Simulación de receptores (Farmacéuticos/Administradores) -->
                      <option value="1" selected>Lic. María Rodríguez</option>
                      <option value="2">Enf. Carlos Pérez</option>
                    </select>
                  </div>

                  <div class="col-sm-3 form-group">
                    <label>Proveedor / Donante (*):</label>
                    <div class="input-group">
                      <select id="proveedor" name="proveedor" class="form-control" required>
                        <option value="">--- Seleccione un proveedor ---</option>
                        <!-- Simulación de proveedores (Ej: SUAF, Ministerios) -->
                        <option value="1">SUAF Portuguesa</option>
                        <option value="2">Ministerio de Salud</option>
                      </select>
                      <span class="input-group-btn">
                        <button class="btn btn-info" type="button" id="btnAggProveedor" data-toggle="modal" data-target="#modalNuevoProveedor" title="Agregar Proveedor" style="height: 34px;">
                          <i><img src="../../recursos/imagenes/iconos/agregar.png" style="width:10px; height:10px;"></i>
                        </button>
                      </span>
                    </div>
                  </div>

                  <div class="col-sm-3 form-group" id="med_group_fecha_recepcion">
                    <label>Fecha y hora de recepción (*):</label>
                    <div class="input-group">
                      <input type="date" id="fecha_recepcion" name="fecha_recepcion" class="form-control" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" onkeydown="return false;" style="width: 100%;">
                      <div class="input-group-btn" style="width: 100%;">
                        <input type="time" id="hora_recepcion" name="hora_recepcion" class="form-control" value="<?php date_default_timezone_set('America/Caracas'); echo date('H:i'); ?>">
                      </div>
                    </div>
                  </div>

                  <div class="col-sm-3 form-group">
                    <label>Observaciones generales:</label>
                    <input type="text" id="observaciones_generales" name="observaciones_generales" class="form-control" placeholder="Ej: Cajas en buen estado, cadena de frío intacta..." maxlength="255">
                  </div>
                </div>
              </div>
            </div>

            <!-- MEDICAMENTOS / VACUNAS A INGRESAR -->
            <div class="box box-success">
              <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-syringe"></i> Vacunas a ingresar:</h3>
                <div class="box-tools pull-right" style="margin-left: 5px;">
                  <button type="button" class="btn btn-primary btn-sm" id="btnAbrirModalAgregar" style="width:200px;">
                    <i class="fa fa-plus"></i> Añadir Vacuna
                  </button>
                  <p></p>
                  <button type="button" class="btn btn-success btn-sm" id="btnAbrirModalCrear" style="width:200px;">
                    <i class="fa fa-plus"></i> Crear Nueva Vacuna
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
                        <th>F. Fabricación</th>
                        <th>F. Vencimiento</th>
                        <th>Cantidad (Dosis)</th>
                        <th>Acciones</th>
                      </tr>
                    </thead>
                    <tbody id="cuerpoTablaMedicamentos">
                      <tr id="filaVacia">
                        <td colspan="6" class="text-center text-muted">Aún no se han añadido vacunas a esta entrada.</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="box-footer text-right" style="background-color: transparent; border-top: 1px solid #f4f4f4; padding-top: 15px;">
                <button type="button" class="btn btn-second" id="abrirModalRegresar">Regresar</button>
                <button type="submit" class="btn btn-success" id="btnPrepararGuardado"><i class="fa fa-save"></i>Procesar</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </section>
</div>

<!-- ======================================================= -->
<!-- MODALES (Mismos que el original, adaptados) -->
<!-- ======================================================= -->

<!-- MODAL AGREGAR VACUNA -->
<div class="modal fade" id="modalAgregarMedicamento" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h4 class="modal-title"><i class="fa fa-plus-circle"></i> Ingresar Lote de Vacuna</h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <label for="filtro_busqueda_rapida">Busqueda Rapida:</label>
              <input type="text" id="filtro_busqueda_rapida" name="filtro_busqueda_rapida" class="form-control" placeholder="Escriba nombre de la vacuna (Ej: BCG, VPI, SRP)...">
            </div>
          </div>
          <br><br>
          <div class="col-sm-12 form-group">
            <label>Vacuna del Catálogo (*):</label>
            <div class="input-group">
              <select id="Id_descripcion_medicamento" name="Id_descripcion_medicamento" class="form-control">
                <option value="">--- Seleccione una Vacuna ---</option>
                <!-- SIMULACIÓN: Aquí irían las vacunas de la BD -->
                <option value="1" data-nombre="Antipoliomielítica (VPI) [1 Dosis]" data-componentes="Virus Inactivados">Antipoliomielítica (VPI) - [1 Dosis]</option>
                <option value="2" data-nombre="Antisarampión (SRP) [10 Dosis]" data-componentes="Virus Atenuados">Antisarampión (SRP) - [10 Dosis]</option>
                <option value="3" data-nombre="Vacuna BCG [20 Dosis]" data-componentes="Bacteria Atenuada">Vacuna BCG - [20 Dosis]</option>
              </select>
              <span class="input-group-btn">
                <button class="btn btn-info" type="button" id="btnBuscarFiltrar" data-toggle="modal" data-target="#modalBúsquedaAvanzadaMedicamento" title="Búsqueda Avanzada" style="height: 34px;">
                  <i><img src="../../recursos/imagenes/iconos/buscar.png" style="width:10px; height:10px;"></i>
                </button>
              </span>
            </div>
          </div>

          <div class="col-sm-4 form-group">
            <label>Existencia Actual:</label>
            <input type="text" id="existencia_actual" class="form-control" readonly disabled style="background-color: #f9f9f9;" placeholder="0">
          </div>
          <div class="col-sm-4 form-group">
            <label>Stock Mínimo:</label>
            <input type="text" id="stock_minimo" class="form-control" readonly disabled style="background-color: #f9f9f9;" placeholder="0">
          </div>
          <div class="col-sm-4 form-group">
            <label>Stock Máximo:</label>
            <input type="text" id="stock_maximo" class="form-control" readonly disabled style="background-color: #f9f9f9;" placeholder="0">
          </div>

          <div class="clearfix"></div>

          <div class="col-sm-4 form-group mt-3">
            <label style="width: 100%;">Número de Lote (*):
              <button type="button" id="btnCopiarUltimoLote" class="btn btn-info btn-xs pull-right" style="display: none; padding: 2px 5px;" title="Copiar Lote y Fechas del último ingresado">
                <i class="fa fa-copy"></i> Usar Último
              </button>
            </label>
            <div class="input-group">
              <input type="text" id="lote" class="form-control" list="lista_lotes" placeholder="Ej: L-2026X" style="text-transform: uppercase;">
              <datalist id="lista_lotes"></datalist>
              <span class="input-group-btn">
                <button class="btn btn-info" type="button" id="infoLote" title="Informacion del lote" style="height: 34px;">
                  <i><img src="../../recursos/imagenes/iconos/info.png" style="width:10px; height:10px;"></i>
                </button>
              </span>
            </div>
          </div>
          <div class="col-sm-4 form-group mt-3">
            <label>F. Fabricación (*):</label>
            <input type="date" id="fecha_fabricacion" class="form-control" onkeydown="return false;">
          </div>
          <div class="col-sm-4 form-group mt-3">
            <label>F. Vencimiento (*):</label>
            <input type="date" id="fecha_vencimiento" class="form-control" disabled onkeydown="return false;">
          </div>

          <div class="clearfix"></div>

          <div class="col-sm-4 form-group mt-3">
            <label>Dosis a Ingresar (*):</label>
            <input type="text" id="cantidad" class="form-control" placeholder="Solo números, sin 0 inicial" oninput="this.value = this.value.replace(/[^0-9]/g, ''); if(this.value === '0') this.value = '1';">
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

<!-- MODAL CONFIRMAR LOTE EXISTENTE -->
<div class="modal fade" id="modalConfirmarLote" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-crimson">
        <h5 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Confirmación de lote existente</h5>
      </div>
      <div class="modal-body">
        <p id="modalConfirmarLoteTexto" style="font-size: 16px;"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" data-dismiss="modal" id="btnCancelarLote">Cancelar</button>
        <button type="button" class="btn btn-danger" data-dismiss="modal" id="btnAceptarLote">Aceptar</button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL BÚSQUEDA AVANZADA (SIMPLIFICADO) -->
<div class="modal fade" id="modalBúsquedaAvanzadaMedicamento" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title">Filtros de Búsqueda Avanzada</h5>
      </div>
      <div class="modal-body">
        <form id="formFiltroModal">
          <p class="text-muted">Complete los campos para filtrar las vacunas.</p>
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="filtro_nombre">Nombre (o ID):</label>
                <input type="text" id="filtro_nombre" name="filtro_nombre" class="form-control" placeholder="Ej: BCG...">
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
                <label for="filtro_principios">Principios activos (contiene):</label>
                <input type="text" id="filtro_principios" name="filtro_principios" class="form-control" placeholder="Ej: Virus Inactivados...">
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

<!-- MODAL CARGAR PEDIDO -->
<div class="modal fade" id="modalCargarPedido" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-crimson">
        <h4 class="modal-title"><i class="fa fa-list"></i> Seleccionar Pedido Pendiente</h4>
      </div>
      <div class="modal-body">
        <table class="table table-bordered table-striped table-hover">
          <thead style="background-color: #f4f4f4;">
            <tr>
              <th class="text-center">N° Pedido</th>
              <th class="text-center">Fecha Creación</th>
              <th class="text-center">Proveedor Solicitado</th>
              <th class="text-center">Acción</th>
            </tr>
          </thead>
          <tbody>
            <!-- SIMULACIÓN DE PEDIDOS -->
            <tr>
              <td class='text-center'><strong>#000001</strong></td>
              <td class='text-center'>01/11/2026 10:00 AM</td>
              <td class='text-center'>SUAF Portuguesa</td>
              <td class='text-center'>
                <button type='button' class='btn btn-info btn-xs' onclick='verDetallePedidoPrevio(1)' title='Ver vacunas de este pedido'><i class='fa fa-info-circle'></i> Ver Info</button>
                <button type='button' class='btn btn-success btn-xs btn-seleccionar-pedido' data-id='1' data-idprov='1' style='margin-left: 5px;'><i class='fa fa-check'></i> Seleccionar</button>
              </td>
            </tr>
            <tr>
              <td class='text-center'><strong>#000002</strong></td>
              <td class='text-center'>02/11/2026 09:30 AM</td>
              <td class='text-center'>Ministerio de Salud</td>
              <td class='text-center'>
                <button type='button' class='btn btn-info btn-xs' onclick='verDetallePedidoPrevio(2)' title='Ver vacunas de este pedido'><i class='fa fa-info-circle'></i> Ver Info</button>
                <button type='button' class='btn btn-success btn-xs btn-seleccionar-pedido' data-id='2' data-idprov='2' style='margin-left: 5px;'><i class='fa fa-check'></i> Seleccionar</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- MODAL VER DETALLE PEDIDO PREVIO -->
<div class="modal fade" id="modalVerDetallePedidoPrevio" tabindex="-1" role="dialog" style="z-index: 1060;">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h4 class="modal-title"><i class="fa fa-shopping-basket"></i> Vista Previa - Contenido del Pedido <span id="num_pedido_previo_titulo"></span></h4>
      </div>
      <div class="modal-body">
        <div style="max-height: 280px; overflow-y: auto; border-bottom: 1px solid #eee;">
          <table class="table table-bordered table-striped" style="margin-bottom: 0;">
            <thead style="background-color: #f8f9fa;">
              <tr>
                <th>Vacuna / Presentación</th>
                <th class="text-center" style="width: 150px;">Cant. Solicitada</th>
              </tr>
            </thead>
            <tbody id="cuerpo_detalle_pedido_previo"></tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL NUEVO PROVEEDOR -->
<div class="modal fade" id="modalNuevoProveedor" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info">
        <h4 class="modal-title"><i class="fa fa-plus-circle"></i> Nuevo Proveedor</h4>
      </div>
      <div class="modal-body">
        <form id="formNuevoProveedor" onsubmit="return false;">
          <div class="form-group">
            <label for="nuevo_nombre_proveedor">Nombre del Proveedor (*):</label>
            <input type="text" class="form-control" id="nuevo_nombre_proveedor" name="nombre_proveedor" placeholder="Ej: SUAF Portuguesa" required autocomplete="off">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-info" id="btnGuardarProveedor"><i class="fa fa-save"></i> Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL AVISO -->
<div class="modal fade" id="avisoModal" tabindex="-1" role="dialog" aria-labelledby="avisoModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div id="headerAviso" class="modal-header bg-crimson">
        <h5 class="modal-title" id="avisoModalLabel">Aviso Importante</h5>
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
<div class="modal fade" id="modalEntradaGuardar" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-green">
        <h5 class="modal-title">Confirmación de Guardado</h5>
      </div>
      <div class="modal-body">
        <p>¿Está seguro de que desea guardar la información de esta entrada de vacunas?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" id="confirmarGuardadoFinal">Guardar</button>
      </div>
    </div>
  </div>
</div>


<?php
// include('modales/inventario/medicamentos_agregar_modal.php'); // COMENTADO PORQUE NO TENEMOS EL ARCHIVO REAL DE VACUNAS
if (isset($conexion)) { $conexion->close(); }
include('includes/footer.php');
?>

<script>
  $(document).ready(function() {

    $('#infoLote').tooltip({ html: true, placement: 'right', title: 'Llene los datos del lote' });

    let listaDetalles = [];
    let lotesCargados = [];
    let editandoIndex = -1;
    let ultimoLoteIngresado = null;

    const hoy = new Date().toISOString().split('T')[0];
    $('#fecha_fabricacion').attr('max', hoy);

    // Lógica de modales (Mantenida de tu código original)
    $('.modal').on('click', '[data-dismiss="modal"]', function(e) {
      e.preventDefault();
      var $modal = $(this).closest('.modal');
      $modal.removeClass('in').addClass('out');
      setTimeout(function() { $modal.modal('hide'); }, 400);
    });

    $('.modal').on('hidden.bs.modal', function() {
      $(this).removeClass('out');
      if ($('.modal:visible').length) {
        $('body').addClass('modal-open');
      } else {
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
      }
    });

    $('.modal').on('show.bs.modal', function() { $(this).removeClass('out'); });

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

    $('#btnAbrirModalCrear').on('click', function() {
      // $('#med_formularioMedicamento')[0].reset(); // Comentado porque no tenemos el modal de crear vacuna
      mostrarAviso("Funcionalidad para crear nuevas vacunas en desarrollo. Por favor, use el catálogo existente.");
    });

    function limpiarFormularioModal() {
      $('#Id_descripcion_medicamento').val('').trigger('change');
      $('#lote, #fecha_fabricacion, #fecha_vencimiento, #cantidad').val('').removeClass('input-error');
      $('#fecha_fabricacion, #fecha_vencimiento').prop('readonly', false);
      $('#fecha_vencimiento').prop('disabled', true);
      $('#lote').css('border-color', '#ced4da');
      $('#existencia_actual, #stock_minimo, #stock_maximo').val('');
    }

    $('#abrirModalRegresar').on('click', function() { $('#modalRegresarInventario').modal('show'); });

    const medicamentoSelectPrincipal = $('#Id_descripcion_medicamento');
    window.opcionesOriginalesMedicamentos = medicamentoSelectPrincipal.html();

    $('#btnLimpiarFiltros').on('click', function() {
      $('#formFiltroModal')[0].reset();
      medicamentoSelectPrincipal.html(window.opcionesOriginalesMedicamentos);
    });

    // SIMULACIÓN DE FILTROS AVANZADOS (No hace petición AJAX real)
    $('#btnAplicarFiltros').on('click', function(e) {
      e.preventDefault();
      mostrarAviso("Los filtros avanzados están simulados. En la versión real, aquí se cargarían las vacunas filtradas desde la BD.");
      $('#modalBúsquedaAvanzadaMedicamento').modal('hide');
    });

    $('#btnGuardarProveedor').on('click', function() {
      const nombreProveedor = $('#nuevo_nombre_proveedor').val().trim();
      if (nombreProveedor === '') {
        $('#nuevo_nombre_proveedor').addClass('input-error');
        mostrarAviso('Por favor, ingrese el nombre del proveedor.');
        return;
      }
      $('#nuevo_nombre_proveedor').removeClass('input-error');

      // SIMULACIÓN DE GUARDADO
      const nuevaOpcion = new Option(nombreProveedor, "999", false, true);
      $('#proveedor').append(nuevaOpcion).trigger('change');
      $('#modalNuevoProveedor').modal('hide');
      $('#nuevo_nombre_proveedor').val('');
      mostrarExito('Proveedor Añadido Exitosamente (Simulado).');
    });

    $('#btnAbrirModalAgregar').on('click', function() {
      editandoIndex = -1;
      limpiarFormularioModal();
      $('#btnConfirmarAgregarMedicamento').html('<i class="fa fa-check"></i> Añadir a la Lista');

      if (ultimoLoteIngresado) {
        $('#btnCopiarUltimoLote').show();
      } else {
        $('#btnCopiarUltimoLote').hide();
      }

      $('#modalAgregarMedicamento').modal('show');
    });

    $('#btnCopiarUltimoLote').on('click', function() {
      if (ultimoLoteIngresado) {
        $('#lote').val(ultimoLoteIngresado.lote);
        $('#fecha_fabricacion').val(ultimoLoteIngresado.fecha_fabricacion);
        $('#fecha_vencimiento').val(ultimoLoteIngresado.fecha_vencimiento).prop('disabled', false);
        $('#lote').trigger('input');
      }
    });

    $('#lote, #cantidad, #fecha_fabricacion, #fecha_vencimiento').on('mousedown keydown', function(e) {
      if (e.type === 'keydown' && e.key === 'Tab') return;
      let medSeleccionado = $('#Id_descripcion_medicamento').val();
      if (!medSeleccionado || medSeleccionado === "") {
        e.preventDefault();
        $(this).blur();
        if ($('#avisoModal').is(':hidden')) {
          mostrarAviso('⚠️ <b>Atención:</b> Debe seleccionar una <b>Vacuna del Catálogo</b> antes de llenar estos campos.');
        }
        return false;
      }
    });

    // SIMULACIÓN AL CAMBIAR VACUNA
    $('#Id_descripcion_medicamento').on('change', function() {
      const medicamentoId = $(this).val();
      $('#lista_lotes').empty();
      lotesCargados = [];
      $('#lote').val('').css('border-color', '#ced4da').data('lote-confirmado', '');
      $('#fecha_fabricacion').val('').prop('readonly', false);
      $('#fecha_vencimiento').val('').prop('disabled', true).prop('readonly', false);
      $('#cantidad').val('');
      $('#infoLote').attr('data-original-title', 'Llene los datos del lote').attr('title', 'Llene los datos del lote');

      if (medicamentoId) {
        // SIMULACIÓN DE CARGA DE DATOS
        if (medicamentoId == '1') { // VPI
          $('#existencia_actual').val(150);
          $('#stock_minimo').val(50);
          $('#stock_maximo').val(500);
          lotesCargados = [{ lote: 'VPI-001', fecha_fabricacion: '2025-01-01', fecha_vencimiento: '2026-01-01', cantidad_actual: 150, nombre_proveedor: 'SUAF' }];
        } else if (medicamentoId == '2') { // SRP
          $('#existencia_actual').val(80);
          $('#stock_minimo').val(20);
          $('#stock_maximo').val(300);
          lotesCargados = [{ lote: 'SRP-991', fecha_fabricacion: '2025-05-10', fecha_vencimiento: '2026-05-10', cantidad_actual: 80, nombre_proveedor: 'Ministerio' }];
        } else if (medicamentoId == '3') { // BCG
          $('#existencia_actual').val(200);
          $('#stock_minimo').val(100);
          $('#stock_maximo').val(600);
        }

        if (lotesCargados.length > 0) {
          lotesCargados.forEach(function(item) {
            $('#lista_lotes').append('<option value="' + item.lote + '">');
          });
        }

        let extActual = parseInt($('#existencia_actual').val()) || 0;
        let sMaximo = parseInt($('#stock_maximo').val()) || 0;

        if (sMaximo > 0 && extActual >= sMaximo) {
          $('#cantidad').prop('disabled', true).val('').attr('placeholder', 'Bloqueado (Stock Lleno)');
          mostrarAviso(`🚫 <b>Atención:</b> La existencia actual (${extActual}) ya es igual o superior al Stock Máximo permitido (${sMaximo}).<br><br>No se permite el ingreso de más dosis de esta vacuna.`);
        } else {
          $('#cantidad').prop('disabled', false).attr('placeholder', 'Solo números, sin 0 inicial');
        }
      } else {
        $('#existencia_actual, #stock_minimo, #stock_maximo').val('');
      }
    });

    $('#lote').on('input change', function() {
      const loteEscrito = $(this).val().trim().toUpperCase();
      $(this).val(loteEscrito);

      if (loteEscrito === "") {
        $('#fecha_fabricacion, #fecha_vencimiento').val('').prop('readonly', false);
        return;
      }

      const loteEncontrado = lotesCargados.find(l => l.lote.toUpperCase() === loteEscrito);

      if (loteEncontrado) {
        if ($(this).data('lote-confirmado') === loteEscrito) {
          aplicarLoteExistente(loteEncontrado);
          return;
        }

        let loteAceptado = false;
        $('#modalConfirmarLoteTexto').html(`¿Está seguro de sumar la cantidad al mismo lote <b>(${loteEscrito})</b> que ya existe en el sistema?`);
        $('#modalConfirmarLote').modal('show');

        $('#btnAceptarLote').off('click').on('click', function() {
          loteAceptado = true;
          $('#lote').data('lote-confirmado', loteEscrito);
          aplicarLoteExistente(loteEncontrado);
        });

        $('#btnCancelarLote').off('click').on('click', function() {});
        $('#modalConfirmarLote').off('hidden.bs.modal').on('hidden.bs.modal', function() {
          if ($('.modal-backdrop').length > 1) { $('.modal-backdrop').last().remove(); }
          if ($('#modalAgregarMedicamento').is(':visible')) { $('body').addClass('modal-open'); }
          if (!loteAceptado) {
            $('#lote').val('').data('lote-confirmado', '');
            $('#fecha_fabricacion, #fecha_vencimiento').val('').prop('readonly', false);
            $('#lote').css('border-color', '#ced4da');
            $('#infoLote').attr('data-original-title', 'Llene los datos del lote').tooltip('fixTitle');
          }
        });

      } else {
        $('#lote').data('lote-confirmado', '');
        $('#fecha_fabricacion, #fecha_vencimiento').prop('readonly', false);
        $(this).css('border-color', '#ced4da');
        $('#infoLote').attr('data-original-title', 'Lote nuevo. Llene los datos.').tooltip('fixTitle');
      }
    });

    function aplicarLoteExistente(loteEncontrado) {
      $('#fecha_fabricacion').val(loteEncontrado.fecha_fabricacion).prop('readonly', true);
      $('#fecha_vencimiento').prop('disabled', false).val(loteEncontrado.fecha_vencimiento).prop('readonly', true);
      $('#lote').css('border-color', '#28a745');
      var infoHtml = "<div style='text-align:left; font-size: 12px;'>" +
        "<b>Proveedor:</b> " + (loteEncontrado.nombre_proveedor || 'No registrado') + "<br>" +
        "<b>F. Fabricación:</b> " + (loteEncontrado.fecha_fabricacion || 'N/A') + "<br>" +
        "<b>F. Vencimiento:</b> " + (loteEncontrado.fecha_vencimiento || 'N/A') + "<br>" +
        "<b>Stock Físico:</b> " + (loteEncontrado.cantidad_actual || 0) +
        "</div>";
      $('#infoLote').attr('data-original-title', infoHtml).tooltip('fixTitle');
    }

    $('#fecha_fabricacion').on('change', function() {
      if ($(this).val()) {
        $('#fecha_vencimiento').prop('disabled', false).attr('min', $(this).val());
      } else {
        $('#fecha_vencimiento').prop('disabled', true).val('').removeAttr('min');
      }
    });

    $('#btnAbrirModalPedidos').on('click', function() { $('#modalCargarPedido').modal('show'); });

    $('.btn-seleccionar-pedido').on('click', function() {
      let idPedido = $(this).data('id');
      let idProveedor = $(this).data('idprov');

      cargarPedidoLogica(idPedido, idProveedor);

      $('#modalCargarPedido').removeClass('in').addClass('out');
      setTimeout(function() {
        $('#modalCargarPedido').modal('hide');
        $('#modalCargarPedido').removeClass('out');
      }, 400);
    });

    // SIMULACIÓN DE CARGA DE PEDIDO
    function cargarPedidoLogica(idPedido, idProveedor) {
      $('#proveedor').val(idProveedor).trigger('change');
      $('#proveedor').css('pointer-events', 'none').css('background-color', '#eee');
      $('#id_pedido_oculto').val(idPedido);
      $('#txt_id_pedido_cargado').text(idPedido.toString().padStart(6, '0'));
      $('#info_pedido_cargado').slideDown();
      $('#btnAbrirModalAgregar, #btnAbrirModalCrear').hide();

      // Simulando los datos que vendrían del AJAX
      listaDetalles = [];
      if(idPedido == 1) {
        listaDetalles.push({
          id_medicamento: '1',
          nombre_medicamento: 'Antipoliomielítica (VPI) [1 Dosis]',
          componentes: 'Virus Inactivados',
          lote: '',
          fecha_fabricacion: '',
          fecha_vencimiento: '',
          cantidad: 50
        });
        listaDetalles.push({
          id_medicamento: '2',
          nombre_medicamento: 'Antisarampión (SRP) [10 Dosis]',
          componentes: 'Virus Atenuados',
          lote: '',
          fecha_fabricacion: '',
          fecha_vencimiento: '',
          cantidad: 20
        });
      } else {
        listaDetalles.push({
          id_medicamento: '3',
          nombre_medicamento: 'Vacuna BCG [20 Dosis]',
          componentes: 'Bacteria Atenuada',
          lote: '',
          fecha_fabricacion: '',
          fecha_vencimiento: '',
          cantidad: 30
        });
      }

      actualizarTablaDetalles();
      mostrarAviso("✅ <b>Pedido cargado (Simulado).</b><br><br>Se han traído las cantidades solicitadas. <b>Atención:</b> Use el botón amarillo de Editar en cada fila para asignar el número de Lote y las Fechas correspondientes antes de guardar.");
    }

    window.verDetallePedidoPrevio = function(idPedido) {
      let idFormateado = idPedido.toString().padStart(6, '0');
      $('#num_pedido_previo_titulo').text('#' + idFormateado);
      $('#cuerpo_detalle_pedido_previo').html('<tr><td colspan="2" class="text-center"><i class="fa fa-spinner fa-spin"></i> Consultando...</td></tr>');
      $('#modalVerDetallePedidoPrevio').modal('show');

      // Simulación de respuesta AJAX
      setTimeout(function() {
        var html = '';
        if (idPedido == 1) {
          html += '<tr><td><strong>Antipoliomielítica (VPI)</strong><br><small class="text-muted">1 Dosis</small></td><td class="text-center"><span class="badge bg-primary" style="font-size: 14px;">50</span></td></tr>';
          html += '<tr><td><strong>Antisarampión (SRP)</strong><br><small class="text-muted">10 Dosis</small></td><td class="text-center"><span class="badge bg-primary" style="font-size: 14px;">20</span></td></tr>';
        } else {
          html += '<tr><td><strong>Vacuna BCG</strong><br><small class="text-muted">20 Dosis</small></td><td class="text-center"><span class="badge bg-primary" style="font-size: 14px;">30</span></td></tr>';
        }
        $('#cuerpo_detalle_pedido_previo').html(html);
      }, 500);
    };

    $('#btnConfirmarAgregarMedicamento').on('click', function() {
      const id_med = $('#Id_descripcion_medicamento').val();
      const nombre_med = $('#Id_descripcion_medicamento option:selected').data('nombre');
      const componentes = $('#Id_descripcion_medicamento option:selected').data('componentes');
      const lote = $('#lote').val().trim().toUpperCase();
      const f_fab = $('#fecha_fabricacion').val();
      const f_venc = $('#fecha_vencimiento').val();
      const cant = parseInt($('#cantidad').val());
      const existencia = parseInt($('#existencia_actual').val()) || 0;
      const sMin = parseInt($('#stock_minimo').val()) || 0;
      const sMax = parseInt($('#stock_maximo').val()) || 0;

      $('.modal-body input, .modal-body select').removeClass('input-error');

      if (!id_med) { $('#Id_descripcion_medicamento').addClass('input-error'); mostrarAviso('Seleccione una vacuna.'); return; }
      if (!lote) { $('#lote').addClass('input-error'); mostrarAviso('Ingrese el número de lote.'); return; }
      if (!f_fab) { $('#fecha_fabricacion').addClass('input-error'); mostrarAviso('Ingrese la fecha de fabricación.'); return; }
      if (!f_venc) { $('#fecha_vencimiento').addClass('input-error'); mostrarAviso('Ingrese la fecha de vencimiento.'); return; }
      if (isNaN(cant) || cant <= 0) { $('#cantidad').addClass('input-error'); mostrarAviso('La cantidad debe ser mayor a 0.'); return; }

      let cantidadAcumuladaLista = 0;
      listaDetalles.forEach((item, index) => {
        if (item.id_medicamento === id_med && index !== editandoIndex) {
          cantidadAcumuladaLista += parseInt(item.cantidad) || 0;
        }
      });

      const inventarioActualMasLista = existencia + cantidadAcumuladaLista;
      const totalProyectado = inventarioActualMasLista + cant;

      if (sMax > 0 && inventarioActualMasLista >= sMax) {
        $('#Id_descripcion_medicamento, #cantidad').addClass('input-error');
        mostrarAviso(`🚫 <b>Acción denegada:</b> La existencia actual (${existencia}) más lo que ya tienes en la tabla (${cantidadAcumuladaLista}) ya alcanzó o superó el Stock Máximo permitido (${sMax}).`);
        return;
      }

      if (sMax > 0 && totalProyectado > sMax) {
        $('#cantidad').addClass('input-error');
        let permitido = sMax - inventarioActualMasLista;
        mostrarAviso(`⚠️ <b>Límite excedido:</b> Actualmente hay ${existencia} dosis y ${cantidadAcumuladaLista} ya agregadas en la tabla.<br><br>El máximo es ${sMax}. <b>Solo puedes ingresar un máximo de ${permitido} dosis nuevas adicionales.</b>`);
        return;
      }

      if (sMin > 0 && totalProyectado < sMin) {
        $('#cantidad').addClass('input-error');
        mostrarAviso(`La cantidad ingresada no cubre el Stock Mínimo requerido (${sMin}). Actualmente hay ${existencia} dosis. Debes ingresar al menos ${sMin - inventarioActualMasLista} dosis para estabilizar el inventario.`);
        return;
      }

      if (f_venc <= hoy) { $('#fecha_vencimiento').addClass('input-error'); mostrarAviso('La vacuna ya está vencida.'); return; }
      if (f_venc < f_fab) { $('#fecha_vencimiento').addClass('input-error'); mostrarAviso('La fecha de vencimiento es incorrecta.'); return; }

      const existeIndex = listaDetalles.findIndex(item => item.id_medicamento === id_med && item.lote === lote);
      if (existeIndex !== -1 && existeIndex !== editandoIndex) {
        mostrarAviso('Esta vacuna con el mismo Lote ya está en la lista. Si desea modificarlo, edítelo directamente usando el botón amarillo en la tabla.');
        return;
      }

      const nuevoItem = {
        id_medicamento: id_med,
        nombre_medicamento: nombre_med,
        componentes: componentes,
        lote: lote,
        fecha_fabricacion: f_fab,
        fecha_vencimiento: f_venc,
        cantidad: cant
      };

      if (editandoIndex !== -1) {
        listaDetalles[editandoIndex] = nuevoItem;
        editandoIndex = -1;
      } else {
        listaDetalles.push(nuevoItem);
      }

      ultimoLoteIngresado = { lote: lote, fecha_fabricacion: f_fab, fecha_vencimiento: f_venc };

      actualizarTablaDetalles();

      $('#modalAgregarMedicamento').removeClass('in').addClass('out');
      setTimeout(function() {
        $('#modalAgregarMedicamento').modal('hide');
        $('#modalAgregarMedicamento').removeClass('out');
      }, 400);
    });

    function actualizarTablaDetalles() {
      const tbody = $('#cuerpoTablaMedicamentos');
      tbody.empty();

      if (listaDetalles.length === 0) {
        tbody.append('<tr id="filaVacia"><td colspan="6" class="text-center text-muted">Aún no se han añadido vacunas a esta entrada.</td></tr>');
      } else {
        listaDetalles.forEach((item, index) => {
          let rowClass = "";
          let diffDias = (new Date(item.fecha_vencimiento) - new Date(hoy)) / (1000 * 60 * 60 * 24);
          if (diffDias < 180) rowClass = "row-vence-pronto";

          let etiquetaLote = item.lote ? `<strong>${item.lote}</strong>` : `<span class="text-danger"><i class="fa fa-exclamation-triangle"></i> Falta Lote</span>`;
          let etiquetaFab = item.fecha_fabricacion ? item.fecha_fabricacion : `<span class="text-danger">Pendiente</span>`;
          let etiquetaVenc = item.fecha_vencimiento ? item.fecha_vencimiento : `<span class="text-danger">Pendiente</span>`;

          tbody.append(`
              <tr class="${rowClass}">
                <td style="text-align: left;">${item.nombre_medicamento}</td>
                <td>${etiquetaLote}</td>
                <td>${etiquetaFab}</td>
                <td>${etiquetaVenc} ${diffDias < 180 ? ' <i class="fa fa-warning text-warning" title="Vence pronto"></i>' : ''}</td>
                <td><span class="badge bg-green" style="font-size:14px;">${item.cantidad}</span></td>
                <td>
                  <button type="button" class="btn btn-warning btn-xs btn-editar-fila" data-index="${index}" title="Editar vacuna">
                    <i><img src="../../recursos/imagenes/iconos/editar.png" style="width:10px; height:10px;"></i>
                  </button>
                  <button type="button" class="btn btn-danger btn-xs btn-eliminar-fila" data-index="${index}" title="Eliminar de la lista">
                    <i><img src="../../recursos/imagenes/iconos/Delete.png" style="width:10px; height:10px;"></i>
                  </button>
                </td>
              </tr>
            `);
        });
      }
      $('#detalle_medicamentos').val(JSON.stringify(listaDetalles));
    }

    $('#cuerpoTablaMedicamentos').on('click', '.btn-editar-fila', function() {
      const index = $(this).data('index');
      const item = listaDetalles[index];
      editandoIndex = index;

      limpiarFormularioModal();

      $('#Id_descripcion_medicamento').val(item.id_medicamento).trigger('change');
      $('#lote').val(item.lote);
      $('#fecha_fabricacion').val(item.fecha_fabricacion);
      $('#fecha_vencimiento').val(item.fecha_vencimiento).prop('disabled', false);
      $('#cantidad').val(item.cantidad);

      $('#btnCopiarUltimoLote').hide();
      $('#btnConfirmarAgregarMedicamento').html('<i class="fa fa-save"></i> Guardar Cambios');

      if ($('#id_pedido_oculto').val() !== "0" && $('#id_pedido_oculto').val() !== "") {
        $('#Id_descripcion_medicamento').css('pointer-events', 'none').css('background-color', '#eee');
        $('#btnBuscarFiltrar').prop('disabled', true);
        $('#cantidad').prop('readonly', true).css('background-color', '#eee');
        $('#modalAgregarMedicamento .modal-title').html('<i class="fa fa-pencil"></i> Completar Lote (Modo Pedido)');
      } else {
        $('#Id_descripcion_medicamento').css('pointer-events', 'auto').css('background-color', '');
        $('#btnBuscarFiltrar').prop('disabled', false);
        $('#cantidad').prop('readonly', false).css('background-color', '');
        $('#modalAgregarMedicamento .modal-title').html('<i class="fa fa-plus-circle"></i> Ingresar Lote de Vacuna');
      }

      $('#modalAgregarMedicamento').modal('show');
    });

    $('#cuerpoTablaMedicamentos').on('click', '.btn-eliminar-fila', function() {
      const index = $(this).data('index');
      listaDetalles.splice(index, 1);
      actualizarTablaDetalles();
    });

    // Lógica de Tags (Mantenida de tu código)
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

    // Validación final antes de guardar
    $('#formularioEntrada').on('submit', function(e) {
      e.preventDefault();
      $('#proveedor').removeClass('input-error');
      $('#receptor').removeClass('input-error');
      $('#fecha_recepcion').removeClass('input-error');
      $('#hora_recepcion').removeClass('input-error');
      $('#btnAbrirModalAgregar').removeClass('btn-error-sombreado');

      let faltanDatosLote = listaDetalles.some(item => item.lote === '' || item.fecha_fabricacion === '' || item.fecha_vencimiento === '');
      if (faltanDatosLote) {
        mostrarAviso("🚫 <b>Datos Incompletos:</b><br><br>Hay vacunas en la lista sin Lote o sin Fechas asignadas. Use el botón amarillo de Editar en cada fila para completar la información.");
        return;
      }

      if ($('#fecha_recepcion').val() === "") { $('#fecha_recepcion').addClass('input-error'); mostrarAviso("La fecha de la recepcion no puede estar vacia."); return; }
      if ($('#hora_recepcion').val() === "") { $('#hora_recepcion').addClass('input-error'); mostrarAviso("La hora de la recepcion no puede estar vacia."); return; }
      if ($('#receptor').val() === "") { $('#receptor').addClass('input-error'); mostrarAviso("Debe seleccionar el Receptor."); return; }
      if ($('#proveedor').val() === "") { $('#proveedor').addClass('input-error'); mostrarAviso("Debe seleccionar el Proveedor/Donante."); return; }
      if (listaDetalles.length === 0) { $('#btnAbrirModalAgregar').addClass('btn-error-sombreado'); mostrarAviso("Debe añadir al menos una vacuna a la lista para generar una entrada."); return; }

      $('#modalEntradaGuardar').modal('show');
    });

    $('#confirmarGuardadoFinal').on('click', function() {
      $('#modalEntradaGuardar').modal('hide');
      // SIMULACIÓN: Aquí se enviaría el formulario real
      // $('#formularioEntrada').off('submit').submit();
      mostrarExito("✅ <b>Entrada registrada (Simulada).</b><br><br>En la versión real, esto guardaría los datos en la base de datos y actualizaría el stock.");
      setTimeout(function() {
        window.location.href = 'vacunacion_inventario_listado.php';
      }, 2500);
    });
  });
</script>