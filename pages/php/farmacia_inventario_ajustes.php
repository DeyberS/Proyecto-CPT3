<?php
include("../../cfg/conexion.php");
$operacion_actual = isset($_GET['op']) ? $_GET['op'] : 'entrada';

include('includes/headerNav2.php');
verificarAcceso('Ajustar Stock de Medicamentos');
?>

<head>
  <title>Inventario | Ajuste de Stock</title>
</head>

<style>
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
</style>

<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Ajuste de Stock
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="#"><i class="fa fa-users"></i>Inventario</a></li>
      <li class="active"><a href="#"><i class="fa fa-user-plus"></i>Ajuste de Stock</a></li>
    </ol>
  </section>

  <section class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="nav-tabs-custom">
          <ul class="nav nav-tabs">
            <li class="active"><a href="#tab_1" data-toggle="tab">Detalle de la operación</a></li>
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
                        $sql_medicamentos = "SELECT 
                              dm.Id AS id_desc, 
                              m.nombre_medicamento, 
                              p.nombre_presentacion,
                              GROUP_CONCAT(CONCAT(IFNULL(pa.nombre,''), ' ', IFNULL(dpm.cantidad_unidad_medida,''), IFNULL(um.unidad,'')) SEPARATOR ' + ') AS componentes 
                              FROM descripcion_medicamento dm
                              INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
                              INNER JOIN presentacion p ON dm.Id_presentacion = p.Id_presentacion
                              INNER JOIN detalle_principio_medicamento dpm ON dm.Id = dpm.id_descripcion_medicamento
                              INNER JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida
                              INNER JOIN principio_activo pa ON dpm.id_principio_activo = pa.Id_principio_activo
                              WHERE m.estatus = 1 AND dm.estatus = 1
                              GROUP BY dm.Id
                              ORDER BY m.nombre_medicamento ASC";
                        $resultado_medicamentos = $conexion->query($sql_medicamentos);

                        if ($resultado_medicamentos && $resultado_medicamentos->num_rows > 0) {
                          while ($row_med = $resultado_medicamentos->fetch_assoc()) {
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
                    <label>Stock máximo (*):</label>
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
                    <button type="button" class="btn btn-second" id="abrirModalRegresar">Regresar</button>
                    <button type="submit" class="btn btn-success" id="guardarAjuste">Actualizar</button>
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

<div class="modal fade" id="modalBúsquedaAvanzadaMedicamento" tabindex="-1" role="dialog" aria-labelledby="modalBúsquedaAvanzadaMedicamentoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" id="modalBúsquedaAvanzadaMedicamentoLabel">Filtros de Búsqueda Avanzada</h5>
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
                  include("../../cfg/conexion.php");
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
        <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="btnLimpiarFiltros">Limpiar Filtros</button>
        <button type="button" class="btn btn-success" id="btnAplicarFiltros">Aplicar Filtros</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="avisoModal" tabindex="-1" role="dialog" aria-labelledby="avisoModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-crimson">
        <h5 class="modal-title" id="avisoModalLabel">Aviso Importante</h5>
      </div>
      <div class="modal-body">
        <p id="avisoTexto"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalAjusteGuardar" tabindex="-1" role="dialog" aria-labelledby="modalAjusteGuardarLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-green">
        <h5 class="modal-title" id="modalAjusteGuardarLabel">Confirmación de Guardado</h5>
      </div>
      <div class="modal-body">
        <p>¿Está seguro de que desea actualizar la información del stock de este medicamento en el inventario?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" id="confirmarGuardadoFinal">Aceptar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalRegresarInventario" tabindex="-1" role="dialog" aria-labelledby="modalRegresarInventarioLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-crimson">
        <h5 class="modal-title" id="modalRegresarInventarioLabel">Confirmación de Regreso</h5>
      </div>
      <div class="modal-body">
        <p>Al hacer clic en "Abandonar Formulario", perderá todos los datos no guardados. ¿Desea continuar?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
        <a href="farmacia_inventario_listado.php" class="btn btn-danger">Abandonar Formulario</a>
      </div>
    </div>
  </div>
</div>

<?php
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

    function limpiarErrores() {
      $('input, select').removeClass('input-error');
    }

    function abrirModalGuardar() {
      var stock_maximo = Number($('#stock_maximo').val());
      var existencia = Number($('#existencia_actual').val());

      if (stock_maximo < existencia) {
        var mensaje = '⚠️ <strong>¡Atención!</strong> La existencia actual supera el stock máximo permitido. ' +
          'Tenga en cuenta que las entradas del sistema hacia este medicamento quedarán bloqueadas temporalmente hasta que se regularice el inventario.';

        var $btnCerrar = $('#avisoModal').find('.btn-second');

        $btnCerrar
          .text('Aceptar')
          .removeClass('btn-second')
          .addClass('btn-danger')
          .off('click')
          .one('click', function() {
            $(this).text('Cerrar').removeClass('btn-danger').addClass('btn-second');
            $('#avisoModal').modal('hide');
            mostrarModalGuardadoFinal();
          });

        mostrarAviso(mensaje);
        return;
      }
      mostrarModalGuardadoFinal();
    }

    function mostrarModalGuardadoFinal() {
      clearTimeout($('#modalAjusteGuardar').data('timer'));
      $('#modalAjusteGuardar').modal('show');
    }

    const medicamentoSelectPrincipal = $('#Id_descripcion_medicamento');
    const opcionesOriginalesMedicamentos = medicamentoSelectPrincipal.html();

    $('#btnLimpiarFiltros').on('click', function() {
      $('#formFiltroModal')[0].reset();
      medicamentoSelectPrincipal.html(opcionesOriginalesMedicamentos);
    });

    $('#btnAplicarFiltros').on('click', function() {
      const operacion = $('#op').val();
      const datosFiltro = $('#formFiltroModal').serialize() + '&modo=' + operacion;
      const busquedaRapida = $('#filtro_busqueda_rapida').val().trim();
      const principios = $('#filtro_principios').val().trim();
      const nombre_med = $('#filtro_nombre').val().trim();

      if (busquedaRapida === "" && principios === "" && nombre_med === "") {
        mostrarAviso("Los campos de filtro están vacíos. Escriba alguna etiqueta para buscar.");
        return;
      }

      $.ajax({
        url: '../../cfg/ajax/filtrar_medicamentos.php',
        type: 'POST',
        data: datosFiltro,
        dataType: 'json',
        success: function(response) {
          medicamentoSelectPrincipal.empty();
          medicamentoSelectPrincipal.append('<option value="">--- Seleccione un Medicamento ---</option>');

          if (response.length > 0) {
            response.forEach(function(item) {
              medicamentoSelectPrincipal.append('<option value="' + item.id_desc + '">' + item.nombre_completo + '</option>');
            });

            if (response.length === 1) {
              medicamentoSelectPrincipal.val(response[0].id_desc).trigger('change');
            }
          } else {
            medicamentoSelectPrincipal.append('<option value="" disabled>⚠️ No se encontraron medicamentos que coincidan con los filtros aplicados.</option>');
          }

        },
        error: function(jqXHR, textStatus, errorThrown) {
          console.error("⚠️ Error en AJAX de filtrado avanzado: ", textStatus, errorThrown);
        }
      });
    });

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
          }
        }
      });

      $contenedor.on('click', '.remove-tag', function() {
        let index = $(this).data('index');
        tagsArray.splice(index, 1);
        renderizarTags();
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

    $('#Id_descripcion_medicamento').on('change', function() {
      var id_desc = $(this).val();
      var operacion = "<?php echo $_GET['op'] ?? 'entrada'; ?>";

      if (id_desc !== "") {
        $.ajax({
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
              $('#existencia_actual').val(data.existencia_actual);
              $('#stock_minimo').val(data.stock_minimo);
              $('#stock_maximo').val(data.stock_maximo);

              if (operacion === 'ajuste') {
                $('#stock_minimo, #stock_maximo').css('border', '2px solid green');
              } else {}
            }
          },
          error: function() {
            console.error("⚠️ Error al conectar con obtener_descripcion_medicamento.php");
          }
        });
      }
    });

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
        const valorSeleccionado = $('#Id_descripcion_medicamento').val();
        let modoOperacion = $('#op').val();

        $.ajax({
          url: '../../cfg/ajax/filtrar_medicamentos.php',
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
              nuevasOpciones += '<option value="" disabled>⚠️ No se encontraron medicamentos.</option>';
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

    $('#formularioAjuste').on('submit', function(e) {
      e.preventDefault();
      limpiarErrores();

      var formularioValido = true;
      var stock_minimo = Number($('#stock_minimo').val());
      var stockMin = $('#stock_minimo').val();
      var stock_maximo = Number($('#stock_maximo').val());
      var existencia = Number($('#existencia_actual').val());

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

    $('#confirmarGuardadoFinal').on('click', function() {
      $('#modalAjusteGuardar').modal('hide');
      $('#formularioAjuste').off('submit').submit();
    });

    $('#abrirModalRegresar').on('click', function() {
      $('#modalRegresarInventario').modal('show');
    });
  });

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