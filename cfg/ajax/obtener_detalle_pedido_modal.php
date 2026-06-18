<?php
// Ajusta la ruta a tu archivo de conexión desde la carpeta ajax
include("../conexion.php");

if (isset($_POST['id_pedido'])) {
    $id_pedido = mysqli_real_escape_string($conexion, $_POST['id_pedido']);
    
    // Consulta para unir los detalles con los nombres de medicamentos y presentaciones
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

    // Retornar los datos en formato JSON para que el Javascript los lea
    header('Content-Type: application/json');
    echo json_encode($datos);
}
?>