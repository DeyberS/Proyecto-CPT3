<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Consultas | Papelera</title>
  <?php
  // Iniciar la sesión para poder acceder a las variables de mensaje
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
    #ReactivarConsulta,
    #EliminarConsulta {
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

    // 1. Consulta para contar el total de consultas
    $sqlConsultas = "SELECT COUNT(id_consulta) AS total_consultas FROM consulta WHERE estatus = 0";
    $queryData   = mysqli_query($conexion, $sqlConsultas);
    $total_consultas = mysqli_fetch_assoc($queryData)['total_consultas'];
    ?>
    <section class="content-header">
      <h1>
        Consultas Inactivas (<?php echo $total_consultas; ?>)
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-user"></i>Consultas</a></li>
        <li><a href="#"><i class="fa fa-book"></i>Papelera</a></li>
      </ol>
    </section>

    <section class="content">
      <div style="padding-bottom: 10px;">
        <a href="../consulta_listado.php" class="btn-sm btn-primary pull-right"> Regresar al Listado</a>
        <input type="text" id="buscar" name="buscar" class="form-control" placeholder="Escriba para buscar..." value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>" style="border-radius:0; height:10%; width:250px; display:inline-block;" autocomplete="off">
      </div>
      <br><br>
      <div id="contenedorTabla">
        <table class="table table-sm table-hover mt-4" width="100%" height="20" id="t_user">
          <thead class="table-dark" style="background-color: #222; color: white; font-size: 12px;">
            <th>Paciente</th>
            <th>Médico</th>
            <th>Motivo de Consulta</th>
            <th>Diagnóstico</th>
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

            // 2. Construir Filtro Dinámico
            $donde = "WHERE c.estatus = 0";
            if ($busqueda != '') {
              $donde .= " AND (p_paciente.nombre LIKE '%$busqueda%' 
                            OR p_paciente.apellido LIKE '%$busqueda%' 
                            OR p_medico.nombre LIKE '%$busqueda%' 
                            OR p_medico.apellido LIKE '%$busqueda%'
                            OR c.motivo_consulta LIKE '%$busqueda%'
                            OR c.diagnostico LIKE '%$busqueda%')";
            }

            // 3. Conteo de registros filtrados
            $sql_conteo = "SELECT COUNT(*) as total 
                          FROM consulta c
                          JOIN persona p_paciente ON c.Id_paciente = p_paciente.id
                          JOIN persona p_medico ON c.Id_medico = p_medico.id 
                          $donde";
            $resultado_conteo = mysqli_query($conexion, $sql_conteo);
            $total_registros = mysqli_fetch_assoc($resultado_conteo)['total'];
            $total_paginas = ceil($total_registros / $registros_por_pagina);
            // Consulta para obtener los registros de la página actual
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
                  <td class=""><span class="text-row text-white"><?= substr($row['motivo_consulta'], 0, 50) . (strlen($row['motivo_consulta']) > 50 ? '...' : ''); ?></span></td>
                  <td class=""><span class="text-row text-white"><?= substr($row['diagnostico'], 0, 50) . (strlen($row['diagnostico']) > 50 ? '...' : ''); ?></span></td>
                  <td class=""><span class="text-row text-white"><?= date('d/m/Y', strtotime($row['fecha_consulta'])); ?></span></td>
                  <?php if (in_array('Gestionar acciones de consultas', $_SESSION["permisos"])) : ?>
                    <td>
                      <?php if (in_array('Reactivar Consultas', $_SESSION["permisos"])) : ?>
                        <a href="#" data-id="<?php echo $row['id_consulta'] ?>" class="btn-sm btn-success btn-reactivar" title="Reactivar Consulta"><img src="../../../recursos/imagenes/iconos/reactivar.png" style="width:15px; height:15px;"></a>
                      <?php endif; ?>
                      <?php if (in_array('Eliminar Consultas', $_SESSION["permisos"])) : ?>
                        <a href="#" data-id="<?php echo $row['id_consulta'] ?>" class="btn-sm btn-danger btn-eliminar" title="Eliminar Consulta"><img src="../../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></a>
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
          $query_string = ($busqueda != '') ? "&buscar=" . urlencode($busqueda) : "";
          
          $rango = 1;
          $inicio_ventana = max(1, $pagina_actual - $rango);
          $fin_ventana = min($total_paginas, $pagina_actual + $rango);

          if ($pagina_actual == 1) $fin_ventana = min($total_paginas, 3);
          if ($pagina_actual == $total_paginas) $inicio_ventana = max(1, $total_paginas - 2);

          if ($pagina_actual > 1) : ?>
            <li><a href="?pagina=1<?= $query_string; ?>">&laquo;&laquo;</a></li>
            <li><a href="?pagina=<?= ($pagina_actual - 1) . $query_string; ?>">&laquo;</a></li>
          <?php endif;

          for ($i = $inicio_ventana; $i <= $fin_ventana; $i++) : ?>
            <li class="<?= ($i == $pagina_actual) ? 'active' : ''; ?>">
              <a href="?pagina=<?= $i . $query_string; ?>"><?= $i; ?></a>
            </li>
          <?php endfor;

          if ($pagina_actual < $total_paginas) : ?>
            <li><a href="?pagina=<?= ($pagina_actual + 1) . $query_string; ?>">&raquo;</a></li>
            <li><a href="?pagina=<?= $total_paginas . $query_string; ?>">&raquo;&raquo;</a></li>
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

    <div class="modal" id="ReactivarConsulta" tabindex="-1" role="dialog" aria-labelledby="ReactivarConsultaLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #dc3545; color: white;">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="ReactivarConsultaLabel">Confirmar Desactivacion</h4>
          </div>
          <div class="modal-body">
            <p>¿Está seguro de que desea reactivar esta consulta?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
            <a href="#" class="btn btn-danger" id="reactivar">Aceptar</a>
          </div>
        </div>
      </div>
    </div>

    <div class="modal" id="EliminarConsulta" tabindex="-1" role="dialog" aria-labelledby="EliminarConsultaLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #dc3545; color: white;">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="EliminarConsultaLabel">Confirmar Desactivacion</h4>
          </div>
          <div class="modal-body">
            <p>¿Está seguro de que desea eliminar esta consulta? Esta acción no se puede deshacer.</p>
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

    <script>
      $(document).ready(function() {

        function closeCustomModal(modalElement) {
          modalElement.removeClass('in').addClass('out');
          setTimeout(() => {
            modalElement.modal('hide').removeClass('out');
          }, 100); // Duración de la animación
        }

        // CORRECCIÓN: Eventos para cerrar el modal de aviso
        $('#ReactivarConsulta .close, #ReactivarConsulta .btn-second').on('click', function() {
          closeCustomModal($('#ReactivarConsulta'));
        });

        $('#EliminarConsulta .close, #EliminarConsulta .btn-second').on('click', function() {
          closeCustomModal($('#EliminarConsulta'));
        });

        $(document).on('click', '.btn-reactivar', function(e) {
          e.preventDefault();
          var IdConsulta = $(this).data('id');
          var urlReactivar = "../../../cfg/reactivar/reactivar_consulta.php?Id=" + IdConsulta;
          $('#reactivar').attr('href', urlReactivar);
          $('#ReactivarConsulta').modal('show');
        })

        $(document).on('click', '.btn-eliminar', function(e) {
          e.preventDefault();
          var IdConsulta = $(this).data('id');
          var urlEliminar = "../../../cfg/eliminar/eliminar_consulta.php?Id=" + IdConsulta;
          $('#eliminar').attr('href', urlEliminar);
          $('#EliminarConsulta').modal('show');
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