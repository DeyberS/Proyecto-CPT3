<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Patologias | Papelera</title>
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
    #ReactivarPatologia,
    #EliminarPatologia {
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
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <?php
    include('../../../cfg/conexion.php');
    $sqlPatologia = ("SELECT * FROM patologias WHERE estatus = 0 ORDER BY Id_patologia ASC");
    $queryData   = mysqli_query($conexion, $sqlPatologia);
    $total_patologia = mysqli_num_rows($queryData);
    ?>
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Patologias Inactivas (<?php echo $total_patologia; ?>)
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-user"></i>Patologias</a></li>
        <li><a href="#"><i class="fa fa-book"></i>Papelera</a></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div style="padding-bottom: 10px;">
        <a href="../salud_patologias_listado.php" class="btn-sm btn-primary pull-right"> Regresar al Listado </a>
        <input type="text" id="buscar" name="buscar" class="form-control" placeholder="Escriba para buscar..." value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>" style="border-radius:0; height:10%; width:250px; display:inline-block;" autocomplete="off">
      </div>
      <br><br>
      <div id="contenedorTabla">
        <table class="table table-sm table-hover mt-4" width="100%" height="20" id="t_user">
          <thead class="table-dark" style="background-color: #222; color: white; font-size: 12px;">
            <th>Nombre de la Patologia</th>
            <th>Codigo CIE-10</th>
            <th>Patologias</th>
            <th>Contagioso</th>
            <?php if (in_array('Gestionar acciones de patologias', $_SESSION["permisos"])) : ?>
              <th>Acciones</th>
            <?php endif; ?>
          </thead>
          <tbody class="tbody" width="100%" style="font-size: 12px;">
            <tr>
              <?php
              // Número de registros por página
              $busqueda = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : '';

              // 2. Configuración de paginación
              $registros_por_pagina = 14;
              $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
              $inicio = ($pagina_actual - 1) * $registros_por_pagina;

              // 3. Definir el filtro base (Patologías en papelera/estatus 0)
              $donde = "WHERE estatus = 0";
              if ($busqueda != '') {
                $donde .= " AND (nombre_patologia LIKE '%$busqueda%')";
              }

              // 4. Conteo de registros filtrados (necesario para recalcular botones de página)
              $sql_conteo = "SELECT COUNT(*) as total FROM patologias $donde";
              $resultado_conteo = mysqli_query($conexion, $sql_conteo);
              $fila_conteo = mysqli_fetch_assoc($resultado_conteo);
              $total_permisos = $fila_conteo['total']; // Mantenemos el nombre de variable original del archivo
              $total_paginas = ceil($total_permisos / $registros_por_pagina);

              // Consulta para obtener los registros de la página actual
              $sql = "SELECT 
              p.*, 
              (SELECT COUNT(DISTINCT hp.Id_persona) 
              FROM historial_patologias hp 
              WHERE hp.Id_patologia = p.Id_patologia) AS total_pacientes
              FROM 
                patologias p 
              $donde
              ORDER BY p.Id_patologia ASC
              LIMIT $inicio, $registros_por_pagina";
              $resultado = $conexion->query($sql);
              ?>
            </tr>
            <tr>
              <?php while ($row = $resultado->fetch_assoc()) { ?>
            </tr>
            <tr>
              <td class=""><span class="text-row text-white"><?= $row['nombre_patologia']; ?></span></td>
              <td class=""><span class="text-row text-white"><?= $row['codigo_cie']; ?></span></td>
              <td class=""><span class="text-row text-white"><?= $row['total_pacientes']; ?></span></td>
              <td class=""><span class="text-row text-white"></span><?= $row['contagioso']; ?></td>
              <?php if (in_array('Gestionar acciones de patologias', $_SESSION["permisos"])) : ?>
                <td>
                  <?php if (in_array('Reactivar Patologias', $_SESSION["permisos"])) : ?>
                    <a href="#" data-id="<?php echo $row['Id_patologia'] ?>" class="btn-sm btn-success btn-reactivar" title="Reactivar"><img src="../../../recursos/imagenes/iconos/reactivar.png" style="width:15px; height:15px;"></a>
                  <?php endif; ?>
                  <?php if (in_array('Eliminar Patologias', $_SESSION["permisos"])) : ?>
                    <a href="#" data-id="<?php echo $row['Id_patologia'] ?>" class="btn-sm btn-danger btn-eliminar" title="Eliminar"><img src="../../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></a>
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
          // String para persistir la búsqueda en los clics de la paginación
          $query_string = ($busqueda != '') ? "&buscar=" . urlencode($busqueda) : "";

          $rango = 1; // Número de páginas a mostrar a los lados de la actual
          $inicio_ventana = max(1, $pagina_actual - $rango);
          $fin_ventana = min($total_paginas, $pagina_actual + $rango);

          // Botones de navegación
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

    <div class="modal" id="ReactivarPatologia" tabindex="-1" role="dialog" aria-labelledby="ReactivarPatologiaLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #dc3545; color: white;">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="ReactivarPatologiaLabel">Confirmar Desactivacion</h4>
          </div>
          <div class="modal-body">
            <p>¿Está seguro de que desea reactivar esta patologia</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
            <a href="#" class="btn btn-danger" id="reactivar">Aceptar</a>
          </div>
        </div>
      </div>
    </div>

    <div class="modal" id="EliminarPatologia" tabindex="-1" role="dialog" aria-labelledby="EliminarPatologiaLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #dc3545; color: white;">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="EliminarPatologiaLabel">Confirmar Desactivacion</h4>
          </div>
          <div class="modal-body">
            <p>¿Está seguro de que desea eliminar esta patologia? Esta acción no se puede deshacer.</p>
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
        $('#ReactivarPatologia .close, #ReactivarPatologia .btn-second').on('click', function() {
          closeCustomModal($('#ReactivarPatologia'));
        });

        $('#EliminarPatologia .close, #EliminarPatologia .btn-second').on('click', function() {
          closeCustomModal($('#EliminarPatologia'));
        });

        $(document).on('click', '.btn-reactivar', function(e) {
          e.preventDefault();
          var IdPatologia = $(this).data('id');
          var urlReactivar = "../../../cfg/reactivar/reactivar_patologia.php?Id=" + IdPatologia;
          $('#reactivar').attr('href', urlReactivar);
          $('#ReactivarPatologia').modal('show');
        })

        $(document).on('click', '.btn-eliminar', function(e) {
          e.preventDefault();
          var IdPatologia = $(this).data('id');
          var urlEliminar = "../../../cfg/eliminar/eliminar_patologia.php?Id=" + IdPatologia;
          $('#eliminar').attr('href', urlEliminar);
          $('#EliminarPatologia').modal('show');
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