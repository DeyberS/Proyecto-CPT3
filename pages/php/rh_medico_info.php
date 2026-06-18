<?php
include("../../cfg/conexion.php");

$id_medico = isset($_GET['Id']) ? $_GET['Id'] : null;

if (!$id_medico) {
    echo "Error: ID de médico no especificado.";
    exit;
}

// Se añadió GROUP_CONCAT y GROUP BY para traer todas las especialidades y departamentos juntos
$sql = "SELECT p.id, p.nombre, p.apellido, p.tipo_cedula, p.cedula, p.fecha_nacimiento, p.genero, p.email,
               tp.telefono, pt.prefijo,
               dm.fecha_ingreso, dm.tipo_medico, dm.cod_colegiatura,
               GROUP_CONCAT(DISTINCT d.nombre_departamento SEPARATOR ', ') as nombre_departamento, 
               GROUP_CONCAT(DISTINCT e.nombre_especialidad SEPARATOR ', ') as nombre_especialidad
        FROM persona p
        LEFT JOIN telefonos_personas tp ON p.id = tp.Id_persona
        LEFT JOIN prefijos_telefonos pt ON tp.Id_prefijo = pt.Id
        LEFT JOIN detalle_medico dm ON p.id = dm.Id_persona
        LEFT JOIN medicos_departamentos md ON dm.Id_detalle_medico = md.Id_detalle_medico
        LEFT JOIN departamento d ON md.Id_departamento = d.Id_departamento
        LEFT JOIN especialidades_medicos em ON dm.Id_detalle_medico = em.Id_detalle_medico
        LEFT JOIN especialidad e ON em.Id_especialidad = e.Id_especialidad
        WHERE p.id = '$id_medico'
        GROUP BY p.id LIMIT 1";

$resultado = $conexion->query($sql);

if ($resultado && $resultado->num_rows > 0) {
    $row = $resultado->fetch_assoc();
} else {
    echo "<div class='alert alert-danger'><h4><i class='icon fa fa-ban'></i> Error</h4>Médico no encontrado.</div>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Médico | Informacion</title>
    <?php include('includes/headerNav2.php'); ?>
    <style>
        :root {
            --primary-dark: #2c3e50;
            --medical-blue: #007bff;
            --bg-gray: #f4f7f6;
        }

        .wrapper {
            display: block !important; 
            background-color: #f4f7f9 !important; 
        }

        .content-wrapper {
            background-color: #f4f7f9 !important;

        }

        .content-custom {
            padding: 0px 10px;
            margin-left: 60px;
        }

        body {
            background-color: var(--bg-gray) !important;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* BORDES RÍGIDOS Y EXTENSIÓN HACIA ABAJO */
        .profile-card {
            background: #fff;
            border-radius: 0px; /* Bordes rectos */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
            border: none;
            min-height: 80vh; /* Se extiende más hacia abajo */
        }

        .profile-header {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #34495e 100%);
            color: white;
            padding: 40px;
            border-bottom: 5px solid var(--medical-blue);
            position: relative;
        }

        /* PERFIL TRANSPARENTE (Sin recuadro blanco) */
        .doctor-avatar {
            width: 110px;
            height: 110px;
            background: transparent; /* Eliminado el fondo blanco */
            border-radius: 0px; 
            padding: 0;
            box-shadow: none; /* Eliminada la sombra */
            object-fit: contain;
        }

        .info-box-custom {
            background: #f8f9fa;
            border-radius: 0px; /* Bordes rectos */
            padding: 20px;
            border-left: 5px solid var(--medical-blue);
            height: 100%;
        }

        .label-custom {
            font-size: 11px;
            color: #95a5a6;
            text-transform: uppercase;
            font-weight: 700;
            display: block;
            margin-bottom: 2px;
        }

        .val-custom {
            font-size: 16px;
            color: var(--primary-dark);
            font-weight: 600;
        }

        .section-title {
            color: var(--medical-blue);
            font-weight: 700;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 20px;
            text-transform: uppercase;
            font-size: 14px;
        }

        .obs-box {
            margin-top: 30px; 
            background: #fff9e6; 
            padding: 15px; 
            border-radius: 0px; 
            border: 1px dashed #f39c12;
        }

        .data-item-card {
            background: #fff; 
            padding: 15px; 
            border-radius: 0px; 
            margin-bottom: 15px; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        /* Clases nuevas para los badges de diseño múltiple */
        .badge-depto {
            background-color: var(--medical-blue);
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            display: inline-block;
            margin-right: 5px;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .badge-espec {
            background-color: #2ecc71;
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            display: inline-block;
            margin-right: 5px;
            margin-bottom: 5px;
            font-weight: 600;
        }

        @media print {
            .no-print { display: none !important; }
            .profile-card { box-shadow: none; border: 1px solid #eee; }
        }
    </style>
</head>

<body class="hold-transition skin-blue sidebar-mini">
    <div class="content-wrapper">
        <section class="content">
            <div class="profile-card">
                <div class="profile-header">
                    <div class="row">
                        <div class="col-sm-2 text-center">
                            <img src="../../recursos/imagenes/iconos/medicos.png" class="doctor-avatar" alt="Médico">
                        </div>
                        <div class="col-sm-10">
                            <h2 style="margin: 5px 0; font-weight: 800;"><?php echo $row['nombre'] . " " . $row['apellido']; ?></h2>
                            <p style="font-size: 18px; opacity: 0.9;"><i class="fa fa-user-md"></i> Especialista en <?php echo !empty($row['nombre_especialidad']) ? $row['nombre_especialidad'] : 'Sin asignar'; ?></p>
                            <div class="row" style="margin-top: 20px;">
                                <div class="col-sm-3">
                                    <span class="label-custom" style="color: #bdc3c7;">Identificación</span>
                                    <span style="font-size: 18px; font-weight: bold;"><?php echo $row['tipo_cedula'] . "-" . $row['cedula']; ?></span>
                                </div>
                                <div class="col-sm-3">
                                    <span class="label-custom" style="color: #bdc3c7;">Fecha Ingreso</span>
                                    <span class="val-custom" style="color: #fff;"><?php echo date("d/m/Y", strtotime($row['fecha_ingreso'])); ?></span>
                                </div>
                                <div class="col-sm-3">
                                    <span class="label-custom" style="color: #bdc3c7;">Número de Colegiatura</span>
                                    <span class="val-custom" style="color: #fff;">#<?php echo str_pad($row['cod_colegiatura'], 5, "0", STR_PAD_LEFT); ?></span>
                                </div>
                                <div class="col-sm-3">
                                    <span class="label-custom" style="color: #bdc3c7;">Tipo de Medico</span>
                                    <span class="val-custom" style="color: #fff;"><?php echo str_pad($row['tipo_medico'], 5, "0", STR_PAD_LEFT); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body" style="padding: 35px 35px 60px 35px;">
                    <div class="row">
                        <div class="col-md-7">
                            <h4 class="section-title"><i class="fa fa-info-circle"></i> Datos Personales y de Contacto</h4>
                            
                            <div class="row" style="margin-bottom: 20px;">
                                <div class="col-sm-6">
                                    <span class="label-custom">Correo Electrónico</span>
                                    <span class="val-custom"><?php echo $row['email']; ?></span>
                                </div>
                                <div class="col-sm-6">
                                    <span class="label-custom">Teléfono Movil</span>
                                    <span class="val-custom"><?php echo $row['prefijo'] . "-" . $row['telefono']; ?></span>
                                </div>
                            </div>

                            <div class="row" style="margin-bottom: 20px;">
                                <div class="col-sm-6">
                                    <span class="label-custom">Género</span>
                                    <span class="val-custom"><?php echo $row['genero']; ?></span>
                                </div>
                                <div class="col-sm-6">
                                    <span class="label-custom">Fecha de Nacimiento</span>
                                    <span class="val-custom"><?php echo date("d/m/Y", strtotime($row['fecha_nacimiento'])); ?></span>
                                </div>
                            </div>

                            <div class="obs-box">
                                <span class="label-custom" style="color: #e67e22;">Observaciones Administrativas</span>
                                <p style="font-size: 13px; font-style: italic; color: #555; margin-bottom: 0;">
                                    "Personal médico debidamente acreditado para su área de trabajo."
                                </p>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="info-box-custom">
                                <h4 style="border-bottom: 2px solid #d6eaf8; padding-bottom: 10px; color: #2980b9; margin-top: 0;">
                                    <i class="fa fa-hospital-o"></i> Ubicación Laboral
                                </h4>

                                <div class="data-item-card">
                                    <span class="label-custom">Departamentos / Áreas</span>
                                    <div style="margin-top: 8px;">
                                        <?php 
                                        if (!empty($row['nombre_departamento'])) {
                                            $departamentos = explode(', ', $row['nombre_departamento']);
                                            foreach($departamentos as $dep) {
                                                echo '<span class="badge-depto"><i class="fa fa-building-o"></i> ' . trim($dep) . '</span>';
                                            }
                                        } else {
                                            echo '<span class="val-custom">No asignado</span>';
                                        }
                                        ?>
                                    </div>
                                </div>

                                <div class="data-item-card">
                                    <span class="label-custom">Especialidades</span>
                                    <div style="margin-top: 8px;">
                                        <?php 
                                        if (!empty($row['nombre_especialidad'])) {
                                            $especialidades = explode(', ', $row['nombre_especialidad']);
                                            foreach($especialidades as $esp) {
                                                echo '<span class="badge-espec"><i class="fa fa-stethoscope"></i> ' . trim($esp) . '</span>';
                                            }
                                        } else {
                                            echo '<span class="val-custom">No asignado</span>';
                                        }
                                        ?>
                                    </div>
                                </div>

                                <div class="text-center" style="margin-top: 25px; opacity: 0.6;">
                                    <i class="fa fa-stethoscope fa-4x" style="color: #bdc3c7;"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row no-print" style="margin-top: 60px; border-top: 1px solid #eee; padding-top: 20px;">
                        <div class="col-xs-12 text-right">
                            <button type="button" class="btn btn-default" style="border-radius: 0px;" data-toggle="modal" data-target="#modalConfirmarRegreso">
                                <i class="fa fa-chevron-left"></i> Volver al Listado
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="modal fade" id="modalConfirmarRegreso" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content" style="border-radius: 0px;">
                    <div class="modal-header" style="background-color: #dc3545; color: white;">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><i class="fa fa-warning"></i> Confirmación</h4>
                    </div>
                    <div class="modal-body">
                        <p>¿Desea cerrar la ficha del médico y regresar al listado principal?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" style="border-radius: 0px;" data-dismiss="modal">Cancelar</button>
                        <a href="rh_medico_listado.php" class="btn btn-danger" style="border-radius: 0px;">Regresar al inicio</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('includes/footer.php'); ?>
</body>
</html>