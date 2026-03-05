<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Pacientes | Listado</title>
  <?php
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
    #DesactivarPaciente,
    #ModalReportePaciente {
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
    // Consulta para contar el total de pacientes con el rol 3
    $sqlPacientes = ("SELECT p.estatus, COUNT(p.id) AS total_pacientes FROM persona p 
      JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona 
      JOIN rol r ON dpr.Id_rol = r.Id_rol
      WHERE r.Id_rol = 3 AND TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) >= 18 AND p.estatus = 1");
    $queryData   = mysqli_query($conexion, $sqlPacientes);
    $total_pacientes = mysqli_fetch_assoc($queryData)['total_pacientes'];
    ?>
    <section class="content-header">
      <h1>
        Pacientes (<?php echo $total_pacientes; ?>)
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-user"></i>Pacientes</a></li>
        <li><a href="#"><i class="fa fa-book"></i>Listado</a></li>
      </ol>
    </section>
    <?php if (isset($_SESSION["permisos"])) : ?>
      <section class="content">
        <div style="padding-bottom: 10px;">
          <?php if (in_array('Ver papelera de pacientes', $_SESSION["permisos"])) : ?>
            <a href="papelera/pacientes_papelera_listado.php" class="btn-sm btn-primary pull-right" style="background-color:gray;"> Papelera </a>
          <?php endif; ?>
          <p class="pull-right" style="width:5px;"></p>
          <?php if (in_array('Generar Reportes de Pacientes', $_SESSION["permisos"])) : ?>
            <a href="#" class="btn-sm btn-info pull-right reporte"><i class="fa fa-book"></i> Generar Reporte </a>
          <?php endif; ?>
          <p class="pull-right" style="width:5px;"></p>
          <?php if (in_array('Crear Pacientes', $_SESSION["permisos"])) : ?>
            <a href="pacientes_agregar.php" class="btn-sm btn-success pull-right"><i class="fa fa-user-plus"></i> Añadir Un Nuevo Paciente </a>
          <?php endif; ?>
          <input type="text" id="buscar" name="buscar" class="form-control" placeholder="Escriba para buscar..." value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>" autocomplete="off">
        </div>
        <br><br>
        <div id="contenedorTabla">
          <table class="table table-sm table-hover mt-4" width="100%" height="20" id="t_user">
            <thead class="table-dark" style="background-color: #222; color: white; font-size: 12px;">
              <th>Cedula</th>
              <th>Nombres Y Apellidos</th>
              <th>Genero</th>
              <th>Edad</th>
              <th>Telefono</th>
              <?php if (in_array('Gestionar acciones de pacientes', $_SESSION["permisos"])) : ?>
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

              // Filtro base para pacientes mayores de edad y activos
              $donde = "WHERE r.Id_rol = 3 AND TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) >= 18 AND p.estatus = 1";

              if ($busqueda != '') {
                $donde .= " AND (p.cedula LIKE '%$busqueda%' OR p.nombre LIKE '%$busqueda%' OR p.apellido LIKE '%$busqueda%')";
              }

              // Consulta para contar el total filtrado
              $sqlPacientes = "SELECT COUNT(DISTINCT p.id) AS total_pacientes 
                              FROM persona p 
                              JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona 
                              JOIN rol r ON dpr.Id_rol = r.Id_rol 
                              $donde";
              $queryData = mysqli_query($conexion, $sqlPacientes);
              $total_pacientes = mysqli_fetch_assoc($queryData)['total_pacientes'];
              $total_paginas = ceil($total_pacientes / $registros_por_pagina);

              // Consulta para obtener los registros de la página actual
              $sql = "SELECT r.Id_rol, pt.prefijo, t.telefono, p.id, p.tipo_cedula, p.cedula, p.nombre, p.apellido, p.genero, p.fecha_nacimiento, p.estatus 
              FROM persona p 
              JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona 
              JOIN rol r ON dpr.Id_rol = r.Id_rol
              LEFT JOIN telefonos_personas t ON p.id = t.Id_persona
              LEFT JOIN prefijos_telefonos pt ON t.Id_prefijo = pt.Id
              $donde
              GROUP BY p.id
              LIMIT $inicio, $registros_por_pagina";
              $resultado = $conexion->query($sql);

              if ($resultado->num_rows > 0) {
                while ($row = $resultado->fetch_assoc()) {
                  // Calcular la edad a partir de la fecha de nacimiento
                  $fechaNacimiento = new DateTime($row['fecha_nacimiento']);
                  $hoy = new DateTime();
                  $edad = $hoy->diff($fechaNacimiento)->y;
              ?>
                  <tr>
                    <td class=""><span class="text-row text-white"><?= ($row['tipo_cedula']) . "-" . ($row['cedula']); ?></span></td>
                    <td class=""><span class="text-row text-white"><?= ($row['nombre']) . " " . ($row['apellido']); ?></span></td>
                    <td class=""><span class="text-row text-white"><?= ($row['genero']); ?></span></td>
                    <td class=""><span class="text-row text-white"><?= $edad; ?></span></td>
                    <td class=""><span class="text-row text-white"><?= ($row['prefijo']) . "-" . ($row['telefono']); ?></span></td>
                    <?php if (in_array('Gestionar acciones de pacientes', $_SESSION["permisos"])) : ?>
                      <td>
                        <?php if (in_array('Ver Pacientes', $_SESSION["permisos"])) : ?>
                          <a href="pacientes_info.php?Id=<?php echo $row['id'] ?>" class="btn-sm btn-info" title="Ver Informacion"><img src="../../recursos/imagenes/iconos/info.png" style="width:15px; height:15px;"></a>
                        <?php endif; ?>
                        <?php if (in_array('Editar Pacientes', $_SESSION["permisos"])) : ?>
                          <a href="pacientes_editar.php?Id=<?php echo $row['id'] ?>" class="btn-sm btn-warning" title="Editar"><img src="../../recursos/imagenes/iconos/editar.png" style="width:15px; height:15px;"></a>
                        <?php endif; ?>
                        <?php if (in_array('Desactivar Pacientes', $_SESSION["permisos"])) : ?>
                          <a href="#" data-id="<?php echo $row['id'] ?>" class="btn-sm btn-danger btn-desactivar" title="Desactivar"><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></a>
                        <?php endif; ?>
                      </td>
                    <?php endif; ?>
                  </tr>
              <?php
                }
              } else {
                echo "<tr><td colspan='7'>No se encontraron pacientes.</td></tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
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

      <div class="modal" id="DesactivarPaciente" tabindex="-1" role="dialog" aria-labelledby="DesactivarPacienteLabel">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header" style="background-color: #dc3545; color: white;">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title" id="DesactivarPacienteLabel">Confirmar Desactivacion</h4>
            </div>
            <div class="modal-body">
              <p>¿Está seguro de que desea desactivar este paciente? Esta acción solo se puede revertir en la papelera.</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
              <a href="#" id="desactivar" class="btn btn-danger">Aceptar</a>
            </div>
          </div>
        </div>
      </div>

      <div class="modal" id="ModalReportePaciente" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header" style="background-color: #337ab7; color: white;">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title" id="myModalLabel"><i class="fa fa-user"></i> Reportes de Pacientes (Mayores de Edad)</h4>
            </div>
            <div class="modal-body">
              <div class="form-group">
                <label>Seleccione el tipo de reporte:</label>
                <select class="form-control" id="tipo_reporte_paciente">
                  <option value="todos">Listado General (Todos)</option>
                  <option value="activos">Solo Pacientes Activos</option>
                  <option value="sangre">Agrupados por Tipo de Sangre</option>
                  <option value="femenino">Solo Pacientes (Femenino)</option>
                  <option value="masculino">Solo Pacientes (Masculino)</option>
                </select>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
              <button type="button" class="btn btn-primary" id="btnEjecutarReportePaciente">Generar PDF</button>
            </div>
          </div>
        </div>
      </div>

      <?php
      include('includes/footer.php');
      ?>

      </body>
      <script>
        $(document).ready(function() {

          function closeCustomModal(modalElement) {
            modalElement.removeClass('in').addClass('out');
            setTimeout(() => {
              modalElement.modal('hide').removeClass('out');
            }, 100); // Duración de la animación
          }

          // CORRECCIÓN: Eventos para cerrar el modal de aviso
          $('#DesactivarPaciente .close, #DesactivarPaciente .btn-second').on('click', function() {
            closeCustomModal($('#DesactivarPaciente'));
          });

          $('.reporte').on('click', function(e) {
            e.preventDefault();
            $('#ModalReportePaciente').modal('show');
          });

          // Cerrar el modal
          $('#ModalReportePaciente .close, #ModalReportePaciente .btn-second').on('click', function() {
            closeCustomModal($('#ModalReportePaciente'));
          });

          // Generar el reporte
          $('#btnEjecutarReportePaciente').on('click', function() {
            var tipo = $('#tipo_reporte_paciente').val();
            window.open('../../cfg/reportes/generar_pdf_pacientes.php?tipo=' + tipo, '_blank');
            $('#ModalReportePaciente').modal('hide');
          });

          $(document).on('click', '.btn-desactivar', function(e) {
            e.preventDefault();
            var IdPaciente = $(this).data('id');
            var urlDesactivar = "../../cfg/desactivar/desactivar_paciente.php?Id=" + IdPaciente;
            $('#desactivar').attr('href', urlDesactivar);
            $('#DesactivarPaciente').modal('show');
          })

          // Script para mostrar los modales de sesión
          <?php if ($mostrar_modal_exito) : ?>
            $('#modalExito').modal('show');
          <?php elseif ($mostrar_modal_error) : ?>
            $('#modalError').modal('show');
          <?php endif; ?>
        });
      </script>

</html>