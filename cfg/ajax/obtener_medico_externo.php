<?php
// Incluir la conexión a la base de datos
include("../conexion.php");

// Preparamos el array de respuesta por defecto (JSON)
$response = array('encontrado' => false, 'nombre' => '');

// Verificamos que lleguen los datos por POST
if (isset($_POST['cedula']) && isset($_POST['tipo_cedula'])) {
    
    // Limpiamos las variables para evitar inyecciones SQL
    $cedula = mysqli_real_escape_string($conexion, trim($_POST['cedula']));
    $tipo_cedula = mysqli_real_escape_string($conexion, trim($_POST['tipo_cedula']));
    
    // BÚSQUEDA MEJORADA: Hacemos INNER JOIN directamente con detalle_medico 
    // para garantizar que la persona de verdad sea un médico activo en el sistema.
    $sql = "SELECT p.nombre, p.apellido 
            FROM persona p
            INNER JOIN detalle_medico dm ON p.id = dm.Id_persona
            WHERE p.cedula = '$cedula' 
            AND p.tipo_cedula = '$tipo_cedula' 
            AND p.estatus IN (1, 2)
            LIMIT 1";

    $resultado = mysqli_query($conexion, $sql);

    // Si la consulta es exitosa y encuentra 1 coincidencia
    if ($resultado && mysqli_num_rows($resultado) > 0) {
        $fila = mysqli_fetch_assoc($resultado);
        
        // Concatenamos el primer nombre y el primer apellido
        $nombre_completo = $fila['nombre'] . " " . $fila['apellido'];
        
        $response['encontrado'] = true;
        $response['nombre'] = trim($nombre_completo); 
    }
}

// Limpiamos cualquier salida previa y enviamos el JSON
header('Content-Type: application/json; charset=utf-8');
echo json_encode($response);
?>