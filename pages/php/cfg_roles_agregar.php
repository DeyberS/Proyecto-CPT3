<?php
include('includes/headerNav2.php');
verificarAcceso('Crear Roles');
?>

<head>
  <title>Roles | Añadir</title>
</head>

<style>
  .input-error input[type="text"],
  .input-error select .input-error {
    border: 2px solid crimson !important;
    box-shadow: 0 0 5px crimson !important;
    background-color: #fff5f5 !important;
  }

  .permisos-wrapper {
    border: 1px solid #d2d6de;
    background: #f9f9f9;
    border-radius: 4px;
    margin-top: 10px;
  }

  .permisos-header {
    padding: 10px;
    background: #eee;
    border-bottom: 1px solid #d2d6de;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .input-sm-custom {
    height: 30px;
    padding: 5px 10px;
    font-size: 12px;
    border-radius: 3px;
    border: 1px solid #ccc;
    width: 100%;
  }

  .permisos-scroll {
    max-height: 250px;
    overflow-y: auto;
    padding: 10px;
    background: #fff;
  }

  .permiso-item {
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 13px;
    display: block;
    cursor: pointer;
  }

  .permiso-item:hover {
    background: #f0f7fd;
  }

  .permiso-item input {
    margin-right: 8px;
    vertical-align: middle;
  }
</style>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Añadir Rol</h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="#"><i class="fa fa-users"></i>Roles</a></li>
      <li class="active"><a href="#"><i class="fa fa-user-plus"></i>Añadir</a></li>
    </ol>
  </section>

  <section class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="nav-tabs-custom">
          <ul class="nav nav-tabs">
            <li class="active"><a href="#tab_1" data-toggle="tab">Información del Rol</a></li>
          </ul>
          <div class="tab-content" style="margin-bottom:15%;">
            <div class="tab-pane active" id="tab_1">
              <div class="box-body">
                <div class="row">
                  <form action="../../cfg/agregar/agregar_rol.php" id="formularioRol" class="form-group" method="POST" novalidate>
                    <div class="col-md-6">
                      <div class="form-group" id="group_nombre">
                        <label for="nombre_rol">Nombre del Rol (*):<span class="text-danger"></span></label>
                        <input type="text" class="form-control" id="nombre_rol" name="nombre_rol" placeholder="Ej: Administrador" required>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Buscar Permiso:</label>
                        <input type="text" id="busquedaPermiso" class="form-control input-sm-custom" placeholder="Escriba para filtrar permisos...">
                      </div>
                    </div>

                    <div class="col-md-12">
                      <div class="permisos-wrapper">
                        <div class="permisos-header">
                          <span style="flex-grow: 1;"><b>Lista de Permisos:</b></span>
                          <button type="button" class="btn btn-second btn-xs" id="btnSelectAll">Seleccionar Todos</button>
                          <button type="button" class="btn btn-second btn-xs" id="btnUnselectAll">Quitar Todos</button>
                        </div>

                        <div class="permisos-scroll" id="listaPermisos">
                          <div class="row">
                            <?php
                            include('../../cfg/conexion.php');
                            $sql = mysqli_query($conexion, "SELECT * FROM permiso ORDER BY nombre_permiso ASC");
                            while ($r = mysqli_fetch_array($sql)) {
                            ?>
                              <div class="col-md-4 col-sm-6 item-contenedor">
                                <label class="permiso-item">
                                  <input type="checkbox" name="permisos[]" value="<?php echo $r['Id_permiso']; ?>" class="check-permiso">
                                  <span class="nombre-p"><?php echo $r['nombre_permiso']; ?></span>
                                </label>
                              </div>
                            <?php } ?>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div style="float:right; margin-top: 2%; margin-right:15px;">
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
          <a href="cfg_roles_listado.php" class="btn btn-danger">Abandonar Formulario</a>
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
          <p>¿Está seguro de que desea guardar el nuevo rol?</p>
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

      $("#busquedaPermiso").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $(".item-contenedor").filter(function() {
          $(this).toggle($(this).find(".nombre-p").text().toLowerCase().indexOf(value) > -1)
        });
      });

      $("#btnSelectAll").click(function() {
        $(".check-permiso").prop('checked', true);
      });

      $("#btnUnselectAll").click(function() {
        $(".check-permiso").prop('checked', false);
      });

      function verificarRolYMostrarModal() {
        const nombre = $('#nombre_rol').val().trim();
        const idRol = $('input[name="Id"]').length ? $('input[name="Id"]').val() : 0;
        const btnGuardar = $('#btnGuardar');
        const textoOriginal = btnGuardar.text();

        btnGuardar.text('Verificando...').attr('disabled', true);

        $.ajax({
          url: '../../cfg/ajax/verificar_existencia_rol.php',
          method: 'POST',
          dataType: 'json',
          data: {
            nombre: nombre,
            id: idRol
          },
          success: function(response) {
            btnGuardar.text(textoOriginal).attr('disabled', false);

            if (response.existe_nombre) {
              $('#group_nombre').addClass('input-error');
              $('#nombre_rol').addClass('input-error');
              mostrarAviso('⚠️ Error: Ya existe un rol con el nombre: ' + nombre);
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

      $('#formularioRol').on('submit', function(e) {
        e.preventDefault();
        limpiarErrores();
        let errores = [];

        if ($('#nombre_rol').val().trim() === "") {
          errores.push("Falta el nombre del rol.");
          $('#group_nombre').addClass('input-error');
        }

        if (errores.length > 0) {
          mostrarAviso('⚠️ Errores: <ul><li>' + errores.join('</li><li>') + '</li></ul>');
        } else {
          verificarRolYMostrarModal();
        }
      });

      $('#confirmarGuardar').on('click', function() {
        $('#modalGuardar').modal('hide');
        $('#formularioRol')[0].submit();
      });

      const campos = [document.getElementById("nombre_rol")];
      campos.forEach(campo => {
        if (campo) {
          campo.addEventListener("keydown", bloquearNumeros);
          campo.addEventListener("input", limpiarNumeros);
        }
      });
    });
  </script>