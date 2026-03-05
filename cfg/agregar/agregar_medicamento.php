<?php
session_start();
// Asegúrate de que 'conexion.php' incluya la variable $conexion
require_once "../conexion.php";

// 1. Recolección y Sanitización de Datos del Formulario
// Se usa mysqli_real_escape_string para prevenir inyecciones SQL
$nombre_medicamento = mysqli_real_escape_string($conexion, $_POST['medicamento']);             // Nombre base (Ibuprofeno)
$descripcion_presentacion = mysqli_real_escape_string($conexion, $_POST['presentacion']);     // Descripción específica (400mg)
$tipo_unidad_medida = mysqli_real_escape_string($conexion, $_POST['tipo_unidad_medida']);             // FK a unidad_medida
$cantidad_unidad_medida = mysqli_real_escape_string($conexion, $_POST['cantidad_unidad_medida']);           // Cantidad (ej. 400)
$via_aplicacion = mysqli_real_escape_string($conexion, $_POST['via']);
$almacenamiento = mysqli_real_escape_string($conexion, $_POST['almacenamiento']);
$composicion = mysqli_real_escape_string($conexion, $_POST['composicion']);

// Se asume que el formulario envía el ID del tipo de presentación general (FK a la tabla 'presentacion').
$id_presentacion_tipo = isset($_POST['presentacion']) ? mysqli_real_escape_string($conexion, $_POST['presentacion']) : 1; 

$estatus = 1;
$success = true;

// 2. Iniciar Transacción para asegurar la integridad de los datos
mysqli_begin_transaction($conexion);

// 3. (A) BUSCAR si el medicamento base ya existe
$sql_check_medicamento = "SELECT Id_medicamento FROM medicamento WHERE nombre_medicamento = '$nombre_medicamento'";
$resultado_check = mysqli_query($conexion, $sql_check_medicamento);

if (mysqli_num_rows($resultado_check) > 0) {
    // Caso 3.a: Medicamento EXISTE - Obtener su ID
    $fila = mysqli_fetch_assoc($resultado_check);
    $id_medicamento = $fila['Id_medicamento'];
} else {
    // Caso 3.b: Medicamento NO EXISTE - Insertar nuevo y obtener el ID
    $sql_insert_medicamento = "INSERT INTO medicamento (nombre_medicamento, estatus) VALUES ('$nombre_medicamento', '$estatus')";
    
    if (mysqli_query($conexion, $sql_insert_medicamento)) {
        $id_medicamento = mysqli_insert_id($conexion);
    } else {
        $success = false;
    }
}

// 4. (B) Insertar datos de la Presentación Detallada (Descripcion_Medicamento)
if ($success && isset($id_medicamento)) {
    // Insertar en descripcion_medicamento (Detalles, Stock y Unidades)
    
    $sql_insert_desc_med = "INSERT INTO descripcion_medicamento (
                                cantidad_unidad_medida, via_aplicacion, almacenamiento, composicion,
                                Id_unidad, Id_presentacion, Id_medicamento, estatus
                            )
                            VALUES (
                                '$cantidad_unidad_medida', '$via_aplicacion', '$almacenamiento', '$composicion',
                                '$tipo_unidad_medida', '$id_presentacion_tipo', '$id_medicamento', '$estatus'
                            )";
    if (!mysqli_query($conexion, $sql_insert_desc_med)) {
        $success = false;
        // Opcionalmente, puedes loggear el error aquí:
        // error_log("Error al insertar descripcion_medicamento: " . mysqli_error($conexion));
    }
} else {
    $success = false; // Falló la obtención/inserción del medicamento base
}

// 5. Finalizar Transacción
if ($success) {
    mysqli_commit($conexion);
    // Redireccionar al éxito
     $_SESSION['mensaje_user_exito'] = '✅ Éxito: El medicamento fue agregado correctamente.';
    header("location: ../../pages/php/farmacia_medicamentos_listado.php");
    exit();
} else {
    // Revertir y redirigir con error
    mysqli_rollback($conexion);
    // Nota: Es importante mostrar el error al usuario o loggearlo
    error_log("Error de transacción al agregar el medicamento: " . $e->getMessage()); 
    $_SESSION['mensaje_user_error'] = '❌ Error de Registro: No se pudo registrar el medicamento. Detalle: ' . $e->getMessage();
    header("location: ../../pages/php/farmacia_medicamentos_listado.php");
    exit();
}

// 6. Cerrar Conexión
mysqli_close($conexion);
?>