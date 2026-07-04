<?php
// Ajusta la ruta del include según la ubicación exacta de este archivo
include("../conexion.php"); 
header('Content-Type: application/json');

// Validar que venga el dato
if (isset($_POST['nombre_proveedor']) && !empty(trim($_POST['nombre_proveedor']))) {
    
    $nombre = trim($_POST['nombre_proveedor']);
    $nombre = $conexion->real_escape_string($nombre);

    // Verificamos si el proveedor ya existe para evitar duplicados exactos
    $sql_check = "SELECT Id_proveedor FROM proveedor WHERE nombre_proveedor = '$nombre'";
    $res_check = $conexion->query($sql_check);

    if ($res_check->num_rows > 0) {
        echo json_encode(['exito' => false, 'mensaje' => 'Este proveedor ya se encuentra registrado.']);
    } else {
        // Estatus 1 por defecto (Activo)
        $sql_insert = "INSERT INTO proveedor (nombre_proveedor, estatus) VALUES ('$nombre', 1)";
        
        if ($conexion->query($sql_insert)) {
            $id_insertado = $conexion->insert_id;
            
            // Retornamos éxito junto con el ID y el Nombre para inyectarlo en el select
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