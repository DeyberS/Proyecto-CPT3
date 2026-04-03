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
                              tp.nombre_tipo,
                              GROUP_CONCAT(CONCAT(IFNULL(pa.nombre,''), ' ', IFNULL(dpm.cantidad_unidad_medida,''), IFNULL(um.unidad,'')) SEPARATOR ' + ') AS componentes 
                              FROM descripcion_medicamento dm
                              INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
                              INNER JOIN tipo_medicamento tp ON dm.Id_tipo = tp.Id_tipo
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
                              echo '<option value="' . $row_med['id_desc'] . '">' . htmlspecialchars($row_med['nombre_medicamento']) . " " . "(" . htmlspecialchars($row_med['componentes']) . ")" . " - " . "[" . htmlspecialchars($row_med['nombre_tipo']) . "]" . '</option>';
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
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="filtro_nombre">Nombre (o ID):</label>
                  <input type="text" id="filtro_nombre" name="filtro_nombre" class="form-control" placeholder="Escriba nombre o ID...">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="filtro_tipo">Tipo:</label>
                  <select id="filtro_tipo" name="filtro_tipo" class="form-control">
                    <option value="">-- Todos --</option>
                    <?php
                    // Cargar tipos de medicamento dinámicamente
                    include("../../cfg/conexion.php"); // Asegura la conexión
                    $sql_tipos = "SELECT Id_tipo, nombre_tipo FROM tipo_medicamento WHERE estatus = 1 ORDER BY nombre_tipo DESC";
                    $res_tipos = $conexion->query($sql_tipos);
                    while ($row_t = $res_tipos->fetch_assoc()) {
                      echo '<option value="' . $row_t['Id_tipo'] . '">' . $row_t['nombre_tipo'] . '</option>';
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
                  <label for="filtro_presentacion">Presentación:</label>
                  <input type="text" id="filtro_presentacion" name="filtro_presentacion" class="form-control" placeholder="Ej: 20 Capsulas">
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
                  <label for="filtro_composicion">Composición (contiene):</label>
                  <input type="text" id="filtro_composicion" name="filtro_composicion" class="form-control" placeholder="Escriba texto de composición...">
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
        clearTimeout($('#modalAjusteGuardar').data('timer'));
        $('#modalAjusteGuardar').modal('show');
      }
      
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

        /*if (stock_minimo > existencia ) {
          $('#stock_minimo').addClass('input-error');
          mostrarAviso('⚠️ Error: Disculpe el stock minimo no puede ser mayor que la existencia en este momento, por favor disminuya o aumente la existencia.');
          return;
        }*/

        if (!formularioValido) {
          mostrarAviso('⚠️ Error: Todos los campos obligatorios (*) deben estar llenos.');
          return;
        }

        // 1.3. Si todo es válido, abrimos el modal de confirmación
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