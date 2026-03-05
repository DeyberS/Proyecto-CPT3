<?php
// get/get_unidades_por_medicamento.php
include("../../../cfg/conexion.php"); // Reutiliza tu conexión

header('Content-Type: application/json');
$response = ['success' => false, 'data' => [], 'error' => ''];
$id_medicamento = $_GET['id'] ?? null;

if (!$id_medicamento || !is_numeric($id_medicamento)) {
    $response['error'] = "ID de medicamento inválido.";
} elseif (isset($conexion)) {
    try {
        $safe_id = $conexion->real_escape_string($id_medicamento);

        // La consulta debe obtener las descripciones/unidades asociadas a ese medicamento.
        $sql = "
            SELECT 
                dm.Id as Id_descripcion_medicamento, 
                um.unidad 
            FROM descripcion_medicamento dm
            JOIN unidad_medida um ON dm.Id_unidad = um.Id_unidad_medida
            WHERE dm.Id_medicamento = '$safe_id'
        ";
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
?>


