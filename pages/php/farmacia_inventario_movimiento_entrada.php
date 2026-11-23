<?php
include("../../cfg/conexion.php");
$operacion_actual = isset($_GET['op']) ? $_GET['op'] : 'entrada';

$sql_ultimo_id = "SELECT MAX(Id_detalle_inventario) AS ultimo FROM detalle_inventario";
$resultado_id = $conexion->query($sql_ultimo_id);
$row_id = $resultado_id->fetch_assoc();
$proximo_id = ($row_id['ultimo'] ? $row_id['ultimo'] : 0) + 1;

$numero_proyectado = str_pad($proximo_id, 6, "0", STR_PAD_LEFT);

include('includes/headerNav2.php');
verificarAcceso('Generar Entradas de Inventario');
?>

<head>
  <title>Inventario | Entrada de Medicamentos</title>
</head>

<div class="content-wrapper">
  <style>
    #avisoModal {
      z-index: 1000000 !important;
    }

    .tags-input-container {
      display: flex;
      flex-wrap: wrap;
      gap: 5px;
      padding: 4px;
      border: 1px solid #d2d6de;
      background-color: #fff;
      min-height: 34px;
      cursor: text;
    }

    .tags-input-container .tag-badge {
      display: inline-flex;
      align-items: center;
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 12px;
      color: white;
    }

    .tags-input-container .tag-badge i {
      margin-left: 6px;
      cursor: pointer;
      font-size: 10px;
    }

    .tags-input-fake {
      border: none;
      outline: none;
      flex-grow: 1;
      min-width: 150px;
      padding: 2px;
    }

    .table-responsive {
      max-height: 220px;
      overflow-y: auto;
    }

    .table-detalle thead th {
      position: sticky;
      top: 0;
      background-color: #f4f4f4;
      z-index: 1;
    }

    .table-detalle th {
      background-color: #f4f4f4;
      text-align: center;
    }

    .table-detalle td {
      text-align: center;
      vertical-align: middle !important;
    }

    .row-vence-pronto {
      background-color: #fff3cd !important;
    }

    .area-trabajo-blanca {
      background-color: #ffffff;
      padding: 25px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
      margin-bottom: 20px;
      overflow: hidden;
    }

    input[type="date"][readonly],
    input[type="date"][disabled] {
      background-color: #eeeeee !important;
      cursor: not-allowed;
    }

    .btn-error-sombreado {
      box-shadow: 0 0 0 3px crimson;
    }

    .modal.fade .modal-dialog {
      transition: transform 0.3s ease-out;
    }

    .modal.out {
      opacity: 0;
    }
  </style>

  <section class="content-header">
    <h1>Entrada de Medicamentos</h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="#"><i class="fa fa-users"></i>Inventario</a></li>
      <li class="active"><a href="#"><i class="fa fa-arrow-down"></i>Entrada</a></li>
    </ol>
  </section>

  <div id="info_pedido_cargado" class="alert alert-info" style="display: none; margin-bottom: 15px;">
    <h4 style="margin-bottom: 5px;"><i class="icon fa fa-info-circle"></i> Pedido Vinculado: #<span id="txt_id_pedido_cargado"></span></h4>
    <p style="margin: 0;">Se ha cargado un pedido. Use el botón amarillo de Editar (<i class="fa fa-pencil"></i>) en cada fila para asignar el Lote y las Fechas antes de guardar. <b>No se puede cambiar el medicamento ni la cantidad solicitada.</b></p>
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
              <button type="button" class="btn btn-danger btn-sm" id="btnQuitarPedido" style="display: none;">
                <i class="fa fa-times"></i> Quitar Pedido
              </button>
              <button type="button" class="btn btn-primary btn-sm" id="btnAbrirModalPedidos">
                <i class="fa fa-download"></i> Cargar Pedido
              </button>   
            </div>
            <br>

            <div class="box box-primary">
              <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-truck"></i> Datos de la recepción:</h3>
              </div>
              <div class="box-body">
                <div class="row">
                  <div class="col-sm-3">
                    <label>Receptor (*):</label>
                    <?php
                    $id_persona_logueada = $_SESSION['id'] ?? 0;
                    if (empty($id_receptor_seleccionado)) {
                      $id_receptor_seleccionado = $id_persona_logueada;
                    }
                    ?>
                    <select name="receptor" id="receptor" class="form-control" required>
                      <option value="">--- Seleccione el receptor ---</option>
                      <?php
                      $sql_receptor = "SELECT p.id, p.nombre, p.apellido FROM persona p INNER JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona INNER JOIN rol r ON dpr.Id_rol = r.Id_rol WHERE p.estatus IN (1, 2) AND r.Id_rol IN (1, 6) ORDER BY p.nombre ASC";
                      $resultado_receptor = $conexion->query($sql_receptor);
                      while ($row_receptor = $resultado_receptor->fetch_assoc()) {
                        $id_db = (int)$row_receptor['id'];
                        $id_objetivo = (int)$id_receptor_seleccionado;
                        $selected = ($id_db === $id_objetivo) ? 'selected="selected"' : '';
                        echo '<option value="' . $id_db . '" ' . $selected . '>' . htmlspecialchars($row_receptor['nombre'] . ' ' . $row_receptor['apellido']) . '</option>';
                      }
                      ?>
                    </select>
                  </div>
                  <div class="col-sm-3 form-group">
                    <label>Proveedor / Donante (*):</label>
                    <div class="input-group">
                      <select id="proveedor" name="proveedor" class="form-control" required>
                        <option value="">--- Seleccione un proveedor ---</option>
                        <?php
                        $sql_proveedor = "SELECT Id_proveedor, nombre_proveedor FROM proveedor WHERE estatus = 1 ORDER BY nombre_proveedor ASC";
                        $resultado_proveedor = $conexion->query($sql_proveedor);
                        if ($resultado_proveedor && $resultado_proveedor->num_rows > 0) {
                          while ($row_pro = $resultado_proveedor->fetch_assoc()) {
                            echo '<option value="' . $row_pro['Id_proveedor'] . '">' . htmlspecialchars($row_pro['nombre_proveedor']) . '</option>';
                          }
                        }
                        ?>
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
                        <input type="time" id="hora_recepcion" name="hora_recepcion" class="form-control" value="<?php date_default_timezone_set('America/Caracas');
                                                                                                                  echo date('H:i'); ?>">
                      </div>
                    </div>
                  </div>
                  <div class="col-sm-3 form-group">
                    <label>Observaciones generales:</label>
                    <input type="text" id="observaciones_generales" name="observaciones_generales" class="form-control" placeholder="Ej: Cajas en buen estado..." maxlength="255">
                  </div>
                </div>
              </div>
            </div>

            <div class="box box-success">
              <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-medkit"></i> Medicamentos a ingresar:</h3>
                <div class="box-tools pull-right" style="margin-left: 5px;">
                  <button type="button" class="btn btn-primary btn-sm" id="btnAbrirModalAgregar" style="width:200px;"><i class="fa fa-plus"></i> Añadir Medicamento</button>
                  <p></p>
                  <button type="button" class="btn btn-success btn-sm" id="btnAbrirModalCrear" style="width:200px;"><i class="fa fa-plus"></i> Crear Nuevo Medicamento</button>
                </div>
              </div>
              <div class="box-body">
                <div class="table-responsive">
                  <table class="table table-bordered table-striped table-hover table-detalle" id="tablaMedicamentos">
                    <thead>
                      <tr>
                        <th>Medicamento</th>
                        <th>Lote</th>
                        <th>F. Fabricación</th>
                        <th>F. Vencimiento</th>
                        <th>Cantidad</th>
                        <th>Acciones</th>
                      </tr>
                    </thead>
                    <tbody id="cuerpoTablaMedicamentos">
                      <tr id="filaVacia">
                        <td colspan="6" class="text-center text-muted">Aún no se han añadido medicamentos a esta entrada.</td>
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

  <div class="modal fade" id="modalAgregarMedicamento" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h4 class="modal-title"><i class="fa fa-plus-circle"></i> Ingresar Lote de Medicamento</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="filtro_busqueda_rapida">Busqueda Rapida:</label>
                <input type="text" id="filtro_busqueda_rapida" name="filtro_busqueda_rapida" class="form-control" placeholder="Escriba nombre, principio activo, presentacion, etc...">
              </div>
            </div>
            <br><br>
            <div class="col-sm-12 form-group">
              <label>Medicamento del Catálogo (*):</label>
              <div class="input-group">
                <select id="Id_descripcion_medicamento" name="Id_descripcion_medicamento" class="form-control">
                  <option value="">--- Seleccione un Medicamento ---</option>
                  <?php
                  $sql_medicamentos = "SELECT dm.Id AS id_desc, m.nombre_medicamento, p.nombre_presentacion, GROUP_CONCAT(CONCAT(IFNULL(pa.nombre,''), ' ', IFNULL(dpm.cantidad_unidad_medida,''), ' ', IFNULL(um.unidad,'')) SEPARATOR ' + ') AS componentes FROM descripcion_medicamento dm INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento INNER JOIN presentacion p ON dm.Id_presentacion = p.Id_presentacion LEFT JOIN detalle_principio_medicamento dpm ON dm.Id = dpm.id_descripcion_medicamento LEFT JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida LEFT JOIN principio_activo pa ON dpm.id_principio_activo = pa.Id_principio_activo WHERE m.estatus = 1 AND (dm.estatus = '1' OR dm.estatus = 1) GROUP BY dm.Id ORDER BY m.nombre_medicamento ASC";
                  $resultado_medicamentos = $conexion->query($sql_medicamentos);
                  if ($resultado_medicamentos && $resultado_medicamentos->num_rows > 0) {
                    while ($row_med = $resultado_medicamentos->fetch_assoc()) {
                      $comp = trim($row_med['componentes']) ? " (" . htmlspecialchars($row_med['componentes']) . ")" : "";
                      $componentesLimpios = trim($row_med['componentes']) ? htmlspecialchars($row_med['componentes']) : 'Sin principios activos registrados';
                      echo '<option value="' . $row_med['id_desc'] . '" data-nombre="' . htmlspecialchars($row_med['nombre_medicamento'] . " [" . $row_med['nombre_presentacion'] . "]") . '" data-componentes="' . $componentesLimpios . '">' . htmlspecialchars($row_med['nombre_medicamento']) . $comp . " - [" . htmlspecialchars($row_med['nombre_presentacion']) . "]" . '</option>';
                    }
                  }
                  ?>
                </select>
                <span class="input-group-btn">
                  <button class="btn btn-info" type="button" id="btnBuscarFiltrar" data-toggle="modal" data-target="#modalBúsquedaAvanzadaMedicamento" title="Búsqueda Avanzada de Medicamentos" style="height: 34px;"><i><img src="../../recursos/imagenes/iconos/buscar.png" style="width:10px; height:10px;"></i></button>
                </span>
              </div>
            </div>
            <div class="col-sm-4 form-group">
              <label>Existencia Actual:</label>
              <input type="text" id="existencia_actual" class="form-control" readonly disabled style="background-color: #f9f9f9;">
            </div>
            <div class="col-sm-4 form-group">
              <label>Stock Mínimo:</label>
              <input type="text" id="stock_minimo" class="form-control" readonly disabled style="background-color: #f9f9f9;">
            </div>
            <div class="col-sm-4 form-group">
              <label>Stock Máximo:</label>
              <input type="text" id="stock_maximo" class="form-control" readonly disabled style="background-color: #f9f9f9;">
            </div>
            <div class="clearfix"></div>
            <div class="col-sm-4 form-group mt-3">
              <label style="width: 100%;">Número de Lote (*):
                <button type="button" id="btnCopiarUltimoLote" class="btn btn-info btn-xs pull-right" style="display: none; padding: 2px 5px;" title="Copiar Lote y Fechas del último ingresado"><i class="fa fa-copy"></i> Usar Último</button>
              </label>
              <div class="input-group">
                <input type="text" id="lote" class="form-control" list="lista_lotes" placeholder="Ej: L-2026X" style="text-transform: uppercase;">
                <datalist id="lista_lotes"></datalist>
                <span class="input-group-btn">
                  <button class="btn btn-info" type="button" id="infoLote" title="Informacion del lote" style="height: 34px;"><i><img src="../../recursos/imagenes/iconos/info.png" style="width:10px; height:10px;"></i></button>
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
              <label>Unidades a Ingresar (*):</label>
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

  <div class="modal fade" id="modalConfirmarLote" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
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

  <div class="modal fade" id="modalBúsquedaAvanzadaMedicamento" role="dialog" aria-labelledby="modalBúsquedaAvanzadaMedicamentoLabel" aria-hidden="true">
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
                  <input type="text" id="filtro_nombre" name="filtro_nombre" class="form-control" placeholder="Escriba nombre o ID...">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="filtro_presentacion">Presentación:</label>
                  <select id="filtro_presentacion" name="filtro_presentacion" class="form-control">
                    <option value="">-- Todos --</option>
                    <?php
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
          <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-second" id="btnLimpiarFiltros">Limpiar Filtros</button>
          <button type="button" class="btn btn-primary" id="btnAplicarFiltros">Aplicar Filtros</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalCargarPedido" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-primary">
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
              <?php
              $sql_pedidos = "SELECT p.id_pedido, p.fecha_creacion, pr.nombre_proveedor, p.id_proveedor FROM pedidos p INNER JOIN proveedor pr ON p.id_proveedor = pr.Id_proveedor WHERE p.estado = 'Pendiente' AND p.estatus = 1";
              $res_pedidos = $conexion->query($sql_pedidos);
              if ($res_pedidos && $res_pedidos->num_rows > 0) {
                while ($row_p = $res_pedidos->fetch_assoc()) {
                  echo "<tr>
                    <td class='text-center'><strong>#" . $row_p['id_pedido'] . "</strong></td>
                    <td class='text-center'>" . date('d/m/Y H:i', strtotime($row_p['fecha_creacion'])) . "</td>
                    <td class='text-center'>" . $row_p['nombre_proveedor'] . "</td>
                    <td class='text-center'>
                      <button type='button' class='btn btn-info btn-xs' onclick='verDetallePedidoPrevio(" . $row_p['id_pedido'] . ")' title='Ver medicamentos de este pedido'><i class='fa fa-info-circle'></i> Ver Info</button>
                      <button type='button' class='btn btn-success btn-xs btn-seleccionar-pedido' data-id='" . $row_p['id_pedido'] . "' data-idprov='" . $row_p['id_proveedor'] . "' style='margin-left: 5px;'><i class='fa fa-check'></i> Seleccionar</button>
                    </td>
                  </tr>";
                }
              } else {
                echo "<tr><td colspan='4' class='text-center text-muted'>No hay pedidos pendientes en este momento.</td></tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalVerDetallePedidoPrevio" tabindex="-1" role="dialog" style="z-index: 1060;">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-info">
          <h4 class="modal-title"><i class="fa fa-shopping-basket"></i> Vista Previa - Contenido del Pedido <span id="num_pedido_previo_titulo"></span></h4>
        </div>
        <div class="modal-body">
          <div style="max-height: 280px; overflow-y: auto; border-bottom: 1px solid #eee;">
            <table class="table table-bordered table-striped" style="margin-bottom: 0;">
              <thead style="background-color: #f8f9fa;">
                <tr>
                  <th>Medicamento / Presentación</th>
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

  <div class="modal fade" id="modalQuitarPedido" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h5 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Quitar Pedido Vinculado</h5>
        </div>
        <div class="modal-body">
          <p>¿Está seguro que desea quitar el pedido vinculado?</p>
          <p class="text-danger" style="margin: 0;">
            <b>Se perderán todos los medicamentos cargados en la tabla</b> (incluyendo los lotes y fechas que ya haya asignado a cada fila).
          </p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-danger" id="confirmarQuitarPedido"><i class="fa fa-times"></i> Quitar Pedido</button>
        </div>
      </div>
    </div>
  </div>

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

  <div class="modal fade" id="modalRegresarInventario" tabindex="-1" role="dialog" aria-labelledby="modalRegresarInventarioLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h5 class="modal-title" id="modalRegresarInventarioLabel">Confirmación de Regreso</h5>
        </div>
        <div class="modal-body">
          <p>Al hacer clic en "Abandonar Formulario", perderá todos los datos no guardados. ¿Desea continuar?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <a href="farmacia_inventario_listado.php" class="btn btn-danger">Abandonar Formulario</a>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalEntradaGuardar" tabindex="-1" role="dialog" aria-labelledby="modalEntradaGuardarLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-green">
          <h5 class="modal-title" id="modalEntradaGuardarLabel">Confirmación de Guardado</h5>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea guardar la información de esta entrada del inventario?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-success" id="confirmarGuardadoFinal">Guardar</button>
        </div>
      </div>
    </div>
  </div>

  <?php
  include('modales/inventario/medicamentos_agregar_modal.php');
  ?>

</div>

<?php
if (isset($conexion)) {
  $conexion->close();
}
include('includes/footer.php');
?>

<script>
  $(document).ready(function() {
    function esc(str) {
      if (str === null || str === undefined) return '';
      return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    $('#infoLote').tooltip({
      html: true,
      placement: 'right',
      title: 'Llene los datos del lote'
    });

    let listaDetalles = [];
    let lotesCargados = [];
    let editandoIndex = -1;
    let ultimoLoteIngresado = null;

    const hoy = new Date().toISOString().split('T')[0];
    const mananaDate = new Date();
    mananaDate.setDate(mananaDate.getDate() + 1);
    const manana = mananaDate.toISOString().split('T')[0];

    $('#fecha_fabricacion').attr('max', hoy);

    $('.modal').on('click', '[data-dismiss="modal"]', function(e) {
      e.preventDefault();
      $(this).closest('.modal').modal('hide');
    });

    $('.modal').on('hidden.bs.modal', function() {
      if ($('.modal:visible').length) {
        $('body').addClass('modal-open');
      } else {
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
      }
    });

    $('.modal').on('show.bs.modal', function() {
      $(this).removeClass('out');
    });

    window.mostrarAviso = function(mensaje) {
      $('#headerAviso').removeClass('bg-green').addClass('bg-crimson');
      $('#avisoTexto').html(mensaje);
      $('#avisoModal').modal('show');
    };

    window.mostrarExito = function(mensaje) {
      $('#headerAviso').removeClass('bg-crimson').addClass('bg-green');
      $('#avisoTexto').html(mensaje);
      $('#avisoModal').modal('show');
    };

    $('#btnAbrirModalCrear').on('click', function() {
      $('#med_formularioMedicamento')[0].reset();
      $('#med_modal_principal').modal('show');
    });

    function limpiarFormularioModal() {
      $('#Id_descripcion_medicamento').val('').trigger('change');
      $('#lote, #fecha_fabricacion, #fecha_vencimiento, #cantidad').val('').removeClass('input-error');
      $('#fecha_fabricacion, #fecha_vencimiento').prop('readonly', false);
      $('#fecha_vencimiento').prop('disabled', true).removeAttr('min');
      $('#lote').css('border-color', '#ced4da');
      $('#existencia_actual, #stock_minimo, #stock_maximo').val('');
      $('#btnConfirmarAgregarMedicamento').prop('disabled', false);
    }

    $('#abrirModalRegresar').on('click', function() {
      $('#modalRegresarInventario').modal('show');
    });

    const medicamentoSelectPrincipal = $('#Id_descripcion_medicamento');
    window.opcionesOriginalesMedicamentos = medicamentoSelectPrincipal.html();

    $('#btnLimpiarFiltros').on('click', function() {
      $('#formFiltroModal')[0].reset();
      medicamentoSelectPrincipal.html(window.opcionesOriginalesMedicamentos);
    });

    $('#btnAplicarFiltros').on('click', function(e) {
      e.preventDefault();
      let datosFiltro = $('#formFiltroModal').serialize();
      const formValores = $('#formFiltroModal').serializeArray();
      const busquedaRapida = $('#filtro_busqueda_rapida').val().trim();
      let hayDatos = false;

      $.each(formValores, function(i, field) {
        if (field.value.trim() !== "") hayDatos = true;
      });

      if (busquedaRapida !== "") {
        hayDatos = true;
        datosFiltro += (datosFiltro.length > 0 ? '&' : '') + 'filtro_busqueda_rapida=' + encodeURIComponent(busquedaRapida);
      }

      let modoOperacion = $('#op').val();
      datosFiltro += (datosFiltro.length > 0 ? '&' : '') + 'modo=' + encodeURIComponent(modoOperacion);

      if (!hayDatos) {
        mostrarAviso("Todos los filtros están vacíos. Escriba o seleccione al menos una opción para buscar.");
        return;
      }

      $.ajax({
        url: '../../cfg/ajax/filtrar_medicamentos.php',
        type: 'POST',
        data: datosFiltro,
        dataType: 'json',
        success: function(response) {
          medicamentoSelectPrincipal.empty();
          medicamentoSelectPrincipal.append('<option value="">--- Seleccione un Medicamento ---</option>');
          if (response.length > 0) {
            response.forEach(function(item) {
              medicamentoSelectPrincipal.append('<option value="' + item.id_desc + '" data-nombre="' + esc(item.nombre_completo) + '">' + esc(item.nombre_completo) + '</option>');
            });
          } else {
            medicamentoSelectPrincipal.append('<option value="" disabled>🛑 No se encontraron medicamentos.</option>');
          }
        },
        error: function(jqXHR, textStatus, errorThrown) {
          console.error("Error en AJAX de filtrado avanzado: ", textStatus, errorThrown);
        }
      });
    });

    $('#btnGuardarProveedor').on('click', function() {
      const nombreProveedor = $('#nuevo_nombre_proveedor').val().trim();
      if (nombreProveedor === '') {
        $('#nuevo_nombre_proveedor').addClass('input-error');
        mostrarAviso('Por favor, ingrese el nombre del proveedor.');
        return;
      }
      $('#nuevo_nombre_proveedor').removeClass('input-error');
      $.ajax({
        url: '../../cfg/ajax/guardar_proveedor.php',
        type: 'POST',
        data: {
          nombre_proveedor: nombreProveedor
        },
        dataType: 'json',
        success: function(response) {
          if (response.exito) {
            const nuevaOpcion = new Option(response.nombre, response.id, false, true);
            $('#proveedor').append(nuevaOpcion).trigger('change');
            $('#modalNuevoProveedor').modal('hide');
            $('#nuevo_nombre_proveedor').val('');
            mostrarExito('Proveedor Añadido Exitosamente.');
          } else {
            mostrarAviso(response.mensaje || 'Error al guardar el proveedor.');
          }
        },
        error: function() {
          mostrarAviso('Error de comunicación con el servidor al intentar guardar el proveedor.');
        }
      });
    });

    $('#modalNuevoProveedor').on('hidden.bs.modal', function() {
      $('#nuevo_nombre_proveedor').val('').removeClass('input-error');
    });

    $('#btnAbrirModalAgregar').on('click', function() {
      editandoIndex = -1;
      limpiarFormularioModal();
      $('#btnConfirmarAgregarMedicamento').html('<i class="fa fa-check"></i> Añadir a la Lista').prop('disabled', false);
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
        $('#fecha_fabricacion').val(ultimoLoteIngresado.fecha_fabricacion).trigger('change');
        $('#fecha_vencimiento').val(ultimoLoteIngresado.fecha_vencimiento);
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
          mostrarAviso('⚠️ <b>Atención:</b> Debe seleccionar un <b>Medicamento del Catálogo</b> antes de llenar estos campos.');
        }
        return false;
      }
    });

    $('#Id_descripcion_medicamento').on('change', function() {
      const medicamentoId = $(this).val();
      $('#lista_lotes').empty();
      lotesCargados = [];
      $('#lote').val('').css('border-color', '#ced4da').data('lote-confirmado', '');
      $('#fecha_fabricacion').val('').prop('readonly', false);
      $('#fecha_vencimiento').val('').prop('disabled', true).prop('readonly', false).removeAttr('min');
      $('#cantidad').val('');
      $('#infoLote').attr('data-original-title', 'Llene los datos del lote').attr('title', 'Llene los datos del lote');
      $('#btnConfirmarAgregarMedicamento').prop('disabled', false);

      if (medicamentoId) {
        $.ajax({
          url: '../../cfg/ajax/obtener_descripcion_medicamento.php',
          type: 'POST',
          data: {
            id: medicamentoId,
            modo: 'entrada'
          },
          dataType: 'json',
          success: function(data) {
            if (!data.error) {
              const extActual = data.existencia_actual !== null && data.existencia_actual !== undefined ? parseInt(data.existencia_actual) : 0;
              const sMinimo = data.stock_minimo !== null && data.stock_minimo !== undefined ? parseInt(data.stock_minimo) : 0;
              const sMaximo = data.stock_maximo !== null && data.stock_maximo !== undefined ? parseInt(data.stock_maximo) : 0;

              $('#existencia_actual').val(extActual);
              $('#stock_minimo').val(sMinimo);
              $('#stock_maximo').val(sMaximo);

              if (sMinimo <= 0 || sMaximo <= 0) {
                $('#btnConfirmarAgregarMedicamento').prop('disabled', true);
                mostrarAviso('⚠️ <b>Medicamento sin configurar:</b><br><br>Este medicamento no tiene establecido su <b>Stock Mínimo</b> o <b>Stock Máximo</b>.<br><br>Debe configurar ambos valores en el catálogo de medicamentos antes de poder registrar entradas de inventario para él.');
                $('#cantidad').prop('disabled', true);
                return;
              }

              if (data.lotes && data.lotes.length > 0) {
                lotesCargados = data.lotes;
                data.lotes.forEach(function(item) {
                  $('#lista_lotes').append('<option value="' + esc(item.lote) + '">');
                });
              }

              let loteActual = $('#lote').val().trim().toUpperCase();
              if (loteActual !== '') {
                const loteEncontrado = lotesCargados.find(l => l.lote.toUpperCase() === loteActual);
                if (loteEncontrado) {
                  aplicarLoteExistente(loteEncontrado);
                  $('#lote').data('lote-confirmado', loteActual);
                }
              }

              if (sMaximo > 0 && extActual >= sMaximo) {
                $('#cantidad').prop('disabled', true).val('').attr('placeholder', 'Bloqueado (Stock Lleno)');
                mostrarAviso(`🚫 <b>Atención:</b> La existencia actual (${extActual}) ya es igual o superior al Stock Máximo permitido (${sMaximo}).<br><br>No se permite el ingreso de más unidades de este medicamento.`);
              } else {
                $('#cantidad').prop('disabled', false).attr('placeholder', 'Solo números, sin 0 inicial');
              }
            } else {
              mostrarAviso('No se pudo obtener la información del medicamento.');
            }
          },
          error: function() {
            mostrarAviso('Error de conexión al obtener la información del medicamento.');
          }
        });
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

        $('#modalConfirmarLoteTexto').html(`¿Está seguro de sumar la cantidad al mismo lote <b>(${esc(loteEscrito)})</b> que ya existe en el sistema?`);
        $('#modalConfirmarLote').modal('show');

        $('#btnAceptarLote').off('click').on('click', function() {
          loteAceptado = true;
          $('#lote').data('lote-confirmado', loteEscrito);
          aplicarLoteExistente(loteEncontrado);
        });

        $('#modalConfirmarLote').off('hidden.bs.modal').on('hidden.bs.modal', function() {
          if ($('.modal-backdrop').length > 1) {
            $('.modal-backdrop').last().remove();
          }
          if ($('#modalAgregarMedicamento').is(':visible')) {
            $('body').addClass('modal-open');
          }
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
        "<b>Proveedor:</b> " + esc(loteEncontrado.nombre_proveedor || 'No registrado') + "<br>" +
        "<b>F. Fabricación:</b> " + esc(loteEncontrado.fecha_fabricacion || 'N/A') + "<br>" +
        "<b>F. Vencimiento:</b> " + esc(loteEncontrado.fecha_vencimiento || 'N/A') + "<br>" +
        "<b>Stock Físico:</b> " + esc(loteEncontrado.cantidad_actual || 0) +
        "</div>";
      $('#infoLote').attr('data-original-title', infoHtml).tooltip('fixTitle');
    }

    $('#fecha_fabricacion').on('change', function() {
      const fabDate = $(this).val();

      if (fabDate) {
        if (fabDate > hoy) {
          mostrarAviso('La fecha de fabricación no puede ser una fecha futura.');
          $(this).val('');
          $('#fecha_vencimiento').prop('disabled', true).val('').removeAttr('min');
          return;
        }
        const minVenc = fabDate > manana ? fabDate : manana;
        $('#fecha_vencimiento').prop('disabled', false).attr('min', minVenc);
        if ($('#fecha_vencimiento').val() && $('#fecha_vencimiento').val() < minVenc) {
          $('#fecha_vencimiento').val('');
        }
      } else {
        $('#fecha_vencimiento').prop('disabled', true).val('').removeAttr('min');
      }
    });

    $('#btnAbrirModalPedidos').on('click', function() {
      $('#modalCargarPedido').modal('show');
    });

    $('.btn-seleccionar-pedido').on('click', function() {
      let idPedido = $(this).data('id');
      let idProveedor = $(this).data('idprov');
      cargarPedidoLogica(idPedido, idProveedor);
      $('#modalCargarPedido').modal('hide');
    });

    $('#btnQuitarPedido').on('click', function() {
      $('#modalQuitarPedido').modal('show');
    });

    $('#confirmarQuitarPedido').on('click', function() {
      // 1) Limpiar estado de la lista
      listaDetalles = [];
      editandoIndex = -1;

      // 2) Resetear el ID del pedido
      $('#id_pedido_oculto').val('0');

      // 3) Ocultar el banner y el botón
      $('#info_pedido_cargado').slideUp();
      $('#btnQuitarPedido').hide();

      // 4) Volver a mostrar los botones de agregar manual
      $('#btnAbrirModalAgregar, #btnAbrirModalCrear').show();
      $('#btnAbrirModalAgregar').removeClass('btn-error-sombreado');

      // 5) Reactivar el select de proveedor
      $('#proveedor').css('pointer-events', 'auto').css('background-color', '');

      // 6) Refrescar la tabla (la deja vacía)
      actualizarTablaDetalles();

      // 7) Cerrar modal y avisar
      $('#modalQuitarPedido').modal('hide');
      mostrarExito('Pedido quitado exitosamente.<br>Ahora puede agregar medicamentos manualmente.');
    });

    function cargarPedidoLogica(idPedido, idProveedor) {
      $('#proveedor').val(idProveedor).trigger('change');
      $('#proveedor').css('pointer-events', 'none').css('background-color', '#eee');
      $('#id_pedido_oculto').val(idPedido);
      $('#txt_id_pedido_cargado').text(idPedido.toString().padStart(6, '0'));
      $('#info_pedido_cargado').slideDown();
      $('#btnAbrirModalAgregar, #btnAbrirModalCrear').hide();
      $('#btnQuitarPedido').show();

      $.ajax({
        url: '../../cfg/ajax/obtener_detalles_pedido.php',
        type: 'POST',
        data: {
          id_pedido: idPedido
        },
        dataType: 'json',
        success: function(data) {
          if (data && data.length > 0) {
            listaDetalles = [];
            data.forEach(function(item) {
              listaDetalles.push({
                id_medicamento: item.id_descripcion_medicamento,
                nombre_medicamento: item.nombre_medicamento + ' [' + item.nombre_presentacion + ']',
                componentes: item.componentes || 'Sin principios activos registrados',
                lote: '',
                fecha_fabricacion: '',
                fecha_vencimiento: '',
                cantidad: parseInt(item.cantidad_solicitada)
              });
            });
            actualizarTablaDetalles();
            mostrarExito("✅ <b>Pedido cargado.</b><br><br>Se han traído las cantidades solicitadas. <b>Atención:</b> Use el botón amarillo de Editar en cada fila para asignar el número de Lote y las Fechas correspondientes antes de guardar.");
          }
        }
      });
    }

    <?php if (isset($_GET['id_pedido_auto']) && isset($_GET['id_proveedor_auto'])) : ?>
      setTimeout(function() {
        cargarPedidoLogica("<?php echo (int)$_GET['id_pedido_auto']; ?>", "<?php echo (int)$_GET['id_proveedor_auto']; ?>");
      }, 500);
    <?php endif; ?>

    window.verDetallePedidoPrevio = function(idPedido) {
      let idFormateado = idPedido.toString().padStart(6, '0');
      $('#num_pedido_previo_titulo').text('#' + idFormateado);
      $('#cuerpo_detalle_pedido_previo').html('<tr><td colspan="2" class="text-center"><i class="fa fa-spinner fa-spin"></i> Consultando medicamentos...</td></tr>');
      $('#modalVerDetallePedidoPrevio').modal('show');

      $.ajax({
        url: '../../cfg/ajax/obtener_detalle_pedido.php',
        type: 'POST',
        data: {
          id_pedido: idPedido
        },
        dataType: 'json',
        success: function(respuesta) {
          var html = '';
          if (respuesta && respuesta.length > 0) {
            respuesta.forEach(function(item) {
              html += '<tr>';
              html += '  <td><strong>' + esc(item.nombre_medicamento) + '</strong><br><small class="text-muted">' + esc(item.presentacion) + '</small></td>';
              html += '  <td class="text-center"><span class="badge bg-primary" style="font-size: 14px;">' + esc(item.cantidad_solicitada) + '</span></td>';
              html += '</tr>';
            });
          } else {
            html = '<tr><td colspan="2" class="text-center text-muted">Este pedido no tiene registros asociados.</td></tr>';
          }
          $('#cuerpo_detalle_pedido_previo').html(html);
        },
        error: function() {
          $('#cuerpo_detalle_pedido_previo').html('<tr><td colspan="2" class="text-center text-danger"><i class="fa fa-exclamation-triangle"></i> No se pudo establecer conexión con el servidor.</td></tr>');
        }
      });
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

      if (sMin <= 0 || sMax <= 0) {
        mostrarAviso('⚠️ <b>Medicamento sin configurar:</b><br><br>Este medicamento no tiene establecido su <b>Stock Mínimo</b> o <b>Stock Máximo</b>.<br><br>Debe configurar ambos valores en el catálogo antes de poder agregar entradas.');
        return;
      }
      if (!id_med) {
        $('#Id_descripcion_medicamento').addClass('input-error');
        mostrarAviso('Seleccione un medicamento.');
        return;
      }
      if (!lote) {
        $('#lote').addClass('input-error');
        mostrarAviso('Ingrese el número de lote.');
        return;
      }
      if (!f_fab) {
        $('#fecha_fabricacion').addClass('input-error');
        mostrarAviso('Ingrese la fecha de fabricación.');
        return;
      }
      if (!f_venc) {
        $('#fecha_vencimiento').addClass('input-error');
        mostrarAviso('Ingrese la fecha de vencimiento.');
        return;
      }
      if (isNaN(cant) || cant <= 0) {
        $('#cantidad').addClass('input-error');
        mostrarAviso('La cantidad debe ser mayor a 0.');
        return;
      }

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
        mostrarAviso(`⚠️ <b>Límite excedido:</b> Actualmente hay ${existencia} unidades y ${cantidadAcumuladaLista} ya agregadas en la tabla.<br><br>El máximo es ${sMax}. <b>Solo puedes ingresar un máximo de ${permitido} unidades nuevas adicionales.</b>`);
        return;
      }
      if (sMin > 0 && totalProyectado < sMin) {
        $('#cantidad').addClass('input-error');
        mostrarAviso(`La cantidad ingresada no cubre el Stock Mínimo requerido (${sMin}). Actualmente hay ${existencia} unidades. Debes ingresar al menos ${sMin - inventarioActualMasLista} unidades para estabilizar el inventario.`);
        return;
      }
      if (f_venc <= hoy) {
        $('#fecha_vencimiento').addClass('input-error');
        mostrarAviso('La fecha de vencimiento debe ser una fecha futura (no puede ser hoy ni antes).');
        return;
      }
      if (f_venc < f_fab) {
        $('#fecha_vencimiento').addClass('input-error');
        mostrarAviso('La fecha de vencimiento no puede ser anterior a la fecha de fabricación.');
        return;
      }

      const existeIndex = listaDetalles.findIndex(item => item.id_medicamento === id_med && item.lote === lote);
      if (existeIndex !== -1 && existeIndex !== editandoIndex) {
        mostrarAviso('Este medicamento con el mismo Lote ya está en la lista. Si desea modificarlo, edítelo directamente usando el botón amarillo en la tabla.');
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

      ultimoLoteIngresado = {
        lote: lote,
        fecha_fabricacion: f_fab,
        fecha_vencimiento: f_venc
      };

      actualizarTablaDetalles();
      $('#modalAgregarMedicamento').modal('hide');
    });

    function actualizarTablaDetalles() {
      const tbody = $('#cuerpoTablaMedicamentos');
      tbody.empty();

      if (listaDetalles.length === 0) {
        tbody.append('<tr id="filaVacia"><td colspan="6" class="text-center text-muted">Aún no se han añadido medicamentos a esta entrada.</td></tr>');
      } else {
        listaDetalles.forEach((item, index) => {
          let rowClass = "";
          let diffDias = (new Date(item.fecha_vencimiento) - new Date(hoy)) / (1000 * 60 * 60 * 24);
          if (diffDias < 180) rowClass = "row-vence-pronto";

          let etiquetaLote = item.lote ? `<strong>${esc(item.lote)}</strong>` : `<span class="text-danger"><i class="fa fa-exclamation-triangle"></i> Falta Lote</span>`;
          let etiquetaFab = item.fecha_fabricacion ? esc(item.fecha_fabricacion) : `<span class="text-danger">Pendiente</span>`;
          let etiquetaVenc = item.fecha_vencimiento ? esc(item.fecha_vencimiento) : `<span class="text-danger">Pendiente</span>`;

          tbody.append(`
            <tr class="${rowClass}">
              <td style="text-align: left;">${esc(item.nombre_medicamento)}</td>
              <td>${etiquetaLote}</td>
              <td>${etiquetaFab}</td>
              <td>${etiquetaVenc} ${diffDias < 180 ? ' <i class="fa fa-warning text-warning" title="Vence pronto"></i>' : ''}</td>
              <td><span class="badge bg-green" style="font-size:14px;">${esc(item.cantidad)}</span></td>
              <td>
                <button type="button" class="btn btn-warning btn-xs btn-editar-fila" data-index="${index}" title="Editar medicamento"><i><img src="../../recursos/imagenes/iconos/editar.png" style="width:10px; height:10px;"></i></button>
                <button type="button" class="btn btn-danger btn-xs btn-eliminar-fila" data-index="${index}" title="Eliminar de la lista"><i><img src="../../recursos/imagenes/iconos/Delete.png" style="width:10px; height:10px;"></i></button>
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
      $('#fecha_fabricacion').val(item.fecha_fabricacion).trigger('change');
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
        $('#modalAgregarMedicamento .modal-title').html('<i class="fa fa-plus-circle"></i> Ingresar Lote de Medicamento');
      }

      $('#modalAgregarMedicamento').modal('show');
    });

    $('#cuerpoTablaMedicamentos').on('click', '.btn-eliminar-fila', function() {
      const index = $(this).data('index');
      listaDetalles.splice(index, 1);
      actualizarTablaDetalles();
    });

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
          const $tag = $(`<span class="tag-badge bg-primary">${esc(tag)} <i class="fa fa-times remove-tag" data-index="${index}"></i></span>`);
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
            if (tagsArray.length === 0) {
              $('#btnLimpiarFiltros').click();
            } else {
              $('#btnAplicarFiltros').click();
            }
          }
        }
      });

      $contenedor.on('click', '.remove-tag', function() {
        let index = $(this).data('index');
        tagsArray.splice(index, 1);
        renderizarTags();
        if (tagsArray.length === 0) {
          $('#btnLimpiarFiltros').click();
        } else {
          $('#btnAplicarFiltros').click();
        }
      });

      $contenedor.on('click', function() {
        $inputFalso.focus();
      });
      $('#btnLimpiarFiltros').on('click', function() {
        tagsArray = [];
        renderizarTags();
      });
    }

    inicializarTags('#filtro_busqueda_rapida');
    inicializarTags('#filtro_principios');

    function actualizarSelectMedicamentosSilencio() {
      if ($('#modalAgregarMedicamento').is(':visible')) return;
      const hayFiltroBusqueda = $('#filtro_busqueda_rapida').val().trim() !== '';
      let hayFiltrosAvanzados = false;
      $.each($('#formFiltroModal').serializeArray(), function(i, field) {
        if (field.value.trim() !== "") hayFiltrosAvanzados = true;
      });

      if (!hayFiltroBusqueda && !hayFiltrosAvanzados) {
        const valorSeleccionado = $('#Id_descripcion_medicamento').val();
        let modoOperacion = $('#op').val();

        $.ajax({
          url: '../../cfg/ajax/filtrar_medicamentos.php',
          type: 'POST',
          data: {
            recarga_silenciosa: true,
            modo: modoOperacion
          },
          dataType: 'json',
          success: function(response) {
            const select = $('#Id_descripcion_medicamento');
            let existeEnRespuesta = false;
            if (response.length > 0) {
              existeEnRespuesta = response.some(item => item.id_desc == valorSeleccionado);
            }

            let opcionInyectada = "";
            if (valorSeleccionado && !existeEnRespuesta) {
              let selectedOption = $('#Id_descripcion_medicamento option:selected');
              if (selectedOption.length > 0) {
                opcionInyectada = selectedOption[0].outerHTML;
              }
            }

            let nuevasOpciones = '<option value="">--- Seleccione un Medicamento ---</option>';
            if (opcionInyectada !== "") {
              nuevasOpciones += opcionInyectada;
            }

            if (response.length > 0) {
              response.forEach(function(item) {
                if (item.id_desc != valorSeleccionado || existeEnRespuesta) {
                  const comp = item.componentes ? ` data-componentes="${esc(item.componentes)}"` : '';
                  nuevasOpciones += `<option value="${item.id_desc}" data-nombre="${esc(item.nombre_completo)}"${comp}>${esc(item.nombre_completo)}</option>`;
                }
              });
            } else if (opcionInyectada === "") {
              nuevasOpciones += '<option value="" disabled>🛑 No se encontraron medicamentos.</option>';
            }

            select.html(nuevasOpciones);
            if (valorSeleccionado) {
              select.val(valorSeleccionado);
            }
            window.opcionesOriginalesMedicamentos = nuevasOpciones;
          }
        });
      }
    }

    setInterval(actualizarSelectMedicamentosSilencio, 2000);

    function actualizarNumeroOperacionSilencio() {
      $.ajax({
        url: '../../cfg/ajax/obtener_proximo_id_operacion.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
          if (response && response.numero_proyectado) {
            $('#badge_numero_operacion').text('#' + response.numero_proyectado);
          }
        },
        error: function() {
          console.log("No se pudo actualizar el nro de operación en 2do plano.");
        }
      });
    }

    setInterval(actualizarNumeroOperacionSilencio, 5000);

    $('#formularioEntrada').on('submit', function(e) {
      e.preventDefault();

      $('#proveedor, #receptor, #fecha_recepcion, #hora_recepcion').removeClass('input-error');
      $('#btnAbrirModalAgregar').removeClass('btn-error-sombreado');

      let faltanDatosLote = listaDetalles.some(item => item.lote === '' || item.fecha_fabricacion === '' || item.fecha_vencimiento === '');
      if (faltanDatosLote) {
        mostrarAviso("🚫 <b>Datos Incompletos:</b><br><br>Hay medicamentos en la lista sin Lote o sin Fechas asignadas. Use el botón amarillo de Editar en cada fila para completar la información.");
        return;
      }
      if ($('#fecha_recepcion').val() === "") {
        $('#fecha_recepcion').addClass('input-error');
        mostrarAviso("La fecha de recepción no puede estar vacía.");
        return;
      }
      if ($('#hora_recepcion').val() === "") {
        $('#hora_recepcion').addClass('input-error');
        mostrarAviso("La hora de recepción no puede estar vacía.");
        return;
      }
      if ($('#receptor').val() === "") {
        $('#receptor').addClass('input-error');
        mostrarAviso("Debe seleccionar el Receptor.");
        return;
      }
      if ($('#proveedor').val() === "") {
        $('#proveedor').addClass('input-error');
        mostrarAviso("Debe seleccionar el Proveedor/Donante.");
        return;
      }
      if (listaDetalles.length === 0) {
        $('#btnAbrirModalAgregar').addClass('btn-error-sombreado');
        mostrarAviso("Debe añadir al menos un medicamento a la lista para generar una entrada.");
        return;
      }

      $('#modalEntradaGuardar').modal('show');
    });

    $('#confirmarGuardadoFinal').on('click', function() {
      $('#modalEntradaGuardar').modal('hide');
      $('#formularioEntrada').off('submit').submit();
    });
  });
</script>