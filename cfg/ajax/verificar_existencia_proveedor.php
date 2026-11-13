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
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0; 

    try {
        $sql_nombre = "SELECT Id_proveedor FROM proveedor WHERE LOWER(nombre_proveedor) = LOWER(?) AND Id_proveedor != ? LIMIT 1";
        
        if ($stmt = $conexion->prepare($sql_nombre)) {
            $stmt->bind_param("si", $nombre, $id);
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