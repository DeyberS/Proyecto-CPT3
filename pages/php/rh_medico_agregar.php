<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Medico | Añadir</title>
  <?php
    include('includes/headerNav2.php');
  ?>
  <style>
    /* ---------------------------------------------------------------------- */
    /* ANIMACIONES Y ESTILOS DE MODALES */
    /* ---------------------------------------------------------------------- */
    @keyframes pulse-opacity {
        0% { opacity: 0; }
        100% { opacity: 1; }
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-50px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeOut {
        from { opacity: 1; transform: translateY(0); }
        to { opacity: 0; transform: translateY(-50px); }
    }
    .modal.in .modal-dialog, #avisoModal, #modalGuardarMedico, #modalRegresarMedico {
        animation: fadeIn 0.4s ease-out;
    }
    .modal.out .modal-dialog {
        animation: fadeOut 0.4s ease-in;
    }
    .modal-open .modal-backdrop {
        opacity: 0.7 !important;
        animation: pulse-opacity 0.3s forwards;
    }
    /* ---------------------------------------------------------------------- */
    /* ESTILOS DE VALIDACIÓN Y LAYOUT */
    /* ---------------------------------------------------------------------- */
    .input-error {
        border: 2px solid crimson !important;
        box-shadow: 0 0 5px crimson;
    }
    .modal {
        position: fixed !important;
        z-index: 99999 !important; 
    }
    .modal-backdrop {
        z-index: 99998 !important;
        transition: .5s;
    }
    .modal.in {
        display: block;
    }
  </style>
  <div class="content-wrapper">
    <section class="content-header">
      <h1>
        Añadir Medico
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-user"></i>Medico</a></li>
        <li class="active"><a href="#"><i class="fa fa-user-plus"></i>Añadir</a></li>
      </ol>
    </section>

    <section class="content">

      <div class="row">
        <div class="col-md-12">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#tab_1" data-toggle="tab">Datos de la Persona</a></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane active" id="tab_1">
                <div class="box-body">
                  <form action="../../cfg/agregar/agregar_medico.php" id="formularioMedico" class="form-group" method="POST">
                    <label class="control-label"></label>
                    <div class="col-sm-1 pull-left" style="margin-top: 30px;">
                      <select name="tipo_cedula" id="tipo_cedula" class="form-control" style="width: 60px;">
                        <option value="V">V-</option>
                        <option value="E">E-</option>
                      </select>
                    </div>
                    <div class="col-sm-3">
                      <p>Cedula (*)</p>
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
                      <p>Telefono (*):</p>
                      <input type="text" class="form-control" name="telefono" id="telefono" placeholder="N° De Telefono" required>
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-3">
                      <p>Sexo (*):</p>
                      <select name="genero" class="form-control" required>
                        <option value="">--- Seleccione Un Genero ---</option>
                        <option value="Masculino">Masculino</option>
                        <option value="Femenino">Femenino</option>
                      </select>
                    </div>
                    <br><br><br><br>
                    <label class="control-label"></label>
                    <div class="col-sm-4">
                      <p>Email (*):</p>
                      <input type="text" class="form-control" value="" name="correo" id="correo" placeholder="nombre.apellido@dominio.com" required> 
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-4">
                      <p>Fecha de ingreso (*):</p>
                      <input type="date" name="fecha_ingreso" id="fechaI" class="form-control" value="" required>
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-3">
                    <p>Area (*):</p>
                      <select name="area" class="form-control" required> 
                        <option selected="disabled">--- Seleccione un Departamento ---</option>
                        <?php
                            include('../../cfg/conexion.php');
                            $sql = $conexion->query("SELECT * FROM departamento");
                            while ($resultado = $sql->fetch_assoc()) {
                            echo "<option value='" . $resultado["Id_departamento"] . "'>" . $resultado['nombre_departamento'] . "</option>";
                          }
                        ?>
                      </select>
                    </div>
                    <br><br><br><br>
                    <label class="control-label"></label>
                    <div class="col-sm-4">
                    <p>Especialidad (*):</p>
                      <select name="especialidad" class="form-control"required> 
                        <option selected="disabled">--- Seleccione una Especialidad ---</option>
                        <?php
                            include('../../cfg/conexion.php');
                            $sql = $conexion->query("SELECT * FROM especialidad");
                            while ($resultado = $sql->fetch_assoc()) {
                            echo "<option value='" . $resultado["Id_especialidad"] . "'>" . $resultado['nombre_especialidad'] . "</option>";
                          }
                        ?>
                      </select>
                    </div>
                    <br><br><br><br><br>
                    <div style="float:right;">
                      <button type="button" class="btn btn-secondary regresar" id="abrirModalRegresar">Regresar</button>
                      <button type="button" class="btn btn-success guardar" id="abrirModalGuardar">Guardar</button>
                    </div>
                    <br>
                  </form>
                </div>
              </div>
            </div>
            </div>
        </div>
        
        <div class="modal" id="avisoModal" tabindex="-1" role="dialog" aria-labelledby="avisoModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header bg-crimson">
                <h5 class="modal-title" id="avisoModalLabel" style="color: white;">Aviso de Validación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <p id="avisoTexto"></p>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
              </div>
            </div>
          </div>
        </div>
        
        <div class="modal" id="modalGuardarMedico" tabindex="-1" role="dialog" aria-labelledby="modalGuardarMedicoLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header" style="background-color: #00a65a; color: white;">
                <h5 class="modal-title" id="modalGuardarMedicoLabel" style="color: white;">Confirmacion de Guardado</h5>
              </div>
              <div class="modal-body">
                <p>¿Está seguro de que desea guardar la información del medico?</p>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="confirmarGuardadoFinal">Guardar</button> 
              </div>
            </div>
          </div>
        </div>

        <div class="modal" id="modalRegresarMedico" tabindex="-1" role="dialog" aria-labelledby="modalRegresarMedicoLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header bg-crimson">
                <h5 class="modal-title" id="modalRegresarMedicoLabel" style="color: white;">Confirmacion de Regreso</h5>
              </div>
              <div class="modal-body">
              <p>Al hacer clic en "Abandonar Formulario", perderá todos los datos no guardados. ¿Desea continuar?</p>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <a href="rh_medico_listado.php" class="btn btn-danger">Abandonar Formulario</a>
              </div>
            </div>
          </div>
        </div>
    </section>
    <?php
    include('includes/footer.php');
    ?>
    </body>
<script>
  // =====================================================================
  // VALIDACIONES DE ENTRADA (Mantenidas)
  // =====================================================================
  function soloLetras(inputElement) {
    if (!inputElement) return;
    inputElement.addEventListener('input', function() {
        this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
    });
  }
  
  soloLetras(document.getElementById('nombre'));
  soloLetras(document.getElementById('apellido'));

  function soloNumerosSinE(campo, maxDigitos) {
    campo.addEventListener("keydown", function (e) {
      const teclasPermitidas = ["Backspace", "Tab", "ArrowLeft", "ArrowRight", "Delete"];

      if (teclasPermitidas.includes(e.key)) return;
      if (e.key.toLowerCase() === "e") { e.preventDefault(); return; }
      if (!/^[0-9]$/.test(e.key)) { e.preventDefault(); return; }
      if (campo.value.length >= maxDigitos) { e.preventDefault(); }
    });

    campo.addEventListener("input", function () {
      campo.value = campo.value.replace(/[^0-9]/g, "").slice(0, maxDigitos);
    });
  }

  soloNumerosSinE(document.getElementById("cedula"), 8);
  soloNumerosSinE(document.getElementById("telefono"), 7);
</script>
<script>
    // =====================================================================
    // LÓGICA: Restricción de Fecha de Nacimiento (Máximo: 18 Años Atrás)
    // =====================================================================
    const fechaNacimientoInput = document.getElementById("fechaN");
    const fechaIngresoInput = document.getElementById("fechaI");
    
    /**
     * Calcula la fecha que es exactamente 18 años atrás desde hoy.
     * Esta será la fecha máxima (MAX) permitida en el calendario para Fecha de Nacimiento.
     */
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
    
    // =====================================================================
    // LÓGICA: Restricción de Fecha de Ingreso (Mínimo: Fecha de Nacimiento) - NUEVA RESTRICCIÓN
    // =====================================================================
    /**
     * Restringe la Fecha de Ingreso (fechaI) para que sea MINIMUM la Fecha de Nacimiento (fechaN).
     */
    function setMinDateIngreso() {
        if (fechaNacimientoInput && fechaIngresoInput && fechaNacimientoInput.value) {
            fechaIngresoInput.min = fechaNacimientoInput.value;
            // También se asegura de que la fecha de ingreso no sea mayor a hoy
            fechaIngresoInput.max = new Date().toISOString().split('T')[0];

            // Si la fecha de ingreso actual es anterior a la de nacimiento, la limpia para forzar la selección.
            if (fechaIngresoInput.value && fechaIngresoInput.value < fechaNacimientoInput.value) {
                 fechaIngresoInput.value = ''; 
            }
        }
    }
    
    // Se inicializa y se llama al cambiar la fecha de nacimiento
    $(document).ready(setMinDateIngreso);
    // Nota: el onchange de fechaN ahora llama a setMinDateIngreso()
    
    // Función que calcula la edad (Mantenida)
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
</script>
  
<script>
    // =====================================================================
    // FUNCIONES AUXILIARES Y MANEJO DE MODALES
    // =====================================================================
    function mostrarAviso(mensaje) {
        $('#avisoTexto').html(mensaje);
        $('#avisoModal').modal('show');
    }

    function limpiarErrores() {
        $('input, select').removeClass('input-error');
    }

    function abrirModalGuardar() {
        $('#modalGuardarMedico').modal('show');
    }

    // =====================================================================
    // 1. VALIDACIÓN DE CÉDULA
    // =====================================================================
    const tipoSelect = document.getElementById('tipo_cedula');
    const cedulaInput = document.getElementById('cedula');

    function validarCedula() {
        const tipo = tipoSelect.value;
        const cedula = parseInt(cedulaInput.value, 10);
        
        if (isNaN(cedula) || cedulaInput.value.trim() === "") return true;

        if (tipo === 'V' && cedula > 80000000) {
            $(cedulaInput).addClass('input-error'); 
            mostrarAviso('⚠️ **Error de Cédula**: Para el tipo V-, la cédula no puede ser mayor a **80.000.000**.');
            return false;
        } else if (tipo === 'E' && cedula < 80000000) {
            $(cedulaInput).addClass('input-error');
            mostrarAviso('⚠️ **Error de Cédula**: Para el tipo E-, la cédula no puede ser menor a **80.000.000**.');
            return false;
        }
        return true;
    }

    $(cedulaInput).on('blur', validarCedula);
    $(tipoSelect).on('change', validarCedula);
    
    // =====================================================================
    // 2. VERIFICACIÓN DE CÉDULA EXISTENTE (AJAX) - NUEVO
    // =====================================================================
    function verificarCedulaExistente() {
        return new Promise((resolve, reject) => {
            const tipo = tipoSelect.value;
            const cedula = cedulaInput.value.trim();
            
            if (cedula === "") { resolve(false); return; }
            
            $.ajax({
                // NOTA: DEBE CREAR ESTE ARCHIVO PHP EN EL SERVIDOR
                url: 'get/get_verificar_cedula.php', 
                method: 'POST',
                dataType: 'json',
                data: {
                    tipo_cedula: tipo,
                    cedula: cedula
                    // No se envía id_medico porque es un nuevo registro
                },
                success: function(response) {
                    // response debe ser {"existe": true} o {"existe": false}
                    resolve(response.existe); 
                },
                error: function(xhr, status, error) {
                    console.error("Error al verificar cédula:", error);
                    reject("Error de conexión con el servidor. (verificar_cedula.php no responde)");
                }
            });
        });
    }

    // =====================================================================
    // 3. VALIDACIÓN PRINCIPAL DEL FORMULARIO
    // =====================================================================
    $('#abrirModalGuardar').on('click', async function(e) {
        e.preventDefault(); 
        
        limpiarErrores();
        var formularioValido = true;
        
        // 3.1. Verificación de campos obligatorios (*)
        $('#formularioMedico [required]').each(function() {
            var $input = $(this);
            var valor = $input.val();
            
            if (valor === null || valor.trim() === "" || valor.includes("--- Seleccione")) {
                $input.addClass('input-error'); 
                formularioValido = false;
            }
        });
        
        if (!formularioValido) {
            mostrarAviso('⚠️ **Error**: Todos los campos obligatorios (*) deben estar llenos.');
            return; 
        }
        
        // 3.2. Validación de Cédula (Formato y rangos)
        if (!validarCedula()) {
            return; 
        }
        
        // 3.3. Validación de Cédula Existente (AJAX)
        try {
            const cedulaExiste = await verificarCedulaExistente();
            if (cedulaExiste) {
                $(cedulaInput).addClass('input-error');
                mostrarAviso('🛑 **Cédula Existente**: El número de Cédula **' + tipoSelect.value + '-' + cedulaInput.value + '** ya se encuentra registrado en el sistema.');
                return;
            }
        } catch (error) {
            mostrarAviso('❌ **Error de Sistema**: Ocurrió un error al verificar la cédula. Detalle: ' + error);
            return;
        }

        // 3.3. RESTRICCIÓN DE FECHA DE INGRESO (Debe ser >= Fecha de Nacimiento + 18 años)
        const fechaNacimientoStr = fechaNacimientoInput.value;
        const fechaIngresoStr = fechaIngresoInput.value;
        
        if (fechaNacimientoStr && fechaIngresoStr) {
            const fechaNac = new Date(fechaNacimientoStr);
            const fechaIng = new Date(fechaIngresoStr);
        
            // Calcular la fecha del 18º cumpleaños
            const minIngresoDate = new Date(fechaNac.getFullYear() + 18, fechaNac.getMonth(), fechaNac.getDate());
            
            // Revertir a string para el mensaje de error (formato YYYY-MM-DD)
            const minYear = minIngresoDate.getFullYear();
            const minMonth = String(minIngresoDate.getMonth() + 1).padStart(2, '0');
            const minDay = String(minIngresoDate.getDate()).padStart(2, '0');
            const minDateString = `${minYear}-${minMonth}-${minDay}`;

            // Verificar si la fecha de ingreso es anterior al 18º cumpleaños
            if (fechaIng < minIngresoDate) { 
                $('#fechaI').addClass('input-error');
                mostrarAviso('🚫 **Error de Edad Mínima**: La Fecha de Ingreso **(' + fechaIngresoStr + ')** debe ser posterior o igual a los **18 años cumplidos** del médico (mínimo: **' + minDateString + '**).');
                return; 
            }
        }

        // 3.4. Validación de Email (@ obligatorio)
        var email = $('#correo').val();
        if (email && !email.includes('@')) { 
            $('#correo').addClass('input-error');
            mostrarAviso('🚫 **Error de Email**: El campo de Email **debe contener** el símbolo "@" para ser válido.');
            return;
        }

        // Si todas las validaciones pasan, abrimos el modal de confirmación
        abrirModalGuardar();
    });
    
    // 4. Lógica para el botón 'Guardar' dentro del modal de confirmación
    $('#confirmarGuardadoFinal').on('click', function() {
        $('#modalGuardarMedico').modal('hide');    
        $('#formularioMedico').submit();
    });
    
    // 5. Lógica para el botón Regresar (Abre el modal)
    $('#abrirModalRegresar').on('click', function() {
        $('#modalRegresarMedico').modal('show');
    });
    
    // 6. FIX CLAVE: CERRAR MODALES CON data-dismiss
    $('.modal').on('click', '[data-dismiss="modal"]', function() {
        var $modal = $(this).closest('.modal');
        $modal.removeClass('in').addClass('out');

        setTimeout(function() {
            $modal.modal('hide');
            $modal.removeClass('out');
        }, 400); 
    });

    // 7. LIMPIEZA ADICIONAL PARA MODALES 
    $('.modal').on('hidden.bs.modal', function () {
        if ($('.modal:visible').length) {
            $('body').addClass('modal-open');
        } else {
            $('body').removeClass('modal-open');
        }
        $('.modal-backdrop').remove(); 
    });
    
</script>
</html>
<script>
    function setMinDateIngreso() {
        const today = new Date().toISOString().split("T")[0];
        
        if (fechaNacimientoInput && fechaIngresoInput && fechaNacimientoInput.value) {
            const fechaNac = new Date(fechaNacimientoInput.value);
            // Calculamos la fecha mínima requerida: Nacimiento + 18 años
            const minDate18 = new Date(fechaNac.getFullYear() + 18, fechaNac.getMonth(), fechaNac.getDate());

            const year = minDate18.getFullYear();
            const month = String(minDate18.getMonth() + 1).padStart(2, '0');
            const day = String(minDate18.getDate()).padStart(2, '0');
            const minDateString = `${year}-${month}-${day}`;

            // 1. Establece la fecha mínima de ingreso (Restricción HTML5)
            fechaIngresoInput.min = minDateString;
            
            // 2. Restricción: No puede ser posterior a hoy
            fechaIngresoInput.max = today; 

            // Nota: No limpiamos el valor de 'fechaIngresoInput.value' en la edición. 
            // La validación principal se encargará de mostrar el error si el valor precargado es inválido.

        } else if (fechaIngresoInput) {
             // Si no hay fecha de nacimiento, solo se restringe la fecha máxima (hoy)
             fechaIngresoInput.min = ""; 
             fechaIngresoInput.max = today; 
        }
    }
    
    // Se inicializa y se llama al cambiar la fecha de nacimiento
    $(document).ready(setMinDateIngreso);
    // Nota: el onchange de fechaN ya llama a setMinDateIngreso()

</script>



