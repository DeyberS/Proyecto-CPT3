<?php
// Iniciar buffer de salida
ob_start();

require_once('../../plugins/vendor/tcpdf/tcpdf.php'); 
include('../conexion.php');

$tipo = $_GET['tipo'];

// 1. LÓGICA DE CONSULTA SQL PARA ÁREAS
switch ($tipo) {
    case 'activas':
        $titulo = "REPORTE DE ÁREAS ACTIVAS";
        $sql = "SELECT * FROM departamento WHERE estatus = 1 ORDER BY nombre_departamento ASC";
        break;
    case 'inactivas':
        $titulo = "REPORTE DE ÁREAS INACTIVAS";
        $sql = "SELECT * FROM departamento WHERE estatus = 0 ORDER BY nombre_departamento ASC";
        break;
    default:
        $titulo = "LISTADO GENERAL DE ÁREAS";
        $sql = "SELECT * FROM departamento ORDER BY nombre_departamento ASC";
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
    th { background-color: #337ab7; color: white; font-weight: bold; border: 1px solid #000; text-align: center; }
    td { border: 1px solid #ccc; text-align: center; font-size: 10pt; }
</style>
<h1>' . $titulo . '</h1>
<table cellpadding="6">
    <thead>
        <tr>
            <th width="100%">Nombre del Área</th>
        </tr>
    </thead>
    <tbody>';

while ($row = mysqli_fetch_assoc($resultado)) {
    $estado = ($row['estatus'] == 1) ? '<span style="color:green;">Activa</span>' : '<span style="color:red;">Inactiva</span>';
    $html .= '<tr>
                <td width="100%" style="text-align:left;">'.mb_strtoupper($row['nombre_departamento']).'</td>
              </tr>';
}

$html .= '</tbody></table>';

// Limpieza de buffer solicitada para evitar errores de cabecera
if (ob_get_contents()) ob_end_clean();

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Reporte_Areas_CPT3.pdf', 'I');


