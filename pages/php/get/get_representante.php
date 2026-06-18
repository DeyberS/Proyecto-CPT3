<?php
header('Content-Type: application/json');

// 1. CONFIGURACIÓN DE BASE DE DATOS
$host = "localhost";
$user = "root";
$password = "";
$db = "cpt3db";

$conexion = new mysqli($host, $user, $password, $db);

if ($conexion->connect_error) {
    echo json_encode(['existe' => false, 'message' => 'Error BD']);
    exit();
}

// 2. OBTENER PARÁMETROS
$tipo_cedula = $_POST['tipo_cedula'] ?? ''; 
$cedula = $_POST['cedula'] ?? '';

if (empty($tipo_cedula) || empty($cedula)) {
    echo json_encode(['existe' => false, 'message' => 'Faltan datos']);
    exit();
}

// Escapar datos
$tipo_cedula = $conexion->real_escape_string($tipo_cedula);
$cedula = $conexion->real_escape_string($cedula);

// 3. CONSULTA SQL (Modificada para verificar si tiene Id_rol = 5)
$sql = "
SELECT 
    p.id, 
    p.nombre, 
    p.apellido,
    p.fecha_nacimiento,
    p.email,
    p.genero, 
    tp.Id_prefijo, 
    tp.telefono,
    IF(EXISTS(SELECT 1 FROM detalle_persona_rol dpr WHERE dpr.Id_persona = p.id AND dpr.Id_rol = 5), 1, 0) AS es_representante
FROM 
    persona p 
LEFT JOIN
    telefonos_personas tp ON p.id = tp.Id_persona 
WHERE 
    p.tipo_cedula = '$tipo_cedula' 
    AND p.cedula = '$cedula'
LIMIT 1
";

$resultado = $conexion->query($sql);

if ($resultado && $resultado->num_rows > 0) {
    $datos = $resultado->fetch_assoc();
    
    // 4. RESPUESTA JSON
    echo json_encode([
        'existe' => true,
        'es_representante' => (bool)$datos['es_representante'], // Enviamos si es representante
        'nombre' => $datos['nombre'],
        'apellido' => $datos['apellido'],
        'fecha_nacimiento' => $datos['fecha_nacimiento'],
        'genero' => $datos['genero'],
        'email' => $datos['email'],
        'prefijo_id' => $datos['Id_prefijo'],
        'telefono_numero' => $datos['telefono']
    ]);
} else {
    // No encontrado
    echo json_encode(['existe' => false]);
}

$conexion->close();
?>