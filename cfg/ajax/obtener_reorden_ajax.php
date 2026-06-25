<?php
session_start();
// Conexión subiendo un nivel desde la carpeta /ajax/ hacia /cfg/
include("../conexion.php"); 

header('Content-Type: application/json; charset=utf-8');

// Consulta utilizando la misma lógica de exclusión de lotes vencidos que ya manejas
$sql = "SELECT 
            dm.Id AS id_descripcion, 
            m.nombre_medicamento, 
            p.nombre_presentacion,
            dm.stock_minimo,
            dm.stock_maximo,
            IFNULL(SUM(ex.cantidad_actual), 0) AS existencia_total
        FROM descripcion_medicamento dm
        INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
        INNER JOIN presentacion p ON dm.Id_presentacion = p.Id_presentacion
        LEFT JOIN lotes_medicamentos lm ON dm.Id = lm.Id_descripcion_medicamento 
            AND lm.estado_lote = 'Disponible' 
            AND lm.fecha_vencimiento > CURDATE()
        LEFT JOIN existencias_stock ex ON lm.Id = ex.Id_lote
        WHERE dm.estatus = 1
        GROUP BY dm.Id
        HAVING existencia_total <= dm.stock_minimo";

$result = $conexion->query($sql);
$respuesta = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $existencia = (int)$row['existencia_total'];
        $maximo = (int)$row['stock_maximo'];
        
        // Fórmula del lote óptimo de reposición: rellenar hasta el stock máximo
        $cantidad_a_pedir = $maximo - $existencia;
        
        // Si por un ajuste manual ya supera el máximo, no sugerimos pedirlo
        if ($cantidad_a_pedir <= 0) {
            continue; 
        }

        $respuesta[] = [
            'id_descripcion' => $row['id_descripcion'],
            'nombre_completo' => $row['nombre_medicamento'] . " [" . $row['nombre_presentacion'] . "]",
            'cantidad_a_pedir' => $cantidad_a_pedir,
            'existencia_actual' => $existencia, // <- NUEVA LÍNEA
            'stock_maximo' => $maximo // <- NUEVA LÍNEA
        ];
    }
    echo json_encode($respuesta);
} else {
    echo json_encode(['error' => $conexion->error]);
}
?>