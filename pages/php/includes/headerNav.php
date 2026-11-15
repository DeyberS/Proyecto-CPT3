<?php
if (session_status() == PHP_SESSION_NONE) {
  ob_start();
  session_start();
}

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
  header("location: index.php");
  exit;
}

function verificarAcceso($permiso_requerido)
{
  if (!isset($_SESSION["permisos"]) || !in_array($permiso_requerido, $_SESSION["permisos"])) {
    header("Location: inicio.php?error=acceso_denegado");
    exit;
  }
}

$es_admin = false;
$es_visitante = false;

if (isset($_SESSION["permisos"])) {
  if (in_array('Gestionar Configuraciones', $_SESSION["permisos"])) {
    $es_admin = true;
  }
  if (in_array('Ver panel de visitante', $_SESSION["permisos"])) {
    $es_visitante = true;
  }
}

$usar_vista_anidada = ($es_admin || $es_visitante);
?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>CPT | Inicio</title>
  <meta name="author" content="Deyber Silva">
  <link rel="stylesheet" href="recursos/bootstrap/css/bootstrap.css">
  <link rel="stylesheet" href="recursos/css/style.css?v=<?php echo filemtime('recursos/css/style.css'); ?>">
  <link rel="icon" type="image/x-ico" href="recursos/imagenes/cpt3.ico">
</head>

<style>
  .sidebar-collapse .ocultar-al-colapsar {
    display: none !important;
  }

  @keyframes glow-recipes-yellow {
    0% {
      background-color: transparent;
    }

    50% {
      background-color: rgba(243, 156, 18, 0.4);
      box-shadow: 0 0 10px rgba(243, 156, 18, 0.6);
    }

    100% {
      background-color: transparent;
    }
  }

  .glow-active {
    animation: glow-recipes-yellow 2s infinite;
    border-radius: 4px;
  }

  .treeview>a>.pull-right-container>.arrow-indicator {
    display: inline-block;
    float: left;
    font-size: 12px;
    color: #b8c7ce;
    transition: transform .2s;
  }

  .treeview.menu-open>a>.pull-right-container>.arrow-indicator {
    transform: rotate(90deg);
  }

  .status-dot {
    display: inline-block;
    width: 8px;
    height: 8px;
    background-color: #00a65a;
    border-radius: 50%;
    margin-right: 5px;
    vertical-align: middle;
  }
  /* ============================================= */
  /* REGLAS EXCLUSIVAS PARA ESCRITORIO (PC)        */
  /* ============================================= */
  @media (min-width: 768px) {
    .main-sidebar {
      position: fixed !important;
      top: 0px !important;
      bottom: 0px !important;
      left: 0 !important;
      height: auto !important;
      max-height: calc(100vh - 60px) !important;
      z-index: 810;
    }

    .sidebar {
      display: flex !important;
      flex-direction: column !important;
      height: 100% !important;
      max-height: 100% !important;
      overflow: hidden !important;
      position: relative !important;
      padding: 0 !important;
    }

    .sidebar-menu {
      flex: 1 1 auto !important;
      overflow-y: auto !important;
      overflow-x: hidden !important;
      margin-bottom: 0 !important;
      padding-bottom: 10px !important;
    }

    .sidebar-footer {
      position: relative !important;
      flex-shrink: 0 !important;
      width: 100% !important;
      padding: 10px 15px !important;
      z-index: 1000;
      background-color: inherit;
      border-top: 1px solid #374850;
    }

    .wrapper {
      display: block !important;
      background-color: #f4f7f9 !important;
    }
  }
  
  /* Estas se quedan globales */
  .sidebar-collapse .ocultar-al-colapsar {
    display: none !important;
  }

  .sidebar-menu::-webkit-scrollbar {
    width: 0px;
    background: transparent;
  }
  .sidebar-menu {
    scrollbar-width: none;
    -ms-overflow-style: none;
  }
  .user-panel {
    flex-shrink: 0 !important;
  }

  .content-wrapper {
    background-color: #f4f7f9 !important;
  }

  .content-custom {
    padding: 50px 10px;
    margin-left: 60px;
  }

  /* Ocultar barras de scroll en general */
  html::-webkit-scrollbar,
  body::-webkit-scrollbar,
  .content-wrapper::-webkit-scrollbar {
    width: 0px;
    background: transparent;
  }
  html, body, .content-wrapper {
    scrollbar-width: none;
    -ms-overflow-style: none;
  }

  .welcome-banner {
    background: linear-gradient(135deg, #0052d4, #4364f7, #6fb1fc);
    color: white;
    padding: 25px 30px;
    border-radius: 12px;
    margin-bottom: 30px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .welcome-banner h2 {
    margin: 0 0 5px 0;
    font-weight: 700;
    font-size: 28px;
  }

  .welcome-banner p {
    margin: 0;
    font-size: 15px;
    opacity: 0.9;
  }

  .modern-card {
    background-color: #1e282c;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 25px;
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    height: 140px;
    justify-content: center;
  }

  .modern-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.25);
  }

  .modern-card .card-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    z-index: 2;
  }

  .modern-card .info {
    color: #ffffff;
    flex: 1;
    padding-right: 15px;
  }

  .modern-card .info h3 {
    font-size: 32px;
    font-weight: 700;
    margin: 0 0 5px 0;
    color: #f8f9fa;
  }

  .modern-card .info p {
    font-size: 13px;
    color: #adb5bd;
    margin: 0;
    line-height: 1.3;
    font-weight: 500;
  }

  .modern-card .icon-box {
    width: 65px;
    height: 65px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: #ffffff;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    flex-shrink: 0;
    z-index: 2;
  }

  .modern-card .card-footer {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    background: rgba(0, 0, 0, 0.2);
    padding: 5px 0;
    text-align: center;
    z-index: 2;
    transition: background 0.3s ease;
  }

  .modern-card .card-footer:hover {
    background: rgba(0, 0, 0, 0.4);
  }

  .modern-card .card-footer a {
    color: #00bcd4;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    display: block;
  }

  .bg-gradient-blue {
    background: linear-gradient(135deg, #36D1DC, #5B86E5);
  }

  .bg-gradient-green {
    background: linear-gradient(135deg, #11998e, #38ef7d);
  }

  .bg-gradient-orange {
    background: linear-gradient(135deg, #FF8008, #FFC837);
  }

  .bg-gradient-red {
    background: linear-gradient(135deg, #ED213A, #93291E);
  }

  .bg-gradient-purple {
    background: linear-gradient(135deg, #8E2DE2, #4A00E0);
  }

  .bg-gradient-teal {
    background: linear-gradient(135deg, #00c6ff, #0072ff);
  }

  .bg-gradient-pink {
    background: linear-gradient(135deg, #ec008c, #fc6767);
  }

  .bg-gradient-gray {
    background: linear-gradient(135deg, #606c88, #3f4c6b);
  }

  .card-list {
    height: auto;
    min-height: 140px;
  }

  .card-list h3 {
    font-size: 22px !important;
    margin-bottom: 10px !important;
  }

  .card-list ul {
    margin: 0;
    padding: 0;
    font-size: 13px;
    color: #ced4da;
  }

  .card-list li {
    margin-bottom: 5px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    padding-bottom: 5px;
  }
</style>

<body class="hold-transition skin-black sidebar-mini">
  <div class="wrapper">
    <div id="full_loader">
      <div id="loader"></div>
    </div>
    <header class="main-header">
      <a href="#" class="logo">
        <span class="logo-mini"><b>CPT</b></span>
        <span class="logo-lg"><b>CPT</b></span>
      </a>
      <nav class="navbar navbar-static-top">
      <img src="recursos/imagenes/iconos/menu.png" data-toggle="offcanvas" role="button" style="width: 45px; height: 45px; cursor: pointer; margin-top: 2px; float: left;">
        <div class="navbar-custom-menu">
          <ul class="nav navbar-nav">
            <li class="dropdown tasks-menu">
            <li class="dropdown notifications-menu">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <img src="recursos/imagenes/iconos/notificaciones.png" class="user-image" height="20px" width="18px" alt="Notificaciones">
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
                <img src="recursos/imagenes/iconos/usuario.png" class="user-image" alt="User Image">
                <span class="user-name-text"><?php if (isset($_SESSION['nombre'])) {
                                          echo '' . $_SESSION['nombre'] . '';
                                        } ?></span>
              </a>
              <ul class="dropdown-menu">
                <li class="user-header">
                  <img src="recursos/imagenes/iconos/usuario.png" class="img-circle" alt="User Image">
                  <p>
                    <span class="user-name-text"><?php if (isset($_SESSION['nombre'])) {
                                              echo '' . $_SESSION['nombre'] . '';
                                            } ?></span>
                    <small>-- En Linea --</small>
                  </p>
                </li>
                <li class="user-footer">
                  <div class="pull-left">
                    <a href="pages/php/cfg_usuario_editar.php?Id=<?php echo $_SESSION['id'] ?>" class="btn btn-second">Editar Mi Usuario</a>
                  </div>
                  <div class="pull-right">
                    <a href="#" class="btn btn-second" data-toggle="modal" data-target="#modalCerrarSesion">Cerrar Sesion</a>
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
            <img src="recursos/imagenes/iconos/usuario.png" class="img-circle" alt="User Image">
          </div>
          <div class="pull-left info">
            <p><span class="user-name-text"><?php if (isset($_SESSION['nombre'])) {
                                          echo '' . $_SESSION['nombre'] . '';
                                        } ?></span></p>
            <a href="#"><span class="status-dot"></span>En Linea</a>
          </div>
        </div>
        <ul class="sidebar-menu">
          <li class="header">MENU DE NAVEGACION</li>

          <li class="treeview">
            <a href="inicio.php">
              <img src="recursos/imagenes/iconos/filled/places/home_alt@2x.png" style="height:25px; width:25px; margin-top:-4px; margin-right:10px; filter:invert(1);">
              <span style="margin-left:-6px;">Inicio</span>
              <?php if ($usar_vista_anidada) : ?>
                <span class="pull-right-container">
                </span>
              <?php endif; ?>
            </a>
          </li>

          <?php if (isset($_SESSION["permisos"])) : ?>

            <?php if ($usar_vista_anidada) : ?>
              <?php /* ============ VISTA ADMIN (ANIDADA) ============ */ ?>

              <?php if (in_array('Gestionar Consultas', $_SESSION["permisos"])) : ?>
                <li class="treeview">
                  <a href="pages/php/consulta_listado.php">
                    <img src="recursos/imagenes/iconos/consultas.png" style="height:22px; width:22px; margin-top:-4px; margin-right:5px;">
                    <span>Consulta</span>
                  </a>
                </li>
              <?php endif; ?>

              <?php if (in_array('Gestionar Citas', $_SESSION["permisos"])) : ?>
                <li class="treeview">
                  <a href="pages/php/citas_medicas_listado.php">
                    <img src="recursos/imagenes/iconos/cita.png" style="height:23px; width:23px; margin-top:-4px; margin-right:5px;">
                    <span>Citas Medicas</span>
                  </a>
                </li>
              <?php endif; ?>

              <?php if (in_array('Gestionar Jornadas', $_SESSION["permisos"])) : ?>
                <li class="treeview">
                  <a href="pages/php/jornadas_listado.php">
                    <img src="recursos/imagenes/iconos/filled/places/rural_post@2x.png" style="height:22px; width:22px; margin-top:-4px; margin-right:5px; filter:invert(1);">
                    <span>Jornadas</span>
                  </a>
                </li>
              <?php endif; ?>

              <?php /* ---------- PROGRAMAS (nuevo) ---------- */ ?>
              <?php if (in_array('Gestionar Jornadas', $_SESSION["permisos"])) : ?>
                <li class="treeview">
                  <a href="pages/php/programa_listado.php">
                    <img src="recursos/imagenes/iconos/filled/objects/book@2x.png" style="height:22px; width:22px; margin-top:-4px; margin-right:5px; filter:invert(1);">
                    <span>Programas</span>
                  </a>
                </li>
              <?php endif; ?>

              <?php if (in_array('Gestionar Vacunas', $_SESSION["permisos"])) : ?>
                <li class="treeview">
                  <a href="#">
                    <img src="recursos/imagenes/iconos/filled/devices/syringe@2x.png" style="height:22px; width:22px; margin-top:-4px; margin-right:5px; filter:invert(1);">
                    <span>Inmunización</span>
                    <span class="pull-right-container">
                      <span class="arrow-indicator">▸</span>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <li><a href="pages/php/vacunacion_listado.php">Tarjetas de Vacunación</a></li>
                    <li><a href="pages/php/vacunacion_cuarto_frio.php">Cuarto Frío</a></li>
                    <li><a href="pages/php/vacunacion_inventario_listado.php">Inventario de Vacunas</a></li>
                    <li><a href="pages/php/vacunacion_lotes_listado.php">Lotes de Vacunas</a></li>
                  </ul>
                </li>
              <?php endif; ?>

              <?php if (in_array('Gestionar Pacientes', $_SESSION["permisos"])) : ?>
                <li class="treeview">
                  <a href="#">
                    <img src="recursos/imagenes/iconos/Paciente_icon.png" style="height:18px; width:18px; margin-top:-4px; margin-right:10px;">
                    <span>Personas</span>
                    <span class="pull-right-container">
                      <span class="arrow-indicator">▸</span>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <?php if (in_array('Ver Pacientes', $_SESSION["permisos"])) : ?>
                      <li><a href="pages/php/pacientes_listado.php">Censo</a></li>
                      <li><a href="pages/php/pacientes_menores_listado.php">Censo - Menores de Edad</a></li>
                    <?php endif; ?>
                    <?php if (in_array('Ver Representantes', $_SESSION["permisos"])) : ?>
                      <li><a href="pages/php/representantes_listado.php">Representantes</a></li>
                    <?php endif; ?>
                  </ul>
                </li>
              <?php endif; ?>

              <?php if (in_array('Gestionar Salud', $_SESSION["permisos"])) : ?>
                <li class="treeview">
                  <a href="#">
                    <img src="recursos/imagenes/iconos/patologias.png" style="height:18px; width:18px; margin-top:-4px; margin-right:10px;">
                    <span>Salud</span>
                    <span class="pull-right-container">
                      <span class="arrow-indicator">▸</span>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <?php if (in_array('Ver Patologias', $_SESSION["permisos"])) : ?>
                      <li><a href="pages/php/salud_patologias_listado.php">Patologías</a></li>
                    <?php endif; ?>
                    <?php if (in_array('Ver Alergias', $_SESSION["permisos"])) : ?>
                      <li><a href="pages/php/salud_alergias_listado.php">Alergias</a></li>
                    <?php endif; ?>
                    <?php if (in_array('Ver Sintomas', $_SESSION["permisos"])) : ?>
                      <li><a href="pages/php/salud_sintomas_listado.php">Síntomas</a></li>
                    <?php endif; ?>
                  </ul>
                </li>
              <?php endif; ?>

              <?php if (in_array('Gestionar Farmacia', $_SESSION["permisos"])) : ?>
                <li class="treeview">
                  <a href="#">
                    <img src="recursos/imagenes/iconos/farmacia_icon.png" style="height:18px; width:18px; margin-top:-4px; margin-right:10px;">
                    <span>Farmacia</span>
                    <span class="pull-right-container">
                      <span class="arrow-indicator">▸</span>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <?php if (in_array('Ver Inventario', $_SESSION["permisos"])) : ?>
                      <li><a href="pages/php/farmacia_inventario_listado.php">Inventario</a></li>
                    <?php endif; ?>
                    <?php if (in_array('Ver kardex de medicamentos', $_SESSION["permisos"])) : ?>
                      <li><a href="pages/php/farmacia_inventario_kardex.php">Kardex</a></li>
                    <?php endif; ?>
                    <?php if (in_array('Ver Medicamentos', $_SESSION["permisos"])) : ?>
                      <li><a href="pages/php/farmacia_medicamentos_listado.php">Medicamentos</a></li>
                    <?php endif; ?>
                    <?php if (in_array('Ver Lotes', $_SESSION["permisos"])) : ?>
                      <li><a href="pages/php/farmacia_lotes_listado.php">Lotes</a></li>
                    <?php endif; ?>
                    <?php if (in_array('Ver Proveedores', $_SESSION["permisos"])) : ?>
                      <li><a href="pages/php/farmacia_proveedores_listado.php">Proveedores</a></li>
                    <?php endif; ?>
                    <?php if (in_array('Ver Laboratorios', $_SESSION["permisos"])) : ?>
                      <li><a href="pages/php/farmacia_laboratorio_listado.php">Laboratorios</a></li>
                    <?php endif; ?>
                    <?php if (in_array('Ver Recetas', $_SESSION["permisos"])) : ?>
                      <li><a href="pages/php/farmacia_prescripciones_listado.php" id="menu-link-recetas">Récipes</a></li>
                    <?php endif; ?>
                    <?php if (in_array('Ver Inventario', $_SESSION["permisos"])) : ?>
                      <li><a href="pages/php/equipos_listado.php">Inventario de Equipos</a></li>
                    <?php endif; ?>
                  </ul>
                </li>
              <?php endif; ?>

              <?php if (in_array('Gestionar RH', $_SESSION["permisos"])) : ?>
                <li class="treeview">
                  <a href="#">
                    <img src="recursos/imagenes/iconos/medico.png" style="height:20px; width:20px; margin-top:-4px; margin-right:10px;">
                    <span>Recursos Humanos</span>
                    <span class="pull-right-container">
                      <span class="arrow-indicator">▸</span>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <?php if (in_array('Ver Medicos', $_SESSION["permisos"])) : ?>
                      <li><a href="pages/php/rh_medico_listado.php">Médicos</a></li>
                    <?php endif; ?>
                    <?php if (in_array('Ver Areas', $_SESSION["permisos"])) : ?>
                      <li><a href="pages/php/rh_areas_listado.php">Áreas</a></li>
                    <?php endif; ?>
                    <?php if (in_array('Ver Especialidades', $_SESSION["permisos"])) : ?>
                      <li><a href="pages/php/rh_especialidades_listado.php">Especialidades</a></li>
                    <?php endif; ?>
                  </ul>
                </li>
              <?php endif; ?>

              <?php if (in_array('Gestionar Configuraciones', $_SESSION["permisos"])) : ?>
                <li class="treeview">
                  <a href="#">
                    <img src="recursos/imagenes/iconos/cfg.png" style="height:18px; width:18px; margin-top:-4px; margin-right:10px;">
                    <span>Configuraciones</span>
                    <span class="pull-right-container">
                      <span class="arrow-indicator">▸</span>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <?php if (in_array('Ver Usuarios', $_SESSION["permisos"])) : ?>
                      <li><a href="pages/php/cfg_usuario_listado.php">Usuarios</a></li>
                    <?php endif; ?>
                    <?php if (in_array('Ver Roles', $_SESSION["permisos"])) : ?>
                      <li><a href="pages/php/cfg_roles_listado.php">Roles</a></li>
                    <?php endif; ?>
                    <?php if (in_array('Ver Permisos', $_SESSION["permisos"])) : ?>
                      <li><a href="pages/php/cfg_permisos_listado.php">Permisos</a></li>
                    <?php endif; ?>
                  </ul>
                </li>
              <?php endif; ?>

            <?php else : ?>
              <?php /* ============ VISTA PLANA (SUPERVISOR, MÉDICO, ETC.) ============ */ ?>

              <?php if (in_array('Gestionar Consultas', $_SESSION["permisos"])) : ?>
                <li class="treeview">
                  <a href="pages/php/consulta_listado.php">
                    <img src="recursos/imagenes/iconos/consultas.png" style="height:22px; width:22px; margin-top:-4px; margin-right:5px;">
                    <span>Consulta</span>
                  </a>
                </li>
              <?php endif; ?>

              <?php if (in_array('Gestionar Citas', $_SESSION["permisos"])) : ?>
                <li class="treeview">
                  <a href="pages/php/citas_medicas_listado.php">
                    <img src="recursos/imagenes/iconos/cita.png" style="height:23px; width:23px; margin-top:-4px; margin-right:5px;">
                    <span>Citas Medicas</span>
                  </a>
                </li>
              <?php endif; ?>

              <?php if (in_array('Gestionar Jornadas', $_SESSION["permisos"])) : ?>
                <li class="treeview">
                  <a href="pages/php/jornadas_listado.php">
                    <img src="recursos/imagenes/iconos/filled/places/rural_post@2x.png" style="height:22px; width:22px; margin-top:-4px; margin-right:5px; filter:invert(1);">
                    <span>Jornadas</span>
                  </a>
                </li>
                <li><a href="pages/php/programa_listado.php">
                    <img src="recursos/imagenes/iconos/filled/objects/book@2x.png" style="height:20px; width:20px; margin-top:-4px; margin-right:10px; filter:invert(1);">
                    <span>Programas</span></a></li>
              <?php endif; ?>

              <?php if (in_array('Gestionar Vacunas', $_SESSION["permisos"])) : ?>
                <li class="header">MÓDULO DE INMUNIZACIÓN</li>
                <li><a href="pages/php/vacunacion_listado.php">
                    <img src="recursos/imagenes/iconos/filled/devices/syringe@2x.png" style="height:20px; width:20px; margin-top:-4px; margin-right:10px; filter:invert(1);">
                    <span>Tarjetas de Vacunación</span></a></li>
                <li><a href="pages/php/vacunacion_cuarto_frio.php">
                    <img src="recursos/imagenes/iconos/filled/devices/syringe@2x.png" style="height:20px; width:20px; margin-top:-4px; margin-right:10px; filter:invert(1);">
                    <span>Cuarto Frío</span></a></li>
                <li><a href="pages/php/vacunacion_inventario_listado.php">
                    <img src="recursos/imagenes/iconos/filled/objects/rdt_result_out_stock@2x.png" style="height:20px; width:20px; margin-top:-4px; margin-right:0px; filter:invert();">
                    <span>Inventario de Vacunas</span></a></li>
                <li><a href="pages/php/vacunacion_lotes_listado.php">
                    <img src="recursos/imagenes/iconos/filled/objects/rdt_result_out_stock@2x.png" style="height:20px; width:20px; margin-top:-4px; margin-right:0px; filter:invert();">
                    <span>Lotes de Vacunas</span></a></li>
              <?php endif; ?>

              <?php if (in_array('Gestionar Pacientes', $_SESSION["permisos"])) : ?>
                <li class="header">MÓDULO DE PERSONAS</li>
                <?php if (in_array('Ver Pacientes', $_SESSION["permisos"])) : ?>
                  <li><a href="pages/php/pacientes_listado.php">
                      <img src="recursos/imagenes/iconos/Paciente_icon.png" style="height:18px; width:18px; margin-top:-4px; margin-right:10px;">
                      <span>Censo General</span></a></li>
                  <li><a href="pages/php/pacientes_menores_listado.php">
                      <img src="recursos/imagenes/iconos/filled/people/child_care@2x.png" style="height:20px; width:20px; margin-top:-4px; margin-right:0px; filter:invert();">
                      <span>Censo Menores</span></a></li>
                <?php endif; ?>
                <?php if (in_array('Ver Representantes', $_SESSION["permisos"])) : ?>
                  <li><a href="pages/php/representantes_listado.php">
                      <img src="recursos/imagenes/iconos/filled/people/i_groups_perspective_crowd@2x.png" style="height:20px; width:20px; margin-top:-4px; margin-right:0px; filter:invert();">
                      <span>Representantes</span></a></li>
                <?php endif; ?>
              <?php endif; ?>

              <?php if (in_array('Gestionar Salud', $_SESSION["permisos"])) : ?>
                <li class="header">MÓDULO DE SALUD</li>
                <?php if (in_array('Ver Patologias', $_SESSION["permisos"])) : ?>
                  <li><a href="pages/php/salud_patologias_listado.php">
                      <img src="recursos/imagenes/iconos/patologias.png" style="height:18px; width:18px; margin-top:-4px; margin-right:10px;">
                      <span>Patologías</span></a></li>
                <?php endif; ?>
                <?php if (in_array('Ver Alergias', $_SESSION["permisos"])) : ?>
                  <li><a href="pages/php/salud_alergias_listado.php">
                      <img src="recursos/imagenes/iconos/filled/ppe/ppe-mask@2x.png" style="height:20px; width:20px; margin-top:-4px; margin-right:0px; filter:invert();">
                      <span>Alergias</span></a></li>
                <?php endif; ?>
                <?php if (in_array('Ver Sintomas', $_SESSION["permisos"])) : ?>
                  <li><a href="pages/php/salud_sintomas_listado.php">
                      <img src="recursos/imagenes/iconos/filled/conditions/chills@2x.png" style="height:20px; width:20px; margin-top:-4px; margin-right:0px; filter:invert();">
                      <span>Síntomas</span></a></li>
                <?php endif; ?>
              <?php endif; ?>

              <?php if (in_array('Gestionar Farmacia', $_SESSION["permisos"])) : ?>
                <li class="header">GESTIÓN DE FARMACIA</li>
                <?php if (in_array('Ver Inventario', $_SESSION["permisos"])) : ?>
                  <li><a href="pages/php/farmacia_inventario_listado.php">
                      <img src="recursos/imagenes/iconos/farmacia_icon.png" style="height:18px; width:18px; margin-top:-4px; margin-right:10px;">
                      <span>Inventario General</span></a></li>
                <?php endif; ?>
                <?php if (in_array('Ver kardex de medicamentos', $_SESSION["permisos"])) : ?>
                  <li><a href="pages/php/farmacia_inventario_kardex.php">
                      <img src="recursos/imagenes/iconos/filled/objects/spreadsheets@2x.png" style="height:18px; width:18px; margin-top:-4px; margin-right:10px; filter:invert();">
                      <span>Kardex</span></a></li>
                <?php endif; ?>
                <?php if (in_array('Ver Medicamentos', $_SESSION["permisos"])) : ?>
                  <li><a href="pages/php/farmacia_medicamentos_listado.php">
                      <img src="recursos/imagenes/iconos/filled/medications/blister_pills_round_x14@2x.png" style="height:18px; width:18px; margin-top:-4px; margin-right:10px; filter:invert();">
                      <span>Medicamentos</span></a></li>
                <?php endif; ?>
                <?php if (in_array('Ver Proveedores', $_SESSION["permisos"])) : ?>
                  <li><a href="pages/php/farmacia_proveedores_listado.php">
                      <img src="recursos/imagenes/iconos/filled/people/city_worker@2x.png" style="height:18px; width:18px; margin-top:-4px; margin-right:10px; filter:invert();">
                      <span>Proveedores</span></a></li>
                <?php endif; ?>
                <?php if (in_array('Ver Laboratorios', $_SESSION["permisos"])) : ?>
                  <li><a href="pages/php/farmacia_laboratorio_listado.php">
                      <img src="recursos/imagenes/iconos/filled/places/rural_post@2x.png" style="height:18px; width:18px; margin-top:-4px; margin-right:10px; filter:invert();">
                      <span>Laboratorios</span></a></li>
                <?php endif; ?>
                <?php if (in_array('Ver Recetas', $_SESSION["permisos"])) : ?>
                  <li><a href="pages/php/farmacia_prescripciones_listado.php" id="menu-link-recetas">
                      <img src="recursos/imagenes/iconos/filled/symbols/i_note_action@2x.png" style="height:20px; width:20px; margin-top:-4px; margin-right:0px; filter:invert();">
                      <span>Récipes (Prescripciones)</span></a></li>
                <?php endif; ?>
                <?php if (in_array('Ver Lotes', $_SESSION["permisos"])) : ?>
                  <li><a href="pages/php/farmacia_lotes_listado.php">
                      <img src="recursos/imagenes/iconos/filled/objects/rdt_result_out_stock@2x.png" style="height:20px; width:20px; margin-top:-4px; margin-right:0px; filter:invert();">
                      <span>Lotes</span></a></li>
                <?php endif; ?>
                <?php if (in_array('Ver Inventario', $_SESSION["permisos"])) : ?>
                  <li><a href="pages/php/equipos_listado.php">
                      <img src="recursos/imagenes/iconos/farmacia_icon.png" style="height:18px; width:18px; margin-top:-4px; margin-right:10px;">
                      <span>Inventario de Equipos</span></a></li>
                <?php endif; ?>
              <?php endif; ?>

              <?php if (in_array('Gestionar RH', $_SESSION["permisos"])) : ?>
                <li class="header">RECURSOS HUMANOS</li>
                <?php if (in_array('Ver Medicos', $_SESSION["permisos"])) : ?>
                  <li><a href="pages/php/rh_medico_listado.php">
                      <img src="recursos/imagenes/iconos/medico.png" style="height:20px; width:20px; margin-top:-4px; margin-right:10px;">
                      <span>Médicos</span></a></li>
                <?php endif; ?>
                <?php if (in_array('Ver Areas', $_SESSION["permisos"])) : ?>
                  <li><a href="pages/php/rh_areas_listado.php">
                      <img src="recursos/imagenes/iconos/filled/places/emergency_post@2x.png" style="height:18px; width:18px; margin-top:-4px; margin-right:0px; filter:invert();">
                      <span>Áreas</span></a></li>
                <?php endif; ?>
                <?php if (in_array('Ver Especialidades', $_SESSION["permisos"])) : ?>
                  <li><a href="pages/php/rh_especialidades_listado.php">
                      <img src="recursos/imagenes/iconos/filled/specialties/coronary_care_unit@2x.png" style="height:18px; width:18px; margin-top:-4px; margin-right:0px; filter:invert();">
                      <span>Especialidades</span></a></li>
                <?php endif; ?>
              <?php endif; ?>

            <?php endif; ?>
          <?php endif; ?>
        </ul>

        <div class="sidebar-footer ocultar-al-colapsar" style="position: absolute; bottom: 0; left: 0; width: 100%; padding: 15px; color: #b8c7ce; border-top: 1px solid #374850; background-color: inherit; z-index: 10;">
          <div style="font-size: 12px; white-space: nowrap; overflow: hidden;">
            <i class="fa fa-calendar" style="margin-right: 5px;"><img src="recursos/imagenes/iconos/calendar-lines-svgrepo-com.svg" style="width: 25px; height: 25px; filter:invert(1);"></i>
            <span id="fecha-actual"></span>
          </div>
          <div style="font-size: 16px; font-weight: bold; margin-top: 5px; white-space: nowrap; overflow: hidden;">
            <i class="fa fa-clock-o" style="margin-right: 5px;"><img src="recursos/imagenes/iconos/clock-two-svgrepo-com.svg" style="width: 25px; height: 25px; filter:invert(1);"></i>
            <span id="reloj-actual"></span>
          </div>
        </div>

      </section>
    </aside>

    <div class="modal fade" id="modalCerrarSesion" tabindex="-1" role="dialog" aria-labelledby="modalCerrarSesionLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header bg-crimson">
            <h4 class="modal-title" id="modalCerrarSesionLabel">Confirmación de Cierre de Sesión</h4>
          </div>
          <div class="modal-body">
            <p>¿Está seguro que desea cerrar su sesión actual?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
            <a href="cfg/login/logout.php" id="confirmarSalida" class="btn btn-danger">Cerrar Sesión</a>
          </div>
        </div>
      </div>
    </div>
 
    <script src="plugins/jQuery/jquery-2.2.3.min.js"></script>
    <script src="recursos/js/app.min.js"></script>
    <script src="recursos/bootstrap/js/bootstrap.min.js"></script>

    <script>
      $(document).ready(function() {
        $('#confirmarSalida').on('click', function(e) {
          e.preventDefault();
          $('#modalCerrarSesion').modal('hide');
          var rutaCerrarSesion = 'cfg/login/logout.php';
          window.location.href = rutaCerrarSesion;
        });

        $('.treeview > a').on('click', function() {
          var $parent = $(this).parent();
          if ($parent.hasClass('treeview-menu')) return;
          if ($parent.find('.treeview-menu').length) {
            $parent.toggleClass('menu-open');
          }
        });
      });
    </script>