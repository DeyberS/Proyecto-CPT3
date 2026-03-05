<?php
session_start();
include("../conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre_area']);

    // Iniciar Transacción para asegurar integridad
    $conexion->begin_transaction();

    try {
        if (empty($nombre)) {
            throw new Exception("El nombre del área es obligatorio.");
        }

        // Insertar en tabla departamento (estatus 1 = Activo)
        $stmt = $conexion->prepare("INSERT INTO departamento (nombre_departamento, estatus) VALUES (?, '1')");
        $stmt->bind_param("s", $nombre);
        
        if (!$stmt->execute()) {
            throw new Exception("Error interno al registrar el área.");
        }

        $conexion->commit();
        $_SESSION['mensaje_user_exito'] = '✅ Éxito: El area fue agregada correctamente.';

    } catch (Exception $e) {
        $conexion->rollback();
        error_log("Error de transacción al agregar el area: " . $e->getMessage()); 
        $_SESSION['mensaje_user_error'] = '❌ Error de Registro: No se pudo registrar el area. Detalle: ' . $e->getMessage();
    }

    $stmt->close();
    header("Location: ../../pages/php/rh_areas_listado.php");
    exit();
}