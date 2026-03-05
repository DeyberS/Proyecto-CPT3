<?php
session_start();
// Incluye el archivo de conexión a la base de datos
include('../conexion.php'); 

if (isset($_GET['Id']) && is_numeric($_GET['Id'])) {
    
    // 1. Obtener y sanitizar el ID de la descripción
    // IMPORTANTE: Asumimos que el ID pasado por la URL (?Id=) es el Id único de la descripción.
    $id_descripcion = $conexion->real_escape_string($_GET['Id']);

    $eliminacion_exitosa = true;
    $mensaje_error = '';

    // Iniciar transacción para asegurar que todas las operaciones se completen o ninguna
    $conexion->begin_transaction();

    try {
        // PASO 1: Obtener el Id_medicamento asociado antes de eliminar la descripción
        $sql_select = "SELECT Id_medicamento FROM descripcion_medicamento WHERE Id = '$id_descripcion'";
        $resultado = $conexion->query($sql_select);

        if ($resultado->num_rows === 0) {
            throw new Exception("Error: No se encontró el registro de descripción con ID: $id_descripcion.");
        }

        $row = $resultado->fetch_assoc();
        $id_medicamento = $row['Id_medicamento'];
        $resultado->free();

        // PASO 2: Eliminar la descripción específica del medicamento
        $sql_delete_descripcion = "DELETE FROM descripcion_medicamento WHERE Id = '$id_descripcion'";
        if (!$conexion->query($sql_delete_descripcion)) {
            throw new Exception("Error al eliminar la descripción: " . $conexion->error);
        }

        // PASO 3: Verificar si quedan otras descripciones para ese Id_medicamento
        $sql_check_other_desc = "SELECT COUNT(*) FROM descripcion_medicamento WHERE Id_medicamento = '$id_medicamento'";
        $count_result = $conexion->query($sql_check_other_desc);
        $count_row = $count_result->fetch_row();
        $remaining_descriptions = $count_row[0];
        $count_result->free();

        // PASO 4: Si no quedan descripciones, eliminar el registro principal de la tabla 'medicamento'
        if ($remaining_descriptions == 0) {
            $sql_delete_medicamento = "DELETE FROM medicamento WHERE Id_medicamento = '$id_medicamento'";
            if (!$conexion->query($sql_delete_medicamento)) {
                throw new Exception("Error al eliminar el medicamento principal: " . $conexion->error);
            }
        }
        
        // Si todo va bien, confirmar la transacción (COMMIT)
        $conexion->commit();

    } catch (Exception $e) {
        // Si hay un error, revertir todos los cambios (ROLLBACK)
        $conexion->rollback();
        $eliminacion_exitosa = false;
        $mensaje_error = $e->getMessage();
    }
    
    // Cierre de la conexión
    $conexion->close();

    // Redirección con mensaje de estado
    if ($eliminacion_exitosa) {
        $_SESSION['mensaje_user_exito'] = "✅ Éxito: El medicamento fue eliminado correctamente.";
        header("Location: ../../pages/php/papelera/farmacia_medicamentos_papelera_listado.php");
        exit();
    } else {
        $_SESSION['mensaje_user_error'] = "❌ Error de Eliminación: " . $e->getMessage();
        header("Location: ../../pages/php/papelera/farmacia_medicamentos_papelera_listado.php");
        exit();
    }

} else {
    // Si no se proporciona un ID válido en la URL
    header("Location: ../../pages/php/papelera/armacia_medicamentos_papelera_listado.php?status=error&message=ID de registro no proporcionado.");
    exit();
}
?>