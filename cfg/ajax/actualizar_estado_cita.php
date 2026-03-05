<?php
include('../conexion.php');
if(isset($_POST['id']) && isset($_POST['estado'])){
    $id = mysqli_real_escape_string($conexion, $_POST['id']);
    $estado = mysqli_real_escape_string($conexion, $_POST['estado']);
    
    $sql = "UPDATE citas SET estado = '$estado' WHERE Id_cita = '$id'";
    if(mysqli_query($conexion, $sql)){
        echo "ok";
    } else {
        echo "error";
    }
}
?>


