<?php
ob_start();
require_once('../../plugins/vendor/tcpdf/tcpdf.php'); 
include('../conexion.php');

$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'general';

// CONSULTA MAESTRA CORREGIDA:
// Se añadió dm.tipo_medico a la consulta y se filtrará por Rol = 7 (Médico Activo)
$sql_base = "SELECT 
                r.Id_rol,
                p.tipo_cedula,
                p.cedula, 
                p.nombre, 
                p.apellido, 
                p.estatus,
                dm.tipo_medico,
                e.nombre_especialidad
             FROM detalle_medico dm
             INNER JOIN persona p ON dm.Id_persona = p.id
             INNER JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona 
             INNER JOIN rol r ON dpr.Id_rol = r.Id_rol
             INNER JOIN especialidades_medicos em ON dm.Id_detalle_medico = em.Id_detalle_medico
             INNER JOIN especialidad e ON em.Id_especialidad = e.Id_especialidad";

switch ($tipo) {
    case 'activos':
        $titulo = "LISTADO DE MÉDICOS ACTIVOS";
        $subtitulo = "Personal médico con estatus vigente en el sistema";
        $sql = $sql_base . " WHERE p.estatus IN (1, 2) AND r.Id_rol = 7 ORDER BY p.apellido ASC";
        break;
    case 'inactivos':
        $titulo = "LISTADO DE MÉDICOS INACTIVOS";
        $subtitulo = "Personal médico inactivo o retirado";
        $sql = $sql_base . " WHERE p.estatus = 0 AND r.Id_rol = 7 ORDER BY p.apellido ASC";
        break;
    case 'internos':
        $titulo = "DIRECTORIO DE MÉDICOS INTERNOS";
        $subtitulo = "Personal de planta y nómina directa de la institución";
        $sql = $sql_base . " WHERE dm.tipo_medico = 'Interno' AND r.Id_rol = 7 ORDER BY p.apellido ASC";
        break;
    case 'externos':
        $titulo = "DIRECTORIO DE MÉDICOS EXTERNOS";
        $subtitulo = "Personal especialista invitado o por honorarios";
        $sql = $sql_base . " WHERE dm.tipo_medico = 'Externo' AND r.Id_rol = 7 ORDER BY p.apellido ASC";
        break;
    case 'especialidad':
        $titulo = "PERSONAL MÉDICO POR ESPECIALIDAD";
        $subtitulo = "Agrupados por área de experticia profesional";
        $sql = $sql_base . " WHERE r.Id_rol = 7 ORDER BY e.nombre_especialidad ASC, p.apellido ASC";
        break;
    default:
        $titulo = "DIRECTORIO MÉDICO GENERAL";
        $subtitulo = "Registro completo de profesionales de la salud";
        $sql = $sql_base . " WHERE r.Id_rol = 7 ORDER BY p.apellido ASC";
        break;
}

$resultado = mysqli_query($conexion, $sql);

// CLASE EXTENDIDA
class MYPDF extends TCPDF {
    public function Header() {
        $this->SetFont('helvetica', 'B', 14);
        $this->SetTextColor(26, 82, 118); 
        $this->Cell(0, 10, 'CONSULTORIO POPULAR TIPO 3 - RECURSOS HUMANOS', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(6);
        $this->SetFont('helvetica', '', 10);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 10, 'Nómina y Directorio de Personal Médico', 0, false, 'C', 0, '', 0, false, 'M', 'M');
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

// CONFIGURACIÓN DEL PDF
$pdf = new MYPDF('L', 'mm', 'A4', true, 'UTF-8', false);
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
            <th width="15%">Cédula</th>
            <th width="35%">Nombre del Médico</th>
            <th width="20%">Especialidad</th>
            <th width="15%">Tipo</th>
            <th width="15%">Estatus</th>
        </tr>
    </thead>
    <tbody>';

$i = 0;
if (mysqli_num_rows($resultado) > 0) {
    while ($row = mysqli_fetch_assoc($resultado)) {
        $estado_clase = ($row['estatus'] == 1 || $row['estatus'] == 2) ? 'status-activo' : 'status-inactivo';
        $estado_texto = ($row['estatus'] == 1 || $row['estatus'] == 2) ? 'Activo' : 'Inactivo';
        $clase = ($i % 2 == 0) ? 'row-even' : 'row-odd';
        $documento = (!empty($row['cedula'])) ? $row['tipo_cedula'].'-'.$row['cedula'] : 'N/A';

        $html .= '<tr class="'.$clase.'">
                    <td width="15%">'.$documento.'</td>
                    <td width="35%" style="text-align:left;">Dr(a). '.mb_strtoupper($row['apellido'].' '.$row['nombre']).'</td>
                    <td width="20%">'.$row['nombre_especialidad'].'</td>
                    <td width="15%">'.strtoupper($row['tipo_medico']).'</td>
                    <td width="15%" class="'.$estado_clase.'">'.$estado_texto.'</td>
                  </tr>';
        $i++;
    }
} else {
    $html .= '<tr><td colspan="5">No se encontraron médicos registrados según el filtro seleccionado.</td></tr>';
}

$html .= '</tbody></table>';

if (ob_get_length()) ob_end_clean();
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Directorio_Medicos.pdf', 'I');
?>