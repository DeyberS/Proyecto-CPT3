<?php
// Incluir la conexión a la base de datos
include("../conexion.php");

if (isset($_POST['cedula']) && isset($_POST['tipo_cedula'])) {
    $cedula = mysqli_real_escape_string($conexion, $_POST['cedula']);
    $tipo_cedula = mysqli_real_escape_string($conexion, $_POST['tipo_cedula']);
    
    $respuesta = array('encontrado' => false, 'nombre' => '', 'entregado_a' => '', 'representante' => '');

    if (!empty($cedula)) {
        // ÚNICA BÚSQUEDA: Buscar estrictamente en la tabla 'persona'
        $sql_persona = "SELECT p.nombre, p.apellido, p.cedula, rep.nombre AS rep_nombre, rep.apellido AS rep_apellido 
        FROM persona p
        JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona
        JOIN rol r ON dpr.Id_rol = r.Id_rol
        LEFT JOIN detalle_paciente_menor dpm ON p.id = dpm.id_persona
        LEFT JOIN persona rep ON dpm.id_representante = rep.id
        WHERE r.Id_rol = 3 AND p.cedula = '$cedula' AND p.tipo_cedula = '$tipo_cedula' LIMIT 1";
        
        $res_persona = mysqli_query($conexion, $sql_persona);

        if ($res_persona && mysqli_num_rows($res_persona) > 0) {
            $row = mysqli_fetch_assoc($res_persona);
            $respuesta['encontrado'] = true;
            $respuesta['nombre'] = trim($row['nombre'] . ' ' . $row['apellido']);
            
            // Si el LEFT JOIN encontró un representante, lo asignamos a la variable
            if (!empty($row['rep_nombre'])) {
                $respuesta['representante'] = trim($row['rep_nombre'] . ' ' . $row['rep_apellido']);
            }
        }
        // Nota: Ya no hay "Plan B" (else) para buscar en solicitud_medicamento. 
        // Si no está registrado en persona, simplemente devuelve encontrado = false.
    }

    // Retornamos el resultado en formato JSON
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($respuesta);
}
?>