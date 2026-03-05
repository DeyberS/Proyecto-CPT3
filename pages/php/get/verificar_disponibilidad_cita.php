<?php
// Incluir tu conexión a la base de datos cpt3db
require_once('../../../cfg/conexion.php');

$response = ['ocupado' => false];

if (isset($_POST['id_medico']) && isset($_POST['fecha']) && isset($_POST['hora'])) {
    $id_medico = $_POST['id_medico'];
    $fecha = $_POST['fecha'];
    $hora_seleccionada = $_POST['hora'];

    // Convertimos la hora seleccionada a un objeto de tiempo para calcular el rango
    // Rango de 20 minutos (10 min antes y 10 min después)
    $inicio_rango = date('H:i:s', strtotime($hora_seleccionada . ' -10 minutes'));
    $fin_rango = date('H:i:s', strtotime($hora_seleccionada . ' +10 minutes'));

    // Buscamos si existe una cita para ese médico y ese día
    // que esté dentro del rango de 20 minutos
    // y que el estatus sea activo (que NO sea Cancelada, Vencida o Inasistente)
    $query = "SELECT id_cita FROM citas 
              WHERE Id_medico = ? 
              AND fecha_cita = ? 
              AND hora_cita BETWEEN ? AND ?
              AND estado NOT IN ('Cancelada', 'Vencida', 'Inasistente')
              AND estatus = 1";

    $stmt = $conexion->prepare($query);
    $stmt->bind_param("isss", $id_medico, $fecha, $inicio_rango, $fin_rango);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $response['ocupado'] = true;
    }

    $stmt->close();
}

echo json_encode($response);
?>