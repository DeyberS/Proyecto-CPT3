<?php
include('../../../cfg/conexion.php');
if(isset($_POST['nombre'])){
    $n = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $d = mysqli_real_escape_string($conexion, $_POST['descripcion']);
    
    $sql = "INSERT INTO principio_activo (nombre, descripcion) VALUES ('$n', '$d')";
    if($conexion->query($sql)){
        echo $conexion->insert_id; // Devuelve el nuevo ID
    } else {
        echo "error";
    }
}
?>