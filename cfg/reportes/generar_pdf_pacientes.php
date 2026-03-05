<?php
ob_start();
require_once('../../plugins/vendor/tcpdf/tcpdf.php'); 
include('../conexion.php');

$tipo = $_GET['tipo'];

// CONSULTA BASE: persona + detalle_paciente
$sql_base = "SELECT 
                r.Id_rol,
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
             INNER JOIN historial_medico hm ON hm.Id_persona = p.id";

switch ($tipo) {
    case 'activos':
        $titulo = "LISTADO DE PACIENTES ACTIVOS";
        $sql = $sql_base . " WHERE p.estatus = 1 AND r.Id_rol = 3 AND TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) >= 18 ORDER BY p.apellido ASC";
        break;
    case 'sangre':
        $titulo = "PACIENTES POR GRUPO SANGUÍNEO";
        $sql = $sql_base . " WHERE r.Id_rol = 3 AND TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) >= 18 ORDER BY hm.grupo_sanguineo ASC, p.apellido ASC";
        break;
    case 'femenino':
        $titulo = "LISTADO DE PACIENTES (FEMENINO)";
        $sql = $sql_base . " WHERE p.genero = 'Femenino' AND r.Id_rol = 3 AND TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) >= 18 ORDER BY p.apellido ASC";
        break;
    case 'masculino':
        $titulo = "LISTADO DE PACIENTES (MASCULINO)";
        $sql = $sql_base . " WHERE p.genero = 'Masculino' AND r.Id_rol = 3 AND TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) >= 18 ORDER BY p.apellido ASC";
        break;
    default:
        $titulo = "CENSO GENERAL DE PACIENTES (MAYORES)";
        $sql = $sql_base . " WHERE r.Id_rol = 3 AND TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) >= 18 ORDER BY p.apellido ASC";
        break;
}

$resultado = mysqli_query($conexion, $sql);

// CONFIGURACIÓN DEL PDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetTitle($titulo);
$pdf->setPrintHeader(false);
$pdf->AddPage();

$html = '
<style>
    h1 { text-align: center; color: #333; font-size: 14pt; }
    table { border-collapse: collapse; width: 100%; }
    th { background-color: #337ab7; color: white; font-weight: bold; border: 1px solid #000; text-align: center; }
    td { border: 1px solid #ccc; text-align: center; font-size: 9pt; }
</style>
<h1>' . $titulo . '</h1>
<table cellpadding="5">
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

while ($row = mysqli_fetch_assoc($resultado)) {
    // Cálculo de edad
    $cumpleanos = new DateTime($row['fecha_nacimiento']);
    $hoy = new DateTime();
    $edad = $hoy->diff($cumpleanos)->y;
    
    $estado = ($row['estatus'] == 1) ? 'Activo' : 'Inactivo';
    $genero = ($row['genero'] == 'M') ? 'Masc.' : 'Fem.';

    $html .= '<tr>
                <td>'.$row['cedula'].'</td>
                <td style="text-align:left;">'.mb_strtoupper($row['apellido'].' '.$row['nombre']).'</td>
                <td>'.$edad.'</td>
                <td>'.$genero.'</td>
                <td>'.$row['tipo_sangre'].'</td>
              </tr>';
}

$html .= '</tbody></table>';

if (ob_get_contents()) ob_end_clean();
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Reporte_Pacientes_Mayores.pdf', 'I');


