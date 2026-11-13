<?php
ob_start(); 
session_start();
error_reporting(0); 
include('../conexion.php');

if (isset($_POST['id']) && isset($_POST['tipo'])) {
    
    $id = mysqli_real_escape_string($conexion, $_POST['id']);
    $tipo = mysqli_real_escape_string($conexion, $_POST['tipo']); 
    $id_usuario = isset($_SESSION['id']) ? $_SESSION['id'] : 0;
    $motivo = mysqli_real_escape_string($conexion, $_POST['motivo']);
    
    mysqli_begin_transaction($conexion);

    try {
        
        if ($tipo === 'Interna') {
            mysqli_query($conexion, "UPDATE prescripcion_medicamentos SET estado_prescripcion = 'cancelado' WHERE Id_consulta = '$id'");

            $res_sol = mysqli_query($conexion, "SELECT id_solicitud FROM solicitud_medicamento WHERE id_consulta = '$id' LIMIT 1");
            if ($fila_sol = mysqli_fetch_assoc($res_sol)) {
                $id_solicitud_vinculada = $fila_sol['id_solicitud'];
                mysqli_query($conexion, "UPDATE solicitud_medicamento SET estatus_general = 'Cancelado' WHERE id_solicitud = '$id_solicitud_vinculada'");
                mysqli_query($conexion, "UPDATE detalle_solicitud SET motivo = '$motivo', estatus_item = 'Cancelado' WHERE id_solicitud = '$id_solicitud_vinculada'");
            }

        } else if ($tipo === 'Externa') {
            mysqli_query($conexion, "UPDATE solicitud_medicamento SET estatus_general = 'Cancelado' WHERE id_solicitud = '$id'");
            mysqli_query($conexion, "UPDATE detalle_solicitud SET motivo = '$motivo', estatus_item = 'Cancelado' WHERE id_solicitud = '$id'");
        }

        mysqli_commit($conexion);
        
        ob_clean();
        echo "ok";

    } catch (Exception $e) {
        mysqli_rollback($conexion);
        ob_clean();
        echo "Error: " . $e->getMessage();
    }
}
?>