<?php
include("../conexion.php");

if (!isset($_POST['busqueda']) || empty(trim($_POST['busqueda']))) {
    echo '<option value="">-- Ingrese cédula o nombre --</option>';
    exit;
}

$busqueda = mysqli_real_escape_string($conexion, $_POST['busqueda']);
$id_medicamento = isset($_POST['id_medicamento']) ? mysqli_real_escape_string($conexion, $_POST['id_medicamento']) : '';
$es_menor = isset($_POST['es_menor']) && ($_POST['es_menor'] == 1 || $_POST['es_menor'] == '1' || $_POST['es_menor'] === 'true');




// Base de la consulta
$sql = "SELECT 
            pm.Id AS id_prescripcion_medicamento,
            pm.dosis,
            c.fecha_consulta,
            per.cedula AS cedula_paciente,
            per.nombre AS nombre_paciente,
            per.apellido AS apellido_paciente,
            TIMESTAMPDIFF(YEAR, per.fecha_nacimiento, CURDATE()) AS edad";

if ($es_menor) {
    // FILTRO ESTRICTO: Solo menores de 18 años
    $sql .= ", rep.nombre AS nombre_rep, rep.apellido AS apellido_rep, rep.cedula AS cedula_rep 
             FROM prescripcion_medicamentos pm
             INNER JOIN consulta c ON pm.Id_consulta = c.Id_consulta
             INNER JOIN persona per ON c.Id_paciente = per.id
             LEFT JOIN detalle_paciente_menor dpm ON per.id = dpm.id_persona
             LEFT JOIN persona rep ON dpm.id_representante = rep.id
             WHERE pm.estatus = 'pendiente' 
             AND TIMESTAMPDIFF(YEAR, per.fecha_nacimiento, CURDATE()) < 18"; 
} else {
    // FILTRO ESTRICTO: Solo mayores o iguales a 18 años (Paciente Interno)
    $sql .= " FROM prescripcion_medicamentos pm
             INNER JOIN consulta c ON pm.Id_consulta = c.Id_consulta
             INNER JOIN persona per ON c.Id_paciente = per.id
             WHERE pm.estatus = 'pendiente'
             AND TIMESTAMPDIFF(YEAR, per.fecha_nacimiento, CURDATE()) >= 18";
}

// Filtro de medicamento
if (!empty($id_medicamento)) {
    $sql .= " AND pm.Id_descripcion_medicamento = '$id_medicamento' ";
}

// Filtro de búsqueda
if ($es_menor) {
    // Si es menor, buscamos por los datos del REPRESENTANTE (alias 'rep')
    $sql .= " AND (rep.cedula LIKE '%$busqueda%' 
               OR rep.nombre LIKE '%$busqueda%' 
               OR rep.apellido LIKE '%$busqueda%') ";
} else {
    // Si es adulto, buscamos por los datos del PACIENTE (alias 'per')
    $sql .= " AND (per.cedula LIKE '%$busqueda%' 
               OR per.nombre LIKE '%$busqueda%' 
               OR per.apellido LIKE '%$busqueda%') ";
}

$sql .= " ORDER BY c.fecha_consulta DESC LIMIT 15";

$resultado = mysqli_query($conexion, $sql);

if ($resultado && mysqli_num_rows($resultado) > 0) {
    echo '<option value="">-- Seleccione la Prescripción --</option>';
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $paciente_completo = "{$fila['nombre_paciente']} {$fila['apellido_paciente']}";
        $rep_completo = isset($fila['nombre_rep']) ? "{$fila['nombre_rep']} {$fila['apellido_rep']}" : "";
        $rep_cedula = $fila['cedula_rep'] ?? "";
    
        echo "<option value='{$fila['id_prescripcion_medicamento']}' 
                data-cantidad='".htmlspecialchars($fila['dosis'])."'
                data-paciente='{$paciente_completo}'
                data-cedula-p='{$fila['cedula_paciente']}'
                data-representante='{$rep_completo}'
                data-cedula-r='{$rep_cedula}'>";
        
        echo "CI: {$fila['cedula_paciente']} | {$paciente_completo} | Edad: {$fila['edad']} años";
        echo "</option>";
    }
} else {
    echo '<option value="">No se encontro ninguna prescripcion</option>';
}
?>