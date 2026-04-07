<?php
 session_start();
 // Include config file
 require_once "../conexion.php";

 // --- 1. Recolección y Sanitización de Datos ---
 $id_medico = (int) ($_POST['Id'] ?? 0); // ID principal para el UPDATE

 // Datos Personales
 $tipo_cedula = $_POST['tipo_cedula'] ?? '';
 $cedula = $_POST['cedula'] ?? '';
 $nombre = $_POST['nombre'] ?? '';
 $apellido = $_POST['apellido'] ?? '';
 $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
 $genero = $_POST['genero'] ?? '';
 $email = $_POST['correo'] ?? null;
 $estado = 1;

 // Datos del Médico, Departamento y Especialidad (NUEVO CAMPO)
 $fecha_ingreso = $_POST['fecha_ingreso'] ?? '';
 $area = (int) ($_POST['area'] ?? 0); // Id_departamento
 $especialidad = (int) ($_POST['especialidad'] ?? 0); // Id_especialidad (NUEVO)
 
 // Teléfonos
 $prefijo = $_POST['prefijo'] ?? '';
 $telefono = $_POST['telefono'] ?? '';

 if ($id_medico <= 0) {
    // Usar mensaje de sesión para error
    $_SESSION['mensaje_user_error'] = '❌ Error: Se requiere una ID de médico válida para la actualización.';
    header('location: ../../pages/php/rh_medico_listado.php');
    exit;
 }

 // --- 2. Inicio de la Transacción ---
 $conexion->begin_transaction();

 try {
    // 2.1. UPDATE en PERSONA (Datos personales)
    $sql_medico = "UPDATE persona 
                   SET nombre = ?, apellido = ?, tipo_cedula = ?, cedula = ?, fecha_nacimiento = ?, genero = ?, email = ?, estatus = ? 
                   WHERE id = ?";
    $stmt_medico = $conexion->prepare($sql_medico);
    
    if (!$stmt_medico) {
        throw new Exception("Error al preparar UPDATE en persona: " . $conexion->error);
    }
    
    $stmt_medico->bind_param("sssssssii", 
        $nombre, $apellido, $tipo_cedula, $cedula, $fecha_nacimiento, $genero, $email, $estado, $id_medico);
    
    if (!$stmt_medico->execute()) {
        throw new Exception("Error al actualizar persona (Médico): " . $stmt_medico->error);
    }
    $stmt_medico->close();


    // 2.2. UPDATE en DETALLE_MEDICO (Fecha de ingreso)
    $sql_detalle_medico = "UPDATE detalle_medico SET fecha_ingreso = ? WHERE Id_persona = ?";
    $stmt_detalle_medico = $conexion->prepare($sql_detalle_medico);
    
    if (!$stmt_detalle_medico) {
        throw new Exception("Error al preparar UPDATE en detalle_medico: " . $conexion->error);
    }
    
    $stmt_detalle_medico->bind_param("si", $fecha_ingreso, $id_medico);
    
    if (!$stmt_detalle_medico->execute()) {
        throw new Exception("Error al actualizar detalle médico: " . $stmt_detalle_medico->error);
    }
    $stmt_detalle_medico->close();


    // --- 2.3. Obtener el Id_detalle_medico (NECESARIO PARA DEPARTAMENTOS Y ESPECIALIDADES) ---
    $id_detalle_medico = 0;
    $sql_get_detalle_id = "SELECT Id_detalle_medico FROM detalle_medico WHERE Id_persona = ?";
    $stmt_get_detalle_id = $conexion->prepare($sql_get_detalle_id);
    
    if (!$stmt_get_detalle_id) {
        throw new Exception("Error al preparar SELECT Id_detalle_medico: " . $conexion->error);
    }
    
    $stmt_get_detalle_id->bind_param("i", $id_medico);
    $stmt_get_detalle_id->execute();
    $res_detalle = $stmt_get_detalle_id->get_result();
    
    if ($res_detalle->num_rows > 0) {
        $id_detalle_medico = $res_detalle->fetch_assoc()['Id_detalle_medico'];
    } else {
        throw new Exception("No se encontró Id_detalle_medico para el médico $id_medico.");
    }
    $stmt_get_detalle_id->close();


    // --- 2.4. Gestión del Departamento (MEDICOS_DEPARTAMENTOS) ---
    if ($id_detalle_medico > 0) {
        // Usamos REPLACE INTO para actualizar o insertar la relación Departamento
        $sql_medicos_departamentos = "REPLACE INTO medicos_departamentos(Id_departamento, Id_detalle_medico) VALUES(?, ?)";
        $stmt_medicos_departamentos = $conexion->prepare($sql_medicos_departamentos);

        if (!$stmt_medicos_departamentos) {
            throw new Exception("Error al preparar REPLACE en medicos_departamentos: " . $conexion->error);
        }

        $stmt_medicos_departamentos->bind_param("ii", $area, $id_detalle_medico);
        
        if (!$stmt_medicos_departamentos->execute()) {
            throw new Exception("Error al actualizar médicos/departamentos: " . $stmt_medicos_departamentos->error);
        }
        $stmt_medicos_departamentos->close();
    }
    
    
    // --- 2.5. Gestión de la Especialidad (ESPECIALIDADES_MEDICOS) ---
    if ($id_detalle_medico > 0 && $especialidad > 0) {
        // Primero BORRAR todas las especialidades anteriores del médico
        $sql_delete_esp = "DELETE FROM especialidades_medicos WHERE Id_detalle_medico = ?";
        $stmt_delete_esp = $conexion->prepare($sql_delete_esp);
        $stmt_delete_esp->bind_param("i", $id_detalle_medico);
        $stmt_delete_esp->execute();
        $stmt_delete_esp->close();
        
        // Luego INSERTAR la nueva especialidad
        $sql_insert_esp = "INSERT INTO especialidades_medicos(Id_especialidad, Id_detalle_medico) VALUES(?, ?)";
        $stmt_insert_esp = $conexion->prepare($sql_insert_esp);
        
        if (!$stmt_insert_esp) {
            throw new Exception("Error al preparar INSERT en especialidades_medicos: " . $conexion->error);
        }

        $stmt_insert_esp->bind_param("ii", $especialidad, $id_detalle_medico);
        
        if (!$stmt_insert_esp->execute()) {
            throw new Exception("Error al insertar especialidad: " . $stmt_insert_esp->error);
        }
        $stmt_insert_esp->close();
    }


    // 2.6. UPDATE/REPLACE en TELEFONOS_PERSONAS
    $sql_telefono = "UPDATE telefonos_personas 
    SET Id_prefijo = ?, telefono = ?, estatus = ? 
    WHERE Id_persona = ?";
    $stmt_telefono = $conexion->prepare($sql_telefono);
    $stmt_telefono->bind_param("isii", $prefijo, $telefono,$estado,$id_medico);
    
    if (!$stmt_telefono->execute()) {
        throw new Exception("Error al actualizar teléfono: " . $stmt_telefono->error);
    }
    $stmt_telefono->close();

    // --- 3. Commit y Redirección Final ---
    $conexion->commit();
    
    // Mensaje de Éxito
    $_SESSION['mensaje_user_exito'] = '✅ Éxito: El médico ' . $nombre . ' ' . $apellido . ' ha sido actualizado correctamente.';

    header('location: ../../pages/php/rh_medico_listado.php');
    exit();

 } catch (Exception $e) {
    // 4. Rollback y Mensaje de Error
    $conexion->rollback();

    error_log("Error de transacción al actualizar el médico: " . $e->getMessage()); 
    // Mensaje de Error
    $_SESSION['mensaje_user_error'] = '❌ Error de Actualización: No se pudo actualizar el médico. Detalle: ' . $e->getMessage();

    header('location: ../../pages/php/rh_medico_listado.php');
    exit();
 }

 $conexion->close();
?>