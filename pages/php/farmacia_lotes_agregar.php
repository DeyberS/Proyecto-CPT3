<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Lotes | Añadir</title>
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

    .modal.in .modal-dialog,
    #avisoModal,
    #modalGuardar {
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
    .has-error input[type="date"],
    .has-error #medicamento,
    .has-error #proveedor,
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
      <h1>Añadir Lote</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-users"></i>Lotes</a></li>
        <li class="active"><a href="#"><i class="fa fa-user-plus"></i>Añadir</a></li>
      </ol>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#tab_1" data-toggle="tab">Informacion del Lote</a></li>
            </ul>
            <div class="tab-content" style="padding-bottom:11%;">
              <div class="tab-pane active" id="tab_1">
                <div class="box-body">
                  <form action="../../cfg/agregar/agregar_lote.php" id="formularioLote" class="form-group" method="POST" novalidate>

                    <div class="col-sm-4 form-group" id="group_nombre">
                      <p>Nombre del lote (*):</p>
                      <input type="text" class="form-control" name="nombre_lote" id="nombre_lote" required>
                    </div>

                    <div class="col-sm-4 form-group" id="group_fabricacion">
                      <p>F. Fabricacion (*):</p>
                      <input type="date" class="form-control" id="fecha_fabricacion" name="fecha_fabricacion" required>
                    </div>

                    <div class="col-sm-4 form-group" id="group_vencimiento">
                      <p>F. Vencimiento (*):</p>
                      <input type="date" class="form-control" id="fecha_vencimiento" min="<?php echo date('Y-m-d'); ?>" name="fecha_vencimiento" required>
                    </div>

                    <br><br><br><br><br>

                    <div class="col-sm-4">
                      <p>Estado del Lote (*):</p>
                      <select name="estado_lote" id="estado_lote" class="form-control" disabled readonly>
                        <option value="Disponible" selected> Disponible</option>
                      </select>
                    </div>

                    <div class="col-sm-4 form-group" id="group_medicamento">
                      <p>Medicamento (*):</p>
                      <select id="medicamento" name="medicamento" class="form-control" required>
                        <option value="">--- Seleccione un medicamento ---</option>
                        <?php
                        // 2. Cargar Medicamentos
                        include("../../cfg/conexion.php");
                        $sql_medicamentos = "SELECT 
                          dm.Id AS id_desc, 
                          m.nombre_medicamento
                          FROM descripcion_medicamento dm
                          INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
                          WHERE m.estatus = 1 AND dm.estatus = 1
                          ORDER BY m.nombre_medicamento ASC";
                        $resultado_medicamentos = $conexion->query($sql_medicamentos);

                        if ($resultado_medicamentos && $resultado_medicamentos->num_rows > 0) {
                          while ($row_med = $resultado_medicamentos->fetch_assoc()) {
                            // Se usa Id_medicamento como value
                            echo '<option value="' . $row_med['id_desc'] . '">' . htmlspecialchars($row_med['nombre_medicamento']) . '</option>';
                          }
                        }
                        ?>
                      </select>
                    </div>

                    <div class="col-sm-4 form-group" id="group_proveedor">
                      <p>Proveedor (*):</p>
                      <select id="proveedor" name="proveedor" class="form-control" required>
                        <option value="">--- Seleccione un proveedor ---</option>
                        <?php
                        // 2. Cargar Medicamentos
                        include("../../cfg/conexion.php");
                        $sql_proveedor = "SELECT Id_proveedor, nombre_proveedor 
                                      FROM proveedor 
                                      ORDER BY nombre_proveedor ASC";

                        $resultado_proveedor = $conexion->query($sql_proveedor);

                        if ($resultado_proveedor && $resultado_proveedor->num_rows > 0) {
                          while ($row_pro = $resultado_proveedor->fetch_assoc()) {
                            // Se usa Id_proveedor como value para que se guarde correctamente la relación
                            echo '<option value="' . $row_pro['Id_proveedor'] . '">' . htmlspecialchars($row_pro['nombre_proveedor']) . '</option>';
                          }
                        }
                        ?>
                      </select>
                    </div>

                    <br>

                    <div style="float:right; margin-top: 2%;">
                      <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#modalRegresar">Regresar</button>
                      <button type="submit" class="btn btn-success" id="btnGuardar">Guardar</button>
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
            <a href="farmacia_lotes_listado.php" class="btn btn-danger">Abandonar Formulario</a>
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
            <p>¿Está seguro de que desea guardar la informacion del nuevo lote?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-success" id="confirmarGuardar">Guardar</button>
          </div>
        </div>
      </div>
    </div>

    <script>
      $(document).ready(function() {
        const formulario = $('#formularioLote');
        const fechaFabricacionInput = $('#fecha_fabricacion');
        const fechaVencimientoInput = $('#fecha_vencimiento');

        // =====================================================================
        // BLOQUEO DE ESCRITURA EN FECHAS
        // =====================================================================
        $('#fecha_fabricacion, #fecha_vencimiento').on('keydown', function(e) {
          e.preventDefault(); // Evita cualquier ingreso por teclado, forzando usar el calendario
        });

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

        // --- VALIDACIÓN DE FECHAS ---
        const today = new Date().toISOString().split('T')[0];
        fechaFabricacionInput.attr('max', today);

        fechaFabricacionInput.on('change', function() {
          const fabricacionDate = $(this).val();
          if (fabricacionDate) {
            fechaVencimientoInput.prop('disabled', false).attr('min', fabricacionDate);
          } else {
            fechaVencimientoInput.prop('disabled', true).val('').removeAttr('min');
          }
        });

        fechaVencimientoInput.on('change', function() {
          const fabricacionDate = fechaFabricacionInput.val();
          const selectedDate = $(this).val();

          if (fabricacionDate && selectedDate < fabricacionDate) {
            mostrarAviso('La fecha de vencimiento no puede ser anterior a la de fabricación.');
            $(this).val('');
          } else if (selectedDate && selectedDate <= today) {
            mostrarAviso('La fecha de vencimiento debe ser una fecha futura.');
            $(this).val('');
          }
        });

        // =====================================================================
        // LÓGICA DE VERIFICACIÓN AJAX (CONEXIÓN A BD REAL)
        // =====================================================================
        function verificarLoteYMostrarModal() {
          const nombre = $('#nombre_lote').val().trim();
          const id_medicamento = $('#medicamento').val();
          const btnGuardar = $('#btnGuardar'); // El botón del formulario

          const textoOriginal = btnGuardar.text();
          btnGuardar.text('Verificando...').attr('disabled', true);

          $.ajax({
            url: 'get/verificar_existencia_lote.php', // ¡Asegúrate que esta ruta sea correcta!
            method: 'POST',
            dataType: 'json',
            data: {
              nombre: nombre,
              id_medicamento: id_medicamento
            },
            success: function(response) {
              btnGuardar.text(textoOriginal).attr('disabled', false);
              limpiarErrores();

              if (response.existe_lote) {
                $('#group_nombre').addClass('has-error');
                $('#nombre_lote').addClass('input-error');
                mostrarAviso('🛑 Error de Duplicidad:<br>Ya existe un lote con el nombre <b>' + nombre + '</b> asignado a este mismo medicamento.');
              } else if (response.error) {
                mostrarAviso('🛑 Error: ' + response.mensaje);
              } else {
                // SI NO EXISTE ERROR NI DUPLICADO, MOSTRAMOS EL MODAL
                $('#modalGuardar').modal('show');
              }
            },
            error: function(xhr, status, error) {
              btnGuardar.text(textoOriginal).attr('disabled', false);
              mostrarAviso('🛑 Error de Servidor: No se pudo verificar la base de datos. <br>Detalle: ' + error);
            }
          });
        }

        // 3. ENVÍO DEL FORMULARIO (CLICK EN GUARDAR PRINCIPAL)
        formulario.on('submit', function(e) {
          e.preventDefault();
          limpiarErrores();
          let errores = [];

          if ($('#nombre_lote').val().trim() === "") {
            errores.push("El nombre del lote no puede estar vacio.");
            $('#group_nombre').addClass('has-error');
          }
          if ($('#fecha_fabricacion').val().trim() === "") {
            errores.push("La fecha de fabricacion no puede estar vacia.");
            $('#group_fabricacion').addClass('has-error');
          }
          if ($('#fecha_vencimiento').val().trim() === "") {
            errores.push("La fecha de vencimiento no puede estar vacia.");
            $('#group_vencimiento').addClass('has-error');
          }
          if ($('#medicamento').val().trim() === "") {
            errores.push("Debe asignar un medicamento a este lote.");
            $('#group_medicamento').addClass('has-error');
          }
          if ($('#proveedor').val().trim() === "") {
            errores.push("Debe asignar un proveedor a este lote.");
            $('#group_proveedor').addClass('has-error');
          }

          if (errores.length > 0) {
            mostrarAviso('⚠️ Errores locales: <ul><li>' + errores.join('</li><li>') + '</li></ul>');
          } else {
            // Pasó validación local -> Va a BD antes del modal
            verificarLoteYMostrarModal();
          }
        });

        // BOTÓN DEL MODAL: Confirmar el Guardado final
        $('#confirmarGuardar').on('click', function() {
          $('#modalGuardar').modal('hide');
          formulario[0].submit(); // Uso del submit nativo para enviar directamente
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