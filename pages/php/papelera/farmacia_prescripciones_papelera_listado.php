<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Papelera | Lista de Recipes</title>
  <?php
  include('../includes/headerPapelera.php');
  include("../../../cfg/conexion.php");
  ?>
</head>

<style>
  /* ---------------------------------------------------------------------- */
  /* ANIMACIONES Y ESTILOS DE MODALES */
  /* ---------------------------------------------------------------------- */
  @keyframes pulse-opacity {
    0% {
      opacity: 0;
    }

    100% {
      opacity: 1;
    }
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(-50px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  @keyframes fadeOut {
    from {
      opacity: 1;
      transform: translateY(0);
    }

    to {
      opacity: 0;
      transform: translateY(-50px);
    }
  }

  .modal.in .modal-dialog,
  #ReactivarPrescripcion,
  #EliminarPrescripcion {
    animation: fadeIn 0.4s ease-out;
  }

  .modal.out .modal-dialog {
    animation: fadeOut 0.4s ease-in;
  }

  /* El backdrop usa pulse-opacity */
  .modal-open .modal-backdrop {
    opacity: 0.7 !important;
    animation: pulse-opacity 0.3s forwards;
  }

  .badge-stock {
    font-size: 1.1em;
  }
</style>

<body class="hold-transition skin-blue sidebar-mini">
  <div class="content-wrapper">
    <?php
    include('../../../cfg/conexion.php');
    $sqlRecipe = ("SELECT * FROM prescripcion_medicamentos WHERE estatus = 0 ORDER BY Id ASC");
    $queryData   = mysqli_query($conexion, $sqlRecipe);
    $total_recipe = mysqli_num_rows($queryData);
    ?>
    <section class="content-header">
      <h1>Total de Recipes Inactivos (<?php echo $total_recipe; ?>)</h1>
    </section>

    <?php
    // --- LÓGICA DE PAGINACIÓN (Basada en medico_listado.php) ---
    $busqueda = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : '';
    $registros_por_pagina = 14;
    $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $inicio = ($pagina_actual - 1) * $registros_por_pagina;

    // 1. Definir el filtro base (Estado pendiente por defecto o según tu lógica)
    $donde = "WHERE pm.estatus = 0"; // Ajusta según tus estados reales
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
              <a href="../farmacia_prescripciones_listado.php" class="btn-sm btn-primary pull-right"> Regresar al Listado </a>
              <input type="text" id="buscar" name="buscar" class="form-control" placeholder="Escriba para buscar..." value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>" style="border-radius:0; height:10%; width:250px; display:inline-block;" autocomplete="off">
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
                              pm.estado_prescripcion AS estado_entrega,
                              pm.Id_descripcion_medicamento,
                              
                              -- Datos de la Consulta
                              c.fecha_consulta,
                              
                              -- Datos del Paciente
                              paciente.nombre AS nom_pac, 
                              paciente.apellido AS ape_pac,
                              paciente.tipo_cedula AS tipo_cedula_pac, 
                              paciente.cedula AS cedula_pac,
                              rep.cedula AS cedula_representante,
                              
                              -- Datos del Médico
                              medico.nombre AS nom_med,
                              medico.apellido AS ape_med,

                              -- Datos del Medicamento
                              m.nombre_medicamento,
                              p.nombre_presentacion,       
                              GROUP_CONCAT(CONCAT(IFNULL(pa.nombre,''), ' ', IFNULL(dpmc.cantidad_unidad_medida,''), IFNULL(um.unidad,'')) SEPARATOR ' + ') AS componentes,          
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
                            LEFT JOIN presentacion p ON dm.Id_presentacion = p.Id_presentacion
                            LEFT JOIN detalle_principio_medicamento dpmc ON dm.Id = dpmc.id_medicamento
                            LEFT JOIN unidad_medida um ON dpmc.id_tipo_unidad_medida = um.Id_unidad_medida
                            LEFT JOIN principio_activo pa ON dpmc.id_principio_activo = pa.Id_principio_activo
                            -- Uniones para llegar al representante
                            LEFT JOIN detalle_paciente_menor dpm ON paciente.id = dpm.id_persona
                            LEFT JOIN persona rep ON dpm.id_representante = rep.id
                            $donde
                            -- FILTRO: Solo mostramos lo que falta por entregar
                            GROUP BY pm.Id -- <--- ESTA LÍNEA ES VITAL
                            ORDER BY c.fecha_consulta ASC LIMIT $inicio, $registros_por_pagina";

                  $resultado = mysqli_query($conexion, $query);

                  while ($row = mysqli_fetch_assoc($resultado)) {
                    // Lógica visual para el Stock
                    $stock = $row['stock_total'];
                    $badgeClass = ($stock > 0) ? 'label-success' : 'label-danger';
                    $btnClass = ($stock > 0) ? 'btn-success' : 'btn-disabled';
                    $disabled = ($stock > 0) ? '' : 'disabled';
                  ?>
                    <tr>
                      <td><?php echo date('d/m/Y', strtotime($row['fecha_consulta'])); ?></td>

                      <td>
                        <strong><?php echo $row['nom_pac'] . " " . $row['ape_pac']; ?></strong><br>
                        <small><?php echo $row['tipo_cedula_pac']; ?>-<?php echo $row['cedula_pac']; ?></small>
                      </td>

                      <td>
                        Dr/a. <?php echo $row['nom_med'] . " " . $row['ape_med']; ?>
                      </td>

                      <td>
                        <span class="text-blue"><?= htmlspecialchars($row['nombre_medicamento'] . " (" . $row['componentes'] . ")"); ?></span><br>
                      </td>

                      <td class="text-center">
                        <?php
                        if ($row['estado_entrega'] == 'pendiente') {
                          echo '<span class="badge bg-yellow">Pendiente</span>';
                        } else if ($row['estado_entrega'] == 'no entregado') {
                          echo '<span class="badge bg-default">No Entregado</span>';
                        } else {
                          echo '<span class="badge bg-green">Entregado</span>';
                        }
                        ?>
                      </td>

                      <?php if (in_array('Gestionar acciones de medicamentos', $_SESSION["permisos"])) : ?>
                        <td>
                          <?php if (in_array('Reactivar Medicamentos', $_SESSION["permisos"])) : ?>
                            <a href="#" data-id="<?php echo $row['id_prescripcion'] ?>" class="btn-sm btn-success btn-reactivar" title="Reactivar"><img src="../../../recursos/imagenes/iconos/reactivar.png" style="width:15px; height:15px;"></a>
                          <?php endif; ?>
                          <?php if (in_array('Eliminar Medicamentos', $_SESSION["permisos"])) : ?>
                            <a href="#" data-id="<?php echo $row['id_prescripcion'] ?>" class="btn-sm btn-danger btn-eliminar" title="Eliminar"><img src="../../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></a>
                          <?php endif; ?>
                        </td>
                      <?php endif; ?>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
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
  </div>
  </div>

  <?php
  // Lógica para preparar los mensajes de los modales de sesión
  $mostrar_modal_exito = false;
  $mostrar_modal_error = false;
  $mensaje_modal = '';

  // Usar las variables consistentes: 'mensaje_user_exito' y 'mensaje_user_error'
  if (isset($_SESSION['mensaje_user_exito'])) {
    $mostrar_modal_exito = true;
    $mensaje_modal = $_SESSION['mensaje_user_exito'];
    unset($_SESSION['mensaje_user_exito']); // Limpiar la sesión
  } elseif (isset($_SESSION['mensaje_user_error'])) {
    $mostrar_modal_error = true;
    $mensaje_modal = $_SESSION['mensaje_user_error'];
    unset($_SESSION['mensaje_user_error']); // Limpiar la sesión
  }
  ?>

  <div class="modal fade" id="modalExito" tabindex="-1" role="dialog" aria-labelledby="modalExitoLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-green">
          <h5 class="modal-title" id="modalExitoLabel" style="color: white;">Operación Exitosa</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
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

  <div class="modal" id="ReactivarPrescripcion" tabindex="-1" role="dialog" aria-labelledby="ReactivarPrescripcionLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: #dc3545; color: white;">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="ReactivarPrescripcionLabel">Confirmar Desactivacion</h4>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea reactivar esta prescripcion?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <a href="#" class="btn btn-danger" id="reactivar">Aceptar</a>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="EliminarPrescripcion" tabindex="-1" role="dialog" aria-labelledby="EliminarPrescripcionLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: #dc3545; color: white;">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="EliminarPrescripcionLabel">Confirmar Desactivacion</h4>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea eliminar esta prescripcion? Esta acción no se puede deshacer.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <a href="#" class="btn btn-danger" id="eliminar">Aceptar</a>
        </div>
      </div>
    </div>
  </div>

  <script>
    $(document).ready(function() {
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

      function closeCustomModal(modalElement) {
        modalElement.removeClass('in').addClass('out');
        setTimeout(() => {
          modalElement.modal('hide').removeClass('out');
        }, 100); // Duración de la animación
      }

      // CORRECCIÓN: Eventos para cerrar el modal de aviso
      $('#ReactivarPrescripcion .close, #ReactivarPrescripcion .btn-second').on('click', function() {
        closeCustomModal($('#ReactivarPrescripcion'));
      });

      $('#EliminarPrescripcion .close, #EliminarPrescripcion .btn-second').on('click', function() {
        closeCustomModal($('#EliminarPrescripcion'));
      });

      $(document).on('click', '.btn-reactivar', function(e) {
        e.preventDefault();
        var IdPrescripcion = $(this).data('id');
        var urlReactivar = "../../../cfg/reactivar/reactivar_prescripcion.php?Id=" + IdPrescripcion;
        $('#reactivar').attr('href', urlReactivar);
        $('#ReactivarPrescripcion').modal('show');
      })

      $(document).on('click', '.btn-eliminar', function(e) {
        e.preventDefault();
        var IdPrescripcion = $(this).data('id');
        var urlEliminar = "../../../cfg/eliminar/eliminar_prescripcion.php?Id=" + IdPrescripcion;
        $('#eliminar').attr('href', urlEliminar);
        $('#EliminarPrescripcion').modal('show');
      })

      <?php if ($mostrar_modal_exito) : ?>
        $('#modalExito').modal('show');
      <?php elseif ($mostrar_modal_error) : ?>
        $('#modalError').modal('show');
      <?php endif; ?>
    });
  </script>

  <?php
  include("../includes/footer.php");
  ?>

</body>

</html>