<?php
include("../conexion.php");

if(isset($_POST['id'])) {
    $id = $_POST['id'];
    $response = array();
    $modo = isset($_POST['modo']) ? $_POST['modo'] : 'salida';

    // 1. Obtener datos de descripción (stock mínimo/máximo)
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

    $condicion_stock = ($modo == 'entrada') ? "" : " AND ex.cantidad_actual > 0";
    // 2. Obtener Lotes ORDENADOS por fecha de vencimiento (los más prontos primero)
    $sql_lotes = "SELECT l.Id, l.Lote AS lote, l.fecha_fabricacion, l.fecha_vencimiento, ex.cantidad_actual 
                  FROM lotes_medicamentos l 
                  INNER JOIN existencias_stock ex ON l.Id = ex.Id_lote 
                  WHERE l.estado_lote = 'Disponible' AND l.Id_descripcion_medicamento = ? 
                  $condicion_stock
                  ORDER BY l.fecha_vencimiento ASC"; //

    $stmt_lotes = $conexion->prepare($sql_lotes);
    $stmt_lotes->bind_param("i", $id);
    $stmt_lotes->execute();
    $res_lotes = $stmt_lotes->get_result();

    $lotes = [];
    $hoy = new DateTime(); //

    while ($row = $res_lotes->fetch_assoc()) {
        $fecha_venc = new DateTime($row['fecha_vencimiento']);
        $intervalo = $hoy->diff($fecha_venc);
        
        // Obtenemos los días (el formato %r incluye el signo negativo si ya venció)
        $row['dias_restantes'] = (int)$intervalo->format("%r%a"); //
        $lotes[] = $row;
    }
    $response['lotes'] = $lotes;

    echo json_encode($response);
}
?>