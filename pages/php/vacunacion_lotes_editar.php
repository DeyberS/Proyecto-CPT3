<?php
if (!isset($_GET['Id']) || empty($_GET['Id'])) {
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }
  $_SESSION['mensaje_user_error'] = "Debe seleccionar un lote válido para editar su información.";
  header("Location: vacunacion_lotes_listado.php");
  exit();
}

$id_lote = $_GET['Id'];

include('includes/headerNav2.php');
verificarAcceso('Editar Lotes'); // O 'Gestionar Vacunas'
?>

<head>
  <title>Lotes de Vacunas | Editar</title>
</head>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Editar Lote de Vacuna</h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="#"><i class="fa fa-medkit"></i>Inmunización</a></li>
      <li><a href="vacunacion_lotes_listado.php"><i class="fa fa-archive"></i>Lotes</a></li>
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
                  
                  <!-- ======================================================= -->
                  <!-- SIMULACIÓN DE DATOS (BORRAR CUANDO CONECTES LA BD REAL) -->
                  <!-- ======================================================= -->
                  <?php
                  // En la versión real, aquí iría tu consulta SQL. Por ahora, quemamos los datos.
                  $row = [
                      'Id_lote' => $id_lote,
                      'nombre_lote' => 'VPI-001',
                      'fecha_fabricacion' => '2025-01-01',
                      'fecha_vencimiento' => '2026-01-01',
                      'estado_lote' => 'Disponible',
                      'Id_medicamento' => '1',
                      'Id_proveedor' => '1',
                      'existencia' => 150
                  ];
                  ?>

                  <input type="hidden" name="Id" value="<?= $row['Id_lote']; ?>">

                  <div class="col-sm-4 form-group" id="group_nombre">
                    <p>Nombre del Lote (*):</p>
                    <input type="text" class="form-control" name="nombre_lote" id="nombre_lote" value="<?php echo $row['nombre_lote'] ?>" style="text-transform: uppercase;" required>
                  </div>

                  <div class="col-sm-4 form-group" id="group_fabricacion">
                    <p>F. Fabricación (*):</p>
                    <input type="date" class="form-control" id="fecha_fabricacion" name="fecha_fabricacion" value="<?php echo $row['fecha_fabricacion'] ?>" required>
                  </div>

                  <div class="col-sm-4 form-group" id="group_vencimiento">
                    <p>F. Vencimiento (*):</p>
                    <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento" min="<?php echo date('Y-m-d'); ?>" value="<?php echo $row['fecha_vencimiento'] ?>" required>
                  </div>

                  <br><br><br><br><br>

                  <div class="col-sm-4">
                    <p>Estado del Lote (*):</p>
                    <select name="estado_lote" id="estado_lote" class="form-control">
                      <option value="Disponible" <?php echo ($row['estado_lote'] == 'Disponible' ? 'selected' : ''); ?>>Disponible</option>
                      <option value="Cuarentena" <?php echo ($row['estado_lote'] == 'Cuarentena' ? 'selected' : ''); ?>>Cuarentena</option>
                      <option value="Vencido" <?php echo ($row['estado_lote'] == 'Vencido' ? 'selected' : ''); ?>>Vencido</option>
                      <option value="Retirado" <?php echo ($row['estado_lote'] == 'Retirado' ? 'selected' : ''); ?>>Retirado</option>
                      <option value="Dañado" <?php echo ($row['estado_lote'] == 'Dañado' ? 'selected' : ''); ?>>Dañado (Cadena de Frío)</option>
                    </select>
                  </div>

                  <div class="col-sm-4 form-group" id="group_medicamento">
                    <p>Vacuna / Biológico (*):</p>
                    <select id="medicamento" name="medicamento" class="form-control" required>
                      <option value="">--- Seleccione una Vacuna ---</option>
                      <!-- SIMULACIÓN: Aquí irían las vacunas de la BD -->
                      <option value="1" <?php echo ($row['Id_medicamento'] == '1' ? 'selected' : ''); ?>>Antipoliomielítica (VPI)</option>
                      <option value="2" <?php echo ($row['Id_medicamento'] == '2' ? 'selected' : ''); ?>>Antisarampión (SRP)</option>
                      <option value="3" <?php echo ($row['Id_medicamento'] == '3' ? 'selected' : ''); ?>>Vacuna BCG</option>
                      <option value="4" <?php echo ($row['Id_medicamento'] == '4' ? 'selected' : ''); ?>>Antiamarílica (Fiebre Amarilla)</option>
                    </select>
                  </div>

                  <div class="col-sm-4 form-group" id="group_proveedor">
                    <p>Proveedor / Donante (*):</p>
                    <select id="proveedor" name="proveedor" class="form-control" required>
                      <option value="">--- Seleccione un proveedor ---</option>
                      <!-- SIMULACIÓN -->
                      <option value="1" <?php echo ($row['Id_proveedor'] == '1' ? 'selected' : ''); ?>>SUAF Portuguesa</option>
                      <option value="2" <?php echo ($row['Id_proveedor'] == '2' ? 'selected' : ''); ?>>Ministerio de Salud</option>
                      <option value="3" <?php echo ($row['Id_proveedor'] == '3' ? 'selected' : ''); ?>>OPS / OMS</option>
                    </select>
                  </div>

                  <div class="col-sm-4 form-group" id="group_existencia">
                    <label>Existencia (Actual en Dosis):</label>
                    <input type="text" class="form-control" id="existencia_actual" name="existencia_actual" value="<?php echo $row['existencia'] ?>" disabled>
                  </div>

                  <br>

                  <div style="float:right; margin-top: 2%;">
                    <button type="button" class="btn btn-second" data-toggle="modal" data-target="#modalRegresar">Regresar</button>
                    <button type="submit" class="btn btn-success" id="btnGuardar">Actualizar</button>
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
          <p>¿Está seguro de que desea actualizar la información del lote de vacuna?</p>
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

      // SIMULACIÓN DE VERIFICACIÓN DE DUPLICIDAD
      function verificarLoteYMostrarModal() {
        const nombre = $('#nombre_lote').val().trim();
        const id_medicamento = $('#medicamento').val();
        const id_lote = $('input[name="Id"]').val();
        const btnGuardar = $('#btnGuardar');

        const textoOriginal = btnGuardar.text();
        btnGuardar.text('Verificando...').attr('disabled', true);

        setTimeout(function() {
          btnGuardar.text(textoOriginal).attr('disabled', false);
          limpiarErrores();

          // Simulamos que "VPI-002" ya existe para probar la validación
          if (nombre.toUpperCase() === 'VPI-002' && id_medicamento == '1') {
            $('#group_nombre').addClass('input-error');
            $('#nombre_lote').addClass('input-error');
            mostrarAviso('⚠️ Error de Duplicidad:<br>Ya existe OTRO lote con el nombre <b>' + nombre + '</b> asignado a esta misma vacuna.');
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
          mostrarAviso('⚠️ Errores: <ul><li>' + errores.join('</li><li>') + '</li></ul>');
        } else {
          verificarLoteYMostrarModal();
        }
      });

      $('#confirmarGuardar').on('click', function() {
        // SIMULACIÓN: Aquí se enviaría el formulario real
        // $('#formularioLote').off('submit').submit();
        $('#modalGuardar').modal('hide');
        mostrarAviso("✅ <b>Lote de Vacuna actualizado (Simulado).</b><br><br>En la versión real, esto guardaría los cambios en la base de datos.");
        setTimeout(function() {
          window.location.href = 'vacunacion_lotes_listado.php';
        }, 2500);
      });
    });
  </script>
</div>