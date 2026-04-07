<?php
include('../../cfg/conexion.php');

$datos_d = null;
$comp_string = '';

if (isset($_GET['duplicar_id'])) {
  $id_url = mysqli_real_escape_string($conexion, $_GET['duplicar_id']);

  // 1. Buscamos los datos generales usando el ID de la descripción (el que viene de la URL)
  $sql_d = "SELECT m.nombre_medicamento, dm.* FROM medicamento m 
              JOIN descripcion_medicamento dm ON m.Id_medicamento = dm.Id_medicamento 
              WHERE dm.Id = '$id_url'";
  $res_d = $conexion->query($sql_d);
  $datos_d = $res_d->fetch_assoc();

  if ($datos_d) {
    // 2. CORRECCIÓN CLAVE: Usamos dm.Id (que aquí es $id_url) para buscar los principios
    // ya que detalle_principio_medicamento.id_medicamento apunta a descripcion_medicamento.Id
    $sql_pa = "SELECT d.id_principio_activo, d.cantidad_unidad_medida, d.id_tipo_unidad_medida, p.nombre 
               FROM detalle_principio_medicamento d
               JOIN principio_activo p ON d.id_principio_activo = p.id_principio_activo
               WHERE d.id_medicamento = '$id_url'";

    $res_pa = $conexion->query($sql_pa);

    $pa_parts = [];
    $lista_pa_full = []; 

    while ($r_pa = $res_pa->fetch_assoc()) {
      $pa_parts[] = $r_pa['id_principio_activo'] . "," . $r_pa['cantidad_unidad_medida'] . "," . $r_pa['id_tipo_unidad_medida'];
      $lista_pa_full[] = $r_pa;
    }

    $comp_string = implode('|', $pa_parts);
    $principios_json = json_encode($lista_pa_full); 
  }
}
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Medicamentos | Añadir</title>
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
    .has-error #tipo_unidad_medida,
    .has-error #tipo,
    .has-error #via_aplicacion,
    .has-error #presentacion,
    .has-error #almacenamiento,
    .has-error #proveedor,
    .has-error .select-pa,
    .input-error {
      border: 2px solid crimson !important;
      box-shadow: 0 0 5px crimson;
    }

    #display_sintomas_seleccionados.input-error {
      border: 2px solid crimson !important;
      box-shadow: 0 0 5px crimson;
    }

    .tooltip-inner {
      max-width: 300px;
      /* Para que no sea un hilo largo */
      background-color: #3c8dbc !important;
      /* Color azul como tu header */
      color: white;
      font-weight: bold;
      border: 1px solid #fff;
    }

    .tooltip.right .tooltip-arrow {
      border-right-color: #3c8dbc !important;
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
      <h1>
        Añadir Medicamento
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-archive"></i>Medicamento</a></li>
        <li class="active"><a href="#"><i class="fa fa-plus-circle"></i>Añadir</a></li>
      </ol>
    </section>

    <section class="content">

      <div class="row">
        <div class="col-md-12">
          <div style="float:right; margin-top:5px; margin-right:5px;">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalCopiarMedicamento" title="Copiar Medicamento" style="background-color: #605ca8; border-color: #605ca8;">
              <i><img src="../../recursos/imagenes/iconos/Importar.png" style="width:20px; height:20px;"></i>
            </button>
          </div>
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#tab_1" data-toggle="tab">Detalles de La Operacion</a></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane active" id="tab_1">
                <div class="box-body">

                  <form id="formularioMedicamento" style="margin-bottom:8%;" method="POST" action="../../cfg/agregar/agregar_medicamento.php" novalidate>
                    <label class="control-label"></label>
                    <div class="col-sm-4 form-group" id="group_nombre">
                      <p>Nombre (*):</p>
                      <input id="medicamento" name="medicamento" class="form-control" type="text" maxlength="100" value="<?php echo $datos_d['nombre_medicamento'] ?? ''; ?>" placeholder="Ej. Ibuprofeno" required>
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-4 form-group" id="group_presentacion">
                      <p>Presentacion (*):</p>
                      <select class="form-control" name="presentacion" id="presentacion" class="form-control" required>
                        <option value="">--- Seleccione la presentacion del medicamento ---</option>
                        <?php
                        $sql = $conexion->query("SELECT * FROM presentacion");
                        while ($r = $sql->fetch_assoc()) {
                          $selected = (isset($datos_d['Id_presentacion']) && $datos_d['Id_presentacion'] == $r['Id_presentacion']) ? 'selected' : '';
                          echo "<option value='{$r['Id_presentacion']}' $selected>{$r['nombre_presentacion']}</option>";
                        }
                        ?>
                      </select>
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-3 form-group" id="group_principio_activo">
                      <p>Principios activos (*):</p>
                      <button type="button" class="btn btn-info btn-block" id="btn_modal_pa" data-toggle="modal" data-placement="top" title="Ninguno seleccionado" data-target="#modalPrincipios">
                        <i></i> Gestionar Principios Activos
                      </button>
                    </div>
                    <input type="hidden" name="composicion_detallada" id="composicion_detallada" value="<?php echo $comp_string ?? ''; ?>" required>
                    <br><br><br><br>
                    <label class="control-label"></label>
                    <div class="col-sm-4 form-group" id="group_contenido_neto">
                      <p>Contenido neto (*):</p>
                      <input id="contenido_neto" name="contenido_neto" class="form-control" type="text" value="<?php echo $datos_d['contenido_neto'] ?? ''; ?>" maxlength="100" placeholder="Ej. Capsulas de 20mg" required>
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-4 form-group" id="group_via">
                      <p>Via de aplicación (*):</p>
                      <select name="via" id="via_aplicacion" class="form-control">
                        <option value="">--- Seleccione una vía de aplicación ---</option>
                        <?php
                        // Definimos el array de opciones para hacerlo más limpio
                        $vias = [
                          "Oral", "Sublingual", "Rectal", "Intravenosa", "Intramuscular",
                          "Subcutanea", "Intradermica", "Topica", "Transdermica",
                          "Inhalatoria", "Oftalmica", "Otica", "Nasal", "Vaginal"
                        ];

                        foreach ($vias as $via) {
                          // Si el valor coincide con el del medicamento a duplicar, marcamos 'selected'
                          $selected = (isset($datos_d['via_aplicacion']) && $datos_d['via_aplicacion'] == $via) ? 'selected' : '';
                          echo "<option value='$via' $selected>$via</option>";
                        }
                        ?>
                      </select>
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-3 form-group" id="group_almacenamiento">
                      <p>C. de almacenamiento (*):</p>
                      <select name="almacenamiento" id="almacenamiento" class="form-control" required>
                        <option value="">--- Seleccione una condición ---</option>
                        <?php
                        // Definimos value => etiqueta para mantener la estructura limpia
                        $condiciones = [
                          "-25_a_-10" => "Congelacion (-25°C a -10°C)",
                          "2_a_8"     => "Refrigeracion (2°C a 8°C)",
                          "8_a_15"    => "Lugar Fresco (8°C a 15°C)",
                          "15_a_25"   => "Temperatura Ambiente (15°C a 25°C)",
                          "max_30"    => "Temperatura Maxima (30°C)"
                        ];

                        foreach ($condiciones as $valor => $etiqueta) {
                          // Comparamos el valor actual con el que traemos para duplicar
                          $selected = (isset($datos_d['almacenamiento']) && $datos_d['almacenamiento'] == $valor) ? 'selected' : '';
                          echo "<option value='$valor' $selected>$etiqueta</option>";
                        }
                        ?>
                      </select>
                    </div>
                    <br><br><br><br>
                    <label class="control-label"></label>
                    <div class="col-sm-4" id="group_laboratorio">
                      <p>Laboratorio:</p>
                      <div class="input-group">
                        <select id="laboratorio" name="laboratorio" class="form-control">
                          <option value="">--- Seleccione un laboratorio ---</option>
                          <?php
                          include('../../cfg/conexion.php');
                          $sql = $conexion->query("SELECT * FROM laboratorio");
                          while ($resultado = $sql->fetch_assoc()) {
                            // Comparamos el ID del laboratorio actual con el del medicamento que estamos duplicando
                            $selected = (isset($datos_d['Id_laboratorio']) && $datos_d['Id_laboratorio'] == $resultado['Id_laboratorio']) ? 'selected' : '';

                            echo "<option value='" . $resultado['Id_laboratorio'] . "' $selected>" . $resultado['nombre_laboratorio'] . "</option>";
                          }
                          ?>
                        </select>

                        <span class="input-group-btn">
                          <button class="btn btn-info" type="button" id="btnInfoMedicamento" data-toggle="modal" data-target="#modalNuevoLaboratorio" title="Agregar Laboratorio" style="height: 34px;">
                            <i><img src="../../recursos/imagenes/iconos/agregar.png" style="width:10px; height:10px;"></i>
                          </button>
                        </span>
                      </div>
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-4 form-group" id="group_excipientes">
                      <p>Excipientes:</p>
                      <input type="text" id="excipientes" name="excipientes" value="<?php echo $datos_d['excipientes'] ?? ''; ?>" placeholder="Ej: Microcristalina celulosa, dióxido de titanio y gelatina." class="form-control">
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-3 form-group" id="group_codigo_barras">
                      <p>Codigo de barras:</p>
                      <input type="text" id="codigo_barras" name="codigo_barras" placeholder="Ej. 234758383" class="form-control">
                    </div>
                    <br><br><br><br>
                    <div style="float:right; margin-top: 2%;">
                      <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#modalRegresar">Regresar</button>
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
  </div>

  <div class="modal fade" id="modalPrincipios" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: #3c8dbc; color: white;">
          <h4 class="modal-title">Agregar Principios Activos</h4>
        </div>
        <div class="modal-body">
          <div id="contenedor_filas_principios">
            <div class="row fila-pa" style="margin-bottom: 10px;">
              <div class="col-sm-6">
                <select class="form-control select-pa" id="principio_activo">
                  <option value="" name="id_pa">--- Seleccione un principio activo ---</option>
                  <?php
                  $sql_pa = $conexion->query("SELECT * FROM principio_activo");
                  while ($r = $sql_pa->fetch_assoc()) {
                    echo "<option value='" . $r['id_principio_activo'] . "' data-nombre='" . $r['nombre'] . "'>" . $r['nombre'] . "</option>";
                  }
                  ?>
                </select>
              </div>
              <div class="col-sm-2 form-group" id="group_unidad">
                <input type="text" class="form-control cant-pa" name="cantidad_unidad_medida" id="u_medida" placeholder="Cant." inputmode="numeric" required>
              </div>
              <div class="col-lg-2 pull-left form-group" id="group_tipo_unidad" style="margin-left:-20px;">
                <select class="form-control uni-pa" name="tipo_unidad_medida" id="tipo_unidad_medida" required>
                  <option selected value="">Primero Seleccione una Presentacion</option>
                </select>
              </div>
              <div class="col-sm-2">
                <button type="button" class="btn btn-danger btn-remove-pa"><i><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></i></button>
              </div>
            </div>
          </div>
          <button type="button" class="btn btn-success btn-sm" id="btn_add_pa">
            <i class="fa fa-plus"></i> Añadir otro
          </button>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-dismiss="modal" id="guardar_pa_temp">Listo</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalNuevoLaboratorio" tabindex="-1" role="dialog" aria-labelledby="labelLaboratorio">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: #3c8dbc; color: white;">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="labelLaboratorio"><i class="fa fa-building"></i>Nuevo Laboratorio</h4>
        </div>
        <div class="modal-body">
          <form id="formNuevoLaboratorio">
            <div class="form-group">
              <label>Nombre del Laboratorio:</label>
              <input type="text" id="nombre_lab_nuevo" class="form-control" placeholder="Ej: Bayer" required>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary" id="btnGuardarLab">Guardar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalCopiarMedicamento" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: #3c8dbc; color: white;">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title"><i class="fa fa-search"></i> Buscar Medicamento para Copiar</h4>
        </div>
        <div class="modal-body">
          <input type="text" id="buscar_plantilla" class="form-control" placeholder="Escribe el nombre del medicamento...">
          <hr>
          <div id="lista_medicamentos_plantilla" style="max-height: 400px; overflow-y: auto;">
            <p class="text-center">Cargando medicamentos...</p>
          </div>
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
          <a href="farmacia_medicamentos_listado.php" class="btn btn-danger">Abandonar Formulario</a>
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
          <p>¿Está seguro de que desea guardar el nuevo medicamento?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-success" id="confirmarGuardar">Guardar</button>
        </div>
      </div>
    </div>
  </div>

  <?php
  include('includes/footer.php');
  ?>

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

      const medicamentoInput = document.getElementById('medicamento');
      const uMedidaInput = document.getElementById('u_medida');

      // --- 1. Validación para NOMBRE (Solo letras y límite de 50) ---
      medicamentoInput.addEventListener('input', function() {
        let valor = this.value;
        // 1.a: Eliminar cualquier carácter que NO sea una letra, espacio o acento.
        valor = valor.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');

        // 1.b: Limitar a 50 caracteres (por si el usuario pega un texto muy largo)
        if (valor.length > 50) {
          valor = valor.substring(0, 50);
        }

        this.value = valor;
      });


      // --- 2. Validación para U. MEDIDA (Solo números, incluyendo decimales) ---
      // Validación para CANTIDAD (Solo números y decimales) - Delegación de eventos
      $(document).on('input', '.cant-pa', function() {
        let valor = this.value;
        valor = valor.replace(/[^0-9.]/g, '');
        const partes = valor.split('.');
        if (partes.length > 2) {
          valor = partes[0] + '.' + partes.slice(1).join('');
        }
        this.value = valor;
      });

      // --- Lógica de Principios Activos en Modal ---

      // Agregar nueva fila dentro del modal
      $('#btn_add_pa').on('click', function() {
        var nuevaFila = $('.fila-pa:first').clone();
        nuevaFila.find('input').val('');
        nuevaFila.find('select').val('');
        $('#contenedor_filas_principios').append(nuevaFila);
      });

      // Quitar fila
      $('#contenedor_filas_principios').on('click', '.btn-remove-pa', function() {
        if ($('.fila-pa').length > 1) {
          $(this).closest('.fila-pa').remove();
        } else {
          mostrarAviso("Debe haber al menos un espacio para principio activo.");
        }
      });

      // 1. Inicializar el tooltip manualmente por ID para evitar conflictos
      $('#btn_modal_pa').tooltip();

      // 2. Actualizar la función de guardado del modal
      $('#guardar_pa_temp').on('click', function() {
        var resumen = [];
        var datos_para_db = [];

        $('.fila-pa').each(function() {
          var nombre = $(this).find('.select-pa option:selected').data('nombre');
          var id_pa = $(this).find('.select-pa').val();
          var cantidad = $(this).find('.cant-pa').val();
          var unidad = $(this).find('.uni-pa option:selected').text();
          var id_unidad = $(this).find('.uni-pa').val();

          if (nombre && cantidad && id_unidad) {
            resumen.push(nombre + " " + cantidad + " " + unidad);
            datos_para_db.push(id_pa + "," + cantidad + "," + id_unidad);
          }
        });

        var boton = $('#btn_modal_pa');

        if (resumen.length > 0) {
          var listaTexto = resumen.join(', ');
          $('#resumen_principios').html("<strong>Incluye:</strong> " + listaTexto);
          $('#composicion_detallada').val(datos_para_db.join('|'));

          // Actualizamos el tooltip correctamente
          boton.attr('data-original-title', listaTexto).tooltip('fixTitle');
        } else {
          $('#resumen_principios').html("<em>Ninguno seleccionado</em>");
          $('#composicion_detallada').val('');
          boton.attr('data-original-title', 'Ninguno seleccionado').tooltip('fixTitle');
        }
      });

      // Actualizar unidades en el modal según el Tipo seleccionado
      document.getElementById('presentacion').addEventListener('change', function() {
        const idPresentacion = this.value;
        // Buscamos todos los selects de unidades por su clase
        const selectsUnidad = document.querySelectorAll('.uni-pa');

        if (idPresentacion !== "") {
          fetch('../../cfg/ajax/obtener_unidades_medicamentos.php?id=' + idPresentacion)
            .then(response => response.text())
            .then(data => {
              selectsUnidad.forEach(select => {
                select.innerHTML = data;
              });
            })
            .catch(error => {
              console.error('Error:', error);
            });
        } else {
          selectsUnidad.forEach(select => {
            select.innerHTML = '<option value="">---</option>';
          });
        }
      });

      // Al hacer clic en el botón guardar del modal
      $('#btnGuardarLab').click(function() {
        var nombre = $('#nombre_lab_nuevo').val();

        if (nombre.trim() === "") {
          mostrarAviso("Por favor ingrese un nombre");
          return;
        }

        $.ajax({
          url: '../../cfg/ajax/guardar_laboratorio.php', // Ruta donde crearás el PHP
          type: 'POST',
          data: {
            nombre: nombre
          },
          success: function(response) {
            if (response != "error") {
              // 1. Cerramos modal
              $('#modalNuevoLaboratorio').modal('hide');
              // 2. Limpiamos input
              $('#nombre_lab_nuevo').val('');
              // 3. Agregamos el nuevo lab al select y lo seleccionamos
              $('#laboratorio').append('<option value="' + response + '" selected>' + nombre + '</option>');
              mostrarAviso("Laboratorio guardado correctamente");
            } else {
              mostrarAviso("Error al guardar el laboratorio");
            }
          }
        });
      });

      // 1. Cargar lista al abrir el modal
      $('#modalCopiarMedicamento').on('shown.bs.modal', function() {
        cargarPlantillas('');
      });

      // 2. Buscador en tiempo real
      $('#buscar_plantilla').on('keyup', function() {
        cargarPlantillas($(this).val());
      });

      function cargarPlantillas(filtro) {
        $.ajax({
          url: 'get/get_plantilla_medicamentos.php',
          method: 'POST',
          data: {
            busqueda: filtro
          },
          success: function(html) {
            $('#lista_medicamentos_plantilla').html(html);
          }
        });
      }

      // 3. Función para COPIAR los datos al formulario
      $(document).on('click', '.btn-copiar-datos', function() {
        const d = $(this).data();

        // Llenar campos básicos
        $('#medicamento').val(d.nombre);
        $('#contenido_neto').val(d.contenido);
        $('#via_aplicacion').val(d.via);
        $('#almacenamiento').val(d.almacenamiento);
        $('#laboratorio').val(d.id_lab);
        $('#excipientes').val(d.excipientes);
        $('#codigo_barras').val(d.codigo);
        $('#presentacion').val(d.id_presentacion);

        // PASO CLAVE: Cargar unidades antes de construir las filas
        if (d.id_presentacion) {
          fetch('../../cfg/ajax/obtener_unidades_medicamentos.php?id=' + d.id_presentacion)
            .then(response => response.text())
            .then(htmlUnidades => {
              // Actualizar todos los selects de unidades
              $('.uni-pa').html(htmlUnidades);

              if (d.composicion) {
                $('#composicion_detallada').val(d.composicion);
                $('#contenedor_filas_principios').empty();

                const filas = d.composicion.split('|');
                const nombres = d.nombres_pa.split('|');

                filas.forEach((fila, index) => {
                  const [id_pa, cant, id_uni] = fila.split(',');
                  const nombrePA = nombres[index];

                  let nuevaFila = `
                        <div class="row fila-pa" style="margin-bottom: 10px;">
                          <div class="col-sm-6">
                            <select class="form-control select-pa">
                                <option value="${id_pa}" selected data-nombre="${nombrePA}">${nombrePA}</option>
                            </select>
                          </div>
                          <div class="col-sm-2">
                            <input type="text" class="form-control cant-pa" value="${cant}">
                          </div>
                          <div class="col-lg-2">
                            <select class="form-control uni-pa">${htmlUnidades}</select>
                          </div>
                          <div class="col-sm-2">
                            <button type="button" class="btn btn-danger btn-remove-pa"><i><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></i></button>
                          </div>
                        </div>`;

                  $('#contenedor_filas_principios').append(nuevaFila);
                  // Asignar la unidad específica a esta fila
                  $('#contenedor_filas_principios .fila-pa:last .uni-pa').val(id_uni);
                });

                // Actualizar UI del botón
                const textoResumen = nombres.join(', ');
                $('#btn_modal_pa').attr('data-original-title', textoResumen).tooltip('fixTitle');
                $('#resumen_principios').html("<strong>Incluye:</strong> " + textoResumen);
              }
            });
        }

        $('#modalCopiarMedicamento').modal('hide');
      });

      // =====================================================================
      // LÓGICA DE VERIFICACIÓN AJAX (CONEXIÓN A BD REAL)
      // =====================================================================
      function verificarMedicamentoYEnviar() {
        const nombre = $('#medicamento').val().trim();
        const id_presentacion = $('#presentacion').val();
        const codigo_barras = $('#codigo_barras').val().trim();
        const btnGuardar = $('#confirmarGuardar');

        const textoOriginal = btnGuardar.text();
        btnGuardar.text('Verificando...').attr('disabled', true);

        $.ajax({
          url: 'get/verificar_existencia_medicamento.php',
          method: 'POST',
          dataType: 'json',
          data: {
            nombre: nombre,
            id_presentacion: id_presentacion,
            codigo_barras: codigo_barras
          },
          success: function(response) {
            limpiarErrores();
            btnGuardar.text(textoOriginal).attr('disabled', false);

            if (response.existe_duplicado) {
              let mensaje = "";

              if (response.tipo_error === 'codigo') {
                mensaje = `⚠️ El código de barras ya está registrado para el medicamento: <b>${response.detalle}</b>.`;
                $('#group_codigo_barras').addClass('has-error');
              } else {
                mensaje = `⚠️ Ya existe un registro de <b>${nombre}</b> con esa misma presentación.`;
                $('#group_nombre, #group_presentacion').addClass('has-error');
              }

              mostrarAviso('🛑 Error de Duplicidad:<br>' + mensaje);
            } else {
              // Si todo está bien, enviamos el formulario
              $('#formularioMedicamento').off('submit').submit();
            }
          },
          error: function() {
            btnGuardar.text(textoOriginal).attr('disabled', false);
            mostrarAviso('🛑 Error de Servidor: No se pudo verificar la base de datos.');
          }
        });
      }

      // 3. ENVÍO DEL FORMULARIO
      $('#formularioMedicamento').on('submit', function(e) {
        e.preventDefault();
        limpiarErrores();
        let errores = [];

        if ($('#medicamento').val().trim() === "") {
          errores.push("Falta el nombre del medicamento.");
          $('#group_nombre').addClass('has-error');
        }

        if ($('#proveedor').val().trim() === "") {
          errores.push("Falta el nombre del proveedor.");
          $('#group_proveedor').addClass('has-error');
        }


        if ($('#presentacion').val().trim() === "") {
          errores.push("Falta la presentacion del medicamento.");
          $('#group_presentacion').addClass('has-error');
        }

        // Validación de los principios activos (revisando el campo oculto que llena el modal)
        if ($('#composicion_detallada').val().trim() === "") {
          errores.push("Debe gestionar al menos un principio activo en el modal.");
          $('#group_principio_activo').addClass('has-error');
        }

        if ($('#contenido_neto').val().trim() === "") {
          errores.push("Falta el contenido neto del medicamento.");
          $('#group_contenido_neto').addClass('has-error');
        }

        if ($('#via_aplicacion').val().trim() === "") {
          errores.push("Falta el tipo de aplicacion.");
          $('#group_via').addClass('has-error');
        }

        if ($('#almacenamiento').val().trim() === "") {
          errores.push("Falta el tipo de almacenamiento.");
          $('#group_almacenamiento').addClass('has-error');
        }

        if (errores.length > 0) {
          mostrarAviso('⚠️ Errores: <ul><li>' + errores.join('</li><li>') + '</li></ul>');
        } else {
          $('#modalGuardar').modal('show');
        }
      });

      $('#confirmarGuardar').on('click', function() {
        $('#modalGuardar').modal('hide');

        verificarMedicamentoYEnviar()
      });

      // --- Aplicar validaciones a campos de solo texto ---
      const campos = [document.getElementById("medicamento")];
      campos.forEach(campo => {
        if (campo) {
          campo.addEventListener("keydown", bloquearNumeros);
          campo.addEventListener("input", limpiarNumeros);
        }
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

      // =====================================================================
      // LÓGICA PARA AUTO-CARGAR PRINCIPIOS ACTIVOS AL DUPLICAR
      // =====================================================================
      <?php if (isset($_GET['duplicar_id']) && $datos_d) : ?>
          (function() {
            const principiosADuplicar = <?php echo $principios_json; ?>;
            const idPresentacion = '<?php echo $datos_d['Id_presentacion']; ?>';

            if (principiosADuplicar.length > 0 && idPresentacion) {
              // 1. Primero obtenemos las unidades de medida correspondientes a la presentación
              fetch('../../cfg/ajax/obtener_unidades_medicamentos.php?id=' + idPresentacion)
                .then(response => response.text())
                .then(htmlUnidades => {

                  // 2. Limpiamos el contenedor de filas del modal
                  $('#contenedor_filas_principios').empty();

                  // 3. Reconstruimos cada fila
                  principiosADuplicar.forEach(pa => {
                    let nuevaFila = `
                  <div class="row fila-pa" style="margin-bottom: 10px;">
                    <div class="col-sm-6">
                      <select class="form-control select-pa">
                        <option value="${pa.id_principio_activo}" selected data-nombre="${pa.nombre}">${pa.nombre}</option>
                      </select>
                    </div>
                    <div class="col-sm-2">
                      <input type="text" class="form-control cant-pa" value="${pa.cantidad_unidad_medida}">
                    </div>
                    <div class="col-lg-2">
                      <select class="form-control uni-pa">${htmlUnidades}</select>
                    </div>
                    <div class="col-sm-2">
                      <button type="button" class="btn btn-danger btn-remove-pa"><i><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></i></button>
                    </div>
                  </div>`;

                    $('#contenedor_filas_principios').append(nuevaFila);

                    // Asignamos la unidad de medida específica a esta fila recién creada
                    $('#contenedor_filas_principios .fila-pa:last .uni-pa').val(pa.id_tipo_unidad_medida);
                  });

                  // 4. Disparamos el clic en "Listo" para que se actualice el tooltip y el campo oculto
                  $('#guardar_pa_temp').click();
                });
            }
          })();
      <?php endif; ?>
    });
  </script>
</body>

</html>