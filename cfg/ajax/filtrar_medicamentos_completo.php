<?php
// cfg/ajax/filtrar_medicamentos_completo.php
include("../conexion.php");

$response = array();

// 1. Iniciar la consulta SQL base con todos los INNER JOINS necesarios
// 1. Consulta SQL base actualizada
$sql = "SELECT 
            dm.Id AS id_desc,
            m.nombre_medicamento,
            tm.nombre_tipo,
            l.nombre_laboratorio,
            dm.presentacion,
            GROUP_CONCAT(CONCAT(IFNULL(pa.nombre,''), ' ', IFNULL(dpm.cantidad_unidad_medida,''), IFNULL(um.unidad,'')) SEPARATOR ' + ') AS componentes 
        FROM descripcion_medicamento dm
        INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
        INNER JOIN tipo_medicamento tm ON dm.Id_tipo = tm.Id_tipo
        LEFT JOIN laboratorio l ON dm.Id_laboratorio = l.Id_laboratorio
        -- Joins adicionales para los componentes --
        INNER JOIN detalle_principio_medicamento dpm ON dm.Id = dpm.id_medicamento
        INNER JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida
        INNER JOIN principio_activo pa ON dpm.id_principio_activo = pa.Id_principio_activo
        WHERE dm.estatus = 1";

// Array para guardar las condiciones de filtrado
$condiciones = array();
$tipos_params = ""; // Cadena para bind_param
$params = array(); // Valores para bind_param

// 2. Revisar cada campo del filtro POST y añadir condiciones
// Usamos prepared statements para seguridad

// Filtro por Nombre o ID (busca en ambos campos)
if (!empty($_POST['filtro_nombre'])) {
    $busqueda = '%' . $_POST['filtro_nombre'] . '%';
    $condiciones[] = "(m.nombre_medicamento LIKE ? OR dm.Id LIKE ?)";
    $tipos_params .= "ss";
    $params[] = $busqueda;
    $params[] = $busqueda;
}

// Filtro por Tipo de Medicamento (coincidencia exacta por ID)
if (!empty($_POST['filtro_tipo'])) {
    $condiciones[] = "dm.Id_tipo = ?";
    $tipos_params .= "i";
    $params[] = $_POST['filtro_tipo'];
}

// Filtro por Principios Activos (contiene texto)
if (!empty($_POST['filtro_principios'])) {
    $busqueda = '%' . $_POST['filtro_principios'] . '%';
    // Esta consulta es compleja porque los principios están en tablas relacionadas
    $condiciones[] = "dm.Id IN (
        SELECT dpm.id_medicamento
        FROM detalle_principio_medicamento dpm
        INNER JOIN principio_activo pa ON dpm.id_principio_activo = pa.id_principio_activo
        WHERE pa.nombre LIKE ?
    )";
    $tipos_params .= "s";
    $params[] = $busqueda;
}

// Filtro por Presentación
if (!empty($_POST['filtro_presentacion'])) {
    $busqueda = '%' . $_POST['filtro_presentacion'] . '%';
    $condiciones[] = "dm.presentacion LIKE ?";
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
if (!empty($_POST['filtro_composicion'])) {
    $busqueda = '%' . $_POST['filtro_composicion'] . '%';
    $condiciones[] = "dm.composicion LIKE ?";
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
    $tipo = $row['nombre_tipo'];
    
    $nombre_completo = "$nombre ($componentes) - [$tipo]";
    
    $medicamentos_filtrados[] = array(
        'id_desc' => $row['id_desc'],
        'nombre_completo' => htmlspecialchars($nombre_completo)
    );
}

// 6. Enviar la respuesta JSON con los medicamentos filtrados
echo json_encode($medicamentos_filtrados);
?>