<?php
require_once('../../plugins/vendor/tcpdf/tcpdf.php'); 
include('../conexion.php');

$tipo = $_GET['tipo'];

// 1. LÓGICA DE CONSULTA SQL PARA SÍNTOMAS
switch ($tipo) {
    case 'activos':
        $titulo = "LISTADO DE SÍNTOMAS ACTIVOS";
        $sql = "SELECT * FROM sintomas WHERE estatus = 1 ORDER BY nombre_sintoma ASC";
        break;
    case 'inactivos':
        $titulo = "LISTADO DE SÍNTOMAS INACTIVOS";
        $sql = "SELECT * FROM sintomas WHERE estatus = 0 ORDER BY nombre_sintoma ASC";
        break;
    default:
        $titulo = "CATÁLOGO GENERAL DE SÍNTOMAS";
        $sql = "SELECT * FROM sintomas ORDER BY nombre_sintoma ASC";
        break;
}

$resultado = mysqli_query($conexion, $sql);

// 2. CONFIGURACIÓN DEL PDF (TCPDF)
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
            <th width="100%">Síntoma</th>
        </tr>
    </thead>
    <tbody>';

while ($row = mysqli_fetch_assoc($resultado)) {
    $estado = ($row['estatus'] == 1) ? '<span style="color:green;">Activo</span>' : '<span style="color:red;">Inactivo</span>';
    $html .= '<tr>
                <td width="100%">'.ucfirst(strtolower($row['nombre_sintoma'])).'</td>
              </tr>';
}

$html .= '</tbody></table>';

$pdf->writeHTML($html, true, false, true, false, '');
if (ob_get_contents()) ob_end_clean();
$pdf->Output('Reporte_Sintomas.pdf', 'I'); // Visualización en navegador


