<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// include("../../cfg/conexion.php"); // COMENTADO: Aún no tenemos BD

$id_url = isset($_GET['Id']) ? (int)$_GET['Id'] : 1;

include('includes/headerNav2.php');
verificarAcceso('Gestionar Jornadas');
?>

<head>
  <title>Jornadas | Información (Template)</title>
</head>

<style>
  :root {
    --primary-dark: #2c3e50;
    --medical-blue: #007bff;
    --bg-gray: #f4f7f9;
    --jornada-green: #27ae60;
    --jornada-orange: #e67e22;
  }

  body { background-color: var(--bg-gray) !important; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
  .content-wrapper { background-color: var(--bg-gray) !important; }
  .content-custome { padding: 0px, 0px; margin-left: 0px; }
  .content { padding: 0px; }
  
  /* Tarjeta principal: más alta, sin scroll interno, scroll en la página */
  .profile-card { 
    max-width: 1200px; 
    margin: 15px auto; 
    background: #fff; 
    border-radius: 0px; 
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); 
    border: none; 
    min-height: 90vh; /* Aumentamos el tamaño */
    overflow: visible; /* Quitamos el scroll de la tarjeta */
  }

  .profile-header { 
    background: linear-gradient(135deg, var(--primary-dark) 0%, #34495e 100%); 
    color: white; 
    padding: 10px; 
    border-bottom: 5px solid var(--medical-blue); 
  }
  
  .jornada-icon { width: 110px; height: 110px; background: transparent; border-radius: 0px; padding: 0; box-shadow: none; object-fit: contain; }
  
  .label-custom { font-size: 11px; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 2px; }
  .label-light { color: #bdc3c7; }
  .label-dark { color: #95a5a6; }
  .val-custom { font-size: 16px; font-weight: 600; }
  .val-light { color: #fff; }
  .val-dark { color: var(--primary-dark); }
  
  .section-title { color: var(--medical-blue); font-weight: 700; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 20px; text-transform: uppercase; font-size: 14px; }
  .data-item-card { background: #f8f9fa; padding: 15px; border-radius: 0px; border-left: 4px solid var(--medical-blue); margin-bottom: 15px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02); }
  
  .nav-tabs-custom { background: transparent; box-shadow: none; }
  .nav-tabs { border-bottom: 2px solid #eee; background: #f8f9fa; padding: 0 20px; }
  .nav-tabs>li>a { border-radius: 0 !important; color: #7f8c8d !important; font-weight: 600; text-transform: uppercase; font-size: 13px; padding: 15px 20px; border: none !important; transition: all 0.3s ease; }
  .nav-tabs>li>a:hover { background-color: #e9ecef; color: var(--primary-dark) !important; }
  .nav-tabs>li.active>a, .nav-tabs>li.active>a:focus, .nav-tabs>li.active>a:hover { color: var(--medical-blue) !important; background-color: #fff !important; border-bottom: 3px solid var(--medical-blue) !important; }
  
  /* Contenido de pestañas: sin scroll interno */
  .tab-content-container { 
    padding: 30px; 
    overflow: visible; 
  }
  
  .badge-tipo { background-color: var(--jornada-orange); color: white; padding: 6px 12px; border-radius: 0px; font-size: 12px; display: inline-block; margin-right: 5px; margin-bottom: 5px; font-weight: 600; }
  .badge-estado { padding: 4px 10px; border-radius: 0px; font-weight: 600; font-size: 11px; text-transform: uppercase; }
  
  .btn-nav-container { display: flex; justify-content: flex-end; gap: 15px; margin-top: 00px; padding-top: 20px; border-top: 1px solid #eee; }

  /* Scroll específico para la tabla de pacientes */
  .pacientes-table-wrapper {
    max-height: 400px; /* Ajusta este valor si quieres más o menos espacio */
    overflow-y: auto;
    border: 1px solid #ddd;
  }

  /* Scrollbar personalizada (opcional) */
  .pacientes-table-wrapper::-webkit-scrollbar { width: 8px; }
  .pacientes-table-wrapper::-webkit-scrollbar-track { background: #f1f1f1; }
  .pacientes-table-wrapper::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 4px; }
  .pacientes-table-wrapper::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
</style>

<body class="hold-transition skin-blue sidebar-mini">
  <div class="content-wrapper">
    <section class="content content-custome">
      <div class="row">
        <div class="col-xs-12">

          <div class="profile-card">
            <?php
            // ==========================================
            // DATOS SIMULADOS (TEMPORAL - SIN BD)
            // ==========================================
            $row = [
              'id' => 1,
              'nombre_jornada' => 'Jornada Antipoliomielitis 2026',
              'tipo_jornada' => 'Inmunización',
              'fecha_inicio' => '2026-05-15',
              'fecha_fin' => '2026-05-20',
              'lugar' => 'ASIC Aguablanca',
              'responsable' => 'Enf. María Rodríguez',
              'meta' => 500,
              'dosis_aplicadas' => 350,
              'estado' => 'En Curso',
              'observaciones' => 'Jornada realizada en la plaza principal con apoyo de la comunidad. Se atendieron niños de 0 a 5 años.'
            ];
            
            $progreso = ($row['meta'] > 0) ? round(($row['dosis_aplicadas'] / $row['meta']) * 100) : 0;
            ?>

            <input type="hidden" name="Id" value="<?= $row['id']; ?>">

            <div class="profile-header">
              <div class="row w-100">
                <div class="col-sm-2 text-center">
                  <img src="../../recursos/imagenes/iconos/filled/places/rural_post@2x.png" class="jornada-icon" style="padding-top:30px; height:100px; width:100; filter:invert(1);" alt="Icono Jornada">
                </div>
                <div class="col-sm-10">
                  <h2 style="margin: 5px 0; font-weight: 800;"><?php echo htmlspecialchars($row['nombre_jornada']); ?></h2>
                  <p style="font-size: 18px; opacity: 0.9;">
                    <i class="fa fa-calendar"></i> 
                    <?php echo date("d/m/Y", strtotime($row['fecha_inicio'])); ?>
                    <?php if ($row['fecha_inicio'] != $row['fecha_fin']) echo ' - ' . date("d/m/Y", strtotime($row['fecha_fin'])); ?>
                  </p>
                  
                  <div class="row" style="margin-top: 20px;">
                    <div class="col-xs-3">
                      <span class="label-custom label-light">Tipo</span>
                      <span class="val-custom val-light"><?php echo htmlspecialchars($row['tipo_jornada']); ?></span>
                    </div>
                    <div class="col-xs-3">
                      <span class="label-custom label-light">Lugar</span>
                      <span class="val-custom val-light"><?php echo htmlspecialchars($row['lugar']); ?></span>
                    </div>
                    <div class="col-xs-3">
                      <span class="label-custom label-light">Responsable</span>
                      <span class="val-custom val-light"><?php echo htmlspecialchars($row['responsable']); ?></span>
                    </div>
                    <div class="col-xs-3">
                      <span class="label-custom label-light">Estado</span>
                      <span class="badge-estado bg-<?php echo ($row['estado'] == 'Finalizada' ? 'green' : ($row['estado'] == 'Cancelada' ? 'red' : 'blue')); ?>" style="font-size: 12px;">
                        <?php echo htmlspecialchars($row['estado']); ?>
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="nav-tabs-custom">
              <ul class="nav nav-tabs">
                <li class="active" data-tab-name="resumen"><a href="#resumen" data-toggle="tab"><i class="fa fa-bar-chart"></i> Resumen y Estadísticas</a></li>
                <li data-tab-name="pacientes"><a href="#pacientes" data-toggle="tab"><i class="fa fa-users"></i> Pacientes Atendidos</a></li>
                <li data-tab-name="observaciones"><a href="#observaciones" data-toggle="tab"><i class="fa fa-file-text-o"></i> Observaciones</a></li>
              </ul>

              <div class="tab-content tab-content-container">

                <!-- PESTAÑA 1: RESUMEN Y ESTADÍSTICAS -->
                <div class="tab-pane active" id="resumen">
                  <div class="row">
                    <div class="col-md-12">
                      
                      <!-- Tarjetas de Progreso -->
                      <div class="row">
                        <div class="col-md-4">
                          <div class="data-item-card" style="border-left-color: var(--jornada-green); background-color: #e8f8f5;">
                            <span class="label-custom label-dark">Dosis Aplicadas</span>
                            <span class="val-custom val-dark" style="font-size: 28px; color: var(--jornada-green);"><?php echo $row['dosis_aplicadas']; ?></span>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="data-item-card" style="border-left-color: var(--medical-blue); background-color: #e3f2fd;">
                            <span class="label-custom label-dark">Meta</span>
                            <span class="val-custom val-dark" style="font-size: 28px; color: var(--medical-blue);"><?php echo $row['meta']; ?></span>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="data-item-card" style="border-left-color: var(--jornada-orange); background-color: #fdf2e9;">
                            <span class="label-custom label-dark">Progreso</span>
                            <span class="val-custom val-dark" style="font-size: 28px; color: var(--jornada-orange);"><?php echo $progreso; ?>%</span>
                          </div>
                        </div>
                      </div>

                      <!-- Barra de Progreso Grande -->
                      <div class="data-item-card" style="margin-top: 20px;">
                        <span class="label-custom label-dark">Avance de la Jornada</span>
                        <div class="progress" style="height: 30px; margin-top: 10px; border-radius: 0px;">
                          <div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="<?php echo $progreso; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $progreso; ?>%; line-height: 30px; font-size: 14px;">
                            <?php echo $progreso; ?>% Completado
                          </div>
                        </div>
                      </div>

                      <!-- Resumen de Acciones (Simuladas) -->
                      <div class="row" style="margin-top: 20px;">
                        <div class="col-md-6">
                          <h4 class="section-title"><i class="fa fa-syringe"></i> Dosis por Vacuna</h4>
                          <div class="data-item-card">
                            <table class="table table-condensed" style="margin-bottom: 0;">
                              <thead>
                                <tr><th>Vacuna</th><th>Cantidad</th></tr>
                              </thead>
                              <tbody>
                                <tr><td>BCG (Antituberculosa)</td><td><span class="badge bg-green">120</span></td></tr>
                                <tr><td>Hepatitis B</td><td><span class="badge bg-green">100</span></td></tr>
                                <tr><td>Polio (IPV/OPV)</td><td><span class="badge bg-green">130</span></td></tr>
                              </tbody>
                            </table>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <h4 class="section-title"><i class="fa fa-heartbeat"></i> Patologías Detectadas</h4>
                          <div class="data-item-card">
                            <table class="table table-condensed" style="margin-bottom: 0;">
                              <thead>
                                <tr><th>Patología</th><th>Casos</th></tr>
                              </thead>
                              <tbody>
                                <tr><td>Hipertensión Arterial</td><td><span class="badge bg-red">15</span></td></tr>
                                <tr><td>Diabetes Mellitus Tipo 2</td><td><span class="badge bg-red">8</span></td></tr>
                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  <div class="btn-nav-container">
                    <button type="button" class="btn btn-second" data-toggle="modal" data-target="#modalConfirmarRegreso"><i class="fa fa-arrow-left"></i> Regresar al Listado</button>
                    <button type="button" class="btn btn-primary next-tab" data-tab-actual="#resumen" data-tab-siguiente="pacientes">Ver Pacientes <i class="fa fa-arrow-right"></i></button>
                  </div>
                </div>

                <!-- PESTAÑA 2: PACIENTES ATENDIDOS -->
                <div class="tab-pane" id="pacientes">
                  <h4 class="section-title"><i class="fa fa-users"></i> Pacientes Atendidos en esta Jornada</h4>
                  
                  <div class="table-responsive">
                    <table class="table table-bordered table-hover" style="font-size: 13px;">
                      <thead style="background-color: #34495e; color: white;">
                        <tr>
                          <th>Cédula</th>
                          <th>Paciente</th>
                          <th>Acción / Detalle</th>
                          <th>Fecha</th>
                          <th>Ver</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td>V-12345678</td>
                          <td>Ana Pérez</td>
                          <td><span class="label label-success">Vacuna: Polio (1ra Dosis)</span></td>
                          <td>15/05/2026</td>
                          <td><a href="vacunacion_info.php?Id=1" class="btn-xs btn-info"><i class="fa fa-eye"></i></a></td>
                        </tr>
                        <tr>
                          <td>V-87654321</td>
                          <td>Carlos Gómez</td>
                          <td><span class="label label-danger">Patología: Hipertensión</span></td>
                          <td>15/05/2026</td>
                          <td><a href="pacientes_info.php?Id=2" class="btn-xs btn-info"><i class="fa fa-eye"></i></a></td>
                        </tr>
                        <tr>
                          <td colspan="5" class="text-center text-muted"><i>Datos de ejemplo (se conectarán a la BD)</i></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>

                  <div class="btn-nav-container">
                    <button type="button" class="btn btn-second prev-tab" data-tab-actual="#pacientes" data-tab-anterior="resumen"><i class="fa fa-arrow-left"></i> Atrás</button>
                    <button type="button" class="btn btn-primary next-tab" data-tab-actual="#pacientes" data-tab-siguiente="observaciones">Siguiente <i class="fa fa-arrow-right"></i></button>
                  </div>
                </div>

                <!-- PESTAÑA 3: OBSERVACIONES -->
                <div class="tab-pane" id="observaciones">
                  <h4 class="section-title"><i class="fa fa-file-text-o"></i> Observaciones de la Jornada</h4>
                  
                  <div class="data-item-card">
                    <p style="margin: 0; color: var(--primary-dark);">
                      <?php echo !empty(trim($row['observaciones'])) ? nl2br(htmlspecialchars($row['observaciones'])) : 'No hay observaciones registradas para esta jornada.'; ?>
                    </p>
                  </div>

                  <div class="btn-nav-container">
                    <button class="btn btn-second prev-tab" data-tab-actual="#observaciones" data-tab-anterior="pacientes"><i class="fa fa-arrow-left"></i> Atrás</button>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <!-- MODAL DE REGRESO -->
  <div class="modal fade" id="modalConfirmarRegreso" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarRegresoLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h4 class="modal-title" id="modalConfirmarRegresoLabel"><i class="fa fa-warning"></i> Confirmación de Regreso</h4>
        </div>
        <div class="modal-body">
          <p>¿Está a punto de cerrar la ficha de la jornada y regresar al listado principal. ¿Desea continuar?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <a href="jornadas_listado.php" class="btn btn-danger">Regresar al listado</a>
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
</div>