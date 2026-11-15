<?php
include('includes/headerNav2.php');
verificarAcceso('Gestionar Citas');
?>

<head>
  <title>Citas Médicas | Listado</title>
</head>

<style>
  @keyframes pulso-borde {
    0% {
      box-shadow: inset 0 0 0px rgba(255, 193, 7, 0);
    }

    50% {
      box-shadow: inset 0 0 10px rgba(255, 193, 7, 0.3);
    }

    100% {
      box-shadow: inset 0 0 0px rgba(255, 193, 7, 0);
    }
  }

  @keyframes parpadeo-alerta {
    0% {
      opacity: 1;
    }

    50% {
      opacity: 0.6;
    }

    100% {
      opacity: 1;
    }
  }

  #modalEditarFechaHora .modal-dialog {
    max-width: 95%;
    width: 800px;
    font-size: 18px;
  }

  #modalEditarFechaHora .modal-body {
    max-height: 60vh;
    overflow-y: auto;
    overflow-x: hidden;
  }

  .fc {
    font-size: 0.9em;
  }

  .fc .fc-toolbar {
    margin-bottom: 10px !important;
    padding: 5px;
  }

  .fc .fc-toolbar-title {
    font-size: 1.2em !important;
    font-weight: bold;
    text-transform: capitalize;
  }

  .fc .fc-daygrid-day-frame {
    min-height: 80px !important;
  }

  .fc-event {
    border-radius: 3px !important;
    padding: 2px 5px !important;
    font-size: 0.85em !important;
    cursor: pointer;
  }
</style>

<div class="content-wrapper">
  <?php
  include('../../cfg/conexion.php');

  $filtro_medico_header = "";
  if (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Medico') {
    $id_persona_logueada = $_SESSION['id'];
    $sql_med_h = "SELECT Id_detalle_medico FROM detalle_medico WHERE Id_persona = $id_persona_logueada LIMIT 1";
    $res_med_h = mysqli_query($conexion, $sql_med_h);
    if ($res_med_h && mysqli_num_rows($res_med_h) > 0) {
      $row_med_h = mysqli_fetch_assoc($res_med_h);
      $filtro_medico_header = " AND Id_medico = " . $row_med_h['Id_detalle_medico'];
    }
  }

  $sqlCita = ("SELECT * FROM citas WHERE estatus = 1" . $filtro_medico_header . " ORDER BY Id_cita ASC");
  $queryData   = mysqli_query($conexion, $sqlCita);
  $total_cita = mysqli_num_rows($queryData);
  ?>

  <section class="content-header">
    <h1>
      Citas Médicas (<?php echo $total_cita; ?>)
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="#"><i class="fa fa-user"></i>Citas Médicas</a></li>
      <li><a href="#"><i class="fa fa-book"></i>Listado</a></li>
    </ol>
  </section>

  <section class="content">
    <div class="toolbar-container">
      <?php if (in_array('Ver papelera de citas', $_SESSION["permisos"])) : ?>
        <a href="papelera/citas_medicas_papelera_listado.php" class="btn-sm btn-primary pull-right" style="background-color:gray;"> Papelera </a>
      <?php endif; ?>
      <p class="pull-right" style="width:5px;"></p>
      <?php if (in_array('Generar Reportes de Citas', $_SESSION["permisos"])) : ?>
        <a href="#" class="btn-sm btn-info pull-right reporte"><i class="fa fa-book"></i> Generar Reporte </a>
      <?php endif; ?>
      <p class="pull-right" style="width:5px;"></p>
      <?php if (in_array('Crear Citas', $_SESSION["permisos"])) : ?>
        <a href="citas_medicas_agregar.php" class="btn-sm btn-success pull-right"> Agendar Nueva Cita </a>
      <?php endif; ?>
      <p class="pull-right" style="width:5px;"></p>
      <?php if (in_array('Gestionar configuraciones de citas', $_SESSION["permisos"])) : ?>
        <a href="#" class="btn-sm btn-info pull-right" data-toggle="modal" data-target="#modalEditarFechaHora">
          <i class="fas fa-calendar-alt"></i> Editar Fecha y Hora Límite
        </a>
      <?php endif; ?>
      <p class="pull-right" style="width:5px;"></p>
      <a href="#" class="btn-sm btn-primary pull-right" id="btnToggleVista" data-vista="tabla">
        <i class="fa fa-calendar"></i> Ver Calendario
      </a>
      <div class="pull-left form-inline">
        <form method="GET" action="" id="formBusquedaRapida">
          <input type="text" id="buscar" name="buscar" class="form-control" placeholder="Escriba para buscar..." value="" style="border-radius:0; height:10%; width:250px; display:inline-block;" autocomplete="off">
        </form>
      </div>
      <span data-toggle="tooltip" data-placement="right" title="Filtros avanzados de búsqueda">
        <button type="button" class="btn-sm btn-primary btn-sm pull-left" data-toggle="modal" data-target="#modalBusquedaAvanzada">
          <i class="fa fa-filter"><img src="../../recursos/imagenes/iconos/filtrar.png" style="width:10px; height:10px; filter:invert(1);" title="filtrar consulta"></i>
        </button>
      </span>
    </div>
    <br><br>
    <div id="contenedorCalendario" style="display: none; background: white; padding: 20px; border-radius: 5px;">
      <div id="calendar" style="max-width: 900px; max-height: 480px; margin:0 auto;"></div>
    </div>
    <div id="contenedorTabla">
      <table class="table table-sm table-hover mt-4" width="100%" height="20" id="t_user">
        <thead class="table-dark">
          <th>Paciente</th>
          <th>Médico</th>
          <th>Fecha y Hora</th>
          <th>Estado</th>
          <?php if (in_array('Gestionar acciones de citas', $_SESSION["permisos"])) : ?>
            <th>Cambios</th>
          <?php endif; ?>
          <th>Acciones</th>

        </thead>
        <tbody class="tbody" width="100%" style="font-size: 12px;">
          <tr>
            <?php
            $busqueda = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : '';
            $f_desde  = isset($_GET['f_desde']) ? mysqli_real_escape_string($conexion, $_GET['f_desde']) : '';
            $f_hasta  = isset($_GET['f_hasta']) ? mysqli_real_escape_string($conexion, $_GET['f_hasta']) : '';
            $f_estado = isset($_GET['f_estado']) ? mysqli_real_escape_string($conexion, $_GET['f_estado']) : '';

            $registros_por_pagina = 14;
            $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
            $inicio = ($pagina_actual - 1) * $registros_por_pagina;

            $donde = "WHERE c.estatus = 1";

            if (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Medico') {
              $id_persona_logueada = $_SESSION['id'];
              $sql_med_l = "SELECT Id_detalle_medico FROM detalle_medico WHERE Id_persona = $id_persona_logueada LIMIT 1";
              $res_med_l = mysqli_query($conexion, $sql_med_l);
              if ($res_med_l && mysqli_num_rows($res_med_l) > 0) {
                $row_med_l = mysqli_fetch_assoc($res_med_l);
                $donde .= " AND c.Id_medico = " . $row_med_l['Id_detalle_medico'];
              }
            }

            if ($busqueda != '') {
              $donde .= " AND (p.nombre LIKE '%$busqueda%' 
                OR p.apellido LIKE '%$busqueda%' 
                OR p.cedula LIKE '%$busqueda%' 
                OR c.motivo LIKE '%$busqueda%'
                OR m.nombre LIKE '%$busqueda%'
                OR m.apellido LIKE '%$busqueda%')";
            }

            if ($f_desde != '') {
              $donde .= " AND c.fecha_cita >= '$f_desde'";
            }
            if ($f_hasta != '') {
              $donde .= " AND c.fecha_cita <= '$f_hasta'";
            }
            if ($f_estado != '') {
              $donde .= " AND c.estado = '$f_estado'";
            }

            $sql_conteo = "SELECT COUNT(*) as total 
               FROM citas c
               INNER JOIN persona p ON c.Id_paciente = p.id
               INNER JOIN detalle_medico dm ON c.Id_medico = dm.Id_detalle_medico
               INNER JOIN persona m ON dm.Id_persona = m.id 
              $donde";
            $resultado_conteo = mysqli_query($conexion, $sql_conteo);
            $fila_conteo = mysqli_fetch_assoc($resultado_conteo);
            $total_citas = $fila_conteo['total'];
            $total_paginas = ceil($total_citas / $registros_por_pagina);

            $sql = "SELECT c.*, Id_cita,
              p.nombre AS nombre_paciente, p.apellido AS apellido_paciente,
              p.tipo_cedula AS tipo_cedula_paciente, p.cedula AS cedula_paciente, 
              m.nombre AS nombre_medico, m.apellido AS apellido_medico
              FROM citas c
              INNER JOIN persona p ON c.Id_paciente = p.id
              INNER JOIN detalle_medico dm ON c.Id_medico = dm.Id_detalle_medico
              INNER JOIN persona m ON dm.Id_persona = m.id
              $donde
              ORDER BY c.fecha_cita DESC, c.hora_cita DESC
              LIMIT $inicio, $registros_por_pagina";
            $resultado = $conexion->query($sql);
            ?>
          </tr>
          <tr>
            <?php

            if ($resultado->num_rows > 0) {
              while ($row = $resultado->fetch_assoc()) {

                $estatus = $row['estado'];
                $clase_badge = 'bg-gray';

                if ($estatus == 'Pendiente') $clase_badge = 'label-warning';
                if ($estatus == 'Confirmada') $clase_badge = 'label-info';
                if ($estatus == 'Finalizada') $clase_badge = 'label-success';
                if ($estatus == 'Cancelada') $clase_badge = 'label-danger';
                if ($estatus == 'Inasistente') $clase_badge = 'label-default';
                if ($estatus == 'Vencida') $clase_badge = 'label-default';
                if ($estatus == 'Reprogramada') $clase_badge = 'label-success';

            ?>
          </tr>
          <tr>
            <td class=""><span class="text-row text-white"><?= $row['nombre_paciente']; ?> <?= $row['apellido_paciente']; ?></span></td>
            <td class=""><span class="text-row text-white"><?= $row['nombre_medico']; ?> <?= $row['apellido_medico']; ?></span></td>
            <td class=""><span class="text-row text-white"><?= date('d/m/Y', strtotime($row['fecha_cita'])); ?> - <?= date('h:i A', strtotime($row['hora_cita'])); ?></span></td>
            <!-- Estado -->
            <td class="">
              <span class="label <?= $clase_badge; ?>" style="font-size: 11px;"><?= strtoupper($estatus); ?></span>
            </td>

            <!-- Cambios (solo si tiene permiso) -->
            <?php if (in_array('Gestionar acciones de citas', $_SESSION["permisos"])) : ?>
              <td class="td-cambios">
                <?php if ($estatus == 'Pendiente') : ?>
                  <a href="#" onclick="cambiarEstado(<?= $row['Id_cita'] ?>, 'Confirmada'); return false;" class="btn-sm btn-info" title="Confirmar"><img src="../../recursos/imagenes/iconos/Arrow-w.png" style="width:15px;height:15px;"></a>
                  <a href="#" onclick="cambiarEstado(<?= $row['Id_cita'] ?>, 'Cancelada'); return false;" class="btn-sm btn-danger" title="Cancelar"><img src="../../recursos/imagenes/iconos/cancelar.png" style="width:15px;height:15px;"></a>
                <?php endif; ?>

                <?php if ($estatus == 'Vencida') : ?>
                  <a href="#" onclick="cambiarEstado(<?= $row['Id_cita'] ?>, 'Cancelada'); return false;" class="btn-sm btn-danger" title="Cancelar"><img src="../../recursos/imagenes/iconos/cancelar.png" style="width:15px;height:15px;"></a>
                <?php endif; ?>

                <?php if ($estatus == 'Inasistente') : ?>
                  <a href="#" onclick="gestionarReprogramacion(<?= $row['Id_cita'] ?>, '<?= $row['estado'] ?>'); return false;" class="btn-sm btn-success" title="Reprogramar"><img src="../../recursos/imagenes/iconos/reprogramar.png" style="width:15px;height:15px;"></a>
                  <a href="#" onclick="cambiarEstado(<?= $row['Id_cita'] ?>, 'Cancelada'); return false;" class="btn-sm btn-danger" title="Cancelar"><img src="../../recursos/imagenes/iconos/cancelar.png" style="width:15px;height:15px;"></a>
                <?php endif; ?>

                <?php if ($estatus == 'Confirmada' || $estatus == 'Reprogramada') : ?>
                  <a href="consulta_agregar.php?tipo_cedula=<?= $row['tipo_cedula_paciente'] ?>&cedula=<?= $row['cedula_paciente'] ?>&Id=<?= $row['Id_cita'] ?>&medico=<?= $row['Id_medico'] ?>&motivo=<?= $row['motivo'] ?>&fecha=<?= $row['fecha_cita'] ?>" class="btn-sm btn-info" title="Atender Paciente"><img src="../../recursos/imagenes/iconos/atender.png" style="width:15px;height:15px;"></a>
                  <a href="#" onclick="cambiarEstado(<?= $row['Id_cita'] ?>, 'Finalizada'); return false;" class="btn-sm btn-success" title="Finalizar"><img src="../../recursos/imagenes/iconos/finalizada.png" style="width:15px;height:15px;"></a>
                  <a href="#" onclick="cambiarEstado(<?= $row['Id_cita'] ?>, 'Cancelada'); return false;" class="btn-sm btn-danger" title="Cancelar"><img src="../../recursos/imagenes/iconos/cancelar.png" style="width:15px;height:15px;"></a>
                <?php endif; ?>
              </td>
            <?php endif; ?>

            <!-- Acciones (siempre) -->
            <td class="td-acciones">
              <?php if (in_array('Generar Comprobante de Cita', $_SESSION["permisos"])) : ?>
                <a href="../../cfg/pdf/pdf_cita.php?Id=<?= $row['Id_cita'] ?>" class="btn-sm btn-primary" title="Generar Comprobante"><img src="../../recursos/imagenes/iconos/documento.png" style="width:15px;height:15px;"></a>
              <?php endif; ?>
              <?php if (in_array('Editar Citas', $_SESSION["permisos"])) : ?>
                <a href="citas_medicas_editar.php?Id=<?= $row['Id_cita'] ?>" class="btn-sm btn-warning" title="Editar"><img src="../../recursos/imagenes/iconos/editar.png" style="width:15px;height:15px;"></a>
              <?php endif; ?>
              <?php if (in_array('Desactivar Citas', $_SESSION["permisos"])) : ?>
                <a href="#" data-id="<?= $row['Id_cita'] ?>" class="btn-sm btn-danger btn-desactivar" title="Desactivar"><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px;height:15px;"></a>
              <?php endif; ?>
            </td>
          </tr>
      <?php }
            } else {
              echo "<tr><td colspan='6'>No se encontraron citas registradas.</td></tr>";
            }  ?>
        </tbody>
      </table>
    </div>
    <nav id="contenedorPaginacion" aria-label="Page navigation">
      <ul class="pagination">
        <?php
        $query_string = (isset($busqueda) && $busqueda != '') ? "&buscar=" . urlencode($busqueda) : "";
        if ($busqueda != '') $query_string .= "&buscar=" . urlencode($busqueda);
        if ($f_desde != '')  $query_string .= "&f_desde=" . urlencode($f_desde);
        if ($f_hasta != '')  $query_string .= "&f_hasta=" . urlencode($f_hasta);
        if ($f_estado != '') $query_string .= "&f_estado=" . urlencode($f_estado);

        if ($pagina_actual > 1) : ?>
          <li><a href="?pagina=1<?php echo $query_string; ?>" title="Primero">&laquo;&laquo;</a></li>
          <li><a href="?pagina=<?php echo ($pagina_actual - 1) . $query_string; ?>">&laquo;</a></li>
        <?php endif;

        $rango = 1;
        $inicio_ventana = max(1, $pagina_actual - $rango);
        $fin_ventana = min($total_paginas, $pagina_actual + $rango);

        if ($pagina_actual == 1) $fin_ventana = min($total_paginas, 3);
        if ($pagina_actual == $total_paginas) $inicio_ventana = max(1, $total_paginas - 2);

        for ($i = $inicio_ventana; $i <= $fin_ventana; $i++) : ?>
          <li class="<?php echo ($i == $pagina_actual) ? 'active' : ''; ?>">
            <a href="?pagina=<?php echo $i . $query_string; ?>"><?php echo $i; ?></a>
          </li>
        <?php endfor;

        if ($pagina_actual < $total_paginas) : ?>
          <li><a href="?pagina=<?php echo ($pagina_actual + 1) . $query_string; ?>">&raquo;</a></li>
          <li><a href="?pagina=<?php echo $total_paginas . $query_string; ?>" title="Último">&raquo;&raquo;</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  </section>

  <?php
  $mostrar_modal_exito = false;
  $mostrar_modal_error = false;
  $mensaje_modal = '';

  if (isset($_SESSION['mensaje_user_exito'])) {
    $mostrar_modal_exito = true;
    $mensaje_modal = $_SESSION['mensaje_user_exito'];
    unset($_SESSION['mensaje_user_exito']);
  } elseif (isset($_SESSION['mensaje_user_error'])) {
    $mostrar_modal_error = true;
    $mensaje_modal = $_SESSION['mensaje_user_error'];
    unset($_SESSION['mensaje_user_error']);
  }
  ?>

  <div class="modal fade" id="modalExito" tabindex="-1" role="dialog" aria-labelledby="modalExitoLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-green">
          <h5 class="modal-title" id="modalExitoLabel" style="color: white;">Operación Exitosa</h5>
        </div>
        <div class="modal-body">
          <p id="mensajeExito"><?php echo $mensaje_modal; ?></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success" data-dismiss="modal">Aceptar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalBusquedaAvanzada" tabindex="-1" role="dialog" aria-labelledby="modalFiltrosLabel">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <form id="formBusquedaAvanzada" method="GET" action="">
          <div class="modal-header bg-primary">

            <h4 class="modal-title" id="modalFiltrosLabel"><i class="fa fa-filter"></i> Filtros Avanzados de Citas</h4>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-4 form-group">
                <label>Fecha Desde</label>
                <input type="date" name="f_desde" class="form-control" max="<?php echo date('Y-m-d'); ?>">
              </div>
              <div class="col-md-4 form-group">
                <label>Fecha Hasta</label>
                <input type="date" name="f_hasta" class="form-control" min="<?php echo date('Y-m-d'); ?>">
              </div>
              <div class="col-md-4 form-group">
                <label>Estado de la Cita</label>
                <select name="f_estado" class="form-control">
                  <option value="">Todos los Estados</option>
                  <option value="Pendiente" <?php echo ($f_estado == 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                  <option value="Confirmada" <?php echo ($f_estado == 'Confirmada') ? 'selected' : ''; ?>>Confirmada</option>
                  <option value="Finalizada" <?php echo ($f_estado == 'Finalizada') ? 'selected' : ''; ?>>Finalizada</option>
                  <option value="Cancelada" <?php echo ($f_estado == 'Cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                  <option value="Inasistente" <?php echo ($f_estado == 'Inasistente') ? 'selected' : ''; ?>>Inasistente</option>
                  <option value="Vencida" <?php echo ($f_estado == 'Vencida') ? 'selected' : ''; ?>>Vencida</option>
                  <option value="Reprogramada" <?php echo ($f_estado == 'Reprogramada') ? 'selected' : ''; ?>>Reprogramada</option>
                </select>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12 form-group">
                <label>Término de Búsqueda (Paciente, Médico, Motivo...)</label>
                <input type="text" name="buscar" class="form-control" placeholder="Escriba aquí..." value="<?php echo htmlspecialchars($busqueda); ?>">
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-second pull-left" onclick="limpiarFiltrosAjax()">Limpiar Filtros</button>
            <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn btn-success"><i class="fa fa-search"></i> Aplicar Búsqueda</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalError" tabindex="-1" role="dialog" aria-labelledby="modalErrorLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h5 class="modal-title" id="modalErrorLabel" style="color: white;">Error en la Operación</h5>
        </div>
        <div class="modal-body">
          <p id="mensajeError"><?php echo $mensaje_modal; ?></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="DesactivarCita" tabindex="-1" role="dialog" aria-labelledby="DesactivarCitaLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h4 class="modal-title" id="DesactivarCitaLabel">Confirmación de Desactivacion</h4>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea desactivar esta cita? Esta acción solo se puede revertir en la papelera.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <a id="desactivar" href="#" class="btn btn-danger">Aceptar</a>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalCambioEstado" tabindex="-1" role="dialog" aria-labelledby="modalCambioEstadoLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h5 class="modal-title" id="modalCambioEstadoLabel" style="color: white;">Confirmar Cambio de Estado</h5>
        </div>
        <div class="modal-body">
          <p id="textoConfirmacionEstado">¿Confirmar cambio de estado?</p>
          <input type="hidden" id="citaIdEstado">
          <input type="hidden" id="citaNuevoEstado">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-danger" id="btnConfirmarEstado">Aceptar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="ModalReporteCitas" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h4 class="modal-title" id="myModalLabel"><i class="fa fa-file-pdf-o"></i> Reportes de Citas Médicas</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Seleccione el tipo de reporte:</label>
            <select class="form-control" id="tipo_reporte_citas">
              <option value="hoy">Citas para el día de Hoy</option>
              <option value="proximas">Próximas Citas (Futuras)</option>
              <option value="estados">Estadísticas por Estado (Pendiente, Finalizada, etc.)</option>
              <option value="totales">Historial Completo de Citas</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-primary" id="btnEjecutarReporteCitas">Generar PDF</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalEditarFechaHora" tabindex="-1" role="dialog" aria-labelledby="tituloModalFecha" aria-hidden="true">
    <div class="modal-dialog " role="document">
      <div class="modal-content">

        <div class="modal-header bg-primary">
          <h4 class="modal-title" id="tituloModalFecha">
            <i class="fa fa-clock-o"></i> Configurar Horarios y Días Laborables por Día
          </h4>
        </div>

        <div class="modal-body">
          <p class="text-muted" style="margin-bottom: 15px;">
            Selecciona los días que deseas configurar con su respectiva hora límite. <br>
            Si marcan un día como <strong>No Laborable</strong>,
            las citas de ese día se deshabilitarán o marcarán en consecuencia.
          </p>

          <form id="formFechaHoraDias">
            <div id="contenedorFilasDias">
            </div>

            <div class="row" style="margin-top: 10px;">
              <div class="col-xs-12">
                <button type="button" class="btn btn-sm btn-info" id="btnAgregarDia">
                  <i class="fa fa-plus-circle"></i> Añadir otro día
                </button>
              </div>
            </div>
          </form>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-success" id="btnGuardarFechaHora">
            <i class="fa fa-save"></i> Guardar Cambios
          </button>
        </div>

      </div>
    </div>
  </div>

  <div class="modal fade" id="modalDetalleCitaEvento" tabindex="-1" role="dialog" aria-labelledby="modalDetalleCitaLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h4 class="modal-title" id="modalDetalleCitaLabel"><i class="fa fa-calendar-check-o"></i> Detalle de la Cita</h4>
        </div>
        <div class="modal-body">
          <p><strong>Motivo:</strong> <span id="eventoMotivo"></span></p>
          <p><strong>Paciente / Médico:</strong> <span id="eventoTitulo"></span></p>
          <p><strong>Fecha y Hora:</strong> <span id="eventoFecha"></span></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <?php
  include('includes/footer.php');
  ?>

  <script src="../../plugins/fullcalendar/dist/index.global.js"></script>
  <script src="../../plugins/fullcalendar/dist/index.global.min.js"></script>
  <script>
    var calendar;

    $('.reporte').on('click', function(e) {
      e.preventDefault();
      $('#ModalReporteCitas').modal('show');
    });

    $('#btnEjecutarReporteCitas').on('click', function() {
      var tipo = $('#tipo_reporte_citas').val();
      window.open('../../cfg/reportes/generar_pdf_citas.php?tipo=' + tipo, '_blank');
      $('#ModalReporteCitas').modal('hide');
    });

    $(document).on('click', '.btn-desactivar', function(e) {
      e.preventDefault();
      var IdCita = $(this).data('id');
      var urlDesactivar = "../../cfg/desactivar/desactivar_cita.php?Id=" + IdCita;
      $('#desactivar').attr('href', urlDesactivar);
      $('#DesactivarCita').modal('show');
    })

    function gestionarReprogramacion(id, estatusActual) {
      if (estatusActual === 'Inasistente') {
        $.ajax({
          url: '../../cfg/ajax/actualizar_estado_cita.php',
          type: 'POST',
          data: {
            id: id,
            estado: 'Reprogramada'
          },
          success: function(response) {
            if (response.trim() === "ok") {
              window.location.href = 'citas_medicas_editar.php?Id=' + id;
            } else {
              alert("Error al intentar cambiar el estado a Reprogramada.");
            }
          },
          error: function() {
            alert("Error de conexión al intentar reprogramar.");
          }
        });
      }
    }

    document.addEventListener('DOMContentLoaded', function() {

      $('#modalEditarFechaHora').on('show.bs.modal', function() {
        inicializarModal();
      });

      $('#btnAgregarDia').on('click', function() {
        agregarFilaDia('', '', '', false);
      });

      $(document).on('click', '.btn-eliminar-fila', function() {
        $(this).closest('.fila-dia').remove();
      });

      const opcionesDias = [{
          val: '1',
          label: 'Lunes'
        },
        {
          val: '2',
          label: 'Martes'
        },
        {
          val: '3',
          label: 'Miércoles'
        },
        {
          val: '4',
          label: 'Jueves'
        },
        {
          val: '5',
          label: 'Viernes'
        },
        {
          val: '6',
          label: 'Sábado'
        },
        {
          val: '7',
          label: 'Domingo'
        }
      ];

      function agregarFilaDia(diaDefault = '', horaInicioDefault = '', horaFinDefault = '', noLaborable = false) {
        let index = $('.fila-dia').length;

        if (index >= 7) {
          alert("Ya has agregado los 7 días de la semana.");
          return;
        }

        let selectOptions = '<option value="">-- Seleccione --</option>';
        opcionesDias.forEach(function(d) {
          let selected = (d.val == diaDefault) ? 'selected' : '';
          selectOptions += `<option value="${d.val}" ${selected}>${d.label}</option>`;
        });

        let htmlFila = `
          <div class="row fila-dia" style="margin-bottom: 12px; background: #f9f9f9; padding: 10px 5px; border-radius: 4px; border: 1px solid #e3e3e3;">
            <div class="col-md-3 col-sm-3 col-xs-12 form-group" style="margin-bottom: 5px;">
              <label style="font-size: 11px;">Día</label>
              <select class="form-control input-sm select-dia" required>
                ${selectOptions}
              </select>
            </div>

            <div class="col-md-2 col-sm-2 col-xs-6 form-group" style="margin-bottom: 5px;">
              <label style="font-size: 11px;">Hora Inicio</label>
              <input type="time" class="form-control input-sm input-hora-inicio" value="${horaInicioDefault}" ${noLaborable ? 'disabled' : ''} required>
            </div>

            <div class="col-md-2 col-sm-2 col-xs-6 form-group" style="margin-bottom: 5px;">
              <label style="font-size: 11px;">Hora Límite</label>
              <input type="time" class="form-control input-sm input-hora-fin" value="${horaFinDefault}" ${noLaborable ? 'disabled' : ''} required>
            </div>

            <div class="col-md-3 col-sm-3 col-xs-6 form-group text-center" style="margin-bottom: 5px; padding-top: 20px;">
              <label style="font-weight: normal; cursor: pointer; font-size: 12px;">
                <input type="checkbox" class="chk-no-laborable" ${noLaborable ? 'checked' : ''}> 
                <span class="label label-danger" style="font-size: 10px;">No Laborable</span>
              </label>
            </div>

            <div class="col-md-2 col-sm-2 col-xs-12 form-group text-right" style="margin-bottom: 5px; padding-top: 18px;">
              <button type="button" class="btn btn-sm btn-danger btn-eliminar-fila" title="Eliminar fila" ${index === 0 ? 'style="display:none;"' : ''}>
                <i class="fa fa-trash"></i> Quitar
              </button>
            </div>
          </div>
        `;

        $('#contenedorFilasDias').append(htmlFila);
      }

      function inicializarModal() {
        $('#contenedorFilasDias').empty();

        agregarFilaDia('', '', '', false);
      }

      $(document).on('change', '.select-dia', function() {
        var $fila = $(this).closest('.fila-dia');
        var diaSeleccionado = $(this).val();

        if (diaSeleccionado !== '') {
          $.ajax({
            url: '../../cfg/ajax/obtener_horarios.php',
            type: 'POST',
            dataType: 'json',
            data: {
              dia_semana: diaSeleccionado
            },
            success: function(respuesta) {
              if (respuesta.existe) {
                $fila.find('.input-hora-inicio').val(respuesta.hora_inicio);
                $fila.find('.input-hora-fin').val(respuesta.hora_fin);

                if (respuesta.estatus == '0') {
                  $fila.find('.chk-no-laborable').prop('checked', true);
                  $fila.find('.input-hora-inicio').prop('disabled', true);
                  $fila.find('.input-hora-fin').prop('disabled', true);
                } else {
                  $fila.find('.chk-no-laborable').prop('checked', false);
                  $fila.find('.input-hora-inicio').prop('disabled', false);
                  $fila.find('.input-hora-fin').prop('disabled', false);
                }
              } else {
                limpiarCamposFila($fila);
              }
            },
            error: function() {
              console.error('Error al consultar el horario del día seleccionado.');
            }
          });
        } else {
          limpiarCamposFila($fila);
        }
      });

      function limpiarCamposFila($fila) {
        $fila.find('.input-hora-inicio').val('');
        $fila.find('.input-hora-fin').val('');
        $fila.find('.chk-no-laborable').prop('checked', false);
      }

      $(document).on('change', '.chk-no-laborable', function() {
        let inputHoraInicio = $(this).closest('.fila-dia').find('.input-hora-inicio');
        let inputHoraFin = $(this).closest('.fila-dia').find('.input-hora-fin');

        if ($(this).is(':checked')) {
          inputHoraInicio.prop('disabled', true).val('');
          inputHoraFin.prop('disabled', true).val('');
        } else {
          inputHoraInicio.prop('disabled', false);
          inputHoraFin.prop('disabled', false);
        }
      });

      $('#btnGuardarFechaHora').off('click').on('click', function() {
        let configuracion = [];
        let diasSeleccionados = new Set();
        let hayError = false;
        let mensajeError = "";

        $('.fila-dia').each(function(i, el) {
          let dia = $(el).find('.select-dia').val();
          let horaInicio = $(el).find('.input-hora-inicio').val();
          let horaFin = $(el).find('.input-hora-fin').val();
          let esNoLaborable = $(el).find('.chk-no-laborable').is(':checked') ? 1 : 0;

          if (!dia) {
            hayError = true;
            mensajeError = "Por favor selecciona un día de la semana en todas las filas.";
            return false;
          }

          if (diasSeleccionados.has(dia)) {
            hayError = true;
            mensajeError = "Has seleccionado el mismo día más de una vez. Por favor corrige los duplicados.";
            return false;
          }
          diasSeleccionados.add(dia);

          if (!esNoLaborable && (!horaInicio || !horaFin)) {
            hayError = true;
            mensajeError = "Debes ingresar tanto una hora de inicio como una hora límite para los días laborables.";
            return false;
          }

          configuracion.push({
            dia: dia,
            hora_inicio: esNoLaborable ? null : horaInicio,
            hora_fin: esNoLaborable ? null : horaFin,
            no_laborable: esNoLaborable
          });
        });

        if (hayError) {
          alert(mensajeError);
          return;
        }

        let btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
          url: '../../cfg/ajax/guardar_fecha_hora.php',
          type: 'POST',
          dataType: 'json',
          data: {
            config: JSON.stringify(configuracion)
          },
          success: function(respuesta) {
            btn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar Cambios');
            $('#modalEditarFechaHora').modal('hide');

            if (respuesta.status === 'ok') {
              $('#mensajeExito').text(respuesta.mensaje);
              $('#modalExito').modal('show');

              setTimeout(function() {
                location.reload();
              }, 1500);
            } else {
              $('#mensajeError').text(respuesta.mensaje || "Ocurrió un error al procesar la solicitud.");
              $('#modalError').modal('show');
            }
          },
          error: function(xhr, status, error) {
            btn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar Cambios');
            alert("Error en el servidor al intentar guardar: " + error);
          }
        });
      });

    });

    function cambiarEstado(id, nuevoEstado) {
      $('#citaIdEstado').val(id);
      $('#citaNuevoEstado').val(nuevoEstado);

      $('#textoConfirmacionEstado').text('¿Está seguro que desea cambiar el estado de esta cita a ' + nuevoEstado + '?');

      $('#modalCambioEstado').modal('show');
    }

    $(document).ready(function() {
      $('#btnConfirmarEstado').on('click', function() {
        var id = $('#citaIdEstado').val();
        var nuevoEstado = $('#citaNuevoEstado').val();

        var btn = $(this);
        btn.prop('disabled', true).text('Procesando...');

        $.post('../../cfg/ajax/actualizar_estado_cita.php', {
          id: id,
          estado: nuevoEstado
        }, function(data) {
          $('#modalCambioEstado').modal('hide');
          btn.prop('disabled', false).text('Aceptar');

          if (data.trim() == 'ok') {
            cargarDatosAjax(window.location.href);

            $('#mensajeExito').text('El estado de la cita se actualizó correctamente.');
            $('#modalExito').modal('show');
          } else {
            $('#mensajeError').text('Error al actualizar el estado de la cita.');
            $('#modalError').modal('show');
          }
        }).fail(function() {
          $('#modalCambioEstado').modal('hide');
          btn.prop('disabled', false).text('Aceptar');
          alert("Error de conexión al procesar el cambio de estado.");
        });
      });
    });

    $(document).ready(function() {
      $('#btnToggleVista').on('click', function(e) {
        e.preventDefault();
        var vistaActual = $(this).attr('data-vista');

        if (vistaActual === 'tabla') {
          $('#contenedorTabla').hide();
          $('#contenedorCalendario').show();
          $('.pagination').hide();
          $(this).attr('data-vista', 'calendario');
          $(this).html('<i class="fa fa-table"></i> Ver Tabla');
          renderizarCalendario();
        } else {
          $('#contenedorCalendario').hide();
          $('#contenedorTabla').show();
          $('.pagination').show();
          $(this).attr('data-vista', 'tabla');
          $(this).html('<i class="fa fa-calendar"></i> Ver Calendario');
        }
      });

      function renderizarCalendario() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
          initialView: 'dayGridMonth',
          locale: 'es',
          height: 490,
          contentHeight: '490',
          aspectRatio: 1.5,
          buttonText: {
            today: 'Hoy',
            week: 'Semana',
            month: 'Mes',
            day: 'Dia',
            list: 'Agenda',
          },
          headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
          },
          events: '../../cfg/ajax/obtener_eventos_citas.php',
          eventClick: function(info) {
            $('#eventoTitulo').text(info.event.title);
            $('#eventoMotivo').text(info.event.extendedProps.description || 'No especificado');

            var fechaFormateada = info.event.start ? info.event.start.toLocaleString() : 'No disponible';
            $('#eventoFecha').text(fechaFormateada);
            $('#modalDetalleCitaEvento').modal('show');
          }
        });
        calendar.render();
      }

      window.cargarDatosAjax = function(url) {
        $('.tbody').css('opacity', '0.4');

        $.get(url, function(data) {
          var htmlParsed = $(data);

          $('.tbody').html(htmlParsed.find('.tbody').html()).css('opacity', '1');
          $('nav[aria-label="Page navigation"]').html(htmlParsed.find('nav[aria-label="Page navigation"]').html());
          e
          window.history.pushState(null, '', url);
        }).fail(function() {
          alert("Error de conexión al aplicar filtros.");
          $('.tbody').css('opacity', '1');
        });
      };

      let timerBusqueda;
      $('#buscar').on('keyup', function() {
        clearTimeout(timerBusqueda);
        let query = $(this).val();
        timerBusqueda = setTimeout(function() {
          var url = 'citas_medicas_listado.php?buscar=' + encodeURIComponent(query);
          cargarDatosAjax(url);
        }, 400);
      });

      $('#formBusquedaRapida').on('submit', function(e) {
        e.preventDefault();
      });

      $('#formBusquedaAvanzada').on('submit', function(e) {
        e.preventDefault();
        var url = 'citas_medicas_listado.php?' + $(this).serialize();
        $('#modalBusquedaAvanzada').modal('hide');
        cargarDatosAjax(url);
      });

      $(document).on('click', 'nav[aria-label="Page navigation"] .pagination a', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        if (url) {
          cargarDatosAjax(url);
        }
      });

      window.limpiarFiltrosAjax = function() {
        $('#formBusquedaAvanzada')[0].reset();
        $('#buscar').val('');
        var urlLimpia = window.location.href.split('?')[0];
        cargarDatosAjax(urlLimpia);
        $('#modalBusquedaAvanzada').modal('hide');
      };

      <?php if ($mostrar_modal_exito) : ?>
        $('#modalExito').modal('show');
      <?php elseif ($mostrar_modal_error) : ?>
        $('#modalError').modal('show');
      <?php endif; ?>
    });
  </script>