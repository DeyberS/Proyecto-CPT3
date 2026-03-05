<?php
session_start();
include('../conexion.php');

if (isset($_GET['Id'])) {
    $Id = mysqli_real_escape_string($conexion, $_GET['Id']);

    // Simplemente cambiamos el estado a 0 (Inactivo/Eliminado)
    $sql = "UPDATE medicamento SET estatus = 1 WHERE Id_medicamento = '$Id'";

    if (mysqli_query($conexion, $sql)) {
        $_SESSION['mensaje_user_exito'] = "✅ Éxito: el medicamento fue activado correctamente.";
        header("Location: ../../pages/php/papelera/farmacia_medicamentos_papelera_listado.php");
    } else {
        $_SESSION['mensaje_user_error'] = "❌ Error de Desactivacion: " . $e->getMessage();
        header("Location: ../../pages/php/papelera/farmacia_medicamentos_papelera_listado.php");
    }
}
?>


