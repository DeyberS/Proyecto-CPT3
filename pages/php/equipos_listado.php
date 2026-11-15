<?php
include('includes/headerNav2.php');
verificarAcceso('Gestionar Equipos');
?>

<head>
  <title>Inventario de Equipos | Listado</title>
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

  /* Estilos para los badges de estado */
  .badge-bueno { background-color: #27ae60; color: white; }
  .badge-regular { background-color: #f39c12; color: white; }
  .badge-danado { background-color: #e74c3c; color: white; }
  .badge-desincorporado { background-color: #7f8c8d; color: white; }
</style>

<div class="content-wrapper">
  <?php
  // ==========================================
  // SIMULACIÓN DE DATOS (TEMPLATE)
  // ==========================================
  $registros_por_pagina = 9;
  $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
  $inicio = ($pagina_actual - 1) * $registros_por_pagina;

  $busqueda = isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : '';

  // Simulamos los equipos
  $equipos_simulados = [
    [
      'id_equipo' => 1,
      'numero_bien' => '001',
      'descripcion' => 'Nevera',
      'marca' => 'Haier',
      'modelo' => 'HT-200',
      'serial_color' => 'Blanco',
      'cantidad' => 1,
      'ubicacion' => 'Consultorio 1',
      'estado' => 'Bueno',
      'fecha_registro' => '2025-01-15'
    ],
    [
      'id_equipo' => 2,
      'numero_bien' => '002',
      'descripcion' => 'Silla tipo ejecutiva',
      'marca' => '-',
      'modelo' => '-',
      'serial_color' => 'Negro',
      'cantidad' => 3,
      'ubicacion' => 'Oficina Administrativa',
      'estado' => 'Bueno',
      'fecha_registro' => '2025-02-20'
    ],
    [
      'id_equipo' => 3,
      'numero_bien' => '003',
      'descripcion' => 'Mesa de madera',
      'marca' => '-',
      'modelo' => '-',
      'serial_color' => 'Marrón',
      'cantidad' => 2,
      'ubicacion' => 'Sala de Espera',
      'estado' => 'Regular',
      'fecha_registro' => '2025-03-10'
    ],
    [
      'id_equipo' => 4,
      'numero_bien' => '004',
      'descripcion' => 'Escritorio metálico',
      'marca' => '-',
      'modelo' => '-',
      'serial_color' => 'Gris',
      'cantidad' => 1,
      'ubicacion' => 'Consultorio 2',
      'estado' => 'Dañado',
      'fecha_registro' => '2025-04-05'
    ],
    [
      'id_equipo' => 5,
      'numero_bien' => '005',
      'descripcion' => 'Aire Acondicionado',
      'marca' => 'Samsung',
      'modelo' => 'AR12',
      'serial_color' => 'Blanco',
      'cantidad' => 1,
      'ubicacion' => 'Consultorio 1',
      'estado' => 'Regular',
      'fecha_registro' => '2025-05-12'
    ]
  ];

  $total_registros = count($equipos_simulados);
  $total_paginas = ceil($total_registros / $registros_por_pagina);
  ?>

  <section class="content-header">
    <h1>
      Inventario de Equipos (<?php echo $total_registros; ?> Registros)
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i> Inicio</a></li>
      <li><a href="#"><i class="fa fa-desktop"></i> Inventario de Equipos</a></li>
      <li class="active"><a href="#"><i class="fa fa-list"></i> Listado</a></li>
    </ol>
  </section>

  <section class="content">
    <div class="toolbar-container">
      <!-- BOTÓN DE NUEVO EQUIPO -->
      <a href="equipos_agregar.php" class="btn-sm btn-success pull-right"><i class="fa fa-plus"></i> Nuevo Equipo</a>

      <div class="pull-left form-inline">
        <form method="GET" action="" id="formBusquedaRapida">
          <input type="text" id="buscar" name="buscar" class="form-control" placeholder="Escriba para buscar..." value="<?php echo htmlspecialchars($busqueda); ?>" style="border-radius:0; height:10%; width:250px; display:inline-block;" autocomplete="off">
        </form>
      </div>
    </div>
    <br><br>
    <div id="contenedorTabla">
      <table class="table table-sm table-hover mt-4" width="100%" id="t_user">
        <thead class="table-dark">
          <tr>
            <th class="text-center">N° Bien</th>
            <th>Descripción</th>
            <th>Marca / Modelo</th>
            <th>Serial / Color</th>
            <th class="text-center">Cantidad</th>
            <th>Ubicación</th>
            <th class="text-center">Estado</th>
            <th class="text-center">Acciones</th>
          </tr>
        </thead>
        <tbody class="tbody" style="font-size: 13px;">
          <?php if (count($equipos_simulados) > 0) : ?>
            <?php foreach ($equipos_simulados as $row) : 
              $clase_badge = 'badge-bueno';
              if ($row['estado'] == 'Regular') $clase_badge = 'badge-regular';
              if ($row['estado'] == 'Dañado') $clase_badge = 'badge-danado';
              if ($row['estado'] == 'Desincorporado') $clase_badge = 'badge-desincorporado';
            ?>
              <tr>
                <td class="text-center"><strong><?php echo htmlspecialchars($row['numero_bien']); ?></strong></td>
                <td><?php echo htmlspecialchars($row['descripcion']); ?></td>
                <td><?php echo htmlspecialchars($row['marca'] . ' / ' . $row['modelo']); ?></td>
                <td><?php echo htmlspecialchars($row['serial_color']); ?></td>
                <td class="text-center"><span class="badge bg-primary" style="font-size: 13px;"><?php echo $row['cantidad']; ?></span></td>
                <td><?php echo htmlspecialchars($row['ubicacion']); ?></td>
                <td class="text-center"><small class="badge <?php echo $clase_badge; ?>"><?php echo $row['estado']; ?></small></td>
                <td class="text-center">
                  <!-- VER DETALLE (abre modal) -->
                  <a href="javascript:void(0);" onclick="abrirModalDetalle(<?php echo $row['id_equipo']; ?>)" class="btn-sm btn-info" title="Ver Detalle e Historial" style="margin-right:5px;">
                    <img src="../../recursos/imagenes/iconos/info.png" style="width:15px; height:15px;">
                  </a>
                  <!-- CAMBIAR ESTADO (abre modal) -->
                  <a href="javascript:void(0);" onclick="abrirModalCambiarEstado(<?php echo $row['id_equipo']; ?>, '<?php echo $row['descripcion']; ?>')" class="btn-sm btn-warning" title="Cambiar Estado">
                    <img src="../../recursos/imagenes/iconos/Editar.png" style="width:15px; height:15px;">
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else : ?>
            <tr><td colspan="8" class="text-center">No hay equipos registrados o que coincidan con la búsqueda.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <nav id="contenedorPaginacion" aria-label="Page navigation">
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
  <!-- MODAL 1: VER DETALLE E HISTORIAL DEL EQUIPO -->
  <!-- ========================================== -->
  <div class="modal fade" id="modalDetalleEquipo" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h4 class="modal-title"><i class="fa fa-desktop"></i> Detalle del Equipo <span id="num_bien_titulo"></span></h4>
        </div>
        <div class="modal-body">
          <!-- DATOS DEL EQUIPO -->
          <div class="row" style="margin-bottom: 20px;">
            <div class="col-md-6">
              <div class="data-item-card" style="background: #f8f9fa; padding: 15px; border-left: 4px solid #007bff;">
                <span class="label-custom" style="font-size: 11px; color: #95a5a6; text-transform: uppercase; font-weight: 700;">Descripción</span>
                <p style="margin: 5px 0 0 0; color: #2c3e50; font-weight: 600;" id="det_descripcion">-</p>
              </div>
            </div>
            <div class="col-md-6">
              <div class="data-item-card" style="background: #f8f9fa; padding: 15px; border-left: 4px solid #007bff;">
                <span class="label-custom" style="font-size: 11px; color: #95a5a6; text-transform: uppercase; font-weight: 700;">Marca / Modelo</span>
                <p style="margin: 5px 0 0 0; color: #2c3e50; font-weight: 600;" id="det_marca">-</p>
              </div>
            </div>
            <div class="col-md-6">
              <div class="data-item-card" style="background: #f8f9fa; padding: 15px; border-left: 4px solid #007bff;">
                <span class="label-custom" style="font-size: 11px; color: #95a5a6; text-transform: uppercase; font-weight: 700;">Serial / Color</span>
                <p style="margin: 5px 0 0 0; color: #2c3e50; font-weight: 600;" id="det_serial">-</p>
              </div>
            </div>
            <div class="col-md-6">
              <div class="data-item-card" style="background: #f8f9fa; padding: 15px; border-left: 4px solid #007bff;">
                <span class="label-custom" style="font-size: 11px; color: #95a5a6; text-transform: uppercase; font-weight: 700;">Ubicación</span>
                <p style="margin: 5px 0 0 0; color: #2c3e50; font-weight: 600;" id="det_ubicacion">-</p>
              </div>
            </div>
          </div>

          <!-- HISTORIAL DE ESTADOS -->
          <h4 style="color: #007bff; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px; text-transform: uppercase; font-size: 14px;">
            <i class="fa fa-history"></i> Historial de Estados
          </h4>
          <div class="table-scroll-container">
            <table class="table table-bordered table-striped" style="margin-bottom: 0;">
              <thead style="background-color: #34495e; color: white; position: sticky; top: 0; z-index: 10;">
                <tr>
                  <th>Fecha</th>
                  <th>Estado Anterior</th>
                  <th>Estado Nuevo</th>
                  <th>Motivo</th>
                  <th>Registrado Por</th>
                </tr>
              </thead>
              <tbody id="cuerpo_historial_equipo">
                <tr><td colspan="5" class="text-center"><i class="fa fa-spinner fa-spin"></i> Cargando historial...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- ========================================== -->
  <!-- MODAL 2: CAMBIAR ESTADO                     -->
  <!-- ========================================== -->
  <div class="modal fade" id="modalCambiarEstado" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-yellow">
          <h4 class="modal-title"><i class="fa fa-exchange"></i> Cambiar Estado del Equipo</h4>
        </div>
        <form id="formCambiarEstado" action="../../cfg/agregar/agregar_cambio_estado.php" method="POST">
          <div class="modal-body">
            <input type="hidden" name="id_equipo" id="id_equipo_cambiar">
            
            <div class="form-group">
              <label>Equipo:</label>
              <input type="text" class="form-control" id="desc_equipo_cambiar" readonly style="background-color: #f8f9fa;">
            </div>

            <div class="form-group">
              <label>Nuevo Estado (*):</label>
              <select name="estado_nuevo" id="estado_nuevo" class="form-control" required>
                <option value="">--- Seleccione el nuevo estado ---</option>
                <option value="Bueno">Bueno</option>
                <option value="Regular">Regular</option>
                <option value="Dañado">Dañado</option>
                <option value="Desincorporado">Desincorporado</option>
              </select>
            </div>

            <div class="form-group">
              <label>Motivo del Cambio (*):</label>
              <textarea name="motivo" id="motivo_cambio" class="form-control" rows="3" placeholder="Describa el motivo del cambio de estado..." required></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-warning"><i class="fa fa-save"></i> Guardar Cambio</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <?php include('includes/footer.php'); ?>

  <script>
    // ==========================================
    // SIMULACIÓN DE DATOS (TEMPLATE)
    // ==========================================
    const equiposData = <?= json_encode($equipos_simulados) ?>;
    
    // Simulamos historial de estados por equipo
    const historialSimulado = {
      1: [
        { fecha: '2025-01-15', anterior: '-', nuevo: 'Bueno', motivo: 'Registro inicial del equipo', usuario: 'Admin' }
      ],
      2: [
        { fecha: '2025-02-20', anterior: '-', nuevo: 'Bueno', motivo: 'Registro inicial del equipo', usuario: 'Admin' },
        { fecha: '2026-03-15', anterior: 'Bueno', nuevo: 'Regular', motivo: 'Desgaste por uso', usuario: 'Admin' }
      ],
      3: [
        { fecha: '2025-03-10', anterior: '-', nuevo: 'Bueno', motivo: 'Registro inicial del equipo', usuario: 'Admin' },
        { fecha: '2026-04-20', anterior: 'Bueno', nuevo: 'Regular', motivo: 'Mesa con rayones', usuario: 'Admin' }
      ],
      4: [
        { fecha: '2025-04-05', anterior: '-', nuevo: 'Bueno', motivo: 'Registro inicial del equipo', usuario: 'Admin' },
        { fecha: '2026-05-10', anterior: 'Bueno', nuevo: 'Dañado', motivo: 'Se rompió la cerradura', usuario: 'Admin' }
      ],
      5: [
        { fecha: '2025-05-12', anterior: '-', nuevo: 'Bueno', motivo: 'Registro inicial del equipo', usuario: 'Admin' },
        { fecha: '2026-06-01', anterior: 'Bueno', nuevo: 'Regular', motivo: 'No enfría bien', usuario: 'Admin' }
      ]
    };

    // ==========================================
    // ABRIR MODAL DE DETALLE
    // ==========================================
    function abrirModalDetalle(idEquipo) {
      let equipo = equiposData.find(e => e.id_equipo === idEquipo);
      
      if (!equipo) return;

      $('#num_bien_titulo').text('# ' + equipo.numero_bien);
      $('#det_descripcion').text(equipo.descripcion);
      $('#det_marca').text(equipo.marca + ' / ' + equipo.modelo);
      $('#det_serial').text(equipo.serial_color);
      $('#det_ubicacion').text(equipo.ubicacion);

      // Cargar historial
      let historial = historialSimulado[idEquipo] || [];
      let tbody = $('#cuerpo_historial_equipo');
      tbody.empty();

      if (historial.length === 0) {
        tbody.html('<tr><td colspan="5" class="text-center text-muted">No hay historial registrado.</td></tr>');
      } else {
        historial.forEach(function(h) {
          let badgeAnterior = getBadgeEstado(h.anterior);
          let badgeNuevo = getBadgeEstado(h.nuevo);
          
          tbody.append(`<tr>
            <td>${formatFecha(h.fecha)}</td>
            <td>${badgeAnterior}</td>
            <td>${badgeNuevo}</td>
            <td>${h.motivo}</td>
            <td>${h.usuario}</td>
          </tr>`);
        });
      }

      $('#modalDetalleEquipo').modal('show');
    }

    // ==========================================
    // ABRIR MODAL DE CAMBIAR ESTADO
    // ==========================================
    function abrirModalCambiarEstado(idEquipo, descripcion) {
      $('#id_equipo_cambiar').val(idEquipo);
      $('#desc_equipo_cambiar').val(descripcion);
      $('#estado_nuevo').val('');
      $('#motivo_cambio').val('');
      $('#modalCambiarEstado').modal('show');
    }

    // ==========================================
    // HELPERS
    // ==========================================
    function getBadgeEstado(estado) {
      if (estado === '-' || estado === '') return '<span class="text-muted">-</span>';
      let clase = 'badge-bueno';
      if (estado === 'Regular') clase = 'badge-regular';
      if (estado === 'Dañado') clase = 'badge-danado';
      if (estado === 'Desincorporado') clase = 'badge-desincorporado';
      return '<span class="badge ' + clase + '">' + estado + '</span>';
    }

    function formatFecha(fechaStr) {
      if (!fechaStr) return '-';
      let partes = fechaStr.split('-');
      return partes[2] + '/' + partes[1] + '/' + partes[0];
    }
  </script>
</div>