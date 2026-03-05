<?php
// Incluir la conexión a la base de datos (se asume esta ruta)
include("../../cfg/conexion.php");
$operacion_actual = isset($_GET['op']) ? $_GET['op'] : 'entrada';
?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Inventario | Ajuste de Stock</title>
  <?php
  include('includes/headerNav2.php');
  ?>
  <style>
    /* ---------------------------------------------------------------------- */
    /* ANIMACIONES Y ESTILOS DE MODALES (Solicitados) */
    /* ---------------------------------------------------------------------- */

    /* Animación para el fondo al abrir el modal */
    @keyframes pulse-opacity {
      0% {
        opacity: 0;
      }

      100% {
        opacity: 1;
      }
    }

    /* Animación para el modal de Bootstrap (reemplaza la clase 'fade') */
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

    /* Aplica la animación al modal que se está mostrando */
    .modal.in .modal-dialog, #avisoModal, #modalAjusteGuardar, #modalRegresarInventario {
      animation: fadeIn 0.4s ease-out;
    }

    /* Aplica la animación de salida cuando el modal tiene la clase de cierre */
    .modal.out .modal-dialog {
      animation: fadeOut 0.4s ease-in;
    }

    /* Estilo para el body cuando un modal está abierto (fondo animado) */
    .modal-open .modal-backdrop {
      opacity: 0.7 !important;
      animation: pulse-opacity 0.3s forwards;
      /* Aplica la animación al backdrop */
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
      /* Asegura que se muestre para la animación */
    }
  </style>
</head>

<body>
  <div class="content-wrapper">
    <section class="content-header">
      <h1>
        Ajuste de Stock
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-users"></i>Inventario</a></li>
        <li class="active"><a href="#"><i class="fa fa-user-plus"></i>Ajuste</a></li>
      </ol>
    </section>

    <section class="content">

      <div class="row">
        <div class="col-md-12">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#tab_1" data-toggle="tab">Detalle de La Operacion</a></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane active" id="tab_1">
                <div class="box-body">
                  <form id="formularioAjuste" style="margin-bottom:2%;" method="POST" action="../../cfg/movimientos_inventario.php" novalidate autocomplete="off">
                    <input type="hidden" name="op" id="op" value="<?php echo $operacion_actual; ?>">
                    <div class="col-sm-4">
                      <label>Medicamento (*):</label>
                      <select id="Id_descripcion_medicamento" name="Id_descripcion_medicamento" class="form-control" required>
                        <option value="">--- Seleccione un Medicamento ---</option>
                        <?php
                        // 2. Cargar Medicamentos
                        $sql_medicamentos = "SELECT 
                          dm.Id AS id_desc, 
                          m.nombre_medicamento, 
                          dm.cantidad_unidad_medida, 
                          dm.via_aplicacion,
                          dm.Id_presentacion
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

                    <div class="col-sm-4">
                      <label>Stock minimo (*):</label>
                      <input type="text" id="stock_minimo" name="stock_minimo" class="form-control" required>
                    </div>

                    <div class="col-sm-4">
                      <label>Stock maximo (*):</label>
                      <input type="text" id="stock_maximo" name="stock_maximo" class="form-control" required>
                    </div>

                    <div class="col-sm-1">
                    </div>

                    <br><br><br><br>

                    <div class="col-sm-4">
                      <label>Existencia (Actual):</label>
                      <input type="text" id="existencia_actual" class="form-control" readonly disabled>
                    </div>

                    <div class="col-sm-1">
                    </div>

                    <br><br><br><br>

                    <div class="col-sm-4">
                    </div>

                    <div style="float:right; margin-top: -1%;">
                      <button type="button" class="btn btn-secondary" id="abrirModalRegresar">Regresar</button>
                      <button type="submit" class="btn btn-success" id="guardarAjuste">Guardar</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
        <?php
        include('includes/footer.php');
        ?>
      </div>
    </section>
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

  <div class="modal" id="modalAjusteGuardar" tabindex="-1" role="dialog" aria-labelledby="modalAjusteGuardarLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: #00a65a; color: white;">
          <h5 class="modal-title" id="modalAjusteGuardarLabel" style="color: white;">Confirmacion de Guardado</h5>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea guardar la información para esta entrada del inventario?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-success" id="confirmarGuardadoFinal">Guardar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalRegresarInventario" tabindex="-1" role="dialog" aria-labelledby="modalRegresarInventarioLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h5 class="modal-title" id="modalRegresarInventarioLabel" style="color: white;">Confirmacion de Regreso</h5>
        </div>
        <div class="modal-body">
          <p>Al hacer clic en "Abandonar Formulario", perderá todos los datos no guardados. ¿Desea continuar?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <a href="farmacia_inventario_listado.php" class="btn btn-danger">Abandonar Formulario</a>
        </div>
      </div>
    </div>
  </div>

  <?php
  // Cierre de la conexión a la base de datos
  if (isset($conexion)) {
    $conexion->close();
  }
  include('includes/footer.php');
  ?>

  <script>
    $(document).ready(function() {
      function mostrarAviso(mensaje) {
        clearTimeout($('#avisoModal').data('timer'));
        $('#avisoTexto').html(mensaje);
        $('#avisoModal').modal('show');
      }

      // Limpia el estado de error de todos los inputs
      function limpiarErrores() {
        $('input, select').removeClass('input-error');
      }

      // Función para abrir el modal de Guardar
      function abrirModalGuardar() {
        clearTimeout($('#modalAjusteGuardar').data('timer'));
        $('#modalAjusteGuardar').modal('show');
      }

      // Escuchar el cambio en el select de descripción de medicamento
      $('#Id_descripcion_medicamento').on('change', function() {
        var id_desc = $(this).val();
        var operacion = "<?php echo $_GET['op'] ?? 'entrada'; ?>"; // Detectamos si es entrada, salida o ajuste

        if (id_desc !== "") {
          $.ajax({
            // Asegúrate de que esta ruta sea correcta según tu estructura de carpetas
            url: '../../cfg/ajax/obtener_descripcion_medicamento.php',
            type: 'POST',
            data: {
              id: id_desc
            },
            dataType: 'json',
            success: function(data) {
              if (data.error) {
                alert(data.error);
              } else {
                // 1. Cargamos la existencia actual (informativo)
                $('#existencia_actual').val(data.existencia_actual);

                // 2. Cargamos los parámetros de stock
                $('#stock_minimo').val(data.stock_minimo);
                $('#stock_maximo').val(data.stock_maximo);

                // 3. Lógica visual según la operación
                if (operacion === 'ajuste') {
                  // Si es ajuste, resaltamos los campos de stock para edición
                  $('#stock_minimo, #stock_maximo').css('border', '2px solid green'); // Ocultamos lote/vencimiento si solo es ajuste
                } else {
                  // Si es entrada/salida, comparamos para mostrar alertas
                 
                }
              }
            },
            error: function() {
              console.error("Error al conectar con obtener_descripcion_medicamento.php");
            }
          });
        }
      });

      $('#formularioAjuste').on('submit', function(e) {
        e.preventDefault();
        limpiarErrores();
        
        var formularioValido = true;

        var stock_minimo = Number($('#stock_minimo').val());
        var stockMin = $('#stock_minimo').val();
        var stock_maximo = Number($('#stock_maximo').val());
        var existencia = Number($('#existencia_actual').val());
        
        // 1.1. Verificación de campos obligatorios vacíos
        $('input[required], select[required]').each(function() {
          var $input = $(this);

          if (($input.is('select') && ($input.val() === null || $input.val() === "")) ||
            (!$input.is('select') && $input.val().trim() === "")) {
            $input.addClass('input-error');
            formularioValido = false;
          }
        });

        if (stockMin.trim() === "") {
          $('#stock_minimo').addClass('input-error');
          mostrarAviso('⚠️ Error: El stock minimo del medicamento no puede estar vacío.');
          return;
        }

        if (stockMin.trim() === "") {
          $('#stock_maximo').addClass('input-error');
          mostrarAviso('⚠️ Error: El stock maximo del medicamento no puede estar vacío.');
          return;
        }

        if (stock_minimo >= stock_maximo) {
          $('#stock_minimo').addClass('input-error');
          mostrarAviso('⚠️ Error: Disculpe el stock minimo no puede ser mayor o igual que el stock maximo.');
          return;
        }

        /*if (stock_minimo > existencia ) {
          $('#stock_minimo').addClass('input-error');
          mostrarAviso('⚠️ Error: Disculpe el stock minimo no puede ser mayor que la existencia en este momento, por favor disminuya o aumente la existencia.');
          return;
        }*/

        if (!formularioValido) {
          mostrarAviso('⚠️ Error: Todos los campos obligatorios (*) deben estar llenos.');
          return;
        }

        // 1.3. Si todo es válido, abrimos el modal de confirmación
        abrirModalGuardar();
      });

      // 1.4. Lógica para el botón 'Guardar' dentro del modal de confirmación
      $('#confirmarGuardadoFinal').on('click', function() {
        $('#modalAjusteGuardar').modal('hide');
        $('#formularioAjuste').off('submit').submit();
      });

      // 1.5. Lógica para el botón Regresar (Abre el modal)
      $('#abrirModalRegresar').on('click', function() {
        $('#modalRegresarInventario').modal('show');
      });

      // =====================================================================
      // FIX CLAVE: CERRAR MODALES CON data-dismiss (Para la animación de salida)
      // =====================================================================
      $('.modal').on('click', '[data-dismiss="modal"]', function() {
        var $modal = $(this).closest('.modal');
        $modal.removeClass('in').addClass('out');

        setTimeout(function() {
          $modal.modal('hide');
          $modal.removeClass('out');
        }, 400);
      });

      // =====================================================================
      // LIMPIEZA ADICIONAL PARA MODALES 
      // =====================================================================
      $('.modal').on('hidden.bs.modal', function() {
        if ($('.modal:visible').length) {
          $('body').addClass('modal-open');
        } else {
          $('body').removeClass('modal-open');
        }
        $('.modal-backdrop').remove();
      });
    });
  </script>
  <script>
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

    soloNumerosSinE(document.getElementById("stock_minimo"), 8);
    soloNumerosSinE(document.getElementById("stock_maximo"), 7);
  </script>

</body>

</html>