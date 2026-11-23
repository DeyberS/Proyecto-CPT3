<?php
include('../../cfg/conexion.php');
$opciones_sintomas = "";

if ($conexion) {
  $sql = "SELECT Id_sintomas, nombre_sintoma FROM sintomas ORDER BY nombre_sintoma ASC";
  $resultado = $conexion->query($sql);

  if ($resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
      $id = $row['Id_sintomas'];
      $nombre = htmlspecialchars($row['nombre_sintoma']);
      $opciones_sintomas .= "<option value='$id' data-nombre='$nombre'>$nombre</option>";
    }
  } else {
    $opciones_sintomas = "<option value=''>No hay síntomas registrados</option>";
  }
} else {
  $opciones_sintomas = "<option value=''>Error de conexión a BD</option>";
}

include('includes/headerNav2.php');
verificarAcceso('Crear Patologias');
?>

<head>
  <title>Patologías | Añadir</title>
</head>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Añadir Patología</h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="#"><i class="fa fa-users"></i>Patologías</a></li>
      <li class="active"><a href="#"><i class="fa fa-user-plus"></i>Añadir</a></li>
    </ol>
  </section>

  <section class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="nav-tabs-custom">
          <ul class="nav nav-tabs">
            <li class="active"><a href="#tab_1" data-toggle="tab">Información de la patología</a></li>
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
                    <button type="button" class="btn btn-second" data-toggle="modal" data-target="#modalRegresar">Regresar</button>
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

  <div class="modal fade" id="modal_sintomas" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
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
        <div class="modal-header bg-primary">
          <h4 class="modal-title">Crear Nuevo Síntoma</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Nombre del Síntoma</label>
            <input type="text" id="nombre_nuevo_sintoma" class="form-control" placeholder="Ej. Dolor de cabeza">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-bg-primary" id="btnGuardarSintomaBD">Guardar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalBuscarSintoma" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
      <div class="modal-content">
        <div class="modal-header bg-info">
          <h4 class="modal-title">Buscar Síntoma</h4>
        </div>
        <div class="modal-body">
          <input type="text" id="inputBuscarSintoma" class="form-control" placeholder="Escriba para filtrar...">
          <div class="list-group" id="listaResultadosSintoma" style="margin-top: 10px; max-height: 200px; overflow-y: auto;"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="avisoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h5 class="modal-title">Aviso Importante</h5>
        </div>
        <div class="modal-body">
          <p id="avisoTexto"></p>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button></div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalRegresar" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h4 class="modal-title">Confirmación de Regreso</h4>
        </div>
        <div class="modal-body">
          <p>Al hacer clic en "Abandonar Formulario", perderá todos los datos no guardados. ¿Desea continuar?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <a href="salud_patologias_listado.php" class="btn btn-danger">Abandonar Formulario</a>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalGuardar" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-green">
          <h4 class="modal-title">Confirmación de Guardado</h4>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea guardar la información de la nueva patología?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-success" id="confirmarGuardar">Aceptar</button>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include('includes/footer.php'); ?>

<script>
  $(document).ready(function() {
    function mostrarAviso(mensaje) {
      $('#avisoTexto').html(mensaje);
      $('#avisoModal').modal('show');
    }

    function limpiarErrores() {
      $('input, select').removeClass('input-error');
      $('.form-group').removeClass('input-error');
      $('#btn_modal_sintomas').removeClass('btn-error-sombreado');
    }

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

      $('#sintomas_ids').val(ids.join(','));
      let textoTooltip = ids.length > 0 ? nombres.join(', ') : 'Ningún síntoma seleccionado';
      $('#btn_modal_sintomas').attr('data-original-title', textoTooltip).tooltip('fixTitle');
    });

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

    $('#btnGuardarSintomaBD').click(function() {
      let nombreSintoma = $('#nombre_nuevo_sintoma').val().trim();

      if (nombreSintoma === "") {
        mostrarAviso("⚠️ El nombre del síntoma no puede estar vacío.");
        return;
      }

      let btn = $(this);
      btn.prop('disabled', true).text('Guardando...');

      $.ajax({
        url: '../../cfg/ajax/ajax_guardar_sintoma.php',
        type: 'POST',
        data: {
          nombre_sintoma: nombreSintoma
        },
        dataType: 'json',
        success: function(response) {
          if (response.success) {
            let nuevoId = response.id;
            let nuevoNombre = response.nombre;
            let nuevaOpcion = `<option value="${nuevoId}">${nuevoNombre}</option>`;

            $('.select-sintoma').append(nuevaOpcion);
            $('#nombre_nuevo_sintoma').val('');
            $('#modalNuevoSintoma').modal('hide');

          } else {
            mostrarAviso("⚠️ Error al guardar: " + (response.error || "Desconocido"));
          }
        },
        error: function() {
          mostrarAviso("⚠️ Error de conexión al intentar guardar el síntoma.");
        },
        complete: function() {
          btn.prop('disabled', false).text('Guardar y Seleccionar');
        }
      });
    });

    $('#modalNuevoSintoma').on('hidden.bs.modal', function() {
      $('#nombre_nuevo_sintoma').val('');
    });

    $('#formularioPatologia').on('submit', function(e) {
      e.preventDefault();
      limpiarErrores();
      let errores = [];

      if ($('#nombre_patologia').val().trim() === "") {
        errores.push("Falta el Nombre de la patología.");
        $('#nombre_patologia').addClass('input-error');
      }
      if ($('#codigo_cie').val().trim() === "") {
        errores.push("Falta el Código CIE-10.");
        $('#codigo_cie').addClass('input-error');
      }
      if ($('#enfermedad_contagiosa').val() === "") {
        errores.push("Seleccione si es contagiosa.");
        $('#enfermedad_contagiosa').addClass('input-error');
      }
      if ($('#sintomas_ids').val().trim() === "") {
        errores.push("Debe seleccionar al menos un Síntoma.");
        $('#btn_modal_sintomas').addClass('btn-error-sombreado');
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
          url: '../../cfg/ajax/verificar_existencia_patologia.php',
          method: 'POST',
          dataType: 'json',
          data: {
            nombre: nombre,
            codigo: codigo,
            id_actual: idActual
          },
          success: function(response) {
            let errores_ajax = [];
            btnGuardar.text(textoOriginal).attr('disabled', false);

            if (response.existe_nombre) {
              errores_ajax.push(`⚠️ Ya existe una patología con el nombre: ${nombre}`);
              $('#nombre_patologia').addClass('input-error');
            }
            if (response.existe_codigo) {
              errores_ajax.push(`⚠️ Ya existe una patología con el Código CIE-10: ${codigo}`);
              $('#codigo_cie').addClass('input-error');
            }

            if (errores_ajax.length > 0) {
              mostrarAviso('⚠️ Error de Duplicidad:' + '<ul><li>' + errores_ajax.join('</li><li>') + '</li></ul>');
            } else {
              $('#modalGuardar').modal('show');
            }
          },
          error: function(xhr, status, error) {
            btnGuardar.text(textoOriginal).attr('disabled', false);
            mostrarAviso('⚠️ Error de Servidor: No se pudo verificar la base de datos.');
          }
        });
      }
    });

    $('#confirmarGuardar').on('click', function() {
      $('#modalGuardar').modal('hide');
      $('#formularioPatologia').off('submit').submit();
    });
  });
</script>