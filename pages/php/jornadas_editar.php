<?php
include("../../cfg/conexion.php");

// Consultamos los ASIC o CPT3 para el desplegable de lugares
// (Ajusta el nombre de la tabla según tu BD)
$query_lugares = $conexion->query("SELECT Id_Area, nombre_area FROM areas WHERE estatus = 1 ORDER BY nombre_area ASC");

include('includes/headerNav2.php');
verificarAcceso('Gestionar Jornadas');
?>

<head>
  <title>Jornadas | Añadir</title>
  <style>
    .input-error {
      border: 2px solid crimson !important;
      box-shadow: 0 0 5px crimson !important;
      background-color: #fff5f5 !important;
    }
    .form-group {
      margin-bottom: 15px;
    }
  </style>
</head>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Añadir Nueva Jornada</h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i> Inicio</a></li>
      <li><a href="jornadas_listado.php"><i class="fa fa-calendar"></i> Jornadas</a></li>
      <li class="active"><a href="#"><i class="fa fa-plus"></i> Añadir</a></li>
    </ol>
  </section>

  <section class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="nav-tabs-custom">
          <ul class="nav nav-tabs">
            <li class="active"><a href="#tab_jornada" data-toggle="tab">Información de la Jornada</a></li>
          </ul>
          <div class="tab-content">
            <div class="tab-pane active" id="tab_jornada" style="margin-bottom: 5%;">
              <div class="box-body">
                <form id="formularioJornada" action="../../cfg/agregar/agregar_jornada.php" method="POST" novalidate>
                  
                  <!-- FILA 1: NOMBRE Y TIPO -->
                  <div class="row">
                    <div class="col-sm-8 form-group" id="group_nombre">
                      <p>Nombre de la Jornada (*):</p>
                      <input type="text" name="nombre_jornada" id="nombre_jornada" class="form-control" placeholder="Ej: Jornada Antipoliomielitis 2026" maxlength="150" required>
                    </div>
                    <div class="col-sm-4 form-group" id="group_tipo">
                      <p>Tipo de Jornada (*):</p>
                      <select name="tipo_jornada" id="tipo_jornada" class="form-control" required>
                        <option value="">--- Seleccione el Tipo ---</option>
                        <option value="Inmunización">Inmunización (Vacunación)</option>
                        <option value="Detección de Patologías">Detección de Patologías</option>
                        <option value="Atención Integral">Atención Integral (Mixta)</option>
                        <option value="Cerco Epidemiológico">Cerco Epidemiológico</option>
                        <option value="Otra">Otra</option>
                      </select>
                    </div>
                  </div>

                  <!-- FILA 2: FECHAS -->
                  <div class="row">
                    <div class="col-sm-4 form-group" id="group_fecha_inicio">
                      <p>Fecha de Inicio (*):</p>
                      <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-sm-4 form-group" id="group_fecha_fin">
                      <p>Fecha de Fin (*):</p>
                      <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-sm-4 form-group" id="group_meta">
                      <p>Meta (Dosis o Pacientes) (*):</p>
                      <input type="number" name="meta" id="meta" class="form-control" placeholder="Ej: 500" min="1" required>
                    </div>
                  </div>

                  <!-- FILA 3: LUGAR Y RESPONSABLE -->
                  <div class="row">
                    <div class="col-sm-6 form-group" id="group_lugar">
                      <p>Lugar / ASIC / CPT3 (*):</p>
                      <select name="id_lugar" id="id_lugar" class="form-control" required>
                        <option value="">--- Seleccione el Lugar ---</option>
                        <?php
                        if ($query_lugares && $query_lugares->num_rows > 0) {
                          while ($lugar = $query_lugares->fetch_assoc()) {
                            echo '<option value="' . $lugar['Id_Area'] . '">' . htmlspecialchars($lugar['nombre_area']) . '</option>';
                          }
                        } else {
                          // Opciones de respaldo si no hay tabla de áreas
                          echo '<option value="ASIC Aguablanca">ASIC Aguablanca</option>';
                          echo '<option value="CPT3 Barrio Sur">CPT3 Barrio Sur</option>';
                          echo '<option value="CPT3 Centro">CPT3 Centro</option>';
                        }
                        ?>
                      </select>
                    </div>
                    <div class="col-sm-6 form-group" id="group_responsable">
                      <p>Responsable de la Jornada (*):</p>
                      <input type="text" name="responsable" id="responsable" class="form-control" placeholder="Nombre del médico o enfermera" maxlength="100" required>
                    </div>
                  </div>

                  <!-- FILA 4: OBSERVACIONES -->
                  <div class="row">
                    <div class="col-sm-12 form-group">
                      <p>Observaciones (Opcional):</p>
                      <textarea name="observaciones" id="observaciones" class="form-control" rows="3" placeholder="Detalles adicionales de la jornada..."></textarea>
                    </div>
                  </div>

                  <!-- BOTONES -->
                  <div style="float:right; margin-top: 20px;">
                    <button type="button" class="btn btn-second" data-toggle="modal" data-target="#modalConfirmarRegreso">Regresar</button>
                    <button type="submit" class="btn btn-success" id="btnGuardarJornada">Guardar Jornada</button>
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
  <div class="modal fade" id="avisoModal" tabindex="-1" role="dialog" aria-labelledby="avisoModalLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson" id="headerTipoPaciente">
          <h5 class="modal-title">Aviso Importante</h5>
        </div>
        <div class="modal-body">
          <p id="avisoTexto"></p>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button></div>
      </div>
    </div>
  </div>

  <!-- MODAL DE CONFIRMACIÓN DE REGRESO -->
  <div class="modal fade" id="modalConfirmarRegreso" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarRegresoLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h4 class="modal-title" id="modalConfirmarRegresoLabel"><i class="fa fa-sign-out"></i> Confirmación de Regreso</h4>
        </div>
        <div class="modal-body">
          <p>Al hacer clic en "Abandonar Formulario", perderá todos los datos no guardados. ¿Desea continuar?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <a href="jornadas_listado.php" class="btn btn-danger">Abandonar Formulario</a>
        </div>
      </div>
    </div>
  </div>

  <?php include('includes/footer.php'); ?>

  <script>
    // ==========================================
    // MOSTRAR AVISOS (COPIA FIEL DE CITAS)
    // ==========================================
    function mostrarAviso(texto, onHiddenCallBack = null) {
      $('#avisoTexto').html(texto);
      if (onHiddenCallBack && typeof onHiddenCallBack === 'function') {
        $('#avisoModal').one('hidden.bs.modal', function() {
          onHiddenCallBack();
        });
      }
      $('#avisoModal').modal('show');
    }

    // ==========================================
    // CONTROL DE MODALES (COPIA FIEL DE CITAS)
    // ==========================================
    $(document).on('show.bs.modal', '.modal', function(event) {
      var zIndex = 1040 + (10 * $('.modal:visible').length);
      $(this).css('z-index', zIndex);
      setTimeout(function() {
        $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
      }, 0);
    });

    $('.modal').on('click', '[data-dismiss="modal"]', function(e) {
      e.preventDefault();
      var $modal = $(this).closest('.modal');
      $modal.removeClass('in').addClass('out');
      if ($modal.data('timerCierre')) clearTimeout($modal.data('timerCierre'));
      var timerId = setTimeout(function() {
        $modal.modal('hide');
        $modal.removeClass('out');
      }, 400);
      $modal.data('timerCierre', timerId);
    });

    $('.modal').on('show.bs.modal', function() {
      if ($(this).data('timerCierre')) clearTimeout($(this).data('timerCierre'));
      $(this).removeClass('out');
    });

    $('.modal').on('hidden.bs.modal', function() {
      if ($('.modal:visible').length) {
        $('body').addClass('modal-open');
      } else {
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
      }
    });

    // ==========================================
    // VALIDACIÓN DE FECHAS
    // ==========================================
    $('#fecha_inicio').on('change', function() {
      const fechaInicio = $(this).val();
      if (fechaInicio) {
        $('#fecha_fin').attr('min', fechaInicio);
        // Si la fecha fin es menor a la fecha inicio, la limpiamos
        if ($('#fecha_fin').val() && $('#fecha_fin').val() < fechaInicio) {
          $('#fecha_fin').val('');
          mostrarAviso("⚠️ La Fecha de Fin no puede ser anterior a la Fecha de Inicio.");
        }
      }
    });

    // ==========================================
    // VALIDACIÓN DEL FORMULARIO
    // ==========================================
    $('#formularioJornada').on('submit', function(e) {
      e.preventDefault();
      
      let isValid = true;
      let errores = [];
      
      $('.input-error').removeClass('input-error');

      if ($('#nombre_jornada').val().trim() === '') {
        $('#nombre_jornada').addClass('input-error');
        errores.push("El nombre de la jornada es obligatorio.");
        isValid = false;
      }
      if ($('#tipo_jornada').val() === '') {
        $('#tipo_jornada').addClass('input-error');
        errores.push("Debe seleccionar el tipo de jornada.");
        isValid = false;
      }
      if ($('#fecha_inicio').val() === '') {
        $('#fecha_inicio').addClass('input-error');
        errores.push("La fecha de inicio es obligatoria.");
        isValid = false;
      }
      if ($('#fecha_fin').val() === '') {
        $('#fecha_fin').addClass('input-error');
        errores.push("La fecha de fin es obligatoria.");
        isValid = false;
      }
      if ($('#fecha_inicio').val() && $('#fecha_fin').val() && $('#fecha_fin').val() < $('#fecha_inicio').val()) {
        $('#fecha_inicio').addClass('input-error');
        $('#fecha_fin').addClass('input-error');
        errores.push("La fecha de fin no puede ser anterior a la fecha de inicio.");
        isValid = false;
      }
      if ($('#meta').val() === '' || parseInt($('#meta').val()) <= 0) {
        $('#meta').addClass('input-error');
        errores.push("La meta debe ser un número mayor a 0.");
        isValid = false;
      }
      if ($('#id_lugar').val() === '') {
        $('#id_lugar').addClass('input-error');
        errores.push("Debe seleccionar el lugar / ASIC / CPT3.");
        isValid = false;
      }
      if ($('#responsable').val().trim() === '') {
        $('#responsable').addClass('input-error');
        errores.push("El responsable de la jornada es obligatorio.");
        isValid = false;
      }

      if (!isValid) {
        mostrarAviso("<b>Por favor corrija los siguientes errores:</b><br><ul><li>" + errores.join("</li><li>") + "</li></ul>");
      } else {
        this.submit();
      }
    });

    // ==========================================
    // LIMPIAR ERRORES AL ESCRIBIR
    // ==========================================
    $(document).ready(function() {
      $('.form-control').on('input change', function() {
        if ($(this).val() !== "") {
          $(this).closest('.form-group').removeClass('input-error');
        }
      });
    });
  </script>
</div>