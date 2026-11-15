<?php
include('../includes/headerPapelera.php');
verificarAcceso('Ver papelera de especialidades');
?>

<head>
  <title>Especialidades | Papelera</title>
</head>

<div class="content-wrapper">
  <?php
  include('../../../cfg/conexion.php');
  $sqlEspecialidad = ("SELECT * FROM especialidad WHERE estatus = 0 ORDER BY Id_especialidad ASC");
  $queryData   = mysqli_query($conexion, $sqlEspecialidad);
  $total_especialidades = mysqli_num_rows($queryData);
  ?>

  <section class="content-header">
    <h1>
      Especialidades Inactivas (<?php echo $total_especialidades ?>)
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="#"><i class="fa fa-user"></i>Especialidades</a></li>
      <li><a href="#"><i class="fa fa-book"></i>Papelera</a></li>
    </ol>
  </section>

  <section class="content">
    <div class="toolbar-container">
      <a href="../rh_especialidades_listado.php" class="btn-sm btn-primary pull-right"> Regresar al Listado </a>
      <input type="text" id="buscar" name="buscar" class="form-control" placeholder="Escriba para buscar..." value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>" style="border-radius:0; height:10%; width:250px; display:inline-block;" autocomplete="off"> </div>
    <br><br>
    <div id="contenedorTabla">
      <table class="table table-sm table-hover mt-4" width="100%" height="20" id="t_user">
        <thead class="table-dark">
          <th>Nombre de la especialidad</th>
          <th>Médicos</th>
          <?php if (in_array('Gestionar acciones de especialidades', $_SESSION["permisos"])) : ?>
            <th>Acciones</th>
          <?php endif; ?>
        </thead>
        <tbody class="tbody" width="100%" style="font-size: 12px;">
          <tr>
            <?php
            $busqueda = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : '';

            $registros_por_pagina = 14;
            $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
            $inicio = ($pagina_actual - 1) * $registros_por_pagina;

            $donde = "WHERE e.estatus = 0";
            if ($busqueda != '') {
              $donde .= " AND (e.nombre_especialidad LIKE '%$busqueda%')";
            }

            $sql_conteo = "SELECT COUNT(*) as total FROM especialidad e $donde";
            $resultado_conteo = mysqli_query($conexion, $sql_conteo);
            $fila_conteo = mysqli_fetch_assoc($resultado_conteo);
            $total_especialidades_filtradas = $fila_conteo['total'];
            $total_paginas = ceil($total_especialidades_filtradas / $registros_por_pagina);

            $sql = "SELECT 
              e.*, 
              (SELECT COUNT(DISTINCT em.Id_detalle_medico) 
              FROM especialidades_medicos em 
              WHERE em.Id_especialidad = e.Id_especialidad) AS total_medicos
              FROM 
                especialidad e
              $donde
              ORDER BY e.Id_especialidad ASC
              LIMIT $inicio, $registros_por_pagina";
            $resultado = $conexion->query($sql);
            ?>
          </tr>
          <tr>
            <?php
            if ($resultado->num_rows > 0) {
              while ($row = $resultado->fetch_assoc()) {
            ?>
          </tr>
          <tr>
            <td class=""><span class="text-row text-white"><?= $row['nombre_especialidad']; ?></span></td>
            <td class=""><span class="text-row text-white"><?= $row['total_medicos']; ?></span></td>
            <?php if (in_array('Gestionar acciones de especialidades', $_SESSION["permisos"])) : ?>
              <td>
                <?php if (in_array('Reactivar Especialidades', $_SESSION["permisos"])) : ?>
                  <a href="#" data-id="<?php echo $row['Id_especialidad'] ?>" class="btn-sm btn-success btn-reactivar" title="Reactivar"><img src="../../../recursos/imagenes/iconos/reactivar.png" style="width:15px; height:15px;"></a>
                <?php endif; ?>
                <?php if (in_array('Eliminar Especialidades', $_SESSION["permisos"])) : ?>
                  <a href="#" class="btn-sm btn-danger btn-eliminar" data-id="<?php echo $row['Id_especialidad'] ?>" title="Eliminar"><img src="../../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></a>
                <?php endif; ?>
              </td>
            <?php endif; ?>
          </tr>
      <?php }
            } else {
              echo "<tr><td colspan='6'>No se encontraron especialidades inactivas.</td></tr>";
            } ?>
        </tbody>
      </table>
    </div>

    <nav id="contenedorPaginacion" aria-label="Page navigation">
      <ul class="pagination">
        <?php
        $query_string = ($busqueda != '') ? "&buscar=" . urlencode($busqueda) : "";

        if ($pagina_actual > 1) : ?>
          <li><a href="?pagina=1<?php echo $query_string; ?>" title="Primero">&laquo;&laquo;</a></li>
          <li><a href="?pagina=<?php echo ($pagina_actual - 1) . $query_string; ?>">&laquo;</a></li>
        <?php endif;

        $rango = 1;
        $inicio_ventana = max(1, $pagina_actual - $rango);
        $fin_ventana = min($total_paginas, $pagina_actual + $rango);

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
          <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="ReactivarEspecialidad" tabindex="-1" role="dialog" aria-labelledby="ReactivarEspecialidadLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-green">
          <h4 class="modal-title" id="ReactivarEspecialidadLabel">Confirmación de Reactivación</h4>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea reactivar esta especialidad</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <a href="#" class="btn btn-success" id="reactivar">Aceptar</a>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="EliminarEspecialidad" tabindex="-1" role="dialog" aria-labelledby="EliminarEspecialidadLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h4 class="modal-title" id="EliminarEspecialidadLabel">Confirmación de Eliminación</h4>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea eliminar esta especialidad? Esta acción no se puede deshacer.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <a href="#" class="btn btn-danger" id="eliminar">Aceptar</a>
        </div>
      </div>
    </div>
  </div>

  <?php
  include('../includes/footer.php');
  ?>

  <script>
    $(document).ready(function() {
      $(document).on('click', '.btn-reactivar', function(e) {
        e.preventDefault();
        var IdEspecialidad = $(this).data('id');
        var urlReactivar = "../../../cfg/reactivar/reactivar_especialidad.php?Id=" + IdEspecialidad;
        $('#reactivar').attr('href', urlReactivar);
        $('#ReactivarEspecialidad').modal('show');
      })

      $(document).on('click', '.btn-eliminar', function(e) {
        e.preventDefault();
        var IdEspecialidad = $(this).data('id');
        var urlEliminar = "../../../cfg/eliminar/eliminar_especialidad.php?Id=" + IdEspecialidad;
        $('#eliminar').attr('href', urlEliminar);
        $('#EliminarEspecialidad').modal('show');
      })

      <?php if ($mostrar_modal_exito) : ?>
        $('#modalExito').modal('show');
      <?php elseif ($mostrar_modal_error) : ?>
        $('#modalError').modal('show');
      <?php endif; ?>
    });
  </script>