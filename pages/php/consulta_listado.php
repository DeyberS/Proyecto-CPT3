<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Consultas | Listado</title>
  <?php
  // Iniciar la sesión para poder acceder a las variables de mensaje
  include('includes/headerNav2.php');
  ?>
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
    #DesactivarConsulta,
    #ModalReporteConsulta {
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
  </style>

  <div class="content-wrapper">
    <?php
    include('../../cfg/conexion.php');

    // Es necesario tener acceso a las variables de sesión para el filtro
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Obtenemos los datos del usuario logueado (asegúrate de que estas variables existan en tu header/login)
    $id_persona_activa = $_SESSION['id'] ?? 0;
    $id_rol_usuario_activo = $_SESSION['rol'] ?? 0;

    // Base de la consulta
    $sqlConsultas = "SELECT COUNT(c.Id_consulta) AS total_consultas 
                    FROM consulta c
                    INNER JOIN detalle_medico dm ON c.Id_medico = dm.Id_detalle_medico
                    WHERE c.estatus = 1";

    // Si el usuario es un Médico (Rol 7 o 4), aplicamos el filtro por su ID de persona
    if ($id_rol_usuario_activo == 7 || $id_rol_usuario_activo == 4) {
        $sqlConsultas .= " AND dm.Id_persona = '$id_persona_activa'";
    }

    $queryData = mysqli_query($conexion, $sqlConsultas);
    $total_consultas = mysqli_fetch_assoc($queryData)['total_consultas'];
    ?>
    <section class="content-header">
      <h1>
        Consultas (<?php echo $total_consultas; ?>)
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-user"></i>Consultas</a></li>
        <li><a href="#"><i class="fa fa-book"></i>Listado</a></li>
      </ol>
    </section>

    <section class="content">
      <div style="padding-bottom: 10px;">
        <?php if (in_array('Ver papelera de consultas', $_SESSION["permisos"])) : ?>
          <a href="papelera/consulta_papelera_listado.php" class="btn-sm btn-primary pull-right" style="background-color:gray;"> Papelera </a>
        <?php endif; ?>
        <p class="pull-right" style="width:5px;"></p>
        <?php if (in_array('Generar Reportes de Consultas', $_SESSION["permisos"])) : ?>
          <a href="#" class="btn-sm btn-info pull-right reporte"> Generar Reporte </a>
        <?php endif; ?>
        <p class="pull-right" style="width:5px;"></p>
        <?php if (in_array('Crear Consultas', $_SESSION["permisos"])) : ?>
          <a href="consulta_agregar.php" class="btn-sm btn-success pull-right"> Nueva Consulta </a>
        <?php endif; ?>
        <input type="text" id="buscar" name="buscar" class="form-control" placeholder="Escriba para buscar..." value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>" style="border-radius:0; height:10%; width:250px; display:inline-block;" autocomplete="off">
      </div>
      <br><br>
      <div id="contenedorTabla">
        <table class="table table-sm table-hover mt-4" width="100%" height="20" id="t_user">
          <thead class="table-dark" style="background-color: #222; color: white; font-size: 12px;">
            <th>Paciente</th>
            <th>Médico</th>
            <th>Fecha de Consulta</th>
            <?php if (in_array('Gestionar acciones de consultas', $_SESSION["permisos"])) : ?>
              <th>Acciones</th>
            <?php endif; ?>
          </thead>
          <tbody class="tbody" width="100%" style="font-size: 12px;">
            <?php
            // Número de registros por página
            $busqueda = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : '';
            $registros_por_pagina = 14;
            $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
            $inicio = ($pagina_actual - 1) * $registros_por_pagina;

            // 2. Definir el filtro base (Importante para que el buscador funcione)
            $donde = "WHERE c.estatus = 1";

            $id_rol_usuario_activo = isset($_SESSION['rol']) ? $_SESSION['rol'] : 0; 
            $id_persona_activa = isset($_SESSION['id']) ? $_SESSION['id'] : (isset($_SESSION['id']) ? $_SESSION['id'] : 0);

            if ($id_rol_usuario_activo == 7) {
              $donde .= " AND dm.Id_persona = '$id_persona_activa'";
          }

          if ($busqueda != '') {
            $donde .= " AND (p_paciente.nombre LIKE '%$busqueda%' 
                          OR p_paciente.apellido LIKE '%$busqueda%' 
                          OR p_medico.nombre LIKE '%$busqueda%' 
                          )";
          }

          // 3. Contar el total de registros FILTRADOS para la paginación
          // CORRECCIÓN: Se agregaron los mismos JOIN de detalle_medico que usas en tu consulta principal
          $sql_conteo = "SELECT COUNT(*) as total 
                        FROM consulta c
                        JOIN persona p_paciente ON c.Id_paciente = p_paciente.id
                        JOIN detalle_medico dm ON c.Id_medico = dm.Id_detalle_medico
                        JOIN persona p_medico ON dm.Id_persona = p_medico.id 
                        $donde";
            $resultado_conteo = mysqli_query($conexion, $sql_conteo);
            $fila_conteo = mysqli_fetch_assoc($resultado_conteo);
            $total_consultas_filtradas = $fila_conteo['total'];
            $total_paginas = ceil($total_consultas_filtradas / $registros_por_pagina);

            // 4. Consulta principal con el filtro aplicado
            $sql = "SELECT 
                    c.id_consulta,
                    p_paciente.nombre AS nombre_paciente, 
                    p_paciente.apellido AS apellido_paciente,
                    p_medico.nombre AS nombre_medico, 
                    p_medico.apellido AS apellido_medico,
                    c.motivo_consulta,
                    c.diagnostico,
                    c.fecha_consulta,
                    c.estatus
                  FROM consulta c
                  JOIN persona p_paciente ON c.Id_paciente = p_paciente.id
                  JOIN detalle_medico dm ON c.Id_medico = dm.Id_detalle_medico
                  JOIN persona p_medico ON dm.Id_persona = p_medico.id
                  $donde
                  ORDER BY c.fecha_consulta DESC
                  LIMIT $inicio, $registros_por_pagina";

            $resultado = $conexion->query($sql);

            if ($resultado->num_rows > 0) {
              while ($row = $resultado->fetch_assoc()) {
            ?>
                <tr>
                  <td class=""><span class="text-row text-white"><?= ($row['nombre_paciente']) . " " . ($row['apellido_paciente']); ?></span></td>
                  <td class=""><span class="text-row text-white"><?= ($row['nombre_medico']) . " " . ($row['apellido_medico']); ?></span></td>
                  <td class=""><span class="text-row text-white"><?= date('d/m/Y', strtotime($row['fecha_consulta'])); ?></span></td>
                  <?php if (in_array('Gestionar acciones de consultas', $_SESSION["permisos"])) : ?>
                    <td>
                      <?php if (in_array('Ver Consultas', $_SESSION["permisos"])) : ?>
                        <a href="consulta_info.php?Id=<?php echo $row['id_consulta'] ?>" class="btn-sm btn-info" title="Ver consulta"><img src="../../recursos/imagenes/iconos/info.png" style="width:15px; height:15px;"></a>
                      <?php endif; ?>
                      <?php if (in_array('Generar Recipe Medico', $_SESSION["permisos"])) : ?>
                        <a href="../../cfg/pdf/consulta_receta_pdf.php?id_consulta=<?php echo $row['id_consulta'] ?>" class="btn-sm btn-primary" title="Generar Recipe"><img src="../../recursos/imagenes/iconos/documento.png" style="width:15px; height:15px;"></a>
                      <?php endif; ?>
                      <?php if (in_array('Editar Consultas', $_SESSION["permisos"])) : ?>
                        <a href="consulta_editar.php?Id=<?php echo $row['id_consulta'] ?>" class="btn-sm btn-warning" title="Editar Consulta"><img src="../../recursos/imagenes/iconos/editar.png" style="width:15px; height:15px;"></a>
                      <?php endif; ?>
                      <?php if (in_array('Desactivar Consultas', $_SESSION["permisos"])) : ?>
                        <a href="#" data-id="<?php echo $row['id_consulta'] ?>" class="btn-sm btn-danger btn-desactivar" title="Desactivar consulta"><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></a>
                      <?php endif; ?>
                    </td>
                  <?php endif; ?>
                </tr>
            <?php
              }
            } else {
              echo "<tr><td colspan='6'>No se encontraron consultas registradas.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
      <nav aria-label="Page navigation" style="position: fixed; bottom:0;">
        <ul class="pagination">
          <?php
          // Crear la cadena de texto para la URL si hay una búsqueda activa
          $query_string = ($busqueda != '') ? "&buscar=" . urlencode($busqueda) : "";
          
          $rango = 1;
          $inicio_ventana = max(1, $pagina_actual - $rango);
          $fin_ventana = min($total_paginas, $pagina_actual + $rango);

          // Botón Primero y Anterior
          if ($pagina_actual > 1) : ?>
            <li><a href="?pagina=1<?php echo $query_string; ?>" title="Primero">&laquo;&laquo;</a></li>
            <li><a href="?pagina=<?php echo ($pagina_actual - 1) . $query_string; ?>">&laquo;</a></li>
          <?php endif;

          // Números de la ventana (se mantiene tu lógica de rango)
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

    <div class="modal" id="DesactivarConsulta" tabindex="-1" role="dialog" aria-labelledby="DesactivarConsultaLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #dc3545; color: white;">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="DesactivarConsultaLabel">Confirmar Desactivacion</h4>
          </div>
          <div class="modal-body">
            <p>¿Está seguro de que desea desactivar esta consulta? Esta acción solo se puede revertir en la papelera.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
            <a id="desactivar" href="#" class="btn btn-danger">Aceptar</a>
          </div>
        </div>
      </div>
    </div>

    <div class="modal" id="ModalReporteConsulta" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #337ab7; color: white;">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title"><i class="fa fa-file-pdf-o"></i> Generar Reporte de Consultas</h4>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label>Seleccione el tipo de reporte:</label>
              <select id="tipo_reporte" class="form-control">
                <optgroup label="General">
                  <option value="todas">Todas las consultas (Histórico)</option>
                  <option value="hoy">Consultas del día de hoy</option>
                </optgroup>
                <optgroup label="Análisis Médico">
                  <option value="patologias">Frecuencia de Patologías (Morbilidad)</option>
                  <option value="riesgo">Pacientes con Signos en Riesgo (Tensión/Saturación)</option>
                  <option value="diagnosticos">Diagnosticos Mas Frecuentes en Consultas</option>
                </optgroup>
              </select>
            </div>
            <!--<p class="text-muted"><small>* El reporte se abrirá en una nueva pestaña en formato PDF.</small></p>-->
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
            <button type="button" id="btnEjecutarReporte" class="btn btn-primary">Generar Reporte</button>
          </div>
        </div>
      </div>
    </div>



    <?php
    include('includes/footer.php');
    ?>

    <script>
      $(document).ready(function() {

        function closeCustomModal(modalElement) {
          modalElement.removeClass('in').addClass('out');
          setTimeout(() => {
            modalElement.modal('hide').removeClass('out');
          }, 100); // Duración de la animación
        }

        // CORRECCIÓN: Eventos para cerrar el modal de aviso
        $('#DesactivarConsulta .close, #DesactivarConsulta .btn-second').on('click', function() {
          closeCustomModal($('#DesactivarConsulta'));
        });

        $('#ModalReporteConsulta .close, #ModalReporteConsulta .btn-second').on('click', function() {
          closeCustomModal($('#ModalReporteConsulta'));
        });

        $(document).on('click', '.btn-desactivar', function(e) {
          e.preventDefault();
          var IdCita = $(this).data('id');
          var urlDesactivar = "../../cfg/desactivar/desactivar_consulta.php?Id=" + IdCita;
          $('#desactivar').attr('href', urlDesactivar);
          $('#DesactivarConsulta').modal('show');
        })

        $('.reporte').on('click', function(e) {
          e.preventDefault();
          $('#ModalReporteConsulta').modal('show');
        });

        // 2. Al hacer clic en el botón "Generar" dentro del modal
        $('#btnEjecutarReporte').on('click', function() {
          var tipo = $('#tipo_reporte').val();
          // Abrimos el generador TCPDF en una nueva pestaña
          window.open('../../cfg/reportes/generar_pdf_consultas.php?tipo=' + tipo, '_blank');
          $('#ModalReporteConsulta').modal('hide');
        })

        // Script para mostrar los modales de sesión
        <?php if ($mostrar_modal_exito) : ?>
          $('#modalExito').modal('show');
        <?php elseif ($mostrar_modal_error) : ?>
          $('#modalError').modal('show');
        <?php endif; ?>
      });
    </script>


    </body>

</html>