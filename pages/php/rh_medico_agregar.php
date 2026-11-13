<?php
include('includes/headerNav2.php');
verificarAcceso('Crear Medicos');
?>

<head>
  <title>Médico | Añadir</title>
</head>

<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Añadir Médico
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="#"><i class="fa fa-user"></i>Médico</a></li>
      <li class="active"><a href="#"><i class="fa fa-user-plus"></i>Añadir</a></li>
    </ol>
  </section>

  <section class="content">

    <div class="row">
      <div class="col-md-12">
        <div class="nav-tabs-custom">
          <ul class="nav nav-tabs">
            <li class="active"><a href="#tab_1" data-toggle="tab">Datos personales del médico</a></li>
          </ul>
          <div class="tab-content">
            <div class="tab-pane active" id="tab_1">
              <div class="box-body">
                <form action="../../cfg/agregar/agregar_medico.php" id="formularioMedico" class="form-group" method="POST">
                  <label class="control-label"></label>
                  <div class="col-sm-1 pull-left" style="margin-top: 30px;">
                    <select name="tipo_cedula" id="tipo_cedula" class="form-control" style="width: 60px;">
                      <option value="V">V-</option>
                      <!--<option value="E">E-</option>-->
                    </select>
                  </div>
                  <div class="col-sm-3">
                    <p>Cédula (*)</p>
                    <input type="text" class="form-control" name="cedula" id="cedula" placeholder="N° de Cedula" required>
                  </div>
                  <label class="control-label"></label>
                  <div class="col-sm-4">
                    <p>Nombres (*):</p>
                    <input type="text" class="form-control" value="" name="nombre" id="nombre" placeholder="Nombres de la Persona" required>
                  </div>
                  <div class="col-sm-3">
                    <p>Apellidos:</p>
                    <input type="text" class="form-control" value="" name="apellido" id="apellido" placeholder="Apellidos de la Persona">
                  </div>
                  <br><br><br><br>
                  <label class="control-label"></label>
                  <div class="col-sm-3">
                    <p>Fecha de nacimiento (*):</p>
                    <input type="date" class="form-control pull-right" id="fechaN" name="fecha_nacimiento" onchange="calcularEdad(); setMinDateIngreso();" max="" required>
                  </div>
                  <div class="col-sm-1" style="margin-top: 30px;">
                    <input type="text" class="form-control pull-right" id="edad" name="edad" readonly>
                  </div>
                  <label class="control-label"></label>
                  <div class="col-sm-1 pull-left" style="margin-top: 30px;">
                    <select name="prefijo" class="form-control" style="width: 70px;" required>
                      <?php
                      include('../../cfg/conexion.php');
                      $sql = $conexion->query("SELECT * FROM prefijos_telefonos");
                      while ($resultado = $sql->fetch_assoc()) {
                        echo "<option value='" . $resultado["Id"] . "'>" . $resultado['prefijo'] . "</option>";
                      }
                      ?>
                    </select>
                  </div>
                  <div class="col-sm-3">
                    <p>Teléfono (*):</p>
                    <input type="text" class="form-control" name="telefono" id="telefono" placeholder="N° De Telefono" required>
                  </div>
                  <label class="control-label"></label>
                  <div class="col-sm-3">
                    <p>Sexo (*):</p>
                    <select name="genero" class="form-control" required>
                      <option value="">--- Seleccione un genero ---</option>
                      <option value="Masculino">Masculino</option>
                      <option value="Femenino">Femenino</option>
                    </select>
                  </div>
                  <br><br><br><br>
                  <label class="control-label"></label>
                  <div class="col-sm-4">
                    <p>Correo (*):</p>
                    <input type="text" class="form-control" value="" name="correo" id="correo" placeholder="nombre.apellido@dominio.com" required>
                  </div>
                  <label class="control-label"></label>
                  <div class="col-sm-4">
                    <p>Fecha de ingreso (*):</p>
                    <input type="date" name="fecha_ingreso" id="fechaI" class="form-control" value="" required>
                  </div>
                  <label class="control-label"></label>
                  <div class="col-sm-3 form-group" id="group_areas">
                    <p>Áreas / Departamentos (*):</p>
                    <button type="button" class="btn btn-info btn-block" id="btn_modal_areas" data-toggle="modal" data-placement="top" title="Ninguna seleccionada" data-target="#modal_areas">
                      <i></i> Gestionar Áreas
                    </button>
                    <input type="hidden" name="areas_seleccionadas" id="areas_seleccionadas" required>
                  </div>
                  <br><br><br>
                  <label class="control-label"></label>
                  <div class="col-sm-4 form-group" id="group_especialidades">
                    <p>Especialidades (*):</p>
                    <button type="button" class="btn btn-info btn-block" id="btn_modal_especialidades" data-toggle="modal" data-placement="top" title="Ninguna seleccionada" data-target="#modal_especialidades">
                      <i></i> Gestionar Especialidades
                    </button>
                    <input type="hidden" name="especialidades_seleccionadas" id="especialidades_seleccionadas" required>
                  </div>
                  <label class="control-label"></label>
                  <div class="col-sm-4 form-group">
                    <p>Número de colegiatura (*):</p>
                    <input type="text" class="form-control" name="cod_colegiatura" id="cod_colegiatura" minlength="4" maxlength="7" placeholder="Ingrese solo numeros (entre 4 y 7 digitos)" oninput="this.value = this.value.replace(/[^0-9]/g, '');" required>
                  </div>

                  <br><br><br><br>
                  <div class="col-sm-12" style="margin-top: 20px; border-top: 1px solid #ddd; padding-top: 15px;">
                    <div class="form-check">
                      <input type="checkbox" class="form-check-input" id="crear_usuario" name="crear_usuario" value="1">
                      <label class="form-check-label" for="crear_usuario" style="font-weight: bold; color: #00a65a;">¿Crear acceso al sistema para este médico?</label>
                    </div>
                  </div>

                  <div id="seccion_password" class="row" style="display: none; margin-top: 0px; margin-left:10px; overflow: hidden; width: 100%;">
                    <div class="col-sm-4">
                      <p>Contraseña (*):</p>
                      <div class="password-container">
                        <input type="password" class="form-control pass-input" name="password" id="password_medico" placeholder="Mínimo 6 caracteres" minlength="6" maxlength="16">
                      </div>
                    </div>
                    <div class="col-sm-4">
                      <p>Confirmar Contraseña (*):</p>
                      <div class="password-container">
                        <input type="password" class="form-control pass-input" name="confirm_password" id="confirm_password_medico" placeholder="Repita la contraseña" minlength="6" maxlength="16">
                      </div>
                    </div>
                  </div>
                  <br><br><br><br>
                  <div style="float:right;">
                    <button type="button" class="btn btn-second regresar" id="abrirModalRegresar">Regresar</button>
                    <button type="button" class="btn btn-success guardar" id="abrirModalGuardar">Guardar</button>
                  </div>
                  <br>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade" id="modal_areas" role="dialog">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header bg-primary">
              <h4 class="modal-title">Agregar Áreas / Departamentos</h4>
            </div>
            <div class="modal-body">
              <div id="contenedor_filas_areas"></div>
              <button type="button" class="btn btn-success btn-sm" id="add_fila_area">
                <i class="fa fa-plus"></i> Añadir otra
              </button>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-primary" id="guardar_areas_listo" data-dismiss="modal">Listo</button>
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade" id="modalBuscarArea" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
          <div class="modal-content">
            <div class="modal-header bg-info">
              <h4 class="modal-title">Buscar Área</h4>
            </div>
            <div class="modal-body">
              <input type="text" id="inputBuscarArea" class="form-control" placeholder="Escriba para filtrar...">
              <div class="list-group" id="listaResultadosArea" style="margin-top: 10px; max-height: 200px; overflow-y: auto;"></div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade" id="modal_especialidades" role="dialog">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header bg-primary">
              <h4 class="modal-title">Agregar Especialidades</h4>
            </div>
            <div class="modal-body">
              <div id="contenedor_filas_especialidades"></div>
              <button type="button" class="btn btn-success btn-sm" id="add_fila_especialidad">
                <i class="fa fa-plus"></i> Añadir otra
              </button>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-primary" id="guardar_especialidades_listo" data-dismiss="modal">Listo</button>
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade" id="modalBuscarEspecialidad" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
          <div class="modal-content">
            <div class="modal-header bg-info">
              <h4 class="modal-title">Buscar Especialidad</h4>
            </div>
            <div class="modal-body">
              <input type="text" id="inputBuscarEspecialidad" class="form-control" placeholder="Escriba para filtrar...">
              <div class="list-group" id="listaResultadosEspecialidad" style="margin-top: 10px; max-height: 200px; overflow-y: auto;"></div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade" id="avisoModal" tabindex="-1" role="dialog" aria-labelledby="avisoModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header bg-crimson">
              <h5 class="modal-title" id="avisoModalLabel">Aviso Importante</h5>
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

      <div class="modal fade" id="modalGuardarMedico" tabindex="-1" role="dialog" aria-labelledby="modalGuardarMedicoLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header bg-green">
              <h5 class="modal-title" id="modalGuardarMedicoLabel">Confirmación de Guardado</h5>
            </div>
            <div class="modal-body">
              <p>¿Está seguro de que desea guardar la información del médico?</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
              <button type="button" class="btn btn-success" id="confirmarGuardadoFinal">Aceptar</button>
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade" id="modalRegresarMedico" tabindex="-1" role="dialog" aria-labelledby="modalRegresarMedicoLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header bg-crimson">
              <h5 class="modal-title" id="modalRegresarMedicoLabel">Confirmación de Regreso</h5>
            </div>
            <div class="modal-body">
              <p>Al hacer clic en "Abandonar Formulario", perderá todos los datos no guardados. ¿Desea continuar?</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
              <a href="rh_medico_listado.php" class="btn btn-danger">Abandonar Formulario</a>
            </div>
          </div>
        </div>
      </div>
  </section>

  <?php
  include('includes/footer.php');
  ?>

  <script>
    function mostrarAviso(mensaje) {
      $('#avisoTexto').html(mensaje);
      $('#avisoModal').modal('show');
    }

    function limpiarErrores() {
      $('input, select').removeClass('input-error');
    }

    const fechaNacimientoInput = document.getElementById("fechaN");
    const fechaIngresoInput = document.getElementById("fechaI");

    function getMaxDateNacimiento() {
      const today = new Date();
      const maxNacimiento = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());

      const year = maxNacimiento.getFullYear();
      const month = String(maxNacimiento.getMonth() + 1).padStart(2, '0');
      const day = String(maxNacimiento.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    }

    const maxDate = getMaxDateNacimiento();
    if (fechaNacimientoInput) {
      fechaNacimientoInput.max = maxDate;
    }

    function setMinDateIngreso() {
      if (fechaNacimientoInput && fechaIngresoInput && fechaNacimientoInput.value) {
        fechaIngresoInput.min = fechaNacimientoInput.value;
        fechaIngresoInput.max = new Date().toISOString().split('T')[0];

        if (fechaIngresoInput.value && fechaIngresoInput.value < fechaNacimientoInput.value) {
          fechaIngresoInput.value = '';
        }
      }
    }

    $(document).ready(setMinDateIngreso);

    function calcularEdad() {
      const input = document.getElementById("fechaN").value;
      const nacimiento = new Date(input);
      const hoy = new Date();

      if (input) {
        let edad = hoy.getFullYear() - nacimiento.getFullYear();
        const mesActual = hoy.getMonth();
        const mesNacimiento = nacimiento.getMonth();

        if (mesActual < mesNacimiento || (mesActual === mesNacimiento && hoy.getDate() < nacimiento.getDate())) {
          edad--;
        }

        document.getElementById("edad").value = edad;
      }
    }
    calcularEdad();

    function soloLetras(inputElement) {
      if (!inputElement) return;
      inputElement.addEventListener('input', function() {
        this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
      });
    }

    soloLetras(document.getElementById('nombre'));
    soloLetras(document.getElementById('apellido'));

    function soloNumerosSinE(campo, maxDigitos) {
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

    soloNumerosSinE(document.getElementById("cedula"), 8);
    soloNumerosSinE(document.getElementById("telefono"), 7);

    async function validarConexionYHora() {
      if (!navigator.onLine) {
        return {
          valido: false,
          error: '⚠️ <b>Adaptador de red desconectado:</b> Verifique su conexión Wi-Fi o cable.'
        };
      }

      try {
        let horaReal;
        let response = await fetch('https://worldtimeapi.org/api/timezone/America/Caracas', {
          cache: 'no-store'
        });

        if (response.ok) {
          const data = await response.json();
          horaReal = new Date(data.datetime).getTime();
        } else {
          response = await fetch('https://timeapi.io/api/Time/current/zone?timeZone=America/Caracas', {
            cache: 'no-store'
          });

          if (!response.ok) throw new Error('Fallo en servidores externos');

          const data = await response.json();
          horaReal = new Date(data.dateTime).getTime();
        }

        const horaLocal = new Date().getTime();

        if (Math.abs(horaReal - horaLocal) > 300000) {
          return {
            valido: false,
            error: '⚠️ <b>Hora del sistema incorrecta:</b> Por favor, ajuste el reloj de su equipo para poder guardar.'
          };
        }

        return {
          valido: true
        };

      } catch (error) {
        console.warn("Aviso: Servidores de tiempo externos bloqueados o caídos. Omitiendo validación estricta.");
        return {
          valido: true
        };
      }
    }

    function abrirModalGuardar() {
      $('#modalGuardarMedico').modal('show');
    }

    $('#crear_usuario').on('change', function() {
      if ($(this).is(':checked')) {
        $('#seccion_password').stop(true, true).slideDown();
        $('.pass-input').prop('required', true);
      } else {
        $('#seccion_password').stop(true, true).slideUp();
        $('.pass-input').prop('required', false);
        $('.pass-input').val('');
      }
    });

    function agregarFilaArea(idSeleccionado = "") {
      let htmlArea = `
          <div class="row fila-area" style="margin-bottom: 10px;">
              <div class="col-sm-10">
                  <div class="input-group">
                      <select class="form-control select-area">
                          <option value="">--- Seleccione un área ---</option>
                          <?php
                          // IMPORTANTE: Asegúrate de tener la conexión a BD disponible aquí
                          $q_area = $conexion->query("SELECT Id_departamento, nombre_departamento FROM departamento WHERE estatus = 1 ORDER BY nombre_departamento ASC");
                          while ($a = $q_area->fetch_assoc()) {
                            echo "<option value='" . $a['Id_departamento'] . "'>" . $a['nombre_departamento'] . "</option>";
                          }
                          ?>
                      </select>
                      <span class="input-group-btn">
                          <button class="btn btn-info btn-search-area" type="button" title="Buscar Área">
                            <i><img src="../../recursos/imagenes/iconos/buscar.png" style="width:10px; height:10px;"></i>
                          </button>
                      </span>
                  </div>
              </div>
              <div class="col-sm-2">
                  <button type="button" class="btn btn-danger btn-remove-area">
                      <i><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></i>
                  </button>
              </div>
          </div>`;

      $('#contenedor_filas_areas').append(htmlArea);
      if (idSeleccionado) {
        $('#contenedor_filas_areas .fila-area:last .select-area').val(idSeleccionado);
      }
    }

    $('#btn_modal_areas').tooltip();
    $('#btn_modal_areas').click(function() {
      if ($('#contenedor_filas_areas').children().length === 0) agregarFilaArea();
    });

    $('#add_fila_area').click(() => agregarFilaArea());
    $(document).on('click', '.btn-remove-area', function() {
      $(this).closest('.fila-area').remove();
    });

    $('#guardar_areas_listo').click(function() {
      let ids = [];
      let nombres = [];
      $('.select-area').each(function() {
        let val = $(this).val();
        if (val && val !== "") {
          ids.push(val);
          nombres.push($(this).find('option:selected').text().trim());
        }
      });
      $('#areas_seleccionadas').val(ids.join('|'));
      let textoTooltip = ids.length > 0 ? nombres.join(', ') : 'Ninguna seleccionada';
      $('#btn_modal_areas').attr('data-original-title', textoTooltip).tooltip('fixTitle');
    });

    $(document).on('change', '.select-area', function() {
      let selectActual = $(this);
      let valorActual = selectActual.val();
      if (valorActual === "") return;

      let conteo = 0;
      $('.select-area').each(function() {
        if ($(this).val() === valorActual) conteo++;
      });
      if (conteo > 1) {
        mostrarAviso("⚠️ <b>Atención:</b> Esta área ya ha sido seleccionada. Por favor, elija una diferente.");
        selectActual.val("");
      }
    });

    function agregarFilaEspecialidad(idSeleccionado = "") {
      let htmlEsp = `
          <div class="row fila-especialidad" style="margin-bottom: 10px;">
              <div class="col-sm-10">
                  <div class="input-group">
                      <select class="form-control select-especialidad">
                          <option value="">--- Seleccione una especialidad ---</option>
                          <?php
                          $q_esp = $conexion->query("SELECT Id_especialidad, nombre_especialidad FROM especialidad WHERE estatus = 1 ORDER BY nombre_especialidad ASC");
                          while ($e = $q_esp->fetch_assoc()) {
                            echo "<option value='" . $e['Id_especialidad'] . "'>" . $e['nombre_especialidad'] . "</option>";
                          }
                          ?>
                      </select>
                      <span class="input-group-btn">
                          <button class="btn btn-info btn-search-especialidad" type="button" title="Buscar Especialidad">
                            <i><img src="../../recursos/imagenes/iconos/buscar.png" style="width:10px; height:10px;"></i>
                          </button>
                      </span>
                  </div>
              </div>
              <div class="col-sm-2">
                  <button type="button" class="btn btn-danger btn-remove-especialidad">
                      <i><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></i>
                  </button>
              </div>
          </div>`;

      $('#contenedor_filas_especialidades').append(htmlEsp);
      if (idSeleccionado) {
        $('#contenedor_filas_especialidades .fila-especialidad:last .select-especialidad').val(idSeleccionado);
      }
    }

    $('#btn_modal_especialidades').tooltip();
    $('#btn_modal_especialidades').click(function() {
      if ($('#contenedor_filas_especialidades').children().length === 0) agregarFilaEspecialidad();
    });

    $('#add_fila_especialidad').click(() => agregarFilaEspecialidad());
    $(document).on('click', '.btn-remove-especialidad', function() {
      $(this).closest('.fila-especialidad').remove();
    });

    $('#guardar_especialidades_listo').click(function() {
      let ids = [];
      let nombres = [];
      $('.select-especialidad').each(function() {
        let val = $(this).val();
        if (val && val !== "") {
          ids.push(val);
          nombres.push($(this).find('option:selected').text().trim());
        }
      });

      $('#especialidades_seleccionadas').val(ids.join('|'));
      let textoTooltip = ids.length > 0 ? nombres.join(', ') : 'Ninguna seleccionada';
      $('#btn_modal_especialidades').attr('data-original-title', textoTooltip).tooltip('fixTitle');
    });

    $(document).on('change', '.select-especialidad', function() {
      let selectActual = $(this);
      let valorActual = selectActual.val();
      if (valorActual === "") return;

      let conteo = 0;
      $('.select-especialidad').each(function() {
        if ($(this).val() === valorActual) conteo++;
      });
      if (conteo > 1) {
        mostrarAviso("⚠️ <b>Atención:</b> Esta especialidad ya ha sido seleccionada. Por favor, elija una diferente.");
        selectActual.val("");
      }
    });

    let selectDestinoTarget = null;

    $(document).on('click', '.btn-search-area', function() {
      selectDestinoTarget = $(this).closest('.input-group').find('.select-area');
      $('#modalBuscarArea').modal('show');
      $('#inputBuscarArea').val('').trigger('keyup');
    });

    $('#inputBuscarArea').on('keyup', function() {
      let texto = $(this).val().toLowerCase();
      let html = '';
      let opciones = $('.select-area:first option').not('[value=""]');
      opciones.each(function() {
        let nombre = $(this).text();
        if (nombre.toLowerCase().includes(texto)) {
          html += `<a href="#" class="list-group-item list-group-item-action seleccionar-area" data-id="${$(this).val()}">${nombre}</a>`;
        }
      });
      $('#listaResultadosArea').html(html);
    });

    $(document).on('click', '.seleccionar-area', function(e) {
      e.preventDefault();
      selectDestinoTarget.val($(this).data('id')).trigger('change');
      $('#modalBuscarArea').modal('hide');
    });

    $(document).on('click', '.btn-search-especialidad', function() {
      selectDestinoTarget = $(this).closest('.input-group').find('.select-especialidad');
      $('#modalBuscarEspecialidad').modal('show');
      $('#inputBuscarEspecialidad').val('').trigger('keyup');
    });

    $('#inputBuscarEspecialidad').on('keyup', function() {
      let texto = $(this).val().toLowerCase();
      let html = '';
      let opciones = $('.select-especialidad:first option').not('[value=""]');
      opciones.each(function() {
        let nombre = $(this).text();
        if (nombre.toLowerCase().includes(texto)) {
          html += `<a href="#" class="list-group-item list-group-item-action seleccionar-especialidad" data-id="${$(this).val()}">${nombre}</a>`;
        }
      });
      $('#listaResultadosEspecialidad').html(html);
    });

    $(document).on('click', '.seleccionar-especialidad', function(e) {
      e.preventDefault();
      selectDestinoTarget.val($(this).data('id')).trigger('change');
      $('#modalBuscarEspecialidad').modal('hide');
    });

    const tipoSelect = document.getElementById('tipo_cedula');
    const cedulaInput = document.getElementById('cedula');

    function validarCedula() {
      const tipo = tipoSelect.value;
      const cedula = parseInt(cedulaInput.value, 10);

      $(cedulaInput).removeClass('input-error');

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

    function verificarDuplicados() {
      return new Promise((resolve, reject) => {
        const cedula = document.getElementById('cedula').value.trim();
        const email = document.getElementById('correo').value.trim();
        const inputId = document.getElementById('medicoId');
        const idMedico = inputId ? inputId.value : 0;

        if (cedula === "" && email === "") {
          resolve({
            existe_cedula: false,
            existe_email: false
          });
          return;
        }

        $.ajax({
          url: '../../cfg/ajax/verificar_existencia_cedula.php',
          method: 'POST',
          dataType: 'json',
          data: {
            cedula: cedula,
            email: email,
            id: idMedico
          },
          success: function(response) {
            resolve(response);
          },
          error: function(xhr, status, error) {
            console.error("⚠️ Error al verificar duplicados:", error);
            reject("⚠️ Error de conexión con el servidor.");
          }
        });
      });
    }

    $('#cedula').on('blur', async function() {
      if ($(this).val().trim() !== "") {
        if (!validarCedula()) return;
        try {
          const duplicados = await verificarDuplicados();
          if (duplicados.existe_cedula) {
            $(this).addClass('input-error');
            mostrarAviso('⚠ Cédula Existente: Esta cédula ya está registrada. Por favor, introduzca una diferente.');
          } else {
            $(this).removeClass('input-error');
          }
        } catch (e) {
          console.error(e);
        }
      }
    });
    $('#tipo_cedula').on('change', validarCedula);

    $('#correo').on('blur', async function() {
      if ($(this).val().trim() !== "") {
        try {
          const duplicados = await verificarDuplicados();
          if (duplicados.existe_email) {
            $(this).addClass('input-error');
            mostrarAviso('⚠ Correo Existente: Este correo electrónico ya está registrado por otro usuario.');
          } else {
            $(this).removeClass('input-error');
          }
        } catch (e) {
          console.error(e);
        }
      }
    });

    $('#guardar_areas_listo').click(function() {
      let ids = [];
      let nombres = [];
      $('.select-area').each(function() {
        let val = $(this).val();
        if (val && val !== "") {
          ids.push(val);
          nombres.push($(this).find('option:selected').text().trim());
        }
      });
      $('#areas_seleccionadas').val(ids.join('|'));
      let textoTooltip = ids.length > 0 ? nombres.join(', ') : 'Ninguna seleccionada';
      $('#btn_modal_areas').attr('data-original-title', textoTooltip).tooltip('fixTitle');

      if (ids.length > 0) $('#btn_modal_areas').removeClass('btn-error-sombreado');
    });

    $('#guardar_especialidades_listo').click(function() {
      let ids = [];
      let nombres = [];
      $('.select-especialidad').each(function() {
        let val = $(this).val();
        if (val && val !== "") {
          ids.push(val);
          nombres.push($(this).find('option:selected').text().trim());
        }
      });
      $('#especialidades_seleccionadas').val(ids.join('|'));
      let textoTooltip = ids.length > 0 ? nombres.join(', ') : 'Ninguna seleccionada';
      $('#btn_modal_especialidades').attr('data-original-title', textoTooltip).tooltip('fixTitle');

      if (ids.length > 0) $('#btn_modal_especialidades').removeClass('btn-error-sombreado');
    });

    $('#abrirModalGuardar').on('click', async function(e) {
      e.preventDefault();
      limpiarErrores();

      $('#btn_modal_areas, #btn_modal_especialidades').removeClass('btn-error-sombreado');
      if (!validarCedula()) return;

      try {
        const duplicados = await verificarDuplicados();
        let mensajeError = "";

        if (duplicados.existe_cedula) {
          $('#cedula').addClass('input-error');
          mensajeError += '⚠ <b>Cédula Existente:</b> El número de cédula ya se encuentra registrado.<br><br>';
        }
        if (duplicados.existe_email) {
          $('#correo').addClass('input-error');
          mensajeError += '⚠ <b>Correo Existente:</b> El email ya se encuentra registrado en el sistema.<br>';
        }

        if (mensajeError !== "") {
          mostrarAviso(mensajeError);
          return;
        }
      } catch (error) {
        mostrarAviso('⚠️ Error de Sistema: Ocurrió un error al verificar los datos en el servidor.');
        return;
      }

      const nombresLegibles = {
        'cedula': 'Cédula',
        'nombre': 'Nombres',
        'apellido': 'Apellidos',
        'fecha_nacimiento': 'Fecha de nacimiento',
        'prefijo': 'Prefijo Telefónico',
        'telefono': 'Teléfono',
        'genero': 'Sexo',
        'correo': 'Email',
        'fecha_ingreso': 'Fecha de ingreso',
        'areas_seleccionadas': 'Áreas / Departamentos',
        'especialidades_seleccionadas': 'Especialidades',
        'cod_colegiatura': 'Número de colegiatura',
        'password': 'Contraseña',
        'confirm_password': 'Confirmar Contraseña'
      };

      var formularioValido = true;
      var camposFaltantes = [];
      let ids_areas_check = [];

      $('.select-area').each(function() {
        if ($(this).val()) ids_areas_check.push($(this).val());
      });
      $('#areas_seleccionadas').val(ids_areas_check.join('|'));

      let ids_esps_check = [];
      $('.select-especialidad').each(function() {
        if ($(this).val()) ids_esps_check.push($(this).val());
      });
      $('#especialidades_seleccionadas').val(ids_esps_check.join('|'));

      $('#formularioMedico [required]').each(function() {
        var $input = $(this);
        var valor = $input.val();
        var nombreInput = $input.attr('name') || $input.attr('id');

        if (valor === null || valor.trim() === "" || valor.includes("--- Seleccione")) {
          formularioValido = false;

          if (nombreInput === 'areas_seleccionadas') {
            $('#btn_modal_areas').addClass('btn-error-sombreado');
          } else if (nombreInput === 'especialidades_seleccionadas') {
            $('#btn_modal_especialidades').addClass('btn-error-sombreado');
          } else {
            $input.addClass('input-error');
          }

          var nombreMostrar = nombresLegibles[nombreInput] || nombreInput;
          if (!camposFaltantes.includes(nombreMostrar)) {
            camposFaltantes.push(nombreMostrar);
          }
        }
      });

      if (!formularioValido) {
        let listaHtml = "<ul style='text-align: left; margin-top: 10px; margin-bottom: 0;'>";
        camposFaltantes.forEach(function(campo) {
          listaHtml += "<li><b>" + campo + "</b></li>";
        });
        listaHtml += "</ul>";

        mostrarAviso('⚠️ Campos Incompletos: Por favor, llene los siguientes campos obligatorios:' + listaHtml);
        return;
      }

      const fechaNacimientoStr = document.getElementById("fechaN").value;
      const fechaIngresoStr = document.getElementById("fechaI").value;

      if (fechaNacimientoStr && fechaIngresoStr) {
        const fechaNac = new Date(fechaNacimientoStr);
        const fechaIng = new Date(fechaIngresoStr);
        const minIngresoDate = new Date(fechaNac.getFullYear() + 18, fechaNac.getMonth(), fechaNac.getDate());

        const minYear = minIngresoDate.getFullYear();
        const minMonth = String(minIngresoDate.getMonth() + 1).padStart(2, '0');
        const minDay = String(minIngresoDate.getDate()).padStart(2, '0');
        const minDateString = `${minYear}-${minMonth}-${minDay}`;

        if (fechaIng < minIngresoDate) {
          $('#fechaI').addClass('input-error');
          mostrarAviso('⚠️ Error de Edad Mínima: La Fecha de Ingreso (' + fechaIngresoStr + ') debe ser posterior o igual a los 18 años cumplidos del médico (mínimo: ' + minDateString + ').');
          return;
        }
      }

      var email = $('#correo').val();
      if (email && !email.includes('@')) {
        $('#correo').addClass('input-error');
        mostrarAviso('⚠️ Error de Email: El campo de Email debe contener el símbolo "@".');
        return;
      }

      var password = $('#password_medico').length ? $('#password_medico').val() : $('#password_edit').val();
      var confirmPassword = $('#confirm_password_medico').length ? $('#confirm_password_medico').val() : $('#confirm_password_edit').val();

      if (($('#crear_usuario').is(':checked')) || (password !== undefined && password.length > 0)) {
        if (password.length < 6) {
          $('.pass-input, #password_edit').addClass('input-error');
          mostrarAviso('⚠️ Error: La contraseña debe tener al menos 6 caracteres.');
          return;
        }
        if (password !== confirmPassword) {
          $('.pass-input, #confirm_password_edit').addClass('input-error');
          mostrarAviso('⚠️ Error: Las contraseñas no coinciden.');
          return;
        }

        const validacionRedHora = await validarConexionYHora();
        if (!validacionRedHora.valido) {
          $('.pass-input, #password_edit, #confirm_password_edit').addClass('input-error');
          mostrarAviso(validacionRedHora.error);
          return;
        }
      }
      abrirModalGuardar();
    });

    $('#confirmarGuardadoFinal').on('click', function() {
      $('#modalGuardarMedico').modal('hide');
      $('#formularioMedico').submit();
    });

    $('#abrirModalRegresar').on('click', function() {
      $('#modalRegresarMedico').modal('show');
    });
  </script>