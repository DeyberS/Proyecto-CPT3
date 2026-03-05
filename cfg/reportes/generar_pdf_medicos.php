<?php
ob_start();
require_once('../../plugins/vendor/tcpdf/tcpdf.php'); 
include('../conexion.php');

$tipo = $_GET['tipo'];

// CONSULTA MAESTRA CON LA RELACIÓN REAL:
// persona -> detalle_medico -> especialidades_medicos -> especialidad
$sql_base = "SELECT 
                r.Id_rol,
                p.cedula, 
                p.nombre, 
                p.apellido, 
                p.estatus,
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
        $sql = $sql_base . " WHERE p.estatus = 1 AND r.Id_rol = 4 ORDER BY p.apellido ASC";
        break;
    case 'inactivos':
        $titulo = "LISTADO DE MÉDICOS INACTIVOS";
        $sql = $sql_base . " WHERE p.estatus = 0 AND r.Id_rol = 4 ORDER BY p.apellido ASC";
        break;
    case 'especialidad':
        $titulo = "PERSONAL MÉDICO POR ESPECIALIDAD";
        $sql = $sql_base . " WHERE r.Id_rol = 4 ORDER BY e.nombre_especialidad ASC, p.apellido ASC";
        break;
    default:
        $titulo = "DIRECTORIO MÉDICO GENERAL";
        $sql = $sql_base . " WHERE r.Id_rol = 4 ORDER BY p.apellido ASC";
        break;
}

$resultado = mysqli_query($conexion, $sql);

// CONFIGURACIÓN DEL PDF
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
            <th width="30%">Cédula</th>
            <th width="40%">Nombre del Médico</th>
            <th width="30%">Especialidad</th>
        </tr>
    </thead>
    <tbody>';

if (mysqli_num_rows($resultado) > 0) {
    while ($row = mysqli_fetch_assoc($resultado)) {
        $estado = ($row['estatus'] == 1) ? 'Activo' : 'Inactivo';
        $html .= '<tr>
                    <td>'.$row['cedula'].'</td>
                    <td style="text-align:left;">'.mb_strtoupper($row['apellido'].' '.$row['nombre']).'</td>
                    <td>'.$row['nombre_especialidad'].'</td>
                    <td>'.$estado.'</td>
                  </tr>';
    }
} else {
    $html .= '<tr><td colspan="5">No se encontraron médicos registrados</td></tr>';
}

$html .= '</tbody></table>';

if (ob_get_contents()) ob_end_clean();
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Reporte_Medicos.pdf', 'I');
