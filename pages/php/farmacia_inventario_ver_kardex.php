<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Farmacia | Kardex de Medicamentos</title>
    <?php
    include('includes/headerNav2.php');
    include("../../cfg/conexion.php");

    // Parámetro para filtrar por un medicamento específico si se desea
    $id_desc_med = isset($_GET['id']) ? (int)$_GET['id'] : 0;
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
    #ModalReporteKardex {
        animation: fadeIn 0.4s ease-out;
    }

    .modal-open .modal-backdrop {
        opacity: 0.7 !important;
        animation: pulse-opacity 0.3s forwards;
    }

    /* Estilo de tabla acorde a tu diseño */
    .table-kardex thead {
        background-color: #f4f4f4;
    }
</style>

<body>
    <div class="content-wrapper">
        <?php
        // --- LÓGICA DE PAGINACIÓN (Basada en medico_listado.php) ---
        $registros_por_pagina = 8;
        $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $inicio = ($pagina_actual - 1) * $registros_por_pagina;

        // Consulta para contar el total de movimientos
        $sql_conteo = "SELECT COUNT(*) as total FROM medicamentos_detalle_inventario";
        $total_registros = $conexion->query($sql_conteo)->fetch_assoc()['total'];
        $total_paginas = ceil($total_registros / $registros_por_pagina);
        ?>

        <?php
        // 1. Primero corregimos el conteo para la paginación
        $donde = "";
        if ($id_desc_med > 0) {
            // Filtramos por el ID del medicamento que viene por GET
            $donde = " WHERE m.Id_medicamento = $id_desc_med";
        }

        $sql_conteo = "SELECT COUNT(*) as total 
               FROM medicamentos_detalle_inventario mdi
               JOIN descripcion_medicamento dm ON mdi.Id_descripcion_medicamento = dm.Id
               JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
               $donde";

        $total_registros = $conexion->query($sql_conteo)->fetch_assoc()['total'];
        $total_paginas = ceil($total_registros / $registros_por_pagina);

        // 2. Luego corregimos la consulta principal
        $sql_kardex = "SELECT 
        di.fecha, 
        m.nombre_medicamento, 
        dm.cantidad_unidad_medida, 
        l.Lote, 
        tm.nombre as tipo,
        mdi.cantidad, 
        mdi.stock_momento,
        di.observaciones
        FROM medicamentos_detalle_inventario mdi
        JOIN detalle_inventario di ON mdi.Id_detalle_inventario = di.Id_detalle_inventario
        JOIN descripcion_medicamento dm ON mdi.Id_descripcion_medicamento = dm.Id
        JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
        JOIN lotes_medicamentos l ON mdi.Id_lote = l.Id
        JOIN tipo_movimiento tm ON di.Id_tipoMovimiento = tm.Id_tipo_movimiento
        $donde
        ORDER BY di.fecha DESC 
        LIMIT $inicio, $registros_por_pagina";

        $resultado = $conexion->query($sql_kardex);
        ?>

        <section class="content-header">
            <h1>Kardex - Movimientos de Inventario</h1>
        </section>

        <section class="content">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <a href="#" class="btn-sm btn-info pull-right reporte"><i class="fa fa-book"></i>Reporte Kardex</a>
                    <p class="pull-right" style="width:5px;"></p>
                    <a href="farmacia_inventario_listado.php" class="btn-sm btn-primary pull-right"><i class="fa fa-book"></i>Volver al Listado</a>
                    <input type="text" placeholder="Buscar.." class="form-control pull-left" id="buscar" onkeyup="filtro()" style="width: 200px;">
                </div>
                <br><br>

                <div class="box-body">
                    <div id="contenedorTabla">
                        <table class="table table-sm table-hover mt-4" width="100%" id="t_user">
                            <thead class="table-dark" style="background-color: #222; color: white; font-size: 12px;">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Medicamento</th>
                                    <th>Lote</th>
                                    <th>Movimiento</th>
                                    <th>Cantidad</th>
                                    <th>(Stock)</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $resultado->fetch_assoc()) : ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y H:i', strtotime($row['fecha'])); ?></td>
                                        <td><?php echo $row['nombre_medicamento'] . " (" . $row['cantidad_unidad_medida'] . ")"; ?></td>
                                        <td><?php echo $row['Lote']; ?></td>
                                        <td>
                                            <span class="label <?php echo ($row['tipo'] == 'Entrada') ? 'label-success' : 'label-danger'; ?>">
                                                <?php echo $row['tipo']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $row['cantidad']; ?></td>
                                        <td><strong><?php echo $row['stock_momento']; ?></strong></td>
                                        <td><?php echo $row['observaciones']; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <nav aria-label="Page navigation" style="position: fixed; bottom:0;">
                    <ul class="pagination">
                        <?php
                        // Mantener el ID del medicamento en los enlaces
                        $query_string = ($id_desc_med > 0) ? "&id=" . $id_desc_med : "";

                        // Botón Primero y Anterior
                        if ($pagina_actual > 1) : ?>
                            <li><a href="?pagina=1<?php echo $query_string; ?>" title="Primero">&laquo;&laquo;</a></li>
                            <li><a href="?pagina=<?php echo ($pagina_actual - 1) . $query_string; ?>">&laquo;</a></li>
                        <?php endif;

                        // --- CONFIGURACIÓN DE LA VENTANA DE NÚMEROS ---
                        $rango = 1;
                        $inicio_ventana = max(1, $pagina_actual - $rango);
                        $fin_ventana = min($total_paginas, $pagina_actual + $rango);

                        // Ajuste para mostrar siempre al menos 3 botones si existen
                        if ($pagina_actual == 1) $fin_ventana = min($total_paginas, 3);
                        if ($pagina_actual == $total_paginas) $inicio_ventana = max(1, $total_paginas - 2);

                        for ($i = $inicio_ventana; $i <= $fin_ventana; $i++) : ?>
                            <li class="<?php echo ($i == $pagina_actual) ? 'active' : ''; ?>">
                                <a href="?pagina=<?php echo $i . $query_string; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor;

                        // Botón Siguiente y Último
                        if ($pagina_actual < $total_paginas) : ?>
                            <li><a href="?pagina=<?php echo ($pagina_actual + 1) . $query_string; ?>">&raquo;</a></li>
                            <li><a href="?pagina=<?php echo $total_paginas . $query_string; ?>" title="Último">&raquo;&raquo;</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
        </section>
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
                        <input type="date" id="k_hasta" class="form-control" max="<?php echo date('Y-m-d'); ?>">
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
            }, 100); // Duración de la animación
        }

        $('#ModalReporteKardex .close, #ModalReporteKardex .btn-second').on('click', function() {
            closeCustomModal($('#ModalReporteKardex'));
        });
        // Funciones de modales replicadas
        $('.reporte').on('click', function() {
            $('#ModalReporteKardex').modal('show');
        });

        $('#btnEjecutarKardex').on('click', function() {
            var desde = $('#k_desde').val();
            var hasta = $('#k_hasta').val();
            window.open('../../cfg/reportes/generar_pdf_kardex.php?desde=' + desde + '&hasta=' + hasta, '_blank');
        });
    </script>
</body>

</html>