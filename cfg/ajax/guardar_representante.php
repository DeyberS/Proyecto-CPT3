<?php
session_start();
require_once "../conexion.php";
header('Content-Type: application/json; charset=utf-8');

$ROL_REPRESENTANTE = 5;
$tipo_cedula          = trim($_POST['tipo_cedula'] ?? 'V');
$cedula               = trim($_POST['cedula'] ?? '');
$nombre               = trim($_POST['nombre'] ?? '');
$apellido             = trim($_POST['apellido'] ?? '');
$fecha_nacimiento     = $_POST['fecha_nacimiento'] ?? '';
$genero               = trim($_POST['genero'] ?? '');
$pais_nacimiento      = $_POST['pais_nacimiento'] ?? '';
$estado_nacimiento    = $_POST['estado_nacimiento'] ?? '';
$municipio_nacimiento = $_POST['municipio_nacimiento'] ?? '';
$prefijo              = $_POST['prefijo'] ?? '';
$telefono             = trim($_POST['telefono'] ?? '');
$email                = trim($_POST['email'] ?? '');
$estado_res        = $_POST['estado'] ?? '';
$municipio_res     = $_POST['municipio'] ?? '';
$sector_res        = $_POST['sector'] ?? '';
$avenida_calle     = trim($_POST['avenida_calle'] ?? '');
$referencia        = trim($_POST['referencia'] ?? '');
$tiempo_residencia = trim($_POST['tiempo_residencia'] ?? '');
$tiempo            = $_POST['tiempo'] ?? 'dia/s';
$estatus = 1;

if (empty($cedula) || empty($nombre) || empty($fecha_nacimiento) || empty($genero) || empty($telefono)) {
    echo json_encode(['success' => false, 'error' => 'Faltan campos obligatorios (cédula, nombre, fecha de nacimiento, género y teléfono).']);
    exit;
}

$ts_nac = strtotime($fecha_nacimiento);
if (!$ts_nac) {
    echo json_encode(['success' => false, 'error' => 'Fecha de nacimiento inválida.']);
    exit;
}
$edad = (int)date('Y') - (int)date('Y', $ts_nac);
$m    = (int)date('n') - (int)date('n', $ts_nac);
if ($m < 0 || ($m === 0 && (int)date('j') < (int)date('j', $ts_nac))) $edad--;
if ($edad < 18) {
    echo json_encode(['success' => false, 'error' => 'El representante debe ser mayor de 18 años.']);
    exit;
}

$stmt = $conexion->prepare("SELECT id FROM persona WHERE cedula = ? AND tipo_cedula = ? LIMIT 1");
$stmt->bind_param("ss", $cedula, $tipo_cedula);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'La cédula ' . $tipo_cedula . '-' . $cedula . ' ya está registrada en el sistema.']);
    $stmt->close();
    exit;
}
$stmt->close();

if (!empty($email)) {
    $stmt_e = $conexion->prepare("SELECT id FROM persona WHERE email = ? LIMIT 1");
    $stmt_e->bind_param("s", $email);
    $stmt_e->execute();
    $stmt_e->store_result();
    if ($stmt_e->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'El correo ' . htmlspecialchars($email) . ' ya está registrado.']);
        $stmt_e->close();
        exit;
    }
    $stmt_e->close();
}

$conexion->begin_transaction();

try {
    $sql_p = "INSERT INTO persona (nombre, apellido, tipo_cedula, cedula, fecha_nacimiento, genero, email, estatus)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_p = $conexion->prepare($sql_p);
    $stmt_p->bind_param("sssssssi", $nombre, $apellido, $tipo_cedula, $cedula, $fecha_nacimiento, $genero, $email, $estatus);
    if (!$stmt_p->execute()) throw new Exception("Error al insertar persona: " . $stmt_p->error);
    $id_representante = $conexion->insert_id;
    $stmt_p->close();

    if (!empty($municipio_nacimiento)) {
        $mun_int = (int)$municipio_nacimiento;
        $stmt_n = $conexion->prepare("INSERT INTO lugar_nacimiento (Id_persona, Id_municipio) VALUES (?, ?)");
        $stmt_n->bind_param("ii", $id_representante, $mun_int);
        $stmt_n->execute();
        $stmt_n->close();
    }

    if (!empty($telefono)) {
        $stmt_t = $conexion->prepare("INSERT INTO telefonos_personas (Id_prefijo, telefono, Id_persona, estatus) VALUES (?, ?, ?, ?)");
        $stmt_t->bind_param("ssii", $prefijo, $telefono, $id_representante, $estatus);
        $stmt_t->execute();
        $stmt_t->close();
    }

    if (!empty($sector_res) || !empty($avenida_calle) || !empty($referencia)) {
        if (!empty($sector_res)) {
            $sec_int = (int)$sector_res;
            $stmt_d = $conexion->prepare("INSERT INTO direccion (avenida_calle, referencia, tiempo_residencia, tiempo, Id_sector, Id_persona, estatus) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt_d->bind_param("ssssiii", $avenida_calle, $referencia, $tiempo_residencia, $tiempo, $sec_int, $id_representante, $estatus);
        } else {
            $stmt_d = $conexion->prepare("INSERT INTO direccion (avenida_calle, referencia, tiempo_residencia, tiempo, Id_persona, estatus) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_d->bind_param("ssssii", $avenida_calle, $referencia, $tiempo_residencia, $tiempo, $id_representante, $estatus);
        }
        $stmt_d->execute();
        $stmt_d->close();
    }

    if ($ROL_REPRESENTANTE > 0) {
        $stmt_r = $conexion->prepare("INSERT INTO detalle_persona_rol (Id_persona, Id_rol, estatus) VALUES (?, ?, ?)");
        $stmt_r->bind_param("iii", $id_representante, $ROL_REPRESENTANTE, $estatus);
        $stmt_r->execute();
        $stmt_r->close();
    }

    $conexion->commit();

    $nombre_completo = trim($nombre . ' ' . $apellido);

    echo json_encode([
        'success'         => true,
        'id'              => $id_representante,
        'tipo_cedula'     => $tipo_cedula,
        'cedula'          => $cedula,
        'nombre'          => $nombre,
        'apellido'        => $apellido,
        'nombre_completo' => $nombre_completo,
        'email'           => $email
    ]);

} catch (Exception $e) {
    $conexion->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conexion->close();
?>