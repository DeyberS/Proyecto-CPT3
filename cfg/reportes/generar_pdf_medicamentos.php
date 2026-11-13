<?php
// 1. Iniciar buffer y silenciar errores de PHP que rompen TCPDF
ob_start();
error_reporting(0); 

require_once('../../plugins/vendor/tcpdf/tcpdf.php'); 
include('../conexion.php');

$tipo = $_GET['tipo'] ?? 'todos';

// CORRECCIÓN SQL: La columna en BD es nombre_presentacion, NO tipo_presentacion
$sql_base = "SELECT 
                dm.Id, 
                m.nombre_medicamento, 
                dm.estatus,
                p.nombre_presentacion AS presentacion
             FROM descripcion_medicamento dm
             INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
             INNER JOIN presentacion p ON dm.Id_presentacion = p.Id_presentacion";

switch ($tipo) {
    case 'activos':
        $titulo = "Catálogo de Medicamentos Disponibles";
        $subtitulo = "Listado de insumos médicos activos";
        // En tu BD el estatus de dm es enum('1','2'), donde 1 asumo es activo
        $sql = $sql_base . " WHERE dm.estatus = '1' ORDER BY m.nombre_medicamento ASC";
        break;
    case 'inactivos':
        $titulo = "Catálogo de Medicamentos Inactivos";
        $subtitulo = "Listado de insumos médicos inactivos/agotados";
        $sql = $sql_base . " WHERE dm.estatus = '2' ORDER BY m.nombre_medicamento ASC";
        break;
    case 'presentacion':
        $titulo = "Catálogo Agrupado por Presentación";
        $subtitulo = "Ordenado por tipo de presentación comercial";
        $sql = $sql_base . " ORDER BY presentacion ASC, m.nombre_medicamento ASC";
        break;
    default:
        $titulo = "Catálogo General de Medicamentos";
        $subtitulo = "Inventario completo de conceptos registrados";
        $sql = $sql_base . " ORDER BY m.nombre_medicamento ASC";
        break;
}

$resultado = mysqli_query($conexion, $sql);

// 2. DISEÑO PROFESIONAL TCPDF
class MYPDF extends TCPDF {
    public function Header() {
        $this->SetY(10);
        $this->SetFont('helvetica', 'B', 13);
        $this->SetTextColor(19, 115, 115); 
        $this->Cell(0, 10, 'CONSULTORIO POPULAR TIPO 3 - GESTIÓN DE FARMACIA', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Line(15, 20, 195, 20); 
    }
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(127, 140, 141); 
        $this->Cell(0, 10, 'Generado el: ' . date('d/m/Y H:i:s') . ' | Página '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

$pdf = new MYPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle($titulo);
$pdf->SetMargins(15, 25, 15);
$pdf->SetAutoPageBreak(TRUE, 20);
$pdf->AddPage();

$html = '
<style>
    h1 { text-align: center; color: #2c3e50; font-size: 15pt; margin-bottom: 0px; text-transform: uppercase; }
    h3 { text-align: center; color: #7f8c8d; font-size: 11pt; margin-top: 5px; font-weight: normal; }
    table.table-med { border-collapse: collapse; width: 100%; margin-top: 25px; }
    th { background-color: #137373; color: #ffffff; font-weight: bold; border: 1px solid #0e5252; text-align: center; padding: 10px; font-size: 10pt; }
    td { border: 1px solid #bdc3c7; padding: 10px; font-size: 9.5pt; text-align: center; }
</style>
<h1>' . $titulo . '</h1>
<h3>' . $subtitulo . '</h3>
<br>
<table class="table-med" cellpadding="6">
    <thead>
        <tr>
            <th width="50%">Nombre del Medicamento</th>
            <th width="30%">Presentación</th>
            <th width="20%">Estatus</th>
        </tr>
    </thead>
    <tbody>';

if ($resultado && mysqli_num_rows($resultado) > 0) {
    $fill = false; 
    while ($row = mysqli_fetch_assoc($resultado)) {
        $color_fila = $fill ? '#f4f9f9' : '#ffffff';
        $fill = !$fill;

        if ($row['estatus'] == '1') {
            $bg_estado = '#e6f4ea'; $color_estado = '#137333'; $texto_estado = 'Activo';
        } else {
            $bg_estado = '#fce8e6'; $color_estado = '#c5221f'; $texto_estado = 'Inactivo';
        }

        $html .= '<tr bgcolor="' . $color_fila . '">
                    <td width="50%" align="left"><b>' . mb_strtoupper($row['nombre_medicamento'], 'UTF-8') . '</b></td>
                    <td width="30%">' . $row['presentacion'] . '</td>
                    <td width="20%" bgcolor="' . $bg_estado . '" style="color:' . $color_estado . '; font-weight:bold;">' . $texto_estado . '</td>
                  </tr>';
    }
} else {
    $html .= '<tr><td colspan="3" style="color: #7f8c8d;">No se encontraron medicamentos en esta categoría.</td></tr>';
}

$html .= '</tbody></table>';
$pdf->writeHTML($html, true, false, true, false, '');

// Limpieza agresiva del buffer para evitar el error TCPDF
while (ob_get_level() > 0) {
    ob_end_clean();
}

$pdf->Output('Reporte_Medicamentos.pdf', 'I');
?>