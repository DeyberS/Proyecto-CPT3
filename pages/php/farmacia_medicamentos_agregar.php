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

  $sql_pat = "SELECT d.id_patologia, p.nombre_patologia 
            FROM detalle_patologia_medicamento d
            JOIN patologias p ON d.id_patologia = p.Id_patologia
            WHERE d.id_medicamento = '$id_url'";

  $res_pat = $conexion->query($sql_pat);
  $pat_parts = [];
  $lista_pat_full = [];

  while ($r_pat = $res_pat->fetch_assoc()) {
    $pat_parts[] = $r_pat['id_patologia'];
    $lista_pat_full[] = $r_pat;
  }

  $patologias_string = implode('|', $pat_parts);
  $patologias_json = json_encode($lista_pat_full);
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
    .has-error #cantidad_concentracion,
    .has-error #tipo_concentracion,
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
                    <div class="col-sm-3 form-group" id="group_via">
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

                    <br><br><br><br>
                    <label class="control-label"></label>
                    <div class="col-sm-4 form-group" id="group_contenido_neto">
                      <p>Contenido neto (*):</p>
                      <input id="contenido_neto" name="contenido_neto" class="form-control" type="text" value="<?php echo $datos_d['contenido_neto'] ?? ''; ?>" maxlength="100" placeholder="Ej. Capsulas de 20mg">
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-4" id="group_concentracion">
                      <p>Concentración:</p>
                      <div class="input-group">
                        <input type="text" class="form-control cantidad-pre" name="cantidad_concentracion" id="cantidad_concentracion" placeholder="Cant." value="<?php echo $datos_d['cantidad_concentracion'] ?? ''; ?>" inputmode="numeric">
                        <div class="input-group-btn" style="width: 60%;">
                          <select class="form-control uni-concentracion" name="tipo_concentracion" id="tipo_concentracion" required style="border-top-left-radius: 0; border-bottom-left-radius: 0; border-left: 0;">
                            <option selected value="">--- Primero seleccione una presentación --- </option>
                          </select>
                        </div>
                      </div>
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-3 form-group" id="group_almacenamiento">
                      <p>Condición de almacenamiento (*):</p>
                      <select name="almacenamiento" id="almacenamiento" class="form-control" required>
                        <option value="">--- Seleccione una condición ---</option>
                        <?php
                        // Definimos value => etiqueta para mantener la estructura limpia
                        $condiciones = [
                          "-25_a_-10" => "Congelación (-25°C a -10°C)",
                          "2_a_8"     => "Refrigeración (2°C a 8°C)",
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
                    <div class="col-sm-4 form-group" id="group_principio_activo">
                      <p>Principios activos (*):</p>
                      <button type="button" class="btn btn-info btn-block" id="btn_modal_pa" data-toggle="modal" data-placement="top" title="Ninguno seleccionado" data-target="#modalPrincipios">
                        <i></i> Gestionar Principios Activos
                      </button>
                    </div>
                    <input type="hidden" name="composicion_detallada" id="composicion_detallada" value="<?php echo $comp_string ?? ''; ?>" required>
                    <label class="control-label"></label>
                    <div class="col-sm-4 form-group" id="group_excipientes">
                      <p>Excipientes:</p>
                      <input type="text" id="excipientes" name="excipientes" value="<?php echo $datos_d['excipientes'] ?? ''; ?>" placeholder="Ej: Microcristalina celulosa, dióxido de titanio y gelatina." class="form-control">
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-3 form-group" id="group_patologia">
                      <p>Patologías asociadas:</p>
                      <button type="button" class="btn btn-info btn-block" id="btn_modal_pat" data-toggle="modal" data-placement="top" title="Ninguna seleccionada" data-target="#modal_pat">
                        <i></i> Gestionar Patologías Asociadas
                      </button>
                    </div>
                    <input type="hidden" name="patologias_seleccionadas" id="patologias_seleccionadas" value="<?php echo $patologias_string ?? ''; ?>">
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
                    <div class="col-sm-4 form-group" id="group_codigo_barras">
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

  <div class="modal fade" id="modal_pat" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: #3c8dbc; color: white;">
          <h4 class="modal-title">Agregar Patologías</h4>
        </div>
        <div class="modal-body">
          <div id="contenedor_filas_patologias">
          </div>
          <button type="button" class="btn btn-primary btn-sm" id="add_fila_pat">
            <i class="fa fa-plus"></i> Añadir otra
          </button>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success" id="guardar_pat_listo" data-dismiss="modal">Listo</button>
        </div>
      </div>
    </div>
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
                  <option selected value="">--- Primero seleccione una presentación ---</option>
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

      // Capturamos la unidad de concentración guardada (si estamos duplicando)
      const unidadConcentracionPrevia = '<?php echo $datos_d['id_tipo_concentracion'] ?? ''; ?>';

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

      $(document).on('input', '#codigo_barras', function() {
        let valor = this.value;
        valor = valor.replace(/[^0-9.]/g, '');
        const partes = valor.split('.');
        if (partes.length > 2) {
          valor = partes[0] + '.' + partes.slice(1).join('');
        }
        this.value = valor;
      });

      $(document).on('input', '.cantidad-pre', function() {
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
        if (window.unidadesDisponibles) {
          nuevaFila.find('.uni-pa').html(window.unidadesDisponibles);
        }
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

        // Usamos find para asegurar que buscamos dentro de CADA fila, sin importar cómo se creó
        $('.fila-pa').each(function() {
          var $fila = $(this);
          var id_pa = $fila.find('.select-pa').val();
          var nombre = $fila.find('.select-pa option:selected').data('nombre') || $fila.find('.select-pa option:selected').text();
          var cantidad = $fila.find('.cant-pa').val();
          var id_unidad = $fila.find('.uni-pa').val();
          var nombre_unidad = $fila.find('.uni-pa option:selected').text();

          if (id_pa && cantidad && id_unidad) {
            resumen.push(nombre.trim() + " " + cantidad + " " + nombre_unidad.trim());
            datos_para_db.push(id_pa + "," + cantidad + "," + id_unidad);
          }
        });

        // Actualizar el input oculto que va al PHP
        $('#composicion_detallada').val(datos_para_db.join('|'));

        // Actualizar visualmente el botón
        var boton = $('#btn_modal_pa');
        if (resumen.length > 0) {
          boton.attr('data-original-title', resumen.join(', ')).tooltip('fixTitle');
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
        $('#cantidad_concentracion').val(d.cantidad_c);

        const unidadASeleccionar = d.tipo_c;


        // PASO CLAVE: Cargar unidades antes de construir las filas
        if (d.id_presentacion) {
          fetch('../../cfg/ajax/obtener_unidades_medicamentos.php?id=' + d.id_presentacion)
            .then(response => response.text())
            .then(htmlUnidades => {

              $('.uni-pa, .uni-concentracion').html(htmlUnidades);


              $('#tipo_concentracion').val(unidadASeleccionar);

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

        if (d.patologias) {
          $('#contenedor_filas_patologias').empty();
          // Convertimos a string por si viene como número y separamos
          const idsPat = String(d.patologias).split('|');
          const nombresPat = d.nombres_pat ? String(d.nombres_pat).split('|') : [];

          idsPat.forEach(id => {
            // Llamamos a la función global que ya tienes definida en la línea 545
            if (id) agregarFilaPatologia(id);
          });

          // Actualizar Tooltip
          let textoTooltip = nombresPat.length > 0 ? nombresPat.join(', ') : 'Patologías seleccionadas';
          $('#btn_modal_pat').attr('data-original-title', textoTooltip).tooltip('fixTitle');
        }


        $('#modalCopiarMedicamento').modal('hide');
      });


      const presentacionCargada = $('#presentacion').val();
      const unidadCargada = '<?php echo $datos_d['Id_tipo_concentracion'] ?? $datos_d['id_tipo_concentracion'] ?? $datos_d['tipo_concentracion'] ?? $datos_d['id_tipo_unidad_medida'] ?? ''; ?>';

      function inicializarUnidades(idPres, unidadSel) {
        if (idPres) {
          $.ajax({
            url: '../../cfg/ajax/obtener_unidades_medicamentos.php?id=' + idPres,
            success: function(data) {
              window.unidadesDisponibles = data;
              $('.uni-pa, .uni-concentracion').html(data);

              if (unidadSel && unidadSel !== "") {
                $('#tipo_concentracion').val(unidadSel);
              }
            }
          });
        }
      }

      if (presentacionCargada) {
        inicializarUnidades(presentacionCargada, unidadCargada);
      }

      $('#presentacion').on('change', function() {
        inicializarUnidades($(this).val(), "");
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

        if ($('#presentacion').val().trim() === "") {
          errores.push("Falta la presentacion del medicamento.");
          $('#group_presentacion').addClass('has-error');
        }

        // Validación de los principios activos (revisando el campo oculto que llena el modal)
        if ($('#composicion_detallada').val().trim() === "") {
          errores.push("Debe gestionar al menos un principio activo en el modal.");
          $('#group_principio_activo').addClass('has-error');
        }

        if ($('#via_aplicacion').val().trim() === "") {
          errores.push("Falta el tipo de aplicacion.");
          $('#group_via').addClass('has-error');
        }

        if ($('#contenido_neto').val().trim() === "") {
          errores.push("Falta el contenido neto del medicamento.");
          $('#group_contenido_neto').addClass('has-error');
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
      // VALIDACIÓN PARA EVITAR DUPLICADOS (Principios Activos y Patologías)
      // =====================================================================

      // Validar Principios Activos duplicados
      $(document).on('change', '.select-pa', function() {
        let selectActual = $(this);
        let valorActual = selectActual.val();

        if (valorActual === "") return; // Ignorar si se selecciona la opción por defecto

        let conteo = 0;
        $('.select-pa').each(function() {
          if ($(this).val() === valorActual) {
            conteo++;
          }
        });

        if (conteo > 1) {
          mostrarAviso("⚠️ <b>Atención:</b> Este principio activo ya ha sido seleccionado en otra fila. Por favor, elija uno diferente o modifique la cantidad en el que ya agregó.");
          selectActual.val(""); // Resetea el select actual a su opción por defecto
        }
      });

      // Validar Patologías duplicadas
      $(document).on('change', '.select-pat', function() {
        let selectActual = $(this);
        let valorActual = selectActual.val();

        if (valorActual === "") return; // Ignorar si se selecciona la opción por defecto

        let conteo = 0;
        $('.select-pat').each(function() {
          if ($(this).val() === valorActual) {
            conteo++;
          }
        });

        if (conteo > 1) {
          mostrarAviso("⚠️ <b>Atención:</b> Esta patología ya ha sido seleccionada en esta lista. Por favor, elija una diferente.");
          selectActual.val(""); // Resetea el select actual a su opción por defecto
        }
      });

      // 1. Cargar patologías existentes (si se está duplicando)
      <?php if (isset($patologias_json)) : ?>
        let patsExistentes = <?php echo $patologias_json; ?>;
        let nombresPat = [];

        patsExistentes.forEach(p => {
          agregarFilaPatologia(p.id_patologia);

          // Ya traemos el nombre desde PHP en el JSON, lo usamos directamente (es más rápido y seguro)
          if (p.nombre_patologia) {
            nombresPat.push(p.nombre_patologia.trim());
          }
        });

        // Como el tooltip aún no se ha inicializado en este punto del script, 
        // simplemente inyectamos los atributos 'title' y 'data-original-title' listos.
        if (nombresPat.length > 0) {
          $('#btn_modal_pat').attr('title', nombresPat.join(', '));
          $('#btn_modal_pat').attr('data-original-title', nombresPat.join(', '));
        }
      <?php endif; ?>


      // 2. Función para añadir una fila nueva
      function agregarFilaPatologia(idSeleccionado = "") {
        let htmlPat = `
        <div class="row fila-pat" style="margin-bottom: 10px;">
            <div class="col-sm-10">
                <select class="form-control select-pat">
                    <option value="">--- Seleccione una patología ---</option>
                    <?php
                    $q = $conexion->query("SELECT Id_patologia, nombre_patologia FROM patologias WHERE estatus = 1 ORDER BY nombre_patologia ASC");
                    while ($p = $q->fetch_assoc()) {
                      echo "<option value='" . $p['Id_patologia'] . "'>" . $p['nombre_patologia'] . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-sm-2">
                <button type="button" class="btn btn-danger btn-remove-pat">
                    <i><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></i>
                </button>
            </div>
        </div>`;
        $('#contenedor_filas_patologias').append(htmlPat);
        if (idSeleccionado) {
          $('#contenedor_filas_patologias .fila-pat:last .select-pat').val(idSeleccionado);
        }
      }

      // 3. Abrir con una fila por defecto si está vacío
      $('#btn_modal_pat').click(function() {
        if ($('#contenedor_filas_patologias').children().length === 0) {
          agregarFilaPatologia();
        }
      });

      // Eventos de botones
      $('#add_fila_pat').click(() => agregarFilaPatologia());

      $(document).on('click', '.btn-remove-pat', function() {
        $(this).closest('.fila-pat').remove();
      });

      $('#btn_modal_pat').tooltip();

      // Guardar y actualizar resumen
      $('#guardar_pat_listo').click(function() {
        let ids = [];
        let nombres = [];

        $('.select-pat').each(function() {
          let val = $(this).val();
          // Solo procesamos si hay un valor seleccionado
          if (val && val !== "") {
            ids.push(val);
            let txt = $(this).find('option:selected').text();
            if (txt) {
              nombres.push(txt.trim());
            }
          }
        });

        $('#patologias_seleccionadas').val(ids.join('|'));

        if (ids.length > 0) {
          $('#btn_modal_pat').attr('data-original-title', nombres.join(', ')).tooltip('fixTitle');
        } else {
          $('#btn_modal_pat').attr('data-original-title', 'Ninguna seleccionada').tooltip('fixTitle');
        }
      });

      // --- NUEVO: Validar que el modal no abra vacío ---
      $('#btn_modal_pat').click(function() {
        // Si el contenedor no tiene ninguna fila adentro, agregamos una vacía automáticamente
        if ($('#contenedor_filas_patologias').children().length === 0) {
          agregarFilaPatologia();
        }
      });

      <?php if (isset($_GET['duplicar_id']) && $datos_d) : ?>
          (function() {
            const principiosADuplicar = <?php echo $principios_json; ?>;
            const idPresentacion = '<?php echo $datos_d['Id_presentacion']; ?>';

            // CLAVE: Capturamos el HTML de las opciones del select original
            // Este select (#principio_activo) ya fue cargado por PHP con todos los principios de la base de datos.
            const opcionesCatalogo = $('#principio_activo').html();

            if (principiosADuplicar.length > 0 && idPresentacion) {
              // 1. Obtenemos las unidades de medida
              fetch('../../cfg/ajax/obtener_unidades_medicamentos.php?id=' + idPresentacion)
                .then(response => response.text())
                .then(htmlUnidades => {

                  // 2. Limpiamos el contenedor
                  $('#contenedor_filas_principios').empty();

                  // 3. Reconstruimos cada fila con el catálogo COMPLETO
                  principiosADuplicar.forEach(pa => {
                    let nuevaFila = `
                    <div class="row fila-pa" style="margin-bottom: 10px;">
                      <div class="col-sm-6">
                        <select class="form-control select-pa">
                          ${opcionesCatalogo} 
                        </select>
                      </div>
                      <div class="col-sm-2">
                        <input type="text" class="form-control cant-pa" value="${pa.cantidad_unidad_medida}">
                      </div>
                      <div class="col-lg-2">
                        <select class="form-control uni-pa">${htmlUnidades}</select>
                      </div>
                      <div class="col-sm-2">
                        <button type="button" class="btn btn-danger btn-remove-pa">
                          <i><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></i>
                        </button>
                      </div>
                    </div>`;

                    $('#contenedor_filas_principios').append(nuevaFila);

                    // 4. Ahora sí podemos asignar los valores porque las opciones existen
                    let $ultima = $('#contenedor_filas_principios .fila-pa:last');
                    $ultima.find('.select-pa').val(pa.id_principio_activo);
                    $ultima.find('.uni-pa').val(pa.id_tipo_unidad_medida);
                  });

                  // 5. Forzamos la actualización de los tooltips y campos ocultos
                  $('#guardar_pa_temp').trigger('click');
                });
            }
          })();
      <?php endif; ?>
    });
  </script>
</body>

</html>