<?php
header('Content-Type: application/json');
include("../conexion.php");

if (!isset($_POST['id_prescripcion']) || !isset($_POST['tipo_despacho'])) {
    echo json_encode([]);
    exit;
}

$id_prescripcion = mysqli_real_escape_string($conexion, $_POST['id_prescripcion']);
$tipo_despacho = mysqli_real_escape_string($conexion, $_POST['tipo_despacho']);

$medicamentos_precargados = [];

/* Ambos tipos (interno/representante/externo) ahora viven en solicitud_medicamento + detalle_solicitud */
$query_meds = "SELECT 
                    dm.Id AS id_medicamento, 
                    m.nombre_medicamento,
                    ds.cantidad_recetada,
                    IFNULL(ds.cantidad_entregada, 0) AS cantidad_entregada, 
                    IFNULL(ds.dosis, '') AS dosis,
                    (SELECT GROUP_CONCAT(CONCAT(IFNULL(pa.nombre,''), ' ', IFNULL(dpm.cantidad_unidad_medida,''), ' ', IFNULL(um.unidad,'')) SEPARATOR ' + ') 
                     FROM detalle_principio_medicamento dpm 
                     LEFT JOIN principio_activo pa ON dpm.id_principio_activo = pa.Id_principio_activo 
                     LEFT JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida 
                     WHERE dpm.id_descripcion_medicamento = dm.Id) AS componentes
                FROM detalle_solicitud ds
                INNER JOIN solicitud_medicamento sm ON ds.id_solicitud = sm.id_solicitud
                INNER JOIN descripcion_medicamento dm ON ds.id_descripcion_medicamento = dm.Id
                INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
                WHERE sm.id_solicitud = '$id_prescripcion'
                  AND ds.estatus_item IN ('Pendiente', 'Parcialmente Entregado')";

$res_meds = mysqli_query($conexion, $query_meds);
if ($res_meds) {
    while ($row_med = mysqli_fetch_assoc($res_meds)) {
        $id_desc = $row_med['id_medicamento'];
        
        $sql_lote = "SELECT l.Lote as lote, ex.cantidad_actual 
                     FROM lotes_medicamentos l 
                     INNER JOIN existencias_stock ex ON l.Id = ex.Id_lote 
                     WHERE l.estado_lote = 'Disponible' 
                       AND l.Id_descripcion_medicamento = '$id_desc' 
                       AND ex.cantidad_actual > 0 
                       AND l.fecha_vencimiento > CURDATE()
                     ORDER BY l.fecha_vencimiento ASC LIMIT 1";
        
        $res_lote = mysqli_query($conexion, $sql_lote);

        if ($lote_data = mysqli_fetch_assoc($res_lote)) {
            $row_med['lote'] = $lote_data['lote'];

            $cant_req = (int)$row_med['cantidad_recetada'];
            $cant_ent = (int)$row_med['cantidad_entregada'];
            $cant_pendiente = $cant_req - $cant_ent;

            $cant_disp = (int)$lote_data['cantidad_actual'];
            $row_med['cantidad'] = ($cant_pendiente > $cant_disp) ? $cant_disp : $cant_pendiente;
            
            $row_med['cantidad_recetada'] = $cant_pendiente;
            $row_med['componentes'] = $row_med['componentes'] ? $row_med['componentes'] : 'Sin principios activos';

            if ($cant_pendiente > 0) {
                $medicamentos_precargados[] = $row_med;
            }
        }
    }
}

echo json_encode($medicamentos_precargados);

if (isset($conexion)) {
    mysqli_close($conexion);
}
?>