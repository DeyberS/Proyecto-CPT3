<?php
session_start();
include("../conexion.php");

if (isset($_GET['Id'])) {
    $id = $_GET['Id'];

    $conexion->begin_transaction();

    try {
        $check = $conexion->prepare("SELECT COUNT(*) FROM rol_permiso WHERE Id_permiso = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $res = $check->get_result()->fetch_row();
        
        if ($res[0] > 0) {
            throw new Exception("No se puede eliminar el permiso porque tiene roles asignados.");
        }

        $stmt = $conexion->prepare("DELETE FROM permiso WHERE Id_permiso = ?");
        $stmt->bind_param("i", $id);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al intentar eliminar el registro.");
        }

        $conexion->commit();
        $_SESSION['mensaje_user_exito'] = "✅ Éxito: El permiso fue eliminado correctamente.";

    } catch (Exception $e) {
        $conexion->rollback();
        $_SESSION['mensaje_user_error'] = "❌ Error de Eliminación: " . $e->getMessage();
    }

    header("Location: ../../pages/php/papelera/cfg_permisos_papelera_listado.php");
    exit();
}