<?php
error_reporting(0);
// 2. Forzar al navegador a entender que es un JSON y no guardarlo en caché
header('Content-Type: application/json; charset=utf-8');
header("Cache-Control: no-cache, must-revalidate");

if (isset($_GET['Id_Municipio'])) {
  $municipio_id = $_GET['Id_Municipio'];
  $conexion = new mysqli("localhost", "root", "", "cpt3db");
  $conexion->set_charset("utf8");
  
  $stmt = $conexion->prepare("SELECT Id_Sector, nombre_sector FROM sector WHERE Id_Municipio = ?");
  $stmt->bind_param("i", $municipio_id);
  $stmt->execute();
  $result = $stmt->get_result();

  $sectores = [];
  while ($row = $result->fetch_assoc()) {
    $sectores[] = $row;
  }

  echo json_encode($sectores);
}
?>