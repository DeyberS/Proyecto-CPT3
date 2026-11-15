<?php
include('../includes/headerPapelera.php');
verificarAcceso('Ver papelera de patologias');
?>

<head>
  <title>Patologías | Papelera</title>
</head>

<div class="content-wrapper">
  <?php
  include('../../../cfg/conexion.php');
  $sqlPatologia = ("SELECT * FROM patologias WHERE estatus = 0 ORDER BY Id_patologia ASC");
  $queryData   = mysqli_query($conexion, $sqlPatologia);
  $total_patologia = mysqli_num_rows($queryData);
  ?>

  <section class="content-header">
    <h1>
      Patologías Inactivas (<?php echo $total_patologia; ?>)
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="#"><i class="fa fa-user"></i>Patologías</a></li>
      <li><a href="#"><i class="fa fa-book"></i>Papelera</a></li>
    </ol>
  </section>

  <section class="content">
    <div class="toolbar-container">
      <a href="../salud_patologias_listado.php" class="btn-sm btn-primary pull-right"> Regresar al Listado </a>
      <input type="text" id="buscar" name="buscar" class="form-control" placeholder="Escriba para buscar..." value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>" style="border-radius:0; height:10%; width:250px; display:inline-block;" autocomplete="off">
    </div>
    <br><br>
    <div id="contenedorTabla">
      <table class="table table-sm table-hover mt-4" width="100%" height="20" id="t_user">
        <thead class="table-dark">
          <th>Nombre de la patología</th>
          <th>Código CIE-10</th>
          <th>Pacientes</th>
          <th>Contagioso</th>
          <?php if (in_array('Gestionar acciones de patologias', $_SESSION["permisos"])) : ?>
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

            $donde = "WHERE estatus = 0";
            if ($busqueda != '') {
              $donde .= " AND (nombre_patologia LIKE '%$busqueda%')";
            }

            $sql_conteo = "SELECT COUNT(*) as total FROM patologias $donde";
            $resultado_conteo = mysqli_query($conexion, $sql_conteo);
            $fila_conteo = mysqli_fetch_assoc($resultado_conteo);
            $total_permisos = $fila_conteo['total'];
            $total_paginas = ceil($total_permisos / $registros_por_pagina);

            $sql = "SELECT 
            p.*, 
            (SELECT COUNT(DISTINCT hp.Id_Historial) 
             FROM historial_patologias hp 
             WHERE hp.Id_patologia = p.Id_patologia
               AND hp.estatus = 1) AS total_pacientes
            FROM 
              patologias p 
            $donde
            ORDER BY p.Id_patologia ASC
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
            <td class=""><span class="text-row text-white"><?= $row['nombre_patologia']; ?></span></td>
            <td class=""><span class="text-row text-white"><?= $row['codigo_cie']; ?></span></td>
            <td class=""><span class="text-row text-white"><?= $row['total_pacientes']; ?></span></td>
            <td class=""><span class="text-row text-white"></span><?= $row['contagioso']; ?></td>
            <?php if (in_array('Gestionar acciones de patologias', $_SESSION["permisos"])) : ?>
              <td>
                <?php if (in_array('Reactivar Patologias', $_SESSION["permisos"])) : ?>
                  <a href="#" data-id="<?php echo $row['Id_patologia'] ?>" class="btn-sm btn-success btn-reactivar" title="Reactivar"><img src="../../../recursos/imagenes/iconos/reactivar.png" style="width:15px; height:15px;"></a>
                <?php endif; ?>
                <?php if (in_array('Eliminar Patologias', $_SESSION["permisos"])) : ?>
                  <a href="#" data-id="<?php echo $row['Id_patologia'] ?>" class="btn-sm btn-danger btn-eliminar" title="Eliminar"><img src="../../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></a>
                <?php endif; ?>
              </td>
            <?php endif; ?>
          </tr>
      <?php }
            } else {
              echo "<tr><td colspan='6'>No se encontraron patologías inactivas.</td></tr>";
            } ?>
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
          <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="ReactivarPatologia" tabindex="-1" role="dialog" aria-labelledby="ReactivarPatologiaLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-green">
          <h4 class="modal-title" id="ReactivarPatologiaLabel">Confirmación de Reactivación</h4>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea reactivar esta patología</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <a href="#" class="btn btn-success" id="reactivar">Aceptar</a>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="EliminarPatologia" tabindex="-1" role="dialog" aria-labelledby="EliminarPatologiaLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h4 class="modal-title" id="EliminarPatologiaLabel">Confirmación de Eliminación</h4>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea eliminar esta patologia? Esta acción no se puede deshacer.</p>
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
        var IdPatologia = $(this).data('id');
        var urlReactivar = "../../../cfg/reactivar/reactivar_patologia.php?Id=" + IdPatologia;
        $('#reactivar').attr('href', urlReactivar);
        $('#ReactivarPatologia').modal('show');
      })

      $(document).on('click', '.btn-eliminar', function(e) {
        e.preventDefault();
        var IdPatologia = $(this).data('id');
        var urlEliminar = "../../../cfg/eliminar/eliminar_patologia.php?Id=" + IdPatologia;
        $('#eliminar').attr('href', urlEliminar);
        $('#EliminarPatologia').modal('show');
      })

      <?php if ($mostrar_modal_exito) : ?>
        $('#modalExito').modal('show');
      <?php elseif ($mostrar_modal_error) : ?>
        $('#modalError').modal('show');
      <?php endif; ?>
    });
  </script>