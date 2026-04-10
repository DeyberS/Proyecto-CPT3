<?php
session_start();
include('../conexion.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Recolección de datos del formulario
    $nombre_medicamento = $_POST['medicamento'];
    $id_presentacion            = $_POST['presentacion']; // ID de tipo_medicamento
    $via_aplicacion     = $_POST['via'];
    $contenido_neto       = $_POST['contenido_neto'];
    $almacenamiento     = $_POST['almacenamiento'];
    $excipientes  = $_POST['excipientes']; // Resumen textual
    $id_laboratorio     = !empty($_POST['laboratorio']) ? (int)$_POST['laboratorio'] : null;
    $cantidad_concentracion = !empty($_POST['cantidad_concentracion']) ? $_POST['cantidad_concentracion'] : null;
    $id_tipo_concentracion = !empty($_POST['tipo_concentracion']) ? $_POST['tipo_concentracion'] : null;
    
    // Si el código de barras viene vacío, le ponemos un 0 para que no choque con el INT de la BD
    $codigo_barras      = !empty($_POST['codigo_barras']) ? (string)$_POST['codigo_barras'] : "Ninguno";

    // Datos detallados de principios activos (viene separado por | y ,)
    $principios_raw     = $_POST['composicion_detallada']; 
    $patologias_raw     = $_POST['patologias_seleccionadas']; 

    // Iniciar Transacción
    $conexion->begin_transaction();

    try {
        // PASO 1: Insertar en la tabla 'medicamento'
        $stmt1 = $conexion->prepare("INSERT INTO medicamento (nombre_medicamento, estatus) VALUES (?, 1)");
        $stmt1->bind_param("s", $nombre_medicamento);
        if (!$stmt1->execute()) {
            throw new Exception("Error al guardar el medicamento: " . $stmt1->error);
        }
        $id_medicamento_generado = $stmt1->insert_id;

        // PASO 2: Insertar en 'descripcion_medicamento'
        $stmt2 = $conexion->prepare("INSERT INTO descripcion_medicamento 
            (via_aplicacion, almacenamiento, excipientes, stock_minimo, stock_maximo, codigo_barras, contenido_neto, cantidad_concentracion, Id_tipo_concentracion, Id_laboratorio, Id_presentacion, Id_medicamento, estatus) 
            VALUES (?, ?, ?, '0', '0', ?, ?, ?, ?, ?, ?, ?, '1')");
        
        // Se cambió la primera 's' de codigo_barras por 'i' ya que ahora nos aseguramos de que sea entero
        $stmt2->bind_param("ssssssiiii", 
            $via_aplicacion, 
            $almacenamiento, 
            $excipientes, 
            $codigo_barras, 
            $contenido_neto,
            $cantidad_concentracion, 
            $id_tipo_concentracion,  
            $id_laboratorio, 
            $id_presentacion, 
            $id_medicamento_generado
        );
        
        if (!$stmt2->execute()) {
            throw new Exception("Error al guardar la descripción: " . $stmt2->error);
        }
        $id_descripcion_generada = $stmt2->insert_id;

        // PASO 3: Decodificar e Insertar Principios Activos Detallados
        if (!empty($principios_raw)) {
            $stmt3 = $conexion->prepare("INSERT INTO detalle_principio_medicamento 
                (id_medicamento, id_principio_activo, id_tipo_unidad_medida, cantidad_unidad_medida) 
                VALUES (?, ?, ?, ?)");

            // Separamos la cadena ej: "1,500,2|3,10,1"
            $filas_pa = explode('|', $principios_raw);
            
            foreach ($filas_pa as $fila) {
                $columnas = explode(',', $fila);
                
                // Asegurarnos de que vengan los 3 datos (id_pa, cantidad, id_unidad)
                if (count($columnas) == 3) {
                    $id_pa = (int)$columnas[0];
                    $cantidad = (int)$columnas[1];
                    $id_unidad = (int)$columnas[2];

                    $stmt3->bind_param("iiii", 
                        $id_descripcion_generada, 
                        $id_pa, 
                        $id_unidad, 
                        $cantidad
                    );
                    
                    if (!$stmt3->execute()) {
                         throw new Exception("Error al guardar principio activo: " . $stmt3->error);
                    }
                }
            }
        }

        // PASO 4: Decodificar e Insertar Patologías
        if (!empty($patologias_raw)) {
            // CORRECCIÓN: Solo 2 columnas, por lo tanto solo 2 signos de interrogación
            $stmt4 = $conexion->prepare("INSERT INTO detalle_patologia_medicamento 
            (Id_medicamento, Id_patologia) 
            VALUES (?, ?)");

            // Las patologías vienen separadas por | (ej: "1|4|15")
            $filas_pat = explode('|', $patologias_raw);

            foreach ($filas_pat as $id_pat) {
                // Limpiamos el valor para asegurarnos que sea un número
                $id_pat_limpio = (int)$id_pat;

                if ($id_pat_limpio > 0) {
                    // "ii" porque ambos son enteros (ID medicamento e ID patología)
                    $stmt4->bind_param(
                        "ii",
                        $id_descripcion_generada,
                        $id_pat_limpio
                    );

                    if (!$stmt4->execute()) {
                        throw new Exception("Error al guardar patología: " . $stmt4->error);
                    }
                }
            }
        }

        // Si todo salió bien, confirmar cambios
        $conexion->commit();
        $_SESSION['mensaje_user_exito'] = '✅ Éxito: El medicamento fue agregado correctamente.';
        header("location: ../../pages/php/farmacia_medicamentos_listado.php");
        exit();

    } catch (Exception $e) {
        $conexion->rollback();
        error_log("Error de transacción al agregar el area: " . $e->getMessage()); 
        $_SESSION['mensaje_user_error'] = '❌ Error de Registro: No se pudo registrar el area. Detalle: ' . $e->getMessage();
        header("location: ../../pages/php/farmacia_medicamentos_listado.php");
    }
}
?>