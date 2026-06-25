<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Farmacia | Crear Pedido</title>
  <?php
  include('includes/headerNav2.php');
  include("../../cfg/conexion.php");
  ?>
  <style>
    /* ---------------------------------------------------------------------- */
    /* ANIMACIONES Y ESTILOS DE MODALES (Heredados de Entrada)                */
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

    /* ---------------------------------------------------------------------- */
    /* ESTILOS PARA EL CONTENEDOR DE TAGS (Simulación de form-control)        */
    /* ---------------------------------------------------------------------- */
    .tags-input-container {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 5px;
      width: 100%;
      min-height: 34px;
      /* Altura estándar de form-control en Bootstrap */
      padding: 4px 12px;
      font-size: 14px;
      line-height: 1.42857143;
      color: #555;
      background-color: #fff;
      border: 1px solid #ccc;
      border-radius: 4px;
      box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075);
      transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
      cursor: text;
    }

    /* Efecto focus idéntico al de Bootstrap */
    .tags-input-container:focus-within {
      border-color: #66afe9;
      outline: 0;
      box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075), 0 0 8px rgba(102, 175, 233, .6);
    }

    .tags-input-fake {
      border: none;
      outline: none;
      background: transparent;
      flex-grow: 1;
      min-width: 150px;
      height: 24px;
      box-shadow: none !important;
    }

    .tag-badge {
      display: inline-flex;
      align-items: center;
      padding: 4px 8px;
      border-radius: 3px;
      font-size: 12px;
      margin-bottom: 2px;
      margin-top: 2px;
    }

    .tag-badge .remove-tag {
      margin-left: 6px;
      cursor: pointer;
      font-weight: bold;
    }

    .modal.in .modal-dialog,
    #avisoModal,
    #modalGuardar,
    #modalAgregarMedicamento,
    #modalBúsquedaAvanzadaMedicamento,
    #modalRegresarInventario {
      animation: fadeIn 0.4s ease-out;
    }

    .modal.out .modal-dialog {
      animation: fadeOut 0.4s ease-in;
    }

    .modal-open .modal-backdrop {
      opacity: 0.7 !important;
      animation: pulse-opacity 0.3s forwards;
    }

    /* ---------------------------------------------------------------------- */
    /* ESTILOS DE VALIDACIÓN Y LAYOUT (Heredados de Entrada)                  */
    /* ---------------------------------------------------------------------- */
    .input-error {
      border: 2px solid crimson !important;
      box-shadow: 0 0 5px crimson;
    }

    .btn-error-sombreado {
      background-color: #f8d7da !important;
      color: #721c24 !important;
      border: 1px solid #f5c6cb !important;
      box-shadow: 0 0 10px rgba(220, 53, 69, 0.6) !important;
      transition: all 0.3s ease;
    }

    .table-detalle th {
      background-color: #f4f4f4;
      text-align: center;
    }

    .table-detalle td {
      text-align: center;
      vertical-align: middle !important;
    }

    .area-trabajo-blanca {
      background-color: #ffffff;
      padding: 25px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
      margin-bottom: 20px;
      overflow: hidden;
    }

    input[type="date"][readonly],
    input[type="date"][disabled] {
      background-color: #eeeeee !important;
      cursor: not-allowed;
    }

    .table-responsive {
      max-height: 220px;
      overflow-y: auto;
    }

    .table-detalle thead th {
      position: sticky;
      top: 0;
      background-color: #f4f4f4;
      z-index: 1;
    }
  </style>
</head>

<body>
  <div class="content-wrapper">
    <section class="content-header">
      <h1>Creación de pedidos <small>Generar nueva solicitud a proveedor</small></h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="farmacia_pedidos_listado.php"><i class="fa fa-shopping-cart"></i>Pedidos</a></li>
        <li class="active"><a href="#"><i class="fa fa-plus"></i>Crear</a></li>
      </ol>
    </section>

    <section class="content">
      <form id="formularioPedido" action="../../cfg/procesar_pedidos.php" method="POST" autocomplete="off">
        <input type="hidden" name="op" value="guardar_pedido">
        <input type="hidden" name="detalles_pedido_json" id="detalles_pedido_json">

        <div class="row">
          <div class="col-md-12">

            <div class="area-trabajo-blanca">

              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title"><i class="fa fa-clipboard"></i> Datos de la solicitud:</h3>
                </div>
                <div class="box-body">
                  <div class="row">

                    <div class="col-sm-4 form-group">
                      <label>Usuario Solicitante (*):</label>
                      <?php
                      $id_persona_logueada = $_SESSION['id'] ?? 0;
                      ?>
                      <select name="id_usuario" id="id_usuario" class="form-control" required>
                        <option value="">--- Seleccione el solicitante ---</option>
                        <?php
                        $sql_solicitante = "SELECT p.id, p.nombre, p.apellido, r.Id_rol 
                                            FROM persona p 
                                            INNER JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona
                                            INNER JOIN rol r ON dpr.Id_rol = r.Id_rol
                                            WHERE p.estatus IN (1, 2) AND r.Id_rol IN (1, 6)
                                            ORDER BY p.nombre ASC";
                        $res_solicitante = $conexion->query($sql_solicitante);

                        while ($row_sol = $res_solicitante->fetch_assoc()) {
                          $id_db = (int)$row_sol['id'];
                          $selected = ($id_db === (int)$id_persona_logueada) ? 'selected="selected"' : '';
                          echo '<option value="' . $id_db . '" ' . $selected . '>' .
                            htmlspecialchars($row_sol['nombre'] . ' ' . $row_sol['apellido']) .
                            '</option>';
                        }
                        ?>
                      </select>
                    </div>

                    <div class="col-sm-4 form-group">
                      <label>Proveedor (*):</label>
                      <select id="proveedor" name="id_proveedor" class="form-control" required>
                        <option value="">--- Seleccione un proveedor ---</option>
                        <?php
                        $sql_proveedor = "SELECT Id_proveedor, nombre_proveedor FROM proveedor WHERE estatus = 1 ORDER BY nombre_proveedor ASC";
                        $resultado_proveedor = $conexion->query($sql_proveedor);
                        if ($resultado_proveedor && $resultado_proveedor->num_rows > 0) {
                          while ($row_pro = $resultado_proveedor->fetch_assoc()) {
                            echo '<option value="' . $row_pro['Id_proveedor'] . '">' . htmlspecialchars($row_pro['nombre_proveedor']) . '</option>';
                          }
                        }
                        ?>
                      </select>
                    </div>

                    <div class="col-sm-3 form-group">
                      <label>Fecha y hora de ajuste (*):</label>
                      <div class="input-group">
                        <input type="date" id="fecha_pedido" name="fecha_pedido" class="form-control" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" onkeydown="return false;" style="width: 100%;">
                        <div class="input-group-btn" style="width: 100%;">
                          <input type="time" id="hora_pedido" name="hora_pedido" class="form-control" value="<?php date_default_timezone_set('America/Caracas');
                                                                                                              echo date('H:i'); ?>">
                        </div>
                      </div>
                    </div>

                  </div>
                </div>
              </div>

              <div class="box box-success">
                <div class="box-header with-border">
                  <h3 class="box-title"><i class="fa fa-medkit"></i> Medicamentos a Solicitar:</h3>
                  <div class="box-tools pull-right" style="margin-left:5px;">
                    <button type="button" class="btn btn-primary btn-sm" id="btnAbrirModalAgregar" style="width:200px;">
                      <i class="fa fa-plus"></i> Añadir Medicamento
                    </button>
                    <p></p>
                    <button type="button" class="btn btn-danger btn-sm" id="btnCargarReorden" style="width:200px;">
                      <i class="fa fa-refresh"></i> Cargar por Punto de Reorden
                    </button>
                  </div>
                </div>

                <div class="box-body">
                  <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-detalle" id="tablaDetallesPedido">
                      <thead>
                        <tr>
                          <th style="text-align: left;">Medicamento / Presentación</th>
                          <th style="width: 150px;">Cantidad Solicitada</th>
                          <th style="width: 100px;">Acciones</th>
                        </tr>
                      </thead>
                      <tbody id="cuerpoTablaPedido">
                        <tr id="filaVacia">
                          <td colspan="3" class="text-center text-muted">Aún no se han añadido medicamentos al pedido actual.</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <div class="box-footer text-right" style="background-color: transparent; border-top: 1px solid #f4f4f4; padding-top: 15px;">
                  <button type="button" class="btn btn-secondary" id="abrirModalRegresar">Regresar</button>
                  <button type="button" class="btn btn-success" id="btnValidarGuardar"><i class="fa fa-save"></i> Guardar Pedido</button>
                </div>
              </div>

            </div>
          </div>
        </div>
      </form>
    </section>

    <div class="modal" id="modalAgregarMedicamento" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header bg-primary">
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 1;">
              <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title" style="color: white;"><i class="fa fa-plus-circle"></i> Agregar al pedido</h4>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="filtro_busqueda_rapida">Busqueda Rapida:</label>
                  <input type="text" id="filtro_busqueda_rapida" name="filtro_busqueda_rapida" class="form-control" placeholder="Escriba nombre, principio activo, presentacion, etc...">
                </div>
              </div>
              <br><br>
              <div class="col-sm-12 form-group">
                <label>Medicamento del Catálogo (*):</label>
                <div class="input-group">
                  <select id="select_medicamento" class="form-control">
                    <option value="">--- Seleccione un Medicamento ---</option>
                    <?php
                    $sql_meds = "SELECT dm.Id AS id_desc, m.nombre_medicamento, p.nombre_presentacion,
                    GROUP_CONCAT(CONCAT(IFNULL(pa.nombre,''), ' ', IFNULL(dpm.cantidad_unidad_medida,''), ' ', IFNULL(um.unidad,'')) SEPARATOR ' + ') AS componentes
                    FROM descripcion_medicamento dm
                    INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
                    INNER JOIN presentacion p ON dm.Id_presentacion = p.Id_presentacion
                    LEFT JOIN detalle_principio_medicamento dpm ON dm.Id = dpm.id_medicamento
                    LEFT JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida
                    LEFT JOIN principio_activo pa ON dpm.id_principio_activo = pa.Id_principio_activo
                    WHERE m.estatus = 1 AND dm.estatus = 1 
                    GROUP BY dm.Id ORDER BY m.nombre_medicamento ASC";
                    $res_meds = $conexion->query($sql_meds);
                    while ($row_med = $res_meds->fetch_assoc()) {
                      $comp = trim($row_med['componentes']) ? " (" . htmlspecialchars($row_med['componentes']) . ")" : "";
                      echo '<option value="' . $row_med['id_desc'] . '" data-nombre="' . htmlspecialchars($row_med['nombre_medicamento'] . " [" . $row_med['nombre_presentacion'] . "]") . '" data-componentes="' . htmlspecialchars($row_med['componentes']) . '">' . htmlspecialchars($row_med['nombre_medicamento']) . $comp . " - [" . htmlspecialchars($row_med['nombre_presentacion']) . "]" . '</option>';
                    }
                    ?>
                  </select>
                  <span class="input-group-btn">
                    <button class="btn btn-info" type="button" data-toggle="modal" data-target="#modalBúsquedaAvanzadaMedicamento" style="height: 34px;">
                      <i><img src="../../recursos/imagenes/iconos/buscar.png" style="width:10px; height:10px;"></i>
                    </button>
                  </span>
                </div>
              </div>

              <div class="col-sm-4 form-group mt-3">
                <label>Existencia Actual:</label>
                <input type="text" id="existencia_actual" class="form-control" readonly disabled style="background-color: #f9f9f9;">
              </div>
              <div class="col-sm-4 form-group mt-3">
                <label>Stock Máximo:</label>
                <input type="text" id="stock_maximo" class="form-control" readonly disabled style="background-color: #f9f9f9;">
              </div>
              <div class="col-sm-4 form-group mt-3">
                <label>Cantidad a Solicitar (*):</label>
                <input type="text" id="cantidad_agregar" class="form-control" placeholder="Solo números" oninput="this.value = this.value.replace(/[^0-9]/g, ''); if(this.value === '0') this.value = '1';">
              </div>

            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-success" id="btnConfirmarAgregarLista"><i class="fa fa-check"></i> Añadir a la Lista</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal" id="modalBúsquedaAvanzadaMedicamento" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header bg-primary">
            <h5 class="modal-title" id="modalBúsquedaAvanzadaMedicamentoLabel" style="color: white;">Filtros de Búsqueda Avanzada</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 1;">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form id="formFiltroModal">
              <p class="text-muted">Complete uno o varios campos para refinar su búsqueda.</p>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="filtro_nombre">Nombre (o ID):</label>
                    <input type="text" id="filtro_nombre" name="filtro_nombre" class="form-control" placeholder="Escriba nombre o ID...">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="filtro_presentacion">Presentación:</label>
                    <select id="filtro_presentacion" name="filtro_presentacion" class="form-control">
                      <option value="">-- Todos --</option>
                      <?php
                      // Cargar tipos de medicamento dinámicamente
                      include("../../cfg/conexion.php"); // Asegura la conexión
                      $sql_tipos = "SELECT Id_presentacion, nombre_presentacion FROM presentacion WHERE estatus = 1 ORDER BY nombre_presentacion DESC";
                      $res_tipos = $conexion->query($sql_tipos);
                      while ($row_t = $res_tipos->fetch_assoc()) {
                        echo '<option value="' . $row_t['Id_presentacion'] . '">' . $row_t['nombre_presentacion'] . '</option>';
                      }
                      ?>
                    </select>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="filtro_principios">Principios activos (contiene):</label>
                    <input type="text" id="filtro_principios" name="filtro_principios" class="form-control" placeholder="Ej: Ibuprofeno">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="filtro_contenido_neto">Contenido neto:</label>
                    <input type="text" id="filtro_contenido_neto" name="filtro_contenido_neto" class="form-control" placeholder="Ej: 20 Capsulas">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="filtro_via">Vía de aplicación:</label>
                    <select id="filtro_via" name="filtro_via" class="form-control">
                      <option value="">-- Todas --</option>
                      <option value="Oral">Oral</option>
                      <option value="Sublingual">Sublingual</option>
                      <option value="Rectal">Rectal</option>
                      <option value="Intravenosa">Intravenosa</option>
                      <option value="Intramuscular">Intramuscular</option>
                      <option value="Subcutanea">Subcutanea</option>
                      <option value="Intradermica">Intradermica</option>
                      <option value="Topica">Topica</option>
                      <option value="Transdermica">Transdermica</option>
                      <option value="Inhalatoria">Inhalatoria</option>
                      <option value="Oftalmica">Oftalmica</option>
                      <option value="Otica">Otica</option>
                      <option value="Nasal">Nasal</option>
                      <option value="Vaginal">Vaginal</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="filtro_almacenamiento">C. de almacenamiento:</label>
                    <select id="filtro_almacenamiento" name="filtro_almacenamiento" class="form-control">
                      <option value="">-- Todas --</option>
                      <option value="-25_a_-10">Congelacion (-25°C a -10°C)</option>
                      <option value="2_a_8">Refrigeracion (2°C a 8°C)</option>
                      <option value="8_a_15">Lugar Fresco (8°C a 15°C)</option>
                      <option value="15_a_25">Temperatura Ambiente (15°C a 25°C)</option>
                      <option value="max_30">Temperatura Maxima (30°C)</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="filtro_laboratorio">Laboratorio:</label>
                    <select id="filtro_laboratorio" name="filtro_laboratorio" class="form-control">
                      <option value="">-- Todos --</option>
                      <?php
                      // Cargar laboratorios dinámicamente
                      $sql_labs = "SELECT Id_laboratorio, nombre_laboratorio FROM laboratorio WHERE estatus = 1 ORDER BY nombre_laboratorio ASC";
                      $res_labs = $conexion->query($sql_labs);
                      while ($row_l = $res_labs->fetch_assoc()) {
                        echo '<option value="' . $row_l['Id_laboratorio'] . '">' . $row_l['nombre_laboratorio'] . '</option>';
                      }
                      ?>
                    </select>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="filtro_excipientes">Excipientes (contiene):</label>
                    <input type="text" id="filtro_excipientes" name="filtro_excipientes" class="form-control" placeholder="Escriba texto de excipientes...">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="filtro_barcode">Código de barras:</label>
                    <input type="text" id="filtro_barcode" name="filtro_barcode" class="form-control" placeholder="Escriba código de barras exacto...">
                  </div>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-warning" id="btnLimpiarFiltros">Limpiar Filtros</button>
            <button type="button" class="btn btn-primary" id="btnAplicarFiltros">Aplicar Filtros</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="avisoModal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div id="headerAvisoPedido" class="modal-header bg-crimson">
            <button type="button" class="close" data-dismiss="modal" style="color: white; opacity: 1;">&times;</button>
            <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Alerta del Sistema</h4>
          </div>
          <div class="modal-body" id="cuerpoAviso" style="font-size: 15px;"></div>
          <div class="modal-footer" style="padding: 10px;">
            <button type="button" id="confirmAviso" class="btn btn-danger btn-sm" data-dismiss="modal">Entendido</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal" id="modalRegresarInventario" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header bg-crimson">
            <h5 class="modal-title" style="color: white;">Confirmación de Regreso</h5>
          </div>
          <div class="modal-body">
            <p>Al hacer clic en "Abandonar Formulario", perderá todos los datos no guardados. ¿Desea continuar?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <a href="farmacia_pedidos_listado.php" class="btn btn-danger">Abandonar Formulario</a>
          </div>
        </div>
      </div>
    </div>

    <div class="modal" id="modalGuardar" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-green">
            <button type="button" class="close" data-dismiss="modal" style="color: white; opacity: 1;">&times;</button>
            <h4 class="modal-title"><i class="fa fa-check-circle"></i> Confirmar Pedido</h4>
          </div>
          <div class="modal-body" style="font-size: 15px;">
            ¿Está seguro que desea registrar este pedido con los medicamentos seleccionados?
            <br><small class="text-muted">La orden quedará en estado "Pendiente".</small>
          </div>
          <div class="modal-footer" style="padding: 10px;">
            <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Modificar</button>
            <button type="button" id="confirmarGuardar" class="btn btn-success btn-sm">Sí, Confirmar</button>
          </div>
        </div>
      </div>
    </div>

  </div>

  <?php include('includes/footer.php'); ?>

  <script>
    let listaItemsPedido = [];
    let indexEdicion = -1;

    function mostrarAviso(mensaje, tipo = 'error') {
      // Si indicamos que es un éxito, pintamos el modal de verde. 
      // Para cualquier otro caso, nos aseguramos de devolverle su color rojo (bg-crimson).
      if (tipo === 'exito') {
        $('#headerAvisoPedido').removeClass('bg-crimson').addClass('bg-green');
        $('#confirmAviso').removeClass('btn-danger').addClass('btn-success');
      } else {
        $('#headerAvisoPedido').removeClass('bg-green').addClass('bg-crimson');
        $('#confirmAviso').removeClass('btn-success').addClass('btn-danger');
      }

      $('#cuerpoAviso').html(mensaje);
      $('#avisoModal').removeClass('out').addClass('in').modal('show');
    }

    $(document).ready(function() {

      // =====================================================================
      // SOLUCIÓN DEFINITIVA PARA MODALES ANIDADOS Y BACKDROPS (FONDOS)
      // =====================================================================

      // 1. Al abrir cualquier modal: Ajustar Z-index y evitar oscurecimiento múltiple
      $(document).on('show.bs.modal', '.modal', function() {
        // Calculamos un z-index progresivo por cada modal abierto para que no colisionen
        var zIndex = 1040 + (10 * $('.modal:visible').length);
        $(this).css('z-index', zIndex);

        setTimeout(function() {
          // Hacemos transparentes todos los backdrops excepto el último (el más reciente)
          // Esto evita que las opacidades de 0.7 se sumen y se vuelva negro.
          $('.modal-backdrop').not(':last').css('opacity', 0);

          // Alineamos el z-index del backdrop correspondiente debajo del modal actual
          $('.modal-backdrop').last().css('z-index', zIndex - 1);
        }, 0);
      });

      // 2. Al cerrar cualquier modal: Restaurar opacidad y arreglar el scroll
      $(document).on('hidden.bs.modal', '.modal', function() {
        // Si aún quedan modales abiertos después de cerrar este
        if ($('.modal:visible').length > 0) {
          // Volvemos a mostrar el backdrop del modal que quedó debajo
          $('.modal-backdrop').last().css('opacity', 0.7);

          // Mantenemos la clase modal-open en el body para que el scroll siga funcionando
          $('body').addClass('modal-open');
        } else {
          // Si ya no hay modales visibles, forzamos una limpieza estricta por si Bootstrap falla
          $('.modal-backdrop').remove();
          $('body').removeClass('modal-open');
          $('body').css('padding-right', '');
        }
      });

      // =====================================================================
      // AUTOMATIZACIÓN DE PEDIDOS POR PUNTO DE REORDEN
      // =====================================================================
      $('#btnCargarReorden').on('click', function() {
        let $btn = $(this);

        // Deshabilitamos el botón temporalmente y cambiamos el icono para dar feedback visual
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Evaluando Inventario...');

        $.ajax({
          url: '../../cfg/ajax/obtener_reorden_ajax.php',
          type: 'GET',
          dataType: 'json',
          success: function(response) {
            if (response.error) {
              mostrarAviso("Error en la consulta del sistema: " + response.error);
              return;
            }

            if (response.length === 0) {
              mostrarAviso("<strong>¡Inventario Seguro!</strong> No existen medicamentos que estén iguales o por debajo del stock mínimo establecido.");
              return;
            }

            let itemsAgregados = 0;

            response.forEach(function(item) {
              // Validación crucial: Evitar que reescriba o duplique si el usuario ya lo cargó manualmente
              let yaExiste = listaItemsPedido.find(prod => prod.id_descripcion == item.id_descripcion);

              if (!yaExiste) {
                listaItemsPedido.push({
                  id_descripcion: item.id_descripcion,
                  nombre: item.nombre_completo,
                  cantidad: parseInt(item.cantidad_a_pedir),
                  existencia_actual: parseInt(item.existencia_actual), // <- NUEVO
                  stock_maximo: parseInt(item.stock_maximo) // <- NUEVO
                });
                itemsAgregados++;
              }
            });

            if (itemsAgregados > 0) {
              actualizarTablaVisual();
              mostrarAviso("Se cargaron automáticamente <strong>" + itemsAgregados + "</strong> medicamentos en estado crítico.", "exito");
            } else {
              mostrarAviso("Los medicamentos que requieren reorden ya se encuentran enlistados en la tabla.");
            }
          },
          error: function() {
            mostrarAviso("No se pudo conectar con el servidor para procesar el Punto de Reorden.");
          },
          complete: function() {
            $btn.prop('disabled', false).html('<i class="fa fa-refresh"></i> Cargar por Punto de Reorden');
          }
        });
      });

      // Consultar stock al seleccionar un medicamento en el modal
      $('#select_medicamento').on('change', function() {
        let idMed = $(this).val();
        $('#existencia_actual, #stock_maximo').val(''); // Limpiamos campos

        if (idMed) {
          $.ajax({
            url: '../../cfg/ajax/obtener_descripcion_medicamento.php',
            type: 'POST',
            data: {
              id: idMed,
              modo: 'pedido'
            },
            dataType: 'json',
            success: function(data) {
              if (!data.error) {
                let ext = data.existencia_actual !== null ? parseInt(data.existencia_actual) : 0;
                let sMax = data.stock_maximo !== null ? parseInt(data.stock_maximo) : 0;

                $('#existencia_actual').val(ext);
                $('#stock_maximo').val(sMax);

                // Si ya el stock está lleno, bloqueamos el input de cantidad
                if (sMax > 0 && ext >= sMax) {
                  $('#cantidad_agregar').prop('disabled', true).val('');
                  mostrarAviso(`🚫 <b>Atención:</b> La existencia actual (${ext}) ya cubre o supera el Stock Máximo permitido (${sMax}). No es necesario pedir más de este medicamento.`);
                } else {
                  $('#cantidad_agregar').prop('disabled', false);
                }
              }
            }
          });
        } else {
          $('#cantidad_agregar').prop('disabled', false);
        }
      });

      // -------------------------------------------------------------
      // FILTRADO AVANZADO AJAX Y ETIQUETAS (TAGS) 
      // -------------------------------------------------------------
      const medicamentoSelectPrincipal = $('#select_medicamento');
      window.opcionesOriginalesMedicamentos = medicamentoSelectPrincipal.html();

      $('#btnLimpiarFiltros').on('click', function() {
        $('#formFiltroModal')[0].reset();
        medicamentoSelectPrincipal.html(window.opcionesOriginalesMedicamentos);
      });

      $('#btnAplicarFiltros').on('click', function(e) {
        e.preventDefault();

        let datosFiltro = $('#formFiltroModal').serialize();
        const formValores = $('#formFiltroModal').serializeArray();
        const busquedaRapida = $('#filtro_busqueda_rapida').val().trim();

        let hayDatos = false;

        $.each(formValores, function(i, field) {
          if (field.value.trim() !== "") {
            hayDatos = true;
          }
        });

        if (busquedaRapida !== "") {
          hayDatos = true;
          datosFiltro += (datosFiltro.length > 0 ? '&' : '') + 'filtro_busqueda_rapida=' + encodeURIComponent(busquedaRapida);
        }

        // En pedidos le pasamos un modo genérico para que el AJAX sepa que no necesita filtrar por lote disponible
        datosFiltro += (datosFiltro.length > 0 ? '&' : '') + 'modo=pedido';

        if (!hayDatos) {
          mostrarAviso("Todos los filtros están vacíos. Escriba o seleccione al menos una opción para buscar.");
          return;
        }

        $.ajax({
          url: '../../cfg/ajax/filtrar_medicamentos_completo.php',
          type: 'POST',
          data: datosFiltro,
          dataType: 'json',
          success: function(response) {
            medicamentoSelectPrincipal.empty();
            medicamentoSelectPrincipal.append('<option value="">--- Seleccione un Medicamento ---</option>');

            if (response.length > 0) {
              response.forEach(function(item) {
                medicamentoSelectPrincipal.append('<option value="' + item.id_desc + '" data-nombre="' + item.nombre_completo + '">' + item.nombre_completo + '</option>');
              });
            } else {
              medicamentoSelectPrincipal.append('<option value="" disabled>🛑 No se encontraron medicamentos.</option>');
            }
          },
          error: function(jqXHR, textStatus, errorThrown) {
            console.error("Error en AJAX de filtrado avanzado: ", textStatus, errorThrown);
          }
        });
      });

      // LÓGICA DE LAS ETIQUETAS (TAGS)
      function inicializarTags(selector) {
        const $inputOriginal = $(selector);
        $inputOriginal.hide();

        const $contenedor = $('<div class="tags-input-container"></div>');
        const $inputFalso = $('<input type="text" class="tags-input-fake" placeholder="' + ($inputOriginal.attr('placeholder') || 'Escriba y presione Enter...') + '">');

        $contenedor.append($inputFalso);
        $inputOriginal.after($contenedor);

        let tagsArray = [];

        function renderizarTags() {
          $contenedor.find('.tag-badge').remove();
          tagsArray.forEach((tag, index) => {
            const $tag = $(`<span class="tag-badge bg-primary">${tag} <i class="fa fa-times remove-tag" data-index="${index}"></i></span>`);
            $inputFalso.before($tag);
          });
          $inputOriginal.val(tagsArray.join(' '));
        }

        $inputFalso.on('keypress', function(e) {
          if (e.which === 13) {
            e.preventDefault();
            let valor = $(this).val().trim();

            if (valor !== '' && !tagsArray.includes(valor)) {
              tagsArray.push(valor);
              $(this).val('');
              renderizarTags();
              $('#btnAplicarFiltros').click();
            } else if (valor === '') {
              $('#btnAplicarFiltros').click();
            }
          }
        });

        $inputFalso.on('keydown', function(e) {
          if (e.which === 8 && $(this).val() === '') {
            e.preventDefault();
            if (tagsArray.length > 0) {
              let ultimaEtiqueta = tagsArray.pop();
              $(this).val(ultimaEtiqueta);
              renderizarTags();
              if (tagsArray.length === 0) {
                $('#btnLimpiarFiltros').click();
              } else {
                $('#btnAplicarFiltros').click();
              }
            }
          }
        });

        $contenedor.on('click', '.remove-tag', function() {
          let index = $(this).data('index');
          tagsArray.splice(index, 1);
          renderizarTags();
          if (tagsArray.length === 0) {
            $('#btnLimpiarFiltros').click();
          } else {
            $('#btnAplicarFiltros').click();
          }
        });

        $contenedor.on('click', function() {
          $inputFalso.focus();
        });

        $('#btnLimpiarFiltros').on('click', function() {
          tagsArray = [];
          renderizarTags();
        });
      }

      inicializarTags('#filtro_busqueda_rapida');
      inicializarTags('#filtro_principios');

      // ACTUALIZACIÓN SILENCIOSA DEL SELECT DE MEDICAMENTOS
      function actualizarSelectMedicamentosSilencio() {
        if ($('#modalAgregarMedicamento').is(':visible')) {
          return;
        }
        const hayFiltroBusqueda = $('#filtro_busqueda_rapida').val().trim() !== '';
        let hayFiltrosAvanzados = false;
        $.each($('#formFiltroModal').serializeArray(), function(i, field) {
          if (field.value.trim() !== "") hayFiltrosAvanzados = true;
        });

        if (!hayFiltroBusqueda && !hayFiltrosAvanzados) {
          const valorSeleccionado = $('#select_medicamento').val();

          $.ajax({
            url: '../../cfg/ajax/filtrar_medicamentos_completo.php',
            type: 'POST',
            data: {
              recarga_silenciosa: true,
              modo: 'pedido'
            },
            dataType: 'json',
            success: function(response) {
              const select = $('#select_medicamento');
              let nuevasOpciones = '<option value="">--- Seleccione un Medicamento ---</option>';

              if (response.length > 0) {
                response.forEach(function(item) {
                  const comp = item.componentes ? ` data-componentes="${item.componentes}"` : '';
                  nuevasOpciones += `<option value="${item.id_desc}" data-nombre="${item.nombre_completo}"${comp}>${item.nombre_completo}</option>`;
                });
              } else {
                nuevasOpciones += '<option value="" disabled>🛑 No se encontraron medicamentos.</option>';
              }

              select.html(nuevasOpciones);

              if (valorSeleccionado) {
                select.val(valorSeleccionado);
              }

              window.opcionesOriginalesMedicamentos = nuevasOpciones;
            }
          });
        }
      }

      setInterval(actualizarSelectMedicamentosSilencio, 2000);

      // Abrir modal de Agregar Medicamento
      $('#btnAbrirModalAgregar').on('click', function() {
        indexEdicion = -1; // Aseguramos el modo "Agregar"

        // Restaurar el aspecto del modal
        $('#modalAgregarMedicamento .modal-title').html('<i class="fa fa-plus-circle"></i> Agregar al pedido');
        $('#btnConfirmarAgregarLista').html('<i class="fa fa-check"></i> Añadir a la Lista');

        // Limpiar y habilitar campos
        $('#select_medicamento').val('').prop('disabled', false).removeClass('input-error');
        $('#cantidad_agregar').val('').removeClass('input-error');

        $('#modalAgregarMedicamento').modal('show');
      });

      // Agregar o Editar medicamento en la tabla
      $('#btnConfirmarAgregarLista').on('click', function() {
        let idMed = $('#select_medicamento').val();
        let nombreMed = $('#select_medicamento option:selected').data('nombre');
        let cantidad = parseInt($('#cantidad_agregar').val());
        let existencia = parseInt($('#existencia_actual').val()) || 0; // Capturamos existencia
        let sMax = parseInt($('#stock_maximo').val()) || 0; // Capturamos el tope

        $('#select_medicamento, #cantidad_agregar').removeClass('input-error');

        if (!idMed) {
          $('#select_medicamento').addClass('input-error');
          mostrarAviso("Debe seleccionar un medicamento del catálogo.");
          return;
        }
        if (!cantidad || cantidad <= 0 || isNaN(cantidad)) {
          $('#cantidad_agregar').addClass('input-error');
          mostrarAviso("Por favor, ingrese una cantidad válida mayor a cero.");
          return;
        }

        // VALIDACIÓN ESTRICTA DEL LÍMITE DE STOCK
        if (sMax > 0 && (existencia + cantidad) > sMax) {
          $('#cantidad_agregar').addClass('input-error');
          let permitido = sMax - existencia; // Calculamos cuánto falta para llegar al tope
          mostrarAviso(`⚠️ <b>Límite excedido:</b> Actualmente hay ${existencia} unidades y el Stock Máximo es ${sMax}.<br><br>Solo puedes pedir un máximo de <b>${permitido}</b> unidades extra para no sobrepasar el límite de tu almacén.`);
          return;
        }

        // Verificamos en qué modo estamos
        if (indexEdicion === -1) {
          let existe = listaItemsPedido.find(item => item.id_descripcion == idMed);
          if (existe) {
            existe.cantidad += cantidad;
          } else {
            listaItemsPedido.push({
              id_descripcion: idMed,
              nombre: nombreMed,
              cantidad: cantidad,
              existencia_actual: existencia, // Guardamos datos en el array
              stock_maximo: sMax // Guardamos datos en el array
            });
          }
        } else {
          listaItemsPedido[indexEdicion].cantidad = cantidad;
          indexEdicion = -1; 
        }

        actualizarTablaVisual();

        $('#modalAgregarMedicamento').removeClass('in').addClass('out');
        setTimeout(function() {
          $('#modalAgregarMedicamento').modal('hide');
          $('#modalAgregarMedicamento').removeClass('out');
        }, 400);
      });

      // Renderizar tabla
      function actualizarTablaVisual() {
        let tbody = $('#cuerpoTablaPedido');
        tbody.empty();

        if (listaItemsPedido.length === 0) {
          tbody.html('<tr id="filaVacia"><td colspan="3" class="text-center text-muted">Aún no se han añadido medicamentos al pedido actual.</td></tr>');
          return;
        }

        listaItemsPedido.forEach((item, index) => {
          let fila = `<tr>
            <td style="text-align: left;"><strong>${item.nombre}</strong></td>
            <td><span class="badge bg-green" style="font-size:14px;">${item.cantidad}</span></td>
            <td>
              <button type="button" class="btn btn-warning btn-xs btn-editar-fila" data-index="${index}" title="Editar medicamento">
                <i"><img src="../../recursos/imagenes/iconos/editar.png" style="width:10px; height:10px;"></i>
              </button>
              <button type="button" class="btn btn-danger btn-xs" onclick="eliminarItem(${index})" title="Quitar de la lista">
                <i><img src="../../recursos/imagenes/iconos/Delete.png" style="width:10px; height:10px;"></i>
              </button>
            </td>
          </tr>`;
          tbody.append(fila);
        });
      }

      window.eliminarItem = function(index) {
        listaItemsPedido.splice(index, 1);
        actualizarTablaVisual();
      };

      // Abrir modal en modo Editar Medicamento (Delegación de eventos por si la tabla se repinta)
      $(document).on('click', '.btn-editar-fila', function() {
        let index = $(this).data('index');
        let item = listaItemsPedido[index];

        indexEdicion = index; // Pasamos a modo "Editar" y guardamos la posición

        $('#existencia_actual').val(item.existencia_actual);
        $('#stock_maximo').val(item.stock_maximo);

        // Cambiar la estética del modal para indicar edición
        $('#modalAgregarMedicamento .modal-title').html('<i class="fa fa-pencil"></i> Editar cantidad solicitada');
        $('#btnConfirmarAgregarLista').html('<i class="fa fa-save"></i> Guardar Cambios');

        // Cargar los datos actuales y bloquear el selector de medicamento
        $('#select_medicamento').val(item.id_descripcion).prop('disabled', true).removeClass('input-error');
        $('#cantidad_agregar').val(item.cantidad).removeClass('input-error');

        $('#modalAgregarMedicamento').modal('show');
      });

      // Modal Regresar
      $('#abrirModalRegresar').on('click', function() {
        $('#modalRegresarInventario').modal('show');
      });

      // Validar y Confirmar Guardado
      $('#btnValidarGuardar').on('click', function() {
        $('#fecha_pedido').removeClass('input-error');
        $('#hora_pedido').removeClass('input-error');
        $('#proveedor').removeClass('input-error');
        $('#id_usuario').removeClass('input-error');
        $('#btnAbrirModalAgregar').removeClass('btn-error-sombreado');

        if ($('#fecha_pedido').val() === "") {
          $('#fecha_pedido').addClass('input-error');
          mostrarAviso("Debe asignar una fecha al pedido.");
          return;
        }

        if ($('#hora_pedido').val() === "") {
          $('#hora_pedido').addClass('input-error');
          mostrarAviso("Debe asignar una hora al pedido.");
          return;
        }

        if ($('#id_usuario').val() === "") {
          $('#id_usuario').addClass('input-error');
          mostrarAviso("Debe seleccionar el Usuario Solicitante.");
          return;
        }

        if ($('#proveedor').val() === "") {
          $('#proveedor').addClass('input-error');
          mostrarAviso("Debe seleccionar obligatoriamente un Proveedor Destino.");
          return;
        }

        if (listaItemsPedido.length === 0) {
          $('#btnAbrirModalAgregar').addClass('btn-error-sombreado');
          mostrarAviso("No puede generar un pedido vacío. Añada al menos un medicamento a la lista.");
          return;
        }

        $('#detalles_pedido_json').val(JSON.stringify(listaItemsPedido));
        $('#modalGuardar').removeClass('out').addClass('in').modal('show');
      });

      // Enviar Formulario
      $('#confirmarGuardar').on('click', function() {
        $('#modalGuardar').modal('hide');
        $('#formularioPedido')[0].submit();
      });

      // Fix cierre animado de modales Bootstrap
      $('.modal').on('click', '[data-dismiss="modal"]', function(e) {
        e.stopPropagation();
        var $modal = $(this).closest('.modal');
        if ($modal.hasClass('in')) {
          $modal.removeClass('in').addClass('out');
          setTimeout(function() {
            $modal.modal('hide');
            $modal.removeClass('out');
          }, 400);
        } else {
          $modal.modal('hide');
        }
      });

    });
  </script>
</body>

</html>