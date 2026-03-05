<?php
ob_start();
require_once('../../plugins/vendor/tcpdf/tcpdf.php'); 
include('../conexion.php');

$tipo = $_GET['tipo'];
$desde = isset($_GET['desde']) ? $_GET['desde'] : '';
$hasta = isset($_GET['hasta']) ? $_GET['hasta'] : '';

if ($tipo == 'existencia') {
    $titulo = "INVENTARIO DE EXISTENCIAS EN STOCK";
    // JOIN: existencias_stock -> descripcion_medicamento -> medicamento + presentacion
    $sql = "SELECT 
                m.nombre_medicamento, 
                p.tipo_presentacion, 
                es.cantidad_actual
            FROM existencias_stock es
            INNER JOIN descripcion_medicamento dm ON es.Id_descripcion_medicamento = dm.Id
            INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
            INNER JOIN presentacion p ON dm.Id_presentacion = p.Id_presentacion
            ORDER BY m.nombre_medicamento ASC";
} else {
    $tipo_mov = ($tipo == 'entradas') ? 'Entrada' : 'Salida';
    $titulo = "MOVIMIENTOS DE " . strtoupper($tipo_mov) . "S";
    $sql = "SELECT 
    di.fecha, 
    di.observaciones, 
    mdi.cantidad, 
    mdi.stock_momento,
    m.nombre_medicamento, 
    p.tipo_presentacion,
    l.Lote
FROM medicamentos_detalle_inventario mdi
INNER JOIN detalle_inventario di ON mdi.Id_detalle_inventario = di.Id_detalle_inventario
INNER JOIN descripcion_medicamento dm ON mdi.Id_descripcion_medicamento = dm.Id
INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
INNER JOIN presentacion p ON dm.Id_presentacion = p.Id_presentacion
INNER JOIN tipo_movimiento tm ON di.Id_TipoMovimiento = tm.Id_tipo_movimiento
LEFT JOIN lotes_medicamentos l ON mdi.Id_lote = l.Id
WHERE tm.nombre = '$tipo_mov' 
AND di.fecha BETWEEN '$desde' AND '$hasta'
ORDER BY di.fecha DESC";
}

$resultado = mysqli_query($conexion, $sql);

$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetTitle($titulo);
$pdf->setPrintHeader(false);
$pdf->AddPage();

$html = '
<style>
    h1 { text-align: center; color: #333; font-size: 14pt; }
    table { border-collapse: collapse; width: 100%; }
    th { background-color: #337ab7; color: white; font-weight: bold; border: 1px solid #000; text-align: center; }
    td { border: 1px solid #ccc; text-align: center; font-size: 9pt; }
</style>
<h1>' . $titulo . '</h1>
<table cellpadding="6">
    <thead>
        <tr>';
        if ($tipo == 'existencia') {
            $html .= '<th width="40%">Medicamento</th><th width="40%">Presentación</th><th width="20%">Stock Actual</th>';
        } else {
            $html .= '<th width="15%">Fecha</th><th width="45%">Medicamento</th><th width="15%">Cant.</th><th width="25%">Motivo</th>';
        }
$html .= '</tr></thead><tbody>';

if(mysqli_num_rows($resultado) > 0) {
    while ($row = mysqli_fetch_assoc($resultado)) {
        $html .= '<tr>';
        if ($tipo == 'existencia') {
            $html .= '<td style="text-align:left;">'.$row['nombre_medicamento'].'</td>
                      <td style="text-align:left;">'.$row['tipo_presentacion'].'</td>
                      <td style="font-weight:bold;">'.$row['cantidad_actual'].'</td>';
        } else {
            $html .= '<td>'.date('d/m/Y', strtotime($row['fecha'])).'</td>
                      <td style="text-align:left;">'.$row['nombre_medicamento'].' ('.$row['tipo_presentacion'].')</td>
                      <td>'.$row['cantidad_actual'].'</td>
                      <td>'.$row['motivo'].'</td>';
        }
        $html .= '</tr>';
    }
} else {
    $html .= '<tr><td colspan="4">No hay datos para mostrar</td></tr>';
}

$html .= '</tbody></table>';

if (ob_get_contents()) ob_end_clean();
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Reporte_Inventario.pdf', 'I');


