<?php
include("../conexion.php"); // Ajusta la ruta a tu conexión si es necesario

// Consulta que busca lotes vencidos con cantidad mayor a 0 en el stock
$sql = "SELECT 
            dm.Id AS id_medicamento, 
            m.nombre_medicamento, 
            p.nombre_presentacion,
            lm.Id AS lote_id, 
            lm.Lote AS lote, 
            lm.fecha_vencimiento, 
            es.cantidad_actual AS cantidad,
            GROUP_CONCAT(CONCAT(IFNULL(pa.nombre,''), ' ', IFNULL(dpm.cantidad_unidad_medida,''), ' ', IFNULL(um.unidad,'')) SEPARATOR ' + ') AS componentes
        FROM lotes_medicamentos lm
        INNER JOIN existencias_stock es ON lm.Id = es.Id_lote
        INNER JOIN descripcion_medicamento dm ON lm.Id_descripcion_medicamento = dm.Id
        INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
        INNER JOIN presentacion p ON dm.Id_presentacion = p.Id_presentacion
        LEFT JOIN detalle_principio_medicamento dpm ON dm.Id = dpm.id_medicamento
        LEFT JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida
        LEFT JOIN principio_activo pa ON dpm.id_principio_activo = pa.Id_principio_activo
        WHERE lm.fecha_vencimiento < CURDATE() 
          AND es.cantidad_actual > 0 
          AND lm.estatus = 1
        GROUP BY lm.Id";

$resultado = $conexion->query($sql);
$medicamentos_vencidos = array();

if ($resultado && $resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $comp = trim($row['componentes']) ? " (" . htmlspecialchars($row['componentes']) . ")" : "";
        
        // Estructura exacta que requiere tu tabla JS actual
        $medicamentos_vencidos[] = array(
            'id_medicamento' => $row['id_medicamento'],
            'nombre_medicamento' => htmlspecialchars($row['nombre_medicamento'] . " [" . $row['nombre_presentacion'] . "]"),
            'componentes' => htmlspecialchars($row['componentes']),
            'lote_id' => $row['lote_id'],
            'lote' => htmlspecialchars($row['lote']),
            'fecha_vencimiento' => $row['fecha_vencimiento'],
            'cantidad' => $row['cantidad'],
            'observacion' => 'Lote vencido añadido automáticamente'
        );
    }
}

echo json_encode($medicamentos_vencidos);
?>