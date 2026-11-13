<?php
include('includes/headerNav2.php');
verificarAcceso('Gestionar Pacientes');
?>

<head>
  <title>Pacientes Menores de Edad | Listado</title>
</head>

<div class="content-wrapper">
  <?php
  include('../../cfg/conexion.php');
  $sqlPacientes = ("SELECT p.estatus, COUNT(p.id) AS total_pacientes FROM persona p
      JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona 
      JOIN rol r ON dpr.Id_rol = r.Id_rol
      WHERE r.Id_rol = 3 AND TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) < 18 AND p.estatus = '1'");
  $queryData   = mysqli_query($conexion, $sqlPacientes);
  $total_pacientes = mysqli_fetch_assoc($queryData)['total_pacientes'];
  ?>
  <section class="content-header">
    <h1>
      Pacientes Menores de Edad (<?php echo $total_pacientes; ?>)
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="#"><i class="fa fa-user"></i>Pacientes Menores de Edad</a></li>
      <li><a href="#"><i class="fa fa-book"></i>Listado</a></li>
    </ol>
  </section>

  <section class="content">
    <div style="padding-bottom: 10px;">
      <?php if (in_array('Ver papelera de pacientes menores de edad', $_SESSION["permisos"])) : ?>
        <a href="papelera/pacientes_menores_papelera_listado.php" class="btn-sm btn-primary pull-right" style="background-color:gray;"> Papelera </a>
      <?php endif; ?>
      <p class="pull-right" style="width:5px;"></p>
      <?php if (in_array('Generar Reportes de Pacientes', $_SESSION["permisos"])) : ?>
        <a href="#" class="btn-sm btn-info pull-right reporte"><i class="fa fa-book"></i> Generar Reporte </a>
      <?php endif; ?>
      <p class="pull-right" style="width:5px;"></p>
      <?php if (in_array('Crear Pacientes', $_SESSION["permisos"])) : ?>
        <a href="pacientes_menores_agregar.php" class="btn-sm btn-success pull-right"><i class="fa fa-user-plus"></i> Añadir Un Nuevo Paciente </a>
      <?php endif; ?>
      <input type="text" id="buscar" name="buscar" class="form-control" placeholder="Escriba para buscar..." value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>" style="border-radius:0; height:10%; width:250px; display:inline-block;" autocomplete="off">
    </div>
    <br><br>
    <div id="contenedorTabla">
      <table class="table table-sm table-hover mt-4" width="100%" height="20" id="t_user">
        <thead class="table-dark" style="background-color: #222; color: white; font-size: 12px;">
          <th>Cedula del Representante</th>
          <th>Representante</th>
          <th>Nombres Y Apellidos Del Menor</th>
          <th>Genero</th>
          <th>Edad</th>
          <th>Tipo De Paciente</th>
          <?php if (in_array('Gestionar acciones de pacientes menores de edad', $_SESSION["permisos"])) : ?>
            <th>Acciones</th>
          <?php endif; ?>
        </thead>
        <tbody class="tbody" width="100%" style="font-size: 12px;">
          <?php
          $busqueda = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : '';
          $registros_por_pagina = 14;
          $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
          $inicio = ($pagina_actual - 1) * $registros_por_pagina;

          $donde = "WHERE dpr.Id_rol = 3 
                      AND TIMESTAMPDIFF(YEAR, p_menor.fecha_nacimiento, CURDATE()) < 18 
                      AND p_menor.estatus = '1'";

          if ($busqueda != '') {
            $donde .= " AND (p_menor.nombre LIKE '%$busqueda%' 
                             OR p_menor.apellido LIKE '%$busqueda%' 
                             OR p_rep.cedula LIKE '%$busqueda%' 
                             OR p_rep.nombre LIKE '%$busqueda%'
                             OR p_rep.apellido LIKE '%$busqueda%')";
          }

          $subquery_rep_principal = "(
              SELECT hr2.Id
              FROM historial_representantes hr2
              WHERE hr2.Id_paciente = p_menor.Id AND hr2.estatus = '1'
              ORDER BY (hr2.tipo_representante = 'Legal') DESC, hr2.Id ASC
              LIMIT 1
            )";

          $sqlConteo = "SELECT COUNT(*) AS total 
                          FROM persona p_menor
                          JOIN detalle_persona_rol dpr ON p_menor.Id = dpr.Id_persona
                          JOIN detalle_paciente_menor dp ON p_menor.Id = dp.Id_persona
                          LEFT JOIN historial_representantes hr ON hr.Id = $subquery_rep_principal
                          LEFT JOIN persona p_rep ON p_rep.Id = hr.Id_representante
                          $donde";
          $queryConteo = mysqli_query($conexion, $sqlConteo);
          if (!$queryConteo) {
            die("<pre style='color:red;padding:15px;background:#fff;'>Error en conteo SQL: " . htmlspecialchars(mysqli_error($conexion)) . "</pre>");
          }
          $total_pacientes = mysqli_fetch_assoc($queryConteo)['total'];
          $total_paginas = ($registros_por_pagina > 0) ? ceil($total_pacientes / $registros_por_pagina) : 0;

          $sql = "SELECT
                        p_menor.id AS id,
                        p_menor.nombre AS nombre_menor,
                        p_menor.apellido AS apellido_menor,
                        p_menor.tipo_cedula AS tipo_cedula,
                        p_menor.cedula AS cedula_menor,
                        p_menor.genero AS genero_menor,
                        p_menor.fecha_nacimiento AS fecha_nacimiento,
                        p_menor.estatus,
                        TIMESTAMPDIFF(YEAR, p_menor.fecha_nacimiento, CURDATE()) AS edad,
                        p_rep.nombre AS nombre_representante,
                        p_rep.apellido AS apellido_representante,
                        p_rep.tipo_cedula AS tipo_cedula_representante,
                        p_rep.cedula AS cedula_representante,
                        hr.parentesco,
                        hr.tipo_representante,
                        dp.tipo_paciente
                    FROM
                        persona p_menor
                    JOIN
                        detalle_persona_rol dpr ON p_menor.Id = dpr.Id_persona
                    JOIN
                        detalle_paciente_menor dp ON p_menor.Id = dp.Id_persona
                    LEFT JOIN
                        historial_representantes hr ON hr.Id = $subquery_rep_principal
                    LEFT JOIN
                        persona p_rep ON p_rep.Id = hr.Id_representante
                    $donde
                    ORDER BY
                        p_menor.apellido, p_menor.nombre
                    LIMIT $inicio, $registros_por_pagina";

          $resultado = $conexion->query($sql);

          if (!$resultado) {
            die("<pre style='color:red;padding:15px;background:#fff;'>Error SQL: " . htmlspecialchars($conexion->error) . "</pre>");
          }

          if ($resultado->num_rows > 0) {
            while ($row = $resultado->fetch_assoc()) {
              $fecha_nac = new DateTime($row['fecha_nacimiento']);
              $hoy = new DateTime();
              $diff = $hoy->diff($fecha_nac);

              if ($diff->y >= 1) {
                $edad_texto = $diff->y . ($diff->y == 1 ? ' año' : ' años');
              } elseif ($diff->m >= 1) {
                $edad_texto = $diff->m . ($diff->m == 1 ? ' mes' : ' meses');
              } elseif ($diff->d >= 1) {
                $edad_texto = $diff->d . ($diff->d == 1 ? ' día' : ' días');
              } else {
                $edad_texto = 'Recién nacido';
              }

              $cedula_rep_texto = (!empty($row['cedula_representante']))
                ? $row['tipo_cedula_representante'] . '-' . $row['cedula_representante']
                : 'Sin representante';
              $nombre_rep_texto = trim(($row['nombre_representante'] ?? '') . ' ' . ($row['apellido_representante'] ?? ''));
              if ($nombre_rep_texto === '') $nombre_rep_texto = 'Sin representante';
          ?>
              <tr>
                <td class=""><span class="text-row text-white"><?= htmlspecialchars($cedula_rep_texto); ?></span></td>
                <td class=""><span class="text-row text-white"><?= htmlspecialchars($nombre_rep_texto); ?></span></td>
                <td class=""><span class="text-row text-white"><?= htmlspecialchars(($row['nombre_menor']) . " " . ($row['apellido_menor'])); ?></span></td>
                <td class=""><span class="text-row text-white"><?= htmlspecialchars($row['genero_menor']); ?></span></td>
                <td class=""><span class="text-row text-white"><?= $edad_texto; ?></span></td>
                <td class=""><span class="text-row text-white"><?= htmlspecialchars($row['tipo_paciente']); ?></span></td>
                <?php if (in_array('Gestionar acciones de pacientes', $_SESSION["permisos"])) : ?>
                  <td>
                    <?php if (in_array('Ver Pacientes', $_SESSION["permisos"])) : ?>
                      <a href="pacientes_menores_info.php?Id=<?php echo $row['id'] ?>" class="btn-sm btn-info" title="Ver Informacion"><img src="../../recursos/imagenes/iconos/info.png" style="width:15px; height:15px;"></a>
                    <?php endif; ?>
                    <?php if (in_array('Editar Pacientes', $_SESSION["permisos"])) : ?>
                      <a href="pacientes_menores_editar.php?Id=<?php echo $row['id'] ?>" class="btn-sm btn-warning" title="Editar"><img src="../../recursos/imagenes/iconos/editar.png" style="width:15px; height:15px;"></a>
                    <?php endif; ?>
                    <?php if (in_array('Desactivar Pacientes', $_SESSION["permisos"])) : ?>
                      <a href="#" data-id="<?php echo $row['id'] ?>" class="btn-sm btn-danger btn-desactivar" title="Desactivar"><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></a>
                    <?php endif; ?>
                  </td>
                <?php endif; ?>
              </tr>
          <?php
            }
          } else {
            echo "<tr><td colspan='7'>No se encontraron pacientes menores registrados.</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>

    <nav id="contenedorPaginacion" aria-label="Page navigation" style="position: fixed; bottom:0;">
      <ul class="pagination">
        <?php
        $query_string = ($busqueda != '') ? "&buscar=" . urlencode($busqueda) : "";
        if ($pagina_actual > 1) : ?>
          <li><a href="?pagina=1<?php echo $query_string; ?>">&laquo;&laquo;</a></li>
          <li><a href="?pagina=<?php echo ($pagina_actual - 1) . $query_string; ?>">&laquo;</a></li>
        <?php endif;

        for ($i = max(1, $pagina_actual - 1); $i <= min($total_paginas, $pagina_actual + 1); $i++) : ?>
          <li class="<?php echo ($i == $pagina_actual) ? 'active' : ''; ?>">
            <a href="?pagina=<?php echo $i . $query_string; ?>"><?php echo $i; ?></a>
          </li>
        <?php endfor;

        if ($pagina_actual < $total_paginas) : ?>
          <li><a href="?pagina=<?php echo ($pagina_actual + 1) . $query_string; ?>">&raquo;</a></li>
          <li><a href="?pagina=<?php echo $total_paginas . $query_string; ?>">&raquo;&raquo;</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  </section>

  <?php
  $mostrar_modal_exito = false;
  $mostrar_modal_error = false;
  $mensaje_modal = '';

  if (isset($_SESSION['mensaje_user_exito'])) {
    $mostrar_modal_exito = true;
    $mensaje_modal = $_SESSION['mensaje_user_exito'];
    unset($_SESSION['mensaje_user_exito']);
  } elseif (isset($_SESSION['mensaje_user_error'])) {
    $mostrar_modal_error = true;
    $mensaje_modal = $_SESSION['mensaje_user_error'];
    unset($_SESSION['mensaje_user_error']);
  }
  ?>

  <div class="modal fade" id="modalExito" tabindex="-1" role="dialog" aria-labelledby="modalExitoLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-green">
          <h5 class="modal-title" id="modalExitoLabel" style="color: white;">Operación Exitosa</h5>
        </div>
        <div class="modal-body">
          <p id="mensajeExito"><?php echo $mensaje_modal; ?></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success" data-dismiss="modal">Aceptar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalError" tabindex="-1" role="dialog" aria-labelledby="modalErrorLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h5 class="modal-title" id="modalErrorLabel" style="color: white;">Error en la Operación</h5>
        </div>
        <div class="modal-body">
          <p id="mensajeError"><?php echo $mensaje_modal; ?></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="DesactivarPaciente" tabindex="-1" role="dialog" aria-labelledby="DesactivarPacienteLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: #dc3545; color: white;">
          <h4 class="modal-title" id="DesactivarPacienteLabel">Confirmacion de Desactivación</h4>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea desactivar este paciente? Esta acción solo se puede revertir en la papelera.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <a href="#" id="desactivar" class="btn btn-danger">Aceptar</a>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="ModalReportePacienteMenor" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h4 class="modal-title" id="myModalLabel"><i class="fa fa-child"></i> Reportes de Pacientes Menores de Edad</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Seleccione el tipo de reporte:</label>
            <select class="form-control" id="tipo_reporte_menor">
              <option value="todos">Listado General (Todos los menores)</option>
              <option value="activos">Solo Menores Activos</option>
              <option value="representante">Agrupados por Representante</option>
              <option value="parentesco">Agrupados por Parentesco</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-primary" id="btnEjecutarReporteMenor">Generar PDF</button>
        </div>
      </div>
    </div>
  </div>

  <?php
  include('includes/footer.php');
  ?>

  <script>
    $(document).ready(function() {

      $('.reporte').on('click', function(e) {
        e.preventDefault();
        $('#ModalReportePacienteMenor').modal('show');
      });

      $('#btnEjecutarReporteMenor').on('click', function() {
        var tipo = $('#tipo_reporte_menor').val();
        window.open('../../cfg/reportes/generar_pdf_pacientes_menores.php?tipo=' + tipo, '_blank');
        $('#ModalReportePacienteMenor').modal('hide');
      });

      $(document).on('click', '.btn-desactivar', function(e) {
        e.preventDefault();
        var IdPaciente = $(this).data('id');
        var urlDesactivar = "../../cfg/desactivar/desactivar_paciente_menor.php?Id=" + IdPaciente;
        $('#desactivar').attr('href', urlDesactivar);
        $('#DesactivarPaciente').modal('show');
      })

      <?php if ($mostrar_modal_exito) : ?>
        $('#modalExito').modal('show');
      <?php elseif ($mostrar_modal_error) : ?>
        $('#modalError').modal('show');
      <?php endif; ?>
    });
  </script>