<?php
include("../../cfg/conexion.php");

// Si viene un ID por la URL (desde el listado), lo capturamos
$id_paciente_url = isset($_GET['Id']) ? (int)$_GET['Id'] : 0;
$cedula_url = '';
$tipo_cedula_url = 'V';

if ($id_paciente_url > 0) {
  $sql_pac = "SELECT tipo_cedula, cedula FROM persona WHERE id = $id_paciente_url";
  $res_pac = $conexion->query($sql_pac);
  if ($row_pac = $res_pac->fetch_assoc()) {
    $tipo_cedula_url = $row_pac['tipo_cedula'];
    $cedula_url = $row_pac['cedula'];
  }
}

include('includes/headerNav2.php');
verificarAcceso('Gestionar Vacunas');
?>

<head>
  <title>Inmunización | Registrar Dosis</title>
</head>

<div class="content-wrapper">
  <style>
    .form-group {
      margin-bottom: 15px;
    }

    .nav-tabs-custom {
      margin-bottom: 0px;
    }
  </style>

  <section class="content-header">
    <h1>Registrar Dosis de Vacuna</h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="vacunacion_listado.php"><i class="fa fa-syringe"></i>Inmunización</a></li>
      <li class="active"><a href="#"><i class="fa fa-plus"></i>Registrar Dosis</a></li>
    </ol>
  </section>

  <section class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="nav-tabs-custom">
          <ul class="nav nav-tabs">
            <li class="active"><a href="#tab_vacuna" data-toggle="tab">Datos de la Vacunación</a></li>
          </ul>
          <div class="tab-content">
            <div class="tab-pane active" id="tab_vacuna" style="margin-bottom: 5%;">
              <div class="box-body">
                <form id="formularioVacuna" action="../../cfg/agregar/agregar_dosis.php" method="POST" novalidate>

                  <!-- FILA 1: PACIENTE -->
                  <div class="row">
                    <div class="col-md-4 col-sm-6 form-group" id="group_cedula_completa">
                      <p>Cédula / Documento (*)</p>
                      <div class="input-group">
                        <span class="input-group-btn" style="width: 20%;">
                          <select name="tipo_cedula_paciente" id="tipo_cedula_paciente" class="form-control" style="border-top-left-radius: 6; border-bottom-left-radius: 6; padding: 6px 4px; font-weight: bold; background-color: #f4f4f4;" <?php echo ($id_paciente_url > 0) ? 'disabled' : ''; ?>>
                            <option value="V" <?php echo ($tipo_cedula_url == 'V') ? 'selected' : ''; ?>>V-</option>
                            <!--<option value="E" <?php echo ($tipo_cedula_url == 'E') ? 'selected' : ''; ?>>E-</option>-->
                            <option value="PN" <?php echo ($tipo_cedula_url == 'PN') ? 'selected' : ''; ?>>PN-</option>
                            <option value="RP" <?php echo ($tipo_cedula_url == 'RP') ? 'selected' : ''; ?>>REP-</option>
                          </select>
                        </span>
                        <input type="text" class="form-control" name="cedula_paciente" id="cedula_paciente" placeholder="N° de Cédula" maxlength="20" value="<?php echo htmlspecialchars($cedula_url); ?>" <?php echo ($id_paciente_url > 0) ? 'readonly' : ''; ?> required>

                        <!-- BOTÓN DE AGREGAR PACIENTE (MISMO QUE CITAS) -->
                        <?php if ($id_paciente_url <= 0) : ?>
                          <span class="input-group-btn btnSelectorPaciente">
                            <button class="btn btn-info" type="button" id="btnAbrirSelectorPaciente" title="Agregar Paciente" style="height: 34px;">
                              <i><img src="../../recursos/imagenes/iconos/agregar.png" style="width:10px; height:10px;"></i>
                            </button>
                          </span>
                        <?php endif; ?>
                      </div>
                    </div>

                    <div class="col-sm-4 form-group" id="group_nombre_paciente">
                      <p>Nombre del Paciente (*)</p>
                      <input type="text" id="nombre_paciente" class="form-control" name="nombre_paciente" readonly placeholder="Nombre del Paciente" required>
                      <input type="hidden" name="id_paciente" id="id_paciente_hidden" value="<?php echo ($id_paciente_url > 0) ? $id_paciente_url : ''; ?>">
                      <input type="hidden" name="edad_paciente" id="edad_paciente_hidden">
                    </div>

                    <div class="col-sm-4 form-group">
                      <p>Fecha de Nacimiento / Edad:</p>
                      <input type="text" id="fecha_nacimiento_paciente" class="form-control" readonly placeholder="Fecha de Nacimiento">
                    </div>
                  </div>

                  <!-- FILA 2: VACUNA Y DOSIS -->
                  <div class="row">
                    <div class="col-sm-6 form-group" id="group_vacuna">
                      <p>Vacuna / Biológico (*):</p>
                      <select name="id_vacuna" id="id_vacuna" class="form-control" required>
                        <option value="">--- Seleccione la Vacuna ---</option>
                        <option value="1">BCG (Antituberculosa)</option>
                        <option value="2">Hepatitis B</option>
                        <option value="3">Polio (IPV/OPV)</option>
                        <option value="4">Pentavalente</option>
                        <option value="5">SRP (Sarampión, Rubéola, Parotiditis)</option>
                        <option value="6">Fiebre Amarilla</option>
                        <option value="7">Toxoide (Td)</option>
                        <option value="8">Influenza</option>
                      </select>
                    </div>
                    <div class="col-sm-6 form-group" id="group_dosis">
                      <p>Dosis a Aplicar (*):</p>
                      <select name="dosis" id="dosis" class="form-control" required>
                        <option value="">--- Seleccione la Dosis ---</option>
                        <option value="1">1ra Dosis</option>
                        <option value="2">2da Dosis</option>
                        <option value="3">3ra Dosis</option>
                        <option value="R1">1er Refuerzo</option>
                        <option value="R2">2do Refuerzo</option>
                        <option value="U">Dosis Única</option>
                      </select>
                    </div>
                  </div>

                  <!-- FILA 3: LOTE, VENCIMIENTO Y VÍA -->
                  <div class="row">
                    <div class="col-sm-4 form-group" id="group_lote">
                      <p>Número de Lote (*):</p>
                      <input type="text" name="lote" id="lote" class="form-control" placeholder="Ej: PT-2024-45A" required>
                    </div>
                    <div class="col-sm-4 form-group">
                      <p>Fecha de Vencimiento del Lote:</p>
                      <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" class="form-control">
                    </div>
                    <div class="col-sm-4 form-group" id="group_via">
                      <p>Vía de Administración (*):</p>
                      <select name="via_administracion" id="via_administracion" class="form-control" required>
                        <option value="Intramuscular">Intramuscular (IM)</option>
                        <option value="Subcutánea">Subcutánea (SC)</option>
                        <option value="Intradérmica">Intradérmica (ID)</option>
                        <option value="Oral">Oral (VO)</option>
                      </select>
                    </div>
                  </div>

                  <!-- FILA 4: FECHA DE APLICACIÓN Y VACUNADOR -->
                  <div class="row">
                    <div class="col-sm-4 form-group" id="group_fecha_aplicacion">
                      <p>Fecha de Aplicación (*):</p>
                      <input type="date" name="fecha_aplicacion" id="fecha_aplicacion" class="form-control" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-sm-4 form-group" id="group_vacunador">
                      <p>Vacunador / Enfermera (*):</p>
                      <input type="text" name="vacunador" id="vacunador" class="form-control" placeholder="Nombre de quien aplica" required>
                    </div>
                    <div class="col-sm-4 form-group">
                      <p>Establecimiento de Salud:</p>
                      <input type="text" name="establecimiento" id="establecimiento" class="form-control" placeholder="Ej: ASIC Aguablanca" value="CPT3 / ASIC">
                    </div>
                  </div>

                  <!-- FILA 5: OBSERVACIONES -->
                  <div class="row">
                    <div class="col-sm-12 form-group">
                      <p>Observaciones / Reacciones (Opcional):</p>
                      <textarea name="observaciones" id="observaciones" class="form-control" rows="2" placeholder="Ej: Paciente presentó fiebre leve posterior..."></textarea>
                    </div>
                  </div>

                  <!-- BOTONES (MISMA ALINEACIÓN DE CITAS) -->
                  <div style="float:right; margin-top: 20px;">
                    <button type="button" class="btn btn-second" data-toggle="modal" data-target="#modalConfirmarRegreso">Regresar</button>
                    <button type="submit" class="btn btn-success" id="btnGuardarDosis">Guardar Dosis</button>
                  </div>

                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- MODALES (COPIADOS DE CITAS) -->
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
          <a href="vacunacion_listado.php" class="btn btn-danger">Abandonar Formulario</a>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL DE SELECCIÓN DE TIPO DE PACIENTE (COPIADO DE CITAS) -->
  <div class="modal fade" id="modalSeleccionTipoPaciente" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h4 class="modal-title text-white"><i class="fa fa-users"></i> Tipo de Paciente</h4>
        </div>
        <div class="modal-body text-center">
          <p>¿Qué tipo de paciente desea registrar?</p>
          <div class="d-flex flex-column">
            <button type="button" class="btn btn-primary btn-block mb-2" id="btnAbrirModalAdulto">
              <i class="fa fa-user"></i> Paciente Adulto (+18)
            </button>
            <button type="button" class="btn btn-info btn-block" id="btnAbrirModalMenor">
              <i class="fa fa-child"></i> Paciente Menor (-18)
            </button>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </div>
  </div>
  <?php
  include('modales/personas/pacientes_agregar_modal.php');
  include('modales/personas/pacientes_menores_agregar_modal.php');
  ?>
</div>

<?php
include('includes/footer.php');
?>

<script>
  const cedulaPacienteInput = document.getElementById('cedula_paciente');
  const tipoCedulaPacienteSelect = document.getElementById('tipo_cedula_paciente');
  const nombrePacienteInput = document.getElementById('nombre_paciente');
  const idPacienteHidden = document.getElementById('id_paciente_hidden');
  const edadPacienteHidden = document.getElementById('edad_paciente_hidden');
  const fechaNacPacienteInput = document.getElementById('fecha_nacimiento_paciente');

  // ==========================================
  // AVISOS (COPIA FIEL DE CITAS)
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
  // CONTROL DE MODALES ANIDADOS (COPIA FIEL DE CITAS)
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
  // FILTROS DE CÉDULA (COPIA FIEL DE CITAS)
  // ==========================================
  function filtrarCedulaCita() {
    const tipo = tipoCedulaPacienteSelect.value;
    let valor = cedulaPacienteInput.value;
    let nuevoValor = valor;
    let longitudMaxima = 20;

    if (tipo === 'V' || tipo === 'E') {
      nuevoValor = valor.replace(/[^0-9]/g, '');
      longitudMaxima = 8;
    } else if (tipo === 'RP') {
      nuevoValor = valor.replace(/[^0-9]/g, '');
      if (nuevoValor.length > 8) {
        nuevoValor = nuevoValor.substring(0, 8) + '-' + nuevoValor.substring(8, 9);
      }
      longitudMaxima = 10;
    } else if (tipo === 'PN') {
      nuevoValor = valor.replace(/[^0-9a-zA-Z- ]/g, '');
      longitudMaxima = 20;
    }

    cedulaPacienteInput.maxLength = longitudMaxima;
    cedulaPacienteInput.value = nuevoValor;
  }

  function ajustarLongitudCedula(tipo) {
    let maxLength = (tipo === 'PN') ? 20 : (tipo === 'RP' ? 10 : 8);
    cedulaPacienteInput.setAttribute('maxlength', maxLength);
    if (cedulaPacienteInput.value.length > maxLength) {
      cedulaPacienteInput.value = cedulaPacienteInput.value.substring(0, maxLength);
    }
  }

  // ==========================================
  // VERIFICAR CÉDULA Y CARGAR DATOS (AJAX)
  // ==========================================
  function verificarCedulaYObtenerDatos(tipo, cedula, callback = null) {
    if (cedula.length < 1) {
      if (callback) callback(false);
      return;
    }

    $.ajax({
      url: '../../cfg/ajax/obtener_paciente.php',
      method: 'POST',
      dataType: 'json',
      data: {
        tipo_cedula: tipo,
        cedula: cedula
      },
      success: function(response) {
        if (response.existe) {
          $('#nombre_paciente').val(response.nombre_completo).removeClass('input-error');
          $('#id_paciente_hidden').val(response.id);
          $('#edad_paciente_hidden').val(response.edad);
          $('#fecha_nacimiento_paciente').val(response.fecha_nacimiento || '');
          $('#group_cedula_completa').removeClass('input-error');

          $('#btnAbrirSelectorPaciente').prop('disabled', true).css({
            'background-color': '#e9ecef',
            'border-color': 'gray',
            'pointer-events': 'none'
          });

          if (callback) callback(true);
        } else {
          $('#nombre_paciente').val('¡Paciente no encontrado!');
          $('#fecha_nacimiento_paciente').val('');
          $('#id_paciente_hidden').val('');
          $('#edad_paciente_hidden').val('');
          $('#group_cedula_completa').addClass('input-error');

          $('#btnAbrirSelectorPaciente').prop('disabled', false).css({
            'background-color': '',
            'border-color': '',
            'pointer-events': ''
          });

          mostrarAviso("⚠️ El paciente con documento " + tipo + "-" + cedula + " no está registrado. Debe agregarlo haciendo clic en el botón <b>(+)</b>.");
          if (callback) callback(false);
        }
      },
      error: function() {
        mostrarAviso('⚠️ Error de conexión al verificar el paciente.');
        if (callback) callback(false);
      }
    });
  }

  // ==========================================
  // EVENTOS DE INPUT (COPIA FIEL DE CITAS)
  // ==========================================
  cedulaPacienteInput.addEventListener('input', filtrarCedulaCita);

  tipoCedulaPacienteSelect.addEventListener('change', function() {
    cedulaPacienteInput.value = '';
    filtrarCedulaCita();
    $('#btnAbrirSelectorPaciente').removeClass('btn-default').addClass('btn-info').prop('disabled', false).css({
      'background-color': '',
      'border-color': '',
      'pointer-events': ''
    });
  });

  let temporizadorBusqueda;
  cedulaPacienteInput.addEventListener('input', function(e) {
    clearTimeout(temporizadorBusqueda);
    const tipo = tipoCedulaPacienteSelect.value;
    const cedula = this.value.trim();
    let limiteMax = (tipo === 'PN') ? 20 : (tipo === 'RP' ? 10 : 8);

    if (cedula.length === 0) {
      $('#nombre_paciente').val('');
      $('#id_paciente_hidden').val('');
      $('#edad_paciente_hidden').val('');
      $('#fecha_nacimiento_paciente').val('');
      $('#group_cedula_completa').removeClass('input-error');
      $('#btnAbrirSelectorPaciente').removeClass('btn-default').addClass('btn-info').prop('disabled', false).css({
        'background-color': '',
        'border-color': '',
        'pointer-events': ''
      });
      return;
    }

    if (e.type === 'blur' || e.type === 'change' || cedula.length === limiteMax || (tipo === 'PN' && cedula.length >= 10)) {
      temporizadorBusqueda = setTimeout(function() {
        verificarCedulaYObtenerDatos(tipo, cedula);
      }, 50);
    }
  });

  cedulaPacienteInput.addEventListener('blur', function() {
    const tipo = tipoCedulaPacienteSelect.value;
    const valor = this.value;
    if (tipo === 'RP') {
      const regexRP = /^[0-9]{8}-[0-9]{1}$/;
      if (!regexRP.test(valor)) {
        mostrarAviso("⚠️ Formato incorrecto. El documento REP debe tener el formato <b>12345678-1</b>.");
        $(this).addClass('input-error');
        return;
      }
    }
    if (valor.length >= 1) {
      verificarCedulaYObtenerDatos(tipo, valor);
    }
  });

  // ==========================================
  // BOTÓN DE AGREGAR PACIENTE (COPIADO DE CITAS)
  // ==========================================
  $('#btnAbrirSelectorPaciente').off('click').on('click', function(e) {
    e.preventDefault();
    const tipo = tipoCedulaPacienteSelect.value;
    const cedula = cedulaPacienteInput.value.trim();

    if (cedula === "") {
      $('#modalSeleccionTipoPaciente').modal('show');
      return;
    }

    verificarCedulaYObtenerDatos(tipo, cedula, function(existe) {
      if (!existe) {
        $('#avisoModal').one('hidden.bs.modal', function() {
          $('#modalSeleccionTipoPaciente').modal('show');
        });
      }
    });
  });

  $('#btnAbrirModalAdulto').off('click').on('click', function() {
    var $modalSel = $('#modalSeleccionTipoPaciente');
    $modalSel.removeClass('in').addClass('out');
    if ($modalSel.data('timerCierre')) clearTimeout($modalSel.data('timerCierre'));
    var timerId = setTimeout(function() {
      $modalSel.modal('hide');
      $modalSel.removeClass('out');
      $('#modalAgregarPacienteExterno').modal('show');
    }, 400);
    $modalSel.data('timerCierre', timerId);
  });

  $('#btnAbrirModalMenor').off('click').on('click', function() {
    var $modalSel = $('#modalSeleccionTipoPaciente');
    $modalSel.removeClass('in').addClass('out');
    if ($modalSel.data('timerCierre')) clearTimeout($modalSel.data('timerCierre'));
    var timerId = setTimeout(function() {
      $modalSel.modal('hide');
      $modalSel.removeClass('out');
      $('#modalAgregarPacienteMenor').modal('show');
    }, 400);
    $modalSel.data('timerCierre', timerId);
  });

  // ==========================================
  // VALIDACIÓN DEL FORMULARIO
  // ==========================================
  $('#formularioVacuna').on('submit', function(e) {
    e.preventDefault();

    let isValid = true;
    let errores = [];

    $('.input-error, .input-error').removeClass('input-error input-error');

    if ($('#id_paciente_hidden').val() === '' || $('#nombre_paciente').val().includes('no encontrado')) {
      $('#group_cedula_completa').addClass('input-error');
      errores.push("Debe verificar e identificar al paciente.");
      isValid = false;
    }
    if ($('#id_vacuna').val() === '') {
      $('#group_vacuna').addClass('input-error');
      errores.push("Debe seleccionar la vacuna.");
      isValid = false;
    }
    if ($('#dosis').val() === '') {
      $('#group_dosis').addClass('input-error');
      errores.push("Debe seleccionar la dosis.");
      isValid = false;
    }
    if ($('#lote').val().trim() === '') {
      $('#group_lote').addClass('input-error');
      errores.push("El número de lote es obligatorio.");
      isValid = false;
    }
    if ($('#fecha_aplicacion').val() === '') {
      $('#group_fecha_aplicacion').addClass('input-error');
      errores.push("La fecha de aplicación es obligatoria.");
      isValid = false;
    }
    if ($('#vacunador').val().trim() === '') {
      $('#group_vacunador').addClass('input-error');
      errores.push("El nombre del vacunador es obligatorio.");
      isValid = false;
    }

    if (!isValid) {
      mostrarAviso("<b>Por favor corrija los siguientes errores:</b><br><ul><li>" + errores.join("</li><li>") + "</li></ul>");
    } else {
      this.submit();
    }
  });

  // ==========================================
  // SI VIENE UN ID POR URL (DESDE EL LISTADO)
  // ==========================================
  <?php if ($id_paciente_url > 0) : ?>
    let tipo_pac = "<?php echo $tipo_cedula_url; ?>";
    let ced_pac = "<?php echo $cedula_url; ?>";
    if (ced_pac !== '') {
      verificarCedulaYObtenerDatos(tipo_pac, ced_pac);
      tipoCedulaPacienteSelect.disabled = true;
    }
  <?php endif; ?>

  ajustarLongitudCedula(tipoCedulaPacienteSelect.value);
  $(document).ready(function() {
    $('.form-control').on('input change', function() {
      if ($(this).val() !== "") {
        $(this).closest('.form-group').removeClass('input-error');
      }
    });
  });
</script>