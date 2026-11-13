<?php
include('includes/headerNav2.php');
verificarAcceso('Gestionar Salud');
?>

<head>
  <title>Salud | Patologías</title>
</head>

<div class="content-wrapper">
  <?php
  include('../../cfg/conexion.php');
  $sqlPatologia = ("SELECT * FROM patologias WHERE estatus = 1 ORDER BY Id_patologia ASC");
  $queryData   = mysqli_query($conexion, $sqlPatologia);
  $total_patologia = mysqli_num_rows($queryData);
  ?>

  <section class="content-header">
    <h1>
      Patologías (<?php echo $total_patologia; ?>)
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="#"><i class="fa fa-user"></i>Patologías</a></li>
      <li><a href="#"><i class="fa fa-book"></i>Listado</a></li>
    </ol>
  </section>

  <section class="content">
    <div style="padding-bottom: 10px;">
      <?php if (in_array('Ver papelera de patologias', $_SESSION["permisos"])) : ?>
        <a href="papelera/salud_patologias_papelera_listado.php" class="btn-sm btn-primary pull-right" style="background-color: gray;"> Papelera </a>
      <?php endif; ?>
      <p class="pull-right" style="width:5px;"></p>
      <?php if (in_array('Generar Reportes de Patologias', $_SESSION["permisos"])) : ?>
        <a href="#" class="btn-sm btn-info pull-right reporte"><i class="fa fa-book"></i> Generar Reporte </a>
      <?php endif; ?>
      <p class="pull-right" style="width:5px;"></p>
      <?php if (in_array('Crear Patologias', $_SESSION["permisos"])) : ?>
        <a href="salud_patologias_agregar.php" class="btn-sm btn-success pull-right"><i class="fa fa-user-plus"></i> Añadir Una Nueva Patología </a>
      <?php endif; ?>
      <input type="text" id="buscar" name="buscar" class="form-control" placeholder="Escriba para buscar..." value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>" style="border-radius:0; height:10%; width:250px; display:inline-block;" autocomplete="off">
    </div>
    <br><br>
    <div id="contenedorTabla">
      <table class="table table-sm table-hover mt-4" width="100%" height="20" id="t_user">
        <thead class="table-dark" style="background-color: #222; color: white; font-size: 12px;">
          <th>Nombre de la Patología</th>
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
            $registros_por_pagina = 14;
            $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
            $inicio = ($pagina_actual - 1) * $registros_por_pagina;

            $busqueda = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : '';
            $donde = "WHERE estatus = 1";
            if ($busqueda != '') {
              $donde .= " AND (nombre_patologia LIKE '%$busqueda%' OR codigo_cie LIKE '%$busqueda%')";
            }

            $sqlPatologia = "SELECT * FROM patologias $donde ORDER BY Id_patologia ASC";
            $queryData = mysqli_query($conexion, $sqlPatologia);
            $total_patologia = mysqli_num_rows($queryData);

            $sql_conteo = "SELECT COUNT(*) as total FROM patologias $donde";
            $resultado_conteo = mysqli_query($conexion, $sql_conteo);
            $fila_conteo = mysqli_fetch_assoc($resultado_conteo);
            $total_registros_filtrados = $fila_conteo['total'];
            $total_paginas = ceil($total_registros_filtrados / $registros_por_pagina);

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
                <?php if (in_array('Ver Patologias', $_SESSION["permisos"])) : ?>
                  <a href="salud_patologias_info.php?Id=<?php echo $row['Id_patologia'] ?>" class="btn-sm btn-info" title="Ver"><img src="../../recursos/imagenes/iconos/info.png" style="width:15px; height:15px;"></a>
                <?php endif; ?>
                <?php if (in_array('Editar Patologias', $_SESSION["permisos"])) : ?>
                  <a href="salud_patologias_editar.php?Id=<?php echo $row['Id_patologia'] ?>" class="btn-sm btn-warning" title="Editar"><img src="../../recursos/imagenes/iconos/editar.png" style="width:15px; height:15px;"></a>
                <?php endif; ?>
                <?php if (in_array('Desactivar Patologias', $_SESSION["permisos"])) : ?>
                  <a href="#" data-id="<?php echo $row['Id_patologia'] ?>" class="btn-sm btn-danger btn-desactivar" title="Desactivar"><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></a>
                <?php endif; ?>
              </td>
            <?php endif; ?>
          </tr>
      <?php }
            } else {
              echo "<tr><td colspan='6'>No se encontraron patologías registradas.</td></tr>";
            } ?>
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

  <div class="modal fade" id="DesactivarPatologia" tabindex="-1" role="dialog" aria-labelledby="DesactivarPatologiaLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h4 class="modal-title" id="DesactivarPatologiaLabel">Confirmación de Desactivación</h4>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea desactivar esta patologia? Esta acción solo se puede revertir en la papelera.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <a href="#" class="btn btn-danger" id="desactivar">Aceptar</a>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="ModalReportePatologia" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h4 class="modal-title" id="myModalLabel"><i class="fa fa-file-pdf-o"></i> Reporte de Catálogo de Patologías</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Seleccione el filtro del reporte:</label>
            <select class="form-control" id="tipo_reporte_patologia">
              <option value="todas">Todas las Patologías (Catálogo Completo)</option>
              <option value="activas">Solo Patologías Activas</option>
              <option value="inactivas">Solo Patologías Inactivas</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-primary" id="btnEjecutarReportePatologia">Generar PDF</button>
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
        $('#ModalReportePatologia').modal('show');
      });

      $(document).on('click', '.btn-desactivar', function(e) {
        e.preventDefault();
        var IdPatologia = $(this).data('id');
        var urlDesactivar = "../../cfg/desactivar/desactivar_patologia.php?Id=" + IdPatologia;
        $('#desactivar').attr('href', urlDesactivar);
        $('#DesactivarPatologia').modal('show');
      })

      $('#btnEjecutarReportePatologia').on('click', function() {
        var tipo = $('#tipo_reporte_patologia').val();
        window.open('../../cfg/reportes/generar_pdf_patologias.php?tipo=' + tipo, '_blank');
        $('#ModalReportePatologia').modal('hide');
      });

      <?php if ($mostrar_modal_exito) : ?>
        $('#modalExito').modal('show');
      <?php elseif ($mostrar_modal_error) : ?>
        $('#modalError').modal('show');
      <?php endif; ?>
    });
  </script>