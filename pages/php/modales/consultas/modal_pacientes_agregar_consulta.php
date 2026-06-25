  <div class="modal" id="modalAgregarPacienteExterno" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <button type="button" class="close text-white" data-dismiss="modal" style="color: white; opacity: 1;">&times;</button>
          <h4 class="modal-title" style="color: white;"><i class="fa fa-user-plus"></i> Registrar Paciente Externo</h4>
        </div>
        <div class="modal-body">
          <form id="formAjaxPacienteExterno">
            <div class="row">
              <div class="col-sm-2 form-group">
                <label>Tipo (*):</label>
                <select name="ext_tipo_cedula" id="ext_tipo_cedula" class="form-control" required>
                  <option value="V">V-</option>
                </select>
              </div>
              <div class="col-sm-4 form-group">
                <label>Cédula (*):</label>
                <input type="text" class="form-control solo-numeros-ext" name="ext_cedula" id="ext_cedula" placeholder="N° de Cédula" maxlength="8" required>
              </div>
              <div class="col-sm-3 form-group">
                <label>Nombre (*):</label>
                <input type="text" class="form-control solo-letras-ext" name="ext_nombre" id="ext_nombre" placeholder="Nombre" maxlength="100" required>
              </div>
              <div class="col-sm-3 form-group">
                <label>Apellido:</label>
                <input type="text" class="form-control solo-letras-ext" name="ext_apellido" id="ext_apellido" placeholder="Apellido" maxlength="100">
              </div>
            </div>
            <div class="row">
              <div class="col-sm-5 form-group">
                <label>Fecha de nacimiento (*):</label>
                <input type="date" class="form-control" id="ext_fecha_nacimiento" name="ext_fecha_nacimiento" required>
              </div>
              <div class="col-sm-1 form-group">
                <label>Edad:</label>
                <input type="text" class="form-control" id="ext_edad" readonly>
              </div>
              <div class="col-sm-3 form-group">
                <label>Sexo (*):</label>
                <select name="ext_genero" id="ext_genero" class="form-control" required>
                  <option value="">--- Seleccione ---</option>
                  <option value="Masculino">Masculino</option>
                  <option value="Femenino">Femenino</option>
                </select>
              </div>
              <div class="col-sm-3 form-group">
                <label>Situación conyugal:</label>
                <select name="ext_situacion" id="ext_situacion" class="form-control">
                  <option value="">--- Seleccione ---</option>
                  <option value="Soltero">Soltero</option>
                  <option value="Casado">Casado</option>
                  <option value="Viudo">Viudo</option>
                  <option value="Divorciado">Divorciado</option>
                  <option value="Unión Estable de Hecho">Unión Estable de Hecho</option>
                </select>
              </div>
            </div>
            <div class="row">
              <div class="col-sm-3 form-group">
                <label>Etnia:</label>
                <select name="ext_etnia" id="ext_etnia" class="form-control">
                  <option value="No">No</option>
                  <option value="Si">Si</option>
                </select>
              </div>
              <div class="col-sm-3 form-group">
                <label>Tipo Etnia:</label>
                <select name="ext_tipo_etnia" id="ext_tipo_etnia" class="form-control" disabled>
                  <option value="">--- Seleccione ---</option>
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
              <div class="col-sm-3 form-group">
                <label>Analfabeta (*):</label>
                <select name="ext_analfabeta" id="ext_analfabeta" class="form-control" required>
                  <option value="">--- Seleccione ---</option>
                  <option value="No">No</option>
                  <option value="Si">Si</option>
                </select>
              </div>
              <div class="col-sm-3 form-group">
                <label>Cotiza S.S:</label>
                <select name="ext_seguro" id="ext_seguro" class="form-control">
                  <option value="">--- Seleccione ---</option>
                  <option value="No">No</option>
                  <option value="Si">Si</option>
                </select>
              </div>
            </div>
            <div class="row">
              <div class="col-sm-3 form-group">
                <label>Prefijo (*):</label>
                <select name="ext_prefijo" id="ext_prefijo" class="form-control" required>
                  <?php
                  $res_pref = $conexion->query("SELECT * FROM prefijos_telefonos WHERE estatus = '1'");
                  while ($row_pref = $res_pref->fetch_assoc()) {
                    echo "<option value='" . $row_pref["Id"] . "'>" . $row_pref['prefijo'] . "</option>";
                  }
                  ?>
                </select>
              </div>
              <div class="col-sm-3 form-group">
                <label>Teléfono (*):</label>
                <input type="text" class="form-control solo-numeros-ext" name="ext_telefono" id="ext_telefono" placeholder="Número" maxlength="7" required>
              </div>
              <div class="col-sm-6 form-group">
                <label>Email (*):</label>
                <input type="email" class="form-control" name="ext_email" id="ext_email" placeholder="correo@gmail.com" required>
              </div>
            </div>
            <hr>
            <div class="row">
              <div class="col-sm-4 form-group">
                <label>Grupo sanguíneo (*):</label>
                <select name="ext_grupo_sanguineo" id="ext_grupo_sanguineo" class="form-control" required>
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
              <div class="col-sm-4 form-group">
                <label>Discapacidad:</label>
                <select name="ext_discapacidad" id="ext_discapacidad" class="form-control">
                  <option value="No">No</option>
                  <option value="Si">Si</option>
                </select>
              </div>
              <div class="col-sm-4 form-group">
                <label>Tipo Discapacidad:</label>
                <select name="ext_tipo_discapacidad" id="ext_tipo_discapacidad" class="form-control" disabled>
                  <option value="">--- Seleccione ---</option>
                  <option value="fisico_motora">Físico-Motora</option>
                  <option value="visual">Visual</option>
                  <option value="auditiva">Auditiva</option>
                  <option value="intelectual">Intelectual</option>
                  <option value="psicosocial">Psicosocial</option>
                  <option value="multiple">Múltiple</option>
                </select>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-success" id="btnGuardarPacienteAjax"><i class="fa fa-save"></i> Guardar y Usar</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    $(document).ready(function() {

      function limpiarErroresModal() {
        $('#formAjaxPacienteExterno .input-error').removeClass('input-error');
      }

      let fechaLimiteAdulto = new Date();
      fechaLimiteAdulto.setFullYear(fechaLimiteAdulto.getFullYear() - 18);
      let maxFechaAdulto = fechaLimiteAdulto.toISOString().split('T')[0];

      // Aplicar restricción al input de fecha
      $('#ext_fecha_nacimiento').attr('max', maxFechaAdulto);

      // 2. Bloquear teclado manteniendo el input cliqueable
      $('#ext_fecha_nacimiento').on('keydown keypress keyup', function(e) {
        if (e.which !== 9) {
          e.preventDefault();
          return false;
        }
      });

      // -------------------------------------------------------------
      // LÓGICA DEL MODAL RÁPIDO PARA PACIENTE EXTERNO (VÍA AJAX)
      // -------------------------------------------------------------

      // Validaciones Teclado (solo números y letras)
      function bloquearNumeros(e) {
        const teclasPermitidas = ["Backspace", "Tab", "ArrowLeft", "ArrowRight", "Delete", "Space"];
        if (teclasPermitidas.includes(e.key)) return;
        if (e.key >= "0" && e.key <= "9") e.preventDefault();
      }

      function limpiarNumeros(e) {
        e.target.value = e.target.value.replace(/[0-9]/g, "");
      }

      function soloNumerosSinE(campo, max) {
        campo.addEventListener("keydown", function(e) {
          const tp = ["Backspace", "Tab", "ArrowLeft", "ArrowRight", "Delete"];
          if (tp.includes(e.key)) return;
          if (e.key.toLowerCase() === "e") {
            e.preventDefault();
            return;
          }
          if (!/^[0-9]$/.test(e.key)) {
            e.preventDefault();
            return;
          }
          if (campo.value.length >= max) {
            e.preventDefault();
          }
        });
        campo.addEventListener("input", function() {
          campo.value = campo.value.replace(/[^0-9]/g, "").slice(0, max);
        });
      }

      // Aplicar filtros
      soloNumerosSinE(document.getElementById("ext_cedula"), 8);
      soloNumerosSinE(document.getElementById("ext_telefono"), 7);
      document.getElementById("ext_nombre").addEventListener("keydown", bloquearNumeros);
      document.getElementById("ext_nombre").addEventListener("input", limpiarNumeros);
      document.getElementById("ext_apellido").addEventListener("keydown", bloquearNumeros);
      document.getElementById("ext_apellido").addEventListener("input", limpiarNumeros);

      // Bloqueos condicionales Etnia y Discapacidad
      $('#ext_etnia').change(function() {
        $('#ext_tipo_etnia').prop('disabled', $(this).val() === 'No').val('');
      });
      $('#ext_discapacidad').change(function() {
        $('#ext_tipo_discapacidad').prop('disabled', $(this).val() === 'No').val('');
      });

      // -------------------------------------------------------------
      // VERIFICACIÓN DE CÉDULA EN TIEMPO REAL (MODAL)
      // -------------------------------------------------------------
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
        const cedulaInput = $('#ext_cedula');
        const tipoSelect = $('#ext_tipo_cedula');
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
      $('#ext_cedula').on('blur', validarCedulaModal);
      $('#ext_tipo_cedula').on('change', validarCedulaModal);

      function validarCedulaMed() {
        const tipo = document.getElementById('ext_tipo_cedula').value;
        const cedulaInput = document.getElementById('ext_cedula');
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
      $('#ext_cedula').on('blur', validarCedulaMed);
      $('#ext_tipo_cedula').on('change', validarCedulaMed);

      // Lógica de Edad
      const today = new Date();
      today.setFullYear(today.getFullYear() - 18);
      document.getElementById('ext_fecha_nacimiento').setAttribute('max', today.toISOString().split('T')[0]);

      $('#ext_fecha_nacimiento').on('change', function() {
        var fNac = $(this).val();
        if (fNac) {
          var dateNac = new Date(fNac);
          var ahora = new Date();
          var edad = ahora.getFullYear() - dateNac.getFullYear();
          var m = ahora.getMonth() - dateNac.getMonth();
          if (m < 0 || (m === 0 && ahora.getDate() < dateNac.getDate())) {
            edad--;
          }

          if (edad < 18) {
            mostrarAviso("🛑 El paciente debe ser mayor de 18 años para ser registrado por esta vía.");
            $('#ext_edad').val('');
            $(this).val('');
          } else {
            $('#ext_edad').val(edad);
          }
        }
      });

      // Precargar cédula buscada al abrir el modal
      $('#modalAgregarPacienteExterno').on('show.bs.modal', function() {
        limpiarErroresModal();
        var tipoBuscado = $('select[name="tipo_cedula_externo"]').val();
        var cedulaBuscada = $('input[name="cedula_externo"]').val();
        if (cedulaBuscada.length >= 6) {
          $('#ext_tipo_cedula').val(tipoBuscado);
          $('#ext_cedula').val(cedulaBuscada);
        }
      });

      // Guardado por AJAX
      $('#btnGuardarPacienteAjax').click(function(e) {
        e.preventDefault();
        limpiarErroresModal();
        let errores = [];
        let esValido = true;

        // Validaciones manuales para colorear inputs
        $('#formAjaxPacienteExterno').find('[required]').each(function() {
          var val = $(this).val();
          if (val === null || val.trim() === "" || val.includes('--- Seleccione')) {
            $(this).addClass('input-error');
            esValido = false;
            errores.push("El campo " + $(this).closest('.form-group').find('label').text().replace('(*):', '').trim() + " es obligatorio.");
          }
        });

        // Validar Cédula V y E
        const tipoCed = $('#ext_tipo_cedula').val();
        const numCed = parseInt($('#ext_cedula').val());
        if (tipoCed === 'V' && numCed > 80000000) {
          $('#ext_cedula').addClass('input-error');
          errores.push("Cédula V- no puede ser mayor a 80.000.000");
          esValido = false;
        }
        if (tipoCed === 'E' && numCed < 80000000) {
          $('#ext_cedula').addClass('input-error');
          errores.push("Cédula E- no puede ser menor a 80.000.000");
          esValido = false;
        }

        if (!esValido) {
          mostrarAviso("⚠️ Verifique los siguientes errores:<ul><li>" + errores.join("</li><li>") + "</li></ul>");
          return;
        }

        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
          url: '../../cfg/ajax/guardar_paciente_externo_ajax.php',
          type: 'POST',
          data: $('#formAjaxPacienteExterno').serialize(),
          dataType: 'json',
          success: function(res) {
            if (res.success) {
              $('#modalAgregarPacienteExterno').modal('hide');

              // 1. Rellenar campos del formulario principal
              $('select[name="tipo_cedula_externo"]').val(res.tipo_ced);
              $('input[name="cedula_externo"]').val(res.cedula);
              $('#paciente_externo').val(res.nombre_completo);
              $('#entregado_a').val(res.nombre_completo);
              $('#div_entregado_a').show();

              // 2. Bloquear los campos (Select e Inputs)
              $('select[name="tipo_cedula_externo"]').css({
                'pointer-events': 'none',
                'background-color': '#e9ecef'
              });
              $('input[name="cedula_externo"]').prop('readonly', true).css('background-color', '#e9ecef');
              $('#paciente_externo').prop('readonly', true).css('background-color', '#e9ecef');
              $('#entregado_a').prop('readonly', true).css('background-color', '#e9ecef');

              $('#formAjaxPacienteExterno')[0].reset();
              mostrarExito("✅ Paciente registrado con éxito y adjuntado al despacho actual.");
            } else {
              if (res.error.includes("ya se encuentra registrada")) {
                $('#ext_cedula').addClass('input-error');
              }
              mostrarAviso("🛑 " + res.error);
            }
          },
          error: function() {
            mostrarAviso("❌ Error de conexión al intentar guardar el paciente.");
          },
          complete: function() {
            btn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar y Usar');
          }
        });
      });
    });
  </script>
  </body>

  </html>