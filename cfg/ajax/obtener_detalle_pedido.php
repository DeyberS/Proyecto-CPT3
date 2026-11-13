<?php
include("../conexion.php");

if (isset($_POST['id_pedido'])) {
    $id_pedido = mysqli_real_escape_string($conexion, $_POST['id_pedido']);
    
    $query = "SELECT 
                dp.cantidad_solicitada, 
                m.nombre_medicamento, 
                p.nombre_presentacion as presentacion
              FROM detalle_pedidos dp
              INNER JOIN descripcion_medicamento dm ON dp.id_descripcion_medicamento = dm.Id
              INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
              INNER JOIN presentacion p ON dm.Id_presentacion = p.Id_presentacion
              WHERE dp.id_pedido = '$id_pedido'";

    $resultado = mysqli_query($conexion, $query);
    $datos = array();

    if ($resultado && mysqli_num_rows($resultado) > 0) {
        while ($row = mysqli_fetch_assoc($resultado)) {
            $datos[] = $row;
        }
    }

    header('Content-Type: application/json');
    echo json_encode($datos);
}
?>