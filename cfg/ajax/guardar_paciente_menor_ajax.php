<?php
session_start();
require_once "../../cfg/conexion.php"; // Ajusta la ruta a tu conexión

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $estado = 1;
    $rol_paciente = 3;
    $rol_representante = 5;

    // Recibir datos del menor
    $menor_tipo_cedula = $_POST['menor_tipo_cedula'] ?? '';
    $menor_cedula = $_POST['menor_cedula'] ?? '';
    $menor_nombre = $_POST['menor_nombre'] ?? '';
    $menor_apellido = $_POST['menor_apellido'] ?? '';
    $menor_fecha_nac = $_POST['menor_fecha_nacimiento'] ?? '';
    $menor_genero = $_POST['menor_genero'] ?? '';
    $menor_sangre = $_POST['menor_grupo_sanguineo'] ?? '';
    $menor_etnia = $_POST['menor_etnia'] ?? 'No';
    $menor_tipo_etnia = $_POST['menor_tipo_etnia'] ?? 'Ninguna';
    $menor_discapacidad = $_POST['menor_discapacidad'] ?? 'No';
    $menor_tipo_discapacidad = $_POST['menor_tipo_discapacidad'] ?? 'Ninguna';
    $menor_analfabeta = $_POST['menor_analfabeta'] ?? 'No';

    // Recibir datos del representante
    $rep_tipo_cedula = $_POST['rep_tipo_cedula'] ?? '';
    $rep_cedula = $_POST['rep_cedula'] ?? '';
    $rep_nombre = $_POST['rep_nombre'] ?? '';
    $rep_apellido = $_POST['rep_apellido'] ?? '';
    $rep_fecha_nac = $_POST['rep_fecha_nacimiento'] ?? '';
    $rep_genero = $_POST['rep_genero'] ?? '';
    $rep_prefijo = $_POST['rep_prefijo'] ?? '';
    $rep_telefono = $_POST['rep_telefono'] ?? '';
    $rep_email = $_POST['rep_email'] ?? '';
    $rep_parentesco = $_POST['rep_parentesco'] ?? '';

    // Variables por defecto para escolaridad del menor (no se piden en el modal rápido)
    $nivel_instruccion = NULL;
    $mision = NULL;
    $anos_aprobados = "0";

    // 1. Validar que la cédula del menor no exista
    $stmt_check = $conexion->prepare("SELECT Id FROM persona WHERE cedula = ? AND tipo_cedula = ?");
    $stmt_check->bind_param("ss", $menor_cedula, $menor_tipo_cedula);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => "El documento del menor ($menor_tipo_cedula-$menor_cedula) ya está registrado."]);
        exit;
    }
    $stmt_check->close();

    $conexion->begin_transaction();

    try {
        // 2. Gestionar al Representante (Upsert)
        // 2. Gestionar al Representante (Upsert)
        $id_representante = 0;
        $stmt_rep_check = $conexion->prepare("SELECT Id FROM persona WHERE cedula = ? AND tipo_cedula = ?");
        $stmt_rep_check->bind_param("ss", $rep_cedula, $rep_tipo_cedula);
        $stmt_rep_check->execute();
        $res_rep = $stmt_rep_check->get_result();

        if ($res_rep->num_rows > 0) {
            // Ya existe, Actualizamos sus datos principales
            $id_representante = $res_rep->fetch_assoc()['Id'];
            $stmt_update_rep = $conexion->prepare("UPDATE persona SET nombre=?, apellido=?, email=?, genero=?, fecha_nacimiento=? WHERE Id=?");
            $stmt_update_rep->bind_param("sssssi", $rep_nombre, $rep_apellido, $rep_email, $rep_genero, $rep_fecha_nac, $id_representante);
            $stmt_update_rep->execute();

            // Actualizar o Insertar Teléfono (Eliminamos el anterior y ponemos el nuevo)
            $conexion->query("DELETE FROM telefonos_personas WHERE Id_persona = $id_representante");
            $stmt_tel = $conexion->prepare("INSERT INTO telefonos_personas (Id_prefijo, telefono, Id_persona, estatus) VALUES (?, ?, ?, ?)");
            $stmt_tel->bind_param("sisi", $rep_prefijo, $rep_telefono, $id_representante, $estado);
            $stmt_tel->execute();

            // Garantizar que tenga el rol de representante
            $check_rol = $conexion->query("SELECT Id_detalle_persona_rol FROM detalle_persona_rol WHERE Id_persona = $id_representante AND Id_rol = $rol_representante");
            if($check_rol->num_rows == 0){
                $conexion->query("INSERT INTO detalle_persona_rol (Id_persona, Id_rol, estatus) VALUES ($id_representante, $rol_representante, $estado)");
            }

        } else {
            // No existe, Insertarlo
            $stmt_rep_in = $conexion->prepare("INSERT INTO persona (nombre, apellido, tipo_cedula, cedula, email, genero, fecha_nacimiento, estatus) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_rep_in->bind_param("sssssssi", $rep_nombre, $rep_apellido, $rep_tipo_cedula, $rep_cedula, $rep_email, $rep_genero, $rep_fecha_nac, $estado);
            $stmt_rep_in->execute();
            $id_representante = $conexion->insert_id;
            
            // Asignar Rol de representante
            $conexion->query("INSERT INTO detalle_persona_rol (Id_persona, Id_rol, estatus) VALUES ($id_representante, $rol_representante, $estado)");
            
            // Insertar Teléfono
            $stmt_tel = $conexion->prepare("INSERT INTO telefonos_personas (Id_prefijo, telefono, Id_persona, estatus) VALUES (?, ?, ?, ?)");
            $stmt_tel->bind_param("sisi", $rep_prefijo, $rep_telefono, $id_representante, $estado);
            $stmt_tel->execute();
        }

        // 3. Insertar al Menor (Paciente)
        $stmt_menor_in = $conexion->prepare("INSERT INTO persona (nombre, apellido, tipo_cedula, cedula, fecha_nacimiento, genero, estatus) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_menor_in->bind_param("ssssssi", $menor_nombre, $menor_apellido, $menor_tipo_cedula, $menor_cedula, $menor_fecha_nac, $menor_genero, $estado);
        $stmt_menor_in->execute();
        $id_menor = $conexion->insert_id;

        // Rol de paciente
        $conexion->query("INSERT INTO detalle_persona_rol (Id_persona, Id_rol, estatus) VALUES ($id_menor, $rol_paciente, $estado)");

        // 4. Detalle del menor (Vínculo completo con representante y GUARDADO COMO EXTERNO)
        $tipo_paciente = 'Externo'; 
        $stmt_detalle = $conexion->prepare("INSERT INTO detalle_paciente_menor (parentesco, analfabeta, etnia, tipo_etnia, nivel_instruccion, mision, años_aprobados, discapacidad, tipo_discapacidad, tipo_paciente, id_persona, id_representante) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_detalle->bind_param(
            "ssssssssssii", 
            $rep_parentesco, 
            $menor_analfabeta, 
            $menor_etnia, 
            $menor_tipo_etnia, 
            $nivel_instruccion, 
            $mision, 
            $anos_aprobados, 
            $menor_discapacidad, 
            $menor_tipo_discapacidad, 
            $tipo_paciente, 
            $id_menor, 
            $id_representante
        );
        $stmt_detalle->execute();

        // 5. Historial médico (Grupo Sanguíneo)
        if(!empty($menor_sangre)){
            $fecha_actual = date("Y-m-d H:i:s");
            $stmt_hist = $conexion->prepare("INSERT INTO historial_medico (fecha, Id_persona, grupo_sanguineo) VALUES (?, ?, ?)");
            $stmt_hist->bind_param("sis", $fecha_actual, $id_menor, $menor_sangre);
            $stmt_hist->execute();
        }

        $conexion->commit();

        echo json_encode([
            'success' => true, 
            'cedula' => $menor_cedula, 
            'tipo_ced' => $menor_tipo_cedula,
            'nombre_completo' => $menor_nombre . " " . $menor_apellido
        ]);

    } catch (Exception $e) {
        $conexion->rollback();
        echo json_encode(['success' => false, 'error' => 'Error al guardar en la base de datos: ' . $e->getMessage()]);
    }
}
?>