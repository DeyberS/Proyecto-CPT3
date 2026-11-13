<?php
include '../conexion.php';

$cedula = $_POST['cedula'];
$query_paciente = "SELECT *, FLOOR(DATEDIFF(CURDATE(), fecha_nacimiento) / 365.25) as edad 
                   FROM persona WHERE cedula = '$cedula'";
$res_paciente = mysqli_query($conexion, $query_paciente);
$paciente = mysqli_fetch_assoc($res_paciente);

$respuesta = [
    'tiene_alertas'          => false,
    'es_menor'               => false,
    'html_contenido'         => '',
    'nombre_representante'   => '',
    'parentesco'             => '',
    'nombre_paciente'        => '',
    'num_discapacidades'     => 0,
    'grupo_sanguineo'        => '',   // ← NUEVO
    'alergias'               => [],
    'patologias'             => [],
    'discapacidades'         => []
];

if ($paciente) {
    $id_persona = $paciente['id'];
    $respuesta['nombre_paciente'] = trim($paciente['nombre'] . ' ' . $paciente['apellido']);

    $html = "";

    /* ============================================================
       GRUPO SANGUÍNEO (fuente única: historial_medico)
       ============================================================ */
    $grupo_sanguineo = '';
    $q_gs = "SELECT grupo_sanguineo FROM historial_medico 
             WHERE Id_persona = '$id_persona' AND estatus = 1
             ORDER BY id_historial DESC LIMIT 1";
    $res_gs = mysqli_query($conexion, $q_gs);
    if ($res_gs && $row_gs = mysqli_fetch_assoc($res_gs)) {
        $grupo_sanguineo = trim($row_gs['grupo_sanguineo'] ?? '');
    }
    $respuesta['grupo_sanguineo'] = $grupo_sanguineo;

    if ($grupo_sanguineo !== '') {
        $gs_safe = htmlspecialchars($grupo_sanguineo);
        $html_gs = "
        <div style='display:flex; align-items:center; gap:12px; padding:12px 15px;
                    margin-bottom:15px; border-radius:6px;
                    background:#fdecea; border-left:5px solid #c0392b;'>
            <i><img src='../../recursos/imagenes/iconos/filled/specialties/hematology@2x.png'
                    style='height:50px; width:50px;'></i>
            <div>
                <div style='font-size:11px; color:#7f1d1d; text-transform:uppercase;
                            letter-spacing:0.5px; font-weight:600;'>Grupo Sanguíneo</div>
                <div style='font-size:22px; font-weight:bold; color:#c0392b; line-height:1.1;'>
                    {$gs_safe}
                </div>
            </div>
        </div>";
    } else {
        $html_gs = "
        <div style='display:flex; align-items:center; gap:12px; padding:12px 15px;
                    margin-bottom:15px; border-radius:6px;
                    background:#f4f4f4; border-left:5px solid #bbb; color:#777;'>
            <i><img src='../../recursos/imagenes/iconos/filled/symbols/alert_triangle@2x.png'
                    style='height:25px; width:25px; opacity:0.4;'></i>
            <div>
                <div style='font-size:11px; text-transform:uppercase; letter-spacing:0.5px;'>
                    Grupo Sanguíneo
                </div>
                <div style='font-size:15px; font-style:italic;'>
                    No registrado en la ficha del paciente
                </div>
            </div>
        </div>";
    }

    if ($paciente['edad'] < 18) {
        $respuesta['es_menor'] = true;
        $html .= "<div class='alert alert-warning'><i><img src='../../recursos/imagenes/iconos/filled/devices/wheelchair@2x.png' style='height:25px; width:25px;'> </i> Paciente Menor de Edad</b> (" . $paciente['edad'] . " años)</div>";
    }

    $q_rep = "SELECT hr.parentesco, p.nombre, p.apellido
    FROM historial_representantes hr
    JOIN persona p ON hr.Id_representante = p.id
    WHERE hr.Id_paciente = '$id_persona'
    AND hr.tipo_representante = 'Legal'
    AND hr.estatus = '1'
    ORDER BY (hr.parentesco IS NOT NULL AND TRIM(hr.parentesco) <> '') DESC, hr.Id DESC
    LIMIT 1";
    $res_rep = mysqli_query($conexion, $q_rep);
    if ($r = mysqli_fetch_assoc($res_rep)) {
        $respuesta['nombre_representante'] = trim($r['nombre'] . ' ' . $r['apellido']);
        $respuesta['parentesco'] = $r['parentesco'] ? $r['parentesco'] : '';
    }

    $q_alergias = "
    SELECT a.nombre_alergia
    FROM historial_alergias h
    JOIN historial_medico hm ON h.Id_Historial = hm.id_historial
    JOIN alergias_conocidas a ON h.Id_alergia = a.Id_alergias_conocidas
    WHERE hm.Id_persona = '$id_persona'
    AND h.estatus = 1
    ";
    $res_alergias = mysqli_query($conexion, $q_alergias);

    if (mysqli_num_rows($res_alergias) > 0) {
        $respuesta['tiene_alertas'] = true;
        $html .= "<h4 class='text'><i><img src='../../recursos/imagenes/iconos/filled/ppe/ppe_face_mask@2x.png' style='height:25px; width:25px;'> </i>Alergias Detectadas:</h4><ul>";
        while ($al = mysqli_fetch_assoc($res_alergias)) {
            $html .= "<li class='text'>" . htmlspecialchars($al['nombre_alergia']) . "</li>";
            $respuesta['alergias'][] = $al['nombre_alergia'];
        }
        $html .= "</ul>";
    }

    $q_patos = "
    SELECT p.nombre_patologia
    FROM historial_patologias h
    JOIN historial_medico hm ON h.Id_Historial = hm.id_historial
    JOIN patologias p ON h.Id_patologia = p.Id_patologia
    WHERE hm.Id_persona = '$id_persona'
    AND h.estatus = 1
    ";
    $res_patos = mysqli_query($conexion, $q_patos);

    if (mysqli_num_rows($res_patos) > 0) {
        $respuesta['tiene_alertas'] = true;
        $html .= "<h4 class='text'><i><img src='../../recursos/imagenes/iconos/filled/symbols/virus-research@2x.png' style='height:25px; width:25px;'> </i>Patologías Previas:</h4><ul>";
        while ($pa = mysqli_fetch_assoc($res_patos)) {
            $html .= "<li class='text'>" . htmlspecialchars($pa['nombre_patologia']) . "</li>";
            $respuesta['patologias'][] = $pa['nombre_patologia'];
        }
        $html .= "</ul>";
    }

    // -------- Discapacidades --------
    $q_disc = "
    SELECT d.nombre_discapacidad
    FROM historial_discapacidades hd
    JOIN historial_medico hm ON hd.Id_Historial = hm.id_historial
    JOIN discapacidades d ON hd.Id_discapacidad = d.Id_discapacidad
    WHERE hm.Id_persona = '$id_persona'
    AND hd.estatus = 1
    ";
    $res_disc = mysqli_query($conexion, $q_disc);
    $num_disc = $res_disc ? mysqli_num_rows($res_disc) : 0;
    $respuesta['num_discapacidades'] = $num_disc;

    if ($num_disc > 0) {
        $respuesta['tiene_alertas'] = true;
        $html .= "<h4 class='text'><i><img src='../../recursos/imagenes/iconos/filled/devices/wheelchair@2x.png' style='height:25px; width:25px;'> </i>Discapacidades Registradas:</h4><ul>";
        while ($dc = mysqli_fetch_assoc($res_disc)) {
            $html .= "<li class='text'>" . htmlspecialchars($dc['nombre_discapacidad']) . "</li>";
            $respuesta['discapacidades'][] = $dc['nombre_discapacidad'];
        }
        $html .= "</ul>";
    }

    // ← CLAVE: anteponemos el bloque del grupo sanguíneo
    $respuesta['html_contenido'] = $html_gs . $html;
}

echo json_encode($respuesta);