<?php
if (!isset($_GET['Id']) || empty($_GET['Id'])) {
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }

  $_SESSION['mensaje_user_error'] = "Debe seleccionar una consulta válida para ver su información.";

  header("Location: pacientes_listado.php");
  exit();
}

$opciones_sintomas_global = "";
$conexion = new mysqli("localhost", "root", "", "cpt3db");
$conexion->set_charset("utf8");
$sql_sg = "SELECT Id_sintomas, nombre_sintoma FROM sintomas WHERE estatus = 1 ORDER BY nombre_sintoma ASC";
$res_sg = $conexion->query($sql_sg);
if ($res_sg) {
  while ($row = $res_sg->fetch_assoc()) {
    $id = $row['Id_sintomas'];
    $nombre = htmlspecialchars($row['nombre_sintoma']);
    $opciones_sintomas_global .= "<option value='$id'>$nombre</option>";
  }
}

if (isset($_POST['ajax_nueva_patologia'])) {
  $conexion = new mysqli("localhost", "root", "", "cpt3db");
  $conexion->set_charset("utf8");
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
  $conexion = new mysqli("localhost", "root", "", "cpt3db");
  $conexion->set_charset("utf8");
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
  $conexion = new mysqli("localhost", "root", "", "cpt3db");
  $conexion->set_charset("utf8");
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
  $conexion = new mysqli("localhost", "root", "", "cpt3db");
  $conexion->set_charset("utf8");
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

include('includes/headerNav2.php');
verificarAcceso('Editar Pacientes');
?>

<head>
  <title>Pacientes | Editar</title>
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
    <h1>
      Editar Paciente
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="#"><i class="fa fa-users"></i>Pacientes</a></li>
      <li class="active"><a href="#"><i class="fa fa-user-plus"></i>Editar</a></li>
    </ol>
  </section>

  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="nav-tabs-custom">
          <ul class="nav nav-tabs">
            <li class="active" data-tab-name="info"><a href="#info" data-toggle="tab">Datos Personales</a></li>
            <li data-tab-name="ocupacion_estudios" class="disabled-tab"><a href="#ocupacion_estudios" data-toggle="tab">Ocupacion Y Estudios Aprobados</a></li>
            <li data-tab-name="direccion" class="disabled-tab"><a href="#direccion" data-toggle="tab">Direccion de Residencia</a></li>
            <li data-tab-name="salud_otros" class="disabled-tab"><a href="#salud_otros" data-toggle="tab">Salud y Otros Datos</a></li>
          </ul>

          <form id="formularioPaciente" action="../../cfg/editar/editar_paciente.php" class="form-group" method="POST" novalidate>
            <div class="tab-content">
              <div class="tab-pane active" id="info">
                <?php
                include("../../cfg/conexion.php");

                $sql = "SELECT p.id, p.email, p.nombre, p.apellido, p.tipo_cedula, p.cedula,
                        p.fecha_nacimiento, p.genero,
                        tp.telefono, pt.prefijo, tp.Id_prefijo,
                        dp.situacion_conyugal, dp.etnia, dp.tipo_etnia, dp.analfabeta,
                        dp.profesion, dp.ocupacion, dp.nivel_instruccion, dp.mision,
                        dp.años_aprobados, dp.seguro_social,
                        hm.grupo_sanguineo,
                        d.avenida_calle, d.referencia, d.tiempo_residencia, d.Id_sector, d.tiempo,
                        ln.Id_municipio AS Id_Municipio_Nac,
                        munnac.Id_Estado AS Id_Estado_Nac,
                        estnac.Id_Pais AS Id_Pais_Nac,
                        dirsec.Id_Municipio AS Id_Municipio_Dir,
                        dirmun.Id_Estado AS Id_Estado_Dir,
                        GROUP_CONCAT(DISTINCT CONCAT(pat.Id_patologia, '::', pat.nombre_patologia, '::', pat.codigo_cie, '::', hp.fecha_registro) SEPARATOR '||') AS patologias_data,
                        GROUP_CONCAT(DISTINCT CONCAT(al.Id_alergias_conocidas, '::', al.nombre_alergia, '::', ha.fecha_registro) SEPARATOR '||') AS alergias_data
                FROM persona p
                LEFT JOIN telefonos_personas tp ON p.id = tp.Id_persona
                LEFT JOIN prefijos_telefonos pt ON tp.Id_prefijo = pt.Id
                LEFT JOIN historial_medico hm ON p.id = hm.Id_persona
                LEFT JOIN detalle_paciente dp ON p.id = dp.id_persona
                LEFT JOIN lugar_nacimiento ln ON p.id = ln.Id_persona
                LEFT JOIN municipio munnac ON ln.Id_municipio = munnac.Id_Municipio
                LEFT JOIN estado estnac ON munnac.Id_Estado = estnac.Id_Estado
                LEFT JOIN direccion d ON p.id = d.Id_persona
                LEFT JOIN sector dirsec ON d.Id_sector = dirsec.Id_Sector
                LEFT JOIN municipio dirmun ON dirsec.Id_Municipio = dirmun.Id_Municipio
                LEFT JOIN historial_patologias hp ON hm.id_historial = hp.Id_Historial
                LEFT JOIN patologias pat ON hp.Id_patologia = pat.Id_patologia
                LEFT JOIN historial_alergias ha ON hm.id_historial = ha.Id_Historial
                LEFT JOIN alergias_conocidas al ON ha.Id_alergia = al.Id_alergias_conocidas
                WHERE p.id = " . (int)$_GET['Id'] . "
                GROUP BY p.id";

                $resultado = $conexion->query($sql);
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

                $representantes_json = '[]';
                $sql_rep = "SELECT hr.Id_representante, p.nombre, p.apellido, hr.tipo_representante, hr.parentesco
                FROM historial_representantes hr
                INNER JOIN persona p ON hr.Id_representante = p.id
                WHERE hr.Id_paciente = $id_paciente AND hr.estatus = '1'";
                $res_rep = $conexion->query($sql_rep);
                if ($res_rep && $res_rep->num_rows > 0) {
                  $arr_rep = [];
                  while ($r = $res_rep->fetch_assoc()) {
                    $arr_rep[] = [
                      'id' => intval($r['Id_representante']),
                      'nombre' => trim($r['nombre'] . ' ' . $r['apellido']),
                      'tipo' => $r['tipo_representante'],
                      'parentesco' => $r['parentesco'] ?? ''
                    ];
                  }
                  $representantes_json = json_encode($arr_rep);
                }

                $id_prefijo = $row['Id_prefijo'] ?? '';
                $id_pais_nac = $row['Id_Pais_Nac'] ?? '';
                $id_estado_nac = $row['Id_Estado_Nac'] ?? '';
                $id_municipio_nac = $row['Id_Municipio_Nac'] ?? '';

                $id_estado_dir = $row['Id_Estado_Dir'] ?? '';
                $id_municipio_dir = $row['Id_Municipio_Dir'] ?? '';
                $id_sector_dir = $row['Id_sector'] ?? '';

                $patologias_data = $row['patologias_data'] ?? '';
                $alergias_data = $row['alergias_data'] ?? '';

                $tiene_discapacidad  = (count(json_decode($discapacidades_json, true))  > 0) ? 'Si' : 'No';
                $tiene_representante = (count(json_decode($representantes_json, true)) > 0) ? 'Si' : 'No';
                ?>

                <input type="hidden" name="Id" value="<?= $id_paciente; ?>">

                <section id="new" style="margin-bottom:6%;">
                  <div class="row">
                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Cédula</p>
                      <div class="input-group">
                        <span class="input-group-addon" style="padding: 0; border: none;">
                          <select name="tipo_cedula" id="tipo_cedula" class="form-control" style="width: 65px; height: 34px; font-weight: bold; background-color: #f4f4f4; border-right: none; border-top-right-radius: 0; border-bottom-right-radius: 0;">
                            <option value="V" <?php echo ($row['tipo_cedula'] == 'V' ? 'selected' : ''); ?>>V-</option>
                          </select>
                        </span>
                        <input type="text" class="form-control" value="<?php echo $row['cedula']; ?>" name="cedula" id="cedula" placeholder="N° de Cedula" maxlength="8" required>
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
                        <input type="date" class="form-control" value="<?php echo $row['fecha_nacimiento']; ?>" id="fechaN" name="fecha_nacimiento" onchange="calcularEdad()" max="<?php echo date('Y-m-d'); ?>" required style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                        <span class="input-group-addon" style="padding: 0; border: none;">
                          <input type="text" class="form-control" id="edad" name="edad" readonly placeholder="Edad" style="width: 80px; height: 34px; text-align: center; background-color: #f4f4f4; font-weight: bold; border-left: none; border-top-left-radius: 0; border-bottom-left-radius: 0;">
                        </span>
                      </div>
                    </div>

                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Sexo (*):</p>
                      <select name="genero" id="genero" class="form-control" required>
                        <option value="">--- Seleccione Un Genero ---</option>
                        <option value="Masculino" <?php echo ($row['genero'] == 'Masculino' ? 'selected' : ''); ?>>Masculino</option>
                        <option value="Femenino" <?php echo ($row['genero'] == 'Femenino' ? 'selected' : ''); ?>>Femenino</option>
                      </select>
                    </div>

                    <div class="col-md-4 col-sm-3 form-group">
                      <p>Situacion conyugal:</p>
                      <select name="situacion_conyugal" class="form-control">
                        <option value="">--- Seleccione ---</option>
                        <option value="Soltero" <?php echo ($row['situacion_conyugal'] == 'Soltero' ? 'selected' : ''); ?>>Soltero</option>
                        <option value="Casado" <?php echo ($row['situacion_conyugal'] == 'Casado' ? 'selected' : ''); ?>>Casado</option>
                        <option value="Viudo" <?php echo ($row['situacion_conyugal'] == 'Viudo' ? 'selected' : ''); ?>>Viudo</option>
                        <option value="Divorciado" <?php echo ($row['situacion_conyugal'] == 'Divorciado' ? 'selected' : ''); ?>>Divorciado</option>
                        <option value="Concubinato" <?php echo ($row['situacion_conyugal'] == 'Concubinato' ? 'selected' : ''); ?>>Concubinato</option>
                      </select>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Pais de nacimiento (*):</p>
                      <select name="pais_nacimiento" id="pais_nacimiento" class="form-control" required>
                        <option value="">--- Seleccione un Pais ---</option>
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
                          <select name="etnia" id="etnia" class="form-control" style="width: 70px; height: 34px; font-weight: bold; background-color: #f4f4f4; border-right: none; border-top-right-radius: 0; border-bottom-right-radius: 0;" required>
                            <option value="No" <?php echo ($row['etnia'] == 'No' ? 'selected' : ''); ?>>No</option>
                            <option value="Si" <?php echo ($row['etnia'] == 'Si' ? 'selected' : ''); ?>>Si</option>
                          </select>
                        </span>
                        <select name="tipo_etnia" id="tipo_etnia" class="form-control" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                          <option value="">--- Seleccione Una Etnia ---</option>
                          <option value="wayuu" <?php echo ($row['etnia'] == 'wayuu' ? 'selected' : ''); ?>>Wayuu</option>
                          <option value="añu" <?php echo ($row['etnia'] == 'añu' ? 'selected' : ''); ?>>Añu</option>
                          <option value="baniva" <?php echo ($row['etnia'] == 'baniva' ? 'selected' : ''); ?>>Baniva</option>
                          <option value="kurripako" <?php echo ($row['etnia'] == 'kurripako' ? 'selected' : ''); ?>>Kurripako</option>
                          <option value="piapoco" <?php echo ($row['etnia'] == 'piapoco' ? 'selected' : ''); ?>>Piapoco</option>
                          <option value="warekena" <?php echo ($row['etnia'] == 'warekena' ? 'selected' : ''); ?>>Warekena</option>
                          <option value="bare" <?php echo ($row['etnia'] == 'bare' ? 'selected' : ''); ?>>Bare</option>
                          <option value="pemon" <?php echo ($row['etnia'] == 'pemon' ? 'selected' : ''); ?>>Pemon</option>
                          <option value="kariña" <?php echo ($row['etnia'] == 'kariña' ? 'selected' : ''); ?>>Kariña</option>
                          <option value="panare" <?php echo ($row['etnia'] == 'panare' ? 'selected' : ''); ?>>Panare</option>
                          <option value="yukpa" <?php echo ($row['etnia'] == 'yukpa' ? 'selected' : ''); ?>>Yukpa</option>
                          <option value="japreira" <?php echo ($row['etnia'] == 'japreira' ? 'selected' : ''); ?>>Japreira</option>
                          <option value="yekuana" <?php echo ($row['etnia'] == 'yekuana' ? 'selected' : ''); ?>>Yekuana</option>
                          <option value="chaima" <?php echo ($row['etnia'] == 'chaima' ? 'selected' : ''); ?>>Chaima</option>
                          <option value="bari" <?php echo ($row['etnia'] == 'bari' ? 'selected' : ''); ?>>Bari</option>
                          <option value="yanomami" <?php echo ($row['etnia'] == 'yanomami' ? 'selected' : ''); ?>>Yanomami</option>
                          <option value="sanema" <?php echo ($row['etnia'] == 'sanema' ? 'selected' : ''); ?>>Sanema</option>
                          <option value="warao" <?php echo ($row['etnia'] == 'warao' ? 'selected' : ''); ?>>Warao</option>
                          <option value="pume" <?php echo ($row['etnia'] == 'pume' ? 'selected' : ''); ?>>Pume</option>
                          <option value="piaroa" <?php echo ($row['etnia'] == 'piaroa' ? 'selected' : ''); ?>>Piaroa</option>
                          <option value="otro" <?php echo ($row['etnia'] == 'otro' ? 'selected' : ''); ?>>Otro/No Aplica</option>
                        </select>
                      </div>
                    </div>

                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Analfabeta (*):</p>
                      <select name="analfabeta" id="analfabeta" class="form-control" required>
                        <option value="">--- Seleccione Una Opcion ---</option>
                        <option value="Si" <?php echo ($row['analfabeta'] == 'Si' ? 'selected' : ''); ?>>Si</option>
                        <option value="No" <?php echo ($row['analfabeta'] == 'No' ? 'selected' : ''); ?>>No</option>
                      </select>
                    </div>

                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Cotiza seguro social:</p>
                      <select name="seguro_social" class="form-control">
                        <option value="">--- Seleccione Una Opcion ---</option>
                        <option value="Si" <?php echo ($row['seguro_social'] == 'Si' ? 'selected' : ''); ?>>Si</option>
                        <option value="No" <?php echo ($row['seguro_social'] == 'No' ? 'selected' : ''); ?>>No</option>
                      </select>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Telefono (*):</p>
                      <div class="input-group">
                        <span class="input-group-addon" style="padding: 0; border: none;">
                          <select name="prefijo" class="form-control" style="width: 80px; height: 34px; font-weight: bold; background-color: #f4f4f4; border-right: none; border-top-right-radius: 0; border-bottom-right-radius: 0;">
                            <option value="" disabled selected>Prefijo</option>
                            <?php
                            $sql = $conexion->query("SELECT * FROM prefijos_telefonos");
                            while ($resultado = $sql->fetch_assoc()) {
                              $selected = ($resultado["Id"] == $id_prefijo) ? 'selected' : '';
                              echo "<option value='" . $resultado["Id"] . "' {$selected}>" . $resultado['prefijo'] . "</option>";
                            }
                            ?>
                          </select>
                        </span>
                        <input type="text" class="form-control" name="telefono" value="<?php echo $row['telefono']; ?>" id="telefono" placeholder="N° De Telefono" minlength="7" maxlength="7" required>
                      </div>
                    </div>

                    <div class="col-md-8 col-sm-8 form-group">
                      <p>Correo (*):</p>
                      <input type="email" class="form-control" name="email" value="<?php echo $row['email']; ?>" id="email" placeholder="nombreapellido2@gmail.com" required>
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
                        <option value="">--- Seleccione Un Nivel De Instruccion ---</option>
                        <option value="sin_instruccion" <?php echo ($row['nivel_instruccion'] == 'sin_instruccion' ? 'selected' : ''); ?>>Sin Instrucción</option>
                        <option value="primaria_incompleta" <?php echo ($row['nivel_instruccion'] == 'primaria_incompleta' ? 'selected' : ''); ?>>Primaria Incompleta</option>
                        <option value="primaria_completa" <?php echo ($row['nivel_instruccion'] == 'primaria_completa' ? 'selected' : ''); ?>>Primaria Completa</option>
                        <option value="bachillerato_incompleto" <?php echo ($row['nivel_instruccion'] == 'bachillerato_incompleto' ? 'selected' : ''); ?>>Educación Media Incompleta</option>
                        <option value="bachiller" <?php echo ($row['nivel_instruccion'] == 'bachiller' ? 'selected' : ''); ?>>Bachiller (Media General)</option>
                        <option value="tecnico_medio" <?php echo ($row['nivel_instruccion'] == 'tecnico_medio' ? 'selected' : ''); ?>>Técnico Medio</option>
                        <option value="tsu" <?php echo ($row['nivel_instruccion'] == 'tsu' ? 'selected' : ''); ?>>Técnico Superior Universitario (T.S.U)</option>
                        <option value="universitario" <?php echo ($row['nivel_instruccion'] == 'universitario' ? 'selected' : ''); ?>>Universitario (Lic./Ing.)</option>
                        <option value="especializacion" <?php echo ($row['nivel_instruccion'] == 'especializacion' ? 'selected' : ''); ?>>Especialización</option>
                        <option value="maestria" <?php echo ($row['nivel_instruccion'] == 'maestria' ? 'selected' : ''); ?>>Maestría</option>
                        <option value="doctorado" <?php echo ($row['nivel_instruccion'] == 'doctorado' ? 'selected' : ''); ?>>Doctorado</option>
                      </select>
                    </div>

                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Profesión:</p>
                      <select name="profesion" id="profesion" class="form-control">
                        <option value="">--- Seleccione Una Profesion ---</option>
                        <optgroup label="Salud">
                          <option value="medico" <?php echo ($row['profesion'] == 'medico' ? 'selected' : ''); ?>>Médico / Especialista</option>
                          <option value="enfermero" <?php echo ($row['profesion'] == 'enfermero' ? 'selected' : ''); ?>>Enfermero(a)</option>
                          <option value="bioanalista" <?php echo ($row['profesion'] == 'bioanalista' ? 'selected' : ''); ?>>Bioanalista</option>
                          <option value="odontologo" <?php echo ($row['profesion'] == 'odontologo' ? 'selected' : ''); ?>>Odontólogo</option>
                          <option value="farmaceuta" <?php echo ($row['profesion'] == 'farmaceuta' ? 'selected' : ''); ?>>Farmacéutico(a)</option>
                        </optgroup>
                        <optgroup label="Ingeniería y Tecnología">
                          <option value="ing_civil" <?php echo ($row['profesion'] == 'ing_civil' ? 'selected' : ''); ?>>Ingeniero Civil</option>
                          <option value="ing_sistemas" <?php echo ($row['profesion'] == 'ing_sistemas' ? 'selected' : ''); ?>>Ingeniero de Sistemas / Computación</option>
                          <option value="ing_mecanico" <?php echo ($row['profesion'] == 'ing_mecanico' ? 'selected' : ''); ?>>Ingeniero Mecánico</option>
                          <option value="analista_it" <?php echo ($row['profesion'] == 'analista_it' ? 'selected' : ''); ?>>Analista de Soporte Técnico</option>
                          <option value="programador" <?php echo ($row['profesion'] == 'programador' ? 'selected' : ''); ?>>Programador / Desarrollador</option>
                        </optgroup>
                        <optgroup label="Educación y Social">
                          <option value="docente" <?php echo ($row['profesion'] == 'docente' ? 'selected' : ''); ?>>Docente</option>
                          <option value="abogado" <?php echo ($row['profesion'] == 'abogado' ? 'selected' : ''); ?>>Abogado(a)</option>
                          <option value="psicologo" <?php echo ($row['profesion'] == 'psicologo' ? 'selected' : ''); ?>>Psicólogo(a)</option>
                          <option value="trabajador_social" <?php echo ($row['profesion'] == 'trabajador_social' ? 'selected' : ''); ?>>Trabajador(a) Social</option>
                        </optgroup>
                        <optgroup label="Administración y Comercio">
                          <option value="administrador" <?php echo ($row['profesion'] == 'administrador' ? 'selected' : ''); ?>>Administrador(a)</option>
                          <option value="contador" <?php echo ($row['profesion'] == 'contador' ? 'selected' : ''); ?>>Contador Público</option>
                          <option value="vendedor" <?php echo ($row['profesion'] == 'vendedor' ? 'selected' : ''); ?>>Vendedor / Comerciante</option>
                        </optgroup>
                      </select>
                    </div>

                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Ocupación:</p>
                      <select name="ocupacion" id="ocupacion" class="form-control">
                        <option value="">--- Seleccione Una Ocupacion ---</option>
                        <optgroup label="Salud">
                          <option value="medico" <?php echo ($row['ocupacion'] == 'medico' ? 'selected' : ''); ?>>Médico / Especialista</option>
                          <option value="enfermero" <?php echo ($row['ocupacion'] == 'enfermero' ? 'selected' : ''); ?>>Enfermero(a)</option>
                          <option value="bioanalista" <?php echo ($row['ocupacion'] == 'bioanalista' ? 'selected' : ''); ?>>Bioanalista</option>
                          <option value="odontologo" <?php echo ($row['ocupacion'] == 'odontologo' ? 'selected' : ''); ?>>Odontólogo</option>
                          <option value="farmaceuta" <?php echo ($row['ocupacion'] == 'farmaceuta' ? 'selected' : ''); ?>>Farmacéutico(a)</option>
                        </optgroup>
                        <optgroup label="Ingeniería y Tecnología">
                          <option value="ing_civil" <?php echo ($row['ocupacion'] == 'ing_civil' ? 'selected' : ''); ?>>Ingeniero Civil</option>
                          <option value="ing_sistemas" <?php echo ($row['ocupacion'] == 'ing_sistemas' ? 'selected' : ''); ?>>Ingeniero de Sistemas / Computación</option>
                          <option value="ing_mecanico" <?php echo ($row['ocupacion'] == 'ing_mecanico' ? 'selected' : ''); ?>>Ingeniero Mecánico</option>
                          <option value="analista_it" <?php echo ($row['ocupacion'] == 'analista_it' ? 'selected' : ''); ?>>Analista de Soporte Técnico</option>
                          <option value="programador" <?php echo ($row['ocupacion'] == 'programador' ? 'selected' : ''); ?>>Programador / Desarrollador</option>
                        </optgroup>
                        <optgroup label="Educación y Social">
                          <option value="docente_primaria" <?php echo ($row['ocupacion'] == 'docente_primaria' ? 'selected' : ''); ?>>Docente de Primaria</option>
                          <option value="profesor_media" <?php echo ($row['ocupacion'] == 'profesor_media' ? 'selected' : ''); ?>>Profesor de Educación Media</option>
                          <option value="abogado" <?php echo ($row['ocupacion'] == 'abogado' ? 'selected' : ''); ?>>Abogado(a)</option>
                          <option value="psicologo" <?php echo ($row['ocupacion'] == 'psicologo' ? 'selected' : ''); ?>>Psicólogo(a)</option>
                          <option value="trabajador_social" <?php echo ($row['ocupacion'] == 'trabajador_social' ? 'selected' : ''); ?>>Trabajador(a) Social</option>
                        </optgroup>
                        <optgroup label="Administración y Comercio">
                          <option value="administrador" <?php echo ($row['ocupacion'] == 'administrador' ? 'selected' : ''); ?>>Administrador(a)</option>
                          <option value="contador" <?php echo ($row['ocupacion'] == 'contador' ? 'selected' : ''); ?>>Contador Público</option>
                          <option value="secretaria" <?php echo ($row['ocupacion'] == 'secretaria' ? 'selected' : ''); ?>>Asistente Administrativo / Secretaria</option>
                          <option value="vendedor" <?php echo ($row['ocupacion'] == 'vendedor' ? 'selected' : ''); ?>>Vendedor / Comerciante</option>
                          <option value="cajero" <?php echo ($row['ocupacion'] == 'cajero' ? 'selected' : ''); ?>>Cajero(a)</option>
                        </optgroup>
                        <optgroup label="Oficios y Otros">
                          <option value="albanil" <?php echo ($row['ocupacion'] == 'albanil' ? 'selected' : ''); ?>>Albañil / Constructor</option>
                          <option value="mecanico" <?php echo ($row['ocupacion'] == 'mecanico' ? 'selected' : ''); ?>>Mecánico Automotriz</option>
                          <option value="cocinero" <?php echo ($row['ocupacion'] == 'cocinero' ? 'selected' : ''); ?>>Cocinero / Chef</option>
                          <option value="chofer" <?php echo ($row['ocupacion'] == 'chofer' ? 'selected' : ''); ?>>Chofer / Transportista</option>
                          <option value="seguridad" <?php echo ($row['ocupacion'] == 'seguridad' ? 'selected' : ''); ?>>Oficial de Seguridad</option>
                          <option value="estudiante" <?php echo ($row['ocupacion'] == 'estudiante' ? 'selected' : ''); ?>>Estudiante</option>
                          <option value="ama_casa" <?php echo ($row['ocupacion'] == 'ama_casa' ? 'selected' : ''); ?>>Hogar / Ama de casa</option>
                        </optgroup>
                      </select>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Mision educativa:</p>
                      <select name="mision" id="mision" class="form-control">
                        <option value="">--- Seleccione Una Mision ---</option>
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
                      <p>Representante:</p>
                      <div style="display: flex; gap: 5px;">
                        <select name="representante" id="tiene_representante" class="form-control" style="width: 70px; flex-shrink: 0;" required>
                          <option value="No" <?php echo ($tiene_representante === 'No' ? 'selected' : ''); ?>>No</option>
                          <option value="Si" <?php echo ($tiene_representante === 'Si' ? 'selected' : ''); ?>>Si</option>
                        </select>
                        <button type="button" class="btn btn-info btn-block" id="btn_modal_rep" data-toggle="modal" data-placement="top" title="Sin Representantes" data-target="#modalRepresentantes" style="flex-grow: 1; margin: 0;">
                          <i></i> Gestionar Representantes
                        </button>
                      </div>
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
                      <p>Grupo Sanguineo (*):</p>
                      <select name="grupo_sanguineo" class="form-control" required>
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
          </form>
        </div>
      </div>
    </div>
  </section>

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

  <div class="modal fade" id="modalBuscarSintoma" role="dialog" style="z-index: 1070;">
    <div class="modal-dialog modal-md" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h4 class="modal-title">Buscar Síntoma</h4>
        </div>
        <div class="modal-body">
          <input type="text" id="inputBuscarSintoma" class="form-control" placeholder="Escriba para filtrar...">
          <div class="list-group" id="listaResultadosSintoma" style="margin-top: 10px; max-height: 200px; overflow-y: auto;"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalNuevaAlergia" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h4 class="modal-title"><i class="fa fa-plus-circle"></i> Nueva Alergia</h4>
        </div>
        <div class="modal-body">
          <form id="formAjaxAlergia">
            <div class="form-group">
              <label>Nombre de la Alergía (*):</label>
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

  <div class="modal fade" id="avisoModal" tabindex="-1" role="dialog" aria-labelledby="avisoModalLabel">
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

  <div class="modal fade" id="modalConfirmarRegreso" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarRegresoLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h4 class="modal-title" id="modalConfirmarRegresoLabel"><i class="fa fa-sign-out"></i>Confirmación de Regreso</h4>
        </div>
        <div class="modal-body">
          <p>Al hacer clic en "Abandonar Formulario", perderá todos los datos no guardados. ¿Desea continuar?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Regresar</button>
          <a href="pacientes_listado.php" class="btn btn-danger">Abandonar Formulario</a>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalGuardarPaciente" tabindex="-1" role="dialog" aria-labelledby="modalGuardarPacienteLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-green">
          <h4 class="modal-title" id="modalGuardarPacienteLabel"><i class="fa fa-save"></i>Confirmación de Guardado</h4>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea actualizar la información del paciente?</p>
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

<?php
$niveles_map = [
  'sin_instruccion'         => 0,
  'primaria_incompleta'     => 1,
  'primaria_completa'       => 2,
  'bachillerato_incompleto' => 3,
  'bachiller'               => 4,
  'tecnico_medio'           => 5,
  'tsu'                     => 6,
  'universitario'           => 7,
  'especializacion'         => 8,
  'maestria'                => 9,
  'doctorado'               => 10,
];

$requisitos_profesion = [
  'medico' => 7, 'enfermero' => 7, 'bioanalista' => 7, 'odontologo' => 7, 'farmaceuta' => 7,
  'ing_civil' => 7, 'ing_sistemas' => 7, 'ing_mecanico' => 7,
  'analista_it' => 6, 'programador' => 6,
  'docente' => 7, 'abogado' => 7, 'psicologo' => 7, 'trabajador_social' => 7,
  'administrador' => 7, 'contador' => 7, 'vendedor' => 4,
];

$requisitos_ocupacion = [
  'medico' => 7, 'enfermero' => 7, 'bioanalista' => 7, 'odontologo' => 7, 'farmaceuta' => 7,
  'ing_civil' => 7, 'ing_sistemas' => 7, 'ing_mecanico' => 7,
  'analista_it' => 6, 'programador' => 6,
  'docente_primaria' => 7, 'profesor_media' => 7, 'abogado' => 7, 'psicologo' => 7, 'trabajador_social' => 7,
  'administrador' => 7, 'contador' => 7, 'secretaria' => 4, 'vendedor' => 4, 'cajero' => 4,
  'albanil' => 0, 'mecanico' => 0, 'cocinero' => 0, 'chofer' => 0,
  'seguridad' => 0, 'estudiante' => 0, 'ama_casa' => 0,
];

$nivel_actual_val = $niveles_map[$row['nivel_instruccion']] ?? -1;
$prof_actual_val  = $requisitos_profesion[$row['profesion']] ?? -1;
$ocup_actual_val  = $requisitos_ocupacion[$row['ocupacion']] ?? -1;

$prof_excepcion = ($row['profesion'] !== '' && $nivel_actual_val >= 0 && $nivel_actual_val < $prof_actual_val);
$ocup_excepcion = ($row['ocupacion'] !== '' && $nivel_actual_val >= 0 && $nivel_actual_val < $ocup_actual_val);
?>

<script>
  window.PARENTESCOS_DISPONIBLES = [{
      val: 'Hijo/a',
      txt: 'Hijo/a'
    },
    {
      val: 'Cónyuge',
      txt: 'Cónyuge'
    },
    {
      val: 'Hermano/a',
      txt: 'Hermano/a'
    },
    {
      val: 'Sobrino/a',
      txt: 'Sobrino/a'
    },
    {
      val: 'Padre',
      txt: 'Padre'
    },
    {
      val: 'Madre',
      txt: 'Madre'
    },
    {
      val: 'Cuidador/a',
      txt: 'Cuidador/a'
    },
    {
      val: 'Apoderado Legal',
      txt: 'Apoderado Legal'
    },
    {
      val: 'Tutor Legal',
      txt: 'Tutor Legal'
    },
    {
      val: 'Otro',
      txt: 'Otro'
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
  const discapacidadesData = <?php echo $discapacidades_json ?? '[]'; ?>;
  const representantesData = <?php echo $representantes_json ?? '[]'; ?>;
  const idActualRegistro = document.querySelector('input[name="Id"]').value;

  window.mostrarAviso = function(mensaje) {
    $('#avisoModalHeader')
      .removeClass('bg-green')
      .addClass('bg-crimson');
    $('#avisoModalLabel').html('<i class="fa fa-warning"></i> Aviso Importante');
    $('#avisoTexto').html(mensaje);
    $('#avisoModal').modal('show');
  };

  window.mostrarAvisoExito = function(mensaje) {
    $('#avisoModalHeader')
      .removeClass('bg-crimson')
      .addClass('bg-green');
    $('#avisoModalLabel').html('<i class="fa fa-check"></i> Operación Exitosa');
    $('#avisoTexto').html(mensaje);
    $('#avisoModal').modal('show');
  };

  $('#avisoModal').on('hidden.bs.modal', function() {
    $('#avisoModalHeader')
      .removeClass('bg-green')
      .addClass('bg-crimson');
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
    if (tabSelector === '#info') {}
  }

  function setMaxDateFor18Years() {
    const today = new Date();
    today.setFullYear(today.getFullYear() - 18);
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    const maxDate = `${year}-${month}-${day}`;

    document.getElementById('fechaN').setAttribute('max', maxDate);
  }

  setMaxDateFor18Years();

  function calcularEdad() {
    var fechaNacimiento = $('#fechaN').val();
    var campoEdad = $('#edad');
    var $fechaInput = $('#fechaN');

    if (fechaNacimiento) {
      var fechaNac = new Date(fechaNacimiento + 'T00:00:00');
      var hoy = new Date();
      hoy.setHours(0, 0, 0, 0);

      if (fechaNac > hoy) {
        mostrarAviso("⚠️ La fecha de nacimiento no puede ser posterior a la fecha actual.");
        $fechaInput.val("");
        $fechaInput.addClass('input-error');
        campoEdad.val("");
        return;
      }

      if (fechaNac.getFullYear() < 1900) {
        mostrarAviso("⚠️ La fecha de nacimiento no puede ser anterior a 1900.");
        $fechaInput.val("");
        $fechaInput.addClass('input-error');
        campoEdad.val("");
        return;
      }

      $fechaInput.removeClass('input-error');

      // 3. Cálculo normal de la edad
      var edad = hoy.getFullYear() - fechaNac.getFullYear();
      var m = hoy.getMonth() - fechaNac.getMonth();
      if (m < 0 || (m === 0 && hoy.getDate() < fechaNac.getDate())) {
        edad--;
      }

      if (edad < 18) {
        mostrarAviso("⚠️ Este campo es para adultos. La edad calculada es " + edad + " años.");
        campoEdad.val("");
      } else {
        campoEdad.val(edad);
      }
    } else {
      campoEdad.val("");
    }
  }

  $('#fechaN').on('change', function() {
    calcularEdad();
  });
  calcularEdad();

  $('#fechaN').on('input', function() {
    var $input = $(this);
    var valor = $input.val();
    if (!valor) return;

    var anio = parseInt(valor.split('-')[0]);
    var anioActual = new Date().getFullYear();

    if (anio > anioActual) {
      mostrarAviso("⚠️ El año no puede ser mayor a " + anioActual + ".");
      $input.val("");
      $('#edad').val("");
      $input.addClass('input-error');
    } else if (anio < 1900) {
      mostrarAviso("⚠️ El año no puede ser menor a 1900.");
      $input.val("");
      $('#edad').val("");
      $input.addClass('input-error');
    } else {
      $input.removeClass('input-error');
    }
  });

  async function verificarDatosUnicosBD(tipo, cedula, email, idPersona) {
    return new Promise((resolve) => {
      if (cedula === "" && email === "") {
        resolve({
          existe_cedula: false,
          existe_email: false
        });
        return;
      }
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
    const cedulaInput = document.getElementById('cedula') || document.getElementById('cedula_rep');
    const tipoSelect = document.getElementById('tipo_cedula') || document.getElementById('tipo_cedula_rep');
    const emailInput = document.getElementById('email') || document.getElementById('email_rep');

    const cedula = cedulaInput ? cedulaInput.value.trim() : "";
    const tipo = tipoSelect ? tipoSelect.value : "";
    const email = emailInput ? emailInput.value.trim() : "";
    let idActual = 0;
    const inputId = document.querySelector('input[name="Id_representante"]') || document.querySelector('input[name="Id"]');

    if (inputId && inputId.value) idActual = inputId.value;

    let esValido = true;
    let errores = [];

    if (cedulaInput) $(cedulaInput).removeClass('input-error');
    if (tipoSelect) $(tipoSelect).removeClass('input-error');
    if (emailInput) $(emailInput).removeClass('input-error');

    const esPaciente = document.getElementById('cedula');
    if (esPaciente && cedula === '') {
      $(esPaciente).addClass('input-error');
      errores.push("El campo de Cédula es obligatorio.");
      return false;
    }

    if (email !== "" && (email.indexOf('@') === -1 || email.indexOf('.') === -1)) {
      if (emailInput) $(emailInput).addClass('input-error');
      errores.push("El campo Email debe tener un formato válido (ej: nombreapellido2@gmail.com).");
      esValido = false;
    }

    if (cedula !== "" && !isNaN(parseInt(cedula))) {
      const cedNum = parseInt(cedula);
      if (tipo === 'V' && cedNum > 80000000) {
        if (cedulaInput) $(cedulaInput).addClass('input-error');
        if (tipoSelect) $(tipoSelect).addClass('input-error');
        errores.push("Para tipo V-, la cédula no puede ser mayor a 80.000.000.");
        esValido = false;
      } else if (tipo === 'E' && cedNum < 80000000) {
        if (cedulaInput) $(cedulaInput).addClass('input-error');
        if (tipoSelect) $(tipoSelect).addClass('input-error');
        errores.push("Para tipo E-, la cédula no puede ser menor a 80.000.000.");
        esValido = false;
      }
    }

    if (!esValido) {
      mostrarAviso("⚠️ <b>Errores de formato:</b><br>" + errores.join("<br>"));
      return false;
    }

    if (cedula !== "" || email !== "") {
      const bd = await verificarDatosUnicosBD(tipo, cedula, email, idActual);

      if (bd.existe_cedula) {
        if (cedulaInput) $(cedulaInput).addClass('input-error');
        if (tipoSelect) $(tipoSelect).addClass('input-error');
        errores.push(`El documento <b>${tipo}-${cedula}</b> ya se encuentra registrado en el sistema.`);
        esValido = false;
      }

      if (bd.existe_email) {
        if (emailInput) $(emailInput).addClass('input-error');
        errores.push(`El correo <b>${email}</b> ya está siendo usado por otra persona.`);
        esValido = false;
      }

      if (!esValido) {
        mostrarAviso("⚠️ <b>Datos duplicados detectados:</b><br>" + errores.join("<br>"));
      }
    }

    return esValido;
  }

  if (document.getElementById('cedula')) document.getElementById('cedula').addEventListener('blur', validarDatosUnicos);
  if (document.getElementById('tipo_cedula')) document.getElementById('tipo_cedula').addEventListener('change', validarDatosUnicos);
  if (document.getElementById('email')) document.getElementById('email').addEventListener('blur', validarDatosUnicos);
  if (document.getElementById('email_rep')) document.getElementById('email_rep').addEventListener('blur', validarDatosUnicos);

  $('#email').on('blur', function() {
    var val = $(this).val().trim();
    if (val === "") {
      $(this).addClass('input-error');
    } else {
      $(this).removeClass('input-error');
    }
  });

  $('#email').on('input', function() {
    if ($(this).val().trim() !== "") {
      $(this).removeClass('input-error');
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
            var estado = data[i];
            estadoSelect.innerHTML += '<option value="' + estado.Id_Estado + '">' + estado.nombre_estado + '</option>';
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
            var municipio = data[i];
            municipioSelect.innerHTML += '<option value="' + municipio.Id_Municipio + '">' + municipio.nombre_municipio + '</option>';
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
    if (sectorSelect) {
      sectorSelect.innerHTML = '<option value="">--- Seleccione Un Sector ---</option>';
    }

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
            var municipio = data[i];
            municipioSelect.innerHTML += '<option value="' + municipio.Id_Municipio + '">' + municipio.nombre_municipio + '</option>';
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
            var sector = data[i];
            sectorSelect.innerHTML += '<option value="' + sector.Id_Sector + '">' + sector.nombre_sector + '</option>';
          }
        }
      });
    }
  });

  document.addEventListener('DOMContentLoaded', () => {
    if (typeof parseData === 'function') {
      inicializarDatosMedicos();
    }
  });

  const selectAnalfabeta = document.getElementById('analfabeta');
  const nivelInstruccion = document.getElementById('nivel_instruccion');
  const mision = document.getElementById('mision');
  const ocupacion = document.getElementById('ocupacion');
  const profesion = document.getElementById('profesion');
  const añosAprobados = document.getElementById('años_aprobados');
  const selectEtnia = document.getElementById('etnia');
  const tipoEtnia = document.getElementById('tipo_etnia');
  const selectDiscapacidad = document.getElementById('discapacidad');
  const tipoDiscapacidad = document.getElementById('tipo_discapacidad');
  const selectTieneRepresentante = document.getElementById('tiene_representante');

  const limitesMision = {
    "robinson": 2,
    "ribas": 5,
    "sucre": 6
  };

  const limitesNivel = {
    "bachiller": 5,
    "TSU": 3,
    "licenciatura": 5
  };

  function toggleOptions() {
    const esAnalfabeta = selectAnalfabeta.value === 'Si';
    const noEsAnalfabeta = selectAnalfabeta.value === 'No';
    const siTieneEtnia = selectEtnia.value === 'Si';
    const noTieneEtnia = selectEtnia.value === 'No';
    const siTieneDiscapacidad = selectDiscapacidad.value === 'Si';
    const noTieneDiscapacidad = selectDiscapacidad.value === 'No';
    const siTieneRepresentante = selectTieneRepresentante ? selectTieneRepresentante.value === 'Si' : false;
    const noTieneRepresentante = selectTieneRepresentante ? selectTieneRepresentante.value === 'No' : false;

    /* ── Analfabeta: bloquea ocupación/profesión/nivel y habilita misión/años ── */
    if (esAnalfabeta) {
      $('#ocupacion, #profesion, #nivel_instruccion').prop('disabled', true);
      $('#mision, #años_aprobados').prop('disabled', false);
    } else if (noEsAnalfabeta) {
      $('#ocupacion, #profesion, #nivel_instruccion').prop('disabled', false);
      $('#mision, #años_aprobados').prop('disabled', true);
      $('#años_help').text('');
    } else {
      $('#ocupacion, #profesion, #nivel_instruccion, #mision, #años_aprobados').prop('disabled', false);
    }

    /* ── Etnia: habilita el sub-select solo si es "Si" ── */
    if (siTieneEtnia) {
      $('#tipo_etnia').prop('disabled', false);
    } else if (noTieneEtnia) {
      $('#tipo_etnia').prop('disabled', true);
    } else {
      $('#tipo_etnia').prop('disabled', false);
    }

    /* ── Discapacidad: habilita/deshabilita el botón ── */
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

    /* ── Representante: habilita/deshabilita el botón ── */
    if (siTieneRepresentante) {
      $('#btn_modal_rep').prop('disabled', false).removeClass('btn-second').addClass('btn-info');

      if (typeof representantesData !== 'undefined' && Array.isArray(representantesData) && representantesData.length > 0) {
        if (!$('#representantes_ids').val() || $('#representantes_ids').val() === '[]') {
          let nombres = representantesData.map(r => r.nombre + ' (' + r.tipo + ')');
          $('#representantes_ids').val(JSON.stringify(representantesData));
          $('#btn_modal_rep').attr('data-original-title', nombres.join(', ')).tooltip('fixTitle');
        }
      }
    } else if (noTieneRepresentante) {
      $('#btn_modal_rep').prop('disabled', true).removeClass('btn-info').addClass('btn-second');
      $('#representantes_ids').val('[]');
      $('#btn_modal_rep').attr('data-original-title', 'Sin Representantes').tooltip('fixTitle');
    }

    /* ── Filtro de instrucción (profesión/ocupación según nivel) ── */
    if (typeof window.__aplicarFiltroInstruccion === 'function') {
      setTimeout(window.__aplicarFiltroInstruccion, 0);
    }
  }

  $('#mision').on('change', function() {
    const seleccion = $(this).val();
    const maxAños = limitesMision[seleccion];

    if (maxAños) {
      $('#años_aprobados').attr('max', maxAños);
      $('#años_aprobados').attr('placeholder', 'Máx: ' + maxAños);
      $('#años_help').text('Duración máxima estimada para Misión ' + seleccion.charAt(0).toUpperCase() + seleccion.slice(1) + ': ' + maxAños + ' años.').css('color', '#777');

      const valorActual = parseInt($('#años_aprobados').val());
      if (valorActual > maxAños) {
        $('#años_aprobados').val(maxAños);
      }
    } else {
      $('#años_aprobados').removeAttr('max');
      $('#años_aprobados').attr('placeholder', '');
      $('#años_help').text('');
    }
  });

  $('#años_aprobados').on('input', function() {
    const max = parseInt($(this).attr('max'));
    const val = parseInt($(this).val());

    if (!isNaN(max) && val > max) {
      $(this).addClass('input-error');
      $('#años_help').text('⚠️ Error: No puede exceder ' + max + ' años para esta misión.').css('color', 'crimson');
    } else {
      $(this).removeClass('input-error');
      const misionSel = $('#mision').val();
      if (limitesMision[misionSel]) {
        $('#años_help').text('Duración máxima estimada para Misión ' + misionSel.charAt(0).toUpperCase() + misionSel.slice(1) + ': ' + limitesMision[misionSel] + ' años.').css('color', '#777');
      } else {
        $('#años_help').text('');
      }
    }
  });

  selectAnalfabeta.addEventListener('change', toggleOptions);
  selectEtnia.addEventListener('change', toggleOptions);
  selectDiscapacidad.addEventListener('change', toggleOptions);
  if (selectTieneRepresentante) {
    selectTieneRepresentante.addEventListener('change', toggleOptions);
  }

  if (selectTieneRepresentante) {
    selectTieneRepresentante.addEventListener('change', function() {
      toggleOptions();
      if (this.value === 'No') {
        $('#representantes_ids').val('[]');
        $('#btn_modal_rep')
          .prop('disabled', true)
          .removeClass('btn-info')
          .addClass('btn-second')
          .attr('data-original-title', 'Sin Representantes')
          .tooltip('fixTitle');
      }
    });
  }

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

  (function() {
    const NIVELES = {
      'sin_instruccion': 0,
      'primaria_incompleta': 1,
      'primaria_completa': 2,
      'bachillerato_incompleto': 3,
      'bachiller': 4,
      'tecnico_medio': 5,
      'tsu': 6,
      'universitario': 7,
      'especializacion': 8,
      'maestria': 9,
      'doctorado': 10
    };

    const REQUISITOS_PROFESION = <?php echo json_encode($requisitos_profesion); ?>;
    const REQUISITOS_OCUPACION = <?php echo json_encode($requisitos_ocupacion); ?>;

    const VALOR_PROF_GUARDADO = <?php echo json_encode($row['profesion'] ?? ''); ?>;
    const VALOR_OCUP_GUARDADO = <?php echo json_encode($row['ocupacion'] ?? ''); ?>;
    let PROF_EXCEPCION = <?php echo $prof_excepcion ? 'true' : 'false'; ?>;
    let OCUP_EXCEPCION = <?php echo $ocup_excepcion ? 'true' : 'false'; ?>;

    let htmlOriginalProfesion = null;
    let htmlOriginalOcupacion = null;

    function capturarOriginales() {
      const selProf = document.getElementById('profesion');
      const selOcup = document.getElementById('ocupacion');
      if (selProf && htmlOriginalProfesion === null) htmlOriginalProfesion = selProf.innerHTML;
      if (selOcup && htmlOriginalOcupacion === null) htmlOriginalOcupacion = selOcup.innerHTML;
    }

    function reconstruirSelect(select, htmlOriginal, requisitos, valorGuardado, esExcepcion, nivelNumerico) {
      if (!select || !htmlOriginal) return;

      const temp = document.createElement('select');
      temp.innerHTML = htmlOriginal;

      const nuevoSelect = document.createElement('select');
      nuevoSelect.name = select.name;
      nuevoSelect.id = select.id;
      nuevoSelect.className = select.className;
      Array.from(select.attributes).forEach(attr => {
        if (attr.name !== 'id' && attr.name !== 'name' && attr.name !== 'class') {
          nuevoSelect.setAttribute(attr.name, attr.value);
        }
      });

      Array.from(temp.children).forEach(child => {
        if (child.tagName === 'OPTION') {
          if (child.value === '') {
            nuevoSelect.appendChild(child.cloneNode(true));
            return;
          }
          const req = requisitos[child.value];
          const esExcepcionActual = esExcepcion && child.value === valorGuardado;
          const nivelOk = (nivelNumerico === -1) || (req === undefined) || (nivelNumerico >= req);
          if (nivelOk || esExcepcionActual) {
            nuevoSelect.appendChild(child.cloneNode(true));
          }
        } else if (child.tagName === 'OPTGROUP') {
          const grupoNuevo = document.createElement('optgroup');
          grupoNuevo.label = child.label;
          let tieneOpciones = false;

          Array.from(child.children).forEach(opt => {
            if (opt.tagName !== 'OPTION') return;
            const req = requisitos[opt.value];
            const esExcepcionActual = esExcepcion && opt.value === valorGuardado;
            const nivelOk = (nivelNumerico === -1) || (req === undefined) || (nivelNumerico >= req);
            if (nivelOk || esExcepcionActual) {
              grupoNuevo.appendChild(opt.cloneNode(true));
              tieneOpciones = true;
            }
          });

          if (tieneOpciones) nuevoSelect.appendChild(grupoNuevo);
        }
      });

      const padre = select.parentNode;
      padre.replaceChild(nuevoSelect, select);

      if (valorGuardado) {
        const optSel = nuevoSelect.querySelector('option[value="' + valorGuardado + '"]');
        if (optSel) nuevoSelect.value = valorGuardado;
      }
    }

    function aplicarFiltros() {
      capturarOriginales();

      const nivelSel = document.getElementById('nivel_instruccion');
      if (!nivelSel) return;
      const nivelVal = NIVELES[nivelSel.value];
      const nivelNumerico = (nivelVal === undefined) ? -1 : nivelVal;
      const selProf = document.getElementById('profesion');
      const selOcup = document.getElementById('ocupacion');
      const valorProfActual = selProf ? selProf.value : '';
      const valorOcupActual = selOcup ? selOcup.value : '';
      const valorGuardadoProf = (PROF_EXCEPCION && valorProfActual === VALOR_PROF_GUARDADO) ? VALOR_PROF_GUARDADO : '';
      const valorGuardadoOcup = (OCUP_EXCEPCION && valorOcupActual === VALOR_OCUP_GUARDADO) ? VALOR_OCUP_GUARDADO : '';

      reconstruirSelect(selProf, htmlOriginalProfesion, REQUISITOS_PROFESION, valorGuardadoProf, PROF_EXCEPCION, nivelNumerico);
      reconstruirSelect(selOcup, htmlOriginalOcupacion, REQUISITOS_OCUPACION, valorGuardadoOcup, OCUP_EXCEPCION, nivelNumerico);

      const nuevaProf = document.getElementById('profesion');
      if (nuevaProf && valorProfActual) {
        const o = nuevaProf.querySelector('option[value="' + valorProfActual + '"]');
        if (o) nuevaProf.value = valorProfActual;
      }
      const nuevaOcup = document.getElementById('ocupacion');
      if (nuevaOcup && valorOcupActual) {
        const o = nuevaOcup.querySelector('option[value="' + valorOcupActual + '"]');
        if (o) nuevaOcup.value = valorOcupActual;
      }
    }

    window.__aplicarFiltroInstruccion = aplicarFiltros;

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', aplicarFiltros);
    } else {
      aplicarFiltros();
    }

    document.addEventListener('change', function(e) {
      if (e.target && e.target.id === 'nivel_instruccion') {
        const selProf = document.getElementById('profesion');
        const selOcup = document.getElementById('ocupacion');
        const vp = selProf ? selProf.value : '';
        const vo = selOcup ? selOcup.value : '';

        PROF_EXCEPCION = false;
        OCUP_EXCEPCION = false;

        aplicarFiltros();

        const nuevaProf = document.getElementById('profesion');
        if (nuevaProf && vp && !nuevaProf.querySelector('option[value="' + vp + '"]')) {
          nuevaProf.value = '';
        }
        const nuevaOcup = document.getElementById('ocupacion');
        if (nuevaOcup && vo && !nuevaOcup.querySelector('option[value="' + vo + '"]')) {
          nuevaOcup.value = '';
        }
      }
    });
  })();

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
      $('#btn_modal_disc')
        .prop('disabled', true)
        .removeClass('btn-info')
        .addClass('btn-second')
        .attr('data-original-title', 'Ninguna seleccionada')
        .tooltip('fixTitle');
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
        $('#btn_modal_rep')
          .prop('disabled', true)
          .removeClass('btn-info')
          .addClass('btn-second')
          .attr('data-original-title', 'Sin Representantes')
          .tooltip('fixTitle');
        cambios.push('representante');
      }
    }

    const $selectEtnia = $('#etnia');
    if ($selectEtnia.val() === 'Si' && (!$('#tipo_etnia').val() || $('#tipo_etnia').val() === '')) {
      $selectEtnia.val('No');
      $('#tipo_etnia').val('').prop('disabled', true);
      cambios.push('etnia');
    }

    if (typeof toggleOptions === 'function') {
      toggleOptions();
    }

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
    listaPatologias
      .slice()
      .sort((a, b) => a.nombre.localeCompare(b.nombre))
      .forEach(p => {
        html += `<option value="${p.id}" data-nombre="${p.nombre}" data-cie="${p.cie || ''}">${p.nombre}</option>`;
      });
    return html;
  }

  function construirOpcionesAlergias() {
    let html = '<option value="">--- Seleccione una alergia ---</option>';
    listaAlergias
      .slice()
      .sort((a, b) => a.nombre.localeCompare(b.nombre))
      .forEach(a => {
        html += `<option value="${a.id}" data-nombre="${a.nombre}">${a.nombre}</option>`;
      });
    return html;
  }

  function construirOpcionesSintomas() {
    let html = '<option value="">--- Seleccione un síntoma ---</option>';
    listaSintomas
      .slice()
      .sort((a, b) => a.nombre.localeCompare(b.nombre))
      .forEach(s => {
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
    ?>

    <?php
    $q_init_ale = $conexion->query("SELECT Id_alergias_conocidas, nombre_alergia FROM alergias_conocidas WHERE estatus = 1 ORDER BY nombre_alergia ASC");
    if ($q_init_ale) {
      while ($a = $q_init_ale->fetch_assoc()) {
        echo "listaAlergias.push({id: " . intval($a['Id_alergias_conocidas']) . ", nombre: " . json_encode($a['nombre_alergia']) . "});\n";
      }
    }
    ?>

    <?php
    $q_init_sin = $conexion->query("SELECT Id_sintomas, nombre_sintoma FROM sintomas WHERE estatus = 1 ORDER BY nombre_sintoma ASC");
    if ($q_init_sin) {
      while ($s = $q_init_sin->fetch_assoc()) {
        echo "listaSintomas.push({id: " . intval($s['Id_sintomas']) . ", nombre: " . json_encode($s['nombre_sintoma']) . "});\n";
      }
    }
    ?>
  });

  function agregarFilaPatologia(id_guardado = "", fecha_guardada = "") {
    let optionsHtml = construirOpcionesPatologias();

    let htmlPat = `
      <div class="row fila-pat" style="margin-bottom: 10px;">
          <div class="col-sm-6">
              <div class="input-group">
                  <select class="form-control select-pat">
                      ${optionsHtml}
                  </select>
                  <span class="input-group-btn">
                      <button class="btn btn-info btn-search-pat" type="button" title="Buscar Patología">
                        <i><img src="../../recursos/imagenes/iconos/buscar.png" style="width:10px; height:10px;"></i>
                      </button>
                  </span>
              </div>
          </div>
          <div class="col-sm-4">
              <input type="date" class="form-control input-fecha-pat" max="<?php echo date('Y-m-d'); ?>">
          </div>
          <div class="col-sm-2">
              <button type="button" class="btn btn-danger btn-remove-pat">
                  <i><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></i>
              </button>
          </div>
      </div>`;

    let $fila = $(htmlPat);
    $('#contenedor_filas_patologias').append($fila);

    if (id_guardado !== "" && id_guardado !== null) {
      setTimeout(() => $fila.find('.select-pat').val(id_guardado.toString().trim()), 10);
    }
    if (fecha_guardada !== "" && fecha_guardada !== null) {
      $fila.find('.input-fecha-pat').val(fecha_guardada.toString().trim());
    }
  }

  function agregarFilaAlergia(id_guardado = "", fecha_guardada = "") {
    let optionsHtml = construirOpcionesAlergias();

    let htmlAle = `
      <div class="row fila-ale" style="margin-bottom: 10px;">
          <div class="col-sm-6">
              <div class="input-group">
                  <select class="form-control select-ale">
                      ${optionsHtml}
                  </select>
                  <span class="input-group-btn">
                      <button class="btn btn-info btn-search-ale" type="button" title="Buscar Alergia">
                        <i><img src="../../recursos/imagenes/iconos/buscar.png" style="width:10px; height:10px;"></i>
                      </button>
                  </span>
              </div>
          </div>
          <div class="col-sm-4">
              <input type="date" class="form-control input-fecha-ale" max="<?php echo date('Y-m-d'); ?>">
          </div>
          <div class="col-sm-2">
              <button type="button" class="btn btn-danger btn-remove-ale">
                  <i><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></i>
              </button>
          </div>
      </div>`;

    let $fila = $(htmlAle);
    $('#contenedor_filas_alergias').append($fila);

    if (id_guardado !== "" && id_guardado !== null) {
      setTimeout(() => $fila.find('.select-ale').val(id_guardado.toString().trim()), 10);
    }
    if (fecha_guardada !== "" && fecha_guardada !== null) {
      $fila.find('.input-fecha-ale').val(fecha_guardada.toString().trim());
    }
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
        if (patNombres.length > 0) {
          $('#btn_modal_pat').attr('data-original-title', patNombres.join(', ')).tooltip('fixTitle');
        }
      }, 100);
    } else {
      agregarFilaPatologia();
    }
    setTimeout(() => {
      if ($('.select-pat').length > 0) {
        opcionesPatologiasHTML = $('.select-pat').first().html();
      }
    }, 150);

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
        if (aleNombres.length > 0) {
          $('#btn_modal_ale').attr('data-original-title', aleNombres.join(', ')).tooltip('fixTitle');
        }
      }, 100);
    } else {
      agregarFilaAlergia();
    }

    setTimeout(() => {
      if ($('.select-ale').length > 0) {
        opcionesAlergiasHTML = $('.select-ale').first().html();
      }
    }, 150);
  }

  document.addEventListener('DOMContentLoaded', () => {
    inicializarDatosMedicos();
  });

  $('#add_fila_pat').on('click', function(e) {
    e.preventDefault();
    e.stopPropagation();

    if ($('.fila-pat').length === 0) {
      agregarFilaPatologia();
      return;
    }

    const $ultimaFila = $('.fila-pat').last();
    const idUltima = $ultimaFila.find('.select-pat').val();

    if (!idUltima || idUltima === "") {
      mostrarAviso("⚠️ <b>Atención:</b> Debe seleccionar una patología en la fila actual antes de añadir otra.");
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

    const $ultimaFila = $('.fila-ale').last();
    const idUltima = $ultimaFila.find('.select-ale').val();

    if (!idUltima || idUltima === "") {
      mostrarAviso("⚠️ <b>Atención:</b> Debe seleccionar una alergia en la fila actual antes de añadir otra.");
      return;
    }

    agregarFilaAlergia();
  });

  $('#add_fila_sintoma_ajax').on('click', function(e) {
    e.preventDefault();
    e.stopPropagation();

    if ($('.fila-sintoma-ajax').length === 0) {
      agregarFilaSintomaAjax();
      return;
    }

    const $ultimaFila = $('.fila-sintoma-ajax').last();
    const idUltima = $ultimaFila.find('.select-sintoma-ajax').val();

    if (!idUltima || idUltima === "") {
      mostrarAviso("⚠️ <b>Atención:</b> Debe seleccionar un síntoma en la fila actual antes de añadir otro.");
      return;
    }

    agregarFilaSintomaAjax();
  });

  $(document).on('click', '.btn-remove-sintoma-ajax', function(e) {
    e.preventDefault();
    const $fila = $(this).closest('.fila-sintoma-ajax');
    const $select = $fila.find('.select-sintoma-ajax');

    const tieneDatos = $select.val() && $select.val() !== "";

    if (tieneDatos) {
      $select.val("");
      return;
    }

    if ($('.fila-sintoma-ajax').length <= 1) {
      return;
    }

    $fila.remove();
  });

  $(document).on('click', '.btn-remove-pat', function(e) {
    e.preventDefault();
    const $fila = $(this).closest('.fila-pat');
    const $select = $fila.find('.select-pat');
    const $fecha = $fila.find('.input-fecha-pat');

    const tieneDatos = ($select.val() && $select.val() !== "") || ($fecha.val() && $fecha.val() !== "");

    if (tieneDatos) {
      $select.val("");
      $fecha.val("");
      return;
    }

    if ($('.fila-pat').length <= 1) {
      return;
    }
    $fila.remove();
  });

  $(document).on('click', '.btn-remove-ale', function(e) {
    e.preventDefault();
    const $fila = $(this).closest('.fila-ale');
    const $select = $fila.find('.select-ale');
    const $fecha = $fila.find('.input-fecha-ale');

    const tieneDatos = ($select.val() && $select.val() !== "") || ($fecha.val() && $fecha.val() !== "");

    if (tieneDatos) {
      $select.val("");
      $fecha.val("");
      return;
    }

    if ($('.fila-ale').length <= 1) {
      return;
    }

    $fila.remove();
  });

  $('#guardar_pat_listo').click(function(e) {
    let seleccionadas = [];
    let nombres = [];
    let errorFecha = false;
    let filaVacia = false;

    $('.fila-pat').each(function() {
      let select = $(this).find('.select-pat');
      let id = select.val();
      let fecha = $(this).find('.input-fecha-pat').val();

      if (!id || id === "") {
        if ($('.fila-pat').length > 1) {
          filaVacia = true;
        }
        return;
      }

      if (!fecha) errorFecha = true;
      let opt = select.find('option:selected');
      seleccionadas.push({
        id: parseInt(id),
        nombre: opt.data('nombre'),
        codigo_cie: opt.data('cie'),
        fecha: fecha
      });
      nombres.push(opt.text().trim());
    });

    if (filaVacia) {
      mostrarAviso("⚠️ <b>Atención:</b> Hay filas de patologías sin seleccionar. Complételas o elimínelas antes de continuar.");
      return false;
    }

    if (errorFecha && e.originalEvent) {
      mostrarAviso("⚠️ Atención: Hay patologías seleccionadas sin fecha de detección.");
      return false;
    }

    $('#patologias_ids').val(JSON.stringify(seleccionadas));
    let boton = $('#btn_modal_pat');
    if (nombres.length > 0) {
      boton.attr('data-original-title', nombres.join(', ')).tooltip('fixTitle').removeClass('input-error');
    } else {
      boton.attr('data-original-title', 'Ninguna seleccionada').tooltip('fixTitle');
    }
    $('#modalPatologias').modal('hide');
  });

  $('#guardar_ale_listo').click(function(e) {
    let seleccionadas = [];
    let nombres = [];
    let errorFecha = false;

    let filaVacia = false;
    $('.fila-ale').each(function() {
      let select = $(this).find('.select-ale');
      let id = select.val();
      let fecha = $(this).find('.input-fecha-ale').val();

      if (!id || id === "") {
        if ($('.fila-ale').length > 1) {
          filaVacia = true;
        }
        return;
      }

      if (!fecha) errorFecha = true;
      let opt = select.find('option:selected');
      seleccionadas.push({
        id: parseInt(id),
        nombre: opt.data('nombre'),
        fecha: fecha
      });
      nombres.push(opt.text().trim());
    });

    if (filaVacia) {
      mostrarAviso("⚠️ <b>Atención:</b> Hay filas de alergias sin seleccionar. Complételas o elimínelas antes de continuar.");
      return false;
    }

    if (errorFecha && e.originalEvent) {
      mostrarAviso("⚠️ Atención: Hay alergias seleccionadas sin fecha de detección.");
      return false;
    }

    $('#alergias_ids').val(JSON.stringify(seleccionadas));
    let boton = $('#btn_modal_ale');
    if (nombres.length > 0) {
      boton.attr('data-original-title', nombres.join(', ')).tooltip('fixTitle').removeClass('input-error');
    } else {
      boton.attr('data-original-title', 'Ninguna seleccionada').tooltip('fixTitle');
    }
    $('#modalAlergias').modal('hide');
  });

  let selectDestinoTarget = null;
  $(document).on('click', '.btn-search-pat', function() {
    selectDestinoTarget = $(this).closest('.input-group').find('.select-pat');
    $('#modalBuscarPat').modal('show');
    $('#inputBuscarPat').val('').trigger('keyup');
  });

  $('#inputBuscarPat').on('keyup', function() {
    let texto = $(this).val().toLowerCase();
    let html = '';
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
    let texto = $(this).val().toLowerCase();
    let html = '';
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
      mostrarAviso("⚠️ <b>Atención:</b> Esta patología ya fue seleccionada.");
      $(this).val("");
    }
  });

  $(document).on('change', '.select-ale', function() {
    let val = $(this).val();
    if (!val) return;
    if ($('.select-ale').filter(function() {
        return $(this).val() === val;
      }).length > 1) {
      mostrarAviso("⚠️ <b>Atención:</b> Esta alergia ya fue seleccionada.");
      $(this).val("");
    }
  });

  $('#btn_abrir_sintomas_ajax').click(function() {
    $('#modalSintomasAjax').modal('show');
    if ($('.fila-sintoma-ajax').length === 0) {
      agregarFilaSintomaAjax();
    }
  });

  function agregarFilaSintomaAjax() {
    let optionsHtml = construirOpcionesSintomas();
    let htmlSintoma = `
        <div class="row fila-sintoma-ajax" style="margin-bottom: 10px;">
            <div class="col-sm-10">
                <select class="form-control select-sintoma-ajax">
                    ${optionsHtml}
                </select>
            </div>
            <div class="col-sm-2">
                <button type="button" class="btn btn-danger btn-remove-sintoma-ajax">
                    <i><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></i>
                </button>
            </div>
        </div>`;
    $('#contenedor_filas_sintomas_ajax').append(htmlSintoma);
  }

  $('#btn_confirmar_sintomas_ajax').click(function() {
    let seleccionados = [];
    let filaVacia = false;

    $('.select-sintoma-ajax').each(function() {
      let val = $(this).val();
      if (val) {
        seleccionados.push(val);
      } else if ($('.select-sintoma-ajax').length > 1) {
        filaVacia = true;
      }
    });

    if (filaVacia) {
      mostrarAviso("⚠️ <b>Atención:</b> Hay filas de síntomas sin seleccionar. Complételas o elimínelas antes de continuar.");
      return;
    }

    $('#ajax_sintomas_ids').val(seleccionados.join(','));

    let boton = $('#btn_abrir_sintomas_ajax');
    if (seleccionados.length > 0) {
      boton.attr('data-original-title', seleccionados.length + ' síntoma(s) seleccionado(s)').tooltip('fixTitle').removeClass('btn-info').addClass('btn-success');
    } else {
      boton.attr('data-original-title', 'Ninguno seleccionado').tooltip('fixTitle').removeClass('btn-success').addClass('btn-info');
    }
    $('#modalSintomasAjax').modal('hide');
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
          // 1. Agregar a la lista en memoria
          listaSintomas.push({
            id: parseInt(res.id),
            nombre: res.nombre
          });

          // 2. Reconstruir TODOS los selects de síntomas del modal
          let nuevasOpciones = construirOpcionesSintomas();
          $('.select-sintoma-ajax').each(function() {
            let valorActual = $(this).val();
            $(this).html(nuevasOpciones);
            if (valorActual) $(this).val(valorActual);
          });

          let selectVacio = $('.select-sintoma-ajax').filter(function() {
            return !$(this).val();
          }).first();
          if (selectVacio.length > 0) {
            selectVacio.val(res.id);
          } else {
            $('.select-sintoma-ajax').last().val(res.id);
          }

          $('#modalNuevoSintoma').modal('hide');
          $('#nombre_nuevo_sintoma').val('');

          mostrarAvisoExito("Síntoma guardado con éxito.");
        } else {
          mostrarAviso(res.error);
        }
      },
      error: function(xhr, status, error) {
        console.error("⚠️ Error AJAX síntoma:", error, xhr.responseText);
        mostrarAviso("⚠️ Error de conexión al guardar el síntoma.");
      }
    });
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

          if (selectVacio.length > 0) {
            selectVacio.val(res.id).trigger('change');
          } else {
            agregarFilaPatologia(res.id, "");
          }

          $('#modalNuevaPatologia').modal('hide');
          $('#formAjaxPatologia')[0].reset();
          $('#contenedor_filas_sintomas_ajax').empty();
          $('#ajax_sintomas_ids').val('');
          $('#btn_abrir_sintomas_ajax')
            .removeClass('btn-success')
            .addClass('btn-info')
            .attr('data-original-title', 'Ninguno seleccionado')
            .tooltip('fixTitle');

          mostrarAvisoExito("Patología guardada con éxito.");
        } else {
          mostrarAviso(res.error);
        }
      },
      error: function(xhr, status, error) {
        console.error("⚠️ Error AJAX patología:", error, xhr.responseText);
        mostrarAviso("⚠️ Error de conexión al guardar la patología.<br>" + xhr.responseText.substring(0, 300));
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

          if (selectVacio.length > 0) {
            selectVacio.val(res.id).trigger('change');
          } else {
            agregarFilaAlergia(res.id, "");
          }

          $('#modalNuevaAlergia').modal('hide');
          $('#formAjaxAlergia')[0].reset();
          mostrarAvisoExito("Alergia guardada con éxito.");
        } else {
          mostrarAviso(res.error);
        }
      },
      error: function(xhr, status, error) {
        console.error("⚠️ Error AJAX alergia:", error, xhr.responseText);
        mostrarAviso("⚠️ Error de conexión al guardar la alergia.<br>" + xhr.responseText.substring(0, 300));
      }
    });
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

  let listaDiscapacidades = [];

  function construirOpcionesDiscapacidades() {
    let html = '<option value="">--- Seleccione una discapacidad ---</option>';
    listaDiscapacidades
      .slice()
      .sort((a, b) => a.nombre.localeCompare(b.nombre))
      .forEach(d => {
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
    const htmlNuevaFila = `
    <div class="row fila-disc-modal" style="margin-top: 15px; padding-top: 15px; border-top: 1px dashed #ddd;">
      <div class="col-sm-6">
        <label>Tipo de Discapacidad (*):</label>
        <select class="form-control select-disc-dinamico">${optionsHtml}</select>
      </div>
      <div class="col-sm-4">
        <label>Fecha detección (Opcional):</label>
        <input type="date" class="form-control input-fecha-disc-dinamico" max="<?php echo date('Y-m-d'); ?>">
      </div>
      <div class="col-sm-2">
        <label style="display:block;">&nbsp;</label>
        <button type="button" class="btn btn-danger btn-remove-disc-modal">
          <i><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></i>
        </button>
      </div>
    </div>
  `;

    let $fila = $(htmlNuevaFila);
    $('.fila-disc-modal').last().after($fila);

    if (id_guardado) $fila.find('.select-disc-dinamico').val(id_guardado);
    if (fecha_guardada) $fila.find('.input-fecha-disc-dinamico').val(fecha_guardada);
  }

  $('#btn_add_disc_row').on('click', function(e) {
    e.preventDefault();

    const $ultimaFila = $('.fila-disc-modal').last();
    const idUltima = $ultimaFila.find('.select-disc-dinamico').val();

    if (!idUltima || idUltima === "") {
      mostrarAviso("⚠️ <b>Atención:</b> Debe seleccionar una discapacidad en la fila actual antes de añadir otra.");
      return;
    }

    agregarFilaDiscapacidad();
  });

  $(document).on('click', '.btn-remove-disc-modal', function(e) {
    e.preventDefault();
    const $fila = $(this).closest('.fila-disc-modal');
    const $select = $fila.find('.select-disc-dinamico');
    const $fecha = $fila.find('.input-fecha-disc-dinamico');
    const tieneDatos = ($select.val() && $select.val() !== "") || ($fecha.val() && $fecha.val() !== "");

    if (tieneDatos) {
      $select.val("");
      $fecha.val("");
      return;
    }

    if ($('.fila-disc-modal').length <= 1) {
      return;
    }

    $fila.remove();
  });

  $(document).on('change', '.select-disc-dinamico', function() {
    let val = $(this).val();
    if (!val) return;

    if ($('.select-disc-dinamico').filter(function() {
        return $(this).val() === val;
      }).length > 1) {

      mostrarAviso("⚠️ <b>Atención:</b> Esta discapacidad ya fue seleccionada en otra fila.");
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
            for (let i = 1; i < lista.length; i++) {
              agregarFilaDiscapacidad(lista[i].id, lista[i].fecha);
            }
          }
        } catch (e) {}
      }
    }
  });

  $('#guardar_disc_listo').click(function(e) {
    e.preventDefault();
    let seleccionadas = [];
    let nombres = [];
    let filaVacia = false;

    $('.fila-disc-modal').each(function() {
      let $select = $(this).find('.select-disc-dinamico');
      let id = $select.val();
      let fecha = $(this).find('.input-fecha-disc-dinamico').val();

      if (!id || id === "") {
        if ($('.fila-disc-modal').length > 1) filaVacia = true;
        return;
      }

      let nombre = $select.find('option:selected').data('nombre') || '';
      seleccionadas.push({
        id: parseInt(id),
        nombre: nombre,
        fecha: fecha
      });
      nombres.push(nombre);
    });

    if (filaVacia) {
      mostrarAviso("⚠️ <b>Atención:</b> Hay filas de discapacidades sin seleccionar. Complételas o elimínelas antes de continuar.");
      return false;
    }

    $('#discapacidades_ids').val(JSON.stringify(seleccionadas));
    let boton = $('#btn_modal_disc');
    if (nombres.length > 0) {
      boton.attr('data-original-title', nombres.join(', ')).tooltip('fixTitle').removeClass('input-error');
    } else {
      boton.attr('data-original-title', 'Ninguna seleccionada').tooltip('fixTitle');
    }
    $('#modalDiscapacidades').modal('hide');
  });

  $('#cancelar_disc_modal').on('click', function(e) {
    e.preventDefault();
    $('#modalDiscapacidades').modal('hide');
  });

  $('#btn_guardar_ajax_disc').click(function() {
    let nombre = $('#ajax_nombre_disc').val().trim();

    if (!nombre) {
      mostrarAviso("El nombre de la discapacidad es obligatorio.");
      return;
    }

    $.ajax({
      url: window.location.href,
      method: 'POST',
      data: {
        ajax_nueva_discapacidad: 1,
        nombre_discapacidad: nombre
      },
      dataType: 'json',
      success: function(res) {
        if (res.success) {
          listaDiscapacidades.push({
            id: parseInt(res.id),
            nombre: res.nombre
          });

          let nuevasOpciones = construirOpcionesDiscapacidades();
          $('.select-disc').each(function() {
            let valorActual = $(this).val();
            $(this).html(nuevasOpciones);
            if (valorActual) $(this).val(valorActual);
          });

          let selectVacio = $('.select-disc').filter(function() {
            return !$(this).val();
          }).first();

          if (selectVacio.length > 0) {
            selectVacio.val(res.id).trigger('change');
          } else {
            agregarFilaDiscapacidad(res.id, "");
          }

          $('#modalNuevaDiscapacidad').modal('hide');
          $('#formAjaxDiscapacidad')[0].reset();
          mostrarAvisoExito("Discapacidad guardada con éxito.");
        } else {
          mostrarAviso(res.error);
        }
      },
      error: function(xhr, status, error) {
        console.error("⚠️ Error AJAX discapacidad:", error, xhr.responseText);
        mostrarAviso("⚠️ Error de conexión al guardar la discapacidad.<br>" + xhr.responseText.substring(0, 300));
      }
    });
  });

  async function validarPestana(tabSelector) {
    let esValido = true;
    let errores = [];
    limpiarErroresPestana(tabSelector);

    $(tabSelector).find('[required]').each(function() {
      var $input = $(this);
      var valor = $input.val();

      if ($input.is(':visible') && !$input.prop('disabled')) {
        if (valor === null || valor.trim() === "" || valor === '--- Seleccione Un Genero ---' || valor === '--- Seleccione Un Estado ---' || valor === '--- Seleccione Un Municpio ---' || valor === '--- Seleccione Una Parroquia ---' || valor === '--- Seleccione Un Sector ---' || valor === '--- Seleccione Una Opcion ---') {
          $input.addClass('input-error');
          esValido = false;
          if ($input.attr('name') === 'cedula') {
            errores.push("El campo de Cédula es obligatorio.");
          } else if ($input.attr('name') === 'fecha_nacimiento') {
            errores.push("El campo de Fecha de nacimiento es obligatorio.");
          } else {
            errores.push(`El campo '${$input.prev('p').text().replace('(*):', '').trim()}' es obligatorio.`);
          }
        }
      }
    });

    if (tabSelector === '#info') {
      const cedulaEsValida = await validarDatosUnicos();
      if (!cedulaEsValida) {
        esValido = false;
      }

      const $cedula = $('#cedula');
      if ($cedula.length && $cedula.val().trim() === '') {
        $cedula.addClass('input-error');
        esValido = false;
        if (!errores.includes("El campo de Cédula es obligatorio.")) {
          errores.push("El campo de Cédula es obligatorio.");
        }
      }

      const fechaNac = document.getElementById("fechaN").value;
      if (fechaNac) {
        const edad = parseInt(document.getElementById('edad').value);
        if (isNaN(edad) || edad < 1) {
          $('#fechaN').addClass('input-error');
          esValido = false;
          errores.push("La edad debe ser mayor a 0.");
        }
      }

      const edad = parseInt($('#edad').val());
      if (isNaN(edad) || edad < 18 || edad > 120) {
        errores.push("El paciente debe ser mayor de 18 años (Fecha de Nacimiento).");
        $('#fechaN').addClass('input-error');
        esValido = false;
      }
    }

    if (tabSelector === '#ocupacion_estudios') {
      if (!$('#años_aprobados').prop('disabled')) {
        const años = parseInt($('#años_aprobados').val());
        const max = parseInt($('#años_aprobados').attr('max'));
        if (isNaN(años) || años < 0) {
          errores.push("El campo Años Aprobados es obligatorio si seleccionó 'Sí' en Analfabeta.");
          $('#años_aprobados').addClass('input-error');
          esValido = false;
        } else if (!isNaN(max) && años > max) {
          errores.push("Los Años Aprobados exceden el máximo permitido para esta Misión (" + max + " años).");
          $('#años_aprobados').addClass('input-error');
          esValido = false;
        }
      }
    }

    if (!esValido) {
      const mensajeError = "Por favor, corrija los siguientes errores:<br>" + errores.filter((v, i, a) => a.indexOf(v) === i).join('<br>');
      mostrarAviso(mensajeError, '⚠️ Error de Validación', 'bg-crimson');
    }

    if (tabSelector === '#salud_otros') {

    }

    if (!esValido && errores.length > 0) {
      mostrarAviso('⚠️ Errores de Formulario:<ul><li>' + errores.join('</li><li>') + '</li></ul>');
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
          mostrarAviso("⚠️ Conflicto de Cédula. Regrese a la pestaña 'Datos Personales' y corrija la información.");
          return;
        }

        const modalSelector = $('#modalGuardarPaciente').length ? '#modalGuardarPaciente' : '#modalGuardarMedico';
        $(modalSelector).modal('show');

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
    const $btn = $(this);
    const tabActualSelector = $btn.data('tab-actual');
    const tabAnteriorName = $btn.data('tab-anterior');

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

  soloNumerosSinE(document.getElementById("cedula"), 8);
  soloNumerosSinE(document.getElementById("telefono"), 7);
  soloNumerosSinE(document.getElementById("tiempo_residencia"));

  setTimeout(function() {
    if (typeof patologiasData !== 'undefined' && patologiasData.trim() !== "") {
      let items = patologiasData.split('||');
      let nombres = [];
      items.forEach(function(item) {
        if (item.trim() !== "") {
          let parts = item.split('::');
          if (parts.length >= 2) nombres.push(parts[1].trim());
        }
      });
      if (nombres.length > 0) {
        $('#btn_modal_pat').attr('data-original-title', nombres.join(', ')).tooltip('fixTitle');
      }
    }

    if (typeof alergiasData !== 'undefined' && alergiasData.trim() !== "") {
      let items = alergiasData.split('||');
      let nombres = [];
      items.forEach(function(item) {
        if (item.trim() !== "") {
          let parts = item.split('::');
          if (parts.length >= 2) nombres.push(parts[1].trim());
        }
      });
      if (nombres.length > 0) {
        $('#btn_modal_ale').attr('data-original-title', nombres.join(', ')).tooltip('fixTitle');
      }
    }

    if (Array.isArray(discapacidadesData) && discapacidadesData.length > 0) {
      let nombres = discapacidadesData.map(d => d.nombre);
      $('#btn_modal_disc').attr('data-original-title', nombres.join(', ')).tooltip('fixTitle');
      $('#discapacidades_ids').val(JSON.stringify(discapacidadesData));
    }

    if (Array.isArray(representantesData) && representantesData.length > 0) {
      let nombres = representantesData.map(r => {
        let txt = r.nombre + ' (' + (r.tipo || 'Legal');
        if (r.parentesco) txt += ' - ' + r.parentesco;
        txt += ')';
        return txt;
      });
      $('#btn_modal_rep').attr('data-original-title', nombres.join(', ')).tooltip('fixTitle');
      $('#representantes_ids').val(JSON.stringify(representantesData));
    }
  }, 100);
</script>