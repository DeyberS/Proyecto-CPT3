<?php
session_start();
include("../conexion.php");

if (isset($_GET['Id']) && !empty($_GET['Id'])) {
    $id = $_GET['Id'];

    $conexion->begin_transaction();

    try {
        // VALIDACIÓN REAL: Revisamos en 'detalle_medico' que es donde está la FK
        $check = $conexion->prepare("SELECT COUNT(*) FROM especialidades_medicos WHERE Id_especialidad = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $res = $check->get_result()->fetch_row();
        
        if ($res[0] > 0) {
            throw new Exception("No se puede eliminar porque hay médicos asociados a esta especialidad.");
        }

        // Si no hay registros asociados, procedemos a borrar
        $stmt = $conexion->prepare("DELETE FROM especialidad WHERE Id_especialidad = ?");
        $stmt->bind_param("i", $id);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al intentar eliminar el registro.");
        }

        $conexion->commit();
        $_SESSION['mensaje_user_exito'] = "✅ Éxito: La especialidad fue eliminada correctamente.";

    } catch (Exception $e) {
        $conexion->rollback();
        $_SESSION['mensaje_user_error'] = "❌ Error de Eliminación: " . $e->getMessage();
    }

} else {
    $_SESSION['mensaje_user_error'] = "❌ No se recibio ningun dato";
}

header("Location: ../../pages/php/papelera/rh_especialidades_papelera_listado.php");
exit();
?>