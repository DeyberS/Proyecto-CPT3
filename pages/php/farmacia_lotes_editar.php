<?php
if (!isset($_GET['Id']) || empty($_GET['Id'])) {
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }
  $_SESSION['mensaje_user_error'] = "Debe seleccionar un lote válido para editar.";
  header("Location: farmacia_lotes_listado.php");
  exit();
}

$id_lote = $_GET['Id'];
include('includes/headerNav2.php');
verificarAcceso('Editar Lotes');
?>

<head>
  <title>Lotes | Editar</title>
  <style>
    .input-error {
      border: 2px solid crimson !important;
      box-shadow: 0 0 5px crimson !important;
      background-color: #fff5f5 !important;
    }

    #contenedor-medicamentos-edicion .list-group-item {
      font-size: 13px;
    }

    #contenedor-medicamentos-edicion .list-group-item:hover {
      background-color: #f5f5f5;
    }

    .tooltip {
      z-index: 10000000 !important;
    }

    #modalMedicamentosLote {
      z-index: 1050;
    }

    #modalBuscarMedicamentoLote {
      z-index: 1060;
    }

    .input-group-addon.stock-badge {
      font-weight: bold;
      min-width: 90px;
      transition: all 0.3s;
    }
  </style>
</head>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Editar Lote</h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="#"><i class="fa fa-users"></i>Lotes</a></li>
      <li class="active"><a href="#"><i class="fa fa-pencil"></i>Editar</a></li>
    </ol>
  </section>

  <section class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="nav-tabs-custom">
          <ul class="nav nav-tabs">
            <li class="active"><a href="#tab_1" data-toggle="tab">Información del lote</a></li>
          </ul>
          <div class="tab-content" style="padding-bottom:11%;">
            <div class="tab-pane active" id="tab_1">
              <div class="box-body">
                <form action="../../cfg/editar/editar_lote.php" id="formularioLote" class="form-group" method="POST" novalidate>
                  <?php
                  include("../../cfg/conexion.php");
                  $lotes_id = $_GET['Id'] ?? 0;

                  // 1. Obtener datos base del lote
                  $sql = "SELECT lm.Id AS Id_lote, lm.Id_proveedor, lm.Lote AS nombre_lote, 
                          lm.fecha_fabricacion, lm.fecha_vencimiento, lm.estado_lote,
                          SUM(ex.cantidad_actual) AS existencia_total
                          FROM lotes_medicamentos lm
                          LEFT JOIN existencias_stock ex ON lm.Id = ex.Id_lote
                          WHERE lm.Id = " . $lotes_id . " GROUP BY lm.Id";
                  $resultado = $conexion->query($sql);
                  $row = $resultado->fetch_assoc();

                  // 2. Obtener medicamentos asociados a este lote (agrupados)
                  $medicamentos_asociados = [];
                  $sql_meds = "SELECT m.Id_medicamento, m.nombre_medicamento, ex.cantidad_actual 
                               FROM lotes_medicamentos lm
                               JOIN descripcion_medicamento dm ON lm.Id_descripcion_medicamento = dm.Id
                               JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
                               JOIN existencias_stock ex ON lm.Id = ex.Id_lote
                               WHERE lm.Id = " . $lotes_id;
                  $res_meds = $conexion->query($sql_meds);
                  while ($m = $res_meds->fetch_assoc()) {
                    $medicamentos_asociados[] = [
                      'id' => $m['Id_medicamento'],
                      'nombre' => $m['nombre_medicamento'],
                      'stock' => $m['cantidad_actual']
                    ];
                  }

                  $tiene_medicamentos = count($medicamentos_asociados) > 0;
                  $nombres_meds = array_column($medicamentos_asociados, 'nombre');
                  $tooltip_meds = $tiene_medicamentos ? implode(', ', $nombres_meds) : 'Sin medicamentos asociados';
                  ?>

                  <input type="hidden" name="Id" value="<?= $row['Id_lote']; ?>">

                  <!-- Nombre del Lote -->
                  <div class="col-sm-4 form-group" id="group_nombre">
                    <p>Nombre del lote (*):</p>
                    <input type="text" class="form-control" name="nombre_lote" id="nombre_lote" value="<?php echo $row['nombre_lote'] ?>" required>
                  </div>

                  <!-- Fecha Fabricación -->
                  <div class="col-sm-4 form-group" id="group_fabricacion">
                    <p>F. Fabricación (*):</p>
                    <input type="date" class="form-control" id="fecha_fabricacion" name="fecha_fabricacion" value="<?php echo $row['fecha_fabricacion'] ?>" required>
                  </div>

                  <!-- Fecha Vencimiento -->
                  <div class="col-sm-4 form-group" id="group_vencimiento">
                    <p>F. Vencimiento (*):</p>
                    <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento" min="<?php echo date('Y-m-d'); ?>" value="<?php echo $row['fecha_vencimiento'] ?>" required>
                  </div>

                  <br><br><br><br><br>

                  <!-- Estado del Lote -->
                  <div class="col-sm-4 form-group">
                    <p>Estado del Lote (*):</p>
                    <select name="estado_lote" id="estado_lote" class="form-control">
                      <option value="Disponible" <?php echo ($row['estado_lote'] == 'Disponible' ? 'selected' : ''); ?>>Disponible</option>
                      <option value="Cuarentena" <?php echo ($row['estado_lote'] == 'Cuarentena' ? 'selected' : ''); ?>>Cuarentena</option>
                      <option value="Vencido" <?php echo ($row['estado_lote'] == 'Vencido' ? 'selected' : ''); ?>>Vencido</option>
                      <option value="Retirado" <?php echo ($row['estado_lote'] == 'Retirado' ? 'selected' : ''); ?>>Retirado</option>
                      <option value="Dañado" <?php echo ($row['estado_lote'] == 'Dañado' ? 'selected' : ''); ?>>Dañado</option>
                    </select>
                  </div>

                  <!-- Existencia Total (Solo Lectura) - REUBICADA -->
                  <div class="col-sm-4 form-group" id="group_existencia">
                    <p>Existencia Total (Stock):</p>
                    <input type="text" class="form-control" id="existencia_actual" value="<?php echo $row['existencia_total'] ?>" disabled style="background-color: #eeeeee; font-weight: bold; color: #d9534f;">
                  </div>

                  <!-- Medicamentos Asociados (Nuevo UI con Modal) -->
                  <div class="col-sm-4 form-group">
                    <p>Medicamentos Asociados:</p>
                    <span id="wrap_btn_modal_meds_lote" title="<?= htmlspecialchars($tooltip_meds) ?>" style="display: block; cursor: help;">
                      <button type="button" class="btn btn-info btn-block" id="btn_modal_meds_lote" data-toggle="modal" data-target="#modalMedicamentosLote">
                        <i class="fa fa-medkit"></i> Gestionar Medicamentos (<span id="count_meds_lote"><?php echo count($medicamentos_asociados); ?></span>)
                      </button>
                    </span>
                    <input type="hidden" name="medicamentos_data" id="medicamentos_ids" value="<?php echo htmlspecialchars(json_encode($medicamentos_asociados)); ?>">
                  </div>

                  <br><br><br><br><br>

                  <!-- Proveedor -->
                  <div class="col-sm-4 form-group" id="group_proveedor">
                    <p>Proveedor (*):</p>
                    <select id="proveedor" name="proveedor" class="form-control" required>
                      <option value="">--- Seleccione un proveedor ---</option>
                      <?php
                      $sql_proveedor = "SELECT Id_proveedor, nombre_proveedor FROM proveedor ORDER BY nombre_proveedor ASC";
                      $resultado_proveedor = $conexion->query($sql_proveedor);
                      if ($resultado_proveedor && $resultado_proveedor->num_rows > 0) {
                        while ($row_pro = $resultado_proveedor->fetch_assoc()) {
                          $selected = ($row_pro['Id_proveedor'] == $row['Id_proveedor']) ? 'selected' : '';
                          echo '<option value="' . $row_pro['Id_proveedor'] . '" ' . $selected . '>' . htmlspecialchars($row_pro['nombre_proveedor']) . '</option>';
                        }
                      }
                      ?>
                    </select>
                  </div>

                  <div class="col-sm-12">
                    <div style="float:right; margin-top: 2%;">
                      <button type="button" class="btn btn-second" data-toggle="modal" data-target="#modalRegresar">Regresar</button>
                      <button type="submit" class="btn btn-success" id="btnGuardar">Actualizar</button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- MODAL GESTIONAR MEDICAMENTOS -->
  <div class="modal fade" id="modalMedicamentosLote" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h4 class="modal-title"><i class="fa fa-medkit"></i> Gestionar Medicamentos del Lote</h4>
        </div>
        <div class="modal-body">
          <?php if ($tiene_medicamentos) : ?>
            <div class="alert alert-info">
              <i class="fa fa-info-circle"></i> Este lote ya tiene medicamentos asignados. Por seguridad, no se permite agregar, eliminar ni modificar los medicamentos existentes.
            </div>
          <?php else : ?>
            <p class="text-muted">Agregue los medicamentos que contendrá este lote. Una vez guardado, no podrá modificarlos aquí.</p>
          <?php endif; ?>

          <div id="contenedor-medicamentos-edicion">
            <!-- Aquí se cargan dinámicamente las filas -->
          </div>

          <?php if (!$tiene_medicamentos) : ?>
            <div style="margin-top: 10px;">
              <button type="button" class="btn btn-success btn-sm" id="add_fila_med_lote"><i class="fa fa-plus"></i> Añadir otro medicamento</button>
            </div>
          <?php endif; ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" id="cancelar_med_modal">Cancelar</button>
          <button type="button" class="btn btn-primary" id="guardar_med_listo">Listo</button>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL BUSCAR MEDICAMENTO -->
  <div class="modal fade" id="modalBuscarMedicamentoLote" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h4 class="modal-title"><i class="fa fa-search"></i> Buscar Medicamento</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Buscar por Nombre o ID:</label>
            <input type="text" id="inputBuscarMedLote" class="form-control" placeholder="Escriba el nombre del medicamento..." autocomplete="off">
          </div>
          <div class="list-group" id="listaResultadosMedLote" style="margin-top: 10px; max-height: 300px; overflow-y: auto;">
            <div class="text-muted text-center" style="padding: 15px;">Escriba para buscar medicamentos...</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- MODALES DE AVISO Y GUARDAR (Mantenidos) -->
  <div class="modal fade" id="avisoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h5 class="modal-title">Aviso Importante</h5>
        </div>
        <div class="modal-body">
          <p id="avisoTexto"></p>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button></div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalRegresar" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h4 class="modal-title">Confirmación de Regreso</h4>
        </div>
        <div class="modal-body">
          <p>Al hacer clic en "Abandonar Formulario", perderá todos los datos no guardados. ¿Desea continuar?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <a href="farmacia_lotes_listado.php" class="btn btn-danger">Abandonar Formulario</a>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalGuardar" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-green">
          <h4 class="modal-title">Confirmación de Guardado</h4>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea actualizar la información del lote?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-success" id="confirmarGuardar">Aceptar</button>
        </div>
      </div>
    </div>
  </div>

  <?php include('includes/footer.php'); ?>

  <script>
    window.LISTA_MEDICAMENTOS_DISPONIBLES = <?php
                                            $lista_meds = [];
                                            $q_all_meds = $conexion->query("SELECT m.Id_medicamento, m.nombre_medicamento FROM medicamento m WHERE m.estatus = 1 ORDER BY m.nombre_medicamento ASC");
                                            if ($q_all_meds) {
                                              while ($m = $q_all_meds->fetch_assoc()) {
                                                $lista_meds[] = ['id' => $m['Id_medicamento'], 'nombre' => $m['nombre_medicamento']];
                                              }
                                            }
                                            echo json_encode($lista_meds);
                                            ?>;
  </script>

  <script>
    (function() {
      'use strict';
      if (window.__editarLoteInicializado) return;
      window.__editarLoteInicializado = true;

      const today = new Date().toISOString().split('T')[0];
      let filaMedDestino = null;
      let tieneMedicamentosCargados = <?php echo $tiene_medicamentos ? 'true' : 'false'; ?>;

      // Inicializar tooltip del botón de medicamentos
      $('#wrap_btn_modal_meds_lote').tooltip({
        container: 'body',
        placement: 'top'
      });

      function mostrarAviso(msg) {
        $('#avisoTexto').html(msg);
        $('#avisoModal').modal('show');
      }

      function limpiarErrores() {
        $('input, select').removeClass('input-error');
      }

      // Lógica para bloquear fechas
      $('#fecha_fabricacion').attr('max', today);
      $('#fecha_vencimiento').prop('disabled', true);

      if ($('#fecha_fabricacion').val()) {
        $('#fecha_vencimiento').prop('disabled', false).attr('min', $('#fecha_fabricacion').val());
      }

      $('#fecha_fabricacion').on('change', function() {
        const fabDate = $(this).val();
        if (fabDate) {
          if (fabDate > today) {
            mostrarAviso('La fecha de fabricación no puede ser futura.');
            $(this).val('');
            $('#fecha_vencimiento').prop('disabled', true).val('');
            return;
          }
          $('#fecha_vencimiento').prop('disabled', false).attr('min', fabDate);
          if ($('#fecha_vencimiento').val() && $('#fecha_vencimiento').val() < fabDate) {
            $('#fecha_vencimiento').val('');
          }
        } else {
          $('#fecha_vencimiento').prop('disabled', true).val('');
        }
      });

      $('#fecha_vencimiento').on('change', function() {
        const vencDate = $(this).val();
        const fabDate = $('#fecha_fabricacion').val();
        if (vencDate) {
          if (vencDate <= today) {
            mostrarAviso('La fecha de vencimiento debe ser una fecha futura (no puede ser hoy ni antes).');
            $(this).val('');
            return;
          }
          if (fabDate && vencDate < fabDate) {
            mostrarAviso('La fecha de vencimiento no puede ser anterior a la de fabricación.');
            $(this).val('');
            return;
          }
        }
      });

      // --- LÓGICA DEL MODAL DE MEDICAMENTOS ---

      function agregarFilaMedicamento(id = '', nombre = '', stock = 0) {
        let colorStock = stock > 0 ? '#d9534f' : '#777';
        let addonStock = `<span class="input-group-addon stock-badge" style="background-color: ${colorStock}; color: white;">Stock: ${stock}</span>`;

        let htmlBotones = '';
        if (!tieneMedicamentosCargados) {
          // Si NO hay datos, mostramos botones de buscar y eliminar
          htmlBotones = `
                <span class="input-group-btn">
                  <button class="btn btn-info btn-search-med-lote" type="button" title="Buscar Medicamento">
                    <i><img src="../../recursos/imagenes/iconos/buscar.png" style="width:15px; height:15px;"></i>
                  </button>
                </span>
                <span class="input-group-btn">
                  <button type="button" class="btn btn-danger btn-remove-med-lote" title="Eliminar">
                    <i><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></i>
                  </button>
                </span>
            `;
        } else {
          // Si YA hay datos, mostramos un candado bloqueado
          htmlBotones = `
                <span class="input-group-btn">
                  <button class="btn btn-second" type="button" disabled title="No editable en lote existente">
                    <i><img src="../../recursos/imagenes/iconos/pass_b.png" style="width:15px; height:15px;"></i>
                  </button>
                </span>
            `;
        }

        let html = `
          <div class="row fila-med-lote" style="margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px dashed #ddd;">
            <div class="col-sm-12">
              <div class="input-group">
                <input type="text" class="form-control input-nombre-med-lote" placeholder="--- Seleccione un medicamento ---" value="${nombre}" readonly style="background:#fff;">
                <input type="hidden" class="input-id-med-lote" value="${id}">
                <input type="hidden" class="input-stock-med-lote" value="${stock}">
                ${addonStock}
                ${htmlBotones}
              </div>
            </div>
          </div>`;
        $('#contenedor-medicamentos-edicion').append(html);
      }

      function cargarMedicamentosGuardados() {
        $('#contenedor-medicamentos-edicion').empty();
        let dataGuardada = $('#medicamentos_ids').val();
        if (dataGuardada && dataGuardada.trim() !== "" && dataGuardada !== "[]") {
          try {
            let lista = JSON.parse(dataGuardada);
            lista.forEach(function(m) {
              agregarFilaMedicamento(m.id, m.nombre, m.stock);
            });
            $('#count_meds_lote').text(lista.length);
          } catch (e) {
            console.error(e);
          }
        } else {
          $('#count_meds_lote').text(0);
        }
      }

      $('#modalMedicamentosLote').on('show.bs.modal', function() {
        cargarMedicamentosGuardados();
      });

      $(document).on('click', '.btn-search-med-lote', function(e) {
        e.preventDefault();
        if (tieneMedicamentosCargados) return; // Bloqueo interno

        filaMedDestino = $(this).closest('.fila-med-lote');
        $('#inputBuscarMedLote').val('');
        $('#listaResultadosMedLote').html('<div class="text-muted text-center" style="padding:15px;">Escriba para buscar...</div>');
        $('#modalBuscarMedicamentoLote').modal('show');
        $('#inputBuscarMedLote').focus();
      });

      let timerMed = null;
      $(document).on('keyup', '#inputBuscarMedLote', function() {
        if (tieneMedicamentosCargados) return;

        let term = $(this).val().trim().toLowerCase();
        if (timerMed) clearTimeout(timerMed);

        let lista = window.LISTA_MEDICAMENTOS_DISPONIBLES || [];
        if (term.length === 0) {
          $('#listaResultadosMedLote').html('<div class="text-muted text-center" style="padding:15px;">Escriba para buscar...</div>');
          return;
        }

        timerMed = setTimeout(function() {
          let html = '';
          let resultados = lista.filter(m => m.nombre.toLowerCase().includes(term) || m.id.toString() === term);

          if (resultados.length === 0) {
            $('#listaResultadosMedLote').html('<div class="text-muted text-center" style="padding:15px;">No se encontraron medicamentos.</div>');
            return;
          }

          resultados.forEach(function(m) {
            html += `<a href="#" class="list-group-item seleccionar-med-lote" data-id="${m.id}" data-nombre="${m.nombre}">
                      <strong>${m.nombre}</strong>
                    </a>`;
          });
          $('#listaResultadosMedLote').html(html);
        }, 300);
      });

      $(document).on('click', '.seleccionar-med-lote', function(e) {
        e.preventDefault();
        if (tieneMedicamentosCargados) return;

        let id = $(this).data('id');
        let nombre = $(this).data('nombre');

        let dup = false;
        $('.input-id-med-lote').each(function() {
          if ($(this).val() == id && $(this).closest('.fila-med-lote')[0] !== filaMedDestino[0]) dup = true;
        });
        if (dup) {
          mostrarAviso('⚠️ Este medicamento ya está asignado en otra fila.');
          return;
        }

        filaMedDestino.find('.input-id-med-lote').val(id);
        filaMedDestino.find('.input-nombre-med-lote').val(nombre);
        filaMedDestino.find('.input-stock-med-lote').val(0);

        // Actualizar visualmente el addon de stock
        let addon = filaMedDestino.find('.input-group-addon');
        addon.text('Stock: 0').css('color', '#777');

        $('#modalBuscarMedicamentoLote').modal('hide');
      });

      $('#add_fila_med_lote').on('click', function() {
        if (tieneMedicamentosCargados) return; // Bloqueo interno

        let $ult = $('.fila-med-lote').last();
        if ($ult.length && !$ult.find('.input-id-med-lote').val()) {
          mostrarAviso('Debe seleccionar un medicamento en la fila actual antes de añadir otro.');
          return;
        }
        agregarFilaMedicamento();
      });

      $(document).on('click', '.btn-remove-med-lote', function() {
        if (tieneMedicamentosCargados) return; // Bloqueo interno

        let $fila = $(this).closest('.fila-med-lote');
        let stock = parseInt($fila.find('.input-stock-med-lote').val()) || 0;
        if (stock > 0) {
          mostrarAviso('No puede eliminar este medicamento porque tiene stock. Ajuste el stock a 0 primero.');
          return;
        }
        $fila.remove();
      });

      $('#guardar_med_listo').on('click', function() {
        if (tieneMedicamentosCargados) {
          $('#modalMedicamentosLote').modal('hide');
          return;
        }

        let seleccionados = [];
        let count = 0;
        $('.fila-med-lote').each(function() {
          let id = $(this).find('.input-id-med-lote').val();
          let nombre = $(this).find('.input-nombre-med-lote').val();
          let stock = $(this).find('.input-stock-med-lote').val();
          if (id) {
            seleccionados.push({
              id: parseInt(id),
              nombre: nombre,
              stock: parseInt(stock)
            });
            count++;
          }
        });

        $('#medicamentos_ids').val(JSON.stringify(seleccionados));
        $('#count_meds_lote').text(count);

        // Actualizar el tooltip del botón principal
        let nombres = seleccionados.map(m => m.nombre);
        let tooltipTxt = nombres.length > 0 ? nombres.join(', ') : 'Sin medicamentos asociados';
        $('#wrap_btn_modal_meds_lote').attr('title', tooltipTxt).tooltip('fixTitle');

        $('#modalMedicamentosLote').modal('hide');
      });

      $('#cancelar_med_modal').on('click', function() {
        cargarMedicamentosGuardados();
        $('#modalMedicamentosLote').modal('hide');
      });

      // --- LÓGICA DE GUARDADO ---

      function verificarLoteYMostrarModal() {
        const nombre = $('#nombre_lote').val().trim();
        const id_lote = $('input[name="Id"]').val();
        const btnGuardar = $('#btnGuardar');

        const textoOriginal = btnGuardar.text();
        btnGuardar.text('Verificando...').attr('disabled', true);

        $.ajax({
          url: '../../cfg/ajax/verificar_existencia_lote.php',
          method: 'POST',
          dataType: 'json',
          data: {
            nombre: nombre,
            id_lote: id_lote,
            solo_nombre: true
          },
          success: function(response) {
            btnGuardar.text(textoOriginal).attr('disabled', false);
            limpiarErrores();

            if (response.existe_lote) {
              $('#group_nombre').addClass('input-error');
              $('#nombre_lote').addClass('input-error');
              mostrarAviso('⚠️ Error de Duplicidad:<br>Ya existe OTRO lote con el nombre <b>' + nombre + '</b> registrado en el sistema.');
            } else if (response.error) {
              mostrarAviso('⚠️ Error: ' + response.mensaje);
            } else {
              $('#modalGuardar').modal('show');
            }
          },
          error: function() {
            btnGuardar.text(textoOriginal).attr('disabled', false);
            mostrarAviso('⚠️ Error de Servidor: No se pudo verificar la base de datos.');
          }
        });
      }

      $('#formularioLote').on('submit', function(e) {
        e.preventDefault();
        limpiarErrores();
        let errores = [];

        if ($('#nombre_lote').val().trim() === "") {
          errores.push("El nombre del lote no puede estar vacío.");
          $('#nombre_lote').addClass('input-error');
        }
        if ($('#fecha_fabricacion').val().trim() === "") {
          errores.push("La fecha de fabricación no puede estar vacía.");
          $('#fecha_fabricacion').addClass('input-error');
        }
        if ($('#fecha_vencimiento').val().trim() === "") {
          errores.push("La fecha de vencimiento no puede estar vacía.");
          $('#fecha_vencimiento').addClass('input-error');
        }
        if ($('#proveedor').val().trim() === "") {
          errores.push("Debe asignar un proveedor a este lote.");
          $('#proveedor').addClass('input-error');
        }

        if (errores.length > 0) {
          mostrarAviso('⚠️ Errores: <ul><li>' + errores.join('</li><li>') + '</li></ul>');
        } else {
          verificarLoteYMostrarModal();
        }
      });

      $('#confirmarGuardar').on('click', function() {
        $('#formularioLote').off('submit').submit();
      });
    })();
  </script>
</div>