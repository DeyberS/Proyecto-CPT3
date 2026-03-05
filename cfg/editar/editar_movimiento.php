<?php
include("../conexion.php");
session_start();

// Verificamos qué operación vamos a realizar
$op = $_POST['op'] ?? '';
$id_detalle = isset($_POST['Id']) ? intval($_POST['Id']) : 0;

if ($id_detalle == 0 || empty($op)) {
    die("Error: Datos insuficientes para procesar la solicitud.");
}

// Iniciamos una transacción para asegurar la integridad de los datos
$conexion->begin_transaction();

try {
    if ($op == 'editar_entrada') {
        // 1. Capturar datos del formulario
        $id_desc = $_POST['Id_descripcion_medicamento'];
        $lote_nombre = $_POST['lote'];
        $cantidad_nueva = intval($_POST['cantidad']);
        $f_fab = $_POST['fecha_fabricacion'];
        $f_ven = $_POST['fecha_vencimiento'];
        $obs = $_POST['observaciones'];

        // 2. OBTENER DATOS ANTERIORES (Importante para el stock)
        $sql_antiguo = "SELECT Id_lote, cantidad, stock_momento FROM medicamentos_detalle_inventario WHERE Id_detalle_inventario = ?";
        $stmt_ant = $conexion->prepare($sql_antiguo);
        $stmt_ant->bind_param("i", $id_detalle);
        $stmt_ant->execute();
        $res_ant = $stmt_ant->get_result();
        $datos_ant = $res_ant->fetch_assoc();

        if (!$datos_ant) {
            throw new Exception("No se encontró el registro original del movimiento.");
        }

        $id_lote = $datos_ant['Id_lote'];
        $cantidad_anterior = $datos_ant['cantidad'];

        // 3. ACTUALIZAR TABLA DE LOTES
        $stmt1 = $conexion->prepare("UPDATE lotes_medicamentos SET Lote = ?, fecha_fabricacion = ?, fecha_vencimiento = ? WHERE Id = ?");
        $stmt1->bind_param("sssi", $lote_nombre, $f_fab, $f_ven, $id_lote);
        $stmt1->execute();

        // 4. ACTUALIZAR CABECERA (Observaciones)
        $stmt2 = $conexion->prepare("UPDATE detalle_inventario SET observaciones = ? WHERE Id_detalle_inventario = ?");
        $stmt2->bind_param("si", $obs, $id_detalle);
        $stmt2->execute();

        // 5. ACTUALIZAR DETALLE (Nueva cantidad)
        $stmt3 = $conexion->prepare("UPDATE medicamentos_detalle_inventario SET cantidad = ?, stock_momento = ? WHERE Id_detalle_inventario = ?");
        $stmt3->bind_param("iii", $cantidad_nueva, $cantidad_nueva, $id_detalle);
        $stmt3->execute();

        // 6. AJUSTAR LA EXISTENCIA REAL EN STOCK
        // Fórmula: Stock Actual - Cantidad Vieja + Cantidad Nueva
        $diferencia = $cantidad_nueva - $cantidad_anterior;
        
        $sql_stock = "UPDATE existencias_stock SET cantidad_actual = cantidad_actual + ? WHERE Id_lote = ? AND Id_descripcion_medicamento = ?";
        $stmt_stk = $conexion->prepare($sql_stock);
        $stmt_stk->bind_param("iii", $diferencia, $id_lote, $id_desc);
        $stmt_stk->execute();

        
    }

    if ($op == 'editar_salida') {
        // Lógica similar para salida: 
        // Stock Actual = Stock Actual + Cantidad Anterior (devuelves) - Cantidad Nueva (vuelves a sacar)
        $cantidad_nueva = intval($_POST['cantidad']);
        $razon = $_POST['observaciones'];
        
        // Obtener cantidad anterior
        $sql_ant = "SELECT Id_lote, cantidad FROM medicamentos_detalle_inventario WHERE Id_detalle_inventario = ?";
        $stmt_ant = $conexion->prepare($sql_ant);
        $stmt_ant->bind_param("i", $id_detalle);
        $stmt_ant->execute();
        $datos_ant = $stmt_ant->get_result()->fetch_assoc();
        
        $cantidad_anterior = $datos_ant['cantidad'];
        $id_lote = $datos_ant['Id_lote'];

        // Actualizar tablas
        $stmt1 = $conexion->prepare("UPDATE detalle_inventario SET observaciones = ? WHERE Id_detalle_inventario = ?");
        $stmt1->bind_param("si", $razon, $id_detalle);
        $stmt1->execute();

        $stmt2 = $conexion->prepare("UPDATE medicamentos_detalle_inventario SET cantidad = ?, stock_momento = ? WHERE Id_detalle_inventario = ?");
        $stmt2->bind_param("iii", $cantidad_nueva, $cantidad_nueva, $id_detalle);
        $stmt2->execute();

        // Ajustar Stock para Salida (es inverso a la entrada)
        $diferencia_salida = $cantidad_anterior - $cantidad_nueva;
        $sql_stock_salida = "UPDATE existencias_stock SET cantidad_actual = cantidad_actual + ? WHERE Id_lote = ?";
        $stmt_stk_s = $conexion->prepare($sql_stock_salida);
        $stmt_stk_s->bind_param("ii", $diferencia_salida, $id_lote);
        $stmt_stk_s->execute();

        $sql_stock_salida2 = "SELECT cantidad_actual FROM existencias_stock WHERE Id_lote = ?";
        $stmt_stk_s2 = $conexion->prepare($sql_stock_salida2);
        $stmt_stk_s2->bind_param("i", $id_lote);
        $stmt_stk_s2->execute();
        $datos_stock = $stmt_stk_s2->get_result()->fetch_assoc();

        $stock_momento = $datos_stock['cantidad_actual'];
        $stmt3 = $conexion->prepare("UPDATE medicamentos_detalle_inventario SET stock_momento = ? WHERE Id_detalle_inventario = ?");
        $stmt3->bind_param("ii", $stock_momento, $id_detalle);
        $stmt3->execute();
    }

    $conexion->commit();
    $_SESSION['mensaje_user_exito'] = '✅ Éxito: El movimiento y el stock han sido actualizados correctamente.';
    header("Location: ../../pages/php/farmacia_inventario_listado.php");

} catch (Exception $e) {
    $conexion->rollback();
    error_log("Error al actualizar el inventario: " . $e->getMessage());
    $_SESSION['mensaje_user_error'] = '❌ Error de Actualización: No se pudo actualizar el movimiento. Detalle: ' . $e->getMessage();
    header("Location: ../../pages/php/farmacia_inventario_listado.php");
}
?>