<?php
ob_start(); 
require_once('../../plugins/vendor/tcpdf/tcpdf.php'); 
include('../conexion.php');

$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'general';

// CONSULTA BASE CORREGIDA: Se cambió DPM.Id_detalle_paciente_menor por DPM.id_persona
$sql_base = "SELECT 
                r.Id_rol,
                PM.tipo_cedula AS tipo_documento_menor,
                PM.cedula AS cedula_menor, 
                PM.nombre AS nombre_menor, 
                PM.apellido AS apellido_menor, 
                PM.fecha_nacimiento,
                PM.genero,
                PM.estatus,
                DPM.parentesco,
                PR.nombre AS nombre_rep, 
                PR.apellido AS apellido_rep
             FROM detalle_paciente_menor DPM
             INNER JOIN persona PM ON DPM.id_persona = PM.id 
             INNER JOIN persona PR ON DPM.id_representante = PR.id
             INNER JOIN detalle_persona_rol dpr ON PM.id = dpr.Id_persona 
             INNER JOIN rol r ON dpr.Id_rol = r.Id_rol
             WHERE TIMESTAMPDIFF(YEAR, PM.fecha_nacimiento, CURDATE()) < 18";

switch ($tipo) {
    case 'activos':
        $titulo = "PACIENTES MENORES DE EDAD ACTIVOS";
        $subtitulo = "Infantes y adolescentes vigentes en el sistema";
        $sql = $sql_base . " AND r.Id_rol = 3 AND PM.estatus = 1 ORDER BY PM.apellido ASC";
        break;
    case 'representante':
        $titulo = "PACIENTES MENORES POR REPRESENTANTE";
        $subtitulo = "Ordenados por filiación al representante legal";
        $sql = $sql_base . " AND r.Id_rol = 3 ORDER BY PR.apellido ASC, PM.apellido ASC";
        break;
    case 'parentesco':
        $titulo = "PACIENTES MENORES POR PARENTESCO";
        $subtitulo = "Clasificados según el grado de consanguinidad";
        $sql = $sql_base . " AND r.Id_rol = 3 ORDER BY DPM.parentesco ASC, PM.apellido ASC";
        break;
    default:
        $titulo = "CENSO GENERAL DE PACIENTES MENORES";
        $subtitulo = "Histórico de registros pediátricos";
        $sql = $sql_base . " AND r.Id_rol = 3 ORDER BY PM.apellido ASC";
        break;
}

$resultado = mysqli_query($conexion, $sql);

// CLASE EXTENDIDA
class MYPDF extends TCPDF {
    public function Header() {
        $this->SetFont('helvetica', 'B', 14);
        $this->SetTextColor(0, 121, 107); 
        $this->Cell(0, 10, 'CONSULTORIO POPULAR TIPO 3 - PACIENTES MENORES DE EDAD', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(6);
        $this->SetFont('helvetica', '', 10);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 10, 'Registro Demográfico de Pacientes Menores', 0, false, 'C', 0, '', 0, false, 'M', 'M');
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
            <th width="15%">Documento</th>
            <th width="30%">Nombre del Menor</th>
            <th width="10%">Edad</th>
            <th width="15%">Parentesco</th>
            <th width="30%">Representante</th>
        </tr>
    </thead>
    <tbody>';

if(mysqli_num_rows($resultado) > 0){
    $i = 0;
    while ($row = mysqli_fetch_assoc($resultado)) {

        // Cálculo de edad preciso (años, meses o días)
        $fecha_nac = new DateTime($row['fecha_nacimiento']);
        $hoy = new DateTime();
        $diff = $hoy->diff($fecha_nac);

        // Lógica para determinar el formato de la edad
        if ($diff->y >= 1) {
            $edad_texto = $diff->y . ($diff->y == 1 ? ' año' : ' años');
        } elseif ($diff->m >= 1) {
            $edad_texto = $diff->m . ($diff->m == 1 ? ' mes' : ' meses');
        } elseif ($diff->d >= 1) {
            $edad_texto = $diff->d . ($diff->d == 1 ? ' día' : ' días');
        } else {
            $edad_texto = 'Recién nacido';
        }

        $clase = ($i % 2 == 0) ? 'row-even' : 'row-odd';
        
        $doc_menor = (!empty($row['cedula_menor'])) ? $row['tipo_documento_menor']."-".$row['cedula_menor'] : 'N/A';

        $html .= '<tr class="'.$clase.'">
                    <td width="15%">'.$doc_menor.'</td>
                    <td width="30%" style="text-align:left;">'.mb_strtoupper($row['apellido_menor'].' '.$row['nombre_menor']).'</td>
                    <td width="10%">'.$edad_texto.'</td>
                    <td width="15%">'.ucfirst(strtolower($row['parentesco'])).'</td>
                    <td width="30%" style="text-align:left;">'.mb_strtoupper($row['apellido_rep'].' '.$row['nombre_rep']).'</td>
                  </tr>';
        $i++;
    }
} else {
    $html .= '<tr><td colspan="5">No se encontraron registros que coincidan con la búsqueda.</td></tr>';
}

$html .= '</tbody></table>';

if (ob_get_length()) ob_end_clean();
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Reporte_Pacientes_Menores_'.date('Ymd_Hi').'.pdf', 'I');
?>