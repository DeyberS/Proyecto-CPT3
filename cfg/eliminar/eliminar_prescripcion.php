<?php
session_start();
include('../conexion.php');

if (isset($_GET['Id'])) {
    $Id = mysqli_real_escape_string($conexion, $_GET['Id']);

    mysqli_begin_transaction($conexion);

    try {
        $sql2 = "DELETE FROM prescripcion_medicamentos WHERE Id = '$Id'";
        if (!mysqli_query($conexion, $sql2)) {
            throw new Exception("⚠️ Error al eliminar el registro de la prescripcion.");
        }

        mysqli_commit($conexion);
        $_SESSION['mensaje_user_exito'] = "✅ Éxito: La prescripcion fue eliminado correctamente.";
        header("Location: ../../pages/php/papelera/farmacia_prescripciones_papelera_listado.php");

    } catch (Exception $e) {
        mysqli_rollback($conexion);
        $_SESSION['mensaje_user_error'] = "⚠️ Error de Eliminación: " . $e->getMessage();
        header("Location: ../../pages/php/papelera/farmacia_prescripciones_papelera_listado.php");
    }
}
?>