<?php
require_once "../conexion.php"; 
header('Content-Type: application/json');

$nombre_sintoma = isset($_POST['nombre_sintoma']) ? trim($_POST['nombre_sintoma']) : '';

if(empty($nombre_sintoma)) {
    echo json_encode(['success' => false, 'error' => 'El nombre está vacío']);
    exit;
}

$stmtCheck = $conexion->prepare("SELECT Id_sintomas FROM sintomas WHERE nombre_sintoma = ?");
$stmtCheck->bind_param("s", $nombre_sintoma);
$stmtCheck->execute();
$stmtCheck->store_result();

if ($stmtCheck->num_rows > 0) {
    echo json_encode([
        'success' => false, 
        'error' => 'El síntoma "<b>' . htmlspecialchars($nombre_sintoma) . '</b>" ya está registrado.'
    ]);
    $stmtCheck->close();
    exit;
}
$stmtCheck->close();

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