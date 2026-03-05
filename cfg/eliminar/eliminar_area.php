<?php
session_start();
include("../conexion.php");

if (isset($_GET['Id'])) {
    $id = $_GET['Id'];

    $conexion->begin_transaction();

    try {
        // Validación de integridad: Evitar borrar si hay médicos vinculados
        $check = $conexion->prepare("SELECT COUNT(*) FROM medicos_departamentos WHERE Id_departamento = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $res = $check->get_result()->fetch_row();
        
        if ($res[0] > 0) {
            throw new Exception("No se puede eliminar el área porque tiene médicos asignados.");
        }

        $stmt = $conexion->prepare("DELETE FROM departamento WHERE Id_departamento = ?");
        $stmt->bind_param("i", $id);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al intentar eliminar el registro.");
        }

        $conexion->commit();
        $_SESSION['mensaje_user_exito'] = "✅ Éxito: El area fue eliminada correctamente.";

    } catch (Exception $e) {
        $conexion->rollback();
        $_SESSION['mensaje_user_error'] = "❌ Error de Eliminación: " . $e->getMessage();
    }

    header("Location: ../../pages/php/papelera/rh_areas_papelera_listado.php");
    exit();
}