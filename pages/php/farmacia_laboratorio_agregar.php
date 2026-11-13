<?php
include('includes/headerNav2.php');
verificarAcceso('Crear Laboratorios');
?>

<head>
  <title>Laboratorio | Añadir</title>
</head>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Añadir Laboratorio</h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="#"><i class="fa fa-users"></i>Laboratorios</a></li>
      <li class="active"><a href="#"><i class="fa fa-user-plus"></i>Añadir</a></li>
    </ol>
  </section>

  <section class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="nav-tabs-custom">
          <ul class="nav nav-tabs">
            <li class="active"><a href="#tab_1" data-toggle="tab">Información del laboratorio</a></li>
          </ul>
          <div class="tab-content" style="height:180px;">
            <div class="tab-pane active" id="tab_1">
              <div class="box-body">
                <form action="../../cfg/agregar/agregar_laboratorio.php" id="formularioLaboratorio" class="form-group" method="POST" novalidate>

                  <div class="col-sm-4 form-group" id="group_nombre">
                    <p>Nombre del laboratorio (*):</p>
                    <input type="text" class="form-control" name="nombre_laboratorio" id="nombre_laboratorio" required>
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
          <a href="farmacia_laboratorio_listado.php" class="btn btn-danger">Abandonar Formulario</a>
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
          <p>¿Está seguro de que desea guardar la información de este nuevo laboratorio?</p>
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

      function verificarLaboratorioYMostrarModal() {
        const nombre = $('#nombre_laboratorio').val().trim();
        const btnGuardar = $('#btnGuardar');

        const textoOriginal = btnGuardar.text();
        btnGuardar.text('Verificando...').attr('disabled', true);

        $.ajax({
          url: '../../cfg/ajax/verificar_existencia_laboratorio.php',
          method: 'POST',
          dataType: 'json',
          data: {
            nombre: nombre
          },
          success: function(response) {
            limpiarErrores();
            btnGuardar.text(textoOriginal).attr('disabled', false);

            if (response.existe_nombre) {
              $('#nombre_laboratorio').addClass('input-error');
              mostrarAviso(`⚠️ Error de Duplicidad:<ul><li>Ya existe un laboratorio registrado con el nombre: <b>${nombre}</b></li></ul>`);
            } else {
              $('#modalGuardar').modal('show');
            }
          },
          error: function(xhr, status, error) {
            btnGuardar.text(textoOriginal).attr('disabled', false);
            mostrarAviso('⚠️ Error de Servidor: No se pudo verificar la base de datos. <br>Detalle: ' + error);
          }
        });
      }

      $('#formularioLaboratorio').on('submit', function(e) {
        e.preventDefault();
        limpiarErrores();
        let errores = [];

        if ($('#nombre_laboratorio').val().trim() === "") {
          errores.push("Falta el nombre del laboratorio.");
          $('#nombre_laboratorio').addClass('input-error');
        }

        if (errores.length > 0) {
          mostrarAviso('⚠️ Errores: <ul><li>' + errores.join('</li><li>') + '</li></ul>');
        } else {
          verificarLaboratorioYMostrarModal();
        }
      });

      $('#confirmarGuardar').on('click', function() {
        $('#formularioLaboratorio').off('submit').submit();
      });

      const campos = [document.getElementById("nombre_laboratorio")];
      campos.forEach(campo => {
        if (campo) {
          campo.addEventListener("keydown", bloquearNumeros);
          campo.addEventListener("input", limpiarNumeros);
        }
      });
    });
  </script>