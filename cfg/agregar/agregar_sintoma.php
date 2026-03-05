<?php
session_start();
include("../conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre_sintoma']);

    // Iniciar Transacción
    $conexion->begin_transaction();

    try {
        if (empty($nombre)) {
            throw new Exception("El nombre del síntoma no puede estar vacío.");
        }

        $stmt = $conexion->prepare("INSERT INTO sintomas (nombre_sintoma) VALUES (?)");
        $stmt->bind_param("s", $nombre);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al insertar el síntoma.");
        }

        // Si todo va bien, confirmamos los cambios
        $conexion->commit();
        $_SESSION['mensaje_user_exito'] = '✅ Éxito: El sintoma fue agregado correctamente.';

    } catch (Exception $e) {
        // Si hay error, revertimos cualquier cambio
        $conexion->rollback();
        error_log("Error de transacción al agregar el sintoma: " . $e->getMessage()); 
        $_SESSION['mensaje_user_error'] = '❌ Error de Registro: No se pudo registrar el sintoma. Detalle: ' . $e->getMessage();
    }

    $stmt->close();
    header("Location: ../../pages/php/salud_sintomas_listado.php");
    exit();
}