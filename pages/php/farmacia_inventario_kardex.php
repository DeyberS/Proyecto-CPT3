<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Farmacia | Kardex de Medicamentos</title>
    <?php
    include('includes/headerNav2.php');
    include("../../cfg/conexion.php");

    // Parámetro para filtrar por nombre de medicamento usando el input
    $med_search = isset($_GET['med_search']) ? trim($_GET['med_search']) : '';
    $mostrar_vacio = empty($med_search);
    ?>
</head>
<style>
    /* Replicando tus animaciones exactas de farmacia_inventario_listado.php */
    @keyframes pulse-opacity {
        0% {
            opacity: 0;
        }

        100% {
            opacity: 1;
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-50px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeOut {
        from {
            opacity: 1;
            transform: translateY(0);
        }

        to {
            opacity: 0;
            transform: translateY(-50px);
        }
    }

    .modal.in .modal-dialog,
    #ModalAdvertencia,
    #ModalReporteKardex {
        animation: fadeIn 0.4s ease-out;
    }

    .modal-open .modal-backdrop {
        opacity: 0.7 !important;
        animation: pulse-opacity 0.3s forwards;
    }

    .table-kardex thead {
        background-color: #f4f4f4;
    }

    /* NUEVO: Estilos para la barra negra alargada y fija en la parte inferior */
    .kardex-footer-bar {
        background-color: #222;
        color: #fff;
        height: 30px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: fixed;
        bottom: 0; /* Lo mantiene pegado abajo de forma alargada */
        z-index: 100;
        box-shadow: 0 -4px 10px rgba(0,0,0,0.4);  
        width: 83%;
    }

    /* Paginación adaptada al fondo negro */
    #pagination_container .pagination {
        margin: 0;
    }
    #pagination_container .pagination > li > a {
        background-color: #333 !important;
        border-color: #444 !important;
        color: #fff !important;
    }
    #pagination_container .pagination > .active > a {
        background-color: #222 !important;
        border-color: #222 !important;
    }
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
            $med_search_esc = $conexion->real_escape_string($med_search);
            $donde = " WHERE m.nombre_medicamento = '$med_search_esc'";

            // 1. Conteo para la paginación filtrado
            $sql_conteo = "SELECT COUNT(*) as total 
                   FROM medicamentos_detalle_inventario mdi
                   JOIN detalle_inventario di ON mdi.Id_detalle_inventario = di.Id_detalle_inventario
                   JOIN descripcion_medicamento dm ON mdi.Id_descripcion_medicamento = dm.Id
                   JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
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
                            JOIN tipo_movimiento tm ON di.Id_tipoMovimiento = tm.Id_tipo_movimiento
                            $donde
                            GROUP BY tm.nombre";

                $res_totales = $conexion->query($sql_totales);

                $total_entradas = 0;
                $total_salidas = 0;

                while ($tot = $res_totales->fetch_assoc()) {
                    $tipo = $tot['tipo'];
                    $cantidad = $tot['total_cantidad'];

                    // Movimientos que suman al inventario
                    if (in_array($tipo, ['Entrada', 'Ajuste por Cuadre (Entrada)', 'Reversión de Salida (Anulación)'])) {
                        $total_entradas += $cantidad;
                    }
                    // Movimientos que restan al inventario
                    elseif (in_array($tipo, ['Salida por Despacho', 'Salida por Vencimiento', 'Salida por Dañado', 'Salida por Pérdida o Robo', 'Ajuste por Cuadre (Salida)', 'Reversión de Entrada (Anulación)'])) {
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
                    <a href="farmacia_inventario_listado.php" class="btn-sm btn-primary pull-right"><i class="fa fa-book"></i> Ir al Inventario</a>
                    <p class="pull-right" style="width:5px;"></p>
                    <?php if (in_array('Generar reporte de kardex', $_SESSION["permisos"])) : ?>
                    <a href="#" class="btn-sm btn-info pull-right reporte"><i class="fa fa-book"></i> Reporte Kardex</a>
                    <?php endif; ?>
                    
                    <div class="pull-left form-inline">
                        <div class="form-group">
                            <input type="text" list="lista_medicamentos" name="med_search" id="med_search_input" class="form-control" placeholder="Buscar medicamento por nombre..." value="<?php echo htmlspecialchars($med_search); ?>" style="width: 350px;" required autocomplete="off">
                            <datalist id="lista_medicamentos">
                                <?php
                                // Cargamos solo medicamentos que tengan movimientos registrados
                                $sql_lista = "SELECT DISTINCT m.nombre_medicamento FROM medicamento m 
                                              JOIN descripcion_medicamento dm ON m.Id_medicamento = dm.Id_medicamento 
                                              JOIN medicamentos_detalle_inventario mdi ON dm.Id = mdi.Id_descripcion_medicamento";
                                $res_lista = $conexion->query($sql_lista);
                                while($rm = $res_lista->fetch_assoc()){
                                    echo "<option value='".htmlspecialchars($rm['nombre_medicamento'])."'>";
                                }
                                ?>
                            </datalist>
                        </div>
                    </div>
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
                                            Empiece a escribir el nombre del medicamento para visualizar su Kardex.
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
                                            <td colspan="8" class="text-center text-danger" style="padding: 20px;">No se encontraron movimientos para este medicamento.</td>
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
                                    $query_string = "&med_search=" . urlencode($med_search);

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
                                <span style="margin-left: 15px; border-left: 1px solid #555; padding-left: 15px;"><i class="fa fa-cube text-warning"></i> Stock Total: <strong style="color:#f39c12; font-size: 16px;"><?php echo (($total_entradas ?: 0) - ($total_salidas ?: 0)); ?></strong></span>
                                <span style="margin-left: 15px; border-left: 1px solid #555; padding-left: 15px;"><i class="fa fa-exchange text-info"></i> Movimientos: <strong style="color:#3498db; font-size: 16px;"><?php echo $total_registros ?: 0; ?></strong></span>
                                <span style="margin-left: 15px; border-left: 1px solid #555; padding-left: 15px;"></span>
                            <?php endif; ?>
                        </div>

                    </div>

                </div>
            </div>
        </section>
    </div>

    <div class="modal" id="ModalAdvertencia" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-crimson">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Atención</h4>
                </div>
                <div class="modal-body text-center">
                    <p style="font-size: 16px; margin-top: 10px;">Debe seleccionar o buscar un medicamento primero para poder generar su reporte de Kardex.</p>
                </div>
                <div class="modal-footer text-center">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Entendido</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="ModalReporteKardex" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Generar Reporte Kardex</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Fecha Desde:</label>
                        <input type="date" id="k_desde" class="form-control" value="<?php echo date('Y-m-01'); ?>" max="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label>Fecha Hasta:</label>
                        <input type="date" id="k_hasta" class="form-control" min="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="btnEjecutarKardex">Generar PDF</button>
                </div>
            </div>
        </div>
    </div>

    <?php include('includes/footer.php'); ?>

    <script>
        function closeCustomModal(modalElement) {
            modalElement.removeClass('in').addClass('out');
            setTimeout(() => {
                modalElement.modal('hide').removeClass('out');
            }, 100);
        }

        $('#ModalAdvertencia .close, #ModalAdvertencia .btn-secondary').on('click', function() {
            closeCustomModal($('#ModalAdvertencia'));
        });

        $('#ModalReporteKardex .close, #ModalReporteKardex .btn-second').on('click', function() {
            closeCustomModal($('#ModalReporteKardex'));
        });
        
        // Abrir el modal de reporte solo si hay un medicamento seleccionado
        $('.reporte').on('click', function(e) {
            e.preventDefault();
            var med = $('#med_search_input').val().trim();
            
            if(med === '') {
                // Si está vacío, mostramos la advertencia
                $('#ModalAdvertencia').modal('show');
            } else {
                // Si hay medicamento, mostramos las fechas
                $('#ModalReporteKardex').modal('show');
            }
        });

        // Generar el PDF enviando el parámetro del medicamento
        $('#btnEjecutarKardex').on('click', function() {
            var desde = $('#k_desde').val();
            var hasta = $('#k_hasta').val();
            var med = $('#med_search_input').val().trim();
            
            // Añadimos el parámetro &medicamento= a la URL
            window.open('../../cfg/reportes/generar_pdf_kardex.php?desde=' + desde + '&hasta=' + hasta + '&medicamento=' + encodeURIComponent(med), '_blank');
            
            // Opcional: Cerrar el modal después de darle click
            closeCustomModal($('#ModalReporteKardex'));
        });

        // -----------------------------------------------------
        // LÓGICA AJAX PARA BÚSQUEDA Y PAGINACIÓN EN VIVO
        // -----------------------------------------------------
        $(document).ready(function() {
            let timer;
            
            // Evento al escribir en el campo de búsqueda
            $('#med_search_input').on('keyup input', function() {
                clearTimeout(timer);
                let query = $(this).val();
                
                // Pequeño retraso de 350ms para no saturar el servidor con cada tecla
                timer = setTimeout(function() {
                    cargarDatos(query, 1);
                }, 350); 
            });

            // Interceptar los clics en los botones de paginación
            $(document).on('click', '#pagination_container .pagination a', function(e) {
                e.preventDefault();
                let url = new URL(this.href, window.location.origin);
                let page = url.searchParams.get("pagina") || 1;
                let query = $('#med_search_input').val();
                cargarDatos(query, page);
            });

            // Función encargada de hacer la llamada AJAX y reemplazar los componentes
            function cargarDatos(query, page) {
                $.ajax({
                    url: 'farmacia_inventario_kardex.php',
                    type: 'GET',
                    data: { med_search: query, pagina: page },
                    success: function(response) {
                        // Extraemos las partes del HTML que nos interesan y las reemplazamos
                        var htmlParsed = $(response);
                        $('#tbody_kardex').html(htmlParsed.find('#tbody_kardex').html());
                        $('#pagination_container').html(htmlParsed.find('#pagination_container').html());
                        $('#resumen_container').html(htmlParsed.find('#resumen_container').html());
                    }
                });
            }
        });
    </script>
</body>

</html>