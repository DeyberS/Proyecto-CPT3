<?php
require '../../../cfg/conexion.php'; 
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
        // Si hay ID (Editar), ignorar el registro actual. Si no hay ID (Agregar), buscar normal.
        if ($id > 0) {
            $sql_nombre = "SELECT Id_departamento FROM departamento WHERE LOWER(nombre_departamento) = LOWER(?) AND Id_departamento != ? LIMIT 1";
            $stmt = $conexion->prepare($sql_nombre);
            $stmt->bind_param("si", $nombre, $id);
        } else {
            $sql_nombre = "SELECT Id_departamento FROM departamento WHERE LOWER(nombre_departamento) = LOWER(?) LIMIT 1";
            $stmt = $conexion->prepare($sql_nombre);
            $stmt->bind_param("s", $nombre);
        }
        
        if ($stmt) {
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