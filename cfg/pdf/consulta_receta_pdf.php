<?php
require_once('../../plugins/vendor/tcpdf/tcpdf.php'); 


// 1. INCLUSIÓN DE CONEXIÓN
include("../conexion.php"); // Asegúrate de que esta ruta es correcta.

// Función de manejo de errores
function handle_error_pdf($mensaje, $sql = '') {
    // Si el script es llamado desde el 'trigger', el output de error puede ser confuso.
    // Es mejor imprimir un mensaje simple.
    die("<h1>❌ Error al generar PDF</h1><p>$mensaje</p><pre>$sql</pre>");
}

// ==========================================================
// 2. OBTENER DATOS DE LA CONSULTA
// ==========================================================
$id_consulta = (int)($_GET['id_consulta'] ?? 0);

if ($id_consulta <= 0 || !isset($conexion)) {
    handle_error_pdf("ID de consulta inválido o error de conexión.");
}

// Consulta Principal (Datos del Paciente, Médico y Tratamiento)
$sql_consulta = "
    SELECT 
        c.fecha_consulta, c.motivo_consulta, c.diagnostico, c.tratamiento_indicado, c.examenes_solicitados, entregado_a,
        p.nombre AS nombre_paciente, p.apellido AS apellido_paciente, p.tipo_cedula AS tipo_cedula_paciente, p.cedula AS cedula_paciente, p.fecha_nacimiento,
        m.nombre AS nombre_medico, m.apellido AS apellido_medico, m.tipo_cedula AS tipo_cedula_medico, m.cedula AS cedula_medico
    FROM consulta c
    JOIN persona p ON c.Id_paciente = p.id
    JOIN persona m ON c.Id_medico = m.id
    WHERE c.Id_consulta = $id_consulta
    LIMIT 1;
";

$res_consulta = mysqli_query($conexion, $sql_consulta);
if (!$res_consulta || mysqli_num_rows($res_consulta) === 0) {
    handle_error_pdf("No se encontró la consulta con ID $id_consulta.", $sql_consulta);
}
$datos_consulta = mysqli_fetch_assoc($res_consulta);


// Obtener Récipes/Prescripciones (Medicamentos)
$sql_receta = "
    SELECT 
        m.nombre_medicamento,
        u.unidad,
        pm.dosis,
        ps.tipo_presentacion
    FROM prescripcion_medicamentos pm
    JOIN descripcion_medicamento dm ON pm.Id_descripcion_medicamento = dm.Id
    JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
    JOIN unidad_medida u ON dm.Id_unidad = u.Id_unidad_medida
    JOIN presentacion ps ON dm.Id_presentacion = ps.Id_presentacion
    WHERE pm.Id_consulta = $id_consulta;
";

$res_receta = mysqli_query($conexion, $sql_receta);
$recetas = [];
while ($row = mysqli_fetch_assoc($res_receta)) {
    $recetas[] = $row;
}

// Calcular Edad del paciente
$fn = new DateTime($datos_consulta['fecha_nacimiento']);
$hoy = new DateTime();
$edad = $fn->diff($hoy)->y;


// ==========================================================
// 3. CONFIGURACIÓN Y GENERACIÓN DEL PDF (TCPDF)
// ==========================================================

// Crear una instancia de PDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// ---------------------------------------------------------
// Configuración del documento
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Sistema CPT-3');
$pdf->SetTitle('Récipe Médico ' . $id_consulta);

// Eliminar encabezado y pie de página estándar
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Establecer márgenes
$pdf->SetMargins(20, 15, 20); 
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Establecer fuente
$pdf->SetFont('helvetica', '', 10);

// Añadir una página
$pdf->AddPage();

// ---------------------------------------------------------
// ESTRUCTURA HTML PARA TCPDF (Simplificada para mejor renderizado)
// ---------------------------------------------------------

$html_header = '
    <h1 style="text-align:center; color:#0056b3;">CENTRO DE SALUD CPT-3</h1>
    <h2 style="text-align:center; border-bottom: 1px solid #333;">RESUMEN DE CONSULTA Y RÉCIPE</h2>
    <br><br>
';

$html_paciente = '
    <table cellpadding="4" cellspacing="0" border="0" style="font-size: 11pt; width: 100%;">
        <tr>
            <td width="50%"><b>Paciente:</b> ' . htmlspecialchars($datos_consulta['nombre_paciente'] . ' ' . $datos_consulta['apellido_paciente']) . '</td>
            <td width="50%"><b>Fecha de la Consulta:</b> ' . date("d/m/Y", strtotime($datos_consulta['fecha_consulta'])) . '</td>
        </tr>
        <tr>
            <td width="50%"><b>Cédula:</b> ' . htmlspecialchars($datos_consulta['tipo_cedula_paciente'].'-'.$datos_consulta['cedula_paciente']) . '</td>
            <td width="50%"><b>Edad:</b> ' . $edad . ' años</td>
        </tr>
    </table>
    <br><br>
';

$html_diagnostico = '
    <h3 style="color:#0056b3; border-bottom: 1px solid #0056b3;">Diagnóstico y Motivo</h3>
    <p style="font-size: 10pt;"><b>Motivo de la Consulta:</b> ' . nl2br(htmlspecialchars($datos_consulta['motivo_consulta'])) . '</p>
    <p style="font-size: 10pt;"><b>Diagnóstico:</b> ' . nl2br(htmlspecialchars($datos_consulta['diagnostico'])) . '</p>
    <p style="font-size: 10pt;"><b>Examenes Solicitados:</b> ' . nl2br(htmlspecialchars($datos_consulta['examenes_solicitados'])) . '</p>
    <br>
';

$html_receta = '
    <h3 style="color:#0056b3; border-bottom: 1px solid #0056b3;">Récipe Médico</h3>
';

// --- Tabla de Receta ---
if (!empty($recetas)) {
    $html_receta .= '
        <table border="1" cellpadding="5" style="width: 100%; border-collapse: collapse; font-size: 10pt;">
            <tr style="background-color: #f0f0f0;">
                <th width="50%">Medicamento</th>
                <th width="50%">Dosis / Unidad</th>
            </tr>';
    
    foreach ($recetas as $receta) {
        $html_receta .= '
            <tr>
                <td>' . htmlspecialchars($receta['nombre_medicamento']) . " " . "(" . " " . htmlspecialchars($receta['tipo_presentacion']) . " " . ")" . '</td>
                <td>' . htmlspecialchars($receta['dosis'] . $receta['unidad']) . '</td>
            </tr>';
    }
    
    $html_receta .= '</table>';
} else {
    $html_receta .= '<p>No se prescribieron medicamentos en esta consulta.</p>';
}

$html_indicaciones = '
    <br>
    <h3 style="color:#0056b3; border-bottom: 1px solid #0056b3;">Indicaciones Generales y Tratamiento</h3>
    <p style="font-size: 10pt;">' . nl2br(htmlspecialchars($datos_consulta['tratamiento_indicado'])) . '</p>
    <p style="font-size: 10pt;"><b>Recibido por:</b>' . nl2br(htmlspecialchars($datos_consulta['entregado_a'])) . '</p>
    <br><br>
';

$html_firma = '
    <div style="text-align: center; margin-top: 50px; font-size: 11pt;">
        <br><br><br>
        <p>_________________________________________</p>
        <p>Firma</p>
        <p><b>Médico Tratante:</b> ' . htmlspecialchars($datos_consulta['nombre_medico'] . ' ' . $datos_consulta['apellido_medico']) . '</p>
        <p><b>Cédula / Registro:</b> ' . htmlspecialchars($datos_consulta['tipo_cedula_medico'].'-'.$datos_consulta['cedula_medico']) . '</p>
    </div>
';


// ---------------------------------------------------------
// Escritura del contenido en el PDF
// ---------------------------------------------------------

$pdf->writeHTML($html_header, true, false, true, false, '');
$pdf->writeHTML($html_paciente, true, false, true, false, '');
$pdf->writeHTML($html_diagnostico, true, false, true, false, '');
$pdf->writeHTML($html_receta, true, false, true, false, '');
$pdf->writeHTML($html_indicaciones, true, false, true, false, '');
$pdf->writeHTML($html_firma, true, false, true, false, '');


// ---------------------------------------------------------
// 4. SALIDA DEL PDF (Stream: mostrar en navegador)
// ---------------------------------------------------------
// 'I' para salida en el navegador (Inline)
$pdf->Output("Recipe Medico " . $datos_consulta['tipo_cedula_paciente'] . "-" . $datos_consulta['cedula_paciente'] . ".pdf", 'D'); 

exit;
?>


