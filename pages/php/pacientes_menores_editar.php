<?php
if (!isset($_GET['Id']) || empty($_GET['Id'])) {
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }
  $_SESSION['mensaje_user_error'] = "Debe seleccionar una consulta válida para ver su información.";
  header("Location: pacientes_menores_listado.php");
  exit();
}

$conexion = new mysqli("localhost", "root", "", "cpt3db");
$conexion->set_charset("utf8");

if (isset($_POST['ajax_nueva_patologia'])) {
  $nombre = $conexion->real_escape_string($_POST['nombre_patologia']);
  $cie = $conexion->real_escape_string($_POST['codigo_cie']);
  $contagiosa = $conexion->real_escape_string($_POST['enfermedad_contagiosa']);

  $verificar = $conexion->query("SELECT Id_patologia FROM patologias WHERE nombre_patologia = '$nombre'");
  if ($verificar->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Ya existe una patología registrada con el nombre: <b>' . $nombre . '</b>']);
    exit;
  }
  $conexion->query("INSERT INTO patologias (nombre_patologia, codigo_cie, estatus, contagioso) VALUES ('$nombre', '$cie', 1, '$contagiosa')");
  $id_pat = $conexion->insert_id;

  if (isset($_POST['sintomas_ids']) && !empty($_POST['sintomas_ids'])) {
    $sintomas = explode(',', $_POST['sintomas_ids']);
    foreach ($sintomas as $id_sintoma) {
      if (trim($id_sintoma) !== "") {
        $id_sin = $conexion->real_escape_string($id_sintoma);
        $conexion->query("INSERT INTO detalle_patologia_sintomas (Id_patologia, Id_sintoma) VALUES ('$id_pat', '$id_sin')");
      }
    }
  }
  echo json_encode(['success' => true, 'id' => $id_pat, 'nombre' => $nombre, 'cie' => $cie]);
  exit;
}

if (isset($_POST['ajax_nueva_alergia'])) {
  $nombre = $conexion->real_escape_string($_POST['nombre_alergia']);
  $verificar = $conexion->query("SELECT Id_alergias_conocidas FROM alergias_conocidas WHERE nombre_alergia = '$nombre'");
  if ($verificar->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Ya existe una alergia registrada con el nombre: <b>' . $nombre . '</b>']);
    exit;
  }
  $conexion->query("INSERT INTO alergias_conocidas (nombre_alergia, estatus) VALUES ('$nombre', 1)");
  echo json_encode(['success' => true, 'id' => $conexion->insert_id, 'nombre' => $nombre]);
  exit;
}

if (isset($_POST['ajax_nuevo_sintoma'])) {
  $nombre = $conexion->real_escape_string($_POST['nombre_sintoma']);
  $verificar = $conexion->query("SELECT Id_sintomas FROM sintomas WHERE nombre_sintoma = '$nombre'");
  if ($verificar->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Ya existe un síntoma registrado con el nombre: <b>' . $nombre . '</b>']);
    exit;
  }
  $conexion->query("INSERT INTO sintomas (nombre_sintoma, estatus) VALUES ('$nombre', 1)");
  echo json_encode(['success' => true, 'id' => $conexion->insert_id, 'nombre' => $nombre]);
  exit;
}

if (isset($_POST['ajax_nueva_discapacidad'])) {
  $nombre = $conexion->real_escape_string($_POST['nombre_discapacidad']);
  $verificar = $conexion->query("SELECT Id_discapacidad FROM discapacidades WHERE nombre_discapacidad = '$nombre'");
  if ($verificar->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Ya existe una discapacidad registrada con el nombre: <b>' . $nombre . '</b>']);
    exit;
  }
  $conexion->query("INSERT INTO discapacidades (nombre_discapacidad, estatus) VALUES ('$nombre', 1)");
  echo json_encode(['success' => true, 'id' => $conexion->insert_id, 'nombre' => $nombre]);
  exit;
}

$opciones_sintomas_global = "";
$sql_sg = "SELECT Id_sintomas, nombre_sintoma FROM sintomas WHERE estatus = 1 ORDER BY nombre_sintoma ASC";
$res_sg = $conexion->query($sql_sg);
if ($res_sg) {
  while ($row_s = $res_sg->fetch_assoc()) {
    $id_s = $row_s['Id_sintomas'];
    $nombre_s = htmlspecialchars($row_s['nombre_sintoma']);
    $opciones_sintomas_global .= "<option value='$id_s'>$nombre_s</option>";
  }
}

include('includes/headerNav2.php');
verificarAcceso('Editar Pacientes Menores de Edad');
?>

<head>
  <title>Pacientes Menores | Editar</title>
</head>

<div class="content-wrapper">
  <style>
    #avisoModal {
      z-index: 999999 !important;
    }

    .nav-tabs>li>a {
      pointer-events: none;
      cursor: default;
    }

    .nav-tabs li.disabled-tab a {
      color: #b2b2b2 !important;
    }
  </style>

  <section class="content-header">
    <h1>Editar Paciente Menor de Edad</h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="#"><i class="fa fa-users"></i>Pacientes Menores</a></li>
      <li class="active"><a href="#"><i class="fa fa-user-plus"></i>Editar</a></li>
    </ol>
  </section>

  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="nav-tabs-custom">
          <ul class="nav nav-tabs">
            <li class="active" data-tab-name="info"><a href="#info" data-toggle="tab">Datos Personales</a></li>
            <li data-tab-name="ocupacion_estudios" class="disabled-tab"><a href="#ocupacion_estudios" data-toggle="tab">Ocupación Y Estudios Aprobados</a></li>
            <li data-tab-name="direccion" class="disabled-tab"><a href="#direccion" data-toggle="tab">Dirección de Residencia</a></li>
            <li data-tab-name="salud_otros" class="disabled-tab"><a href="#salud_otros" data-toggle="tab">Salud y Otros Datos</a></li>
          </ul>

          <form id="formularioPaciente" action="../../cfg/editar/editar_paciente_menor.php" class="form-group" method="POST" novalidate>

            <div class="tab-content">
              <div class="tab-pane active" id="info">
                <?php
                include("../../cfg/conexion.php");
                $id_paciente_actual = intval($_GET['Id']);

                $sql = "SELECT 
                  p.id, p.email, p.nombre, p.apellido, p.tipo_cedula, p.cedula, p.fecha_nacimiento, p.genero,
                  dp.etnia, dp.tipo_etnia, dp.analfabeta, dp.nivel_instruccion, dp.mision, dp.años_aprobados,
                  hm.grupo_sanguineo,
                  d.avenida_calle, d.referencia, d.tiempo_residencia, d.tiempo, d.Id_sector,
                  ln.Id_municipio AS Id_Municipio_Nac,
                  munnac.Id_Estado AS Id_Estado_Nac,
                  estnac.Id_Pais AS Id_Pais_Nac,
                  dirsec.Id_Municipio AS Id_Municipio_Dir,
                  dirmun.Id_Estado AS Id_Estado_Dir,
                  rep.id AS id_representante,
                  rep.nombre AS nombre_representante,
                  rep.apellido AS apellido_representante,
                  rep.genero AS genero_representante,
                  rep.email AS email_representante,
                  rep.tipo_cedula AS tipo_cedula_representante,
                  rep.cedula AS cedula_representante,
                  rep.fecha_nacimiento AS fecha_nacimiento_representante,
                  trp.telefono AS telefono_representante,
                  ptrp.Id AS id_prefijo_representante,
                  GROUP_CONCAT(DISTINCT CONCAT(pat.Id_patologia, '::', pat.nombre_patologia, '::', pat.codigo_cie, '::', hp.fecha_registro) SEPARATOR '||') AS patologias_data,
                  GROUP_CONCAT(DISTINCT CONCAT(al.Id_alergias_conocidas, '::', al.nombre_alergia, '::', ha.fecha_registro) SEPARATOR '||') AS alergias_data
                FROM persona p
                LEFT JOIN detalle_paciente_menor dp ON p.id = dp.id_persona
                LEFT JOIN historial_medico hm ON p.id = hm.Id_persona
                LEFT JOIN lugar_nacimiento ln ON p.id = ln.Id_persona
                LEFT JOIN municipio munnac ON ln.Id_municipio = munnac.Id_Municipio
                LEFT JOIN estado estnac ON munnac.Id_Estado = estnac.Id_Estado
                LEFT JOIN direccion d ON p.id = d.Id_persona
                LEFT JOIN sector dirsec ON d.Id_sector = dirsec.Id_Sector
                LEFT JOIN municipio dirmun ON dirsec.Id_Municipio = dirmun.Id_Municipio
                LEFT JOIN historial_representantes hr ON hr.Id = (
                    SELECT hr2.Id
                    FROM historial_representantes hr2
                    WHERE hr2.Id_paciente = p.id
                    ORDER BY (hr2.tipo_representante = 'Legal') DESC, hr2.Id ASC
                    LIMIT 1
                )
                LEFT JOIN persona rep ON rep.id = hr.Id_representante
                LEFT JOIN telefonos_personas trp ON rep.id = trp.Id_persona AND trp.estatus = '1'
                LEFT JOIN prefijos_telefonos ptrp ON trp.Id_prefijo = ptrp.Id
                LEFT JOIN historial_patologias hp ON hm.id_historial = hp.Id_Historial
                LEFT JOIN patologias pat ON hp.Id_patologia = pat.Id_patologia
                LEFT JOIN historial_alergias ha ON hm.id_historial = ha.Id_Historial
                LEFT JOIN alergias_conocidas al ON ha.Id_alergia = al.Id_alergias_conocidas
                WHERE p.id = $id_paciente_actual
                GROUP BY p.id";

                $resultado = $conexion->query($sql);

                if (!$resultado) {
                  die("<pre style='color:red;padding:15px;background:#fff;'>Error SQL al cargar paciente: "
                    . htmlspecialchars($conexion->error) . "</pre>");
                }
                if ($resultado->num_rows === 0) {
                  die("<pre style='color:orange;padding:15px;background:#fff;'>No se encontró el paciente con ID "
                    . intval($id_paciente_actual) . ".</pre>");
                }
                $row = $resultado->fetch_assoc();
                $id_paciente = $row['id'];

                $discapacidades_json = '[]';
                $sql_disc = "SELECT hd.Id_discapacidad, d.nombre_discapacidad, hd.fecha_registro 
                FROM historial_discapacidades hd
                INNER JOIN discapacidades d ON hd.Id_discapacidad = d.Id_discapacidad
                INNER JOIN historial_medico hm ON hd.Id_Historial = hm.id_historial
                WHERE hm.Id_persona = $id_paciente AND hd.estatus = '1'";
                $res_disc = $conexion->query($sql_disc);
                if ($res_disc && $res_disc->num_rows > 0) {
                  $arr_disc = [];
                  while ($d = $res_disc->fetch_assoc()) {
                    $arr_disc[] = [
                      'id' => intval($d['Id_discapacidad']),
                      'nombre' => $d['nombre_discapacidad'],
                      'fecha' => $d['fecha_registro'] ?? ''
                    ];
                  }
                  $discapacidades_json = json_encode($arr_disc);
                }

                $id_prefijo_rep = $row['id_prefijo_representante'] ?? '';
                $id_pais_nac = $row['Id_Pais_Nac'] ?? '';
                $id_estado_nac = $row['Id_Estado_Nac'] ?? '';
                $id_municipio_nac = $row['Id_Municipio_Nac'] ?? '';
                $id_estado_dir = $row['Id_Estado_Dir'] ?? '';
                $id_municipio_dir = $row['Id_Municipio_Dir'] ?? '';
                $id_sector_dir = $row['Id_sector'] ?? '';
                $patologias_data = $row['patologias_data'] ?? '';
                $alergias_data = $row['alergias_data'] ?? '';
                $id_representante_actual = $row['id_representante'] ?? '';
                $tiene_discapacidad = (count(json_decode($discapacidades_json, true)) > 0) ? 'Si' : 'No';
                ?>

                <input type="hidden" name="Id" value="<?= $id_paciente; ?>">
                <input type="hidden" name="Id_representante" value="<?= $id_representante_actual; ?>">

                <section id="new" style="margin-bottom:6%;">
                  <div class="row">
                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Cédula / Documento (*):</p>
                      <div class="input-group">
                        <span class="input-group-addon" style="padding: 0; border: none;">
                          <select name="tipo_cedula" id="tipo_cedula" class="form-control" style="width: 75px; height: 34px; font-weight: bold; background-color: #f4f4f4; border-right: none; border-top-right-radius: 0; border-bottom-right-radius: 0;" required>
                            <option value="PN" <?php echo ($row['tipo_cedula'] == 'PN' ? 'selected' : ''); ?>>PN-</option>
                            <option value="V" <?php echo ($row['tipo_cedula'] == 'V' ? 'selected' : ''); ?>>V-</option>
                            <option value="RP" <?php echo ($row['tipo_cedula'] == 'RP' ? 'selected' : ''); ?>>REP-</option>
                          </select>
                        </span>
                        <input type="text" class="form-control" name="cedula" value="<?php echo $row['cedula']; ?>" id="cedula" placeholder="N° de documento" required>
                      </div>
                    </div>
                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Nombre (*):</p>
                      <input type="text" class="form-control" name="nombre" value="<?php echo $row['nombre']; ?>" oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]/g, '');" placeholder="Nombre Del Paciente" maxlength="100" required>
                    </div>
                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Apellido:</p>
                      <input type="text" class="form-control" name="apellido" value="<?php echo $row['apellido']; ?>" oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]/g, '');" placeholder="Apellido Del Paciente" maxlength="100">
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-4 col-sm-5 form-group">
                      <p>Fecha de nacimiento / Edad (*):</p>
                      <div class="input-group">
                        <input type="date" class="form-control" value="<?php echo $row['fecha_nacimiento']; ?>" id="fechaN" name="fecha_nacimiento" onchange="calcularEdad()" required style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                        <span class="input-group-addon" style="padding: 0; border: none;">
                          <input type="text" class="form-control" id="edad" name="edad" readonly placeholder="Edad" style="width: 120px; height: 34px; text-align: center; background-color: #f4f4f4; font-weight: bold; border-left: none; border-top-left-radius: 0; border-bottom-left-radius: 0;">
                        </span>
                      </div>
                    </div>
                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Sexo (*):</p>
                      <select name="genero" id="genero" class="form-control" required>
                        <option value="">--- Seleccione Un Género ---</option>
                        <option value="Masculino" <?php echo ($row['genero'] == 'Masculino' ? 'selected' : ''); ?>>Masculino</option>
                        <option value="Femenino" <?php echo ($row['genero'] == 'Femenino' ? 'selected' : ''); ?>>Femenino</option>
                      </select>
                    </div>
                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Analfabeta (*):</p>
                      <select name="analfabeta" id="analfabeta" class="form-control" required>
                        <option value="">--- Seleccione Una Opción ---</option>
                        <option value="Si" <?php echo ($row['analfabeta'] == 'Si' ? 'selected' : ''); ?>>Si</option>
                        <option value="No" <?php echo ($row['analfabeta'] == 'No' ? 'selected' : ''); ?>>No</option>
                      </select>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-4 col-sm-4 form-group">
                      <p>País de nacimiento (*):</p>
                      <select name="pais_nacimiento" id="pais_nacimiento" class="form-control" required>
                        <option value="">--- Seleccione un País ---</option>
                        <?php
                        $result_nac = $conexion->query("SELECT Id_Pais, nombre_pais FROM pais");
                        while ($row_list = $result_nac->fetch_assoc()) {
                          $selected = ($row_list['Id_Pais'] == $id_pais_nac) ? 'selected' : '';
                          echo "<option value='{$row_list['Id_Pais']}' {$selected}>{$row_list['nombre_pais']}</option>";
                        }
                        ?>
                      </select>
                    </div>
                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Estado de nacimiento (*):</p>
                      <select name="estado_nacimiento" id="estado_nacimiento" class="form-control" required>
                        <option value="">--- Seleccione Un Estado ---</option>
                        <?php
                        if ($id_pais_nac) {
                          $res_est_nac = $conexion->query("SELECT Id_Estado, nombre_estado FROM estado WHERE Id_Pais = $id_pais_nac");
                          while ($row_est_nac = $res_est_nac->fetch_assoc()) {
                            $selected = ($row_est_nac['Id_Estado'] == $id_estado_nac) ? 'selected' : '';
                            echo "<option value='{$row_est_nac['Id_Estado']}' {$selected}>{$row_est_nac['nombre_estado']}</option>";
                          }
                        }
                        ?>
                      </select>
                    </div>
                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Municipio de nacimiento (*):</p>
                      <select name="municipio_nacimiento" id="municipio_nacimiento" class="form-control" required>
                        <option value="">--- Seleccione Un Municipio ---</option>
                        <?php
                        if ($id_estado_nac) {
                          $res_mun_nac = $conexion->query("SELECT Id_Municipio, nombre_municipio FROM municipio WHERE Id_Estado = $id_estado_nac");
                          while ($row_mun_nac = $res_mun_nac->fetch_assoc()) {
                            $selected = ($row_mun_nac['Id_Municipio'] == $id_municipio_nac) ? 'selected' : '';
                            echo "<option value='{$row_mun_nac['Id_Municipio']}' {$selected}>{$row_mun_nac['nombre_municipio']}</option>";
                          }
                        }
                        ?>
                      </select>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Etnia:</p>
                      <div class="input-group">
                        <span class="input-group-addon" style="padding: 0; border: none;">
                          <select name="etnia" id="etnia" class="form-control" style="width: 70px; height: 34px; font-weight: bold; background-color: #f4f4f4; border-right: none; border-top-right-radius: 0; border-bottom-right-radius: 0;">
                            <option value="No" <?php echo ($row['etnia'] == 'No' ? 'selected' : ''); ?>>No</option>
                            <option value="Si" <?php echo ($row['etnia'] == 'Si' ? 'selected' : ''); ?>>Si</option>
                          </select>
                        </span>
                        <select name="tipo_etnia" id="tipo_etnia" class="form-control" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                          <option value="">--- Seleccione Una Etnia ---</option>
                          <option value="wayuu" <?php echo ($row['tipo_etnia'] == 'wayuu' ? 'selected' : ''); ?>>Wayuu</option>
                          <option value="añu" <?php echo ($row['tipo_etnia'] == 'añu' ? 'selected' : ''); ?>>Añu</option>
                          <option value="baniva" <?php echo ($row['tipo_etnia'] == 'baniva' ? 'selected' : ''); ?>>Baniva</option>
                          <option value="kurripako" <?php echo ($row['tipo_etnia'] == 'kurripako' ? 'selected' : ''); ?>>Kurripako</option>
                          <option value="piapoco" <?php echo ($row['tipo_etnia'] == 'piapoco' ? 'selected' : ''); ?>>Piapoco</option>
                          <option value="warekena" <?php echo ($row['tipo_etnia'] == 'warekena' ? 'selected' : ''); ?>>Warekena</option>
                          <option value="bare" <?php echo ($row['tipo_etnia'] == 'bare' ? 'selected' : ''); ?>>Bare</option>
                          <option value="pemon" <?php echo ($row['tipo_etnia'] == 'pemon' ? 'selected' : ''); ?>>Pemon</option>
                          <option value="kariña" <?php echo ($row['tipo_etnia'] == 'kariña' ? 'selected' : ''); ?>>Kariña</option>
                          <option value="panare" <?php echo ($row['tipo_etnia'] == 'panare' ? 'selected' : ''); ?>>Panare</option>
                          <option value="yukpa" <?php echo ($row['tipo_etnia'] == 'yukpa' ? 'selected' : ''); ?>>Yukpa</option>
                          <option value="japreira" <?php echo ($row['tipo_etnia'] == 'japreira' ? 'selected' : ''); ?>>Japreira</option>
                          <option value="yekuana" <?php echo ($row['tipo_etnia'] == 'yekuana' ? 'selected' : ''); ?>>Yekuana</option>
                          <option value="chaima" <?php echo ($row['tipo_etnia'] == 'chaima' ? 'selected' : ''); ?>>Chaima</option>
                          <option value="bari" <?php echo ($row['tipo_etnia'] == 'bari' ? 'selected' : ''); ?>>Bari</option>
                          <option value="yanomami" <?php echo ($row['tipo_etnia'] == 'yanomami' ? 'selected' : ''); ?>>Yanomami</option>
                          <option value="sanema" <?php echo ($row['tipo_etnia'] == 'sanema' ? 'selected' : ''); ?>>Sanema</option>
                          <option value="warao" <?php echo ($row['tipo_etnia'] == 'warao' ? 'selected' : ''); ?>>Warao</option>
                          <option value="pume" <?php echo ($row['tipo_etnia'] == 'pume' ? 'selected' : ''); ?>>Pume</option>
                          <option value="piaroa" <?php echo ($row['tipo_etnia'] == 'piaroa' ? 'selected' : ''); ?>>Piaroa</option>
                          <option value="otro" <?php echo ($row['tipo_etnia'] == 'otro' ? 'selected' : ''); ?>>Otro/No Aplica</option>
                          <option value="ninguna" <?php echo ($row['tipo_etnia'] == 'ninguna' ? 'selected' : ''); ?>>Ninguna</option>
                        </select>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-sm-12" style="text-align: right; margin-top: 0px;">
                      <button type="button" class="btn btn-second" data-toggle="modal" data-target="#modalConfirmarRegreso">Regresar</button>
                      <button type="button" class="btn btn-primary next-tab" data-tab-actual="#info" data-tab-siguiente="ocupacion_estudios">Siguiente</button>
                    </div>
                  </div>
                </section>
              </div>

              <div class="tab-pane" id="ocupacion_estudios">
                <section id="new" style="margin-bottom:4%;">
                  <div class="row">
                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Nivel de instrucción:</p>
                      <select name="nivel_instruccion" id="nivel_instruccion" class="form-control">
                        <option value="">--- Seleccione Un Nivel De Instrucción ---</option>
                        <option value="sin_instruccion" <?php echo ($row['nivel_instruccion'] == 'sin_instruccion' ? 'selected' : ''); ?>>Sin Instrucción</option>
                        <option value="primaria_incompleta" <?php echo ($row['nivel_instruccion'] == 'primaria_incompleta' ? 'selected' : ''); ?>>Primaria Incompleta</option>
                        <option value="primaria_completa" <?php echo ($row['nivel_instruccion'] == 'primaria_completa' ? 'selected' : ''); ?>>Primaria Completa</option>
                        <option value="bachillerato_incompleto" <?php echo ($row['nivel_instruccion'] == 'bachillerato_incompleto' ? 'selected' : ''); ?>>Educación Media Incompleta</option>
                        <option value="bachiller" <?php echo ($row['nivel_instruccion'] == 'bachiller' ? 'selected' : ''); ?>>Bachiller (Media General)</option>
                      </select>
                    </div>
                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Misión educativa:</p>
                      <select name="mision" id="mision" class="form-control">
                        <option value="">--- Seleccione Una Misión ---</option>
                        <option value="robinson" <?php echo ($row['mision'] == 'robinson' ? 'selected' : ''); ?>>Robinson</option>
                        <option value="ribas" <?php echo ($row['mision'] == 'ribas' ? 'selected' : ''); ?>>Ribas</option>
                        <option value="sucre" <?php echo ($row['mision'] == 'sucre' ? 'selected' : ''); ?>>Sucre</option>
                      </select>
                    </div>
                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Años aprobados:</p>
                      <input type="number" id="años_aprobados" value="<?php echo $row['años_aprobados']; ?>" class="form-control" name="años_aprobados" min="0" max="10">
                      <p class="text-muted" id="años_help" style="margin-top: 5px;">Máximo:</p>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-sm-12" style="text-align: right; margin-top: 20px;">
                      <button type="button" class="btn btn-second prev-tab" data-tab-actual="#ocupacion_estudios" data-tab-anterior="info">Atras</button>
                      <button type="button" class="btn btn-primary next-tab" data-tab-actual="#ocupacion_estudios" data-tab-siguiente="direccion">Siguiente</button>
                    </div>
                  </div>
                </section>
              </div>

              <div class="tab-pane" id="direccion">
                <section id="new" style="margin-bottom: 3%;">
                  <div class="row">
                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Estado (*):</p>
                      <select name="estado" id="estado" class="form-control" required>
                        <option value="">--- Seleccione Un Estado ---</option>
                        <?php
                        $result_dir = $conexion->query("SELECT Id_Estado, nombre_estado FROM estado WHERE Id_Pais = 1");
                        while ($row_dir = $result_dir->fetch_assoc()) {
                          $selected = ($row_dir['Id_Estado'] == $id_estado_dir) ? 'selected' : '';
                          echo "<option value='{$row_dir['Id_Estado']}' {$selected}>{$row_dir['nombre_estado']}</option>";
                        }
                        ?>
                      </select>
                    </div>
                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Municipio (*):</p>
                      <select name="municipio" id="municipio" class="form-control" required>
                        <option value="">--- Seleccione Un Municipio ---</option>
                        <?php
                        if ($id_estado_dir) {
                          $res_mun_dir = $conexion->query("SELECT Id_Municipio, nombre_municipio FROM municipio WHERE Id_Estado = $id_estado_dir");
                          while ($row_mun_dir = $res_mun_dir->fetch_assoc()) {
                            $selected = ($row_mun_dir['Id_Municipio'] == $id_municipio_dir) ? 'selected' : '';
                            echo "<option value='{$row_mun_dir['Id_Municipio']}' {$selected}>{$row_mun_dir['nombre_municipio']}</option>";
                          }
                        }
                        ?>
                      </select>
                    </div>
                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Sector (*):</p>
                      <select name="sector" id="sector" class="form-control" required>
                        <option value="">--- Seleccione Un Sector ---</option>
                        <?php
                        if ($id_municipio_dir) {
                          $res_sec_dir = $conexion->query("SELECT Id_Sector, nombre_sector FROM sector WHERE Id_Municipio = $id_municipio_dir");
                          while ($row_sec_dir = $res_sec_dir->fetch_assoc()) {
                            $selected = ($row_sec_dir['Id_Sector'] == $id_sector_dir) ? 'selected' : '';
                            echo "<option value='{$row_sec_dir['Id_Sector']}' {$selected}>{$row_sec_dir['nombre_sector']}</option>";
                          }
                        }
                        ?>
                      </select>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-4 col-sm-6 form-group">
                      <p>Avenida/calle:</p>
                      <input type="text" class="form-control" name="avenida_calle" value="<?php echo $row['avenida_calle']; ?>">
                    </div>
                    <div class="col-md-4 col-sm-6 form-group">
                      <p>Punto de referencia:</p>
                      <input type="text" class="form-control" name="referencia" value="<?php echo $row['referencia']; ?>">
                    </div>
                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Tiempo de residencia:</p>
                      <div class="input-group">
                        <input type="text" class="form-control" name="tiempo_residencia" id="tiempo_residencia" value="<?php echo $row['tiempo_residencia']; ?>" placeholder="Cantidad" style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                        <span class="input-group-addon" style="padding: 0; border: none;">
                          <select name="tiempo" id="tiempo" class="form-control" style="width: 110px; height: 34px; background-color: #f4f4f4; border-left: none; border-top-left-radius: 0; border-bottom-left-radius: 0;">
                            <option value="dia/s" <?php echo ($row['tiempo'] == 'dia/s' ? 'selected' : ''); ?>>Dia/s</option>
                            <option value="semanas/s" <?php echo ($row['tiempo'] == 'semanas/s' ? 'selected' : ''); ?>>Semanas/s</option>
                            <option value="meses/s" <?php echo ($row['tiempo'] == 'meses/s' ? 'selected' : ''); ?>>Meses/s</option>
                            <option value="años/s" <?php echo ($row['tiempo'] == 'años/s' ? 'selected' : ''); ?>>Año/s</option>
                          </select>
                        </span>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-sm-12" style="text-align: right; margin-top: 0px;">
                      <button type="button" class="btn btn-second prev-tab" data-tab-actual="#direccion" data-tab-anterior="ocupacion_estudios">Atras</button>
                      <button type="button" class="btn btn-primary next-tab" data-tab-actual="#direccion" data-tab-siguiente="salud_otros">Siguiente</button>
                    </div>
                  </div>
                </section>
              </div>

              <div class="tab-pane" id="salud_otros">
                <section id="new" style="margin-bottom: 0%; padding-top: 15px;">
                  <div class="row">
                    <div class="col-sm-4 form-group" id="group_patologia">
                      <p>Patologías detectadas previamente:</p>
                      <button type="button" class="btn btn-info btn-block" id="btn_modal_pat" data-toggle="modal" data-placement="top" title="Ninguna seleccionada" data-target="#modalPatologias">
                        <i></i> Gestionar Patologías
                      </button>
                      <input type="hidden" name="patologias_data" id="patologias_ids" value="">
                    </div>
                    <div class="col-sm-4 form-group" id="group_alergia">
                      <p>Alergías detectadas previamente:</p>
                      <button type="button" class="btn btn-info btn-block" id="btn_modal_ale" data-toggle="modal" data-placement="top" title="Ninguna seleccionada" data-target="#modalAlergias">
                        <i></i> Gestionar Alergias
                      </button>
                      <input type="hidden" name="alergias_data" id="alergias_ids" value="">
                    </div>
                    <div class="col-sm-4 form-group" id="group_representante">
                      <p>Representante Legal / Responsable (*):</p>
                      <button type="button" class="btn btn-info btn-block" id="btn_modal_rep" data-toggle="modal" data-placement="top" title="Sin Representantes" data-target="#modalRepresentantes">
                        <i></i> Gestionar Representantes
                      </button>
                      <input type="hidden" name="representantes_data" id="representantes_ids" value="">
                    </div>
                  </div>

                  <div class="row" style="margin-bottom: 0px;">
                    <div class="col-sm-4 form-group" id="group_discapacidad">
                      <p>Tipo de discapacidad:</p>
                      <div style="display: flex; gap: 5px;">
                        <select name="discapacidad" id="discapacidad" class="form-control" style="width: 70px; flex-shrink: 0;" required>
                          <option value="No" <?php echo ($tiene_discapacidad === 'No' ? 'selected' : ''); ?>>No</option>
                          <option value="Si" <?php echo ($tiene_discapacidad === 'Si' ? 'selected' : ''); ?>>Si</option>
                        </select>
                        <button type="button" class="btn btn-info btn-block" id="btn_modal_disc" data-toggle="modal" data-placement="top" title="Ninguna seleccionada" data-target="#modalDiscapacidades" style="flex-grow: 1; margin: 0;">
                          <i></i> Gestionar Discapacidades
                        </button>
                      </div>
                      <input type="hidden" name="discapacidades_data" id="discapacidades_ids" value="">
                    </div>

                    <div class="col-sm-4 form-group">
                      <p>Grupo Sanguíneo (*):</p>
                      <select name="grupo_sanguineo" id="grupo_sanguineo" class="form-control" required>
                        <option value="">--- Seleccione ---</option>
                        <option value="A+" <?php echo ($row['grupo_sanguineo'] == 'A+' ? 'selected' : ''); ?>>A+</option>
                        <option value="A-" <?php echo ($row['grupo_sanguineo'] == 'A-' ? 'selected' : ''); ?>>A-</option>
                        <option value="B+" <?php echo ($row['grupo_sanguineo'] == 'B+' ? 'selected' : ''); ?>>B+</option>
                        <option value="B-" <?php echo ($row['grupo_sanguineo'] == 'B-' ? 'selected' : ''); ?>>B-</option>
                        <option value="AB+" <?php echo ($row['grupo_sanguineo'] == 'AB+' ? 'selected' : ''); ?>>AB+</option>
                        <option value="AB-" <?php echo ($row['grupo_sanguineo'] == 'AB-' ? 'selected' : ''); ?>>AB-</option>
                        <option value="O+" <?php echo ($row['grupo_sanguineo'] == 'O+' ? 'selected' : ''); ?>>O+</option>
                        <option value="O-" <?php echo ($row['grupo_sanguineo'] == 'O-' ? 'selected' : ''); ?>>O-</option>
                      </select>
                    </div>
                  </div>

                  <div style="clear: both; text-align: right; margin-top:0px;">
                    <button type="button" class="btn btn-second prev-tab" data-tab-actual="#salud_otros" data-tab-anterior="direccion">Atrás</button>
                    <button type="button" class="btn btn-success next-tab" data-tab-actual="#salud_otros" data-tab-siguiente="confirmar">Actualizar</button>
                  </div>
                </section>
              </div>
            </div>

            <div class="modal fade" id="modalPatologias" role="dialog">
              <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                  <div class="modal-header bg-primary">
                    <h4 class="modal-title"><i class="fa fa-medkit"></i> Gestionar Patologías</h4>
                  </div>
                  <div class="modal-body">
                    <div id="contenedor_filas_patologias"></div>
                    <button type="button" class="btn btn-success btn-sm" id="add_fila_pat">
                      <i class="fa fa-plus"></i> Añadir otra
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalNuevaPatologia">
                      <i class="fa fa-plus-circle"></i> Nueva Patología
                    </button>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-second" id="cancelar_pat_modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="guardar_pat_listo">Listo</button>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal fade" id="modalBuscarPat" role="dialog">
              <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                  <div class="modal-header bg-primary">
                    <h4 class="modal-title">Buscar Patología</h4>
                  </div>
                  <div class="modal-body">
                    <input type="text" id="inputBuscarPat" class="form-control" placeholder="Escriba para filtrar...">
                    <div class="list-group" id="listaResultadosPat" style="margin-top: 10px; max-height: 200px; overflow-y: auto;"></div>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal fade" id="modalAlergias" role="dialog">
              <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                  <div class="modal-header bg-primary">
                    <h4 class="modal-title"><i class="fa fa-wheelchair"></i> Gestionar Alergias</h4>
                  </div>
                  <div class="modal-body">
                    <div id="contenedor_filas_alergias"></div>
                    <button type="button" class="btn btn-success btn-sm" id="add_fila_ale">
                      <i class="fa fa-plus"></i> Añadir otra
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalNuevaAlergia">
                      <i class="fa fa-plus-circle"></i> Nueva Alergia
                    </button>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-second" id="cancelar_ale_modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="guardar_ale_listo">Listo</button>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal fade" id="modalBuscarAle" role="dialog">
              <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                  <div class="modal-header bg-primary">
                    <h4 class="modal-title">Buscar Alergia</h4>
                  </div>
                  <div class="modal-body">
                    <input type="text" id="inputBuscarAle" class="form-control" placeholder="Escriba para filtrar...">
                    <div class="list-group" id="listaResultadosAle" style="margin-top: 10px; max-height: 200px; overflow-y: auto;"></div>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal fade" id="modalNuevaPatologia" role="dialog">
              <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                  <div class="modal-header bg-primary">
                    <h4 class="modal-title"><i class="fa fa-plus-circle"></i> Nueva Patología</h4>
                  </div>
                  <div class="modal-body">
                    <form id="formAjaxPatologia">
                      <div class="form-group">
                        <label>Nombre de la Patología (*):</label>
                        <input type="text" class="form-control" id="ajax_nombre_pat" required>
                      </div>
                      <div class="form-group">
                        <label>Código CIE (Opcional):</label>
                        <input type="text" class="form-control" id="ajax_cie_pat">
                      </div>
                      <div class="form-group">
                        <label>¿Es contagiosa?</label>
                        <select class="form-control" id="ajax_contagiosa_pat">
                          <option value="NO">NO</option>
                          <option value="SI">SI</option>
                        </select>
                      </div>
                      <div class="form-group">
                        <label>Síntomas (*):</label>
                        <button type="button" class="btn btn-info btn-block" id="btn_abrir_sintomas_ajax" data-toggle="tooltip" title="Ninguno seleccionado">
                          <i><img src="../../recursos/imagenes/iconos/buscar.png" style="width:15px; height:15px; filter: invert(1);"></i> Gestionar Síntomas
                        </button>
                        <input type="hidden" id="ajax_sintomas_ids" value="">
                      </div>
                    </form>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btn_guardar_ajax_pat">Guardar</button>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal fade" id="modalSintomasAjax" role="dialog" style="z-index: 1060;">
              <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                  <div class="modal-header bg-primary">
                    <h4 class="modal-title"><i class="fa fa-list"></i> Seleccionar Síntomas</h4>
                  </div>
                  <div class="modal-body">
                    <div id="contenedor_filas_sintomas_ajax"></div>
                    <button type="button" class="btn btn-success btn-sm pull-left" id="add_fila_sintoma_ajax">
                      <i class="fa fa-plus"></i> Añadir otro
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalNuevoSintoma" style="margin-left:5px;">
                      <i class="fa fa-star"></i> Nuevo Síntoma
                    </button>
                    <div style="clear: both;"></div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="btn_confirmar_sintomas_ajax">Confirmar</button>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal fade" id="modalNuevoSintoma" role="dialog" style="z-index: 1070;">
              <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                  <div class="modal-header bg-primary">
                    <h4 class="modal-title">Crear Nuevo Síntoma</h4>
                  </div>
                  <div class="modal-body">
                    <div class="form-group">
                      <label>Nombre del Síntoma</label>
                      <input type="text" id="nombre_nuevo_sintoma" class="form-control" placeholder="Ej. Dolor de cabeza">
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="btnGuardarSintomaBD">Guardar</button>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal fade" id="modalNuevaAlergia" role="dialog">
              <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                  <div class="modal-header bg-primary">
                    <h4 class="modal-title"><i class="fa fa-plus-circle"></i> Nueva Alergia</h4>
                  </div>
                  <div class="modal-body">
                    <form id="formAjaxAlergia">
                      <div class="form-group">
                        <label>Nombre de la Alergia (*):</label>
                        <input type="text" class="form-control" id="ajax_nombre_ale" required>
                      </div>
                    </form>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btn_guardar_ajax_ale">Guardar</button>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal fade" id="modalDiscapacidades" role="dialog">
              <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                  <div class="modal-header bg-primary">
                    <h4 class="modal-title"><i class="fa fa-wheelchair"></i> Gestionar Discapacidad</h4>
                  </div>
                  <div class="modal-body">
                    <div class="row fila-disc-modal">
                      <div class="col-sm-6">
                        <label>Tipo de Discapacidad (*):</label>
                        <select class="form-control select-disc-dinamico" id="select_disc_unica"></select>
                      </div>
                      <div class="col-sm-4">
                        <label>Fecha detección (Opcional):</label>
                        <input type="date" class="form-control input-fecha-disc-dinamico" id="fecha_disc_unica" max="<?php echo date('Y-m-d'); ?>">
                      </div>
                      <div class="col-sm-2">
                        <label style="display:block;">&nbsp;</label>
                        <button type="button" class="btn btn-danger btn-remove-disc-modal">
                          <i><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></i>
                        </button>
                      </div>
                    </div>
                    <div style="margin-top: 15px;">
                      <button type="button" class="btn btn-success btn-sm" id="btn_add_disc_row">
                        <i class="fa fa-plus-circle"></i> Añadir Otra Discapacidad
                      </button>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-second" id="cancelar_disc_modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="guardar_disc_listo">Listo</button>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal fade" id="avisoModal" tabindex="-1" role="dialog">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header bg-crimson" id="avisoModalHeader">
                    <h4 class="modal-title" id="avisoModalLabel"><i class="fa fa-warning"></i>Aviso Importante</h4>
                  </div>
                  <div class="modal-body">
                    <p id="avisoTexto"></p>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal fade" id="modalConfirmarRegreso" tabindex="-1" role="dialog">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header bg-crimson">
                    <h4 class="modal-title"><i class="fa fa-sign-out"></i> Confirmación de Regreso</h4>
                  </div>
                  <div class="modal-body">
                    <p>Al hacer clic en "Abandonar Formulario", perderá todos los datos no guardados. ¿Desea continuar?</p>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-second" data-dismiss="modal">Regresar</button>
                    <a href="pacientes_menores_listado.php" class="btn btn-danger">Abandonar Formulario</a>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal fade" id="modalGuardarPaciente" tabindex="-1" role="dialog">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header bg-green">
                    <h4 class="modal-title"><i class="fa fa-save"></i> Confirmación de Guardado</h4>
                  </div>
                  <div class="modal-body">
                    <p>¿Está seguro de que desea actualizar la información del paciente menor de edad?</p>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-second" data-dismiss="modal">Regresar</button>
                    <button type="button" class="btn btn-success" id="confirmarGuardadoFinal">Aceptar</button>
                  </div>
                </div>
              </div>
            </div>

            <?php
            include('modales/personas/representantes_gestionar_modal.php');
            include('modales/personas/representantes_agregar_modal.php');
            ?>

        </div>

        <?php
        include('includes/footer.php');
        ?>

        <script>
          window.PARENTESCOS_DISPONIBLES = [{
              val: 'Madre',
              txt: 'Madre'
            },
            {
              val: 'Padre',
              txt: 'Padre'
            },
            {
              val: 'Abuelo/a',
              txt: 'Abuelo/a'
            },
            {
              val: 'Tío/a',
              txt: 'Tío/a'
            },
            {
              val: 'Hermano/a',
              txt: 'Hermano/a'
            },
            {
              val: 'Tutor Legal',
              txt: 'Tutor Legal'
            },
            {
              val: 'Otro',
              txt: 'Otro'
            },
            {
              val: 'No aplica',
              txt: 'No aplica'
            }
          ];
        </script>

        <script>
          const idPaciente = "<?php echo $id_paciente; ?>";
          const saved_id_pais_nac = "<?php echo $id_pais_nac; ?>";
          const saved_id_estado_nac = "<?php echo $id_estado_nac; ?>";
          const saved_id_municipio_nac = "<?php echo $id_municipio_nac; ?>";
          const saved_id_estado_dir = "<?php echo $id_estado_dir; ?>";
          const saved_id_municipio_dir = "<?php echo $id_municipio_dir; ?>";
          const saved_id_sector_dir = "<?php echo $id_sector_dir; ?>";
          const patologiasData = "<?php echo $patologias_data; ?>";
          const alergiasData = "<?php echo $alergias_data; ?>";
          const cedulaRepOriginal = "<?php echo htmlspecialchars($row['cedula_representante'] ?? ''); ?>";
          const tipoCedulaRepOriginal = "<?php echo htmlspecialchars($row['tipo_cedula_representante'] ?? 'V'); ?>";
          const discapacidadesData = <?php echo $discapacidades_json ?? '[]'; ?>;
          const idRepresentanteActual = "<?php echo $id_representante_actual; ?>";
          <?php
          $representantes_json_menor = '[]';
          $sql_rep_menor = "SELECT hr.Id_representante, p.nombre, p.apellido, hr.tipo_representante, hr.parentesco
              FROM historial_representantes hr
              INNER JOIN persona p ON hr.Id_representante = p.id
              WHERE hr.Id_paciente = $id_paciente AND hr.estatus = '1'";
          $res_rep_menor = $conexion->query($sql_rep_menor);
          if ($res_rep_menor && $res_rep_menor->num_rows > 0) {
            $arr_rep_menor = [];
            while ($r_m = $res_rep_menor->fetch_assoc()) {
              $arr_rep_menor[] = [
                'id' => intval($r_m['Id_representante']),
                'nombre' => trim($r_m['nombre'] . ' ' . $r_m['apellido']),
                'tipo' => $r_m['tipo_representante'],
                'parentesco' => $r_m['parentesco'] ?? ''
              ];
            }
            $representantes_json_menor = json_encode($arr_rep_menor);
          }
          ?>
          const representantesData = <?php echo $representantes_json_menor; ?>;

          window.mostrarAviso = function(mensaje) {
            $('#avisoModalHeader').removeClass('bg-green').addClass('bg-crimson');
            $('#avisoModalLabel').html('<i class="fa fa-warning"></i> Aviso Importante');
            $('#avisoTexto').html(mensaje);
            $('#avisoModal').modal('show');
          };

          window.mostrarAvisoExito = function(mensaje) {
            $('#avisoModalHeader').removeClass('bg-crimson').addClass('bg-green');
            $('#avisoModalLabel').html('<i class="fa fa-check"></i> Operación Exitosa');
            $('#avisoTexto').html(mensaje);
            $('#avisoModal').modal('show');
          };

          $('#avisoModal').on('hidden.bs.modal', function() {
            $('#avisoModalHeader').removeClass('bg-green').addClass('bg-crimson');
            $('#avisoModalLabel').html('<i class="fa fa-warning"></i> Aviso Importante');
          });

          $('.modal').on('hidden.bs.modal', function() {
            $(this).removeClass('out');
            if (!$('.modal.in').length) {
              $('body').removeClass('modal-open');
              $('.modal-backdrop').remove();
            } else {
              $('body').addClass('modal-open');
            }
          });

          function limpiarErroresPestana(tabSelector) {
            $(tabSelector).find('.input-error').removeClass('input-error');
          }

          function setFechaLimitesMenor() {
            const hoy = new Date();
            const minFecha = new Date(hoy.getFullYear() - 18, hoy.getMonth(), hoy.getDate());
            minFecha.setDate(minFecha.getDate() + 1);
            const yMin = minFecha.getFullYear();
            const mMin = String(minFecha.getMonth() + 1).padStart(2, '0');
            const dMin = String(minFecha.getDate()).padStart(2, '0');
            document.getElementById('fechaN').setAttribute('min', `${yMin}-${mMin}-${dMin}`);
            const yMax = hoy.getFullYear();
            const mMax = String(hoy.getMonth() + 1).padStart(2, '0');
            const dMax = String(hoy.getDate()).padStart(2, '0');
            document.getElementById('fechaN').setAttribute('max', `${yMax}-${mMax}-${dMax}`);
          }
          setFechaLimitesMenor();

          function calcularDiferenciaExacta(fechaNacimiento, fechaReferencia) {
            let años = fechaReferencia.getFullYear() - fechaNacimiento.getFullYear();
            let meses = fechaReferencia.getMonth() - fechaNacimiento.getMonth();
            let días = fechaReferencia.getDate() - fechaNacimiento.getDate();

            if (días < 0) {
              meses--;
              const mesAnterior = new Date(fechaReferencia.getFullYear(), fechaReferencia.getMonth(), 0);
              días += mesAnterior.getDate();
            }

            if (meses < 0) {
              años--;
              meses += 12;
            }

            const totalDias = Math.floor(
              (fechaReferencia.getTime() - fechaNacimiento.getTime()) / (1000 * 60 * 60 * 24)
            );

            let textoEdad;
            if (años > 0) {
              textoEdad = años + (años === 1 ? ' año' : ' años');
            } else if (meses > 0) {
              textoEdad = meses + (meses === 1 ? ' mes' : ' meses');
            } else if (días > 0) {
              textoEdad = días + (días === 1 ? ' día' : ' días');
            } else {
              textoEdad = '0 días';
            }

            return {
              años,
              meses,
              días,
              totalDias,
              textoEdad
            };
          }

          function calcularEdad() {
            var fechaNacimiento = $('#fechaN').val();
            var campoEdad = $('#edad');
            var $fechaInput = $('#fechaN');

            campoEdad.val('').removeAttr('data-edad-anios').removeAttr('title');
            $fechaInput.removeClass('input-error');

            if (!fechaNacimiento) return;

            var fechaNac = new Date(fechaNacimiento + 'T00:00:00');
            var hoy = new Date();
            hoy.setHours(0, 0, 0, 0);

            if (fechaNac > hoy) {
              mostrarAviso("⚠️ La fecha de nacimiento no puede ser posterior a la fecha actual.");
              $fechaInput.val("").addClass('input-error');
              return;
            }
            if (fechaNac.getFullYear() < 1900) {
              mostrarAviso("⚠️ La fecha de nacimiento no puede ser anterior a 1900.");
              $fechaInput.val("").addClass('input-error');
              return;
            }

            const calc = calcularDiferenciaExacta(fechaNac, hoy);

            if (calc.años >= 18) {
              mostrarAviso(
                "⚠️ Este formulario es solo para menores de 18 años.<br>" +
                "La edad calculada es: <b>" + calc.textoEdad + "</b>."
              );
              campoEdad
                .val('Mayor de edad (' + calc.años + ' años)')
                .attr('data-edad-anios', calc.años)
                .css('color', 'crimson');
              return;
            }

            campoEdad
              .val(calc.textoEdad)
              .attr('data-edad-anios', calc.años)
              .css('color', '#333');
          }
          $('#fechaN').on('change', calcularEdad);
          calcularEdad();

          async function verificarDatosUnicosBD(tipo, cedula, email, idPersona) {
            return new Promise((resolve) => {
              $.ajax({
                url: '../../cfg/ajax/verificar_existencia_cedula.php',
                method: 'POST',
                dataType: 'json',
                data: {
                  tipo_cedula: tipo,
                  cedula: cedula,
                  email: email,
                  id: idPersona
                },
                success: function(response) {
                  resolve(response);
                },
                error: function() {
                  resolve({
                    existe_cedula: false,
                    existe_email: false
                  });
                }
              });
            });
          }

          async function validarDatosUnicos() {
            const cedulaInput = document.getElementById('cedula');
            const tipoSelect = document.getElementById('tipo_cedula');
            const emailInput = document.getElementById('email');
            const cedula = cedulaInput ? cedulaInput.value.trim() : "";
            const tipo = tipoSelect ? tipoSelect.value : "";
            const email = emailInput ? emailInput.value.trim() : "";
            let idActual = 0;
            const inputId = document.querySelector('input[name="Id"]');
            if (inputId && inputId.value) idActual = inputId.value;

            let esValido = true;
            let errores = [];
            if (cedulaInput) $(cedulaInput).removeClass('input-error');
            if (tipoSelect) $(tipoSelect).removeClass('input-error');

            if (cedula === '') {
              if (cedulaInput) $(cedulaInput).addClass('input-error');
              errores.push("El campo de Cédula es obligatorio.");
              mostrarAviso(errores.join("<br>"));
              return false;
            }

            if (cedula !== "" && !isNaN(parseInt(cedula))) {
              const cedNum = parseInt(cedula);
              if (tipo === 'V' && cedNum > 80000000) {
                if (cedulaInput) $(cedulaInput).addClass('input-error');
                errores.push("Para tipo V-, la cédula no puede ser mayor a 80.000.000.");
                esValido = false;
              }
            }

            if (tipo === 'RP') {
              const regexRP = /^[0-9]{8}-[0-9]{1}$/;
              if (!regexRP.test(cedula)) {
                if (cedulaInput) $(cedulaInput).addClass('input-error');
                mostrarAviso("⚠️ Formato incorrecto para REP. Debe ser: <b>12345678-1</b>");
                return false;
              }
            }

            if (!esValido) {
              mostrarAviso("⚠️ " + errores.join("<br>"));
              return false;
            }

            if (cedula !== "") {
              const bd = await verificarDatosUnicosBD(tipo, cedula, email || "", idActual);
              if (bd.existe_cedula) {
                if (cedulaInput) $(cedulaInput).addClass('input-error');
                if (tipoSelect) $(tipoSelect).addClass('input-error');
                mostrarAviso(`El documento <b>${tipo}-${cedula}</b> ya se encuentra registrado en el sistema.`);
                return false;
              }
            }
            return true;
          }

          function alternarCamposRepresentante(bloquear) {
            $('#nombre_rep, #fechaN_rep, #apellido_rep, #telefono_rep, #email_rep').prop('readonly', bloquear);
            $('#genero_rep, #prefijo_rep, #parentesco').prop('disabled', bloquear);
            if (bloquear) {
              $('#nombre_rep, #apellido_rep, #fechaN_rep, #telefono_rep, #email_rep').css('background-color', '#eee');
              $('#prefijo_rep, #genero_rep, #parentesco').css('background-color', '#eee');
            } else {
              $('#nombre_rep, #apellido_rep, #fechaN_rep, #telefono_rep, #email_rep').css('background-color', '#fff');
              $('#prefijo_rep, #genero_rep, #parentesco').css('background-color', '#fff');
            }
          }

          async function verificarCedulaRepEnBD(tipo, cedula) {
            return new Promise((resolve) => {
              $.ajax({
                url: '../../cfg/ajax/obtener_representante.php',
                method: 'POST',
                dataType: 'json',
                data: {
                  tipo_cedula: tipo,
                  cedula: cedula
                },
                success: function(data) {
                  resolve(data);
                },
                error: function() {
                  resolve({
                    existe: false
                  });
                }
              });
            });
          }

          async function validarCedulaRepFormato() {
            const cedulaRepInput = document.getElementById('cedula_rep');
            const tipoCedulaRepSelect = document.getElementById('tipo_cedula_rep');
            if (!cedulaRepInput) return true;
            const cedula = cedulaRepInput.value.trim();
            const tipo = tipoCedulaRepSelect.value;
            let esValido = true;

            $(cedulaRepInput).removeClass('input-error');
            $(tipoCedulaRepSelect).removeClass('input-error');

            if (cedula === "") {
              return true;
            }

            if (cedula === cedulaRepOriginal && tipo === tipoCedulaRepOriginal) {
              return true;
            }

            const cedulaMenor = $('#cedula').val() ? $('#cedula').val().trim() : '';
            const tipoMenor = $('#tipo_cedula').val() ? $('#tipo_cedula').val() : '';
            if (cedula === cedulaMenor && tipo === tipoMenor) {
              $(cedulaRepInput).addClass('input-error');
              mostrarAviso('⚠️ La cédula del representante no puede ser igual a la del menor.');
              return false;
            }

            if (tipo === 'V') {
              const num = parseInt(cedula);
              if (!isNaN(num) && num > 80000000) {
                $(cedulaRepInput).addClass('input-error');
                mostrarAviso('⚠️ Cédula V- del representante no puede ser mayor a 80.000.000.');
                return false;
              }
            }

            $('#nombre_rep').attr('placeholder', 'Buscando datos...');
            const data = await verificarCedulaRepEnBD(tipo, cedula);

            if (data && data.existe) {
              if (data.es_representante === true || data.es_representante === 1) {
                $('#nombre_rep').val(data.nombre || '');
                $('#apellido_rep').val(data.apellido || '');
                if (data.fecha_nacimiento) {
                  $('#fechaN_rep').val(data.fecha_nacimiento);
                  calcularEdadRep();
                }
                if (data.telefono_numero) $('#telefono_rep').val(data.telefono_numero);
                if (data.prefijo_id) $('#prefijo_rep').val(data.prefijo_id);
                if (data.genero) $('#genero_rep').val(data.genero);
                if (data.email) $('#email_rep').val(data.email);
                alternarCamposRepresentante(true);
                $('#nombre_rep').attr('placeholder', 'Nombre Del Representante');
              } else {
                $('#nombre_rep').val('');
                $('#apellido_rep').val('');
                $('#fechaN_rep').val('');
                $('#edad_rep').val('');
                $('#telefono_rep').val('');
                $('#prefijo_rep').val('');
                $('#genero_rep').val('');
                $('#email_rep').val('');
                $(cedulaRepInput).addClass('input-error');
                alternarCamposRepresentante(true);
                mostrarAviso('⚠️ Esta cédula ya está registrada pero no pertenece a un representante. Ingrese otra cédula.');
                esValido = false;
              }
            } else {
              if ($('#nombre_rep').prop('readonly')) {
                $('#nombre_rep').val('');
                $('#apellido_rep').val('');
                $('#fechaN_rep').val('');
                $('#edad_rep').val('');
                $('#telefono_rep').val('');
                $('#prefijo_rep').val('');
                $('#genero_rep').val('');
                $('#email_rep').val('');
              }
              alternarCamposRepresentante(false);
              $('#nombre_rep').attr('placeholder', 'Nombre Del Representante');
            }
            return esValido;
          }

          if (document.getElementById('cedula_rep')) {
            document.getElementById('cedula_rep').addEventListener('blur', validarCedulaRepFormato);
            document.getElementById('tipo_cedula_rep').addEventListener('change', validarCedulaRepFormato);
          }

          $(document).ready(function() {
            if (cedulaRepOriginal && cedulaRepOriginal.trim() !== '') {
              alternarCamposRepresentante(true);
            }
          });

          document.getElementById('pais_nacimiento').addEventListener('change', function() {
            var paisId = this.value;
            var estadoSelect = document.getElementById('estado_nacimiento');
            var municipioSelect = document.getElementById('municipio_nacimiento');
            estadoSelect.innerHTML = '<option value="">--- Seleccione Un Estado ---</option>';
            municipioSelect.innerHTML = '<option value="">--- Seleccione Un Municipio ---</option>';
            if (paisId) {
              $.ajax({
                url: '../../cfg/ajax/obtener_estados.php',
                method: 'GET',
                data: {
                  Id_Pais: paisId
                },
                dataType: 'json',
                cache: false,
                success: function(data) {
                  for (var i = 0; i < data.length; i++) {
                    estadoSelect.innerHTML += '<option value="' + data[i].Id_Estado + '">' + data[i].nombre_estado + '</option>';
                  }
                }
              });
            }
          });

          document.getElementById('estado_nacimiento').addEventListener('change', function() {
            var estadoId = this.value;
            var municipioSelect = document.getElementById('municipio_nacimiento');
            municipioSelect.innerHTML = '<option value="">--- Seleccione Un Municipio ---</option>';
            if (estadoId) {
              $.ajax({
                url: '../../cfg/ajax/obtener_municipios.php',
                method: 'GET',
                data: {
                  Id_Estado: estadoId
                },
                dataType: 'json',
                cache: false,
                success: function(data) {
                  for (var i = 0; i < data.length; i++) {
                    municipioSelect.innerHTML += '<option value="' + data[i].Id_Municipio + '">' + data[i].nombre_municipio + '</option>';
                  }
                }
              });
            }
          });

          document.getElementById('estado').addEventListener('change', function() {
            var estadoId = this.value;
            var municipioSelect = document.getElementById('municipio');
            var sectorSelect = document.getElementById('sector');
            municipioSelect.innerHTML = '<option value="">--- Seleccione Un Municipio ---</option>';
            if (sectorSelect) sectorSelect.innerHTML = '<option value="">--- Seleccione Un Sector ---</option>';
            if (estadoId) {
              $.ajax({
                url: '../../cfg/ajax/obtener_municipios.php',
                method: 'GET',
                data: {
                  Id_Estado: estadoId
                },
                dataType: 'json',
                cache: false,
                success: function(data) {
                  for (var i = 0; i < data.length; i++) {
                    municipioSelect.innerHTML += '<option value="' + data[i].Id_Municipio + '">' + data[i].nombre_municipio + '</option>';
                  }
                }
              });
            }
          });

          document.getElementById('municipio').addEventListener('change', function() {
            var municipioId = this.value;
            var sectorSelect = document.getElementById('sector');
            sectorSelect.innerHTML = '<option value="">--- Seleccione Un Sector ---</option>';
            if (municipioId) {
              $.ajax({
                url: '../../cfg/ajax/obtener_sectores.php',
                method: 'GET',
                data: {
                  Id_Municipio: municipioId
                },
                dataType: 'json',
                cache: false,
                success: function(data) {
                  for (var i = 0; i < data.length; i++) {
                    sectorSelect.innerHTML += '<option value="' + data[i].Id_Sector + '">' + data[i].nombre_sector + '</option>';
                  }
                }
              });
            }
          });

          const selectAnalfabeta = document.getElementById('analfabeta');
          const mision = document.getElementById('mision');
          const añosAprobados = document.getElementById('años_aprobados');
          const selectEtnia = document.getElementById('etnia');
          const tipoEtnia = document.getElementById('tipo_etnia');
          const selectDiscapacidad = document.getElementById('discapacidad');

          const limitesMision = {
            "robinson": 6,
            "ribas": 5,
            "sucre": 6
          };

          function toggleOptions() {
            const esAnalfabeta = selectAnalfabeta.value === 'Si';
            const siTieneDiscapacidad = selectDiscapacidad.value === 'Si';
            const noTieneDiscapacidad = selectDiscapacidad.value === 'No';

            if (esAnalfabeta) {
              $('#mision, #años_aprobados').prop('disabled', false);
            } else if (selectAnalfabeta.value === 'No') {
              $('#mision, #años_aprobados').prop('disabled', true);
              $('#años_help').text('');
            } else {
              $('#mision, #años_aprobados').prop('disabled', false);
            }
            if (selectEtnia.value === 'Si') {
              $('#tipo_etnia').prop('disabled', false);
            } else if (selectEtnia.value === 'No') {
              $('#tipo_etnia').prop('disabled', true);
            } else {
              $('#tipo_etnia').prop('disabled', false);
            }
            if (siTieneDiscapacidad) {
              $('#btn_modal_disc').prop('disabled', false).removeClass('btn-second').addClass('btn-info');

              if (typeof discapacidadesData !== 'undefined' && Array.isArray(discapacidadesData) && discapacidadesData.length > 0) {
                if (!$('#discapacidades_ids').val() || $('#discapacidades_ids').val() === '[]') {
                  let nombres = discapacidadesData.map(d => d.nombre);
                  $('#discapacidades_ids').val(JSON.stringify(discapacidadesData));
                  $('#btn_modal_disc').attr('data-original-title', nombres.join(', ')).tooltip('fixTitle');
                }
              }
            } else {
              $('#btn_modal_disc').prop('disabled', true).removeClass('btn-info').addClass('btn-second');
              $('#discapacidades_ids').val('[]');
              $('#btn_modal_disc').attr('data-original-title', 'Ninguna seleccionada').tooltip('fixTitle');
            }
          }

          if (selectAnalfabeta) selectAnalfabeta.addEventListener('change', toggleOptions);
          if (selectEtnia) selectEtnia.addEventListener('change', toggleOptions);
          selectDiscapacidad.addEventListener('change', toggleOptions);

          selectDiscapacidad.addEventListener('change', function() {
            toggleOptions();
            if (this.value === 'No') {
              $('#discapacidades_ids').val('[]');
              $('#btn_modal_disc')
                .prop('disabled', true)
                .removeClass('btn-info')
                .addClass('btn-second')
                .attr('data-original-title', 'Ninguna seleccionada')
                .tooltip('fixTitle');
            }
          });
          toggleOptions();

          $('#mision').on('change', function() {
            const seleccion = $(this).val();
            const maxAños = limitesMision[seleccion];
            if (maxAños) {
              $('#años_aprobados').attr('max', maxAños).attr('placeholder', 'Máx: ' + maxAños);
              $('#años_help').text('Duración máxima para Misión ' + seleccion.charAt(0).toUpperCase() + seleccion.slice(1) + ': ' + maxAños + ' años.');
              const valorActual = parseInt($('#años_aprobados').val());
              if (valorActual > maxAños) $('#años_aprobados').val(maxAños);
            } else {
              $('#años_aprobados').removeAttr('max').attr('placeholder', '');
              $('#años_help').text('');
            }
          });

          $('#años_aprobados').on('input', function() {
            const max = parseInt($(this).attr('max'));
            const val = parseInt($(this).val());
            if (!isNaN(max) && val > max) {
              $(this).addClass('input-error');
              $('#años_help').text('⚠️ No puede exceder ' + max + ' años.').css('color', 'crimson');
            } else {
              $(this).removeClass('input-error');
              const m = $('#mision').val();
              if (limitesMision[m]) {
                $('#años_help').text('Duración máxima para Misión ' + m.charAt(0).toUpperCase() + m.slice(1) + ': ' + limitesMision[m] + ' años.').css('color', '#777');
              } else {
                $('#años_help').text('');
              }
            }
          });

          function normalizarSiSinDatos() {
            let cambios = [];

            const $selectDisc = $('#discapacidad');
            const discIdsRaw = $('#discapacidades_ids').val();
            let discLista = [];
            try {
              discLista = JSON.parse(discIdsRaw || '[]');
            } catch (e) {
              discLista = [];
            }

            if ($selectDisc.val() === 'Si' && (!Array.isArray(discLista) || discLista.length === 0)) {
              $selectDisc.val('No');
              $('#discapacidades_ids').val('[]');
              $('#btn_modal_disc').prop('disabled', true).removeClass('btn-info').addClass('btn-second').attr('data-original-title', 'Ninguna seleccionada').tooltip('fixTitle');
              cambios.push('discapacidad');
            }

            const $selectRep = $('#tiene_representante');
            if ($selectRep.length) {
              const repIdsRaw = $('#representantes_ids').val();
              let repLista = [];
              try {
                repLista = JSON.parse(repIdsRaw || '[]');
              } catch (e) {
                repLista = [];
              }

              if ($selectRep.val() === 'Si' && (!Array.isArray(repLista) || repLista.length === 0)) {
                $selectRep.val('No');
                $('#representantes_ids').val('[]');
                $('#btn_modal_rep').prop('disabled', true).removeClass('btn-info').addClass('btn-second').attr('data-original-title', 'Sin Representantes').tooltip('fixTitle');
                cambios.push('representante');
              }
            }

            const $selectEtnia = $('#etnia');
            if ($selectEtnia.val() === 'Si' && (!$('#tipo_etnia').val() || $('#tipo_etnia').val() === '')) {
              $selectEtnia.val('No');
              $('#tipo_etnia').val('').prop('disabled', true);
              cambios.push('etnia');
            }

            if (typeof toggleOptions === 'function') toggleOptions();
            return {
              cambios
            };
          }

          $('#btn_modal_pat, #btn_modal_ale').tooltip();

          let listaPatologias = [];
          let listaAlergias = [];
          let listaSintomas = [];

          function construirOpcionesPatologias() {
            let html = '<option value="">--- Seleccione una patología ---</option>';
            listaPatologias.slice().sort((a, b) => a.nombre.localeCompare(b.nombre)).forEach(p => {
              html += `<option value="${p.id}" data-nombre="${p.nombre}" data-cie="${p.cie || ''}">${p.nombre}</option>`;
            });
            return html;
          }

          function construirOpcionesAlergias() {
            let html = '<option value="">--- Seleccione una alergia ---</option>';
            listaAlergias.slice().sort((a, b) => a.nombre.localeCompare(b.nombre)).forEach(a => {
              html += `<option value="${a.id}" data-nombre="${a.nombre}">${a.nombre}</option>`;
            });
            return html;
          }

          function construirOpcionesSintomas() {
            let html = '<option value="">--- Seleccione un síntoma ---</option>';
            listaSintomas.slice().sort((a, b) => a.nombre.localeCompare(b.nombre)).forEach(s => {
              html += `<option value="${s.id}">${s.nombre}</option>`;
            });
            return html;
          }

          $(document).ready(function() {
            <?php
            $q_init_pat = $conexion->query("SELECT Id_patologia, nombre_patologia, codigo_cie FROM patologias WHERE estatus = 1 ORDER BY nombre_patologia ASC");
            if ($q_init_pat) {
              while ($p = $q_init_pat->fetch_assoc()) {
                echo "listaPatologias.push({id: " . intval($p['Id_patologia']) . ", nombre: " . json_encode($p['nombre_patologia']) . ", cie: " . json_encode($p['codigo_cie']) . "});\n";
              }
            }
            $q_init_ale = $conexion->query("SELECT Id_alergias_conocidas, nombre_alergia FROM alergias_conocidas WHERE estatus = 1 ORDER BY nombre_alergia ASC");
            if ($q_init_ale) {
              while ($a = $q_init_ale->fetch_assoc()) {
                echo "listaAlergias.push({id: " . intval($a['Id_alergias_conocidas']) . ", nombre: " . json_encode($a['nombre_alergia']) . "});\n";
              }
            }
            $q_init_sin = $conexion->query("SELECT Id_sintomas, nombre_sintoma FROM sintomas WHERE estatus = 1 ORDER BY nombre_sintoma ASC");
            if ($q_init_sin) {
              while ($s = $q_init_sin->fetch_assoc()) {
                echo "listaSintomas.push({id: " . intval($s['Id_sintomas']) . ", nombre: " . json_encode($s['nombre_sintoma']) . "});\n";
              }
            }
            ?>
          });

          function agregarFilaPatologia(id_guardado = "", fecha_guardada = "") {
            let htmlPat = `
                <div class="row fila-pat" style="margin-bottom: 10px;">
                  <div class="col-sm-6">
                    <div class="input-group">
                      <select class="form-control select-pat">${construirOpcionesPatologias()}</select>
                      <span class="input-group-btn">
                        <button class="btn btn-info btn-search-pat" type="button" title="Buscar"><i><img src="../../recursos/imagenes/iconos/buscar.png" style="width:10px;height:10px;"></i></button>
                      </span>
                    </div>
                  </div>
                  <div class="col-sm-4"><input type="date" class="form-control input-fecha-pat" max="<?php echo date('Y-m-d'); ?>"></div>
                  <div class="col-sm-2"><button type="button" class="btn btn-danger btn-remove-pat"><i><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px;height:15px;"></i></button></div>
                </div>`;
            let $fila = $(htmlPat);
            $('#contenedor_filas_patologias').append($fila);
            if (id_guardado !== "" && id_guardado !== null) setTimeout(() => $fila.find('.select-pat').val(id_guardado.toString().trim()), 10);
            if (fecha_guardada !== "" && fecha_guardada !== null) $fila.find('.input-fecha-pat').val(fecha_guardada.toString().trim());
          }

          function agregarFilaAlergia(id_guardado = "", fecha_guardada = "") {
            let htmlAle = `
                <div class="row fila-ale" style="margin-bottom: 10px;">
                  <div class="col-sm-6">
                    <div class="input-group">
                      <select class="form-control select-ale">${construirOpcionesAlergias()}</select>
                      <span class="input-group-btn">
                        <button class="btn btn-info btn-search-ale" type="button" title="Buscar"><i><img src="../../recursos/imagenes/iconos/buscar.png" style="width:10px;height:10px;"></i></button>
                      </span>
                    </div>
                  </div>
                  <div class="col-sm-4"><input type="date" class="form-control input-fecha-ale" max="<?php echo date('Y-m-d'); ?>"></div>
                  <div class="col-sm-2"><button type="button" class="btn btn-danger btn-remove-ale"><i><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px;height:15px;"></i></button></div>
                </div>`;
            let $fila = $(htmlAle);
            $('#contenedor_filas_alergias').append($fila);
            if (id_guardado !== "" && id_guardado !== null) setTimeout(() => $fila.find('.select-ale').val(id_guardado.toString().trim()), 10);
            if (fecha_guardada !== "" && fecha_guardada !== null) $fila.find('.input-fecha-ale').val(fecha_guardada.toString().trim());
          }

          function inicializarDatosMedicos() {
            $('#contenedor_filas_patologias').empty();
            $('#contenedor_filas_alergias').empty();

            let patSeleccionadas = [];
            let patNombres = [];
            if (patologiasData && patologiasData.trim() !== "") {
              let items = patologiasData.split('||');
              items.forEach(function(item) {
                if (item.trim() !== "") {
                  let parts = item.split('::');
                  if (parts.length >= 3) {
                    let id_pat = parts[0] ? parts[0].trim() : '';
                    let fecha_pat = parts[3] ? parts[3].trim() : '';
                    agregarFilaPatologia(id_pat, fecha_pat);
                    setTimeout(() => {
                      let opt = $(`.select-pat option[value="${id_pat}"]`).first();
                      if (opt.length) patNombres.push(opt.text().trim());
                    }, 50);
                    patSeleccionadas.push({
                      id: parseInt(id_pat),
                      fecha: fecha_pat
                    });
                  }
                }
              });
              $('#patologias_ids').val(JSON.stringify(patSeleccionadas));
              setTimeout(() => {
                if (patNombres.length > 0) $('#btn_modal_pat').attr('data-original-title', patNombres.join(', ')).tooltip('fixTitle');
              }, 100);
            } else {
              agregarFilaPatologia();
            }

            let aleSeleccionadas = [];
            let aleNombres = [];
            if (alergiasData && alergiasData.trim() !== "") {
              let items = alergiasData.split('||');
              items.forEach(function(item) {
                if (item.trim() !== "") {
                  let parts = item.split('::');
                  if (parts.length >= 2) {
                    let id_ale = parts[0] ? parts[0].trim() : '';
                    let fecha_ale = parts[2] ? parts[2].trim() : '';
                    agregarFilaAlergia(id_ale, fecha_ale);
                    setTimeout(() => {
                      let opt = $(`.select-ale option[value="${id_ale}"]`).first();
                      if (opt.length) aleNombres.push(opt.text().trim());
                    }, 50);
                    aleSeleccionadas.push({
                      id: parseInt(id_ale),
                      fecha: fecha_ale
                    });
                  }
                }
              });
              $('#alergias_ids').val(JSON.stringify(aleSeleccionadas));
              setTimeout(() => {
                if (aleNombres.length > 0) $('#btn_modal_ale').attr('data-original-title', aleNombres.join(', ')).tooltip('fixTitle');
              }, 100);
            } else {
              agregarFilaAlergia();
            }
          }

          document.addEventListener('DOMContentLoaded', inicializarDatosMedicos);

          $('#add_fila_pat').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if ($('.fila-pat').length === 0) {
              agregarFilaPatologia();
              return;
            }
            const $ultima = $('.fila-pat').last();
            if (!$ultima.find('.select-pat').val()) {
              mostrarAviso("⚠️ Debe seleccionar una patología en la fila actual antes de añadir otra.");
              return;
            }
            agregarFilaPatologia();
          });

          $('#add_fila_ale').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if ($('.fila-ale').length === 0) {
              agregarFilaAlergia();
              return;
            }
            const $ultima = $('.fila-ale').last();
            if (!$ultima.find('.select-ale').val()) {
              mostrarAviso("⚠️ Debe seleccionar una alergia en la fila actual antes de añadir otra.");
              return;
            }
            agregarFilaAlergia();
          });

          $(document).on('click', '.btn-remove-pat', function(e) {
            e.preventDefault();
            const $fila = $(this).closest('.fila-pat');
            const tieneDatos = $fila.find('.select-pat').val() || $fila.find('.input-fecha-pat').val();
            if (tieneDatos) {
              $fila.find('.select-pat').val('');
              $fila.find('.input-fecha-pat').val('');
              return;
            }
            if ($('.fila-pat').length <= 1) return;
            $fila.remove();
          });

          $(document).on('click', '.btn-remove-ale', function(e) {
            e.preventDefault();
            const $fila = $(this).closest('.fila-ale');
            const tieneDatos = $fila.find('.select-ale').val() || $fila.find('.input-fecha-ale').val();
            if (tieneDatos) {
              $fila.find('.select-ale').val('');
              $fila.find('.input-fecha-ale').val('');
              return;
            }
            if ($('.fila-ale').length <= 1) return;
            $fila.remove();
          });

          $('#guardar_pat_listo').click(function(e) {
            let seleccionadas = [],
              nombres = [],
              errorFecha = false,
              filaVacia = false;
            $('.fila-pat').each(function() {
              let $sel = $(this).find('.select-pat');
              let id = $sel.val();
              let fecha = $(this).find('.input-fecha-pat').val();
              if (!id) {
                if ($('.fila-pat').length > 1) filaVacia = true;
                return;
              }
              if (!fecha) errorFecha = true;
              let opt = $sel.find('option:selected');
              seleccionadas.push({
                id: parseInt(id),
                nombre: opt.data('nombre'),
                codigo_cie: opt.data('cie'),
                fecha: fecha
              });
              nombres.push(opt.text().trim());
            });
            if (filaVacia) {
              mostrarAviso("⚠️ Hay filas de patologías sin seleccionar. Complételas o elimínelas.");
              return false;
            }
            if (errorFecha && e.originalEvent) {
              mostrarAviso("⚠️ Hay patologías seleccionadas sin fecha.");
              return false;
            }
            $('#patologias_ids').val(JSON.stringify(seleccionadas));
            if (nombres.length > 0) $('#btn_modal_pat').attr('data-original-title', nombres.join(', ')).tooltip('fixTitle').removeClass('input-error');
            else $('#btn_modal_pat').attr('data-original-title', 'Ninguna seleccionada').tooltip('fixTitle');
            $('#modalPatologias').modal('hide');
          });

          $('#guardar_ale_listo').click(function(e) {
            let seleccionadas = [],
              nombres = [],
              errorFecha = false,
              filaVacia = false;
            $('.fila-ale').each(function() {
              let $sel = $(this).find('.select-ale');
              let id = $sel.val();
              let fecha = $(this).find('.input-fecha-ale').val();
              if (!id) {
                if ($('.fila-ale').length > 1) filaVacia = true;
                return;
              }
              if (!fecha) errorFecha = true;
              let opt = $sel.find('option:selected');
              seleccionadas.push({
                id: parseInt(id),
                nombre: opt.data('nombre'),
                fecha: fecha
              });
              nombres.push(opt.text().trim());
            });
            if (filaVacia) {
              mostrarAviso("⚠️ Hay filas de alergias sin seleccionar. Complételas o elimínelas.");
              return false;
            }
            if (errorFecha && e.originalEvent) {
              mostrarAviso("⚠️ Hay alergias seleccionadas sin fecha.");
              return false;
            }
            $('#alergias_ids').val(JSON.stringify(seleccionadas));
            if (nombres.length > 0) $('#btn_modal_ale').attr('data-original-title', nombres.join(', ')).tooltip('fixTitle').removeClass('input-error');
            else $('#btn_modal_ale').attr('data-original-title', 'Ninguna seleccionada').tooltip('fixTitle');
            $('#modalAlergias').modal('hide');
          });

          let selectDestinoTarget = null;
          $(document).on('click', '.btn-search-pat', function() {
            selectDestinoTarget = $(this).closest('.input-group').find('.select-pat');
            $('#modalBuscarPat').modal('show');
            $('#inputBuscarPat').val('').trigger('keyup');
          });
          $('#inputBuscarPat').on('keyup', function() {
            let texto = $(this).val().toLowerCase(),
              html = '';
            $('.select-pat:first option').not('[value=""]').each(function() {
              let nombre = $(this).text();
              if (nombre.toLowerCase().includes(texto)) html += `<a href="#" class="list-group-item list-group-item-action seleccionar-pat" data-id="${$(this).val()}">${nombre}</a>`;
            });
            $('#listaResultadosPat').html(html);
          });
          $(document).on('click', '.seleccionar-pat', function(e) {
            e.preventDefault();
            selectDestinoTarget.val($(this).data('id')).trigger('change');
            $('#modalBuscarPat').modal('hide');
          });

          $(document).on('click', '.btn-search-ale', function() {
            selectDestinoTarget = $(this).closest('.input-group').find('.select-ale');
            $('#modalBuscarAle').modal('show');
            $('#inputBuscarAle').val('').trigger('keyup');
          });
          $('#inputBuscarAle').on('keyup', function() {
            let texto = $(this).val().toLowerCase(),
              html = '';
            $('.select-ale:first option').not('[value=""]').each(function() {
              let nombre = $(this).text();
              if (nombre.toLowerCase().includes(texto)) html += `<a href="#" class="list-group-item list-group-item-action seleccionar-ale" data-id="${$(this).val()}">${nombre}</a>`;
            });
            $('#listaResultadosAle').html(html);
          });
          $(document).on('click', '.seleccionar-ale', function(e) {
            e.preventDefault();
            selectDestinoTarget.val($(this).data('id')).trigger('change');
            $('#modalBuscarAle').modal('hide');
          });

          $(document).on('change', '.select-pat', function() {
            let val = $(this).val();
            if (!val) return;
            if ($('.select-pat').filter(function() {
                return $(this).val() === val;
              }).length > 1) {
              mostrarAviso("⚠️ Esta patología ya fue seleccionada.");
              $(this).val("");
            }
          });
          $(document).on('change', '.select-ale', function() {
            let val = $(this).val();
            if (!val) return;
            if ($('.select-ale').filter(function() {
                return $(this).val() === val;
              }).length > 1) {
              mostrarAviso("⚠️ Esta alergia ya fue seleccionada.");
              $(this).val("");
            }
          });

          $('#cancelar_pat_modal').on('click', function(e) {
            e.preventDefault();
            inicializarDatosMedicos();
            $('#modalPatologias').modal('hide');
          });
          $('#cancelar_ale_modal').on('click', function(e) {
            e.preventDefault();
            inicializarDatosMedicos();
            $('#modalAlergias').modal('hide');
          });

          $('#btn_abrir_sintomas_ajax').click(function() {
            $('#modalSintomasAjax').modal('show');
            if ($('.fila-sintoma-ajax').length === 0) agregarFilaSintomaAjax();
          });

          function agregarFilaSintomaAjax() {
            let htmlSintoma = `
                <div class="row fila-sintoma-ajax" style="margin-bottom: 10px;">
                  <div class="col-sm-10"><select class="form-control select-sintoma-ajax">${construirOpcionesSintomas()}</select></div>
                  <div class="col-sm-2"><button type="button" class="btn btn-danger btn-remove-sintoma-ajax"><i><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px;height:15px;"></i></button></div>
                </div>`;
            $('#contenedor_filas_sintomas_ajax').append(htmlSintoma);
          }
          $('#add_fila_sintoma_ajax').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if ($('.fila-sintoma-ajax').length === 0) {
              agregarFilaSintomaAjax();
              return;
            }
            const $ultima = $('.fila-sintoma-ajax').last();
            if (!$ultima.find('.select-sintoma-ajax').val()) {
              mostrarAviso("⚠️ Debe seleccionar un síntoma en la fila actual antes de añadir otro.");
              return;
            }
            agregarFilaSintomaAjax();
          });
          $(document).on('click', '.btn-remove-sintoma-ajax', function(e) {
            e.preventDefault();
            const $fila = $(this).closest('.fila-sintoma-ajax');
            if ($fila.find('.select-sintoma-ajax').val()) {
              $fila.find('.select-sintoma-ajax').val('');
              return;
            }
            if ($('.fila-sintoma-ajax').length <= 1) return;
            $fila.remove();
          });
          $('#btn_confirmar_sintomas_ajax').click(function() {
            let seleccionados = [],
              filaVacia = false;
            $('.select-sintoma-ajax').each(function() {
              let val = $(this).val();
              if (val) seleccionados.push(val);
              else if ($('.select-sintoma-ajax').length > 1) filaVacia = true;
            });
            if (filaVacia) {
              mostrarAviso("⚠️ Hay filas de síntomas sin seleccionar.");
              return;
            }
            $('#ajax_sintomas_ids').val(seleccionados.join(','));
            let boton = $('#btn_abrir_sintomas_ajax');
            if (seleccionados.length > 0) boton.attr('data-original-title', seleccionados.length + ' síntoma(s)').tooltip('fixTitle').removeClass('btn-info').addClass('btn-success');
            else boton.attr('data-original-title', 'Ninguno seleccionado').tooltip('fixTitle').removeClass('btn-success').addClass('btn-info');
            $('#modalSintomasAjax').modal('hide');
          });

          $('#btn_guardar_ajax_pat').click(function() {
            let nombre = $('#ajax_nombre_pat').val().trim();
            let cie = $('#ajax_cie_pat').val().trim();
            let contagiosa = $('#ajax_contagiosa_pat').val();
            let sintomas = $('#ajax_sintomas_ids').val();
            if (!nombre) {
              mostrarAviso("El nombre de la patología es obligatorio.");
              return;
            }
            $.ajax({
              url: window.location.href,
              method: 'POST',
              data: {
                ajax_nueva_patologia: 1,
                nombre_patologia: nombre,
                codigo_cie: cie,
                enfermedad_contagiosa: contagiosa,
                sintomas_ids: sintomas
              },
              dataType: 'json',
              success: function(res) {
                if (res.success) {
                  listaPatologias.push({
                    id: parseInt(res.id),
                    nombre: res.nombre,
                    cie: res.cie || ''
                  });
                  let nuevasOpciones = construirOpcionesPatologias();
                  $('.select-pat').each(function() {
                    let valorActual = $(this).val();
                    $(this).html(nuevasOpciones);
                    if (valorActual) $(this).val(valorActual);
                  });
                  let selectVacio = $('.select-pat').filter(function() {
                    return !$(this).val();
                  }).first();
                  if (selectVacio.length > 0) selectVacio.val(res.id).trigger('change');
                  else agregarFilaPatologia(res.id, "");
                  $('#modalNuevaPatologia').modal('hide');
                  $('#formAjaxPatologia')[0].reset();
                  $('#contenedor_filas_sintomas_ajax').empty();
                  $('#ajax_sintomas_ids').val('');
                  $('#btn_abrir_sintomas_ajax').removeClass('btn-success').addClass('btn-info').attr('data-original-title', 'Ninguno seleccionado').tooltip('fixTitle');
                  mostrarAvisoExito("Patología guardada con éxito.");
                } else {
                  mostrarAviso(res.error);
                }
              },
              error: function(xhr) {
                mostrarAviso("⚠️ Error al guardar la patología.<br>" + xhr.responseText.substring(0, 200));
              }
            });
          });

          $('#btn_guardar_ajax_ale').click(function() {
            let nombre = $('#ajax_nombre_ale').val().trim();
            if (!nombre) {
              mostrarAviso("El nombre de la alergia es obligatorio.");
              return;
            }
            $.ajax({
              url: window.location.href,
              method: 'POST',
              data: {
                ajax_nueva_alergia: 1,
                nombre_alergia: nombre
              },
              dataType: 'json',
              success: function(res) {
                if (res.success) {
                  listaAlergias.push({
                    id: parseInt(res.id),
                    nombre: res.nombre
                  });
                  let nuevasOpciones = construirOpcionesAlergias();
                  $('.select-ale').each(function() {
                    let valorActual = $(this).val();
                    $(this).html(nuevasOpciones);
                    if (valorActual) $(this).val(valorActual);
                  });
                  let selectVacio = $('.select-ale').filter(function() {
                    return !$(this).val();
                  }).first();
                  if (selectVacio.length > 0) selectVacio.val(res.id).trigger('change');
                  else agregarFilaAlergia(res.id, "");
                  $('#modalNuevaAlergia').modal('hide');
                  $('#formAjaxAlergia')[0].reset();
                  mostrarAvisoExito("Alergia guardada con éxito.");
                } else {
                  mostrarAviso(res.error);
                }
              },
              error: function(xhr) {
                mostrarAviso("⚠️ Error al guardar la alergia.<br>" + xhr.responseText.substring(0, 200));
              }
            });
          });

          $('#btnGuardarSintomaBD').click(function() {
            let nombre = $('#nombre_nuevo_sintoma').val().trim();
            if (!nombre) {
              mostrarAviso("El nombre del síntoma es obligatorio.");
              return;
            }
            $.ajax({
              url: window.location.href,
              method: 'POST',
              data: {
                ajax_nuevo_sintoma: 1,
                nombre_sintoma: nombre
              },
              dataType: 'json',
              success: function(res) {
                if (res.success) {
                  listaSintomas.push({
                    id: parseInt(res.id),
                    nombre: res.nombre
                  });
                  let nuevasOpciones = construirOpcionesSintomas();
                  $('.select-sintoma-ajax').each(function() {
                    let valorActual = $(this).val();
                    $(this).html(nuevasOpciones);
                    if (valorActual) $(this).val(valorActual);
                  });
                  let selectVacio = $('.select-sintoma-ajax').filter(function() {
                    return !$(this).val();
                  }).first();
                  if (selectVacio.length > 0) selectVacio.val(res.id);
                  else $('.select-sintoma-ajax').last().val(res.id);
                  $('#modalNuevoSintoma').modal('hide');
                  $('#nombre_nuevo_sintoma').val('');
                  mostrarAvisoExito("Síntoma guardado con éxito.");
                } else {
                  mostrarAviso(res.error);
                }
              },
              error: function(xhr) {
                mostrarAviso("⚠️ Error al guardar el síntoma.<br>" + xhr.responseText.substring(0, 200));
              }
            });
          });

          let listaDiscapacidades = [];

          function construirOpcionesDiscapacidades() {
            let html = '<option value="">--- Seleccione una discapacidad ---</option>';
            listaDiscapacidades.slice().sort((a, b) => a.nombre.localeCompare(b.nombre)).forEach(d => {
              html += `<option value="${d.id}" data-nombre="${d.nombre}">${d.nombre}</option>`;
            });
            return html;
          }

          $(document).ready(function() {
            <?php
            $q_init_disc = $conexion->query("SELECT Id_discapacidad, nombre_discapacidad FROM discapacidades WHERE estatus = 1 ORDER BY nombre_discapacidad ASC");
            if ($q_init_disc) {
              while ($d = $q_init_disc->fetch_assoc()) {
                echo "listaDiscapacidades.push({id: " . intval($d['Id_discapacidad']) . ", nombre: " . json_encode($d['nombre_discapacidad']) . "});\n";
              }
            }
            ?>
            $('#select_disc_unica').html(construirOpcionesDiscapacidades());
          });

          function agregarFilaDiscapacidad(id_guardado = "", fecha_guardada = "") {
            const optionsHtml = construirOpcionesDiscapacidades();
            const htmlNueva = `
                <div class="row fila-disc-modal" style="margin-top: 15px; padding-top: 15px; border-top: 1px dashed #ddd;">
                  <div class="col-sm-6"><label>Tipo de Discapacidad (*):</label><select class="form-control select-disc-dinamico">${optionsHtml}</select></div>
                  <div class="col-sm-4"><label>Fecha detección (Opcional):</label><input type="date" class="form-control input-fecha-disc-dinamico" max="<?php echo date('Y-m-d'); ?>"></div>
                  <div class="col-sm-2"><label style="display:block;">&nbsp;</label><button type="button" class="btn btn-danger btn-remove-disc-modal"><i><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px;height:15px;"></i></button></div>
                </div>`;
            let $fila = $(htmlNueva);
            $('.fila-disc-modal').last().after($fila);
            if (id_guardado) $fila.find('.select-disc-dinamico').val(id_guardado);
            if (fecha_guardada) $fila.find('.input-fecha-disc-dinamico').val(fecha_guardada);
          }

          $('#btn_add_disc_row').on('click', function(e) {
            e.preventDefault();
            const $ultima = $('.fila-disc-modal').last();
            if (!$ultima.find('.select-disc-dinamico').val()) {
              mostrarAviso("⚠️ Debe seleccionar una discapacidad en la fila actual antes de añadir otra.");
              return;
            }
            agregarFilaDiscapacidad();
          });

          $(document).on('click', '.btn-remove-disc-modal', function(e) {
            e.preventDefault();
            const $fila = $(this).closest('.fila-disc-modal');
            const tieneDatos = $fila.find('.select-disc-dinamico').val() || $fila.find('.input-fecha-disc-dinamico').val();
            if (tieneDatos) {
              $fila.find('.select-disc-dinamico').val('');
              $fila.find('.input-fecha-disc-dinamico').val('');
              return;
            }
            if ($('.fila-disc-modal').length <= 1) return;
            $fila.remove();
          });

          $(document).on('change', '.select-disc-dinamico', function() {
            let val = $(this).val();
            if (!val) return;
            if ($('.select-disc-dinamico').filter(function() {
                return $(this).val() === val;
              }).length > 1) {
              mostrarAviso("⚠️ Esta discapacidad ya fue seleccionada en otra fila.");
              $(this).val("");
            }
          });

          $('#btn_modal_disc').on('click', function() {
            $('.fila-disc-modal').not(':first').remove();
            $('#select_disc_unica').html(construirOpcionesDiscapacidades()).val('');
            $('#fecha_disc_unica').val('');
            if (Array.isArray(discapacidadesData) && discapacidadesData.length > 0) {
              $('#select_disc_unica').val(discapacidadesData[0].id);
              $('#fecha_disc_unica').val(discapacidadesData[0].fecha || '');
              for (let i = 1; i < discapacidadesData.length; i++) {
                agregarFilaDiscapacidad(discapacidadesData[i].id, discapacidadesData[i].fecha || '');
              }
            } else {
              const dataGuardada = $('#discapacidades_ids').val();
              if (dataGuardada && dataGuardada.trim() !== "" && dataGuardada !== "[]") {
                try {
                  const lista = JSON.parse(dataGuardada);
                  if (Array.isArray(lista) && lista.length > 0) {
                    $('#select_disc_unica').val(lista[0].id);
                    if (lista[0].fecha) $('#fecha_disc_unica').val(lista[0].fecha);
                    for (let i = 1; i < lista.length; i++) agregarFilaDiscapacidad(lista[i].id, lista[i].fecha);
                  }
                } catch (e) {}
              }
            }
          });

          $('#guardar_disc_listo').click(function(e) {
            e.preventDefault();
            let seleccionadas = [],
              nombres = [],
              filaVacia = false;
            $('.fila-disc-modal').each(function() {
              let $sel = $(this).find('.select-disc-dinamico');
              let id = $sel.val();
              let fecha = $(this).find('.input-fecha-disc-dinamico').val();
              if (!id) {
                if ($('.fila-disc-modal').length > 1) filaVacia = true;
                return;
              }
              let nombre = $sel.find('option:selected').data('nombre') || '';
              seleccionadas.push({
                id: parseInt(id),
                nombre: nombre,
                fecha: fecha
              });
              nombres.push(nombre);
            });
            if (filaVacia) {
              mostrarAviso("⚠️ Hay filas de discapacidades sin seleccionar.");
              return false;
            }
            $('#discapacidades_ids').val(JSON.stringify(seleccionadas));
            let boton = $('#btn_modal_disc');
            if (nombres.length > 0) boton.attr('data-original-title', nombres.join(', ')).tooltip('fixTitle').removeClass('input-error');
            else boton.attr('data-original-title', 'Ninguna seleccionada').tooltip('fixTitle');
            $('#modalDiscapacidades').modal('hide');
          });

          $('#cancelar_disc_modal').on('click', function(e) {
            e.preventDefault();
            $('#modalDiscapacidades').modal('hide');
          });

          $('#discapacidad').on('change', function() {
            const siTiene = this.value === 'Si';
            $('#btn_modal_disc').prop('disabled', !siTiene);
            if (!siTiene) {
              $('#discapacidades_ids').val('[]');
              $('#btn_modal_disc').attr('data-original-title', 'Ninguna seleccionada').tooltip('fixTitle');
            } else if (Array.isArray(discapacidadesData) && discapacidadesData.length > 0) {
              let nombres = discapacidadesData.map(d => d.nombre);
              $('#btn_modal_disc').attr('data-original-title', nombres.join(', ')).tooltip('fixTitle');
              $('#discapacidades_ids').val(JSON.stringify(discapacidadesData));
            }
          });

          $(document).ready(function() {
            if ($('#discapacidad').val() === 'Si') {
              $('#btn_modal_disc').prop('disabled', false);
              if (Array.isArray(discapacidadesData) && discapacidadesData.length > 0) {
                $('#btn_modal_disc').attr('data-original-title', discapacidadesData.map(d => d.nombre).join(', ')).tooltip('fixTitle');
                $('#discapacidades_ids').val(JSON.stringify(discapacidadesData));
              }
            } else {
              $('#btn_modal_disc').prop('disabled', true);
            }
          });

          function actualizarBotonRepresentantes() {
            const repIdsRaw = $('#representantes_ids').val();
            let repLista = [];
            try {
              repLista = JSON.parse(repIdsRaw || '[]');
            } catch (e) {
              repLista = [];
            }

            const $btn = $('#btn_modal_rep');
            const hayRepresentantes = Array.isArray(repLista) && repLista.length > 0;

            if (hayRepresentantes) {
              const nombres = repLista.map(function(r) {
                let txt = r.nombre + ' (' + (r.tipo || 'Legal');
                if (r.parentesco && String(r.parentesco).trim() !== '') {
                  txt += ' - ' + r.parentesco;
                }
                txt += ')';
                return txt;
              });

              const tooltipTexto = nombres.length === 1 ?
                nombres[0] :
                nombres.length + ' representantes: ' + nombres.join(', ');

              $btn
                .attr('data-original-title', tooltipTexto)
                .tooltip('fixTitle')
                .removeClass('input-error btn-second')
                .addClass('btn-info')
                .html('<i></i> Gestionar Representantes');
            } else {
              $btn
                .attr('data-original-title', 'Sin Representantes')
                .tooltip('fixTitle')
                .removeClass('btn-info')
                .addClass('btn-second')
                .html('<i></i> Gestionar Representantes');
            }
          }

          document.addEventListener('DOMContentLoaded', function() {
            const inputRep = document.getElementById('representantes_ids');
            if (inputRep) {
              const observer = new MutationObserver(function() {
                actualizarBotonRepresentantes();
              });
              observer.observe(inputRep, {
                attributes: true,
                attributeFilter: ['value']
              });

              actualizarBotonRepresentantes();
            }
          });

          $(document).on('hidden.bs.modal', '#modalRepresentantes', function() {
            setTimeout(actualizarBotonRepresentantes, 50);
          });

          $(document).on('click', '#guardar_rep_listo', function() {
            setTimeout(actualizarBotonRepresentantes, 100);
          });

          $(document).ready(function() {
            if (Array.isArray(representantesData) && representantesData.length > 0) {
              $('#representantes_ids').val(JSON.stringify(representantesData));
            }

            actualizarBotonRepresentantes();

            const repIdsRaw = $('#representantes_ids').val();
            let repLista = [];
            try {
              repLista = JSON.parse(repIdsRaw || '[]');
            } catch (e) {
              repLista = [];
            }
            if (!Array.isArray(repLista) || repLista.length === 0) {
              $('#btn_modal_rep').addClass('input-error');
            } else {
              $('#btn_modal_rep').removeClass('input-error');
            }
          });

          async function validarPestana(tabSelector) {
            let esValido = true;
            let errores = [];
            limpiarErroresPestana(tabSelector);

            $(tabSelector).find('[required]').each(function() {
              var $input = $(this);
              var valor = $input.val();
              if ($input.is(':visible') && !$input.prop('disabled')) {
                if (valor === null || valor.trim() === "" || valor.includes('--- Seleccione')) {
                  $input.addClass('input-error');
                  esValido = false;
                  errores.push(`El campo '${$input.prev('p').text().replace('(*):', '').replace('(*)', '').trim()}' es obligatorio.`);
                }
              }
            });

            if (tabSelector === '#info') {
              const cedulaValida = await validarDatosUnicos();
              if (!cedulaValida) esValido = false;

              const fechaNac = $('#fechaN').val();
              if (fechaNac) {
                const edadAnios = parseInt($('#edad').attr('data-edad-anios') || 'NaN');
                if (isNaN(edadAnios) || edadAnios < 0 || edadAnios >= 18) {
                  $('#fechaN').addClass('input-error');
                  esValido = false;
                  errores.push("El paciente debe ser menor de 18 años (Fecha de Nacimiento).");
                }
              } else {
                $('#fechaN').addClass('input-error');
                esValido = false;
                errores.push("La Fecha de Nacimiento es obligatoria.");
              }
            }

            if (tabSelector === '#ocupacion_estudios') {
              if (!$('#años_aprobados').prop('disabled')) {
                const años = parseInt($('#años_aprobados').val());
                const max = parseInt($('#años_aprobados').attr('max'));
                if (!isNaN(max) && !isNaN(años) && años > max) {
                  errores.push("Los Años Aprobados exceden el máximo permitido (" + max + " años).");
                  $('#años_aprobados').addClass('input-error');
                  esValido = false;
                }
              }
            }

            if (tabSelector === '#direccion') {
              if ($('#municipio').val().trim() === "") {
                $('#municipio').addClass('input-error');
                esValido = false;
                errores.push("Debe seleccionar un Municipio.");
              }
            }

            if (tabSelector === '#salud_otros') {
              const repIdsRaw = $('#representantes_ids').val();
              let repLista = [];
              try {
                repLista = JSON.parse(repIdsRaw || '[]');
              } catch (e) {
                repLista = [];
              }

              if (!Array.isArray(repLista) || repLista.length === 0) {
                $('#btn_modal_rep').addClass('input-error');
                errores.push("Debe registrar al menos un <b>Representante</b> (haga clic en 'Gestionar Representantes').");
                esValido = false;
              } else {
                $('#btn_modal_rep').removeClass('input-error');
              }

              // Validar grupo sanguíneo
              if ($('#grupo_sanguineo').val() === "") {
                $('#grupo_sanguineo').addClass('input-error');
                esValido = false;
                errores.push("Debe seleccionar un Grupo Sanguíneo.");
              }
            }

            if (!esValido && errores.length > 0) {
              mostrarAviso('⚠️ Errores de Formulario:<ul><li>' + errores.filter((v, i, a) => a.indexOf(v) === i).join('</li><li>') + '</li></ul>');
            }
            return esValido;
          }

          $('.next-tab').off('click').on('click', async function() {
            const $btn = $(this);
            const tabActualSelector = $btn.data('tab-actual');
            const tabSiguienteName = $btn.data('tab-siguiente');
            const nextTabLink = $(`.nav-tabs li[data-tab-name="${tabSiguienteName}"] a`);

            $btn.prop('disabled', true).text('Validando...');
            const esValido = await validarPestana(tabActualSelector);
            $btn.prop('disabled', false).text(tabSiguienteName === 'confirmar' ? 'Actualizar' : 'Siguiente');

            if (esValido) {
              if (tabSiguienteName === 'confirmar') {
                normalizarSiSinDatos();

                const cedulaFinalValida = await validarDatosUnicos();
                if (!cedulaFinalValida) {
                  mostrarAviso("⚠️ Conflicto de Cédula. Regrese a la pestaña 'Datos Personales' y corrija.");
                  return;
                }
                $('#modalGuardarPaciente').modal('show');
              } else {
                $('.nav-tabs li').removeClass('active');
                $('.tab-content .tab-pane').removeClass('active');
                $(`.nav-tabs li[data-tab-name="${tabSiguienteName}"]`).removeClass('disabled-tab').addClass('active');
                nextTabLink.tab('show');
                $('#' + tabSiguienteName).addClass('active');
              }
            }
          });

          $('.prev-tab').on('click', function() {
            const tabActualSelector = $(this).data('tab-actual');
            const tabAnteriorName = $(this).data('tab-anterior');
            if (tabAnteriorName) {
              const prevTabLink = $(`.nav-tabs li[data-tab-name="${tabAnteriorName}"] a`);
              const $anteriorTabLi = $(`.nav-tabs li[data-tab-name="${tabAnteriorName}"]`);
              $('.nav-tabs li').removeClass('active');
              $('.tab-content .tab-pane').removeClass('active');
              $anteriorTabLi.addClass('active');
              prevTabLink.tab('show');
              $('#' + tabAnteriorName).addClass('active');
            } else {
              $('#modalConfirmarRegreso').modal('show');
            }
          });

          $('#confirmarGuardadoFinal').on('click', function() {
            normalizarSiSinDatos();
            $('#modalGuardarPaciente').modal('hide');
            $('#formularioPaciente').submit();
          });

          function soloNumerosSinE(campo, maxDigitos) {
            if (!campo) return;
            campo.addEventListener("keydown", function(e) {
              const teclasPermitidas = ["Backspace", "Tab", "ArrowLeft", "ArrowRight", "Delete"];
              if (teclasPermitidas.includes(e.key)) return;
              if (e.key.toLowerCase() === "e") {
                e.preventDefault();
                return;
              }
              if (!/^[0-9]$/.test(e.key)) {
                e.preventDefault();
                return;
              }
              if (campo.value.length >= maxDigitos) {
                e.preventDefault();
              }
            });
            campo.addEventListener("input", function() {
              campo.value = campo.value.replace(/[^0-9]/g, "").slice(0, maxDigitos);
            });
          }
          soloNumerosSinE(document.getElementById("telefono_rep"), 7);
          soloNumerosSinE(document.getElementById("tiempo_residencia"), 4);

          function filtrarCedulaPorTipo() {
            const tipo = $('#tipo_cedula').val();
            const $ced = $('#cedula');
            let valor = $ced.val();
            let nuevoValor = valor;
            let maxLen = 20;
            if (tipo === 'V' || tipo === 'E') {
              nuevoValor = valor.replace(/[^0-9]/g, '');
              maxLen = 8;
            } else if (tipo === 'PN') {
              nuevoValor = valor.replace(/[^0-9]/g, '');
              maxLen = 20;
            } else if (tipo === 'RP') {
              nuevoValor = valor.replace(/[^0-9]/g, '');
              if (nuevoValor.length > 8) nuevoValor = nuevoValor.substring(0, 8) + '-' + nuevoValor.substring(8, 9);
              maxLen = 10;
            }
            $ced.attr('maxlength', maxLen).val(nuevoValor);
          }
          $('#cedula').on('input', filtrarCedulaPorTipo);
          $('#tipo_cedula').on('change', filtrarCedulaPorTipo);
          filtrarCedulaPorTipo();

          $('#cedula_rep').on('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8);
          });

          $(document).ready(function() {
            if ($('#discapacidad').val() === 'Si') {
              $('#btn_modal_disc').prop('disabled', false);
            }
          });
        </script>