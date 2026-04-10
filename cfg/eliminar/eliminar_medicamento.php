<?php
session_start();
include('../conexion.php'); 

if (isset($_GET['Id']) && is_numeric($_GET['Id'])) {
    
    $id_descripcion = $_GET['Id'];
    $conexion->begin_transaction();

    try {
        // 1. Obtener datos necesarios
        $sql_select = "SELECT Id_medicamento FROM descripcion_medicamento WHERE Id = ?";
        $stmt_sel = $conexion->prepare($sql_select);
        $stmt_sel->bind_param("i", $id_descripcion);
        $stmt_sel->execute();
        $res = $stmt_sel->get_result();

        if ($res->num_rows === 0) {
            throw new Exception("No se encontró el registro.");
        }

        $row = $res->fetch_assoc();
        $id_medicamento_principal = $row['Id_medicamento'];
        
        $sql_del_det = "DELETE FROM detalle_patologia_medicamento WHERE Id_medicamento = ?";
        $stmt_det = $conexion->prepare($sql_del_det);
        $stmt_det->bind_param("i", $id_descripcion);
        if (!$stmt_det->execute()) {
            throw new Exception("Error en principios activos: " . $conexion->error);
        }

        // 2. ELIMINAR DETALLES (Principios Activos)
        $sql_del_det = "DELETE FROM detalle_principio_medicamento WHERE id_medicamento = ?";
        $stmt_det = $conexion->prepare($sql_del_det);
        $stmt_det->bind_param("i", $id_descripcion);
        if (!$stmt_det->execute()) {
            throw new Exception("Error en principios activos: " . $conexion->error);
        }

        // 3. ELIMINAR LA DESCRIPCIÓN
        $sql_del_desc = "DELETE FROM descripcion_medicamento WHERE Id = ?";
        $stmt_desc = $conexion->prepare($sql_del_desc);
        $stmt_desc->bind_param("i", $id_descripcion);
        if (!$stmt_desc->execute()) {
            // Aquí es donde saltará si el medicamento está en una receta/consulta
            throw new Exception("No se puede eliminar: Este medicamento ya está vinculado a una historia médica o consulta.");
        }

        // 4. VERIFICAR SI QUEDAN OTRAS PRESENTACIONES
        $sql_check = "SELECT COUNT(*) as total FROM descripcion_medicamento WHERE Id_medicamento = ?";
        $stmt_check = $conexion->prepare($sql_check);
        $stmt_check->bind_param("i", $id_medicamento_principal);
        $stmt_check->execute();
        $count = $stmt_check->get_result()->fetch_assoc()['total'];

        if ($count == 0) {
            $conexion->query("DELETE FROM medicamento WHERE Id_medicamento = $id_medicamento_principal");
        }

        $conexion->commit();
        $_SESSION['mensaje_user_exito'] = "✅ Eliminado permanentemente.";

    } catch (Exception $e) {
        $conexion->rollback();
        // Mandamos el mensaje de error exacto a la sesión
        $_SESSION['mensaje_user_error'] = "❌ " . $e->getMessage();
    }
}

// Redirigir siempre de vuelta a la papelera
header("location: ../../pages/php/papelera/farmacia_medicamentos_papelera_listado.php");
exit;
?>