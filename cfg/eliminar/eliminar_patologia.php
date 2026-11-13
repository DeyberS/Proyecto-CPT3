<?PHP
 session_start();
 include ("../conexion.php"); 

 $Id = (int) ($_GET['Id'] ?? 0);
 
 if ($Id <= 0) {
     $_SESSION['mensaje_user_error'] = '⚠️ Error: ID de patología no válida para eliminar.';
     header('location: ../../pages/php/papelera/salud_patologias_papelera_listado.php');
     exit();
 }
 
 $nombre_patologia = '';
 $codigo_cie = '';
 $sql_get_info = "SELECT nombre_patologia, codigo_cie FROM patologias WHERE Id_patologia = ?";
 $stmt_info = $conexion->prepare($sql_get_info);
 $stmt_info->bind_param("i", $Id);
 $stmt_info->execute();
 $result_info = $stmt_info->get_result();
 if ($result_info->num_rows > 0) {
    $row_info = $result_info->fetch_assoc();
    $nombre_patologia = $row_info['nombre_patologia'];
    $codigo_cie = $row_info['codigo_cie'];
 } else {
    $_SESSION['mensaje_user_error'] = '⚠️ Error: La patología que intenta eliminar no se encontró.';
    header('location: ../../pages/php/papelera/salud_patologias_papelera_listado.php');
    exit();
 }
 $stmt_info->close();

$conexion->begin_transaction();

try {
    $sql_delete_sintomas = "DELETE FROM detalle_patologia_sintomas WHERE Id_patologia = ?";
    $stmt_sintomas = $conexion->prepare($sql_delete_sintomas);
    if ($stmt_sintomas === false) {
        throw new Exception('⚠️ Fallo al preparar la eliminación de síntomas.');
    }
    $stmt_sintomas->bind_param("i", $Id);
    if (!$stmt_sintomas->execute()) {
        throw new Exception('⚠️ Fallo al ejecutar la eliminación de síntomas: ' . $stmt_sintomas->error);
    }
    $stmt_sintomas->close();

    $sql_delete_patologia = "DELETE FROM patologias WHERE Id_patologia = ?";
    $stmt_patologia = $conexion->prepare($sql_delete_patologia);
    if ($stmt_patologia === false) {
        throw new Exception('⚠️ Fallo al preparar la eliminación de la patología principal.');
    }
    $stmt_patologia->bind_param("i", $Id);
    
    if (!$stmt_patologia->execute()) {
        $error_code = $conexion->errno;
        
        if ($error_code == 1451) {
            $_SESSION['mensaje_user_error'] = '⚠️ Restricción de Datos: No se puede eliminar la patología ' . $nombre_patologia . ' (' . $codigo_cie . '). Existen pacientes registrados en historial_patologias que la utilizan.';
            $stmt_patologia->close();
            $conexion->rollback();
            header('location: ../../pages/php/papelera/salud_patologias_papelera_listado.php');
            exit();
        } else {
            throw new Exception('⚠️ Fallo al ejecutar la eliminación de la patología: ' . $stmt_patologia->error);
        }
    }
    $stmt_patologia->close();

    $conexion->commit();
    $_SESSION['mensaje_user_exito'] = '🗑️ Eliminación Exitosa: La patología ' . $nombre_patologia . ' (' . $codigo_cie . ') y sus relaciones con los síntomas han sido eliminadas permanentemente.';

} catch (Exception $e) {
    $conexion->rollback();
    
    if (!isset($_SESSION['mensaje_user_error'])) {
        $_SESSION['mensaje_user_error'] = '⚠️ Error de Eliminación: No se pudo completar la operación. Detalle: ' . $e->getMessage();
    }
}

$conexion->close();
header('location: ../../pages/php/papelera/salud_patologias_papelera_listado.php');
exit();
?>