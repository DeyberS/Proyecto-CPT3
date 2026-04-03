<?php
session_start();
include('../conexion.php');

if (isset($_GET['Id'])) {
    $Id = mysqli_real_escape_string($conexion, $_GET['Id']);

    // Simplemente cambiamos el estado a 0 (Inactivo/Eliminado)
    $sql = "UPDATE laboratorio SET estatus = 0 WHERE Id_laboratorio = '$Id'";

    if (mysqli_query($conexion, $sql)) {
        $_SESSION['mensaje_user_exito'] = "✅ Éxito: el laboratorio fue desactivado correctamente.";
        header("Location: ../../pages/php/farmacia_laboratorio_listado.php");
    } else {
        $_SESSION['mensaje_user_error'] = "❌ Error de Desactivacion: " . $e->getMessage();
        header("Location: ../../pages/php/farmacia_laboratorio_listado.php");
    }
}
?>


