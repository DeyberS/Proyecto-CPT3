<?php
include("../conexion.php");
header('Content-Type: application/json');

$accion = $_POST['accion'];
$id_item = $_POST['id_item'];
$id_historial = $_POST['id_historial_global'];
$cedula = $_POST['cedula'];
$fecha = $_POST['fecha'];

// Obtenemos el ID interno de la persona
$res_p = $conexion->query("SELECT id FROM persona WHERE cedula = '$cedula' LIMIT 1");
$id_persona = ($res_p->fetch_assoc())['id'];

if ($accion == 'vincular_patologia') {
    // 1. VALIDACIÓN DE DUPLICADOS
    $check = $conexion->query("SELECT 1 FROM historial_patologias 
                               WHERE Id_persona = '$id_persona' AND Id_patologia = '$id_item'");
    
    if ($check->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Esta patología ya está registrada en el historial del paciente."]);
        exit;
    }

    // 2. INSERCIÓN
    $sql = "INSERT INTO historial_patologias (Id_historial, Id_persona, Id_patologia, fecha_registro, estatus) 
            VALUES ('$id_historial', '$id_persona', '$id_item', '$fecha', 1)";
} 

elseif ($accion == 'vincular_alergia') {
    // 1. VALIDACIÓN DE DUPLICADOS
    $check = $conexion->query("SELECT 1 FROM historial_alergias 
                               WHERE Id_persona = '$id_persona' AND Id_alergia = '$id_item'");
    
    if ($check->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Esta alergia ya está registrada para este paciente."]);
        exit;
    }

    // 2. INSERCIÓN
    $sql = "INSERT INTO historial_alergias (Id_historial, Id_persona, Id_alergia, fecha_registro, estatus) 
            VALUES ('$id_historial', '$id_persona', '$id_item', '$fecha', 1)";
}

if ($conexion->query($sql)) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error al guardar: " . $conexion->error]);
}