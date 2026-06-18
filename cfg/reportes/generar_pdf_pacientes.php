<?php
ob_start(); 
require_once('../../plugins/vendor/tcpdf/tcpdf.php'); 
include('../conexion.php');

$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'general';

// CONSULTA BASE: persona + detalle_paciente[cite: 3]
$sql_base = "SELECT 
                r.Id_rol,
                p.tipo_cedula,
                p.cedula, 
                p.nombre, 
                p.apellido, 
                p.genero, 
                p.fecha_nacimiento, 
                hm.grupo_sanguineo, 
                p.estatus 
             FROM detalle_paciente dp
             INNER JOIN persona p ON dp.Id_persona = p.id
             INNER JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona 
             INNER JOIN rol r ON dpr.Id_rol = r.Id_rol
             INNER JOIN historial_medico hm ON hm.Id_persona = p.id"; //[cite: 3]

switch ($tipo) {
    case 'activos':
        $titulo = "LISTADO DE PACIENTES ACTIVOS"; //[cite: 3]
        $subtitulo = "Pacientes mayores de edad vigentes en el sistema";
        $sql = $sql_base . " WHERE p.estatus = 1 AND r.Id_rol = 3 AND TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) >= 18 ORDER BY p.apellido ASC"; //[cite: 3]
        break;
    case 'sangre':
        $titulo = "PACIENTES POR GRUPO SANGUÍNEO"; //[cite: 3]
        $subtitulo = "Clasificación hematológica de adultos";
        $sql = $sql_base . " WHERE r.Id_rol = 3 AND TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) >= 18 ORDER BY hm.grupo_sanguineo ASC, p.apellido ASC"; //[cite: 3]
        break;
    case 'femenino':
        $titulo = "LISTADO DE PACIENTES (FEMENINO)"; //[cite: 3]
        $subtitulo = "Pacientes adultas registradas";
        $sql = $sql_base . " WHERE p.genero = 'Femenino' AND r.Id_rol = 3 AND TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) >= 18 ORDER BY p.apellido ASC"; //[cite: 3]
        break;
    case 'masculino':
        $titulo = "LISTADO DE PACIENTES (MASCULINO)"; //[cite: 3]
        $subtitulo = "Pacientes adultos registrados";
        $sql = $sql_base . " WHERE p.genero = 'Masculino' AND r.Id_rol = 3 AND TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) >= 18 ORDER BY p.apellido ASC"; //[cite: 3]
        break;
    default:
        $titulo = "CENSO GENERAL DE PACIENTES (MAYORES)"; //[cite: 3]
        $subtitulo = "Todos los registros de pacientes adultos";
        $sql = $sql_base . " WHERE r.Id_rol = 3 AND TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) >= 18 ORDER BY p.apellido ASC"; //[cite: 3]
        break;
}

$resultado = mysqli_query($conexion, $sql);

// CLASE EXTENDIDA PARA MEMBRETE Y PIE DE PÁGINA
class MYPDF extends TCPDF {
    public function Header() {
        $this->SetFont('helvetica', 'B', 14);
        $this->SetTextColor(0, 121, 107); // Verde Teal
        $this->Cell(0, 10, 'CONSULTORIO POPULAR TIPO 3 - PACIENTES', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(6);
        $this->SetFont('helvetica', '', 10);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 10, 'Registro Demográfico de Pacientes', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        
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

// CONFIGURACIÓN DEL PDF
$pdf = new MYPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetTitle($titulo); //[cite: 3]
$pdf->SetMargins(15, 30, 15);
$pdf->SetAutoPageBreak(TRUE, 15);
$pdf->AddPage(); //[cite: 3]

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
            <th width="15%">Cédula</th>
            <th width="40%">Nombre Completo</th>
            <th width="15%">Edad</th>
            <th width="15%">Sexo</th>
            <th width="15%">T. Sangre</th>
        </tr>
    </thead>
    <tbody>';

$i = 0;
while ($row = mysqli_fetch_assoc($resultado)) {
    // Cálculo de edad[cite: 3]
    $cumpleanos = new DateTime($row['fecha_nacimiento']); //[cite: 3]
    $hoy = new DateTime(); //[cite: 3]
    $edad = $hoy->diff($cumpleanos)->y; //[cite: 3]
    
    $genero = ($row['genero'] == 'Masculino' || $row['genero'] == 'M') ? 'Masculino' : 'Femenino';
    $clase = ($i % 2 == 0) ? 'row-even' : 'row-odd';

    $html .= '<tr class="'.$clase.'">
                <td width="15%">'.$row['tipo_cedula']."-".$row['cedula'].'</td>
                <td width="40%" style="text-align:left;">'.mb_strtoupper($row['apellido'].' '.$row['nombre']).'</td>
                <td width="15%">'.$edad.'</td>
                <td width="15%">'.$genero.'</td>
                <td width="15%">'.(!empty($row['grupo_sanguineo']) ? $row['grupo_sanguineo'] : 'N/A').'</td>
              </tr>';
    $i++;
}

$html .= '</tbody></table>';

if (ob_get_length()) ob_end_clean(); //[cite: 3]
$pdf->writeHTML($html, true, false, true, false, ''); //[cite: 3]
$pdf->Output('Reporte_Pacientes_Mayores_'.date('Ymd_Hi').'.pdf', 'I'); //[cite: 3]
?>