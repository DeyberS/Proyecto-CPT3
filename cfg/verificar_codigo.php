<?php
session_start();
require_once "conexion.php";

header('Content-Type: application/json');

$codigo = isset($_POST['codigo']) ? mysqli_real_escape_string($conexion, trim($_POST['codigo'])) : '';
$email  = isset($_POST['email']) ? mysqli_real_escape_string($conexion, trim($_POST['email'])) : '';
$tipo   = isset($_POST['tipo']) ? trim($_POST['tipo']) : 'usuario'; // 'medico' o 'usuario'

if (empty($codigo) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos de verificación.']);
    exit;
}

// Buscamos a la persona inactiva (estatus 3) con ese correo y código
$sql_buscar = "SELECT id FROM persona WHERE email = '$email' AND token_activacion = '$codigo' AND estatus = '3' LIMIT 1";
$resultado_buscar = mysqli_query($conexion, $sql_buscar);

if (mysqli_num_rows($resultado_buscar) > 0) {
    
    // Si el código es correcto, actualizamos a estatus 2 (o activo) y limpiamos el token
    $sql_activar = "UPDATE persona SET estatus = '2', token_activacion = NULL WHERE email = '$email'";
    
    if (mysqli_query($conexion, $sql_activar)) {
        $msgExit = ($tipo === 'medico') ? '✅ Cuenta médica activada exitosamente.' : '✅ Cuenta de usuario activada exitosamente.';
        echo json_encode(['success' => true, 'message' => $msgExit]);
    } else {
        echo json_encode(['success' => false, 'message' => '❌ Error en la base de datos al intentar activar.']);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => '⚠️ Código incorrecto o la cuenta ya fue activada.']);
}
?>