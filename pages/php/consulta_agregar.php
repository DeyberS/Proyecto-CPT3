<?php
include("../../cfg/conexion.php");

/* ============================================================
   HANDLERS AJAX
   ============================================================ */
if (isset($_POST['ajax_nueva_alergia'])) {
  header('Content-Type: application/json');
  $nombre = $conexion->real_escape_string($_POST['nombre_alergia']);
  $verificar = $conexion->query("SELECT Id_alergias_conocidas FROM alergias_conocidas WHERE nombre_alergia = '$nombre'");
  if ($verificar && $verificar->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Ya existe una alergia registrada con el nombre: <b>' . $nombre . '</b>']);
    exit;
  }
  $conexion->query("INSERT INTO alergias_conocidas (nombre_alergia, estatus) VALUES ('$nombre', 1)");
  echo json_encode(['success' => true, 'id' => $conexion->insert_id, 'nombre' => $nombre]);
  exit;
}

if (isset($_POST['ajax_nueva_patologia'])) {
  header('Content-Type: application/json');
  $nombre = $conexion->real_escape_string($_POST['nombre_patologia']);
  $cie = $conexion->real_escape_string($_POST['codigo_cie'] ?? '');
  $contagiosa = $conexion->real_escape_string($_POST['enfermedad_contagiosa'] ?? 'NO');

  $verificar = $conexion->query("SELECT Id_patologia FROM patologias WHERE nombre_patologia = '$nombre'");
  if ($verificar && $verificar->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Ya existe una patología registrada con el nombre: <b>' . $nombre . '</b>']);
    exit;
  }
  $conexion->query("INSERT INTO patologias (nombre_patologia, codigo_cie, estatus, contagioso) VALUES ('$nombre', '$cie', 1, '$contagiosa')");
  echo json_encode(['success' => true, 'id' => $conexion->insert_id, 'nombre' => $nombre, 'cie' => $cie]);
  exit;
}

/* ============================================================
   CATÁLOGOS
   ============================================================ */
$q_ex = $conexion->query("SELECT id_examen, nombre, tipo FROM examen_catalogo WHERE estatus = 1 ORDER BY nombre ASC");
$lista_examenes_catalogo = [];
while ($e = $q_ex->fetch_assoc()) {
  $lista_examenes_catalogo[] = ['id' => (int)$e['id_examen'], 'nombre' => $e['nombre'], 'tipo' => $e['tipo']];
}

$lista_alergias_catalogo = [];
$q_ale = $conexion->query("SELECT Id_alergias_conocidas, nombre_alergia FROM alergias_conocidas WHERE estatus = 1 ORDER BY nombre_alergia ASC");
if ($q_ale) while ($a = $q_ale->fetch_assoc()) $lista_alergias_catalogo[] = ['id' => (int)$a['Id_alergias_conocidas'], 'nombre' => $a['nombre_alergia']];

$lista_patologias_catalogo = [];
$q_pat = $conexion->query("SELECT Id_patologia, nombre_patologia, codigo_cie FROM patologias WHERE estatus = 1 ORDER BY nombre_patologia ASC");
if ($q_pat) while ($p = $q_pat->fetch_assoc()) $lista_patologias_catalogo[] = ['id' => (int)$p['Id_patologia'], 'nombre' => $p['nombre_patologia'], 'cie' => $p['codigo_cie']];

$lista_discapacidades_catalogo = [];
$q_disc = $conexion->query("SELECT Id_discapacidad, nombre_discapacidad FROM discapacidades WHERE estatus = 1 ORDER BY nombre_discapacidad ASC");
if ($q_disc) while ($d = $q_disc->fetch_assoc())
  $lista_discapacidades_catalogo[] = ['id' => (int)$d['Id_discapacidad'], 'nombre' => $d['nombre_discapacidad']];

$res_horarios = $conexion->query("SELECT dia_semana, hora_inicio, hora_fin, estatus FROM configuracion_horarios");
$horarios_db = [];
while ($row_h = $res_horarios->fetch_assoc()) $horarios_db[$row_h['dia_semana']] = $row_h;

$cedula      = $_GET['cedula'] ?? '';
$id_cita     = $_GET['Id'] ?? '';
$motivo_cita = $_GET['motivo'] ?? '';
$tipo_get    = $_GET['tipo_cedula'] ?? 'V';

$datos_paciente = [];
$error_busqueda = null;
$id_medico_cita = null;
$fecha_cita     = null;

if ($id_cita) {
  $sql_cita = "SELECT dm.Id_detalle_medico AS medico_id, c.fecha_cita
               FROM citas c JOIN detalle_medico dm ON c.Id_medico = dm.Id_detalle_medico
               WHERE c.id_cita = ?";
  $stmt = $conexion->prepare($sql_cita);
  $stmt->bind_param("i", $id_cita);
  $stmt->execute();
  $resultado = $stmt->get_result();
  if ($fila = $resultado->fetch_assoc()) {
    $id_medico_cita = (int)$fila['medico_id'];
    $fecha_cita     = $fila['fecha_cita'];
  }
  $stmt->close();
}

if (!empty($cedula) && isset($conexion)) {
  try {
    $safe = $conexion->real_escape_string($cedula);
    $r = $conexion->query("SELECT nombre, apellido, cedula, fecha_nacimiento, genero FROM persona WHERE cedula = '$safe'");
    $datos_paciente = $r ? $r->fetch_assoc() : [];
    if ($r) $r->free();
    if (!$datos_paciente) $error_busqueda = "Paciente con Cédula " . htmlspecialchars($cedula) . " no encontrado.";
  } catch (\mysqli_sql_exception $e) {
    $error_busqueda = "Error de BD: " . $e->getMessage();
  }
}

include('includes/headerNav2.php');
verificarAcceso('Crear Consultas');
?>

<head>
  <title>Consultas | Añadir</title>
</head>

<style>
  .input-error input[type="text"], .input-error select, .input-error textarea,
  input.input-error, select.input-error, textarea.input-error {
    border: 2px solid crimson !important;
    box-shadow: 0 0 5px crimson !important;
    background-color: #fff5f5 !important;
  }
  .nav-tabs>li>a { pointer-events: none; cursor: default; }
  .nav-tabs li.disabled-table a { color: #b2b2b2 !important; }
  #avisoModalConsulta { z-index: 2000000 !important; }
  .fila-paciente-top .form-group { margin-bottom: 10px; }
  #edad_paciente { background-color: #f4f4f4 !important; font-weight: bold; color: #333; text-align: center; }
  .tab-content .tab-panel { display: none; }
  .tab-content .tab-panel.active { display: block; }
  .row-actions {
    display: flex !important; justify-content: flex-end !important;
    gap: 10px !important; margin: 22px 0 15px !important; padding: 0 5px !important;
  }
  .row-actions .btn { min-width: 110px; }
  .tab-pane section { padding-bottom: 5px; }
  .fila-ale .select-ale, .fila-pat .select-pat, .fila-disc .select-disc { width: 100%; }

  .btn-antecedente-tipo {
    min-height: 70px; text-align: left; padding: 10px 15px;
    font-weight: 600; font-size: 13px; white-space: normal; line-height: 1.2;
  }
  .btn-antecedente-tipo .badge { background: #fff; color: #3c8dbc; font-weight: bold; }
  .chk-antecedente-wrap { padding: 6px 10px; border-bottom: 1px solid #eee; }
  .chk-antecedente-wrap:hover { background: #f9f9f9; }
  .chk-antecedente-wrap label { font-weight: normal; cursor: pointer; margin: 0; }

  /* Modales responsivos */
  .modal-dialog { max-height: calc(100vh - 60px); margin: 30px auto; }
  .modal-content { max-height: calc(100vh - 60px); display: flex; flex-direction: column; }
  .modal-header, .modal-footer { flex-shrink: 0; }
  .modal-body { overflow-y: auto; -webkit-overflow-scrolling: touch; flex: 1 1 auto; }
  @media (max-width: 767px) {
    .modal-dialog { max-height: calc(100vh - 20px); margin: 10px auto; }
    .modal-content { max-height: calc(100vh - 20px); }
  }
  .modal.in ~ .modal-backdrop { z-index: 1040; }

  /* Campana de alertas */
  #li-notificacion-alertas>a.link-campana-alertas {
    pointer-events: auto !important; cursor: pointer !important; text-decoration: none !important;
    display: inline-block !important; position: relative !important; padding: 12px 18px !important; line-height: 1 !important;
  }
  #li-notificacion-alertas .badge.bg-crimson {
    position: absolute !important; top: 6px !important; right: 8px !important;
    background-color: crimson !important; color: #fff !important; font-size: 10px !important;
    padding: 2px 6px !important; font-weight: bold !important; border-radius: 10px !important;
    min-width: 18px !important; text-align: center !important; border: 2px solid #fff !important;
    box-shadow: 0 1px 3px rgba(0,0,0,0.3) !important; line-height: 1.2 !important;
    z-index: 5 !important; display: inline-block !important;
  }
  #li-notificacion-alertas .icono-campana-alertas { display: inline-block !important; line-height: 1 !important; }
  #li-notificacion-alertas>a.link-campana-alertas:hover .icono-campana-alertas img { opacity: 0.5 !important; }
  #li-notificacion-alertas .icono-campana-alertas img {
    height: 25px !important; width: 25px !important; display: block !important; margin-top: 0px !important;
  }
</style>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Nueva Consulta Médica</h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="#"><i class="fa fa-users"></i>Consultas</a></li>
      <li class="active"><a href="#"><i class="fa fa-pencil"></i>Añadir</a></li>
    </ol>
  </section>

  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="nav-tabs-custom">
          <ul class="nav nav-tabs">
            <li class="active" data-tab-name="consulta"><a href="#consulta" data-toggle="table">Información del Paciente</a></li>
            <li data-tab-name="historial" class="disabled-table"><a href="#historial" data-toggle="table">Historial Médico</a></li>
            <li data-tab-name="diagnostico" class="disabled-table"><a href="#diagnostico" data-toggle="table">Diagnóstico</a></li>
            <li data-tab-name="indicacion_tratamiento" class="disabled-table"><a href="#indicacion_tratamiento" data-toggle="table">Indicación Final y Tratamiento</a></li>
            <li class="pull-right" id="li-notificacion-alertas" style="display:none;">
              <a href="javascript:void(0)" onclick="abrirModalAlertas(); return false;" title="Alertas del paciente" class="link-campana-alertas">
                <span class="badge bg-crimson" id="badge-total-alertas" style="display:none;" title="Alertas">0</span>
                <i class="icono-campana-alertas">
                  <img src="../../recursos/imagenes/iconos/filled/symbols/alert_triangle@2x.png" alt="Alertas">
                </i>
              </a>
            </li>
          </ul>

          <div class="tab-content">
            <form action="../../cfg/agregar/agregar_consulta.php" id="formularioConsulta" class="form-group" method="POST" novalidate>
              <!-- ============ TAB CONSULTA ============ -->
              <div class="tab-pane tab-panel active" id="consulta" style="margin-bottom:-3%;">
                <section>
                  <input type="hidden" name="id_cita_atendida" value="<?php echo htmlspecialchars($id_cita) ?>">
                  <input type="hidden" id="id_historial_global" value="">
                  <input type="hidden" name="medicamentos_json" id="medicamentos_json" value="">
                  <input type="hidden" name="examenes_json" id="examenes_json" value="">
                  <input type="hidden" name="antecedentes_json" id="antecedentes_json" value="">
                  <input type="hidden" name="id_representante" id="id_representante" value="">

                  <div class="row fila-paciente-top" style="margin-top: 15px;">
                    <div class="col-md-4 col-sm-6 form-group">
                      <p>Cédula / Documento (*)</p>
                      <div class="input-group" style="display:flex; width:100%; table-layout:fixed;">
                        <span class="input-group-addon" style="padding:0; border:none; width:80px; flex:0 0 80px;">
                          <select name="tipo_cedula_paciente" id="tipo_cedula_paciente" class="form-control" style="width:80px; height:34px; font-weight:bold; background-color:#f4f4f4; border-right:none; border-top-right-radius:0; border-bottom-right-radius:0;" <?php echo !empty($cedula) ? 'tabindex="-1" style="pointer-events:none; background-color:#eee;"' : ''; ?>>
                            <option value="PN" <?php echo ($tipo_get === 'PN') ? 'selected' : ''; ?>>PN-</option>
                            <option value="V" <?php echo ($tipo_get === 'V')  ? 'selected' : ''; ?>>V-</option>
                            <option value="RP" <?php echo ($tipo_get === 'RP') ? 'selected' : ''; ?>>REP-</option>
                          </select>
                        </span>
                        <input type="text" class="form-control" name="cedula_paciente" id="cedula_paciente" placeholder="N° de Cédula" maxlength="20" value="<?php echo htmlspecialchars($cedula); ?>" style="border-top-left-radius:0; border-bottom-left-radius:0; box-sizing:border-box;" <?php echo !empty($cedula) ? 'readonly tabindex="-1"' : ''; ?> required>
                        <span class="input-group-btn" style="flex:0 0 34px;">
                          <button class="btn btn-info" type="button" id="btnAbrirSelectorPaciente" title="Agregar Paciente" style="height:34px; width:34px; padding:0;">
                            <i class="fa fa-plus" style="font-size:12px;">+</i>
                          </button>
                        </span>
                      </div>
                    </div>

                    <div class="col-md-4 col-sm-6 form-group">
                      <p>Nombre del Paciente (*)</p>
                      <input type="text" id="nombre_paciente" class="form-control" name="nombre_paciente" readonly placeholder="Nombre del Paciente" value="<?php echo htmlspecialchars(trim(($datos_paciente['nombre'] ?? '') . ' ' . ($datos_paciente['apellido'] ?? ''))); ?>" required>
                    </div>

                    <div class="col-md-4 col-sm-12 form-group">
                      <p>Fecha de nacimiento / Edad (*):</p>
                      <div class="input-group" style="display:flex; width:100%; table-layout:fixed;">
                        <input type="date" class="form-control" id="fecha_nacimiento_paciente" name="fecha_nacimiento_paciente" max="<?php echo date('Y-m-d'); ?>" readonly value="<?php echo htmlspecialchars($datos_paciente['fecha_nacimiento'] ?? ''); ?>" style="border-top-right-radius:0; border-bottom-right-radius:0; box-sizing:border-box; width:60%; flex:0 0 60%;">
                        <span class="input-group-addon" style="padding:0; border:none; width:40%; flex:0 0 40%;">
                          <input type="text" class="form-control" id="edad_paciente" name="edad_paciente" readonly placeholder="Edad" style="width:100%; height:34px; text-align:center; background-color:#f4f4f4; font-weight:bold; border-left:none; border-top-left-radius:0; border-bottom-left-radius:0; box-sizing:border-box;">
                        </span>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-sm-4">
                      <p>Médico (*):</p>
                      <?php
                      $id_persona_logueada = $_SESSION['id'] ?? 0;
                      $rol_usuario = $_SESSION['rol'] ?? 0;
                      $es_admin = ($rol_usuario == 1);
                      $id_medico_seleccionado = $id_medico_cita;
                      $bloquear_medico = false;

                      if (empty($id_medico_seleccionado) && !$es_admin) {
                        $stmt_logged = $conexion->prepare("SELECT Id_detalle_medico FROM detalle_medico WHERE Id_persona = ?");
                        $stmt_logged->bind_param("i", $id_persona_logueada);
                        $stmt_logged->execute();
                        $res_logged = $stmt_logged->get_result();
                        if ($row_logged = $res_logged->fetch_assoc()) $id_medico_seleccionado = $row_logged['Id_detalle_medico'];
                        $stmt_logged->close();
                      }
                      if (!empty($id_medico_cita) || (!$es_admin && !empty($id_medico_seleccionado))) $bloquear_medico = true;
                      ?>
                      <select name="medico" id="medico" class="form-control" <?php echo $bloquear_medico ? 'style="pointer-events: none; background-color: #eee;" tabindex="-1"' : ''; ?> required>
                        <option value="">--- Seleccione el médico ---</option>
                        <?php
                        $res_med = $conexion->query("SELECT dm.Id_detalle_medico, p.nombre, p.apellido FROM detalle_medico dm INNER JOIN persona p ON dm.Id_persona = p.id WHERE p.estatus IN (1, 2) AND dm.tipo_medico = 'Interno' ORDER BY p.nombre ASC");
                        while ($m = $res_med->fetch_assoc()) {
                          $id_db = (int)$m['Id_detalle_medico'];
                          echo '<option value="' . $id_db . '" ' . ($id_db === (int)$id_medico_seleccionado ? 'selected' : '') . '>' . htmlspecialchars($m['nombre'] . ' ' . $m['apellido']) . '</option>';
                        }
                        ?>
                      </select>
                    </div>
                    <div class="col-sm-4">
                      <p>Fecha de consulta (*):</p>
                      <input type="date" name="fecha_consulta" id="fecha_consulta" class="form-control" value="<?php echo !empty($fecha_cita) ? htmlspecialchars($fecha_cita) : date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" <?php echo !empty($fecha_cita) ? 'readonly' : ''; ?> required>
                    </div>
                    <div class="col-sm-4">
                      <p>Peso (kg)</p>
                      <input type="text" id="peso" class="form-control" name="peso" placeholder="Ej: 70.5">
                    </div>
                  </div>

                  <div class="row" style="margin-top: 15px;">
                    <div class="col-sm-4">
                      <p>Talla (cm)</p>
                      <input type="text" id="talla" class="form-control" name="talla" placeholder="Ej: 172">
                    </div>
                    <div class="col-sm-4">
                      <p>Temperatura (°C)</p>
                      <input type="text" id="temperatura" class="form-control" name="temperatura" placeholder="Ej: 36.7">
                    </div>
                    <div class="col-sm-4">
                      <p>Frec. Cardíaca (lpm)</p>
                      <input type="text" id="frecuencia_cardiaca" class="form-control" name="frecuencia_cardiaca" placeholder="Ej: 72">
                    </div>
                  </div>

                  <div class="row" style="margin-top: 15px;">
                    <div class="col-sm-4">
                      <p>Tensión Arterial (mmHg)</p>
                      <div class="input-group">
                        <input type="text" id="presion_sistolica" class="form-control" name="presion_sistolica" placeholder="Sistólica" inputmode="numeric">
                        <span class="input-group-addon">/</span>
                        <input type="text" id="presion_diastolica" class="form-control" name="presion_diastolica" placeholder="Diastólica" inputmode="numeric">
                      </div>
                    </div>
                    <div class="col-sm-4">
                      <p>Saturación O₂ (%)</p>
                      <input type="text" id="saturacion" class="form-control" name="saturacion" placeholder="Ej: 98">
                    </div>
                    <div class="col-sm-4">
                      <p>Frec. Resp. (rpm)</p>
                      <input type="text" id="frecuencia_respiratoria" class="form-control" name="frecuencia_respiratoria" placeholder="Ej: 16">
                    </div>
                  </div>

                  <div class="row" style="margin-top: 15px;">
                    <div class="col-sm-12">
                      <p>Motivo de la consulta (*)</p>
                      <textarea id="motivo_consulta" name="motivo_consulta" rows="3" class="form-control" placeholder="Motivo principal o queja del paciente" <?php echo !empty($motivo_cita) ? 'readonly' : ''; ?> required><?php echo htmlspecialchars($motivo_cita ?? ''); ?></textarea>
                    </div>
                  </div>

                  <div class="row-actions">
                    <button type="button" class="btn btn-second" data-toggle="modal" data-target="#modalConfirmarRegresoConsulta">Regresar</button>
                    <button type="button" class="btn btn-info" id="btn_ver_historial_medico">
                      <i class="fa fa-notes-medical"></i> Ver Historial Médico
                    </button>
                    <button type="button" class="btn btn-primary next-table" data-table-actual="consulta" data-table-siguiente="diagnostico">Siguiente</button>
                  </div>
                </section>
              </div>

              <!-- ============ TAB HISTORIAL (idéntico a editar) ============ -->
              <div class="tab-pane tab-panel" id="historial" style="margin-bottom:-3%;">
                <section>
                  <div class="row" style="margin-top:15px;">
                    <div class="col-md-6">
                      <p>Consultas:</p>
                      <button type="button" class="btn btn-info btn-block btn-historial" onclick="abrirTimelineCompleta()">
                        <i></i> Visualizar
                      </button>
                    </div>
                    <div class="col-md-6">
                      <p>Antecedentes:</p>
                      <button type="button" class="btn btn-info btn-block btn-historial" onclick="abrirModalAntecedentes()">
                        <i></i> Visualizar
                      </button>
                    </div>
                  </div>

                  <div class="row" style="margin-top:15px;">
                    <div class="col-md-6">
                      <p>Alergias:</p>
                      <button type="button" class="btn btn-info btn-block btn-historial" id="btn_modal_alergias" onclick="abrirModalAlergia()" title="Ninguna conocida">
                        <i></i> Gestionar
                      </button>
                    </div>
                    <div class="col-md-6">
                      <p>Patologías:</p>
                      <button type="button" class="btn btn-info btn-block btn-historial" id="btn_modal_patologias" onclick="abrirModalPatologia()" title="Ninguna registrada">
                        <i></i> Gestionar
                      </button>
                    </div>
                  </div>

                  <div class="row" style="margin-top:15px;">
                    <div class="col-md-6">
                      <p>Discapacidades:</p>
                      <button type="button" class="btn btn-info btn-block btn-historial" id="btn_modal_discapacidad" onclick="abrirModalDiscapacidad()">
                        <i></i> Gestionar
                      </button>
                    </div>
                  </div>

                  <div class="row-actions">
                    <button type="button" class="btn btn-second prev-table" data-table-anterior="consulta">Atrás</button>
                    <button type="button" class="btn btn-primary next-table" data-table-actual="historial" data-table-siguiente="diagnostico">Siguiente</button>
                  </div>
                </section>
              </div>

              <!-- ============ TAB DIAGNÓSTICO ============ -->
              <div class="tab-pane tab-panel" id="diagnostico" style="margin-bottom:-3%;">
                <section>
                  <div class="row">
                    <div class="col-md-12">
                      <label for="lectura_examenes"><b>Lectura de Resultados (Exámenes):</b></label>
                      <textarea class="form-control" name="lectura_examenes" id="lectura_examenes" rows="3" placeholder="Ej: Glucemia 110, Colesterol alto..."></textarea>
                    </div>
                  </div>

                  <div class="row" style="margin-top:15px;">
                    <div class="col-md-6">
                      <label for="id_patologia_dx"><b>Patología (catálogo):</b></label>
                      <select class="form-control" name="id_patologia_dx" id="id_patologia_dx">
                        <option value="">--- Sin patología del catálogo ---</option>
                        <?php
                        $q = $conexion->query("SELECT Id_patologia, nombre_patologia, codigo_cie FROM patologias WHERE estatus = 1 ORDER BY nombre_patologia ASC");
                        while ($p = $q->fetch_assoc()) {
                          echo '<option value="' . (int)$p['Id_patologia'] . '" data-cie="' . htmlspecialchars($p['codigo_cie']) . '">' . htmlspecialchars($p['nombre_patologia']) . '</option>';
                        }
                        ?>
                      </select>
                    </div>
                    <div class="col-md-6">
                      <label for="codigo_cie_dx"><b>Código CIE:</b></label>
                      <input type="text" class="form-control" name="codigo_cie_dx" id="codigo_cie_dx" placeholder="Se completa al elegir una patología" readonly style="background-color:#f4f4f4; cursor:not-allowed;">
                    </div>
                  </div>

                  <div class="row" style="margin-top:15px;">
                    <div class="col-sm-12">
                      <p>Diagnóstico / Descripción libre (*)</p>
                      <textarea name="diagnostico_text" id="diagnostico_text" class="form-control" rows="4" placeholder="Diagnóstico del paciente..." required></textarea>
                    </div>
                  </div>

                  <div class="row" style="margin-top:15px;">
                    <div class="col-sm-6">
                      <p>Evolución / Resultado (*)</p>
                      <select name="estado_paciente" id="estado_paciente" class="form-control" required>
                        <option value="">Seleccione Estado</option>
                        <option value="Primera Consulta">Primera Consulta (Inicio del Caso)</option>
                        <option value="Sano">Mejoría Total (Sano)</option>
                        <option value="En tratamiento">Mejoría Parcial (En tratamiento)</option>
                        <option value="Sin mejoria">Sin Mejoría (Requiere Re-entrada)</option>
                        <option value="Empeoro">Empeoramiento</option>
                      </select>
                    </div>
                    <div class="col-sm-6">
                      <p>¿Reportó Reacción Adversa?</p>
                      <select name="reaccion_adversa" id="reaccion_adversa" class="form-control">
                        <option value="NO">No</option>
                        <option value="SI">Sí, presentó reacción</option>
                      </select>
                    </div>
                  </div>

                  <div class="row" style="margin-top:15px;">
                    <div class="col-sm-6">
                      <p>Detalle de Evolución / Resultados (*)</p>
                      <textarea name="evolucion_resultado" id="evolucion_resultado" class="form-control" rows="1" placeholder="Descripción de la evolución del paciente" required></textarea>
                    </div>
                    <div class="col-sm-6" id="detalle_reaccion" style="display:none;">
                      <p>Detalle de la reacción:</p>
                      <textarea name="detalle_reaccion" id="detalle_reaccion_text" class="form-control" rows="2" placeholder="Describa la reacción"></textarea>
                    </div>
                  </div>

                  <div class="row-actions">
                    <button type="button" class="btn btn-second prev-table" data-table-anterior="consulta">Atrás</button>
                    <button type="button" class="btn btn-primary next-table" data-table-actual="diagnostico" data-table-siguiente="indicacion_tratamiento">Siguiente</button>
                  </div>
                </section>
              </div>

              <!-- ============ TAB TRATAMIENTO ============ -->
              <div class="tab-pane tab-panel" id="indicacion_tratamiento" style="margin-bottom:-3%;">
                <section>
                  <div class="row">
                    <div class="col-md-6">
                      <p>Nuevos Exámenes Solicitados:</p>
                      <button type="button" class="btn btn-info btn-block" id="btn_modal_examenes" data-toggle="modal" data-target="#modalExamenes" data-placement="top" title="Ninguno seleccionado">
                        <i></i> Gestionar Exámenes
                      </button>
                    </div>
                    <div class="col-md-6">
                      <p>Medicamentos (*):</p>
                      <button type="button" class="btn btn-info btn-block btn-error-sombreado" id="medicamento_agg" data-toggle="modal" data-target="#modalSeleccionMedicamentos" data-placement="top" title="Ninguno seleccionado">
                        <i></i> Gestionar Medicamentos
                      </button>
                    </div>
                  </div>

                  <div class="row" style="margin-top:15px;">
                    <div class="col-md-3">
                      <label><b>¿Quién recibe la receta?</b></label>
                      <select name="tipo_receptor" id="tipo_receptor" class="form-control">
                        <option value="Paciente">El paciente</option>
                        <option value="Representante">Representante legal</option>
                        <option value="Familiar">Familiar</option>
                        <option value="Otro">Otro (no registrado)</option>
                      </select>
                    </div>
                    <div class="col-md-3">
                      <label><b>Responsable:</b></label>
                      <input type="text" name="responsable" id="responsable" class="form-control" placeholder="Nombre completo de quien recibe">
                    </div>
                    <div class="col-md-6">
                      <label><b>Parentesco/Relación:</b></label>
                      <select id="parentesco_representante" class="form-control">
                        <option value="Ninguno">Ninguno</option>
                        <option value="Padre">Padre</option>
                        <option value="Madre">Madre</option>
                        <option value="Abuelo/a">Abuelo/a</option>
                        <option value="Abuela">Abuela</option>
                        <option value="Tío/a">Tío/a</option>
                        <option value="Hermano/a">Hermano/a</option>
                        <option value="Hijo/a">Hijo/a</option>
                        <option value="Sobrino/a">Sobrino/a</option>
                        <option value="Cónyuge">Cónyuge</option>
                        <option value="Cuidador/a">Cuidador/a</option>
                        <option value="Apoderado Legal">Apoderado Legal</option>
                        <option value="Tutor Legal">Tutor Legal</option>
                        <option value="Otro">Otro</option>
                        <option value="No aplica">No aplica</option>
                      </select>
                      <input type="hidden" name="parentesco_representante" id="parentesco_representante_hidden" value="Ninguno">
                    </div>
                  </div>

                  <div class="row" id="wrap_cedula_receptor" style="margin-top:15px; display:none;">
                    <div class="col-md-6">
                      <label><b>Cédula del receptor (no registrado):</b></label>
                      <input type="text" name="cedula_receptor" id="cedula_receptor" class="form-control" placeholder="Ej: V-12345678">
                    </div>
                  </div>

                  <div class="row" style="margin-top:15px;">
                    <div class="col-sm-3">
                      <p>Próxima Cita (Fecha):</p>
                      <input type="date" name="fecha_cita" id="fecha_cita" min="<?php echo date('Y-m-d'); ?>" class="form-control">
                    </div>
                    <div class="col-sm-3">
                      <p>Hora:</p>
                      <input type="time" name="hora_cita" id="hora_cita" class="form-control" disabled>
                    </div>
                    <div class="col-md-6">
                      <p>Plan / Tratamiento General (*):</p>
                      <textarea name="tratamiento_indicado" id="tratamiento_indicado" class="form-control" rows="2" required></textarea>
                    </div>
                  </div>

                  <div class="row" style="margin-top:15px;">
                    <div class="col-md-12">
                      <p>Notas y observaciones adicionales:</p>
                      <textarea name="notas_adicionales" id="notas_adicionales" class="form-control" rows="2"></textarea>
                    </div>
                  </div>

                  <div class="row-actions">
                    <button type="button" class="btn btn-second prev-table" data-table-anterior="diagnostico">Atrás</button>
                    <button type="button" class="btn btn-success" id="btn_finalizar_consulta">Guardar</button>
                  </div>
                </section>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<!-- ============ MODALES ============ -->
<div class="modal fade" id="modalSeleccionTipoPaciente" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h4 class="modal-title text-white"><i class="fa fa-users"></i> Tipo de Paciente</h4>
      </div>
      <div class="modal-body text-center">
        <p>¿Qué tipo de paciente desea registrar?</p>
        <button type="button" class="btn btn-primary btn-block mb-2" id="btnAbrirModalAdulto"><i class="fa fa-user"></i> Paciente Adulto (+18)</button>
        <button type="button" class="btn btn-info btn-block" id="btnAbrirModalMenor"><i class="fa fa-child"></i> Paciente Menor (-18)</button>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalSeleccionMedicamentos" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info">
        <h4 class="modal-title"><i class="fa fa-medkit"></i> Seleccionar Medicamentos</h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-xs-9">
            <div class="form-group">
              <label for="selectMedicamentos">Medicamento:</label>
              <div class="input-group">
                <select id="selectMedicamentos" class="form-control select2" style="width: 100%;">
                  <option value="">Cargando medicamentos...</option>
                </select>
                <span class="input-group-btn">
                  <button class="btn btn-info" type="button" id="btnInfoMedicamento" data-toggle="tooltip" title="Detalles" style="height: 34px;">
                    <i><img src="../../recursos/imagenes/iconos/info.png" style="width:15px; height:15px;"></i>
                  </button>
                </span>
              </div>
            </div>
          </div>
          <div class="col-xs-3">
            <div class="form-group">
              <label>&nbsp;</label>
              <button type="button" class="btn btn-info" id="btnAnadirMedicamento" style="width:100%;"><i class="fa fa-plus"></i> Añadir</button>
            </div>
          </div>
        </div>
        <hr style="margin: 10px 0;">
        <div class="row">
          <div class="col-xs-12">
            <h5 style="font-weight: bold; color: #555;">Medicamentos Seleccionados:</h5>
            <div id="contenedorMedicamentosSeleccionados" style="min-height: 80px; border: 2px dashed #ddd; padding: 15px; border-radius: 6px; background-color: #f9f9f9;">
              <p class="text-muted text-center" id="textoVacio">No hay medicamentos añadidos aún.</p>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" id="aplicarSeleccionMedicamentos"><i class="fa fa-check"></i> Aplicar Selección</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalExamenes" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info">
        <h4 class="modal-title"><i class="fa fa-flask"></i> Gestionar Exámenes Solicitados</h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-sm-6">
            <label>Examen:</label>
            <select id="selectExamen" class="form-control"><option value="">--- Seleccione un examen ---</option></select>
          </div>
          <div class="col-sm-3">
            <label>Prioridad:</label>
            <select id="prioridadExamen" class="form-control">
              <option value="Rutina">Rutina</option>
              <option value="Urgente">Urgente</option>
            </select>
          </div>
          <div class="col-sm-3">
            <label>&nbsp;</label>
            <button type="button" class="btn btn-info form-control" id="btn_add_examen"><i class="fa fa-plus"></i> Añadir</button>
          </div>
        </div>
        <div class="form-group" style="margin-top:10px;">
          <label>Indicaciones (opcional):</label>
          <input type="text" id="indicacionesExamen" class="form-control" placeholder="Ej: en ayunas, con contraste">
        </div>
        <hr>
        <div id="contenedor_examenes" style="min-height: 80px; border: 2px dashed #ddd; padding: 15px; border-radius: 6px; background-color: #f9f9f9;">
          <p class="text-muted text-center" id="texto_vacio_examenes">No hay exámenes añadidos aún.</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" id="aplicar_examenes"><i class="fa fa-check"></i> Aplicar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalAlergiaRapida" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary"><h4 class="modal-title"><i class="fa fa-warning"></i> Gestionar Alergias</h4></div>
      <div class="modal-body">
        <div id="existentes_alergias_wrap" style="display:none; margin-bottom:15px;">
          <h5 style="color:#555; border-bottom:1px solid #ddd; padding-bottom:5px;">Alergias registradas previamente</h5>
          <div id="existentes_alergias_list" style="margin-top:8px;"></div>
        </div>
        <h5 style="color:#555; border-bottom:1px solid #ddd; padding-bottom:5px;">Nuevas alergias a registrar</h5>
        <div id="contenedor_filas_alergias" style="margin-top:10px;"></div>
        <div style="margin-top:10px;">
          <button type="button" class="btn btn-success btn-sm" id="add_fila_ale"><i class="fa fa-plus"></i> Añadir otra</button>
          <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalNuevaAlergiaRapida" style="margin-left:5px;"><i class="fa fa-plus-circle"></i> Nueva Alergia</button>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="guardar_alergias_listo">Listo</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalPatologiaRapida" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary"><h4 class="modal-title"><i class="fa fa-heartbeat"></i> Gestionar Patologías</h4></div>
      <div class="modal-body">
        <div id="existentes_patologias_wrap" style="display:none; margin-bottom:15px;">
          <h5 style="color:#555; border-bottom:1px solid #ddd; padding-bottom:5px;">Patologías registradas previamente</h5>
          <div id="existentes_patologias_list" style="margin-top:8px;"></div>
        </div>
        <h5 style="color:#555; border-bottom:1px solid #ddd; padding-bottom:5px;">Nuevas patologías a registrar</h5>
        <div id="contenedor_filas_patologias" style="margin-top:10px;"></div>
        <div style="margin-top:10px;">
          <button type="button" class="btn btn-success btn-sm" id="add_fila_pat"><i class="fa fa-plus"></i> Añadir otra</button>
          <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalNuevaPatologiaRapida" style="margin-left:5px;"><i class="fa fa-plus-circle"></i> Nueva Patología</button>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="guardar_patologias_listo">Listo</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalDiscapacidadRapida" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary"><h4 class="modal-title"><i class="fa fa-wheelchair"></i> Gestionar Discapacidades</h4></div>
      <div class="modal-body">
        <div id="existentes_discapacidades_wrap" style="display:none; margin-bottom:15px;">
          <h5 style="color:#555; border-bottom:1px solid #ddd; padding-bottom:5px;">Discapacidades registradas previamente</h5>
          <div id="existentes_discapacidades_list" style="margin-top:8px;"></div>
        </div>
        <h5 style="color:#555; border-bottom:1px solid #ddd; padding-bottom:5px;">Nuevas discapacidades a registrar</h5>
        <div id="contenedor_filas_discapacidades" style="margin-top:10px;"></div>
        <div style="margin-top:10px;">
          <button type="button" class="btn btn-success btn-sm" id="add_fila_disc"><i class="fa fa-plus"></i> Añadir otra</button>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="guardar_discapacidades_listo">Listo</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalNuevaAlergiaRapida" tabindex="-1" role="dialog" style="z-index: 1080;">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary"><h4 class="modal-title"><i class="fa fa-plus-circle"></i> Nueva Alergia (Catálogo)</h4></div>
      <div class="modal-body">
        <div class="form-group"><label>Nombre de la Alergia (*):</label><input type="text" class="form-control" id="ajax_nombre_ale_nuevo"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btn_guardar_nueva_ale">Guardar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalNuevaPatologiaRapida" tabindex="-1" role="dialog" style="z-index: 1080;">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary"><h4 class="modal-title"><i class="fa fa-plus-circle"></i> Nueva Patología (Catálogo)</h4></div>
      <div class="modal-body">
        <div class="form-group"><label>Nombre (*):</label><input type="text" class="form-control" id="ajax_nombre_pat_nuevo"></div>
        <div class="form-group"><label>Código CIE (Opcional):</label><input type="text" class="form-control" id="ajax_cie_pat_nuevo"></div>
        <div class="form-group"><label>¿Es contagiosa?</label>
          <select class="form-control" id="ajax_contagiosa_pat_nuevo">
            <option value="NO">NO</option>
            <option value="SI">SI</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btn_guardar_nueva_pat">Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- ============ MODALES DE ANTECEDENTES ============ -->
<div class="modal fade" id="modalAntecedentesLista" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary"><h4 class="modal-title"><i class="fa fa-clipboard-list"></i> Antecedentes Médicos del Paciente</h4></div>
      <div class="modal-body">
        <p class="text-muted">Seleccione el tipo de antecedente que desea registrar o revisar:</p>
        <div class="row" id="contenedor-botones-antecedentes"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-success" id="btn_guardar_todos_antecedentes"><i class="fa fa-save"></i> Guardar Selecciones</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalChecklistAntecedente" tabindex="-1" role="dialog" style="z-index: 1080;">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info">
        <h4 class="modal-title" id="titulo-checklist-antecedente"><i class="fa fa-check-square-o"></i> Checklist</h4>
      </div>
      <div class="modal-body">
        <div id="contenedor-checklist-antecedente"></div>
        <div class="form-group" style="margin-top:15px;">
          <label><b>Observaciones adicionales:</b></label>
          <textarea class="form-control" id="observaciones-checklist" rows="2" placeholder="Detalles u observaciones..."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" id="btn_guardar_checklist_antecedente"><i class="fa fa-check"></i> Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- ============ AVISOS Y CONFIRMACIONES ============ -->
<div class="modal fade" id="avisoModalConsulta" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-crimson" id="headerAvisoConsulta">
        <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Aviso Importante</h4>
      </div>
      <div class="modal-body"><div id="avisoTextoConsulta"></div></div>
      <div class="modal-footer"><button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button></div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalGuardarConsulta" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-green"><h4 class="modal-title"><i class="fa fa-save"></i> Confirmación de Guardado</h4></div>
      <div class="modal-body"><p>¿Está seguro de que desea guardar la información de la nueva consulta?</p></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" id="enviar">Aceptar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalConfirmarRegresoConsulta" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-crimson"><h4 class="modal-title"><i class="fa fa-sign-out"></i> Confirmación de Regreso</h4></div>
      <div class="modal-body"><p>Al hacer clic en "Abandonar Formulario", perderá todos los datos no guardados. ¿Desea continuar?</p></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
        <a href="consulta_listado.php" class="btn btn-danger">Abandonar Formulario</a>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalAlertaSalud" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-crimson">
        <h4 class="modal-title text-white"><i class="fas fa-exclamation-circle"></i> Puntos de Atención</h4>
      </div>
      <div class="modal-body"><div id="contenedorAlertas" class="px-3"></div></div>
      <div class="modal-footer"><button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button></div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalTimelineCompleta" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info"><h4 class="modal-title"><i class="fa fa-history"></i> Historial Completo de Consultas</h4></div>
      <div class="modal-body" style="max-height: 450px; overflow-y: auto;">
        <div id="contenedor-timeline-completa">
          <div class="text-center"><i class="fa fa-refresh fa-spin"></i> Cargando historial...</div>
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button></div>
    </div>
  </div>
</div>

<?php if ($error_busqueda) : ?>
  <div class="modal fade" id="modal-error" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson"><h5 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Error de Búsqueda</h5></div>
        <div class="modal-body"><p><?php echo htmlspecialchars($error_busqueda); ?></p></div>
        <div class="modal-footer"><button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button></div>
      </div>
    </div>
  </div>
  <script>$(function() { $('#modal-error').modal('show'); });</script>
<?php endif; ?>

<?php
include('modales/personas/pacientes_agregar_modal.php');
include('modales/personas/pacientes_menores_agregar_modal.php');
include('includes/footer.php');
?>

<script>
  window.LISTA_ALERGIAS_CATALOGO = <?php echo json_encode($lista_alergias_catalogo); ?>;
  window.LISTA_PATOLOGIAS_CATALOGO = <?php echo json_encode($lista_patologias_catalogo); ?>;
  window.LISTA_DISCAPACIDADES_CATALOGO = <?php echo json_encode($lista_discapacidades_catalogo); ?>;
  window.LISTA_EXAMENES_CATALOGO = <?php echo json_encode($lista_examenes_catalogo); ?>;

  const configuracionHorarios = <?php echo json_encode($horarios_db); ?>;
  const cedulaPacienteInput = document.getElementById('cedula_paciente');
  const tipoCedulaPacienteSelect = document.getElementById('tipo_cedula_paciente');
  const nombrePacienteInput = document.getElementById('nombre_paciente');
  const fechaNacimientoPacienteInput = document.getElementById('fecha_nacimiento_paciente');
  const edadPacienteInput = document.getElementById('edad_paciente');

  let alergiasActuales = [];
  let patologiasActuales = [];
  let discapacidadesActuales = [];
  let medicamentosSeleccionados = [];
  let examenesSeleccionados = [];
  let historialUnlocked = false;

  /* ============================================================
     ANTECEDENTES: TEMPLATES (idéntico a editar)
     ============================================================ */
  const ANTECEDENTES_TEMPLATES = {
    perinatales: {
      titulo: '2.a) Perinatales (Menores de 19)',
      icono: 'fa-baby',
      onlyMinor: true,
      items: [
        { label: '2.a.1 Carnet Prenatal' },
        { label: '2.a.2 N° Consultas Prenatal (Z34.9)', input: 'number', placeholder: 'N°' },
        { label: '2.a.3 Trastornos del Embarazo (Z87.5)' },
        { label: '2.a.4 Embarazo a término (Z34.9)' },
        { label: '2.a.5 Parto único espontáneo (O80.9)' },
        { label: '2.a.6 Parto por cesárea (Z87.5)' },
        { label: '2.a.7 Parto por fórceps (Z87.5)' },
        { label: '2.a.8 Complicaciones del parto (Z87.5)' },
        { label: '2.a.9 Complicación del puerperio (Z87.5)' },
        { label: '2.a.10 Trastornos del RN (Z87.6)' },
        { label: '2.a.11 Reanimación (Z87.6)' },
        { label: '2.a.12 Peso al Nacer (Z00.1)', input: 'number', placeholder: 'gr' },
        { label: '2.a.13 Talla al Nacer (Z00.1)', input: 'number', placeholder: 'cm' },
        { label: '2.a.14 Perímetro Cefálico (Z00.1)', input: 'number', placeholder: 'cm' },
        { label: '2.a.15 Lactancia Exclusiva (Z13.2)', input: 'number', placeholder: 'meses' },
        { label: '2.a.16 Lactancia Mixta (Z13.2)', input: 'number', placeholder: 'meses' },
        { label: '2.a.17 Ablactación (Z13.2)', input: 'number', placeholder: 'meses' },
        { label: '2.a.18 Egreso RN Patológico (Z87.6)' },
        { label: '2.a.19 Asfixia (Z87.6)' },
        { label: '2.a.20 Egreso RN Sano (Z87.6)' }
      ]
    },
    familiares: {
      titulo: '2.b) Familiares y Otros Contactos',
      icono: 'fa-users',
      items: [
        { label: '2.b.1 Cáncer (Z80.9)', parentesco: true },
        { label: '2.b.2 Alcoholismo (Z81.1)', parentesco: true },
        { label: '2.b.3 Tabaquismo (Z81.2)', parentesco: true },
        { label: '2.b.4 Drogas (Z81.3)', parentesco: true },
        { label: '2.b.5 T. Mentales (Z81.8)', parentesco: true },
        { label: '2.b.6 Neurológica (Z82.0)', parentesco: true },
        { label: '2.b.7 Varices (Z82.4)', parentesco: true },
        { label: '2.b.8 Cardiopatía (Z82.4)', parentesco: true },
        { label: '2.b.9 HTA Sist. (Z82.4)', parentesco: true },
        { label: '2.b.10 Asma (Z82.5)', parentesco: true },
        { label: '2.b.11 Artritis (Z82.6)', parentesco: true },
        { label: '2.b.12 Malform. Congénita (Z82.7)', parentesco: true },
        { label: '2.b.13 VIH/SIDA (Z83.0)', parentesco: true },
        { label: '2.b.14 Sífilis/ITS (Z83.1)', parentesco: true },
        { label: '2.b.15 Tuberculosis (Z83.1)', parentesco: true },
        { label: '2.b.16 Alergia (Z83.2)', parentesco: true },
        { label: '2.b.17 Diabetes (Z83.3)', parentesco: true },
        { label: '2.b.18 Desnutrición (Z83.4)', parentesco: true },
        { label: '2.b.19 Obesidad (Z83.4)', parentesco: true },
        { label: '2.b.20 Gastropatía (Z83.7)', parentesco: true },
        { label: '2.b.21 Enf. Renal (Z84.1)', parentesco: true },
        { label: '2.b.22 Otras (observaciones)', parentesco: true }
      ]
    },
    gineco: {
      titulo: '2.c) Sexuales y Reproductivos',
      icono: 'fa-venus-mars',
      items: [
        { label: '2.c.1 Telarquia', onlySex: 'Femenino' },
        { label: '2.c.2 Pubarquia', onlySex: 'Femenino' },
        { label: '2.c.3 Menarquia', input: 'number', placeholder: 'edad', onlySex: 'Femenino' },
        { label: '2.c.4 Inicio Prec. Testicular', input: 'number', placeholder: 'edad', onlySex: 'Masculino', onlyMinor: true },
        { label: '2.c.5 Ciclo Menstrual', input: 'text', placeholder: 'ej: 28 días', onlySex: 'Femenino' },
        { label: '2.c.6 Primera Relación Sexual', input: 'number', placeholder: 'edad' },
        { label: '2.c.7 Frecuencia de Rel. Sexual' },
        { label: '2.c.8 Número de Parejas', input: 'number', placeholder: 'N°' },
        { label: '2.c.9 Rel. Sexual Satisfactoria' },
        { label: '2.c.10 Anticoncepción (ACO/DIU/Otro)' },
        { label: '2.c.11 Número de Gestas', input: 'number', placeholder: 'N°', onlySex: 'Femenino' },
        { label: '2.c.12 Número de Partos', input: 'number', placeholder: 'N°', onlySex: 'Femenino' },
        { label: '2.c.13 Número de Cesáreas', input: 'number', placeholder: 'N°', onlySex: 'Femenino' },
        { label: '2.c.14 Número de Abortos', input: 'number', placeholder: 'N°', onlySex: 'Femenino' },
        { label: '2.c.15 Legrado Uterino', onlySex: 'Femenino' },
        { label: '2.c.16 Menopausia / Andropausia' },
        { label: '2.c.17 Otros' }
      ]
    },
    habitos: {
      titulo: '2.c) Estilo y Modo de Vida',
      icono: 'fa-heart',
      items: [
        { label: '2.c.18 Fuma (Z72.0)' },
        { label: '2.c.19 ¿Desde cuándo Fuma? (Z72.0)', input: 'text', placeholder: 'años', dependsOn: '2.c.18 Fuma (Z72.0)' },
        { label: '2.c.20 N° de Cigarrillos al día (Z72.0)', input: 'number', placeholder: 'N°', dependsOn: '2.c.18 Fuma (Z72.0)' },
        { label: '2.c.21 Alcohol (Z72.1)' },
        { label: '2.c.22 Cant. de Alcohol x semana (Z72.1)', input: 'text', placeholder: 'ej: 3 latas', dependsOn: '2.c.21 Alcohol (Z72.1)' },
        { label: '2.c.23 Drogas ilícitas (Z72.2)' },
        { label: '2.c.24 Actividad Física (Z72.3)' },
        { label: '2.c.25 Sedentarismo (Z72.3)' },
        { label: '2.c.26 Manejo del Estrés (Z72.3)' },
        { label: '2.c.46 Chupa Dedo (Z86.5)' },
        { label: '2.c.47 Onicofagia (Z86.5)' },
        { label: '2.c.27 Otros Habitos' }
      ]
    },
    patologicos: {
      titulo: '2.c) Eventos y Situaciones Personales',
      icono: 'fa-stethoscope',
      items: [
        { label: '2.c.28 Violencia Psicológica (Z91.4)' },
        { label: '2.c.29 Violencia Física (Z91.6)' },
        { label: '2.c.30 Violencia Sexual (Z91.6)' },
        { label: '2.c.31 Accidentes (Z91.8)' },
        { label: '2.c.32 Otros eventos (Z91.8)' },
        { label: '2.c.33 Hospitalización (Z91.8)', input: 'text', placeholder: 'año/motivo' },
        { label: '2.c.34 Intervención Quirúrgica (Z98.8)', input: 'text', placeholder: 'año/procedimiento' },
        { label: '2.c.35 Otros' }
      ]
    }
  };

  window.ANTECEDENTES_SELECCIONADOS = window.ANTECEDENTES_SELECCIONADOS || {};

  /* ============================================================
     HELPERS
     ============================================================ */
  function esc(s) {
    if (s === null || s === undefined) return '';
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }
  function getTodayISO() {
    const d = new Date();
    return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
  }

  window.mostrarAviso = function(texto, onHidden) {
    $('#headerAvisoConsulta').removeClass('bg-green').addClass('bg-crimson');
    $('#avisoTextoConsulta').html(texto);
    const $m = $('#avisoModalConsulta');
    $m.off('hidden.bs.modal');
    if (typeof onHidden === 'function') $m.one('hidden.bs.modal', onHidden);
    $m.modal('show');
  };
  window.mostrarAvisoExito = function(texto) {
    $('#headerAvisoConsulta').removeClass('bg-crimson').addClass('bg-green');
    $('#avisoTextoConsulta').html(texto);
    $('#avisoModalConsulta').modal('show');
  };

  /* ============================================================
     ALERTAS
     ============================================================ */
  const estadoAlertas = { alergias: 0, patologias: 0, discapacidades: 0 };
  function actualizarBadgeAlerta(tipo, cantidad) {
    const n = Math.max(0, parseInt(cantidad) || 0);
    estadoAlertas[tipo] = n;
    const total = estadoAlertas.alergias + estadoAlertas.patologias + estadoAlertas.discapacidades;
    const $badge = $('#badge-total-alertas');
    if (total > 0) {
      const detalle = 'Alergias: ' + estadoAlertas.alergias + ' | Patologías: ' + estadoAlertas.patologias + ' | Discapacidades: ' + estadoAlertas.discapacidades;
      $badge.text(total > 999 ? '999+' : String(total)).attr('title', detalle).show();
      $('#li-notificacion-alertas > a').attr('title', 'Alertas del paciente — ' + detalle);
    } else {
      $badge.hide();
      $('#li-notificacion-alertas > a').attr('title', 'Alertas del paciente');
    }
    $('#li-notificacion-alertas').toggle(total > 0);
  }
  window.abrirModalAlertas = function() { $('#modalAlertaSalud').modal('show'); };

  /* ============================================================
     EDAD
     ============================================================ */
  function calcularDiferenciaExacta(nac, ref) {
    let años = ref.getFullYear() - nac.getFullYear();
    let meses = ref.getMonth() - nac.getMonth();
    let días = ref.getDate() - nac.getDate();
    if (días < 0) { meses--; const mp = new Date(ref.getFullYear(), ref.getMonth(), 0); días += mp.getDate(); }
    if (meses < 0) { años--; meses += 12; }
    const total = Math.floor((ref.getTime() - nac.getTime()) / 86400000);
    let texto;
    if (años >= 1) texto = años + (años === 1 ? ' año' : ' años');
    else if (meses >= 1) { texto = meses + (meses === 1 ? ' mes' : ' meses'); if (días > 0) texto += ' y ' + días + (días === 1 ? ' día' : ' días'); }
    else if (días > 0) texto = días + (días === 1 ? ' día' : ' días');
    else texto = '0 días';
    if (total >= 0 && total < 28) texto = 'Recién nacido (' + (total === 0 ? 'hoy' : total + (total === 1 ? ' día' : ' días')) + ')';
    return { años, meses, días, totalDias: total, texto };
  }
  function calcularEdadPaciente() {
    if (!fechaNacimientoPacienteInput || !edadPacienteInput) return;
    const fecha = fechaNacimientoPacienteInput.value;
    if (!fecha) { $(edadPacienteInput).val('').removeAttr('data-edad-anios'); return; }
    const fN = new Date(fecha + 'T00:00:00');
    const hoy = new Date(); hoy.setHours(0, 0, 0, 0);
    if (isNaN(fN.getTime()) || fN > hoy) { $(edadPacienteInput).val(''); return; }
    const calc = calcularDiferenciaExacta(fN, hoy);
    $(edadPacienteInput).val(calc.texto).attr('data-edad-anios', calc.años).css('color', '#333');
  }

  /* ============================================================
     CÉDULA
     ============================================================ */
  function ajustarLongitudCedula(tipo) {
    let maxLength = (tipo === 'V' || tipo === 'E') ? 8 : (tipo === 'RP') ? 10 : 20;
    cedulaPacienteInput.setAttribute('maxlength', maxLength);
    if (cedulaPacienteInput.value.length > maxLength) cedulaPacienteInput.value = cedulaPacienteInput.value.substring(0, maxLength);
  }
  function filtrarCedulaCita() {
    const tipo = tipoCedulaPacienteSelect.value;
    let valor = cedulaPacienteInput.value, nuevo = valor, max = 20;
    if (tipo === 'V' || tipo === 'E') { nuevo = valor.replace(/[^0-9]/g, ''); max = 8; }
    else if (tipo === 'RP') {
      nuevo = valor.replace(/[^0-9]/g, '');
      if (nuevo.length > 8) nuevo = nuevo.substring(0, 8) + '-' + nuevo.substring(8, 9);
      max = 10;
    } else if (tipo === 'PN') { nuevo = valor.replace(/[^0-9a-zA-Z- ]/g, ''); max = 20; }
    cedulaPacienteInput.maxLength = max;
    cedulaPacienteInput.value = nuevo;
  }
  function validarLongitudCedula() {
    const tipo = tipoCedulaPacienteSelect.value;
    const cedula = cedulaPacienteInput.value.trim();
    if (tipo === 'RP') {
      if (!/^[0-9]{8}-[0-9]{1}$/.test(cedula)) return { valido: false, mensaje: 'Formato REP- incorrecto. Debe ser 12345678-1.' };
      return { valido: true };
    }
    const max = (tipo === 'PN') ? 20 : 8;
    if (cedula.length < 1 || cedula.length > max) return { valido: false, mensaje: `La cédula tipo ${tipo} debe tener entre 1 y ${max} caracteres.` };
    return { valido: true };
  }

  /* ============================================================
     ALERTAS: actualizar botones y badges
     ============================================================ */
  function actualizarAlertas(alergias, patologias) {
    alergiasActuales = (alergias || []).map(a => typeof a === 'string' ? a : (a.nombre || a.nombre_alergia || '')).filter(Boolean);
    patologiasActuales = (patologias || []).map(p => typeof p === 'string' ? p : (p.nombre || p.nombre_patologia || '')).filter(Boolean);
    const nA = alergiasActuales.length, nP = patologiasActuales.length;
    const $btnA = $('#btn_modal_alergias'), $btnP = $('#btn_modal_patologias');
    if (nA === 0) $btnA.html('<i></i> Gestionar').attr('data-original-title', 'Ninguna conocida').attr('title', 'Ninguna conocida').tooltip('fixTitle');
    else $btnA.html('<i></i> Total Registradas (' + nA + ')').attr('data-original-title', alergiasActuales.join(', ')).attr('title', alergiasActuales.join(', ')).tooltip('fixTitle');
    if (nP === 0) $btnP.html('<i></i> Gestionar').attr('data-original-title', 'Ninguna registrada').attr('title', 'Ninguna registrada').tooltip('fixTitle');
    else $btnP.html('<i></i> Total Registradas (' + nP + ')').attr('data-original-title', patologiasActuales.join(', ')).attr('title', patologiasActuales.join(', ')).tooltip('fixTitle');
    actualizarBadgeAlerta('alergias', nA);
    actualizarBadgeAlerta('patologias', nP);
  }
  function actualizarBotonDiscapacidad() {
    const $btn = $('#btn_modal_discapacidad');
    if (!$btn.length) return;
    const nD = discapacidadesActuales.length;
    if (nD === 0) $btn.html('<i></i> Gestionar').attr('data-original-title', 'Ninguna registrada').attr('title', 'Ninguna registrada').tooltip('fixTitle');
    else $btn.html('<i></i> Total Registradas (' + nD + ')').attr('data-original-title', discapacidadesActuales.join(', ')).attr('title', discapacidadesActuales.join(', ')).tooltip('fixTitle');
  }

  /* ============================================================
     CARGAR HISTORIAL (incluye antecedentes JSON)
     ============================================================ */
  function cargarHistorialCompleto(cedula) {
    $.ajax({
      url: '../../cfg/ajax/obtener_historial.php?cedula=' + encodeURIComponent(cedula),
      method: 'GET', dataType: 'json',
      success: function(response) {
        if (response.success) {
          const data = response.data;
          actualizarAlertas(data.alergias, data.patologias);
          if (response.id_historial) $('#id_historial_global').val(response.id_historial);

          if (data.antecedentes && Object.keys(data.antecedentes).length > 0) {
            window.ANTECEDENTES_SELECCIONADOS = data.antecedentes;
          } else {
            window.ANTECEDENTES_SELECCIONADOS = {};
          }
        } else {
          actualizarAlertas([], []);
        }
      },
      error: function() { actualizarAlertas([], []); }
    });
  }

  /* ============================================================
     RESPONSABLE / RECEPTOR
     ============================================================ */
  function resetResponsableReceptor() {
    $('#id_representante').val('');
    $('#responsable').val('').prop('readonly', false).css('background-color', '');
    setParentescoSelect('Ninguno');
    $('#parentesco_representante').prop('disabled', true);
    $('#tipo_receptor').val('Paciente').trigger('change');
    $('#wrap_cedula_receptor').hide();
  }
  function setParentescoSelect(valor) {
    const $sel = $('#parentesco_representante');
    const v = (valor || '').toString().trim() || 'Ninguno';
    if ($sel.find('option[value="' + v.replace(/"/g, '\\"') + '"]').length === 0) {
      $sel.append($('<option>').val(v).text(v));
    }
    $sel.val(v);
    $('#parentesco_representante_hidden').val(v);
  }

  $(document).on('change', '#parentesco_representante', function() {
    $('#parentesco_representante_hidden').val($(this).val() || 'Ninguno');
  });
  $(document).on('change', '#tipo_receptor', function() {
    const esOtro = $(this).val() === 'Otro';
    $('#wrap_cedula_receptor').toggle(esOtro);
    if (esOtro) $('#cedula_receptor').prop('required', true);
    else $('#cedula_receptor').prop('required', false).val('');
  });

  /* ============================================================
     VERIFICAR CÉDULA
     ============================================================ */
  function verificarCedulaYObtenerDatosConsulta(tipo, cedula) {
    nombrePacienteInput.value = '';
    fechaNacimientoPacienteInput.value = '';
    edadPacienteInput.value = '';
    $(edadPacienteInput).removeAttr('data-edad-anios');
    resetResponsableReceptor();
    actualizarAlertas([], []);
    window.ANTECEDENTES_SELECCIONADOS = {};

    if (!cedula || cedula.length < 1 || !tipo) return Promise.resolve(false);
    const check = validarLongitudCedula();
    if (!check.valido) {
      $(cedulaPacienteInput).addClass('input-error');
      mostrarAviso('⚠️ ' + esc(check.mensaje));
      return Promise.resolve(false);
    }
    return new Promise(resolve => {
      $.ajax({
        url: '../../cfg/ajax/obtener_paciente.php',
        method: 'POST', dataType: 'json',
        data: { tipo_cedula: tipo, cedula: cedula },
        success: function(response) {
          if (response.existe) {
            nombrePacienteInput.value = response.nombre_completo;
            fechaNacimientoPacienteInput.value = response.fecha_nacimiento;
            $(cedulaPacienteInput).removeClass('input-error');
            calcularEdadPaciente();
            cargarHistorialCompleto(cedula);
            resolverAlertasPaciente(cedula);
            $('#btnAbrirSelectorPaciente').removeClass('btn-info').addClass('btn-default').prop('disabled', true)
              .css({ 'background-color': '#e9ecef', 'border-color': 'gray', 'pointer-events': 'none' });
            resolve(true);
          } else {
            $(cedulaPacienteInput).addClass('input-error');
            nombrePacienteInput.value = '¡Paciente no encontrado!';
            mostrarAviso('⚠️ El paciente con documento <b>' + esc(tipo) + '-' + esc(cedula) + '</b> no está registrado. Use el botón <b>(+)</b> para registrarlo.');
            $('#btnAbrirSelectorPaciente').removeClass('btn-default').addClass('btn-info').prop('disabled', false)
              .css({ 'background-color': '', 'border-color': '', 'pointer-events': '' });
            resolve(false);
          }
        },
        error: function() {
          $(cedulaPacienteInput).addClass('input-error');
          nombrePacienteInput.value = 'Error de conexión.';
          resolve(false);
        }
      });
    });
  }

  /* ============================================================
     RESOLVER ALERTAS
     ============================================================ */
  let _alertasReqSeq = 0;
  function resolverAlertasPaciente(cedula) {
    const mySeq = ++_alertasReqSeq;
    resetResponsableReceptor();
    $.ajax({
      url: '../../cfg/ajax/obtener_alertas_paciente.php',
      type: 'POST', data: { cedula: cedula },
      success: function(response) {
        if (mySeq !== _alertasReqSeq) return;
        try {
          const datos = JSON.parse(response);
          if (Array.isArray(datos.alergias)) alergiasActuales = datos.alergias;
          if (Array.isArray(datos.patologias)) patologiasActuales = datos.patologias;
          if (Array.isArray(datos.discapacidades)) discapacidadesActuales = datos.discapacidades;

          actualizarAlertas(alergiasActuales, patologiasActuales);
          actualizarBadgeAlerta('discapacidades', discapacidadesActuales.length);
          actualizarBotonDiscapacidad();
          $('#contenedorAlertas').html(datos.html_contenido || '');

          const $responsable = $('#responsable');
          const $parentesco = $('#parentesco_representante');
          const nombreRep = (datos.nombre_representante || '').trim();
          const nombrePac = (datos.nombre_paciente || '').trim();

          if (nombreRep !== '') {
            $('#id_representante').val(datos.id_representante || '');
            $responsable.val(nombreRep).prop('readonly', true).css('background-color', '#e9ecef');
            $parentesco.prop('disabled', true);
            setParentescoSelect(datos.parentesco);
            $('#tipo_receptor').val('Representante').trigger('change');
          } else if (nombrePac !== '') {
            $responsable.val(nombrePac).prop('readonly', true).css('background-color', '#e9ecef');
            $parentesco.val('Ninguno').prop('disabled', true);
            setParentescoSelect('Ninguno');
            $('#tipo_receptor').val('Paciente').trigger('change');
          } else {
            $responsable.val('').prop('readonly', false).css('background-color', '');
            $parentesco.val('Ninguno').prop('disabled', true);
            setParentescoSelect('Ninguno');
            $('#tipo_receptor').val('Paciente').trigger('change');
          }
        } catch (e) { console.error('Error procesando alertas:', e); }
      },
      error: function(xhr, status, err) {
        if (mySeq !== _alertasReqSeq) return;
        console.error('Error AJAX alertas:', status, err);
        $('#responsable').val('').prop('readonly', false).css('background-color', '');
        setParentescoSelect('Ninguno');
        $('#parentesco_representante').prop('disabled', true);
      }
    });
  }

  /* ============================================================
     TIMELINE
     ============================================================ */
  function abrirTimelineCompleta() {
    const cedula = $('#cedula_paciente').val();
    if (!cedula) { mostrarAviso('Por favor, ingrese o busque un paciente primero.'); return; }
    $('#modalTimelineCompleta').modal('show');
    $('#contenedor-timeline-completa').html('<div class="text-center"><i class="fa fa-refresh fa-spin"></i> Cargando historial completo...</div>');
    $.ajax({
      url: '../../cfg/ajax/obtener_historial.php',
      type: 'GET', data: { cedula: cedula }, dataType: 'json',
      success: function(response) {
        if (response.success) {
          const consultas = response.data.historial_consultas || [];
          if (consultas.length > 0) {
            let html = '<div class="timeline">';
            consultas.forEach(function(con) {
              html += `<div class="timeline-item" style="border-left: 2px solid #3c8dbc; padding-left: 15px; margin-bottom: 15px; position: relative;">
                  <i class="fa fa-calendar" style="position: absolute; left: -10px; background: #fff; color: #3c8dbc;"></i>
                  <span class="time" style="color: #666; font-size: 12px;"><i class="fa fa-clock-o"></i> ${esc(con.fecha_consulta)}</span>
                  <h4 class="timeline-header" style="margin-top: 0; font-weight: bold; color: #222;">Motivo: ${esc(con.motivo_consulta)}</h4>
                  <div class="timeline-body"><strong>Diagnóstico:</strong> ${esc(con.diagnostico || 'No registrado')}<br><strong>Tratamiento:</strong> ${esc(con.tratamiento_indicado || 'N/A')}</div>
                </div><hr>`;
            });
            html += '</div>';
            $('#contenedor-timeline-completa').html(html);
          } else {
            $('#contenedor-timeline-completa').html('<div class="alert alert-info">El paciente no tiene consultas previas registradas.</div>');
          }
        } else {
          $('#contenedor-timeline-completa').html('<div class="alert alert-danger">⚠️ Error: ' + esc(response.error || 'Desconocido') + '</div>');
        }
      },
      error: function() { $('#contenedor-timeline-completa').html('<div class="alert alert-danger">⚠️ Error crítico al conectar.</div>'); }
    });
  }

  /* ============================================================
     ANTECEDENTES: Modal + Checklists
     ============================================================ */
  window.abrirModalAntecedentes = function() {
    if (!$('#cedula_paciente').val()) { mostrarAviso('No hay paciente cargado.'); return; }
    renderizarBotonesAntecedentes();
    $('#modalAntecedentesLista').modal('show');
  };

  function renderizarBotonesAntecedentes() {
    const $cont = $('#contenedor-botones-antecedentes');
    $cont.empty();
    let edad = parseInt($(edadPacienteInput).attr('data-edad-anios') || 100);
    if (isNaN(edad)) edad = 100;
    let genero = window.PACIENTE_GENERO || 'Masculino';
    let isFemenino = genero.toLowerCase().includes('femen') || genero.toLowerCase() === 'm';

    Object.keys(ANTECEDENTES_TEMPLATES).forEach(function(key) {
      const tmpl = ANTECEDENTES_TEMPLATES[key];
      if (tmpl.onlyMinor && edad >= 19) return;
      const seleccionados = window.ANTECEDENTES_SELECCIONADOS[key] || {};
      const cant = Object.keys(seleccionados).length;
      const badge = cant > 0 ? ` <span class="badge bg-green">${cant}</span>` : '';
      $cont.append(`
        <div class="col-xs-12 col-sm-6 col-md-4" style="margin-bottom:10px;">
          <button type="button" class="btn btn-info btn-block btn-antecedente-tipo" data-key="${key}" style="min-height: 70px; text-align: left; padding: 10px 15px;">
            <i class="fa ${tmpl.icono} fa-2x pull-left" style="margin-top: 5px; margin-right: 10px;"></i>
            <span style="display: block; font-size: 12px; opacity: 0.8; margin-bottom: 3px;">${esc(tmpl.titulo)}</span>
            <span style="font-size: 13px; font-weight: bold; display: inline-block; max-width: 80%; line-height: 1.2;">Gestionar</span>
            ${badge}
          </button>
        </div>
      `);
    });
  }

  $(document).on('click', '.btn-antecedente-tipo', function() {
    const key = $(this).data('key');
    const tmpl = ANTECEDENTES_TEMPLATES[key];
    if (!tmpl) return;
    $('#titulo-checklist-antecedente').html(`<i class="fa ${tmpl.icono}"></i> ${esc(tmpl.titulo)}`);
    const seleccionados = window.ANTECEDENTES_SELECCIONADOS[key] || {};
    let html = '<div class="row">';
    let edad = parseInt($(edadPacienteInput).attr('data-edad-anios') || 100);
    if (isNaN(edad)) edad = 100;
    let genero = window.PACIENTE_GENERO || 'Masculino';
    let isFemenino = genero.toLowerCase().includes('femen') || genero.toLowerCase() === 'm';
    let isMasculino = !isFemenino;

    tmpl.items.forEach(function(item) {
      let label = typeof item === 'string' ? item : item.label;
      let inputType = typeof item === 'object' ? item.input : null;
      let placeholder = typeof item === 'object' ? item.placeholder : '';
      if (item.onlySex === 'Femenino' && !isFemenino) return;
      if (item.onlySex === 'Masculino' && !isMasculino) return;
      if (item.onlyMinor && edad >= 19) return;
      let dependsOn = typeof item === 'object' ? item.dependsOn : null;
      let isChecked = seleccionados[label] !== undefined;
      let valExtra = isChecked && typeof seleccionados[label] === 'string' ? seleccionados[label] : '';
      let checkedStr = isChecked ? 'checked' : '';
      let parentChecked = dependsOn ? (seleccionados[dependsOn] !== undefined) : true;
      let wrapperStyle = (dependsOn && !parentChecked) ? 'display: none;' : '';

      html += `<div class="col-sm-12 col-md-6 parent-col" style="${wrapperStyle}" data-child-of="${dependsOn ? esc(dependsOn) : ''}">
          <div class="chk-antecedente-wrap d-flex align-items-center" style="display:flex; align-items:center; justify-content:space-between;">
            <label style="flex: 1; margin-right: 10px;">
              <input type="checkbox" class="chk-antecedente" value="${esc(label)}" data-depends-on="${dependsOn ? esc(dependsOn) : ''}" ${checkedStr}>
              ${esc(label)}
            </label>`;

      if (typeof item === 'object' && item.parentesco) {
        html += `<select class="form-control input-sm input-extra-antecedente select-parentesco" style="width: 90px; display: ${isChecked ? 'block' : 'none'};">
            <option value="">--</option>
            <option value="Padre" ${valExtra === 'Padre' ? 'selected' : ''}>A (Padre)</option>
            <option value="Madre" ${valExtra === 'Madre' ? 'selected' : ''}>B (Madre)</option>
            <option value="Hermanos" ${valExtra === 'Hermanos' ? 'selected' : ''}>C (Hermanos)</option>
            <option value="Abuelos" ${valExtra === 'Abuelos' ? 'selected' : ''}>D (Abuelos)</option>
            <option value="Otros" ${valExtra === 'Otros' ? 'selected' : ''}>E (Otros)</option>
          </select>`;
      } else if (inputType) {
        html += `<input type="${inputType}" class="form-control input-sm input-extra-antecedente" placeholder="${esc(placeholder)}" value="${esc(valExtra)}" style="width: 90px; display: ${isChecked ? 'block' : 'none'};">`;
      }

      html += `</div></div>`;
    });

    html += '</div>';
    $('#contenedor-checklist-antecedente').html(html).data('key', key);
    const obs = window.ANTECEDENTES_SELECCIONADOS[key + '_obs'] || '';
    $('#observaciones-checklist').val(obs);
    $('#modalChecklistAntecedente').modal('show');
  });

  $(document).on('change', '.chk-antecedente', function() {
    const label = $(this).val();
    const isChecked = this.checked;
    const $input = $(this).closest('.chk-antecedente-wrap').find('.input-extra-antecedente');
    if ($input.length) { $input.toggle(isChecked); if (!isChecked) $input.val(''); }
    const $childCols = $(`.parent-col[data-child-of="${esc(label)}"]`);
    if ($childCols.length > 0) {
      if (isChecked) $childCols.slideDown(200);
      else $childCols.slideUp(200, function() { $(this).find('.chk-antecedente').prop('checked', false).trigger('change'); });
    }
  });

  $('#btn_guardar_checklist_antecedente').on('click', function() {
    const key = $('#contenedor-checklist-antecedente').data('key');
    if (!key) return;
    const seleccionados = {};
    $('#contenedor-checklist-antecedente .chk-antecedente:checked').each(function() {
      const label = $(this).val();
      const $input = $(this).closest('.chk-antecedente-wrap').find('.input-extra-antecedente');
      if ($input.length > 0 && $input.val().trim() !== '') seleccionados[label] = $input.val().trim();
      else seleccionados[label] = true;
    });
    window.ANTECEDENTES_SELECCIONADOS[key] = seleccionados;
    const obs = $('#observaciones-checklist').val().trim();
    if (obs) window.ANTECEDENTES_SELECCIONADOS[key + '_obs'] = obs;
    else delete window.ANTECEDENTES_SELECCIONADOS[key + '_obs'];
    renderizarBotonesAntecedentes();
    $('#modalChecklistAntecedente').modal('hide');
    setTimeout(function() { mostrarAvisoExito('Antecedente actualizado exitosamente.'); }, 350);
  });

  $('#btn_guardar_todos_antecedentes').on('click', function() {
    $('#antecedentes_json').val(JSON.stringify(window.ANTECEDENTES_SELECCIONADOS));
    $('#modalAntecedentesLista').modal('hide');
    setTimeout(function() { mostrarAvisoExito('Antecedentes registrados correctamente. Se guardarán con la consulta.'); }, 350);
  });

  /* ============================================================
     HORARIOS
     ============================================================ */
  function validarHorarioYFechaProgramada() {
    const fechaVal = $('#fecha_cita').val();
    const horaVal = $('#hora_cita').val();
    if (!fechaVal && !horaVal) return { valido: true };
    if (!fechaVal || !horaVal) {
      if (!fechaVal) $('#fecha_cita').addClass('input-error');
      if (!horaVal) $('#hora_cita').addClass('input-error');
      return { valido: false, mensaje: 'Para agendar una cita debe completar Fecha y Hora.' };
    }
    const hoy = new Date(); hoy.setHours(0, 0, 0, 0);
    const fSel = new Date(fechaVal + 'T00:00:00');
    if (isNaN(fSel.getTime()) || fSel <= hoy) {
      $('#fecha_cita').addClass('input-error');
      return { valido: false, mensaje: 'La fecha de la cita debe ser posterior a hoy.' };
    }
    const diaDB = (fSel.getUTCDay() === 0) ? 7 : fSel.getUTCDay();
    const cfg = configuracionHorarios[diaDB];
    if (!cfg || cfg.estatus == 0) {
      $('#fecha_cita').addClass('input-error');
      return { valido: false, mensaje: 'El establecimiento no presta servicio ese día.' };
    }
    const [h, m] = horaVal.split(':').map(Number);
    const [hI, mI] = cfg.hora_inicio.split(':').map(Number);
    const [hF, mF] = cfg.hora_fin.split(':').map(Number);
    if (h * 60 + m < hI * 60 + mI || h * 60 + m > hF * 60 + mF) {
      $('#hora_cita').addClass('input-error');
      return { valido: false, mensaje: `Horario de atención: ${cfg.hora_inicio.substring(0,5)} a ${cfg.hora_fin.substring(0,5)}.` };
    }
    return { valido: true };
  }

  /* ============================================================
     MEDICAMENTOS
     ============================================================ */
  function actualizarTooltipMedicamentos() {
    const $btn = $('#medicamento_agg');
    if (medicamentosSeleccionados.length > 0) {
      const nombres = medicamentosSeleccionados.map(m => m.nombre);
      $btn.removeClass('btn-error-sombreado').attr('data-original-title', nombres.join(', ')).tooltip('fixTitle');
    } else {
      $btn.addClass('btn-error-sombreado').attr('data-original-title', 'Ninguno seleccionado').tooltip('fixTitle');
    }
  }
  function renderizarListaMedicamentos() {
    const $c = $('#contenedorMedicamentosSeleccionados');
    $c.empty();
    if (medicamentosSeleccionados.length === 0) {
      $c.append('<p class="text-muted text-center" id="textoVacio">No hay medicamentos añadidos aún.</p>');
      return;
    }
    medicamentosSeleccionados.forEach((med, i) => {
      $c.append(`
      <div class="panel panel-default med-item" data-index="${i}" style="margin-bottom:10px;">
        <div class="panel-heading" style="padding:8px 12px;">
          <strong>${esc(med.nombre)}</strong>
          <small class="text-muted">${esc(med.detalles || '')}</small>
          <button type="button" class="close btn-quitar-med" data-index="${i}" style="margin-top:-2px;">&times;</button>
        </div>
        <div class="panel-body" style="padding:10px;">
          <div class="row">
            <div class="col-sm-3"><label class="small">Cantidad</label><input type="number" min="1" class="form-control input-sm med-cantidad" data-index="${i}" value="${med.cantidad || 1}"></div>
            <div class="col-sm-3"><label class="small">Dosis</label><input type="text" class="form-control input-sm med-dosis" data-index="${i}" placeholder="Ej: 1 tableta" value="${esc(med.dosis || '')}"></div>
            <div class="col-sm-3"><label class="small">Frecuencia</label><input type="text" class="form-control input-sm med-frecuencia" data-index="${i}" placeholder="Ej: cada 8h" value="${esc(med.frecuencia || '')}"></div>
            <div class="col-sm-3"><label class="small">Vía</label><select class="form-control input-sm med-via" data-index="${i}">
              ${['Oral','Intravenosa','Intramuscular','Subcutanea','Topica','Oftalmica','Otica','Nasal','Rectal','Inhalatoria','Otra']
                .map(v => `<option value="${v}" ${med.via === v ? 'selected' : ''}>${v}</option>`).join('')}
            </select></div>
          </div>
          <div class="row" style="margin-top:6px;">
            <div class="col-sm-4"><label class="small">Duración</label><input type="text" class="form-control input-sm med-duracion" data-index="${i}" placeholder="Ej: 5 días" value="${esc(med.duracion || '')}"></div>
            <div class="col-sm-8"><label class="small">Indicaciones</label><input type="text" class="form-control input-sm med-indicaciones" data-index="${i}" placeholder="Ej: después de comer" value="${esc(med.indicaciones || '')}"></div>
          </div>
        </div>
      </div>`);
    });
  }
  $(document).on('change input', '.med-cantidad, .med-dosis, .med-frecuencia, .med-via, .med-duracion, .med-indicaciones', function() {
    const i = $(this).data('index');
    const field = this.className.match(/med-(\w+)/)[1];
    medicamentosSeleccionados[i][field] = $(this).val();
  });
  function cargarMedicamentosBaseAjax() {
    const $s = $('#selectMedicamentos');
    $s.html('<option value="">Cargando medicamentos...</option>');
    $.ajax({
      url: '../../cfg/ajax/obtener_medicamentos_base.php',
      method: 'GET', dataType: 'json',
      success: function(response) {
        $s.empty().append('<option value="">--- Seleccione un Medicamento ---</option>');
        if (response.success && response.data.length > 0) {
          response.data.forEach(med => {
            const comp = med.componentes || 'Sin componentes';
            const pres = med.nombre_presentacion || 'N/A';
            const via = med.via_aplicacion || 'No definida';
            $s.append(`<option value="${esc(med.Id_descripcion)}" data-nombre="${esc(med.nombre_medicamento)}" data-via="${esc(via)}" data-contenido="${esc(med.contenido_neto || 'No definida')}" data-presentacion="${esc(pres)}" data-componentes="${esc(comp)}">${esc(med.nombre_medicamento)} (${esc(comp)}) [${esc(pres)}]</option>`);
          });
          if ($.fn.select2) { if ($s.hasClass('select2-hidden-accessible')) $s.select2('destroy'); $s.select2(); }
        } else $s.append('<option value="">No hay medicamentos disponibles</option>');
      },
      error: function() { $s.empty().append('<option value="">Error al cargar</option>'); }
    });
  }
  function actualizarTooltipInfoMedicamento() {
    const opt = $('#selectMedicamentos option:selected');
    if (!opt.val()) { $('#btnInfoMedicamento').attr('data-original-title', 'Seleccione para ver detalles').tooltip('fixTitle'); return; }
    const info = `<strong>Vía:</strong> ${esc(opt.data('via') || 'N/A')}<br><strong>Pres:</strong> ${esc(opt.data('presentacion') || 'N/A')}<br><strong>Principio Activo:</strong> ${esc(opt.data('componentes') || 'N/A')}`;
    $('#btnInfoMedicamento').attr('data-original-title', info).tooltip('fixTitle');
  }

  /* ============================================================
     EXÁMENES
     ============================================================ */
  function construirOpcionesExamenes() {
    let html = '<option value="">--- Seleccione un examen ---</option>';
    (window.LISTA_EXAMENES_CATALOGO || []).slice().sort((a, b) => a.nombre.localeCompare(b.nombre)).forEach(e => {
      html += `<option value="${e.id}" data-nombre="${esc(e.nombre)}" data-tipo="${esc(e.tipo)}">[${esc(e.tipo)}] ${esc(e.nombre)}</option>`;
    });
    return html;
  }
  function renderizarListaExamenes() {
    const $c = $('#contenedor_examenes');
    $c.empty();
    if (examenesSeleccionados.length === 0) {
      $c.append('<p class="text-muted text-center" id="texto_vacio_examenes">No hay exámenes añadidos aún.</p>');
      return;
    }
    examenesSeleccionados.forEach((ex, i) => {
      $c.append(`
      <div class="alert alert-info alert-dismissible" style="margin-bottom:5px; padding:8px;">
        <button type="button" class="close btn-quitar-examen" data-index="${i}">&times;</button>
        <strong>${esc(ex.nombre)}</strong>
        <span class="label label-${ex.prioridad === 'Urgente' ? 'danger' : 'default'}" style="margin-left:8px;">${ex.prioridad}</span>
        ${ex.indicaciones ? `<br><small>${esc(ex.indicaciones)}</small>` : ''}
      </div>`);
    });
  }
  $('#modalExamenes').on('show.bs.modal', function() {
    $('#selectExamen').html(construirOpcionesExamenes());
    $('#prioridadExamen').val('Rutina');
    $('#indicacionesExamen').val('');
    renderizarListaExamenes();
  });
  $('#btn_add_examen').off('click').on('click', function() {
    const $opt = $('#selectExamen option:selected');
    const id = $opt.val();
    if (!id) { mostrarAviso('Seleccione un examen.'); return; }
    if (examenesSeleccionados.some(e => e.id === id)) { mostrarAviso('Ese examen ya está en la lista.'); return; }
    examenesSeleccionados.push({
      id, nombre: $opt.data('nombre'), tipo: $opt.data('tipo'),
      prioridad: $('#prioridadExamen').val(),
      indicaciones: $('#indicacionesExamen').val().trim()
    });
    $('#selectExamen').val('');
    $('#indicacionesExamen').val('');
    renderizarListaExamenes();
  });
  $(document).on('click', '.btn-quitar-examen', function() {
    examenesSeleccionados.splice($(this).data('index'), 1);
    renderizarListaExamenes();
  });
  $('#aplicar_examenes').off('click').on('click', function() {
    $('#examenes_json').val(JSON.stringify(examenesSeleccionados));
    $('#btn_modal_examenes').attr('data-original-title',
      examenesSeleccionados.length ? examenesSeleccionados.map(e => e.nombre).join(', ') : 'Ninguno seleccionado'
    ).tooltip('fixTitle');
    $('#modalExamenes').modal('hide');
  });

  /* ============================================================
     ALERGIAS / PATOLOGÍAS / DISCAPACIDADES
     ============================================================ */
  function construirOpcionesAlergias() {
    let html = '<option value="">--- Seleccione una alergia ---</option>';
    (window.LISTA_ALERGIAS_CATALOGO || []).slice().sort((a, b) => a.nombre.localeCompare(b.nombre)).forEach(a => {
      html += `<option value="${a.id}" data-nombre="${esc(a.nombre)}">${esc(a.nombre)}</option>`;
    });
    return html;
  }
  function agregarFilaAlergia() {
    $('#contenedor_filas_alergias').append(`
      <div class="row fila-ale" style="margin-bottom: 10px;">
        <div class="col-sm-7"><select class="form-control select-ale">${construirOpcionesAlergias()}</select></div>
        <div class="col-sm-3"><input type="date" class="form-control input-fecha-ale" max="${getTodayISO()}" value="${getTodayISO()}"></div>
        <div class="col-sm-2"><button type="button" class="btn btn-danger btn-remove-ale"><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></button></div>
      </div>`);
  }
  function construirOpcionesPatologias() {
    let html = '<option value="">--- Seleccione una patología ---</option>';
    (window.LISTA_PATOLOGIAS_CATALOGO || []).slice().sort((a, b) => a.nombre.localeCompare(b.nombre)).forEach(p => {
      html += `<option value="${p.id}" data-nombre="${esc(p.nombre)}">${esc(p.nombre)}</option>`;
    });
    return html;
  }
  function agregarFilaPatologia() {
    $('#contenedor_filas_patologias').append(`
      <div class="row fila-pat" style="margin-bottom: 10px;">
        <div class="col-sm-7"><select class="form-control select-pat">${construirOpcionesPatologias()}</select></div>
        <div class="col-sm-3"><input type="date" class="form-control input-fecha-pat" max="${getTodayISO()}" value="${getTodayISO()}"></div>
        <div class="col-sm-2"><button type="button" class="btn btn-danger btn-remove-pat"><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></button></div>
      </div>`);
  }
  function construirOpcionesDiscapacidades() {
    let html = '<option value="">--- Seleccione una discapacidad ---</option>';
    (window.LISTA_DISCAPACIDADES_CATALOGO || []).slice().sort((a, b) => a.nombre.localeCompare(b.nombre)).forEach(d => {
      html += `<option value="${d.id}" data-nombre="${esc(d.nombre)}">${esc(d.nombre)}</option>`;
    });
    return html;
  }
  function agregarFilaDiscapacidad() {
    $('#contenedor_filas_discapacidades').append(`
      <div class="row fila-disc" style="margin-bottom: 10px;">
        <div class="col-sm-7"><select class="form-control select-disc">${construirOpcionesDiscapacidades()}</select></div>
        <div class="col-sm-3"><input type="date" class="form-control input-fecha-disc" max="${getTodayISO()}" value="${getTodayISO()}"></div>
        <div class="col-sm-2"><button type="button" class="btn btn-danger btn-remove-disc"><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></button></div>
      </div>`);
  }

  window.abrirModalAlergia = function() {
    if (!$('#cedula_paciente').val()) { mostrarAviso('Por favor, cargue o ingrese la cédula de un paciente primero.'); return; }
    if (alergiasActuales.length > 0) {
      $('#existentes_alergias_wrap').show();
      $('#existentes_alergias_list').html(alergiasActuales.map(a => `<span class="label label-info" style="display:inline-block; margin:0 5px 5px 0; padding:6px 10px; font-size:13px;">${esc(a)}</span>`).join(''));
    } else $('#existentes_alergias_wrap').hide();
    $('#contenedor_filas_alergias').empty();
    agregarFilaAlergia();
    $('#modalAlergiaRapida').modal('show');
  };
  window.abrirModalPatologia = function() {
    if (!$('#cedula_paciente').val()) { mostrarAviso('Por favor, cargue o ingrese la cédula de un paciente primero.'); return; }
    if (patologiasActuales.length > 0) {
      $('#existentes_patologias_wrap').show();
      $('#existentes_patologias_list').html(patologiasActuales.map(p => `<span class="label label-info" style="display:inline-block; margin:0 5px 5px 0; padding:6px 10px; font-size:13px;">${esc(p)}</span>`).join(''));
    } else $('#existentes_patologias_wrap').hide();
    $('#contenedor_filas_patologias').empty();
    agregarFilaPatologia();
    $('#modalPatologiaRapida').modal('show');
  };
  window.abrirModalDiscapacidad = function() {
    if (!$('#cedula_paciente').val()) { mostrarAviso('Por favor, cargue o ingrese la cédula de un paciente primero.'); return; }
    if (discapacidadesActuales.length > 0) {
      $('#existentes_discapacidades_wrap').show();
      $('#existentes_discapacidades_list').html(discapacidadesActuales.map(d => `<span class="label label-info" style="display:inline-block; margin:0 5px 5px 0; padding:6px 10px; font-size:13px;">${esc(d)}</span>`).join(''));
    } else $('#existentes_discapacidades_wrap').hide();
    $('#contenedor_filas_discapacidades').empty();
    agregarFilaDiscapacidad();
    $('#modalDiscapacidadRapida').modal('show');
  };

  /* ============================================================
     TABS (idéntico a editar: historial opcional)
     ============================================================ */
  function goToTab(tabName) {
    if (tabName === 'historial') historialUnlocked = true;
    const tabs = ['consulta', 'historial', 'diagnostico', 'indicacion_tratamiento'];
    const idx = tabs.indexOf(tabName);
    if (idx === -1) return;
    $('.tab-panel').removeClass('active');
    $('.nav-tabs li').removeClass('active');
    $('#' + tabName).addClass('active');
    tabs.forEach((name, i) => {
      const $li = $(`.nav-tabs li[data-tab-name="${name}"]`);
      $li.removeClass('disabled-table');
      if (name === 'historial') {
        if (!historialUnlocked) $li.addClass('disabled-table');
      } else if (i > idx) {
        $li.addClass('disabled-table');
      }
    });
    $(`.nav-tabs li[data-tab-name="${tabName}"]`).addClass('active').removeClass('disabled-table');
  }

  /* ============================================================
     VALIDACIONES
     ============================================================ */
  async function validarCamposConsulta(tabName) {
    let errores = [];
    const $tab = $('#' + tabName);
    $tab.find('.input-error').removeClass('input-error');
    function req(sel, label) {
      const $i = $(sel);
      if (!$i.length || !$i.is(':visible')) return;
      const v = ($i.val() || '').toString().trim();
      if (v === '' || v.indexOf('--- Seleccione') === 0) {
        $i.addClass('input-error'); errores.push(label);
      }
    }
    if (tabName === 'consulta') {
      req('#cedula_paciente', 'Cédula');
      req('#nombre_paciente', 'Nombre del paciente');
      req('#medico', 'Médico');
      req('#fecha_consulta', 'Fecha de consulta');
      req('#motivo_consulta', 'Motivo de la consulta');
      if ($('#nombre_paciente').val().includes('no encontrado') || $('#nombre_paciente').val().trim() === '') {
        $('#cedula_paciente').addClass('input-error');
        errores.push('Debe ingresar una cédula válida de un paciente registrado.');
      }
    }
    if (tabName === 'diagnostico') {
      req('#diagnostico_text', 'Diagnóstico');
      req('#estado_paciente', 'Evolución / Resultado');
      req('#evolucion_resultado', 'Detalle de Evolución');
    }
    if (tabName === 'indicacion_tratamiento') {
      if (medicamentosSeleccionados.length === 0) {
        $('#medicamento_agg').addClass('btn-error-sombreado');
        errores.push('Debe seleccionar al menos un medicamento.');
      } else $('#medicamento_agg').removeClass('btn-error-sombreado');
      req('#responsable', 'Nombre de quien recibe la receta');
      if ($('#tipo_receptor').val() === 'Otro') req('#cedula_receptor', 'Cédula del receptor (obligatoria para "Otro")');
      req('#tratamiento_indicado', 'Plan / Tratamiento general');
      const c = validarHorarioYFechaProgramada();
      if (!c.valido) errores.push(c.mensaje);
    }
    if (errores.length > 0) {
      mostrarAviso('Verifique:<ul><li>' + errores.map(esc).join('</li><li>') + '</li></ul>');
      return false;
    }
    return true;
  }

  /* ============================================================
     NUEVO PACIENTE
     ============================================================ */
  function verificarCedulaParaNuevoPaciente(tipo, cedula, callback) {
    if (!cedula || cedula.length < 1) { callback(false); return; }
    $.ajax({
      url: '../../cfg/ajax/obtener_paciente.php',
      method: 'POST', dataType: 'json',
      data: { tipo_cedula: tipo, cedula: cedula },
      success: r => callback(!!r.existe),
      error: () => callback(false)
    });
  }
  function abrirModalTipoPaciente() { $('#modalSeleccionTipoPaciente').modal('show'); }
  function cerrarSelectorTipoPacienteY(callback) {
    const $m = $('#modalSeleccionTipoPaciente');
    $m.removeClass('in').addClass('out');
    if ($m.data('timerCierre')) clearTimeout($m.data('timerCierre'));
    const t = setTimeout(() => {
      $m.modal('hide');
      $m.removeClass('out');
      if (typeof callback === 'function') callback();
    }, 400);
    $m.data('timerCierre', t);
  }
  function abrirModalAgregarAdulto() { cerrarSelectorTipoPacienteY(() => $('#modalAgregarPacienteExterno').modal('show')); }
  function abrirModalAgregarMenor() { cerrarSelectorTipoPacienteY(() => $('#modalAgregarPacienteMenor').modal('show')); }

  /* ============================================================
     INIT
     ============================================================ */
  $(document).ready(function() {
    $('#medicamento_agg, #btn_modal_alergias, #btn_modal_patologias, #btn_modal_examenes, #btn_modal_discapacidad').tooltip({
      container: 'body', placement: 'top'
    });
    $('#btnInfoMedicamento').tooltip({ html: true });
    $('#medicamento_agg').addClass('btn-error-sombreado');
    ajustarLongitudCedula(tipoCedulaPacienteSelect.value);
    calcularEdadPaciente();
    $('#tipo_receptor').trigger('change');

    // Guardar género del paciente para los filtros de antecedentes
    const generoInicial = '<?php echo $datos_paciente['genero'] ?? ''; ?>'.trim();
    if (generoInicial) window.PACIENTE_GENERO = generoInicial;

    if (cedulaPacienteInput.value !== '') {
      verificarCedulaYObtenerDatosConsulta(tipoCedulaPacienteSelect.value, cedulaPacienteInput.value);
    }

    $('#btnAbrirSelectorPaciente').off('click').on('click', function(e) {
      e.preventDefault();
      const tipo = $('#tipo_cedula_paciente').val();
      const cedula = $('#cedula_paciente').val().trim();
      if (cedula === '') { abrirModalTipoPaciente(); return; }
      verificarCedulaParaNuevoPaciente(tipo, cedula, existe => {
        if (existe) mostrarAviso('⚠️ Ese documento ya está registrado.');
        else $('#avisoModalConsulta').one('hidden.bs.modal', () => abrirModalTipoPaciente());
      });
    });
    $('#btnAbrirModalAdulto').off('click').on('click', abrirModalAgregarAdulto);
    $('#btnAbrirModalMenor').off('click').on('click', abrirModalAgregarMenor);

    cedulaPacienteInput.addEventListener('input', function() {
      filtrarCedulaCita();
      if (this.value.length < 7) {
        $(cedulaPacienteInput).removeClass('input-error');
        nombrePacienteInput.value = '';
        fechaNacimientoPacienteInput.value = '';
        edadPacienteInput.value = '';
        $(edadPacienteInput).removeAttr('data-edad-anios');
        resetResponsableReceptor();
        actualizarAlertas([], []);
        window.ANTECEDENTES_SELECCIONADOS = {};
      }
    });
    cedulaPacienteInput.addEventListener('blur', function() {
      const tipo = tipoCedulaPacienteSelect.value;
      const valor = this.value.trim();
      if (!valor) return;
      const c = validarLongitudCedula();
      if (!c.valido) {
        mostrarAviso('⚠️ ' + esc(c.mensaje));
        $(this).addClass('input-error');
        return;
      }
      verificarCedulaYObtenerDatosConsulta(tipo, valor);
    });
    tipoCedulaPacienteSelect.addEventListener('change', function() {
      cedulaPacienteInput.value = '';
      ajustarLongitudCedula(this.value);
      filtrarCedulaCita();
      $(cedulaPacienteInput).removeClass('input-error');
      $(nombrePacienteInput).val('');
      fechaNacimientoPacienteInput.value = '';
      edadPacienteInput.value = '';
      $(edadPacienteInput).removeAttr('data-edad-anios');
      resetResponsableReceptor();
      actualizarAlertas([], []);
      window.ANTECEDENTES_SELECCIONADOS = {};
      $('#btnAbrirSelectorPaciente').removeClass('btn-default').addClass('btn-info').prop('disabled', false)
        .css({ 'background-color': '', 'border-color': '', 'pointer-events': '' });
    });

    ['motivo_consulta', 'responsable'].forEach(id => {
      const el = document.getElementById(id);
      if (!el) return;
      el.addEventListener('input', function() { this.value = this.value.replace(/[0-9]/g, ''); });
    });
    $('#peso, #temperatura').on('input', function() { this.value = this.value.replace(/[^0-9.]/g, ''); });
    $('#talla, #frecuencia_cardiaca, #saturacion, #frecuencia_respiratoria').on('input', function() { this.value = this.value.replace(/[^0-9]/g, ''); });
    $('#presion_sistolica, #presion_diastolica').on('input', function() { this.value = this.value.replace(/[^0-9]/g, ''); });

    $('#reaccion_adversa').on('change', function() {
      $('#detalle_reaccion').css('display', this.value === 'SI' ? 'block' : 'none');
    });
    $('#id_patologia_dx').on('change', function() {
      const $opt = $(this).find('option:selected');
      $('#codigo_cie_dx').val($opt.data('cie') || '');
    });
    $('#estado_paciente').on('change', function() {
      if (this.value === 'Primera Consulta') $('#evolucion_resultado').val('Paciente acude por primera vez. Se inicia protocolo.');
      else $('#evolucion_resultado').val('');
    });
    $('#fecha_consulta').on('change', function() {
      if (this.value > getTodayISO()) { mostrarAviso('La fecha no puede ser mayor a hoy.'); this.value = getTodayISO(); }
    });
    $('#fecha_cita').on('change', function() {
      if (!this.value) { $('#hora_cita').val('').prop('disabled', true); return; }
      const f = new Date(this.value + 'T00:00:00');
      const diaDB = (f.getUTCDay() === 0) ? 7 : f.getUTCDay();
      const cfg = configuracionHorarios[diaDB];
      if (!cfg || cfg.estatus == 0) {
        mostrarAviso('⚠️ El establecimiento no presta servicio ese día.');
        this.value = ''; $('#hora_cita').val('').prop('disabled', true); $(this).addClass('input-error');
        return;
      }
      $('#hora_cita').prop('disabled', false).attr('min', cfg.hora_inicio.substring(0, 5)).attr('max', cfg.hora_fin.substring(0, 5));
      $(this).removeClass('input-error');
      if ($('#hora_cita').val()) $('#hora_cita').trigger('change');
    });
    $('#hora_cita').on('change', function() {
      const hv = this.value, fv = $('#fecha_cita').val();
      if (!hv || !fv) return;
      const c = validarHorarioYFechaProgramada();
      if (!c.valido) { mostrarAviso('⚠️ ' + esc(c.mensaje)); $(this).val('').addClass('input-error'); }
      else $(this).removeClass('input-error');
    });

    /* ===== NAVEGACIÓN ===== */
    $('.next-table').off('click').on('click', async function() {
      const $btn = $(this);
      const tabActual = $btn.data('table-actual');
      const tabSig = $btn.data('table-siguiente');

      if (tabActual === 'consulta') {
        const cedula = $('#cedula_paciente').val().trim();
        const tipo = $('#tipo_cedula_paciente').val();
        const nombreActual = nombrePacienteInput.value.trim();
        const yaCargado = nombreActual !== '' && !nombreActual.includes('no encontrado') && !nombreActual.includes('Error');
        if (cedula.length >= 1 && !yaCargado) {
          $btn.prop('disabled', true).text('Verificando...');
          await verificarCedulaYObtenerDatosConsulta(tipo, cedula);
          $btn.prop('disabled', false).text('Siguiente');
        }
      }

      const ok = await validarCamposConsulta(tabActual);
      if (!ok) return;
      $('#' + tabActual).find('.input-error').removeClass('input-error');
      goToTab(tabSig);
    });
    $('.prev-table').off('click').on('click', function() {
      $('.tab-panel.active').find('.input-error').removeClass('input-error');
      goToTab($(this).data('table-anterior'));
    });

    /* ===== VER HISTORIAL DESDE TAB CONSULTA ===== */
    $('#btn_ver_historial_medico').off('click').on('click', async function() {
      const ok = await validarCamposConsulta('consulta');
      if (!ok) return;
      goToTab('historial');
    });

    /* ===== GUARDAR ===== */
    $('#btn_finalizar_consulta').off('click').on('click', async function() {
      const ok = await validarCamposConsulta('indicacion_tratamiento');
      if (!ok) { goToTab('indicacion_tratamiento'); return; }
      $('#enviar').prop('disabled', false).text('Aceptar');
      $('#modalGuardarConsulta').modal('show');
    });
    $('#enviar').off('click').on('click', function() {
      $(this).prop('disabled', true).text('Procesando...');
      $('#formularioConsulta').trigger('submit');
    });
    $('#formularioConsulta').on('submit', function() {
      $('#medicamentos_json').val(JSON.stringify(medicamentosSeleccionados));
      $('#examenes_json').val(JSON.stringify(examenesSeleccionados));
      $('#antecedentes_json').val(JSON.stringify(window.ANTECEDENTES_SELECCIONADOS));
      $('#parentesco_representante_hidden').val($('#parentesco_representante').val() || 'Ninguno');
      return true;
    });

    /* ===== MEDICAMENTOS ===== */
    $('#modalSeleccionMedicamentos').on('show.bs.modal', cargarMedicamentosBaseAjax);
    $('#selectMedicamentos').on('change', actualizarTooltipInfoMedicamento);
    $('#btnAnadirMedicamento').off('click').on('click', function() {
      const $s = $('#selectMedicamentos');
      const selected = $s.find('option:selected');
      const id = $s.val();
      if (!id) { mostrarAviso('Seleccione un medicamento.'); return; }
      if (medicamentosSeleccionados.some(m => m.id === id)) { mostrarAviso('Ya agregado.'); return; }
      medicamentosSeleccionados.push({
        id, nombre: selected.data('nombre') || selected.text(),
        detalles: (selected.data('via') || '') + ' - ' + (selected.data('contenido') || ''),
        tipo: selected.data('presentacion') || '',
        cantidad: 1, dosis: '', frecuencia: '', via: 'Oral', duracion: '', indicaciones: ''
      });
      renderizarListaMedicamentos();
      $s.val('').trigger('change');
      actualizarTooltipMedicamentos();
    });
    $(document).on('click', '.btn-quitar-med', function() {
      medicamentosSeleccionados.splice($(this).data('index'), 1);
      renderizarListaMedicamentos();
      actualizarTooltipMedicamentos();
    });
    $('#aplicarSeleccionMedicamentos').off('click').on('click', function() {
      $('#medicamentos_json').val(JSON.stringify(medicamentosSeleccionados));
      actualizarTooltipMedicamentos();
      $('#modalSeleccionMedicamentos').modal('hide');
      if (medicamentosSeleccionados.length > 0) $('#medicamento_agg').removeClass('btn-error-sombreado');
    });

    /* ===== ALERGIAS ===== */
    $('#add_fila_ale').off('click').on('click', function(e) {
      e.preventDefault();
      const $ult = $('.fila-ale').last();
      if ($ult.length && !$ult.find('.select-ale').val()) { mostrarAviso('Seleccione la alergia actual antes de añadir otra.'); return; }
      agregarFilaAlergia();
    });
    $(document).on('click', '.btn-remove-ale', function() {
      const $fila = $(this).closest('.fila-ale');
      const $sel = $fila.find('.select-ale'), $fecha = $fila.find('.input-fecha-ale');
      if ($sel.val() || $fecha.val()) { $sel.val(''); $fecha.val(getTodayISO()); return; }
      if ($('.fila-ale').length <= 1) return;
      $fila.remove();
    });
    $(document).on('change', '.select-ale', function() {
      const v = $(this).val();
      if (!v) return;
      if ($('.select-ale').filter(function() { return $(this).val() === v; }).length > 1) {
        mostrarAviso('Esa alergia ya fue seleccionada.'); $(this).val('');
      }
    });
    $('#guardar_alergias_listo').off('click').on('click', function() {
      const cedula = $('#cedula_paciente').val();
      const idHist = $('#id_historial_global').val();
      const filas = [];
      let hayVacia = false;
      $('.fila-ale').each(function() {
        const id = $(this).find('.select-ale').val();
        const fecha = $(this).find('.input-fecha-ale').val();
        if (!id) { if ($('.fila-ale').length > 1) hayVacia = true; return; }
        if (!fecha) { hayVacia = true; return; }
        filas.push({ id, fecha });
      });
      if (hayVacia) { mostrarAviso('Complete o elimine las filas vacías.'); return; }
      if (filas.length === 0) { $('#modalAlergiaRapida').modal('hide'); return; }
      const $btn = $(this);
      $btn.prop('disabled', true).text('Guardando...');
      let pend = filas.length, ok = 0, err = 0, mensajesError = [];
      filas.forEach(f => {
        $.ajax({
          url: '../../cfg/ajax/actualizar_patologias_alergias.php',
          type: 'POST',
          data: { accion: 'vincular_alergia', id_item: f.id, fecha: f.fecha, cedula: cedula, id_historial_global: idHist },
          success: function(r) {
            if (r.status === 'success' || r.status === 'ok') ok++;
            else { err++; const msg = (r && r.message) ? r.message : 'No se pudo guardar.'; if (!mensajesError.includes(msg)) mensajesError.push(msg); }
            fin();
          },
          error: function() { err++; const msg = 'Error de conexión.'; if (!mensajesError.includes(msg)) mensajesError.push(msg); fin(); }
        });
      });
      function fin() {
        pend--;
        if (pend > 0) return;
        $btn.prop('disabled', false).text('Listo');
        $('#modalAlergiaRapida').modal('hide');
        if (err === 0) mostrarAvisoExito(ok + ' alergia(s) guardada(s) correctamente.');
        else if (ok === 0) mostrarAviso('<b>No se pudo guardar:</b><br><br>' + mensajesError.map(m => '• ' + esc(m)).join('<br>'));
        else mostrarAviso('<b>' + ok + ' guardada(s) correctamente.</b><br><br><b>Avisos:</b><br>' + mensajesError.map(m => '• ' + esc(m)).join('<br>'));
        resolverAlertasPaciente(cedula);
        cargarHistorialCompleto(cedula);
      }
    });

    /* ===== PATOLOGÍAS ===== */
    $('#add_fila_pat').off('click').on('click', function(e) {
      e.preventDefault();
      const $ult = $('.fila-pat').last();
      if ($ult.length && !$ult.find('.select-pat').val()) { mostrarAviso('Seleccione la patología actual antes de añadir otra.'); return; }
      agregarFilaPatologia();
    });
    $(document).on('click', '.btn-remove-pat', function() {
      const $fila = $(this).closest('.fila-pat');
      const $sel = $fila.find('.select-pat'), $fecha = $fila.find('.input-fecha-pat');
      if ($sel.val() || $fecha.val()) { $sel.val(''); $fecha.val(getTodayISO()); return; }
      if ($('.fila-pat').length <= 1) return;
      $fila.remove();
    });
    $(document).on('change', '.select-pat', function() {
      const v = $(this).val();
      if (!v) return;
      if ($('.select-pat').filter(function() { return $(this).val() === v; }).length > 1) {
        mostrarAviso('Esa patología ya fue seleccionada.'); $(this).val('');
      }
    });
    $('#guardar_patologias_listo').off('click').on('click', function() {
      const cedula = $('#cedula_paciente').val();
      const idHist = $('#id_historial_global').val();
      const filas = [];
      let hayVacia = false;
      $('.fila-pat').each(function() {
        const id = $(this).find('.select-pat').val();
        const fecha = $(this).find('.input-fecha-pat').val();
        if (!id) { if ($('.fila-pat').length > 1) hayVacia = true; return; }
        if (!fecha) { hayVacia = true; return; }
        filas.push({ id, fecha });
      });
      if (hayVacia) { mostrarAviso('Complete o elimine las filas vacías.'); return; }
      if (filas.length === 0) { $('#modalPatologiaRapida').modal('hide'); return; }
      const $btn = $(this);
      $btn.prop('disabled', true).text('Guardando...');
      let pend = filas.length, ok = 0, err = 0, mensajesError = [];
      filas.forEach(f => {
        $.ajax({
          url: '../../cfg/ajax/actualizar_patologias_alergias.php',
          type: 'POST',
          data: { accion: 'vincular_patologia', id_item: f.id, fecha: f.fecha, cedula: cedula, id_historial_global: idHist },
          success: function(r) {
            if (r.status === 'success' || r.status === 'ok') ok++;
            else { err++; const msg = (r && r.message) ? r.message : 'No se pudo guardar.'; if (!mensajesError.includes(msg)) mensajesError.push(msg); }
            fin();
          },
          error: function() { err++; const msg = 'Error de conexión.'; if (!mensajesError.includes(msg)) mensajesError.push(msg); fin(); }
        });
      });
      function fin() {
        pend--;
        if (pend > 0) return;
        $btn.prop('disabled', false).text('Listo');
        $('#modalPatologiaRapida').modal('hide');
        if (err === 0) mostrarAvisoExito(ok + ' patología(s) guardada(s) correctamente.');
        else if (ok === 0) mostrarAviso('<b>No se pudo guardar:</b><br><br>' + mensajesError.map(m => '• ' + esc(m)).join('<br>'));
        else mostrarAviso('<b>' + ok + ' guardada(s) correctamente.</b><br><br><b>Avisos:</b><br>' + mensajesError.map(m => '• ' + esc(m)).join('<br>'));
        resolverAlertasPaciente(cedula);
        cargarHistorialCompleto(cedula);
      }
    });

    /* ===== DISCAPACIDADES ===== */
    $('#add_fila_disc').off('click').on('click', function(e) {
      e.preventDefault();
      const $ult = $('.fila-disc').last();
      if ($ult.length && !$ult.find('.select-disc').val()) { mostrarAviso('Seleccione la discapacidad actual antes de añadir otra.'); return; }
      agregarFilaDiscapacidad();
    });
    $(document).on('click', '.btn-remove-disc', function() {
      const $fila = $(this).closest('.fila-disc');
      const $sel = $fila.find('.select-disc'), $fecha = $fila.find('.input-fecha-disc');
      if ($sel.val() || $fecha.val()) { $sel.val(''); $fecha.val(getTodayISO()); return; }
      if ($('.fila-disc').length <= 1) return;
      $fila.remove();
    });
    $(document).on('change', '.select-disc', function() {
      const v = $(this).val();
      if (!v) return;
      if ($('.select-disc').filter(function() { return $(this).val() === v; }).length > 1) {
        mostrarAviso('Esa discapacidad ya fue seleccionada.'); $(this).val('');
      }
    });
    $('#guardar_discapacidades_listo').off('click').on('click', function() {
      const cedula = $('#cedula_paciente').val();
      const idHist = $('#id_historial_global').val();
      const filas = [];
      let hayVacia = false;
      $('.fila-disc').each(function() {
        const id = $(this).find('.select-disc').val();
        const fecha = $(this).find('.input-fecha-disc').val();
        if (!id) { if ($('.fila-disc').length > 1) hayVacia = true; return; }
        if (!fecha) { hayVacia = true; return; }
        filas.push({ id, fecha });
      });
      if (hayVacia) { mostrarAviso('Complete o elimine las filas vacías.'); return; }
      if (filas.length === 0) { $('#modalDiscapacidadRapida').modal('hide'); return; }
      const $btn = $(this);
      $btn.prop('disabled', true).text('Guardando...');
      let pend = filas.length, ok = 0, err = 0, mensajesError = [];
      filas.forEach(f => {
        $.ajax({
          url: '../../cfg/ajax/actualizar_patologias_alergias.php',
          type: 'POST',
          data: { accion: 'vincular_discapacidad', id_item: f.id, fecha: f.fecha, cedula: cedula, id_historial_global: idHist },
          success: function(r) {
            if (r.status === 'success' || r.status === 'ok') ok++;
            else { err++; const msg = (r && r.message) ? r.message : 'No se pudo guardar.'; if (!mensajesError.includes(msg)) mensajesError.push(msg); }
            fin();
          },
          error: function() { err++; const msg = 'Error de conexión.'; if (!mensajesError.includes(msg)) mensajesError.push(msg); fin(); }
        });
      });
      function fin() {
        pend--;
        if (pend > 0) return;
        $btn.prop('disabled', false).text('Listo');
        $('#modalDiscapacidadRapida').modal('hide');
        if (err === 0) mostrarAvisoExito(ok + ' discapacidad(es) guardada(s) correctamente.');
        else if (ok === 0) mostrarAviso('<b>No se pudo guardar:</b><br><br>' + mensajesError.map(m => '• ' + esc(m)).join('<br>'));
        else mostrarAviso('<b>' + ok + ' guardada(s) correctamente.</b><br><br><b>Avisos:</b><br>' + mensajesError.map(m => '• ' + esc(m)).join('<br>'));
        resolverAlertasPaciente(cedula);
        cargarHistorialCompleto(cedula);
      }
    });

    /* ===== NUEVAS ALERGIAS / PATOLOGÍAS ===== */
    $('#btn_guardar_nueva_ale').off('click').on('click', function() {
      const nombre = $('#ajax_nombre_ale_nuevo').val().trim();
      if (!nombre) { mostrarAviso('Ingrese el nombre.'); return; }
      $.ajax({
        url: window.location.href, method: 'POST', dataType: 'json',
        data: { ajax_nueva_alergia: 1, nombre_alergia: nombre },
        success: function(r) {
          if (r.success) {
            window.LISTA_ALERGIAS_CATALOGO.push({ id: parseInt(r.id), nombre: r.nombre });
            $('.select-ale').each(function() {
              const v = $(this).val(); $(this).html(construirOpcionesAlergias()); if (v) $(this).val(v);
            });
            $('#modalNuevaAlergiaRapida').modal('hide');
            $('#ajax_nombre_ale_nuevo').val('');
            const $vacio = $('.fila-ale').filter(function() { return !$(this).find('.select-ale').val(); }).first();
            if ($vacio.length) $vacio.find('.select-ale').val(r.id);
            else { agregarFilaAlergia(); $('.fila-ale').last().find('.select-ale').val(r.id); }
            mostrarAvisoExito('Alergia agregada al catálogo.');
          } else mostrarAviso(r.error || 'Error');
        },
        error: function() { mostrarAviso('Error de conexión.'); }
      });
    });
    $('#btn_guardar_nueva_pat').off('click').on('click', function() {
      const nombre = $('#ajax_nombre_pat_nuevo').val().trim();
      const cie = $('#ajax_cie_pat_nuevo').val().trim();
      const cont = $('#ajax_contagiosa_pat_nuevo').val();
      if (!nombre) { mostrarAviso('Ingrese el nombre.'); return; }
      $.ajax({
        url: window.location.href, method: 'POST', dataType: 'json',
        data: { ajax_nueva_patologia: 1, nombre_patologia: nombre, codigo_cie: cie, enfermedad_contagiosa: cont },
        success: function(r) {
          if (r.success) {
            window.LISTA_PATOLOGIAS_CATALOGO.push({ id: parseInt(r.id), nombre: r.nombre, cie: r.cie || '' });
            $('.select-pat').each(function() {
              const v = $(this).val(); $(this).html(construirOpcionesPatologias()); if (v) $(this).val(v);
            });
            $('#modalNuevaPatologiaRapida').modal('hide');
            $('#ajax_nombre_pat_nuevo').val(''); $('#ajax_cie_pat_nuevo').val('');
            const $vacio = $('.fila-pat').filter(function() { return !$(this).find('.select-pat').val(); }).first();
            if ($vacio.length) $vacio.find('.select-pat').val(r.id);
            else { agregarFilaPatologia(); $('.fila-pat').last().find('.select-pat').val(r.id); }
            mostrarAvisoExito('Patología agregada al catálogo.');
          } else mostrarAviso(r.error || 'Error');
        },
        error: function() { mostrarAviso('Error de conexión.'); }
      });
    });
  });
</script>