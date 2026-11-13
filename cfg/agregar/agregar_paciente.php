<?php
session_start();
require_once "../conexion.php";

$tipo_cedula       = $_POST['tipo_cedula'] ?? '';
$cedula            = $_POST['cedula'] ?? '';
$nombre            = $_POST['nombre'] ?? '';
$apellido          = $_POST['apellido'] ?? '';
$fecha_nacimiento  = $_POST['fecha_nacimiento'] ?? '';
$genero            = $_POST['genero'] ?? '';
$email             = $_POST['email'] ?? '';

$situacion_conyugal = $_POST['situacion_conyugal'] ?? null;
$etnia              = $_POST['etnia'] ?? 'No';
$tipo_etnia         = $_POST['tipo_etnia'] ?? null;
$analfabeta         = $_POST['analfabeta'] ?? null;
$seguro_social      = $_POST['seguro_social'] ?? null;

$profesion          = $_POST['profesion'] ?? null;
$ocupacion          = $_POST['ocupacion'] ?? null;
$nivel_instruccion  = $_POST['nivel_instruccion'] ?? null;
$mision             = $_POST['mision'] ?? null;
$años_aprobados     = !empty($_POST['años_aprobados']) ? (int)$_POST['años_aprobados'] : null;

$lugar_nacimiento_municipio = !empty($_POST['municipio_nacimiento']) ? (int)$_POST['municipio_nacimiento'] : null;

$sector            = !empty($_POST['sector']) ? (int)$_POST['sector'] : null;
$avenida_calle     = $_POST['avenida_calle'] ?? '';
$referencia        = $_POST['referencia'] ?? '';
$tiempo_residencia = $_POST['tiempo_residencia'] ?? '';
$tiempo            = $_POST['tiempo'] ?? '';

$prefijo  = !empty($_POST['prefijo']) ? (int)$_POST['prefijo'] : null;
$telefono = $_POST['telefono'] ?? '';

$grupo_sanguineo     = $_POST['grupo_sanguineo'] ?? '';
$discapacidades_json = $_POST['discapacidades_data'] ?? '[]';
$representantes_json = $_POST['representantes_data'] ?? '[]';
$patologias_json     = $_POST['patologias_data'] ?? '[]';
$alergias_json       = $_POST['alergias_data'] ?? '[]';

$fecha_actual = date("Y-m-d H:i:s");
$estado = 1;
$rol = 3;

$stmt_verificar = $conexion->prepare("SELECT id FROM persona WHERE tipo_cedula = ? AND cedula = ? LIMIT 1");
if ($stmt_verificar === false) {
    $_SESSION['mensaje_user_error'] = "⚠️ Error al preparar la verificación: " . $conexion->error;
    header('location: ../../pages/php/pacientes_listado.php');
    exit();
}
$stmt_verificar->bind_param("ss", $tipo_cedula, $cedula);
$stmt_verificar->execute();
$stmt_verificar->store_result();

if ($stmt_verificar->num_rows > 0) {
    $_SESSION['mensaje_user_error'] = "⚠️ La cédula $tipo_cedula-$cedula ya está registrada.";
    $stmt_verificar->close();
    $conexion->close();
    header('location: ../../pages/php/pacientes_listado.php');
    exit();
}
$stmt_verificar->close();

$conexion->begin_transaction();

try {

    /* ── 1) persona (con password vacío para cumplir NOT NULL) ── */
    $password = '';
    $sql_paciente = "INSERT INTO persona 
        (nombre, apellido, tipo_cedula, cedula, fecha_nacimiento, genero, email, password, estatus)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_paciente = $conexion->prepare($sql_paciente);
    $stmt_paciente->bind_param(
        "ssssssssi",
        $nombre, $apellido, $tipo_cedula, $cedula,
        $fecha_nacimiento, $genero, $email, $password, $estado
    );
    if (!$stmt_paciente->execute()) {
        throw new Exception("Error al insertar persona: " . $stmt_paciente->error);
    }
    $id_paciente = $conexion->insert_id;
    $stmt_paciente->close();

    /* ── 2) detalle_paciente (SIN discapacidad ni representante) ── */
    $tipo_paciente = 'Interno';
    $sql_detalle = "INSERT INTO detalle_paciente
        (situacion_conyugal, etnia, tipo_etnia, analfabeta, seguro_social,
         profesion, ocupacion, nivel_instruccion, mision, años_aprobados,
         tipo_paciente, id_persona)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_detalle = $conexion->prepare($sql_detalle);
    $stmt_detalle->bind_param(
        "sssssssssisi",
        $situacion_conyugal, $etnia, $tipo_etnia, $analfabeta, $seguro_social,
        $profesion, $ocupacion, $nivel_instruccion, $mision, $años_aprobados,
        $tipo_paciente, $id_paciente
    );
    if (!$stmt_detalle->execute()) {
        throw new Exception("Error al insertar detalle paciente: " . $stmt_detalle->error);
    }
    $stmt_detalle->close();

    /* ── 3) dirección ── */
    $sql_direccion = "INSERT INTO direccion
        (tiempo_residencia, tiempo, avenida_calle, referencia, Id_persona, Id_sector, estatus)
        VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_direccion = $conexion->prepare($sql_direccion);
    $stmt_direccion->bind_param(
        "ssssiii",
        $tiempo_residencia, $tiempo, $avenida_calle, $referencia,
        $id_paciente, $sector, $estado
    );
    if (!$stmt_direccion->execute()) {
        throw new Exception("Error al insertar dirección: " . $stmt_direccion->error);
    }
    $stmt_direccion->close();

    /* ── 4) lugar de nacimiento ── */
    if ($lugar_nacimiento_municipio !== null) {
        $sql_lugar = "INSERT INTO lugar_nacimiento (Id_persona, Id_municipio) VALUES (?, ?)";
        $stmt_lugar = $conexion->prepare($sql_lugar);
        $stmt_lugar->bind_param("ii", $id_paciente, $lugar_nacimiento_municipio);
        if (!$stmt_lugar->execute()) {
            throw new Exception("Error al insertar lugar nacimiento: " . $stmt_lugar->error);
        }
        $stmt_lugar->close();
    }

    /* ── 5) teléfono ── */
    if ($prefijo !== null && $telefono !== '') {
        $sql_telefono = "INSERT INTO telefonos_personas (Id_prefijo, telefono, Id_persona, estatus)
                         VALUES (?, ?, ?, ?)";
        $stmt_telefono = $conexion->prepare($sql_telefono);
        $stmt_telefono->bind_param("isii", $prefijo, $telefono, $id_paciente, $estado);
        if (!$stmt_telefono->execute()) {
            throw new Exception("Error al insertar teléfono: " . $stmt_telefono->error);
        }
        $stmt_telefono->close();
    }

    /* ── 6) historial médico ── */
    $gs = $grupo_sanguineo !== '' ? $grupo_sanguineo : 'N/A';
    $sql_historia = "INSERT INTO historial_medico (fecha, Id_persona, grupo_sanguineo, estatus)
                     VALUES (?, ?, ?, 1)";
    $stmt_historia = $conexion->prepare($sql_historia);
    $stmt_historia->bind_param("sis", $fecha_actual, $id_paciente, $gs);
    if (!$stmt_historia->execute()) {
        throw new Exception("Error al insertar historial: " . $stmt_historia->error);
    }
    $id_historial = $conexion->insert_id;
    $stmt_historia->close();

    /* ── 7) rol paciente ── */
    $sql_rol = "INSERT INTO detalle_persona_rol (Id_persona, Id_rol, estatus) VALUES (?, ?, ?)";
    $stmt_rol = $conexion->prepare($sql_rol);
    $stmt_rol->bind_param("iii", $id_paciente, $rol, $estado);
    if (!$stmt_rol->execute()) {
        throw new Exception("Error al insertar rol: " . $stmt_rol->error);
    }
    $stmt_rol->close();

    /* ── 8) patologías (SIN Id_persona) ── */
    $patologias_array = json_decode($patologias_json, true);
    if (is_array($patologias_array) && count($patologias_array) > 0) {
        $sql_pat = "INSERT INTO historial_patologias
                    (Id_patologia, Id_Historial, fecha_registro, estatus)
                    VALUES (?, ?, ?, 1)";
        $stmt_pat = $conexion->prepare($sql_pat);
        foreach ($patologias_array as $pat) {
            $id_patologia = (int)($pat['id'] ?? 0);
            if ($id_patologia <= 0) continue;
            $fecha_pat = !empty($pat['fecha']) ? $pat['fecha'] : date('Y-m-d');
            $stmt_pat->bind_param("iis", $id_patologia, $id_historial, $fecha_pat);
            if (!$stmt_pat->execute()) {
                throw new Exception("Error al insertar patología $id_patologia: " . $stmt_pat->error);
            }
        }
        $stmt_pat->close();
    }

    /* ── 9) alergias (SIN Id_persona) ── */
    $alergias_array = json_decode($alergias_json, true);
    if (is_array($alergias_array) && count($alergias_array) > 0) {
        $sql_ale = "INSERT INTO historial_alergias
                    (Id_alergia, Id_Historial, fecha_registro, estatus)
                    VALUES (?, ?, ?, 1)";
        $stmt_ale = $conexion->prepare($sql_ale);
        foreach ($alergias_array as $ale) {
            $id_alergia = (int)($ale['id'] ?? 0);
            if ($id_alergia <= 0) continue;
            $fecha_ale = !empty($ale['fecha']) ? $ale['fecha'] : date('Y-m-d');
            $stmt_ale->bind_param("iis", $id_alergia, $id_historial, $fecha_ale);
            if (!$stmt_ale->execute()) {
                throw new Exception("Error al insertar alergia $id_alergia: " . $stmt_ale->error);
            }
        }
        $stmt_ale->close();
    }

    /* ── 10) discapacidades (Id_Historial, no Id_persona) ── */
    $disc_array = json_decode($discapacidades_json, true);
    if (is_array($disc_array) && count($disc_array) > 0) {
        $sql_disc = "INSERT INTO historial_discapacidades
                     (Id_Historial, Id_discapacidad, fecha_registro, estatus)
                     VALUES (?, ?, ?, 1)";
        $stmt_disc = $conexion->prepare($sql_disc);
        foreach ($disc_array as $disc) {
            $id_disc = (int)($disc['id'] ?? 0);
            if ($id_disc <= 0) continue;
            $fecha_disc = !empty($disc['fecha']) ? $disc['fecha'] : date('Y-m-d');
            $stmt_disc->bind_param("iis", $id_historial, $id_disc, $fecha_disc);
            if (!$stmt_disc->execute()) {
                throw new Exception("Error al insertar discapacidad $id_disc: " . $stmt_disc->error);
            }
        }
        $stmt_disc->close();
    }

    /* ── 11) representantes (Id_paciente = persona.id) ── */
    $rep_array = json_decode($representantes_json, true);
    if (is_array($rep_array) && count($rep_array) > 0) {
        $sql_rep = "INSERT INTO historial_representantes
                    (Id_paciente, Id_representante, tipo_representante, parentesco, estatus)
                    VALUES (?, ?, ?, ?, 1)";
        $stmt_rep = $conexion->prepare($sql_rep);
        foreach ($rep_array as $rep) {
            $id_rep = (int)($rep['id'] ?? 0);
            if ($id_rep <= 0) continue;
            $tipo = ($rep['tipo'] ?? 'Legal') === 'Suplente' ? 'Suplente' : 'Legal';
            $parentesco = trim($rep['parentesco'] ?? '');
            $parentesco_val = $parentesco !== '' ? $parentesco : null;
            $stmt_rep->bind_param("iiss", $id_paciente, $id_rep, $tipo, $parentesco_val);
            if (!$stmt_rep->execute()) {
                throw new Exception("Error al insertar representante $id_rep: " . $stmt_rep->error);
            }
        }
        $stmt_rep->close();
    }

    $conexion->commit();

    $esMedico = (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Medico - Usuario')
             || (isset($_SESSION['rol']) && $_SESSION['rol'] === '7');

    if ($esMedico) {
        header('location: ../../pages/php/consulta_agregar.php');
        exit();
    }

    $_SESSION['mensaje_user_exito'] = "✅ Éxito: El paciente $nombre $apellido fue agregado correctamente.";
    header('location: ../../pages/php/pacientes_listado.php');
    exit();

} catch (Exception $e) {
    $conexion->rollback();
    error_log("⚠️ Error al agregar paciente: " . $e->getMessage());
    $_SESSION['mensaje_user_error'] = "⚠️ Error de Registro: " . $e->getMessage();
    header('location: ../../pages/php/pacientes_listado.php');
    exit();
}

$conexion->close();
?>