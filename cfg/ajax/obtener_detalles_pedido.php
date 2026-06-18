<?php
session_start();
include("../conexion.php");

if(isset($_POST['id_pedido'])) {
    $id_pedido = mysqli_real_escape_string($conexion, $_POST['id_pedido']);
    
    $sql = "SELECT dp.id_descripcion_medicamento, dp.cantidad_solicitada, m.nombre_medicamento, p.nombre_presentacion,
            GROUP_CONCAT(CONCAT(IFNULL(pa.nombre,''), ' ', IFNULL(dpm.cantidad_unidad_medida,''), ' ', IFNULL(um.unidad,'')) SEPARATOR ' + ') AS componentes
            FROM detalle_pedidos dp
            INNER JOIN descripcion_medicamento dm ON dp.id_descripcion_medicamento = dm.Id
            INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
            INNER JOIN presentacion p ON dm.Id_presentacion = p.Id_presentacion
            LEFT JOIN detalle_principio_medicamento dpm ON dm.Id = dpm.id_medicamento
            LEFT JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida
            LEFT JOIN principio_activo pa ON dpm.id_principio_activo = pa.Id_principio_activo
            WHERE dp.id_pedido = '$id_pedido'
            GROUP BY dm.Id, dp.id_detalle";
            
    $resultado = mysqli_query($conexion, $sql);
    
    $detalles = array();
    while($row = mysqli_fetch_assoc($resultado)) {
        $detalles[] = $row;
    }
    
    echo json_encode($detalles);
}
?>