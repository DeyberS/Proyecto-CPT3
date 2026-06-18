<?php
include("../../cfg/conexion.php");

$id_url = isset($_GET['Id']) ? (int)$_GET['Id'] : 0;
if ($id_url <= 0) die("ID no válido.");

// 1. CONSULTA PRINCIPAL DE LA PATOLOGÍA
$sql_patologia = "SELECT * FROM patologias WHERE Id_patologia = $id_url";
$resultado = $conexion->query($sql_patologia);
$row = $resultado->fetch_assoc();

if (!$row) die("Registro no encontrado.");

// 2. CONSULTA DE SÍNTOMAS ASOCIADOS
$sintomas = [];
$sql_sintomas = "SELECT s.nombre_sintoma 
                 FROM detalle_patologia_sintomas dps
                 JOIN sintomas s ON dps.Id_sintoma = s.Id_sintomas
                 WHERE dps.Id_patologia = $id_url";
$res_sintomas = $conexion->query($sql_sintomas);
if ($res_sintomas) {
    while ($s = $res_sintomas->fetch_assoc()) {
        $sintomas[] = $s;
    }
}

// 3. CONSULTA DE MEDICAMENTOS ASOCIADOS (OPCIONAL/SI APLICA EN TU SISTEMA)
$medicamentos = [];
$sql_med = "SELECT m.nombre_medicamento, dm.cantidad_concentracion, u.unidad 
            FROM detalle_patologia_medicamento dpm
            JOIN descripcion_medicamento dm ON dpm.Id_medicamento = dm.Id
            JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
            LEFT JOIN unidad_medida u ON dm.Id_tipo_concentracion = u.Id_unidad_medida
            WHERE dpm.Id_patologia = $id_url";
$res_med = $conexion->query($sql_med);
if ($res_med) {
    while ($m = $res_med->fetch_assoc()) {
        $medicamentos[] = $m;
    }
}

$conexion->close();
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Información de Patología | <?php echo htmlspecialchars($row['nombre_patologia']); ?></title>
    <?php include('includes/headerNav2.php'); ?>

    <style>
        /* CONFIGURACIÓN DEL WRAPPER */
        .wrapper {
            display: block !important; 
            min-height: 100% !important; 
            overflow-x: hidden !important; 
            background-color: #f4f7f9 !important; 
        }

        .content-wrapper {
            background-color: #f4f7f9 !important;
            min-height: 125vh !important; 
        }

        .content-custom {
            padding: 50px 10px;
            margin-left: 60px;
        }

        .main-container {
            background: white;
            min-height: 60vh;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        /* DISEÑO DE CABECERA (Adaptado para Patología) */
        .hero-header {
            background: linear-gradient(135deg, #d81b60 0%, #222d32 100%);
            color: white;
            padding: 35px 30px;
            border-bottom: 5px solid #f39c12;
            position: relative;
        }

        .hero-icon {
            font-size: 50px;
            position: absolute;
            right: 40px;
            top: 20px;
            opacity: 0.2;
        }

        .info-padding {
            padding: 40px 30px;
        }

        /* DISEÑO DE LA TABLA DE INFORMACIÓN */
        .table-info-pat th {
            background: #f9f9f9;
            width: 35%;
            color: #555;
            font-size: 13px;
            text-transform: uppercase;
            vertical-align: middle !important;
        }

        .table-info-pat td {
            font-size: 16px;
            font-weight: 500;
            color: #333;
            vertical-align: middle !important;
        }

        /* DISEÑO DE ITEMS PARA SÍNTOMAS Y MEDICAMENTOS */
        .sintoma-item {
            background: #fffafa;
            border: 1px solid #fee2e2;
            border-left: 4px solid #f39c12;
            padding: 12px 15px;
            margin-bottom: 8px;
            border-radius: 4px;
            font-size: 15px;
            color: #444;
            transition: transform 0.2s;
        }

        .sintoma-item:hover {
            transform: translateX(5px);
            background: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .med-item {
            background: #fdfdfd;
            border: 1px solid #eee;
            border-left: 4px solid #00a65a;
            padding: 12px 15px;
            margin-bottom: 8px;
            border-radius: 4px;
            font-size: 15px;
        }

        /* ANIMACIONES Y MODALES (Idéntico a tu sistema) */
        @keyframes pulse-opacity {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-50px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeOut {
            from { opacity: 1; transform: translateY(0); }
            to { opacity: 0; transform: translateY(-50px); }
        }

        .modal.in .modal-dialog { animation: fadeIn 0.4s ease-out; }
        .modal.out .modal-dialog { animation: fadeOut 0.4s ease-in; }
        .modal-open .modal-backdrop { opacity: 0.7 !important; animation: pulse-opacity 0.3s forwards; }

        .modal { position: fixed !important; z-index: 99999 !important; }
        .modal-backdrop { z-index: 99998 !important; transition: .5s; }
        .modal.in { display: block; }

        .modal-header-danger {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>

<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        <div class="content-wrapper">
            <section class="content content-custom">
                <div class="main-container">
                    
                    <div class="hero-header text-left">
                        <i class="fa fa-stethoscope hero-icon"></i>
                        <h1 style="margin:0; font-weight: 800; letter-spacing: 1px;">
                            <?php echo htmlspecialchars($row['nombre_patologia']); ?>
                        </h1>
                        <p style="margin-top:8px; font-size: 18px; opacity: 0.9;">
                            <i class="fa fa-barcode"></i> Código CIE-10: <strong><?php echo htmlspecialchars($row['codigo_cie']); ?></strong>
                        </p>
                    </div>

                    <div class="info-padding">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="page-header" style="color: #d81b60; font-weight:bold;">
                                    <i class="fa fa-file-text-o"></i> Información Clínica
                                </h4>
                                <table class="table table-bordered table-info-pat">
                                    <tr>
                                        <th>Nombre de la Patología</th>
                                        <td><?php echo htmlspecialchars($row['nombre_patologia']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Código Internacional (CIE-10)</th>
                                        <td>
                                            <span class="label label-primary" style="font-size: 14px;">
                                                <?php echo htmlspecialchars($row['codigo_cie']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>¿Es Enfermedad Contagiosa?</th>
                                        <td>
                                            <?php if ($row['contagioso'] == 'SI') : ?>
                                                <span class="label label-danger" style="font-size: 14px; padding: 5px 10px;">
                                                    <i class="fa fa-warning"></i> SÍ, ES CONTAGIOSA
                                                </span>
                                            <?php else : ?>
                                                <span class="label label-success" style="font-size: 14px; padding: 5px 10px;">
                                                    <i class="fa fa-shield"></i> NO ES CONTAGIOSA
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Descripción / Observaciones</th>
                                        <td>
                                            <?php 
                                            if (!empty($row['descripcion'])) {
                                                echo nl2br(htmlspecialchars($row['descripcion']));
                                            } else {
                                                echo '<span class="text-muted"><i>No se ha registrado una descripción clínica detallada para esta patología.</i></span>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <div class="col-md-6">
                                <h4 class="page-header" style="color: #f39c12; font-weight:bold;">
                                    <i class="fa fa-heartbeat"></i> Síntomas Frecuentes
                                </h4>
                                <?php if (empty($sintomas)) : ?>
                                    <div class="alert alert-default" style="background: #f9f9f9; border: 1px dashed #ccc; color: #777;">
                                        <i class="fa fa-info-circle"></i> No hay síntomas asociados registrados en el sistema.
                                    </div>
                                <?php else : ?>
                                    <?php foreach ($sintomas as $s) : ?>
                                        <div class="sintoma-item">
                                            <i class="fa fa-check text-orange" style="margin-right: 8px;"></i> 
                                            <strong><?php echo htmlspecialchars($s['nombre_sintoma']); ?></strong>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <h4 class="page-header text-green" style="font-weight:bold; margin-top: 40px;">
                                    <i class="fa fa-medkit"></i> Medicamentos Relacionados
                                </h4>
                                <?php if (empty($medicamentos)) : ?>
                                    <p class="text-muted" style="font-style: italic;">Sin tratamientos farmacológicos predeterminados vinculados.</p>
                                <?php else : ?>
                                    <?php foreach ($medicamentos as $med) : ?>
                                        <div class="med-item">
                                            <strong><?php echo htmlspecialchars($med['nombre_medicamento']); ?></strong>
                                            <?php if($med['cantidad_concentracion']): ?>
                                                <span class="pull-right badge bg-green">
                                                    <?php echo htmlspecialchars($med['cantidad_concentracion'] . " " . $med['unidad']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <hr style="margin-top: 50px;">
                                <div class="no-print text-right">
                                    <button type="button" class="btn btn-secondary btn-lg" style="box-shadow: 0 2px 5px rgba(0,0,0,0.2);" data-toggle="modal" data-target="#modalConfirmarRegreso">
                                        <i class="fa fa-arrow-left"></i> Regresar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div class="modal" id="modalConfirmarRegreso" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarRegresoLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header modal-header-danger">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="modalConfirmarRegresoLabel"><i class="fa fa-warning"></i> Confirmación de Regreso</h4>
                    </div>
                    <div class="modal-body">
                        <p>Está a punto de regresar al inicio. ¿Desea continuar?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <a href="javascript:history.back()" class="btn btn-danger">Regresar al Inicio</a>
                    </div>
                </div>
            </div>
        </div>

        <?php include('includes/footer.php'); ?>
    </div>

    <script>
        $(document).ready(function() {
            $('.modal').on('click', '[data-dismiss="modal"]', function(e) {
                e.stopPropagation();
                var $modal = $(this).closest('.modal');
                if ($modal.hasClass('in')) {
                    $modal.removeClass('in').addClass('out');
                    setTimeout(function() {
                        $modal.modal('hide');
                        $modal.removeClass('out');
                    }, 400);
                } else {
                    $modal.modal('hide');
                }
            });

            $('.modal').on('hidden.bs.modal', function() {
                if (!$('.modal.in').length) {
                    $('body').removeClass('modal-open');
                    $('.modal-backdrop').remove();
                } else {
                    $('body').addClass('modal-open');
                }
            });
        });
    </script>
</body>

</html>