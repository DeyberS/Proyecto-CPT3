<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

include("../../cfg/conexion.php");

$id_url = isset($_GET['Id']) ? (int)$_GET['Id'] : 0;

if ($id_url <= 0) {
  $_SESSION['mensaje_user_error'] = "Debe seleccionar una consulta válida para ver su información.";
  header("Location: pacientes_listado.php");
  exit;
}

include('includes/headerNav2.php');
verificarAcceso('Ver Pacientes');

// Permiso para editar antecedentes desde esta ficha
$puede_editar_antecedentes = in_array('Editar Pacientes', $_SESSION['permisos'] ?? [], true);
?>

<head>
  <title>Pacientes | Información</title>
</head>

<div class="content-wrapper">
  <style>
    :root {
      --primary-dark: #2c3e50;
      --medical-blue: #007bff;
      --bg-gray: #f4f7f9;
    }

    .pac-info-page {
      background-color: var(--bg-gray);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      min-height: 100vh;
    }

    .pac-info-page .content-wrapper {
      background-color: var(--bg-gray);
    }

    .pac-info-page .content {
      padding: 0;
    }

    .pac-info-page .content-custome {
      padding: 0;
      margin-left: 0;
    }

    .profile-card {
      max-width: 1200px;
      margin: 15px auto;
      background: #fff;
      border-radius: 0px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      border: none;
      min-height: 80vh;
      overflow: hidden;
    }

    .profile-header {
      background: linear-gradient(135deg, var(--primary-dark) 0%, #34495e 100%);
      color: white;
      padding: 10px;
      border-bottom: 5px solid var(--medical-blue);
    }

    .paciente-avatar {
      width: 110px;
      height: 110px;
      background: transparent;
      border-radius: 0px;
      padding: 0;
      box-shadow: none;
      object-fit: contain;
    }

    .label-custom {
      font-size: 11px;
      text-transform: uppercase;
      font-weight: 700;
      display: block;
      margin-bottom: 2px;
    }

    .label-light {
      color: #bdc3c7;
    }

    .label-dark {
      color: #95a5a6;
    }

    .val-custom {
      font-size: 16px;
      font-weight: 600;
    }

    .val-light {
      color: #fff;
    }

    .val-dark {
      color: var(--primary-dark);
    }

    .section-title {
      color: var(--medical-blue);
      font-weight: 700;
      border-bottom: 1px solid #eee;
      padding-bottom: 10px;
      margin-bottom: 20px;
      text-transform: uppercase;
      font-size: 14px;
    }

    .data-item-card {
      background: #f8f9fa;
      padding: 15px;
      border-radius: 0px;
      border-left: 4px solid var(--medical-blue);
      margin-bottom: 15px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
    }

    .nav-tabs-custom {
      background: transparent;
      box-shadow: none;
    }

    .nav-tabs {
      border-bottom: 2px solid #eee;
      background: #f8f9fa;
      padding: 0 20px;
    }

    .nav-tabs>li>a {
      border-radius: 0;
      color: #7f8c8d;
      font-weight: 600;
      text-transform: uppercase;
      font-size: 13px;
      padding: 15px 20px;
      border: none;
      transition: all 0.3s ease;
    }

    .nav-tabs>li>a:hover {
      background-color: #e9ecef;
      color: var(--primary-dark);
    }

    .nav-tabs>li.active>a,
    .nav-tabs>li.active>a:focus,
    .nav-tabs>li.active>a:hover {
      color: var(--medical-blue);
      background-color: #fff;
      border-bottom: 3px solid var(--medical-blue);
    }

    .tab-content-container {
      padding: 30px;
    }

    .badge-alergia,
    .badge-patologia,
    .badge-discapacidad,
    .badge-representante {
      color: white;
      padding: 6px 12px;
      border-radius: 0px;
      font-size: 12px;
      display: inline-block;
      margin-right: 5px;
      margin-bottom: 5px;
      font-weight: 600;
    }

    .badge-alergia {
      background-color: #e67e22;
    }

    .badge-patologia {
      background-color: #e74c3c;
    }

    .badge-discapacidad {
      background-color: #8e44ad;
    }

    .badge-representante {
      background-color: #0d9488;
    }

    .badge-estado {
      padding: 4px 10px;
      border-radius: 0px;
      font-weight: 600;
      font-size: 11px;
      text-transform: uppercase;
    }

    .btn-nav-container {
      display: flex;
      justify-content: flex-end;
      gap: 15px;
      margin-top: 00px;
      padding-top: 20px;
      border-top: 1px solid #eee;
    }

    .timeline {
      position: relative;
      padding-left: 20px;
      border-left: 2px solid #eee;
    }

    .timeline-item {
      margin-bottom: 20px;
      position: relative;
    }

    .timeline-item::before {
      content: '';
      position: absolute;
      left: -27px;
      top: 0px;
      width: 12px;
      height: 12px;
      background-color: var(--medical-blue);
      border-radius: 50%;
      border: 2px solid #fff;
    }

    .badge-container {
      position: relative;
      max-height: 96px;
      overflow: hidden;
      transition: max-height .25s ease;
      padding-bottom: 0;
      margin-bottom: 4px;
    }

    .badge-container.has-overflow:not(.expanded)::after {
      content: '';
      position: absolute;
      left: 0;
      right: 0;
      bottom: 0;
      height: 34px;
      background: linear-gradient(to bottom, rgba(255, 255, 255, 0), #f8f9fa 90%);
      pointer-events: none;
    }

    .badge-container.bg-orange.has-overflow:not(.expanded)::after {
      background: linear-gradient(to bottom, rgba(253, 242, 233, 0), #fdf2e9 90%);
    }

    .badge-container.bg-red.has-overflow:not(.expanded)::after {
      background: linear-gradient(to bottom, rgba(253, 237, 236, 0), #fdedec 90%);
    }

    .ver-mas-btn {
      background: none;
      border: none;
      color: var(--medical-blue);
      font-weight: 700;
      font-size: 12px;
      padding: 4px 0;
      cursor: pointer;
      text-decoration: underline;
      display: inline-block;
      margin-top: 2px;
    }

    .ver-mas-btn:hover {
      color: var(--primary-dark);
    }

    .ver-mas-btn i {
      margin-right: 4px;
    }

    .modal-items-scroll {
      max-height: none;
      overflow-y: visible;
    }

    .modal-items-scroll.scrollable {
      max-height: 280px;
      overflow-y: auto;
      padding-right: 8px;
    }

    .modal-items-scroll.scrollable::-webkit-scrollbar {
      width: 6px;
    }

    .modal-items-scroll.scrollable::-webkit-scrollbar-thumb {
      background: #cbd5e1;
      border-radius: 3px;
    }

    .modal-items-scroll.scrollable::-webkit-scrollbar-thumb:hover {
      background: #94a3b8;
    }

    .chip-item {
      display: block;
      padding: 10px 14px;
      margin-bottom: 8px;
      background: #f8fafc;
      border-left: 3px solid var(--medical-blue);
      font-size: 13px;
      color: var(--primary-dark);
      font-weight: 600;
      border-radius: 0;
      word-wrap: break-word;
    }

    .chip-item:last-child {
      margin-bottom: 0;
    }

    .chip-item.chip-orange {
      border-left-color: #e67e22;
      background: #fdf2e9;
    }

    .chip-item.chip-red {
      border-left-color: #e74c3c;
      background: #fdedec;
    }

    .chip-item.chip-purple {
      border-left-color: #8e44ad;
      background: #f3e8f7;
    }

    .chip-item.chip-teal {
      border-left-color: #0d9488;
      background: #e6f7f5;
    }

    .chip-item small {
      font-weight: 500;
      color: #7f8c8d;
      margin-left: 6px;
    }

    .chip-item strong {
      display: block;
      margin-bottom: 2px;
    }

    .antecedente-list {
      list-style: none;
      padding-left: 0;
      margin-bottom: 0;
    }

    .antecedente-list li {
      padding: 6px 0;
      border-bottom: 1px dashed #eee;
      font-size: 14px;
      color: var(--primary-dark);
    }

    .antecedente-list li:last-child {
      border-bottom: none;
    }

    .antecedente-list li i {
      color: #27ae60;
      margin-right: 8px;
    }

    /* Botones de antecedentes (replican estilo de consulta_editar.php) */
    .btn-antecedente-tipo {
      min-height: 70px;
      text-align: left;
      padding: 10px 15px;
      font-weight: 600;
      font-size: 13px;
      white-space: normal;
      line-height: 1.2;
      border-radius: 0;
    }

    .btn-antecedente-tipo .badge {
      background: #fff;
      color: #3c8dbc;
      font-weight: bold;
    }

    /* Checklist dentro del modal editable */
    .chk-antecedente-wrap {
      padding: 6px 10px;
      border-bottom: 1px solid #eee;
    }

    .chk-antecedente-wrap:hover {
      background: #f9f9f9;
    }

    .chk-antecedente-wrap label {
      font-weight: normal;
      cursor: pointer;
      margin: 0;
    }
  </style>

  <section class="content content-custome">
    <div class="row">
      <div class="col-xs-12">
        <div class="profile-card">
          <?php
          $sql = "SELECT p.id, p.email, p.nombre, p.apellido, p.tipo_cedula, p.cedula, p.fecha_nacimiento, p.genero, p.email,
                tp.telefono, pt.prefijo, tp.Id_prefijo,
                dp.situacion_conyugal, dp.etnia, dp.tipo_etnia, dp.analfabeta, dp.profesion, dp.ocupacion, dp.nivel_instruccion, dp.mision, dp.años_aprobados, dp.seguro_social,
                hm.grupo_sanguineo,
                (SELECT GROUP_CONCAT(CONCAT(pc.nombre_patologia, ' (', DATE_FORMAT(hp.fecha_registro, '%d/%m/%Y'), ')') SEPARATOR '||')
                 FROM historial_patologias hp
                 JOIN patologias pc ON hp.Id_patologia = pc.Id_patologia
                 JOIN historial_medico hm2 ON hp.Id_Historial = hm2.id_historial
                 WHERE hm2.Id_persona = p.id) AS lista_patologias,
                (SELECT GROUP_CONCAT(CONCAT(ac.nombre_alergia, ' (', DATE_FORMAT(ha.fecha_registro, '%d/%m/%Y'), ')') SEPARATOR '||')
                 FROM historial_alergias ha
                 JOIN alergias_conocidas ac ON ha.Id_alergia = ac.Id_alergias_conocidas
                 JOIN historial_medico hm3 ON ha.Id_Historial = hm3.id_historial
                 WHERE hm3.Id_persona = p.id) AS lista_alergias,
                d.avenida_calle, d.referencia, d.tiempo_residencia, d.tiempo,
                paisnac.nombre_pais AS nombre_pais_nac,
                estnac.nombre_estado AS nombre_estado_nac,
                munnac.nombre_municipio AS nombre_municipio_nac,
                dirsec.nombre_sector AS nombre_sector_dir,
                dirmun.nombre_municipio AS nombre_municipio_dir,
                direst.nombre_estado AS nombre_estado_dir
                FROM persona p
                LEFT JOIN telefonos_personas tp ON p.id = tp.Id_persona
                LEFT JOIN prefijos_telefonos pt ON tp.Id_prefijo = pt.Id
                LEFT JOIN historial_medico hm ON p.id = hm.Id_persona
                LEFT JOIN detalle_paciente dp ON p.id = dp.Id_persona
                LEFT JOIN lugar_nacimiento ln ON p.id = ln.Id_persona
                LEFT JOIN municipio munnac ON ln.Id_municipio = munnac.Id_Municipio
                LEFT JOIN estado estnac ON munnac.Id_Estado = estnac.Id_Estado
                LEFT JOIN pais paisnac ON estnac.Id_Pais = paisnac.Id_Pais
                LEFT JOIN direccion d ON p.id = d.Id_persona
                LEFT JOIN sector dirsec ON d.Id_sector = dirsec.Id_Sector
                LEFT JOIN municipio dirmun ON dirsec.Id_Municipio = dirmun.Id_Municipio
                LEFT JOIN estado direst ON dirmun.Id_Estado = direst.Id_Estado
                WHERE p.id = " . (int)$id_url;

          $resultado = $conexion->query($sql);
          $row = $resultado->fetch_assoc();
          $id_paciente = (int)$row['id'];

          $representantes_array = [];
          $sql_reps = "SELECT
                            hr.Id AS id_relacion, hr.Id_representante, hr.tipo_representante, hr.parentesco,
                            r.nombre AS nombre_rep, r.apellido AS apellido_rep, r.tipo_cedula AS tipo_cedula_rep,
                            r.cedula AS cedula_rep, r.email AS email_rep, r.genero AS genero_rep,
                            (SELECT tp.telefono FROM telefonos_personas tp WHERE tp.Id_persona = r.id AND tp.estatus = '1' ORDER BY tp.Id ASC LIMIT 1) AS telefono_rep,
                            (SELECT pr.prefijo FROM telefonos_personas tp INNER JOIN prefijos_telefonos pr ON tp.Id_prefijo = pr.Id WHERE tp.Id_persona = r.id AND tp.estatus = '1' ORDER BY tp.Id ASC LIMIT 1) AS prefijo_rep
                         FROM historial_representantes hr
                         INNER JOIN persona r ON hr.Id_representante = r.id
                         WHERE hr.Id_paciente = $id_paciente AND hr.estatus = '1'
                         ORDER BY hr.tipo_representante ASC, hr.Id ASC";
          $res_reps = $conexion->query($sql_reps);
          if ($res_reps && $res_reps->num_rows > 0) {
            while ($r = $res_reps->fetch_assoc()) {
              $representantes_array[] = $r;
            }
          }

          $discapacidades_array = [];
          $sql_discs = "SELECT hd.Id, hd.Id_discapacidad, hd.fecha_registro, d.nombre_discapacidad
                          FROM historial_discapacidades hd
                          INNER JOIN discapacidades d ON hd.Id_discapacidad = d.Id_discapacidad
                          INNER JOIN historial_medico hm ON hd.Id_Historial = hm.id_historial
                          WHERE hm.Id_persona = $id_paciente AND hd.estatus = '1'
                          ORDER BY hd.fecha_registro ASC, hd.Id ASC";
          $res_discs = $conexion->query($sql_discs);
          if ($res_discs && $res_discs->num_rows > 0) {
            while ($d = $res_discs->fetch_assoc()) {
              $discapacidades_array[] = $d;
            }
          }
          ?>

          <input type="hidden" name="Id" value="<?= $row['id']; ?>">

          <div class="profile-header">
            <div class="row w-100">
              <div class="col-sm-2 text-center">
                <img src="../../recursos/imagenes/iconos/Paciente_icon1.png" class="paciente-avatar" style="padding-top:30px; height:100px; width:100;" alt="Foto Paciente">
              </div>
              <div class="col-sm-10">
                <h2 style="margin: 5px 0; font-weight: 800;"><?php echo $row['nombre']; ?> <?php echo $row['apellido']; ?></h2>
                <p style="font-size: 18px; opacity: 0.9;"><i class="fa fa-id-card-o"></i> Documento: <?php echo $row['tipo_cedula']; ?>-<?php echo $row['cedula']; ?></p>
                <div class="row" style="margin-top: 20px;">
                  <div class="col-xs-4">
                    <span class="label-custom label-light">F. Nacimiento</span>
                    <span class="val-custom val-light"><?php echo date("d/m/Y", strtotime($row['fecha_nacimiento'])); ?></span>
                  </div>
                  <div class="col-xs-4">
                    <span class="label-custom label-light">Sexo</span>
                    <span class="val-custom val-light"><?php echo !empty(trim($row['genero'])) ? $row['genero'] : 'No registrado'; ?></span>
                  </div>
                  <div class="col-xs-4">
                    <span class="label-custom label-light">Grupo Sanguíneo</span>
                    <span class="val-custom val-light" style="color:#ff6b6b; font-size:20px;"><?php echo !empty(trim($row['grupo_sanguineo'])) ? $row['grupo_sanguineo'] : 'N/A'; ?></span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active" data-tab-name="info"><a href="#info" data-toggle="tab"><i class="fa fa-user"></i> Datos Personales</a></li>
              <li data-tab-name="ocupacion_estudios"><a href="#ocupacion_estudios" data-toggle="tab"><i class="fa fa-graduation-cap"></i> Ocupación y Estudios</a></li>
              <li data-tab-name="salud"><a href="#salud" data-toggle="tab"><i class="fa fa-heartbeat"></i> Salud</a></li>
              <li data-tab-name="salud_otros"><a href="#salud_otros" data-toggle="tab"><i class="fa fa-file-text-o"></i> Antecedentes Médicos</a></li>
            </ul>

            <div class="tab-content tab-content-container">
              <div class="tab-pane active" id="info">
                <div class="row">
                  <div class="col-md-5">
                    <h4 class="section-title"><i class="fa fa-phone"></i> Contacto y Origen</h4>
                    <div class="data-item-card">
                      <span class="label-custom label-dark">N. Teléfono</span>
                      <span class="val-custom val-dark"><?php echo !empty(trim($row['telefono'])) ? $row['prefijo'] . '-' . $row['telefono'] : 'Ninguno'; ?></span>
                    </div>
                    <div class="data-item-card">
                      <span class="label-custom label-dark">Correo Electrónico</span>
                      <span class="val-custom val-dark"><?php echo !empty(trim($row['email'])) ? $row['email'] : 'Ninguno'; ?></span>
                    </div>
                    <div class="data-item-card" style="border-left-color: #95a5a6;">
                      <span class="label-custom label-dark">Lugar de Nacimiento</span>
                      <p style="margin: 5px 0 0 0; color: var(--primary-dark); font-weight: 500;">
                        <?php echo !empty(trim($row['nombre_pais_nac'])) ? $row['nombre_pais_nac'] : 'N/A'; ?>,
                        <?php echo !empty(trim($row['nombre_estado_nac'])) ? $row['nombre_estado_nac'] : 'N/A'; ?>,
                        <?php echo !empty(trim($row['nombre_municipio_nac'])) ? $row['nombre_municipio_nac'] : 'N/A'; ?>
                      </p>
                    </div>
                    <div class="data-item-card" style="border-left-color: #0d9488;">
                      <span class="label-custom label-dark">
                        <i class="fa fa-users" style="color:#0d9488;"></i> Representante(s) Legal(es)
                        <span class="badge bg-teal" style="font-size:10px;"><?php echo count($representantes_array); ?></span>
                      </span>
                      <div style="margin-top: 8px;" id="cont-representantes"></div>
                    </div>
                  </div>

                  <div class="col-md-7">
                    <h4 class="section-title"><i class="fa fa-address-card-o"></i> Datos Sociales y Residencia</h4>
                    <div class="row">
                      <div class="col-sm-6">
                        <div class="data-item-card">
                          <span class="label-custom label-dark">Sit. Conyugal</span>
                          <span class="val-custom val-dark"><?php echo !empty(trim($row['situacion_conyugal'])) ? $row['situacion_conyugal'] : 'No registrada'; ?></span>
                        </div>
                        <div class="data-item-card">
                          <span class="label-custom label-dark">Etnia</span>
                          <span class="val-custom val-dark">
                            <span class="badge bg-<?php echo ($row['etnia'] == 'Si' ? 'green' : 'red'); ?> badge-estado"><?php echo !empty(trim($row['etnia'])) ? $row['etnia'] : 'No'; ?></span>
                            <?php if ($row['etnia'] == 'Si') echo '(' . $row['tipo_etnia'] . ')'; ?>
                          </span>
                        </div>
                      </div>
                      <div class="col-sm-6">
                        <div class="data-item-card">
                          <span class="label-custom label-dark">¿Analfabeta?</span>
                          <span class="val-custom val-dark"><span class="badge bg-<?php echo ($row['analfabeta'] == 'Si' ? 'green' : 'red'); ?> badge-estado"><?php echo !empty(trim($row['analfabeta'])) ? $row['analfabeta'] : 'No'; ?></span></span>
                        </div>
                        <div class="data-item-card">
                          <span class="label-custom label-dark">¿Cotiza S.S?</span>
                          <span class="val-custom val-dark"><span class="badge bg-<?php echo ($row['seguro_social'] == 'Si' ? 'green' : 'red'); ?> badge-estado"><?php echo !empty(trim($row['seguro_social'])) ? $row['seguro_social'] : 'No'; ?></span></span>
                        </div>
                      </div>
                    </div>

                    <div class="data-item-card" style="border-left-color: #27ae60; margin-top: 10px;">
                      <span class="label-custom label-dark">Dirección de Residencia</span>
                      <div class="row" style="margin-top: 10px;">
                        <div class="col-sm-6">
                          <p style="margin-bottom: 5px;"><strong>Estado:</strong> <?php echo !empty(trim($row['nombre_estado_dir'])) ? $row['nombre_estado_dir'] : 'N/A'; ?></p>
                          <p style="margin-bottom: 5px;"><strong>Municipio:</strong> <?php echo !empty(trim($row['nombre_municipio_dir'])) ? $row['nombre_municipio_dir'] : 'N/A'; ?></p>
                          <p style="margin-bottom: 5px;"><strong>Sector:</strong> <?php echo !empty(trim($row['nombre_sector_dir'])) ? $row['nombre_sector_dir'] : 'N/A'; ?></p>
                        </div>
                        <div class="col-sm-6">
                          <p style="margin-bottom: 5px;"><strong>Av/Calle:</strong> <?php echo !empty(trim($row['avenida_calle'])) ? $row['avenida_calle'] : 'N/A'; ?></p>
                          <p style="margin-bottom: 5px;"><strong>Ref:</strong> <?php echo !empty(trim($row['referencia'])) ? $row['referencia'] : 'N/A'; ?></p>
                          <p style="margin-bottom: 5px;"><strong>T. Resid.:</strong> <?php echo !empty(trim($row['tiempo_residencia'])) ? $row['tiempo_residencia'] . ' ' . $row['tiempo'] : 'N/A'; ?></p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="btn-nav-container">
                  <button type="button" class="btn btn-second" data-toggle="modal" data-target="#modalConfirmarRegreso"><i class="fa fa-arrow-left"></i> Regresar al Menú</button>
                  <button type="button" class="btn btn-primary next-tab" data-tab-siguiente="ocupacion_estudios">Siguiente <i class="fa fa-arrow-right"></i></button>
                </div>
              </div>

              <div class="tab-pane" id="ocupacion_estudios">
                <div class="row">
                  <div class="col-md-5">
                    <h4 class="section-title"><i class="fa fa-briefcase"></i> Ámbito Laboral / Académico</h4>
                    <div class="data-item-card">
                      <span class="label-custom label-dark">Profesión</span>
                      <span class="val-custom val-dark"><?php echo !empty(trim($row['profesion'])) ? $row['profesion'] : 'Ninguna'; ?></span>
                    </div>
                    <div class="data-item-card">
                      <span class="label-custom label-dark">Ocupación Actual</span>
                      <span class="val-custom val-dark"><?php echo !empty(trim($row['ocupacion'])) ? $row['ocupacion'] : 'Ninguna'; ?></span>
                    </div>
                    <div class="data-item-card">
                      <span class="label-custom label-dark">Nivel de Instrucción</span>
                      <span class="val-custom val-dark">
                        <?php
                        $instruccion = trim($row['nivel_instruccion']);
                        echo ($instruccion === 'sin_instruccion') ? 'Sin Instrucción' : (!empty($instruccion) ? $instruccion : 'Ninguno');
                        ?>
                      </span>
                    </div>
                  </div>
                  <div class="col-md-7">
                    <h4 class="section-title"><i class="fa fa-handshake-o"></i> Misiones Educativas</h4>
                    <div class="data-item-card" style="border-left-color: #f1c40f;">
                      <div class="row">
                        <div class="col-sm-4">
                          <span class="label-custom label-dark">¿Participó?</span>
                          <span class="val-custom val-dark"><span class="badge bg-<?php echo ($row['mision'] ? 'green' : 'red'); ?> badge-estado"><?php echo (!empty(trim($row['mision'])) ? 'Si' : 'No'); ?></span></span>
                        </div>
                        <div class="col-sm-4">
                          <span class="label-custom label-dark">Nombre de Misión</span>
                          <span class="val-custom val-dark"><?php echo !empty(trim($row['mision'])) ? $row['mision'] : 'N/A'; ?></span>
                        </div>
                        <div class="col-sm-4">
                          <span class="label-custom label-dark">Años Aprobados</span>
                          <span class="val-custom val-dark"><?php echo !empty(trim($row['años_aprobados'])) ? $row['años_aprobados'] : '0'; ?></span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="btn-nav-container">
                  <button type="button" class="btn btn-second prev-tab" data-tab-anterior="info"><i class="fa fa-arrow-left"></i> Atrás</button>
                  <button type="button" class="btn btn-primary next-tab" data-tab-siguiente="salud">Siguiente <i class="fa fa-arrow-right"></i></button>
                </div>
              </div>

              <div class="tab-pane" id="salud">
                <div class="row">
                  <div class="col-md-5">
                    <h4 class="section-title"><i class="fa fa-exclamation-triangle"></i> Alertas de Salud</h4>
                    <div class="data-item-card" style="border-left-color: #e67e22; background-color: #fdf2e9;">
                      <span class="label-custom" style="color: #d35400;">Alergias Conocidas</span>
                      <div style="margin-top: 8px;" id="cont-alergias"></div>
                    </div>
                    <div class="data-item-card" style="border-left-color: #e74c3c; background-color: #fdedec;">
                      <span class="label-custom" style="color: #c0392b;">Patologías Crónicas</span>
                      <div style="margin-top: 8px;" id="cont-patologias"></div>
                    </div>
                    <div class="data-item-card" style="border-left-color: #8e44ad;">
                      <span class="label-custom label-dark">
                        <i class="fa fa-wheelchair" style="color:#8e44ad;"></i> Discapacidades
                        <?php if (count($discapacidades_array) > 0) : ?>
                          <span class="badge bg-purple" style="font-size:10px;"><?php echo count($discapacidades_array); ?></span>
                        <?php endif; ?>
                      </span>
                      <div style="margin-top: 8px;" id="cont-discapacidades"></div>
                    </div>
                  </div>
                  <div class="col-md-7">
                    <h4 class="section-title"><i class="fa fa-calendar-check-o"></i> Consultas Recientes</h4>
                    <div class="timeline" id="historial-consultas-container">
                      <div class="alert alert-info" style="border-radius: 0px;"><i class="fa fa-spinner fa-spin"></i> Cargando las consultas...</div>
                    </div>
                  </div>
                </div>
                <div class="btn-nav-container">
                  <button type="button" class="btn btn-second prev-tab" data-tab-anterior="ocupacion_estudios"><i class="fa fa-arrow-left"></i> Atrás</button>
                  <button type="button" class="btn btn-primary next-tab" data-tab-siguiente="salud_otros">Siguiente <i class="fa fa-arrow-right"></i></button>
                </div>
              </div>

              <!-- ═══════════ TAB ANTECEDENTES (con botones por categoría) ═══════════ -->
              <div class="tab-pane" id="salud_otros">
                <h4 class="section-title"><i class="fa fa-file-medical-alt"></i> Antecedentes Médicos</h4>
                <p class="text-muted" style="margin-bottom:15px;">
                  Seleccione una categoría para ver el detalle registrado<?= $puede_editar_antecedentes ? ' o editarlo' : '' ?>.
                </p>
                <div id="contenedor-antecedentes">
                  <div class="alert alert-info" style="border-radius:0px;">
                    <i class="fa fa-spinner fa-spin"></i> Cargando antecedentes médicos...
                  </div>
                </div>
                <div class="btn-nav-container">
                  <button class="btn btn-second prev-tab" data-tab-anterior="salud"><i class="fa fa-arrow-left"></i> Atrás</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Modal de Confirmación de Regreso -->
  <div class="modal fade" id="modalConfirmarRegreso" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h4 class="modal-title"><i class="fa fa-warning"></i> Confirmación de Regreso</h4>
        </div>
        <div class="modal-body">
          <p>¿Está a punto de cerrar la ficha del paciente y regresar al listado principal. ¿Desea continuar?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <a href="pacientes_listado.php" class="btn btn-danger">Regresar al listado</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Historial Completo de Consultas -->
  <div class="modal fade" id="modalTodasConsultas" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header bg-info">
          <h4 class="modal-title"><i class="fa fa-history"></i> Historial Completo de Consultas</h4>
        </div>
        <div class="modal-body" style="max-height: 500px; overflow-y: auto; background-color: var(--bg-gray); padding: 25px;">
          <div id="contenedor-modal-consultas" class="timeline"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- ═══════════ MODAL DUAL: VER/EDITAR ANTECEDENTE ═══════════ -->
  <div class="modal fade" id="modalVerAntecedente" tabindex="-1" role="dialog" data-mode="view" data-puede-editar="<?= $puede_editar_antecedentes ? '1' : '0' ?>" data-key="">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content" style="border-radius:0;">
        <div class="modal-header bg-info">
          <h4 class="modal-title" id="titulo-modal-antecedente">
            <i class="fa fa-file-text-o"></i> Antecedentes
          </h4>
        </div>
        <div class="modal-body" id="cuerpo-modal-antecedente" style="max-height:60vh; overflow-y:auto;">
        </div>
        <div class="modal-footer" style="background:#f8f9fa;">
          <div class="footer-view">
            <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
            <?php if ($puede_editar_antecedentes) : ?>
              <button type="button" class="btn btn-primary" id="btn-pasar-a-editar">
                <i class="fa fa-pencil"></i> Editar
              </button>
            <?php endif; ?>
          </div>
          <div class="footer-edit" style="display:none;">
            <button type="button" class="btn btn-second" id="btn-cancelar-edicion">Cancelar</button>
            <button type="button" class="btn btn-success" id="btn-guardar-antecedente">
              <i class="fa fa-save"></i> Guardar cambios
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include('includes/footer.php'); ?>

<script>
  /* ════════════════════════════════════════════════════════════════
       HELPERS
       ════════════════════════════════════════════════════════════════ */
  function esc(s) {
    if (s === null || s === undefined) return '';
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

  /* ════════════════════════════════════════════════════════════════
     TABS
     ════════════════════════════════════════════════════════════════ */
  $(document).ready(function() {
    if (!window.location.hash || window.location.hash === '#info') {
      $('a[href="#info"]').tab('show');
    }
    if (window.history.replaceState) {
      window.history.replaceState(null, null, window.location.pathname + window.location.search);
    }
  });

  $('.next-tab').on('click', function() {
    const tabSiguienteName = $(this).data('tab-siguiente');
    const nextTabLink = $(`.nav-tabs li[data-tab-name="${tabSiguienteName}"] a`);
    const $siguienteTabLi = $(`.nav-tabs li[data-tab-name="${tabSiguienteName}"]`);
    $('.nav-tabs li').removeClass('active');
    $('.tab-content .tab-pane').removeClass('active');
    $siguienteTabLi.addClass('active');
    nextTabLink.tab('show');
    $('#' + tabSiguienteName).addClass('active');
  });

  $('.prev-tab').on('click', function() {
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

  /* ════════════════════════════════════════════════════════════════
     BADGE GROUPS (alergias / patologías / discapacidades / reps)
     ════════════════════════════════════════════════════════════════ */
  function renderBadgeGroup(opts) {
    const {
      containerId,
      items = [],
      emptyText = 'Ninguno registrado',
      emptyColor = '#999',
      maxVisible = 3,
      title = 'Detalle',
      colorClass = '',
      badgeClass = ''
    } = opts;
    const $container = $('#' + containerId);
    $container.empty();

    if (!items.length) {
      $container.html(`<span class="val-custom" style="color:${emptyColor};">${emptyText}</span>`);
      return;
    }

    const visibles = items.slice(0, maxVisible);
    let html = '<div class="badge-container ' + (items.length > maxVisible ? 'has-overflow' : '') + '" id="badge-wrap-' + containerId + '">';
    visibles.forEach(it => {
      html += it.html || `<span class="${badgeClass}">${it.label}</span>`;
    });
    html += '</div>';

    if (items.length > maxVisible) {
      html += `
          <button type="button" class="ver-mas-btn" data-toggle="modal" data-target="#modalItems-${containerId}">
            <i class="fa fa-plus-circle"></i> Ver más (${items.length})
          </button>
          <div class="modal fade" id="modalItems-${containerId}" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
              <div class="modal-content" style="border-radius:0;">
                <div class="modal-header" style="background:var(--primary-dark); color:#fff;">
                  <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:.9;">&times;</button>
                  <h4 class="modal-title"><i class="fa fa-list"></i> ${title} (${items.length})</h4>
                </div>
                <div class="modal-body">
                  <div class="modal-items-scroll ${items.length > maxVisible ? 'scrollable' : ''}">
                    ${items.map(it => it.htmlModal || `<div class="chip-item ${colorClass}">${it.label}${it.sub ? '<small>' + it.sub + '</small>' : ''}</div>`).join('')}
                  </div>
                </div>
                <div class="modal-footer" style="background:#f8f9fa;">
                  <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
                </div>
              </div>
            </div>
          </div>`;
    }
    $container.html(html);
  }

  /* ════════════════════════════════════════════════════════════════
     DATOS BASE — iniciales de la ficha
     ════════════════════════════════════════════════════════════════ */
  $(document).ready(function() {
    const cedulaPaciente = "<?php echo $row['cedula']; ?>";
    window.datosActuales = {};

    const reps = <?= json_encode(array_map(function ($rep) {
                    $tel = (!empty(trim($rep['telefono_rep']))) ? $rep['prefijo_rep'] . '-' . $rep['telefono_rep'] : '';
                    $ced = $rep['tipo_cedula_rep'] . '-' . $rep['cedula_rep'];
                    $tipo = $rep['tipo_representante'] ?? 'Legal';
                    $parentesco = !empty(trim($rep['parentesco'])) ? ' · ' . $rep['parentesco'] : '';
                    $htmlVista = '<span class="badge-representante">' . htmlspecialchars($rep['nombre_rep'] . ' ' . $rep['apellido_rep']) . '</span>';
                    $htmlModal = '<div class="chip-item chip-teal">'
                      . '<strong>' . htmlspecialchars($rep['nombre_rep'] . ' ' . $rep['apellido_rep']) . '</strong>'
                      . '<span class="badge bg-' . ($tipo === 'Legal' ? 'blue' : 'yellow') . ' badge-estado" style="margin-right:6px;">' . htmlspecialchars($tipo) . '</span>'
                      . ($parentesco ? '<span class="badge bg-purple badge-estado">' . htmlspecialchars(trim($rep['parentesco'])) . '</span>' : '')
                      . '<small><i class="fa fa-id-card-o"></i> ' . htmlspecialchars($ced) . '</small>'
                      . ($tel ? '<small><i class="fa fa-phone"></i> ' . htmlspecialchars($tel) . '</small>' : '')
                      . (!empty(trim($rep['email_rep'])) ? '<small><i class="fa fa-envelope-o"></i> ' . htmlspecialchars($rep['email_rep']) . '</small>' : '')
                      . '</div>';
                    return ['label' => $rep['nombre_rep'] . ' ' . $rep['apellido_rep'], 'sub' => $ced . ($tel ? ' · ' . $tel : ''), 'html' => $htmlVista, 'htmlModal' => $htmlModal];
                  }, $representantes_array)) ?>;

    renderBadgeGroup({
      containerId: 'cont-representantes',
      items: reps,
      emptyText: 'Ninguno registrado',
      emptyColor: '#0d9488',
      maxVisible: 3,
      title: 'Representantes Legales',
      colorClass: 'chip-teal',
      badgeClass: 'badge-representante'
    });

    const alergias = <?= json_encode(!empty($row['lista_alergias']) ? array_map(function ($a) {
                        return ['label' => trim($a)];
                      }, explode('||', $row['lista_alergias'])) : []) ?>;
    renderBadgeGroup({
      containerId: 'cont-alergias',
      items: alergias,
      emptyText: 'Ninguna registrada',
      emptyColor: '#e67e22',
      maxVisible: 3,
      title: 'Alergias Conocidas',
      colorClass: 'chip-orange',
      badgeClass: 'badge-alergia'
    });

    const patologias = <?= json_encode(!empty($row['lista_patologias']) ? array_map(function ($p) {
                          return ['label' => trim($p)];
                        }, explode('||', $row['lista_patologias'])) : []) ?>;
    renderBadgeGroup({
      containerId: 'cont-patologias',
      items: patologias,
      emptyText: 'Ninguna registrada',
      emptyColor: '#e74c3c',
      maxVisible: 3,
      title: 'Patologías Crónicas',
      colorClass: 'chip-red',
      badgeClass: 'badge-patologia'
    });

    <?php if (count($discapacidades_array) > 0) : ?>
      const discs = <?= json_encode(array_map(function ($d) {
                      return ['label' => $d['nombre_discapacidad'], 'sub' => !empty($d['fecha_registro']) ? '(' . date('d/m/Y', strtotime($d['fecha_registro'])) . ')' : ''];
                    }, $discapacidades_array)) ?>;
      renderBadgeGroup({
        containerId: 'cont-discapacidades',
        items: discs,
        emptyText: 'Si (sin detalle)',
        emptyColor: '#8e44ad',
        maxVisible: 3,
        title: 'Discapacidades',
        colorClass: 'chip-purple',
        badgeClass: 'badge-discapacidad'
      });
    <?php else : ?>
      $('#cont-discapacidades').html('<span class="badge bg-red badge-estado">No</span>');
    <?php endif; ?>

    if (cedulaPaciente) {
      cargarHistorial(cedulaPaciente);
    }

    function cargarHistorial(cedula) {
      $.ajax({
        url: '../../cfg/ajax/obtener_historial.php',
        type: 'GET',
        data: {
          cedula: cedula
        },
        dataType: 'json',
        success: function(response) {
          if (response.success) {
            window.datosActuales = response.data.antecedentes || {};
            window.historialCompleto = response.data.historial_consultas || [];
            renderizarBotonesAntecedentesInfo();
            renderizarListaConsultas(window.historialCompleto.slice(0, 2), '#historial-consultas-container', true);
          } else {
            $('#historial-consultas-container').html('<div class="alert alert-danger" style="border-radius:0px;">Error al cargar el historial.</div>');
            $('#contenedor-antecedentes').html('<div class="alert alert-danger" style="border-radius:0px;">Error al cargar los antecedentes.</div>');
          }
        }
      });
    }

    function renderizarListaConsultas(consultas, contenedorId, mostrarLinkMas) {
      const $div = $(contenedorId);
      $div.empty();
      const lista = consultas || [];

      if (lista.length > 0) {
        lista.forEach(c => {
          const fecha = c.fecha_consulta.split('-').reverse().join('/');
          $div.append(`
              <div class="timeline-item">
                <div class="panel panel-default" style="border-radius: 0px; border-left: 4px solid var(--medical-blue); box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 0;">
                  <div class="panel-heading" style="background-color: #f8f9fa; border-bottom: 1px solid #eee; border-radius: 0px;">
                    <span class="text-primary" style="font-weight: 700; color: var(--primary-dark);"><i class="fa fa-calendar"></i> ${fecha}</span>
                    <?php if (in_array('Ver Consultas', $_SESSION["permisos"])) : ?>
                    <a href="consulta_info.php?Id=${c.Id_consulta}" class="pull-right small btn btn-default btn-xs" style="border-radius: 0px;"><i class="fa fa-eye"></i> Ver Detalle</a>
                    <?php endif; ?>
                    <span class="pull-right small text-muted" style="margin-right:15px; margin-top: 2px; font-weight: 600;"><i class="fa fa-user-md"></i> Dr/a. ${c.medico_nombre}</span>
                  </div>
                  <div class="panel-body">
                    <div class="row" style="margin-bottom: 10px;">
                      <div class="col-sm-12">
                        <span class="label-custom label-dark">Motivo</span>
                        <span class="val-custom val-dark">${c.motivo_consulta || 'N/A'}</span>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-sm-4">
                        <span class="label-custom label-dark">Diagnóstico</span>
                        <span class="badge bg-blue badge-estado">${c.diagnostico || 'N/A'}</span>
                      </div>
                      <div class="col-sm-8 text-right text-muted" style="font-size: 13px;">
                        <strong>Peso:</strong> ${c.peso || '-'} | <strong>Talla:</strong> ${c.talla || '-'} |
                        <strong>Sat:</strong> ${c.saturacion || '-'} | <strong>TA:</strong> ${c.tension || '-'} | <strong>Temp:</strong> ${c.temperatura || '-'}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            `);
        });

        if (mostrarLinkMas && window.historialCompleto && window.historialCompleto.length > 2) {
          $div.append(`
              <div class="text-center" style="margin-top: 20px;">
                <a href="#modalTodasConsultas" data-toggle="modal" class="btn btn-default" style="border-radius: 0px; font-weight: 600; border: 1px solid var(--medical-blue); color: var(--medical-blue);">
                  Ver el historial completo (${window.historialCompleto.length} registros)
                </a>
              </div>
            `);
        }
      } else {
        $div.append(`<div class="alert alert-warning text-center" style="border-radius: 0px; background-color: #fff9e6; border: 1px dashed #f39c12; color: #d35400;"><i class="fa fa-info-circle fa-lg"></i><br>No se encontraron consultas previas.</div>`);
      }
    }

    $(document).on('show.bs.modal', '#modalTodasConsultas', function() {
      renderizarListaConsultas(window.historialCompleto, '#contenedor-modal-consultas', false);
    });

    /* ════════════════════════════════════════════════════════════════
       ANTECEDENTES: TEMPLATES (mismos que consulta_editar.php)
       ════════════════════════════════════════════════════════════════ */
    window.ANTECEDENTES_TEMPLATES = {
      perinatales: {
        titulo: '2.a) Perinatales (Menores de 19)',
        icono: 'fa-baby',
        onlyMinor: true,
        items: [{
            label: '2.a.1 Carnet Prenatal'
          },
          {
            label: '2.a.2 N° Consultas Prenatal (Z34.9)',
            input: 'number',
            placeholder: 'N°'
          },
          {
            label: '2.a.3 Trastornos del Embarazo (Z87.5)'
          },
          {
            label: '2.a.4 Embarazo a término (Z34.9)'
          },
          {
            label: '2.a.5 Parto único espontáneo (O80.9)'
          },
          {
            label: '2.a.6 Parto por cesárea (Z87.5)'
          },
          {
            label: '2.a.7 Parto por fórceps (Z87.5)'
          },
          {
            label: '2.a.8 Complicaciones del parto (Z87.5)'
          },
          {
            label: '2.a.9 Complicación del puerperio (Z87.5)'
          },
          {
            label: '2.a.10 Trastornos del RN (Z87.6)'
          },
          {
            label: '2.a.11 Reanimación (Z87.6)'
          },
          {
            label: '2.a.12 Peso al Nacer (Z00.1)',
            input: 'number',
            placeholder: 'gr'
          },
          {
            label: '2.a.13 Talla al Nacer (Z00.1)',
            input: 'number',
            placeholder: 'cm'
          },
          {
            label: '2.a.14 Perímetro Cefálico (Z00.1)',
            input: 'number',
            placeholder: 'cm'
          },
          {
            label: '2.a.15 Lactancia Exclusiva (Z13.2)',
            input: 'number',
            placeholder: 'meses'
          },
          {
            label: '2.a.16 Lactancia Mixta (Z13.2)',
            input: 'number',
            placeholder: 'meses'
          },
          {
            label: '2.a.17 Ablactación (Z13.2)',
            input: 'number',
            placeholder: 'meses'
          },
          {
            label: '2.a.18 Egreso RN Patológico (Z87.6)'
          },
          {
            label: '2.a.19 Asfixia (Z87.6)'
          },
          {
            label: '2.a.20 Egreso RN Sano (Z87.6)'
          }
        ]
      },
      familiares: {
        titulo: '2.b) Familiares y Otros Contactos',
        icono: 'fa-users',
        items: [{
            label: '2.b.1 Cáncer (Z80.9)',
            parentesco: true
          },
          {
            label: '2.b.2 Alcoholismo (Z81.1)',
            parentesco: true
          },
          {
            label: '2.b.3 Tabaquismo (Z81.2)',
            parentesco: true
          },
          {
            label: '2.b.4 Drogas (Z81.3)',
            parentesco: true
          },
          {
            label: '2.b.5 T. Mentales (Z81.8)',
            parentesco: true
          },
          {
            label: '2.b.6 Neurológica (Z82.0)',
            parentesco: true
          },
          {
            label: '2.b.7 Varices (Z82.4)',
            parentesco: true
          },
          {
            label: '2.b.8 Cardiopatía (Z82.4)',
            parentesco: true
          },
          {
            label: '2.b.9 HTA Sist. (Z82.4)',
            parentesco: true
          },
          {
            label: '2.b.10 Asma (Z82.5)',
            parentesco: true
          },
          {
            label: '2.b.11 Artritis (Z82.6)',
            parentesco: true
          },
          {
            label: '2.b.12 Malform. Congénita (Z82.7)',
            parentesco: true
          },
          {
            label: '2.b.13 VIH/SIDA (Z83.0)',
            parentesco: true
          },
          {
            label: '2.b.14 Sífilis/ITS (Z83.1)',
            parentesco: true
          },
          {
            label: '2.b.15 Tuberculosis (Z83.1)',
            parentesco: true
          },
          {
            label: '2.b.16 Alergia (Z83.2)',
            parentesco: true
          },
          {
            label: '2.b.17 Diabetes (Z83.3)',
            parentesco: true
          },
          {
            label: '2.b.18 Desnutrición (Z83.4)',
            parentesco: true
          },
          {
            label: '2.b.19 Obesidad (Z83.4)',
            parentesco: true
          },
          {
            label: '2.b.20 Gastropatía (Z83.7)',
            parentesco: true
          },
          {
            label: '2.b.21 Enf. Renal (Z84.1)',
            parentesco: true
          },
          {
            label: '2.b.22 Otras (observaciones)',
            parentesco: true
          }
        ]
      },
      gineco: {
        titulo: '2.c) Sexuales y Reproductivos',
        icono: 'fa-venus-mars',
        items: [{
            label: '2.c.1 Telarquia',
            onlySex: 'Femenino'
          },
          {
            label: '2.c.2 Pubarquia',
            onlySex: 'Femenino'
          },
          {
            label: '2.c.3 Menarquia',
            input: 'number',
            placeholder: 'edad',
            onlySex: 'Femenino'
          },
          {
            label: '2.c.4 Inicio Prec. Testicular',
            input: 'number',
            placeholder: 'edad',
            onlySex: 'Masculino',
            onlyMinor: true
          },
          {
            label: '2.c.5 Ciclo Menstrual',
            input: 'text',
            placeholder: 'ej: 28 días',
            onlySex: 'Femenino'
          },
          {
            label: '2.c.6 Primera Relación Sexual',
            input: 'number',
            placeholder: 'edad'
          },
          {
            label: '2.c.7 Frecuencia de Rel. Sexual'
          },
          {
            label: '2.c.8 Número de Parejas',
            input: 'number',
            placeholder: 'N°'
          },
          {
            label: '2.c.9 Rel. Sexual Satisfactoria'
          },
          {
            label: '2.c.10 Anticoncepción (ACO/DIU/Otro)'
          },
          {
            label: '2.c.11 Número de Gestas',
            input: 'number',
            placeholder: 'N°',
            onlySex: 'Femenino'
          },
          {
            label: '2.c.12 Número de Partos',
            input: 'number',
            placeholder: 'N°',
            onlySex: 'Femenino'
          },
          {
            label: '2.c.13 Número de Cesáreas',
            input: 'number',
            placeholder: 'N°',
            onlySex: 'Femenino'
          },
          {
            label: '2.c.14 Número de Abortos',
            input: 'number',
            placeholder: 'N°',
            onlySex: 'Femenino'
          },
          {
            label: '2.c.15 Legrado Uterino',
            onlySex: 'Femenino'
          },
          {
            label: '2.c.16 Menopausia / Andropausia'
          },
          {
            label: '2.c.17 Otros'
          }
        ]
      },
      habitos: {
        titulo: '2.c) Estilo y Modo de Vida',
        icono: 'fa-heart',
        items: [{
            label: '2.c.18 Fuma (Z72.0)'
          },
          {
            label: '2.c.19 ¿Desde cuándo Fuma? (Z72.0)',
            input: 'text',
            placeholder: 'años',
            dependsOn: '2.c.18 Fuma (Z72.0)'
          },
          {
            label: '2.c.20 N° de Cigarrillos al día (Z72.0)',
            input: 'number',
            placeholder: 'N°',
            dependsOn: '2.c.18 Fuma (Z72.0)'
          },
          {
            label: '2.c.21 Alcohol (Z72.1)'
          },
          {
            label: '2.c.22 Cant. de Alcohol x semana (Z72.1)',
            input: 'text',
            placeholder: 'ej: 3 latas',
            dependsOn: '2.c.21 Alcohol (Z72.1)'
          },
          {
            label: '2.c.23 Drogas ilícitas (Z72.2)'
          },
          {
            label: '2.c.24 Actividad Física (Z72.3)'
          },
          {
            label: '2.c.25 Sedentarismo (Z72.3)'
          },
          {
            label: '2.c.26 Manejo del Estrés (Z72.3)'
          },
          {
            label: '2.c.46 Chupa Dedo (Z86.5)'
          },
          {
            label: '2.c.47 Onicofagia (Z86.5)'
          },
          {
            label: '2.c.27 Otros Habitos'
          }
        ]
      },
      patologicos: {
        titulo: '2.c) Eventos y Situaciones Personales',
        icono: 'fa-stethoscope',
        items: [{
            label: '2.c.28 Violencia Psicológica (Z91.4)'
          },
          {
            label: '2.c.29 Violencia Física (Z91.6)'
          },
          {
            label: '2.c.30 Violencia Sexual (Z91.6)'
          },
          {
            label: '2.c.31 Accidentes (Z91.8)'
          },
          {
            label: '2.c.32 Otros eventos (Z91.8)'
          },
          {
            label: '2.c.32 Hospitalización (Z91.8)',
            input: 'text',
            placeholder: 'año/motivo'
          },
          {
            label: '2.c.34 Intervención Quirúrgica (Z98.8)',
            input: 'text',
            placeholder: 'año/procedimiento'
          },
          {
            label: '2.c.35 Otros'
          }
        ]
      }
    };

    const ANTECEDENTES_META = {
      perinatales: {
        titulo: '2.a) Perinatales',
        icono: 'fa-baby'
      },
      familiares: {
        titulo: '2.b) Familiares y otros contactos',
        icono: 'fa-users'
      },
      gineco: {
        titulo: '2.c) Sexuales y reproductivos',
        icono: 'fa-venus-mars'
      },
      habitos: {
        titulo: '2.c) Estilo y modo de vida',
        icono: 'fa-heart'
      },
      patologicos: {
        titulo: '2.c) Eventos y situaciones personales',
        icono: 'fa-stethoscope'
      }
    };

    /* Edad y sexo del paciente para filtros de templates */
    const _PAC_EDAD = <?php
                      $nac_pac = new DateTime($row['fecha_nacimiento']);
                      echo (new DateTime())->diff($nac_pac)->y;
                      ?>;
    const _PAC_GENERO = "<?= htmlspecialchars($row['genero'] ?? '') ?>";
    const _PAC_IS_F = _PAC_GENERO.toLowerCase().includes('femen');
    const _PAC_IS_M = !_PAC_IS_F;

    /* ════════════════════════════════════════════════════════════════
       NORMALIZACIÓN (acepta objeto nuevo o string antiguo)
       ════════════════════════════════════════════════════════════════ */
    function normalizarDatosAnt(datos) {
      if (typeof datos === 'string') {
        const s = datos.trim();
        if (!s) return {
          items: {},
          obs: ''
        };
        try {
          const parsed = JSON.parse(s);
          if (typeof parsed === 'object' && parsed !== null) return normalizarDatosAnt(parsed);
        } catch (e) {
          /* no es JSON */
        }
        return {
          items: {
            'Registro': s
          },
          obs: ''
        };
      }
      if (typeof datos === 'object' && datos !== null) {
        const items = Object.assign({}, datos);
        const obs = items.__obs__ || '';
        delete items.__obs__;
        return {
          items,
          obs
        };
      }
      return {
        items: {},
        obs: ''
      };
    }

    /* ════════════════════════════════════════════════════════════════
       RENDER DE BOTONES (5 categorías en el tab)
       ════════════════════════════════════════════════════════════════ */
    function renderizarBotonesAntecedentesInfo() {
      const $cont = $('#contenedor-antecedentes').empty();
      let html = '<div class="row">';
      Object.entries(ANTECEDENTES_META).forEach(([key, meta]) => {
        const datos = normalizarDatosAnt(window.datosActuales[key] || {});
        const cant = Object.keys(datos.items).length;
        const badge = cant > 0 ?
          ` <span class="badge" style="background:#fff;color:#3c8dbc;">${cant}</span>` :
          '';
        html += `
            <div class="col-xs-12 col-sm-6 col-md-4" style="margin-bottom:10px;">
              <button type="button" class="btn btn-info btn-block btn-antecedente-tipo btn-ver-antecedente"
                      data-key="${key}">
                <i class="fa ${meta.icono} fa-2x pull-left" style="margin-top:5px; margin-right:10px;"></i>
                <span style="display:block; font-size:12px; opacity:.8; margin-bottom:3px;">${esc(meta.titulo)}</span>
                <span style="font-size:13px; font-weight:bold; line-height:1.2;">Ver detalle</span>
                ${badge}
              </button>
            </div>`;
      });
      html += '</div>';
      $cont.html(html);
    }

    /* ════════════════════════════════════════════════════════════════
       MODO VER
       ════════════════════════════════════════════════════════════════ */
    function renderAntecedenteView(key) {
      const meta = ANTECEDENTES_META[key];
      const datos = normalizarDatosAnt(window.datosActuales[key] || {});
      const items = datos.items;
      const obs = datos.obs;

      let body;
      if (Object.keys(items).length === 0 && !obs) {
        body = '<div class="alert alert-warning" style="border-radius:0;">' +
          '<i class="fa fa-info-circle"></i> No hay datos registrados en esta categoría.' +
          '</div>';
      } else {
        body = '';
        if (Object.keys(items).length > 0) {
          body += '<ul class="antecedente-list">';
          for (const [label, valor] of Object.entries(items)) {
            const detalle = (valor === true || valor === '' || valor == null) ?
              '' :
              `: <b style="color:var(--medical-blue);">${esc(valor)}</b>`;
            body += `<li><i class="fa fa-check-circle"></i> ${esc(label)}${detalle}</li>`;
          }
          body += '</ul>';
        }
        if (obs) {
          body += '<hr><p class="label-custom label-dark">Observaciones</p>' +
            '<div class="data-item-card" style="margin-top:5px;">' + esc(obs) + '</div>';
        }
      }

      $('#titulo-modal-antecedente').html(`<i class="fa ${meta.icono}"></i> ${esc(meta.titulo)}`);
      $('#cuerpo-modal-antecedente').html(body);
    }

    /* ════════════════════════════════════════════════════════════════
       MODO EDITAR (checklists)
       ════════════════════════════════════════════════════════════════ */
    function renderAntecedenteEdit(key) {
      const tmpl = ANTECEDENTES_TEMPLATES[key];
      const meta = ANTECEDENTES_META[key];
      if (!tmpl) return;

      const datos = normalizarDatosAnt(window.datosActuales[key] || {});
      const seleccionados = datos.items;
      const obs = datos.obs;

      let html = '<div class="row">';
      tmpl.items.forEach(item => {
        const label = typeof item === 'string' ? item : item.label;
        const inputType = typeof item === 'object' ? item.input : null;
        const placeholder = typeof item === 'object' ? item.placeholder : '';
        const dependsOn = typeof item === 'object' ? item.dependsOn : null;
        const parentesco = typeof item === 'object' ? item.parentesco : false;

        if (item.onlySex === 'Femenino' && !_PAC_IS_F) return;
        if (item.onlySex === 'Masculino' && !_PAC_IS_M) return;
        if (item.onlyMinor && _PAC_EDAD >= 19) return;

        const isChecked = seleccionados[label] !== undefined;
        const valExtra = isChecked && typeof seleccionados[label] === 'string' ? seleccionados[label] : '';
        const checked = isChecked ? 'checked' : '';
        const parentOk = dependsOn ? (seleccionados[dependsOn] !== undefined) : true;
        const wrapperStyle = (dependsOn && !parentOk) ? 'display:none;' : '';

        html += `
            <div class="col-sm-12 col-md-6 parent-col" style="${wrapperStyle}" data-child-of="${dependsOn ? esc(dependsOn) : ''}">
              <div class="chk-antecedente-wrap" style="display:flex; align-items:center; justify-content:space-between;">
                <label style="flex:1; margin-right:10px;">
                  <input type="checkbox" class="chk-antecedente" value="${esc(label)}"
                         data-depends-on="${dependsOn ? esc(dependsOn) : ''}" ${checked}>
                  ${esc(label)}
                </label>`;

        if (parentesco) {
          const opts = ['Padre', 'Madre', 'Hermanos', 'Abuelos', 'Otros'];
          html += `<select class="form-control input-sm input-extra-antecedente select-parentesco"
                             style="width:100px; display:${isChecked ? 'block' : 'none'};">
                       <option value="">--</option>
                       ${opts.map(p => `<option value="${p}" ${valExtra === p ? 'selected' : ''}>${p}</option>`).join('')}
                     </select>`;
        } else if (inputType) {
          html += `<input type="${inputType}" class="form-control input-sm input-extra-antecedente"
                           placeholder="${esc(placeholder)}" value="${esc(valExtra)}"
                           style="width:90px; display:${isChecked ? 'block' : 'none'};">`;
        }

        html += '</div></div>';
      });
      html += '</div>';

      html += `<div class="form-group" style="margin-top:15px;">
                   <label><b>Observaciones adicionales:</b></label>
                   <textarea class="form-control" id="obs-modal-antecedente" rows="2"
                             placeholder="Detalles u observaciones...">${esc(obs)}</textarea>
                 </div>`;

      $('#titulo-modal-antecedente').html(`<i class="fa ${meta.icono}"></i> Editando: ${esc(meta.titulo)}`);
      $('#cuerpo-modal-antecedente').html(html);
    }

    /* ════════════════════════════════════════════════════════════════
       HANDLERS VER ↔ EDITAR ↔ GUARDAR
       ════════════════════════════════════════════════════════════════ */
    $(document).on('click', '.btn-ver-antecedente', function() {
      const key = $(this).data('key');
      $('#modalVerAntecedente')
        .attr('data-mode', 'view')
        .attr('data-key', key)
        .find('.footer-view').show().end()
        .find('.footer-edit').hide();
      renderAntecedenteView(key);
      $('#modalVerAntecedente').modal('show');
    });

    $(document).on('click', '#btn-pasar-a-editar', function() {
      const key = $('#modalVerAntecedente').attr('data-key');
      if (!key) return;
      $('#modalVerAntecedente')
        .attr('data-mode', 'edit')
        .find('.footer-view').hide().end()
        .find('.footer-edit').show();
      renderAntecedenteEdit(key);
    });

    $(document).on('click', '#btn-cancelar-edicion', function() {
      const key = $('#modalVerAntecedente').attr('data-key');
      if (!key) return;

      // ¿Hay cambios reales?
      const original = normalizarDatosAnt(window.datosActuales[key] || {});
      const items = {};
      $('#cuerpo-modal-antecedente .chk-antecedente:checked').each(function() {
        const label = $(this).val();
        const $inp = $(this).closest('.chk-antecedente-wrap').find('.input-extra-antecedente');
        items[label] = ($inp.length && $inp.val().trim() !== '') ? $inp.val().trim() : true;
      });
      const obs = $('#obs-modal-antecedente').val().trim();

      const hayCambios =
        JSON.stringify(items) !== JSON.stringify(original.items) ||
        obs !== original.obs;

      const volver = () => {
        $('#modalVerAntecedente')
          .attr('data-mode', 'view')
          .find('.footer-view').show().end()
          .find('.footer-edit').hide();
        renderAntecedenteView(key);
      };

      if (!hayCambios) return volver();
      if (confirm('Hay cambios sin guardar. ¿Descartarlos?')) volver();
    });

    $(document).on('change', '.chk-antecedente', function() {
      const label = $(this).val();
      const isChecked = this.checked;

      const $input = $(this).closest('.chk-antecedente-wrap').find('.input-extra-antecedente');
      if ($input.length) {
        $input.toggle(isChecked);
        if (!isChecked) $input.val('');
      }

      const $childCols = $(`.parent-col[data-child-of="${esc(label)}"]`);
      if ($childCols.length > 0) {
        if (isChecked) {
          $childCols.slideDown(200);
        } else {
          $childCols.slideUp(200, function() {
            $(this).find('.chk-antecedente').prop('checked', false).trigger('change');
          });
        }
      }
    });

    $(document).on('click', '#btn-guardar-antecedente', function() {
      const key = $('#modalVerAntecedente').attr('data-key');
      if (!key) return;

      const $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

      const items = {};
      $('#cuerpo-modal-antecedente .chk-antecedente:checked').each(function() {
        const label = $(this).val();
        const $inp = $(this).closest('.chk-antecedente-wrap').find('.input-extra-antecedente');
        items[label] = ($inp.length && $inp.val().trim() !== '') ? $inp.val().trim() : true;
      });
      const obs = $('#obs-modal-antecedente').val().trim();

      $.ajax({
        url: '../../cfg/ajax/editar_antecedentes.php',
        type: 'POST',
        dataType: 'json',
        data: {
          cedula: "<?= htmlspecialchars($row['cedula']) ?>",
          categoria: key,
          datos: JSON.stringify(items),
          observaciones: obs
        },
        success: function(r) {
          $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar cambios');

          if (!r || !r.success) {
            alert((r && r.error) || 'No se pudo guardar.');
            return;
          }

          // Actualizar estado local (con __obs__ dentro para conservar la observación)
          const nuevosDatos = Object.assign({}, items);
          if (obs) nuevosDatos.__obs__ = obs;
          window.datosActuales[key] = nuevosDatos;

          // Volver a modo ver
          $('#modalVerAntecedente')
            .attr('data-mode', 'view')
            .find('.footer-view').show().end()
            .find('.footer-edit').hide();
          renderAntecedenteView(key);
          renderizarBotonesAntecedentesInfo();
        },
        error: function() {
          $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar cambios');
          alert('Error de conexión al guardar.');
        }
      });
    });

    $(document).on('hidden.bs.modal', '#modalVerAntecedente', function() {
      $(this).attr('data-mode', 'view').attr('data-key', '');
      $(this).find('.footer-view').show().end().find('.footer-edit').hide();
    });
  });
</script>