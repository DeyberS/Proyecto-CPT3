<?php
session_start();
include('../conexion.php');

if (!isset($_SESSION["id"]) || !$conexion) {
    exit(json_encode(['data' => [], 'iluminar_recetas' => false]));
}

$id_usuario_actual = $_SESSION["id"];
$alertas = [];
$iluminar_menu = false;

// Determinar de dónde viene la petición para ajustar las rutas
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
$es_pagina_interna = strpos($referer, 'pages/php') !== false;

// Obtener notificaciones no leídas
$stmt = $conexion->prepare("SELECT id, tipo, titulo, mensaje, ruta, fecha_creacion FROM notificaciones_usuarios WHERE id_usuario = ? AND leida = 0 ORDER BY fecha_creacion DESC");
$stmt->bind_param("i", $id_usuario_actual);
$stmt->execute();
$resultado = $stmt->get_result();

while ($fila = $resultado->fetch_assoc()) {
    $ruta_dinamica = $fila['ruta'];
    
    // Ajuste dinámico de ruta si estamos dentro de pages/php
    if ($es_pagina_interna && !empty($ruta_dinamica)) {
        $ruta_dinamica = '../../' . $ruta_dinamica;
    }

    if ($fila['tipo'] === 'receta_disponible') {
        $iluminar_menu = true;
    }

    $alertas[] = [
        'id_notificacion' => $fila['id'],
        'categoria' => $fila['tipo'],
        'titulo' => $fila['titulo'],
        'mensaje' => $fila['mensaje'],
        'ruta' => $ruta_dinamica
    ];
}

$stmt->close();

header('Content-Type: application/json');
echo json_encode([
    'data' => $alertas, 
    'iluminar_recetas' => $iluminar_menu
]);
?>