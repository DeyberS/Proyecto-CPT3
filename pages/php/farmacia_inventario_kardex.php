<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Farmacia | Kardex de Medicamentos</title>
    <?php
    include('includes/headerNav2.php');
    include("../../cfg/conexion.php");

    // --- RECEPCIÓN DE VARIABLES DE FILTRADO ---
    $busqueda      = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : '';
    $f_desde       = isset($_GET['f_desde']) ? mysqli_real_escape_string($conexion, $_GET['f_desde']) : '';
    $f_hasta       = isset($_GET['f_hasta']) ? mysqli_real_escape_string($conexion, $_GET['f_hasta']) : '';
    $f_tipo_mov    = isset($_GET['f_tipo_mov']) ? mysqli_real_escape_string($conexion, $_GET['f_tipo_mov']) : '';
    $f_lote        = isset($_GET['f_lote']) ? mysqli_real_escape_string($conexion, $_GET['f_lote']) : '';
    $f_proveedor   = isset($_GET['f_proveedor']) ? mysqli_real_escape_string($conexion, $_GET['f_proveedor']) : '';
    
    $f_cant_min    = (isset($_GET['f_cant_min']) && $_GET['f_cant_min'] !== '') ? (int)$_GET['f_cant_min'] : 0;
    $f_cant_max    = (isset($_GET['f_cant_max']) && $_GET['f_cant_max'] !== '') ? (int)$_GET['f_cant_max'] : 0;
    $f_stock_min   = (isset($_GET['f_stock_min']) && $_GET['f_stock_min'] !== '') ? (int)$_GET['f_stock_min'] : 0;
    $f_stock_max   = (isset($_GET['f_stock_max']) && $_GET['f_stock_max'] !== '') ? (int)$_GET['f_stock_max'] : 0;

    // Determinar si hay filtros aplicados para mostrar la tabla o dejar el estado vacío
    $filtros_aplicados = ($busqueda != '' || $f_desde != '' || $f_hasta != '' || $f_tipo_mov != '' || $f_lote != '' || $f_proveedor != '' || $f_cant_min > 0 || $f_cant_max > 0 || $f_stock_min > 0 || $f_stock_max > 0);
    $mostrar_vacio = !$filtros_aplicados;

    // Construcción del WHERE dinámico
    $donde = " WHERE 1=1 ";

    if ($busqueda != '') {
        $donde .= " AND (m.nombre_medicamento LIKE '%$busqueda%' OR l.Lote LIKE '%$busqueda%' OR p.nombre_proveedor LIKE '%$busqueda%')";
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
    if ($f_lote != '') {
        $donde .= " AND l.Lote LIKE '%$f_lote%'";
    }
    if ($f_proveedor != '') {
        $donde .= " AND p.nombre_proveedor LIKE '%$f_proveedor%'";
    }
    if ($f_cant_min > 0) {
        $donde .= " AND mdi.cantidad >= $f_cant_min";
    }
    if ($f_cant_max > 0) {
        $donde .= " AND mdi.cantidad <= $f_cant_max";
    }
    if ($f_stock_min > 0) {
        $donde .= " AND mdi.stock_momento >= $f_stock_min";
    }
    if ($f_stock_max > 0) {
        $donde .= " AND mdi.stock_momento <= $f_stock_max";
    }
    ?>
</head>
<style>
    @keyframes pulse-opacity { 0% { opacity: 0; } 100% { opacity: 1; } }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-50px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes fadeOut { from { opacity: 1; transform: translateY(0); } to { opacity: 0; transform: translateY(-50px); } }

    .modal.in .modal-dialog, #ModalAdvertencia, #modalBusquedaAvanzadaKardex { animation: fadeIn 0.4s ease-out; }
    .modal-open .modal-backdrop { opacity: 0.7 !important; animation: pulse-opacity 0.3s forwards; }
    .table-kardex thead { background-color: #f4f4f4; }

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
        box-shadow: 0 -4px 10px rgba(0,0,0,0.4);  
        width: 83%;
    }
    #pagination_container .pagination { margin: 0; }
    #pagination_container .pagination > li > a { background-color: #333 !important; border-color: #444 !important; color: #fff !important; }
    #pagination_container .pagination > .active > a { background-color: #222 !important; border-color: #222 !important; }
</style>

<body>
    <div class="content-wrapper">
        <?php
        // --- LÓGICA DE PAGINACIÓN ---
        $registros_por_pagina = 8;
        $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $inicio = ($pagina_actual - 1) * $registros_por_pagina;
        $total_paginas = 0;
        
        $total_entradas = 0;
        $total_salidas = 0;

        if (!$mostrar_vacio) {
            // 1. Conteo para la paginación filtrado
            $sql_conteo = "SELECT COUNT(*) as total 
                   FROM medicamentos_detalle_inventario mdi
                   JOIN detalle_inventario di ON mdi.Id_detalle_inventario = di.Id_detalle_inventario
                   JOIN descripcion_medicamento dm ON mdi.Id_descripcion_medicamento = dm.Id
                   JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
                   JOIN lotes_medicamentos l ON mdi.Id_lote = l.Id
                   JOIN proveedor p ON l.Id_proveedor = p.Id_proveedor
                   JOIN tipo_movimiento tm ON di.Id_tipoMovimiento = tm.Id_tipo_movimiento
                   $donde";

            $total_registros = $conexion->query($sql_conteo)->fetch_assoc()['total'];
            $total_paginas = ceil($total_registros / $registros_por_pagina);

            // 2. Consulta principal del Kardex
            $sql_kardex = "SELECT 
            di.fecha, 
            m.nombre_medicamento, 
            tdm.nombre_presentacion,
            GROUP_CONCAT(CONCAT(IFNULL(pa.nombre,''), ' ', IFNULL(dpm.cantidad_unidad_medida,''), IFNULL(um.unidad,'')) SEPARATOR ' + ') AS componentes,
            l.Lote, 
            p.nombre_proveedor,
            tm.nombre as tipo,
            mdi.cantidad, 
            mdi.stock_momento,
            di.observaciones
            FROM medicamentos_detalle_inventario mdi
            JOIN detalle_inventario di ON mdi.Id_detalle_inventario = di.Id_detalle_inventario
            JOIN descripcion_medicamento dm ON mdi.Id_descripcion_medicamento = dm.Id
            JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
            JOIN presentacion tdm ON dm.Id_presentacion = tdm.Id_presentacion
            JOIN detalle_principio_medicamento dpm ON dm.Id = dpm.id_medicamento
            JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida
            JOIN principio_activo pa ON dpm.id_principio_activo = pa.Id_principio_activo
            JOIN lotes_medicamentos l ON mdi.Id_lote = l.Id
            JOIN proveedor p ON l.Id_proveedor = p.Id_proveedor
            JOIN tipo_movimiento tm ON di.Id_tipoMovimiento = tm.Id_tipo_movimiento
            $donde
            GROUP BY mdi.Id
            ORDER BY di.fecha DESC 
            LIMIT $inicio, $registros_por_pagina";

            $resultado = $conexion->query($sql_kardex);

            // 3. Consulta para los totales del medicamento
            $sql_totales = "SELECT tm.nombre as tipo, SUM(mdi.cantidad) as total_cantidad 
                            FROM medicamentos_detalle_inventario mdi
                            JOIN detalle_inventario di ON mdi.Id_detalle_inventario = di.Id_detalle_inventario
                            JOIN descripcion_medicamento dm ON mdi.Id_descripcion_medicamento = dm.Id
                            JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
                            JOIN lotes_medicamentos l ON mdi.Id_lote = l.Id
                            JOIN proveedor p ON l.Id_proveedor = p.Id_proveedor
                            JOIN tipo_movimiento tm ON di.Id_tipoMovimiento = tm.Id_tipo_movimiento
                            $donde
                            GROUP BY tm.nombre";

            $res_totales = $conexion->query($sql_totales);

            while ($tot = $res_totales->fetch_assoc()) {
                $tipo = $tot['tipo'];
                $cantidad = $tot['total_cantidad'];

                if (in_array($tipo, ['Entrada', 'Ajuste por Cuadre (Entrada)', 'Reversión de Salida (Anulación)'])) {
                    $total_entradas += $cantidad;
                } elseif (in_array($tipo, ['Salida por Despacho', 'Salida por Vencimiento', 'Salida por Dañado', 'Salida por Pérdida o Robo', 'Ajuste por Cuadre (Salida)', 'Reversión de Entrada (Anulación)'])) {
                    $total_salidas += $cantidad;
                }
            }
        }
        ?>

        <section class="content-header">
            <h1>Kardex - Movimientos de Inventario</h1>
        </section>

        <section class="content">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <a href="farmacia_inventario_listado.php" style="height:31px;" class="btn-sm btn-primary pull-right"><i class="fa fa-book"></i> Ir al Inventario</a>
                    
                    <p class="pull-right" style="width:5px;"></p>
                    <?php if (in_array('Generar reporte de kardex', $_SESSION["permisos"])) : ?>
                    <button class="btn-sm btn-info pull-right" id="btnGenerarReporteDirecto"><i class="fa fa-file-pdf-o"></i>Generar Reporte</button>
                    <?php endif; ?>
                    
                    <div class="pull-left form-inline">
                    <form method="GET" action="" id="formBusquedaRapida">
                        <input type="text" name="buscar" id="buscar" class="form-control" placeholder="Buscar medicamento, lote o proveedor..." value="<?php echo htmlspecialchars($busqueda); ?>" style="border-radius:0; height:10%; width:250px; display:inline-block;" autocomplete="off">
                    </form>
                    </div>

                    <p class="pull-right" style="width:5px;"></p>
                    <span data-toggle="tooltip" data-placement="right" title="Aqui podras filtrar de manera avanzada la busqueda de los movimientos en el inventario.">
                        <button type="button" class="btn-sm btn-primary btn-sm pull-left" data-toggle="modal" data-target="#modalBusquedaAvanzadaKardex">
                            <i class="fa fa-filter"><img src="../../recursos/imagenes/iconos/filtrar.png" style="width:10px; height:10px; filter:invert(1);" title="filtrar receta"></i>
                        </button>
                    </span> 
                </div>
                <br><br>

                <div class="box-body">
                    <div id="contenedorTabla">
                        <table class="table table-sm table-hover mt-4" width="100%" id="t_user">
                            <thead class="table-dark" style="background-color: #222; color: white; font-size: 12px;">
                                <tr>
                                    <th>Medicamento</th>   
                                    <th>Lote</th>
                                    <th>Proveedor</th>      
                                    <th>Cantidad</th>
                                    <th>(Stock)</th>
                                    <th>Fecha</th>
                                    <th>Observaciones</th>
                                    <th>Movimiento</th>
                                </tr>
                            </thead>
                            <tbody id="tbody_kardex">
                                <?php if ($mostrar_vacio): ?>
                                    <tr class="crud">
                                        <td colspan="8" class="text-center" style="padding: 30px; font-size: 16px; color: #666;">
                                            <i class="fa fa-search text-muted fa-2x"></i><br><br>
                                            Empiece a escribir o utilice los filtros para visualizar el Kardex.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php if ($resultado->num_rows > 0): ?>
                                        <?php while ($row = $resultado->fetch_assoc()) : ?>
                                            <tr>
                                                <td><small class="text-row text-black"><?= htmlspecialchars($row['nombre_medicamento'] . " (" . $row['componentes'] . ")"); ?></small></td>
                                                <td><small class="text-row text-black"><?php echo $row['Lote']; ?></small></td>
                                                <td><small class="text-row text-black"><?php echo $row['nombre_proveedor']; ?></small></td>             
                                                <td><small class="text-row text-black"><?php echo $row['cantidad']; ?></small></td>
                                                <td><small class="text-row text-black"><?php echo $row['stock_momento']; ?></small></td>
                                                <td><small class="text-row text-black"><?php echo date('d/m/Y H:i', strtotime($row['fecha'])); ?></td>
                                                <td><small class="text-row text-black"><?php echo $row['observaciones']; ?></small></td>
                                                <td>
                                                    <span class="badge <?php echo ($row['tipo'] == 'Entrada' OR $row['tipo'] == 'Ajuste por Cuadre (Entrada)') ? 'bg-green' : 'bg-crimson'; ?>">
                                                        <?php echo $row['tipo']; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-danger" style="padding: 20px;">No se encontraron movimientos con los filtros aplicados.</td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="kardex-footer-bar" id="kardex_footer_bar">
                        
                        <div id="pagination_container">
                            <?php if (!$mostrar_vacio && $total_paginas > 0): ?>
                            <nav aria-label="Page navigation" style="text-align: left;">
                                <ul class="pagination">
                                    <?php
                                    // Preservar los parámetros GET para la paginación
                                    $params = $_GET;
                                    unset($params['pagina']);
                                    $query_string = '&' . http_build_query($params);

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
                            <?php endif; ?>
                        </div>

                        <div id="resumen_container" style="font-size: 15px;">
                            <?php if (!$mostrar_vacio && $resultado->num_rows > 0): ?>
                                <span style="margin-left: 15px;"><i class="fa fa-arrow-up text-success"></i> Entradas Totales: <strong style="color:#2ecc71; font-size: 16px;"><?php echo $total_entradas ?: 0; ?></strong></span>
                                <span style="margin-left: 15px; border-left: 1px solid #555; padding-left: 15px;"><i class="fa fa-arrow-down text-danger"></i> Salidas Totales: <strong style="color:#e74c3c; font-size: 16px;"><?php echo $total_salidas ?: 0; ?></strong></span>
                                <span style="margin-left: 15px; border-left: 1px solid #555; padding-left: 15px;"><i class="fa fa-cube text-warning"></i> Variación: <strong style="color:#f39c12; font-size: 16px;"><?php echo (($total_entradas ?: 0) - ($total_salidas ?: 0)); ?></strong></span>
                                <span style="margin-left: 15px; border-left: 1px solid #555; padding-left: 15px;"><i class="fa fa-exchange text-info"></i> Movimientos: <strong style="color:#3498db; font-size: 16px;"><?php echo $total_registros ?: 0; ?></strong></span>
                                <span style="margin-left: 15px; border-left: 1px solid #555; padding-left: 15px;"></span>
                            <?php endif; ?>
                        </div>

                    </div>

                </div>
            </div>
        </section>
    </div>

    <div class="modal fade" id="modalBusquedaAvanzadaKardex" tabindex="-1" role="dialog" aria-labelledby="modalFiltrosLabel">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <form id="formBusquedaAvanzada" method="GET" action="">
            <div class="modal-header bg-primary">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title" id="modalFiltrosLabel"><i class="fa fa-filter"></i> Filtros Avanzados de Kardex</h4>
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
                    while($tp = $res_tipos->fetch_assoc()){
                        $sel = ($f_tipo_mov == $tp['nombre']) ? 'selected' : '';
                        echo "<option value='".htmlspecialchars($tp['nombre'])."' $sel>".htmlspecialchars($tp['nombre'])."</option>";
                    }
                    ?>
                  </select>
                </div>
              </div>

              <div class="row">
                <div class="col-md-4 form-group">
                  <label>Medicamento</label>
                  <input type="text" name="buscar" class="form-control" placeholder="Nombre..." oninput="this.value = this.value.replace(/[0-9]/g, '');" value="<?php echo htmlspecialchars($busqueda); ?>">
                </div>
                <div class="col-md-4 form-group">
                  <label>Lote</label>
                  <input type="text" name="f_lote" class="form-control" placeholder="Ej: LOTE-XYZ" value="<?php echo htmlspecialchars($f_lote); ?>">
                </div>
                <div class="col-md-4 form-group">
                  <label>Proveedor</label>
                  <input type="text" name="f_proveedor" class="form-control" placeholder="Nombre de proveedor..." oninput="this.value = this.value.replace(/[0-9]/g, '');" value="<?php echo htmlspecialchars($f_proveedor); ?>">
                </div>
              </div>

              <div class="row">
                <div class="col-md-3 form-group">
                  <label>Cantidad Movimiento Mín.</label>
                  <input type="number" name="f_cant_min" class="form-control" min="1" placeholder="Ej: 1" value="<?php echo $f_cant_min > 0 ? $f_cant_min : ''; ?>">
                </div>
                <div class="col-md-3 form-group">
                  <label>Cantidad Movimiento Máx.</label>
                  <input type="number" name="f_cant_max" class="form-control" min="1" placeholder="Ej: 500" value="<?php echo $f_cant_max > 0 ? $f_cant_max : ''; ?>">
                </div>
                <div class="col-md-3 form-group">
                  <label>Stock Momento Mínimo</label>
                  <input type="number" name="f_stock_min" class="form-control" min="0" placeholder="Ej: 0" value="<?php echo $f_stock_min > 0 ? $f_stock_min : ''; ?>">
                </div>
                <div class="col-md-3 form-group">
                  <label>Stock Momento Máximo</label>
                  <input type="number" name="f_stock_max" class="form-control" min="0" placeholder="Ej: 1000" value="<?php echo $f_stock_max > 0 ? $f_stock_max : ''; ?>">
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default pull-left" onclick="limpiarFiltrosAjax()">Limpiar Filtros</button>
              <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
              <button type="submit" class="btn btn-success"><i class="fa fa-search"></i> Aplicar Búsqueda</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="modal" id="ModalAdvertencia" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-crimson">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Atención</h4>
                </div>
                <div class="modal-body text-center">
                    <p style="font-size: 16px; margin-top: 10px;">Debe aplicar al menos un filtro de búsqueda para generar el reporte de Kardex.</p>
                </div>
                <div class="modal-footer text-center">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Entendido</button>
                </div>
            </div>
        </div>
    </div>

    <?php include('includes/footer.php'); ?>

    <script>
        $(document).ready(function() {

            // ==========================================
            // LÓGICA AJAX PARA BÚSQUEDA Y PAGINACIÓN EN VIVO
            // ==========================================
            window.cargarDatosAjax = function(url) {
                $('#tbody_kardex').css('opacity', '0.4');

                $.get(url, function(data) {
                    var htmlParsed = $(data);
                    
                    // Inyectamos las partes necesarias de la vista actual sin recargar
                    $('#tbody_kardex').html(htmlParsed.find('#tbody_kardex').html()).css('opacity', '1');
                    $('#pagination_container').html(htmlParsed.find('#pagination_container').html());
                    $('#resumen_container').html(htmlParsed.find('#resumen_container').html());

                    // Actualizar URL del navegador silenciosamente
                    window.history.pushState(null, '', url);
                }).fail(function() {
                    alert("Error de conexión al aplicar filtros.");
                    $('#tbody_kardex').css('opacity', '1');
                });
            };

            // Búsqueda Rápida con KeyUp
            let timer;
            $('#buscar').on('keyup', function() {
                clearTimeout(timer);
                let query = $(this).val();
                timer = setTimeout(function() {
                    var url = 'farmacia_inventario_kardex.php?buscar=' + encodeURIComponent(query);
                    cargarDatosAjax(url);
                }, 400); 
            });

            // Evitar que el ENTER en búsqueda rápida recargue la página entera
            $('#formBusquedaRapida').on('submit', function(e) {
                e.preventDefault();
            });

            // Interceptar Búsqueda Avanzada
            $('#formBusquedaAvanzada').on('submit', function(e) {
                e.preventDefault();
                var url = 'farmacia_inventario_kardex.php?' + $(this).serialize();
                $('#modalBusquedaAvanzadaKardex').modal('hide');
                cargarDatosAjax(url);
            });

            // Interceptar paginación
            $(document).on('click', '#pagination_container .pagination a', function(e) {
                e.preventDefault();
                var url = $(this).attr('href');
                if (url) {
                    cargarDatosAjax(url);
                }
            });

            // Limpiar filtros via AJAX
            window.limpiarFiltrosAjax = function() {
                $('#formBusquedaAvanzada')[0].reset();
                $('#buscar').val('');
                var urlLimpia = window.location.href.split('?')[0]; 
                cargarDatosAjax(urlLimpia);
                $('#modalBusquedaAvanzadaKardex').modal('hide');
            };

            // ==========================================
            // LÓGICA DE GENERACIÓN DE REPORTE
            // ==========================================
            $('#btnGenerarReporteDirecto').on('click', function(e) {
                e.preventDefault();
                
                // Extraer el query string actual directamente de la barra de direcciones del navegador
                var queryString = window.location.search;
                
                // Si está vacío y no hay ningún parámetro aplicado, lanzamos advertencia.
                // También verificamos que al menos 'buscar' o algún 'f_' tenga valor.
                if(queryString === '' || queryString === '?buscar=') {
                    $('#ModalAdvertencia').modal('show');
                } else {
                    // Abrimos el PDF enviándole todos los parámetros activos en este momento
                    window.open('../../cfg/reportes/generar_pdf_kardex.php' + queryString, '_blank');
                }
            });

        });
    </script>
</body>

</html>