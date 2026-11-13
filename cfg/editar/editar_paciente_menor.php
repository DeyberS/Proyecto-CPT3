<?php
session_start();
require_once "../conexion.php";

function redireccionar_error($mensaje) {
    $_SESSION['mensaje_user_error'] = $mensaje;
    header('location: ../../pages/php/pacientes_menores_listado.php'); 
    exit();
}

$id_paciente_a_actualizar = (int)($_POST['Id'] ?? 0);
if ($id_paciente_a_actualizar <= 0) {
    redireccionar_error("ID de paciente inválido.");
}

$estado = 1;
$fecha_actual = date("Y-m-d H:i:s");

$tipo_documento_menor = $_POST['tipo_cedula'] ?? '';
$cedula_menor         = $_POST['cedula'] ?? '';
$nombre_menor         = $_POST['nombre'] ?? '';
$apellido_menor       = $_POST['apellido'] ?? '';
$fecha_nacimiento     = $_POST['fecha_nacimiento'] ?? '';
$lugar_nacimiento     = !empty($_POST['municipio_nacimiento']) ? (int)$_POST['municipio_nacimiento'] : null;
$genero               = $_POST['genero'] ?? '';
$etnia                = $_POST['etnia'] ?? 'No';
$tipo_etnia           = $_POST['tipo_etnia'] ?? 'Ninguna';

$nivel_instruccion = $_POST['nivel_instruccion'] ?? null;
$mision            = $_POST['mision'] ?? null;
$años_aprobados    = !empty($_POST['años_aprobados']) ? (int)$_POST['años_aprobados'] : null;
$analfabeta        = $_POST['analfabeta'] ?? 'No';

$grupo_sanguineo = $_POST['grupo_sanguineo'] ?? 'N/A';
if ($grupo_sanguineo === '') $grupo_sanguineo = 'N/A';

$sector_residencia = !empty($_POST['sector']) ? (int)$_POST['sector'] : null;
$avenida_calle     = $_POST['avenida_calle'] ?? 'N/A';
$referencia        = $_POST['referencia'] ?? 'N/A';
$tiempo_residencia = $_POST['tiempo_residencia'] ?? 'N/A';
$tiempo            = $_POST['tiempo'] ?? null;

$patologias_json      = $_POST['patologias_data'] ?? '[]';
$alergias_json        = $_POST['alergias_data'] ?? '[]';
$discapacidades_json  = $_POST['discapacidades_data'] ?? '[]';
$representantes_json  = $_POST['representantes_data'] ?? '[]';

$representantes_array = json_decode($representantes_json, true);
if (!is_array($representantes_array)) $representantes_array = [];

$discapacidades_array = json_decode($discapacidades_json, true);
if (!is_array($discapacidades_array)) $discapacidades_array = [];

$conexion->begin_transaction();

try {

    /* ── 1) persona ── */
    $stmt = $conexion->prepare("UPDATE persona SET
        nombre = ?, apellido = ?, tipo_cedula = ?, cedula = ?,
        fecha_nacimiento = ?, genero = ?
        WHERE id = ?");
    $stmt->bind_param("ssssssi",
        $nombre_menor, $apellido_menor, $tipo_documento_menor,
        $cedula_menor, $fecha_nacimiento, $genero, $id_paciente_a_actualizar);
    if (!$stmt->execute()) {
        throw new Exception("persona: " . $stmt->error);
    }
    $stmt->close();

    /* ── 2) detalle_paciente_menor (SIN discapacidad/tipo_discapacidad) ── */
    $stmt = $conexion->prepare("INSERT INTO detalle_paciente_menor
        (etnia, tipo_etnia, analfabeta, nivel_instruccion, mision, años_aprobados, tipo_paciente, id_persona)
        VALUES (?, ?, ?, ?, ?, ?, 'Interno', ?)
        ON DUPLICATE KEY UPDATE
            etnia = VALUES(etnia),
            tipo_etnia = VALUES(tipo_etnia),
            analfabeta = VALUES(analfabeta),
            nivel_instruccion = VALUES(nivel_instruccion),
            mision = VALUES(mision),
            años_aprobados = VALUES(años_aprobados)");
    $stmt->bind_param("sssssii",
        $etnia, $tipo_etnia, $analfabeta, $nivel_instruccion,
        $mision, $años_aprobados, $id_paciente_a_actualizar);
    if (!$stmt->execute()) {
        throw new Exception("detalle_paciente_menor: " . $stmt->error);
    }
    $stmt->close();

    /* ── 3) dirección (UPSERT) ── */
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
        $id_paciente_a_actualizar, $sector_residencia, $estado);
    if (!$stmt->execute()) {
        throw new Exception("direccion: " . $stmt->error);
    }
    $stmt->close();

    /* ── 4) lugar de nacimiento ── */
    if ($lugar_nacimiento !== null) {
        $stmt = $conexion->prepare("INSERT INTO lugar_nacimiento (Id_persona, Id_municipio)
            VALUES (?, ?) ON DUPLICATE KEY UPDATE Id_municipio = VALUES(Id_municipio)");
        $stmt->bind_param("ii", $id_paciente_a_actualizar, $lugar_nacimiento);
        if (!$stmt->execute()) {
            throw new Exception("lugar_nacimiento: " . $stmt->error);
        }
        $stmt->close();
    }

    /* ── 5) historial_medico (obtener/crear/actualizar) ── */
    $stmt = $conexion->prepare("SELECT id_historial FROM historial_medico WHERE Id_persona = ? LIMIT 1");
    $stmt->bind_param("i", $id_paciente_a_actualizar);
    $stmt->execute();
    $row_h = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($row_h) {
        $id_historial_medico = (int)$row_h['id_historial'];
        $stmt = $conexion->prepare("UPDATE historial_medico SET grupo_sanguineo = ? WHERE id_historial = ?");
        $stmt->bind_param("si", $grupo_sanguineo, $id_historial_medico);
    } else {
        $stmt = $conexion->prepare("INSERT INTO historial_medico (fecha, Id_persona, grupo_sanguineo, estatus)
            VALUES (?, ?, ?, 1)");
        $stmt->bind_param("sis", $fecha_actual, $id_paciente_a_actualizar, $grupo_sanguineo);
    }
    if (!$stmt->execute()) {
        throw new Exception("historial_medico: " . $stmt->error);
    }
    if (!$row_h) {
        $id_historial_medico = $conexion->insert_id;
    }
    $stmt->close();

    /* ── 6) Patologías: DELETE + INSERT con Id_Historial ── */
    $stmt = $conexion->prepare("DELETE FROM historial_patologias WHERE Id_Historial = ?");
    $stmt->bind_param("i", $id_historial_medico);
    if (!$stmt->execute()) {
        throw new Exception("DELETE patologías: " . $stmt->error);
    }
    $stmt->close();

    $patologias_array = json_decode($patologias_json, true);
    if (is_array($patologias_array) && !empty($patologias_array)) {
        $stmt = $conexion->prepare("INSERT INTO historial_patologias
            (Id_patologia, Id_Historial, fecha_registro, estatus)
            VALUES (?, ?, ?, 1)");
        foreach ($patologias_array as $pat) {
            $id_pat = (int)($pat['id'] ?? 0);
            if ($id_pat <= 0) continue;
            $fecha = !empty($pat['fecha']) ? $pat['fecha'] : date('Y-m-d');
            $stmt->bind_param("iis", $id_pat, $id_historial_medico, $fecha);
            if (!$stmt->execute()) {
                throw new Exception("INSERT patología $id_pat: " . $stmt->error);
            }
        }
        $stmt->close();
    }

    /* ── 7) Alergias: DELETE + INSERT con Id_Historial ── */
    $stmt = $conexion->prepare("DELETE FROM historial_alergias WHERE Id_Historial = ?");
    $stmt->bind_param("i", $id_historial_medico);
    if (!$stmt->execute()) {
        throw new Exception("DELETE alergias: " . $stmt->error);
    }
    $stmt->close();

    $alergias_array = json_decode($alergias_json, true);
    if (is_array($alergias_array) && !empty($alergias_array)) {
        $stmt = $conexion->prepare("INSERT INTO historial_alergias
            (Id_alergia, Id_Historial, fecha_registro, estatus)
            VALUES (?, ?, ?, 1)");
        foreach ($alergias_array as $ale) {
            $id_ale = (int)($ale['id'] ?? 0);
            if ($id_ale <= 0) continue;
            $fecha = !empty($ale['fecha']) ? $ale['fecha'] : date('Y-m-d');
            $stmt->bind_param("iis", $id_ale, $id_historial_medico, $fecha);
            if (!$stmt->execute()) {
                throw new Exception("INSERT alergia $id_ale: " . $stmt->error);
            }
        }
        $stmt->close();
    }

    /* ── 8) Discapacidades: DELETE + INSERT con Id_Historial ── */
    $stmt = $conexion->prepare("DELETE FROM historial_discapacidades WHERE Id_Historial = ?");
    $stmt->bind_param("i", $id_historial_medico);
    if (!$stmt->execute()) {
        throw new Exception("DELETE discapacidades: " . $stmt->error);
    }
    $stmt->close();

    if (!empty($discapacidades_array)) {
        $stmt = $conexion->prepare("INSERT INTO historial_discapacidades
            (Id_Historial, Id_discapacidad, fecha_registro, estatus)
            VALUES (?, ?, ?, 1)");
        foreach ($discapacidades_array as $disc) {
            $id_disc = (int)($disc['id'] ?? 0);
            if ($id_disc <= 0) continue;
            $fecha = !empty($disc['fecha']) ? $disc['fecha'] : date('Y-m-d');
            $stmt->bind_param("iis", $id_historial_medico, $id_disc, $fecha);
            if (!$stmt->execute()) {
                throw new Exception("INSERT discapacidad $id_disc: " . $stmt->error);
            }
        }
        $stmt->close();
    }

    /* ── 9) Representantes: DELETE + INSERT ── */
    $stmt = $conexion->prepare("DELETE FROM historial_representantes WHERE Id_paciente = ?");
    $stmt->bind_param("i", $id_paciente_a_actualizar);
    if (!$stmt->execute()) {
        throw new Exception("DELETE representantes: " . $stmt->error);
    }
    $stmt->close();

    if (!empty($representantes_array)) {
        $stmt = $conexion->prepare("INSERT INTO historial_representantes
            (Id_paciente, Id_representante, tipo_representante, parentesco, estatus)
            VALUES (?, ?, ?, ?, 1)");
        foreach ($representantes_array as $rep) {
            $rep_id = (int)($rep['id'] ?? 0);
            if ($rep_id <= 0) continue;
            $tipo_rep = ($rep['tipo'] ?? 'Legal') === 'Suplente' ? 'Suplente' : 'Legal';
            $parentesco = trim($rep['parentesco'] ?? '');
            $parentesco_val = $parentesco !== '' ? $parentesco : null;
            $stmt->bind_param("iiss", $id_paciente_a_actualizar, $rep_id, $tipo_rep, $parentesco_val);
            if (!$stmt->execute()) {
                throw new Exception("INSERT representante $rep_id: " . $stmt->error);
            }
        }
        $stmt->close();
    }

    $conexion->commit();

    $_SESSION['mensaje_user_exito'] = "✅ Paciente $nombre_menor $apellido_menor actualizado.";
    header('location: ../../pages/php/pacientes_menores_listado.php');
    exit();

} catch (Exception $e) {
    $conexion->rollback();
    error_log("Error al editar paciente menor: " . $e->getMessage());
    $_SESSION['mensaje_user_error'] = "⚠️ No se pudo actualizar el menor: " . $e->getMessage();
    header('location: ../../pages/php/pacientes_menores_listado.php');
    exit();
}

$conexion->close();
?>