<?php
include("../../cfg/conexion.php");

$id_url = isset($_GET['Id']) ? (int)$_GET['Id'] : 0;
if ($id_url <= 0) die("ID no válido.");

// CONSULTA EXTENDIDA
$sql = "SELECT m.nombre_medicamento, dm.*, tm.nombre_tipo, l.nombre_laboratorio 
        FROM descripcion_medicamento dm
        JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento 
        LEFT JOIN tipo_medicamento tm ON dm.Id_tipo = tm.Id_tipo
        LEFT JOIN laboratorio l ON dm.Id_laboratorio = l.Id_laboratorio
        WHERE dm.Id = $id_url";

$resultado = $conexion->query($sql);
$row = $resultado->fetch_assoc();
if (!$row) die("Registro no encontrado.");

// PRINCIPIOS ACTIVOS
$principios = [];
$sql_pa = "SELECT pa.nombre, dpm.cantidad_unidad_medida, um.unidad 
           FROM detalle_principio_medicamento dpm
           JOIN principio_activo pa ON dpm.id_principio_activo = pa.id_principio_activo
           JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida
           WHERE dpm.id_medicamento = " . $row['Id'];
$res_pa = $conexion->query($sql_pa);
while ($p = $res_pa->fetch_assoc()) $principios[] = $p;
$texto_almacenamiento = [
    "-25_a_-10" => "Congelación (-25°C a -10°C)",
    "2_a_8"     => "Refrigeración (2°C a 8°C)",
    "8_a_15"    => "Lugar Fresco (8°C a 15°C)",
    "15_a_25"   => "Temperatura Ambiente (15°C a 25°C)",
    "max_30"    => "Temperatura Máxima (30°C)"
];
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Informacion del Medicamento | <?php echo $row['nombre_medicamento']; ?></title>
    <?php include('includes/headerNav2.php'); ?>

    <style>
        /* Evita que se pegue a la barra superior y bordes */
        .content-wrapper {
            background-color: #f4f7f9 !important;
        }

        .content-custom {
            padding: 70px 15px;
        }

        .main-container {
            background: white;
            min-height: 60vh;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .hero-header {
            background: linear-gradient(135deg, #3c8dbc 0%, #222d32 100%);
            color: white;
            padding: 30px;
            border-bottom: 4px solid #00a65a;
        }

        .info-padding {
            padding: 30px;
        }

        /* Estilo de tablas para los datos */
        .table-info-med th {
            background: #f9f9f9;
            width: 35%;
            color: #777;
            font-size: 12px;
            text-transform: uppercase;
        }

        .table-info-med td {
            font-size: 15px;
            font-weight: 600;
            color: #333;
        }

        .pa-item {
            background: #fdfdfd;
            border: 1px solid #eee;
            border-left: 4px solid #00a65a;
            padding: 10px 15px;
            margin-bottom: 5px;
            border-radius: 3px;
        }

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
    </style>
</head>

<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        <div class="content-wrapper">
            <section class="content content-custom">
                <div class="main-container">
                    <div class="hero-header text-center">
                        <h1 style="margin:0; font-weight: 800;"><?php echo $row['nombre_medicamento']; ?></h1>
                        <p style="margin-top:5px; font-size: 16px; opacity: 0.9;">
                            <i class="fa fa-industry"></i> <?php echo $row['nombre_laboratorio'] ?: 'No Especificado'; ?>
                        </p>
                    </div>

                    <div class="info-padding">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="page-header text-blue"><i class="fa fa-list-alt"></i> Datos Técnicos </h4>
                                <table class="table table-bordered table-info-med">
                                    <tr>
                                        <th>Código de Barras</th>
                                        <td><?php echo $row['codigo_barras']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Categoría</th>
                                        <td><?php echo $row['nombre_tipo'] ?: 'General'; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Vía de Aplicación</th>
                                        <td><?php echo $row['via_aplicacion']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Presentación</th>
                                        <td><?php echo $row['presentacion']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Almacenamiento</th>
                                        <td>
                                            <i class="fa fa-snowflake-o text-info"></i>
                                            <?php
                                            $valor_db = $row['almacenamiento'];
                                            // Si el valor está en nuestro mapa, lo imprimimos; si no, mostramos el original
                                            echo isset($texto_almacenamiento[$valor_db]) ? $texto_almacenamiento[$valor_db] : str_replace('_', ' ', $valor_db);
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Composicion</th>
                                        <td><?php echo $row['composicion'] ?: 'No especificada'; ?></td>
                                    </tr>
                                </table>
                            </div>

                            <div class="col-md-6">
                                <h4 class="page-header text-green"><i class="fa fa-flask"></i> Principio Activo</h4>
                                <?php if (empty($principios)) : ?>
                                    <p class="text-muted">Sin principios activos.</p>
                                <?php else : ?>
                                    <?php foreach ($principios as $p) : ?>
                                        <div class="pa-item">
                                            <strong><?php echo $p['nombre']; ?></strong>
                                            <span class="pull-right badge bg-green"><?php echo $p['cantidad_unidad_medida'] . " " . $p['unidad']; ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <hr>
                                <div class="no-print text-right" style="margin-top:210px;">
                                    <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#modalConfirmarRegreso">Regresar</button>
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
    </div>
</body>

</html>