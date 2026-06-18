<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Farmacia | Lista de Recipes</title>
  <?php
  include('includes/headerNav2.php');
  include("../../cfg/conexion.php");
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
  #DesactivarPrescripcion,
  #ModalReportePrescripcion,
  #modalConfirmarEstado,
  #modalListadoEstados {
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
    $sql_base = "
        SELECT 
            'Interna' AS tipo_receta,
            c.Id_consulta AS id_prescripcion,
            CASE 
                WHEN SUM(CASE WHEN pm.estado_prescripcion = 'cancelado' THEN 1 ELSE 0 END) > 0 THEN 'Cancelado'
                WHEN SUM(CASE WHEN pm.estado_prescripcion = 'entregado' THEN 1 ELSE 0 END) = COUNT(pm.Id) THEN 'Entregado'
                WHEN SUM(CASE WHEN pm.estado_prescripcion = 'entregado' THEN 1 ELSE 0 END) > 0 THEN 'Parcial'
                ELSE 'Pendiente'
            END AS estado_entrega,
            c.fecha_consulta AS fecha_solicitud,
            paciente.nombre AS nom_pac, 
            paciente.apellido AS ape_pac,
            paciente.tipo_cedula AS tipo_cedula_pac, 
            paciente.cedula AS cedula_pac,
            rep.cedula AS cedula_representante,
            medico.nombre AS nom_med,
            medico.apellido AS ape_med,
            GROUP_CONCAT(CONCAT('• ', m.nombre_medicamento) SEPARATOR '<br>') AS nombre_medicamento,
            TIMESTAMPDIFF(YEAR, paciente.fecha_nacimiento, CURDATE()) < 18 AS es_menor
        FROM consulta c
        INNER JOIN prescripcion_medicamentos pm ON c.Id_consulta = pm.Id_consulta
        INNER JOIN persona paciente ON c.Id_paciente = paciente.id
        INNER JOIN detalle_medico dmd ON c.Id_medico = dmd.Id_detalle_medico
        INNER JOIN persona medico ON dmd.Id_persona = medico.id
        INNER JOIN descripcion_medicamento dm ON pm.Id_descripcion_medicamento = dm.Id
        INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
        LEFT JOIN presentacion p ON dm.Id_presentacion = p.Id_presentacion
        LEFT JOIN (
            SELECT 
                dpm.id_medicamento as id_desc, 
                GROUP_CONCAT(CONCAT(IFNULL(pa.nombre,''), ' ', IFNULL(dpm.cantidad_unidad_medida,''), ' ', IFNULL(um.unidad,'')) SEPARATOR ' + ') AS componentes
            FROM detalle_principio_medicamento dpm
            LEFT JOIN principio_activo pa ON dpm.id_principio_activo = pa.Id_principio_activo
            LEFT JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida
            GROUP BY dpm.id_medicamento
        ) comp_tbl ON dm.Id = comp_tbl.id_desc
        LEFT JOIN detalle_paciente_menor dpm_menor ON paciente.id = dpm_menor.id_persona
        LEFT JOIN persona rep ON dpm_menor.id_representante = rep.id
        WHERE pm.estatus = 1
        GROUP BY c.Id_consulta

        UNION ALL

        SELECT 
            'Externa' AS tipo_receta,
            sm.id_solicitud AS id_prescripcion,
            sm.estatus_general AS estado_entrega,
            DATE(sm.fecha_solicitud) AS fecha_solicitud,
            paciente.nombre AS nom_pac,
            paciente.apellido AS ape_pac,
            paciente.tipo_cedula AS tipo_cedula_pac,
            paciente.cedula AS cedula_pac,
            rep.cedula AS cedula_representante,
            medico.nombre AS nom_med,
            medico.apellido AS ape_med,
            GROUP_CONCAT(CONCAT('• ', m.nombre_medicamento, ' (Cant: ', ds.cantidad_recetada, ')') SEPARATOR '<br>') AS nombre_medicamento,
            TIMESTAMPDIFF(YEAR, paciente.fecha_nacimiento, CURDATE()) < 18 AS es_menor
        FROM solicitud_medicamento sm
        INNER JOIN detalle_solicitud ds ON sm.id_solicitud = ds.id_solicitud
        INNER JOIN descripcion_medicamento dm ON ds.id_medicamento = dm.Id
        INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
        INNER JOIN persona paciente ON sm.id_paciente = paciente.id
        INNER JOIN detalle_medico dmd ON sm.id_medico = dmd.Id_detalle_medico
        INNER JOIN persona medico ON dmd.Id_persona = medico.id
        LEFT JOIN presentacion p ON dm.Id_presentacion = p.Id_presentacion
        LEFT JOIN (
            SELECT 
                dpm.id_medicamento as id_desc, 
                GROUP_CONCAT(CONCAT(IFNULL(pa.nombre,''), ' ', IFNULL(dpm.cantidad_unidad_medida,''), ' ', IFNULL(um.unidad,'')) SEPARATOR ' + ') AS componentes
            FROM detalle_principio_medicamento dpm
            LEFT JOIN principio_activo pa ON dpm.id_principio_activo = pa.Id_principio_activo
            LEFT JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida
            GROUP BY dpm.id_medicamento
        ) comp_tbl ON dm.Id = comp_tbl.id_desc
        LEFT JOIN detalle_paciente_menor dpm_menor ON paciente.id = dpm_menor.id_persona
        LEFT JOIN persona rep ON dpm_menor.id_representante = rep.id
        WHERE sm.origen = 'Externo'
        GROUP BY sm.id_solicitud
    ";

    // --- LÓGICA DE PAGINACIÓN Y FILTROS ---
    $busqueda = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : '';
    $registros_por_pagina = 14;
    $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $inicio = ($pagina_actual - 1) * $registros_por_pagina;

    $donde = " WHERE 1=1";
    if ($busqueda != '') {
      $donde .= " AND (nom_pac LIKE '%$busqueda%' 
                     OR ape_pac LIKE '%$busqueda%' 
                     OR CONCAT(nom_pac, ' ', ape_pac) LIKE '%$busqueda%'
                     OR cedula_pac LIKE '%$busqueda%' 
                     OR nombre_medicamento LIKE '%$busqueda%')";
    }

    // Contar el total de registros FILTRADOS unificados
    $sql_conteo = "SELECT COUNT(*) as total FROM ($sql_base) AS base_unificada $donde";
    $resultado_conteo = mysqli_query($conexion, $sql_conteo);
    $fila_conteo = mysqli_fetch_assoc($resultado_conteo);
    $total_registros = $fila_conteo['total'];
    $total_paginas = ceil($total_registros / $registros_por_pagina);

    // --- CONTEO PARA LOS BOTONES DE ESTADO ---
    $pendientes = $completadas = $parciales = $canceladas = 0;
    $sql_totales = "SELECT estado_entrega, COUNT(*) as total FROM ($sql_base) AS base_unificada GROUP BY estado_entrega";
    $res_totales = mysqli_query($conexion, $sql_totales);

    while ($tot = mysqli_fetch_assoc($res_totales)) {
      $estado = strtolower($tot['estado_entrega']);

      // Corregido: Usamos += para acumular y reparamos la lógica de las condiciones condicionales
      if ($estado == 'pendiente') {
        $pendientes += $tot['total'];
      } elseif ($estado == 'entregado' || $estado == 'completado' || $estado == 'completada') {
        $completadas += $tot['total'];
      } elseif ($estado == 'parcial' || $estado == 'parcialmente entregado') {
        $parciales += $tot['total'];
      } elseif ($estado == 'cancelado' || $estado == 'no entregado') {
        $canceladas += $tot['total'];
      }
    }
    ?>

    <section class="content-header">
      <h1>Control de Despacho de Medicamentos (<?php echo $total_registros; ?> Récipes)</h1>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="box box-primary">
            <div class="box-header with-border">
              <p class="pull-right" style="width:5px;"></p>
              <a href="farmacia_inventario_listado.php" class="btn-sm btn-primary pull-right"> Ver Inventario General</a>

              <p class="pull-right" style="width:5px;"></p>
              <form method="GET" action="" style="display:inline;">
                <input type="text" id="buscar" name="buscar" class="form-control" placeholder="Escriba para buscar..." value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>" autocomplete="off" style="width:250px; display:inline-block;">
              </form>
        
              <p class="pull-right" style="width:5px;"></p>
              <button class="btn-sm btn-primary pull-right btn-sm btn-abrir-modal-estado" data-estado="Cancelado">Canceladas (<?php echo $canceladas; ?>)</button>      
              <p class="pull-right" style="width:5px;"></p>
              <button class="btn-sm btn-primary pull-right btn-sm btn-abrir-modal-estado" data-estado="Parcial">Parciales (<?php echo $parciales; ?>)</button>
              <p class="pull-right" style="width:5px;"></p>
              <button class="btn-sm btn-primary pull-right btn-sm btn-abrir-modal-estado" data-estado="Pendiente">Pendientes (<?php echo $pendientes; ?>)</button>
              <p class="pull-right" style="width:5px;"></p>
              <button class="btn-sm btn-primary pull-right btn-sm btn-abrir-modal-estado" data-estado="Entregado">Completadas (<?php echo $completadas; ?>)</button>
            </div>
          </div>
          <br><br>

          <div class="box-body">
            <div id="contenedorTabla">
              <table class="table table-sm table-hover mt-4" width="100%" id="t_user">
                <thead class="table-dark" style="background-color: #222; color: white; font-size: 12px;">
                  <tr>
                    <th>Fecha Consulta</th>
                    <th>Paciente / Tipo</th>
                    <th>Médico Tratante</th>
                    <th>Medicamento Solicitado</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Acciones</th>
                  </tr>
                </thead>
                <tbody class="tbody" width="100%" style="font-size: 12px;">
                  <?php
                  // ==============================================================================
                  // 2. CONSULTA FINAL PARA OBTENER LOS DATOS Y STOCK
                  // ==============================================================================
                  $query = "
                    SELECT base_unificada.*
                    FROM ($sql_base) AS base_unificada
                    $donde
                    ORDER BY fecha_solicitud DESC 
                    LIMIT $inicio, $registros_por_pagina
                  ";

                  $resultado = mysqli_query($conexion, $query);

                  while ($row = mysqli_fetch_assoc($resultado)) {

                    // Definir etiqueta del tipo de receta
                    if ($row['tipo_receta'] === 'Interna') {
                      $etiquetaTipo = ($row['es_menor'] == 1) ? '<span class="label label-info">Interna - Rep.</span>' : '<span class="label label-primary">Interna - Pac.</span>';
                    } else {
                      $etiquetaTipo = '<span class="label label-warning" style="background-color:#f39c12;">Externa</span>';
                    }
                  ?>
                    <tr>
                      <td><?php echo date('d/m/Y', strtotime($row['fecha_solicitud'])); ?></td>

                      <td>
                        <?php echo $etiquetaTipo; ?><br>
                        <strong><?php echo htmlspecialchars(trim($row['nom_pac'] . " " . $row['ape_pac'])); ?></strong><br>
                        <small><?php echo htmlspecialchars($row['tipo_cedula_pac']); ?>-<?php echo htmlspecialchars($row['cedula_pac']); ?></small>
                      </td>

                      <td>
                        Dr/a. <?php echo htmlspecialchars(trim($row['nom_med'] . " " . $row['ape_med'])); ?>
                      </td>

                      <td>
                        <span class="text-blue"><?php echo $row['nombre_medicamento']; ?></span><br>
                      </td>


                      <td class="text-center">
                        <?php
                        if ($row['estado_entrega'] == 'pendiente' || $row['estado_entrega'] == 'Pendiente') {
                          echo '<span class="badge bg-yellow">Pendiente</span>';
                        } else if ($row['estado_entrega'] == 'Parcial' || $row['estado_entrega'] == 'Parcialmente Entregado') {
                          echo '<span class="badge bg-aqua">Parcial</span>';
                        } else if ($row['estado_entrega'] == 'no entregado' || $row['estado_entrega'] == 'No entregado') {
                          echo '<span class="badge bg-default">No Entregado</span>';
                        } else if ($row['estado_entrega'] == 'Cancelado') {
                          echo '<span class="badge bg-crimson">Cancelado</span>';
                        } else {
                          echo '<span class="badge bg-green">Entregado</span>';
                        }
                        ?>
                      </td>

                      <?php if (in_array('Gestionar acciones de recetas', $_SESSION["permisos"])) : ?>
                        <td class="text-center">
                          <?php if ($row['estado_entrega'] == 'pendiente' || $row['estado_entrega'] == 'Parcialmente Entregado' || $row['estado_entrega'] == 'Parcial' || $row['estado_entrega'] == 'Pendiente') : ?>
                            <?php
                            $cedula_a_enviar = ($row['es_menor'] == 1 && !empty($row['cedula_representante'])) ? $row['cedula_representante'] : $row['cedula_pac'];
                            ?>
                            <?php if (in_array('Ver informacion de recetas', $_SESSION["permisos"])) : ?>
                              <a href="farmacia_prescripciones_ver.php?id=<?php echo $row['id_prescripcion']; ?>&tipo=<?php echo $row['tipo_receta']; ?>" class="btn btn-info btn-sm" title="Ver Informacion">
                                <img src="../../recursos/imagenes/iconos/info.png" style="width:15px; height:15px;">
                              </a>
                            <?php endif; ?>
                            <?php if (in_array('Generar Despacho de Inventario', $_SESSION["permisos"])) : ?>
                              <a href="farmacia_inventario_movimiento_despacho.php?id_pres=<?php echo $row['id_prescripcion']; ?>&pac=<?php echo urlencode($cedula_a_enviar); ?>&menor=<?php echo $row['es_menor']; ?>&tipo=<?php echo $row['tipo_receta']; ?>&from=prescripciones" class="btn btn-success btn-sm" title="Despachar Receta">
                                <img src="../../recursos/imagenes/iconos/enviar.png" style="width:15px; height:15px;">
                              </a>
                            <?php endif; ?>
                            <?php if (in_array('Cancelar Recetas', $_SESSION["permisos"])) : ?>
                              <button onclick="cambiarEstado(<?php echo $row['id_prescripcion'] ?>, 'no entregado', '<?php echo $row['tipo_receta'] ?>')" class="btn btn-sm btn-danger btn-accion-rapida" title="Cancelar">
                                <img src="../../recursos/imagenes/iconos/cancelar.png" style="width:15px; height:15px;">
                              </button>
                            <?php endif; ?>

                          <?php elseif ($row['estado_entrega'] == 'no entregado' || $row['estado_entrega'] == 'Cancelado') : ?>
                            <?php if (in_array('Ver informacion de recetas', $_SESSION["permisos"])) : ?>
                              <a href="farmacia_prescripciones_ver.php?id=<?php echo $row['id_prescripcion']; ?>&tipo=<?php echo $row['tipo_receta']; ?>" class="btn btn-info btn-sm" title="Ver Informacion">
                                <img src="../../recursos/imagenes/iconos/info.png" style="width:15px; height:15px;">
                              </a>
                            <?php endif; ?>
                          <?php else : ?>
                            <?php if (in_array('Ver informacion de recetas', $_SESSION["permisos"])) : ?>
                              <a href="farmacia_prescripciones_ver.php?id=<?php echo $row['id_prescripcion']; ?>&tipo=<?php echo $row['tipo_receta']; ?>" class="btn btn-info btn-sm" title="Ver Informacion">
                                <img src="../../recursos/imagenes/iconos/info.png" style="width:15px; height:15px;">
                              </a>
                            <?php endif; ?>
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

  <div class="modal" id="modalConfirmarEstado" tabindex="-1" role="dialog" aria-labelledby="modalLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: #dc3545; color: white;">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="modalLabel">Confirmar Acción</h4>
        </div>
        <div class="modal-body">
          <p id="mensajeModal">¿Está seguro de que desea cambiar el estado de esta receta?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
          <button type="button" id="btnConfirmarEstado" class="btn btn-danger">Aceptar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalListadoEstados" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document" style="width: 90%;">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:white;"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="tituloModalEstados" style="color:white;">Recetas</h4>
        </div>
        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
          <div id="loader-modal-estados" class="text-center" style="display:none;">
            <i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>
            <p>Cargando recetas...</p>
          </div>
          <div id="contenido-modal-estados">
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    $(document).ready(function() {
      // Se añade el parámetro tipoReceta para enviar por AJAX
      // Variables para almacenar temporalmente los datos antes de confirmar
      let datosEstadoTemp = {
        id: null,
        nuevoEstado: null,
        tipoReceta: null
      };

      window.cambiarEstado = function(id, nuevoEstado, tipoReceta) {
        // 1. Guardamos los datos para usarlos al confirmar
        datosEstadoTemp = {
          id: id,
          nuevoEstado: nuevoEstado,
          tipoReceta: tipoReceta
        };

        // 2. Personalizamos el mensaje del modal
        $('#mensajeModal').text('¿Está seguro de que desea cambiar el estado a "' + nuevoEstado + '"?');

        // 3. Abrimos el modal
        $('#modalConfirmarEstado').modal('show');
      };

      // Abrir modal de estados
      $('.btn-abrir-modal-estado').on('click', function() {
        var estado = $(this).data('estado');
        $('#tituloModalEstados').text('Recetas - Estado: ' + estado);
        $('#contenido-modal-estados').empty();
        $('#loader-modal-estados').show();
        $('#modalListadoEstados').modal('show');

        $.ajax({
          url: '../../cfg/ajax/get_recetas_por_estado.php',
          type: 'GET',
          data: {
            estado: estado
          },
          success: function(response) {
            $('#loader-modal-estados').hide();
            $('#contenido-modal-estados').html(response);
          },
          error: function() {
            $('#loader-modal-estados').hide();
            $('#contenido-modal-estados').html('<div class="alert alert-danger">Error al cargar los datos.</div>');
          }
        });
      });

      // Escuchador para el botón "Aceptar" dentro del modal
      $(document).ready(function() {
        $('#btnConfirmarEstado').click(function() {
          // Deshabilitar botón para evitar múltiples clics
          const btn = $(this);
          btn.prop('disabled', true);

          // Ejecutar la petición AJAX
          $.post('../../cfg/ajax/actualizar_estado_receta.php', {
            id: datosEstadoTemp.id,
            estado_entrega: datosEstadoTemp.nuevoEstado,
            tipo: datosEstadoTemp.tipoReceta
          }, function(data) {
            if (data.trim() == 'ok') {
              location.reload(); // Recarga exitosa
            } else {
              alert('Error al actualizar: ' + data);
              btn.prop('disabled', false); // Rehabilitar en caso de error
              $('#modalConfirmarEstado').modal('hide');
            }
          }).fail(function() {
            alert('Error de conexión con el servidor');
            btn.prop('disabled', false);
            $('#modalConfirmarEstado').modal('hide');
          });
        });
      });

      function closeCustomModal(modalElement) {
        modalElement.removeClass('in').addClass('out');
        setTimeout(() => {
          modalElement.modal('hide').removeClass('out');
        }, 100);
      }

      $('#modalConfirmarEstado .close, #modalConfirmarEstado .btn-default').on('click', function() {
        closeCustomModal($('#modalConfirmarEstado'));
      });

      $('.reporte').on('click', function(e) {
        e.preventDefault();
        $('#ModalReportePrescripcion').modal('show');
      });

      $('#ModalReportePrescripcion .close, #ModalReportePrescripcion .btn-second').on('click', function() {
        closeCustomModal($('#ModalReportePrescripcion'));
      });

      $('#btnEjecutarReportePrescripcion').on('click', function() {
        var tipo = $('#tipo_reporte_Prescripcion').val();
        window.open('../../cfg/reportes/generar_pdf_Prescripciones.php?tipo=' + tipo, '_blank');
        $('#ModalReportePrescripcion').modal('hide');
      });

      // Se actualiza el botón desactivar para que envíe el origen/tipo en la URL
      $(document).on('click', '.btn-desactivar', function(e) {
        e.preventDefault();
        var IdPrescripcion = $(this).data('id');
        var tipoReceta = $(this).data('tipo');
        var urlDesactivar = "../../cfg/desactivar/desactivar_prescripcion.php?Id=" + IdPrescripcion + "&tipo=" + tipoReceta;
        $('#desactivar').attr('href', urlDesactivar);
        $('#DesactivarPrescripcion').modal('show');
      })

      <?php if ($mostrar_modal_exito) : ?>
        $('#modalExito').modal('show');
      <?php elseif ($mostrar_modal_error) : ?>
        $('#modalError').modal('show');
      <?php endif; ?>
    });
  </script>

  <?php
  include("includes/footer.php");
  ?>

</body>

</html>