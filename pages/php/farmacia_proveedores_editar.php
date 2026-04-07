<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Proveedor | Editar</title>
  <?php
    include('includes/headerNav2.php');
  ?>
  <style>
    /* ---------------------------------------------------------------------- */
    /* ESTILOS Y ANIMACIONES DE MODALES (Copiados de patologias_agregar.php) */
    /* ---------------------------------------------------------------------- */
    @keyframes pulse-opacity { 0% { opacity: 0; } 100% { opacity: 1; } }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-50px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes fadeOut { from { opacity: 1; transform: translateY(0); } to { opacity: 0; transform: translateY(-50px); } }

    .modal.in .modal-dialog, #avisoModal, #modalGuardar { animation: fadeIn 0.4s ease-out; }
    .modal.out .modal-dialog { animation: fadeOut 0.4s ease-in; }
    .modal-open .modal-backdrop { opacity: 0.7 !important; animation: pulse-opacity 0.3s forwards; }

    /* ESTILOS DE VALIDACIÓN */
    .has-error input[type="text"], .has-error select, .input-error {
      border: 2px solid crimson !important;
      box-shadow: 0 0 5px crimson;
    }
    #display_sintomas_seleccionados.input-error {
      border: 2px solid crimson !important;
      box-shadow: 0 0 5px crimson;
    }

    /* Modales por encima */
    .modal { position: fixed !important; z-index: 99999 !important; }
    .modal-backdrop { z-index: 99998 !important; transition: .5s; }
    .modal.in { display: block; }
  </style>

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
                <li class="active"><a href="#tab_1" data-toggle="tab">Informacion del Proveedor</a></li>
              </ul>
              <div class="tab-content">
                <div class="tab-pane active" id="tab_1" style="height:180px;">
                  <div class="box-body">
                    <form id="formularioProveedor" action="../../cfg/editar/editar_proveedor.php" class="form-group" method="POST" novalidate>
                    <?php
                      include("../../cfg/conexion.php");

                      // 1. Cargar datos de la patología a editar
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
                        <button type="button" class="btn btn-secondary regresar" data-toggle="modal" data-target="#modalRegresar">Regresar</button>
                        <button type="submit" class="btn btn-success guardar" id="btnGuardar">Guardar</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
           </div>
          </div>
        </section>     
           
      <?php
        include('includes/footer.php');
      ?>
    
    <div class="modal" id="avisoModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-crimson" style="color: white;">
                    <h5 class="modal-title">Aviso de Validación</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body"><p id="avisoTexto"></p></div>
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
                <div class="modal-body"><p>Al hacer clic en "Abandonar Formulario", perderá todos los datos no guardados. ¿Desea continuar?</p></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <a href="farmacia_proveedores_listado.php" class="btn btn-danger">Abandonar Formulario</a>
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
                <div class="modal-body"><p>¿Está seguro de que desea actualizar la informacion del Permiso?</p></div>
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

        // --- VISUALIZACIÓN UNIFICADA EN DISPLAY PRINCIPAL ---

        // 3. ENVÍO DEL FORMULARIO
        // Se usa la validación local (sin la validación de duplicidad AJAX que solicitaste omitir)
        $('#formularioProveedor').on('submit', function(e) {
          e.preventDefault(); 
          limpiarErrores();
          let errores = [];
          
          if ($('#nombre_proveedor').val().trim() === "") {
            errores.push("Falta el nombre del proveedor.");
            $('#group_nombre').addClass('has-error');
          }
          

          if (errores.length > 0) {
            mostrarAviso('⚠️ Errores de Formulario: <ul><li>' + errores.join('</li><li>') + '</li></ul>');
          } else {
            // Si pasa la validación local, muestra el modal de confirmación
            $('#modalGuardar').modal('show');
          }
        });
        
        $('#confirmarGuardar').on('click', function() {
            $('#modalGuardar').modal('hide');
            // Al no requerir la validación AJAX de duplicidad, se envía directamente el formulario
            $('#formularioProveedor').off('submit').submit(); 
        });

        // --- Aplicar validaciones a campos de solo texto ---
        const campos = [document.getElementById("nombre_proveedor")];
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
                setTimeout(function() { $modal.modal('hide'); $modal.removeClass('out'); }, 400); 
            } else { $modal.modal('hide'); }
        });
        
        $('.modal').on('hidden.bs.modal', function () {
            if (!$('.modal.in').length) { 
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove(); 
            } else { $('body').addClass('modal-open'); }
        });
      });
    </script>
</body>

</html>