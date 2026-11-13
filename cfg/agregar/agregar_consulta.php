<?php
session_start();
include("../conexion.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($conexion)) {
    $_SESSION['mensaje_user_error'] = 'Error: Acceso denegado o falta conexión.';
    header("Location: ../../pages/php/consulta_listado.php");
    exit();
}

function sanitizar($conexion, $input) {
    return mysqli_real_escape_string($conexion, trim($input ?? ''));
}

/**
 * Normaliza la cédula: quita prefijo (V-, E-, PN-, REP-, RP-) y espacios.
 * Preserva guiones internos (necesarios para formato RP: 12345678-1).
 */
function normalizar_cedula($cedula) {
    $cedula = trim($cedula ?? '');
    $cedula = preg_replace('/^(V|E|PN|REP|RP)\s*-?\s*/i', '', $cedula);
    return trim($cedula);
}

/* ══════════════════════════════════════════════════════════════════
   1) CAPTURA DE DATOS
   ══════════════════════════════════════════════════════════════════ */
$cedula_paciente   = normalizar_cedula(sanitizar($conexion, $_POST['cedula_paciente'] ?? ''));
$id_medico         = (int)($_POST['medico'] ?? 0);
$fecha_consulta    = sanitizar($conexion, $_POST['fecha_consulta'] ?? date('Y-m-d'));
$motivo_consulta   = sanitizar($conexion, $_POST['motivo_consulta'] ?? '');
$tratamiento_indi  = sanitizar($conexion, $_POST['tratamiento_indicado'] ?? '');
$notas_adicionales = sanitizar($conexion, $_POST['notas_adicionales'] ?? '');
$lectura_examenes  = sanitizar($conexion, $_POST['lectura_examenes'] ?? '');

/* ── Signos vitales ── */
$peso               = ($_POST['peso'] ?? '')                !== '' ? (float)$_POST['peso']                : null;
$talla              = ($_POST['talla'] ?? '')               !== '' ? (float)$_POST['talla']               : null;
$temperatura        = ($_POST['temperatura'] ?? '')         !== '' ? (float)$_POST['temperatura']         : null;
$presion_sistolica  = ($_POST['presion_sistolica'] ?? '')   !== '' ? (int)$_POST['presion_sistolica']     : null;
$presion_diastolica = ($_POST['presion_diastolica'] ?? '')  !== '' ? (int)$_POST['presion_diastolica']    : null;
$frecuencia_card    = ($_POST['frecuencia_cardiaca'] ?? '') !== '' ? (int)$_POST['frecuencia_cardiaca']   : null;
$frecuencia_resp    = ($_POST['frecuencia_respiratoria'] ?? '') !== '' ? (int)$_POST['frecuencia_respiratoria'] : null;
$saturacion         = ($_POST['saturacion'] ?? '')          !== '' ? (int)$_POST['saturacion']            : null;

/* ── Diagnóstico ── */
$id_patologia_dx = (int)($_POST['id_patologia_dx'] ?? 0);
$codigo_cie_dx   = sanitizar($conexion, $_POST['codigo_cie_dx'] ?? '');
$diagnostico_txt = sanitizar($conexion, $_POST['diagnostico_text'] ?? '');

/* ── Evolución / reacción adversa ── */
$estado_paciente      = sanitizar($conexion, $_POST['estado_paciente'] ?? '');
$evolucion_resultado  = sanitizar($conexion, $_POST['evolucion_resultado'] ?? '');
$reaccion_adversa     = sanitizar($conexion, $_POST['reaccion_adversa'] ?? 'NO');
$detalle_reaccion     = sanitizar($conexion, $_POST['detalle_reaccion'] ?? '');

/* ── Cita de seguimiento ── */
$fecha_cita = !empty($_POST['fecha_cita']) ? sanitizar($conexion, $_POST['fecha_cita']) : null;
$hora_cita  = !empty($_POST['hora_cita'])  ? sanitizar($conexion, $_POST['hora_cita'])  : null;

/* ── ANTECEDENTES (JSON unificado) ── */
$antecedentes_json = trim($_POST['antecedentes_json'] ?? '');
// Validar que sea JSON válido (array u objeto) antes de guardarlo
if ($antecedentes_json !== '') {
    $antecedentes_decoded = json_decode($antecedentes_json, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($antecedentes_decoded)) {
        $antecedentes_json = ''; // JSON corrupto → se ignora (no rompe la consulta)
        error_log("⚠️ Antecedentes JSON inválido recibido en agregar_consulta.php");
    }
}

/* ── Receptor de la receta ── */
$tipo_receptor          = sanitizar($conexion, $_POST['tipo_receptor'] ?? 'Paciente');
$responsable            = sanitizar($conexion, $_POST['responsable'] ?? '');
$parentesco_represent   = sanitizar($conexion, $_POST['parentesco_representante'] ?? '');
$cedula_receptor        = sanitizar($conexion, $_POST['cedula_receptor'] ?? '');
$id_representante_post  = (int)($_POST['id_representante'] ?? 0);

/* ── Cita atendida ── */
$id_cita_atendida = (int)($_POST['id_cita_atendida'] ?? 0);

/* ── Exámenes y medicamentos (JSON) ── */
$examenes_json     = json_decode($_POST['examenes_json'] ?? '[]', true);
$medicamentos_json = json_decode($_POST['medicamentos_json'] ?? '[]', true);
if (!is_array($examenes_json))     $examenes_json = [];
if (!is_array($medicamentos_json)) $medicamentos_json = [];

mysqli_autocommit($conexion, false);

try {

    /* ══════════════════════════════════════════════════════════════
       1.1) VALIDACIÓN SERVER-SIDE DE CAMPOS OBLIGATORIOS
       ══════════════════════════════════════════════════════════════ */
    if ($cedula_paciente === '')   throw new Exception("La cédula del paciente es obligatoria.");
    if ($id_medico <= 0)           throw new Exception("Debe seleccionar un médico válido.");
    if ($motivo_consulta === '')   throw new Exception("El motivo de la consulta es obligatorio.");
    if ($diagnostico_txt === '')   throw new Exception("El diagnóstico es obligatorio.");
    if ($estado_paciente === '')   throw new Exception("El estado/evolución del paciente es obligatorio.");
    if ($evolucion_resultado === '') throw new Exception("El detalle de la evolución es obligatorio.");
    if ($tratamiento_indi === '')  throw new Exception("El plan/tratamiento general es obligatorio.");

    /* ══════════════════════════════════════════════════════════════
       2) BUSCAR PERSONA E HISTORIAL
       ══════════════════════════════════════════════════════════════ */
    $sql_ids = "SELECT p.id AS id_persona, hm.id_historial
                FROM persona p
                JOIN historial_medico hm ON p.id = hm.Id_persona
                WHERE p.cedula = '$cedula_paciente' LIMIT 1";
    $res_ids = mysqli_query($conexion, $sql_ids);
    if (!$res_ids || mysqli_num_rows($res_ids) === 0) {
        throw new Exception("Paciente no encontrado o sin historial médico. (Cédula buscada: $cedula_paciente)");
    }
    $datos        = mysqli_fetch_assoc($res_ids);
    $id_persona   = (int)$datos['id_persona'];
    $id_historial = (int)$datos['id_historial'];

    /* ══════════════════════════════════════════════════════════════
       2.1) FALLBACK DE CIE DESDE LA PATOLOGÍA
       ══════════════════════════════════════════════════════════════ */
    if ($codigo_cie_dx === '' && $id_patologia_dx > 0) {
        $r_cie = mysqli_query($conexion, "SELECT codigo_cie FROM patologias WHERE Id_patologia = $id_patologia_dx LIMIT 1");
        if ($r_cie && $row_cie = mysqli_fetch_assoc($r_cie)) {
            $codigo_cie_dx = $row_cie['codigo_cie'] ?? '';
        }
    }

    /* ══════════════════════════════════════════════════════════════
       3) INSERT CONSULTA (tabla base)
       ══════════════════════════════════════════════════════════════ */
    $sql_consulta = "INSERT INTO consulta
        (Id_paciente, Id_medico, Id_historial, fecha_consulta, motivo_consulta, tratamiento_indicado, estatus)
        VALUES ($id_persona, $id_medico, $id_historial, '$fecha_consulta', '$motivo_consulta', '$tratamiento_indi', 1)";

    if (!mysqli_query($conexion, $sql_consulta)) {
        throw new Exception("Error al insertar la consulta: " . mysqli_error($conexion));
    }
    $id_consulta = (int)mysqli_insert_id($conexion);

    /* ══════════════════════════════════════════════════════════════
       3.1) SIGNOS VITALES (solo si hay al menos uno)
       ══════════════════════════════════════════════════════════════ */
    $hay_signos = ($peso !== null || $talla !== null || $temperatura !== null ||
                   $presion_sistolica !== null || $presion_diastolica !== null ||
                   $frecuencia_card !== null || $frecuencia_resp !== null || $saturacion !== null);

    if ($hay_signos) {
        $peso_sql        = $peso               === null ? 'NULL' : (float)$peso;
        $talla_sql       = $talla              === null ? 'NULL' : (float)$talla;
        $temp_sql        = $temperatura        === null ? 'NULL' : (float)$temperatura;
        $psis_sql        = $presion_sistolica  === null ? 'NULL' : (int)$presion_sistolica;
        $pdia_sql        = $presion_diastolica === null ? 'NULL' : (int)$presion_diastolica;
        $fc_sql          = $frecuencia_card    === null ? 'NULL' : (int)$frecuencia_card;
        $fr_sql          = $frecuencia_resp    === null ? 'NULL' : (int)$frecuencia_resp;
        $sat_sql         = $saturacion         === null ? 'NULL' : (int)$saturacion;

        $sql_sv = "INSERT INTO signos_vitales_consulta
            (id_consulta, peso, talla, temperatura, presion_sistolica, presion_diastolica,
             frecuencia_cardiaca, frecuencia_respiratoria, saturacion)
            VALUES ($id_consulta, $peso_sql, $talla_sql, $temp_sql, $psis_sql, $pdia_sql,
                    $fc_sql, $fr_sql, $sat_sql)";
        if (!mysqli_query($conexion, $sql_sv)) {
            throw new Exception("Error al guardar signos vitales: " . mysqli_error($conexion));
        }
    }

    /* ══════════════════════════════════════════════════════════════
       3.2) DIAGNÓSTICO
       ══════════════════════════════════════════════════════════════ */
    $id_patologia_sql = $id_patologia_dx > 0 ? (int)$id_patologia_dx : 'NULL';
    $cie_sql          = $codigo_cie_dx !== '' ? "'$codigo_cie_dx'" : 'NULL';
    $diag_libre_sql   = $diagnostico_txt !== '' ? "'$diagnostico_txt'" : 'NULL';

    $sql_diag = "INSERT INTO diagnostico_consulta
        (id_consulta, id_patologia, descripcion_libre, tipo, codigo_cie)
        VALUES ($id_consulta, $id_patologia_sql, $diag_libre_sql, 'Principal', $cie_sql)";
    if (!mysqli_query($conexion, $sql_diag)) {
        throw new Exception("Error al guardar el diagnóstico: " . mysqli_error($conexion));
    }

    /* ══════════════════════════════════════════════════════════════
       3.3) EVOLUCIÓN + LECTURA DE EXÁMENES
       ══════════════════════════════════════════════════════════════ */
    $estado_sql  = $estado_paciente !== ''      ? "'$estado_paciente'"     : 'NULL';
    $evol_sql    = $evolucion_resultado !== ''  ? "'$evolucion_resultado'" : 'NULL';
    $lectura_sql = $lectura_examenes !== ''     ? "'$lectura_examenes'"    : 'NULL';

    $sql_evo = "INSERT INTO evolucion_consulta
        (id_consulta, estado_paciente, evolucion_resultado, lectura_examenes)
        VALUES ($id_consulta, $estado_sql, $evol_sql, $lectura_sql)";
    if (!mysqli_query($conexion, $sql_evo)) {
        throw new Exception("Error al guardar la evolución: " . mysqli_error($conexion));
    }

    /* ══════════════════════════════════════════════════════════════
       3.4) REACCIÓN ADVERSA (solo si es SI)
       ══════════════════════════════════════════════════════════════ */
    if ($reaccion_adversa === 'SI') {
        $detalle_rac_sql = $detalle_reaccion !== '' ? "'$detalle_reaccion'" : 'NULL';
        $sql_rac = "INSERT INTO reaccion_adversa_consulta
            (id_consulta, tiene_reaccion, detalle)
            VALUES ($id_consulta, 'SI', $detalle_rac_sql)";
        if (!mysqli_query($conexion, $sql_rac)) {
            throw new Exception("Error al guardar reacción adversa: " . mysqli_error($conexion));
        }
    }

    /* ══════════════════════════════════════════════════════════════
       3.5) EXÁMENES SOLICITADOS
       ══════════════════════════════════════════════════════════════ */
    if (!empty($examenes_json)) {
        $sql_sol_ex = "INSERT INTO solicitud_examen
            (id_consulta, id_medico, estado, estatus)
            VALUES ($id_consulta, $id_medico, 'Pendiente', 1)";
        if (!mysqli_query($conexion, $sql_sol_ex)) {
            throw new Exception("Error al crear solicitud de exámenes: " . mysqli_error($conexion));
        }
        $id_sol_ex = (int)mysqli_insert_id($conexion);

        foreach ($examenes_json as $ex) {
            $id_ex       = (int)($ex['id'] ?? 0);
            $prioridad   = ($ex['prioridad'] ?? 'Rutina') === 'Urgente' ? 'Urgente' : 'Rutina';
            $indic_ex    = sanitizar($conexion, $ex['indicaciones'] ?? '');
            if ($id_ex <= 0) continue;

            $sql_det_ex = "INSERT INTO detalle_solicitud_examen
                (id_solicitud_examen, id_examen, prioridad, indicaciones, estado_item)
                VALUES ($id_sol_ex, $id_ex, '$prioridad', '$indic_ex', 'Pendiente')";
            if (!mysqli_query($conexion, $sql_det_ex)) {
                throw new Exception("Error al guardar examen $id_ex: " . mysqli_error($conexion));
            }
        }
    }

    /* ══════════════════════════════════════════════════════════════
       3.6) MEDICAMENTOS (SOLICITUD + DETALLE)
       ══════════════════════════════════════════════════════════════ */
    if (!empty($medicamentos_json)) {

        $id_receptor_sql = 'NULL';

        if ($tipo_receptor === 'Paciente') {
            $id_receptor_sql = (string)$id_persona;
        } elseif ($id_representante_post > 0) {
            $id_receptor_sql = (string)$id_representante_post;
        } elseif ($cedula_receptor !== '') {
            $ced_limpia = normalizar_cedula($cedula_receptor);
            if ($ced_limpia !== '') {
                $r_rec = mysqli_query($conexion, "SELECT id FROM persona WHERE cedula = '$ced_limpia' LIMIT 1");
                if ($r_rec && $row_rec = mysqli_fetch_assoc($r_rec)) {
                    $id_receptor_sql = (string)(int)$row_rec['id'];
                }
            }
        } elseif (in_array($tipo_receptor, ['Representante', 'Familiar'], true)) {
            $r_rep = mysqli_query($conexion,
                "SELECT Id_representante FROM historial_representantes
                 WHERE Id_paciente = $id_persona AND estatus = 1
                 ORDER BY (tipo_representante = 'Legal') DESC, Id ASC LIMIT 1");
            if ($r_rep && $row_rep = mysqli_fetch_assoc($r_rep)) {
                $id_receptor_sql = (string)(int)$row_rep['Id_representante'];
            }
        }

        $entregado_sql    = $responsable !== '' ? "'" . substr($responsable, 0, 100) . "'" : 'NULL';
        $cedula_rec_sql   = $cedula_receptor !== '' ? "'$cedula_receptor'" : 'NULL';
        $parentesco_sql   = $parentesco_represent !== '' ? "'$parentesco_represent'" : 'NULL';

        $sql_sol_med = "INSERT INTO solicitud_medicamento
            (origen, id_consulta, id_paciente, id_medico, id_receptor, tipo_receptor,
             entregado_a, cedula_receptor, parentesco_receptor, estatus_general)
            VALUES ('Interno', $id_consulta, $id_persona, $id_medico, $id_receptor_sql,
                    '$tipo_receptor', $entregado_sql, $cedula_rec_sql, $parentesco_sql, 'Pendiente')";

        if (!mysqli_query($conexion, $sql_sol_med)) {
            throw new Exception("Error al crear solicitud de medicamentos: " . mysqli_error($conexion));
        }
        $id_solicitud = (int)mysqli_insert_id($conexion);

        $motivo_item_sql = $motivo_consulta !== '' ? "'$motivo_consulta'" : 'NULL';

        foreach ($medicamentos_json as $med) {
            $id_desc_med  = (int)($med['id'] ?? 0);
            $cantidad     = max(1, (int)($med['cantidad'] ?? 1));
            $dosis        = sanitizar($conexion, $med['dosis'] ?? '');
            $frecuencia   = sanitizar($conexion, $med['frecuencia'] ?? '');
            $via          = sanitizar($conexion, $med['via'] ?? 'Oral');
            $duracion     = sanitizar($conexion, $med['duracion'] ?? '');
            $indic_med    = sanitizar($conexion, $med['indicaciones'] ?? '');

            if ($id_desc_med <= 0) continue;

            $vias_validas = ['Oral','Intravenosa','Intramuscular','Subcutanea','Topica',
                             'Oftalmica','Otica','Nasal','Rectal','Inhalatoria','Otra'];
            if (!in_array($via, $vias_validas, true)) $via = 'Oral';

            $dosis_sql    = $dosis     !== '' ? "'$dosis'"     : 'NULL';
            $frec_sql     = $frecuencia!== '' ? "'$frecuencia'" : 'NULL';
            $duracion_sql = $duracion  !== '' ? "'$duracion'"   : 'NULL';
            $indic_sql    = $indic_med !== '' ? "'$indic_med'"  : 'NULL';

            $sql_det_med = "INSERT INTO detalle_solicitud
                (id_solicitud, id_descripcion_medicamento, cantidad_recetada, dosis, frecuencia,
                 via, duracion, indicaciones, cantidad_entregada, estatus_item, motivo)
                VALUES ($id_solicitud, $id_desc_med, $cantidad, $dosis_sql, $frec_sql,
                        '$via', $duracion_sql, $indic_sql, 0, 'Pendiente', $motivo_item_sql)";

            if (!mysqli_query($conexion, $sql_det_med)) {
                throw new Exception("Error al guardar medicamento $id_desc_med: " . mysqli_error($conexion));
            }
        }
    }

    /* ══════════════════════════════════════════════════════════════
       3.7) ANTECEDENTES (JSON unificado en historial_medico)
       ══════════════════════════════════════════════════════════════ */
    if ($antecedentes_json !== '' && $antecedentes_json !== '{}') {
        $safe_json = mysqli_real_escape_string($conexion, $antecedentes_json);
        $sql_ant = "UPDATE historial_medico
                    SET antecedentes_json = '$safe_json'
                    WHERE id_historial = $id_historial";
        if (!mysqli_query($conexion, $sql_ant)) {
            throw new Exception("Error al guardar antecedentes: " . mysqli_error($conexion));
        }
    }

    /* ══════════════════════════════════════════════════════════════
       3.8) NOTAS ADICIONALES
       ══════════════════════════════════════════════════════════════ */
    if ($notas_adicionales !== '') {
        $sql_notas = "INSERT INTO observaciones_historial_medico
            (Id_historial_medico, Id_medico, observacion, fecha)
            VALUES ($id_historial, $id_medico, '$notas_adicionales', NOW())";
        if (!mysqli_query($conexion, $sql_notas)) {
            throw new Exception("Error al guardar nota adicional: " . mysqli_error($conexion));
        }
    }

    /* ══════════════════════════════════════════════════════════════
       3.9) CITA DE SEGUIMIENTO (NO rompe la consulta si falla)
       ══════════════════════════════════════════════════════════════ */
    if (!empty($fecha_cita) && !empty($hora_cita)) {
        $sql_med_esp = "SELECT dm.Id_detalle_medico, em.Id_especialidad
                        FROM detalle_medico dm
                        INNER JOIN especialidades_medicos em ON dm.Id_detalle_medico = em.Id_detalle_medico
                        WHERE dm.Id_detalle_medico = $id_medico
                        LIMIT 1";
        $res_me  = mysqli_query($conexion, $sql_med_esp);
        $fila_me = $res_me ? mysqli_fetch_assoc($res_me) : null;

        if ($fila_me) {
            $id_esp = (int)$fila_me['Id_especialidad'];
            $motivo_cita_sql = $motivo_consulta !== '' ? "'$motivo_consulta'" : 'NULL';

            $sql_cita = "INSERT INTO citas
                (fecha_cita, hora_cita, motivo, estatus, estado, Id_paciente, Id_medico, Id_especialidad)
                VALUES ('$fecha_cita', '$hora_cita', $motivo_cita_sql, 1, 'Pendiente',
                        $id_persona, $id_medico, $id_esp)";
            if (!mysqli_query($conexion, $sql_cita)) {
                error_log("No se agendó cita de seguimiento: " . mysqli_error($conexion));
                $_SESSION['warning_cita'] = 'La cita de seguimiento no se agendó: ' . mysqli_error($conexion);
            }
        } else {
            error_log("No se agendó cita de seguimiento: médico $id_medico sin especialidad.");
            $_SESSION['warning_cita'] = 'La cita de seguimiento no se agendó: el médico no tiene especialidad asignada.';
        }
    }

    /* ══════════════════════════════════════════════════════════════
       3.10) MARCAR CITA ATENDIDA COMO FINALIZADA
       ══════════════════════════════════════════════════════════════ */
    if ($id_cita_atendida > 0) {
        $sql_upd_cita = "UPDATE citas SET estado = 'Finalizada' WHERE id_cita = $id_cita_atendida";
        if (!mysqli_query($conexion, $sql_upd_cita)) {
            throw new Exception("Error al actualizar la cita atendida: " . mysqli_error($conexion));
        }
    }

    /* ══════════════════════════════════════════════════════════════
       4) COMMIT
       ══════════════════════════════════════════════════════════════ */
    mysqli_commit($conexion);

    $msg = '✅ La consulta fue registrada correctamente.';
    if (!empty($_SESSION['warning_cita'])) {
        $msg .= ' ⚠️ ' . $_SESSION['warning_cita'];
        unset($_SESSION['warning_cita']);
    }
    $_SESSION['mensaje_user_exito'] = $msg;

    header("Location: ../pdf/receta_pdf.php?id_consulta=$id_consulta");
    exit();

} catch (Exception $e) {
    mysqli_rollback($conexion);
    error_log("⚠️ Error al agregar la consulta: " . $e->getMessage());
    $_SESSION['mensaje_user_error'] = '⚠️ No se pudo registrar la consulta. Detalle: ' . $e->getMessage();
    header("Location: ../../pages/php/consulta_listado.php");
    exit();
}