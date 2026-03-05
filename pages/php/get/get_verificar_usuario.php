<?php
include('../../../cfg/conexion.php'); 
header('Content-Type: application/json');

$response = ['existe' => false];

if (isset($_POST['nombre'])) {
    $nombre_usuario = trim($_POST['nombre']);
    // Capturamos el ID si viene (para el caso de edición)
    $id_excluir = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    if (empty($nombre_usuario)) {
        echo json_encode($response);
        exit();
    }
    
    $sql = "SELECT COUNT(*) FROM persona WHERE nombre = ?";
    if ($id_excluir > 0) {
        $sql .= " AND id != ?";
    }

    $stmt = $conexion->prepare($sql);
    
    if ($id_excluir > 0) {
        $stmt->bind_param("si", $nombre_usuario, $id_excluir);
    } else {
        $stmt->bind_param("s", $nombre_usuario);
    }

    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    
    if ($count > 0) {
        $response['existe'] = true;
    }
    
    echo json_encode($response);
}