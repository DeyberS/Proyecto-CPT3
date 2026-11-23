<?php
session_start();
include('../conexion.php');

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION["id"]) || !$conexion) {
    exit(json_encode([
        'ok' => false,
        'data' => [],
        'changed' => false,
        'last_id' => 0,
        'total_pendientes' => 0,
        'iluminar_recetas' => false,
        'ids_recetas' => [],
        'server_time' => date('c')
    ]));
}

$id_usuario_actual = (int)$_SESSION["id"];
$since_id = isset($_GET['since_id']) ? (int)$_GET['since_id'] : 0;

$alertas = [];

$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
$es_pagina_interna = strpos($referer, 'pages/php') !== false;

/* ---------------------------------------------------------------
   1) Notificaciones NUEVAS desde since_id (o todas si since_id=0)
   --------------------------------------------------------------- */
if ($since_id > 0) {
    $stmt = $conexion->prepare("
        SELECT id, tipo, titulo, mensaje, ruta, fecha_creacion
        FROM notificaciones_usuarios
        WHERE id_usuario = ? AND leida = 0 AND id > ?
        ORDER BY id ASC
        LIMIT 100
    ");
    $stmt->bind_param("ii", $id_usuario_actual, $since_id);
} else {
    $stmt = $conexion->prepare("
        SELECT id, tipo, titulo, mensaje, ruta, fecha_creacion
        FROM notificaciones_usuarios
        WHERE id_usuario = ? AND leida = 0
        ORDER BY id ASC
        LIMIT 100
    ");
    $stmt->bind_param("i", $id_usuario_actual);
}

$stmt->execute();
$resultado = $stmt->get_result();

while ($fila = $resultado->fetch_assoc()) {
    $ruta_dinamica = $fila['ruta'];
    if ($es_pagina_interna && !empty($ruta_dinamica)) {
        $ruta_dinamica = '../../' . $ruta_dinamica;
    }

    $alertas[] = [
        'id_notificacion' => (int)$fila['id'],
        'id'              => (int)$fila['id'],
        'categoria'       => $fila['tipo'],
        'tipo'            => $fila['tipo'],
        'titulo'          => $fila['titulo'],
        'mensaje'         => $fila['mensaje'],
        'ruta'            => $ruta_dinamica,
        'fecha_creacion'  => $fila['fecha_creacion']
    ];
}
$stmt->close();

/* ---------------------------------------------------------------
   2) Último id no leído
   --------------------------------------------------------------- */
$last_id = $since_id;
$stmtMax = $conexion->prepare("
    SELECT COALESCE(MAX(id), 0) AS max_id
    FROM notificaciones_usuarios
    WHERE id_usuario = ? AND leida = 0
");
$stmtMax->bind_param("i", $id_usuario_actual);
$stmtMax->execute();
$rowMax = $stmtMax->get_result()->fetch_assoc();
if ($rowMax && (int)$rowMax['max_id'] > $last_id) {
    $last_id = (int)$rowMax['max_id'];
}
$stmtMax->close();

/* ---------------------------------------------------------------
   3) Total pendientes (para el badge)
   --------------------------------------------------------------- */
$total_pendientes = 0;
$stmtCount = $conexion->prepare("
    SELECT COUNT(*) AS total
    FROM notificaciones_usuarios
    WHERE id_usuario = ? AND leida = 0
");
$stmtCount->bind_param("i", $id_usuario_actual);
$stmtCount->execute();
$rowCount = $stmtCount->get_result()->fetch_assoc();
if ($rowCount) $total_pendientes = (int)$rowCount['total'];
$stmtCount->close();

/* ---------------------------------------------------------------
   4) ⚡ ILUMINACIÓN: TODAS las recetas sin leer, independiente de since_id
      Esta es la fuente de verdad para el glow del menú.
   --------------------------------------------------------------- */
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
    'ok'                => true,
    'data'              => $alertas,
    'changed'           => count($alertas) > 0,
    'last_id'           => $last_id,
    'total_pendientes'  => $total_pendientes,
    'iluminar_recetas'  => count($ids_recetas) > 0,
    'ids_recetas'       => $ids_recetas,
    'server_time'       => date('c')
]);