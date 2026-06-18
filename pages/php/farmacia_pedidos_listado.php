<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Farmacia | Pedidos</title>
  <?php
  // Incluir encabezado, navegación y conexión
  include('includes/headerNav2.php');
  include("../../cfg/conexion.php");
  ?>
  <style>
    /* ---------------------------------------------------------------------- */
    /* ANIMACIONES Y ESTILOS DE MODALES (Heredados del Inventario) */
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
    #modalDetallePedido,
    #modalCancelarPedido {
      animation: fadeIn 0.4s ease-out;
    }

    .modal.out .modal-dialog {
      animation: fadeOut 0.4s ease-in;
    }

    .modal-open .modal-backdrop {
      opacity: 0.7 !important;
      animation: pulse-opacity 0.3s forwards;
    }

    /* Estilos para el scroll interno de la tabla en el modal */
    .table-scroll-container {
      max-height: 250px;
      overflow-y: auto;
      border-bottom: 1px solid #eee;
    }

    .table-scroll-container::-webkit-scrollbar {
      width: 6px;
    }

    .table-scroll-container::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 5px;
    }

    .table-scroll-container::-webkit-scrollbar-thumb {
      background: #bdc3c7;
      border-radius: 5px;
    }
  </style>
</head>

<body>
  <div class="content-wrapper">
    <?php
    // --- LÓGICA DE PAGINACIÓN Y BÚSQUEDA ---
    $registros_por_pagina = 9;
    $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $inicio = ($pagina_actual - 1) * $registros_por_pagina;

    $busqueda = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : '';

    $donde = "WHERE p.estatus = 1";

    if ($busqueda != '') {
      $donde .= " AND (prov.nombre_proveedor LIKE '%$busqueda%' 
                  OR per.nombre LIKE '%$busqueda%' 
                  OR p.estado LIKE '%$busqueda%'
                  OR p.id_pedido LIKE '%$busqueda%')";
    }

    // Conteo para paginación
    $sql_conteo = "SELECT COUNT(p.id_pedido) as total 
                   FROM pedidos p
                   INNER JOIN proveedor prov ON p.id_proveedor = prov.Id_proveedor
                   INNER JOIN persona per ON p.id_usuario = per.id
                   $donde";

    $resultado_conteo = mysqli_query($conexion, $sql_conteo);
    $fila_conteo = mysqli_fetch_assoc($resultado_conteo);
    $total_registros = $fila_conteo['total'];
    $total_paginas = ceil($total_registros / $registros_por_pagina);
    ?>

    <section class="content-header">
      <h1>
        Gestión de Pedidos <small>(<?php echo $total_registros; ?> Registros)</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-shopping-cart"></i>Pedidos</a></li>
        <li class="active"><a href="#"><i class="fa fa-list"></i>Listado</a></li>
      </ol>
    </section>

    <section class="content">
      <div style="padding-bottom: 10px;">
        <a href="farmacia_pedidos_crear.php" class="btn-sm btn-success pull-right"><i class="fa fa-plus"></i> Nuevo Pedido</a>

        <form method="GET" action="" style="display: inline-block;">
          <input type="text" id="buscar" name="buscar" class="form-control pull-left" placeholder="Buscar pedido..." value="<?php echo htmlspecialchars($busqueda); ?>" style="width: 250px;" autocomplete="off">
        </form>
      </div>

      <div id="contenedorTabla">
        <table class="table table-sm table-hover mt-4" width="100%" id="t_user">
          <thead class="table-dark" style="background-color: #222; color: white; font-size: 12px;">
            <tr>
              <th class="text-center">N° Pedido</th>
              <th>Fecha de Solicitud</th>
              <th>Proveedor</th>
              <th>Solicitado Por</th>
              <th class="text-center">Estado</th>
              <th class="text-center">Acciones</th>
            </tr>
          </thead>
          <tbody class="tbody" style="font-size: 13px;">
            <?php
            // CONSULTA PRINCIPAL CON LIMIT Y OFFSET
            $sql = "SELECT p.id_pedido, p.id_proveedor, p.fecha_creacion, p.estado, 
                           prov.nombre_proveedor, 
                           per.nombre, per.apellido 
                    FROM pedidos p
                    INNER JOIN proveedor prov ON p.id_proveedor = prov.Id_proveedor
                    INNER JOIN persona per ON p.id_usuario = per.id
                    $donde
                    ORDER BY p.fecha_creacion DESC
                    LIMIT $inicio, $registros_por_pagina";

            $resultado = $conexion->query($sql);

            if ($resultado && $resultado->num_rows > 0) {
              while ($row = $resultado->fetch_assoc()) {
                // Configurar el color del badge según el estado
                $clase_badge = 'bg-yellow'; // Pendiente
                if ($row['estado'] == 'Recibido') $clase_badge = 'bg-green';
                if ($row['estado'] == 'Cancelado') $clase_badge = 'bg-crimson';
            ?>
                <tr>
                  <td class="text-center"><strong>#<?php echo str_pad($row['id_pedido'], 6, "0", STR_PAD_LEFT); ?></strong></td>
                  <td><?php echo date("d/m/Y h:i A", strtotime($row['fecha_creacion'])); ?></td>
                  <td><?php echo htmlspecialchars($row['nombre_proveedor']); ?></td>
                  <td><?php echo htmlspecialchars($row['nombre'] . ' ' . $row['apellido']); ?></td>
                  <td class="text-center"><small class="badge <?php echo $clase_badge; ?>"><?php echo $row['estado']; ?></small></td>
                  <td class="text-center">
                    <a href="javascript:void(0);" onclick="abrirModalDetalle(<?php echo $row['id_pedido']; ?>, '<?php echo $row['estado']; ?>', <?php echo $row['id_proveedor']; ?>)" class="btn-sm btn-info" title="Ver Artículos"><img src="../../recursos/imagenes/iconos/info.png" style="width:15px; height:15px;"></a>
                    <?php if (in_array('Generar reportes de pedidos', $_SESSION["permisos"])) : ?>
                      <a target="_blank" href="../../cfg/pdf/pedido_pdf.php?Id=<?php echo $row['id_pedido'] ?>" class="btn-sm btn-primary" title="Imprimir PDF del Pedido"><img src="../../recursos/imagenes/iconos/documento.png" style="width:15px; height:15px;"></a>
                    <?php endif; ?>
                    <?php if ($row['estado'] == 'Pendiente') : ?>
                      <a href="javascript:void(0);" onclick="abrirModalCancelar(<?php echo $row['id_pedido']; ?>)" class="btn-sm btn-danger" title="Cancelar Pedido"><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></a>
                    <?php else : ?>
                      <a href="#" class="btn-sm" style="background-color: gray;" title="No se puede cancelar en este estado"><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px; opacity: 0.5;"></a>
                    <?php endif; ?>
                  </td>
                </tr>
            <?php
              }
            } else {
              echo '<tr><td colspan="6" class="text-center">No hay pedidos registrados o que coincidan con la búsqueda.</td></tr>';
            }
            ?>
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

          $rango = 2;
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

    <div class="modal fade" id="modalDetallePedido" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header bg-primary" style="background-color: #3498db; color: white;">
            <button type="button" class="close" onclick="closeCustomModal($('#modalDetallePedido'))" aria-label="Close" style="color: white; opacity: 1;">
              <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title"><i class="fa fa-shopping-basket"></i> Detalle del Pedido <span id="num_pedido_titulo"></span></h4>
          </div>
          <div class="modal-body">
            <div class="table-scroll-container">
              <table class="table table-bordered table-striped" style="margin-bottom: 0;">
                <thead style="background-color: #f8f9fa;">
                  <tr>
                    <th>Medicamento / Presentación</th>
                    <th class="text-center" style="width: 150px;">Cant. Solicitada</th>
                  </tr>
                </thead>
                <tbody id="cuerpo_detalle_pedido">
                </tbody>
              </table>
            </div>
          </div>
          <div class="modal-footer">
            <a href="#" id="btnProcesarPedidoModal" class="btn btn-success pull-left" style="display:none;"><i class="fa fa-arrow-circle-right"></i> Procesar Entrada de este Pedido</a>
            <button type="button" class="btn btn-default" onclick="closeCustomModal($('#modalDetallePedido'))">Cerrar</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="modalCancelarPedido" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header bg-crimson" style="background-color: #dc3545; color: white;">
            <button type="button" class="close" onclick="closeCustomModal($('#modalCancelarPedido'))" aria-label="Close" style="color: white; opacity: 1;">
              <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Cancelar Pedido</h4>
          </div>
          <form action="../../cfg/procesar_pedidos.php" method="POST">
            <div class="modal-body">
              <p style="font-size: 15px;">¿Está seguro que desea cancelar el <strong>Pedido #<span id="display_id_cancelar"></span></strong>?</p>
              <p class="text-muted small">Esta acción marcará el pedido como "Cancelado" y no podrá ser procesado en Entradas.</p>

              <input type="hidden" name="op" value="cancelar_pedido">
              <input type="hidden" name="id_pedido" id="id_pedido_cancelar_input">
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" onclick="closeCustomModal($('#modalCancelarPedido'))">Cerrar</button>
              <button type="submit" class="btn btn-danger">Confirmar Cancelación</button>
            </div>
          </form>
        </div>
      </div>
    </div>

  </div>
  <?php include('includes/footer.php'); ?>

  <script>
    // Función para cerrar modales con animación
    function closeCustomModal(modalElement) {
      modalElement.removeClass('in').addClass('out');
      setTimeout(() => {
        modalElement.modal('hide').removeClass('out');
      }, 400);
    }

    // Modal para Ver Detalles
    function abrirModalDetalle(idPedido, estado, idProveedor) {
      // Formatear ID con ceros a la izquierda
      let idFormateado = idPedido.toString().padStart(6, '0');
      $('#num_pedido_titulo').text('#' + idFormateado);

      $('#cuerpo_detalle_pedido').html('<tr><td colspan="2" class="text-center"><i class="fa fa-spinner fa-spin"></i> Cargando...</td></tr>');

      if (estado === 'Pendiente') {
        $('#btnProcesarPedidoModal').show().attr('href', 'farmacia_inventario_movimiento_entrada.php?id_pedido_auto=' + idPedido + '&id_proveedor_auto=' + idProveedor);
      } else {
        $('#btnProcesarPedidoModal').hide();
      }

      $('#modalDetallePedido').removeClass('out').addClass('in').modal('show');

      $.ajax({
        url: '../../cfg/ajax/obtener_detalle_pedido_modal.php',
        type: 'POST',
        data: {
          id_pedido: idPedido
        },
        dataType: 'json',
        success: function(respuesta) {
          var html = '';
          if (respuesta.length > 0) {
            respuesta.forEach(function(item) {
              html += '<tr>';
              html += '  <td><strong>' + item.nombre_medicamento + '</strong><br><small class="text-muted">' + item.presentacion + '</small></td>';
              html += '  <td class="text-center"><span class="badge bg-primary" style="font-size: 14px;">' + item.cantidad_solicitada + '</span></td>';
              html += '</tr>';
            });
          } else {
            html = '<tr><td colspan="2" class="text-center text-muted">No se encontraron productos.</td></tr>';
          }
          $('#cuerpo_detalle_pedido').html(html);
        },
        error: function() {
          $('#cuerpo_detalle_pedido').html('<tr><td colspan="2" class="text-center text-danger">Error de conexión al cargar datos.</td></tr>');
        }
      });
    }

    // Modal para Cancelar (Similar a la Anulación del inventario)
    function abrirModalCancelar(idPedido) {
      document.getElementById('id_pedido_cancelar_input').value = idPedido;
      document.getElementById('display_id_cancelar').textContent = idPedido.toString().padStart(6, '0');
      $('#modalCancelarPedido').removeClass('out').addClass('in').modal('show');
    }

    // Limpieza de clases al cerrar nativamente por Bootstrap
    $('.modal').on('hidden.bs.modal', function() {
      $(this).removeClass('out');
    });
  </script>
</body>

</html>