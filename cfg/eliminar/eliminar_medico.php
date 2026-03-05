<?php
 session_start();
 // Include config file
 require_once "../conexion.php";

 // --- 1. Recolección de Datos (ID del Médico) ---
 // La ID se recibe directamente y debe ser un número entero.
 $id_medico = $_POST['Id'] ?? $_GET['Id'] ?? null;

 // Convertir a entero para mayor seguridad
 $id_medico = (int) $id_medico; 

 if ($id_medico <= 0) {
    $_SESSION['mensaje_user_error'] = '❌ Error: Se requiere una ID de médico válida para la eliminación.';
    header('location: ../../pages/php/papelera/rh_medico_papelera_listado.php');
    exit;
 }

 // --- 2. Obtener IDs Dependientes y Cédula para confirmación ---

 // a. Obtener el ID del DETALLE_MEDICO (necesario para medicos_departamentos y especialidades_medicos)
 $id_detalle_medico = 0;
 $sql_get_detalle_id = "SELECT Id_detalle_medico FROM detalle_medico WHERE Id_persona = ?";
 $stmt_get_detalle_id = $conexion->prepare($sql_get_detalle_id);

 if (!$stmt_get_detalle_id) {
    // Si la preparación falla, redirigimos con un error de BD.
    $_SESSION['mensaje_user_error'] = '❌ Error de BD: Fallo al preparar la consulta de detalle_medico.';
    header('location: ../../pages/php/papelera/rh_medico_papelera_listado.php');
    exit;
 }
 
 $stmt_get_detalle_id->bind_param("i", $id_medico);
 $stmt_get_detalle_id->execute();
 $res_detalle = $stmt_get_detalle_id->get_result();

 if ($res_detalle->num_rows === 0) {
    $id_detalle_medico = 0; 
 } else {
    $id_detalle_medico = $res_detalle->fetch_assoc()['Id_detalle_medico'];
 }
 $stmt_get_detalle_id->close();


 // b. Obtener Cédula para el mensaje de éxito
 $cedula_info = '';
 $sql_get_cedula = "SELECT tipo_cedula, cedula FROM persona WHERE id = ? LIMIT 1";
 $stmt_get_cedula = $conexion->prepare($sql_get_cedula);
 $stmt_get_cedula->bind_param("i", $id_medico);
 $stmt_get_cedula->execute();
 $res_cedula = $stmt_get_cedula->get_result();

 if ($res_cedula->num_rows === 0) {
    // Si la persona no existe, detenemos.
    $_SESSION['mensaje_user_error'] = '❌ Error: El médico con ID ' . $id_medico . ' no fue encontrado en el sistema.';
    header('location: ../../pages/php/papelera/rh_medico_papelera_listado.php');
    $stmt_get_cedula->close();
    $conexion->close();
    exit();
 }
 $info = $res_cedula->fetch_assoc();
 $cedula_info = $info['tipo_cedula'] . '-' . $info['cedula'];
 $stmt_get_cedula->close();


 // --- 3. Inicio de la Transacción de Eliminación ---
 $conexion->begin_transaction();

 try {
    // 3.1. Eliminar MEDICOS_DEPARTAMENTOS (Depende de detalle_medico)
    if ($id_detalle_medico > 0) {
        $sql_del_med_dep = "DELETE FROM medicos_departamentos WHERE Id_detalle_medico = ?";
        $stmt_del_med_dep = $conexion->prepare($sql_del_med_dep);
        $stmt_del_med_dep->bind_param("i", $id_detalle_medico);
        if (!$stmt_del_med_dep->execute()) {
            throw new Exception("Error al eliminar médicos/departamentos: " . $stmt_del_med_dep->error);
        }
        $stmt_del_med_dep->close();
    }
    
    // 3.1.5. Eliminar ESPECIALIDADES_MEDICOS (Depende de detalle_medico)
    if ($id_detalle_medico > 0) {
        $sql_del_esp = "DELETE FROM especialidades_medicos WHERE Id_detalle_medico = ?";
        $stmt_del_esp = $conexion->prepare($sql_del_esp);
        $stmt_del_esp->bind_param("i", $id_detalle_medico);
        if (!$stmt_del_esp->execute()) {
            throw new Exception("Error al eliminar especialidades del médico: " . $stmt_del_esp->error);
        }
        $stmt_del_esp->close();
    }

    // 3.2. Eliminar DETALLE_MEDICO (Depende de persona)
    if ($id_detalle_medico > 0) {
        $sql_del_detalle_medico = "DELETE FROM detalle_medico WHERE Id_detalle_medico = ?";
        $stmt_del_detalle_medico = $conexion->prepare($sql_del_detalle_medico);
        $stmt_del_detalle_medico->bind_param("i", $id_detalle_medico);
        if (!$stmt_del_detalle_medico->execute()) {
            throw new Exception("Error al eliminar detalle médico: " . $stmt_del_detalle_medico->error);
        }
        $stmt_del_detalle_medico->close();
    }

    // 3.3. Eliminar TELEFONOS_PERSONAS (Depende de persona)
    $sql_del_telefono = "DELETE FROM telefonos_personas WHERE Id_persona = ?";
    $stmt_del_telefono = $conexion->prepare($sql_del_telefono);
    $stmt_del_telefono->bind_param("i", $id_medico);
    if (!$stmt_del_telefono->execute()) {
        throw new Exception("Error al eliminar teléfono: " . $stmt_del_telefono->error);
    }
    $stmt_del_telefono->close();

    // 3.4. Eliminar DETALLE_PERSONA_ROL (Depende de persona)
    $sql_del_rol = "DELETE FROM detalle_persona_rol WHERE Id_persona = ?";
    $stmt_del_rol = $conexion->prepare($sql_del_rol);
    $stmt_del_rol->bind_param("i", $id_medico);
    if (!$stmt_del_rol->execute()) {
        throw new Exception("Error al eliminar rol: " . $stmt_del_rol->error);
    }
    $stmt_del_rol->close();

    // 3.5. ELIMINACIÓN FINAL: Eliminar al Médico de la tabla PERSONA
    $sql_del_medico = "DELETE FROM persona WHERE id = ?";
    $stmt_del_medico = $conexion->prepare($sql_del_medico);
    $stmt_del_medico->bind_param("i", $id_medico);
    if (!$stmt_del_medico->execute()) {
        throw new Exception("Error al eliminar persona (Médico): " . $stmt_del_medico->error);
    }
    $stmt_del_medico->close();

    // --- 4. Commit y Redirección Final ---
    $conexion->commit();
    
    // Redirección al éxito
    $_SESSION['mensaje_user_exito'] = '🗑️ Eliminación Exitosa: El médico con cédula ' . $cedula_info . ' ha sido eliminado permanentemente.';
    
    header('location: ../../pages/php/papelera/rh_medico_papelera_listado.php');
    exit();

 } catch (Exception $e) {
    // 5. Rollback y Mensaje de Error
    $conexion->rollback();

    error_log("Error de transacción al eliminar médico por ID: " . $e->getMessage()); 

    $_SESSION['mensaje_user_error'] = '❌ Error de Eliminación: No se pudo eliminar el médico. Detalle: ' . $e->getMessage();
    header('location: ../../pages/php/papelera/rh_medico_papelera_listado.php');
    exit();
 }

 $conexion->close();
?>