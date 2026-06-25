<?php
// cfg/ajax/filtrar_medicamentos_completo.php
include("../conexion.php");

$response = array();
// Recibimos el modo (si no viene, por seguridad asumimos 'despacho')
$modo = isset($_POST['modo']) ? $_POST['modo'] : 'despacho';

// 1. Iniciar la consulta SQL base
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
        LEFT JOIN laboratorio l ON dm.Id_laboratorio = l.Id_laboratorio
        INNER JOIN detalle_principio_medicamento dpm ON dm.Id = dpm.id_medicamento
        INNER JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida
        INNER JOIN principio_activo pa ON dpm.id_principio_activo = pa.Id_principio_activo
        WHERE dm.estatus = 1 AND m.estatus = 1 AND p.estatus = 1 AND l.estatus = 1";

// SOLO aplicar el filtro estricto de stock y vencimiento si NO es una entrada ni un pedido
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

// Array para guardar las condiciones de filtrado
$condiciones = array();
$tipos_params = ""; // Cadena para bind_param
$params = array(); // Valores para bind_param

// 2. Revisar cada campo del filtro POST y añadir condiciones
// Usamos prepared statements para seguridad

// Filtro por Búsqueda Rápida (Etiquetas / Tags)
if (!empty($_POST['filtro_busqueda_rapida'])) {
    $tags = explode(' ', $_POST['filtro_busqueda_rapida']);
    foreach ($tags as $tag) {
        if(trim($tag) != "") {
            $busqueda = '%' . trim($tag) . '%';
            // Usamos EXISTS para buscar en los principios activos sin alterar el GROUP_CONCAT principal
            $condiciones[] = "(m.nombre_medicamento LIKE ? OR dm.excipientes LIKE ? OR p.nombre_presentacion LIKE ? OR 
                              EXISTS (SELECT 1 FROM detalle_principio_medicamento dsub 
                                      INNER JOIN principio_activo psub ON dsub.id_principio_activo = psub.Id_principio_activo 
                                      WHERE dsub.id_medicamento = dm.Id AND psub.nombre LIKE ?))";
            $tipos_params .= "ssss";
            $params[] = $busqueda;
            $params[] = $busqueda;
            $params[] = $busqueda;
            $params[] = $busqueda;
        }
    }
}

// Filtro por Nombre o ID (busca en ambos campos)
if (!empty($_POST['filtro_nombre'])) {
    $busqueda = '%' . $_POST['filtro_nombre'] . '%';
    $condiciones[] = "(m.nombre_medicamento LIKE ? OR dm.Id LIKE ?)";
    $tipos_params .= "ss";
    $params[] = $busqueda;
    $params[] = $busqueda;
}

// Filtro por Tipo de Medicamento (coincidencia exacta por ID)
if (!empty($_POST['filtro_presentacion'])) {
    $condiciones[] = "dm.Id_presentacion = ?";
    $tipos_params .= "i";
    $params[] = $_POST['filtro_presentacion'];
}

// Filtro por Principios Activos (Etiquetas / Tags)
if (!empty($_POST['filtro_principios'])) {
    $tags_principios = explode(' ', $_POST['filtro_principios']);
    foreach ($tags_principios as $tag) {
        if(trim($tag) != "") {
            $busqueda_pa = '%' . trim($tag) . '%';
            // Usamos EXISTS para aislar el filtro del componente
            $condiciones[] = "EXISTS (SELECT 1 FROM detalle_principio_medicamento dsub2 
                                      INNER JOIN principio_activo psub2 ON dsub2.id_principio_activo = psub2.Id_principio_activo 
                                      WHERE dsub2.id_medicamento = dm.Id AND psub2.nombre LIKE ?)";
            $tipos_params .= "s";
            $params[] = $busqueda_pa;
        }
    }
}

// Filtro por Presentación
if (!empty($_POST['filtro_contenido_neto'])) {
    $busqueda = '%' . $_POST['filtro_contenido_neto'] . '%';
    $condiciones[] = "dm.contenido_neto LIKE ?";
    $tipos_params .= "s";
    $params[] = $busqueda;
}

// Filtro por Vía de Aplicación
if (!empty($_POST['filtro_via'])) {
    $condiciones[] = "dm.via_aplicacion = ?";
    $tipos_params .= "s";
    $params[] = $_POST['filtro_via'];
}

// Filtro por Condición de Almacenamiento
if (!empty($_POST['filtro_almacenamiento'])) {
    $condiciones[] = "dm.almacenamiento = ?";
    $tipos_params .= "s";
    $params[] = $_POST['filtro_almacenamiento'];
}

// Filtro por Laboratorio (ID exacto)
if (!empty($_POST['filtro_laboratorio'])) {
    $condiciones[] = "dm.Id_laboratorio = ?";
    $tipos_params .= "i";
    $params[] = $_POST['filtro_laboratorio'];
}

// Filtro por Composición (contiene texto)
if (!empty($_POST['filtro_excipientes'])) {
    $busqueda = '%' . $_POST['filtro_excipientes'] . '%';
    $condiciones[] = "dm.excipientes LIKE ?";
    $tipos_params .= "s";
    $params[] = $busqueda;
}

// Filtro por Código de Barras (coincidencia exacta)
if (!empty($_POST['filtro_barcode'])) {
    $condiciones[] = "dm.codigo_barras = ?";
    $tipos_params .= "s";
    $params[] = $_POST['filtro_barcode'];
}

// 3. Si hay condiciones de filtrado, añadirlas a la consulta SQL
if (count($condiciones) > 0) {
    $sql .= " AND " . implode(" AND ", $condiciones);
}

$sql .= " GROUP BY dm.Id ORDER BY m.nombre_medicamento ASC LIMIT 100";

// 4. Preparar y ejecutar la consulta SQL compleja
$stmt = $conexion->prepare($sql);

if (!empty($tipos_params)) {
    // Usar call_user_func_array para enlazar parámetros dinámicamente
    // Esto es necesario porque el número de parámetros varía
    $bind_names[] = $tipos_params;
    for ($i=0; $i<count($params); $i++) {
        $bind_name = 'bind' . $i;
        $$bind_name = $params[$i];
        $bind_names[] = &$$bind_name;
    }
    call_user_func_array(array($stmt, 'bind_param'), $bind_names);
}

$stmt->execute();
$resultado = $stmt->get_result();

$medicamentos_filtrados = [];

// 5. Formatear los resultados para el dropdown
while ($row = $resultado->fetch_assoc()) {
    // Formato: Nombre (Componente 1 + Componente 2) - [Tipo]
    $nombre = $row['nombre_medicamento'];
    $componentes = !empty($row['componentes']) ? $row['componentes'] : 'Sin componentes';
    $tipo = $row['nombre_presentacion'];
    
    $nombre_completo = "$nombre ($componentes) - [$tipo]";
    
    $medicamentos_filtrados[] = array(
        'id_desc' => $row['id_desc'],
        'nombre_completo' => htmlspecialchars($nombre_completo)
    );
}

// 6. Enviar la respuesta JSON con los medicamentos filtrados
echo json_encode($medicamentos_filtrados);
?>