<?php
session_start();
include('../conexion.php');

header('Content-Type: application/json');

if (!isset($_SESSION["id"]) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['status' => 'error']));
}

$id_usuario_actual = $_SESSION["id"];

if (isset($_POST['ids']) && is_array($_POST['ids'])) {
    $ids = implode(',', array_map('intval', $_POST['ids']));
    $conexion->query("UPDATE notificaciones_usuarios SET leida = 1 WHERE id_usuario = $id_usuario_actual AND id IN ($ids)");
} else {
    $conexion->query("UPDATE notificaciones_usuarios SET leida = 1 WHERE id_usuario = $id_usuario_actual AND leida = 0");
}

echo json_encode(['status' => 'success']);
?>