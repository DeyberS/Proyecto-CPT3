<?php
session_start();
include("../conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre_laboratorio']);

    // Iniciar Transacción para asegurar integridad
    $conexion->begin_transaction();

    try {
        if (empty($nombre)) {
            throw new Exception("El nombre del laboratorio es obligatorio.");
        }

        // Insertar en tabla departamento (estatus 1 = Activo)
        $stmt_lote = $conexion->prepare("INSERT INTO laboratorio (nombre_laboratorio) VALUES (?)");
        $stmt_lote->bind_param("s", $nombre);        
        
        if (!$stmt_lote->execute()) {
            throw new Exception("Error interno al registrar el laboratorio.");
        }
        $stmt_lote->close();


        $conexion->commit();
        $_SESSION['mensaje_user_exito'] = '✅ Éxito: El laboratorio fue agregado correctamente.';
        header("Location: ../../pages/php/farmacia_laboratorio_listado.php");
        exit();

    } catch (Exception $e) {
        $conexion->rollback();
        error_log("Error de transacción al agregar el laboratorio: " . $e->getMessage()); 
        $_SESSION['mensaje_user_error'] = '❌ Error de Registro: No se pudo registrar el laboratorio. Detalle: ' . $e->getMessage();
        header("Location: ../../pages/php/farmacia_laboratorio_listado.php");
        exit();
    }
}