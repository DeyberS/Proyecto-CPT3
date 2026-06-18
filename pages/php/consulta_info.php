<?php
include("../../cfg/conexion.php");

$id_consulta = $_GET['Id'] ?? null;
$datos_consulta = [];
$diagnosticos_cargados = [];
$medicamentos_cargados = [];
$error_busqueda = null;

if (!$id_consulta || !is_numeric($id_consulta)) {
    $error_busqueda = "ID de consulta no válido.";
} elseif (isset($conexion)) {
    try {
        $safe_id = $conexion->real_escape_string($id_consulta);

        // 1. CONSULTA MAESTRA (Ajustada a las llaves de tu SQL)
        $sql_consulta = "
        SELECT 
            c.*,
            p.nombre AS pac_nombre, p.apellido AS pac_apellido, p.tipo_cedula AS pac_tipo_cedula, p.cedula AS pac_cedula, p.fecha_nacimiento, 
            h.Id_historial
        FROM consulta c
        JOIN historial_medico h ON c.Id_historial = h.Id_historial
        JOIN persona p ON h.Id_persona = p.Id
        WHERE c.Id_consulta = '$safe_id'
        ";

        $result_consulta = $conexion->query($sql_consulta);
        $datos_consulta = ($result_consulta && $result_consulta->num_rows > 0) ? $result_consulta->fetch_assoc() : null;

        if (!$datos_consulta) {
            $error_busqueda = "La consulta #$id_consulta no existe.";
        } else {
            // 2. DIAGNÓSTICOS (Basado en tu tabla consulta_diagnostico)
            $sql_diag = "SELECT c.* 
                         FROM consulta c
                         WHERE c.Id_consulta = '$safe_id'";
            $res_diag = $conexion->query($sql_diag);
            while ($row = $res_diag->fetch_assoc()) {
                $diagnosticos_cargados[] = $row;
            }
            $id_historial = $datos_consulta['Id_historial'];
            $id_medico_consulta = $datos_consulta['Id_medico']; // ID del médico guardado en la consulta

            // A. OBTENER NOMBRE DEL MÉDICO
            $nombre_medico = "No asignado";
            $sql_medico_info = "SELECT dm.Id_detalle_medico, dm.Id_persona AS id, p.nombre, p.apellido 
            FROM detalle_medico dm
            INNER JOIN persona p ON dm.Id_persona = p.id
            WHERE dm.Id_detalle_medico = '$id_medico_consulta'";
            $res_med_info = $conexion->query($sql_medico_info);
            if ($res_med_info && $med_row = $res_med_info->fetch_assoc()) {
                $nombre_medico = $med_row['nombre'] . ' ' . $med_row['apellido'];
            }

            $nota_cargada = '';
            if ($id_historial) {
                $sql_notas = "SELECT observacion FROM observaciones_historial_medico 
                      WHERE Id_historial_medico = '$id_historial'
                      ORDER BY fecha DESC, Id DESC LIMIT 1";
                $result_notas = $conexion->query($sql_notas);
                if ($result_notas && $row_nota = $result_notas->fetch_assoc()) {
                    $nota_cargada = $row_nota['observacion'];
                }
            }


            $medicamentos_lista = [];
            // Usamos una consulta que respeta las llaves foráneas de tu archivo .sql
            $sql_med = "SELECT 
            m.nombre_medicamento AS nombre, 
            
            -- 1. Nombres de los principios activos separados por coma
            GROUP_CONCAT(pa.nombre SEPARATOR ' + ') AS nombre_principio_activo,
            
            ps.nombre_presentacion AS presentacion,
            
            -- 2. Dosis formateadas: (200mg) o (200mg + 400mg)
            CONCAT('(', GROUP_CONCAT(CONCAT(dpm.cantidad_unidad_medida, ' ', um_pa.unidad) SEPARATOR ' + '), ')') AS dosis,
            
            -- Otros datos necesarios
            dm.contenido_neto AS detalle_empaque,
            dm.via_aplicacion,
            dm.almacenamiento,
            l.nombre_laboratorio
            
            FROM prescripcion_medicamentos p
            INNER JOIN descripcion_medicamento dm ON p.Id_descripcion_medicamento = dm.Id 
            INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento 
            LEFT JOIN presentacion ps ON dm.Id_presentacion = ps.Id_presentacion 
            
            -- Relación corregida con dm.Id según tu estructura
            LEFT JOIN detalle_principio_medicamento dpm ON dm.Id = dpm.id_medicamento
            LEFT JOIN principio_activo pa ON dpm.id_principio_activo = pa.Id_principio_activo
            LEFT JOIN unidad_medida um_pa ON dpm.id_tipo_unidad_medida = um_pa.Id_unidad_medida
            LEFT JOIN laboratorio l ON dm.Id_laboratorio = l.Id_laboratorio
            
            WHERE p.Id_consulta = '$safe_id'
            
            -- Agrupamos para que los principios del mismo medicamento se junten
            GROUP BY dm.Id";
            
            $res_med = $conexion->query($sql_med);
            
            if ($res_med && $res_med->num_rows > 0) {
                while ($row = $res_med->fetch_assoc()) {
                    $medicamentos_lista[] = $row;
                }
            } else {
                // Si sigue sin salir nada, imprime el error para debug
                echo "Error o sin datos: " . $conexion->error;
            }

            // Extraemos el tratamiento general de la consulta maestra para usarlo en la tabla
            $tratamiento_general = $datos_consulta['tratamiento_indicado'] ?? 'Sin indicaciones';
        }
    } catch (Exception $e) {
        $error_busqueda = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Consulta Médica #<?= $id_consulta ?></title>
    <?php include('includes/headerNav2.php'); ?>
    <style>
        /* Estilos copiados de consulta_nueva.php */
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

        /* El modal.in y modal.out controlan la animación del modal-dialog */
        .modal.in .modal-dialog {
            animation: fadeIn 0.4s ease-out;
        }

        .modal.out .modal-dialog {
            animation: fadeOut 0.4s ease-in;
        }

        /* El backdrop usa pulse-opacity */
        .modal-open .modal-backdrop {
            opacity: 0.7 !important;
            animation: pulse-opacity 0.3s forwards;
        }

        /* ---------------------------------------------------------------------- */
        /* ESTILOS DE VALIDACIÓN Y LAYOUT */
        /* ---------------------------------------------------------------------- */
        .input-error {
            border: 2px solid crimson !important;
            box-shadow: 0 0 5px crimson;
        }

        #avisoModal,
        #modalPatologias,
        #modalAlergias,
        #modalGuardarMedico {
            z-index: 999999 !important;
        }

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

        /* MODIFICACIÓN SOLICITADA: Bloquear click en las pestañas */
        .nav-tabs>li>a {
            pointer-events: none;
            cursor: default;
        }

        /* Estilos para pestañas bloqueadas visualmente */
        .nav-tabs li.disabled-tab a {
            color: #b2b2b2 !important;
        }

        .modal-header-danger {
            background-color: #dc3545;
            color: white;
        }

        /* NUEVO: Estilo para etiquetas de medicamento en el modal */
        .medicamento-tag {
            display: inline-block;
            padding: 5px 10px;
            margin-right: 5px;
            margin-bottom: 5px;
            background-color: #3c8dbc;
            color: white;
            border-radius: 4px;
            font-size: 14px;
            cursor: default;
        }

        .medicamento-tag .close-btn {
            margin-left: 8px;
            font-weight: bold;
            cursor: pointer;
            color: white;
            text-shadow: none;
            opacity: 1;
        }
    </style>
    <style>
        .report-box {
            background: #fff;
            padding: 30px;
            margin: 20px auto;
            max-width: 20000px;
            border-top: 5px solid #00c0ef;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .info-header {
            border-bottom: 2px solid #f4f4f4;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }

        .vitals-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        .vital-card {
            background: #f9f9f9;
            border: 1px solid #eee;
            padding: 10px;
            border-radius: 4px;
            flex: 1;
            min-width: 100px;
            text-align: center;
        }

        .vital-card small {
            display: block;
            color: #777;
            font-weight: bold;
        }

        .doctor-box {
            background: #f0f9ff;
            border: 1px solid #cce5ff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        @media print {
            .no-print {
                display: none;
            }

            .report-box {
                box-shadow: none;
                border: none;
            }
        }
    </style>
</head>

<body class="hold-transition skin-blue sidebar-mini">
    <div class="content-wrapper">
        <section class="content">
            <?php if ($error_busqueda) : ?>
                <div class="alert alert-danger"><?= $error_busqueda ?></div>
            <?php else : ?>
                <div class="report-box">
                    <div class="info-header">
                        <div class="row">
                            <div class="col-xs-8">
                                <h3 style="margin:0;">RESUMEN DE CONSULTA MÉDICA</h3>
                                <p><b>Paciente:</b> <?= $datos_consulta['pac_nombre'] . ' ' . $datos_consulta['pac_apellido'] ?><br>
                                   <b>Cédula:</b> <?= $datos_consulta['pac_tipo_cedula'] ?>-<?= $datos_consulta['pac_cedula'] ?>
                                </p>
                            </div>
                            <div class="col-xs-4 text-right">
                                <b>Fecha:</b> <?= date('d/m/Y', strtotime($datos_consulta['fecha_consulta'])) ?><br>
                                <b>Folio:</b> #<?= $id_consulta ?><br>
                                <b>Médico:</b> DR(A). <?= $nombre_medico ?><br>
                            </div>
                        </div>
                    </div>

                    <h4>Signos Vitales</h4>
                    <div class="vitals-row">
                        <div class="vital-card"><small>Peso</small> <?= $datos_consulta['peso'] ?: '--' ?> kg</div>
                        <div class="vital-card"><small>Talla</small> <?= $datos_consulta['talla'] ?: '--' ?> cm</div>
                        <div class="vital-card"><small>Temp.</small> <?= $datos_consulta['temperatura'] ?: '--' ?> °C</div>
                        <div class="vital-card"><small>Tensión</small> <?= $datos_consulta['tension'] ?: '--' ?></div>
                        <div class="vital-card"><small>Saturación</small> <?= $datos_consulta['saturacion'] ?: '--' ?> %</div>
                        <div class="vital-card"><small>Tensión</small> <?= $datos_consulta['frecuencia_cardiaca'] ?: '--' ?></div>
                        <div class="vital-card"><small>Saturación</small> <?= $datos_consulta['frecuencia_respiratoria'] ?: '--' ?> %</div>
                    </div>

                    <h4>Detalles Clínicos</h4>
                    <p>
                        <b>Motivo: </b> <?= nl2br($datos_consulta['motivo_consulta']) ?>

                        <b class="pull-right">Diagnóstico(s): <?= nl2br($datos_consulta['diagnostico']) ?></b>

                    <p><b>Observaciones:</b> <?= $nota_cargada ?>

                        
                    </p>

                    <br>
                    <h4>Medicamentos recibidos por: <?= nl2br($datos_consulta['entregado_a']) ?></h4>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr class="active">
                                <th>Medicamento</th>
                                <th>Dosis</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($medicamentos_lista)) : ?>
                                <?php foreach ($medicamentos_lista as $med) : ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($med['nombre']); ?> - <?php echo htmlspecialchars($med['nombre_principio_activo']); ?> (<?php echo htmlspecialchars($med['presentacion']); ?>)</td>

                                        <td><?php echo htmlspecialchars($med['dosis']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="3" class="text-center">No hay medicamentos registrados para esta consulta.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <div class="no-print text-right" style="margin-top:20px;">
                        <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#modalConfirmarRegreso">Regresar</button>
                        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modalDetallesCompletos">
                            <i class="fa fa-eye"></i> Ver más detalles
                        </button>
                        <a href="../../cfg/consulta_receta_pdf.php?id_consulta=<?php echo $datos_consulta['Id_consulta'] ?>" class="btn btn-primary"><i class="fa fa-print"></i> Imprimir Receta Medica</a>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <div class="modal fade" id="modalDetallesCompletos" tabindex="-1" role="dialog" aria-labelledby="labelDetalles">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #5bc0de; color:white;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="labelDetalles"><i class="fa fa-file-text-o"></i> Detalles Adicionales de la Consulta</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="box box-solid box-default">
                                <div class="box-header">
                                    <h3 class="box-title">Evolución y Reacción</h3>
                                </div>
                                <div class="box-body">
                                    <strong><i class="fa fa-line-chart"></i> Estado del Paciente:</strong>
                                    <p class="text-muted"><?php echo nl2br(htmlspecialchars($datos_consulta['estado_paciente'] ?? 'No registrada')); ?></p>
                                    <hr>
                                    <strong><i class="fa fa-line-chart"></i> Evolución:</strong>
                                    <p class="text-muted"><?php echo nl2br(htmlspecialchars($datos_consulta['evolucion_resultado'] ?? 'No registrada')); ?></p>
                                    <hr>
                                    <strong><i class="fa fa-warning"></i> Reacción Adversas:</strong>
                                    <p class="text-muted"><?php echo nl2br(htmlspecialchars($datos_consulta['reaccion_adversa'] ?? 'Ninguna')); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="box box-solid box-default">
                                <div class="box-header">
                                    <h3 class="box-title">Exámenes y Paraclínicos</h3>
                                </div>
                                <div class="box-body">
                                    <strong><i class="fa fa-flask"></i> Lectura de Exámenes:</strong>
                                    <p class="text-muted"><?php echo nl2br(htmlspecialchars($datos_consulta['lectura_examenes'] ?? 'No registrados')); ?></p>
                                    <hr>
                                    <strong><i class="fa fa-plus-square"></i> Nuevos Exámenes Solicitados:</strong>
                                    <p class="text-muted"><?php echo nl2br(htmlspecialchars($datos_consulta['examenes_solicitados'] ?? 'Ninguno')); ?></p>
                                    <hr>
                                    <strong><i class="fa fa-plus-square"></i> Detalle De La Reaccion:</strong>
                                    <p class="text-muted"><?php echo nl2br(htmlspecialchars($datos_consulta['detalle_reaccion'] ?? 'No registrados')); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="box box-solid box-default">
                                <div class="box-header">
                                    <h3 class="box-title">Plan de Tratamiento y Observaciones</h3>
                                </div>
                                <div class="box-body">
                                    <p><?php echo nl2br(htmlspecialchars($datos_consulta['tratamiento_indicado'] ?? 'Sin plan registrado')); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="modalConfirmarRegreso" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarRegresoLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-danger">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="modalConfirmarRegresoLabel"><i class="fa fa-warning"></i>Confirmación de Regreso</h4>
                </div>
                <div class="modal-body">
                    <p>Esta apunto de regresar al inicio. ¿Desea continuar?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
                    <a href="javascript:history.back()" class="btn btn-danger">Regresar al Inicio</a>
                </div>
            </div>
        </div>
    </div>

    <?php include('includes/footer.php'); ?>

</body>

</html>