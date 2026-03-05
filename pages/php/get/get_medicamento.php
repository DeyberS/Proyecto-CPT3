<?php
// get/get_medicamento_data.php - CÓDIGO DE DIAGNÓSTICO
header('Content-Type: application/json');

// Incluir la conexión a la base de datos (AJUSTE LA RUTA si es necesario)
include("../../../cfg/conexion.php"); 

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
$conexion->begin_transaction(); // Iniciar transacción

try {
    // --- 1. CONSULTA DE DATOS ESTÁTICOS (Stock Min/Max) ---
    // Esta consulta es la clave. Si falla, los campos de stock no se llenarán.
    $sql_descripcion = "
        SELECT 
            dm.Id AS id_inventario, 
            dm.stockMinimo, 
            dm.stockMaximo
        FROM 
            descripcion_medicamento dm
        WHERE 
            dm.Id_medicamento = ?
        LIMIT 1;
    ";
    
    $stmt_desc = $conexion->prepare($sql_descripcion);
    
    if ($stmt_desc === false) {
         throw new Exception("ERROR SQL (Prepare 1): Problema en la sintaxis de la consulta de Stock Min/Max. Detalles: " . $conexion->error);
    }
    
    $stmt_desc->bind_param("i", $id_medicamento_general);
    
    if (!$stmt_desc->execute()) {
         throw new Exception("ERROR SQL (Execute 1): No se pudo ejecutar la consulta de Stock Min/Max. Detalles: " . $stmt_desc->error);
    }

    $resultado_desc = $stmt_desc->get_result();

    if ($resultado_desc->num_rows === 0) {
        // ESTO ES LO MÁS PROBABLE: EL MEDICAMENTO NO TIENE UNA PRESENTACIÓN DE INVENTARIO
        $response['message'] = "ERROR DE DATOS: El ID de Medicamento ({$id_medicamento_general}) NO tiene un registro de inventario (descripcion_medicamento) asociado. Verifique si el medicamento fue ingresado correctamente en la tabla 'descripcion_medicamento'.";
        $stmt_desc->close();
        $conexion->rollback();
        echo json_encode($response);
        exit;
    }

    $datos_inventario = $resultado_desc->fetch_assoc();
    $id_inventario = $datos_inventario['id_inventario'];
    
    
    $response['data']['stock_minimo'] = $datos_inventario['stockMinimo'];
    $response['data']['stock_maximo'] = $datos_inventario['stockMaximo'];
    $response['data']['id_inventario'] = $datos_inventario['id_inventario'];
    $stmt_desc->close();


    // --- 2. CONSULTA DE EXISTENCIA ACTUAL (Cálculo Dinámico) ---
    // Esta consulta es la que probablemente está devolviendo CERO porque no hay movimientos vinculados o los nombres son incorrectos.
    $sql_stock = "
        SELECT 
            COALESCE(SUM(CASE 
                            WHEN tm.nombre = 'Entrada' THEN mdi.cantidad 
                            WHEN tm.nombre = 'Salida' THEN -mdi.cantidad -- Resta
                            ELSE 0 
                         END), 0) AS ExistenciaActual
        FROM 
            medicamentos_detalle_inventario mdi
        JOIN 
            detalle_inventario di ON mdi.Id_detalle_inventario = di.Id_detalle_inventario
        JOIN
            tipo_movimiento tm ON di.Id_TipoMovimiento = tm.Id_tipo_movimiento
        WHERE 
            mdi.Id_medicamento = ?;
    ";
    
    $stmt_stock = $conexion->prepare($sql_stock);

    if ($stmt_stock === false) {
         throw new Exception("ERROR SQL (Prepare 2): Problema en la sintaxis de la consulta de Existencia Actual. Detalles: " . $conexion->error);
    }
    
    $stmt_stock->bind_param("i", $id_inventario); 
    
    if (!$stmt_stock->execute()) {
         throw new Exception("ERROR SQL (Execute 2): No se pudo ejecutar la consulta de Existencia Actual. Detalles: " . $stmt_stock->error);
    }
    
    $resultado_stock = $stmt_stock->get_result();
    $row_stock = $resultado_stock->fetch_assoc();
    
    // Si la consulta no encuentra movimientos, devolverá 0 (ExistenciaActual) gracias a COALESCE. 
    $response['data']['existencia'] = $row_stock['ExistenciaActual'];
    $stmt_stock->close();
    
    // Si llegamos aquí, ambas consultas funcionaron.
    $conexion->commit();
    $response['success'] = true;
    $response['message'] = "Datos de inventario cargados con éxito.";
    
} catch (Exception $e) {
    $conexion->rollback();
    // Muestra el error atrapado en la excepción
    $response['message'] = "Fallo General: " . $e->getMessage(); 
}

$conexion->close();
echo json_encode($response);
?>