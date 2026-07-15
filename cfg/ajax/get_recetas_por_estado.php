<?php
session_start();
include("../conexion.php");

if (!isset($_SESSION["loggedin"]) || !in_array('Generar Despacho de Inventario', $_SESSION["permisos"])) {
    exit("<div class='alert alert-danger'>No tienes permisos.</div>");
}

$estado_requerido = isset($_GET['estado']) ? $_GET['estado'] : '';

// --- NUEVA VALIDACIÓN PARA HOMOLOGAR AMBOS ESTADOS ---
if (strcasecmp($estado_requerido, 'Completado') == 0 || strcasecmp($estado_requerido, 'Completada') == 0 || strcasecmp($estado_requerido, 'entregado') == 0) {
    $estado_requerido = 'Entregado'; // Forzamos el valor exacto que calcula tu CASE de SQL
}

// A partir de aquí tu código sigue exactamente igual...
$sql_base = "
        SELECT 
            'Interna' AS tipo_receta,
            c.Id_consulta AS id_prescripcion,
            CASE 
                WHEN SUM(CASE WHEN pm.estado_prescripcion = 'cancelado' THEN 1 ELSE 0 END) > 0 THEN 'Cancelado'
                WHEN SUM(CASE WHEN pm.estado_prescripcion = 'entregado' THEN 1 ELSE 0 END) = COUNT(pm.Id) THEN 'Entregado'
                WHEN SUM(CASE WHEN pm.estado_prescripcion = 'entregado' THEN 1 ELSE 0 END) > 0 THEN 'Parcial'
                ELSE 'Pendiente'
            END AS estado_entrega,
            c.fecha_consulta AS fecha_solicitud,
            paciente.nombre AS nom_pac, 
            paciente.apellido AS ape_pac,
            paciente.tipo_cedula AS tipo_cedula_pac, 
            paciente.cedula AS cedula_pac,
            rep.cedula AS cedula_representante,
            medico.nombre AS nom_med,
            medico.apellido AS ape_med,
            GROUP_CONCAT(CONCAT('• ', m.nombre_medicamento) SEPARATOR '<br>') AS nombre_medicamento,
            TIMESTAMPDIFF(YEAR, paciente.fecha_nacimiento, CURDATE()) < 18 AS es_menor
        FROM consulta c
        INNER JOIN prescripcion_medicamentos pm ON c.Id_consulta = pm.Id_consulta
        INNER JOIN persona paciente ON c.Id_paciente = paciente.id
        INNER JOIN detalle_medico dmd ON c.Id_medico = dmd.Id_detalle_medico
        INNER JOIN persona medico ON dmd.Id_persona = medico.id
        INNER JOIN descripcion_medicamento dm ON pm.Id_descripcion_medicamento = dm.Id
        INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
        LEFT JOIN detalle_paciente_menor dpm_menor ON paciente.id = dpm_menor.id_persona
        LEFT JOIN persona rep ON dpm_menor.id_representante = rep.id
        WHERE pm.estatus = 1
        GROUP BY c.Id_consulta

        UNION ALL

        SELECT 
            'Externa' AS tipo_receta,
            sm.id_solicitud AS id_prescripcion,
            sm.estatus_general AS estado_entrega,
            DATE(sm.fecha_solicitud) AS fecha_solicitud,
            paciente.nombre AS nom_pac,
            paciente.apellido AS ape_pac,
            paciente.tipo_cedula AS tipo_cedula_pac,
            paciente.cedula AS cedula_pac,
            rep.cedula AS cedula_representante,
            medico.nombre AS nom_med,
            medico.apellido AS ape_med,
            GROUP_CONCAT(CONCAT('• ', m.nombre_medicamento, ' (Cant: ', ds.cantidad_recetada, ')') SEPARATOR '<br>') AS nombre_medicamento,
            TIMESTAMPDIFF(YEAR, paciente.fecha_nacimiento, CURDATE()) < 18 AS es_menor
        FROM solicitud_medicamento sm
        INNER JOIN detalle_solicitud ds ON sm.id_solicitud = ds.id_solicitud
        INNER JOIN descripcion_medicamento dm ON ds.id_medicamento = dm.Id
        INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
        INNER JOIN persona paciente ON sm.id_paciente = paciente.id
        INNER JOIN detalle_medico dmd ON sm.id_medico = dmd.Id_detalle_medico
        INNER JOIN persona medico ON dmd.Id_persona = medico.id
        LEFT JOIN presentacion p ON dm.Id_presentacion = p.Id_presentacion
        LEFT JOIN (
            SELECT 
                dpm.id_medicamento as id_desc, 
                GROUP_CONCAT(CONCAT(IFNULL(pa.nombre,''), ' ', IFNULL(dpm.cantidad_unidad_medida,''), ' ', IFNULL(um.unidad,'')) SEPARATOR ' + ') AS componentes
            FROM detalle_principio_medicamento dpm
            LEFT JOIN principio_activo pa ON dpm.id_principio_activo = pa.Id_principio_activo
            LEFT JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida
            GROUP BY dpm.id_medicamento
        ) comp_tbl ON dm.Id = comp_tbl.id_desc
        LEFT JOIN detalle_paciente_menor dpm_menor ON paciente.id = dpm_menor.id_persona
        LEFT JOIN persona rep ON dpm_menor.id_representante = rep.id
        WHERE sm.origen = 'Externo'
        GROUP BY sm.id_solicitud
";

// =======================================================
// LÓGICA DE FILTRADOS (Heredada del CRUD principal)
// =======================================================
$busqueda = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : '';
$f_desde = isset($_GET['f_desde']) ? mysqli_real_escape_string($conexion, $_GET['f_desde']) : '';
$f_hasta = isset($_GET['f_hasta']) ? mysqli_real_escape_string($conexion, $_GET['f_hasta']) : '';
$f_tipo_ced = isset($_GET['f_tipo_ced']) ? mysqli_real_escape_string($conexion, $_GET['f_tipo_ced']) : '';
$f_cedula = isset($_GET['f_cedula']) ? mysqli_real_escape_string($conexion, $_GET['f_cedula']) : '';
$f_paciente = isset($_GET['f_paciente']) ? mysqli_real_escape_string($conexion, $_GET['f_paciente']) : '';
$f_doctor = isset($_GET['f_doctor']) ? mysqli_real_escape_string($conexion, $_GET['f_doctor']) : '';
$f_medicamento = isset($_GET['f_medicamento']) ? mysqli_real_escape_string($conexion, $_GET['f_medicamento']) : '';
$f_sexo_pac = isset($_GET['f_sexo_pac']) ? mysqli_real_escape_string($conexion, $_GET['f_sexo_pac']) : '';
$f_sexo_med = isset($_GET['f_sexo_med']) ? mysqli_real_escape_string($conexion, $_GET['f_sexo_med']) : '';
$f_cant_min = isset($_GET['f_cant_min']) ? (int)$_GET['f_cant_min'] : 0;
$f_cant_max = isset($_GET['f_cant_max']) ? (int)$_GET['f_cant_max'] : 0;

// Empezamos determinando el estado principal que requiere el Modal
if ($estado_requerido == 'Cancelado') {
    $donde = " WHERE estado_entrega IN ('Cancelado', 'no entregado', 'No entregado')";
} elseif ($estado_requerido == 'Parcial') {
    $donde = " WHERE estado_entrega IN ('Parcial', 'Parcialmente Entregado')";
} elseif ($estado_requerido == 'Pendiente') {
    $donde = " WHERE estado_entrega IN ('Pendiente', 'pendiente')";
} elseif ($estado_requerido == 'Entregado') {
    $donde = " WHERE estado_entrega IN ('Entregado', 'entregado', 'Completado', 'Completada', 'completado')";
} else {
    $donde = " WHERE estado_entrega = '$estado_requerido'";
}

// Aplicamos los filtros extras si existen
if ($busqueda != '') {
    $donde .= " AND (nom_pac LIKE '%$busqueda%' 
               OR ape_pac LIKE '%$busqueda%' 
               OR CONCAT(nom_pac, ' ', ape_pac) LIKE '%$busqueda%'
               OR cedula_pac LIKE '%$busqueda%' 
               OR nombre_medicamento LIKE '%$busqueda%')";
}
if ($f_desde != '') {
    $donde .= " AND fecha_solicitud >= '$f_desde'";
}
if ($f_hasta != '') {
    $donde .= " AND fecha_solicitud <= '$f_hasta'";
}
if ($f_tipo_ced != '') {
    $donde .= " AND tipo_cedula_pac = '$f_tipo_ced'";
}
if ($f_cedula != '') {
    $donde .= " AND cedula_pac LIKE '%$f_cedula%'";
}
if ($f_paciente != '') {
    $donde .= " AND CONCAT(nom_pac, ' ', ape_pac) LIKE '%$f_paciente%'";
}
if ($f_doctor != '') {
    $donde .= " AND CONCAT(nom_med, ' ', ape_med) LIKE '%$f_doctor%'";
}
if ($f_medicamento != '') {
    $donde .= " AND nombre_medicamento LIKE '%$f_medicamento%'";
}
if ($f_sexo_pac != '') {
    $donde .= " AND genero_pac = '$f_sexo_pac'";
}
if ($f_sexo_med != '') {
    $donde .= " AND genero_med = '$f_sexo_med'";
}
if ($f_cant_min > 0) {
    $donde .= " AND nombre_medicamento REGEXP 'Cant: ([0-9]+)' AND CAST(REGEXP_SUBSTR(nombre_medicamento, '(?<=Cant: )[0-9]+') AS UNSIGNED) >= $f_cant_min";
}
if ($f_cant_max > 0) {
    $donde .= " AND nombre_medicamento REGEXP 'Cant: ([0-9]+)' AND CAST(REGEXP_SUBSTR(nombre_medicamento, '(?<=Cant: )[0-9]+') AS UNSIGNED) <= $f_cant_max";
}

// Armamos el Query Final combinando todo
$query = "SELECT * FROM ($sql_base) AS base_unificada $donde ORDER BY fecha_solicitud DESC";
$resultado = mysqli_query($conexion, $query);

// ... El resto del código de la tabla se mantiene igual ($resultado = mysqli_query...)

echo '<table class="table table-sm table-hover" width="100%" style="font-size: 12px;">';
echo '<thead class="table-dark" style="background-color: #222; color: white;"><tr><th>Fecha</th><th>Paciente</th><th>Médico</th><th>Medicamento</th><th class="text-center">Estado</th><th class="text-center">Acciones</th></tr></thead><tbody>';

if(mysqli_num_rows($resultado) == 0){
    echo '<tr><td colspan="6" class="text-center">No se encontraron recetas en este estado.</td></tr>';
}

while ($row = mysqli_fetch_assoc($resultado)) {
    $etiquetaTipo = ($row['tipo_receta'] === 'Interna') ? (($row['es_menor'] == 1) ? '<span class="label label-info">Interna-Rep.</span>' : '<span class="label label-primary">Interna</span>') : '<span class="label label-warning">Externa</span>';
    
    $span_class="";
    if ($row['estado_entrega'] == 'pendiente' || $row['estado_entrega'] == 'Pendiente') {
        $span_class .= '<span class="badge bg-yellow">';
      } else if ($row['estado_entrega'] == 'Parcial' || $row['estado_entrega'] == 'Parcialmente Entregado') {
        $span_class .= '<span class="badge bg-yellow">';
      } else if ($row['estado_entrega'] == 'no entregado' || $row['estado_entrega'] == 'No entregado') {
        $span_class .= '<span class="badge bg-default">';
      } else if ($row['estado_entrega'] == 'Cancelado') {
        $span_class .= '<span class="badge bg-crimson">';
      } else {
        $span_class .= '<span class="badge bg-green">';
      }
    // Preparar botonera igual que en el main
    $botones = "";
    if ($row['estado_entrega'] == 'pendiente' || $row['estado_entrega'] == 'Parcialmente Entregado' || $row['estado_entrega'] == 'Parcial' || $row['estado_entrega'] == 'Pendiente') {
        $cedula_a_enviar = ($row['es_menor'] == 1 && !empty($row['cedula_representante'])) ? $row['cedula_representante'] : $row['cedula_pac'];
        $botones .= '<a href="farmacia_prescripciones_ver.php?id='.$row['id_prescripcion'].'&tipo='.$row['tipo_receta'].'" class="btn btn-info btn-sm" title="Ver Informacion"><img src="../../recursos/imagenes/iconos/info.png" style="width:15px; height:15px;"></a> ';
        $botones .= '<a href="farmacia_inventario_movimiento_despacho.php?id_pres='.$row['id_prescripcion'].'&pac='.urlencode($cedula_a_enviar).'&menor='.$row['es_menor'].'&tipo='.$row['tipo_receta'].'&from=prescripciones" class="btn btn-success btn-sm" title="Despachar Receta"><img src="../../recursos/imagenes/iconos/enviar.png" style="width:15px; height:15px;"></a> ';
        $botones .= '<button onclick="cambiarEstado('.$row['id_prescripcion'].', \'no entregado\', \''.$row['tipo_receta'].'\')" class="btn btn-sm btn-danger btn-accion-rapida" title="Cancelar"><img src="../../recursos/imagenes/iconos/cancelar.png" style="width:15px; height:15px;"></button>';
    } else {
        $botones .= '<a href="farmacia_prescripciones_ver.php?id='.$row['id_prescripcion'].'&tipo='.$row['tipo_receta'].'" class="btn btn-info btn-sm" title="Ver Informacion"><img src="../../recursos/imagenes/iconos/info.png" style="width:15px; height:15px;"></a>';
    }

    echo '<tr>';
    echo '<td>' . date('d/m/Y', strtotime($row['fecha_solicitud'])) . '</td>';
    echo '<td>' . $etiquetaTipo . '<br><strong>' . htmlspecialchars(trim($row['nom_pac']." ".$row['ape_pac'])) . '</strong><br><small>'.$row['tipo_cedula_pac']."-".$row['cedula_pac'].'</small></td>';
    echo '<td>Dr/a. ' . htmlspecialchars(trim($row['nom_med']." ".$row['ape_med'])) . '</td>';
    echo '<td><span class="text-blue">' . $row['nombre_medicamento'] . '</span></td>';
    echo '<td class="text-center">' . $span_class . strtoupper($estado_requerido) . '</span></td>';
    echo '<td class="text-center">' . $botones . '</td>';
    echo '</tr>';
}
echo '</tbody></table>';
?>