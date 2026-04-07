<?php
session_start();
include("../conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre_lote']);
    $fecha_fabricacion = trim($_POST['fecha_fabricacion']);
    $fecha_vencimiento = trim($_POST['fecha_vencimiento']);
    $medicamento = trim($_POST['medicamento']);
    $proveedor = trim($_POST['proveedor']);

    // Iniciar Transacción para asegurar integridad
    $conexion->begin_transaction();

    try {
        if (empty($nombre)) {
            throw new Exception("El nombre del lote es obligatorio.");
        }

        // Insertar en tabla departamento (estatus 1 = Activo)
        $stmt_lote = $conexion->prepare("INSERT INTO lotes_medicamentos (Id_descripcion_medicamento, Id_proveedor, Lote, fecha_fabricacion, fecha_vencimiento, estado_lote, estatus) VALUES (?, ?, ?, ?, ?, 'Disponible', '1')");
        $stmt_lote->bind_param("iisss", $medicamento, $proveedor, $nombre, $fecha_fabricacion, $fecha_vencimiento);        
        
        if (!$stmt_lote->execute()) {
            throw new Exception("Error interno al registrar el área.");
        }
        $id_lote = $conexion->insert_id; 
        $stmt_lote->close();

        $stmt_existencia = $conexion->prepare("INSERT INTO existencias_stock (Id_descripcion_medicamento, Id_lote, cantidad_actual, ultima_actualizacion) VALUES (?, ?, '0', NOW())");
        $stmt_existencia->bind_param("ii", $medicamento, $id_lote);
        
        if (!$stmt_existencia->execute()) {
            throw new Exception("Error interno al registrar la existencia.");
        }

        $stmt_existencia->close();

        $conexion->commit();
        $_SESSION['mensaje_user_exito'] = '✅ Éxito: El lote fue agregado correctamente.';
        header("Location: ../../pages/php/farmacia_lotes_listado.php");
        exit();

    } catch (Exception $e) {
        $conexion->rollback();
        error_log("Error de transacción al agregar el lote: " . $e->getMessage()); 
        $_SESSION['mensaje_user_error'] = '❌ Error de Registro: No se pudo registrar el lote. Detalle: ' . $e->getMessage();
        header("Location: ../../pages/php/farmacia_lotes_listado.php");
        exit();
    }
}