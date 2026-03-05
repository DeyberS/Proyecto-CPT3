<?php
// eliminar_cita.php
session_start();
include('../conexion.php'); // Ajusta la ruta según tu estructura real

if (isset($_GET['Id']) && !empty($_GET['Id'])) {
    
    $id_cita = intval($_GET['Id']);

    // Iniciar transacción por seguridad
    $conexion->begin_transaction();

    try {
        // Preparar la consulta de eliminación
        $sql = "DELETE FROM citas WHERE Id_cita = ?";
        $stmt = $conexion->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Error al preparar la eliminación: " . $conexion->error);
        }

        $stmt->bind_param("i", $id_cita);
        
        if (!$stmt->execute()) {
            throw new Exception("No se pudo eliminar la cita médica.");
        }

        // Si se eliminó correctamente, confirmar cambios
        $conexion->commit();

        $_SESSION['mensaje_user_exito'] = "✅ Éxito: La cita fue eliminada correctamente.";
        header("Location: ../../pages/php/papelera/citas_medicas_papelera_listado.php");

    } catch (Exception $e) {
        // En caso de error, revertir
        $conexion->rollback();
        $_SESSION['mensaje_user_error'] = "❌ Error de Eliminación: " . $e->getMessage();
        header("Location: ../../pages/php/papelera/citas_medicas_papelera_listado.php");
    }

    $stmt->close();
    $conexion->close();

} else {
    echo "ID de cita no válido.";
}

exit();
?>