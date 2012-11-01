<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Medicamentos | Papelera</title>
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
    #ReactivarMedicamento,
    #EliminarMedicamento {
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

    // Consulta para contar el total de medicamentos
    $sqlTotal = "SELECT m.estatus, COUNT(m.Id_medicamento) AS total 
                 FROM medicamento m
                 JOIN descripcion_medicamento dm ON m.Id_medicamento = dm.Id_medicamento
                 WHERE m.estatus = 0";
    $queryTotal = mysqli_query($conexion, $sqlTotal);
    $total_medicamentos = mysqli_fetch_assoc($queryTotal)['total'];
    ?>
    <section class="content-header">
      <h1>
        Medicamentos inactivos (<?php echo $total_medicamentos; ?>)
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-user"></i>Medicamentos</a></li>
        <li><a href="#"><i class="fa fa-book"></i>Papelera</a></li>
      </ol>
    </section>

    <section class="content">
      <div style="padding-bottom: 10px;">
        <a href="../farmacia_medicamentos_listado.php" class="btn-sm btn-primary pull-right"> Regresar al Listado </a>
        <input type="text" id="buscar" name="buscar" class="form-control" style="border-radius:0; height:10%; width:250px; display:inline-block;" placeholder="Escriba para buscar..." value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>" autocomplete="off">
        <p class="pull-right" style="width:5px;"></p>
        <span data-toggle="tooltip" data-placement="right" title="Aqui podras filtrar de manera avanzada la busqueda de medicamentos.">
          <button type="button" class="btn-sm btn-primary btn-sm pull-left" data-toggle="modal" data-target="#modalBusquedaAvanzadaMedicamentos">
            <i class="fa fa-filter"><img src="../../../recursos/imagenes/iconos/filtrar.png" style="width:10px; height:10px; filter:invert(1);" title="filtrar receta"></i>
          </button>
        </span>
      </div>
      <br><br>
      <div id="contenedorTabla">
        <table class="table table-sm table-hover mt-4" width="100%" height="20" id="t_user">
          <thead class="table-dark" style="background-color: #222; color: white; font-size: 12px;">
            <th>Medicamento</th>
            <th>Presentación</th>
            <th>Contenido. N</th>
            <th>Via de Aplicacion</th>
            <th>Codigo de Barras</th>
            <?php if (in_array('Gestionar acciones de medicamentos', $_SESSION["permisos"])) : ?>
              <th>Acciones</th>
            <?php endif; ?>
          </thead>
          <tbody class="tbody" width="100%" style="font-size: 12px;">
            <tr>
              <?php
              $busqueda = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : '';
              $f_presentacion = isset($_GET['f_presentacion']) ? mysqli_real_escape_string($conexion, $_GET['f_presentacion']) : '';
              $f_via = isset($_GET['f_via']) ? mysqli_real_escape_string($conexion, $_GET['f_via']) : '';
              $f_lab = isset($_GET['f_lab']) ? mysqli_real_escape_string($conexion, $_GET['f_lab']) : '';
              $f_codigo = isset($_GET['f_codigo']) ? mysqli_real_escape_string($conexion, $_GET['f_codigo']) : '';

              $registros_por_pagina = 14;
              $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
              $inicio = ($pagina_actual - 1) * $registros_por_pagina;

              // 1. Definir el filtro base
              $donde = "WHERE m.estatus = 0";
              if ($busqueda != '') {
                $donde .= " AND (m.nombre_medicamento LIKE '%$busqueda%' OR p.nombre_presentacion LIKE '%$busqueda%' OR dm.via_aplicacion LIKE '%$busqueda%')";
              }
              if ($f_presentacion != '') {
                $donde .= " AND p.nombre_presentacion = '$f_presentacion'";
              }
              if ($f_via != '') {
                $donde .= " AND dm.via_aplicacion = '$f_via'";
              }
              if ($f_lab != '') {
                $donde .= " AND lab.nombre_laboratorio LIKE '%$f_lab%'";
              }
              if ($f_codigo != '') {
                $donde .= " AND dm.codigo_barras LIKE '%$f_codigo%'";
              }

              // 2. Contar total filtrado
              $sql_conteo = "SELECT COUNT(DISTINCT m.Id_medicamento) as total 
                FROM medicamento m
                JOIN descripcion_medicamento dm ON m.Id_medicamento = dm.Id_medicamento
                JOIN presentacion p ON dm.Id_presentacion = p.Id_presentacion
                LEFT JOIN laboratorio lab ON dm.Id_laboratorio = lab.Id_laboratorio
                $donde";
              $resultado_conteo = mysqli_query($conexion, $sql_conteo);
              $total_paginas = ceil(mysqli_fetch_assoc($resultado_conteo)['total'] / $registros_por_pagina);

              // 3. Consulta principal
              $sql = "SELECT m.Id_medicamento AS Id_medicamento, m.nombre_medicamento, dm.Id, 
                dm.Id_presentacion, dm.contenido_neto, dm.via_aplicacion, p.nombre_presentacion, 
                dm.codigo_barras, lab.nombre_laboratorio,
                GROUP_CONCAT(CONCAT(IFNULL(pa.nombre,''), ' ', IFNULL(dpm.cantidad_unidad_medida,''), IFNULL(um.unidad,'')) SEPARATOR ' + ') AS componentes, m.estatus
                FROM medicamento m
                JOIN descripcion_medicamento dm ON m.Id_medicamento = dm.Id_medicamento
                INNER JOIN presentacion p ON dm.Id_presentacion = p.Id_presentacion     
                INNER JOIN detalle_principio_medicamento dpm ON dm.Id = dpm.id_medicamento
                INNER JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida
                INNER JOIN principio_activo pa ON dpm.id_principio_activo = pa.Id_principio_activo
                LEFT JOIN laboratorio lab ON dm.Id_laboratorio = lab.Id_laboratorio
                $donde  
                GROUP BY m.Id_medicamento  
                ORDER BY m.Id_medicamento ASC
                LIMIT $inicio, $registros_por_pagina";
              $resultado = $conexion->query($sql);
              ?>
            </tr>
            <tr>
              <?php
              if ($resultado->num_rows > 0) {
              while ($row = $resultado->fetch_assoc()) { 
              ?>
            </tr>
            <tr>
              <td class=""><span class="text-row text-white"><?= $row['nombre_medicamento']; ?> (<?= $row['componentes']; ?>)</span></td>
              <td class=""><span class="text-row text-white"><?= $row['nombre_presentacion']; ?></span></td>
              <td class=""><span class="text-row text-white"><?= $row['contenido_neto']; ?></span></td>
              <td class=""><span class="text-row text-white"><?= $row['via_aplicacion']; ?></span></td>
              <td class=""><span class="text-row text-white"><?= $row['codigo_barras']; ?></span></td>
              <?php if (in_array('Gestionar acciones de medicamentos', $_SESSION["permisos"])) : ?>
                <td>
                  <?php if (in_array('Reactivar Medicamentos', $_SESSION["permisos"])) : ?>
                    <a href="#" data-id="<?php echo $row['Id_medicamento'] ?>" class="btn-sm btn-success btn-reactivar" title="Reactivar"><img src="../../../recursos/imagenes/iconos/reactivar.png" style="width:15px; height:15px;"></a>
                  <?php endif; ?>
                  <?php if (in_array('Eliminar Medicamentos', $_SESSION["permisos"])) : ?>
                    <a href="#" data-id="<?php echo $row['Id'] ?>" class="btn-sm btn-danger btn-eliminar" title="Eliminar"><img src="../../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></a>
                  <?php endif; ?>
                </td>
              <?php endif; ?>
            </tr>
            <?php }
            } else {
              echo "<tr><td colspan='6'>No se encontraron medicamentos inactivos.</td></tr>";
            } ?>
          </tbody>
        </table>
      </div>
       <nav id="contenedorPaginacion" aria-label="Page navigation" style="position: fixed; bottom:0;">
        <ul class="pagination">
          <?php
          $query_string = ($busqueda != '') ? "&buscar=" . urlencode($busqueda) : "";

          // Botón Primero y Anterior
          if ($pagina_actual > 1) : ?>
            <li><a href="?pagina=1<?= $query_string; ?>">&laquo;&laquo;</a></li>
            <li><a href="?pagina=<?= ($pagina_actual - 1) . $query_string; ?>">&laquo;</a></li>
          <?php endif;

          // Rango de páginas a mostrar
          $rango = 1;
          $inicio_ventana = max(1, $pagina_actual - $rango);
          $fin_ventana = min($total_paginas, $pagina_actual + $rango);

          for ($i = $inicio_ventana; $i <= $fin_ventana; $i++) : ?>
            <li class="<?= ($i == $pagina_actual) ? 'active' : ''; ?>">
              <a href="?pagina=<?= $i . $query_string; ?>"><?= $i; ?></a>
            </li>
          <?php endfor;

          // Botón Siguiente y Último
          if ($pagina_actual < $total_paginas) : ?>
            <li><a href="?pagina=<?= ($pagina_actual + 1) . $query_string; ?>">&raquo;</a></li>
            <li><a href="?pagina=<?= $total_paginas . $query_string; ?>">&raquo;&raquo;</a></li>
          <?php endif; ?>
        </ul>
      </nav>
    </section>

    <div class="modal" id="ReactivarMedicamento" tabindex="-1" role="dialog" aria-labelledby="ReactivarMedicamentoLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #dc3545; color: white;">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="ReactivarMedicamentoLabel">Confirmar Desactivacion</h4>
          </div>
          <div class="modal-body">
            <p>¿Está seguro de que desea reactivar este medicamento?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
            <a href="#" class="btn btn-danger" id="reactivar">Aceptar</a>
          </div>
        </div>
      </div>
    </div>

    <div class="modal" id="EliminarMedicamento" tabindex="-1" role="dialog" aria-labelledby="EliminarMedicamentoLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #dc3545; color: white;">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="EliminarMedicamentoLabel">Confirmar Desactivacion</h4>
          </div>
          <div class="modal-body">
            <p>¿Está seguro de que desea eliminar este medicamento? Esta acción no se puede deshacer.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
            <a href="#" class="btn btn-danger" id="eliminar">Aceptar</a>
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

    <div class="modal fade" id="modalBusquedaAvanzadaMedicamentos" tabindex="-1" role="dialog" aria-labelledby="modalFiltrosLabel">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <form id="formBusquedaAvanzada" method="GET" action="">
            <div class="modal-header bg-primary">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title" id="modalFiltrosLabel"><i class="fa fa-filter"></i> Filtros Avanzados de Medicamentos</h4>
            </div>
            <div class="modal-body">
              <div class="row">
                <div class="col-md-4 form-group">
                  <label>Presentación</label>
                  <select name="f_presentacion" class="form-control">
                    <option value="">Todas</option>
                    <?php
                    $res_pres = $conexion->query("SELECT nombre_presentacion FROM presentacion WHERE estatus = '1' ORDER BY nombre_presentacion ASC");
                    while ($pr = $res_pres->fetch_assoc()) {
                      echo "<option value='" . htmlspecialchars($pr['nombre_presentacion']) . "'>" . htmlspecialchars($pr['nombre_presentacion']) . "</option>";
                    }
                    ?>
                  </select>
                </div>
                <div class="col-md-4 form-group">
                  <label>Vía de Aplicación</label>
                  <select name="f_via" class="form-control">
                    <option value="">Todas</option>
                    <option value="Oral">Oral</option>
                    <option value="Intravenosa">Intravenosa</option>
                    <option value="Intramuscular">Intramuscular</option>
                    <option value="Tópica">Tópica</option>
                    <option value="Oftálmica">Oftálmica</option>
                  </select>
                </div>
                <div class="col-md-4 form-group">
                  <label>Laboratorio</label>
                  <input type="text" name="f_lab" class="form-control" placeholder="Nombre del laboratorio...">
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 form-group">
                  <label>Código de Barras</label>
                  <input type="number" name="f_codigo" class="form-control" placeholder="Ej: 274898248...">
                </div>
                <div class="col-md-6 form-group">
                  <label>Búsqueda General</label>
                  <input type="text" name="buscar" id="buscar_modal_med" class="form-control" placeholder="Medicamento...">
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default pull-left" onclick="limpiarFiltrosAjax()">Limpiar Filtros</button>
              <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
              <button type="submit" class="btn btn-success"><i class="fa fa-search"></i> Aplicar Búsqueda</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <?php
    include('../includes/footer.php');
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
        $('#ReactivarMedicamento .close, #ReactivarMedicamento .btn-second').on('click', function() {
          closeCustomModal($('#ReactivarMedicamento'));
        });

        $('#EliminarMedicamento .close, #EliminarMedicamento .btn-second').on('click', function() {
          closeCustomModal($('#EliminarMedicamento'));
        });

        $(document).on('click', '.btn-reactivar', function(e) {
          e.preventDefault();
          var IdMedicamento = $(this).data('id');
          var urlReactivar = "../../../cfg/reactivar/reactivar_medicamento.php?Id=" + IdMedicamento;
          $('#reactivar').attr('href', urlReactivar);
          $('#ReactivarMedicamento').modal('show');
        })

        $(document).on('click', '.btn-eliminar', function(e) {
          e.preventDefault();
          var IdMedicamento = $(this).data('id');
          var urlEliminar = "../../../cfg/eliminar/eliminar_medicamento.php?Id=" + IdMedicamento;
          $('#eliminar').attr('href', urlEliminar);
          $('#EliminarMedicamento').modal('show');
        })

        // ==========================================
        // LÓGICA AJAX BÚSQUEDA Y PAGINACIÓN (MEDICAMENTOS)
        // ==========================================
        window.cargarDatosAjax = function(url) {
          // Ajuste de selector: usamos #contenedorTabla ya que es el ID que envuelve tu tabla
          $('#contenedorTabla').css('opacity', '0.4');

          $.get(url, function(data) {
            var htmlParsed = $(data);

            // Actualizamos el contenido de la tabla y la paginación
            $('#contenedorTabla').html(htmlParsed.find('#contenedorTabla').html()).css('opacity', '1');

            // Ajuste de selector: 'nav[aria-label="Page navigation"]' para capturar la paginación correctamente
            $('nav[aria-label="Page navigation"]').html(htmlParsed.find('nav[aria-label="Page navigation"]').html());

            window.history.pushState(null, '', url);
          }).fail(function() {
            alert("Error de conexión al aplicar filtros.");
            $('#contenedorTabla').css('opacity', '1');
          });
        };

        let timer;
        // Desvinculamos el evento global del footer para evitar conflictos
        $('#buscar').off('keyup');

        $('#buscar').on('keyup', function() {
          clearTimeout(timer);
          let query = $(this).val();

          // Sincroniza con el input dentro del modal de filtros
          $('#buscar_modal_med').val(query);

          timer = setTimeout(function() {
            // Serializamos todo el formulario de filtros avanzados para no perder ningún parámetro
            var filtrosActuales = $('#formBusquedaAvanzada').serialize();
            var url = 'farmacia_medicamentos_papelera_listado.php?' + filtrosActuales;
            cargarDatosAjax(url);
          }, 400);
        });

        $('#formBusquedaAvanzada').on('submit', function(e) {
          e.preventDefault();
          var url = 'farmacia_medicamentos_papelera_listado.php?' + $(this).serialize();
          $('#modalBusquedaAvanzadaMedicamentos').modal('hide');
          cargarDatosAjax(url);
        });

        // Corregido: Delegación de eventos para la paginación
        $(document).on('click', 'nav[aria-label="Page navigation"] .pagination a', function(e) {
          e.preventDefault();
          var url = $(this).attr('href');
          if (url) cargarDatosAjax(url);
        });

        window.limpiarFiltrosAjax = function() {
          $('#formBusquedaAvanzada')[0].reset();
          $('#buscar').val('');
          $('#buscar_modal_med').val('');
          var urlLimpia = window.location.href.split('?')[0];
          cargarDatosAjax(urlLimpia);
          $('#modalBusquedaAvanzadaMedicamentos').modal('hide');
        };

        // Script para mostrar los modales de sesión
        <?php if ($mostrar_modal_exito) : ?>
          $('#modalExito').modal('show');
        <?php elseif ($mostrar_modal_error) : ?>
          $('#modalError').modal('show');
        <?php endif; ?>
      });
    </script>

</html>