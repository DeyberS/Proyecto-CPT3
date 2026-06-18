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
            <div class="tab-content" style="padding-bottom:3%;">
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

                    <div class="col-sm-4 form-group" id="group_sintomas">
                      <p>Síntomas asociados (*):</p>
                      <button type="button" class="btn btn-info btn-block" id="btn_modal_sintomas" data-toggle="modal" data-placement="top" title="Ningún síntoma seleccionado" data-target="#modal_sintomas">
                        <i></i> Gestionar Síntomas
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

    <div class="modal fade" id="modal_sintomas" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #3c8dbc; color: white;">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Gestionar Síntomas</h4>
          </div>
          <div class="modal-body">
            <div id="contenedor_filas_sintomas"></div>
            <button type="button" class="btn btn-success btn-sm pull-left" id="add_fila_sintoma">
              <i class="fa fa-plus"></i> Añadir otro
            </button>
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalNuevoSintoma" style="margin-left:5px;">
              <i class="fa fa-star"></i> Nuevo Sintoma
            </button>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" id="guardar_sintomas_listo" data-dismiss="modal">Listo</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="modalNuevoSintoma" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #3c8dbc; color: white;">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Crear Nuevo Síntoma</h4>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label>Nombre del Síntoma</label>
              <input type="text" id="nombre_nuevo_sintoma" class="form-control" placeholder="Ej. Dolor de cabeza">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-success" id="btnGuardarSintomaBD">Guardar y Seleccionar</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal" id="modalBuscarSintoma" role="dialog">
      <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
          <div class="modal-header bg-info" style="background-color: #3c8dbc; color: white;">
            <h4 class="modal-title">Buscar Síntoma</h4>
          </div>
          <div class="modal-body">
            <input type="text" id="inputBuscarSintoma" class="form-control" placeholder="Escriba para filtrar...">
            <div class="list-group" id="listaResultadosSintoma" style="margin-top: 10px; max-height: 200px; overflow-y: auto;"></div>
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
            <p>Al hacer clic en "Abandonar Formulario", perderá todos los datos no guardados. ¿Desea continuar?</p>
          </div>
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

        // =====================================================================
        // MANEJADORES DE EVENTOS
        // =====================================================================

        function agregarFilaSintoma(idSeleccionado = "") {
          let htmlSintoma = `
            <div class="row fila-sintoma" style="margin-bottom: 10px;">
                <div class="col-sm-10">
                    <div class="input-group">
                        <select class="form-control select-sintoma">
                            <option value="">--- Seleccione un síntoma ---</option>
                            <?php echo $opciones_sintomas; ?>
                        </select>
                        <span class="input-group-btn">
                            <button class="btn btn-info btn-search-sintoma" type="button" title="Buscar Síntoma">
                              <i><img src="../../recursos/imagenes/iconos/buscar.png" style="width:10px; height:10px;"></i>
                            </button>
                        </span>
                    </div>
                </div>
                <div class="col-sm-2">
                    <button type="button" class="btn btn-danger btn-remove-sintoma">
                        <i><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></i>
                    </button>
                </div>
            </div>`;

          $('#contenedor_filas_sintomas').append(htmlSintoma);
          if (idSeleccionado) {
            $('#contenedor_filas_sintomas .fila-sintoma:last .select-sintoma').val(idSeleccionado);
          }
        }

        $('#btn_modal_sintomas').tooltip();
        $('#btn_modal_sintomas').click(function() {
          if ($('#contenedor_filas_sintomas').children().length === 0) agregarFilaSintoma();
        });

        $('#add_fila_sintoma').click(() => agregarFilaSintoma());
        $(document).on('click', '.btn-remove-sintoma', function() {
          $(this).closest('.fila-sintoma').remove();
        });

        $('#guardar_sintomas_listo').click(function() {
          let ids = [];
          let nombres = [];
          $('.select-sintoma').each(function() {
            let val = $(this).val();
            if (val && val !== "") {
              ids.push(val);
              nombres.push($(this).find('option:selected').text().trim());
            }
          });
          // Unimos por coma (,) como lo esperaba tu backend originalmente
          $('#sintomas_ids').val(ids.join(','));
          let textoTooltip = ids.length > 0 ? nombres.join(', ') : 'Ningún síntoma seleccionado';
          $('#btn_modal_sintomas').attr('data-original-title', textoTooltip).tooltip('fixTitle');
        });

        // Prevenir duplicados
        $(document).on('change', '.select-sintoma', function() {
          let selectActual = $(this);
          let valorActual = selectActual.val();
          if (valorActual === "") return;

          let conteo = 0;
          $('.select-sintoma').each(function() {
            if ($(this).val() === valorActual) conteo++;
          });
          if (conteo > 1) {
            mostrarAviso("⚠️ <b>Atención:</b> Este síntoma ya ha sido seleccionado. Por favor, elija uno diferente.");
            selectActual.val("");
          }
        });

        // Buscador en Tiempo Real
        let selectDestinoTargetSintoma = null;
        $(document).on('click', '.btn-search-sintoma', function() {
          selectDestinoTargetSintoma = $(this).closest('.input-group').find('.select-sintoma');
          $('#modalBuscarSintoma').modal('show');
          $('#inputBuscarSintoma').val('').trigger('keyup');
        });

        $('#inputBuscarSintoma').on('keyup', function() {
          let texto = $(this).val().toLowerCase();
          let html = '';
          let opciones = $('.select-sintoma:first option').not('[value=""]');
          opciones.each(function() {
            let nombre = $(this).text();
            if (nombre.toLowerCase().includes(texto)) {
              html += `<a href="#" class="list-group-item list-group-item-action seleccionar-sintoma" data-id="${$(this).val()}">${nombre}</a>`;
            }
          });
          $('#listaResultadosSintoma').html(html);
        });

        $(document).on('click', '.seleccionar-sintoma', function(e) {
          e.preventDefault();
          selectDestinoTargetSintoma.val($(this).data('id')).trigger('change');
          $('#modalBuscarSintoma').modal('hide');
        });

        // =====================================================================
        // CREAR NUEVO SÍNTOMA EN LA BASE DE DATOS (AJAX)
        // =====================================================================
        $('#btnGuardarSintomaBD').click(function() {
          let nombreSintoma = $('#nombre_nuevo_sintoma').val().trim();

          if (nombreSintoma === "") {
            mostrarAviso("⚠️ El nombre del síntoma no puede estar vacío.");
            return;
          }

          let btn = $(this);
          btn.prop('disabled', true).text('Guardando...');

          $.ajax({
            url: '../../cfg/ajax/ajax_guardar_sintoma.php', // <-- OJO: Cambia esto por la ruta real a tu archivo PHP que hace el INSERT
            type: 'POST',
            data: {
              nombre_sintoma: nombreSintoma
            },
            dataType: 'json',
            success: function(response) {
              if (response.success) {
                let nuevoId = response.id;
                let nuevoNombre = response.nombre;

                // 1. Agregar la nueva opción a TODOS los selects existentes (para que esté disponible)
                let nuevaOpcion = `<option value="${nuevoId}">${nuevoNombre}</option>`;
                $('.select-sintoma').append(nuevaOpcion);

                // 2. ELIMINADO: agregarFilaSintoma(nuevoId); <-- Esto causaba que se creara otro select sin tu permiso

                // 3. Limpiamos y cerramos el modal
                $('#nombre_nuevo_sintoma').val('');

                // Cerramos el modal forzando el ocultamiento
                $('#modalNuevoSintoma').modal('hide');

              } else {
                mostrarAviso("Error al guardar: " + (response.error || "Desconocido"));
              }
            },
            error: function() {
              mostrarAviso("Error de conexión al intentar guardar el síntoma.");
            },
            complete: function() {
              btn.prop('disabled', false).text('Guardar y Seleccionar');
            }
          });
        });

        // Limpiar el input cuando se cierre el modal para que no quede basura
        $('#modalNuevoSintoma').on('hidden.bs.modal', function() {
          $('#nombre_nuevo_sintoma').val('');
        });

        // 3. ENVÍO DEL FORMULARIO Y VERIFICACIÓN PREVIA
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
          if ($('#sintomas_ids').val().trim() === "") {
            errores.push("Debe seleccionar al menos un Síntoma.");
            $('#group_sintomas').addClass('has-error');
          }

          if (errores.length > 0) {
            mostrarAviso('⚠️ Errores:<ul><li>' + errores.join('</li><li>') + '</li></ul>');
          } else {
            const nombre = $('#nombre_patologia').val().trim();
            const codigo = $('#codigo_cie').val().trim();
            const idActual = $('input[name="Id"]').length > 0 ? $('input[name="Id"]').val() : 0;
            const btnGuardar = $('#btnGuardar');
            const textoOriginal = btnGuardar.text();

            btnGuardar.text('Verificando...').attr('disabled', true);

            $.ajax({
              url: 'get/verificar_existencia_patologia.php',
              method: 'POST',
              dataType: 'json',
              data: { nombre: nombre, codigo: codigo, id_actual: idActual },
              success: function(response) {
                let errores_ajax = [];
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
                  $('#modalGuardar').modal('show');
                }
              },
              error: function(xhr, status, error) {
                btnGuardar.text(textoOriginal).attr('disabled', false);
                mostrarAviso('🛑 Error de Servidor: No se pudo verificar la base de datos.');
              }
            });
          }
        });

        $('#confirmarGuardar').on('click', function() {
          $('#modalGuardar').modal('hide');
          $('#formularioPatologia').off('submit').submit();
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