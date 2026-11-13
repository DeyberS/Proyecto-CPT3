<?php
require_once '../conexion.php'; 
header('Content-Type: application/json');

if (isset($_POST['config'])) {
    $configuracion = json_decode($_POST['config'], true);

    if (is_array($configuracion)) {
        mysqli_begin_transaction($conexion);

        try {
            $sql = "INSERT INTO configuracion_horarios (dia_semana, hora_inicio, hora_fin, estatus) 
                    VALUES (?, ?, ?, ?) 
                    ON DUPLICATE KEY UPDATE 
                    hora_inicio = VALUES(hora_inicio), 
                    hora_fin = VALUES(hora_fin), 
                    estatus = VALUES(estatus)";
            
            $stmt = mysqli_prepare($conexion, $sql);

            foreach ($configuracion as $item) {
                $dia = $item['dia'];
                
                if ($item['no_laborable'] == 1) {
                    $hora_inicio = '00:00:00';
                    $hora_fin = '00:00:00';
                    $estatus = 0;
                } else {
                    $hora_inicio = $item['hora_inicio'];
                    $hora_fin = $item['hora_fin'];
                    $estatus = 1;
                }

                mysqli_stmt_bind_param($stmt, "issi", $dia, $hora_inicio, $hora_fin, $estatus);
                mysqli_stmt_execute($stmt);
            }

            mysqli_commit($conexion);
            echo json_encode(['status' => 'ok', 'mensaje' => 'Horarios y días configurados correctamente.']);

        } catch (Exception $e) {
            mysqli_rollback($conexion);
            echo json_encode(['status' => 'error', 'mensaje' => 'Error al guardar: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'mensaje' => 'Formato de datos no válido.']);
    }
} else {
    echo json_encode(['status' => 'error', 'mensaje' => 'No se recibieron los datos de configuración.']);
}
?>