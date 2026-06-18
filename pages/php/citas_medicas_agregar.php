<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Citas | Añadir</title>
  <?php
  include('includes/headerNav2.php');
  ?>
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
    .modal.in .modal-dialog, #avisoModal, #modalGuardarCita, #modalRegistroRapido, #modalConfirmacionMenor {
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
    #modalPatologias,
    #modalAlergias,
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
  </style>
</head>

<body>

  <div class="content-wrapper">
    <section class="content-header">
      <h1>Añadir Nueva Cita</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-calendar"></i>Citas</a></li>
        <li class="active">Añadir</li>
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
                  <form action="../../cfg/agregar/agregar_cita.php" id="formularioCita" class="form-group" method="POST" novalidate>
                    <div class="row">
                      <label class="control-label"></label>
                      <div class="col-sm-1 pull-left form-group" style="margin-top: 30px;" id="group_tipo_cedula">
                        <select name="tipo_cedula_paciente" id="tipo_cedula_paciente" class="form-control" style="width: 60px;">
                          <option value="PN">PN-</option>
                          <option value="V">V-</option>
                          <option value="RP">REP-</option>
                          <!--<option value="E">E-</option>-->
                        </select>
                      </div>
                      <div class="col-sm-3 form-group" id="group_cedula">
                        <p>Cédula/Documento (*)</p>
                        <input type="text" class="form-control" name="cedula_paciente" id="cedula_paciente" placeholder="N° de Cédula" maxlength="20" required>
                      </div>
                      <div class="col-sm-4 form-group" id="group_nombre_paciente">
                        <p>Nombre del Paciente (*)</p>
                        <input type="text" id="nombre_paciente" class="form-control" name="nombre_paciente" readonly placeholder="Nombre del Paciente" required>
                        <input type="hidden" name="id_paciente" id="id_paciente_hidden">
                      </div>
                      <div class="col-sm-4 form-group" id="group_especialidad">
                        <p>Especialidad (*):</p>
                        <select class="form-control" name="id_especialidad" id="id_especialidad" required>
                          <option value="">Seleccione especialidad</option>
                          <?php
                          include('../../cfg/conexion.php');
                          $sql_especialidad = "SELECT * FROM especialidad";
                          $row1 = $conexion->query($sql_especialidad);
                          while ($row = $row1->fetch_assoc()) {
                            echo "<option value='" . $row['Id_especialidad'] . "'>" . $row['nombre_especialidad'] . "</option>";
                          }
                          ?>
                        </select>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-sm-4 form-group" id="group_medico">
                        <p>Médico Especialista (*):</p>
                        <select class="form-control" name="id_medico" id="id_medico" required disabled>
                          <option value="">Seleccione primero una especialidad</option>
                        </select>
                      </div>

                      <div class="col-sm-4 form-group" id="group_fecha">
                        <p>Fecha (*):</p>
                        <input type="date" class="form-control" name="fecha_cita" id="fecha_cita" min="<?php echo date('Y-m-d'); ?>" required>
                      </div>

                      <div class="col-sm-4 form-group" id="group_hora">
                        <p>Hora (*):</p>
                        <input type="time" class="form-control" name="hora_cita" id="hora_cita" min="09:00" max="12:00" required>
                      </div>
                    </div>

                    <div class="row">

                      <div class="col-sm-12 form-group">
                        <p>Motivo de la consulta:</p>
                        <textarea class="form-control" name="motivo" id="motivo" rows="2" placeholder="Breve descripción..." required></textarea>
                      </div>
                    </div>
                    <div style="float:right; margin-top: 20px;">
                      <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#modalConfirmarRegreso">Regresar</button>
                      <button type="submit" class="btn btn-success" id="btnGuardarCita">Añadir Cita</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <?php include('includes/footer.php'); ?>
          </div>
        </div>
      </div>
    </section>

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

    <div class="modal" id="modalRegistroRapido" tabindex="-1" role="dialog" aria-labelledby="modalRegistroRapidoLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #00c0ef; color: white;">
            <h4 class="modal-title" id="modalRegistroRapidoLabel"><i class="fa fa-user-plus"></i> Añadir Nuevo Paciente</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body" id="contenidoModalRegistro">
            <p class="text-center"><i class="fa fa-spinner fa-spin fa-2x fa-fw"></i> Cargando Formulario...</p>
          </div>
        </div>
      </div>
    </div>

    <div class="modal" id="modalConfirmacionMenor" tabindex="-1" role="dialog" aria-labelledby="modalConfirmacionMenorLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">

          <div class="modal-header modal-header-danger">
            <h5 class="modal-title" id="modalConfirmacionMenorLabel"><i class="fa fa-question-circle"></i> Confirmacion de Tipo de Registro</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <div class="modal-body">
            <p id="textoConfirmacionMenor" class="text-center"></p>
            <hr>
            <p class="text-center">Por favor, seleccione el tipo de registro apropiado:</p>
          </div>

          <div class="modal-footer">
            <a href="pacientes_menores_agregar.php?pagina=cita" class="btn btn-warning">Registro del Menor</a>
            <a href="pacientes_agregar.php?pagina=cita" class="btn btn-primary">Registro Estandar</a>
            <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          </div>
        </div>
      </div>
    </div>

    <script>
      const cedulaPacienteInput = document.getElementById('cedula_paciente');
      const tipoCedulaPacienteSelect = document.getElementById('tipo_cedula_paciente');
      const nombrePacienteInput = document.getElementById('nombre_paciente');

      function mostrarAviso(texto, onHiddenCallBack = null) {
        // 1. Insertar el texto
        $('#avisoTexto').html(texto);

        // 2. Asegurarnos de que el botón de cerrar funcione manualmente por si falla el data-dismiss
        $('#avisoModal .close, #avisoModal .btn-secondary').off('click').on('click', function() {
          $('#avisoModal').modal('hide');
        });

        $('#modalGuardarCita .close, #modalGuardarCita .btn-second').off('click').on('click', function() {
          $('#modalGuardarCita').modal('hide');
        });

        // 3. Manejar el callback si existe
        if (onHiddenCallBack && typeof onHiddenCallBack === 'function') {
          $('#avisoModal').one('hidden.bs.modal', function() {
            onHiddenCallBack();
          });
        }

        // 4. Mostrar el modal
        $('#avisoModal').modal('show');
      }

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
          // Deja solo números primero
          nuevoValor = valor.replace(/[^0-9]/g, '');
          // Si pasa de 8 dígitos, inserta el guion antes del noveno
          if (nuevoValor.length > 8) {
            nuevoValor = nuevoValor.substring(0, 8) + '-' + nuevoValor.substring(8, 9);
          }
          longitudMaxima = 10; // 8 números + guion + 1 número
        } else if (tipo === 'PN') {
          nuevoValor = valor.replace(/[^0-9a-zA-Z- ]/g, '');
          longitudMaxima = 20;
        }

        cedulaPacienteInput.maxLength = longitudMaxima;
        cedulaPacienteInput.value = nuevoValor;
      }

      function aplicarRestricciones() {
        $('#cedula_paciente').on('input', filtrarCedulaCita);
        $('#tipo_cedula_paciente').on('change', function() {
           $('#cedula_paciente').val(''); // Limpiamos al cambiar para evitar choques
           filtrarCedulaCita();
        });
      }

      function ajustarLongitudCedula(tipo) {
        let maxLength = (tipo === 'PN') ? 20 : 8;

        // Aplicamos el límite físico al input
        cedulaPacienteInput.setAttribute('maxlength', maxLength);

        // Si al cambiar el tipo, el texto actual supera el nuevo límite, lo cortamos inmediatamente
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
                mensaje: `Formato incorrecto. El documento REP debe tener el formato 12345678-1.`
             };
           }
           return { valido: true };
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
        const minRequired = 1; // El mínimo que pediste

        if (longitud > 0 && longitud < minRequired) {
          $(this).closest('.form-group').addClass('has-error');
          // Opcional: un pequeño texto bajo el input que diga "Mínimo 2 caracteres"
        } else {
          $(this).closest('.form-group').removeClass('has-error');
        }
      });

      $('#hora_cita').on('change', function() {
        const horaVal = $(this).val(); // Retorna "HH:mm"
        if (!horaVal) return;

        const [horas, minutos] = horaVal.split(':').map(Number);
        const tiempoDecimal = horas + (minutos / 60);

        const horaInicio = 9.0; // 09:00 AM
        const horaFin = 12.0; // 12:00 PM

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

      // AÑADIDO: Nueva función para cargar el formulario de registro en el modal
      function cargarFormularioRegistro(tipo, cedula) {
        const modalBody = $('#contenidoModalRegistro');
        const $avisoModal = $('#avisoModal'); // Referencia al modal de error

        // 1. Limpiamos y mostramos un loader
        modalBody.html('<div class="text-center" style="padding: 30px;"><i class="fa fa-spinner fa-spin fa-3x fa-fw"></i> Cargando Formulario de Registro...</div>');

        // 2. Cargamos el contenido del archivo externo (Asegúrate de que esta ruta sea correcta)
        $.get('modales/consultas/modal_pacientes_agregar_consulta.php', {
          tipo_cedula: tipo,
          cedula: cedula
        }, function(html_content) {
          modalBody.html(html_content);

          // --- CAMBIO CLAVE: CERRAR EL MODAL DE AVISO/ERROR ANTES DE MOSTRAR EL DE REGISTRO ---
          closeCustomModal($avisoModal);

          // 3. Mostrar el modal de registro
          $('#modalRegistroRapido').modal('show');
          $('#cedula_paciente').val(cedula);
          $('#tipo_cedula_paciente').val(tipo);
        }).fail(function() {
          modalBody.html('<p class="alert alert-danger">Error al cargar el formulario de registro. Verifique la ruta del archivo `formulario_registro_paciente.php`.</p>');
          // Mostrar el modal de registro (o dejar que el aviso se muestre)
          $('#modalRegistroRapido').modal('show');
        });
      }

      // AÑADIDO: Nueva función para cargar el formulario de registro en el modal
      function cargarFormularioRegistroMenor(tipo, cedula) {
        const modalBody = $('#contenidoModalRegistro');
        const $avisoModal = $('#avisoModal'); // Referencia al modal de error

        // 1. Limpiamos y mostramos un loader
        modalBody.html('<div class="text-center" style="padding: 30px;"><i class="fa fa-spinner fa-spin fa-3x fa-fw"></i> Cargando Formulario de Registro...</div>');

        // 2. Cargamos el contenido del archivo externo (Asegúrate de que esta ruta sea correcta)
        $.get('modales/consultas/modal_pacientes_menores_agregar_consulta.php', {
          tipo_cedula: tipo,
          cedula: cedula
        }, function(html_content) {
          modalBody.html(html_content);

          // --- CAMBIO CLAVE: CERRAR EL MODAL DE AVISO/ERROR ANTES DE MOSTRAR EL DE REGISTRO ---
          closeCustomModal($avisoModal);

          // 3. Mostrar el modal de registro
          $('#modalRegistroRapido').modal('show');

        }).fail(function() {
          modalBody.html('<p class="alert alert-danger">Error al cargar el formulario de registro. Verifique la ruta del archivo `formulario_registro_paciente.php`.</p>');
          // Mostrar el modal de registro (o dejar que el aviso se muestre)
          $('#modalRegistroRapido').modal('show');
        });
      }

      async function verificarCedulaYObtenerDatos(tipo, cedula) {
        if (cedula.length < 1) return;

        $.ajax({
          url: 'get/get_paciente.php', // Asegúrate de que esta ruta sea correcta
          method: 'POST',
          dataType: 'json',
          data: {
            tipo_cedula: tipo,
            cedula: cedula
          },
          success: function(response) {
            if (response.existe) {
              // PACIENTE ENCONTRADO
              $('#nombre_paciente').val(response.nombre_completo).removeClass('input-error');
              $('#id_paciente_hidden').val(response.id); // Guardamos el ID para el INSERT
              $('#cedula_paciente').removeClass('input-error');
            } else {
              // PACIENTE NO ENCONTRADO
              $('#nombre_paciente').val('¡Paciente no encontrado!');
              $('#cedula_paciente').addClass('input-error');
              $('#id_paciente_hidden').val('');

              determinarTipoRegistro(tipo, cedula);
            }
          },
          error: function() {
            mostrarAviso('⚠️ Error de conexión al verificar el paciente.');
          }
        });
      }

      function validarCamposCita() {
        let errores = 0;
        // Lista de IDs a validar
        const campos1 = ['cedula_paciente', 'id_especialidad', 'id_medico', 'fecha_cita', 'hora_cita', 'motivo'];

        campos1.forEach(id => {
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

      $(document).ready(function() {
        aplicarRestricciones();
        $('.form-control').on('input change', function() {
          if ($(this).val() !== "") {
            $(this).closest('.form-group').removeClass('has-error');
          }
        });
        // --- Función para cargar médicos según especialidad ---
        $('#id_especialidad').on('change', function() {
          const espId = $(this).val();
          if (espId) {
            $('#id_medico').prop('disabled', false).html('<option value="">Cargando médicos...</option>');
            $.post('get/obtener_medicos_por_especialidad.php', {
              id_especialidad: espId
            }, function(data) {
              $('#id_medico').html(data);
            });
          } else {
            $('#id_medico').prop('disabled', true).html('<option value="">Seleccione primero una especialidad</option>');
          }
        });

        // LÓGICA DE VERIFICACIÓN DE DISPONIBILIDAD (AJAX)
        function verificarDisponibilidadYEnviar() {
          const btnGuardarCita = $('#confirmarGuardarCita');
          const datos = {
            id_medico: $('#id_medico').val(),
            fecha: $('#fecha_cita').val(),
            hora: $('#hora_cita').val()
          };

          btnGuardarCita.text('Verificando disponibilidad...').attr('disabled', true);

          $.ajax({
            url: 'get/verificar_disponibilidad_cita.php',
            method: 'POST',
            dataType: 'json',
            data: datos,
            success: function(response) {
              btnGuardarCita.text('Confirmar').attr('disabled', false);
              if (response.ocupado) {
                mostrarAviso('🛑 El médico ya tiene una cita programada para esa fecha y hora.');
                $('#group_fecha, #group_hora').addClass('has-error');
              } else {
                $('#formularioCita').off('submit').submit();
              }
            },
            error: function() {
              btnGuardarCita.text('Confirmar').attr('disabled', false);
              mostrarAviso('🛑 Error al conectar con el servidor.');
            }
          });
        }

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
          
          } else {
            $('#modalGuardarCita').modal('show');
          }
        });

        $('#cedula_paciente').on('blur', function() {
          const tipo = $('#tipo_cedula_paciente').val();
          const cedula = $(this).val();
          if (cedula.length >= 1) {
            verificarCedulaYObtenerDatos(tipo, cedula);
          }
        });

        // Al cambiar el tipo (V/E/PN) también re-verifica si hay algo escrito
        $('#tipo_cedula_paciente').on('change', function() {
          const cedula = $('#cedula_paciente').val();
          if (cedula.length >= 7) {
            verificarCedulaYObtenerDatos($(this).val(), cedula);
          }
        });

        // Llamadas de inicialización
        ajustarLongitudCedula(tipoCedulaPacienteSelect.value);
        aplicarRestricciones();

        $('#confirmarGuardarCita').on('click', function() {
          $('#modalGuardarCita').modal('hide');
          verificarDisponibilidadYEnviar();
        });
      });
    </script>
    <script>
      // AÑADIDO: Función para determinar el flujo de registro cuando la cédula es ambigua (V o E)
      function determinarTipoRegistro(tipo, cedula) {
        const $modal = $('#modalConfirmacionMenor');
        const $texto = $('#textoConfirmacionMenor');

        $texto.html(`El paciente con Documento ${tipo}-${cedula} NO existe en la base de datos.`);

        // Mostramos el modal
        $modal.modal('show');

        // Forzar que el botón "Cerrar" (el de la X o el de cancelar) funcione
        $modal.find('[data-dismiss="modal"]').off('click').on('click', function() {
          $modal.modal('hide');
        });

        const cleanupAndClose = () => {
          $('#btnConfirmarRegistroMenor').off('click');
          $('#btnConfirmarRegistroAdulto').off('click');
          $modal.modal('hide');
        };

        $('#btnConfirmarRegistroMenor').on('click', function() {
          cleanupAndClose();
          cargarFormularioRegistroMenor(tipo, cedula);
        });

        $('#btnConfirmarRegistroAdulto').on('click', function() {
          cleanupAndClose();
          cargarFormularioRegistro(tipo, cedula);
          // Aquí llamas a tu función de registro estándar
        });
      }
    </script>
</body>

</html>