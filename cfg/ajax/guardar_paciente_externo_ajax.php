<?php
// Archivo: cfg/ajax/ajax_agregar_paciente_externo.php
session_start();
require_once "../conexion.php";

// Definimos que el retorno será JSON
header('Content-Type: application/json');

// 1. Recolección de Datos Personales
$tipo_cedula = $_POST['ext_tipo_cedula'] ?? 'V';
$cedula = $_POST['ext_cedula'] ?? '';
$nombre = trim($_POST['ext_nombre'] ?? '');
$apellido = trim($_POST['ext_apellido'] ?? '');
$fecha_nacimiento = $_POST['ext_fecha_nacimiento'] ?? '';
$genero = $_POST['ext_genero'] ?? '';
$situacion_conyugal = $_POST['ext_situacion'] ?? '';
$etnia = $_POST['ext_etnia'] ?? 'No';
$tipo_etnia = $_POST['ext_tipo_etnia'] ?? '';
$analfabeta = $_POST['ext_analfabeta'] ?? 'No';
$seguro_social = $_POST['ext_seguro'] ?? 'No';

// Teléfonos y Email
$prefijo = $_POST['ext_prefijo'] ?? '';
$telefono = $_POST['ext_telefono'] ?? '';
$email = $_POST['ext_email'] ?? '';

// Datos de Salud
$grupo_sanguineo = $_POST['ext_grupo_sanguineo'] ?? '';
$discapacidad = $_POST['ext_discapacidad'] ?? 'No';
$tipo_discapacidad = $_POST['ext_tipo_discapacidad'] ?? '';

// Variables internas por defecto
$fecha_actual = date("Y-m-d H:i:s");
$estado = 1;
$rol = 3; // 3 corresponde al rol de Paciente

// 2. Verificación de campos obligatorios en el backend
if(empty($cedula) || empty($nombre) || empty($fecha_nacimiento) || empty($genero) || empty($grupo_sanguineo) || empty($telefono)) {
    echo json_encode(['success' => false, 'error' => 'Faltan campos obligatorios.']);
    exit;
}

// 3. Verificar si la cédula ya existe
$sql_check = "SELECT cedula FROM persona WHERE cedula = ? AND tipo_cedula = ? LIMIT 1";
$stmt_check = $conexion->prepare($sql_check);
$stmt_check->bind_param("ss", $cedula, $tipo_cedula);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'La cédula '.$tipo_cedula.'-'.$cedula.' ya se encuentra registrada en el sistema.']);
    $stmt_check->close();
    exit;
}
$stmt_check->close();

// 4. Iniciar Transacción de Inserción
$conexion->begin_transaction();

try {
    // 4.1 Insertar Persona
    $sql_persona = "INSERT INTO persona (nombre, apellido, tipo_cedula, cedula, fecha_nacimiento, genero, email, estatus) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_p = $conexion->prepare($sql_persona);
    $stmt_p->bind_param("sssssssi", $nombre, $apellido, $tipo_cedula, $cedula, $fecha_nacimiento, $genero, $email, $estado);
    if (!$stmt_p->execute()) throw new Exception("Error Persona: " . $stmt_p->error);
    $id_paciente = $conexion->insert_id;
    $stmt_p->close();

    // 4.2 Insertar Detalle Paciente (Campos faltantes quedan vacíos por ser vía rápida)
    $vacio = "";
    $nvl_inst = "sin_instruccion";
    $sql_detalle = "INSERT INTO detalle_paciente (situacion_conyugal, etnia, tipo_etnia, analfabeta, seguro_social, profesion, ocupacion, nivel_instruccion, discapacidad, tipo_discapacidad, tipo_paciente, id_persona) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Externo', ?)";
    $stmt_d = $conexion->prepare($sql_detalle);
    $stmt_d->bind_param("ssssssssssi", $situacion_conyugal, $etnia, $tipo_etnia, $analfabeta, $seguro_social, $vacio, $vacio, $nvl_inst, $discapacidad, $tipo_discapacidad, $id_paciente);
    if (!$stmt_d->execute()) throw new Exception("Error Detalle: " . $stmt_d->error);
    $stmt_d->close();

    // 4.3 Insertar Dirección (Dummy - Para no romper la DB si es requerida en otras vistas)
    $referencia_dummy = "Registrado Vía Rápida (Despacho)";
    $tiempo_dummy = "dia/s";
    $sql_dir = "INSERT INTO direccion (tiempo_residencia, tiempo, avenida_calle, referencia, Id_persona, estatus) 
                VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_dir = $conexion->prepare($sql_dir);
    $stmt_dir->bind_param("ssssii", $vacio, $tiempo_dummy, $vacio, $referencia_dummy, $id_paciente, $estado);
    $stmt_dir->execute();
    $stmt_dir->close();

    // 4.4 Insertar Teléfono
    $sql_tel = "INSERT INTO telefonos_personas (Id_prefijo, telefono, Id_persona, estatus) 
                VALUES (?, ?, ?, ?)";
    $stmt_t = $conexion->prepare($sql_tel);
    $stmt_t->bind_param("ssii", $prefijo, $telefono, $id_paciente, $estado);
    $stmt_t->execute();
    $stmt_t->close();

    // 4.5 Insertar Historial Médico
    $sql_hist = "INSERT INTO historial_medico (fecha, Id_persona, grupo_sanguineo, estatus) 
                 VALUES (?, ?, ?, ?)";
    $stmt_h = $conexion->prepare($sql_hist);
    $stmt_h->bind_param("sisi", $fecha_actual, $id_paciente, $grupo_sanguineo, $estado);
    if (!$stmt_h->execute()) throw new Exception("Error Historial: " . $stmt_h->error);
    $stmt_h->close();

    // 4.6 Insertar Rol (Paciente = 3)
    $sql_rol = "INSERT INTO detalle_persona_rol (Id_persona, Id_rol, estatus) 
                VALUES (?, ?, ?)";
    $stmt_r = $conexion->prepare($sql_rol);
    $stmt_r->bind_param("iii", $id_paciente, $rol, $estado);
    $stmt_r->execute();
    $stmt_r->close();

    // 5. Commit y Respuesta JSON Exitoso
    $conexion->commit();
    
    $nombre_completo_respuesta = trim($nombre . ' ' . $apellido);
    echo json_encode([
        'success' => true, 
        'tipo_ced' => $tipo_cedula,
        'cedula' => $cedula,
        'nombre_completo' => $nombre_completo_respuesta
    ]);

} catch (Exception $e) {
    // Si falla algo, hacemos Rollback
    $conexion->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conexion->close();
?>