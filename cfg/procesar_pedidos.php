<?php
session_start();
// Asegúrate de que la ruta a tu conexión sea la correcta según la ubicación del archivo
include("conexion.php"); // Ajustado según tu include en el frontend

if (isset($_POST['op'])) {
    $op = $_POST['op'];

    // -------------------------------------------------------------
    // 1. GUARDAR NUEVO PEDIDO
    // -------------------------------------------------------------
    if ($op == 'guardar_pedido') {
        $id_proveedor = mysqli_real_escape_string($conexion, $_POST['id_proveedor']);
        $id_usuario = mysqli_real_escape_string($conexion, $_POST['id_usuario']);
        $detalles_json = $_POST['detalles_pedido_json'];
        $fecha_pedido = $_POST['fecha_pedido'] ?? date('Y-m-d');
        $hora_pedido = $_POST['hora_pedido'] ?? date('H:i');
        $fecha_hora_exacta = $fecha_pedido . ' ' . $hora_pedido . ':00';

        // Iniciamos la transacción SQL para asegurar la integridad de los datos
        mysqli_begin_transaction($conexion);

        try {
            // Decodificamos el JSON primero para verificar que no esté vacío
            $detalles = json_decode($detalles_json, true);
            
            if (empty($detalles)) {
                throw new Exception("El pedido no contiene medicamentos.");
            }

            // 1. Insertar el pedido general (fecha_creacion se llena solo gracias a CURRENT_TIMESTAMP)
            $query_pedido = "INSERT INTO pedidos (fecha_creacion, id_proveedor, id_usuario, estado, estatus) 
                             VALUES ('$fecha_hora_exacta', '$id_proveedor', '$id_usuario', 'Pendiente', 1)";
            
            if (!mysqli_query($conexion, $query_pedido)) {
                throw new Exception("Error al registrar la cabecera del pedido: " . mysqli_error($conexion));
            }

            // Obtener el ID del pedido recién insertado
            $id_pedido = mysqli_insert_id($conexion);

            // 2. Insertar cada medicamento en la tabla detalle_pedidos
            foreach ($detalles as $item) {
                $id_descripcion = mysqli_real_escape_string($conexion, $item['id_descripcion']);
                $cantidad = mysqli_real_escape_string($conexion, $item['cantidad']);

                $query_detalle = "INSERT INTO detalle_pedidos (id_pedido, id_descripcion_medicamento, cantidad_solicitada) 
                                  VALUES ('$id_pedido', '$id_descripcion', '$cantidad')";
                
                if (!mysqli_query($conexion, $query_detalle)) {
                    throw new Exception("Error al registrar el detalle del medicamento ID $id_descripcion: " . mysqli_error($conexion));
                }
            }

            // Si todo fue exitoso, confirmamos los cambios en la base de datos
            mysqli_commit($conexion);
            
            // Redireccionar al listado con éxito
            header("Location: pdf/pedido_impresion.php?id_pedido=" . $id_pedido);
            exit();

        } catch (Exception $e) {
            // Si hubo algún fallo, deshacemos cualquier insert que se haya hecho en este proceso
            mysqli_rollback($conexion);
            
            // (Opcional) Aquí puedes guardar $e->getMessage() en un archivo de log de errores
            
            // Redireccionar con error
            header("Location: ../pages/php/farmacia_pedidos_listado.php?status=error");
            exit();
        }
    }

    // -------------------------------------------------------------
    // 2. CANCELAR PEDIDO
    // -------------------------------------------------------------
    if ($op == 'cancelar_pedido') {
        $id_pedido = mysqli_real_escape_string($conexion, $_POST['id_pedido']);
        
        // Actualizar el estado a 'Cancelado'
        $query_cancelar = "UPDATE pedidos SET estado = 'Cancelado' WHERE id_pedido = '$id_pedido'";
        
        if (mysqli_query($conexion, $query_cancelar)) {
            header("Location: ../pages/php/farmacia_pedidos_listado.php?status=cancelled");
            exit();
        } else {
            header("Location: ../pages/php/farmacia_pedidos_listado.php?status=error");
            exit();
        }
    }
}
?>