<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Medicamentos | Editar</title>
  <?php
  include('includes/headerNav2.php');
  ?>
  <style>
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

    .modal.in .modal-dialog, #avisoModal, #modalGuardar {
      animation: fadeIn 0.4s ease-out;
    }

    .modal.out .modal-dialog {
      animation: fadeOut 0.4s ease-in;
    }

    .modal-open .modal-backdrop {
      opacity: 0.7 !important;
      animation: pulse-opacity 0.3s forwards;
    }

    /* ESTILOS DE VALIDACIÓN */
    .has-error input[type="text"],
    .has-error #tipo_unidad_medida,
    .has-error #presentacion,
    .has-error #via_aplicacion,
    .has-error #almacenamiento,
    .input-error {
      border: 2px solid crimson !important;
      box-shadow: 0 0 5px crimson;
    }

    #display_sintomas_seleccionados.input-error {
      border: 2px solid crimson !important;
      box-shadow: 0 0 5px crimson;
    }

    /* Modales por encima */
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
</head>

<body>
  <div class="content-wrapper">
    <section class="content-header">
      <h1>
        Editar Medicamento
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-archive"></i>Medicamento</a></li>
        <li class="active"><a href="#"><i class="fa fa-plus-circle"></i>Editar</a></li>
      </ol>
    </section>

    <section class="content">

      <div class="row">
        <div class="col-md-12">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#tab_1" data-toggle="tab">Detalles de La Operacion</a></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane active" id="tab_1">
                <div class="box-body">
                  <form id="formularioMedicamento" style="margin-bottom:6%;" method="POST" action="../../cfg/editar/editar_medicamento.php" novalidate>
                    <?php
                    // Incluye la conexión y obtiene los datos del medicamento
                    include("../../cfg/conexion.php");

                    // Validación básica para Id_descripcion
                    $id_descripcion_url = isset($_GET['Id']) ? (int)$_GET['Id'] : 0;

                    // Consulta principal con JOIN para obtener UN solo dato usando el ID ÚNICO de la descripción
                    $sql = "SELECT 
                      m.Id_medicamento, 
                      m.nombre_medicamento, 
                      dm.Id,      /* <--- Seleccionamos el ID único de la descripción */
                      dm.Id_presentacion, 
                      dm.Id_unidad, 
                      dm.cantidad_unidad_medida,
                      dm.via_aplicacion,
                      dm.almacenamiento,
                      dm.composicion
                      FROM 
                      medicamento m
                      JOIN 
                      descripcion_medicamento dm ON m.Id_medicamento = dm.Id_medicamento
                      JOIN 
                      unidad_medida um ON dm.Id_unidad = um.Id_unidad_medida
                      WHERE dm.Id = " . $id_descripcion_url;

                    $resultado = $conexion->query($sql);
                    $row = $resultado->fetch_assoc();

                    // Si no se encuentra el registro, se podría redirigir o mostrar un error
                    if (!$row) {
                      die("Error: No se encontró el medicamento con esa descripción.");
                    }

                    // Definición de variables para preselección
                    $presentacion_db_id = $row['Id_presentacion'];
                    $unidad_medida_db_id = $row['Id_unidad'];
                    $via_db = $row['via_aplicacion'];
                    $almacenamiento_db = $row['almacenamiento'];
                    ?>

                    <input type="hidden" name="Id" value="<?= $row['Id']; ?>">
                    <input type="hidden" name="Id_medicamento" value="<?= $row['Id_medicamento']; ?>">
                    <label class="control-label"></label>
                    <div class="col-sm-4 form-group" id="group_nombre">
                      <p>Nombre (*):</p>
                      <input id="medicamento" name="medicamento" class="form-control" type="text" maxlength="100" required value="<?php echo $row['nombre_medicamento']; ?>">
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-4 form-group" id="group_presentacion">
                      <p>Presentacion:</p>
                      <select class="form-control" name="presentacion" id="presentacion" required>
                        <option value="">Seleccione Una Presentacion</option>
                        <?php
                        // Consulta para cargar las presentaciones
                        $sql_presentacion = $conexion->query("SELECT * FROM presentacion");
                        while ($resultado_pres = $sql_presentacion->fetch_assoc()) {
                          // Seleccionar la opción si su Id_presentacion coincide con el valor guardado
                          $selected = ($resultado_pres['Id_presentacion'] == $presentacion_db_id) ? 'selected' : '';
                          echo "<option value='" . $resultado_pres['Id_presentacion'] . "' " . $selected . ">" . $resultado_pres['tipo_presentacion'] . "</option>";
                        }
                        ?>
                      </select>
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-2 form-group" id="group_unidad">
                      <p>U. Medida (*):</p>
                      <input type="text" class="form-control" value="<?php echo $row['cantidad_unidad_medida']; ?>" name="cantidad_unidad_medida" id="u_medida" placeholder="800" inputmode="numeric" required>
                    </div>
                    <div class="col-lg-1 pull-left form-group" id="group_tipo_unidad" style="margin-top: 30px; margin-left:-20px;">
                      <select class="form-control" name="tipo_medida" id="tipo_unidad_medida" required>
                        <option value disabled="">---</option>
                      </select>
                    </div>
                    <br><br><br><br>
                    <label class="control-label"></label>
                    <div class="col-sm-4 form-group" id="group_via">
                      <p>Via de aplicacion (*):</p>
                      <select name="via" id="via_aplicacion" class="form-control">
                        <option value="">Selecione una Via de Aplicacion</option>
                        <option value="Oral" <?php if ($via_db == 'Oral') echo 'selected'; ?>>Oral</option>
                        <option value="Sublingual" <?php if ($via_db == 'Sublingual') echo 'selected'; ?>>Sublingual</option>
                        <option value="Rectal" <?php if ($via_db == 'Rectal') echo 'selected'; ?>>Rectal</option>
                        <option value="Intravenosa" <?php if ($via_db == 'Intravenosa') echo 'selected'; ?>>Intravenosa</option>
                        <option value="Intramuscular" <?php if ($via_db == 'Intramuscular') echo 'selected'; ?>>Intramuscular</option>
                        <option value="Subcutanea" <?php if ($via_db == 'Subcutanea') echo 'selected'; ?>>Subcutanea</option>
                        <option value="Intradermica" <?php if ($via_db == 'Intradermica') echo 'selected'; ?>>Intradermica</option>
                        <option value="Topica" <?php if ($via_db == 'Topica') echo 'selected'; ?>>Topica</option>
                        <option value="Transdermica" <?php if ($via_db == 'Transdermica') echo 'selected'; ?>>Transdermica</option>
                        <option value="Inhalatoria" <?php if ($via_db == 'Inhalatoria') echo 'selected'; ?>>Inhalatoria</option>
                        <option value="Oftalmica" <?php if ($via_db == 'Oftalmica') echo 'selected'; ?>>Oftalmica</option>
                        <option value="Otica" <?php if ($via_db == 'Otica') echo 'selected'; ?>>Otica</option>
                        <option value="Nasal" <?php if ($via_db == 'Nasal') echo 'selected'; ?>>Nasal</option>
                        <option value="Vaginal" <?php if ($via_db == 'Vaginal') echo 'selected'; ?>>Vaginal</option>
                      </select>
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-4 form-group" id="group_almacenamiento">
                      <p>C. de almacenamiento (*):</p>
                      <select name="almacenamiento" id="almacenamiento" class="form-control" required>
                        <option value="">Seleccione una Condicion</option>
                        <option value="-25_a_-10" <?php if ($almacenamiento_db == '-25_a_-10') echo 'selected'; ?>>Congelacion (-25*C a -10*C)</option>
                        <option value="2_a_8" <?php if ($almacenamiento_db == '2_a_8') echo 'selected'; ?>>Refrigeracion (2*C a 8*C)</option>
                        <option value="8_a_15" <?php if ($almacenamiento_db == '8_a_15') echo 'selected'; ?>>Lugar Fresco (8*C a 15*C)</option>
                        <option value="15_a_25" <?php if ($almacenamiento_db == '15_a_25') echo 'selected'; ?>>Temperatura Ambiente (15*C a 25*C)</option>
                        <option value="max_30" <?php if ($almacenamiento_db == 'max_30') echo 'selected'; ?>>Temperatura Maxima (30*C)</option>
                      </select>
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-3">
                      <p>Composicion:</p>
                      <input type="text" id="composicion" value="<?php echo $row['composicion']; ?>" name="composicion" class="form-control">
                    </div>
                    <br><br><br><br>
                    <div style="float:right; margin-top: 2%;">
                      <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#modalRegresar">Regresar</button>
                      <button type="submit" class="btn btn-success" id="btnGuardar">Guardar</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <div class="modal" id="avisoModal" tabindex="-1" role="dialog">
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

  <div class="modal fade" id="modalRegresar" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson" style="color: white;">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title">Confirmación de Regreso</h4>
        </div>
        <div class="modal-body">
          <p>Al hacer clic en "Abandonar Formulario", perderá todos los datos no guardados. ¿Desea continuar?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <a href="farmacia_medicamentos_listado.php" class="btn btn-danger">Abandonar Formulario</a>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalGuardar" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: #00a65a; color: white;">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title">Confirmación de Guardado</h4>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea actualizar la informacion del medicamento?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-success" id="confirmarGuardar">Actualizar</button>
        </div>
      </div>
    </div>
  </div>

  <?php
  include('includes/footer.php');
  ?>

  <script>
    $(document).ready(function() {

      const medicamentoInput = document.getElementById('medicamento');
      const uMedidaInput = document.getElementById('u_medida');
      const selectPresentacion = document.getElementById('presentacion');
      const selectUnidad = document.getElementById('tipo_unidad_medida');

      // Capturamos el ID que viene de la base de datos desde PHP
      const unidadGuardada = "<?php echo $unidad_medida_db_id; ?>";

      // =====================================================================
      // FUNCIONES DE VISUALIZACIÓN
      // =====================================================================

      function mostrarAviso(mensaje) {
        $('#avisoTexto').html(mensaje);
        $('#avisoModal').modal('show');
      }

      function limpiarErrores() {
        $('input, select').removeClass('input-error');
        $('.form-group').removeClass('has-error');
      }

      // Función que bloquea números (solo texto)
      function bloquearNumeros(e) {
        const teclasPermitidas = ["Backspace", "Tab", "ArrowLeft", "ArrowRight", "Delete", "Shift"];
        if (teclasPermitidas.includes(e.key)) return;
        if (e.key >= "0" && e.key <= "9") {
          e.preventDefault();
        }
      }
      // Función que limpia números pegados (solo texto)
      function limpiarNumeros(e) {
        e.target.value = e.target.value.replace(/[0-9]/g, "");
      }

      function cargarUnidades(idPresentacion, valorASeleccionar = null) {
        if (!idPresentacion) {
          selectUnidad.innerHTML = '<option value="">---</option>';
          return;
        }

        fetch('../../cfg/ajax/obtener_unidades_medicamentos.php?id=' + idPresentacion)
          .then(response => response.text())
          .then(data => {
            // Insertamos las opciones filtradas que vienen del PHP
            selectUnidad.innerHTML = data;

            // Si tenemos un valor guardado (al cargar la página), lo seleccionamos
            if (valorASeleccionar) {
              selectUnidad.value = valorASeleccionar;
            }

            console.log("Unidades cargadas para presentación: " + idPresentacion);
          })
          .catch(error => {
            console.error('Error en AJAX:', error);
            selectUnidad.innerHTML = '<option value="">Error</option>';
          });
      }

      // 1. Ejecución inmediata al cargar la página para EDICIÓN
      if (selectPresentacion.value !== "") {
        cargarUnidades(selectPresentacion.value, unidadGuardada);
      }

      // 2. Ejecución cuando el usuario CAMBIA la presentación manualmente
      selectPresentacion.addEventListener('change', function() {
        cargarUnidades(this.value); // Aquí no pasamos el segundo parámetro para que quede en blanco
      });

      // --- VALIDACIONES EXISTENTES ---
      medicamentoInput.addEventListener('input', function() {
        let valor = this.value;
        valor = valor.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
        if (valor.length > 50) valor = valor.substring(0, 50);
        this.value = valor;
      });

      const numericInputs = [uMedidaInput];
      numericInputs.forEach(input => {
        input.addEventListener('input', function() {
          let valor = this.value;
          valor = valor.replace(/[^0-9.]/g, '');
          const partes = valor.split('.');
          if (partes.length > 2) valor = partes[0] + '.' + partes.slice(1).join('');
          this.value = valor;
        });
      });

      // =====================================================================
      // LÓGICA DE VERIFICACIÓN AJAX (CONEXIÓN A BD REAL)
      // =====================================================================

      // 3. ENVÍO DEL FORMULARIO
      $('#formularioMedicamento').on('submit', function(e) {
        e.preventDefault();
        limpiarErrores();
        let errores = [];

        if ($('#medicamento').val().trim() === "") {
          errores.push("Falta el nombre del medicamento.");
          $('#group_nombre').addClass('has-error');
        }

        if ($('#presentacion').val().trim() === "") {
          errores.push("Falta el tipo de presentacion.");
          $('#group_presentacion').addClass('has-error');
        }

        if ($('#u_medida').val().trim() === "") {
          errores.push("Falta la cantidad del medicamento.");
          $('#group_unidad').addClass('has-error');
        }

        if ($('#tipo_unidad_medida').val().trim() === "") {
          errores.push("Falta el tipo de unidad de medida.");
          $('#group_tipo_unidad').addClass('has-error');
        }

        if ($('#via_aplicacion').val().trim() === "") {
          errores.push("Falta el tipo de aplicacion.");
          $('#group_via').addClass('has-error');
        }

        if ($('#almacenamiento').val().trim() === "") {
          errores.push("Falta el tipo de almacenamiento.");
          $('#group_almacenamiento').addClass('has-error');
        }

        if (errores.length > 0) {
          mostrarAviso('⚠️ Errores: <ul><li>' + errores.join('</li><li>') + '</li></ul>');
        } else {
          $('#modalGuardar').modal('show');
        }
      });

      $('#confirmarGuardar').on('click', function() {
        $('#modalGuardar').modal('hide');

        $('#formularioMedicamento').off('submit').submit();
      });

      // --- Aplicar validaciones a campos de solo texto ---
      const campos = [document.getElementById("medicamento")];
      campos.forEach(campo => {
        if (campo) {
          campo.addEventListener("keydown", bloquearNumeros);
          campo.addEventListener("input", limpiarNumeros);
        }
      });

      // FIX DE MODALES (Cierre suave)
      $('.modal').on('click', '[data-dismiss="modal"]', function(e) {
        e.stopPropagation();
        var $modal = $(this).closest('.modal');
        if ($modal.hasClass('in')) {
          $modal.removeClass('in').addClass('out');
          setTimeout(function() {
            $modal.modal('hide');
            $modal.removeClass('out');
          }, 400);
        } else {
          $modal.modal('hide');
        }
      });

      $('.modal').on('hidden.bs.modal', function() {
        if (!$('.modal.in').length) {
          $('body').removeClass('modal-open');
          $('.modal-backdrop').remove();
        } else {
          $('body').addClass('modal-open');
        }
      });
    });
  </script>
</body>

</html>