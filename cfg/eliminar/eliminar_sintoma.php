<?php
session_start();
include("../conexion.php");

if (isset($_GET['Id']) && !empty($_GET['Id'])) {
    $id = $_GET['Id'];

    $conexion->begin_transaction();

    try {
        // Verificamos integridad referencial en historial_sintomas
        $check = $conexion->prepare("SELECT COUNT(*) FROM detalle_patologia_sintomas WHERE Id_sintoma = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $res = $check->get_result()->fetch_row();
        
        if ($res[0] > 0) {
            throw new Exception("No se puede eliminar este síntoma porque está asociado a historiales de pacientes.");
        }

        $stmt = $conexion->prepare("DELETE FROM sintomas WHERE Id_sintomas = ?");
        $stmt->bind_param("i", $id);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al intentar eliminar el registro.");
        }

        $conexion->commit();
        $_SESSION['mensaje_user_exito'] = "✅ Éxito: El sintoma fue eliminada correctamente.";

    } catch (Exception $e) {
        $conexion->rollback();
        $_SESSION['mensaje_user_error'] = "❌ Error de Eliminación: " . $e->getMessage();
    }

} else {
    $_SESSION['mensaje_user_error'] = "❌ No se recibio ningun dato";
}

header("Location: ../pages/php/papelera/salud_sintomas_papelera_listado.php");
exit();
?>