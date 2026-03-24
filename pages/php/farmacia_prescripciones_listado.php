<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Farmacia | Lista de Recipes</title>
  <?php
  // Ajusta las rutas según tu estructura de carpetas real
  include('includes/headerNav2.php');
  include("../../cfg/conexion.php");
  ?>
</head>

<style>
  .badge-stock {
    font-size: 1.1em;
  }
</style>

<body class="hold-transition skin-blue sidebar-mini">
  <div class="content-wrapper">
    <?php
    include('../../cfg/conexion.php');
    $sqlRecipe = ("SELECT * FROM prescripcion_medicamentos ORDER BY Id ASC");
    $queryData   = mysqli_query($conexion, $sqlRecipe);
    $total_recipe = mysqli_num_rows($queryData);
    ?>
    <section class="content-header">
      <h1>Control de Despacho de Medicamentos (<?php echo $total_recipe; ?> Recipes)</h1>
    </section>

    <?php
    // --- LÓGICA DE PAGINACIÓN (Basada en medico_listado.php) ---
    $busqueda = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : '';
    $registros_por_pagina = 14;
    $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $inicio = ($pagina_actual - 1) * $registros_por_pagina;

    // 1. Definir el filtro base (Estado pendiente por defecto o según tu lógica)
    $donde = "WHERE pm.estatus != 'eliminado'"; // Ajusta según tus estados reales
    if ($busqueda != '') {
      $donde .= " AND (paciente.nombre LIKE '%$busqueda%' 
                     OR paciente.apellido LIKE '%$busqueda%' 
                     OR paciente.cedula LIKE '%$busqueda%' 
                     OR m.nombre_medicamento LIKE '%$busqueda%')";
    }

    // 2. Contar el total de registros FILTRADOS
    $sql_conteo = "SELECT COUNT(*) as total 
                   FROM prescripcion_medicamentos pm
                   INNER JOIN consulta c ON pm.Id_consulta = c.Id_consulta
                   INNER JOIN persona paciente ON c.Id_paciente = paciente.id
                   INNER JOIN descripcion_medicamento dm ON pm.Id_descripcion_medicamento = dm.Id
                   INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
                   $donde";
    $resultado_conteo = mysqli_query($conexion, $sql_conteo);
    $fila_conteo = mysqli_fetch_assoc($resultado_conteo);
    $total_registros = $fila_conteo['total'];
    $total_paginas = ceil($total_registros / $registros_por_pagina)
    ?>

    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="box box-primary">
            <div class="box-header with-border">
              <a href="farmacia_inventario_listado.php" class="btn-sm btn-primary pull-right"> <i class="fa fa-cubes"></i> Ver Inventario General</a>
              <input type="text" id="buscar" name="buscar" class="form-control" placeholder="Escriba para buscar..." value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>" autocomplete="off">
            </div>
          </div>
          <br><br>

          <div class="box-body">
            <div id="contenedorTabla">
              <table class="table table-sm table-hover mt-4" width="100%" id="t_user">
                <thead class="table-dark" style="background-color: #222; color: white; font-size: 12px;">
                  <tr>
                    <th>Fecha Consulta</th>
                    <th>Paciente</th>
                    <th>Médico Tratante</th>
                    <th>Medicamento Solicitado</th>
                    <th>Dosis Indicada</th>
                    <th class="text-center">Stock Disponible</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Acciones</th>
                  </tr>
                </thead>
                <tbody class="tbody" width="100%" style="font-size: 12px;">
                  <?php
                  // CONSULTA OPTIMIZADA (Relaciona Prescripción -> Consulta -> Paciente/Médico -> Stock)
                  $query = "SELECT 
                              -- Identificadores
                              pm.Id AS id_prescripcion,
                              pm.dosis,
                              pm.estatus AS estado_entrega,
                              pm.Id_descripcion_medicamento,
                              
                              -- Datos de la Consulta
                              c.fecha_consulta,
                              
                              -- Datos del Paciente
                              paciente.nombre AS nom_pac, 
                              paciente.apellido AS ape_pac, 
                              paciente.cedula AS cedula_pac,
                              rep.cedula AS cedula_representante,
                              
                              -- Datos del Médico
                              medico.nombre AS nom_med,
                              medico.apellido AS ape_med,

                              -- Datos del Medicamento
                              m.nombre_medicamento,
                              pres.tipo_presentacion,                 
                              TIMESTAMPDIFF(YEAR, paciente.fecha_nacimiento, CURDATE()) < 18 AS es_menor,
                              
                              -- Cálculo de Stock (Suma de lotes disponibles)
                              (SELECT IFNULL(SUM(es.cantidad_actual), 0) 
                               FROM existencias_stock es 
                               WHERE es.Id_descripcion_medicamento = pm.Id_descripcion_medicamento
                              ) AS stock_total

                            FROM prescripcion_medicamentos pm
                            INNER JOIN consulta c ON pm.Id_consulta = c.Id_consulta
                            INNER JOIN persona paciente ON c.Id_paciente = paciente.id
                            INNER JOIN persona medico ON c.Id_medico = medico.id
                            INNER JOIN descripcion_medicamento dm ON pm.Id_descripcion_medicamento = dm.Id
                            INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
                            LEFT JOIN presentacion pres ON dm.Id_presentacion = pres.Id_presentacion
                            -- Uniones para llegar al representante
                            LEFT JOIN detalle_paciente_menor dpm ON paciente.id = dpm.id_persona
                            LEFT JOIN persona rep ON dpm.id_representante = rep.id
                            $donde
                            -- FILTRO: Solo mostramos lo que falta por entregar
                            ORDER BY c.fecha_consulta ASC LIMIT $inicio, $registros_por_pagina";

                  $resultado = mysqli_query($conexion, $query);

                  while ($row = mysqli_fetch_assoc($resultado)) {
                    // Lógica visual para el Stock
                    $stock = $row['stock_total'];
                    $badgeClass = ($stock > 0) ? 'label-success' : 'label-danger';
                    $btnClass = ($stock > 0) ? 'btn-info' : 'btn-disabled';
                    $disabled = ($stock > 0) ? '' : 'disabled';
                  ?>
                    <tr>
                      <td><?php echo date('d/m/Y', strtotime($row['fecha_consulta'])); ?></td>

                      <td>
                        <strong><?php echo $row['nom_pac'] . " " . $row['ape_pac']; ?></strong><br>
                        <small>C.I: <?php echo $row['cedula_pac']; ?></small>
                      </td>

                      <td>
                        Dr/a. <?php echo $row['nom_med'] . " " . $row['ape_med']; ?>
                      </td>

                      <td>
                        <span class="text-blue"><?php echo $row['nombre_medicamento']; ?></span><br>
                        <small class="">(<?php echo $row['tipo_presentacion']; ?>)</small>
                      </td>

                      <td><?php echo $row['dosis']; ?></td>

                      <td class="text-center">
                        <span class="">
                          <?php echo $stock; ?>
                        </span>
                      </td>

                      <td class="text-center">
                        <?php
                        if ($row['estado_entrega'] == 'pendiente') {
                          echo '<span class="label label-warning">Pendiente</span>';
                        } else if ($row['estado_entrega'] == 'no entregado') {
                          echo '<span class="label label-default">No Entregado</span>';
                        } else {
                          echo '<span class="label label-success">Entregado</span>';
                        }
                        ?>
                      </td>

                      <td class="text-center">
                        <?php if ($row['estado_entrega'] == 'pendiente') : ?>
                          <?php
                          // Si es menor, enviamos la cédula del representante, si no, la del paciente
                          $cedula_a_enviar = ($row['es_menor'] == 1) ? $row['cedula_representante'] : $row['cedula_pac'];
                          ?>

                          <a href="farmacia_inventario_movimiento_salida.php?id_pres=<?php echo $row['id_prescripcion']; ?>&id_med=<?php echo $row['Id_descripcion_medicamento']; ?>&pac=<?php echo urlencode($cedula_a_enviar); ?>&menor=<?php echo $row['es_menor']; ?>&from=prescripciones" class="btn <?php echo $btnClass; ?> btn-sm" <?php echo $disabled; ?> title="Despachar Medicamento">
                            <img src="../../recursos/imagenes/iconos/enviar.png" style="width:15px; height:15px;">
                          </a>
                          <button onclick="cambiarEstado(<?php echo $row['id_prescripcion'] ?>, 'no entregado')" class="btn btn-sm btn-danger btn-accion-rapida" title="Cancelar"><img src="../../recursos/imagenes/iconos/cancelar.png" style="width:15px; height:15px;"></button>
                        <?php else : ?>
                          <a href="farmacia_prescripciones_ver.php?id=<?php echo $row['id_prescripcion']; ?>" class="btn <?php echo $btnClass; ?> btn-sm" <?php echo $disabled; ?> title="Ver Informarcion">
                            <img src="../../recursos/imagenes/iconos/info.png" style="width:15px; height:15px;">
                          </a>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
  </div>
  <nav aria-label="Page navigation" style="position: fixed; bottom:0;">
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


  <script>
    function cambiarEstado(id, nuevoEstado) {
      if (confirm('¿Confirmar cambio a ' + nuevoEstado + '?')) {
        $.post('../../cfg/ajax/actualizar_estado_receta.php', {
          id: id,
          estado_entrega: nuevoEstado
        }, function(data) {
          if (data.trim() == 'ok') {
            location.reload();
          } else {
            alert('Error al actualizar');
          }
        });
      }
    }
  </script>

  <?php
  include("includes/footer.php");
  ?>

</body>

</html>