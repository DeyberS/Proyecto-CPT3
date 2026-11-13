<?php
if (isset($_GET['Id_patologia'])) {
  $id = $_GET['Id_patologia'];
  $conexion = new mysqli("localhost", "root", "", "cpt3db");

  $stmt = $conexion->prepare("SELECT codigo_cie FROM patologias WHERE Id_patologia = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($row = $result->fetch_assoc()) {
    echo json_encode(['codigo_cie' => $row['codigo_cie']]);
  } else {
    echo json_encode(['codigo_cie' => '']);
  }
}
?>