<?php
session_start();

// Incluir el archivo de conexión
require_once "../conexion.php";

// ******************************************************************
// 🛑 CONFIGURACIÓN DE ERRORES (QUITAR EN PRODUCCIÓN FINAL)
// ******************************************************************


// Función de redirección centralizada
function redireccionar_error($mensaje) {
    // Almacenar el mensaje de error en la sesión
    $_SESSION['mensaje_user_error'] = $mensaje;
    // Redireccionar al listado, o a la página de edición si deseas que vuelva allí
    header('location: ../../pages/php/pacientes_menores_listado.php'); 
    exit();
}

// --- 1. Recolección de Datos Clave para la Actualización ---
$id_paciente_a_actualizar = $_POST['Id'] ?? null; 

if (is_null($id_paciente_a_actualizar) || !is_numeric($id_paciente_a_actualizar)) {
    redireccionar_error("Error fatal: No se ha proporcionado un ID de paciente válido para actualizar.");
}

if ($conexion->connect_error) {
    redireccionar_error("Fallo CRÍTICO de conexión a la base de datos: " . $conexion->connect_error);
}

// --- 2. Recolección y Sanitización de Datos (Asegurando Tipos) ---
$estado = 1;
$fecha_actual = date("Y-m-d H:i:s");
$rol_representante = 5;

// Datos del MENOR
$tipo_documento_menor = $_POST['tipo_cedula'] ?? '';
$cedula_menor = $_POST['cedula'] ?? '';
$nombre_menor = $_POST['nombre'] ?? '';
$apellido_menor = $_POST['apellido'] ?? '';
$fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
$lugar_nacimiento = $_POST['municipio_nacimiento'] ?? 0; 
$lugar_nacimiento = is_numeric($lugar_nacimiento) ? intval($lugar_nacimiento) : 0; 
$genero = $_POST['genero'] ?? '';
$etnia = $_POST['etnia'] ?? 'No';
$tipo_etnia = $_POST['tipo_etnia'] ?? 'Ninguna';

// Escolaridad
$nivel_instruccion = $_POST['nivel_instruccion'] ?? null;
$mision = $_POST['mision'] ?? null;
$años_aprobados = $_POST['años_aprobados'] ?? 0;
$analfabeta = $_POST['analfabeta'] ?? 'No';

// Salud y M:M
$grupo_sanguineo = $_POST['grupo_sanguineo'] ?? 'Ninguno Conocido';
$discapacidad = $_POST['discapacidad'] ?? 'No';
$tipo_discapacidad = $_POST['tipo_discapacidad'] ?? 'Ninguna';
$patologias_ids_string = $_POST['patologias_ids'] ?? 'Ninguna Conocida';
$alergias_ids_string = $_POST['alergias_ids'] ?? 'Ninguna Conocida';

 // Datos del REPRESENTANTE
$tipo_cedula_rep = $_POST['tipo_cedula_rep'] ?? '';
$cedula_rep = $_POST['cedula_rep'] ?? '';
$nombre_rep = $_POST['nombre_rep'] ?? '';
$apellido_rep = $_POST['apellido_rep'] ?? '';
$email_rep = $_POST['email_rep'] ?? '';
$genero_rep = $_POST['genero_rep'] ?? '';
$fecha_nacimiento_rep = $_POST['fecha_nacimiento_rep'] ?? null;
$parentesco = $_POST['parentesco'] ?? '';

// Teléfonos del REPRESENTANTE (¡Punto crítico de errores de FK!)
$prefijo_rep = $_POST['prefijo_rep'] ?? 0; 
$prefijo_rep = is_numeric($prefijo_rep) ? intval($prefijo_rep) : 0; 
$telefono_rep = $_POST['telefono_rep'] ?? '';

// Dirección del Paciente (Residencia) (¡Punto crítico de errores de FK!)
$sector_residencia = $_POST['sector'] ?? 0; 
$sector_residencia = is_numeric($sector_residencia) ? intval($sector_residencia) : 0; 
$avenida_calle = $_POST['avenida_calle'] ?? 'N/A';
$referencia_punto = $_POST['referencia'] ?? 'N/A';
$tiempo_residencia = $_POST['tiempo_residencia'] ?? 'N/A';
$tiempo = $_POST['tiempo'] ?? null;
$referencia = $referencia_punto;

// --- 3. Inicio de la Transacción ---
$conexion->begin_transaction();

try {
    // 4.1. Obtener ID del Representante actual
    $sql_get_rep_id = "SELECT id_representante FROM detalle_paciente_menor WHERE id_persona = ?";
    $stmt_get_rep_id = $conexion->prepare($sql_get_rep_id);
    $stmt_get_rep_id->bind_param("i", $id_paciente_a_actualizar);
    $stmt_get_rep_id->execute();
    $stmt_get_rep_id->bind_result($id_representante_actual);
    $stmt_get_rep_id->fetch();
    $stmt_get_rep_id->close();
    
    $id_representante = $id_representante_actual ?? 0; 
    
    // --- 5. Gestión del REPRESENTANTE (Upsert) ---

    $sql_check_rep = "SELECT Id FROM persona WHERE cedula = ? AND tipo_cedula = ? LIMIT 1";
    $stmt_check_rep = $conexion->prepare($sql_check_rep);
    $stmt_check_rep->bind_param("ss", $cedula_rep, $tipo_cedula_rep);
    $stmt_check_rep->execute();
    $res_check_rep = $stmt_check_rep->get_result();

    if ($res_check_rep->num_rows > 0) {
        // REPRESENTANTE ENCONTRADO 
        $id_representante_nuevo = $res_check_rep->fetch_assoc()['Id'];

        $id_representante = $id_representante_nuevo;
    } else {

        // Opción B: Insertar REPRESENTANTE NUEVO
        $sql_insert_rep = "INSERT INTO persona(nombre, apellido, tipo_cedula, cedula, email, genero, fecha_nacimiento, estatus) 
                               VALUES(?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert_rep = $conexion->prepare($sql_insert_rep);
        $stmt_insert_rep->bind_param("sssssssi", $nombre_rep, $apellido_rep, $tipo_cedula_rep, $cedula_rep, $email_rep, $genero_rep, $fecha_nacimiento_rep, $estado);

        if (!$stmt_insert_rep->execute()) {
            throw new Exception("Error al insertar persona (Representante) (5.2B): " . $stmt_insert_rep->error);
        }
        $id_representante = $conexion->insert_id;
        $stmt_insert_rep->close();

        // 4.7. INSERT en DETALLE_PERSONA_ROL
        $sql_rol_representante = "INSERT INTO detalle_persona_rol(Id_persona, Id_rol, estatus) VALUES(?, ?, ?)";
        $stmt_rol_representante = $conexion->prepare($sql_rol_representante);
        $stmt_rol_representante->bind_param("iii",
            $id_representante,
            $rol_representante,
            $estado
        );
        if (!$stmt_rol_representante->execute()) {
            throw new Exception("Error al insertar rol: " . $stmt_rol_representante->error);
        }
        $stmt_rol_representante->close();

        // INSERT en TELEFONOS_PERSONAS (Representante Nuevo)
        $sql_insert_tel = "INSERT INTO telefonos_personas(Id_prefijo, telefono, Id_persona, estatus) VALUES(?, ?, ?, ?)";
        $stmt_insert_tel = $conexion->prepare($sql_insert_tel);
        $stmt_insert_tel->bind_param("iisi", $prefijo_rep, $telefono_rep, $id_representante, $estado);
        if (!$stmt_insert_tel->execute()) {
            throw new Exception("Error al insertar teléfono (Representante 5.2B): " . $stmt_insert_tel->error);
        }
        $stmt_insert_tel->close();
    }

    $stmt_check_rep->close();


    // --- 6. Actualización del Paciente Menor ---

    // 6.1. UPDATE en PERSONA (Menor)
    $sql_paciente = "UPDATE persona SET nombre=?, apellido=?, tipo_cedula=?, cedula=?, fecha_nacimiento=?, genero=? WHERE Id=?";
    $stmt_paciente = $conexion->prepare($sql_paciente);
    $stmt_paciente->bind_param("ssssssi", $nombre_menor, $apellido_menor, $tipo_documento_menor, $cedula_menor, $fecha_nacimiento, $genero, $id_paciente_a_actualizar);
    
    if (!$stmt_paciente->execute()) {
        throw new Exception("Error al actualizar persona (Menor) (6.1): " . $stmt_paciente->error);
    }
    $stmt_paciente->close();

    // 6.2. UPSERT en DETALLE_PACIENTE_MENOR
    $sql_detalle_menor = "INSERT INTO detalle_paciente_menor(parentesco, analfabeta, etnia, tipo_etnia, nivel_instruccion, mision, años_aprobados, discapacidad, tipo_discapacidad, id_persona, id_representante)
                          VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                          ON DUPLICATE KEY UPDATE parentesco=VALUES(parentesco), analfabeta=VALUES(analfabeta), etnia=VALUES(etnia), tipo_etnia=VALUES(tipo_etnia), nivel_instruccion=VALUES(nivel_instruccion), mision=VALUES(mision), años_aprobados=VALUES(años_aprobados), discapacidad=VALUES(discapacidad), tipo_discapacidad=VALUES(tipo_discapacidad), id_representante=VALUES(id_representante)";
    $stmt_detalle_menor = $conexion->prepare($sql_detalle_menor);
    $stmt_detalle_menor->bind_param("sssssssssii", $parentesco, $analfabeta, $etnia, $tipo_etnia, $nivel_instruccion, $mision, $años_aprobados, $discapacidad, $tipo_discapacidad, $id_paciente_a_actualizar, $id_representante);
    
    if (!$stmt_detalle_menor->execute()) {
        throw new Exception("Error al gestionar detalle paciente menor (6.2): " . $stmt_detalle_menor->error);
    }
    $stmt_detalle_menor->close();


    // 6.3. UPSERT en DIRECCION
    $sql_direccion = "INSERT INTO direccion(tiempo_residencia, tiempo, avenida_calle, referencia, Id_persona, Id_sector, estatus)
                      VALUES(?, ?, ?, ?, ?, ?, ?)
                      ON DUPLICATE KEY UPDATE tiempo_residencia=VALUES(tiempo_residencia), tiempo=VALUES(tiempo), avenida_calle=VALUES(avenida_calle), referencia=VALUES(referencia), Id_sector=VALUES(Id_sector)";
    $stmt_direccion = $conexion->prepare($sql_direccion);
    $stmt_direccion->bind_param("ssssiii", $tiempo_residencia, $tiempo, $avenida_calle, $referencia, $id_paciente_a_actualizar, $sector_residencia, $estado); 

    if (!$stmt_direccion->execute()) {
        throw new Exception("Error al gestionar dirección (6.3): " . $stmt_direccion->error);
    }
    $stmt_direccion->close();


    // 6.4. UPSERT en LUGAR_NACIMIENTO
    $sql_lugar_nacimiento = "INSERT INTO lugar_nacimiento(Id_persona, Id_municipio) VALUES(?, ?)
                             ON DUPLICATE KEY UPDATE Id_municipio=VALUES(Id_municipio)";
    $stmt_lugar_nacimiento = $conexion->prepare($sql_lugar_nacimiento);
    $stmt_lugar_nacimiento->bind_param("ii", $id_paciente_a_actualizar, $lugar_nacimiento);
    if (!$stmt_lugar_nacimiento->execute()) {
        throw new Exception("Error al gestionar lugar de nacimiento (6.4): " . $stmt_lugar_nacimiento->error);
    }
    $stmt_lugar_nacimiento->close();


    // 6.5. UPDATE/INSERT en HISTORIAL_MEDICO
    $id_historial_medico = 0;
    $sql_get_historial = "SELECT Id_historial FROM historial_medico WHERE Id_persona = ? LIMIT 1";
    $stmt_get_historial = $conexion->prepare($sql_get_historial);
    $stmt_get_historial->bind_param("i", $id_paciente_a_actualizar);
    $stmt_get_historial->execute();
    $stmt_get_historial->bind_result($id_historial_medico);
    $stmt_get_historial->fetch();
    $stmt_get_historial->close();

    if ($id_historial_medico > 0) {
        $sql_historia = "UPDATE historial_medico SET grupo_sanguineo=? WHERE Id_historial=?";
        $stmt_historia = $conexion->prepare($sql_historia);
        $stmt_historia->bind_param("si", $grupo_sanguineo, $id_historial_medico);
    } else {
        $sql_historia = "INSERT INTO historial_medico(fecha, Id_persona, grupo_sanguineo) VALUES(?, ?, ?)";
        $stmt_historia = $conexion->prepare($sql_historia);
        $stmt_historia->bind_param("sis", $fecha_actual, $id_paciente_a_actualizar, $grupo_sanguineo);
    }
    
    if (!$stmt_historia->execute()) {
        throw new Exception("Error al gestionar historial médico (6.5): " . $stmt_historia->error);
    }
    
    if ($id_historial_medico == 0) {
        $id_historial_medico = $conexion->insert_id; 
    }
    $stmt_historia->close();

    
    // --- 7. Manejo de Patologías y Alergias (M:M) ---

    // 7.1. Patologías (historial_patologias)
    $sql_delete_patologias = "DELETE FROM historial_patologias WHERE Id_persona = ?";
    $stmt_delete_patologias = $conexion->prepare($sql_delete_patologias);
    $stmt_delete_patologias->bind_param("i", $id_paciente_a_actualizar); 
    if (!$stmt_delete_patologias->execute()) {
        throw new Exception("Error al eliminar patologías antiguas (7.1): " . $stmt_delete_patologias->error);
    }
    $stmt_delete_patologias->close();
    
    if (!empty($patologias_ids_string)) {
        $patologias_array = array_filter(array_map('trim', explode(',', $patologias_ids_string)));
        $sql_patologia_m2m = "INSERT INTO historial_patologias(Id_patologia, Id_historial, Id_persona, estatus) VALUES(?, ?, ?, ?)";
        $stmt_patologia_m2m = $conexion->prepare($sql_patologia_m2m);
        
        foreach ($patologias_array as $patologia_id) {
            $stmt_patologia_m2m->bind_param("iiii", $patologia_id, $id_historial_medico, $id_paciente_a_actualizar, $estado); 
            if (!$stmt_patologia_m2m->execute()) {
                throw new Exception("Error al insertar patología ID $patologia_id (7.1): " . $stmt_patologia_m2m->error);
            }
        }
        $stmt_patologia_m2m->close();
    }

    // 7.2. Alergias (historial_alergias)
    $sql_delete_alergias = "DELETE FROM historial_alergias WHERE Id_persona = ?";
    $stmt_delete_alergias = $conexion->prepare($sql_delete_alergias);
    $stmt_delete_alergias->bind_param("i", $id_paciente_a_actualizar);
    if (!$stmt_delete_alergias->execute()) {
        throw new Exception("Error al eliminar alergias antiguas (7.2): " . $stmt_delete_alergias->error);
    }
    $stmt_delete_alergias->close();

    if (!empty($alergias_ids_string)) {
        $alergias_array = array_filter(array_map('trim', explode(',', $alergias_ids_string)));
        $sql_alergia_m2m = "INSERT INTO historial_alergias(Id_alergia, Id_historial, Id_persona, estatus) VALUES(?, ?, ?, ?)";
        $stmt_alergia_m2m = $conexion->prepare($sql_alergia_m2m);
        
        foreach ($alergias_array as $alergia_id) {
            $stmt_alergia_m2m->bind_param("iiii", $alergia_id, $id_historial_medico, $id_paciente_a_actualizar, $estado);
            if (!$stmt_alergia_m2m->execute()) {
                throw new Exception("Error al insertar alergia ID $alergia_id (7.2): " . $stmt_alergia_m2m->error);
            }
        }
        $stmt_alergia_m2m->close();
    }

    // --- 8. Commit y Redirección Final (Éxito) ---
    $conexion->commit();
    
    // Generar mensaje de éxito en la sesión para el modal
    $_SESSION['mensaje_user_exito'] = '✅ Éxito: El paciente ' . '' . $nombre_menor . ' ' . $apellido_menor . '' . ' ha sido actualizado correctamente.';
    // Redirecciona a la lista para mostrar el mensaje
    header('location: ../../pages/php/pacientes_menores_listado.php'); 
    exit();

} catch (Exception $e) {
    // 9. Rollback y Mensaje de Error
    $conexion->rollback();

    // Guardar error en el log del servidor y luego redireccionar con mensaje al usuario
    $_SESSION['mensaje_user_error'] = '❌ Error de Registro: No se pudo actualizar el paciente menor de edad. Detalle: ' . $e->getMessage();
    // Redirecciona a la lista para mostrar el mensaje
    header('location: ../../pages/php/pacientes_menores_listado.php');
}

$conexion->close();
?>


