<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Pacientes | Informacion</title>
  <?php
  include('includes/headerNav2.php');
  ?>
  <div class="content-wrapper">

    <style>
      /* --- ESTILOS PARA LA VISTA TIPO CURRÍCULUM --- */
      .paciente-profile-header {
        display: flex;
        align-items: center;
        padding: 20px;
        background-color: #d8edf3;
        /* FONDO AZUL CLARO */
        border-bottom: 3px solid #007bff;
        /* Color principal azul para consistencia */
        margin-bottom: 20px;
        border-radius: 5px 5px 0 0;
      }

      .paciente-photo img {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        border: 3px solid #d2d6de;
        margin-right: 20px;
        object-fit: cover;
        filter: none;
        background-color: transparent !important;
      }

      .paciente-main-info h2 {
        margin: 0 0 5px 0;
        font-size: 2.2em;
        color: #333;
        font-weight: 600;
      }

      .cedula-info {
        font-size: 1.1em;
        color: #666;
        margin-bottom: 10px;
      }

      /* Etiquetas de Datos Vitales */
      .vital-tags span {
        display: inline-block;
        background-color: #e3f2fd;
        color: #007bff;
        padding: 4px 8px;
        margin-right: 10px;
        border-radius: 4px;
        font-size: 0.9em;
        font-weight: 600;
      }

      /* Cuerpo del CV (Contenido de 2 columnas) */
      .info-cv-body {
        padding: 0 20px 20px 20px;
      }

      .cv-sidebar {
        border-right: 1px solid #eee;
        padding-right: 25px;
      }

      .cv-section {
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 1px dashed #eee;
      }

      .cv-section h4 {
        color: #2c3e50;
        border-bottom: 2px solid #ecf0f1;
        padding-bottom: 5px;
        margin-bottom: 15px;
        font-size: 1.2em;
      }

      .cv-section p {
        margin-bottom: 5px;
        font-size: 0.95em;
      }

      .cv-main-content {
        padding-left: 25px;
      }

      .alert-section {
        background-color: #fcecec;
        border-left: 4px solid #dd4b39;
        padding: 10px;
        border-radius: 4px;
        margin-bottom: 25px;
      }

      /* Estilos para Timeline (Consultas) */
      .timeline {
        position: relative;
        padding-left: 20px;
        margin-left: 10px;
        border-left: 2px solid #ccc;
      }

      .timeline-item {
        margin-bottom: 20px;
        position: relative;
      }

      .timeline-item::before {
        content: '';
        position: absolute;
        left: -27px;
        top: 5px;
        width: 12px;
        height: 12px;
        background-color: #007bff;
        border-radius: 50%;
        border: 2px solid #fff;
      }

      .timeline-date {
        font-weight: 700;
        font-size: 0.9em;
        color: #555;
      }

      .timeline-detail h4 {
        margin-top: 5px;
      }

      /* Adaptación para pantallas pequeñas (móviles) */
      @media (max-width: 991px) {
        .paciente-profile-header {
          flex-direction: column;
          align-items: flex-start;
        }

        .paciente-photo {
          margin-bottom: 15px;
        }

        .cv-sidebar {
          border-right: none;
          border-bottom: 1px solid #eee;
          padding-right: 15px;
          padding-bottom: 20px;
        }

        .cv-main-content {
          padding-left: 15px;
          padding-top: 20px;
        }
      }
    </style>
    <style>
      /* ---------------------------------------------------------------------- */
      /* ANIMACIONES Y ESTILOS DE MODALES (Copiado de pacientes_agregar.php) */
      /* ---------------------------------------------------------------------- */
      @keyframes pulse-opacity {
        0% {
          opacity: 0;
        }

        100% {
          opacity: 1;
        }
      }

      @keyframes fadeIn {
        from {
          opacity: 0;
          transform: translateY(-50px);
        }

        to {
          opacity: 1;
          transform: translateY(0);
        }
      }

      @keyframes fadeOut {
        from {
          opacity: 1;
          transform: translateY(0);
        }

        to {
          opacity: 0;
          transform: translateY(-50px);
        }
      }

      /* El modal.in y modal.out controlan la animación del modal-dialog */
      .modal.in .modal-dialog {
        animation: fadeIn 0.4s ease-out;
      }

      .modal.out .modal-dialog {
        animation: fadeOut 0.4s ease-in;
      }

      /* El backdrop usa pulse-opacity */
      .modal-open .modal-backdrop {
        opacity: 0.7 !important;
        animation: pulse-opacity 0.3s forwards;
      }

      /* ---------------------------------------------------------------------- */
      /* ESTILOS DE VALIDACIÓN Y LAYOUT (Copiado de pacientes_agregar.php) */
      /* ---------------------------------------------------------------------- */
      /* CAMBIO: Color de error a crimson (rojo fuerte) */


      .modal {
        position: fixed !important;
        z-index: 99999 !important;
      }

      .modal-backdrop {
        z-index: 99998 !important;
        transition: .5s;
      }

      /* La clase 'in' es clave para que Bootstrap sepa que el modal está abierto */
      .modal.in {
        display: block;
      }

      /* MODIFICACIÓN SOLICITADA: Bloquear click en las pestañas */
      /* Esto evita que el usuario pulse las pestañas manualmente */
      .nav-tabs>li>a {
        pointer-events: none;
        cursor: default;
      }

      /* Estilos para pestañas bloqueadas visualmente */
      .nav-tabs li.disabled-tab a {
        color: #b2b2b2 !important;
        Color: gris para indicar que está bloqueada
      }

      /* Estilos de medico_agregar.php (Se mantienen los estilos definidos previamente) */
      @keyframes open {
        from {
          opacity: 1;
          pointer-events: none;
        }

        to {
          opacity: 1;
          pointer-events: unset;
        }
      }

      @keyframes exit {
        from {
          opacity: 0;
          pointer-events: none;
        }

        to {
          opacity: 1;
          pointer-events: unset;
        }
      }

      @keyframes b {
        from {
          opacity: 0;
          pointer-events: none;
          background-color: lightgray;
          color: black;
        }

        to {
          opacity: 1;
          pointer-events: unset;
          background-color: lightgray;
          color: black;
        }
      }

      .btn-second {
        background-color: #00c0ef;
        border-color: #00acd6;
        color: white;
        animation: b;
        animation-duration: 3s;
      }

      .pop {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        display: flex;
        background: #111111bd;
        opacity: 0;
        pointer-events: none;
        animation: open;
        animation-duration: 10s;
      }

      .modal__contain {
        width: 550px;
        height: 220px;
        margin-top: 180px;
        margin-left: 350px;
        background: #FFF;
        animation: exit;
        animation-duration: 1s;
      }

      .modal-bod {
        text-align: center;
      }

      .modal-head {
        color: white;
        background-color: green;
      }

      /* CAMBIO: Estilo para header de aviso de error usando bg-danger color*/
      .modal-header-danger {
        background-color: #dc3545;
        /* Rojo de Bootstrap bg-danger */
        color: white;
      }
    </style>
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="nav-tabs-custom">

            <?php
            include("../../cfg/conexion.php");

            // CONSULTA SQL CORREGIDA Y AMPLIADA
            $sql = "SELECT p.id, p.email, p.nombre, p.apellido, p.tipo_cedula, p.cedula, p.fecha_nacimiento, p.genero, p.email,
                tp.telefono, pt.prefijo, tp.Id_prefijo,
                dp.situacion_conyugal, dp.etnia, dp.tipo_etnia, dp.analfabeta, dp.profesion, dp.ocupacion, dp.nivel_instruccion, dp.mision, dp.años_aprobados, dp.seguro_social, dp.discapacidad, dp.tipo_discapacidad,
                hm.grupo_sanguineo,
                
                -- Agrega las patologías (Nombre y Código CIE-10) usando subquery
                (SELECT GROUP_CONCAT(pc.nombre_patologia, ' Fecha:', hp.fecha_registro SEPARATOR '||')
                 FROM historial_patologias hp
                 JOIN patologias pc ON hp.Id_patologia = pc.Id_patologia
                 WHERE hp.Id_persona = p.id) AS lista_patologias,
                
                -- Agrega las alergias (Solo Nombre) usando subquery
                (SELECT GROUP_CONCAT(ac.nombre_alergia, ' Fecha:', ha.fecha_registro SEPARATOR '||')
                 FROM historial_alergias ha
                 JOIN alergias_conocidas ac ON ha.Id_alergia = ac.Id_alergias_conocidas
                 WHERE ha.Id_persona = p.id) AS lista_alergias,
                 
                /* ------------------------------------- */

                d.avenida_calle, d.referencia, d.tiempo_residencia, d.tiempo,
                
                /* Nombres de Lugar de Nacimiento */
                paisnac.nombre_pais AS nombre_pais_nac, 
                estnac.nombre_estado AS nombre_estado_nac,   
                munnac.nombre_municipio AS nombre_municipio_nac,  
                
                /* Nombres de Residencia */
                dirsec.nombre_sector AS nombre_sector_dir,
                dirmun.nombre_municipio AS nombre_municipio_dir,
                direst.nombre_estado AS nombre_estado_dir

                FROM persona p
                LEFT JOIN telefonos_personas tp ON p.id = tp.Id_persona
                LEFT JOIN prefijos_telefonos pt ON tp.Id_prefijo = pt.Id
                LEFT JOIN historial_medico hm ON p.id = hm.Id_persona 
                -- SE REMUEVE EL JOIN A LA TABLA OBSOLETA DE UNA SOLA ALERGIA
                LEFT JOIN detalle_paciente dp ON p.id = dp.Id_persona 
                
                /* JOINS para Lugar de Nacimiento (LN) */
                LEFT JOIN lugar_nacimiento ln ON p.id = ln.Id_persona
                LEFT JOIN municipio munnac ON ln.Id_municipio = munnac.Id_Municipio
                LEFT JOIN estado estnac ON munnac.Id_Estado = estnac.Id_Estado
                LEFT JOIN pais paisnac ON estnac.Id_Pais = paisnac.Id_Pais

                /* JOINS para Dirección (D) */
                LEFT JOIN direccion d ON p.id = d.Id_persona
                LEFT JOIN sector dirsec ON d.Id_sector = dirsec.Id_Sector
                LEFT JOIN municipio dirmun ON dirsec.Id_Municipio = dirmun.Id_Municipio
                LEFT JOIN estado direst ON dirmun.Id_Estado = direst.Id_Estado
                
                WHERE p.id =" . $_GET['Id'];

            $resultado = $conexion->query($sql);
            $row = $resultado->fetch_assoc();
            ?>

            <input type="hidden" name="Id" value="<?= $row['id']; ?>">

            <div class="paciente-profile-header">
              <div class="paciente-photo">
                <img src="../../recursos/imagenes/iconos/Paciente_icon1.png" alt="Foto Paciente">
              </div>
              <div class="paciente-main-info">
                <h2><?php echo $row['nombre']; ?> <?php echo $row['apellido']; ?></h2>
                <p class="cedula-info">Cédula: <?php echo $row['tipo_cedula']; ?>-<?php echo $row['cedula']; ?></p>
                <div class="vital-tags">
                  <span><i class="fa fa-birthday-cake"></i> F. Nacimiento: <?php echo $row['fecha_nacimiento']; ?></span>
                  <span><i class="fa fa-venus-mars"></i> Sexo: <?php echo $row['genero']; ?></span>
                  <span><i class="fa fa-heartbeat"></i> Grupo Sanguíneo: <?php echo $row['grupo_sanguineo']; ?></span>
                </div>
              </div>
            </div>

            <ul class="nav nav-tabs">
              <li class="active" data-tab-name="info"><a href="#info" data-toggle="tab">Datos Personales</a></li>
              <li data-tab-name="ocupacion_estudios" class="disabled-tab"><a href="#ocupacion_estudios" data-toggle="tab">Ocupacion y Estudios</a></li>
              <li data-tab-name="salud" class="disabled-tab"><a href="#salud" data-toggle="tab">Salud</a></li>
              <li data-tab-name="salud_otros" class="disabled-tab"><a href="#salud_otros" data-toggle="tab">Historial Medico</a></li>
            </ul>

            <div class="tab-content">

              <div class="tab-pane active" id="info">
                <section id="new" style="margin-bottom:1%;">
                  <div class="row info-cv-body">

                    <div class="col-md-4 cv-sidebar">

                      <div class="cv-section alert-section">
                        <h4><i class="fa fa-phone"></i> Información de Contacto</h4>
                        <p><strong>N. Telefono:</strong> <?php echo $row['prefijo']; ?>-<?php echo $row['telefono'] ?: 'Ninguno'; ?></p>
                        <p><strong>Correo Electrónico:</strong> <?php echo $row['email'] ?: 'Ninguno'; ?></p>
                      </div>

                      <div class="cv-section">
                        <h4><i class="fa fa-map-marker"></i> Lugar de Nacimiento</h4>
                        <p><strong>País:</strong> <?php echo $row['nombre_pais_nac'] ?: 'No registrado'; ?></p>
                        <p><strong>Estado:</strong> <?php echo $row['nombre_estado_nac'] ?: 'No registrado'; ?></p>
                        <p><strong>Municipio:</strong> <?php echo $row['nombre_municipio_nac'] ?: 'No registrado'; ?></p>
                      </div>

                    </div>
                    <div class="col-md-8 cv-main-content">

                      <div class="cv-section">
                        <h4><i class="fa fa-user"></i> Datos Personales y Sociales</h4>
                        <div class="row">
                          <div class="col-sm-6">
                            <p><strong>Situación Conyugal:</strong> <?php echo $row['situacion_conyugal']; ?></p>
                            <strong>Etnia:</strong> <span class="badge bg-<?php echo ($row['etnia'] == 'Si' ? 'green' : 'red'); ?>"><?php echo $row['etnia']; ?></span> </p>
                            <p> <strong>Tipo de Etnia:</strong> <?php echo $row['tipo_etnia']; ?> </p>
                          </div>
                          <div class="col-sm-6">
                            <p><strong>¿Analfabeta?:</strong> <span class="badge bg-<?php echo ($row['analfabeta'] == 'Si' ? 'green' : 'red'); ?>"><?php echo $row['analfabeta']; ?></span></p>
                            <p><strong>¿Cotiza S.S?:</strong> <span class="badge bg-<?php echo ($row['seguro_social'] == 'Si' ? 'green' : 'red'); ?>"><?php echo $row['seguro_social']; ?></span></p>
                          </div>
                        </div>
                      </div>

                      <div class="cv-section">
                        <h4><i class="fa fa-home"></i> Residencia</h4>
                        <div class="row">
                          <div class="col-sm-6">
                            <p><strong>Estado:</strong> <?php echo $row['nombre_estado_dir'] ?: 'No registrado'; ?></p>
                            <p><strong>Municipio:</strong> <?php echo $row['nombre_municipio_dir'] ?: 'No registrado'; ?></p>
                            <p><strong>Sector:</strong> <?php echo $row['nombre_sector_dir'] ?: 'No registrado'; ?></p>
                            <p><strong>Tiempo de Residencia:</strong> <?php echo $row['tiempo_residencia']; ?> <?php echo $row['tiempo']; ?></p>
                          </div>
                          <div class="col-sm-6">
                            <p><strong>Punto de Refencia:</strong> <?php echo $row['referencia'] ?: 'Ninguno'; ?></p>
                            <p><strong>Avenida/Calle:</strong> <?php echo $row['avenida_calle'] ?: 'Ninguna'; ?></p>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  <div style="float:right; margin-top:-2%;">
                    <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#modalConfirmarRegreso">Regresar</button>
                    <button type="button" class="btn btn-primary next-tab" data-tab-actual="#info" data-tab-siguiente="ocupacion_estudios">Siguiente</button>
                  </div>
                </section>
              </div>
              <div class="tab-pane" id="ocupacion_estudios">
                <section id="new" style="margin-bottom:4%;">
                  <div class="row info-cv-body">

                    <div class="col-md-4 cv-sidebar">

                      <div class="cv-section alert-section">
                        <h4><i class="fa fa-graduation-cap"></i> Ocupacion y Estudios</h4>
                        <p><strong>Profesion:</strong> <?php echo $row['profesion'] ?: 'Ninguna'; ?></p>
                        <p><strong>Ocupacion:</strong> <?php echo $row['ocupacion'] ?: 'Ninguna'; ?></p>
                        <p><strong>Nivel de Instruccion:</strong> <?php echo $row['nivel_instruccion'] ?: 'Ninguno'; ?></p>
                      </div>

                      <div class="cv-section">
                        <h4><i class="fa fa-users"></i> </h4>
                        <p></p>
                      </div>
                    </div>
                    <div class="col-md-8 cv-main-content">

                      <div class="cv-section">
                        <h4><i class="fa fa-handshake-o"></i> Misión Educativa</h4>
                        <div class="row">
                          <div class="col-sm-6">
                            <p><strong>Participó en Misión:</strong> <span class="badge bg-<?php echo ($row['mision'] ? 'green' : 'red'); ?>"><?php echo ($row['mision'] ? 'Si' : 'No'); ?></span></p>
                            <p><strong>Misión:</strong> <?php echo $row['mision'] ?: 'Ninguna'; ?></p>
                            <p><strong>Años Aprobados:</strong> <?php echo $row['años_aprobados']; ?></p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div style="float:right; margin-top:1%;">
                    <button type="button" class="btn btn-secondary prev-tab" data-tab-anterior="info">Atras</button>
                    <button type="button" class="btn btn-primary next-tab" data-tab-actual="#ocupacion_estudios" data-tab-siguiente="salud">Siguiente</button>
                  </div>
                </section>
              </div>

              <div class="tab-pane" id="salud">
                <section id="new" style="margin-bottom:4%;">
                  <div class="row info-cv-body">

                    <div class="col-md-4 cv-sidebar">

                      <div class="cv-section alert-section">
                        <h4><i class="fa fa-exclamation-triangle"></i> <strong> Alergias Conocidas:</strong></h4>
                        <?php
                        $alergias = explode('||', $row['lista_alergias']);
                        if (!empty($row['lista_alergias'])) {
                          foreach ($alergias as $alergia) {
                            $nombre_alergia = htmlspecialchars(trim($alergia));
                            if ($nombre_alergia) {
                              // Se utiliza bg-red para destacar las alergias
                              echo '<span class="badge bg-red" style="margin-right: 5px; margin-bottom: 5px; display: inline-block; background-color:orange;">' . $nombre_alergia . '</span>';
                            }
                          }
                        } else {
                          echo '<p><strong>Ninguna conocida</strong></p>';
                        }
                        ?>
                      </div>

                      <div class="cv-section">
                        <h4><i class="fa fa-stethoscope"></i><strong> Patologías Crónicas:</strong></h4>
                        <?php
                        $patologias = explode('||', $row['lista_patologias']);
                        if (!empty($row['lista_patologias'])) {
                          foreach ($patologias as $patologia) {
                            $nombre_patologia = htmlspecialchars(trim($patologia));
                            if ($nombre_patologia) {
                              // Se utiliza bg-red para destacar las patologias
                              echo '<span class="badge bg-red" style="margin-right: 5px; margin-bottom: 5px; display: inline-block; background-color:crimson;">' . $nombre_patologia . '</span>';
                            }
                          }
                        } else {
                          echo '<p><strong>Ninguna registrada</strong></p>';
                        }
                        ?>
                      </div>

                      <h4><i class="fa fa-users"></i> </h4>
                      <p><strong>Es Discapacitado:</strong> <span class="badge bg-<?php echo ($row['discapacidad'] == 'Si' ? 'green' : 'red'); ?>"><?php echo $row['discapacidad']; ?></span></p>
                      <p><strong>Tipo de discapacidad:</strong> <?php echo $row['tipo_discapacidad'] ?: 'Ninguna'; ?></p>

                    </div>
                    <div class="col-md-8 cv-main-content">

                      <div class="timeline">
                        <h4><i class="fa fa-calendar-check-o"></i> Consultas Recientes </h4>
                        <div class="timeline-item">
                          <div id="historial-consultas-container">
                            <div class="alert alert-info">
                              Cargando las consultas mas recientes...
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div style="float:right; margin-top:1%;">
                    <button type="button" class="btn btn-secondary prev-tab" data-tab-actual="#salud" data-tab-anterior="ocupacion_estudios">Atras</button>
                    <button type="button" class="btn btn-primary next-tab" data-tab-actual="#salud" data-tab-siguiente="salud_otros">Siguiente</button>
                  </div>
                </section>
              </div>
              <div class="tab-pane" id="salud_otros">
                <section id="new" style="margin-bottom:4%;">
                  <div id="contenedor-antecedentes">
                    <div class="alert alert-info">Cargando antecedentes médicos...</div>
                  </div>
                  <div style="float:right; margin-top:0%;">
                    <button class="btn btn-secondary prev-tab" data-tab-actual="#salud_otros" data-tab-anterior="salud">Atras</button>
                  </div>
                </section>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <div class="modal" id="modalConfirmarRegreso" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarRegresoLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header modal-header-danger">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="modalConfirmarRegresoLabel"><i class="fa fa-sign-out"></i> Confirmacion de Regreso</h4>
        </div>
        <div class="modal-body">
          <p>Esta apunto de regresar al inicio. ¿Desea continuar?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
          <a href="pacientes_listado.php" class="btn btn-danger">Regresar al Inicio</a>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalTodasConsultas" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color:#5bc0de; color:white;">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title"><i class="fa fa-history"></i> Historial Completo de Consultas</h4>
        </div>
        <div class="modal-body" style="max-height: 450px; overflow-y: auto; background-color: #f4f4f4;">
          <div id="contenedor-modal-consultas"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalEditarAntecedentes" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form id="formEditarAntecedentes" action="../../cfg/editar_antecedentes.php">
          <div class="modal-header" style="background-color:#f0ad4e; color:white;">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title"><i class="fa fa-edit"></i> Editar Antecedentes Médicos</h4>
          </div>
          <div class="modal-body">
            <input type="hidden" name="cedula" value="<?php echo $row['cedula']; ?>">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Antecedentes Perinatales</label>
                  <textarea name="perinatales" id="edit_perinatales" name="edit_perinatales" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group">
                  <label>Antecedentes Familiares</label>
                  <textarea name="familiares" id="edit_familiares" name="edit_familiares" class="form-control" rows="3"></textarea>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Sexualidad y Reproductivos</label>
                  <textarea name="sexualidad" id="edit_sexualidad" name="edit_sexualidad" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group">
                  <label>Estilo de Vida / Observaciones</label>
                  <textarea name="estilo_vida" id="edit_estilo" name="edit_estilo" class="form-control" rows="3"></textarea>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success">Guardar Cambios</button>
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
    // Asegura que al cargar la página, se active la primera pestaña si no hay un hash en la URL.
    $(document).ready(function() {
      // 1. Activa la primera pestaña si no hay hash en la URL
      if (!window.location.hash || window.location.hash === '#info') {
        $('a[href="#info"]').tab('show');
      }

      // 2. Limpia el hash de la URL después de la carga para evitar que el navegador recuerde la última pestaña.
      if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.pathname + window.location.search);
      }
    });

    $('.next-tab').on('click', async function() {
      const $btn = $(this);
      const tabActualSelector = $btn.data('tab-actual');
      const tabSiguienteName = $btn.data('tab-siguiente');
      const nextTabLink = $(`.nav-tabs li[data-tab-name="${tabSiguienteName}"] a`);

      const $siguienteTabLi = $(`.nav-tabs li[data-tab-name="${tabSiguienteName}"]`);
      // 1. Quitar la clase disabled-tab y la clase active
      $('.nav-tabs li').removeClass('active');
      $('.tab-content .tab-pane').removeClass('active');
      $siguienteTabLi.removeClass('disabled-tab').addClass('active');

      // 2. Activar la pestaña siguiente
      nextTabLink.tab('show');
      $('#' + tabSiguienteName).addClass('active');

    });

    $('.prev-tab').on('click', function() {
      const $btn = $(this);
      const tabActualSelector = $btn.data('tab-actual');
      const tabAnteriorName = $btn.data('tab-anterior');

      if (tabAnteriorName) {
        const prevTabLink = $(`.nav-tabs li[data-tab-name="${tabAnteriorName}"] a`);
        const $anteriorTabLi = $(`.nav-tabs li[data-tab-name="${tabAnteriorName}"]`);

        // 1. Quitar la clase active de la pestaña actual
        $('.nav-tabs li').removeClass('active');
        $('.tab-content .tab-pane').removeClass('active');

        // 2. Activar la pestaña anterior
        $anteriorTabLi.addClass('active');
        prevTabLink.tab('show');
        $('#' + tabAnteriorName).addClass('active');

        // Opcional: Re-deshabilitar la pestaña a la que se regresa, si se desea.
        // En modo edición, las pestañas deberían quedar desbloqueadas una vez visitadas.
      } else {
        // En la primera pestaña, el botón Regresar dispara el modal de confirmación de abandono
        $('#modalConfirmarRegreso').modal('show');
      }
    });

    // --- Lógica Final de Guardado (Adaptada de pacientes_agregar.php) ---
    $('#confirmarGuardadoFinal').on('click', function() {
      $('#modalGuardarMedico').modal('hide');
      // El action del formulario ya apunta a ../../cfg/editar_paciente.php
      $('#formularioPaciente').submit();
    });
</script>
<script>
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
      window.datosActuales = {}; // Variable global para guardar los datos

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
              window.datosActuales = response.data.antecedentes || {}; // Guardamos para editar luego
              window.historialCompleto = response.data.historial_consultas;
              // Si no hay historial, inicializamos como arreglo vacío
              window.historialCompleto = response.data.historial_consultas || [];

              // 1. Renderizar ANTECEDENTES con botón de EDITAR
              $('#contenedor-antecedentes').html(`
                        <?php if (in_array('Editar Antecedentes', $_SESSION["permisos"])) : ?>
                        <div class="row" style="margin-bottom:15px;">
                            <div class="col-xs-12">
                                <a href="#modalEditarAntecedentes" data-toggle="modal" class="btn btn-warning btn-sm pull-right">
                                <img src="../../recursos/imagenes/iconos/Editar.png" style="width:15px; height:15px;"> Editar Antecedentes
                                </a>
                            </div>
                        </div>
                        <?php endif; ?> 
                        <div class="row info-cv-body">
                            <div class="col-md-6">
                                <h4><i class="fa fa-child"></i> Perinatales</h4>
                                <p class="well well-sm">${window.datosActuales.perinatales || 'N/A'}</p>
                                <h4><i class="fa fa-users"></i> Familiares</h4>
                                <p class="well well-sm">${window.datosActuales.familiares || 'N/A'}</p>
                            </div>
                            <div class="col-md-6">
                                <h4><i class="fa fa-heartbeat"></i> Sexualidad/Reproductivos</h4>
                                <p class="well well-sm">${window.datosActuales.sexualidad_reproductivos || 'N/A'}</p>
                                <h4><i class="fa fa-leaf"></i> Estilo de Vida y Notas</h4>
                                <p class="well well-sm">${window.datosActuales.estilo_vida || 'N/A'}</p>
                            </div>
                        </div>
                    `);
              renderizarListaConsultas(window.historialCompleto.slice(0, 2), '#historial-consultas-container', true);
            } else {
              $('#historial-consultas-container').html('<div class="alert alert-danger">Error al cargar el historial.</div>');
              $('#contenedor-antecedentes').html('<div class="alert alert-danger">Error al cargar los antecedentes.</div>');
            }
          }
        });
      }

      function renderizarListaConsultas(consultas, contenedorId, mostrarLinkMas) {
        const $div = $(contenedorId);
        $div.empty(); // Limpia el "Cargando..." o contenido previo

        // Forzamos que si consultas es null o undefined, sea un arreglo vacío
        const lista = consultas || [];

        if (lista.length > 0) {
          // SI HAY CONSULTAS: Las dibujamos
          lista.forEach(c => {
            const fecha = c.fecha_consulta.split('-').reverse().join('/');
            $div.append(`
                <div class="panel panel-default" style="border-left: 5px solid #00c0ef; margin-bottom: 15px;">
                    <div class="panel-heading" style="background-color: #f9f9f9; padding: 5px 15px;">
                        <small class="text-primary"><b>FECHA: ${fecha}</b></small>
                        <?php if (in_array('Ver Consultas', $_SESSION["permisos"])) : ?>
                        <a href="consulta_info.php?Id=${c.Id_consulta}" class="pull-right small text-muted">Ver consulta</a>
                        <?php endif; ?> 
                        <span class="pull-right small text-muted" style="margin-right:12px;">Médico: ${c.medico_nombre}</span>                      
                    </div>
                    <div class="panel-body">
                        <div>
                          <b>Motivo:</b> ${c.motivo_consulta || 'N/A'} 
                          <p class="pull-right" style="margin-left:12px;"><b>Peso:</b> ${c.peso || 'N/A'}</p>
                          <p class="pull-right" style="margin-left:12px;"><b>Talla:</b> ${c.talla || 'N/A'}</p>
                          <p class="pull-right"><b>Saturacion:</b> ${c.saturacion || 'N/A'}</p>
                          </p>
                        </div>
                        <div>
                          <b>Diagnóstico:</b> ${c.diagnostico || 'N/A'}
                          <p class="pull-right" style="margin-left:12px;"><b>Temperatura:</b> ${c.temperatura || 'N/A'}</p>
                          <p class="pull-right" style="margin-left:12px;"><b>Tension:</b> ${c.tension || 'N/A'}</p>
                          </p>
                        </div>
                        <div>
                          <p class="pull-right" style="margin-left:12px;"><b>Frecuencia Cardiaca:</b> ${c.frecuencia_cardiaca || 'N/A'}</p>
                          <p class="pull-right"><b>Frecuencia Respiratoria:</b> ${c.frecuencia_respiratoria || 'N/A'}</p>
                        </div>
                    </div>
                </div>
            `);
          });

          // Solo mostramos el link de "Ver más" si hay más de 2 en el historial total
          if (mostrarLinkMas && window.historialCompleto && window.historialCompleto.length > 2) {
            $div.append(`
                <div class="text-center">
                    <a href="#modalTodasConsultas" data-toggle="modal" style="font-weight:bold; text-decoration:underline;">
                        Ver historial clínico completo (${window.historialCompleto.length} registros)
                    </a>
                </div>
            `);
          }

        } else {
          // SI NO HAY CONSULTAS: Mostramos el mensaje de "No se encontraron"
          $div.append(`
            <div class="alert alert-warning text-center" style="margin-top: 10px; border: 1px dashed #f39c12;">
                <i class="fa fa-info-circle"></i> No se encontraron consultas previas registradas para este paciente.
            </div>
        `);
        }
      }

      // 3. EVENTOS DE MODALES
      $(document).on('show.bs.modal', '#modalTodasConsultas', function() {
        renderizarListaConsultas(window.historialCompleto, '#contenedor-modal-consultas', false);
      });

      $(document).on('show.bs.modal', '#modalEditarAntecedentes', function() {
        $('#edit_perinatales').val(window.datosActuales.perinatales);
        $('#edit_familiares').val(window.datosActuales.familiares);
        $('#edit_sexualidad').val(window.datosActuales.sexualidad_reproductivos);
        $('#edit_estilo').val(window.datosActuales.estilo_vida);
      });

      // RECUPERAMOS LA FECHA DESDE PHP (Asegúrate que el formato sea YYYY-MM-DD)
      const fechaNac = "<?php echo $row['fecha_nacimiento']; ?>";
      const edadPaciente = obtenerEdad(fechaNac);

      console.log("Edad detectada: " + edadPaciente); // Esto te ayudará a depurar en la consola (F12)

      // APLICAR BLOQUEO
      // En medicina, perinatales suele ser para niños. Si es adulto (>=18), bloqueamos.
      // Si tu lógica es al revés (bloquear si es niño), cambia la condición.
      if (edadPaciente >= 18) {
        $('#edit_perinatales')
          .prop('disabled', true)
          .attr('placeholder', 'No aplica para mayores de edad')
          .css('background-color', '#f4f4f4');

      } else {
        $('#edit_perinatales')
          .prop('disabled', false)
          .attr('placeholder', '')
          .css('background-color', '#ffffff');
        $('#msg-bloqueo').remove();
      }

      // 4. GUARDAR EDICIÓN
      $('#formEditarAntecedentes').on('submit', function(e) {
        e.preventDefault();

        var periField = $('#edit_perinatales');
        var wasDisabled = periField.prop('disabled');
        periField.prop('disabled', false); // Habilitar para capturar dato

        $.ajax({
          url: '../../cfg/editar_antecedentes.php',
          type: 'POST',
          data: $(this).serialize(),
          // No pongas dataType: 'json' todavía para poder ver errores de texto si ocurren
          success: function(res) {
            console.log("Respuesta del servidor:", res);

            try {
              // Si el servidor mandó un error de PHP, esto fallará y saltará al catch
              var data = (typeof res === 'object') ? res : JSON.parse(res);

              if (data.success) {
                alert("✅ Actualización exitosa");
                $('#modalEditarAntecedentes').modal('hide');
                location.reload();
              } else {
                alert("⚠️ Error: " + data.error);
              }
            } catch (err) {
              alert("❌ Error de Formato: El servidor devolvió una respuesta inválida. Revisa la consola (F12).");
              console.error("Respuesta no-JSON recibida:", res);
            }
          },
          error: function(xhr) {
            alert("🚫 Error de conexión (Status " + xhr.status + "). Revisa la ruta del archivo.");
            console.error(xhr.responseText);
          },
          complete: function() {
            periField.prop('disabled', wasDisabled);
          }
        });
      });
    });
</script>
</html>