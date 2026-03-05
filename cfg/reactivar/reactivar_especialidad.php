<?php
session_start();
include('../conexion.php');

if (isset($_GET['Id'])) {
    $Id = mysqli_real_escape_string($conexion, $_GET['Id']);

    // Simplemente cambiamos el estado a 0 (Inactivo/Eliminado)
    $sql = "UPDATE especialidad SET estatus = 1 WHERE Id_especialidad = '$Id'";

    if (mysqli_query($conexion, $sql)) {
        $_SESSION['mensaje_user_exito'] = "✅ Éxito: la especialidad fue activada correctamente.";
        header("Location: ../../pages/php/papelera/rh_especialidades_papelera_listado.php");
    } else {
        $_SESSION['mensaje_user_error'] = "❌ Error de Activacion: " . $e->getMessage();
        header("Location: ../../pages/php/papelera/rh_especialidades_papelera_listado.php");
    }
}
?>


