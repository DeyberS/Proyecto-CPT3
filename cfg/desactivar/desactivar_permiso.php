<?php
session_start();
include('../conexion.php');

if (isset($_GET['Id'])) {
    $Id = mysqli_real_escape_string($conexion, $_GET['Id']);

    // Simplemente cambiamos el estado a 0 (Inactivo/Eliminado)
    $sql = "UPDATE permiso SET estatus = 0 WHERE Id_permiso = '$Id'";

    if (mysqli_query($conexion, $sql)) {
        $_SESSION['mensaje_user_exito'] = "✅ Éxito: el permiso fue desactivado correctamente.";
        header("Location: ../../pages/php/cfg_permisos_listado.php");
    } else {
        $_SESSION['mensaje_user_error'] = "❌ Error de Desactivacion: " . $e->getMessage();
        header("Location: ../../pages/php/cfg_permisos_listado.php");
    }
}
?>


