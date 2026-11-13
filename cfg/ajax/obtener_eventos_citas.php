<?php
include('../conexion.php');
date_default_timezone_set('America/Caracas');

$sql = "SELECT c.Id_cita, c.fecha_cita, c.hora_cita, c.motivo, c.estado,
               p.nombre AS nombre_paciente, p.apellido AS apellido_paciente,
               m.nombre AS nombre_medico, m.apellido AS apellido_medico
        FROM citas c
        INNER JOIN persona p ON c.Id_paciente = p.id
        INNER JOIN detalle_medico dm ON c.Id_medico = dm.Id_detalle_medico
        INNER JOIN persona m ON dm.Id_persona = m.id WHERE c.estatus = 1";

$resultado = $conexion->query($sql);
$eventos = array();

while ($row = $resultado->fetch_assoc()) {
    if ($row['estado'] == 'Pendiente') $color = '#f39c12';
    if ($row['estado'] == 'Confirmada') $color = '#00c0ef';
    if ($row['estado'] == 'Finalizada') $color = '#00a65a';

    $eventos[] = array(
        'id'    => $row['Id_cita'],
        'title' => $row['nombre_paciente'] . " - " . 'Dr(A)' . " " . $row['nombre_medico'] . " " . "(" . $row['estado'] . ")",
        'start' => $row['fecha_cita'] . 'T' . $row['hora_cita'],
        'description' => $row['motivo'],
        'backgroundColor' => $color,
        'borderColor'     => $color,
        'allDay' => false
    );
}

header('Content-Type: application/json');
echo json_encode($eventos);
?>


