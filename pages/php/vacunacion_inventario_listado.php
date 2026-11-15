<?php
include("../../cfg/conexion.php");
include('includes/headerNav2.php');
verificarAcceso('Gestionar Vacunas');
?>

<head>
  <title>Inventario de Vacunas | Listado</title>
</head>

<style>
  .btn-disabled {
    background-color: gray;
    opacity: 0.6;
    cursor: not-allowed;
  }
  .badge-lote { font-size: 10px; background-color: #555; }
  .badge-vence { font-size: 10px; background-color: #d9534f; }
  .badge-frio { font-size: 10px; background-color: #00c0ef; }
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
  $id_persona_activa = isset($_SESSION['id']) ? $_SESSION['id'] : 0;

  if ($id_rol_usuario_activo == 9) {
    $donde .= " AND di.Id_persona = '$id_persona_activa'";
  }

  if ($busqueda != '') {
    $donde .= " AND (v.nombre_vacuna LIKE '%$busqueda%' 
                  OR l.numero_lote LIKE '%$busqueda%' 
                  OR tm.nombre LIKE '%$busqueda%')";
  }

  if ($f_desde != '') { $donde .= " AND DATE(di.fecha) >= '$f_desde'"; }
  if ($f_hasta != '') { $donde .= " AND DATE(di.fecha) <= '$f_hasta'"; }
  if ($f_tipo_mov != '') { $donde .= " AND tm.nombre = '$f_tipo_mov'"; }
  if ($f_responsable != '') { $donde .= " AND p.nombre LIKE '%$f_responsable%'"; }

  /*
  $sql_conteo = "SELECT COUNT(DISTINCT di.Id_detalle_inventario) as total 
                 FROM detalle_inventario_vacunas di
                 INNER JOIN vacunas_detalle_inventario vdi ON di.Id_detalle_inventario = vdi.Id_detalle_inventario
                 INNER JOIN lotes_vacunas l ON vdi.Id_lote = l.Id
                 INNER JOIN catalogo_vacunas v ON l.Id_vacuna = v.Id
                 INNER JOIN tipo_movimiento tm ON di.Id_tipoMovimiento = tm.Id_tipo_movimiento
                 INNER JOIN persona p ON di.Id_persona = p.Id
                 $donde";
  $resultado_conteo = mysqli_query($conexion, $sql_conteo);
  $fila_conteo = mysqli_fetch_assoc($resultado_conteo);
  $total_registros = $fila_conteo['total'];
  */
  
  $total_registros = 3;
  $total_paginas = ceil($total_registros / $registros_por_pagina);
  ?>

  <section class="content-header">
    <h1>
      Inventario de Biológicos (<?php echo $total_registros; ?> Movimientos)
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i></i> Inicio</a></li>
      <li><a href="#"><i></i> Inmunización</a></li>
      <li class="active"><a href="#"><i></i> Inventario de Vacunas</a></li>
    </ol>
  </section>

  <section class="content">
    <div class="toolbar-container">
      <a href="#" class="btn-sm btn-info pull-right reporte" style="margin-left:5px;"><i class="fa fa-book"></i> Reportes PDF</a>
      <a href="vacunacion_pedidos_listado.php?op=pedido" class="btn-sm btn-primary pull-right" style="margin-left:5px;"><i class="fa fa-thermometer-empty"></i>Pedidos</a>
      <a href="vacunacion_ajuste_stock.php?op=ajuste_stock" class="btn-sm btn-primary pull-right" style="margin-left:5px;"><i class="fa fa-thermometer-empty"></i>Ajuste de Stock</a>
      <a href="vacunacion_inventario_movimiento_salida.php?op=ajuste_salida" class="btn-sm btn-primary pull-right" style="margin-left:5px;"><i class="fa fa-thermometer-empty"></i>Ajuste de Inventatio</a>
      <a href="vacunacion_inventario_movimiento_despacho.php?op=despacho" class="btn-sm btn-primary pull-right" style="margin-left:5px;"><i class="fa fa-arrow-down"></i>Despacho</a>
      <a href="vacunacion_inventario_movimiento_entrada.php?op=entrada" class="btn-sm btn-primary pull-right" style="margin-left:5px;"><i class="fa fa-arrow-up"></i>Entrada</a>

      <div class="pull-left form-inline">
        <form method="GET" action="" id="formBusquedaRapida">
          <input type="text" id="buscar" name="buscar" class="form-control" placeholder="Buscar vacuna o lote..." value="<?php echo htmlspecialchars($busqueda); ?>" style="border-radius:0; height:10%; width:250px; display:inline-block;" autocomplete="off">
        </form>
      </div>
      
      <span data-toggle="tooltip" data-placement="right" title="Filtros avanzados de inventario de vacunas">
        <button type="button" class="btn-sm btn-primary btn-sm pull-left" data-toggle="modal" data-target="#modalBusquedaAvanzada" style="margin-left:5px;">
          <i class="fa fa-filter"><img src="../../recursos/imagenes/iconos/filtrar.png" style="width:10px; height:10px; filter:invert(1);" title="filtrar"></i>
        </button>
      </span>
    </div>
    <br><br>

    <div id="contenedorTabla">
      <table class="table table-sm table-hover mt-4" width="100%" id="t_user">
        <thead class="table-dark">
          <tr>
            <th>Responsable</th>
            <th>Vacuna / Biológico</th>
            <th>Lote y Vencimiento</th>     
            <th>Cantidad</th>
            <th>Fecha / Hora</th>
            <th>Movimiento</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody class="tbody" style="font-size: 12px;">
          
          <!-- ======================================================= -->
          <!-- SIMULACIÓN DE DATOS (BORRAR CUANDO CONECTES LA BD REAL) -->
          <!-- ======================================================= -->
          <tr>
            <td><small class="text-row text-white">Lic. María Rodríguez</small></td>
            <td>
              <b>Antipoliomielítica (VPI)</b><br>
              <small class="text-muted">Presentación: 1 dosis</small>
            </td>
            <td>
              <span class="">Lote: 12345A</span><br>
              <span class="">Vence: 10/12/2025</span>
            </td>       
            <td><b>+ 50 Unid.</b></td>
            <td><small class="text-row text-white">24/05/2024 08:30 AM</small></td>
            <td><small class="badge bg-green">Entrada</small></td>
            <td>
              <a href="vacunacion_inventario_info.php" class="btn-sm btn-info" title="Ver Detalle"><img src="../../recursos/imagenes/iconos/info.png" style="width:15px; height:15px;"></a>
            </td>
          </tr>

          <tr>
            <td><small class="text-row text-white">Enf. Carlos Pérez</small></td>
            <td>
              <b>Antisarampión (SRP)</b><br>
              <small class="text-muted">Presentación: 10 dosis</small>
            </td>
            <td>
              <span class="">Lote: SRP-991</span><br>
              <span class="">Vence: 01/08/2024</span>
            </td>
            <td><b>- 2 Unid.</b></td>
            <td><small class="text-row text-white">24/05/2024 09:15 AM</small></td>
            <td><small class="badge bg-crimson">Despacho</small></td>
            <td>
              <a href="vacunacion_inventario_info.php" class="btn-sm btn-info" title="Ver Detalle"><img src="../../recursos/imagenes/iconos/info.png" style="width:15px; height:15px;"></a>
            </td>
          </tr>

          <tr>
            <td><small class="text-row text-white">Lic. María Rodríguez</small></td>
            <td>
              <b>Vacuna BCG</b><br>
              <small class="text-muted">Presentación: 20 dosis</small>
            </td>
            <td>
              <span class="">Lote: BCG-003</span><br>
              <span class="">Vence: 15/11/2026</span>
            </td>
            <td><b>- 1 Unid.</b></td>
            <td><small class="text-row text-white">23/05/2024 04:00 PM</small></td>
            <td><small class="badge bg-crimson">Ajuste de Inventario</small></td>
            <td>
              <a href="vacunacion_inventario_info.php" class="btn-sm btn-info" title="Ver Detalle"><img src="../../recursos/imagenes/iconos/info.png" style="width:15px; height:15px;"></a>
            </td>
          </tr>
          <!-- FIN DE LA SIMULACIÓN -->
          
        </tbody>
      </table>
    </div>

    <!-- PAGINACIÓN (Mantenida) -->
    <nav id="contenedorPaginacion" aria-label="Page navigation">
      <ul class="pagination">
        <li class="disabled"><a href="#">&laquo;</a></li>
        <li class="active"><a href="#">1</a></li>
        <li><a href="#">2</a></li>
        <li><a href="#">&raquo;</a></li>
      </ul>
    </nav>
  </section>

  <!-- MODALES (Reutilizados del código original) -->
  
  <!-- MODAL DE REPORTES -->
  <div class="modal fade" id="ModalReporteInventario" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h4 class="modal-title"><i class="fa fa-syringe"></i> Reportes de Vacunación</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Tipo de Reporte:</label>
            <select class="form-control" id="tipo_reporte_inv">
              <optgroup label="Estado Actual">
                <option value="stock">Existencia Actual de Vacunas</option>
                <option value="vencimientos">Próximos a Vencer (Alerta)</option>
                <option value="frio">Estado de la Cadena de Frío</option>
              </optgroup>
              <optgroup label="Movimientos">
                <option value="entradas">Entradas (Lotes Recibidos)</option>
                <option value="salidas">Salidas (Dosis Aplicadas)</option>
                <option value="mermas">Mermas y Pérdidas</option>
              </optgroup>
            </select>
          </div>

          <div id="seccion_fechas" style="display:none;">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Desde:</label>
                  <input type="date" class="form-control" id="fecha_desde" value="<?= date('Y-m-d'); ?>">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Hasta:</label>
                  <input type="date" class="form-control" id="fecha_hasta" value="<?= date('Y-m-d'); ?>">
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

  <!-- MODAL DE FILTROS AVANZADOS -->
  <div class="modal fade" id="modalBusquedaAvanzada" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <form id="formBusquedaAvanzada" method="GET" action="">
          <div class="modal-header bg-primary">
            <h4 class="modal-title"><i class="fa fa-filter"></i> Filtros de Inventario de Vacunas</h4>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-4 form-group">
                <label>Fecha Desde</label>
                <input type="date" name="f_desde" class="form-control" value="<?php echo $f_desde; ?>">
              </div>
              <div class="col-md-4 form-group">
                <label>Fecha Hasta</label>
                <input type="date" name="f_hasta" class="form-control" value="<?php echo $f_hasta; ?>">
              </div>
              <div class="col-md-4 form-group">
                <label>Tipo de Movimiento</label>
                <select name="f_tipo_mov" class="form-control">
                  <option value="">Todos</option>
                  <option value="Entrada">Entrada</option>
                  <option value="Salida">Salida</option>
                  <option value="Ajuste">Ajuste por Pérdida</option>
                </select>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 form-group">
                <label>Responsable</label>
                <input type="text" name="f_responsable" class="form-control" placeholder="Nombre del responsable..." value="<?php echo htmlspecialchars($f_responsable); ?>">
              </div>
              <div class="col-md-6 form-group">
                <label>Nombre de la Vacuna o Lote</label>
                <input type="text" name="buscar" class="form-control" placeholder="Ej: BCG o Lote 123..." value="<?php echo htmlspecialchars($busqueda); ?>">
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
  $(document).ready(function() {
    
    // Lógica para mostrar fechas solo en reportes de movimiento
    $('#tipo_reporte_inv').on('change', function() {
      var valor = $(this).val();
      if (valor == 'entradas' || valor == 'salidas' || valor == 'mermas') {
        $('#seccion_fechas').fadeIn();
      } else {
        $('#seccion_fechas').fadeOut();
      }
    });

    $('.reporte').on('click', function(e) {
      e.preventDefault();
      $('#ModalReporteInventario').modal('show');
    });

    // Simulación de generación de PDF
    $('#btnEjecutarReporteInv').on('click', function() {
      var tipo = $('#tipo_reporte_inv').val();
      var desde = $('#fecha_desde').val();
      var hasta = $('#fecha_hasta').val();
      
      var url = '../../cfg/reportes/generar_pdf_vacunas_inventario.php?tipo=' + tipo;
      
      if (tipo == 'entradas' || tipo == 'salidas' || tipo == 'mermas') {
        url += '&desde=' + desde + '&hasta=' + hasta;
      }

      // window.open(url, '_blank'); // Descomentar cuando el generador de PDF exista
      alert("Generando Reporte: " + tipo + " (Función pendiente de backend)");
      $('#ModalReporteInventario').modal('hide');
    });

    // Búsqueda en vivo (Filtra la tabla visualmente)
    $("#buscar").on("keyup", function() {
      var value = $(this).val().toLowerCase();
      $("#t_user tbody tr").filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
      });
    });
  });
</script>