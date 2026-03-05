<?php
// actualizar_cita.php
session_start();
include('../conexion.php');

// Verificamos que se reciban los datos mínimos necesarios
if (isset($_POST['id_cita']) && isset($_POST['id_medico']) && isset($_POST['id_especialidad'])) {
    
    // 1. Recibir y sanear datos
    $id_cita         = intval($_POST['id_cita']);
    $id_medico       = intval($_POST['id_medico']);
    $id_especialidad = intval($_POST['id_especialidad']);
    $fecha_cita      = $_POST['fecha_cita'];
    $hora_cita       = $_POST['hora_cita'];
    $motivo          = trim($_POST['motivo']);
    $estado         = intval($_POST['estado']);

    // Iniciar transacción
    $conexion->begin_transaction();

    try {
        // 2. Preparar la sentencia SQL de actualización
        $sql = "UPDATE citas SET 
                    fecha_cita = ?, 
                    hora_cita = ?, 
                    motivo = ?, 
                    estado = ?, 
                    Id_medico = ?, 
                    Id_especialidad = ? 
                WHERE Id_cita = ?";
        
        $stmt = $conexion->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Error en la preparación: " . $conexion->error);
        }

        // 3. Vincular parámetros (sssiiii)
        // sss: fecha, hora, motivo | iiii: estado, medico, especialidad, id_cita
        $stmt->bind_param("sssiiii", 
            $fecha_cita, 
            $hora_cita, 
            $motivo, 
            $estado, 
            $id_medico, 
            $id_especialidad, 
            $id_cita
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Error al actualizar: " . $stmt->error);
        }

        // Confirmar cambios
        $conexion->commit();
        $_SESSION['mensaje_user_exito'] = '✅ Éxito: La cita fue actualizda correctamente.';
        header("Location: ../../pages/php/citas_medicas_listado.php");

    } catch (Exception $e) {
        // Revertir en caso de error
        $conexion->rollback();
        error_log("Error de transacción al actualizar la cita: " . $e->getMessage()); 
        $_SESSION['mensaje_user_error'] = '❌ Error de Registro: No se pudo actualizar la cita. Detalle: ' . $e->getMessage();
        header("Location: ../../pages/php/citas_medicas_listado.php");
    }

    $stmt->close();
    $conexion->close();

} else {
    echo "Error: Datos insuficientes para procesar la actualización.";
}
?>