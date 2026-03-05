<?php
session_start();

require_once "../conexion.php";

/* ===============================
   1. RECOLECCION DE DATOS
================================*/

// Datos Personales
$tipo_cedula = $_POST['tipo_cedula'] ?? '';
$cedula = $_POST['cedula'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$apellido = $_POST['apellido'] ?? '';
$fecha_nacimiento = $_POST['fecha_nacimiento_adulto'] ?? '';
$lugar_nacimiento = $_POST['municipio_nacimiento'] ?? '';
$genero = $_POST['genero'] ?? '';
$situacion_conyugal = $_POST['situacion_conyugal'] ?? '';
$etnia = $_POST['etnia'] ?? 'No';
$tipo_etnia = $_POST['tipo_etnia'] ?? '';
$analfabeta = $_POST['analfabeta'] ?? '';
$seguro_social = $_POST['seguro_social'] ?? '';
$email = $_POST['email'] ?? '';
$estado = 1;

// Ocupacion Y Estudios
$profesion = $_POST['profesion'] ?? '';
$ocupacion = $_POST['ocupacion'] ?? '';
$nivel_instruccion = $_POST['nivel_instruccion'] ?? '';
$mision = $_POST['mision'] ?? '';
$años_aprobados = $_POST['años_aprobados'] ?? 'Ninguno';

// Direccion
$avenida_calle = $_POST['avenida_calle'] ?? '';
$sector = $_POST['sector_adulto'] ?? '1';
$referencia_punto = $_POST['punto_referencia'] ?? '';
$tiempo_residencia = $_POST['tiempo_residencia'] ?? '';
$tiempo = $_POST['tiempo'] ?? '';

// Telefonos
$prefijo = $_POST['prefijo'] ?? '';
$telefono = $_POST['telefono'] ?? '';

// Historial y M:M
$fecha_actual = date("Y-m-d H:i:s");

$patologias_json = $_POST['patologias_data'] ?? '';
$alergias_json   = $_POST['alergias_data'] ?? '';

$grupo_sanguineo = $_POST['grupo_sanguineo'] ?? '';
$discapacidad = $_POST['discapacidad'] ?? 'No';
$tipo_discapacidad = $_POST['tipo_discapacidad'] ?? '';

$esta_en_cita = $_POST['esta_en_cita'] ?? 'No';

$rol = 3;
$referencia = $referencia_punto;


/* ===============================
   2. VALIDAR CEDULA
================================*/

$sql_verificar_cedula = "SELECT cedula FROM persona WHERE cedula = ? LIMIT 1";
$stmt_verificar = $conexion->prepare($sql_verificar_cedula);

if ($stmt_verificar === false) {
    $_SESSION['mensaje_estado'] = 'error';
    $_SESSION['mensaje_texto'] = "Error al preparar la verificación de cédula: " . $conexion->error;
    header('location: ../../pages/php/pacientes_listado.php');
    exit();
}

$stmt_verificar->bind_param("s", $cedula);
$stmt_verificar->execute();
$stmt_verificar->store_result();

if ($stmt_verificar->num_rows > 0) {
    $_SESSION['mensaje_estado'] = 'error';
    $_SESSION['mensaje_texto'] = "Error: La cédula $tipo_cedula-$cedula ya se encuentra registrada. ❌";
    header('location: ../../pages/php/pacientes_listado.php');
    $stmt_verificar->close();
    $conexion->close();
    exit();
}

$stmt_verificar->close();


/* ===============================
   3. TRANSACCION
================================*/

$conexion->begin_transaction();

try {

    /* ===============================
       4. INSERTS GENERALES
    ================================*/

    // PERSONA
    $sql_paciente = "INSERT INTO persona(nombre, apellido, tipo_cedula, cedula, fecha_nacimiento, genero, email, estatus) 
                     VALUES(?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt_paciente = $conexion->prepare($sql_paciente);
    $stmt_paciente->bind_param(
        "sssssssi",
        $nombre,
        $apellido,
        $tipo_cedula,
        $cedula,
        $fecha_nacimiento,
        $genero,
        $email,
        $estado
    );

    if (!$stmt_paciente->execute()) {
        throw new Exception("Error al insertar persona: " . $stmt_paciente->error);
    }

    $id_paciente = $conexion->insert_id;
    $stmt_paciente->close();


    // DETALLE PACIENTE
    $sql_detalle_paciente = "INSERT INTO detalle_paciente(
        situacion_conyugal, etnia, tipo_etnia, analfabeta, seguro_social,
        profesion, ocupacion, nivel_instruccion, mision, años_aprobados,
        discapacidad, tipo_discapacidad, id_persona)
        VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt_detalle = $conexion->prepare($sql_detalle_paciente);
    $stmt_detalle->bind_param(
        "sssssssssssii",
        $situacion_conyugal,
        $etnia,
        $tipo_etnia,
        $analfabeta,
        $seguro_social,
        $profesion,
        $ocupacion,
        $nivel_instruccion,
        $mision,
        $años_aprobados,
        $discapacidad,
        $tipo_discapacidad,
        $id_paciente
    );

    if (!$stmt_detalle->execute()) {
        throw new Exception("Error al insertar detalle paciente: " . $stmt_detalle->error);
    }

    $stmt_detalle->close();


    // DIRECCION
    $sql_direccion = "INSERT INTO direccion(
        tiempo_residencia, tiempo, avenida_calle, referencia,
        Id_persona, Id_sector, estatus)
        VALUES(?, ?, ?, ?, ?, ?, ?)";

    $stmt_direccion = $conexion->prepare($sql_direccion);
    $stmt_direccion->bind_param(
        "ssssisi",
        $tiempo_residencia,
        $tiempo,
        $avenida_calle,
        $referencia,
        $id_paciente,
        $sector,
        $estado
    );

    if (!$stmt_direccion->execute()) {
        throw new Exception("Error al insertar dirección: " . $stmt_direccion->error);
    }

    $stmt_direccion->close();


    // LUGAR NACIMIENTO
    $sql_lugar_nacimiento = "INSERT INTO lugar_nacimiento(Id_persona, Id_municipio)
                             VALUES(?, ?)";

    $stmt_lugar_nacimiento = $conexion->prepare($sql_lugar_nacimiento);
    $stmt_lugar_nacimiento->bind_param("ii", $id_paciente, $lugar_nacimiento);

    if (!$stmt_lugar_nacimiento->execute()) {
        throw new Exception("Error al insertar lugar nacimiento: " . $stmt_lugar_nacimiento->error);
    }

    $stmt_lugar_nacimiento->close();


    // TELEFONO
    $sql_telefono = "INSERT INTO telefonos_personas(
        Id_prefijo, telefono, Id_persona, estatus)
        VALUES(?, ?, ?, ?)";

    $stmt_telefono = $conexion->prepare($sql_telefono);
    $stmt_telefono->bind_param(
        "sisi",
        $prefijo,
        $telefono,
        $id_paciente,
        $estado
    );

    if (!$stmt_telefono->execute()) {
        throw new Exception("Error al insertar teléfono: " . $stmt_telefono->error);
    }

    $stmt_telefono->close();


    // HISTORIAL
    $sql_historia = "INSERT INTO historial_medico(
        fecha, Id_persona, grupo_sanguineo)
        VALUES(?, ?, ?)";

    $stmt_historia = $conexion->prepare($sql_historia);
    $stmt_historia->bind_param("sis", $fecha_actual, $id_paciente, $grupo_sanguineo);

    if (!$stmt_historia->execute()) {
        throw new Exception("Error al insertar historial: " . $stmt_historia->error);
    }

    $id_historial_medico = $conexion->insert_id;
    $stmt_historia->close();


    // ROL
    $sql_rol = "INSERT INTO detalle_persona_rol(
        Id_persona, Id_rol, estatus)
        VALUES(?, ?, ?)";

    $stmt_rol = $conexion->prepare($sql_rol);
    $stmt_rol->bind_param("iii", $id_paciente, $rol, $estado);

    if (!$stmt_rol->execute()) {
        throw new Exception("Error al insertar rol: " . $stmt_rol->error);
    }

    $stmt_rol->close();


    /* ===============================
       5. PATOLOGIAS CON FECHA
    ================================*/

    if (!empty($patologias_json)) {

        $patologias_array = json_decode($patologias_json, true);

        if (is_array($patologias_array)) {

            $sql_patologia_m2m = "
              INSERT INTO historial_patologias
              (Id_patologia, Id_historial, Id_persona, fecha_registro, estatus)
              VALUES (?, ?, ?, ?, ?)";

            $stmt_patologia_m2m = $conexion->prepare($sql_patologia_m2m);

            foreach ($patologias_array as $p) {

                $idPatologia = intval($p['id']);
                $fechaReg    = $p['fecha'];

                $stmt_patologia_m2m->bind_param(
                    "iiisi",
                    $idPatologia,
                    $id_historial_medico,
                    $id_paciente,
                    $fechaReg,
                    $estado
                );

                if (!$stmt_patologia_m2m->execute()) {
                    throw new Exception("Error patología ID $idPatologia");
                }
            }

            $stmt_patologia_m2m->close();
        }
    }


    /* ===============================
       6. ALERGIAS CON FECHA
    ================================*/

    if (!empty($alergias_json)) {

        $alergias_array = json_decode($alergias_json, true);

        if (is_array($alergias_array)) {

            $sql_alergia_m2m = "
              INSERT INTO historial_alergias
              (Id_alergia, Id_historial, Id_persona, fecha_registro, estatus)
              VALUES (?, ?, ?, ?, ?)";

            $stmt_alergia_m2m = $conexion->prepare($sql_alergia_m2m);

            foreach ($alergias_array as $a) {

                $idAlergia = intval($a['id']);
                $fechaReg = $a['fecha'];

                $stmt_alergia_m2m->bind_param(
                    "iiisi",
                    $idAlergia,
                    $id_historial_medico,
                    $id_paciente,
                    $fechaReg,
                    $estado
                );

                if (!$stmt_alergia_m2m->execute()) {
                    throw new Exception("Error alergia ID $idAlergia");
                }
            }

            $stmt_alergia_m2m->close();
        }
    }


    /* ===============================
       7. COMMIT + REDIRECT
    ================================*/

    if ($_SESSION['nombre_rol'] === 'Medico - Usuario' || $_SESSION['rol'] === '7') {

        $conexion->commit();
        header('location: ../../pages/php/consulta_agregar.php');
        exit();

    } else {

        $_SESSION['mensaje_user_exito'] =
            '✅ Éxito: El paciente ' . $nombre . ' ' . $apellido . ' fue agregado correctamente.';

        $conexion->commit();
        header('location: ../../pages/php/pacientes_listado.php');
        exit();
    }

} catch (Exception $e) {

    $conexion->rollback();

    error_log("Error al agregar paciente: " . $e->getMessage());

    $_SESSION['mensaje_user_error'] =
        '❌ Error de Registro: No se pudo registrar el paciente. Detalle: ' . $e->getMessage();

    header('location: ../../pages/php/pacientes_listado.php');
    exit();
}

$conexion->close();
?>

