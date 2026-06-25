<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Recursos Humanos | Especialidades</title>
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
    #DesactivarEspecialidad,
    #ModalReporteEspecialidad {
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
    include('../../cfg/conexion.php');
    $sqlEspecialidad = ("SELECT * FROM especialidad WHERE estatus = 1 ORDER BY Id_especialidad ASC");
    $queryData   = mysqli_query($conexion, $sqlEspecialidad);
    $total_especialidades = mysqli_num_rows($queryData);
    ?>
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Especialidades (<?php echo $total_especialidades ?>)
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-user"></i>Especialidades</a></li>
        <li><a href="#"><i class="fa fa-book"></i>Listado</a></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div style="padding-bottom: 10px;">
        <?php if (in_array('Ver papelera de especialidades', $_SESSION["permisos"])) : ?>
          <a href="papelera/rh_especialidades_papelera_listado.php" class="btn-sm btn-primary pull-right" style="background-color: gray;"> Papelera </a>
        <?php endif; ?>
        <p class="pull-right" style="width:5px;"></p>
        <?php if (in_array('Generar Reportes de Especialidades', $_SESSION["permisos"])) : ?>
          <a href="#" class="btn-sm btn-info pull-right reporte"><i class="fa fa-book"></i> Generar Reporte </a>
        <?php endif; ?>
        <p class="pull-right" style="width:5px;"></p>
        <?php if (in_array('Crear Especialidades', $_SESSION["permisos"])) : ?>
          <a href="rh_especialidades_agregar.php" class="btn-sm btn-success pull-right"><i class="fa fa-user-plus"></i> Añadir Una Nueva Especialidad </a>
        <?php endif; ?>
        <input type="text" id="buscar" name="buscar" class="form-control pull-left" placeholder="Buscar.." value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>" style="border-radius:0; height:10%; width:250px; display:inline-block;" autocomplete="off">
      </div>
      <br><br>
      <div id="contenedorTabla">
        <table class="table table-sm table-hover mt-4" width="100%" height="20" id="t_user">
          <thead class="table-dark" style="background-color: #222; color: white; font-size: 12px;">
            <th>Nombre de la Especialidad</th>
            <th>Medicos</th>
            <?php if (in_array('Gestionar acciones de especialidades', $_SESSION["permisos"])) : ?>
              <th>Acciones</th>
            <?php endif; ?>
          </thead>
          <tbody class="tbody" width="100%" style="font-size: 12px;">
            <tr>
              <?php
              // Número de registros por página
              $busqueda = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : '';
              $registros_por_pagina = 14;
              $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
              $inicio = ($pagina_actual - 1) * $registros_por_pagina;

              // 2. Definir el filtro base
              $donde = "WHERE e.estatus = 1";
              if ($busqueda != '') {
                $donde .= " AND (e.nombre_especialidad LIKE '%$busqueda%')";
              }

              // 3. Contar el total de registros filtrados
              $sql_conteo = "SELECT COUNT(*) as total FROM especialidad e $donde";
              $resultado_conteo = mysqli_query($conexion, $sql_conteo);
              $fila_conteo = mysqli_fetch_assoc($resultado_conteo);
              $total_registros = $fila_conteo['total'];
              $total_paginas = ceil($total_registros / $registros_por_pagina);

              // Consulta para obtener los registros de la página actual
              $sql = "SELECT 
              e.*, 
              (SELECT COUNT(DISTINCT em.Id_detalle_medico) 
              FROM especialidades_medicos em 
              WHERE em.Id_especialidad = e.Id_especialidad) AS total_medicos
              FROM 
                especialidad e
              $donde
              ORDER BY e.Id_especialidad ASC
              LIMIT $inicio, $registros_por_pagina";
              $resultado = $conexion->query($sql);
              ?>
            </tr>
            <tr>
              <?php while ($row = $resultado->fetch_assoc()) { ?>
            </tr>
            <tr>
              <td class=""><span class="text-row text-white"><?= $row['nombre_especialidad']; ?></span></td>
              <td class=""><span class="text-row text-white"><?= $row['total_medicos']; ?></span></td>
              <?php if (in_array('Gestionar acciones de especialidades', $_SESSION["permisos"])) : ?>
                <td>
                  <?php if (in_array('Editar Especialidades', $_SESSION["permisos"])) : ?>
                    <a href="rh_especialidades_editar.php?Id=<?php echo $row['Id_especialidad'] ?>" class="btn-sm btn-warning" title="Editar"><img src="../../recursos/imagenes/iconos/editar.png" style="width:15px; height:15px;"></a>
                  <?php endif; ?>
                  <?php if (in_array('Desactivar Especialidades', $_SESSION["permisos"])) : ?>
                    <a href="#" data-id="<?php echo $row['Id_especialidad'] ?>" class="btn-sm btn-danger btn-desactivar" title="Desactivar"><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></a>
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

          // Parámetros de ventana para los botones
          $rango = 1;
          $inicio_ventana = max(1, $pagina_actual - $rango);
          $fin_ventana = min($total_paginas, $pagina_actual + $rango);

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

    <div class="modal" id="DesactivarEspecialidad" tabindex="-1" role="dialog" aria-labelledby="DesactivarEspecialidadLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #dc3545; color: white;">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="DesactivarEspecialidadLabel">Confirmar Desactivacion</h4>
          </div>
          <div class="modal-body">
            <p>¿Está seguro de que desea desactivar esta especialidad? Esta acción solo se puede revertir en la papelera.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
            <a href="#" id="desactivar" class="btn btn-danger">Aceptar</a>
          </div>
        </div>
      </div>
    </div>

    <div class="modal" id="ModalReporteEspecialidad" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #337ab7; color: white;">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="myModalLabel"><i class="fa fa-file-pdf-o"></i> Reportes de Especialidades Médicas</h4>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label>Seleccione el tipo de reporte:</label>
              <select class="form-control" id="tipo_reporte_especialidad">
                <option value="todas">Todas las Especialidades</option>
                <option value="activas">Solo Especialidades Activas</option>
                <option value="inactivas">Solo Especialidades Inactivas</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-primary" id="btnEjecutarReporteEspecialidad">Generar PDF</button>
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
        $('#DesactivarEspecialidad .close, #DesactivarEspecialidad .btn-second').on('click', function() {
          closeCustomModal($('#DesactivarEspecialidad'));
        });

        // Abrir modal usando la clase .reporte
        $('.reporte').on('click', function(e) {
          e.preventDefault();
          $('#ModalReporteEspecialidad').modal('show');
        });

        // Cerrar modal
        $('#ModalReporteEspecialidad .close, #ModalReporteEspecialidad .btn-second').on('click', function() {
          closeCustomModal($('#ModalReporteEspecialidad'));
        });

        // Ejecutar reporte
        $('#btnEjecutarReporteEspecialidad').on('click', function() {
          var tipo = $('#tipo_reporte_especialidad').val();
          window.open('../../cfg/reportes/generar_pdf_especialidades.php?tipo=' + tipo, '_blank');
          $('#ModalReporteEspecialidad').modal('hide');
        });

        $(document).on('click', '.btn-desactivar', function(e) {
          e.preventDefault();
          var IdEspecialidad = $(this).data('id');
          var urlDesactivar = "../../cfg/desactivar/desactivar_especialidad.php?Id=" + IdEspecialidad;
          $('#desactivar').attr('href', urlDesactivar);
          $('#DesactivarEspecialidad').modal('show');
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