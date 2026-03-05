<?php
session_start();
require_once "../conexion.php"; 

$id_representante = $_POST['Id']; 

// PERSONA
$tipo_cedula = $_POST['tipo_cedula'];
$cedula = $_POST['cedula'];
$nombre = $_POST['nombre'];
$apellido = $_POST['apellido'];
$fecha_nacimiento = $_POST['fecha_nacimiento'];
$genero = $_POST['genero'];
$email = $_POST['email'];

// DETALLE representante
$situacion_conyugal = $_POST['situacion_conyugal'];

// LUGAR NACIMIENTO
$lugar_nacimiento_municipio = !empty($_POST['municipio_nacimiento']) ? $_POST['municipio_nacimiento'] : "NULL";

// TELEFONO
$prefijo = $_POST['prefijo'];
$telefono = $_POST['telefono'];

// DIRECCION
$sector = !empty($_POST['sector']) ? $_POST['sector'] : "NULL";
$avenida_calle = $_POST['avenida_calle'];
$referencia = $_POST['punto_referencia']; 
$tiempo_residencia = $_POST['tiempo_residencia'];
$tiempo = $_POST['tiempo'] ?? '';

// ------------------------------------
// 2. Validación de Cédula Duplicada
// ------------------------------------
$query_check = "SELECT id FROM persona WHERE tipo_cedula = '$tipo_cedula' AND cedula = '$cedula' AND id != '$id_representante'";
$result_check = mysqli_query($conexion, $query_check);

if (mysqli_num_rows($result_check) > 0) {
    $_SESSION['mensaje_estado'] = 'error';
    $_SESSION['mensaje_texto'] = "La cédula '$tipo_cedula-$cedula' ya está registrada para otro representante. ❌";
    header("location: ../../pages/php/representantes_listado.php?Id=$id_representante");
    exit();
}

// ------------------------------------
// 3. Transacción
// ------------------------------------
mysqli_begin_transaction($conexion);

try {
    // --- 3.1. ACTUALIZAR persona ---
    $sql_persona = "UPDATE persona SET 
                        tipo_cedula = '$tipo_cedula', 
                        cedula = '$cedula', 
                        nombre = '$nombre', 
                        apellido = '$apellido', 
                        fecha_nacimiento = '$fecha_nacimiento',
                        genero = '$genero',
                        email = '$email'
                    WHERE id = '$id_representante'";
    if (!mysqli_query($conexion, $sql_persona)) {
        throw new Exception("Error al actualizar la tabla persona.");
    }

    // --- 3.3. DIRECCION (UPDATE o INSERT) ---
    $sql_check_dir = "SELECT 1 FROM direccion WHERE Id_persona = '$id_representante'";
    $res_dir = mysqli_query($conexion, $sql_check_dir);

    if (mysqli_num_rows($res_dir) > 0) {
        // UPDATE
        $sql_direccion = "UPDATE direccion SET 
                            tiempo_residencia = '$tiempo_residencia',
                            tiempo = '$tiempo',  
                            avenida_calle = '$avenida_calle', 
                            referencia = '$referencia', 
                            Id_sector = $sector
                          WHERE Id_persona = '$id_representante'";
    } else {
        // INSERT
        $sql_direccion = "INSERT INTO direccion(Id_persona, tiempo_residencia, tiempo, avenida_calle, referencia, Id_sector, estatus)
                          VALUES('$id_representante', '$tiempo_residencia', '$tiempo', '$avenida_calle', '$referencia', $sector, 1)";
    }
    if (!mysqli_query($conexion, $sql_direccion)) {
        throw new Exception("Error al guardar dirección.");
    }

    // --- 3.4. LUGAR_NACIMIENTO (UPDATE o INSERT) ---
    $sql_check_ln = "SELECT 1 FROM lugar_nacimiento WHERE Id_persona = '$id_representante'";
    $res_ln = mysqli_query($conexion, $sql_check_ln);

    if (mysqli_num_rows($res_ln) > 0) {
        $sql_lugar_nacimiento = "UPDATE lugar_nacimiento SET Id_municipio = $lugar_nacimiento_municipio
                                 WHERE Id_persona = '$id_representante'";
    } else {
        $sql_lugar_nacimiento = "INSERT INTO lugar_nacimiento(Id_persona, Id_municipio)
                                 VALUES('$id_representante', $lugar_nacimiento_municipio)";
    }
    if (!mysqli_query($conexion, $sql_lugar_nacimiento)) {
        throw new Exception("Error al guardar lugar de nacimiento.");
    }

    // --- 3.5. TELEFONOS_PERSONAS (UPDATE o INSERT) ---
    $sql_check_tel = "SELECT 1 FROM telefonos_personas WHERE Id_persona = '$id_representante'";
    $res_tel = mysqli_query($conexion, $sql_check_tel);

    if (mysqli_num_rows($res_tel) > 0) {
        $sql_telefono = "UPDATE telefonos_personas SET 
                            Id_prefijo = '$prefijo', 
                            telefono = '$telefono'
                         WHERE Id_persona = '$id_representante'";
    } else {
        $sql_telefono = "INSERT INTO telefonos_personas(Id_persona, Id_prefijo, telefono, estatus)
                         VALUES('$id_representante', '$prefijo', '$telefono', 1)";
    }
    if (!mysqli_query($conexion, $sql_telefono)) {
        throw new Exception("Error al guardar teléfono.");
    }
    
    // --- Commit ---
    mysqli_commit($conexion);
    $_SESSION['mensaje_user_exito'] = '✅ Éxito: El representante ' . $nombre . ' ' . $apellido . ' ha sido actualizado correctamente.';
    header('location: ../../pages/php/representantes_listado.php');

} catch (Exception $e) {
    mysqli_rollback($conexion);
    $_SESSION['mensaje_user_error'] = '❌ Error de Actualización: ' . $e->getMessage();
    header("location: ../../pages/php/representantes_listado.php?Id=$id_representante");
}

mysqli_close($conexion);
?>

