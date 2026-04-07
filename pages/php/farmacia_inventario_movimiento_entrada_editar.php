<?php
// Incluir la conexión a la base de datos (se asume esta ruta)
include("../../cfg/conexion.php");
$operacion_actual = isset($_GET['op']) ? $_GET['op'] : 'entrada';
?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Inventario | Editar Entrada</title>
  <?php
  include('includes/headerNav2.php');
  ?>

  <style>
    /* ---------------------------------------------------------------------- */
    /* ANIMACIONES Y ESTILOS DE MODALES (Solicitados) */
    /* ---------------------------------------------------------------------- */

    /* Animación para el fondo al abrir el modal */
    @keyframes pulse-opacity {
      0% {
        opacity: 0;
      }

      100% {
        opacity: 1;
      }
    }

    /* Animación para el modal de Bootstrap (reemplaza la clase 'fade') */
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

    /* Aplica la animación al modal que se está mostrando */
    .modal.in .modal-dialog,
    #avisoModal,
    #modalEntradaGuardar,
    #modalBúsquedaAvanzadaMedicamento,
    #modalRegresarInventario {
      animation: fadeIn 0.4s ease-out;
    }

    /* Aplica la animación de salida cuando el modal tiene la clase de cierre */
    .modal.out .modal-dialog {
      animation: fadeOut 0.4s ease-in;
    }

    /* Estilo para el body cuando un modal está abierto (fondo animado) */
    .modal-open .modal-backdrop {
      opacity: 0.7 !important;
      animation: pulse-opacity 0.3s forwards;
      /* Aplica la animación al backdrop */
    }

    /* ---------------------------------------------------------------------- */
    /* ESTILOS DE VALIDACIÓN Y LAYOUT */
    /* ---------------------------------------------------------------------- */
    .input-error {
      border: 2px solid crimson !important;
      box-shadow: 0 0 5px crimson;
    }

    .modal {
      position: fixed !important;
      z-index: 99999 !important;
    }

    .modal-backdrop {
      z-index: 99998 !important;
      transition: .5s;
    }

    .modal.in {
      display: block;
      /* Asegura que se muestre para la animación */
    }
  </style>
</head>

<body>
  <div class="content-wrapper">
    <section class="content-header">
      <h1>
        Editar Entrada de Medicamentos
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-users"></i>Inventario</a></li>
        <li class="active"><a href="#"><i class="fa fa-user-plus"></i>Entrada</a></li>
      </ol>
    </section>

    <section class="content">

      <div class="row">
        <div class="col-md-12">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#tab_1" data-toggle="tab">Detalle de La Operacion</a></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane active" id="tab_1">
                <div class="box-body">
                  <form id="formularioEntrada" style="margin-bottom: 11%;" method="POST" action="../../cfg/editar/editar_movimiento.php" novalidate autocomplete="off">
                    <?php
                    include("../../cfg/conexion.php");
                    $inventario_id = $_GET['id'] ?? 0;

                    // Consulta optimizada para traer datos del medicamento y el lote
                    $sql = "SELECT
                        di.Id_detalle_inventario,
                        di.fecha AS fecha_operacion,
                        m.Id_medicamento,
                        dm.Id AS id_desc,
                        m.nombre_medicamento,
                        l.Id_proveedor,
                        l.Lote,
                        l.fecha_fabricacion,
                        l.fecha_vencimiento,
                        mdi.cantidad,
                        ex.cantidad_actual AS existencia,
                        dm.stock_minimo,
                        dm.stock_maximo,
                        di.observaciones
                      FROM detalle_inventario di
                      JOIN medicamentos_detalle_inventario mdi ON di.Id_detalle_inventario = mdi.Id_detalle_inventario
                      JOIN lotes_medicamentos l ON mdi.Id_lote = l.Id
                      JOIN existencias_stock ex ON l.Id = ex.Id_lote
                      JOIN descripcion_medicamento dm ON l.Id_descripcion_medicamento = dm.Id
                      JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
                      WHERE di.Id_detalle_inventario = $inventario_id";

                    $resultado = $conexion->query($sql);
                    $row = $resultado->fetch_assoc();
                    ?>

                    <input type="hidden" name="Id" value="<?= $inventario_id; ?>">
                    <input type="hidden" name="op" id="op" value="editar_entrada">

                    <label class="control-label"></label>
                    <div class="col-sm-4">
                      <label>Medicamento (*): </label>
                      <div class="input-group">
                        <select id="Id_descripcion_medicamento" name="Id_descripcion_medicamento" class="form-control" required>
                          <option value="">--- Seleccione un Medicamento ---</option>
                          <?php
                          $sql_meds = "SELECT dm.Id AS id_desc, 
                          m.nombre_medicamento,
                          p.nombre_presentacion,
                          GROUP_CONCAT(CONCAT(IFNULL(pa.nombre,''), ' ', IFNULL(dpm.cantidad_unidad_medida,''), IFNULL(um.unidad,'')) SEPARATOR ' + ') AS componentes 
                          FROM descripcion_medicamento dm 
                          INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento 
                          INNER JOIN presentacion p ON dm.Id_presentacion = p.Id_presentacion
                          INNER JOIN detalle_principio_medicamento dpm ON dm.Id = dpm.id_medicamento
                          INNER JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida
                          INNER JOIN principio_activo pa ON dpm.id_principio_activo = pa.Id_principio_activo
                          WHERE m.estatus = 1 AND dm.estatus = 1
                          GROUP BY dm.Id";
                          $res_meds = $conexion->query($sql_meds);
                          while ($m = $res_meds->fetch_assoc()) {
                            $selected = ($m['id_desc'] == $row['id_desc']) ? 'selected' : '';
                            echo '<option value="' . $m['id_desc'] . '" ' . $selected . '>'
                              . htmlspecialchars($m['nombre_medicamento']) . ' (' . htmlspecialchars($m['componentes']) . ') - ' . '[' . htmlspecialchars($m['nombre_presentacion']) . ']' . '</option>';
                          }
                          ?>
                        </select>

                        <span class="input-group-btn">
                          <button class="btn btn-info" type="button" id="btnBuscarFiltrar" data-toggle="modal" data-target="#modalBúsquedaAvanzadaMedicamento" title="Búsqueda Avanzada de Medicamentos" style="height: 34px;">
                            <i><img src="../../recursos/imagenes/iconos/buscar.png" style="width:10px; height:10px;"></i>
                          </button>
                        </span>
                      </div>
                    </div>

                    <div class="col-sm-4">
                      <label>Stock minimo:</label>
                      <input type="text" id="stock_minimo" name="stock_minimo" class="form-control" value="<?php echo $row['stock_minimo'] ?>" readonly readonly>
                    </div>

                    <div class="col-sm-4">
                      <label>Stock maximo:</label>
                      <input type="text" id="stock_maximo" name="stock_maximo" class="form-control" value="<?php echo $row['stock_maximo'] ?>" readonly readonly>
                    </div>

                    <div class="col-sm-1">
                    </div>

                    <br><br><br><br>

                    <div class="col-sm-4 form-group" id="group_proveedor">
                      <label>Proveedor (*):</label>
                      <select id="proveedor" name="proveedor" class="form-control" required>
                        <option value="">--- Seleccione un proveedor ---</option>
                        <?php
                        include("../../cfg/conexion.php");
                        $sql_proveedor = "SELECT Id_proveedor, nombre_proveedor FROM proveedor ORDER BY nombre_proveedor ASC";
                        $resultado_proveedor = $conexion->query($sql_proveedor);

                        if ($resultado_proveedor && $resultado_proveedor->num_rows > 0) {
                          while ($row_pro = $resultado_proveedor->fetch_assoc()) {
                            // Ahora $row['Id_proveedor'] sí tendrá el valor de la base de datos
                            $selected = ($row_pro['Id_proveedor'] == $row['Id_proveedor']) ? 'selected' : '';

                            echo '<option value="' . $row_pro['Id_proveedor'] . '" ' . $selected . '>'
                              . htmlspecialchars($row_pro['nombre_proveedor']) .
                              '</option>';
                          }
                        }
                        ?>
                      </select>
                    </div>

                    <div class="col-sm-4">
                      <label>Lote (*):</label>
                      <input type="text" name="lote" id="lote" class="form-control" list="lista_lotes" placeholder="Escriba o seleccione..." value="<?= $row['Lote'] ?>" readonly required>
                      <datalist id="lista_lotes">
                      </datalist>
                    </div>

                    <div class="col-sm-4">
                      <label>F. Fabricacion (*):</label>
                      <input type="date" id="fecha_fabricacion" name="fecha_fabricacion" class="form-control" value="<?= $row['fecha_fabricacion'] ?>" required readonly>
                    </div>

                    <div class="col-sm-1">
                    </div>

                    <br><br><br><br>

                    <div class="col-sm-4">
                      <label>F. Vencimiento (*):</label>
                      <input type="date" id="fecha_vencimiento" name="fecha_vencimiento" class="form-control" value="<?= $row['fecha_vencimiento'] ?>" required readonly>
                    </div>

                    <div class="col-sm-4">
                      <label>Existencia (Actual):</label>
                      <input type="text" id="existencia_actual" class="form-control" value="<?php echo $row['existencia'] ?>" readonly readonly>
                    </div>

                    <div class="col-sm-4">
                      <label>Unidades a ingresar (*):</label>
                      <input type="text" id="cantidad" name="cantidad" class="form-control" inputmode="numeric" value="<?= $row['cantidad'] ?>" required>
                    </div>

                    <div class="col-sm-1">
                    </div>

                    <br><br><br><br>

                    <div class="col-sm-4">
                      <label>Observaciones:</label>
                      <input type="text" id="observaciones" name="observaciones" class="form-control" value="<?= $row['observaciones'] ?>" maxlength="255">
                    </div>

                    <div class="col-sm-4">
                    </div>

                    <div style="float:right; margin-top: 2%;">
                      <button type="button" class="btn btn-secondary" id="abrirModalRegresar">Regresar</button>
                      <button type="submit" class="btn btn-success" id="guardarEntrada">Guardar</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <div class="modal" id="modalBúsquedaAvanzadaMedicamento" tabindex="-1" role="dialog" aria-labelledby="modalBúsquedaAvanzadaMedicamentoLabel" aria-hidden="true">
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

  <div class="modal" id="avisoModal" tabindex="-1" role="dialog" aria-labelledby="avisoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h5 class="modal-title" id="avisoModalLabel" style="color: white;">Aviso de Validación</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p id="avisoTexto"></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalEntradaGuardar" tabindex="-1" role="dialog" aria-labelledby="modalEntradaGuardarLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-green">
          <h5 class="modal-title" id="modalEntradaGuardarLabel" style="color: white;">Confirmacion de Guardado</h5>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea guardar la información para esta entrada del inventario?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-success" id="confirmarGuardadoFinal">Guardar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalRegresarInventario" tabindex="-1" role="dialog" aria-labelledby="modalRegresarInventarioLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h5 class="modal-title" id="modalRegresarInventarioLabel" style="color: white;">Confirmacion de Regreso</h5>
        </div>
        <div class="modal-body">
          <p>Al hacer clic en "Abandonar Formulario", perderá todos los datos no guardados. ¿Desea continuar?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <a href="farmacia_inventario_listado.php" class="btn btn-danger">Abandonar Formulario</a>
        </div>
      </div>
    </div>
  </div>

  <?php
  // Cierre de la conexión a la base de datos
  if (isset($conexion)) {
    $conexion->close();
  }
  include('includes/footer.php');
  ?>

  <script>
    $(document).ready(function() {
      let lotesCargados = []; // Variable para lo id="confirmarGuardadoFinal"s lotes del medicamento seleccionado
      // 1. Referencias a elementos del DOM (Mezclando jQuery y Vanila para compatibilidad con tu código)
      const formulario = document.getElementById('formularioEntrada');
      const fechaFabricacionInput = $('#fecha_fabricacion');
      const fechaVencimientoInput = $('#fecha_vencimiento');
      const cantidadInput = $('#cantidad');
      const medicamentoSelect = $('#Id_descripcion_medicamento');
      const stockMinimoInput = $('#stock_minimo');
      const stockMaximoInput = $('#stock_maximo');
      const listaLotes = $('#lista_lotes');

      function mostrarAviso(mensaje) {
        clearTimeout($('#avisoModal').data('timer'));
        $('#avisoTexto').html(mensaje);
        $('#avisoModal').modal('show');
      }

      // Limpia el estado de error de todos los inputs
      function limpiarErrores() {
        $('input, select').removeClass('input-error');
      }

      // Función para abrir el modal de Guardar
      function abrirModalGuardar() {
        clearTimeout($('#modalEntradaGuardar').data('timer'));
        $('#modalEntradaGuardar').modal('show');
      }

      // --- CONFIGURACIÓN INICIAL ---

      // --- FUNCIÓN DE VALIDACIÓN GENERAL ---
      function checkFormValidity() {
        let allRequiredFieldsFilled = true;
        const requiredFields = formulario.querySelectorAll('[required]');

        requiredFields.forEach(field => {
          if (field.readonly && field.id === 'fecha_vencimiento') {
            // No bloquea si está deshabilitado
          } else if (!field.value.trim()) {
            allRequiredFieldsFilled = false;
          }
        });
      }

      // --- VALIDACIÓN DE FECHAS (No futuras para fabricación) ---
      const today = new Date().toISOString().split('T')[0];
      fechaFabricacionInput.attr('max', today);

      // --- EVENTOS DE VALIDACIÓN ---
      $(formulario).on('input change', checkFormValidity);

      fechaFabricacionInput.on('change', function() {
        const fabricacionDate = $(this).val();
        if (fabricacionDate) {
          fechaVencimientoInput.prop('readonly', false).attr('min', fabricacionDate);
        } else {
          fechaVencimientoInput.prop('readonly', true).val('').removeAttr('min');
        }
        checkFormValidity();
      });

      fechaVencimientoInput.on('change', function() {
        const fabricacionDate = fechaFabricacionInput.val();
        const selectedDate = $(this).val();

        if (fabricacionDate && selectedDate < fabricacionDate) {
          mostrarAviso('La fecha de vencimiento no puede ser anterior a la de fabricación.');
          $(this).val('');
        } else if (selectedDate && selectedDate <= today) {
          mostrarAviso('La fecha de vencimiento debe ser una fecha futura.');
          $(this).val('');
        }
        checkFormValidity();
      });

      cantidadInput.on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value && parseInt(this.value) <= 0) this.value = 1;
        checkFormValidity();
      });

      const medicamentoSelectPrincipal = $('#Id_descripcion_medicamento');

      // 1. Lógica para botón Limpiar Filtros dentro del Modal
      $('#btnLimpiarFiltros').on('click', function() {
        // Resetea el formulario del modal (formFiltroModal)
        $('#formFiltroModal')[0].reset();
        // Opcionalmente, podrías ejecutar el filtrado vacío para cargar todos
      });

      // 2. Lógica principal: Clic en "Aplicar Filtros" dentro del Modal
      $('#btnAplicarFiltros').on('click', function() {
        // Serializar los datos del formulario del modal
        const datosFiltro = $('#formFiltroModal').serialize();

        // Mostrar un indicador de carga en el botón si es necesario

        // Realizar la llamada AJAX al nuevo backend
        $.ajax({
          url: '../../cfg/ajax/filtrar_medicamentos_completo.php', // El nuevo backend
          type: 'POST',
          data: datosFiltro, // Envía los valores de los filtros del modal
          dataType: 'json',
          success: function(response) {
            // 1. Limpiar el select principal
            medicamentoSelectPrincipal.empty();

            // 2. Añadir la opción inicial por defecto
            medicamentoSelectPrincipal.append('<option value="">--- Seleccione un Medicamento ---</option>');

            // 3. Repoblar el select con los nuevos resultados filtrados
            if (response.length > 0) {
              // Iterar sobre el array de medicamentos devuelto por PHP
              response.forEach(function(item) {
                // Crear el nuevo <option value="id_desc">nombre completo descriptivo</option>
                medicamentoSelectPrincipal.append('<option value="' + item.id_desc + '">' + item.nombre_completo + '</option>');
              });

              // Opcionalmente: Si hay un solo resultado, seleccionarlo automáticamente
              /* if (response.length === 1) {
                  medicamentoSelectPrincipal.val(response[0].id_desc).trigger('change');
              } */
            } else {
              // Si no hay resultados
              medicamentoSelectPrincipal.append('<option value="" disabled>🛑 No se encontraron medicamentos que coincidan con los filtros aplicados.</option>');
            }

            // 4. Cerrar el modal animadamente usando tu lógica existente
            $('#modalBúsquedaAvanzadaMedicamento').removeClass('in').addClass('out');
            setTimeout(function() {
              $('#modalBúsquedaAvanzadaMedicamento').modal('hide');
              $('#modalBúsquedaAvanzadaMedicamento').removeClass('out');
            }, 400);

            // 5. Opcional: Mostrar un aviso si hay resultados
            /* if (response.length > 0) {
               // alert('Se encontraron ' + response.length + ' medicamentos. Busque en la lista principal.');
            } */
          },
          error: function(jqXHR, textStatus, errorThrown) {
            console.error("Error en AJAX de filtrado avanzado: ", textStatus, errorThrown);
            // Mostrar aviso de error si tienes una función para ello
            // mostrarAviso('🛑 Error al intentar filtrar los medicamentos desde el modal.');
          }
        });
      });

      // --- LLAMADA AJAX AL SELECCIONAR MEDICAMENTO ---

      $('#Id_descripcion_medicamento').on('change', function() {
        const medicamentoId = $(this).val();
        $('#lista_lotes').empty();
        lotesCargados = []; // Limpiamos los lotes previos

        if (medicamentoId) {
          $.ajax({
            url: '../../cfg/ajax/obtener_descripcion_medicamento.php',
            type: 'POST',
            data: {
              id: medicamentoId,
              modo: 'entrada'
            },
            dataType: 'json',
            success: function(data) {
              if (!data.error) {
                // Llenamos stocks y existencia
                $('#existencia_actual').val(data.existencia_actual);
                $('#stock_minimo').val(data.stock_minimo);
                $('#stock_maximo').val(data.stock_maximo);

                // Guardamos y llenamos el datalist
                if (data.lotes) {
                  lotesCargados = data.lotes;
                  data.lotes.forEach(function(item) {
                    $('#lista_lotes').append('<option value="' + item.lote + '">');
                  });
                }
              }
            }
          });
        }
      });

      // --- AL ESCRIBIR O SELECCIONAR UN LOTE ---
      $('#lote').on('input change', function() {
        const loteEscrito = $(this).val().trim();

        if (loteEscrito === "") {
          $('#fecha_fabricacion, #fecha_vencimiento').val('').prop('readonly', false);
          return;
        }

        const loteEncontrado = lotesCargados.find(l => l.lote.toString().toLowerCase() === loteEscrito.toLowerCase());

        if (loteEncontrado) {

          // Llenamos los campos de fecha
          $('#fecha_fabricacion').val(loteEncontrado.fecha_fabricacion);
          $('#fecha_vencimiento').prop('readonly', false).val(loteEncontrado.fecha_vencimiento);

          // Bloqueamos para evitar que modifiquen datos de un lote que ya existe en BD
          $('#lote, #fecha_fabricacion, #fecha_vencimiento').prop('readonly', true);

          // Agregamos una clase visual para saber que es un lote existente
          $(this).css('border-color', '#28a745');
        } else {
          console.log("Lote nuevo o no terminado de escribir...");

          // Si no existe, permitimos edición manual
          $('#fecha_fabricacion, #fecha_vencimiento').prop('readonly', false);
          $(this).css('border-color', '#ced4da');
        }
        checkFormValidity();
      });

      $('#formularioEntrada').on('submit', function(e) {
        e.preventDefault();
        limpiarErrores();

        const cantidad = parseFloat($('#cantidad').val());
        const stockMin = $('#stock_minimo').val();
        const stockMax = $('#stock_maximo').val();
        var nombreLote = $('#lote').val();
        var formularioValido = true;

        // 1.1. Verificación de campos obligatorios vacíos
        $('input[required], select[required]').each(function() {
          var $input = $(this);

          if (($input.is('select') && ($input.val() === null || $input.val() === "")) ||
            (!$input.is('select') && $input.val().trim() === "")) {
            $input.addClass('input-error');
            formularioValido = false;
          }
        });

        if (nombreLote.trim() === "") {
          $('#lote').addClass('input-error');
          mostrarAviso('🛑 Error: El lote del medicamento no puede estar vacío.');
          return;
        }

        if (isNaN(cantidad) || cantidad <= 0) {
          mostrarAviso("🛑 Error: Ingrese una cantidad válida mayor a 0.");
          return;
        }

        // Validación de Stock Máximo (Importante en Entradas)
        if (!isNaN(stockMax) && cantidad > stockMax) {
          mostrarAviso("🛑 Error: La cantidad ingresada (" + cantidad + ") supera el stock máximo permitido (" + stockMax + ").");
          return;
        }

        // Validación de Stock Mínimo (Opcional en entradas, pero útil para sugerencias)
        if (!isNaN(stockMin) && cantidad < stockMin) {
          // Podrías dejarlo como advertencia o error según tu regla de negocio
          mostrarAviso("🛑 Error: La cantidad es menor al stock mínimo.");
          return;
        }

        if (!formularioValido) {
          mostrarAviso('⚠️ Error: Todos los campos obligatorios (*) deben estar llenos.');
          return;
        }
        // 1.3. Si todo es válido, abrimos el modal de confirmación
        abrirModalGuardar();
      });

      // 1.4. Lógica para el botón 'Guardar' dentro del modal de confirmación
      $('#confirmarGuardadoFinal').on('click', function() {
        $('#modalEntradaGuardar').modal('hide');
        $('#formularioEntrada').off('submit').submit();
      });

      // 1.5. Lógica para el botón Regresar (Abre el modal)
      $('#abrirModalRegresar').on('click', function() {
        $('#modalRegresarInventario').modal('show');
      });

      // =====================================================================
      // FIX CLAVE: CERRAR MODALES CON data-dismiss (Para la animación de salida)
      // =====================================================================
      $('.modal').on('click', '[data-dismiss="modal"]', function() {
        var $modal = $(this).closest('.modal');
        $modal.removeClass('in').addClass('out');

        setTimeout(function() {
          $modal.modal('hide');
          $modal.removeClass('out');
        }, 400);
      });

      // =====================================================================
      // LIMPIEZA ADICIONAL PARA MODALES 
      // =====================================================================
      $('.modal').on('hidden.bs.modal', function() {
        if ($('.modal:visible').length) {
          $('body').addClass('modal-open');
        } else {
          $('body').removeClass('modal-open');
        }
        $('.modal-backdrop').remove();
      });

      if ($('#Id_descripcion_medicamento').val()) {
        $('#Id_descripcion_medicamento').trigger('change');
      }

      // Al final de tu script, justo antes de cerrar });
      if ($('#op').val() === 'editar_entrada') {
        // Bloqueo estricto para edición
        $('#Id_descripcion_medicamento').prop('disabled', true); // Los select usan disabled
        $('#lote, #fecha_fabricacion, #fecha_vencimiento').prop('readonly', true);

        // Si deshabilitas el select, añade un campo oculto porque los 'disabled' no se envían por POST
        if ($('#Id_descripcion_medicamento').prop('disabled')) {
          $('<input>').attr({
            type: 'hidden',
            name: 'Id_descripcion_medicamento',
            value: $('#Id_descripcion_medicamento').val()
          }).appendTo('#formularioEntrada');
        }
      }

      if ($('#lote').val() !== "") {
        $('#lote').prop('readonly', true);
      }
    });
  </script>
</body>

</html>