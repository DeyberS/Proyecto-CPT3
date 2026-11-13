<?php
include('includes/headerNav2.php');
verificarAcceso('Crear Sintomas');
?>

<head>
  <title>Síntomas | Añadir</title>
</head>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Añadir Síntoma</h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="#"><i class="fa fa-users"></i>Síntomas</a></li>
      <li class="active"><a href="#"><i class="fa fa-user-plus"></i>Añadir</a></li>
    </ol>
  </section>

  <section class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="nav-tabs-custom">
          <ul class="nav nav-tabs">
            <li class="active"><a href="#tab_1" data-toggle="tab">Información del síntoma</a></li>
          </ul>
          <div class="tab-content" style="height:170px;">
            <div class="tab-pane active" id="tab_1">
              <div class="box-body">
                <form action="../../cfg/agregar/agregar_sintoma.php" id="formularioSintoma" class="form-group" method="POST" novalidate>

                  <div class="col-sm-4 form-group" id="group_nombre">
                    <p>Nombre del síntoma (*):</p>
                    <input type="text" class="form-control" name="nombre_sintoma" id="nombre_sintoma" required>
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
          <a href="salud_sintomas_listado.php" class="btn btn-danger">Abandonar Formulario</a>
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
          <p>¿Está seguro de que desea guardar la nueva sintoma?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-success" id="confirmarGuardar">Aceptar</button>
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
      }

      function bloquearNumeros(e) {
        const teclasPermitidas = ["Backspace", "Tab", "ArrowLeft", "ArrowRight", "Delete", "Shift"];
        if (teclasPermitidas.includes(e.key)) return;
        if (e.key >= "0" && e.key <= "9") {
          e.preventDefault();
        }
      }

      function limpiarNumeros(e) {
        e.target.value = e.target.value.replace(/[0-9]/g, "");
      }

      $('#formularioSintoma').on('submit', function(e) {
        e.preventDefault();
        limpiarErrores();
        let errores = [];

        if ($('#nombre_sintoma').val().trim() === "") {
          errores.push("Falta el nombre del sintoma.");
          $('#nombre_sintoma').addClass('input-error');
        }

        if (errores.length > 0) {
          mostrarAviso('⚠️ Errores: <ul><li>' + errores.join('</li><li>') + '</li></ul>');
        } else {
          const nombre = $('#nombre_sintoma').val().trim();
          const idActual = $('input[name="Id"]').length > 0 ? $('input[name="Id"]').val() : 0;
          const btnGuardar = $('#btnGuardar');
          const textoOriginal = btnGuardar.text();

          btnGuardar.text('Verificando...').attr('disabled', true);

          $.ajax({
            url: '../../cfg/ajax/verificar_existencia_sintoma.php',
            method: 'POST',
            dataType: 'json',
            data: {
              nombre: nombre,
              id_actual: idActual
            },
            success: function(response) {
              btnGuardar.text(textoOriginal).attr('disabled', false);
              if (response.existe_nombre) {
                $('#nombre_sintoma').addClass('input-error');
                mostrarAviso('⚠️ Error de Duplicidad:<ul><li>⚠️ Ya existe un sintoma con el nombre: ' + nombre + '</li></ul>');
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
        $('#formularioSintoma').off('submit').submit();
      });

      const campos = [document.getElementById("nombre_sintoma")];
      campos.forEach(campo => {
        if (campo) {
          campo.addEventListener("keydown", bloquearNumeros);
          campo.addEventListener("input", limpiarNumeros);
        }
      });
    });
  </script>