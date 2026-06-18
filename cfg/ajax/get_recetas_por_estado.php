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

// Adaptamos el filtro de estado (Manejo de variaciones de texto)
$filtro = "";
if ($estado_requerido == 'Cancelado') {
    $filtro = "WHERE estado_entrega IN ('Cancelado', 'no entregado', 'No entregado')";
} elseif ($estado_requerido == 'Parcial') {
    $filtro = "WHERE estado_entrega IN ('Parcial', 'Parcialmente Entregado')";
} elseif ($estado_requerido == 'Pendiente') {
    $filtro = "WHERE estado_entrega IN ('Pendiente', 'pendiente')";
} elseif ($estado_requerido == 'Entregado') {
    // CORREGIDO: Al pedir 'Entregado', la query buscará tanto los calculados internos como los strings externos
    $filtro = "WHERE estado_entrega IN ('Entregado', 'entregado', 'Completado', 'Completada', 'completado')";
} else {
    $filtro = "WHERE estado_entrega = '$estado_requerido'";
}

$query = "SELECT * FROM ($sql_base) AS base_unificada $filtro ORDER BY fecha_solicitud DESC";
$resultado = mysqli_query($conexion, $query);

echo '<table class="table table-sm table-hover table-bordered" width="100%" style="font-size: 12px;">';
echo '<thead class="table-dark" style="background-color: #222; color: white;"><tr><th>Fecha</th><th>Paciente</th><th>Médico</th><th>Medicamento</th><th class="text-center">Estado</th><th class="text-center">Acciones</th></tr></thead><tbody>';

if(mysqli_num_rows($resultado) == 0){
    echo '<tr><td colspan="6" class="text-center">No se encontraron recetas en este estado.</td></tr>';
}

while ($row = mysqli_fetch_assoc($resultado)) {
    $etiquetaTipo = ($row['tipo_receta'] === 'Interna') ? (($row['es_menor'] == 1) ? '<span class="label label-info">Interna-Rep.</span>' : '<span class="label label-primary">Interna</span>') : '<span class="label label-warning">Externa</span>';
    
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
    echo '<td>' . $etiquetaTipo . '<br><strong>' . htmlspecialchars(trim($row['nom_pac']." ".$row['ape_pac'])) . '</strong><br><small>'.$row['cedula_pac'].'</small></td>';
    echo '<td>Dr/a. ' . htmlspecialchars(trim($row['nom_med']." ".$row['ape_med'])) . '</td>';
    echo '<td><span class="text-blue">' . $row['nombre_medicamento'] . '</span></td>';
    echo '<td class="text-center"><span class="badge bg-black">' . strtoupper($estado_requerido) . '</span></td>';
    echo '<td class="text-center">' . $botones . '</td>';
    echo '</tr>';
}
echo '</tbody></table>';
?>