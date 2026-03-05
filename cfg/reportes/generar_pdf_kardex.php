<?php
// 1. Incluir la librería TCPDF y la conexión
require_once('../../plugins/vendor/tcpdf/tcpdf.php'); 
include('../conexion.php');

// 2. Recibir parámetros del modal de farmacia_inventario_ver_kardex.php
$desde = isset($_GET['desde']) ? $_GET['desde'] : '';
$hasta = isset($_GET['hasta']) ? $_GET['hasta'] : '';

// 3. Configuración del documento TCPDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Información del documento
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Sistema Farmacia');
$pdf->SetTitle('Kardex de Medicamentos');

// Eliminar cabecera y pie por defecto para usar diseño personalizado
$pdf->setPrintHeader(false);
$pdf->setFooterData(array(0,64,0), array(0,64,128));

// Configurar márgenes
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

$pdf->AddPage();

// 4. Estilo de Título (Basado en tu diseño de colores oscuros)
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'REPORTE DE KARDEX', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 5, 'Historial de movimientos de inventario', 0, 1, 'C');

if (!empty($desde) && !empty($hasta)) {
    $pdf->SetFont('helvetica', 'I', 9);
    $pdf->Cell(0, 10, "Periodo: " . date('d/m/Y', strtotime($desde)) . " al " . date('d/m/Y', strtotime($hasta)), 0, 1, 'C');
}
$pdf->Ln(5);

// 5. Estructura de la Tabla (HTML para mayor facilidad con TCPDF)
// Usamos el color #222 para el encabezado como en tu tabla t_user
$html = '
<table border="0.5" cellpadding="4">
    <thead>
        <tr style="background-color: #222; color: white; font-weight: bold; text-align: center;">
            <th width="15%">Fecha</th>
            <th width="35%">Medicamento</th>
            <th width="15%">Lote</th>
            <th width="15%">Movimiento</th>
            <th width="20%">Stock Final</th>
        </tr>
    </thead>
    <tbody>';

// 6. Consulta SQL corregida con tus parámetros
$sql = "SELECT 
            di.fecha, 
            m.nombre_medicamento, 
            dm.cantidad_unidad_medida, 
            l.Lote, 
            tm.nombre as tipo, 
            mdi.stock_momento
        FROM medicamentos_detalle_inventario mdi
        JOIN detalle_inventario di ON mdi.Id_detalle_inventario = di.Id_detalle_inventario
        JOIN descripcion_medicamento dm ON mdi.Id_descripcion_medicamento = dm.Id
        JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
        JOIN lotes_medicamentos l ON mdi.Id_lote = l.Id
        JOIN tipo_movimiento tm ON di.Id_TipoMovimiento = tm.id_tipo_movimiento";

if (!empty($desde) && !empty($hasta)) {
    $sql .= " WHERE di.fecha BETWEEN '$desde 00:00:00' AND '$hasta 23:59:59'";
}

$sql .= " ORDER BY di.fecha DESC";
$resultado = $conexion->query($sql);

if ($resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $fecha = date('d/m/Y', strtotime($row['fecha']));
        $med = $row['nombre_medicamento'] . " (" . $row['cantidad_unidad_medida'] . ")";
        $color_tipo = ($row['tipo'] == 'Entrada') ? 'color: #28a745;' : 'color: #dc3545;'; // Verde o Rojo

        $html .= '
        <tr>
            <td style="text-align: center;">' . $fecha . '</td>
            <td>' . $med . '</td>
            <td style="text-align: center;">' . $row['Lote'] . '</td>
            <td style="text-align: center; font-weight: bold; ' . $color_tipo . '">' . $row['tipo'] . '</td>
            <td style="text-align: center; font-weight: bold;">' . $row['stock_momento'] . '</td>
        </tr>';
    }
} else {
    $html .= '<tr><td colspan="5" style="text-align: center;">No hay movimientos en este rango.</td></tr>';
}

$html .= '</tbody></table>';

// Escribir el HTML al PDF
$pdf->SetFont('helvetica', '', 9);
$pdf->writeHTML($html, true, false, true, false, '');
if (ob_get_contents()) ob_end_clean();
// 7. Salida del PDF
$pdf->Output('Kardex_Farmacia_' . date('dmY') . '.pdf', 'I');
?>


