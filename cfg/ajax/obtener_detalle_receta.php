<?php
include("../conexion.php");

$id_prescripcion = isset($_POST['id_prescripcion']) ? intval($_POST['id_prescripcion']) : 0;
$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : '';

$medicamentos = [];

if ($id_prescripcion > 0) {
    // Subconsulta para traer los componentes agrupados
    $join_componentes = "
        LEFT JOIN (
            SELECT 
                dpm.id_medicamento as id_desc, 
                GROUP_CONCAT(CONCAT(IFNULL(pa.nombre,''), ' ', IFNULL(dpm.cantidad_unidad_medida,''), ' ', IFNULL(um.unidad,'')) SEPARATOR ' + ') AS componentes
            FROM detalle_principio_medicamento dpm
            LEFT JOIN principio_activo pa ON dpm.id_principio_activo = pa.Id_principio_activo
            LEFT JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida
            GROUP BY dpm.id_medicamento
        ) comp_tbl ON dm.Id = comp_tbl.id_desc
    ";

    if ($tipo === 'Interna') {
        $sql = "SELECT dm.Id as id_medicamento, m.nombre_medicamento, p.nombre_presentacion, comp_tbl.componentes, 1 as cantidad_recetada
                FROM prescripcion_medicamentos pm
                INNER JOIN descripcion_medicamento dm ON pm.Id_descripcion_medicamento = dm.Id
                INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
                LEFT JOIN presentacion p ON dm.Id_presentacion = p.Id_presentacion
                $join_componentes
                WHERE pm.Id_consulta = $id_prescripcion AND pm.estado_prescripcion != 'entregado'";
    } else {
        $sql = "SELECT dm.Id as id_medicamento, m.nombre_medicamento, p.nombre_presentacion, comp_tbl.componentes, ds.cantidad_recetada, ds.cantidad_entregada
                FROM detalle_solicitud ds
                INNER JOIN descripcion_medicamento dm ON ds.id_medicamento = dm.Id
                INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
                LEFT JOIN presentacion p ON dm.Id_presentacion = p.Id_presentacion
                $join_componentes
                WHERE ds.id_solicitud = $id_prescripcion AND ds.estatus_item != 'Entregado'";
    }

    $res = mysqli_query($conexion, $sql);
    while ($row = mysqli_fetch_assoc($res)) {
        // Calculamos cuánto falta por entregar si es parcial
        $ya_entregado = isset($row['cantidad_entregada']) ? intval($row['cantidad_entregada']) : 0;
        $recetado = intval($row['cantidad_recetada']);
        $pendiente = $recetado - $ya_entregado;

        // Formatear los componentes para que se vea limpio
        $texto_componentes = !empty($row['componentes']) ? $row['componentes'] : 'Sin componentes activos';

        if($pendiente > 0 || $tipo === 'Interna'){
            $medicamentos[] = [
                'id_medicamento' => $row['id_medicamento'],
                'nombre_medicamento' => $row['nombre_medicamento'],
                'componentes' => $texto_componentes . ' (' . $row['nombre_presentacion'] . ')',
                'cantidad_recetada' => ($tipo === 'Interna') ? 1 : $pendiente,
                'cantidad_ya_entregada' => $ya_entregado
            ];
        }
    }
}

echo json_encode($medicamentos);
?>