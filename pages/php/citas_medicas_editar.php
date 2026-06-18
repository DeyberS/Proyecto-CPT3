<?php
include('../../cfg/conexion.php');

// 1. Validar que recibimos un ID válido por la URL
if (!isset($_GET['Id']) || empty($_GET['Id'])) {
  echo "<script>alert('ID de cita no proporcionado'); window.location.href='citas_medicas_listado.php';</script>";
  exit;
}

$id_cita = $_GET['Id'];

// 2. Consultar los datos de la cita y del paciente (incluyendo su cédula)
// Usamos JOIN para traer la cédula y nombre del paciente vinculado a la cita
$sql_datos = "SELECT c.*, p.cedula, p.tipo_cedula, p.nombre, p.apellido 
              FROM citas c 
              INNER JOIN persona p ON c.Id_paciente = p.id 
              WHERE c.Id_cita = ?";
$stmt_cita = $conexion->prepare($sql_datos);
$stmt_cita->bind_param("i", $id_cita);
$stmt_cita->execute();
$res_cita = $stmt_cita->get_result();

if ($res_cita->num_rows === 0) {
  echo "<script>alert('Cita no encontrada'); window.location.href='citas_medicas_listado.php';</script>";
  exit;
}

$datos = $res_cita->fetch_assoc();
?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Citas | Editar</title>
  <?php include('includes/headerNav2.php'); ?>
</head>

<style>
  .has-error .form-control {
    border: 2px solid crimson !important;
    box-shadow: 0 0 5px crimson;
  }

  /* ---------------------------------------------------------------------- */
  /* ANIMACIONES Y ESTILOS DE MODALES */
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

  /* El modal.in y modal.out controlan la animación del modal-dialog */
  .modal.in .modal-dialog,
  #avisoModal,
  #modalGuardarCita {
    animation: fadeIn 0.4s ease-out;
  }

  .modal.out .modal-dialog {
    animation: fadeOut 0.4s ease-in;
  }

  /* El backdrop usa pulse-opacity */
  .modal-open .modal-backdrop {
    opacity: 0.7 !important;
    animation: pulse-opacity 0.3s forwards;
  }

  /* ---------------------------------------------------------------------- */
  /* ESTILOS DE VALIDACIÓN Y LAYOUT */
  /* ---------------------------------------------------------------------- */

  #avisoModal,
  #modalGuardarMedico {
    z-index: 999999 !important;
  }

  .modal {
    position: fixed !important;
    z-index: 99999 !important;
  }

  .modal-backdrop {
    z-index: 99998 !important;
    transition: .5s;
  }

  /* La clase 'in' es clave para que Bootstrap sepa que el modal está abierto */
  .modal.in {
    display: block;
  }

  .modal-header-danger {
    background-color: #dc3545;
    /* Rojo de Bootstrap bg-danger */
    color: white;
  }

  .input-error {
    border: 2px solid crimson !important;
    box-shadow: 0 0 5px crimson;
  }
</style>
</head>

<body>
  <div class="content-wrapper">
    <section class="content-header">
      <h1>Editar Cita Médica #<?php echo $id_cita; ?></h1>
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
                        <input type="hidden" name="id_cita" value="<?php echo $id_cita; ?>">

                        <div class="row">
                          <div class="col-sm-1 pull-left" style="margin-top: 30px;">
                            <select name="tipo_cedula_paciente" class="form-control" disabled>
                              <option value="V" <?php if ($datos['tipo_cedula'] == 'V') echo 'selected'; ?>>V-</option>
                              <!--<option value="E" <?php if ($datos['tipo_cedula'] == 'E') echo 'selected'; ?>>E-</option>-->
                              <option value="PN" <?php if ($datos['tipo_cedula'] == 'PN') echo 'selected'; ?>>PN-</option>
                            </select>
                          </div>
                          <div class="col-sm-3">
                            <p>Cédula/Documento</p>
                            <input type="text" class="form-control" value="<?php echo $datos['cedula']; ?>" readonly>
                          </div>
                          <div class="col-sm-4 form-group">
                            <p>Paciente</p>
                            <input type="text" class="form-control" value="<?php echo $datos['nombre'] . ' ' . $datos['apellido']; ?>" readonly>
                            <input type="hidden" name="id_paciente" value="<?php echo $datos['Id_paciente']; ?>">
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
                            <input type="date" class="form-control" name="fecha_cita" id="fecha_cita" value="<?php echo $datos['fecha_cita']; ?>" min="<?php echo date('Y-m-d'); ?>" required>
                          </div>

                          <div class="col-sm-4 form-group" id="group_hora">
                            <p>Hora (*)</p>
                            <input type="time" class="form-control" name="hora_cita" id="hora_cita" value="<?php echo $datos['hora_cita']; ?>" min="09:00" max="13:00" required>
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
                          <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#modalConfirmarRegreso">Regresar</button>
                          <button type="submit" class="btn btn-success" id="btnActualizarCita">Actualizar Cita</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            </div>
    </section>
  </div>

  <div class="modal" id="avisoModal" tabindex="-1" role="dialog" aria-labelledby="avisoModalLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson" style="color: white;">
          <h5 class="modal-title">Aviso de Validación</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <p id="avisoTexto"></p>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button></div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalGuardarCita" tabindex="-1" role="dialog" aria-labelledby="modalGuardarCitaLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: #00a65a; color: white;">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title">Confirmación de Cita</h4>
        </div>
        <div class="modal-body">
          <p>¿Desea confirmar el agendamiento de esta cita médica?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-success" id="confirmarGuardarCita">Guardar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalConfirmarRegreso" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarRegresoLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header modal-header-danger">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="modalConfirmarRegresoLabel"><i class="fa fa-sign-out"></i> Confirmación de Regreso</h4>
        </div>
        <div class="modal-body">
          <p>Al hacer clic en "Abandonar Formulario", perderá todos los datos no guardados. ¿Desea continuar?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <a href="citas_medicas_listado.php" class="btn btn-danger">Abandonar Formulario</a>
        </div>
      </div>
    </div>
  </div>

  <?php include('includes/footer.php'); ?>

  <script>
    function mostrarAviso(texto, onHiddenCallBack = null) {
      const $avisoModal = $('#avisoModal').modal('show');
      $('#avisoTexto').html(texto);

      $avisoModal.off('hidden.bs.modal');

      if (onHiddenCallBack && typeof onHiddenCallBack === 'function') {
        $avisoModal.one('hidden.bs.modal', function() {
          onHiddenCallBack();
        });
      }
      $avisoModal.modal('show');
    }

    $('#avisoModal .close, #avisoModal .btn-secondary').off('click').on('click', function() {
      $('#avisoModal').modal('hide');
    });

    $('#modalGuardarCita .close, #modalGuardarCita .btn-second').off('click').on('click', function() {
      $('#modalGuardarCita').modal('hide');
    });

    function limpiarErrores() {
      $('.form-group').removeClass('has-error');
    }

    function validarHorarioLaboral(hora) {
      // La hora llega en formato "HH:mm" (ej: "10:30")
      const [h, m] = hora.split(':').map(Number);
      const horaDecimal = h + (m / 60);

      const inicio = 9.0; // 09:00 AM
      const fin = 12.0; // 12:00 PM (12:00 horas)

      if (horaDecimal >= inicio && horaDecimal <= fin) {
        return true;
      } else {
        return false;
      }
    }

    function validarCamposCita() {
      let errores = 0;
      // Lista de IDs a validar
      const campos = ['id_especialidad', 'id_medico', 'fecha_cita', 'hora_cita', 'motivo'];

      campos.forEach(id => {
        const inputc = $('#' + id);
        if (inputc.val() === "" || inputc.val() === null) {
          inputc.closest('.form-group').addClass('has-error');
          errores++;
        } else {
          inputc.closest('.form-group').removeClass('has-error');
        }
      });

      return errores === 0; // Retorna true si todo está bien
    }

    $('#hora_cita').on('change', function() {
      const horaVal = $(this).val(); // Retorna "HH:mm"
      if (!horaVal) return;

      const [horas, minutos] = horaVal.split(':').map(Number);
      const tiempoDecimal = horas + (minutos / 60);

      const horaInicio = 9.0; // 09:00 AM
      const horaFin = 12.0; // 01:00 PM

      if (tiempoDecimal < horaInicio || tiempoDecimal > horaFin) {
        mostrarAviso("🛑 Horario no disponible. <br>Nuestro horario de atención es de <b>9:00 AM a 12:00 PM</b>.");
        $(this).val(""); // Limpiamos el campo inmediatamente
        $(this).closest('.form-group').addClass('has-error');
      } else {
        $(this).closest('.form-group').removeClass('has-error');
      }
    });

    $('#fecha_cita').on('change', function() {
      const fechaSeleccionada = new Date(this.value + 'T00:00:00');
      const diaSemana = fechaSeleccionada.getUTCDay(); // 0 = Domingo, 6 = Sábado

      if (diaSemana === 0 || diaSemana === 6) {
        mostrarAviso("🛑 No se pueden agendar citas los fines de semana (Sábados o Domingos).");
        this.value = ""; // Limpia el campo
        $(this).closest('.form-group').addClass('has-error');
      } else {
        $(this).closest('.form-group').removeClass('has-error');
      }
    });

    const fechaInput = document.getElementById('fecha_cita');
      if (fechaInput) {
        fechaInput.addEventListener('change', function() {
          const hoy = new Date().toISOString().split('T')[0];
          if (this.value > hoy) {
            mostrarAviso("La fecha de la cita no puede ser mayor al día de hoy.");
            this.value = hoy; // Resetear al día de hoy
          }
        });
      }

    $(document).ready(function() {
      $('.form-control').on('input change', function() {
        if ($(this).val() !== "") {
          $(this).closest('.form-group').removeClass('has-error');
        }
      });
      // Cargar médicos de la especialidad actual al iniciar
      const espInicial = $('#id_especialidad').val();
      const medicoActual = "<?php echo $datos['Id_medico']; ?>";

      if (espInicial) {
        $.post('get/obtener_medicos_por_especialidad.php', {
          id_especialidad: espInicial
        }, function(data) {
          $('#id_medico').html(data);
          $('#id_medico').val(medicoActual); // Seleccionar el médico que ya tenía la cita
        });
      }

      // Lógica de cambio de especialidad (igual que en agregar.php)
      $('#id_especialidad').on('change', function() {
        const espId = $(this).val();
        if (espId) {
          $.post('get/obtener_medicos_por_especialidad.php', {
            id_especialidad: espId
          }, function(data) {
            $('#id_medico').html(data);
          });
        }
      });

      $('#formularioCita').on('submit', function(e) {
        e.preventDefault();
        limpiarErrores();
        let errores = [];

        if (!validarCamposCita()) {
          mostrarAviso("⚠️ Por favor, rellene todos los campos marcados en rojo.");
          return;
        }

        // 2. Validar Fin de Semana (NUEVO)
        const fechaVal = $('#fecha_cita').val();
        const d = new Date(fechaVal + 'T00:00:00');
        const dia = d.getUTCDay();
        if (dia === 0 || dia === 6) {
          mostrarAviso("🛑 La fecha seleccionada es un fin de semana. Por favor, elija un día laborable.");
          $('#group_fecha').addClass('has-error');
          return;
        }

        const horaSeleccionada = $('#hora_cita').val();
        if (!validarHorarioLaboral(horaSeleccionada)) {
          mostrarAviso("🛑 El horario de atención es únicamente de 9:00 AM a 1:00 PM.");
          $('#group_hora').addClass('has-error');
          return;
        } else {
          $('#modalGuardarCita').modal('show');
        }
      });

      $('#confirmarGuardarCita').on('click', function() {
        $('#modalGuardarCita').modal('hide');
        $('#formularioCita').off('submit').submit();
      });
    });
  </script>
</body>

</html>