<?php
session_start();
include("../conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['Id']);
    $nombre = trim($_POST['nombre_lote']);
    $fecha_fabricacion = trim($_POST['fecha_fabricacion']);
    $fecha_vencimiento = trim($_POST['fecha_vencimiento']);
    $proveedor = intval($_POST['proveedor']);
    $estado_lote = trim($_POST['estado_lote']);
    
    // Recibimos el JSON con los medicamentos asociados
    $medicamentos_json = isset($_POST['medicamentos_data']) ? $_POST['medicamentos_data'] : '[]';
    $medicamentos_array = json_decode($medicamentos_json, true);

    $conexion->begin_transaction();

    try {
        // 1. Validaciones básicas
        if (empty($id) || empty($nombre)) {
            throw new Exception("Los datos base del lote están incompletos.");
        }
        if (!is_array($medicamentos_array) || count($medicamentos_array) === 0) {
            throw new Exception("El lote debe tener al menos un medicamento asociado.");
        }

        // 2. Verificar que el lote exista
        $check_stmt = $conexion->prepare("SELECT Id FROM lotes_medicamentos WHERE Id = ? LIMIT 1");
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $check_stmt->store_result();
        if ($check_stmt->num_rows === 0) {
            throw new Exception("El lote que intenta editar no existe.");
        }
        $check_stmt->close();

        // 3. Verificar duplicidad del nombre del lote (excluyendo el actual)
        $dup_stmt = $conexion->prepare("SELECT Id FROM lotes_medicamentos WHERE LOWER(Lote) = LOWER(?) AND Id != ? LIMIT 1");
        $dup_stmt->bind_param("si", $nombre, $id);
        $dup_stmt->execute();
        $dup_stmt->store_result();
        if ($dup_stmt->num_rows > 0) {
            throw new Exception("Ya existe otro lote con el nombre '<b>" . htmlspecialchars($nombre) . "</b>'. Por favor, use un nombre diferente.");
        }
        $dup_stmt->close();

        // 4. Obtener el primer medicamento del array (para el UPDATE principal)
        // Nota: En la estructura de la BD, cada fila de lotes_medicamentos apunta a UN medicamento.
        // Como ahora el lote puede tener varios medicamentos, usaremos el primero como "base" para el UPDATE
        // y luego nos encargaremos de los demás en la tabla de relación.
        $primer_medicamento_id = intval($medicamentos_array[0]['id']);

        // 5. Actualizar los datos base del lote en la primera fila (o en la fila que ya exista)
        $update_stmt = $conexion->prepare("
            UPDATE lotes_medicamentos 
            SET Id_descripcion_medicamento = ?, 
                Id_proveedor = ?, 
                Lote = ?, 
                fecha_fabricacion = ?, 
                fecha_vencimiento = ?, 
                estado_lote = ? 
            WHERE Id = ?
        ");
        $update_stmt->bind_param("iissssi", $primer_medicamento_id, $proveedor, $nombre, $fecha_fabricacion, $fecha_vencimiento, $estado_lote, $id);
        
        if (!$update_stmt->execute()) {
            throw new Exception("No se pudo actualizar la información base del lote.");
        }
        $update_stmt->close();

        // 6. Sincronizar los medicamentos asociados
        // Primero, obtener los medicamentos actuales asociados a este lote
        $meds_actuales = [];
        $res_actuales = $conexion->query("SELECT Id_descripcion_medicamento FROM lotes_medicamentos WHERE Id = $id");
        while ($row_act = $res_actuales->fetch_assoc()) {
            $meds_actuales[] = intval($row_act['Id_descripcion_medicamento']);
        }

        // Obtener los IDs nuevos del array
        $meds_nuevos = array_map(function($m) { return intval($m['id']); }, $medicamentos_array);

        // Caso A: Medicamentos que ya existían y siguen estando (no hacer nada o actualizar si fuera necesario)
        // Caso B: Medicamentos nuevos que no estaban (INSERTAR)
        // Caso C: Medicamentos que estaban y ya no están (ELIMINAR) - Solo si su stock es 0

        // Insertar los nuevos que no estén ya
        foreach ($meds_nuevos as $med_id) {
            if (!in_array($med_id, $meds_actuales)) {
                $insert_stmt = $conexion->prepare("
                    INSERT INTO lotes_medicamentos (Id_descripcion_medicamento, Id_proveedor, Lote, fecha_fabricacion, fecha_vencimiento, estado_lote, estatus) 
                    VALUES (?, ?, ?, ?, ?, ?, 1)
                ");
                $insert_stmt->bind_param("iissss", $med_id, $proveedor, $nombre, $fecha_fabricacion, $fecha_vencimiento, $estado_lote);
                if (!$insert_stmt->execute()) {
                    throw new Exception("No se pudo asociar el nuevo medicamento al lote.");
                }
                $insert_stmt->close();
            }
        }

        // Eliminar los que ya no están (solo si su stock es 0)
        foreach ($meds_actuales as $med_id) {
            if (!in_array($med_id, $meds_nuevos)) {
                // Verificar stock
                $stock_stmt = $conexion->prepare("SELECT SUM(cantidad_actual) AS total FROM existencias_stock WHERE Id_lote = ? AND Id_descripcion_medicamento = ?");
                $stock_stmt->bind_param("ii", $id, $med_id);
                $stock_stmt->execute();
                $stock_res = $stock_stmt->get_result()->fetch_assoc();
                $stock_total = intval($stock_res['total'] ?? 0);
                $stock_stmt->close();

                if ($stock_total > 0) {
                    throw new Exception("No se puede eliminar el medicamento con ID $med_id del lote porque aún tiene stock ($stock_total unidades). Ajuste el stock a 0 primero.");
                }

                // Eliminar la relación
                $del_stmt = $conexion->prepare("DELETE FROM lotes_medicamentos WHERE Id = ? AND Id_descripcion_medicamento = ?");
                $del_stmt->bind_param("ii", $id, $med_id);
                if (!$del_stmt->execute()) {
                    throw new Exception("No se pudo eliminar el medicamento del lote.");
                }
                $del_stmt->close();
            }
        }

        $conexion->commit();
        $_SESSION['mensaje_user_exito'] = '✅ Éxito: El lote y sus medicamentos asociados han sido actualizados correctamente.';

    } catch (Exception $e) {
        $conexion->rollback();
        $_SESSION['mensaje_user_error'] = '⚠️ Error de Actualización: ' . $e->getMessage();
    }

    header("Location: ../../pages/php/farmacia_lotes_listado.php");
    exit();
} else {
    // Si no es POST, redirigir
    header("Location: ../../pages/php/farmacia_lotes_listado.php");
    exit();
}
?>