<?php
session_start();
include("../conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre_alergia']);

    // Iniciar Transacción
    $conexion->begin_transaction();

    try {
        if (empty($nombre)) {
            throw new Exception("El nombre de la alergia no puede estar vacío.");
        }

        $stmt = $conexion->prepare("INSERT INTO alergias_conocidas (nombre_alergia) VALUES (?)");
        $stmt->bind_param("s", $nombre);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al insertar en la base de datos.");
        }

        // Si todo sale bien, confirmar cambios
        $conexion->commit();
        $_SESSION['mensaje_user_exito'] = '✅ Éxito: La alergia fue agregada correctamente.';

    } catch (Exception $e) {
        // Si hay error, revertir cambios
        $conexion->rollback();
        error_log("Error de transacción al agregar la alergia: " . $e->getMessage()); 
        $_SESSION['mensaje_user_error'] = '❌ Error de Registro: No se pudo registrar la alergia. Detalle: ' . $e->getMessage();
    }

    $stmt->close();
    header("Location: ../../pages/php/salud_alergias_listado.php");
    exit();
}