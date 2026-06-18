<?php
session_start();
header('Content-Type: application/json');
require_once "../conexion.php"; // Ajusta el path si es necesario

// Recolección de datos
$tipo_cedula = $_POST['tipo_cedula_med'] ?? '';
$cedula = $_POST['cedula_med'] ?? '';
$nombre = $_POST['nombre_med'] ?? '';
$apellido = $_POST['apellido_med'] ?? '';
$fecha_nacimiento = $_POST['fecha_nacimiento_med'] ?? '';
$genero = $_POST['genero_med'] ?? '';
$email = $_POST['correo_med'] ?? null;
$prefijo = $_POST['prefijo_med'] ?? '';
$telefono = $_POST['telefono_med'] ?? '';

// Asumimos fecha de ingreso como hoy para registros externos, ya que no se pide en el form
$codigo_colegiatura = $_POST['codigo_colegiatura'] ?? '';
$fecha_ingreso = date('Y-m-d'); 
$estado = 2; // Estatus de persona
$rol = 7; // Rol Médico
$password_hash = ""; // Sin acceso al sistema

$conexion->begin_transaction();

try {
    // 1. INSERT en PERSONA
    $sql_medico = "INSERT INTO persona(nombre, apellido, tipo_cedula, cedula, fecha_nacimiento, genero, email, password, estatus) 
                   VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_medico = $conexion->prepare($sql_medico);
    $stmt_medico->bind_param("ssssssssi", $nombre, $apellido, $tipo_cedula, $cedula, $fecha_nacimiento, $genero, $email, $password_hash, $estado);
    
    if (!$stmt_medico->execute()) {
        throw new Exception("Error al insertar persona: " . $stmt_medico->error);
    }
    $id_medico = $conexion->insert_id;
    $stmt_medico->close();

    // 2. INSERT en DETALLE_MEDICO (Requerido por la BD)
    $sql_detalle_medico = "INSERT INTO detalle_medico(cod_colegiatura, fecha_ingreso, tipo_medico, Id_persona) VALUES(?, ?, 'Externo', ?)";
    $stmt_detalle_medico = $conexion->prepare($sql_detalle_medico);
    $stmt_detalle_medico->bind_param("isi", $codigo_colegiatura, $fecha_ingreso, $id_medico);
    
    if (!$stmt_detalle_medico->execute()) {
        throw new Exception("Error al insertar detalle médico: " . $stmt_detalle_medico->error);
    }
    $stmt_detalle_medico->close();

    // 3. INSERT en TELEFONOS_PERSONAS
    $sql_telefono = "INSERT INTO telefonos_personas(Id_prefijo, telefono, Id_persona, estatus) VALUES(?, ?, ?, ?)";
    $stmt_telefono = $conexion->prepare($sql_telefono);
    $stmt_telefono->bind_param("sisi", $prefijo, $telefono, $id_medico, $estado);
    
    if (!$stmt_telefono->execute()) {
        throw new Exception("Error al insertar teléfono: " . $stmt_telefono->error);
    }
    $stmt_telefono->close();

    // 4. INSERT en DETALLE_PERSONA_ROL
    $sql_rol = "INSERT INTO detalle_persona_rol(Id_persona, Id_rol, estatus) VALUES(?, ?, ?)";
    $stmt_rol = $conexion->prepare($sql_rol);
    $stmt_rol->bind_param("iii", $id_medico, $rol, $estado);

    if (!$stmt_rol->execute()) {
        throw new Exception("Error al insertar rol: " . $stmt_rol->error);
    }
    $stmt_rol->close();

    $conexion->commit();
    echo json_encode(["success" => true, "message" => "Médico registrado correctamente."]);

} catch (Exception $e) {
    $conexion->rollback();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}

$conexion->close();
?>