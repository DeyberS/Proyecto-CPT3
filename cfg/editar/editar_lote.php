<?php
session_start();
include("../conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['Id']; 
    $nombre = trim($_POST['nombre_lote']);
    $fecha_fabricacion = trim($_POST['fecha_fabricacion']);
    $fecha_vencimiento = trim($_POST['fecha_vencimiento']);
    $medicamento = trim($_POST['medicamento']);
    $estado_lote = trim($_POST['estado_lote']);

    $conexion->begin_transaction();

    try {
        if (empty($id) || empty($nombre)) {
            throw new Exception("Datos incompletos para procesar la edición.");
        }

        $stmt = $conexion->prepare("UPDATE lotes_medicamentos SET Id_descripcion_medicamento = ?, Lote = ?, fecha_fabricacion = ? , fecha_vencimiento = ?, estado_lote = ? WHERE Id = ?");
        $stmt->bind_param("issssi", $medicamento, $nombre, $fecha_fabricacion, $fecha_vencimiento, $estado_lote, $id);

        if (!$stmt->execute()) {
            throw new Exception("No se pudo actualizar el lote.");
        }

        $conexion->commit();
        $_SESSION['mensaje_user_exito'] = '✅ Éxito: El lote ha sido actualizado correctamente.';

    } catch (Exception $e) {
        $conexion->rollback();
        $_SESSION['mensaje_user_error'] = '❌ Error de Actualización: ' . $e->getMessage();
    }

    header("Location: ../../pages/php/farmacia_lotes_listado.php");
    exit();
}