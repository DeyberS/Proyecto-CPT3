<?php
include("../conexion.php");

if(isset($_POST['id'])) {
    $id = $_POST['id'];
    $response = array();
    $modo = isset($_POST['modo']) ? $_POST['modo'] : 'ajuste_salida'; 
    $id_tipo_movimiento = isset($_POST['id_tipo_movimiento']) ? $_POST['id_tipo_movimiento'] : '';

    $sql_desc = "SELECT * FROM descripcion_medicamento WHERE estatus = 1 AND Id = ?";
    $stmt1 = $conexion->prepare($sql_desc);
    $stmt1->bind_param("i", $id);
    $stmt1->execute();
    $res1 = $stmt1->get_result()->fetch_assoc();

    if ($res1) {
        $sql_stock = "SELECT SUM(cantidad_actual) as total FROM existencias_stock WHERE Id_descripcion_medicamento = ?";
        $stmt2 = $conexion->prepare($sql_stock);
        $stmt2->bind_param("i", $id);
        $stmt2->execute();
        $res2 = $stmt2->get_result()->fetch_assoc();

        $response = $res1;
        $response['existencia_actual'] = $res2['total'] ?? 0;
    }

    if ($modo == 'entrada') {
        $condicion_stock = "";
        $condicion_estado = "l.estado_lote = 'Disponible'";

    } else if ($modo == 'despacho') {
        $condicion_stock = " AND ex.cantidad_actual > 0 AND l.fecha_vencimiento > CURDATE()";
        $condicion_estado = "l.estado_lote = 'Disponible'";
    } else if ($modo == 'ajuste_salida') {
        if ($id_tipo_movimiento == '3') {
            $condicion_stock = " AND ex.cantidad_actual > 0 AND l.fecha_vencimiento <= CURDATE()";
            $condicion_estado = "l.estado_lote IN ('Disponible', 'Vencido')";
        } else {
            $condicion_stock = " AND ex.cantidad_actual > 0 AND l.fecha_vencimiento > CURDATE()";
            $condicion_estado = "l.estado_lote = 'Disponible'";
        }
    } else {
        $condicion_stock = " AND ex.cantidad_actual > 0";
        $condicion_estado = "l.estado_lote = 'Disponible'";
    }

    $sql_lotes = "SELECT l.Id, l.Lote AS lote, l.fecha_fabricacion, l.fecha_vencimiento, ex.cantidad_actual, p.nombre_proveedor 
              FROM lotes_medicamentos l 
              INNER JOIN existencias_stock ex ON l.Id = ex.Id_lote 
              LEFT JOIN proveedor p ON l.Id_proveedor = p.Id_proveedor
              WHERE $condicion_estado AND l.Id_descripcion_medicamento = ? 
              $condicion_stock
              ORDER BY l.fecha_vencimiento ASC";

    $stmt_lotes = $conexion->prepare($sql_lotes);
    $stmt_lotes->bind_param("i", $id);
    $stmt_lotes->execute();
    $res_lotes = $stmt_lotes->get_result();

    $lotes = [];
    $hoy = new DateTime('today');

    while ($row = $res_lotes->fetch_assoc()) {
        $fecha_venc = new DateTime($row['fecha_vencimiento']);
        $intervalo = $hoy->diff($fecha_venc);
        
        $row['dias_restantes'] = (int)$intervalo->format("%r%a");
        $lotes[] = $row;
    }
    $response['lotes'] = $lotes;

    echo json_encode($response);
}
?>