<?php
include('../../../cfg/conexion.php'); 
header('Content-Type: application/json');

$response = ['existe_nombre' => false, 'existe_email' => false];

$nombre_usuario = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$email_usuario = isset($_POST['email']) ? trim($_POST['email']) : '';
$id_excluir = isset($_POST['id']) ? (int)$_POST['id'] : 0;

// 1. Verificar Nombre de Usuario
if (!empty($nombre_usuario)) {
    $sql_nombre = "SELECT COUNT(*) FROM persona WHERE nombre = ?";
    if ($id_excluir > 0) {
        $sql_nombre .= " AND id != ?";
    }

    $stmt = $conexion->prepare($sql_nombre);
    if ($id_excluir > 0) {
        $stmt->bind_param("si", $nombre_usuario, $id_excluir);
    } else {
        $stmt->bind_param("s", $nombre_usuario);
    }

    $stmt->execute();
    $stmt->bind_result($count_nombre);
    $stmt->fetch();
    $stmt->close();
    
    if ($count_nombre > 0) {
        $response['existe_nombre'] = true;
    }
}

// 2. Verificar Correo Electrónico
if (!empty($email_usuario)) {
    $sql_email = "SELECT COUNT(*) FROM persona WHERE email = ?";
    if ($id_excluir > 0) {
        $sql_email .= " AND id != ?";
    }

    $stmt2 = $conexion->prepare($sql_email);
    if ($id_excluir > 0) {
        $stmt2->bind_param("si", $email_usuario, $id_excluir);
    } else {
        $stmt2->bind_param("s", $email_usuario);
    }

    $stmt2->execute();
    $stmt2->bind_result($count_email);
    $stmt2->fetch();
    $stmt2->close();
    
    if ($count_email > 0) {
        $response['existe_email'] = true;
    }
}

echo json_encode($response);
?>