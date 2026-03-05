<?php
session_start();
include("../conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['Id'];
    $nombre = trim($_POST['nombre_alergia']);

    $conexion->begin_transaction();

    try {
        if (empty($id) || empty($nombre)) {
            throw new Exception("Datos insuficientes para la edición.");
        }

        $stmt = $conexion->prepare("UPDATE alergias_conocidas SET nombre_alergia = ? WHERE Id_alergias_conocidas = ?");
        $stmt->bind_param("si", $nombre, $id);

        if (!$stmt->execute()) {
            throw new Exception("No se pudo actualizar el registro.");
        }

        $conexion->commit();
        $_SESSION['mensaje_user_exito'] = '✅ Éxito: La alergia ha sido actualizada correctamente.';

    } catch (Exception $e) {
        $conexion->rollback();
        $_SESSION['mensaje_user_error'] = '❌ Error de Actualización: ' . $e->getMessage();
    }

    header("Location: ../../pages/php/salud_alergias_listado.php");
    exit();
}


