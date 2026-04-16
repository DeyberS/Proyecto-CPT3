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
  <title>Inventario | Entrada </title>
  <?php
  include('includes/headerNav2.php');
  ?>

  <style>
    /* ---------------------------------------------------------------------- */
    /* ANIMACIONES Y ESTILOS DE MODALES (Igualados a Ajustes)                 */
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
    #avisoModal,
    #modalEntradaGuardar,
    #modalBúsquedaAvanzadaMedicamento,
    #modalRegresarInventario,
    #modalAgregarMedicamento {
      animation: fadeIn 0.4s ease-out;
    }

    .modal.out .modal-dialog {
      animation: fadeOut 0.4s ease-in;
    }

    .modal-open .modal-backdrop {
      opacity: 0.7 !important;
      animation: pulse-opacity 0.3s forwards;
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
    }

    /* ---------------------------------------------------------------------- */
    /* ESTILOS DE VALIDACIÓN Y LAYOUT */
    /* ---------------------------------------------------------------------- */
    .input-error {
      border: 2px solid crimson !important;
      box-shadow: 0 0 5px crimson;
    }

    .table-detalle th {
      background-color: #f4f4f4;
      text-align: center;
    }

    .table-detalle td {
      text-align: center;
      vertical-align: middle !important;
    }

    .row-vence-pronto {
      background-color: #fff3cd !important;
    }

    .area-trabajo-blanca {
      background-color: #ffffff;
      padding: 25px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
      margin-bottom: 20px;
      overflow: hidden;
      /* Asegura contener elementos flotantes internamente */
    }

    /* Regla añadida para mantener el color gris en inputs de fecha bloqueados */
    input[type="date"][readonly],
    input[type="date"][disabled] {
      background-color: #eeeeee !important;
      cursor: not-allowed;
    }
  </style>
</head>

<body>
  <div class="content-wrapper">
    <section class="content-header">
      <h1>Entrada de Medicamentos <small>Recepción y Lotes</small></h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-users"></i>Inventario</a></li>
        <li class="active"><a href="#"><i class="fa fa-arrow-down"></i>Entrada</a></li>
      </ol>
    </section>

    <section class="content">
      <form id="formularioEntrada" method="POST" action="../../cfg/movimientos_inventario.php" novalidate autocomplete="off">
        <input type="hidden" name="op" id="op" value="<?php echo $operacion_actual; ?>">
        <input type="hidden" name="detalle_medicamentos" id="detalle_medicamentos" value="[]">

        <div class="row">
          <div class="col-md-12">

            <div class="area-trabajo-blanca">

              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title"><i class="fa fa-truck"></i> Datos de la Recepción:</h3>
                </div>
                <div class="box-body">
                  <div class="row">
                    <div class="col-sm-4 form-group">
                      <label>Proveedor / Donante (*):</label>
                      <select id="proveedor" name="proveedor" class="form-control" required>
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

                    <div class="col-sm-4 form-group">
                      <label>Fecha de Recepción (*):</label>
                      <input type="date" id="fecha_recepcion" name="fecha_recepcion" class="form-control" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" onkeydown="return false;" readonly>
                    </div>

                    <div class="col-sm-4 form-group">
                      <label>Observaciones Generales:</label>
                      <input type="text" id="observaciones_generales" name="observaciones_generales" class="form-control" placeholder="Ej: Cajas en buen estado..." maxlength="255">
                    </div>
                  </div>
                </div>
              </div>

              <div class="box box-success">
                <div class="box-header with-border">
                  <h3 class="box-title"><i class="fa fa-medkit"></i> Medicamentos a Ingresar:</h3>
                  <div class="box-tools pull-right">
                    <button type="button" class="btn btn-primary btn-sm" id="btnAbrirModalAgregar">
                      <i class="fa fa-plus"></i> Añadir Medicamento
                    </button>
                  </div>
                </div>
                <div class="box-body">
                  <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-detalle" id="tablaMedicamentos">
                      <thead>
                        <tr>
                          <th>Medicamento</th>
                          <th>Lote</th>
                          <th>F. Fabricación</th>
                          <th>F. Vencimiento</th>
                          <th>Cant.</th>
                          <th>Acciones</th>
                        </tr>
                      </thead>
                      <tbody id="cuerpoTablaMedicamentos">
                        <tr id="filaVacia">
                          <td colspan="6" class="text-center text-muted">Aún no se han añadido medicamentos a esta entrada.</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
                <div class="box-footer text-right" style="background-color: transparent; border-top: 1px solid #f4f4f4; padding-top: 15px;">
                  <button type="button" class="btn btn-secondary" id="abrirModalRegresar">Regresar</button>
                  <button type="submit" class="btn btn-success" id="btnPrepararGuardado"><i class="fa fa-save"></i>Guardar</button>
                </div>
              </div>

            </div>
          </div>
        </div>
      </form>
    </section>
  </div>

  <div class="modal" id="modalAgregarMedicamento" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 1;">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title" style="color: white;"><i class="fa fa-plus-circle"></i> Ingresar Lote de Medicamento</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-sm-12 form-group">
              <label>Medicamento del Catálogo (*):</label>
              <div class="input-group">
                <select id="Id_descripcion_medicamento" name="Id_descripcion_medicamento" class="form-control">
                  <option value="">--- Seleccione un Medicamento ---</option>
                  <?php
                  $sql_medicamentos = "SELECT dm.Id AS id_desc, m.nombre_medicamento, p.nombre_presentacion,
                    GROUP_CONCAT(CONCAT(IFNULL(pa.nombre,''), ' ', IFNULL(dpm.cantidad_unidad_medida,''), ' ', IFNULL(um.unidad,'')) SEPARATOR ' + ') AS componentes
                    FROM descripcion_medicamento dm
                    INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
                    INNER JOIN presentacion p ON dm.Id_presentacion = p.Id_presentacion
                    LEFT JOIN detalle_principio_medicamento dpm ON dm.Id = dpm.id_medicamento
                    LEFT JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida
                    LEFT JOIN principio_activo pa ON dpm.id_principio_activo = pa.Id_principio_activo
                    WHERE m.estatus = 1 AND (dm.estatus = '1' OR dm.estatus = 1) 
                    GROUP BY dm.Id ORDER BY m.nombre_medicamento ASC";

                  $resultado_medicamentos = $conexion->query($sql_medicamentos);
                  if ($resultado_medicamentos && $resultado_medicamentos->num_rows > 0) {
                    while ($row_med = $resultado_medicamentos->fetch_assoc()) {
                      $comp = trim($row_med['componentes']) ? " (" . htmlspecialchars($row_med['componentes']) . ")" : "";
                      // EXTRAÍMOS EL COMPONENTE PARA PASARLO POR DATA-COMPONENTES
                      $componentesLimpios = trim($row_med['componentes']) ? htmlspecialchars($row_med['componentes']) : 'Sin principios activos registrados';
                      echo '<option value="' . $row_med['id_desc'] . '" data-nombre="' . htmlspecialchars($row_med['nombre_medicamento'] . " [" . $row_med['nombre_presentacion'] . "]") . '" data-componentes="' . $componentesLimpios . '">' . htmlspecialchars($row_med['nombre_medicamento']) . $comp . " - [" . htmlspecialchars($row_med['nombre_presentacion']) . "]" . '</option>';
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

            <div class="col-sm-4 form-group">
              <label>Existencia Actual:</label>
              <input type="text" id="existencia_actual" class="form-control" readonly disabled style="background-color: #f9f9f9;">
            </div>
            <div class="col-sm-4 form-group">
              <label>Stock Mínimo:</label>
              <input type="text" id="stock_minimo" class="form-control" readonly disabled style="background-color: #f9f9f9;">
            </div>
            <div class="col-sm-4 form-group">
              <label>Stock Máximo:</label>
              <input type="text" id="stock_maximo" class="form-control" readonly disabled style="background-color: #f9f9f9;">
            </div>

            <div class="clearfix"></div>

            <div class="col-sm-4 form-group mt-3">
              <label style="width: 100%;">Número de Lote (*):
                <button type="button" id="btnCopiarUltimoLote" class="btn btn-info btn-xs pull-right" style="display: none; padding: 2px 5px;" title="Copiar Lote y Fechas del último ingresado">
                  <i class="fa fa-copy"></i> Usar Último
                </button>
              </label>
              <input type="text" id="lote" class="form-control" list="lista_lotes" placeholder="Ej: L-2026X" style="text-transform: uppercase;">
              <datalist id="lista_lotes"></datalist>
            </div>
            <div class="col-sm-4 form-group mt-3">
              <label>F. Fabricación (*):</label>
              <input type="date" id="fecha_fabricacion" class="form-control" onkeydown="return false;">
            </div>
            <div class="col-sm-4 form-group mt-3">
              <label>F. Vencimiento (*):</label>
              <input type="date" id="fecha_vencimiento" class="form-control" disabled onkeydown="return false;">
            </div>

            <div class="clearfix"></div>

            <div class="col-sm-4 form-group mt-3">
              <label>Unidades a Ingresar (*):</label>
              <input type="text" id="cantidad" class="form-control" placeholder="Solo números, sin 0 inicial" oninput="this.value = this.value.replace(/[^0-9]/g, ''); if(this.value === '0') this.value = '1';">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-success" id="btnConfirmarAgregarMedicamento"><i class="fa fa-check"></i> Añadir a la Lista</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalBúsquedaAvanzadaMedicamento" role="dialog" aria-labelledby="modalBúsquedaAvanzadaMedicamentoLabel" aria-hidden="true">
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


  <?php if (isset($conexion)) {
    $conexion->close();
  }
  include('includes/footer.php'); ?>

  <script>
    $(document).ready(function() {
      // ARRAY PRINCIPAL QUE GUARDA LOS MEDICAMENTOS
      let listaDetalles = [];
      let lotesCargados = [];
      
      // NUEVAS VARIABLES PARA EDICIÓN Y REUTILIZACIÓN DE LOTE
      let editandoIndex = -1;
      let ultimoLoteIngresado = null; 

      const hoy = new Date().toISOString().split('T')[0];
      $('#fecha_fabricacion').attr('max', hoy);

      // ---------------------------------------------------------------------
      // LÓGICA DE CIERRE DE MODALES ANIMADOS (Traida de Ajustes)
      // ---------------------------------------------------------------------
      $('.modal').on('click', '[data-dismiss="modal"]', function() {
        var $modal = $(this).closest('.modal');
        $modal.removeClass('in').addClass('out');

        setTimeout(function() {
          $modal.modal('hide');
          $modal.removeClass('out');
        }, 400);
      });

      $('.modal').on('hidden.bs.modal', function() {
        if ($('.modal:visible').length) {
          $('body').addClass('modal-open');
        } else {
          $('body').removeClass('modal-open');
          // EL BACKDROP SE ELIMINA SOLO SI NO QUEDAN MODALES ABIERTOS
          $('.modal-backdrop').remove();
        }
      });

      // -------------------------------------------------------------
      // FUNCIONES BASE
      // -------------------------------------------------------------
      function mostrarAviso(mensaje) {
        $('#avisoTexto').html(mensaje);
        $('#avisoModal').modal('show');
      }

      function limpiarFormularioModal() {
        $('#Id_descripcion_medicamento').val('').trigger('change');
        $('#lote, #fecha_fabricacion, #fecha_vencimiento, #cantidad').val('').removeClass('input-error');
        $('#fecha_fabricacion, #fecha_vencimiento').prop('readonly', false);
        $('#fecha_vencimiento').prop('disabled', true);
        $('#lote').css('border-color', '#ced4da');
        $('#existencia_actual, #stock_minimo, #stock_maximo').val('');
      }

      $('#abrirModalRegresar').on('click', function() {
        $('#modalRegresarInventario').modal('show');
      });

      // -------------------------------------------------------------
      // FILTRADO AVANZADO AJAX (Igual a Ajustes)
      // -------------------------------------------------------------
      const medicamentoSelectPrincipal = $('#Id_descripcion_medicamento');

      $('#btnLimpiarFiltros').on('click', function() {
        $('#formFiltroModal')[0].reset();
      });

      $('#btnAplicarFiltros').on('click', function(e) {
        e.preventDefault(); 

        const datosFiltro = $('#formFiltroModal').serialize();

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

      // -------------------------------------------------------------
      // LÓGICA DEL MODAL AGREGAR MEDICAMENTO
      // -------------------------------------------------------------
      $('#btnAbrirModalAgregar').on('click', function() {
        editandoIndex = -1; // Nos aseguramos que es un nuevo registro
        limpiarFormularioModal();
        $('#btnConfirmarAgregarMedicamento').html('<i class="fa fa-check"></i> Añadir a la Lista');
        
        // Muestra u oculta el botón de copiar lote dependiendo de si hay uno previo
        if (ultimoLoteIngresado) {
          $('#btnCopiarUltimoLote').show();
        } else {
          $('#btnCopiarUltimoLote').hide();
        }

        $('#modalAgregarMedicamento').modal('show');
      });

      // LÓGICA PARA BOTÓN REUTILIZAR LOTE
      $('#btnCopiarUltimoLote').on('click', function() {
        if (ultimoLoteIngresado) {
          $('#lote').val(ultimoLoteIngresado.lote);
          $('#fecha_fabricacion').val(ultimoLoteIngresado.fecha_fabricacion);
          $('#fecha_vencimiento').val(ultimoLoteIngresado.fecha_vencimiento).prop('disabled', false);
          $('#lote').trigger('input'); // Para que ejecute cualquier validación
        }
      });

      $('#Id_descripcion_medicamento').on('change', function() {
        const medicamentoId = $(this).val();
        $('#lista_lotes').empty();
        lotesCargados = [];

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
                $('#existencia_actual').val(data.existencia_actual !== null && data.existencia_actual !== undefined ? data.existencia_actual : 0);
                $('#stock_minimo').val(data.stock_minimo !== null && data.stock_minimo !== undefined ? data.stock_minimo : 0);
                $('#stock_maximo').val(data.stock_maximo !== null && data.stock_maximo !== undefined ? data.stock_maximo : 0);

                if (data.lotes && data.lotes.length > 0) {
                  lotesCargados = data.lotes;
                  data.lotes.forEach(function(item) {
                    $('#lista_lotes').append('<option value="' + item.lote + '">');
                  });
                }
              }
            }
          });
        } else {
          $('#existencia_actual, #stock_minimo, #stock_maximo').val('');
        }
      });

      $('#lote').on('input change', function() {
        const loteEscrito = $(this).val().trim().toUpperCase();
        $(this).val(loteEscrito);

        if (loteEscrito === "") {
          $('#fecha_fabricacion, #fecha_vencimiento').val('').prop('readonly', false);
          return;
        }

        const loteEncontrado = lotesCargados.find(l => l.lote.toUpperCase() === loteEscrito);

        if (loteEncontrado) {
          $('#fecha_fabricacion').val(loteEncontrado.fecha_fabricacion).prop('readonly', true);
          $('#fecha_vencimiento').prop('disabled', false).val(loteEncontrado.fecha_vencimiento).prop('readonly', true);
          $(this).css('border-color', '#28a745');
        } else {
          $('#fecha_fabricacion, #fecha_vencimiento').prop('readonly', false);
          $(this).css('border-color', '#ced4da');
        }
      });

      $('#fecha_fabricacion').on('change', function() {
        if ($(this).val()) {
          $('#fecha_vencimiento').prop('disabled', false).attr('min', $(this).val());
        } else {
          $('#fecha_vencimiento').prop('disabled', true).val('').removeAttr('min');
        }
      });

      // -------------------------------------------------------------
      // AGREGAR O EDITAR EN LA LISTA DE DETALLES
      // -------------------------------------------------------------
      $('#btnConfirmarAgregarMedicamento').on('click', function() {
        const id_med = $('#Id_descripcion_medicamento').val();
        const nombre_med = $('#Id_descripcion_medicamento option:selected').data('nombre');
        const componentes = $('#Id_descripcion_medicamento option:selected').data('componentes'); // Capturamos Principios Activos
        const lote = $('#lote').val().trim().toUpperCase();
        const f_fab = $('#fecha_fabricacion').val();
        const f_venc = $('#fecha_vencimiento').val();
        const cant = parseInt($('#cantidad').val());

        $('.modal-body input, .modal-body select').removeClass('input-error');

        if (!id_med) {
          $('#Id_descripcion_medicamento').addClass('input-error');
          mostrarAviso('Seleccione un medicamento.');
          return;
        }
        if (!lote) {
          $('#lote').addClass('input-error');
          mostrarAviso('Ingrese el número de lote.');
          return;
        }
        if (!f_fab) {
          $('#fecha_fabricacion').addClass('input-error');
          mostrarAviso('Ingrese la fecha de fabricación.');
          return;
        }
        if (!f_venc) {
          $('#fecha_vencimiento').addClass('input-error');
          mostrarAviso('Ingrese la fecha de vencimiento.');
          return;
        }
        if (isNaN(cant) || cant <= 0) {
          $('#cantidad').addClass('input-error');
          mostrarAviso('La cantidad debe ser mayor a 0.');
          return;
        }
        if (f_venc <= hoy) {
          $('#fecha_vencimiento').addClass('input-error');
          mostrarAviso('El medicamento ya está vencido.');
          return;
        }
        if (f_venc < f_fab) {
          $('#fecha_vencimiento').addClass('input-error');
          mostrarAviso('La fecha de vencimiento es incorrecta.');
          return;
        }

        // Verificamos si existe PERO ignoramos el que estamos editando actualmente
        const existeIndex = listaDetalles.findIndex(item => item.id_medicamento === id_med && item.lote === lote);
        if (existeIndex !== -1 && existeIndex !== editandoIndex) {
          mostrarAviso('Este medicamento con el mismo Lote ya está en la lista. Si desea modificarlo, edítelo directamente usando el botón amarillo en la tabla.');
          return;
        }

        const nuevoItem = {
          id_medicamento: id_med,
          nombre_medicamento: nombre_med,
          componentes: componentes, // Guardamos los principios activos
          lote: lote,
          fecha_fabricacion: f_fab,
          fecha_vencimiento: f_venc,
          cantidad: cant
        };

        // Si estamos editando, reemplazamos el registro, sino hacemos un push nuevo
        if (editandoIndex !== -1) {
          listaDetalles[editandoIndex] = nuevoItem;
          editandoIndex = -1; // Reseteamos el index tras editar
        } else {
          listaDetalles.push(nuevoItem);
        }

        // Guardamos los datos de este lote como el "último ingresado" para usarlo después
        ultimoLoteIngresado = {
          lote: lote,
          fecha_fabricacion: f_fab,
          fecha_vencimiento: f_venc
        };

        actualizarTablaDetalles();

        // Cierre animado del modal agregar medicamento
        $('#modalAgregarMedicamento').removeClass('in').addClass('out');
        setTimeout(function() {
          $('#modalAgregarMedicamento').modal('hide');
          $('#modalAgregarMedicamento').removeClass('out');
        }, 400);
      });

      // -------------------------------------------------------------
      // DIBUJAR LA TABLA, ELIMINAR Y EDITAR ÍTEMS
      // -------------------------------------------------------------
      function actualizarTablaDetalles() {
        const tbody = $('#cuerpoTablaMedicamentos');
        tbody.empty();

        if (listaDetalles.length === 0) {
          tbody.append('<tr id="filaVacia"><td colspan="6" class="text-center text-muted">Aún no se han añadido medicamentos a esta entrada.</td></tr>');
        } else {
          listaDetalles.forEach((item, index) => {
            let rowClass = "";
            let diffDias = (new Date(item.fecha_vencimiento) - new Date(hoy)) / (1000 * 60 * 60 * 24);
            if (diffDias < 180) rowClass = "row-vence-pronto";

            // Se agregó ${item.componentes} debajo del nombre y el botón de editar
            tbody.append(`
              <tr class="${rowClass}">
                <td style="text-align: left;">
                  ${item.nombre_medicamento}
                  <br><small class="text-muted" style="font-size: 11px;"><i>${item.componentes}</i></small>
                </td>
                <td><strong>${item.lote}</strong></td>
                <td>${item.fecha_fabricacion}</td>
                <td>${item.fecha_vencimiento} ${diffDias < 180 ? ' <i class="fa fa-warning text-warning" title="Vence pronto"></i>' : ''}</td>
                <td><span class="badge bg-green" style="font-size:14px;">${item.cantidad}</span></td>
                <td>
                  <button type="button" class="btn btn-warning btn-xs btn-editar-fila" data-index="${index}" title="Editar medicamento">
                    <i"><img src="../../recursos/imagenes/iconos/editar.png" style="width:10px; height:10px;"></i>
                  </button>
                  <button type="button" class="btn btn-danger btn-xs btn-eliminar-fila" data-index="${index}" title="Eliminar de la lista">
                    <i><img src="../../recursos/imagenes/iconos/Delete.png" style="width:10px; height:10px;"></i>
                  </button>
                </td>
              </tr>
            `);
          });
        }

        $('#detalle_medicamentos').val(JSON.stringify(listaDetalles));
      }

      // ACCIÓN DE EDITAR (Carga los datos al modal)
      $('#cuerpoTablaMedicamentos').on('click', '.btn-editar-fila', function() {
        const index = $(this).data('index');
        const item = listaDetalles[index];
        editandoIndex = index; // Declaramos en qué posición estamos trabajando

        limpiarFormularioModal(); // Limpiamos primero todo el modal
        
        // Rellenamos el modal con los datos del item
        $('#Id_descripcion_medicamento').val(item.id_medicamento).trigger('change');
        $('#lote').val(item.lote);
        $('#fecha_fabricacion').val(item.fecha_fabricacion);
        $('#fecha_vencimiento').val(item.fecha_vencimiento).prop('disabled', false);
        $('#cantidad').val(item.cantidad);

        $('#btnCopiarUltimoLote').hide(); // Ocultamos el botón de copiar lote para no confundir mientras se edita
        $('#btnConfirmarAgregarMedicamento').html('<i class="fa fa-save"></i> Guardar Cambios');
        
        $('#modalAgregarMedicamento').modal('show');
      });

      $('#cuerpoTablaMedicamentos').on('click', '.btn-eliminar-fila', function() {
        const index = $(this).data('index');
        listaDetalles.splice(index, 1);
        actualizarTablaDetalles();
      });

      // -------------------------------------------------------------
      // SUBMIT Y GUARDADO FINAL
      // -------------------------------------------------------------
      $('#formularioEntrada').on('submit', function(e) {
        e.preventDefault();

        if ($('#proveedor').val() === "") {
          mostrarAviso("Debe seleccionar el Proveedor/Donante.");
          return;
          
        }

        if (listaDetalles.length === 0) {
          mostrarAviso("Debe añadir al menos un medicamento a la lista para generar una entrada.");
          return;
        }

        $('#modalEntradaGuardar').modal('show');
      });

      $('#confirmarGuardadoFinal').on('click', function() {
        $('#modalEntradaGuardar').modal('hide');
        $('#formularioEntrada').off('submit').submit();
      });
    });
  </script>
</body>

</html>