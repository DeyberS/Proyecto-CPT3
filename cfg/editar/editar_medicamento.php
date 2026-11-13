<?php
session_start();
include('../conexion.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_descripcion = $_POST['Id']; 
    $id_medicamento = $_POST['Id_medicamento']; 
    $nombre_medicamento = $_POST['medicamento'];
    $id_presentacion            = $_POST['presentacion'];
    $via_aplicacion     = $_POST['via'];
    $contenido_neto       = $_POST['contenido_neto'];
    $almacenamiento     = $_POST['almacenamiento'];
    $excipientes  = $_POST['excipientes'];
    $id_laboratorio     = !empty($_POST['laboratorio']) ? (int)$_POST['laboratorio'] : null;
    $codigo_barras      = !empty($_POST['codigo_barras']) ? $_POST['codigo_barras'] : 'Ninguno';
    $cantidad_concentracion = !empty($_POST['cantidad_concentracion']) ? $_POST['cantidad_concentracion'] : null;
    $id_tipo_concentracion = !empty($_POST['tipo_concentracion']) ? $_POST['tipo_concentracion'] : null;
    $principios_raw     = $_POST['composicion_detallada']; 

    $conexion->begin_transaction();

    try {
        $stmt1 = $conexion->prepare("UPDATE medicamento SET nombre_medicamento = ? WHERE Id_medicamento = ?");
        $stmt1->bind_param("si", $nombre_medicamento, $id_medicamento);
        $stmt1->execute();

        $sql_desc = "UPDATE descripcion_medicamento SET 
                        Id_tipo_concentracion = ?,
                        Id_presentacion = ?, 
                        Id_laboratorio = ?, 
                        codigo_barras = ?, 
                        cantidad_concentracion = ?,
                        contenido_neto = ?, 
                        via_aplicacion = ?, 
                        almacenamiento = ?, 
                        excipientes = ? 
                     WHERE Id = ?";
        
        $stmt2 = $conexion->prepare($sql_desc);
        $stmt2->bind_param("iiissssssi",
            $id_tipo_concentracion, 
            $id_presentacion, 
            $id_laboratorio, 
            $codigo_barras, 
            $cantidad_concentracion,
            $contenido_neto, 
            $via_aplicacion, 
            $almacenamiento, 
            $excipientes, 
            $id_descripcion
        );
        $stmt2->execute();

        $stmt_del = $conexion->prepare("DELETE FROM detalle_principio_medicamento WHERE id_medicamento = ?");
        $stmt_del->bind_param("i", $id_descripcion);
        $stmt_del->execute();

        if (!empty($principios_raw)) {
            $stmt3 = $conexion->prepare("INSERT INTO detalle_principio_medicamento 
                (id_medicamento, id_principio_activo, id_tipo_unidad_medida, cantidad_unidad_medida) 
                VALUES (?, ?, ?, ?)");

            $filas_pa = explode('|', $principios_raw);
            foreach ($filas_pa as $fila) {
                $columnas = explode(',', $fila);
                if (count($columnas) == 3) {
                    $id_pa    = (int)$columnas[0];
                    $cantidad = (float)$columnas[1];
                    $id_unid  = (int)$columnas[2];

                    $stmt3->bind_param("iiid", $id_descripcion, $id_pa, $id_unid, $cantidad);
                    $stmt3->execute();
                }
            }
        }

        $patologias_raw = isset($_POST['patologias_seleccionadas']) ? $_POST['patologias_seleccionadas'] : '';
        $patologias = !empty($patologias_raw) ? explode('|', $patologias_raw) : [];

        $sql_delete = "DELETE FROM detalle_patologia_medicamento WHERE Id_medicamento = ?";
        $stmt_del = $conexion->prepare($sql_delete);
        $stmt_del->bind_param("i", $id_descripcion);
        $stmt_del->execute();

        if (!empty($patologias)) {
            $sql_insert = "INSERT INTO detalle_patologia_medicamento (Id_medicamento, Id_patologia) VALUES (?, ?)";
            $stmt_ins = $conexion->prepare($sql_insert);

            foreach ($patologias as $id_patologia) {
                $id_patologia = trim($id_patologia);
                if (!empty($id_patologia)) {
                    $stmt_ins->bind_param("ii", $id_descripcion, $id_patologia);
                    $stmt_ins->execute();
                }
            }
        }

        $conexion->commit();
        $_SESSION['mensaje_user_exito'] = '✅ Los cambios se guardaron correctamente.';

    } catch (Exception $e) {
        $conexion->rollback();
        $_SESSION['mensaje_user_error'] = '⚠️ Error al actualizar: ' . $e->getMessage();
    }

    header("location: ../../pages/php/farmacia_medicamentos_listado.php");
    exit();
}