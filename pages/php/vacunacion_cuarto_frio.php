<?php
include('includes/headerNav2.php');
verificarAcceso('Gestionar Vacunas'); 
?>

<head>
  <title>Inmunización | Control de Cuarto Frío</title>
</head>

<style>
  /* Estilos copiados del Kardex de Inventario */
  .table-kardex thead {
    background-color: #f4f4f4;
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

  /* Estilos para el estado de la temperatura */
  .temp-ok { color: #27ae60; font-weight: bold; }
  .temp-alert { color: #e74c3c; font-weight: bold; }
  .fila-alerta { background-color: #fdedec !important; }
</style>

<div class="content-wrapper">
  <?php
  // ==========================================
  // SIMULACIÓN DE DATOS (TEMPLATE)
  // ==========================================
  $registros_por_pagina = 10;
  $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
  $inicio = ($pagina_actual - 1) * $registros_por_pagina;
  
  $busqueda = isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : '';
  $f_desde = isset($_GET['f_desde']) ? htmlspecialchars($_GET['f_desde']) : '';
  $f_hasta = isset($_GET['f_hasta']) ? htmlspecialchars($_GET['f_hasta']) : '';
  $f_turno = isset($_GET['f_turno']) ? htmlspecialchars($_GET['f_turno']) : '';
  $f_alerta = isset($_GET['f_alerta']) ? htmlspecialchars($_GET['f_alerta']) : '';

  $filtros_aplicados = ($busqueda != '' || $f_desde != '' || $f_hasta != '' || $f_turno != '' || $f_alerta != '');
  $mostrar_vacio = !$filtros_aplicados;

  // Simulamos la bitácora (Kardex de temperatura)
  $bitacora_simulada = [
    ['fecha' => '01/06/2026', 'hora' => '08:00 AM', 'temp' => 4.0, 'turno' => 'Mañana', 'cuidador' => 'Enf. María Rodríguez', 'procedencia' => 'Apertura', 'obs' => 'Nevera en óptimas condiciones'],
    ['fecha' => '01/06/2026', 'hora' => '11:30 AM', 'temp' => 4.2, 'turno' => 'Mañana', 'cuidador' => 'Enf. María Rodríguez', 'procedencia' => 'Control', 'obs' => 'Sin novedades'],
    ['fecha' => '01/06/2026', 'hora' => '04:00 PM', 'temp' => 9.5, 'turno' => 'Tarde', 'cuidador' => 'Enf. José Pérez', 'procedencia' => 'Cierre', 'obs' => '¡ALERTA! Corte eléctrico por 15 min'],
    ['fecha' => '31/05/2026', 'hora' => '08:00 AM', 'temp' => 3.8, 'turno' => 'Mañana', 'cuidador' => 'Enf. Ana Gómez', 'procedencia' => 'Apertura', 'obs' => 'Inicio de jornada'],
    ['fecha' => '31/05/2026', 'hora' => '04:00 PM', 'temp' => 4.5, 'turno' => 'Tarde', 'cuidador' => 'Enf. José Pérez', 'procedencia' => 'Cierre', 'obs' => 'Fin de jornada'],
  ];

  $total_registros = count($bitacora_simulada);
  $total_paginas = ceil($total_registros / $registros_por_pagina);
  ?>

  <section class="content-header">
    <h1>Control de Cuarto Frío (Bitácora)</h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i> Inicio</a></li>
      <li><a href="#"><i class="fa fa-snowflake-o"></i> Inmunización</a></li>
      <li class="active"><i class="fa fa-thermometer-half"></i> Cuarto Frío</li>
    </ol>
  </section>

  <section class="content">
    <div class="box box-primary">
      <div class="box-header with-border">
        <!-- Botones superiores -->
        <button type="button" class="btn-sm btn-success pull-right" data-toggle="modal" data-target="#modalRegistrarTemp">
          <i class="fa fa-plus"></i> Registrar Temperatura
        </button>
        
        <p class="pull-right" style="width:5px;"></p>
        
        <button type="button" class="btn-sm btn-warning pull-right" data-toggle="modal" data-target="#modalCambiarTurno">
          <i class="fa fa-exchange"></i> Cambio de Turno
        </button>

        <!-- Buscador rápido -->
        <div class="pull-left form-inline">
          <form method="GET" action="" id="formBusquedaRapida">
            <input type="text" name="buscar" id="buscar" class="form-control" placeholder="Buscar por cuidador..." value="<?php echo $busqueda; ?>" style="border-radius:0; height:10%; width:250px; display:inline-block;" autocomplete="off">
          </form>
        </div>

        <p class="pull-right" style="width:5px;"></p>
        
        <!-- Botón de filtros avanzados -->
        <button type="button" class="btn-sm btn-primary pull-left" data-toggle="modal" data-target="#modalBusquedaAvanzada">
          <i class="fa fa-filter"><img src="../../recursos/imagenes/iconos/filtrar.png" style="width:10px; height:10px; filter:invert(1);"></i>
        </button>
      </div>
      <br><br>

      <div class="box-body">
        <div id="contenedorTabla">
          <table class="table table-sm table-hover mt-4" width="100%" id="t_user">
            <thead class="table-dark" style="background-color: #222; color: white; font-size: 12px;">
              <tr>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Temperatura (°C)</th>
                <th>Turno</th>
                <th>Cuidador / Responsable</th>
                <th>Procedencia o Destino</th>
                <th>Observaciones</th>
              </tr>
            </thead>
            <tbody id="tbody_kardex">
              <?php if ($mostrar_vacio) : ?>
                <tr>
                  <td colspan="7" class="text-center" style="padding: 30px; font-size: 16px; color: #666;">
                    <i class="fa fa-search text-muted fa-2x"></i><br><br>
                    Empiece a escribir o utilice los filtros para visualizar la bitácora.
                  </td>
                </tr>
              <?php else : ?>
                <?php foreach ($bitacora_simulada as $row) : 
                  $temp_ok = ($row['temp'] >= 2.0 && $row['temp'] <= 8.0);
                ?>
                  <tr class="<?php echo !$temp_ok ? 'fila-alerta' : ''; ?>">
                    <td><small class="text-row text-black"><?php echo $row['fecha']; ?></small></td>
                    <td><small class="text-row text-black"><?php echo $row['hora']; ?></small></td>
                    <td>
                      <small class="<?php echo $temp_ok ? 'temp-ok' : 'temp-alert'; ?>">
                        <?php echo $row['temp']; ?> °C
                      </small>
                    </td>
                    <td><small class="text-row text-black"><?php echo $row['turno']; ?></small></td>
                    <td><small class="text-row text-black"><?php echo $row['cuidador']; ?></small></td>
                    <td><small class="text-row text-black"><?php echo $row['procedencia']; ?></small></td>
                    <td><small class="text-row text-black"><?php echo $row['obs']; ?></small></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- FOOTER BAR (ESTILO KARDEX) -->
        <div class="kardex-footer-bar" id="kardex_footer_bar">
          <div id="pagination_container">
            <?php if (!$mostrar_vacio && $total_paginas > 0) : ?>
              <nav aria-label="Page navigation" style="text-align: left;">
                <ul class="pagination">
                  <li class="disabled"><a href="#">&laquo;</a></li>
                  <li class="active"><a href="#">1</a></li>
                  <li class="disabled"><a href="#">&raquo;</a></li>
                </ul>
              </nav>
            <?php endif; ?>
          </div>

          <div id="resumen_container" style="font-size: 15px;">
            <?php if (!$mostrar_vacio) : ?>
              <span style="margin-left: 15px;"><i class="fa fa-thermometer-half text-info"></i> Registros: <strong><?php echo $total_registros; ?></strong></span>
              <span style="margin-left: 15px; border-left: 1px solid #555; padding-left: 15px;"><i class="fa fa-exclamation-triangle text-danger"></i> Alertas (Temp. > 8°C): <strong style="color:#e74c3c;">1</strong></span>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </div>
  </section>
</div>

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
          <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Guardar Registro</button>
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
      <div class="modal-header bg-warning">
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
          <button type="submit" class="btn btn-warning"><i class="fa fa-save"></i> Registrar Cambio</button>
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
                <option value="si" <?php echo ($f_alerta == 'si') ? 'selected' : ''; ?>>Sí, solo alertas (> 8°C o < 2°C)</option>
              </select>
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

<?php include('includes/footer.php'); ?>

<script>
  $(document).ready(function() {
    // Lógica de AJAX (simulada aquí para el template)
    window.cargarDatosAjax = function(url) {
      $('#tbody_kardex').css('opacity', '0.4');
      // Simulamos la recarga
      setTimeout(function() {
        $('#tbody_kardex').css('opacity', '1');
        // window.history.pushState(null, '', url);
      }, 300);
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

    $('#formBusquedaAvanzada').on('submit', function(e) {
      e.preventDefault();
      var url = 'vacunacion_cuarto_frio.php?' + $(this).serialize();
      $('#modalBusquedaAvanzada').modal('hide');
      cargarDatosAjax(url);
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
</div>