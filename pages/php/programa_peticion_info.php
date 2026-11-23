<?php
include("../../cfg/conexion.php");

$id_peticion = isset($_GET['Id']) ? (int)$_GET['Id'] : 0;

if ($id_peticion <= 0) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['mensaje_user_error'] = "Debe seleccionar una petición válida para ver su información.";
    header("Location: programa_listado.php");
    exit();
}

include('includes/headerNav2.php');
verificarAcceso('Gestionar Programa');

// ==========================================
// SIMULACIÓN DE DATOS (TEMPLATE - SIN BD)
// ==========================================
$peticion = [
    'id_peticion' => $id_peticion,
    'mes' => '2026-05',
    'fecha_generacion' => '2026-05-02',
    'medico_solicita' => 'Dra. Ana Gómez',
    'estado' => 'Recibida Parcial',
    'observaciones' => 'Petición mensual correspondiente a mayo. Se solicita a Guanare el envío de medicamentos para los pacientes crónicos del ASIC Agua Blanca.',
    'detalles' => [
        ['paciente' => 'Ana Pérez', 'cedula' => 'V-12345678', 'enfermedad' => 'Diabetes Mellitus Tipo 2', 'medicamento' => 'Metformina 850mg', 'dosis' => '850mg', 'frecuencia' => 'Cada 12h', 'cantidad_solicitada' => 60, 'cantidad_recibida' => 60],
        ['paciente' => 'Carlos Gómez', 'cedula' => 'V-87654321', 'enfermedad' => 'Hipertensión Arterial', 'medicamento' => 'Enalapril 10mg', 'dosis' => '10mg', 'frecuencia' => 'Cada 24h', 'cantidad_solicitada' => 30, 'cantidad_recibida' => 20],
        ['paciente' => 'María Rodríguez', 'cedula' => 'V-11223344', 'enfermedad' => 'Epilepsia', 'medicamento' => 'Carbamazepina 200mg', 'dosis' => '200mg', 'frecuencia' => 'Cada 12h', 'cantidad_solicitada' => 60, 'cantidad_recibida' => 0],
    ]
];

// Calcular totales
$total_solicitado = 0;
$total_recibido = 0;
$pacientes_unicos = [];
foreach ($peticion['detalles'] as $d) {
    $total_solicitado += $d['cantidad_solicitada'];
    $total_recibido += $d['cantidad_recibida'];
    if (!in_array($d['cedula'], $pacientes_unicos)) {
        $pacientes_unicos[] = $d['cedula'];
    }
}

$es_completa = ($peticion['estado'] == 'Recibida Total');
$puede_recibir = ($peticion['estado'] == 'Enviada' || $peticion['estado'] == 'Recibida Parcial' || $peticion['estado'] == 'Borrador');

$tipo_bg = '#3498db';
$tipo_label = 'PETICIÓN A GUANARE';
if ($peticion['estado'] == 'Recibida Total') {
    $tipo_bg = '#27ae60';
    $tipo_label = 'RECIBIDA TOTAL';
} elseif ($peticion['estado'] == 'Recibida Parcial') {
    $tipo_bg = '#f39c12';
    $tipo_label = 'RECIBIDA PARCIAL';
} elseif ($peticion['estado'] == 'Cancelada') {
    $tipo_bg = '#e74c3c';
    $tipo_label = 'CANCELADA';
}
?>

<head>
    <title>Petición | Información</title>
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
            max-height: 300px;
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
            font-size: 14px;
            font-weight: bold;
            padding: 5px 10px;
            border: 1px solid #eee;
            border-radius: 5px;
            display: inline-block;
        }

        .qty-solicitada {
            color: var(--primary);
            background: #f8f9fa;
        }

        .qty-recibida {
            color: #27ae60;
            background: #e8f8f5;
        }

        .qty-pendiente {
            color: #e74c3c;
            background: #fdedec;
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
        }
    </style>

    <section class="content">
        <div class="receipt-card">
            <div class="receipt-header">
                <div class="status-badge"><i class="fa fa-medkit"></i> <?php echo $tipo_label; ?></div>
                <h2 style="margin: 0;">PETICIÓN DE MEDICAMENTOS A GUANARE</h2>
                <div class="row" style="margin-top: 15px;">
                    <div class="col-xs-4">
                        <span class="label-custom" style="color: #bdc3c7;">N° Petición</span>
                        <span style="font-size: 20px; font-weight: bold;">#<?php echo str_pad($peticion['id_peticion'], 6, "0", STR_PAD_LEFT); ?></span>
                    </div>
                    <div class="col-xs-4">
                        <span class="label-custom" style="color: #bdc3c7;">Mes</span>
                        <span class="val-custom" style="color: #fff;"><?php echo htmlspecialchars($peticion['mes']); ?></span>
                    </div>
                    <div class="col-xs-4">
                        <span class="label-custom" style="color: #bdc3c7;">Fecha de Generación</span>
                        <span class="val-custom" style="color: #fff;"><?php echo date("d/m/Y", strtotime($peticion['fecha_generacion'])); ?></span>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="row" style="flex-grow: 1;">
                    <div class="col-md-8">
                        <h4 style="color: var(--primary); border-bottom: 2px solid #eee; padding-bottom: 5px; margin-top:0;">
                            <i class="fa fa-pills"></i> Medicamentos Solicitados
                        </h4>

                        <div class="table-scroll-container">
                            <table class="table table-hover table-bordered table-productos">
                                <thead>
                                    <tr>
                                        <th>Paciente</th>
                                        <th>Medicamento</th>
                                        <th class="text-center">Dosis</th>
                                        <th class="text-center">Frecuencia</th>
                                        <th class="text-center">Solicitado</th>
                                        <th class="text-center">Recibido</th>
                                        <th class="text-center">Pendiente</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($peticion['detalles'])) : ?>
                                        <?php foreach ($peticion['detalles'] as $d) :
                                            $pendiente = $d['cantidad_solicitada'] - $d['cantidad_recibida'];
                                            $clase_pendiente = ($pendiente > 0) ? 'qty-pendiente' : 'qty-recibida';
                                        ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($d['paciente']); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($d['cedula']); ?></small>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($d['medicamento']); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($d['enfermedad']); ?></small>
                                                </td>
                                                <td class="text-center"><?php echo htmlspecialchars($d['dosis']); ?></td>
                                                <td class="text-center"><?php echo htmlspecialchars($d['frecuencia']); ?></td>
                                                <td class="text-center"><span class="qty-badge qty-solicitada"><?php echo $d['cantidad_solicitada']; ?></span></td>
                                                <td class="text-center"><span class="qty-badge qty-recibida"><?php echo $d['cantidad_recibida']; ?></span></td>
                                                <td class="text-center"><span class="qty-badge <?php echo $clase_pendiente; ?>"><?php echo $pendiente; ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No se encontraron medicamentos en esta petición.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="row" style="margin-top: 15px;">
                            <div class="col-md-4">
                                <div style="background: #f8f9fa; padding: 10px; border-radius: 8px; border-left: 4px solid var(--primary);">
                                    <span class="label-custom">Pacientes Incluidos</span>
                                    <span class="val-custom" style="font-size: 20px; font-weight: bold;"><?php echo count($pacientes_unicos); ?></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div style="background: #f8f9fa; padding: 10px; border-radius: 8px; border-left: 4px solid #3498db;">
                                    <span class="label-custom">Total Solicitado</span>
                                    <span class="val-custom" style="font-size: 20px; font-weight: bold; color: #3498db;"><?php echo $total_solicitado; ?></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div style="background: #f8f9fa; padding: 10px; border-radius: 8px; border-left: 4px solid #27ae60;">
                                    <span class="label-custom">Total Recibido</span>
                                    <span class="val-custom" style="font-size: 20px; font-weight: bold; color: #27ae60;"><?php echo $total_recibido; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-entrega">
                            <h4 style="border-bottom: 2px solid #d6eaf8; padding-bottom: 10px; color: #2980b9; margin-top:0;">
                                <i class="fa fa-user-md"></i> Datos de la Petición
                            </h4>

                            <div style="background: #fff; padding: 15px; border-radius: 8px; border-left: 5px solid #8e44ad; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 15px;">
                                <span class="label-custom" style="color: #8e44ad;">Médico Solicitante</span>
                                <span class="val-custom"><strong><?php echo htmlspecialchars($peticion['medico_solicita']); ?></strong></span>
                            </div>

                            <div style="background: #fff; padding: 15px; border-radius: 8px; border-left: 5px solid #f39c12; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 15px;">
                                <span class="label-custom" style="color: #e67e22;">Estado de la Petición</span>
                                <span class="val-custom"><strong><?php echo htmlspecialchars($peticion['estado']); ?></strong></span>
                            </div>

                            <div style="background: #fff; padding: 15px; border-radius: 8px; border-left: 5px solid #3498db; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                                <span class="label-custom">Establecimiento</span>
                                <span class="val-custom"><strong>CPT3 / ASIC Agua Blanca</strong></span>
                            </div>

                            <div style="margin-top: 20px; border-top: 1px solid #d6eaf8; padding-top: 10px;">
                                <span class="label-custom">Observaciones Generales</span>
                                <p style="font-size: 13px; font-style: italic; color: #555;">
                                    "<?php echo !empty($peticion['observaciones']) ? htmlspecialchars($peticion['observaciones']) : 'Sin observaciones adicionales registradas.'; ?>"
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

                        <?php if ($puede_recibir) : ?>
                            <a href="farmacia_inventario_movimiento_entrada.php?id_peticion_auto=<?php echo $peticion['id_peticion']; ?>" class="btn btn-success pull-right" style="margin-left: 5px;">
                                <i class="fa fa-arrow-circle-right"></i> Registrar Recepción
                            </a>
                        <?php endif; ?>

                        <button type="button" class="btn btn-second pull-right" data-toggle="modal" data-target="#modalConfirmarRegreso">
                            <i class="fa fa-chevron-left"></i> Regresar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="modalConfirmarRegreso" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarRegresoLabel">
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
                    <a href="programa_listado.php" class="btn btn-danger">Regresar al Listado</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>