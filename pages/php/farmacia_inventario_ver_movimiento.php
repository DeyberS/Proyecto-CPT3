<?php
include("../../cfg/conexion.php");

$id_movimiento = isset($_GET['id']) ? intval($_GET['id']) : 0;

$sql_cabecera = "SELECT 
            di.*,
            tm.nombre as tipo_nom,
            u.nombre as usuario_nombre, u.apellido as usuario_apellido, u.tipo_cedula as usuario_tipo_cedula, u.cedula as usuario_cedula,
            rec.nombre as rec_nombre, rec.apellido as rec_apellido, rec.tipo_cedula as rec_tipo_cedula, rec.cedula as rec_cedula,
            pac.nombre as pac_nombre, pac.apellido as pac_apellido, pac.tipo_cedula as pac_tipo_cedula, pac.cedula as pac_cedula,
            rep.nombre as rep_nombre, rep.apellido as rep_apellido, rep.tipo_cedula as rep_tipo_cedula, rep.cedula as rep_cedula
        FROM detalle_inventario di
        INNER JOIN persona u ON di.Id_Persona = u.id 
        LEFT JOIN persona rec ON di.Id_receptor = rec.id
        INNER JOIN tipo_movimiento tm ON di.Id_TipoMovimiento = tm.Id_tipo_movimiento
        LEFT JOIN detalle_solicitud ds ON di.id_detalle_solicitud = ds.id_detalle
        LEFT JOIN solicitud_medicamento sm ON ds.id_solicitud = sm.id_solicitud
        LEFT JOIN persona pac ON sm.id_paciente = pac.id
        LEFT JOIN historial_representantes hr ON pac.id = hr.Id_paciente AND hr.estatus='1' AND hr.tipo_representante='Legal'
        LEFT JOIN persona rep ON hr.Id_representante = rep.id
        WHERE di.Id_detalle_inventario = '$id_movimiento' LIMIT 1";

$res_cabecera = mysqli_query($conexion, $sql_cabecera);

// Si la consulta falla, mostrar el error para depurar (quitar en producción)
if (!$res_cabecera) {
    die("Error en la consulta: " . mysqli_error($conexion));
}

if (mysqli_num_rows($res_cabecera) == 0) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['mensaje_user_error'] = "El movimiento #$id_movimiento no fue encontrado en la base de datos.";
    header("Location: farmacia_inventario_listado.php");
    exit();
}

$mov = mysqli_fetch_assoc($res_cabecera);

$sql_detalles = "SELECT 
            mdi.cantidad, 
            mdi.observacion,
            m.nombre_medicamento, 
            tpm.nombre_presentacion,
            GROUP_CONCAT(DISTINCT CONCAT(IFNULL(pa.nombre,''), ' ', IFNULL(dpmd.cantidad_unidad_medida,''), IFNULL(um.unidad,'')) SEPARATOR ' + ') AS componentes,
            l.Lote as nombre_lote,
            l.fecha_vencimiento
        FROM medicamentos_detalle_inventario mdi
        INNER JOIN descripcion_medicamento dm ON mdi.Id_descripcion_medicamento = dm.Id
        INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
        INNER JOIN presentacion tpm ON dm.Id_presentacion = tpm.Id_presentacion
        INNER JOIN lotes_medicamentos l ON mdi.Id_lote = l.Id
        LEFT JOIN detalle_principio_medicamento dpmd ON dm.Id = dpmd.id_descripcion_medicamento
        LEFT JOIN unidad_medida um ON dpmd.id_tipo_unidad_medida = um.Id_unidad_medida
        LEFT JOIN principio_activo pa ON dpmd.id_principio_activo = pa.Id_principio_activo
        WHERE mdi.Id_detalle_inventario = '$id_movimiento'
        GROUP BY mdi.Id";

$res_detalles = mysqli_query($conexion, $sql_detalles);

$detalles_array = [];
$mostrar_observacion = false;

if ($res_detalles && mysqli_num_rows($res_detalles) > 0) {
    while ($row = mysqli_fetch_assoc($res_detalles)) {
        if (!empty(trim($row['observacion']))) {
            $mostrar_observacion = true;
        }
        $detalles_array[] = $row;
    }
}

$esEntrada = (strcasecmp($mov['tipo_nom'], 'Entrada') == 0 or strcasecmp($mov['tipo_nom'], 'Ajuste por Cuadre (Entrada)') == 0);
$tipo_label = strtoupper($mov['tipo_nom']) . ' DE INVENTARIO';
$tipo_bg = $esEntrada ? '#27ae60' : '#e74c3c';
$simbolo = $esEntrada ? '+' : '-';
$comprobante_raw = $mov['comprobante'] ?? '';
$rutas_evidencias_raw = [];
$rutas_evidencias = [];

if (!empty($comprobante_raw)) {
    $decodificado = json_decode($comprobante_raw, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decodificado)) {
        $rutas_evidencias_raw = $decodificado;
    } else {
        $rutas_evidencias_raw = [$comprobante_raw];
    }
}

foreach ($rutas_evidencias_raw as $ruta) {
    if (!empty($ruta)) {
        $pos = strpos($ruta, 'recursos/');
        if ($pos !== false) {
            $ruta_relativa = substr($ruta, $pos);
            $rutas_evidencias[] = '../../' . $ruta_relativa;
        } else {
            $rutas_evidencias[] = $ruta;
        }
    }
}

include('includes/headerNav2.php');
verificarAcceso('Ver Inventario');
?>

<!-- El resto del HTML y CSS se mantiene igual -->

<head>
    <title>Movimiento | Informacion</title>
</head>

<div class="content-wrapper">
    <style>
        :root {
            --primary: #2c3e50;
            --accent: #3498db;
            --bg: #f4f7f6;
        }

        /* ============================================================
       BASE (todos los dispositivos)
       ============================================================ */
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: var(--bg) !important;
        }

        .wrapper,
        .content-wrapper {
            background-color: #f4f7f9 !important;
            min-height: 100vh;
        }

        .content-custom {
            padding: 20px 10px;
            margin-left: 0;
            box-sizing: border-box;
        }

        .receipt-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin: 10px auto;
            border: none;
            display: flex;
            flex-direction: column;
            max-height: 92vh;
            max-width: min(112vw, 1600px);
            width: 100%;
        }

        .receipt-header {
            background: var(--primary);
            color: white;
            padding: 20px 30px;
            border-bottom: 5px solid <?php echo $tipo_bg; ?>;
            position: relative;
            flex-shrink: 0;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .receipt-header h1,
        .receipt-header h2,
        .receipt-header h3,
        .receipt-header h4 {
            margin: 0;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .card-body {
            padding: 20px 30px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            min-height: 0;
            /* necesario para que flex-grow funcione */
        }

        .status-badge {
            position: absolute;
            top: 25px;
            right: 30px;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 800;
            background-color: <?php echo $tipo_bg; ?>;
            color: white;
            white-space: nowrap;
        }

        .info-entrega {
            background: #ebf5fb;
            border-radius: 10px;
            padding: 20px;
            border: 1px solid #d6eaf8;
            height: 100%;
        }

        .label-custom {
            font-size: 11px;
            color: #95a5a6;
            text-transform: uppercase;
            font-weight: 700;
            display: block;
        }

        .val-custom {
            font-size: 16px;
            color: #2c3e50;
            font-weight: 500;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .table-scroll-container {
            max-height: clamp(200px, 32vh, 420px);
            overflow-y: auto;
            border-bottom: 1px solid #eee;
            margin-bottom: 15px;
        }

        .table-scroll-container::-webkit-scrollbar {
            width: 6px;
        }

        .table-scroll-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 5px;
        }

        .table-scroll-container::-webkit-scrollbar-thumb {
            background: #bdc3c7;
            border-radius: 5px;
        }

        .table-scroll-container::-webkit-scrollbar-thumb:hover {
            background: #95a5a6;
        }

        .table-productos {
            margin-bottom: 0;
        }

        .table-productos th {
            background-color: #f8f9fa;
            color: var(--primary);
            font-size: 13px;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .table-productos td {
            vertical-align: middle !important;
            font-size: 14px;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .qty-badge {
            font-size: 16px;
            font-weight: bold;
            color: <?php echo $tipo_bg; ?>;
            background: #fdfdfd;
            padding: 5px 10px;
            border: 1px solid #eee;
            border-radius: 5px;
            display: inline-block;
            white-space: nowrap;
        }

        .img-strip-btn {
            position: relative;
            width: 100%;
            height: 50px;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            border: 2px dashed #ccc;
            background-color: #ecf0f1;
            transition: all 0.3s;
        }

        .img-strip-btn:hover {
            border-color: #3498db;
        }

        .img-strip-bg {
            position: absolute;
            top: -20px;
            left: -20px;
            right: -20px;
            bottom: -20px;
            background-size: cover;
            background-position: center;
            filter: blur(6px);
            opacity: 0.6;
            z-index: 1;
        }

        .img-strip-text {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: var(--primary);
            font-size: 15px;
            text-shadow: 1px 1px 3px rgba(255, 255, 255, 0.8);
        }

        .print-only {
            display: none !important;
        }

        /* ============================================================
       TABLET (≤ 991 px)
       ============================================================ */
        @media (max-width: 991px) {
            .content-custom {
                padding: 15px 8px;
            }

            .receipt-header {
                padding: 18px 20px;
            }

            .card-body {
                padding: 15px 20px;
            }

            .status-badge {
                top: 15px;
                right: 20px;
                padding: 8px 14px;
                font-size: 12px;
            }

            .info-entrega {
                padding: 15px;
            }
        }

        /* ============================================================
       MÓVIL (≤ 767 px)
       ============================================================ */
        @media (max-width: 767px) {

            /* Permitir scroll vertical natural: el recibo puede crecer */
            html,
            body {
                overflow-x: hidden !important;
                overflow-y: auto !important;
                height: auto !important;
            }

            .wrapper,
            .content-wrapper {
                min-height: auto !important;
            }

            .content-custom {
                padding: 10px 6px;
                margin-left: 0 !important;
                height: auto !important;
                overflow: visible !important;
            }

            .receipt-card {
                max-height: none !important;
                /* deja crecer al recibo */
                margin: 5px auto;
                border-radius: 8px;
            }

            /* Header: título arriba, badge debajo (sin absolute) */
            .receipt-header {
                padding: 15px;
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .receipt-header h1,
            .receipt-header h2,
            .receipt-header h3,
            .receipt-header h4 {
                font-size: 1.15em;
                line-height: 1.25;
            }

            .status-badge {
                position: static !important;
                padding: 6px 12px;
                font-size: 11px;
                align-self: flex-start;
            }

            .card-body {
                padding: 12px;
                overflow: visible;
            }

            .info-entrega {
                padding: 12px;
                height: auto;
                margin-bottom: 12px;
            }

            .label-custom {
                font-size: 10px;
            }

            .val-custom {
                font-size: 13px;
            }

            /* Tabla: más alta y con scroll interno más cómodo */
            .table-scroll-container {
                max-height: 240px;
            }

            .table-productos th {
                font-size: 11px;
                padding: 6px 4px;
            }

            .table-productos td {
                font-size: 12px;
                padding: 6px 4px;
            }

            .qty-badge {
                font-size: 13px;
                padding: 3px 7px;
            }

            .img-strip-btn {
                height: 42px;
                margin-top: 6px;
            }

            .img-strip-text {
                font-size: 12px;
            }

            /* Botones del recibo: full-width apilados */
            .receipt-card .no-print .btn,
            .receipt-card .no-print a.btn {
                display: block;
                width: 100%;
                margin: 4px 0 !important;
            }
        }

        /* ============================================================
       MÓVIL PEQUEÑO (≤ 360 px)
       ============================================================ */
        @media (max-width: 360px) {
            .content-custom {
                padding: 8px 4px;
            }

            .receipt-header {
                padding: 12px;
            }

            .card-body {
                padding: 10px;
            }

            .info-entrega {
                padding: 10px;
            }

            .table-productos th,
            .table-productos td {
                font-size: 10.5px;
                padding: 5px 3px;
            }

            .status-badge {
                font-size: 10px;
                padding: 5px 10px;
            }
        }

        /* ============================================================
       IMPRESIÓN
       ============================================================ */
        @media print {

            body,
            html,
            .wrapper,
            .content-wrapper,
            .receipt-card {
                height: auto !important;
                max-height: none !important;
                overflow: visible !important;
            }

            .table-scroll-container {
                max-height: none !important;
                overflow: visible !important;
            }

            .table-productos th {
                position: static;
            }

            .no-print {
                display: none !important;
            }

            .print-only {
                display: block !important;
            }

            .receipt-card {
                box-shadow: none;
                border: 1px solid #eee;
                margin: 0;
            }

            .img-comprobante-print {
                width: 100%;
                max-height: 250px;
                object-fit: contain;
                border: 2px solid #ccc;
                border-radius: 8px;
                padding: 5px;
            }
        }
    </style>

    <section class="content">
        <div class="receipt-card">
            <div class="receipt-header">
                <div class="status-badge"><i class="fa <?php echo $esEntrada ? 'fa-plus' : 'fa-minus'; ?>"></i> <?php echo $tipo_label; ?></div>
                <h2 style="margin: 0;">COMPROBANTE DE MOVIMIENTO</h2>
                <div class="row" style="margin-top: 15px;">
                    <div class="col-xs-4">
                        <span class="label-custom" style="color: #bdc3c7;">N° Registro</span>
                        <span style="font-size: 20px; font-weight: bold;">#<?php echo str_pad($mov['Id_detalle_inventario'], 6, "0", STR_PAD_LEFT); ?></span>
                    </div>
                    <div class="col-xs-4">
                        <span class="label-custom" style="color: #bdc3c7;">Fecha y Hora</span>
                        <span class="val-custom" style="color: #fff;"><?php echo date("d/m/Y h:i A", strtotime($mov['fecha'])); ?></span>
                    </div>
                    <div class="col-xs-4">
                        <span class="label-custom" style="color: #bdc3c7;">Atendido por</span>
                        <span class="val-custom" style="color: #fff;"><?php echo htmlspecialchars($mov['usuario_nombre'] . " " . $mov['usuario_apellido']); ?></span>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="row" style="flex-grow: 1;">

                    <div class="col-md-8">
                        <h4 style="color: var(--primary); border-bottom: 2px solid #eee; padding-bottom: 5px; margin-top:0;">
                            <i class="fa fa-pills"></i> Productos Movilizados
                        </h4>

                        <div class="table-scroll-container">
                            <table class="table table-hover table-bordered table-productos">
                                <thead>
                                    <tr>
                                        <th>Medicamento</th>
                                        <th>Presentación</th>
                                        <th>Lote</th>
                                        <th>Vence</th>
                                        <th class="text-center">Cant.</th>
                                        <?php if ($mostrar_observacion) : ?>
                                            <th>Observación</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($detalles_array)) : ?>
                                        <?php foreach ($detalles_array as $prod) : ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($prod['nombre_medicamento']); ?></strong><br>
                                                    <small class="text-muted"><?php echo !empty($prod['componentes']) ? htmlspecialchars($prod['componentes']) : 'Sin componentes'; ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($prod['nombre_presentacion']); ?></td>
                                                <td><?php echo htmlspecialchars($prod['nombre_lote']); ?></td>
                                                <td style="<?php echo (strtotime($prod['fecha_vencimiento']) < time()) ? 'color: red; font-weight:bold;' : ''; ?>">
                                                    <?php echo date("d/m/Y", strtotime($prod['fecha_vencimiento'])); ?>
                                                </td>
                                                <td class="text-center">
                                                    <span class="qty-badge"><?php echo $simbolo . " " . $prod['cantidad']; ?></span>
                                                </td>
                                                <?php if ($mostrar_observacion) : ?>
                                                    <td><?php echo !empty(trim($prod['observacion'])) ? htmlspecialchars($prod['observacion']) : '<span class="text-muted">-</span>'; ?></td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="<?php echo $mostrar_observacion ? '6' : '5'; ?>" class="text-center">No se encontraron medicamentos en este movimiento.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if (!empty($rutas_evidencias)) : ?>
                            <div class="form-group col-md-12">
                                <label><i class="fa fa-camera"></i> Evidencias / Soportes Digitales Adjuntos:</label>
                                <div style="display: flex; flex-wrap: wrap; gap: 12px; margin-top: 5px;">
                                    <?php foreach ($rutas_evidencias as $index => $ruta) : ?>
                                        <?php if (!empty($ruta)) : ?>
                                            <div class="thumbnail-evidencia" style="border: 2px solid #ddd; padding: 3px; border-radius: 6px; background: #fff; cursor: pointer; transition: transform 0.2s;" data-toggle="modal" data-target="#modalVerImagen" data-src="<?php echo $ruta; ?>" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                                <img src="<?php echo $ruta; ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 4px;">
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-4">
                        <div class="info-entrega">
                            <h4 style="border-bottom: 2px solid #d6eaf8; padding-bottom: 10px; color: #2980b9; margin-top:0;">
                                <i class="fa fa-user-md"></i> Detalles del Destino
                            </h4>

                            <?php if (!empty($mov['rec_nombre'])) : ?>
                                <div style="background: #fff; padding: 15px; border-radius: 8px; border-left: 5px solid #8e44ad; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 15px;">
                                    <span class="label-custom" style="color: #8e44ad;">Receptor / Responsable Asignado</span>
                                    <span class="val-custom"><strong><?php echo htmlspecialchars($mov['rec_nombre'] . " " . $mov['rec_apellido']); ?></strong></span><br>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($mov['pac_nombre'])) : ?>
                                <div style="background: #fff; padding: 15px; border-radius: 8px; border-left: 5px solid #00a65a; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                                    <span class="label-custom">Paciente</span>
                                    <span class="val-custom"><strong><?php echo htmlspecialchars($mov['pac_nombre'] . " " . $mov['pac_apellido']); ?></strong></span><br>
                                    <small class="text-muted">C.I: <?php echo htmlspecialchars($mov['pac_tipo_cedula'] . "-" . $mov['pac_cedula']); ?></small>
                                </div>
                                <br>
                                <?php if (!empty($mov['rep_nombre'])) : ?>
                                    <div style="background: #fff; padding: 15px; border-radius: 8px; border-left: 5px solid #f39c12; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                                        <span class="label-custom" style="color: #e67e22;">Representante Autorizado</span>
                                        <span class="val-custom"><strong><?php echo htmlspecialchars($mov['rep_nombre'] . " " . $mov['rep_apellido']); ?></strong></span><br>
                                        <small class="text-muted">C.I: <?php echo htmlspecialchars($mov['rep_tipo_cedula'] . "-" . $mov['rep_cedula']); ?></small>
                                    </div>
                                <?php endif; ?>
                            <?php elseif (empty($mov['rec_nombre'])) : ?>
                                <div class="text-center" style="padding: 20px 10px;">
                                    <i class="fa fa-truck fa-3x" style="opacity: 0.2; color: #2c3e50;"></i>
                                    <p style="margin-top: 15px;">Este registro corresponde a un movimiento de <strong>Stock</strong>.</p>
                                </div>
                            <?php endif; ?>

                            <div style="margin-top: 20px; border-top: 1px solid #d6eaf8; padding-top: 10px;">
                                <span class="label-custom">Observaciones Generales</span>
                                <p style="font-size: 13px; font-style: italic; color: #555;">
                                    "<?php echo (!empty($mov['observaciones']) && trim($mov['observaciones']) != '') ? htmlspecialchars($mov['observaciones']) : 'Sin observaciones adicionales registradas.'; ?>"
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row no-print" style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 15px;">
                    <div class="col-xs-12">
                        <button type="button" style="margin-left: 5px;" class="btn btn-primary pull-right" onclick="window.print();">
                            <i class="fa fa-print"></i> Imprimir
                        </button>

                        <button type="button" class="btn btn-second pull-right" data-toggle="modal" data-target="#modalConfirmarRegreso">
                            <i class="fa fa-chevron-left"></i> Regresar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="modalConfirmarRegreso" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarRegreso">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-crimson">
                    <h4 class="modal-title" id="modalConfirmarRegresoLabel"><i class="fa fa-warning"></i> Confirmación de Regreso</h4>
                </div>
                <div class="modal-body">
                    <p style="font-size: 16px;">Está a punto de regresar al listado. ¿Desea continuar?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
                    <a href="farmacia_inventario_listado.php" class="btn btn-danger">Regresar al Inicio</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalVerImagen" tabindex="-1" role="dialog" aria-labelledby="modalVerImagenLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h4 class="modal-title" id="modalVerImagenLabel"><i class="fa fa-camera"></i> Soporte Digital del Movimiento</h4>
                </div>
                <div class="modal-body text-center" style="background: #f4f4f4;">
                    <img id="img-modal-visor" src="" class="img-responsive" style="margin: 0 auto; max-height: 75vh; border: 3px solid #fff; box-shadow: 0 4px 15px rgba(0,0,0,0.15); border-radius: 5px;">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

</div>

<?php include('includes/footer.php'); ?>

<script>
    $(document).ready(function() {
        $('.thumbnail-evidencia').on('click', function() {
            var rutaImagen = $(this).data('src');
            $('#img-modal-visor').attr('src', rutaImagen);
        });
    });
</script>