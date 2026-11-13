<?php
session_start();

require_once "../conexion.php";

$id_paciente = $_GET['Id'] ?? null;

if (is_null($id_paciente) || !is_numeric($id_paciente)) {
    $_SESSION['mensaje_user_error'] = "⚠️ Error: ID de paciente no proporcionado o inválido. ❌";
    header('location: ../../pages/php/papelera/pacientes_papelera_listado.php');
    exit();
}

$conexion->begin_transaction();

try {
    $sql_del_patologias = "DELETE FROM historial_patologias WHERE Id_persona = ?";
    $stmt_patologias = $conexion->prepare($sql_del_patologias);
    $stmt_patologias->bind_param("i", $id_paciente);
    if (!$stmt_patologias->execute()) {
         throw new Exception("⚠️ Error al eliminar patologías: " . $stmt_patologias->error);
    }
    $stmt_patologias->close();
    
    $sql_del_alergias = "DELETE FROM historial_alergias WHERE Id_persona = ?";
    $stmt_alergias = $conexion->prepare($sql_del_alergias);
    $stmt_alergias->bind_param("i", $id_paciente);
    if (!$stmt_alergias->execute()) {
         throw new Exception("⚠️ Error al eliminar alergias: " . $stmt_alergias->error);
    }
    $stmt_alergias->close();

    $sql_del_lugar_nac = "DELETE FROM lugar_nacimiento WHERE Id_persona = ?";
    $stmt_lugar_nac = $conexion->prepare($sql_del_lugar_nac);
    $stmt_lugar_nac->bind_param("i", $id_paciente);
    if (!$stmt_lugar_nac->execute()) {
         throw new Exception("⚠️ Error al eliminar lugar de nacimiento: " . $stmt_lugar_nac->error);
    }
    $stmt_lugar_nac->close();
    
    $sql_del_detalle = "DELETE FROM detalle_paciente WHERE Id_persona = ?";
    $stmt_detalle = $conexion->prepare($sql_del_detalle);
    $stmt_detalle->bind_param("i", $id_paciente);
    if (!$stmt_detalle->execute()) {
         throw new Exception("⚠️ Error al eliminar detalle paciente: " . $stmt_detalle->error);
    }
    $stmt_detalle->close();
    
    $sql_del_rol = "DELETE FROM detalle_persona_rol WHERE Id_persona = ?";
    $stmt_rol = $conexion->prepare($sql_del_rol);
    $stmt_rol->bind_param("i", $id_paciente);
    if (!$stmt_rol->execute()) {
         throw new Exception("⚠️ Error al eliminar rol: " . $stmt_rol->error);
    }
    $stmt_rol->close();

    $sql_del_direccion = "DELETE FROM direccion WHERE Id_persona = ?";
    $stmt_direccion = $conexion->prepare($sql_del_direccion);
    $stmt_direccion->bind_param("i", $id_paciente);
    if (!$stmt_direccion->execute()) {
        throw new Exception("⚠️ Error al eliminar dirección: " . $stmt_direccion->error);
    }
    $stmt_direccion->close();

    $sql_del_telefono = "DELETE FROM telefonos_personas WHERE Id_persona = ?";
    $stmt_telefono = $conexion->prepare($sql_del_telefono);
    $stmt_telefono->bind_param("i", $id_paciente);
    if (!$stmt_telefono->execute()) {
        throw new Exception("⚠️ Error al eliminar teléfono: " . $stmt_telefono->error);
    }
    $stmt_telefono->close();
    
    $sql_del_historial = "DELETE FROM historial_medico WHERE Id_persona = ?";
    $stmt_historial = $conexion->prepare($sql_del_historial);
    $stmt_historial->bind_param("i", $id_paciente);
    if (!$stmt_historial->execute()) {
         throw new Exception("⚠️ Error al eliminar historial médico: " . $stmt_historial->error);
    }
    $stmt_historial->close();
    
    $sql_del_persona = "DELETE FROM persona WHERE Id = ?";
    $stmt_persona = $conexion->prepare($sql_del_persona);
    $stmt_persona->bind_param("i", $id_paciente);
    if (!$stmt_persona->execute()) {
        throw new Exception("⚠️ Error al eliminar persona: " . $stmt_persona->error);
    }
    $stmt_persona->close();

    $conexion->commit();
    
    $_SESSION['mensaje_user_exito'] = '🗑️ Eliminación Exitosa: El paciente con la cédula ' . '' . $nombre . $apellido . '' . ' ha sido eliminado permanentemente.';
    header('location: ../../pages/php/papelera/pacientes_papelera_listado.php');
    exit();

} catch (Exception $e) {
    $conexion->rollback();

    error_log("Error al eliminar paciente permanentemente: " . $e->getMessage()); 
    $_SESSION['mensaje_user_error'] = '⚠️ Error de Eliminación: No se pudo eliminar el paciente. Detalle: ' . $e->getMessage();
    header('location: ../../pages/php/papelera/pacientes_papelera_listado.php');
    exit();
}

$conexion->close();
?>