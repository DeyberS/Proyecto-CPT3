<div class="modal" id="modalAgregarPacienteMenor" role="dialog" data-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <button type="button" class="close text-white" data-dismiss="modal" style="color: white; opacity: 1;">&times;</button>
        <h4 class="modal-title" style="color: white;"><i class="fa fa-child"></i> Registrar Paciente Menor Externo</h4>
      </div>
      <div class="modal-body">
        <form id="formAjaxPacienteMenor">

          <h5 style="color: #3c8dbc; font-weight: bold; border-bottom: 1px solid #ddd; padding-bottom: 5px;">Datos del Paciente Menor</h5>
          <div class="row">
            <div class="col-sm-2 form-group">
              <label>Tipo (*):</label>
              <select name="menor_tipo_cedula" id="menor_tipo_cedula" class="form-control" required>
                <option value="PN">PN-</option>
                <option value="V">V-</option>
                <option value="RP">REP-</option>
              </select>
            </div>
            <div class="col-sm-4 form-group">
              <label>Documento/Cédula (*):</label>
              <input type="text" class="form-control" name="menor_cedula" id="menor_cedula" placeholder="N° de Documento" maxlength="20" required>
            </div>
            <div class="col-sm-3 form-group">
              <label>Nombre (*):</label>
              <input type="text" class="form-control solo-letras-menor" name="menor_nombre" id="menor_nombre" placeholder="Nombre" maxlength="100" required>
            </div>
            <div class="col-sm-3 form-group">
              <label>Apellido:</label>
              <input type="text" class="form-control solo-letras-menor" name="menor_apellido" id="menor_apellido" placeholder="Apellido" maxlength="100">
            </div>
          </div>

          <div class="row">
            <div class="col-sm-4 form-group">
              <label>F. Nacimiento (*):</label>
              <input type="date" class="form-control" id="menor_fecha_nacimiento" name="menor_fecha_nacimiento" required>
            </div>
            <div class="col-sm-2 form-group">
              <label>Edad:</label>
              <input type="text" class="form-control" id="menor_edad" readonly>
            </div>
            <div class="col-sm-3 form-group">
              <label>Sexo (*):</label>
              <select name="menor_genero" id="menor_genero" class="form-control" required>
                <option value="">--- Seleccione ---</option>
                <option value="Masculino">Masculino</option>
                <option value="Femenino">Femenino</option>
              </select>
            </div>
            <div class="col-sm-3 form-group">
              <label>Grupo sanguíneo:</label>
              <select name="menor_grupo_sanguineo" id="menor_grupo_sanguineo" class="form-control" required>
                <option value="">--- Seleccione ---</option>
                <option value="A+">A+</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B-">B-</option>
                <option value="O+">O+</option>
                <option value="O-">O-</option>
                <option value="AB+">AB+</option>
                <option value="AB-">AB-</option>
              </select>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-2 form-group">
              <label>Etnia:</label>
              <select name="menor_etnia" id="menor_etnia" class="form-control">
                <option value="No">No</option>
                <option value="Si">Si</option>
              </select>
            </div>
            <div class="col-sm-2 form-group">
              <label>Tipo Etnia:</label>
              <select name="menor_tipo_etnia" id="menor_tipo_etnia" class="form-control" disabled>
                <option value="Ninguna">--- Seleccione ---</option>
                <option value="wayuu">Wayuu</option>
                <option value="añu">Añu</option>
                <option value="baniva">Baniva</option>
                <option value="kurripako">Kurripako</option>
                <option value="piapoco">Piapoco</option>
                <option value="warekena">Warekena</option>
                <option value="bare">Bare</option>
                <option value="pemon">Pemon</option>
                <option value="kariña">Kariña</option>
                <option value="panare">Panare</option>
                <option value="yukpa">Yukpa</option>
                <option value="japreira">Japreira</option>
                <option value="yekuana">Yekuana</option>
                <option value="chaima">Chaima</option>
                <option value="bari">Barí</option>
                <option value="yanomami">Yanomami</option>
                <option value="sanema">Sanema</option>
                <option value="warao">Warao</option>
                <option value="pume">Pumé</option>
                <option value="piaroa">Piaroa</option>
                <option value="otro">Otro/No Aplica</option>
              </select>
            </div>
            <div class="col-sm-2 form-group">
              <label>Discap.:</label>
              <select name="menor_discapacidad" id="menor_discapacidad" class="form-control">
                <option value="No">No</option>
                <option value="Si">Si</option>
              </select>
            </div>
            <div class="col-sm-3 form-group">
              <label>Tipo Discap.:</label>
              <select name="menor_tipo_discapacidad" id="menor_tipo_discapacidad" class="form-control" disabled>
                <option value="Ninguna">--- Seleccione ---</option>
                <option value="fisico_motora">Físico-Motora</option>
                <option value="visual">Visual</option>
                <option value="auditiva">Auditiva</option>
                <option value="intelectual">Intelectual</option>
                <option value="psicosocial">Psicosocial</option>
                <option value="multiple">Múltiple</option>
              </select>
            </div>
            <div class="col-sm-3 form-group">
              <label>Analfabeta:</label>
              <select name="menor_analfabeta" id="menor_analfabeta" class="form-control">
                <option value="No">No</option>
                <option value="Si">Si</option>
              </select>
            </div>
          </div>

          <h5 style="color: #00a65a; font-weight: bold; border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-top: 15px;">Datos del Representante Legal</h5>
          <div class="row">
            <div class="col-sm-2 form-group">
              <label>Tipo (*):</label>
              <select name="rep_tipo_cedula" id="rep_tipo_cedula" class="form-control" required>
                <option value="V">V-</option>
                <option value="E">E-</option>
              </select>
            </div>
            <div class="col-sm-4 form-group">
              <label>Cédula Rep. (*):</label>
              <input type="text" class="form-control solo-numeros-menor" name="rep_cedula" id="rep_cedula" placeholder="Buscar Cédula..." maxlength="8" required>
            </div>
            <div class="col-sm-3 form-group">
              <label>Nombre (*):</label>
              <input type="text" class="form-control solo-letras-menor rep-auto" name="rep_nombre" id="rep_nombre" placeholder="Nombre" maxlength="100" required>
            </div>
            <div class="col-sm-3 form-group">
              <label>Apellido (*):</label>
              <input type="text" class="form-control solo-letras-menor rep-auto" name="rep_apellido" id="rep_apellido" placeholder="Apellido" maxlength="100" required>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-4 form-group">
              <label>F. Nacimiento (*):</label>
              <input type="date" class="form-control rep-auto" id="rep_fecha_nacimiento" name="rep_fecha_nacimiento" required>
            </div>
            <div class="col-sm-2 form-group">
              <label>Edad:</label>
              <input type="text" class="form-control" id="rep_edad" readonly>
            </div>
            <div class="col-sm-3 form-group">
              <label>Prefijo (*):</label>
              <select name="rep_prefijo" id="rep_prefijo" class="form-control rep-auto" required>
                <option value="">Prefijo</option>
                <?php
                if (isset($conexion)) {
                  $res_pref = $conexion->query("SELECT * FROM prefijos_telefonos WHERE estatus = '1'");
                  while ($row_pref = $res_pref->fetch_assoc()) {
                    echo "<option value='" . $row_pref["Id"] . "'>" . $row_pref['prefijo'] . "</option>";
                  }
                }
                ?>
              </select>
            </div>
            <div class="col-sm-3 form-group">
              <label>Teléfono (*):</label>
              <input type="text" class="form-control solo-numeros-menor rep-auto" name="rep_telefono" id="rep_telefono" placeholder="Número" maxlength="7" required>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-4 form-group">
              <label>Sexo (*):</label>
              <select name="rep_genero" id="rep_genero" class="form-control rep-auto" required>
                <option value="">--- Seleccione ---</option>
                <option value="Masculino">Masculino</option>
                <option value="Femenino">Femenino</option>
              </select>
            </div>
            <div class="col-sm-5 form-group">
              <label>Email:</label>
              <input type="email" class="form-control rep-auto" name="rep_email" id="rep_email" placeholder="correo@ejemplo.com">
            </div>
            <div class="col-sm-3 form-group">
              <label>Parentesco (*):</label>
              <select name="rep_parentesco" id="rep_parentesco" class="form-control" required>
                <option value="">--- Seleccione Parentesco ---</option>
                <option value="Padre">Padre</option>
                <option value="Madre">Madre</option>
                <option value="Abuelo(a)">Abuelo(a)</option>
                <option value="Tío(a)">Tío(a)</option>
                <option value="Tutor Legal">Tutor Legal</option>
                <option value="Otro">Otro</option>
              </select>
            </div>
          </div>

        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" id="btnGuardarMenorAjax"><i class="fa fa-save"></i> Guardar y Usar</button>
      </div>
    </div>
  </div>
</div>

<script>
  $(document).ready(function() {

    $(document).ready(function() {
      // 1. Control estricto de fechas para el Menor de Edad
      // =====================================================================
      // 1. CONTROL ESTRICTO DE CALENDARIOS (MENOR Y REPRESENTANTE)
      // =====================================================================
      let fechaActualInit = new Date();

      // Calculamos exactamente hace 18 años
      let fechaLimite18 = new Date();
      fechaLimite18.setFullYear(fechaActualInit.getFullYear() - 18);
      let maxFechaAdulto = fechaLimite18.toISOString().split('T')[0]; // Fecha máxima permitida para el Representante

      // Ajustamos +1 día para el mínimo del menor (precisión jurídica)
      let minFechaMenorObj = new Date(fechaLimite18);
      minFechaMenorObj.setDate(minFechaMenorObj.getDate() + 1);
      let minFechaMenor = minFechaMenorObj.toISOString().split('T')[0];

      // Fecha de hoy (Máximo para el menor)
      let hoyActual = fechaActualInit.toISOString().split('T')[0];

      // Aplicar límites:
      // El menor debe haber nacido entre hace 18 años y hoy
      $('#menor_fecha_nacimiento').attr('min', minFechaMenor).attr('max', hoyActual);

      // El representante DEBE tener más de 18 años (El calendario no mostrará fechas recientes)
      $('#rep_fecha_nacimiento').attr('max', maxFechaAdulto);

      // Bloquear el teclado en ambos inputs para forzar el uso del calendario nativo
      $('#menor_fecha_nacimiento, #rep_fecha_nacimiento').on('keydown keypress keyup', function(e) {
        if (e.which !== 9) {
          e.preventDefault();
          return false;
        }
      });
    });

    // 1. RESTRICCIONES DE TECLADO
    $('.solo-numeros-menor').on('input', function() {
      this.value = this.value.replace(/[^0-9]/g, '');
    });
    $('.solo-letras-menor').on('input', function() {
      this.value = this.value.replace(/[0-9]/g, '');
    });

    $('#menor_cedula').on('input', function() {
      if ($('#menor_tipo_cedula').val() === 'V') {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8);
      } else {
        this.value = this.value.replace(/[^0-9a-zA-Z- ]/g, '').slice(0, 20);
      }
    });

    // 1. CONTROL DE ENTRADA Y AUTO-FORMATEO MIENTRAS SE ESCRIBE
    $('#menor_cedula').on('input', function() {
      let tipo = $('#menor_tipo_cedula').val();

      if (tipo === 'V') {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8);
      } else if (tipo === 'PN') {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 20);
      } else if (tipo === 'RP') {
        // Quitamos todo lo que no sea número
        let valor = this.value.replace(/[^0-9]/g, '');

        // Si el usuario escribe más de 8 números, inyectamos el guion automáticamente
        if (valor.length > 8) {
          valor = valor.substring(0, 8) + '-' + valor.substring(8, 9);
        }

        // Al reconstruir la cadena así, el máximo siempre será de 10 caracteres (8 num + 1 guion + 1 num)
        this.value = valor;
      }
    });

    // 2. ADVERTENCIA DE FORMATO INCORRECTO AL SALIR DEL CAMPO (BLUR)
    $('#menor_cedula').on('blur', function() {
      let tipo = $('#menor_tipo_cedula').val();
      let cedula = $(this).val();

      if (tipo === 'RP' && cedula.length > 0) {
        // Expresión regular: Exactamente 8 dígitos, un guion, y 1 dígito al final
        var regexRP = /^[0-9]{8}-[0-9]{1}$/;
        if (!regexRP.test(cedula)) {
          mostrarAviso("🛑 Formato incorrecto. Para el documento de Representante (RP), debe colocar los 8 números de la cédula seguidos del número de hijo.<br><br>Ejemplo: <b>12345678-1</b>");
          $(this).addClass('input-error');
        } else {
          $(this).removeClass('input-error');
        }
      }
    });

    $('#menor_discapacidad').change(function() {
      if ($(this).val() === 'Si') {
        $('#menor_tipo_discapacidad').prop('disabled', false).val('');
      } else {
        $('#menor_tipo_discapacidad').prop('disabled', true).val('Ninguna');
      }
    });

    // =====================================================================
    // 1. CÁLCULO DE EDADES DETALLADO (AÑOS, MESES O DÍAS)
    // =====================================================================
    const fechaActual = new Date();
    const hoyStr = fechaActual.toISOString().split('T')[0];

    // Calculamos EXACTAMENTE hace 18 años para el representante
    const hace18Anios = new Date(fechaActual.getFullYear() - 18, fechaActual.getMonth(), fechaActual.getDate());
    const fechaMaxRep = hace18Anios.toISOString().split('T')[0];

    // Aplicar límites a los calendarios
    $('#rep_fecha_nacimiento').attr('max', fechaMaxRep);

    // Nueva función para calcular edad detallada en menores
    function calcularEdadMenorDetallada(fechaNacStr) {
      if (!fechaNacStr) return {
        totalAnios: null,
        texto: ''
      };

      // Forzamos formato local evitando desfases de zona horaria
      let cumple = new Date(fechaNacStr + 'T00:00:00');
      let ahora = new Date();

      let anios = ahora.getFullYear() - cumple.getFullYear();
      let meses = ahora.getMonth() - cumple.getMonth();
      let dias = ahora.getDate() - cumple.getDate();

      if (dias < 0) {
        let mesAnterior = new Date(ahora.getFullYear(), ahora.getMonth(), 0);
        dias += mesAnterior.getDate();
        meses--;
      }
      if (meses < 0) {
        meses += 12;
        anios--;
      }

      let texto = '';
      if (anios >= 1) {
        texto = anios + (anios === 1 ? " año" : " años");
      } else if (meses >= 1) {
        texto = meses + (meses === 1 ? " mes" : " meses");
      } else {
        texto = dias + (dias === 1 ? " día" : " días");
      }

      return {
        totalAnios: anios,
        texto: texto
      };
    }

    // Función genérica para la edad del representante
    function calcularEdadInput(fechaNacStr, targetInput) {
      if (!fechaNacStr) {
        $(targetInput).val('');
        return null;
      }
      let cumple = new Date(fechaNacStr + 'T00:00:00');
      let edad = fechaActual.getFullYear() - cumple.getFullYear();
      let m = fechaActual.getMonth() - cumple.getMonth();
      if (m < 0 || (m === 0 && fechaActual.getDate() < cumple.getDate())) {
        edad--;
      }
      $(targetInput).val(edad);
      return edad;
    }

    // Evento de cambio de fecha para el Menor
    $('#menor_fecha_nacimiento').on('change', function() {
      let resultado = calcularEdadMenorDetallada($(this).val());

      if (resultado.totalAnios !== null) {
        if (resultado.totalAnios >= 18) {
          mostrarAviso("🛑 El paciente debe ser menor de 18 años.");
          $(this).val('');
          $('#menor_edad').val('');
        } else {
          // Inserta la cadena formateada ("X meses", "X días" o "X años")
          $('#menor_edad').val(resultado.texto);
        }
      }
    });

    // =====================================================================
    // 1.5 VERIFICACIÓN DE CÉDULA/DOCUMENTO DEL MENOR EN TIEMPO REAL
    // =====================================================================
    async function verificarCedulaMenorModal(tipo, cedula) {
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
            resolve(response.existe_cedula);
          },
          error: function(xhr, status, error) {
            console.error("Error al verificar cédula del menor:", error);
            reject('Error al conectar con el servidor.');
          }
        });
      });
    }

    async function validarCedulaMenorRealTime() {
      const cedulaInput = $('#menor_cedula');
      const tipoSelect = $('#menor_tipo_cedula');
      const cedula = cedulaInput.val().trim();
      const tipo = tipoSelect.val();

      cedulaInput.removeClass('input-error');

      if (cedula === "") return true;

      try {
        const existe = await verificarCedulaMenorModal(tipo, cedula);
        if (existe) {
          cedulaInput.addClass('input-error');
          mostrarAviso('🛑 El documento/cédula ' + tipo + '-' + cedula + ' ya se encuentra registrado en el sistema.');
          return false;
        }
      } catch (error) {
        console.error(error);
      }
      return true;
    }

    // Disparadores para validar la existencia de la cédula del menor
    $('#menor_cedula').on('blur', validarCedulaMenorRealTime);
    $('#menor_tipo_cedula').on('change', validarCedulaMenorRealTime);

    $('#rep_fecha_nacimiento').on('change', function() {
      let edad = calcularEdadInput($(this).val(), '#rep_edad');
      // Doble validación por si logran saltar el calendario
      if (edad < 18) {
        mostrarAviso("🛑 El representante debe ser mayor de 18 años.");
        $(this).val('');
        $('#rep_edad').val('');
      }
    });

    // =====================================================================
    // 2. FUNCIÓN DE BLOQUEO TOTAL DEL REPRESENTANTE
    // =====================================================================
    function bloquearCamposRep(bloquearCompleto, vaciarDatos = true) {
      if (vaciarDatos) {
        $('.rep-auto').val('');
        $('#rep_prefijo, #rep_genero, #rep_edad').val('');
      }

      if (bloquearCompleto) {
        $('.rep-auto').prop('readonly', true).css('background-color', '#e9ecef');
        $('#rep_prefijo, #rep_genero').prop('disabled', true);
      } else {
        $('.rep-auto').prop('readonly', false).css('background-color', '');
        $('#rep_prefijo, #rep_genero').prop('disabled', false);
      }
    }

    // Inicialmente, si no hay cédula de representante, los campos están bloqueados
    bloquearCamposRep(true, true);

    // =====================================================================
    // 3. VALIDACIÓN CRUZADA Y DE ROL (REPRESENTANTE)
    // =====================================================================
    async function validarCedulaRepModal() {
      const cedulaRepInput = $('#rep_cedula');
      const tipoRep = $('#rep_tipo_cedula').val();
      const cedulaRep = cedulaRepInput.val().trim();

      const cedulaMenor = $('#menor_cedula').val() ? $('#menor_cedula').val().trim() : '';
      const tipoMenor = $('#menor_tipo_cedula').val() ? $('#menor_tipo_cedula').val() : '';

      cedulaRepInput.removeClass('input-error');

      // Si borran la cédula, bloqueamos todo
      if (cedulaRep === "") {
        bloquearCamposRep(true, true);
        return false;
      }

      // REGLA 1: La cédula del rep NO puede ser igual a la del menor
      if (cedulaRep === cedulaMenor && tipoRep === tipoMenor) {
        cedulaRepInput.addClass('input-error');
        bloquearCamposRep(true, true); // Bloquea y vacía
        mostrarAviso('🛑 <b>Error Crítico:</b> La cédula del representante no puede ser idéntica a la del paciente menor.');
        return false;
      }

      // Límites lógicos (V, E)
      const cedulaNum = parseInt(cedulaRep);
      if (tipoRep === 'V' && cedulaNum > 80000000) {
        cedulaRepInput.addClass('input-error');
        bloquearCamposRep(true, true);
        mostrarAviso('🛑 Error: Para el tipo V-, la cédula no puede ser mayor a 80.000.000');
        return false;
      } else if (tipoRep === 'E' && cedulaNum < 80000000) {
        cedulaRepInput.addClass('input-error');
        bloquearCamposRep(true, true);
        mostrarAviso('🛑 Error: Para el tipo E-, la cédula no puede ser menor a 80.000.000');
        return false;
      }

      $('#rep_nombre').attr('placeholder', 'Buscando en BD...');

      // REGLA 2: Buscar en BD y verificar ROL 5
      $.ajax({
        url: '../../cfg/ajax/obtener_representante_ajax.php',
        type: 'POST',
        data: {
          cedula: cedulaRep,
          tipo_cedula: tipoRep
        },
        dataType: 'json',
        success: function(res) {
          if (res && res.existe) {
            if (res.es_representante === true || res.es_representante === 1) {
              // ÉXITO: Existe y es representante. Llenamos datos y bloqueamos para que no editen.
              $('#rep_nombre').val(res.nombre);
              $('#rep_apellido').val(res.apellido);
              if (res.fecha_nacimiento) {
                $('#rep_fecha_nacimiento').val(res.fecha_nacimiento);
                calcularEdadInput(res.fecha_nacimiento, '#rep_edad');
              }
              if (res.telefono) $('#rep_telefono').val(res.telefono);
              if (res.id_prefijo) $('#rep_prefijo').val(res.id_prefijo);
              if (res.genero) $('#rep_genero').val(res.genero);
              if (res.email) $('#rep_email').val(res.email);

              bloquearCamposRep(true, false); // Bloquea pero NO vacía los datos
              mostrarAviso("✅ Se cargaron los datos del representante existente.");
            } else {
              // ERROR DE ROL: Existe pero no es representante.
              cedulaRepInput.addClass('input-error');
              bloquearCamposRep(true, true); // Bloquea y vacía todo
              mostrarAviso('🛑 <b>Cédula Restringida:</b> Esta cédula está registrada, pero <b>NO tiene el rol de Representante</b>. No puede usarla aquí. Ingrese una diferente.');
            }
          } else {
            // NO EXISTE: Es un representante nuevo. Habilitamos todo para que escriba.
            bloquearCamposRep(false, true); // Desbloquea y asegura que esté vacío
            $('#rep_nombre').attr('placeholder', 'Nombre del nuevo rep.');
          }
        },
        error: function() {
          console.error("Error al conectar con la base de datos.");
        }
      });
    }

    // Disparadores para la validación del Representante
    $('#rep_cedula').off('blur').on('blur', validarCedulaRepModal);
    $('#rep_tipo_cedula').off('change').on('change', validarCedulaRepModal);

    // Disparador CRUZADO: Si cambian la cédula del MENOR, re-evaluar si choca con la del REP
    $('#menor_cedula, #menor_tipo_cedula').on('blur change', function() {
      const cedulaMenor = $('#menor_cedula').val() ? $('#menor_cedula').val().trim() : '';
      const tipoMenor = $('#menor_tipo_cedula').val() ? $('#menor_tipo_cedula').val() : '';
      const cedulaRep = $('#rep_cedula').val() ? $('#rep_cedula').val().trim() : '';
      const tipoRep = $('#rep_tipo_cedula').val() ? $('#rep_tipo_cedula').val() : '';

      if (cedulaMenor !== '' && cedulaMenor === cedulaRep && tipoMenor === tipoRep) {
        $('#menor_cedula').addClass('input-error');
        mostrarAviso('🛑 <b>Error Crítico:</b> La cédula del paciente menor no puede ser idéntica a la de su representante.');
        $('#menor_cedula').val(''); // Borra la del menor inmediatamente
      } else {
        $('#menor_cedula').removeClass('input-error');
      }
    });

    // 5. GUARDAR VIA AJAX
    $('#btnGuardarMenorAjax').click(function(e) {
      e.preventDefault();
      $('#formAjaxPacienteMenor .input-error').removeClass('input-error');

      let errores = [];
      let esValido = true;

      // Validar obligatorios
      $('#formAjaxPacienteMenor').find('[required]').each(function() {
        if (!$(this).val() || $(this).val() === "") {
          $(this).addClass('input-error');
          esValido = false;
        }
      });

      if (!esValido) {
        mostrarAviso("⚠️ Por favor complete todos los campos marcados en rojo.");
        return;
      }

      // Habilitar selects bloqueados para que viajen en el POST
      $('#rep_prefijo, #rep_genero, #menor_tipo_etnia, #menor_tipo_discapacidad').prop('disabled', false);

      let btn = $(this);
      btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

      // Capturamos el nombre del representante antes de que se limpie el modal
      let nombreRepresentante = $('#rep_nombre').val() + ' ' + $('#rep_apellido').val();

      $.ajax({
        url: '../../cfg/ajax/guardar_paciente_menor_ajax.php',
        type: 'POST',
        data: $('#formAjaxPacienteMenor').serialize(),
        dataType: 'json',
        success: function(res) {
          if (res.success) {
            $('#modalAgregarPacienteMenor').modal('hide');

            // 1. Rellenar campos en el formulario principal
            $('select[name="tipo_cedula_externo"]').val(res.tipo_ced);
            $('input[name="cedula_externo"]').val(res.cedula);
            $('#paciente_externo').val(res.nombre_completo);
            $('#entregado_a').val(nombreRepresentante.trim());
            $('#div_entregado_a').show();

            // 2. Bloquear los campos (Select e Inputs)
            $('select[name="tipo_cedula_externo"]').css({
              'pointer-events': 'none',
              'background-color': '#e9ecef'
            });
            $('input[name="cedula_externo"]').prop('readonly', true).css('background-color', '#e9ecef');
            $('#paciente_externo').prop('readonly', true).css('background-color', '#e9ecef');
            $('#entregado_a').prop('readonly', true).css('background-color', '#e9ecef');

            $('#formAjaxPacienteMenor')[0].reset();
            $('#headerDespachoAviso').removeClass('bg-crimson').addClass('bg-green');
            mostrarAviso("✅ Menor y Representante registrados con éxito.");
          } else {
            mostrarAviso("🛑 " + res.error);
          }
        },
        error: function() {
          mostrarAviso("❌ Error de conexión al intentar guardar.");
        },
        complete: function() {
          btn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar y Usar');
          // Restaurar disabled según selectores
          $('#menor_etnia').trigger('change');
          $('#menor_discapacidad').trigger('change');
        }
      });
    });
  });
</script>