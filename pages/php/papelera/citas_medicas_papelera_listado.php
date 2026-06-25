<!DOCTYPE html>
<html>
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
  #ReactivarCita,
  #EliminarCita {
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

  @keyframes pulso-borde {
    0% {
      box-shadow: inset 0 0 0px rgba(255, 193, 7, 0);
    }

    50% {
      box-shadow: inset 0 0 10px rgba(255, 193, 7, 0.3);
    }

    100% {
      box-shadow: inset 0 0 0px rgba(255, 193, 7, 0);
    }
  }

  @keyframes parpadeo-alerta {
    0% {
      opacity: 1;
    }

    50% {
      opacity: 0.6;
    }

    100% {
      opacity: 1;
    }
  }

  /* Alerta visual: Parpadeo para citas urgentes (faltan menos de 30 min) */
  .cita-proxima {
    background-color: #fff3cd !important;
    /* Amarillo suave */
    border-left: 5px solid #ffc107;
    /* Barra lateral de advertencia */
    transition: all 0.5s ease;
  }

  /* Animación de pulso suave en el borde */
  .cita-proxima td {
    animation: pulso-borde 2s infinite;
  }

  .btn-accion {
    margin-right: 2px;
  }

  .fc {
    font-size: 0.9em;
    /* Letra un poco más pequeña */
  }

  /* Reducir el tamaño de la cabecera (Mes y botones) */
  .fc .fc-toolbar {
    margin-bottom: 10px !important;
    padding: 5px;
  }

  .fc .fc-toolbar-title {
    font-size: 1.2em !important;
    font-weight: bold;
    text-transform: capitalize;
  }

  /* Hacer las celdas más compactas */
  .fc .fc-daygrid-day-frame {
    min-height: 80px !important;
    /* Altura de la celda más corta */
  }

  /* Estilo para los eventos (píldoras más delgadas) */
  .fc-event {
    border-radius: 3px !important;
    padding: 2px 5px !important;
    font-size: 0.85em !important;
    cursor: pointer;
  }
</style>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Citas Medicas | Papelera</title>
  <?php
  include('../includes/headerPapelera.php');
  ?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <?php
    include('../../../cfg/conexion.php');
    $sqlCita = ("SELECT * FROM citas WHERE estatus = 0 ORDER BY Id_cita ASC");
    $queryData   = mysqli_query($conexion, $sqlCita);
    $total_cita = mysqli_num_rows($queryData);
    ?>
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Citas Medicas Inactivas (<?php echo $total_cita; ?>)
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-user"></i>Citas Medicas</a></li>
        <li><a href="#"><i class="fa fa-book"></i>Papelera</a></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div style="padding-bottom: 10px;">
        <a href="../citas_medicas_listado.php" class="btn-sm btn-primary pull-right"><i class="fa fa-book"></i> Regresar al Listado </a>
        <input type="text" id="buscar" name="buscar" class="form-control pull-left" placeholder="Escriba para buscar..." value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>" style="border-radius:0; height:10%; width:250px; display:inline-block;" autocomplete="off"> </div>
      <br><br>
      <div id="contenedorTabla">
        <table class="table table-sm table-hover mt-4" width="100%" height="20" id="t_user">
          <thead class="table-dark" style="background-color: #222; color: white; font-size: 12px;">
            <th>Paciente</th>
            <th>Motivo</th>
            <th>Medico</th>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Estado</th>
            <?php if (in_array('Gestionar acciones de citas', $_SESSION["permisos"])) : ?>
              <th>Acciones</th>
            <?php endif; ?>
          </thead>
          <tbody class="tbody" width="100%" style="font-size: 12px;">
            <tr>
              <?php
              // --- Lógica de Paginación ---
              $busqueda = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : '';
              $registros_por_pagina = 14;
              $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
              $inicio = ($pagina_actual - 1) * $registros_por_pagina;

              // 2. Definir el filtro base (Papelera: estatus = 0)
              $donde = "WHERE c.estatus = 0";
              if ($busqueda != '') {
                $donde .= " AND (p.nombre LIKE '%$busqueda%' 
                              OR p.apellido LIKE '%$busqueda%' 
                              OR c.motivo LIKE '%$busqueda%' 
                              OR m.nombre LIKE '%$busqueda%' 
                              OR m.apellido LIKE '%$busqueda%')";
              }

              // 3. Contar el total de registros FILTRADOS
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

              // Consulta para obtener los registros de la página actual
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
              <?php while ($row = $resultado->fetch_assoc()) {

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
                $clase_badge = 'bg-gray'; // Default

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
              <td class=""><span class="text-row text-white"><?= $row['motivo']; ?></span></td>
              <td class=""><span class="text-row text-white"><?= $row['nombre_medico']; ?> <?= $row['apellido_medico']; ?></span></td>
              <td class=""><span class="text-row text-white"><?= date('d/m/Y', strtotime($row['fecha_cita'])); ?></span></td>
              <td class=""><span class="text-row text-white"><?= date('h:i A', strtotime($row['hora_cita'])); ?></span></td>
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
          <?php }  ?>
          </tbody>
        </table>
      </div>
      <nav aria-label="Page navigation" style="position: fixed; bottom:0;">
        <ul class="pagination">
          <?php
          $query_string = ($busqueda != '') ? "&buscar=" . urlencode($busqueda) : "";

          $rango = 1;
          $inicio_ventana = max(1, $pagina_actual - $rango);
          $fin_ventana = min($total_paginas, $pagina_actual + $rango);

          // Ajuste para mostrar siempre al menos 3 botones si existen
          if ($pagina_actual == 1) $fin_ventana = min($total_paginas, 3);
          if ($pagina_actual == $total_paginas) $inicio_ventana = max(1, $total_paginas - 2);

          // Botón Primero y Anterior
          if ($pagina_actual > 1) : ?>
            <li><a href="?pagina=1<?php echo $query_string; ?>" title="Primero">&laquo;&laquo;</a></li>
            <li><a href="?pagina=<?php echo ($pagina_actual - 1) . $query_string; ?>">&laquo;</a></li>
          <?php endif;

          // Números de la ventana
          for ($i = $inicio_ventana; $i <= $fin_ventana; $i++) : ?>
            <li class="<?php echo ($i == $pagina_actual) ? 'active' : ''; ?>">
              <a href="?pagina=<?php echo $i . $query_string; ?>"><?php echo $i; ?></a>
            </li>
          <?php endfor;

          // Botón Siguiente y Último
          if ($pagina_actual < $total_paginas) : ?>
            <li><a href="?pagina=<?php echo ($pagina_actual + 1) . $query_string; ?>">&raquo;</a></li>
            <li><a href="?pagina=<?php echo $total_paginas . $query_string; ?>" title="Último">&raquo;&raquo;</a></li>
          <?php endif; ?>
        </ul>
      </nav>
    </section>
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

    <div class="modal fade" id="modalError" tabindex="-1" role="dialog" aria-labelledby="modalErrorLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header bg-crimson">
            <h5 class="modal-title" id="modalErrorLabel" style="color: white;">Error en la Operación</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
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

    <div class="modal" id="ReactivarCita" tabindex="-1" role="dialog" aria-labelledby="ReactivarCitaLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #dc3545; color: white;">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="ReactivarCitaLabel">Confirmar Desactivacion</h4>
          </div>
          <div class="modal-body">
            <p>¿Está seguro de que desea reactivar esta cita?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
            <a href="#" class="btn btn-danger" id="reactivar">Aceptar</a>
          </div>
        </div>
      </div>
    </div>

    <div class="modal" id="EliminarCita" tabindex="-1" role="dialog" aria-labelledby="EliminarCitaLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #dc3545; color: white;">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="EliminarCitaLabel">Confirmar Desactivacion</h4>
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
    </body>
    <script src="../../../plugins/fullcalendar/dist/index.global.js"></script>
    <script src="../../../plugins/fullcalendar/dist/index.global.min.js"></script>
    <script>
      var calendar;

      function closeCustomModal(modalElement) {
        modalElement.removeClass('in').addClass('out');
        setTimeout(() => {
          modalElement.modal('hide').removeClass('out');
        }, 100); // Duración de la animación
      }

      // CORRECCIÓN: Eventos para cerrar el modal de aviso
      $('#ReactivarCita .close, #ReactivarCita .btn-second').on('click', function() {
        closeCustomModal($('#ReactivarCita'));
      });

      $('#EliminarCita .close, #EliminarCita .btn-second').on('click', function() {
        closeCustomModal($('#EliminarCita'));
      });

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
          // Ejecutamos el cambio de estado silencioso antes de ir a editar
          $.ajax({
            url: '../../../cfg/ajax/actualizar_estado_cita.php',
            type: 'POST',
            data: {
              id: id,
              estado: 'Reprogramada'
            },
            success: function(response) {
              if (response.trim() === "ok") {
                // Si se actualizó bien, saltamos directo al editor
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

      // Función AJAX para actualizar estado
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
            height: 490, // Altura fija en píxeles para que no se estire al infinito
            contentHeight: '490',
            aspectRatio: 1.5, // Controla la proporción ancho/alto
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
            events: '../../../cfg/ajax/obtener_eventos_citas.php', // Debes crear este archivo PHP
            eventClick: function(info) {
              alert('Cita: ' + info.event.title + '\nMotivo: ' + info.event.extendedProps.description);
            }
          });
          calendar.render();
        }

        // Script para mostrar los modales de sesión
        <?php if ($mostrar_modal_exito) : ?>
          $('#modalExito').modal('show');
        <?php elseif ($mostrar_modal_error) : ?>
          $('#modalError').modal('show');
        <?php endif; ?>
      });
    </script>

</html>