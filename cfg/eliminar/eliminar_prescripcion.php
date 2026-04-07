<?php
session_start();
include('../conexion.php');

if (isset($_GET['Id'])) {
    $Id = mysqli_real_escape_string($conexion, $_GET['Id']);

    // Iniciamos transacción para que si falla un borrado, no se ejecute el otro
    mysqli_begin_transaction($conexion);

    try {
        // 1. Verificar si hay usuarios asociados a este rol (Seguridad adicional)
        //$checkUsuarios = mysqli_query($conexion, "SELECT * FROM detalle_persona_rol WHERE Id = '$Id'");
        //if (mysqli_num_rows($checkUsuarios) > 0) {
            //throw new Exception("No se puede eliminar porque hay usuarios asignados a este rol.");
        //}

        // 2. Eliminar permisos asociados en la tabla intermedia
        //$sql1 = "DELETE FROM rol_permiso WHERE Id = '$Id'";
        //if (!mysqli_query($conexion, $sql1)) {
            //throw new Exception("Error al eliminar los permisos del rol.");
        //}

        // 3. Eliminar el rol
        $sql2 = "DELETE FROM prescripcion_medicamentos WHERE Id = '$Id'";
        if (!mysqli_query($conexion, $sql2)) {
            throw new Exception("Error al eliminar el registro de la prescripcion.");
        }

        mysqli_commit($conexion);
        $_SESSION['mensaje_user_exito'] = "✅ Éxito: La prescripcion fue eliminado correctamente.";
        header("Location: ../../pages/php/papelera/farmacia_prescripciones_papelera_listado.php");

    } catch (Exception $e) {
        mysqli_rollback($conexion);
        $_SESSION['mensaje_user_error'] = "❌ Error de Eliminación: " . $e->getMessage();
        header("Location: ../../pages/php/papelera/farmacia_prescripciones_papelera_listado.php");
    }
}
?>