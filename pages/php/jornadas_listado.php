<?php
include('includes/headerNav2.php');
verificarAcceso('Gestionar Jornadas'); 
?>

<head>
  <title>Jornadas | Listado</title>
</head>

<div class="content-wrapper">
  <?php
  // include('../../cfg/conexion.php');
  
  // Query Template para cuando conectes la BD
  /*
  $sqlJornadas = ("SELECT j.*, 
                    (SELECT COUNT(*) FROM vacunas_aplicadas va WHERE va.id_jornada = j.id) as dosis_aplicadas
                    FROM jornadas j 
                    WHERE j.estatus = 1");
  $queryData = mysqli_query($conexion, $sqlJornadas);
  $total_jornadas = mysqli_num_rows($queryData);
  */
  
  $total_jornadas = 0; // Variable quemada para el diseño
  ?>

  <section class="content-header">
    <h1>
      Jornadas de Salud (<?php echo $total_jornadas; ?>)
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i> Inicio</a></li>
      <li><a href="#"><i class="fa fa-calendar"></i> Jornadas</a></li>
      <li><a href="#"><i class="fa fa-book"></i> Listado</a></li>
    </ol>
  </section>

  <?php if (isset($_SESSION["permisos"])) : ?>
    <section class="content">
      <div class="toolbar-container">
        
        <!-- BOTÓN DE REPORTES (ABRE MODAL) -->
        <?php if (in_array('Generar Reportes de Jornadas', $_SESSION["permisos"]) || in_array('Gestionar Jornadas', $_SESSION["permisos"])) : ?>
          <a href="#" class="btn-sm btn-info pull-right reporte" style="margin-left:5px;"><i class="fa fa-book"></i> Reportes </a>
        <?php endif; ?>

        <!-- BOTÓN DE CREAR JORNADA -->
        <?php if (in_array('Gestionar Jornadas', $_SESSION["permisos"])) : ?>
          <a href="jornadas_agregar.php" class="btn-sm btn-success pull-right" style="margin-left:5px;"> <i class="fa fa-plus"></i> Nueva Jornada </a>
        <?php endif; ?>

        <input type="text" id="buscar" name="buscar" class="form-control" placeholder="Escriba para buscar..." style="border-radius:0; height:10%; width:250px; display:inline-block;" autocomplete="off">
      </div>
      <br><br>

      <div id="contenedorTabla" class="table-responsive">
        <table class="table table-sm table-hover mt-4" width="100%" id="t_user">
          <thead class="table-dark">
            <th>Nombre de la Jornada</th>
            <th>Tipo</th>
            <th>Fecha</th>
            <th>Lugar / ASIC</th>
            <th>Responsable</th>
            <th>Progreso (Meta)</th>
            <th>Estado</th>
            <th>Acciones</th>
          </thead>
          <tbody class="tbody" width="100%" style="font-size: 12px;">
            <?php
            // Simulación para el diseño visual (reemplazar con el while real)
            $jornadas_simuladas = [
                [
                  'id' => 1, 
                  'nombre' => 'Jornada Antipoliomielitis 2026', 
                  'tipo' => 'Inmunización', 
                  'fecha_inicio' => '2026-05-15', 
                  'fecha_fin' => '2026-05-20', 
                  'lugar' => 'ASIC Aguablanca', 
                  'responsable' => 'Enf. María Rodríguez', 
                  'meta' => 500, 
                  'logrado' => 350, 
                  'estado' => 'En Curso'
                ],
                [
                  'id' => 2, 
                  'nombre' => 'Plan Luciérnaga - Sector Sur', 
                  'tipo' => 'Casa a Casa', 
                  'fecha_inicio' => '2026-05-10', 
                  'fecha_fin' => '2026-05-12', 
                  'lugar' => 'CPT3 Barrio Sur', 
                  'responsable' => 'Dr. José Pérez', 
                  'meta' => 200, 
                  'logrado' => 200, 
                  'estado' => 'Finalizada'
                ],
                [
                  'id' => 3, 
                  'nombre' => 'Detección de Hipertensión', 
                  'tipo' => 'Detección de Patologías', 
                  'fecha_inicio' => '2026-05-25', 
                  'fecha_fin' => '2026-05-25', 
                  'lugar' => 'Plaza Bolívar', 
                  'responsable' => 'Dra. Ana Gómez', 
                  'meta' => 100, 
                  'logrado' => 45, 
                  'estado' => 'En Curso'
                ],
            ];

            if (count($jornadas_simuladas) > 0) {
              foreach ($jornadas_simuladas as $row) {
                $progreso = ($row['meta'] > 0) ? round(($row['logrado'] / $row['meta']) * 100) : 0;
                
                // Colores según el estado
                $badge_estado = 'label-default';
                if ($row['estado'] == 'En Curso') $badge_estado = 'label-primary';
                if ($row['estado'] == 'Finalizada') $badge_estado = 'label-success';
                if ($row['estado'] == 'Cancelada') $badge_estado = 'label-danger';
            ?>
                <tr>
                  <td><span class="text-row text-white"><strong><?= $row['nombre']; ?></strong></span></td>
                  <td><span class="text-row text-white"><?= $row['tipo']; ?></span></td>
                  <td>
                    <span class="text-row text-white">
                      <?= date('d/m/Y', strtotime($row['fecha_inicio'])); ?>
                      <?php if ($row['fecha_inicio'] != $row['fecha_fin']) : ?>
                        - <?= date('d/m/Y', strtotime($row['fecha_fin'])); ?>
                      <?php endif; ?>
                    </span>
                  </td>
                  <td><span class="text-row text-white"><?= $row['lugar']; ?></span></td>
                  <td><span class="text-row text-white"><?= $row['responsable']; ?></span></td>
                  
                  <!-- Columna de Progreso -->
                  <td>
                    <div class="progress progress-xs" style="margin-bottom: 2px;">
                      <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?= $progreso; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= $progreso; ?>%">
                        <span class="sr-only"><?= $progreso; ?>% Complete</span>
                      </div>
                    </div>
                    <span class="text-row text-white" style="font-size: 10px;">
                      <?= $row['logrado']; ?> / <?= $row['meta']; ?> (<?= $progreso; ?>%)
                    </span>
                  </td>

                  <!-- Columna de Estado -->
                  <td>
                    <span class="label <?= $badge_estado; ?>" style="font-size: 10px;">
                      <?= $row['estado']; ?>
                    </span>
                  </td>

                  <td>
                    <!-- ACCIONES CON ESPACIADO CORRECTO -->
                    <a href="jornadas_info.php?Id=<?php echo $row['id'] ?>" class="btn-sm btn-info" title="Ver Detalles de la Jornada" style="margin-right: 5px;">
                        <img src="../../recursos/imagenes/iconos/info.png" style="width:15px; height:15px;">
                    </a>
                    <?php if (in_array('Gestionar Jornadas', $_SESSION["permisos"])) : ?>
                      <a href="jornadas_editar.php?Id=<?php echo $row['id'] ?>" class="btn-sm btn-warning" title="Editar Jornada" style="margin-right: 5px;">
                          <img src="../../recursos/imagenes/iconos/editar.png" style="width:15px; height:15px;">
                      </a>
                    <?php endif; ?>
                  </td>
                </tr>
            <?php
              }
            } else {
              echo "<tr><td colspan='8' class='text-center' style='padding: 20px;'><i class='fa fa-folder-open' style='font-size: 30px; color: #ccc;'></i><br>No se encontraron jornadas registradas.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>

      <nav id="contenedorPaginacion" aria-label="Page navigation">
        <ul class="pagination">
          <li class="disabled"><a href="#">&laquo;</a></li>
          <li class="active"><a href="#">1</a></li>
          <li class="disabled"><a href="#">&raquo;</a></li>
        </ul>
      </nav>
    </section>
  <?php endif; ?>

  <!-- MODAL DE REPORTES -->
  <div class="modal fade" id="ModalReporteJornada" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h4 class="modal-title" id="myModalLabel"><i class="fa fa-book"></i> Reportes de Jornadas</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Seleccione el tipo de reporte:</label>
            <select class="form-control" id="tipo_reporte_jornada">
              <option value="todas">Listado General de Jornadas</option>
              <option value="inmunizacion">Jornadas de Inmunización</option>
              <option value="deteccion">Jornadas de Detección de Patologías</option>
              <option value="integral">Jornadas de Atención Integral</option>
              <option value="finalizadas">Solo Jornadas Finalizadas</option>
              <option value="curso">Solo Jornadas en Curso</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-primary" id="btnEjecutarReporteJornada">Generar PDF</button>
        </div>
      </div>
    </div>
  </div>

  <?php include('includes/footer.php'); ?>
  
  <script>
    $(document).ready(function() {
        // Buscador en vivo
        $("#buscar").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#t_user tbody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });

        // Lógica del Modal de Reportes
        $('.reporte').on('click', function(e) {
          e.preventDefault();
          $('#ModalReporteJornada').modal('show');
        });

        $('#btnEjecutarReporteJornada').on('click', function() {
          var tipo = $('#tipo_reporte_jornada').val();
          // Aquí irá la ruta de tu generador de PDF de jornadas
          window.open('../../cfg/reportes/generar_pdf_jornadas.php?tipo=' + tipo, '_blank');
          $('#ModalReporteJornada').modal('hide');
        });
    });
  </script>
</div>