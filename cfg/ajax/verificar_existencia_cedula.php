<?php
include('../conexion.php'); 
header('Content-Type: application/json');

$response = ['existe_cedula' => false, 'existe_email' => false];

if (isset($_POST['cedula']) || isset($_POST['email'])) {
    $cedula = isset($_POST['cedula']) ? trim($_POST['cedula']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $id_excluir = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    if (!empty($cedula)) {
        $sql_cedula = "SELECT COUNT(*) FROM persona WHERE cedula = ?";
        if ($id_excluir > 0) {
            $sql_cedula .= " AND id != ?";
        }

        $stmt = $conexion->prepare($sql_cedula);
        
        if ($id_excluir > 0) {
            $stmt->bind_param("si", $cedula, $id_excluir);
        } else {
            $stmt->bind_param("s", $cedula);
        }

        $stmt->execute();
        $stmt->bind_result($count_cedula);
        $stmt->fetch();
        $stmt->close();
        
        if ($count_cedula > 0) {
            $response['existe_cedula'] = true;
        }
    }

    if (!empty($email)) {
        $sql_email = "SELECT COUNT(*) FROM persona WHERE email = ?";
        if ($id_excluir > 0) {
            $sql_email .= " AND id != ?";
        }

        $stmt2 = $conexion->prepare($sql_email);
        
        if ($id_excluir > 0) {
            $stmt2->bind_param("si", $email, $id_excluir);
        } else {
            $stmt2->bind_param("s", $email);
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
}
?>