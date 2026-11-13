<?php
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dia_semana'])) {
    $dia = $_POST['dia_semana'];

    $sql = "SELECT dia_semana, hora_inicio, hora_fin, estatus FROM configuracion_horarios WHERE dia_semana = ? LIMIT 1";
    
    if ($stmt = mysqli_prepare($conexion, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $dia);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        $horario = mysqli_fetch_assoc($resultado);
        
        if ($horario) {
            echo json_encode([
                'existe'      => true,
                'hora_inicio' => $horario['hora_inicio'],
                'hora_fin'    => $horario['hora_fin'],
                'estatus'     => (string)$horario['estatus'] 
            ]);
        } else {
            echo json_encode(['existe' => false]);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(['existe' => false, 'error' => mysqli_error($conexion)]);
    }
} 
else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT dia_semana, hora_inicio, hora_fin, estatus FROM configuracion_horarios ORDER BY dia_semana ASC";
    $resultado = mysqli_query($conexion, $sql);
    
    $data = [];
    if ($resultado) {
        while($row = mysqli_fetch_assoc($resultado)) {
            $data[] = $row;
        }
        echo json_encode(['status' => 'success', 'data' => $data]);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($conexion)]);
    }
} 
else {
    echo json_encode(['existe' => false, 'error' => 'Método no soportado']);
}
?>