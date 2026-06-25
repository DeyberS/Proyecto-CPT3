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
  <title>Inventario | Ajuste de Stock</title>
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

    /* --- ESTILOS NUEVOS PARA ERRORES Y ETIQUETAS (TAGS) --- */

    /* 1. Botón rojo sombreado para cuando falta el medicamento */
    .btn-error-sombreado {
      background-color: #f8d7da !important;
      color: #721c24 !important;
      border: 1px solid #f5c6cb !important;
      box-shadow: 0 0 10px rgba(220, 53, 69, 0.6) !important;
      transition: all 0.3s ease;
    }

    /* 2. Diseño del contenedor de Etiquetas (Tags) */
    .tags-input-container {
      display: flex;
      flex-wrap: wrap;
      gap: 5px;
      padding: 4px;
      border: 1px solid #d2d6de;
      background-color: #fff;
      min-height: 34px;
      cursor: text;
    }

    .tags-input-container .tag-badge {
      display: inline-flex;
      align-items: center;
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 12px;
      color: white;
    }

    .tags-input-container .tag-badge i {
      margin-left: 6px;
      cursor: pointer;
      font-size: 10px;
    }

    .tags-input-fake {
      border: none;
      outline: none;
      flex-grow: 1;
      min-width: 150px;
      padding: 2px;
    }

    /* Aplica la animación al modal que se está mostrando */
    .modal.in .modal-dialog,
    #avisoModal,
    #modalAjusteGuardar,
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
        Ajuste de Stock
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-users"></i>Inventario</a></li>
        <li class="active"><a href="#"><i class="fa fa-user-plus"></i>Ajuste</a></li>
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
                  <form id="formularioAjuste" style="margin-bottom:2%;" method="POST" action="../../cfg/movimientos_inventario.php" novalidate autocomplete="off">
                    <input type="hidden" name="op" id="op" value="<?php echo $operacion_actual; ?>">
                    <label class="control-label"></label>
                    <div class="col-sm-4" id="group_laboratorio">
                      <label>Medicamento (*):</label>
                      <div class="input-group">
                        <select id="Id_descripcion_medicamento" name="Id_descripcion_medicamento" class="form-control" required>
                          <option value="">--- Seleccione un Medicamento ---</option>
                          <?php
                          // 2. Cargar Medicamentos
                          $sql_medicamentos = "SELECT 
                              dm.Id AS id_desc, 
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
                              GROUP BY dm.Id
                              ORDER BY m.nombre_medicamento ASC";
                          $resultado_medicamentos = $conexion->query($sql_medicamentos);

                          if ($resultado_medicamentos && $resultado_medicamentos->num_rows > 0) {
                            while ($row_med = $resultado_medicamentos->fetch_assoc()) {
                              // Se usa Id_medicamento como value
                              echo '<option value="' . $row_med['id_desc'] . '">' . htmlspecialchars($row_med['nombre_medicamento']) . " " . "(" . htmlspecialchars($row_med['componentes']) . ")" . " - " . "[" . htmlspecialchars($row_med['nombre_presentacion']) . "]" . '</option>';
                            }
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
                      <label>Stock minimo (*):</label>
                      <input type="text" id="stock_minimo" name="stock_minimo" class="form-control" required>
                    </div>

                    <div class="col-sm-4">
                      <label>Stock maximo (*):</label>
                      <input type="text" id="stock_maximo" name="stock_maximo" class="form-control" required>
                    </div>

                    <div class="col-sm-1">
                    </div>

                    <br><br><br><br>

                    <div class="col-sm-4">
                      <label>Existencia (Actual):</label>
                      <input type="text" id="existencia_actual" class="form-control" readonly disabled>
                    </div>

                    <div class="col-sm-1">
                    </div>

                    <br><br><br><br>

                    <div class="col-sm-4">
                    </div>

                    <div style="float:right; margin-top: -1%;">
                      <button type="button" class="btn btn-secondary" id="abrirModalRegresar">Regresar</button>
                      <button type="submit" class="btn btn-success" id="guardarAjuste">Guardar</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
        <?php
        include('includes/footer.php');
        ?>
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
            <div class="col-md-12">
              <div class="form-group">
                <label for="filtro_busqueda_rapida">Busqueda Rapida:</label>
                <input type="text" id="filtro_busqueda_rapida" name="filtro_busqueda_rapida" class="form-control" placeholder="Escriba nombre, principio activo, presentacion, etc...">
              </div>
            </div>
            <br><br>
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

  <div class="modal" id="modalAjusteGuardar" tabindex="-1" role="dialog" aria-labelledby="modalAjusteGuardarLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: #00a65a; color: white;">
          <h5 class="modal-title" id="modalAjusteGuardarLabel" style="color: white;">Confirmacion de Guardado</h5>
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
        var stock_maximo = Number($('#stock_maximo').val());
        var existencia = Number($('#existencia_actual').val());

        if (stock_maximo < existencia) {
          // 1. Inventamos el texto de advertencia que me pediste
          var mensaje = '⚠️ <strong>¡Atención!</strong> La existencia actual supera el stock máximo permitido. ' +
            'Tenga en cuenta que las entradas del sistema hacia este medicamento quedarán bloqueadas temporalmente hasta que se regularice el inventario.';

          // 2. Personalizamos el botón de cerrar del modal #avisoModal
          var $btnCerrar = $('#avisoModal').find('.btn-secondary'); // Buscamos el botón dentro de tu modal

          $btnCerrar
            .text('Aceptar') // Cambiamos el texto a "Aceptar"
            .removeClass('btn-secondary') // Quitamos el color gris
            .addClass('btn-danger') // Le ponemos el color rojo (danger)
            .off('click') // Limpiamos eventos 'click' previos para que no se acumulen
            .one('click', function() { // .one se ejecuta una sola vez al hacer clic
              // 3. Al darle "Aceptar", restauramos el botón a su estado original por si se usa en otra parte
              $(this).text('Cerrar').removeClass('btn-danger').addClass('btn-secondary');

              // 4. Procedemos a mostrar el modal de guardado técnico
              $('#avisoModal').modal('hide'); // Opcional: por si Bootstrap no lo cierra automáticamente al cambiar clases
              mostrarModalGuardadoFinal();
            });

          // 5. Mostramos el aviso con el texto configurado
          mostrarAviso(mensaje);

          return; // Retornamos para detener la ejecución y que NO se abra el modal de guardado de inmediato
        }

        // Si NO se cumple la condición, el código sigue su flujo normal aquí abajo
        mostrarModalGuardadoFinal();
      }

      // Factorizamos esta parte en una pequeña función para poder reutilizarla
      function mostrarModalGuardadoFinal() {
        clearTimeout($('#modalAjusteGuardar').data('timer'));
        $('#modalAjusteGuardar').modal('show');
      }

      const medicamentoSelectPrincipal = $('#Id_descripcion_medicamento');
      const opcionesOriginalesMedicamentos = medicamentoSelectPrincipal.html(); // Guardamos las opciones iniciales cargadas por PHP

      $('#btnLimpiarFiltros').on('click', function() {
        $('#formFiltroModal')[0].reset();
        medicamentoSelectPrincipal.html(opcionesOriginalesMedicamentos); // Restauramos la lista completa
      });

      // 2. Lógica principal: Clic en "Aplicar Filtros" dentro del Modal
      $('#btnAplicarFiltros').on('click', function() {
        // Serializar los datos del formulario del modal
        // NUEVO CÓDIGO
        const operacion = $('#op').val(); // Obtenemos 'entrada', 'salida' o 'ajuste' del input oculto
        const datosFiltro = $('#formFiltroModal').serialize() + '&modo=' + operacion;
        const busquedaRapida = $('#filtro_busqueda_rapida').val().trim();
        const principios = $('#filtro_principios').val().trim();
        const nombre_med = $('#filtro_nombre').val().trim();

        // --- NUEVA VALIDACIÓN: Si ambos están vacíos, lanzamos alerta y nos detenemos ---
        if (busquedaRapida === "" && principios === "" && nombre_med === "") {
          mostrarAviso("Los campos de filtro están vacíos. Escriba alguna etiqueta para buscar.");
          return; // El return evita que se ejecute la petición AJAX
        }

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

          },
          error: function(jqXHR, textStatus, errorThrown) {
            console.error("Error en AJAX de filtrado avanzado: ", textStatus, errorThrown);
            // Mostrar aviso de error si tienes una función para ello
            // mostrarAviso('🛑 Error al intentar filtrar los medicamentos desde el modal.');
          }
        });
      });

      // -------------------------------------------------------------
      // SISTEMA DE ETIQUETAS (TAGS) PARA BÚSQUEDA RÁPIDA
      // -------------------------------------------------------------
      function inicializarTags(selector) {
        const $inputOriginal = $(selector);
        $inputOriginal.hide(); // Ocultamos el input original

        // Creamos la estructura visual falsa
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
          // Unimos las palabras con espacio para enviarlas al backend por POST normal
          $inputOriginal.val(tagsArray.join(' '));
        }

        $inputFalso.on('keypress', function(e) {
          if (e.which === 13) { // Tecla Enter
            e.preventDefault();
            let valor = $(this).val().trim();
            if (valor !== '' && !tagsArray.includes(valor)) {
              tagsArray.push(valor);
              $(this).val('');
              renderizarTags();
            }
          }
        });

        // Al hacer clic en la "X" eliminamos la etiqueta
        $contenedor.on('click', '.remove-tag', function() {
          let index = $(this).data('index');
          tagsArray.splice(index, 1);
          renderizarTags();
        });

        // Foco visual al hacer clic en el contenedor
        $contenedor.on('click', function() {
          $inputFalso.focus();
        });

        // Limpiar cuando se resetee el formulario
        $('#btnLimpiarFiltros').on('click', function() {
          tagsArray = [];
          renderizarTags();
        });
      }

      // Inicializamos la funcionalidad en los dos inputs requeridos
      inicializarTags('#filtro_busqueda_rapida');
      inicializarTags('#filtro_principios');

      // Escuchar el cambio en el select de descripción de medicamento
      $('#Id_descripcion_medicamento').on('change', function() {
        var id_desc = $(this).val();
        var operacion = "<?php echo $_GET['op'] ?? 'entrada'; ?>"; // Detectamos si es entrada, salida o ajuste

        if (id_desc !== "") {
          $.ajax({
            // Asegúrate de que esta ruta sea correcta según tu estructura de carpetas
            url: '../../cfg/ajax/obtener_descripcion_medicamento.php',
            type: 'POST',
            data: {
              id: id_desc
            },
            dataType: 'json',
            success: function(data) {
              if (data.error) {
                alert(data.error);
              } else {
                // 1. Cargamos la existencia actual (informativo)
                $('#existencia_actual').val(data.existencia_actual);

                // 2. Cargamos los parámetros de stock
                $('#stock_minimo').val(data.stock_minimo);
                $('#stock_maximo').val(data.stock_maximo);

                // 3. Lógica visual según la operación
                if (operacion === 'ajuste') {
                  // Si es ajuste, resaltamos los campos de stock para edición
                  $('#stock_minimo, #stock_maximo').css('border', '2px solid green'); // Ocultamos lote/vencimiento si solo es ajuste
                } else {
                  // Si es entrada/salida, comparamos para mostrar alertas

                }
              }
            },
            error: function() {
              console.error("Error al conectar con obtener_descripcion_medicamento.php");
            }
          });
        }
      });

      // -------------------------------------------------------------
      // ACTUALIZACIÓN SILENCIOSA DEL SELECT DE MEDICAMENTOS
      // -------------------------------------------------------------
      function actualizarSelectMedicamentosSilencio() {
        if ($('#modalAgregarMedicamento').is(':visible')) {
          return;
        }
        // 1. Verificamos si hay alguna búsqueda/filtro activo
        const hayFiltroBusqueda = $('#filtro_busqueda_rapida').val().trim() !== '';
        let hayFiltrosAvanzados = false;
        $.each($('#formFiltroModal').serializeArray(), function(i, field) {
          if (field.value.trim() !== "") hayFiltrosAvanzados = true;
        });

        // 2. Solo actualizamos si el usuario NO está usando los filtros
        if (!hayFiltroBusqueda && !hayFiltrosAvanzados) {
          // Guardamos el ID del medicamento que el usuario tenga seleccionado actualmente
          const valorSeleccionado = $('#Id_descripcion_medicamento').val();
          let modoOperacion = $('#op').val();

          $.ajax({
            url: '../../cfg/ajax/filtrar_medicamentos_completo.php', // Enviamos petición vacía para traer todos
            type: 'POST',
            data: {
              recarga_silenciosa: true,
              modo: modoOperacion
            },
            dataType: 'json',
            success: function(response) {
              const select = $('#Id_descripcion_medicamento');
              let nuevasOpciones = '<option value="">--- Seleccione un Medicamento ---</option>';

              if (response.length > 0) {
                response.forEach(function(item) {
                  const comp = item.componentes ? ` data-componentes="${item.componentes}"` : '';
                  nuevasOpciones += `<option value="${item.id_desc}" data-nombre="${item.nombre_completo}"${comp}>${item.nombre_completo}</option>`;
                });
              } else {
                nuevasOpciones += '<option value="" disabled>🛑 No se encontraron medicamentos.</option>';
              }

              // Actualizamos el DOM sin interrumpir al usuario
              select.html(nuevasOpciones);

              // Restauramos la selección que tenía
              if (valorSeleccionado) {
                select.val(valorSeleccionado);
              }

              // Actualizamos el respaldo original para cuando limpien los filtros
              window.opcionesOriginalesMedicamentos = nuevasOpciones;
            }
          });
        }
      }

      // Ejecutar la actualización silenciosa cada 2000 milisegundos (2 segundos)
      setInterval(actualizarSelectMedicamentosSilencio, 2000);

      $('#formularioAjuste').on('submit', function(e) {
        e.preventDefault();
        limpiarErrores();

        var formularioValido = true;

        var stock_minimo = Number($('#stock_minimo').val());
        var stockMin = $('#stock_minimo').val();
        var stock_maximo = Number($('#stock_maximo').val());
        var existencia = Number($('#existencia_actual').val());

        // 1.1. Verificación de campos obligatorios vacíos
        $('input[required], select[required]').each(function() {
          var $input = $(this);

          if (($input.is('select') && ($input.val() === null || $input.val() === "")) ||
            (!$input.is('select') && $input.val().trim() === "")) {
            $input.addClass('input-error');
            formularioValido = false;
          }
        });

        if (stockMin.trim() === "") {
          $('#stock_minimo').addClass('input-error');
          mostrarAviso('⚠️ Error: El stock minimo del medicamento no puede estar vacío.');
          return;
        }

        if (stockMin.trim() === "") {
          $('#stock_maximo').addClass('input-error');
          mostrarAviso('⚠️ Error: El stock maximo del medicamento no puede estar vacío.');
          return;
        }

        if (stock_minimo >= stock_maximo) {
          $('#stock_minimo').addClass('input-error');
          mostrarAviso('⚠️ Error: Disculpe el stock minimo no puede ser mayor o igual que el stock maximo.');
          return;
        }

        if (stock_minimo <= 0) {
          $('#stock_minimo').addClass('input-error');
          mostrarAviso('El stock minimo debe ser mayor a 0.');
          return;
        }

        if (!formularioValido) {
          mostrarAviso('⚠️ Error: Todos los campos obligatorios (*) deben estar llenos.');
          return;
        }

        abrirModalGuardar();
      });

      // 1.4. Lógica para el botón 'Guardar' dentro del modal de confirmación
      $('#confirmarGuardadoFinal').on('click', function() {
        $('#modalAjusteGuardar').modal('hide');
        $('#formularioAjuste').off('submit').submit();
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
    });
  </script>
  <script>
    function soloNumerosSinE(campo, maxDigitos) {
      campo.addEventListener("keydown", function(e) {
        const teclasPermitidas = ["Backspace", "Tab", "ArrowLeft", "ArrowRight", "Delete"];

        if (teclasPermitidas.includes(e.key)) return;
        if (e.key.toLowerCase() === "e") {
          e.preventDefault();
          return;
        }
        if (!/^[0-9]$/.test(e.key)) {
          e.preventDefault();
          return;
        }
        if (campo.value.length >= maxDigitos) {
          e.preventDefault();
        }
      });

      campo.addEventListener("input", function() {
        campo.value = campo.value.replace(/[^0-9]/g, "").slice(0, maxDigitos);
      });
    }

    soloNumerosSinE(document.getElementById("stock_minimo"), 8);
    soloNumerosSinE(document.getElementById("stock_maximo"), 7);
  </script>

</body>

</html>