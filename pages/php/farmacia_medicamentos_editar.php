<?php
include("../../cfg/conexion.php");
$id_url = isset($_GET['Id']) ? (int)$_GET['Id'] : 0;

// Consulta extendida para traer Laboratorio, Tipo y Código de barras
$sql = "SELECT m.*, dm.* FROM medicamento m 
        JOIN descripcion_medicamento dm ON m.Id_medicamento = dm.Id_medicamento 
        WHERE dm.Id = $id_url";
$resultado = $conexion->query($sql);
$row = $resultado->fetch_assoc();

if (!$row) die("Error: Registro no encontrado.");

// --- LÓGICA DE PRINCIPIOS ACTIVOS (Movida aquí arriba) ---
$resumen_tooltip = [];
$datos_hidden = [];

$sql_actuales = $conexion->query("SELECT * FROM detalle_principio_medicamento WHERE id_medicamento = " . $row['Id']);

if ($sql_actuales->num_rows > 0) {
  while ($temp = $sql_actuales->fetch_assoc()) {
    $id_p = $temp['id_principio_activo'];
    $nom_p = $conexion->query("SELECT nombre FROM principio_activo WHERE id_principio_activo = $id_p")->fetch_assoc()['nombre'];

    $id_u = $temp['id_tipo_unidad_medida'];
    $uni_p = $conexion->query("SELECT unidad FROM unidad_medida WHERE Id_unidad_medida = $id_u")->fetch_assoc()['unidad'];

    $resumen_tooltip[] = $nom_p . " " . $temp['cantidad_unidad_medida'] . " " . $uni_p;
    $datos_hidden[] = $id_p . "," . $temp['cantidad_unidad_medida'] . "," . $id_u;
  }
  // Devolvemos el puntero al inicio para que el bucle del modal funcione más abajo
  $sql_actuales->data_seek(0);
}

$texto_tooltip = !empty($resumen_tooltip) ? implode(', ', $resumen_tooltip) : 'Ninguno seleccionado';
$valor_hidden = !empty($datos_hidden) ? implode('|', $datos_hidden) : '';
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Medicamentos | Editar</title>
  <?php
  include('includes/headerNav2.php');
  ?>
  <style>
    /* Manteniendo tus animaciones y estilos originales */
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
    #modalRegresar,
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

    .has-error input[type="text"],
    .has-error select,
    .has-error #tipo_unidad_medida,
    .has-error #tipo,
    .has-error #via_aplicacion,
    .has-error #presentacion,
    .has-error #almacenamiento,
    .has-error .select-pa,
    .input-error {
      border: 2px solid crimson !important;
      box-shadow: 0 0 5px crimson;
    }

    .tooltip-inner {
      max-width: 300px;
      background-color: #3c8dbc !important;
      color: white;
      font-weight: bold;
      border: 1px solid #fff;
    }

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
      <h1>Editar Medicamento</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-archive"></i>Medicamento</a></li>
        <li class="active">Editar</li>
      </ol>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#tab_1" data-toggle="tab">Detalles de La Operación</a></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane active" id="tab_1">
                <div class="box-body">
                  <form id="formularioMedicamento" style="margin-bottom:14%;" method="POST" action="../../cfg/editar/editar_medicamento.php" novalidate>
                    <?php
                    include("../../cfg/conexion.php");
                    $id_url = isset($_GET['Id']) ? (int)$_GET['Id'] : 0;

                    // Consulta extendida para traer Laboratorio, Tipo y Código de barras
                    $sql = "SELECT m.*, dm.* FROM medicamento m 
                            JOIN descripcion_medicamento dm ON m.Id_medicamento = dm.Id_medicamento 
                            WHERE dm.Id = $id_url";
                    $resultado = $conexion->query($sql);
                    $row = $resultado->fetch_assoc();

                    if (!$row) die("Error: Registro no encontrado.");
                    ?>

                    <input type="hidden" name="Id" value="<?= $row['Id']; ?>">
                    <input type="hidden" name="Id_medicamento" value="<?= $row['Id_medicamento']; ?>">

                    <div class="col-sm-4 form-group" id="group_nombre">
                      <p>Nombre (*):</p>
                      <input id="medicamento" name="medicamento" class="form-control" type="text" maxlength="100" required value="<?= $row['nombre_medicamento']; ?>">
                    </div>

                    <div class="col-sm-4 form-group" id="group_tipo">
                      <p>Tipo (*):</p>
                      <select class="form-control" name="tipo" id="tipo" required>
                      <option selected value="">Seleccione el Tipo de Medicamento</option>
                        <?php
                        $sql_t = $conexion->query("SELECT * FROM tipo_medicamento");
                        while ($r_t = $sql_t->fetch_assoc()) {
                          $sel = ($r_t['Id_tipo'] == $row['Id_tipo']) ? 'selected' : '';
                          echo "<option value='" . $r_t['Id_tipo'] . "' $sel>" . $r_t['nombre_tipo'] . "</option>";
                        }
                        ?>
                      </select>
                    </div>

                    <div class="col-sm-3 form-group" id="group_principio_activo">
                      <p>Principios activos (*):</p>
                      <button type="button" class="btn btn-info btn-block" id="btn_modal_pa" data-toggle="modal" data-target="#modalPrincipios" title="<?= $texto_tooltip ?>" data-original-title="<?= $texto_tooltip ?>">
                        Gestionar Principios Activos
                      </button>
                    </div>
                    <input type="hidden" name="composicion_detallada" id="composicion_detallada" value="<?= $valor_hidden ?>" required>

                    <div class="clearfix"></div><br>

                    <div class="col-sm-4 form-group" id="group_presentacion">
                      <p>Presentación (*):</p>
                      <input id="presentacion" name="presentacion" class="form-control" type="text" required value="<?= $row['presentacion']; ?>">
                    </div>

                    <div class="col-sm-4 form-group" id="group_via">
                      <p>Vía de aplicación (*):</p>
                      <select name="via" id="via_aplicacion" class="form-control">
                      <option value="">Selecione una Via de Aplicacion</option>
                        <?php
                        $vias = ["Oral", "Sublingual", "Rectal", "Intravenosa", "Intramuscular", "Subcutanea", "Intradermica", "Topica", "Transdermica", "Inhalatoria", "Oftalmica", "Otica", "Nasal", "Vaginal"];
                        foreach ($vias as $v) {
                          $sel = ($row['via_aplicacion'] == $v) ? 'selected' : '';
                          echo "<option value='$v' $sel>$v</option>";
                        }
                        ?>
                      </select>
                    </div>

                    <div class="col-sm-3 form-group" id="group_almacenamiento">
                      <p>C. Almacenamiento (*):</p>
                      <select name="almacenamiento" id="almacenamiento" class="form-control" required>
                      <option value="">Seleccione una Condicion</option>
                        <option value="-25_a_-10" <?= ($row['almacenamiento'] == '-25_a_-10') ? 'selected' : '' ?>>Congelación (-25°C a -10°C)</option>
                        <option value="2_a_8" <?= ($row['almacenamiento'] == '2_a_8') ? 'selected' : '' ?>>Refrigeración (2°C a 8°C)</option>
                        <option value="8_a_15" <?= ($row['almacenamiento'] == '8_a_15') ? 'selected' : '' ?>>Lugar Fresco (8°C a 15°C)</option>
                        <option value="15_a_25" <?= ($row['almacenamiento'] == '15_a_25') ? 'selected' : '' ?>>Temperatura Ambiente (15°C a 25°C)</option>
                        <option value="max_30" <?= ($row['almacenamiento'] == 'max_30') ? 'selected' : '' ?>>Temperatura Maxima (30°C)</option>
                      </select>
                    </div>

                    <div class="clearfix"></div><br>

                    <div class="col-sm-4" id="group_laboratorio">
                      <p>Laboratorio:</p>
                      <div class="input-group">
                        <select id="laboratorio" name="laboratorio" class="form-control">
                          <option value="">Seleccione un Laboratorio</option>
                          <?php
                          $sql_l = $conexion->query("SELECT * FROM laboratorio");
                          while ($r_l = $sql_l->fetch_assoc()) {
                            $sel = ($r_l['Id_laboratorio'] == $row['Id_laboratorio']) ? 'selected' : '';
                            echo "<option value='" . $r_l['Id_laboratorio'] . "' $sel>" . $r_l['nombre_laboratorio'] . "</option>";
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

                    <div class="col-sm-4 form-group">
                      <p>Composición:</p>
                      <input type="text" name="composicion" class="form-control" value="<?= $row['composicion'] ?>">
                    </div>

                    <div class="col-sm-3 form-group">
                      <p>Código de Barras:</p>
                      <input type="text" name="codigo_barras" class="form-control" value="<?= $row['codigo_barras'] ?>">
                    </div>

                    <div class="col-sm-12" style="margin-top: 2%;">
                      <div style="float:right;">
                        <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#modalRegresar">Regresar</button>
                        <button type="submit" class="btn btn-success" id="btnGuardar">Actualizar</button>
                      </div>
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
          <h4 class="modal-title">Editar Principios Activos</h4>
        </div>
        <div class="modal-body">
          <div id="contenedor_filas_principios">
            <?php
            if ($sql_actuales->num_rows > 0) {
              while ($pa = $sql_actuales->fetch_assoc()) {
            ?>
                <div class="row fila-pa" style="margin-bottom: 10px;">
                  <div class="col-sm-6">
                    <select class="form-control select-pa">
                    <option value="" id="pa">Seleccione un Principio Activo</option>
                      <?php
                      $sql_p = $conexion->query("SELECT * FROM principio_activo");
                      while ($rp = $sql_p->fetch_assoc()) {
                        // Corrección del nombre de la columna aquí: id_principio_activo
                        $s = ($rp['id_principio_activo'] == $pa['id_principio_activo']) ? 'selected' : '';
                        echo "<option value='" . $rp['id_principio_activo'] . "' data-nombre='" . $rp['nombre'] . "' $s>" . $rp['nombre'] . "</option>";
                      }
                      ?>
                    </select>
                  </div>
                  <div class="col-sm-2">
                    <input type="text" class="form-control cant-pa" id="u_medida" value="<?= $pa['cantidad_unidad_medida'] ?>" placeholder="Cant.">
                  </div>
                  <div class="col-sm-2">
                    <select class="form-control uni-pa" id="tipo_unidad_medida" data-unidad-actual="<?= $pa['id_tipo_unidad_medida'] ?>"></select>
                  </div>
                  <div class="col-sm-2">
                    <button type="button" class="btn btn-danger btn-remove-pa"><i><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></i></button>
                  </div>
                </div>
              <?php
              }
            } else {
              // Si no hay ninguno, mostrar una fila vacía base
              ?>
              <div class="row fila-pa" style="margin-bottom: 10px;">
                <div class="col-sm-6">
                  <select class="form-control select-pa">
                    <option value="">Seleccione un Principio Activo</option>
                    <?php
                    $sql_p = $conexion->query("SELECT * FROM principio_activo");
                    while ($rp = $sql_p->fetch_assoc()) {
                      echo "<option value='" . $rp['id_principio_activo'] . "' data-nombre='" . $rp['nombre'] . "'>" . $rp['nombre'] . "</option>";
                    }
                    ?>
                  </select>
                </div>
                <div class="col-sm-2">
                  <input type="text" class="form-control cant-pa" placeholder="Cant.">
                </div>
                <div class="col-sm-2">
                  <select class="form-control uni-pa" data-unidad-actual=""></select>
                </div>
                <div class="col-sm-2">
                  <button type="button" class="btn btn-danger btn-remove-pa"><i></i></button>
                </div>
              </div>
            <?php } ?>
          </div>
          <button type="button" class="btn btn-success btn-sm" id="btn_add_pa"><i class="fa fa-plus"></i> Añadir otro</button>
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

  <div class="modal" id="modalRegresar" tabindex="-1" role="dialog">
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

  <?php include('includes/footer.php'); ?>

  <script>
    $(document).ready(function() {
      // Capturamos el ID de tipo inicial para cargar las unidades
      const tipoInicial = $("#tipo").val();

      // --- FUNCIONES CORE ---
      function mostrarAviso(mensaje) {
        $('#avisoTexto').html(mensaje);
        $('#avisoModal').modal('show');
      }

      function limpiarErrores() {
        $('.form-group').removeClass('has-error');
        $('.form-control').removeClass('input-error');
      }

      function closeCustomModal(modalElement) {
        modalElement.removeClass('in').addClass('out');
        setTimeout(() => {
          modalElement.modal('hide').removeClass('out');
        }, 100); // Duración de la animación
      }

      // CORRECCIÓN: Eventos para cerrar el modal de aviso
      $('#avisoModal .close, #avisoModal .btn-secondary').on('click', function() {
        closeCustomModal($('#avisoModal'));
      });

      $('#modalGuardar .close, #modalGuardar .btn-secondary').on('click', function() {
        closeCustomModal($('#modalGuardar'));
      });

      // --- LÓGICA DE PRINCIPIOS ACTIVOS (Igual a agregar.php) ---
      $('#btn_add_pa').on('click', function() {
        var nuevaFila = $('.fila-pa:first').clone();
        nuevaFila.find('input').val('');
        nuevaFila.find('select').val('');
        $('#contenedor_filas_principios').append(nuevaFila);
      });

      $('#contenedor_filas_principios').on('click', '.btn-remove-pa', function() {
        if ($('.fila-pa').length > 1) $(this).closest('.fila-pa').remove();
      });

      // Guardar composición en el input hidden
      $('#guardar_pa_temp').on('click', function() {
        var resumen = [];
        var datos_para_db = [];

        $('.fila-pa').each(function() {
          var nombre = $(this).find('.select-pa option:selected').data('nombre');
          var id_pa = $(this).find('.select-pa').val();
          var cantidad = $(this).find('.cant-pa').val();
          var unidad = $(this).find('.uni-pa option:selected').text();
          var id_unidad = $(this).find('.uni-pa').val();

          if (id_pa && cantidad && id_unidad) {
            resumen.push(nombre + " " + cantidad + " " + unidad);
            datos_para_db.push(id_pa + "," + cantidad + "," + id_unidad);
          }
        });

        $('#composicion_detallada').val(datos_para_db.join('|'));
        $('#btn_modal_pa').attr('data-original-title', resumen.join(', ') || 'Ninguno seleccionado').tooltip('fixTitle');
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

      // Cargar unidades por AJAX según el Tipo (Igual a agregar.php)
      function actualizarUnidades(idTipo) {
        if (idTipo) {
          fetch('../../cfg/ajax/obtener_unidades_medicamentos.php?id=' + idTipo)
            .then(r => r.text())
            .then(data => {
              $('.uni-pa').each(function() {
                // 1. Insertamos las opciones que trajo el AJAX
                $(this).html(data);

                // 2. Recuperamos el ID que guardamos en el atributo data-unidad-actual
                const unidadGuardada = $(this).data('unidad-actual');

                // 3. Si existe un valor guardado, lo seleccionamos automáticamente
                if (unidadGuardada) {
                  $(this).val(unidadGuardada);
                }
              });
            })
            .catch(error => console.error('Error al cargar unidades:', error));
        }
      }

      $('#tipo').on('change', function() {
        actualizarUnidades($(this).val());
      });

      // Inicialización
      actualizarUnidades(tipoInicial);
      $('#btn_modal_pa').tooltip();

      // --- VALIDACIONES DE CAMPOS ---
      $(document).on('input', '#medicamento', function() {
        this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '').substring(0, 50);
      });

      $(document).on('input', '.cant-pa', function() {
        this.value = this.value.replace(/[^0-9.]/g, '');
      });

      // --- ENVÍO DEL FORMULARIO ---
      $('#formularioMedicamento').on('submit', function(e) {
        e.preventDefault();
        limpiarErrores();
        let errores = [];

        if ($('#medicamento').val().trim() === "") {
          errores.push("Falta el nombre del medicamento.");
          $('#group_nombre').addClass('has-error');
        }

        if ($('#tipo').val().trim() === "") {
          errores.push("Falta el tipo de medicamento.");
          $('#group_tipo').addClass('has-error');
        }

        // Validación de los principios activos (revisando el campo oculto que llena el modal)
        if ($('#composicion_detallada').val().trim() === "") {
          errores.push("Debe gestionar al menos un principio activo en el modal.");
          $('#group_principio_activo').addClass('has-error');
        }

        if ($('#u_medida').val().trim() === "") {
          errores.push("Falta la cantidad del medicamento.");
          $('#group_unidad').addClass('has-error');
        }

        if ($('#tipo_unidad_medida').val().trim() === "") {
          errores.push("Falta el tipo de unidad de medida.");
          $('#group_tipo_unidad').addClass('has-error');
        }

        if ($('#presentacion').val().trim() === "") {
          errores.push("Falta la presentacion del medicamento.");
          $('#group_presentacion').addClass('has-error');
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
        $('#formularioMedicamento').off('submit').submit();
      });
    });
  </script>
</body>

</html>