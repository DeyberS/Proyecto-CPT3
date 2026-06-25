<?php
// 1. Incluir la librería TCPDF y la conexión
require_once('../../plugins/vendor/tcpdf/tcpdf.php'); 
include('../../cfg/conexion.php'); // Ajusta esta ruta si es diferente

// 2. RECEPCIÓN Y SANITIZACIÓN DE VARIABLES DE FILTRADO
// Validación de "solo texto" para la búsqueda general (medicamento). Elimina números usando preg_replace.
$busqueda      = isset($_GET['buscar']) ? preg_replace('/[0-9]/', '', $_GET['buscar']) : ''; 
$busqueda      = $conexion->real_escape_string($busqueda);

$f_desde       = isset($_GET['f_desde']) ? $conexion->real_escape_string($_GET['f_desde']) : '';
$f_hasta       = isset($_GET['f_hasta']) ? $conexion->real_escape_string($_GET['f_hasta']) : '';
$f_tipo_mov    = isset($_GET['f_tipo_mov']) ? $conexion->real_escape_string($_GET['f_tipo_mov']) : '';
$f_lote        = isset($_GET['f_lote']) ? $conexion->real_escape_string($_GET['f_lote']) : '';
$f_proveedor   = isset($_GET['f_proveedor']) ? $conexion->real_escape_string($_GET['f_proveedor']) : '';
$f_cant_min    = (isset($_GET['f_cant_min']) && $_GET['f_cant_min'] !== '') ? (int)$_GET['f_cant_min'] : 0;
$f_cant_max    = (isset($_GET['f_cant_max']) && $_GET['f_cant_max'] !== '') ? (int)$_GET['f_cant_max'] : 0;
$f_stock_min   = (isset($_GET['f_stock_min']) && $_GET['f_stock_min'] !== '') ? (int)$_GET['f_stock_min'] : 0;
$f_stock_max   = (isset($_GET['f_stock_max']) && $_GET['f_stock_max'] !== '') ? (int)$_GET['f_stock_max'] : 0;

// Validación lógica de fechas: Si "desde" es mayor que "hasta", las invertimos automáticamente para no romper el SQL
if (!empty($f_desde) && !empty($f_hasta)) {
    if (strtotime($f_desde) > strtotime($f_hasta)) {
        $temp = $f_desde;
        $f_desde = $f_hasta;
        $f_hasta = $temp;
    }
}

// 3. CONSTRUCCIÓN DEL WHERE DINÁMICO
$donde = " WHERE 1=1 ";

if ($busqueda != '') {
    // Busca coincidencias en nombre de medicamento, lote o proveedor
    $donde .= " AND (m.nombre_medicamento LIKE '%$busqueda%' OR l.Lote LIKE '%$busqueda%' OR p.nombre_proveedor LIKE '%$busqueda%')";
}
if ($f_desde != '') { $donde .= " AND DATE(di.fecha) >= '$f_desde'"; }
if ($f_hasta != '') { $donde .= " AND DATE(di.fecha) <= '$f_hasta'"; }
if ($f_tipo_mov != '') { $donde .= " AND tm.nombre = '$f_tipo_mov'"; }
if ($f_lote != '') { $donde .= " AND l.Lote LIKE '%$f_lote%'"; }
if ($f_proveedor != '') { $donde .= " AND p.nombre_proveedor LIKE '%$f_proveedor%'"; }
if ($f_cant_min > 0) { $donde .= " AND mdi.cantidad >= $f_cant_min"; }
if ($f_cant_max > 0) { $donde .= " AND mdi.cantidad <= $f_cant_max"; }
if ($f_stock_min > 0) { $donde .= " AND mdi.stock_momento >= $f_stock_min"; }
if ($f_stock_max > 0) { $donde .= " AND mdi.stock_momento <= $f_stock_max"; }

// 4. Configuración del documento TCPDF
// Se usa 'L' (Landscape) porque hay más columnas dinámicas que mostrar.
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false); 

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Sistema Farmacia');
$pdf->SetTitle('Reporte Avanzado de Kardex');

// Eliminar cabecera y pie por defecto
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Configurar márgenes
$pdf->SetMargins(10, 15, 10);
$pdf->SetAutoPageBreak(TRUE, 15);

$pdf->AddPage();

// 5. Encabezado del PDF
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'REPORTE AVANZADO DE KARDEX', 0, 1, 'C');

$pdf->SetFont('helvetica', 'I', 10);
$pdf->SetTextColor(80, 80, 80);

// Construcción del subtítulo con resumen de filtros activos
$filtros_texto = array();
if ($busqueda != '') $filtros_texto[] = "Búsqueda: $busqueda";
if ($f_desde != '' && $f_hasta != '') $filtros_texto[] = "Periodo: " . date('d/m/Y', strtotime($f_desde)) . " - " . date('d/m/Y', strtotime($f_hasta));
elseif ($f_desde != '') $filtros_texto[] = "Desde: " . date('d/m/Y', strtotime($f_desde));
elseif ($f_hasta != '') $filtros_texto[] = "Hasta: " . date('d/m/Y', strtotime($f_hasta));
if ($f_tipo_mov != '') $filtros_texto[] = "Movimiento: $f_tipo_mov";
if ($f_lote != '') $filtros_texto[] = "Lote: $f_lote";
if ($f_proveedor != '') $filtros_texto[] = "Proveedor: $f_proveedor";

if (count($filtros_texto) > 0) {
    $pdf->Cell(0, 6, implode(' | ', $filtros_texto), 0, 1, 'C');
} else {
    $pdf->Cell(0, 6, 'Todos los registros (Sin filtros específicos)', 0, 1, 'C');
}

$pdf->SetTextColor(0, 0, 0); // Volver a negro
$pdf->Ln(5);

// 6. Estructura de la Tabla
// Se añaden columnas de Medicamento y Proveedor ya que la búsqueda ahora es general
$html = '
<table border="0.5" cellpadding="4">
    <thead>
        <tr style="background-color: #222; color: white; font-weight: bold; text-align: center; font-size: 9px;">
            <th width="14.3%">Fecha</th>
            <th width="14.3%">Medicamento</th>
            <th width="14.3%">Lote</th>
            <th width="14.3%">Proveedor</th>
            <th width="14.3%">Movimiento</th>
            <th width="14.3%">Cantidad</th>
            <th width="14.3%">Stock Final</th>
        </tr>
    </thead>
    <tbody>';

// 7. Consulta SQL principal
$sql = "SELECT 
            di.fecha, 
            m.nombre_medicamento, 
            l.Lote, 
            p.nombre_proveedor,
            tm.nombre as tipo,
            mdi.cantidad, 
            mdi.stock_momento,
            GROUP_CONCAT(CONCAT(IFNULL(pa.nombre,''), ' ', IFNULL(dpm.cantidad_unidad_medida,''), IFNULL(um.unidad,'')) SEPARATOR ' + ') AS componentes
        FROM medicamentos_detalle_inventario mdi
        JOIN detalle_inventario di ON mdi.Id_detalle_inventario = di.Id_detalle_inventario
        JOIN descripcion_medicamento dm ON mdi.Id_descripcion_medicamento = dm.Id
        JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
        JOIN detalle_principio_medicamento dpm ON dm.Id = dpm.id_medicamento
        JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida
        JOIN principio_activo pa ON dpm.id_principio_activo = pa.Id_principio_activo
        JOIN lotes_medicamentos l ON mdi.Id_lote = l.Id
        JOIN proveedor p ON l.Id_proveedor = p.Id_proveedor
        JOIN tipo_movimiento tm ON di.Id_tipoMovimiento = tm.Id_tipo_movimiento
        $donde
        GROUP BY mdi.Id
        ORDER BY di.fecha DESC";

$resultado = $conexion->query($sql);

if ($resultado && $resultado->num_rows > 0) {
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
            <td>' . htmlspecialchars($row['nombre_medicamento']) . ' (' . htmlspecialchars($row['componentes']) . ') ' . '</td>
            <td style="text-align: center;">' . htmlspecialchars($row['Lote']) . '</td>
            <td style="text-align: center;">' . htmlspecialchars($row['nombre_proveedor']) . '</td>
            <td style="font-weight: bold; ' . $color_tipo . '">' . $tipo . '</td>
            <td style="text-align: center; font-weight: bold; ' . $color_tipo . '">' . $signo . $row['cantidad'] . '</td>
            <td style="text-align: center; font-weight: bold;">' . $row['stock_momento'] . '</td>
        </tr>';
    }
} else {
    $html .= '<tr><td colspan="7" style="text-align: center; font-size: 10px; padding: 15px;">No hay movimientos registrados que coincidan con los filtros aplicados.</td></tr>';
}

$html .= '</tbody></table>';

// Escribir el HTML al PDF
$pdf->SetFont('helvetica', '', 9);
$pdf->writeHTML($html, true, false, true, false, '');

// Evitar errores de "Headers already sent"
if (ob_get_contents()) ob_end_clean();

// 8. Salida del PDF
$nombre_archivo = 'Reporte_Kardex_' . date('Ymd_Hi') . '.pdf';
$pdf->Output($nombre_archivo, 'I');
?>