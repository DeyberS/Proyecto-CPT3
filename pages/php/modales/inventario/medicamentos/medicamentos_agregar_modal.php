<?php
include('../../cfg/conexion.php');
$datos_d = null;
$comp_string = '';

if (isset($_GET['duplicar_id'])) {
  $id_url = mysqli_real_escape_string($conexion, $_GET['duplicar_id']);

  $sql_d = "SELECT m.nombre_medicamento, dm.* FROM medicamento m 
              JOIN descripcion_medicamento dm ON m.Id_medicamento = dm.Id_medicamento 
              WHERE dm.Id = '$id_url'";
  $res_d = $conexion->query($sql_d);
  $datos_d = $res_d->fetch_assoc();

  if ($datos_d) {
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
<style>
  /* ESTILOS DE VALIDACIÓN */
  .med-has-error input[type="text"],
  .med-has-error select,
  .med-input-error {
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

  .tooltip.right .tooltip-arrow {
    border-right-color: #3c8dbc !important;
  }
</style>

<div class="modal" id="med_modal_principal" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background-color: #3c8dbc; color: white;">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><i class="fa fa-plus-circle"></i> Añadir Medicamento</h4>
      </div>
      <div class="modal-body">
        <form id="med_formularioMedicamento" method="POST" action="../../cfg/ajax/guardar_medicamento_ajax.php" novalidate>
          <div class="row">
            <label class="control-label"></label>
            <div class="col-sm-4 form-group" id="med_group_nombre">
              <p>Nombre (*):</p>
              <input id="medicamento" name="medicamento" class="form-control" type="text" maxlength="100" value="<?php echo $datos_d['nombre_medicamento'] ?? ''; ?>" placeholder="Ej. Ibuprofeno" required>
            </div>
            <label class="control-label"></label>
            <div class="col-sm-4 form-group" id="med_group_presentacion">
              <p>Presentacion (*):</p>
              <select class="form-control" name="presentacion" id="presentacion" required>
                <option value="">--- Seleccione presentacion ---</option>
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
            <div class="col-sm-4 form-group" id="med_group_contenido_neto" style="top:-20px;">
              <p>Cantidad en la presentación (*):</p>
              <input id="contenido_neto" name="contenido_neto" class="form-control" type="text" value="<?php echo $datos_d['contenido_neto'] ?? ''; ?>" maxlength="100" placeholder="Ej. Capsulas de 20mg">
            </div>

            <label class="control-label"></label>
            <div class="col-sm-4 form-group" id="med_group_concentracion">
              <p>Unidad de presentación:</p>
              <div class="input-group">
                <input type="text" class="form-control med-cantidad-pre" name="cantidad_concentracion" id="cantidad_concentracion" placeholder="Cant." value="<?php echo $datos_d['cantidad_concentracion'] ?? ''; ?>" inputmode="numeric">
                <div class="input-group-btn" style="width: 60%;">
                  <select class="form-control med-uni-concentracion" name="tipo_concentracion" id="tipo_concentracion" required style="border-top-left-radius: 0; border-bottom-left-radius: 0; border-left: 0;">
                    <option selected value="">--- Primero seleccione una presentación --- </option>
                  </select>
                </div>
              </div>
            </div>
            <label class="control-label"></label>
            <div class="col-sm-4 form-group" id="med_group_via">
              <p>Via de aplicación (*):</p>
              <select name="via" id="via_aplicacion" class="form-control">
                <option value="">--- Seleccione una vía de aplicación ---</option>
                <?php
                $vias = [
                  "Oral", "Sublingual", "Rectal", "Intravenosa", "Intramuscular",
                  "Subcutanea", "Intradermica", "Topica", "Transdermica",
                  "Inhalatoria", "Oftalmica", "Otica", "Nasal", "Vaginal"
                ];
                foreach ($vias as $via) {
                  $selected = (isset($datos_d['via_aplicacion']) && $datos_d['via_aplicacion'] == $via) ? 'selected' : '';
                  echo "<option value='$via' $selected>$via</option>";
                }
                ?>
              </select>
            </div>
            <label class="control-label"></label>
            <div class="col-sm-4 form-group" id="med_group_almacenamiento">
              <p>Condición de almacenamiento (*):</p>
              <select name="almacenamiento" id="almacenamiento" class="form-control" required>
                <option value="">--- Seleccione una condición ---</option>
                <?php
                $condiciones = [
                  "-25_a_-10" => "Congelación (-25°C a -10°C)",
                  "2_a_8"     => "Refrigeración (2°C a 8°C)",
                  "8_a_15"    => "Lugar Fresco (8°C a 15°C)",
                  "15_a_25"   => "Temperatura Ambiente (15°C a 25°C)",
                  "max_30"    => "Temperatura Maxima (30°C)"
                ];
                foreach ($condiciones as $valor => $etiqueta) {
                  $selected = (isset($datos_d['almacenamiento']) && $datos_d['almacenamiento'] == $valor) ? 'selected' : '';
                  echo "<option value='$valor' $selected>$etiqueta</option>";
                }
                ?>
              </select>
            </div>
            <label class="control-label"></label>
            <div class="col-sm-4 form-group" id="med_group_laboratorio">
              <p>Laboratorio:</p>
              <div class="input-group">
                <select id="laboratorio" name="laboratorio" class="form-control">
                  <option value="">--- Seleccione un laboratorio ---</option>
                  <?php
                  $sql = $conexion->query("SELECT * FROM laboratorio");
                  while ($resultado = $sql->fetch_assoc()) {
                    $selected = (isset($datos_d['Id_laboratorio']) && $datos_d['Id_laboratorio'] == $resultado['Id_laboratorio']) ? 'selected' : '';
                    echo "<option value='" . $resultado['Id_laboratorio'] . "' $selected>" . $resultado['nombre_laboratorio'] . "</option>";
                  }
                  ?>
                </select>
                <span class="input-group-btn">
                  <button class="btn btn-info" type="button" id="med_btnInfoMedicamento" data-toggle="modal" data-target="#med_modalNuevoLaboratorio" title="Agregar Laboratorio" style="height: 34px;">
                    <i><img src="../../recursos/imagenes/iconos/agregar.png" style="width:10px; height:10px;"></i>
                  </button>
                </span>
              </div>
            </div>
            <label class="control-label"></label>
            <div class="col-sm-4 form-group" id="med_group_principio_activo">
              <p>Composición (*):</p>
              <button type="button" class="btn btn-info btn-block" id="med_btn_modal_pa" data-toggle="modal" data-placement="top" title="Ninguno seleccionado" data-target="#med_modalPrincipios">
                <i></i> Gestionar Composición
              </button>
            </div>
            <input type="hidden" name="composicion_detallada" id="composicion_detallada" value="<?php echo $comp_string ?? ''; ?>" required>
            <label class="control-label"></label>
            <div class="col-sm-4 form-group" id="med_group_patologia">
              <p>Patologías asociadas:</p>
              <button type="button" class="btn btn-info btn-block" id="med_btn_modal_pat" data-toggle="modal" data-placement="top" title="Ninguna seleccionada" data-target="#med_modal_pat">
                <i></i> Gestionar Patologías Asociadas
              </button>
            </div>
            <input type="hidden" name="patologias_seleccionadas" id="patologias_seleccionadas" value="<?php echo $patologias_string ?? ''; ?>">
            <label class="control-label"></label>
            <div class="col-sm-4 form-group" id="med_group_codigo_barras">
              <p>Codigo de barras:</p>
              <input type="text" id="codigo_barras" name="codigo_barras" placeholder="Ej. 234758383" class="form-control">
            </div>
            <label class="control-label"></label>
            <div class="col-sm-4 form-group" id="med_group_stock_minimo_medicamento">
              <p>Stock minimo (*):</p>
              <input type="text" id="stock_minimo_medicamento" name="stock_minimo_medicamento" class="form-control" placeholder="Ej. 1" required>
            </div>
            <label class="control-label"></label>
            <div class="col-sm-4 form-group" id="med_group_stock_maximo_medicamento">
              <p>Stock maximo (*):</p>
              <input type="text" id="stock_maximo_medicamento" name="stock_maximo_medicamento" class="form-control" placeholder="Ej. 100" required>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <span data-toggle="tooltip" data-placement="top" title="Importar los datos de un medicamento ya registrado para agilizar su creación">
          <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#med_modalCopiarMedicamento" style="background-color: #605ca8; border-color: #605ca8;">
            <i class="fa fa-copy"></i> Importar
          </button>
        </span>
        <button type="button" class="btn btn-success" id="med_btnGuardarFinal">Guardar Medicamento</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="med_modalCopiarMedicamento" tabindex="-1" role="dialog" style="z-index: 1045;">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background-color: #3c8dbc; color: white;">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><i class="fa fa-search"></i> Buscar Medicamento para Copiar</h4>
      </div>
      <div class="modal-body">
        <input type="text" id="med_buscar_plantilla" class="form-control" placeholder="Escribe el nombre del medicamento...">
        <hr>
        <div id="med_lista_medicamentos_plantilla" style="max-height: 400px; overflow-y: auto;">
          <p class="text-center">Cargando medicamentos...</p>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="med_modalPrincipios" role="dialog" style="z-index: 1050;">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background-color: #3c8dbc; color: white;">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Agregar Composición del Medicamento</h4>
      </div>
      <div class="modal-body">
        <div id="med_contenedor_filas_principios">
          <div class="row med-fila-pa" style="margin-bottom: 10px;">
            <div class="col-sm-6">
              <div class="input-group">
                <select class="form-control med-select-pa" id="med_principio_activo">
                  <option value="" name="id_pa">--- Seleccione un principio activo ---</option>
                  <?php
                  $sql_pa = $conexion->query("SELECT * FROM principio_activo");
                  while ($r = $sql_pa->fetch_assoc()) {
                    echo "<option value='" . $r['id_principio_activo'] . "' data-nombre='" . $r['nombre'] . "'>" . $r['nombre'] . "</option>";
                  }
                  ?>
                </select>
                <span class="input-group-btn">
                  <button class="btn btn-info med-btn-search-pa" type="button" title="Buscar P.A.">
                    <i><img src="../../recursos/imagenes/iconos/buscar.png" style="width:10px; height:10px;"></i>
                  </button>
                </span>
              </div>
            </div>
            <div class="col-sm-2 form-group">
              <input type="text" class="form-control med-cant-pa" placeholder="Cant." inputmode="numeric" required>
            </div>
            <div class="col-sm-2 pull-left form-group" style="margin-left:-20px;">
              <select class="form-control med-uni-pa" required>
                <option selected value="">--- Primero seleccione una presentación ---</option>
              </select>
            </div>
            <div class="col-sm-2">
              <button type="button" class="btn btn-danger med-btn-remove-pa">
                <i><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></i>
              </button>
            </div>
          </div>
        </div>
        <div class="col-sm-12 form-group" id="med_group_excipientes" style="left:-10px;">
          <p>Excipientes:</p>
          <input type="text" id="excipientes" name="excipientes" value="<?php echo $datos_d['excipientes'] ?? ''; ?>" placeholder="Ej: Microcristalina celulosa, dióxido de titanio y gelatina." class="form-control">
        </div>
        <button type="button" class="btn btn-success btn-sm" id="med_btn_add_pa">
          <i class="fa fa-plus"></i> Añadir Otro Principio Activo
        </button>
        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#med_modalNuevoPA">
          <i class="fa fa-plus-circle"></i> Nuevo Principio Activo
        </button>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal" id="med_guardar_pa_temp">Listo</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="med_modal_pat" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background-color: #3c8dbc; color: white;">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Agregar Patologías</h4>
      </div>
      <div class="modal-body">
        <div id="med_contenedor_filas_patologias"></div>
        <button type="button" class="btn btn-success btn-sm" id="med_add_fila_pat">
          <i class="fa fa-plus"></i> Añadir Otra Patología
        </button>
        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#med_modalNuevaPatologia">
          <i class="fa fa-plus-circle"></i> Nueva Patología
        </button>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="med_guardar_pat_listo" data-dismiss="modal">Listo</button>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="med_modalBuscarPA" role="dialog" style="z-index: 1060;">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background-color: #3c8dbc; color: white;">
        <h4 class="modal-title">Buscar Principio Activo</h4>
      </div>
      <div class="modal-body">
        <input type="text" id="med_inputBuscarPA" class="form-control" placeholder="Escriba para filtrar...">
        <div class="list-group" id="med_listaResultadosPA" style="margin-top: 10px; max-height: 200px; overflow-y: auto;"></div>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="med_modalBuscarPat" role="dialog">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background-color: #3c8dbc; color: white;">
        <h4 class="modal-title">Buscar Patología</h4>
      </div>
      <div class="modal-body">
        <input type="text" id="med_inputBuscarPat" class="form-control" placeholder="Escriba para filtrar...">
        <div class="list-group" id="med_listaResultadosPat" style="margin-top: 10px; max-height: 200px; overflow-y: auto;"></div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="med_modalNuevoLaboratorio" tabindex="-1" role="dialog" style="z-index: 1050;">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background-color: #3c8dbc; color: white;">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><i class="fa fa-building"></i> Nuevo Laboratorio</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>Nombre del Laboratorio:</label>
          <input type="text" id="med_nombre_lab_nuevo" class="form-control" placeholder="Ej: Bayer" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="med_btnGuardarLab">Guardar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="med_modalNuevoPA" role="dialog" style="z-index: 1060;">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background-color: #3c8dbc; color: white;">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Añadir Nuevo Principio Activo</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>Nombre (*):</label>
          <input type="text" id="med_nombre_pa_nuevo" class="form-control" placeholder="Ej. Paracetamol">
        </div>
        <div class="form-group">
          <label>Descripción:</label>
          <input type="text" id="med_desc_pa_nuevo" class="form-control" placeholder="Opcional">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" id="med_btnGuardarNuevoPA">Guardar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="med_modalNuevaPatologia" role="dialog" style="z-index: 1060;">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background-color: #3c8dbc; color: white;">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Añadir Nueva Patología</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>Nombre (*):</label>
          <input type="text" id="med_nombre_pat_nuevo" class="form-control">
        </div>
        <div class="form-group">
          <label>Código CIE (*):</label>
          <input type="text" id="med_cie_pat_nuevo" class="form-control" placeholder="Ej. A00">
        </div>
        <div class="form-group">
          <label>Contagioso (*):</label>
          <select id="med_contagioso_pat_nuevo" class="form-control">
            <option value="NO">NO</option>
            <option value="SI">SI</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" id="med_btnGuardarNuevaPat">Guardar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="med_avisoModal" role="dialog">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div id="med_headerAviso" class="modal-header bg-crimson">
        <h5 class="modal-title">Aviso</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:white;"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <p id="med_avisoTexto"></p>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button></div>
    </div>
  </div>
</div>

<script>
  (function($) {
    $(document).ready(function() {

      const unidadConcentracionPrevia = '<?php echo $datos_d['id_tipo_concentracion'] ?? ''; ?>';

      function limpiarErrores() {
        $('#med_formularioMedicamento').find('input, select').removeClass('med-input-error');
        $('#med_formularioMedicamento').find('.form-group').removeClass('med-has-error');
      }

      function bloquearNumeros(e) {
        const teclasPermitidas = ["Backspace", "Tab", "ArrowLeft", "ArrowRight", "Delete", "Shift"];
        if (teclasPermitidas.includes(e.key)) return;
        if (e.key >= "0" && e.key <= "9") e.preventDefault();
      }

      function limpiarNumeros(e) {
        e.target.value = e.target.value.replace(/[0-9]/g, "");
      }

      // =====================================================================
      // RESTRICCIONES DE INPUTS
      // =====================================================================
      $('#medicamento').on('input', function() {
        let valor = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
        if (valor.length > 50) valor = valor.substring(0, 50);
        this.value = valor;
      });

      $(document).on('input', '.med-cant-pa, #codigo_barras, .med-cantidad-pre, #stock_minimo_medicamento, #stock_maximo_medicamento', function() {
        let valor = this.value.replace(/[^0-9.]/g, '');
        const partes = valor.split('.');
        if (partes.length > 2) valor = partes[0] + '.' + partes.slice(1).join('');
        this.value = valor;
      });

      const campos = [document.getElementById("medicamento")];
      campos.forEach(campo => {
        if (campo) {
          campo.addEventListener("keydown", bloquearNumeros);
          campo.addEventListener("input", limpiarNumeros);
        }
      });

      // =====================================================================
      // TOOLTIPS Y EVENTOS DE CARGA UNIDADES
      // =====================================================================
      $('#med_btn_modal_pa, #med_btn_modal_pat').tooltip();

      const presentacionCargada = $('#presentacion').val();
      const unidadCargada = '<?php echo $datos_d['Id_tipo_concentracion'] ?? $datos_d['id_tipo_concentracion'] ?? $datos_d['tipo_concentracion'] ?? $datos_d['id_tipo_unidad_medida'] ?? ''; ?>';

      function inicializarUnidades(idPres, unidadSel) {
        if (idPres) {
          $.ajax({
            url: '../../cfg/ajax/obtener_unidades_medicamentos.php?id=' + idPres,
            success: function(data) {
              window.medUnidadesDisponibles = data;
              $('.med-uni-pa, .med-uni-concentracion').html(data);
              if (unidadSel && unidadSel !== "") {
                $('#tipo_concentracion').val(unidadSel);
              }
            }
          });
        }
      }

      if (presentacionCargada) inicializarUnidades(presentacionCargada, unidadCargada);

      $('#presentacion').on('change', function() {
        inicializarUnidades($(this).val(), "");
      });

      // =====================================================================
      // LÓGICA: COMPOSICIÓN (PRINCIPIOS ACTIVOS)
      // =====================================================================
      $('#med_btn_add_pa').on('click', function() {
        var nuevaFila = $('.med-fila-pa:first').clone();
        nuevaFila.find('input').val('');
        if (window.medUnidadesDisponibles) {
          nuevaFila.find('.med-uni-pa').html(window.medUnidadesDisponibles);
        }
        $('#med_contenedor_filas_principios').append(nuevaFila);
      });

      $('#med_contenedor_filas_principios').on('click', '.med-btn-remove-pa', function() {
        if ($('.med-fila-pa').length > 1) {
          $(this).closest('.med-fila-pa').remove();
        } else {
          mostrarAviso("Debe haber al menos un espacio para principio activo.");
        }
      });

      $(document).on('change', '.med-select-pa', function() {
        let selectActual = $(this);
        let valorActual = selectActual.val();
        if (valorActual === "") return;

        let conteo = 0;
        $('.med-select-pa').each(function() {
          if ($(this).val() === valorActual) conteo++;
        });

        if (conteo > 1) {
          mostrarAviso("⚠️ <b>Atención:</b> Este principio activo ya ha sido seleccionado en otra fila. Por favor, elija uno diferente o modifique la cantidad en el que ya agregó.");
          selectActual.val("");
        }
      });

      $('#med_guardar_pa_temp').on('click', function() {
        var resumen = [];
        var datos_para_db = [];

        $('.med-fila-pa').each(function() {
          var $fila = $(this);
          var id_pa = $fila.find('.med-select-pa').val();
          var nombre = $fila.find('.med-select-pa option:selected').data('nombre') || $fila.find('.med-select-pa option:selected').text();
          var cantidad = $fila.find('.med-cant-pa').val();
          var id_unidad = $fila.find('.med-uni-pa').val();
          var nombre_unidad = $fila.find('.med-uni-pa option:selected').text();

          if (id_pa && cantidad && id_unidad) {
            resumen.push(nombre.trim() + " " + cantidad + " " + nombre_unidad.trim());
            datos_para_db.push(id_pa + "," + cantidad + "," + id_unidad);
          }
        });

        $('#composicion_detallada').val(datos_para_db.join('|'));

        var boton = $('#med_btn_modal_pa');
        if (resumen.length > 0) {
          boton.attr('data-original-title', resumen.join(', '));
        }
      });

      // =====================================================================
      // LÓGICA: PATOLOGÍAS
      // =====================================================================
      <?php if (isset($patologias_json)) : ?>
        let patsExistentes = <?php echo $patologias_json; ?>;
        let nombresPat = [];
        patsExistentes.forEach(p => {
          agregarFilaPatologia(p.id_patologia);
          if (p.nombre_patologia) nombresPat.push(p.nombre_patologia.trim());
        });
        if (nombresPat.length > 0) {
          $('#med_btn_modal_pat').attr('title', nombresPat.join(', '));
          $('#med_btn_modal_pat').attr('data-original-title', nombresPat.join(', '));
        }
      <?php endif; ?>

      function agregarFilaPatologia(idSeleccionado = "") {
        let htmlPat = `
      <div class="row med-fila-pat" style="margin-bottom: 10px;">
          <div class="col-sm-10">
              <div class="input-group">
                  <select class="form-control med-select-pat">
                      <option value="">--- Seleccione una patología ---</option>
                      <?php
                      $q = $conexion->query("SELECT Id_patologia, nombre_patologia FROM patologias WHERE estatus = 1 ORDER BY nombre_patologia ASC");
                      while ($p = $q->fetch_assoc()) {
                        echo "<option value='" . $p['Id_patologia'] . "'>" . $p['nombre_patologia'] . "</option>";
                      }
                      ?>
                  </select>
                  <span class="input-group-btn">
                      <button class="btn btn-info med-btn-search-pat" type="button" title="Buscar Patología">
                        <i><img src="../../recursos/imagenes/iconos/buscar.png" style="width:10px; height:10px;"></i>
                      </button>
                  </span>
              </div>
          </div>
          <div class="col-sm-2">
              <button type="button" class="btn btn-danger med-btn-remove-pat">
                  <i><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></i>
              </button>
          </div>
      </div>`;
        $('#med_contenedor_filas_patologias').append(htmlPat);
        if (idSeleccionado) {
          $('#med_contenedor_filas_patologias .med-fila-pat:last .med-select-pat').val(idSeleccionado);
        }
      }

      $('#med_btn_modal_pat').click(function() {
        if ($('#med_contenedor_filas_patologias').children().length === 0) agregarFilaPatologia();
      });

      $('#med_add_fila_pat').click(() => agregarFilaPatologia());

      $(document).on('click', '.med-btn-remove-pat', function() {
        $(this).closest('.med-fila-pat').remove();
      });

      $(document).on('change', '.med-select-pat', function() {
        let selectActual = $(this);
        let valorActual = selectActual.val();
        if (valorActual === "") return;

        let conteo = 0;
        $('.med-select-pat').each(function() {
          if ($(this).val() === valorActual) conteo++;
        });

        if (conteo > 1) {
          mostrarAviso("⚠️ <b>Atención:</b> Esta patología ya ha sido seleccionada en esta lista. Por favor, elija una diferente.");
          selectActual.val("");
        }
      });

      $('#med_guardar_pat_listo').click(function() {
        let ids = [];
        let nombres = [];

        $('.med-select-pat').each(function() {
          let val = $(this).val();
          if (val && val !== "") {
            ids.push(val);
            let txt = $(this).find('option:selected').text();
            if (txt) nombres.push(txt.trim());
          }
        });

        $('#patologias_seleccionadas').val(ids.join('|'));

        if (ids.length > 0) {
          $('#med_btn_modal_pat').attr('data-original-title', nombres.join(', '));
        } else {
          $('#med_btn_modal_pat').attr('data-original-title', 'Ninguna seleccionada');
        }
      });

      // =====================================================================
      // GUARDAR MODALES DINÁMICOS (AJAX)
      // =====================================================================
      $('#med_btnGuardarLab').click(function() {
        var nombre = $('#med_nombre_lab_nuevo').val();
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
              $('#med_modalNuevoLaboratorio').modal('hide');
              $('#med_nombre_lab_nuevo').val('');
              $('#laboratorio').append('<option value="' + response + '" selected>' + nombre + '</option>');
              mostrarAviso("Laboratorio guardado correctamente");
            } else {
              mostrarAviso("Error al guardar el laboratorio");
            }
          }
        });
      });

      $('#med_btnGuardarNuevoPA').click(function() {
        let nombre = $('#med_nombre_pa_nuevo').val();
        let desc = $('#med_desc_pa_nuevo').val();
        if (nombre.trim() === '') {
          mostrarAviso("Nombre obligatorio");
          return;
        }
        $.ajax({
          url: 'get/guardar_principio_activo.php',
          type: 'POST',
          data: {
            nombre: nombre,
            descripcion: desc
          },
          success: function(response) {
            if (response !== "error") {
              $('#med_modalNuevoPA').modal('hide');
              let nuevaOpcion = `<option value="${response}" data-nombre="${nombre}">${nombre}</option>`;
              $('.med-select-pa').append(nuevaOpcion);
              $('#med_nombre_pa_nuevo, #med_desc_pa_nuevo').val('');
              mostrarAviso("Principio Activo guardado con éxito");
            } else {
              mostrarAviso("Error al guardar en DB");
            }
          }
        });
      });

      $('#med_btnGuardarNuevaPat').click(function() {
        let nombre = $('#med_nombre_pat_nuevo').val();
        let cie = $('#med_cie_pat_nuevo').val();
        let contagioso = $('#med_contagioso_pat_nuevo').val();
        if (nombre.trim() === '' || cie.trim() === '') {
          mostrarAviso("Nombre y CIE obligatorios");
          return;
        }
        $.ajax({
          url: 'get/guardar_patologia.php',
          type: 'POST',
          data: {
            nombre: nombre,
            cie: cie,
            contagioso: contagioso
          },
          success: function(response) {
            if (response !== "error") {
              $('#med_modalNuevaPatologia').modal('hide');
              let nuevaOpcion = `<option value="${response}">${nombre}</option>`;
              $('.med-select-pat').append(nuevaOpcion);
              $('#med_nombre_pat_nuevo, #med_cie_pat_nuevo').val('');
              mostrarAviso("Patología guardada con éxito");
            } else {
              mostrarAviso("Error al guardar en DB (Posible código CIE duplicado)");
            }
          }
        });
      });

      // =====================================================================
      // BÚSQUEDAS (PA Y PATOLOGÍAS)
      // =====================================================================
      let selectDestinoTarget = null;

      $(document).on('click', '.med-btn-search-pa', function() {
        selectDestinoTarget = $(this).closest('.input-group').find('.med-select-pa');
        $('#med_modalBuscarPA').modal('show');
        $('#med_inputBuscarPA').val('').trigger('keyup');
      });

      $('#med_inputBuscarPA').on('keyup', function() {
        let texto = $(this).val().toLowerCase();
        let html = '';
        let opciones = $('.med-select-pa:first option').not('[value=""]');
        opciones.each(function() {
          let nombre = $(this).text();
          if (nombre.toLowerCase().includes(texto)) {
            html += `<a href="#" class="list-group-item list-group-item-action med-seleccionar-pa" data-id="${$(this).val()}">${nombre}</a>`;
          }
        });
        $('#med_listaResultadosPA').html(html);
      });

      $(document).on('click', '.med-seleccionar-pa', function(e) {
        e.preventDefault();
        selectDestinoTarget.val($(this).data('id')).trigger('change');
        $('#med_modalBuscarPA').modal('hide');
      });

      $(document).on('click', '.med-btn-search-pat', function() {
        selectDestinoTarget = $(this).closest('.input-group').find('.med-select-pat');
        $('#med_modalBuscarPat').modal('show');
        $('#med_inputBuscarPat').val('').trigger('keyup');
      });

      $('#med_inputBuscarPat').on('keyup', function() {
        let texto = $(this).val().toLowerCase();
        let html = '';
        let opciones = $('.med-select-pat:first option').not('[value=""]');
        opciones.each(function() {
          let nombre = $(this).text();
          if (nombre.toLowerCase().includes(texto)) {
            html += `<a href="#" class="list-group-item list-group-item-action med-seleccionar-pat" data-id="${$(this).val()}">${nombre}</a>`;
          }
        });
        $('#med_listaResultadosPat').html(html);
      });

      $(document).on('click', '.med-seleccionar-pat', function(e) {
        e.preventDefault();
        selectDestinoTarget.val($(this).data('id')).trigger('change');
        $('#med_modalBuscarPat').modal('hide');
      });

      // =====================================================================
      // IMPORTAR / COPIAR PLANTILLA
      // =====================================================================
      $('#med_modalCopiarMedicamento').on('shown.bs.modal', function() {
        cargarPlantillas('');
      });

      $('#med_buscar_plantilla').on('keyup', function() {
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
            $('#med_lista_medicamentos_plantilla').html(html);
          }
        });
      }

      $(document).on('click', '.btn-copiar-datos', function() {
        const d = $(this).data();

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

        if (d.id_presentacion) {
          fetch('../../cfg/ajax/obtener_unidades_medicamentos.php?id=' + d.id_presentacion)
            .then(response => response.text())
            .then(htmlUnidades => {
              $('.med-uni-pa, .med-uni-concentracion').html(htmlUnidades);
              $('#tipo_concentracion').val(unidadASeleccionar);

              if (d.composicion) {
                $('#composicion_detallada').val(d.composicion);

                const opcionesCatalogo = $('.med-select-pa').first().html();

                $('#med_contenedor_filas_principios').empty();

                const filas = d.composicion.split('|');
                const nombres = d.nombres_pa.split('|');

                filas.forEach((fila, index) => {
                  const [id_pa, cant, id_uni] = fila.split(',');
                  const nombrePA = nombres[index];

                  let nuevaFila = `
                      <div class="row med-fila-pa" style="margin-bottom: 10px;">
                        <div class="col-sm-6">
                           <div class="input-group">
                              <select class="form-control med-select-pa">
                                  ${opcionesCatalogo}
                              </select>
                              <span class="input-group-btn">
                                  <button class="btn btn-info med-btn-search-pa" type="button" title="Buscar P.A.">
                                    <i><img src="../../recursos/imagenes/iconos/buscar.png" style="width:10px; height:10px;"></i>
                                  </button>
                              </span>
                           </div>
                        </div>
                        <div class="col-sm-2">
                          <input type="text" class="form-control med-cant-pa" value="${cant}">
                        </div>
                        <div class="col-lg-2">
                          <select class="form-control med-uni-pa">${htmlUnidades}</select>
                        </div>
                        <div class="col-sm-2">
                          <button type="button" class="btn btn-danger med-btn-remove-pa"><i><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></i></button>
                        </div>
                      </div>`;

                  $('#med_contenedor_filas_principios').append(nuevaFila);
                  $('#med_contenedor_filas_principios .med-fila-pa:last .med-select-pa').val(id_pa);
                  $('#med_contenedor_filas_principios .med-fila-pa:last .med-uni-pa').val(id_uni);
                });

                const textoResumen = nombres.join(', ');
                $('#med_btn_modal_pa').attr('data-original-title', textoResumen);
              }
            });
        }

        if (d.patologias) {
          $('#med_contenedor_filas_patologias').empty();
          const idsPat = String(d.patologias).split('|');
          const nombresPat = d.nombres_pat ? String(d.nombres_pat).split('|') : [];

          idsPat.forEach(id => {
            if (id) agregarFilaPatologia(id);
          });

          let textoTooltip = nombresPat.length > 0 ? nombresPat.join(', ') : 'Patologías seleccionadas';
          $('#med_btn_modal_pat').attr('data-original-title', textoTooltip);
        }

        $('#med_modalCopiarMedicamento').modal('hide');
      });

      // =====================================================================
      // GUARDADO FINAL (VERIFICACIÓN + AJAX APPEND)
      // =====================================================================
      $('#med_btnGuardarFinal').on('click', function(e) {
        e.preventDefault();
        limpiarErrores();
        let stockMinTexto = $('#stock_minimo_medicamento').val().trim();
        let stockMaxTexto = $('#stock_maximo_medicamento').val().trim();
        let errores = [];

        if ($('#medicamento').val().trim() === "") {
          errores.push("Falta el nombre del medicamento.");
          $('#med_group_nombre').addClass('med-has-error');
        }
        if ($('#presentacion').val() === "") {
          errores.push("Falta la presentacion del medicamento.");
          $('#med_group_presentacion').addClass('med-has-error');
        }
        if ($('#cantidad_concentracion').val().trim() === "") {
          errores.push("Falta la cantidad de la unidad de presentación.");
          $('#med_group_concentracion').addClass('med-has-error');
        }
        if ($('#composicion_detallada').val() === "") {
          errores.push("Debe gestionar al menos un principio activo en el modal.");
          $('#med_group_principio_activo').addClass('med-has-error');
        }
        if ($('#via_aplicacion').val() === "") {
          errores.push("Falta el tipo de aplicacion.");
          $('#med_group_via').addClass('med-has-error');
        }
        if ($('#contenido_neto').val().trim() === "") {
          errores.push("Falta el contenido neto del medicamento.");
          $('#med_group_contenido_neto').addClass('med-has-error');
        }
        if ($('#almacenamiento').val() === "") {
          errores.push("Falta el tipo de almacenamiento.");
          $('#med_group_almacenamiento').addClass('med-has-error');
        }

        if (stockMinTexto === "") {
          errores.push('El stock mínimo del medicamento no puede estar vacío.');
          $('#med_group_stock_minimo_medicamento').addClass('med-has-error');
        } else if (parseInt(stockMinTexto) <= 0) {
          errores.push('El stock mínimo debe ser mayor a 0.');
          $('#med_group_stock_minimo_medicamento').addClass('med-has-error');
        }

        if (stockMaxTexto === "") {
          errores.push('El stock máximo del medicamento no puede estar vacío.');
          $('#med_group_stock_maximo_medicamento').addClass('med-has-error');
        } else if (parseInt(stockMaxTexto) <= 0) {
          errores.push('El stock máximo debe ser mayor a 0.');
          $('#med_group_stock_maximo_medicamento').addClass('med-has-error');
        }

        if (stockMinTexto !== "" && stockMaxTexto !== "") {
          let minVal = parseInt(stockMinTexto);
          let maxVal = parseInt(stockMaxTexto);
          if (minVal >= maxVal) {
            errores.push('Disculpe, el stock mínimo (' + minVal + ') no puede ser mayor o igual que el stock máximo (' + maxVal + ').');
            $('#med_group_stock_minimo_medicamento, #med_group_stock_maximo_medicamento').addClass('med-has-error');
          }
        }

        if (errores.length > 0) {
          mostrarAviso("⚠️ Errores: <br><ul><li>" + errores.join('</li><li>') + "</li></ul>");
          return;
        }

        const nombre = $('#medicamento').val().trim();
        const id_presentacion = $('#presentacion').val();
        const codigo_barras = $('#codigo_barras').val().trim();
        const btnGuardar = $(this);

        const textoOriginal = btnGuardar.html();
        btnGuardar.html('<i class="fa fa-spinner fa-spin"></i> Verificando...').prop('disabled', true);

        // ======================= AJAX 1: VERIFICAR =======================
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
            if (response.existe_duplicado) {
              btnGuardar.html(textoOriginal).prop('disabled', false);
              let mensaje = "";
              if (response.tipo_error === 'codigo') {
                mensaje = `⚠️ El código de barras ya está registrado para el medicamento: <b>${response.detalle}</b>.`;
                $('#med_group_codigo_barras').addClass('med-has-error');
              } else {
                mensaje = `⚠️ Ya existe un registro de <b>${nombre}</b> con esa misma presentación.`;
                $('#med_group_nombre, #med_group_presentacion').addClass('med-has-error');
              }
              mostrarAviso('🛑 Error de Duplicidad:<br>' + mensaje);
            } else {

              // ======================= AJAX 2: GUARDAR =======================
              let formData = $('#med_formularioMedicamento').serialize();

              $.ajax({
                url: '../../cfg/ajax/guardar_medicamento_ajax.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(respuestaGuardado) {

                  if (respuestaGuardado.success) {
                    let nuevoId = respuestaGuardado.id_desc;

                    btnGuardar.html(textoOriginal).prop('disabled', false);

                    // 1. Iniciamos tu animación de salida
                    $('#med_modal_principal').removeClass('in').addClass('out');

                    // 2. Dejamos que termine tu animación y ocultamos el modal
                    setTimeout(function() {
                      $('#med_modal_principal').modal('hide');
                      $('#med_modal_principal').removeClass('out');


                      setTimeout(function() {
                        mostrarExito("✅ El medicamento ya se creó exitosamente.");
                      }, 150);

                      // 4. Refrescamos la lista en segundo plano
                      $.ajax({
                        url: '../../cfg/ajax/filtrar_medicamentos_completo.php',
                        type: 'POST',
                        data: {
                          filtro_busqueda_rapida: '',
                          modo: $('#op').length ? $('#op').val() : 'entrada'
                        },
                        dataType: 'json',
                        success: function(responseLista) {
                          const $selectMed = $('#Id_descripcion_medicamento');

                          if ($selectMed.length > 0) {
                            $selectMed.empty().append('<option value="">--- Seleccione un Medicamento ---</option>');
                            if (responseLista.length > 0) {
                              responseLista.forEach(function(item) {
                                $selectMed.append('<option value="' + item.id_desc + '" data-nombre="' + item.nombre_completo + '">' + item.nombre_completo + '</option>');
                              });
                            }
                            if (typeof window.opcionesOriginalesMedicamentos !== 'undefined') {
                              window.opcionesOriginalesMedicamentos = $selectMed.html();
                            }
                          }

                          if (nuevoId && !isNaN(nuevoId) && $selectMed.length > 0) {
                            $selectMed.val(nuevoId).trigger('change');
                          }
                        }
                      });
                    }, 400);

                  } else {
                    btnGuardar.html(textoOriginal).prop('disabled', false);
                    mostrarAviso("🛑 Hubo un problema: " + respuestaGuardado.message);
                  }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                  btnGuardar.html(textoOriginal).prop('disabled', false);
                  console.error("Error AJAX:", textStatus, errorThrown, jqXHR.responseText);
                  mostrarAviso("🛑 Hubo un error crítico de conexión al intentar guardar el medicamento en el servidor.");
                }
              }); // Cierra AJAX 2 (Guardar)
            }
          },
          error: function(jqXHR, textStatus, errorThrown) {
            btnGuardar.html(textoOriginal).prop('disabled', false);
            console.error("Error AJAX verificación:", textStatus, errorThrown);
            mostrarAviso("🛑 Hubo un error al verificar la duplicidad.");
          }
        }); // Cierra AJAX 1 (Verificar)
      }); // Cierra evento click
      // =====================================================================
      // RECONSTRUIR AL DUPLICAR (DESDE PHP)
      // =====================================================================
      <?php if (isset($_GET['duplicar_id']) && $datos_d) : ?>(function() {
          const principiosADuplicar = <?php echo $principios_json; ?>;
          const idPresentacion = '<?php echo $datos_d['Id_presentacion']; ?>';
          const opcionesCatalogo = $('#med_principio_activo').html();

          if (principiosADuplicar.length > 0 && idPresentacion) {
            fetch('../../cfg/ajax/obtener_unidades_medicamentos.php?id=' + idPresentacion)
              .then(response => response.text())
              .then(htmlUnidades => {
                $('#med_contenedor_filas_principios').empty();
                principiosADuplicar.forEach(pa => {
                  let nuevaFila = `
                <div class="row med-fila-pa" style="margin-bottom: 10px;">
                  <div class="col-sm-6">
                    <div class="input-group">
                      <select class="form-control med-select-pa">
                        ${opcionesCatalogo}
                      </select>
                      <span class="input-group-btn">
                        <button class="btn btn-info med-btn-search-pa" type="button" title="Buscar P.A.">
                          <i><img src="../../recursos/imagenes/iconos/buscar.png" style="width:10px; height:10px;"></i>
                        </button>
                      </span>
                    </div>
                  </div>
                  <div class="col-sm-2">
                    <input type="text" class="form-control med-cant-pa" value="${pa.cantidad_unidad_medida}">
                  </div>
                  <div class="col-sm-2">
                    <select class="form-control med-uni-pa">${htmlUnidades}</select>
                  </div>
                  <div class="col-sm-2">
                    <button type="button" class="btn btn-danger med-btn-remove-pa">
                      <i><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></i>
                    </button>
                  </div>
                </div>`;
                  $('#med_contenedor_filas_principios').append(nuevaFila);
                  let $ultima = $('#med_contenedor_filas_principios .med-fila-pa:last');
                  $ultima.find('.med-select-pa').val(pa.id_principio_activo);
                  $ultima.find('.med-uni-pa').val(pa.id_tipo_unidad_medida);
                });
                $('#med_guardar_pa_temp').trigger('click');
              });
          }
        })();
      <?php endif; ?>
      // =====================================================================
      // VACIAR DATOS AL CERRAR EL MODAL
      // =====================================================================
      $('#med_modal_principal').on('hidden.bs.modal', function() {
        // 1. Resetear el formulario principal (limpia inputs, selects y excipientes)
        $('#med_formularioMedicamento')[0].reset();

        // 2. Limpiar explícitamente el campo de excipientes por seguridad
        $('#excipientes').val('');

        // 3. Vaciar campos ocultos
        $('#composicion_detallada').val('');
        $('#patologias_seleccionadas').val('');

        // 4. Resetear filas de Principios Activos: 
        // Borramos todas las filas creadas dinámicamente excepto la primera, y vaciamos esa primera.
        $('#med_contenedor_filas_principios .med-fila-pa').not(':first').remove();
        $('#med_contenedor_filas_principios .med-fila-pa:first').find('input').val('');
        $('#med_contenedor_filas_principios .med-fila-pa:first').find('select').val('');

        // 5. Resetear contenedor de Patologías (lo vaciamos por completo)
        $('#med_contenedor_filas_patologias').empty();

        // 6. Restablecer los tooltips de los botones azules
        $('#med_btn_modal_pa').attr('data-original-title', 'Ninguno seleccionado');
        $('#med_btn_modal_pat').attr('data-original-title', 'Ninguna seleccionada');

        // 7. Limpiar las clases rojas de error por si se cerró tras un intento fallido
        limpiarErrores();
      });

    });
  })(jQuery);
</script>