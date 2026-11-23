<?php
include('includes/headerNav2.php');
verificarAcceso('Gestionar Vacunas');
?>

<head>
  <title>Inmunización | Control de Cuarto Frío</title>
</head>

<div class="content-wrapper">
  <?php
  // ==========================================
  // CONFIGURACIÓN DE PAGINACIÓN Y FILTROS
  // ==========================================
  $registros_por_pagina = 9;
  $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
  $inicio = ($pagina_actual - 1) * $registros_por_pagina;

  $busqueda      = isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : '';
  $f_desde       = isset($_GET['f_desde']) ? htmlspecialchars($_GET['f_desde']) : '';
  $f_hasta       = isset($_GET['f_hasta']) ? htmlspecialchars($_GET['f_hasta']) : '';
  $f_turno       = isset($_GET['f_turno']) ? htmlspecialchars($_GET['f_turno']) : '';
  $f_alerta      = isset($_GET['f_alerta']) ? htmlspecialchars($_GET['f_alerta']) : '';

  // Simulación de la bitácora (en producción vendría de la BD)
  $bitacora_simulada = [
    ['id' => 1, 'fecha' => '01/06/2026', 'hora' => '08:00 AM', 'temp' => 4.0, 'turno' => 'Mañana', 'cuidador' => 'Enf. María Rodríguez', 'procedencia' => 'Apertura', 'obs' => 'Nevera en óptimas condiciones'],
    ['id' => 2, 'fecha' => '01/06/2026', 'hora' => '11:30 AM', 'temp' => 4.2, 'turno' => 'Mañana', 'cuidador' => 'Enf. María Rodríguez', 'procedencia' => 'Control', 'obs' => 'Sin novedades'],
    ['id' => 3, 'fecha' => '01/06/2026', 'hora' => '04:00 PM', 'temp' => 9.5, 'turno' => 'Tarde', 'cuidador' => 'Enf. José Pérez', 'procedencia' => 'Cierre', 'obs' => '¡ALERTA! Corte eléctrico por 15 min'],
    ['id' => 4, 'fecha' => '31/05/2026', 'hora' => '08:00 AM', 'temp' => 3.8, 'turno' => 'Mañana', 'cuidador' => 'Enf. Ana Gómez', 'procedencia' => 'Apertura', 'obs' => 'Inicio de jornada'],
    ['id' => 5, 'fecha' => '31/05/2026', 'hora' => '04:00 PM', 'temp' => 4.5, 'turno' => 'Tarde', 'cuidador' => 'Enf. José Pérez', 'procedencia' => 'Cierre', 'obs' => 'Fin de jornada'],
  ];

  // Aplicar filtros (simulados)
  $registros_filtrados = array_filter($bitacora_simulada, function ($r) use ($busqueda, $f_desde, $f_hasta, $f_turno, $f_alerta) {
    if ($busqueda != '' && stripos($r['cuidador'], $busqueda) === false) return false;
    if ($f_desde != '' && strtotime($r['fecha']) < strtotime($f_desde)) return false;
    if ($f_hasta != '' && strtotime($r['fecha']) > strtotime($f_hasta)) return false;
    if ($f_turno != '' && $r['turno'] !== $f_turno) return false;
    if ($f_alerta == 'si' && ($r['temp'] >= 2.0 && $r['temp'] <= 8.0)) return false;
    return true;
  });

  $total_registros = count($registros_filtrados);
  $total_paginas = ceil($total_registros / $registros_por_pagina);
  $registros_pagina = array_slice($registros_filtrados, $inicio, $registros_por_pagina);
  ?>

  <style>
    /* Estilos coherentes con el listado de farmacia */
    .btn-disabled {
      background-color: gray;
      opacity: 0.6;
      pointer-events: none;
    }

    .temp-ok {
      color: #27ae60;
      font-weight: bold;
    }

    .temp-alert {
      color: #e74c3c;
      font-weight: bold;
    }

    .fila-alerta {
      background-color: #fdedec !important;
    }

    .badge-turno {
      font-size: 0.85em;
      padding: 4px 8px;
    }

    .kardex-footer-bar {
      background-color: #222;
      color: #fff;
      height: 30px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: fixed;
      bottom: 0;
      z-index: 100;
      box-shadow: 0 -4px 10px rgba(0, 0, 0, 0.4);
      width: 83%;
    }

    #pagination_container .pagination {
      margin: 0;
    }

    #pagination_container .pagination>li>a {
      background-color: #333 !important;
      border-color: #444 !important;
      color: #fff !important;
    }

    #pagination_container .pagination>.active>a {
      background-color: #222 !important;
      border-color: #222 !important;
    }
  </style>

  <section class="content-header">
    <h1>
      Control de Cuarto Frío (<?php echo $total_registros; ?> Registros)
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i> Inicio</a></li>
      <li><a href="#"><i class="fa fa-snowflake-o"></i> Inmunización</a></li>
      <li class="active"><i class="fa fa-thermometer-half"></i> Cuarto Frío</li>
    </ol>
  </section>

  <section class="content">
    <div class="toolbar-container">
      <!-- Botones de acción -->
      <a href="#" class="btn-sm btn-primary pull-right" data-toggle="modal" data-target="#modalRegistrarTemp">Registrar Temperatura</a>
      <p class="pull-right" style="width:5px;"></p>
      <a href="#" class="btn-sm btn-info pull-right" data-toggle="modal" data-target="#modalCambiarTurno">Cambio de Turno</a>
      <p class="pull-right" style="width:5px;"></p>
      <?php if (in_array('Generar Reportes de Cuarto Frío', $_SESSION["permisos"])) : ?>
        <a href="#" class="btn-sm btn-info pull-right reporte">Generar Reporte</a>
      <?php endif; ?>

      <!-- Buscador rápido -->
      <div class="pull-left form-inline">
        <form method="GET" action="" id="formBusquedaRapida">
          <input type="text" id="buscar" name="buscar" class="form-control" placeholder="Buscar por cuidador..." value="<?php echo $busqueda; ?>" style="border-radius:0; height:10%; width:250px; display:inline-block;" autocomplete="off">
        </form>
      </div>
      <p class="pull-right" style="width:5px;"></p>
      <!-- Botón de filtros avanzados -->
      <span data-toggle="tooltip" data-placement="top" title="Filtrar búsqueda">
        <button type="button" class="btn-sm btn-primary pull-left" data-toggle="modal" data-target="#modalBusquedaAvanzada">
          <i class="fa fa-filter"><img src="../../recursos/imagenes/iconos/filtrar.png" style="width:15px; height:20px; filter:invert(1);" title="Filtrar bitácora"></i>
        </button>
      </span>
    </div>
    <br><br>

    <div id="contenedorTabla">
      <table class="table table-sm table-hover mt-4" width="100%" id="t_user">
        <thead class="table-dark">
          <tr>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Temperatura (°C)</th>
            <th>Turno</th>
            <th>Cuidador / Responsable</th>
            <th>Procedencia o Destino</th>
            <th>Observaciones</th>
            <?php if (in_array('Gestionar acciones de cuarto frío', $_SESSION["permisos"])) : ?>
              <th>Acciones</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody class="tbody" style="font-size: 12px;">
          <?php if (empty($registros_pagina)) : ?>
            <tr>
              <td colspan="8" class="text-center" style="padding: 30px; font-size: 16px; color: #666;">
                <i class="fa fa-search text-muted fa-2x"></i><br><br>
                No se encontraron registros con los filtros aplicados.
              </td>
            </tr>
          <?php else : ?>
            <?php foreach ($registros_pagina as $row) :
              $temp_ok = ($row['temp'] >= 2.0 && $row['temp'] <= 8.0);
              $clase_temp = $temp_ok ? 'temp-ok' : 'temp-alert';
              $clase_fila = !$temp_ok ? 'fila-alerta' : '';
            ?>
              <tr class="<?php echo $clase_fila; ?>">
                <td><small class="text-row text-white"><?php echo $row['fecha']; ?></small></td>
                <td><small class="text-row text-white"><?php echo $row['hora']; ?></small></td>
                <td>
                  <small class="<?php echo $clase_temp; ?>">
                    <?php echo $row['temp']; ?> °C
                  </small>
                </td>
                <td>
                  <span class="badge badge-turno <?php echo ($row['turno'] == 'Mañana') ? 'bg-blue' : (($row['turno'] == 'Tarde') ? 'bg-orange' : 'bg-purple'); ?>">
                    <?php echo $row['turno']; ?>
                  </span>
                </td>
                <td><small class="text-row text-white"><?php echo htmlspecialchars($row['cuidador']); ?></small></td>
                <td><small class="text-row text-white"><?php echo htmlspecialchars($row['procedencia']); ?></small></td>
                <td><small class="text-row text-white"><?php echo htmlspecialchars($row['obs']); ?></small></td>
                <?php if (in_array('Gestionar acciones de cuarto frío', $_SESSION["permisos"])) : ?>
                  <td>
                    <?php if (in_array('Ver Registros de Cuarto Frío', $_SESSION["permisos"])) : ?>
                      <a href="vacunacion_cuarto_frio_ver.php?id=<?php echo $row['id']; ?>" class="btn-sm btn-info" title="Ver"><img src="../../recursos/imagenes/iconos/info.png" style="width:15px; height:15px;"></a>
                    <?php endif; ?>
                    <?php if (in_array('Editar Registros de Cuarto Frío', $_SESSION["permisos"])) : ?>
                      <a href="vacunacion_cuarto_frio_editar.php?id=<?php echo $row['id']; ?>" class="btn-sm btn-warning" title="Editar"><i class="fa fa-pencil"></i></a>
                    <?php endif; ?>
                  </td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Paginación -->
    <nav id="contenedorPaginacion" aria-label="Page navigation">
      <ul class="pagination">
        <?php
        $query_string = '';
        if ($busqueda != '') $query_string .= "&buscar=" . urlencode($busqueda);
        if ($f_desde != '') $query_string .= "&f_desde=" . urlencode($f_desde);
        if ($f_hasta != '') $query_string .= "&f_hasta=" . urlencode($f_hasta);
        if ($f_turno != '') $query_string .= "&f_turno=" . urlencode($f_turno);
        if ($f_alerta != '') $query_string .= "&f_alerta=" . urlencode($f_alerta);

        if ($pagina_actual > 1) : ?>
          <li><a href="?pagina=1<?php echo $query_string; ?>">&laquo;&laquo;</a></li>
          <li><a href="?pagina=<?php echo ($pagina_actual - 1) . $query_string; ?>">&laquo;</a></li>
        <?php endif;

        $rango = 1;
        $inicio_ventana = max(1, $pagina_actual - $rango);
        $fin_ventana = min($total_paginas, $pagina_actual + $rango);

        for ($i = $inicio_ventana; $i <= $fin_ventana; $i++) : ?>
          <li class="<?php echo ($i == $pagina_actual) ? 'active' : ''; ?>">
            <a href="?pagina=<?php echo $i . $query_string; ?>"><?php echo $i; ?></a>
          </li>
        <?php endfor;

        if ($pagina_actual < $total_paginas) : ?>
          <li><a href="?pagina=<?php echo ($pagina_actual + 1) . $query_string; ?>">&raquo;</a></li>
          <li><a href="?pagina=<?php echo $total_paginas . $query_string; ?>">&raquo;&raquo;</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  </section>

  <!-- ========================================== -->
  <!-- MODAL 1: REGISTRAR TEMPERATURA             -->
  <!-- ========================================== -->
  <div class="modal fade" id="modalRegistrarTemp" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h4 class="modal-title"><i class="fa fa-thermometer-half"></i> Registrar Temperatura</h4>
        </div>
        <form action="../../cfg/agregar/agregar_temp_cuarto_frio.php" method="POST">
          <div class="modal-body">
            <div class="form-group">
              <label>Temperatura Actual (°C) (*):</label>
              <input type="number" step="0.1" name="temperatura" class="form-control" min="0" max="20" placeholder="Ej: 4.5" required>
            </div>
            <div class="form-group">
              <label>Turno (*):</label>
              <select name="turno" class="form-control" required>
                <option value="Mañana">Mañana</option>
                <option value="Tarde">Tarde</option>
                <option value="Noche">Noche</option>
              </select>
            </div>
            <div class="form-group">
              <label>Observaciones:</label>
              <textarea name="observaciones" class="form-control" rows="2" placeholder="Ej: Control rutinario..."></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Guardar Registro</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- ========================================== -->
  <!-- MODAL 2: CAMBIAR TURNO / CUIDADOR          -->
  <!-- ========================================== -->
  <div class="modal fade" id="modalCambiarTurno" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-info">
          <h4 class="modal-title"><i class="fa fa-exchange"></i> Cambio de Turno</h4>
        </div>
        <form action="../../cfg/agregar/agregar_cambio_turno.php" method="POST">
          <div class="modal-body">
            <p class="text-muted">Registre la entrega y recepción del cuarto frío.</p>
            <div class="row">
              <div class="col-sm-6">
                <div class="form-group">
                  <label>Turno Saliente:</label>
                  <input type="text" class="form-control" value="Mañana" readonly style="background:#eee;">
                </div>
                <div class="form-group">
                  <label>Cuidador Saliente:</label>
                  <input type="text" class="form-control" value="Enf. María Rodríguez" readonly style="background:#eee;">
                </div>
                <div class="form-group">
                  <label>Temp. al Entregar (°C):</label>
                  <input type="number" step="0.1" name="temp_entrega" class="form-control" required>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="form-group">
                  <label>Turno Entrante (*):</label>
                  <select name="turno_entrante" class="form-control" required>
                    <option value="Tarde">Tarde</option>
                    <option value="Noche">Noche</option>
                    <option value="Mañana">Mañana</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>Cuidador Entrante (*):</label>
                  <select name="id_cuidador_entrante" class="form-control" required>
                    <option value="">--- Seleccione ---</option>
                    <option value="1">Enf. José Pérez</option>
                    <option value="2">Enf. Ana Gómez</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>Temp. al Recibir (°C):</label>
                  <input type="number" step="0.1" name="temp_recibe" class="form-control" required>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-info"><i class="fa fa-save"></i> Registrar Cambio</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- ========================================== -->
  <!-- MODAL 3: FILTROS AVANZADOS                 -->
  <!-- ========================================== -->
  <div class="modal fade" id="modalBusquedaAvanzada" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <form id="formBusquedaAvanzada" method="GET" action="">
          <div class="modal-header bg-primary">
            <h4 class="modal-title"><i class="fa fa-filter"></i> Filtros de Bitácora</h4>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-4 form-group">
                <label>Fecha Desde</label>
                <input type="date" name="f_desde" class="form-control" max="<?php echo date('Y-m-d'); ?>" value="<?php echo $f_desde; ?>">
              </div>
              <div class="col-md-4 form-group">
                <label>Fecha Hasta</label>
                <input type="date" name="f_hasta" class="form-control" min="<?php echo date('Y-m-d'); ?>" value="<?php echo $f_hasta; ?>">
              </div>
              <div class="col-md-4 form-group">
                <label>Turno</label>
                <select name="f_turno" class="form-control">
                  <option value="">Todos</option>
                  <option value="Mañana" <?php echo ($f_turno == 'Mañana') ? 'selected' : ''; ?>>Mañana</option>
                  <option value="Tarde" <?php echo ($f_turno == 'Tarde') ? 'selected' : ''; ?>>Tarde</option>
                  <option value="Noche" <?php echo ($f_turno == 'Noche') ? 'selected' : ''; ?>>Noche</option>
                </select>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 form-group">
                <label>¿Solo mostrar alertas? (Temp. fuera de rango)</label>
                <select name="f_alerta" class="form-control">
                  <option value="">No, mostrar todo</option>
                  <option value="si" <?php echo ($f_alerta == 'si') ? 'selected' : ''; ?>>Sí, solo alertas (> 8°C o < 2°C)</option> </select> </div> </div> </div> <div class="modal-footer">
                      <button type="button" class="btn btn-second pull-left" onclick="limpiarFiltrosAjax()">Limpiar Filtros</button>
                      <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
                      <button type="submit" class="btn btn-success"><i class="fa fa-search"></i> Aplicar Búsqueda</button>
              </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include('includes/footer.php'); ?>

<script>
  $(document).ready(function() {
    // === AJAX para búsqueda y filtros ===
    window.cargarDatosAjax = function(url) {
      $('.tbody').css('opacity', '0.4');
      $.get(url, function(data) {
        var htmlParsed = $(data);
        $('.tbody').html(htmlParsed.find('.tbody').html()).css('opacity', '1');
        $('nav[aria-label="Page navigation"]').html(htmlParsed.find('nav[aria-label="Page navigation"]').html());
        window.history.pushState(null, '', url);
      }).fail(function() {
        alert("Error de conexión al aplicar filtros.");
        $('.tbody').css('opacity', '1');
      });
    };

    let timer;
    $('#buscar').on('keyup', function() {
      clearTimeout(timer);
      let query = $(this).val();
      timer = setTimeout(function() {
        var url = 'vacunacion_cuarto_frio.php?buscar=' + encodeURIComponent(query);
        cargarDatosAjax(url);
      }, 400);
    });

    $('#formBusquedaRapida').on('submit', function(e) {
      e.preventDefault();
    });

    $('#formBusquedaAvanzada').on('submit', function(e) {
      e.preventDefault();
      var url = 'vacunacion_cuarto_frio.php?' + $(this).serialize();
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
  });
</script>