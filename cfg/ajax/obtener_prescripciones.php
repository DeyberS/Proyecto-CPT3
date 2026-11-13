<?php
include("../conexion.php");

if (!isset($_POST['busqueda']) || empty(trim($_POST['busqueda']))) {
    echo '<option value="">-- Ingrese cédula o nombre --</option>';
    exit;
}

$busqueda = mysqli_real_escape_string($conexion, $_POST['busqueda']);
$tipo_cedula = isset($_POST['tipo_cedula']) ? mysqli_real_escape_string($conexion, $_POST['tipo_cedula']) : '';
$id_medicamento = isset($_POST['id_medicamento']) ? mysqli_real_escape_string($conexion, $_POST['id_medicamento']) : '';
$es_menor = isset($_POST['es_menor']) && ($_POST['es_menor'] == 1 || $_POST['es_menor'] == '1' || $_POST['es_menor'] === 'true');
$metodo = isset($_POST['metodo']) ? mysqli_real_escape_string($conexion, $_POST['metodo']) : '';

$sql = "SELECT 
            sm.id_solicitud AS id_prescripcion_medicamento,
            c.fecha_consulta,
            per.tipo_cedula AS tipo_cedula_paciente,
            per.cedula AS cedula_paciente,
            per.nombre AS nombre_paciente,
            per.apellido AS apellido_paciente,
            per.fecha_nacimiento,
            per.genero";

if ($es_menor) {
    $sql .= ", rep.nombre AS nombre_rep, rep.apellido AS apellido_rep, rep.tipo_cedula AS tipo_cedula_rep, rep.cedula AS cedula_rep 
             FROM solicitud_medicamento sm
             INNER JOIN consulta c ON sm.id_consulta = c.Id_consulta
             INNER JOIN detalle_solicitud ds ON sm.id_solicitud = ds.id_solicitud
             INNER JOIN persona per ON sm.id_paciente = per.id
             LEFT JOIN historial_representantes hr ON per.id = hr.Id_paciente AND hr.estatus='1' AND hr.tipo_representante='Legal'
             LEFT JOIN persona rep ON hr.Id_representante = rep.id
             WHERE sm.origen = 'Interno'
             AND sm.estatus_general IN ('Pendiente', 'Parcial')
             AND ds.estatus_item IN ('Pendiente', 'Parcialmente Entregado')
             AND TIMESTAMPDIFF(YEAR, per.fecha_nacimiento, CURDATE()) < 18"; 
} else {
    $sql .= " FROM solicitud_medicamento sm
             INNER JOIN consulta c ON sm.id_consulta = c.Id_consulta
             INNER JOIN detalle_solicitud ds ON sm.id_solicitud = ds.id_solicitud
             INNER JOIN persona per ON sm.id_paciente = per.id
             WHERE sm.origen = 'Interno'
             AND sm.estatus_general IN ('Pendiente', 'Parcial')
             AND ds.estatus_item IN ('Pendiente', 'Parcialmente Entregado')
             AND TIMESTAMPDIFF(YEAR, per.fecha_nacimiento, CURDATE()) >= 18";
}

if (!empty($id_medicamento)) {
    $sql .= " AND ds.id_descripcion_medicamento = '$id_medicamento' ";
}

if ($es_menor) {
    if ($metodo === 'cedula') {
        $sql .= " AND rep.cedula LIKE '%$busqueda%'";
        if (!empty($tipo_cedula)) {
            $sql .= " AND rep.tipo_cedula = '$tipo_cedula'";
        }
    } else {
        $sql .= " AND (rep.nombre LIKE '%$busqueda%' OR rep.apellido LIKE '%$busqueda%')";
    }
} else {
    if ($metodo === 'cedula') {
        $sql .= " AND per.cedula LIKE '%$busqueda%'";
        if (!empty($tipo_cedula)) {
            $sql .= " AND per.tipo_cedula = '$tipo_cedula'";
        }
    } else {
        $sql .= " AND (per.nombre LIKE '%$busqueda%' OR per.apellido LIKE '%$busqueda%')";
    }
}

$sql .= " GROUP BY sm.id_solicitud ORDER BY c.fecha_consulta DESC LIMIT 15";

$resultado = mysqli_query($conexion, $sql);

if ($resultado && mysqli_num_rows($resultado) > 0) {
    echo '<option value="">-- Seleccione una prescripción --</option>';
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $paciente_completo = "{$fila['nombre_paciente']} {$fila['apellido_paciente']}";
        $rep_completo = isset($fila['nombre_rep']) ? "{$fila['nombre_rep']} {$fila['apellido_rep']}" : "";
        $tipo_ced_p = $fila['tipo_cedula_paciente'] ?? '';
        $ced_p      = $fila['cedula_paciente'] ?? '';
        $tipo_ced_r = $fila['tipo_cedula_rep'] ?? '';
        $ced_r      = $fila['cedula_rep'] ?? '';
        $fecha_formateada = date("d-m-Y", strtotime($fila['fecha_consulta']));
        $fecha_nac = new DateTime($fila['fecha_nacimiento']);
        $hoy = new DateTime();
        $diff = $hoy->diff($fecha_nac);
        
        if ($diff->y > 0) {
            $edad_str = $diff->y . " años";
        } elseif ($diff->m > 0) {
            $edad_str = $diff->m . " meses";
        } else {
            $edad_str = $diff->d . " dias de nacido";
        }
        
        $genero_str = isset($fila['genero']) ? $fila['genero'] : 'No especificado';
    
        echo "<option value='{$fila['id_prescripcion_medicamento']}' 
                data-paciente='{$paciente_completo}'
                data-tipo-cedula-p='{$tipo_ced_p}'
                data-cedula-p='{$ced_p}'
                data-edad='{$edad_str}'
                data-genero='{$genero_str}'
                data-representante='{$rep_completo}'
                data-tipo-cedula-r='{$tipo_ced_r}'
                data-cedula-r='{$ced_r}'>";
        
        echo "{$fecha_formateada} - {$paciente_completo}";
        echo "</option>";
    }
} else {
    echo '<option value="">No se encontro ninguna prescripcion</option>';
}
?>