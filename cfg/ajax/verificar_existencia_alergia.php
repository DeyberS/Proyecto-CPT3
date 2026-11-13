<?php
require '../conexion.php'; 
header('Content-Type: application/json');

$response = [
    'existe_nombre' => false,
    'error' => false,
    'mensaje' => ''
];

if (isset($_POST['nombre'])) {
    
    $nombre = trim($_POST['nombre']);
    $id_actual = isset($_POST['id_actual']) ? intval($_POST['id_actual']) : 0;

    try {
        $sql_nombre = "SELECT Id_alergias_conocidas FROM alergias_conocidas WHERE LOWER(nombre_alergia) = LOWER(?) AND Id_alergias_conocidas != ? LIMIT 1";
        
        if ($stmt = $conexion->prepare($sql_nombre)) {
            $stmt->bind_param("si", $nombre, $id_actual);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $response['existe_nombre'] = true;
            }
            $stmt->close();
        } else {
            throw new Exception("Error al preparar consulta de nombre.");
        }

    } catch (Exception $e) {
        $response['error'] = true;
        $response['mensaje'] = $e->getMessage();
    }
} else {
    $response['error'] = true;
    $response['mensaje'] = "Datos incompletos.";
}

echo json_encode($response);
?>