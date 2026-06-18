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

    .btn-error-sombreado {
      background-color: #f8d7da !important;
      color: #721c24 !important;
      border: 1px solid #f5c6cb !important;
      box-shadow: 0 0 10px rgba(220, 53, 69, 0.6) !important;
      transition: all 0.3s ease;
    }

    .modal.in .modal-dialog,
    #avisoModal,
    #modalGuardarMedico,
    #modalRegresarMedico {
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
                                p.id, p.email, p.nombre, p.apellido, p.tipo_cedula, p.cedula, p.fecha_nacimiento, p.genero, p.password,
                                dm.fecha_ingreso, 
                                dm.cod_colegiatura,
                                dm.tipo_medico,
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
                        <!--<option value="E" <?php echo ($row['tipo_cedula'] == 'E' ? 'selected' : ''); ?>>E-</option>-->
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
                        <option value="">--- Seleccione un genero ---</option>
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
                      <input type="text" class="form-control" name="cod_colegiatura" id="cod_colegiatura" value="<?php echo $row['cod_colegiatura']; ?>" minlength="4" maxlength="7" placeholder="Ingrese solo numeros (entre 4 y 7 digitos)" oninput="this.value = this.value.replace(/[^0-9]/g, '');" required>
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-3">
                      <p>Tipo (*):</p>
                      <select name="tipo_medico" class="form-control" required>
                        <option value="">--- Seleccione el tipo de medico ---</option>
                        <option value="Interno" <?php echo ($row['tipo_medico'] == 'Interno' ? 'selected' : ''); ?>>Interno</option>
                        <option value="Externo" <?php echo ($row['tipo_medico'] == 'Externo' ? 'selected' : ''); ?>>Externo</option>
                      </select>
                    </div>
                    <br><br><br><br>
                    <div class="col-sm-12" style="margin-top: 20px; border-top: 1px solid #ddd; padding-top: 15px;">
                      <h4 style="color: #00a65a;"><i class="fa fa-lock"></i> Seguridad de la Cuenta</h4>

                      <?php if (!empty($row['password'])) : ?>
                        <div class="alert alert-info" style="padding: 10px; margin-bottom: 10px; border-radius: 5px;">
                          <i class="fa fa-info-circle"></i> <strong>Aviso:</strong> Este médico <strong>ya posee credenciales de acceso</strong> al sistema. Llenar estos campos sobrescribirá su contraseña actual.
                        </div>
                      <?php else : ?>
                        <div class="alert alert-warning" style="padding: 10px; margin-bottom: 10px; border-radius: 5px; color: #8a6d3b; background-color: #fcf8e3; border-color: #faebcc;">
                          <i class="fa fa-exclamation-triangle"></i> <strong>Aviso:</strong> Este médico <strong>NO posee credenciales de acceso</strong>. Llene estos campos si desea otorgarle acceso al sistema.
                        </div>
                      <?php endif; ?>

                      <p class="text-muted">Deje estos campos en blanco si no desea modificar ni asignar una contraseña.</p>
                    </div>

                    <div class="col-sm-4">
                      <p>Nueva Contraseña:</p>
                      <input type="password" class="form-control" name="password" id="password_edit" placeholder="Mínimo 6 caracteres" minlength="6">
                    </div>
                    <div class="col-sm-4">
                      <p>Confirmar Contraseña:</p>
                      <input type="password" class="form-control" id="confirm_password_edit" placeholder="Repita la contraseña">
                    </div>
                    <br><br><br><br><br><br><br><br><br><br><br>
                    <div style="float:right;">
                      <button type="button" class="btn btn-secondary regresar" id="abrirModalRegresar">Regresar</button>
                      <button type="button" class="btn btn-success guardar" id="abrirModalGuardar">Actualizar</button>
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
              <div class="modal-header" style="background-color: #3c8dbc; color: white;">
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

        <div class="modal" id="modalBuscarArea" role="dialog">
          <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
              <div class="modal-header bg-info" style="background-color: #3c8dbc; color: white;">
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
              <div class="modal-header" style="background-color: #3c8dbc; color: white;">
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

        <div class="modal" id="modalBuscarEspecialidad" role="dialog">
          <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
              <div class="modal-header bg-info" style="background-color: #3c8dbc; color: white;">
                <h4 class="modal-title">Buscar Especialidad</h4>
              </div>
              <div class="modal-body">
                <input type="text" id="inputBuscarEspecialidad" class="form-control" placeholder="Escriba para filtrar...">
                <div class="list-group" id="listaResultadosEspecialidad" style="margin-top: 10px; max-height: 200px; overflow-y: auto;"></div>
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
      // LÓGICA: Alternar obligatoriedad de Áreas y Especialidades según Tipo
      // =====================================================================
      function toggleRequisitosMedico() {
        // Obtenemos el valor actual del select de tipo de médico
        const tipoMedico = $('select[name="tipo_medico"]').val();

        const $inputAreas = $('#areas_seleccionadas');
        const $inputEspecialidades = $('#especialidades_seleccionadas');
        const $btnAreas = $('#btn_modal_areas');
        const $btnEspecialidades = $('#btn_modal_especialidades');

        if (tipoMedico === 'Externo') {
          // Si es Externo, removemos el atributo 'required' (pueden quedar vacíos)
          $inputAreas.removeAttr('required');
          $inputEspecialidades.removeAttr('required');

          // Limpiamos los estilos de error en caso de que el usuario haya intentado
          // guardar previamente como "Interno" y se hayan sombreado de rojo
          $btnAreas.removeClass('btn-error-sombreado');
          $btnEspecialidades.removeClass('btn-error-sombreado');
        } else {
          // Si es Interno (o está vacío), volvemos a hacerlos obligatorios
          $inputAreas.attr('required', 'required');
          $inputEspecialidades.attr('required', 'required');
        }
      }

      // 1. Ejecutar al cargar la página (para evaluar el estado precargado desde la Base de Datos)
      $(document).ready(function() {
        toggleRequisitosMedico();
      });

      // 2. Ejecutar automáticamente cada vez que el usuario cambie la opción en el Select
      $(document).on('change', 'select[name="tipo_medico"]', function() {
        toggleRequisitosMedico();
      });

      // =====================================================================
      // LÓGICA PARA ÁREAS / DEPARTAMENTOS
      // =====================================================================
      function agregarFilaArea(idSeleccionado = "") {
        let htmlArea = `
          <div class="row fila-area" style="margin-bottom: 10px;">
              <div class="col-sm-10">
                  <div class="input-group">
                      <select class="form-control select-area">
                          <option value="">--- Seleccione un área ---</option>
                          <?php
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

      // =====================================================================
      // LÓGICA PARA ESPECIALIDADES
      // =====================================================================
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

      // =====================================================================
      // BUSCADORES EN TIEMPO REAL (ÁREAS Y ESPECIALIDADES)
      // =====================================================================
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

      // =====================================================================
      // PRECARGAR DATOS ACTUALES
      // =====================================================================
      $(document).ready(function() {
        <?php
        // Obtenemos el ID de la URL
        $id_doc_edit = $_GET['Id'];

        // Consulta para obtener las Áreas vinculadas actualmente
        $res_a = $conexion->query("SELECT md.Id_departamento FROM medicos_departamentos md 
                                     INNER JOIN detalle_medico dm ON md.Id_detalle_medico = dm.Id_detalle_medico 
                                     WHERE dm.Id_persona = '$id_doc_edit'");
        $list_areas = [];
        while ($ra = $res_a->fetch_assoc()) {
          $list_areas[] = $ra['Id_departamento'];
        }

        // Consulta para obtener las Especialidades vinculadas actualmente
        $res_e = $conexion->query("SELECT em.Id_especialidad FROM especialidades_medicos em 
                                     INNER JOIN detalle_medico dm ON em.Id_detalle_medico = dm.Id_detalle_medico 
                                     WHERE dm.Id_persona = '$id_doc_edit'");
        $list_esps = [];
        while ($re = $res_e->fetch_assoc()) {
          $list_esps[] = $re['Id_especialidad'];
        }
        ?>

        let areasPre = <?php echo json_encode($list_areas); ?>;
        let espsPre = <?php echo json_encode($list_esps); ?>;

        // Cargar Áreas
        if (areasPre && areasPre.length > 0) {
          areasPre.forEach(id => agregarFilaArea(id));
          $('#guardar_areas_listo').trigger('click'); // Forzamos la actualización del tooltip e input hidden
        }

        // Cargar Especialidades
        if (espsPre && espsPre.length > 0) {
          espsPre.forEach(id => agregarFilaEspecialidad(id));
          $('#guardar_especialidades_listo').trigger('click'); // Forzamos la actualización del tooltip e input hidden
        }
      });

      // =====================================================================
      // 1. VALIDACIÓN DE CÉDULA (Formato y Rangos)
      // =====================================================================
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

      // =====================================================================
      // 2. VERIFICACIÓN UNIFICADA DE DUPLICADOS (CÉDULA Y EMAIL)
      // =====================================================================
      
      function verificarDuplicados() {
        return new Promise((resolve, reject) => {
          const cedula = document.getElementById('cedula').value.trim();
          const email = document.getElementById('correo').value.trim();
          
          // Busca si existe el input de id_medico (para el caso de editar)
          const inputId = document.getElementById('medicoId');
          const idMedico = inputId ? inputId.value : 0;

          // Si ambos están vacíos, no hay nada que verificar
          if (cedula === "" && email === "") {
            resolve({ existe_cedula: false, existe_email: false });
            return;
          }

          $.ajax({
            url: 'get/verificar_existencia_cedula.php',
            method: 'POST',
            dataType: 'json',
            data: {
              cedula: cedula,
              email: email,
              id: idMedico // Enviamos 'id' para coincidir con tu PHP: $_POST['id']
            },
            success: function(response) {
              // Devuelve el objeto completo: { existe_cedula: bool, existe_email: bool }
              resolve(response);
            },
            error: function(xhr, status, error) {
              console.error("Error al verificar duplicados:", error);
              reject("Error de conexión con el servidor.");
            }
          });
        });
      }

      // PETICIÓN 1: Validar cédula en tiempo real al salir del campo (blur)
      $('#cedula').on('blur', async function() {
        if ($(this).val().trim() !== "") {
          if (!validarCedula()) return; // Valida formato primero
          try {
            const duplicados = await verificarDuplicados();
            if (duplicados.existe_cedula) {
              $(this).addClass('input-error');
              mostrarAviso('🛑 Cédula Existente: Esta cédula ya está registrada. Por favor, introduzca una diferente.');
            } else {
              $(this).removeClass('input-error');
            }
          } catch (e) {
            console.error(e);
          }
        }
      });
      $('#tipo_cedula').on('change', validarCedula);

      // PETICIÓN NUEVA: Validar email en tiempo real al salir del campo (blur)
      $('#correo').on('blur', async function() {
        if ($(this).val().trim() !== "") {
          try {
            const duplicados = await verificarDuplicados();
            if (duplicados.existe_email) {
              $(this).addClass('input-error');
              mostrarAviso('🛑 Correo Existente: Este correo electrónico ya está registrado por otro usuario.');
            } else {
              $(this).removeClass('input-error');
            }
          } catch (e) {
            console.error(e);
          }
        }
      });

      // PETICIÓN 2: Quitar la clase de error de los botones al elegir algo y dar "Listo"
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

        // Quitar sombreado rojo si seleccionó al menos una
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

        // Quitar sombreado rojo si seleccionó al menos una
        if (ids.length > 0) $('#btn_modal_especialidades').removeClass('btn-error-sombreado');
      });


      // =====================================================================
      // 3. VALIDACIÓN PRINCIPAL DEL FORMULARIO AL GUARDAR
      // =====================================================================
      $('#abrirModalGuardar').on('click', async function(e) {
        e.preventDefault();

        // Limpieza de errores visuales previa
        limpiarErrores();
        $('#btn_modal_areas, #btn_modal_especialidades').removeClass('btn-error-sombreado');

        // PETICIÓN 1: Verificar cédula ANTES de evaluar el resto del formulario
        if (!validarCedula()) return;

        // VERIFICACIÓN INTEGRAL DE DUPLICADOS EN BASE DE DATOS
        try {
          const duplicados = await verificarDuplicados();
          let mensajeError = "";

          if (duplicados.existe_cedula) {
            $('#cedula').addClass('input-error');
            mensajeError += '🛑 <b>Cédula Existente:</b> El número de cédula ya se encuentra registrado.<br><br>';
          }
          if (duplicados.existe_email) {
            $('#correo').addClass('input-error');
            mensajeError += '🛑 <b>Correo Existente:</b> El email ya se encuentra registrado en el sistema.<br>';
          }

          // Si hay algún error, mostramos el aviso y detenemos el guardado
          if (mensajeError !== "") {
            mostrarAviso(mensajeError);
            return; 
          }
        } catch (error) {
          mostrarAviso('❌ Error de Sistema: Ocurrió un error al verificar los datos en el servidor.');
          return;
        }


        // PETICIÓN 3: Diccionario de nombres legibles para los campos
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

        // Asegurar que los inputs ocultos estén actualizados antes de validar
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

        // Recorrer los campos requeridos
        $('#formularioMedico [required]').each(function() {
          var $input = $(this);
          var valor = $input.val();
          var nombreInput = $input.attr('name') || $input.attr('id');

          if (valor === null || valor.trim() === "" || valor.includes("--- Seleccione")) {
            formularioValido = false;

            // PETICIÓN 2: Marcar los botones con sombreado rojo si faltan áreas o especialidades
            if (nombreInput === 'areas_seleccionadas') {
              $('#btn_modal_areas').addClass('btn-error-sombreado');
            } else if (nombreInput === 'especialidades_seleccionadas') {
              $('#btn_modal_especialidades').addClass('btn-error-sombreado');
            } else {
              $input.addClass('input-error'); // Marcar inputs normales
            }

            // Registrar el nombre del campo faltante
            var nombreMostrar = nombresLegibles[nombreInput] || nombreInput;
            if (!camposFaltantes.includes(nombreMostrar)) {
              camposFaltantes.push(nombreMostrar);
            }
          }
        });

        // Mostrar alerta detallada si hay campos vacíos
        if (!formularioValido) {
          let listaHtml = "<ul style='text-align: left; margin-top: 10px; margin-bottom: 0;'>";
          camposFaltantes.forEach(function(campo) {
            listaHtml += "<li><b>" + campo + "</b></li>";
          });
          listaHtml += "</ul>";

          mostrarAviso('⚠️ Campos Incompletos: Por favor, llene los siguientes campos obligatorios:' + listaHtml);
          return;
        }

        // 3.3. Restricción de Fecha de Ingreso (Mínimo: Nacimiento + 18 años)
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
            mostrarAviso('🚫 Error de Edad Mínima: La Fecha de Ingreso (' + fechaIngresoStr + ') debe ser posterior o igual a los 18 años cumplidos del médico (mínimo: ' + minDateString + ').');
            return;
          }
        }

        // 3.4. Validación de Email
        var email = $('#correo').val();
        if (email && !email.includes('@')) {
          $('#correo').addClass('input-error');
          mostrarAviso('🚫 Error de Email: El campo de Email debe contener el símbolo "@".');
          return;
        }

        // 3.5. Validación de Contraseña (Maneja tanto Agregar como Editar)
        var password = $('#password_medico').length ? $('#password_medico').val() : $('#password_edit').val();
        var confirmPassword = $('#confirm_password_medico').length ? $('#confirm_password_medico').val() : $('#confirm_password_edit').val();

        // En agregar valida si el checkbox está marcado. En editar valida si el usuario escribió algo.
        if (($('#crear_usuario').is(':checked')) || (password !== undefined && password.length > 0)) {
          if (password.length < 6) {
            $('.pass-input, #password_edit').addClass('input-error');
            mostrarAviso('🚫 Error: La contraseña debe tener al menos 6 caracteres.');
            return;
          }
          if (password !== confirmPassword) {
            $('.pass-input, #confirm_password_edit').addClass('input-error');
            mostrarAviso('🚫 Error: Las contraseñas no coinciden.');
            return;
          }
        }

        // Si TODAS las validaciones pasan
        abrirModalGuardar();
      });

      // 4. Confirmación Modal
      $('#confirmarGuardadoFinal').on('click', function() {
        $('#modalGuardarMedico').modal('hide');
        $('#formularioMedico').submit();
      });

      // 5. Cancelación Modal
      $('#abrirModalRegresar').on('click', function() {
        $('#modalRegresarMedico').modal('show');
      });

      // 6. Cierre de modales
      $('.modal').on('click', '[data-dismiss="modal"]', function() {
        var $modal = $(this).closest('.modal');
        $modal.removeClass('in').addClass('out');
        setTimeout(function() {
          $modal.modal('hide');
          $modal.removeClass('out');
        }, 400);
      });

      $('.modal').on('hidden.bs.modal', function() {
        if ($('.modal:visible').length) $('body').addClass('modal-open');
        else $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
      });
    </script>

</html>