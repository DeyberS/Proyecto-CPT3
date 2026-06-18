<?php
// ajax_guardar_sintoma.php
require_once "../../cfg/conexion.php"; 
header('Content-Type: application/json');

// Usamos trim para limpiar espacios accidentales
$nombre_sintoma = isset($_POST['nombre_sintoma']) ? trim($_POST['nombre_sintoma']) : '';

if(empty($nombre_sintoma)) {
    echo json_encode(['success' => false, 'error' => 'El nombre está vacío']);
    exit;
}

// --- 1. VERIFICAR SI EL SÍNTOMA YA EXISTE ---
$stmtCheck = $conexion->prepare("SELECT Id_sintomas FROM sintomas WHERE nombre_sintoma = ?");
$stmtCheck->bind_param("s", $nombre_sintoma);
$stmtCheck->execute();
$stmtCheck->store_result();

if ($stmtCheck->num_rows > 0) {
    // Si ya existe, enviamos error y detenemos el script
    echo json_encode([
        'success' => false, 
        'error' => 'El síntoma "<b>' . htmlspecialchars($nombre_sintoma) . '</b>" ya está registrado.'
    ]);
    $stmtCheck->close();
    exit;
}
$stmtCheck->close();

// --- 2. SI NO EXISTE, PROCEDEMOS A INSERTAR ---
$stmt = $conexion->prepare("INSERT INTO sintomas (nombre_sintoma, estatus) VALUES (?, 1)");
$stmt->bind_param("s", $nombre_sintoma);

if($stmt->execute()) {
    echo json_encode([
        'success' => true, 
        'id' => $conexion->insert_id, 
        'nombre' => htmlspecialchars($nombre_sintoma)
    ]);
} else {
    echo json_encode(['success' => false, 'error' => $conexion->error]);
}

$stmt->close();
$conexion->close();
?>