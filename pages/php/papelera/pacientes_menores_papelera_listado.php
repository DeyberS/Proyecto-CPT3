<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Pacientes Menores de Edad | Papelera</title>
  <?php
  include('../includes/headerPapelera.php');
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
    #ReactivarPaciente,
    #EliminarPaciente {
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
    include('../../../cfg/conexion.php');
    // Consulta para contar el total de pacientes con el rol 3
    $sqlPacientes = ("SELECT p.estatus, COUNT(p.id) AS total_pacientes FROM persona p
      JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona 
      JOIN rol r ON dpr.Id_rol = r.Id_rol
      WHERE r.Id_rol = 3 AND TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) < 18 AND p.estatus = 0");
    $queryData   = mysqli_query($conexion, $sqlPacientes);
    $total_pacientes = mysqli_fetch_assoc($queryData)['total_pacientes'];
    ?>
    <section class="content-header">
      <h1>
        Pacientes Menores de Edad (<?php echo $total_pacientes; ?>)
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-user"></i>Pacientes Menores de Edad</a></li>
        <li><a href="#"><i class="fa fa-book"></i>Papelera</a></li>
      </ol>
    </section>

    <section class="content">
      <div style="padding-bottom: 10px;">
        <a href="../pacientes_menores_listado.php" class="btn-sm btn-primary pull-right"><i class="fa fa-user-plus"></i> Regresar al Listado </a>
        <input type="text" id="buscar" name="buscar" class="form-control" placeholder="Escriba para buscar..." value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>" autocomplete="off"> </div>
      <br><br>
      <div id="contenedorTabla">
        <table class="table table-sm table-hover mt-4" width="100%" height="20" id="t_user">
          <thead class="table-dark" style="background-color: #222; color: white; font-size: 12px;">
            <th>Cedula del Representante</th>
            <th>Representante</th>
            <th>Nombres Y Apellidos Del Menor</th>
            <th>Genero</th>
            <th>Edad</th>
            <?php if (in_array('Gestionar acciones de pacientes menores de edad', $_SESSION["permisos"])) : ?>
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

            // 2. Definir el filtro base (Papelera: estatus = 0)
            $donde = "WHERE r.Id_rol = 3 AND TIMESTAMPDIFF(YEAR, p_menor.fecha_nacimiento, CURDATE()) < 18 AND p_menor.estatus = 0";

            if ($busqueda != '') {
              $donde .= " AND (p_menor.nombre LIKE '%$busqueda%' 
                            OR p_menor.apellido LIKE '%$busqueda%' 
                            OR p_menor.cedula LIKE '%$busqueda%' 
                            OR p_rep.cedula LIKE '%$busqueda%' 
                            OR p_rep.nombre LIKE '%$busqueda%' 
                            OR p_rep.apellido LIKE '%$busqueda%')";
            }

            // 3. Contar el total de registros FILTRADOS para la paginación
            $sql_conteo = "SELECT COUNT(*) as total 
                          FROM persona p_menor 
                          JOIN detalle_persona_rol dpr ON p_menor.id = dpr.Id_persona 
                          JOIN rol r ON dpr.Id_rol = r.Id_rol 
                          JOIN detalle_paciente_menor dp ON p_menor.Id = dp.Id_persona
                          JOIN persona p_rep ON dp.Id_representante = p_rep.Id
                          $donde";

            $resultado_conteo = mysqli_query($conexion, $sql_conteo);
            $fila_conteo = mysqli_fetch_assoc($resultado_conteo);
            $total_permisos = $fila_conteo['total'];
            $total_paginas = ceil($total_permisos / $registros_por_pagina);

            // Consulta para obtener los registros de la página actual
            // Consulta corregida: añadimos el JOIN con la tabla 'rol' r
            $sql = "SELECT
            p_menor.id AS id,
            p_menor.nombre AS nombre_menor,
            p_menor.apellido AS apellido_menor,
            p_menor.tipo_cedula AS tipo_cedula,
            p_menor.cedula AS cedula_menor,
            p_menor.genero AS genero_menor,
            p_menor.fecha_nacimiento AS fecha_nacimiento,
            p_menor.estatus,
            TIMESTAMPDIFF(YEAR, p_menor.fecha_nacimiento, CURDATE()) AS edad,
            p_rep.nombre AS nombre_representante,
            p_rep.apellido AS apellido_representante,
            p_rep.tipo_cedula AS tipo_cedula_representante,
            p_rep.cedula AS cedula_representante,
            dp.parentesco
            FROM persona p_menor
            JOIN detalle_persona_rol dpr ON p_menor.Id = dpr.Id_persona
            JOIN rol r ON dpr.Id_rol = r.Id_rol -- ESTA LÍNEA ES LA QUE FALTABA
            JOIN detalle_paciente_menor dp ON p_menor.Id = dp.Id_persona
            JOIN persona p_rep ON dp.Id_representante = p_rep.Id
            $donde
            ORDER BY p_menor.apellido, p_menor.nombre
            LIMIT $inicio, $registros_por_pagina";
            $resultado = $conexion->query($sql);

            if ($resultado->num_rows > 0) {
              while ($row = $resultado->fetch_assoc()) {
                // Calcular la edad a partir de la fecha de nacimiento
                $fechaNacimiento = new DateTime($row['fecha_nacimiento']);
                $hoy = new DateTime();
                $edad = $hoy->diff($fechaNacimiento)->y;

                // Separar la primera letra de la cédula y el resto del número
                $tipo_cedula = substr($row['cedula_menor'], 0, 1);
                $numero_cedula = substr($row['cedula_menor'], 1);
            ?>
                <tr>
                  <td class=""><span class="text-row text-white"><?= ($row['tipo_cedula_representante']) . "-" . ($row['cedula_representante']); ?></span></td>
                  <td class=""><span class="text-row text-white"><?= ($row['nombre_representante']) . " " . ($row['apellido_representante']); ?></span></td>
                  <td class=""><span class="text-row text-white"><?= ($row['nombre_menor']) . " " . ($row['apellido_menor']); ?></span></td>
                  <td class=""><span class="text-row text-white"><?= ($row['genero_menor']); ?></span></td>
                  <td class=""><span class="text-row text-white"><?= $edad; ?></span></td>
                  <?php if (in_array('Gestionar acciones de pacientes menores de edad', $_SESSION["permisos"])) : ?>
                    <td>
                      <?php if (in_array('Reactivar Pacientes Menores de Edad', $_SESSION["permisos"])) : ?>
                        <a href="#" data-id="<?php echo $row['id'] ?>" class="btn-sm btn-success btn-reactivar" title="Reactivar"><img src="../../../recursos/imagenes/iconos/reactivar.png" style="width:15px; height:15px;"></a>
                      <?php endif; ?>
                      <?php if (in_array('Eliminar Pacientes Menores de Edad', $_SESSION["permisos"])) : ?>
                        <a href="#" data-id="<?php echo $row['id'] ?>" class="btn-sm btn-danger btn-eliminar" title="Eliminar"><img src="../../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></a>
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
      <nav aria-label="Page navigation" style="position: fixed; bottom:0;">
        <ul class="pagination">
          <?php
          // Definimos el query string para mantener la búsqueda en los enlaces
          $query_string = ($busqueda != '') ? "&buscar=" . urlencode($busqueda) : "";

          // --- CONFIGURACIÓN DE LA VENTANA ---
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

          // Números de la ventana con el filtro incluido
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

    <div class="modal" id="ReactivarPaciente" tabindex="-1" role="dialog" aria-labelledby="ReactivarPacienteLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #dc3545; color: white;">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="ReactivarPacienteLabel">Confirmar Desactivacion</h4>
          </div>
          <div class="modal-body">
            <p>¿Está seguro de que desea reactivar este paciente</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
            <a href="#" class="btn btn-danger" id="reactivar">Aceptar</a>
          </div>
        </div>
      </div>
    </div>

    <div class="modal" id="EliminarPaciente" tabindex="-1" role="dialog" aria-labelledby="EliminarPacienteLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #dc3545; color: white;">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="EliminarPacienteLabel">Confirmar Desactivacion</h4>
          </div>
          <div class="modal-body">
            <p>¿Está seguro de que desea eliminar este paciente? Esta acción no se puede deshacer.</p>
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
    <script>
      $(document).ready(function() {

        function closeCustomModal(modalElement) {
          modalElement.removeClass('in').addClass('out');
          setTimeout(() => {
            modalElement.modal('hide').removeClass('out');
          }, 100); // Duración de la animación
        }

        // CORRECCIÓN: Eventos para cerrar el modal de aviso
        $('#ReactivarPaciente .close, #ReactivarPaciente .btn-second').on('click', function() {
          closeCustomModal($('#ReactivarPaciente'));
        });

        $('#EliminarPaciente .close, #EliminarPaciente .btn-second').on('click', function() {
          closeCustomModal($('#EliminarPaciente'));
        });

        $(document).on('click', '.btn-reactivar', function(e) {
          e.preventDefault();
          var IdPaciente = $(this).data('id');
          var urlReactivar = "../../../cfg/reactivar/reactivar_paciente_menor.php?Id=" + IdPaciente;
          $('#reactivar').attr('href', urlReactivar);
          $('#ReactivarPaciente').modal('show');
        })

        $(document).on('click', '.btn-eliminar', function(e) {
          e.preventDefault();
          var IdPaciente = $(this).data('id');
          var urlEliminar = "../../../cfg/eliminar/eliminar_paciente_menor.php?Id=" + IdPaciente;
          $('#eliminar').attr('href', urlEliminar);
          $('#EliminarPaciente').modal('show');
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