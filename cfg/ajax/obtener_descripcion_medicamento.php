<?php
include("../conexion.php"); //[cite: 6]

if(isset($_POST['id'])) { //[cite: 6]
    $id = $_POST['id']; //[cite: 6]
    $response = array(); //[cite: 6]
    $modo = isset($_POST['modo']) ? $_POST['modo'] : 'ajuste_salida'; //[cite: 6]
    
    // CAPTURAMOS LA OPCIÓN DEL AJUSTE (ej. opción 3)
    // Si no viene en el AJAX, simplemente quedará en blanco
    $id_tipo_movimiento = isset($_POST['id_tipo_movimiento']) ? $_POST['id_tipo_movimiento'] : '';

    // 1. Obtener datos de descripción (stock mínimo/máximo)[cite: 6]
    $sql_desc = "SELECT * FROM descripcion_medicamento WHERE estatus = 1 AND Id = ?"; //[cite: 6]
    $stmt1 = $conexion->prepare($sql_desc); //[cite: 6]
    $stmt1->bind_param("i", $id); //[cite: 6]
    $stmt1->execute(); //[cite: 6]
    $res1 = $stmt1->get_result()->fetch_assoc(); //[cite: 6]

    if ($res1) { //[cite: 6]
        $sql_stock = "SELECT SUM(cantidad_actual) as total FROM existencias_stock WHERE Id_descripcion_medicamento = ?"; //[cite: 6]
        $stmt2 = $conexion->prepare($sql_stock); //[cite: 6]
        $stmt2->bind_param("i", $id); //[cite: 6]
        $stmt2->execute(); //[cite: 6]
        $res2 = $stmt2->get_result()->fetch_assoc(); //[cite: 6]

        $response = $res1; //[cite: 6]
        $response['existencia_actual'] = $res2['total'] ?? 0; //[cite: 6]
    } //[cite: 6]

    // --- LÓGICA CORREGIDA ---
    if ($modo == 'entrada') {
        $condicion_stock = "";
        $condicion_estado = "l.estado_lote = 'Disponible'"; // Solo disponibles para entrada

    } else if ($modo == 'despacho') {
        // Despacho normal: con stock, vigentes y disponibles
        $condicion_stock = " AND ex.cantidad_actual > 0 AND l.fecha_vencimiento > CURDATE()";
        $condicion_estado = "l.estado_lote = 'Disponible'";
    } else if ($modo == 'ajuste_salida') {
        if ($id_tipo_movimiento == '3') {
            // SOLAMENTE VENCIDOS (Opción 3)
            // Permitimos buscar tanto los marcados como 'Vencido' como los 'Disponible' que ya expiraron
            $condicion_stock = " AND ex.cantidad_actual > 0 AND l.fecha_vencimiento <= CURDATE()";
            $condicion_estado = "l.estado_lote IN ('Disponible', 'Vencido')";
        } else {
            // OTRAS SALIDAS (Pérdidas, Daños) -> Mostrar vigentes
            $condicion_stock = " AND ex.cantidad_actual > 0 AND l.fecha_vencimiento > CURDATE()";
            $condicion_estado = "l.estado_lote = 'Disponible'";
        }
    } else {
        // Genérico
        $condicion_stock = " AND ex.cantidad_actual > 0";
        $condicion_estado = "l.estado_lote = 'Disponible'";
    }

    // Inyectamos la variable $condicion_estado en lugar de dejar 'Disponible' fijo
    $sql_lotes = "SELECT l.Id, l.Lote AS lote, l.fecha_fabricacion, l.fecha_vencimiento, ex.cantidad_actual, p.nombre_proveedor 
              FROM lotes_medicamentos l 
              INNER JOIN existencias_stock ex ON l.Id = ex.Id_lote 
              LEFT JOIN proveedor p ON l.Id_proveedor = p.Id_proveedor
              WHERE $condicion_estado AND l.Id_descripcion_medicamento = ? 
              $condicion_stock
              ORDER BY l.fecha_vencimiento ASC";

    $stmt_lotes = $conexion->prepare($sql_lotes); //[cite: 6]
    $stmt_lotes->bind_param("i", $id); //[cite: 6]
    $stmt_lotes->execute(); //[cite: 6]
    $res_lotes = $stmt_lotes->get_result(); //[cite: 6]

    $lotes = []; //[cite: 6]
    $hoy = new DateTime('today'); //[cite: 6]

    while ($row = $res_lotes->fetch_assoc()) { //[cite: 6]
        $fecha_venc = new DateTime($row['fecha_vencimiento']); //[cite: 6]
        $intervalo = $hoy->diff($fecha_venc); //[cite: 6]
        
        $row['dias_restantes'] = (int)$intervalo->format("%r%a"); //[cite: 6]
        $lotes[] = $row; //[cite: 6]
    } //[cite: 6]
    $response['lotes'] = $lotes; //[cite: 6]

    echo json_encode($response); //[cite: 6]
} //[cite: 6]
?>