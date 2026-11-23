<?php
include('includes/headerNav2.php');
verificarAcceso('Crear Representantes');
?>

<head>
  <title>Representantes | Añadir</title>
</head>

<div class="content-wrapper">
  <style>
    #avisoModal {
      z-index: 999999 !important;
    }

    .nav-tabs>li>a {
      pointer-events: none;
      cursor: default;
    }

    .nav-tabs li.disabled-tab a {
      color: #b2b2b2 !important;
    }
  </style>

  <section class="content-header">
    <h1>
      Añadir Representante
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="#"><i class="fa fa-users"></i>Representantes</a></li>
      <li class="active"><a href="#"><i class="fa fa-user-plus"></i>Añadir</a></li>
    </ol>
  </section>

  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="nav-tabs-custom">
          <ul class="nav nav-tabs">
            <li class="active" data-tab-name="info"><a href="#info" data-toggle="tab">Datos Personales</a></li>
            <li data-tab-name="direccion" class="disabled-tab"><a href="#direccion" data-toggle="tab">Dirección de Residencia</a></li>
          </ul>

          <form id="formularioRepresentante" action="../../cfg/agregar/agregar_representante.php" method="POST" novalidate>

            <div class="tab-content">
              <div class="tab-pane active" id="info">
                <section id="new" style="margin-bottom:6%;">
                  <div class="row">
                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Cédula (*):</p>
                      <div class="input-group">
                        <span class="input-group-addon" style="padding: 0; border: none;">
                          <select name="tipo_cedula" id="tipo_cedula" class="form-control" style="width: 65px; height: 34px; font-weight: bold; background-color: #f4f4f4; border-right: none; border-top-right-radius: 0; border-bottom-right-radius: 0;">
                            <option value="V">V-</option>
                          </select>
                        </span>
                        <input type="text" class="form-control" name="cedula" id="cedula" placeholder="N° de Cédula" maxlength="8" required>
                      </div>
                    </div>

                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Nombre (*):</p>
                      <input type="text" class="form-control" name="nombre" id="solo_texto" oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]/g, '');" placeholder="Nombre Del Representante" maxlength="100" required>
                    </div>

                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Apellido:</p>
                      <input type="text" class="form-control" name="apellido" id="solo_texto1" oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]/g, '');" placeholder="Apellido Del Representante" maxlength="100">
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-4 col-sm-5 form-group">
                      <p>Fecha de nacimiento / Edad (*):</p>
                      <div class="input-group">
                        <input type="date" class="form-control" id="fechaN" name="fecha_nacimiento" max="<?php echo date('Y-m-d'); ?>" onchange="calcularEdad()" required style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                        <span class="input-group-addon" style="padding: 0; border: none;">
                          <input type="text" class="form-control" id="edad" name="edad" readonly placeholder="Edad" style="width: 80px; height: 34px; text-align: center; background-color: #f4f4f4; font-weight: bold; border-left: none; border-top-left-radius: 0; border-bottom-left-radius: 0;">
                        </span>
                      </div>
                    </div>

                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Sexo (*):</p>
                      <select name="genero" id="genero" class="form-control" required>
                        <option value="">--- Seleccione Un Género ---</option>
                        <option value="Masculino">Masculino</option>
                        <option value="Femenino">Femenino</option>
                      </select>
                    </div>

                    <div class="col-md-4 col-sm-4 form-group">
                      <p>País de nacimiento:</p>
                      <select name="pais_nacimiento" id="pais_nacimiento" class="form-control">
                        <option value="">--- Seleccione un País ---</option>
                        <?php
                        $conexion = new mysqli("localhost", "root", "", "cpt3db");
                        $conexion->set_charset("utf8");
                        $result_nac = $conexion->query("SELECT Id_Pais, nombre_pais FROM pais");
                        while ($row_list = $result_nac->fetch_assoc()) {
                          echo "<option value='{$row_list['Id_Pais']}'>{$row_list['nombre_pais']}</option>";
                        }
                        ?>
                      </select>
                    </div>
                  </div>

                  <div class="row">

                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Estado de nacimiento:</p>
                      <select name="estado_nacimiento" id="estado_nacimiento" class="form-control">
                        <option value="">--- Seleccione Un Estado ---</option>
                      </select>
                    </div>

                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Municipio de nacimiento:</p>
                      <select name="municipio_nacimiento" id="municipio_nacimiento" class="form-control">
                        <option value="">--- Seleccione Un Municipio ---</option>
                      </select>
                    </div>

                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Teléfono (*):</p>
                      <div class="input-group">
                        <span class="input-group-addon" style="padding: 0; border: none;">
                          <select name="prefijo" id="prefijo_telefono" class="form-control" style="width: 80px; height: 34px; font-weight: bold; background-color: #f4f4f4; border-right: none; border-top-right-radius: 0; border-bottom-right-radius: 0;" required>
                            <?php
                            $sql = $conexion->query("SELECT * FROM prefijos_telefonos");
                            while ($resultado = $sql->fetch_assoc()) {
                              echo "<option value='" . $resultado["Id"] . "'>" . $resultado['prefijo'] . "</option>";
                            }
                            ?>
                          </select>
                        </span>
                        <input type="text" class="form-control" name="telefono" id="telefono" placeholder="N° De Teléfono" minlength="7" maxlength="7" required>
                      </div>
                    </div>
                  </div>

                  <div class="row">

                    <div class="col-md-8 col-sm-8 form-group">
                      <p>Correo (*):</p>
                      <input type="email" class="form-control" name="email" id="email" placeholder="nombreapellido2@gmail.com" required>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-sm-12" style="text-align: right; margin-top: 20px;">
                      <button type="button" class="btn btn-second" data-toggle="modal" data-target="#modalConfirmarRegreso">Regresar</button>
                      <button type="button" class="btn btn-primary next-tab" data-tab-actual="#info" data-tab-siguiente="direccion">Siguiente</button>
                    </div>
                  </div>

                </section>
              </div>

              <div class="tab-pane" id="direccion">
                <section id="new" style="margin-bottom: 3%;">

                  <div class="row">
                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Estado:</p>
                      <select name="estado" id="estado" class="form-control">
                        <option value="">--- Seleccione Un Estado ---</option>
                        <?php
                        $result_dir = $conexion->query("SELECT Id_Estado, nombre_estado FROM estado WHERE Id_Pais = 1");
                        while ($row_dir = $result_dir->fetch_assoc()) {
                          echo "<option value='{$row_dir['Id_Estado']}'>{$row_dir['nombre_estado']}</option>";
                        }
                        ?>
                      </select>
                    </div>

                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Municipio:</p>
                      <select name="municipio" id="municipio" class="form-control">
                        <option value="">--- Seleccione Un Municipio ---</option>
                      </select>
                    </div>

                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Sector:</p>
                      <select name="sector" id="sector" class="form-control">
                        <option value="">--- Seleccione Un Sector ---</option>
                      </select>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-4 col-sm-6 form-group">
                      <p>Avenida/Calle:</p>
                      <input type="text" class="form-control" name="avenida_calle">
                    </div>

                    <div class="col-md-4 col-sm-6 form-group">
                      <p>Punto de referencia:</p>
                      <input type="text" class="form-control" name="referencia">
                    </div>

                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Tiempo de residencia:</p>
                      <div class="input-group">
                        <input type="text" class="form-control" name="tiempo_residencia" id="tiempo_residencia" placeholder="Cantidad" style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                        <span class="input-group-addon" style="padding: 0; border: none;">
                          <select name="tiempo" id="tiempo" class="form-control" style="width: 110px; height: 34px; background-color: #f4f4f4; border-left: none; border-top-left-radius: 0; border-bottom-left-radius: 0;">
                            <option value="dia/s">Dia/s</option>
                            <option value="semanas/s">Semanas/s</option>
                            <option value="meses/s">Meses/s</option>
                            <option value="años/s">Año/s</option>
                          </select>
                        </span>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-sm-12" style="text-align: right; margin-top: 20px;">
                      <button type="button" class="btn btn-second prev-tab" data-tab-actual="#direccion" data-tab-anterior="info">Atrás</button>
                      <button type="button" class="btn btn-success next-tab" data-tab-actual="#direccion" data-tab-siguiente="confirmar">Guardar</button>
                    </div>
                  </div>

                </section>
              </div>

            </div>
          </form>
        </div>
      </div>
    </div>
  </section>

  <div class="modal fade" id="avisoModal" tabindex="-1" role="dialog" aria-labelledby="avisoModalLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson" id="avisoModalHeader">
          <h4 class="modal-title" id="avisoModalLabel"><i class="fa fa-warning"></i> Aviso Importante</h4>
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
          <button type="button" class="btn btn-second" data-dismiss="modal">Regresar</button>
          <a href="representantes_listado.php" class="btn btn-danger">Abandonar Formulario</a>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalGuardarRepresentante" tabindex="-1" role="dialog" aria-labelledby="modalGuardarRepresentanteLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-green">
          <h4 class="modal-title" id="modalGuardarRepresentanteLabel"><i class="fa fa-save"></i>Confirmación de Guardado</h4>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea guardar la información del nuevo representante?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Regresar</button>
          <button type="button" class="btn btn-success" id="confirmarGuardadoFinal">Aceptar</button>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
include('includes/footer.php');
?>

<script>
  window.mostrarAviso = function(mensaje) {
    $('#avisoModalHeader').removeClass('bg-green').addClass('bg-crimson');
    $('#avisoModalLabel').html('<i class="fa fa-warning"></i> Aviso Importante');
    $('#avisoTexto').html(mensaje);
    $('#avisoModal').modal('show');
  };

  window.mostrarAvisoExito = function(mensaje) {
    $('#avisoModalHeader').removeClass('bg-crimson').addClass('bg-green');
    $('#avisoModalLabel').html('<i class="fa fa-check"></i> Operación Exitosa');
    $('#avisoTexto').html(mensaje);
    $('#avisoModal').modal('show');
  };

  $('#avisoModal').on('hidden.bs.modal', function() {
    $('#avisoModalHeader').removeClass('bg-green').addClass('bg-crimson');
    $('#avisoModalLabel').html('<i class="fa fa-warning"></i> Aviso Importante');
  });

  $('.modal').on('hidden.bs.modal', function() {
    $(this).removeClass('out');
    if (!$('.modal.in').length) {
      $('body').removeClass('modal-open');
      $('.modal-backdrop').remove();
    } else {
      $('body').addClass('modal-open');
    }
  });

  function limpiarErroresPestana(tabSelector) {
    $(tabSelector).find('.input-error').removeClass('input-error');
  }

  function setMaxDateFor18Years() {
    const today = new Date();
    today.setFullYear(today.getFullYear() - 18);
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    const maxDate = `${year}-${month}-${day}`;
    document.getElementById('fechaN').setAttribute('max', maxDate);
  }
  setMaxDateFor18Years();

  function calcularEdad() {
    var fechaNacimiento = $('#fechaN').val();
    var campoEdad = $('#edad');
    var $fechaInput = $('#fechaN');

    if (fechaNacimiento) {
      var fechaNac = new Date(fechaNacimiento + 'T00:00:00');
      var hoy = new Date();
      hoy.setHours(0, 0, 0, 0);

      if (fechaNac > hoy) {
        mostrarAviso("⚠️ La fecha de nacimiento no puede ser posterior a la fecha actual.");
        $fechaInput.val("");
        $fechaInput.addClass('input-error');
        campoEdad.val("");
        return;
      }

      if (fechaNac.getFullYear() < 1900) {
        mostrarAviso("⚠️ La fecha de nacimiento no puede ser anterior a 1900.");
        $fechaInput.val("");
        $fechaInput.addClass('input-error');
        campoEdad.val("");
        return;
      }

      $fechaInput.removeClass('input-error');

      var edad = hoy.getFullYear() - fechaNac.getFullYear();
      var m = hoy.getMonth() - fechaNac.getMonth();
      if (m < 0 || (m === 0 && hoy.getDate() < fechaNac.getDate())) {
        edad--;
      }

      if (edad < 18) {
        mostrarAviso("⚠️ Este campo es para adultos. La edad calculada es " + edad + " años.");
        campoEdad.val("");
      } else {
        campoEdad.val(edad);
      }
    } else {
      campoEdad.val("");
    }
  }

  $('#fechaN').on('change', function() {
    calcularEdad();
  });
  calcularEdad();

  $('#fechaN').on('input', function() {
    var $input = $(this);
    var valor = $input.val();
    if (!valor) return;

    var anio = parseInt(valor.split('-')[0]);
    var anioActual = new Date().getFullYear();

    if (anio > anioActual) {
      mostrarAviso("⚠️ El año no puede ser mayor a " + anioActual + ".");
      $input.val("");
      $('#edad').val("");
      $input.addClass('input-error');
    } else if (anio < 1900) {
      mostrarAviso("⚠️ El año no puede ser menor a 1900.");
      $input.val("");
      $('#edad').val("");
      $input.addClass('input-error');
    } else {
      $input.removeClass('input-error');
    }
  });

  async function verificarDatosUnicosBD(tipo, cedula, email, idPersona) {
    return new Promise((resolve) => {
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
          tipo_cedula: tipo,
          cedula: cedula,
          email: email,
          id: idPersona
        },
        success: function(response) {
          resolve(response);
        },
        error: function() {
          resolve({
            existe_cedula: false,
            existe_email: false
          });
        }
      });
    });
  }

  async function validarDatosUnicos() {
    const cedulaInput = document.getElementById('cedula') || document.getElementById('cedula_rep');
    const tipoSelect = document.getElementById('tipo_cedula') || document.getElementById('tipo_cedula_rep');
    const emailInput = document.getElementById('email') || document.getElementById('email_rep');

    const cedula = cedulaInput ? cedulaInput.value.trim() : "";
    const tipo = tipoSelect ? tipoSelect.value : "";
    const email = emailInput ? emailInput.value.trim() : "";

    let idActual = 0;
    const inputId = document.querySelector('input[name="Id_representante"]') || document.querySelector('input[name="Id"]');
    if (inputId && inputId.value) idActual = inputId.value;

    let esValido = true;
    let errores = [];

    if (cedulaInput) $(cedulaInput).removeClass('input-error');
    if (tipoSelect) $(tipoSelect).removeClass('input-error');
    if (emailInput) $(emailInput).removeClass('input-error');

    if (email !== "" && (email.indexOf('@') === -1 || email.indexOf('.') === -1)) {
      if (emailInput) $(emailInput).addClass('input-error');
      errores.push("El campo Email debe tener un formato válido (ej: nombreapellido2@gmail.com).");
      esValido = false;
    }

    if (cedula !== "" && !isNaN(parseInt(cedula))) {
      const cedNum = parseInt(cedula);
      if (tipo === 'V' && cedNum > 80000000) {
        if (cedulaInput) $(cedulaInput).addClass('input-error');
        if (tipoSelect) $(tipoSelect).addClass('input-error');
        errores.push("Para tipo V-, la cédula no puede ser mayor a 80.000.000.");
        esValido = false;
      } else if (tipo === 'E' && cedNum < 80000000) {
        if (cedulaInput) $(cedulaInput).addClass('input-error');
        if (tipoSelect) $(tipoSelect).addClass('input-error');
        errores.push("Para tipo E-, la cédula no puede ser menor a 80.000.000.");
        esValido = false;
      }
    }

    if (!esValido) {
      mostrarAviso("⚠️ <b>Errores de formato:</b><br>" + errores.join("<br>"));
      return false;
    }

    if (cedula !== "" || email !== "") {
      const bd = await verificarDatosUnicosBD(tipo, cedula, email, idActual);

      if (bd.existe_cedula) {
        if (cedulaInput) $(cedulaInput).addClass('input-error');
        if (tipoSelect) $(tipoSelect).addClass('input-error');
        errores.push(`El documento <b>${tipo}-${cedula}</b> ya se encuentra registrado en el sistema.`);
        esValido = false;
      }

      if (bd.existe_email) {
        if (emailInput) $(emailInput).addClass('input-error');
        errores.push(`El correo <b>${email}</b> ya está siendo usado por otra persona.`);
        esValido = false;
      }

      if (!esValido) {
        mostrarAviso("⚠️ <b>Datos duplicados detectados:</b><br>" + errores.join("<br>"));
      }
    }

    return esValido;
  }

  if (document.getElementById('cedula')) document.getElementById('cedula').addEventListener('blur', validarDatosUnicos);
  if (document.getElementById('tipo_cedula')) document.getElementById('tipo_cedula').addEventListener('change', validarDatosUnicos);
  if (document.getElementById('email')) document.getElementById('email').addEventListener('blur', validarDatosUnicos);

  $('#email').on('blur', function() {
    var val = $(this).val().trim();
    if (val === "") $(this).addClass('input-error');
    else $(this).removeClass('input-error');
  });

  $('#email').on('input', function() {
    if ($(this).val().trim() !== "") $(this).removeClass('input-error');
  });

  document.getElementById('pais_nacimiento').addEventListener('change', function() {
    var paisId = this.value;
    var estadoSelect = document.getElementById('estado_nacimiento');
    var municipioSelect = document.getElementById('municipio_nacimiento');

    estadoSelect.innerHTML = '<option value="">--- Seleccione Un Estado ---</option>';
    municipioSelect.innerHTML = '<option value="">--- Seleccione Un Municipio ---</option>';

    if (paisId) {
      $.ajax({
        url: '../../cfg/ajax/obtener_estados.php',
        method: 'GET',
        data: {
          Id_Pais: paisId
        },
        dataType: 'json',
        cache: false,
        success: function(data) {
          for (var i = 0; i < data.length; i++) {
            estadoSelect.innerHTML += '<option value="' + data[i].Id_Estado + '">' + data[i].nombre_estado + '</option>';
          }
        }
      });
    }
  });

  document.getElementById('estado_nacimiento').addEventListener('change', function() {
    var estadoId = this.value;
    var municipioSelect = document.getElementById('municipio_nacimiento');
    municipioSelect.innerHTML = '<option value="">--- Seleccione Un Municipio ---</option>';

    if (estadoId) {
      $.ajax({
        url: '../../cfg/ajax/obtener_municipios.php',
        method: 'GET',
        data: {
          Id_Estado: estadoId
        },
        dataType: 'json',
        cache: false,
        success: function(data) {
          for (var i = 0; i < data.length; i++) {
            municipioSelect.innerHTML += '<option value="' + data[i].Id_Municipio + '">' + data[i].nombre_municipio + '</option>';
          }
        }
      });
    }
  });

  document.getElementById('estado').addEventListener('change', function() {
    var estadoId = this.value;
    var municipioSelect = document.getElementById('municipio');
    var sectorSelect = document.getElementById('sector');

    municipioSelect.innerHTML = '<option value="">--- Seleccione Un Municipio ---</option>';
    if (sectorSelect) sectorSelect.innerHTML = '<option value="">--- Seleccione Un Sector ---</option>';

    if (estadoId) {
      $.ajax({
        url: '../../cfg/ajax/obtener_municipios.php',
        method: 'GET',
        data: {
          Id_Estado: estadoId
        },
        dataType: 'json',
        cache: false,
        success: function(data) {
          for (var i = 0; i < data.length; i++) {
            municipioSelect.innerHTML += '<option value="' + data[i].Id_Municipio + '">' + data[i].nombre_municipio + '</option>';
          }
        }
      });
    }
  });

  document.getElementById('municipio').addEventListener('change', function() {
    var municipioId = this.value;
    var sectorSelect = document.getElementById('sector');
    sectorSelect.innerHTML = '<option value="">--- Seleccione Un Sector ---</option>';

    if (municipioId) {
      $.ajax({
        url: '../../cfg/ajax/obtener_sectores.php',
        method: 'GET',
        data: {
          Id_Municipio: municipioId
        },
        dataType: 'json',
        cache: false,
        success: function(data) {
          for (var i = 0; i < data.length; i++) {
            sectorSelect.innerHTML += '<option value="' + data[i].Id_Sector + '">' + data[i].nombre_sector + '</option>';
          }
        }
      });
    }
  });

  async function validarPestana(tabSelector) {
    let esValido = true;
    let errores = [];
    limpiarErroresPestana(tabSelector);

    $(tabSelector).find('[required]').each(function() {
      var $input = $(this);
      var valor = $input.val();

      if ($input.is(':visible') && !$input.prop('disabled')) {
        if (valor === null || valor.trim() === "" || valor === '--- Seleccione Un Género ---' || valor === '--- Seleccione Un Estado ---' || valor === '--- Seleccione Un Municipio ---' || valor === '--- Seleccione Un Sector ---' || valor === '--- Seleccione Una Opción ---' || valor === '--- Seleccione un País ---') {
          $input.addClass('input-error');
          esValido = false;
          if ($input.attr('name') === 'cedula') {
            errores.push("El campo de Cédula es obligatorio.");
          } else if ($input.attr('name') === 'fecha_nacimiento') {
            errores.push("El campo de Fecha de nacimiento es obligatorio.");
          } else {
            errores.push(`El campo '${$input.prev('p').text().replace('(*):', '').trim()}' es obligatorio.`);
          }
        }
      }
    });

    if (tabSelector === '#info') {
      const cedulaEsValida = await validarDatosUnicos();
      if (!cedulaEsValida) esValido = false;

      const $cedula = $('#cedula');
      if ($cedula.length && $cedula.val().trim() === '') {
        $cedula.addClass('input-error');
        esValido = false;
        if (!errores.includes("El campo de Cédula es obligatorio.")) {
          errores.push("El campo de Cédula es obligatorio.");
        }
      }

      const fechaNac = document.getElementById("fechaN").value;
      if (fechaNac) {
        const edad = parseInt(document.getElementById('edad').value);
        if (isNaN(edad) || edad < 1) {
          $('#fechaN').addClass('input-error');
          esValido = false;
          errores.push("La edad debe ser mayor a 0.");
        }
      }

      const edad = parseInt($('#edad').val());
      if (isNaN(edad) || edad < 18 || edad > 120) {
        errores.push("El representante debe ser mayor de 18 años (Fecha de Nacimiento).");
        $('#fechaN').addClass('input-error');
        esValido = false;
      }
    }

    if (!esValido && errores.length > 0) {
      const mensajeError = "Por favor, corrija los siguientes errores:<br>" + errores.filter((v, i, a) => a.indexOf(v) === i).join('<br>');
      mostrarAviso(mensajeError);
    }

    return esValido;
  }

  $('.next-tab').off('click').on('click', async function() {
    const $btn = $(this);
    const tabActualSelector = $btn.data('tab-actual');
    const tabSiguienteName = $btn.data('tab-siguiente');
    const nextTabLink = $(`.nav-tabs li[data-tab-name="${tabSiguienteName}"] a`);

    $btn.prop('disabled', true).text('Validando...');
    const esValido = await validarPestana(tabActualSelector);
    $btn.prop('disabled', false).text(tabSiguienteName === 'confirmar' ? 'Guardar' : 'Siguiente');

    if (esValido) {
      if (tabSiguienteName === 'confirmar') {
        const cedulaFinalValida = await validarDatosUnicos();
        if (!cedulaFinalValida) {
          mostrarAviso("⚠️ Conflicto de Cédula. Regrese a la pestaña 'Datos Personales' y corrija la información.");
          return;
        }

        $('#modalGuardarRepresentante').modal('show');
      } else {
        $('.nav-tabs li').removeClass('active');
        $('.tab-content .tab-pane').removeClass('active');
        $(`.nav-tabs li[data-tab-name="${tabSiguienteName}"]`).removeClass('disabled-tab').addClass('active');
        nextTabLink.tab('show');
        $('#' + tabSiguienteName).addClass('active');
      }
    }
  });

  $('.prev-tab').on('click', function() {
    const $btn = $(this);
    const tabAnteriorName = $btn.data('tab-anterior');

    if (tabAnteriorName) {
      const prevTabLink = $(`.nav-tabs li[data-tab-name="${tabAnteriorName}"] a`);
      const $anteriorTabLi = $(`.nav-tabs li[data-tab-name="${tabAnteriorName}"]`);

      $('.nav-tabs li').removeClass('active');
      $('.tab-content .tab-pane').removeClass('active');
      $anteriorTabLi.addClass('active');
      prevTabLink.tab('show');
      $('#' + tabAnteriorName).addClass('active');
    } else {
      $('#modalConfirmarRegreso').modal('show');
    }
  });

  $('#confirmarGuardadoFinal').on('click', function() {
    $('#modalGuardarRepresentante').modal('hide');
    $('#formularioRepresentante').submit();
  });

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
      if (campo.value.length >= maxDigitos) e.preventDefault();
    });
    campo.addEventListener("input", function() {
      campo.value = campo.value.replace(/[^0-9]/g, "").slice(0, maxDigitos);
    });
  }

  soloNumerosSinE(document.getElementById("cedula"), 8);
  soloNumerosSinE(document.getElementById("telefono"), 7);
  soloNumerosSinE(document.getElementById("tiempo_residencia"));
</script>