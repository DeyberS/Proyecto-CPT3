<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Medicos | Informacion</title>
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
        border: 3px solid whitesmoke;
        margin-right: 20px;
        object-fit: cover;
        filter: grayscale();
        background-color: transparent;
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
            $sql = "SELECT p.id, p.nombre, p.apellido, p.tipo_cedula, p.cedula, p.fecha_nacimiento, p.genero, p.email,
                tp.telefono, pt.prefijo, tp.Id_prefijo,
                dm.fecha_ingreso, d.nombre_departamento, e.nombre_especialidad

                FROM persona p
                LEFT JOIN telefonos_personas tp ON p.id = tp.Id_persona
                LEFT JOIN prefijos_telefonos pt ON tp.Id_prefijo = pt.Id
                -- SE REMUEVE EL JOIN A LA TABLA OBSOLETA DE UNA SOLA ALERGIA
                LEFT JOIN detalle_medico dm ON p.id = dm.Id_persona
                LEFT JOIN medicos_departamentos md ON dm.Id_detalle_medico = md.Id_detalle_medico
                LEFT JOIN departamento d ON md.Id_departamento = d.Id_departamento
                LEFT JOIN especialidades_medicos em ON dm.Id_detalle_medico = em.Id_detalle_medico
                LEFT JOIN especialidad e ON em.Id_especialidad = e.Id_especialidad
                
                WHERE p.id =" . $_GET['Id'];

            $resultado = $conexion->query($sql);
            $row = $resultado->fetch_assoc();
            ?>

            <input type="hidden" name="Id" value="<?= $row['id']; ?>">

            <div class="paciente-profile-header">
              <div class="paciente-photo">
                <img src="../../recursos/imagenes/iconos/medicos.png" alt="Foto Paciente">
              </div>
              <div class="paciente-main-info">
                <h2><?php echo $row['nombre']; ?> <?php echo $row['apellido']; ?></h2>
                <p class="cedula-info">Cédula: <?php echo $row['tipo_cedula']; ?>-<?php echo $row['cedula']; ?></p>
                <div class="vital-tags">
                  <span><i class="fa fa-birthday-cake"></i> F. Nacimiento: <?php echo $row['fecha_nacimiento']; ?></span>
                  <span><i class="fa fa-venus-mars"></i> Sexo: <?php echo $row['genero']; ?></span>
                  <span><i class="fa fa-heartbeat"></i> Fecha de Ingreso: <?php echo $row['fecha_ingreso']; ?> </span>
                </div>
              </div>
            </div>

            <ul class="nav nav-tabs">
              <li class="active"><a href="#info" data-toggle="tab">Datos Personales</a></li>
            </ul>

            <div class="tab-content">

              <div class="tab-pane active" id="info">
                <section id="new" style="margin-bottom:1%;">
                  <div class="row info-cv-body">

                    <div class="col-md-4 cv-sidebar">

                      <div class="cv-section alert-section">
                        <h4><i class="fa fa-phone"></i> Información de Contacto</h4>
                        <p><strong>N. Telefono:</strong> <?php echo $row['prefijo']; ?>-<?php echo $row['telefono']; ?></p>
                        <p><strong>Correo Electrónico:</strong> <?php echo $row['email']; ?></p>
                      </div>

                      <div class="cv-section">
                        <h4><i class="fa fa-map-marker"></i> Areas y Especialidades</h4>
                        <p><strong>Departamento/s:</strong> <?php echo $row['nombre_departamento']; ?> </p>
                        
                      </div>
                      <p><strong>Especialidad/es:</strong> <?php echo $row['nombre_especialidad']; ?> </p>
                    </div>
                    <div class="col-md-8 cv-main-content">

                      <div class="cv-section">
                        <h4><i class="fa fa-user"></i> </h4>
                        <div class="row">
                          <div class="col-sm-6">

                          </div>
                          <div class="col-sm-6">
                            
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  <div style="float:right; margin-top:-2%;">
                    <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#modalConfirmarRegreso">Regresar</button>
                    <!--<button type="button" class="btn btn-primary next-tab" data-tab-actual="#info" data-tab-siguiente="ocupacion_estudios">Siguiente</button>-->
                  </div>
                </section>
              </div>
              <div class="tab-pane" id="ocupacion_estudios">
                <section id="new" style="margin-bottom:4%;">
 
                  <div style="float:right; margin-top:1%;">
                    <button type="button" class="btn btn-secondary prev-tab" data-tab-anterior="info">Atras</button>
                    <button type="button" class="btn btn-primary next-tab" data-tab-actual="#ocupacion_estudios" data-tab-siguiente="salud">Siguiente</button>
                  </div>
                </section>
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
          <a href="rh_medico_listado.php" class="btn btn-danger">Regresar al Inicio</a>
        </div>
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
  </script>

</html>