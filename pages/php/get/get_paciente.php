<?php
// Establecer la cabecera para que el navegador sepa que la respuesta es JSON
header('Content-Type: application/json');

// 1. Incluir archivo de conexión a la base de datos
// NOTA: Debes asegurarte de que este archivo contenga la conexión a tu DB ($conexion)
// Por ejemplo:
// $conexion = new mysqli("localhost", "usuario", "contraseña", "base_de_datos");
include('../../../cfg/conexion.php'); 

// Array de respuesta por defecto
$response = [
    'existe' => false,
    'nombre_completo' => '',
    'fecha_nacimiento' => ''
];

// Verificar si se recibieron los datos POST
if (!isset($_POST['tipo_cedula']) || !isset($_POST['cedula'])) {
    // Si faltan datos, enviamos la respuesta por defecto (existe: false) y salimos
    echo json_encode($response);
    exit;
}

// 2. Obtener y sanear los datos de entrada
$tipo_cedula = trim($_POST['tipo_cedula']);
$cedula = trim($_POST['cedula']);

// Validaciones básicas de seguridad
if (empty($tipo_cedula) || empty($cedula) || strlen($cedula) < 1 || !is_numeric($cedula)) {
    echo json_encode($response);
    exit;
}

// 3. Preparar la consulta SQL
// Usamos parámetros (?) para prevenir inyección SQL
$sql = "SELECT r.Id_rol, p.id, p.tipo_cedula, p.cedula, p.nombre, p.apellido, p.fecha_nacimiento 
FROM persona p 
JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona 
JOIN rol r ON dpr.Id_rol = r.Id_rol
WHERE r.Id_rol = 3 AND tipo_cedula = ? AND cedula = ?";

// Inicializar y preparar la sentencia
if ($stmt = $conexion->prepare($sql)) {
    // Vincular parámetros (s: string, i: integer, d: double, b: blob)
    $stmt->bind_param("ss", $tipo_cedula, $cedula);
    
    // Ejecutar la consulta
    $stmt->execute();
    
    // Obtener el resultado
    $result = $stmt->get_result();
    
    // 4. Procesar el resultado
    if ($result->num_rows === 1) {
        $paciente = $result->fetch_assoc();
        
        // Paciente encontrado, construir la respuesta exitosa
        $response['existe'] = true;
        // Combinamos nombre y apellido, asumiendo que los tienes separados
        $response['nombre_completo'] = $paciente['nombre'] . ' ' . $paciente['apellido'];
        $response['fecha_nacimiento'] = $paciente['fecha_nacimiento'];
        $response['id'] = $paciente['id'];
        
    }
    
    // Cerrar la sentencia
    $stmt->close();
} else {
    // Error en la preparación de la consulta (útil para debug)
    // En un entorno de producción, podrías omitir esto o registrar el error.
    error_log("Error al preparar la consulta: " . $conexion->error);
}

// Cerrar la conexión
$conexion->close();

// 5. Devolver la respuesta en formato JSON
echo json_encode($response);

?>


