<?php
session_start();
include("../conexion.php");

if (isset($_GET['Id'])) {
    $id = $_GET['Id'];

    $conexion->begin_transaction();

    try {
        $check = $conexion->prepare("SELECT COUNT(*) FROM descripcion_medicamento WHERE Id_laboratorio = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $res = $check->get_result()->fetch_row();
        
        if ($res[0] > 0) {
            throw new Exception("No se puede eliminar el laboratorio porque tiene medicamentos asignados.");
        }

        $stmt = $conexion->prepare("DELETE FROM laboratorio WHERE Id_laboratorio = ?");
        $stmt->bind_param("i", $id);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al intentar eliminar el registro.");
        }

        $conexion->commit();
        $_SESSION['mensaje_user_exito'] = "✅ Éxito: El laboratorio fue eliminado correctamente.";

    } catch (Exception $e) {
        $conexion->rollback();
        $_SESSION['mensaje_user_error'] = "❌ Error de Eliminación: " . $e->getMessage();
    }

    header("Location: ../../pages/php/papelera/farmacia_laboratorio_papelera_listado.php");
    exit();
}