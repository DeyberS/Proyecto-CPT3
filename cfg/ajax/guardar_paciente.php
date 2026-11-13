<?php
header('Content-Type: application/json');
session_start();
require_once "../conexion.php";

$origen        = $_POST['origen'] ?? 'Externo';
$tipo_paciente = ($origen === 'Interno') ? 'Interno' : 'Externo';
$es_interno    = isset($_POST['ext_es_interno']) ? (int)$_POST['ext_es_interno'] : 0;

/* =========================================================
   DATOS PERSONALES
   ========================================================= */
$tipo_cedula        = trim($_POST['ext_tipo_cedula']  ?? 'V');
$cedula             = trim($_POST['ext_cedula']       ?? '');
$nombre             = trim($_POST['ext_nombre']       ?? '');
$apellido           = trim($_POST['ext_apellido']     ?? '');
$fecha_nacimiento   = trim($_POST['ext_fecha_nacimiento'] ?? '');
$genero             = trim($_POST['ext_genero']       ?? '');
$situacion_conyugal = trim($_POST['ext_situacion']    ?? '');
$email              = trim($_POST['ext_email']        ?? '');

/* Nacimiento (solo se envían cuando es interno) */
$municipio_nacimiento = trim($_POST['ext_municipio_nacimiento'] ?? '');

/* Etnia / sociales */
$etnia         = trim($_POST['ext_etnia']      ?? 'No');
$tipo_etnia    = trim($_POST['ext_tipo_etnia'] ?? '');
$analfabeta    = trim($_POST['ext_analfabeta'] ?? '');
$seguro_social = trim($_POST['ext_seguro']     ?? '');

/* Teléfono */
$prefijo  = trim($_POST['ext_prefijo']  ?? '');
$telefono = trim($_POST['ext_telefono'] ?? '');

/* Ocupación (solo interno) */
$nivel_instruccion = trim($_POST['ext_nivel_instruccion'] ?? '');
$profesion         = trim($_POST['ext_profesion']         ?? '');
$ocupacion         = trim($_POST['ext_ocupacion']         ?? '');
$mision            = trim($_POST['ext_mision']            ?? '');
$anos_aprobados    = trim($_POST['ext_anos_aprobados']    ?? '');

/* Dirección (solo interno) */
$sector_dir        = trim($_POST['ext_sector']            ?? '');
$avenida_calle     = trim($_POST['ext_avenida_calle']     ?? '');
$referencia        = trim($_POST['ext_referencia']        ?? '');
$tiempo_residencia = trim($_POST['ext_tiempo_residencia'] ?? '');
$tiempo            = trim($_POST['ext_tiempo']            ?? 'dia/s');

/* Salud */
$grupo_sanguineo = trim($_POST['ext_grupo_sanguineo'] ?? '');

/* JSON arrays */
$patologias     = json_decode($_POST['ext_patologias_data']     ?? '[]', true);
$alergias       = json_decode($_POST['ext_alergias_data']       ?? '[]', true);
$discapacidades = json_decode($_POST['ext_discapacidades_data'] ?? '[]', true);
$representantes = json_decode($_POST['ext_representantes_data'] ?? '[]', true);

if (!is_array($patologias))     $patologias     = [];
if (!is_array($alergias))       $alergias       = [];
if (!is_array($discapacidades)) $discapacidades = [];
if (!is_array($representantes)) $representantes = [];

/* =========================================================
   VALIDACIONES
   ========================================================= */
$faltantes = [];
if ($cedula === '')           $faltantes[] = 'Cédula';
if ($nombre === '')           $faltantes[] = 'Nombre';
if ($fecha_nacimiento === '') $faltantes[] = 'Fecha de nacimiento';
if ($genero === '')           $faltantes[] = 'Sexo';
if ($analfabeta === '')       $faltantes[] = 'Analfabeta';
if ($telefono === '')         $faltantes[] = 'Teléfono';
if ($email === '')            $faltantes[] = 'Correo';
if ($grupo_sanguineo === '')  $faltantes[] = 'Grupo sanguíneo';

if (!empty($faltantes)) {
    echo json_encode([
        'success' => false,
        'error'   => 'Faltan campos obligatorios: ' . implode(', ', $faltantes)
    ]);
    exit;
}

/* Validar cédula V / E */
$numCed = intval($cedula);
if ($tipo_cedula === 'V' && $numCed > 80000000) {
    echo json_encode(['success' => false, 'error' => 'Cédula V- no puede ser mayor a 80.000.000']);
    exit;
}
if ($tipo_cedula === 'E' && $numCed < 80000000) {
    echo json_encode(['success' => false, 'error' => 'Cédula E- no puede ser menor a 80.000.000']);
    exit;
}

/* Validar grupo sanguíneo */
$grupos_validos = ['A+','A-','B+','B-','AB+','AB-','O+','O-'];
if (!in_array($grupo_sanguineo, $grupos_validos, true)) {
    echo json_encode(['success' => false, 'error' => 'Grupo sanguíneo inválido']);
    exit;
}

/* Calcular edad */
try {
    $fechaNacObj = new DateTime($fecha_nacimiento);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Fecha de nacimiento inválida']);
    exit;
}
$hoy = new DateTime();
$hoy->setTime(0, 0, 0);
$fechaNacObj->setTime(0, 0, 0);
$edad = $hoy->diff($fechaNacObj)->y;

if ($edad < 18) {
    echo json_encode(['success' => false, 'error' => 'El paciente debe ser mayor de 18 años']);
    exit;
}

/* Verificar cédula duplicada */
$stmt_check = $conexion->prepare("SELECT id FROM persona WHERE cedula = ? AND tipo_cedula = ? LIMIT 1");
$stmt_check->bind_param("ss", $cedula, $tipo_cedula);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    echo json_encode([
        'success' => false,
        'error'   => 'La cédula ' . $tipo_cedula . '-' . $cedula . ' ya se encuentra registrada en el sistema.'
    ]);
    $stmt_check->close();
    exit;
}
$stmt_check->close();

/* =========================================================
   TRUNCAR CAMPOS SEGÚN LÍMITES DE LA BD
   ========================================================= */
$nombre             = mb_substr($nombre, 0, 100);
$apellido           = mb_substr($apellido, 0, 100);
$email              = mb_substr($email, 0, 100);
$avenida_calle      = mb_substr($avenida_calle, 0, 25);   // varchar(25)
$referencia         = mb_substr($referencia, 0, 50);      // varchar(50)
$tiempo_residencia  = mb_substr($tiempo_residencia, 0, 35);// varchar(35)

/* Ajustar tipo_etnia: si no es étnico, no aplica */
if ($etnia !== 'Si') {
    $tipo_etnia = '';
}

/* Flags derivados de los arrays */
$tiene_discapacidad  = !empty($discapacidades) ? 'Si' : 'No';
$tiene_representante = !empty($representantes) ? 'Si' : 'No';

/* Variables globales */
$fecha_actual    = date("Y-m-d H:i:s");
$estatus_int     = 1;   // para columnas int(1)
$estatus_str     = '1'; // para columnas enum('1','2')
$rol_paciente    = 3;   // rol Paciente
$password_vacio  = '';

/* =========================================================
   TRANSACCIÓN
   ========================================================= */
$conexion->begin_transaction();

try {

    /* ---------------------------------------------------------
       1) persona
       --------------------------------------------------------- */
    $email_db    = ($email !== '')    ? $email    : null;
    $apellido_db = ($apellido !== '') ? $apellido : null;

    $sql_persona = "INSERT INTO persona 
                    (nombre, apellido, tipo_cedula, cedula, fecha_nacimiento, genero, email, password, estatus) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_p = $conexion->prepare($sql_persona);
    if (!$stmt_p) throw new Exception("Prepare persona: " . $conexion->error);

    $stmt_p->bind_param(
        "ssssssssi",
        $nombre, $apellido_db, $tipo_cedula, $cedula, $fecha_nacimiento,
        $genero, $email_db, $password_vacio, $estatus_int
    );

    if (!$stmt_p->execute()) throw new Exception("Error Persona: " . $stmt_p->error);
    $id_paciente = $conexion->insert_id;
    $stmt_p->close();

    /* ---------------------------------------------------------
       2) detalle_paciente
       Solo guarda ocupación/estudios cuando es INTERNO
       --------------------------------------------------------- */
    if ($es_interno) {
        $sit_conyugal_db = ($situacion_conyugal !== '') ? $situacion_conyugal : null;
        $profesion_db    = ($profesion !== '')          ? $profesion         : null;
        $ocupacion_db    = ($ocupacion !== '')          ? $ocupacion         : null;
        $nivel_inst_db   = ($nivel_instruccion !== '')  ? $nivel_instruccion : null;
        $mision_db       = ($mision !== '')             ? $mision            : null;
        $anos_db         = ($anos_aprobados !== '' && is_numeric($anos_aprobados))
                            ? intval($anos_aprobados) : 0;
    } else {
        $sit_conyugal_db = ($situacion_conyugal !== '') ? $situacion_conyugal : null;
        $profesion_db    = null;
        $ocupacion_db    = null;
        $nivel_inst_db   = null;
        $mision_db       = null;
        $anos_db         = 0;
    }

    $tipo_etnia_db  = ($tipo_etnia !== '')  ? $tipo_etnia  : null;
    $etnia_db       = ($etnia !== '')       ? $etnia       : 'No';
    $analfabeta_db  = ($analfabeta !== '')  ? $analfabeta  : 'No';
    $seguro_db      = ($seguro_social !== '') ? $seguro_social : 'No';

    $sql_detalle = "INSERT INTO detalle_paciente 
                    (situacion_conyugal, etnia, tipo_etnia, analfabeta, seguro_social,
                     profesion, ocupacion, nivel_instruccion, mision, `años_aprobados`,
                     discapacidad, representante, tipo_paciente, id_persona) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_d = $conexion->prepare($sql_detalle);
    if (!$stmt_d) throw new Exception("Prepare detalle: " . $conexion->error);

    $stmt_d->bind_param(
        "sssssssssissis",
        $sit_conyugal_db, $etnia_db, $tipo_etnia_db, $analfabeta_db, $seguro_db,
        $profesion_db, $ocupacion_db, $nivel_inst_db, $mision_db, $anos_db,
        $tiene_discapacidad, $tiene_representante, $tipo_paciente, $id_paciente
    );

    if (!$stmt_d->execute()) throw new Exception("Error Detalle: " . $stmt_d->error);
    $stmt_d->close();

    /* ---------------------------------------------------------
       3) lugar_nacimiento (solo interno)
       --------------------------------------------------------- */
    if ($es_interno && $municipio_nacimiento !== '') {
        $mun_nac_int = intval($municipio_nacimiento);
        if ($mun_nac_int > 0) {
            $sql_lug  = "INSERT INTO lugar_nacimiento (Id_persona, Id_municipio) VALUES (?, ?)";
            $stmt_lug = $conexion->prepare($sql_lug);
            if ($stmt_lug) {
                $stmt_lug->bind_param("ii", $id_paciente, $mun_nac_int);
                $stmt_lug->execute();
                $stmt_lug->close();
            }
        }
    }

    /* ---------------------------------------------------------
       4) direccion
       Interno → datos reales | Externo → dummy
       --------------------------------------------------------- */
    if ($es_interno) {
        $sector_int = ($sector_dir !== '' && is_numeric($sector_dir)) ? intval($sector_dir) : null;

        $sql_dir = "INSERT INTO direccion 
                    (tiempo_residencia, tiempo, avenida_calle, referencia, Id_persona, Id_sector, estatus) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_dir = $conexion->prepare($sql_dir);
        if (!$stmt_dir) throw new Exception("Prepare direccion: " . $conexion->error);

        $stmt_dir->bind_param(
            "ssssisi",
            $tiempo_residencia, $tiempo, $avenida_calle, $referencia,
            $id_paciente, $sector_int, $estatus_str
        );

        if (!$stmt_dir->execute()) throw new Exception("Error Direccion: " . $stmt_dir->error);
        $stmt_dir->close();

    } else {
        $vacio      = "";
        $ref_dummy  = "Registrado Vía Rápida (" . $origen . ")";
        $tiempo_d   = "dia/s";

        $sql_dir = "INSERT INTO direccion 
                    (tiempo_residencia, tiempo, avenida_calle, referencia, Id_persona, estatus) 
                    VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_dir = $conexion->prepare($sql_dir);
        if ($stmt_dir) {
            $stmt_dir->bind_param(
                "ssssis",
                $vacio, $tiempo_d, $vacio, $ref_dummy, $id_paciente, $estatus_str
            );
            $stmt_dir->execute();
            $stmt_dir->close();
        }
    }

    /* ---------------------------------------------------------
       5) telefonos_personas
       --------------------------------------------------------- */
    $prefijo_int = intval($prefijo);
    if ($prefijo_int > 0) {
        $sql_tel  = "INSERT INTO telefonos_personas (Id_prefijo, telefono, Id_persona, estatus) 
                     VALUES (?, ?, ?, ?)";
        $stmt_tel = $conexion->prepare($sql_tel);
        if (!$stmt_tel) throw new Exception("Prepare telefono: " . $conexion->error);

        $stmt_tel->bind_param("isis", $prefijo_int, $telefono, $id_paciente, $estatus_str);

        if (!$stmt_tel->execute()) throw new Exception("Error Telefono: " . $stmt_tel->error);
        $stmt_tel->close();
    }

    /* ---------------------------------------------------------
       6) historial_medico (necesitamos el id_historial)
       --------------------------------------------------------- */
    $sql_hist  = "INSERT INTO historial_medico (fecha, Id_persona, grupo_sanguineo, estatus) 
                  VALUES (?, ?, ?, ?)";
    $stmt_hist = $conexion->prepare($sql_hist);
    if (!$stmt_hist) throw new Exception("Prepare historial: " . $conexion->error);

    $stmt_hist->bind_param("sisi", $fecha_actual, $id_paciente, $grupo_sanguineo, $estatus_int);

    if (!$stmt_hist->execute()) throw new Exception("Error Historial: " . $stmt_hist->error);
    $id_historial = $conexion->insert_id;
    $stmt_hist->close();

    /* ---------------------------------------------------------
       7) detalle_persona_rol (rol Paciente = 3)
       --------------------------------------------------------- */
    $sql_rol  = "INSERT INTO detalle_persona_rol (Id_persona, Id_rol, estatus) VALUES (?, ?, ?)";
    $stmt_rol = $conexion->prepare($sql_rol);
    if (!$stmt_rol) throw new Exception("Prepare rol: " . $conexion->error);

    $stmt_rol->bind_param("iis", $id_paciente, $rol_paciente, $estatus_str);

    if (!$stmt_rol->execute()) throw new Exception("Error Rol: " . $stmt_rol->error);
    $stmt_rol->close();

    /* ---------------------------------------------------------
       8) historial_patologias
       --------------------------------------------------------- */
    if (!empty($patologias)) {
        $sql_pat  = "INSERT INTO historial_patologias 
                     (Id_persona, Id_patologia, Id_Historial, fecha_registro, estatus) 
                     VALUES (?, ?, ?, ?, ?)";
        $stmt_pat = $conexion->prepare($sql_pat);
        if (!$stmt_pat) throw new Exception("Prepare patologia: " . $conexion->error);

        foreach ($patologias as $pat) {
            $id_pat = isset($pat['id']) ? intval($pat['id']) : 0;
            if ($id_pat <= 0) continue;

            $fecha_pat = (!empty($pat['fecha'])) ? $pat['fecha'] : date('Y-m-d');

            $stmt_pat->bind_param("iiiss", $id_paciente, $id_pat, $id_historial, $fecha_pat, $estatus_str);
            $stmt_pat->execute();
        }
        $stmt_pat->close();
    }

    /* ---------------------------------------------------------
       9) historial_alergias
       --------------------------------------------------------- */
    if (!empty($alergias)) {
        $sql_ale  = "INSERT INTO historial_alergias 
                     (Id_persona, Id_alergia, Id_Historial, fecha_registro, estatus) 
                     VALUES (?, ?, ?, ?, ?)";
        $stmt_ale = $conexion->prepare($sql_ale);
        if (!$stmt_ale) throw new Exception("Prepare alergia: " . $conexion->error);

        foreach ($alergias as $ale) {
            $id_ale = isset($ale['id']) ? intval($ale['id']) : 0;
            if ($id_ale <= 0) continue;

            $fecha_ale = (!empty($ale['fecha'])) ? $ale['fecha'] : date('Y-m-d');

            $stmt_ale->bind_param("iiiss", $id_paciente, $id_ale, $id_historial, $fecha_ale, $estatus_str);
            $stmt_ale->execute();
        }
        $stmt_ale->close();
    }

    /* ---------------------------------------------------------
       10) historial_discapacidades
       (fecha_registro permite NULL)
       --------------------------------------------------------- */
    if (!empty($discapacidades)) {
        $sql_disc  = "INSERT INTO historial_discapacidades 
                      (Id_persona, Id_discapacidad, fecha_registro, estatus) 
                      VALUES (?, ?, ?, ?)";
        $stmt_disc = $conexion->prepare($sql_disc);
        if (!$stmt_disc) throw new Exception("Prepare discapacidad: " . $conexion->error);

        foreach ($discapacidades as $disc) {
            $id_disc = isset($disc['id']) ? intval($disc['id']) : 0;
            if ($id_disc <= 0) continue;

            $fecha_disc = (!empty($disc['fecha'])) ? $disc['fecha'] : null;

            $stmt_disc->bind_param("iiss", $id_paciente, $id_disc, $fecha_disc, $estatus_str);
            $stmt_disc->execute();
        }
        $stmt_disc->close();
    }

    /* ---------------------------------------------------------
       11) historial_representantes
       --------------------------------------------------------- */
    if (!empty($representantes)) {
        $sql_rep  = "INSERT INTO historial_representantes 
                     (Id_paciente, Id_representante, tipo_representante, parentesco, estatus) 
                     VALUES (?, ?, ?, ?, ?)";
        $stmt_rep = $conexion->prepare($sql_rep);
        if (!$stmt_rep) throw new Exception("Prepare representante: " . $conexion->error);

        foreach ($representantes as $rep) {
            $id_rep = isset($rep['id']) ? intval($rep['id']) : 0;
            if ($id_rep <= 0) continue;

            $tipo_rep = (isset($rep['tipo']) && in_array($rep['tipo'], ['Legal', 'Suplente'], true))
                        ? $rep['tipo'] : 'Legal';

            $parentesco_rep = (isset($rep['parentesco']) && $rep['parentesco'] !== '')
                              ? mb_substr($rep['parentesco'], 0, 25) : null;

            $stmt_rep->bind_param("iisss", $id_paciente, $id_rep, $tipo_rep, $parentesco_rep, $estatus_str);
            $stmt_rep->execute();
        }
        $stmt_rep->close();
    }

    /* ---------------------------------------------------------
       COMMIT
       --------------------------------------------------------- */
    $conexion->commit();

    $nombre_completo = trim($nombre . ' ' . $apellido);

    echo json_encode([
        'success'         => true,
        'id_pac'          => $id_paciente,
        'tipo_ced'        => $tipo_cedula,
        'cedula'          => $cedula,
        'nombre_completo' => $nombre_completo,
        'edad'            => $edad,
        'tipo_paciente'   => $tipo_paciente
    ]);

} catch (Exception $e) {
    $conexion->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conexion->close();
?>