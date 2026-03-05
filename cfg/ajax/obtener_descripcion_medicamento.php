<?php
include("../conexion.php");

if(isset($_POST['id'])) {
    $id = $_POST['id'];
    $response = array();

    // 1. Obtener TODOS los datos de descripcion_medicamento
    $sql_desc = "SELECT * FROM descripcion_medicamento WHERE estatus = 1 AND Id = ?";
    $stmt1 = $conexion->prepare($sql_desc);
    $stmt1->bind_param("i", $id);
    $stmt1->execute();
    $res1 = $stmt1->get_result()->fetch_assoc();

    if ($res1) {
        // 2. Obtener la suma total de existencias
        $sql_stock = "SELECT SUM(cantidad_actual) as total FROM existencias_stock WHERE Id_descripcion_medicamento = ?";
        $stmt2 = $conexion->prepare($sql_stock);
        $stmt2->bind_param("i", $id);
        $stmt2->execute();
        $res2 = $stmt2->get_result()->fetch_assoc();

        // 3. Unificamos todo en un solo array
        $response = $res1; // Esto ya incluye stock_minimo, stock_maximo, via_aplicacion, etc.
        $response['existencia_actual'] = $res2['total'] ?? 0;
    } else {
        $response['error'] = "No se encontraron datos.";
    }

    $sql_lotes = "SELECT l.Id, l.Lote AS lote, l.fecha_fabricacion, l.fecha_vencimiento, ex.cantidad_actual, l.estado_lote FROM lotes_medicamentos l INNER JOIN existencias_stock ex ON l.Id = ex.Id_lote WHERE l.estado_lote = 'Disponible' AND l.Id_descripcion_medicamento = ?";
    $stmt_lotes = $conexion->prepare($sql_lotes);
    $stmt_lotes->bind_param("i", $id);
    $stmt_lotes->execute();
    $res_lotes = $stmt_lotes->get_result();

    $lotes = [];
    while ($row = $res_lotes->fetch_assoc()) {
        $lotes[] = $row; // Ahora el array contiene: Lote, fecha_fabricacion y fecha_vencimiento
    }
    $response['lotes'] = $lotes;

    echo json_encode($response);
}
?>


