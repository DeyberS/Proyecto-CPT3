<?php
// ARCHIVO: verificar_existencia_lote.php

require '../../../cfg/conexion.php'; 

header('Content-Type: application/json');

$response = [
    'existe_lote' => false,
    'error' => false,
    'mensaje' => ''
];

// Validamos que lleguen el nombre y el id_medicamento
if (isset($_POST['nombre']) && isset($_POST['id_medicamento'])) {
    
    $nombre = trim($_POST['nombre']);
    $id_medicamento = intval($_POST['id_medicamento']);
    // Recibimos el ID del lote si viene (útil para la edición)
    $id_lote_actual = isset($_POST['id_lote']) ? intval($_POST['id_lote']) : 0;

    try {
        // Consulta base: Verifica nombre de lote + el mismo medicamento
        $sql = "SELECT Id FROM lotes_medicamentos WHERE LOWER(Lote) = LOWER(?) AND Id_descripcion_medicamento = ? LIMIT 1";
        
        // Si estamos editando, excluimos el lote actual de la búsqueda
        if ($id_lote_actual > 0) {
            $sql = "SELECT Id FROM lotes_medicamentos WHERE LOWER(Lote) = LOWER(?) AND Id_descripcion_medicamento = ? AND Id != ? LIMIT 1";
        }
        
        if ($stmt = $conexion->prepare($sql)) {
            // Asignamos los parámetros de acuerdo a si es agregar o editar
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