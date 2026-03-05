<?php
session_start();

// Incluir el archivo de conexión (se asume que $conexion es tu objeto de conexión MySQLi)
require_once "../conexion.php";

// Función de redirección centralizada para errores
function redireccionar_error($mensaje, $conexion, $pagina_origen = 'pacientes_menores_listado.php') {
    // Si la conexión está activa y en transacción, hacer rollback antes de cerrar
    if ($conexion && $conexion->in_transaction) {
        $conexion->rollback();
    }
    // Almacenar el mensaje de error en la sesión
    $_SESSION['mensaje_user_error'] = $mensaje;
    // Redireccionar a la página de agregar (si es un error de validación) o al listado.
    header('location: ../../pages/php/' . $pagina_origen); // Modificado para usar la variable de sesión
    exit();
}

// Función de redirección centralizada para éxito
function redireccionar_exito($mensaje) {
    // Almacenar el mensaje de éxito en la sesión
    $_SESSION['mensaje_user_exito'] = $mensaje;
    // Redireccionar al listado
    header('location: ../../pages/php/pacientes_menores_listado.php'); // Redirección al listado
    exit();
}


// --- 1. Recolección y Sanitización de Datos ---
// Estado estático (asumido del template)
$estado = 1;
$rol_paciente = 3;
$rol_representante = 5;
$fecha_actual = date("Y-m-d H:i:s");

// Datos del MENOR
$tipo_documento_menor = $_POST['tipo_cedula'] ?? '';
$cedula_menor = $_POST['cedula'] ?? '';
$nombre_menor = $_POST['nombre'] ?? '';
$apellido_menor = $_POST['apellido'] ?? '';
$fecha_nacimiento = $_POST['fecha_nacimiento_menor'] ?? null;
$lugar_nacimiento = $_POST['municipio_nacimiento'] ?? ''; // Id_municipio
$genero = $_POST['genero'] ?? '';
$etnia = $_POST['etnia'] ?? 'No';
$tipo_etnia = $_POST['tipo_etnia'] ?? 'Ninguna';

// Escolaridad
$nivel_instruccion = $_POST['nivel_instruccion'] ?? null;
$mision = $_POST['mision'] ?? null;
$años_aprobados = $_POST['años_aprobados'] ?? 0;

// Salud y M:M
$grupo_sanguineo = $_POST['grupo_sanguineo'] ?? '';
$analfabeta = $_POST['analfabeta'] ?? 'No';
$discapacidad = $_POST['discapacidad'] ?? 'No';
$tipo_discapacidad = $_POST['tipo_discapacidad'] ?? 'Ninguna';
$patologias_ids_string = $_POST['patologias_ids'] ?? '';
$alergias_ids_string = $_POST['alergias_ids'] ?? '';

// Datos del REPRESENTANTE
$tipo_cedula_rep = $_POST['tipo_cedula_rep'] ?? '';
$cedula_rep = $_POST['cedula_rep'] ?? '';
$nombre_rep = $_POST['nombre_rep'] ?? '';
$apellido_rep = $_POST['apellido_rep'] ?? '';
$email_rep = $_POST['email_rep'] ?? '';
$genero_rep = $_POST['genero_rep'] ?? '';
$fecha_nacimiento_rep = $_POST['fecha_nacimiento_rep'] ?? null; 
$parentesco = $_POST['parentesco'] ?? '';

// Teléfonos del REPRESENTANTE
$prefijo_rep = $_POST['prefijo_rep'] ?? '';
$telefono_rep = $_POST['telefono_rep'] ?? '';

// Dirección del Paciente (Residencia)
$sector_residencia = $_POST['sector_menor'] ?? ''; // Id_sector
$avenida_calle = $_POST['avenida_calle'] ?? '';
$referencia_punto = $_POST['referencia'] ?? '';
$tiempo_residencia = $_POST['tiempo_residencia'] ?? '';
$tiempo = $_POST['tiempo'] ?? '';
$referencia = $referencia_punto;

$redireccion = $_POST['redireccion'] ?? '';

// --- 2. Validación de Cédula del Menor (Para evitar duplicados) ---

$sql_verificar_menor = "SELECT cedula FROM persona WHERE cedula = ? AND tipo_cedula = ? LIMIT 1";
$stmt_verificar_menor = $conexion->prepare($sql_verificar_menor);

if ($stmt_verificar_menor === false) {
    // Uso de la nueva función de error
    redireccionar_error("Error al preparar la verificación del menor: " . $conexion->error, $conexion);
}

if (empty($_POST['fecha_nacimiento_menor'])) {
    redireccionar_error("La fecha de nacimiento del menor es obligatoria.", $conexion);
}

$stmt_verificar_menor->bind_param("ss", $cedula_menor, $tipo_documento_menor);
$stmt_verificar_menor->execute();
$stmt_verificar_menor->store_result();

if ($stmt_verificar_menor->num_rows > 0) {
    $stmt_verificar_menor->close();
    $conexion->close();
    // Uso de la nueva función de error
    $mensaje_error = "Error: El documento $tipo_documento_menor-$cedula_menor ya se encuentra registrado. Por favor, verifique el número de documento.";
    redireccionar_error($mensaje_error, null, 'pacientes_menores_listado.php');
}
$stmt_verificar_menor->close();

// --- 3. Inicio de la Transacción ---
$conexion->begin_transaction();

try {
    // --- 4. Gestión del REPRESENTANTE (Lógica de Upsert) ---
    $id_representante = 0;
    
    // 4.1. Buscar si el Representante ya existe por Cédula
    $sql_check_rep = "SELECT Id FROM persona WHERE cedula = ? AND tipo_cedula = ? LIMIT 1";
    $stmt_check_rep = $conexion->prepare($sql_check_rep);
    $stmt_check_rep->bind_param("ss", $cedula_rep, $tipo_cedula_rep);
    $stmt_check_rep->execute();
    $res_check_rep = $stmt_check_rep->get_result();

    if ($res_check_rep->num_rows > 0) {
        // REPRESENTANTE ENCONTRADO
        $id_representante = $res_check_rep->fetch_assoc()['Id'];
        
        // Se actualizan SOLO los datos de PERSONA
        $sql_update_rep = "UPDATE persona SET nombre=?, apellido=?, email=?, genero=?, fecha_nacimiento=? WHERE Id=?";
        $stmt_update_rep = $conexion->prepare($sql_update_rep);
        $stmt_update_rep->bind_param("sssssi", $nombre_rep, $apellido_rep, $email_rep, $genero_rep, $fecha_nacimiento_rep, $id_representante);
        if (!$stmt_update_rep->execute()) {
             throw new Exception("Error al actualizar datos del representante existente: " . $stmt_update_rep->error);
        }
        $stmt_update_rep->close();

        
    } else {
        // REPRESENTANTE NUEVO: Insertar en PERSONA
        $sql_insert_rep = "INSERT INTO persona(nombre, apellido, tipo_cedula, cedula, email, genero, fecha_nacimiento, estatus) 
                           VALUES(?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert_rep = $conexion->prepare($sql_insert_rep);
        $stmt_insert_rep->bind_param("sssssssi", $nombre_rep, $apellido_rep, $tipo_cedula_rep, $cedula_rep, $email_rep, $genero_rep, $fecha_nacimiento_rep, $estado);
        
        if (!$stmt_insert_rep->execute()) {
            throw new Exception("Error al insertar persona (Representante): " . $stmt_insert_rep->error);
        }

        $id_representante = $conexion->insert_id;
        $stmt_insert_rep->close();

         // 4.7. INSERT en DETALLE_PERSONA_ROL
        $sql_rol_representante = "INSERT INTO detalle_persona_rol(Id_persona, Id_rol, estatus) VALUES(?, ?, ?)";
        $stmt_rol_representante = $conexion->prepare($sql_rol_representante);
        $stmt_rol_representante->bind_param("iii", $id_representante, $rol_representante, $estado);
        if (!$stmt_rol_representante->execute()) {
            throw new Exception("Error al insertar rol: " . $stmt_rol_representante->error);
        }
        $stmt_rol_representante->close();


        // 4.2. INSERT en TELEFONOS_PERSONAS (Representante) - SOLO SI ES NUEVO
        $sql_telefono_rep = "INSERT INTO telefonos_personas(Id_prefijo, telefono, Id_persona, estatus) 
                             VALUES(?, ?, ?, ?) 
                             ON DUPLICATE KEY UPDATE Id_prefijo = VALUES(Id_prefijo), telefono = VALUES(telefono)";
        $stmt_telefono_rep = $conexion->prepare($sql_telefono_rep);
        $stmt_telefono_rep->bind_param("sisi", $prefijo_rep, $telefono_rep, $id_representante, $estado);
        if (!$stmt_telefono_rep->execute()) {
            throw new Exception("Error al insertar teléfono (Representante): " . $stmt_telefono_rep->error);
        }
        $stmt_telefono_rep->close();
    }
    $stmt_check_rep->close();

    $id_paciente = 0;

    // 5.1. INSERT en PERSONA (Paciente Menor)
    $sql_paciente = "INSERT INTO persona(nombre, apellido, tipo_cedula, cedula, fecha_nacimiento, genero, estatus) 
                     VALUES(?, ?, ?, ?, ?, ?, ?)";
    $stmt_paciente = $conexion->prepare($sql_paciente);
    $stmt_paciente->bind_param("ssssssi", $nombre_menor, $apellido_menor, $tipo_documento_menor, $cedula_menor, $fecha_nacimiento, $genero, $estado);
    
    if (!$stmt_paciente->execute()) {
        throw new Exception("Error al insertar persona (Menor): " . $stmt_paciente->error);
    }
    $id_paciente = $conexion->insert_id;
    $stmt_paciente->close();


    // 5.2. INSERT en DETALLE_PACIENTE_MENOR
    // Nota: Corregido el bind_param, asumiendo que id_representante es INT (i). El archivo original tenía 7 placeholders y 8 variables en bind_param, lo cual causaría error, lo he corregido a 8 placeholders.
    $sql_detalle_menor = "INSERT INTO detalle_paciente_menor(parentesco, analfabeta, etnia, tipo_etnia, nivel_instruccion, mision, años_aprobados, discapacidad, tipo_discapacidad, id_persona, id_representante)
                             VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; 
    $stmt_detalle_menor = $conexion->prepare($sql_detalle_menor);
    $stmt_detalle_menor->bind_param("sssssssssii", $parentesco, $analfabeta, $etnia, $tipo_etnia, $nivel_instruccion, $mision, $años_aprobados, $discapacidad, $tipo_discapacidad, $id_paciente, $id_representante); 
    
    if (!$stmt_detalle_menor->execute()) {
        throw new Exception("Error al insertar detalle paciente menor: " . $stmt_detalle_menor->error);
    }
    $stmt_detalle_menor->close();


    // 5.3. INSERT en DIRECCION (Residencia del Menor)
    $sql_direccion = "INSERT INTO direccion(tiempo_residencia, tiempo, avenida_calle, referencia, Id_persona, Id_sector, estatus)
                      VALUES(?, ?, ?, ?, ?, ?, ?)";
    $stmt_direccion = $conexion->prepare($sql_direccion);
    $stmt_direccion->bind_param("ssssisi", $tiempo_residencia, $tiempo, $avenida_calle, $referencia, $id_paciente, $sector_residencia, $estado);
    if (!$stmt_direccion->execute()) {
        throw new Exception("Error al insertar dirección: " . $stmt_direccion->error);
    }
    $stmt_direccion->close();

    // 5.4. INSERT en LUGAR_NACIMIENTO
    $sql_lugar_nacimiento = "INSERT INTO lugar_nacimiento(Id_persona, Id_municipio) VALUES(?, ?)";
    $stmt_lugar_nacimiento = $conexion->prepare($sql_lugar_nacimiento);
    $stmt_lugar_nacimiento->bind_param("ii", $id_paciente, $lugar_nacimiento);
    if (!$stmt_lugar_nacimiento->execute()) {
        throw new Exception("Error al insertar lugar de nacimiento: " . $stmt_lugar_nacimiento->error);
    }
    $stmt_lugar_nacimiento->close();


    // 5.5. INSERT en HISTORIAL_MEDICO
    $sql_historia = "INSERT INTO historial_medico(fecha, Id_persona, grupo_sanguineo) VALUES(?, ?, ?)";
    $stmt_historia = $conexion->prepare($sql_historia);
    $stmt_historia->bind_param("sis", $fecha_actual, $id_paciente, $grupo_sanguineo);
    
    if (!$stmt_historia->execute()) {
        throw new Exception("Error al insertar historial médico: " . $stmt_historia->error);
    }
    $id_historial_medico = $conexion->insert_id; 
    $stmt_historia->close();

    // 5.6. INSERT en DETALLE_PERSONA_ROL (Paciente Menor)
    $sql_rol = "INSERT INTO detalle_persona_rol(Id_persona, Id_rol, estatus) VALUES(?, ?, ?)";
    $stmt_rol = $conexion->prepare($sql_rol);
    $stmt_rol->bind_param("iii", $id_paciente, $rol_paciente, $estado);
    if (!$stmt_rol->execute()) {
        throw new Exception("Error al insertar rol: " . $stmt_rol->error);
    }
    $stmt_rol->close();

    // --- 6. Manejo de Patologías y Alergias (M:M) ---
    // 6.1. Patologías (historial_patologias)
    if (!empty($patologias_ids_string)) {
        $patologias_array = array_filter(array_map('trim', explode(',', $patologias_ids_string)));
        
        $sql_patologia_m2m = "INSERT INTO historial_patologias(Id_patologia, Id_historial, Id_persona, estatus) VALUES(?, ?, ?, ?)";
        $stmt_patologia_m2m = $conexion->prepare($sql_patologia_m2m);
        
        if ($stmt_patologia_m2m === false) {
             throw new Exception("Error al preparar la inserción de patologías: " . $conexion->error);
        }

        foreach ($patologias_array as $patologia_id) {
            $stmt_patologia_m2m->bind_param("iiii", $patologia_id, $id_historial_medico, $id_paciente, $estado);
            if (!$stmt_patologia_m2m->execute()) {
                throw new Exception("Error al insertar patología ID $patologia_id: " . $stmt_patologia_m2m->error);
            }
        }
        $stmt_patologia_m2m->close();
    }

    // 6.2. Alergias (historial_alergias)
    if (!empty($alergias_ids_string)) {
        $alergias_array = array_filter(array_map('trim', explode(',', $alergias_ids_string)));

        $sql_alergia_m2m = "INSERT INTO historial_alergias(Id_alergia, Id_historial, Id_persona, estatus) VALUES(?, ?, ?, ?)";
        $stmt_alergia_m2m = $conexion->prepare($sql_alergia_m2m);
        
        if ($stmt_alergia_m2m === false) {
             throw new Exception("Error al preparar la inserción de alergias: " . $conexion->error);
        }

        foreach ($alergias_array as $alergia_id) {
            $stmt_alergia_m2m->bind_param("iiii", $alergia_id, $id_historial_medico, $id_paciente, $estado);
            if (!$stmt_alergia_m2m->execute()) {
                throw new Exception("Error al insertar alergia ID $alergia_id: " . $stmt_alergia_m2m->error);
            }
        }
        $stmt_alergia_m2m->close();
    }

    // --- 7. Commit y Redirección Final ---
    if ($_SESSION['nombre_rol'] === 'Medico - Usuario' && $redireccion === 'consulta' || $_SESSION['rol'] === '7' && $redireccion === 'consulta') {
        $conexion->commit();
        header('location: ../../pages/php/consulta_agregar.php');
        exit();
    } else if ($_SESSION['nombre_rol'] === 'Medico - Usuario' && $redireccion === 'cita' || $_SESSION['rol'] === '7' && $redireccion === 'cita' || $_SESSION['nombre_rol'] === 'Recepcionista' && $redireccion === 'cita'){
        $conexion->commit();
        header('location: ../../pages/php/citas_medicas_agregar.php');
    } else {
        $_SESSION['mensaje_user_exito'] = '✅ Éxito: El paciente ' . '' . $nombre . ' ' . $apellido . '' . ' fue agregado correctamente.';
        $conexion->commit();
        header('location: ../../pages/php/pacientes_menores_listado.php'); 
        exit();
    }

} catch (Exception $e) {
    // 8. Rollback y Mensaje de Error
    error_log("Error de transacción al agregar el paciente menor de edad: " . $e->getMessage()); 

    // Uso de la nueva función de error
    $_SESSION['mensaje_user_error'] = '❌ Error de Registro: No se pudo registrar el paciente. Detalle: ' . $e->getMessage();
    // Redirecciona a la lista para mostrar el mensaje
    header('location: ../../pages/php/pacientes_menores_listado.php');
}

$conexion->close();
?>