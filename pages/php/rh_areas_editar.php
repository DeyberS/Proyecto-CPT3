<?php
if (!isset($_GET['Id']) || empty($_GET['Id'])) {
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }

  $_SESSION['mensaje_user_error'] = "Debe seleccionar una consulta válida para ver su información.";

  header("Location: rh_areas_listado.php");
  exit();
}
include('includes/headerNav2.php');
verificarAcceso('Editar Areas');
?>

<head>
  <title>Áreas | Editar</title>
</head>

<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Editar Área
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="#"><i class="fa fa-users"></i>Áreas</a></li>
      <li class="active"><a href="#"><i class="fa fa-user-plus"></i>Editar</a></li>
    </ol>
  </section>

  <section class="content">

    <div class="row">
      <div class="col-md-12">
        <div class="nav-tabs-custom">
          <ul class="nav nav-tabs">
            <li class="active"><a href="#tab_1" data-toggle="tab">Información del área</a></li>
          </ul>
          <div class="tab-content">
            <div class="tab-pane active" id="tab_1" style="height:180px;">
              <div class="box-body">
                <form id="formularioArea" action="../../cfg/editar/editar_area.php" class="form-group" method="POST" novalidate>
                  <?php
                  include("../../cfg/conexion.php");

                  $areas_id = $_GET['Id'] ?? 0;
                  $sql = "SELECT * FROM departamento WHERE Id_departamento =" . $areas_id;
                  $resultado = $conexion->query($sql);
                  $row = $resultado->fetch_assoc();
                  ?>

                  <input type="hidden" name="Id" value="<?= $row['Id_departamento']; ?>">

                  <label class="control-label"></label>
                  <div class="col-sm-4 form-group" id="group_nombre">
                    <p>Nombre del área (*)</p>
                    <input type="text" class="form-control" placeholder="" name="nombre_area" id="nombre_area" value="<?php echo htmlspecialchars($row['nombre_departamento']); ?>" required>
                  </div>

                  <br><br><br><br><br><br>

                  <div style="float:right; margin-top: 1%;">
                    <button type="button" class="btn btn-second regresar" data-toggle="modal" data-target="#modalRegresar">Regresar</button>
                    <button type="submit" class="btn btn-success guardar" id="btnGuardar">Actualizar</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
  </section>
</div>

<div class="modal fade" id="avisoModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-crimson">
        <h5 class="modal-title">Aviso de Validación</h5>
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
        <a href="rh_areas_listado.php" class="btn btn-danger">Abandonar Formulario</a>
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
        <p>¿Está seguro de que desea actualizar la información del área?</p>
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

    function verificarAreaYMostrarModal() {
      const nombre = $('#nombre_area').val().trim();
      const id_area = $('input[name="Id"]').val();
      const btnGuardar = $('#formularioArea button[type="submit"]');

      const textoOriginal = btnGuardar.text();
      btnGuardar.text('Verificando...').attr('disabled', true);

      $.ajax({
        url: '../../cfg/ajax/verificar_existencia_area.php',
        method: 'POST',
        dataType: 'json',
        data: {
          nombre: nombre,
          id: id_area
        },
        success: function(response) {
          let errores_ajax = [];
          limpiarErrores();
          btnGuardar.text(textoOriginal).attr('disabled', false);

          if (response.existe_nombre) {
            errores_ajax.push(`⚠️ Ya existe otra área registrada con el nombre: ${nombre}`);
            $('#nombre_area').addClass('input-error');
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

    $('#formularioArea').on('submit', function(e) {
      e.preventDefault();
      limpiarErrores();
      let errores = [];

      if ($('#nombre_area').val().trim() === "") {
        errores.push("Falta el Nombre del área.");
        $('#nombre_area').addClass('input-error');
      }

      if (errores.length > 0) {
        mostrarAviso('⚠️ Errores de Formulario: <ul><li>' + errores.join('</li><li>') + '</li></ul>');
      } else {
        verificarAreaYMostrarModal();
      }
    });

    $('#confirmarGuardar').on('click', function() {
      $('#modalGuardar').modal('hide');
      $('#formularioArea').off('submit').submit();
    });

    const campos = [document.getElementById("nombre_area")];
    campos.forEach(campo => {
      if (campo) {
        campo.addEventListener("keydown", bloquearNumeros);
        campo.addEventListener("input", limpiarNumeros);
      }
    });
  });
</script>