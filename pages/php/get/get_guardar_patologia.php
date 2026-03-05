<?php
// Asegura que la respuesta sea JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

// 1. Recoger datos
$nombre = isset($_POST['nombre_patologia']) ? trim($_POST['nombre_patologia']) : '';
$codigo_cie = isset($_POST['codigo_cie']) ? trim($_POST['codigo_cie']) : '';
$contagiosa = isset($_POST['enfermedad_contagiosa']) ? trim($_POST['enfermedad_contagiosa']) : 'No';

if (empty($nombre) || empty($codigo_cie)) {
    echo json_encode(['success' => false, 'message' => 'Faltan campos requeridos (Nombre o Código CIE).']);
    exit;
}

// 2. Conexión a la BD (Asegúrate de usar tus credenciales)
$conexion_add = new mysqli("localhost", "root", "", "cpt3db");

if ($conexion_add->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la BD: ' . $conexion_add->connect_error]);
    exit;
}

try {
    // 3. Preparar e insertar la consulta SQL
    $stmt = $conexion_add->prepare("INSERT INTO patologias (nombre_patologia, codigo_cie, contagioso) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nombre, $codigo_cie, $contagiosa);

    if ($stmt->execute()) {
        $new_id = $stmt->insert_id;
        
        // Éxito: Retornar los datos de la nueva patología
        echo json_encode([
            'success' => true, 
            'message' => "¡Patología '{$nombre}' añadida con éxito!",
            'data' => [
                'Id_patologia' => $new_id,
                'nombre_patologia' => $nombre,
                'codigo_cie' => $codigo_cie
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar patología: ' . $stmt->error]);
    }
    
    $stmt->close();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error inesperado: ' . $e->getMessage()]);
}

$conexion_add->close();
?>


