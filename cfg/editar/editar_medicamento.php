<?php
session_start();
include('../conexion.php'); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Recoger y sanitizar los IDs CLAVE
    // Id_descripcion es la clave única para actualizar la descripción (dm)
    $id_descripcion = $conexion->real_escape_string($_POST['Id']);
    // Id_medicamento es la clave para actualizar el nombre (m)
    $id_medicamento = $conexion->real_escape_string($_POST['Id_medicamento']);

    // 2. Recoger y sanitizar los datos del medicamento
    $nombre_medicamento = $conexion->real_escape_string(trim($_POST['medicamento']));
    
    // 3. Recoger y sanitizar los datos de la descripción
    $id_presentacion = $conexion->real_escape_string($_POST['presentacion']);
    $cantidad_unidad_medida = $conexion->real_escape_string($_POST['cantidad_unidad_medida']);
    $tipo_unidad = $conexion->real_escape_string($_POST['tipo_medida']);
    $via_aplicacion = $conexion->real_escape_string($_POST['via']);
    $almacenamiento = $conexion->real_escape_string($_POST['almacenamiento']);
    $composicion = $conexion->real_escape_string(trim($_POST['composicion']));
    
    // Inicializar una bandera para el estado de la operación
    $actualizacion_exitosa = true;

    // ==========================================================
    // PARTE 1: Actualizar el nombre en la tabla 'medicamento'
    // ==========================================================
    $sql_medicamento = "UPDATE medicamento SET 
                        nombre_medicamento = '$nombre_medicamento'
                        WHERE Id_medicamento = '$id_medicamento'";

    if (!$conexion->query($sql_medicamento)) {
        $actualizacion_exitosa = false;
        // Opcional: registrar el error de la base de datos
        // echo "Error al actualizar medicamento: " . $conexion->error; 
    }

    // ====================================================================
    // PARTE 2: Actualizar los detalles en la tabla 'descripcion_medicamento'
    // Usamos Id_descripcion para actualizar la fila específica
    // ====================================================================
    if ($actualizacion_exitosa) {
        $sql_descripcion = "UPDATE descripcion_medicamento SET    
                            cantidad_unidad_medida = '$cantidad_unidad_medida',
                            via_aplicacion = '$via_aplicacion',
                            almacenamiento = '$almacenamiento',
                            composicion = '$composicion',
                            Id_unidad = '$tipo_unidad',
                            Id_presentacion = '$id_presentacion'
                            WHERE Id = '$id_descripcion'";

        if (!$conexion->query($sql_descripcion)) {
            $actualizacion_exitosa = false;
            // Opcional: registrar el error de la base de datos
            // echo "Error al actualizar descripción: " . $conexion->error;
        }
    }

    // ==========================================================
    // PARTE 3: Manejo de la respuesta y redirección
    // ==========================================================
    $conexion->close();

    // Redireccionar al listado de insumos con un mensaje de estado
    if ($actualizacion_exitosa) {
        // Asumo que tu listado está en '../farmacia_insumos.php'
        $_SESSION['mensaje_user_exito'] = '✅ Éxito: El medicamento ha sido actualizado correctamente.';
        header("Location: ../../pages/php/farmacia_medicamentos_listado.php");
        exit();
    } else {
        // Redireccionar con un mensaje de error
        $_SESSION['mensaje_user_error'] = '❌ Error de Actualización: ' . $e->getMessage();
        header("Location: ../../pages/php/farmacia_medicamentos_listado.php");
        exit();
    }

} else {
    // Si alguien intenta acceder al script directamente
    header("Location: ../../pages/php/farmacia_medicamentos_listado.php");
    exit();
}

?>