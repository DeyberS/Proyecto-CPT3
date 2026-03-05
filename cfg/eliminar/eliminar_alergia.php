<?php
session_start();
include("../conexion.php");

if (isset($_GET['Id'])) {
    $id = $_GET['Id'];

    $conexion->begin_transaction();

    try {
        // Verificar si está en uso antes de borrar (Integridad Referencial)
        $check = $conexion->prepare("SELECT COUNT(*) FROM historial_alergias WHERE Id_alergia = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $res = $check->get_result()->fetch_row();
        
        if ($res[0] > 0) {
            throw new Exception("No se puede eliminar esta alergia porque está asignada a pacientes.");
        }

        $stmt = $conexion->prepare("DELETE FROM alergias_conocidas WHERE Id_alergias_conocidas = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $conexion->commit();
        $_SESSION['mensaje_user_exito'] = "✅ Éxito: La alergia fue eliminada correctamente.";

    } catch (Exception $e) {
        $conexion->rollback();
        $_SESSION['mensaje_user_error'] = "❌ Error de Eliminación: " . $e->getMessage();
    }

    header("Location: ../../pages/php/papelera/salud_alergias_papelera_listado.php");
    exit();
}


