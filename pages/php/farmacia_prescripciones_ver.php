<?php
include("../../cfg/conexion.php");

// Validación del ID
$id_pres = isset($_GET['id']) ? mysqli_real_escape_string($conexion, $_GET['id']) : '';

if (empty($id_pres)) {
    header("Location: farmacia_prescripciones_listado.php");
    exit;
}

// Consulta detallada de la prescripción uniendo datos del paciente, médico y detalles completos del medicamento
$query = "SELECT 
            pm.*, 
            c.fecha_consulta, c.tratamiento_indicado,
            paciente.nombre AS nom_pac, paciente.apellido AS ape_pac, paciente.tipo_cedula AS tipo_cedula_pac, paciente.cedula AS cedula_pac,
            paciente.fecha_nacimiento, paciente.genero,
            medico.nombre AS nom_med, medico.apellido AS ape_med,
            m.nombre_medicamento,
            p.nombre_presentacion,
            dm.Id as id_desc_med, dm.codigo_barras, dm.via_aplicacion, dm.contenido_neto, dm.almacenamiento, dm.excipientes,
            l.nombre_laboratorio
          FROM prescripcion_medicamentos pm
          INNER JOIN consulta c ON pm.Id_consulta = c.Id_consulta
          INNER JOIN persona paciente ON c.Id_paciente = paciente.id
          INNER JOIN persona medico ON c.Id_medico = medico.id
          INNER JOIN descripcion_medicamento dm ON pm.Id_descripcion_medicamento = dm.Id
          INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
          LEFT JOIN presentacion p ON dm.Id_presentacion = p.Id_presentacion
          LEFT JOIN laboratorio l ON dm.Id_laboratorio = l.Id_laboratorio
          WHERE pm.Id = '$id_pres'";

$res = $conexion->query($query);
$data = $res->fetch_assoc();

if (!$data) {
    echo "Prescripción no encontrada.";
    exit;
}

// PRINCIPIOS ACTIVOS DEL MEDICAMENTO RECETADO
$principios = [];
$sql_pa = "SELECT pa.nombre, dpm.cantidad_unidad_medida, um.unidad 
           FROM detalle_principio_medicamento dpm
           JOIN principio_activo pa ON dpm.id_principio_activo = pa.id_principio_activo
           JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida
           WHERE dpm.id_medicamento = " . $data['id_desc_med'];
$res_pa = $conexion->query($sql_pa);
while ($p = $res_pa->fetch_assoc()) {
    $principios[] = $p;
}

// Mapeo de almacenamiento
$texto_almacenamiento = [
    "-25_a_-10" => "Congelación (-25°C a -10°C)",
    "2_a_8"     => "Refrigeración (2°C a 8°C)",
    "8_a_15"    => "Lugar Fresco (8°C a 15°C)",
    "15_a_25"   => "Temperatura Ambiente (15°C a 25°C)",
    "max_30"    => "Temperatura Máxima (30°C)"
];

// Configuración visual del estado_prescripcion
$status_color = "";
$status_icon = "";
switch (strtolower($data['estado_prescripcion'])) {
    case 'pendiente':
        $status_color = "#f39c12"; // Naranja
        $status_icon = "fa-clock-o";
        break;
    case 'entregado':
        $status_color = "#00a65a"; // Verde
        $status_icon = "fa-check-circle";
        break;
    case 'parcial':
        $status_color = "#00c0ef"; // Azul claro
        $status_icon = "fa-pie-chart";
        break;
    default:
        $status_color = "#dd4b39"; // Rojo
        $status_icon = "fa-times-circle";
        break;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Detalle de Prescripción | <?php echo $data['nombre_medicamento']; ?></title>
    <?php include('includes/headerNav2.php'); ?>

    <style>
        .content-wrapper {
            background-color: #f4f7f9 !important;
        }

        .content-custom {
            padding: 40px 15px;
        }

        .main-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-bottom: 30px;
        }

        /* Hero Header Dinámico */
        .hero-header {
            background: linear-gradient(135deg, #605ca8 0%, #333152 100%);
            color: white;
            padding: 30px;
            border-bottom: 5px solid <?php echo $status_color; ?>;
            position: relative;
        }

        .hero-status-badge {
            position: absolute;
            top: 20px;
            right: 30px;
            background: <?php echo $status_color; ?>;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
            letter-spacing: 1px;
            text-transform: uppercase;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        }

        .info-padding {
            padding: 30px;
        }

        .section-title {
            color: #3c8dbc;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }

        /* Estilos de tabla del medicamento */
        .table-info-med th {
            background: #f9f9f9;
            width: 35%;
            color: #777;
            font-size: 12px;
            text-transform: uppercase;
        }
        .table-info-med td {
            font-size: 14px;
            font-weight: 600;
            color: #333;
        }

        /* Cajas de información (Paciente / Médico) */
        .info-box-custom {
            background: #fdfdfd;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .info-box-custom .label-custom {
            color: #95a5a6;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .info-box-custom .value-custom {
            font-size: 15px;
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 10px;
        }

        /* Principios Activos */
        .pa-item {
            background: #fff;
            border: 1px solid #eee;
            border-left: 4px solid #00a65a;
            padding: 10px 15px;
            margin-bottom: 8px;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        /* Indicaciones (Tratamiento) */
        .well-custom {
            background: #fcfcfc;
            border: 1px solid #e3e3e3;
            border-left: 5px solid #f39c12;
            padding: 20px;
            font-size: 1.1em;
            line-height: 1.6;
            color: #555;
            border-radius: 4px;
        }

        .btn-lg-custom {
            padding: 10px 25px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
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
                    <div class="hero-header">
                        <div class="hero-status-badge">
                            <i class="fa <?php echo $status_icon; ?>"></i> <?php echo $data['estado_prescripcion']; ?>
                        </div>
                        <h4 style="margin:0; opacity: 0.8; font-weight: 300;">Expediente de Prescripción #<?php echo $data['Id']; ?></h4>
                        <p style="margin-top:5px; font-size: 16px; opacity: 0.9;">
                            <i class="fa fa-stethoscope"></i> Prescrito el <?php echo date('d/m/Y h:i A', strtotime($data['fecha_consulta'])); ?>
                        </p>
                    </div>

                    <div class="info-padding">
                        <div class="row">
                            <div class="col-md-5">
                                <h4 class="section-title"><i class="fa fa-user-circle"></i> Involucrados</h4>
                                
                                <div class="info-box-custom">
                                    <h5 class="text-blue" style="margin-top: 0; font-weight: bold;"><i class="fa fa-user"></i> Datos del Paciente</h5>
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <p class="label-custom">Nombre Completo</p>
                                            <p class="value-custom"><?php echo $data['nom_pac'] . " " . $data['ape_pac']; ?></p>
                                        </div>
                                        <div class="col-xs-6">
                                            <p class="label-custom">Cédula</p>
                                            <p class="value-custom"><?php echo $data['tipo_cedula_pac']; ?>-<?php echo $data['cedula_pac']; ?></p>
                                        </div>
                                        <div class="col-xs-6">
                                            <p class="label-custom">Género</p>
                                            <p class="value-custom"><?php echo ($data['genero'] == 'Masculino') ? 'Masculino' : 'Femenino'; ?></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="info-box-custom">
                                    <h5 class="text-blue" style="margin-top: 0; font-weight: bold;"><i class="fa fa-user-md"></i> Médico Tratante</h5>
                                    <p class="label-custom">Nombre del Profesional</p>
                                    <p class="value-custom">Dr(a). <?php echo $data['nom_med'] . " " . $data['ape_med']; ?></p>
                                </div>

                                <h4 class="section-title" style="margin-top: 30px;"><i class="fa fa-file-text-o"></i> Dosificación e Indicaciones</h4>
                                <div class="well-custom">
                                    <?php echo nl2br($data['tratamiento_indicado'] ?: 'No se registraron indicaciones adicionales.'); ?>
                                </div>
                            </div>

                            <div class="col-md-7">
                                <h4 class="section-title"><i class="fa fa-list-alt"></i> Ficha Técnica del Medicamento</h4>
                                
                                <table class="table table-bordered table-info-med">
                                    <tr>
                                        <th>Nombre</th>
                                        <td><?php echo $data['nombre_medicamento'] ?: 'No Especificado'; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Presentación / Contenido Neto</th>
                                        <td>
                                            <span><?php echo $data['nombre_presentacion'] ?: 'General'; ?> / </span>
                                            <?php echo $data['contenido_neto']; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Vía de Aplicación</th>
                                        <td><?php echo $data['via_aplicacion']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Código de Barras</th>
                                        <td><i class="fa fa-barcode"></i> <?php echo $data['codigo_barras']; ?></td>
                                    </tr>
                                </table>

                                <h4 class="section-title" style="margin-top: 25px;"><i class="fa fa-flask"></i> Principios Activos</h4>
                                <?php if (empty($principios)) : ?>
                                    <p class="text-muted"><i class="fa fa-info-circle"></i> No se han registrado principios activos para este medicamento.</p>
                                <?php else : ?>
                                    <div class="row">
                                    <?php foreach ($principios as $p) : ?>
                                        <div class="col-sm-6">
                                            <div class="pa-item">
                                                <strong><?php echo $p['nombre']; ?></strong>
                                                <span class="pull-right badge bg-green"><?php echo $p['cantidad_unidad_medida'] . " " . $p['unidad']; ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                            </div>
                        </div> <div style="float:right; margin-bottom: 20px;">
                            <div class="col-xs-12">
                                <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#modalConfirmarRegreso" id="abrirModalRegresar">Regresar</button>

                                <?php if (strtolower($data['estado_prescripcion']) == 'pendiente') : ?>
                                    <a class="btn btn-success" href="farmacia_inventario_movimiento_salida.php?id_pres=<?php echo $data['Id']; ?>&id_med=<?php echo $data['Id_descripcion_medicamento']; ?>&pac=<?php echo $data['cedula_pac']; ?>" class="btn btn-success btn-lg-custom pull-right shadow">
                                        <i class="fa fa-check-circle"></i> Procesar y Despachar
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                </div>

            </section>
        </div>

        <div class="modal fade" id="modalConfirmarRegreso" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarRegresoLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header modal-header-danger">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="modalConfirmarRegresoLabel"><i class="fa fa-warning"></i> Confirmación de Regreso</h4>
                    </div>
                    <div class="modal-body">
                        <p>Al hacer clic en "Regresar al Listado", saldrá de los detalles de esta prescripción. ¿Desea continuar?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <a href="farmacia_prescripciones_listado.php" class="btn btn-danger">Regresar al Listado</a>
                    </div>
                </div>
            </div>
        </div>

        <?php include('includes/footer.php'); ?>
    </div>
</body>

</html>