<?php
include("../conexion.php");

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

try {
    $modo = isset($_POST['modo']) ? $_POST['modo'] : 'despacho';
    $modos_validos = ['despacho', 'entrada', 'pedido', 'ajuste'];
    if (!in_array($modo, $modos_validos, true)) $modo = 'despacho';

    $sql = "SELECT 
                dm.Id AS id_desc,
                m.nombre_medicamento,
                p.nombre_presentacion,
                l.nombre_laboratorio,
                dm.contenido_neto,
                GROUP_CONCAT(CONCAT(IFNULL(pa.nombre,''), ' ', IFNULL(dpm.cantidad_unidad_medida,''), IFNULL(um.unidad,'')) SEPARATOR ' + ') AS componentes 
            FROM descripcion_medicamento dm
            INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
            INNER JOIN presentacion p ON dm.Id_presentacion = p.Id_presentacion
            LEFT  JOIN laboratorio l ON dm.Id_laboratorio = l.Id_laboratorio AND l.estatus = 1
            LEFT  JOIN detalle_principio_medicamento dpm ON dm.Id = dpm.id_descripcion_medicamento
            LEFT  JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida
            LEFT  JOIN principio_activo pa ON dpm.id_principio_activo = pa.Id_principio_activo
            WHERE dm.estatus = 1 AND m.estatus = 1 AND p.estatus = 1";

    if ($modo !== 'entrada' && $modo !== 'pedido' && $modo !== 'ajuste') {
        $sql .= " AND EXISTS (
                    SELECT 1 FROM lotes_medicamentos lm 
                    INNER JOIN existencias_stock ex ON lm.Id = ex.Id_lote 
                    WHERE lm.Id_descripcion_medicamento = dm.Id 
                    AND lm.estado_lote = 'Disponible' 
                    AND ex.cantidad_actual > 0 
                    AND lm.fecha_vencimiento > CURDATE()
                )";
    }

    $condiciones = array();
    $tipos_params = "";
    $params = array();

    if (!empty($_POST['filtro_busqueda_rapida'])) {
        $tags = explode(' ', $_POST['filtro_busqueda_rapida']);
        foreach ($tags as $tag) {
            if (trim($tag) != "") {
                $busqueda = '%' . trim($tag) . '%';
                $condiciones[] = "(m.nombre_medicamento LIKE ? OR dm.excipientes LIKE ? OR p.nombre_presentacion LIKE ? OR 
                                  EXISTS (SELECT 1 FROM detalle_principio_medicamento dsub 
                                          INNER JOIN principio_activo psub ON dsub.id_principio_activo = psub.Id_principio_activo 
                                          WHERE dsub.id_descripcion_medicamento = dm.Id AND psub.nombre LIKE ?))";
                $tipos_params .= "ssss";
                array_push($params, $busqueda, $busqueda, $busqueda, $busqueda);
            }
        }
    }
    if (!empty($_POST['filtro_nombre'])) {
        $busqueda = '%' . $_POST['filtro_nombre'] . '%';
        $condiciones[] = "(m.nombre_medicamento LIKE ? OR CAST(dm.Id AS CHAR) LIKE ?)";
        $tipos_params .= "ss";
        array_push($params, $busqueda, $busqueda);
    }
    if (!empty($_POST['filtro_presentacion'])) {
        $condiciones[] = "dm.Id_presentacion = ?";
        $tipos_params .= "i";
        $params[] = $_POST['filtro_presentacion'];
    }
    if (!empty($_POST['filtro_principios'])) {
        foreach (explode(' ', $_POST['filtro_principios']) as $tag) {
            if (trim($tag) != "") {
                $busqueda_pa = '%' . trim($tag) . '%';
                $condiciones[] = "EXISTS (SELECT 1 FROM detalle_principio_medicamento dsub2 
                                          INNER JOIN principio_activo psub2 ON dsub2.id_principio_activo = psub2.Id_principio_activo 
                                          WHERE dsub2.id_descripcion_medicamento = dm.Id AND psub2.nombre LIKE ?)";
                $tipos_params .= "s";
                $params[] = $busqueda_pa;
            }
        }
    }
    if (!empty($_POST['filtro_contenido_neto'])) {
        $condiciones[] = "dm.contenido_neto LIKE ?";
        $tipos_params .= "s";
        $params[] = '%' . $_POST['filtro_contenido_neto'] . '%';
    }
    if (!empty($_POST['filtro_via'])) {
        $condiciones[] = "dm.via_aplicacion = ?";
        $tipos_params .= "s";
        $params[] = $_POST['filtro_via'];
    }
    if (!empty($_POST['filtro_almacenamiento'])) {
        $condiciones[] = "dm.almacenamiento = ?";
        $tipos_params .= "s";
        $params[] = $_POST['filtro_almacenamiento'];
    }
    if (!empty($_POST['filtro_laboratorio'])) {
        $condiciones[] = "dm.Id_laboratorio = ?";
        $tipos_params .= "i";
        $params[] = $_POST['filtro_laboratorio'];
    }
    if (!empty($_POST['filtro_excipientes'])) {
        $condiciones[] = "dm.excipientes LIKE ?";
        $tipos_params .= "s";
        $params[] = '%' . $_POST['filtro_excipientes'] . '%';
    }
    if (!empty($_POST['filtro_barcode'])) {
        $condiciones[] = "dm.codigo_barras = ?";
        $tipos_params .= "s";
        $params[] = $_POST['filtro_barcode'];
    }

    if (count($condiciones) > 0) {
        $sql .= " AND " . implode(" AND ", $condiciones);
    }

    $sql .= " GROUP BY dm.Id ORDER BY m.nombre_medicamento ASC LIMIT 100";

    $stmt = $conexion->prepare($sql);
    if (!$stmt) throw new Exception('Prepare falló: ' . $conexion->error);

    if (!empty($tipos_params)) {
        $bind_names = array($tipos_params);
        for ($i = 0; $i < count($params); $i++) {
            $bind_names[] = &$params[$i];
        }
        call_user_func_array(array($stmt, 'bind_param'), $bind_names);
    }

    $stmt->execute();
    $resultado = $stmt->get_result();

    $medicamentos_filtrados = [];
    while ($row = $resultado->fetch_assoc()) {
        $nombre      = $row['nombre_medicamento'];
        $componentes = !empty($row['componentes']) ? $row['componentes'] : 'Sin componentes';
        $tipo        = $row['nombre_presentacion'];
        $nombre_completo = "$nombre ($componentes) - [$tipo]";

        $medicamentos_filtrados[] = array(
            'id_desc'         => $row['id_desc'],
            'nombre_completo' => $nombre_completo
        );
    }

    echo json_encode($medicamentos_filtrados);

} catch (Throwable $e) {
    error_log('[filtrar_medicamentos] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => true, 'mensaje' => $e->getMessage()]);
}