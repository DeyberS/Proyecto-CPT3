<?php
require_once('../../plugins/vendor/tcpdf/tcpdf.php'); 
include('../conexion.php');

$tipo = $_GET['tipo'];
$fecha_hoy = date('Y-m-d');

// 1. LÓGICA DE CONSULTA SQL PARA CITAS
switch ($tipo) {
    case 'hoy':
        $titulo = "CITAS PROGRAMADAS PARA HOY ($fecha_hoy)";
        $sql = "SELECT c.*, p.nombre as p_nom, p.apellido as p_ape, p.cedula, m.nombre as m_nom, m.apellido as m_ape 
                FROM citas c 
                JOIN persona p ON c.Id_paciente = p.id 
                JOIN persona m ON c.Id_medico = m.id 
                WHERE c.fecha_cita = '$fecha_hoy' AND c.estatus = 1";
        break;

    case 'proximas':
        $titulo = "PRÓXIMAS CITAS (FUTURAS)";
        $sql = "SELECT c.*, p.nombre as p_nom, p.apellido as p_ape, p.cedula, m.nombre as m_nom, m.apellido as m_ape 
                FROM citas c 
                JOIN persona p ON c.Id_paciente = p.id 
                JOIN persona m ON c.Id_medico = m.id 
                WHERE c.fecha_cita > '$fecha_hoy' AND c.estatus = 1 
                ORDER BY c.fecha_cita ASC";
        break;

    default:
        $titulo = "HISTORIAL TOTAL DE CITAS";
        $sql = "SELECT c.*, p.nombre as p_nom, p.apellido as p_ape, p.cedula, m.nombre as m_nom, m.apellido as m_ape 
                FROM citas c 
                JOIN persona p ON c.Id_paciente = p.id 
                JOIN persona m ON c.Id_medico = m.id 
                WHERE c.estatus = 1 
                ORDER BY c.fecha_cita DESC";
        break;
}

$resultado = mysqli_query($conexion, $sql);

// 2. CONFIGURACIÓN DEL PDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetTitle($titulo);
$pdf->AddPage();

$html = '
<style>
    h1 { text-align: center; font-size: 14pt; }
    table { border-collapse: collapse; width: 100%; }
    th { background-color: #d9534f; color: white; font-weight: bold; border: 1px solid #000; text-align: center; }
    td { border: 1px solid #ccc; text-align: center; font-size: 9pt; }
</style>
<h1>' . $titulo . '</h1>
<table cellpadding="5">
    <thead>
        <tr>
            <th>Fecha/Hora</th>
            <th>Paciente</th>
            <th>Médico</th>
            <th>Motivo</th>
            <th>Estado</th>
        </tr>
    </thead>
    <tbody>';

while ($row = mysqli_fetch_assoc($resultado)) {
    $html .= '<tr>
                <td>'.$row['fecha_cita'].'<br>'.$row['hora_cita'].'</td>
                <td>'.$row['p_nom'].' '.$row['p_ape'].'<br>C.I: '.$row['cedula'].'</td>
                <td>Dr. '.$row['m_nom'].' '.$row['m_ape'].'</td>
                <td>'.$row['motivo'].'</td>
                <td>'.$row['estado'].'</td>
              </tr>';
}

$html .= '</tbody></table>';

$pdf->writeHTML($html, true, false, true, false, '');
if (ob_get_contents()) ob_end_clean();
$pdf->Output('Reporte_Citas.pdf', 'I');


