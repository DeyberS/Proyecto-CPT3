<?php
// Iniciar buffer de salida
ob_start();

require_once('../../plugins/vendor/tcpdf/tcpdf.php'); 
include('../conexion.php');

$tipo = $_GET['tipo'];

$sql_base = "SELECT 
                dm.Id, 
                m.nombre_medicamento, 
                dm.estatus,
                p.tipo_presentacion AS presentacion
             FROM descripcion_medicamento dm
             INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
             INNER JOIN presentacion p ON dm.Id_presentacion = p.Id_presentacion";

switch ($tipo) {
    case 'activos':
        $titulo = "CATÁLOGO DE MEDICAMENTOS DISPONIBLES";
        $sql = $sql_base . " WHERE dm.estatus = 1 ORDER BY m.nombre_medicamento ASC";
        break;
    case 'inactivos':
        $titulo = "CATÁLOGO DE MEDICAMENTOS INACTIVOS";
        $sql = $sql_base . " WHERE dm.estatus = 0 ORDER BY m.nombre_medicamento ASC";
        break;
    case 'presentacion':
        $titulo = "CATÁLOGO AGRUPADO POR PRESENTACIÓN";
        $sql = $sql_base . " ORDER BY presentacion ASC, m.nombre_medicamento ASC";
        break;
    default:
        $titulo = "CATÁLOGO GENERAL DE MEDICAMENTOS";
        $sql = $sql_base . " ORDER BY m.nombre_medicamento ASC";
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
    td { border: 1px solid #ccc; text-align: center; font-size: 10pt; }
</style>
<h1>' . $titulo . '</h1>
<table cellpadding="6">
    <thead>
        <tr>
            <th width="70%">Nombre del Medicamento</th>
            <th width="30%">Presentación</th>
        </tr>
    </thead>
    <tbody>';

while ($row = mysqli_fetch_assoc($resultado)) {
    $estado = ($row['estatus'] == 1) ? '<span style="color:green;">Activo</span>' : '<span style="color:red;">Inactivo</span>';
    
    $html .= '<tr>
                <td width="70%" style="text-align:left;">'.mb_strtoupper($row['nombre_medicamento']).'</td>
                <td width="30%">'.$row['presentacion'].'</td>
              </tr>';
}

$html .= '</tbody></table>';

// Limpieza de buffer solicitada
if (ob_get_contents()) ob_end_clean();

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Reporte_Medicamentos_CPT3.pdf', 'I');


