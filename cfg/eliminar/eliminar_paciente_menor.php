<?php
session_start();

// Incluir el archivo de conexión
require_once "../conexion.php";

// Función de redirección centralizada para errores
function redireccionar_error($mensaje, $conexion) {
    // Si la conexión está activa y en transacción, hacer rollback antes de cerrar
    if ($conexion && $conexion->in_transaction) {
        $conexion->rollback();
    }
    // Almacenar el mensaje de error en la sesión
    $_SESSION['mensaje_user_error'] = $mensaje;
    // Redireccionar al listado
    header('location: ../../pages/php/papelera/pacientes_menores_papelera_listado.php');
    exit();
}

// Función de redirección centralizada para éxito
function redireccionar_exito($mensaje) {
    // Almacenar el mensaje de éxito en la sesión
    $_SESSION['mensaje_user_exito'] = $mensaje;
    // Redireccionar al listado
    header('location: ../../pages/php/papelera/pacientes_menores_papelera_listado.php');
    exit();
}


// --- 1. Recolección de Datos (ID del Paciente) ---
// La ID se recibe directamente y debe ser un número entero.
$id_paciente = $_POST['Id'] ?? $_GET['Id'] ?? null;

// Convertir a entero para mayor seguridad
$id_paciente = (int) $id_paciente; 

if ($id_paciente <= 0) {
    redireccionar_error("Error: Se requiere una ID de paciente válida para la eliminación.", $conexion);
}

// --- 2. Buscar y Obtener la Cédula (Opcional, solo para el mensaje de éxito) ---
$cedula_menor = '';
$tipo_documento_menor = '';

$sql_get_info = "SELECT tipo_cedula, cedula, nombre, apellido FROM persona WHERE Id = ? LIMIT 1";
$stmt_get_info = $conexion->prepare($sql_get_info);

if ($stmt_get_info === false) {
    redireccionar_error("Error al preparar la búsqueda de información del menor: " . $conexion->error, $conexion);
}

$stmt_get_info->bind_param("i", $id_paciente);
$stmt_get_info->execute();
$res_get_info = $stmt_get_info->get_result();

if ($res_get_info->num_rows === 0) {
    $stmt_get_info->close();
    $conexion->close();
    // Mensaje de error si no se encuentra el paciente
    redireccionar_error("Error: El paciente con ID $id_paciente no fue encontrado.", null);
}

$info = $res_get_info->fetch_assoc();
$tipo_documento_menor = $info['tipo_cedula'];
$cedula_menor = $info['cedula'];
$nombre_menor = $info['nombre'];
$apellido_menor = $info['apellido'];
$stmt_get_info->close();


// --- 3. Inicio de la Transacción de Eliminación ---
$conexion->begin_transaction();

try {
    // Nota: El orden de eliminación es crucial para evitar errores de llave foránea.

    // 3.1. Eliminar Patologías y Alergias (Tablas M:M)
    // Se asume que Id_historial NO es llave foránea, solo Id_persona. Si Id_historial es FK, se requeriría borrar historial médico ANTES.
    $sql_del_patologias = "DELETE FROM historial_patologias WHERE Id_persona = ?";
    $stmt_del_patologias = $conexion->prepare($sql_del_patologias);
    $stmt_del_patologias->bind_param("i", $id_paciente);
    if (!$stmt_del_patologias->execute()) {
        throw new Exception("Error al eliminar patologías: " . $stmt_del_patologias->error);
    }
    $stmt_del_patologias->close();

    $sql_del_alergias = "DELETE FROM historial_alergias WHERE Id_persona = ?";
    $stmt_del_alergias = $conexion->prepare($sql_del_alergias);
    $stmt_del_alergias->bind_param("i", $id_paciente);
    if (!$stmt_del_alergias->execute()) {
        throw new Exception("Error al eliminar alergias: " . $stmt_del_alergias->error);
    }
    $stmt_del_alergias->close();

    // 3.2. Eliminar Historial Médico
    // (Debe ser después de Patologías/Alergias si estas tablas usan Id_historial como FK)
    $sql_del_historial = "DELETE FROM historial_medico WHERE Id_persona = ?";
    $stmt_del_historial = $conexion->prepare($sql_del_historial);
    $stmt_del_historial->bind_param("i", $id_paciente);
    if (!$stmt_del_historial->execute()) {
        throw new Exception("Error al eliminar historial médico: " . $stmt_del_historial->error);
    }
    $stmt_del_historial->close();
    
    // 3.3. Eliminar LUGAR_NACIMIENTO
    $sql_del_lugar_nacimiento = "DELETE FROM lugar_nacimiento WHERE Id_persona = ?";
    $stmt_del_lugar_nacimiento = $conexion->prepare($sql_del_lugar_nacimiento);
    $stmt_del_lugar_nacimiento->bind_param("i", $id_paciente);
    if (!$stmt_del_lugar_nacimiento->execute()) {
        throw new Exception("Error al eliminar lugar de nacimiento: " . $stmt_del_lugar_nacimiento->error);
    }
    $stmt_del_lugar_nacimiento->close();
    
    // 3.4. Eliminar Dirección (Residencia del Menor)
    $sql_del_direccion = "DELETE FROM direccion WHERE Id_persona = ?";
    $stmt_del_direccion = $conexion->prepare($sql_del_direccion);
    $stmt_del_direccion->bind_param("i", $id_paciente);
    if (!$stmt_del_direccion->execute()) {
        throw new Exception("Error al eliminar dirección: " . $stmt_del_direccion->error);
    }
    $stmt_del_direccion->close();

    // 3.5. Eliminar DETALLE_PACIENTE_MENOR
    $sql_del_detalle_menor = "DELETE FROM detalle_paciente_menor WHERE Id_persona = ?";
    $stmt_del_detalle_menor = $conexion->prepare($sql_del_detalle_menor);
    $stmt_del_detalle_menor->bind_param("i", $id_paciente);
    if (!$stmt_del_detalle_menor->execute()) {
        throw new Exception("Error al eliminar detalle paciente menor: " . $stmt_del_detalle_menor->error);
    }
    $stmt_del_detalle_menor->close();

    // 3.6. Eliminar DETALLE_PERSONA_ROL
    $sql_del_rol = "DELETE FROM detalle_persona_rol WHERE Id_persona = ?";
    $stmt_del_rol = $conexion->prepare($sql_del_rol);
    $stmt_del_rol->bind_param("i", $id_paciente);
    if (!$stmt_del_rol->execute()) {
        throw new Exception("Error al eliminar rol: " . $stmt_del_rol->error);
    }
    $stmt_del_rol->close();

    // 3.7. **ELIMINACIÓN FINAL**: Eliminar al Paciente Menor de la tabla PERSONA
    $sql_del_paciente = "DELETE FROM persona WHERE Id = ?";
    $stmt_del_paciente = $conexion->prepare($sql_del_paciente);
    $stmt_del_paciente->bind_param("i", $id_paciente);
    if (!$stmt_del_paciente->execute()) {
        throw new Exception("Error al eliminar persona (Menor): " . $stmt_del_paciente->error);
    }
    $stmt_del_paciente->close();
    
    // --- 4. Commit y Redirección Final ---
    $conexion->commit();
    
    // Redirección al éxito
    $_SESSION['mensaje_user_exito'] = '🗑️ Eliminación Exitosa: El paciente con la cédula ' . '' . $nombre . $apellido . '' . ' ha sido eliminado permanentemente.';
    header('location: ../../pages/php/papelera/pacientes_menores_papelera_listado.php');

} catch (Exception $e) {
    // 5. Rollback y Mensaje de Error
    error_log("Error al eliminar paciente permanentemente: " . $e->getMessage()); 
    $_SESSION['mensaje_user_error'] = '❌ Error de Eliminación: No se pudo eliminar el paciente. Detalle: ' . $e->getMessage();
    header('location: ../../pages/php/papelera/pacientes_menores_papelera_listado.php');
}

$conexion->close();
?>