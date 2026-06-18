<?php
require_once('../../plugins/vendor/tcpdf/tcpdf.php'); 

// 1. INCLUSIÓN DE CONEXIÓN (Ajusta la ruta si es necesario)
include("../conexion.php"); 

function handle_error_pdf($mensaje, $sql = '') {
    die("<h1>❌ Error al generar PDF</h1><p>$mensaje</p><pre>$sql</pre>");
}

// ==========================================================
// 2. OBTENER DATOS DEL PEDIDO
// ==========================================================
$id_pedido = (int)($_GET['Id'] ?? 0);

if ($id_pedido <= 0 || !isset($conexion)) {
    handle_error_pdf("ID de pedido inválido o error de conexión.");
}

// Consulta Principal
$sql_pedido = "
    SELECT 
        p.id_pedido, p.fecha_creacion, p.estado,
        prov.nombre_proveedor,
        per.nombre AS nom_usuario, per.apellido AS ape_usuario, per.cedula
    FROM pedidos p
    JOIN proveedor prov ON p.id_proveedor = prov.Id_proveedor
    JOIN persona per ON p.id_usuario = per.id
    WHERE p.id_pedido = $id_pedido
    LIMIT 1;
";

$res_pedido = mysqli_query($conexion, $sql_pedido);
if (!$res_pedido || mysqli_num_rows($res_pedido) === 0) {
    handle_error_pdf("No se encontró el pedido con ID $id_pedido.", $sql_pedido);
}
$datos_pedido = mysqli_fetch_assoc($res_pedido);

// ==========================================================
// 3. OBTENER DETALLES DE LOS MEDICAMENTOS (CORREGIDO)
// ==========================================================
$sql_detalles = "
    SELECT 
        dp.id_detalle,
        dp.cantidad_solicitada,
        m.nombre_medicamento,
        pres.nombre_presentacion,
        dm.contenido_neto,
        GROUP_CONCAT(CONCAT(IFNULL(pa.nombre,''), ' ', IFNULL(dpm.cantidad_unidad_medida,''), ' ', IFNULL(um.unidad,'')) SEPARATOR ' + ') AS componentes
    FROM detalle_pedidos dp
    JOIN descripcion_medicamento dm ON dp.id_descripcion_medicamento = dm.Id
    JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
    LEFT JOIN presentacion pres ON dm.Id_presentacion = pres.Id_presentacion
    LEFT JOIN detalle_principio_medicamento dpm ON dm.Id = dpm.id_medicamento
    LEFT JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida
    LEFT JOIN principio_activo pa ON dpm.id_principio_activo = pa.Id_principio_activo
    WHERE dp.id_pedido = $id_pedido
    GROUP BY 
        dp.id_detalle, 
        dp.cantidad_solicitada, 
        m.nombre_medicamento, 
        pres.nombre_presentacion, 
        dm.contenido_neto;
";

$res_detalles = mysqli_query($conexion, $sql_detalles);

// Si hay error en la consulta SQL, lo mostramos (evita fallos silenciosos)
if (!$res_detalles) {
    handle_error_pdf("Error de base de datos al buscar los medicamentos: " . mysqli_error($conexion), $sql_detalles);
}

$detalles = [];
while ($row = mysqli_fetch_assoc($res_detalles)) {
    $detalles[] = $row;
}

// ==========================================================
// 4. CONFIGURACIÓN Y GENERACIÓN DEL PDF (TCPDF)
// ==========================================================
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Sistema CPT-3');
$pdf->SetTitle('Orden de Pedido #' . str_pad($id_pedido, 6, "0", STR_PAD_LEFT));

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(20, 15, 20); 
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->SetFont('helvetica', '', 10);

$pdf->AddPage();

// ---------------------------------------------------------
// ESTRUCTURA HTML PARA TCPDF
// ---------------------------------------------------------
$html_header = '
    <h1 style="text-align:center; color:#0056b3;">CENTRO DE SALUD CPT-3</h1>
    <h2 style="text-align:center; border-bottom: 1px solid #333;">ORDEN DE PEDIDO A PROVEEDOR</h2>
    <br><br>
';

$html_info = '
    <table cellpadding="4" cellspacing="0" border="0" style="font-size: 11pt; width: 100%;">
        <tr>
            <td width="50%"><b>N° de Pedido:</b> #' . str_pad($datos_pedido['id_pedido'], 6, "0", STR_PAD_LEFT) . '</td>
            <td width="50%"><b>Fecha de Emisión:</b> ' . date("d/m/Y h:i A", strtotime($datos_pedido['fecha_creacion'])) . '</td>
        </tr>
        <tr>
            <td width="50%"><b>Proveedor:</b> ' . htmlspecialchars($datos_pedido['nombre_proveedor']) . '</td>
            <td width="50%"><b>Estado:</b> ' . htmlspecialchars($datos_pedido['estado']) . '</td>
        </tr>
    </table>
    <br><br>
    <h3 style="color:#0056b3; border-bottom: 1px solid #0056b3;">Detalle de Insumos</h3>
';

// Tabla HTML estructurada con THEAD y TBODY para que no se rompa TCPDF
$html_tabla = '
    <table border="1" cellpadding="5" style="width: 100%; border-collapse: collapse; font-size: 10pt;">
        <thead>
            <tr style="background-color: #f0f0f0;">
                <th width="75%"><b>Medicamento / Presentación</b></th>
                <th width="25%" style="text-align:center;"><b>Cant. Solicitada</b></th>
            </tr>
        </thead>
        <tbody>';

if (!empty($detalles)) {
    foreach ($detalles as $item) {
        $componentes = !empty($item['componentes']) ? ' (' . htmlspecialchars($item['componentes']) . ')' : '';
        $html_tabla .= '
            <tr>
                <td width="75%">' . htmlspecialchars($item['nombre_medicamento']) . $componentes . ' - ' . htmlspecialchars($item['nombre_presentacion']) . ' (' . htmlspecialchars($item['contenido_neto']) . ')</td>
                <td width="25%" style="text-align:center;">' . htmlspecialchars($item['cantidad_solicitada']) . '</td>
            </tr>';
    }
} else {
    $html_tabla .= '
            <tr>
                <td colspan="2" width="100%" style="text-align:center;">No se encontraron artículos en este pedido.</td>
            </tr>';
}

$html_tabla .= '
        </tbody>
    </table>';

$html_firma = '
    <div style="text-align: center; margin-top: 50px; font-size: 11pt;">
        <br><br><br>
        <p>_________________________________________</p>
        <p>Firma y Sello</p>
        <p><b>Solicitado por:</b> ' . htmlspecialchars($datos_pedido['nom_usuario'] . ' ' . $datos_pedido['ape_usuario']) . '</p>
        <p><b>Cédula:</b> ' . htmlspecialchars($datos_pedido['cedula']) . '</p>
    </div>
';

$pdf->writeHTML($html_header . $html_info . $html_tabla . $html_firma, true, false, true, false, '');

// ---------------------------------------------------------
// 4. SALIDA DEL PDF
// ---------------------------------------------------------
$nombre_archivo = "Pedido_Proveedor_" . str_pad($id_pedido, 6, "0", STR_PAD_LEFT) . ".pdf";
$pdf->Output($nombre_archivo, 'I'); 
exit;
?>