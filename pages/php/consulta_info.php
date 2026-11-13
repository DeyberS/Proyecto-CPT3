<?php
session_start();
include("../../cfg/conexion.php");

/* ============================================================
   1) VALIDACIÓN INICIAL → SESSION + REDIRECT
   ============================================================ */
$id_consulta = (int)($_GET['Id'] ?? 0);

if ($id_consulta <= 0) {
    $_SESSION['mensaje_user_error'] = 'Debe seleccionar una consulta válida para ver su información.';
    header("Location: consulta_listado.php");
    exit();
}

if (!isset($conexion)) {
    $_SESSION['mensaje_user_error'] = 'Error de conexión a la base de datos. Intente de nuevo.';
    header("Location: consulta_listado.php");
    exit();
}

/* ============================================================
   2) CARGA DE DATOS
   ============================================================ */
$datos_consulta        = null;
$signos_vitales        = null;
$diagnostico           = null;
$evolucion             = null;
$reaccion_adversa      = null;
$solicitud_med         = null;
$medicamentos_lista    = [];
$examenes_lista        = [];
$nota_cargada          = '';
$nombre_medico         = 'No asignado';
$edad_paciente         = null;

try {
    $safe_id = $conexion->real_escape_string($id_consulta);

    /* --- Consulta principal (solo columnas que existen en `consulta`) --- */
    $sql_consulta = "
        SELECT 
            c.Id_consulta,
            c.fecha_consulta,
            c.motivo_consulta,
            c.tratamiento_indicado,
            c.Id_medico,
            c.Id_paciente,
            p.nombre        AS pac_nombre,
            p.apellido      AS pac_apellido,
            p.tipo_cedula   AS pac_tipo_cedula,
            p.cedula        AS pac_cedula,
            p.fecha_nacimiento,
            h.id_historial
        FROM consulta c
        JOIN historial_medico h ON c.Id_historial = h.id_historial
        JOIN persona p ON h.Id_persona = p.id
        WHERE c.Id_consulta = '$safe_id'
        LIMIT 1
    ";
    $res = $conexion->query($sql_consulta);

    if (!$res || $res->num_rows === 0) {
        $_SESSION['mensaje_user_error'] = "La consulta #$id_consulta no existe o fue eliminada.";
        header("Location: consulta_listado.php");
        exit();
    }
    $datos_consulta = $res->fetch_assoc();

    /* --- Médico --- */
    $id_medico_consulta = (int)$datos_consulta['Id_medico'];
    if ($id_medico_consulta > 0) {
        $sql_med = "SELECT p.nombre, p.apellido
                    FROM detalle_medico dm
                    INNER JOIN persona p ON dm.Id_persona = p.id
                    WHERE dm.Id_detalle_medico = $id_medico_consulta
                    LIMIT 1";
        $r_med = $conexion->query($sql_med);
        if ($r_med && $m = $r_med->fetch_assoc()) {
            $nombre_medico = trim($m['nombre'] . ' ' . $m['apellido']);
        }
    }

    /* --- Nota adicional (última observación del historial) --- */
    $id_historial = (int)$datos_consulta['id_historial'];
    if ($id_historial > 0) {
        $sql_notas = "SELECT observacion
                      FROM observaciones_historial_medico
                      WHERE Id_historial_medico = $id_historial
                      ORDER BY fecha DESC, id DESC
                      LIMIT 1";
        $r_nota = $conexion->query($sql_notas);
        if ($r_nota && $n = $r_nota->fetch_assoc()) {
            $nota_cargada = $n['observacion'];
        }
    }

    /* --- Signos vitales (último registro de la consulta) --- */
    $sql_sv = "SELECT peso, talla, temperatura, presion_sistolica, presion_diastolica,
                      frecuencia_cardiaca, frecuencia_respiratoria, saturacion, fecha_medicion
               FROM signos_vitales_consulta
               WHERE id_consulta = '$safe_id'
               ORDER BY fecha_medicion DESC
               LIMIT 1";
    $r_sv = $conexion->query($sql_sv);
    if ($r_sv && $sv = $r_sv->fetch_assoc()) {
        $signos_vitales = $sv;
    }

    /* --- Diagnóstico principal --- */
    $sql_dx = "SELECT dc.id_patologia, dc.descripcion_libre, dc.codigo_cie,
                      pa.nombre_patologia
               FROM diagnostico_consulta dc
               LEFT JOIN patologias pa ON dc.id_patologia = pa.Id_patologia
               WHERE dc.id_consulta = '$safe_id' AND dc.tipo = 'Principal'
               ORDER BY dc.id_diagnostico DESC
               LIMIT 1";
    $r_dx = $conexion->query($sql_dx);
    if ($r_dx && $dx = $r_dx->fetch_assoc()) {
        $diagnostico = $dx;
    }

    /* --- Evolución --- */
    $sql_ev = "SELECT estado_paciente, evolucion_resultado, lectura_examenes, fecha_registro
               FROM evolucion_consulta
               WHERE id_consulta = '$safe_id'
               ORDER BY fecha_registro DESC
               LIMIT 1";
    $r_ev = $conexion->query($sql_ev);
    if ($r_ev && $ev = $r_ev->fetch_assoc()) {
        $evolucion = $ev;
    }

    /* --- Reacción adversa --- */
    $sql_ra = "SELECT tiene_reaccion, detalle, fecha_registro
               FROM reaccion_adversa_consulta
               WHERE id_consulta = '$safe_id'
               LIMIT 1";
    $r_ra = $conexion->query($sql_ra);
    if ($r_ra && $ra = $r_ra->fetch_assoc()) {
        $reaccion_adversa = $ra;
    }

    /* --- Solicitud de medicamento (receptor) --- */
    $sql_sm = "SELECT tipo_receptor, entregado_a, parentesco_receptor, cedula_receptor, estatus_general, fecha_solicitud
               FROM solicitud_medicamento
               WHERE id_consulta = '$safe_id'
               ORDER BY id_solicitud DESC
               LIMIT 1";
    $r_sm = $conexion->query($sql_sm);
    if ($r_sm && $sm = $r_sm->fetch_assoc()) {
        $solicitud_med = $sm;
    }

    /* --- Medicamentos prescritos (detalle_solicitud) --- */
    $sql_medicamentos = "
        SELECT
            ds.id_detalle,
            ds.cantidad_recetada,
            ds.dosis,
            ds.frecuencia,
            ds.via,
            ds.duracion,
            ds.indicaciones,
            ds.cantidad_entregada,
            ds.estatus_item,
            m.nombre_medicamento,
            GROUP_CONCAT(DISTINCT pa.nombre SEPARATOR ' + ') AS principio_activo,
            ps.nombre_presentacion,
            dm.contenido_neto,
            dm.via_aplicacion,
            l.nombre_laboratorio
        FROM detalle_solicitud ds
        INNER JOIN solicitud_medicamento sm ON ds.id_solicitud = sm.id_solicitud
        INNER JOIN descripcion_medicamento dm ON ds.id_descripcion_medicamento = dm.Id
        INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
        LEFT JOIN presentacion ps ON dm.Id_presentacion = ps.Id_presentacion
        LEFT JOIN detalle_principio_medicamento dpm ON dm.Id = dpm.id_descripcion_medicamento
        LEFT JOIN principio_activo pa ON dpm.id_principio_activo = pa.id_principio_activo
        LEFT JOIN laboratorio l ON dm.Id_laboratorio = l.Id_laboratorio
        WHERE sm.id_consulta = '$safe_id'
        GROUP BY ds.id_detalle
        ORDER BY ds.id_detalle ASC
    ";
    $r_meds = $conexion->query($sql_medicamentos);
    if ($r_meds) {
        while ($row = $r_meds->fetch_assoc()) {
            $medicamentos_lista[] = $row;
        }
    }

    /* --- Exámenes solicitados --- */
    $sql_examenes = "
        SELECT
            dse.id_detalle_examen,
            dse.prioridad,
            dse.indicaciones,
            dse.estado_item,
            e.nombre  AS nombre_examen,
            e.tipo    AS tipo_examen,
            e.codigo  AS codigo_examen
        FROM detalle_solicitud_examen dse
        INNER JOIN solicitud_examen se ON dse.id_solicitud_examen = se.id_solicitud_examen
        INNER JOIN examen_catalogo e ON dse.id_examen = e.id_examen
        WHERE se.id_consulta = '$safe_id'
        ORDER BY dse.id_detalle_examen ASC
    ";
    $r_ex = $conexion->query($sql_examenes);
    if ($r_ex) {
        while ($row = $r_ex->fetch_assoc()) {
            $examenes_lista[] = $row;
        }
    }

    /* --- Edad --- */
    if (!empty($datos_consulta['fecha_nacimiento']) && $datos_consulta['fecha_nacimiento'] !== '0000-00-00') {
        try {
            $fn  = new DateTime($datos_consulta['fecha_nacimiento']);
            $hoy = new DateTime();
            $edad_paciente = $fn->diff($hoy)->y;
        } catch (Exception $e) {
            $edad_paciente = null;
        }
    }
} catch (Exception $e) {
    $_SESSION['mensaje_user_error'] = 'Error al cargar la consulta: ' . $e->getMessage();
    header("Location: consulta_listado.php");
    exit();
}

/* ============================================================
   3) HELPERS DE RENDERIZADO
   ============================================================ */
function v($val, $sufijo = '', $fallback = '—') {
    if ($val === null || $val === '' || $val === '0000-00-00') return $fallback;
    return htmlspecialchars($val) . $sufijo;
}

function sv_vital($sv, $campo) {
    if (!$sv || !isset($sv[$campo]) || $sv[$campo] === null || $sv[$campo] === '') return null;
    return $sv[$campo];
}

/* Combinar presión sistólica y diastólica */
$tension_texto = '';
if ($signos_vitales) {
    $ps = $signos_vitales['presion_sistolica']  ?? null;
    $pd = $signos_vitales['presion_diastolica'] ?? null;
    if ($ps !== null && $pd !== null && $ps !== '' && $pd !== '') {
        $tension_texto = (int)$ps . '/' . (int)$pd;
    } elseif ($ps !== null && $ps !== '') {
        $tension_texto = (int)$ps;
    } elseif ($pd !== null && $pd !== '') {
        $tension_texto = '/' . (int)$pd;
    }
}

/* Reacción adversa: normalizar */
$tiene_reaccion = false;
$detalle_reaccion_txt = '';
if ($reaccion_adversa) {
    $tiene_reaccion = (strtoupper(trim($reaccion_adversa['tiene_reaccion'] ?? '')) === 'SI');
    $detalle_reaccion_txt = $reaccion_adversa['detalle'] ?? '';
}

/* Diagnóstico: armar descripción visible */
$diag_texto_principal = '';
if ($diagnostico) {
    if (!empty($diagnostico['descripcion_libre'])) {
        $diag_texto_principal = $diagnostico['descripcion_libre'];
    } elseif (!empty($diagnostico['nombre_patologia'])) {
        $diag_texto_principal = $diagnostico['nombre_patologia'];
    }
}
$cie_diagnostico = $diagnostico['codigo_cie'] ?? '';

/* ============================================================
   4) INCLUDE HEADER (solo si todo OK)
   ============================================================ */
include('includes/headerNav2.php');
verificarAcceso('Ver Consultas');
?>
<head>
    <title>Consulta Médica - Información</title>
</head>

<style>
    .report-box {
        background: #fff;
        padding: 30px;
        margin: 20px auto;
        border-top: 5px solid #00c0ef;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .info-header {
        border-bottom: 2px solid #f4f4f4;
        margin-bottom: 20px;
        padding-bottom: 10px;
    }

    .vitals-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 20px;
    }

    .vital-card {
        background: #f9f9f9;
        border: 1px solid #eee;
        padding: 10px;
        border-radius: 4px;
        flex: 1 1 130px;
        min-width: 130px;
        text-align: center;
    }

    .vital-card small {
        display: block;
        color: #777;
        font-weight: bold;
        font-size: 11px;
        margin-bottom: 4px;
    }

    .vital-card strong {
        font-size: 16px;
        color: #222;
    }

    .vital-card.empty {
        opacity: .55;
        background: #fbfbfb;
    }

    .doctor-box {
        background: #f0f9ff;
        border: 1px solid #cce5ff;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    .section-title {
        border-left: 4px solid #00c0ef;
        padding-left: 10px;
        margin: 25px 0 12px;
        font-weight: 700;
        color: #333;
    }

    .data-label {
        color: #777;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-bottom: 2px;
        display: block;
    }

    .data-value {
        font-size: 14px;
        color: #222;
    }

    .alert-reaction {
        border-left: 4px solid #d9534f;
        background: #fff5f5;
        padding: 10px 15px;
        border-radius: 4px;
        margin-top: 10px;
    }

    .badge-estado {
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 3px;
        color: #fff;
        margin-left: 6px;
    }

    .badge-Pendiente    { background: #f0ad4e; }
    .badge-Entregado    { background: #00a65a; }
    .badge-Parcialmente { background: #3c8dbc; }
    .badge-Cancelado    { background: #d9534f; }
    .badge-Tomada       { background: #3c8dbc; }
    .badge-Resultado    { background: #00a65a; }

    @media print {
        .no-print { display: none !important; }
        .report-box { box-shadow: none; border: none; margin: 0; padding: 0; }
        .vital-card { break-inside: avoid; }
    }

    .row-actions {
        display: flex !important;
        justify-content: flex-end !important;
        gap: 10px !important;
        margin: 22px 0 15px !important;
        padding: 0 5px !important;
    }

    .row-actions .btn {
        min-width: 110px;
    }
</style>

<div class="content-wrapper">
    <section class="content">

        <div class="report-box">

            <!-- ========== ENCABEZADO ========== -->
            <div class="info-header">
                <div class="row">
                    <div class="col-xs-8">
                        <h3 style="margin:0;">Resumen de Consulta Médica</h3>
                    </div>
                    <div class="col-xs-4 text-right">
                        <b>Fecha:</b> <?= date('d/m/Y', strtotime($datos_consulta['fecha_consulta'])) ?><br>
                        <b>Médico:</b> Dr(a). <?= htmlspecialchars($nombre_medico) ?>
                    </div>
                </div>

                <div class="row" style="margin-top: 15px;">
                    <div class="col-sm-6">
                        <span class="data-label">Paciente</span>
                        <span class="data-value">
                            <b><?= htmlspecialchars($datos_consulta['pac_nombre'] . ' ' . $datos_consulta['pac_apellido']) ?></b>
                        </span>
                    </div>
                    <div class="col-sm-3">
                        <span class="data-label">Cédula</span>
                        <span class="data-value">
                            <?= htmlspecialchars($datos_consulta['pac_tipo_cedula'] . '-' . $datos_consulta['pac_cedula']) ?>
                        </span>
                    </div>
                    <div class="col-sm-3">
                        <span class="data-label">Edad</span>
                        <span class="data-value">
                            <?= $edad_paciente !== null ? $edad_paciente . ' años' : '—' ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- ========== SIGNOS VITALES ========== -->
            <h4 class="section-title"><i><img src="../../recursos/imagenes/iconos/filled/symbols/health@2x.png" style="height: 20px; width:20;"></i> Signos Vitales</h4>
            <div class="vitals-row">
                <div class="vital-card <?= sv_vital($signos_vitales,'peso') === null ? 'empty' : '' ?>">
                    <small>Peso</small><strong><?= v(sv_vital($signos_vitales,'peso'), ' kg') ?></strong>
                </div>
                <div class="vital-card <?= sv_vital($signos_vitales,'talla') === null ? 'empty' : '' ?>">
                    <small>Talla</small><strong><?= v(sv_vital($signos_vitales,'talla'), ' cm') ?></strong>
                </div>
                <div class="vital-card <?= sv_vital($signos_vitales,'temperatura') === null ? 'empty' : '' ?>">
                    <small>Temperatura</small><strong><?= v(sv_vital($signos_vitales,'temperatura'), ' °C') ?></strong>
                </div>
                <div class="vital-card <?= $tension_texto === '' ? 'empty' : '' ?>">
                    <small>Tensión</small><strong><?= v($tension_texto, ' mmHg') ?></strong>
                </div>
                <div class="vital-card <?= sv_vital($signos_vitales,'frecuencia_cardiaca') === null ? 'empty' : '' ?>">
                    <small>Frec. Cardíaca</small><strong><?= v(sv_vital($signos_vitales,'frecuencia_cardiaca'), ' lpm') ?></strong>
                </div>
                <div class="vital-card <?= sv_vital($signos_vitales,'saturacion') === null ? 'empty' : '' ?>">
                    <small>Saturación O₂</small><strong><?= v(sv_vital($signos_vitales,'saturacion'), ' %') ?></strong>
                </div>
                <div class="vital-card <?= sv_vital($signos_vitales,'frecuencia_respiratoria') === null ? 'empty' : '' ?>">
                    <small>Frec. Respiratoria</small><strong><?= v(sv_vital($signos_vitales,'frecuencia_respiratoria'), ' rpm') ?></strong>
                </div>
            </div>

            <!-- ========== DETALLES CLÍNICOS ========== -->
            <h4 class="section-title"><i><img src="../../recursos/imagenes/iconos/filled/symbols/health_data_security@2x.png" style="height: 20px; width:20;"></i> Detalles Clínicos</h4>
            <div class="row">
                <div class="col-md-6">
                    <span class="data-label">Motivo de la consulta</span>
                    <p class="data-value"><?= nl2br(htmlspecialchars($datos_consulta['motivo_consulta'] ?? '—')) ?></p>
                </div>
                <div class="col-md-6">
                    <span class="data-label">Diagnóstico</span>
                    <p class="data-value">
                        <?php if ($diag_texto_principal !== ''): ?>
                            <?= nl2br(htmlspecialchars($diag_texto_principal)) ?>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <?php if ($tiene_reaccion && !empty($detalle_reaccion_txt)): ?>
                <div class="alert-reaction">
                    <b><i class="fa fa-exclamation-triangle"></i> Reacción adversa reportada:</b>
                    <p style="margin: 5px 0 0;"><?= nl2br(htmlspecialchars($detalle_reaccion_txt)) ?></p>
                </div>
            <?php endif; ?>

            <!-- ========== MEDICAMENTOS ========== -->
            <h4 class="section-title">
                <i><img src="../../recursos/imagenes/iconos/filled/medications/medicines@2x.png" style="height: 20px; width:20;"></i> Medicamentos prescritos
                <?php if ($solicitud_med && !empty($solicitud_med['entregado_a'])): ?>
                    <small class="text-muted" style="font-weight: normal;">
                        — Recibido por: <b><?= htmlspecialchars($solicitud_med['entregado_a']) ?></b>
                        <?php if (!empty($solicitud_med['parentesco_receptor'])): ?>
                            <i>(<?= htmlspecialchars($solicitud_med['parentesco_receptor']) ?>)</i>
                        <?php endif; ?>
                    </small>
                <?php endif; ?>
            </h4>

            <table class="table table-bordered table-striped">
                <thead>
                    <tr class="active">
                        <th style="width: 45%;">Medicamento</th>
                        <th style="width: 20%;">Dosis</th>
                        <th style="width: 15%;">Vía</th>
                        <th style="width: 12%;">Duración</th>
                        <th style="width: 8%;">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($medicamentos_lista)): ?>
                        <?php foreach ($medicamentos_lista as $med): ?>
                            <tr>
                                <td>
                                    <b><?= htmlspecialchars($med['nombre_medicamento']) ?></b>
                                    <?php if (!empty($med['principio_activo'])): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($med['principio_activo']) ?></small>
                                    <?php endif; ?>
                                    <?php if (!empty($med['nombre_presentacion']) || !empty($med['contenido_neto'])): ?>
                                        <br><small class="text-muted">
                                            <?= htmlspecialchars(trim(($med['nombre_presentacion'] ?? '') . ' — ' . ($med['contenido_neto'] ?? ''), ' —')) ?>
                                        </small>
                                    <?php endif; ?>
                                    <?php if (!empty($med['dosis']) || !empty($med['frecuencia'])): ?>
                                        <br><small class="text-muted">
                                            <?= htmlspecialchars(trim(($med['dosis'] ?? '') . ' — ' . ($med['frecuencia'] ?? ''), ' —')) ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($med['dosis'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($med['via'] ?? ($med['via_aplicacion'] ?? '—')) ?></td>
                                <td><?= htmlspecialchars($med['duracion'] ?? '—') ?></td>
                                <td>
                                    <?php
                                    $estado_item = $med['estatus_item'] ?? 'Pendiente';
                                    $clase_badge = 'badge-' . str_replace(' ', '', $estado_item);
                                    ?>
                                    <span class="badge-estado <?= $clase_badge ?>">
                                        <?= htmlspecialchars($estado_item) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">No hay medicamentos registrados para esta consulta.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- ========== BOTONES ========== -->
            <div class="row-actions" style="margin-top: 25px;">
                <button type="button" class="btn btn-second" data-toggle="modal" data-target="#modalConfirmarRegreso">
                    <i class="fa fa-arrow-left"></i> Regresar
                </button>
                <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modalDetallesCompletos">
                    <i class="fa fa-eye"></i> Ver más detalles
                </button>
                <a href="../../cfg/consulta_receta_pdf.php?id_consulta=<?= (int)$datos_consulta['Id_consulta'] ?>"
                   target="_blank" class="btn btn-primary">
                    <i class="fa fa-print"></i> Imprimir Récipe
                </a>
            </div>

        </div>
    </section>
</div>

<!-- =========================================================
     MODAL: DETALLES COMPLETOS
     ========================================================= -->
<div class="modal fade" id="modalDetallesCompletos" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h4 class="modal-title"><i class="fa fa-file-text-o"></i> Detalles Adicionales de la Consulta</h4>
            </div>
            <div class="modal-body">

                <div class="row">
                    <div class="col-md-6">
                        <div class="box box-solid box-default">
                            <div class="box-header"><h3 class="box-title">Evolución y Reacción</h3></div>
                            <div class="box-body">
                                <strong><i class="fa fa-line-chart"></i> Estado del paciente:</strong>
                                <p class="text-muted"><?= nl2br(htmlspecialchars($evolucion['estado_paciente'] ?? '—')) ?></p>
                                <hr>
                                <strong><i class="fa fa-line-chart"></i> Evolución / Resultado:</strong>
                                <p class="text-muted"><?= nl2br(htmlspecialchars($evolucion['evolucion_resultado'] ?? '—')) ?></p>
                                <hr>
                                <strong><i class="fa fa-warning"></i> Reacción adversa:</strong>
                                <p class="text-muted">
                                    <?= $tiene_reaccion ? 'Sí reportada' : 'No reportada' ?>
                                    <?php if ($tiene_reaccion && !empty($detalle_reaccion_txt)): ?>
                                        <br><em><?= nl2br(htmlspecialchars($detalle_reaccion_txt)) ?></em>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="box box-solid box-default">
                            <div class="box-header"><h3 class="box-title">Exámenes y Paraclínicos</h3></div>
                            <div class="box-body">
                                <strong><i class="fa fa-flask"></i> Lectura de exámenes:</strong>
                                <p class="text-muted"><?= nl2br(htmlspecialchars($evolucion['lectura_examenes'] ?? '—')) ?></p>
                                <hr>
                                <strong><i class="fa fa-plus-square"></i> Nuevos exámenes solicitados:</strong>
                                <?php if (!empty($examenes_lista)): ?>
                                    <ul style="padding-left: 18px; margin-bottom: 0;">
                                        <?php foreach ($examenes_lista as $ex): ?>
                                            <li class="text-muted" style="margin-bottom: 6px;">
                                                <b><?= htmlspecialchars($ex['nombre_examen']) ?></b>
                                                <small>(<?= htmlspecialchars($ex['tipo_examen']) ?>)</small>
                                                — Prioridad: <?= htmlspecialchars($ex['prioridad']) ?>
                                                <?php if (!empty($ex['indicaciones'])): ?>
                                                    <br><small><em><?= htmlspecialchars($ex['indicaciones']) ?></em></small>
                                                <?php endif; ?>
                                                <?php if (!empty($ex['estado_item'])): ?>
                                                    <br><small>Estado: <?= htmlspecialchars($ex['estado_item']) ?></small>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="text-muted"><em>No se solicitaron exámenes en esta consulta.</em></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="box box-solid box-default">
                            <div class="box-header"><h3 class="box-title">Plan de Tratamiento e Indicaciones</h3></div>
                            <div class="box-body">
                                <p><?= nl2br(htmlspecialchars($datos_consulta['tratamiento_indicado'] ?? '—')) ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($solicitud_med && (!empty($solicitud_med['entregado_a']) || !empty($solicitud_med['cedula_receptor']))): ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="box box-solid box-default">
                                <div class="box-header"><h3 class="box-title">Receptor del Medicamento</h3></div>
                                <div class="box-body">
                                    <p>
                                        <b>Tipo:</b> <?= htmlspecialchars($solicitud_med['tipo_receptor'] ?? 'Paciente') ?><br>
                                        <?php if (!empty($solicitud_med['entregado_a'])): ?>
                                            <b>Nombre:</b> <?= htmlspecialchars($solicitud_med['entregado_a']) ?><br>
                                        <?php endif; ?>
                                        <?php if (!empty($solicitud_med['parentesco_receptor'])): ?>
                                            <b>Parentesco:</b> <?= htmlspecialchars($solicitud_med['parentesco_receptor']) ?><br>
                                        <?php endif; ?>
                                        <?php if (!empty($solicitud_med['cedula_receptor'])): ?>
                                            <b>Cédula:</b> <?= htmlspecialchars($solicitud_med['cedula_receptor']) ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- =========================================================
     MODAL: CONFIRMAR REGRESO
     ========================================================= -->
<div class="modal fade" id="modalConfirmarRegreso" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-crimson">
                <h4 class="modal-title"><i class="fa fa-warning"></i> Confirmación de Regreso</h4>
            </div>
            <div class="modal-body">
                <p>Está a punto de regresar al listado. ¿Desea continuar?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
                <a href="consulta_listado.php" class="btn btn-danger">Regresar al Listado</a>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>