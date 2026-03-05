<?php
if (isset($_GET['Id_Municipio'])) {
  $municipio_id = $_GET['Id_Municipio'];
  $conexion = new mysqli("localhost", "root", "", "cpt3db");
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