<?php
// Iniciamos el buffer de salida para evitar que Warnings de PHP rompan el JSON
ob_start(); 
session_start();
require_once "../conexion.php"; // Ajusta el path según la ubicación real de tu conexion.php

$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_medicamento     = $_POST['medicamento'] ?? '';
    $id_presentacion        = $_POST['presentacion'] ?? '';
    $via_aplicacion         = $_POST['via'] ?? '';
    $contenido_neto         = $_POST['contenido_neto'] ?? '';
    $almacenamiento         = $_POST['almacenamiento'] ?? '';
    $excipientes            = $_POST['excipientes'] ?? '';
    $id_laboratorio         = !empty($_POST['laboratorio']) ? (int)$_POST['laboratorio'] : null;
    $cantidad_concentracion = !empty($_POST['cantidad_concentracion']) ? $_POST['cantidad_concentracion'] : null;
    $id_tipo_concentracion  = !empty($_POST['tipo_concentracion']) ? $_POST['tipo_concentracion'] : null;
    $codigo_barras          = !empty($_POST['codigo_barras']) ? (string)$_POST['codigo_barras'] : "Ninguno";

    $principios_raw         = $_POST['composicion_detallada'] ?? '';
    $patologias_raw         = $_POST['patologias_seleccionadas'] ?? '';

    $conexion->begin_transaction();

    try {
        // PASO 1: Insertar Medicamento
        $stmt1 = $conexion->prepare("INSERT INTO medicamento (nombre_medicamento, estatus) VALUES (?, 1)");
        $stmt1->bind_param("s", $nombre_medicamento);
        if (!$stmt1->execute()) throw new Exception("Error al guardar el medicamento: " . $stmt1->error);
        $id_medicamento_generado = $stmt1->insert_id;
        $stmt1->close();

        // PASO 2: Insertar Descripción
        $stmt2 = $conexion->prepare("INSERT INTO descripcion_medicamento 
            (via_aplicacion, almacenamiento, excipientes, stock_minimo, stock_maximo, codigo_barras, contenido_neto, cantidad_concentracion, Id_tipo_concentracion, Id_laboratorio, Id_presentacion, Id_medicamento, estatus) 
            VALUES (?, ?, ?, '0', '0', ?, ?, ?, ?, ?, ?, ?, '1')");
        $stmt2->bind_param("ssssssiiii", $via_aplicacion, $almacenamiento, $excipientes, $codigo_barras, $contenido_neto, $cantidad_concentracion, $id_tipo_concentracion, $id_laboratorio, $id_presentacion, $id_medicamento_generado);
        if (!$stmt2->execute()) throw new Exception("Error al guardar la descripción: " . $stmt2->error);
        $id_descripcion_generada = $stmt2->insert_id;
        $stmt2->close();

        // PASO 3: Insertar Principios Activos
        if (!empty($principios_raw)) {
            $stmt3 = $conexion->prepare("INSERT INTO detalle_principio_medicamento (id_medicamento, id_principio_activo, id_tipo_unidad_medida, cantidad_unidad_medida) VALUES (?, ?, ?, ?)");
            $filas_pa = explode('|', $principios_raw);
            foreach ($filas_pa as $fila) {
                $columnas = explode(',', $fila);
                if (count($columnas) == 3) {
                    $id_pa = (int)$columnas[0];
                    $cantidad = (int)$columnas[1];
                    $id_unidad = (int)$columnas[2];
                    $stmt3->bind_param("iiii", $id_descripcion_generada, $id_pa, $id_unidad, $cantidad);
                    if (!$stmt3->execute()) throw new Exception("Error al guardar principio activo: " . $stmt3->error);
                }
            }
            $stmt3->close();
        }

        // PASO 4: Insertar Patologías
        if (!empty($patologias_raw)) {
            $stmt4 = $conexion->prepare("INSERT INTO detalle_patologia_medicamento (Id_medicamento, Id_patologia) VALUES (?, ?)");
            $filas_pat = explode('|', $patologias_raw);
            foreach ($filas_pat as $id_pat) {
                $id_pat_limpio = (int)$id_pat;
                if ($id_pat_limpio > 0) {
                    $stmt4->bind_param("ii", $id_descripcion_generada, $id_pat_limpio);
                    if (!$stmt4->execute()) throw new Exception("Error al guardar patología: " . $stmt4->error);
                }
            }
            $stmt4->close();
        }

        $conexion->commit();

        // Buscar el nombre de la presentación
        $sql_pres = $conexion->query("SELECT nombre_presentacion FROM presentacion WHERE Id_presentacion = '$id_presentacion'");
        $row_pres = $sql_pres->fetch_assoc();
        $nombre_presentacion = $row_pres['nombre_presentacion'] ?? 'N/A';

        // Construir el string de los componentes
        $componentes_string = "";
        if (!empty($principios_raw)) {
            $nombres_pa = [];
            $filas_pa = explode('|', $principios_raw);
            foreach ($filas_pa as $fila) {
                $columnas = explode(',', $fila);
                if (count($columnas) == 3) {
                    $id_pa = (int)$columnas[0];
                    $cantidad = $columnas[1];
                    $id_unidad = (int)$columnas[2];

                    $sql_pa_info = $conexion->query("SELECT pa.nombre, um.unidad FROM principio_activo pa, unidad_medida um WHERE pa.Id_principio_activo = '$id_pa' AND um.Id_unidad_medida = '$id_unidad'");
                    if ($row_pa = $sql_pa_info->fetch_assoc()) {
                        $nombres_pa[] = $row_pa['nombre'] . " " . $cantidad . " " . $row_pa['unidad'];
                    }
                }
            }
            $componentes_string = implode(' + ', $nombres_pa);
        }

        // Preparamos la respuesta de éxito
        $response = [
            'success' => true,
            'id_desc' => $id_descripcion_generada,
            'nombre_medicamento' => $nombre_medicamento,
            'nombre_presentacion' => $nombre_presentacion,
            'componentes' => $componentes_string,
            'message' => 'Medicamento registrado correctamente.'
        ];

    } catch (Exception $e) {
        $conexion->rollback();
        // Preparamos la respuesta de error
        $response = ['success' => false, 'message' => $e->getMessage()];
    }
    
    if(isset($conexion)) $conexion->close();
} else {
    $response = ['success' => false, 'message' => 'Método no permitido.'];
}

// Limpiamos el buffer (borrando cualquier HTML/Warning que haya intentado colarse)
ob_end_clean();

// Enviamos únicamente el JSON
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>