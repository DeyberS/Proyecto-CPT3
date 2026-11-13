<?php
if (!isset($_GET['Id']) || empty($_GET['Id'])) {
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }

  $_SESSION['mensaje_user_error'] = "Debe seleccionar una consulta válida para ver su información.";

  header("Location: farmacia_proveedores_listado.php");
  exit();
}

$id_proveedor = $_GET['Id'];

include('includes/headerNav2.php');
verificarAcceso('Editar Proveedores');
?>

<head>
  <title>Proveedores | Editar</title>

  <div class="content-wrapper">
    <section class="content-header">
      <h1>
        Editar Proveedor
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-users"></i>Proveedores</a></li>
        <li class="active"><a href="#"><i class="fa fa-user-plus"></i>Editar</a></li>
      </ol>
    </section>

    <section class="content">

      <div class="row">
        <div class="col-md-12">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#tab_1" data-toggle="tab">Información del proveedor</a></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane active" id="tab_1" style="height:180px;">
                <div class="box-body">
                  <form id="formularioProveedor" action="../../cfg/editar/editar_proveedor.php" class="form-group" method="POST" novalidate>
                    <?php
                    include("../../cfg/conexion.php");

                    $proveedor = $_GET['Id'] ?? 0;
                    $sql = "SELECT * FROM proveedor WHERE Id_proveedor =" . $proveedor;
                    $resultado = $conexion->query($sql);
                    $row = $resultado->fetch_assoc();
                    ?>

                    <input type="hidden" name="Id" value="<?= $row['Id_proveedor']; ?>">

                    <label class="control-label"></label>
                    <div class="col-sm-4 form-group" id="group_nombre">
                      <p>Nombre del proveedor (*)</p>
                      <input type="text" class="form-control" placeholder="" name="nombre_proveedor" id="nombre_proveedor" value="<?php echo htmlspecialchars($row['nombre_proveedor']); ?>" required>
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
            <a href="farmacia_proveedores_listado.php" class="btn btn-danger">Abandonar Formulario</a>
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
            <p>¿Está seguro de que desea actualizar la información del proveedor?</p>
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

        function verificarProveedorYMostrarModal() {
          const nombre = $('#nombre_proveedor').val().trim();
          const id_proveedor = $('input[name="Id"]').val();
          const btnGuardar = $('#btnGuardar');

          const textoOriginal = btnGuardar.text();
          btnGuardar.text('Verificando...').attr('disabled', true);

          $.ajax({
            url: '../../cfg/ajax/verificar_existencia_proveedor.php',
            method: 'POST',
            dataType: 'json',
            data: {
              nombre: nombre,
              id: id_proveedor
            },
            success: function(response) {
              limpiarErrores();
              btnGuardar.text(textoOriginal).attr('disabled', false);

              if (response.existe_nombre) {
                $('#nombre_proveedor').addClass('input-error');
                mostrarAviso(`⚠️ Error de Duplicidad:<ul><li>Ya existe otro proveedor registrado con el nombre: <b>${nombre}</b></li></ul>`);
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

        $('#formularioProveedor').on('submit', function(e) {
          e.preventDefault();
          limpiarErrores();
          let errores = [];

          if ($('#nombre_proveedor').val().trim() === "") {
            errores.push("Falta el nombre del proveedor.");
            $('#nombre_proveedor').addClass('input-error');
          }

          if (errores.length > 0) {
            mostrarAviso('⚠️ Errores de Formulario: <ul><li>' + errores.join('</li><li>') + '</li></ul>');
          } else {
            verificarProveedorYMostrarModal();
          }
        });

        $('#confirmarGuardar').on('click', function() {
          $('#modalGuardar').modal('hide');
          $('#formularioProveedor').off('submit').submit();
        });

        const campos = [document.getElementById("nombre_proveedor")];
        campos.forEach(campo => {
          if (campo) {
            campo.addEventListener("keydown", bloquearNumeros);
            campo.addEventListener("input", limpiarNumeros);
          }
        });
      });
    </script>