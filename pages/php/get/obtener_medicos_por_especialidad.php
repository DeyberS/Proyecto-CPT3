<?php
// Incluir la conexión a tu base de datos cpt3db
require_once('../../../cfg/conexion.php');

// Verificamos que se haya enviado el ID de la especialidad
if (isset($_POST['id_especialidad']) && !empty($_POST['id_especialidad'])) {
    $id_especialidad = $_POST['id_especialidad'];

    $query = "SELECT m.Id_detalle_medico, p.nombre, p.apellido, m.tipo_medico 
              FROM detalle_medico m
              INNER JOIN persona p ON m.Id_persona = p.id
              INNER JOIN especialidades_medicos em ON m.Id_detalle_medico = em.Id_detalle_medico
              WHERE em.Id_especialidad = ? AND p.estatus IN (1, 2) AND m.tipo_medico = 'Interno'";

    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $id_especialidad);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo '<option value="">Seleccione un médico</option>';
        while ($row = $result->fetch_assoc()) {
            // Se genera la opción con el ID del detalle_medico y el nombre completo
            echo '<option value="' . $row['Id_detalle_medico'] . '">' . $row['nombre'] . ' ' . $row['apellido'] . '</option>';
        }
    } else {
        echo '<option value="">No hay médicos disponibles para esta especialidad</option>';
    }

    $stmt->close();
} else {
    echo '<option value="">Seleccione primero una especialidad</option>';
}
?>


