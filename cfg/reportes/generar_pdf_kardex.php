<?php
// 1. Incluir la librería TCPDF y la conexión
require_once('../../plugins/vendor/tcpdf/tcpdf.php'); 
include('../../cfg/conexion.php'); // Ajusta esta ruta si es diferente

// 2. Recibir parámetros del frontend
$desde = isset($_GET['desde']) ? $_GET['desde'] : '';
$hasta = isset($_GET['hasta']) ? $_GET['hasta'] : '';
$medicamento = isset($_GET['medicamento']) ? trim($_GET['medicamento']) : '';

// 3. Validación de seguridad: Si no hay medicamento, no podemos generar Kardex
if (empty($medicamento)) {
    die("Error crítico: No se ha especificado un medicamento para generar el Kardex.");
}

// 4. Configuración del documento TCPDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Información del documento
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Sistema Farmacia');
$pdf->SetTitle('Kardex - ' . $medicamento);

// Eliminar cabecera y pie por defecto para usar diseño personalizado
$pdf->setPrintHeader(false);
$pdf->setFooterData(array(0,64,0), array(0,64,128));

// Configurar márgenes
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

$pdf->AddPage();

// 5. Encabezado del PDF
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'REPORTE DE KARDEX', 0, 1, 'C');

$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(0, 102, 204); // Color azul para resaltar el medicamento
$pdf->Cell(0, 8, 'Producto: ' . $medicamento, 0, 1, 'C');
$pdf->SetTextColor(0, 0, 0); // Volver a negro

if (!empty($desde) && !empty($hasta)) {
    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->Cell(0, 8, "Periodo evaluado: " . date('d/m/Y', strtotime($desde)) . " al " . date('d/m/Y', strtotime($hasta)), 0, 1, 'C');
}
$pdf->Ln(5);

// 6. Estructura de la Tabla (Añadí la columna Cantidad)
$html = '
<table border="0.5" cellpadding="4">
    <thead>
        <tr style="background-color: #222; color: white; font-weight: bold; text-align: center; font-size: 8px;">
            <th width="20%">Fecha</th>
            <th width="20%">Lote</th>
            <th width="20%">Tipo de Movimiento</th>
            <th width="20%">Cantidad</th>
            <th width="20%">Stock Final</th>
        </tr>
    </thead>
    <tbody>';

// 7. Consulta SQL adaptada (Idéntica a la vista web para asegurar exactitud)
$medicamento_esc = $conexion->real_escape_string($medicamento);

$sql = "SELECT 
            di.fecha, 
            m.nombre_medicamento, 
            l.Lote, 
            tm.nombre as tipo,
            mdi.cantidad, 
            mdi.stock_momento
        FROM medicamentos_detalle_inventario mdi
        JOIN detalle_inventario di ON mdi.Id_detalle_inventario = di.Id_detalle_inventario
        JOIN descripcion_medicamento dm ON mdi.Id_descripcion_medicamento = dm.Id
        JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
        JOIN lotes_medicamentos l ON mdi.Id_lote = l.Id
        JOIN tipo_movimiento tm ON di.Id_tipoMovimiento = tm.Id_tipo_movimiento
        WHERE m.nombre_medicamento = '$medicamento_esc'";

// Filtro de fechas
if (!empty($desde) && !empty($hasta)) {
    $sql .= " AND (di.fecha BETWEEN '$desde 00:00:00' AND '$hasta 23:59:59')";
}

// Orden cronológico (Usualmente un Kardex en PDF se lee de más antiguo a más nuevo, pero lo dejé DESC como tu web)
$sql .= " GROUP BY mdi.Id ORDER BY di.fecha DESC";

$resultado = $conexion->query($sql);

if ($resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $fecha = date('d/m/Y H:i', strtotime($row['fecha']));
        $tipo = $row['tipo'];
        
        // Lógica de colores según el tipo de movimiento
        if (in_array($tipo, ['Entrada', 'Ajuste por Cuadre (Entrada)', 'Reversión de Salida (Anulación)'])) {
            $color_tipo = 'color: #1e8449;'; // Verde
            $signo = '+';
        } else {
            $color_tipo = 'color: #c0392b;'; // Rojo
            $signo = '-';
        }

        $html .= '
        <tr style="font-size: 9px;">
            <td style="text-align: center;">' . $fecha . '</td>
            <td style="text-align: center;">' . $row['Lote'] . '</td>
            <td style="font-weight: bold; ' . $color_tipo . '">' . $tipo . '</td>
            <td style="text-align: center; font-weight: bold; ' . $color_tipo . '">' . $signo . $row['cantidad'] . '</td>
            <td style="text-align: center; font-weight: bold;">' . $row['stock_momento'] . '</td>
        </tr>';
    }
} else {
    $html .= '<tr><td colspan="5" style="text-align: center; font-size: 10px;">No hay movimientos registrados para este medicamento en las fechas seleccionadas.</td></tr>';
}

$html .= '</tbody></table>';

// Escribir el HTML al PDF
$pdf->SetFont('helvetica', '', 9);
$pdf->writeHTML($html, true, false, true, false, '');

// Evitar errores de "Headers already sent"
if (ob_get_contents()) ob_end_clean();

// 8. Salida del PDF
$pdf->Output('Kardex_' . preg_replace('/[^a-zA-Z0-9]/', '_', $medicamento) . '_' . date('Ymd') . '.pdf', 'I');
?>