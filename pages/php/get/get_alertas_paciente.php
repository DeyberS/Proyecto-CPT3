<?php
// Incluye tu conexión a la base de datos
include '../../../cfg/conexion.php'; 

$cedula = $_POST['cedula'];

// 1. Buscar Datos Básicos y Edad (Asumiendo que tienes fecha_nacimiento)
$query_paciente = "SELECT *, FLOOR(DATEDIFF(CURDATE(), fecha_nacimiento) / 365.25) as edad 
                   FROM persona WHERE cedula = '$cedula'";
$res_paciente = mysqli_query($conexion, $query_paciente);
$paciente = mysqli_fetch_assoc($res_paciente);

$respuesta = [
    'tiene_alertas' => false,
    'es_menor' => false,
    'html_contenido' => '',
    'nombre_representante' => ''
];

if ($paciente) {
    $id_persona = $paciente['id'];

    // 🔹 DEVOLVEMOS EL NOMBRE COMPLETO DEL PACIENTE
    $respuesta['nombre_paciente'] = $paciente['nombre'] . ' ' . $paciente['apellido'];

    $html = "";
}



if ($paciente) {
    $id_persona = $paciente['id'];
    $html = "";

    // 2. Lógica de Menores con tu tabla detalle_paciente_menor
    if ($paciente['edad'] < 18) {
        $respuesta['es_menor'] = true;
        
        // JOIN entre detalle_paciente_menor y persona para sacar el nombre del representante
        $q_rep = "SELECT d.*,p.nombre, p.apellido 
                  FROM detalle_paciente_menor d
                  JOIN persona p ON d.id_representante = p.id
                  WHERE d.id_persona = '$id_persona'";
        
        $res_rep = mysqli_query($conexion, $q_rep);
        if($r = mysqli_fetch_assoc($res_rep)) {
            $respuesta['nombre_representante'] = $r['nombre'] . " " . $r['apellido'];
            $respuesta['parentesco'] = $r['parentesco'];
        }
        $html .= "<div class='alert alert-warning'><b>PACIENTE MENOR DE EDAD</b> (" . $paciente['edad'] . " años)</div>";
    }

    // 2. Buscar Alergias (En tu tabla de historial_alergia)
    $q_alergias = "SELECT a.nombre_alergia 
                   FROM historial_alergias h 
                   JOIN alergias_conocidas a ON h.Id_alergia = a.Id_alergias_conocidas
                   WHERE h.Id_persona = '$id_persona'";
    $res_alergias = mysqli_query($conexion, $q_alergias);
    
    if (mysqli_num_rows($res_alergias) > 0) {
        $respuesta['tiene_alertas'] = true;
        $html .= "<h4 class='text'><br>ALERGIAS DETECTADAS:</br></h4><ul>";
        while ($al = mysqli_fetch_assoc($res_alergias)) {
            $html .= "<li class='text'>" . $al['nombre_alergia'] . "</li>";
        }
        $html .= "</ul>";
    }

    // 3. Buscar Patologías (En tu tabla de historial_patologia)
    $q_patos = "SELECT p.nombre_patologia 
                FROM historial_patologias h 
                JOIN patologias p ON h.Id_patologia = p.Id_patologia 
                WHERE h.Id_persona = '$id_persona'";
    $res_patos = mysqli_query($conexion, $q_patos);

    if (mysqli_num_rows($res_patos) > 0) {
        $respuesta['tiene_alertas'] = true;
        $html .= "<h4 class='text'><br>PATOLOGÍAS PREVIAS:</br></h4><ul>";
        while ($pa = mysqli_fetch_assoc($res_patos)) {
            $html .= "<li class='text'>" . $pa['nombre_patologia'] . "</li>";
        }
        $html .= "</ul>";
    }

    $respuesta['html_contenido'] = $html;
}

echo json_encode($respuesta);
?>


