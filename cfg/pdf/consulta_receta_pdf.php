<?php
require_once('../../plugins/vendor/tcpdf/tcpdf.php');

include("../conexion.php");

function handle_error_pdf($mensaje, $sql = '') {
    die("<h1>❌ Error al generar PDF</h1><p>" . htmlspecialchars($mensaje) . "</p><pre>" . htmlspecialchars($sql) . "</pre>");
}

$id_consulta = (int)($_GET['id_consulta'] ?? 0);

if ($id_consulta <= 0 || !isset($conexion)) {
    handle_error_pdf("ID de consulta inválido o error de conexión.");
}

/* ============================================================
   1) CONSULTA PRINCIPAL + PACIENTE + MÉDICO
   ============================================================ */
$sql_consulta = "
    SELECT 
        c.Id_consulta,
        c.fecha_consulta,
        c.motivo_consulta,
        c.tratamiento_indicado,
        p.nombre        AS nombre_paciente,
        p.apellido      AS apellido_paciente,
        p.tipo_cedula   AS tipo_cedula_paciente,
        p.cedula        AS cedula_paciente,
        p.fecha_nacimiento,
        m.nombre        AS nombre_medico,
        m.apellido      AS apellido_medico,
        m.tipo_cedula   AS tipo_cedula_medico,
        m.cedula        AS cedula_medico
    FROM consulta c
    JOIN persona p ON c.Id_paciente = p.id
    JOIN detalle_medico dm ON c.Id_medico = dm.Id_detalle_medico
    JOIN persona m ON dm.Id_persona = m.id
    WHERE c.Id_consulta = $id_consulta
    LIMIT 1;
";

$res_consulta = mysqli_query($conexion, $sql_consulta);
if (!$res_consulta || mysqli_num_rows($res_consulta) === 0) {
    handle_error_pdf("No se encontró la consulta con ID $id_consulta.", $sql_consulta);
}
$datos = mysqli_fetch_assoc($res_consulta);

/* ============================================================
   2) SIGNOS VITALES (último registro)
   ============================================================ */
$sv = null;
$sql_sv = "SELECT peso, talla, temperatura, presion_sistolica, presion_diastolica,
                  frecuencia_cardiaca, frecuencia_respiratoria, saturacion
           FROM signos_vitales_consulta
           WHERE id_consulta = $id_consulta
           ORDER BY fecha_medicion DESC
           LIMIT 1";
$res_sv = mysqli_query($conexion, $sql_sv);
if ($res_sv && $row_sv = mysqli_fetch_assoc($res_sv)) {
    $sv = $row_sv;
}

/* ============================================================
   3) DIAGNÓSTICO PRINCIPAL
   ============================================================ */
$dx = null;
$sql_dx = "SELECT dc.descripcion_libre, dc.codigo_cie,
                  pa.nombre_patologia
           FROM diagnostico_consulta dc
           LEFT JOIN patologias pa ON dc.id_patologia = pa.Id_patologia
           WHERE dc.id_consulta = $id_consulta AND dc.tipo = 'Principal'
           ORDER BY dc.id_diagnostico DESC
           LIMIT 1";
$res_dx = mysqli_query($conexion, $sql_dx);
if ($res_dx && $row_dx = mysqli_fetch_assoc($res_dx)) {
    $dx = $row_dx;
}

/* ============================================================
   4) EVOLUCIÓN
   ============================================================ */
$ev = null;
$sql_ev = "SELECT estado_paciente, evolucion_resultado, lectura_examenes
           FROM evolucion_consulta
           WHERE id_consulta = $id_consulta
           ORDER BY fecha_registro DESC
           LIMIT 1";
$res_ev = mysqli_query($conexion, $sql_ev);
if ($res_ev && $row_ev = mysqli_fetch_assoc($res_ev)) {
    $ev = $row_ev;
}

/* ============================================================
   5) REACCIÓN ADVERSA
   ============================================================ */
$ra = null;
$sql_ra = "SELECT tiene_reaccion, detalle
           FROM reaccion_adversa_consulta
           WHERE id_consulta = $id_consulta
           LIMIT 1";
$res_ra = mysqli_query($conexion, $sql_ra);
if ($res_ra && $row_ra = mysqli_fetch_assoc($res_ra)) {
    $ra = $row_ra;
}

/* ============================================================
   6) SOLICITUD (RECEPTOR DE LA RECETA)
   ============================================================ */
$sm = null;
$sql_sm = "SELECT tipo_receptor, entregado_a, parentesco_receptor, cedula_receptor
           FROM solicitud_medicamento
           WHERE id_consulta = $id_consulta
           ORDER BY id_solicitud DESC
           LIMIT 1";
$res_sm = mysqli_query($conexion, $sql_sm);
if ($res_sm && $row_sm = mysqli_fetch_assoc($res_sm)) {
    $sm = $row_sm;
}

/* ============================================================
   7) MEDICAMENTOS (detalle_solicitud)
   ============================================================ */
$sql_receta = "
    SELECT
        m.nombre_medicamento,
        GROUP_CONCAT(DISTINCT pa.nombre SEPARATOR ' + ') AS principio_activo,
        ps.nombre_presentacion,
        dm.contenido_neto,
        dm.via_aplicacion                            AS via_catalogo,
        CONCAT(dpm.cantidad_unidad_medida, ' ', um.unidad) AS concentracion,
        ds.cantidad_recetada,
        ds.dosis,
        ds.frecuencia,
        ds.via,
        ds.duracion,
        ds.indicaciones,
        ds.estatus_item
    FROM detalle_solicitud ds
    INNER JOIN solicitud_medicamento sm ON ds.id_solicitud = sm.id_solicitud
    INNER JOIN descripcion_medicamento dm ON ds.id_descripcion_medicamento = dm.Id
    INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
    LEFT JOIN presentacion ps ON dm.Id_presentacion = ps.Id_presentacion
    LEFT JOIN detalle_principio_medicamento dpm ON dm.Id = dpm.id_descripcion_medicamento
    LEFT JOIN principio_activo pa ON dpm.id_principio_activo = pa.id_principio_activo
    LEFT JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida
    WHERE sm.id_consulta = $id_consulta
    GROUP BY ds.id_detalle
    ORDER BY ds.id_detalle ASC
";

$res_receta = mysqli_query($conexion, $sql_receta);
$recetas = [];
if ($res_receta) {
    while ($row = mysqli_fetch_assoc($res_receta)) {
        $recetas[] = $row;
    }
}

/* ============================================================
   8) EXÁMENES SOLICITADOS
   ============================================================ */
$sql_ex = "
    SELECT
        e.nombre    AS nombre_examen,
        e.tipo      AS tipo_examen,
        e.codigo    AS codigo_examen,
        dse.prioridad,
        dse.indicaciones,
        dse.estado_item
    FROM detalle_solicitud_examen dse
    INNER JOIN solicitud_examen se ON dse.id_solicitud_examen = se.id_solicitud_examen
    INNER JOIN examen_catalogo e ON dse.id_examen = e.id_examen
    WHERE se.id_consulta = $id_consulta
    ORDER BY dse.id_detalle_examen ASC
";
$res_ex = mysqli_query($conexion, $sql_ex);
$examenes = [];
if ($res_ex) {
    while ($row = mysqli_fetch_assoc($res_ex)) {
        $examenes[] = $row;
    }
}

/* ============================================================
   9) EDAD
   ============================================================ */
$edad = 0;
if (!empty($datos['fecha_nacimiento']) && $datos['fecha_nacimiento'] !== '0000-00-00') {
    $fn  = new DateTime($datos['fecha_nacimiento']);
    $hoy = new DateTime();
    $edad = $fn->diff($hoy)->y;
}

/* ============================================================
   10) SIGNOS VITALES (texto)
   ============================================================ */
$sv_textos = [];
if ($sv) {
    if (!empty($sv['peso']))                   $sv_textos[] = 'Peso: '     . htmlspecialchars($sv['peso']) . ' kg';
    if (!empty($sv['talla']))                  $sv_textos[] = 'Talla: '    . htmlspecialchars($sv['talla']) . ' cm';
    if (!empty($sv['temperatura']))            $sv_textos[] = 'Temp: '     . htmlspecialchars($sv['temperatura']) . ' °C';
    if (!empty($sv['presion_sistolica']) || !empty($sv['presion_diastolica'])) {
        $ps = $sv['presion_sistolica']  ?? '';
        $pd = $sv['presion_diastolica'] ?? '';
        $sv_textos[] = 'Tensión: ' . htmlspecialchars(trim($ps . '/' . $pd, '/')) . ' mmHg';
    }
    if (!empty($sv['frecuencia_cardiaca']))    $sv_textos[] = 'FC: '       . htmlspecialchars($sv['frecuencia_cardiaca']) . ' lpm';
    if (!empty($sv['saturacion']))             $sv_textos[] = 'SatO₂: '    . htmlspecialchars($sv['saturacion']) . ' %';
    if (!empty($sv['frecuencia_respiratoria'])) $sv_textos[] = 'FR: '      . htmlspecialchars($sv['frecuencia_respiratoria']) . ' rpm';
}

$html_signos_vitales = '';
if (count($sv_textos) > 0) {
    $html_signos_vitales = '
        <h3 style="color:#0056b3; border-bottom: 1px solid #0056b3;">Signos Vitales</h3>
        <p style="font-size: 10pt;">' . implode(' &nbsp;|&nbsp; ', $sv_textos) . '</p>
        <br>
    ';
}

/* ============================================================
   11) BLOQUES HTML
   ============================================================ */
$html_header = '
    <h1 style="text-align:center; color:#0056b3;">CENTRO DE SALUD CPT-3</h1>
    <h2 style="text-align:center; border-bottom: 1px solid #333;">RESUMEN DE CONSULTA Y RÉCIPE</h2>
    <p style="text-align:center; font-size: 9pt; color:#666;">Consulta N°: ' . htmlspecialchars($datos['Id_consulta']) . '</p>
    <br>
';

$html_paciente = '
    <table cellpadding="4" cellspacing="0" border="0" style="font-size: 11pt; width: 100%;">
        <tr>
            <td width="50%"><b>Paciente:</b> ' . htmlspecialchars($datos['nombre_paciente'] . ' ' . $datos['apellido_paciente']) . '</td>
            <td width="50%"><b>Fecha de la Consulta:</b> ' . date("d/m/Y", strtotime($datos['fecha_consulta'])) . '</td>
        </tr>
        <tr>
            <td width="50%"><b>Cédula:</b> ' . htmlspecialchars($datos['tipo_cedula_paciente'] . '-' . $datos['cedula_paciente']) . '</td>
            <td width="50%"><b>Edad:</b> ' . $edad . ' años</td>
        </tr>
    </table>
    <br>
';

/* --- Diagnóstico y motivo --- */
$html_diagnostico = '<h3 style="color:#0056b3; border-bottom: 1px solid #0056b3;">Diagnóstico y Motivo</h3>';

if (!empty(trim($datos['motivo_consulta']))) {
    $html_diagnostico .= '<p style="font-size: 10pt;"><b>Motivo de la Consulta:</b> ' . nl2br(htmlspecialchars($datos['motivo_consulta'])) . '</p>';
}

if ($dx) {
    $desc_dx = !empty($dx['descripcion_libre'])
        ? $dx['descripcion_libre']
        : ($dx['nombre_patologia'] ?? '');
    if ($desc_dx !== '') {
        $linea = '<b>Diagnóstico:</b> ' . nl2br(htmlspecialchars($desc_dx));
        if (!empty($dx['codigo_cie'])) {
            $linea .= ' <span style="font-size:9pt; color:#666;">(CIE: ' . htmlspecialchars($dx['codigo_cie']) . ')</span>';
        }
        $html_diagnostico .= '<p style="font-size: 10pt;">' . $linea . '</p>';
    }
}

if ($ev) {
    if (!empty(trim($ev['estado_paciente']))) {
        $html_diagnostico .= '<p style="font-size: 10pt;"><b>Estado del Paciente:</b> ' . htmlspecialchars($ev['estado_paciente']) . '</p>';
    }
    if (!empty(trim($ev['evolucion_resultado']))) {
        $html_diagnostico .= '<p style="font-size: 10pt;"><b>Evolución / Resultado:</b> ' . nl2br(htmlspecialchars($ev['evolucion_resultado'])) . '</p>';
    }
    if (!empty(trim($ev['lectura_examenes']))) {
        $html_diagnostico .= '<p style="font-size: 10pt;"><b>Lectura de Exámenes:</b> ' . nl2br(htmlspecialchars($ev['lectura_examenes'])) . '</p>';
    }
}

if ($ra && strtoupper(trim($ra['tiene_reaccion'] ?? '')) === 'SI' && !empty(trim($ra['detalle'] ?? ''))) {
    $html_diagnostico .= '<p style="font-size: 10pt; color:#a00;"><b>⚠ Reacción Adversa:</b> ' . nl2br(htmlspecialchars($ra['detalle'])) . '</p>';
}

$html_diagnostico .= '<br>';

/* --- Récipe médico --- */
$html_receta = '<h3 style="color:#0056b3; border-bottom: 1px solid #0056b3;">Récipe Médico</h3>';

if (!empty($recetas)) {
    $html_receta .= '
        <table border="1" cellpadding="5" style="width: 100%; border-collapse: collapse; font-size: 9.5pt;">
            <thead>
                <tr style="background-color: #f0f0f0;">
                    <th width="38%">Medicamento</th>
                    <th width="15%">Dosis</th>
                    <th width="17%">Frecuencia</th>
                    <th width="15%">Vía</th>
                    <th width="15%">Duración</th>
                </tr>
            </thead>
            <tbody>';

    foreach ($recetas as $r) {
        $med_linea = '<b>' . htmlspecialchars($r['nombre_medicamento']) . '</b>';
        if (!empty($r['principio_activo'])) {
            $med_linea .= '<br><span style="font-size:8.5pt; color:#555;">' . htmlspecialchars($r['principio_activo']) . '</span>';
        }
        if (!empty($r['nombre_presentacion']) || !empty($r['contenido_neto'])) {
            $presentacion_linea = trim(($r['nombre_presentacion'] ?? '') . ' — ' . ($r['contenido_neto'] ?? ''), ' —');
            if ($presentacion_linea !== '') {
                $med_linea .= '<br><span style="font-size:8.5pt; color:#777;">' . htmlspecialchars($presentacion_linea) . '</span>';
            }
        }
        if (!empty($r['concentracion'])) {
            $med_linea .= '<br><span style="font-size:8.5pt; color:#777;">Concentración: ' . htmlspecialchars($r['concentracion']) . '</span>';
        }
        if (!empty($r['indicaciones'])) {
            $med_linea .= '<br><span style="font-size:8.5pt; color:#555;"><i>' . htmlspecialchars($r['indicaciones']) . '</i></span>';
        }

        $dosis_txt      = !empty($r['dosis']) ? $r['dosis'] : '—';
        $frecuencia_txt = !empty($r['frecuencia']) ? $r['frecuencia'] : '—';
        $via_txt        = !empty($r['via']) ? $r['via'] : (!empty($r['via_catalogo']) ? $r['via_catalogo'] : '—');
        $duracion_txt   = !empty($r['duracion']) ? $r['duracion'] : '—';

        $html_receta .= '
            <tr>
                <td>' . $med_linea . '</td>
                <td>' . htmlspecialchars($dosis_txt) . '</td>
                <td>' . htmlspecialchars($frecuencia_txt) . '</td>
                <td>' . htmlspecialchars($via_txt) . '</td>
                <td>' . htmlspecialchars($duracion_txt) . '</td>
            </tr>';
    }
    $html_receta .= '</tbody></table>';
} else {
    $html_receta .= '<p style="font-size: 10pt;">No se prescribieron medicamentos en esta consulta.</p>';
}
$html_receta .= '<br>';

/* --- Exámenes solicitados --- */
$html_examenes = '';
if (!empty($examenes)) {
    $html_examenes = '<h3 style="color:#0056b3; border-bottom: 1px solid #0056b3;">Exámenes Solicitados</h3>';
    $html_examenes .= '
        <table border="1" cellpadding="5" style="width: 100%; border-collapse: collapse; font-size: 9.5pt;">
            <thead>
                <tr style="background-color: #f0f0f0;">
                    <th width="55%">Examen</th>
                    <th width="15%">Tipo</th>
                    <th width="15%">Prioridad</th>
                    <th width="15%">Indicaciones</th>
                </tr>
            </thead>
            <tbody>';
    foreach ($examenes as $ex) {
        $nombre_ex = $ex['nombre_examen'] ?? '';
        if (!empty($ex['codigo_examen'])) $nombre_ex .= ' (' . $ex['codigo_examen'] . ')';
        $html_examenes .= '
            <tr>
                <td>' . htmlspecialchars($nombre_ex) . '</td>
                <td>' . htmlspecialchars($ex['tipo_examen'] ?? '—') . '</td>
                <td>' . htmlspecialchars($ex['prioridad'] ?? '—') . '</td>
                <td>' . htmlspecialchars($ex['indicaciones'] ?? '—') . '</td>
            </tr>';
    }
    $html_examenes .= '</tbody></table><br>';
}

/* --- Indicaciones generales + receptor --- */
$html_indicaciones = '<h3 style="color:#0056b3; border-bottom: 1px solid #0056b3;">Indicaciones Generales y Tratamiento</h3>';

if (!empty(trim($datos['tratamiento_indicado']))) {
    $html_indicaciones .= '<p style="font-size: 10pt;">' . nl2br(htmlspecialchars($datos['tratamiento_indicado'])) . '</p>';
}

if ($sm && !empty(trim($sm['entregado_a'] ?? ''))) {
    $recibido = '<b>Recibido por:</b> ' . htmlspecialchars($sm['entregado_a']);
    if (!empty(trim($sm['parentesco_receptor'] ?? ''))) {
        $recibido .= ' <i>(' . htmlspecialchars($sm['parentesco_receptor']) . ')</i>';
    }
    if (!empty(trim($sm['cedula_receptor'] ?? ''))) {
        $recibido .= ' — C.I: ' . htmlspecialchars($sm['cedula_receptor']);
    }
    $html_indicaciones .= '<p style="font-size: 10pt;">' . $recibido . '</p>';
}

$html_indicaciones .= '<br><br>';

/* --- Firma --- */
$html_firma = '
    <div style="text-align: center; margin-top: 50px; font-size: 11pt;">
        <br><br><br>
        <p>_________________________________________</p>
        <p>Firma</p>
        <p><b>Médico Tratante:</b> ' . htmlspecialchars($datos['nombre_medico'] . ' ' . $datos['apellido_medico']) . '</p>
        <p><b>Cédula / Registro:</b> ' . htmlspecialchars($datos['tipo_cedula_medico'] . '-' . $datos['cedula_medico']) . '</p>
    </div>
';

/* ============================================================
   12) PDF
   ============================================================ */
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Sistema CPT-3');
$pdf->SetTitle('Récipe Médico ' . $id_consulta);

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(20, 15, 20);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->SetFont('helvetica', '', 10);
$pdf->AddPage();

$pdf->writeHTML($html_header,         true, false, true, false, '');
$pdf->writeHTML($html_paciente,       true, false, true, false, '');
$pdf->writeHTML($html_signos_vitales, true, false, true, false, '');
$pdf->writeHTML($html_diagnostico,    true, false, true, false, '');
$pdf->writeHTML($html_receta,         true, false, true, false, '');
if (!empty($html_examenes)) {
    $pdf->writeHTML($html_examenes,   true, false, true, false, '');
}
$pdf->writeHTML($html_indicaciones,   true, false, true, false, '');
$pdf->writeHTML($html_firma,          true, false, true, false, '');

$pdf->Output(
    "Recipe_Medico_" . $datos['tipo_cedula_paciente'] . "-" . $datos['cedula_paciente'] . ".pdf",
    'I'
);
exit;