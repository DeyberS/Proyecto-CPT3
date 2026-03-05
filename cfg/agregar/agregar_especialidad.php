<?php
session_start();
include("../conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre_especialidad']);

    $conexion->begin_transaction();

    try {
        if (empty($nombre)) {
            throw new Exception("El nombre de la especialidad es obligatorio.");
        }

        $stmt = $conexion->prepare("INSERT INTO especialidad (nombre_especialidad) VALUES (?)");
        $stmt->bind_param("s", $nombre);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al registrar la especialidad.");
        }

        $conexion->commit();
        $_SESSION['mensaje_user_exito'] = '✅ Éxito: La especialidad fue agregada correctamente.';

    } catch (Exception $e) {
        $conexion->rollback();
        error_log("Error de transacción al agregar la especialidad: " . $e->getMessage()); 
        $_SESSION['mensaje_user_error'] = '❌ Error de Registro: No se pudo registrar la especialidad. Detalle: ' . $e->getMessage();
    }

    $stmt->close();
    header("Location: ../../pages/php/rh_especialidades_listado.php");
    exit();
}


