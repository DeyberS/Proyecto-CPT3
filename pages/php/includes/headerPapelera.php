<?php
//Initialize the session
session_start();
//Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
  header("location: ../../../../index.php");
  exit;
}

// Determinar los roles para asignar el tipo de vista
$es_admin = false;
$es_visitante = false;

if (isset($_SESSION["permisos"])) {
    // Es administrador si tiene el permiso de configuraciones
    if (in_array('Gestionar Configuraciones', $_SESSION["permisos"])) {
        $es_admin = true;
    }
    
    // Es visitante (supervisión). 
    if (in_array('Ver panel de visitante', $_SESSION["permisos"])) {
        $es_visitante = true;
    }
}

// Bandera que determina si el usuario usará la vista anidada clásica
$usar_vista_anidada = ($es_admin || $es_visitante);
?>
<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
<link rel="stylesheet" href="../../../recursos/bootstrap/css/bootstrap.css">

<link rel="stylesheet" href="../../../recursos/css/style.css">
<link rel="icon" type="image/x-ico" href="../../../recursos/imagenes/cpt3.ico">
<style>
.sidebar-collapse .ocultar-al-colapsar {
    display: none !important;
  }

  .sidebar-footer {
    position: absolute;
    bottom: 0;
    width: 100%;
    /* Color de fondo del sidebar */
    padding: 15px;
    z-index: 1000;
  }

  @keyframes glow-recipes-yellow {
    0% { background-color: transparent; }
    50% { background-color: rgba(243, 156, 18, 0.4); box-shadow: 0 0 10px rgba(243, 156, 18, 0.6); }
    100% { background-color: transparent; }
  }
  .glow-active {
    animation: glow-recipes-yellow 2s infinite;
    border-radius: 4px;
  }

  .wrapper {
    display: block !important;
    min-height: 100% !important;
    overflow-x: hidden !important;
    background-color: #f4f7f9 !important;
  }

  .content-wrapper {
    background-color: #f4f7f9 !important;
  }

  .content-custom {
    padding: 50px 10px;
    margin-left: 60px;
  }

  /* Añade un margen al final del menú para que el último item no quede tapado por el reloj */
  .sidebar-menu {
    padding-bottom: 80px;
  }
</style>
<body class="hold-transition skin-black sidebar-mini">
  <div id="full_loader">
    <div id="loader"></div>
  </div>
  <div class="wrapper">
    <header class="main-header">
      <a href="#" class="logo">
        <span class="logo-mini"><b>CPT3</b></span>
        <span class="logo-lg"><b>CPT3</b></span>
      </a>
      <nav class="navbar navbar-static-top">
        <img src="../../../recursos/imagenes/iconos/menu.png" data-toggle="offcanvas" role="button" style="width: 45px; height: 45px;">
        </a>

        <div class="navbar-custom-menu">
          <ul class="nav navbar-nav">
            <li class="dropdown tasks-menu">
            <li class="dropdown notifications-menu">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <img src="../../../recursos/imagenes/iconos/notificaciones.png" class="user-image" height="20px" width="18px" alt="Notificaciones">
                <span class="label label-danger" id="contador-notificaciones" style="display: none;">0</span>
              </a>
              <ul class="dropdown-menu">
                <li class="header" style="display: flex; justify-content: space-between; align-items: center;">
                  <span id="titulo-notificaciones">No tienes notificaciones</span>
                </li>
                <li>
                  <ul class="menu" id="lista-notificaciones-dropdown" style="max-height: 300px; overflow-y: auto !important; list-style: none; padding: 0;">
                  </ul>
                </li>
                <li class="footer"><a href="javascript:void(0)" onclick="limpiarNotificaciones()" style="color:blue;">Marcar como leídas</a></li>
              </ul>
            </li>
            </li>
            <li class="dropdown user user-menu">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <img src="../../../recursos/imagenes/iconos/usuario.png" class="user-image" alt="User Image">
                <span class="hidden-xs"><?php if (isset($_SESSION['nombre'])) {
                                          echo '' . $_SESSION['nombre'] . '';
                                        } ?></span>
              </a>
              <ul class="dropdown-menu">
                <li class="user-header">
                  <img src="../../../recursos/imagenes/iconos/usuario.png" class="img-circle" alt="User Image">
                  <p>
                    <span class="hidden-xs"><?php if (isset($_SESSION['nombre'])) {
                                              echo '' . $_SESSION['nombre'] . '';
                                            } ?></span>
                    <small>-- En Linea --</small>
                  </p>
                </li>
                <li class="user-footer">
                  <div class="pull-left">
                    <a href="../cfg_usuario_editar.php?Id=<?php echo $_SESSION['id'] ?>" class="btn btn-default btn-flat">Editar Mi Usuario</a>
                  </div>
                  <div class="pull-right">
                    <a href="#" class="btn btn-default btn-flat" data-toggle="modal" data-target="#modalCerrarSesion">Cerrar Sesion</a>
                  </div>
                </li>
              </ul>
            </li>
          </ul>
        </div>
      </nav>
    </header>
    <aside class="main-sidebar">
      <section class="sidebar">
        <div class="user-panel">
          <div class="pull-left image">
            <img src="../../../recursos/imagenes/iconos/usuario.png" class="img-circle" alt="User Image">
          </div>
          <div class="pull-left info">
            <p><span class="hidden-xs"><?php if (isset($_SESSION['nombre'])) {
                                          echo '' . $_SESSION['nombre'] . '';
                                        } ?></span></p>
            <a href="#"><i class="fa fa-circle text-success"></i>En Linea</a>
          </div>
        </div>
        <ul class="sidebar-menu">
          <li class="header">MENU DE NAVEGACION</li>
          
          <li class="treeview">
            <a href="../../../inicio.php">
              <img src="../../../recursos/imagenes/iconos/inicio.png" style="height:18px; width:18; margin-top:-4px; margin-right:10px;">
              <span>Inicio</span>
              <?php if ($usar_vista_anidada) : ?>
              <span class="pull-right-container">
                <i class="fa fa-angle-right pull-left"></i>
              </span>
              <?php endif; ?>
            </a>
          </li>

          <?php if (isset($_SESSION["permisos"])) : ?>
            
            <?php if ($usar_vista_anidada) : ?>
              <?php if (in_array('Gestionar Consultas', $_SESSION["permisos"])) : ?>
                <li class="treeview">
                  <a href="../consulta_listado.php">
                    <img src="../../../recursos/imagenes/iconos/consultas.png" style="height:22px; width:22; margin-top:-4px; margin-right:5px;">
                    <span>Consulta</span>
                    <span class="pull-right-container">
                      <i class="fa fa-angle-right pull-left"></i>
                    </span>
                  </a>
                </li>
              <?php endif; ?>
              <?php if (in_array('Gestionar Citas', $_SESSION["permisos"])) : ?>
                <li class="treeview">
                  <a href="../citas_medicas_listado.php">
                    <img src="../../../recursos/imagenes/iconos/cita.png" style="height:23px; width:23; margin-top:-4px; margin-right:5px;">
                    <span>Citas Medicas</span>
                    <span class="pull-right-container">
                      <i class="fa fa-angle-right pull-left"></i>
                    </span>
                  </a>
                </li>
              <?php endif; ?>
              <?php if (in_array('Gestionar Pacientes', $_SESSION["permisos"])) : ?>
                <li class="treeview">
                  <a href="../pacientes_listado.php">
                    <img src="../../../recursos/imagenes/iconos/Paciente_icon.png" style="height:18px; width:18; margin-top:-4px; margin-right:10px;">
                    <span>Pacientes</span>
                    <span class="pull-right-container">
                      <i class="fa fa-angle-right pull-left"></i>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <?php if (in_array('Ver Pacientes', $_SESSION["permisos"])) : ?>
                    <li><a href="../pacientes_listado.php"></i>Censo</a></li>
                    <li><a href="../pacientes_menores_listado.php"></i>Censo - Menores de Edad</a></li>
                    <?php endif; ?>
                    <?php if (in_array('Ver Representantes', $_SESSION["permisos"])) : ?>
                    <li><a href="../representantes_listado.php">Representantes</a></li>
                    <?php endif; ?>
                  </ul>
                </li>
              <?php endif; ?>
              <?php if (in_array('Gestionar Salud', $_SESSION["permisos"])) : ?>
                <li class="treeview">
                  <a href="#">
                    <img src="../../../recursos/imagenes/iconos/patologias.png" style="height:18px; width:18; margin-top:-4px; margin-right:10px;">
                    <span>Salud</span>
                    <span class="pull-right-container">
                      <i class="fa fa-angle-right pull-left"></i>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <?php if (in_array('Ver Patologias', $_SESSION["permisos"])) : ?>
                    <li><a href="../salud_patologias_listado.php">Patologias</a></li>
                    <?php endif; ?>
                    <?php if (in_array('Ver Alergias', $_SESSION["permisos"])) : ?>
                      <li><a href="../salud_alergias_listado.php">Alergias</a></li>
                    <?php endif; ?>
                    <?php if (in_array('Ver Sintomas', $_SESSION["permisos"])) : ?>
                      <li><a href="../salud_sintomas_listado.php">Sintomas</a></li>
                    <?php endif; ?>
                  </ul>
                </li>
              <?php endif; ?>
              <?php if (in_array('Gestionar Farmacia', $_SESSION["permisos"])) : ?>
                <li class="treeview">
                  <a href="#">
                    <img src="../../../recursos/imagenes/iconos/farmacia_icon.png" style="height:18px; width:18; margin-top:-4px; margin-right:10px;">
                    <span>Farmacia</span>
                    <span class="pull-right-container">
                      <span class="pull-right-container">
                        <i class="fa fa-angle-right pull-left"></i>
                      </span>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <?php if (in_array('Ver Inventario', $_SESSION["permisos"])) : ?>
                      <li><a href="../farmacia_inventario_listado.php"></i>Inventario</a></li>
                    <?php endif; ?>
                    <?php if (in_array('Ver kardex de medicamentos', $_SESSION["permisos"])) : ?>
                      <li><a href="../farmacia_inventario_kardex.php"><i class="fa fa-user-plus"></i>Kardex</a></li>
                    <?php endif; ?>
                    <?php if (in_array('Ver Medicamentos', $_SESSION["permisos"])) : ?>
                      <li><a href="../farmacia_medicamentos_listado.php"><i class="fa fa-user-plus"></i>Medicamentos</a></li>
                    <?php endif; ?>
                    <?php if (in_array('Ver Lotes', $_SESSION["permisos"])) : ?>
                      <li><a href="../farmacia_lotes_listado.php"><i class="fa fa-user-plus"></i>Lotes</a></li>
                    <?php endif; ?>
                    <?php if (in_array('Ver Proveedores', $_SESSION["permisos"])) : ?>
                      <li><a href="../farmacia_proveedores_listado.php"><i class="fa fa-user-plus"></i>Proveedores</a></li>
                    <?php endif; ?>
                    <?php if (in_array('Ver Laboratorios', $_SESSION["permisos"])) : ?>  
                      <li><a href="../farmacia_laboratorio_listado.php"><i class="fa fa-user-plus"></i>Laboratorios</a></li>
                    <?php endif; ?>    
                    <?php if (in_array('Ver Recetas', $_SESSION["permisos"])) : ?>
                      <li><a href="../farmacia_prescripciones_listado.php" id="menu-link-recetas"><i class="fa fa-user-plus"></i>Récipes</a></li>
                    <?php endif; ?>
                  </ul>
                </li>
              <?php endif; ?>
              <?php if (in_array('Gestionar RH', $_SESSION["permisos"])) : ?>
                <li class="treeview">
                  <a href="#">
                    <img src="../../../recursos/imagenes/iconos/medico.png" style="height:20px; width:20; margin-top:-4px; margin-right:10px;">
                    <span>Recursos Humanos</span>
                    <span class="pull-right-container">
                      <i class="fa fa-angle-right pull-left"></i>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <?php if (in_array('Ver Medicos', $_SESSION["permisos"])) : ?>
                      <li><a href="../rh_medico_listado.php"></i>Medicos</a></li>
                    <?php endif; ?>
                    <?php if (in_array('Ver Areas', $_SESSION["permisos"])) : ?>
                      <li><a href="../rh_areas_listado.php"><i class="fa fa-user-plus"></i>Areas</a></li>
                    <?php endif; ?>
                    <?php if (in_array('Ver Especialidades', $_SESSION["permisos"])) : ?>
                      <li><a href="../rh_especialidades_listado.php"><i class="fa fa-user-plus"></i>Especialidades</a></li>
                    <?php endif; ?>
                  </ul>
                </li>
              <?php endif; ?>
              
              <?php if (in_array('Gestionar Configuraciones', $_SESSION["permisos"])) : ?>
                <li class="treeview">
                  <a href="#">
                    <img src="../../../recursos/imagenes/iconos/cfg.png" style="height:18px; width:18; margin-top:-4px; margin-right:10px;">
                    <span>Configuraciones</span>
                    <span class="pull-right-container">
                      <i class="fa fa-angle-right pull-left"></i>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <?php if (in_array('Ver Usuarios', $_SESSION["permisos"])) : ?>
                      <li><a href="../cfg_usuario_listado.php">Usuarios</a></li>
                    <?php endif; ?>
                    <?php if (in_array('Ver Roles', $_SESSION["permisos"])) : ?>
                      <li><a href="../cfg_roles_listado.php">Roles</a></li>
                    <?php endif; ?>
                    <?php if (in_array('Ver Permisos', $_SESSION["permisos"])) : ?>
                      <li><a href="../cfg_permisos_listado.php">Permisos</a></li>
                    <?php endif; ?>
                  </ul>
                </li>
              <?php endif; ?>

            <?php else : ?>
              <?php if (in_array('Gestionar Consultas', $_SESSION["permisos"])) : ?>
                <li class="treeview">
                  <a href="../consulta_listado.php">
                    <img src="../../../recursos/imagenes/iconos/consultas.png" style="height:22px; width:22; margin-top:-4px; margin-right:5px;">
                    <span>Consulta</span>
                  </a>
                </li>
              <?php endif; ?>

              <?php if (in_array('Gestionar Citas', $_SESSION["permisos"])) : ?>
                <li class="treeview">
                  <a href="../citas_medicas_listado.php">
                    <img src="../../../recursos/imagenes/iconos/cita.png" style="height:23px; width:23; margin-top:-4px; margin-right:5px;">
                    <span>Citas Medicas</span>
                  </a>
                </li>
              <?php endif; ?>

              <?php if (in_array('Gestionar Pacientes', $_SESSION["permisos"])) : ?>
                <li class="header">MÓDULO DE PACIENTES</li>
                <?php if (in_array('Ver Pacientes', $_SESSION["permisos"])) : ?>
                  <li><a href="../pacientes_listado.php"><img src="../../../recursos/imagenes/iconos/Paciente_icon.png" style="height:18px; width:18; margin-top:-4px; margin-right:10px;"> <span>Censo General</span></a></li>
                  <li><a href="../pacientes_menores_listado.php"><i class="fa fa-child" style="margin-right:10px; font-size:18px;"><img src="../../../recursos/imagenes/iconos/filled/people/child_care@2x.png" style="height:20px; width:20; margin-top:-4px; margin-right:0px; filter:invert();"></i> <span>Censo Menores</span></a></li>
                <?php endif; ?>
                <?php if (in_array('Ver Representantes', $_SESSION["permisos"])) : ?>
                  <li><a href="../representantes_listado.php"><i class="fa fa-users" style="margin-right:10px; font-size:18px;"><img src="../../../recursos/imagenes/iconos/filled/people/i_groups_perspective_crowd@2x.png" style="height:20px; width:20; margin-top:-4px; margin-right:0px; filter:invert();"></i> <span>Representantes</span></a></li>
                <?php endif; ?>
              <?php endif; ?>

              <?php if (in_array('Gestionar Salud', $_SESSION["permisos"])) : ?>
                <li class="header">MÓDULO DE SALUD</li>
                <?php if (in_array('Ver Patologias', $_SESSION["permisos"])) : ?>
                  <li><a href="../salud_patologias_listado.php"><img src="../../../recursos/imagenes/iconos/patologias.png" style="height:18px; width:18; margin-top:-4px; margin-right:10px;"> <span>Patologías</span></a></li>
                <?php endif; ?>
                <?php if (in_array('Ver Alergias', $_SESSION["permisos"])) : ?>
                  <li><a href="../salud_alergias_listado.php"><i class="fa fa-exclamation-triangle" style="margin-right:10px; font-size:18px;"><img src="../../../recursos/imagenes/iconos/filled/ppe/ppe-mask@2x.png" style="height:20px; width:20; margin-top:-4px; margin-right:0px; filter:invert();"></i> <span>Alergias</span></a></li>
                <?php endif; ?>
                <?php if (in_array('Ver Sintomas', $_SESSION["permisos"])) : ?>
                  <li><a href="../salud_sintomas_listado.php"><i class="fa fa-heartbeat" style="margin-right:10px; font-size:18px;"><img src="../../../recursos/imagenes/iconos/filled/conditions/chills@2x.png" style="height:20px; width:20; margin-top:-4px; margin-right:0px; filter:invert();"></i> <span>Síntomas</span></a></li>
                <?php endif; ?>
              <?php endif; ?>

              <?php if (in_array('Gestionar Farmacia', $_SESSION["permisos"])) : ?>
                <li class="header">GESTIÓN DE FARMACIA</li>
                <?php if (in_array('Ver Inventario', $_SESSION["permisos"])) : ?>
                  <li><a href="../farmacia_inventario_listado.php"><img src="../../../recursos/imagenes/iconos/farmacia_icon.png" style="height:18px; width:18; margin-top:-4px; margin-right:10px;"> <span>Inventario General</span></a></li>
                <?php endif; ?>
                <?php if (in_array('Ver kardex de medicamentos', $_SESSION["permisos"])) : ?>
                  <li><a href="../farmacia_inventario_kardex.php"><i class="fa fa-list-alt" style="margin-right:10px; font-size:18px;"><img src="../../../recursos/imagenes/iconos/filled/objects/spreadsheets@2x.png" style="height:18px; width:18; margin-top:-4px; margin-right:0px; filter:invert();"></i> <span>Kardex</span></a></li>
                <?php endif; ?>  
                <?php if (in_array('Ver Medicamentos', $_SESSION["permisos"])) : ?>
                  <li><a href="../farmacia_medicamentos_listado.php"><i class="fa fa-medkit" style="margin-right:10px; font-size:18px;"><img src="../../../recursos/imagenes/iconos/filled/medications/blister_pills_round_x14@2x.png" style="height:18px; width:18; margin-top:-4px; margin-right:0px; filter:invert();"></i> <span>Medicamentos</span></a></li>
                <?php endif; ?>  
                <?php if (in_array('Ver Proveedores', $_SESSION["permisos"])) : ?>
                  <li><a href="../farmacia_proveedores_listado.php"><i class="fa fa-truck" style="margin-right:10px; font-size:18px;"><img src="../../../recursos/imagenes/iconos/filled/people/city_worker@2x.png" style="height:18px; width:18; margin-top:-4px; margin-right:0px; filter:invert();"></i> <span>Proveedores</span></a></li>
                <?php endif; ?>   
                <?php if (in_array('Ver Laboratorios', $_SESSION["permisos"])) : ?>  
                  <li><a href="../farmacia_laboratorio_listado.php"><i class="fa fa-flask" style="margin-right:10px; font-size:18px;"><img src="../../../recursos/imagenes/iconos/filled/places/rural_post@2x.png" style="height:18px; width:18; margin-top:-4px; margin-right:0px; filter:invert();"></i> <span>Laboratorios</span></a></li>
                <?php endif; ?>  
                <?php if (in_array('Ver Recetas', $_SESSION["permisos"])) : ?>
                  <li><a href="../farmacia_prescripciones_listado.php" id="menu-link-recetas"><i class="fa fa-file-text-o" style="margin-right:10px; font-size:18px;"><img src="../../../recursos/imagenes/iconos/filled/symbols/i_note_action@2x.png" style="height:20px; width:20; margin-top:-4px; margin-right:0px; filter:invert();"></i> <span>Récipes (Prescripciones)</span></a></li>
                <?php endif; ?>
                <?php if (in_array('Ver Lotes', $_SESSION["permisos"])) : ?>
                  <li><a href="../farmacia_lotes_listado.php"><i class="fa fa-cubes" style="margin-right:10px; font-size:18px;"><img src="../../../recursos/imagenes/iconos/filled/objects/rdt_result_out_stock@2x.png" style="height:20px; width:20; margin-top:-4px; margin-right:0px; filter:invert();"></i> <span>Lotes</span></a></li>
                <?php endif; ?>
              <?php endif; ?>

              <?php if (in_array('Gestionar RH', $_SESSION["permisos"])) : ?>
                <li class="header">RECURSOS HUMANOS</li>
                <?php if (in_array('Ver Medicos', $_SESSION["permisos"])) : ?>
                  <li><a href="../rh_medico_listado.php"><img src="../../../recursos/imagenes/iconos/medico.png" style="height:20px; width:20; margin-top:-4px; margin-right:10px;"> <span>Médicos</span></a></li>
                <?php endif; ?>
                <?php if (in_array('Ver Areas', $_SESSION["permisos"])) : ?>
                  <li><a href="../rh_areas_listado.php"><i class="fa fa-hospital-o" style="margin-right:10px; font-size:18px;"><img src="../../../recursos/imagenes/iconos/filled/places/emergency_post@2x.png" style="height:18px; width:18; margin-top:-4px; margin-right:0px; filter:invert();"></i> <span>Áreas</span></a></li>
                <?php endif; ?>
                <?php if (in_array('Ver Especialidades', $_SESSION["permisos"])) : ?>
                  <li><a href="../rh_especialidades_listado.php"><i class="fa fa-star" style="margin-right:10px; font-size:18px;"><img src="../../../recursos/imagenes/iconos/filled/specialties/coronary_care_unit@2x.png" style="height:18px; width:18; margin-top:-4px; margin-right:0px; filter:invert();"></i> <span>Especialidades</span></a></li>
                <?php endif; ?>
              <?php endif; ?>

            <?php endif; ?> 
          <?php endif; ?>

          <div class="sidebar-footer ocultar-al-colapsar" style="padding: 15px; color: #b8c7ce; border-top: 1px solid #374850; margin-top: 80%;">
            <div style="font-size: 12px; white-space: nowrap; overflow: hidden;">
              <i class="fa fa-calendar" style="margin-right: 5px;"></i>
              <span id="fecha-actual"></span>
            </div>
            <div style="font-size: 16px; font-weight: bold; margin-top: 5px; white-space: nowrap; overflow: hidden;">
              <i class="fa fa-clock-o" style="margin-right: 5px;"></i>
              <span id="reloj-actual"></span>
            </div>
          </div>
      </section>
    </aside>

    <div class="modal fade" id="modalCerrarSesion" tabindex="-1" role="dialog" aria-labelledby="modalCerrarSesionLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header bg-crimson" style="background-color: #dc3545; color: white;">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title" id="modalCerrarSesionLabel">Confirmación de Cierre de Sesión</h4>
          </div>
          <div class="modal-body">
            <p>¿Está seguro que desea cerrar su sesión actual?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
            <a href="../../../cfg/lgout.php" id="confirmarSalida" class="btn btn-danger">Cerrar Sesión</a>
          </div>
        </div>
      </div>
    </div>

    <script>
      $(document).ready(function() {

        // =====================================================================
        // LÓGICA DE CIERRE DE SESIÓN (MODAL)
        // =====================================================================

        // Capturamos el clic del botón 'Cerrar Sesión' dentro del modal de confirmación
        $('#confirmarSalida').on('click', function(e) {
          e.preventDefault();

          // 1. Ocultamos el modal de confirmación
          $('#modalCerrarSesion').modal('hide');

          // 2. Definimos la ruta de cierre de sesión.
          // **AJUSTA ESTA RUTA** si tu archivo de logout está en una ubicación diferente.
          var rutaCerrarSesion = '../../../../cfg/logout.php';

          // 3. Redirigimos al usuario a la ruta de cierre de sesión
          window.location.href = rutaCerrarSesion;
        });
      });
    </script>

    <script src="../../../plugins/jQuery/jquery-2.2.3.min.js"></script>
    <script src="../../../recursos/js/app.min.js"></script>

    <script src="../../../recursos/bootstrap/js/bootstrap.min.js"></script>
    <script src="../../../recursos/bootstrap/js/bootstrap.bundle.min.js"></script>