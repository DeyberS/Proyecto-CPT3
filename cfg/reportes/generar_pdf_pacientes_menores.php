<?php
ob_start();
require_once('../../plugins/vendor/tcpdf/tcpdf.php'); 
include('../conexion.php');

$tipo = $_GET['tipo'];

// CONSULTA BASE: 
// Solo personas cuya edad calculada sea < 18
// Estatus se toma de la tabla persona del menor (PM)
$sql_base = "SELECT 
                r.Id_rol,
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
             INNER JOIN persona PM ON DPM.Id_detalle_paciente_menor = PM.id
             INNER JOIN persona PR ON DPM.Id_representante = PR.id
             INNER JOIN detalle_persona_rol dpr ON PM.id = dpr.Id_persona 
             INNER JOIN rol r ON dpr.Id_rol = r.Id_rol
             WHERE TIMESTAMPDIFF(YEAR, PM.fecha_nacimiento, CURDATE()) < 18";

switch ($tipo) {
    case 'activos':
        $titulo = "PACIENTES MENORES DE EDAD ACTIVOS";
        $sql = $sql_base . " AND r.Id_rol = 3 AND PM.estatus = 1 ORDER BY PM.apellido ASC";
        break;
    case 'representante':
        $titulo = "PACIENTES MENORES POR REPRESENTANTE";
        $sql = $sql_base . " AND r.Id_rol = 3 ORDER BY PR.apellido ASC, PM.apellido ASC";
        break;
    case 'parentesco':
        $titulo = "PACIENTES MENORES POR PARENTESCO";
        $sql = $sql_base . " AND r.Id_rol = 3 ORDER BY DPM.parentesco ASC, PM.apellido ASC";
        break;
    default:
        $titulo = "CENSO GENERAL DE PACIENTES MENORES";
        $sql = $sql_base . " AND r.Id_rol = 3 ORDER BY PM.apellido ASC";
        break;
}

$resultado = mysqli_query($conexion, $sql);

// CONFIGURACIÓN DEL PDF (Horizontal para mostrar nombres de menor y representante)
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
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
            <th width="25%">Nombre del Menor</th>
            <th width="15%">Edad</th>
            <th width="20%">Parentesco</th>
            <th width="25%">Representante</th>
        </tr>
    </thead>
    <tbody>';

if(mysqli_num_rows($resultado) > 0){
    while ($row = mysqli_fetch_assoc($resultado)) {
        // Cálculo de edad preciso
        $fecha_nac = new DateTime($row['fecha_nacimiento']);
        $hoy = new DateTime();
        $edad = $hoy->diff($fecha_nac)->y;
        
        $estado = ($row['estatus'] == 1) ? 'Activo' : 'Inactivo';

        $html .= '<tr>
                    <td>'.($row['cedula_menor'] ? $row['cedula_menor'] : 'N/A').'</td>
                    <td style="text-align:left;">'.mb_strtoupper($row['apellido_menor'].' '.$row['nombre_menor']).'</td>
                    <td>'.$edad.' años</td>
                    <td>'.ucfirst($row['parentesco']).'</td>
                    <td style="text-align:left;">'.mb_strtoupper($row['apellido_rep'].' '.$row['nombre_rep']).'</td>
                  </tr>';
    }
} else {
    $html .= '<tr><td colspan="6">No se encontraron pacientes menores de 18 años</td></tr>';
}

$html .= '</tbody></table>';

if (ob_get_contents()) ob_end_clean();
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Reporte_Pacientes_Menores.pdf', 'I');


