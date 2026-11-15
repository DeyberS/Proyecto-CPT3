<?php
include('../includes/headerPapelera.php');
verificarAcceso('Ver papelera de pacientes menores de edad');
?>

<head>
  <title>Pacientes Menores de Edad | Papelera</title>
</head>

<div class="content-wrapper">
  <?php
  include('../../../cfg/conexion.php');
  $sqlPacientes = "SELECT COUNT(p.id) AS total_pacientes
                     FROM persona p
                     JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona
                     JOIN rol r ON dpr.Id_rol = r.Id_rol
                     WHERE r.Id_rol = 3
                       AND TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) < 18
                       AND p.estatus = '0'";
  $queryData = mysqli_query($conexion, $sqlPacientes);
  if (!$queryData) {
    die("<pre style='color:red;padding:15px;background:#fff;'>Error en conteo inicial: "
      . htmlspecialchars(mysqli_error($conexion)) . "</pre>");
  }
  $total_pacientes = mysqli_fetch_assoc($queryData)['total_pacientes'];
  ?>

  <section class="content-header">
    <h1>
      Pacientes Menores de Edad Inactivos (<?php echo $total_pacientes; ?>)
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="#"><i class="fa fa-user"></i>Pacientes Menores de Edad</a></li>
      <li><a href="#"><i class="fa fa-book"></i>Papelera</a></li>
    </ol>
  </section>

  <section class="content">
    <div class="toolbar-container">
      <a href="../pacientes_menores_listado.php" class="btn-sm btn-primary pull-right">
        <i class="fa fa-arrow-left"></i> Regresar al Listado
      </a>
      <input type="text" id="buscar" name="buscar" class="form-control" placeholder="Escriba para buscar..." value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>" style="border-radius:0; height:10%; width:250px; display:inline-block;" autocomplete="off">
    </div>
    <br><br>
    <div id="contenedorTabla">
      <table class="table table-sm table-hover mt-4" width="100%" height="20" id="t_user">
        <thead class="table-dark">
          <th>Cedula del Representante</th>
          <th>Representante</th>
          <th>Nombres Y Apellidos Del Menor</th>
          <th>Genero</th>
          <th>Edad</th>
          <?php if (in_array('Gestionar acciones de pacientes menores de edad', $_SESSION["permisos"])) : ?>
            <th>Acciones</th>
          <?php endif; ?>
        </thead>
        <tbody class="tbody" width="100%" style="font-size: 12px;">
          <?php
          $busqueda = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : '';
          $registros_por_pagina = 14;
          $pagina_actual = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
          $inicio = ($pagina_actual - 1) * $registros_por_pagina;

          $donde = "WHERE dpr.Id_rol = 3
                      AND TIMESTAMPDIFF(YEAR, p_menor.fecha_nacimiento, CURDATE()) < 18
                      AND p_menor.estatus = '0'";

          if ($busqueda != '') {
            $donde .= " AND (p_menor.nombre LIKE '%$busqueda%'
                            OR p_menor.apellido LIKE '%$busqueda%'
                            OR p_menor.cedula LIKE '%$busqueda%'
                            OR p_rep.cedula LIKE '%$busqueda%'
                            OR p_rep.nombre LIKE '%$busqueda%'
                            OR p_rep.apellido LIKE '%$busqueda%')";
          }

          $subquery_rep_principal = "(
              SELECT hr2.Id
              FROM historial_representantes hr2
              WHERE hr2.Id_paciente = p_menor.Id
              ORDER BY (hr2.tipo_representante = 'Legal') DESC, hr2.Id ASC
              LIMIT 1
            )";

          $sql_conteo = "SELECT COUNT(*) AS total
                           FROM persona p_menor
                           JOIN detalle_persona_rol dpr ON p_menor.Id = dpr.Id_persona
                           JOIN detalle_paciente_menor dp ON p_menor.Id = dp.Id_persona
                           LEFT JOIN historial_representantes hr ON hr.Id = $subquery_rep_principal
                           LEFT JOIN persona p_rep ON p_rep.Id = hr.Id_representante
                           $donde";
          $resultado_conteo = mysqli_query($conexion, $sql_conteo);
          if (!$resultado_conteo) {
            die("<pre style='color:red;padding:15px;background:#fff;'>Error en conteo SQL: "
              . htmlspecialchars(mysqli_error($conexion)) . "</pre>");
          }
          $fila_conteo = mysqli_fetch_assoc($resultado_conteo);
          $total_permisos = (int)$fila_conteo['total'];
          $total_paginas = ($registros_por_pagina > 0) ? ceil($total_permisos / $registros_por_pagina) : 0;

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
                    FROM persona p_menor
                    JOIN detalle_persona_rol dpr ON p_menor.Id = dpr.Id_persona
                    JOIN detalle_paciente_menor dp ON p_menor.Id = dp.Id_persona
                    LEFT JOIN historial_representantes hr ON hr.Id = $subquery_rep_principal
                    LEFT JOIN persona p_rep ON p_rep.Id = hr.Id_representante
                    $donde
                    ORDER BY p_menor.apellido, p_menor.nombre
                    LIMIT $inicio, $registros_por_pagina";

          $resultado = $conexion->query($sql);

          if ($resultado === false) {
            die("<pre style='color:red;padding:15px;background:#fff;'>Error SQL: "
              . htmlspecialchars($conexion->error) . "</pre>");
          }

          if ($resultado->num_rows > 0) {
            while ($row = $resultado->fetch_assoc()) {
              $fechaNacimiento = new DateTime($row['fecha_nacimiento']);
              $hoy = new DateTime();
              $diff = $hoy->diff($fechaNacimiento);

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
                <td><span class="text-row text-white"><?= htmlspecialchars($cedula_rep_texto); ?></span></td>
                <td><span class="text-row text-white"><?= htmlspecialchars($nombre_rep_texto); ?></span></td>
                <td><span class="text-row text-white"><?= htmlspecialchars($row['nombre_menor'] . " " . $row['apellido_menor']); ?></span></td>
                <td><span class="text-row text-white"><?= htmlspecialchars($row['genero_menor']); ?></span></td>
                <td><span class="text-row text-white"><?= $edad_texto; ?></span></td>
                <?php if (in_array('Gestionar acciones de pacientes menores de edad', $_SESSION["permisos"])) : ?>
                  <td>
                    <?php if (in_array('Reactivar Pacientes Menores de Edad', $_SESSION["permisos"])) : ?>
                      <a href="#" data-id="<?php echo $row['id'] ?>" class="btn-sm btn-success btn-reactivar" title="Reactivar">
                        <img src="../../../recursos/imagenes/iconos/reactivar.png" style="width:15px; height:15px;">
                      </a>
                    <?php endif; ?>
                    <?php if (in_array('Eliminar Pacientes Menores de Edad', $_SESSION["permisos"])) : ?>
                      <a href="#" data-id="<?php echo $row['id'] ?>" class="btn-sm btn-danger btn-eliminar" title="Eliminar">
                        <img src="../../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;">
                      </a>
                    <?php endif; ?>
                  </td>
                <?php endif; ?>
              </tr>
          <?php
            }
          } else {
            echo "<tr><td colspan='6'>No se encontraron pacientes menores inactivos.</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>

    <nav id="contenedorPaginacion" aria-label="Page navigation">
      <ul class="pagination">
        <?php
        $query_string = ($busqueda != '') ? "&buscar=" . urlencode($busqueda) : "";

        $rango = 1;
        $inicio_ventana = max(1, $pagina_actual - $rango);
        $fin_ventana = min($total_paginas, $pagina_actual + $rango);

        if ($pagina_actual == 1) $fin_ventana = min($total_paginas, 3);
        if ($pagina_actual == $total_paginas) $inicio_ventana = max(1, $total_paginas - 2);

        if ($pagina_actual > 1) : ?>
          <li><a href="?pagina=1<?php echo $query_string; ?>" title="Primero">&laquo;&laquo;</a></li>
          <li><a href="?pagina=<?php echo ($pagina_actual - 1) . $query_string; ?>">&laquo;</a></li>
        <?php endif;

        for ($i = $inicio_ventana; $i <= $fin_ventana; $i++) : ?>
          <li class="<?php echo ($i == $pagina_actual) ? 'active' : ''; ?>">
            <a href="?pagina=<?php echo $i . $query_string; ?>"><?php echo $i; ?></a>
          </li>
        <?php endfor;

        if ($pagina_actual < $total_paginas) : ?>
          <li><a href="?pagina=<?php echo ($pagina_actual + 1) . $query_string; ?>">&raquo;</a></li>
          <li><a href="?pagina=<?php echo $total_paginas . $query_string; ?>" title="Último">&raquo;&raquo;</a></li>
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
          <h5 class="modal-title" id="modalExitoLabel">Operación Exitosa</h5>
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
          <h5 class="modal-title" id="modalErrorLabel">Error en la Operación</h5>
        </div>
        <div class="modal-body">
          <p id="mensajeError"><?php echo $mensaje_modal; ?></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="ReactivarPaciente" tabindex="-1" role="dialog" aria-labelledby="ReactivarPacienteLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-green">
          <h4 class="modal-title" id="ReactivarPacienteLabel">Confirmación de Reactivación</h4>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea reactivar este paciente?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <a href="#" class="btn btn-success" id="reactivar">Aceptar</a>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="EliminarPaciente" tabindex="-1" role="dialog" aria-labelledby="EliminarPacienteLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h4 class="modal-title" id="EliminarPacienteLabel">Confirmación de Eliminación</h4>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea eliminar este paciente? Esta acción <b>no se puede deshacer</b>.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <a href="#" class="btn btn-danger" id="eliminar">Aceptar</a>
        </div>
      </div>
    </div>
  </div>

  <?php include('../includes/footer.php'); ?>

  <script>
    $(document).ready(function() {

      $(document).on('click', '.btn-reactivar', function(e) {
        e.preventDefault();
        var IdPaciente = $(this).data('id');
        var urlReactivar = "../../../cfg/reactivar/reactivar_paciente_menor.php?Id=" + IdPaciente;
        $('#reactivar').attr('href', urlReactivar);
        $('#ReactivarPaciente').modal('show');
      });

      $(document).on('click', '.btn-eliminar', function(e) {
        e.preventDefault();
        var IdPaciente = $(this).data('id');
        var urlEliminar = "../../../cfg/eliminar/eliminar_paciente_menor.php?Id=" + IdPaciente;
        $('#eliminar').attr('href', urlEliminar);
        $('#EliminarPaciente').modal('show');
      });

      <?php if ($mostrar_modal_exito) : ?>
        $('#modalExito').modal('show');
      <?php elseif ($mostrar_modal_error) : ?>
        $('#modalError').modal('show');
      <?php endif; ?>
    });
  </script>