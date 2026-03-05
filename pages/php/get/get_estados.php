<?php
if (isset($_GET['Id_Pais'])) {
  $pais_id = $_GET['Id_Pais'];
  $conexion = new mysqli("localhost", "root", "", "cpt3db");

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