<?php
include('includes/headerNav2.php');
verificarAcceso('Crear Pedidos');
include("../../cfg/conexion.php");
?>

<head>
  <title>Pedidos de Vacunas | Añadir</title>
</head>

<style>
  .tags-input-container {
    display: flex; flex-wrap: wrap; align-items: center; gap: 5px; width: 100%; min-height: 34px;
    padding: 4px 12px; font-size: 14px; line-height: 1.42857143; color: #555; background-color: #fff;
    border: 1px solid #ccc; border-radius: 4px; box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075);
    transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s; cursor: text;
  }
  .tags-input-container:focus-within { border-color: #66afe9; outline: 0; box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(102,175,233,.6); }
  .tags-input-fake { border: none; outline: none; background: transparent; flex-grow: 1; min-width: 150px; height: 24px; box-shadow: none !important; }
  .tag-badge { display: inline-flex; align-items: center; padding: 4px 8px; border-radius: 3px; font-size: 12px; margin-bottom: 2px; margin-top: 2px; }
  .tag-badge .remove-tag { margin-left: 6px; cursor: pointer; font-weight: bold; }
  .table-detalle th { background-color: #f4f4f4; text-align: center; }
  .table-detalle td { text-align: center; vertical-align: middle !important; }
  .area-trabajo-blanca { background-color: #ffffff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08); margin-bottom: 20px; overflow: hidden; }
  input[type="date"][readonly], input[type="date"][disabled] { background-color: #eeeeee !important; cursor: not-allowed; }
  .table-responsive { max-height: 220px; overflow-y: auto; }
  .table-detalle thead th { position: sticky; top: 0; background-color: #f4f4f4; z-index: 1; }
</style>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Añadir Pedido de Vacunas</h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="vacunacion_pedidos_listado.php"><i class="fa fa-shopping-cart"></i>Pedidos</a></li>
      <li class="active"><a href="#"><i class="fa fa-plus"></i>Añadir</a></li>
    </ol>
  </section>

  <section class="content">
    <form id="formularioPedido" action="../../cfg/procesar_pedidos.php" method="POST" autocomplete="off">
      <input type="hidden" name="op" value="guardar_pedido">
      <input type="hidden" name="detalles_pedido_json" id="detalles_pedido_json">

      <div class="row">
        <div class="col-md-12">
          <div class="area-trabajo-blanca">

            <!-- DATOS DE LA SOLICITUD -->
            <div class="box box-primary">
              <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-clipboard"></i>Datos de la solicitud:</h3>
              </div>
              <div class="box-body">
                <div class="row">
                  <div class="col-sm-4 form-group">
                    <label>Usuario Solicitante (*):</label>
                    <select name="id_usuario" id="id_usuario" class="form-control" required>
                      <option value="">--- Seleccione el solicitante ---</option>
                      <!-- SIMULACIÓN: Aquí irían los usuarios de la BD -->
                      <option value="1" selected>Lic. María Rodríguez</option>
                      <option value="2">Enf. Carlos Pérez</option>
                    </select>
                  </div>

                  <div class="col-sm-4 form-group">
                    <label>Proveedor / Donante (*):</label>
                    <select id="proveedor" name="id_proveedor" class="form-control" required>
                      <option value="">--- Seleccione un proveedor ---</option>
                      <!-- SIMULACIÓN -->
                      <option value="1">SUAF Portuguesa</option>
                      <option value="2">Ministerio de Salud</option>
                    </select>
                  </div>

                  <div class="col-sm-3 form-group">
                    <label>Fecha y hora del pedido (*):</label>
                    <div class="input-group">
                      <input type="date" id="fecha_pedido" name="fecha_pedido" class="form-control" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" onkeydown="return false;" style="width: 100%;">
                      <div class="input-group-btn" style="width: 100%;">
                        <input type="time" id="hora_pedido" name="hora_pedido" class="form-control" value="<?php date_default_timezone_set('America/Caracas'); echo date('H:i'); ?>">
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- VACUNAS A SOLICITAR -->
            <div class="box box-success">
              <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-syringe"></i> Vacunas a Solicitar:</h3>
                <div class="box-tools pull-right" style="margin-left:5px;">
                  <button type="button" class="btn btn-primary btn-sm" id="btnAbrirModalAgregar" style="width:200px;">
                    <i class="fa fa-plus"></i> Añadir Vacuna
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
                        <th style="text-align: left;">Vacuna / Presentación</th>
                        <th style="width: 150px;">Cantidad Solicitada (Dosis)</th>
                        <th style="width: 100px;">Acciones</th>
                      </tr>
                    </thead>
                    <tbody id="cuerpoTablaPedido">
                      <tr id="filaVacia">
                        <td colspan="3" class="text-center text-muted">Aún no se han añadido vacunas al pedido actual.</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <div class="box-footer text-right" style="background-color: transparent; border-top: 1px solid #f4f4f4; padding-top: 15px;">
                <button type="button" class="btn btn-second" id="abrirModalRegresar">Regresar</button>
                <button type="button" class="btn btn-success" id="btnValidarGuardar"><i class="fa fa-save"></i>Guardar Pedido</button>
              </div>
            </div>

          </div>
        </div>
      </div>
    </form>
  </section>

  <!-- MODAL AGREGAR VACUNA -->
  <div class="modal fade" id="modalAgregarMedicamento" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h4 class="modal-title"><i class="fa fa-plus-circle"></i>Agregar al pedido</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="filtro_busqueda_rapida">Busqueda Rapida:</label>
                <input type="text" id="filtro_busqueda_rapida" name="filtro_busqueda_rapida" class="form-control" placeholder="Escriba nombre de la vacuna...">
              </div>
            </div>
            <br><br>
            <div class="col-sm-12 form-group">
              <label>Vacuna del Catálogo (*):</label>
              <div class="input-group">
                <select id="select_medicamento" class="form-control">
                  <option value="">--- Seleccione una Vacuna ---</option>
                  <!-- SIMULACIÓN -->
                  <option value="1" data-nombre="Antipoliomielítica (VPI) [1 Dosis]" data-componentes="Virus Inactivados">Antipoliomielítica (VPI) - [1 Dosis]</option>
                  <option value="2" data-nombre="Antisarampión (SRP) [10 Dosis]" data-componentes="Virus Atenuados">Antisarampión (SRP) - [10 Dosis]</option>
                  <option value="3" data-nombre="Vacuna BCG [20 Dosis]" data-componentes="Bacteria Atenuada">Vacuna BCG - [20 Dosis]</option>
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
              <label>Dosis a Solicitar (*):</label>
              <input type="text" id="cantidad_agregar" class="form-control" placeholder="Solo números" oninput="this.value = this.value.replace(/[^0-9]/g, ''); if(this.value === '0') this.value = '1';">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-success" id="btnConfirmarAgregarLista"><i class="fa fa-check"></i>Añadir</button>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL BÚSQUEDA AVANZADA (SIMPLIFICADO) -->
  <div class="modal fade" id="modalBúsquedaAvanzadaMedicamento" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h5 class="modal-title" id="modalBúsquedaAvanzadaMedicamentoLabel">Filtros de Búsqueda Avanzada</h5>
        </div>
        <div class="modal-body">
          <form id="formFiltroModal">
            <p class="text-muted">Complete uno o varios campos para refinar su búsqueda.</p>
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="filtro_nombre">Nombre (o ID):</label>
                  <input type="text" id="filtro_nombre" name="filtro_nombre" class="form-control" placeholder="Escriba nombre...">
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
                  <input type="text" id="filtro_principios" name="filtro_principios" class="form-control" placeholder="Ej: Virus...">
                </div>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-second" id="btnLimpiarFiltros">Limpiar Filtros</button>
          <button type="button" class="btn btn-primary" id="btnAplicarFiltros">Aplicar Filtros</button>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL AVISO -->
  <div class="modal fade" id="avisoModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div id="headerAvisoPedido" class="modal-header bg-crimson">
          <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i>Aviso Importante</h4>
        </div>
        <div class="modal-body" id="cuerpoAviso" style="font-size: 15px;"></div>
        <div class="modal-footer" style="padding: 10px;">
          <button type="button" id="confirmAviso" class="btn btn-second" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL REGRESAR -->
  <div class="modal fade" id="modalRegresarInventario" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h5 class="modal-title">Confirmación de Regreso</h5>
        </div>
        <div class="modal-body">
          <p>Al hacer clic en "Abandonar Formulario", perderá todos los datos no guardados. ¿Desea continuar?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <a href="vacunacion_pedidos_listado.php" class="btn btn-danger">Abandonar Formulario</a>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL GUARDAR -->
  <div class="modal" id="modalGuardar" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-green">
          <h4 class="modal-title"><i class="fa fa-check-circle"></i> Confirmación de Guardado</h4>
        </div>
        <div class="modal-body" style="font-size: 15px;">
          ¿Está seguro que desea registrar este pedido de vacunas?
          <br><small class="text-muted">La orden quedará en estado "Pendiente".</small>
        </div>
        <div class="modal-footer" style="padding: 10px;">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <button type="button" id="confirmarGuardar" class="btn btn-success">Aceptar</button>
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
    // Lógica de modales (Mantenida)
    $(document).on('show.bs.modal', '.modal', function() {
      var zIndex = 1040 + (10 * $('.modal:visible').length);
      $(this).css('z-index', zIndex);
      setTimeout(function() {
        $('.modal-backdrop').not(':last').css('opacity', 0);
        $('.modal-backdrop').last().css('z-index', zIndex - 1);
      }, 0);
    });

    $(document).on('hidden.bs.modal', '.modal', function() {
      if ($('.modal:visible').length > 0) {
        $('.modal-backdrop').last().css('opacity', 0.7);
        $('body').addClass('modal-open');
      } else {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
        $('body').css('padding-right', '');
      }
    });

    // SIMULACIÓN DE CARGA POR PUNTO DE REORDEN
    $('#btnCargarReorden').on('click', function() {
      let $btn = $(this);
      $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Verificando...');

      // SIMULACIÓN: En lugar de AJAX, quemamos los datos
      setTimeout(function() {
        let response = [
          { id_descripcion: '1', nombre_completo: 'Antipoliomielítica (VPI) [1 Dosis]', cantidad_a_pedir: 200, existencia_actual: 20, stock_maximo: 500 },
          { id_descripcion: '3', nombre_completo: 'Vacuna BCG [20 Dosis]', cantidad_a_pedir: 50, existencia_actual: 10, stock_maximo: 300 }
        ];

        if (response.length === 0) {
          mostrarAviso("<strong>¡Inventario Seguro!</strong> No existen vacunas que estén iguales o por debajo del stock mínimo establecido.");
          $btn.prop('disabled', false).html('<i class="fa fa-refresh"></i> Cargar por Punto de Reorden');
          return;
        }

        let itemsAgregados = 0;
        response.forEach(function(item) {
          let yaExiste = listaItemsPedido.find(prod => prod.id_descripcion == item.id_descripcion);
          if (!yaExiste) {
            listaItemsPedido.push({
              id_descripcion: item.id_descripcion,
              nombre: item.nombre_completo,
              cantidad: parseInt(item.cantidad_a_pedir),
              existencia_actual: parseInt(item.existencia_actual),
              stock_maximo: parseInt(item.stock_maximo)
            });
            itemsAgregados++;
          }
        });

        if (itemsAgregados > 0) {
          actualizarTablaVisual();
          mostrarAviso("Se cargaron automáticamente <strong>" + itemsAgregados + "</strong> vacunas en estado crítico.", "exito");
        } else {
          mostrarAviso("Las vacunas que requieren reorden ya se encuentran enlistadas en la tabla.");
        }
        $btn.prop('disabled', false).html('<i class="fa fa-refresh"></i> Cargar por Punto de Reorden');
      }, 1000);
    });

    // SIMULACIÓN AL SELECCIONAR VACUNA
    $('#select_medicamento').on('change', function() {
      let idMed = $(this).val();
      $('#existencia_actual, #stock_maximo').val('');

      if (idMed) {
        // SIMULACIÓN: En lugar de AJAX, quemamos los datos
        let data = {};
        if (idMed == '1') { data = { existencia_actual: 20, stock_maximo: 500 }; }
        else if (idMed == '2') { data = { existencia_actual: 100, stock_maximo: 300 }; }
        else if (idMed == '3') { data = { existencia_actual: 10, stock_maximo: 300 }; }

        let ext = data.existencia_actual || 0;
        let sMax = data.stock_maximo || 0;

        $('#existencia_actual').val(ext);
        $('#stock_maximo').val(sMax);

        if (sMax > 0 && ext >= sMax) {
          $('#cantidad_agregar').prop('disabled', true).val('');
          mostrarAviso(`⚠️ <b>Atención:</b> La existencia actual (${ext}) ya cubre o supera el Stock Máximo permitido (${sMax}). No es necesario pedir más de esta vacuna.`);
        } else {
          $('#cantidad_agregar').prop('disabled', false);
        }
      } else {
        $('#cantidad_agregar').prop('disabled', false);
      }
    });

    // LÓGICA DE FILTROS AVANZADOS (SIMULADA)
    const medicamentoSelectPrincipal = $('#select_medicamento');
    window.opcionesOriginalesMedicamentos = medicamentoSelectPrincipal.html();

    $('#btnLimpiarFiltros').on('click', function() {
      $('#formFiltroModal')[0].reset();
      medicamentoSelectPrincipal.html(window.opcionesOriginalesMedicamentos);
    });

    $('#btnAplicarFiltros').on('click', function(e) {
      e.preventDefault();
      const busquedaRapida = $('#filtro_busqueda_rapida').val().trim();
      const nombre_med = $('#filtro_nombre').val().trim();
      const principios = $('#filtro_principios').val().trim();

      if (busquedaRapida === "" && nombre_med === "" && principios === "") {
        mostrarAviso("Los campos de filtro están vacíos. Escriba alguna etiqueta para buscar.");
        return;
      }

      let opcionesFiltradas = '<option value="">--- Seleccione una Vacuna ---</option>';
      const $opciones = $(opcionesOriginalesMedicamentos).filter('option[value!=""]');
      let hayResultados = false;

      $opciones.each(function() {
        let texto = $(this).text().toLowerCase();
        let match = true;
        if (busquedaRapida && !texto.includes(busquedaRapida.toLowerCase())) match = false;
        if (nombre_med && !texto.includes(nombre_med.toLowerCase())) match = false;
        if (principios && !texto.includes(principios.toLowerCase())) match = false;
        if (match) { opcionesFiltradas += $(this)[0].outerHTML; hayResultados = true; }
      });

      if (hayResultados) {
        medicamentoSelectPrincipal.html(opcionesFiltradas);
        $('#modalBúsquedaAvanzadaMedicamento').modal('hide');
      } else {
        mostrarAviso("⚠️ No se encontraron vacunas que coincidan con los filtros aplicados.");
      }
    });

    // LÓGICA DE TAGS
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
            if (tagsArray.length === 0) { $('#btnLimpiarFiltros').click(); } else { $('#btnAplicarFiltros').click(); }
          }
        }
      });

      $contenedor.on('click', '.remove-tag', function() {
        let index = $(this).data('index');
        tagsArray.splice(index, 1);
        renderizarTags();
        if (tagsArray.length === 0) { $('#btnLimpiarFiltros').click(); } else { $('#btnAplicarFiltros').click(); }
      });

      $contenedor.on('click', function() { $inputFalso.focus(); });
      $('#btnLimpiarFiltros').on('click', function() { tagsArray = []; renderizarTags(); });
    }

    inicializarTags('#filtro_busqueda_rapida');
    inicializarTags('#filtro_principios');

    // ABRIR MODAL AGREGAR
    $('#btnAbrirModalAgregar').on('click', function() {
      indexEdicion = -1;
      $('#modalAgregarMedicamento .modal-title').html('<i class="fa fa-plus-circle"></i> Agregar al pedido');
      $('#btnConfirmarAgregarLista').html('<i class="fa fa-check"></i> Añadir a la Lista');
      $('#select_medicamento').val('').prop('disabled', false).removeClass('input-error');
      $('#cantidad_agregar').val('').removeClass('input-error');
      $('#existencia_actual, #stock_maximo').val('');
      $('#modalAgregarMedicamento').modal('show');
    });

    // CONFIRMAR AGREGAR A LA LISTA
    $('#btnConfirmarAgregarLista').on('click', function() {
      let idMed = $('#select_medicamento').val();
      let nombreMed = $('#select_medicamento option:selected').data('nombre');
      let cantidad = parseInt($('#cantidad_agregar').val());
      let existencia = parseInt($('#existencia_actual').val()) || 0;
      let sMax = parseInt($('#stock_maximo').val()) || 0;

      $('#select_medicamento, #cantidad_agregar').removeClass('input-error');

      if (!idMed) { $('#select_medicamento').addClass('input-error'); mostrarAviso("Debe seleccionar una vacuna del catálogo."); return; }
      if (!cantidad || cantidad <= 0 || isNaN(cantidad)) { $('#cantidad_agregar').addClass('input-error'); mostrarAviso("Por favor, ingrese una cantidad válida mayor a cero."); return; }

      if (sMax > 0 && (existencia + cantidad) > sMax) {
        $('#cantidad_agregar').addClass('input-error');
        let permitido = sMax - existencia;
        mostrarAviso(`⚠️ <b>Límite excedido:</b> Actualmente hay ${existencia} dosis y el Stock Máximo es ${sMax}.<br><br>Solo puedes pedir un máximo de <b>${permitido}</b> dosis extra para no sobrepasar el límite de tu almacén.`);
        return;
      }

      if (indexEdicion === -1) {
        let existe = listaItemsPedido.find(item => item.id_descripcion == idMed);
        if (existe) {
          existe.cantidad += cantidad;
        } else {
          listaItemsPedido.push({ id_descripcion: idMed, nombre: nombreMed, cantidad: cantidad, existencia_actual: existencia, stock_maximo: sMax });
        }
      } else {
        listaItemsPedido[indexEdicion].cantidad = cantidad;
        indexEdicion = -1;
      }

      actualizarTablaVisual();
      $('#modalAgregarMedicamento').removeClass('in').addClass('out');
      setTimeout(function() { $('#modalAgregarMedicamento').modal('hide'); $('#modalAgregarMedicamento').removeClass('out'); }, 400);
    });

    // ACTUALIZAR TABLA VISUAL
    function actualizarTablaVisual() {
      let tbody = $('#cuerpoTablaPedido');
      tbody.empty();

      if (listaItemsPedido.length === 0) {
        tbody.html('<tr id="filaVacia"><td colspan="3" class="text-center text-muted">Aún no se han añadido vacunas al pedido actual.</td></tr>');
        return;
      }

      listaItemsPedido.forEach((item, index) => {
        let fila = `<tr>
            <td style="text-align: left;"><strong>${item.nombre}</strong></td>
            <td><span class="badge bg-green" style="font-size:14px;">${item.cantidad}</span></td>
            <td>
              <button type="button" class="btn btn-warning btn-xs btn-editar-fila" data-index="${index}" title="Editar vacuna">
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

    // EDITAR ITEM
    $(document).on('click', '.btn-editar-fila', function() {
      let index = $(this).data('index');
      let item = listaItemsPedido[index];
      indexEdicion = index;

      $('#existencia_actual').val(item.existencia_actual);
      $('#stock_maximo').val(item.stock_maximo);
      $('#modalAgregarMedicamento .modal-title').html('<i class="fa fa-pencil"></i> Editar cantidad solicitada');
      $('#btnConfirmarAgregarLista').html('<i class="fa fa-save"></i> Guardar Cambios');
      $('#select_medicamento').val(item.id_descripcion).prop('disabled', true).removeClass('input-error');
      $('#cantidad_agregar').val(item.cantidad).removeClass('input-error');
      $('#modalAgregarMedicamento').modal('show');
    });

    $('#abrirModalRegresar').on('click', function() { $('#modalRegresarInventario').modal('show'); });

    // VALIDACIÓN FINAL
    $('#btnValidarGuardar').on('click', function() {
      $('#fecha_pedido').removeClass('input-error');
      $('#hora_pedido').removeClass('input-error');
      $('#proveedor').removeClass('input-error');
      $('#id_usuario').removeClass('input-error');
      $('#btnAbrirModalAgregar').removeClass('btn-error-sombreado');

      if ($('#fecha_pedido').val() === "") { $('#fecha_pedido').addClass('input-error'); mostrarAviso("Debe asignar una fecha al pedido."); return; }
      if ($('#hora_pedido').val() === "") { $('#hora_pedido').addClass('input-error'); mostrarAviso("Debe asignar una hora al pedido."); return; }
      if ($('#id_usuario').val() === "") { $('#id_usuario').addClass('input-error'); mostrarAviso("Debe seleccionar el Usuario Solicitante."); return; }
      if ($('#proveedor').val() === "") { $('#proveedor').addClass('input-error'); mostrarAviso("Debe seleccionar obligatoriamente un Proveedor / Donante."); return; }

      if (listaItemsPedido.length === 0) {
        $('#btnAbrirModalAgregar').addClass('btn-error-sombreado');
        mostrarAviso("No puede generar un pedido vacío. Añada al menos una vacuna a la lista.");
        return;
      }

      $('#detalles_pedido_json').val(JSON.stringify(listaItemsPedido));
      $('#modalGuardar').removeClass('out').addClass('in').modal('show');
    });

    $('#confirmarGuardar').on('click', function() {
      $('#modalGuardar').modal('hide');
      // SIMULACIÓN: Aquí se enviaría el formulario real
      // $('#formularioPedido')[0].submit();
      mostrarAviso("✅ <b>Pedido de Vacunas registrado (Simulado).</b><br><br>En la versión real, esto guardaría el pedido en la base de datos.", "exito");
      setTimeout(function() {
        window.location.href = 'vacunacion_pedidos_listado.php';
      }, 2500);
    });
  });
</script>