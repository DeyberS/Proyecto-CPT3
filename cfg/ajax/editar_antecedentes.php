<?php
include("../conexion.php");
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) session_start();

$response = ['success' => false, 'error' => ''];

// ───── 1. Permiso en servidor ─────
$permisos = $_SESSION['permisos'] ?? [];
if (!in_array('Editar Pacientes', $permisos, true)) {
    echo json_encode(['success' => false, 'error' => 'No tiene permiso para editar antecedentes.']);
    exit;
}

// ───── 2. Entrada ─────
$cedula    = trim($_POST['cedula'] ?? '');
$categoria = trim($_POST['categoria'] ?? '');
$datos_raw = $_POST['datos'] ?? '{}';
$obs       = trim($_POST['observaciones'] ?? '');

if ($cedula === '' || $categoria === '') {
    echo json_encode(['success' => false, 'error' => 'Parámetros incompletos.']);
    exit;
}

$datos = json_decode($datos_raw, true);
if (!is_array($datos)) {
    echo json_encode(['success' => false, 'error' => 'Formato de datos inválido.']);
    exit;
}

// ───── 3. Whitelist categorías → tablas ─────
// [ rel_table, mae_table, fk, solo_menor_de_18 ]
$CATEGORIAS = [
    'perinatales' => ['historial_antecedentes_perinatales',              'antecedentes_perinatales',              'Id_antecedente', true],
    'familiares'  => ['historial_antecedentes_familiares',               'antecedentes_familiares',               'Id_antecedente', false],
    'gineco'      => ['historial_antecedentes_sexuales_reproductivos',   'antecedentes_sexuales_reproductivos',   'Id_antecedente', false],
    'habitos'     => ['estilos_de_vida_paciente',                        'tipos_estilos_de_vida',                 'Id_tipo',        false],
    'patologicos' => ['historial_antecedentes_patologicos',              'antecedentes_patologicos',              'Id_antecedente', false],
];

if (!isset($CATEGORIAS[$categoria])) {
    echo json_encode(['success' => false, 'error' => 'Categoría no válida.']);
    exit;
}

list($t_rel, $t_mae, $fk, $solo_menor) = $CATEGORIAS[$categoria];

try {
    // ───── 4. Buscar historial del paciente ─────
    $stmt = $conexion->prepare(
        "SELECT h.id_historial, p.fecha_nacimiento
         FROM historial_medico h
         JOIN persona p ON h.Id_persona = p.id
         WHERE p.cedula = ? LIMIT 1"
    );
    $stmt->bind_param("s", $cedula);
    $stmt->execute();
    $res = $stmt->get_result();

    if (!$res || $res->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Historial del paciente no encontrado.']);
        exit;
    }

    $row_p    = $res->fetch_assoc();
    $id_hist  = (int)$row_p['id_historial'];

    // ───── 5. Restricción de edad para perinatales ─────
    if ($solo_menor) {
        $nac  = new DateTime($row_p['fecha_nacimiento']);
        $edad = (new DateTime())->diff($nac)->y;
        if ($edad >= 18) {
            echo json_encode([
                'success' => false,
                'error'   => 'Solo se registran antecedentes perinatales en menores de 18 años.'
            ]);
            exit;
        }
    }

    // ───── 6. Observaciones dentro del JSON como __obs__ ─────
    if ($obs !== '') {
        $datos['__obs__'] = $obs;
    } else {
        unset($datos['__obs__']);
    }
    $json = json_encode($datos, JSON_UNESCAPED_UNICODE);

    // ───── 7. Upsert ─────
    $stmt = $conexion->prepare("SELECT $fk FROM $t_rel WHERE Id_Historial = ? LIMIT 1");
    $stmt->bind_param("i", $id_hist);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        // UPDATE (incluso si queda vacío, para poder "borrar" lo previo)
        $id_ant = (int)$res->fetch_assoc()[$fk];
        $stmt = $conexion->prepare("UPDATE $t_mae SET descripcion = ? WHERE Id = ?");
        $stmt->bind_param("si", $json, $id_ant);
        $stmt->execute();
    } else if (!empty($datos)) {
        // INSERT solo si hay datos reales
        $stmt = $conexion->prepare("INSERT INTO $t_mae (descripcion, estatus) VALUES (?, '1')");
        $stmt->bind_param("s", $json);
        $stmt->execute();
        $nuevo_id = (int)$conexion->insert_id;

        $stmt = $conexion->prepare("INSERT INTO $t_rel (Id_Historial, $fk, estatus) VALUES (?, ?, '1')");
        $stmt->bind_param("ii", $id_hist, $nuevo_id);
        $stmt->execute();
    }

    $response['success'] = true;
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);