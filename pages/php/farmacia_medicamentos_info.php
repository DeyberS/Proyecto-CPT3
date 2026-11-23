<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include("../../cfg/conexion.php");

$id_url = isset($_GET['Id']) ? (int)$_GET['Id'] : 0;

if ($id_url <= 0) {
    $_SESSION['mensaje_user_error'] = "Debe seleccionar una consulta válida para ver su información.";
    header("Location: farmacia_medicamentos_listado.php");
    exit;
}

$sql = "SELECT m.nombre_medicamento, dm.*, p.nombre_presentacion, l.nombre_laboratorio, u.unidad 
        FROM descripcion_medicamento dm
        JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento 
        LEFT JOIN unidad_medida u ON dm.Id_tipo_concentracion = u.Id_unidad_medida
        LEFT JOIN presentacion p ON dm.Id_presentacion = p.Id_presentacion
        LEFT JOIN laboratorio l ON dm.Id_laboratorio = l.Id_laboratorio
        WHERE dm.Id = $id_url";

$resultado = $conexion->query($sql);
$row = $resultado ? $resultado->fetch_assoc() : null;

if (!$row) {
    $_SESSION['mensaje_user_error'] = "No se encontró la información del medicamento solicitado.";
    header("Location: farmacia_medicamentos_listado.php");
    exit;
}

$principios = [];
$sql_pa = "SELECT pa.nombre, dpm.cantidad_unidad_medida, um.unidad 
           FROM detalle_principio_medicamento dpm
           JOIN principio_activo pa ON dpm.id_principio_activo = pa.id_principio_activo
           JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida
           WHERE dpm.id_descripcion_medicamento = " . (int)$row['Id'];
$res_pa = $conexion->query($sql_pa);
while ($p = $res_pa->fetch_assoc()) $principios[] = $p;

$patologias = [];
$sql_pat = "SELECT pat.nombre_patologia
           FROM detalle_patologia_medicamento dptm
           JOIN patologias pat ON dptm.Id_patologia = pat.Id_patologia
           WHERE dptm.Id_descripcion_medicamento = " . (int)$row['Id'];
$res_pat = $conexion->query($sql_pat);
while ($pat = $res_pat->fetch_assoc()) $patologias[] = $pat;
$texto_almacenamiento = [
    "-25_a_-10" => "Congelación (-25°C a -10°C)",
    "2_a_8"     => "Refrigeración (2°C a 8°C)",
    "8_a_15"    => "Lugar Fresco (8°C a 15°C)",
    "15_a_25"   => "Temperatura Ambiente (15°C a 25°C)",
    "max_30"    => "Temperatura Máxima (30°C)"
];

include('includes/headerNav2.php');
verificarAcceso('Ver Medicamentos');
?>

<head>
    <title>Medicamento | Información</title>
</head>

<div class="content-wrapper">
    <style>
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
            padding: 50px 10px;
            margin-left: 0px;
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
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .hero-header h1 {
            margin: 0;
            font-weight: 800;
            font-size: 2em;
            line-height: 1.2;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .hero-header p {
            margin-top: 5px;
            font-size: 16px;
            opacity: 0.9;
        }

        .info-padding {
            padding: 30px;
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
            font-size: 15px;
            font-weight: 600;
            color: #333;
            vertical-align: middle !important;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .pa-item,
        .pat-item {
            background: #fdfdfd;
            border: 1px solid #eee;
            padding: 10px 15px;
            margin-bottom: 5px;
            border-radius: 3px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
        }

        .pa-item {
            border-left: 4px solid #00a65a;
        }

        .pat-item {
            border-left: 4px solid crimson;
        }

        .pa-item strong,
        .pat-item strong {
            flex: 1 1 auto;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .pa-item .badge {
            flex: 0 0 auto;
        }

        .nav-tabs>li>a {
            pointer-events: none;
            cursor: default;
        }

        .nav-tabs li.disabled-tab a {
            color: #b2b2b2 !important;
        }

        /* El botón Regresar deja de depender de margin-top inline */
        .info-padding .no-print.text-right {
            margin-top: 50px !important;
        }

        /* ============================================================
       TABLET (768 – 991 px)
       ============================================================ */
        @media (max-width: 991px) {
            .content-custom {
                padding: 25px 15px;
            }

            .hero-header {
                padding: 24px 20px;
            }

            .hero-header h1 {
                font-size: 1.7em;
            }

            .info-padding {
                padding: 20px;
            }

            .table-info-med th {
                width: 40%;
                font-size: 11px;
            }
        }

        /* ============================================================
       MÓVIL (≤ 767 px)  — Android / iPhone
       ============================================================ */
        @media (max-width: 767px) {
            .content-custom {
                margin-left: 0 !important;
                padding: 15px 8px;
            }

            .main-container {
                border-radius: 6px;
                min-height: auto;
            }

            .hero-header {
                padding: 20px 15px;
            }

            .hero-header h1 {
                font-size: 1.4em;
                line-height: 1.25;
            }

            .hero-header p {
                font-size: 13px;
            }

            .info-padding {
                padding: 15px;
            }

            .page-header {
                font-size: 16px;
                margin: 15px 0 10px 0;
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

            .pa-item,
            .pat-item {
                padding: 8px 10px;
                font-size: 13px;
            }

            .pa-item .badge {
                font-size: 11px !important;
            }

            /* Las dos columnas (Datos Técnicos / Principio Activo)
           se apilan con un pequeño margen entre ellas */
            .info-padding>.row>.col-md-6 {
                float: none !important;
                width: 100% !important;
            }

            .info-padding>.row>.col-md-6+.col-md-6 {
                margin-top: 15px;
            }

            /* El botón Regresar ocupa todo el ancho,
           sin el margin-top:210px inline que deja hueco */
            .info-padding .no-print.text-right {
                margin-top: 15px !important;
                text-align: center !important;
            }

            .info-padding .no-print.text-right .btn {
                width: 100%;
                padding: 10px;
                font-size: 14px;
            }

            .info-padding hr {
                margin: 15px 0 !important;
            }
        }

        /* ============================================================
       MÓVIL PEQUEÑO (≤ 360 px)
       ============================================================ */
        @media (max-width: 360px) {
            .content-custom {
                padding: 10px 5px;
            }

            .hero-header {
                padding: 15px 12px;
            }

            .hero-header h1 {
                font-size: 1.15em;
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
        }
    </style>

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
                                <th>Presentación</th>
                                <td><?php echo $row['nombre_presentacion'] ?: 'General'; ?></td>
                            </tr>
                            <tr>
                                <th>Vía de Aplicación</th>
                                <td><?php echo $row['via_aplicacion']; ?></td>
                            </tr>
                            <tr>
                                <th>Contenido Neto</th>
                                <td><?php echo $row['contenido_neto']; ?></td>
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
                                <th>Excipientes</th>
                                <td><?php echo $row['excipientes'] ?: 'No especificada'; ?></td>
                            </tr>
                            <tr>
                                <th>Concentración</th>
                                <td><?php echo $row['cantidad_concentracion'] ?: 'No especificada'; ?><?php echo $row['unidad']; ?></td>
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
                        <h4 class="page-header text-crimson"><i class="fa fa-flask"></i> Patologias </h4>
                        <?php if (empty($patologias)) : ?>
                            <p class="text-muted">Sin patologias.</p>
                        <?php else : ?>
                            <?php foreach ($patologias as $pat) : ?>
                                <div class="pat-item">
                                    <strong><?php echo $pat['nombre_patologia']; ?></strong>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <hr>
                        <div class="no-print text-right" style="margin-top:210px;">
                            <button type="button" class="btn btn-second" data-toggle="modal" data-target="#modalConfirmarRegreso">Regresar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="modalConfirmarRegreso" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarRegresoLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-crimson">
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
</div>

<?php include('includes/footer.php'); ?>