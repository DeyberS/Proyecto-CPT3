<?php
// get/get_permisos_por_rol.php
header('Content-Type: application/json');
include("../../cfg/conexion.php");

$id_rol = $_POST['id_rol'];

$sql = "SELECT p.Id_permiso, p.nombre_permiso 
        FROM permiso p
        INNER JOIN rol_permiso rp ON p.Id_permiso = rp.Id_permiso
        WHERE rp.Id_rol = ? AND p.estatus = 1";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_rol);
$stmt->execute();
$result = $stmt->get_result();

$permisos = [];
while ($row = $result->fetch_assoc()) {
    $permisos[] = [
        'id' => $row['Id_permiso'],
        'nombre' => $row['nombre_permiso']
    ];
}

echo json_encode($permisos);
?>