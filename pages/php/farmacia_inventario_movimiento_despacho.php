<?php
// Incluir la conexión a la base de datos (se asume esta ruta)
include("../../cfg/conexion.php");
$operacion_actual = isset($_GET['op']) ? $_GET['op'] : 'despacho';

// --- INICIO LÓGICA DE AUTORELLENADO DESDE LISTADO ---
$auto_id_pres = isset($_GET['id_pres']) ? $_GET['id_pres'] : '';
$auto_tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$auto_pac = isset($_GET['pac']) ? $_GET['pac'] : '';
$auto_menor = isset($_GET['menor']) ? $_GET['menor'] : '0';
$auto_id_med = isset($_GET['id_med']) ? $_GET['id_med'] : '';

$ext_paciente = '';
$ext_medico = '';
$ext_cedula = '';
$ext_tipo_ced = 'V';

if ($auto_tipo == 'Externa' && $auto_id_pres != '') {
  // CORRECCIÓN: Consultar directamente a la tabla solicitud_medicamento por su ID
  $sql_ext = "SELECT sm.datos_paciente_externo, sm.datos_medico_externo, sm.cedula_externo, sm.tipo_cedula_externo 
              FROM solicitud_medicamento sm 
              WHERE sm.id_solicitud = '$auto_id_pres'";
  $res_ext = mysqli_query($conexion, $sql_ext);
  if ($res_ext && $row_ext = mysqli_fetch_assoc($res_ext)) {
      $ext_paciente = $row_ext['datos_paciente_externo'];
      $ext_medico = $row_ext['datos_medico_externo'];
      $ext_cedula = $row_ext['cedula_externo'];
      $ext_tipo_ced = $row_ext['tipo_cedula_externo'] ? $row_ext['tipo_cedula_externo'] : 'V';
  }
}

// --- NUEVO: PRECARGAR MEDICAMENTOS CON SU LOTE (FIFO) ---
$medicamentos_precargados = [];
if ($auto_id_pres != '') {
    $query_meds = "";
    if ($auto_tipo == 'Interna') {
        // En recetas internas no se especifica cantidad en la DB, se asume 1 por defecto
        $query_meds = "SELECT 
                        dm.Id AS id_medicamento, 
                        m.nombre_medicamento,
                        1 AS cantidad_recetada,
                        '' AS dosis,
                        (SELECT GROUP_CONCAT(CONCAT(IFNULL(pa.nombre,''), ' ', IFNULL(dpm.cantidad_unidad_medida,''), ' ', IFNULL(um.unidad,'')) SEPARATOR ' + ') 
                         FROM detalle_principio_medicamento dpm 
                         LEFT JOIN principio_activo pa ON dpm.id_principio_activo = pa.Id_principio_activo 
                         LEFT JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida 
                         WHERE dpm.id_medicamento = dm.Id) AS componentes
                    FROM prescripcion_medicamentos pm
                    INNER JOIN descripcion_medicamento dm ON pm.Id_descripcion_medicamento = dm.Id
                    INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
                    WHERE pm.Id_consulta = '$auto_id_pres' AND pm.estado_prescripcion IN ('pendiente', 'parcial')";
    } else if ($auto_tipo == 'Externa') {
      $query_meds = "SELECT 
                      dm.Id AS id_medicamento, 
                      m.nombre_medicamento,
                      ds.cantidad_recetada,
                      IFNULL(ds.cantidad_entregada, 0) AS cantidad_entregada, /* <--- AÑADIMOS ESTO */
                      '' AS dosis,
                      (SELECT GROUP_CONCAT(CONCAT(IFNULL(pa.nombre,''), ' ', IFNULL(dpm.cantidad_unidad_medida,''), ' ', IFNULL(um.unidad,'')) SEPARATOR ' + ') 
                       FROM detalle_principio_medicamento dpm 
                       LEFT JOIN principio_activo pa ON dpm.id_principio_activo = pa.Id_principio_activo 
                       LEFT JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida 
                       WHERE dpm.id_medicamento = dm.Id) AS componentes
                  FROM detalle_solicitud ds
                  INNER JOIN descripcion_medicamento dm ON ds.id_medicamento = dm.Id
                  INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
                  WHERE ds.id_solicitud = '$auto_id_pres' AND ds.estatus_item IN ('Pendiente', 'Parcialmente Entregado')";
  }

    if ($query_meds != "") {
        $res_meds = mysqli_query($conexion, $query_meds);
        while ($row_med = mysqli_fetch_assoc($res_meds)) {
            $id_desc = $row_med['id_medicamento'];
            // Buscar el Lote disponible con mayor prioridad (fecha de vencimiento más próxima - FIFO)
            $sql_lote = "SELECT l.Lote as lote, ex.cantidad_actual 
                         FROM lotes_medicamentos l 
                         INNER JOIN existencias_stock ex ON l.Id = ex.Id_lote 
                         WHERE l.estado_lote = 'Disponible' AND l.Id_descripcion_medicamento = '$id_desc' 
                         AND ex.cantidad_actual > 0 
                         ORDER BY l.fecha_vencimiento ASC LIMIT 1";
            $res_lote = mysqli_query($conexion, $sql_lote);
            
            // Si hay un lote disponible, lo agregamos a la lista
            if ($lote_data = mysqli_fetch_assoc($res_lote)) {
              $row_med['lote'] = $lote_data['lote'];
              
              // 1. Calculamos la cantidad pendiente real
              $cant_req = (int)$row_med['cantidad_recetada'];
              $cant_ent = isset($row_med['cantidad_entregada']) ? (int)$row_med['cantidad_entregada'] : 0;
              $cant_pendiente = $cant_req - $cant_ent;
              
              $cant_disp = (int)$lote_data['cantidad_actual'];
              
              // 2. Aseguramos que la sugerencia no supere la existencia NI lo que falta por entregar
              $row_med['cantidad'] = ($cant_pendiente > $cant_disp) ? $cant_disp : $cant_pendiente; 
              // Actualizamos la variable para que el formulario sepa cuánto es lo pendiente realmente
              $row_med['cantidad_recetada'] = $cant_pendiente;
              $row_med['componentes'] = $row_med['componentes'] ? $row_med['componentes'] : 'Sin principios activos';
              
              // 3. SOLO agregamos el medicamento si todavía falta cantidad por entregar
              if ($cant_pendiente > 0) {
                  $medicamentos_precargados[] = $row_med;
              }
          }
        }
    }
}
?>


<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Inventario | Despacho de Medicamentos </title>
  <?php
  include('includes/headerNav2.php');
  ?>

  <style>
    /* ANIMACIONES Y ESTILOS DE MODALES */
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
    #modalDespachoGuardar,
    #modalBúsquedaAvanzadaMedicamento,
    #modalRegresarInventario,
    #modalAgregarMedicamento {
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

    .table-detalle th {
      background-color: #f4f4f4;
      text-align: center;
    }

    .table-detalle td {
      text-align: center;
      vertical-align: middle !important;
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
  </style>
</head>

<body>
  <div class="content-wrapper">
    <section class="content-header">
      <h1>Despacho de Medicamentos <small>Entrega a Pacientes</small></h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-users"></i>Inventario</a></li>
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

              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title"><i class="fa fa-user"></i> Datos de Entrega:</h3>
                </div>
                <div class="box-body">
                  <div class="row">
                    <div class="col-sm-3 form-group">
                      <label>Tipo de Atención (*):</label>
                      <select id="tipo_despacho" name="tipo_despacho" class="form-control" required>
                        <option value="interno">Paciente Interno</option>
                        <option value="representante">Entrega a Representante (Menor)</option>
                        <option value="externo">Récipe Externo (Manual)</option>
                      </select>
                    </div>

                    <div class="col-sm-2 form-group campo-interno">
                      <label>Buscar por:</label>
                      <select id="metodo_busqueda" class="form-control">
                        <option value="cedula">Cédula</option>
                        <option value="nombre">Nombre</option>
                      </select>
                    </div>

                    <div class="col-sm-3 form-group campo-interno">
                      <label>Buscar Paciente / Rep.:</label>

                      <div id="div_busqueda_cedula" class="input-group">
                        <span class="input-group-btn" style="width: 35%;">
                          <select id="tipo_cedula" class="form-control" style="padding: 6px 2px; height: 34px;">
                            <option value="V">V</option>
                            <option value="E">E</option>
                            <option value="PN">PN</option>
                          </select>
                        </span>
                        <input type="text" id="busqueda_cedula" class="form-control" placeholder="Número..." style="height: 34px;" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                      </div>

                      <div id="div_busqueda_nombre" style="display:none;">
                        <input type="text" id="busqueda_nombre" class="form-control" placeholder="Nombre o Apellido..." oninput="this.value = this.value.replace(/[0-9]/g, '')">
                      </div>
                    </div>

                    <div class="col-sm-4 form-group campo-interno">
                      <label>Recetas Encontradas:</label>
                      <select id="id_prescripcion" name="id_prescripcion" class="form-control">
                        <option value="">Seleccione receta...</option>
                      </select>
                    </div>

                    <div class="col-sm-3 form-group campo-externo" style="display:none;">
                      <label>Cédula / Documento:</label>
                      <div class="input-group">
                        <span class="input-group-btn" style="width: 25%;">
                          <select id="tipo_cedula" name="tipo_cedula_externo" class="form-control" style="padding: 6px 2px; border-top-right-radius: 0; border-bottom-right-radius: 0;">
                            <option value="V">V-</option>
                            <option value="E">E-</option>
                          </select>
                        </span>
                        <input type="text" id="busqueda_cedula" name="cedula_externo" maxlength="8" class="form-control" placeholder="Número..." oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                      </div>
                    </div>

                    <div class="col-sm-3 form-group campo-externo" style="display:none;">
                      <label>Paciente Externo (*):</label>
                      <input type="text" id="paciente_externo" name="paciente_externo" class="form-control" placeholder="Nombre completo" oninput="this.value = this.value.replace(/[0-9]/g, '')">
                    </div>

                    <div class="col-sm-3 form-group campo-externo" style="display:none;">
                      <label>Médico (*):</label>
                      <input type="text" id="medico_externo" name="medico_externo" class="form-control" placeholder="Nombre del médico" oninput="this.value = this.value.replace(/[0-9]/g, '')">
                    </div>

                    <div id="detalles_vinculo" class="col-sm-12 campo-interno" style="margin-top: 20px; display:none;">
                      <div class="row">
                        <div class="col-sm-6">
                          <div class="well well-sm" style="border-left: 5px solid #00a65a; background: #00a65a14; padding: 15px;">
                            <h5 style="margin-top:0; color:#00a65a; font-weight:bold;"><i class="fa fa-user"></i> DATOS DEL PACIENTE</h5>
                            <input type="text" id="info_paciente_nom" class="form-control" readonly style="border:none; background:transparent; font-weight:bold; box-shadow:none; font-size:1.1em; padding: 0;">
                            <input type="text" id="info_paciente_ced" class="form-control" readonly style="border:none; background:transparent; font-weight:bold; box-shadow:none; font-size:1.1em; padding: 0;">
                          </div>
                        </div>

                        <div id="col_info_rep" class="col-sm-6" style="display:none;">
                          <div class="well well-sm" style="border-left: 5px solid #3c8dbc; background: #f4f8ff; padding: 15px;">
                            <h5 style="margin-top:0; color:#3c8dbc; font-weight:bold;"><i class="fa fa-users"></i> REPRESENTANTE LEGAL</h5>
                            <input type="text" id="info_rep_nom" class="form-control" readonly style="border:none; background:transparent; font-weight:bold; box-shadow:none; font-size:1.1em; padding: 0;">
                            <input type="text" id="info_rep_ced" class="form-control" readonly style="border:none; background:transparent; font-weight:bold; box-shadow:none; font-size:1.1em; padding: 0;">
                          </div>
                        </div>
                      </div>
                    </div>

                  </div>
                </div>
              </div>

              <div class="box box-success">
                <div class="box-header with-border">
                  <h3 class="box-title"><i class="fa fa-medkit"></i> Medicamento a Retirar:</h3>
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
                          <th>Nombre del Medicamento</th>
                          <th>Lote</th>
                          <th id="th_recetada" style="width: 80px; display:none;">Cantidad Récipe</th>
                          <th style="width: 80px;">Cantidad</th>
                          <th>Observación / Aplicación</th>
                          <th style="width: 100px;">Acciones</th>
                        </tr>
                      </thead>
                      <tbody id="cuerpoTablaMedicamentos">
                        <tr id="filaVacia">
                          <td colspan="5" class="text-center text-muted">Aún no se han añadido medicamentos a este despacho.</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
                <div class="box-footer text-right" style="background-color: transparent; border-top: 1px solid #f4f4f4; padding-top: 15px;">
                  <button type="button" class="btn btn-secondary" id="abrirModalRegresar">Regresar</button>
                  <button type="submit" class="btn btn-success" id="btnPrepararGuardado"><i class="fa fa-save"></i>Despachar</button>
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
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 1;">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title" style="color: white;"><i class="fa fa-share-square-o"></i> Retirar Medicamento</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-sm-12 form-group">
              <label>Medicamento del Catálogo (*):</label>
              <div class="input-group">
                <select id="Id_descripcion_medicamento" name="Id_descripcion_medicamento" class="form-control select2" style="width: 100%;">
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
                      $componentesLimpios = trim($row_med['componentes']) ? htmlspecialchars($row_med['componentes']) : 'Sin principios activos';
                      echo '<option value="' . $row_med['id_desc'] . '" data-nombre="' . htmlspecialchars($row_med['nombre_medicamento'] . " [" . $row_med['nombre_presentacion'] . "]") . '" data-componentes="' . $componentesLimpios . '">' . htmlspecialchars($row_med['nombre_medicamento']) . $comp . " - [" . htmlspecialchars($row_med['nombre_presentacion']) . "]" . '</option>';
                    }
                  }
                  ?>
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
              <select id="lote_seleccionado" class="form-control">
                <option value="">Seleccione el medicamento primero</option>
              </select>
            </div>

            <div class="col-sm-4 form-group mt-3">
              <label>Existencia Actual:</label>
              <input type="text" id="existencia_actual" class="form-control" readonly disabled style="background-color: #f9f9f9;">
            </div>

            <div class="col-sm-4 form-group mt-3">
              <label>Cantidad a Retirar (*):</label>
              <input type="text" id="cantidad" class="form-control" placeholder="1" oninput="this.value = this.value.replace(/[^0-9]/g, ''); if(this.value === '0') this.value = '1';">
            </div>

            <div class="col-sm-4 form-group mt-3" id="grupo_recetada" style="display:none;">
              <label>Cantidad en Récipe:</label>
              <input type="text" id="cantidad_recetada" class="form-control" placeholder="Ej: 20" oninput="this.value = this.value.replace(/[^0-9]/g, ''); if(this.value === '0') this.value = '1';">
            </div>

            <div class="clearfix"></div>

            <div class="col-sm-12 form-group mt-3">
              <label>Observación / Aplicación (Dosis):</label>
              <textarea id="dosis_aplicacion" class="form-control" rows="2" placeholder="Ej: Tomar 1 tableta cada 8 horas..."></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-success" id="btnConfirmarAgregarMedicamento"><i class="fa fa-check"></i> Añadir a la Lista</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalBúsquedaAvanzadaMedicamento" tabindex="-1" role="dialog" aria-labelledby="modalBúsquedaAvanzadaMedicamentoLabel" aria-hidden="true" data-backdrop="static">
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

  <div class="modal" id="avisoModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h5 class="modal-title" style="color: white;">Aviso de Validación</h5>
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

  <div class="modal" id="modalDespachoGuardar" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-green">
          <h5 class="modal-title" style="color: white;">Confirmacion de Despacho</h5>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea procesar esta salida del inventario?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-success" id="confirmarGuardadoFinal">Procesar Salida</button>
        </div>
      </div>
    </div>
  </div>


  <?php if (isset($conexion)) {
    $conexion->close();
  }
  include('includes/footer.php'); ?>

  <script>
    $(document).ready(function() {

      // INICIALIZACIÓN DE SELECT2 SEGURA Y VARIABLES GLOBALES
      if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2();
      }

      var listaDetalles = [];
      var editandoIndex = -1;
      const medicamentoSelectPrincipal = $('#Id_descripcion_medicamento');

      // ---------------------------------------------------------------------
      // REPARACIÓN DEL MODAL AVANZADO SOBRE EL MODAL DE AGREGAR (REQ 7)
      // ---------------------------------------------------------------------
      $(document).on('show.bs.modal', '.modal', function(event) {
        var zIndex = 1040 + (10 * $('.modal:visible').length);
        $(this).css('z-index', zIndex);
        setTimeout(function() {
          $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
        }, 0);
      });

      // ---------------------------------------------------------------------
      // LÓGICA DE CIERRE DE MODALES ANIMADOS
      // ---------------------------------------------------------------------
      $('.modal').on('click', '[data-dismiss="modal"]', function(e) {
        e.preventDefault();
        var $modal = $(this).closest('.modal');
        $modal.removeClass('in').addClass('out');
        setTimeout(function() {
          $modal.modal('hide');
          $modal.removeClass('out');
        }, 400);
      });

      $('.modal').on('hidden.bs.modal', function() {
        if ($('.modal:visible').length) {
          $('body').addClass('modal-open');
        } else {
          $('body').removeClass('modal-open');
          $('.modal-backdrop').remove();
        }
      });

      function mostrarAviso(mensaje) {
        $('#avisoTexto').html(mensaje);
        $('#avisoModal').modal('show');
      }

      function limpiarFormularioModal() {
        // Al limpiar, nos aseguramos de que no esté bloqueado si antes se bloqueó temporalmente
        $('#Id_descripcion_medicamento').prop('disabled', false).val('').trigger('change');
        $('#btnBusquedaAvanzada').prop('disabled', false);

        $('#lote_seleccionado').html('<option value="">Seleccione el medicamento primero</option>');
        $('#cantidad, #cantidad_recetada, #dosis_aplicacion, #existencia_actual').val('').removeClass('input-error');
        $('#lote_seleccionado').removeClass('input-error');
      }

      $('#abrirModalRegresar').on('click', function() {
        $('#modalRegresarInventario').modal('show');
      });

      // -------------------------------------------------------------
      // GESTIÓN DE LOS MÉTODOS DE BÚSQUEDA Y OCULTAMIENTO INICIAL
      // -------------------------------------------------------------
      $('#tipo_despacho').on('change', function() {
        var modo = $(this).val();

        if (listaDetalles.length > 0) {
          // Opcional: Puedes agregar un confirm() si quieres que el usuario valide antes de borrar
          listaDetalles = []; // Vacía el arreglo global
          actualizarTablaDetalles(); // Refresca la tabla visualmente
        }

        $('.campo-interno, .campo-externo').hide();
        $('#detalles_vinculo').hide(); // Ocultar datos extra al cambiar

        if (modo === '') {
          // Si no hay motivo, todo queda oculto
        } else if (modo === 'interno' || modo === 'representante') {
          $('.campo-interno').show();
          $('#detalles_vinculo').hide(); // Ocultar hasta seleccionar receta
          buscarPrescripcionesAjax(); // Refrescar búsqueda enviando es_menor correspondiente
        } else if (modo === 'externo') {
          $('.campo-externo').show();
        }
      });

      $('#metodo_busqueda').on('change', function() {
        if ($(this).val() === 'cedula') {
          $('#div_busqueda_cedula').show();
          $('#div_busqueda_nombre').hide();
        } else {
          $('#div_busqueda_cedula').hide();
          $('#div_busqueda_nombre').show();
        }
        buscarPrescripcionesAjax();
      });

      // -------------------------------------------------------------
      // LÓGICA DE BÚSQUEDA AJAX DE RECETAS
      // -------------------------------------------------------------
      function buscarPrescripcionesAjax(callback) {
        var metodo = $('#metodo_busqueda').val();
        var busqueda = metodo === 'cedula' ? $('#busqueda_cedula').val() : $('#busqueda_nombre').val();
        var tipoCed = metodo === 'cedula' ? $('#tipo_cedula').val() : '';
        var modo = $('#tipo_despacho').val();
        var es_menor = (modo === 'representante') ? 1 : 0;

        busqueda = busqueda || ""; // Prevención de errores si está vacío

        if (busqueda.length > 2 || (metodo === 'cedula' && busqueda.length >= 6)) {
          $.ajax({
            url: '../../cfg/ajax/obtener_prescripciones.php',
            type: 'POST',
            data: {
              busqueda: busqueda,
              tipo_cedula: tipoCed,
              metodo: metodo,
              es_menor: es_menor
            },
            success: function(res) {
              $('#id_prescripcion').html(res);
              // Disparar change para limpiar los datos visuales si se vació la lista
              $('#id_prescripcion').trigger('change');
              if (typeof callback === 'function') {
                  callback();
              }
            },
            error: function() {
              console.error("Error al buscar prescripciones");
            }
          });
        } else {
            if (typeof callback === 'function') {
                callback();
            }
        }
      }

      $('#busqueda_cedula, #busqueda_nombre').on('keyup', function() { buscarPrescripcionesAjax(); });
      $('#tipo_cedula').on('change', function() { buscarPrescripcionesAjax(); });

      // MOSTRAR DATOS DEL PACIENTE/REPRESENTANTE
      $('#id_prescripcion').on('change', function() {
        var selected = $(this).find('option:selected');

        if ($(this).val() !== "") {
          var pac = selected.data('paciente') || 'Desconocido';
          var pacCed = (selected.data('tipo-cedula-p') || '') + '-' + (selected.data('cedula-p') || '');
          var edad = selected.data('edad') ? selected.data('edad') + ' años' : '';
          var rep = selected.data('representante') || '';
          var repCed = (selected.data('tipo-cedula-r') || '') + '-' + (selected.data('cedula-r') || '');

          $('#info_paciente_nom').val(pac);
          var addEdad = edad ? ' | Edad: ' + edad : '';
          $('#info_paciente_ced').val('C.I: ' + pacCed + addEdad);

          if ($('#tipo_despacho').val() === 'representante' && rep !== "") {
            $('#info_rep_nom').val(rep);
            $('#info_rep_ced').val('C.I: ' + repCed);
            $('#col_info_rep').show();
          } else {
            $('#col_info_rep').hide();
          }

          $('#detalles_vinculo').show();
        } else {
          $('#detalles_vinculo').hide();
        }
      });

      // -------------------------------------------------------------
      // LÓGICA DEL FILTRADO AVANZADO 
      // -------------------------------------------------------------
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
                // Pasando los datos requeridos para la tabla de salida
                var nombreData = item.nombre_medicamento ? item.nombre_medicamento : item.nombre_completo;
                var compData = item.componentes ? item.componentes : '';
                medicamentoSelectPrincipal.append('<option value="' + item.id_desc + '" data-nombre="' + nombreData + '" data-componentes="' + compData + '">' + item.nombre_completo + '</option>');
              });
            } else {
              medicamentoSelectPrincipal.append('<option value="" disabled>🛑 No se encontraron medicamentos que coincidan con los filtros aplicados.</option>');
            }

            // NOTA: Se eliminó el código que cerraba el modal automáticamente aquí.
          },
          error: function(jqXHR, textStatus, errorThrown) {
            console.error("Error en AJAX de filtrado avanzado: ", textStatus, errorThrown);
          }
        });
      });

      // -------------------------------------------------------------
      // LÓGICA DEL MODAL AGREGAR MEDICAMENTO
      // -------------------------------------------------------------

      // Control de visibilidad de Cantidad en Récipe según el tipo de despacho
      $('#tipo_despacho').on('change', function() {
        if ($(this).val() === 'externo') {
          $('#grupo_recetada').show();
        } else {
          $('#grupo_recetada').hide();
          $('#cantidad_recetada').val('');
        }
      });

      $('#btnAbrirModalAgregar').on('click', function() {
        editandoIndex = -1;
        limpiarFormularioModal();
        
        // CORRECCIÓN: Evitamos el trigger('change') y solo validamos si se muestra el campo
        if ($('#tipo_despacho').val() === 'externo') {
          $('#grupo_recetada').show();
        } else {
          $('#grupo_recetada').hide();
        }
        
        $('#btnConfirmarAgregarMedicamento').html('<i class="fa fa-check"></i> Añadir a la Lista');
        $('#modalAgregarMedicamento').modal('show');
      });

      // AL SELECCIONAR MEDICAMENTO, BUSCAR LOS LOTES DISPONIBLES EN STOCK
      $('#Id_descripcion_medicamento').on('change', function() {
        var medicamentoId = $(this).val();
        $('#lote_seleccionado').empty();
        $('#existencia_actual').val('');

        if (medicamentoId) {
          $.ajax({
            url: '../../cfg/ajax/obtener_descripcion_medicamento.php',
            type: 'POST',
            data: {
              id: medicamentoId,
              modo: 'salida'
            },
            dataType: 'json',
            success: function(data) {
              if (!data.error) {
                $('#lote_seleccionado').append('<option value="">-- Seleccione Lote --</option>');
                if (data.lotes && data.lotes.length > 0) {
                  $.each(data.lotes, function(index, item) {
                    var diasFaltantes = "";
                    if (item.fecha_vencimiento) {
                      var fVenc = new Date(item.fecha_vencimiento);
                      var hoy = new Date();
                      var diffTime = fVenc - hoy;
                      var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

                      if (diffDays > 0) {
                        diasFaltantes = " (Faltan " + diffDays + " días)";
                      } else if (diffDays === 0) {
                        diasFaltantes = " (Vence Hoy)";
                      } else {
                        diasFaltantes = " (Vencido hace " + Math.abs(diffDays) + " días)";
                      }
                    }
                    $('#lote_seleccionado').append('<option value="' + item.lote + '" data-stock="' + item.cantidad_actual + '">Lote: ' + item.lote + ' - Stock: ' + item.cantidad_actual + diasFaltantes + '</option>');
                  });
                } else {
                  $('#lote_seleccionado').append('<option value="">Sin stock disponible</option>');
                }
              }
            }
          });
        }
      });

      // AL SELECCIONAR EL LOTE, MOSTRAR LA EXISTENCIA
      $('#lote_seleccionado').on('change', function() {
        var stock = $(this).find(':selected').data('stock');
        $('#existencia_actual').val(stock ? stock : '0');
      });

      // -------------------------------------------------------------
      // AGREGAR O EDITAR EN LA LISTA DE DETALLES
      // -------------------------------------------------------------
      $('#btnConfirmarAgregarMedicamento').on('click', function() {
        var id_med = $('#Id_descripcion_medicamento').val();
        var nombre_med = $('#Id_descripcion_medicamento option:selected').data('nombre');
        var componentes = $('#Id_descripcion_medicamento option:selected').data('componentes');
        var lote = $('#lote_seleccionado').val();
        var existencia = parseInt($('#existencia_actual').val());
        var cant = parseInt($('#cantidad').val());
        var cant_rec = parseInt($('#cantidad_recetada').val());
        var dosis = $('#dosis_aplicacion').val().trim();
        var esExterno = $('#tipo_despacho').val() === 'externo';

        $('.modal-body input, .modal-body select').removeClass('input-error');

        if (!id_med) {
          $('#Id_descripcion_medicamento').addClass('input-error');
          mostrarAviso('Seleccione un medicamento.');
          return;
        }
        if (!lote) {
          $('#lote_seleccionado').addClass('input-error');
          mostrarAviso('Debe seleccionar un lote con disponibilidad.');
          return;
        }
        if (isNaN(cant) || cant <= 0) {
          $('#cantidad').addClass('input-error');
          mostrarAviso('La cantidad a retirar debe ser mayor a 0.');
          return;
        }
        if (cant > existencia) {
          $('#cantidad').addClass('input-error');
          mostrarAviso('La cantidad supera la existencia actual en este lote (' + existencia + ').');
          return;
        }

        // Validación para externo: si no pone cantidad récipe, asumimos que es igual a la entrega
        if (esExterno && isNaN(cant_rec)) {
          cant_rec = cant;
        } else if (!esExterno) {
          cant_rec = cant; // Interno no maneja diferencia
        }

        var existeIndex = -1;
        for (var i = 0; i < listaDetalles.length; i++) {
          if (listaDetalles[i].id_medicamento === id_med && listaDetalles[i].lote === lote) {
            existeIndex = i;
            break;
          }
        }

        if (existeIndex !== -1 && existeIndex !== editandoIndex) {
          mostrarAviso('Este medicamento de este Lote ya está en la lista de despacho.');
          return;
        }

        var nuevoItem = {
          id_medicamento: id_med,
          nombre_medicamento: nombre_med,
          componentes: componentes,
          lote: lote,
          cantidad: cant,
          cantidad_recetada: cant_rec, // Nuevo campo
          dosis: dosis
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

      // -------------------------------------------------------------
      // DIBUJAR LA TABLA, ELIMINAR Y EDITAR ÍTEMS
      // -------------------------------------------------------------
      function actualizarTablaDetalles() {
        var tbody = $('#cuerpoTablaMedicamentos');
        var esExterno = $('#tipo_despacho').val() === 'externo';

        // LOGICA DEL ENCABEZADO: Si es externo lo muestra, si no lo oculta
        if (esExterno) {
          $('#th_recetada').show();
        } else {
          $('#th_recetada').hide();
        }

        tbody.empty();

        if (listaDetalles.length === 0) {
          // Si no hay nada, el colspan debe ser 6 si es externo o 5 si es interno
          var columnas = esExterno ? 6 : 5;
          tbody.append('<tr><td colspan="' + columnas + '" class="text-center text-muted">Aún no se han añadido medicamentos.</td></tr>');
        } else {
          $.each(listaDetalles, function(index, item) {

            // Solo creamos la celda de "Recetada" si es externo
            var celdaRecetada = esExterno ? '<td><span class="badge bg-blue" style="font-size:14px;">' + item.cantidad_recetada + '</span></td>' : '';

            tbody.append(
              '<tr>' +
              '<td><strong>' + item.nombre_medicamento + '</strong><br><small>' + item.componentes + '</small></td>' +
              '<td>' + item.lote + '</td>' +
              celdaRecetada + // Aparece solo en externo
              '<td><span class="badge bg-green" style="font-size:14px;">' + item.cantidad + '</span></td>' +
              '<td>' + (item.dosis ? item.dosis : 'Sin observación') + '</td>' +
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

      // ACCIÓN DE EDITAR
      $('#cuerpoTablaMedicamentos').on('click', '.btn-editar-fila', function() {
        var index = $(this).data('index');
        var item = listaDetalles[index];
        editandoIndex = index;

        limpiarFormularioModal();

        // Disparar lógica de visibilidad
        if ($('#tipo_despacho').val() === 'externo') {
          $('#grupo_recetada').show();
        } else {
          $('#grupo_recetada').hide();
        }

        $('#Id_descripcion_medicamento').val(item.id_medicamento).trigger('change');

        // Si fue cargado vía GET bloqueamos el cambio del medicamento
        if ("<?php echo $auto_id_pres; ?>" !== "") {
            $('#Id_descripcion_medicamento').prop('disabled', true);
            $('#btnBusquedaAvanzada').prop('disabled', true);
        }

        setTimeout(function() {
          $('#lote_seleccionado').val(item.lote).trigger('change');
          $('#cantidad').val(item.cantidad);
          $('#cantidad_recetada').val(item.cantidad_recetada); // Cargar valor guardado
          $('#dosis_aplicacion').val(item.dosis);
        }, 500);

        $('#btnConfirmarAgregarMedicamento').html('<i class="fa fa-save"></i> Guardar Cambios');
        $('#modalAgregarMedicamento').modal('show');
      });

      $('#cuerpoTablaMedicamentos').on('click', '.btn-eliminar-fila', function() {
        var index = $(this).data('index');
        listaDetalles.splice(index, 1);
        actualizarTablaDetalles();
      });

      // -------------------------------------------------------------
      // SUBMIT Y GUARDADO FINAL
      // -------------------------------------------------------------
      $('#formularioDespacho').on('submit', function(e) {
        e.preventDefault();

        var modo = $('#tipo_despacho').val();
        
        // --- VALIDACIONES DE RECETA Y PACIENTE ---
        if (modo === 'interno' || modo === 'representante') {
            var recetaSeleccionada = $('#id_prescripcion').val();
            if (!recetaSeleccionada || recetaSeleccionada === "") {
                mostrarAviso("Debe buscar y seleccionar una receta interna para procesar el despacho.");
                return;
            }
        } else if (modo === 'externo') {
            var cedulaExterno = $('input[name="cedula_externo"]').val();
            if (!cedulaExterno || cedulaExterno.trim() === "") {
                mostrarAviso("Debe ingresar la cédula del paciente externo.");
                return;
            }
            if ($('#paciente_externo').val() === "" || $('#medico_externo').val() === "") {
                mostrarAviso("Debe llenar el nombre del Paciente Externo y Médico Tratante.");
                return;
            }
        }
        // ----------------------------------------

        if (listaDetalles.length === 0) {
          mostrarAviso("Debe añadir al menos un medicamento a la lista para procesar el despacho.");
          return;
        }

        // Importante: Remover disabled a los select antes de enviar el formulario para que pasen en el POST si es necesario.
        $('#Id_descripcion_medicamento').prop('disabled', false);

        $('#modalDespachoGuardar').modal('show');
      });

      $('#confirmarGuardadoFinal').on('click', function() {
        $('#modalDespachoGuardar').modal('hide');
        $('#formularioDespacho').off('submit').submit();
      });

      // =========================================================================
      // --- INICIO SCRIPT AUTORELLENADO DESDE GET Y BLOQUEOS DE PRECARGA ---
      // =========================================================================
      var auto_id_pres = "<?php echo $auto_id_pres; ?>";
      var auto_tipo = "<?php echo $auto_tipo; ?>";
      var auto_pac = "<?php echo $auto_pac; ?>";
      var auto_menor = "<?php echo $auto_menor; ?>";
      
      // Pasar los medicamentos precargados por PHP al entorno JS
      var medicamentos_precargados = <?php echo json_encode($medicamentos_precargados ?? []); ?>;

      if (auto_id_pres !== "") {
          
          // Bloqueo de campos (solo lectura y sin clics) porque el formulario viene precargado
          $('#tipo_despacho, #metodo_busqueda, #tipo_cedula, #id_prescripcion, select[name="tipo_cedula_externo"]').css({'pointer-events': 'none', 'background-color': '#eeeeee', 'tabindex': '-1'});
          $('#busqueda_cedula, #busqueda_nombre, input[name="cedula_externo"], #paciente_externo, #medico_externo').prop('readonly', true);

          if (auto_tipo === 'Interna') {
              if (auto_menor === '1') {
                  $('#tipo_despacho').val('representante').trigger('change');
              } else {
                  $('#tipo_despacho').val('interno').trigger('change');
              }
              
              var tipo_ced = 'V';
              var num_ced = auto_pac;
              if (auto_pac.indexOf('-') > -1) {
                  var parts = auto_pac.split('-');
                  tipo_ced = parts[0];
                  num_ced = parts[1];
              } else {
                  var match = auto_pac.match(/^[A-Za-z]+/);
                  if(match){
                      tipo_ced = match[0].toUpperCase();
                      num_ced = auto_pac.replace(match[0], '');
                  }
              }
              
              $('#metodo_busqueda').val('cedula').trigger('change');
              $('#tipo_cedula').val(tipo_ced);
              $('#busqueda_cedula').val(num_ced);
              
              // Se hace la búsqueda y se selecciona automáticamente
              buscarPrescripcionesAjax(function() {
                  setTimeout(function() {
                      $('#id_prescripcion').val(auto_id_pres).trigger('change');
                      
                      // Cargar los medicamentos automáticamente a la tabla
                      if (medicamentos_precargados.length > 0) {
                          listaDetalles = medicamentos_precargados;
                          actualizarTablaDetalles();
                      } else {
                          mostrarAviso("No hay stock disponible para los medicamentos de esta receta.");
                      }
                  }, 500);
              });

          } else if (auto_tipo === 'Externa') {
              // AL CAMBIAR AL FINAL, GARANTIZA QUE LOS CAMPOS EXTERNOS APAREZCAN
              $('#tipo_despacho').val('externo').trigger('change');

              if (auto_id_pres !== "") {
                  $('#id_prescripcion').html('<option value="' + auto_id_pres + '" selected>Receta Externa (ID: ' + auto_id_pres + ')</option>');
              }
              
              $('select[name="tipo_cedula_externo"]').val("<?php echo $ext_tipo_ced; ?>");
              $('input[name="cedula_externo"]').val("<?php echo $ext_cedula; ?>");
              $('#paciente_externo').val("<?php echo $ext_paciente; ?>");
              $('#medico_externo').val("<?php echo $ext_medico; ?>");
              
              // Cargar los medicamentos automáticamente a la tabla
              if (medicamentos_precargados.length > 0) {
                  listaDetalles = medicamentos_precargados;
                  actualizarTablaDetalles();
              } else {
                  mostrarAviso("No hay stock disponible para los medicamentos de esta receta externa.");
              }
          }
      } else {
          // Si no hay precarga de parámetros, inicializa la vista de forma regular
          $('#tipo_despacho').trigger('change');
          $('#metodo_busqueda').trigger('change');
      }

    });
  </script>
</body>
</html>