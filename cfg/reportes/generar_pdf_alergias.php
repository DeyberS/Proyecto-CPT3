<?php
require_once('../../plugins/vendor/tcpdf/tcpdf.php'); 
include('../conexion.php');

$tipo = $_GET['tipo'];

// 1. LÓGICA DE CONSULTA SQL
switch ($tipo) {
    case 'activas':
        $titulo = "CATÁLOGO DE ALERGIAS ACTIVAS";
        $sql = "SELECT * FROM alergias_conocidas WHERE estatus = 1 ORDER BY nombre_alergia ASC";
        break;
    case 'inactivas':
        $titulo = "CATÁLOGO DE ALERGIAS INACTIVAS";
        $sql = "SELECT * FROM alergias_conocidas WHERE estatus = 0 ORDER BY nombre_alergia ASC";
        break;
    default:
        $titulo = "CATÁLOGO GENERAL DE ALERGIAS";
        $sql = "SELECT * FROM alergias_conocidas ORDER BY nombre_alergia ASC";
        break;
}

$resultado = mysqli_query($conexion, $sql);

// 2. CONFIGURACIÓN TCPDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetTitle($titulo);
$pdf->setPrintHeader(false);
$pdf->AddPage();

$html = '
<style>
    h1 { text-align: center; color: #333; font-size: 14pt; }
    table { border-collapse: collapse; width: 100%; }
    th { background-color: #337ab7; color: white; font-weight: bold; border: 1px solid #000; text-align: center; }
    td { border: 1px solid #ccc; text-align: left; font-size: 10pt; }
</style>
<h1>' . $titulo . '</h1>
<table cellpadding="6">
    <thead>
        <tr>
            <th width="100%">Alergia</th>
        </tr>
    </thead>
    <tbody>';

while ($row = mysqli_fetch_assoc($resultado)) {
    $estado = ($row['estatus'] == 1) ? '<span style="color:green;">Activa</span>' : '<span style="color:red;">Inactiva</span>';
    $html .= '<tr>
                <td width="60%">'.ucwords(strtolower($row['nombre_alergia'])).'</td>
              </tr>';
}

$html .= '</tbody></table>';

$pdf->writeHTML($html, true, false, true, false, '');
if (ob_get_contents()) ob_end_clean();
$pdf->Output('Reporte_Alergias.pdf', 'I');


