<?php
session_start();
require_once "../conexion.php";

function redireccionar_error($mensaje, $conexion = null) {
    if ($conexion && $conexion->in_transaction) $conexion->rollback();
    $_SESSION['mensaje_user_error'] = $mensaje;
    header('location: ../../pages/php/pacientes_menores_listado.php');
    exit();
}

$estado = 1;
$rol_paciente = 3;
$fecha_actual = date("Y-m-d H:i:s");

$tipo_documento_menor = $_POST['tipo_cedula'] ?? '';
$cedula_menor         = $_POST['cedula'] ?? '';
$nombre_menor         = $_POST['nombre'] ?? '';
$apellido_menor       = $_POST['apellido'] ?? '';
$fecha_nacimiento     = $_POST['fecha_nacimiento'] ?? null;
$lugar_nacimiento     = !empty($_POST['municipio_nacimiento']) ? (int)$_POST['municipio_nacimiento'] : null;
$genero               = $_POST['genero'] ?? '';
$etnia                = $_POST['etnia'] ?? 'No';
$tipo_etnia           = $_POST['tipo_etnia'] ?? 'Ninguna';

$nivel_instruccion = $_POST['nivel_instruccion'] ?? null;
$mision            = $_POST['mision'] ?? null;
$años_aprobados    = !empty($_POST['años_aprobados']) ? (int)$_POST['años_aprobados'] : null;
$analfabeta        = $_POST['analfabeta'] ?? 'No';

$grupo_sanguineo = $_POST['grupo_sanguineo'] ?? '';

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

if (empty($fecha_nacimiento)) redireccionar_error("La fecha de nacimiento del menor es obligatoria.", $conexion);
if (empty($cedula_menor) || empty($tipo_documento_menor)) redireccionar_error("La cédula del menor es obligatoria.", $conexion);
if (empty($representantes_array)) redireccionar_error("Debe registrar al menos un representante legal.", $conexion);

$stmt_verificar = $conexion->prepare("SELECT cedula FROM persona WHERE cedula = ? AND tipo_cedula = ? LIMIT 1");
if ($stmt_verificar === false) redireccionar_error("Error al preparar verificación: " . $conexion->error, $conexion);
$stmt_verificar->bind_param("ss", $cedula_menor, $tipo_documento_menor);
$stmt_verificar->execute();
$stmt_verificar->store_result();

if ($stmt_verificar->num_rows > 0) {
    $stmt_verificar->close();
    redireccionar_error("⚠️ El documento $tipo_documento_menor-$cedula_menor ya está registrado.", null);
}
$stmt_verificar->close();

$conexion->begin_transaction();

try {

    /* ── 1) persona con password ── */
    $password = '';
    $sql_paciente = "INSERT INTO persona
        (nombre, apellido, tipo_cedula, cedula, fecha_nacimiento, genero, password, estatus)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_paciente = $conexion->prepare($sql_paciente);
    $stmt_paciente->bind_param(
        "sssssssi",
        $nombre_menor, $apellido_menor, $tipo_documento_menor,
        $cedula_menor, $fecha_nacimiento, $genero, $password, $estado
    );
    if (!$stmt_paciente->execute()) {
        throw new Exception("Error al insertar persona: " . $stmt_paciente->error);
    }
    $id_paciente = $conexion->insert_id;
    $stmt_paciente->close();

    /* ── 2) detalle_paciente_menor sin discapacidad/tipo_discapacidad ── */
    $sql_detalle = "INSERT INTO detalle_paciente_menor
        (etnia, tipo_etnia, analfabeta, nivel_instruccion, mision, años_aprobados, tipo_paciente, id_persona)
        VALUES (?, ?, ?, ?, ?, ?, 'Interno', ?)";
    $stmt_detalle = $conexion->prepare($sql_detalle);
    $stmt_detalle->bind_param(
        "sssssii",
        $etnia, $tipo_etnia, $analfabeta,
        $nivel_instruccion, $mision, $años_aprobados, $id_paciente
    );
    if (!$stmt_detalle->execute()) {
        throw new Exception("Error al insertar detalle menor: " . $stmt_detalle->error);
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
        $id_paciente, $sector_residencia, $estado
    );
    if (!$stmt_direccion->execute()) {
        throw new Exception("Error al insertar dirección: " . $stmt_direccion->error);
    }
    $stmt_direccion->close();

    /* ── 4) lugar de nacimiento ── */
    if ($lugar_nacimiento !== null) {
        $sql_lugar = "INSERT INTO lugar_nacimiento (Id_persona, Id_municipio) VALUES (?, ?)";
        $stmt_lugar = $conexion->prepare($sql_lugar);
        $stmt_lugar->bind_param("ii", $id_paciente, $lugar_nacimiento);
        if (!$stmt_lugar->execute()) {
            throw new Exception("Error al insertar lugar nacimiento: " . $stmt_lugar->error);
        }
        $stmt_lugar->close();
    }

    /* ── 5) historial médico ── */
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

    /* ── 6) rol paciente ── */
    $sql_rol = "INSERT INTO detalle_persona_rol (Id_persona, Id_rol, estatus) VALUES (?, ?, ?)";
    $stmt_rol = $conexion->prepare($sql_rol);
    $stmt_rol->bind_param("iii", $id_paciente, $rol_paciente, $estado);
    if (!$stmt_rol->execute()) {
        throw new Exception("Error al insertar rol: " . $stmt_rol->error);
    }
    $stmt_rol->close();

    /* ── 7) patologías (SIN Id_persona) ── */
    $patologias_array = json_decode($patologias_json, true);
    if (is_array($patologias_array) && !empty($patologias_array)) {
        $sql_pat = "INSERT INTO historial_patologias
                    (Id_patologia, Id_Historial, fecha_registro, estatus)
                    VALUES (?, ?, ?, 1)";
        $stmt_pat = $conexion->prepare($sql_pat);
        foreach ($patologias_array as $patologia) {
            $id_pat = (int)($patologia['id'] ?? 0);
            if ($id_pat <= 0) continue;
            $fecha_reg = !empty($patologia['fecha']) ? $patologia['fecha'] : date('Y-m-d');
            $stmt_pat->bind_param("iis", $id_pat, $id_historial, $fecha_reg);
            if (!$stmt_pat->execute()) {
                throw new Exception("Error patología $id_pat: " . $stmt_pat->error);
            }
        }
        $stmt_pat->close();
    }

    /* ── 8) alergias (SIN Id_persona) ── */
    $alergias_array = json_decode($alergias_json, true);
    if (is_array($alergias_array) && !empty($alergias_array)) {
        $sql_ale = "INSERT INTO historial_alergias
                    (Id_alergia, Id_Historial, fecha_registro, estatus)
                    VALUES (?, ?, ?, 1)";
        $stmt_ale = $conexion->prepare($sql_ale);
        foreach ($alergias_array as $alergia) {
            $id_ale = (int)($alergia['id'] ?? 0);
            if ($id_ale <= 0) continue;
            $fecha_reg = !empty($alergia['fecha']) ? $alergia['fecha'] : date('Y-m-d');
            $stmt_ale->bind_param("iis", $id_ale, $id_historial, $fecha_reg);
            if (!$stmt_ale->execute()) {
                throw new Exception("Error alergia $id_ale: " . $stmt_ale->error);
            }
        }
        $stmt_ale->close();
    }

    /* ── 9) discapacidades (Id_Historial, no Id_persona) ── */
    if (!empty($discapacidades_array)) {
        $sql_disc = "INSERT INTO historial_discapacidades
                     (Id_Historial, Id_discapacidad, fecha_registro, estatus)
                     VALUES (?, ?, ?, 1)";
        $stmt_disc = $conexion->prepare($sql_disc);
        foreach ($discapacidades_array as $disc) {
            $id_disc = (int)($disc['id'] ?? 0);
            if ($id_disc <= 0) continue;
            $fecha_disc = !empty($disc['fecha']) ? $disc['fecha'] : date('Y-m-d');
            $stmt_disc->bind_param("iis", $id_historial, $id_disc, $fecha_disc);
            if (!$stmt_disc->execute()) {
                throw new Exception("Error discapacidad $id_disc: " . $stmt_disc->error);
            }
        }
        $stmt_disc->close();
    }

    /* ── 10) representantes ── */
    if (!empty($representantes_array)) {
        $sql_rep = "INSERT INTO historial_representantes
                    (Id_paciente, Id_representante, tipo_representante, parentesco, estatus)
                    VALUES (?, ?, ?, ?, 1)";
        $stmt_rep = $conexion->prepare($sql_rep);
        foreach ($representantes_array as $rep) {
            $rep_id = (int)($rep['id'] ?? 0);
            if ($rep_id <= 0) continue;
            $tipo_rep = ($rep['tipo'] ?? 'Legal') === 'Suplente' ? 'Suplente' : 'Legal';
            $parentesco = trim($rep['parentesco'] ?? '');
            $parentesco_val = $parentesco !== '' ? $parentesco : null;
            $stmt_rep->bind_param("iiss", $id_paciente, $rep_id, $tipo_rep, $parentesco_val);
            if (!$stmt_rep->execute()) {
                throw new Exception("Error representante $rep_id: " . $stmt_rep->error);
            }
        }
        $stmt_rep->close();
    }

    $conexion->commit();

    $redireccion = $_POST['redireccion'] ?? '';
    $esMedico = (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Medico - Usuario')
             || (isset($_SESSION['rol']) && $_SESSION['rol'] === '7');
    $esRecepcionista = (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Recepcionista');

    if ($esMedico && $redireccion === 'consulta') {
        header('location: ../../pages/php/consulta_agregar.php');
        exit();
    } else if (($esMedico || $esRecepcionista) && $redireccion === 'cita') {
        header('location: ../../pages/php/citas_medicas_agregar.php');
        exit();
    }

    $_SESSION['mensaje_user_exito'] = "✅ Éxito: El paciente $nombre_menor $apellido_menor fue agregado correctamente.";
    header('location: ../../pages/php/pacientes_menores_listado.php');
    exit();

} catch (Exception $e) {
    $conexion->rollback();
    error_log("⚠️ Error al agregar paciente menor: " . $e->getMessage());
    $_SESSION['mensaje_user_error'] = "⚠️ Error de Registro: " . $e->getMessage();
    header('location: ../../pages/php/pacientes_menores_listado.php');
    exit();
}

$conexion->close();
?>