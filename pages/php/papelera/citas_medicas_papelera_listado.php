<?php
include('../includes/headerPapelera.php');
verificarAcceso('Ver papelera de citas');
?>

<head>
  <title>Citas Médicas | Papelera</title>

  <div class="content-wrapper">
    <?php
    include('../../../cfg/conexion.php');
    $sqlCita = ("SELECT * FROM citas WHERE estatus = 0 ORDER BY Id_cita ASC");
    $queryData   = mysqli_query($conexion, $sqlCita);
    $total_cita = mysqli_num_rows($queryData);
    ?>

    <section class="content-header">
      <h1>
        Citas Médicas Inactivas (<?php echo $total_cita; ?>)
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-user"></i>Citas Médicas</a></li>
        <li><a href="#"><i class="fa fa-book"></i>Papelera</a></li>
      </ol>
    </section>

    <section class="content">
      <div style="padding-bottom: 10px;">
        <a href="../citas_medicas_listado.php" class="btn-sm btn-primary pull-right"><i class="fa fa-book"></i> Regresar al Listado </a>
        <div class="pull-left form-inline">
          <form method="GET" action="" id="formBusquedaRapida">
            <input type="text" id="buscar" name="buscar" class="form-control" placeholder="Escriba para buscar..." value="" style="border-radius:0; height:10%; width:250px; display:inline-block;" autocomplete="off">
          </form>
        </div>
        <span data-toggle="tooltip" data-placement="right" title="Filtros avanzados de búsqueda">
          <button type="button" class="btn-sm btn-primary btn-sm pull-left" data-toggle="modal" data-target="#modalBusquedaAvanzada">
            <i class="fa fa-filter"><img src="../../../recursos/imagenes/iconos/filtrar.png" style="width:10px; height:10px; filter:invert(1);" title="filtrar consulta"></i>
          </button>
        </span>
        <br><br>
        <div id="contenedorTabla">
          <table class="table table-sm table-hover mt-4" width="100%" height="20" id="t_user">
            <thead class="table-dark" style="background-color: #222; color: white; font-size: 12px;">
              <th>Paciente</th>
              <th>Médico</th>
              <th>Fecha y Hora</th>
              <th>Estado</th>
              <?php if (in_array('Gestionar acciones de citas', $_SESSION["permisos"])) : ?>
                <th>Acciones</th>
              <?php endif; ?>
            </thead>
            <tbody class="tbody" width="100%" style="font-size: 12px;">
              <tr>
                <?php
                $busqueda = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : '';
                $f_desde  = isset($_GET['f_desde']) ? mysqli_real_escape_string($conexion, $_GET['f_desde']) : '';
                $f_hasta  = isset($_GET['f_hasta']) ? mysqli_real_escape_string($conexion, $_GET['f_hasta']) : '';
                $f_estado = isset($_GET['f_estado']) ? mysqli_real_escape_string($conexion, $_GET['f_estado']) : '';

                $registros_por_pagina = 14;
                $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
                $inicio = ($pagina_actual - 1) * $registros_por_pagina;

                $donde = "WHERE c.estatus = 0";

                if ($busqueda != '') {
                  $donde .= " AND (p.nombre LIKE '%$busqueda%' 
                OR p.apellido LIKE '%$busqueda%' 
                OR p.cedula LIKE '%$busqueda%' 
                OR c.motivo LIKE '%$busqueda%'
                OR m.nombre LIKE '%$busqueda%'
                OR m.apellido LIKE '%$busqueda%')";
                }

                if ($f_desde != '') {
                  $donde .= " AND c.fecha_cita >= '$f_desde'";
                }
                if ($f_hasta != '') {
                  $donde .= " AND c.fecha_cita <= '$f_hasta'";
                }
                if ($f_estado != '') {
                  $donde .= " AND c.estado = '$f_estado'";
                }

                $sql_conteo = "SELECT COUNT(*) as total 
                            FROM citas c
                            INNER JOIN persona p ON c.Id_paciente = p.id
                            INNER JOIN detalle_medico dm ON c.Id_medico = dm.Id_detalle_medico
                            INNER JOIN persona m ON dm.Id_persona = m.id 
                            $donde";

                $resultado_conteo = mysqli_query($conexion, $sql_conteo);
                $fila_conteo = mysqli_fetch_assoc($resultado_conteo);
                $total_citas = $fila_conteo['total'];
                $total_paginas = ceil($total_citas / $registros_por_pagina);

                $sql = "SELECT c.*, Id_cita,
              p.nombre AS nombre_paciente, 
              p.apellido AS apellido_paciente,
              p.tipo_cedula AS tipo_cedula_paciente, 
              p.cedula AS cedula_paciente, 
              m.nombre AS nombre_medico, 
              m.apellido AS apellido_medico
              FROM citas c
              INNER JOIN persona p ON c.Id_paciente = p.id
              INNER JOIN detalle_medico dm ON c.Id_medico = dm.Id_detalle_medico
              INNER JOIN persona m ON dm.Id_persona = m.id
              $donde
              ORDER BY c.fecha_cita DESC, c.hora_cita DESC
              LIMIT $inicio, $registros_por_pagina";
                $resultado = $conexion->query($sql);
                ?>
              </tr>
              <tr>
                <?php
                if ($resultado->num_rows > 0) {
                  while ($row = $resultado->fetch_assoc()) {

                    $estatus = $row['estado'];
                    $clase_alerta = '';
                    if ($estatus == 'Confirmada' && $row['fecha_cita'] == $hoy) {
                      $hora_cita_unix = strtotime($row['hora_cita']);
                      $ahora_unix = strtotime($ahora);
                      $diferencia = ($hora_cita_unix - $ahora_unix) / 60;
                      if ($diferencia <= 30 && $diferencia > 0) {
                        $clase_alerta = 'cita-proxima';
                      }
                    }
                    $clase_badge = 'bg-gray';

                    if ($estatus == 'Pendiente') $clase_badge = 'label-warning';
                    if ($estatus == 'Confirmada') $clase_badge = 'label-info';
                    if ($estatus == 'Finalizada') $clase_badge = 'label-success';
                    if ($estatus == 'Cancelada') $clase_badge = 'label-danger';
                    if ($estatus == 'Inasistente') $clase_badge = 'label-default';
                    if ($estatus == 'Vencida') $clase_badge = 'label-default';
                    if ($estatus == 'Reprogramada') $clase_badge = 'label-success';

                ?>
              </tr>
              <tr>
                <td class=""><span class="text-row text-white"><?= $row['nombre_paciente']; ?> <?= $row['apellido_paciente']; ?></span></td>
                <td class=""><span class="text-row text-white"><?= $row['nombre_medico']; ?> <?= $row['apellido_medico']; ?></span></td>
                <td class=""><span class="text-row text-white"><?= date('d/m/Y', strtotime($row['fecha_cita'])); ?> - <?= date('h:i A', strtotime($row['hora_cita'])); ?></span></td>
                <td class=""><span class="label <?= $clase_badge; ?>" style="font-size: 11px;"><?= strtoupper($estatus); ?></span></td>
                <?php if (in_array('Gestionar acciones de citas', $_SESSION["permisos"])) : ?>
                  <td>
                    <?php if (in_array('Reactivar Citas', $_SESSION["permisos"])) : ?>
                      <a href="#" data-id="<?php echo $row['Id_cita'] ?>" class="btn-sm btn-success btn-reactivar" title="Reactivar"><img src="../../../recursos/imagenes/iconos/reactivar.png" style="width:15px; height:15px;"></a>
                    <?php endif; ?>
                    <?php if (in_array('Eliminar Citas', $_SESSION["permisos"])) : ?>
                      <a href="#" data-id="<?php echo $row['Id_cita'] ?>" class="btn-sm btn-danger btn-eliminar" title="Eliminar"><img src="../../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></a>
                    <?php endif; ?>
                  </td>
                <?php endif; ?>
              </tr>
          <?php }
                } else {
                  echo "<tr><td colspan='6'>No se encontraron citas inactivas.</td></tr>";
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
            <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="modalBusquedaAvanzada" tabindex="-1" role="dialog" aria-labelledby="modalFiltrosLabel">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <form id="formBusquedaAvanzada" method="GET" action="">
            <div class="modal-header bg-primary">
              <h4 class="modal-title" id="modalFiltrosLabel"><i class="fa fa-filter"></i> Filtros Avanzados de Citas</h4>
            </div>
            <div class="modal-body">
              <div class="row">
                <div class="col-md-4 form-group">
                  <label>Fecha Desde</label>
                  <input type="date" name="f_desde" class="form-control" max="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="col-md-4 form-group">
                  <label>Fecha Hasta</label>
                  <input type="date" name="f_hasta" class="form-control" min="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="col-md-4 form-group">
                  <label>Estado de la Cita</label>
                  <select name="f_estado" class="form-control">
                    <option value="">Todos los Estados</option>
                    <option value="Pendiente" <?php echo ($f_estado == 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                    <option value="Confirmada" <?php echo ($f_estado == 'Confirmada') ? 'selected' : ''; ?>>Confirmada</option>
                    <option value="Finalizada" <?php echo ($f_estado == 'Finalizada') ? 'selected' : ''; ?>>Finalizada</option>
                    <option value="Cancelada" <?php echo ($f_estado == 'Cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                    <option value="Inasistente" <?php echo ($f_estado == 'Inasistente') ? 'selected' : ''; ?>>Inasistente</option>
                    <option value="Vencida" <?php echo ($f_estado == 'Vencida') ? 'selected' : ''; ?>>Vencida</option>
                    <option value="Reprogramada" <?php echo ($f_estado == 'Reprogramada') ? 'selected' : ''; ?>>Reprogramada</option>
                  </select>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12 form-group">
                  <label>Término de Búsqueda (Paciente, Médico, Motivo...)</label>
                  <input type="text" name="buscar" class="form-control" placeholder="Escriba aquí..." value="<?php echo htmlspecialchars($busqueda); ?>">
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-second pull-left" onclick="limpiarFiltrosAjax()">Limpiar Filtros</button>
              <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
              <button type="submit" class="btn btn-success"><i class="fa fa-search"></i> Aplicar Búsqueda</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="modal fade" id="ReactivarCita" tabindex="-1" role="dialog" aria-labelledby="ReactivarCitaLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header bg-green" <h4 class="modal-title" id="ReactivarCitaLabel">Confirmación de Reactivación</h4>
          </div>
          <div class="modal-body">
            <p>¿Está seguro de que desea reactivar esta cita?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
            <a href="#" class="btn btn-success" id="reactivar">Aceptar</a>
          </div>
        </div>
      </div>
    </div>

    <div class="modal" id="EliminarCita" tabindex="-1" role="dialog" aria-labelledby="EliminarCitaLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header bg-crimson">
            <h4 class="modal-title" id="EliminarCitaLabel">Confirmación de Eliminación</h4>
          </div>
          <div class="modal-body">
            <p>¿Está seguro de que desea eliminar esta cita? Esta acción no se puede deshacer.</p>
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

    <script src="../../../plugins/fullcalendar/dist/index.global.js"></script>
    <script src="../../../plugins/fullcalendar/dist/index.global.min.js"></script>
    <script>
      var calendar;

      $(document).on('click', '.btn-reactivar', function(e) {
        e.preventDefault();
        var IdCita = $(this).data('id');
        var urlReactivar = "../../../cfg/reactivar/reactivar_cita.php?Id=" + IdCita;
        $('#reactivar').attr('href', urlReactivar);
        $('#ReactivarCita').modal('show');
      })

      $(document).on('click', '.btn-eliminar', function(e) {
        e.preventDefault();
        var IdCita = $(this).data('id');
        var urlEliminar = "../../../cfg/eliminar/eliminar_cita.php?Id=" + IdCita;
        $('#eliminar').attr('href', urlEliminar);
        $('#EliminarCita').modal('show');
      })

      function gestionarReprogramacion(id, estatusActual) {
        if (estatusActual === 'Inasistente') {
          $.ajax({
            url: '../../../cfg/ajax/actualizar_estado_cita.php',
            type: 'POST',
            data: {
              id: id,
              estado: 'Reprogramada'
            },
            success: function(response) {
              if (response.trim() === "ok") {
                window.location.href = 'citas_medicas_editar.php?Id=' + id;
              } else {
                alert("Error al intentar cambiar el estado a Reprogramada.");
              }
            },
            error: function() {
              alert("Error de conexión al intentar reprogramar.");
            }
          });
        }
      }

      function cambiarEstado(id, nuevoEstado) {
        if (confirm('¿Confirmar cambio a ' + nuevoEstado + '?')) {
          $.post('../../../cfg/ajax/actualizar_estado_cita.php', {
            id: id,
            estado: nuevoEstado
          }, function(data) {
            if (data.trim() == 'ok') {
              location.reload();
            } else {
              alert('Error al actualizar');
            }
          });
        }
      }

      $(document).ready(function() {
        $('#btnVistaCalendario').on('click', function() {
          $('#contenedorTabla').hide();
          $('#contenedorCalendario').show();
          $('.pagination').hide();
          $('.btn').removeClass('active');
          $(this).addClass('active');
          renderizarCalendario();
        });

        $('#btnVistaTabla').on('click', function() {
          $('#contenedorCalendario').hide();
          $('#contenedorTabla').show();
          $('.pagination').show();
          $('.btn').removeClass('active');
          $(this).addClass('active');
        });

        function renderizarCalendario() {
          var calendarEl = document.getElementById('calendar');
          var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'es',
            height: 490,
            contentHeight: '490',
            aspectRatio: 1.5,
            buttonText: {
              today: 'Hoy',
              week: 'Semana',
              month: 'Mes',
              day: 'Dia',
              list: 'Agenda',
            },
            headerToolbar: {
              left: 'prev,next today',
              center: 'title',
              right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            events: '../../../cfg/ajax/obtener_eventos_citas.php',
            eventClick: function(info) {
              alert('Cita: ' + info.event.title + '\nMotivo: ' + info.event.extendedProps.description);
            }
          });
          calendar.render();
        }

        window.cargarDatosAjax = function(url) {
          $('.tbody').css('opacity', '0.4');

          $.get(url, function(data) {
            var htmlParsed = $(data);

            $('.tbody').html(htmlParsed.find('.tbody').html()).css('opacity', '1');
            $('nav[aria-label="Page navigation"]').html(htmlParsed.find('nav[aria-label="Page navigation"]').html());

            window.history.pushState(null, '', url);
          }).fail(function() {
            alert("Error de conexión al aplicar filtros.");
            $('.tbody').css('opacity', '1');
          });
        };

        let timerBusqueda;
        $('#buscar').on('keyup', function() {
          clearTimeout(timerBusqueda);
          let query = $(this).val();
          timerBusqueda = setTimeout(function() {
            var url = 'citas_medicas_papelera_listado.php?buscar=' + encodeURIComponent(query);
            cargarDatosAjax(url);
          }, 400);
        });

        $('#formBusquedaRapida').on('submit', function(e) {
          e.preventDefault();
        });

        $('#formBusquedaAvanzada').on('submit', function(e) {
          e.preventDefault();
          var url = 'citas_medicas_papelera_listado.php?' + $(this).serialize();
          $('#modalBusquedaAvanzada').modal('hide');
          cargarDatosAjax(url);
        });

        $(document).on('click', 'nav[aria-label="Page navigation"] .pagination a', function(e) {
          e.preventDefault();
          var url = $(this).attr('href');
          if (url) {
            cargarDatosAjax(url);
          }
        });

        window.limpiarFiltrosAjax = function() {
          $('#formBusquedaAvanzada')[0].reset();
          $('#buscar').val('');
          var urlLimpia = window.location.href.split('?')[0];
          cargarDatosAjax(urlLimpia);
          $('#modalBusquedaAvanzada').modal('hide');
        };

        <?php if ($mostrar_modal_exito) : ?>
          $('#modalExito').modal('show');
        <?php elseif ($mostrar_modal_error) : ?>
          $('#modalError').modal('show');
        <?php endif; ?>
      });
    </script>