<?php
include('includes/headerNav2.php');
verificarAcceso('Gestionar RH');
?>

<head>
  <title>Recursos Humanos | Áreas</title>
</head>

<div class="content-wrapper">
  <?php
  include('../../cfg/conexion.php');
  $sqlArea = ("SELECT * FROM departamento WHERE estatus = 1 ORDER BY Id_departamento ASC");
  $queryData   = mysqli_query($conexion, $sqlArea);
  $total_area = mysqli_num_rows($queryData);
  ?>

  <section class="content-header">
    <h1>
      Áreas (<?php echo $total_area; ?>)
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="#"><i class="fa fa-user"></i>Áreas</a></li>
      <li><a href="#"><i class="fa fa-book"></i>Listado</a></li>
    </ol>
  </section>

  <section class="content">
    <div style="padding-bottom: 10px;">
      <?php if (in_array('Ver papelera de areas', $_SESSION["permisos"])) : ?>
        <a href="papelera/rh_areas_papelera_listado.php" class="btn-sm btn-primary pull-right" style="background-color: gray;"> Papelera </a>
      <?php endif; ?>
      <p class="pull-right" style="width:5px;"></p>
      <?php if (in_array('Generar Reportes de Areas', $_SESSION["permisos"])) : ?>
        <a href="#" class="btn-sm btn-info pull-right reporte"><i class="fa fa-book"></i> Generar Reporte </a>
      <?php endif; ?>
      <p class="pull-right" style="width:5px;"></p>
      <?php if (in_array('Crear Areas', $_SESSION["permisos"])) : ?>
        <a href="rh_areas_agregar.php" class="btn-sm btn-success pull-right"><i class="fa fa-user-plus"></i> Añadir Una Nueva Área </a>
      <?php endif; ?>
      <input type="text" id="buscar" name="buscar" class="form-control" placeholder="Escriba para buscar..." value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>" style="border-radius:0; height:10%; width:250px; display:inline-block;" autocomplete="off">
    </div>
    <br><br>
    <div id="contenedorTabla">
      <table class="table table-sm table-hover mt-4" width="100%" height="20" id="t_user">
        <thead class="table-dark" style="background-color: #222; color: white; font-size: 12px;">
          <th>Nombre del área</th>
          <th>Empleados</th>
          <?php if (in_array('Gestionar acciones de areas', $_SESSION["permisos"])) : ?>
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

            $donde = "WHERE dm.estatus = 1";
            if ($busqueda != '') {
              $donde .= " AND (dm.nombre_departamento LIKE '%$busqueda%')";
            }

            $sql_conteo = "SELECT COUNT(*) as total FROM departamento dm $donde";
            $resultado_conteo = mysqli_query($conexion, $sql_conteo);
            $fila_conteo = mysqli_fetch_assoc($resultado_conteo);
            $total_registros = $fila_conteo['total'];
            $total_paginas = ceil($total_registros / $registros_por_pagina);

            $sql = "SELECT 
              dm.*, 
              (SELECT COUNT(DISTINCT md.Id_detalle_medico) 
              FROM medicos_departamentos md 
              WHERE md.Id_departamento = dm.Id_departamento) AS total_medicos
              FROM 
                departamento dm
              $donde
              ORDER BY dm.Id_departamento ASC
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
            <td class=""><span class="text-row text-white"><?= $row['nombre_departamento']; ?></span></td>
            <td class=""><span class="text-row text-white"><?= $row['total_medicos']; ?></span></td>
            <?php if (in_array('Gestionar acciones de areas', $_SESSION["permisos"])) : ?>
              <td>
                <?php if (in_array('Editar Areas', $_SESSION["permisos"])) : ?>
                  <a href="rh_areas_editar.php?Id=<?php echo $row['Id_departamento'] ?>" title="Editar" class="btn-sm btn-warning"><img src="../../recursos/imagenes/iconos/editar.png" style="width:15px; height:15px;"></a>
                <?php endif; ?>
                <?php if (in_array('Desactivar Areas', $_SESSION["permisos"])) : ?>
                  <a href="#" data-id="<?php echo $row['Id_departamento'] ?>" title="Desactivar" class="btn-sm btn-danger btn-desactivar"><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></a>
                <?php endif; ?>
              </td>
            <?php endif; ?>
          </tr>
      <?php }
            } else {
              echo "<tr><td colspan='6'>No se encontraron áreas registradas.</td></tr>";
            } ?>
        </tbody>
      </table>
    </div>

    <nav id="contenedorPaginacion" aria-label="Page navigation" style="position: fixed; bottom:0;">
      <ul class="pagination">
        <?php
        $query_string = ($busqueda != '') ? "&buscar=" . urlencode($busqueda) : "";

        $rango = 1;
        $inicio_ventana = max(1, $pagina_actual - $rango);
        $fin_ventana = min($total_paginas, $pagina_actual + $rango);

        if ($pagina_actual > 1) : ?>
          <li><a href="?pagina=1<?php echo $query_string; ?>">&laquo;&laquo;</a></li>
          <li><a href="?pagina=<?php echo ($pagina_actual - 1) . $query_string; ?>">&laquo;</a></li>
        <?php endif;

        for ($i = $inicio_ventana; $i <= $fin_ventana; $i++) : ?>
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

  <div class="modal fade" id="DesactivarArea" tabindex="-1" role="dialog" aria-labelledby="DesactivarAreaLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h4 class="modal-title" id="DesactivarAreaLabel">Confirmación de Desactivación</h4>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea desactivar esta área? Esta acción solo se puede revertir en la papelera.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <a href="#" id="desactivar" class="btn btn-danger">Aceptar</a>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="ModalReporteArea" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h4 class="modal-title" id="myModalLabel"><i class="fa fa-file-pdf-o"></i> Reportes de Áreas de la Institución</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Seleccione el filtro del reporte:</label>
            <select class="form-control" id="tipo_reporte_area">
              <option value="todas">Todas las Áreas Registradas</option>
              <option value="activas">Solo Áreas Activas</option>
              <option value="inactivas">Solo Áreas Inactivas</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-primary" id="btnEjecutarReporteArea">Generar PDF</button>
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
        $('#ModalReporteArea').modal('show');
      });

      $('#btnEjecutarReporteArea').on('click', function() {
        var tipo = $('#tipo_reporte_area').val();
        window.open('../../cfg/reportes/generar_pdf_areas.php?tipo=' + tipo, '_blank');
        $('#ModalReporteArea').modal('hide');
      });

      $(document).on('click', '.btn-desactivar', function(e) {
        e.preventDefault();
        var IdArea = $(this).data('id');
        var urlDesactivar = "../../cfg/desactivar/desactivar_area.php?Id=" + IdArea;
        $('#desactivar').attr('href', urlDesactivar);
        $('#DesactivarArea').modal('show');
      })

      <?php if ($mostrar_modal_exito) : ?>
        $('#modalExito').modal('show');
      <?php elseif ($mostrar_modal_error) : ?>
        $('#modalError').modal('show');
      <?php endif; ?>
    });
  </script>