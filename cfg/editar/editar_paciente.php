<?php
session_start();
require_once "../conexion.php"; 

$id_paciente       = (int)($_POST['Id'] ?? 0);
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

$prefijo  = !empty($_POST['prefijo']) ? (int)$_POST['prefijo'] : null;
$telefono = $_POST['telefono'] ?? '';

$sector            = !empty($_POST['sector']) ? (int)$_POST['sector'] : null;
$avenida_calle     = $_POST['avenida_calle'] ?? '';
$referencia        = $_POST['punto_referencia'] ?? '';
$tiempo_residencia = $_POST['tiempo_residencia'] ?? '';
$tiempo            = $_POST['tiempo'] ?? '';

$grupo_sanguineo = $_POST['grupo_sanguineo'] ?? '';
if ($grupo_sanguineo === '') $grupo_sanguineo = 'N/A';

$patologias_json      = $_POST['patologias_data'] ?? '[]';
$alergias_json        = $_POST['alergias_data'] ?? '[]';
$discapacidades_json  = $_POST['discapacidades_data'] ?? '[]';
$representantes_json  = $_POST['representantes_data'] ?? '[]';

$estado = 1;

/* Verificar cédula duplicada */
$stmt_check = $conexion->prepare("SELECT id FROM persona WHERE tipo_cedula = ? AND cedula = ? AND id != ?");
$stmt_check->bind_param("ssi", $tipo_cedula, $cedula, $id_paciente);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows > 0) {
    $_SESSION['mensaje_user_error'] = "La cédula $tipo_cedula-$cedula ya está registrada.";
    $stmt_check->close();
    $conexion->close();
    header("location: ../../pages/php/pacientes_listado.php");
    exit();
}
$stmt_check->close();

$conexion->begin_transaction();

try {

    /* Obtener id_historial (necesario para patologías/alergias/discapacidades) */
    $stmt_h = $conexion->prepare("SELECT id_historial FROM historial_medico WHERE Id_persona = ? LIMIT 1");
    $stmt_h->bind_param("i", $id_paciente);
    $stmt_h->execute();
    $row_h = $stmt_h->get_result()->fetch_assoc();
    $stmt_h->close();

    if (!$row_h) {
        throw new Exception("El paciente no tiene historial médico.");
    }
    $id_historial = (int)$row_h['id_historial'];

    /* ── 1) persona ── */
    $stmt = $conexion->prepare("UPDATE persona SET 
        tipo_cedula = ?, cedula = ?, nombre = ?, apellido = ?,
        fecha_nacimiento = ?, genero = ?, email = ?
        WHERE id = ?");
    $stmt->bind_param("sssssssi",
        $tipo_cedula, $cedula, $nombre, $apellido,
        $fecha_nacimiento, $genero, $email, $id_paciente);
    if (!$stmt->execute()) {
        throw new Exception("persona: " . $stmt->error);
    }
    $stmt->close();

    /* ── 2) detalle_paciente (SIN discapacidad ni representante) ── */
    $stmt = $conexion->prepare("UPDATE detalle_paciente SET
        situacion_conyugal = ?, etnia = ?, tipo_etnia = ?, analfabeta = ?,
        seguro_social = ?, profesion = ?, ocupacion = ?, nivel_instruccion = ?,
        mision = ?, años_aprobados = ?
        WHERE id_persona = ?");
    $stmt->bind_param("sssssssssii",
        $situacion_conyugal, $etnia, $tipo_etnia, $analfabeta,
        $seguro_social, $profesion, $ocupacion, $nivel_instruccion,
        $mision, $años_aprobados, $id_paciente);
    if (!$stmt->execute()) {
        throw new Exception("detalle_paciente: " . $stmt->error);
    }
    $stmt->close();

    /* ── 3) dirección (UPSERT por si no existía) ── */
    $stmt = $conexion->prepare("INSERT INTO direccion
        (tiempo_residencia, tiempo, avenida_calle, referencia, Id_persona, Id_sector, estatus)
        VALUES (?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            tiempo_residencia = VALUES(tiempo_residencia),
            tiempo = VALUES(tiempo),
            avenida_calle = VALUES(avenida_calle),
            referencia = VALUES(referencia),
            Id_sector = VALUES(Id_sector)");
    $stmt->bind_param("ssssiii",
        $tiempo_residencia, $tiempo, $avenida_calle, $referencia,
        $id_paciente, $sector, $estado);
    if (!$stmt->execute()) {
        throw new Exception("direccion: " . $stmt->error);
    }
    $stmt->close();

    /* ── 4) lugar de nacimiento ── */
    if ($lugar_nacimiento_municipio !== null) {
        $stmt = $conexion->prepare("INSERT INTO lugar_nacimiento (Id_persona, Id_municipio)
            VALUES (?, ?) ON DUPLICATE KEY UPDATE Id_municipio = VALUES(Id_municipio)");
        $stmt->bind_param("ii", $id_paciente, $lugar_nacimiento_municipio);
        if (!$stmt->execute()) {
            throw new Exception("lugar_nacimiento: " . $stmt->error);
        }
        $stmt->close();
    }

    /* ── 5) teléfono (UPDATE si existe, INSERT si no) ── */
    if ($prefijo !== null && $telefono !== ''
    ) {
        $stmt = $conexion->prepare("
        SELECT Id FROM telefonos_personas
        WHERE Id_persona = ? AND estatus = 1
        ORDER BY Id ASC LIMIT 1
    ");
        $stmt->bind_param("i", $id_paciente);
        $stmt->execute();
        $row_tel = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row_tel) {
            $stmt = $conexion->prepare("
            UPDATE telefonos_personas
            SET Id_prefijo = ?, telefono = ?
            WHERE Id = ?
        ");
            $stmt->bind_param("isi", $prefijo, $telefono, $row_tel['Id']);
            if (!$stmt->execute()) {
                throw new Exception("telefono UPDATE: " . $stmt->error);
            }
            $stmt->close();
        } else {
            $stmt = $conexion->prepare("
            INSERT INTO telefonos_personas
                (Id_prefijo, telefono, Id_persona, estatus)
            VALUES (?, ?, ?, ?)
        ");
            $stmt->bind_param("isii", $prefijo, $telefono, $id_paciente, $estado);
            if (!$stmt->execute()) {
                throw new Exception("telefono INSERT: " . $stmt->error);
            }
            $stmt->close();
        }
    }

    /* ── 6) historial_medico (grupo sanguíneo) ── */
    $stmt = $conexion->prepare("UPDATE historial_medico SET grupo_sanguineo = ? WHERE id_historial = ?");
    $stmt->bind_param("si", $grupo_sanguineo, $id_historial);
    if (!$stmt->execute()) {
        throw new Exception("historial_medico: " . $stmt->error);
    }
    $stmt->close();

    /* ── 7) Patologías: DELETE + INSERT con Id_Historial ── */
    $stmt = $conexion->prepare("DELETE FROM historial_patologias WHERE Id_Historial = ?");
    $stmt->bind_param("i", $id_historial);
    if (!$stmt->execute()) {
        throw new Exception("DELETE patologías: " . $stmt->error);
    }
    $stmt->close();

    $patologias_array = json_decode($patologias_json, true);
    if (is_array($patologias_array) && count($patologias_array) > 0) {
        $stmt = $conexion->prepare("INSERT INTO historial_patologias
            (Id_patologia, Id_Historial, fecha_registro, estatus)
            VALUES (?, ?, ?, 1)");
        foreach ($patologias_array as $pat) {
            $id_pat = (int)($pat['id'] ?? 0);
            if ($id_pat <= 0) continue;
            $fecha = !empty($pat['fecha']) ? $pat['fecha'] : date('Y-m-d');
            $stmt->bind_param("iis", $id_pat, $id_historial, $fecha);
            if (!$stmt->execute()) {
                throw new Exception("INSERT patología $id_pat: " . $stmt->error);
            }
        }
        $stmt->close();
    }

    /* ── 8) Alergias: DELETE + INSERT con Id_Historial ── */
    $stmt = $conexion->prepare("DELETE FROM historial_alergias WHERE Id_Historial = ?");
    $stmt->bind_param("i", $id_historial);
    if (!$stmt->execute()) {
        throw new Exception("DELETE alergias: " . $stmt->error);
    }
    $stmt->close();

    $alergias_array = json_decode($alergias_json, true);
    if (is_array($alergias_array) && count($alergias_array) > 0) {
        $stmt = $conexion->prepare("INSERT INTO historial_alergias
            (Id_alergia, Id_Historial, fecha_registro, estatus)
            VALUES (?, ?, ?, 1)");
        foreach ($alergias_array as $ale) {
            $id_ale = (int)($ale['id'] ?? 0);
            if ($id_ale <= 0) continue;
            $fecha = !empty($ale['fecha']) ? $ale['fecha'] : date('Y-m-d');
            $stmt->bind_param("iis", $id_ale, $id_historial, $fecha);
            if (!$stmt->execute()) {
                throw new Exception("INSERT alergia $id_ale: " . $stmt->error);
            }
        }
        $stmt->close();
    }

    /* ── 9) Discapacidades: DELETE + INSERT con Id_Historial ── */
    $stmt = $conexion->prepare("DELETE FROM historial_discapacidades WHERE Id_Historial = ?");
    $stmt->bind_param("i", $id_historial);
    if (!$stmt->execute()) {
        throw new Exception("DELETE discapacidades: " . $stmt->error);
    }
    $stmt->close();

    $disc_array = json_decode($discapacidades_json, true);
    if (is_array($disc_array) && count($disc_array) > 0) {
        $stmt = $conexion->prepare("INSERT INTO historial_discapacidades
            (Id_Historial, Id_discapacidad, fecha_registro, estatus)
            VALUES (?, ?, ?, 1)");
        foreach ($disc_array as $disc) {
            $id_disc = (int)($disc['id'] ?? 0);
            if ($id_disc <= 0) continue;
            $fecha = !empty($disc['fecha']) ? $disc['fecha'] : date('Y-m-d');
            $stmt->bind_param("iis", $id_historial, $id_disc, $fecha);
            if (!$stmt->execute()) {
                throw new Exception("INSERT discapacidad $id_disc: " . $stmt->error);
            }
        }
        $stmt->close();
    }

    /* ── 10) Representantes: DELETE + INSERT ── */
    $stmt = $conexion->prepare("DELETE FROM historial_representantes WHERE Id_paciente = ?");
    $stmt->bind_param("i", $id_paciente);
    if (!$stmt->execute()) {
        throw new Exception("DELETE representantes: " . $stmt->error);
    }
    $stmt->close();

    $rep_array = json_decode($representantes_json, true);
    if (is_array($rep_array) && count($rep_array) > 0) {
        $stmt = $conexion->prepare("INSERT INTO historial_representantes
            (Id_paciente, Id_representante, tipo_representante, parentesco, estatus)
            VALUES (?, ?, ?, ?, 1)");
        foreach ($rep_array as $rep) {
            $id_rep = (int)($rep['id'] ?? 0);
            if ($id_rep <= 0) continue;
            $tipo = ($rep['tipo'] ?? 'Legal') === 'Suplente' ? 'Suplente' : 'Legal';
            $parentesco = trim($rep['parentesco'] ?? '');
            $parentesco_val = $parentesco !== '' ? $parentesco : null;
            $stmt->bind_param("iiss", $id_paciente, $id_rep, $tipo, $parentesco_val);
            if (!$stmt->execute()) {
                throw new Exception("INSERT representante $id_rep: " . $stmt->error);
            }
        }
        $stmt->close();
    }

    $conexion->commit();
    $_SESSION['mensaje_user_exito'] = "Paciente actualizado correctamente.";
    header("location: ../../pages/php/pacientes_listado.php");
    exit();

} catch (Exception $e) {
    $conexion->rollback();
    error_log("Error al editar paciente: " . $e->getMessage());
    $_SESSION['mensaje_user_error'] = "⚠️ No se pudo actualizar el paciente: " . $e->getMessage();
    header("location: ../../pages/php/pacientes_listado.php");
    exit();
}

$conexion->close();
?>