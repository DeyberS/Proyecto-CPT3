<?php
include('includes/headerNav2.php');
verificarAcceso('Gestionar Vacunas'); 
?>

<head>
  <title>Inmunización | Tarjetas de Vacunación</title>
</head>

<div class="content-wrapper">
  <?php
  // include('../../cfg/conexion.php');
  
  // Query Template para cuando conectes la BD
  /*
  $sqlPacientes = ("SELECT p.id, p.tipo_cedula, p.cedula, p.nombre, p.apellido, p.fecha_nacimiento, 
                    (SELECT COUNT(*) FROM vacunas_aplicadas va WHERE va.id_paciente = p.id) as total_dosis
                    FROM persona p 
                    WHERE p.estatus = 1");
  $queryData = mysqli_query($conexion, $sqlPacientes);
  $total_pacientes = mysqli_num_rows($queryData);
  */
  
  $total_pacientes = 0; // Variable quemada para el diseño
  ?>

  <section class="content-header">
    <h1>
      Tarjetas de Vacunación (<?php echo $total_pacientes; ?>)
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i> Inicio</a></li>
      <li><a href="#"> Inmunización</a></li>
      <li><a href="#"><i class="fa fa-book"></i> Tarjetas de Vacunación</a></li>
    </ol>
  </section>

  <?php if (isset($_SESSION["permisos"])) : ?>
    <section class="content">
      <div class="toolbar-container">
        
        <!-- BOTÓN DE REPORTES (ABRE MODAL) -->
        <?php if (in_array('Generar Reportes de Pacientes', $_SESSION["permisos"])) : ?>
          <a href="#" class="btn-sm btn-info pull-right reporte" style="margin-left:5px;"><i class="fa fa-book"></i> Reportes </a>
        <?php endif; ?>

        <!-- BOTÓN DE REGISTRAR DOSIS -->
        <a href="vacunacion_registro.php" class="btn-sm btn-success pull-right" style="margin-left:5px;"> <i class="fa fa-plus"></i> Registrar Dosis </a>

        <input type="text" id="buscar" name="buscar" class="form-control" placeholder="Escriba para buscar..." style="border-radius:0; height:10%; width:250px; display:inline-block;" autocomplete="off">
      </div>
      <br><br>

      <div id="contenedorTabla" class="table-responsive">
        <table class="table table-sm table-hover mt-4" width="100%" id="t_user">
          <thead class="table-dark">
            <th>Cédula</th>
            <th>Nombres Y Apellidos</th>
            <th>Fecha Nac.</th>
            <th>Edad</th>
            <th>Dosis Aplicadas</th>
            <th>Esquema</th>
            <th>Acciones</th>
          </thead>
          <tbody class="tbody" width="100%" style="font-size: 12px;">
            <?php
            // Simulación para el diseño visual (reemplazar con el while real)
            $pacientes_simulados = [
                ['id'=>1, 'tipo_cedula'=>'V', 'cedula'=>'12345678', 'nombre'=>'Ana', 'apellido'=>'Pérez', 'fecha_nacimiento'=>'2020-05-10', 'dosis'=>5],
                ['id'=>2, 'tipo_cedula'=>'V', 'cedula'=>'87654321', 'nombre'=>'Carlos', 'apellido'=>'Gómez', 'fecha_nacimiento'=>'2018-11-20', 'dosis'=>2],
            ];

            if (count($pacientes_simulados) > 0) {
              foreach ($pacientes_simulados as $row) {
                $fechaNacimiento = new DateTime($row['fecha_nacimiento']);
                $hoy = new DateTime();
                $edad = $hoy->diff($fechaNacimiento)->y;
                $meses = $hoy->diff($fechaNacimiento)->m;
            ?>
                <tr>
                  <td><span class="text-row text-white"><?= $row['tipo_cedula'] . "-" . $row['cedula']; ?></span></td>
                  <td><span class="text-row text-white"><?= $row['nombre'] . " " . $row['apellido']; ?></span></td>
                  <td><span class="text-row text-white"><?= $row['fecha_nacimiento']; ?></span></td>
                  <td><span class="text-row text-white"><?= $edad . " años" . ($edad < 1 ? " y " . $meses . " meses" : ""); ?></span></td>
                  
                  <td>
                    <span class="label label-primary" style="font-size: 11px;">
                      <?= $row['dosis']; ?> dosis
                    </span>
                  </td>
                  
                  <td>
                    <span class="text-success"><i class="fa fa-check"></i> Al día</span>
                  </td>

                  <td>
                    <!-- ACCIONES CON ESPACIADO CORRECTO -->
                    <a href="vacunacion_info.php?Id=<?php echo $row['id'] ?>" class="btn-sm btn-info" title="Ver Tarjeta de Vacunación" style="margin-right: 5px;">
                        <img src="../../recursos/imagenes/iconos/info.png" style="width:15px; height:15px;">
                    </a>
                    <a href="vacunacion_registro.php?Id=<?php echo $row['id'] ?>" class="btn-sm btn-success" title="Aplicar Vacuna">
                        <img src="../../recursos/imagenes/iconos/filled/devices/syringe@2x.png" style="width:15px; height:15px; filter:invert(1);">
                    </a>
                  </td>
                </tr>
            <?php
              }
            } else {
              echo "<tr><td colspan='7' class='text-center' style='padding: 20px;'><i class='fa fa-folder-open' style='font-size: 30px; color: #ccc;'></i><br>No se encontraron pacientes registrados para vacunar.</td></tr>";
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

  <!-- MODAL DE REPORTES (MISMO ESTILO QUE PACIENTES) -->
  <div class="modal fade" id="ModalReporteVacuna" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h4 class="modal-title" id="myModalLabel"><i class="fa fa-book"></i> Reportes de Inmunización</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Seleccione el tipo de reporte:</label>
            <select class="form-control" id="tipo_reporte_vacuna">
              <option value="cobertura">Cobertura de Vacunación (General)</option>
              <option value="esquema">Esquemas Incompletos (Pendientes)</option>
              <option value="dosis">Dosis Aplicadas por Período</option>
              <option value="lotes">Consumo por Lotes (Trazabilidad)</option>
              <option value="menores">Tarjetas de Menores de Edad</option>
              <option value="adultos">Tarjetas de Adultos</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-primary" id="btnEjecutarReporteVacuna">Generar PDF</button>
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
          $('#ModalReporteVacuna').modal('show');
        });

        $('#btnEjecutarReporteVacuna').on('click', function() {
          var tipo = $('#tipo_reporte_vacuna').val();
          // Aquí irá la ruta de tu generador de PDF de vacunas
          window.open('../../cfg/reportes/generar_pdf_vacunas.php?tipo=' + tipo, '_blank');
          $('#ModalReporteVacuna').modal('hide');
        });
    });
  </script>
</div>