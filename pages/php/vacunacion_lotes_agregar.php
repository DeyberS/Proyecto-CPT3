<?php
include('includes/headerNav2.php');
verificarAcceso('Crear Lotes'); // O 'Gestionar Vacunas'
?>

<head>
  <title>Lotes de Vacunas | Añadir</title>
</head>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Añadir Lote de Vacuna</h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="#"><i class="fa fa-medkit"></i>Inmunización</a></li>
      <li><a href="vacunacion_lotes_listado.php"><i class="fa fa-archive"></i>Lotes</a></li>
      <li class="active"><a href="#"><i class="fa fa-plus"></i>Añadir</a></li>
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
                    <p>Nombre del Lote (*):</p>
                    <input type="text" class="form-control" name="nombre_lote" id="nombre_lote" placeholder="Ej: VPI-001" style="text-transform: uppercase;" required>
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

                  <div class="col-sm-4 form-group" id="group_vacuna">
                    <p>Vacuna / Biológico (*):</p>
                    <select id="medicamento" name="medicamento" class="form-control" required>
                      <option value="">--- Seleccione una Vacuna ---</option>
                      <!-- SIMULACIÓN: Aquí irían las vacunas de la BD -->
                      <option value="1">Antipoliomielítica (VPI)</option>
                      <option value="2">Antisarampión (SRP)</option>
                      <option value="3">Vacuna BCG</option>
                      <option value="4">Antiamarílica (Fiebre Amarilla)</option>
                    </select>
                  </div>

                  <div class="col-sm-4 form-group" id="group_proveedor">
                    <p>Proveedor / Donante (*):</p>
                    <select id="proveedor" name="proveedor" class="form-control" required>
                      <option value="">--- Seleccione un proveedor ---</option>
                      <!-- SIMULACIÓN -->
                      <option value="1">SUAF Portuguesa</option>
                      <option value="2">Ministerio de Salud</option>
                      <option value="3">OPS / OMS</option>
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

  <!-- MODAL AVISO -->
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

  <!-- MODAL REGRESAR -->
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
          <a href="vacunacion_lotes_listado.php" class="btn btn-danger">Abandonar Formulario</a>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL GUARDAR -->
  <div class="modal fade" id="modalGuardar" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-green">
          <h4 class="modal-title">Confirmación de Guardado</h4>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea guardar la información del nuevo lote de vacunas?</p>
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

      // Bloquear escritura manual en fechas
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

      // SIMULACIÓN DE VERIFICACIÓN DE DUPLICIDAD (En lugar de AJAX)
      function verificarLoteYMostrarModal() {
        const nombre = $('#nombre_lote').val().trim();
        const id_vacuna = $('#medicamento').val();
        const btnGuardar = $('#btnGuardar');

        const textoOriginal = btnGuardar.text();
        btnGuardar.text('Verificando...').attr('disabled', true);

        setTimeout(function() {
          btnGuardar.text(textoOriginal).attr('disabled', false);
          limpiarErrores();

          // Simulamos que "VPI-001" ya existe para probar la validación
          if (nombre.toUpperCase() === 'VPI-001' && id_vacuna == '1') {
            $('#nombre_lote').addClass('input-error');
            mostrarAviso('⚠️ Error de Duplicidad:<br>Ya existe un lote con el nombre <b>' + nombre + '</b> asignado a esta misma vacuna.');
          } else {
            $('#modalGuardar').modal('show');
          }
        }, 800);
      }

      formulario.on('submit', function(e) {
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
        if ($('#medicamento').val().trim() === "") {
          errores.push("Debe asignar una vacuna a este lote.");
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
        // SIMULACIÓN: Aquí se enviaría el formulario real
        // $('#formularioLote').off('submit').submit();
        $('#modalGuardar').modal('hide');
        mostrarAviso("✅ <b>Lote de Vacuna registrado (Simulado).</b><br><br>En la versión real, esto guardaría el lote en la base de datos.");
        setTimeout(function() {
          window.location.href = 'vacunacion_lotes_listado.php';
        }, 2500);
      });
    });
  </script>
</div>