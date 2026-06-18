<?php
include("../conexion.php"); // Ajusta la ruta a tu conexión si es diferente

$response = array();

// Recibir variables, permitiendo que estén vacías
$tipo_cedula = isset($_POST['tipo_cedula']) ? mysqli_real_escape_string($conexion, trim($_POST['tipo_cedula'])) : 'V';
$cedula = isset($_POST['cedula']) ? mysqli_real_escape_string($conexion, trim($_POST['cedula'])) : '';
$nombre = isset($_POST['nombre']) ? mysqli_real_escape_string($conexion, trim($_POST['nombre'])) : '';

// Consulta base: Traer todos los médicos
$sql = "SELECT p.cedula, p.tipo_cedula, p.nombre, p.apellido 
        FROM persona p
        INNER JOIN detalle_persona_rol u ON p.id = u.Id_persona
        INNER JOIN rol r ON u.Id_rol = r.Id_rol
        WHERE r.nombre_rol LIKE '%medico%'";

// Añadir filtros dinámicamente si el usuario escribió algo
if ($cedula !== '') {
    $sql .= " AND p.cedula LIKE '%$cedula%'";
}
if ($nombre !== '') {
    // Busca coincidencias tanto en el nombre como en el apellido
    $sql .= " AND (p.nombre LIKE '%$nombre%' OR p.apellido LIKE '%$nombre%')";
}
if ($tipo_cedula !== '') {
    $sql .= " AND p.tipo_cedula = '$tipo_cedula'";
}

// Ordenar alfabéticamente
$sql .= " ORDER BY p.nombre ASC, p.apellido ASC LIMIT 100"; 

$resultado = mysqli_query($conexion, $sql);

if ($resultado && mysqli_num_rows($resultado) > 0) {
    while($fila = mysqli_fetch_assoc($resultado)) {
        $nombre_completo = trim($fila['nombre'] . " " . $fila['apellido']);
        
        $response[] = array(
            'cedula' => $fila['cedula'],
            'tipo_cedula' => $fila['tipo_cedula'],
            'nombre' => $nombre_completo
        );
    }
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response);
?>