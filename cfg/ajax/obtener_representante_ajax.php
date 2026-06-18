<?php
require_once "../conexion.php"; // Ajusta la ruta a tu conexión si es diferente

header('Content-Type: application/json');

if (isset($_POST['cedula']) && isset($_POST['tipo_cedula'])) {
    $cedula = mysqli_real_escape_string($conexion, $_POST['cedula']);
    $tipo_cedula = mysqli_real_escape_string($conexion, $_POST['tipo_cedula']);
    
    // Retornamos 'existe' y 'es_representante' tal como lo hace el formulario normal
    $respuesta = ['existe' => false, 'es_representante' => false];

    if (!empty($cedula)) {
        // Buscamos a la persona, verificamos su rol dinámicamente y unimos su teléfono
        $sql = "SELECT p.id, p.nombre, p.apellido, p.email, p.genero, p.fecha_nacimiento, tp.Id_prefijo, tp.telefono,
                IF(EXISTS(SELECT 1 FROM detalle_persona_rol dpr WHERE dpr.Id_persona = p.id AND dpr.Id_rol = 5), 1, 0) AS es_representante 
                FROM persona p
                LEFT JOIN telefonos_personas tp ON p.Id = tp.Id_persona
                WHERE p.cedula = '$cedula' AND p.tipo_cedula = '$tipo_cedula' LIMIT 1";
                
        $resultado = mysqli_query($conexion, $sql);

        if ($resultado && mysqli_num_rows($resultado) > 0) {
            $row = mysqli_fetch_assoc($resultado);
            $respuesta = [
                'existe' => true,
                'es_representante' => (bool)$row['es_representante'],
                'nombre' => $row['nombre'],
                'apellido' => $row['apellido'],
                'email' => $row['email'],
                'genero' => $row['genero'],
                'fecha_nacimiento' => $row['fecha_nacimiento'],
                'id_prefijo' => $row['Id_prefijo'],
                'telefono' => $row['telefono']
            ];
        }
    }
    echo json_encode($respuesta);
} else {
    echo json_encode(['existe' => false, 'error' => 'Parámetros inválidos']);
}
?>