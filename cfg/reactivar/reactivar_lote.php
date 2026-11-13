<?php
session_start();
include('../conexion.php');

if (isset($_GET['Id'])) {
    $Id = mysqli_real_escape_string($conexion, $_GET['Id']);

    $sql = "UPDATE lotes_medicamentos SET estatus = 1, estado_lote = 'Disponible' WHERE Id = '$Id'";

    if (mysqli_query($conexion, $sql)) {
        $_SESSION['mensaje_user_exito'] = "✅ Éxito: el lote fue desactivado correctamente.";
        header("Location: ../../pages/php/papelera/farmacia_lotes_papelera_listado.php");
    } else {
        $_SESSION['mensaje_user_error'] = "⚠️ Error de Desactivacion: " . $e->getMessage();
        header("Location: ../../pages/php/papelera/farmacia_lotes_papelera_listado.php");
    }
}
?>


