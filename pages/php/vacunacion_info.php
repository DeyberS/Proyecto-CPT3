<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

include("../../cfg/conexion.php");

$id_url = isset($_GET['Id']) ? (int)$_GET['Id'] : 0;

// NOTA: En vacunacion_listado.php enviamos la cedula como Id. 
// Si prefieres enviar el ID de la persona, ajusta el listado. Aquí asumiremos que es el ID de la persona.
if ($id_url <= 0) {
  $_SESSION['mensaje_user_error'] = "Debe seleccionar un paciente válido para ver su tarjeta de vacunación.";
  header("Location: vacunacion_listado.php");
  exit;
}

include('includes/headerNav2.php');
verificarAcceso('Gestionar Vacunas');
?>

<head>
  <title>Inmunización | Tarjeta de Vacunación</title>
</head>

<div class="content-wrapper">
  <style>
    /* TUS ESTILOS SE MANTIENEN EXACTAMENTE IGUAL */
    :root {
      --primary-dark: #2c3e50;
      --medical-blue: #007bff;
      --bg-gray: #f4f7f9;
      --vacuna-green: #27ae60;
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

    .profile-card {
      max-width: 1200px;
      margin: 15px auto;
      background: #fff;
      border-radius: 0px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      border: none;
      min-height: 80vh;
      overflow: hidden;
    }

    .profile-header {
      background: linear-gradient(135deg, var(--primary-dark) 0%, #34495e 100%);
      color: white;
      padding: 10px;
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

    .label-custom {
      font-size: 11px;
      text-transform: uppercase;
      font-weight: 700;
      display: block;
      margin-bottom: 2px;
    }

    .label-light {
      color: #bdc3c7;
    }

    .label-dark {
      color: #95a5a6;
    }

    .val-custom {
      font-size: 16px;
      font-weight: 600;
    }

    .val-light {
      color: #fff;
    }

    .val-dark {
      color: var(--primary-dark);
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

    .data-item-card {
      background: #f8f9fa;
      padding: 15px;
      border-radius: 0px;
      border-left: 4px solid var(--medical-blue);
      margin-bottom: 15px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
    }

    .nav-tabs-custom {
      background: transparent;
      box-shadow: none;
    }

    .nav-tabs {
      border-bottom: 2px solid #eee;
      background: #f8f9fa;
      padding: 0 20px;
    }

    .nav-tabs>li>a {
      border-radius: 0 !important;
      color: #7f8c8d !important;
      font-weight: 600;
      text-transform: uppercase;
      font-size: 13px;
      padding: 15px 20px;
      border: none !important;
      transition: all 0.3s ease;
    }

    .nav-tabs>li>a:hover {
      background-color: #e9ecef;
      color: var(--primary-dark) !important;
    }

    .nav-tabs>li.active>a,
    .nav-tabs>li.active>a:focus,
    .nav-tabs>li.active>a:hover {
      color: var(--medical-blue) !important;
      background-color: #fff !important;
      border-bottom: 3px solid var(--medical-blue) !important;
    }

    .tab-content-container {
      padding: 30px;
    }

    .badge-vacuna {
      background-color: var(--vacuna-green);
      color: white;
      padding: 6px 12px;
      border-radius: 0px;
      font-size: 12px;
      display: inline-block;
      margin-right: 5px;
      margin-bottom: 5px;
      font-weight: 600;
    }

    .badge-pendiente {
      background-color: #e67e22;
      color: white;
      padding: 6px 12px;
      border-radius: 0px;
      font-size: 12px;
      display: inline-block;
      margin-right: 5px;
      margin-bottom: 5px;
      font-weight: 600;
    }

    .btn-nav-container {
      display: flex;
      justify-content: flex-end;
      gap: 15px;
      margin-top: 00px;
      padding-top: 20px;
      border-top: 1px solid #eee;
    }

    /* Estilos para la Tabla de Vacunas (Tarjeta) */
    .table-vacunas {
      background: white;
      border: 1px solid #ddd;
    }

    .table-vacunas th {
      background-color: #34495e;
      color: white;
      text-align: center;
      font-size: 12px;
      border: 1px solid #2c3e50;
    }

    .table-vacunas td {
      border: 1px solid #eee;
      font-size: 13px;
      text-align: center;
      vertical-align: middle;
    }

    .table-vacunas td.vacuna-nombre {
      text-align: left;
      font-weight: 600;
      color: var(--primary-dark);
    }

    .celda-aplicada {
      background-color: #e8f8f5;
      color: #1e8449;
      font-weight: bold;
    }

    .celda-pendiente {
      background-color: #fcf3cf;
      color: #b7950b;
    }

    .celda-vacia {
      background-color: #f8f9fa;
      color: #bdc3c7;
    }

    /* Timeline para el historial */
    .timeline {
      position: relative;
      padding-left: 20px;
      border-left: 2px solid #eee;
    }

    .timeline-item {
      margin-bottom: 20px;
      position: relative;
    }

    .timeline-item::before {
      content: '';
      position: absolute;
      left: -27px;
      top: 0px;
      width: 12px;
      height: 12px;
      background-color: var(--vacuna-green);
      border-radius: 50%;
      border: 2px solid #fff;
    }
  </style>

  <section class="content content-custome">
    <div class="row">
      <div class="col-xs-12">

        <div class="profile-card">
          <?php
          // Query para traer los datos del paciente
          $sql = "SELECT p.id, p.nombre, p.apellido, p.tipo_cedula, p.cedula, p.fecha_nacimiento, p.genero,
                tp.telefono, pt.prefijo,
                (SELECT GROUP_CONCAT(CONCAT(ac.nombre_alergia, ' (', DATE_FORMAT(ha.fecha_registro, '%d/%m/%Y'), ')') SEPARATOR '||')
                 FROM historial_alergias ha
                 JOIN alergias_conocidas ac ON ha.Id_alergia = ac.Id_alergias_conocidas
                 WHERE ha.Id_persona = p.id) AS lista_alergias,
                hm.grupo_sanguineo
                FROM persona p
                LEFT JOIN telefonos_personas tp ON p.id = tp.Id_persona
                LEFT JOIN prefijos_telefonos pt ON tp.Id_prefijo = pt.Id
                LEFT JOIN historial_medico hm ON p.id = hm.Id_persona 
                WHERE p.id =" . $id_url;

          $resultado = $conexion->query($sql);
          $row = $resultado->fetch_assoc();
          $id_paciente = $row['id'];
          ?>

          <input type="hidden" name="Id" value="<?= $row['id']; ?>">

          <div class="profile-header">
            <div class="row w-100">
              <div class="col-sm-2 text-center">
                <img src="../../recursos/imagenes/iconos/Paciente_icon1.png" class="paciente-avatar" style="padding-top:30px; height:100px; width:100;" alt="Foto Paciente">
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
                    <?php
                    $fechaNac = new DateTime($row['fecha_nacimiento']);
                    $hoy = new DateTime();
                    $edad = $hoy->diff($fechaNac);
                    echo '<span class="val-custom val-light">' . $edad->y . ' años ' . $edad->m . ' meses</span>';
                    ?>
                  </div>
                  <div class="col-xs-3">
                    <span class="label-custom label-light">Sexo</span>
                    <span class="val-custom val-light"><?php echo !empty(trim($row['genero'])) ? $row['genero'] : 'No registrado'; ?></span>
                  </div>
                  <div class="col-xs-3">
                    <span class="label-custom label-light">Grupo Sanguíneo</span>
                    <span class="val-custom val-light"><?php echo !empty(trim($row['grupo_sanguineo'])) ? $row['grupo_sanguineo'] : 'N/A'; ?></span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active" data-tab-name="tarjeta"><a href="#tarjeta" data-toggle="tab"><i class="fa fa-id-card-o"></i> Tarjeta de Vacunación</a></li>
              <li data-tab-name="historial"><a href="#historial" data-toggle="tab"><i class="fa fa-history"></i> Historial de Dosis</a></li>
              <li data-tab-name="alertas"><a href="#alertas" data-toggle="tab"><i class="fa fa-exclamation-triangle"></i> Alertas y Alergias</a></li>
            </ul>

            <div class="tab-content tab-content-container">

              <!-- PESTAÑA 1: TARJETA VISUAL (COMO LA FOTO 15) -->
              <div class="tab-pane active" id="tarjeta">
                <div class="row">
                  <div class="col-md-12">

                    <div class="alert alert-info" style="border-radius: 0px; background-color: #e3f2fd; border-left: 5px solid #007bff; color: #0c5460;">
                      <i class="fa fa-info-circle"></i> <strong>Nota:</strong> Este es el esquema de vacunación sugerido según la edad del paciente. Las celdas verdes indican dosis aplicadas, las amarillas están pendientes.
                    </div>

                    <table class="table table-vacunas table-bordered">
                      <thead>
                        <tr>
                          <th style="width: 200px;">Vacuna / Biológico</th>
                          <th>1ra Dosis</th>
                          <th>2da Dosis</th>
                          <th>3ra Dosis</th>
                          <th>Refuerzo 1</th>
                          <th>Refuerzo 2</th>
                        </tr>
                      </thead>
                      <tbody>
                        <!-- EJEMPLO DE ESQUEMA (Esto luego se genera dinámicamente con PHP) -->
                        <tr>
                          <td class="vacuna-nombre">BCG (Antituberculosa)</td>
                          <td class="celda-aplicada">Aplicada<br><small>12/05/2020</small></td>
                          <td class="celda-vacia">-</td>
                          <td class="celda-vacia">-</td>
                          <td class="celda-vacia">-</td>
                          <td class="celda-vacia">-</td>
                        </tr>
                        <tr>
                          <td class="vacuna-nombre">Hepatitis B</td>
                          <td class="celda-aplicada">Aplicada<br><small>10/05/2020</small></td>
                          <td class="celda-aplicada">Aplicada<br><small>10/06/2020</small></td>
                          <td class="celda-vacia">-</td>
                          <td class="celda-vacia">-</td>
                          <td class="celda-vacia">-</td>
                        </tr>
                        <tr>
                          <td class="vacuna-nombre">Polio (IPV/OPV)</td>
                          <td class="celda-aplicada">Aplicada<br><small>10/06/2020</small></td>
                          <td class="celda-pendiente">Pendiente</td>
                          <td class="celda-vacia">-</td>
                          <td class="celda-vacia">-</td>
                          <td class="celda-vacia">-</td>
                        </tr>
                        <tr>
                          <td class="vacuna-nombre">Pentavalente</td>
                          <td class="celda-aplicada">Aplicada<br><small>10/06/2020</small></td>
                          <td class="celda-aplicada">Aplicada<br><small>10/07/2020</small></td>
                          <td class="celda-pendiente">Pendiente</td>
                          <td class="celda-vacia">-</td>
                          <td class="celda-vacia">-</td>
                        </tr>
                        <tr>
                          <td class="vacuna-nombre">SRP (Sarampión, Rubéola, Parotiditis)</td>
                          <td class="celda-pendiente">Pendiente</td>
                          <td class="celda-vacia">-</td>
                          <td class="celda-vacia">-</td>
                          <td class="celda-vacia">-</td>
                          <td class="celda-vacia">-</td>
                        </tr>
                      </tbody>
                    </table>

                  </div>
                </div>
                <div class="btn-nav-container">
                  <button type="button" class="btn btn-second" data-toggle="modal" data-target="#modalConfirmarRegreso"><i class="fa fa-arrow-left"></i> Regresar al Listado</button>
                  <button type="button" class="btn btn-primary next-tab" data-tab-actual="#tarjeta" data-tab-siguiente="historial">Ver Historial <i class="fa fa-arrow-right"></i></button>
                </div>
              </div>

              <!-- PESTAÑA 2: HISTORIAL CRONOLÓGICO -->
              <div class="tab-pane" id="historial">
                <h4 class="section-title"><i class="fa fa-list-alt"></i> Registro Cronológico de Dosis Aplicadas</h4>

                <div class="timeline">
                  <!-- EJEMPLO DE REGISTRO (Luego se hace con foreach en PHP) -->
                  <div class="timeline-item">
                    <div class="panel panel-default" style="border-radius: 0px; border-left: 4px solid var(--vacuna-green); box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                      <div class="panel-heading" style="background-color: #f8f9fa; border-bottom: 1px solid #eee;">
                        <strong style="color: var(--vacuna-green);"><i class="fa fa-syringe"></i> Pentavalente - 2da Dosis</strong>
                        <span class="pull-right text-muted"><i class="fa fa-calendar"></i> 10/07/2020</span>
                      </div>
                      <div class="panel-body">
                        <div class="row">
                          <div class="col-sm-4"><span class="label-custom label-dark">Lote</span><span class="val-dark">PT-2020-45A</span></div>
                          <div class="col-sm-4"><span class="label-custom label-dark">Vía de Adm.</span><span class="val-dark">Intramuscular</span></div>
                          <div class="col-sm-4"><span class="label-custom label-dark">Aplicado por</span><span class="val-dark">Enf. María Rodríguez</span></div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="timeline-item">
                    <div class="panel panel-default" style="border-radius: 0px; border-left: 4px solid var(--vacuna-green); box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                      <div class="panel-heading" style="background-color: #f8f9fa; border-bottom: 1px solid #eee;">
                        <strong style="color: var(--vacuna-green);"><i class="fa fa-syringe"></i> Hepatitis B - 2da Dosis</strong>
                        <span class="pull-right text-muted"><i class="fa fa-calendar"></i> 10/06/2020</span>
                      </div>
                      <div class="panel-body">
                        <div class="row">
                          <div class="col-sm-4"><span class="label-custom label-dark">Lote</span><span class="val-dark">HB-2020-12B</span></div>
                          <div class="col-sm-4"><span class="label-custom label-dark">Vía de Adm.</span><span class="val-dark">Intramuscular</span></div>
                          <div class="col-sm-4"><span class="label-custom label-dark">Aplicado por</span><span class="val-dark">Enf. María Rodríguez</span></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="btn-nav-container">
                  <button type="button" class="btn btn-second prev-tab" data-tab-actual="#historial" data-tab-anterior="tarjeta"><i class="fa fa-arrow-left"></i> Atrás</button>
                  <button type="button" class="btn btn-primary next-tab" data-tab-actual="#historial" data-tab-siguiente="alertas">Siguiente <i class="fa fa-arrow-right"></i></button>
                </div>
              </div>

              <!-- PESTAÑA 3: ALERTAS -->
              <div class="tab-pane" id="alertas">
                <div class="row">
                  <div class="col-md-6">
                    <h4 class="section-title"><i class="fa fa-exclamation-triangle" style="color: #e67e22;"></i> Alergias del Paciente</h4>
                    <div class="data-item-card" style="border-left-color: #e67e22; background-color: #fdf2e9;">
                      <div style="margin-top: 8px;">
                        <?php
                        // Mostrar alergias desde la BD (usando el campo lista_alergias)
                        if (!empty($row['lista_alergias'])) {
                          $alergias_array = explode('||', $row['lista_alergias']);
                          foreach ($alergias_array as $alergia) {
                            echo '<span class="badge-pendiente" style="background-color: #e67e22; margin-bottom: 8px;">' . trim($alergia) . '</span><br>';
                          }
                        } else {
                          echo '<span style="color: #999;">Ninguna alergia registrada en el sistema.</span>';
                        }
                        ?>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <h4 class="section-title"><i class="fa fa-info-circle" style="color: var(--medical-blue);"></i> Observaciones de Inmunización</h4>
                    <div class="data-item-card">
                      <p style="margin: 0; color: var(--primary-dark);">
                        <i class="fa fa-check-circle text-success"></i> <strong>Contraindicaciones:</strong> Ninguna registrada.
                      </p>
                      <hr style="margin: 10px 0;">
                      <p style="margin: 0; color: var(--primary-dark);">
                        <i class="fa fa-check-circle text-success"></i> <strong>Reacciones Adversas (ESAVI):</strong> Ninguna reportada.
                      </p>
                    </div>
                  </div>
                </div>

                <div class="btn-nav-container">
                  <button class="btn btn-second prev-tab" data-tab-actual="#alertas" data-tab-anterior="historial"><i class="fa fa-arrow-left"></i> Atrás</button>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- MODAL DE REGRESO -->
  <div class="modal fade" id="modalConfirmarRegreso" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarRegresoLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h4 class="modal-title" id="modalConfirmarRegresoLabel"><i class="fa fa-warning"></i> Confirmación de Regreso</h4>
        </div>
        <div class="modal-body">
          <p>¿Está a punto de cerrar la tarjeta de vacunación del paciente y regresar al listado principal. ¿Desea continuar?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <a href="vacunacion_listado.php" class="btn btn-danger">Regresar al listado</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include('includes/footer.php'); ?>

<script>
  $(document).ready(function() {
    // Lógica para navegar entre pestañas con botones "Siguiente" y "Atrás"
    $('.next-tab').on('click', function() {
      const tabSiguienteName = $(this).data('tab-siguiente');
      $(`.nav-tabs li[data-tab-name="${tabSiguienteName}"] a`).tab('show');
    });

    $('.prev-tab').on('click', function() {
      const tabAnteriorName = $(this).data('tab-anterior');
      if (tabAnteriorName) {
        $(`.nav-tabs li[data-tab-name="${tabAnteriorName}"] a`).tab('show');
      }
    });
  });
</script>