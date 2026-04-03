<?php
session_start();
include("../conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['Id']; 
    $nombre = trim($_POST['nombre_laboratorio']);

    $conexion->begin_transaction();

    try {
        if (empty($id) || empty($nombre)) {
            throw new Exception("Datos incompletos para procesar la edición.");
        }

        $stmt = $conexion->prepare("UPDATE laboratorio SET nombre_laboratorio = ? WHERE Id_laboratorio = ?");
        $stmt->bind_param("si", $nombre, $id);

        if (!$stmt->execute()) {
            throw new Exception("No se pudo actualizar el laboratorio.");
        }

        $conexion->commit();
        $_SESSION['mensaje_user_exito'] = '✅ Éxito: El laboratorio ha sido actualizado correctamente.';

    } catch (Exception $e) {
        $conexion->rollback();
        $_SESSION['mensaje_user_error'] = '❌ Error de Actualización: ' . $e->getMessage();
    }

    header("Location: ../../pages/php/farmacia_laboratorio_listado.php");
    exit();
}