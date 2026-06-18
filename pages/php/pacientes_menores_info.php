<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Pacientes Menores | Información</title>
  <?php
  include('includes/headerNav2.php');
  ?>
  <style>
    :root {
      --primary-dark: #2c3e50;
      --medical-blue: #007bff;
      --bg-gray: #f4f7f9;
    }

    body, html {
      background-color: var(--bg-gray) !important;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      overflow: hidden !important; /* Elimina la barra de desplazamiento global */
    }

    .content-wrapper {
      background-color: var(--bg-gray) !important;
      height: 100vh;
      overflow: hidden;
    }

    .content-custome {
      padding: 15px;
      height: calc(100vh - 50px); /* Altura restando el top header de AdminLTE */
    }

    /* --- CONTENEDOR PRINCIPAL: ESTIRADO, ESTÁTICO Y SIN TOCAR ABAJO --- */
    .profile-card {
      width: 98%; /* Más amplio hacia los lados */
      max-width: none; 
      margin: 0 auto 30px auto; /* 30px de margen abajo para que no toque el fondo */
      background: #fff;
      border-radius: 0px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      border: none;
      
      /* Altura exacta para que no se desborde la ventana */
      height: calc(120vh - 140px); 
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }

    /* --- CABECERA ESTILO MÉDICO --- */
    .profile-header {
      background: linear-gradient(135deg, var(--primary-dark) 0%, #34495e 100%);
      color: white;
      padding: 25px 40px; /* Padding un poco más ajustado para ganar espacio vertical */
      border-bottom: 5px solid var(--medical-blue);
      flex-shrink: 0; 
    }

    .paciente-avatar {
      width: 100px;
      height: 100px;
      background: transparent;
      border-radius: 0px;
      padding: 0;
      box-shadow: none;
      object-fit: contain;
    }

    /* --- ETIQUETAS Y VALORES ESTILO MÉDICO --- */
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

    /* --- SISTEMA DE PESTAÑAS (TABS) ADAPTADO --- */
    .nav-tabs-custom {
      background: transparent;
      box-shadow: none;
      display: flex;
      flex-direction: column;
      flex: 1; /* Absorbe todo el espacio restante de la tarjeta */
      overflow: hidden; /* Evita desbordamiento exterior */
    }

    .nav-tabs {
      border-bottom: 2px solid #eee;
      background: #f8f9fa;
      padding: 0 20px;
      flex-shrink: 0;
    }

    .nav-tabs>li>a {
      border-radius: 0 !important;
      color: #7f8c8d !important;
      font-weight: 600;
      text-transform: uppercase;
      font-size: 13px;
      padding: 15px 20px;
      border: none !important;
      transition: all 0.3s ease;
    }

    .nav-tabs>li>a:hover {
      background-color: #e9ecef;
      color: var(--primary-dark) !important;
    }

    .nav-tabs>li.active>a,
    .nav-tabs>li.active>a:focus,
    .nav-tabs>li.active>a:hover {
      color: var(--medical-blue) !important;
      background-color: #fff !important;
      border-bottom: 3px solid var(--medical-blue) !important;
    }

    .tab-content-container {
      padding: 30px;
      flex: 1; /* Ocupa el espacio dinámico disponible */
      overflow-y: auto; /* Muestra scroll SOLAMENTE si es estrictamente necesario */
      overflow-x: hidden;
    }

    /* --- BADGES Y ALERTAS --- */
    .badge-alergia, .badge-patologia {
      color: white;
      padding: 6px 12px;
      border-radius: 0px;
      font-size: 12px;
      display: inline-block;
      margin-right: 5px;
      margin-bottom: 5px;
      font-weight: 600;
    }
    
    .badge-alergia { background-color: #e67e22; }
    .badge-patologia { background-color: #e74c3c; }

    .badge-estado {
      padding: 4px 10px;
      border-radius: 0px;
      font-weight: 600;
      font-size: 11px;
      text-transform: uppercase;
    }

    /* Botones de Navegación Inferior */
    .btn-nav-container {
      display: flex;
      justify-content: flex-end;
      gap: 15px;
      margin-top: 30px;
      padding-top: 20px;
      border-top: 1px solid #eee;
    }

    /* Timeline */
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

    /* Estilos Modales */
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-50px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes fadeOut { from { opacity: 1; transform: translateY(0); } to { opacity: 0; transform: translateY(-50px); } }

    .modal.in .modal-dialog { animation: fadeIn 0.3s ease-out; }
    .modal.out .modal-dialog { animation: fadeOut 0.3s ease-in; }
    .modal-content { border-radius: 0px !important; }

    /* Asegurar buena visualización en dispositivos pequeños */
    @media (max-width: 768px) {
      body, html { overflow: auto !important; }
      .profile-card { height: auto; margin-bottom: 20px; }
      .tab-content-container { overflow-y: visible; }
    }
  </style>
</head>

<body class="hold-transition skin-blue sidebar-mini">
  <div class="content-wrapper">
    <section class="content content-custome">
      <div class="row w-100 m-0" style="width: 100%; margin: 0;">
        <div class="col-xs-12" style="padding: 0;">

          <div class="profile-card">

            <?php
            include("../../cfg/conexion.php");

            // CONSULTA SQL
            $sql = "SELECT 
                p.id, p.email, p.nombre, p.apellido, p.tipo_cedula, p.cedula, p.fecha_nacimiento, p.genero,
                dp.etnia, dp.analfabeta, dp.tipo_etnia, dp.nivel_instruccion, dp.mision, dp.años_aprobados, dp.parentesco, dp.discapacidad, dp.tipo_discapacidad, dp.Id_representante,
                hm.grupo_sanguineo, 
                ptrp.prefijo, 
                munnac.nombre_municipio, estnac.nombre_estado, paisnac.nombre_pais, dirsec.nombre_sector, direst.nombre_estado,
                d.avenida_calle, d.referencia, d.tiempo_residencia, d.tiempo, d.Id_sector,  

                ln.Id_municipio AS Id_Municipio_Nac,  
                munnac.nombre_municipio AS Municipio_Nac,
                munnac.Id_Estado AS Id_Estado_Nac,   
                estnac.Id_Pais AS Id_Pais_Nac,      
                paisnac.nombre_pais AS Pais_Nac,
                estnac.nombre_estado AS Estado_Nac,

                direst.Id_Pais AS Id_Pais_Dir,
                paisdir.nombre_pais AS Pais_Dir,
                dirsec.Id_Municipio AS Id_Municipio_Dir,
                dirmun.nombre_municipio AS Municipio_Dir,
                dirmun.Id_Estado AS Id_Estado_Dir,
                direst.nombre_estado AS Estado_Dir,
                dirsec.nombre_sector AS Sector_Dir,

                p.id AS id_menor, 
                p.nombre AS nombre_menor, 
                p.apellido AS apellido_menor, 
                p.tipo_cedula AS tipo_cedula_menor, 
                p.cedula AS documento_menor,
                p.fecha_nacimiento AS fecha_nacimiento_menor,
                p.genero AS genero_menor,
                dp.etnia AS etnia_menor,
                dp.tipo_etnia AS etnia_tipo_menor,
                dp.analfabeta AS analfabeta_menor, 
                dp.nivel_instruccion AS instruccion_menor,
                dp.discapacidad AS discapacidad_menor,
                dp.tipo_discapacidad AS discapacidad_tipo_menor,

                r.id AS id_representante, 
                r.nombre AS nombre_representante, 
                r.apellido AS apellido_representante, 
                r.tipo_cedula AS tipo_cedula_representante, 
                r.cedula AS cedula_representante,
                r.email AS email_representante,
                dp.parentesco AS relacion_menor,

                trp.telefono AS telefono_representante,
                ptrp.Id AS id_prefijo_representante,
                ptrp.prefijo AS prefijo_representante,

                GROUP_CONCAT(DISTINCT CONCAT(pat.nombre_patologia, ' Fecha: ', hp.fecha_registro) SEPARATOR '||') AS patologias_data,
                GROUP_CONCAT(DISTINCT CONCAT(al.nombre_alergia, ' Fecha: ', ha.fecha_registro) SEPARATOR '||') AS alergias_data

                FROM persona p
                LEFT JOIN telefonos_personas tp ON p.id = tp.Id_persona
                LEFT JOIN prefijos_telefonos pt ON tp.Id_prefijo = pt.Id
                LEFT JOIN historial_medico hm ON p.id = hm.Id_persona 
                LEFT JOIN detalle_paciente_menor dp ON p.id = dp.Id_persona 

                LEFT JOIN lugar_nacimiento ln ON p.id = ln.Id_persona
                LEFT JOIN municipio munnac ON ln.Id_municipio = munnac.Id_Municipio
                LEFT JOIN estado estnac ON munnac.Id_Estado = estnac.Id_Estado
                LEFT JOIN pais paisnac ON paisnac.Id_Pais = estnac.Id_Pais

                LEFT JOIN direccion d ON p.id = d.Id_persona
                LEFT JOIN sector dirsec ON d.Id_sector = dirsec.Id_Sector
                LEFT JOIN municipio dirmun ON dirsec.Id_Municipio = dirmun.Id_Municipio
                LEFT JOIN estado direst ON dirmun.Id_Estado = direst.Id_Estado
                LEFT JOIN pais paisdir ON paisdir.Id_Pais = direst.Id_Pais

                LEFT JOIN persona r ON dp.Id_representante = r.id
                LEFT JOIN telefonos_personas trp ON r.id = trp.Id_persona
                LEFT JOIN prefijos_telefonos ptrp ON trp.Id_prefijo = ptrp.Id

                LEFT JOIN historial_patologias hp ON hm.id_historial = hp.Id_Historial AND p.id = hp.Id_persona
                LEFT JOIN patologias pat ON hp.Id_patologia = pat.Id_patologia
                LEFT JOIN historial_alergias ha ON hm.id_historial = ha.Id_Historial AND p.id = ha.Id_persona
                LEFT JOIN alergias_conocidas al ON ha.Id_alergia = al.Id_alergias_conocidas

                WHERE p.id =" . $_GET['Id'] . "
                GROUP BY p.id";

            $resultado = $conexion->query($sql);
            $row = $resultado->fetch_assoc();

            // Extracción de variables
            $id_pais_nac = (!empty(trim($row['Pais_Nac']))) ? $row['Pais_Nac'] : 'No registrado';
            $id_estado_nac = (!empty(trim($row['Estado_Nac']))) ? $row['Estado_Nac'] : 'No registrado';
            $id_municipio_nac = (!empty(trim($row['Municipio_Nac']))) ? $row['Municipio_Nac'] : 'No registrado';

            $id_pais_dir = (!empty(trim($row['Pais_Dir']))) ? $row['Pais_Dir'] : 'No registrado';
            $id_estado_dir = (!empty(trim($row['Estado_Dir']))) ? $row['Estado_Dir'] : 'No registrado';
            $id_municipio_dir = (!empty(trim($row['Municipio_Dir']))) ? $row['Municipio_Dir'] : 'No registrado';
            $id_sector_dir = (!empty(trim($row['Sector_Dir']))) ? $row['Sector_Dir'] : 'No registrado';

            $patologias_data = $row['patologias_data'] ?? '';
            $alergias_data = $row['alergias_data'] ?? '';

            $nombre_menor = $row['nombre_menor'] ?? '';
            $apellido_menor = $row['apellido_menor'] ?? '';
            $tipo_cedula_menor = $row['tipo_cedula_menor'] ?? 'PN';
            $documento_menor = $row['documento_menor'] ?? '';

            $fecha_nac_orig = $row['fecha_nacimiento_menor'] ?? '';
            $fecha_nac_display = (!empty($fecha_nac_orig) && $fecha_nac_orig != '0000-00-00') ? date('d/m/Y', strtotime($fecha_nac_orig)) : 'No registrado';

            $genero_menor = (!empty(trim($row['genero_menor']))) ? $row['genero_menor'] : 'No registrado';
            $etnia_menor = $row['etnia_menor'] ?? '';
            $etnia_tipo_menor = $row['etnia_tipo_menor'] ?? '';
            $analfabeta_menor = $row['analfabeta_menor'] ?? '';
            $instruccion_menor = $row['instruccion_menor'] ?? '';
            $mision_menor = $row['mision'] ?? '';
            $años_aprobados_menor = $row['años_aprobados'] ?? '';
            $discapacidad = $row['discapacidad_menor'] ?? '';
            $discapacidad_tipo_menor = $row['discapacidad_tipo_menor'] ?? '';

            $nombre_representante = $row['nombre_representante'] ?? '';
            $apellido_representante = $row['apellido_representante'] ?? '';
            $tipo_cedula_representante = $row['tipo_cedula_representante'] ?? 'V';
            $cedula_representante = $row['cedula_representante'] ?? '';
            $relacion_menor = $row['relacion_menor'] ?? '';
            $telefono_representante = $row['telefono_representante'] ?? '';
            $id_prefijo_representante = $row['prefijo_representante'] ?? '';

            $avenida_calle = (!empty(trim($row['avenida_calle']))) ? $row['avenida_calle'] : 'Ninguna';
            $referencia = (!empty(trim($row['referencia']))) ? $row['referencia'] : 'Ninguno';
            $tiempo_residencia = $row['tiempo_residencia'] ?? '';
            $tiempo = $row['tiempo'] ?? '';
            $residencia_str = (!empty(trim($tiempo_residencia))) ? $tiempo_residencia . ' ' . $tiempo : 'No registrado';

            $grupo_sanguineo = (!empty(trim($row['grupo_sanguineo']))) ? $row['grupo_sanguineo'] : 'No registrado';
            ?>

            <input type="hidden" name="Id" value="<?= $row['id']; ?>">

            <div class="profile-header">
              <div class="row w-100">
                <div class="col-sm-2 text-center">
                  <img src="../../recursos/imagenes/iconos/Paciente_icon1.png" class="paciente-avatar" alt="Foto Paciente" onerror="this.src='../../recursos/imagenes/iconos/usuario2.png';">
                </div>
                <div class="col-sm-10">
                  <h2 style="margin: 5px 0; font-weight: 800;"><?php echo $nombre_menor; ?> <?php echo $apellido_menor; ?></h2>
                  <p style="font-size: 18px; opacity: 0.9;"><i class="fa fa-id-card-o"></i> Documento: <?php echo $tipo_cedula_menor; ?>-<?php echo $documento_menor; ?></p>
                  <div class="row" style="margin-top: 15px;">
                    <div class="col-xs-4">
                      <span class="label-custom label-light">F. Nacimiento</span>
                      <span class="val-custom val-light"><?php echo $fecha_nac_display; ?></span>
                    </div>
                    <div class="col-xs-4">
                      <span class="label-custom label-light">Sexo</span>
                      <span class="val-custom val-light"><?php echo $genero_menor; ?></span>
                    </div>
                    <div class="col-xs-4">
                      <span class="label-custom label-light">Grupo Sanguíneo</span>
                      <span class="val-custom val-light"><?php echo $grupo_sanguineo; ?></span>
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
                <li data-tab-name="salud_otros"><a href="#salud_otros" data-toggle="tab"><i class="fa fa-file-text-o"></i> Historial Médico</a></li>
              </ul>

              <div class="tab-content tab-content-container">

                <div class="tab-pane active" id="info">
                  <div class="row">
                    <div class="col-md-5">
                      <h4 class="section-title"><i class="fa fa-users"></i> Info del Representante</h4>

                      <div class="data-item-card" style="border-left-color: #0d9488;">
                        <span class="label-custom label-dark">Cédula / Identidad</span>
                        <span class="val-custom val-dark"><?php echo $tipo_cedula_representante; ?>-<?php echo $cedula_representante; ?></span>
                      </div>
                      <div class="data-item-card" style="border-left-color: #0d9488;">
                        <span class="label-custom label-dark">Nombre y Apellido</span>
                        <span class="val-custom val-dark"><?php echo $nombre_representante; ?> <?php echo $apellido_representante; ?></span>
                      </div>
                      <div class="data-item-card" style="border-left-color: #0d9488;">
                        <div class="row">
                          <div class="col-sm-6">
                            <span class="label-custom label-dark">Parentesco</span>
                            <span class="val-custom val-dark"><?php echo $relacion_menor; ?></span>
                          </div>
                          <div class="col-sm-6">
                            <span class="label-custom label-dark">N. Teléfono</span>
                            <span class="val-custom val-dark"><?php echo $id_prefijo_representante; ?>-<?php echo $telefono_representante; ?></span>
                          </div>
                        </div>
                      </div>

                      <h4 class="section-title" style="margin-top: 30px;"><i class="fa fa-map-marker"></i> Lugar de Nacimiento</h4>
                      <div class="data-item-card" style="border-left-color: #95a5a6;">
                        <p style="margin: 0; color: var(--primary-dark); font-weight: 500;">
                          <?php echo $id_pais_nac; ?>, <?php echo $id_estado_nac; ?>, <?php echo $id_municipio_nac; ?>
                        </p>
                      </div>
                    </div>

                    <div class="col-md-7">
                      <h4 class="section-title"><i class="fa fa-address-card-o"></i> Datos Sociales y Residencia</h4>

                      <div class="row">
                        <div class="col-sm-6">
                          <div class="data-item-card">
                            <span class="label-custom label-dark">Etnia</span>
                            <span class="val-custom val-dark">
                              <span class="badge bg-<?php echo ($etnia_menor == 'Si' ? 'green' : 'red'); ?> badge-estado"><?php echo !empty(trim($etnia_menor)) ? $etnia_menor : 'No'; ?></span>
                              <?php if ($etnia_menor == 'Si') echo '(' . $etnia_tipo_menor . ')'; ?>
                            </span>
                          </div>
                        </div>
                        <div class="col-sm-6">
                          <div class="data-item-card">
                            <span class="label-custom label-dark">¿Analfabeta?</span>
                            <span class="val-custom val-dark"><span class="badge bg-<?php echo ($analfabeta_menor == 'Si' ? 'green' : 'red'); ?> badge-estado"><?php echo !empty(trim($analfabeta_menor)) ? $analfabeta_menor : 'No'; ?></span></span>
                          </div>
                        </div>
                      </div>

                      <div class="data-item-card" style="border-left-color: #27ae60; margin-top: 10px;">
                        <span class="label-custom label-dark">Dirección de Residencia</span>
                        <div class="row" style="margin-top: 10px;">
                          <div class="col-sm-6">
                            <p style="margin-bottom: 5px;"><strong>País:</strong> <?php echo $id_pais_dir; ?></p>
                            <p style="margin-bottom: 5px;"><strong>Estado:</strong> <?php echo $id_estado_dir; ?></p>
                            <p style="margin-bottom: 5px;"><strong>Municipio:</strong> <?php echo $id_municipio_dir; ?></p>
                            <p style="margin-bottom: 5px;"><strong>Sector:</strong> <?php echo $id_sector_dir; ?></p>
                          </div>
                          <div class="col-sm-6">
                            <p style="margin-bottom: 5px;"><strong>Av/Calle:</strong> <?php echo $avenida_calle; ?></p>
                            <p style="margin-bottom: 5px;"><strong>Ref:</strong> <?php echo $referencia; ?></p>
                            <p style="margin-bottom: 5px;"><strong>T. Resid.:</strong> <?php echo $residencia_str; ?></p>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  <div class="btn-nav-container">
                    <button type="button" class="btn btn-default" data-toggle="modal" data-target="#modalConfirmarRegreso"><i class="fa fa-arrow-left"></i> Regresar al Menú</button>
                    <button type="button" class="btn btn-primary next-tab" data-tab-actual="#info" data-tab-siguiente="ocupacion_estudios">Siguiente <i class="fa fa-arrow-right"></i></button>
                  </div>
                </div>

                <div class="tab-pane" id="ocupacion_estudios">
                  <div class="row">
                    <div class="col-md-5">
                      <h4 class="section-title"><i class="fa fa-briefcase"></i> Ámbito Académico</h4>
                      <div class="data-item-card">
                        <span class="label-custom label-dark">Nivel de Instrucción</span>
                        <span class="val-custom val-dark">
                          <?php
                          $instruccion = trim($instruccion_menor);
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
                            <span class="val-custom val-dark"><span class="badge bg-<?php echo ($mision_menor ? 'green' : 'red'); ?> badge-estado"><?php echo (!empty(trim($mision_menor)) ? 'Si' : 'No'); ?></span></span>
                          </div>
                          <div class="col-sm-4">
                            <span class="label-custom label-dark">Nombre de Misión</span>
                            <span class="val-custom val-dark"><?php echo !empty(trim($mision_menor)) ? $mision_menor : 'N/A'; ?></span>
                          </div>
                          <div class="col-sm-4">
                            <span class="label-custom label-dark">Años Aprobados</span>
                            <span class="val-custom val-dark"><?php echo !empty(trim($años_aprobados_menor)) ? $años_aprobados_menor : '0'; ?></span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="btn-nav-container">
                    <button type="button" class="btn btn-default prev-tab" data-tab-anterior="info"><i class="fa fa-arrow-left"></i> Atrás</button>
                    <button type="button" class="btn btn-primary next-tab" data-tab-actual="#ocupacion_estudios" data-tab-siguiente="salud">Siguiente <i class="fa fa-arrow-right"></i></button>
                  </div>
                </div>

                <div class="tab-pane" id="salud">
                  <div class="row">
                    <div class="col-md-5">
                      <h4 class="section-title"><i class="fa fa-exclamation-triangle"></i> Alertas de Salud</h4>

                      <div class="data-item-card" style="border-left-color: #e67e22; background-color: #fdf2e9;">
                        <span class="label-custom" style="color: #d35400;">Alergias Conocidas</span>
                        <div style="margin-top: 8px;">
                          <?php
                          $alergias = explode('||', $alergias_data);
                          if (!empty($alergias_data)) {
                            foreach ($alergias as $alergia) {
                              $nombre_alergia = htmlspecialchars(trim($alergia));
                              if ($nombre_alergia) echo '<span class="badge-alergia">' . $nombre_alergia . '</span>';
                            }
                          } else {
                            echo '<span class="val-custom" style="color: #e67e22;">Ninguna registrada</span>';
                          }
                          ?>
                        </div>
                      </div>

                      <div class="data-item-card" style="border-left-color: #e74c3c; background-color: #fdedec;">
                        <span class="label-custom" style="color: #c0392b;">Patologías Crónicas</span>
                        <div style="margin-top: 8px;">
                          <?php
                          $patologias = explode('||', $patologias_data);
                          if (!empty($patologias_data)) {
                            foreach ($patologias as $patologia) {
                              $nombre_patologia = htmlspecialchars(trim($patologia));
                              if ($nombre_patologia) echo '<span class="badge-patologia">' . $nombre_patologia . '</span>';
                            }
                          } else {
                            echo '<span class="val-custom" style="color: #e74c3c;">Ninguna registrada</span>';
                          }
                          ?>
                        </div>
                      </div>

                      <div class="data-item-card">
                        <span class="label-custom label-dark">Discapacidad</span>
                        <div style="margin-top: 8px;">
                          <span class="val-custom val-dark">
                            <span class="badge bg-<?php echo ($discapacidad == 'Si' ? 'green' : 'red'); ?> badge-estado"><?php echo !empty(trim($discapacidad)) ? $discapacidad : 'No'; ?></span>
                            <?php if ($discapacidad == 'Si' && !empty(trim($discapacidad_tipo_menor))) echo ' - ' . $discapacidad_tipo_menor; ?>
                          </span>
                        </div>
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
                    <button type="button" class="btn btn-default prev-tab" data-tab-actual="#salud" data-tab-anterior="ocupacion_estudios"><i class="fa fa-arrow-left"></i> Atrás</button>
                    <button type="button" class="btn btn-primary next-tab" data-tab-actual="#salud" data-tab-siguiente="salud_otros">Siguiente <i class="fa fa-arrow-right"></i></button>
                  </div>
                </div>

                <div class="tab-pane" id="salud_otros">
                  <h4 class="section-title"><i class="fa fa-file-medical-alt"></i> Antecedentes Médicos Detallados</h4>
                  <div id="contenedor-antecedentes">
                    <div class="alert alert-info" style="border-radius: 0px;"><i class="fa fa-spinner fa-spin"></i> Cargando antecedentes médicos...</div>
                  </div>

                  <div class="btn-nav-container">
                    <button class="btn btn-default prev-tab" data-tab-actual="#salud_otros" data-tab-anterior="salud"><i class="fa fa-arrow-left"></i> Atrás</button>
                  </div>
                </div>

              </div>
            </div>

          </div>
        </div>
      </div>
    </section>
  </div>

  <div class="modal fade" id="modalConfirmarRegreso" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarRegresoLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: #dc3545; color: white;">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true" style="color:white;">&times;</span></button>
          <h4 class="modal-title" id="modalConfirmarRegresoLabel"><i class="fa fa-warning"></i> Confirmación</h4>
        </div>
        <div class="modal-body">
          <p>¿Está a punto de cerrar la ficha del paciente y regresar al listado principal. ¿Desea continuar?</p>
        </div>
        <div class="modal-footer" style="background-color: #f8f9fa;">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
          <a href="pacientes_menores_listado.php" class="btn btn-danger">Regresar al inicio</a>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalTodasConsultas" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: var(--medical-blue); color:white;">
          <button type="button" class="close" data-dismiss="modal" style="color:white;">&times;</button>
          <h4 class="modal-title"><i class="fa fa-history"></i> Historial Completo de Consultas</h4>
        </div>
        <div class="modal-body" style="max-height: 500px; overflow-y: auto; background-color: var(--bg-gray); padding: 25px;">
          <div id="contenedor-modal-consultas" class="timeline"></div>
        </div>
        <div class="modal-footer" style="background-color: #ffffff;">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalEditarAntecedentes" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form id="formEditarAntecedentes" action="../../cfg/editar_antecedentes.php">
          <div class="modal-header" style="background-color:#f39c12; color:white;">
            <button type="button" class="close" data-dismiss="modal" style="color:white;">&times;</button>
            <h4 class="modal-title"><i class="fa fa-edit"></i> Editar Antecedentes Médicos</h4>
          </div>
          <div class="modal-body" style="padding: 25px;">
            <input type="hidden" name="cedula" value="<?php echo $documento_menor; ?>">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label style="color: var(--primary-dark); font-weight: 600;">Antecedentes Perinatales</label>
                  <textarea name="perinatales" id="edit_perinatales" class="form-control" rows="4" style="border-radius: 0px;"></textarea>
                </div>
                <div class="form-group">
                  <label style="color: var(--primary-dark); font-weight: 600;">Antecedentes Familiares</label>
                  <textarea name="familiares" id="edit_familiares" class="form-control" rows="4" style="border-radius: 0px;"></textarea>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label style="color: var(--primary-dark); font-weight: 600;">Sexualidad y Reproductivos</label>
                  <textarea name="sexualidad" id="edit_sexualidad" class="form-control" rows="4" style="border-radius: 0px;"></textarea>
                </div>
                <div class="form-group">
                  <label style="color: var(--primary-dark); font-weight: 600;">Estilo de Vida / Observaciones</label>
                  <textarea name="estilo_vida" id="edit_estilo" class="form-control" rows="4" style="border-radius: 0px;"></textarea>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer" style="background-color: #f8f9fa;">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Guardar Cambios</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <?php
  include('includes/footer.php');
  ?>

</body>
<script>
  // Navegación de pestañas
  $(document).ready(function() {
    if (!window.location.hash || window.location.hash === '#info') {
      $('a[href="#info"]').tab('show');
    }

    if (window.history.replaceState) {
      window.history.replaceState(null, null, window.location.pathname + window.location.search);
    }
  });

  $('.next-tab').on('click', async function() {
    const $btn = $(this);
    const tabSiguienteName = $btn.data('tab-siguiente');
    const nextTabLink = $(`.nav-tabs li[data-tab-name="${tabSiguienteName}"] a`);
    const $siguienteTabLi = $(`.nav-tabs li[data-tab-name="${tabSiguienteName}"]`);

    $('.nav-tabs li').removeClass('active');
    $('.tab-content .tab-pane').removeClass('active');
    $siguienteTabLi.addClass('active');

    nextTabLink.tab('show');
    $('#' + tabSiguienteName).addClass('active');
  });

  $('.prev-tab').on('click', function() {
    const $btn = $(this);
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

  // Lógica AJAX
  function obtenerEdad(fechaString) {
    if (!fechaString) return 0;
    var hoy = new Date();
    var cumpleanos = new Date(fechaString);
    var edad = hoy.getFullYear() - cumpleanos.getFullYear();
    var m = hoy.getMonth() - cumpleanos.getMonth();
    if (m < 0 || (m === 0 && hoy.getDate() < cumpleanos.getDate())) {
      edad--;
    }
    return edad;
  }

  $(document).ready(function() {
    const cedulaPaciente = "<?php echo $documento_menor; ?>";
    window.datosActuales = {};

    if (cedulaPaciente) {
      cargarHistorial(cedulaPaciente);
    }

    function cargarHistorial(cedula) {
      $.ajax({
        url: 'get/get_historial_ajax.php',
        type: 'GET',
        data: {
          cedula: cedula
        },
        dataType: 'json',
        success: function(response) {
          if (response.success) {
            window.datosActuales = response.data.antecedentes || {};
            window.historialCompleto = response.data.historial_consultas || [];

            // Inyección de antecedentes con estilo médico (recto y corporativo)
            $('#contenedor-antecedentes').html(`
                        <?php if (in_array('Editar Antecedentes', $_SESSION["permisos"])) : ?>
                        <div class="row" style="margin-bottom:20px;">
                            <div class="col-xs-12 text-right">
                                <a href="#modalEditarAntecedentes" data-toggle="modal" class="btn btn-warning" style="border-radius: 0px;">
                                <i class="fa fa-pencil-square-o"></i> Editar Antecedentes
                                </a>
                            </div>
                        </div>
                        <?php endif; ?> 
                        <div class="row">
                            <div class="col-md-6">
                                <div class="data-item-card">
                                    <span class="label-custom label-dark"><i class="fa fa-child" style="color:var(--medical-blue);"></i> Perinatales</span>
                                    <p style="margin: 5px 0 0 0; color: var(--primary-dark); font-weight: 500;">${window.datosActuales.perinatales || 'N/A'}</p>
                                </div>
                                <div class="data-item-card">
                                    <span class="label-custom label-dark"><i class="fa fa-users" style="color:var(--medical-blue);"></i> Familiares</span>
                                    <p style="margin: 5px 0 0 0; color: var(--primary-dark); font-weight: 500;">${window.datosActuales.familiares || 'N/A'}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="data-item-card">
                                    <span class="label-custom label-dark"><i class="fa fa-heartbeat" style="color:var(--medical-blue);"></i> Sexualidad / Reproductivos</span>
                                    <p style="margin: 5px 0 0 0; color: var(--primary-dark); font-weight: 500;">${window.datosActuales.sexualidad_reproductivos || 'N/A'}</p>
                                </div>
                                <div class="data-item-card">
                                    <span class="label-custom label-dark"><i class="fa fa-leaf" style="color:var(--medical-blue);"></i> Estilo de Vida y Notas</span>
                                    <p style="margin: 5px 0 0 0; color: var(--primary-dark); font-weight: 500;">${window.datosActuales.estilo_vida || 'N/A'}</p>
                                </div>
                            </div>
                        </div>
                    `);
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

          // Inyección de Consultas (estilo rígido)
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
                                    <strong>Peso:</strong> ${c.peso || '-'} | 
                                    <strong>Talla:</strong> ${c.talla || '-'} | 
                                    <strong>Sat:</strong> ${c.saturacion || '-'} | 
                                    <strong>TA:</strong> ${c.tension || '-'} | 
                                    <strong>Temp:</strong> ${c.temperatura || '-'}
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
        $div.append(`
            <div class="alert alert-warning text-center" style="border-radius: 0px; background-color: #fff9e6; border: 1px dashed #f39c12; color: #d35400;">
                <i class="fa fa-info-circle fa-lg"></i><br>No se encontraron consultas previas.
            </div>
        `);
      }
    }

    $(document).on('show.bs.modal', '#modalTodasConsultas', function() {
      renderizarListaConsultas(window.historialCompleto, '#contenedor-modal-consultas', false);
    });

    $(document).on('show.bs.modal', '#modalEditarAntecedentes', function() {
      $('#edit_perinatales').val(window.datosActuales.perinatales);
      $('#edit_familiares').val(window.datosActuales.familiares);
      $('#edit_sexualidad').val(window.datosActuales.sexualidad_reproductivos);
      $('#edit_estilo').val(window.datosActuales.estilo_vida);
    });

    const fechaNac = "<?php echo $fecha_nac_orig; ?>";
    const edadPaciente = obtenerEdad(fechaNac);

    if (edadPaciente >= 18) {
      $('#edit_perinatales')
        .prop('disabled', true)
        .attr('placeholder', 'No aplica para mayores de edad')
        .css('background-color', '#f1f5f9');
    } else {
      $('#edit_perinatales')
        .prop('disabled', false)
        .attr('placeholder', '')
        .css('background-color', '#ffffff');
    }

    $('#formEditarAntecedentes').on('submit', function(e) {
      e.preventDefault();

      var periField = $('#edit_perinatales');
      var wasDisabled = periField.prop('disabled');
      periField.prop('disabled', false);

      $.ajax({
        url: '../../cfg/editar_antecedentes.php',
        type: 'POST',
        data: $(this).serialize(),
        success: function(res) {
          try {
            var data = (typeof res === 'object') ? res : JSON.parse(res);
            if (data.success) {
              alert("✅ Actualización exitosa");
              $('#modalEditarAntecedentes').modal('hide');
              location.reload();
            } else {
              alert("⚠️ Error: " + data.error);
            }
          } catch (err) {
            alert("❌ Error de Formato en el servidor.");
          }
        },
        error: function(xhr) {
          alert("🚫 Error de conexión.");
        },
        complete: function() {
          periField.prop('disabled', wasDisabled);
        }
      });
    });
  });
</script>

</html>