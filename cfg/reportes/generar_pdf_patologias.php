<?php
require_once('../../plugins/vendor/tcpdf/tcpdf.php'); 
include('../conexion.php');

$tipo = $_GET['tipo'];

// 1. LÓGICA DE CONSULTA SQL
switch ($tipo) {
    case 'activas':
        $titulo = "CATÁLOGO DE PATOLOGÍAS ACTIVAS";
        $sql = "SELECT * FROM patologias WHERE estatus = 1 ORDER BY nombre_patologia ASC";
        break;
    case 'inactivas':
        $titulo = "CATÁLOGO DE PATOLOGÍAS INACTIVAS";
        $sql = "SELECT * FROM patologias WHERE estatus = 0 ORDER BY nombre_patologia ASC";
        break;
    default:
        $titulo = "CATÁLOGO MAESTRO DE PATOLOGÍAS";
        $sql = "SELECT * FROM patologias ORDER BY nombre_patologia ASC";
        break;
}

$resultado = mysqli_query($conexion, $sql);

// 2. CONFIGURACIÓN DEL PDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetTitle($titulo);
$pdf->setPrintHeader(false);
$pdf->AddPage();

$html = '
<style>
    h1 { text-align: center; color: #333; font-size: 14pt; }
    table { border-collapse: collapse; width: 100%; }
    th { background-color: #5bc0de; color: white; font-weight: bold; border: 1px solid #000; text-align: center; }
    td { border: 1px solid #ccc; text-align: left; font-size: 10pt; }
    .status-1 { color: green; font-weight: bold; }
    .status-0 { color: red; font-weight: bold; }
</style>
<h1>' . $titulo . '</h1>
<table cellpadding="6">
    <thead>
        <tr>
            <th width="100%">Nombre de la Patología / Enfermedad</th>
        </tr>
    </thead>
    <tbody>';

while ($row = mysqli_fetch_assoc($resultado)) {
    $estado = ($row['estatus'] == 1) ? '<span class="status-1">Activa</span>' : '<span class="status-0">Inactiva</span>';
    $html .= '<tr>
                <td width="100%">'.ucfirst(strtolower($row['nombre_patologia'])).'</td>
              </tr>';
}

$html .= '</tbody></table>';

$pdf->writeHTML($html, true, false, true, false, '');
if (ob_get_contents()) ob_end_clean();
$pdf->Output('Reporte_Patologias.pdf', 'I');


