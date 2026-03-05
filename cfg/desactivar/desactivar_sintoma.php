<?php
session_start();
include('../conexion.php');

if (isset($_GET['Id'])) {
    $Id = mysqli_real_escape_string($conexion, $_GET['Id']);

    // Simplemente cambiamos el estado a 0 (Inactivo/Eliminado)
    $sql = "UPDATE sintomas SET estatus = 0 WHERE Id_sintomas = '$Id'";

    if (mysqli_query($conexion, $sql)) {
        $_SESSION['mensaje_user_exito'] = "✅ Éxito: el sintoma fue desactivado correctamente.";
        header("Location: ../../pages/php/salud_sintomas_listado.php");
    } else {
        $_SESSION['mensaje_user_error'] = "❌ Error de Desactivacion: " . $e->getMessage();
        header("Location: ../../pages/php/salud_sintomas_listado.php");
    }
}
?>


