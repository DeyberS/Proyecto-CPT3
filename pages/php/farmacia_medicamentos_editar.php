<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

include("../../cfg/conexion.php");
$id_url = isset($_GET['Id']) ? (int)$_GET['Id'] : 0;

$sql = "SELECT m.*, dm.* FROM medicamento m 
      JOIN descripcion_medicamento dm ON m.Id_medicamento = dm.Id_medicamento 
      WHERE dm.Id = $id_url";

$resultado = $conexion->query($sql);
$row = $resultado ? $resultado->fetch_assoc() : null;

if (!$row) {
  $_SESSION['mensaje_user_error'] = "Debe seleccionar una consulta válida para ver su información.";
  header("Location: farmacia_medicamentos_listado.php");
  exit;
}

$resumen_tooltip = [];
$datos_hidden = [];
$sql_actuales = $conexion->query("SELECT * FROM detalle_principio_medicamento WHERE id_descripcion_medicamento = " . (int)$row['Id']);

if ($sql_actuales->num_rows > 0) {
  while ($temp = $sql_actuales->fetch_assoc()) {
    $id_p = $temp['id_principio_activo'];
    $nom_p = $conexion->query("SELECT nombre FROM principio_activo WHERE id_principio_activo = $id_p")->fetch_assoc()['nombre'];
    $id_u = $temp['id_tipo_unidad_medida'];
    $uni_p = $conexion->query("SELECT unidad FROM unidad_medida WHERE Id_unidad_medida = $id_u")->fetch_assoc()['unidad'];

    $resumen_tooltip[] = $nom_p . " " . $temp['cantidad_unidad_medida'] . " " . $uni_p;
    $datos_hidden[] = $id_p . "," . $temp['cantidad_unidad_medida'] . "," . $id_u;
  }
  $sql_actuales->data_seek(0);
}
$texto_tooltip = !empty($resumen_tooltip) ? implode(', ', $resumen_tooltip) : 'Ninguno seleccionado';
$valor_hidden = !empty($datos_hidden) ? implode('|', $datos_hidden) : '';

$patologias_ids = [];
$sql_pat = $conexion->query("SELECT Id_patologia FROM detalle_patologia_medicamento WHERE Id_descripcion_medicamento = " . (int)$row['Id']);
while ($p = $sql_pat->fetch_assoc()) {
  $patologias_ids[] = $p['Id_patologia'];
}
$valor_patologias = implode(',', $patologias_ids);

include('includes/headerNav2.php');
verificarAcceso('Editar Medicamentos');
?>

<head>
  <title>Medicamentos | Editar</title>
</head>

<style>
  .tooltip-inner {
    max-width: 300px;
    background-color: #3c8dbc !important;
    color: white;
    font-weight: bold;
    border: 1px solid #fff;
  }

  .tooltip.right .tooltip-arrow {
    border-right-color: #3c8dbc !important;
  }
</style>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Editar Medicamentos</h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="#"><i class="fa fa-archive"></i>Medicamentos</a></li>
      <li class="active">Editar</li>
    </ol>
  </section>

  <section class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="nav-tabs-custom">
          <ul class="nav nav-tabs">
            <li class="active"><a href="#tab_1" data-toggle="tab">Información del medicamento</a></li>
          </ul>
          <div class="tab-content">
            <div class="tab-pane active" id="tab_1">
              <div class="box-body">
                <form id="formularioMedicamento" style="margin-bottom:14%;" method="POST" action="../../cfg/editar/editar_medicamento.php" novalidate>
                  <?php
                  include("../../cfg/conexion.php");
                  $id_url = isset($_GET['Id']) ? (int)$_GET['Id'] : 0;

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

                  <div class="col-sm-4 form-group" id="group_presentacion">
                    <p>Presentación (*):</p>
                    <select class="form-control" name="presentacion" id="presentacion" required>
                      <option selected value="">--- Seleccione la presentación del medicamento ---</option>
                      <?php
                      $sql_t = $conexion->query("SELECT * FROM presentacion");
                      while ($r_t = $sql_t->fetch_assoc()) {
                        $sel = ($r_t['Id_presentacion'] == $row['Id_presentacion']) ? 'selected' : '';
                        echo "<option value='" . $r_t['Id_presentacion'] . "' $sel>" . $r_t['nombre_presentacion'] . "</option>";
                      }
                      ?>
                    </select>
                  </div>

                  <div class="col-sm-3 form-group" id="group_contenido_neto">
                    <p>Cantidad en la presentación (*):</p>
                    <input id="contenido_neto" name="contenido_neto" class="form-control" type="text" required value="<?= $row['contenido_neto']; ?>">
                  </div>

                  <div class="clearfix"></div>

                  <label class="control-label"></label>
                  <div class="col-sm-4" id="group_concentracion">
                    <p>Unidad de presentación:</p>
                    <div class="input-group">
                      <input type="text" class="form-control" name="cantidad_concentracion" id="cantidad_concentracion" placeholder="Cant." value="<?= htmlspecialchars($row['cantidad_concentracion']); ?>">

                      <div class="input-group-btn" style="width: 60%;">
                        <select class="form-control" name="tipo_concentracion" id="tipo_concentracion" data-unidad-actual="<?= $row['Id_tipo_concentracion']; ?>" required>
                          <option value="">--- Cargando ---</option>
                        </select>
                      </div>
                    </div>
                  </div>

                  <div class="col-sm-4 form-group" id="group_via">
                    <p>Vía de aplicación (*):</p>
                    <select name="via" id="via_aplicacion" class="form-control">
                      <option value="">--- Seleccione una vía de aplicación ---</option>
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
                    <p>Condición de almacenamiento (*):</p>
                    <select name="almacenamiento" id="almacenamiento" class="form-control" required>
                      <option value="">--- Seleccione una condición ---</option>
                      <option value="-25_a_-10" <?= ($row['almacenamiento'] == '-25_a_-10') ? 'selected' : '' ?>>Congelación (-25°C a -10°C)</option>
                      <option value="2_a_8" <?= ($row['almacenamiento'] == '2_a_8') ? 'selected' : '' ?>>Refrigeración (2°C a 8°C)</option>
                      <option value="8_a_15" <?= ($row['almacenamiento'] == '8_a_15') ? 'selected' : '' ?>>Lugar Fresco (8°C a 15°C)</option>
                      <option value="15_a_25" <?= ($row['almacenamiento'] == '15_a_25') ? 'selected' : '' ?>>Temperatura Ambiente (15°C a 25°C)</option>
                      <option value="max_30" <?= ($row['almacenamiento'] == 'max_30') ? 'selected' : '' ?>>Temperatura Máxima (30°C)</option>
                    </select>
                  </div>

                  <div class="clearfix"></div>
                  <div class="col-sm-4" id="group_laboratorio">
                    <p>Laboratorio:</p>
                    <div class="input-group">
                      <select id="laboratorio" name="laboratorio" class="form-control">
                        <option value="">--- Seleccione un laboratorio ---</option>
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
                  <div class="col-sm-4 form-group" id="group_principio_activo">
                    <p>Composición (*):</p>
                    <button type="button" class="btn btn-info btn-block" id="btn_modal_pa" data-toggle="modal" data-target="#modalPrincipios" title="<?= $texto_tooltip ?>" data-original-title="<?= $texto_tooltip ?>">
                      Gestionar Composición
                    </button>
                  </div>
                  <input type="hidden" name="composicion_detallada" id="composicion_detallada" value="<?= $valor_hidden ?>" required>
                  <label class="control-label"></label>
                  <div class="col-sm-3 form-group" id="group_patologia">
                    <p>Patologías asociadas:</p>
                    <button type="button" class="btn btn-info btn-block" id="btn_modal_pat" data-toggle="modal" data-placement="top" title="Ninguna seleccionada" data-target="#modal_pat">
                      <i></i> Gestionar Patologías Asociadas
                    </button>
                  </div>
                  <input type="hidden" name="patologias_seleccionadas" id="patologias_seleccionadas" value="<?php echo $valor_patologias; ?>" required>
                 <div class=" clearfix">
              </div>
              <div class="col-sm-4 form-group">
                <p>Código de Barras:</p>
                <input type="text" name="codigo_barras" class="form-control" value="<?= $row['codigo_barras'] ?>">
              </div>
              <div class="col-sm-12" style="margin-top: 4%;">
                <div style="float:right;">
                  <button type="button" class="btn btn-second" data-toggle="modal" data-target="#modalRegresar">Regresar</button>
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

<div class="modal fade" id="modal_pat" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info">
        <h4 class="modal-title">Agregar Patologías</h4>
      </div>
      <div class="modal-body">
        <div id="contenedor_filas_patologias">
        </div>
        <button type="button" class="btn btn-success btn-sm" id="add_fila_pat">
          <i class="fa fa-plus"></i> Añadir Otra Patología
        </button>
        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalNuevaPatologia">
          <i class="fa fa-plus-circle"></i> Nueva Patología
        </button>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="guardar_pat_listo" data-dismiss="modal">Listo</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalPrincipios" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info">
        <h4 class="modal-title">Editar Composición del Medicamento</h4>
      </div>
      <div class="modal-body">
        <div id="contenedor_filas_principios">
          <?php
          if ($sql_actuales->num_rows > 0) {
            while ($pa = $sql_actuales->fetch_assoc()) {
          ?>
              <div class="row fila-pa" style="margin-bottom: 10px;">
                <div class="col-sm-6">
                  <div class="input-group">
                    <select class="form-control select-pa">
                      <option value="" id="pa">--- Seleccione un principio activo ---</option>
                      <?php
                      $sql_p = $conexion->query("SELECT * FROM principio_activo");
                      while ($rp = $sql_p->fetch_assoc()) {
                        $s = ($rp['id_principio_activo'] == $pa['id_principio_activo']) ? 'selected' : '';
                        echo "<option value='" . $rp['id_principio_activo'] . "' data-nombre='" . $rp['nombre'] . "' $s>" . $rp['nombre'] . "</option>";
                      }
                      ?>
                    </select>
                    <span class="input-group-btn">
                      <button class="btn btn-info btn-search-pa" type="button" title="Buscar P.A.">
                        <i><img src="../../recursos/imagenes/iconos/buscar.png" style="width:10px; height:10px;"></i>
                      </button>
                    </span>
                  </div>
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
            ?>
            <div class="row fila-pa" style="margin-bottom: 10px;">
              <div class="col-sm-6">
                <select class="form-control select-pa">
                  <option value="">--- Seleccione un principio activo ---</option>
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
            <?php } ?>
            </div>
            <div class="col-sm-12 form-group" style="left:-10px;">
              <p>Excipientes:</p>
              <input type="text" name="excipientes" class="form-control" value="<?= $row['excipientes'] ?>">
            </div>
            <button type="button" class="btn btn-success btn-sm" id="btn_add_pa">
              <i class="fa fa-plus"></i> Añadir Otro Principio Activo
            </button>
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalNuevoPA">
              <i class="fa fa-plus-circle"></i> Nuevo Principio Activo
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
        <div class="modal-header bg-info">
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
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary" id="btnGuardarLab">Guardar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalBuscarPA" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
      <div class="modal-content">
        <div class="modal-header bg-info">
          <h4 class="modal-title">Buscar Principio Activo</h4>
        </div>
        <div class="modal-body">
          <input type="text" id="inputBuscarPA" class="form-control" placeholder="Escriba para filtrar...">
          <div class="list-group" id="listaResultadosPA" style="margin-top: 10px; max-height: 200px; overflow-y: auto;"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalBuscarPat" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
      <div class="modal-content">
        <div class="modal-header bg-info">
          <h4 class="modal-title">Buscar Patología</h4>
        </div>
        <div class="modal-body">
          <input type="text" id="inputBuscarPat" class="form-control" placeholder="Escriba para filtrar...">
          <div class="list-group" id="listaResultadosPat" style="margin-top: 10px; max-height: 200px; overflow-y: auto;"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalNuevoPA" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-info">
          <h4 class="modal-title">Añadir Nuevo Principio Activo</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Nombre (*):</label>
            <input type="text" id="nombre_pa_nuevo" class="form-control" placeholder="Ej. Paracetamol">
          </div>
          <div class="form-group">
            <label>Descripción:</label>
            <input type="text" id="desc_pa_nuevo" class="form-control" placeholder="Opcional">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-success" id="btnGuardarNuevoPA">Guardar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalNuevaPatologia" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-info">
          <h4 class="modal-title">Añadir Nueva Patología</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Nombre (*):</label>
            <input type="text" id="nombre_pat_nuevo" class="form-control">
          </div>
          <div class="form-group">
            <label>Código CIE (*):</label>
            <input type="text" id="cie_pat_nuevo" class="form-control" placeholder="Ej. A00">
          </div>
          <div class="form-group">
            <label>Contagioso (*):</label>
            <select id="contagioso_pat_nuevo" class="form-control">
              <option value="NO">NO</option>
              <option value="SI">SI</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-success" id="btnGuardarNuevaPat">Guardar</button>
        </div>
      </div>
    </div>
  </div>

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
          <a href="farmacia_medicamentos_listado.php" class="btn btn-danger">Abandonar Formulario</a>
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
          <p>¿Está seguro de que desea actualizar la información del medicamento?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-success" id="confirmarGuardar">Guardar</button>
        </div>
      </div>
    </div>
  </div>

  <?php include('includes/footer.php'); ?>

  <script>
    $(document).ready(function() {
      const tipoInicial = $("#presentacion").val();

      function mostrarAviso(mensaje) {
        $('#avisoTexto').html(mensaje);
        $('#avisoModal').modal('show');
      }

      function limpiarErrores() {
        $('.form-group').removeClass('input-error');
        $('.form-control').removeClass('input-error');
        $('#btn_modal_pa').removeClass('btn-error-sombreado');
      }

      $('#btn_add_pa').on('click', function() {
        var nuevaFila = $('.fila-pa:first').clone();
        nuevaFila.find('input').val('');
        nuevaFila.find('select').val('');
        nuevaFila.find('.uni-pa').attr('data-unidad-actual', '');

        $('#contenedor_filas_principios').append(nuevaFila);

        actualizarUnidades($("#presentacion").val());
      });

      $('#contenedor_filas_principios').on('click', '.btn-remove-pa', function() {
        if ($('.fila-pa').length > 1) $(this).closest('.fila-pa').remove();
      });

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

      $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();

        let patologias = $('#patologias_seleccionadas').val().split(',');
        patologias.forEach(function(id) {
          if (id !== "") {
            $('.checkbox-patologia[value="' + id + '"]').prop('checked', true);
          }
        });

        if ($('#composicion_detallada').val() !== "") {
          console.log("Composición cargada: " + $('#composicion_detallada').val());
        }
      });

      let selectDestinoTarget = null;

      $(document).on('click', '.btn-search-pa', function() {
        selectDestinoTarget = $(this).closest('.input-group').find('.select-pa');
        $('#modalBuscarPA').modal('show');
        $('#inputBuscarPA').val('').trigger('keyup');
      });

      $('#inputBuscarPA').on('keyup', function() {
        let texto = $(this).val().toLowerCase();
        let html = '';
        let opciones = $('.select-pa:first option').not('[value=""]');

        opciones.each(function() {
          let nombre = $(this).text();
          if (nombre.toLowerCase().includes(texto)) {
            html += `<a href="#" class="list-group-item list-group-item-action seleccionar-pa" data-id="${$(this).val()}">${nombre}</a>`;
          }
        });
        $('#listaResultadosPA').html(html);
      });

      $(document).on('click', '.seleccionar-pa', function(e) {
        e.preventDefault();
        selectDestinoTarget.val($(this).data('id')).trigger('change');
        $('#modalBuscarPA').modal('hide');
      });

      $(document).on('click', '.btn-search-pat', function() {
        selectDestinoTarget = $(this).closest('.input-group').find('.select-pat');
        $('#modalBuscarPat').modal('show');
        $('#inputBuscarPat').val('').trigger('keyup');
      });

      $('#inputBuscarPat').on('keyup', function() {
        let texto = $(this).val().toLowerCase();
        let html = '';
        let opciones = $('.select-pat:first option').not('[value=""]');

        opciones.each(function() {
          let nombre = $(this).text();
          if (nombre.toLowerCase().includes(texto)) {
            html += `<a href="#" class="list-group-item list-group-item-action seleccionar-pat" data-id="${$(this).val()}">${nombre}</a>`;
          }
        });
        $('#listaResultadosPat').html(html);
      });

      $(document).on('click', '.seleccionar-pat', function(e) {
        e.preventDefault();
        selectDestinoTarget.val($(this).data('id')).trigger('change');
        $('#modalBuscarPat').modal('hide');
      });

      $('#btnGuardarNuevoPA').click(function() {
        let nombre = $('#nombre_pa_nuevo').val();
        let desc = $('#desc_pa_nuevo').val();

        if (nombre.trim() === '') {
          mostrarAviso("Nombre obligatorio");
          return;
        }

        $.ajax({
          url: '../../cfg/ajax/guardar_principio_activo.php',
          type: 'POST',
          data: {
            nombre: nombre,
            descripcion: desc
          },
          success: function(response) {
            if (response !== "error") {
              $('#modalNuevoPA').modal('hide');
              let nuevaOpcion = `<option value="${response}" data-nombre="${nombre}">${nombre}</option>`;
              $('.select-pa').append(nuevaOpcion);
              $('#nombre_pa_nuevo, #desc_pa_nuevo').val('');
              mostrarAviso("Principio Activo guardado con éxito");
            } else {
              mostrarAviso("Error al guardar en DB");
            }
          }
        });
      });

      $('#btnGuardarNuevaPat').click(function() {
        let nombre = $('#nombre_pat_nuevo').val();
        let cie = $('#cie_pat_nuevo').val();
        let contagioso = $('#contagioso_pat_nuevo').val();

        if (nombre.trim() === '' || cie.trim() === '') {
          mostrarAviso("Nombre y CIE obligatorios");
          return;
        }

        $.ajax({
          url: '../../cfg/ajax/guardar_patologia.php',
          type: 'POST',
          data: {
            nombre: nombre,
            cie: cie,
            contagioso: contagioso
          },
          success: function(response) {
            if (response !== "error") {
              $('#modalNuevaPatologia').modal('hide');
              let nuevaOpcion = `<option value="${response}">${nombre}</option>`;
              $('.select-pat').append(nuevaOpcion);
              $('#nombre_pat_nuevo, #cie_pat_nuevo').val('');
              mostrarAviso("Patología guardada con éxito");
            } else {
              mostrarAviso("Error al guardar en DB (Posible código CIE duplicado)");
            }
          }
        });
      });

      $(document).on('change', '.select-pa', function() {
        let selectActual = $(this);
        let valorActual = selectActual.val();

        if (valorActual === "") return;

        let conteo = 0;
        $('.select-pa').each(function() {
          if ($(this).val() === valorActual) {
            conteo++;
          }
        });

        if (conteo > 1) {
          mostrarAviso("⚠️ <b>Atención:</b> Este principio activo ya ha sido seleccionado en otra fila. Por favor, elija uno diferente o modifique la cantidad en el que ya agregó.");
          selectActual.val("");
        }
      });

      $(document).on('change', '.select-pat', function() {
        let selectActual = $(this);
        let valorActual = selectActual.val();

        if (valorActual === "") return;

        let conteo = 0;
        $('.select-pat').each(function() {
          if ($(this).val() === valorActual) {
            conteo++;
          }
        });

        if (conteo > 1) {
          mostrarAviso("⚠️ <b>Atención:</b> Esta patología ya ha sido seleccionada en esta lista. Por favor, elija una diferente.");
          selectActual.val("");
        }
      });

      let patologiasExistentes = "<?= $valor_patologias; ?>".split(',');
      if (patologiasExistentes[0] !== "") {
        patologiasExistentes.forEach(id => {
          agregarFilaPatologia(id);
        });

        setTimeout(() => {
          let nombresInit = [];
          $('.select-pat').each(function() {
            let txt = $(this).find('option:selected').text();
            if (txt && $(this).val() !== "") nombresInit.push(txt.trim());
          });
          $('#btn_modal_pat').attr('data-original-title', nombresInit.join(', ')).tooltip('fixTitle');
        }, 500);
      }

      $('#btnGuardarLab').click(function() {
        var nombre = $('#nombre_lab_nuevo').val();

        if (nombre.trim() === "") {
          mostrarAviso("Por favor ingrese un nombre");
          return;
        }

        $.ajax({
          url: '../../cfg/ajax/guardar_laboratorio.php',
          type: 'POST',
          data: {
            nombre: nombre
          },
          success: function(response) {
            if (response != "error") {
              $('#modalNuevoLaboratorio').modal('hide');
              $('#nombre_lab_nuevo').val('');
              $('#laboratorio').append('<option value="' + response + '" selected>' + nombre + '</option>');
              mostrarAviso("Laboratorio guardado correctamente");
            } else {
              mostrarAviso("Error al guardar el laboratorio");
            }
          }
        });
      });

      function actualizarUnidades(idTipo) {
        if (idTipo) {
          fetch('../../cfg/ajax/obtener_unidades_medicamentos.php?id=' + idTipo)
            .then(r => r.text())
            .then(data => {
              $('.uni-pa, #tipo_concentracion').each(function() {
                $(this).html(data);

                const unidadGuardada = $(this).data('unidad-actual');

                if (unidadGuardada) {
                  $(this).val(unidadGuardada);
                }
              });
            })
            .catch(error => console.error('Error al cargar unidades:', error));
        }
      }

      $('#presentacion').on('change', function() {
        actualizarUnidades($(this).val());
      });

      actualizarUnidades(tipoInicial);
      $('#btn_modal_pa').tooltip();

      $(document).on('input', '#medicamento', function() {
        this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '').substring(0, 50);
      });

      $(document).on('input', '.cant-pa', function() {
        this.value = this.value.replace(/[^0-9.]/g, '');
      });

      $(document).on('input', '#stock_minimo', function() {
        this.value = this.value.replace(/[^0-9.]/g, '');
      });

      $(document).on('input', '#stock_maximo', function() {
        this.value = this.value.replace(/[^0-9.]/g, '');
      });

      function verificarMedicamentoEditarYMostrarModal() {
        const nombre = $('#medicamento').val().trim();
        const id_presentacion = $('#presentacion').val();
        const codigo_barras = $('input[name="codigo_barras"]').val().trim();
        const id_actual = $('input[name="Id"]').val();
        const btnGuardar = $('#btnGuardar');

        const textoOriginal = btnGuardar.text();
        btnGuardar.text('Verificando...').attr('disabled', true);

        $.ajax({
          url: '../../cfg/ajax/verificar_existencia_medicamento.php',
          method: 'POST',
          dataType: 'json',
          data: {
            nombre: nombre,
            id_presentacion: id_presentacion,
            codigo_barras: codigo_barras,
            id_actual: id_actual
          },
          success: function(response) {
            btnGuardar.text(textoOriginal).attr('disabled', false);

            if (response.existe_duplicado) {
              let mensaje = "";
              if (response.tipo_error === 'codigo') {
                mensaje = `⚠️ El código de barras ya está registrado en otro medicamento: <b>${response.detalle}</b>.`;
                $('input[name="codigo_barras"]').addClass('input-error');
              } else {
                mensaje = `⚠️ Ya existe OTRO registro de <b>${nombre}</b> con esa misma presentación.`;
                $('#medicamento, #presentacion').addClass('input-error');
              }
              mostrarAviso('⚠️ Error de Duplicidad:<br>' + mensaje);
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

      $('#formularioMedicamento').on('submit', function(e) {
        e.preventDefault();
        limpiarErrores();
        let errores = [];

        if ($('#medicamento').val().trim() === "") {
          errores.push("Falta el nombre del medicamento.");
          $('#medicamento').addClass('input-error');
        }
        if ($('#presentacion').val().trim() === "") {
          errores.push("Falta la presentacion del medicamento.");
          $('#presentacion').addClass('input-error');
        }
        if ($('#composicion_detallada').val().trim() === "") {
          errores.push("Debe gestionar al menos un principio activo en el modal.");
          $('#btn_modal_pa').addClass('btn-error-sombreado');
        }
        if ($('#contenido_neto').val().trim() === "") {
          errores.push("Falta el contenido neto del medicamento.");
          $('#contenido_neto').addClass('input-error');
        }
        if ($('#via_aplicacion').val().trim() === "") {
          errores.push("Falta el tipo de aplicacion.");
          $('#via_aplicacion').addClass('input-error');
        }
        if ($('#almacenamiento').val().trim() === "") {
          errores.push("Falta el tipo de almacenamiento.");
          $('#almacenamiento').addClass('input-error');
        }

        if (errores.length > 0) {
          mostrarAviso('⚠️ Errores: <ul><li>' + errores.join('</li><li>') + '</li></ul>');
        } else {
          verificarMedicamentoEditarYMostrarModal();
        }
      });

      $('#confirmarGuardar').on('click', function() {
        $('#modalGuardar').modal('hide');
        $('#formularioMedicamento').off('submit').submit();
      });

      $('#modalPatologias').on('show.bs.modal', function() {
        let seleccionadas = $('#patologias_seleccionadas').val().split(',');
        $('.checkbox-patologia').prop('checked', false);
        seleccionadas.forEach(id => {
          if (id !== "") {
            $(`.checkbox-patologia[value="${id}"]`).prop('checked', true);
          }
        });
      });

      <?php if (isset($patologias_json)) : ?>
        let patsExistentes = <?php echo $patologias_json; ?>;
        patsExistentes.forEach(p => agregarFilaPatologia(p.id_patologia));
      <?php endif; ?>

      function agregarFilaPatologia(idSeleccionado = "") {
        let htmlPat = `
        <div class="row fila-pat" style="margin-bottom: 10px;">
            <div class="col-sm-10">
              <div class="input-group">
                <select class="form-control select-pat">
                    <option value="">--- Seleccione una patología ---</option>
                    <?php
                    $q = $conexion->query("SELECT Id_patologia, nombre_patologia FROM patologias WHERE estatus = 1 ORDER BY nombre_patologia ASC");
                    while ($p = $q->fetch_assoc()) {
                      echo "<option value='" . $p['Id_patologia'] . "'>" . $p['nombre_patologia'] . "</option>";
                    }
                    ?>
                </select>
                <span class="input-group-btn">
                    <button class="btn btn-info btn-search-pat" type="button" title="Buscar Patología">
                        <i><img src="../../recursos/imagenes/iconos/buscar.png" style="width:10px; height:10px;"></i>
                    </button>
                </span>
              </div>
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

      $('#btn_modal_pat').click(function() {
        if ($('#contenedor_filas_patologias').children().length === 0) {
          agregarFilaPatologia();
        }
      });

      $('#add_fila_pat').click(() => agregarFilaPatologia());

      $(document).on('click', '.btn-remove-pat', function() {
        $(this).closest('.fila-pat').remove();
      });

      $('#btn_modal_pat').tooltip();

      $('#guardar_pat_listo').click(function() {
        let ids = [];
        let nombres = [];

        $('.select-pat').each(function() {
          let val = $(this).val();
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

      $('#btn_modal_pat').click(function() {
        if ($('#contenedor_filas_patologias').children().length === 0) {
          agregarFilaPatologia();
        }
      });
    });
  </script>