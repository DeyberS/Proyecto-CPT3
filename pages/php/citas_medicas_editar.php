<?php
include('../../cfg/conexion.php');

if (!isset($_GET['Id']) || empty($_GET['Id'])) {
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }

  $_SESSION['mensaje_user_error'] = "Debe seleccionar una consulta válida para ver su información.";

  header("Location: citas_medicas_listado.php");
  exit();
}

$sql_horarios = "SELECT dia_semana, hora_inicio, hora_fin, estatus FROM configuracion_horarios";
$res_horarios = $conexion->query($sql_horarios);
$horarios_db = array();

while ($row_h = $res_horarios->fetch_assoc()) {
  $horarios_db[$row_h['dia_semana']] = $row_h;
}

$id_cita = $_GET['Id'];

$sql_datos = "SELECT c.*, p.cedula, p.tipo_cedula, p.nombre, p.apellido 
              FROM citas c 
              INNER JOIN persona p ON c.Id_paciente = p.id 
              WHERE c.Id_cita = ?";
$stmt_cita = $conexion->prepare($sql_datos);
$stmt_cita->bind_param("i", $id_cita);
$stmt_cita->execute();
$res_cita = $stmt_cita->get_result();

if ($res_cita->num_rows === 0) {
  header("Location: citas_medicas_listado.php");
  exit;
}

$datos = $res_cita->fetch_assoc();

include('includes/headerNav2.php');
verificarAcceso('Editar Citas');
?>

<head>
  <title>Citas Médicas | Editar</title>
</head>

<style>
  .input-error {
    border: 2px solid crimson !important;
    box-shadow: 0 0 5px crimson !important;
    background-color: #fff5f5 !important;
  }

  .has-error .form-control {
    border: 2px solid crimson !important;
    box-shadow: 0 0 5px crimson !important;
    background-color: #fff5f5 !important;
  }
</style>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Editar Cita</h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="#"><i class="fa fa-calendar"></i>Citas</a></li>
      <li class="active">Editar</li>
    </ol>
  </section>

  <section class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="nav-tabs-custom">
          <ul class="nav nav-tabs">
            <li class="active"><a href="#tab_1" data-toggle="tab">Información de la Cita</a></li>
          </ul>
          <div class="tab-content">
            <div class="tab-pane active" id="tab_1" style="margin-bottom: 5%;">
              <div class="box-body">
                <div class="nav-tabs-custom">
                  <div class="tab-content">
                    <form action="../../cfg/editar/editar_cita.php" id="formularioCita" method="POST" novalidate>
                      <input type="hidden" id="id_cita" name="id_cita" value="<?php echo $id_cita; ?>">

                      <div class="row">
                        <div class="col-md-4 col-sm-6 form-group">
                          <p>Cédula/Documento</p>
                          <div class="input-group">
                            <span class="input-group-btn" style="width: 20%;">
                              <select name="tipo_cedula_paciente" id="tipo_cedula_paciente" class="form-control" style="border-top-left-radius: 6; border-bottom-left-radius: 6; padding: 6px 4px; font-weight: bold; background-color: #f4f4f4;" disabled>
                                <option value="PN" <?php if ($datos['tipo_cedula'] == 'PN') echo 'selected'; ?>>PN-</option>
                                <option value="V" <?php if ($datos['tipo_cedula'] == 'V') echo 'selected'; ?>>V-</option>
                                <option value="RP" <?php if ($datos['tipo_cedula'] == 'RP') echo 'selected'; ?>>RP-</option>
                                <!--<option value="E" <?php if ($datos['tipo_cedula'] == 'E') echo 'selected'; ?>>E-</option>-->
                              </select>
                            </span>

                            <input type="text" class="form-control" name="cedula_paciente" id="cedula_paciente" placeholder="N° de Cédula" value="<?php echo $datos['cedula']; ?>" readonly>

                            <span class="input-group-btn btnSelectorPaciente">
                              <button class="btn btn-info" type="button" id="btnAbrirSelectorPaciente" title="Agregar Paciente" style="height: 34px;">
                                <i><img src="../../recursos/imagenes/iconos/agregar.png" style="width:10px; height:10px;"></i>
                              </button>
                            </span>

                          </div>
                        </div>

                        <div class="col-sm-4 form-group">
                          <p>Paciente</p>
                          <input type="text" class="form-control" id="nombre_paciente" value="<?php echo $datos['nombre'] . ' ' . $datos['apellido']; ?>" readonly>
                          <input type="hidden" name="id_paciente" id="id_paciente_hidden" value="<?php echo $datos['Id_paciente']; ?>">
                          <input type="hidden" name="edad_paciente" id="edad_paciente_hidden">
                        </div>

                        <div class="col-sm-4" id="group_especialidad">
                          <p>Especialidad (*)</p>
                          <select class="form-control" name="id_especialidad" id="id_especialidad" required>
                            <?php
                            $sql_esp = "SELECT * FROM especialidad";
                            $res_esp = $conexion->query($sql_esp);
                            while ($e = $res_esp->fetch_assoc()) {
                              $sel = ($e['Id_especialidad'] == $datos['Id_especialidad']) ? 'selected' : '';
                              echo "<option value='{$e['Id_especialidad']}' $sel>{$e['nombre_especialidad']}</option>";
                            }
                            ?>
                          </select>
                        </div>
                      </div>

                      <div class="row" style="margin-top: 15px;">
                        <div class="col-sm-4 form-group" id="group_medico">
                          <p>Médico Especialista (*)</p>
                          <select class="form-control" name="id_medico" id="id_medico" required>
                            <option value="<?php echo $datos['Id_medico']; ?>">Cargando médico actual...</option>
                          </select>
                        </div>

                        <div class="col-sm-4 form-group" id="group_fecha">
                          <p>Fecha (*)</p>
                          <input type="date" class="form-control" name="fecha_cita" id="fecha_cita" value="<?php echo $datos['fecha_cita']; ?>" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                        </div>

                        <div class="col-sm-4 form-group" id="group_hora">
                          <p>Hora (*)</p>
                          <input type="time" class="form-control" name="hora_cita" id="hora_cita" value="<?php echo $datos['hora_cita']; ?>" required>
                        </div>
                      </div>

                      <div class="row" style="margin-top: 15px;">
                        <div class="col-sm-8 form-group" id="group_motivo">
                          <p>Motivo de la consulta:</p>
                          <textarea class="form-control" name="motivo" id="motivo" rows="2" required><?php echo $datos['motivo']; ?></textarea>
                        </div>
                        <div class="col-sm-4 form-group" id="group_estado">
                          <p>Estado de la Cita (*)</p>
                          <select class="form-control" name="estado" id="estado" required>
                            <option value="1" <?php if ($datos['estado'] == "Pendiente") echo 'selected'; ?>>Pendiente</option>
                            <option value="2" <?php if ($datos['estado'] == "Confirmada") echo 'selected'; ?>>Confirmada</option>
                            <option value="3" <?php if ($datos['estado'] == "Cancelada") echo 'selected'; ?>>Cancelada</option>
                            <option value="4" <?php if ($datos['estado'] == "Finalizada") echo 'selected'; ?>>Finalizada</option>
                            <option value="5" <?php if ($datos['estado'] == "Vencida") echo 'selected'; ?>>Vencida</option>
                            <option value="6" <?php if ($datos['estado'] == "Inasistente") echo 'selected'; ?>>Inasistente</option>
                            <option value="7" <?php if ($datos['estado'] == "Reprogramada") echo 'selected'; ?>>Reprogramada</option>
                          </select>
                        </div>
                      </div>

                      <div style="float:right; margin-top: 30px;">
                        <button type="button" class="btn btn-second" data-toggle="modal" data-target="#modalConfirmarRegreso">Regresar</button>
                        <button type="submit" class="btn btn-success" id="btnActualizarCita">Actualizar</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
  </section>
</div>

<div class="modal fade" id="avisoModal" tabindex="-1" role="dialog" aria-labelledby="avisoModalLabel">
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

<div class="modal fade" id="modalGuardarCita" tabindex="-1" role="dialog" aria-labelledby="modalGuardarCitaLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-green">
        <h4 class="modal-title">Confirmación de Cita</h4>
      </div>
      <div class="modal-body">
        <p>¿Está seguro de que desea actualizar la información de esta cita?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" id="confirmarGuardarCita">Aceptar</button>
      </div>
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
        <a href="citas_medicas_listado.php" class="btn btn-danger">Abandonar Formulario</a>
      </div>
    </div>
  </div>
</div>

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
include('includes/footer.php');
?>

<script>
  const cedulaPacienteInput = document.getElementById('cedula_paciente');
  const tipoCedulaPacienteSelect = document.getElementById('tipo_cedula_paciente');
  const nombrePacienteInput = document.getElementById('nombre_paciente');

  function mostrarAviso(texto, onHiddenCallBack = null) {
    $('#avisoTexto').html(texto);

    if (onHiddenCallBack && typeof onHiddenCallBack === 'function') {
      $('#avisoModal').one('hidden.bs.modal', function() {
        onHiddenCallBack();
      });
    }

    $('#avisoModal').modal('show');
  }

  window.mostrarExito = function(mensaje) {
    $('#headerTipoPaciente').removeClass('bg-crimson').addClass('bg-green');
    $('#avisoTexto').html(mensaje);
    $('#avisoModal').modal('show');
  }

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

    if ($modal.data('timerCierre')) {
      clearTimeout($modal.data('timerCierre'));
    }

    var timerId = setTimeout(function() {
      $modal.modal('hide');
      $modal.removeClass('out');
    }, 400);

    $modal.data('timerCierre', timerId);
  });

  $('.modal').on('show.bs.modal', function() {
    if ($(this).data('timerCierre')) {
      clearTimeout($(this).data('timerCierre'));
    }
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

  function limpiarErrores() {
    $('.form-group').removeClass('has-error');
  }

  function filtrarCedulaCita() {
    const tipo = $('#tipo_cedula_paciente').val();
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
    let maxLength = (tipo === 'PN') ? 20 : 8;
    cedulaPacienteInput.setAttribute('maxlength', maxLength);

    if (cedulaPacienteInput.value.length > maxLength) {
      cedulaPacienteInput.value = cedulaPacienteInput.value.substring(0, maxLength);
    }
  }

  function validarLongitudCedula() {
    const tipo = $('#tipo_cedula_paciente').val();
    const cedula = $('#cedula_paciente').val().trim();
    const longitud = cedula.length;

    if (tipo === 'RP') {
      const regexRP = /^[0-9]{8}-[0-9]{1}$/;
      if (!regexRP.test(cedula)) {
        return {
          valido: false,
          mensaje: `Formato incorrecto. El documento REP- debe tener el formato 12345678-1.`
        };
      }
      return {
        valido: true
      };
    }

    let min = 2;
    let max = (tipo === 'PN') ? 20 : 8;

    if (longitud < min || longitud > max) {
      return {
        valido: false,
        mensaje: `La cédula tipo ${tipo} debe tener entre ${min} y ${max} caracteres.`
      };
    }
    return {
      valido: true
    };
  }

  $('#cedula_paciente').on('blur', function() {
    const tipo = $('#tipo_cedula_paciente').val();
    const longitud = this.value.length;
    const minRequired = 1;

    if (longitud > 0 && longitud < minRequired) {
      $(this).closest('.form-group').addClass('has-error');
    } else {
      $(this).closest('.form-group').removeClass('has-error');
    }
  });

  function validarCamposCita() {
    let erroresCampos = [];

    const campos1 = [{
        id: 'cedula_paciente',
        nombre: 'Cédula / Documento'
      },
      {
        id: 'id_especialidad',
        nombre: 'Especialidad'
      },
      {
        id: 'id_medico',
        nombre: 'Médico Especialista'
      },
      {
        id: 'fecha_cita',
        nombre: 'Fecha'
      },
      {
        id: 'hora_cita',
        nombre: 'Hora'
      },
      {
        id: 'motivo',
        nombre: 'Motivo de la consulta'
      }
    ];

    campos1.forEach(campo => {
      const inputc = $('#' + campo.id);
      if (inputc.val() === "" || inputc.val() === null) {
        inputc.closest('.form-group').addClass('has-error');
        erroresCampos.push(campo.nombre);
      } else {
        inputc.closest('.form-group').removeClass('has-error');
      }
    });

    return erroresCampos;
  }

  const configuracionHorarios = <?php echo json_encode($horarios_db); ?>;

  $('#fecha_cita').on('change', function() {
    if (!this.value) return;

    const fechaSeleccionada = new Date(this.value + 'T00:00:00');
    const diaSemanaJS = fechaSeleccionada.getUTCDay();

    const diaDB = (diaSemanaJS === 0) ? 7 : diaSemanaJS;
    const configDia = configuracionHorarios[diaDB];

    if (!configDia || configDia.estatus == 0) {
      mostrarAviso("⚠️ El establecimiento no presta servicio en el día seleccionado.");
      this.value = "";
      $('#hora_cita').val("").prop('disabled', true);
      $(this).closest('.form-group').addClass('has-error');
      return;
    }

    const horaInicio = configDia.hora_inicio.substring(0, 5);
    const horaFin = configDia.hora_fin.substring(0, 5);

    $('#hora_cita').prop('disabled', false)
      .attr('min', horaInicio)
      .attr('max', horaFin);

    $(this).closest('.form-group').removeClass('has-error');

    if ($('#hora_cita').val()) {
      $('#hora_cita').trigger('change');
    }
  });

  $('#hora_cita').on('change', function() {
    const horaVal = $(this).val();
    const fechaVal = $('#fecha_cita').val();

    if (!horaVal || !fechaVal) return;

    const fechaSeleccionada = new Date(fechaVal + 'T00:00:00');
    const diaSemanaJS = fechaSeleccionada.getUTCDay();
    const diaDB = (diaSemanaJS === 0) ? 7 : diaSemanaJS;
    const configDia = configuracionHorarios[diaDB];

    if (!configDia || configDia.estatus == 0) return;

    const [h, m] = horaVal.split(':').map(Number);
    const minutosSeleccionados = h * 60 + m;

    const [hInicio, mInicio] = configDia.hora_inicio.split(':').map(Number);
    const minutosInicio = hInicio * 60 + mInicio;

    const [hFin, mFin] = configDia.hora_fin.split(':').map(Number);
    const minutosFin = hFin * 60 + mFin;

    if (minutosSeleccionados < minutosInicio || minutosSeleccionados > minutosFin) {
      const horaInicioFmt = configDia.hora_inicio.substring(0, 5);
      const horaFinFmt = configDia.hora_fin.substring(0, 5);

      mostrarAviso(`⚠️ Horario no disponible.<br>Nuestro horario de atención para este día es de <b>${horaInicioFmt} a ${horaFinFmt}</b>.`);
      $(this).val("");
      $(this).closest('.form-group').addClass('has-error');
    } else {
      $(this).closest('.form-group').removeClass('has-error');
    }
  });

  $(document).ready(function() {
    aplicarRestricciones();

    const cedulaInicial = $('#cedula_paciente').val().trim();

    if (cedulaInicial !== "") {
      $('#btnAbrirSelectorPaciente')
        .removeClass('btn-info')
        .addClass('btn-default')
        .prop('disabled', true)
        .css({
          'background-color': '#e9ecef',
          'border-color': 'gray',
          'pointer-events': 'none'
        });
    }

    if ($('#fecha_cita').val()) {
      $('#fecha_cita').trigger('change');
    }

    $('.form-control').on('input change', function() {
      if ($(this).val() !== "") {
        $(this).closest('.form-group').removeClass('has-error');
      }
    });

    const espInicial = $('#id_especialidad').val();
    const medicoActual = "<?php echo $datos['Id_medico']; ?>";

    if (espInicial) {
      $.post('../../cfg/ajax/obtener_medicos_por_especialidad.php', {
        id_especialidad: espInicial
      }, function(data) {
        $('#id_medico').html(data);
        $('#id_medico').val(medicoActual);
      });
    }

    $('#id_especialidad').on('change', function() {
      const espId = $(this).val();
      if (espId) {
        $.post('../../cfg/ajax/obtener_medicos_por_especialidad.php', {
          id_especialidad: espId
        }, function(data) {
          $('#id_medico').html(data);
        });
      }
    });

    function verificarDisponibilidadYMostrarModal() {
      const btnGuardarCita = $('#btnGuardarCita');
      const datos = {
        id_medico: $('#id_medico').val(),
        fecha: $('#fecha_cita').val(),
        hora: $('#hora_cita').val(),
        id_cita: $('#id_cita').val()
      };

      btnGuardarCita.text('Verificando disponibilidad...').attr('disabled', true);

      $.ajax({
        url: '../../cfg/ajax/verificar_disponibilidad_cita.php',
        method: 'POST',
        dataType: 'json',
        data: datos,
        success: function(response) {
          btnGuardarCita.text('Guardar Cambios').attr('disabled', false);
          if (response.ocupado) {
            mostrarAviso('⚠️ El médico ya tiene otra cita programada en ese horario o dentro del rango de 1 hora.');
            $('#group_medico, #group_fecha, #group_hora').addClass('has-error');
          } else {
            $('#modalGuardarCita').modal('show');
          }
        },
        error: function(xhr, status, error) {
          btnGuardarCita.text('Guardar Cambios').attr('disabled', false);
          mostrarAviso('⚠️ Error al conectar con el servidor para verificar disponibilidad.');
        }
      });
    }

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
            $('#nombre_paciente').val(response.nombre_completo).removeClass('has-error');
            $('#id_paciente_hidden').val(response.id);
            $('#edad_paciente_hidden').val(response.edad);
            $('#group_cedula').removeClass('has-error');

            $('#btnAbrirSelectorPaciente').removeClass('btn-info').addClass('btn-default').prop('disabled', true).css({
              'background-color': '#e9ecef',
              'border-color': 'gray',
              'pointer-events': 'none'
            });

            if (callback) callback(true);
          } else {

            $('#nombre_paciente').val('¡Paciente no encontrado!');
            $('#cedula_paciente').val('');
            $('#id_paciente_hidden').val('');
            $('#edad_paciente_hidden').val('');
            $('#group_cedula').addClass('has-error');

            $('#btnAbrirSelectorPaciente').removeClass('btn-default').addClass('btn-info').prop('disabled', false).css({
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

    let temporizadorBusqueda;

    $('#cedula_paciente').on('input change blur', function(e) {
      clearTimeout(temporizadorBusqueda);

      const tipo = $('#tipo_cedula_paciente').val();
      const cedula = $(this).val().trim();

      let limiteMax = (tipo === 'PN') ? 20 : (tipo === 'RP' ? 10 : 8);

      if (cedula.length === 0) {
        $('#nombre_paciente').val('');
        $('#id_paciente_hidden').val('');
        $('#edad_paciente_hidden').val('');
        $('#group_cedula').removeClass('has-error');

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

    function aplicarRestricciones() {
      $('#cedula_paciente').on('input', filtrarCedulaCita);
      $('#tipo_cedula_paciente').on('change', function() {
        $('#cedula_paciente').val('');
        filtrarCedulaCita();

        $('#btnAbrirSelectorPaciente').removeClass('btn-default').addClass('btn-info').prop('disabled', false).css({
          'background-color': '',
          'border-color': '',
          'pointer-events': ''
        });
      });
    }

    $('#btnAbrirSelectorPaciente').off('click').on('click', function(e) {
      e.preventDefault();
      const tipo = $('#tipo_cedula_paciente').val();
      const cedula = $('#cedula_paciente').val().trim();

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

      if ($modalSel.data('timerCierre')) {
        clearTimeout($modalSel.data('timerCierre'));
      }

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

      if ($modalSel.data('timerCierre')) {
        clearTimeout($modalSel.data('timerCierre'));
      }

      var timerId = setTimeout(function() {
        $modalSel.modal('hide');
        $modalSel.removeClass('out');
        $('#modalAgregarPacienteMenor').modal('show');
      }, 400);

      $modalSel.data('timerCierre', timerId);
    });

    $('#formularioCita').on('submit', function(e) {
      e.preventDefault();
      limpiarErrores();
      let erroresCampos = validarCamposCita();

      if (erroresCampos.length > 0) {
        mostrarAviso("⚠️ Por favor complete los siguientes campos obligatorios:<ul><li>" + erroresCampos.join("</li><li>") + "</li></ul>");
        return;
      }

      const fechaVal = $('#fecha_cita').val();
      const fechaSeleccionada = new Date(fechaVal + 'T00:00:00');
      const hoy = new Date();
      hoy.setHours(0, 0, 0, 0);

      if (fechaSeleccionada <= hoy) {
        mostrarAviso("⚠️ No se pueden programar citas para el mismo día. La fecha mínima permitida es a partir de mañana.");
        $('#group_fecha').addClass('has-error');
        return;
      } else {
        verificarDisponibilidadYMostrarModal();
      }
    });

    $('#confirmarGuardarCita').on('click', function() {
      $('#modalGuardarCita').modal('hide');
      $('#formularioCita').off('submit').submit();
    });
  });
</script>