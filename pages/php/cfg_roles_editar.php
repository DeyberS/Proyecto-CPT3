<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Roles | Editar</title>
  <?php
  include('includes/headerNav2.php');
  ?>
  <style>
    /* ---------------------------------------------------------------------- */
    /* ESTILOS Y ANIMACIONES DE MODALES (Copiados de patologias_agregar.php) */
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
    .has-error select,
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

    .permisos-wrapper { border: 1px solid #d2d6de; background: #f9f9f9; border-radius: 4px; margin-top: 10px; }
    .permisos-header { padding: 10px; background: #eee; border-bottom: 1px solid #d2d6de; display: flex; align-items: center; gap: 10px; }
    .input-sm-custom { height: 30px; padding: 5px 10px; font-size: 12px; border-radius: 3px; border: 1px solid #ccc; width: 100%; }
    .permisos-scroll { max-height: 250px; overflow-y: auto; padding: 10px; background: #fff; }
    .permiso-item { padding: 4px 8px; border-radius: 3px; font-size: 13px; display: block; cursor: pointer; }
    .permiso-item:hover { background: #f0f7fd; }
    .permiso-item input { margin-right: 8px; vertical-align: middle; }
  </style>

  <div class="content-wrapper">
    <section class="content-header">
      <h1>
        Editar Rol
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-users"></i>Roles</a></li>
        <li class="active"><a href="#"><i class="fa fa-user-plus"></i>Editar</a></li>
      </ol>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#tab_1" data-toggle="tab">Informacion del Rol</a></li>
            </ul>
            <div class="tab-content" style="margin-bottom:15%;">
              <div class="tab-pane active" id="tab_1">
                <div class="box-body">
                 <div class="row">
                  <form id="formularioRol" action="../../cfg/editar/editar_rol.php" class="form-group" method="POST" novalidate>
                    <?php
                    include("../../cfg/conexion.php");

                    // Obtener el ID del rol a editar
                    $id_rol = $_GET['Id'];

                    // 1. Consultar los datos básicos del rol
                    $queryRol = mysqli_query($conexion, "SELECT * FROM rol WHERE Id_rol = '$id_rol'");
                    $dataRol = mysqli_fetch_array($queryRol);

                    // 2. Obtener los IDs de los permisos que ya tiene este rol y guardarlos en un array
                    $permisosAsignados = [];
                    $queryAsignados = mysqli_query($conexion, "SELECT Id_permiso FROM rol_permiso WHERE Id_rol = '$id_rol'");
                    while ($rowA = mysqli_fetch_array($queryAsignados)) {
                      $permisosAsignados[] = $rowA['Id_permiso'];
                    }
                    ?>

                    <input type="hidden" name="Id" value="<?php echo $id_rol; ?>">

                    <label class="control-label"></label>
                    <div class="col-md-6" id="group_nombre">
                      <div class="form-group">
                        <label for="nombre_rol">Nombre del Rol <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre_rol" name="nombre_rol" value="<?php echo $dataRol['nombre_rol']; ?>" required>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Buscar Permisos</label>
                        <input type="text" id="busquedaPermiso" class="form-control input-sm-custom" placeholder="Buscar permiso asignado o nuevo...">
                      </div>
                    </div>

                    <div class="col-md-12">
                      <div class="permisos-wrapper">
                        <div class="permisos-header">
                          <span style="flex-grow: 1;"><b>Lista de Permisos</b></span>
                          <button type="button" class="btn btn-default btn-xs" id="btnSelectAll">Seleccionar Todos</button>
                          <button type="button" class="btn btn-default btn-xs" id="btnUnselectAll">Quitar Todos</button>
                        </div>

                        <div class="permisos-scroll" id="listaPermisos">
                          <div class="row">
                            <?php
                            $sqlAll = mysqli_query($conexion, "SELECT * FROM permiso ORDER BY nombre_permiso ASC");
                            while ($r = mysqli_fetch_array($sqlAll)) {
                              // VERIFICACIÓN CLAVE: ¿Este ID está en el array de asignados?
                              $checked = in_array($r['Id_permiso'], $permisosAsignados) ? 'checked' : '';
                            ?>
                              <div class="col-md-4 col-sm-6 item-contenedor">
                                <label class="permiso-item">
                                  <input type="checkbox" name="permisos[]" value="<?php echo $r['Id_permiso']; ?>" class="check-permiso" <?php echo $checked; ?>>
                                  <span class="nombre-p"><?php echo $r['nombre_permiso']; ?></span>
                                </label>
                              </div>
                            <?php } ?>
                          </div>
                        </div>
                      </div>
                    </div>

                    <br><br><br><br><br><br>

                    <div style="float:right; margin-top: 1%; margin-right:15px;">
                      <button type="button" class="btn btn-secondary regresar" data-toggle="modal" data-target="#modalRegresar">Regresar</button>
                      <button type="submit" class="btn btn-success guardar" id="btnGuardar">Guardar</button>
                    </div>
                  </form>
                 </div>
                </div>
              </div>
            </div>
            <?php
            include('includes/footer.php');
            ?>
          </div>
        </div>
    </section>
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
          <a href="cfg_roles_listado.php" class="btn btn-danger">Abandonar Formulario</a>
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
          <p>¿Está seguro de que desea actualizar la informacion del Rol?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-success" id="confirmarGuardar">Actualizar</button>
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

      // Función que bloquea números (solo texto)
      function bloquearNumeros(e) {
        const teclasPermitidas = ["Backspace", "Tab", "ArrowLeft", "ArrowRight", "Delete", "Shift"];
        if (teclasPermitidas.includes(e.key)) return;
        if (e.key >= "0" && e.key <= "9") {
          e.preventDefault();
        }
      }
      // Función que limpia números pegados (solo texto)
      function limpiarNumeros(e) {
        e.target.value = e.target.value.replace(/[0-9]/g, "");
      }

      $("#busquedaPermiso").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $(".item-contenedor").filter(function() {
          $(this).toggle($(this).find(".nombre-p").text().toLowerCase().indexOf(value) > -1)
        });
      });

      // Seleccionar / Deseleccionar
      $("#btnSelectAll").click(function() { $(".check-permiso").prop('checked', true); });
      $("#btnUnselectAll").click(function() { $(".check-permiso").prop('checked', false); });

      function verificarRolYMostrarModal() {
          const nombre = $('#nombre_rol').val().trim();
          const idRol = $('input[name="Id"]').length ? $('input[name="Id"]').val() : 0;
          const btnGuardar = $('#btnGuardar');
          const textoOriginal = btnGuardar.text();
          
          btnGuardar.text('Verificando...').attr('disabled', true);

          $.ajax({
            url: 'get/verificar_existencia_rol.php',
            method: 'POST',
            dataType: 'json',
            data: { nombre: nombre, id: idRol },
            success: function(response) {
              btnGuardar.text(textoOriginal).attr('disabled', false);

              if (response.existe_nombre) {
                $('#group_nombre').addClass('has-error');
                $('#nombre_rol').addClass('input-error');
                mostrarAviso('⚠️ Error: Ya existe un rol con el nombre: ' + nombre);
              } else {
                // Si NO existe, mostramos el modal
                $('#modalGuardar').modal('show');
              }
            },
            error: function() {
              btnGuardar.text(textoOriginal).attr('disabled', false);
              mostrarAviso('🛑 Error de Servidor: No se pudo verificar la base de datos.');
            }
          });
        }

        $('#formularioRol').on('submit', function(e) {
          e.preventDefault();
          limpiarErrores();
          let errores = [];

          if ($('#nombre_rol').val().trim() === "") {
            errores.push("Falta el nombre del rol.");
            $('#group_nombre').addClass('has-error');
          }
          
          if (errores.length > 0) {
            mostrarAviso('⚠️ Errores: <ul><li>' + errores.join('</li><li>') + '</li></ul>');
          } else {
            // Validar antes del modal
            verificarRolYMostrarModal();
          }
        });

        $('#confirmarGuardar').on('click', function() {
          $('#modalGuardar').modal('hide');
          $('#formularioRol')[0].submit();
        });

      // --- Aplicar validaciones a campos de solo texto ---
      const campos = [document.getElementById("nombre_rol")];
      campos.forEach(campo => {
        if (campo) {
          campo.addEventListener("keydown", bloquearNumeros);
          campo.addEventListener("input", limpiarNumeros);
        }
      });

      // FIX DE MODALES (Cierre suave y gestión de backdrop)
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