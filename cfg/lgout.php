<?php
    //Initialize the session
    session_start();
    //Check if the user is logged in, if not then redirect him to login page
    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
        header("location: ../inicio.php");
        exit;
    }
    $id = $_SESSION['id'];
    require_once "conexion.php";
    
    $status_off = 2;
    $sql = "UPDATE persona SET 
                estatus='".$status_off."'
                WHERE id =".$id."";
    if ($resultado = $conexion->query($sql)) {
        session_destroy();
        // Redirect user to welcome page
        header("Location: ../inicio.php");
    }
    
?>