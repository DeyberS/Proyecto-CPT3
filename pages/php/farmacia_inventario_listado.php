<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Farmacia | Inventario</title>
  <?php
  // 1. Incluir encabezado y navegación
  include('includes/headerNav2.php');

  // 2. INCLUIR LA CONEXIÓN A LA BASE DE DATOS
  include("../../cfg/conexion.php");
  ?>
</head>
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
  #DesactivarLote,
  #ModalReporteInventario,
  #ModalConfirmarEliminar {
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

  .btn-disabled {
    background-color: gray;
  }
</style>

<body>
  <div class="content-wrapper">
    <?php
    // --- LÓGICA DE PAGINACIÓN Y BÚSQUEDA ---
    $registros_por_pagina = 9;
    $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $inicio = ($pagina_actual - 1) * $registros_por_pagina;

    // Capturar término de búsqueda para filtrar la consulta
    $busqueda = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : '';

    // Definir el filtro dinámico
    $donde = "WHERE 1=1";
    if ($busqueda != '') {
      $donde .= " AND (m.nombre_medicamento LIKE '%$busqueda%' 
                  OR p.nombre LIKE '%$busqueda%' 
                  OR l.Lote LIKE '%$busqueda%' 
                  OR tm.nombre LIKE '%$busqueda%')";
    }

    // Contar el total de registros filtrados para que la paginación sea exacta
    $sql_conteo = "SELECT COUNT(*) as total 
                 FROM medicamentos_detalle_inventario mdi
                 INNER JOIN detalle_inventario di ON mdi.Id_detalle_inventario = di.Id_detalle_inventario
                 INNER JOIN lotes_medicamentos l ON mdi.Id_lote = l.Id
                 INNER JOIN descripcion_medicamento dm ON l.Id_descripcion_medicamento = dm.Id
                 INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
                 INNER JOIN tipo_movimiento tm ON di.Id_tipoMovimiento = tm.Id_tipo_movimiento
                 INNER JOIN persona p ON di.Id_persona = p.Id
                 $donde";

    $resultado_conteo = mysqli_query($conexion, $sql_conteo);
    $fila_conteo = mysqli_fetch_assoc($resultado_conteo);
    $total_registros = $fila_conteo['total'];
    $total_paginas = ceil($total_registros / $registros_por_pagina);
    ?>

    <section class="content-header">
      <h1>
        Inventario (<?php echo $total_registros; ?> Movimientos)
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-user"></i>Inventario</a></li>
        <li class="active"><a href="#"><i class="fa fa-book"></i>Listado</a></li>
      </ol>
    </section>

    <section class="content">
      <div style="padding-bottom: 10px;">
        <?php if (in_array('Generar Reportes de Inventario', $_SESSION["permisos"])) : ?>
          <a href="#" class="btn-sm btn-info pull-right reporte"><i class="fa fa-book"></i> Generar Reporte </a>
        <?php endif; ?>
        <p class="pull-right" style="width:5px;"></p>
        <?php if (in_array('Generar Salidas de Inventario', $_SESSION["permisos"])) : ?>
          <a href="farmacia_inventario_movimiento_salida.php?op=salida" class="btn-sm btn-danger pull-right"><i class="fa fa-arrow-down"></i> Salida</a>
        <?php endif; ?>
        <p class="pull-right" style="width:5px;"></p>
        <?php if (in_array('Generar Entradas de Inventario', $_SESSION["permisos"])) : ?>
          <a href="farmacia_inventario_movimiento_entrada.php?op=entrada" class="btn-sm btn-success pull-right"><i class="fa fa-arrow-up"></i> Entrada </a>
        <?php endif; ?>
        <p class="pull-right" style="width:5px;"></p>
        <?php if (in_array('Ajustar Stock de Medicamentos', $_SESSION["permisos"])) : ?>
          <a href="farmacia_inventario_ajustes.php?op=ajuste" class="btn-sm btn-warning pull-right"><i class="fa fa-cog"></i> Ajuste de Stock </a>
        <?php endif; ?>
        <p class="pull-right" style="width:5px;"></p>
        <?php if (in_array('Ajustar Stock de Medicamentos', $_SESSION["permisos"])) : ?>
          <a href="farmacia_prescripciones_listado.php" class="btn-sm btn-primary pull-right"><i class="fa fa-cog"></i> Ver Recetas </a>
        <?php endif; ?>
        <input type="text" id="buscar" name="buscar" class="form-control pull-left" placeholder="Escriba para buscar..." value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>" style="width: 200px;" autocomplete="off">
      </div>
      <br><br>

      <div id="contenedorTabla">
        <table class="table table-sm table-hover mt-4" width="100%" id="t_user">
          <thead class="table-dark" style="background-color: #222; color: white; font-size: 12px;">
            <tr>
              <th>Responsable</th>
              <th>Medicamento</th>
              <th>Existencia</th>
              <th>Stock Mín/Máx</th>
              <th>N. Lote</th>
              <th>Cantidad</th>
              <th>Movimiento</th>
              <th>Fecha</th>
              <?php if (in_array('Gestionar acciones de inventario', $_SESSION["permisos"])) : ?>
                <th>Acciones</th>
              <?php endif; ?>
            </tr>
          </thead>
          <tbody class="tbody" style="font-size: 12px;">
            <?php
            // 3. CONSULTA SQL CON PAGINACIÓN Y SUB-QUERY DE EXISTENCIA TOTAL
            $sql = "SELECT
                m.Id_medicamento, 
                m.nombre_medicamento,
                p.nombre,
                tdm.nombre_presentacion,
                GROUP_CONCAT(CONCAT(IFNULL(pa.nombre,''), ' ', IFNULL(dpm.cantidad_unidad_medida,''), IFNULL(um.unidad,'')) SEPARATOR ' + ') AS componentes,
                l.Lote,
                tm.nombre as tipo_nom,
                mdi.cantidad,
                mdi.stock_momento,
                di.fecha,
                di.Id_detalle_inventario,
                dm.stock_minimo,
                dm.stock_maximo
            FROM medicamentos_detalle_inventario mdi
            INNER JOIN detalle_inventario di ON mdi.Id_detalle_inventario = di.Id_detalle_inventario
            INNER JOIN lotes_medicamentos l ON mdi.Id_lote = l.Id
            INNER JOIN descripcion_medicamento dm ON l.Id_descripcion_medicamento = dm.Id
            INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
            INNER JOIN presentacion tdm ON dm.Id_presentacion = tdm.Id_presentacion
            INNER JOIN detalle_principio_medicamento dpm ON dm.Id = dpm.id_medicamento
            INNER JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida
            INNER JOIN principio_activo pa ON dpm.id_principio_activo = pa.Id_principio_activo
            INNER JOIN tipo_movimiento tm ON di.Id_tipoMovimiento = tm.Id_tipo_movimiento
            INNER JOIN persona p ON di.Id_persona = p.Id
            $donde
            GROUP BY mdi.Id
            ORDER BY di.fecha DESC
            LIMIT $inicio, $registros_por_pagina";

            $resultado = $conexion->query($sql);
            $contador = 0;
            if ($resultado && $resultado->num_rows > 0) {
              while ($row = $resultado->fetch_assoc()) {
                $clase_badge = (strcasecmp($row['tipo_nom'], 'Entrada') == 0) ? 'bg-green' : 'bg-crimson';
                $es_entrada = (strcasecmp($row['tipo_nom'], 'Entrada') == 0);
                $simbolo = $es_entrada ? '+' : '-';
                $color_texto = $es_entrada ? 'text-green' : 'text-red'; // Opcional: para dar color al número
            ?>
                <tr>
                  <td><small class="text-row text-white"><?= htmlspecialchars($row['nombre']); ?></small></td>
                  <td><small class="text-row text-white"><?= htmlspecialchars($row['nombre_medicamento'] . " (" . $row['componentes'] . ")"); ?></small></td>
                  <td><small class="text-row text-white"><?= htmlspecialchars($row['stock_momento']); ?></small></td>
                  <td><small class="text-row text-white"><?= "Min: " . $row['stock_minimo'] . " / Max: " . $row['stock_maximo']; ?></small></td>
                  <td><small class="text-row text-white"><?= htmlspecialchars($row['Lote']); ?></small></td>
                  <td><small class="text-row text-white"><strong><?= $simbolo . $row['cantidad']; ?></strong></small></td>
                  <td><small class="badge <?= $clase_badge; ?>"><?= htmlspecialchars($row['tipo_nom']); ?></small></td>
                  <td><small class="text-row text-white"><?= date("d/m/Y H:i", strtotime($row['fecha'])); ?></small></td>
                  <?php if (in_array('Gestionar acciones de inventario', $_SESSION["permisos"])) : ?>
                    <td>
                      <?php if (in_array('Ver Consultas', $_SESSION["permisos"])) : ?>
                        <a href="farmacia_inventario_ver_kardex.php?id=<?php echo $row['Id_medicamento'] ?>" class="btn-sm btn-success" title="Ver Kardex"><img src="../../recursos/imagenes/iconos/Consulta-Reporte-w.png" style="width:15px; height:15px;"></a>
                      <?php endif; ?>
                      <?php if (in_array('Ver Consultas', $_SESSION["permisos"])) : ?>
                        <a href="farmacia_inventario_ver_movimiento.php?id=<?php echo $row['Id_detalle_inventario'] ?>" class="btn-sm btn-info" title="Ver Movimiento"><img src="../../recursos/imagenes/iconos/info.png" style="width:15px; height:15px;"></a>
                      <?php endif; ?>
                      <?php if (in_array('Editar Movimientos de Inventario', $_SESSION["permisos"])) : ?>
                        <a href="../../cfg/redireccion_movimiento_editar.php?id=<?= $row['Id_detalle_inventario']; ?>" title="Editar" class="btn-sm btn-warning"><img src="../../recursos/imagenes/iconos/editar.png" style="width:15px; height:15px;"></a>
                      <?php endif; ?>
                      <?php if (in_array('Eliminar Movimientos de Inventario', $_SESSION["permisos"])) : ?>
                        <?php if ($contador === 0 && $pagina_actual === 1) : ?>
                          <a href="javascript:void(0);" onclick="confirmarEliminar(<?= $row['Id_detalle_inventario']; ?>)" title="Eliminar" class="btn-sm btn-danger"><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></a>
                        <?php else : ?>
                          <a href="#" class="btn-sm btn-disabled"><img src="../../recursos/imagenes/iconos/Delete.png" title="No se pueden eliminar registros antiguos, realice un ajuste" style="width:15px; height:15px;"></a>
                        <?php endif; ?>
                      <?php endif; ?>
                    </td>
                  <?php endif; ?>
                </tr>
            <?php $contador++;
              }
            } else {
              echo '<tr><td colspan="8" class="text-center">No hay movimientos registrados.</td></tr>';
            }
            ?>
          </tbody>
        </table>
      </div>

      <nav aria-label="Page navigation" style="position: fixed; bottom:0;">
        <ul class="pagination">
          <?php
          // Mantener el término de búsqueda en los enlaces de paginación
          $query_string = ($busqueda != '') ? "&buscar=" . urlencode($busqueda) : "";

          // Botón Primero y Anterior
          if ($pagina_actual > 1) : ?>
            <li><a href="?pagina=1<?php echo $query_string; ?>">&laquo;&laquo;</a></li>
            <li><a href="?pagina=<?php echo ($pagina_actual - 1) . $query_string; ?>">&laquo;</a></li>
          <?php endif;

          // Números de la ventana
          $rango = 1;
          $inicio_ventana = max(1, $pagina_actual - $rango);
          $fin_ventana = min($total_paginas, $pagina_actual + $rango);

          for ($i = $inicio_ventana; $i <= $fin_ventana; $i++) : ?>
            <li class="<?php echo ($i == $pagina_actual) ? 'active' : ''; ?>">
              <a href="?pagina=<?php echo $i . $query_string; ?>"><?php echo $i; ?></a>
            </li>
          <?php endfor;

          // Botón Siguiente y Último
          if ($pagina_actual < $total_paginas) : ?>
            <li><a href="?pagina=<?php echo ($pagina_actual + 1) . $query_string; ?>">&raquo;</a></li>
            <li><a href="?pagina=<?php echo $total_paginas . $query_string; ?>">&raquo;&raquo;</a></li>
          <?php endif; ?>
        </ul>
      </nav>
    </section>

    <?php
    // Lógica de Modales de sesión (Basada en medico_listado.php)
    $mostrar_modal_exito = false;
    $mostrar_modal_error = false;
    $mensaje_modal = '';

    if (isset($_SESSION['mensaje_user_exito'])) {
      $mostrar_modal_exito = true;
      $mensaje_modal = $_SESSION['mensaje_user_exito'];
      unset($_SESSION['mensaje_user_exito']);
    } elseif (isset($_SESSION['mensaje_user_error'])) {
      $mostrar_modal_error = true;
      $mensaje_modal = $_SESSION['mensaje_user_error'];
      unset($_SESSION['mensaje_user_error']);
    }
    ?>

    <div class="modal fade" id="modalExito" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header bg-green">
            <h5 class="modal-title" style="color: white;">Operación Exitosa</h5>
          </div>
          <div class="modal-body">
            <p><?= $mensaje_modal; ?></p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-success" data-dismiss="modal">Aceptar</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="modalError" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header bg-crimson">
            <h5 class="modal-title" style="color: white;">Error en la Operación</h5>
          </div>
          <div class="modal-body">
            <p><?= $mensaje_modal; ?></p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal" id="ModalReporteInventario" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #337ab7; color: white;">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="myModalLabel"><i class="fa fa-boxes"></i> Reportes de Inventario y Movimientos</h4>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label>Tipo de Reporte:</label>
              <select class="form-control" id="tipo_reporte_inv">
                <option value="existencia">Existencia Actual (Stock)</option>
                <option value="entradas">Reporte de Entradas (Ingresos)</option>
                <option value="salidas">Reporte de Salidas (Egresos)</option>
              </select>
            </div>

            <div id="seccion_fechas" style="display:none;">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Desde:</label>
                    <input type="date" class="form-control" id="fecha_desde" value="<?= date('Y-m-d'); ?>">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Hasta:</label>
                    <input type="date" class="form-control" id="fecha_hasta" value="<?= date('Y-m-d'); ?>">
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-primary" id="btnEjecutarReporteInv">Generar PDF</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal" id="ModalConfirmarEliminar" tabindex="-1" role="dialog" aria-labelledby="labelEliminar">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #dc3545; color: white;">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="labelEliminar"><i class="fa fa-exclamation-triangle"></i> Confirmar Eliminación</h4>
          </div>
          <div class="modal-body">
            <p style="font-size: 16px;">¿Está seguro de que desea eliminar este registro de movimiento? Esta acción no se puede deshacer.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
            <a href="#" id="btnAceptarEliminar" class="btn btn-danger">Aceptar</a>
          </div>
        </div>
      </div>
    </div>

    <?php include('includes/footer.php'); ?>
  </div>

  <script>
    function confirmarEliminar(id) {
      // Construimos la URL hacia el archivo que procesa la eliminación
      var urlEliminar = '../../cfg/eliminar/eliminar_movimiento.php?id=' + id;

      // Asignamos la URL al botón "Aceptar" del modal
      $('#btnAceptarEliminar').attr('href', urlEliminar);

      // Mostramos el modal
      $('#ModalConfirmarEliminar').modal('show');
    }


    function closeCustomModal(modalElement) {
      modalElement.removeClass('in').addClass('out');
      setTimeout(() => {
        modalElement.modal('hide').removeClass('out');
      }, 100); // Duración de la animación
    }

    $(document).ready(function() {
      $('.reporte').on('click', function(e) {
        e.preventDefault();
        $('#ModalReporteInventario').modal('show');
      });

      // Mostrar/Ocultar fechas según el tipo de reporte
      $('#tipo_reporte_inv').on('change', function() {
        var valor = $(this).val();
        if (valor == 'entradas' || valor == 'salidas') {
          $('#seccion_fechas').fadeIn();
        } else {
          $('#seccion_fechas').fadeOut();
        }
      });

      $('#ModalReporteInventario .close, #ModalReporteInventario .btn-second').on('click', function() {
        closeCustomModal($('#ModalReporteInventario'));
      });

      $('#ModalConfirmarEliminar .close, #ModalConfirmarEliminar .btn-second').on('click', function() {
        closeCustomModal($('#ModalConfirmarEliminar'));
      });

      // Ejecutar reporte
      $('#btnEjecutarReporteInv').on('click', function() {
        var tipo = $('#tipo_reporte_inv').val();
        var desde = $('#fecha_desde').val();
        var hasta = $('#fecha_hasta').val();

        var url = '../../cfg/reportes/generar_pdf_inventario.php?tipo=' + tipo;

        if (tipo == 'entradas' || tipo == 'salidas') {
          url += '&desde=' + desde + '&hasta=' + hasta;
        }

        window.open(url, '_blank');
        $('#ModalReporteInventario').modal('hide');
      });
      <?php if ($mostrar_modal_exito) : ?>
        $('#modalExito').modal('show');
      <?php elseif ($mostrar_modal_error) : ?>
        $('#modalError').modal('show');
      <?php endif; ?>
    });
  </script>
</body>

</html>