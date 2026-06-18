<?php
// get/get_historial_ajax.php

// 1. INCLUIR CONEXIÓN A LA BASE DE DATOS
// ** ASEGÚRATE DE QUE ESTA RUTA SEA CORRECTA **
include("../../../cfg/conexion.php"); 

header('Content-Type: application/json');
$output = [
    'success' => false, 
    'error' => '', 
    'data' => [
        'id_historial' => [],
        'alergias' => [],
        'patologias' => [],
        'historial_consultas' => [],
        'antecedentes' => [
            'perinatales' => '',
            'familiares' => '',
            'sexualidad_reproductivos' => '',
            'estilo_vida' => '',
            'notas_adicionales' => ''
        ],
    ]
];

$cedula = $_GET['cedula'] ?? ''; 

if (empty($cedula) || !isset($conexion)) {
    $output['error'] = 'Cédula no proporcionada o error de conexión.';
    echo json_encode($output);
    exit;
}

try {
    $safe_cedula = $conexion->real_escape_string($cedula);

    // ------------------------------------------------------------------
    // A. OBTENER ID_PERSONA Y ID_HISTORIAL
    // ------------------------------------------------------------------
    $sql_ids = "
        SELECT 
            p.id AS id_persona, 
            hm.id_historial 
        FROM persona p
        JOIN historial_medico hm ON p.id = hm.Id_persona
        WHERE p.cedula = '$safe_cedula'
    ";
    $result_ids = $conexion->query($sql_ids);
    $ids = $result_ids ? $result_ids->fetch_assoc() : null;
    if ($result_ids) $result_ids->free();

    if (!$ids) {
        // Si el paciente existe, pero no tiene historial médico creado
        $output['error'] = "Paciente encontrado, pero no tiene un Historial Médico creado.";
        $output['success'] = true; // Lo marcamos como true para que el front no muestre un error grave, sino un aviso
        echo json_encode($output);
        exit;
    }

    $id_persona = $ids['id_persona'];
    $id_historial = $ids['id_historial'];
    $output['id_historial'] = $id_historial;
    
    // ------------------------------------------------------------------
    // B. OBTENER ALERGIAS
    // ------------------------------------------------------------------
    $sql_alergias = "
        SELECT ac.nombre_alergia
        FROM historial_alergias ha
        JOIN alergias_conocidas ac ON ha.Id_alergia = ac.Id_alergias_conocidas
        WHERE ha.Id_historial = $id_historial
    ";
    $result_alergias = $conexion->query($sql_alergias);
    if ($result_alergias) {
        while ($row = $result_alergias->fetch_assoc()) {
            $output['data']['alergias'][] = $row['nombre_alergia'];
        }
        $result_alergias->free();
    }
    
    // ------------------------------------------------------------------
    // C. OBTENER PATOLOGÍAS CRÓNICAS
    // ------------------------------------------------------------------
    $sql_patologias = "
        SELECT p.nombre_patologia
        FROM historial_patologias hp
        JOIN patologias p ON hp.Id_patologia = p.Id_patologia
        WHERE hp.Id_historial = $id_historial
    ";
    $result_patologias = $conexion->query($sql_patologias);
    if ($result_patologias) {
        while ($row = $result_patologias->fetch_assoc()) {
            $output['data']['patologias'][] = $row['nombre_patologia'];
        }
        $result_patologias->free();
    }

    // ------------------------------------------------------------------
    // D. OBTENER LÍNEA DE TIEMPO (ÚLTIMAS 2 CONSULTAS)
    // ------------------------------------------------------------------
    $sql_consultas = "
        SELECT
            c.Id_consulta, 
            c.fecha_consulta, 
            c.motivo_consulta, 
            c.diagnostico,
            c.talla,
            c.peso,
            temperatura,
            tension,
            saturacion,
            frecuencia_cardiaca, 
            frecuencia_respiratoria, 
            c.tratamiento_indicado,
            CONCAT(pm.nombre, ' ', pm.apellido) AS medico_nombre
        FROM consulta c
        JOIN detalle_medico dm ON c.Id_medico = dm.Id_detalle_medico
        JOIN persona pm ON dm.Id_persona = pm.id
        WHERE c.Id_paciente = $id_persona
        ORDER BY c.fecha_consulta DESC
        LIMIT 5 
    ";
    $result_consultas = $conexion->query($sql_consultas);
    if ($result_consultas) {
        while ($row = $result_consultas->fetch_assoc()) {
            $output['data']['historial_consultas'][] = $row;
        }
        $result_consultas->free();
    }

    // ------------------------------------------------------------------
    // E. OBTENER ANTECEDENTES Y OBSERVACIONES (Últimos datos de texto libre)
    // ------------------------------------------------------------------

    // 1. Perinatales
    $sql_perinatal = "SELECT ap.descripcion FROM antecedentes_perinatales ap JOIN historial_antecedentes_perinatales hap ON ap.Id = hap.Id_antecedente WHERE hap.Id_Historial = $id_historial ORDER BY hap.Id DESC LIMIT 1"; 
    $res_perinatal = $conexion->query($sql_perinatal);
    $output['data']['antecedentes']['perinatales'] = $res_perinatal && $res_perinatal->num_rows > 0 ? $res_perinatal->fetch_assoc()['descripcion'] : '';
    if ($res_perinatal) $res_perinatal->free();

    // 2. Familiares
    $sql_familiar = "SELECT af.descripcion FROM antecedentes_familiares af JOIN historial_antecedentes_familiares haf ON af.Id = haf.Id_antecedente WHERE haf.Id_Historial = $id_historial ORDER BY haf.Id DESC LIMIT 1"; 
    $res_familiar = $conexion->query($sql_familiar);
    $output['data']['antecedentes']['familiares'] = $res_familiar && $res_familiar->num_rows > 0 ? $res_familiar->fetch_assoc()['descripcion'] : '';
    if ($res_familiar) $res_familiar->free();

    // 3. Sexuales y Reproductivos
    $sql_reproductivo = "SELECT asr.descripcion FROM antecedentes_sexuales_reproductivos asr JOIN historial_antecedentes_sexuales_reproductivos hasr ON asr.Id = hasr.Id_antecedente WHERE hasr.Id_Historial = $id_historial ORDER BY hasr.Id DESC LIMIT 1"; 
    $res_reproductivo = $conexion->query($sql_reproductivo);
    $output['data']['antecedentes']['sexualidad_reproductivos'] = $res_reproductivo && $res_reproductivo->num_rows > 0 ? $res_reproductivo->fetch_assoc()['descripcion'] : '';
    if ($res_reproductivo) $res_reproductivo->free();
    
    // 4. Estilo de Vida y Notas Adicionales
    $sql_estilo_vida = "SELECT asr.descripcion FROM tipos_estilos_de_vida asr JOIN estilos_de_vida_paciente hasr ON asr.Id = hasr.Id_tipo WHERE hasr.Id_Historial = $id_historial ORDER BY hasr.Id DESC LIMIT 1"; 
    $res_estilo_vida = $conexion->query($sql_estilo_vida);
    $output['data']['antecedentes']['estilo_vida'] = $res_estilo_vida && $res_estilo_vida->num_rows > 0 ? $res_estilo_vida->fetch_assoc()['descripcion'] : '';
    if ($res_estilo_vida) $res_estilo_vida->free();


    // 5. Notas Adicionales (Desde Observaciones)
    $sql_obs = "SELECT observacion FROM observaciones_historial_medico WHERE Id_historial_medico = $id_historial ORDER BY fecha DESC LIMIT 10"; 
    $res_obs = $conexion->query($sql_obs);
    $observaciones = [];
    if ($res_obs) {
        while ($row = $res_obs->fetch_assoc()) {
            $observaciones[] = $row['observacion'];
        }
        $res_obs->free();
    }
    
    if (!empty($notas_raw)) {
        $output['data']['antecedentes']['notas_adicionales'] = implode("\n---\n", array_slice($notas_raw, 0, 3));
    }
    
    $output['success'] = true;

} catch (\mysqli_sql_exception $e) {
    $output['error'] = "Error de base de datos (MySQLi): " . $e->getMessage();
} catch (\Exception $e) {
    $output['error'] = "Error desconocido al procesar datos: " . $e->getMessage();
}

echo json_encode($output);
?>


