<?php
// get/get_medicamentos_base.php
include("../../../cfg/conexion.php"); // Reutiliza tu conexión

header('Content-Type: application/json');
$response = ['success' => false, 'data' => [], 'error' => ''];

if (isset($conexion)) {
    try {
        // Adaptar la consulta para obtener ID y Nombre del medicamento base
        $sql = "SELECT dm.Id_medicamento, m.nombre_medicamento, p.tipo_presentacion
        FROM descripcion_medicamento dm
        INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
        INNER JOIN presentacion p ON dm.Id_presentacion  = p.Id_presentacion
        ORDER BY m.nombre_medicamento ASC";
        $result = $conexion->query($sql);

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $response['data'][] = $row;
            }
            $response['success'] = true;
            $result->free();
        } else {
            $response['error'] = "Error al ejecutar la consulta: " . $conexion->error;
        }

    } catch (\mysqli_sql_exception $e) {
        $response['error'] = "Error de conexión o consulta: " . $e->getMessage();
    }
} else {
    $response['error'] = "No se estableció la conexión a la base de datos.";
}

echo json_encode($response);
// No cierres la conexión aquí si está en un entorno donde se maneja globalmente. 
// Si la abres aquí, ciérrala.
// if (isset($conexion)) $conexion->close();
?>