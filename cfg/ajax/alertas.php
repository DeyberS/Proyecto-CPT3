<?php
session_start();
include('../conexion.php');
date_default_timezone_set('America/Caracas');
$hoy = date('Y-m-d');
$proximo_vencimiento = date('Y-m-d', strtotime('+30 days')); // Margen de 30 días para "Próximo a vencer"
$ahora = date('H:i:s');
$hace20min = date('H:i:s', strtotime('-20 minutes'));

if (!isset($_SESSION["permisos"])) {
    exit(json_encode([]));
}

$id_usuario_actual = $_SESSION["id"];
$permisos = $_SESSION["permisos"];
$alertas = [];

// --- 1. ACTUALIZACIONES AUTOMÁTICAS (Mantenimiento) ---

// Citas
$conexion->query("UPDATE citas SET estado = 'Vencida' WHERE estado = 'Pendiente' AND (fecha_cita < '$hoy' OR (fecha_cita = '$hoy' AND hora_cita < '$hace20min'))");
$conexion->query("UPDATE citas SET estado = 'Inasistente' WHERE estado = 'Confirmada' AND (fecha_cita < '$hoy' OR (fecha_cita = '$hoy' AND hora_cita < '$hace20min'))");

// Lotes (Vencer lotes que llegaron a su fecha)
$conexion->query("UPDATE lotes_medicamentos SET estado_lote = 'Vencido' WHERE estado_lote = 'Disponible' AND (fecha_vencimiento < '$hoy')");


// --- 2. NOTIFICACIONES DE CITAS (Si tiene permiso) ---
if (in_array('Ver notificaciones de citas', $permisos)) {
    $filtro_medico = !in_array('Ver Citas', $permisos) ? " AND c.Id_medico = '$id_usuario_actual'" : "";
    
    $sqlCitas = "SELECT c.Id_cita, c.estado, c.hora_cita, p.nombre, p.apellido 
                 FROM citas c 
                 INNER JOIN persona p ON c.Id_paciente = p.id 
                 WHERE c.fecha_cita = '$hoy' AND c.estado IN ('Confirmada', 'Vencida', 'Inasistente', 'Reprogramada') AND c.estatus = 1 
                 $filtro_medico";

    $resCitas = $conexion->query($sqlCitas);
    while($row = $resCitas->fetch_assoc()) {
        $hora_cita_unix = strtotime($row['hora_cita']);
        $ahora_unix = strtotime($ahora);
        $dif = ($hora_cita_unix - $ahora_unix) / 60;

        $alertas[] = [
            'id' => $row['Id_cita'],
            'categoria' => 'cita',
            'titulo' => $row['nombre'] . " " . $row['apellido'],
            'estatus' => $row['estado'],
            'detalle' => "Hora: " . date('h:i A', strtotime($row['hora_cita'])),
            'proxima' => ($dif <= 30 && $dif > 0) ? true : false,
            'ruta' => 'citas_medicas_listado.php'
        ];
    }
}

// --- 3. NOTIFICACIONES DE INVENTARIO (Si tiene permiso) ---
if (in_array('Ver notificaciones de inventario', $permisos)) {
    
    // A. Stock Mínimo y Próximo
    $sqlStock = "SELECT m.nombre_medicamento, e.cantidad_actual, dm.stock_minimo, e.Id_descripcion_medicamento, dm.Id, dm.estatus, l.Id, l.estatus
                 FROM existencias_stock e 
                 JOIN descripcion_medicamento dm ON e.Id_descripcion_medicamento = dm.Id
                 JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
                 JOIN lotes_medicamentos l ON dm.Id = l.Id 
                 WHERE e.cantidad_actual <= (dm.stock_minimo * 1.2)"; // 20% por encima del mínimo ya es aviso
    
    $resStock = $conexion->query($sqlStock);
    while($s = $resStock->fetch_assoc()) {
        $tipoStock = ($s['cantidad_actual'] <= $s['stock_minimo']) ? 'critico' : 'bajo';
        $alertas[] = [
            'id' => 'stock_' . $s['nombre_medicamento'] .'_'.$s['cantidad_actual'],
            'categoria' => 'inventario_stock',
            'titulo' => "Stock " . ($tipoStock == 'critico' ? "Crítico" : "Bajo"),
            'estatus' => $tipoStock,
            'detalle' => $s['nombre_medicamento'] . ": " . $s['cantidad_actual'] . " unid. (Mín: " . $s['stock_minimo'] . ")",
            'ruta' => 'farmacia_inventario_listado.php'
        ];
    }

    // B. Lotes Vencidos y Próximos a Vencer
    $sqlLotes = "SELECT m.nombre_medicamento, l.fecha_vencimiento, l.Id, l.estado_lote, l.estatus
                 FROM lotes_medicamentos l 
                 JOIN descripcion_medicamento dm ON l.Id_descripcion_medicamento = dm.Id
                 JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento 
                 WHERE l.estado_lote = 'Vencido' AND l.estatus = 1
                 OR (l.fecha_vencimiento <= '$proximo_vencimiento' AND l.estado_lote = 'Disponible'  AND l.estatus = 1)";
    
    $resLotes = $conexion->query($sqlLotes);
    while($l = $resLotes->fetch_assoc()) {
        $esVencido = ($l['fecha_vencimiento'] < $hoy || $l['estado_lote'] == 'Vencido');
        $alertas[] = [
            'id' => 'lote_' . $l['Id'],
            'categoria' => 'inventario_lote',
            'titulo' => $esVencido ? "¡Se Ha Vencido Un Lote!" : "¡Un Lote Esta Por Vencer!",
            'estatus' => $esVencido ? 'vencido' : 'alerta',
            'detalle' => $l['nombre_medicamento'] . " (Vence: " . date('d/m/Y', strtotime($l['fecha_vencimiento'])) . ")",
            'ruta' => 'farmacia_lotes_listado.php'
        ];
    }
}

echo json_encode($alertas);


