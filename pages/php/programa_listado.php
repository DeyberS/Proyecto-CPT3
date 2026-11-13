<?php
include('includes/headerNav2.php');
verificarAcceso('Gestionar Programa');
?>

<head>
  <title>Programa de Crónicos | Listado de Peticiones</title>
</head>

<style>
  .table-scroll-container {
    max-height: 250px;
    overflow-y: auto;
    border-bottom: 1px solid #eee;
  }
  .table-scroll-container::-webkit-scrollbar { width: 6px; }
  .table-scroll-container::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 5px; }
  .table-scroll-container::-webkit-scrollbar-thumb { background: #bdc3c7; border-radius: 5px; }

  /* Separación de botones de acción en la tabla */
  .acciones-cell {
    display: flex;
    gap: 5px;
    justify-content: center;
    align-items: center;
  }

  /* Separación de botones superiores */
  .acciones-superiores {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
    margin-bottom: 10px;
  }

  /* Estilos para el modal de entregas */
  .badge-mes {
    font-size: 13px;
    padding: 5px 10px;
    background-color: #3498db;
    color: white;
    border-radius: 4px;
  }

  .badge-cantidad {
    font-size: 14px;
    padding: 5px 10px;
    border-radius: 4px;
    font-weight: bold;
  }

  .badge-cantidad-entregada {
    background-color: #e8f8f5;
    color: #1e8449;
    border: 1px solid #27ae60;
  }

  .badge-cantidad-solicitada {
    background-color: #f8f9fa;
    color: #2c3e50;
    border: 1px solid #bdc3c7;
  }
</style>

<div class="content-wrapper">
  <?php
  // ==========================================
  // SIMULACIÓN DE DATOS (TEMPLATE)
  // ==========================================
  $registros_por_pagina = 9;
  $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
  $inicio = ($pagina_actual - 1) * $registros_por_pagina;

  $busqueda = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : '';

  // Simulamos peticiones (una petición = varios pacientes)
  $peticiones_simuladas = [
    [
      'id_peticion' => 1,
      'mes' => '2026-05',
      'fecha_generacion' => '2026-05-02',
      'medico' => 'Dra. Ana Gómez',
      'pacientes_incluidos' => 3,
      'estado' => 'Recibida Total',
      'detalles' => [
        ['paciente' => 'Ana Pérez', 'medicamento' => 'Metformina 850mg', 'cantidad' => 60],
        ['paciente' => 'Carlos Gómez', 'medicamento' => 'Enalapril 10mg', 'cantidad' => 30],
        ['paciente' => 'María Rodríguez', 'medicamento' => 'Carbamazepina 200mg', 'cantidad' => 60]
      ]
    ],
    [
      'id_peticion' => 2,
      'mes' => '2026-04',
      'fecha_generacion' => '2026-04-03',
      'medico' => 'Dra. Ana Gómez',
      'pacientes_incluidos' => 2,
      'estado' => 'Recibida Parcial',
      'detalles' => [
        ['paciente' => 'Ana Pérez', 'medicamento' => 'Metformina 850mg', 'cantidad' => 60],
        ['paciente' => 'Carlos Gómez', 'medicamento' => 'Enalapril 10mg', 'cantidad' => 30]
      ]
    ]
  ];

  // Simulamos el historial de entregas (medicamentos entregados a cada paciente)
  $entregas_simuladas = [
    [
      'id_entrega' => 1,
      'fecha_entrega' => '2026-05-10',
      'mes' => '2026-05',
      'paciente' => 'Ana Pérez',
      'cedula' => 'V-12345678',
      'medicamento' => 'Metformina 850mg',
      'dosis' => '850mg',
      'frecuencia' => 'Cada 12h',
      'cantidad_entregada' => 60,
      'id_peticion' => 1,
      'entregado_por' => 'Dra. Ana Gómez'
    ],
    [
      'id_entrega' => 2,
      'fecha_entrega' => '2026-05-10',
      'mes' => '2026-05',
      'paciente' => 'Carlos Gómez',
      'cedula' => 'V-87654321',
      'medicamento' => 'Enalapril 10mg',
      'dosis' => '10mg',
      'frecuencia' => 'Cada 24h',
      'cantidad_entregada' => 20,
      'id_peticion' => 1,
      'entregado_por' => 'Dra. Ana Gómez'
    ],
    [
      'id_entrega' => 3,
      'fecha_entrega' => '2026-04-15',
      'mes' => '2026-04',
      'paciente' => 'Ana Pérez',
      'cedula' => 'V-12345678',
      'medicamento' => 'Metformina 850mg',
      'dosis' => '850mg',
      'frecuencia' => 'Cada 12h',
      'cantidad_entregada' => 60,
      'id_peticion' => 2,
      'entregado_por' => 'Dra. Ana Gómez'
    ],
    [
      'id_entrega' => 4,
      'fecha_entrega' => '2026-04-15',
      'mes' => '2026-04',
      'paciente' => 'Carlos Gómez',
      'cedula' => 'V-87654321',
      'medicamento' => 'Enalapril 10mg',
      'dosis' => '10mg',
      'frecuencia' => 'Cada 24h',
      'cantidad_entregada' => 30,
      'id_peticion' => 2,
      'entregado_por' => 'Dra. Ana Gómez'
    ]
  ];

  $total_registros = count($peticiones_simuladas);
  $total_paginas = ceil($total_registros / $registros_por_pagina);

  // Obtener meses únicos para el filtro del modal
  $meses_disponibles = [];
  foreach ($entregas_simuladas as $e) {
    if (!in_array($e['mes'], $meses_disponibles)) {
      $meses_disponibles[] = $e['mes'];
    }
  }
  sort($meses_disponibles);
  ?>

  <section class="content-header">
    <h1>
      Programa de Crónicos - Peticiones (<?php echo $total_registros; ?> Registros)
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i> Inicio</a></li>
      <li><a href="#"><i class="fa fa-heartbeat"></i> Programa de Crónicos</a></li>
      <li class="active"><a href="#"><i class="fa fa-list"></i> Peticiones</a></li>
    </ol>
  </section>

  <section class="content">
    <div class="acciones-superiores">
      <!-- BOTÓN DE HISTORIAL DE ENTREGAS (abre modal) -->
      <a href="#" class="btn-sm btn-info pull-right" data-toggle="modal" data-target="#modalHistorialEntregas" style="height:31px;">
        <i class="fa fa-history"></i> Ver Entregas
      </button>

      <!-- BOTÓN DE NUEVA PETICIÓN -->
      <a href="programa_peticion_agregar.php" class="btn-sm btn-success pull-right" style="height:31px;">
        <i class="fa fa-plus"></i> Nueva Petición
      </a>

      <!-- BUSCADOR -->
      <form method="GET" action="" style="display: inline-block; margin: 0;">
        <input type="text" id="buscar" name="buscar" class="form-control" style="border-radius:0; height:31px; width:250px;" placeholder="Buscar petición..." value="<?php echo htmlspecialchars($busqueda); ?>" autocomplete="off">
      </form>
    </div>

    <div id="contenedorTabla">
      <table class="table table-sm table-hover mt-4" width="100%" id="t_user">
        <thead class="table-dark" style="background-color: #222; color: white; font-size: 12px;">
          <tr>
            <th class="text-center">N° Petición</th>
            <th>Mes</th>
            <th>Fecha Generación</th>
            <th>Médico Solicitante</th>
            <th class="text-center">Pacientes Incluidos</th>
            <th class="text-center">Estado</th>
            <th class="text-center">Acciones</th>
          </tr>
        </thead>
        <tbody class="tbody" style="font-size: 13px;">
          <?php if (count($peticiones_simuladas) > 0) : ?>
            <?php foreach ($peticiones_simuladas as $row) : 
              $clase_badge = 'bg-yellow';
              if ($row['estado'] == 'Recibida Total') $clase_badge = 'bg-green';
              if ($row['estado'] == 'Cancelada') $clase_badge = 'bg-crimson';
            ?>
              <tr>
                <td class="text-center"><strong>#<?php echo str_pad($row['id_peticion'], 6, "0", STR_PAD_LEFT); ?></strong></td>
                <td><?php echo htmlspecialchars($row['mes']); ?></td>
                <td><?php echo date("d/m/Y", strtotime($row['fecha_generacion'])); ?></td>
                <td><?php echo htmlspecialchars($row['medico']); ?></td>
                <td class="text-center">
                  <span class="badge bg-blue" style="font-size: 13px;"><?php echo $row['pacientes_incluidos']; ?> pacientes</span>
                </td>
                <td class="text-center"><small class="badge <?php echo $clase_badge; ?>"><?php echo $row['estado']; ?></small></td>
                <td>
                  <!-- ACCIONES SEPARADAS -->
                  <div class="acciones-cell">
                    <!-- VER DETALLE (abre modal) -->
                    <a href="javascript:void(0);" onclick="abrirModalDetalle(<?php echo $row['id_peticion']; ?>)" class="btn-sm btn-info" title="Ver Detalle">
                      <img src="../../recursos/imagenes/iconos/info.png" style="width:15px; height:15px;">
                    </a>
                    <!-- PDF -->
                    <?php if (in_array('Generar Reportes de Programa', $_SESSION["permisos"])) : ?>
                      <a target="_blank" href="../../cfg/pdf/peticion_pdf.php?Id=<?php echo $row['id_peticion'] ?>" class="btn-sm btn-primary" title="Imprimir PDF de la Petición">
                        <img src="../../recursos/imagenes/iconos/documento.png" style="width:15px; height:15px;">
                      </a>
                    <?php endif; ?>
                    <!-- REGISTRAR RECEPCIÓN (si no está completa) -->
                    <?php if ($row['estado'] != 'Recibida Total' && $row['estado'] != 'Cancelada') : ?>
                      <a href="programa_recepcion.php?Id=<?php echo $row['id_peticion']; ?>" class="btn-sm btn-success" title="Registrar Recepción">
                        <img src="../../recursos/imagenes/iconos/enviar.png" style="width:15px; height:15px;">
                      </a>
                    <?php endif; ?>
                    <!-- CANCELAR (si está pendiente) -->
                    <?php if ($row['estado'] == 'Pendiente') : ?>
                      <a href="javascript:void(0);" onclick="abrirModalCancelar(<?php echo $row['id_peticion']; ?>)" class="btn-sm btn-danger" title="Cancelar Petición">
                        <img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;">
                      </a>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else : ?>
            <tr><td colspan="7" class="text-center">No hay peticiones registradas o que coincidan con la búsqueda.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <nav id="contenedorPaginacion" aria-label="Page navigation" style="position: fixed; bottom:0;">
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

  <!-- ========================================== -->
  <!-- MODAL: HISTORIAL DE ENTREGAS POR PACIENTE  -->
  <!-- ========================================== -->
  <div class="modal fade" id="modalHistorialEntregas" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header bg-info">
          <h4 class="modal-title"><i class="fa fa-history"></i> Historial de Entregas a Pacientes</h4>
        </div>
        <div class="modal-body">
          <!-- FILTRO POR MES -->
          <div class="row" style="margin-bottom: 15px;">
            <div class="col-md-4">
              <label>Filtrar por Mes:</label>
              <select id="filtro_mes_entregas" class="form-control">
                <option value="">Todos los meses</option>
                <?php foreach ($meses_disponibles as $mes) : ?>
                  <option value="<?php echo $mes; ?>"><?php echo $mes; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label>Filtrar por Paciente:</label>
              <input type="text" id="filtro_paciente_entregas" class="form-control" placeholder="Cédula o nombre...">
            </div>
            <div class="col-md-4">
              <label>&nbsp;</label>
              <button type="button" class="btn btn-default btn-block" id="btnLimpiarFiltrosEntregas">
                <i class="fa fa-refresh"></i> Limpiar Filtros
              </button>
            </div>
          </div>

          <!-- TABLA DE ENTREGAS -->
          <div class="table-responsive" style="max-height: 400px;">
            <table class="table table-bordered table-striped table-hover" id="tabla_entregas">
              <thead style="background-color: #34495e; color: white; position: sticky; top: 0; z-index: 10;">
                <tr>
                  <th>Fecha</th>
                  <th>Mes</th>
                  <th>Paciente</th>
                  <th>Medicamento</th>
                  <th>Dosis</th>
                  <th>Frecuencia</th>
                  <th class="text-center">Cant. Entregada</th>
                  <th>Entregado Por</th>
                </tr>
              </thead>
              <tbody id="cuerpo_tabla_entregas">
                <?php foreach ($entregas_simuladas as $e) : ?>
                  <tr class="fila-entrega" data-mes="<?php echo $e['mes']; ?>" data-paciente="<?php echo strtolower($e['paciente'] . ' ' . $e['cedula']); ?>">
                    <td><?php echo date("d/m/Y", strtotime($e['fecha_entrega'])); ?></td>
                    <td><span class="badge-mes"><?php echo $e['mes']; ?></span></td>
                    <td>
                      <strong><?php echo htmlspecialchars($e['paciente']); ?></strong><br>
                      <small class="text-muted"><?php echo htmlspecialchars($e['cedula']); ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($e['medicamento']); ?></td>
                    <td><?php echo htmlspecialchars($e['dosis']); ?></td>
                    <td><?php echo htmlspecialchars($e['frecuencia']); ?></td>
                    <td class="text-center">
                      <span class="badge-cantidad badge-cantidad-entregada"><?php echo $e['cantidad_entregada']; ?></span>
                    </td>
                    <td><?php echo htmlspecialchars($e['entregado_por']); ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <!-- Mensaje si no hay resultados -->
          <div id="mensaje_sin_entregas" style="display: none;" class="alert alert-info text-center">
            <i class="fa fa-info-circle"></i> No se encontraron entregas con los filtros seleccionados.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL DE DETALLE DE LA PETICIÓN (UNA PETICIÓN = VARIOS PACIENTES) -->
  <div class="modal fade" id="modalDetallePeticion" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h4 class="modal-title"><i class="fa fa-medkit"></i> Detalle de la Petición <span id="num_peticion_titulo"></span></h4>
        </div>
        <div class="modal-body">
          <div class="table-scroll-container">
            <table class="table table-bordered table-striped" style="margin-bottom: 0;">
              <thead style="background-color: #f8f9fa;">
                <tr>
                  <th>Paciente</th>
                  <th>Medicamento</th>
                  <th class="text-center" style="width: 150px;">Cant. Solicitada</th>
                </tr>
              </thead>
              <tbody id="cuerpo_detalle_peticion">
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <a href="#" id="btnRegistrarRecepcionModal" class="btn btn-success pull-left" style="display:none;">
            <i class="fa fa-arrow-circle-right"></i> Registrar Recepción
          </a>
          <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL DE CANCELAR PETICIÓN -->
  <div class="modal fade" id="modalCancelarPeticion" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Confirmación de Cancelación</h4>
        </div>
        <form action="../../cfg/procesar_peticion.php" method="POST">
          <div class="modal-body">
            <p style="font-size: 15px;">¿Está seguro que desea cancelar la <strong>Petición #<span id="display_id_cancelar"></span></strong>?</p>
            <p class="text-muted small">Esta acción marcará la petición como "Cancelada" y no podrá ser procesada en Recepciones.</p>

            <input type="hidden" name="op" value="cancelar_peticion">
            <input type="hidden" name="id_peticion" id="id_peticion_cancelar_input">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn btn-danger">Aceptar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <?php include('includes/footer.php'); ?>

  <script>
    function abrirModalDetalle(idPeticion) {
      let idFormateado = idPeticion.toString().padStart(6, '0');
      $('#num_peticion_titulo').text('#' + idFormateado);

      $('#cuerpo_detalle_peticion').html('<tr><td colspan="3" class="text-center"><i class="fa fa-spinner fa-spin"></i> Cargando...</td></tr>');

      $('#modalDetallePeticion').modal('show');

      // Simulamos la carga AJAX (en producción: '../../cfg/ajax/obtener_detalle_peticion.php')
      setTimeout(function() {
        var peticion = null;
        <?php foreach ($peticiones_simuladas as $p) : ?>
          if (idPeticion === <?= $p['id_peticion'] ?>) {
            peticion = <?= json_encode($p) ?>;
          }
        <?php endforeach; ?>

        if (peticion) {
          if (peticion.estado !== 'Recibida Total' && peticion.estado !== 'Cancelada') {
            $('#btnRegistrarRecepcionModal').show().attr('href', 'programa_recepcion.php?Id=' + idPeticion);
          } else {
            $('#btnRegistrarRecepcionModal').hide();
          }

          var html = '';
          peticion.detalles.forEach(function(item) {
            html += '<tr>';
            html += '  <td><strong>' + item.paciente + '</strong></td>';
            html += '  <td>' + item.medicamento + '</td>';
            html += '  <td class="text-center"><span class="badge bg-primary" style="font-size: 14px;">' + item.cantidad + '</span></td>';
            html += '</tr>';
          });
          $('#cuerpo_detalle_peticion').html(html);
        } else {
          $('#cuerpo_detalle_peticion').html('<tr><td colspan="3" class="text-center text-muted">No se encontraron detalles.</td></tr>');
        }
      }, 300);
    }

    function abrirModalCancelar(idPeticion) {
      document.getElementById('id_peticion_cancelar_input').value = idPeticion;
      document.getElementById('display_id_cancelar').textContent = idPeticion.toString().padStart(6, '0');
      $('#modalCancelarPeticion').modal('show');
    }

    // ==========================================
    // FILTROS DEL MODAL DE ENTREGAS
    // ==========================================
    function filtrarEntregas() {
      let mes = $('#filtro_mes_entregas').val();
      let paciente = $('#filtro_paciente_entregas').val().toLowerCase().trim();
      let filasVisibles = 0;

      $('.fila-entrega').each(function() {
        let filaMes = $(this).data('mes');
        let filaPaciente = $(this).data('paciente');
        let mostrar = true;

        if (mes !== '' && filaMes !== mes) {
          mostrar = false;
        }
        if (paciente !== '' && filaPaciente.indexOf(paciente) === -1) {
          mostrar = false;
        }

        $(this).toggle(mostrar);
        if (mostrar) filasVisibles++;
      });

      if (filasVisibles === 0) {
        $('#mensaje_sin_entregas').show();
      } else {
        $('#mensaje_sin_entregas').hide();
      }
    }

    $('#filtro_mes_entregas').on('change', filtrarEntregas);
    $('#filtro_paciente_entregas').on('keyup', filtrarEntregas);

    $('#btnLimpiarFiltrosEntregas').on('click', function() {
      $('#filtro_mes_entregas').val('');
      $('#filtro_paciente_entregas').val('');
      filtrarEntregas();
    });
  </script>
</div>