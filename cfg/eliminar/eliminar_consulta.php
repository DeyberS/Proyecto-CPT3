<?php
session_start();
include("../conexion.php"); 

header('Content-Type: text/html; charset=utf-8');

if (empty($_GET['Id'])) {
    $_SESSION['mensaje_user_error'] = '⚠️ Error: ID de consulta no proporcionado para la eliminación.';
    header("Location: ../../pages/php/papelera/consulta_papelera_listado.php");
    exit;
}

$id_consulta = (int)$_GET['Id'];

if (!$conexion || $conexion->connect_error) {
    $_SESSION['mensaje_user_error'] = '⚠️ Error de conexión a la base de datos.';
    header("Location: ../../pages/php/papelera/consulta_papelera_listado.php");
    exit;
}

$conexion->begin_transaction();
$error = false;
$mensaje_error = '';

try {

    $sql_eliminar = "DELETE FROM consulta WHERE id_consulta = ?";
    $stmt_eliminar = $conexion->prepare($sql_eliminar);
    $stmt_eliminar->bind_param("i", $id_consulta);
    
    if (!$stmt_eliminar->execute()) {
        throw new Exception("⚠️ Error al eliminar la consulta principal: " . $stmt_eliminar->error);
    }
    
    if ($stmt_eliminar->affected_rows === 0) {
        throw new Exception("⚠️ La consulta con ID $id_consulta no fue encontrada o ya ha sido eliminada.");
    }
    
    $stmt_eliminar->close();

    $conexion->commit();

    $_SESSION['mensaje_user_exito'] = "✅ Consulta eliminada con éxito. La consulta N° {$id_consulta} ha sido removida del historial.";
    header("Location: ../../pages/php/papelera/consulta_papelera_listado.php");
    exit;

} catch (Exception $e) {

    $_SESSION['mensaje_user_error'] = "⚠️ Error al eliminar. Detalle: " . $e->getMessage();
    header("Location: ../../pages/php/papelera/consulta_papelera_listado.php");
    exit;
}

$conexion->close();
?>
