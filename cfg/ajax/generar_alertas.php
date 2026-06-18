<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Evitar salidas accidentales que rompan el JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

include(__DIR__ . '/../conexion.php');

// Ajusta las rutas de PHPMailer si es necesario
require __DIR__ . '/../../plugins/PHPMailer/src/Exception.php';
require __DIR__ . '/../../plugins/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../../plugins/PHPMailer/src/SMTP.php';

date_default_timezone_set('America/Caracas');
$hoy_str = date('Y-m-d');
$hace20min = date('H:i:s', strtotime('-20 minutes'));
$proximo_vencimiento = date('Y-m-d', strtotime('+30 days'));

if (!$conexion) {
    exit(json_encode(['status' => 'error', 'message' => 'Sin conexión a BD.']));
}

// 1. ACTUALIZAR ESTADOS (Citas y Lotes)
$conexion->query("UPDATE citas SET estado = 'Vencida' WHERE estado IN ('Pendiente', 'Confirmada') AND (fecha_cita < '$hoy_str' OR (fecha_cita = '$hoy_str' AND hora_cita < '$hace20min'))");
$conexion->query("UPDATE lotes_medicamentos SET estado_lote = 'Vencido' WHERE fecha_vencimiento < '$hoy_str' AND estado_lote != 'Vencido'");

// 2. OBTENER USUARIOS POR ROL
$admins = [];
$resAdmins = $conexion->query("SELECT Id_persona FROM detalle_persona_rol WHERE Id_rol = 1 AND estatus IN (1, 2)");
if ($resAdmins) { while($row = $resAdmins->fetch_assoc()) { $admins[] = $row['Id_persona']; } }

$personal_farmacia = [];
$resFarmacia = $conexion->query("SELECT Id_persona FROM detalle_persona_rol WHERE Id_rol IN (6, 9) AND estatus IN (1, 2)");
if ($resFarmacia) { while($row = $resFarmacia->fetch_assoc()) { $personal_farmacia[] = $row['Id_persona']; } }

$usuarios_inventario = array_unique(array_merge($admins, $personal_farmacia));

// Preparar el Insert (El IGNORE evita duplicados gracias a tu llave única idx_unica_notificacion)
$stmtNotif = $conexion->prepare("INSERT IGNORE INTO notificaciones_usuarios (id_usuario, tipo, referencia_id, titulo, mensaje, ruta) VALUES (?, ?, ?, ?, ?, ?)");

$contador_alertas = 0;

// 3. INVENTARIO (Lotes por vencer / vencidos)
$sqlLotes = "SELECT l.Id, l.Lote, m.nombre_medicamento, l.estado_lote, l.fecha_vencimiento 
             FROM lotes_medicamentos l 
             JOIN descripcion_medicamento dm ON l.Id_descripcion_medicamento = dm.Id
             JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento 
             WHERE l.estado_lote = 'Vencido' OR (l.fecha_vencimiento <= '$proximo_vencimiento' AND l.estado_lote != 'Vencido')";

$resLotes = $conexion->query($sqlLotes);
if ($resLotes && $resLotes->num_rows > 0) {
    while($l = $resLotes->fetch_assoc()) {
        $estado_noti = ($l['estado_lote'] == 'Vencido') ? 'Vencido' : 'Proximo';
        $ref_id = 'lote_' . $l['Id'] . '_' . $estado_noti; 
        $tipo = 'inventario_lote';
        $ruta = "pages/php/farmacia_lotes_listado.php";

        if ($estado_noti == 'Vencido') {
            $titulo = "Lote Vencido Crítico";
            $mensaje = "El lote " . $l['Lote'] . " de " . $l['nombre_medicamento'] . " venció el " . $l['fecha_vencimiento'] . ". Retirar de estantes.";
        } else {
            $titulo = "Lote próximo a vencer";
            $mensaje = "El lote " . $l['Lote'] . " de " . $l['nombre_medicamento'] . " vence pronto (" . $l['fecha_vencimiento'] . ").";
        }

        foreach($usuarios_inventario as $id_user) {
            $stmtNotif->bind_param("isssss", $id_user, $tipo, $ref_id, $titulo, $mensaje, $ruta);
            if ($stmtNotif->execute() && $stmtNotif->affected_rows > 0) $contador_alertas++;
        }
    }
}

// 4. INVENTARIO (Stock Crítico)
$sqlStock = "SELECT es.Id_existencia, m.nombre_medicamento, l.Lote, es.cantidad_actual, dm.stock_minimo 
             FROM existencias_stock es
             JOIN descripcion_medicamento dm ON es.Id_descripcion_medicamento = dm.Id
             JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
             JOIN lotes_medicamentos l ON es.Id_lote = l.Id
             WHERE es.cantidad_actual <= dm.stock_minimo 
             AND l.estado_lote != 'Vencido'";

$resStock = $conexion->query($sqlStock);
if ($resStock && $resStock->num_rows > 0) {
    while($s = $resStock->fetch_assoc()) {
        $cantidad = (int)$s['cantidad_actual'];
        $estado_stock = ($cantidad <= 0) ? 'Agotado' : 'Critico';
        $ref_id = 'stock_' . $s['Id_existencia'] . '_' . $estado_stock; 
        $tipo = 'inventario_stock';
        $ruta = "pages/php/farmacia_inventario_listado.php";
        
        if ($estado_stock == 'Agotado') {
            $titulo = "Stock Agotado";
            $mensaje = "¡URGENTE! El medicamento " . $s['nombre_medicamento'] . " (Lote: " . $s['Lote'] . ") se ha agotado.";
        } else {
            $titulo = "Stock Crítico";
            $mensaje = "Stock bajo de " . $s['nombre_medicamento'] . " (Lote: " . $s['Lote'] . "). Solo quedan " . $cantidad . " unidades.";
        }

        foreach($usuarios_inventario as $id_user) {
            $stmtNotif->bind_param("isssss", $id_user, $tipo, $ref_id, $titulo, $mensaje, $ruta);
            if ($stmtNotif->execute() && $stmtNotif->affected_rows > 0) $contador_alertas++;
        }
    }
}

// 5. CITAS DE HOY
$sqlCitas = "SELECT c.id_cita, c.hora_cita, p.nombre AS p_nombre, p.apellido AS p_apellido, dm.Id_persona AS id_medico
             FROM citas c
             JOIN persona p ON c.Id_paciente = p.id
             JOIN detalle_medico dm ON c.Id_medico = dm.Id_detalle_medico
             WHERE c.fecha_cita = '$hoy_str' AND c.estado = 'Confirmada'";

$resCitas = $conexion->query($sqlCitas);
if ($resCitas && $resCitas->num_rows > 0) {
    while($c = $resCitas->fetch_assoc()) {
        $ref_id = 'cita_' . $c['id_cita'] . '_hoy';
        $tipo = 'cita_medica';
        $titulo = "Cita programada para hoy";
        $hora_formato = date("h:i A", strtotime($c['hora_cita']));
        $mensaje = "Paciente: " . $c['p_nombre'] . " " . $c['p_apellido'] . " a las " . $hora_formato;
        $ruta = "pages/php/citas_medicas_listado.php";
        
        $usuarios_citas = array_unique(array_merge($admins, [$c['id_medico']]));
        foreach($usuarios_citas as $id_user) {
            $stmtNotif->bind_param("isssss", $id_user, $tipo, $ref_id, $titulo, $mensaje, $ruta);
            if ($stmtNotif->execute() && $stmtNotif->affected_rows > 0) $contador_alertas++;
        }
    }
}

// 5. CITAS DE HOY
$sqlCitasVencidas = "SELECT c.id_cita, c.hora_cita, p.nombre AS p_nombre, p.apellido AS p_apellido, dm.Id_persona AS id_medico
             FROM citas c
             JOIN persona p ON c.Id_paciente = p.id
             JOIN detalle_medico dm ON c.Id_medico = dm.Id_detalle_medico
             WHERE c.estado = 'Vencida'";

$resCitasVencidas = $conexion->query($sqlCitasVencidas);
if ($resCitasVencidas && $resCitasVencidas->num_rows > 0) {
    while($c = $resCitasVencidas->fetch_assoc()) {
        $ref_id = 'cita_' . $c['id_cita'] . '_vencida';
        $tipo = 'cita_medica';
        $titulo = "Cita Vencida";
        $hora_formato = date("h:i A", strtotime($c['hora_cita']));
        $mensaje = "Paciente: " . $c['p_nombre'] . " " . $c['p_apellido'] . " a las " . $hora_formato;
        $ruta = "pages/php/citas_medicas_listado.php";
        
        $usuarios_citas = array_unique(array_merge($admins, [$c['id_medico']]));
        foreach($usuarios_citas as $id_user) {
            $stmtNotif->bind_param("isssss", $id_user, $tipo, $ref_id, $titulo, $mensaje, $ruta);
            if ($stmtNotif->execute() && $stmtNotif->affected_rows > 0) $contador_alertas++;
        }
    }
}

// 6. RECETAS PENDIENTES CON STOCK
$roles_autorizados = array_unique(array_merge($admins, $personal_farmacia));

$sqlRecetasPendientes = "
    SELECT 
        'Interna' as tipo, pm.Id as id_item_receta, pm.Id_consulta as id_prescripcion, m.nombre_medicamento, 
        CONCAT(p.nombre, ' ', p.apellido) as paciente, p.cedula as cedula_paciente, p.email as email_paciente,
        SUM(es.cantidad_actual) as stock_total, 0 as cantidad_faltante
    FROM prescripcion_medicamentos pm
    JOIN consulta c ON pm.Id_consulta = c.Id_consulta
    JOIN persona p ON c.Id_paciente = p.id
    JOIN descripcion_medicamento dm ON pm.Id_descripcion_medicamento = dm.Id
    JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
    LEFT JOIN existencias_stock es ON dm.Id = es.Id_descripcion_medicamento
    WHERE LOWER(pm.estado_prescripcion) IN ('pendiente', 'parcial') AND pm.estatus = 1 AND pm.paciente_notificado = 0
    GROUP BY pm.Id, pm.Id_consulta, dm.Id, m.nombre_medicamento, p.nombre, p.apellido, p.email, p.id
    HAVING stock_total > 0
    UNION ALL
    SELECT 
        'Externa' as tipo, ds.id_detalle as id_item_receta, ds.id_solicitud as id_prescripcion, m.nombre_medicamento,
        CONCAT(p.nombre, ' ', p.apellido) as paciente, p.cedula as cedula_paciente, p.email as email_paciente,
        SUM(es.cantidad_actual) as stock_total, (ds.cantidad_recetada - ds.cantidad_entregada) as cantidad_faltante
    FROM detalle_solicitud ds
    JOIN solicitud_medicamento sm ON ds.id_solicitud = sm.id_solicitud
    JOIN persona p ON sm.id_paciente = p.id
    JOIN descripcion_medicamento dm ON ds.id_medicamento = dm.Id
    JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
    LEFT JOIN existencias_stock es ON dm.Id = es.Id_descripcion_medicamento
    WHERE ds.estatus_item IN ('Pendiente', 'Parcialmente Entregado') AND ds.paciente_notificado = 0
    GROUP BY ds.id_detalle, ds.id_solicitud, dm.Id, m.nombre_medicamento, p.nombre, p.apellido, p.email, p.id, cantidad_faltante
    HAVING stock_total >= cantidad_faltante AND cantidad_faltante > 0
";

$resRecetas = $conexion->query($sqlRecetasPendientes);

if ($resRecetas && $resRecetas->num_rows > 0) {
    while($r = $resRecetas->fetch_assoc()) {
        $nombre_med = $r['nombre_medicamento'];
        $paciente_nombre = $r['paciente'];
        $email_paciente = $r['email_paciente'];
        $cedula_paciente = $r['cedula_paciente'];
        
        $ref_id = 'disp_' . $r['tipo'] . '_' . $r['id_item_receta']; 
        $tipo_noti = 'receta_disponible';
        $titulo = "Medicina Disponible para Despachar";
        $mensaje = "Ya hay stock de " . $nombre_med . " para la receta pendiente de " . $paciente_nombre . ".";
        $ruta = "pages/php/farmacia_prescripciones_listado.php?buscar=" . urlencode($paciente_nombre);
        
        foreach($roles_autorizados as $id_user) {
            $stmtNotif->bind_param("isssss", $id_user, $tipo_noti, $ref_id, $titulo, $mensaje, $ruta);
            $stmtNotif->execute();
        }
        
        // Enviar Correo
        if (!empty($email_paciente) && filter_var($email_paciente, FILTER_VALIDATE_EMAIL)) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com'; 
                $mail->SMTPAuth   = true;
                $mail->Username   = 'cpt3sistema@gmail.com'; 
                $mail->Password   = 'Jrjjfgomexsyyxqg'; 
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                $mail->setFrom('cpt3sistema@gmail.com', 'Farmacia CPT3');
                $mail->addAddress($email_paciente, $paciente_nombre);
                $mail->isHTML(true);
                $mail->Subject = 'Medicamento Disponible - Sistema CPT3';
                $mail->Body    = "<h3>Hola, $paciente_nombre</h3><p>Te informamos que el medicamento <strong>$nombre_med</strong> que tenías pendiente ya se encuentra disponible en nuestro inventario.</p><p>Por favor, acércate a la farmacia lo más pronto posible para su retiro.</p>";
                $mail->send();
            } catch (Exception $e) {
                // Esto guardará el motivo exacto del fallo en el log del servidor
                error_log("Fallo al enviar correo a $email_paciente. Error de PHPMailer: " . $mail->ErrorInfo);
            }
        }

        // Marcar como notificado en BD
        $id_item = (int)$r['id_item_receta'];
        if ($r['tipo'] == 'Interna') {
            $conexion->query("UPDATE prescripcion_medicamentos SET paciente_notificado = 1 WHERE Id = $id_item");
        } else {
            $conexion->query("UPDATE detalle_solicitud SET paciente_notificado = 1 WHERE id_detalle = $id_item");
        }
        $contador_alertas++;
    }
}

echo json_encode(['status' => 'success', 'nuevas' => $contador_alertas]);
?>