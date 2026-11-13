<?php
// Iniciar buffer y silenciar advertencias de PHP que rompen TCPDF
ob_start();
error_reporting(0); 

require_once('../../plugins/vendor/tcpdf/tcpdf.php'); 
include('../conexion.php'); // Asegúrate de que la ruta de conexión sea la correcta

// Recibir variables del filtro
$tipo = $_GET['tipo'] ?? 'todos';
$fecha_desde = $_GET['desde'] ?? '';
$fecha_hasta = $_GET['hasta'] ?? '';

// 1. CONSTRUCCIÓN DE LA CONSULTA BASE UNIFICADA (Idéntica a la vista)
$sql_base = "
    SELECT 
        'Interna' AS tipo_receta,
        c.Id_consulta AS id_prescripcion,
        CASE 
            WHEN SUM(CASE WHEN pm.estado_prescripcion = 'cancelado' THEN 1 ELSE 0 END) > 0 THEN 'Cancelado'
            WHEN SUM(CASE WHEN pm.estado_prescripcion = 'entregado' THEN 1 ELSE 0 END) = COUNT(pm.Id) THEN 'Entregado'
            WHEN SUM(CASE WHEN pm.estado_prescripcion = 'entregado' THEN 1 ELSE 0 END) > 0 THEN 'Parcial'
            ELSE 'Pendiente'
        END AS estado_entrega,
        c.fecha_consulta AS fecha_solicitud,
        paciente.nombre AS nom_pac, 
        paciente.apellido AS ape_pac,
        paciente.cedula AS cedula_pac,
        medico.nombre AS nom_med,
        medico.apellido AS ape_med,
        GROUP_CONCAT(CONCAT('• ', m.nombre_medicamento) SEPARATOR '<br>') AS nombre_medicamento
    FROM consulta c
    INNER JOIN prescripcion_medicamentos pm ON c.Id_consulta = pm.Id_consulta
    INNER JOIN persona paciente ON c.Id_paciente = paciente.id
    INNER JOIN detalle_medico dmd ON c.Id_medico = dmd.Id_detalle_medico
    INNER JOIN persona medico ON dmd.Id_persona = medico.id
    INNER JOIN descripcion_medicamento dm ON pm.Id_descripcion_medicamento = dm.Id
    INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
    WHERE pm.estatus = 1
    GROUP BY c.Id_consulta

    UNION ALL

    SELECT 
        'Externa' AS tipo_receta,
        sm.id_solicitud AS id_prescripcion,
        sm.estatus_general AS estado_entrega,
        DATE(sm.fecha_solicitud) AS fecha_solicitud,
        paciente.nombre AS nom_pac,
        paciente.apellido AS ape_pac,
        paciente.cedula AS cedula_pac,
        medico.nombre AS nom_med,
        medico.apellido AS ape_med,
        GROUP_CONCAT(CONCAT('• ', m.nombre_medicamento, ' (Cant: ', ds.cantidad_recetada, ')') SEPARATOR '<br>') AS nombre_medicamento
    FROM solicitud_medicamento sm
    INNER JOIN detalle_solicitud ds ON sm.id_solicitud = ds.id_solicitud
    INNER JOIN descripcion_medicamento dm ON ds.id_medicamento = dm.Id
    INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
    INNER JOIN persona paciente ON sm.id_paciente = paciente.id
    INNER JOIN detalle_medico dmd ON sm.id_medico = dmd.Id_detalle_medico
    INNER JOIN persona medico ON dmd.Id_persona = medico.id
    WHERE sm.origen = 'Externo'
    GROUP BY sm.id_solicitud
";

// 2. APLICAR FILTROS SEGÚN LA SOLICITUD
$condiciones = " WHERE 1=1 ";

// Filtro de estado
switch ($tipo) {
    case 'entregados':
        $titulo = "Reporte de Récipes Completados";
        $subtitulo = "Medicamentos totalmente despachados al paciente";
        $condiciones .= " AND estado_entrega IN ('Entregado', 'Completado', 'Completada') ";
        break;
    case 'pendientes':
        $titulo = "Reporte de Récipes Pendientes / Parciales";
        $subtitulo = "Requerimientos a la espera de stock en farmacia";
        $condiciones .= " AND estado_entrega IN ('Pendiente', 'Parcial', 'Parcialmente Entregado') ";
        break;
    case 'cancelados':
        $titulo = "Reporte de Récipes Cancelados";
        $subtitulo = "Solicitudes anuladas o no entregadas";
        $condiciones .= " AND estado_entrega IN ('Cancelado', 'no entregado', 'No Entregado') ";
        break;
    default:
        $titulo = "Historial General de Récipes Médicos";
        $subtitulo = "Control global de prescripciones procesadas";
        break;
}

// Filtro de fechas
$filtroFechasVisual = "";
if (!empty($fecha_desde) && !empty($fecha_hasta)) {
    $condiciones .= " AND fecha_solicitud BETWEEN '$fecha_desde' AND '$fecha_hasta' ";
    $filtroFechasVisual = "<br><span style='font-size: 9pt; color: #555;'>Periodo: " . date('d/m/Y', strtotime($fecha_desde)) . " al " . date('d/m/Y', strtotime($fecha_hasta)) . "</span>";
} elseif (!empty($fecha_desde)) {
    $condiciones .= " AND fecha_solicitud >= '$fecha_desde' ";
    $filtroFechasVisual = "<br><span style='font-size: 9pt; color: #555;'>A partir del: " . date('d/m/Y', strtotime($fecha_desde)) . "</span>";
} elseif (!empty($fecha_hasta)) {
    $condiciones .= " AND fecha_solicitud <= '$fecha_hasta' ";
    $filtroFechasVisual = "<br><span style='font-size: 9pt; color: #555;'>Hasta el: " . date('d/m/Y', strtotime($fecha_hasta)) . "</span>";
}

$sql_final = "SELECT * FROM ($sql_base) AS base_unificada $condiciones ORDER BY fecha_solicitud DESC";
$resultado = mysqli_query($conexion, $sql_final);

// 3. CONFIGURACIÓN DEL PDF CON TCPDF
class MYPDF extends TCPDF {
    public function Header() {
        $this->SetY(10);
        $this->SetFont('helvetica', 'B', 13);
        $this->SetTextColor(0, 192, 239); // Color azul informativo (Bootstrap info)
        $this->Cell(0, 10, 'CONSULTORIO POPULAR TIPO 3 - CONTROL DE DESPACHO', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Line(15, 20, 195, 20); 
    }
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(127, 140, 141); 
        $this->Cell(0, 10, 'Generado el: ' . date('d/m/Y H:i:s') . ' | Página '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

$pdf = new MYPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle($titulo);
$pdf->SetMargins(15, 25, 15);
$pdf->SetAutoPageBreak(TRUE, 20);
$pdf->AddPage();

// 4. ESTRUCTURA HTML PARA EL REPORTE
$html = '
<style>
    h1 { text-align: center; color: #333; font-size: 15pt; margin-bottom: 0px; text-transform: uppercase; }
    h3 { text-align: center; color: #666; font-size: 11pt; margin-top: 5px; font-weight: normal; }
    table.table-report { border-collapse: collapse; width: 100%; margin-top: 25px; }
    th { background-color: #222; color: #ffffff; font-weight: bold; border: 1px solid #555; text-align: center; padding: 8px; font-size: 9pt; }
    td { border: 1px solid #bdc3c7; padding: 8px; font-size: 8.5pt; text-align: center; vertical-align: middle; }
    .td-left { text-align: left; }
</style>
<h1>' . $titulo . '</h1>
<h3>' . $subtitulo . $filtroFechasVisual . '</h3>
<br>
<table class="table-report" cellpadding="5">
    <thead>
        <tr>
            <th width="12%">Fecha</th>
            <th width="28%">Paciente</th>
            <th width="20%">Médico</th>
            <th width="25%">Medicamentos</th>
            <th width="15%">Estado</th>
        </tr>
    </thead>
    <tbody>';

if ($resultado && mysqli_num_rows($resultado) > 0) {
    $fill = false; 
    while ($row = mysqli_fetch_assoc($resultado)) {
        $color_fila = $fill ? '#f9f9f9' : '#ffffff';
        $fill = !$fill;

        // Estilos dinámicos para los estados (Simulando Badges de Bootstrap)
        $estado = strtolower($row['estado_entrega']);
        if ($estado == 'entregado' || $estado == 'completado' || $estado == 'completada') {
            $bg_estado = '#00a65a'; $color_estado = '#ffffff'; $texto_estado = 'Entregado';
        } elseif ($estado == 'pendiente') {
            $bg_estado = '#f39c12'; $color_estado = '#ffffff'; $texto_estado = 'Pendiente';
        } elseif ($estado == 'parcial' || $estado == 'parcialmente entregado') {
            $bg_estado = '#f39c12'; $color_estado = '#ffffff'; $texto_estado = 'Parcial';
        } else { // Cancelado / No entregado
            $bg_estado = '#dc3545'; $color_estado = '#ffffff'; $texto_estado = 'Cancelado';
        }

        // Formateo de nombres
        $nombre_paciente = mb_strtoupper($row['nom_pac'] . " " . $row['ape_pac'], 'UTF-8');
        $nombre_medico = mb_strtoupper($row['nom_med'] . " " . $row['ape_med'], 'UTF-8');
        $fecha = date('d/m/Y', strtotime($row['fecha_solicitud']));

        $html .= '<tr bgcolor="' . $color_fila . '">
                    <td width="12%">' . $fecha . '<br><small>('.$row['tipo_receta'].')</small></td>
                    <td width="28%" class="td-left"><b>' . $nombre_paciente . '</b><br><small>C.I: ' . $row['cedula_pac'] . '</small></td>
                    <td width="20%">' . $nombre_medico . '</td>
                    <td width="25%" class="td-left" style="color: #3c8dbc;">' . $row['nombre_medicamento'] . '</td>
                    <td width="15%" bgcolor="' . $bg_estado . '" style="color:' . $color_estado . '; font-weight:bold;">' . $texto_estado . '</td>
                  </tr>';
    }
} else {
    $html .= '<tr><td colspan="5" style="color: #7f8c8d; text-align: center;">No se encontraron récipes que coincidan con los filtros aplicados.</td></tr>';
}

$html .= '</tbody></table>';

$pdf->writeHTML($html, true, false, true, false, '');

// Limpieza para evitar el error TCPDF: "Some data has already been output"
while (ob_get_level() > 0) {
    ob_end_clean();
}

$pdf->Output('Reporte_Recetas.pdf', 'I');
?>