<?php
include("../../cfg/conexion.php");

$id_movimiento = isset($_GET['id']) ? $_GET['id'] : null;

/*if (!$id_movimiento) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['mensaje_user_error'] = "Debe seleccionar un movimiento válido para ver su información.";
    header("Location: vacunacion_inventario_listado.php");
    exit();
}*/

// =======================================================
// SIMULACIÓN DE DATOS (BORRAR CUANDO CONECTES LA BD REAL)
// =======================================================
// En la versión real, aquí irían tus consultas SQL reales.
// Por ahora, quemamos los datos para que el diseño se vea.

// Simulamos que el ID 1 es una ENTRADA y el ID 2 es un DESPACHO
$esEntrada = ($id_movimiento == 1);

$mov = [
    'Id_detalle_inventario' => $id_movimiento,
    'fecha' => date('Y-m-d H:i:s'),
    'usuario_nombre' => 'Lic. María',
    'usuario_apellido' => 'Rodríguez',
    'tipo_nom' => $esEntrada ? 'Entrada' : 'Despacho',
    'observaciones' => $esEntrada ? 'Lote recibido del Ministerio de Salud. Cadena de frío intacta.' : 'Despacho a Ambulatorio Urbano II. Guía N° 000123.',
    'comprobante' => '' // Aquí iría el JSON de las fotos si existieran
];

// Datos del Destino (Simulados)
$mov['rec_nombre'] = $esEntrada ? 'Ministerio de Salud' : 'Ambulatorio Urbano II';
$mov['rec_apellido'] = $esEntrada ? '' : 'Agua Blanca';
$mov['pac_nombre'] = null; // En vacunas no hay paciente en el inventario
$mov['rep_nombre'] = null;

// Detalles de la tabla (Simulados)
$detalles_array = [];
if ($esEntrada) {
    $detalles_array[] = ['nombre_medicamento' => 'Antipoliomielítica (VPI)', 'nombre_presentacion' => '1 Dosis', 'componentes' => 'Virus Inactivados', 'nombre_lote' => 'VPI-001', 'fecha_vencimiento' => '2026-01-01', 'cantidad' => 500, 'observacion' => 'Lote en buen estado'];
    $detalles_array[] = ['nombre_medicamento' => 'Antisarampión (SRP)', 'nombre_presentacion' => '10 Dosis', 'componentes' => 'Virus Atenuados', 'nombre_lote' => 'SRP-991', 'fecha_vencimiento' => '2026-05-10', 'cantidad' => 200, 'observacion' => ''];
} else {
    $detalles_array[] = ['nombre_medicamento' => 'Antipoliomielítica (VPI)', 'nombre_presentacion' => '1 Dosis', 'componentes' => 'Virus Inactivados', 'nombre_lote' => 'VPI-001', 'fecha_vencimiento' => '2026-01-01', 'cantidad' => 150, 'observacion' => 'Despacho a ambulatorio'];
}

$mostrar_observacion = true; // Forzamos a true para que se vea la columna en el template
$rutas_evidencias = []; // Simulamos que no hay fotos para este ejemplo

$tipo_label = strtoupper($mov['tipo_nom']) . ' DE INVENTARIO';
$tipo_bg = $esEntrada ? '#27ae60' : '#e74c3c';
$simbolo = $esEntrada ? '+' : '-';

include('includes/headerNav2.php');
verificarAcceso('Ver Inventario');
?>

<head>
    <title>Movimiento de Vacunas | Información</title>
</head>

<div class="content-wrapper">
    <style>
        :root {
            --primary: #2c3e50;
            --accent: #3498db;
            --bg: #f4f7f6;
        }

        @media screen {

            html,
            body {
                overflow: hidden !important;
                height: 118vh !important;
                background-color: var(--bg) !important;
            }

            .wrapper,
            .content-wrapper {
                height: 118vh !important;
                min-height: 118vh !important;
                overflow: hidden !important;
                background-color: #f4f7f9 !important;
            }

            .content-custom {
                padding: 20px 10px;
                margin-left: 60px;
                height: calc(100vh - 50px);
                overflow: hidden;
            }
        }

        body {
            font-family: 'Segoe UI', sans-serif;
        }

        .receipt-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin: 10px auto;
            border: none;
            display: flex;
            flex-direction: column;
            max-height: 85vh;
        }

        .receipt-header {
            background: var(--primary);
            color: white;
            padding: 20px 30px;
            border-bottom: 5px solid <?php echo $tipo_bg; ?>;
            position: relative;
            flex-shrink: 0;
        }

        .card-body {
            padding: 20px 30px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
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

        .table-scroll-container {
            max-height: 160px;
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
                <h2 style="margin: 0;">COMPROBANTE DE MOVIMIENTO DE VACUNAS</h2>
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
                        <span class="label-custom" style="color: #bdc3c7;">Registrado por</span>
                        <span class="val-custom" style="color: #fff;"><?php echo $mov['usuario_nombre'] . " " . $mov['usuario_apellido']; ?></span>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="row" style="flex-grow: 1;">
                    <div class="col-md-8">
                        <h4 style="color: var(--primary); border-bottom: 2px solid #eee; padding-bottom: 5px; margin-top:0;">
                            <i class="fa fa-syringe"></i> Vacunas Movilizadas
                        </h4>

                        <div class="table-scroll-container">
                            <table class="table table-hover table-bordered table-productos">
                                <thead>
                                    <tr>
                                        <th>Vacuna</th>
                                        <th>Presentación</th>
                                        <th>Lote</th>
                                        <th>Vence</th>
                                        <th class="text-center">Cant. (Dosis)</th>
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
                                                <?php if ($mostrar_observacion) : ?>
                                                    <td><?php echo !empty(trim($prod['observacion'])) ? $prod['observacion'] : '<span class="text-muted">-</span>'; ?></td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="<?php echo $mostrar_observacion ? '6' : '5'; ?>" class="text-center">No se encontraron vacunas en este movimiento.</td>
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
                                <i class="fa fa-hospital-o"></i> Detalles del Movimiento
                            </h4>

                            <?php if (!empty($mov['rec_nombre'])) : ?>
                                <div style="background: #fff; padding: 15px; border-radius: 8px; border-left: 5px solid #8e44ad; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 15px;">
                                    <span class="label-custom" style="color: #8e44ad;"><?php echo $esEntrada ? 'Proveedor / Donante' : 'Destino (Ambulatorio / Centro)'; ?></span>
                                    <span class="val-custom"><strong><?php echo $mov['rec_nombre'] . " " . $mov['rec_apellido']; ?></strong></span><br>
                                </div>
                            <?php endif; ?>

                            <div style="margin-top: 20px; border-top: 1px solid #d6eaf8; padding-top: 10px;">
                                <span class="label-custom">Observaciones Generales</span>
                                <p style="font-size: 13px; font-style: italic; color: #555;">
                                    "<?php echo (!empty($mov['observaciones']) && trim($mov['observaciones']) != '') ? $mov['observaciones'] : 'Sin observaciones adicionales registradas.'; ?>"
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

    <!-- MODAL REGRESAR -->
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
                    <a href="vacunacion_inventario_listado.php" class="btn btn-danger">Regresar al Inicio</a>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL VER IMAGEN -->
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