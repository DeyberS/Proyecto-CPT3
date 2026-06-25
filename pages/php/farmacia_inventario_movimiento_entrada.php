<?php
// Incluir la conexión a la base de datos (se asume esta ruta)
include("../../cfg/conexion.php");
$operacion_actual = isset($_GET['op']) ? $_GET['op'] : 'entrada';

$sql_ultimo_id = "SELECT MAX(Id_detalle_inventario) AS ultimo FROM detalle_inventario";
$resultado_id = $conexion->query($sql_ultimo_id);
$row_id = $resultado_id->fetch_assoc();
$proximo_id = ($row_id['ultimo'] ? $row_id['ultimo'] : 0) + 1;
// Formateamos el número para que tenga 6 dígitos (ej: 000281)
$numero_proyectado = str_pad($proximo_id, 6, "0", STR_PAD_LEFT);
?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Inventario | Entrada </title>
  <?php
  include('includes/headerNav2.php');
  ?>

  <style>
    /* ---------------------------------------------------------------------- */
    /* ANIMACIONES Y ESTILOS DE MODALES (Igualados a Ajustes)                 */
    /* ---------------------------------------------------------------------- */

    #avisoModal {
      z-index: 1000000 !important;
      /* Fuerza a estar por encima del 99999 de los demás modales */
    }

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

    /* --- ESTILOS NUEVOS PARA ERRORES Y ETIQUETAS (TAGS) --- */

    /* 1. Botón rojo sombreado para cuando falta el medicamento */
    .btn-error-sombreado {
      background-color: #f8d7da !important;
      color: #721c24 !important;
      border: 1px solid #f5c6cb !important;
      box-shadow: 0 0 10px rgba(220, 53, 69, 0.6) !important;
      transition: all 0.3s ease;
    }

    /* 2. Diseño del contenedor de Etiquetas (Tags) */
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

    .modal.in .modal-dialog,
    #avisoModal,
    #modalEntradaGuardar,
    #modalBúsquedaAvanzadaMedicamento,
    #modalRegresarInventario,
    #modalAgregarMedicamento,
    #med_modal_principal,
    #modalCargarPedido {
      animation: fadeIn 0.4s ease-out;
    }

    .modal.out .modal-dialog {
      animation: fadeOut 0.4s ease-in;
    }

    .modal-open .modal-backdrop {
      opacity: 0.7 !important;
    }

    .modal-backdrop+.modal-backdrop {
      opacity: 0 !important;
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

    /* Añadir dentro de tu etiqueta <style> */
    .table-responsive {
      max-height: 220px;
      /* Altura aproximada para 3 filas */
      overflow-y: auto;
    }

    /* Fijar el encabezado para que no se pierda al bajar el scroll */
    .table-detalle thead th {
      position: sticky;
      top: 0;
      background-color: #f4f4f4;
      z-index: 1;
    }

    /* ---------------------------------------------------------------------- */
    /* ESTILOS DE VALIDACIÓN Y LAYOUT */
    /* ---------------------------------------------------------------------- */
    .input-error {
      border: 2px solid crimson !important;
      box-shadow: 0 0 5px crimson;
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
      /* Asegura contener elementos flotantes internamente */
    }

    /* Regla añadida para mantener el color gris en inputs de fecha bloqueados */
    input[type="date"][readonly],
    input[type="date"][disabled] {
      background-color: #eeeeee !important;
      cursor: not-allowed;
    }
  </style>
</head>

<body>
  <div class="content-wrapper">
    <section class="content-header">
      <h1>Entrada de Medicamentos <small>Recepción y Lotes</small></h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-users"></i>Inventario</a></li>
        <li class="active"><a href="#"><i class="fa fa-arrow-down"></i>Entrada</a></li>
      </ol>
    </section>

    <div id="info_pedido_cargado" class="alert alert-info" style="display: none; margin-bottom: 15px;">
      <h4 style="margin-bottom: 5px;"><i class="icon fa fa-info-circle"></i> Pedido Vinculado: #<span id="txt_id_pedido_cargado"></span></h4>
      <p style="margin: 0;">Se ha cargado un pedido. Por favor, use el botón amarillo de Editar (<i class="fa fa-pencil"></i>) en cada fila inferior para asignar el Lote y las Fechas antes de guardar. <b>No se puede cambiar el medicamento ni la cantidad solicitada.</b></p>
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

              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title"><i class="fa fa-truck"></i> Datos de la recepción:</h3>
                </div>
                <div class="box-body">
                  <div class="row">
                    <div class="col-sm-3">
                      <label>Receptor (*):</label>
                      <?php
                      // Obtenemos el ID del usuario logueado desde la sesión
                      $id_persona_logueada = $_SESSION['id'] ?? 0;

                      // Si no hay un receptor seleccionado previamente (por ejemplo, si es un registro nuevo),
                      // asignamos por defecto el ID del usuario logueado.
                      if (empty($id_receptor_seleccionado)) {
                        $id_receptor_seleccionado = $id_persona_logueada;
                      }
                      ?>

                      <select name="receptor" id="receptor" class="form-control" required>
                        <option value="">--- Seleccione el receptor ---</option>
                        <?php
                        // NOTA: Agregué 'p.apellido' a la consulta. En tu código original intentabas imprimirlo, 
                        // pero no lo estabas pidiendo en el SELECT, lo que causaría un error de "Undefined array key".
                        $sql_receptor = "SELECT p.id, p.nombre, p.apellido, r.Id_rol, p.estatus 
                        FROM persona p 
                        INNER JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona
                        INNER JOIN rol r ON dpr.Id_rol = r.Id_rol
                        WHERE p.estatus IN (1, 2) AND r.Id_rol IN (1, 6)
                        ORDER BY p.nombre ASC";

                        $resultado_receptor = $conexion->query($sql_receptor);

                        while ($row_receptor = $resultado_receptor->fetch_assoc()) {
                          $id_db = (int)$row_receptor['id'];
                          $id_objetivo = (int)$id_receptor_seleccionado;

                          // Si el ID de la base de datos coincide con el objetivo (usuario logueado), añade el 'selected'
                          $selected = ($id_db === $id_objetivo) ? 'selected="selected"' : '';

                          echo '<option value="' . $id_db . '" ' . $selected . '>' .
                            htmlspecialchars($row_receptor['nombre'] . ' ' . $row_receptor['apellido']) .
                            '</option>';
                        }
                        ?>
                      </select>
                    </div>

                    <div class="col-sm-3 form-group">
                      <label>Proveedor / Donante (*):</label>
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
                    </div>

                    <label class="control-label"></label>
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
                    <button type="button" class="btn btn-primary btn-sm" id="btnAbrirModalAgregar" style="width:200px;">
                      <i class="fa fa-plus"></i> Añadir Medicamento
                    </button>
                    <p></p>
                    <button type="button" class="btn btn-success btn-sm" id="btnAbrirModalCrear" style="width:200px;">
                      <i class="fa fa-plus"></i> Crear Nuevo Medicamento
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
                  <button type="button" class="btn btn-secondary" id="abrirModalRegresar">Regresar</button>
                  <button type="submit" class="btn btn-success" id="btnPrepararGuardado"><i class="fa fa-save"></i>Procesar</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>
    </section>
  </div>

  <div class="modal" id="modalAgregarMedicamento" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 1;">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title" style="color: white;"><i class="fa fa-plus-circle"></i> Ingresar Lote de Medicamento</h4>
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
                  $sql_medicamentos = "SELECT dm.Id AS id_desc, m.nombre_medicamento, p.nombre_presentacion,
                    GROUP_CONCAT(CONCAT(IFNULL(pa.nombre,''), ' ', IFNULL(dpm.cantidad_unidad_medida,''), ' ', IFNULL(um.unidad,'')) SEPARATOR ' + ') AS componentes
                    FROM descripcion_medicamento dm
                    INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
                    INNER JOIN presentacion p ON dm.Id_presentacion = p.Id_presentacion
                    LEFT JOIN detalle_principio_medicamento dpm ON dm.Id = dpm.id_medicamento
                    LEFT JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida
                    LEFT JOIN principio_activo pa ON dpm.id_principio_activo = pa.Id_principio_activo
                    WHERE m.estatus = 1 AND (dm.estatus = '1' OR dm.estatus = 1) 
                    GROUP BY dm.Id ORDER BY m.nombre_medicamento ASC";

                  $resultado_medicamentos = $conexion->query($sql_medicamentos);
                  if ($resultado_medicamentos && $resultado_medicamentos->num_rows > 0) {
                    while ($row_med = $resultado_medicamentos->fetch_assoc()) {
                      $comp = trim($row_med['componentes']) ? " (" . htmlspecialchars($row_med['componentes']) . ")" : "";
                      // EXTRAÍMOS EL COMPONENTE PARA PASARLO POR DATA-COMPONENTES
                      $componentesLimpios = trim($row_med['componentes']) ? htmlspecialchars($row_med['componentes']) : 'Sin principios activos registrados';
                      echo '<option value="' . $row_med['id_desc'] . '" data-nombre="' . htmlspecialchars($row_med['nombre_medicamento'] . " [" . $row_med['nombre_presentacion'] . "]") . '" data-componentes="' . $componentesLimpios . '">' . htmlspecialchars($row_med['nombre_medicamento']) . $comp . " - [" . htmlspecialchars($row_med['nombre_presentacion']) . "]" . '</option>';
                    }
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
              <label>Unidades a Ingresar (*):</label>
              <input type="text" id="cantidad" class="form-control" placeholder="Solo números, sin 0 inicial" oninput="this.value = this.value.replace(/[^0-9]/g, ''); if(this.value === '0') this.value = '1';">
            </div>

            <!--<div class="col-sm-4 form-group mt-3">
              <label>Cantidad por unidad (*):</label>
              <input type="text" id="cantidad_unidad" class="form-control" placeholder="Ej 20 ML, 10 G, 300 L">
            </div>-->
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-success" id="btnConfirmarAgregarMedicamento"><i class="fa fa-check"></i> Añadir a la Lista</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalConfirmarLote" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h5 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Confirmar Lote Existente</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 1;">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p id="modalConfirmarLoteTexto" style="font-size: 16px;"></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal" id="btnCancelarLote">Cancelar</button>
          <button type="button" class="btn btn-danger" data-dismiss="modal" id="btnAceptarLote">Aceptar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalBúsquedaAvanzadaMedicamento" role="dialog" aria-labelledby="modalBúsquedaAvanzadaMedicamentoLabel" aria-hidden="true">
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
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-warning" id="btnLimpiarFiltros">Limpiar Filtros</button>
          <button type="button" class="btn btn-primary" id="btnAplicarFiltros">Aplicar Filtros</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalCargarPedido" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <button type="button" class="close text-white" data-dismiss="modal" style="color:white; opacity: 1;">&times;</button>
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
              $sql_pedidos = "SELECT p.id_pedido, p.fecha_creacion, pr.nombre_proveedor, p.id_proveedor 
                              FROM pedidos p 
                              INNER JOIN proveedor pr ON p.id_proveedor = pr.Id_proveedor 
                              WHERE p.estado = 'Pendiente' AND p.estatus = 1";
              $res_pedidos = $conexion->query($sql_pedidos);
              if ($res_pedidos && $res_pedidos->num_rows > 0) {
                while ($row_p = $res_pedidos->fetch_assoc()) {
                  echo "<tr>
                            <td class='text-center'><strong>#" . $row_p['id_pedido'] . "</strong></td>
                            <td class='text-center'>" . date('d/m/Y H:i', strtotime($row_p['fecha_creacion'])) . "</td>
                            <td class='text-center'>" . $row_p['nombre_proveedor'] . "</td>
                            <td class='text-center'>
                              <button type='button' class='btn btn-info btn-xs' onclick='verDetallePedidoPrevio(" . $row_p['id_pedido'] . ")' title='Ver medicamentos de este pedido'>
                                <i class='fa fa-info-circle'></i> Ver Info
                              </button>
                              
                              <button type='button' class='btn btn-success btn-xs btn-seleccionar-pedido' 
                                      data-id='" . $row_p['id_pedido'] . "' 
                                      data-idprov='" . $row_p['id_proveedor'] . "' style='margin-left: 5px;'>
                                <i class='fa fa-check'></i> Seleccionar
                              </button>
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

  <div class="modal" id="modalVerDetallePedidoPrevio" tabindex="-1" role="dialog" style="z-index: 1060;">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: #3498db; color: white;">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 1;">
            <span aria-hidden="true">&times;</span>
          </button>
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
              <tbody id="cuerpo_detalle_pedido_previo">
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Vista Previa</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="avisoModal" tabindex="-1" role="dialog" aria-labelledby="avisoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div id="headerAviso" class="modal-header bg-crimson">
          <h5 class="modal-title" id="avisoModalLabel" style="color: white;">Aviso de Validación</h5>
        </div>
        <div class="modal-body">
          <p id="avisoTexto"></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
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

  <div class="modal" id="modalEntradaGuardar" tabindex="-1" role="dialog" aria-labelledby="modalEntradaGuardarLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-green">
          <h5 class="modal-title" id="modalEntradaGuardarLabel" style="color: white;">Confirmacion de Guardado</h5>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea guardar la información para esta entrada del inventario?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-success" id="confirmarGuardadoFinal">Guardar</button>
        </div>
      </div>
    </div>
  </div>


  <?php
  include('modales/inventario/medicamentos/medicamentos_agregar_modal.php');

  if (isset($conexion)) {
    $conexion->close();
  }

  include('includes/footer.php');
  ?>

  <script>
    $(document).ready(function() {

      $('#infoLote').tooltip({
        html: true,
        placement: 'right',
        title: 'Llene los datos del lote'
      });
      // ARRAY PRINCIPAL QUE GUARDA LOS MEDICAMENTOS
      let listaDetalles = [];
      let lotesCargados = [];

      // NUEVAS VARIABLES PARA EDICIÓN Y REUTILIZACIÓN DE LOTE
      let editandoIndex = -1;
      let ultimoLoteIngresado = null;

      const hoy = new Date().toISOString().split('T')[0];
      $('#fecha_fabricacion').attr('max', hoy);

      // ---------------------------------------------------------------------
      // LÓGICA DE CIERRE DE MODALES ANIMADOS
      // ---------------------------------------------------------------------
      $('.modal').on('click', '[data-dismiss="modal"]', function(e) {
        e.preventDefault(); // <-- IMPORTANTE: Evita que Bootstrap lo cierre de golpe
        var $modal = $(this).closest('.modal');
        $modal.removeClass('in').addClass('out');

        setTimeout(function() {
          $modal.modal('hide');
        }, 400);
      });

      // Limpieza profunda al terminar de ocultarse
      $('.modal').on('hidden.bs.modal', function() {
        $(this).removeClass('out'); // <-- Previene que la clase se quede pegada

        if ($('.modal:visible').length) {
          $('body').addClass('modal-open');
        } else {
          $('body').removeClass('modal-open');
          $('.modal-backdrop').remove();
        }
      });

      // Limpieza preventiva antes de abrir para evitar dobles animaciones
      $('.modal').on('show.bs.modal', function() {
        $(this).removeClass('out');
      });
      // -------------------------------------------------------------
      // FUNCIONES BASE
      // -------------------------------------------------------------
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
        $('#med_formularioMedicamento')[0].reset();

        $('#med_modal_principal').modal('show');
      });

      function limpiarFormularioModal() {
        $('#Id_descripcion_medicamento').val('').trigger('change');
        $('#lote, #fecha_fabricacion, #fecha_vencimiento, #cantidad').val('').removeClass('input-error');
        $('#fecha_fabricacion, #fecha_vencimiento').prop('readonly', false);
        $('#fecha_vencimiento').prop('disabled', true);
        $('#lote').css('border-color', '#ced4da');
        $('#existencia_actual, #stock_minimo, #stock_maximo').val('');
      }

      $('#abrirModalRegresar').on('click', function() {
        $('#modalRegresarInventario').modal('show');
      });

      // -------------------------------------------------------------
      // FILTRADO AVANZADO AJAX (Igual a Ajustes)
      // -------------------------------------------------------------
      const medicamentoSelectPrincipal = $('#Id_descripcion_medicamento');
      window.opcionesOriginalesMedicamentos = medicamentoSelectPrincipal.html(); // Guardamos las opciones iniciales cargadas por PHP

      $('#btnLimpiarFiltros').on('click', function() {
        $('#formFiltroModal')[0].reset();
        medicamentoSelectPrincipal.html(window.opcionesOriginalesMedicamentos); // Restauramos la lista completa
      });

      $('#btnAplicarFiltros').on('click', function(e) {
        e.preventDefault();

        let datosFiltro = $('#formFiltroModal').serialize();
        const formValores = $('#formFiltroModal').serializeArray();
        const busquedaRapida = $('#filtro_busqueda_rapida').val().trim();

        let hayDatos = false;

        // Revisar si algún campo del modal avanzado tiene texto
        $.each(formValores, function(i, field) {
          if (field.value.trim() !== "") {
            hayDatos = true;
          }
        });

        if (busquedaRapida !== "") {
          hayDatos = true;
          // Agregamos la variable al string que se va a enviar al servidor
          datosFiltro += (datosFiltro.length > 0 ? '&' : '') + 'filtro_busqueda_rapida=' + encodeURIComponent(busquedaRapida);
        }

        // --- CÓDIGO NUEVO A AGREGAR ---
        let modoOperacion = $('#op').val(); // Toma el valor del input hidden de operación
        datosFiltro += (datosFiltro.length > 0 ? '&' : '') + 'modo=' + encodeURIComponent(modoOperacion);
        // ------------------------------

        // Si todo está completamente vacío, lanzamos alerta y detenemos la ejecución
        if (!hayDatos) {
          mostrarAviso("Todos los filtros están vacíos. Escriba o seleccione al menos una opción para buscar.");
          return;
        }

        $.ajax({
          url: '../../cfg/ajax/filtrar_medicamentos_completo.php',
          type: 'POST',
          data: datosFiltro, // <-- Ahora sí incluye la búsqueda rápida
          dataType: 'json',
          success: function(response) {
            medicamentoSelectPrincipal.empty();
            medicamentoSelectPrincipal.append('<option value="">--- Seleccione un Medicamento ---</option>');

            if (response.length > 0) {
              response.forEach(function(item) {
                medicamentoSelectPrincipal.append('<option value="' + item.id_desc + '" data-nombre="' + item.nombre_completo + '">' + item.nombre_completo + '</option>');
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

      // -------------------------------------------------------------
      // LÓGICA DEL MODAL AGREGAR MEDICAMENTO
      // -------------------------------------------------------------
      $('#btnAbrirModalAgregar').on('click', function() {
        editandoIndex = -1; // Nos aseguramos que es un nuevo registro
        limpiarFormularioModal();
        $('#btnConfirmarAgregarMedicamento').html('<i class="fa fa-check"></i> Añadir a la Lista');

        // Muestra u oculta el botón de copiar lote dependiendo de si hay uno previo
        if (ultimoLoteIngresado) {
          $('#btnCopiarUltimoLote').show();
        } else {
          $('#btnCopiarUltimoLote').hide();
        }

        $('#modalAgregarMedicamento').modal('show');
      });

      // LÓGICA PARA BOTÓN REUTILIZAR LOTE
      $('#btnCopiarUltimoLote').on('click', function() {
        if (ultimoLoteIngresado) {
          $('#lote').val(ultimoLoteIngresado.lote);
          $('#fecha_fabricacion').val(ultimoLoteIngresado.fecha_fabricacion);
          $('#fecha_vencimiento').val(ultimoLoteIngresado.fecha_vencimiento).prop('disabled', false);
          $('#lote').trigger('input'); // Para que ejecute cualquier validación
        }
      });

      // --- NUEVA VALIDACIÓN: Bloquear campos si no hay medicamento seleccionado ---
      $('#lote, #cantidad, #fecha_fabricacion, #fecha_vencimiento').on('mousedown keydown', function(e) {
        // Permitimos usar la tecla "Tab" para no trabar la navegación por teclado
        if (e.type === 'keydown' && e.key === 'Tab') return;

        let medSeleccionado = $('#Id_descripcion_medicamento').val();

        // Si el valor está vacío, bloqueamos la escritura y el clic
        if (!medSeleccionado || medSeleccionado === "") {
          e.preventDefault();
          $(this).blur(); // Quitamos el foco del campo

          // Lanzamos el aviso solo si el modal no está ya abierto (evita bucles)
          if ($('#avisoModal').is(':hidden')) {
            mostrarAviso('⚠️ <b>Atención:</b> Debe seleccionar un <b>Medicamento del Catálogo</b> antes de llenar estos campos.');
          }
          return false;
        }
      });
      // ----------------------------------------------------------------------------

      $('#Id_descripcion_medicamento').on('change', function() {
        const medicamentoId = $(this).val();
        $('#lista_lotes').empty();
        lotesCargados = [];

        // --- SOLUCIÓN 1: Limpiar campos al cambiar de medicamento ---
        $('#lote').val('').css('border-color', '#ced4da').data('lote-confirmado', '');
        $('#fecha_fabricacion').val('').prop('readonly', false);
        $('#fecha_vencimiento').val('').prop('disabled', true).prop('readonly', false);
        $('#cantidad').val('');

        // CORRECCIÓN: Se retira .tooltip('fixTitle') para evitar el bloqueo del modal
        $('#infoLote').attr('data-original-title', 'Llene los datos del lote').attr('title', 'Llene los datos del lote');
        // -----------------------------------------------------------

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
                $('#existencia_actual').val(data.existencia_actual !== null && data.existencia_actual !== undefined ? data.existencia_actual : 0);
                $('#stock_minimo').val(data.stock_minimo !== null && data.stock_minimo !== undefined ? data.stock_minimo : 0);
                $('#stock_maximo').val(data.stock_maximo !== null && data.stock_maximo !== undefined ? data.stock_maximo : 0);

                if (data.lotes && data.lotes.length > 0) {
                  lotesCargados = data.lotes;
                  data.lotes.forEach(function(item) {
                    $('#lista_lotes').append('<option value="' + item.lote + '">');
                  });
                }

                let loteActual = $('#lote').val().trim().toUpperCase();
                if (loteActual !== '') {
                  // Buscamos si el lote que estamos editando ya está en la base de datos
                  const loteEncontrado = lotesCargados.find(l => l.lote.toUpperCase() === loteActual);
                  if (loteEncontrado) {
                    // Si existe, usamos tu función que bloquea los campos (readonly)
                    aplicarLoteExistente(loteEncontrado);
                    $('#lote').data('lote-confirmado', loteActual);
                  }
                }

                let extActual = parseInt(data.existencia_actual) || 0;
                let sMaximo = parseInt(data.stock_maximo) || 0;

                if (sMaximo > 0 && extActual >= sMaximo) {
                  $('#cantidad').prop('disabled', true).val('').attr('placeholder', 'Bloqueado (Stock Lleno)');
                  mostrarAviso(`🚫 <b>Atención:</b> La existencia actual (${extActual}) ya es igual o superior al Stock Máximo permitido (${sMaximo}).<br><br>No se permite el ingreso de más unidades de este medicamento.`);
                } else {
                  $('#cantidad').prop('disabled', false).attr('placeholder', 'Solo números, sin 0 inicial');
                }
              }
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

          // Bandera para saber si realmente le dio al botón Aceptar
          let loteAceptado = false;

          $('#modalConfirmarLoteTexto').html(`¿Está seguro de sumar la cantidad al mismo lote <b>(${loteEscrito})</b> que ya existe en el sistema?`);
          $('#modalConfirmarLote').modal('show');

          // Lógica al dar click en Aceptar explícitamente
          // Lógica al dar click en Aceptar explícitamente
          $('#btnAceptarLote').off('click').on('click', function() {
            loteAceptado = true;
            // No usamos .modal('hide') aquí porque el botón ya tiene data-dismiss="modal"
            // lo cual activa tu propia animación de cierre automáticamente.

            $('#lote').data('lote-confirmado', loteEscrito);
            aplicarLoteExistente(loteEncontrado);
          });

          // Lógica al dar click en Cancelar
          $('#btnCancelarLote').off('click').on('click', function() {
            // Dejamos que el data-dismiss="modal" haga el trabajo de cerrar animado
          });

          // FUNDAMENTAL: Evento corregido (antes decía 'hidden.bs.modals')
          $('#modalConfirmarLote').off('hidden.bs.modal').on('hidden.bs.modal', function() {

            // Eliminamos SOLO el backdrop extra (el último) para no afectar el modal de abajo
            if ($('.modal-backdrop').length > 1) {
              $('.modal-backdrop').last().remove();
            }

            // Mantenemos el body con scroll para el modal que quedó abierto
            if ($('#modalAgregarMedicamento').is(':visible')) {
              $('body').addClass('modal-open');
            }

            // Lógica original de limpieza
            if (!loteAceptado) {
              $('#lote').val('').data('lote-confirmado', ''); // Vaciamos el input
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

      // --- NUEVO: ABRIR MODAL Y CARGAR PEDIDO ---
      $('#btnAbrirModalPedidos').on('click', function() {
        $('#modalCargarPedido').modal('show');
      });

      $('.btn-seleccionar-pedido').on('click', function() {
        let idPedido = $(this).data('id');
        let idProveedor = $(this).data('idprov');

        cargarPedidoLogica(idPedido, idProveedor); // Usamos la nueva función centralizada

        $('#modalCargarPedido').removeClass('in').addClass('out');
        setTimeout(function() {
          $('#modalCargarPedido').modal('hide');
          $('#modalCargarPedido').removeClass('out');
        }, 400);
      });

      // --- NUEVO: FUNCIÓN PARA CARGAR PEDIDO ---
      function cargarPedidoLogica(idPedido, idProveedor) {
        // Asignamos el proveedor y bloqueamos el campo
        $('#proveedor').val(idProveedor).trigger('change');
        $('#proveedor').css('pointer-events', 'none').css('background-color', '#eee');

        $('#id_pedido_oculto').val(idPedido);

        // Mostrar indicador visual
        $('#txt_id_pedido_cargado').text(idPedido.toString().padStart(6, '0'));
        $('#info_pedido_cargado').slideDown();

        // Ocultamos los botones de agregar extra para evitar descuadres del pedido
        $('#btnAbrirModalAgregar, #btnAbrirModalCrear').hide();

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
              mostrarAviso("✅ <b>Pedido cargado.</b><br><br>Se han traído las cantidades solicitadas. <b>Atención:</b> Use el botón amarillo de Editar en cada fila para asignar el número de Lote y las Fechas correspondientes antes de guardar.");
            }
          }
        });
      }

      // --- DISPARADOR AUTOMÁTICO DESDE LA URL ---
      <?php if (isset($_GET['id_pedido_auto']) && isset($_GET['id_proveedor_auto'])) : ?>
        setTimeout(function() {
          cargarPedidoLogica("<?php echo (int)$_GET['id_pedido_auto']; ?>", "<?php echo (int)$_GET['id_proveedor_auto']; ?>");
        }, 500); // Pequeño delay para asegurar que el DOM esté listo
      <?php endif; ?>

      // --- NUEVO: FUNCIÓN PARA VER CONTENIDO ANTES DE SELECCIONAR ---
      // --- NUEVO: FUNCIÓN PARA VER CONTENIDO ANTES DE SELECCIONAR ---
      window.verDetallePedidoPrevio = function(idPedido) {
        // Formatear el identificador numérico con ceros a la izquierda
        let idFormateado = idPedido.toString().padStart(6, '0');
        $('#num_pedido_previo_titulo').text('#' + idFormateado);

        // Insertar un indicador visual de carga rápida
        $('#cuerpo_detalle_pedido_previo').html('<tr><td colspan="2" class="text-center"><i class="fa fa-spinner fa-spin"></i> Consultando medicamentos...</td></tr>');

        // Desplegar el modal de detalles intermedio
        $('#modalVerDetallePedidoPrevio').modal('show');

        // Consumir el componente AJAX existente del sistema
        $.ajax({
          url: '../../cfg/ajax/obtener_detalle_pedido_modal.php',
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
                html += '  <td><strong>' + item.nombre_medicamento + '</strong><br><small class="text-muted">' + item.presentacion + '</small></td>';
                html += '  <td class="text-center"><span class="badge bg-primary" style="font-size: 14px;">' + item.cantidad_solicitada + '</span></td>';
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

      // -------------------------------------------------------------
      // AGREGAR O EDITAR EN LA LISTA DE DETALLES
      // -------------------------------------------------------------
      $('#btnConfirmarAgregarMedicamento').on('click', function() {
        const id_med = $('#Id_descripcion_medicamento').val();
        const nombre_med = $('#Id_descripcion_medicamento option:selected').data('nombre');
        const componentes = $('#Id_descripcion_medicamento option:selected').data('componentes'); // Capturamos Principios Activos
        const lote = $('#lote').val().trim().toUpperCase();
        const f_fab = $('#fecha_fabricacion').val();
        const f_venc = $('#fecha_vencimiento').val();
        const cant = parseInt($('#cantidad').val());
        const existencia = parseInt($('#existencia_actual').val()) || 0;
        const sMin = parseInt($('#stock_minimo').val()) || 0;
        const sMax = parseInt($('#stock_maximo').val()) || 0;
        const totalProyectado = existencia + cant;

        $('.modal-body input, .modal-body select').removeClass('input-error');

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
        // 1. Bloqueo total: Si la existencia ya supera o iguala el stock máximo
        if (sMax > 0 && existencia >= sMax) {
          $('#Id_descripcion_medicamento, #cantidad').addClass('input-error');
          mostrarAviso(`🚫 <b>Acción denegada:</b> No puedes ingresar este medicamento. La existencia actual (${existencia}) ya alcanzó o superó el Stock Máximo permitido (${sMax}).`);
          return;
        }
        // 2. Advertencia de límite: Si la cantidad a ingresar empuja el inventario por encima del límite
        if (sMax > 0 && totalProyectado > sMax) {
          $('#cantidad').addClass('input-error');
          let permitido = sMax - existencia; // Calculamos cuánto es lo máximo que puede ingresar
          mostrarAviso(`⚠️ <b>Límite excedido:</b> Intentas ingresar demasiadas unidades.<br><br>Actualmente hay ${existencia} unidades y el máximo es ${sMax}. <b>Solo puedes ingresar un máximo de ${permitido} unidades nuevas.</b>`);
          return;
        }
        if (sMin > 0 && totalProyectado < sMin) {
          $('#cantidad').addClass('input-error');
          mostrarAviso(`La cantidad ingresada no cubre el Stock Mínimo requerido (${sMin}). Actualmente hay ${existencia} unidades. Debes ingresar al menos ${sMin - existencia} unidades para estabilizar el inventario.`);
          return;
        }
        if (isNaN(cant) || cant <= 0) {
          $('#cantidad').addClass('input-error');
          mostrarAviso('La cantidad debe ser mayor a 0.');
          return;
        }
        if (f_venc <= hoy) {
          $('#fecha_vencimiento').addClass('input-error');
          mostrarAviso('El medicamento ya está vencido.');
          return;
        }
        if (f_venc < f_fab) {
          $('#fecha_vencimiento').addClass('input-error');
          mostrarAviso('La fecha de vencimiento es incorrecta.');
          return;
        }

        // Verificamos si existe PERO ignoramos el que estamos editando actualmente
        const existeIndex = listaDetalles.findIndex(item => item.id_medicamento === id_med && item.lote === lote);
        if (existeIndex !== -1 && existeIndex !== editandoIndex) {
          mostrarAviso('Este medicamento con el mismo Lote ya está en la lista. Si desea modificarlo, edítelo directamente usando el botón amarillo en la tabla.');
          return;
        }

        const nuevoItem = {
          id_medicamento: id_med,
          nombre_medicamento: nombre_med,
          componentes: componentes, // Guardamos los principios activos
          lote: lote,
          fecha_fabricacion: f_fab,
          fecha_vencimiento: f_venc,
          cantidad: cant
        };

        // Si estamos editando, reemplazamos el registro, sino hacemos un push nuevo
        if (editandoIndex !== -1) {
          listaDetalles[editandoIndex] = nuevoItem;
          editandoIndex = -1; // Reseteamos el index tras editar
        } else {
          listaDetalles.push(nuevoItem);
        }

        // Guardamos los datos de este lote como el "último ingresado" para usarlo después
        ultimoLoteIngresado = {
          lote: lote,
          fecha_fabricacion: f_fab,
          fecha_vencimiento: f_venc
        };

        actualizarTablaDetalles();

        // Cierre animado del modal agregar medicamento
        $('#modalAgregarMedicamento').removeClass('in').addClass('out');
        setTimeout(function() {
          $('#modalAgregarMedicamento').modal('hide');
          $('#modalAgregarMedicamento').removeClass('out');
        }, 400);
      });

      // -------------------------------------------------------------
      // DIBUJAR LA TABLA, ELIMINAR Y EDITAR ÍTEMS
      // -------------------------------------------------------------
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

            // Reemplaza la sección del append dentro de actualizarTablaDetalles con esto:
            let etiquetaLote = item.lote ? `<strong>${item.lote}</strong>` : `<span class="text-danger"><i class="fa fa-exclamation-triangle"></i> Falta Lote</span>`;
            let etiquetaFab = item.fecha_fabricacion ? item.fecha_fabricacion : `<span class="text-danger">Pendiente</span>`;
            let etiquetaVenc = item.fecha_vencimiento ? item.fecha_vencimiento : `<span class="text-danger">Pendiente</span>`;

            tbody.append(`
              <tr class="${rowClass}">
                <td style="text-align: left;">
                  ${item.nombre_medicamento}
                </td>
                <td>${etiquetaLote}</td>
                <td>${etiquetaFab}</td>
                <td>${etiquetaVenc} ${diffDias < 180 ? ' <i class="fa fa-warning text-warning" title="Vence pronto"></i>' : ''}</td>
                <td><span class="badge bg-green" style="font-size:14px;">${item.cantidad}</span></td>
                <td>
                  <button type="button" class="btn btn-warning btn-xs btn-editar-fila" data-index="${index}" title="Editar medicamento">
                    <i"><img src="../../recursos/imagenes/iconos/editar.png" style="width:10px; height:10px;"></i>
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

      // ACCIÓN DE EDITAR (Carga los datos al modal)
      $('#cuerpoTablaMedicamentos').on('click', '.btn-editar-fila', function() {
        const index = $(this).data('index');
        const item = listaDetalles[index];
        editandoIndex = index; // Declaramos en qué posición estamos trabajando

        limpiarFormularioModal(); // Limpiamos primero todo el modal

        // Rellenamos el modal con los datos del item
        $('#Id_descripcion_medicamento').val(item.id_medicamento).trigger('change');
        $('#lote').val(item.lote);
        $('#fecha_fabricacion').val(item.fecha_fabricacion);
        $('#fecha_vencimiento').val(item.fecha_vencimiento).prop('disabled', false);
        $('#cantidad').val(item.cantidad);

        $('#btnCopiarUltimoLote').hide(); // Ocultamos el botón de copiar lote para no confundir mientras se edita
        $('#btnConfirmarAgregarMedicamento').html('<i class="fa fa-save"></i> Guardar Cambios');

        $('#btnCopiarUltimoLote').hide();
        $('#btnConfirmarAgregarMedicamento').html('<i class="fa fa-save"></i> Guardar Cambios');

        // --- NUEVO: BLOQUEO DE EDICIÓN SI ES UN PEDIDO ---
        if ($('#id_pedido_oculto').val() !== "0" && $('#id_pedido_oculto').val() !== "") {
          // Modo Pedido: Bloquear Select y Cantidad
          $('#Id_descripcion_medicamento').css('pointer-events', 'none').css('background-color', '#eee');
          $('#btnBuscarFiltrar').prop('disabled', true);
          $('#cantidad').prop('readonly', true).css('background-color', '#eee');
          $('#modalAgregarMedicamento .modal-title').html('<i class="fa fa-pencil"></i> Completar Lote (Modo Pedido)');
        } else {
          // Modo Normal: Desbloquear
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

      // -------------------------------------------------------------
      // SISTEMA DE ETIQUETAS (TAGS) PARA BÚSQUEDA RÁPIDA
      // -------------------------------------------------------------
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
          if (e.which === 13) { // Tecla Enter
            e.preventDefault();
            let valor = $(this).val().trim();

            if (valor !== '' && !tagsArray.includes(valor)) {
              tagsArray.push(valor);
              $(this).val('');
              renderizarTags();
              // Apenas aparezca el tag, busca de una vez
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

              // Si al borrar quedó vacío, limpiamos filtros. Si no, re-aplicamos la búsqueda.
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

          // Si al eliminar en la "X" queda vacío, resetea a todo. Si no, re-aplica.
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

      // Inicializamos la funcionalidad en los dos inputs requeridos
      inicializarTags('#filtro_busqueda_rapida');
      inicializarTags('#filtro_principios');

      function actualizarSelectMedicamentosSilencio() {
        if ($('#modalAgregarMedicamento').is(':visible')) {
          return;
        }

        // 1. Verificamos si hay alguna búsqueda/filtro activo
        const hayFiltroBusqueda = $('#filtro_busqueda_rapida').val().trim() !== '';
        let hayFiltrosAvanzados = false;
        $.each($('#formFiltroModal').serializeArray(), function(i, field) {
          if (field.value.trim() !== "") hayFiltrosAvanzados = true;
        });

        // 2. Solo actualizamos si el usuario NO está usando los filtros
        if (!hayFiltroBusqueda && !hayFiltrosAvanzados) {
          // Guardamos el ID del medicamento que el usuario tenga seleccionado actualmente
          const valorSeleccionado = $('#Id_descripcion_medicamento').val();
          let modoOperacion = $('#op').val();

          $.ajax({
            url: '../../cfg/ajax/filtrar_medicamentos_completo.php',
            type: 'POST',
            data: {
              recarga_silenciosa: true,
              modo: modoOperacion
            },
            dataType: 'json',
            success: function(response) {
              const select = $('#Id_descripcion_medicamento');

              // VERIFICAR SI EL SELECCIONADO EXISTE EN LA RESPUESTA
              let existeEnRespuesta = false;
              if (response.length > 0) {
                existeEnRespuesta = response.some(item => item.id_desc == valorSeleccionado);
              }

              let opcionInyectada = "";
              if (valorSeleccionado && !existeEnRespuesta) {
                // Si es un medicamento recién creado y no viene en el AJAX aún, clonamos su HTML para no perderlo
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
                  // Agregamos las opciones (evitando duplicar si ya lo inyectamos)
                  if (item.id_desc != valorSeleccionado || existeEnRespuesta) {
                    const comp = item.componentes ? ` data-componentes="${item.componentes}"` : '';
                    nuevasOpciones += `<option value="${item.id_desc}" data-nombre="${item.nombre_completo}"${comp}>${item.nombre_completo}</option>`;
                  }
                });
              } else if (opcionInyectada === "") {
                nuevasOpciones += '<option value="" disabled>🛑 No se encontraron medicamentos.</option>';
              }

              // Actualizamos el DOM sin interrumpir al usuario
              select.html(nuevasOpciones);

              // Restauramos la selección que tenía
              if (valorSeleccionado) {
                select.val(valorSeleccionado);
              }

              // Actualizamos el respaldo original para cuando limpien los filtros
              window.opcionesOriginalesMedicamentos = nuevasOpciones;
            }
          });
        }
      }

      // Ejecutar la actualización silenciosa cada 2000 milisegundos (2 segundos)
      setInterval(actualizarSelectMedicamentosSilencio, 2000);

      // -------------------------------------------------------------
      // ACTUALIZAR NÚMERO DE OPERACIÓN EN TIEMPO REAL
      // -------------------------------------------------------------
      function actualizarNumeroOperacionSilencio() {
        $.ajax({
          url: '../../cfg/ajax/obtener_proximo_id_operacion.php',
          type: 'GET',
          dataType: 'json',
          success: function(response) {
            if (response && response.numero_proyectado) {
              // Actualiza visualmente el texto del badge
              $('#badge_numero_operacion').text('#' + response.numero_proyectado);
            }
          },
          error: function(jqXHR, textStatus, errorThrown) {
            // Omitimos mostrar alertas para no molestar al usuario si hay un microcorte de red
            console.log("No se pudo actualizar el nro de operación en 2do plano.");
          }
        });
      }

      // Ejecutar la revisión del ID cada 5000 milisegundos (5 segundos)
      setInterval(actualizarNumeroOperacionSilencio, 5000);

      // -------------------------------------------------------------
      // SUBMIT Y GUARDADO FINAL
      // -------------------------------------------------------------
      $('#formularioEntrada').on('submit', function(e) {
        e.preventDefault();

        // Limpiar errores previos
        $('#proveedor').removeClass('input-error');
        $('#receptor').removeClass('input-error');
        $('#fecha_recepcion').removeClass('input-error');
        $('#hora_recepcion').removeClass('input-error');
        $('#btnAbrirModalAgregar').removeClass('btn-error-sombreado');

        let faltanDatosLote = listaDetalles.some(item => item.lote === '' || item.fecha_fabricacion === '' || item.fecha_vencimiento === '');
        if (faltanDatosLote) {
          mostrarAviso("🚫 <b>Datos Incompletos:</b><br><br>Hay medicamentos en la lista sin Lote o sin Fechas asignadas. Use el botón amarillo de Editar en cada fila para completar la información.");
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
</body>

</html>