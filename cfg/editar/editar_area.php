<?php
session_start();
include("../conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['Id']; 
    $nombre = trim($_POST['nombre_area']);

    $conexion->begin_transaction();

    try {
        if (empty($id) || empty($nombre)) {
            throw new Exception("Datos incompletos para procesar la edición.");
        }

        $stmt = $conexion->prepare("UPDATE departamento SET nombre_departamento = ? WHERE Id_departamento = ?");
        $stmt->bind_param("si", $nombre, $id);

        if (!$stmt->execute()) {
            throw new Exception("No se pudo actualizar el nombre del área.");
        }

        $conexion->commit();
        $_SESSION['mensaje_user_exito'] = '✅ Éxito: El area ha sido actualizada correctamente.';

    } catch (Exception $e) {
        $conexion->rollback();
        $_SESSION['mensaje_user_error'] = '❌ Error de Actualización: ' . $e->getMessage();
    }

    header("Location: ../../pages/php/rh_areas_listado.php");
    exit();
}