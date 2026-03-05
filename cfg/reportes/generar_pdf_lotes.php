<?php
ob_start();
require_once('../../plugins/vendor/tcpdf/tcpdf.php'); 
include('../conexion.php');

$tipo = $_GET['tipo'];
$fecha_hoy = date('Y-m-d');
$fecha_limite = date('Y-m-d', strtotime($fecha_hoy . ' + 60 days')); // Margen de 2 meses

// Consulta base corregida para Lotes (Triple Join)
$sql_base = "SELECT 
                l.*, 
                m.nombre_medicamento, 
                p.tipo_presentacion AS presentacion,
                ex.cantidad_actual AS cantidad
             FROM lotes_medicamentos l
             INNER JOIN descripcion_medicamento dm ON l.Id_descripcion_medicamento = dm.Id
             INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
             INNER JOIN presentacion p ON dm.Id_presentacion = p.Id_presentacion
             INNER JOIN existencias_stock ex ON l.Id = ex.Id_lote";

switch ($tipo) {
    case 'vencidos':
        $titulo = "REPORTE DE LOTES VENCIDOS";
        $sql = $sql_base . " WHERE l.fecha_vencimiento < '$fecha_hoy' AND l.estatus = 1";
        break;
    case 'proximos_vencer':
        $titulo = "LOTES PRÓXIMOS A VENCER (60 DÍAS)";
        $sql = $sql_base . " WHERE l.fecha_vencimiento BETWEEN '$fecha_hoy' AND '$fecha_limite' AND l.estatus = 1";
        break;
    case 'stock_bajo':
        $titulo = "ALERTA DE STOCK CRÍTICO EN LOTES";
        $sql = $sql_base . " WHERE ex.cantidad_actual <= 20 AND l.estatus = 1 ORDER BY ex.cantidad_actual ASC";
        break;
    case 'activos':
        $titulo = "LOTES ACTIVOS";
        $sql = $sql_base . " WHERE l.estatus = 1 ORDER BY ex.cantidad_actual ASC";
        break;
    default:
        $titulo = "INVENTARIO GENERAL DE LOTES";
        $sql = $sql_base . " ORDER BY l.fecha_vencimiento ASC";
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
    td { border: 1px solid #ccc; text-align: center; font-size: 9pt; }
    .rojo { color: #d9534f; font-weight: bold; }
    .naranja { color: #f0ad4e; font-weight: bold; }
</style>
<h1>' . $titulo . '</h1>
<table cellpadding="5">
    <thead>
        <tr>
            <th width="30%">Medicamento</th>
            <th width="20%">Número de Lote</th>
            <th width="15%">Cantidad</th>
            <th width="20%">Vencimiento</th>
            <th width="15%">Estado</th>
        </tr>
    </thead>
    <tbody>';

while ($row = mysqli_fetch_assoc($resultado)) {
    // Lógica de colores para alertas
    $clase_vence = "";
    if ($row['fecha_vencimiento'] < $fecha_hoy) {
        $clase_vence = 'class="rojo"';
    } elseif ($row['fecha_vencimiento'] <= $fecha_limite) {
        $clase_vence = 'class="naranja"';
    }

    $clase_stock = ($row['cantidad'] <= 20) ? 'class="rojo"' : '';

    $html .= '<tr>
                <td width="30%" style="text-align:left;">'.mb_strtoupper($row['nombre_medicamento']).'</td>
                <td width="20%">'.$row['numero_lote'].'</td>
                <td width="15%" '.$clase_stock.'>'.$row['cantidad'].'</td>
                <td width="20%" '.$clase_vence.'>'.$row['fecha_vencimiento'].'</td>
                <td width="15%">'.($row['estatus'] == 1 ? 'Activo' : 'Inactivo').'</td>
              </tr>';
}

$html .= '</tbody></table>';

if (ob_get_contents()) ob_end_clean();

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Reporte_Lotes_Farmacia.pdf', 'I');


