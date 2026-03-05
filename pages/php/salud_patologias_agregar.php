<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Patologías | Añadir</title>
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
    .has-error #enfermedad_contagiosa,
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
      <h1>Añadir Patología</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-users"></i>Patologías</a></li>
        <li class="active"><a href="#"><i class="fa fa-user-plus"></i>Añadir</a></li>
      </ol>
    </section>
    <?php
    include('../../cfg/conexion.php');
    $opciones_sintomas = ""; // Variable para guardar los options

    if ($conexion) {
      // Consulta para obtener todos los síntomas ordenados alfabéticamente
      // Asumo que la tabla es 'sintomas' y los campos 'Id_sintomas' y 'nombre_sintoma'
      $sql = "SELECT Id_sintomas, nombre_sintoma FROM sintomas ORDER BY nombre_sintoma ASC";
      $resultado = $conexion->query($sql);

      if ($resultado->num_rows > 0) {
        while ($row = $resultado->fetch_assoc()) {
          // Creamos las opciones HTML
          $id = $row['Id_sintomas'];
          $nombre = htmlspecialchars($row['nombre_sintoma']); // Sanitizar texto
          $opciones_sintomas .= "<option value='$id' data-nombre='$nombre'>$nombre</option>";
        }
      } else {
        $opciones_sintomas = "<option value=''>No hay síntomas registrados</option>";
      }
    } else {
      $opciones_sintomas = "<option value=''>Error de conexión a BD</option>";
    }
    ?>
    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#tab_1" data-toggle="tab">Informacion de la Patología</a></li>
            </ul>
            <div class="tab-content" style="padding-bottom:5%;">
              <div class="tab-pane active" id="tab_1">
                <div class="box-body">
                  <form action="../../cfg/agregar/agregar_patologia.php" id="formularioPatologia" class="form-group" method="POST" novalidate>
                    <input type="hidden" name="sintomas_ids" id="sintomas_ids" value="">

                    <div class="col-sm-4 form-group" id="group_nombre">
                      <p>Nombre de la patología (*):</p>
                      <input type="text" class="form-control" name="nombre_patologia" id="nombre_patologia" required>
                    </div>
                    <div class="col-sm-4 form-group" id="group_codigo">
                      <p>Código CIE-10 (*):</p>
                      <input type="text" class="form-control" name="codigo_cie" id="codigo_cie" placeholder="Ej: A00.0" maxlength="8" required>
                    </div>
                    <div class="col-sm-3 form-group" id="group_contagiosa">
                      <p>Enfermedad contagiosa (*):</p>
                      <select name="enfermedad_contagiosa" id="enfermedad_contagiosa" class="form-control" required>
                        <option value="">--- Seleccione una respuesta ---</option>
                        <option value="SI">Sí</option>
                        <option value="NO">No</option>
                      </select>
                    </div>

                    <br><br><br><br>

                    <div class="col-sm-12">
                      <p>Síntomas asociados (*):</p>
                      <div id="display_sintomas_seleccionados" class="well well-sm" style="min-height: 50px; background-color: #f9f9f9; padding: 10px;">
                        Ningún síntoma seleccionado.
                      </div>
                      <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modalSintomas">
                        <i class="fa fa-list"></i> Seleccionar Síntomas
                      </button>
                      <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalNuevoSintoma">
                        <i class="fa fa-plus"></i> Agregar Nuevo Síntoma
                      </button>
                    </div>

                    <br><br><br><br><br><br>

                    <div style="float:right; margin-top: 0%;">
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

    <div class="modal fade" id="modalSintomas" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #31b0d5; color: white;">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Seleccionar Síntomas</h4>
          </div>
          <div class="modal-body">
            <p>Síntomas seleccionados actualmente:</p>
            <div id="lista_sintomas_seleccionadas_modal" class="well well-sm" style="min-height: 50px;"></div>
            <br>
            <p>Síntomas disponibles:</p>
            <select id="lista_todos_sintomas" class="form-control" style="max-height: 200px; overflow-y: auto;">
              <option value="">--- Seleccione un Síntoma ---</option>
              <?php echo $opciones_sintomas; ?>
            </select>
            <button type="button" class="btn btn-sm btn-info pull-right" id="añadirSintomasSeleccionados" style="margin-top: 10px;">
              <i class="fa fa-plus-circle"></i> Añadir
            </button>
            <br><br>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-success" id="guardarSeleccionSintomas">Guardar Selección</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="modalNuevoSintoma" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header bg-primary" style="color: white;">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Añadir Nuevo Síntoma</h4>
          </div>
          <div class="modal-body">
            <form id="formNuevoSintoma">
              <div class="form-group">
                <label for="nuevo_nombre_sintoma">Nombre del Síntoma (*)</label>
                <input type="text" class="form-control" id="nuevo_nombre_sintoma" name="nombre_sintoma" required>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-success" id="btnGuardarNuevoSintoma">Guardar</button>
          </div>
        </div>
      </div>
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
          <p>Al hacer clic en "Abandonar Formulario", perderá todos los datos no guardados. ¿Desea continuar?</p>          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <a href="salud_patologias_listado.php" class="btn btn-danger">Abandonar Formulario</a>
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
            <p>¿Está seguro de que desea guardar la nueva Patología?</p>
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
        let sintomasSeleccionados = [];

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
          $('#display_sintomas_seleccionados').removeClass('input-error');
        }

        // --- VISUALIZACIÓN UNIFICADA EN DISPLAY PRINCIPAL ---
        function actualizarDisplaySintomas() {
          const display = $('#display_sintomas_seleccionados');
          const inputHidden = $('#sintomas_ids');
          const nombres = sintomasSeleccionados.map(s => s.nombre);
          const ids = sintomasSeleccionados.map(s => s.id);
          const LIMITE_DISPLAY = 3;

          inputHidden.val(ids.join(','));

          if (nombres.length > 0) {
            let htmlContent = '';

            for (let i = 0; i < Math.min(nombres.length, LIMITE_DISPLAY); i++) {
              htmlContent += `<span class="text" style="margin-right: 5px; margin-bottom: 5px; display: inline-block; padding: 6px;">${nombres[i]},</span>`;
            }

            if (nombres.length > LIMITE_DISPLAY) {
              let restantes = nombres.length - LIMITE_DISPLAY;
              htmlContent += `<span class="text-muted" style="margin-left:5px; font-weight:bold;">... y ${restantes} más.</span>`;
            }

            display.html(htmlContent);

          } else {
            display.html('Ningún síntoma seleccionado.');
          }
        }

        // --- RENDERIZADO EN EL MODAL (CON BOTÓN DE ELIMINAR 'X') ---
        function renderizarSeleccionadosModal() {
          $('#lista_sintomas_seleccionadas_modal').empty();
          sintomasSeleccionados.forEach(sintoma => {
            $('#lista_sintomas_seleccionadas_modal').append(
              // El icono ahora va pegado al nombre, y el padding del span se ajusta para contener ambos.
              `<span class="label label-primary" style="margin-right: 5px; margin-bottom: 5px; display: inline-block; font-size: 14px; padding: 6px 10px 6px 10px;">
                ${sintoma.nombre} 
                <i class="quitar-sintoma" data-id="${sintoma.id}" 
                   style="cursor: pointer; margin-left: 8px; color: white:#dc3545;" 
                   title="Eliminar">x</i>
            </span>`
            );
          });
        }

        // =====================================================================
        // LÓGICA DE VERIFICACIÓN AJAX (CONEXIÓN A BD REAL)
        // =====================================================================
        function verificarPatologiaYEnviar() {
          const nombre = $('#nombre_patologia').val().trim();
          const codigo = $('#codigo_cie').val().trim();
          const btnGuardar = $('#confirmarGuardar');

          // Estado de carga
          const textoOriginal = btnGuardar.text();
          btnGuardar.text('Verificando...').attr('disabled', true);

          // LLAMADA AJAX AL SCRIPT DE VERIFICACIÓN
          // Asegúrate de haber creado el archivo 'verificar_existencia_patologia.php' en la carpeta 'cfg'
          $.ajax({
            url: 'get/verificar_existencia_patologia.php',
            method: 'POST',
            dataType: 'json',
            data: {
              nombre: nombre,
              codigo: codigo
            },
            success: function(response) {
              let errores_ajax = [];
              limpiarErrores();
              btnGuardar.text(textoOriginal).attr('disabled', false);

              if (response.existe_nombre) {
                errores_ajax.push(`⚠️ Ya existe una patología con el nombre: ${nombre}`);
                $('#group_nombre').addClass('has-error');
                $('#nombre_patologia').addClass('input-error');
              }
              if (response.existe_codigo) {
                errores_ajax.push(`⚠️ Ya existe una patología con el Código CIE-10: ${codigo}`);
                $('#group_codigo').addClass('has-error');
                $('#codigo_cie').addClass('input-error');
              }

              if (errores_ajax.length > 0) {
                mostrarAviso('🛑 Error de Duplicidad:' + '<ul><li>' + errores_ajax.join('</li><li>') + '</li></ul>');
              } else {
                // Si no hay errores, ENVIAR FORMULARIO
                $('#formularioPatologia').off('submit').submit();
              }
            },
            error: function(xhr, status, error) {
              btnGuardar.text(textoOriginal).attr('disabled', false);
              // Fallback visual en caso de error de red (opcional) o mostrar alerta
              mostrarAviso('🛑 Error de Servidor: No se pudo verificar la base de datos. <br>Detalle: ' + error);
            }
          });
        }

        // =====================================================================
        // MANEJADORES DE EVENTOS
        // =====================================================================

        // 1. GESTIÓN DE SÍNTOMAS (Modal Seleccionar/Eliminar)
        $('#modalSintomas').on('show.bs.modal', function() {
          $('#lista_todos_sintomas').val("");
          renderizarSeleccionadosModal();
        });

        $('#añadirSintomasSeleccionados').on('click', function() {
          const select = $('#lista_todos_sintomas');
          const selectedOption = select.find('option:selected');
          const id = selectedOption.val();
          const nombre = selectedOption.data('nombre');

          if (!id) {
            mostrarAviso('⚠️ Seleccione un síntoma de la lista.');
            return;
          }
          // Se asegura de que no se añada dos veces
          if (sintomasSeleccionados.some(s => s.id == id)) {
            mostrarAviso('⚠️ Este síntoma ya está añadido.');
            return;
          }

          sintomasSeleccionados.push({
            id: parseInt(id),
            nombre: nombre
          });
          renderizarSeleccionadosModal();
          select.val("");
        });

        // Evento Delegado para ELIMINAR SÍNTOMA (La "X") de la lista TEMPORAL
        $('#lista_sintomas_seleccionadas_modal').on('click', '.quitar-sintoma', function() {
          const idQuitar = $(this).data('id');
          sintomasSeleccionados = sintomasSeleccionados.filter(s => s.id != idQuitar);
          $(this).closest('.label').remove();
          mostrarAviso(`Síntoma eliminado de la lista temporal. Recuerde guardar los cambios.`);
        });

        $('#guardarSeleccionSintomas').on('click', function() {
          actualizarDisplaySintomas();
          if (sintomasSeleccionados.length > 0) $('#display_sintomas_seleccionados').removeClass('input-error');
          $('#modalSintomas').modal('hide');
        });


        // 2. NUEVO SÍNTOMA RÁPIDO (Guardar en BD)
        $('#btnGuardarNuevoSintoma').on('click', function() {
          const nombreSintoma = $('#nuevo_nombre_sintoma').val().trim();
          const btn = $(this);

          if (nombreSintoma === "") {
            mostrarAviso('🛑 El nombre es obligatorio.');
            return;
          }

          const textoOriginal = btn.text();
          btn.text('Guardando...').attr('disabled', true);

          // LLAMADA AJAX para guardar en la BD y obtener el ID real
          $.ajax({
            url: '../../cfg/agregar_sintoma.php',
            method: 'POST',
            dataType: 'json',
            data: {
              nombre_sintoma: nombreSintoma
            },
            success: function(response) {
              btn.text(textoOriginal).attr('disabled', false);

              if (response.success && response.id_sintoma) {
                const nuevoId = parseInt(response.id_sintoma);

                // SOLUCIÓN 2: Solo agregamos a la lista de opciones (Select)
                // NO se agrega al array sintomasSeleccionados ni se llama a actualizarDisplaySintomas()
                $('#lista_todos_sintomas').append(`<option value='${nuevoId}' data-nombre='${nombreSintoma}'>${nombreSintoma}</option>`);

                // Cerrar y limpiar
                $('#modalNuevoSintoma').modal('hide');
                $('#formNuevoSintoma')[0].reset();
                mostrarAviso(`✅ Síntoma ${nombreSintoma} añadido a la base de datos y disponible para ser seleccionado.`);

              } else {
                const mensaje = response.message || 'Error desconocido al guardar el síntoma.';
                mostrarAviso(`🛑 Error de guardado: ${mensaje}`);
              }
            },
            error: function() {
              btn.text(textoOriginal).attr('disabled', false);
              mostrarAviso('🛑 Error de Conexión: No se pudo contactar al servidor para guardar el síntoma.');
            }
          });
        });

        // 3. ENVÍO DEL FORMULARIO
        $('#formularioPatologia').on('submit', function(e) {
          e.preventDefault();
          limpiarErrores();
          let errores = [];

          if ($('#nombre_patologia').val().trim() === "") {
            errores.push("Falta el Nombre de la patología.");
            $('#group_nombre').addClass('has-error');
          }
          if ($('#codigo_cie').val().trim() === "") {
            errores.push("Falta el Código CIE-10.");
            $('#group_codigo').addClass('has-error');
          }
          if ($('#enfermedad_contagiosa').val() === "") {
            errores.push("Seleccione si es contagiosa.");
            $('#group_contagiosa').addClass('has-error');
          }
          if (sintomasSeleccionados.length === 0) {
            errores.push("Debe seleccionar al menos un Síntoma.");
            $('#display_sintomas_seleccionados').addClass('input-error');
          }

          if (errores.length > 0) {
            mostrarAviso('⚠️ Errores:<ul><li>' + errores.join('</li><li>') + '</li></ul>');
          } else {
            $('#modalGuardar').modal('show');
          }
        });

        $('#confirmarGuardar').on('click', function() {
          $('#modalGuardar').modal('hide');
          verificarPatologiaYEnviar(); // Inicia proceso AJAX
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

        actualizarDisplaySintomas();
      });
    </script>
</body>

</html>