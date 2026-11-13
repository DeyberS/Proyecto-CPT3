<?php
session_start();
include('../conexion.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_medicamento = $_POST['medicamento'];
    $id_presentacion            = $_POST['presentacion'];
    $via_aplicacion     = $_POST['via'];
    $contenido_neto       = $_POST['contenido_neto'];
    $almacenamiento     = $_POST['almacenamiento'];
    $excipientes  = $_POST['excipientes'];
    $id_laboratorio     = !empty($_POST['laboratorio']) ? (int)$_POST['laboratorio'] : null;
    $cantidad_concentracion = !empty($_POST['cantidad_concentracion']) ? $_POST['cantidad_concentracion'] : null;
    $id_tipo_concentracion = !empty($_POST['tipo_concentracion']) ? $_POST['tipo_concentracion'] : null;
    $codigo_barras      = !empty($_POST['codigo_barras']) ? (string)$_POST['codigo_barras'] : "Ninguno";
    $principios_raw     = $_POST['composicion_detallada']; 
    $patologias_raw     = $_POST['patologias_seleccionadas']; 

    $conexion->begin_transaction();

    try {
        $stmt1 = $conexion->prepare("INSERT INTO medicamento (nombre_medicamento, estatus) VALUES (?, 1)");
        $stmt1->bind_param("s", $nombre_medicamento);
        if (!$stmt1->execute()) {
            throw new Exception("⚠️ Error al guardar el medicamento: " . $stmt1->error);
        }
        $id_medicamento_generado = $stmt1->insert_id;

        $stmt2 = $conexion->prepare("INSERT INTO descripcion_medicamento 
            (via_aplicacion, almacenamiento, excipientes, stock_minimo, stock_maximo, codigo_barras, contenido_neto, cantidad_concentracion, Id_tipo_concentracion, Id_laboratorio, Id_presentacion, Id_medicamento, estatus) 
            VALUES (?, ?, ?, '0', '0', ?, ?, ?, ?, ?, ?, ?, '1')");
        
        $stmt2->bind_param("ssssssiiii", 
            $via_aplicacion, 
            $almacenamiento, 
            $excipientes, 
            $codigo_barras, 
            $contenido_neto,
            $cantidad_concentracion, 
            $id_tipo_concentracion,  
            $id_laboratorio, 
            $id_presentacion, 
            $id_medicamento_generado
        );
        
        if (!$stmt2->execute()) {
            throw new Exception("⚠️ Error al guardar la descripción: " . $stmt2->error);
        }
        $id_descripcion_generada = $stmt2->insert_id;

        if (!empty($principios_raw)) {
            $stmt3 = $conexion->prepare("INSERT INTO detalle_principio_medicamento 
                (id_medicamento, id_principio_activo, id_tipo_unidad_medida, cantidad_unidad_medida) 
                VALUES (?, ?, ?, ?)");

            $filas_pa = explode('|', $principios_raw);
            
            foreach ($filas_pa as $fila) {
                $columnas = explode(',', $fila);

                if (count($columnas) == 3) {
                    $id_pa = (int)$columnas[0];
                    $cantidad = (int)$columnas[1];
                    $id_unidad = (int)$columnas[2];

                    $stmt3->bind_param("iiii", 
                        $id_descripcion_generada, 
                        $id_pa, 
                        $id_unidad, 
                        $cantidad
                    );
                    
                    if (!$stmt3->execute()) {
                         throw new Exception("⚠️ Error al guardar principio activo: " . $stmt3->error);
                    }
                }
            }
        }

        if (!empty($patologias_raw)) {
            $stmt4 = $conexion->prepare("INSERT INTO detalle_patologia_medicamento 
            (Id_medicamento, Id_patologia) 
            VALUES (?, ?)");

            $filas_pat = explode('|', $patologias_raw);

            foreach ($filas_pat as $id_pat) {
                $id_pat_limpio = (int)$id_pat;

                if ($id_pat_limpio > 0) {
                    $stmt4->bind_param(
                        "ii",
                        $id_descripcion_generada,
                        $id_pat_limpio
                    );

                    if (!$stmt4->execute()) {
                        throw new Exception("⚠️ Error al guardar patología: " . $stmt4->error);
                    }
                }
            }
        }

        $conexion->commit();

        $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        if ($is_ajax) {
            $sql_pres = $conexion->query("SELECT nombre_presentacion FROM presentacion WHERE Id_presentacion = '$id_presentacion'");
            $row_pres = $sql_pres->fetch_assoc();

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'id_desc' => $id_descripcion_generada,
                'nombre_medicamento' => $nombre_medicamento,
                'nombre_presentacion' => $row_pres['nombre_presentacion'] ?? 'N/A'
            ]);
            exit();
        } else {
            $_SESSION['mensaje_user_exito'] = '✅ Éxito: El medicamento fue agregado correctamente.';
            header("location: ../../pages/php/farmacia_medicamentos_listado.php");
            exit();
        }
    } catch (Exception $e) {
        $conexion->rollback();
        error_log("⚠️ Error de transacción al agregar el medicamento: " . $e->getMessage());

        $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit();
        } else {
            $_SESSION['mensaje_user_error'] = '⚠️ Error de Registro: No se pudo registrar el medicamento. Detalle: ' . $e->getMessage();
            header("location: ../../pages/php/farmacia_medicamentos_listado.php");
            exit();
        }
    } {
    }
}
?>