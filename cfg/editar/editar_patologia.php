<?PHP
 session_start();
 // Include config file
 include ("../conexion.php");
 
    $id = $_POST['Id'] ?? 0;
    $nombre_patologia = $_POST['nombre_patologia'] ?? '';
    $codigo_cie = $_POST['codigo_cie'] ?? '';
    $contagiosa = $_POST['enfermedad_contagiosa'] ?? 'NO'; // Asegurar valor por defecto
    // NUEVO: Obtener los IDs de los síntomas seleccionados (string separado por comas)
    $sintomas_ids_string = $_POST['sintomas_ids'] ?? ''; 

    if ($id <= 0 || empty($nombre_patologia) || empty($codigo_cie)) {
        $_SESSION['mensaje_user_error'] = '❌ Error: Datos incompletos o ID de patología no válida para actualizar.';
        header('location: ../../pages/php/salud_patologias_listado.php');
        exit();
    }
    
    // Convertir la cadena de IDs a un array de enteros válidos
    $sintomas_ids = array_filter(array_map('intval', explode(',', $sintomas_ids_string)));
    
    // =========================================================================
    // 1. ACTUALIZAR TABLA PRINCIPAL (patologias)
    // =========================================================================
    
    $sql_update_patologia = "UPDATE patologias SET 
            nombre_patologia = ?,
            codigo_cie = ?,
            contagioso = ?
            WHERE Id_patologia = ?";
            
    $stmt_patologia = $conexion->prepare($sql_update_patologia);
    
    if ($stmt_patologia === false) {
        $_SESSION['mensaje_user_error'] = '❌ Error de BD: No se pudo preparar la consulta de edición (Patología). Detalle: ' . $conexion->error;
        header('location: ../../pages/php/salud_patologias_listado.php');
        exit();
    }

    $stmt_patologia->bind_param("sssi", $nombre_patologia, $codigo_cie, $contagiosa, $id);

    if ($stmt_patologia->execute()) {
        $stmt_patologia->close(); // Cerrar el statement de UPDATE

        // =====================================================================
        // 2. ACTUALIZAR SÍNTOMAS ASOCIADOS (patologias_sintomas)
        // La tabla de asociación se asume como 'patologias_sintomas'
        // =====================================================================

        // 2a. ELIMINAR todas las asociaciones existentes para esta patología
        $sql_delete_sintomas = "DELETE FROM detalle_patologia_sintomas WHERE Id_patologia = ?";
        $stmt_delete = $conexion->prepare($sql_delete_sintomas);
        $stmt_delete->bind_param("i", $id);
        
        if (!$stmt_delete->execute()) {
            $_SESSION['mensaje_user_error'] = '❌ Error de Actualización: La Patología se actualizó, pero falló la limpieza de los síntomas anteriores. Detalle: ' . $stmt_delete->error;
            $stmt_delete->close();
            $conexion->close();
            header('location: ../../pages/php/salud_patologias_listado.php');
            exit();
        }
        $stmt_delete->close();

        // 2b. INSERTAR las nuevas asociaciones
        $success_sintomas = true;
        
        if (!empty($sintomas_ids)) {
            $sql_insert_sintoma = "INSERT INTO detalle_patologia_sintomas (Id_patologia, Id_sintoma) VALUES (?, ?)";
            $stmt_insert = $conexion->prepare($sql_insert_sintoma);
            
            if ($stmt_insert === false) {
                 $_SESSION['mensaje_user_error'] = '❌ Error de BD: La Patología se actualizó, pero falló al preparar la inserción de síntomas. Detalle: ' . $conexion->error;
                 $conexion->close();
                 header('location: ../../pages/php/patologias_listado.php');
                 exit();
            }
            
            foreach ($sintomas_ids as $sintoma_id) {
                // 'ii' significa dos enteros (Id_patologia, Id_sintomas)
                $stmt_insert->bind_param("ii", $id, $sintoma_id); 
                if (!$stmt_insert->execute()) {
                    $success_sintomas = false;
                    // Detener la inserción al primer error
                    break; 
                }
            }
            $stmt_insert->close();
        }

        if ($success_sintomas) {
            // Éxito total
            $_SESSION['mensaje_user_exito'] = '✅ Éxito: La Patología ' . $nombre_patologia . ' (' . $codigo_cie . ') fue actualizada correctamente.';
            
        } else {
            // Error en la inserción de síntomas
             $_SESSION['mensaje_user_error'] = '⚠️ Advertencia: Patología principal actualizada, pero falló la inserción de los síntomas. Detalles: ' . $conexion->error;
             header('location: ../../pages/php/salud_patologias_listado.php.php');
        }
        
    } else {
        // Error al ejecutar la consulta de actualización principal
        $_SESSION['mensaje_user_error'] = '❌ Error de Actualización: No se pudo actualizar la patología principal. Detalle: ' . $stmt_patologia->error;
        header('location: ../../pages/php/salud_patologias_listado.php.php');
    }
    
    $conexion->close();
    header('location: ../../pages/php/salud_patologias_listado.php');
    exit();
?>