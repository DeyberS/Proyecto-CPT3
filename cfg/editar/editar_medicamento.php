<?php
session_start();
include('../conexion.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Recolección de IDs
    $id_descripcion = $_POST['Id']; 
    $id_medicamento = $_POST['Id_medicamento']; 

    // 2. Datos generales
    $nombre_medicamento = $_POST['medicamento'];
    $id_presentacion            = $_POST['presentacion'];
    $via_aplicacion     = $_POST['via'];
    $contenido_neto       = $_POST['contenido_neto'];
    $almacenamiento     = $_POST['almacenamiento'];
    $excipientes  = $_POST['excipientes'];
    $id_laboratorio     = !empty($_POST['laboratorio']) ? (int)$_POST['laboratorio'] : null;
    $codigo_barras      = !empty($_POST['codigo_barras']) ? $_POST['codigo_barras'] : 'Ninguno';

    // Principios activos (formato: "id,cant,unid|id,cant,unid")
    $principios_raw     = $_POST['composicion_detallada']; 

    $conexion->begin_transaction();

    try {
        // PASO 1: Actualizar nombre en la tabla 'medicamento' (Maestra)
        $stmt1 = $conexion->prepare("UPDATE medicamento SET nombre_medicamento = ? WHERE Id_medicamento = ?");
        $stmt1->bind_param("si", $nombre_medicamento, $id_medicamento);
        $stmt1->execute();

        // PASO 2: Actualizar todos los detalles en 'descripcion_medicamento'
        // Incluimos laboratorio, código de barras y el resto de campos del formulario
        $sql_desc = "UPDATE descripcion_medicamento SET 
                        Id_presentacion = ?, 
                        Id_laboratorio = ?, 
                        codigo_barras = ?, 
                        contenido_neto = ?, 
                        via_aplicacion = ?, 
                        almacenamiento = ?, 
                        excipientes = ? 
                     WHERE Id = ?";
        
        $stmt2 = $conexion->prepare($sql_desc);
        $stmt2->bind_param("iisssssi", 
            $id_presentacion, 
            $id_laboratorio, 
            $codigo_barras, 
            $contenido_neto, 
            $via_aplicacion, 
            $almacenamiento, 
            $excipientes, 
            $id_descripcion
        );
        $stmt2->execute();

        // PASO 3: Actualizar Principios Activos (Limpiar e Insertar nuevos)
        // Borramos los actuales vinculados a este ID de descripción
        $stmt_del = $conexion->prepare("DELETE FROM detalle_principio_medicamento WHERE id_medicamento = ?");
        $stmt_del->bind_param("i", $id_descripcion);
        $stmt_del->execute();

        // Insertamos la nueva lista procesando el string oculto
        if (!empty($principios_raw)) {
            $stmt3 = $conexion->prepare("INSERT INTO detalle_principio_medicamento 
                (id_medicamento, id_principio_activo, id_tipo_unidad_medida, cantidad_unidad_medida) 
                VALUES (?, ?, ?, ?)");

            $filas_pa = explode('|', $principios_raw);
            foreach ($filas_pa as $fila) {
                $columnas = explode(',', $fila);
                if (count($columnas) == 3) {
                    $id_pa    = (int)$columnas[0];
                    $cantidad = (float)$columnas[1]; // Usamos float por si hay decimales en cantidad
                    $id_unid  = (int)$columnas[2];

                    $stmt3->bind_param("iiid", $id_descripcion, $id_pa, $id_unid, $cantidad);
                    $stmt3->execute();
                }
            }
        }

        // Si todo salió bien, confirmamos los cambios
        $conexion->commit();
        $_SESSION['mensaje_user_exito'] = '✅ Los cambios se guardaron correctamente.';

    } catch (Exception $e) {
        // Si hay error, revertimos todo para no dejar datos corruptos
        $conexion->rollback();
        $_SESSION['mensaje_user_error'] = '❌ Error al actualizar: ' . $e->getMessage();
    }

    header("location: ../../pages/php/farmacia_medicamentos_listado.php");
    exit();
}