<?php
include("../../cfg/conexion.php");

$id_movimiento = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id_movimiento) {
    echo "Error: ID de movimiento no especificado.";
    exit;
}

// ==============================================================================
// CONSULTA 1: DATOS DE LA CABECERA (El movimiento en general, paciente, usuario)
// ==============================================================================
$sql_cabecera = "SELECT 
            di.*,
            tm.nombre as tipo_nom,
            u.nombre as usuario_nombre, u.apellido as usuario_apellido,
            pac.nombre as pac_nombre, pac.apellido as pac_apellido, pac.tipo_cedula as pac_tipo_cedula, pac.cedula as pac_cedula,
            rep.nombre as rep_nombre, rep.apellido as rep_apellido, rep.tipo_cedula as rep_tipo_cedula, rep.cedula as rep_cedula
        FROM detalle_inventario di
        INNER JOIN persona u ON di.Id_persona = u.id 
        INNER JOIN tipo_movimiento tm ON di.Id_tipoMovimiento = tm.Id_tipo_movimiento
        /* Relaciones opcionales para paciente y representante */
        LEFT JOIN prescripcion_medicamentos pr ON di.Id_prescripcion = pr.Id
        LEFT JOIN consulta c ON pr.Id_consulta = c.Id_consulta
        LEFT JOIN persona pac ON c.Id_paciente = pac.id
        LEFT JOIN detalle_paciente_menor dpm_menor ON pac.id = dpm_menor.id_persona
        LEFT JOIN persona rep ON dpm_menor.id_representante = rep.id
        WHERE di.Id_detalle_inventario = '$id_movimiento' LIMIT 1";

$res_cabecera = mysqli_query($conexion, $sql_cabecera);

if ($res_cabecera && mysqli_num_rows($res_cabecera) > 0) {
    $mov = mysqli_fetch_assoc($res_cabecera);
} else {
    echo "<div class='alert alert-danger'><h4><i class='icon fa fa-ban'></i> Error</h4>Movimiento #$id_movimiento no encontrado en la base de datos.</div>";
    exit;
}

// ==============================================================================
// CONSULTA 2: DATOS DEL DETALLE (Todos los medicamentos dentro de este movimiento)
// ==============================================================================
$sql_detalles = "SELECT 
            mdi.cantidad, 
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
        /* Relaciones para componentes */
        LEFT JOIN detalle_principio_medicamento dpmd ON dm.Id = dpmd.id_medicamento
        LEFT JOIN unidad_medida um ON dpmd.id_tipo_unidad_medida = um.Id_unidad_medida
        LEFT JOIN principio_activo pa ON dpmd.id_principio_activo = pa.Id_principio_activo
        WHERE mdi.Id_detalle_inventario = '$id_movimiento'
        GROUP BY mdi.Id";

$res_detalles = mysqli_query($conexion, $sql_detalles);

// Variables de diseño
$esEntrada = (strcasecmp($mov['tipo_nom'], 'Entrada') == 0);
$tipo_label = strtoupper($mov['tipo_nom']) . ' DE INVENTARIO';
$tipo_bg = $esEntrada ? '#27ae60' : '#e74c3c';
$simbolo = $esEntrada ? '+' : '-';

// Ruta comprobante
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
        
        /* Estilos nuevos para la tabla de productos */
        .table-productos th {
            background-color: #f8f9fa;
            color: var(--primary);
            font-size: 13px;
        }
        .table-productos td {
            vertical-align: middle !important;
            font-size: 14px;
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
                        
                        <div class="col-md-8">
                            <h4 style="color: var(--primary); border-bottom: 2px solid #eee; padding-bottom: 10px; margin-top:0;">
                                <i class="fa fa-pills"></i> Productos Movilizados
                            </h4>
                            
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered table-productos">
                                    <thead>
                                        <tr>
                                            <th>Medicamento</th>
                                            <th>Presentación</th>
                                            <th>Lote</th>
                                            <th>Vence</th>
                                            <th class="text-center">Cant.</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($res_detalles && mysqli_num_rows($res_detalles) > 0): ?>
                                            <?php while ($prod = mysqli_fetch_assoc($res_detalles)): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo $prod['nombre_medicamento']; ?></strong><br>
                                                        <small class="text-muted"><?php echo !empty($prod['componentes']) ? $prod['componentes'] : 'Sin componentes'; ?></small>
                                                    </td>
                                                    <td><?php echo $prod['nombre_presentacion']; ?></td>
                                                    <td><?php echo $prod['nombre_lote']; ?></td>
                                                    <td style="<?php echo (strtotime($prod['fecha_vencimiento']) < time()) ? 'color: red; font-weight:bold;' : ''; ?>">
                                                        <?php echo date("d/m/Y", strtotime($prod['fecha_vencimiento'])); ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="qty-badge"><?php echo $simbolo . " " . $prod['cantidad']; ?></span>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center">No se encontraron medicamentos en este movimiento.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div style="margin-top: 30px;">
                                <span class="label-custom"><i class="fa fa-camera"></i> Foto del Comprobante (Soporte Digital)</span>
                                <?php if (!empty($mov['comprobante']) && file_exists($ruta_comprobante)) : ?>
                                    <img src="<?php echo $ruta_comprobante; ?>" class="img-comprobante" style="max-width: 300px;" alt="Foto Comprobante">
                                <?php else : ?>
                                    <div style="border: 2px dashed #ccc; width: 100%; padding: 15px; border-radius: 8px; color: #999; display: inline-block;">
                                        <i class="fa fa-image fa-2x"></i><br><small>Sin soporte</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="info-entrega">
                                <h4 style="border-bottom: 2px solid #d6eaf8; padding-bottom: 10px; color: #2980b9; margin-top:0;">
                                    <i class="fa fa-user-md"></i> Detalles del Destino
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
                                    <div class="text-center" style="padding: 20px 10px;">
                                        <i class="fa fa-truck fa-3x" style="opacity: 0.2; color: #2c3e50;"></i>
                                        <p style="margin-top: 15px;">Este registro corresponde a un movimiento de <strong>Stock Interno</strong> (Ajuste, Entrada Proveedor, etc).</p>
                                    </div>
                                <?php endif; ?>

                                <div style="margin-top: 30px; border-top: 1px solid #d6eaf8; padding-top: 15px;">
                                    <span class="label-custom">Observaciones Generales</span>
                                    <p style="font-size: 13px; font-style: italic; color: #555;">
                                        "<?php echo (!empty($mov['observaciones']) && trim($mov['observaciones']) != '') ? $mov['observaciones'] : 'Sin observaciones adicionales registradas.'; ?>"
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row no-print" style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
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
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="modalConfirmarRegresoLabel"><i class="fa fa-warning"></i> Confirmación</h4>
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
    </div>
    
    <?php include('includes/footer.php'); ?>
</body>
</html>