<?php
 session_start();
 // Include config file
 require_once "../conexion.php";

 // --- 1. Recolección y Sanitización de Datos ---
 // Los datos se recogen de $_POST.
 $tipo_cedula = $_POST['tipo_cedula'] ?? '';
 $cedula = $_POST['cedula'] ?? '';
 $nombre = $_POST['nombre'] ?? '';
 $apellido = $_POST['apellido'] ?? '';
 $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
 $genero = $_POST['genero'] ?? '';
 $email = $_POST['correo'] ?? null; // Puede ser NULL
 $fecha_ingreso = $_POST['fecha_ingreso'] ?? '';
 $area = $_POST['area'] ?? '';
 $especialidad = $_POST['especialidad'] ?? '';
 $prefijo = $_POST['prefijo'] ?? '';
 $telefono = $_POST['telefono'] ?? '';

 $estado = 1;
 $rol = 4;
 
 // --- 2. Inicio de la Transacción ---
 $conexion->begin_transaction();

 try {
    $id_medico = 0;
    
    // 2.1. INSERT en PERSONA (Médico)
    // Usa sentencias preparadas para mayor seguridad (Inyección SQL)
    $sql_medico = "INSERT INTO persona(nombre, apellido, tipo_cedula, cedula, fecha_nacimiento, genero, email, estatus) 
                   VALUES(?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_medico = $conexion->prepare($sql_medico);
    
    if (!$stmt_medico) {
        throw new Exception("Error al preparar INSERT en persona: " . $conexion->error);
    }
    
    $stmt_medico->bind_param("sssssssi", $nombre, $apellido, $tipo_cedula, $cedula, $fecha_nacimiento, $genero, $email, $estado);
    
    if (!$stmt_medico->execute()) {
        throw new Exception("Error al insertar persona (Médico): " . $stmt_medico->error);
    }
    $id_medico = $conexion->insert_id;
    $stmt_medico->close();


    // 2.2. INSERT en DETALLE_MEDICO
    $sql_detalle_medico = "INSERT INTO detalle_medico(fecha_ingreso, Id_persona)
                           VALUES(?, ?)";
    $stmt_detalle_medico = $conexion->prepare($sql_detalle_medico);
    
    if (!$stmt_detalle_medico) {
        throw new Exception("Error al preparar INSERT en detalle_medico: " . $conexion->error);
    }
    
    $stmt_detalle_medico->bind_param("si", $fecha_ingreso, $id_medico);
    
    if (!$stmt_detalle_medico->execute()) {
        throw new Exception("Error al insertar detalle médico: " . $stmt_detalle_medico->error);
    }
    
    // **OBTENER LA ID DEL DETALLE_MEDICO** (Punto solicitado)
    $id_detalle_medico = $conexion->insert_id;
    $stmt_detalle_medico->close();


    // 2.3. INSERT en MEDICOS_DEPARTAMENTOS
    // Usa la ID obtenida en el paso anterior ($id_detalle_medico)
    $sql_medicos_departamentos = "INSERT INTO medicos_departamentos(Id_departamento, Id_detalle_medico)
                                  VALUES(?, ?)";
    $stmt_medicos_departamentos = $conexion->prepare($sql_medicos_departamentos);

    if (!$stmt_medicos_departamentos) {
        throw new Exception("Error al preparar INSERT en medicos_departamentos: " . $conexion->error);
    }

    $stmt_medicos_departamentos->bind_param("ii", $area, $id_detalle_medico);
    
    if (!$stmt_medicos_departamentos->execute()) {
        throw new Exception("Error al insertar médicos/departamentos: " . $stmt_medicos_departamentos->error);
    }
    $stmt_medicos_departamentos->close();

    // 2.4. INSERT en MEDICOS_DEPARTAMENTOS
    // Usa la ID obtenida en el paso anterior ($id_detalle_medico)
    $sql_especialidades_medicos = "INSERT INTO especialidades_medicos(Id_especialidad, Id_detalle_medico)
                                  VALUES(?, ?)";
    $stmt_especialidades_medicos = $conexion->prepare($sql_especialidades_medicos);

    if (!$stmt_especialidades_medicos) {
        throw new Exception("Error al preparar INSERT en especialidades_medicos: " . $conexion->error);
    }

    $stmt_especialidades_medicos->bind_param("ii", $especialidad, $id_detalle_medico);
    
    if (!$stmt_especialidades_medicos->execute()) {
        throw new Exception("Error al insertar especialidad: " . $stmt_especialidades_medicos->error);
    }
    $stmt_especialidades_medicos->close();


    // 2.4.2 INSERT en TELEFONOS_PERSONAS
    $sql_telefono = "INSERT INTO telefonos_personas(Id_prefijo, telefono, Id_persona, estatus)
                     VALUES(?, ?, ?, ?)";
    $stmt_telefono = $conexion->prepare($sql_telefono);

    if (!$stmt_telefono) {
        throw new Exception("Error al preparar INSERT en telefonos_personas: " . $conexion->error);
    }

    $stmt_telefono->bind_param("sisi", $prefijo, $telefono, $id_medico, $estado);
    
    if (!$stmt_telefono->execute()) {
        throw new Exception("Error al insertar teléfono: " . $stmt_telefono->error);
    }
    $stmt_telefono->close();


    // 2.5. INSERT en DETALLE_PERSONA_ROL
    $sql_rol = "INSERT INTO detalle_persona_rol(Id_persona, Id_rol, estatus)
                VALUES(?, ?, ?)";
    $stmt_rol = $conexion->prepare($sql_rol);

    if (!$stmt_rol) {
        throw new Exception("Error al preparar INSERT en detalle_persona_rol: " . $conexion->error);
    }

    $stmt_rol->bind_param("iii", $id_medico, $rol, $estado);

    if (!$stmt_rol->execute()) {
        throw new Exception("Error al insertar rol: " . $stmt_rol->error);
    }
    $stmt_rol->close();


    // 3. Commit y Redirección Final
    $conexion->commit();
    
    // Mensaje de Éxito
    $cedula_info = $tipo_cedula . '-' . $cedula; 
    $_SESSION['mensaje_user_exito'] = '✅ Éxito: El Médico ' . $nombre . ' ' . $apellido . ' (' . $cedula_info . ') fue agregado correctamente.';

    header('location: ../../pages/php/rh_medico_listado.php');
    exit();

 } catch (Exception $e) {
    // 4. Rollback y Mensaje de Error
    $conexion->rollback();

    error_log("Error de transacción al agregar el médico: " . $e->getMessage()); 
    // Mensaje de Error
    $_SESSION['mensaje_user_error'] = '❌ Error de Registro: No se pudo registrar el médico. Detalle: ' . $e->getMessage();

    header('location: ../../pages/php/rh_medico_listado.php');
    exit();
 }

 $conexion->close();
?>