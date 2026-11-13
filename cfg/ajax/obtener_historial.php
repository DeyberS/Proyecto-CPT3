<?php
include("../conexion.php"); 

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
        $output['error'] = "Paciente encontrado, pero no tiene un Historial Médico creado.";
        $output['success'] = true;
        echo json_encode($output);
        exit;
    }

    $id_persona = $ids['id_persona'];
    $id_historial = $ids['id_historial'];
    $output['id_historial'] = $id_historial;

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

    $sql_consultas = "
    SELECT
      c.Id_consulta,
      c.fecha_consulta,
      c.motivo_consulta,
      c.tratamiento_indicado,
      CONCAT(pm.nombre, ' ', pm.apellido) AS medico_nombre,
      (SELECT sv.peso FROM signos_vitales_consulta sv
       WHERE sv.id_consulta = c.Id_consulta
       ORDER BY sv.id_medicion DESC LIMIT 1) AS peso,
      (SELECT sv.talla FROM signos_vitales_consulta sv
       WHERE sv.id_consulta = c.Id_consulta
       ORDER BY sv.id_medicion DESC LIMIT 1) AS talla,
      (SELECT sv.temperatura FROM signos_vitales_consulta sv
       WHERE sv.id_consulta = c.Id_consulta
       ORDER BY sv.id_medicion DESC LIMIT 1) AS temperatura,
      (SELECT sv.presion_sistolica FROM signos_vitales_consulta sv
       WHERE sv.id_consulta = c.Id_consulta
       ORDER BY sv.id_medicion DESC LIMIT 1) AS tension,
      (SELECT sv.saturacion FROM signos_vitales_consulta sv
       WHERE sv.id_consulta = c.Id_consulta
       ORDER BY sv.id_medicion DESC LIMIT 1) AS saturacion,
      (SELECT sv.frecuencia_cardiaca FROM signos_vitales_consulta sv
       WHERE sv.id_consulta = c.Id_consulta
       ORDER BY sv.id_medicion DESC LIMIT 1) AS frecuencia_cardiaca,
      (SELECT sv.frecuencia_respiratoria FROM signos_vitales_consulta sv
       WHERE sv.id_consulta = c.Id_consulta
       ORDER BY sv.id_medicion DESC LIMIT 1) AS frecuencia_respiratoria,
      (SELECT dc.descripcion_libre FROM diagnostico_consulta dc
       WHERE dc.id_consulta = c.Id_consulta AND dc.tipo = 'Principal'
       LIMIT 1) AS diagnostico
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

    $antecedentes_db = [];
    $sql_hist = "SELECT antecedentes_json FROM historial_medico WHERE id_historial = $id_historial LIMIT 1";
    $res_hist = $conexion->query($sql_hist);
    if ($res_hist && $row_hist = $res_hist->fetch_assoc()) {
        $json_data = json_decode($row_hist['antecedentes_json'], true);
        if (is_array($json_data)) {
            $antecedentes_db = $json_data;
        }
    }
    
    // Asignamos el objeto JSON al array de salida
    $output['data']['antecedentes'] = $antecedentes_db;

    $sql_obs = "SELECT observacion FROM observaciones_historial_medico WHERE Id_historial_medico = $id_historial ORDER BY fecha DESC LIMIT 10"; 
    $res_obs = $conexion->query($sql_obs);
    $observaciones = [];
    if ($res_obs) {
        while ($row = $res_obs->fetch_assoc()) {
            $observaciones[] = $row['observacion'];
        }
        $res_obs->free();
    }
    
    if (!empty($observaciones)) {
        $output['data']['antecedentes']['notas_adicionales'] = implode("\n---\n", array_slice($observaciones, 0, 3));
    }
    
    $output['success'] = true;

} catch (\mysqli_sql_exception $e) {
    $output['error'] = "Error de base de datos (MySQLi): " . $e->getMessage();
} catch (\Exception $e) {
    $output['error'] = "Error desconocido al procesar datos: " . $e->getMessage();
}

echo json_encode($output);
?>


