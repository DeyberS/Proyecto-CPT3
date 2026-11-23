<?php
include("../../cfg/conexion.php");

$id_pres = isset($_GET['id']) ? mysqli_real_escape_string($conexion, $_GET['id']) : '';
$tipo_receta = isset($_GET['tipo']) ? mysqli_real_escape_string($conexion, $_GET['tipo']) : 'Interna';

if (empty($id_pres)) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['mensaje_user_error'] = "Debe seleccionar una consulta válida para ver su información.";

    header("Location: farmacia_prescripciones_listado.php");
    exit();
}

if ($tipo_receta === 'Interna') {
    $query_general = "SELECT 
        sm.id_solicitud AS Id,
        c.fecha_consulta, c.tratamiento_indicado, sm.entregado_a,
        paciente.nombre AS nom_pac, paciente.apellido AS ape_pac, paciente.tipo_cedula AS tipo_cedula_pac, paciente.cedula AS cedula_pac,
        paciente.fecha_nacimiento, paciente.genero,
        medico.nombre AS nom_med, medico.apellido AS ape_med,
        sm.estatus_general,
        TIMESTAMPDIFF(YEAR, paciente.fecha_nacimiento, CURDATE()) < 18 AS es_menor,
        rep.cedula AS cedula_representante
    FROM solicitud_medicamento sm
    INNER JOIN consulta c ON sm.id_consulta = c.Id_consulta
    INNER JOIN persona paciente ON sm.id_paciente = paciente.id
    INNER JOIN detalle_medico dmd ON sm.id_medico = dmd.Id_detalle_medico
    INNER JOIN persona medico ON dmd.Id_persona = medico.id
    LEFT JOIN historial_representantes hr ON paciente.id = hr.Id_paciente AND hr.estatus='1' AND hr.tipo_representante='Legal'
    LEFT JOIN persona rep ON hr.Id_representante = rep.id
    WHERE sm.id_solicitud = '$id_pres'";

    $query_meds = "SELECT 
                ds.id_detalle AS id_item, 
                ds.estatus_item AS estado_prescripcion, 
                ds.motivo, ds.cantidad_recetada,
                ds.id_descripcion_medicamento AS Id_descripcion_medicamento,
                m.nombre_medicamento,
                p.nombre_presentacion,
                dm.Id as id_desc_med, dm.codigo_barras, dm.via_aplicacion, dm.contenido_neto, dm.almacenamiento, dm.excipientes,
                l.nombre_laboratorio
            FROM detalle_solicitud ds
            INNER JOIN descripcion_medicamento dm ON ds.id_descripcion_medicamento = dm.Id
            INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
            LEFT JOIN presentacion p ON dm.Id_presentacion = p.Id_presentacion
            LEFT JOIN laboratorio l ON dm.Id_laboratorio = l.Id_laboratorio
            WHERE ds.id_solicitud = '$id_pres'";
} else {
    $query_general = "SELECT 
                sm.id_solicitud AS Id, 
                sm.fecha_solicitud AS fecha_consulta, 
                'Receta Externa - Sin indicaciones detalladas registradas en el sistema.' AS tratamiento_indicado,
                sm.entregado_a,
                paciente.nombre AS nom_pac, paciente.apellido AS ape_pac, 
                paciente.tipo_cedula AS tipo_cedula_pac, paciente.cedula AS cedula_pac,
                paciente.fecha_nacimiento, paciente.genero,
                medico.nombre AS nom_med, medico.apellido AS ape_med,
                sm.estatus_general,
                TIMESTAMPDIFF(YEAR, paciente.fecha_nacimiento, CURDATE()) < 18 AS es_menor,
                rep.cedula AS cedula_representante
              FROM solicitud_medicamento sm
              INNER JOIN persona paciente ON sm.id_paciente = paciente.id
              INNER JOIN detalle_medico dmd ON sm.id_medico = dmd.Id_detalle_medico
              INNER JOIN persona medico ON dmd.Id_persona = medico.id
              LEFT JOIN historial_representantes hr ON paciente.id = hr.Id_paciente AND hr.estatus='1' AND hr.tipo_representante='Legal'
              LEFT JOIN persona rep ON hr.Id_representante = rep.id
              WHERE sm.id_solicitud = '$id_pres'";

    $query_meds = "SELECT 
                ds.id_detalle AS id_item, 
                ds.estatus_item AS estado_prescripcion, 
                ds.motivo,
                ds.id_descripcion_medicamento AS Id_descripcion_medicamento,
                m.nombre_medicamento,
                p.nombre_presentacion,
                dm.Id as id_desc_med, dm.codigo_barras, dm.via_aplicacion, dm.contenido_neto, dm.almacenamiento, dm.excipientes,
                l.nombre_laboratorio,
                ds.cantidad_recetada
              FROM detalle_solicitud ds
              INNER JOIN descripcion_medicamento dm ON ds.id_descripcion_medicamento = dm.Id
              INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
              LEFT JOIN presentacion p ON dm.Id_presentacion = p.Id_presentacion
              LEFT JOIN laboratorio l ON dm.Id_laboratorio = l.Id_laboratorio
              WHERE ds.id_solicitud = '$id_pres'";
}

$res_general = $conexion->query($query_general);

if (!$res_general) {
    die("Error de sintaxis en la consulta general SQL: " . $conexion->error);
}

$data = $res_general->fetch_assoc();

if (!$data) {
    echo "Prescripción no encontrada.";
    exit;
}

/* El estado general ahora se toma SIEMPRE de solicitud_medicamento.estatus_general
   (aplica tanto a internas como a externas) */
$estado_general = strtolower($data['estatus_general']);

$res_meds = $conexion->query($query_meds);
$medicamentos = [];

if ($res_meds) {
    while ($m = $res_meds->fetch_assoc()) {
        $principios = [];
        $sql_pa = "SELECT pa.nombre, dpm.cantidad_unidad_medida, um.unidad 
                   FROM detalle_principio_medicamento dpm
                   JOIN principio_activo pa ON dpm.id_principio_activo = pa.id_principio_activo
                   JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida
                   WHERE dpm.id_descripcion_medicamento = " . $m['id_desc_med'];

        $res_pa = $conexion->query($sql_pa);
        if ($res_pa) {
            while ($p = $res_pa->fetch_assoc()) {
                $principios[] = $p;
            }
        }
        $m['principios'] = $principios;
        $medicamentos[] = $m;
    }
}

$med_principal = count($medicamentos) > 0 ? $medicamentos[0] : null;

$estado_limpio = $estado_general;
if ($estado_limpio == 'parcialmente entregado') $estado_limpio = 'parcial';

$status_color = "";
$status_icon = "";
switch ($estado_limpio) {
    case 'pendiente':
        $status_color = "#f39c12";
        $status_icon = "fa-clock-o";
        break;
    case 'entregado':
    case 'completado':
        $estado_limpio = 'entregado';
        $status_color = "#00a65a";
        $status_icon = "fa-check-circle";
        break;
    case 'parcial':
        $status_color = "#f39c12";
        $status_icon = "fa-pie-chart";
        break;
    default:
        $status_color = "#dd4b39";
        $status_icon = "fa-times-circle";
        break;
}

$cedula_a_enviar = ($data['es_menor'] == 1 && !empty($data['cedula_representante'])) ? $data['cedula_representante'] : $data['cedula_pac'];

include('includes/headerNav2.php');
verificarAcceso('Ver informacion de recetas');
?>

<head>
    <title>Detalle de Prescripción | <?php echo trim($data['nom_pac'] . " " . $data['ape_pac']); ?></title>
</head>

<div class="content-wrapper">
    <style>
        /* ============================================================
       BASE (todos los dispositivos)
       ============================================================ */
        .wrapper {
            display: block !important;
            min-height: 100% !important;
            overflow-x: hidden !important;
            background-color: #f4f7f9 !important;
        }

        .content-wrapper {
            background-color: #f4f7f9 !important;
            min-height: calc(100vh - 50px) !important;
        }

        .content-custom {
            padding: 20px 10px;
            margin-left: 0;
            /* ← fix: antes 40px, cortaba a la derecha */
            box-sizing: border-box;
        }

        /* Contenedor principal: cubre bien la pantalla en PC, sin verse
       desproporcionado en monitores muy grandes */
        .main-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin: 0 auto 30px auto;
            width: 100%;
            max-width: 1400px;
            /* ← cubre la pantalla sin estirarse al infinito */
            min-height: 70vh;
            /* ← en PC con poco contenido, no se ve vacío */
            display: flex;
            flex-direction: column;
        }

        .hero-header {
            background: linear-gradient(135deg, #605ca8 0%, #333152 100%);
            color: white;
            padding: 30px;
            border-bottom: 5px solid <?php echo $status_color; ?>;
            position: relative;
            overflow-wrap: anywhere;
            word-break: break-word;
            flex-shrink: 0;
        }

        .hero-header h1,
        .hero-header h2,
        .hero-header h3,
        .hero-header h4 {
            margin: 0;
            overflow-wrap: anywhere;
            word-break: break-word;
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
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            white-space: nowrap;
        }

        .info-padding {
            padding: 30px;
            flex-grow: 1;
            /* ← el contenido rellena el main-container */
            display: flex;
            flex-direction: column;
        }

        .section-title {
            color: #3c8dbc;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }

        .table-info-med {
            margin-bottom: 0;
        }

        .table-info-med th {
            background: #f9f9f9;
            width: 35%;
            color: #777;
            font-size: 12px;
            text-transform: uppercase;
            vertical-align: middle !important;
            overflow-wrap: break-word;
        }

        .table-info-med td {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            vertical-align: middle !important;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .info-box-custom {
            background: #fdfdfd;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
            overflow-wrap: anywhere;
            word-break: break-word;
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
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .pa-item {
            background: #fff;
            border: 1px solid #eee;
            border-left: 4px solid #00a65a;
            padding: 10px 15px;
            margin-bottom: 8px;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .well-custom {
            background: #fcfcfc;
            border: 1px solid #e3e3e3;
            border-left: 5px solid #f39c12;
            padding: 20px;
            font-size: 1.1em;
            line-height: 1.6;
            color: #555;
            border-radius: 4px;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .btn-lg-custom {
            padding: 10px 25px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ============================================================
       TABLET (≤ 991 px)
       ============================================================ */
        @media (max-width: 991px) {
            .content-custom {
                padding: 15px 8px;
            }

            .hero-header {
                padding: 24px 20px;
            }

            .hero-status-badge {
                top: 15px;
                right: 20px;
                padding: 6px 12px;
                font-size: 12px;
            }

            .info-padding {
                padding: 20px;
            }

            .main-container {
                min-height: 60vh;
            }

            .table-info-med th {
                width: 40%;
                font-size: 11px;
            }
        }

        /* ============================================================
       MÓVIL (≤ 767 px)
       ============================================================ */
        @media (max-width: 767px) {
            .content-custom {
                margin-left: 0 !important;
                padding: 12px 8px;
            }

            .main-container {
                border-radius: 6px;
                min-height: auto;
                margin-bottom: 15px;
            }

            .hero-header {
                padding: 18px 15px;
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .hero-header h1,
            .hero-header h2,
            .hero-header h3,
            .hero-header h4 {
                font-size: 1.25em;
                line-height: 1.3;
            }

            /* Badge: deja de ser absolute y se apila debajo del título */
            .hero-status-badge {
                position: static !important;
                padding: 6px 12px;
                font-size: 11px;
                align-self: flex-start;
            }

            .info-padding {
                padding: 15px;
            }

            .section-title {
                font-size: 15px;
                margin-bottom: 12px;
                padding-bottom: 6px;
            }

            .table-info-med th {
                width: 42%;
                font-size: 11px;
                padding: 6px 8px !important;
                white-space: normal;
            }

            .table-info-med td {
                font-size: 13px;
                padding: 6px 8px !important;
            }

            .info-box-custom {
                padding: 12px;
                margin-bottom: 12px;
            }

            .info-box-custom .label-custom {
                font-size: 10px;
            }

            .info-box-custom .value-custom {
                font-size: 13px;
            }

            .pa-item {
                padding: 8px 10px;
                font-size: 13px;
            }

            .well-custom {
                padding: 14px;
                font-size: 1em;
                line-height: 1.55;
            }

            .btn-lg-custom {
                width: 100%;
                padding: 10px;
                font-size: 13px;
            }

            /* Las columnas de Bootstrap se apilan con margen */
            .info-padding>.row>[class*="col-"] {
                margin-bottom: 10px;
            }
        }

        /* ============================================================
       MÓVIL PEQUEÑO (≤ 360 px)
       ============================================================ */
        @media (max-width: 360px) {
            .content-custom {
                padding: 8px 5px;
            }

            .hero-header {
                padding: 14px 12px;
            }

            .hero-header h1 {
                font-size: 1.1em;
            }

            .info-padding {
                padding: 10px;
            }

            .table-info-med th {
                width: 45%;
                font-size: 10px;
            }

            .table-info-med td {
                font-size: 12px;
            }

            .well-custom {
                font-size: 0.95em;
                padding: 12px;
            }
        }
    </style>

    <section class="content content-custom">
        <div class="main-container">
            <div class="hero-header">
                <div class="hero-status-badge">
                    <i class="fa <?php echo $status_icon; ?>"></i> <?php echo ucfirst($estado_limpio); ?>
                </div>
                <h4 style="margin:0; opacity: 0.8; font-weight: 300;">Expediente de Prescripción (<?php echo $tipo_receta; ?>) #<?php echo $data['Id']; ?></h4>
                <p style="margin-top:5px; font-size: 16px; opacity: 0.9;">
                    <i class="fa fa-stethoscope"></i> Solicitado el <?php echo date('d/m/Y h:i A', strtotime($data['fecha_consulta'])); ?>
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
                                    <p class="value-custom"><?php echo trim($data['nom_pac'] . " " . $data['ape_pac']); ?></p>
                                </div>
                                <div class="col-xs-6">
                                    <p class="label-custom">Cédula</p>
                                    <p class="value-custom"><?php echo $data['tipo_cedula_pac']; ?>-<?php echo $data['cedula_pac']; ?></p>
                                </div>
                                <div class="col-xs-6">
                                    <p class="label-custom">Género</p>
                                    <p class="value-custom"><?php echo $data['genero'] ? $data['genero'] : 'No Especificado'; ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="info-box-custom">
                            <h5 class="text-blue" style="margin-top: 0; font-weight: bold;"><i class="fa fa-user-md"></i> Médico Tratante</h5>
                            <p class="label-custom">Nombre del Profesional</p>
                            <p class="value-custom">Dr(a). <?php echo trim($data['nom_med'] . " " . $data['ape_med']); ?></p>
                        </div>

                        <h4 class="section-title" style="margin-top: 30px;"><i class="fa fa-file-text-o"></i> Más detalles</h4>
                        <div class="well-custom">
                            <?php if (!empty($med_principal) && !empty($med_principal['motivo'])) : ?>
                                <p>
                                    <strong>Motivo:</strong> <?php echo htmlspecialchars($med_principal['motivo']); ?>
                                </p>
                            <?php endif; ?>
                            <p>Medicamento Entregado A: <?php echo nl2br($data['entregado_a'] ?: 'No se registro a la persona...'); ?> </p>
                            <p>Tratamiento: <?php echo nl2br($data['tratamiento_indicado'] ?: 'No se registraron indicaciones adicionales.'); ?></p>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <h4 class="section-title"><i class="fa fa-list-alt"></i> Medicamentos Solicitados (<?php echo count($medicamentos); ?>)</h4>

                        <?php if (count($medicamentos) > 0) :
                            $med = $medicamentos[0];
                        ?>
                            <div>
                                <h5 class="text-blue" style="font-weight: bold; font-size: 16px; margin-bottom: 15px;">
                                    <i class="fa fa-medkit"></i> <?php echo $med['nombre_medicamento'] ?: 'No Especificado'; ?>
                                    <?php if (isset($med['cantidad_recetada']) && $med['cantidad_recetada'] > 0) : ?>
                                        <span class="badge bg-blue pull-right" style="font-size: 13px;">Cant: <?php echo $med['cantidad_recetada']; ?></span>
                                    <?php endif; ?>
                                </h5>

                                <table class="table table-bordered table-info-med">
                                    <tr>
                                        <th>Presentación / Cont. Neto</th>
                                        <td>
                                            <span><?php echo $med['nombre_presentacion'] ?: 'General'; ?> / </span>
                                            <?php echo $med['contenido_neto']; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Vía de Aplicación</th>
                                        <td><?php echo $med['via_aplicacion']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Código de Barras</th>
                                        <td><i class="fa fa-barcode"></i> <?php echo $med['codigo_barras']; ?></td>
                                    </tr>
                                </table>

                                <h5 style="margin-top: 15px; font-weight: bold; color: #555; font-size: 13px;"><i class="fa fa-flask"></i> Principios Activos</h5>
                                <?php if (empty($med['principios'])) : ?>
                                    <p class="text-muted"><i class="fa fa-info-circle"></i> No se han registrado principios activos.</p>
                                <?php else : ?>
                                    <div class="row">
                                        <?php foreach ($med['principios'] as $p) : ?>
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
                        <?php endif; ?>

                        <?php if (count($medicamentos) > 1) : ?>
                            <div style="margin-top: 25px; padding-top: 20px; border-top: 2px dashed #e0e0e0; text-align: center;">
                                <button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#modalOtrosMedicamentos" data-backdrop="static" data-keyboard="false">
                                    <i class="fa fa-plus-circle"></i> Ver los otros <?php echo count($medicamentos) - 1; ?> medicamentos
                                </button>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>

                <div style="float:right; margin-bottom: 20px; margin-top: 30px;">
                    <div class="col-xs-12">
                        <button type="button" class="btn btn-second" data-toggle="modal" data-target="#modalConfirmarRegreso" id="abrirModalRegresar">Regresar</button>

                        <?php if ($estado_limpio == 'pendiente' || $estado_limpio == 'parcial') : ?>
                            <a class="btn btn-success" href="farmacia_inventario_movimiento_despacho.php?id_pres=<?php echo $data['Id']; ?>&pac=<?php echo urlencode($cedula_a_enviar); ?>&menor=<?php echo $data['es_menor']; ?>&tipo=<?php echo $tipo_receta; ?>&from=prescripciones" class="btn btn-success btn-lg-custom pull-right shadow" style="margin-left: 10px;">
                                <i class="fa fa-check-circle"></i> Procesar y Despachar
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>

    </section>

    <div class="modal fade" id="modalConfirmarRegreso" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarRegresoLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-crimson">
                    <h4 class="modal-title" id="modalConfirmarRegresoLabel"><i class="fa fa-warning"></i> Confirmación de Regreso</h4>
                </div>
                <div class="modal-body">
                    <p>Al hacer clic en "Regresar al Listado", saldrá de los detalles de esta prescripción. ¿Desea continuar?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
                    <a href="farmacia_prescripciones_listado.php" class="btn btn-danger">Regresar al Inicio</a>
                </div>
            </div>
        </div>
    </div>

    <?php if (count($medicamentos) > 1) : ?>
        <div class="modal fade" id="modalOtrosMedicamentos" tabindex="-1" role="dialog" aria-labelledby="modalOtrosMedLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content" style="background-color: #f4f7f9;">
                    <div class="modal-header modal-header-primary">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 1;"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="modalOtrosMedLabel"><i class="fa fa-list-ul"></i> Resto de Medicamentos Solicitados</h4>
                    </div>
                    <div class="modal-body">

                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="selectMedicamentoModal" class="text-blue"><i class="fa fa-search"></i> Seleccione el medicamento a consultar:</label>
                            <select id="selectMedicamentoModal" class="form-control input-lg" style="border: 2px solid #3c8dbc; border-radius: 5px;">
                                <?php for ($i = 1; $i < count($medicamentos); $i++) : ?>
                                    <option value="<?php echo $i; ?>"><?php echo $medicamentos[$i]['nombre_medicamento'] ?: 'No Especificado'; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <?php for ($i = 1; $i < count($medicamentos); $i++) :
                            $med = $medicamentos[$i];
                        ?>
                            <div id="detalle-med-<?php echo $i; ?>" class="detalle-medicamento-modal" style="display: none; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e0e0e0;">
                                <h5 class="text-blue" style="font-weight: bold; font-size: 18px; margin-bottom: 15px; margin-top: 0;">
                                    <i class="fa fa-medkit"></i> <?php echo $med['nombre_medicamento'] ?: 'No Especificado'; ?>
                                    <?php if (isset($med['cantidad_recetada']) && $med['cantidad_recetada'] > 0) : ?>
                                        <span class="badge bg-blue pull-right" style="font-size: 13px;">Cant: <?php echo $med['cantidad_recetada']; ?></span>
                                    <?php endif; ?>
                                </h5>

                                <table class="table table-bordered table-info-med">
                                    <tr>
                                        <th>Presentación / Cont. Neto</th>
                                        <td>
                                            <span><?php echo $med['nombre_presentacion'] ?: 'General'; ?> / </span>
                                            <?php echo $med['contenido_neto']; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Vía de Aplicación</th>
                                        <td><?php echo $med['via_aplicacion']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Código de Barras</th>
                                        <td><i class="fa fa-barcode"></i> <?php echo $med['codigo_barras']; ?></td>
                                    </tr>
                                </table>

                                <h5 style="margin-top: 15px; font-weight: bold; color: #555; font-size: 14px;"><i class="fa fa-flask"></i> Principios Activos</h5>
                                <?php if (empty($med['principios'])) : ?>
                                    <p class="text-muted"><i class="fa fa-info-circle"></i> No se han registrado principios activos.</p>
                                <?php else : ?>
                                    <div class="row">
                                        <?php foreach ($med['principios'] as $p) : ?>
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
                        <?php endfor; ?>

                    </div>
                    <div class="modal-footer" style="background: white;">
                        <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar Lista</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php include('includes/footer.php'); ?>

<script>
    $(document).ready(function() {
        $('#selectMedicamentoModal').on('change', function() {
            var selectedId = $(this).val();
            $('.detalle-medicamento-modal').hide();
            $('#detalle-med-' + selectedId).fadeIn('fast');
        });

        if ($('#selectMedicamentoModal').length > 0) {
            $('#selectMedicamentoModal').trigger('change');
        }
    });
</script>