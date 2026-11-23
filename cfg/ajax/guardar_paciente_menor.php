<?php
session_start();
require_once "../conexion.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

/* =========================================================
   1) RECOLECCIÓN DE DATOS
   ========================================================= */
$tipo_cedula        = trim($_POST['ext_tipo_cedula'] ?? '');
$cedula             = trim($_POST['ext_cedula'] ?? '');
$nombre             = trim($_POST['ext_nombre'] ?? '');
$apellido           = trim($_POST['ext_apellido'] ?? '');
$fecha_nacimiento   = trim($_POST['ext_fecha_nacimiento'] ?? '');
$genero             = trim($_POST['ext_genero'] ?? '');
$analfabeta         = trim($_POST['ext_analfabeta'] ?? 'No');
$etnia              = trim($_POST['ext_etnia'] ?? 'No');
$tipo_etnia         = trim($_POST['ext_tipo_etnia'] ?? '');
$grupo_sanguineo    = trim($_POST['ext_grupo_sanguineo'] ?? '');

$estado_dir         = trim($_POST['ext_estado'] ?? '');
$municipio_dir      = trim($_POST['ext_municipio'] ?? '');
$sector_dir         = trim($_POST['ext_sector'] ?? '');
$avenida_calle      = trim($_POST['ext_avenida_calle'] ?? '');
$referencia         = trim($_POST['ext_referencia'] ?? '');
$tiempo_residencia  = trim($_POST['ext_tiempo_residencia'] ?? '');
$tiempo             = trim($_POST['ext_tiempo'] ?? 'dia/s');

$municipio_nacimiento = trim($_POST['ext_municipio_nacimiento'] ?? '');

$nivel_instruccion  = trim($_POST['ext_nivel_instruccion'] ?? '');
$mision             = trim($_POST['ext_mision'] ?? '');
$anos_aprobados     = trim($_POST['ext_anos_aprobados'] ?? '0');

$patologias     = json_decode($_POST['ext_patologias_data'] ?? '[]', true);
$alergias       = json_decode($_POST['ext_alergias_data'] ?? '[]', true);
$discapacidades = json_decode($_POST['ext_discapacidades_data'] ?? '[]', true);
$representantes = json_decode($_POST['ext_representantes_data'] ?? '[]', true);

if (!is_array($patologias))     $patologias = [];
if (!is_array($alergias))       $alergias = [];
if (!is_array($discapacidades)) $discapacidades = [];
if (!is_array($representantes)) $representantes = [];

/* =========================================================
   2) DETERMINAR TIPO DE PACIENTE
   - Checkbox marcado             -> Interno
   - Viene de Citas (origen)      -> Interno
   - Viene de Despacho (origen)   -> Externo
   ========================================================= */
$origen                  = $_POST['origen'] ?? 'Externo';
$es_interno_checkbox     = (($_POST['ext_es_interno'] ?? '0') === '1');
$guardar_datos_completos = $es_interno_checkbox; // si guarda dirección, ocupación, lugar nac.

if ($es_interno_checkbox || $origen === 'Interno') {
    $tipo_paciente = 'Interno';
} else {
    $tipo_paciente = 'Externo';
}

/* =========================================================
   3) VALIDACIONES
   ========================================================= */
$faltantes = [];
if ($cedula === '')             $faltantes[] = 'Cédula';
if ($nombre === '')             $faltantes[] = 'Nombre';
if ($fecha_nacimiento === '')   $faltantes[] = 'Fecha de nacimiento';
if ($genero === '')             $faltantes[] = 'Sexo';
if ($analfabeta === '')         $faltantes[] = 'Analfabeta';
if ($grupo_sanguineo === '')    $faltantes[] = 'Grupo sanguíneo';
if (empty($representantes))     $faltantes[] = 'Al menos un Representante';

if (!empty($faltantes)) {
    echo json_encode(['success' => false, 'error' => 'Faltan campos obligatorios: ' . implode(', ', $faltantes)]);
    exit;
}

if (!in_array($tipo_cedula, ['V', 'PN', 'RP'], true)) {
    echo json_encode(['success' => false, 'error' => 'Tipo de cédula inválido']);
    exit;
}

$numCed = intval($cedula);
if ($tipo_cedula === 'V' && $numCed > 80000000) {
    echo json_encode(['success' => false, 'error' => 'Cédula V- no puede ser mayor a 80.000.000']);
    exit;
}
if ($tipo_cedula === 'RP' && !preg_match('/^[0-9]{8}-[0-9]{1}$/', $cedula)) {
    echo json_encode(['success' => false, 'error' => 'Formato REP incorrecto. Ejemplo: 12345678-1']);
    exit;
}

$grupos_validos = ['A+','A-','B+','B-','AB+','AB-','O+','O-'];
if (!in_array($grupo_sanguineo, $grupos_validos, true)) {
    echo json_encode(['success' => false, 'error' => 'Grupo sanguíneo inválido']);
    exit;
}

try {
    $fechaNacObj = new DateTime($fecha_nacimiento);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Fecha de nacimiento inválida']);
    exit;
}
$hoy = new DateTime();
$hoy->setTime(0, 0, 0);
$fechaNacObj->setTime(0, 0, 0);
if ($fechaNacObj > $hoy) {
    echo json_encode(['success' => false, 'error' => 'La fecha de nacimiento no puede ser futura']);
    exit;
}
$edad = $hoy->diff($fechaNacObj)->y;
if ($edad >= 18) {
    echo json_encode(['success' => false, 'error' => 'El paciente debe ser menor de 18 años (edad calculada: ' . $edad . ')']);
    exit;
}

$stmt_check = $conexion->prepare("SELECT id FROM persona WHERE cedula = ? AND tipo_cedula = ? LIMIT 1");
$stmt_check->bind_param("ss", $cedula, $tipo_cedula);
$stmt_check->execute();
$stmt_check->store_result();
if ($stmt_check->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'La cédula ' . $tipo_cedula . '-' . $cedula . ' ya se encuentra registrada en el sistema.']);
    $stmt_check->close();
    exit;
}
$stmt_check->close();

/* =========================================================
   4) NORMALIZACIÓN
   ========================================================= */
$nombre   = mb_substr($nombre, 0, 100);
$apellido = mb_substr($apellido, 0, 100);

if ($etnia !== 'Si') {
    $etnia = 'No';
    $tipo_etnia = 'Ninguna';
}
if ($tipo_etnia === '') $tipo_etnia = 'Ninguna';
$tipo_etnia = mb_substr($tipo_etnia, 0, 25);

if (!empty($discapacidades)) {
    $discapacidad_flag = 'Si';
    if (count($discapacidades) === 1) {
        $tipo_discapacidad = mb_substr($discapacidades[0]['nombre'] ?? 'Si', 0, 25);
    } else {
        $tipo_discapacidad = 'Múltiple';
    }
} else {
    $discapacidad_flag = 'No';
    $tipo_discapacidad = 'Ninguna';
}

if ($guardar_datos_completos) {
    $nivel_instruccion_db = ($nivel_instruccion !== '') ? mb_substr($nivel_instruccion, 0, 35) : null;
    $mision_db            = ($mision !== '')            ? mb_substr($mision, 0, 35) : null;
    $anos_db              = is_numeric($anos_aprobados)  ? intval($anos_aprobados) : 0;
} else {
    $nivel_instruccion_db = null;
    $mision_db            = null;
    $anos_db              = 0;
}

$sector_int = null;
if ($guardar_datos_completos && $sector_dir !== '' && is_numeric($sector_dir)) {
    $sector_int = intval($sector_dir);
}

$tiempo_residencia = mb_substr($tiempo_residencia, 0, 35);
$avenida_calle     = mb_substr($avenida_calle, 0, 25);
$referencia        = mb_substr($referencia, 0, 50);

/* =========================================================
   5) TRANSACCIÓN
   ========================================================= */
$estatus_int  = 1;
$estatus_str  = '1';
$rol_paciente = 3;
$password_vacio = '';
$fecha_actual = date('Y-m-d H:i:s');

$conexion->begin_transaction();

try {

    /* 5.1 persona */
    $email_db    = null;
    $apellido_db = ($apellido !== '') ? $apellido : null;

    $sql_p = "INSERT INTO persona (nombre, apellido, tipo_cedula, cedula, fecha_nacimiento, genero, email, password, estatus) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_p = $conexion->prepare($sql_p);
    if (!$stmt_p) throw new Exception("Prepare persona: " . $conexion->error);
    $stmt_p->bind_param("ssssssssi", $nombre, $apellido_db, $tipo_cedula, $cedula, $fecha_nacimiento, $genero, $email_db, $password_vacio, $estatus_int);
    if (!$stmt_p->execute()) throw new Exception("Execute persona: " . $stmt_p->error);
    $id_menor = $conexion->insert_id;
    $stmt_p->close();

    /* 5.2 detalle_persona_rol (Paciente = 3) */
    $sql_rol = "INSERT INTO detalle_persona_rol (Id_persona, Id_rol, estatus) VALUES (?, ?, ?)";
    $stmt_rol = $conexion->prepare($sql_rol);
    if (!$stmt_rol) throw new Exception("Prepare rol: " . $conexion->error);
    $stmt_rol->bind_param("iis", $id_menor, $rol_paciente, $estatus_str);
    if (!$stmt_rol->execute()) throw new Exception("Execute rol: " . $stmt_rol->error);
    $stmt_rol->close();

    /* 5.3 detalle_paciente_menor */
    $sql_dm = "INSERT INTO detalle_paciente_menor 
    (etnia, tipo_etnia, analfabeta, nivel_instruccion, mision, `años_aprobados`, tipo_paciente, id_persona) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_dm = $conexion->prepare($sql_dm);
    if (!$stmt_dm) throw new Exception("Prepare detalle_menor: " . $conexion->error);
    $stmt_dm->bind_param("sssssisi", $etnia, $tipo_etnia, $analfabeta, $nivel_instruccion_db, $mision_db, $anos_db, $tipo_paciente, $id_menor);    
    if (!$stmt_dm->execute()) throw new Exception("Execute detalle_menor: " . $stmt_dm->error);
    $stmt_dm->close();

    /* 5.4 historial_medico */
    $sql_hist = "INSERT INTO historial_medico (grupo_sanguineo, fecha, Id_persona, estatus) VALUES (?, ?, ?, ?)";
    $stmt_hist = $conexion->prepare($sql_hist);
    if (!$stmt_hist) throw new Exception("Prepare historial: " . $conexion->error);
    $stmt_hist->bind_param("ssii", $grupo_sanguineo, $fecha_actual, $id_menor, $estatus_int);
    if (!$stmt_hist->execute()) throw new Exception("Execute historial: " . $stmt_hist->error);
    $id_historial = $conexion->insert_id;
    $stmt_hist->close();

    /* 5.5 patologías */
    if (!empty($patologias)) {
        $sql_pat = "INSERT INTO historial_patologias (Id_patologia, Id_Historial, fecha_registro, estatus) VALUES (?, ?, ?, ?)";
        $stmt_pat = $conexion->prepare($sql_pat);
        if (!$stmt_pat) throw new Exception("Prepare patologia: " . $conexion->error);
        foreach ($patologias as $p) {
            $id_pat = intval($p['id'] ?? 0);
            if ($id_pat <= 0) continue;
            $fecha_pat = !empty($p['fecha']) ? $p['fecha'] : date('Y-m-d');
            $stmt_pat->bind_param("iiss", $id_pat, $id_historial, $fecha_reg, $estatus_str);
            if (!$stmt_pat->execute()) throw new Exception("Execute patologia: " . $stmt_pat->error);
        }
        $stmt_pat->close();
    }

    /* 5.6 alergias */
    if (!empty($alergias)) {
        $sql_ale = "INSERT INTO historial_alergias (Id_alergia, Id_Historial, fecha_registro, estatus) VALUES (?, ?, ?, ?)";        
        $stmt_ale = $conexion->prepare($sql_ale);
        if (!$stmt_ale) throw new Exception("Prepare alergia: " . $conexion->error);
        foreach ($alergias as $a) {
            $id_ale = intval($a['id'] ?? 0);
            if ($id_ale <= 0) continue;
            $fecha_ale = !empty($a['fecha']) ? $a['fecha'] : date('Y-m-d');
            $stmt_ale->bind_param("iiss", $id_ale, $id_historial, $fecha_reg, $estatus_str);           
            if (!$stmt_ale->execute()) throw new Exception("Execute alergia: " . $stmt_ale->error);
        }
        $stmt_ale->close();
    }

    /* 5.7 discapacidades */
    if (!empty($discapacidades)) {
        $sql_disc = "INSERT INTO historial_discapacidades (Id_Historial, Id_discapacidad, fecha_registro, estatus) VALUES (?, ?, ?, ?)"; 
        $stmt_disc = $conexion->prepare($sql_disc);
        if (!$stmt_disc) throw new Exception("Prepare discapacidad: " . $conexion->error);
        foreach ($discapacidades as $d) {
            $id_disc = intval($d['id'] ?? 0);
            if ($id_disc <= 0) continue;
            $fecha_disc = !empty($d['fecha']) ? $d['fecha'] : null;
            $stmt_disc->bind_param("iiss", $id_historial, $id_disc, $fecha_disc, $estatus_str);            
            if (!$stmt_disc->execute()) throw new Exception("Execute discapacidad: " . $stmt_disc->error);
        }
        $stmt_disc->close();
    }

    /* 5.8 representantes (id ya existe en persona) */
    if (!empty($representantes)) {
        $sql_rep = "INSERT INTO historial_representantes (Id_paciente, Id_representante, tipo_representante, parentesco, estatus) VALUES (?, ?, ?, ?, ?)";
        $stmt_rep = $conexion->prepare($sql_rep);
        if (!$stmt_rep) throw new Exception("Prepare representante: " . $conexion->error);
        foreach ($representantes as $r) {
            $id_rep = intval($r['id'] ?? 0);
            if ($id_rep <= 0) continue;
            $tipo_rep   = in_array($r['tipo'] ?? 'Legal', ['Legal', 'Suplente'], true) ? $r['tipo'] : 'Legal';
            $parentesco = !empty($r['parentesco']) ? mb_substr($r['parentesco'], 0, 25) : null;
            $stmt_rep->bind_param("iisss", $id_menor, $id_rep, $tipo_rep, $parentesco, $estatus_str);
            if (!$stmt_rep->execute()) throw new Exception("Execute representante: " . $stmt_rep->error);
        }
        $stmt_rep->close();
    }

    /* 5.9 lugar_nacimiento (solo si interno y municipio válido) */
    if ($guardar_datos_completos && $municipio_nacimiento !== '' && is_numeric($municipio_nacimiento)) {
        $mun_nac_int = intval($municipio_nacimiento);
        if ($mun_nac_int > 0) {
            $sql_lug = "INSERT INTO lugar_nacimiento (Id_persona, Id_municipio) VALUES (?, ?)";
            $stmt_lug = $conexion->prepare($sql_lug);
            if ($stmt_lug) {
                $stmt_lug->bind_param("ii", $id_menor, $mun_nac_int);
                if (!$stmt_lug->execute()) throw new Exception("Execute lugar_nacimiento: " . $stmt_lug->error);
                $stmt_lug->close();
            }
        }
    }

    /* 5.10 dirección: completa si es interno, dummy si externo */
    if ($guardar_datos_completos) {
        $sql_dir = "INSERT INTO direccion (tiempo_residencia, tiempo, avenida_calle, referencia, Id_persona, Id_sector, estatus) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_dir = $conexion->prepare($sql_dir);
        if (!$stmt_dir) throw new Exception("Prepare direccion: " . $conexion->error);
        $stmt_dir->bind_param("ssssiis", $tiempo_residencia, $tiempo, $avenida_calle, $referencia, $id_menor, $sector_int, $estatus_str);
        if (!$stmt_dir->execute()) throw new Exception("Execute direccion: " . $stmt_dir->error);
        $stmt_dir->close();
    } else {
        $vacio     = "";
        $ref_dummy = "Registrado Vía Rápida (" . $origen . ")";
        $tiempo_d  = "dia/s";
        $sql_dir = "INSERT INTO direccion (tiempo_residencia, tiempo, avenida_calle, referencia, Id_persona, estatus) 
                    VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_dir = $conexion->prepare($sql_dir);
        if ($stmt_dir) {
            $stmt_dir->bind_param("ssssis", $vacio, $tiempo_d, $vacio, $ref_dummy, $id_menor, $estatus_str);
            if (!$stmt_dir->execute()) throw new Exception("Execute direccion dummy: " . $stmt_dir->error);
            $stmt_dir->close();
        }
    }

    $conexion->commit();

    $nombre_completo = trim($nombre . ' ' . $apellido);

    echo json_encode([
        'success'         => true,
        'id_pac'          => $id_menor,
        'tipo_ced'        => $tipo_cedula,
        'cedula'          => $cedula,
        'nombre_completo' => $nombre_completo,
        'edad'            => $edad,
        'tipo_paciente'   => $tipo_paciente,
        'es_interno'      => $guardar_datos_completos
    ]);

} catch (Exception $e) {
    $conexion->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conexion->close();
?>