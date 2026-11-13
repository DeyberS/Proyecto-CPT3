<?php
    session_start();
    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
        header("location: ../../inicio.php");
        exit;
    }
    $id = $_SESSION['id'];
    require_once "../conexion.php";
    
    $status_off = 2;
    $sql = "UPDATE persona SET 
                estatus='".$status_off."'
                WHERE id =".$id."";
    if ($resultado = $conexion->query($sql)) {
        session_destroy();
        header("Location: ../../inicio.php");
    }
?>