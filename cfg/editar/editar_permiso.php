<?php
session_start();
include("../conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['Id']; 
    $nombre = trim($_POST['nombre_permiso']);
    $descripcion = trim($_POST['descripcion']);

    $conexion->begin_transaction();

    try {
        if (empty($id) || empty($nombre)) {
            throw new Exception("Datos incompletos para procesar la edición.");
        }

        $stmt = $conexion->prepare("UPDATE permiso SET nombre_permiso = ?, descripcion = ? WHERE Id_permiso = ?");
        $stmt->bind_param("ssi", $nombre, $descripcion, $id);

        if (!$stmt->execute()) {
            throw new Exception("No se pudo actualizar el permiso.");
        }

        $conexion->commit();
        $_SESSION['mensaje_user_exito'] = '✅ Éxito: El permiso ha sido actualizado correctamente.';

    } catch (Exception $e) {
        $conexion->rollback();
        $_SESSION['mensaje_user_error'] = '❌ Error de Actualización: ' . $e->getMessage();
    }

    header("Location: ../../pages/php/cfg_permisos_listado.php");
    exit();
}