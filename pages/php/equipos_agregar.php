<?php
include('includes/headerNav2.php');
verificarAcceso('Gestionar Equipos');
?>

<head>
  <title>Inventario de Equipos | Añadir</title>
</head>

<div class="content-wrapper">
  <style>
    .form-group {
      margin-bottom: 15px;
    }
  </style>

  <section class="content-header">
    <h1>Añadir Nuevo Equipo</h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i> Inicio</a></li>
      <li><a href="equipos_listado.php"><i class="fa fa-desktop"></i> Inventario de Equipos</a></li>
      <li class="active"><a href="#"><i class="fa fa-plus"></i> Añadir</a></li>
    </ol>
  </section>

  <section class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="nav-tabs-custom">
          <ul class="nav nav-tabs">
            <li class="active"><a href="#tab_1" data-toggle="tab">Información del Equipo</a></li>
          </ul>
          <div class="tab-content">
            <div class="tab-pane active" id="tab_1" style="margin-bottom: 5%;">
              <div class="box-body">
                <form id="formularioEquipo" action="../../cfg/agregar/agregar_equipo.php" method="POST" novalidate>

                  <!-- FILA 1: DESCRIPCIÓN Y CATEGORÍA -->
                  <div class="row">
                    <div class="col-sm-8 form-group" id="group_descripcion">
                      <p>Descripción del Equipo (*):</p>
                      <input type="text" name="descripcion" id="descripcion" class="form-control" placeholder="Ej: Silla tipo ejecutiva, Nevera, Escritorio..." maxlength="200" required>
                    </div>
                    <div class="col-sm-4 form-group" id="group_categoria">
                      <p>Categoría (*):</p>
                      <select name="id_categoria" id="id_categoria" class="form-control" required>
                        <option value="">--- Seleccione la Categoría ---</option>
                        <option value="1">Mobiliario</option>
                        <option value="2">Equipo Electrónico</option>
                        <option value="3">Equipo Médico</option>
                        <option value="4">Equipo de Oficina</option>
                        <option value="5">Vehículo</option>
                        <option value="6">Otro</option>
                      </select>
                    </div>
                  </div>

                  <!-- FILA 2: MARCA Y MODELO -->
                  <div class="row">
                    <div class="col-sm-4 form-group" id="group_marca">
                      <p>Marca:</p>
                      <input type="text" name="marca" id="marca" class="form-control" placeholder="Ej: Haier" maxlength="100">
                    </div>
                    <div class="col-sm-4 form-group" id="group_modelo">
                      <p>Modelo:</p>
                      <input type="text" name="modelo" id="modelo" class="form-control" placeholder="Ej: HT-200" maxlength="100">
                    </div>
                    <div class="col-sm-4 form-group" id="group_serial">
                      <p>Serial / Color:</p>
                      <input type="text" name="serial_color" id="serial_color" class="form-control" placeholder="Ej: Blanco, Serial 12345" maxlength="100">
                    </div>
                  </div>

                  <!-- FILA 3: CANTIDAD, UBICACIÓN Y N° BIEN -->
                  <div class="row">
                    <div class="col-sm-4 form-group" id="group_cantidad">
                      <p>Cantidad (*):</p>
                      <input type="number" name="cantidad" id="cantidad" class="form-control" value="1" min="1" required>
                    </div>
                    <div class="col-sm-4 form-group" id="group_ubicacion">
                      <p>Ubicación (*):</p>
                      <input type="text" name="ubicacion" id="ubicacion" class="form-control" placeholder="Ej: Consultorio 1, Oficina, Pasillo..." maxlength="150" required>
                    </div>
                    <div class="col-sm-4 form-group" id="group_numero_bien">
                      <p>N° de Bien Nacional:</p>
                      <input type="text" name="numero_bien" id="numero_bien" class="form-control" placeholder="Ej: 001, 002..." maxlength="50">
                    </div>
                  </div>

                  <!-- FILA 4: ESTADO INICIAL Y FECHA -->
                  <div class="row">
                    <div class="col-sm-4 form-group" id="group_estado">
                      <p>Estado Inicial (*):</p>
                      <select name="estado" id="estado" class="form-control" required>
                        <option value="Bueno">Bueno</option>
                        <option value="Regular">Regular</option>
                        <option value="Dañado">Dañado</option>
                      </select>
                    </div>
                    <div class="col-sm-4 form-group">
                      <p>Fecha de Registro (*):</p>
                      <input type="date" name="fecha_registro" id="fecha_registro" class="form-control" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                  </div>

                  <!-- BOTONES -->
                  <div style="float:right; margin-top: 20px;">
                    <button type="button" class="btn btn-second" data-toggle="modal" data-target="#modalRegresar">Regresar</button>
                    <button type="submit" class="btn btn-success" id="btnGuardar"><i class="fa fa-save"></i> Guardar Equipo</button>
                  </div>

                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- MODAL DE AVISO -->
  <div class="modal fade" id="avisoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h4 class="modal-title"><i class="fa fa-warning"></i> Aviso Importante</h4>
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

  <!-- MODAL DE REGRESAR -->
  <div class="modal fade" id="modalRegresar" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h4 class="modal-title"><i class="fa fa-sign-out"></i> Confirmación de Regreso</h4>
        </div>
        <div class="modal-body">
          <p>Al hacer clic en "Abandonar Formulario", perderá todos los datos no guardados. ¿Desea continuar?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <a href="equipos_listado.php" class="btn btn-danger">Abandonar Formulario</a>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL DE CONFIRMACIÓN DE GUARDADO -->
  <div class="modal fade" id="modalGuardar" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-green">
          <h4 class="modal-title"><i class="fa fa-check-circle"></i> Confirmación de Guardado</h4>
        </div>
        <div class="modal-body">
          <p>¿Está seguro que desea registrar este equipo en el inventario?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-success" id="confirmarGuardar">Aceptar</button>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include('includes/footer.php'); ?>

<script>
  $(document).ready(function() {
    function mostrarAviso(mensaje) {
      $('#avisoTexto').html(mensaje);
      $('#avisoModal').modal('show');
    }

    function limpiarErrores() {
      $('.form-group').removeClass('has-error');
      $('.input-error').removeClass('input-error');
    }

    // Validar el formulario
    $('#formularioEquipo').on('submit', function(e) {
      e.preventDefault();
      limpiarErrores();

      let errores = [];

      if ($('#descripcion').val().trim() === '') {
        errores.push('La descripción del equipo es obligatoria.');
        $('#descripcion').addClass('input-error');
      }

      if ($('#id_categoria').val() === '') {
        errores.push('Debe seleccionar una categoría.');
        $('#id_categoria').addClass('input-error');
      }

      if ($('#cantidad').val() === '' || parseInt($('#cantidad').val()) <= 0) {
        errores.push('La cantidad debe ser un número mayor a 0.');
        $('#cantidad').addClass('input-error');
      }

      if ($('#ubicacion').val().trim() === '') {
        errores.push('La ubicación es obligatoria.');
        $('#ubicacion').addClass('input-error');
      }

      if ($('#estado').val() === '') {
        errores.push('Debe seleccionar el estado inicial del equipo.');
        $('#estado').addClass('input-error');
      }

      if ($('#fecha_registro').val() === '') {
        errores.push('La fecha de registro es obligatoria.');
        $('#fecha_registro').addClass('input-error');
      }

      if (errores.length > 0) {
        mostrarAviso('<b>Por favor corrija los siguientes errores:</b><br><ul><li>' + errores.join('</li><li>') + '</li></ul>');
      } else {
        $('#modalGuardar').modal('show');
      }
    });

    // Confirmar guardado
    $('#confirmarGuardar').on('click', function() {
      $('#modalGuardar').modal('hide');
      $('#formularioEquipo').off('submit').submit();
    });

    // Limpiar errores al escribir
    $('.form-control').on('input change', function() {
      if ($(this).val() !== '') {
        $(this).removeClass('input-error');
      }
    });

    // Validar que la cantidad sea positiva
    $('#cantidad').on('input', function() {
      let valor = this.value;
      valor = valor.replace(/[^0-9]/g, '');
      if (valor === '' || parseInt(valor) < 1) {
        this.value = 1;
      } else {
        this.value = valor;
      }
    });
  });
</script>