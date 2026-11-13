<?php
include("../conexion.php"); 
header('Content-Type: application/json');

if (isset($_POST['nombre_proveedor']) && !empty(trim($_POST['nombre_proveedor']))) {
    
    $nombre = trim($_POST['nombre_proveedor']);
    $nombre = $conexion->real_escape_string($nombre);

    $sql_check = "SELECT Id_proveedor FROM proveedor WHERE nombre_proveedor = '$nombre'";
    $res_check = $conexion->query($sql_check);

    if ($res_check->num_rows > 0) {
        echo json_encode(['exito' => false, 'mensaje' => 'Este proveedor ya se encuentra registrado.']);
    } else {
        $sql_insert = "INSERT INTO proveedor (nombre_proveedor, estatus) VALUES ('$nombre', 1)";
        
        if ($conexion->query($sql_insert)) {
            $id_insertado = $conexion->insert_id;
            
            echo json_encode([
                'exito' => true,
                'id' => $id_insertado,
                'nombre' => htmlspecialchars($nombre)
            ]);
        } else {
            echo json_encode(['exito' => false, 'mensaje' => 'Error al guardar en la base de datos.']);
        }
    }
} else {
    echo json_encode(['exito' => false, 'mensaje' => 'El nombre del proveedor está vacío.']);
}

$conexion->close();
?>