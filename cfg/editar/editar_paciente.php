<?php
session_start();
require_once "../conexion.php"; 

// ------------------------------------
// 1. Recolección de Datos
// ------------------------------------

$id_paciente = $_POST['Id']; 

$tipo_cedula = $_POST['tipo_cedula'];
$cedula = $_POST['cedula'];
$nombre = $_POST['nombre'];
$apellido = $_POST['apellido'];
$fecha_nacimiento = $_POST['fecha_nacimiento'];
$genero = $_POST['genero'];
$email = $_POST['email'];

$situacion_conyugal = $_POST['situacion_conyugal'];
$etnia = $_POST['etnia'] ?? 'No';
$tipo_etnia = $_POST['tipo_etnia'] ?? '';
$analfabeta = $_POST['analfabeta'];
$seguro_social = $_POST['seguro_social'];
$profesion = $_POST['profesion'];
$ocupacion = $_POST['ocupacion'];
$nivel_instruccion = $_POST['nivel_instruccion'];
$mision = $_POST['mision'];
$años_aprobados = $_POST['años_aprobados'];

$lugar_nacimiento_municipio = $_POST['municipio_nacimiento'];

$prefijo = $_POST['prefijo'];
$telefono = $_POST['telefono'];

$sector = $_POST['sector'];
$avenida_calle = $_POST['avenida_calle'];
$referencia = $_POST['punto_referencia']; 
$tiempo_residencia = $_POST['tiempo_residencia'];
$tiempo = $_POST['tiempo'] ?? '';

$grupo_sanguineo = $_POST['grupo_sanguineo'];

$patologias_ids_csv = $_POST['patologias_ids'] ?? ''; 
$alergias_ids_csv = $_POST['alergias_ids'] ?? '';

$patologias_fechas_csv = $_POST['patologias_fechas'] ?? '';
$alergias_fechas_csv = $_POST['alergias_fechas'] ?? '';

$discapacidad = $_POST['discapacidad'] ?? 'No';
$tipo_discapacidad = $_POST['tipo_discapacidad'] ?? '';   


// ------------------------------------
// 2. Validación de Cédula
// ------------------------------------

$query_check = "SELECT id FROM persona WHERE tipo_cedula = '$tipo_cedula' AND cedula = '$cedula' AND id != '$id_paciente'";
$result_check = mysqli_query($conexion, $query_check);

if (mysqli_num_rows($result_check) > 0) {
    $_SESSION['mensaje_estado'] = 'error';
    $_SESSION['mensaje_texto'] = "La cédula '$tipo_cedula-$cedula' ya está registrada.";
    header("location: ../../pages/php/pacientes_listado.php?Id=$id_paciente");
    exit();
}

// ------------------------------------
// 3. Transacción
// ------------------------------------

mysqli_begin_transaction($conexion);

try {

    $result_historial = mysqli_query($conexion, "SELECT id_historial FROM historial_medico WHERE Id_persona = '$id_paciente'");
    $row_historial = mysqli_fetch_assoc($result_historial);
    $id_historial = $row_historial['id_historial'];


// -------------------------------------------------------------
// ACTUALIZACIONES NORMALES (SIN CAMBIOS)
// -------------------------------------------------------------

mysqli_query($conexion,"UPDATE persona SET 
tipo_cedula='$tipo_cedula',
cedula='$cedula',
nombre='$nombre',
apellido='$apellido',
fecha_nacimiento='$fecha_nacimiento',
genero='$genero',
email='$email'
WHERE id='$id_paciente'");

mysqli_query($conexion,"UPDATE detalle_paciente SET
situacion_conyugal='$situacion_conyugal',
etnia='$etnia',
tipo_etnia='$tipo_etnia',
analfabeta='$analfabeta',
seguro_social='$seguro_social',
profesion='$profesion',
ocupacion='$ocupacion',
nivel_instruccion='$nivel_instruccion',
mision='$mision',
años_aprobados='$años_aprobados',
discapacidad='$discapacidad',
tipo_discapacidad='$tipo_discapacidad'
WHERE Id_persona='$id_paciente'");

mysqli_query($conexion,"UPDATE direccion SET
tiempo_residencia='$tiempo_residencia',
tiempo='$tiempo',
avenida_calle='$avenida_calle',
referencia='$referencia',
Id_sector='$sector'
WHERE Id_persona='$id_paciente'");

mysqli_query($conexion,"UPDATE lugar_nacimiento SET
Id_municipio='$lugar_nacimiento_municipio'
WHERE Id_persona='$id_paciente'");

mysqli_query($conexion,"UPDATE telefonos_personas SET
Id_prefijo='$prefijo',
telefono='$telefono'
WHERE Id_persona='$id_paciente'");

mysqli_query($conexion,"UPDATE historial_medico SET
grupo_sanguineo='$grupo_sanguineo'
WHERE Id_persona='$id_paciente'");


// -------------------------------------------------------------
// 🔥 PATOLOGÍAS CON FECHA
// -------------------------------------------------------------

mysqli_query($conexion,"DELETE FROM historial_patologias 
WHERE Id_Historial='$id_historial' AND Id_persona='$id_paciente'");

$patologias_ids = array_filter(explode(',',$patologias_ids_csv));
$patologias_fechas = [];

foreach(explode(',',$patologias_fechas_csv) as $item){
    if(strpos($item,':')!==false){
        [$id,$fecha]=explode(':',$item);
        $patologias_fechas[$id]=$fecha;
    }
}

$values=[];

foreach($patologias_ids as $id_patologia){

    $fecha = $patologias_fechas[$id_patologia] ?? date('Y-m-d');

    $values[]="('$id_patologia','$id_historial','$id_paciente',1,'$fecha')";
}

if($values){
    mysqli_query($conexion,"INSERT INTO historial_patologias 
    (Id_patologia,Id_Historial,Id_persona,estatus,fecha_registro)
    VALUES ".implode(',',$values));
}


// -------------------------------------------------------------
// 🔥 ALERGIAS CON FECHA
// -------------------------------------------------------------

mysqli_query($conexion,"DELETE FROM historial_alergias 
WHERE Id_Historial='$id_historial' AND Id_persona='$id_paciente'");

$alergias_ids = array_filter(explode(',',$alergias_ids_csv));
$alergias_fechas=[];

foreach(explode(',',$alergias_fechas_csv) as $item){
    if(strpos($item,':')!==false){
        [$id,$fecha]=explode(':',$item);
        $alergias_fechas[$id]=$fecha;
    }
}

$values=[];

foreach($alergias_ids as $id_alergia){

    $fecha = $alergias_fechas[$id_alergia] ?? date('Y-m-d');

    $values[]="('$id_alergia','$id_historial','$id_paciente',1,'$fecha')";
}

if($values){
    mysqli_query($conexion,"INSERT INTO historial_alergias
    (Id_alergia,Id_Historial,Id_persona,estatus,fecha_registro)
    VALUES ".implode(',',$values));
}


mysqli_commit($conexion);

$_SESSION['mensaje_user_exito']="Paciente actualizado correctamente";
header("location: ../../pages/php/pacientes_listado.php");

}catch(Exception $e){

mysqli_rollback($conexion);
$_SESSION['mensaje_user_error']="Error: ".$e->getMessage();
header("location: ../../pages/php/pacientes_listado.php");

}

mysqli_close($conexion);
?>

