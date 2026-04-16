<?php
//Initialize the session
session_start();
//Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
  header("location: ../../index.php");
  exit;
}

?>
<!-- Tell the browser to be responsive to screen width -->
<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
<meta charset="UTF-8">
<!-- Bootstrap 3.36 -->
<link rel="stylesheet" href="../../recursos/bootstrap/css/bootstrap.css">

<!-- Theme style -->
<link rel="stylesheet" href="../../recursos/css/style.css">
<link rel="icon" type="image/x-ico" href="../../recursos/imagenes/cpt3.ico">
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
        <img src="../../recursos/imagenes/iconos/menu.png" data-toggle="offcanvas" role="button" style="width: 45px; height: 45px;">
        </a>

        <div class="navbar-custom-menu">
          <ul class="nav navbar-nav">
            <li class="dropdown tasks-menu">
            <li class="dropdown notifications-menu">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <img src="../../recursos/imagenes/iconos/notificaciones.png" class="user-image" height="20px" width="18px" alt="Notificaciones">
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
                <img src="../../recursos/imagenes/iconos/usuario.png" class="user-image" alt="User Image">
                <span class="hidden-xs"><?php if (isset($_SESSION['nombre'])) {
                                          echo '' . $_SESSION['nombre'] . '';
                                        } ?></span>
              </a>
              <ul class="dropdown-menu">
                <li class="user-header">
                  <img src="../../recursos/imagenes/iconos/usuario.png" class="img-circle" alt="User Image">
                  <p>
                    <?php if (isset($_SESSION['nombre'])) {
                      echo '' . $_SESSION['nombre'] . '';
                    } ?>
                    <small>-- En Linea --</small>
                  </p>
                </li>
                <li class="user-footer">
                  <div class="pull-right">
                    <a href="" class="btn btn-default btn-flat" data-toggle="modal" data-target="#modalCerrarSesion">Cerrar Sesion</a>
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
            <img src="../../recursos/imagenes/iconos/usuario.png" class="img-circle" alt="User Image">
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
            <a href="../../inicio.php">
              <img src="../../recursos/imagenes/iconos/inicio.png" style="height:18px; width:18; margin-top:-4px; margin-right:10px;">
              <span>Inicio</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-right pull-left"></i>
              </span>
            </a>
          </li>
          <?php if (isset($_SESSION["permisos"])) : ?>
            <?php if (in_array('Gestionar Consultas', $_SESSION["permisos"])) : ?>
              <li class="treeview">
                <a href="consulta_listado.php">
                  <img src="../../recursos/imagenes/iconos/consultas.png" style="height:22px; width:22; margin-top:-4px; margin-right:5px;">
                  <span>Consulta</span>
                  <span class="pull-right-container">
                    <i class="fa fa-angle-right pull-left"></i>
                  </span>
                </a>
              </li>
            <?php endif; ?>
            <?php if (in_array('Gestionar Citas', $_SESSION["permisos"])) : ?>
              <li class="treeview">
                <a href="citas_medicas_listado.php">
                  <img src="../../recursos/imagenes/iconos/cita.png" style="height:23px; width:23; margin-top:-4px; margin-right:5px;">
                  <span>Citas Medicas</span>
                  <span class="pull-right-container">
                    <i class="fa fa-angle-right pull-left"></i>
                  </span>
                </a>
              </li>
            <?php endif; ?>
            <?php if (in_array('Gestionar Pacientes', $_SESSION["permisos"])) : ?>
              <li class="treeview">
                <a href="pacientes_listado.php">
                  <img src="../../recursos/imagenes/iconos/Paciente_icon.png" style="height:18px; width:18; margin-top:-4px; margin-right:10px;">
                  <span>Pacientes</span>
                  <span class="pull-right-container">
                    <i class="fa fa-angle-right pull-left"></i>
                  </span>
                  <ul class="treeview-menu">
                    <li><a href="pacientes_listado.php"><i class="fa fa-book"></i>Censo</a></li>
                    <li><a href="pacientes_menores_listado.php"><i class="fa fa-book"></i>Censo - Menores de Edad</a></li>
                    <?php if (in_array('Ver Representantes', $_SESSION["permisos"])) : ?>
                    <li><a href="representantes_listado.php">Representantes</a></li>
                    <?php endif; ?>
                  </ul>
              </li>
            <?php endif; ?>
            <?php if (in_array('Gestionar Salud', $_SESSION["permisos"])) : ?>
              <li class="treeview">
                <a href="#">
                  <img src="../../recursos/imagenes/iconos/patologias.png" style="height:18px; width:18; margin-top:-4px; margin-right:10px;">
                  <span>Salud</span>
                  <span class="pull-right-container">
                    <i class="fa fa-angle-right pull-left"></i>
                  </span>
                </a>
                <ul class="treeview-menu">
                <?php if (in_array('Ver Patologias', $_SESSION["permisos"])) : ?>
                  <li><a href="salud_patologias_listado.php">Patologias</a></li>
                  <?php endif; ?>
                  <?php if (in_array('Ver Alergias', $_SESSION["permisos"])) : ?>
                  <li><a href="salud_alergias_listado.php">Alergias</a></li>
                  <?php endif; ?>
                  <?php if (in_array('Ver Sintomas', $_SESSION["permisos"])) : ?>
                  <li><a href="salud_sintomas_listado.php">Sintomas</a></li>
                  <?php endif; ?>
                </ul>
              </li>
            <?php endif; ?>
            <?php if (in_array('Gestionar Farmacia', $_SESSION["permisos"])) : ?>
              <li class="treeview">
                <a href="#">
                  <img src="../../recursos/imagenes/iconos/Farmacia_icon.png" style="height:18px; width:18; margin-top:-4px; margin-right:10px;">
                  <span>Farmacia</span>
                  <span class="pull-right-container">
                    <span class="pull-right-container">
                      <i class="fa fa-angle-right pull-left"></i>
                    </span>
                  </span>
                </a>
                <ul class="treeview-menu">
                  <?php if (in_array('Ver Inventario', $_SESSION["permisos"])) : ?>
                    <li><a href="farmacia_inventario_listado.php"><i class="fa fa-book"></i>Inventario</a></li>
                  <?php endif; ?>
                  <?php if (in_array('Ver Inventario', $_SESSION["permisos"])) : ?>
                    <li><a href="farmacia_inventario_kardex.php"><i class="fa fa-book"></i>Kardex</a></li>
                  <?php endif; ?>
                  <?php if (in_array('Ver Medicamentos', $_SESSION["permisos"])) : ?>
                    <li><a href="farmacia_medicamentos_listado.php"><i class="fa fa-user-plus"></i>Medicamentos</a></li>
                  <?php endif; ?>
                  <?php if (in_array('Ver Lotes', $_SESSION["permisos"])) : ?>
                    <li><a href="farmacia_lotes_listado.php"><i class="fa fa-user-plus"></i>Lotes</a></li>
                  <?php endif; ?>
                  <?php if (in_array('Ver Medicamentos', $_SESSION["permisos"])) : ?>
                    <li><a href="farmacia_proveedores_listado.php"><i class="fa fa-user-plus"></i>Proveedores</a></li>
                  <?php endif; ?>
                  <?php if (in_array('Ver Medicamentos', $_SESSION["permisos"])) : ?>
                    <li><a href="farmacia_laboratorio_listado.php"><i class="fa fa-user-plus"></i>Laboratorios</a></li>
                  <?php endif; ?>           
                  <?php if (in_array('Ver Medicamentos', $_SESSION["permisos"])) : ?>
                    <li><a href="farmacia_prescripciones_listado.php"><i class="fa fa-user-plus"></i>Recipes</a></li>
                  <?php endif; ?>
                </ul>
              </li>
            <?php endif; ?>
            <?php if (in_array('Gestionar RH', $_SESSION["permisos"])) : ?>
              <li class="treeview">
                <a href="#">
                  <img src="../../recursos/imagenes/iconos/medico.png" style="height:18px; width:18; margin-top:-4px; margin-right:10px;">
                  <span>Recursos Humanos</span>
                  <span class="pull-right-container">
                    <i class="fa fa-angle-right pull-left"></i>
                  </span>
                </a>
                <ul class="treeview-menu">
                  <?php if (in_array('Ver Medicos', $_SESSION["permisos"])) : ?>
                    <li><a href="rh_medico_listado.php"><i class="fa fa-book"></i>Medicos</a></li>
                  <?php endif; ?>
                  <?php if (in_array('Ver Areas', $_SESSION["permisos"])) : ?>
                    <li><a href="rh_areas_listado.php"><i class="fa fa-user-plus"></i>Areas</a></li>
                  <?php endif; ?>
                  <?php if (in_array('Ver Especialidades', $_SESSION["permisos"])) : ?>
                    <li><a href="rh_especialidades_listado.php"><i class="fa fa-user-plus"></i>Especialidades</a></li>
                  <?php endif; ?>
                </ul>
              </li>
            <?php endif; ?>
            <?php if (in_array('Gestionar Configuraciones', $_SESSION["permisos"])) : ?>
              <li class="treeview">
                <a href="#">
                  <img src="../../recursos/imagenes/iconos/cfg.png" style="height:18px; width:18; margin-top:-4px; margin-right:10px;">
                  <span>Configuraciones</span>
                  <span class="pull-right-container">
                    <i class="fa fa-angle-right pull-left"></i>
                  </span>
                </a>
                <ul class="treeview-menu">
                  <?php if (in_array('Ver Usuarios', $_SESSION["permisos"])) : ?>
                    <li><a href="cfg_usuario_listado.php"><i class="fa fa-user"></i>Usuarios</a></li>
                  <?php endif; ?>
                  <?php if (in_array('Ver Roles', $_SESSION["permisos"])) : ?>
                    <li><a href="cfg_roles_listado.php"><i class="fa fa-user"></i>Roles</a></li>
                  <?php endif; ?>
                  <?php if (in_array('Ver Permisos', $_SESSION["permisos"])) : ?>
                    <li><a href="cfg_permisos_listado.php"><i class="fa fa-user"></i>Permisos</a></li>
                  <?php endif; ?>
                </ul>
              </li>
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
            <a href="../../cfg/lgout.php" id="confirmarSalida" class="btn btn-danger">Cerrar Sesión</a>
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
          var rutaCerrarSesion = '../../cfg/logout.php';

          // 3. Redirigimos al usuario a la ruta de cierre de sesión
          window.location.href = rutaCerrarSesion;
        });
      });
    </script>

    <script src="../../plugins/jQuery/jquery-2.2.3.min.js"></script>
    <script src="../../recursos/js/app.min.js"></script>

    <!-- Bootstrap 3.3.6 -->
    <script src="../../recursos/bootstrap/js/bootstrap.min.js"></script>
    <script src="../../recursos/bootstrap/js/bootstrap.bundle.min.js"></script>