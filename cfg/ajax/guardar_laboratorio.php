<?php
include('../conexion.php');

if (isset($_POST['nombre'])) {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    
    $sql = "INSERT INTO laboratorio (nombre_laboratorio, estatus) VALUES ('$nombre', 1)";
    
    if ($conexion->query($sql)) {
        echo $conexion->insert_id;
    } else {
        echo "error";
    }
}
?>