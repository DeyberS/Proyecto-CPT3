<?php
session_start();
include("../conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['Id']; 
    $nombre = trim($_POST['nombre_sintoma']);

    $conexion->begin_transaction();

    try {
        if (empty($id) || empty($nombre)) {
            throw new Exception("Datos insuficientes para la edición.");
        }

        $stmt = $conexion->prepare("UPDATE sintomas SET nombre_sintoma = ? WHERE Id_sintomas = ?");
        $stmt->bind_param("si", $nombre, $id);

        if (!$stmt->execute()) {
            throw new Exception("No se pudo actualizar el registro.");
        }

        $conexion->commit();
        $_SESSION['mensaje_user_exito'] = '✅ Éxito: El sintoma ha sido actualizado correctamente.';

    } catch (Exception $e) {
        $conexion->rollback();
        $_SESSION['mensaje_user_error'] = '❌ Error de Actualización: ' . $e->getMessage();
    }

    header("Location: ../../pages/php/salud_sintomas_listado.php");
    exit();
}