<?php
include("../../cfg/conexion.php");
$operacion_actual = isset($_GET['op']) ? $_GET['op'] : 'entrada';

include('includes/headerNav2.php');
verificarAcceso('Ajustar Stock de Medicamentos'); // Puedes cambiar el permiso a 'Gestionar Vacunas'
?>

<head>
  <title>Inventario | Ajuste de Stock de Vacunas</title>
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
      Ajuste de Stock de Vacunas
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="#"><i class="fa fa-medkit"></i>Inmunización</a></li>
      <li class="active"><a href="#"><i class="fa fa-cog"></i>Ajuste de Stock</a></li>
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
                    <label>Vacuna / Biológico (*):</label>
                    <div class="input-group">
                      <select id="Id_descripcion_medicamento" name="Id_descripcion_medicamento" class="form-control" required>
                        <option value="">--- Seleccione una Vacuna ---</option>
                        <!-- SIMULACIÓN: Aquí irían las vacunas de la BD -->
                        <option value="1" data-nombre="Antipoliomielítica (VPI)" data-presentacion="1 Dosis">Antipoliomielítica (VPI) - [1 Dosis]</option>
                        <option value="2" data-nombre="Antisarampión (SRP)" data-presentacion="10 Dosis">Antisarampión (SRP) - [10 Dosis]</option>
                        <option value="3" data-nombre="Vacuna BCG" data-presentacion="20 Dosis">Vacuna BCG - [20 Dosis]</option>
                      </select>

                      <span class="input-group-btn">
                        <button class="btn btn-info" type="button" id="btnBuscarFiltrar" data-toggle="modal" data-target="#modalBúsquedaAvanzadaMedicamento" title="Búsqueda Avanzada de Vacunas" style="height: 34px;">
                          <i><img src="../../recursos/imagenes/iconos/buscar.png" style="width:10px; height:10px;"></i>
                        </button>
                      </span>
                    </div>
                  </div>

                  <div class="col-sm-4">
                    <label>Stock mínimo de dosis (*):</label>
                    <input type="text" id="stock_minimo" name="stock_minimo" class="form-control" required>
                  </div>

                  <div class="col-sm-4">
                    <label>Stock máximo de dosis (*):</label>
                    <input type="text" id="stock_maximo" name="stock_maximo" class="form-control" required>
                  </div>

                  <div class="col-sm-1"></div>

                  <br><br><br><br>

                  <div class="col-sm-4">
                    <label>Existencia (Actual en dosis):</label>
                    <input type="text" id="existencia_actual" class="form-control" readonly disabled>
                  </div>

                  <div class="col-sm-1"></div>

                  <br><br><br><br>

                  <div class="col-sm-4"></div>

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

<!-- MODAL BÚSQUEDA AVANZADA -->
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
              <input type="text" id="filtro_busqueda_rapida" name="filtro_busqueda_rapida" class="form-control" placeholder="Escriba nombre de vacuna...">
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
                  <option value="">-- Todas --</option>
                  <option value="1">1 Dosis</option>
                  <option value="2">10 Dosis</option>
                  <option value="3">20 Dosis</option>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="filtro_principios">Componentes (contiene):</label>
                <input type="text" id="filtro_principios" name="filtro_principios" class="form-control" placeholder="Ej: Virus Inactivados...">
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

<!-- MODAL AVISO -->
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

<!-- MODAL GUARDAR -->
<div class="modal fade" id="modalAjusteGuardar" tabindex="-1" role="dialog" aria-labelledby="modalAjusteGuardarLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-green">
        <h5 class="modal-title" id="modalAjusteGuardarLabel">Confirmación de Guardado</h5>
      </div>
      <div class="modal-body">
        <p>¿Está seguro de que desea actualizar la información del stock de esta vacuna en el inventario?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" id="confirmarGuardadoFinal">Aceptar</button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL REGRESAR -->
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
        <a href="vacunacion_inventario_listado.php" class="btn btn-danger">Abandonar Formulario</a>
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
          'Tenga en cuenta que las entradas del sistema hacia esta vacuna quedarán bloqueadas temporalmente hasta que se regularice el inventario.';

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

    // SIMULACIÓN DE FILTROS AVANZADOS
    $('#btnAplicarFiltros').on('click', function() {
      const busquedaRapida = $('#filtro_busqueda_rapida').val().trim();
      const nombre_med = $('#filtro_nombre').val().trim();
      const principios = $('#filtro_principios').val().trim();

      if (busquedaRapida === "" && principios === "" && nombre_med === "") {
        mostrarAviso("Los campos de filtro están vacíos. Escriba alguna etiqueta para buscar.");
        return;
      }

      // SIMULACIÓN: En lugar de AJAX, filtramos las opciones existentes visualmente
      let opcionesFiltradas = '<option value="">--- Seleccione una Vacuna ---</option>';
      const $opciones = $(opcionesOriginalesMedicamentos).filter('option[value!=""]');
      
      let hayResultados = false;
      $opciones.each(function() {
        let texto = $(this).text().toLowerCase();
        let match = true;
        if (busquedaRapida && !texto.includes(busquedaRapida.toLowerCase())) match = false;
        if (nombre_med && !texto.includes(nombre_med.toLowerCase())) match = false;
        if (principios && !texto.includes(principios.toLowerCase())) match = false;

        if (match) {
          opcionesFiltradas += $(this)[0].outerHTML;
          hayResultados = true;
        }
      });

      if (hayResultados) {
        medicamentoSelectPrincipal.html(opcionesFiltradas);
        $('#modalBúsquedaAvanzadaMedicamento').modal('hide');
      } else {
        mostrarAviso("⚠️ No se encontraron vacunas que coincidan con los filtros aplicados.");
      }
    });

    // SIMULACIÓN DE CARGA DE DATOS AL SELECCIONAR VACUNA
    $('#Id_descripcion_medicamento').on('change', function() {
      var id_desc = $(this).val();
      var operacion = "<?php echo $_GET['op'] ?? 'entrada'; ?>";

      if (id_desc !== "") {
        // SIMULACIÓN: Aquí iría el AJAX real. Por ahora quemamos datos.
        let data = {};
        if (id_desc == '1') { // VPI
          data = { existencia_actual: 150, stock_minimo: 50, stock_maximo: 500 };
        } else if (id_desc == '2') { // SRP
          data = { existencia_actual: 80, stock_minimo: 20, stock_maximo: 300 };
        } else if (id_desc == '3') { // BCG
          data = { existencia_actual: 200, stock_minimo: 100, stock_maximo: 600 };
        }

        if (data.existencia_actual !== undefined) {
          $('#existencia_actual').val(data.existencia_actual);
          $('#stock_minimo').val(data.stock_minimo);
          $('#stock_maximo').val(data.stock_maximo);

          if (operacion === 'ajuste') {
            $('#stock_minimo, #stock_maximo').css('border', '2px solid green');
          }
        } else {
          $('#existencia_actual').val('');
          $('#stock_minimo').val('');
          $('#stock_maximo').val('');
        }
      }
    });

    // Lógica de Tags (Mantenida)
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

    // VALIDACIÓN DEL FORMULARIO
    $('#formularioAjuste').on('submit', function(e) {
      e.preventDefault();
      limpiarErrores();

      var formularioValido = true;
      var stock_minimo = Number($('#stock_minimo').val());
      var stockMin = $('#stock_minimo').val();
      var stock_maximo = Number($('#stock_maximo').val());

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
        mostrarAviso('⚠️ Error: El stock minimo de la vacuna no puede estar vacío.');
        return;
      }

      if ($('#stock_maximo').val().trim() === "") {
        $('#stock_maximo').addClass('input-error');
        mostrarAviso('⚠️ Error: El stock maximo de la vacuna no puede estar vacío.');
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
      // SIMULACIÓN: Aquí se enviaría el formulario real
      // $('#formularioAjuste').off('submit').submit();
      mostrarAviso("✅ <b>Ajuste de Stock Simulado.</b><br><br>En la versión real, esto actualizaría los valores en la base de datos.");
      setTimeout(function() {
        window.location.href = 'vacunacion_inventario_listado.php';
      }, 2500);
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