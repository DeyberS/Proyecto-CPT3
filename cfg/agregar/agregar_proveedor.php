<?php
session_start();
include("../conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre_proveedor']);

    // Iniciar Transacción para asegurar integridad
    $conexion->begin_transaction();

    try {
        if (empty($nombre)) {
            throw new Exception("El nombre del proveedor es obligatorio.");
        }

        // Insertar en tabla departamento (estatus 1 = Activo)
        $stmt_lote = $conexion->prepare("INSERT INTO proveedor (nombre_proveedor) VALUES (?)");
        $stmt_lote->bind_param("s", $nombre);        
        
        if (!$stmt_lote->execute()) {
            throw new Exception("Error interno al registrar el proveedor.");
        }
        $stmt_lote->close();


        $conexion->commit();
        $_SESSION['mensaje_user_exito'] = '✅ Éxito: El proveedor fue agregado correctamente.';
        header("Location: ../../pages/php/farmacia_proveedores_listado.php");
        exit();

    } catch (Exception $e) {
        $conexion->rollback();
        error_log("Error de transacción al agregar el proveedor: " . $e->getMessage()); 
        $_SESSION['mensaje_user_error'] = '❌ Error de Registro: No se pudo registrar el proveedor. Detalle: ' . $e->getMessage();
        header("Location: ../../pages/php/farmacia_proveedores_listado.php");
        exit();
    }
}