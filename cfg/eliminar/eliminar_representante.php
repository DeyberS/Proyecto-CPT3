<?php
session_start();
require_once "../conexion.php";

$id_representante = $_GET['Id']; // o $_POST según tu formulario

// --- 1. Verificar relación con paciente menor ---
$sql_check = "SELECT 1 FROM detalle_paciente_menor WHERE Id_representante = '$id_representante' LIMIT 1";
$result_check = mysqli_query($conexion, $sql_check);

if (mysqli_num_rows($result_check) > 0) {
    // Existe relación → no se puede eliminar
    $_SESSION['mensaje_user_error'] = "❌ No se puede eliminar: El representante está asociado a un paciente menor.";
    header("location: ../../pages/php/papelera/representantes_papelera_listado.php");
    exit();
}

// --- 2. Transacción ---
mysqli_begin_transaction($conexion);

try {
    // --- 2.1. Eliminar teléfonos ---
    $sql_tel = "DELETE FROM telefonos_personas WHERE Id_persona = '$id_representante'";
    mysqli_query($conexion, $sql_tel);

    // --- 2.2. Eliminar rol ---
    $sql_rol = "DELETE FROM detalle_persona_rol WHERE Id_persona = '$id_representante'";
    mysqli_query($conexion, $sql_rol);

    // --- 2.3. Eliminar dirección SOLO si existe ---
    $sql_check_dir = "SELECT 1 FROM direccion WHERE Id_persona = '$id_representante'";
    $res_dir = mysqli_query($conexion, $sql_check_dir);
    if (mysqli_num_rows($res_dir) > 0) {
        $sql_dir = "DELETE FROM direccion WHERE Id_persona = '$id_representante'";
        mysqli_query($conexion, $sql_dir);
    }

    // --- 2.4. Eliminar lugar_nacimiento SOLO si existe ---
    $sql_check_ln = "SELECT 1 FROM lugar_nacimiento WHERE Id_persona = '$id_representante'";
    $res_ln = mysqli_query($conexion, $sql_check_ln);
    if (mysqli_num_rows($res_ln) > 0) {
        $sql_ln = "DELETE FROM lugar_nacimiento WHERE Id_persona = '$id_representante'";
        mysqli_query($conexion, $sql_ln);
    }

    // --- 2.5. Eliminar persona ---
    $sql_persona = "DELETE FROM persona WHERE id = '$id_representante'";
    if (!mysqli_query($conexion, $sql_persona)) {
        throw new Exception("Error al eliminar persona.");
    }

    // --- Commit ---
    mysqli_commit($conexion);
    $_SESSION['mensaje_user_exito'] = "✅ Éxito: El representante fue eliminado correctamente.";
    header("location: ../../pages/php/papelera/representantes_papelera_listado.php");

} catch (Exception $e) {
    mysqli_rollback($conexion);
    $_SESSION['mensaje_user_error'] = "❌ Error de Eliminación: " . $e->getMessage();
    header("location: ../../pages/php/papelera/representantes_papelera_listado.php");
}

mysqli_close($conexion);
?>