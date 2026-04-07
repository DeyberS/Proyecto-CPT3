<?php
// get/get_medicamentos_base.php
include("../../../cfg/conexion.php"); 

header('Content-Type: application/json');
$response = ['success' => false, 'data' => [], 'error' => ''];

if (isset($conexion)) {
    $sql = "SELECT 
                dm.Id AS Id_descripcion, 
                m.nombre_medicamento, 
                dm.contenido_neto,
                dm.via_aplicacion,
                p.nombre_presentacion,
                GROUP_CONCAT(CONCAT(IFNULL(pa.nombre,''), ' ', IFNULL(dpm.cantidad_unidad_medida,''), IFNULL(um.unidad,'')) SEPARATOR ' + ') AS componentes
            FROM descripcion_medicamento dm
            INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
            LEFT JOIN presentacion p ON dm.Id_presentacion = p.Id_presentacion
            LEFT JOIN detalle_principio_medicamento dpm ON dm.Id = dpm.id_medicamento
            LEFT JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida
            LEFT JOIN principio_activo pa ON dpm.id_principio_activo = pa.Id_principio_activo
            WHERE dm.estatus = '1'
            GROUP BY dm.Id
            ORDER BY m.nombre_medicamento ASC";

    $result = $conexion->query($sql);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $response['data'][] = $row;
        }
        $response['success'] = true;
    } else {
        $response['error'] = "Error SQL: " . $conexion->error;
    }
} else {
    $response['error'] = "Error de conexión: No se encontró la variable \$conexion";
}

echo json_encode($response);
// NO PONGAS NADA MÁS AQUÍ