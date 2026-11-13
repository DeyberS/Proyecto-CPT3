<?php
include("../conexion.php");
header('Content-Type: application/json');

$accion       = $_POST['accion'] ?? '';
$id_item      = (int)($_POST['id_item'] ?? 0);
$id_historial = (int)($_POST['id_historial_global'] ?? 0);
$cedula       = $_POST['cedula'] ?? '';
$fecha        = $_POST['fecha'] ?? date('Y-m-d');

if (!$id_item || !$id_historial || !$cedula) {
  echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
  exit;
}

// Verificar que el historial pertenece al paciente
$stmt = $conexion->prepare("
  SELECT hm.id_historial
  FROM historial_medico hm
  JOIN persona p ON hm.Id_persona = p.id
  WHERE hm.id_historial = ? AND p.cedula = ?
");
$stmt->bind_param("is", $id_historial, $cedula);
$stmt->execute();
$owner = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$owner) {
  echo json_encode(["status" => "error", "message" => "El historial no corresponde al paciente."]);
  exit;
}

if ($accion === 'vincular_patologia') {
  $stmt = $conexion->prepare("
    SELECT 1 FROM historial_patologias 
    WHERE Id_Historial = ? AND Id_patologia = ?
  ");
  $stmt->bind_param("ii", $id_historial, $id_item);
  $stmt->execute();
  if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Disculpe, esa patología ya está registrada para el paciente."]);
    $stmt->close();
    exit;
  }
  $stmt->close();

  $stmt = $conexion->prepare("
    INSERT INTO historial_patologias (Id_Historial, Id_patologia, fecha_registro, estatus)
    VALUES (?, ?, ?, 1)
  ");
  $stmt->bind_param("iis", $id_historial, $id_item, $fecha);
}

elseif ($accion === 'vincular_alergia') {
  $stmt = $conexion->prepare("
    SELECT 1 FROM historial_alergias 
    WHERE Id_Historial = ? AND Id_alergia = ?
  ");
  $stmt->bind_param("ii", $id_historial, $id_item);
  $stmt->execute();
  if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Disculpe, esa alergia ya está registrada para el paciente."]);
    $stmt->close();
    exit;
  }
  $stmt->close();

  $stmt = $conexion->prepare("
    INSERT INTO historial_alergias (Id_Historial, Id_alergia, fecha_registro, estatus)
    VALUES (?, ?, ?, 1)
  ");
  $stmt->bind_param("iis", $id_historial, $id_item, $fecha);
}

elseif ($accion === 'vincular_discapacidad') {
  $stmt = $conexion->prepare("
    SELECT 1 FROM historial_discapacidades 
    WHERE Id_Historial = ? AND Id_discapacidad = ?
  ");
  $stmt->bind_param("ii", $id_historial, $id_item);
  $stmt->execute();
  if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Disculpe, esa discapacidad ya está registrada para el paciente."]);
    $stmt->close();
    exit;
  }
  $stmt->close();

  $stmt = $conexion->prepare("
    INSERT INTO historial_discapacidades (Id_Historial, Id_discapacidad, fecha_registro, estatus)
    VALUES (?, ?, ?, 1)
  ");
  $stmt->bind_param("iis", $id_historial, $id_item, $fecha);
}

else {
  echo json_encode(["status" => "error", "message" => "Acción no reconocida."]);
  exit;
}

if ($stmt->execute()) {
  echo json_encode(["status" => "success"]);
} else {
  echo json_encode(["status" => "error", "message" => "Error al guardar: " . $stmt->error]);
}
$stmt->close();