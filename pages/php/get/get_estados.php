<?php
error_reporting(0);
// 2. Forzar al navegador a entender que es un JSON y no guardarlo en caché
header('Content-Type: application/json; charset=utf-8');
header("Cache-Control: no-cache, must-revalidate");

if (isset($_GET['Id_Pais'])) {
  $pais_id = $_GET['Id_Pais'];
  $conexion = new mysqli("localhost", "root", "", "cpt3db");

  $conexion->set_charset("utf8");

  $stmt = $conexion->prepare("SELECT Id_Estado, nombre_estado FROM estado WHERE Id_Pais = ?");
  $stmt->bind_param("i", $pais_id);
  $stmt->execute();
  $result = $stmt->get_result();

  $estados = [];
  while ($row = $result->fetch_assoc()) {
    $estados[] = $row;
  }

  echo json_encode($estados);
}
?>