<div class="modal fade" id="modalAgregarMedicoExterno" tabindex="-1" role="dialog" aria-labelledby="modalAgregarMedicoLabel" aria-hidden="true" data-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 1;">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title" style="color: white;"><i class="fa fa-user-md"></i> Añadir Médico Externo</h4>
      </div>
      <div class="modal-body">
        <form id="formMedicoExterno" class="form-group" method="POST">
          <div class="row">
            <div class="col-sm-2" style="margin-top: 30px;">
              <select name="tipo_cedula_med" id="tipo_cedula_med" class="form-control">
                <option value="V">V-</option>
              </select>
            </div>
            <div class="col-sm-4">
              <p>Cedula (*)</p>
              <input type="text" class="form-control" name="cedula_med" id="cedula_med" placeholder="N° de Cedula" required>
            </div>
            <div class="col-sm-6">
              <p>Nombres (*):</p>
              <input type="text" class="form-control" name="nombre_med" id="nombre_med" placeholder="Nombres del Médico" required>
            </div>
          </div>
          <br>
          <div class="row">
            <div class="col-sm-6">
              <p>Apellidos:</p>
              <input type="text" class="form-control" name="apellido_med" id="apellido_med" placeholder="Apellidos del Médico">
            </div>
            <div class="col-sm-4">
              <p>Fecha de nacimiento (*):</p>
              <input type="date" class="form-control" id="fechaN_med" name="fecha_nacimiento_med" onchange="calcularEdadMed()" required>
            </div>
            <div class="col-sm-2" style="margin-top: 30px;">
              <input type="text" class="form-control" id="edad_med" name="edad_med" readonly placeholder="Edad">
            </div>
          </div>
          <br>
          <div class="row">
            <div class="col-sm-2" style="margin-top: 30px;">
              <select name="prefijo_med" id="prefijo_med" class="form-control" required>
                <?php
                // Asegúrate de que $conexion esté disponible aquí
                $sql_pref = $conexion->query("SELECT * FROM prefijos_telefonos");
                while ($resultado = $sql_pref->fetch_assoc()) {
                  echo "<option value='" . $resultado["Id"] . "'>" . $resultado['prefijo'] . "</option>";
                }
                ?>
              </select>
            </div>
            <div class="col-sm-4">
              <p>Telefono (*):</p>
              <input type="text" class="form-control" name="telefono_med" id="telefono_med" placeholder="N° De Telefono" required>
            </div>
            <div class="col-sm-3">
              <p>Sexo (*):</p>
              <select name="genero_med" id="genero_med" class="form-control" required>
                <option value="">--- Seleccione ---</option>
                <option value="Masculino">Masculino</option>
                <option value="Femenino">Femenino</option>
              </select>
            </div>
            <div class="col-sm-3">
              <p>Email (*):</p>
              <input type="email" class="form-control" name="correo_med" id="correo_med" placeholder="ejemplo@dominio.com" required>
            </div>
          </div>
          <br>
          <div class="row">
            <div class="col-sm-6">
              <p>Número de colegiatura (*):</p>
              <input type="text" class="form-control" name="cod_colegiatura" id="cod_colegiatura" minlength="4" maxlength="7" placeholder="Ingrese solo numeros (entre 4 y 7 digitos)" oninput="this.value = this.value.replace(/[^0-9]/g, '');" required>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" id="btnGuardarMedicoExterno">Guardar y Usar</button>
      </div>
    </div>
  </div>
</div>

<script>
  // =====================================================================
  // VALIDACIONES PARA MÉDICO EXTERNO
  // =====================================================================

  // 1. Solo Letras y Números
  function soloLetrasMed(inputElement) {
    if (!inputElement) return;
    inputElement.addEventListener('input', function() {
      this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
    });
  }
  soloLetrasMed(document.getElementById('nombre_med'));
  soloLetrasMed(document.getElementById('apellido_med'));

  function soloNumerosSinEMed(campo, maxDigitos) {
    if (!campo) return;
    campo.addEventListener("keydown", function(e) {
      const teclasPermitidas = ["Backspace", "Tab", "ArrowLeft", "ArrowRight", "Delete"];
      if (teclasPermitidas.includes(e.key)) return;
      if (e.key.toLowerCase() === "e") {
        e.preventDefault();
        return;
      }
      if (!/^[0-9]$/.test(e.key)) {
        e.preventDefault();
        return;
      }
      if (campo.value.length >= maxDigitos) {
        e.preventDefault();
      }
    });
    campo.addEventListener("input", function() {
      campo.value = campo.value.replace(/[^0-9]/g, "").slice(0, maxDigitos);
    });
  }
  soloNumerosSinEMed(document.getElementById("cedula_med"), 8);
  soloNumerosSinEMed(document.getElementById("telefono_med"), 7);

  $(document).ready(function() {
    // 1. Establecer la fecha máxima (Mínimo 18 años atrás)
    $('#fechaN_med').attr('max', getMaxDateNacimientoMed());

    // 2. Bloquear la escritura manual pero mantener el input activo para clics
    $('#fechaN_med').on('keydown keypress keyup', function(e) {
      // Permitir solo la tecla Tab (código 9) para navegación y evitar bloqueos
      if (e.which !== 9) {
        e.preventDefault();
        return false;
      }
    });
  });

  // 2. Cálculo y Restricción de Edad (Mínimo 20 años)
  function getMaxDateNacimientoMed() {
    const today = new Date();
    const maxNacimiento = new Date(today.getFullYear() - 20, today.getMonth(), today.getDate());
    const year = maxNacimiento.getFullYear();
    const month = String(maxNacimiento.getMonth() + 1).padStart(2, '0');
    const day = String(maxNacimiento.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }

  const fechaNacimientoMedInput = document.getElementById("fechaN_med");
  if (fechaNacimientoMedInput) {
    fechaNacimientoMedInput.max = getMaxDateNacimientoMed();
  }

  function calcularEdadMed() {
    const input = document.getElementById("fechaN_med").value;
    if (input) {
      const nacimiento = new Date(input);
      const hoy = new Date();
      let edad = hoy.getFullYear() - nacimiento.getFullYear();
      const mesActual = hoy.getMonth();
      const mesNacimiento = nacimiento.getMonth();
      if (mesActual < mesNacimiento || (mesActual === mesNacimiento && hoy.getDate() < nacimiento.getDate())) {
        edad--;
      }
      document.getElementById("edad_med").value = edad;
    }
  }

  async function verificarCedulaModal(tipo, cedula) {
    return new Promise((resolve, reject) => {
      if (cedula === "" || tipo === "") {
        resolve(false);
        return;
      }
      $.ajax({
        url: 'get/verificar_existencia_cedula.php',
        method: 'POST',
        dataType: 'json',
        data: {
          tipo_cedula: tipo,
          cedula: cedula
        },
        success: function(response) {
          // CORRECCIÓN: El PHP devuelve existe_cedula[cite: 3, 4]
          resolve(response.existe_cedula);
        },
        error: function(xhr, status, error) {
          console.error("Error al verificar cédula:", error);
          reject('Error al conectar con el servidor.');
        }
      });
    });
  }

  async function validarCedulaModal() {
    const cedulaInput = $('#cedula_med');
    const tipoSelect = $('#tipo_cedula_med');
    const cedula = parseInt(cedulaInput.val().trim());
    const tipo = tipoSelect.val();

    // Limpiar error previo
    cedulaInput.removeClass('input-error');

    if (isNaN(cedula) || cedulaInput.val().trim() === "") return true;

    try {
      const existe = await verificarCedulaModal(tipo, cedula);
      if (existe) {
        cedulaInput.addClass('input-error');
        mostrarAviso('🛑 La cédula ' + tipo + '-' + cedula + ' ya se encuentra registrada en el sistema.');
        return false;
      }
    } catch (error) {
      console.error(error);
    }
    return true;
  }

  // Disparar la validación cuando el usuario sale del input de cédula o cambia el tipo
  $('#cedula_med').on('blur', validarCedulaModal);
  $('#tipo_cedula_med').on('change', validarCedulaModal);

  // 3. Validación de Cédula (Rangos)
  function validarCedulaMed() {
    const tipo = document.getElementById('tipo_cedula_med').value;
    const cedulaInput = document.getElementById('cedula_med');
    const cedula = parseInt(cedulaInput.value, 10);

    if (isNaN(cedula) || cedulaInput.value.trim() === "") return true;

    if (tipo === 'V' && cedula > 80000000) {
      $(cedulaInput).addClass('input-error');
      mostrarAviso('⚠️ Error de Cédula: Para el tipo V-, la cédula no puede ser mayor a 80.000.000.');
      return false;
    } else if (tipo === 'E' && cedula < 80000000) {
      $(cedulaInput).addClass('input-error');
      mostrarAviso('⚠️ Error de Cédula: Para el tipo E-, la cédula no puede ser menor a 80.000.000.');
      return false;
    }
    return true;
  }
  $('#cedula_med').on('blur', validarCedulaMed);
  $('#tipo_cedula_med').on('change', validarCedulaMed);

  // 4. ENVÍO POR AJAX
  $('#btnGuardarMedicoExterno').on('click', function(e) {
    e.preventDefault();
    $('.modal-body input, .modal-body select').removeClass('input-error');

    // Validar campos vacíos
    let formularioValido = true;
    $('#formMedicoExterno [required]').each(function() {
      if ($(this).val() === null || $(this).val().trim() === "" || $(this).val() === "--- Seleccione ---") {
        $(this).addClass('input-error');
        formularioValido = false;
      }
    });

    if (!formularioValido) {
      mostrarAviso('⚠️ Error: Todos los campos obligatorios (*) deben estar llenos.');
      return;
    }

    // NUEVO: Validar estrictamente que tenga al menos 18 años antes de enviar
    let edad_calculada = parseInt($('#edad_med').val());
    if (isNaN(edad_calculada) || edad_calculada < 20) {
      $('#fechaN_med').addClass('input-error');
      mostrarAviso('🛑 Error: El médico no puede ser menor de 20 años.');
      return;
    }

    // ELIMINADO: if (!verificarCedulaModal()) return; (Estaba mal llamado y rompía el código)[cite: 4]

    if (!validarCedulaMed()) return;

    // Validar Email
    var email = $('#correo_med').val();
    if (email && !email.includes('@')) {
      $('#correo_med').addClass('input-error');
      mostrarAviso('🚫 Error de Email: El campo de Email debe contener "@".');
      return;
    }

    // Comprobar cédula existente y guardar
    $.ajax({
      url: 'get/verificar_existencia_cedula.php',
      method: 'POST',
      dataType: 'json',
      data: {
        tipo_cedula: $('#tipo_cedula_med').val(),
        cedula: $('#cedula_med').val()
      },
      success: function(response) {
        // CORRECCIÓN: Usar response.existe_cedula tal como lo manda el PHP[cite: 3, 4]
        if (response.existe_cedula) {
          $('#cedula_med').addClass('input-error');
          mostrarAviso('🛑 Cédula Existente: Esta cédula ya está registrada.');
        } else {
          // Si no existe, guardamos mediante AJAX
          $.ajax({
            url: '../../cfg/ajax/guardar_medico_externo_ajax.php',
            method: 'POST',
            data: $('#formMedicoExterno').serialize(),
            dataType: 'json',
            success: function(res) {
              if (res.success) {
                $('#modalAgregarMedicoExterno').modal('hide');
                // 1. Obtener valores del modal
                let tipoCedula = $('#tipo_cedula_med').val();
                let cedula = $('#cedula_med').val();
                let nombreCompleto = $('#nombre_med').val() + ' ' + $('#apellido_med').val();

                // 2. Rellenar los campos en el formulario principal
                $('#tipo_cedula_medico').val(tipoCedula);
                $('#busqueda_cedula_medico').val(cedula);
                $('#medico_externo').val(nombreCompleto.trim());

                // 3. Bloquear los campos visualmente y funcionalmente
                $('#tipo_cedula_medico').css({
                  'pointer-events': 'none',
                  'background-color': '#e9ecef'
                });
                $('#busqueda_cedula_medico').prop('readonly', true).css('background-color', '#e9ecef');
                $('#medico_externo').prop('readonly', true).css('background-color', '#e9ecef');

                // 4. Limpiar y cerrar modal
                $('#formMedicoExterno')[0].reset();        
                mostrarExito('✅ Médico registrado exitosamente.');
              } else {
                mostrarAviso('❌ Error: ' + res.message);
              }
            }
          });
        }
      },
      error: function() {
        mostrarAviso('❌ Error al verificar la cédula con el servidor.');
      }
    });
  });
</script>