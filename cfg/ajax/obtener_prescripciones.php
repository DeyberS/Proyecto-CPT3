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




// Base de la consulta
$sql = "SELECT 
            pm.Id AS id_prescripcion_medicamento,
            c.fecha_consulta,
            per.tipo_cedula AS tipo_cedula_paciente,
            per.cedula AS cedula_paciente,
            per.nombre AS nombre_paciente,
            per.apellido AS apellido_paciente,
            TIMESTAMPDIFF(YEAR, per.fecha_nacimiento, CURDATE()) AS edad";

if ($es_menor) {
    // FILTRO ESTRICTO: Solo menores de 18 años
    $sql .= ", rep.nombre AS nombre_rep, rep.apellido AS apellido_rep, rep.tipo_cedula AS tipo_cedula_rep, rep.cedula AS cedula_rep 
             FROM prescripcion_medicamentos pm
             INNER JOIN consulta c ON pm.Id_consulta = c.Id_consulta
             INNER JOIN persona per ON c.Id_paciente = per.id
             LEFT JOIN detalle_paciente_menor dpm ON per.id = dpm.id_persona
             LEFT JOIN persona rep ON dpm.id_representante = rep.id
             WHERE pm.estado_prescripcion = 'pendiente' 
             AND TIMESTAMPDIFF(YEAR, per.fecha_nacimiento, CURDATE()) < 18"; 
} else {
    // FILTRO ESTRICTO: Solo mayores o iguales a 18 años (Paciente Interno)
    $sql .= " FROM prescripcion_medicamentos pm
             INNER JOIN consulta c ON pm.Id_consulta = c.Id_consulta
             INNER JOIN persona per ON c.Id_paciente = per.id
             WHERE pm.estado_prescripcion = 'pendiente'
             AND TIMESTAMPDIFF(YEAR, per.fecha_nacimiento, CURDATE()) >= 18";
}

// Filtro de medicamento
if (!empty($id_medicamento)) {
    $sql .= " AND pm.Id_descripcion_medicamento = '$id_medicamento' ";
}

// Filtro de búsqueda
if ($es_menor) {
    if ($metodo === 'cedula') {
        // Solo filtramos por tipo_cedula si el método es cédula
        $sql .= " AND rep.cedula LIKE '%$busqueda%' AND rep.tipo_cedula = '$tipo_cedula'";
    } else {
        $sql .= " AND (rep.nombre LIKE '%$busqueda%' OR rep.apellido LIKE '%$busqueda%')";
    }
} else {
    if ($metodo === 'cedula') {
        // Solo filtramos por tipo_cedula si el método es cédula
        $sql .= " AND per.cedula LIKE '%$busqueda%' AND per.tipo_cedula = '$tipo_cedula'";
    } else {
        $sql .= " AND (per.nombre LIKE '%$busqueda%' OR per.apellido LIKE '%$busqueda%')";
    }
}

$sql .= " ORDER BY c.fecha_consulta DESC LIMIT 15";

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
    
        echo "<option value='{$fila['id_prescripcion_medicamento']}' 
                data-cantidad='".htmlspecialchars($fila['dosis'])."'
                data-paciente='{$paciente_completo}'
                data-tipo-cedula-p='{$tipo_ced_p}'
                data-cedula-p='{$ced_p}'
                data-representante='{$rep_completo}'
                data-tipo-cedula-r='{$tipo_ced_r}'
                data-cedula-r='{$ced_r}'>";
        
        echo "{$tipo_ced_p}-{$fila['cedula_paciente']} - {$paciente_completo} - Edad: {$fila['edad']} años";
        echo "</option>";
    }
} else {
    echo '<option value="">No se encontro ninguna prescripcion</option>';
}
?>