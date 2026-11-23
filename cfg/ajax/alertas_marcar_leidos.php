<?php
session_start();
include('../conexion.php');

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION["id"]) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['status' => 'error', 'message' => 'no_session_or_method']));
}

$id_usuario_actual = (int)$_SESSION["id"];
$ids_marcados = [];

/* Modo 1: IDs específicos */
if (isset($_POST['ids']) && is_array($_POST['ids']) && count($_POST['ids']) > 0) {
    $ids_limpios = array_values(array_filter(array_map('intval', $_POST['ids']), function($v) {
        return $v > 0;
    }));

    if (count($ids_limpios) > 0) {
        $placeholders = implode(',', array_fill(0, count($ids_limpios), '?'));
        $types = str_repeat('i', count($ids_limpios) + 1);
        $params = array_merge([$id_usuario_actual], $ids_limpios);

        $stmt = $conexion->prepare("
            UPDATE notificaciones_usuarios
            SET leida = 1
            WHERE id_usuario = ? AND id IN ($placeholders)
        ");
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $ids_marcados = $ids_limpios;
        $stmt->close();
    }
}
/* Modo 2: hasta un id */
elseif (isset($_POST['ultimo_id']) && (int)$_POST['ultimo_id'] > 0) {
    $ultimo_id = (int)$_POST['ultimo_id'];

    $stmt = $conexion->prepare("
        UPDATE notificaciones_usuarios
        SET leida = 1
        WHERE id_usuario = ? AND leida = 0 AND id <= ?
    ");
    $stmt->bind_param("ii", $id_usuario_actual, $ultimo_id);
    $stmt->execute();
    $stmt->close();
}
/* Modo 3: todas */
else {
    $stmt = $conexion->prepare("
        UPDATE notificaciones_usuarios
        SET leida = 1
        WHERE id_usuario = ? AND leida = 0
    ");
    $stmt->bind_param("i", $id_usuario_actual);
    $stmt->execute();
    $stmt->close();
}

/* Estado actualizado para el cliente */
$total_pendientes = 0;
$stmtCount = $conexion->prepare("
    SELECT COUNT(*) AS total
    FROM notificaciones_usuarios
    WHERE id_usuario = ? AND leida = 0
");
$stmtCount->bind_param("i", $id_usuario_actual);
$stmtCount->execute();
$row = $stmtCount->get_result()->fetch_assoc();
if ($row) $total_pendientes = (int)$row['total'];
$stmtCount->close();

$ids_recetas = [];
$stmtRecIds = $conexion->prepare("
    SELECT id
    FROM notificaciones_usuarios
    WHERE id_usuario = ? AND leida = 0 AND tipo = 'receta_disponible'
    ORDER BY id ASC
");
$stmtRecIds->bind_param("i", $id_usuario_actual);
$stmtRecIds->execute();
$resRecIds = $stmtRecIds->get_result();
while ($row = $resRecIds->fetch_assoc()) {
    $ids_recetas[] = (int)$row['id'];
}
$stmtRecIds->close();

echo json_encode([
    'status'           => 'success',
    'ids_marcados'     => $ids_marcados,
    'total_pendientes' => $total_pendientes,
    'iluminar_recetas' => count($ids_recetas) > 0,
    'ids_recetas'      => $ids_recetas
]);