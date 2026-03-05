<?php
include("../../cfg/conexion.php");

// Validación del ID
$id_pres = isset($_GET['id']) ? mysqli_real_escape_string($conexion, $_GET['id']) : '';

if (empty($id_pres)) {
    header("Location: farmacia_prescripciones_listado.php");
    exit;
}

// Consulta detallada de la prescripción
$query = "SELECT 
            pm.*, 
            c.fecha_consulta,
            paciente.nombre AS nom_pac, paciente.apellido AS ape_pac, paciente.cedula AS cedula_pac,
            paciente.fecha_nacimiento, paciente.genero,
            medico.nombre AS nom_med, medico.apellido AS ape_med,
            m.nombre_medicamento,
            pres.tipo_presentacion,
            dm.cantidad_unidad_medida,
            u.unidad,
            c.tratamiento_indicado
          FROM prescripcion_medicamentos pm
          INNER JOIN consulta c ON pm.Id_consulta = c.Id_consulta
          INNER JOIN persona paciente ON c.Id_paciente = paciente.id
          INNER JOIN persona medico ON c.Id_medico = medico.id
          INNER JOIN descripcion_medicamento dm ON pm.Id_descripcion_medicamento = dm.Id
          INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
          INNER JOIN unidad_medida u ON dm.Id_unidad = u.Id_unidad_medida
          LEFT JOIN presentacion pres ON dm.Id_presentacion = pres.Id_presentacion
          WHERE pm.Id = '$id_pres'";

$res = $conexion->query($query);
$data = $res->fetch_assoc();

if (!$data) {
    echo "Prescripción no encontrada.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Detalle de Prescripción | Farmacia</title>
    <?php include('includes/headerNav2.php'); ?>
    <style>
        .content-wrapper {
            background-color: #f4f7f6;
        }

        .info-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            border: none;
        }

        .info-header {
            padding: 20px 25px;
            border-bottom: 1px solid #eee;
            font-size: 20px;
            font-weight: 700;
            color: #2c3e50;
            display: flex;
            align-items: center;
        }

        .info-header i {
            margin-right: 10px;
            color: #3c8dbc;
        }

        .info-body {
            padding: 25px;
        }

        /* Estilos de etiquetas y valores */
        .label-custom {
            color: #95a5a6;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .value-custom {
            font-size: 16px;
            color: #2c3e50;
            font-weight: 500;
            margin-bottom: 20px;
        }

        /* Caja de estatus mejorada */
        .status-banner {
            padding: 15px;
            border-radius: 8px;
            display: inline-block;
            min-width: 200px;
            text-align: center;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .well-custom {
            background: #fcfcfc;
            border: 1px solid #e3e3e3;
            border-left: 5px solid #3c8dbc;
            padding: 20px;
            font-size: 1.1em;
            line-height: 1.6;
            color: #555;
            border-radius: 4px;
        }

        .btn-lg-custom {
            padding: 12px 30px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>
</head>

<body class="hold-transition skin-blue sidebar-mini">
    <div class="content-wrapper">
        <section class="content-header" style="padding: 25px 15px;">
            <h1>
                <i class="fa fa-file-text-o text-primary"></i>
                Gestión de Prescripción Médica
                <small>Expediente #<?php echo $data['Id']; ?></small>
            </h1>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-4 col-md-5">
                        <div class="info-card">
                            <div class="info-header"><i class="fa fa-user-circle"></i> Paciente</div>
                            <div class="info-body">
                                <div class="row">
                                    <div class="col-xs-12">
                                        <p class="label-custom">Nombre Completo</p>
                                        <p class="value-custom" style="font-size: 1.2em;"><?php echo $data['nom_pac'] . " " . $data['ape_pac']; ?></p>
                                    </div>
                                    <div class="col-xs-6">
                                        <p class="label-custom">Cédula</p>
                                        <p class="value-custom">V-<?php echo $data['cedula_pac']; ?></p>
                                    </div>
                                    <div class="col-xs-6">
                                        <p class="label-custom">Género</p>
                                        <p class="value-custom"><?php echo ($data['genero'] == 'M') ? 'Masculino' : 'Femenino'; ?></p>
                                    </div>
                                </div>
                                <hr>
                                <div class="text-center" style="margin-top: 20px;">
                                    <p class="label-custom">Estado Actual</p>
                                    <div class="status-banner <?php
                                                                echo ($data['estatus'] == 'pendiente') ? 'bg-yellow' : (($data['estatus'] == 'entregado') ? 'bg-green' : 'bg-red');
                                                                ?>">
                                        <?php echo strtoupper($data['estatus']); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8 col-md-7">
                        <div class="info-card">
                            <div class="info-header"><i class="fa fa-heartbeat"></i> Detalles de la Orden</div>
                            <div class="info-body">
                                <div class="row">
                                    <div class="col-md-7">
                                        <p class="label-custom">Medicamento</p>
                                        <p class="value-custom text-blue" style="font-size: 1.8em; line-height: 1;">
                                            <?php echo $data['nombre_medicamento']; ?>
                                        </p>
                                    </div>
                                    <div class="col-md-5">
                                        <p class="label-custom">Presentación / Concentración</p>
                                        <p class="value-custom">
                                            <span class="label label-default" style="font-size: 14px;"><?php echo $data['tipo_presentacion']; ?></span>
                                            <span class="label label-info" style="font-size: 14px;"><?php echo $data['cantidad_unidad_medida'] . " " . $data['unidad']; ?></span>
                                        </p>
                                    </div>
                                </div>

                                <div class="row" style="margin-top: 20px;">
                                    <div class="col-sm-12">
                                        <p class="label-custom">Indicaciones y Dosificación</p>
                                        <div class="well-custom">
                                            <?php echo nl2br($data['tratamiento_indicado']); ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="row" style="margin-top: 30px;">
                                    <div class="col-sm-6">
                                        <p class="label-custom"><i class="fa fa-user-md"></i> Médico Emisor</p>
                                        <p class="value-custom">Dr(a). <?php echo $data['nom_med'] . " " . $data['ape_med']; ?></p>
                                    </div>
                                    <div class="col-sm-6">
                                        <p class="label-custom"><i class="fa fa-calendar"></i> Fecha y Hora de Emisión</p>
                                        <p class="value-custom"><?php echo date('d/m/Y - h:i A', strtotime($data['fecha_consulta'])); ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="info-body" style="background: #f9f9f9; border-top: 1px solid #eee; border-radius: 0 0 8px 8px;">
                                <button type="button" class="btn btn-default btn-lg-custom" data-toggle="modal" data-target="#modalRegresar">
                                    <i class="fa fa-chevron-left"></i> Regresar
                                </button>

                                <?php if ($data['estatus'] == 'pendiente') : ?>
                                    <a href="farmacia_inventario_movimiento_salida.php?id_pres=<?php echo $data['Id']; ?>&id_med=<?php echo $data['Id_descripcion_medicamento']; ?>&pac=<?php echo $data['cedula_pac']; ?>" class="btn btn-success btn-lg-custom pull-right shadow">
                                        <i class="fa fa-check-circle"></i> Procesar y Despachar
                                    </a>
                                <?php else : ?>
                                   
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="modal fade" id="modalRegresar" tabindex="-1" role="dialog" aria-labelledby="modalRegresarLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-crimson">
                    <h5 class="modal-title" id="modalRegresarLabel" style="color: white;">Confirmacion de Regreso</h5>
                </div>
                <div class="modal-body">
                    <p>Al hacer clic en "Abandonar Formulario", perderá todos los datos no guardados. ¿Desea continuar?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <a href="farmacia_prescripciones_listado.php" class="btn btn-danger">Abandonar Formulario</a>
                </div>
            </div>
        </div>
    </div>

    <?php include('includes/footer.php'); ?>
</body>

</html>