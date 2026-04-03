<?php
include("../../cfg/conexion.php");

$id_movimiento = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id_movimiento) {
    echo "Error: ID de movimiento no especificado.";
    exit;
}

/**
 * CONSULTA OPTIMIZADA (Sin tabla usuarios)
 * Buscamos el movimiento y vinculamos:
 * 1. Persona (El funcionario que registra, unido por Id_usuario)
 * 2. Medicamento, Presentación y Lote
 * 3. Persona (El paciente, en caso de que exista una prescripción vinculada)
 */
$sql = "SELECT 
            di.*, 
            mdi.cantidad, 
            m.nombre_medicamento, 
            tpm.nombre_tipo,
            l.Lote as nombre_lote,
            l.fecha_vencimiento,
            -- Datos del funcionario que registró
            u.nombre as usuario_nombre, u.apellido as usuario_apellido,
            -- Datos del paciente (si aplica)
            pac.nombre as pac_nombre, pac.apellido as pac_apellido, pac.tipo_cedula as pac_tipo_cedula, pac.cedula as pac_cedula,
            rep.nombre as rep_nombre, rep.apellido as rep_apellido, rep.tipo_cedula as rep_tipo_cedula, rep.cedula as rep_cedula
        FROM detalle_inventario di
        INNER JOIN medicamentos_detalle_inventario mdi ON di.Id_detalle_inventario = mdi.Id_detalle_inventario
        INNER JOIN descripcion_medicamento dm ON mdi.Id_descripcion_medicamento = dm.Id
        INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
        INNER JOIN tipo_medicamento tpm ON dm.Id_tipo = tpm.Id_tipo
        INNER JOIN lotes_medicamentos l ON mdi.Id_lote = l.Id
        -- El registro de quién lo hizo se cruza con persona
        INNER JOIN persona u ON di.Id_persona = u.id 
        -- Relación opcional con el paciente a través de la prescripción
        LEFT JOIN prescripcion_medicamentos pr ON di.Id_prescripcion = pr.Id
        LEFT JOIN consulta c ON pr.Id_consulta = c.Id_consulta
        LEFT JOIN detalle_paciente dp ON c.Id_paciente = dp.id_persona
        LEFT JOIN persona pac ON dp.Id_persona = pac.id
        LEFT JOIN detalle_paciente_menor dpm ON c.Id_paciente = dpm.id_persona
        LEFT JOIN persona rep ON dpm.id_representante = rep.id
        WHERE di.Id_detalle_inventario = '$id_movimiento'";

$res = mysqli_query($conexion, $sql);
$mov = mysqli_fetch_assoc($res);

if (!$mov) {
    echo "Movimiento no encontrado.";
    exit;
}

$tipo_label = ($mov['Id_TipoMovimiento'] == '1') ? 'ENTRADA' : 'SALIDA';
$tipo_class = ($mov['Id_TipoMovimiento'] == '1') ? 'label-success' : 'label-danger';

$tipoMovimiento = intval($row['Id_TipoMovimiento'] ?? 0);

$observaciones = strtolower($mov['observaciones'] ?? '');

$esRecetaExterna = strpos($observaciones, 'Récipe Externo') == false;

$tienePrescripcion = !empty($mov['id_prescripcion']); // si existe en el SELECT

$esEntrada = $tipoMovimiento === 1;

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Detalle de Movimiento</title>
    <?php include('includes/headerNav2.php'); ?>

    <style>
        /* ANIMACIONES Y ESTILOS DE MODALES */
        /* ---------------------------------------------------------------------- */
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
        #modalConfirmarRegreso {
            animation: fadeIn 0.4s ease-out;
        }

        .modal.out .modal-dialog {
            animation: fadeOut 0.4s ease-in;
        }

        .modal-open .modal-backdrop {
            opacity: 0.7 !important;
            animation: pulse-opacity 0.3s forwards;
        }

        /* Modales por encima */
        .modal {
            position: fixed !important;
            z-index: 99999 !important;
        }

        .modal-backdrop {
            z-index: 99998 !important;
            transition: .5s;
        }

        .modal.in {
            display: block;
        }

        .box-body {
            background: #ffffff !important;
            color: #333;
            padding-bottom: 5%;
        }

        .info-label {
            font-weight: bold;
            color: #777;
            text-transform: uppercase;
            font-size: 0.85em;
            display: block;
        }

        .info-data {
            font-size: 1.1em;
            margin-bottom: 15px;
            border-bottom: 1px solid #f4f4f4;
            padding-bottom: 5px;
            min-height: 25px;
        }

        .ficha-header {
            border-bottom: 2px solid #3c8dbc;
            margin-bottom: 20px;
            padding-bottom: 10px;
            background: #fafafa;
            padding: 15px;
            border-radius: 4px;
        }

        .well-custom {
            background: #fdfdfd;
            border: 1px solid #e3e3e3;
            padding: 15px;
            border-radius: 5px;
        }

        .text-blue-custom {
            color: #3c8dbc;
            font-weight: bold;
        }
    </style>
</head>

<body class="hold-transition skin-blue sidebar-mini">
    <div class="content-wrapper">
        <section class="content">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-list-alt"></i> Información Completa del Movimiento</h3>
                    <div class="box-tools pull-right">
                        <span class="label <?php echo $tipo_class; ?>" style="font-size: 1.1em; padding: 5px 10px;">
                            <i class="fa <?php echo ($mov['Id_TipoMovimiento'] == '1') ? 'fa-arrow-up' : 'fa-arrow-down'; ?>"></i>
                            <?php echo $tipo_label; ?>
                        </span>
                    </div>
                </div>

                <div class="box-body">
                    <div class="row ficha-header" style="margin-left: 0px; width:1018px;">
                        <div class="col-md-4">
                            <span class="info-label">ID de Registro</span>
                            <div class="info-data">#<?php echo str_pad($mov['Id_detalle_inventario'], 5, "0", STR_PAD_LEFT); ?></div>
                        </div>
                        <div class="col-md-4">
                            <span class="info-label">Fecha y Hora</span>
                            <div class="info-data"><?php echo date("d/m/Y - h:i A", strtotime($mov['fecha'])); ?></div>
                        </div>
                        <div class="col-md-4">
                            <span class="info-label">Funcionario Responsable</span>
                            <div class="info-data"><i class="fa fa-user-circle-o"></i> <?php echo $mov['usuario_nombre'] . " " . $mov['usuario_apellido']; ?></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6" style="margin-left: 10px;">
                            <h4 class="page-header text-blue-custom"><i class="fa fa-medkit"></i> Datos del Medicamento</h4>
                            <div class="well-custom">
                                <span class="info-label">Nombre y Presentación</span>
                                <div class="info-data"><?php echo $mov['nombre_medicamento']; ?> <small>(<?php echo $mov['nombre_tipo']; ?>)</small></div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <span class="info-label">Lote Utilizado</span>
                                        <div class="info-data"><?php echo $mov['nombre_lote']; ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="info-label">Vencimiento del Lote</span>
                                        <div class="info-data"><?php echo date("d/m/Y", strtotime($mov['fecha_vencimiento'])); ?></div>
                                    </div>
                                </div>

                                <span class="info-label">Cantidad Movilizada</span>
                                <div class="info-data" style="font-size: 1.8em; font-weight: bold; color: <?php echo ($mov['Id_TipoMovimiento'] == '1') ? '#00a65a' : '#dd4b39'; ?>;">
                                    <?php echo ($mov['Id_TipoMovimiento'] == '1') ? '+' : '-'; ?>
                                    <?php echo $mov['cantidad']; ?>
                                    <small style="color: inherit;">unidades</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <h4 class="page-header text-blue-custom"><i class="fa fa-info-circle"></i> Justificación y Destino</h4>
                            <?php if ($esRecetaExterna): ?>
                            <span class="info-label">Concepto / Motivo</span>
                            <div class="info-data"><?php echo $mov['observaciones']; ?></div>
                            <?php endif; ?>

                            <?php if (!empty($mov['pac_nombre'])) : ?>

                                <div class="well-custom" style="border-left: 4px solid #3c8dbc;">
                                    <span class="info-label">Paciente Beneficiario</span>
                                    <div class="info-data">
                                        <?php echo $mov['pac_nombre'] . " " . $mov['pac_apellido']; ?>
                                        <?php if (!empty($mov['rep_cedula'])) : ?>
                                            <small class="text-muted">(Menor de Edad)</small>
                                        <?php endif; ?>
                                    </div>
                                    <span class="info-label">Cédula de Identidad</span>
                                    <div class="info-data"><?php echo $mov['pac_cedula']; ?></div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($mov['rep_cedula'])) : ?>
                                <div class="row" style="margin-top: 15px;">
                                    <div class="col-sm-12">
                                        <div class="well well-sm" style="border-left: 5px solid #f39c12; background-color: #fff9eb;">
                                            <h5 style="margin-top:0; color:#e67e22; font-weight:bold;">
                                                <i class="fa fa-users"></i> Retirado por (Representante Legal)
                                            </h5>
                                            <p style="margin-bottom: 0;">
                                                <strong>Nombre:</strong> <?= $mov['rep_nombre'] . " " . $mov['rep_apellido']; ?> <br>
                                                <strong>Cédula:</strong> <?= $mov['rep_tipo_cedula']; ?>-<?= $mov['rep_cedula']; ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (strpos($mov['observaciones'], 'Médico:') !== false || strpos($mov['observaciones'], 'Paciente:') !== false) : ?>
                                <div class="well-custom" style="background: #fff9eb; border-left: 4px solid #f39c12;">
                                    <span class="info-label">Información de Documentación Externa</span>
                                    <div class="info-data" style="font-size: 0.95em; line-height: 1.4;">
                                        <?php echo nl2br($mov['observaciones']); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (isset($mov['estatus']) && $mov['estatus'] == 0) : ?>
                        <div class="row">
                            <div class="col-md-12 text-center" style="margin-top: 20px;">
                                <div style="border: 4px solid #dd4b39; color: #dd4b39; display: inline-block; padding: 10px 30px; font-weight: bold; font-size: 2em; transform: rotate(-3deg); border-radius: 10px; opacity: 0.8;">
                                    <i class="fa fa-ban"></i> MOVIMIENTO ANULADO
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div>
                        <button type="button" style="margin-left: 5px;" class="btn btn-primary pull-right" onclick="window.print();">
                            <i class="fa fa-print"></i> Imprimir
                        </button>

                        <button type="button" class="btn btn-second pull-right" data-toggle="modal" data-target="#modalConfirmarRegreso">
                            <i class="fa fa-chevron-left"></i> Regresar
                        </button>
                    </div>
                </div>
        </section>
    </div>

    <div class="modal" id="modalConfirmarRegreso" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarRegreso">
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

    <?php include('includes/footer.php'); ?>
</body>

</html>