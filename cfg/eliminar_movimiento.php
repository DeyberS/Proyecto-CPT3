<?php
// Script: ../../cfg/movimiento_eliminar.php

// Incluir la conexión a la base de datos (ajuste la ruta si es necesario)
include("conexion.php");

// Asegurar que el ID del movimiento fue pasado
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../../../pages/php/farmacia_inventario.php?msg=ID de movimiento no válido");
    exit();
}

$id_movimiento = (int)$_GET['id'];

// --- INICIO DE TRANSACCIÓN ---
$conexion->begin_transaction();

try {
    // 1. OBTENER DATOS DEL MOVIMIENTO (Cantidad, Tipo, ID de Inventario)
    $sql_get_data = "
        SELECT 
            mdi.cantidad, 
            di.Id_TipoMovimiento, 
            mdi.Id_medicamento -- Esto es descripcion_medicamento.Id
        FROM 
            detalle_inventario di
        JOIN 
            medicamentos_detalle_inventario mdi ON di.Id_detalle_inventario = mdi.Id_detalle_inventario
        WHERE 
            di.Id_detalle_inventario = ?
    ";
    
    $stmt_get_data = $conexion->prepare($sql_get_data);
    $stmt_get_data->bind_param("i", $id_movimiento);
    $stmt_get_data->execute();
    $resultado = $stmt_get_data->get_result();

    if ($resultado->num_rows === 0) {
        throw new Exception("Movimiento no encontrado.");
    }

    $row = $resultado->fetch_assoc();
    $cantidad = $row['cantidad'];
    $tipoMovimiento = $row['Id_TipoMovimiento'];
    $id_inv_real = $row['Id_medicamento'];
    $stmt_get_data->close();
    
    // 2. DETERMINAR LA OPERACIÓN DE REVERSIÓN DE STOCK
    // Basado en el ID de tipo de movimiento (asumiendo 1=Entrada, 2=Salida)
    if ($tipoMovimiento == 1) { // Si fue una Entrada (+ stock), revertimos con una RESTA (-)
        $operacion = '-';
        $tipo_reversion = "restado";
    } else if ($tipoMovimiento == 2) { // Si fue una Salida (- stock), revertimos con una SUMA (+)
        $operacion = '+';
        $tipo_reversion = "sumado";
    } else {
        // Manejar otros tipos de movimiento si existen y no afectan stock
        throw new Exception("Tipo de movimiento desconocido o no afecta stock.");
    }
    
    // 3. ACTUALIZAR (REVERTIR) LA EXISTENCIA GLOBAL en descripcion_medicamento
    $sql_update_existencia = "
        UPDATE descripcion_medicamento 
        SET existencia = existencia " . $operacion . " ? 
        WHERE Id = ?
    ";
    
    $stmt_update = $conexion->prepare($sql_update_existencia);
    $stmt_update->bind_param("ii", $cantidad, $id_inv_real);
    $stmt_update->execute();
    $stmt_update->close();
    
    
    // 4. ELIMINAR EL REGISTRO DE DETALLE (medicamentos_detalle_inventario)
    $sql_delete_det = "
        DELETE FROM medicamentos_detalle_inventario 
        WHERE Id_detalle_inventario = ?
    ";
    $stmt_delete_det = $conexion->prepare($sql_delete_det);
    $stmt_delete_det->bind_param("i", $id_movimiento);
    $stmt_delete_det->execute();
    $stmt_delete_det->close();
    
    
    // 5. ELIMINAR EL REGISTRO PRINCIPAL (detalle_inventario)
    $sql_delete_mov = "
        DELETE FROM detalle_inventario 
        WHERE Id_detalle_inventario = ?
    ";
    $stmt_delete_mov = $conexion->prepare($sql_delete_mov);
    $stmt_delete_mov->bind_param("i", $id_movimiento);
    $stmt_delete_mov->execute();
    $stmt_delete_mov->close();


    // Si todo fue bien, confirmar la transacción
    $conexion->commit();
    
    // Redireccionar al éxito
    header("Location: ../pages/php/farmacia_inventario.php?msg=Movimiento eliminado y stock revertido con éxito.");
    exit();

} catch (Exception $e) {
    // Si algo falló, deshacer todos los cambios
    $conexion->rollback();
    
    // Redireccionar al error
    error_log("Error al eliminar movimiento: " . $e->getMessage());
    header("Location: ../pages/php/farmacia_inventario.php?msg=Error al eliminar movimiento: " . $e->getMessage());
    exit();
}

$conexion->close();
?>