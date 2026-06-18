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
  #DesactivarCita,
  #ModalReporteCitas {
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
  <title>Citas Medicas | Listado</title>
  <?php
  include('includes/headerNav2.php');
  ?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <?php
    include('../../cfg/conexion.php');
    $sqlCita = ("SELECT * FROM citas WHERE estatus = 1 ORDER BY Id_cita ASC");
    $queryData   = mysqli_query($conexion, $sqlCita);
    $total_cita = mysqli_num_rows($queryData);
    ?>
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Citas Medicas (<?php echo $total_cita; ?>)
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-user"></i>Citas Medicas</a></li>
        <li><a href="#"><i class="fa fa-book"></i>Listado</a></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div style="padding-bottom: 10px;">
        <?php if (in_array('Ver papelera de citas', $_SESSION["permisos"])) : ?>
          <a href="papelera/citas_medicas_papelera_listado.php" class="btn-sm btn-primary pull-right" style="background-color:gray;"> Papelera </a>
        <?php endif; ?>
        <p class="pull-right" style="width:5px;"></p>
        <?php if (in_array('Generar Reportes de Citas', $_SESSION["permisos"])) : ?>
          <a href="#" class="btn-sm btn-info pull-right reporte"><i class="fa fa-book"></i> Generar Reporte </a>
        <?php endif; ?>
        <p class="pull-right" style="width:5px;"></p>
        <?php if (in_array('Crear Citas', $_SESSION["permisos"])) : ?>
          <a href="citas_medicas_agregar.php" class="btn-sm btn-success pull-right"> Agendar Nueva Cita </a>
        <?php endif; ?>
        <p class="pull-right" style="width:5px;"></p>
        <a href="#" class="btn-sm btn-primary active pull-right" id="btnVistaTabla">Tabla</a>
        <p class="pull-right" style="width:5px;"></p>
        <a href="#" class="btn-sm btn-primary pull-right" id="btnVistaCalendario">Calendario</a>
        <p class="pull-right" style="width:5px;"></p>
        <input type="text" id="buscar" name="buscar" class="form-control pull-left" placeholder="Escriba para buscar..." value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>" autocomplete="off">
      </div>
      <br><br>
      <div id="contenedorCalendario" style="display: none; background: white; padding: 20px; border-radius: 5px;">
        <div id="calendar" style="max-width: 900px; max-height: 480px; margin:0 auto;"></div>
      </div>
      <div id="contenedorTabla">
        <table class="table table-sm table-hover mt-4" width="100%" height="20" id="t_user">
          <thead class="table-dark" style="background-color: #222; color: white; font-size: 12px;">
            <th>Paciente</th>
            <th>Medico</th>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Estado</th>
            <?php if (in_array('Gestionar acciones de citas', $_SESSION["permisos"])) : ?>
              <th>Cambios</th>
            <?php endif; ?>
            <th>Acciones</th>

          </thead>
          <tbody class="tbody" width="100%" style="font-size: 12px;">
            <tr>
              <?php
              // --- Lógica de Paginación y Búsqueda ---
              $busqueda = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : '';
              $registros_por_pagina = 14;
              $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
              $inicio = ($pagina_actual - 1) * $registros_por_pagina;

              // 1. Definir el filtro base
              $donde = "WHERE c.estatus = 1";
              if ($busqueda != '') {
                $donde .= " AND (p.nombre LIKE '%$busqueda%' 
                OR p.apellido LIKE '%$busqueda%' 
                OR p.cedula LIKE '%$busqueda%' 
                OR c.motivo LIKE '%$busqueda%'
                OR m.nombre LIKE '%$busqueda%'
                OR m.apellido LIKE '%$busqueda%')";
              }

              // 2. Contar el total de registros FILTRADOS
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

              // 3. Obtener los registros usando el filtro $donde
              $sql = "SELECT c.*, Id_cita,
              p.nombre AS nombre_paciente, p.apellido AS apellido_paciente,
              p.tipo_cedula AS tipo_cedula_paciente, p.cedula AS cedula_paciente, 
              m.nombre AS nombre_medico, m.apellido AS apellido_medico
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
              <td class=""><span class="text-row text-white"><?= $row['nombre_medico']; ?> <?= $row['apellido_medico']; ?></span></td>
              <td class=""><span class="text-row text-white"><?= date('d/m/Y', strtotime($row['fecha_cita'])); ?></span></td>
              <td class=""><span class="text-row text-white"><?= date('h:i A', strtotime($row['hora_cita'])); ?></span></td>
              <td class=""><span class="label <?= $clase_badge; ?>" style="font-size: 11px;"><?= strtoupper($estatus); ?></span></td>
              <?php if (in_array('Gestionar acciones de citas', $_SESSION["permisos"])) : ?>
                <td>
                  <?php if (in_array('Gestionar acciones de citas', $_SESSION["permisos"])) : ?>
                    <?php if ($estatus == 'Pendiente') : ?>
                      <button onclick="cambiarEstado(<?php echo $row['Id_cita'] ?>, 'Confirmada')" class="btn btn-xs btn-info btn-accion-rapida" title="Confirmar"><img src="../../recursos/imagenes/iconos/Arrow-w.png" style="width:15px; height:15px;"></button>
                      <button onclick="cambiarEstado(<?php echo $row['Id_cita'] ?>, 'Cancelada')" class="btn btn-xs btn-danger btn-accion-rapida" title="Cancelar"><img src="../../recursos/imagenes/iconos/cancelar.png" style="width:15px; height:15px;"></button>
                    <?php else : ?>
                    <?php endif; ?>

                    <?php if ($estatus == 'Vencida') : ?>
                      <button onclick="cambiarEstado(<?php echo $row['Id_cita'] ?>, 'Cancelada')" class="btn btn-xs btn-danger btn-accion-rapida" title="Cancelar"><img src="../../recursos/imagenes/iconos/cancelar.png" style="width:15px; height:15px;"></button>
                    <?php else : ?>
                    <?php endif; ?>

                    <?php if ($row['estado'] == 'Inasistente') : ?>
                      <button onclick="gestionarReprogramacion(<?php echo $row['Id_cita'] ?>, '<?php echo $row['estado'] ?>')" class="btn btn-xs btn-success btn-accion-rapida" title="Reprogramar">
                        <img src="../../recursos/imagenes/iconos/reprogramar.png" style="width:15px; height:15px;">
                      </button>

                      <button onclick="cambiarEstado(<?php echo $row['Id_cita'] ?>, 'Cancelada')" class="btn btn-xs btn-danger btn-accion-rapida" title="Cancelar">
                        <img src="../../recursos/imagenes/iconos/cancelar.png" style="width:15px; height:15px;">
                      </button>
                    <?php endif; ?>

                    <?php if ($estatus == 'Confirmada') : ?>
                      <a href="consulta_agregar.php?tipo_cedula=<?php echo $row['tipo_cedula_paciente']; ?>&cedula=<?php echo $row['cedula_paciente']; ?>&Id=<?php echo $row['Id_cita']; ?>&medico=<?php echo $row['Id_medico']; ?>&motivo=<?php echo $row['motivo']; ?>&fecha=<?php echo $row['fecha_cita']; ?>" class="btn btn-xs btn-info btn-accion" title="Atender Paciente">
                        <img src="../../recursos/imagenes/iconos/atender.png" style="width:15px; height:15px;">
                      </a>
                      <button onclick="cambiarEstado(<?php echo $row['Id_cita'] ?>, 'Finalizada')" class="btn btn-xs btn-success btn-accion-rapida" title="Finalizar"><img src="../../recursos/imagenes/iconos/finalizada.png" style="width:15px; height:15px;"></button>
                      <button onclick="cambiarEstado(<?php echo $row['Id_cita'] ?>, 'Cancelada')" class="btn btn-xs btn-danger btn-accion-rapida" title="Cancelar"><img src="../../recursos/imagenes/iconos/cancelar.png" style="width:15px; height:15px;"></button>
                    <?php else : ?>
                    <?php endif; ?>

                    <?php if ($estatus == 'Reprogramada') : ?>
                      <a href="consulta_agregar.php?tipo_cedula=<?php echo $row['tipo_cedula_paciente']; ?>&cedula=<?php echo $row['cedula_paciente']; ?>&Id=<?php echo $row['Id_cita']; ?>&medico=<?php echo $row['Id_medico']; ?>&motivo=<?php echo $row['motivo']; ?>&fecha=<?php echo $row['fecha_cita']; ?>" class="btn btn-xs btn-info btn-accion" title="Atender Paciente">
                        <img src="../../recursos/imagenes/iconos/atender.png" style="width:15px; height:15px;">
                      </a>
                      <button onclick="cambiarEstado(<?php echo $row['Id_cita'] ?>, 'Finalizada')" class="btn btn-xs btn-success btn-accion-rapida" title="Finalizar"><img src="../../recursos/imagenes/iconos/finalizada.png" style="width:15px; height:15px;"></button>
                      <button onclick="cambiarEstado(<?php echo $row['Id_cita'] ?>, 'Cancelada')" class="btn btn-xs btn-danger btn-accion-rapida" title="Cancelar"><img src="../../recursos/imagenes/iconos/cancelar.png" style="width:15px; height:15px;"></button>
                    <?php else : ?>
                    <?php endif; ?>
                </td>
              <?php endif; ?>
              <td>
              <?php endif; ?>
              <?php if (in_array('Generar Comprobante de Cita', $_SESSION["permisos"])) : ?>
                <a href="../../cfg/pdf/pdf_cita.php?Id=<?php echo $row['Id_cita'] ?>" class="btn-sm btn-primary" title="Generar Comprobante"><img src="../../recursos/imagenes/iconos/documento.png" style="width:15px; height:15px;"></a>
              <?php endif; ?>
              <?php if (in_array('Editar Citas', $_SESSION["permisos"])) : ?>
                <a href="citas_medicas_editar.php?Id=<?php echo $row['Id_cita'] ?>" class="btn-sm btn-warning" title="Editar"><img src="../../recursos/imagenes/iconos/editar.png" style="width:15px; height:15px;"></a>
              <?php endif; ?>
              <?php if (in_array('Desactivar Citas', $_SESSION["permisos"])) : ?>
                <a href="#" data-id="<?php echo $row['Id_cita'] ?>" class="btn-sm btn-danger btn-desactivar" title="Desactivar"><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></a>
              <?php endif; ?>
              </td>
            </tr>
          <?php }  ?>
          </tbody>
        </table>
      </div>
      <nav aria-label="Page navigation" style="position: fixed; bottom:0;">
        <ul class="pagination">
          <?php
          // Mantener el parámetro de búsqueda en los enlaces de paginación
          $query_string = (isset($busqueda) && $busqueda != '') ? "&buscar=" . urlencode($busqueda) : "";

          // Botón Primero y Anterior
          if ($pagina_actual > 1) : ?>
            <li><a href="?pagina=1<?php echo $query_string; ?>" title="Primero">&laquo;&laquo;</a></li>
            <li><a href="?pagina=<?php echo ($pagina_actual - 1) . $query_string; ?>">&laquo;</a></li>
          <?php endif;

          // --- LÓGICA DE VENTANA DE NÚMEROS ---
          $rango = 1;
          $inicio_ventana = max(1, $pagina_actual - $rango);
          $fin_ventana = min($total_paginas, $pagina_actual + $rango);

          // Ajuste para mostrar siempre al menos 3 botones si existen
          if ($pagina_actual == 1) $fin_ventana = min($total_paginas, 3);
          if ($pagina_actual == $total_paginas) $inicio_ventana = max(1, $total_paginas - 2);

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

    <div class="modal" id="DesactivarCita" tabindex="-1" role="dialog" aria-labelledby="DesactivarCitaLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #dc3545; color: white;">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="DesactivarCitaLabel">Confirmar Desactivacion</h4>
          </div>
          <div class="modal-body">
            <p>¿Está seguro de que desea desactivar esta cita? Esta acción solo se puede revertir en la papelera.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
            <a id="desactivar" href="#" class="btn btn-danger">Aceptar</a>
          </div>
        </div>
      </div>
    </div>

    <div class="modal" id="ModalReporteCitas" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #337ab7; color: white;">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="myModalLabel"><i class="fa fa-file-pdf-o"></i> Reportes de Citas Médicas</h4>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label>Seleccione el tipo de reporte:</label>
              <select class="form-control" id="tipo_reporte_citas">
                <option value="hoy">Citas para el día de Hoy</option>
                <option value="proximas">Próximas Citas (Futuras)</option>
                <option value="estados">Estadísticas por Estado (Pendiente, Finalizada, etc.)</option>
                <option value="totales">Historial Completo de Citas</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-primary" id="btnEjecutarReporteCitas">Generar PDF</button>
          </div>
        </div>
      </div>
    </div>




    <?php
    include('includes/footer.php');
    ?>

    </body>
    <script src="../../plugins/fullcalendar/dist/index.global.js"></script>
    <script src="../../plugins/fullcalendar/dist/index.global.min.js"></script>
    <script>
      var calendar;

      function closeCustomModal(modalElement) {
        modalElement.removeClass('in').addClass('out');
        setTimeout(() => {
          modalElement.modal('hide').removeClass('out');
        }, 100); // Duración de la animación
      }

      // CORRECCIÓN: Eventos para cerrar el modal de aviso
      $('#DesactivarCita .close, #DesactivarCita .btn-second').on('click', function() {
        closeCustomModal($('#DesactivarCita'));
      });

      $('#ModalReporteCitas .close, #ModalReporteCitas .btn-second').on('click', function() {
        closeCustomModal($('#ModalReporteCitas'));
      });

      // Abrir el modal al hacer clic en el botón de reporte
      $('.reporte').on('click', function(e) {
        e.preventDefault();
        $('#ModalReporteCitas').modal('show');
      });

      // Ejecutar la generación del reporte
      $('#btnEjecutarReporteCitas').on('click', function() {
        var tipo = $('#tipo_reporte_citas').val();
        // Redirigir al nuevo archivo generador
        window.open('../../cfg/reportes/generar_pdf_citas.php?tipo=' + tipo, '_blank');
        $('#ModalReporteCitas').modal('hide');
      });

      $(document).on('click', '.btn-desactivar', function(e) {
        e.preventDefault();
        var IdCita = $(this).data('id');
        var urlDesactivar = "../../cfg/desactivar/desactivar_cita.php?Id=" + IdCita;
        $('#desactivar').attr('href', urlDesactivar);
        $('#DesactivarCita').modal('show');
      })

      function gestionarReprogramacion(id, estatusActual) {
        if (estatusActual === 'Inasistente') {
          // Ejecutamos el cambio de estado silencioso antes de ir a editar
          $.ajax({
            url: '../../cfg/ajax/actualizar_estado_cita.php',
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
          $.post('../../cfg/ajax/actualizar_estado_cita.php', {
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
            events: '../../cfg/ajax/obtener_eventos_citas.php', // Debes crear este archivo PHP
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