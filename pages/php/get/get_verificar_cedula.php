<?php
// =========================================================================
// ARCHIVO: verificar_cedula.php
// FUNCIÓN: Verifica si una cédula (tipo + número) ya está registrada en la 
//          tabla 'persona'. En modo edición, ignora el ID de la persona 
//          actual.
// RUTA ESPERADA: ../../cfg/verificar_cedula.php
// =========================================================================

// Configuración de cabeceras para respuesta JSON
header('Content-Type: application/json');

// Incluir el archivo de conexión a la base de datos
// Asumiendo que 'conexion.php' está en la misma carpeta cfg/
include("../../../cfg/conexion.php"); 

// Establecer la respuesta por defecto como "no existe"
$response = array('existe' => false);

// 1. Obtener y sanitizar los datos del POST
$tipo_cedula = isset($_POST['tipo_cedula']) ? mysqli_real_escape_string($conexion, $_POST['tipo_cedula']) : '';
$cedula = isset($_POST['cedula']) ? mysqli_real_escape_string($conexion, $_POST['cedula']) : '';
// El ID del médico solo se envía si se está EDITANDO, en AGREGAR estará vacío o no se enviará.
$id_medico = isset($_POST['id_medico']) ? (int)$_POST['id_medico'] : 0;

// Validar que se recibieron los datos necesarios
if (empty($tipo_cedula) || empty($cedula)) {
    // Si faltan datos, retorna un error (o simplemente 'false' si quieres)
    echo json_encode(['existe' => false, 'error' => 'Datos de cédula incompletos']);
    exit;
}

// 2. Construir la consulta SQL
$sql = "SELECT id FROM persona WHERE tipo_cedula = '$tipo_cedula' AND cedula = '$cedula'";

// Si estamos en modo edición ($id_medico > 0), agregamos una cláusula para excluir al médico actual
if ($id_medico > 0) {
    // Busca si la cédula existe en cualquier registro que NO sea el del médico actual
    $sql .= " AND id != $id_medico";
}

// 3. Ejecutar la consulta
$resultado = mysqli_query($conexion, $sql);

// 4. Procesar el resultado
if ($resultado) {
    // Si mysqli_num_rows() es mayor que 0, significa que se encontró al menos un registro
    if (mysqli_num_rows($resultado) > 0) {
        $response['existe'] = true;
    }
    // Liberar el resultado
    mysqli_free_result($resultado);
} else {
    // Manejo de error de la consulta SQL
    $response = array('existe' => false, 'error' => 'Error en la consulta SQL: ' . mysqli_error($conexion));
}

// 5. Devolver la respuesta en formato JSON
echo json_encode($response);

// Cerrar la conexión (asumiendo que $conexion se creó en conexion.php)
mysqli_close($conexion);
?>