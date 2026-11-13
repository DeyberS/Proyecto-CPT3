<?php
session_start();
include('../conexion.php');

if (isset($_POST['id_paciente']) && isset($_POST['id_medico']) && isset($_POST['id_especialidad'])) {

    $id_paciente     = $_POST['id_paciente'];
    $id_medico       = $_POST['id_medico'];
    $id_especialidad = $_POST['id_especialidad'];
    $fecha_cita      = $_POST['fecha_cita'];
    $hora_cita       = $_POST['hora_cita'];
    $motivo          = $_POST['motivo'];
    $estatus         = 1;

    $conexion->begin_transaction();

    try {
        $sql = "INSERT INTO citas (
                    fecha_cita, 
                    hora_cita, 
                    motivo, 
                    estatus, 
                    Id_paciente, 
                    Id_medico, 
                    Id_especialidad, 
                    fecha_registro
                ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conexion->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("⚠️ Error en la preparación de la consulta: " . $conexion->error);
        }

        $stmt->bind_param("sssiiii", $fecha_cita, $hora_cita, $motivo, $estatus, $id_paciente, $id_medico, $id_especialidad);
        
        if (!$stmt->execute()) {
            throw new Exception("⚠️ Error al ejecutar la inserción: " . $stmt->error);
        }

        $conexion->commit();
        $_SESSION['mensaje_user_exito'] = '✅ Éxito: La cita fue agregada correctamente.';
        header("Location: ../../pages/php/citas_medicas_listado.php");
    } catch (Exception $e) {
        $conexion->rollback();
        error_log("Error de transacción al agendar la cita: " . $e->getMessage()); 
        $_SESSION['mensaje_user_error'] = '⚠️ Error de Registro: No se pudo registrar la cita. Detalle: ' . $e->getMessage();
        header("Location: ../../pages/php/citas_medicas_listado.php");
    }

    $stmt->close();
    $conexion->close();

} else {
    echo "⚠️ Error: No se recibieron todos los datos necesarios del formulario.";
}
?>