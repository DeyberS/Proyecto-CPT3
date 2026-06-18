<?php
require '../../../cfg/conexion.php'; 
header('Content-Type: application/json');

$response = ['existe_nombre' => false, 'error' => false, 'mensaje' => ''];

if (isset($_POST['nombre'])) {
    $nombre = trim($_POST['nombre']);
    $id_excluir = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    try {
        $sql = "SELECT Id_permiso FROM permiso WHERE LOWER(nombre_permiso) = LOWER(?)";
        if ($id_excluir > 0) {
            $sql .= " AND Id_permiso != ?";
        }
        $sql .= " LIMIT 1";
        
        if ($stmt = $conexion->prepare($sql)) {
            if ($id_excluir > 0) {
                $stmt->bind_param("si", $nombre, $id_excluir);
            } else {
                $stmt->bind_param("s", $nombre);
            }
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $response['existe_nombre'] = true;
            }
            $stmt->close();
        } else {
            throw new Exception("Error al preparar consulta.");
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