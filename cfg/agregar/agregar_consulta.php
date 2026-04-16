<?php
session_start();
include("../conexion.php"); // Asegúrate de que esta ruta es correcta.

// Validación de seguridad básica
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($conexion)) {
    die("<h3 style='color:red'>Error: Acceso denegado o falta conexión.</h3>");
}

// ==========================================================
// 1. FUNCIONES AUXILIARES
// ==========================================================

function sanitizar($conexion, $input) {
    return mysqli_real_escape_string($conexion, trim($input ?? ''));
}

function handle_error($conexion, $ubicacion, $sql, $mensaje_extra = '') {
    if ($conexion) mysqli_rollback($conexion);
    echo "<div style='background:#f8d7da; padding:20px; border:1px solid #f5c6cb; border-radius:5px;'>";
    echo "<h2 style='color:#721c24'>❌ Error en: $ubicacion</h2>";
    echo "<p><strong>Mensaje:</strong> " . mysqli_error($conexion) . " $mensaje_extra</p>";
    echo "<pre style='background:#eee; padding:10px;'>$sql</pre>";
    echo "<br><button onclick='window.history.back()'>Volver</button>";
    echo "</div>";
    exit();
}

/**
 * Función inteligente para antecedentes:
 * Solo guarda si NO existen registros previos para este historial (REGLA: una sola vez).
 */
function verificar_y_guardar_antecedente($conexion, $id_historial, $texto_antecedente, $tabla_catalogo, $tabla_relacion, $campo_fk_catalogo = 'Id_antecedente') {
    if (empty($texto_antecedente) || $texto_antecedente === 'N/A o no aplica') return;

    $sql_check = "SELECT count(*) as total FROM $tabla_relacion WHERE Id_Historial = $id_historial AND estatus = '1'";
    $check_res = mysqli_query($conexion, $sql_check);
    
    if (!$check_res) {
        handle_error($conexion, "Verificación Antecedente", $sql_check, "Error al buscar historial previo.");
    }
    
    $row = mysqli_fetch_assoc($check_res);

    if ($row['total'] > 0) {
        return; 
    }

    $sql_cat = "INSERT INTO $tabla_catalogo (descripcion, estatus) VALUES ('$texto_antecedente', '1')";
    if (!mysqli_query($conexion, $sql_cat)) {
        handle_error($conexion, "Insertar Catálogo $tabla_catalogo", $sql_cat);
    }
    $id_nuevo_item = mysqli_insert_id($conexion);

    $sql_rel = "INSERT INTO $tabla_relacion ($campo_fk_catalogo, Id_Historial, estatus) VALUES ($id_nuevo_item, $id_historial, '1')";
    if (!mysqli_query($conexion, $sql_rel)) {
        handle_error($conexion, "Relacionar Antecedente $tabla_relacion", $sql_rel);
    }
}

// ==========================================================
// 2. RECEPCIÓN Y SANITIZACIÓN DE DATOS
// (CÓDIGO OMITIDO POR SER EL MISMO QUE YA FUNCIONA)
// ==========================================================
// Datos principales
$cedula_paciente = sanitizar($conexion, $_POST['cedula_paciente']);
$id_medico       = (int)$_POST['medico'];
$fecha_consulta  = sanitizar($conexion, $_POST['fecha_consulta']);
$motivo_consulta = sanitizar($conexion, $_POST['motivo_consulta']);
$diagnostico     = sanitizar($conexion, $_POST['diagnostico_text']);
$indicaciones    = sanitizar($conexion, $_POST['indicaciones']);
$notas_adicionales = sanitizar($conexion, $_POST['notas_adicionales']);

// Signos vitales (Manejo de NULL si están vacíos)
$peso          = !empty($_POST['peso']) ? "'" . (float)$_POST['peso'] . "'" : "NULL";
$talla         = !empty($_POST['talla']) ? "'" . (float)$_POST['talla'] . "'" : "NULL";
$temperatura   = !empty($_POST['temperatura']) ? "'" . (float)$_POST['temperatura'] . "'" : "NULL";
$tension       = !empty($_POST['tension']) ? "'" . sanitizar($conexion, $_POST['tension']) . "'" : "NULL"; 
$frec_cardiaca = !empty($_POST['frecuencia_cardiaca']) ? (int)$_POST['frecuencia_cardiaca'] : "NULL";
$saturacion    = !empty($_POST['saturacion']) ? (int)$_POST['saturacion'] : "NULL";
$frec_resp     = !empty($_POST['frecuencia_respiratoria']) ? (int)$_POST['frecuencia_respiratoria'] : "NULL";
$fecha_cita  = !empty($_POST['fecha_cita']) ? sanitizar($conexion, $_POST['fecha_cita']) : "NULL";
$hora_cita  = !empty($_POST['hora_cita']) ? sanitizar($conexion, $_POST['hora_cita']) : "NULL";

// Antecedentes (Textos)
$perinatales   = $_POST['perinatales'];
$familiares    = $_POST['familiares'];
$sexualidad    = $_POST['sexualidad_reproductivos'];
$estilo_vida   = $_POST['estilo_vida'];

$estado_paciente   = mysqli_real_escape_string($conexion, $_POST['estado_paciente']);
$reaccion_adversa    = mysqli_real_escape_string($conexion, $_POST['reaccion_adversa']);
$detalle_reaccion    = mysqli_real_escape_string($conexion, $_POST['detalle_reaccion']);
$evolucion_resultado   = mysqli_real_escape_string($conexion, $_POST['evolucion_resultado']);

$lectura_examenes   = mysqli_real_escape_string($conexion, $_POST['lectura_examenes']);
$examenes_solicitados   = mysqli_real_escape_string($conexion, $_POST['examenes_solicitados']);

$entregado_a   = mysqli_real_escape_string($conexion, $_POST['entregado_a']);
$parentesco_representante   = mysqli_real_escape_string($conexion, $_POST['parentesco_representante']);

$id_cita_atendida = $_POST['id_cita_atendida'] ?? '';

// ==========================================================
// 3. LÓGICA DE TRANSACCIÓN (GUARDADO)
// ==========================================================
mysqli_autocommit($conexion, false);

// A. OBTENER IDs DEL PACIENTE (Persona e Historial)
$sql_ids = "SELECT p.id AS id_persona, hm.id_historial 
            FROM persona p 
            JOIN historial_medico hm ON p.id = hm.Id_persona 
            WHERE p.cedula = '$cedula_paciente' LIMIT 1";

$res_ids = mysqli_query($conexion, $sql_ids);
if (!$res_ids || mysqli_num_rows($res_ids) === 0) {
    handle_error($conexion, "Buscar Paciente", $sql_ids, "Paciente no encontrado o sin historial médico inicializado.");
}
$datos = mysqli_fetch_assoc($res_ids);
$id_persona   = $datos['id_persona'];
$id_historial = $datos['id_historial'];

// B. INSERTAR CONSULTA
$sql_consulta = "INSERT INTO consulta (
    Id_paciente, Id_medico, Id_historial, fecha_consulta, motivo_consulta, 
    diagnostico, tratamiento_indicado, 
    peso, talla, temperatura, tension, frecuencia_cardiaca, saturacion, frecuencia_respiratoria, estado_paciente, reaccion_adversa, 
    detalle_reaccion, evolucion_resultado, lectura_examenes, examenes_solicitados, entregado_a, parentesco
) VALUES (
    '$id_persona', '$id_medico', '$id_historial', '$fecha_consulta', '$motivo_consulta',
    '$diagnostico', '$indicaciones',
    '$peso', '$talla', '$temperatura', '$tension', '$frec_cardiaca', '$saturacion', '$frec_resp', '$estado_paciente', '$reaccion_adversa', 
    '$detalle_reaccion', '$evolucion_resultado', '$lectura_examenes', '$examenes_solicitados', '$entregado_a', '$parentesco_representante'
)";

if (!mysqli_query($conexion, $sql_consulta)) {
    handle_error($conexion, "Insertar Consulta", $sql_consulta);
}
$id_consulta = mysqli_insert_id($conexion); // <<-- ID DE LA CONSULTA CREADA


if ($id_consulta) {
    // 1. Obtener ID del Paciente (Persona)
    $sql_get_paciente = "SELECT Id_persona FROM historial_medico WHERE Id_historial = '$id_historial' LIMIT 1";
    $res_p = mysqli_query($conexion, $sql_get_paciente);
    $fila_p = mysqli_fetch_assoc($res_p);
    $id_paciente_real = $fila_p['Id_persona'];

    // 2. Obtener el ID de detalle_medico y su Especialidad asignada
    // Hacemos un JOIN entre detalle_medico y especialidades_medicos
    $sql_med_esp = "SELECT dm.Id_detalle_medico, em.Id_especialidad 
                    FROM detalle_medico dm
                    INNER JOIN especialidades_medicos em ON dm.Id_detalle_medico = em.Id_detalle_medico
                    WHERE dm.Id_persona = '$id_medico' 
                    LIMIT 1"; // Traemos la primera especialidad encontrada
    
    $res_med_esp = mysqli_query($conexion, $sql_med_esp);
    $fila_me = mysqli_fetch_assoc($res_med_esp);

    if ($fila_me) {
        $id_detalle_medico_real = $fila_me['Id_detalle_medico'];
        $id_especialidad_real   = $fila_me['Id_especialidad'];

        // 3. Insertar la Cita con todos los IDs requeridos
        if (!empty($fecha_cita) && !empty($hora_cita)) {
            $sql_create_cita = "INSERT INTO citas (
                fecha_cita, 
                hora_cita, 
                motivo, 
                estatus, 
                Id_paciente, 
                Id_medico, 
                Id_especialidad, 
                fecha_registro
            ) VALUES (
                '$fecha_cita', 
                '$hora_cita', 
                '$motivo_consulta', 
                'Pendiente', 
                '$id_paciente_real', 
                '$id_detalle_medico_real', 
                '$id_especialidad_real', 
                NOW()
            )";
            
            if (!mysqli_query($conexion, $sql_create_cita)) {
                handle_error($conexion, "Error al insertar cita (Especialidad)", $sql_create_cita);
            }
        }
    } else {
        // Este error saldrá si el médico no tiene ninguna especialidad configurada en la tabla intermedia
        handle_error($conexion, "Médico sin especialidad", "Persona ID: $id_medico", "No se encontró una especialidad vinculada a este médico en 'especialidades_medicos'.");
    }
}

// C. GUARDAR ANTECEDENTES (SOLO UNA VEZ)
verificar_y_guardar_antecedente($conexion, $id_historial, $perinatales, 'antecedentes_perinatales', 'historial_antecedentes_perinatales');
verificar_y_guardar_antecedente($conexion, $id_historial, $familiares, 'antecedentes_familiares', 'historial_antecedentes_familiares');
verificar_y_guardar_antecedente($conexion, $id_historial, $sexualidad, 'antecedentes_sexuales_reproductivos', 'historial_antecedentes_sexuales_reproductivos');
verificar_y_guardar_antecedente($conexion, $id_historial, $estilo_vida, 'tipos_estilos_de_vida', 'estilos_de_vida_paciente', 'Id_tipo');

/// ==========================================================
// SECCIÓN DE PRESCRIPCIÓN DE MEDICAMENTOS
// ==========================================================

// 1. Recibimos el dato
// ==========================================================
// SECCIÓN DE PRESCRIPCIÓN DE MEDICAMENTOS Y SOLICITUD A FARMACIA
// ==========================================================

// 1. Recibimos el dato
$medicamento_input = $_POST['medicamento_full_data'] ?? '';

// 2. Verificamos que no esté vacío
if (!empty($medicamento_input)) {

    // Intentamos decodificar por si acaso viene como JSON puro desde el frontend
    $medicamentos = json_decode($medicamento_input, true);

    // Si NO es un JSON (es decir, es una cadena como "5, 12, 8")
    if (!is_array($medicamentos)) {
        $medicamentos = [];
        // Separamos la cadena de texto usando la coma como delimitador
        $ids_separados = explode(',', $medicamento_input);

        // Creamos el arreglo con la estructura que espera el ciclo foreach
        foreach ($ids_separados as $id_val) {
            $id_limpio = trim($id_val); // Quitamos espacios en blanco accidentales
            if (!empty($id_limpio)) {
                $medicamentos[] = ['id' => $id_limpio];
            }
        }
    }

    // 3. Si hay medicamentos, creamos la Solicitud General (Farmacia) UNA sola vez
    if (count($medicamentos) > 0) {
        // Insertamos la solicitud. Al ser de una consulta, el origen es 'Interno'.
        // Llenamos los campos de externos vacíos/0 por defecto.
        $sql_solicitud = "INSERT INTO solicitud_medicamento 
                          (origen, id_consulta, tipo_cedula_externo, cedula_externo, estatus_general, fecha_solicitud) 
                          VALUES ('Interno', $id_consulta, '', 0, 'Pendiente', NOW())";

        if (!mysqli_query($conexion, $sql_solicitud)) {
            handle_error($conexion, "Error al crear solicitud de farmacia", $sql_solicitud);
        }

        $id_solicitud = mysqli_insert_id($conexion); // Obtenemos el ID de la solicitud recién creada

        // 4. Ahora recorremos el array para registrar cada medicamento en ambas partes
        foreach ($medicamentos as $med) {
            $id_desc_med = isset($med['id']) ? (int)$med['id'] : 0;

            // ⚠️ OJO: Aquí capturamos la cantidad si viene en el JSON, sino ponemos 1 por defecto.
            $cantidad_recetada = isset($med['cantidad']) ? (int)$med['cantidad'] : 1;

            if ($id_desc_med > 0) {
                // A. Registrar en el Historial Médico (Prescripción)
                $sql_presc = "INSERT INTO prescripcion_medicamentos 
                              (Id_consulta, Id_descripcion_medicamento, estado_prescripcion) 
                              VALUES ($id_consulta, $id_desc_med, 'pendiente')";

                if (!mysqli_query($conexion, $sql_presc)) {
                    handle_error($conexion, "Error al insertar medicamento en prescripción ID: $id_desc_med", $sql_presc);
                }

                // B. Registrar en el Detalle de la Solicitud (Para la Farmacia)
                $sql_detalle_solicitud = "INSERT INTO detalle_solicitud 
                                          (id_solicitud, id_medicamento, cantidad_recetada, cantidad_entregada, estatus_item) 
                                          VALUES ($id_solicitud, $id_desc_med, $cantidad_recetada, 0, 'Pendiente')";

                if (!mysqli_query($conexion, $sql_detalle_solicitud)) {
                    handle_error($conexion, "Error al insertar detalle de solicitud farmacia ID: $id_desc_med", $sql_detalle_solicitud);
                }
            }
        }
    }
}

// E. GUARDAR OBSERVACIONES AL HISTORIAL
if (!empty($notas_adicionales)) {
    $sql_notas = "INSERT INTO observaciones_historial_medico (Id_historial_medico, Id_medico, observacion, fecha) 
                  VALUES ($id_historial, $id_medico, '$notas_adicionales', NOW())";
    if (!mysqli_query($conexion, $sql_notas)) {
        // La inserción de notas no es crítica.
    }
}


if (!empty($id_cita_atendida)) {
    $id_cita_atendida = sanitizar($conexion, $id_cita_atendida);
    $sql_update_cita = "UPDATE citas SET estatus = 'Finalizada' WHERE Id_cita = '$id_cita_atendida'";
    
    if (!mysqli_query($conexion, $sql_update_cita)) {
        // Si falla la actualización de la cita, cancelamos todo para mantener la integridad
        handle_error($conexion, "Error al actualizar el estado de la cita", $sql_update_cita);
    }
}

// ==========================================================
// 4. FINALIZAR TRANSACCIÓN Y REDIRECCIÓN
// ==========================================================
if (mysqli_commit($conexion)) {
    // ⚠ ESTE ES EL PASO CLAVE: Redirigimos al script intermedio que maneja el PDF y la redirección final.
    $_SESSION['mensaje_user_exito'] = '✅ Éxito: La consulta fue agregada correctamente.';
    header("Location: ../pdf/receta_pdf.php?id_consulta=$id_consulta");
    exit();
} else {
    handle_error($conexion, "Commit Final", "No se pudo finalizar la transacción.");
    error_log("Error de transacción al agregar la consulta: " . $e->getMessage()); 
    $_SESSION['mensaje_user_error'] = '❌ Error de Registro: No se pudo registrar la consulta. Detalle: ' . $e->getMessage();
    header("Location: ../../pages/php/consulta_listado.php");
}
?>