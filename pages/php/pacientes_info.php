<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Pacientes | Información</title>
  <?php
  include('includes/headerNav2.php');
  ?>
  <style>
    :root {
        --primary-dark: #2c3e50;
        --medical-blue: #007bff;
        --bg-gray: #f4f7f9;
    }

    body {
        background-color: var(--bg-gray) !important;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .content-wrapper {
        background-color: var(--bg-gray) !important;
    }

    .content-custome{
      padding: 0px, 0px;
      margin-left: 0px;
    }

    .content{
      padding: 0px;
    }

    /* --- CONTENEDOR PRINCIPAL: LIMITADO Y CENTRADO --- */
    .profile-card {
        max-width: 1100px;
        margin: 20px auto; 
        background: #fff;
        border-radius: 0px; /* Bordes rectos tipo rh_medico */
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        border: none;
        min-height: 80vh;
        overflow: hidden;
    }

    /* --- CABECERA ESTILO MÉDICO --- */
    .profile-header {
        background: linear-gradient(135deg, var(--primary-dark) 0%, #34495e 100%);
        color: white;
        padding: 40px;
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

    /* --- ETIQUETAS Y VALORES ESTILO MÉDICO --- */
    .label-custom {
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 700;
        display: block;
        margin-bottom: 2px;
    }

    .label-light { color: #bdc3c7; }
    .label-dark { color: #95a5a6; }

    .val-custom {
        font-size: 16px;
        font-weight: 600;
    }

    .val-light { color: #fff; }
    .val-dark { color: var(--primary-dark); }

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
        box-shadow: 0 2px 5px rgba(0,0,0,0.02);
    }

    /* --- SISTEMA DE PESTAÑAS (TABS) ADAPTADO --- */
    .nav-tabs-custom {
        background: transparent;
        box-shadow: none;
    }

    .nav-tabs {
        border-bottom: 2px solid #eee;
        background: #f8f9fa;
        padding: 0 20px;
    }

    .nav-tabs > li > a {
        border-radius: 0 !important;
        color: #7f8c8d !important;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 13px;
        padding: 15px 20px;
        border: none !important;
        transition: all 0.3s ease;
    }

    .nav-tabs > li > a:hover {
        background-color: #e9ecef;
        color: var(--primary-dark) !important;
    }

    .nav-tabs > li.active > a, 
    .nav-tabs > li.active > a:focus, 
    .nav-tabs > li.active > a:hover {
        color: var(--medical-blue) !important;
        background-color: #fff !important;
        border-bottom: 3px solid var(--medical-blue) !important;
    }

    .tab-content-container {
        padding: 30px;
    }

    /* --- BADGES Y ALERTAS --- */
    .badge-alergia {
        background-color: #e67e22;
        color: white;
        padding: 6px 12px;
        border-radius: 0px; /* Cuadrado */
        font-size: 12px;
        display: inline-block;
        margin-right: 5px;
        margin-bottom: 5px;
        font-weight: 600;
    }

    .badge-patologia {
        background-color: #e74c3c;
        color: white;
        padding: 6px 12px;
        border-radius: 0px; /* Cuadrado */
        font-size: 12px;
        display: inline-block;
        margin-right: 5px;
        margin-bottom: 5px;
        font-weight: 600;
    }

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
    .modal-content { border-radius: 0px !important; } /* Modales cuadrados */
  </style>
</head>

<body class="hold-transition skin-blue sidebar-mini">
  <div class="content-wrapper">
    <section class="content content-custome">
      <div class="row">
        <div class="col-xs-12">
          
          <div class="profile-card">
            
            <?php
            include("../../cfg/conexion.php");

            // CONSULTA SQL (Mantenida idéntica)
            $sql = "SELECT p.id, p.email, p.nombre, p.apellido, p.tipo_cedula, p.cedula, p.fecha_nacimiento, p.genero, p.email,
                tp.telefono, pt.prefijo, tp.Id_prefijo,
                dp.situacion_conyugal, dp.etnia, dp.tipo_etnia, dp.analfabeta, dp.profesion, dp.ocupacion, dp.nivel_instruccion, dp.mision, dp.años_aprobados, dp.seguro_social, dp.discapacidad, dp.tipo_discapacidad,
                hm.grupo_sanguineo,
                
                (SELECT GROUP_CONCAT(CONCAT(pc.nombre_patologia, ' (', DATE_FORMAT(hp.fecha_registro, '%d/%m/%Y'), ')') SEPARATOR '||')
                 FROM historial_patologias hp
                 JOIN patologias pc ON hp.Id_patologia = pc.Id_patologia
                 WHERE hp.Id_persona = p.id) AS lista_patologias,
                
                (SELECT GROUP_CONCAT(CONCAT(ac.nombre_alergia, ' (', DATE_FORMAT(ha.fecha_registro, '%d/%m/%Y'), ')') SEPARATOR '||')
                 FROM historial_alergias ha
                 JOIN alergias_conocidas ac ON ha.Id_alergia = ac.Id_alergias_conocidas
                 WHERE ha.Id_persona = p.id) AS lista_alergias,

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
                
                WHERE p.id =" . $_GET['Id'];

            $resultado = $conexion->query($sql);
            $row = $resultado->fetch_assoc();
            ?>

            <input type="hidden" name="Id" value="<?= $row['id']; ?>">

            <div class="profile-header">
                <div class="row w-100">
                    <div class="col-sm-2 text-center">
                        <img src="../../recursos/imagenes/iconos/Paciente_icon1.png" class="paciente-avatar" alt="Foto Paciente">
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
                                <span class="val-custom val-light"><?php echo !empty(trim($row['grupo_sanguineo'])) ? $row['grupo_sanguineo'] : 'N/A'; ?></span>
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
                            <h4 class="section-title"><i class="fa fa-phone"></i> Contacto y Origen</h4>
                            
                            <div class="data-item-card">
                                <span class="label-custom label-dark">N. Teléfono</span>
                                <span class="val-custom val-dark"><?php echo !empty(trim($row['telefono'])) ? $row['prefijo'].'-'.$row['telefono'] : 'Ninguno'; ?></span>
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
                                            <?php if($row['etnia'] == 'Si') echo '('.$row['tipo_etnia'].')'; ?>
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
                        <button type="button" class="btn btn-default" data-toggle="modal" data-target="#modalConfirmarRegreso"><i class="fa fa-arrow-left"></i> Regresar al Menú</button>
                        <button type="button" class="btn btn-primary next-tab" data-tab-actual="#info" data-tab-siguiente="ocupacion_estudios">Siguiente <i class="fa fa-arrow-right"></i></button>
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
                                $alergias = explode('||', $row['lista_alergias']);
                                if (!empty($row['lista_alergias'])) {
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
                                $patologias = explode('||', $row['lista_patologias']);
                                if (!empty($row['lista_patologias'])) {
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
                                        <span class="badge bg-<?php echo ($row['discapacidad'] == 'Si' ? 'green' : 'red'); ?> badge-estado"><?php echo !empty(trim($row['discapacidad'])) ? $row['discapacidad'] : 'No'; ?></span>
                                        <?php if($row['discapacidad'] == 'Si' && !empty(trim($row['tipo_discapacidad']))) echo ' - '.$row['tipo_discapacidad']; ?>
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
          <a href="pacientes_listado.php" class="btn btn-danger">Regresar al inicio</a>
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
            <input type="hidden" name="cedula" value="<?php echo $row['cedula']; ?>">
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
      $siguienteTabLi.removeClass('disabled-tab').addClass('active');

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
      const cedulaPaciente = "<?php echo $row['cedula']; ?>";
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

      const fechaNac = "<?php echo $row['fecha_nacimiento']; ?>";
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