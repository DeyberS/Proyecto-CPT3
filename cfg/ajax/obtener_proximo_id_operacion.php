<?php
// obtener_proximo_id_operacion.php
include("../conexion.php"); // Asegúrate de que la ruta a tu conexión sea la correcta

header('Content-Type: application/json');

$response = array();

// Misma consulta que tienes en la cabecera de tu archivo principal
$sql_ultimo_id = "SELECT MAX(Id_detalle_inventario) AS ultimo FROM detalle_inventario";
$resultado_id = $conexion->query($sql_ultimo_id);

if ($resultado_id) {
    $row_id = $resultado_id->fetch_assoc();
    $proximo_id = ($row_id['ultimo'] ? $row_id['ultimo'] : 0) + 1;
    
    // Formateamos a 6 dígitos
    $numero_proyectado = str_pad($proximo_id, 6, "0", STR_PAD_LEFT);
    
    $response['success'] = true;
    $response['numero_proyectado'] = $numero_proyectado;
} else {
    $response['success'] = false;
}

echo json_encode($response);
?>