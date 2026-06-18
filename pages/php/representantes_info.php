<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Representantes | Información</title>
  <?php
  include('includes/headerNav2.php');
  ?>
  <style>
    :root {
        --primary-dark: #2c3e50;
        --medical-blue: #007bff;
        --bg-gray: #f4f7f9;
    }

    body {
        background-color: var(--bg-gray) !important;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .content-wrapper {
        background-color: var(--bg-gray) !important;
    }

    .content-custome {
      padding: 0px, 0px;
      margin-left: 0px;
    }

    .content {
      padding: 0px;
    }

    /* --- CONTENEDOR PRINCIPAL --- */
    .profile-card {
        max-width: 1100px;
        margin: 20px auto; 
        background: #fff;
        border-radius: 0px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        border: none;
        min-height: 80vh;
        overflow: hidden;
    }

    /* --- CABECERA ESTILO --- */
    .profile-header {
        background: linear-gradient(135deg, var(--primary-dark) 0%, #34495e 100%);
        color: white;
        padding: 40px;
        border-bottom: 5px solid var(--medical-blue);
    }

    .paciente-avatar {
        width: 110px;
        height: 110px;
        background: transparent;
        border-radius: 0px;
        padding: 0;
        box-shadow: none;
        object-fit: contain;
    }

    /* --- ETIQUETAS Y VALORES --- */
    .label-custom {
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 700;
        display: block;
        margin-bottom: 2px;
    }

    .label-light { color: #bdc3c7; }
    .label-dark { color: #95a5a6; }

    .val-custom {
        font-size: 16px;
        font-weight: 600;
    }

    .val-light { color: #fff; }
    .val-dark { color: var(--primary-dark); }

    .section-title {
        color: var(--medical-blue);
        font-weight: 700;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
        margin-bottom: 20px;
        text-transform: uppercase;
        font-size: 14px;
    }

    .data-item-card {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 0px;
        border-left: 4px solid var(--medical-blue);
        margin-bottom: 15px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.02);
    }

    /* --- SISTEMA DE PESTAÑAS (TABS) --- */
    .nav-tabs-custom {
        background: transparent;
        box-shadow: none;
    }

    .nav-tabs {
        border-bottom: 2px solid #eee;
        background: #f8f9fa;
        padding: 0 20px;
    }

    .nav-tabs > li > a {
        border-radius: 0 !important;
        color: #7f8c8d !important;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 13px;
        padding: 15px 20px;
        border: none !important;
        transition: all 0.3s ease;
    }

    .nav-tabs > li > a:hover {
        background-color: #e9ecef;
        color: var(--primary-dark) !important;
    }

    .nav-tabs > li.active > a, 
    .nav-tabs > li.active > a:focus, 
    .nav-tabs > li.active > a:hover {
        color: var(--medical-blue) !important;
        background-color: #fff !important;
        border-bottom: 3px solid var(--medical-blue) !important;
    }

    .tab-content-container {
        padding: 30px;
    }

    .badge-estado {
        padding: 4px 10px;
        border-radius: 0px;
        font-weight: 600;
        font-size: 11px;
        text-transform: uppercase;
    }

    /* Botones de Navegación Inferior */
    .btn-nav-container {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }

    /* Estilos Modales */
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-50px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes fadeOut { from { opacity: 1; transform: translateY(0); } to { opacity: 0; transform: translateY(-50px); } }
    .modal.in .modal-dialog { animation: fadeIn 0.3s ease-out; }
    .modal.out .modal-dialog { animation: fadeOut 0.3s ease-in; }
    .modal-content { border-radius: 0px !important; }
  </style>
</head>

<body class="hold-transition skin-blue sidebar-mini">
  <div class="content-wrapper">
    <section class="content content-custome">
      <div class="row">
        <div class="col-xs-12">
          
          <div class="profile-card">
            
            <?php
            include("../../cfg/conexion.php");

            // VALIDAR ID
            $id_representante = isset($_GET['Id']) ? intval($_GET['Id']) : 0;

            // CONSULTA SQL (Resolviendo los nombres como en pacientes_info)
            $sql = "SELECT p.id, p.email, p.nombre, p.apellido, p.tipo_cedula, p.cedula, p.fecha_nacimiento, p.genero,
                tp.telefono, pt.prefijo,
                d.avenida_calle, d.referencia, d.tiempo_residencia, d.tiempo,
                
                paisnac.nombre_pais AS nombre_pais_nac, 
                estnac.nombre_estado AS nombre_estado_nac,   
                munnac.nombre_municipio AS nombre_municipio_nac,  
                
                dirsec.nombre_sector AS nombre_sector_dir,
                dirmun.nombre_municipio AS nombre_municipio_dir,
                direst.nombre_estado AS nombre_estado_dir

                FROM persona p
                LEFT JOIN telefonos_personas tp ON p.id = tp.Id_persona
                LEFT JOIN prefijos_telefonos pt ON tp.Id_prefijo = pt.Id
                
                LEFT JOIN lugar_nacimiento ln ON p.id = ln.Id_persona
                LEFT JOIN municipio munnac ON ln.Id_municipio = munnac.Id_Municipio
                LEFT JOIN estado estnac ON munnac.Id_Estado = estnac.Id_Estado
                LEFT JOIN pais paisnac ON estnac.Id_Pais = paisnac.Id_Pais

                LEFT JOIN direccion d ON p.id = d.Id_persona
                LEFT JOIN sector dirsec ON d.Id_sector = dirsec.Id_Sector
                LEFT JOIN municipio dirmun ON dirsec.Id_Municipio = dirmun.Id_Municipio
                LEFT JOIN estado direst ON dirmun.Id_Estado = direst.Id_Estado
                
                WHERE p.id = $id_representante LIMIT 1";

            $resultado = $conexion->query($sql);
            if($resultado && $resultado->num_rows > 0) {
                $row = $resultado->fetch_assoc();
            } else {
                echo "<div class='alert alert-danger'>Representante no encontrado.</div>";
                exit;
            }

            // Calcular Edad
            $fecha_nac = new DateTime($row['fecha_nacimiento']);
            $hoy = new DateTime();
            $edad = $hoy->diff($fecha_nac)->y;
            ?>

            <input type="hidden" name="Id" value="<?= $row['id']; ?>">

            <div class="profile-header">
                <div class="row w-100">
                    <div class="col-sm-2 text-center">
                        <img src="../../recursos/imagenes/iconos/Paciente_icon1.png" class="paciente-avatar" alt="Foto Representante">
                    </div>
                    <div class="col-sm-10">
                        <h2 style="margin: 5px 0; font-weight: 800;"><?php echo $row['nombre']; ?> <?php echo $row['apellido']; ?></h2>
                        <p style="font-size: 18px; opacity: 0.9;"><i class="fa fa-id-card-o"></i> Documento: <?php echo $row['tipo_cedula']; ?>-<?php echo $row['cedula']; ?></p>
                        <div class="row" style="margin-top: 20px;">
                            <div class="col-xs-3">
                                <span class="label-custom label-light">F. Nacimiento</span>
                                <span class="val-custom val-light"><?php echo date("d/m/Y", strtotime($row['fecha_nacimiento'])); ?></span>
                            </div>
                            <div class="col-xs-3">
                                <span class="label-custom label-light">Edad</span>
                                <span class="val-custom val-light"><?php echo $edad; ?> años</span>
                            </div>
                            <div class="col-xs-3">
                                <span class="label-custom label-light">Sexo</span>
                                <span class="val-custom val-light"><?php echo !empty(trim($row['genero'])) ? $row['genero'] : 'No registrado'; ?></span>
                            </div>
                            <div class="col-xs-3">
                                <span class="label-custom label-light">Rol</span>
                                <span class="val-custom val-light">Representante / Tutor</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="nav-tabs-custom">
              <ul class="nav nav-tabs">
                <li class="active" data-tab-name="info"><a href="#info" data-toggle="tab"><i class="fa fa-user"></i> Datos Personales</a></li>
                <li data-tab-name="direccion"><a href="#direccion" data-toggle="tab"><i class="fa fa-map-marker"></i> Dirección de Residencia</a></li>
                <li data-tab-name="pacientes_cargo"><a href="#pacientes_cargo" data-toggle="tab"><i class="fa fa-child"></i> Pacientes a Cargo</a></li>
              </ul>

              <div class="tab-content tab-content-container">

                <div class="tab-pane active" id="info">
                    <div class="row">
                        <div class="col-md-6">
                            <h4 class="section-title"><i class="fa fa-phone"></i> Información de Contacto</h4>
                            
                            <div class="data-item-card">
                                <span class="label-custom label-dark">N. Teléfono</span>
                                <span class="val-custom val-dark"><?php echo !empty(trim($row['telefono'])) ? $row['prefijo'].'-'.$row['telefono'] : 'Ninguno'; ?></span>
                            </div>
                            
                            <div class="data-item-card">
                                <span class="label-custom label-dark">Correo Electrónico</span>
                                <span class="val-custom val-dark"><?php echo !empty(trim($row['email'])) ? $row['email'] : 'Ninguno'; ?></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h4 class="section-title"><i class="fa fa-globe"></i> Origen</h4>
                            <div class="data-item-card" style="border-left-color: #95a5a6;">
                                <span class="label-custom label-dark">Lugar de Nacimiento</span>
                                <p style="margin: 5px 0 0 0; color: var(--primary-dark); font-weight: 500; font-size: 15px;">
                                    <?php echo !empty(trim($row['nombre_pais_nac'])) ? $row['nombre_pais_nac'] : 'N/A'; ?>, 
                                    <?php echo !empty(trim($row['nombre_estado_nac'])) ? $row['nombre_estado_nac'] : 'N/A'; ?>, 
                                    <?php echo !empty(trim($row['nombre_municipio_nac'])) ? $row['nombre_municipio_nac'] : 'N/A'; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="btn-nav-container">
                        <button type="button" class="btn btn-default" data-toggle="modal" data-target="#modalConfirmarRegreso"><i class="fa fa-arrow-left"></i> Regresar al Menú</button>
                        <button type="button" class="btn btn-primary next-tab" data-tab-actual="#info" data-tab-siguiente="direccion">Siguiente <i class="fa fa-arrow-right"></i></button>
                    </div>
                </div>

                <div class="tab-pane" id="direccion">
                    <div class="row">
                        <div class="col-md-12">
                            <h4 class="section-title"><i class="fa fa-home"></i> Ubicación y Detalles de Residencia</h4>
                            <div class="data-item-card" style="border-left-color: #27ae60;">
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div style="margin-bottom: 15px;">
                                            <span class="label-custom label-dark">Estado</span>
                                            <span class="val-custom val-dark"><?php echo !empty(trim($row['nombre_estado_dir'])) ? $row['nombre_estado_dir'] : 'N/A'; ?></span>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div style="margin-bottom: 15px;">
                                            <span class="label-custom label-dark">Municipio</span>
                                            <span class="val-custom val-dark"><?php echo !empty(trim($row['nombre_municipio_dir'])) ? $row['nombre_municipio_dir'] : 'N/A'; ?></span>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div style="margin-bottom: 15px;">
                                            <span class="label-custom label-dark">Sector</span>
                                            <span class="val-custom val-dark"><?php echo !empty(trim($row['nombre_sector_dir'])) ? $row['nombre_sector_dir'] : 'N/A'; ?></span>
                                        </div>
                                    </div>
                                </div>
                                <hr style="border-top: 1px solid #e2e8f0; margin-top: 5px; margin-bottom: 15px;">
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div style="margin-bottom: 10px;">
                                            <span class="label-custom label-dark">Av/Calle</span>
                                            <span class="val-custom val-dark"><?php echo !empty(trim($row['avenida_calle'])) ? $row['avenida_calle'] : 'N/A'; ?></span>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div style="margin-bottom: 10px;">
                                            <span class="label-custom label-dark">Referencia</span>
                                            <span class="val-custom val-dark"><?php echo !empty(trim($row['referencia'])) ? $row['referencia'] : 'N/A'; ?></span>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div style="margin-bottom: 10px;">
                                            <span class="label-custom label-dark">Tiempo de Residencia</span>
                                            <span class="val-custom val-dark"><?php echo !empty(trim($row['tiempo_residencia'])) ? $row['tiempo_residencia'] . ' ' . $row['tiempo'] : 'N/A'; ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="btn-nav-container">
                        <button type="button" class="btn btn-default prev-tab" data-tab-anterior="info"><i class="fa fa-arrow-left"></i> Atrás</button>
                        <button type="button" class="btn btn-primary next-tab" data-tab-actual="#direccion" data-tab-siguiente="pacientes_cargo">Siguiente <i class="fa fa-arrow-right"></i></button>
                    </div>
                </div>

                <div class="tab-pane" id="pacientes_cargo">
                    <h4 class="section-title"><i class="fa fa-users"></i> Menores Registrados Bajo su Tutoría</h4>
                    <div class="row">
                        <div class="col-md-12">
                            <?php
                            // Buscar los menores que tiene asignado este representante
                            $sql_menores = "SELECT p_menor.id, p_menor.nombre, p_menor.apellido, p_menor.tipo_cedula, p_menor.cedula, dpm.parentesco 
                                            FROM detalle_paciente_menor dpm
                                            JOIN persona p_menor ON dpm.id_persona = p_menor.id
                                            WHERE dpm.id_representante = $id_representante";
                            $res_menores = $conexion->query($sql_menores);

                            if($res_menores && $res_menores->num_rows > 0) {
                                echo '<div class="table-responsive">
                                        <table class="table table-bordered table-striped" style="background: #fff;">
                                            <thead>
                                                <tr style="background-color: var(--primary-dark); color: white;">
                                                    <th>Documento</th>
                                                    <th>Nombre del Paciente Menor</th>
                                                    <th>Parentesco</th>
                                                    <th>Acción</th>
                                                </tr>
                                            </thead>
                                            <tbody>';
                                while($menor = $res_menores->fetch_assoc()) {
                                    $cedulaCompleta = !empty($menor['cedula']) ? $menor['tipo_cedula'].'-'.$menor['cedula'] : 'S/C';
                                    echo '<tr>
                                            <td style="vertical-align: middle; font-weight:600;">' . $cedulaCompleta . '</td>
                                            <td style="vertical-align: middle;">' . $menor['nombre'] . ' ' . $menor['apellido'] . '</td>
                                            <td style="vertical-align: middle;"><span class="badge bg-blue badge-estado">' . $menor['parentesco'] . '</span></td>
                                            <td style="vertical-align: middle; text-align: center;">
                                                <a href="pacientes_info.php?Id=' . $menor['id'] . '" class="btn btn-default btn-xs" style="border-radius:0px;"><i class="fa fa-eye"></i> Ver Ficha</a>
                                            </td>
                                          </tr>';
                                }
                                echo '      </tbody>
                                        </table>
                                      </div>';
                            } else {
                                echo '<div class="alert alert-warning text-center" style="border-radius: 0px; background-color: #fff9e6; border: 1px dashed #f39c12; color: #d35400;">
                                        <i class="fa fa-info-circle fa-lg"></i><br>Actualmente no tiene pacientes menores registrados a su cargo.
                                      </div>';
                            }
                            ?>
                        </div>
                    </div>
                    
                    <div class="btn-nav-container">
                        <button class="btn btn-default prev-tab" data-tab-actual="#pacientes_cargo" data-tab-anterior="direccion"><i class="fa fa-arrow-left"></i> Atrás</button>
                    </div>
                </div>

              </div>
            </div>
            
          </div> 
        </div>
      </div>
    </section>
  </div>

  <div class="modal fade" id="modalConfirmarRegreso" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarRegresoLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: #dc3545; color: white;">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true" style="color:white;">&times;</span></button>
          <h4 class="modal-title" id="modalConfirmarRegresoLabel"><i class="fa fa-warning"></i> Confirmación</h4>
        </div>
        <div class="modal-body">
          <p>¿Está a punto de cerrar la ficha del representante y regresar al listado principal. ¿Desea continuar?</p>
        </div>
        <div class="modal-footer" style="background-color: #f8f9fa;">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
          <a href="representantes_listado.php" class="btn btn-danger">Regresar al listado</a>
        </div>
      </div>
    </div>
  </div>

  <?php include('includes/footer.php'); ?>

</body>

<script>
    // Navegación de pestañas (Reutilizada de pacientes_info)
    $(document).ready(function() {
      if (!window.location.hash || window.location.hash === '#info') {
        $('a[href="#info"]').tab('show');
      }

      if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.pathname + window.location.search);
      }
    });

    $('.next-tab').on('click', async function() {
      const $btn = $(this);
      const tabSiguienteName = $btn.data('tab-siguiente');
      const nextTabLink = $(`.nav-tabs li[data-tab-name="${tabSiguienteName}"] a`);
      const $siguienteTabLi = $(`.nav-tabs li[data-tab-name="${tabSiguienteName}"]`);
      
      $('.nav-tabs li').removeClass('active');
      $('.tab-content .tab-pane').removeClass('active');
      $siguienteTabLi.removeClass('disabled-tab').addClass('active');

      nextTabLink.tab('show');
      $('#' + tabSiguienteName).addClass('active');
    });

    $('.prev-tab').on('click', function() {
      const $btn = $(this);
      const tabAnteriorName = $btn.data('tab-anterior');

      if (tabAnteriorName) {
        const prevTabLink = $(`.nav-tabs li[data-tab-name="${tabAnteriorName}"] a`);
        const $anteriorTabLi = $(`.nav-tabs li[data-tab-name="${tabAnteriorName}"]`);

        $('.nav-tabs li').removeClass('active');
        $('.tab-content .tab-pane').removeClass('active');

        $anteriorTabLi.addClass('active');
        prevTabLink.tab('show');
        $('#' + tabAnteriorName).addClass('active');

      } else {
        $('#modalConfirmarRegreso').modal('show');
      }
    });
</script>
</html>