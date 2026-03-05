<?php
session_start();
include("../conexion.php");

if (isset($_GET['Id'])) {
    $id = $_GET['Id'];

    $conexion->begin_transaction();

    try {
        // Validación de integridad: Evitar borrar si hay médicos vinculados
        $check = $conexion->prepare("SELECT COUNT(*) FROM medicamentos_detalle_inventario WHERE Id_lote = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $res = $check->get_result()->fetch_row();
        
        if ($res[0] > 0) {
            throw new Exception("No se puede eliminar el lote porque tiene un movimiento activo.");
        }

        $stmt = $conexion->prepare("DELETE FROM existencias_stock WHERE Id_lote = ?");
        $stmt->bind_param("i", $id);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al intentar eliminar el registro.");
        }

        $stmt = $conexion->prepare("DELETE FROM lotes_medicamentos WHERE Id = ?");
        $stmt->bind_param("i", $id);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al intentar eliminar el registro.");
        }

        $conexion->commit();
        $_SESSION['mensaje_user_exito'] = "✅ Éxito: El lote fue eliminado correctamente.";

    } catch (Exception $e) {
        $conexion->rollback();
        $_SESSION['mensaje_user_error'] = "❌ Error de Eliminación: " . $e->getMessage();
    }

    header("Location: ../../pages/php/papelera/farmacia_lotes_papelera_listado.php");
    exit();
}