<?php
include("../conexion.php");
$response = array('encontrado' => false, 'nombre' => '');

if (isset($_POST['cedula']) && isset($_POST['tipo_cedula'])) {
    $cedula = mysqli_real_escape_string($conexion, trim($_POST['cedula']));
    $tipo_cedula = mysqli_real_escape_string($conexion, trim($_POST['tipo_cedula']));

    $sql = "SELECT p.nombre, p.apellido 
            FROM persona p
            INNER JOIN detalle_medico dm ON p.id = dm.Id_persona
            WHERE p.cedula = '$cedula' 
            AND p.tipo_cedula = '$tipo_cedula' 
            AND p.estatus IN (1, 2)
            LIMIT 1";

    $resultado = mysqli_query($conexion, $sql);

    if ($resultado && mysqli_num_rows($resultado) > 0) {
        $fila = mysqli_fetch_assoc($resultado);
        $nombre_completo = $fila['nombre'] . " " . $fila['apellido'];
        
        $response['encontrado'] = true;
        $response['nombre'] = trim($nombre_completo); 
    }
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response);
?>