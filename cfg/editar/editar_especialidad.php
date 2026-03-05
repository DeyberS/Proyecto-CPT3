<?php
session_start();
include("../conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['Id']; 
    $nombre = trim($_POST['nombre_especialidad']);

    $conexion->begin_transaction();

    try {
        if (empty($id) || empty($nombre)) {
            throw new Exception("Datos incompletos para actualizar.");
        }

        $stmt = $conexion->prepare("UPDATE especialidad SET nombre_especialidad = ? WHERE Id_especialidad = ?");
        $stmt->bind_param("si", $nombre, $id);

        if (!$stmt->execute()) {
            throw new Exception("No se pudo actualizar la especialidad.");
        }

        $conexion->commit();
        $_SESSION['mensaje_user_exito'] = '✅ Éxito: La especialidad ha sido actualizada correctamente.';

    } catch (Exception $e) {
        $conexion->rollback();
        $_SESSION['mensaje_user_error'] = '❌ Error de Actualización: ' . $e->getMessage();
    }

    header("Location: ../../pages/php/rh_especialidades_listado.php");
    exit();
}