<?php
 session_start();
 // Include config file
 require_once "../conexion.php";

 $nombre_patologia = $_POST['nombre_patologia'] ?? '';
 $codigo_cie = $_POST['codigo_cie'] ?? '';
 $contagiosa = $_POST['enfermedad_contagiosa'] ?? 'NO';
 $sintomas_ids_string = $_POST['sintomas_ids'] ?? '';
 $estado = 1;
 
 // 1. VALIDACIÓN BÁSICA DE DATOS
 if (empty($nombre_patologia) || empty($codigo_cie)) {
    $_SESSION['mensaje_user_error'] = '❌ Error: El nombre y el código CIE-10 son obligatorios.';
    header("location: ../../pages/php/salud_patologias_listado.php");
    exit();
 }
 
 // Convertir la cadena de IDs a un array de enteros válidos
 $sintomas_ids = array_filter(array_map('intval', explode(',', $sintomas_ids_string)));
 
 // 2. INSERTAR PATOLOGÍA PRINCIPAL
 $sql_insert_patologia = "INSERT INTO patologias(nombre_patologia, codigo_cie, estatus, contagioso) 
                          VALUES(?, ?, ?, ?)";
 
 $stmt_patologia = $conexion->prepare($sql_insert_patologia);
 
 if ($stmt_patologia === false) {
    $_SESSION['mensaje_user_error'] = '❌ Error de BD: No se pudo preparar la consulta para agregar patología. Detalle: ' . $conexion->error;
    header("location: ../../pages/php/salud_patologias_listado.php");
    exit();
 }
 
 $stmt_patologia->bind_param("ssis", $nombre_patologia, $codigo_cie, $estado, $contagiosa);
 
 if ($stmt_patologia->execute()) {
    
    $nueva_patologia_id = $conexion->insert_id;
    $stmt_patologia->close();
    
    $success_sintomas = true;
    $sintoma_error_detail = ''; // NUEVO: Variable para capturar el detalle del error

    // 3. INSERTAR SÍNTOMAS ASOCIADOS (patologias_sintomas)
    if (!empty($sintomas_ids) && $nueva_patologia_id > 0) {
        $sql_insert_sintoma = "INSERT INTO detalle_patologia_sintomas (Id_patologia, Id_sintoma) VALUES (?, ?)";
        $stmt_insert_sintoma = $conexion->prepare($sql_insert_sintoma);
        
        if ($stmt_insert_sintoma === false) {
             $_SESSION['mensaje_user_error'] = '⚠️ Advertencia: Patología insertada, pero falló al preparar la inserción de síntomas. Detalle: ' . $conexion->error;
             $conexion->close();
             header('location: ../../pages/php/salud_patologias_listado.php');
             exit();
        }
        
        foreach ($sintomas_ids as $sintoma_id) {
            $stmt_insert_sintoma->bind_param("ii", $nueva_patologia_id, $sintoma_id); 
            if (!$stmt_insert_sintoma->execute()) {
                $success_sintomas = false;
                // CAPTURA DEL ERROR ESPECÍFICO
                $sintoma_error_detail = 'ID de Síntoma que falló: ' . $sintoma_id . ' | Error de MySQL: ' . $stmt_insert_sintoma->error;
                break; 
            }
        }
        $stmt_insert_sintoma->close();
    }

    // 4. MENSAJE FINAL
    if ($success_sintomas) {
        $_SESSION['mensaje_user_exito'] = '✅ Éxito: La Patología ' . $nombre_patologia . ' (' . $codigo_cie . ') fue agregada correctamente.';
    } else {
         // Se usa el detalle del error capturado
         $_SESSION['mensaje_user_error'] = '⚠️ Error: La Patología se agregó, pero falló la inserción de un síntoma. Razón: ' . $sintoma_error_detail;
         header("location: ../../pages/php/salud_patologias_agregar.php");
    }
    
 } else {
    $_SESSION['mensaje_user_error'] = '❌ Error de Registro: No se pudo registrar la patología. Detalle: ' . $stmt_patologia->error;
    header("location: ../../pages/php/salud_patologias_agregar.php");
 }

 $conexion->close();
 header("location: ../../pages/php/salud_patologias_listado.php");
 exit();
?>