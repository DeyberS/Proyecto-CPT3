<?php
include("../../cfg/conexion.php");

$id_movimiento = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id_movimiento) {
    echo "Error: ID de movimiento no especificado.";
    exit;
}

// SQL Mejorado: Usamos LEFT JOIN en componentes y datos de paciente para que NADA bloquee la visualización
$sql = "SELECT 
            di.*, 
            mdi.cantidad, 
            m.nombre_medicamento, 
            tpm.nombre_presentacion,
            GROUP_CONCAT(DISTINCT CONCAT(IFNULL(pa.nombre,''), ' ', IFNULL(dpmd.cantidad_unidad_medida,''), IFNULL(um.unidad,'')) SEPARATOR ' + ') AS componentes,
            l.Lote as nombre_lote,
            l.fecha_vencimiento,
            u.nombre as usuario_nombre, u.apellido as usuario_apellido,
            pac.nombre as pac_nombre, pac.apellido as pac_apellido, pac.tipo_cedula as pac_tipo_cedula, pac.cedula as pac_cedula,
            rep.nombre as rep_nombre, rep.apellido as rep_apellido, rep.tipo_cedula as rep_tipo_cedula, rep.cedula as rep_cedula
        FROM detalle_inventario di
        INNER JOIN medicamentos_detalle_inventario mdi ON di.Id_detalle_inventario = mdi.Id_detalle_inventario
        INNER JOIN descripcion_medicamento dm ON mdi.Id_descripcion_medicamento = dm.Id
        INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
        INNER JOIN presentacion tpm ON dm.Id_presentacion = tpm.Id_presentacion
        INNER JOIN lotes_medicamentos l ON mdi.Id_lote = l.Id
        INNER JOIN persona u ON di.Id_persona = u.id 
        /* Relaciones opcionales para no perder el registro si faltan datos */
        LEFT JOIN detalle_principio_medicamento dpmd ON dm.Id = dpmd.id_medicamento
        LEFT JOIN unidad_medida um ON dpmd.id_tipo_unidad_medida = um.Id_unidad_medida
        LEFT JOIN principio_activo pa ON dpmd.id_principio_activo = pa.Id_principio_activo
        LEFT JOIN prescripcion_medicamentos pr ON di.Id_prescripcion = pr.Id
        LEFT JOIN consulta c ON pr.Id_consulta = c.Id_consulta
        LEFT JOIN persona pac ON c.Id_paciente = pac.id
        LEFT JOIN detalle_paciente_menor dpm_menor ON pac.id = dpm_menor.id_persona
        LEFT JOIN persona rep ON dpm_menor.id_representante = rep.id
        WHERE di.Id_detalle_inventario = '$id_movimiento'
        GROUP BY di.Id_detalle_inventario LIMIT 1";

$res = mysqli_query($conexion, $sql);

if ($res && mysqli_num_rows($res) > 0) {
    $mov = mysqli_fetch_assoc($res);
} else {
    echo "<div class='alert alert-danger'><h4><i class='icon fa fa-ban'></i> Error</h4>Movimiento #$id_movimiento no encontrado en la base de datos.</div>";
    exit;
}

$esEntrada = ($mov['Id_TipoMovimiento'] == '1');
$tipo_label = $esEntrada ? 'ENTRADA DE INVENTARIO' : 'SALIDA DE INVENTARIO';
$tipo_bg = $esEntrada ? '#27ae60' : '#e74c3c';

// Ajustar esta ruta según donde guardes tus fotos de comprobante
$ruta_comprobante = "../../recursos/comprobantes/" . $mov['comprobante'];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Comprobante #<?php echo $id_movimiento; ?></title>
    <?php include('includes/headerNav2.php'); ?>
    <style>
        :root {
            --primary: #2c3e50;
            --accent: #3498db;
            --bg: #f4f7f6;
        }

        body {
            background-color: var(--bg) !important;
            font-family: 'Segoe UI', sans-serif;
        }

        .receipt-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
            border: none;
            min-height: 600px;
        }

        .receipt-header {
            background: var(--primary);
            color: white;
            padding: 30px;
            border-bottom: 5px solid <?php echo $tipo_bg; ?>;
            position: relative;
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
        }

        .cantidad-container {
            background: #fcfcfc;
            border: 2px solid #eee;
            border-left: 8px solid <?php echo $tipo_bg; ?>;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .cantidad-valor {
            font-size: 40px;
            font-weight: 900;
            color: <?php echo $tipo_bg; ?>;
        }

        .info-entrega {
            background: #ebf5fb;
            border-radius: 10px;
            padding: 20px;
            border: 1px solid #d6eaf8;
            height: 100%;
        }

        .img-comprobante {
            width: 100%;
            max-height: 250px;
            object-fit: contain;
            border: 2px dashed #ccc;
            border-radius: 8px;
            padding: 5px;
            background: #fff;
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
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .receipt-card {
                box-shadow: none;
                border: 1px solid #eee;
            }
        }
    </style>
</head>

<body class="hold-transition skin-blue sidebar-mini">
    <div class="content-wrapper">
        <section class="content">
            <div class="receipt-card">
                <div class="receipt-header">
                    <div class="status-badge"><i class="fa <?php echo $esEntrada ? 'fa-plus' : 'fa-minus'; ?>"></i> <?php echo $tipo_label; ?></div>
                    <h2 style="margin: 0;">COMPROBANTE DE MOVIMIENTO</h2>
                    <div class="row" style="margin-top: 25px;">
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
                            <span class="val-custom" style="color: #fff;"><?php echo $mov['usuario_nombre'] . " " . $mov['usuario_apellido']; ?></span>
                        </div>
                    </div>
                </div>

                <div class="card-body" style="padding: 30px;">
                    <div class="row">
                        <div class="col-md-7">
                            <h3 style="color: var(--accent); margin-top: 0;"><strong><?php echo $mov['nombre_medicamento']; ?></strong></h3>
                            <p class="text-muted"><?php echo !empty($mov['componentes']) ? $mov['componentes'] : 'Sin componentes registrados'; ?></p>

                            <div class="cantidad-container">
                                <div>
                                    <span class="label-custom">Cantidad Movilizada</span>
                                    <span style="font-weight: bold;"><?php echo $mov['nombre_presentacion']; ?></span>
                                </div>
                                <div class="cantidad-valor"><?php echo $esEntrada ? '+' : '-'; ?> <?php echo $mov['cantidad']; ?></div>
                            </div>

                            <div class="row">
                                <div class="col-xs-6">
                                    <span class="label-custom">Lote</span>
                                    <span class="val-custom"><?php echo $mov['nombre_lote']; ?></span>
                                </div>
                                <div class="col-xs-6">
                                    <span class="label-custom">Vencimiento</span>
                                    <span class="val-custom" style="color: #c0392b;"><?php echo date("d/m/Y", strtotime($mov['fecha_vencimiento'])); ?></span>
                                </div>
                            </div>

                            <div style="margin-top: 25px;">
                                <span class="label-custom"><i class="fa fa-camera"></i> Foto del Comprobante (Soporte Digital)</span>
                                <?php if (!empty($mov['comprobante']) && file_exists($ruta_comprobante)) : ?>
                                    <img src="<?php echo $ruta_comprobante; ?>" class="img-comprobante" alt="Foto Comprobante">
                                <?php else : ?>
                                    <div class="text-center" style="border: 2px dashed #ccc; padding: 20px; border-radius: 8px; color: #999;">
                                        <i class="fa fa-image fa-3x"></i><br>Sin imagen de soporte adjunta
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="info-entrega">
                                <h4 style="border-bottom: 2px solid #d6eaf8; padding-bottom: 10px; color: #2980b9;">
                                    <i class="fa fa-user-md"></i> Destino del Medicamento
                                </h4>

                                <?php if (!empty($mov['pac_nombre'])) : ?>
                                    <div style="background: #fff; padding: 15px; border-radius: 8px; border-left: 5px solid #00a65a; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                                        <span class="label-custom">Paciente</span>
                                        <span class="val-custom"><strong><?php echo $mov['pac_nombre'] . " " . $mov['pac_apellido']; ?></strong></span><br>
                                        <small class="text-muted">C.I: <?php echo $mov['pac_tipo_cedula'] . "-" . $mov['pac_cedula']; ?></small>
                                    </div>
                                    <br>
                                    <?php if (!empty($mov['rep_nombre'])) : ?>
                                        <div style="background: #fff; padding: 15px; border-radius: 8px; border-left: 5px solid #f39c12; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                                            <span class="label-custom" style="color: #e67e22;">Representante Autorizado</span>
                                            <span class="val-custom"><strong><?php echo $mov['rep_nombre'] . " " . $mov['rep_apellido']; ?></strong></span><br>
                                            <small class="text-muted">C.I: <?php echo $mov['rep_tipo_cedula'] . "-" . $mov['rep_cedula']; ?></small>
                                        </div>
                                    <?php endif; ?>
                                <?php else : ?>
                                    <div class="text-center" style="padding: 30px 10px;">
                                        <i class="fa fa-truck fa-3x" style="opacity: 0.2; color: #2c3e50;"></i>
                                        <p style="margin-top: 15px;">Este registro corresponde a un movimiento de <strong>Stock Interno</strong>.</p>
                                    </div>
                                <?php endif; ?>

                                <div style="margin-top: 30px; border-top: 1px solid #d6eaf8; padding-top: 15px;">
                                    <span class="label-custom">Observaciones</span>
                                    <p style="font-size: 13px; font-style: italic; color: #555;">
                                        "<?php echo !empty($mov['observaciones']) ? $mov['observaciones'] : 'Sin observaciones adicionales registradas.'; ?>"
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row no-print" style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                        <div>
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
    </div>
    </section>

    <div class="modal fade" id="modalConfirmarRegreso" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarRegreso">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-crimson">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="modalConfirmarRegresoLabel"><i class="fa fa-warning"></i>Confirmación de Regreso</h4>
                </div>
                <div class="modal-body">
                    <p>Esta apunto de regresar al inicio. ¿Desea continuar?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
                    <a href="farmacia_inventario_listado.php" class="btn btn-danger">Regresar al Inicio</a>
                </div>
            </div>
        </div>
    </div>
    </div>
    <?php include('includes/footer.php'); ?>
</body>

</html>