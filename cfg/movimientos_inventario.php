<?php
include("conexion.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_desc = intval($_POST['Id_descripcion_medicamento'] ?? 0);
    $cantidad = intval($_POST['cantidad'] ?? 0);
    $id_usuario = $_SESSION['id'] ?? 0;
    $observaciones_base = $_POST['observaciones'] ?? '';
    $id_presc = $_POST['id_prescripcion'] ?? null;

    if ($id_desc <= 0) {
        $_SESSION['mensaje_user_error'] = "Medicamento inválido.";
        header("Location: ../pages/php/farmacia_inventario_listado.php");
        exit;
    }

    $conexion->begin_transaction();

    try {

        /* =====================================================
           ========== DETECTAR OPERACIÓN =======================
        ======================================================*/

        $esEntrada = isset($_POST['fecha_fabricacion']) && isset($_POST['fecha_vencimiento']);
        $esAjuste  = isset($_POST['stock_minimo']) && isset($_POST['stock_maximo']);
        $esSalida  = !$esEntrada && !$esAjuste && isset($_POST['lote']);

        /* =====================================================
           ================= AJUSTE ============================
        ======================================================*/

        if ($esAjuste) {

            $stock_min = intval($_POST['stock_minimo']);
            $stock_max = intval($_POST['stock_maximo']);

            $sql = "
                UPDATE descripcion_medicamento
                SET stock_minimo=?, stock_maximo=?
                WHERE Id=?";

            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("iii",$stock_min,$stock_max,$id_desc);
            $stmt->execute();

            $conexion->commit();

            $_SESSION['mensaje_user_exito'] = "Ajuste de stock actualizado.";
            header("Location: ../pages/php/farmacia_inventario_listado.php");
            exit;
        }

        /* =====================================================
           ================= ENTRADA ===========================
        ======================================================*/

        if ($esEntrada) {

            $lote_nombre = trim($_POST['lote']);
            $proveedor = trim($_POST['proveedor']);
            $f_fab = $_POST['fecha_fabricacion'];
            $f_ven = $_POST['fecha_vencimiento'];

            $sql = "
                SELECT Id FROM lotes_medicamentos
                WHERE Lote=? AND Id_descripcion_medicamento=?";

            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("si",$lote_nombre,$id_desc);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res->num_rows > 0) {
                $id_lote = $res->fetch_assoc()['Id'];
            } else {

                $sql = "
                    INSERT INTO lotes_medicamentos
                    (Id_descripcion_medicamento,Id_proveedor,Lote,fecha_fabricacion,fecha_vencimiento,estado_lote)
                    VALUES (?,?,?,?,?,1)";

                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("iisss",$id_desc,$proveedor,$lote_nombre,$f_fab,$f_ven);
                $stmt->execute();

                $id_lote = $conexion->insert_id;
            }

            $tipo_mov = 1;
        }

        /* =====================================================
           ================= SALIDA ============================
        ======================================================*/

        if ($esSalida) {

            // 📥 detectar lote aunque cambie nombre
            if (isset($_POST['lote'])) {
                $id_lote = intval($_POST['lote']);
            } elseif (isset($_POST['Id_lote'])) {
                $id_lote = intval($_POST['Id_lote']);
            } elseif (isset($_POST['id_lote'])) {
                $id_lote = intval($_POST['id_lote']);
            } else {
                throw new Exception("No se recibió el lote.");
            }
        
            if ($id_lote <= 0) {
                throw new Exception("Lote vacío o inválido.");
            }
        
            // 🔒 validar lote y stock
            $sql = "
                SELECT e.cantidad_actual
                FROM lotes_medicamentos l
                JOIN existencias_stock e ON l.Id = e.Id_lote
                WHERE l.Id = ?
                AND l.Id_descripcion_medicamento = ?
                AND l.estatus = 1
                FOR UPDATE";

            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("ii",$id_lote,$id_desc);
            $stmt->execute();
            $res = $stmt->get_result();
        
            if ($res->num_rows === 0) {
                throw new Exception("Lote vacío, no existe o no pertenece al medicamento.");
            }
        
            $stock_lote = $res->fetch_assoc()['cantidad_actual'];
        
            if ($stock_lote < $cantidad) {
                throw new Exception("Stock insuficiente en ese lote.");
            }

            /* =====================================================
            ================= VALIDACIÓN DETALLADA ============
            ======================================================*/
            // 1. Verificamos si el lote existe por sí solo
            $sql_test = "SELECT Id_descripcion_medicamento, estatus FROM lotes_medicamentos WHERE Id = ?";
            $stmt_t = $conexion->prepare($sql_test);
            $stmt_t->bind_param("i", $id_lote);
            $stmt_t->execute();
            $res_t = $stmt_t->get_result();

            if ($res_t->num_rows === 0) {
                throw new Exception("El ID de lote $id_lote no existe en la tabla lotes_medicamentos.");
            }

            $lote_data = $res_t->fetch_assoc();

            // 2. Comparamos los IDs
            if (intval($lote_data['Id_descripcion_medicamento']) !== $id_desc) {
                throw new Exception("Conflicto: El lote pertenece al medicamento ID: " . $lote_data['Id_descripcion_medicamento'] . " pero intentas sacarlo del ID: " . $id_desc);
            }

            // 3. Verificamos el estatus
            if ($lote_data['estatus'] != 1) {
                throw new Exception("El lote existe pero su estatus es " . $lote_data['estatus'] . " (debe ser 1).");
            }

            // 4. Si todo lo anterior pasó, verificamos el stock
            $sql_final = "SELECT cantidad_actual FROM existencias_stock WHERE Id_lote = ? AND Id_descripcion_medicamento = ?";
            $stmt_f = $conexion->prepare($sql_final);
            $stmt_f->bind_param("ii",
                $id_lote,
                $id_desc
            );
            $stmt_f->execute();
            $res_f = $stmt_f->get_result();

            if ($res_f->num_rows === 0) {
                throw new Exception("El lote existe, pero no tiene registro de cantidades en la tabla existencias_stock.");
            }

            $stock_lote = $res_f->fetch_assoc()['cantidad_actual'];
            $tipo_mov = 2;
        
            $tipo_mov = 2;
        }

        /* =====================================================
           =============== STOCK GLOBAL ========================
        ======================================================*/

        $sql = "
            SELECT SUM(cantidad_actual) total
            FROM existencias_stock
            WHERE Id_descripcion_medicamento=?";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i",$id_desc);
        $stmt->execute();

        $stk_previo = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

        $stock_momento =
            ($esEntrada)
            ? $stk_previo + $cantidad
            : $stk_previo - $cantidad;

        /* =====================================================
        ================= CABECERA UNIFICADA ================
        ======================================================*/

        $id_prescripcion_final = (!empty($_POST['id_prescripcion'])) ? intval($_POST['id_prescripcion']) : null;

        $obs_final = $_POST['observaciones'] ?? '';

        // Si es Externo, metemos los datos en la observación
        if ($obs_final === 'Récipe Externo') {
            $med = $_POST['medico_externo'] ?? 'N/A';
            $pac = $_POST['paciente_externo_nombre'] ?? 'N/A';
            $pac_tce = $_POST['tipo_cedula_ext'] ?? 'N/A';
            $ce = $_POST['paciente_externo_cedula'] ?? 'N/A';
            $num = $_POST['numero_recipe'] ?? 'N/A';
            $obs_final .= " | Médico: $med | Paciente: $pac $pac_tce $ce  | Receta Ext: $num";
        }

        $foto_base64 = $_POST['foto_base64'] ?? '';
        $nombre_archivo_final = null;

        if (!empty($foto_base64)) {
            if (preg_match('/^data:image\/(\w+);base64,/', $foto_base64, $type)) {
                $data = substr($foto_base64, strpos($foto_base64, ',') + 1);
                $type = strtolower($type[1]);
                $data = base64_decode($data);

                $nombre_archivo_final = "comp_" . time() . "_" . uniqid() . "." . $type;
                $ruta_carpeta = "../recursos/comprobantes/";

                if (!is_dir($ruta_carpeta)) {
                    mkdir($ruta_carpeta, 0777, true);
                }

                file_put_contents($ruta_carpeta . $nombre_archivo_final, $data);
            }
        }

        // 1. Preparamos el SQL
        $sql = "INSERT INTO detalle_inventario 
        (Id_TipoMovimiento, Id_Persona, fecha, observaciones, Id_prescripcion, comprobante) 
        VALUES (?, ?, NOW(), ?, ?, ?)";

        $stmt = $conexion->prepare($sql);

        // 2. Vinculamos parámetros
        $stmt->bind_param(
            "iisis",
            $tipo_mov,
            $id_usuario,
            $obs_final,
            $id_prescripcion_final,
            $nombre_archivo_final
        );

        // 3. ¡IMPORTANTE! Ejecutar la sentencia antes de pedir el ID
        if (!$stmt->execute()) {
            throw new Exception("Error al insertar en detalle_inventario: " . $stmt->error);
        }

        // 4. Ahora sí obtenemos el ID generado
        $id_mov = $conexion->insert_id;


        if ($esSalida && $id_presc> 0) {
            // Cambiamos el estatus a una cadena de texto, por ejemplo: 'Entregado'
            $nuevo_estado = "entregado"; 
            
            $sql_update_presc = "UPDATE prescripcion_medicamentos SET estado_prescripcion = ? WHERE Id = ?";
            $stmt_p = $conexion->prepare($sql_update_presc);
            
            // "s" es para string (cadena), "i" es para integer (entero)
            $stmt_p->bind_param("si", $nuevo_estado, $id_presc); 
            $stmt_p->execute();
        }

        /* =====================================================
           ================= DETALLE ===========================
        ======================================================*/

        $sql = "
            INSERT INTO medicamentos_detalle_inventario
            (Id_detalle_inventario,
             Id_descripcion_medicamento,
             Id_lote,
             cantidad,
             stock_momento)
            VALUES (?,?,?,?,?)";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param(
            "iiiii",
            $id_mov,
            $id_desc,
            $id_lote,
            $cantidad,
            $stock_momento
        );
        $stmt->execute();

        /* =====================================================
           ================ EXISTENCIAS ========================
        ======================================================*/

        if ($esEntrada) {

            $sql = "
                INSERT INTO existencias_stock
                (Id_descripcion_medicamento,Id_lote,cantidad_actual)
                VALUES (?,?,?)
                ON DUPLICATE KEY UPDATE
                cantidad_actual = cantidad_actual + VALUES(cantidad_actual)";

            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("iii",$id_desc,$id_lote,$cantidad);

        } else {

            $sql = "
                UPDATE existencias_stock
                SET cantidad_actual = cantidad_actual - ?
                WHERE Id_descripcion_medicamento=?
                AND Id_lote=?";

            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("iii",$cantidad,$id_desc,$id_lote);
        }

        $stmt->execute();

        $conexion->commit();

        $_SESSION['mensaje_user_exito'] = "Movimiento procesado correctamente.";

    } catch (Exception $e) {

        $conexion->rollback();

        $_SESSION['mensaje_user_error'] = $e->getMessage();
    }

    header("Location: ../pages/php/farmacia_inventario_listado.php");
}
?>

