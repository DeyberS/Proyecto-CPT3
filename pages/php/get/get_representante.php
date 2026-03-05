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

// 2. OBTENER PARÁMETROS (CORREGIDO: Usar $_POST y los nombres correctos del JS)
$tipo_cedula = $_POST['tipo_cedula'] ?? ''; // JS envía 'tipo_cedula', no 'tipo'
$cedula = $_POST['cedula'] ?? '';

if (empty($tipo_cedula) || empty($cedula)) {
    echo json_encode(['existe' => false, 'message' => 'Faltan datos']);
    exit();
}

// Escapar datos
$tipo_cedula = $conexion->real_escape_string($tipo_cedula);
$cedula = $conexion->real_escape_string($cedula);

// 3. CONSULTA SQL
// Asegúrate de que los nombres de tablas y columnas sean exactos a tu BD
$sql = "
SELECT 
    p.id, 
    p.nombre, 
    p.apellido,
    p.fecha_nacimiento,
    p.email,
    p.genero, 
    tp.Id_prefijo, 
    tp.telefono
FROM 
    persona p 
LEFT JOIN
    telefonos_personas tp ON p.id = tp.Id_persona 
WHERE 
    p.tipo_cedula = '$tipo_cedula' 
    AND p.cedula = '$cedula'
    AND p.fecha_nacimiento <= DATE_SUB(CURDATE(), INTERVAL 18 YEAR)
LIMIT 1
";

$resultado = $conexion->query($sql);

if ($resultado && $resultado->num_rows > 0) {
    $datos = $resultado->fetch_assoc();
    
    // 4. RESPUESTA JSON (CORREGIDO: Estructura plana para coincidir con el JS)
    echo json_encode([
        'existe' => true,           // El JS espera 'existe'
        'nombre' => $datos['nombre'],
        'apellido' => $datos['apellido'],
        'fecha_nacimiento' => $datos['fecha_nacimiento'],
        'genero' => $datos['genero'],
        'email' => $datos['email'],
        'prefijo_id' => $datos['Id_prefijo'],     // El JS espera 'prefijo_id'
        'telefono_numero' => $datos['telefono']   // El JS espera 'telefono_numero'
    ]);
} else {
    // No encontrado
    echo json_encode(['existe' => false]);
}

$conexion->close();
?>