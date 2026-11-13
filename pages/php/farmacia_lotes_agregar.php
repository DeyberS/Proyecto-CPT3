<?php
include('includes/headerNav2.php');
verificarAcceso('Crear Lotes');
?>

<head>
  <title>Lotes | Añadir</title>
</head>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Añadir Lote</h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="#"><i class="fa fa-users"></i>Lotes</a></li>
      <li class="active"><a href="#"><i class="fa fa-user-plus"></i>Añadir</a></li>
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
                <form action="../../cfg/agregar/agregar_lote.php" id="formularioLote" class="form-group" method="POST" novalidate>

                  <div class="col-sm-4 form-group" id="group_nombre">
                    <p>Nombre del lote (*):</p>
                    <input type="text" class="form-control" name="nombre_lote" id="nombre_lote" required>
                  </div>

                  <div class="col-sm-4 form-group" id="group_fabricacion">
                    <p>F. Fabricación (*):</p>
                    <input type="date" class="form-control" id="fecha_fabricacion" name="fecha_fabricacion" required>
                  </div>

                  <div class="col-sm-4 form-group" id="group_vencimiento">
                    <p>F. Vencimiento (*):</p>
                    <input type="date" class="form-control" id="fecha_vencimiento" min="<?php echo date('Y-m-d'); ?>" name="fecha_vencimiento" required>
                  </div>

                  <br><br><br><br><br>

                  <div class="col-sm-4">
                    <p>Estado del Lote (*):</p>
                    <select name="estado_lote" id="estado_lote" class="form-control" disabled readonly>
                      <option value="Disponible" selected> Disponible</option>
                    </select>
                  </div>

                  <div class="col-sm-4 form-group" id="group_medicamento">
                    <p>Medicamento (*):</p>
                    <select id="medicamento" name="medicamento" class="form-control" required>
                      <option value="">--- Seleccione un medicamento ---</option>
                      <?php
                      include("../../cfg/conexion.php");
                      $sql_medicamentos = "SELECT 
                          dm.Id AS id_desc, 
                          m.nombre_medicamento
                          FROM descripcion_medicamento dm
                          INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
                          WHERE m.estatus = 1 AND dm.estatus = 1
                          ORDER BY m.nombre_medicamento ASC";
                      $resultado_medicamentos = $conexion->query($sql_medicamentos);

                      if ($resultado_medicamentos && $resultado_medicamentos->num_rows > 0) {
                        while ($row_med = $resultado_medicamentos->fetch_assoc()) {
                          echo '<option value="' . $row_med['id_desc'] . '">' . htmlspecialchars($row_med['nombre_medicamento']) . '</option>';
                        }
                      }
                      ?>
                    </select>
                  </div>

                  <div class="col-sm-4 form-group" id="group_proveedor">
                    <p>Proveedor (*):</p>
                    <select id="proveedor" name="proveedor" class="form-control" required>
                      <option value="">--- Seleccione un proveedor ---</option>
                      <?php
                      include("../../cfg/conexion.php");
                      $sql_proveedor = "SELECT Id_proveedor, nombre_proveedor 
                                      FROM proveedor 
                                      ORDER BY nombre_proveedor ASC";

                      $resultado_proveedor = $conexion->query($sql_proveedor);

                      if ($resultado_proveedor && $resultado_proveedor->num_rows > 0) {
                        while ($row_pro = $resultado_proveedor->fetch_assoc()) {
                          echo '<option value="' . $row_pro['Id_proveedor'] . '">' . htmlspecialchars($row_pro['nombre_proveedor']) . '</option>';
                        }
                      }
                      ?>
                    </select>
                  </div>

                  <br>

                  <div style="float:right; margin-top: 2%;">
                    <button type="button" class="btn btn-second" data-toggle="modal" data-target="#modalRegresar">Regresar</button>
                    <button type="submit" class="btn btn-success" id="btnGuardar">Guardar</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

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
          <p>¿Está seguro de que desea guardar la informacion del nuevo lote?</p>
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
    $(document).ready(function() {
      const formulario = $('#formularioLote');
      const fechaFabricacionInput = $('#fecha_fabricacion');
      const fechaVencimientoInput = $('#fecha_vencimiento');

      $('#fecha_fabricacion, #fecha_vencimiento').on('keydown', function(e) {
        e.preventDefault();
      });

      function mostrarAviso(mensaje) {
        $('#avisoTexto').html(mensaje);
        $('#avisoModal').modal('show');
      }

      function limpiarErrores() {
        $('input, select').removeClass('input-error');
        $('.form-group').removeClass('input-error');
      }

      const today = new Date().toISOString().split('T')[0];
      fechaFabricacionInput.attr('max', today);

      fechaFabricacionInput.on('change', function() {
        const fabricacionDate = $(this).val();
        if (fabricacionDate) {
          fechaVencimientoInput.prop('disabled', false).attr('min', fabricacionDate);
        } else {
          fechaVencimientoInput.prop('disabled', true).val('').removeAttr('min');
        }
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
      });

      function verificarLoteYMostrarModal() {
        const nombre = $('#nombre_lote').val().trim();
        const id_medicamento = $('#medicamento').val();
        const btnGuardar = $('#btnGuardar');

        const textoOriginal = btnGuardar.text();
        btnGuardar.text('Verificando...').attr('disabled', true);

        $.ajax({
          url: '../../cfg/ajax/verificar_existencia_lote.php',
          method: 'POST',
          dataType: 'json',
          data: {
            nombre: nombre,
            id_medicamento: id_medicamento
          },
          success: function(response) {
            btnGuardar.text(textoOriginal).attr('disabled', false);
            limpiarErrores();

            if (response.existe_lote) {
              $('#nombre_lote').addClass('input-error');
              mostrarAviso('⚠️ Error de Duplicidad:<br>Ya existe un lote con el nombre <b>' + nombre + '</b> asignado a este mismo medicamento.');
            } else if (response.error) {
              mostrarAviso('⚠️ Error: ' + response.mensaje);
            } else {
              $('#modalGuardar').modal('show');
            }
          },
          error: function(xhr, status, error) {
            btnGuardar.text(textoOriginal).attr('disabled', false);
            mostrarAviso('⚠️ Error de Servidor: No se pudo verificar la base de datos. <br>Detalle: ' + error);
          }
        });
      }

      formulario.on('submit', function(e) {
        e.preventDefault();
        limpiarErrores();
        let errores = [];

        if ($('#nombre_lote').val().trim() === "") {
          errores.push("El nombre del lote no puede estar vacio.");
          $('#nombre_lote').addClass('input-error');
        }
        if ($('#fecha_fabricacion').val().trim() === "") {
          errores.push("La fecha de fabricacion no puede estar vacia.");
          $('#fecha_fabricacion').addClass('input-error');
        }
        if ($('#fecha_vencimiento').val().trim() === "") {
          errores.push("La fecha de vencimiento no puede estar vacia.");
          $('#fecha_vencimiento').addClass('input-error');
        }
        if ($('#medicamento').val().trim() === "") {
          errores.push("Debe asignar un medicamento a este lote.");
          $('#medicamento').addClass('input-error');
        }
        if ($('#proveedor').val().trim() === "") {
          errores.push("Debe asignar un proveedor a este lote.");
          $('#proveedor').addClass('input-error');
        }

        if (errores.length > 0) {
          mostrarAviso('⚠️ Errores locales: <ul><li>' + errores.join('</li><li>') + '</li></ul>');
        } else {
          verificarLoteYMostrarModal();
        }
      });

      $('#confirmarGuardar').on('click', function() {
        $('#formularioLote').off('submit').submit();
      });
    });
  </script>