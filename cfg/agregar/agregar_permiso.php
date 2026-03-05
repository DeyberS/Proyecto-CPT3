<?php
session_start();
include("../conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre_permiso']);
    $descripcion = trim($_POST['descripcion']);

    // Iniciar Transacción para asegurar integridad
    $conexion->begin_transaction();

    try {
        if (empty($nombre)) {
            throw new Exception("El nombre del permiso es obligatorio.");
        }

        // Insertar en tabla departamento (estatus 1 = Activo)
        $stmt_lote = $conexion->prepare("INSERT INTO permiso (nombre_permiso, descripcion) VALUES (?, ?)");
        $stmt_lote->bind_param("ss", $nombre, $descripcion);        
        
        if (!$stmt_lote->execute()) {
            throw new Exception("Error interno al registrar el permiso.");
        }
        $stmt_lote->close();


        $conexion->commit();
        $_SESSION['mensaje_user_exito'] = '✅ Éxito: El permiso fue agregado correctamente.';
        header("Location: ../../pages/php/cfg_permisos_listado.php");
        exit();

    } catch (Exception $e) {
        $conexion->rollback();
        error_log("Error de transacción al agregar el permiso: " . $e->getMessage()); 
        $_SESSION['mensaje_user_error'] = '❌ Error de Registro: No se pudo registrar el permiso. Detalle: ' . $e->getMessage();
        header("Location: ../../pages/php/cfg_permisos_listado.php");
        exit();
    }
}