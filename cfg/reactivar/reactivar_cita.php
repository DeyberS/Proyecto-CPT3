<?php
session_start();
include('../conexion.php');

if (isset($_GET['Id'])) {
    $Id = mysqli_real_escape_string($conexion, $_GET['Id']);

    // Simplemente cambiamos el estado a 0 (Inactivo/Eliminado)
    $sql = "UPDATE citas SET estatus = 1 WHERE Id_cita = '$Id'";

    if (mysqli_query($conexion, $sql)) {
        $_SESSION['mensaje_user_exito'] = "✅ Éxito: la cita fue activada correctamente.";
        header("Location: ../../pages/php/papelera/citas_medicas_papelera_listado.php");
    } else {
        $_SESSION['mensaje_user_error'] = "❌ Error de Activacion: " . $e->getMessage();
        header("Location: ../../pages/php/papelera/citas_medicas_papelera_listado.php");
    }
}
?>


