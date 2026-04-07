<?php
require '../../../cfg/conexion.php'; 
header('Content-Type: application/json');

$response = ['existe_duplicado' => false, 'tipo_error' => '', 'error' => false];

if (isset($_POST['nombre'], $_POST['id_presentacion'], $_POST['codigo_barras'])) {
    $nombre = trim($_POST['nombre']);
    $id_presentacion = (int)$_POST['id_presentacion'];
    $codigo_barras = trim($_POST['codigo_barras']);

    try {
        // Buscamos si existe el mismo medicamento+presentación O si el código ya está en uso
        $sql = "SELECT m.nombre_medicamento, d.codigo_barras 
                FROM descripcion_medicamento d
                JOIN medicamento m ON d.Id_medicamento = m.Id_medicamento
                WHERE (LOWER(m.nombre_medicamento) = LOWER(?) AND d.Id_presentacion = ?)
                OR (d.codigo_barras = ? AND d.codigo_barras != '') 
                LIMIT 1";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sis", $nombre, $id_presentacion, $codigo_barras);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($fila = $result->fetch_assoc()) {
            $response['existe_duplicado'] = true;
            
            // Identificamos qué causó el conflicto
            if ($fila['codigo_barras'] === $codigo_barras && $codigo_barras !== '') {
                $response['tipo_error'] = 'codigo';
                $response['detalle'] = $fila['nombre_medicamento']; // Para decir a quién pertenece el código
            } else {
                $response['tipo_error'] = 'medicamento';
            }
        }
        $stmt->close();

    } catch (Exception $e) {
        $response['error'] = true;
    }
}
echo json_encode($response);