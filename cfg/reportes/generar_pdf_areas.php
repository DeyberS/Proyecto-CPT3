<?php
ob_start();
require_once('../../plugins/vendor/tcpdf/tcpdf.php'); 
include('../conexion.php');

$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'general';

// 1. LÓGICA DE CONSULTA SQL PARA ÁREAS
switch ($tipo) {
    case 'activas':
        $titulo = "REPORTE DE ÁREAS ACTIVAS";
        $subtitulo = "Departamentos operativos en la institución";
        $sql = "SELECT * FROM departamento WHERE estatus = 1 ORDER BY nombre_departamento ASC";
        break;
    case 'inactivas':
        $titulo = "REPORTE DE ÁREAS INACTIVAS";
        $subtitulo = "Departamentos desincorporados o clausurados";
        $sql = "SELECT * FROM departamento WHERE estatus = 0 ORDER BY nombre_departamento ASC";
        break;
    default:
        $titulo = "LISTADO GENERAL DE ÁREAS";
        $subtitulo = "Catálogo maestro de departamentos institucionales";
        $sql = "SELECT * FROM departamento ORDER BY nombre_departamento ASC";
        break;
}

$resultado = mysqli_query($conexion, $sql);

// 2. CLASE EXTENDIDA PARA MEMBRETE ADMINISTRATIVO
class MYPDF extends TCPDF {
    public function Header() {
        $this->SetFont('helvetica', 'B', 14);
        $this->SetTextColor(26, 82, 118); // Azul Corporativo/Institucional
        $this->Cell(0, 10, 'CONSULTORIO POPULAR TIPO 3 - RECURSOS HUMANOS', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(6);
        $this->SetFont('helvetica', '', 10);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 10, 'Sistema de Gestión y Organización Administrativa', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $style = array('width' => 0.5, 'color' => array(26, 82, 118));
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
    h2 { text-align: center; color: #1A5276; font-size: 14pt; margin-bottom: 2px; }
    h4 { text-align: center; color: #666666; font-size: 11pt; font-weight: normal; margin-top: 0px; margin-bottom: 15px; }
    table { border-collapse: collapse; width: 100%; }
    th { background-color: #2980B9; color: white; font-weight: bold; text-align: center; border: 1px solid #cccccc; }
    td { border: 1px solid #cccccc; text-align: center; font-size: 10pt; color: #333333; }
    .row-even { background-color: #ebf5fb; }
    .row-odd { background-color: #ffffff; }
    .status-activo { color: #155724; font-weight: bold; }
    .status-inactivo { color: #721c24; font-weight: bold; }
</style>
<h2>' . $titulo . '</h2>
<h4>' . $subtitulo . '</h4>
<table cellpadding="6">
    <thead>
        <tr>
            <th width="15%">Nº</th>
            <th width="65%">Nombre del Área o Departamento</th>
            <th width="20%">Estatus</th>
        </tr>
    </thead>
    <tbody>';

$i = 0;
$count = 1;
if(mysqli_num_rows($resultado) > 0){
    while ($row = mysqli_fetch_assoc($resultado)) {
        $estado_clase = ($row['estatus'] == 1) ? 'status-activo' : 'status-inactivo';
        $estado_texto = ($row['estatus'] == 1) ? 'Activa' : 'Inactiva';
        $clase = ($i % 2 == 0) ? 'row-even' : 'row-odd';

        $html .= '<tr class="'.$clase.'">
                    <td width="15%">'.$count.'</td>
                    <td width="65%" style="text-align:left;">'.mb_strtoupper($row['nombre_departamento']).'</td>
                    <td width="20%" class="'.$estado_clase.'">'.$estado_texto.'</td>
                  </tr>';
        $i++;
        $count++;
    }
} else {
    $html .= '<tr><td colspan="3">No hay áreas registradas para mostrar.</td></tr>';
}

$html .= '</tbody></table>';

if (ob_get_length()) ob_end_clean();
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Reporte_Areas_CPT3.pdf', 'I');
?>