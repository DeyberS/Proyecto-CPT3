<?php
// Incluir la conexión a la base de datos (se asume esta ruta)
include("../../cfg/conexion.php");
$operacion_actual = isset($_GET['op']) ? $_GET['op'] : 'despacho';

$sql_ultimo_id = "SELECT MAX(Id_detalle_inventario) AS ultimo FROM detalle_inventario";
$resultado_id = $conexion->query($sql_ultimo_id);
$row_id = $resultado_id->fetch_assoc();
$proximo_id = ($row_id['ultimo'] ? $row_id['ultimo'] : 0) + 1;
// Formateamos el número para que tenga 6 dígitos (ej: 000281)
$numero_proyectado = str_pad($proximo_id, 6, "0", STR_PAD_LEFT);

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
  $sql_ext = "SELECT 
                  sm.id_medico,
                  sm.entregado_a,
                  p.nombre AS p_nom, 
                  p.apellido AS p_ape, 
                  p.cedula AS p_ced, 
                  p.tipo_cedula AS p_tced,
                  pm.nombre AS m_nom, 
                  pm.apellido AS m_ape,
                  pm.cedula AS m_ced,        
                  pm.tipo_cedula AS m_tced,
                  rep.nombre AS r_nom,
                  rep.apellido AS r_ape
              FROM solicitud_medicamento sm
              INNER JOIN persona p ON sm.id_paciente = p.id
              LEFT JOIN detalle_medico dm ON sm.id_medico = dm.Id_detalle_medico
              LEFT JOIN persona pm ON dm.id_persona = pm.id
              LEFT JOIN detalle_paciente_menor dpm ON p.id = dpm.id_persona
              LEFT JOIN persona rep ON dpm.id_representante = rep.id
              WHERE sm.id_solicitud = '$auto_id_pres'";

  $res_ext = mysqli_query($conexion, $sql_ext);
  if ($res_ext && $row_ext = mysqli_fetch_assoc($res_ext)) {
    $ext_paciente = trim($row_ext['p_nom'] . ' ' . $row_ext['p_ape']);
    $ext_medico   = !empty($row_ext['m_nom']) ? trim($row_ext['m_nom'] . ' ' . $row_ext['m_ape']) : 'Médico Externo';
    $ext_cedula   = $row_ext['p_ced'];
    $ext_tipo_ced = $row_ext['p_tced'] ? $row_ext['p_tced'] : 'V';
    $ext_cedula_med = $row_ext['m_ced'];
    $ext_tipo_ced_med = $row_ext['m_tced'] ? $row_ext['m_tced'] : 'V';

    // Lógica para determinar el nombre de quien recibe el medicamento
    $ext_entregado_a = $ext_paciente;
    if ($auto_menor == '1' && !empty($row_ext['r_nom'])) {
        $ext_entregado_a = trim($row_ext['r_nom'] . ' ' . $row_ext['r_ape']);
    } elseif (!empty($row_ext['entregado_a'])) {
        $ext_entregado_a = $row_ext['entregado_a'];
    }

    if (!empty($row_ext['id_medico'])) {
      $auto_id_med = $row_ext['id_medico'];
    }
  }
}

// --- NUEVO: PRECARGAR MEDICAMENTOS CON SU LOTE (FIFO) ---
$medicamentos_precargados = [];
if ($auto_id_pres != '') {
  $query_meds = "";

  if ($auto_tipo == 'Interna') {
    // Recetas internas (Se guarda en prescripcion_medicamentos)
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
    // Recetas externas (Se guarda en detalle_solicitud)
    $query_meds = "SELECT 
                      dm.Id AS id_medicamento, 
                      m.nombre_medicamento,
                      ds.cantidad_recetada,
                      IFNULL(ds.cantidad_entregada, 0) AS cantidad_entregada, 
                      '' AS dosis,
                      (SELECT GROUP_CONCAT(CONCAT(IFNULL(pa.nombre,''), ' ', IFNULL(dpm.cantidad_unidad_medida,''), ' ', IFNULL(um.unidad,'')) SEPARATOR ' + ') 
                       FROM detalle_principio_medicamento dpm 
                       LEFT JOIN principio_activo pa ON dpm.id_principio_activo = pa.Id_principio_activo 
                       LEFT JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida 
                       WHERE dpm.id_medicamento = dm.Id) AS componentes
                  FROM detalle_solicitud ds
                  INNER JOIN descripcion_medicamento dm ON ds.id_medicamento = dm.Id
                  INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
                  WHERE ds.id_solicitud = '$auto_id_pres'";
  }

  if ($query_meds != "") {
    $res_meds = mysqli_query($conexion, $query_meds);
    if ($res_meds) {
      while ($row_med = mysqli_fetch_assoc($res_meds)) {
        $id_desc = $row_med['id_medicamento'];

        // Buscar el Lote disponible con mayor prioridad (fecha de vencimiento más próxima - FIFO)
        $sql_lote = "SELECT l.Lote as lote, ex.cantidad_actual 
                    FROM lotes_medicamentos l 
                    INNER JOIN existencias_stock ex ON l.Id = ex.Id_lote 
                    WHERE l.estado_lote = 'Disponible' 
                    AND l.Id_descripcion_medicamento = '$id_desc' 
                    AND ex.cantidad_actual > 0 
                    AND l.fecha_vencimiento > CURDATE()
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

    /* Forzar que los input-groups utilicen flexbox moderno para resistir los cambios de zoom */
    .input-group {
      display: flex !important;
      width: 100% !important;
      align-items: stretch;
    }

    .input-group .form-control {
      flex: 1 1 auto;
      width: 1% !important;
      /* Mantiene la consistencia de Bootstrap */
      height: 34px !important;
    }

    .input-group-btn {
      width: auto !important;
      display: flex !important;
      align-items: stretch;
    }

    .input-group-btn .btn {
      height: 34px !important;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    /* Ajuste específico para que tu select de la cédula mantenga tamaño exacto */
    .input-group-btn style[width="25%"],
    .input-group-btn[style*="width: 25%"] {
      width: 65px !important;
      /* Cambiar el % por una medida fija en píxeles evita que se deforme con el zoom */
      flex: 0 0 65px !important;
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

              <div class="box-tools pull-left" style="margin-top: 2px;">
                <span style="font-size: 16px; font-weight: bold; color: #555;">
                  Operación N°: <span class="badge bg-blue" style="font-size: 15px; padding: 5px 10px; letter-spacing: 1px;">#<?php echo $numero_proyectado; ?></span>
                </span>
              </div>

              <br>

              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title"><i class="fa fa-user"></i> Datos de entrega:</h3>
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
                            <option value="V">V-</option>
                            <!--<option value="E">E-</option>-->
                            <!--<option value="PN">PN-</option>-->
                          </select>
                        </span>
                        <input type="text" id="busqueda_cedula" class="form-control" placeholder="Número de documento" style="height: 34px;" maxlength="8" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                      </div>

                      <div id="div_busqueda_nombre" style="display:none;">
                        <input type="text" id="busqueda_nombre" class="form-control" placeholder="Nombre o Apellido..." oninput="this.value = this.value.replace(/[0-9]/g, '')">
                      </div>
                    </div>

                    <div class="col-sm-4 form-group campo-interno">
                      <label>Recetas Encontradas:</label>
                      <select id="id_prescripcion" name="id_prescripcion" class="form-control">
                        <option value="">Seleccione una receta...</option>
                      </select>
                    </div>

                    <div class="col-sm-3 form-group campo-externo" style="display:none;">
                      <label>Cédula / Documento:</label>
                      <div class="input-group">
                        <span class="input-group-btn" style="width: 25%;">
                          <select id="tipo_cedula" name="tipo_cedula_externo" class="form-control" style="padding: 6px 2px; border-top-right-radius: 0; border-bottom-right-radius: 0;">
                            <option value="V">V-</option>
                            <option value="PN">PN-</option>
                            <option value="RP">REP-</option>
                          </select>
                        </span>
                        <input type="text" id="busqueda_cedula" name="cedula_externo" class="form-control" placeholder="Número de documento" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                      </div>
                    </div>

                    <div class="col-sm-3 form-group campo-externo" style="display:none;">
                      <label>Nombre (Paciente Externo) (*):</label>
                      <div class="input-group">
                        <input type="text" id="paciente_externo" name="paciente_externo" class="form-control" placeholder="Nombre completo" oninput="this.value = this.value.replace(/[0-9]/g, '')">
                        <span class="input-group-btn">
                          <button class="btn btn-info" type="button" id="btnAbrirSelectorPaciente" data-toggle="modal" data-target="#modalSeleccionTipoPaciente" title="Agregar Paciente" style="height: 34px;">
                            <i><img src="../../recursos/imagenes/iconos/agregar.png" style="width:10px; height:10px;"></i>
                          </button>
                        </span>
                      </div>
                    </div>

                    <div class="col-sm-3 form-group campo-externo" style="display:none;">
                      <label>Cédula (Médico):</label>
                      <div class="input-group">

                        <span class="input-group-btn" style="width: 25%;">
                          <select id="tipo_cedula_medico" name="tipo_cedula_medico" class="form-control" style="padding: 6px 2px; border-top-right-radius: 0; border-bottom-right-radius: 0;">
                            <option value="V">V-</option>
                          </select>
                        </span>

                        <input type="text" id="busqueda_cedula_medico" name="cedula_medico" maxlength="8" class="form-control" placeholder="Número de documento" oninput="this.value = this.value.replace(/[^0-9]/g, '')">

                        <span class="input-group-btn">
                          <button class="btn btn-info" type="button" id="btnBuscarMedico" data-toggle="modal" data-target="#modalBuscarMedico" title="Buscar Medico" style="height: 34px;">
                            <i><img src="../../recursos/imagenes/iconos/buscar.png" style="width:10px; height:10px;"></i>
                          </button>
                        </span>

                      </div>
                    </div>

                    <div class="col-sm-3 form-group campo-externo" style="display:none;">
                      <label>Médico Externo (*):</label>
                      <div class="input-group">
                        <input type="text" id="medico_externo" name="medico_externo" class="form-control" placeholder="Nombre del médico" oninput="this.value = this.value.replace(/[0-9]/g, '')">
                        <span class="input-group-btn">
                          <button class="btn btn-info" type="button" data-toggle="modal" data-target="#modalAgregarMedicoExterno" title="Agregar Medico" style="height: 34px;">
                            <i><img src="../../recursos/imagenes/iconos/agregar.png" style="width:10px; height:10px;"></i>
                          </button>
                        </span>
                      </div>
                    </div>

                    <div class="col-sm-3 form-group campo-externo" id="div_entregado_a" style="display:none;">
                      <label>Entregado a:</label>
                      <input type="text" id="entregado_a" name="entregado_a" class="form-control" placeholder="Nombre de quien recibe" oninput="this.value = this.value.replace(/[0-9]/g, '')">
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
                  <div class="box-tools pull-right" style="margin-left: 5px;">
                    <button type="button" class="btn btn-primary btn-sm" id="btnAbrirModalAgregar" style="width:200px;">
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
                          <!--<th>Observación / Aplicación</th>-->
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
                <div class="box box-default">
                  <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-camera"></i> Evidencia del Despacho (Medicamentos, Foto del Récipe, etc.):</h3>
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

  <div class="modal fade" id="modalSeleccionTipoPaciente" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h4 class="modal-title text-white"><i class="fa fa-users"></i> Tipo de Paciente</h4>
        </div>
        <div class="modal-body text-center">
          <p>¿Qué tipo de paciente desea registrar?</p>
          <div class="d-flex flex-column">
            <button type="button" class="btn btn-primary btn-block mb-2" id="btnAbrirModalAdulto">
              <i class="fa fa-user"></i> Paciente Adulto (+18)
            </button>
            <button type="button" class="btn btn-info btn-block" id="btnAbrirModalMenor">
              <i class="fa fa-child"></i> Paciente Menor (-18)
            </button>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
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

  <div class="modal fade" id="modalBuscarMedico" tabindex="-1" role="dialog" aria-labelledby="labelModalMedico" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content" style="border-radius: 8px;">
        <div class="modal-header bg-primary" style="border-top-left-radius: 8px; border-top-right-radius: 8px;">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="labelModalMedico" style="color: white;"><i class="fa fa-user-md"></i> Buscar Médico Externo</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-sm-6 form-group">
              <label>Filtrar por Cédula:</label>
              <div class="input-group">
                <span class="input-group-btn" style="width: 30%;">
                  <select id="modal_buscar_tipo_cedula" class="form-control" style="padding: 6px 2px; height: 34px;">
                    <option value="V">V-</option>
                  </select>
                </span>
                <input type="text" id="modal_buscar_cedula" class="form-control" placeholder="Ej: 12345678" style="height: 34px;" maxlength="8" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
              </div>
            </div>
            <div class="col-sm-6 form-group">
              <label>Filtrar por Nombre:</label>
              <input type="text" id="modal_buscar_nombre" class="form-control" placeholder="Ej: Carlos Mendoza" oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '')">
            </div>
          </div>

          <div class="form-group">
            <label>Seleccione el Médico:</label>
            <select id="modal_select_resultados_medicos" class="form-control">
              <option value="" disabled selected>Comience a escribir para buscar un médico...</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-info" id="btnAceptarMedicoModal">Aceptar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalAgregarMedicamento" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 1;">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title" style="color: white;"><i class="fa fa-share-square-o"></i> Despachar Medicamento</h4>
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

                    AND EXISTS (
                        SELECT 1 FROM lotes_medicamentos lm 
                        INNER JOIN existencias_stock ex ON lm.Id = ex.Id_lote 
                        WHERE lm.Id_descripcion_medicamento = dm.Id 
                        AND lm.estado_lote = 'Disponible' 
                        AND ex.cantidad_actual > 0 
                        AND lm.fecha_vencimiento > CURDATE()
                    )
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
              <div class="input-group">
                <select id="lote_seleccionado" class="form-control">
                  <option value="">Seleccione el medicamento primero</option>
                </select>
                <span class="input-group-btn">
                  <button class="btn btn-info" type="button" id="infoLote" title="Informacion del lote" style="height: 34px;">
                    <i><img src="../../recursos/imagenes/iconos/info.png" style="width:10px; height:10px;"></i>
                  </button>
                </span>
              </div>
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
              <!--<label>Observación / Aplicación (Dosis):</label>-->
              <input type="hidden" id="dosis_aplicacion" class="form-control" rows="2" placeholder="Ej: Tomar 1 tableta cada 8 horas...">
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
        <div id="headerDespachoAviso" class="modal-header bg-crimson">
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

  <?php
  include('modales/inventario/medicamentos/medicamentos_agregar_modal.php');
  include('modales/recursos_humanos/medicos/modal_medico_agregar.php');
  include('modales/consultas/modal_pacientes_agregar_consulta.php');
  include('modales/consultas/modal_pacientes_menores_agregar_consulta.php');

  if (isset($conexion)) {
    $conexion->close();
  }

  include('includes/footer.php');
  ?>

  <script>
    $(document).ready(function() {
      $.fn.modal.Constructor.prototype.enforceFocus = function() {};

      $('#infoLote').tooltip({
        html: true,
        placement: 'right', // o 'bottom'
        title: 'Seleccione un lote primero'
      });

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

      $(document).ready(function() {
        // 2. Al pulsar "Adulto", cerramos el selector y abrimos el modal que ya tienes
        $('#btnAbrirModalAdulto').off('click').on('click', function() {
          $('#modalSeleccionTipoPaciente').modal('hide'); // Inicia el cierre (tarda 400ms)

          // Esperamos 450ms para asegurar que el anterior cerró completamente
          setTimeout(function() {
            $('#modalAgregarPacienteExterno').modal('show');
          }, 450);
        });

        // 3. Al pulsar "Menor"
        $('#btnAbrirModalMenor').off('click').on('click', function() {
          $('#modalSeleccionTipoPaciente').modal('hide');

          setTimeout(function() {
            $('#modalAgregarPacienteMenor').modal('show');
          }, 450);
        });
      });

      // ---------------------------------------------------------------------
      // LÓGICA DE CIERRE DE MODALES ANIMADOS (CORREGIDA)
      // ---------------------------------------------------------------------
      $('.modal').on('click', '[data-dismiss="modal"]', function(e) {
        e.preventDefault();
        var $modal = $(this).closest('.modal');

        $modal.removeClass('in').addClass('out');

        // 1. Limpiamos cualquier temporizador viejo para que no haya choques
        if ($modal.data('timerCierre')) {
          clearTimeout($modal.data('timerCierre'));
        }

        // 2. Creamos el nuevo temporizador de cierre y lo guardamos
        var timerId = setTimeout(function() {
          $modal.modal('hide');
          $modal.removeClass('out');
        }, 400); // 400ms de la animación

        $modal.data('timerCierre', timerId);
      });

      // NUEVO: Si se manda a abrir un modal, cancelamos su cierre fantasma y limpiamos la clase 'out'
      $('.modal').on('show.bs.modal', function() {
        if ($(this).data('timerCierre')) {
          clearTimeout($(this).data('timerCierre'));
        }
        $(this).removeClass('out');
      });

      $('.modal').on('hidden.bs.modal', function() {
        if ($('.modal:visible').length) {
          $('body').addClass('modal-open');
        } else {
          $('body').removeClass('modal-open');
          $('.modal-backdrop').remove();
        }
      });

      window.mostrarAviso = function(mensaje) {
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
      // LÓGICA DE BÚSQUEDA Y BLOQUEO DE MÉDICO EXTERNO (MODAL)
      // -------------------------------------------------------------

      // Función para buscar médicos mediante AJAX
      function filtrarMedicosModal() {
        let tipo_cedula = $('#modal_buscar_tipo_cedula').val();
        let cedula = $('#modal_buscar_cedula').val().trim();
        let nombre = $('#modal_buscar_nombre').val().trim();
        let select = $('#modal_select_resultados_medicos');

        // Eliminamos el 'return' para permitir que busque todos si los campos están vacíos
        select.empty().append('<option value="" disabled selected>Cargando médicos...</option>');

        $.ajax({
          url: '../../cfg/ajax/buscar_medicos.php',
          type: 'POST',
          data: {
            tipo_cedula: tipo_cedula,
            cedula: cedula,
            nombre: nombre
          },
          dataType: 'json',
          success: function(response) {
            select.empty();

            if (response.length > 0) {
              // Agregamos una opción por defecto para obligar al usuario a hacer clic en uno
              select.append('<option value="" disabled selected>--- Seleccione un médico de la lista ---</option>');
              response.forEach(function(medico) {
                select.append(`<option value="${medico.cedula}" data-tipo="${medico.tipo_cedula}" data-nombre="${medico.nombre}">${medico.tipo_cedula}-${medico.cedula} | ${medico.nombre}</option>`);
              });
            } else {
              select.append('<option value="" disabled>❌ No se encontraron médicos.</option>');
            }
          },
          error: function() {
            select.empty().append('<option value="" disabled>Error al cargar la lista.</option>');
          }
        });
      }

      // Escuchar la escritura o el cambio en los campos del modal
      $('#modal_buscar_tipo_cedula, #modal_buscar_cedula, #modal_buscar_nombre').on('input change', function() {
        filtrarMedicosModal();
      });

      // NUEVO: Cargar todos los médicos automáticamente cuando se abre el modal
      $('#modalBuscarMedico').on('show.bs.modal', function() {
        // Limpiamos los inputs de búsqueda por si quedaron sucios de antes
        $('#modal_buscar_cedula').val('');
        $('#modal_buscar_nombre').val('');
        // Ejecutamos la búsqueda (al estar vacíos, traerá todos)
        filtrarMedicosModal();
      });
      // Acción al hacer clic en "Aceptar" en el modal
      $('#btnAceptarMedicoModal').on('click', function() {
        let opcionSeleccionada = $('#modal_select_resultados_medicos option:selected');

        // Validación: Asegurar que se haya seleccionado una opción válida y no el mensaje por defecto
        if (opcionSeleccionada.length > 0 && !opcionSeleccionada.is(':disabled')) {
          let cedula = opcionSeleccionada.val();
          let tipo = opcionSeleccionada.data('tipo');
          let nombre = opcionSeleccionada.data('nombre');

          // Rellenar campos externos del formulario principal
          $('#tipo_cedula_medico').val(tipo);
          $('#busqueda_cedula_medico').val(cedula);
          $('#medico_externo').val(nombre);

          // Bloquear campos para evitar modificaciones manuales
          $('#busqueda_cedula_medico').attr('readonly', true);
          $('#medico_externo').attr('readonly', true);
          $('#tipo_cedula_medico').css({
            'pointer-events': 'none',
            'background-color': '#eee'
          });

          // Cerrar modal
          $('#modalBuscarMedico').modal('hide');
        } else {
          mostrarAviso("Debe realizar una búsqueda y seleccionar un médico válido de la lista.");
        }
      });

      // Si el usuario borra manualmente los datos (por ejemplo, si limpia el input de la cédula), se desbloquean los campos
      $('#busqueda_cedula_medico, #medico_externo').on('keyup input', function() {
        if ($('#busqueda_cedula_medico').val().trim() === "" && $('#medico_externo').val().trim() === "") {
          desbloquearCamposMedico();
        }
      });

      function desbloquearCamposMedico() {
        $('#busqueda_cedula_medico').removeAttr('readonly');
        $('#medico_externo').removeAttr('readonly');
        $('#tipo_cedula_medico').css({
          'pointer-events': 'auto',
          'background-color': '#fff'
        });
      }

      $('select[name="tipo_cedula_externo"]').on('change', function() {
        let tipo = $(this).val();
        let inputCedula = $('input[name="cedula_externo"]');

        if (tipo === 'V') {
          inputCedula.attr('maxlength', '8');
          inputCedula.val(inputCedula.val().replace(/[^0-9]/g, ''));
          inputCedula.off('input.validacion').on('input.validacion', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
          });
        } else if (tipo === 'PN') {
          inputCedula.attr('maxlength', '20');
          inputCedula.off('input.validacion').on('input.validacion', function() {
            this.value = this.value.replace(/[^0-9a-zA-Z-]/g, '');
          });
        } else if (tipo === 'RP') {
          // NUEVA LÓGICA PARA REPRESENTANTE (RP)
          inputCedula.attr('maxlength', '10'); // 8 números + 1 guion + 1 número
          inputCedula.val(inputCedula.val().replace(/[^0-9-]/g, ''));

          inputCedula.off('input.validacion').on('input.validacion', function() {
            // Quitamos todo lo que no sea número
            let valor = this.value.replace(/[^0-9]/g, '');

            // Si el usuario ya escribió más de 8 números, insertamos el guion automáticamente
            if (valor.length > 8) {
              valor = valor.substring(0, 8) + '-' + valor.substring(8, 9);
            }
            this.value = valor;
          });
        }
      });
      // Disparar el evento al cargar para que tome la configuración inicial
      $('select[name="tipo_cedula_externo"]').trigger('change');

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

      let streamModal = null; // Variable global para la cámara

      $(document).ready(function() {
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

        busqueda = busqueda || "";

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
              $('#id_prescripcion').html(res).trigger('change');

              // Verifica si la respuesta no trajo options válidos
              if (res.includes("No se encontro ninguna prescripcion")) {
                mostrarAviso("🛑 No se encontró ninguna prescripción con los datos proporcionados.");
              }

              if (typeof callback === 'function') callback();
            }
          });
        } else {
          if (typeof callback === 'function') callback();
        }
      }

      $('#busqueda_cedula, #busqueda_nombre').on('keyup', function() {
        buscarPrescripcionesAjax();
      });
      $('#tipo_cedula').on('change', function() {
        buscarPrescripcionesAjax();
      });

      // MOSTRAR DATOS DEL PACIENTE/REPRESENTANTE
      $('#id_prescripcion').on('change', function() {
        var selected = $(this).find('option:selected');
        var id_receta = $(this).val();

        if (id_receta !== "") {
          var pac = selected.data('paciente') || 'Desconocido';
          var pacCed = (selected.data('tipo-cedula-p') || '') + '-' + (selected.data('cedula-p') || '');
          var edad = selected.data('edad') ? selected.data('edad') : '';
          var genero = selected.data('genero') ? selected.data('genero') : '';
          var rep = selected.data('representante') || '';
          var repCed = (selected.data('tipo-cedula-r') || '') + '-' + (selected.data('cedula-r') || '');

          $('#info_paciente_nom').val(pac);
          var addInfo = (edad ? ' | Edad: ' + edad : '') + (genero ? ' | Sexo: ' + genero : '');
          $('#info_paciente_ced').val('C.I: ' + pacCed + addInfo);

          if ($('#tipo_despacho').val() === 'representante' && rep !== "") {
            $('#info_rep_nom').val(rep);
            $('#info_rep_ced').val('C.I: ' + repCed);
            $('#col_info_rep').show();
          } else {
            $('#col_info_rep').hide();
          }

          $('#detalles_vinculo').show();

          // --- NUEVA LÓGICA: Buscar los medicamentos por AJAX ---
          $.ajax({
            url: '../../cfg/ajax/obtener_medicamentos_prescripcion.php', // Debes crear este archivo
            type: 'POST',
            data: {
              id_prescripcion: id_receta,
              tipo_despacho: $('#tipo_despacho').val() // 'interno', 'representante' o 'externo'
            },
            dataType: 'json',
            success: function(response) {
              if (response && response.length > 0) {
                listaDetalles = response; // Actualizamos el arreglo global
                actualizarTablaDetalles(); // Redibujamos la tabla
              } else {
                listaDetalles = [];
                actualizarTablaDetalles();
                mostrarAviso("No hay stock disponible o medicamentos pendientes para esta receta.");
              }
            },
            error: function() {
              mostrarAviso("Error al intentar cargar los medicamentos de la receta.");
            }
          });

        } else {
          $('#detalles_vinculo').hide();
          // Limpiar la tabla si el usuario vuelve a "Seleccione receta..."
          listaDetalles = [];
          actualizarTablaDetalles();
        }
      });

      // -------------------------------------------------------------
      // LÓGICA DEL FILTRADO AVANZADO 
      // -------------------------------------------------------------
      const medicamentoSelecte = $('#Id_descripcion_medicamento');
      const opcionesOriginalesMedicamentos = medicamentoSelecte.html(); // Guardamos las opciones iniciales cargadas por PHP

      $('#btnLimpiarFiltros').on('click', function() {
        $('#formFiltroModal')[0].reset();
        medicamentoSelecte.html(opcionesOriginalesMedicamentos); // Restauramos la lista completa
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
              modo: 'despacho'
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
                    $('#lote_seleccionado').append('<option value="' + item.lote + '" ' +
                      'data-stock="' + item.cantidad_actual + '" ' +
                      'data-vencido="' + (diffDays <= 0 ? 'si' : 'no') + '" ' +
                      'data-fab="' + (item.fecha_fabricacion ? item.fecha_fabricacion : 'N/A') + '" ' +
                      'data-venc="' + (item.fecha_vencimiento ? item.fecha_vencimiento : 'N/A') + '" ' +
                      'data-prov="' + (item.nombre_proveedor ? item.nombre_proveedor : 'Sin Proveedor') + '">' +
                      'Lote: ' + item.lote + ' - Stock: ' + item.cantidad_actual + diasFaltantes +
                      '</option>');
                  });
                } else {
                  $('#lote_seleccionado').append('<option value="">Sin stock disponible</option>');
                }
              }
            }
          });
        }
      });

      $('#lote_seleccionado').on('change', function() {
        var option = $(this).find(':selected');
        var stock = option.data('stock');
        $('#existencia_actual').val(stock ? stock : '0');

        // --- NUEVA LÓGICA DEL TOOLTIP ---
        if ($(this).val() !== "") {
          var fab = option.data('fab');
          var venc = option.data('venc');
          var prov = option.data('prov');

          // Construimos el HTML del tooltip
          var infoHtml = "<div style='text-align:left; font-size: 12px;'>" +
            "<b>Proveedor:</b> " + prov + "<br>" +
            "<b>F. Fabricación:</b> " + fab + "<br>" +
            "<b>F. Vencimiento:</b> " + venc + "<br>" +
            "<b>Stock Físico:</b> " + stock +
            "</div>";

          // Actualizamos el tooltip de Bootstrap dinámicamente
          $('#infoLote').attr('data-original-title', infoHtml).tooltip('fixTitle');
        } else {
          $('#infoLote').attr('data-original-title', 'Seleccione un lote primero').tooltip('fixTitle');
        }
      });

      // -------------------------------------------------------------
      // AUTORELLENADO DE PACIENTE EXTERNO (UNIFICADO)
      // -------------------------------------------------------------
      $('input[name="cedula_externo"]').off('keyup blur').on('keyup blur', function(e) {
        var cedula = $(this).val();
        var tipo = $('select[name="tipo_cedula_externo"]').val();
        var inputNombre = $('#paciente_externo');
        var inputEntregado = $('#entregado_a');

        // Solo hacemos la consulta si la cédula tiene al menos 6 dígitos
        if (cedula.length >= 6) {
          $.ajax({
            url: '../../cfg/ajax/obtener_paciente_externo.php',
            type: 'POST',
            data: {
              cedula: cedula,
              tipo_cedula: tipo // Enviamos el tipo de cédula
            },
            dataType: 'json',
            success: function(response) {
              if (response.encontrado) {
                // Si lo encuentra, rellena y bloquea (readonly)
                inputNombre.val(response.nombre).prop('readonly', true).css('background-color', '#e9ecef');

                // Autorellenar y bloquear el campo 'entregado a' si hay datos previos o representante (Menores)
                if (response.entregado_a && response.entregado_a !== '') {
                  inputEntregado.val(response.entregado_a)
                    .prop('readonly', true)
                    .css('background-color', '#e9ecef');
                } else if (response.representante && response.representante !== '') {
                  inputEntregado.val(response.representante)
                    .prop('readonly', true)
                    .css('background-color', '#e9ecef');
                } else {
                  // Si es mayor de edad o no tiene historial, queda vacío y editable
                  inputEntregado.val('')
                    .prop('readonly', false)
                    .css('background-color', '#ffffff');
                }
              } else {
                inputNombre.val('').prop('readonly', true).css('background-color', '#f8d7da');
                inputEntregado.val('').prop('readonly', true).css('background-color', '#f8d7da');

                if (cedula === "") {
                  inputNombre.css('background-color', '#e9ecef');
                  inputEntregado.css('background-color', '#e9ecef');
                  return;
                }

                // Si el evento fue 'blur' (perdió el foco) y no lo encontró, mostramos el aviso
                if (e.type === 'blur') {
                  mostrarAviso("🛑 El paciente con documento " + tipo + "-" + cedula + " no está registrado o no es un paciente. <b>Debe registrarlo obligatoriamente</b> haciendo clic en el botón <b>(+)</b>.");
                  return;
                }
              }
            },
            error: function() {
              console.error("Error al buscar datos del paciente externo.");
            }
          });
        } else {
          // Si borran la cédula y quedan menos de 6 dígitos, liberamos el campo
          inputNombre.val('').prop('readonly', false).css('background-color', '');
          inputEntregado.val('');
        }
      });

      // BÚSQUEDA AUTORELLENADO DE MÉDICO EXTERNO
      $('#busqueda_cedula_medico').off('blur').on('blur', function() {
        var cedula = $(this).val();
        var tipo = $('#tipo_cedula_medico').val();
        var inputNombre = $('#medico_externo');

        if (cedula.length >= 6) {
          $.ajax({
            url: '../../cfg/ajax/obtener_medico_externo.php', // Crearás este archivo en tu backend
            type: 'POST',
            data: {
              cedula: cedula,
              tipo_cedula: tipo
            },
            dataType: 'json',
            success: function(response) {
              if (response.encontrado) {
                // Si lo encuentra, rellena, concatena (si lo deseas) y bloquea el campo
                inputNombre.val(response.nombre).prop('readonly', true).css('background-color', '#e9ecef');
              } else {
                // Si no lo encuentra, libera el campo para escritura manual y lanza el modal de aviso
                inputNombre.val('').prop('readonly', false).css('background-color', '');
                mostrarAviso("🛑 El médico con documento " + tipo + "-" + cedula + " no está registrado. Ingrese el nombre manualmente o regístrelo.");
              }
            },
            error: function() {
              console.error("Error al buscar datos del médico externo.");
            }
          });
        } else {
          // Liberar si borran la cédula
          inputNombre.val('').prop('readonly', false).css('background-color', '');
        }
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

        var esVencido = $('#lote_seleccionado option:selected').data('vencido');
        if (esVencido === 'si') {
          $('#lote_seleccionado').addClass('input-error');
          mostrarAviso('No puede despachar un medicamento de un lote vencido a un paciente.');
          return;
        }

        // VALIDACIÓN: Si el tipo de despacho es externo, el paciente y médico son obligatorios
        if ($('#tipo_despacho').val() === 'externo') {
          if ($('#paciente_externo').val().trim() === "") {
            $('#paciente_externo').addClass('input-error');
            mostrarAviso("El registro del Paciente Externo es estrictamente obligatorio para este despacho.");
            return; // Detiene el envío del formulario
          }

          if ($('#busqueda_cedula_medico').val().trim() === "" || $('#medico_externo').val().trim() === "") {
            $('#busqueda_cedula_medico').addClass('input-error');
            mostrarAviso("Debe ingresar o buscar la Cédula y Nombre del Médico.");
            return;
          }
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
              '<td><strong>' + item.nombre_medicamento + '</strong></td>' +
              '<td>' + item.lote + '</td>' +
              celdaRecetada + // Aparece solo en externo
              '<td><span class="badge bg-green" style="font-size:14px;">' + item.cantidad + '</span></td>' +
              //'<td>' + (item.dosis ? item.dosis : 'Sin observación') + '</td>' +
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

        // 1. Limpiar todos los bordes rojos previos al intentar guardar
        $('.form-control').removeClass('input-error');
        var hayErrores = false;
        var modo = $('#tipo_despacho').val();

        // --- VALIDACIONES DE RECETA Y PACIENTES (CON BORDES ROJOS) ---
        if (modo === 'interno' || modo === 'representante') {
          var recetaSeleccionada = $('#id_prescripcion').val();
          if (!recetaSeleccionada || recetaSeleccionada === "") {
            $('#id_prescripcion').addClass('input-error');
            mostrarAviso("Debe buscar y seleccionar una receta interna para procesar el despacho.");
            hayErrores = true;
          }
        } else if (modo === 'externo') {
          var cedulaExterno = $('input[name="cedula_externo"]').val();
          var pacienteExterno = $('#paciente_externo').val();
          var cedulaMedico = $('#busqueda_cedula_medico').val();
          var medicoExterno = $('#medico_externo').val();

          if (!cedulaExterno || cedulaExterno.trim() === "") {
            $('input[name="cedula_externo"]').addClass('input-error');
            hayErrores = true;
          }
          if (!pacienteExterno || pacienteExterno.trim() === "") {
            $('#paciente_externo').addClass('input-error');
            hayErrores = true;
          }
          if (!cedulaMedico || cedulaMedico.trim() === "") {
            $('#busqueda_cedula_medico').addClass('input-error');
            hayErrores = true;
          }
          if (!medicoExterno || medicoExterno.trim() === "") {
            $('#medico_externo').addClass('input-error');
            hayErrores = true;
          }

          if (hayErrores) {
            mostrarAviso("Faltan datos requeridos. Complete todos los campos marcados en rojo en la sección del Paciente Externo.");
          }
        }

        // 2. Si se detectó algún campo vacío en las comprobaciones anteriores, abortamos la función
        if (hayErrores) return;

        // 3. Validar si la tabla de medicamentos tiene productos
        if (listaDetalles.length === 0) {
          $('#btnAbrirModalAgregar').removeClass('btn-primary').addClass('btn-error-sombreado');
          mostrarAviso("Debe añadir al menos un medicamento a la lista para procesar el despacho.");
          return;
        }

        // Importante: Remover disabled a los select antes de enviar el formulario para que pasen en el POST si es necesario.
        $('#Id_descripcion_medicamento').prop('disabled', false);

        $('#modalDespachoGuardar').modal('show');
      });

      $('#btnAbrirModalAgregar').on('click', function() {
        $(this).removeClass('btn-error-sombreado').addClass('btn-primary');
        editandoIndex = -1;
        limpiarFormularioModal();
        $('#btnConfirmarAgregarMedicamento').html('<i class="fa fa-check"></i> Añadir a la Lista');
        $('#modalAgregarMedicamento').modal('show');
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
        $('#tipo_despacho, #metodo_busqueda, #tipo_cedula, #id_prescripcion, select[name="tipo_cedula_externo"]').css({
          'pointer-events': 'none',
          'background-color': '#eeeeee',
          'tabindex': '-1'
        });
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
            if (match) {
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
          $('#entregado_a').val("<?php echo isset($ext_entregado_a) ? $ext_entregado_a : $ext_paciente; ?>");
          $('#medico_externo').val("<?php echo $ext_medico; ?>");
          $('#tipo_cedula_medico').val("<?php echo $ext_tipo_ced_med ?? 'V'; ?>");
          $('#busqueda_cedula_medico').val("<?php echo $ext_cedula_med ?? ''; ?>");

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

    // -------------------------------------------------------------
    // ACTUALIZACIÓN SILENCIOSA DEL SELECT DE MEDICAMENTOS
    // -------------------------------------------------------------
    function actualizarSelectMedicamentosSilencio() {
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

        $.ajax({
          url: '../../cfg/ajax/filtrar_medicamentos_completo.php', // Enviamos petición vacía para traer todos
          type: 'POST',
          data: {
            recarga_silenciosa: true
          },
          dataType: 'json',
          success: function(response) {
            const select = $('#Id_descripcion_medicamento');
            let nuevasOpciones = '<option value="">--- Seleccione un Medicamento ---</option>';

            if (response.length > 0) {
              response.forEach(function(item) {
                const comp = item.componentes ? ` data-componentes="${item.componentes}"` : '';
                nuevasOpciones += `<option value="${item.id_desc}" data-nombre="${item.nombre_completo}"${comp}>${item.nombre_completo}</option>`;
              });
            } else {
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
  </script>
</body>

</html>