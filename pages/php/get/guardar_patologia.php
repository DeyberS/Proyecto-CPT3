<?php
include('../../../cfg/conexion.php');
if(isset($_POST['nombre'])){
    $n = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $cie = mysqli_real_escape_string($conexion, $_POST['cie']);
    $c = mysqli_real_escape_string($conexion, $_POST['contagioso']);
    
    $sql = "INSERT INTO patologias (nombre_patologia, descripcion, codigo_cie, contagioso, estatus) 
            VALUES ('$n', NULL, '$cie', '$c', 1)";
            
    if($conexion->query($sql)){
        echo $conexion->insert_id; // Devuelve el nuevo ID
    } else {
        echo "error";
    }
}
?>