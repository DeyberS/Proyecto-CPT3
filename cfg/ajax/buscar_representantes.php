<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');

require_once "../conexion.php";

$q = isset($_GET['q']) ? trim($_GET['q']) : "";

if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

$termino = "%" . $q . "%";

$sql = "SELECT p.id, p.nombre, p.apellido, p.tipo_cedula, p.cedula, p.email
        FROM persona p
        JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona
        JOIN rol r ON dpr.Id_rol = r.Id_rol
        WHERE (r.nombre_rol LIKE '%Representante%' OR r.Id_rol = 4)
          AND p.estatus IN (1, 2)
          AND (
                p.cedula LIKE ?
             OR p.nombre LIKE ?
             OR p.apellido LIKE ?
             OR CONCAT(p.nombre, ' ', p.apellido) LIKE ?
          )
        ORDER BY p.nombre ASC
        LIMIT 25";

if ($stmt = mysqli_prepare($conexion, $sql)) {
    mysqli_stmt_bind_param($stmt, "ssss", $termino, $termino, $termino, $termino);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    $resultados = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $resultados[] = [
            'id' => $row['id'],
            'nombre' => $row['nombre'],
            'apellido' => $row['apellido'],
            'tipo_cedula' => $row['tipo_cedula'],
            'cedula' => $row['cedula'],
            'email' => $row['email']
        ];
    }
    mysqli_stmt_close($stmt);
    echo json_encode($resultados);
} else {
    echo json_encode([]);
}

mysqli_close($conexion);