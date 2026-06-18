<?php
// Configurar la cabecera para devolver JSON
header('Content-Type: application/json');

// Incluir la conexión a la base de datos
include("../conexion.php");

// Verificar que vengan los datos necesarios
if (!isset($_POST['id_prescripcion']) || !isset($_POST['tipo_despacho'])) {
    echo json_encode([]);
    exit;
}

$id_prescripcion = mysqli_real_escape_string($conexion, $_POST['id_prescripcion']);
$tipo_despacho = mysqli_real_escape_string($conexion, $_POST['tipo_despacho']);

$medicamentos_precargados = [];
$query_meds = "";

// Determinar el tipo de consulta según el despacho
if ($tipo_despacho == 'interno' || $tipo_despacho == 'representante') {
    // Recetas internas (Se guarda en prescripcion_medicamentos)
    $query_meds = "SELECT 
                        dm.Id AS id_medicamento, 
                        m.nombre_medicamento,
                        1 AS cantidad_recetada,
                        0 AS cantidad_entregada,
                        '' AS dosis,
                        (SELECT GROUP_CONCAT(CONCAT(IFNULL(pa.nombre,''), ' ', IFNULL(dpm.cantidad_unidad_medida,''), ' ', IFNULL(um.unidad,'')) SEPARATOR ' + ') 
                         FROM detalle_principio_medicamento dpm 
                         LEFT JOIN principio_activo pa ON dpm.id_principio_activo = pa.Id_principio_activo 
                         LEFT JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida 
                         WHERE dpm.id_medicamento = dm.Id) AS componentes
                    FROM prescripcion_medicamentos pm
                    INNER JOIN descripcion_medicamento dm ON pm.Id_descripcion_medicamento = dm.Id
                    INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
                    WHERE pm.Id_consulta = '$id_prescripcion' AND pm.estado_prescripcion IN ('pendiente', 'parcial')";
                    
} else if ($tipo_despacho == 'externo') {
    // Recetas externas (Se guarda en detalle_solicitud)
    $query_meds = "SELECT 
                      dm.Id AS id_medicamento, 
                      m.nombre_medicamento,
                      ds.cantidad_recetada,
                      IFNULL(ds.cantidad_entregada, 0) AS cantidad_entregada, 
                      '' AS dosis,
                      (SELECT GROUP_CONCAT(CONCAT(IFNULL(pa.nombre,''), ' ', IFNULL(dpm.cantidad_unidad_medida,''), ' ', IFNULL(um.unidad,'')) SEPARATOR ' + ') 
                       FROM detalle_principio_medicamento dpm 
                       LEFT JOIN principio_activo pa ON dpm.id_principio_activo = pa.Id_principio_activo 
                       LEFT JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida 
                       WHERE dpm.id_medicamento = dm.Id) AS componentes
                  FROM detalle_solicitud ds
                  INNER JOIN descripcion_medicamento dm ON ds.id_medicamento = dm.Id
                  INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
                  WHERE ds.id_solicitud = '$id_prescripcion'";
}

if ($query_meds != "") {
    $res_meds = mysqli_query($conexion, $query_meds);
    if ($res_meds) {
        while ($row_med = mysqli_fetch_assoc($res_meds)) {
            $id_desc = $row_med['id_medicamento'];
            
            // Buscar el Lote disponible con mayor prioridad (fecha de vencimiento más próxima - FIFO)
            $sql_lote = "SELECT l.Lote as lote, ex.cantidad_actual 
                      FROM lotes_medicamentos l 
                      INNER JOIN existencias_stock ex ON l.Id = ex.Id_lote 
                      WHERE l.estado_lote = 'Disponible' 
                      AND l.Id_descripcion_medicamento = '$id_desc' 
                      AND ex.cantidad_actual > 0 
                      AND l.fecha_vencimiento > CURDATE()
                      ORDER BY l.fecha_vencimiento ASC LIMIT 1";
            
            $res_lote = mysqli_query($conexion, $sql_lote);

            // Si hay un lote disponible, lo procesamos
            if ($lote_data = mysqli_fetch_assoc($res_lote)) {
                $row_med['lote'] = $lote_data['lote'];

                // 1. Calculamos la cantidad pendiente real
                $cant_req = (int)$row_med['cantidad_recetada'];
                $cant_ent = (int)$row_med['cantidad_entregada'];
                $cant_pendiente = $cant_req - $cant_ent;

                $cant_disp = (int)$lote_data['cantidad_actual'];

                // 2. Aseguramos que la sugerencia no supere la existencia NI lo que falta por entregar
                $row_med['cantidad'] = ($cant_pendiente > $cant_disp) ? $cant_disp : $cant_pendiente;
                
                // Actualizamos la variable para el formulario frontend
                $row_med['cantidad_recetada'] = $cant_pendiente;
                $row_med['componentes'] = $row_med['componentes'] ? $row_med['componentes'] : 'Sin principios activos';

                // 3. SOLO agregamos el medicamento si todavía falta cantidad por entregar
                if ($cant_pendiente > 0) {
                    $medicamentos_precargados[] = $row_med;
                }
            }
        }
    }
}

// Retornar los resultados al frontend
echo json_encode($medicamentos_precargados);

// Cerrar conexión
if (isset($conexion)) {
    mysqli_close($conexion);
}
?>