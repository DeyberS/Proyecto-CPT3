<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Medicamentos | Listado</title>
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
    #DesactivarMedicamentos,
    #ModalReporteMedicamento {
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

    // Consulta para contar el total de medicamentos
    $sqlTotal = "SELECT m.estatus, COUNT(m.Id_medicamento) AS total 
                 FROM medicamento m        
                 JOIN descripcion_medicamento dm ON m.Id_medicamento = dm.Id_medicamento
                 WHERE m.estatus = 1";
    $queryTotal = mysqli_query($conexion, $sqlTotal);
    $total_medicamentos = mysqli_fetch_assoc($queryTotal)['total'];
    ?>
    <section class="content-header">
      <h1>
        Total de Medicamentos (<?php echo $total_medicamentos; ?>)
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-user"></i>Insumos</a></li>
        <li><a href="#"><i class="fa fa-book"></i>Listado</a></li>
      </ol>
    </section>

    <section class="content">
      <div style="padding-bottom: 10px;">
        <?php if (in_array('Ver papelera de medicamentos', $_SESSION["permisos"])) : ?>
          <a href="papelera/farmacia_medicamentos_papelera_listado.php" class="btn-sm btn-primary pull-right" style="background-color:gray;"> Papelera </a>
        <?php endif; ?>
        <p class="pull-right" style="width:5px;"></p>
        <?php if (in_array('Generar Reportes de Medicamentos', $_SESSION["permisos"])) : ?>
          <a href="#" class="btn-sm btn-info pull-right reporte"><i class="fa fa-book"></i> Generar Reporte </a>
        <?php endif; ?>
        <p class="pull-right" style="width:5px;"></p>
        <?php if (in_array('Crear Medicamentos', $_SESSION["permisos"])) : ?>
          <a href="farmacia_medicamentos_agregar.php" class="btn-sm btn-success pull-right"><i class="fa fa-user-plus"></i> Añadir Un Nuevo Medicamento </a>
        <?php endif; ?>
        <input type="text" id="buscar" name="buscar" class="form-control" placeholder="Escriba para buscar..." value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>" autocomplete="off">
      </div>
      <br><br>
      <div id="contenedorTabla">
        <table class="table table-sm table-hover mt-4" width="100%" height="20" id="t_user">
          <thead class="table-dark" style="background-color: #222; color: white; font-size: 12px;">
            <th>Medicamento</th>
            <th>Presentación</th>
            <th>U. Medida</th>
            <th>Via de Aplicacion</th>
            <?php if (in_array('Gestionar acciones de medicamentos', $_SESSION["permisos"])) : ?>
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

              // 1. Definir el filtro base
              $donde = "WHERE m.estatus = 1";
              if ($busqueda != '') {
                  $donde .= " AND (m.nombre_medicamento LIKE '%$busqueda%' 
                              OR p.tipo_presentacion LIKE '%$busqueda%' 
                              OR dm.via_aplicacion LIKE '%$busqueda%')";
              }

              // 2. Contar el total de registros FILTRADOS para la paginación
              $sql_conteo = "SELECT COUNT(*) as total 
                            FROM medicamento m
                            JOIN descripcion_medicamento dm ON m.Id_medicamento = dm.Id_medicamento
                            JOIN presentacion p ON dm.Id_presentacion = p.Id_presentacion
                            $donde";
              $resultado_conteo = mysqli_query($conexion, $sql_conteo);
              $fila_conteo = mysqli_fetch_assoc($resultado_conteo);
              $total_medicamentos = $fila_conteo['total'];
              $total_paginas = ceil($total_medicamentos / $registros_por_pagina);
              // Consulta principal con JOIN para obtener los datos paginados
              $sql = "SELECT 
                        m.Id_medicamento AS Id_medicamento,
                        m.nombre_medicamento,
                        dm.Id, 
                        dm.Id_presentacion, 
                        dm.Id_unidad, 
                        dm.cantidad_unidad_medida,
                        dm.via_aplicacion,
                        p.tipo_presentacion,
                        um.unidad,
                        m.estatus
                    FROM 
                        medicamento m
                    JOIN 
                        descripcion_medicamento dm ON m.Id_medicamento = dm.Id_medicamento
                    JOIN
                        presentacion p ON dm.Id_presentacion = p.Id_presentacion
                    JOIN
                        unidad_medida um ON dm.Id_unidad = um.Id_unidad_medida
                    $donde    
                    ORDER BY 
                        m.Id_medicamento ASC
                    LIMIT $inicio, $registros_por_pagina";
              $resultado = $conexion->query($sql);
              ?>
            </tr>
            <tr>
              <?php while ($row = $resultado->fetch_assoc()) { ?>
            </tr>
            <tr>
              <td class=""><span class="text-row text-white"><?= $row['nombre_medicamento']; ?></span></td>
              <td class=""><span class="text-row text-white"><?= $row['tipo_presentacion']; ?></span></td>
              <td class=""><span class="text-row text-white"><?= $row['cantidad_unidad_medida']; ?><?= $row['unidad']; ?></span></td>
              <td class=""><span class="text-row text-white"><?= $row['via_aplicacion']; ?></span></td>
              <?php if (in_array('Gestionar acciones de medicamentos', $_SESSION["permisos"])) : ?>
                <td>
                  <?php if (in_array('Editar Medicamentos', $_SESSION["permisos"])) : ?>
                    <a href="farmacia_medicamentos_editar.php?Id=<?php echo $row['Id'] ?>" class="btn-sm btn-warning" title="Editar"><img src="../../recursos/imagenes/iconos/editar.png" style="width:15px; height:15px;"></a>
                  <?php endif; ?>
                  <?php if (in_array('Desactivar Medicamentos', $_SESSION["permisos"])) : ?>
                    <a href="#" data-id="<?php echo $row['Id_medicamento'] ?>" class="btn-sm btn-danger btn-desactivar" title="Desactivar"><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></a>
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
    include('includes/footer.php');
    ?>

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

    <div class="modal" id="DesactivarMedicamentos" tabindex="-1" role="dialog" aria-labelledby="DesactivarMedicamentosLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #dc3545; color: white;">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="DesactivarMedicamentosLabel">Confirmar Desactivacion</h4>
          </div>
          <div class="modal-body">
            <p>¿Está seguro de que desea desactivar este medicamento? Esta acción solo se puede revertir en la papelera.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
            <a href="#" id="desactivar" class="btn btn-danger">Aceptar</a>
          </div>
        </div>
      </div>
    </div>

    <div class="modal" id="ModalReporteMedicamento" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #337ab7; color: white;">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="myModalLabel"><i class="fa fa-file-pdf-o"></i> Reportes de Catálogo de Medicamentos</h4>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label>Seleccione el tipo de reporte:</label>
              <select class="form-control" id="tipo_reporte_medicamento">
                <option value="todos">Catálogo Completo (Orden Alfabético)</option>
                <option value="activos">Listado de Medicamentos Activos</option>
                <option value="inactivos">Listado de Medicamentos Inactivos</option>
                <option value="presentacion">Agrupados por Tipo de Presentación</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-primary" id="btnEjecutarReporteMedicamento">Generar PDF</button>
          </div>
        </div>
      </div>
    </div>

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
        $('#DesactivarMedicamentos .close, #DesactivarMedicamentos .btn-second').on('click', function() {
          closeCustomModal($('#DesactivarMedicamentos'));
        });

        $('.reporte').on('click', function(e) {
          e.preventDefault();
          $('#ModalReporteMedicamento').modal('show');
        });

        // Cerrar el modal usando tu función closeCustomModal
        $('#ModalReporteMedicamento .close, #ModalReporteMedicamento .btn-second').on('click', function() {
          closeCustomModal($('#ModalReporteMedicamento'));
        });

        // Función para redirigir al generador PDF
        $('#btnEjecutarReporteMedicamento').on('click', function() {
          var tipo = $('#tipo_reporte_medicamento').val();
          window.open('../../cfg/reportes/generar_pdf_medicamentos.php?tipo=' + tipo, '_blank');
          $('#ModalReporteMedicamento').modal('hide');
        });

        $(document).on('click', '.btn-desactivar', function(e) {
          e.preventDefault();
          var IdMedicamentos = $(this).data('id');
          var urlDesactivar = "../../cfg/desactivar/desactivar_medicamento.php?Id=" + IdMedicamentos;
          $('#desactivar').attr('href', urlDesactivar);
          $('#DesactivarMedicamentos').modal('show');
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