<?php
ob_start();
require_once('../../plugins/vendor/tcpdf/tcpdf.php'); 
include('../conexion.php');

$tipo = $_GET['tipo'];

// CONSULTA SQL REAL SEGÚN TU BD
// PM = Persona Menor (Paciente)
// PR = Persona Representante
$sql_base = "SELECT 
                PR.cedula AS cedula_rep, 
                PR.nombre AS nombre_rep, 
                PR.apellido AS apellido_rep,
                DPM.parentesco,
                PR.fecha_nacimiento,
                PM.nombre AS nombre_menor,
                PM.apellido AS apellido_menor,
                PR.estatus,
                PR.fecha_nacimiento
             FROM detalle_paciente_menor DPM
             INNER JOIN persona PM ON DPM.Id_detalle_paciente_menor = PM.id
             INNER JOIN persona PR ON DPM.Id_representante = PR.id
             INNER JOIN detalle_persona_rol dpr ON PR.id = dpr.Id_persona 
             INNER JOIN rol r ON dpr.Id_rol = r.Id_rol";

switch ($tipo) {
    case 'activos':
        $titulo = "LISTADO DE REPRESENTANTES ACTIVOS";
        $sql = $sql_base . " WHERE r.Id_rol = 5 AND TIMESTAMPDIFF(YEAR, PR.fecha_nacimiento, CURDATE()) >= 18 AND PR.estatus = 1 ORDER BY PR.apellido ASC";
        break;
    case 'inactivos':
        $titulo = "LISTADO DE REPRESENTANTES INACTIVOS";
        $sql = $sql_base . " WHERE r.Id_rol = 5 AND TIMESTAMPDIFF(YEAR, PR.fecha_nacimiento, CURDATE()) >= 18 AND PR.estatus = 0 ORDER BY PR.apellido ASC";
        break;
    default:
        $titulo = "CATÁLOGO GENERAL DE REPRESENTANTES Y MENORES";
        $sql = $sql_base . " WHERE r.Id_rol = 5 AND TIMESTAMPDIFF(YEAR, PR.fecha_nacimiento, CURDATE()) >= 18 ORDER BY PR.apellido ASC";
        break;
}

$resultado = mysqli_query($conexion, $sql);

// CONFIGURACIÓN DEL PDF (Horizontal para mejor legibilidad)
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetTitle($titulo);
$pdf->setPrintHeader(false);
$pdf->AddPage();

$html = '
<style>
    h1 { text-align: center; color: #333; font-size: 14pt; }
    table { border-collapse: collapse; width: 100%; }
    th { background-color: #337ab7; color: white; font-weight: bold; border: 1px solid #000; text-align: center; }
    td { border: 1px solid #ccc; text-align: center; font-size: 10pt; }
</style>
<h1>' . $titulo . '</h1>
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
    while ($row = mysqli_fetch_assoc($resultado)) {
        $estado = ($row['estatus'] == 1) ? 'Activo' : 'Inactivo';
        $html .= '<tr>
                    <td>'.$row['cedula_rep'].'</td>
                    <td style="text-align:left;">'.mb_strtoupper($row['apellido_rep'].' '.$row['nombre_rep']).'</td>
                    <td>'.ucfirst(strtolower($row['parentesco'])).'</td>
                    <td style="text-align:left;">'.mb_strtoupper($row['apellido_menor'].' '.$row['nombre_menor']).'</td>
                  </tr>';
    }
} else {
    $html .= '<tr><td colspan="5">No hay registros vinculados</td></tr>';
}

$html .= '</tbody></table>';

if (ob_get_contents()) ob_end_clean();
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Reporte_Representantes_Menores.pdf', 'I');


