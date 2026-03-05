<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Inventario | Papelera</title>
  <?php
  // 1. Incluir encabezado y navegación
  include('includes/headerNav2.php');
  
  // 2. INCLUIR LA CONEXIÓN A LA BASE DE DATOS
  include("../../cfg/conexion.php"); 
  ?>
</head>

<body>
  <div class="content-wrapper">
    <?php
      // --- LÓGICA DE PAGINACIÓN (Basada en medico_listado.php) ---
      $registros_por_pagina = 14;

      $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
      $inicio = ($pagina_actual - 1) * $registros_por_pagina;

      $sql_conteo = "SELECT COUNT(*) as total FROM medicamentos_detalle_inventario WHERE estatus = 0";
      $resultado_conteo = mysqli_query($conexion, $sql_conteo);
      $fila_conteo = mysqli_fetch_assoc($resultado_conteo);
      $total_permisos = $fila_conteo['total'];

      $total_paginas = ceil($total_permisos / $registros_por_pagina);
    ?>

    <section class="content-header">
      <h1>
        Inventario (<?php echo $total_registros; ?> Movimientos)
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-user"></i>Inventario</a></li>
        <li class="active"><a href="#"><i class="fa fa-book"></i>Listado</a></li>
      </ol>
    </section>

    <section class="content">
      <div style="padding-bottom: 10px;">
          <a href="inventario_ajustes.php?op=ajuste" class="btn-sm btn-warning pull-right"><i class="fa fa-cog"></i> Ajuste de Stock </a>
        <input type="text" placeholder="Buscar.." class="form-control pull-left" id="buscar" onkeyup="filtro()" style="width: 200px;">
      </div>
      <br><br>

      <div id="contenedorTabla">
        <table class="table table-sm table-hover mt-4" width="100%" id="t_user">
          <thead class="table-dark" style="background-color: #222; color: white; font-size: 12px;">
            <tr>
              <th>Responsable</th>
              <th>Medicamento</th>
              <th>Existencia</th>
              <th>Stock Mín/Máx</th>
              <th>N. Lote</th>
              <th>Cantidad</th>
              <th>Movimiento</th>       
              <th>Fecha</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody class="tbody" style="font-size: 12px;">
            <?php
            // 3. CONSULTA SQL CON PAGINACIÓN Y SUB-QUERY DE EXISTENCIA TOTAL
            $sql = "SELECT 
              m.nombre_medicamento,
              p.nombre,
              pm.tipo_presentacion,
              l.Lote,
              tm.nombre as tipo_nom,
              mdi.cantidad,
              mdi.stock_momento, -- LA COLUMNA NUEVA
              di.fecha,
              di.Id_detalle_inventario,
              dm.stock_minimo,
              dm.stock_maximo
            FROM medicamentos_detalle_inventario mdi
            INNER JOIN detalle_inventario di ON mdi.Id_detalle_inventario = di.Id_detalle_inventario
            INNER JOIN lotes_medicamentos l ON mdi.Id_lote = l.Id
            INNER JOIN descripcion_medicamento dm ON l.Id_descripcion_medicamento = dm.Id
            INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
            INNER JOIN presentacion pm ON dm.Id_presentacion = pm.Id_presentacion
            INNER JOIN tipo_movimiento tm ON di.Id_tipoMovimiento = tm.Id_tipo_movimiento
            INNER JOIN persona p ON di.Id_persona = p.Id
            WHERE mdi.estatus = 0
            ORDER BY di.fecha DESC
            LIMIT $inicio, $registros_por_pagina";

            $resultado = $conexion->query($sql);

            if ($resultado && $resultado->num_rows > 0) {
                while ($row = $resultado->fetch_assoc()) {
                    $clase_badge = (strcasecmp($row['tipo_nom'], 'Entrada') == 0) ? 'bg-green' : 'bg-crimson';
                    ?>
                    <tr>
                      <td><span class="text-row text-white"><?= htmlspecialchars($row['nombre']);?></span></td>
                      <td><span class="text-row text-white"><?= htmlspecialchars($row['nombre_medicamento'] . " (" . $row['tipo_presentacion'] . ")"); ?></span></td>
                      <td><span class="text-row text-white"><?= htmlspecialchars($row['stock_momento']); ?></span></td>
                      <td><span class="text-row text-white"><?= "Min: ".$row['stock_minimo']." / Max: ".$row['stock_maximo']; ?></span></td>
                      <td><span class="text-row text-white"><?= htmlspecialchars($row['Lote']); ?></span></td>
                      <td><span class="text-row text-white"><strong><?= $row['cantidad']; ?></strong></span></td>
                                            <td><span class="badge <?= $clase_badge; ?>"><?= htmlspecialchars($row['tipo_nom']); ?></span></td>
                      <td><span class="text-row text-white"><?= date("d/m/Y H:i", strtotime($row['fecha'])); ?></span></td>
                      <td>
                        <a href="../../cfg/redireccion_movimiento_editar.php?id=<?= $row['Id_detalle_inventario']; ?>" class="btn-sm btn-warning"><img src="../../recursos/imagenes/iconos/reactivar.png" style="width:15px; height:15px;"></a>
                        <a href="javascript:void(0);" onclick="confirmarEliminar(<?= $row['Id_detalle_inventario']; ?>)" class="btn-sm btn-danger"><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></a>
                      </td>
                    </tr>
                <?php }
            } else {
                echo '<tr><td colspan="8" class="text-center">No hay movimientos registrados.</td></tr>';
            }
            ?>
          </tbody>
        </table>
      </div>
      <nav aria-label="Page navigation" style="position: fixed; bottom:0;">
        <ul class="pagination">
          <?php
          $total_paginas = ceil($total_permisos / $registros_por_pagina);

          // --- CONFIGURACIÓN DE LA VENTANA ---
          $rango = 1; // Número de páginas a mostrar a los lados de la actual
          $inicio_ventana = max(1, $pagina_actual - $rango);
          $fin_ventana = min($total_paginas, $pagina_actual + $rango);

          // Ajuste para mostrar siempre al menos 3 botones si existen
          if ($pagina_actual == 1) $fin_ventana = min($total_paginas, 3);
          if ($pagina_actual == $total_paginas) $inicio_ventana = max(1, $total_paginas - 2);

          // Botón Primero y Anterior
          if ($pagina_actual > 1) : ?>
            <li><a href="?pagina=1" title="Primero">&laquo;&laquo;</a></li>
            <li><a href="?pagina=<?php echo ($pagina_actual - 1); ?>">&laquo;</a></li>
          <?php endif;

          // Números de la ventana
          for ($i = $inicio_ventana; $i <= $fin_ventana; $i++) : ?>
            <li class="<?php echo ($i == $pagina_actual) ? 'active' : ''; ?>">
              <a href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
          <?php endfor;

          // Botón Siguiente y Último
          if ($pagina_actual < $total_paginas) : ?>
            <li><a href="?pagina=<?php echo ($pagina_actual + 1); ?>">&raquo;</a></li>
            <li><a href="?pagina=<?php echo $total_paginas; ?>" title="Último">&raquo;&raquo;</a></li>
          <?php endif; ?>
        </ul>
      </nav>
    </section>

    <?php
    // Lógica de Modales de sesión (Basada en medico_listado.php)
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

    <div class="modal fade" id="modalExito" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header bg-green">
            <h5 class="modal-title" style="color: white;">Operación Exitosa</h5>
          </div>
          <div class="modal-body"><p><?= $mensaje_modal; ?></p></div>
          <div class="modal-footer">
            <button type="button" class="btn btn-success" data-dismiss="modal">Aceptar</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="modalError" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header bg-red">
            <h5 class="modal-title" style="color: white;">Error en la Operación</h5>
          </div>
          <div class="modal-body"><p><?= $mensaje_modal; ?></p></div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>

    <?php include('includes/footer.php'); ?>
  </div>

  <script>
    function confirmarEliminar(id) {
      if (confirm("¿Está seguro de eliminar este registro? Esto afectará el stock global.")) {
        window.location.href = '../../cfg/eliminar/eliminar_movimiento.php?id=' + id;
      }
    }

    $(document).ready(function() {
      <?php if ($mostrar_modal_exito): ?>
          $('#modalExito').modal('show');
      <?php elseif ($mostrar_modal_error): ?>
          $('#modalError').modal('show');
      <?php endif; ?>
    });
  </script>
</body>
</html>