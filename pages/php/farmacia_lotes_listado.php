<?php
include('includes/headerNav2.php');
verificarAcceso('Ver Lotes');
?>

<head>
  <title>Lotes | Listado</title>
</head>

<div class="content-wrapper">
  <?php
  include('../../cfg/conexion.php');
  $sqlLote = ("SELECT * FROM lotes_medicamentos WHERE estatus = 1 ORDER BY Id ASC");
  $queryData   = mysqli_query($conexion, $sqlLote);
  $total_lotes = mysqli_num_rows($queryData);
  ?>

  <section class="content-header">
    <h1>
      Lotes (<?php echo $total_lotes; ?>)
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="#"><i class="fa fa-user"></i>Lotes</a></li>
      <li><a href="#"><i class="fa fa-book"></i>Listado</a></li>
    </ol>
  </section>

  <section class="content">
    <div class="toolbar-container">
      <p class="pull-right" style="width:5px;"></p>
      <?php if (in_array('Ver papelera de lotes', $_SESSION["permisos"])) : ?>
        <a href="papelera/farmacia_lotes_papelera_listado.php" class="btn-sm btn-primary pull-right" style="background-color: gray;"> Papelera </a>
      <?php endif; ?>
      <p class="pull-right" style="width:5px;"></p>
      <?php if (in_array('Generar Reportes de Lotes', $_SESSION["permisos"])) : ?>
        <a href="#" class="btn-sm btn-info pull-right reporte"><i class="fa fa-book"></i> Generar Reporte </a>
      <?php endif; ?>
      <input type="text" id="buscar" name="buscar" class="form-control pull-left" placeholder="Escriba para buscar..." style="border-radius:0; height:10%; width:250px; display:inline-block;" value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>" style="width: 200px;" autocomplete="off">
      <p class="pull-right" style="width:5px;"></p>
      <span data-toggle="tooltip" data-placement="top" title="Filtrar búsqueda.">
        <button type="button" class="btn-sm btn-primary btn-sm pull-left" data-toggle="modal" data-target="#modalBusquedaAvanzadaLotes">
          <i><img src="../../recursos/imagenes/iconos/filtrar.png" style="width:15px; height:20px; filter:invert(1);" title="Filtrar lote"></i>
        </button>
      </span>
    </div>
    <br><br>
    <div id="contenedorTabla">
      <table class="table table-sm table-hover mt-4" width="100%" height="20" id="t_user">
        <thead class="table-dark">
          <th>Nombre del Lote</th>
          <th>Fecha de Fabricación</th>
          <th>Fecha de Vencimiento</th>
          <th>Medicamentos Asociados</th>
          <th>T. Medicamento (Total)</th>
          <th>Estado</th>
          <?php if (in_array('Gestionar acciones de lotes', $_SESSION["permisos"])) : ?>
            <th>Acciones</th>
          <?php endif; ?>
        </thead>
        <tbody class="tbody" width="100%" style="font-size: 12px;">
          <tr>
            <?php
            $registros_por_pagina = 14;
            $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
            $inicio = ($pagina_actual - 1) * $registros_por_pagina;

            $busqueda     = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : '';
            $f_estado     = isset($_GET['f_estado']) ? mysqli_real_escape_string($conexion, $_GET['f_estado']) : '';
            $f_fab_desde  = isset($_GET['f_fab_desde']) ? mysqli_real_escape_string($conexion, $_GET['f_fab_desde']) : '';
            $f_fab_hasta  = isset($_GET['f_fab_hasta']) ? mysqli_real_escape_string($conexion, $_GET['f_fab_hasta']) : '';
            $f_venc_desde = isset($_GET['f_venc_desde']) ? mysqli_real_escape_string($conexion, $_GET['f_venc_desde']) : '';
            $f_venc_hasta = isset($_GET['f_venc_hasta']) ? mysqli_real_escape_string($conexion, $_GET['f_venc_hasta']) : '';
            $f_proveedor  = isset($_GET['f_proveedor']) ? mysqli_real_escape_string($conexion, $_GET['f_proveedor']) : '';
            $f_stock_min  = (isset($_GET['f_stock_min']) && $_GET['f_stock_min'] !== '') ? (int)$_GET['f_stock_min'] : 0;
            $f_stock_max  = (isset($_GET['f_stock_max']) && $_GET['f_stock_max'] !== '') ? (int)$_GET['f_stock_max'] : 0;

            $donde = "WHERE lm.estatus = 1 GROUP BY lm.Id";

            if ($busqueda != '') {
              $donde .= " HAVING (lm.Lote LIKE '%$busqueda%' OR GROUP_CONCAT(m.nombre_medicamento) LIKE '%$busqueda%' OR lm.estado_lote LIKE '%$busqueda%')";
            }
            if ($f_estado != '') {
              $donde .= " AND lm.estado_lote = '$f_estado'";
            }
            if ($f_fab_desde != '') {
              $donde .= " AND lm.fecha_fabricacion >= '$f_fab_desde'";
            }
            if ($f_fab_hasta != '') {
              $donde .= " AND lm.fecha_fabricacion <= '$f_fab_hasta'";
            }
            if ($f_venc_desde != '') {
              $donde .= " AND lm.fecha_vencimiento >= '$f_venc_desde'";
            }
            if ($f_venc_hasta != '') {
              $donde .= " AND lm.fecha_vencimiento <= '$f_venc_hasta'";
            }
            if ($f_proveedor != '') {
              $donde .= " AND prov.nombre_proveedor LIKE '%$f_proveedor%'";
            }
            if ($f_stock_min > 0) {
              $donde .= " AND ex.cantidad_actual >= $f_stock_min";
            }
            if ($f_stock_max > 0) {
              $donde .= " AND ex.cantidad_actual <= $f_stock_max";
            }

            $sql_conteo = "SELECT COUNT(DISTINCT lm.Id) as total 
               FROM lotes_medicamentos lm
               JOIN existencias_stock ex ON lm.Id = ex.Id_lote
               JOIN descripcion_medicamento dm ON lm.Id_descripcion_medicamento = dm.Id
               JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
               LEFT JOIN proveedor prov ON lm.Id_proveedor = prov.Id_proveedor
               $donde";
            $resultado_conteo = mysqli_query($conexion, $sql_conteo);
            $fila_conteo = mysqli_fetch_assoc($resultado_conteo);
            $total_registros = $fila_conteo['total'];
            $total_paginas = ceil($total_registros / $registros_por_pagina);

            $sql = "SELECT 
                        lm.Id, 
                        lm.Lote, 
                        lm.fecha_fabricacion, 
                        lm.fecha_vencimiento, 
                        lm.estado_lote,
                        prov.nombre_proveedor,
                        SUM(ex.cantidad_actual) AS cantidad_actual,
                        GROUP_CONCAT(DISTINCT m.nombre_medicamento SEPARATOR ', ') AS medicamentos
                    FROM lotes_medicamentos lm
                    JOIN existencias_stock ex ON lm.Id = ex.Id_lote
                    JOIN descripcion_medicamento dm ON lm.Id_descripcion_medicamento = dm.Id
                    JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
                    LEFT JOIN proveedor prov ON lm.Id_proveedor = prov.Id_proveedor
                    $donde
                    ORDER BY lm.fecha_fabricacion DESC
                    LIMIT $inicio, $registros_por_pagina";

            $resultado = $conexion->query($sql);
            ?>
          </tr>
          <tr>
            <?php
            if ($resultado->num_rows > 0) {
              while ($row = $resultado->fetch_assoc()) {

                $estatus = $row['estado_lote'];
                $clase_badge = 'bg-gray';

                if ($estatus == 'Cuarentena') $clase_badge = 'bg-yellow';
                if ($estatus == 'Disponible') $clase_badge = 'bg-green';
                if ($estatus == 'Dañado') $clase_badge = 'bg-crimson';
                if ($estatus == 'Vencido') $clase_badge = 'label-default';
                if ($estatus == 'Retirado') $clase_badge = 'label-default';

                $lista_medicamentos = $row['medicamentos'];
                $lista_corta = $lista_medicamentos;
                if (strlen($lista_medicamentos) > 40) {
                    $lista_corta = substr($lista_medicamentos, 0, 40) . ' (...)';
                }

            ?>
          </tr>
          <tr>
            <td class=""><span class="text-row text-white"><?= $row['Lote']; ?></span></td>
            <td class=""><span class="text-row text-white"><?= date('d/m/Y', strtotime($row['fecha_fabricacion'])); ?></span></td>
            <td class=""><span class="text-row text-white"><?= date('d/m/Y', strtotime($row['fecha_vencimiento'])); ?></span></td>
            
            <td class="" title="<?= htmlspecialchars($lista_medicamentos); ?>">
                <span class="text-row text-white"><?= htmlspecialchars($lista_corta); ?></span>
            </td>
            
            <td class=""><span class="text-row text-white"><?= $row['cantidad_actual']; ?></span></td>
            <td class=""><span class="badge <?= $clase_badge; ?>" style="font-size:11px;"><?= strtoupper($estatus); ?></span></td>
            <?php if (in_array('Gestionar acciones de lotes', $_SESSION["permisos"])) : ?>
              <td>
                <?php if (in_array('Editar Lotes', $_SESSION["permisos"])) : ?>
                  <a href="farmacia_lotes_editar.php?Id=<?php echo $row['Id'] ?>" class="btn-sm btn-warning" title="Editar"><img src="../../recursos/imagenes/iconos/editar.png" style="width:15px; height:15px;"></a>
                <?php endif; ?>
                <?php if (in_array('Desactivar Lotes', $_SESSION["permisos"])) : ?>
                  <a href="#" data-id="<?php echo $row['Id'] ?>" data-stock="<?php echo $row['cantidad_actual'] ?>" class="btn-sm btn-danger btn-desactivar" title="Desactivar"><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></a>
                <?php endif; ?>
              </td>
            <?php endif; ?>
          </tr>
      <?php }
            } else {
              echo "<tr><td colspan='7'>No se encontraron lotes registrados.</td></tr>";
            } ?>
        </tbody>
      </table>
    </div>

    <nav id="contenedorPaginacion" aria-label="Page navigation">
      <ul class="pagination">
        <?php
        $query_string = ($busqueda != '') ? "&buscar=" . urlencode($busqueda) : "";

        if ($pagina_actual > 1) : ?>
          <li><a href="?pagina=1<?php echo $query_string; ?>" title="Primero">&laquo;&laquo;</a></li>
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

  <div class="modal fade" id="DesactivarLote" tabindex="-1" role="dialog" aria-labelledby="DesactivarLoteLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h4 class="modal-title" id="DesactivarLoteLabel">Confirmación de Desactivación</h4>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea desactivar este lote? Esta acción solo se puede revertir en la papelera.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <a id="desactivar" href="#" class="btn btn-danger">Aceptar</a>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="AvisoStockLote" tabindex="-1" role="dialog" aria-labelledby="AvisoStockLoteLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h4 class="modal-title" id="AvisoStockLoteLabel"><i class="fa fa-exclamation-triangle"></i> Acción Denegada</h4>
        </div>
        <div class="modal-body">
          <p style="font-size: 15px;">No puede desactivar este lote porque <strong>aún tiene stock disponible</strong> en el inventario.</p>
          <p class="text-muted">Primero debe dar de baja las unidades sobrantes utilizando el módulo de <b>Ajuste de Inventario</b>. Una vez que el stock llegue a 0, podrá desactivarlo.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Entendido</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalBusquedaAvanzadaLotes" tabindex="-1" role="dialog" aria-labelledby="modalFiltrosLabel">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <form id="formBusquedaAvanzada" method="GET" action="">
          <div class="modal-header bg-primary">
            <h4 class="modal-title" id="modalFiltrosLabel"><i class="fa fa-filter"></i> Filtros Avanzados de Lotes</h4>
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
                <label>Proveedor</label>
                <input type="text" name="f_proveedor" class="form-control" placeholder="Nombre del proveedor...">
              </div>
              <div class="col-md-4 form-group">
                <label>Búsqueda General</label>
                <input type="text" name="buscar" id="buscar_modal" class="form-control" placeholder="Lote o Medicamento...">
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
                <label>Stock Actual Mínimo</label>
                <input type="number" name="f_stock_min" class="form-control" min="0" placeholder="Ej: 0">
              </div>
              <div class="col-md-6 form-group">
                <label>Stock Actual Máximo</label>
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

  <div class="modal fade" id="ModalReporteLote" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h4 class="modal-title" id="myModalLabel"><i class="fa fa-archive"></i> Reportes de Lotes e Inventario</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Seleccione el tipo de reporte:</label>
            <select class="form-control" id="tipo_reporte_lote">
              <option value="todos">Inventario General de Lotes</option>
              <option value="vencidos">Lotes Vencidos (Fecha pasada)</option>
              <option value="proximos_vencer">Próximos a Vencer (30-60 días)</option>
              <option value="stock_bajo">Alerta de Stock Bajo (Menos de 20 unidades)</option>
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

  <?php
  include('includes/footer.php');
  ?>

  <script>
    $(document).ready(function() {
      $('.reporte').on('click', function(e) {
        e.preventDefault();
        $('#ModalReporteLote').modal('show');
      });

      $('#btnEjecutarReporteLote').on('click', function() {
        var tipo = $('#tipo_reporte_lote').val();
        window.open('../../cfg/reportes/generar_pdf_lotes.php?tipo=' + tipo, '_blank');
        $('#ModalReporteLote').modal('hide');
      });

      $(document).on('click', '.btn-desactivar', function(e) {
        e.preventDefault();
        var IdLote = $(this).data('id');
        var stockLote = parseInt($(this).data('stock')) || 0;

        if (stockLote > 0) {
          $('#AvisoStockLote').modal('show');
        } else {
          var urlDesactivar = "../../cfg/desactivar/desactivar_lote.php?Id=" + IdLote;
          $('#desactivar').attr('href', urlDesactivar);
          $('#DesactivarLote').modal('show');
        }
      });

      window.cargarDatosAjax = function(url) {
        $('#contenedorTabla').css('opacity', '0.4');

        $.get(url, function(data) {
          var htmlParsed = $(data);

          $('#contenedorTabla').html(htmlParsed.find('#contenedorTabla').html()).css('opacity', '1');
          $('nav[aria-label="Page navigation"]').html(htmlParsed.find('nav[aria-label="Page navigation"]').html());

          window.history.pushState(null, '', url);
        }).fail(function() {
          alert("Error de conexión al aplicar filtros.");
          $('#contenedorTabla').css('opacity', '1');
        });
      };

      let timer;

      $('#buscar').off('keyup');

      $('#buscar').on('keyup', function() {
        clearTimeout(timer);
        let query = $(this).val();

        $('#buscar_modal').val(query);

        timer = setTimeout(function() {
          var filtrosActuales = $('#formBusquedaAvanzada').serialize();
          var url = 'farmacia_lotes_listado.php?' + filtrosActuales;
          cargarDatosAjax(url);
        }, 400);
      });

      $('#formBusquedaAvanzada').on('submit', function(e) {
        e.preventDefault();
        var url = 'farmacia_lotes_listado.php?' + $(this).serialize();
        $('#modalBusquedaAvanzadaLotes').modal('hide');
        cargarDatosAjax(url);
      });

      $(document).on('click', 'nav[aria-label="Page navigation"] .pagination a', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        if (url) cargarDatosAjax(url);
      });

      window.limpiarFiltrosAjax = function() {
        $('#formBusquedaAvanzada')[0].reset();
        $('#buscar').val('');
        $('#buscar_modal').val('');
        var urlLimpia = window.location.href.split('?')[0];
        cargarDatosAjax(urlLimpia);
        $('#modalBusquedaAvanzadaLotes').modal('hide');
      };

      <?php if ($mostrar_modal_exito) : ?>
        $('#modalExito').modal('show');
      <?php elseif ($mostrar_modal_error) : ?>
        $('#modalError').modal('show');
      <?php endif; ?>
    });
  </script>