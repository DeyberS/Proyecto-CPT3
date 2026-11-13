<?php
session_start();
include("../conexion.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($conexion)) {
    $_SESSION['mensaje_user_error'] = 'Error: Acceso denegado o falta conexión.';
    header("Location: ../../pages/php/consulta_listado.php");
    exit();
}

/* ============================================================
   HELPERS
   ============================================================ */
function sanitizar($conexion, $input)
{
    return mysqli_real_escape_string($conexion, trim($input ?? ''));
}

function truncar($str, $max)
{
    if ($str === null || $str === '') return $str;
    return mb_substr($str, 0, $max);
}

function parse_decimal($valor)
{
    if ($valor === '' || $valor === null) return null;
    $valor = str_replace(',', '.', trim($valor));
    if (!is_numeric($valor)) return null;
    return (float)$valor;
}

function parse_entero($valor)
{
    if ($valor === '' || $valor === null) return null;
    if (!is_numeric(trim($valor))) return null;
    return (int)$valor;
}

/**
 * Resuelve un "id" del JSON de medicamentos al id_descripcion_medicamento correcto.
 * Los precargados traen ds.id_detalle; los nuevos traen directamente dm.Id.
 */
function resolver_descripcion_medicamento($conexion, $id_o_detalle)
{
    $id = (int)$id_o_detalle;
    if ($id <= 0) return 0;
    // Intentar como id_detalle
    $q = mysqli_query($conexion, "SELECT id_descripcion_medicamento FROM detalle_solicitud WHERE id_detalle = $id LIMIT 1");
    if ($q && $row = mysqli_fetch_assoc($q)) return (int)$row['id_descripcion_medicamento'];
    // Intentar como id_descripcion directo
    $q = mysqli_query($conexion, "SELECT Id FROM descripcion_medicamento WHERE Id = $id LIMIT 1");
    if ($q && mysqli_fetch_assoc($q)) return $id;
    return 0;
}

/**
 * Resuelve un examen del JSON al id_examen correcto.
 * Los precargados vienen con id=null → buscar por nombre; los nuevos traen id del catálogo.
 */
function resolver_id_examen($conexion, $id, $nombre, $tipo = 'Otro')
{
    $id = (int)$id;
    if ($id > 0) {
        $q = mysqli_query($conexion, "SELECT id_examen FROM examen_catalogo WHERE id_examen = $id LIMIT 1");
        if ($q && mysqli_fetch_assoc($q)) return $id;
    }
    $nombre_esc = mysqli_real_escape_string($conexion, trim($nombre ?? ''));
    if ($nombre_esc !== '') {
        $q = mysqli_query($conexion, "SELECT id_examen FROM examen_catalogo WHERE nombre = '$nombre_esc' LIMIT 1");
        if ($q && $row = mysqli_fetch_assoc($q)) return (int)$row['id_examen'];
        $tipo_val = in_array($tipo, ['Laboratorio', 'Imagen', 'Patologia', 'Otro']) ? $tipo : 'Otro';
        if (mysqli_query($conexion, "INSERT INTO examen_catalogo (nombre, tipo, estatus) VALUES ('$nombre_esc', '$tipo_val', 1)")) {
            return mysqli_insert_id($conexion);
        }
    }
    return 0;
}

/* ============================================================
   1) VALIDAR IDs MÍNIMOS
   ============================================================ */
$id_consulta  = (int)($_POST['Id_Consulta'] ?? 0);
$id_historial = (int)($_POST['Id_historial'] ?? 0);
$id_medico    = (int)($_POST['medico'] ?? 0);

if ($id_consulta <= 0 || $id_historial <= 0 || $id_medico <= 0) {
    $_SESSION['mensaje_user_error'] = '⚠️ Error: Faltan datos obligatorios (consulta, historial o médico).';
    header("Location: ../../pages/php/consulta_listado.php");
    exit();
}

/* ============================================================
   2) CAPTURA Y NORMALIZACIÓN DE CAMPOS
   ============================================================ */
$fecha_consulta    = sanitizar($conexion, $_POST['fecha_consulta'] ?? '');
$motivo_consulta   = sanitizar($conexion, $_POST['motivo_consulta'] ?? '');
$tratamiento_ind   = sanitizar($conexion, $_POST['tratamiento_indicado'] ?? '');
$notas_adicionales = sanitizar($conexion, $_POST['notas_adicionales'] ?? '');

if ($fecha_consulta === '') {
    $_SESSION['mensaje_user_error'] = '⚠️ Error: La fecha de consulta es obligatoria.';
    header("Location: ../../pages/php/consulta_listado.php");
    exit();
}

// Signos vitales
$peso          = parse_decimal($_POST['peso'] ?? '');
$talla         = parse_decimal($_POST['talla'] ?? '');
$temperatura   = parse_decimal($_POST['temperatura'] ?? '');
$presion_sis   = parse_entero($_POST['presion_sistolica'] ?? '');
$presion_dia   = parse_entero($_POST['presion_diastolica'] ?? '');
$frec_cardiaca = parse_entero($_POST['frecuencia_cardiaca'] ?? '');
$saturacion    = parse_entero($_POST['saturacion'] ?? '');
$frec_resp     = parse_entero($_POST['frecuencia_respiratoria'] ?? '');

// Diagnóstico
$diagnostico_text = sanitizar($conexion, $_POST['diagnostico_text'] ?? '');
$id_patologia_dx  = (int)($_POST['id_patologia_dx'] ?? 0);
$codigo_cie_dx    = truncar(sanitizar($conexion, $_POST['codigo_cie_dx'] ?? ''), 15);

// Evolución
$estado_paciente     = truncar(sanitizar($conexion, $_POST['estado_paciente'] ?? ''), 45);
$evolucion_resultado = truncar(sanitizar($conexion, $_POST['evolucion_resultado'] ?? ''), 255);
$lectura_examenes    = sanitizar($conexion, $_POST['lectura_examenes'] ?? '');

// Reacción adversa
$reaccion_adversa = strtoupper(trim($_POST['reaccion_adversa'] ?? 'NO'));
$reaccion_adversa = in_array($reaccion_adversa, ['SI', 'SÍ', 'S'], true) ? 'SI' : 'NO';
$detalle_reaccion = ($reaccion_adversa === 'SI')
    ? truncar(sanitizar($conexion, $_POST['detalle_reaccion'] ?? ''), 255)
    : '';

// Receptor de receta
$tipo_receptor_val = sanitizar($conexion, $_POST['tipo_receptor'] ?? 'Paciente');
if (!in_array($tipo_receptor_val, ['Paciente', 'Representante', 'Familiar', 'Otro'], true)) {
    $tipo_receptor_val = 'Paciente';
}
$responsable_val     = truncar(sanitizar($conexion, $_POST['responsable'] ?? ($_POST['entregado_a'] ?? '')), 100);
$parentesco_val      = truncar(sanitizar($conexion, $_POST['parentesco_representante'] ?? 'Ninguno'), 25);
if ($parentesco_val === '') $parentesco_val = 'Ninguno';
$cedula_receptor_val = truncar(sanitizar($conexion, $_POST['cedula_receptor'] ?? ''), 20);

// Cita
$fecha_cita = sanitizar($conexion, $_POST['fecha_cita'] ?? '');
$hora_cita  = sanitizar($conexion, $_POST['hora_cita'] ?? '');

// JSON
$medicamentos = json_decode($_POST['medicamentos_json'] ?? '[]', true);
if (!is_array($medicamentos)) $medicamentos = [];

$examenes = json_decode($_POST['examenes_json'] ?? '[]', true);
if (!is_array($examenes)) $examenes = [];

/* ============================================================
   3) TRANSACCIÓN
   ============================================================ */
mysqli_begin_transaction($conexion);

try {

    /* --- 3.1 Verificar consulta --- */
    $res_actual = mysqli_query($conexion, "SELECT Id_historial, Id_paciente FROM consulta WHERE Id_consulta = $id_consulta LIMIT 1");
    if (!$res_actual || mysqli_num_rows($res_actual) === 0) {
        throw new Exception("La consulta con ID $id_consulta no existe.");
    }
    $consulta_actual = mysqli_fetch_assoc($res_actual);
    $id_persona = (int)$consulta_actual['Id_paciente'];

    /* --- GUARDAR ANTECEDENTES JSON --- */
    $antecedentes_json = $_POST['antecedentes_json'] ?? '';
    if ($antecedentes_json !== '') {
        $safe_json = mysqli_real_escape_string($conexion, $antecedentes_json);
        $sql_ant = "UPDATE historial_medico SET antecedentes_json = '$safe_json' WHERE id_historial = $id_historial";
        if (!mysqli_query($conexion, $sql_ant)) {
            throw new Exception("Error al guardar antecedentes: " . mysqli_error($conexion));
        }
    }

    if ((int)$consulta_actual['Id_historial'] !== $id_historial) {
        throw new Exception("El historial enviado no coincide con el de la consulta original.");
    }

    /* --- 3.2 UPDATE consulta (solo columnas que existen en la tabla) --- */
    $sql_update = "UPDATE consulta SET
        Id_medico            = $id_medico,
        fecha_consulta       = '$fecha_consulta',
        motivo_consulta      = '$motivo_consulta',
        tratamiento_indicado = '$tratamiento_ind'
        WHERE Id_consulta = $id_consulta";
    if (!mysqli_query($conexion, $sql_update)) {
        throw new Exception("Actualización de consulta: " . mysqli_error($conexion));
    }

    /* --- 3.3 Signos vitales --- */
    $peso_sql  = $peso          !== null ? "'$peso'"         : "NULL";
    $talla_sql = $talla         !== null ? "'$talla'"        : "NULL";
    $temp_sql  = $temperatura   !== null ? "'$temperatura'"  : "NULL";
    $psis_sql  = $presion_sis   !== null ? "$presion_sis"    : "NULL";
    $pdia_sql  = $presion_dia   !== null ? "$presion_dia"    : "NULL";
    $fc_sql    = $frec_cardiaca !== null ? "$frec_cardiaca"  : "NULL";
    $sat_sql   = $saturacion    !== null ? "$saturacion"     : "NULL";
    $fr_sql    = $frec_resp     !== null ? "$frec_resp"      : "NULL";

    $res_sv = mysqli_query($conexion, "SELECT id_medicion FROM signos_vitales_consulta WHERE id_consulta = $id_consulta ORDER BY fecha_medicion DESC LIMIT 1");
    if ($res_sv && $row_sv = mysqli_fetch_assoc($res_sv)) {
        $id_medicion = (int)$row_sv['id_medicion'];
        $sql_sv = "UPDATE signos_vitales_consulta SET
            peso = $peso_sql, talla = $talla_sql, temperatura = $temp_sql,
            presion_sistolica = $psis_sql, presion_diastolica = $pdia_sql,
            frecuencia_cardiaca = $fc_sql, frecuencia_respiratoria = $fr_sql,
            saturacion = $sat_sql, fecha_medicion = NOW()
            WHERE id_medicion = $id_medicion";
    } else {
        $sql_sv = "INSERT INTO signos_vitales_consulta
            (id_consulta, fecha_medicion, peso, talla, temperatura, presion_sistolica, presion_diastolica, frecuencia_cardiaca, frecuencia_respiratoria, saturacion)
            VALUES ($id_consulta, NOW(), $peso_sql, $talla_sql, $temp_sql, $psis_sql, $pdia_sql, $fc_sql, $fr_sql, $sat_sql)";
    }
    if (!mysqli_query($conexion, $sql_sv)) {
        throw new Exception("Guardado de signos vitales: " . mysqli_error($conexion));
    }

    /* --- 3.4 Diagnóstico principal --- */
    $id_pat_sql = $id_patologia_dx > 0 ? "$id_patologia_dx" : "NULL";
    $cie_sql    = $codigo_cie_dx !== '' ? "'$codigo_cie_dx'" : "NULL";
    $desc_sql   = $diagnostico_text !== '' ? "'$diagnostico_text'" : "NULL";

    $res_dx = mysqli_query($conexion, "SELECT id_diagnostico FROM diagnostico_consulta WHERE id_consulta = $id_consulta AND tipo = 'Principal' ORDER BY id_diagnostico DESC LIMIT 1");
    if ($res_dx && $row_dx = mysqli_fetch_assoc($res_dx)) {
        $id_dx = (int)$row_dx['id_diagnostico'];
        $sql_dx = "UPDATE diagnostico_consulta SET
            id_patologia = $id_pat_sql,
            descripcion_libre = $desc_sql,
            codigo_cie = $cie_sql
            WHERE id_diagnostico = $id_dx";
    } else {
        $sql_dx = "INSERT INTO diagnostico_consulta
            (id_consulta, id_patologia, descripcion_libre, tipo, codigo_cie)
            VALUES ($id_consulta, $id_pat_sql, $desc_sql, 'Principal', $cie_sql)";
    }
    if (!mysqli_query($conexion, $sql_dx)) {
        throw new Exception("Guardado del diagnóstico: " . mysqli_error($conexion));
    }

    /* --- 3.5 Evolución --- */
    $estado_sql = $estado_paciente !== '' ? "'$estado_paciente'" : "NULL";
    $evol_sql   = $evolucion_resultado !== '' ? "'$evolucion_resultado'" : "NULL";
    $lect_sql   = $lectura_examenes !== '' ? "'$lectura_examenes'" : "NULL";

    $res_ev = mysqli_query($conexion, "SELECT id_evolucion FROM evolucion_consulta WHERE id_consulta = $id_consulta ORDER BY fecha_registro DESC LIMIT 1");
    if ($res_ev && $row_ev = mysqli_fetch_assoc($res_ev)) {
        $id_ev = (int)$row_ev['id_evolucion'];
        $sql_ev = "UPDATE evolucion_consulta SET
            estado_paciente = $estado_sql,
            evolucion_resultado = $evol_sql,
            lectura_examenes = $lect_sql,
            fecha_registro = NOW()
            WHERE id_evolucion = $id_ev";
    } else {
        $sql_ev = "INSERT INTO evolucion_consulta
            (id_consulta, estado_paciente, evolucion_resultado, lectura_examenes, fecha_registro)
            VALUES ($id_consulta, $estado_sql, $evol_sql, $lect_sql, NOW())";
    }
    if (!mysqli_query($conexion, $sql_ev)) {
        throw new Exception("Guardado de evolución: " . mysqli_error($conexion));
    }

    /* --- 3.6 Reacción adversa --- */
    $detalle_sql = $detalle_reaccion !== '' ? "'$detalle_reaccion'" : "NULL";

    $res_ra = mysqli_query($conexion, "SELECT id_reaccion FROM reaccion_adversa_consulta WHERE id_consulta = $id_consulta LIMIT 1");
    if ($res_ra && $row_ra = mysqli_fetch_assoc($res_ra)) {
        $id_ra = (int)$row_ra['id_reaccion'];
        $sql_ra = "UPDATE reaccion_adversa_consulta SET
            tiene_reaccion = '$reaccion_adversa',
            detalle = $detalle_sql
            WHERE id_reaccion = $id_ra";
    } else {
        $sql_ra = "INSERT INTO reaccion_adversa_consulta
            (id_consulta, tiene_reaccion, detalle)
            VALUES ($id_consulta, '$reaccion_adversa', $detalle_sql)";
    }
    if (!mysqli_query($conexion, $sql_ra)) {
        throw new Exception("Guardado de reacción adversa: " . mysqli_error($conexion));
    }

    /* --- 3.7 Solicitud_medicamento (receptor) --- */
    $responsable_sql = $responsable_val !== '' ? "'$responsable_val'" : "NULL";
    $parentesco_sql  = $parentesco_val  !== '' ? "'$parentesco_val'"  : "NULL";
    $cedula_rec_sql  = $cedula_receptor_val !== '' ? "'$cedula_receptor_val'" : "NULL";

    $res_sol = mysqli_query($conexion, "SELECT id_solicitud FROM solicitud_medicamento WHERE id_consulta = $id_consulta ORDER BY id_solicitud DESC LIMIT 1");
    $id_solicitud = null;

    if ($res_sol && $row_sol = mysqli_fetch_assoc($res_sol)) {
        $id_solicitud = (int)$row_sol['id_solicitud'];
        $sql_sol = "UPDATE solicitud_medicamento SET
            tipo_receptor = '$tipo_receptor_val',
            entregado_a = $responsable_sql,
            parentesco_receptor = $parentesco_sql,
            cedula_receptor = $cedula_rec_sql
            WHERE id_solicitud = $id_solicitud";
        if (!mysqli_query($conexion, $sql_sol)) {
            throw new Exception("Actualización de solicitud: " . mysqli_error($conexion));
        }
    } elseif (!empty($medicamentos) || $responsable_val !== '' || $tipo_receptor_val !== 'Paciente') {
        $entregado_short = $responsable_val !== '' ? $responsable_val : 'Sin especificar';
        $sql_sol = "INSERT INTO solicitud_medicamento
            (origen, id_consulta, id_paciente, id_medico, tipo_receptor, entregado_a, parentesco_receptor, cedula_receptor, estatus_general)
            VALUES ('Interno', $id_consulta, $id_persona, $id_medico, '$tipo_receptor_val', '$entregado_short', $parentesco_sql, $cedula_rec_sql, 'Pendiente')";
        if (!mysqli_query($conexion, $sql_sol)) {
            throw new Exception("Creación de solicitud: " . mysqli_error($conexion));
        }
        $id_solicitud = mysqli_insert_id($conexion);
    }

    /* --- 3.8 Medicamentos --- */
    $med_resueltos = [];
    foreach ($medicamentos as $med) {
        $id_desc = resolver_descripcion_medicamento($conexion, $med['id'] ?? 0);
        if ($id_desc <= 0) continue;
        $via_val = $med['via'] ?? 'Oral';
        if (!in_array($via_val, ['Oral', 'Intravenosa', 'Intramuscular', 'Subcutanea', 'Topica', 'Oftalmica', 'Otica', 'Nasal', 'Rectal', 'Inhalatoria', 'Otra'])) {
            $via_val = 'Oral';
        }
        $med_resueltos[] = [
            'id_descripcion' => $id_desc,
            'cantidad'       => max(1, (int)($med['cantidad'] ?? 1)),
            'dosis'          => truncar(sanitizar($conexion, $med['dosis'] ?? ''), 50),
            'frecuencia'     => truncar(sanitizar($conexion, $med['frecuencia'] ?? ''), 50),
            'via'            => $via_val,
            'duracion'       => truncar(sanitizar($conexion, $med['duracion'] ?? ''), 50),
            'indicaciones'   => truncar(sanitizar($conexion, $med['indicaciones'] ?? ''), 255),
        ];
    }

    if ($id_solicitud) {
        mysqli_query($conexion, "DELETE FROM detalle_solicitud WHERE id_solicitud = $id_solicitud AND estatus_item = 'Pendiente'");
    }

    if (!empty($med_resueltos)) {
        if (!$id_solicitud) {
            $sql_sol = "INSERT INTO solicitud_medicamento
                (origen, id_consulta, id_paciente, id_medico, tipo_receptor, entregado_a, estatus_general)
                VALUES ('Interno', $id_consulta, $id_persona, $id_medico, '$tipo_receptor_val', 'Sin especificar', 'Pendiente')";
            if (!mysqli_query($conexion, $sql_sol)) {
                throw new Exception("Creación de solicitud (2): " . mysqli_error($conexion));
            }
            $id_solicitud = mysqli_insert_id($conexion);
        }

        $motivo_item = $motivo_consulta !== '' ? $motivo_consulta : 'Sin motivo especificado';

        foreach ($med_resueltos as $m) {
            // No duplicar si ya existe con estatus no pendiente
            $res_chk = mysqli_query($conexion, "SELECT id_detalle FROM detalle_solicitud
                WHERE id_solicitud = $id_solicitud
                  AND id_descripcion_medicamento = {$m['id_descripcion']}
                  AND estatus_item <> 'Pendiente'
                LIMIT 1");
            if ($res_chk && mysqli_fetch_assoc($res_chk)) continue;

            $dosis_sql = $m['dosis']        !== '' ? "'{$m['dosis']}'"        : "NULL";
            $frec_sql  = $m['frecuencia']   !== '' ? "'{$m['frecuencia']}'"   : "NULL";
            $dur_sql   = $m['duracion']     !== '' ? "'{$m['duracion']}'"     : "NULL";
            $ind_sql   = $m['indicaciones'] !== '' ? "'{$m['indicaciones']}'" : "NULL";

            $sql_det = "INSERT INTO detalle_solicitud
                (id_solicitud, id_descripcion_medicamento, cantidad_recetada, dosis, frecuencia, via, duracion, indicaciones, cantidad_entregada, estatus_item, motivo)
                VALUES ($id_solicitud, {$m['id_descripcion']}, {$m['cantidad']}, $dosis_sql, $frec_sql, '{$m['via']}', $dur_sql, $ind_sql, 0, 'Pendiente', '$motivo_item')";
            if (!mysqli_query($conexion, $sql_det)) {
                throw new Exception("Inserción de detalle_solicitud: " . mysqli_error($conexion));
            }
        }
    }

    /* --- 3.9 Exámenes --- */
    $ex_resueltos = [];
    foreach ($examenes as $ex) {
        $id_ex = resolver_id_examen($conexion, $ex['id'] ?? 0, $ex['nombre'] ?? '', $ex['tipo'] ?? 'Otro');
        if ($id_ex <= 0) continue;
        $prioridad = in_array($ex['prioridad'] ?? 'Rutina', ['Rutina', 'Urgente']) ? $ex['prioridad'] : 'Rutina';
        $ex_resueltos[] = [
            'id_examen'    => $id_ex,
            'prioridad'    => $prioridad,
            'indicaciones' => truncar(sanitizar($conexion, $ex['indicaciones'] ?? ''), 255),
        ];
    }

    $res_sex = mysqli_query($conexion, "SELECT id_solicitud_examen FROM solicitud_examen WHERE id_consulta = $id_consulta ORDER BY id_solicitud_examen DESC LIMIT 1");
    $id_solicitud_ex = null;
    if ($res_sex && $row_sex = mysqli_fetch_assoc($res_sex)) {
        $id_solicitud_ex = (int)$row_sex['id_solicitud_examen'];
        mysqli_query($conexion, "DELETE FROM detalle_solicitud_examen WHERE id_solicitud_examen = $id_solicitud_ex AND estado_item = 'Pendiente'");
    }

    if (!empty($ex_resueltos)) {
        if (!$id_solicitud_ex) {
            $sql_sex = "INSERT INTO solicitud_examen (id_consulta, id_medico, estado, estatus) VALUES ($id_consulta, $id_medico, 'Pendiente', 1)";
            if (!mysqli_query($conexion, $sql_sex)) {
                throw new Exception("Creación de solicitud_examen: " . mysqli_error($conexion));
            }
            $id_solicitud_ex = mysqli_insert_id($conexion);
        }

        foreach ($ex_resueltos as $e) {
            $res_chk = mysqli_query($conexion, "SELECT id_detalle_examen FROM detalle_solicitud_examen
                WHERE id_solicitud_examen = $id_solicitud_ex AND id_examen = {$e['id_examen']} LIMIT 1");
            if ($res_chk && mysqli_fetch_assoc($res_chk)) continue;

            $ind_sql = $e['indicaciones'] !== '' ? "'{$e['indicaciones']}'" : "NULL";
            $sql_dex = "INSERT INTO detalle_solicitud_examen
                (id_solicitud_examen, id_examen, prioridad, indicaciones, estado_item)
                VALUES ($id_solicitud_ex, {$e['id_examen']}, '{$e['prioridad']}', $ind_sql, 'Pendiente')";
            if (!mysqli_query($conexion, $sql_dex)) {
                throw new Exception("Inserción de detalle_solicitud_examen: " . mysqli_error($conexion));
            }
        }
    }

    /* --- 3.10 Notas adicionales --- */
    if ($notas_adicionales !== '') {
        $res_nota = mysqli_query($conexion, "SELECT id FROM observaciones_historial_medico
            WHERE Id_historial_medico = $id_historial AND Id_medico = $id_medico
            ORDER BY fecha DESC, id DESC LIMIT 1");
        if ($res_nota && $row_nota = mysqli_fetch_assoc($res_nota)) {
            $id_nota = (int)$row_nota['id'];
            mysqli_query($conexion, "UPDATE observaciones_historial_medico
                SET observacion = '$notas_adicionales', fecha = NOW()
                WHERE id = $id_nota");
        } else {
            mysqli_query($conexion, "INSERT INTO observaciones_historial_medico
                (Id_historial_medico, Id_medico, observacion, fecha)
                VALUES ($id_historial, $id_medico, '$notas_adicionales', NOW())");
        }
    }

    /* --- 3.11 Cita (solo si hay fecha y hora) --- */
    if ($fecha_cita !== '' && $hora_cita !== '') {
        $id_especialidad = 0;

        $res_esp = mysqli_query($conexion, "SELECT Id_especialidad FROM especialidades_medicos WHERE Id_detalle_medico = $id_medico LIMIT 1");
        if ($res_esp && $row_esp = mysqli_fetch_assoc($res_esp)) {
            $id_especialidad = (int)$row_esp['Id_especialidad'];
        }
        if ($id_especialidad <= 0) {
            $res_esp2 = mysqli_query($conexion, "SELECT Id_especialidad FROM especialidad ORDER BY Id_especialidad ASC LIMIT 1");
            if ($res_esp2 && $row_esp2 = mysqli_fetch_assoc($res_esp2)) {
                $id_especialidad = (int)$row_esp2['Id_especialidad'];
            }
        }

        if ($id_especialidad > 0) {
            $res_cita = mysqli_query($conexion, "SELECT id_cita FROM citas
                WHERE Id_paciente = $id_persona AND estado IN ('Pendiente','Confirmada','Reprogramada')
                ORDER BY fecha_cita ASC LIMIT 1");
            $motivo_cita = truncar($motivo_consulta, 255);

            if ($res_cita && $row_cita = mysqli_fetch_assoc($res_cita)) {
                $id_cita = (int)$row_cita['id_cita'];
                $sql_cita = "UPDATE citas SET
                    fecha_cita = '$fecha_cita',
                    hora_cita = '$hora_cita',
                    Id_medico = $id_medico,
                    Id_especialidad = $id_especialidad
                    WHERE id_cita = $id_cita";
            } else {
                $sql_cita = "INSERT INTO citas
                    (fecha_cita, hora_cita, motivo, Id_paciente, Id_medico, Id_especialidad, estado, estatus)
                    VALUES ('$fecha_cita', '$hora_cita', '$motivo_cita', $id_persona, $id_medico, $id_especialidad, 'Pendiente', 1)";
            }
            if (!mysqli_query($conexion, $sql_cita)) {
                throw new Exception("Guardado de cita: " . mysqli_error($conexion));
            }
        }
    }

    /* --- 3.12 COMMIT --- */
    if (!mysqli_commit($conexion)) {
        throw new Exception("El COMMIT falló.");
    }

    $_SESSION['mensaje_user_exito'] = '✅ Éxito: La consulta fue actualizada correctamente.';
    header("Location: ../../pages/php/consulta_listado.php?id_consulta=$id_consulta");
    exit();
} catch (Exception $e) {
    mysqli_rollback($conexion);
    error_log("⚠️ Error al editar la consulta: " . $e->getMessage());
    $_SESSION['mensaje_user_error'] = '⚠️ Error de Actualización: ' . $e->getMessage();
    header("Location: ../../pages/php/consulta_listado.php");
    exit();
}
