<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Medico | Editar</title>
  <?php
    include('includes/headerNav2.php');
  ?>
  <style>
    /* ---------------------------------------------------------------------- */
    /* ANIMACIONES Y ESTILOS DE MODALES (Añadidos para consistencia) */
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
        Editar Medico
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-user"></i>Medico</a></li>
        <li class="active"><a href="#"><i class="fa fa-pencil"></i>Editar</a></li>
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
                  <form action="../../cfg/editar/editar_medico.php" id="formularioMedico" class="form-group" method="POST">
                    <?php

                    include("../../cfg/conexion.php");

                    $sql = "SELECT 
                                p.id, p.email, p.nombre, p.apellido, p.tipo_cedula, p.cedula, p.fecha_nacimiento, p.genero, 
                                dm.fecha_ingreso, 
                                tp.telefono, 
                                pt.Id AS Id_prefijo, pt.prefijo,
                                md.Id_departamento, 
                                d.nombre_departamento,
                                em.Id_especialidad,
                                e.nombre_especialidad
                            FROM persona p
                            JOIN telefonos_personas tp ON p.id = tp.Id_persona
                            JOIN prefijos_telefonos pt ON tp.Id_prefijo = pt.Id
                            JOIN detalle_medico dm ON p.id = dm.Id_persona
                            LEFT JOIN medicos_departamentos md ON dm.Id_detalle_medico = md.Id_detalle_medico
                            LEFT JOIN departamento d ON md.Id_departamento = d.Id_departamento
                            LEFT JOIN especialidades_medicos em ON dm.Id_detalle_medico = em.Id_detalle_medico
                            LEFT JOIN especialidad e ON em.Id_especialidad = e.Id_especialidad
                            WHERE p.id =" . $_GET['Id'];
                    $resultado = $conexion->query($sql);

                    $row = $resultado->fetch_assoc();

                    ?>

                    <input type="hidden" name="Id" id="medicoId" value="<?= $row['id']; ?>">
                    
                    <label class="control-label"></label>
                    <div class="col-sm-1 pull-left" style="margin-top: 30px;">
                      <select name="tipo_cedula" id="tipo_cedula" class="form-control" style="width: 60px;">
                        <option value="V" <?php echo ($row['tipo_cedula'] == 'V' ? 'selected' : ''); ?>>V-</option>
                        <option value="E" <?php echo ($row['tipo_cedula'] == 'E' ? 'selected' : ''); ?>>E-</option>
                      </select>
                    </div>
                    <div class="col-sm-3">
                      <p>Cedula (*)</p>
                      <input type="text" class="form-control" name="cedula" id="cedula" placeholder="N° de Cedula" value="<?php echo $row['cedula']; ?>" required>
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-4">
                      <p>Nombres (*):</p>
                      <input type="text" class="form-control" name="nombre" id="nombre" placeholder="Nombres de la Persona" value="<?php echo $row['nombre']; ?>" required>
                    </div>
                    <div class="col-sm-3">
                      <p>Apellidos:</p>
                      <input type="text" class="form-control" name="apellido" id="apellido" placeholder="Apellidos de la Persona" value="<?php echo $row['apellido']; ?>">
                    </div>
                    <br><br><br><br>
                    <label class="control-label"></label>
                    <div class="col-sm-3">
                      <p>Fecha de nacimiento (*):</p>
                      <input type="date" class="form-control pull-right" id="fechaN" name="fecha_nacimiento" value="<?php echo $row['fecha_nacimiento']; ?>" onchange="calcularEdad(); setMinDateIngreso();" max="" required>
                    </div>
                    <div class="col-sm-1" style="margin-top: 30px;">
                      <input type="text" class="form-control pull-right" id="edad" name="edad" readonly>
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-1 pull-left" style="margin-top: 30px;">
                      <select name="prefijo" class="form-control" style="width: 70px;" required>
                        <?php
                        // Carga el prefijo actual y lista los demás.
                        echo "<option selected value='" . $row['Id_prefijo'] . "'>" . $row['prefijo'] . "</option>";

                        $sql2 = $conexion->query("SELECT Id, prefijo FROM prefijos_telefonos");
                        while ($resultado2 = $sql2->fetch_assoc()) {
                           if ($resultado2['Id'] != $row['Id_prefijo']) {
                                echo "<option value='" . $resultado2['Id'] . "'>" . $resultado2['prefijo'] . "</option>";
                           }
                        }

                        ?>
                      </select>
                    </div>
                    <div class="col-sm-3">
                      <p>Telefono (*):</p>
                      <input type="text" class="form-control" value="<?php echo $row['telefono']; ?>" name="telefono" id="telefono" placeholder="N° De Telefono" required>
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-3">
                      <p>Sexo (*):</p>
                      <select name="genero" class="form-control" required>
                        <option value="">--- Seleccione Un Genero ---</option>
                        <option value="Masculino" <?php echo ($row['genero'] == 'Masculino' ? 'selected' : ''); ?>>Masculino</option>
                        <option value="Femenino" <?php echo ($row['genero'] == 'Femenino' ? 'selected' : ''); ?>>Femenino</option>
                      </select>
                    </div>
                    <br><br><br><br>
                    <label class="control-label"></label>
                    <div class="col-sm-4">
                      <p>Email (*):</p>
                      <input type="text" class="form-control" value="<?php echo $row['email']; ?>" name="correo" id="correo" placeholder="nombre.apellido@dominio.com" required>
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-4">
                      <p>Fecha de ingreso (*):</p>
                      <input type="date" name="fecha_ingreso" id="fechaI" class="form-control" value="<?php echo $row['fecha_ingreso']; ?>" required>
                    </div>
                    <label class="control-label"></label>           
                    <div class="col-sm-3">
                      <p>Area (*):</p>
                      <select name="area" class="form-control" required>
                        <?php
                        // Carga el departamento actual y lista los demás.

                        if ($row['Id_departamento'] && $row['nombre_departamento']) {
                            echo "<option selected value='" . $row['Id_departamento'] . "'>" . $row['nombre_departamento'] . "</option>";
                        } else {
                            echo "<option selected value=''>--- Seleccione un Área ---</option>";
                        }

                        $sql2 = $conexion->query("SELECT Id_departamento, nombre_departamento FROM departamento");
                        while ($resultado2 = $sql2->fetch_assoc()) {
                            // Evitar repetir la opción seleccionada si ya se mostró
                            if ($resultado2['Id_departamento'] != $row['Id_departamento']) {
                                echo "<option value='" . $resultado2['Id_departamento'] . "'>" . $resultado2['nombre_departamento'] . "</option>";
                            }
                        }

                        ?>
                      </select>
                    </div>
                    <br><br>
                    <label class="control-label"></label>
                    <div class="col-sm-4">
                    <p>Especialidad (*):</p>
                    <select name="especialidad" class="form-control" required>
                        <?php
                        // Carga la especialidad actual y lista las demás.

                        if ($row['Id_especialidad'] && $row['nombre_especialidad']) {
                            echo "<option selected value='" . $row['Id_especialidad'] . "'>" . $row['nombre_especialidad'] . "</option>";
                        } else {
                            echo "<option selected value=''>Seleccione una Especialidad</option>";
                        }

                        $sql2 = $conexion->query("SELECT Id_especialidad, nombre_especialidad FROM especialidad");
                        while ($resultado2 = $sql2->fetch_assoc()) {
                            // Evitar repetir la opción seleccionada si ya se mostró
                            if ($resultado2['Id_especialidad'] != $row['Id_especialidad']) {
                                echo "<option value='" . $resultado2['Id_especialidad'] . "'>" . $resultado2['nombre_especialidad'] . "</option>";
                            }
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
              <div class="modal-header bg-green" style="background-color: #00a65a; color: white;">
                <h5 class="modal-title" id="modalGuardarMedicoLabel" style="color: white;">Confirmacion de Guardado</h5>
              </div>
              <div class="modal-body">
                <p>¿Está seguro de que desea actualizar la información del medico?</p>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="confirmarGuardadoFinal">Actualizar</button> 
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
    const today = new Date().toISOString().split("T")[0];
    const fechaNacimientoInput = document.getElementById("fechaN");
    const fechaIngresoInput = document.getElementById("fechaI");

    // =====================================================================
    // LÓGICA: Restricción de Fecha de Nacimiento (Máximo: 18 Años Atrás)
    // =====================================================================
    function getMaxDateNacimiento() {
        const d = new Date();
        d.setFullYear(d.getFullYear() - 18); 
        return d.toISOString().split('T')[0];
    }
    document.getElementById("fechaN").max = getMaxDateNacimiento(); 
    
    // =====================================================================
    // LÓGICA: Restricción de Fecha de Ingreso (Mínimo: Nacimiento + 18 años)
    // =====================================================================
    /*
     * Restringe la Fecha de Ingreso (fechaI) para que sea MINIMUM la Fecha de Nacimiento + 18 años.
     */
    function setMinDateIngreso() {
        if (fechaIngresoInput && fechaNacimientoInput.value) {
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

            // 3. Si la fecha de ingreso actual es anterior a la nueva fecha mínima, la limpia para forzar la selección.
            if (fechaIngresoInput.value && fechaIngresoInput.value < minDateString) {
                 fechaIngresoInput.value = ''; 
            }
        } else if (fechaIngresoInput) {
             fechaIngresoInput.min = ""; 
             fechaIngresoInput.max = today; 
        }
    }
    
    // Se inicializa y se llama al cambiar la fecha de nacimiento
    $(document).ready(setMinDateIngreso);
    
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
    // 1. VALIDACIÓN DE CÉDULA (Formato y Rangos)
    // =====================================================================
    const tipoSelect = document.getElementById('tipo_cedula');
    const cedulaInput = document.getElementById('cedula');

    function validarCedula() {
        const tipo = tipoSelect.value;
        const cedula = parseInt(cedulaInput.value, 10);
        
        // Limpiar el error visual antes de validar
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

    $(cedulaInput).on('blur', validarCedula);
    $(tipoSelect).on('change', validarCedula);
    

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
            mostrarAviso('⚠️ Error: Todos los campos obligatorios (*) deben estar llenos.');
            return; 
        }
        
        // 3.3. Validación de Fechas de Nacimiento e Ingreso
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
                mostrarAviso('🚫 Error de Edad Mínima: La Fecha de Ingreso (' + fechaIngresoStr + ') debe ser posterior o igual a los 18 años cumplidos del médico (mínimo: ' + minDateString + ').');
                return; 
            }
        }
        
        // 3.5. Validación de Email (@ obligatorio)
        var email = $('#correo').val();
        if (email && !email.includes('@')) { 
            $('#correo').addClass('input-error');
            mostrarAviso('🚫 Error de Email: El campo de Email debe contener el símbolo "@" para ser válido.');
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
    
    // 6. FIX CLAVE: CERRAR MODALES CON data-dismiss (RESTAURADO)
    $('.modal').on('click', '[data-dismiss="modal"]', function() {
        var $modal = $(this).closest('.modal');
        $modal.removeClass('in').addClass('out');

        setTimeout(function() {
            $modal.modal('hide');
            $modal.removeClass('out');
        }, 400); 
    });

    // 7. LIMPIEZA ADICIONAL PARA MODALES (RESTAURADA)
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