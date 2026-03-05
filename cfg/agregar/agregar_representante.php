<?php
session_start();
require_once "../conexion.php";

// --- 1. Recolección de Datos ---
$tipo_cedula = $_POST['tipo_cedula'] ?? '';
$cedula = $_POST['cedula'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$apellido = $_POST['apellido'] ?? '';
$fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
$lugar_nacimiento = $_POST['municipio_nacimiento'] ?? ''; // puede venir vacío
$genero = $_POST['genero'] ?? '';
$situacion_conyugal = $_POST['situacion_conyugal'] ?? '';
$email = $_POST['email'] ?? '';
$estado = 1;

// Dirección
$avenida_calle = $_POST['avenida_calle'] ?? '';
$sector = $_POST['sector'] ?? ''; // puede venir vacío
$referencia_punto = $_POST['punto_referencia'] ?? '';
$tiempo_residencia = $_POST['tiempo_residencia'] ?? '';
$tiempo = $_POST['tiempo'] ?? '';

// Teléfonos
$prefijo = $_POST['prefijo'] ?? '';
$telefono = $_POST['telefono'] ?? '';

// Variables auxiliares
$rol = 5;
$referencia = $referencia_punto;

// --- 2. Validación de Cédula Existente ---
$sql_verificar_cedula = "SELECT cedula FROM persona WHERE cedula = ? LIMIT 1";
$stmt_verificar = $conexion->prepare($sql_verificar_cedula);
if ($stmt_verificar === false) {
    $_SESSION['mensaje_estado'] = 'error';
    $_SESSION['mensaje_texto'] = "Error al preparar la verificación de cédula: " . $conexion->error;
    header('location: ../../pages/php/representantes_listado.php');
    exit();
}
$stmt_verificar->bind_param("s", $cedula);
$stmt_verificar->execute();
$stmt_verificar->store_result();

if ($stmt_verificar->num_rows > 0) {
    $_SESSION['mensaje_estado'] = 'error';
    $_SESSION['mensaje_texto'] = "Error: La cédula $tipo_cedula-$cedula ya se encuentra registrada.";
    header('location: ../../pages/php/representantes_listado.php');
    $stmt_verificar->close();
    $conexion->close();
    exit();
}
$stmt_verificar->close();

// --- 3. Inicio de la Transacción ---
$conexion->begin_transaction();

try {
    $id_representante = 0;

    // 4.1. INSERT en PERSONA
    $sql_representante = "INSERT INTO persona(nombre, apellido, tipo_cedula, cedula, fecha_nacimiento, genero, email, estatus) 
                          VALUES(?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_representante = $conexion->prepare($sql_representante);
    $stmt_representante->bind_param("sssssssi", $nombre, $apellido, $tipo_cedula, $cedula, $fecha_nacimiento, $genero, $email, $estado);
    
    if (!$stmt_representante->execute()) {
        throw new Exception("Error al insertar persona: " . $stmt_representante->error);
    }
    $id_representante = $conexion->insert_id;
    $stmt_representante->close();

    // 4.3. INSERT en DIRECCION SOLO si sector no está vacío
    if (!empty($sector)) {
        $sql_direccion = "INSERT INTO direccion(tiempo_residencia, tiempo, avenida_calle, referencia, Id_persona, Id_sector, estatus)
                          VALUES(?, ?, ?, ?, ?, ?, ?)";
        $stmt_direccion = $conexion->prepare($sql_direccion);
        $stmt_direccion->bind_param("ssssisi", $tiempo_residencia, $tiempo, $avenida_calle, $referencia, $id_representante, $sector, $estado);

        if (!$stmt_direccion->execute()) {
            throw new Exception("Error al insertar dirección: " . $stmt_direccion->error);
        }
        $stmt_direccion->close();
    }
    // 👉 Si sector está vacío, no se inserta dirección

    // 4.4. INSERT en LUGAR_NACIMIENTO SOLO si municipio no está vacío
    if (!empty($lugar_nacimiento)) {
        $sql_lugar_nacimiento = "INSERT INTO lugar_nacimiento(Id_persona, Id_municipio) VALUES(?, ?)";
        $stmt_lugar_nacimiento = $conexion->prepare($sql_lugar_nacimiento);
        $stmt_lugar_nacimiento->bind_param("ii", $id_representante, $lugar_nacimiento);
        if (!$stmt_lugar_nacimiento->execute()) {
            throw new Exception("Error al insertar lugar de nacimiento: " . $stmt_lugar_nacimiento->error);
        }
        $stmt_lugar_nacimiento->close();
    }
    // 👉 Si municipio está vacío, no se inserta lugar de nacimiento

    // 4.5. INSERT en TELEFONOS_PERSONAS
    $sql_telefono = "INSERT INTO telefonos_personas(Id_prefijo, telefono, Id_persona, estatus) VALUES(?, ?, ?, ?)";
    $stmt_telefono = $conexion->prepare($sql_telefono);
    $stmt_telefono->bind_param("sisi", $prefijo, $telefono, $id_representante, $estado);
    if (!$stmt_telefono->execute()) {
        throw new Exception("Error al insertar teléfono: " . $stmt_telefono->error);
    }
    $stmt_telefono->close();

    // 4.7. INSERT en DETALLE_PERSONA_ROL
    $sql_rol = "INSERT INTO detalle_persona_rol(Id_persona, Id_rol, estatus) VALUES(?, ?, ?)";
    $stmt_rol = $conexion->prepare($sql_rol);
    $stmt_rol->bind_param("iii", $id_representante, $rol, $estado);
    if (!$stmt_rol->execute()) {
        throw new Exception("Error al insertar rol: " . $stmt_rol->error);
    }
    $stmt_rol->close();

    // --- 7. Commit ---
    $conexion->commit();
    
    $_SESSION['mensaje_user_exito'] = '✅ Éxito: El representante ' . $nombre . ' ' . $apellido . ' fue agregado correctamente.';
    header('location: ../../pages/php/representantes_listado.php'); 
    exit();

} catch (Exception $e) {
    $conexion->rollback();
    error_log("Error de transacción al agregar el representante: " . $e->getMessage()); 
    $_SESSION['mensaje_user_error'] = '❌ Error de Registro: No se pudo registrar al representante. Detalle: ' . $e->getMessage();
    header('location: ../../pages/php/representantes_listado');
    exit();
}

$conexion->close();
?>