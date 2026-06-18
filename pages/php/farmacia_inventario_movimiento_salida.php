<?php
include("../../cfg/conexion.php");
$operacion_actual = isset($_GET['op']) ? $_GET['op'] : 'ajuste_salida';

$sql_ultimo_id = "SELECT MAX(Id_detalle_inventario) AS ultimo FROM detalle_inventario";
$resultado_id = $conexion->query($sql_ultimo_id);
$row_id = $resultado_id->fetch_assoc();
$proximo_id = ($row_id['ultimo'] ? $row_id['ultimo'] : 0) + 1;
// Formateamos el número para que tenga 6 dígitos (ej: 000281)
$numero_proyectado = str_pad($proximo_id, 6, "0", STR_PAD_LEFT);
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
    #modalSalidaGuardar,
    #modalBúsquedaAvanzadaMedicamento,
    #modalRegresarInventario,
    #modalAgregarMedicamento {
      animation: fadeIn 0.4s ease-out;
    }

    .modal.out .modal-dialog {
      animation: fadeOut 0.4s ease-out;
    }

    .modal-open .modal-backdrop {
      opacity: 0.7 !important;
      animation: pulse-opacity 0.3s forwards;
    }

    .modal-header .close {
      color: #fff;
      filter: alpha(opacity=80);
      opacity: .8;
      text-shadow: none;
    }

    .modal-header .close:hover {
      opacity: 1;
    }

    .input-error {
      border: 2px solid crimson !important;
      box-shadow: 0 0 5px crimson;
    }

    .bg-crimson {
      background-color: #dc3545 !important;
      color: white !important;
    }

    .bg-warning-custom {
      background-color: #f39c12 !important;
      color: white !important;
    }

    .content-wrapper {
      min-height: 100vh !important;
      overflow-y: auto;
    }

    .main-sidebar {
      position: fixed !important;
      height: 100%;
    }

    /* ESTILOS DE TABLA (Traídos de la Entrada) */
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
    }

    input[readonly],
    input[disabled] {
      background-color: #eeeeee !important;
      cursor: not-allowed;
    }

    .text-green-bold {
      color: #00a65a;
      font-weight: bold;
      font-size: 1.2em;
    }
  </style>
</head>

<body class="hold-transition skin-blue sidebar-mini">
  <div class="content-wrapper">
    <section class="content-header">
      <h1>Salidas especiales y ajustes <small>Módulo de control</small></h1>
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

              <div class="box-tools pull-left" style="margin-top: 2px;">
                <span style="font-size: 16px; font-weight: bold; color: #555;">
                  Operación N°: <span class="badge bg-blue" style="font-size: 15px; padding: 5px 10px; letter-spacing: 1px;">#<?php echo $numero_proyectado; ?></span>
                </span>
              </div>

              <br>

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
                        <option value="3" data-tipo="resta">Salida por Vencimiento</option>
                        <option value="4" data-tipo="resta">Salida por Dañado</option>
                        <option value="5" data-tipo="resta">Salida por Pérdida o Robo</option>
                        <option value="7" data-tipo="resta">Ajuste por Cuadre (Salida / Faltante)</option>
                        <option value="6" data-tipo="suma">Ajuste por Cuadre (Entrada / Sobrante)</option>
                      </select>
                    </div>

                    <input type="hidden" name="tipo_ajuste" id="tipo_ajuste" value="resta">

                    <div class="col-sm-3">
                      <label>Encargado (*):</label>
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

                    <label class="control-label"></label>
                    <div class="col-sm-3 form-group" id="med_group_fecha_recepcion">
                      <label>Fecha y hora de ajuste (*):</label>
                      <div class="input-group">
                        <input type="date" id="fecha_recepcion" name="fecha_recepcion" class="form-control" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" onkeydown="return false;" style="width: 100%;">
                        <div class="input-group-btn" style="width: 100%;">
                          <input type="time" id="hora_recepcion" name="hora_recepcion" class="form-control" value="<?php date_default_timezone_set('America/Caracas');
                                                                                                                    echo date('H:i'); ?>">
                        </div>
                      </div>
                    </div>

                    <div class="col-sm-3 form-group">
                      <label>Observaciones / Justificación (*):</label>
                      <input type="text" name="observaciones_generales" id="observaciones_generales" class="form-control" placeholder="Ej: Frascos rotos durante el traslado..." required>
                    </div>
                  </div>
                </div>
              </div>

              <div class="box box-danger">
                <div class="box-header with-border">
                  <h3 class="box-title"><i class="fa fa-medkit"></i> Medicamentos a dar de baja:</h3>
                  <div class="box-tools pull-right" style="margin-left: 5px;">
                    <button type="button" class="btn btn-primary btn-sm" id="btnAbrirModalAgregar" style="width:200px;">
                      <i class="fa fa-plus"></i> Añadir Medicamento
                    </button>
                    <p></p>
                    <button type="button" class="btn btn-danger btn-sm" id="btnAñadirVencidos" style="display:none; margin-right: 5px; width:200px;">
                      <i class="fa fa-exclamation-triangle"></i> Añadir Medicamentos Vencidos
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
                          <th>Cantidad</th>
                          <th>Observaciones</th>
                          <th>Acciones</th>
                        </tr>
                      </thead>
                      <tbody id="cuerpoTablaMedicamentos">
                        <tr id="filaVacia">
                          <td colspan="6" class="text-center text-muted">Aún no se han añadido medicamentos para dar de baja.</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <div class="box box-default">
                <div class="box-header with-border">
                  <h3 class="box-title"><i class="fa fa-camera"></i> Evidencia del ajuste (Acta, Foto del daño):</h3>
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
                  <button type="button" class="btn btn-secondary" id="abrirModalRegresar">Regresar</button>
                  <button type="submit" class="btn btn-danger" id="btnPrepararGuardado"><i class="fa fa-trash"></i>Procesar</button>
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
                    AND EXISTS (
                        SELECT 1 FROM lotes_medicamentos lm 
                        INNER JOIN existencias_stock ex ON lm.Id = ex.Id_lote 
                        WHERE lm.Id_descripcion_medicamento = dm.Id 
                        AND lm.estado_lote = 'Disponible' 
                        AND ex.cantidad_actual > 0 
                        AND lm.fecha_vencimiento > CURDATE()
                    )
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
              <label>Cantidad (*):</label>
              <input type="text" id="cantidad_baja" class="form-control" placeholder="Solo números">
              <small id="max_stock_help" class="form-text text-muted" style="color:#d9534f; font-weight:bold;"></small>
            </div>

            <div class="col-sm-12 form-group mt-3">
              <label>Observación:</label>
              <textarea id="observaciones" name="observaciones" class="form-control" rows="2" placeholder="Ej: Se ha vencido hace 3 dias..."></textarea>
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
        <div class="modal-body">
          <p id="avisoTexto"></p>
        </div>
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
        <div id="headerSalidaGuardar" class="modal-header bg-green">
          <h5 class="modal-title" style="color: white;">Confirmar Ajuste</h5>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de procesar este ajuste de inventario? Esta acción modificara unidades del sistema.</p>
        </div>
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
      setTimeout(function() {
        $modal.modal('hide').removeClass('out');
      }, 400);
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
            navigator.mediaDevices.getUserMedia({
                video: {
                  facingMode: "environment"
                }
              })
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
          setTimeout(() => {
            $('#placeholder-texto').text('Esperando archivo...');
          }, 1000);
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

      // Variable global para almacenar las imágenes
      let evidenciasArray = [];

      // 1. Al confirmar una foto desde el modal, la agregamos al array
      $('#btn-aceptar-evidencia').off('click').on('click', function() {
        const base64Str = $('#foto-previa-modal').attr('src');
        if (base64Str) {
          evidenciasArray.push(base64Str);
          actualizarMiniaturasUI();
        }
        $('#modalEvidencia').modal('hide');
      });

      // 2. Función para renderizar las imágenes y actualizar el input oculto
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

        // Guardamos el array como JSON en el input oculto para que PHP lo reciba
        $('#fotos_base64_array').val(JSON.stringify(evidenciasArray));
      }

      // 3. Quitar una imagen específica al dar clic en la "X"
      $(document).on('click', '.btn-quitar-evidencia', function() {
        const index = $(this).data('index');
        evidenciasArray.splice(index, 1);
        actualizarMiniaturasUI();
      });

      // 4. Ver imagen en grande
      $(document).on('click', '.miniatura-img', function() {
        const index = $(this).data('index');
        $('#img-vista-grande').attr('src', evidenciasArray[index]);
        $('#modalVisualizarFoto').modal('show');
      });

      $('#modalEvidencia').on('hidden.bs.modal', function() {
        if (streamModal) {
          streamModal.getTracks().forEach(t => t.stop());
          streamModal = null;
        }
      });

      // Mostrar/Ocultar el botón y limpiar tabla al cambiar de motivo
      $('#id_tipo_movimiento').on('change', function() {
        // Limpiar lista siempre que se cambie la opción
        if (listaDetalles.length > 0) {
          listaDetalles = [];
          actualizarTablaDetalles();
        }

        // 3 es el ID de "Salida por Vencimiento"
        if ($(this).val() === "3") {
          $('#btnAñadirVencidos').fadeIn();
        } else {
          $('#btnAñadirVencidos').fadeOut();
        }
      });

      // Acción del botón Añadir Vencidos
      $('#btnAñadirVencidos').on('click', function() {
        if (confirm("¿Está seguro de querer añadir TODOS los medicamentos vencidos (con stock mayor a 0) a la lista? Se sobrescribirá la lista actual.")) {

          $.ajax({
            url: '../../cfg/ajax/obtener_lotes_vencidos.php', // Archivo PHP que crearemos
            type: 'GET',
            dataType: 'json',
            success: function(response) {
              if (response.length > 0) {
                listaDetalles = response;
                actualizarTablaDetalles();
                mostrarAviso("Éxito: Se añadieron " + response.length + " lotes vencidos a la lista.");
              } else {
                mostrarAviso("No hay medicamentos vencidos registrados en el stock actualmente.");
              }
            },
            error: function() {
              mostrarAviso("Error al comunicarse con el servidor para obtener los vencidos.");
            }
          });
        }
      });

      // -------------------------------------------------------------
      // FILTRADO AVANZADO EXACTO DEL ARCHIVO AJUSTES
      // -------------------------------------------------------------
      const medicamentoSelectPrincipal = $('#Id_descripcion_medicamento');
      const opcionesOriginalesMedicamentos = medicamentoSelectPrincipal.html(); // Guardamos las opciones iniciales cargadas por PHP

      $('#btnLimpiarFiltros').on('click', function() {
        $('#formFiltroModal')[0].reset();
        medicamentoSelectPrincipal.html(opcionesOriginalesMedicamentos); // Restauramos la lista completa
      });

      $('#btnAplicarFiltros').on('click', function() {
        const datosFiltro = $('#formFiltroModal').serialize();
        const busquedaRapida = $('#filtro_busqueda_rapida').val().trim();
        const principios = $('#filtro_principios').val().trim();
        const nombre_med = $('#filtro_nombre').val().trim();

        // --- NUEVA VALIDACIÓN: Si ambos están vacíos, lanzamos alerta y nos detenemos ---
        if (busquedaRapida === "" && principios === "" && nombre_med === "") {
          mostrarAviso("Los campos de filtro están vacíos. Escriba alguna etiqueta para buscar.");
          return; // El return evita que se ejecute la petición AJAX
        }

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

        // Revisar si hay búsqueda rápida y ANEXARLA a los datos del AJAX
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

      // Actualizar el tipo de ajuste oculto cuando cambie el select
      $('#id_tipo_movimiento').on('change', function() {
        const tipo = $(this).find(':selected').data('tipo');
        $('#tipo_ajuste').val(tipo);

        // Cambiar visualmente el botón de confirmación
        if (tipo === 'suma') {
          $('#confirmarGuardadoFinal').removeClass('btn-danger').addClass('btn-success').text('Procesar Entrada');
          $('#btnPrepararGuardado').removeClass('btn-danger').addClass('btn-success').html('<i class="fa fa-plus"></i> Procesar Entrada');
        } else {
          $('#confirmarGuardadoFinal').removeClass('btn-success').addClass('btn-danger').text('Procesar Baja');
          $('#btnPrepararGuardado').removeClass('btn-success').addClass('btn-danger').html('<i class="fa fa-trash"></i> Procesar Baja');
          $('#headerSalidaGuardar').removeClass('bg-green').addClass('bg-crimson');
        }
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
        $('#observaciones').val('');
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

      // Inicializar Tooltip en el botón de Info Lote
      $('#infoLote').tooltip({
        html: true,
        placement: 'right',
        title: 'Seleccione un lote primero'
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
            data: {
              id: medId,
              modo: 'ajuste_salida',
              id_tipo_movimiento: $('#id_tipo_movimiento').val()
            }, // Usa el mismo AJAX de despacho que trae existencias
            dataType: 'json',
            success: function(data) {
              $lotes.empty().append('<option value="">-- Seleccione un lote --</option>');
              if (data.lotes && data.lotes.length > 0) {
                data.lotes.forEach(l => {
                  let estilo = l.dias_restantes <= 0 ? "color:red;" : "";

                  // Agregado para mostrar los días restantes
                  let textoDisp = "";
                  if (l.dias_restantes < 0) {
                    textoDisp = " (VENCIDO)";
                  } else if (l.dias_restantes === 0) {
                    textoDisp = " (VENCE HOY)";
                  } else {
                    textoDisp = ` (Faltan ${l.dias_restantes} días)`;
                  }

                  // NUEVO CÓDIGO:
                  let prov = l.nombre_proveedor ? l.nombre_proveedor : 'Sin Proveedor';
                  let fab = l.fecha_fabricacion ? l.fecha_fabricacion : 'N/A';

                  $lotes.append(`<option value="${l.Id}" data-lote="${l.lote}" data-venc="${l.fecha_vencimiento}" data-fab="${fab}" data-prov="${prov}" data-cant="${l.cantidad_actual}" data-dias="${l.dias_restantes}" style="${estilo}">${l.lote} - Stock: ${l.cantidad_actual} ${textoDisp}</option>`);
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
          let diasRestantesData = option.data('dias');
          let textoDias = diasRestantesData < 0 ? ' (VENCIDO)' : (diasRestantesData === 0 ? ' (VENCE HOY)' : ` (Faltan ${diasRestantesData} días)`);

          $('#fecha_vencimiento_readonly').val(option.data('venc'));

          const tipoActual = $('#id_tipo_movimiento').find(':selected').data('tipo') || 'resta';
          if (tipoActual === 'suma') {
            $('#max_stock_help').text(`Stock actual: ${option.data('cant')} unid. (Ingrese cantidad a sumar)`);
          } else {
            $('#max_stock_help').text(`Máximo disponible para baja: ${option.data('cant')} unid.`);
          }

          // --- NUEVA LÓGICA DEL TOOLTIP ---
          var infoHtml = "<div style='text-align:left; font-size: 12px;'>" +
            "<b>Proveedor:</b> " + option.data('prov') + "<br>" +
            "<b>F. Fabricación:</b> " + option.data('fab') + "<br>" +
            "<b>F. Vencimiento:</b> " + option.data('venc') + "<br>" +
            "<b>Stock Físico:</b> " + option.data('cant') +
            "</div>";

          $('#infoLote').attr('data-original-title', infoHtml).tooltip('fixTitle');
        } else {
          $('#fecha_vencimiento_readonly').val('');
          $('#max_stock_help').text('');
          $('#infoLote').attr('data-original-title', 'Seleccione un lote primero').tooltip('fixTitle');
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
        const observaciones = $('#observaciones').val();

        $('.modal-body .form-control').removeClass('input-error');

        if (!id_med) {
          $('#Id_descripcion_medicamento').addClass('input-error');
          mostrarAviso('Seleccione un medicamento.');
          return;
        }
        if (!lote_id) {
          $('#lista_lotes').addClass('input-error');
          mostrarAviso('Seleccione un lote.');
          return;
        }
        if (isNaN(cant_baja) || cant_baja <= 0) {
          $('#cantidad_baja').addClass('input-error');
          mostrarAviso('Ingrese una cantidad válida.');
          return;
        }
        // En tu validación dentro de $('#btnConfirmarAgregarMedicamento').on('click', ...)
        const tipoActual = $('#id_tipo_movimiento').find(':selected').data('tipo') || 'resta';

        if (isNaN(cant_baja) || cant_baja <= 0) {
          $('#cantidad_baja').addClass('input-error');
          mostrarAviso('Ingrese una cantidad válida.');
          return;
        }

        // SOLO validamos el tope máximo si es una RESTA (Salida/Baja/Pérdida)
        if (tipoActual === 'resta' && cant_baja > max_cant) {
          $('#cantidad_baja').addClass('input-error');
          mostrarAviso(`La cantidad supera el stock físico de este lote (${max_cant}).`);
          return;
        }

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

      // -------------------------------------------------------------
      // DIBUJAR LA TABLA Y ACCIONES (Eliminar/Editar)
      // -------------------------------------------------------------
      function actualizarTablaDetalles() {
        const tbody = $('#cuerpoTablaMedicamentos');
        tbody.empty();

        if (listaDetalles.length === 0) {
          tbody.append('<tr id="filaVacia"><td colspan="6" class="text-center text-muted">Aún no se han añadido medicamentos.</td></tr>');
        } else {
          // Detectar si es suma o resta
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

        // Cargar datos al modal
        $('#Id_descripcion_medicamento').val(item.id_medicamento).trigger('change');
        $('#observaciones').val(item.observacion);

        // Dar tiempo al AJAX de lotes para cargar y luego seleccionarlo
        setTimeout(() => {
          $('#lista_lotes').val(item.lote_id).trigger('change');
          setTimeout(() => {
            $('#cantidad_baja').val(item.cantidad);
          }, 100);
        }, 600);

        $('#btnConfirmarAgregarMedicamento').html('<i class="fa fa-save"></i> Guardar Cambios');
        $('#modalAgregarMedicamento').modal('show');
      });

      // -------------------------------------------------------------
      // SUBMIT DEL FORMULARIO PRINCIPAL
      // -------------------------------------------------------------
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
          mostrarAviso("Debe seleccionar el Receptor.");
          return;
        }

        if ($('#observaciones_generales').val().trim() === "") {
          $('#observaciones_generales').addClass('input-error');
          mostrarAviso("Debe ingresar una justificación / observación obligatoria.");
          return;
        }

        if (listaDetalles.length === 0) {
          $('#btnAbrirModalAgregar').removeClass('btn-primary').addClass('btn-error-sombreado');
          mostrarAviso("La lista está vacía. Debe añadir al menos un medicamento para procesar la baja.");
          return;
        }

        // Validación opcional: Evidencia obligatoria si es "Dañado" o "Robo"
        const motivo = parseInt($('#id_tipo_movimiento').val());
        const evidencia = $('#fotos_base64_array').val();
        if ((motivo === 4 || motivo === 5) && (!evidencia || evidencia.trim() === "")) {
          mostrarAviso('<strong>📸 Evidencia Sugerida/Obligatoria:</strong><br>Para registrar daños, pérdidas o robos, es altamente recomendable (o requerido por su política) adjuntar un comprobante o foto.');
          return; // Puedes quitar el "return" si quieres que sea solo un aviso y no obligatorio
        }

        $('#modalSalidaGuardar').modal('show');
      });

      $('#id_tipo_movimiento').on('change', function() {
        $(this).removeClass('input-error');
      });

      $('#observaciones_generales').on('input', function() {
        $(this).removeClass('input-error');
      });

      // Quitar el sombreado rojo del botón de añadir cuando se abre el modal
      $('#btnAbrirModalAgregar').on('click', function() {
        $(this).removeClass('btn-error-sombreado').addClass('btn-primary');
        editandoIndex = -1;
        limpiarFormularioModal();
        $('#btnConfirmarAgregarMedicamento').html('<i class="fa fa-check"></i> Añadir a la Lista');
        $('#modalAgregarMedicamento').modal('show');
      });

      $('#confirmarGuardadoFinal').on('click', function() {
        $('#formularioSalida').off('submit').submit();
      });

    });
  </script>
</body>

</html>