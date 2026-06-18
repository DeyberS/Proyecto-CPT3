<?php
// Asegurar que no haya ninguna salida HTML previa
ob_start();

require_once('../../plugins/vendor/tcpdf/tcpdf.php'); 
include('../conexion.php');

$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'general';
$fecha_hoy = date('Y-m-d');
$fecha_limite = date('Y-m-d', strtotime($fecha_hoy . ' + 60 days')); // Margen de 2 meses

// 1. CONSULTA BASE CORREGIDA
// Se corrigió 'p.tipo_presentacion' por 'p.nombre_presentacion' y se solicitó 'l.Lote'
$sql_base = "SELECT 
                l.Lote,
                l.fecha_vencimiento,
                l.estado_lote,
                l.estatus,
                m.nombre_medicamento, 
                p.nombre_presentacion AS presentacion,
                ex.cantidad_actual AS cantidad
             FROM lotes_medicamentos l
             INNER JOIN descripcion_medicamento dm ON l.Id_descripcion_medicamento = dm.Id
             INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
             INNER JOIN presentacion p ON dm.Id_presentacion = p.Id_presentacion
             INNER JOIN existencias_stock ex ON l.Id = ex.Id_lote";

switch ($tipo) {
    case 'vencidos':
        $titulo = "REPORTE DE LOTES VENCIDOS";
        $subtitulo = "Medicamentos caducados que requieren desincorporación";
        $sql = $sql_base . " WHERE l.fecha_vencimiento < '$fecha_hoy' AND l.estatus = 1 ORDER BY l.fecha_vencimiento ASC";
        break;
    case 'proximos_vencer':
        $titulo = "LOTES PRÓXIMOS A VENCER";
        $subtitulo = "Medicamentos que expiran en los próximos 60 días";
        $sql = $sql_base . " WHERE l.fecha_vencimiento BETWEEN '$fecha_hoy' AND '$fecha_limite' AND l.estatus = 1 ORDER BY l.fecha_vencimiento ASC";
        break;
    case 'stock_bajo':
        $titulo = "ALERTA DE STOCK CRÍTICO EN LOTES";
        $subtitulo = "Lotes con existencias iguales o menores a 20 unidades";
        $sql = $sql_base . " WHERE ex.cantidad_actual <= 20 AND l.estatus = 1 ORDER BY ex.cantidad_actual ASC";
        break;
    case 'activos':
        $titulo = "LOTES ACTIVOS EN INVENTARIO";
        $subtitulo = "Todos los lotes operativos y en uso";
        $sql = $sql_base . " WHERE l.estatus = 1 ORDER BY ex.cantidad_actual ASC";
        break;
    default:
        $titulo = "INVENTARIO GENERAL DE LOTES";
        $subtitulo = "Registro histórico completo del almacén";
        $sql = $sql_base . " WHERE l.estatus = 1 ORDER BY l.fecha_vencimiento ASC";
        break;
}

$resultado = mysqli_query($conexion, $sql);

// 2. CLASE EXTENDIDA PARA MEMBRETE (DISEÑO FARMACIA)
class MYPDF extends TCPDF {
    public function Header() {
        $this->SetFont('helvetica', 'B', 14);
        $this->SetTextColor(30, 132, 73); // Verde Esmeralda Clínico
        $this->Cell(0, 10, 'CONSULTORIO POPULAR TIPO 3 - SERVICIO DE FARMACIA', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(6);
        $this->SetFont('helvetica', '', 10);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 10, 'Control y Gestión de Lotes de Medicamentos', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $style = array('width' => 0.5, 'color' => array(30, 132, 73));
        $this->Line(15, 22, $this->getPageWidth()-15, 22, $style);
    }
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(150, 150, 150);
        $this->Cell(0, 10, 'Generado el: ' . date('d/m/Y h:i A') . ' | Página '.$this->getAliasNumPage().' de '.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// 3. CONFIGURACIÓN DEL PDF
$pdf = new MYPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetTitle($titulo);
$pdf->SetMargins(15, 30, 15);
$pdf->SetAutoPageBreak(TRUE, 15);
$pdf->AddPage();

$html = '
<style>
    h2 { text-align: center; color: #1E8449; font-size: 14pt; margin-bottom: 2px; }
    h4 { text-align: center; color: #666666; font-size: 11pt; font-weight: normal; margin-top: 0px; margin-bottom: 15px; }
    table { border-collapse: collapse; width: 100%; }
    th { background-color: #27AE60; color: white; font-weight: bold; text-align: center; border: 1px solid #cccccc; }
    td { border: 1px solid #cccccc; text-align: center; font-size: 10pt; color: #333333; }
    .row-even { background-color: #e9f7ef; }
    .row-odd { background-color: #ffffff; }
    .alerta-roja { color: #c0392b; font-weight: bold; }
    .alerta-naranja { color: #d35400; font-weight: bold; }
</style>
<h2>' . $titulo . '</h2>
<h4>' . $subtitulo . '</h4>
<table cellpadding="6">
    <thead>
        <tr>
            <th width="35%">Medicamento (Presentación)</th>
            <th width="20%">Lote</th>
            <th width="15%">Cantidad</th>
            <th width="15%">Vencimiento</th>
            <th width="15%">Estado</th>
        </tr>
    </thead>
    <tbody>';

$i = 0;
if (mysqli_num_rows($resultado) > 0) {
    while ($row = mysqli_fetch_assoc($resultado)) {
        $clase = ($i % 2 == 0) ? 'row-even' : 'row-odd';
        $fecha_f = date('d/m/Y', strtotime($row['fecha_vencimiento']));

        // Lógica de colores para alertas visuales
        $vencimiento_html = $fecha_f;
        if ($row['fecha_vencimiento'] < $fecha_hoy) {
            $vencimiento_html = '<span class="alerta-roja">'.$fecha_f.'</span>';
        } elseif ($row['fecha_vencimiento'] <= $fecha_limite) {
            $vencimiento_html = '<span class="alerta-naranja">'.$fecha_f.'</span>';
        }

        $stock_html = $row['cantidad'];
        if ($row['cantidad'] <= 20) {
            $stock_html = '<span class="alerta-roja">'.$row['cantidad'].'</span>';
        }
        
        $estado = ($row['estatus'] == 1) ? $row['estado_lote'] : 'Inactivo';

        $html .= '<tr class="'.$clase.'">
                    <td width="35%" style="text-align:left;">'.mb_strtoupper($row['nombre_medicamento']).' <br><small style="color:#666;">['.$row['presentacion'].']</small></td>
                    <td width="20%">'.mb_strtoupper($row['Lote']).'</td>
                    <td width="15%">'.$stock_html.'</td>
                    <td width="15%">'.$vencimiento_html.'</td>
                    <td width="15%">'.$estado.'</td>
                  </tr>';
        $i++;
    }
} else {
    $html .= '<tr><td colspan="5">No se encontraron lotes registrados bajo este filtro.</td></tr>';
}

$html .= '</tbody></table>';

// Limpieza absoluta del buffer. Esto es lo que previene el error TCPDF
if (ob_get_length()) { ob_end_clean(); }

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Reporte_Lotes_Farmacia_'.date('Ymd_Hi').'.pdf', 'I');
?>