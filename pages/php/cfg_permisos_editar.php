<?php
include("../../cfg/conexion.php");

// 1. Validar si no se envió el ID o si está vacío
if (!isset($_GET['Id']) || empty($_GET['Id'])) {
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }

  $_SESSION['mensaje_user_error'] = "Debe seleccionar una consulta válida para ver su información.";

  header("Location: cfg_permisos_listado.php");
  exit();
}

$permiso = mysqli_real_escape_string($conexion, $_GET['Id']);

// 2. Consultar si el registro realmente existe en la base de datos
$sql = "SELECT * FROM permiso WHERE Id_permiso = '$permiso'";
$resultado = $conexion->query($sql);

if (!$resultado || $resultado->num_rows === 0) {
  $_SESSION['mensaje_user_error'] = "Error: El permiso solicitado no existe o fue eliminado.";
  header("Location: cfg_permisos_listado.php");
  exit();
}

$row = $resultado->fetch_assoc();

include('includes/headerNav2.php');
verificarAcceso('Editar Permisos');
?>

<head>
  <title>Permisos | Editar</title>
</head>

<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Editar Permiso
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="#"><i class="fa fa-users"></i>Permisos</a></li>
      <li class="active"><a href="#"><i class="fa fa-user-plus"></i>Editar</a></li>
    </ol>
  </section>

  <section class="content">

    <div class="row">
      <div class="col-md-12">
        <div class="nav-tabs-custom">
          <ul class="nav nav-tabs">
            <li class="active"><a href="#tab_1" data-toggle="tab">Información del Permiso</a></li>
          </ul>
          <div class="tab-content">
            <div class="tab-pane active" id="tab_1" style="height:180px;">
              <div class="box-body">
                <form id="formularioPermiso" action="../../cfg/editar/editar_permiso.php" class="form-group" method="POST" novalidate>
                  <?php
                  include("../../cfg/conexion.php");

                  $permiso = $_GET['Id'] ?? 0;
                  $sql = "SELECT * FROM permiso WHERE Id_permiso =" . $permiso;
                  $resultado = $conexion->query($sql);
                  $row = $resultado->fetch_assoc();
                  ?>

                  <input type="hidden" name="Id" value="<?= $row['Id_permiso']; ?>">

                  <label class="control-label"></label>
                  <div class="col-sm-4 form-group" id="group_nombre">
                    <p>Nombre del permiso (*)</p>
                    <input type="text" class="form-control" placeholder="" name="nombre_permiso" id="nombre_permiso" value="<?php echo htmlspecialchars($row['nombre_permiso']); ?>" required>
                  </div>

                  <div class="col-sm-4 form-group" id="group_descripcion">
                    <p>Descripción (*)</p>
                    <input type="text" class="form-control" placeholder="" name="descripcion" id="descripcion" value="<?php echo htmlspecialchars($row['descripcion']); ?>" required>
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
    </div>
  </section>

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
          <a href="cfg_permisos_listado.php" class="btn btn-danger">Abandonar Formulario</a>
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
          <p>¿Está seguro de que desea actualizar la información del Permiso?</p>
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

    function verificarPermisoYMostrarModal() {
      const nombre = $('#nombre_permiso').val().trim();
      const idPermiso = $('input[name="Id"]').length ? $('input[name="Id"]').val() : 0;
      const btnGuardar = $('#btnGuardar');
      const textoOriginal = btnGuardar.text();

      btnGuardar.text('Verificando...').attr('disabled', true);

      $.ajax({
        url: '../../cfg/ajax/verificar_existencia_permiso.php',
        method: 'POST',
        dataType: 'json',
        data: {
          nombre: nombre,
          id: idPermiso
        },
        success: function(response) {
          btnGuardar.text(textoOriginal).attr('disabled', false);

          if (response.existe_nombre) {
            $('#nombre_permiso').addClass('input-error');
            mostrarAviso('⚠️ Error: Ya existe un permiso con el nombre: ' + nombre);
          } else {
            $('#modalGuardar').modal('show');
          }
        },
        error: function() {
          btnGuardar.text(textoOriginal).attr('disabled', false);
          mostrarAviso('⚠️ Error de Servidor: No se pudo verificar la base de datos.');
        }
      });
    }

    $('#formularioPermiso').on('submit', function(e) {
      e.preventDefault();
      limpiarErrores();
      let errores = [];

      if ($('#nombre_permiso').val().trim() === "") {
        errores.push("Falta el nombre del permiso.");
        $('#nombre_permiso').addClass('input-error');
      }
      if ($('#descripcion').val().trim() === "") {
        errores.push("Falta la descripcion del permiso.");
        $('#descripcion').addClass('input-error');
      }

      if (errores.length > 0) {
        mostrarAviso('⚠️ Errores: <ul><li>' + errores.join('</li><li>') + '</li></ul>');
      } else {
        verificarPermisoYMostrarModal();
      }
    });

    $('#confirmarGuardar').on('click', function() {
      $('#modalGuardar').modal('hide');
      $('#formularioPermiso')[0].submit();
    });

    const campos = [document.getElementById("nombre_permiso"), document.getElementById("descripcion")];
    campos.forEach(campo => {
      if (campo) {
        campo.addEventListener("keydown", bloquearNumeros);
        campo.addEventListener("input", limpiarNumeros);
      }
    });
  });
</script>