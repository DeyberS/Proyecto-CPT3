<?php
include("conexion.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_usuario = $_SESSION['id'] ?? 0;
    $op = $_POST['op'] ?? '';

    /* =====================================================
       ========== 1. ENTRADA DE INVENTARIO (MULTIPLE) ======
    ======================================================*/
    if ($op === 'entrada' && !empty($_POST['detalle_medicamentos'])) {
        $conexion->begin_transaction();
        try {
            $detalles = json_decode($_POST['detalle_medicamentos'], true);
            if (!is_array($detalles) || count($detalles) === 0) {
                throw new Exception("No se recibieron medicamentos para la entrada.");
            }

            $proveedor = intval($_POST['proveedor'] ?? 0);
            if ($proveedor <= 0) {
                throw new Exception("Debe seleccionar un proveedor o donante.");
            }

            $obs_final = $_POST['observaciones_generales'] ?? '';
            $fecha_recepcion = $_POST['fecha_recepcion'] ?? date('Y-m-d');
            $fecha_hora = $fecha_recepcion . ' ' . date('H:i:s');
            
            // Insertar Cabecera
            $sql_cabecera = "INSERT INTO detalle_inventario (Id_TipoMovimiento, Id_Persona, fecha, observaciones) VALUES (1, ?, NOW(), ?)";
            $stmt_cab = $conexion->prepare($sql_cabecera);
            $stmt_cab->bind_param("is", $id_usuario, $obs_final);
            $stmt_cab->execute();
            $id_mov = $conexion->insert_id;

            foreach ($detalles as $item) {
                $id_desc_item = intval($item['id_medicamento']);
                $lote_nombre = trim($item['lote']);
                $f_fab = $item['fecha_fabricacion'];
                $f_ven = $item['fecha_vencimiento'];
                $cant_item = intval($item['cantidad']);

                // Buscar o crear lote
                $sql_lote = "SELECT Id FROM lotes_medicamentos WHERE Lote=? AND Id_descripcion_medicamento=?";
                $stmt_l = $conexion->prepare($sql_lote);
                $stmt_l->bind_param("si", $lote_nombre, $id_desc_item);
                $stmt_l->execute();
                $res_l = $stmt_l->get_result();

                if ($res_l->num_rows > 0) {
                    $id_lote_item = $res_l->fetch_assoc()['Id'];
                } else {
                    $sql_ins_lote = "INSERT INTO lotes_medicamentos (Id_descripcion_medicamento, Id_proveedor, Lote, fecha_fabricacion, fecha_vencimiento, estado_lote) VALUES (?, ?, ?, ?, ?, 1)";
                    $stmt_il = $conexion->prepare($sql_ins_lote);
                    $stmt_il->bind_param("iisss", $id_desc_item, $proveedor, $lote_nombre, $f_fab, $f_ven);
                    $stmt_il->execute();
                    $id_lote_item = $conexion->insert_id;
                }

                // Calcular stock momento (global)
                $sql_stk = "SELECT SUM(cantidad_actual) as total FROM existencias_stock WHERE Id_descripcion_medicamento=?";
                $stmt_stk = $conexion->prepare($sql_stk);
                $stmt_stk->bind_param("i", $id_desc_item);
                $stmt_stk->execute();
                $stk_previo = $stmt_stk->get_result()->fetch_assoc()['total'] ?? 0;

                $stock_momento_item = $stk_previo + $cant_item;

                // Insertar Detalle
                $sql_det = "INSERT INTO medicamentos_detalle_inventario (Id_detalle_inventario, Id_descripcion_medicamento, Id_lote, cantidad, stock_momento) VALUES (?, ?, ?, ?, ?)";
                $stmt_det = $conexion->prepare($sql_det);
                $stmt_det->bind_param("iiiii", $id_mov, $id_desc_item, $id_lote_item, $cant_item, $stock_momento_item);
                $stmt_det->execute();

                // Actualizar Existencias
                $sql_ex = "INSERT INTO existencias_stock (Id_descripcion_medicamento, Id_lote, cantidad_actual) VALUES (?, ?, ?)
                           ON DUPLICATE KEY UPDATE cantidad_actual = cantidad_actual + VALUES(cantidad_actual)";
                $stmt_ex = $conexion->prepare($sql_ex);
                $stmt_ex->bind_param("iii", $id_desc_item, $id_lote_item, $cant_item);
                $stmt_ex->execute();
            }

            $conexion->commit();
            $_SESSION['mensaje_user_exito'] = "Entrada procesada correctamente.";
        } catch (Exception $e) {
            $conexion->rollback();
            $_SESSION['mensaje_user_error'] = $e->getMessage();
        }
        header("Location: ../pages/php/farmacia_inventario_listado.php");
        exit;
    }

    /* =====================================================
       ========== 2. AJUSTE DE STOCK (MIN/MAX) =============
    ======================================================*/
    if (isset($_POST['stock_minimo']) && isset($_POST['stock_maximo'])) {
        try {
            $id_desc = intval($_POST['Id_descripcion_medicamento']);
            $stock_min = intval($_POST['stock_minimo']);
            $stock_max = intval($_POST['stock_maximo']);

            $sql = "UPDATE descripcion_medicamento SET stock_minimo=?, stock_maximo=? WHERE Id=?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("iii", $stock_min, $stock_max, $id_desc);
            $stmt->execute();

            $_SESSION['mensaje_user_exito'] = "Límites de stock actualizados.";
        } catch (Exception $e) {
            $_SESSION['mensaje_user_error'] = $e->getMessage();
        }
        header("Location: ../pages/php/farmacia_inventario_listado.php");
        exit;
    }

    /*=====================================================
    ========== 2. SALIDA DE INVENTARIO (DESPACHO) =======
    ======================================================*/
    if ($op === 'despacho' && !empty($_POST['detalle_medicamentos'])) {
        $conexion->begin_transaction();
        try {
            $detalles = json_decode($_POST['detalle_medicamentos'], true);
            $tipo_despacho = $_POST['tipo_despacho'] ?? 'interno';
            $id_presc_form = intval($_POST['id_prescripcion'] ?? 0);
            
            // --- 1. LÓGICA DE OBSERVACIONES DINÁMICAS ---
            // Capturamos a quién se le entrega. Asegúrate de tener un <input name="nombre_receptor"> en tu form para internos/repres.
            $paciente_ext = $_POST['paciente_externo'] ?? '';
            $nombre_receptor = $_POST['nombre_receptor'] ?? $paciente_ext; 
            if (empty($nombre_receptor)) $nombre_receptor = 'No especificado';

            $obs_base = "";
            switch (strtolower($tipo_despacho)) {
                case 'interno':
                    $obs_base = "Despacho a paciente interno: " . $nombre_receptor;
                    break;
                case 'representante':
                    $obs_base = "Entrega realizada al representante: " . $nombre_receptor;
                    break;
                case 'externo':
                    $obs_base = "Despacho a paciente externo: " . $nombre_receptor;
                    break;
                default:
                    $obs_base = "Despacho general: " . $nombre_receptor;
                    break;
            }

            $obs_del_formulario = $_POST['observaciones_generales'] ?? '';
            // Unimos nuestra nota dinámica con la que haya escrito el usuario
            $obs_final = !empty($obs_del_formulario) ? $obs_base . " | Nota: " . $obs_del_formulario : $obs_base;

            // --- 2. LÓGICA DE DOSIS ---
            $obs_dosis = [];
            foreach ($detalles as $item) {
                if (!empty($item['dosis'])) {
                    $obs_dosis[] = $item['nombre_medicamento'] . " (" . $item['dosis'] . ")";
                }
            }
            if (count($obs_dosis) > 0) {
                $separador = ($obs_final != '') ? " | " : "";
                $obs_final .= $separador . "Indicaciones: " . implode(", ", $obs_dosis);
            }

            // --- RESOLUCIÓN Y EXTRACCIÓN DE ID CORRECTOS ---
            $id_solicitud_ext = null;
            $id_solicitud_existente = null;
            $id_consulta_interno = null;

            if ($tipo_despacho === 'externo') {
                if ($id_presc_form > 0) {
                    // Primero verificamos si el ID que llega es directamente el de la solicitud padre
                    $res_chk = $conexion->query("SELECT id_solicitud FROM solicitud_medicamento WHERE id_solicitud = " . $id_presc_form);
                    if ($res_chk && $res_chk->num_rows > 0) {
                        $id_solicitud_existente = $id_presc_form;
                    } else {
                        // Si no, verificamos si es el ID del detalle
                        $res = $conexion->query("SELECT id_solicitud FROM detalle_solicitud WHERE id_detalle = " . $id_presc_form);
                        if ($row = $res->fetch_assoc()) {
                            $id_solicitud_existente = $row['id_solicitud'];
                        }
                    }
                }

                // Si sigue siendo null, entonces sí es una solicitud externa totalmente nueva
                if (!$id_solicitud_existente) {
                    $tipo_cedula_ext = $_POST['tipo_cedula_externo'] ?? '';
                    $cedula_ext = $_POST['cedula_externo'] ?? '';
                    $medico_ext = $_POST['medico_externo'] ?? '';

                    $sql_sol = "INSERT INTO solicitud_medicamento (origen, datos_paciente_externo, tipo_cedula_externo, cedula_externo, datos_medico_externo, fecha_solicitud, estatus_general) 
                                VALUES ('Externo', ?, ?, ?, ?, NOW(), 'Pendiente')";
                    $stmt_sol = $conexion->prepare($sql_sol);
                    $stmt_sol->bind_param("ssss", $paciente_ext, $tipo_cedula_ext, $cedula_ext, $medico_ext);
                    $stmt_sol->execute();
                    $id_solicitud_ext = $conexion->insert_id;
                }
            } else {
                if ($id_presc_form > 0) {
                    // ¡CORRECCIÓN AQUÍ!
                    // Como el frontend ahora envía el Id_consulta directamente,
                    // verificamos directamente en la tabla consulta.
                    $res = $conexion->query("SELECT Id_consulta FROM consulta WHERE Id_consulta = " . $id_presc_form);
                    if ($row = $res->fetch_assoc()) {
                        $id_consulta_interno = $row['Id_consulta'];
                    }
                }
            }
            // ------------------------------------------------

            // CREAR CABECERA DE INVENTARIO CON LA OBSERVACIÓN DINÁMICA
            // Nota: Aquí se usa $obs_final en lugar de la variable directa del form
            $sql_cab = "INSERT INTO detalle_inventario (Id_TipoMovimiento, Id_Persona, fecha, observaciones) 
                        VALUES (2, ?, NOW(), ?)";
            $stmt_cab = $conexion->prepare($sql_cab);
            $stmt_cab->bind_param("is", $id_usuario, $obs_final);
            $stmt_cab->execute();
            $id_mov_inv = $conexion->insert_id;

            // RECORRER CADA MEDICAMENTO
            foreach ($detalles as $item) {
                $id_desc = intval($item['id_medicamento']);
                $lote_nombre = $item['lote'];
                $cant = intval($item['cantidad']); // Cantidad a despachar AHORA
                $cant_recetada = isset($item['cantidad_recetada']) ? intval($item['cantidad_recetada']) : $cant;

                // --- 3. CÁLCULO DEL ACUMULADO PARA ENTREGAS PARCIALES ---
                // Verifica si desde el frontend (JS) estás enviando cuánto se ha entregado antes. Si no, asumimos 0.
                $cantidad_previa = isset($item['cantidad_ya_entregada']) ? intval($item['cantidad_ya_entregada']) : 0;
                
                // Buscar lote y stock actual
                $sql_l = "SELECT e.Id_lote, e.cantidad_actual FROM existencias_stock e 
                          JOIN lotes_medicamentos l ON e.Id_lote = l.Id 
                          WHERE l.Lote = ? AND e.Id_descripcion_medicamento = ?";
                $stmt_l = $conexion->prepare($sql_l);
                $stmt_l->bind_param("si", $lote_nombre, $id_desc);
                $stmt_l->execute();
                $res_l = $stmt_l->get_result()->fetch_assoc();

                if (!$res_l || $res_l['cantidad_actual'] < $cant) {
                    throw new Exception("Stock insuficiente para el medicamento ID: $id_desc");
                }

                $nuevo_stock = $res_l['cantidad_actual'] - $cant;

                // A. Detalle del movimiento 
                $sql_det_inv = "INSERT INTO medicamentos_detalle_inventario (Id_detalle_inventario, Id_descripcion_medicamento, Id_lote, cantidad, stock_momento) 
                                VALUES (?, ?, ?, ?, ?)";
                $stmt_det = $conexion->prepare($sql_det_inv);
                $stmt_det->bind_param("iiiii", $id_mov_inv, $id_desc, $res_l['Id_lote'], $cant, $nuevo_stock);
                $stmt_det->execute();

                // B. Actualizar Existencias
                $sql_upd = "UPDATE existencias_stock SET cantidad_actual = ? WHERE Id_lote = ? AND Id_descripcion_medicamento = ?";
                $stmt_upd = $conexion->prepare($sql_upd);
                $stmt_upd->bind_param("iii", $nuevo_stock, $res_l['Id_lote'], $id_desc);
                $stmt_upd->execute();

                // C. Actualización Inteligente de Estatus (CORREGIDO)
                if ($tipo_despacho === 'externo') {
                    if ($id_solicitud_existente) {
                        // Para externos, la BD ya tiene la columna "cantidad_entregada", así que calculamos directamente en SQL:
                        $sql_get = "SELECT cantidad_recetada, cantidad_entregada FROM detalle_solicitud WHERE id_solicitud = ? AND id_medicamento = ?";
                        $stmt_get = $conexion->prepare($sql_get);
                        $stmt_get->bind_param("ii", $id_solicitud_existente, $id_desc);
                        $stmt_get->execute();
                        $res_get = $stmt_get->get_result()->fetch_assoc();

                        if ($res_get) {
                            $nueva_cantidad_entregada = $res_get['cantidad_entregada'] + $cant;
                            $estatus_item = ($nueva_cantidad_entregada >= $res_get['cantidad_recetada']) ? 'Entregado' : 'Parcialmente Entregado';

                            $sql_upd_ds = "UPDATE detalle_solicitud SET cantidad_entregada = ?, estatus_item = ? 
                                           WHERE id_solicitud = ? AND id_medicamento = ?";
                            $stmt_upd_ds = $conexion->prepare($sql_upd_ds);
                            $stmt_upd_ds->bind_param("isii", $nueva_cantidad_entregada, $estatus_item, $id_solicitud_existente, $id_desc);
                            $stmt_upd_ds->execute();
                        }
                    } elseif ($id_solicitud_ext) {
                        $estatus_item = ($cant >= $cant_recetada) ? 'Entregado' : 'Parcialmente Entregado';
                        $sql_det_sol = "INSERT INTO detalle_solicitud (id_solicitud, id_medicamento, cantidad_recetada, cantidad_entregada, estatus_item) 
                                        VALUES (?, ?, ?, ?, ?)";
                        $stmt_ds = $conexion->prepare($sql_det_sol);
                        $stmt_ds->bind_param("iiiis", $id_solicitud_ext, $id_desc, $cant_recetada, $cant, $estatus_item);
                        $stmt_ds->execute();
                    }
                } else {
                    // Para Internos / Representantes
                    if ($id_consulta_interno) {
                        // Como la cantidad siempre es 1, al despacharlo el estado pasa directamente a 'entregado'
                        $sql_upd_presc = "UPDATE prescripcion_medicamentos 
                                          SET estado_prescripcion = 'entregado' 
                                          WHERE Id_consulta = ? AND Id_descripcion_medicamento = ?";
                        $stmt_upd_presc = $conexion->prepare($sql_upd_presc);
                        
                        if ($stmt_upd_presc) {
                            $stmt_upd_presc->bind_param("ii", $id_consulta_interno, $id_desc);
                            $stmt_upd_presc->execute();
                        } else {
                            throw new Exception("Error al actualizar estado de la receta: " . $conexion->error);
                        }
                    }
                }
            }

        if (($tipo_despacho === 'interno' || $tipo_despacho === 'representante') && $id_consulta_interno) {
                
            $sql_check_int = "SELECT 
                                COUNT(*) as total_items,
                                SUM(CASE WHEN estado_prescripcion = 'entregado' THEN 1 ELSE 0 END) as total_entregados,
                                SUM(CASE WHEN estado_prescripcion IN ('pendiente', 'parcial') THEN 1 ELSE 0 END) as total_pendientes
                              FROM prescripcion_medicamentos WHERE Id_consulta = ?";
            
            $stmt_check_int = $conexion->prepare($sql_check_int);
            $stmt_check_int->bind_param("i", $id_consulta_interno);
            $stmt_check_int->execute();
            $res_check_int = $stmt_check_int->get_result()->fetch_assoc();

            // Forzamos el casteo a enteros para que PHP no falle en la matemática
            $total_items = (int)$res_check_int['total_items'];
            $total_entregados = (int)$res_check_int['total_entregados'];
            $total_pendientes = (int)$res_check_int['total_pendientes'];

            $nuevo_estatus_general = 'Pendiente';

            // Lógica de estado general
            if ($total_entregados > 0 && $total_entregados === $total_items) {
                $nuevo_estatus_general = 'Completado';
            } elseif ($total_entregados > 0 || ($total_pendientes > 0 && $total_pendientes < $total_items)) {
                $nuevo_estatus_general = 'Parcial';
            }

            // Actualizamos el estado de la receta general vinculada a esta consulta
            $sql_upd_general_int = "UPDATE solicitud_medicamento SET estatus_general = ? WHERE id_consulta = ? AND origen = 'Interno'";
            $stmt_upd_general_int = $conexion->prepare($sql_upd_general_int);
            $stmt_upd_general_int->bind_param("si", $nuevo_estatus_general, $id_consulta_interno);
            $stmt_upd_general_int->execute();
        }

        if ($tipo_despacho === 'externo' && ($id_solicitud_existente || $id_solicitud_ext)) {
            $id_evaluar = $id_solicitud_existente ? $id_solicitud_existente : $id_solicitud_ext;
            
            $sql_check_ext = "SELECT 
                                COUNT(*) as total_items,
                                SUM(CASE WHEN estatus_item = 'Entregado' THEN 1 ELSE 0 END) as total_entregados,
                                SUM(CASE WHEN estatus_item = 'Parcialmente Entregado' THEN 1 ELSE 0 END) as total_parciales
                              FROM detalle_solicitud WHERE id_solicitud = ?";
            
            $stmt_check_ext = $conexion->prepare($sql_check_ext);
            $stmt_check_ext->bind_param("i", $id_evaluar);
            $stmt_check_ext->execute();
            $res_check_ext = $stmt_check_ext->get_result()->fetch_assoc();

            $total_items = (int)$res_check_ext['total_items'];
            $total_entregados = (int)$res_check_ext['total_entregados'];
            $total_parciales = (int)$res_check_ext['total_parciales'];

            $nuevo_estatus_general = 'Pendiente';

            if ($total_entregados > 0 && $total_entregados === $total_items) {
                $nuevo_estatus_general = 'Completado';
            } elseif ($total_entregados > 0 || $total_parciales > 0) {
                $nuevo_estatus_general = 'Parcial';
            }

            // Actualizamos el estado general de la solicitud externa
            $sql_upd_general_ext = "UPDATE solicitud_medicamento SET estatus_general = ? WHERE id_solicitud = ?";
            $stmt_upd_general_ext = $conexion->prepare($sql_upd_general_ext);
            $stmt_upd_general_ext->bind_param("si", $nuevo_estatus_general, $id_evaluar);
            $stmt_upd_general_ext->execute();
        }

            $conexion->commit();
            $_SESSION['mensaje_user_exito'] = "Despacho procesado correctamente.";
            header("Location: ../pages/php/farmacia_inventario_listado.php");
            exit;
        } catch (Exception $e) {
            $conexion->rollback();
            $_SESSION['mensaje_user_error'] = "Error: " . $e->getMessage();
            header("Location: ../pages/php/farmacia_inventario_listado.php");
            exit;
        }
    }

    /* =====================================================
       ========== 4. SALIDA DE INVENTARIO (BAJAS/AJUSTES) ==
    ======================================================*/
    if ($op === 'ajuste_salida' && !empty($_POST['detalle_medicamentos'])) {
        $conexion->begin_transaction();
        try {
            $detalles = json_decode($_POST['detalle_medicamentos'], true);
            if (!is_array($detalles) || count($detalles) === 0) {
                throw new Exception("No se recibieron medicamentos para dar de baja.");
            }

            // Datos de la cabecera
            $id_tipo_mov = intval($_POST['id_tipo_movimiento']); // IDs 3, 4, 5, 7...
            $obs_final = $_POST['observaciones_generales'] ?? '';
            
            // Procesamiento de la Evidencia (Imagen Base64)
            $foto_base64 = $_POST['foto_base64'] ?? '';
            $ruta_comprobante = null;

            if (!empty($foto_base64)) {
                $image_parts = explode(";base64,", $foto_base64);
                if (count($image_parts) == 2) {
                    $image_base64 = base64_decode($image_parts[1]);
                    // Generar un nombre único para la imagen
                    $nombre_archivo = 'evidencia_ajuste_' . time() . '_' . rand(1000,9999) . '.jpg';
                    // Asumiendo que este script está en la carpeta cfg/, subimos a la raíz y entramos a recursos
                    $dir_absoluto = '../../recursos/imagenes/comprobantes/';
                    
                    // Crear el directorio si no existe
                    if (!file_exists($dir_absoluto)) {
                        mkdir($dir_absoluto, 0777, true);
                    }
                    
                    $ruta_guardado = $dir_absoluto . $nombre_archivo;
                    if (file_put_contents($ruta_guardado, $image_base64)) {
                        // Guardamos la ruta en la BD (la ruta relativa útil para mostrarla luego)
                        $ruta_comprobante = 'recursos/imagenes/comprobantes/' . $nombre_archivo;
                    }
                }
            }

            // 1. Insertar Cabecera en detalle_inventario (Incluyendo el comprobante)
            $sql_cab = "INSERT INTO detalle_inventario (Id_TipoMovimiento, Id_Persona, fecha, observaciones, comprobante) 
                        VALUES (?, ?, NOW(), ?, ?)";
            $stmt_cab = $conexion->prepare($sql_cab);
            $stmt_cab->bind_param("iiss", $id_tipo_mov, $id_usuario, $obs_final, $ruta_comprobante);
            $stmt_cab->execute();
            $id_mov_inv = $conexion->insert_id;

            // 2. Recorrer los detalles de la baja
            foreach ($detalles as $item) {
                $id_desc = intval($item['id_medicamento']);
                $id_lote = intval($item['lote_id']); // En el frontend de bajas, lo envías directamente como lote_id
                $cant_baja = intval($item['cantidad']);

                // Buscar stock actual por Lote
                $sql_l = "SELECT cantidad_actual FROM existencias_stock 
                          WHERE Id_lote = ? AND Id_descripcion_medicamento = ?";
                $stmt_l = $conexion->prepare($sql_l);
                $stmt_l->bind_param("ii", $id_lote, $id_desc);
                $stmt_l->execute();
                $res_l = $stmt_l->get_result()->fetch_assoc();

                if (!$res_l || $res_l['cantidad_actual'] < $cant_baja) {
                    throw new Exception("Stock insuficiente para procesar la baja del medicamento. (ID Desc: $id_desc, Lote: $id_lote)");
                }

                $nuevo_stock = $res_l['cantidad_actual'] - $cant_baja;

                // Detalle del movimiento
                $sql_det_inv = "INSERT INTO medicamentos_detalle_inventario (Id_detalle_inventario, Id_descripcion_medicamento, Id_lote, cantidad, stock_momento) 
                                VALUES (?, ?, ?, ?, ?)";
                $stmt_det = $conexion->prepare($sql_det_inv);
                $stmt_det->bind_param("iiiii", $id_mov_inv, $id_desc, $id_lote, $cant_baja, $nuevo_stock);
                $stmt_det->execute();

                // Actualizar Existencias
                $sql_upd = "UPDATE existencias_stock SET cantidad_actual = ? WHERE Id_lote = ? AND Id_descripcion_medicamento = ?";
                $stmt_upd = $conexion->prepare($sql_upd);
                $stmt_upd->bind_param("iii", $nuevo_stock, $id_lote, $id_desc);
                $stmt_upd->execute();
            }

            $conexion->commit();
            $_SESSION['mensaje_user_exito'] = "Ajuste de inventario procesado y guardado correctamente.";
            header("Location: ../pages/php/farmacia_inventario_listado.php");
            exit;
        } catch (Exception $e) {
            $conexion->rollback();
            $_SESSION['mensaje_user_error'] = "Error al procesar la baja: " . $e->getMessage();
            header("Location: ../pages/php/farmacia_inventario_listado.php");
            exit;
        }
    }

    /* =====================================================
       ========== 5. ANULACIÓN / REVERSIÓN DE MOVIMIENTOS ==
    ======================================================*/
    if ($op === 'revertir_movimiento') {
        $conexion->begin_transaction();
        try {
            // Recibimos el ID del movimiento que queremos anular desde el Historial
            $id_movimiento_original = intval($_POST['id_detalle_inventario']);
            $motivo_anulacion = $_POST['motivo_anulacion'] ?? 'Error de registro detectado por administrador';

            // 1. Obtener la información del movimiento original
            $sql_orig = "SELECT Id_TipoMovimiento, estado_movimiento, Id_prescripcion, observaciones 
                         FROM detalle_inventario WHERE Id_detalle_inventario = ?";
            $stmt_orig = $conexion->prepare($sql_orig);
            $stmt_orig->bind_param("i", $id_movimiento_original);
            $stmt_orig->execute();
            $mov_original = $stmt_orig->get_result()->fetch_assoc();

            if (!$mov_original) {
                throw new Exception("El movimiento no existe en la base de datos.");
            }
            if ($mov_original['estado_movimiento'] === 'Anulado') {
                throw new Exception("Este movimiento ya fue anulado previamente.");
            }

            $tipo_original = intval($mov_original['Id_TipoMovimiento']);
            $id_prescripcion_orig = $mov_original['Id_prescripcion']; // Puede ser null
            $observaciones_orig = strtolower($mov_original['observaciones']);

            // Detectar si fue despacho externo leyendo las observaciones que tu mismo sistema guarda
            $es_externo = (strpos($observaciones_orig, 'externo') !== false);

            $nuevo_tipo_mov = ($tipo_original == 1) ? 8 : 9; // 8: Reversión de Entrada, 9: Reversión de Salida
            $obs_reversion = "ANULACIÓN DE MOV. #" . $id_movimiento_original . " | Motivo: " . $motivo_anulacion;

            // 2. Insertar la Cabecera del Movimiento de Reversión
            $sql_cab = "INSERT INTO detalle_inventario (Id_TipoMovimiento, Id_Persona, fecha, observaciones, estado_movimiento) 
                        VALUES (?, ?, NOW(), ?, 'Activo')";
            $stmt_cab = $conexion->prepare($sql_cab);
            $stmt_cab->bind_param("iis", $nuevo_tipo_mov, $id_usuario, $obs_reversion);
            $stmt_cab->execute();
            $id_mov_reversion = $conexion->insert_id;

            // 3. Buscar todos los medicamentos involucrados en ese movimiento original
            $sql_det = "SELECT Id_descripcion_medicamento, Id_lote, cantidad 
                        FROM medicamentos_detalle_inventario WHERE Id_detalle_inventario = ?";
            $stmt_det = $conexion->prepare($sql_det);
            $stmt_det->bind_param("i", $id_movimiento_original);
            $stmt_det->execute();
            $detalles_originales = $stmt_det->get_result();

            // 4. Recorrer los medicamentos y revertir el stock y los estados
            while ($item = $detalles_originales->fetch_assoc()) {
                $id_desc = $item['Id_descripcion_medicamento'];
                $id_lote = $item['Id_lote'];
                $cant_revertir = $item['cantidad'];

                // Consultar stock actual de ese lote
                $sql_stock = "SELECT cantidad_actual FROM existencias_stock WHERE Id_lote = ? AND Id_descripcion_medicamento = ?";
                $stmt_stock = $conexion->prepare($sql_stock);
                $stmt_stock->bind_param("ii", $id_lote, $id_desc);
                $stmt_stock->execute();
                $stock_data = $stmt_stock->get_result()->fetch_assoc();
                $stock_actual = $stock_data ? $stock_data['cantidad_actual'] : 0;

                $nuevo_stock = 0;

                if ($tipo_original == 1) {
                    // ---> ES UNA ENTRADA: RESTAMOS STOCK <---
                    if ($stock_actual < $cant_revertir) {
                        throw new Exception("No se puede anular esta Entrada. El stock actual ($stock_actual) es menor a la cantidad original ($cant_revertir) del ID $id_desc. Esto significa que ya fueron despachados a pacientes.");
                    }
                    $nuevo_stock = $stock_actual - $cant_revertir;

                    // Si el lote se queda sin stock (0), lo inhabilitamos pasándolo a 'Retirado'
                    if ($nuevo_stock <= 0) {
                        $sql_lote_ret = "UPDATE lotes_medicamentos SET estado_lote = 'Retirado' WHERE Id = ?";
                        $stmt_lote_ret = $conexion->prepare($sql_lote_ret);
                        $stmt_lote_ret->bind_param("i", $id_lote);
                        $stmt_lote_ret->execute();
                    }
                } else {
                    // ---> ES UNA SALIDA/DESPACHO: SUMAMOS STOCK AL ALMACÉN <---
                    $nuevo_stock = $stock_actual + $cant_revertir;

                    // ---> REVERTIR ESTADO LÓGICO DE RECETAS Y SOLICITUDES <---
                    if ($es_externo) {
                        // REVERSIÓN EXTERNA: Buscar el detalle_solicitud que coincide con este medicamento
                        $filtro_ext = $id_prescripcion_orig ? "AND id_solicitud = $id_prescripcion_orig" : "ORDER BY id_detalle DESC LIMIT 1";
                        $sql_ds = "SELECT id_detalle, id_solicitud, cantidad_entregada 
                                   FROM detalle_solicitud 
                                   WHERE id_medicamento = ? AND cantidad_entregada > 0 $filtro_ext";
                        $stmt_ds = $conexion->prepare($sql_ds);
                        $stmt_ds->bind_param("i", $id_desc);
                        $stmt_ds->execute();
                        $res_ds = $stmt_ds->get_result()->fetch_assoc();

                        if ($res_ds) {
                            $estatus_actual = $res_ds['estatus_item'];
                            $nueva_cant_entregada = $res_ds['cantidad_entregada'] - $cant_revertir;
                            if ($nueva_cant_entregada < 0) $nueva_cant_entregada = 0;

                            if ($estatus_actual === 'Cancelado') {
                                $nuevo_estatus_item = 'Cancelado';
                                $nuevo_estatus_general = 'Cancelado';
                            } else {
                                $nuevo_estatus_item = ($nueva_cant_entregada <= 0) ? 'Pendiente' : 'Parcialmente Entregado';
                                $nuevo_estatus_general = ($nueva_cant_entregada <= 0) ? 'Pendiente' : 'Parcial';
                            }

                            // Actualizar el item específico
                            $sql_upd_ds = "UPDATE detalle_solicitud SET cantidad_entregada = ?, estatus_item = ? WHERE id_detalle = ?";
                            $stmt_upd_ds = $conexion->prepare($sql_upd_ds);
                            $stmt_upd_ds->bind_param("isi", $nueva_cant_entregada, $nuevo_estatus_item, $res_ds['id_detalle']);
                            $stmt_upd_ds->execute();

                            // Actualizar el estatus general de la solicitud padre
                            $estatus_general = ($nueva_cant_entregada <= 0) ? 'Pendiente' : 'Parcial';
                            $sql_upd_gen = "UPDATE solicitud_medicamento SET estatus_general = ? WHERE id_solicitud = ?";
                            $stmt_upd_gen = $conexion->prepare($sql_upd_gen);
                            $stmt_upd_gen->bind_param("si", $estatus_general, $res_ds['id_solicitud']);
                            $stmt_upd_gen->execute();
                        }
                    } else {
                        // REVERSIÓN INTERNA: Volver prescripcion_medicamentos a 'pendiente'
                        $filtro_int = $id_prescripcion_orig ? "AND Id_consulta = $id_prescripcion_orig" : "";
                        $sql_pi = "UPDATE prescripcion_medicamentos 
                                   SET estado_prescripcion = 'pendiente' 
                                   WHERE Id_descripcion_medicamento = ? AND estado_prescripcion NOT IN ('pendiente', 'cancelado') $filtro_int 
                                   ORDER BY Id DESC LIMIT 1";
                        $stmt_pi = $conexion->prepare($sql_pi);
                        $stmt_pi->bind_param("i", $id_desc);
                        $stmt_pi->execute();
                    }
                }

                // 5. Actualizar la tabla de existencias
                $sql_upd = "UPDATE existencias_stock SET cantidad_actual = ? WHERE Id_lote = ? AND Id_descripcion_medicamento = ?";
                $stmt_upd = $conexion->prepare($sql_upd);
                $stmt_upd->bind_param("iii", $nuevo_stock, $id_lote, $id_desc);
                $stmt_upd->execute();

                // 6. Registrar en detalle que se hizo este contra-movimiento
                $sql_ins_det = "INSERT INTO medicamentos_detalle_inventario (Id_detalle_inventario, Id_descripcion_medicamento, Id_lote, cantidad, stock_momento) 
                                VALUES (?, ?, ?, ?, ?)";
                $stmt_ins_det = $conexion->prepare($sql_ins_det);
                $stmt_ins_det->bind_param("iiiii", $id_mov_reversion, $id_desc, $id_lote, $cant_revertir, $nuevo_stock);
                $stmt_ins_det->execute();
            }

            // 7. Marcar el movimiento original como 'Anulado' para que no se duplique la acción
            $sql_anular = "UPDATE detalle_inventario SET estado_movimiento = 'Anulado' WHERE Id_detalle_inventario = ?";
            $stmt_anular = $conexion->prepare($sql_anular);
            $stmt_anular->bind_param("i", $id_movimiento_original);
            $stmt_anular->execute();

            $conexion->commit();
            $_SESSION['mensaje_user_exito'] = "Movimiento anulado exitosamente. El inventario y las recetas/lotes han sido restaurados.";
            header("Location: ../pages/php/farmacia_inventario_listado.php");
            exit;
        } catch (Exception $e) {
            $conexion->rollback();
            $_SESSION['mensaje_user_error'] = "Error al anular: " . $e->getMessage();
            header("Location: ../pages/php/farmacia_inventario_listado.php");
            exit;
        }
    }
}
?>