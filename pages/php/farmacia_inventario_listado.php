<?php
include("../../cfg/conexion.php");
include('includes/headerNav2.php');
verificarAcceso('Gestionar Farmacia');
?>

<head>
  <title>Inventario | Listado</title>
</head>

<style>
  .btn-disabled {
    background-color: gray;
  }
</style>

<div class="content-wrapper">
  <?php
  $registros_por_pagina = 9;
  $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
  $inicio = ($pagina_actual - 1) * $registros_por_pagina;

  $busqueda      = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : '';
  $f_desde       = isset($_GET['f_desde']) ? mysqli_real_escape_string($conexion, $_GET['f_desde']) : '';
  $f_hasta       = isset($_GET['f_hasta']) ? mysqli_real_escape_string($conexion, $_GET['f_hasta']) : '';
  $f_tipo_mov    = isset($_GET['f_tipo_mov']) ? mysqli_real_escape_string($conexion, $_GET['f_tipo_mov']) : '';
  $f_responsable = isset($_GET['f_responsable']) ? mysqli_real_escape_string($conexion, $_GET['f_responsable']) : '';

  $donde = "WHERE 1=1";

  $id_rol_usuario_activo = isset($_SESSION['rol']) ? $_SESSION['rol'] : 0;
  $id_persona_activa = isset($_SESSION['id']) ? $_SESSION['id'] : (isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : 0);

  if ($id_rol_usuario_activo == 9) {
    $donde .= " AND di.Id_persona = '$id_persona_activa'";
  }

  if ($busqueda != '') {
    $donde .= " AND (m.nombre_medicamento LIKE '%$busqueda%' 
                  OR p.nombre LIKE '%$busqueda%' 
                  OR l.Lote LIKE '%$busqueda%' 
                  OR tm.nombre LIKE '%$busqueda%')";
  }

  if ($f_desde != '') {
    $donde .= " AND DATE(di.fecha) >= '$f_desde'";
  }
  if ($f_hasta != '') {
    $donde .= " AND DATE(di.fecha) <= '$f_hasta'";
  }
  if ($f_tipo_mov != '') {
    $donde .= " AND tm.nombre = '$f_tipo_mov'";
  }
  if ($f_responsable != '') {
    $donde .= " AND p.nombre LIKE '%$f_responsable%'";
  }

  $sql_conteo = "SELECT COUNT(DISTINCT di.Id_detalle_inventario) as total 
                 FROM detalle_inventario di
                 INNER JOIN medicamentos_detalle_inventario mdi ON di.Id_detalle_inventario = mdi.Id_detalle_inventario
                 INNER JOIN lotes_medicamentos l ON mdi.Id_lote = l.Id
                 INNER JOIN descripcion_medicamento dm ON l.Id_descripcion_medicamento = dm.Id
                 INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
                 INNER JOIN tipo_movimiento tm ON di.Id_tipoMovimiento = tm.Id_tipo_movimiento
                 INNER JOIN persona p ON di.Id_persona = p.Id
                 $donde";

  $resultado_conteo = mysqli_query($conexion, $sql_conteo);
  $fila_conteo = mysqli_fetch_assoc($resultado_conteo);
  $total_registros = $fila_conteo['total'];
  $total_paginas = ceil($total_registros / $registros_por_pagina);
  ?>

  <section class="content-header">
    <h1>
      Inventario (<?php echo $total_registros; ?> Movimientos)
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="#"><i class="fa fa-user"></i>Inventario</a></li>
      <li class="active"><a href="#"><i class="fa fa-book"></i>Listado</a></li>
    </ol>
  </section>

  <section class="content">
    <div style="padding-bottom: 10px;">
      <?php if (in_array('Generar Reportes de Inventario', $_SESSION["permisos"])) : ?>
        <a href="#" class="btn-sm btn-info pull-right reporte"><i class="fa fa-book"></i> Generar Reporte </a>
      <?php endif; ?>
      <p class="pull-right" style="width:5px;"></p>
      <?php if (in_array('Generar Pedidos', $_SESSION["permisos"])) : ?>
        <a href="farmacia_pedidos_listado.php" class="btn-sm btn-primary pull-right"><i class="fa fa-cog"></i> Pedidos </a>
      <?php endif; ?>
      <p class="pull-right" style="width:5px;"></p>
      <?php if (in_array('Ajustar Stock de Medicamentos', $_SESSION["permisos"])) : ?>
        <a href="farmacia_inventario_ajustes.php?op=ajuste" class="btn-sm btn-primary pull-right"><i class="fa fa-cog"></i> Ajuste de Stock </a>
      <?php endif; ?>

      <p class="pull-right" style="width:5px;"></p>
      <?php if (in_array('Generar Salidas de Inventario', $_SESSION["permisos"])) : ?>
        <a href="farmacia_inventario_movimiento_salida.php?op=ajuste_salida" class="btn-sm btn-primary pull-right"><i class="fa fa-arrow-down"></i> Ajuste de Inventario</a>
      <?php endif; ?>
      <p class="pull-right" style="width:5px;"></p>
      <?php if (in_array('Generar Despacho de Inventario', $_SESSION["permisos"])) : ?>
        <a href="farmacia_inventario_movimiento_despacho.php?op=despacho" class="btn-sm btn-primary pull-right"><i class="fa fa-arrow-down"></i> Despacho</a>
      <?php endif; ?>
      <p class="pull-right" style="width:5px;"></p>
      <?php if (in_array('Generar Entradas de Inventario', $_SESSION["permisos"])) : ?>
        <a href="farmacia_inventario_movimiento_entrada.php?op=entrada" class="btn-sm btn-primary pull-right"><i class="fa fa-arrow-up"></i> Entrada </a>
      <?php endif; ?>

      <div class="pull-left form-inline">
        <form method="GET" action="" id="formBusquedaRapida">
          <input type="text" id="buscar" name="buscar" class="form-control" placeholder="Escriba para buscar..." value="<?php echo htmlspecialchars($busqueda); ?>" style="border-radius:0; height:10%; width:250px; display:inline-block;" autocomplete="off">
        </form>
      </div>
      <p class="pull-right" style="width:5px;"></p>
      <span data-toggle="tooltip" data-placement="right" title="Aqui podras filtrar de manera avanzada la busqueda de movimientos.">
        <button type="button" class="btn-sm btn-primary btn-sm pull-left" data-toggle="modal" data-target="#modalBusquedaAvanzada">
          <i class="fa fa-filter"><img src="../../recursos/imagenes/iconos/filtrar.png" style="width:10px; height:10px; filter:invert(1);" title="filtrar receta"></i>
        </button>
      </span>
    </div>
    <br><br>

    <div id="contenedorTabla">
      <table class="table table-sm table-hover mt-4" width="100%" id="t_user">
        <thead class="table-dark" style="background-color: #222; color: white; font-size: 12px;">
          <tr>
            <th>Responsable</th>
            <th>Resumen de Productos</th>
            <th>Fecha</th>
            <th>Movimiento</th>
            <?php if (in_array('Gestionar acciones de inventario', $_SESSION["permisos"])) : ?>
              <th>Acciones</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody class="tbody" style="font-size: 12px;">
          <?php
          $sql = "SELECT
                di.Id_detalle_inventario,
                di.estado_movimiento,
                di.fecha,
                di.Id_tipoMovimiento,
                tm.nombre as tipo_nom,
                p.nombre as responsable,
                GROUP_CONCAT(CONCAT(m.nombre_medicamento, ' (x', mdi.cantidad, ')') SEPARATOR ', ') AS resumen_productos
            FROM detalle_inventario di
            INNER JOIN medicamentos_detalle_inventario mdi ON di.Id_detalle_inventario = mdi.Id_detalle_inventario
            INNER JOIN lotes_medicamentos l ON mdi.Id_lote = l.Id
            INNER JOIN descripcion_medicamento dm ON l.Id_descripcion_medicamento = dm.Id
            INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
            INNER JOIN tipo_movimiento tm ON di.Id_tipoMovimiento = tm.Id_tipo_movimiento
            INNER JOIN persona p ON di.Id_persona = p.Id
            $donde
            GROUP BY di.Id_detalle_inventario
            ORDER BY di.fecha DESC
            LIMIT $inicio, $registros_por_pagina";

          $resultado = $conexion->query($sql);
          $primer_activo_encontrado = false;

          if ($resultado && $resultado->num_rows > 0) {
            while ($row = $resultado->fetch_assoc()) {

              $tipos_prohibidos = [8, 9];

              $es_primer_activo = false;
              if ($row['estado_movimiento'] !== 'Anulado' && !in_array($row['Id_tipoMovimiento'], $tipos_prohibidos) && !$primer_activo_encontrado) {
                $es_primer_activo = true;
                $primer_activo_encontrado = true;
              }

              $clase_badge = (strcasecmp($row['tipo_nom'], 'Entrada') == 0 or strcasecmp($row['tipo_nom'], 'Ajuste por Cuadre (Entrada)') == 0) ? 'bg-green' : 'bg-crimson';
              $es_entrada = (strcasecmp($row['tipo_nom'], 'Entrada') == 0);

              $simbolo = $es_entrada ? '+' : '-';
              $color_texto = $es_entrada ? 'text-green' : 'text-red';
          ?>
              <tr>
                <td><small class="text-row text-white"><?= htmlspecialchars($row['responsable']); ?></small></td>
                <td><small class="text-row text-white"><?= htmlspecialchars($row['resumen_productos']); ?></small></td>
                <td><small class="text-row text-white"><?= date("d/m/Y H:i", strtotime($row['fecha'])); ?></small></td>
                <td><small class="badge <?= $clase_badge; ?>"><?= htmlspecialchars($row['tipo_nom']); ?></small></td>
                <?php if (in_array('Gestionar acciones de inventario', $_SESSION["permisos"])) : ?>
                  <td>
                    <?php if (in_array('Ver Movimientos de Inventario', $_SESSION["permisos"])) : ?>
                      <a href="farmacia_inventario_ver_movimiento.php?id=<?php echo $row['Id_detalle_inventario'] ?>" class="btn-sm btn-info" title="Ver"><img src="../../recursos/imagenes/iconos/info.png" style="width:15px; height:15px;"></a>
                    <?php endif; ?>
                    <?php if (in_array('Anular Movimientos de Inventario', $_SESSION["permisos"])) : ?>
                      <?php if ($row['estado_movimiento'] !== 'Anulado' && !in_array($row['Id_tipoMovimiento'], $tipos_prohibidos)) : ?>

                        <?php if ($es_primer_activo && $pagina_actual === 1 && empty($busqueda)) : ?>
                          <a href="javascript:void(0);" onclick="abrirModalAnulacion(<?= $row['Id_detalle_inventario']; ?>)" title="Anular Movimiento" class="btn-sm btn-danger">
                            <img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;">
                          </a>
                        <?php else : ?>
                          <a href="#" class="btn-sm btn-disabled" title="Solo puede anular el último movimiento realizado para evitar descuadres de stock">
                            <img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px; opacity: 0.5;">
                          </a>
                        <?php endif; ?>

                      <?php else : ?>
                        <span class="badge badge-secondary">Anulado</span>
                      <?php endif; ?>
                    <?php endif; ?>
                  </td>
                <?php endif; ?>
              </tr>
          <?php
            }
          } else {
            echo '<tr><td colspan="8">No hay movimientos registrados.</td></tr>';
          }
          ?>
        </tbody>
      </table>
    </div>

    <nav id="contenedorPaginacion" aria-label="Page navigation" style="position: fixed; bottom:0;">
      <ul class="pagination">
        <?php
        $query_string = ($busqueda != '') ? "&buscar=" . urlencode($busqueda) : "";

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

  <div class="modal fade" id="modalExito" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-green">
          <h5 class="modal-title">Operación Exitosa</h5>
        </div>
        <div class="modal-body">
          <p><?= $mensaje_modal; ?></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success" data-dismiss="modal">Aceptar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalError" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h5 class="modal-title">Error en la Operación</h5>
        </div>
        <div class="modal-body">
          <p><?= $mensaje_modal; ?></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalAnulacion" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h5 class="modal-title">Confirmación de Anulación</h5>
        </div>
        <form action="../../cfg/movimientos_inventario.php" method="POST">
          <div class="modal-body">
            <p>¿Está seguro de que desea anular el <strong>Movimiento #<span id="display_id"></span></strong>?</p>
            <p class="text-muted small">Esta acción revertirá el stock y no se puede deshacer.</p>

            <input type="hidden" name="op" value="revertir_movimiento">
            <input type="hidden" name="id_detalle_inventario" id="id_anular_input">

            <div id="divMotivoAnulacion" style="margin-top: 15px;">
              <label for="motivo_anulacion">Motivo de la anulación <span class="text-danger">*</span></label>
              <select id="motivo_anulacion" name="motivo_anulacion" class="form-control" required>
                <option value="" selected disabled>-- Seleccione el motivo --</option>
                <optgroup label="Anulación permitida (el físico NO salió)">
                  <option value="Error de digitación - el medicamento nunca salió del almacén">Error de digitación (el físico nunca salió)</option>
                  <option value="Error de cantidad - se registró más de lo realmente despachado">Error de cantidad</option>
                  <option value="Error de lote - se seleccionó el lote equivocado">Error de lote</option>
                </optgroup>
                <optgroup label="NO use este modal (el físico SÍ salió)">
                  <option value="DEVOLUCION" disabled>Si el paciente devolvió el medicamento → registre una DEVOLUCIÓN</option>
                </optgroup>
              </select>
              <small id="errorMotivo" class="text-danger" style="display:none; font-weight:bold;">Debe seleccionar un motivo obligatorio.</small>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-danger">Confirmar</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="ModalReporteInventario" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h4 class="modal-title" id="myModalLabel"><i class="fa fa-boxes"></i> Reportes de Inventario y Movimientos</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Tipo de Reporte:</label>
            <select class="form-control" id="tipo_reporte_inv">
              <?php $rol_actual = isset($_SESSION['rol']) ? $_SESSION['rol'] : 0; ?>
              <optgroup label="Estado Actual">
                <option value="existencia">Existencia Actual (Stock)</option>
                <?php if ($rol_actual != 9) : ?>
                  <option value="vencimientos">Próximos a Vencer (Control de Lotes)</option>
                <?php endif; ?>
              </optgroup>

              <optgroup label="Movimientos y Trazabilidad">
                <?php if ($rol_actual != 9) : ?>
                  <option value="entradas">Entradas (Ingresos de Suministros)</option>
                <?php endif; ?>
                <option value="despacho">Salidas por Dispensación (Récipes)</option>
                <?php if ($rol_actual != 9) : ?>
                  <option value="bajas">Bajas y Mermas (Vencidos/Dañados)</option>
                  <option value="ajustes">Ajustes de Inventario (Auditoría)</option>
                <?php endif; ?>
              </optgroup>

              <optgroup label="Análisis y Planificación">
                <option value="consumo">Consumo Mensual Actualizado (CMA)</option>
              </optgroup>
            </select>
          </div>

          <div id="seccion_fechas" style="display:none;">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Desde:</label>
                  <input type="date" class="form-control" id="fecha_desde" value="<?= date('Y-m-d'); ?>" max="<?= date('Y-m-d'); ?>">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Hasta:</label>
                  <input type="date" class="form-control" id="fecha_hasta" value="<?= date('Y-m-d'); ?>" min="<?= date('Y-m-d'); ?>">
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-primary" id="btnEjecutarReporteInv">Generar PDF</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalBusquedaAvanzada" tabindex="-1" role="dialog" aria-labelledby="modalFiltrosLabel">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <form id="formBusquedaAvanzada" method="GET" action="">
          <div class="modal-header bg-primary">
            <h4 class="modal-title" id="modalFiltrosLabel"><i class="fa fa-filter"></i> Filtros Avanzados de Movimientos</h4>
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
                <label>Tipo de Movimiento</label>
                <select name="f_tipo_mov" class="form-control">
                  <option value="">Todos</option>
                  <?php
                  $res_tipos = $conexion->query("SELECT nombre FROM tipo_movimiento ORDER BY nombre ASC");
                  while ($tp = $res_tipos->fetch_assoc()) {
                    $sel = ($f_tipo_mov == $tp['nombre']) ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($tp['nombre']) . "' $sel>" . htmlspecialchars($tp['nombre']) . "</option>";
                  }
                  ?>
                </select>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 form-group">
                <label>Responsable</label>
                <input type="text" name="f_responsable" class="form-control" placeholder="Nombre del responsable..." oninput="this.value = this.value.replace(/[0-9]/g, '');" value="<?php echo htmlspecialchars($f_responsable); ?>">
              </div>
              <div class="col-md-6 form-group">
                <label>Contiene Producto / Medicamento</label>
                <input type="text" name="buscar" class="form-control" placeholder="Nombre de medicamento..." oninput="this.value = this.value.replace(/[0-9]/g, '');" value="<?php echo htmlspecialchars($busqueda); ?>">
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
</div>

<?php include('includes/footer.php'); ?>

<script>
  window.abrirModalAnulacion = function(id) {
    document.getElementById('id_anular_input').value = id;
    document.getElementById('display_id').textContent = id;
    $('#modalAnulacion').modal('show');
  };

  $(document).ready(function() {
    $('.reporte').on('click', function(e) {
      e.preventDefault();
      $('#ModalReporteInventario').modal('show');
    });

    $('#tipo_reporte_inv').on('change', function() {
      var valor = $(this).val();
      if (valor == 'entradas' || valor == 'salidas' || valor == 'despacho' || valor == 'bajas' || valor == 'ajustes') {
        $('#seccion_fechas').fadeIn();
      } else {
        $('#seccion_fechas').fadeOut();
      }
    });

    $('#btnEjecutarReporteInv').on('click', function() {
      var tipo = $('#tipo_reporte_inv').val();
      var desde = $('#fecha_desde').val();
      var hasta = $('#fecha_hasta').val();

      var url = '../../cfg/reportes/generar_pdf_inventario.php?tipo=' + tipo;

      if (tipo == 'entradas' || tipo == 'salidas' || tipo == 'despacho' || tipo == 'bajas' || tipo == 'ajustes') {
        url += '&desde=' + desde + '&hasta=' + hasta;
      }

      window.open(url, '_blank');
      $('#ModalReporteInventario').modal('hide');
    });

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
        var url = 'farmacia_inventario_listado.php?buscar=' + encodeURIComponent(query);
        cargarDatosAjax(url);
      }, 400);
    });

    $('#formBusquedaRapida').on('submit', function(e) {
      e.preventDefault();
    });

    $('#formBusquedaAvanzada').on('submit', function(e) {
      e.preventDefault();
      var url = 'farmacia_inventario_listado.php?' + $(this).serialize();
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