<?php
session_start();
include('../conexion.php');

if (isset($_GET['Id'])) {
    $Id = mysqli_real_escape_string($conexion, $_GET['Id']);

    // Simplemente cambiamos el estado a 0 (Inactivo/Eliminado)
    $sql = "UPDATE proveedor SET estatus = 1 WHERE Id_proveedor = '$Id'";

    if (mysqli_query($conexion, $sql)) {
        $_SESSION['mensaje_user_exito'] = "✅ Éxito: el proveedor fue activado correctamente.";
        header("Location: ../../pages/php/papelera/farmacia_proveedores_papelera_listado.php");
    } else {
        $_SESSION['mensaje_user_error'] = "❌ Error de Activacion: " . $e->getMessage();
        header("Location: ../../pages/php/papelera/farmacia_proveedores_papelera_listado.php");
    }
}
?>


