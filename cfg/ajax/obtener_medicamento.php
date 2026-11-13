<?php
header('Content-Type: application/json');
include("../conexion.php"); 

$response = ['success' => false, 'message' => '', 'data' => []];

if (!$conexion) {
    $response['message'] = "Fallo en la conexión a la base de datos (conexion.php).";
    echo json_encode($response);
    exit;
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    $response['message'] = "Error de Petición: ID de medicamento no recibido o inválido.";
    echo json_encode($response);
    exit;
}

$id_medicamento_general = intval($_POST['id']);
$conexion->begin_transaction();

try {
    // El ID que llega es el ID de la tabla `medicamento` (genérico).
    // Buscamos su descripción para obtener stock_min/max.
    $sql_descripcion = "
        SELECT 
            dm.Id AS id_inventario, 
            dm.stock_minimo, 
            dm.stock_maximo
        FROM 
            descripcion_medicamento dm
        WHERE 
            dm.Id_medicamento = ?
        LIMIT 1;
    ";
    
    $stmt_desc = $conexion->prepare($sql_descripcion);
    
    if ($stmt_desc === false) {
         throw new Exception("ERROR SQL (Prepare 1): " . $conexion->error);
    }
    
    $stmt_desc->bind_param("i", $id_medicamento_general);
    
    if (!$stmt_desc->execute()) {
         throw new Exception("ERROR SQL (Execute 1): " . $stmt_desc->error);
    }

    $resultado_desc = $stmt_desc->get_result();

    if ($resultado_desc->num_rows === 0) {
        $response['message'] = "ERROR DE DATOS: El ID de Medicamento ({$id_medicamento_general}) NO tiene un registro de inventario (descripcion_medicamento) asociado. Verifique si el medicamento fue ingresado correctamente en la tabla 'descripcion_medicamento'.";
        $stmt_desc->close();
        $conexion->rollback();
        echo json_encode($response);
        exit;
    }

    $datos_inventario = $resultado_desc->fetch_assoc();
    $id_inventario = $datos_inventario['id_inventario'];
    
    $response['data']['stock_minimo'] = $datos_inventario['stock_minimo'];
    $response['data']['stock_maximo'] = $datos_inventario['stock_maximo'];
    $response['data']['id_inventario'] = $datos_inventario['id_inventario'];
    $stmt_desc->close();

    // Existencia actual desde existencias_stock (fuente de verdad)
    $sql_stock = "
        SELECT COALESCE(SUM(cantidad_actual), 0) AS ExistenciaActual
        FROM existencias_stock
        WHERE Id_descripcion_medicamento = ?;
    ";
    
    $stmt_stock = $conexion->prepare($sql_stock);

    if ($stmt_stock === false) {
         throw new Exception("ERROR SQL (Prepare 2): " . $conexion->error);
    }
    
    $stmt_stock->bind_param("i", $id_inventario); 
    
    if (!$stmt_stock->execute()) {
         throw new Exception("ERROR SQL (Execute 2): " . $stmt_stock->error);
    }
    
    $resultado_stock = $stmt_stock->get_result();
    $row_stock = $resultado_stock->fetch_assoc();
    
    $response['data']['existencia'] = $row_stock['ExistenciaActual'];
    $stmt_stock->close();

    $conexion->commit();
    $response['success'] = true;
    $response['message'] = "Datos de inventario cargados con éxito.";
    
} catch (Exception $e) {
    $conexion->rollback();
    $response['message'] = "Fallo General: " . $e->getMessage(); 
}

$conexion->close();
echo json_encode($response);
?>