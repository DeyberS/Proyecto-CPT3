<?php
header('Content-Type: application/json');
include('../conexion.php'); 

$response = [
    'existe' => false,
    'nombre_completo' => '',
    'fecha_nacimiento' => ''
];

if (!isset($_POST['tipo_cedula']) || !isset($_POST['cedula'])) {
    echo json_encode($response);
    exit;
}

$tipo_cedula = trim($_POST['tipo_cedula'] ?? $_POST['tipo_cedula_paciente'] ?? '');
$cedula = trim($_POST['cedula'] ?? $_POST['cedula_paciente'] ?? '');

if (empty($tipo_cedula) || empty($cedula) || strlen($cedula) < 1) {
    echo json_encode($response);
    exit;
}

if ($tipo_cedula === 'RP') {
    if (!preg_match('/^[0-9]+-[0-9]+$/', $cedula)) {
        echo json_encode($response);
        exit;
    }
} else {
    if (!is_numeric($cedula)) {
        echo json_encode($response);
        exit;
    }
}

$sql = "SELECT r.Id_rol, p.id, p.tipo_cedula, p.cedula, p.nombre, p.apellido, p.fecha_nacimiento 
FROM persona p 
JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona 
JOIN rol r ON dpr.Id_rol = r.Id_rol
WHERE r.Id_rol = 3 AND tipo_cedula = ? AND cedula = ?";

if ($stmt = $conexion->prepare($sql)) {
    $stmt->bind_param("ss", $tipo_cedula, $cedula);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $paciente = $result->fetch_assoc();
        
        $response['existe'] = true;
        $response['nombre_completo'] = $paciente['nombre'] . ' ' . $paciente['apellido'];
        $response['fecha_nacimiento'] = $paciente['fecha_nacimiento'];
        $response['id'] = $paciente['id'];
        
    }
    $stmt->close();
} else {
    error_log("Error al preparar la consulta: " . $conexion->error);
}

$conexion->close();

echo json_encode($response);

?>


