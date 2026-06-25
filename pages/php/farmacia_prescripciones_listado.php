<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Farmacia | Lista de Recipes</title>
  <?php
  include('includes/headerNav2.php');
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
  #DesactivarPrescripcion,
  #ModalReportePrescripcion,
  #modalConfirmarEstado,
  #modalListadoEstados {
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

  .badge-stock {
    font-size: 1.1em;
  }
</style>

<body class="hold-transition skin-blue sidebar-mini">
  <div class="content-wrapper">
    <?php
    $sql_base = "
        SELECT 
            'Interna' AS tipo_receta,
            c.Id_consulta AS id_prescripcion,
            CASE 
                WHEN SUM(CASE WHEN pm.estado_prescripcion = 'cancelado' THEN 1 ELSE 0 END) > 0 THEN 'Cancelado'
                WHEN SUM(CASE WHEN pm.estado_prescripcion = 'entregado' THEN 1 ELSE 0 END) = COUNT(pm.Id) THEN 'Entregado'
                WHEN SUM(CASE WHEN pm.estado_prescripcion = 'entregado' THEN 1 ELSE 0 END) > 0 THEN 'Parcial'
                ELSE 'Pendiente'
            END AS estado_entrega,
            c.fecha_consulta AS fecha_solicitud,
            paciente.nombre AS nom_pac, 
            paciente.apellido AS ape_pac,
            paciente.tipo_cedula AS tipo_cedula_pac, 
            paciente.cedula AS cedula_pac,
            paciente.genero AS genero_pac,
            rep.cedula AS cedula_representante,
            medico.nombre AS nom_med,
            medico.apellido AS ape_med,
             medico.genero AS genero_med,
            GROUP_CONCAT(CONCAT('• ', m.nombre_medicamento) SEPARATOR '<br>') AS nombre_medicamento,
            TIMESTAMPDIFF(YEAR, paciente.fecha_nacimiento, CURDATE()) < 18 AS es_menor
        FROM consulta c
        INNER JOIN prescripcion_medicamentos pm ON c.Id_consulta = pm.Id_consulta
        INNER JOIN persona paciente ON c.Id_paciente = paciente.id
        INNER JOIN detalle_medico dmd ON c.Id_medico = dmd.Id_detalle_medico
        INNER JOIN persona medico ON dmd.Id_persona = medico.id
        INNER JOIN descripcion_medicamento dm ON pm.Id_descripcion_medicamento = dm.Id
        INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
        LEFT JOIN presentacion p ON dm.Id_presentacion = p.Id_presentacion
        LEFT JOIN (
            SELECT 
                dpm.id_medicamento as id_desc, 
                GROUP_CONCAT(CONCAT(IFNULL(pa.nombre,''), ' ', IFNULL(dpm.cantidad_unidad_medida,''), ' ', IFNULL(um.unidad,'')) SEPARATOR ' + ') AS componentes
            FROM detalle_principio_medicamento dpm
            LEFT JOIN principio_activo pa ON dpm.id_principio_activo = pa.Id_principio_activo
            LEFT JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida
            GROUP BY dpm.id_medicamento
        ) comp_tbl ON dm.Id = comp_tbl.id_desc
        LEFT JOIN detalle_paciente_menor dpm_menor ON paciente.id = dpm_menor.id_persona
        LEFT JOIN persona rep ON dpm_menor.id_representante = rep.id
        WHERE pm.estatus = 1
        GROUP BY c.Id_consulta

        UNION ALL

        SELECT 
            'Externa' AS tipo_receta,
            sm.id_solicitud AS id_prescripcion,
            sm.estatus_general AS estado_entrega,
            DATE(sm.fecha_solicitud) AS fecha_solicitud,
            paciente.nombre AS nom_pac,
            paciente.apellido AS ape_pac,
            paciente.tipo_cedula AS tipo_cedula_pac,
            paciente.cedula AS cedula_pac,
            paciente.genero AS genero_pac,
            rep.cedula AS cedula_representante,
            medico.nombre AS nom_med,
            medico.apellido AS ape_med,
            medico.genero AS genero_med,
            GROUP_CONCAT(CONCAT('• ', m.nombre_medicamento, ' (Cant: ', ds.cantidad_recetada, ')') SEPARATOR '<br>') AS nombre_medicamento,
            TIMESTAMPDIFF(YEAR, paciente.fecha_nacimiento, CURDATE()) < 18 AS es_menor
        FROM solicitud_medicamento sm
        INNER JOIN detalle_solicitud ds ON sm.id_solicitud = ds.id_solicitud
        INNER JOIN descripcion_medicamento dm ON ds.id_medicamento = dm.Id
        INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
        INNER JOIN persona paciente ON sm.id_paciente = paciente.id
        INNER JOIN detalle_medico dmd ON sm.id_medico = dmd.Id_detalle_medico
        INNER JOIN persona medico ON dmd.Id_persona = medico.id
        LEFT JOIN presentacion p ON dm.Id_presentacion = p.Id_presentacion
        LEFT JOIN (
            SELECT 
                dpm.id_medicamento as id_desc, 
                GROUP_CONCAT(CONCAT(IFNULL(pa.nombre,''), ' ', IFNULL(dpm.cantidad_unidad_medida,''), ' ', IFNULL(um.unidad,'')) SEPARATOR ' + ') AS componentes
            FROM detalle_principio_medicamento dpm
            LEFT JOIN principio_activo pa ON dpm.id_principio_activo = pa.Id_principio_activo
            LEFT JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida
            GROUP BY dpm.id_medicamento
        ) comp_tbl ON dm.Id = comp_tbl.id_desc
        LEFT JOIN detalle_paciente_menor dpm_menor ON paciente.id = dpm_menor.id_persona
        LEFT JOIN persona rep ON dpm_menor.id_representante = rep.id
        WHERE sm.origen = 'Externo'
        GROUP BY sm.id_solicitud
    ";

    // --- LÓGICA DE PAGINACIÓN Y FILTROS ---
    $busqueda = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : '';
    $f_desde = isset($_GET['f_desde']) ? mysqli_real_escape_string($conexion, $_GET['f_desde']) : '';
    $f_hasta = isset($_GET['f_hasta']) ? mysqli_real_escape_string($conexion, $_GET['f_hasta']) : '';
    $f_estado = isset($_GET['f_estado']) ? mysqli_real_escape_string($conexion, $_GET['f_estado']) : '';
    $f_tipo_ced = isset($_GET['f_tipo_ced']) ? mysqli_real_escape_string($conexion, $_GET['f_tipo_ced']) : '';
    $f_cedula = isset($_GET['f_cedula']) ? mysqli_real_escape_string($conexion, $_GET['f_cedula']) : '';
    $f_paciente = isset($_GET['f_paciente']) ? mysqli_real_escape_string($conexion, $_GET['f_paciente']) : '';
    $f_doctor = isset($_GET['f_doctor']) ? mysqli_real_escape_string($conexion, $_GET['f_doctor']) : '';
    $f_medicamento = isset($_GET['f_medicamento']) ? mysqli_real_escape_string($conexion, $_GET['f_medicamento']) : '';
    $f_sexo_pac = isset($_GET['f_sexo_pac']) ? mysqli_real_escape_string($conexion, $_GET['f_sexo_pac']) : '';
    $f_sexo_med = isset($_GET['f_sexo_med']) ? mysqli_real_escape_string($conexion, $_GET['f_sexo_med']) : '';
    $f_cant_min = isset($_GET['f_cant_min']) ? (int)$_GET['f_cant_min'] : 0;
    $f_cant_max = isset($_GET['f_cant_max']) ? (int)$_GET['f_cant_max'] : 0;

    $registros_por_pagina = 14;
    $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $inicio = ($pagina_actual - 1) * $registros_por_pagina;

    $donde = " WHERE 1=1";

    // Filtro rápido original
    if ($busqueda != '') {
      $donde .= " AND (nom_pac LIKE '%$busqueda%' 
                 OR ape_pac LIKE '%$busqueda%' 
                 OR CONCAT(nom_pac, ' ', ape_pac) LIKE '%$busqueda%'
                 OR cedula_pac LIKE '%$busqueda%' 
                 OR nombre_medicamento LIKE '%$busqueda%')";
    }

    // Filtros avanzados
    if ($f_desde != '') {
      $donde .= " AND fecha_solicitud >= '$f_desde'";
    }
    if ($f_hasta != '') {
      $donde .= " AND fecha_solicitud <= '$f_hasta'";
    }
    if ($f_estado != '') {
      // Validar coincidencias de estados unificados
      if ($f_estado == 'Entregado') {
        $donde .= " AND estado_entrega IN ('Entregado', 'Completado', 'Completada')";
      } else {
        $donde .= " AND estado_entrega LIKE '%$f_estado%'";
      }
    }
    if ($f_tipo_ced != '') {
      $donde .= " AND tipo_cedula_pac = '$f_tipo_ced'";
    }
    if ($f_cedula != '') {
      $donde .= " AND cedula_pac LIKE '%$f_cedula%'";
    }
    if ($f_paciente != '') {
      $donde .= " AND CONCAT(nom_pac, ' ', ape_pac) LIKE '%$f_paciente%'";
    }
    if ($f_doctor != '') {
      $donde .= " AND CONCAT(nom_med, ' ', ape_med) LIKE '%$f_doctor%'";
    }
    if ($f_medicamento != '') {
      $donde .= " AND nombre_medicamento LIKE '%$f_medicamento%'";
    }
    if ($f_sexo_pac != '') {
      $donde .= " AND genero_pac = '$f_sexo_pac'";
    }
    if ($f_sexo_med != '') {
      $donde .= " AND genero_med = '$f_sexo_med'";
    }

    // Nota sobre cantidades: Dado que `nombre_medicamento` agrupa con GROUP_CONCAT,
    // buscar por cantidad exacta requiere procesar la cadena generada si quieres filtrar la vista unificada.
    if ($f_cant_min > 0) {
      $donde .= " AND nombre_medicamento REGEXP 'Cant: ([0-9]+)' AND CAST(REGEXP_SUBSTR(nombre_medicamento, '(?<=Cant: )[0-9]+') AS UNSIGNED) >= $f_cant_min";
    }
    if ($f_cant_max > 0) {
      $donde .= " AND nombre_medicamento REGEXP 'Cant: ([0-9]+)' AND CAST(REGEXP_SUBSTR(nombre_medicamento, '(?<=Cant: )[0-9]+') AS UNSIGNED) <= $f_cant_max";
    }

    // Contar el total de registros FILTRADOS unificados
    $sql_conteo = "SELECT COUNT(*) as total FROM ($sql_base) AS base_unificada $donde";
    $resultado_conteo = mysqli_query($conexion, $sql_conteo);
    $fila_conteo = mysqli_fetch_assoc($resultado_conteo);
    $total_registros = $fila_conteo['total'];
    $total_paginas = ceil($total_registros / $registros_por_pagina);

    // --- CONTEO PARA LOS BOTONES DE ESTADO ---
    $pendientes = $completadas = $parciales = $canceladas = 0;
    $sql_totales = "SELECT estado_entrega, COUNT(*) as total FROM ($sql_base) AS base_unificada GROUP BY estado_entrega";
    $res_totales = mysqli_query($conexion, $sql_totales);

    while ($tot = mysqli_fetch_assoc($res_totales)) {
      $estado = strtolower($tot['estado_entrega']);

      // Corregido: Usamos += para acumular y reparamos la lógica de las condiciones condicionales
      if ($estado == 'pendiente') {
        $pendientes += $tot['total'];
      } elseif ($estado == 'entregado' || $estado == 'completado' || $estado == 'completada') {
        $completadas += $tot['total'];
      } elseif ($estado == 'parcial' || $estado == 'parcialmente entregado') {
        $parciales += $tot['total'];
      } elseif ($estado == 'cancelado' || $estado == 'no entregado') {
        $canceladas += $tot['total'];
      }
    }
    ?>

    <section class="content-header">
      <h1>Control de Despacho de Medicamentos (<span id="contador-total-ajax"><?php echo $total_registros; ?></span> Récipes)</h1>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="box box-primary">
            <div class="box-header with-border">
              <p class="pull-right" style="width:5px;"></p>
              <a href="farmacia_inventario_listado.php" style="height:31px;" class="btn-sm btn-primary pull-right">Ir al Inventario</a>
              <p class="pull-right" style="width:5px;"></p>
              <?php if (in_array('Generar Reporte de Recetas', $_SESSION["permisos"])) : ?>
                <a href="#" class="btn-sm btn-info pull-right reporte" style="height:31px;"><i class="fa fa-book"></i> Generar Reporte </a>
              <?php endif; ?>
              <p class="pull-right" style="width:5px;"></p>
              <form method="GET" action="" style="display:inline;">
                <input type="text" id="buscar" name="buscar" class="form-control" placeholder="Escriba para buscar..." value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>" autocomplete="off" style="border-radius:0; height:10%; width:250px; display:inline-block;">
              </form>

              <p class="pull-right" style="width:5px;"></p>
              <span data-toggle="tooltip" data-placement="right" title="Aqui podras filtrar de manera avanzada la busqueda de recetas.">
                <button type="button" class="btn-sm btn-primary btn-sm pull-left" data-toggle="modal" data-target="#modalBusquedaAvanzada">
                  <i class="fa fa-filter"><img src="../../recursos/imagenes/iconos/filtrar.png" style="width:10px; height:10px; filter:invert(1);" title="filtrar receta"></i>
                </button>
              </span>

              <p class="pull-left" style="width:5px;"></p>
              <button class="btn-sm btn-light pull-left btn-sm btn-abrir-modal-estado" data-estado="Cancelado">Canceladas (<?php echo $canceladas; ?>)</button>
              <p class="pull-left" style="width:5px;"></p>
              <button class="btn-sm btn-light pull-left btn-sm btn-abrir-modal-estado" data-estado="Parcial">Parciales (<?php echo $parciales; ?>)</button>
              <p class="pull-left" style="width:5px;"></p>
              <button class="btn-sm btn-light pull-left btn-sm btn-abrir-modal-estado" data-estado="Pendiente">Pendientes (<?php echo $pendientes; ?>)</button>
              <p class="pull-left" style="width:5px;"></p>
              <button class="btn-sm btn-light pull-left btn-sm btn-abrir-modal-estado" data-estado="Entregado">Completadas (<?php echo $completadas; ?>)</button>
            </div>
          </div>
          <br><br>

          <div class="box-body">
            <div id="contenedorTabla">
              <table class="table table-sm table-hover mt-4" width="100%" id="t_user">
                <thead class="table-dark" style="background-color: #222; color: white; font-size: 12px;">
                  <tr>
                    <th>Fecha Consulta</th>
                    <th>Paciente / Tipo</th>
                    <th>Médico Tratante</th>
                    <th>Medicamento Solicitado</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Acciones</th>
                  </tr>
                </thead>
                <tbody class="tbody" width="100%" style="font-size: 12px;">
                  <?php
                  // ==============================================================================
                  // 2. CONSULTA FINAL PARA OBTENER LOS DATOS Y STOCK
                  // ==============================================================================
                  $query = "
                    SELECT base_unificada.*
                    FROM ($sql_base) AS base_unificada
                    $donde
                    ORDER BY fecha_solicitud DESC 
                    LIMIT $inicio, $registros_por_pagina
                  ";

                  $resultado = mysqli_query($conexion, $query);

                  while ($row = mysqli_fetch_assoc($resultado)) {

                    // Definir etiqueta del tipo de receta
                    if ($row['tipo_receta'] === 'Interna') {
                      $etiquetaTipo = ($row['es_menor'] == 1) ? '<span class="label label-info">Interna - Rep.</span>' : '<span class="label label-primary">Interna - Pac.</span>';
                    } else {
                      $etiquetaTipo = '<span class="label label-warning" style="background-color:#f39c12;">Externa</span>';
                    }
                  ?>
                    <tr>
                      <td><?php echo date('d/m/Y', strtotime($row['fecha_solicitud'])); ?></td>

                      <td>
                        <?php echo $etiquetaTipo; ?><br>
                        <strong><?php echo htmlspecialchars(trim($row['nom_pac'] . " " . $row['ape_pac'])); ?></strong><br>
                        <small><?php echo htmlspecialchars($row['tipo_cedula_pac']); ?>-<?php echo htmlspecialchars($row['cedula_pac']); ?></small>
                      </td>

                      <td>
                        Dr/a. <?php echo htmlspecialchars(trim($row['nom_med'] . " " . $row['ape_med'])); ?>
                      </td>

                      <td>
                        <span class="text-blue"><?php echo $row['nombre_medicamento']; ?></span><br>
                      </td>


                      <td class="text-center">
                        <?php
                        if ($row['estado_entrega'] == 'pendiente' || $row['estado_entrega'] == 'Pendiente') {
                          echo '<span class="badge bg-yellow">Pendiente</span>';
                        } else if ($row['estado_entrega'] == 'Parcial' || $row['estado_entrega'] == 'Parcialmente Entregado') {
                          echo '<span class="badge bg-yellow">Parcial</span>';
                        } else if ($row['estado_entrega'] == 'no entregado' || $row['estado_entrega'] == 'No entregado') {
                          echo '<span class="badge bg-default">No Entregado</span>';
                        } else if ($row['estado_entrega'] == 'Cancelado') {
                          echo '<span class="badge bg-crimson">Cancelado</span>';
                        } else {
                          echo '<span class="badge bg-green">Entregado</span>';
                        }
                        ?>
                      </td>

                      <?php if (in_array('Gestionar acciones de recetas', $_SESSION["permisos"])) : ?>
                        <td class="text-center">
                          <?php if ($row['estado_entrega'] == 'pendiente' || $row['estado_entrega'] == 'Parcialmente Entregado' || $row['estado_entrega'] == 'Parcial' || $row['estado_entrega'] == 'Pendiente') : ?>
                            <?php
                            $cedula_a_enviar = ($row['es_menor'] == 1 && !empty($row['cedula_representante'])) ? $row['cedula_representante'] : $row['cedula_pac'];
                            ?>
                            <?php if (in_array('Ver informacion de recetas', $_SESSION["permisos"])) : ?>
                              <a href="farmacia_prescripciones_ver.php?id=<?php echo $row['id_prescripcion']; ?>&tipo=<?php echo $row['tipo_receta']; ?>" class="btn btn-info btn-sm" title="Ver Informacion">
                                <img src="../../recursos/imagenes/iconos/info.png" style="width:15px; height:15px;">
                              </a>
                            <?php endif; ?>
                            <?php if (in_array('Generar Despacho de Inventario', $_SESSION["permisos"])) : ?>
                              <a href="farmacia_inventario_movimiento_despacho.php?id_pres=<?php echo $row['id_prescripcion']; ?>&pac=<?php echo urlencode($cedula_a_enviar); ?>&menor=<?php echo $row['es_menor']; ?>&tipo=<?php echo $row['tipo_receta']; ?>&from=prescripciones" class="btn btn-success btn-sm" title="Despachar Receta">
                                <img src="../../recursos/imagenes/iconos/enviar.png" style="width:15px; height:15px;">
                              </a>
                            <?php endif; ?>
                            <?php if (in_array('Cancelar Recetas', $_SESSION["permisos"])) : ?>
                              <button onclick="cambiarEstado(<?php echo $row['id_prescripcion'] ?>, 'no entregado', '<?php echo $row['tipo_receta'] ?>')" class="btn btn-sm btn-danger btn-accion-rapida" title="Cancelar">
                                <img src="../../recursos/imagenes/iconos/cancelar.png" style="width:15px; height:15px;">
                              </button>
                            <?php endif; ?>

                          <?php elseif ($row['estado_entrega'] == 'no entregado' || $row['estado_entrega'] == 'Cancelado') : ?>
                            <?php if (in_array('Ver informacion de recetas', $_SESSION["permisos"])) : ?>
                              <a href="farmacia_prescripciones_ver.php?id=<?php echo $row['id_prescripcion']; ?>&tipo=<?php echo $row['tipo_receta']; ?>" class="btn btn-info btn-sm" title="Ver Informacion">
                                <img src="../../recursos/imagenes/iconos/info.png" style="width:15px; height:15px;">
                              </a>
                            <?php endif; ?>
                          <?php else : ?>
                            <?php if (in_array('Ver informacion de recetas', $_SESSION["permisos"])) : ?>
                              <a href="farmacia_prescripciones_ver.php?id=<?php echo $row['id_prescripcion']; ?>&tipo=<?php echo $row['tipo_receta']; ?>" class="btn btn-info btn-sm" title="Ver Informacion">
                                <img src="../../recursos/imagenes/iconos/info.png" style="width:15px; height:15px;">
                              </a>
                            <?php endif; ?>
                          <?php endif; ?>
                        </td>
                      <?php endif; ?>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
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
  </div>
  </div>

  <?php
  // Lógica para preparar los mensajes de los modales de sesión
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

  <div class="modal" id="modalListadoEstados" role="dialog">
    <div class="modal-dialog modal-lg" role="document" style="width: 90%;">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:white;"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="tituloModalEstados" style="color:white;">Recetas</h4>
        </div>
        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
          <div id="loader-modal-estados" class="text-center" style="display:none;">
            <i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>
            <p>Cargando recetas...</p>
          </div>
          <div id="contenido-modal-estados">
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalConfirmarEstado" tabindex="-1" role="dialog" aria-labelledby="modalLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: #dc3545; color: white;">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="modalLabel">Confirmar Acción</h4>
        </div>
        <div class="modal-body">
          <p id="mensajeModal">¿Está seguro de que desea cancelar esta receta?</p>

          <div id="divMotivoCancelacion" style="display: none; margin-top: 15px;">
            <label for="motivo_cancelacion">Motivo de la cancelación <span class="text-danger">*</span></label>
            <textarea id="motivo_cancelacion" name="motivo_cancelacion" class="form-control" rows="3" placeholder="Especifique el motivo por el cual se cancela el récipe..."></textarea>
            <small id="errorMotivo" class="text-danger" style="display:none; font-weight:bold;">Debe especificar un motivo obligatorio.</small>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
          <button type="button" id="btnConfirmarEstado" class="btn btn-danger">Aceptar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalBusquedaAvanzada" tabindex="-1" role="dialog" aria-labelledby="modalBusquedaAvanzadaLabel">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <form id="formBusquedaAvanzada" method="GET" action="">
          <div class="modal-header bg-primary">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="modalBusquedaAvanzadaLabel"><i class="fa fa-filter"></i> Filtros Avanzados de Récipes</h4>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-3 form-group">
                <label>Fecha Desde</label>
                <input type="date" name="f_desde" class="form-control" max="<?php echo date('Y-m-d'); ?>">
              </div>
              <div class="col-md-3 form-group">
                <label>Fecha Hasta</label>
                <input type="date" name="f_hasta" class="form-control" min="<?php echo date('Y-m-d'); ?>">
              </div>

              <div class="col-md-3 form-group">
                <label>Estado de Entrega</label>
                <select name="f_estado" class="form-control">
                  <option value="">Todos</option>
                  <option value="Pendiente">Pendiente</option>
                  <option value="Parcial">Parcial</option>
                  <option value="Entregado">Completado/Entregado</option>
                  <option value="Cancelado">Cancelado</option>
                </select>
              </div>
              <div class="col-md-3 form-group">
                <label>Tipo de Cédula</label>
                <select name="f_tipo_ced" class="form-control">
                  <option value="">Todos</option>
                  <option value="V">V - Venezolano</option>
                  <option value="PN">PN - Partida de nacimiento</option>
                  <option value="RP">RP - Representado</option>
                </select>
              </div>
            </div>

            <div class="row">
              <div class="col-md-3 form-group">
                <label>Cédula Paciente</label>
                <input type="text" name="f_cedula" class="form-control" placeholder="Ej: 22333333" oninput="this.value = this.value.replace(/[^0-9\-]/g, '');">
              </div>
              <div class="col-md-3 form-group">
                <label>Nombre Paciente</label>
                <input type="text" name="f_paciente" class="form-control" placeholder="Escribe el nombre..." oninput="this.value = this.value.replace(/[0-9]/g, '');">
              </div>
              <div class="col-md-3 form-group">
                <label>Nombre Doctor</label>
                <input type="text" name="f_doctor" class="form-control" placeholder="Escribe el doctor..." oninput="this.value = this.value.replace(/[0-9]/g, '');">
              </div>
              <div class="col-md-3 form-group">
                <label>Medicamento</label>
                <input type="text" name="f_medicamento" class="form-control" placeholder="Escribe el medicamento..." oninput="this.value = this.value.replace(/[0-9]/g, '');">
              </div>
            </div>

            <div class="row">
              <div class="col-md-3 form-group">
                <label>Sexo Paciente</label>
                <select name="f_sexo_pac" class="form-control">
                  <option value="">Ambos</option>
                  <option value="Masculino">Masculino</option>
                  <option value="Femenino">Femenino</option>
                </select>
              </div>
              <div class="col-md-3 form-group">
                <label>Sexo Médico</label>
                <select name="f_sexo_med" class="form-control">
                  <option value="">Ambos</option>
                  <option value="Masculino">Masculino</option>
                  <option value="Femenino">Femenino</option>
                </select>
              </div>
              <div class="col-md-3 form-group">
                <label>Cantidad Mínima</label>
                <input type="number" name="f_cant_min" class="form-control" min="1" placeholder="Ej: 1" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
              </div>
              <div class="col-md-3 form-group">
                <label>Cantidad Máxima</label>
                <input type="number" name="f_cant_max" class="form-control" min="1" placeholder="Ej: 10" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
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

  <div class="modal" id="ModalReportePrescripcion" tabindex="-1" style="font-size: 10px;" role="dialog" aria-labelledby="myModalLabelReporte">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="myModalLabelReporte"><i class="fa fa-file-pdf-o"></i> Generar Reporte de Récipes</h4>
        </div>
        <div class="modal-body">
          <p class="text-muted">Seleccione los criterios para generar el listado de récipes (PDF).</p>

          <div class="form-group">
            <label>Tipo de Récipes (Estado):</label>
            <select class="form-control" id="tipo_reporte_receta">
              <option value="todos">Historial Completo (Todas las recetas)</option>
              <option value="entregados">Completadas / Totalmente Despachadas</option>
              <option value="pendientes">Pendientes / Parcialmente Entregadas (Por falta de stock)</option>
              <option value="cancelados">Canceladas / No Entregadas</option>
            </select>
          </div>

          <div class="row">
            <div class="col-md-6 form-group">
              <label>Fecha Desde (Opcional):</label>
              <input type="date" id="rep_fecha_desde" class="form-control" max="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="col-md-6 form-group">
              <label>Fecha Hasta (Opcional):</label>
              <input type="date" id="rep_fecha_hasta" class="form-control" min="<?php echo date('Y-m-d'); ?>">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-primary" id="btnEjecutarReporteRecetas">Generar PDF</button>
        </div>
      </div>
    </div>
  </div>

  <?php
  include("includes/footer.php");
  ?>

  <script>
    $(document).ready(function() {

      // ==========================================
      // 1. SISTEMA AJAX PARA FILTROS Y PAGINACIÓN
      // ==========================================

      window.cargarDatosAjax = function(url) {
        $('#contenedorTabla tbody').css('opacity', '0.4');

        $.get(url, function(data) {
          // Extraemos la tabla, paginación Y el nuevo contador
          var nuevaTabla = $(data).find('#contenedorTabla').html();
          var nuevaPaginacion = $(data).find('.pagination').html();
          var nuevoContador = $(data).find('#contador-total-ajax').html(); // <-- Nuevo

          // Inyectamos los datos en la vista actual sin recargar
          $('#contenedorTabla').html(nuevaTabla);
          $('.pagination').html(nuevaPaginacion);
          $('#contador-total-ajax').html(nuevoContador); // <-- Actualiza el H1 instantáneamente

          window.history.pushState(null, '', url);

        }).fail(function() {
          alert("Error de conexión al aplicar filtros.");
          $('#contenedorTabla tbody').css('opacity', '1');
        });
      };

      // Interceptar búsqueda rápida
      $('form:has(#buscar)').on('submit', function(e) {
        e.preventDefault();
        var url = 'farmacia_prescripciones_listado.php?' + $(this).serialize();
        cargarDatosAjax(url);
      });

      // Interceptar búsqueda avanzada modal
      $('#formBusquedaAvanzada').on('submit', function(e) {
        e.preventDefault();
        var url = 'farmacia_prescripciones_listado.php?' + $(this).serialize();
        $('#modalBusquedaAvanzada').modal('hide');
        cargarDatosAjax(url);
      });

      // Interceptar paginación
      $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        if (url) {
          cargarDatosAjax(url);
        }
      });

      // Limpiar filtros via AJAX
      window.limpiarFiltrosAjax = function() {
        $('#formBusquedaAvanzada')[0].reset();
        $('#buscar').val('');
        var urlLimpia = window.location.href.split('?')[0]; // URL base sin parámetros
        cargarDatosAjax(urlLimpia);
        $('#modalBusquedaAvanzada').modal('hide');
      };


      // ==========================================
      // 2. FORZAR CIERRE DE MODALES EN LA "X"
      // ==========================================
      // A veces Bootstrap choca con AJAX, esto asegura que cualquier botón "cerrar" funcione.
      $(document).on('click', '[data-dismiss="modal"]', function() {
        $(this).closest('.modal').modal('hide');
      });


      // ==========================================
      // 3. LÓGICA DE BOTONES SUPERIORES (PENDIENTES, COMPLETADAS...)
      // ==========================================
      $('.btn-abrir-modal-estado').on('click', function() {
        var estado = $(this).data('estado');
        $('#tituloModalEstados').text('Recetas - Estado: ' + estado);
        $('#contenido-modal-estados').empty();
        $('#loader-modal-estados').show();
        $('#modalListadoEstados').modal('show');

        $.ajax({
          url: '../../cfg/ajax/get_recetas_por_estado.php',
          type: 'GET',
          data: {
            estado: estado
          },
          success: function(response) {
            $('#loader-modal-estados').hide();
            $('#contenido-modal-estados').html(response);
          },
          error: function() {
            $('#loader-modal-estados').hide();
            $('#contenido-modal-estados').html('<div class="alert alert-danger">Error al cargar los datos.</div>');
          }
        });
      });
      
      // -------------------------------------------------------------
      // LÓGICA PARA EL REPORTE DE RECETAS EN PDF
      // -------------------------------------------------------------
      $('.reporte').on('click', function(e) {
        e.preventDefault();
        $('#ModalReportePrescripcion').modal('show');
      });

      $('#btnEjecutarReporteRecetas').on('click', function() {
        var tipo = $('#tipo_reporte_receta').val();
        var fecha_desde = $('#rep_fecha_desde').val();
        var fecha_hasta = $('#rep_fecha_hasta').val();

        // Construimos la URL con los parámetros
        var urlPDF = '../../cfg/reportes/generar_pdf_recetas.php?tipo=' + tipo;
        
        if(fecha_desde !== '') urlPDF += '&desde=' + fecha_desde;
        if(fecha_hasta !== '') urlPDF += '&hasta=' + fecha_hasta;

        // Abrimos el PDF en una nueva pestaña
        window.open(urlPDF, '_blank');
        $('#ModalReportePrescripcion').modal('hide');
      });

      // ==========================================
      // 4. LÓGICA DEL MODAL DE CANCELACIÓN (MOTIVOS)
      // ==========================================
      let datosEstadoTemp = {
        id: null,
        nuevoEstado: null,
        tipoReceta: null
      };

      window.cambiarEstado = function(id, nuevoEstado, tipoReceta) {
        datosEstadoTemp = {
          id: id,
          nuevoEstado: nuevoEstado,
          tipoReceta: tipoReceta
        };

        if (nuevoEstado === 'no entregado' || nuevoEstado === 'Cancelado') {
          $('#divMotivoCancelacion').slideDown();
          $('#motivo_cancelacion').val('');
          $('#errorMotivo').hide();
        } else {
          $('#divMotivoCancelacion').slideUp();
        }

        $('#modalConfirmarEstado').modal('show');
      };

      $('#btnConfirmarEstado').click(function() {
        let motivoCancelacion = '';

        if (datosEstadoTemp.nuevoEstado === 'no entregado' || datosEstadoTemp.nuevoEstado === 'Cancelado') {
          motivoCancelacion = $('#motivo_cancelacion').val().trim();
          if (motivoCancelacion === '') {
            $('#errorMotivo').slideDown();
            $('#motivo_cancelacion').focus();
            return;
          }
        }

        const btn = $(this);
        btn.prop('disabled', true);
        btn.text('Procesando...');

        $.post('../../cfg/ajax/actualizar_estado_receta.php', {
          id: datosEstadoTemp.id,
          estado_entrega: datosEstadoTemp.nuevoEstado,
          tipo: datosEstadoTemp.tipoReceta,
          motivo: motivoCancelacion
        }, function(data) {
          if (data.trim() == 'ok') {
            cargarDatosAjax(window.location.href);
            $('#modalConfirmarEstado').modal('hide');
          } else {
            alert('Error al actualizar: ' + data);
          }
        }).fail(function() {
          alert('Error de conexión con el servidor');
        }).always(function() {
          btn.prop('disabled', false);
          btn.text('Aceptar');
        });
      });

    });
  </script>
</body>

</html>