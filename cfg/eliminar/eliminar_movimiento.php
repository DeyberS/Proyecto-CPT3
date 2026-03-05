<?php
include("../conexion.php");
session_start();

if (isset($_GET['id'])) {
    $id_movimiento = intval($_GET['id']);
    
    $conexion->begin_transaction();

    try {
        // 1. Obtener datos para revertir el stock antes de borrar nada
        $sql_info = "SELECT mdi.Id_descripcion_medicamento, mdi.Id_lote, mdi.cantidad, di.Id_TipoMovimiento 
                     FROM medicamentos_detalle_inventario mdi
                     INNER JOIN detalle_inventario di ON mdi.Id_detalle_inventario = di.Id_detalle_inventario
                     WHERE di.Id_detalle_inventario = ?";
        $stmt_info = $conexion->prepare($sql_info);
        $stmt_info->bind_param("i", $id_movimiento);
        $stmt_info->execute();
        $movimiento = $stmt_info->get_result()->fetch_assoc();

        if ($movimiento) {
            $id_desc = $movimiento['Id_descripcion_medicamento'];
            $id_lote = $movimiento['Id_lote'];
            $cantidad = $movimiento['cantidad'];
            $tipo = $movimiento['Id_TipoMovimiento']; 

            // 2. REVERTIR EL STOCK (Antes de borrar los registros)
            // Si era Entrada (1), restamos. Si era Salida (2), sumamos.
            if ($tipo == 1) {
                $sql_revertir = "UPDATE existencias_stock SET cantidad_actual = cantidad_actual - ? 
                                 WHERE Id_descripcion_medicamento = ? AND Id_lote = ?";
            } else {
                $sql_revertir = "UPDATE existencias_stock SET cantidad_actual = cantidad_actual + ? 
                                 WHERE Id_descripcion_medicamento = ? AND Id_lote = ?";
            }
            $stmt_rev = $conexion->prepare($sql_revertir);
            $stmt_rev->bind_param("iii", $cantidad, $id_desc, $id_lote);
            $stmt_rev->execute();

            // 3. ELIMINAR PRIMERO EL DETALLE (La tabla hija que causa el error)
            $sql_del_hija = "DELETE FROM medicamentos_detalle_inventario WHERE Id_detalle_inventario = ?";
            $stmt_hija = $conexion->prepare($sql_del_hija);
            $stmt_hija->bind_param("i", $id_movimiento);
            $stmt_hija->execute();

            // 4. ELIMINAR LA CABECERA (La tabla padre)
            $sql_del_padre = "DELETE FROM detalle_inventario WHERE Id_detalle_inventario = ?";
            $stmt_padre = $conexion->prepare($sql_del_padre);
            $stmt_padre->bind_param("i", $id_movimiento);
            $stmt_padre->execute();

            $conexion->commit();
            $_SESSION['mensaje_user_exito'] = "Movimiento eliminado y stock revertido correctamente.";
        } else {
            throw new Exception("No se encontró el registro.");
        }

    } catch (Exception $e) {
        $conexion->rollback();
        $_SESSION['mensaje_user_error'] = "Error al eliminar: " . $e->getMessage();
    }
    
    header("Location: ../../pages/php/farmacia_inventario_listado.php");
    exit();
}
