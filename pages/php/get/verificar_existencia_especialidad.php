<?php
// ARCHIVO: cfg/verificar_existencia_patologia.php

// 1. Incluir archivo de conexión (AJUSTA LA RUTA SEGÚN TU PROYECTO)
require '../../../cfg/conexion.php'; 
// Asegúrate de que $conexion es la variable de tu conexión mysqli

// 2. Configurar cabecera JSON
header('Content-Type: application/json');

$response = [
    'existe_nombre' => false,
    'error' => false,
    'mensaje' => ''
];

// Validar que lleguen los datos
if (isset($_POST['nombre'])) {
    
    $nombre = trim($_POST['nombre']);

    try {
        // --- A. VERIFICAR NOMBRE (Insensible a mayúsculas/minúsculas) ---
        $sql_nombre = "SELECT Id_especialidad FROM especialidad WHERE LOWER(nombre_especialidad) = LOWER(?) LIMIT 1";
        
        if ($stmt = $conexion->prepare($sql_nombre)) {
            $stmt->bind_param("s", $nombre);
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