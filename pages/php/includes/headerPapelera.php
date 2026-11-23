<?php
if (session_status() == PHP_SESSION_NONE) {
  ob_start();
  session_start();
}

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
  header("location: ../../../index.php");
  exit;
}

function verificarAcceso($permiso_requerido)
{
  if (!isset($_SESSION["permisos"]) || !in_array($permiso_requerido, $_SESSION["permisos"])) {
    header("Location: ../../../inicio.php?error=acceso_denegado");
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

if (!isset($conexion)) {
  require_once(__DIR__ . '/../../../cfg/conexion.php'); // ajusta según el header
}

if (!function_exists('getEstadoUsuarioActual')) {
  function getEstadoUsuarioActual($conexion) {
    if (!isset($_SESSION['id'])) {
      return ['clase' => 'error', 'texto' => 'Sin Sesión', 'titulo' => 'No hay sesión'];
    }
    $id = intval($_SESSION['id']);
    $res = mysqli_query($conexion, "SELECT estatus, login_attempts FROM persona WHERE id = $id LIMIT 1");

    if (!$res || mysqli_num_rows($res) === 0) {
      return ['clase' => 'offline', 'texto' => 'No existe', 'titulo' => 'Usuario no encontrado en BD (¿sesión huérfana?)'];
    }
    $row = mysqli_fetch_assoc($res);
    $intentos = (int)$row['login_attempts'];
    $estatus  = (int)$row['estatus'];

    if ($intentos >= 3) {
      return ['clase' => 'blocked', 'texto' => 'Bloqueado', 'titulo' => "Bloqueado por intentos ($intentos)"];
    }
    switch ($estatus) {
      case 1: return ['clase' => 'online',   'texto' => 'En Línea',    'titulo' => 'Sesión activa'];
      case 2: return ['clase' => 'offline',  'texto' => 'Desconectado','titulo' => 'BD dice desconectado (posible sesión zombie)'];
      case 0: return ['clase' => 'disabled', 'texto' => 'Desactivado', 'titulo' => 'Cuenta desactivada'];
      default: return ['clase' => 'error',   'texto' => 'Desconocido', 'titulo' => "Estatus desconocido: $estatus"];
    }
  }
}

$estadoActual = getEstadoUsuarioActual($conexion);
?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="author" content="Deyber Silva">
  <link rel="stylesheet" href="../../../recursos/bootstrap/css/bootstrap.css">
  <link rel="stylesheet" href="../../../recursos/css/style.css?v=<?php echo filemtime('../../../recursos/css/style.css'); ?>">
  <link rel="icon" type="image/x-icon" href="../../../recursos/imagenes/cpt3.ico">
</head>

<body class="hold-transition skin-black sidebar-mini">
  <div id="full_loader">
    <div id="loader"></div>
  </div>
  <div class="wrapper">
    <header class="main-header">
      <a href="#" class="logo">
        <span class="logo-mini"><b>CPT</b></span>
        <span class="logo-lg"><b>CPT</b></span>
      </a>
      <nav class="navbar navbar-static-top">
        <img src="../../../recursos/imagenes/iconos/filled/symbols/ui_menu@2x.png" class="invert-color" data-toggle="offcanvas" role="button" style="width: 45px; height: 45px;">

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
                <img src="../../../recursos/imagenes/iconos/filled/symbols/ui_user_profile@2x.png" class="user-image invert-color" alt="Imagen de Usuario">
                <span class="user-name-text"><?php if (isset($_SESSION['nombre'])) { echo '' . $_SESSION['nombre'] . ''; } ?></span>
              </a>
              <ul class="dropdown-menu">
                <li class="user-header">
                  <img src="../../../recursos/imagenes/iconos/filled/symbols/ui_user_profile@2x.png" class="img-circle invert-color" alt="Imagen de Usuario">
                  <p>
                    <span class="user-name-text"><?php if (isset($_SESSION['nombre'])) { echo '' . $_SESSION['nombre'] . ''; } ?></span>
                  </p>
                </li>
                <li class="user-footer">
                  <div class="pull-left">
                    <a href="../cfg_usuario_editar.php?Id=<?php echo $_SESSION['id'] ?>" class="btn btn-second">Editar Mi Usuario</a>
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
            <img src="../../../recursos/imagenes/iconos/filled/symbols/ui_user_profile@2x.png" class="img-circle invert-color" alt="Imagen de Usuario">
          </div>
          <div class="pull-left info">
            <p><span class="user-name-text"><?php if (isset($_SESSION['nombre'])) { echo '' . $_SESSION['nombre'] . ''; } ?></span></p>
            <a href="#" title="<?php echo htmlspecialchars($estadoActual['titulo']); ?>">
              <span class="status-dot <?php echo $estadoActual['clase']; ?>"></span>
              <?php echo htmlspecialchars($estadoActual['texto']); ?>
            </a>
          </div>
        </div>
        <ul class="sidebar-menu">
          <li class="header">MENU DE NAVEGACION</li>

          <li class="treeview">
            <a href="../../../inicio.php">
              <img src="../../../recursos/imagenes/iconos/filled/places/home_alt@2x.png" class="sidebar-icon">
              <span style="margin-left:-6px;">Inicio</span>
            </a>
          </li>

          <?php if (isset($_SESSION["permisos"])) : ?>

            <?php if ($usar_vista_anidada) : ?>
              <?php /* ============ VISTA ADMIN (ANIDADA) ============ */ ?>

              <?php if (in_array('Gestionar Consultas', $_SESSION["permisos"])) : ?>
                <li class="treeview">
                  <a href="../consulta_listado.php">
                    <img src="../../../recursos/imagenes/iconos/filled/objects/health_worker_form@2x.png" class="sidebar-icon">
                    <span>Consulta</span>
                  </a>
                </li>
              <?php endif; ?>

              <?php if (in_array('Gestionar Citas', $_SESSION["permisos"])) : ?>
                <li class="treeview">
                  <a href="../citas_medicas_listado.php">
                    <img src="../../../recursos/imagenes/iconos/filled/objects/calendar@2x.png" class="sidebar-icon">
                    <span>Citas Medicas</span>
                  </a>
                </li>
              <?php endif; ?>

              <?php if (in_array('Gestionar Jornadas', $_SESSION["permisos"])) : ?>
                <li class="treeview">
                  <a href="../jornadas_listado.php">
                    <img src="../../../recursos/imagenes/iconos/filled/places/rural_post@2x.png" class="sidebar-icon">
                    <span>Jornadas</span>
                  </a>
                </li>
              <?php endif; ?>

              <?php /* ---------- Programa (nuevo) ---------- */ ?>
              <?php if (in_array('Gestionar Jornadas', $_SESSION["permisos"])) : ?>
                <li class="treeview">
                  <a href="../programa_listado.php">
                    <img src="../../../recursos/imagenes/iconos/filled/objects/book@2x.png" class="sidebar-icon">
                    <span>Programa</span>
                  </a>
                </li>
              <?php endif; ?>

              <?php if (in_array('Gestionar Vacunas', $_SESSION["permisos"])) : ?>
                <li class="treeview">
                  <a href="#">
                    <img src="../../../recursos/imagenes/iconos/filled/devices/syringe@2x.png" class="sidebar-icon">
                    <span>Inmunización</span>
                    <span class="pull-right-container">
                      <span class="arrow-indicator">▸</span>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <li><a href="../vacunacion_listado.php">Tarjetas de Vacunación</a></li>
                    <li><a href="../vacunacion_cuarto_frio.php">Cuarto Frío</a></li>
                    <li><a href="../vacunacion_inventario_listado.php">Inventario de Vacunas</a></li>
                    <li><a href="../vacunacion_lotes_listado.php">Lotes de Vacunas</a></li>
                  </ul>
                </li>
              <?php endif; ?>

              <?php if (in_array('Gestionar Pacientes', $_SESSION["permisos"])) : ?>
                <li class="treeview">
                  <a href="#">
                    <img src="../../../recursos/imagenes/iconos/filled/people/people@2x.png" class="sidebar-icon">
                    <span>Personas</span>
                    <span class="pull-right-container">
                      <span class="arrow-indicator">▸</span>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <?php if (in_array('Ver Pacientes', $_SESSION["permisos"])) : ?>
                      <li><a href="../pacientes_listado.php">Censo</a></li>
                      <li><a href="../pacientes_menores_listado.php">Censo - Menores de Edad</a></li>
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
                    <img src="../../../recursos/imagenes/iconos/filled/ppe/ppe_face_mask@2x.png" class="sidebar-icon">
                    <span>Salud</span>
                    <span class="pull-right-container">
                      <span class="arrow-indicator">▸</span>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <?php if (in_array('Ver Patologias', $_SESSION["permisos"])) : ?>
                      <li><a href="../salud_patologias_listado.php">Patologías</a></li>
                    <?php endif; ?>
                    <?php if (in_array('Ver Alergias', $_SESSION["permisos"])) : ?>
                      <li><a href="../salud_alergias_listado.php">Alergias</a></li>
                    <?php endif; ?>
                    <?php if (in_array('Ver Sintomas', $_SESSION["permisos"])) : ?>
                      <li><a href="../salud_sintomas_listado.php">Síntomas</a></li>
                    <?php endif; ?>
                  </ul>
                </li>
              <?php endif; ?>

              <?php if (in_array('Gestionar Farmacia', $_SESSION["permisos"])) : ?>
                <li class="treeview">
                  <a href="#">
                    <img src="../../../recursos/imagenes/iconos/filled/places/rural_post_alt@2x.png" class="sidebar-icon">
                    <span>Farmacia</span>
                    <span class="pull-right-container">
                      <span class="arrow-indicator">▸</span>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <?php if (in_array('Ver Inventario', $_SESSION["permisos"])) : ?>
                      <li><a href="../farmacia_inventario_listado.php">Inventario</a></li>
                    <?php endif; ?>
                    <?php if (in_array('Ver kardex de medicamentos', $_SESSION["permisos"])) : ?>
                      <li><a href="../farmacia_inventario_kardex.php">Kardex</a></li>
                    <?php endif; ?>
                    <?php if (in_array('Ver Medicamentos', $_SESSION["permisos"])) : ?>
                      <li><a href="../farmacia_medicamentos_listado.php">Medicamentos</a></li>
                    <?php endif; ?>
                    <?php if (in_array('Ver Lotes', $_SESSION["permisos"])) : ?>
                      <li><a href="../farmacia_lotes_listado.php">Lotes</a></li>
                    <?php endif; ?>
                    <?php if (in_array('Ver Proveedores', $_SESSION["permisos"])) : ?>
                      <li><a href="../farmacia_proveedores_listado.php">Proveedores</a></li>
                    <?php endif; ?>
                    <?php if (in_array('Ver Laboratorios', $_SESSION["permisos"])) : ?>
                      <li><a href="../farmacia_laboratorio_listado.php">Laboratorios</a></li>
                    <?php endif; ?>
                    <?php if (in_array('Ver Recetas', $_SESSION["permisos"])) : ?>
                      <li><a href="../farmacia_prescripciones_listado.php" id="menu-link-recetas">Récipes</a></li>
                    <?php endif; ?>
                    <?php if (in_array('Ver Inventario', $_SESSION["permisos"])) : ?>
                      <li><a href="../equipos_listado.php">Inventario de Equipos</a></li>
                    <?php endif; ?>
                  </ul>
                </li>
              <?php endif; ?>

              <?php if (in_array('Gestionar RH', $_SESSION["permisos"])) : ?>
                <li class="treeview">
                  <a href="#">
                    <img src="../../../recursos/imagenes/iconos/filled/people/doctor@2x.png" class="sidebar-icon">
                    <span>Recursos Humanos</span>
                    <span class="pull-right-container">
                      <span class="arrow-indicator">▸</span>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <?php if (in_array('Ver Medicos', $_SESSION["permisos"])) : ?>
                      <li><a href="../rh_medico_listado.php">Médicos</a></li>
                    <?php endif; ?>
                    <?php if (in_array('Ver Areas', $_SESSION["permisos"])) : ?>
                      <li><a href="../rh_areas_listado.php">Áreas</a></li>
                    <?php endif; ?>
                    <?php if (in_array('Ver Especialidades', $_SESSION["permisos"])) : ?>
                      <li><a href="../rh_especialidades_listado.php">Especialidades</a></li>
                    <?php endif; ?>
                  </ul>
                </li>
              <?php endif; ?>

              <?php if (in_array('Gestionar Configuraciones', $_SESSION["permisos"])) : ?>
                <li class="treeview">
                  <a href="#">
                    <img src="../../../recursos/imagenes/iconos/filled/symbols/ui_settings@2x.png" class="sidebar-icon">
                    <span>Configuraciones</span>
                    <span class="pull-right-container">
                      <span class="arrow-indicator">▸</span>
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
              <?php /* ============ VISTA PLANA (SUPERVISOR, MÉDICO, ETC.) ============ */ ?>

              <?php if (in_array('Gestionar Consultas', $_SESSION["permisos"])) : ?>
                <li class="treeview">
                  <a href="../consulta_listado.php">
                    <img src="../../../recursos/imagenes/iconos/filled/objects/health_worker_form@2x.png" class="sidebar-icon">
                    <span>Consulta</span>
                  </a>
                </li>
              <?php endif; ?>

              <?php if (in_array('Gestionar Citas', $_SESSION["permisos"])) : ?>
                <li class="treeview">
                  <a href="../citas_medicas_listado.php">
                    <img src="../../../recursos/imagenes/iconos/filled/objects/calendar@2x.png" class="sidebar-icon">
                    <span>Citas Medicas</span>
                  </a>
                </li>
              <?php endif; ?>

              <?php if (in_array('Gestionar Jornadas', $_SESSION["permisos"])) : ?>
                <li class="treeview">
                  <a href="../jornadas_listado.php">
                    <img src="../../../recursos/imagenes/iconos/filled/places/rural_post@2x.png" class="sidebar-icon">
                    <span>Jornadas</span>
                  </a>
                </li>
                <li><a href="../programa_listado.php">
                  <img src="../../../recursos/imagenes/iconos/filled/objects/book@2x.png" style="height:20px; width:20px; margin-top:-4px; margin-right:10px; filter:invert(1);">
                  <span>Programa</span></a></li>
              <?php endif; ?>

              <?php if (in_array('Gestionar Vacunas', $_SESSION["permisos"])) : ?>
                <li class="header">MÓDULO DE INMUNIZACIÓN</li>
                <li><a href="../vacunacion_listado.php">
                  <img src="../../../recursos/imagenes/iconos/filled/devices/syringe@2x.png" style="height:20px; width:20px; margin-top:-4px; margin-right:10px; filter:invert(1);">
                  <span>Tarjetas de Vacunación</span></a></li>
                <li><a href="../vacunacion_cuarto_frio.php">
                  <img src="../../../recursos/imagenes/iconos/filled/devices/syringe@2x.png" style="height:20px; width:20px; margin-top:-4px; margin-right:10px; filter:invert(1);">
                  <span>Cuarto Frío</span></a></li>
                <li><a href="../vacunacion_inventario_listado.php">
                  <img src="../../../recursos/imagenes/iconos/filled/objects/rdt_result_out_stock@2x.png" style="height:20px; width:20px; margin-top:-4px; margin-right:0px; filter:invert();">
                  <span>Inventario de Vacunas</span></a></li>
                <li><a href="../vacunacion_lotes_listado.php">
                  <img src="../../../recursos/imagenes/iconos/filled/objects/rdt_result_out_stock@2x.png" style="height:20px; width:20px; margin-top:-4px; margin-right:0px; filter:invert();">
                  <span>Lotes de Vacunas</span></a></li>
              <?php endif; ?>

              <?php if (in_array('Gestionar Pacientes', $_SESSION["permisos"])) : ?>
                <li class="header">MÓDULO DE PERSONAS</li>
                <?php if (in_array('Ver Pacientes', $_SESSION["permisos"])) : ?>
                  <li><a href="../pacientes_listado.php">
                    <img src="../../../recursos/imagenes/iconos/filled/people/people@2x.png" class="sidebar-icon">
                    <span>Censo General</span></a></li>
                  <li><a href="../pacientes_menores_listado.php">
                    <img src="../../../recursos/imagenes/iconos/filled/people/child_care@2x.png" style="height:20px; width:20px; margin-top:-4px; margin-right:0px; filter:invert();">
                    <span>Censo Menores</span></a></li>
                <?php endif; ?>
                <?php if (in_array('Ver Representantes', $_SESSION["permisos"])) : ?>
                  <li><a href="../representantes_listado.php">
                    <img src="../../../recursos/imagenes/iconos/filled/people/i_groups_perspective_crowd@2x.png" style="height:20px; width:20px; margin-top:-4px; margin-right:0px; filter:invert();">
                    <span>Representantes</span></a></li>
                <?php endif; ?>
              <?php endif; ?>

              <?php if (in_array('Gestionar Salud', $_SESSION["permisos"])) : ?>
                <li class="header">MÓDULO DE SALUD</li>
                <?php if (in_array('Ver Patologias', $_SESSION["permisos"])) : ?>
                  <li><a href="../salud_patologias_listado.php">
                    <img src="../../../recursos/imagenes/iconos/filled/ppe/ppe_face_mask@2x.png" class="sidebar-icon">
                    <span>Patologías</span></a></li>
                <?php endif; ?>
                <?php if (in_array('Ver Alergias', $_SESSION["permisos"])) : ?>
                  <li><a href="../salud_alergias_listado.php">
                    <img src="../../../recursos/imagenes/iconos/filled/ppe/ppe-mask@2x.png" style="height:20px; width:20px; margin-top:-4px; margin-right:0px; filter:invert();">
                    <span>Alergias</span></a></li>
                <?php endif; ?>
                <?php if (in_array('Ver Sintomas', $_SESSION["permisos"])) : ?>
                  <li><a href="../salud_sintomas_listado.php">
                    <img src="../../../recursos/imagenes/iconos/filled/conditions/chills@2x.png" style="height:20px; width:20px; margin-top:-4px; margin-right:0px; filter:invert();">
                    <span>Síntomas</span></a></li>
                <?php endif; ?>
              <?php endif; ?>

              <?php if (in_array('Gestionar Farmacia', $_SESSION["permisos"])) : ?>
                <li class="header">GESTIÓN DE FARMACIA</li>
                <?php if (in_array('Ver Inventario', $_SESSION["permisos"])) : ?>
                  <li><a href="../farmacia_inventario_listado.php">
                    <img src="../../../recursos/imagenes/iconos/filled/places/rural_post_alt@2x.png" class="sidebar-icon">
                    <span>Inventario General</span></a></li>
                <?php endif; ?>
                <?php if (in_array('Ver kardex de medicamentos', $_SESSION["permisos"])) : ?>
                  <li><a href="../farmacia_inventario_kardex.php">
                    <img src="../../../recursos/imagenes/iconos/filled/objects/spreadsheets@2x.png" style="height:18px; width:18px; margin-top:-4px; margin-right:10px; filter:invert();">
                    <span>Kardex</span></a></li>
                <?php endif; ?>
                <?php if (in_array('Ver Medicamentos', $_SESSION["permisos"])) : ?>
                  <li><a href="../farmacia_medicamentos_listado.php">
                    <img src="../../../recursos/imagenes/iconos/filled/medications/blister_pills_round_x14@2x.png" style="height:18px; width:18px; margin-top:-4px; margin-right:10px; filter:invert();">
                    <span>Medicamentos</span></a></li>
                <?php endif; ?>
                <?php if (in_array('Ver Proveedores', $_SESSION["permisos"])) : ?>
                  <li><a href="../farmacia_proveedores_listado.php">
                    <img src="../../../recursos/imagenes/iconos/filled/people/city_worker@2x.png" style="height:18px; width:18px; margin-top:-4px; margin-right:10px; filter:invert();">
                    <span>Proveedores</span></a></li>
                <?php endif; ?>
                <?php if (in_array('Ver Laboratorios', $_SESSION["permisos"])) : ?>
                  <li><a href="../farmacia_laboratorio_listado.php">
                    <img src="../../../recursos/imagenes/iconos/filled/places/rural_post@2x.png" style="height:18px; width:18px; margin-top:-4px; margin-right:10px; filter:invert();">
                    <span>Laboratorios</span></a></li>
                <?php endif; ?>
                <?php if (in_array('Ver Recetas', $_SESSION["permisos"])) : ?>
                  <li><a href="../farmacia_prescripciones_listado.php" id="menu-link-recetas">
                    <img src="../../../recursos/imagenes/iconos/filled/symbols/i_note_action@2x.png" style="height:20px; width:20px; margin-top:-4px; margin-right:0px; filter:invert();">
                    <span>Récipes (Prescripciones)</span></a></li>
                <?php endif; ?>
                <?php if (in_array('Ver Lotes', $_SESSION["permisos"])) : ?>
                  <li><a href="../farmacia_lotes_listado.php">
                    <img src="../../../recursos/imagenes/iconos/filled/objects/rdt_result_out_stock@2x.png" style="height:20px; width:20px; margin-top:-4px; margin-right:0px; filter:invert();">
                    <span>Lotes</span></a></li>
                <?php endif; ?>
                <?php if (in_array('Ver Inventario', $_SESSION["permisos"])) : ?>
                  <li><a href="../equipos_listado.php">
                    <img src="../../../recursos/imagenes/iconos/filled/places/rural_post_alt@2x.png" class="sidebar-icon">
                    <span>Inventario de Equipos</span></a></li>
                <?php endif; ?>
              <?php endif; ?>

              <?php if (in_array('Gestionar RH', $_SESSION["permisos"])) : ?>
                <li class="header">RECURSOS HUMANOS</li>
                <?php if (in_array('Ver Medicos', $_SESSION["permisos"])) : ?>
                  <li><a href="../rh_medico_listado.php">
                    <img src="../../../recursos/imagenes/iconos/filled/people/doctor@2x.png" class="sidebar-icon">
                    <span>Médicos</span></a></li>
                <?php endif; ?>
                <?php if (in_array('Ver Areas', $_SESSION["permisos"])) : ?>
                  <li><a href="../rh_areas_listado.php">
                    <img src="../../../recursos/imagenes/iconos/filled/places/emergency_post@2x.png" style="height:18px; width:18px; margin-top:-4px; margin-right:0px; filter:invert();">
                    <span>Áreas</span></a></li>
                <?php endif; ?>
                <?php if (in_array('Ver Especialidades', $_SESSION["permisos"])) : ?>
                  <li><a href="../rh_especialidades_listado.php">
                    <img src="../../../recursos/imagenes/iconos/filled/specialties/coronary_care_unit@2x.png" style="height:18px; width:18px; margin-top:-4px; margin-right:0px; filter:invert();">
                    <span>Especialidades</span></a></li>
                <?php endif; ?>
              <?php endif; ?>

            <?php endif; ?>
          <?php endif; ?>
        </ul>

        <div class="sidebar-footer ocultar-al-colapsar">
          <div style="font-size: 12px; white-space: nowrap; overflow: hidden;">
            <i class="fa fa-calendar" style="margin-right: 5px;"><img src="../../../recursos/imagenes/iconos/calendar-lines-svgrepo-com.svg" style="width: 25px; height: 25px; filter:invert(1);"></i>
            <span id="fecha-actual"></span>
          </div>
          <div style="font-size: 16px; font-weight: bold; margin-top: 5px; white-space: nowrap; overflow: hidden;">
            <i class="fa fa-clock-o" style="margin-right: 5px;"><img src="../../../recursos/imagenes/iconos/clock-two-svgrepo-com.svg" style="width: 25px; height: 25px; filter:invert(1);"></i>
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
            <a href="../../../cfg/login/logout.php" id="confirmarSalida" class="btn btn-danger">Cerrar Sesión</a>
          </div>
        </div>
      </div>
    </div>

    <script src="../../../plugins/jQuery/jquery-2.2.3.min.js" data-persistent="true"></script>
    <script src="../../../recursos/js/app.min.js" data-persistent="true"></script>
    <script src="../../../recursos/bootstrap/js/bootstrap.min.js" data-persistent="true"></script>

    <script data-persistent="true">
    $(document).ready(function() {
      var $btnLogout = $('#confirmarSalida');
      if ($btnLogout.length) {
        var urlLogoutAbs = $btnLogout.prop('href');

        $btnLogout.attr('href', urlLogoutAbs);
        $btnLogout.attr('data-no-pjax', 'true');
        $btnLogout.addClass('no-pjax');         

        console.log('[LOGOUT] URL capturada:', urlLogoutAbs);
        $(document).off('click.logoutConfirm', '#confirmarSalida');
        $(document).on('click.logoutConfirm', '#confirmarSalida', function(e) {
          e.preventDefault();
          e.stopImmediatePropagation();

          localStorage.removeItem('cpt_sidebar_collapse');
          sessionStorage.removeItem('recetas_vistas_ids');
          localStorage.removeItem('cpt_last_alert_id');
          localStorage.removeItem('cpt_alertas_cache');

          $('#modalCerrarSesion').modal('hide');

          setTimeout(function() {
            window.location.replace(urlLogoutAbs);
          }, 80);

          return false;
        });
      }
    });
    </script>