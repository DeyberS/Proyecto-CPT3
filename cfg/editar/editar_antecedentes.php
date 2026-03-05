<?php
include("../conexion.php");
header('Content-Type: application/json');

$cedula = $_POST['cedula'] ?? '';
$response = ['success' => false, 'error' => ''];

try {
    // 1. Obtener ID Historial y la edad del paciente (para validar en servidor)
    $sql_p = "SELECT h.id_historial, p.fecha_nacimiento 
              FROM historial_medico h 
              JOIN persona p ON h.Id_persona = p.id 
              WHERE p.cedula = '$cedula' LIMIT 1";
    $res_p = $conexion->query($sql_p);
    
    if ($res_p && $res_p->num_rows > 0) {
        $row_p = $res_p->fetch_assoc();
        $id_hist = $row_p['id_historial'];
        
        // Calcular edad en el servidor para mayor seguridad
        $nacimiento = new DateTime($row_p['fecha_nacimiento']);
        $hoy = new DateTime();
        $edad = $hoy->diff($nacimiento)->y;

        // 2. Definir qué vamos a procesar
        $secciones = [
            'familiares'  => ['historial_antecedentes_familiares', 'antecedentes_familiares', 'Id_antecedente'],
            'sexualidad'  => ['historial_antecedentes_sexuales_reproductivos', 'antecedentes_sexuales_reproductivos', 'Id_antecedente']
        ];

        // REGLA: Solo procesar PERINATALES si es menor de 18 años
        if ($edad < 18) {
            $secciones['perinatales'] = ['historial_antecedentes_perinatales', 'antecedentes_perinatales', 'Id_antecedente'];
        }

        foreach ($secciones as $post_key => $tablas) {
            if (!isset($_POST[$post_key])) continue;

            $texto = mysqli_real_escape_string($conexion, $_POST[$post_key]);
            $t_rel = $tablas[0];
            $t_mae = $tablas[1];
            $fk    = $tablas[2];

            $check = $conexion->query("SELECT $fk FROM $t_rel WHERE Id_Historial = $id_hist LIMIT 1");

            if ($check && $check->num_rows > 0) {
                $id_ant = $check->fetch_assoc()[$fk];
                $conexion->query("UPDATE $t_mae SET descripcion = '$texto' WHERE Id = $id_ant");
            } else {
                // Solo insertar si hay contenido
                if (!empty($texto)) {
                    $conexion->query("INSERT INTO $t_mae (descripcion, estatus) VALUES ('$texto', '1')");
                    $nuevo_id = $conexion->insert_id;
                    $conexion->query("INSERT INTO $t_rel (Id_Historial, $fk, estatus) VALUES ($id_hist, $nuevo_id, '1')");
                }
            }
        }

        // REGLA PARA ESTILOS DE VIDA: 
        // Si ya tiene registros, solo actualizar si el campo vino en el POST y no está vacío
        if (isset($_POST['estilo_vida']) && !empty($_POST['estilo_vida'])) {
            $estilo_txt = mysqli_real_escape_string($conexion, $_POST['estilo_vida']);
            $check_e = $conexion->query("SELECT Id_tipo FROM estilos_de_vida_paciente WHERE Id_Historial = $id_hist LIMIT 1");
            
            if ($check_e && $check_e->num_rows > 0) {
                $id_t = $check_e->fetch_assoc()['Id_tipo'];
                $conexion->query("UPDATE tipos_estilos_de_vida SET descripcion = '$estilo_txt' WHERE Id = $id_t");
            } else {
                $conexion->query("INSERT INTO tipos_estilos_de_vida (descripcion, estatus) VALUES ('$estilo_txt', '1')");
                $n_id = $conexion->insert_id;
                $conexion->query("INSERT INTO estilos_de_vida_paciente (Id_Historial, Id_tipo, estatus) VALUES ($id_hist, $n_id, '1')");
            }
        }

        $response['success'] = true;
    } else {
        $response['error'] = "Historial no encontrado.";
    }
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
