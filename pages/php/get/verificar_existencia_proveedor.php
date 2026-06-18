<?php
require '../../../cfg/conexion.php'; 
header('Content-Type: application/json');

$response = [
    'existe_nombre' => false,
    'error' => false,
    'mensaje' => ''
];

// Validar que lleguen los datos
if (isset($_POST['nombre'])) {
    
    $nombre = trim($_POST['nombre']);
    // Recibir ID opcional para el caso de edición. Si no viene, será 0.
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0; 

    try {
        // --- A. VERIFICAR NOMBRE (Insensible a mayúsculas/minúsculas y excluyendo el ID actual) ---
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