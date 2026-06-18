<?php
ob_start();
require_once('../../plugins/vendor/tcpdf/tcpdf.php'); 
include('../conexion.php');

$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'general';

// 1. LÓGICA DE CONSULTA SQL
switch ($tipo) {
    case 'activos':
        $titulo = "CATÁLOGO DE SÍNTOMAS ACTIVOS";
        $subtitulo = "Listado de sintomatología clínica disponible";
        $sql = "SELECT * FROM sintomas WHERE estatus = 1 ORDER BY nombre_sintoma ASC";
        break;
    case 'inactivos':
        $titulo = "CATÁLOGO DE SÍNTOMAS INACTIVOS";
        $subtitulo = "Listado de sintomatología desincorporada";
        $sql = "SELECT * FROM sintomas WHERE estatus = 0 ORDER BY nombre_sintoma ASC";
        break;
    default:
        $titulo = "CATÁLOGO MAESTRO DE SÍNTOMAS";
        $subtitulo = "Registro general de signos y síntomas clínicos";
        $sql = "SELECT * FROM sintomas ORDER BY nombre_sintoma ASC";
        break;
}

$resultado = mysqli_query($conexion, $sql);

// 2. CLASE EXTENDIDA
class MYPDF extends TCPDF {
    public function Header() {
        $this->SetFont('helvetica', 'B', 14);
        $this->SetTextColor(44, 62, 80); 
        $this->Cell(0, 10, 'CONSULTORIO POPULAR TIPO 3 - REGISTRO CLÍNICO MAESTRO', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(6);
        $this->SetFont('helvetica', '', 10);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 10, 'Departamento de Epidemiología y Estadísticas de Salud', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $style = array('width' => 0.5, 'color' => array(44, 62, 80));
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
    h2 { text-align: center; color: #2C3E50; font-size: 14pt; margin-bottom: 2px; }
    h4 { text-align: center; color: #666666; font-size: 11pt; font-weight: normal; margin-top: 0px; margin-bottom: 15px; }
    table { border-collapse: collapse; width: 100%; }
    th { background-color: #34495E; color: white; font-weight: bold; text-align: center; border: 1px solid #cccccc; }
    td { border: 1px solid #cccccc; text-align: center; font-size: 10pt; color: #333333; }
    .row-even { background-color: #f8f9fa; }
    .row-odd { background-color: #ffffff; }
    .status-activo { color: #155724; font-weight: bold; }
    .status-inactivo { color: #721c24; font-weight: bold; }
</style>
<h2>' . $titulo . '</h2>
<h4>' . $subtitulo . '</h4>
<table cellpadding="6">
    <thead>
        <tr>
            <th width="10%">Nº</th>
            <th width="70%">Descripción del Síntoma Clínico</th>
            <th width="20%">Estatus</th>
        </tr>
    </thead>
    <tbody>';

$i = 0;
$count = 1;
if(mysqli_num_rows($resultado) > 0){
    while ($row = mysqli_fetch_assoc($resultado)) {
        $estado_clase = ($row['estatus'] == 1) ? 'status-activo' : 'status-inactivo';
        $estado_texto = ($row['estatus'] == 1) ? 'Activo' : 'Inactivo';
        $clase = ($i % 2 == 0) ? 'row-even' : 'row-odd';

        $html .= '<tr class="'.$clase.'">
                    <td width="10%">'.$count.'</td>
                    <td width="70%" style="text-align:left;">'.mb_strtoupper($row['nombre_sintoma']).'</td>
                    <td width="20%" class="'.$estado_clase.'">'.$estado_texto.'</td>
                  </tr>';
        $i++;
        $count++;
    }
} else {
    $html .= '<tr><td colspan="3">No hay registros para mostrar.</td></tr>';
}

$html .= '</tbody></table>';

if (ob_get_length()) ob_end_clean();
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Catalogo_Sintomas.pdf', 'I');
?>