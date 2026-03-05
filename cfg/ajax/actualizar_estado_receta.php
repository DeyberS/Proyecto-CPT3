<?php
include('../conexion.php');
if(isset($_POST['id']) && isset($_POST['estado_entrega'])){
    $id = mysqli_real_escape_string($conexion, $_POST['id']);
    $estado = "no entregado";
    
    $sql = "UPDATE prescripcion_medicamentos SET estatus = '$estado' WHERE Id = '$id'";
    if(mysqli_query($conexion, $sql)){
        echo "ok";
    } else {
        echo "error";
    }
}
?>


