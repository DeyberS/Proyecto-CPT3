<?php
if (isset($_GET['Id_Estado'])) {
  $estado_id = $_GET['Id_Estado'];
  $conexion = new mysqli("localhost", "root", "", "cpt3db");
  $stmt = $conexion->prepare("SELECT Id_Municipio, nombre_municipio FROM municipio WHERE Id_Estado = ?");
  $stmt->bind_param("i", $estado_id);
  $stmt->execute();
  $result = $stmt->get_result();

  $municipios = [];
  while ($row = $result->fetch_assoc()) {
    $municipios[] = $row;
  }

  echo json_encode($municipios);
}
?>
