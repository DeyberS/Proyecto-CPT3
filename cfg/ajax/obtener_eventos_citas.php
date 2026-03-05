<?php
// Incluimos la conexión a la base de datos
include('../conexion.php');

// Establecemos la zona horaria para que coincida con tu listado
date_default_timezone_set('America/Caracas');

// Consulta para obtener las citas con los nombres de pacientes y médicos
// Usamos los mismos JOINs que tienes en tu archivo de listado
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
    // Definimos el color según el estado (siguiendo tu lógica de badges)
    $color = '#95a5a6'; // Gris por defecto (Vencida/Cancelada)
    if ($row['estado'] == 'Pendiente') $color = '#f39c12'; // Amarillo/Naranja
    if ($row['estado'] == 'Confirmada') $color = '#00c0ef'; // Azul cielo
    if ($row['estado'] == 'Finalizada') $color = '#00a65a'; // Verde

    // FullCalendar requiere un formato específico de objeto
    $eventos[] = array(
        'id'    => $row['Id_cita'],
        'title' => $row['nombre_paciente'] . " - " . 'Dr(A)' . " " . $row['nombre_medico'] . " " . "(" . $row['estado'] . ")",
        'start' => $row['fecha_cita'] . 'T' . $row['hora_cita'], // Formato ISO8601
        'description' => $row['motivo'],
        'backgroundColor' => $color,
        'borderColor'     => $color,
        'allDay' => false
    );
}

// Devolvemos el array codificado en JSON para que JS lo procese
header('Content-Type: application/json');
echo json_encode($eventos);
?>


