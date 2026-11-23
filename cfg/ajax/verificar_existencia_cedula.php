<?php
include('../conexion.php');

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

try {
    $response = ['existe_cedula' => false, 'existe_email' => false];

    if (!isset($_POST['cedula']) && !isset($_POST['email'])) {
        echo json_encode($response);
        exit;
    }

    $cedula      = isset($_POST['cedula'])      ? trim($_POST['cedula'])      : '';
    $tipo_cedula = isset($_POST['tipo_cedula']) ? trim($_POST['tipo_cedula']) : '';
    $email       = isset($_POST['email'])       ? trim($_POST['email'])       : '';
    $id_excluir  = isset($_POST['id'])          ? (int)$_POST['id']           : 0;

    /* ---------- Cédula ---------- */
    if ($cedula !== '') {
        // ⚠️ Si llega tipo_cedula, validamos la pareja (tipo + número)
        if ($tipo_cedula !== '') {
            $sql_cedula = "SELECT COUNT(*) FROM persona WHERE cedula = ? AND tipo_cedula = ?";
            $tipos = "ss";
            $params = [$cedula, $tipo_cedula];
        } else {
            // Fallback: si no llega tipo_cedula, mantenemos el comportamiento antiguo
            // para no romper compatibilidad con llamadas viejas
            $sql_cedula = "SELECT COUNT(*) FROM persona WHERE cedula = ?";
            $tipos = "s";
            $params = [$cedula];
        }

        if ($id_excluir > 0) {
            $sql_cedula .= " AND id != ?";
            $tipos .= "i";
            $params[] = $id_excluir;
        }

        $stmt = $conexion->prepare($sql_cedula);
        if (!$stmt) throw new Exception('Prepare cédula: ' . $conexion->error);

        $stmt->bind_param($tipos, ...$params);
        $stmt->execute();
        $stmt->bind_result($count_cedula);
        $stmt->fetch();
        $stmt->close();

        if ($count_cedula > 0) {
            $response['existe_cedula'] = true;
        }
    }

    /* ---------- Email ---------- */
    if ($email !== '') {
        $sql_email = "SELECT COUNT(*) FROM persona WHERE email = ?";
        $tipos_e = "s";
        $params_e = [$email];

        if ($id_excluir > 0) {
            $sql_email .= " AND id != ?";
            $tipos_e .= "i";
            $params_e[] = $id_excluir;
        }

        $stmt2 = $conexion->prepare($sql_email);
        if (!$stmt2) throw new Exception('Prepare email: ' . $conexion->error);

        $stmt2->bind_param($tipos_e, ...$params_e);
        $stmt2->execute();
        $stmt2->bind_result($count_email);
        $stmt2->fetch();
        $stmt2->close();

        if ($count_email > 0) {
            $response['existe_email'] = true;
        }
    }

    echo json_encode($response);

} catch (Throwable $e) {
    error_log('[verificar_existencia] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'mensaje' => 'No se pudo verificar la existencia.',
        'existe_cedula' => false,
        'existe_email'  => false
    ]);
}