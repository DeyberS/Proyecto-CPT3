<?php
// ARCHIVO: cfg/verificar_existencia_patologia.php

// 1. Incluir archivo de conexión (AJUSTA LA RUTA SEGÚN TU PROYECTO)
require '../../../cfg/conexion.php'; 
// Asegúrate de que $conexion es la variable de tu conexión mysqli

// 2. Configurar cabecera JSON
header('Content-Type: application/json');

$response = [
    'existe_nombre' => false,
    'existe_codigo' => false,
    'error' => false,
    'mensaje' => ''
];

// Validar que lleguen los datos
if (isset($_POST['nombre']) && isset($_POST['codigo'])) {
    
    $nombre = trim($_POST['nombre']);
    $codigo = trim($_POST['codigo']);
    $id_actual = isset($_POST['id_actual']) ? intval($_POST['id_actual']) : 0;

    try {
        // --- A. VERIFICAR NOMBRE ---
        $sql_nombre = "SELECT Id_patologia FROM patologias WHERE LOWER(nombre_patologia) = LOWER(?) AND Id_patologia != ? LIMIT 1";
        
        if ($stmt = $conexion->prepare($sql_nombre)) {
            $stmt->bind_param("si", $nombre, $id_actual);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) { $response['existe_nombre'] = true; }
            $stmt->close();
        } else { throw new Exception("Error al preparar consulta de nombre."); }

        // --- B. VERIFICAR CÓDIGO CIE-10 ---
        $sql_codigo = "SELECT Id_patologia FROM patologias WHERE codigo_cie = ? AND Id_patologia != ? LIMIT 1";
        
        if ($stmt2 = $conexion->prepare($sql_codigo)) {
            $stmt2->bind_param("si", $codigo, $id_actual);
            $stmt2->execute();
            $stmt2->store_result();
            if ($stmt2->num_rows > 0) { $response['existe_codigo'] = true; }
            $stmt2->close();
        } else { throw new Exception("Error al preparar consulta de código."); }

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