<?php
require '../conexion.php'; 

header('Content-Type: application/json');

$response = [
    'existe_lote' => false,
    'error' => false,
    'mensaje' => ''
];

// Verificamos si nos envían el parámetro "solo_nombre"
$solo_nombre = isset($_POST['solo_nombre']) && $_POST['solo_nombre'] === 'true';

if (isset($_POST['nombre']) && ($solo_nombre || isset($_POST['id_medicamento']))) {
    $nombre = trim($_POST['nombre']);
    $id_lote_actual = isset($_POST['id_lote']) ? intval($_POST['id_lote']) : 0;

    try {
        if ($solo_nombre) {
            // 🔹 MODO 1: Solo verificar por nombre (para edición de lote con múltiples medicamentos)
            $sql = "SELECT Id FROM lotes_medicamentos WHERE LOWER(Lote) = LOWER(?) LIMIT 1";
            
            if ($id_lote_actual > 0) {
                $sql = "SELECT Id FROM lotes_medicamentos WHERE LOWER(Lote) = LOWER(?) AND Id != ? LIMIT 1";
            }

            if ($stmt = $conexion->prepare($sql)) {
                if ($id_lote_actual > 0) {
                    $stmt->bind_param("si", $nombre, $id_lote_actual);
                } else {
                    $stmt->bind_param("s", $nombre);
                }
                
                $stmt->execute();
                $stmt->store_result();
                
                if ($stmt->num_rows > 0) {
                    $response['existe_lote'] = true;
                }
                $stmt->close();
            } else {
                throw new Exception("Error al preparar la consulta de base de datos.");
            }

        } else {
            // 🔹 MODO 2: Verificar por nombre + medicamento (para el registro de nuevos lotes)
            $id_medicamento = intval($_POST['id_medicamento']);
            
            $sql = "SELECT Id FROM lotes_medicamentos WHERE LOWER(Lote) = LOWER(?) AND Id_descripcion_medicamento = ? LIMIT 1";
            
            if ($id_lote_actual > 0) {
                $sql = "SELECT Id FROM lotes_medicamentos WHERE LOWER(Lote) = LOWER(?) AND Id_descripcion_medicamento = ? AND Id != ? LIMIT 1";
            }
            
            if ($stmt = $conexion->prepare($sql)) {
                if ($id_lote_actual > 0) {
                    $stmt->bind_param("sii", $nombre, $id_medicamento, $id_lote_actual);
                } else {
                    $stmt->bind_param("si", $nombre, $id_medicamento);
                }
                
                $stmt->execute();
                $stmt->store_result();
                
                if ($stmt->num_rows > 0) {
                    $response['existe_lote'] = true;
                }
                $stmt->close();
            } else {
                throw new Exception("Error al preparar la consulta de base de datos.");
            }
        }

    } catch (Exception $e) {
        $response['error'] = true;
        $response['mensaje'] = $e->getMessage();
    }
} else {
    $response['error'] = true;
    $response['mensaje'] = "Datos incompletos para la verificación.";
}

echo json_encode($response);
?>