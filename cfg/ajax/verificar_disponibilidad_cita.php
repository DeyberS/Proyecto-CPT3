<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');
require_once('../conexion.php');

$response = ['ocupado' => false];

if (isset($_POST['id_medico']) && isset($_POST['fecha']) && isset($_POST['hora'])) {
    $id_medico = $_POST['id_medico'];
    $fecha = $_POST['fecha'];
    $hora_seleccionada = $_POST['hora'];
    
    $id_cita_actual = isset($_POST['id_cita']) && !empty($_POST['id_cita']) ? $_POST['id_cita'] : null;

    $timestamp = strtotime($hora_seleccionada);
    $inicio_rango = date('H:i:s', $timestamp - 3600);
    $fin_rango = date('H:i:s', $timestamp + 3600);

    if ($id_cita_actual) {
        $query = "SELECT Id_cita FROM citas 
                  WHERE Id_medico = ? 
                  AND fecha_cita = ? 
                  AND TIME(hora_cita) BETWEEN ? AND ?
                  AND estado IN ('Pendiente', 'Confirmada', 'Reprogramada')
                  AND estatus = 1
                  AND Id_cita != ?";

        if ($stmt = $conexion->prepare($query)) {
            $stmt->bind_param("isssi", $id_medico, $fecha, $inicio_rango, $fin_rango, $id_cita_actual);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $response['ocupado'] = true;
            }
            $stmt->close();
        }
    } else {
        $query = "SELECT Id_cita FROM citas 
                  WHERE Id_medico = ? 
                  AND fecha_cita = ? 
                  AND TIME(hora_cita) BETWEEN ? AND ?
                  AND estado IN ('Pendiente', 'Confirmada', 'Reprogramada')
                  AND estatus = 1";

        if ($stmt = $conexion->prepare($query)) {
            $stmt->bind_param("isss", $id_medico, $fecha, $inicio_rango, $fin_rango);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $response['ocupado'] = true;
            }
            $stmt->close();
        }
    }
}

ob_end_clean();
echo json_encode($response);
exit;
?>