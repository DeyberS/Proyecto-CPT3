<?php
include('includes/headerNav2.php');
verificarAcceso('Ver Lotes'); // O 'Gestionar Vacunas'
?>

<head>
  <title>Lotes de Vacunas | Listado</title>
</head>

<div class="content-wrapper">
  <?php
  include('../../cfg/conexion.php');
  
  // SIMULACIÓN: Cuando tengas la BD real, descomenta esto.
  /*
  $sqlLote = ("SELECT * FROM lotes_medicamentos WHERE estatus = 1 ORDER BY Id ASC");
  $queryData   = mysqli_query($conexion, $sqlLote);
  $total_lotes = mysqli_num_rows($queryData);
  */
  $total_lotes = 5; // Simulado
  ?>

  <section class="content-header">
    <h1>
      Lotes de Vacunas (<?php echo $total_lotes; ?>)
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="#"><i class="fa fa-medkit"></i>Inmunización</a></li>
      <li><a href="#"><i class="fa fa-archive"></i>Lotes</a></li>
      <li class="active"><a href="#"><i class="fa fa-list"></i>Listado</a></li>
    </ol>
  </section>

  <section class="content">
    <div class="toolbar-container">
      <p class="pull-right" style="width:5px;"></p>
      <?php if (in_array('Ver papelera de lotes', $_SESSION["permisos"])) : ?>
        <a href="papelera/vacunacion_lotes_papelera_listado.php" class="btn-sm btn-primary pull-right" style="background-color: gray;"> Papelera </a>
      <?php endif; ?>
      <p class="pull-right" style="width:5px;"></p>
      <?php if (in_array('Generar Reportes de Lotes', $_SESSION["permisos"])) : ?>
        <a href="#" class="btn-sm btn-info pull-right reporte"><i class="fa fa-book"></i> Generar Reporte </a>
      <?php endif; ?>
      <p class="pull-right" style="width:5px;"></p>
      <?php if (in_array('Crear Lotes', $_SESSION["permisos"])) : ?>
        <a href="vacunacion_lotes_agregar.php" class="btn-sm btn-success pull-right"><i class="fa fa-plus"></i> Añadir Nuevo Lote </a>
      <?php endif; ?>
      
      <input type="text" id="buscar" name="buscar" class="form-control pull-left" placeholder="Buscar lote o vacuna..." style="border-radius:0; height:10%; width:250px; display:inline-block;" value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>" autocomplete="off">
      
      <p class="pull-right" style="width:5px;"></p>
      <span data-toggle="tooltip" data-placement="right" title="Filtros avanzados de búsqueda de lotes">
        <button type="button" class="btn-sm btn-primary btn-sm pull-left" data-toggle="modal" data-target="#modalBusquedaAvanzadaLotes">
          <i class="fa fa-filter"><img src="../../recursos/imagenes/iconos/filtrar.png" style="width:10px; height:10px; filter:invert(1);" title="filtrar"></i>
        </button>
      </span>
    </div>
    <br><br>
    
    <div id="contenedorTabla">
      <table class="table table-sm table-hover mt-4" width="100%" height="20" id="t_user">
        <thead class="table-dark">
          <tr>
            <th>Nombre del Lote</th>
            <th>F. Fabricación</th>
            <th>F. Vencimiento</th>
            <th>Vacuna / Biológico</th>
            <th>Stock (Dosis)</th>
            <th>Temp. Almacenamiento</th>
            <th>Estado</th>
            <?php if (in_array('Gestionar acciones de lotes', $_SESSION["permisos"])) : ?>
              <th>Acciones</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody class="tbody" width="100%" style="font-size: 12px;">
          
          <!-- ======================================================= -->
          <!-- SIMULACIÓN DE DATOS (BORRAR CUANDO CONECTES LA BD REAL) -->
          <!-- ======================================================= -->
          
          <!-- LOTE 1: DISPONIBLE -->
          <tr>
            <td><span class="text-row text-white">VPI-001</span></td>
            <td><span class="text-row text-white">01/01/2025</span></td>
            <td><span class="text-row text-white">01/01/2026</span></td>
            <td><span class="text-row text-white">Antipoliomielítica (VPI)</span></td>
            <td><span class="text-row text-white">150</span></td>
            <td><span class="text-row text-white">2°C a 8°C</span></td>
            <td><span class="badge bg-green" style="font-size:11px;">DISPONIBLE</span></td>
            <td>
              <a href="#" class="btn-sm btn-warning" title="Editar"><img src="../../recursos/imagenes/iconos/editar.png" style="width:15px; height:15px;"></a>
              <a href="#" data-id="1" class="btn-sm btn-danger btn-desactivar" title="Desactivar"><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></a>
            </td>
          </tr>

          <!-- LOTE 2: DISPONIBLE -->
          <tr>
            <td><span class="text-row text-white">SRP-991</span></td>
            <td><span class="text-row text-white">10/05/2025</span></td>
            <td><span class="text-row text-white">10/05/2026</span></td>
            <td><span class="text-row text-white">Antisarampión (SRP)</span></td>
            <td><span class="text-row text-white">80</span></td>
            <td><span class="text-row text-white">2°C a 8°C</span></td>
            <td><span class="badge bg-green" style="font-size:11px;">DISPONIBLE</span></td>
            <td>
              <a href="#" class="btn-sm btn-warning" title="Editar"><img src="../../recursos/imagenes/iconos/editar.png" style="width:15px; height:15px;"></a>
              <a href="#" data-id="2" class="btn-sm btn-danger btn-desactivar" title="Desactivar"><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></a>
            </td>
          </tr>

          <!-- LOTE 3: CUARENTENA (Cadena de frío rota) -->
          <tr>
            <td><span class="text-row text-white">BCG-003</span></td>
            <td><span class="text-row text-white">01/01/2025</span></td>
            <td><span class="text-row text-white">15/11/2026</span></td>
            <td><span class="text-row text-white">Vacuna BCG</span></td>
            <td><span class="text-row text-white">200</span></td>
            <td><span class="text-row text-white">2°C a 8°C</span></td>
            <td><span class="badge bg-yellow" style="font-size:11px;">CUARENTENA</span></td>
            <td>
              <a href="#" class="btn-sm btn-warning" title="Editar"><img src="../../recursos/imagenes/iconos/editar.png" style="width:15px; height:15px;"></a>
              <a href="#" data-id="3" class="btn-sm btn-danger btn-desactivar" title="Desactivar"><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></a>
            </td>
          </tr>

          <!-- LOTE 4: VENCIDO -->
          <tr>
            <td><span class="text-row text-white">VPI-002</span></td>
            <td><span class="text-row text-white">01/06/2024</span></td>
            <td><span class="text-row text-white">01/12/2024</span></td>
            <td><span class="text-row text-white">Antipoliomielítica (VPI)</span></td>
            <td><span class="text-row text-white">50</span></td>
            <td><span class="text-row text-white">2°C a 8°C</span></td>
            <td><span class="badge label-default" style="font-size:11px; background-color: #777;">VENCIDO</span></td>
            <td>
              <a href="#" class="btn-sm btn-warning" title="Editar"><img src="../../recursos/imagenes/iconos/editar.png" style="width:15px; height:15px;"></a>
              <a href="#" data-id="4" class="btn-sm btn-danger btn-desactivar" title="Desactivar"><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></a>
            </td>
          </tr>

          <!-- LOTE 5: DAÑADO -->
          <tr>
            <td><span class="text-row text-white">SRP-881</span></td>
            <td><span class="text-row text-white">15/01/2024</span></td>
            <td><span class="text-row text-white">15/11/2024</span></td>
            <td><span class="text-row text-white">Antisarampión (SRP)</span></td>
            <td><span class="text-row text-white">30</span></td>
            <td><span class="text-row text-white">2°C a 8°C</span></td>
            <td><span class="badge bg-crimson" style="font-size:11px;">DAÑADO</span></td>
            <td>
              <a href="#" class="btn-sm btn-warning" title="Editar"><img src="../../recursos/imagenes/iconos/editar.png" style="width:15px; height:15px;"></a>
              <a href="#" data-id="5" class="btn-sm btn-danger btn-desactivar" title="Desactivar"><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></a>
            </td>
          </tr>
          <!-- FIN DE LA SIMULACIÓN -->
          
        </tbody>
      </table>
    </div>

    <!-- PAGINACIÓN (Mantenida) -->
    <nav id="contenedorPaginacion" aria-label="Page navigation">
      <ul class="pagination">
        <li class="disabled"><a href="#">&laquo;&laquo;</a></li>
        <li class="active"><a href="#">1</a></li>
        <li><a href="#">&raquo;</a></li>
        <li><a href="#">&raquo;&raquo;</a></li>
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

  <!-- MODAL EXITO -->
  <div class="modal fade" id="modalExito" tabindex="-1" role="dialog" aria-labelledby="modalExitoLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-green">
          <h5 class="modal-title" id="modalExitoLabel">Operación Exitosa</h5>
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

  <!-- MODAL ERROR -->
  <div class="modal fade" id="modalError" tabindex="-1" role="dialog" aria-labelledby="modalErrorLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h5 class="modal-title" id="modalErrorLabel">Error en la Operación</h5>
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

  <!-- MODAL DESACTIVAR -->
  <div class="modal fade" id="DesactivarLote" tabindex="-1" role="dialog" aria-labelledby="DesactivarLoteLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h4 class="modal-title" id="DesactivarLoteLabel">Confirmación de Desactivación</h4>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea desactivar este lote de vacunas? Esta acción solo se puede revertir en la papelera.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <a id="desactivar" href="#" class="btn btn-danger">Aceptar</a>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL FILTROS AVANZADOS -->
  <div class="modal fade" id="modalBusquedaAvanzadaLotes" tabindex="-1" role="dialog" aria-labelledby="modalFiltrosLabel">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <form id="formBusquedaAvanzada" method="GET" action="">
          <div class="modal-header bg-primary">
            <h4 class="modal-title" id="modalFiltrosLabel"><i class="fa fa-filter"></i> Filtros Avanzados de Lotes de Vacunas</h4>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-4 form-group">
                <label>Estado del Lote</label>
                <select name="f_estado" class="form-control">
                  <option value="">Todos</option>
                  <option value="Disponible">Disponible</option>
                  <option value="Cuarentena">Cuarentena</option>
                  <option value="Vencido">Vencido</option>
                  <option value="Retirado">Retirado</option>
                  <option value="Dañado">Dañado</option>
                </select>
              </div>
              <div class="col-md-4 form-group">
                <label>Proveedor / Donante</label>
                <input type="text" name="f_proveedor" class="form-control" placeholder="Nombre del proveedor...">
              </div>
              <div class="col-md-4 form-group">
                <label>Búsqueda General</label>
                <input type="text" name="buscar" id="buscar_modal" class="form-control" placeholder="Lote o Vacuna...">
              </div>
            </div>
            <div class="row">
              <div class="col-md-3 form-group">
                <label>F. Fabricación Desde</label>
                <input type="date" name="f_fab_desde" max="<?php echo date('Y-m-d'); ?>" class="form-control">
              </div>
              <div class="col-md-3 form-group">
                <label>F. Fabricación Hasta</label>
                <input type="date" name="f_fab_hasta" min="<?php echo date('Y-m-d'); ?>" class="form-control">
              </div>
              <div class="col-md-3 form-group">
                <label>F. Vencimiento Desde</label>
                <input type="date" name="f_venc_desde" max="<?php echo date('Y-m-d'); ?>" class="form-control">
              </div>
              <div class="col-md-3 form-group">
                <label>F. Vencimiento Hasta</label>
                <input type="date" name="f_venc_hasta" min="<?php echo date('Y-m-d'); ?>" class="form-control">
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 form-group">
                <label>Stock Actual Mínimo (Dosis)</label>
                <input type="number" name="f_stock_min" class="form-control" min="0" placeholder="Ej: 0">
              </div>
              <div class="col-md-6 form-group">
                <label>Stock Actual Máximo (Dosis)</label>
                <input type="number" name="f_stock_max" class="form-control" min="0" placeholder="Ej: 500">
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

  <!-- MODAL REPORTES -->
  <div class="modal fade" id="ModalReporteLote" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h4 class="modal-title" id="myModalLabel"><i class="fa fa-archive"></i> Reportes de Lotes de Vacunas</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Seleccione el tipo de reporte:</label>
            <select class="form-control" id="tipo_reporte_lote">
              <option value="todos">Inventario General de Lotes</option>
              <option value="vencidos">Lotes Vencidos (Fecha pasada)</option>
              <option value="proximos_vencer">Próximos a Vencer (30-60 días)</option>
              <option value="stock_bajo">Alerta de Stock Bajo (Menos de 20 dosis)</option>
              <option value="activos">Solo Lotes Activos</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-primary" id="btnEjecutarReporteLote">Generar PDF</button>
        </div>
      </div>
    </div>
  </div>

  <?php include('includes/footer.php'); ?>

  <script>
    $(document).ready(function() {
      $('.reporte').on('click', function(e) {
        e.preventDefault();
        $('#ModalReporteLote').modal('show');
      });

      $('#btnEjecutarReporteLote').on('click', function() {
        var tipo = $('#tipo_reporte_lote').val();
        // window.open('../../cfg/reportes/generar_pdf_lotes_vacunas.php?tipo=' + tipo, '_blank');
        alert("Generando reporte: " + tipo + " (Función pendiente de backend)");
        $('#ModalReporteLote').modal('hide');
      });

      $(document).on('click', '.btn-desactivar', function(e) {
        e.preventDefault();
        var IdLote = $(this).data('id');
        var urlDesactivar = "../../cfg/desactivar/desactivar_lote_vacuna.php?Id=" + IdLote;
        $('#desactivar').attr('href', urlDesactivar);
        $('#DesactivarLote').modal('show');
      });

      // Búsqueda en vivo (Filtra la tabla visualmente)
      let timer;
      $('#buscar').on('keyup', function() {
        clearTimeout(timer);
        let query = $(this).val().toLowerCase();
        $('#buscar_modal').val(query);

        timer = setTimeout(function() {
          $("#t_user tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(query) > -1)
          });
        }, 400);
      });

      $('#formBusquedaAvanzada').on('submit', function(e) {
        e.preventDefault();
        // SIMULACIÓN: En lugar de AJAX, filtramos la tabla visualmente
        let estado = $('select[name="f_estado"]').val().toLowerCase();
        let proveedor = $('input[name="f_proveedor"]').val().toLowerCase();
        let buscar = $('#buscar_modal').val().toLowerCase();
        
        $("#t_user tbody tr").filter(function() {
          let texto = $(this).text().toLowerCase();
          let match = true;
          if (estado && !texto.includes(estado)) match = false;
          if (proveedor && !texto.includes(proveedor)) match = false;
          if (buscar && !texto.includes(buscar)) match = false;
          $(this).toggle(match);
        });
        
        $('#modalBusquedaAvanzadaLotes').modal('hide');
      });

      window.limpiarFiltrosAjax = function() {
        $('#formBusquedaAvanzada')[0].reset();
        $('#buscar').val('');
        $('#buscar_modal').val('');
        $("#t_user tbody tr").show();
        $('#modalBusquedaAvanzadaLotes').modal('hide');
      };

      <?php if ($mostrar_modal_exito) : ?>
        $('#modalExito').modal('show');
      <?php elseif ($mostrar_modal_error) : ?>
        $('#modalError').modal('show');
      <?php endif; ?>
    });
  </script>
</div>