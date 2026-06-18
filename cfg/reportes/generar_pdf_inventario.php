<?php
ob_start();
require_once('../../plugins/vendor/tcpdf/tcpdf.php'); 
include('../conexion.php');

// Iniciar sesión para obtener el nombre del usuario (ajusta según tu sistema)
session_start();
$usuario_generador = isset($_SESSION['Usuario']) ? $_SESSION['Usuario'] : 'Sistema Administrativo';

// Recibir parámetros y evitar inyecciones SQL básicas
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'existencia';
$desde = isset($_GET['desde']) && $_GET['desde'] !== '' ? mysqli_real_escape_string($conexion, $_GET['desde']) : '2000-01-01';
$hasta = isset($_GET['hasta']) && $_GET['hasta'] !== '' ? mysqli_real_escape_string($conexion, $_GET['hasta']) : '2100-12-31';

$id_rol = isset($_SESSION['rol']) ? $_SESSION['rol'] : 0;
$id_persona_activa = isset($_SESSION['id']) ? $_SESSION['id'] : (isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : 0);

$filtro_usuario = "";
// Si es el rol 9 (Encargado de Despacho), forzamos a que solo vea sus propios registros
if ($id_rol == 9) {
    $filtro_usuario = " AND di.Id_Persona = '$id_persona_activa' ";
}

// --- CONFIGURACIÓN DE TCPDF ---
class MYPDF extends TCPDF {
    public function Header() {
        // Logo de referencia (puedes cambiar la ruta al PNG definitivo luego)
        // $this->Image('../../dist/img/logo.png', 15, 10, 30); 
        
        $this->SetFont('helvetica', 'B', 14);
        $this->SetTextColor(51, 122, 183); // Color azul profesional (#337ab7)
        $this->Cell(0, 15, 'CPT3 - GESTIÓN DE FARMACIA', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(8);
        $this->SetFont('helvetica', 'I', 10);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 10, 'Control de Inventario y Suministros Médicos', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(15);
        $this->Line(15, 35, 195, 35); // Línea decorativa
    }

    public function Footer() {
        $this->SetY(-25);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Página '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        $this->SetY(-15);
        // Validar si la variable de sesión existe antes de imprimirla en el Footer
        $usuario = isset($_SESSION['Usuario']) ? $_SESSION['Usuario'] : 'Sistema';
        $this->Cell(0, 10, 'Generado por: ' . $usuario . ' - Fecha: ' . date('d/m/Y H:i'), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Reporte de Inventario - ' . strtoupper($tipo));
$pdf->SetMargins(15, 40, 15);
$pdf->SetAutoPageBreak(TRUE, 25);
$pdf->AddPage();

// --- LÓGICA DE CONSULTAS SEGÚN EL TIPO ---
$titulo_reporte = "";
$html_tabla = "";
$usa_fechas = false;

// Subconsulta reutilizable para agrupar los principios activos asociados al medicamento
$sub_query_principios = "(SELECT GROUP_CONCAT(pa.nombre SEPARATOR ' + ') 
                          FROM detalle_principio_medicamento dpm 
                          INNER JOIN principio_activo pa ON dpm.id_principio_activo = pa.id_principio_activo 
                          WHERE dpm.id_medicamento = dm.Id) AS principios_activos";

switch ($tipo) {
    case 'existencia':
        $titulo_reporte = "EXISTENCIA ACTUAL EN STOCK";
        $sql = "SELECT m.nombre_medicamento, p.nombre_presentacion, dm.contenido_neto, l.Lote, l.fecha_vencimiento, es.cantidad_actual,
                $sub_query_principios
                FROM existencias_stock es
                INNER JOIN descripcion_medicamento dm ON es.Id_descripcion_medicamento = dm.Id
                INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
                INNER JOIN presentacion p ON dm.Id_presentacion = p.Id_presentacion
                INNER JOIN lotes_medicamentos l ON es.Id_lote = l.Id
                WHERE es.cantidad_actual > 0 ORDER BY m.nombre_medicamento ASC";
        
        $header_tabla = '<tr>
            <th width="20%" style="background-color:#f2f2f2; font-weight:bold; border:1px solid #ccc;">Medicamento (P.A)</th>
            <th width="20%" style="background-color:#f2f2f2; font-weight:bold; border:1px solid #ccc;">Lote</th>
            <th width="20%" style="background-color:#f2f2f2; font-weight:bold; border:1px solid #ccc;">Vencimiento</th>
            <th width="20%" style="background-color:#f2f2f2; font-weight:bold; border:1px solid #ccc;">Presentacion</th>
            <th width="20%" style="background-color:#f2f2f2; font-weight:bold; border:1px solid #ccc;">Stock</th>
        </tr>';
        break;
        
        case 'vencimientos':
            $titulo_reporte = "PRÓXIMOS VENCIMIENTOS (ALERTA - PRÓXIMOS 6 MESES)";
            $sql = "SELECT m.nombre_medicamento, l.Lote, l.fecha_vencimiento, es.cantidad_actual,
                    $sub_query_principios 
                    FROM existencias_stock es
                    INNER JOIN descripcion_medicamento dm ON es.Id_descripcion_medicamento = dm.Id
                    INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
                    INNER JOIN lotes_medicamentos l ON es.Id_lote = l.Id
                    WHERE l.fecha_vencimiento > CURDATE() 
                      AND l.fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 6 MONTH) 
                      AND es.cantidad_actual > 0
                      AND l.estado_lote = 'Disponible'
                    ORDER BY l.fecha_vencimiento ASC";
            
        
        $header_tabla = '<tr>
            <th width="25%" style="background-color:#f2f2f2; font-weight:bold; border:1px solid #ccc;">Medicamento (P.A)</th>
            <th width="25%" style="background-color:#f2f2f2; font-weight:bold; border:1px solid #ccc;">Lote</th>
            <th width="25%" style="background-color:#f2f2f2; font-weight:bold; border:1px solid #ccc;">Fecha Venc.</th>
            <th width="25%" style="background-color:#f2f2f2; font-weight:bold; border:1px solid #ccc;">Stock</th>
        </tr>';
        break;

    case 'entradas':
        $usa_fechas = true;
        $titulo_reporte = "REPORTE DE ENTRADAS AL INVENTARIO";
        $sql = "SELECT di.fecha AS fecha_movimiento, m.nombre_medicamento, mdi.cantidad, tm.nombre as tipo_movimiento,
                       CONCAT(per.nombre, ' ', IFNULL(per.apellido, '')) AS Usuario, l.Lote,
                       $sub_query_principios
                FROM medicamentos_detalle_inventario mdi
                INNER JOIN detalle_inventario di ON mdi.Id_detalle_inventario = di.Id_detalle_inventario
                INNER JOIN tipo_movimiento tm ON di.Id_TipoMovimiento = tm.Id_tipo_movimiento
                INNER JOIN descripcion_medicamento dm ON mdi.Id_descripcion_medicamento = dm.Id
                INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
                INNER JOIN lotes_medicamentos l ON mdi.Id_lote = l.Id
                INNER JOIN persona per ON di.Id_Persona = per.id
                WHERE tm.Id_tipo_movimiento = 1 
                AND DATE(di.fecha) BETWEEN '$desde' AND '$hasta'
                ORDER BY di.fecha DESC";

        $header_tabla = '<tr>
            <th width="25%" style="background-color:#f2f2f2; font-weight:bold; border:1px solid #ccc;">Fecha</th>
            <th width="25%" style="background-color:#f2f2f2; font-weight:bold; border:1px solid #ccc;">Medicamento (P.A) / Lote</th>
            <th width="25%" style="background-color:#f2f2f2; font-weight:bold; border:1px solid #ccc;">Cant.</th>
            <th width="25%" style="background-color:#f2f2f2; font-weight:bold; border:1px solid #ccc;">Recibido/Registrado por</th>
        </tr>';
        break;

        case 'despacho':
            $usa_fechas = true;
            $titulo_reporte = "REPORTE DE DISPENSACIÓN (RÉCIPES)";
            
            // Se unen las tablas de pacientes internos (adultos/menores) y externos
            $sql = "SELECT di.fecha AS fecha_movimiento, m.nombre_medicamento, mdi.cantidad, tm.nombre as tipo_movimiento,
                           CONCAT(per.nombre, ' ', IFNULL(per.apellido, '')) AS Usuario, l.Lote,
                           $sub_query_principios, di.observaciones,
                           pac.nombre AS pac_nombre, pac.apellido AS pac_apellido, pac.tipo_cedula AS pac_tipo_cedula, pac.cedula AS pac_cedula,
                           rep.nombre AS rep_nombre, rep.apellido AS rep_apellido, rep.tipo_cedula AS rep_tipo_cedula, rep.cedula AS rep_cedula,
                           sm.datos_paciente_externo AS ext_nombre, sm.tipo_cedula_externo AS ext_tipo_cedula, sm.cedula_externo AS ext_cedula
                    FROM medicamentos_detalle_inventario mdi
                    INNER JOIN detalle_inventario di ON mdi.Id_detalle_inventario = di.Id_detalle_inventario
                    INNER JOIN tipo_movimiento tm ON di.Id_TipoMovimiento = tm.Id_tipo_movimiento
                    INNER JOIN descripcion_medicamento dm ON mdi.Id_descripcion_medicamento = dm.Id
                    INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
                    INNER JOIN lotes_medicamentos l ON mdi.Id_lote = l.Id
                    INNER JOIN persona per ON di.Id_Persona = per.id
                    
                    -- Relación para pacientes internos (vienen de una consulta)
                    LEFT JOIN prescripcion_medicamentos pm ON di.Id_prescripcion = pm.Id
                    LEFT JOIN consulta c ON pm.Id_consulta = c.Id_consulta
                    LEFT JOIN persona pac ON c.Id_paciente = pac.id
                    
                    -- Relación en caso de que sea un paciente interno menor de edad
                    LEFT JOIN detalle_paciente_menor dpm_menor ON pac.id = dpm_menor.id_persona
                    LEFT JOIN persona rep ON dpm_menor.id_representante = rep.id
                    
                    -- Relación para pacientes externos (cruzando por fecha/hora exacta)
                    LEFT JOIN solicitud_medicamento sm ON sm.fecha_solicitud = di.fecha
                    
                    WHERE tm.Id_tipo_movimiento = 2 
                    AND DATE(di.fecha) BETWEEN '$desde' AND '$hasta'
                    $filtro_usuario
                    ORDER BY di.fecha DESC";
    
            $header_tabla = '<tr>
                <th width="20%" style="background-color:#f2f2f2; font-weight:bold; border:1px solid #ccc;">Fecha</th>
                <th width="20%" style="background-color:#f2f2f2; font-weight:bold; border:1px solid #ccc;">Medicamento (P.A) / Lote</th>
                <th width="20%" style="background-color:#f2f2f2; font-weight:bold; border:1px solid #ccc;">Cant.</th>
                <th width="20%" style="background-color:#f2f2f2; font-weight:bold; border:1px solid #ccc;">Paciente / Destino</th>
                <th width="20%" style="background-color:#f2f2f2; font-weight:bold; border:1px solid #ccc;">Despachado por</th>
            </tr>';
            break;

    case 'bajas':
        $usa_fechas = true;
        $titulo_reporte = "REPORTE DE BAJAS Y MERMAS";
        // Bajas: Salida por Vencimiento(3), Dañado(4), Pérdida o Robo(5)
        $sql = "SELECT di.fecha AS fecha_movimiento, m.nombre_medicamento, mdi.cantidad, tm.nombre as tipo_movimiento,
                       CONCAT(per.nombre, ' ', IFNULL(per.apellido, '')) AS Usuario, l.Lote,
                       $sub_query_principios
                FROM medicamentos_detalle_inventario mdi
                INNER JOIN detalle_inventario di ON mdi.Id_detalle_inventario = di.Id_detalle_inventario
                INNER JOIN tipo_movimiento tm ON di.Id_TipoMovimiento = tm.Id_tipo_movimiento
                INNER JOIN descripcion_medicamento dm ON mdi.Id_descripcion_medicamento = dm.Id
                INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
                INNER JOIN lotes_medicamentos l ON mdi.Id_lote = l.Id
                INNER JOIN persona per ON di.Id_Persona = per.id
                WHERE tm.Id_tipo_movimiento IN (3, 4, 5) 
                AND DATE(di.fecha) BETWEEN '$desde' AND '$hasta'
                ORDER BY di.fecha DESC";

        $header_tabla = '<tr>
            <th width="20%" style="background-color:#f2f2f2; font-weight:bold; border:1px solid #ccc;">Fecha</th>
            <th width="20%" style="background-color:#f2f2f2; font-weight:bold; border:1px solid #ccc;">Medicamento (P.A)</th>
            <th width="20%" style="background-color:#f2f2f2; font-weight:bold; border:1px solid #ccc;">Motivo Baja</th>
            <th width="20%" style="background-color:#f2f2f2; font-weight:bold; border:1px solid #ccc;">Cant.</th>
            <th width="20%" style="background-color:#f2f2f2; font-weight:bold; border:1px solid #ccc;">Registrado por</th>
        </tr>';
        break;

    case 'ajustes':
        $usa_fechas = true;
        $titulo_reporte = "REPORTE DE AJUSTES (AUDITORÍA)";
        // Ajustes: Ajuste Cuadre Entrada(6), Salida(7), Reversión Entrada(8), Reversión Salida(9)
        $sql = "SELECT di.fecha AS fecha_movimiento, m.nombre_medicamento, mdi.cantidad, tm.nombre as tipo_movimiento,
                       CONCAT(per.nombre, ' ', IFNULL(per.apellido, '')) AS Usuario, l.Lote,
                       $sub_query_principios
                FROM medicamentos_detalle_inventario mdi
                INNER JOIN detalle_inventario di ON mdi.Id_detalle_inventario = di.Id_detalle_inventario
                INNER JOIN tipo_movimiento tm ON di.Id_TipoMovimiento = tm.Id_tipo_movimiento
                INNER JOIN descripcion_medicamento dm ON mdi.Id_descripcion_medicamento = dm.Id
                INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
                INNER JOIN lotes_medicamentos l ON mdi.Id_lote = l.Id
                INNER JOIN persona per ON di.Id_Persona = per.id
                WHERE tm.Id_tipo_movimiento IN (6, 7, 8, 9) 
                AND DATE(di.fecha) BETWEEN '$desde' AND '$hasta'
                ORDER BY di.fecha DESC";

        $header_tabla = '<tr>
            <th width="20%" style="background-color:#f2f2f2; font-weight:bold; border:1px solid #ccc;">Fecha</th>
            <th width="20%" style="background-color:#f2f2f2; font-weight:bold; border:1px solid #ccc;">Medicamento (P.A)</th>
            <th width="20%" style="background-color:#f2f2f2; font-weight:bold; border:1px solid #ccc;">Tipo Ajuste</th>
            <th width="20%" style="background-color:#f2f2f2; font-weight:bold; border:1px solid #ccc;">Cant.</th>
            <th width="20%" style="background-color:#f2f2f2; font-weight:bold; border:1px solid #ccc;">Auditor</th>
        </tr>';
        break;

    case 'consumo':
        $usa_fechas = true;
        $titulo_reporte = "CONSUMO MENSUAL / DISPENSACIÓN TOTAL";
        $sql = "SELECT m.nombre_medicamento, SUM(mdi.cantidad) as total_consumido,
                       $sub_query_principios
                FROM medicamentos_detalle_inventario mdi
                INNER JOIN detalle_inventario di ON mdi.Id_detalle_inventario = di.Id_detalle_inventario
                INNER JOIN descripcion_medicamento dm ON mdi.Id_descripcion_medicamento = dm.Id
                INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
                WHERE di.Id_TipoMovimiento = 2 
                AND DATE(di.fecha) BETWEEN '$desde' AND '$hasta'
                $filtro_usuario
                GROUP BY dm.Id, m.nombre_medicamento
                ORDER BY total_consumido DESC";

        $header_tabla = '<tr>
            <th width="50%" style="background-color:#f2f2f2; font-weight:bold; border:1px solid #ccc;">Medicamento (P.A)</th>
            <th width="50%" style="background-color:#f2f2f2; font-weight:bold; border:1px solid #ccc;">Total Dispensado en el Periodo</th>
        </tr>';
        break;
}

// Ejecutar Consulta
$resultado = mysqli_query($conexion, $sql);

// Estilos CSS para el contenido
$style = '
<style>
    h1 { color: #333; font-size: 16pt; text-align: center; }
    .periodo { text-align: center; color: #666; font-size: 10pt; margin-bottom: 20px; }
    table { border-collapse: collapse; font-size: 9pt; }
    th { text-align: center; padding: 8px; }
    td { border: 1px solid #eee; padding: 6px; vertical-align: middle; }
    .vencido { color: red; font-weight: bold; }
</style>';

$html = $style;
$html .= '<h1>' . $titulo_reporte . '</h1>';
if ($usa_fechas && $desde != '') {
    $html .= '<p class="periodo">Periodo: ' . date('d/m/Y', strtotime($desde)) . ' al ' . date('d/m/Y', strtotime($hasta)) . '</p>';
}

$html .= '<table cellpadding="5" cellspacing="0" width="100%">
            <thead>' . $header_tabla . '</thead>
            <tbody>';

if ($resultado && mysqli_num_rows($resultado) > 0) {
    $fill = false;
    while ($row = mysqli_fetch_assoc($resultado)) {
        $bgcolor = ($fill) ? '#f9f9f9' : '#ffffff';
        $html .= '<tr style="background-color:' . $bgcolor . ';">';
        
        // Unificar el nombre del medicamento con sus principios activos
        $principios = !empty($row['principios_activos']) ? ' (' . $row['principios_activos'] . ')' : '';
        $nombre_med = $row['nombre_medicamento'] . $principios;

        if ($tipo == 'existencia') {
            $html .= '<td>' . $nombre_med . '</td>';
            $html .= '<td align="center">' . $row['Lote'] . '</td>';
            $html .= '<td align="center">' . date('d/m/Y', strtotime($row['fecha_vencimiento'])) . '</td>';
            $html .= '<td align="center">' . $row['nombre_presentacion'] . ' (' . $row['contenido_neto'] . ')</td>';
            $html .= '<td align="center"><b>' . $row['cantidad_actual'] . '</b></td>';
        } elseif ($tipo == 'vencimientos') {
            $clase = (strtotime($row['fecha_vencimiento']) < strtotime('+3 month')) ? 'class="vencido"' : '';
            $html .= '<td>' . $nombre_med . '</td>';
            $html .= '<td align="center">' . $row['Lote'] . '</td>';
            $html .= '<td align="center" '.$clase.'>' . date('d/m/Y', strtotime($row['fecha_vencimiento'])) . '</td>';
            $html .= '<td align="center">' . $row['cantidad_actual'] . '</td>';
        } elseif ($tipo == 'entradas') {
            $html .= '<td>' . date('d/m/Y H:i', strtotime($row['fecha_movimiento'])) . '</td>';
            $html .= '<td>' . $nombre_med . '<br><small>Lote: ' . $row['Lote'] . '</small></td>';
            $html .= '<td align="center">' . $row['cantidad'] . '</td>';
            $html .= '<td>' . $row['Usuario'] . '</td>';
            
        } elseif ($tipo == 'despacho') {
            $html .= '<td>' . date('d/m/Y H:i', strtotime($row['fecha_movimiento'])) . '</td>';
            $html .= '<td>' . $nombre_med . '<br><small>Lote: ' . $row['Lote'] . '</small></td>';
            $html .= '<td align="center">' . $row['cantidad'] . '</td>';
            
            // Lógica para determinar quién es el paciente
            $paciente_info = "-";
            if (!empty($row['pac_nombre'])) {
                // Es interno
                if (!empty($row['rep_nombre'])) {
                    // Es un menor (mostramos paciente y representante)
                    $paciente_info = "<b>Paciente:</b> " . $row['pac_nombre'] . " " . $row['pac_apellido'] . "<br><small><b>Rep:</b> " . $row['rep_nombre'] . " " . $row['rep_apellido'] . " (" . $row['rep_tipo_cedula'] . "-" . $row['rep_cedula'] . ")</small>";
                } else {
                    // Es adulto
                    $paciente_info = $row['pac_nombre'] . " " . $row['pac_apellido'] . "<br><small>(" . $row['pac_tipo_cedula'] . "-" . $row['pac_cedula'] . ")</small>";
                }
            } else if (!empty($row['ext_nombre'])) {
                // Es externo
                $paciente_info = $row['ext_nombre'] . "<br><small>Externo (" . $row['ext_tipo_cedula'] . "-" . $row['ext_cedula'] . ")</small>";
            } else {
                // Respaldo en caso de que sea un registro viejo o manual: lee las observaciones directamente
                $paciente_info = "<small>" . htmlspecialchars($row['observaciones']) . "</small>";
            }

            $html .= '<td>' . $paciente_info . '</td>';
            $html .= '<td>' . $row['Usuario'] . '</td>';
            
        } elseif ($tipo == 'bajas' || $tipo == 'ajustes') {
            $html .= '<td>' . date('d/m/Y H:i', strtotime($row['fecha_movimiento'])) . '</td>';
            $html .= '<td>' . $nombre_med . ' <br><small>Lote: ' . $row['Lote'] . '</small></td>';
            $html .= '<td>' . $row['tipo_movimiento'] . '</td>';
            $html .= '<td align="center">' . $row['cantidad'] . '</td>';
            $html .= '<td>' . $row['Usuario'] . '</td>';
        } elseif ($tipo == 'consumo') {
            $html .= '<td>' . $nombre_med . '</td>';
            $html .= '<td align="center"><b>' . $row['total_consumido'] . '</b> Unds.</td>';
        }
        
        $html .= '</tr>';
        $fill = !$fill;
    }
} else {
    $columnas = 5;
    if ($tipo == 'vencimientos' || $tipo == 'entradas') $columnas = 4;
    if ($tipo == 'consumo') $columnas = 2;
    $html .= '<tr><td colspan="'.$columnas.'" align="center">No se encontraron registros en este periodo.</td></tr>';
}

$html .= '</tbody></table>';

// Firmas al final
$html .= '<br><br><br><br>
<table width="100%" style="text-align:center; border: none;">
    <tr>
        <td>__________________________<br>Responsable de Farmacia</td>
        <td>__________________________<br>Coordinación ASIC</td>
    </tr>
</table>';

$pdf->writeHTML($html, true, false, true, false, '');
ob_end_clean();
$pdf->Output('reporte_inventario_' . date('dmY') . '.pdf', 'I');
?>