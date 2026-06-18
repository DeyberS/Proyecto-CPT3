<?php
ob_start(); 
require_once('../../plugins/vendor/tcpdf/tcpdf.php'); 
include('../conexion.php');

$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'general';

// CONSULTA BASE CORREGIDA: Se cambió DPM.Id_detalle_paciente_menor por DPM.id_persona
$sql_base = "SELECT 
                PR.tipo_cedula,
                PR.cedula AS cedula_rep, 
                PR.nombre AS nombre_rep, 
                PR.apellido AS apellido_rep,
                DPM.parentesco,
                PR.fecha_nacimiento,
                PM.nombre AS nombre_menor,
                PM.apellido AS apellido_menor,
                PR.estatus
             FROM detalle_paciente_menor DPM
             INNER JOIN persona PM ON DPM.id_persona = PM.id 
             INNER JOIN persona PR ON DPM.id_representante = PR.id
             INNER JOIN detalle_persona_rol dpr ON PR.id = dpr.Id_persona 
             INNER JOIN rol r ON dpr.Id_rol = r.Id_rol";

switch ($tipo) {
    case 'activos':
        $titulo = "LISTADO DE REPRESENTANTES ACTIVOS";
        $subtitulo = "Representantes con estatus vigente en el sistema";
        $sql = $sql_base . " WHERE r.Id_rol = 5 AND TIMESTAMPDIFF(YEAR, PR.fecha_nacimiento, CURDATE()) >= 18 AND PR.estatus = 1 ORDER BY PR.apellido ASC";
        break;
    case 'inactivos':
        $titulo = "LISTADO DE REPRESENTANTES INACTIVOS";
        $subtitulo = "Representantes no vigentes en el sistema";
        $sql = $sql_base . " WHERE r.Id_rol = 5 AND TIMESTAMPDIFF(YEAR, PR.fecha_nacimiento, CURDATE()) >= 18 AND PR.estatus = 0 ORDER BY PR.apellido ASC";
        break;
    default:
        $titulo = "CATÁLOGO GENERAL DE REPRESENTANTES Y MENORES";
        $subtitulo = "Vinculación completa de responsables legales y pacientes pediátricos";
        $sql = $sql_base . " WHERE r.Id_rol = 5 AND TIMESTAMPDIFF(YEAR, PR.fecha_nacimiento, CURDATE()) >= 18 ORDER BY PR.apellido ASC";
        break;
}

$resultado = mysqli_query($conexion, $sql);

// CLASE EXTENDIDA
class MYPDF extends TCPDF {
    public function Header() {
        $this->SetFont('helvetica', 'B', 14);
        $this->SetTextColor(0, 121, 107); 
        $this->Cell(0, 10, 'CONSULTORIO POPULAR TIPO 3 - REPRESENTANTES', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(6);
        $this->SetFont('helvetica', '', 10);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 10, 'Registro de Vinculación Familiar', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $style = array('width' => 0.5, 'color' => array(0, 121, 107));
        $this->Line(15, 22, $this->getPageWidth()-15, 22, $style);
    }
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(150, 150, 150);
        $this->Cell(0, 10, 'Generado el: ' . date('d/m/Y h:i A') . ' | Página '.$this->getAliasNumPage().' de '.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// CONFIGURACIÓN DEL PDF (Horizontal)
$pdf = new MYPDF('L', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetTitle($titulo);
$pdf->SetMargins(15, 30, 15);
$pdf->SetAutoPageBreak(TRUE, 15);
$pdf->AddPage();

$html = '
<style>
    h2 { text-align: center; color: #333333; font-size: 14pt; margin-bottom: 2px; }
    h4 { text-align: center; color: #666666; font-size: 11pt; font-weight: normal; margin-top: 0px; margin-bottom: 15px; }
    table { border-collapse: collapse; width: 100%; }
    th { background-color: #00796B; color: white; font-weight: bold; text-align: center; border: 1px solid #cccccc; }
    td { border: 1px solid #cccccc; text-align: center; font-size: 10pt; color: #333333; }
    .row-even { background-color: #f2f9f9; }
    .row-odd { background-color: #ffffff; }
</style>
<h2>' . $titulo . '</h2>
<h4>' . $subtitulo . '</h4>
<table cellpadding="6">
    <thead>
        <tr>
            <th width="15%">Cédula Rep.</th>
            <th width="35%">Nombre del Representante</th>
            <th width="15%">Parentesco</th>
            <th width="35%">Nombre del Menor (Paciente)</th>
        </tr>
    </thead>
    <tbody>';

if (mysqli_num_rows($resultado) > 0) {
    $i = 0;
    while ($row = mysqli_fetch_assoc($resultado)) {
        $clase = ($i % 2 == 0) ? 'row-even' : 'row-odd';
        $html .= '<tr class="'.$clase.'">
                    <td width="15%">'.$row['tipo_cedula'].'-'.$row['cedula_rep'].'</td>
                    <td width="35%" style="text-align:left;">'.mb_strtoupper($row['apellido_rep'].' '.$row['nombre_rep']).'</td>
                    <td width="15%">'.ucfirst(strtolower($row['parentesco'])).'</td>
                    <td width="35%" style="text-align:left;">'.mb_strtoupper($row['apellido_menor'].' '.$row['nombre_menor']).'</td>
                  </tr>';
        $i++;
    }
} else {
    $html .= '<tr><td colspan="4">No hay registros vinculados para mostrar.</td></tr>';
}

$html .= '</tbody></table>';

if (ob_get_length()) ob_end_clean();
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Reporte_Representantes_'.date('Ymd_Hi').'.pdf', 'I');
?>