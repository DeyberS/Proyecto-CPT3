<?php
session_start();
// Asegúrate de que esta ruta es correcta para tu entorno.
include("../conexion.php"); 

// Validación de seguridad básica
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($conexion)) {
    die("<h3 style='color:red'>Error: Acceso denegado o falta conexión.</h3>");
}

/**
 * Función para sanitizar entradas.
 */
function sanitizar($conexion, $input) {
    // Usamos el operador de fusión de null para evitar errores si el input no existe
    return mysqli_real_escape_string($conexion, trim($input ?? ''));
}

/**
 * Función para manejar errores de transacción y mostrar un mensaje.
 */
function handle_error($conexion, $ubicacion, $sql, $mensaje_extra = '') {
    if ($conexion) mysqli_rollback($conexion);
    echo "<div style='background:#f8d7da; padding:20px; border:1px solid #f5c6cb; border-radius:5px;'>";
    echo "<h2 style='color:#721c24'>❌ Error en: $ubicacion</h2>";
    echo "<p><strong>Mensaje:</strong> " . mysqli_error($conexion) . " $mensaje_extra</p>";
    echo "<pre style='background:#eee; padding:10px;'>$sql</pre>";
    echo "<p><strong>¡Restricción Violada!</strong> Revise que los ID de medicamento y unidades existan en sus respectivas tablas.</p>";
    echo "<br><button onclick='window.history.back()'>Volver</button>";
    echo "</div>";
    exit();
}

// Iniciar transacción para asegurar la integridad de los datos
mysqli_begin_transaction($conexion);

// ==========================================================
// 1. OBTENER Y SANITIZAR DATOS DEL FORMULARIO
// ==========================================================

// Datos de consulta
$id_consulta = sanitizar($conexion, $_POST['Id_Consulta']);
$id_historial = sanitizar($conexion, $_POST['Id_historial']);
$id_medico = sanitizar($conexion, $_POST['medico']);
$fecha_consulta = sanitizar($conexion, $_POST['fecha_consulta']);
$motivo_consulta = sanitizar($conexion, $_POST['motivo_consulta']);
$peso = sanitizar($conexion, $_POST['peso']);
$talla = sanitizar($conexion, $_POST['talla']);
$temperatura = sanitizar($conexion, $_POST['temperatura']);
$tension = sanitizar($conexion, $_POST['tension']);
$frecuencia_cardiaca = sanitizar($conexion, $_POST['frecuencia_cardiaca']);
$saturacion = sanitizar($conexion, $_POST['saturacion']);
$frecuencia_respiratoria = sanitizar($conexion, $_POST['frecuencia_respiratoria']);
$diagnostico = sanitizar($conexion, $_POST['diagnostico_text']);
$indicaciones = sanitizar($conexion, $_POST['indicaciones']);

$estado_paciente   = mysqli_real_escape_string($conexion, $_POST['estado_paciente']);
$reaccion_adversa    = mysqli_real_escape_string($conexion, $_POST['reaccion_adversa']);
$detalle_reaccion    = mysqli_real_escape_string($conexion, $_POST['detalle_reaccion']);
$evolucion_resultado   = mysqli_real_escape_string($conexion, $_POST['evolucion_resultado']);

$txt_peri = mysqli_real_escape_string($conexion, $_POST['perinatales']);
$txt_fam  = mysqli_real_escape_string($conexion, $_POST['familiares']);
$txt_sex  = mysqli_real_escape_string($conexion, $_POST['sexualidad_reproductivos']);
$txt_est  = mysqli_real_escape_string($conexion, $_POST['estilo_vida']);

$lectura_examenes   = mysqli_real_escape_string($conexion, $_POST['lectura_examenes']);
$examenes_solicitados   = mysqli_real_escape_string($conexion, $_POST['examenes_solicitados']);

$entregado_a   = mysqli_real_escape_string($conexion, $_POST['entregado_a']);
$parentesco_representante   = mysqli_real_escape_string($conexion, $_POST['parentesco_representante']);

// Medicamentos (JSON)
$medicamentos_json = $_POST['medicamento_full_data'] ?? '[]';
$medicamentos = json_decode($medicamentos_json, true);

// Notas Adicionales (van al historial como un nuevo registro)
$notas_adicionales = sanitizar($conexion, $_POST['notas_adicionales']);


// ==========================================================
// 2. ACTUALIZAR CONSULTA PRINCIPAL
// ==========================================================

$sql_update_consulta = "
    UPDATE consulta SET
        Id_medico = '$id_medico',
        fecha_consulta = '$fecha_consulta',
        motivo_consulta = '$motivo_consulta',
        peso = '$peso',
        talla = '$talla',
        temperatura = '$temperatura',
        tension = '$tension',
        frecuencia_cardiaca = '$frecuencia_cardiaca',
        saturacion = '$saturacion',
        frecuencia_respiratoria = '$frecuencia_respiratoria',
        diagnostico = '$diagnostico',
        tratamiento_indicado = '$indicaciones',
        estado_paciente = '$estado_paciente',
        reaccion_adversa = '$reaccion_adversa',
        detalle_reaccion = '$detalle_reaccion',
        evolucion_resultado = '$evolucion_resultado',
        lectura_examenes = '$lectura_examenes',
        examenes_solicitados = '$examenes_solicitados',
        entregado_a = '$entregado_a',
        parentesco = '$parentesco_representante'
    WHERE Id_consulta = '$id_consulta'
";

if (!mysqli_query($conexion, $sql_update_consulta)) {
    handle_error($conexion, "Actualización de Consulta Principal", $sql_update_consulta);
}


// ==========================================================
// 3. PROCESAR MEDICAMENTOS (ELIMINAR Y REINSERTAR)
// ==========================================================

// A. ELIMINAR PRESCRIPCIONES ANTIGUAS
$sql_delete_prescripcion = "DELETE FROM prescripcion_medicamentos WHERE Id_consulta = '$id_consulta'";
if (!mysqli_query($conexion, $sql_delete_prescripcion)) {
    handle_error($conexion, "Eliminación de Prescripciones Antiguas", $sql_delete_prescripcion);
}

// B. INSERTAR NUEVAS PRESCRIPCIONES
if (!empty($medicamentos) && is_array($medicamentos)) {
    
    foreach ($medicamentos as $med) {
        // Ahora el ID viene directo como 'id' (es el ID de la tabla descripcion_medicamento)
        $id_descripcion_medicamento = mysqli_real_escape_string($conexion, $med['id']);

        // Validamos que el ID no sea vacío para evitar errores de FK
        if (!empty($id_descripcion_medicamento)) {
            $sql_insert_prescripcion = "
                INSERT INTO prescripcion_medicamentos (Id_consulta, Id_descripcion_medicamento)
                VALUES ('$id_consulta', '$id_descripcion_medicamento')
            ";

            if (!mysqli_query($conexion, $sql_insert_prescripcion)) {
                handle_error($conexion, "Inserción de Prescripcion_Medicamentos", $sql_insert_prescripcion);
            }
        }
    }
}

mysqli_query($conexion, "UPDATE antecedentes_perinatales SET descripcion = '$txt_peri' 
    WHERE Id = (SELECT Id_antecedente FROM historial_antecedentes_perinatales WHERE Id_historial = '$id_historial' LIMIT 1)");

// Actualizar Familiares
mysqli_query($conexion, "UPDATE antecedentes_familiares SET descripcion = '$txt_fam' 
    WHERE Id = (SELECT Id_antecedente FROM historial_antecedentes_familiares WHERE Id_historial = '$id_historial' LIMIT 1)");

// Actualizar Sexuales
mysqli_query($conexion, "UPDATE antecedentes_sexuales_reproductivos SET descripcion = '$txt_sex' 
    WHERE Id = (SELECT Id_antecedente FROM historial_antecedentes_sexuales_reproductivos WHERE Id_historial = '$id_historial' LIMIT 1)");

// Actualizar Estilo de Vida
mysqli_query($conexion, "UPDATE tipos_estilos_de_vida SET descripcion = '$txt_est' 
    WHERE Id = (SELECT Id_tipo FROM estilos_de_vida_paciente WHERE Id_historial = '$id_historial' LIMIT 1)");

// Actualizar Observaciones (Aquí el campo es 'observacion' y la tabla se une por Id_historial_medico)
mysqli_query($conexion, "UPDATE observaciones_historial_medico SET observacion = '$notas_adicionales' 
    WHERE Id_historial_medico = '$id_historial'");

// ==========================================================
// 5. FINALIZAR TRANSACCIÓN Y REDIRECCIÓN
// ==========================================================

if (mysqli_commit($conexion)) {
    // Redirección exitosa (ajusta la ruta según tu sistema)
    // Se recomienda redirigir al historial del paciente o a la lista de consultas.
    header("Location: ../../pages/php/consulta_listado.php?id_consulta=$id_historial");
    $_SESSION['mensaje_user_exito'] = '✅ Éxito: La consulta fue actualizada correctamente.';
    exit();
} else {
    handle_error($conexion, "Commit de Transacción", "", "La transacción falló al intentar actualizar.");
    $_SESSION['mensaje_user_error'] = '❌ Error de Actualización: ' . $e->getMessage();
    header("Location: ../../pages/php/consulta_listado.php");
}
?>


