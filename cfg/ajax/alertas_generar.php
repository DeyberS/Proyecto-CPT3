<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

ini_set('display_errors', 0);
error_reporting(E_ALL);
set_time_limit(300);

include(__DIR__ . '/../conexion.php');

require __DIR__ . '/../../plugins/PHPMailer/src/Exception.php';
require __DIR__ . '/../../plugins/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../../plugins/PHPMailer/src/SMTP.php';

date_default_timezone_set('America/Santiago');
$hoy_str = date('Y-m-d');
$hace20min = date('H:i:s', strtotime('-20 minutes'));
$proximo_vencimiento = date('Y-m-d', strtotime('+30 days'));

if (!$conexion) {
    exit(json_encode(['status' => 'error', 'message' => 'Sin conexión a BD.']));
}

$conexion->query("
    UPDATE citas
    SET estado = 'Vencida'
    WHERE estado IN ('Pendiente', 'Confirmada')
      AND estatus = 1
      AND (
        fecha_cita < '$hoy_str'
        OR (fecha_cita = '$hoy_str' AND hora_cita < '$hace20min')
      )
");

$conexion->query("
    UPDATE lotes_medicamentos
    SET estado_lote = 'Vencido'
    WHERE fecha_vencimiento < '$hoy_str'
      AND estado_lote != 'Vencido'
      AND estatus = 1
");

$admins = [];
$resAdmins = $conexion->query("SELECT Id_persona FROM detalle_persona_rol WHERE Id_rol = 1 AND estatus IN (1, 2)");
if ($resAdmins) {
    while ($row = $resAdmins->fetch_assoc()) {
        $admins[] = (int)$row['Id_persona'];
    }
}

$personal_farmacia = [];
$resFarmacia = $conexion->query("SELECT Id_persona FROM detalle_persona_rol WHERE Id_rol IN (6, 9) AND estatus IN (1, 2)");
if ($resFarmacia) {
    while ($row = $resFarmacia->fetch_assoc()) {
        $personal_farmacia[] = (int)$row['Id_persona'];
    }
}

$usuarios_inventario = array_values(array_unique(array_merge($admins, $personal_farmacia)));
$roles_autorizados   = $usuarios_inventario; // para recetas

$stmtNotif = $conexion->prepare("
    INSERT IGNORE INTO notificaciones_usuarios
        (id_usuario, tipo, referencia_id, titulo, mensaje, ruta)
    VALUES (?, ?, ?, ?, ?, ?)
");

if (!$stmtNotif) {
    error_log('[alertas_generar] Falló prepare del INSERT: ' . $conexion->error);
    exit(json_encode(['status' => 'error', 'message' => 'prepare_failed']));
}

$contador_alertas = 0;

$sqlLotes = "
    SELECT l.Id, l.Lote, m.nombre_medicamento, l.estado_lote, l.fecha_vencimiento
    FROM lotes_medicamentos l
    JOIN descripcion_medicamento dm ON l.Id_descripcion_medicamento = dm.Id
    JOIN medicamento m              ON dm.Id_medicamento = m.Id_medicamento
    WHERE l.estatus = 1
      AND dm.estatus = '1'
      AND m.estatus = 1
      AND (
        l.estado_lote = 'Vencido'
        OR (l.fecha_vencimiento <= '$proximo_vencimiento'
            AND l.estado_lote IN ('Disponible', 'Cuarentena'))
      )
";

$resLotes = $conexion->query($sqlLotes);
if (!$resLotes) {
    error_log('[alertas_generar] SQL lotes: ' . $conexion->error);
} elseif ($resLotes->num_rows > 0) {
    while ($l = $resLotes->fetch_assoc()) {
        $estado_noti = ($l['estado_lote'] === 'Vencido') ? 'Vencido' : 'Proximo';
        $ref_id = 'lote_' . $l['Id'] . '_' . $estado_noti;
        $tipo   = 'inventario_lote';
        $ruta   = 'pages/php/farmacia_lotes_listado.php';

        if ($estado_noti === 'Vencido') {
            $titulo  = 'Lote Vencido Crítico';
            $mensaje = 'El lote ' . $l['Lote'] . ' de ' . $l['nombre_medicamento']
                     . ' venció el ' . $l['fecha_vencimiento'] . '. Retirar de estantes.';
        } else {
            $titulo  = 'Lote próximo a vencer';
            $mensaje = 'El lote ' . $l['Lote'] . ' de ' . $l['nombre_medicamento']
                     . ' vence pronto (' . $l['fecha_vencimiento'] . ').';
        }

        foreach ($usuarios_inventario as $id_user) {
            $stmtNotif->bind_param("isssss", $id_user, $tipo, $ref_id, $titulo, $mensaje, $ruta);
            if ($stmtNotif->execute() && $stmtNotif->affected_rows > 0) {
                $contador_alertas++;
            }
        }
    }
}

$sqlStock = "
    SELECT es.Id_existencia, m.nombre_medicamento, l.Lote,
           es.cantidad_actual, dm.stock_minimo
    FROM existencias_stock es
    JOIN descripcion_medicamento dm ON es.Id_descripcion_medicamento = dm.Id
    JOIN medicamento m              ON dm.Id_medicamento = m.Id_medicamento
    JOIN lotes_medicamentos l       ON es.Id_lote = l.Id
    WHERE es.cantidad_actual <= dm.stock_minimo
      AND l.estado_lote IN ('Disponible', 'Cuarentena')
      AND l.estatus = 1
      AND dm.estatus = '1'
      AND m.estatus = 1
";

$resStock = $conexion->query($sqlStock);
if (!$resStock) {
    error_log('[alertas_generar] SQL stock: ' . $conexion->error);
} elseif ($resStock->num_rows > 0) {
    while ($s = $resStock->fetch_assoc()) {
        $cantidad = (int)$s['cantidad_actual'];
        $estado_stock = ($cantidad <= 0) ? 'Agotado' : 'Critico';
        $ref_id = 'stock_' . $s['Id_existencia'] . '_' . $estado_stock;
        $tipo   = 'inventario_stock';
        $ruta   = 'pages/php/farmacia_inventario_listado.php';

        if ($estado_stock === 'Agotado') {
            $titulo  = 'Stock Agotado';
            $mensaje = '¡URGENTE! El medicamento ' . $s['nombre_medicamento']
                     . ' (Lote: ' . $s['Lote'] . ') se ha agotado.';
        } else {
            $titulo  = 'Stock Crítico';
            $mensaje = 'Stock bajo de ' . $s['nombre_medicamento']
                     . ' (Lote: ' . $s['Lote'] . '). Solo quedan ' . $cantidad . ' unidades.';
        }

        foreach ($usuarios_inventario as $id_user) {
            $stmtNotif->bind_param("isssss", $id_user, $tipo, $ref_id, $titulo, $mensaje, $ruta);
            if ($stmtNotif->execute() && $stmtNotif->affected_rows > 0) {
                $contador_alertas++;
            }
        }
    }
}

$sqlCitas = "
    SELECT c.id_cita, c.hora_cita,
           p.nombre AS p_nombre, p.apellido AS p_apellido,
           dm.Id_persona AS id_medico
    FROM citas c
    JOIN persona p       ON c.Id_paciente = p.id
    JOIN detalle_medico dm ON c.Id_medico = dm.Id_detalle_medico
    WHERE c.fecha_cita = '$hoy_str'
      AND c.estado = 'Confirmada'
      AND c.estatus = 1
";

$resCitas = $conexion->query($sqlCitas);
if (!$resCitas) {
    error_log('[alertas_generar] SQL citas hoy: ' . $conexion->error);
} elseif ($resCitas->num_rows > 0) {
    while ($c = $resCitas->fetch_assoc()) {
        $ref_id = 'cita_' . $c['id_cita'] . '_hoy';
        $tipo   = 'cita_medica';
        $titulo = 'Cita programada para hoy';
        $hora_formato = date('h:i A', strtotime($c['hora_cita']));
        $mensaje = 'Paciente: ' . $c['p_nombre'] . ' ' . $c['p_apellido']
                 . ' a las ' . $hora_formato;
        $ruta = 'pages/php/citas_medicas_listado.php';

        $usuarios_citas = array_values(array_unique(array_merge($admins, [(int)$c['id_medico']])));
        foreach ($usuarios_citas as $id_user) {
            $stmtNotif->bind_param("isssss", $id_user, $tipo, $ref_id, $titulo, $mensaje, $ruta);
            if ($stmtNotif->execute() && $stmtNotif->affected_rows > 0) {
                $contador_alertas++;
            }
        }
    }
}

$sqlCitasVencidas = "
    SELECT c.id_cita, c.hora_cita,
           p.nombre AS p_nombre, p.apellido AS p_apellido,
           dm.Id_persona AS id_medico
    FROM citas c
    JOIN persona p       ON c.Id_paciente = p.id
    JOIN detalle_medico dm ON c.Id_medico = dm.Id_detalle_medico
    WHERE c.estado = 'Vencida'
      AND c.estatus = 1
";

$resCitasVencidas = $conexion->query($sqlCitasVencidas);
if (!$resCitasVencidas) {
    error_log('[alertas_generar] SQL citas vencidas: ' . $conexion->error);
} elseif ($resCitasVencidas->num_rows > 0) {
    while ($c = $resCitasVencidas->fetch_assoc()) {
        $ref_id = 'cita_' . $c['id_cita'] . '_vencida';
        $tipo   = 'cita_medica';
        $titulo = 'Cita Vencida';
        $hora_formato = date('h:i A', strtotime($c['hora_cita']));
        $mensaje = 'Paciente: ' . $c['p_nombre'] . ' ' . $c['p_apellido']
                 . ' a las ' . $hora_formato;
        $ruta = 'pages/php/citas_medicas_listado.php';

        $usuarios_citas = array_values(array_unique(array_merge($admins, [(int)$c['id_medico']])));
        foreach ($usuarios_citas as $id_user) {
            $stmtNotif->bind_param("isssss", $id_user, $tipo, $ref_id, $titulo, $mensaje, $ruta);
            if ($stmtNotif->execute() && $stmtNotif->affected_rows > 0) {
                $contador_alertas++;
            }
        }
    }
}

$sqlRecetasPendientes = "
    SELECT
        ds.id_detalle AS id_item_receta,
        ds.id_solicitud AS id_prescripcion,
        m.nombre_medicamento,
        CONCAT(p.nombre, ' ', p.apellido) AS paciente,
        p.cedula AS cedula_paciente,
        p.email  AS email_paciente,
        SUM(es.cantidad_actual) AS stock_total,
        (ds.cantidad_recetada - ds.cantidad_entregada) AS cantidad_faltante
    FROM detalle_solicitud ds
    JOIN solicitud_medicamento sm   ON ds.id_solicitud = sm.id_solicitud
    JOIN persona p                  ON sm.id_paciente = p.id
    JOIN descripcion_medicamento dm ON ds.id_descripcion_medicamento = dm.Id
    JOIN medicamento m              ON dm.Id_medicamento = m.Id_medicamento
    LEFT JOIN existencias_stock es  ON dm.Id = es.Id_descripcion_medicamento
    WHERE ds.estatus_item IN ('Pendiente', 'Parcialmente Entregado')
      AND ds.paciente_notificado = 0
      AND dm.estatus = '1'
      AND m.estatus = 1
    GROUP BY
        ds.id_detalle, ds.id_solicitud, dm.Id,
        m.nombre_medicamento, p.nombre, p.apellido,
        p.email, p.cedula, p.id, cantidad_faltante
    HAVING stock_total >= cantidad_faltante
       AND cantidad_faltante > 0
";

$resRecetas = $conexion->query($sqlRecetasPendientes);

if (!$resRecetas) {
    error_log('[alertas_generar] SQL recetas: ' . $conexion->error);
} elseif ($resRecetas->num_rows > 0) {
    while ($r = $resRecetas->fetch_assoc()) {
        $nombre_med      = $r['nombre_medicamento'];
        $paciente_nombre = $r['paciente'];
        $email_paciente  = $r['email_paciente'];
        $id_item         = (int)$r['id_item_receta'];

        $ref_id       = 'disp_' . $id_item;
        $tipo_noti    = 'receta_disponible';
        $titulo       = 'Medicina Disponible para Despachar';
        $mensaje      = 'Ya hay stock de ' . $nombre_med
                      . ' para la receta pendiente de ' . $paciente_nombre . '.';
        $ruta         = 'pages/php/farmacia_prescripciones_listado.php?buscar='
                      . urlencode($paciente_nombre);

        /* Notificación en el sistema: 1 por cada usuario autorizado */
        $insertadas = 0;
        foreach ($roles_autorizados as $id_user) {
            $stmtNotif->bind_param("isssss", $id_user, $tipo_noti, $ref_id, $titulo, $mensaje, $ruta);
            $stmtNotif->execute();
            if ($stmtNotif->affected_rows > 0) $insertadas++;
        }
        if ($insertadas > 0) $contador_alertas++;

        /* Correo al paciente */
        if (!empty($email_paciente) && filter_var($email_paciente, FILTER_VALIDATE_EMAIL)) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'cpt3sistema@gmail.com';
                $mail->Password   = 'Jrjjfgomexsyyxqg'; // ⚠️ mover a variable de entorno
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                $mail->setFrom('cpt3sistema@gmail.com', 'Farmacia CPT3');
                $mail->addAddress($email_paciente, $paciente_nombre);
                $mail->isHTML(true);
                $mail->Subject = 'Medicamento Disponible - Sistema CPT3';
                $mail->Body    = '<h3>Hola, ' . $paciente_nombre . '</h3>'
                    . '<p>Te informamos que el medicamento <strong>' . $nombre_med . '</strong> '
                    . 'que tenías pendiente ya se encuentra disponible en nuestro inventario.</p>'
                    . '<p>Por favor, acércate a la farmacia lo más pronto posible para su retiro.</p>';
                $mail->send();
            } catch (Exception $e) {
                error_log('[alertas_generar] Fallo correo a ' . $email_paciente
                        . ': ' . $mail->ErrorInfo);
            }
        }

        /* Marcar como notificado para no repetir */
        $conexion->query("
            UPDATE detalle_solicitud
            SET paciente_notificado = 1
            WHERE id_detalle = $id_item
        ");
    }
}

/* ---------------------------------------------------------------
   Cierre
   --------------------------------------------------------------- */
$stmtNotif->close();

header('Content-Type: application/json');
echo json_encode(['status' => 'success', 'nuevas' => $contador_alertas]);