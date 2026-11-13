<?php
include('../conexion.php');
$id_pres = $_GET['id'];
$filtro = "";

if ($id_pres == 1 || $id_pres == 2 || $id_pres == 7 || $id_pres == 10 || $id_pres == 11) {
    $filtro = "WHERE unidad IN ('mg', 'g', 'mcg')";
} else if ($id_pres == 3 || $id_pres == 4 || $id_pres == 8 || $id_pres == 9) {
    $filtro = "WHERE unidad IN ('ml', 'L')";
}
else if ($id_pres == 5) {
    $filtro = "WHERE unidad IN ('g', 'mg')";
}
else if ($id_pres == 6) {
    $filtro = "WHERE unidad IN ('ml', 'mg')";
}

$sql = $conexion->query("SELECT * FROM unidad_medida $filtro");

echo '<option value="">--- Seleccione una unidad ---</option>';
while ($r = $sql->fetch_assoc()) {
    echo "<option value='".$r['Id_unidad_medida']."'>".$r['unidad']."</option>";
}
?>


