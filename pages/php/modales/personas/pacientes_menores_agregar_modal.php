<?php
if (!isset($conexion)) {
  include('../../cfg/conexion.php');
}

$lista_pat_menor = [];
$q = $conexion->query("SELECT Id_patologia, nombre_patologia, codigo_cie FROM patologias WHERE estatus = 1 ORDER BY nombre_patologia ASC");
if ($q) {
  while ($p = $q->fetch_assoc()) {
    $lista_pat_menor[] = ['id' => intval($p['Id_patologia']), 'nombre' => $p['nombre_patologia'], 'cie' => $p['codigo_cie']];
  }
}

$lista_ale_menor = [];
$q = $conexion->query("SELECT Id_alergias_conocidas, nombre_alergia FROM alergias_conocidas WHERE estatus = 1 ORDER BY nombre_alergia ASC");
if ($q) {
  while ($a = $q->fetch_assoc()) {
    $lista_ale_menor[] = ['id' => intval($a['Id_alergias_conocidas']), 'nombre' => $a['nombre_alergia']];
  }
}

$lista_disc_menor = [];
$q = $conexion->query("SELECT Id_discapacidad, nombre_discapacidad FROM discapacidades WHERE estatus = 1 ORDER BY nombre_discapacidad ASC");
if ($q) {
  while ($d = $q->fetch_assoc()) {
    $lista_disc_menor[] = ['id' => intval($d['Id_discapacidad']), 'nombre' => $d['nombre_discapacidad']];
  }
}
?>

<div class="modal fade" id="modalAgregarPacienteMenor" role="dialog" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-lg" role="document" style="width: 92%; max-width: 1150px;">
    <div class="modal-content">
      <div class="modal-header bg-primary" style="cursor: default;">
        <h4 class="modal-title" style="color: white;">
          <i class="fa fa-child"></i> Registrar Paciente Menor de Edad
        </h4>
      </div>

      <div class="modal-body" style="padding: 15px 20px; max-height: 78vh; overflow-y: auto;">

        <div class="row" style="margin-bottom: 10px;">
          <div class="col-sm-12">
            <div class="form-group" style="margin-bottom: 5px;">
              <label style="font-weight: bold; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
                <input type="checkbox" id="chk_menor_interno" style="width: 18px; height: 18px; margin: 0; cursor: pointer;">
                <span>Paciente Interno (marcar para incluir dirección, ocupación y estudios)</span>
              </label>
              <p class="text-muted" style="margin: 4px 0 0 26px; font-size: 12px;">
                Si está desmarcado (Externo), solo se usarán Datos Personales, Representantes, Grupo Sanguíneo y Discapacidades.
              </p>
            </div>
          </div>
        </div>

        <div class="nav-tabs-custom" style="box-shadow: none; margin-bottom: 0;">
          <ul class="nav nav-tabs" id="tabsMenorModal">
            <li class="active" data-tab-name="info">
              <a href="#mn_info" data-toggle="tab">Datos Personales</a>
            </li>
            <li data-tab-name="ocupacion_estudios" class="tab-opcional-menor disabled-tab" style="display: none;">
              <a href="#mn_ocupacion" data-toggle="tab">Ocupación Y Estudios</a>
            </li>
            <li data-tab-name="direccion" class="tab-opcional-menor disabled-tab" style="display: none;">
              <a href="#mn_direccion" data-toggle="tab">Dirección de Residencia</a>
            </li>
            <li data-tab-name="salud_otros" class="disabled-tab">
              <a href="#mn_salud" data-toggle="tab">Salud y Otros Datos</a>
            </li>
          </ul>

          <form id="formAjaxPacienteMenor" novalidate autocomplete="off">
            <input type="hidden" name="ext_es_menor" id="ext_es_menor" value="1">

            <div class="tab-content" style="padding-top: 15px;">

              <div class="tab-pane active" id="mn_info">
                <section style="margin-bottom: 10px;">
                  <div class="row">
                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Cédula / Documento (*):</p>
                      <div class="input-group">
                        <span class="input-group-addon" style="padding: 0; border: none;">
                          <select name="ext_tipo_cedula" id="ext_tipo_cedula" class="form-control" style="width: 75px; height: 34px; font-weight: bold; background-color: #f4f4f4; border-right: none; border-top-right-radius: 0; border-bottom-right-radius: 0;">
                            <option value="PN">PN-</option>
                            <option value="V">V-</option>
                            <option value="RP">REP-</option>
                          </select>
                        </span>
                        <input type="text" class="form-control" name="ext_cedula" id="ext_cedula" placeholder="N° de documento" maxlength="20">
                      </div>
                    </div>

                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Nombre (*):</p>
                      <input type="text" class="form-control" name="ext_nombre" id="ext_nombre" placeholder="Nombre Del Paciente" maxlength="100">
                    </div>

                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Apellido:</p>
                      <input type="text" class="form-control" name="ext_apellido" id="ext_apellido" placeholder="Apellido Del Paciente" maxlength="100">
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-4 col-sm-5 form-group">
                      <p>Fecha de nacimiento / Edad (*):</p>
                      <div class="input-group">
                        <input type="date" class="form-control" id="ext_fecha_nacimiento" name="ext_fecha_nacimiento" style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                        <span class="input-group-addon" style="padding: 0; border: none;">
                          <input type="text" class="form-control" id="ext_edad" name="ext_edad" readonly placeholder="Edad" style="width: 120px; height: 34px; text-align: center; background-color: #f4f4f4; font-weight: bold; border-left: none; border-top-left-radius: 0; border-bottom-left-radius: 0;">
                        </span>
                      </div>
                    </div>

                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Sexo (*):</p>
                      <select name="ext_genero" id="ext_genero" class="form-control">
                        <option value="">--- Seleccione Un Género ---</option>
                        <option value="Masculino">Masculino</option>
                        <option value="Femenino">Femenino</option>
                      </select>
                    </div>

                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Analfabeta (*):</p>
                      <select name="ext_analfabeta" id="ext_analfabeta" class="form-control">
                        <option value="">--- Seleccione Una Opción ---</option>
                        <option value="Si">Si</option>
                        <option value="No">No</option>
                      </select>
                    </div>
                  </div>

                  <div class="row solo-interno">
                    <div class="col-md-4 col-sm-4 form-group">
                      <p>País de nacimiento (*):</p>
                      <select name="ext_pais_nacimiento" id="ext_pais_nacimiento" class="form-control">
                        <option value="">--- Seleccione un País ---</option>
                        <?php
                        $result_nac_menor = $conexion->query("SELECT Id_Pais, nombre_pais FROM pais");
                        if ($result_nac_menor) {
                          while ($row_nac_menor = $result_nac_menor->fetch_assoc()) {
                            echo "<option value='{$row_nac_menor['Id_Pais']}'>{$row_nac_menor['nombre_pais']}</option>";
                          }
                        }
                        ?>
                      </select>
                    </div>

                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Estado de nacimiento (*):</p>
                      <select name="ext_estado_nacimiento" id="ext_estado_nacimiento" class="form-control">
                        <option value="">--- Seleccione Un Estado ---</option>
                      </select>
                    </div>

                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Municipio de nacimiento (*):</p>
                      <select name="ext_municipio_nacimiento" id="ext_municipio_nacimiento" class="form-control">
                        <option value="">--- Seleccione Un Municipio ---</option>
                      </select>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Etnia:</p>
                      <div class="input-group">
                        <span class="input-group-addon" style="padding: 0; border: none;">
                          <select name="ext_etnia" id="ext_etnia" class="form-control" style="width: 70px; height: 34px; font-weight: bold; background-color: #f4f4f4; border-right: none; border-top-right-radius: 0; border-bottom-right-radius: 0;">
                            <option value="No">No</option>
                            <option value="Si">Si</option>
                          </select>
                        </span>
                        <select name="ext_tipo_etnia" id="ext_tipo_etnia" class="form-control" style="border-top-left-radius: 0; border-bottom-left-radius: 0;" disabled>
                          <option value="">--- Seleccione Una Etnia ---</option>
                          <option value="wayuu">Wayuu</option>
                          <option value="añu">Añu</option>
                          <option value="baniva">Baniva</option>
                          <option value="kurripako">Kurripako</option>
                          <option value="piapoco">Piapoco</option>
                          <option value="warekena">Warekena</option>
                          <option value="bare">Bare</option>
                          <option value="pemon">Pemon</option>
                          <option value="kariña">Kariña</option>
                          <option value="panare">Panare</option>
                          <option value="yukpa">Yukpa</option>
                          <option value="japreira">Japreira</option>
                          <option value="yekuana">Yekuana</option>
                          <option value="chaima">Chaima</option>
                          <option value="bari">Barí</option>
                          <option value="yanomami">Yanomami</option>
                          <option value="sanema">Sanema</option>
                          <option value="warao">Warao</option>
                          <option value="pume">Pumé</option>
                          <option value="piaroa">Piaroa</option>
                          <option value="otro">Otro/No Aplica</option>
                          <option value="ninguna">Ninguna</option>
                        </select>
                      </div>
                    </div>
                  </div>
                </section>
              </div>

              <div class="tab-pane" id="mn_ocupacion">
                <section style="margin-bottom: 10px;">
                  <div class="row">
                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Nivel de instrucción:</p>
                      <select name="ext_nivel_instruccion" id="ext_nivel_instruccion" class="form-control">
                        <option value="">--- Seleccione Un Nivel ---</option>
                        <option value="sin_instruccion">Sin Instrucción</option>
                        <option value="primaria_incompleta">Primaria Incompleta</option>
                        <option value="primaria_completa">Primaria Completa</option>
                        <option value="bachillerato_incompleto">Educación Media Incompleta</option>
                        <option value="bachiller">Bachiller (Media General)</option>
                      </select>
                    </div>

                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Misión educativa:</p>
                      <select name="ext_mision" id="ext_mision" class="form-control">
                        <option value="">--- Seleccione Una Misión ---</option>
                        <option value="robinson">Robinson</option>
                        <option value="ribas">Ribas</option>
                        <option value="sucre">Sucre</option>
                      </select>
                    </div>

                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Años aprobados:</p>
                      <input type="number" id="ext_anos_aprobados" class="form-control" name="ext_anos_aprobados" min="0" max="10">
                      <p class="text-muted" id="ext_anos_help" style="margin-top: 5px;">Máximo:</p>
                    </div>
                  </div>
                </section>
              </div>

              <div class="tab-pane" id="mn_direccion">
                <section style="margin-bottom: 10px;">
                  <div class="row">
                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Estado (*):</p>
                      <select name="ext_estado" id="ext_estado" class="form-control">
                        <option value="">--- Seleccione Un Estado ---</option>
                        <?php
                        $result_dir_menor = $conexion->query("SELECT Id_Estado, nombre_estado FROM estado WHERE Id_Pais = 1");
                        if ($result_dir_menor) {
                          while ($row_dir_menor = $result_dir_menor->fetch_assoc()) {
                            echo "<option value='{$row_dir_menor['Id_Estado']}'>{$row_dir_menor['nombre_estado']}</option>";
                          }
                        }
                        ?>
                      </select>
                    </div>

                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Municipio (*):</p>
                      <select name="ext_municipio" id="ext_municipio" class="form-control">
                        <option value="">--- Seleccione Un Municipio ---</option>
                      </select>
                    </div>

                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Sector (*):</p>
                      <select name="ext_sector" id="ext_sector" class="form-control">
                        <option value="">--- Seleccione Un Sector ---</option>
                      </select>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-4 col-sm-6 form-group">
                      <p>Avenida/calle:</p>
                      <input type="text" class="form-control" name="ext_avenida_calle" id="ext_avenida_calle">
                    </div>

                    <div class="col-md-4 col-sm-6 form-group">
                      <p>Punto de referencia:</p>
                      <input type="text" class="form-control" name="ext_referencia" id="ext_referencia">
                    </div>

                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Tiempo de residencia:</p>
                      <div class="input-group">
                        <input type="text" class="form-control" name="ext_tiempo_residencia" id="ext_tiempo_residencia" placeholder="Cantidad" style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                        <span class="input-group-addon" style="padding: 0; border: none;">
                          <select name="ext_tiempo" id="ext_tiempo" class="form-control" style="width: 110px; height: 34px; background-color: #f4f4f4; border-left: none; border-top-left-radius: 0; border-bottom-left-radius: 0;">
                            <option value="dia/s">Dia/s</option>
                            <option value="semanas/s">Semanas/s</option>
                            <option value="meses/s">Meses/s</option>
                            <option value="años/s">Año/s</option>
                          </select>
                        </span>
                      </div>
                    </div>
                  </div>
                </section>
              </div>

              <div class="tab-pane" id="mn_salud">
                <section style="margin-bottom: 0; padding-top: 5px;">
                  <div class="row">
                    <div class="col-sm-4 form-group solo-interno-salud">
                      <p>Patologías detectadas previamente:</p>
                      <span id="wrap_btn_modal_pat_mn" title="Ninguna seleccionada" style="display: block;">
                        <button type="button" class="btn btn-info btn-block" id="btn_modal_pat_mn" data-target="#modalPatologiasMenor" style="width: 100%;">
                          <i class="fa fa-medkit"></i> Gestionar Patologías
                        </button>
                      </span>
                      <input type="hidden" name="ext_patologias_data" id="ext_patologias_ids" value="">
                    </div>

                    <div class="col-sm-4 form-group solo-interno-salud">
                      <p>Alergías detectadas previamente:</p>
                      <span id="wrap_btn_modal_ale_mn" title="Ninguna seleccionada" style="display: block;">
                        <button type="button" class="btn btn-info btn-block" id="btn_modal_ale_mn" data-target="#modalAlergiasMenor" style="width: 100%;">
                          <i class="fa fa-wheelchair"></i> Gestionar Alergias
                        </button>
                      </span>
                      <input type="hidden" name="ext_alergias_data" id="ext_alergias_ids" value="">
                    </div>

                    <div class="col-sm-4 form-group">
                      <p>Representante Legal / Responsable (*):</p>
                      <span id="wrap_btn_modal_rep_mn" title="Debe registrar al menos un representante" style="display: block;">
                        <button type="button" class="btn btn-info btn-block" id="btn_modal_rep_mn" data-target="#modalRepresentantesMenor" style="width: 100%;">
                          <i class="fa fa-users"></i> Gestionar Representantes
                        </button>
                      </span>
                      <input type="hidden" name="ext_representantes_data" id="ext_representantes_ids" value="">
                    </div>

                    <div class="col-sm-4 form-group">
                      <p>Tipo de discapacidad:</p>
                      <div style="display: flex; gap: 5px;">
                        <select name="ext_discapacidad" id="ext_discapacidad" class="form-control" style="width: 70px; flex-shrink: 0;">
                          <option value="No">No</option>
                          <option value="Si">Si</option>
                        </select>
                        <span id="wrap_btn_modal_disc_mn" title="Ninguna seleccionada" style="flex-grow: 1; margin: 0; display: block;">
                          <button type="button" class="btn btn-second btn-block" id="btn_modal_disc_mn" data-target="#modalDiscapacidadesMenor" style="width: 100%;" disabled>
                            <i class="fa fa-wheelchair"></i> Gestionar Discapacidades
                          </button>
                        </span>
                      </div>
                      <input type="hidden" name="ext_discapacidades_data" id="ext_discapacidades_ids" value="">
                    </div>

                    <div class="col-sm-4 form-group">
                      <p>Grupo Sanguíneo (*):</p>
                      <select name="ext_grupo_sanguineo" id="ext_grupo_sanguineo" class="form-control">
                        <option value="">--- Seleccione ---</option>
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                      </select>
                    </div>
                  </div>
                </section>
              </div>

            </div>
          </form>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-second" id="btnCancelarMenorModal">Cancelar</button>
        <button type="button" class="btn btn-second" id="btnAtrasMenorModal" style="display: none;">Atrás</button>
        <button type="button" class="btn btn-primary" id="btnSiguienteMenorModal">Siguiente</button>
        <button type="button" class="btn btn-success" id="btnGuardarMenorAjax" style="display: none;"><i class="fa fa-save"></i> Guardar y Usar</button>
      </div>
    </div>
  </div>
</div>

<!-- ===================== SUB-MODALES ===================== -->

<div class="modal fade" id="modalPatologiasMenor" role="dialog" style="z-index: 1070;">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h4 class="modal-title"><i class="fa fa-medkit"></i> Gestionar Patologías</h4>
      </div>
      <div class="modal-body">
        <div id="contenedor_filas_patologias_mn"></div>
        <button type="button" class="btn btn-success btn-sm" id="add_fila_pat_mn"><i class="fa fa-plus"></i> Añadir otra</button>
        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalNuevaPatologiaMenor"><i class="fa fa-plus-circle"></i> Nueva Patología</button>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" id="cancelar_pat_modal_mn">Cancelar</button>
        <button type="button" class="btn btn-primary" id="guardar_pat_listo_mn">Listo</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalBuscarPatMenor" role="dialog" style="z-index: 1080;">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h4 class="modal-title">Buscar Patología</h4>
      </div>
      <div class="modal-body">
        <input type="text" id="inputBuscarPatMenor" class="form-control" placeholder="Escriba para filtrar...">
        <div class="list-group" id="listaResultadosPatMenor" style="margin-top: 10px; max-height: 200px; overflow-y: auto;"></div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalAlergiasMenor" role="dialog" style="z-index: 1070;">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h4 class="modal-title"><i class="fa fa-wheelchair"></i> Gestionar Alergias</h4>
      </div>
      <div class="modal-body">
        <div id="contenedor_filas_alergias_mn"></div>
        <button type="button" class="btn btn-success btn-sm" id="add_fila_ale_mn"><i class="fa fa-plus"></i> Añadir otra</button>
        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalNuevaAlergiaMenor"><i class="fa fa-plus-circle"></i> Nueva Alergia</button>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" id="cancelar_ale_modal_mn">Cancelar</button>
        <button type="button" class="btn btn-primary" id="guardar_ale_listo_mn">Listo</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalBuscarAleMenor" role="dialog" style="z-index: 1080;">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h4 class="modal-title">Buscar Alergia</h4>
      </div>
      <div class="modal-body">
        <input type="text" id="inputBuscarAleMenor" class="form-control" placeholder="Escriba para filtrar...">
        <div class="list-group" id="listaResultadosAleMenor" style="margin-top: 10px; max-height: 200px; overflow-y: auto;"></div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalNuevaPatologiaMenor" role="dialog" style="z-index: 1080;">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h4 class="modal-title"><i class="fa fa-plus-circle"></i> Nueva Patología</h4>
      </div>
      <div class="modal-body">
        <form id="formAjaxPatologiaMenor">
          <div class="form-group"><label>Nombre de la Patología (*):</label><input type="text" class="form-control" id="ajax_nombre_pat_mn" required></div>
          <div class="form-group"><label>Código CIE (Opcional):</label><input type="text" class="form-control" id="ajax_cie_pat_mn"></div>
          <div class="form-group">
            <label>¿Es contagiosa?</label>
            <select class="form-control" id="ajax_contagiosa_pat_mn">
              <option value="NO">NO</option>
              <option value="SI">SI</option>
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btn_guardar_ajax_pat_mn">Guardar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalNuevaAlergiaMenor" role="dialog" style="z-index: 1080;">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h4 class="modal-title"><i class="fa fa-plus-circle"></i> Nueva Alergia</h4>
      </div>
      <div class="modal-body">
        <form id="formAjaxAlergiaMenor">
          <div class="form-group"><label>Nombre de la Alergia (*):</label><input type="text" class="form-control" id="ajax_nombre_ale_mn" required></div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btn_guardar_ajax_ale_mn">Guardar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalDiscapacidadesMenor" role="dialog" style="z-index: 1070;">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h4 class="modal-title"><i class="fa fa-wheelchair"></i> Gestionar Discapacidad</h4>
      </div>
      <div class="modal-body">
        <div class="row fila-disc-modal-mn">
          <div class="col-sm-6">
            <label>Tipo de Discapacidad (*):</label>
            <select class="form-control select-disc-dinamico-mn" id="select_disc_unica_mn"></select>
          </div>
          <div class="col-sm-4">
            <label>Fecha detección (Opcional):</label>
            <input type="date" class="form-control input-fecha-disc-dinamico-mn" id="fecha_disc_unica_mn" max="<?php echo date('Y-m-d'); ?>">
          </div>
          <div class="col-sm-2">
            <label style="display:block;">&nbsp;</label>
            <button type="button" class="btn btn-danger btn-remove-disc-modal-mn">
              <img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;">
            </button>
          </div>
        </div>
        <div style="margin-top: 15px;">
          <button type="button" class="btn btn-success btn-sm" id="btn_add_disc_row_mn"><i class="fa fa-plus-circle"></i> Añadir Otra Discapacidad</button>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" id="cancelar_disc_modal_mn">Cancelar</button>
        <button type="button" class="btn btn-primary" id="guardar_disc_listo_mn">Listo</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalRepresentantesMenor" role="dialog" style="z-index: 1070;">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h4 class="modal-title"><i class="fa fa-users"></i> Gestionar Representantes</h4>
      </div>
      <div class="modal-body">
        <div class="alert alert-info" style="padding: 8px 12px; margin-bottom: 10px;">
          <i class="fa fa-info-circle"></i> Debe registrar al menos un <b>Representante Legal</b> para el paciente menor.
        </div>
        <div id="contenedor_filas_representantes_mn"></div>
        <div style="margin-top: 10px;">
          <button type="button" class="btn btn-success btn-sm" id="add_fila_rep_mn"><i class="fa fa-plus"></i> Añadir otro</button>
          <button type="button" class="btn btn-primary btn-sm" id="btn_abrir_nuevo_representante_mn"><i class="fa fa-user-plus"></i> Nuevo Representante</button>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" id="cancelar_rep_modal_mn">Cancelar</button>
        <button type="button" class="btn btn-primary" id="guardar_rep_listo_mn">Listo</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalBuscarRepMenor" role="dialog" style="z-index: 1080;">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h4 class="modal-title"><i class="fa fa-search"></i> Buscar Representante</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>Buscar por Cédula o Nombre:</label>
          <input type="text" id="inputBuscarRepMenor" class="form-control" placeholder="Escriba la cédula o el nombre..." autocomplete="off">
        </div>
        <div class="list-group" id="listaResultadosRepMenor" style="margin-top: 10px; max-height: 300px; overflow-y: auto;"></div>
      </div>
    </div>
  </div>
</div>

<style>
  #tabsMenorModal li.disabled-tab {
    pointer-events: none;
    opacity: 0.5;
  }

  #tabsMenorModal li.disabled-tab a {
    color: #b2b2b2 !important;
    cursor: not-allowed;
  }

  #modalAgregarPacienteMenor input,
  #modalAgregarPacienteMenor select,
  #modalAgregarPacienteMenor textarea,
  #modalAgregarPacienteMenor button {
    pointer-events: auto !important;
  }

  #modalAgregarPacienteMenor .input-error {
    border: 2px solid crimson !important;
    box-shadow: 0 0 5px crimson !important;
    background-color: #fff5f5 !important;
  }

  #wrap_btn_modal_rep_mn,
  #wrap_btn_modal_disc_mn,
  #wrap_btn_modal_pat_mn,
  #wrap_btn_modal_ale_mn {
    cursor: help;
  }

  #avisoModal {
    z-index: 9999999 !important;
  }

  .tooltip {
    z-index: 10000000 !important;
  }
</style>

<script>
  window.LISTA_PATOLOGIAS_MENOR = <?php echo json_encode($lista_pat_menor); ?>;
  window.LISTA_ALERGIAS_MENOR = <?php echo json_encode($lista_ale_menor); ?>;
  window.LISTA_DISCAPACIDADES_MENOR = <?php echo json_encode($lista_disc_menor); ?>;

  if (typeof window.PARENTESCOS_DISPONIBLES === 'undefined') {
    window.PARENTESCOS_DISPONIBLES = [{
        val: 'Madre',
        txt: 'Madre'
      },
      {
        val: 'Padre',
        txt: 'Padre'
      },
      {
        val: 'Abuelo/a',
        txt: 'Abuelo/a'
      },
      {
        val: 'Tío/a',
        txt: 'Tío/a'
      },
      {
        val: 'Hermano/a',
        txt: 'Hermano/a'
      },
      {
        val: 'Tutor Legal',
        txt: 'Tutor Legal'
      },
      {
        val: 'Otro',
        txt: 'Otro'
      },
      {
        val: 'No aplica',
        txt: 'No aplica'
      }
    ];
  }
</script>

<script>
  (function() {
    'use strict';
    if (window.__modalPacienteMenorReestructurado) return;
    window.__modalPacienteMenorReestructurado = true;

    var TAB_IDS = {
      'info': 'mn_info',
      'ocupacion_estudios': 'mn_ocupacion',
      'direccion': 'mn_direccion',
      'salud_otros': 'mn_salud'
    };

    var limitesMision = {
      'robinson': 6,
      'ribas': 5,
      'sucre': 6
    };

    function getTodayISO() {
      var d = new Date();
      return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    }

    function mostrarAviso(msg) {
      if (typeof window.mostrarAviso === 'function') {
        window.mostrarAviso(msg);
      } else if ($('#avisoModal').length) {
        $('#avisoTexto').html(msg);
        $('#avisoModal').modal('show');
      } else {
        alert(msg.replace(/<[^>]+>/g, ''));
      }
    }

    function mostrarExito(msg) {
      if (typeof window.mostrarExito === 'function') {
        window.mostrarExito(msg);
      } else if (typeof window.mostrarAvisoExito === 'function') {
        window.mostrarAvisoExito(msg);
      } else {
        mostrarAviso(msg);
      }
    }

    // ====== MANEJO DE CÉDULA (V / PN / RP) — helpers fuera del ready ======
    function _tipoCedulaMenor() {
      var sel = document.querySelector('#modalAgregarPacienteMenor select[name="ext_tipo_cedula"]');
      return sel ? sel.value : 'PN';
    }

    function _inputCedulaMenor() {
      return document.querySelector('#modalAgregarPacienteMenor input[name="ext_cedula"]');
    }

    function _limiteCedulaMenor(tipo) {
      if (tipo === 'V') return 8;
      if (tipo === 'RP') return 10; // 8 dígitos + guion + 1 dígito
      return 20; // PN
    }

    function ajustarLimiteCedulaMenor() {
      var el = _inputCedulaMenor();
      if (!el) return;
      var tipo = _tipoCedulaMenor();
      var max = _limiteCedulaMenor(tipo);
      // Forzar AMBOS: propiedad y atributo
      el.maxLength = max;
      el.setAttribute('maxlength', max);
      if (tipo === 'RP') {
        el.setAttribute('placeholder', 'Ej: 12345678-9');
      } else {
        el.setAttribute('placeholder', 'Máx. ' + max + ' dígitos');
      }
    }

    function filtrarCedulaMenor() {
      var el = _inputCedulaMenor();
      if (!el) return;
      var tipo = _tipoCedulaMenor();
      var max = _limiteCedulaMenor(tipo);

      // CRÍTICO: re-forzar maxlength en cada pulsación
      if (el.maxLength !== max) {
        el.maxLength = max;
        el.setAttribute('maxlength', max);
      }

      if (tipo === 'V') {
        el.value = el.value.replace(/[^0-9]/g, '').slice(0, 8);
        return;
      }
      if (tipo === 'PN') {
        el.value = el.value.replace(/[^0-9]/g, '').slice(0, 20);
        return;
      }
      if (tipo === 'RP') {
        var raw = el.value;
        var teniaGuion = raw.indexOf('-') > -1;
        var solo = raw.replace(/[^0-9]/g, '').slice(0, 9);
        if (solo.length > 8) {
          el.value = solo.substring(0, 8) + '-' + solo.substring(8, 9);
        } else if (solo.length === 8 && teniaGuion) {
          el.value = solo + '-';
        } else {
          el.value = solo;
        }
      }
    }

    function actualizarTooltip($wrap, texto) {
      var t = texto || 'Ninguna seleccionada';
      // Sincroniza title y data-original-title
      $wrap.attr('title', t).attr('data-original-title', t);
      if ($wrap.data('bs.tooltip')) {
        // fixTitle mueve title -> data-original-title sin destruir el tooltip
        $wrap.tooltip('fixTitle');
        // Si el tooltip está visible ahora mismo, actualiza el texto mostrado
        var tip = $wrap.data('bs.tooltip').tip;
        if (tip && tip.hasClass('in')) {
          tip.find('.tooltip-inner').text(t);
        }
      } else {
        $wrap.tooltip({
          container: 'body',
          placement: 'top'
        });
      }
    }

    function inicializarTooltipsMenor() {
      $('#wrap_btn_modal_pat_mn, #wrap_btn_modal_ale_mn, #wrap_btn_modal_disc_mn, #wrap_btn_modal_rep_mn')
        .each(function() {
          var $el = $(this);
          // No borres el title si ya hay un data-original-title con texto
          var texto = $el.attr('data-original-title') || $el.attr('title') || 'Ninguna seleccionada';
          $el.attr('title', texto).attr('data-original-title', texto);
          if ($el.data('bs.tooltip')) {
            $el.tooltip('fixTitle');
          } else {
            $el.tooltip({
              container: 'body',
              placement: 'top'
            });
          }
        });
    }

    function setMaxFecha18Menor() {
      var hoy = new Date();
      var limite18 = new Date(hoy.getFullYear() - 18, hoy.getMonth(), hoy.getDate());
      limite18.setDate(limite18.getDate() + 1);
      var yMin = limite18.getFullYear();
      var mMin = String(limite18.getMonth() + 1).padStart(2, '0');
      var dMin = String(limite18.getDate()).padStart(2, '0');
      var inp = document.querySelector('#modalAgregarPacienteMenor #ext_fecha_nacimiento');
      if (inp) inp.setAttribute('min', yMin + '-' + mMin + '-' + dMin);

      var yMax = hoy.getFullYear();
      var mMax = String(hoy.getMonth() + 1).padStart(2, '0');
      var dMax = String(hoy.getDate()).padStart(2, '0');
      if (inp) inp.setAttribute('max', yMax + '-' + mMax + '-' + dMax);
    }

    function calcularEdadMenor() {
      var $f = $('#modalAgregarPacienteMenor #ext_fecha_nacimiento');
      var $e = $('#modalAgregarPacienteMenor #ext_edad');
      var val = $f.val();
      $f.removeClass('input-error');
      $e.val('').removeAttr('data-edad-anios');
      if (!val) return;

      var fNac = new Date(val + 'T00:00:00');
      var hoy = new Date();
      hoy.setHours(0, 0, 0, 0);

      if (fNac > hoy) {
        $f.addClass('input-error');
        mostrarAviso("La fecha de nacimiento no puede ser posterior a la fecha actual.");
        $f.val('');
        return;
      }
      if (fNac.getFullYear() < 1900) {
        $f.addClass('input-error');
        mostrarAviso("La fecha de nacimiento no puede ser anterior a 1900.");
        $f.val('');
        return;
      }

      var anios = hoy.getFullYear() - fNac.getFullYear();
      var meses = hoy.getMonth() - fNac.getMonth();
      var dias = hoy.getDate() - fNac.getDate();
      if (dias < 0) {
        meses--;
        var mesAnt = new Date(hoy.getFullYear(), hoy.getMonth(), 0);
        dias += mesAnt.getDate();
      }
      if (meses < 0) {
        anios--;
        meses += 12;
      }

      var texto;
      if (anios >= 1) texto = anios + (anios === 1 ? ' año' : ' años');
      else if (meses >= 1) texto = meses + (meses === 1 ? ' mes' : ' meses');
      else texto = dias + (dias === 1 ? ' día' : ' días');

      if (anios >= 18) {
        mostrarAviso("Este formulario es solo para <b>menores de 18 años</b>.<br>La edad calculada es: <b>" + texto + "</b>.");
        $e.val('Mayor de edad (' + anios + ' años)').attr('data-edad-anios', anios).css('color', 'crimson');
        $f.addClass('input-error');
        return;
      }

      $e.val(texto).attr('data-edad-anios', anios).css('color', '#333');
    }

    function verificarCedulaDuplicadaMenor() {
      return new Promise(function(resolve) {
        var tipo = $('#modalAgregarPacienteMenor #ext_tipo_cedula').val();
        var ced = $('#modalAgregarPacienteMenor #ext_cedula').val().trim();
        if (!ced) {
          resolve(true);
          return;
        }

        $.ajax({
          url: '../../cfg/ajax/verificar_existencia_cedula.php',
          method: 'POST',
          dataType: 'json',
          data: {
            tipo_cedula: tipo,
            cedula: ced
          },
          success: function(r) {
            if (r && r.existe_cedula) {
              $('#modalAgregarPacienteMenor #ext_cedula').addClass('input-error');
              mostrarAviso('El documento <b>' + tipo + '-' + ced + '</b> ya se encuentra registrado en el sistema.');
              resolve(false);
            } else {
              $('#modalAgregarPacienteMenor #ext_cedula').removeClass('input-error');
              resolve(true);
            }
          },
          error: function() {
            resolve(true);
          }
        });
      });
    }

    // ====== TOGGLE INTERNO/EXTERNO ======
    function toggleInternoExternoMenor() {
      var esInterno = $('#chk_menor_interno').is(':checked');
      var tabActivo = $('#tabsMenorModal li.active').data('tab-name');

      if (esInterno) {
        $('.tab-opcional-menor').show();
        $('.solo-interno').show();
        $('.solo-interno-salud').show();
      } else {
        $('.tab-opcional-menor').hide();
        $('.solo-interno').hide();
        $('.solo-interno-salud').hide();
      }

      if (tabActivo === 'salud_otros') {
        $('#tabsMenorModal li[data-tab-name="salud_otros"]').addClass('disabled-tab');
        activarTabMenor('info');
      } else if ((tabActivo === 'ocupacion_estudios' || tabActivo === 'direccion') && !esInterno) {
        activarTabMenor('info');
      }
    }

    // ====== MANEJO DE PESTAÑAS ======
    function marcarTabActivoMenor(name) {
      var tabId = TAB_IDS[name];
      if (!tabId) return;
      $('#tabsMenorModal li').removeClass('active');
      $('#tabsMenorModal li[data-tab-name="' + name + '"]').addClass('active');
      Object.keys(TAB_IDS).forEach(function(k) {
        $('#' + TAB_IDS[k]).removeClass('active in');
      });
      $('#' + tabId).addClass('active in');
      actualizarBotonesFooterMenor();
    }

    function activarTabMenor(name) {
      marcarTabActivoMenor(name);
    }

    function desbloquearTabMenor(name) {
      $('#tabsMenorModal li[data-tab-name="' + name + '"]').removeClass('disabled-tab');
    }

    function actualizarBotonesFooterMenor() {
      var tabActual = $('#tabsMenorModal li.active').data('tab-name');
      var esPrimerTab = (tabActual === 'info');
      var esUltimoTab = (tabActual === 'salud_otros');

      $('#btnCancelarMenorModal').toggle(esPrimerTab);
      $('#btnAtrasMenorModal').toggle(!esPrimerTab);
      $('#btnSiguienteMenorModal').toggle(!esUltimoTab);
      $('#btnGuardarMenorAjax').toggle(esUltimoTab);
    }

    function siguienteTabParaMenor(tabActual) {
      var esInterno = $('#chk_menor_interno').is(':checked');
      if (tabActual === 'info') return esInterno ? 'ocupacion_estudios' : 'salud_otros';
      if (tabActual === 'ocupacion_estudios') return 'direccion';
      if (tabActual === 'direccion') return 'salud_otros';
      return null;
    }

    function tabAnteriorParaMenor(tabActual) {
      var esInterno = $('#chk_menor_interno').is(':checked');
      if (tabActual === 'salud_otros') return esInterno ? 'direccion' : 'info';
      if (tabActual === 'direccion') return 'ocupacion_estudios';
      if (tabActual === 'ocupacion_estudios') return 'info';
      return null;
    }

    // ====== CONSTRUCTORES DE OPCIONES ======
    function construirOpcionesPatologiasMenor() {
      var html = '<option value="">--- Seleccione una patología ---</option>';
      (window.LISTA_PATOLOGIAS_MENOR || []).slice().sort(function(a, b) {
          return a.nombre.localeCompare(b.nombre);
        })
        .forEach(function(p) {
          html += '<option value="' + p.id + '" data-nombre="' + p.nombre + '" data-cie="' + (p.cie || '') + '">' + p.nombre + '</option>';
        });
      return html;
    }

    function construirOpcionesAlergiasMenor() {
      var html = '<option value="">--- Seleccione una alergia ---</option>';
      (window.LISTA_ALERGIAS_MENOR || []).slice().sort(function(a, b) {
          return a.nombre.localeCompare(b.nombre);
        })
        .forEach(function(a) {
          html += '<option value="' + a.id + '" data-nombre="' + a.nombre + '">' + a.nombre + '</option>';
        });
      return html;
    }

    function construirOpcionesDiscapacidadesMenor() {
      var html = '<option value="">--- Seleccione una discapacidad ---</option>';
      (window.LISTA_DISCAPACIDADES_MENOR || []).slice().sort(function(a, b) {
          return a.nombre.localeCompare(b.nombre);
        })
        .forEach(function(d) {
          html += '<option value="' + d.id + '" data-nombre="' + d.nombre + '">' + d.nombre + '</option>';
        });
      return html;
    }

    // ====== FILAS DINÁMICAS PATOLOGÍAS ======
    function agregarFilaPatologiaMenor() {
      var html = '<div class="row fila-pat-mn" style="margin-bottom: 10px;">' +
        '<div class="col-sm-6"><div class="input-group">' +
        '<select class="form-control select-pat-mn">' + construirOpcionesPatologiasMenor() + '</select>' +
        '<span class="input-group-btn"><button class="btn btn-info btn-search-pat-mn" type="button"><img src="../../recursos/imagenes/iconos/buscar.png" style="width:15px;height:15px;"></button></span>' +
        '</div></div>' +
        '<div class="col-sm-4"><input type="date" class="form-control input-fecha-pat-mn" max="' + getTodayISO() + '"></div>' +
        '<div class="col-sm-2"><button type="button" class="btn btn-danger btn-remove-pat-mn"><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px;height:15px;"></button></div>' +
        '</div>';
      $('#contenedor_filas_patologias_mn').append(html);
    }

    function agregarFilaAlergiaMenor() {
      var html = '<div class="row fila-ale-mn" style="margin-bottom: 10px;">' +
        '<div class="col-sm-6"><div class="input-group">' +
        '<select class="form-control select-ale-mn">' + construirOpcionesAlergiasMenor() + '</select>' +
        '<span class="input-group-btn"><button class="btn btn-info btn-search-ale-mn" type="button"><img src="../../recursos/imagenes/iconos/buscar.png" style="width:15px;height:15px;"></button></span>' +
        '</div></div>' +
        '<div class="col-sm-4"><input type="date" class="form-control input-fecha-ale-mn" max="' + getTodayISO() + '"></div>' +
        '<div class="col-sm-2"><button type="button" class="btn btn-danger btn-remove-ale-mn"><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px;height:15px;"></button></div>' +
        '</div>';
      $('#contenedor_filas_alergias_mn').append(html);
    }

    function agregarFilaDiscapacidadMenor() {
      var html = '<div class="row fila-disc-modal-mn" style="margin-top: 15px; padding-top: 15px; border-top: 1px dashed #ddd;">' +
        '<div class="col-sm-6"><label>Tipo de Discapacidad (*):</label><select class="form-control select-disc-dinamico-mn">' + construirOpcionesDiscapacidadesMenor() + '</select></div>' +
        '<div class="col-sm-4"><label>Fecha detección (Opcional):</label><input type="date" class="form-control input-fecha-disc-dinamico-mn" max="' + getTodayISO() + '"></div>' +
        '<div class="col-sm-2"><label style="display:block;">&nbsp;</label><button type="button" class="btn btn-danger btn-remove-disc-modal-mn"><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px;height:15px;"></button></div>' +
        '</div>';
      $('.fila-disc-modal-mn').last().after(html);
    }

    function agregarFilaRepMenor(id, tipo, nombre, parentesco) {
      id = id || '';
      tipo = tipo || 'Legal';
      nombre = nombre || '';
      parentesco = parentesco || '';
      var opciones = window.PARENTESCOS_DISPONIBLES || [];
      var optsHtml = '<option value="">--- Parentesco ---</option>';
      opciones.forEach(function(op) {
        optsHtml += '<option value="' + op.val + '" ' + (op.val === parentesco ? 'selected' : '') + '>' + op.txt + '</option>';
      });
      var html = '<div class="row fila-rep-mn" style="margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px dashed #ddd;">' +
        '<div class="col-sm-4"><div class="input-group">' +
        '<input type="text" class="form-control input-nombre-rep-mn" placeholder="--- Seleccione un representante ---" value="' + nombre + '" readonly style="background:#fff;">' +
        '<input type="hidden" class="input-id-rep-mn" value="' + id + '">' +
        '<span class="input-group-btn"><button class="btn btn-info btn-search-rep-mn" type="button"><img src="../../recursos/imagenes/iconos/buscar.png" style="width:15px;height:15px;"></button></span>' +
        '</div></div>' +
        '<div class="col-sm-3"><select class="form-control select-tipo-rep-mn"><option value="Legal" ' + (tipo === 'Legal' ? 'selected' : '') + '>Representante Legal</option><option value="Suplente" ' + (tipo === 'Suplente' ? 'selected' : '') + '>Representante Suplente</option></select></div>' +
        '<div class="col-sm-3"><select class="form-control select-parentesco-rep-mn">' + optsHtml + '</select></div>' +
        '<div class="col-sm-2"><button type="button" class="btn btn-danger btn-remove-rep-mn"><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px;height:15px;"></button></div>' +
        '</div>';
      $('#contenedor_filas_representantes_mn').append(html);
    }

    function validarTabMenor(tabName) {
      var errores = [];
      var esInterno = $('#chk_menor_interno').is(':checked');
      var tabId = TAB_IDS[tabName];
      if (tabId) $('#' + tabId).find('.input-error').removeClass('input-error');

      function req(sel, label) {
        var $i = $('#modalAgregarPacienteMenor ' + sel);
        if (!$i.is(':visible')) return;
        var val = $i.val();
        if (val === null || val === undefined) val = '';
        val = String(val).trim();
        if (val === '' || val.indexOf('--- Seleccione') === 0) {
          $i.addClass('input-error');
          errores.push(label);
        }
      }

      if (tabName === 'info') {
        req('#ext_cedula', 'Cédula');
        req('#ext_nombre', 'Nombre');
        req('#ext_fecha_nacimiento', 'Fecha de nacimiento');
        req('#ext_genero', 'Sexo');
        req('#ext_analfabeta', 'Analfabeta');
        if (esInterno) {
          req('#ext_pais_nacimiento', 'País de nacimiento');
          req('#ext_estado_nacimiento', 'Estado de nacimiento');
          req('#ext_municipio_nacimiento', 'Municipio de nacimiento');
        }
        var edad = parseInt($('#modalAgregarPacienteMenor #ext_edad').attr('data-edad-anios'));
        if (isNaN(edad) || edad < 0 || edad >= 18) {
          $('#modalAgregarPacienteMenor #ext_fecha_nacimiento').addClass('input-error');
          errores.push('El paciente debe ser menor de 18 años');
        }
        var tipo = $('#modalAgregarPacienteMenor #ext_tipo_cedula').val();
        var num = parseInt($('#modalAgregarPacienteMenor #ext_cedula').val());
        if (tipo === 'V' && !isNaN(num) && num > 80000000) {
          $('#modalAgregarPacienteMenor #ext_cedula').addClass('input-error');
          errores.push('Cédula V- no puede ser mayor a 80.000.000');
        }
        if (tipo === 'RP') {
          var regexRP = /^[0-9]{8}-[0-9]{1}$/;
          if (!regexRP.test($('#modalAgregarPacienteMenor #ext_cedula').val())) {
            $('#modalAgregarPacienteMenor #ext_cedula').addClass('input-error');
            errores.push('Formato REP incorrecto. Ejemplo: 12345678-1');
          }
        }
      }

      if (tabName === 'direccion' && esInterno) {
        req('#ext_estado', 'Estado (dirección)');
        req('#ext_municipio', 'Municipio (dirección)');
        req('#ext_sector', 'Sector');
      }

      if (tabName === 'salud_otros') {
        var $m = $('#modalAgregarPacienteMenor');
        // Auto-reset si discapacidad = Si pero sin datos
        if ($m.find('#ext_discapacidad').val() === 'Si') {
          var dRaw = $m.find('#ext_discapacidades_ids').val();
          var dList = [];
          try {
            dList = JSON.parse(dRaw || '[]');
          } catch (e) {
            dList = [];
          }
          if (!Array.isArray(dList) || dList.length === 0) {
            $m.find('#ext_discapacidad').val('No');
            $m.find('#btn_modal_disc_mn').prop('disabled', true).removeClass('btn-info').addClass('btn-second');
            actualizarTooltip($('#wrap_btn_modal_disc_mn'), 'Ninguna seleccionada');
          }
        }

        req('#ext_grupo_sanguineo', 'Grupo sanguíneo');
        var repIdsRaw = $m.find('#ext_representantes_ids').val();
        var repLista = [];
        try {
          repLista = JSON.parse(repIdsRaw || '[]');
        } catch (e) {
          repLista = [];
        }
        if (!Array.isArray(repLista) || repLista.length === 0) {
          $m.find('#btn_modal_rep_mn').addClass('btn-error-sombreado');
          errores.push('Debe registrar al menos un <b>Representante</b> (obligatorio para menores).');
        } else {
          $m.find('#btn_modal_rep_mn').removeClass('btn-error-sombreado');
        }
      }

      if (errores.length > 0) {
        mostrarAviso('Verifique los siguientes errores:<ul><li>' + errores.join('</li><li>') + '</li></ul>');
        return false;
      }
      return true;
    }

    function limpiarModalMenor() {
      var $form = $('#formAjaxPacienteMenor');
      if ($form.length) {
        $form[0].reset();
        $form.find('.input-error').removeClass('input-error');
      }
      var $m = $('#modalAgregarPacienteMenor');
      $m.find('#ext_estado_nacimiento').html('<option value="">--- Seleccione Un Estado ---</option>');
      $m.find('#ext_municipio_nacimiento').html('<option value="">--- Seleccione Un Municipio ---</option>');
      $m.find('#ext_municipio').html('<option value="">--- Seleccione Un Municipio ---</option>');
      $m.find('#ext_sector').html('<option value="">--- Seleccione Un Sector ---</option>');
      $m.find('#ext_tipo_etnia').prop('disabled', true).val('');
      $m.find('#ext_edad').val('').removeAttr('data-edad-anios');
      $m.find('#chk_menor_interno').prop('checked', false);
      $('.tab-opcional-menor').hide();
      $('.solo-interno').hide();
      $('.solo-interno-salud').hide();

      $('#tabsMenorModal li').removeClass('disabled-tab');
      $('#tabsMenorModal li[data-tab-name="ocupacion_estudios"]').addClass('disabled-tab');
      $('#tabsMenorModal li[data-tab-name="direccion"]').addClass('disabled-tab');
      $('#tabsMenorModal li[data-tab-name="salud_otros"]').addClass('disabled-tab');
      marcarTabActivoMenor('info');

      $m.find('#ext_patologias_ids').val('');
      $m.find('#ext_alergias_ids').val('');
      $m.find('#ext_discapacidades_ids').val('');
      $m.find('#ext_representantes_ids').val('');
      actualizarTooltip($('#wrap_btn_modal_pat_mn'), 'Ninguna seleccionada');
      actualizarTooltip($('#wrap_btn_modal_ale_mn'), 'Ninguna seleccionada');
      actualizarTooltip($('#wrap_btn_modal_disc_mn'), 'Ninguna seleccionada');
      actualizarTooltip($('#wrap_btn_modal_rep_mn'), 'Debe registrar al menos un representante');
      $m.find('#btn_modal_disc_mn').prop('disabled', true).removeClass('btn-info').addClass('btn-second');
      $m.find('#ext_discapacidad').val('No');

      $('#contenedor_filas_patologias_mn').empty();
      $('#contenedor_filas_alergias_mn').empty();
      $('#contenedor_filas_representantes_mn').empty();
      $('#modalDiscapacidadesMenor .fila-disc-modal-mn').not(':first').remove();
      $('#select_disc_unica_mn').val('').html(construirOpcionesDiscapacidadesMenor());
      $('#fecha_disc_unica_mn').val('');

      // Recrear tooltips con el texto por defecto
      $('#wrap_btn_modal_pat_mn, #wrap_btn_modal_ale_mn, #wrap_btn_modal_disc_mn, #wrap_btn_modal_rep_mn')
        .each(function() {
          var $el = $(this);
          var t = $el.attr('data-original-title') || 'Ninguna seleccionada';
          $el.tooltip('destroy').attr('title', t).attr('data-original-title', t).tooltip({
            container: 'body',
            placement: 'top'
          });
        });

      actualizarBotonesFooterMenor();
      ajustarLimiteCedulaMenor();
    }

    $(document).ready(function() {
      setMaxFecha18Menor();

      $(document).on('input', '#modalAgregarPacienteMenor input[name="ext_cedula"]', filtrarCedulaMenor);

      $(document).on('change', '#modalAgregarPacienteMenor select[name="ext_tipo_cedula"]', function() {
        var inp = _inputCedulaMenor();
        if (inp) {
          inp.value = '';
          inp.classList.remove('input-error');
        }
        ajustarLimiteCedulaMenor();
      });

      $(document).on('input', '#modalAgregarPacienteMenor #ext_tiempo_residencia', function() {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 4);
      });

      $(document).on('input', '#modalAgregarPacienteMenor #ext_nombre, #modalAgregarPacienteMenor #ext_apellido', function() {
        this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]/g, '');
      });

      $(document).on('input', '#modalAgregarPacienteMenor #ext_avenida_calle, #modalAgregarPacienteMenor #ext_referencia', function() {
        this.value = this.value.replace(/[^a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s#\-\.\,]/g, '');
      });

      $(document).on('change blur', '#modalAgregarPacienteMenor #ext_fecha_nacimiento', calcularEdadMenor);
      $(document).on('change', '#chk_menor_interno', toggleInternoExternoMenor);

      $(document).on('change', '#modalAgregarPacienteMenor #ext_etnia', function() {
        if ($(this).val() === 'Si') $('#modalAgregarPacienteMenor #ext_tipo_etnia').prop('disabled', false);
        else $('#modalAgregarPacienteMenor #ext_tipo_etnia').prop('disabled', true).val('');
      });

      $(document).on('change', '#modalAgregarPacienteMenor #ext_pais_nacimiento', function() {
        var $m = $('#modalAgregarPacienteMenor');
        var paisId = $(this).val();
        var $e = $m.find('#ext_estado_nacimiento');
        var $mun = $m.find('#ext_municipio_nacimiento');
        $e.html('<option value="">--- Seleccione Un Estado ---</option>');
        $mun.html('<option value="">--- Seleccione Un Municipio ---</option>');
        if (!paisId) return;
        $.ajax({
          url: '../../cfg/ajax/obtener_estados.php',
          method: 'GET',
          data: {
            Id_Pais: paisId
          },
          dataType: 'json',
          cache: false,
          success: function(data) {
            if (!Array.isArray(data)) return;
            data.forEach(function(e) {
              $e.append('<option value="' + e.Id_Estado + '">' + e.nombre_estado + '</option>');
            });
          }
        });
      });

      $(document).on('change', '#modalAgregarPacienteMenor #ext_estado_nacimiento', function() {
        var $m = $('#modalAgregarPacienteMenor');
        var id = $(this).val();
        var $mun = $m.find('#ext_municipio_nacimiento');
        $mun.html('<option value="">--- Seleccione Un Municipio ---');
        if (!id) return;
        $.ajax({
          url: '../../cfg/ajax/obtener_municipios.php',
          method: 'GET',
          data: {
            Id_Estado: id
          },
          dataType: 'json',
          cache: false,
          success: function(data) {
            if (!Array.isArray(data)) return;
            data.forEach(function(m) {
              $mun.append('<option value="' + m.Id_Municipio + '">' + m.nombre_municipio + '</option>');
            });
          }
        });
      });

      $(document).on('change', '#modalAgregarPacienteMenor #ext_estado', function() {
        var $m = $('#modalAgregarPacienteMenor');
        var id = $(this).val();
        var $mun = $m.find('#ext_municipio');
        var $sec = $m.find('#ext_sector');
        $mun.html('<option value="">--- Seleccione Un Municipio ---</option>');
        $sec.html('<option value="">--- Seleccione Un Sector ---</option>');
        if (!id) return;
        $.ajax({
          url: '../../cfg/ajax/obtener_municipios.php',
          method: 'GET',
          data: {
            Id_Estado: id
          },
          dataType: 'json',
          cache: false,
          success: function(data) {
            if (!Array.isArray(data)) return;
            data.forEach(function(m) {
              $mun.append('<option value="' + m.Id_Municipio + '">' + m.nombre_municipio + '</option>');
            });
          }
        });
      });

      $(document).on('change', '#modalAgregarPacienteMenor #ext_municipio', function() {
        var $m = $('#modalAgregarPacienteMenor');
        var id = $(this).val();
        var $sec = $m.find('#ext_sector');
        $sec.html('<option value="">--- Seleccione Un Sector ---</option>');
        if (!id) return;
        $.ajax({
          url: '../../cfg/ajax/obtener_sectores.php',
          method: 'GET',
          data: {
            Id_Municipio: id
          },
          dataType: 'json',
          cache: false,
          success: function(data) {
            if (!Array.isArray(data)) return;
            data.forEach(function(s) {
              $sec.append('<option value="' + s.Id_Sector + '">' + s.nombre_sector + '</option>');
            });
          }
        });
      });

      $(document).on('change input', '.input-fecha-pat-mn, .input-fecha-ale-mn, .input-fecha-disc-dinamico-mn, #fecha_disc_unica_mn', function() {
        var v = $(this).val();
        if (!v) return;
        if (v > getTodayISO()) {
          $(this).val('');
          mostrarAviso('No se puede seleccionar una fecha futura.');
        }
      });

      $(document).on('blur', '#modalAgregarPacienteMenor #ext_cedula', verificarCedulaDuplicadaMenor);

      $(document).on('change', '#modalAgregarPacienteMenor #ext_mision', function() {
        var max = limitesMision[$(this).val()];
        var $anos = $('#modalAgregarPacienteMenor #ext_anos_aprobados');
        if (max) {
          $anos.attr('max', max).attr('placeholder', 'Máx: ' + max);
          $('#modalAgregarPacienteMenor #ext_anos_help').text('Duración máxima: ' + max + ' años.');
          if (parseInt($anos.val()) > max) $anos.val(max);
        } else {
          $anos.removeAttr('max').attr('placeholder', '');
          $('#modalAgregarPacienteMenor #ext_anos_help').text('');
        }
      });
      $(document).on('change', '#modalAgregarPacienteMenor #ext_discapacidad', function() {
        var $m = $('#modalAgregarPacienteMenor');
        if ($(this).val() === 'Si') {
          $m.find('#btn_modal_disc_mn').prop('disabled', false).removeClass('btn-second').addClass('btn-info');
        } else {
          $m.find('#btn_modal_disc_mn').prop('disabled', true).removeClass('btn-info').addClass('btn-second');
          $m.find('#ext_discapacidades_ids').val('[]');
          actualizarTooltip($('#wrap_btn_modal_disc_mn'), 'Ninguna seleccionada');
        }
      });

      $('#modalAgregarPacienteMenor').on('show.bs.modal', function() {
        ajustarLimiteCedulaMenor();
        inicializarTooltipsMenor();
        setMaxFecha18Menor();
        toggleInternoExternoMenor();
        actualizarBotonesFooterMenor();
        var tipoBuscado = $('select[name="tipo_cedula_externo"]').val() || $('#tipo_cedula_paciente').val() || 'PN';
        var cedulaBuscada = $('input[name="cedula_externo"]').val() || $('#cedula_paciente').val() || '';
        if (cedulaBuscada && cedulaBuscada.trim().length > 0) {
          if (['PN', 'V', 'RP'].indexOf(tipoBuscado) !== -1) {
            $('#modalAgregarPacienteMenor #ext_tipo_cedula').val(tipoBuscado);
          }
          $('#modalAgregarPacienteMenor #ext_cedula').val(cedulaBuscada.trim()).trigger('input');
        }
      });

      $('#modalAgregarPacienteMenor').on('hidden.bs.modal', function() {
        limpiarModalMenor();
        setTimeout(function() {
          if (!$('.modal.in').length) {
            $('body').removeClass('modal-open').css('padding-right', '');
            $('.modal-backdrop').remove();
          }
        }, 100);
      });

      $(document).on('click', '#btnCancelarMenorModal', function() {
        $('#modalAgregarPacienteMenor').modal('hide');
      });

      $(document).on('click', '#btnSiguienteMenorModal', async function() {
        var btn = $(this);
        var tabActual = $('#tabsMenorModal li.active').data('tab-name');
        if (!validarTabMenor(tabActual)) return;

        if (tabActual === 'info') {
          btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Verificando...');
          var cedOk = await verificarCedulaDuplicadaMenor();
          btn.prop('disabled', false).text('Siguiente');
          if (!cedOk) return;
        }

        var sig = siguienteTabParaMenor(tabActual);
        if (sig) {
          desbloquearTabMenor(sig);
          activarTabMenor(sig);
        }
      });

      $(document).on('click', '#btnAtrasMenorModal', function() {
        var ant = tabAnteriorParaMenor($('#tabsMenorModal li.active').data('tab-name'));
        if (ant) activarTabMenor(ant);
      });

      $(document).on('click', '#tabsMenorModal a[data-toggle="tab"]', function(e) {
        if ($(this).closest('li').hasClass('disabled-tab')) {
          e.preventDefault();
          return false;
        }
      });

      // ====== PATOLOGÍAS ======
      $(document).on('click', '#btn_modal_pat_mn', function() {
        $('#contenedor_filas_patologias_mn').empty();
        var data = $('#modalAgregarPacienteMenor #ext_patologias_ids').val();
        var loaded = false;
        if (data) {
          try {
            var lista = JSON.parse(data);
            if (Array.isArray(lista) && lista.length > 0) {
              lista.forEach(function(p) {
                agregarFilaPatologiaMenor();
                var $row = $('.fila-pat-mn').last();
                $row.find('.select-pat-mn').val(p.id);
                if (p.fecha) $row.find('.input-fecha-pat-mn').val(p.fecha);
              });
              loaded = true;
            }
          } catch (e) {}
        }
        if (!loaded) agregarFilaPatologiaMenor();
        $('#modalPatologiasMenor').modal('show');
      });
      $(document).on('click', '#add_fila_pat_mn', function() {
        var $u = $('.fila-pat-mn').last();
        if ($u.length && !$u.find('.select-pat-mn').val()) {
          mostrarAviso('Seleccione la patología actual antes de añadir otra.');
          return;
        }
        agregarFilaPatologiaMenor();
      });
      $(document).on('click', '.btn-remove-pat-mn', function() {
        var $f = $(this).closest('.fila-pat-mn');
        if ($f.find('.select-pat-mn').val() || $f.find('.input-fecha-pat-mn').val()) {
          $f.find('.select-pat-mn').val('');
          $f.find('.input-fecha-pat-mn').val('');
        } else if ($('.fila-pat-mn').length > 1) $f.remove();
      });
      $(document).on('change', '.select-pat-mn', function() {
        var v = $(this).val();
        if (!v) return;
        if ($('.select-pat-mn').filter(function() {
            return $(this).val() === v;
          }).length > 1) {
          mostrarAviso('Esa patología ya fue seleccionada.');
          $(this).val('');
        }
      });
      $(document).on('click', '#guardar_pat_listo_mn', function() {
        var sel = [],
          nom = [];
        $('.fila-pat-mn').each(function() {
          var $s = $(this).find('.select-pat-mn');
          var id = $s.val();
          if (!id) return;
          var f = $(this).find('.input-fecha-pat-mn').val();
          var opt = $s.find('option:selected');
          sel.push({
            id: parseInt(id),
            nombre: opt.data('nombre'),
            codigo_cie: opt.data('cie'),
            fecha: f
          });
          nom.push(opt.text().trim());
        });
        $('#modalAgregarPacienteMenor #ext_patologias_ids').val(sel.length ? JSON.stringify(sel) : '');
        actualizarTooltip($('#wrap_btn_modal_pat_mn'), nom.length ? nom.join(', ') : 'Ninguna seleccionada');
        $('#modalPatologiasMenor').modal('hide');
      });

      // Búsqueda Patología
      var targetPatMn = null;
      $(document).on('click', '.btn-search-pat-mn', function() {
        targetPatMn = $(this).closest('.input-group').find('.select-pat-mn');
        $('#modalBuscarPatMenor').modal('show');
        $('#inputBuscarPatMenor').val('').trigger('keyup');
      });
      $(document).on('keyup', '#inputBuscarPatMenor', function() {
        var t = $(this).val().toLowerCase();
        var html = '';
        $('.select-pat-mn:first option').not('[value=""]').each(function() {
          if ($(this).text().toLowerCase().indexOf(t) > -1)
            html += '<a href="#" class="list-group-item seleccionar-pat-mn" data-id="' + $(this).val() + '">' + $(this).text() + '</a>';
        });
        $('#listaResultadosPatMenor').html(html);
      });
      $(document).on('click', '.seleccionar-pat-mn', function(e) {
        e.preventDefault();
        targetPatMn.val($(this).data('id')).trigger('change');
        $('#modalBuscarPatMenor').modal('hide');
      });

      // Nueva Patología AJAX
      $(document).on('click', '#btn_guardar_ajax_pat_mn', function() {
        var nombre = $('#ajax_nombre_pat_mn').val().trim();
        var cie = $('#ajax_cie_pat_mn').val().trim();
        var cont = $('#ajax_contagiosa_pat_mn').val();
        if (!nombre) {
          mostrarAviso('El nombre es obligatorio.');
          return;
        }
        $.ajax({
          url: window.location.href,
          method: 'POST',
          data: {
            ajax_nueva_patologia: 1,
            nombre_patologia: nombre,
            codigo_cie: cie,
            enfermedad_contagiosa: cont
          },
          dataType: 'json',
          success: function(res) {
            if (res.success) {
              window.LISTA_PATOLOGIAS_MENOR.push({
                id: parseInt(res.id),
                nombre: res.nombre,
                cie: res.cie || ''
              });
              var opts = construirOpcionesPatologiasMenor();
              $('.select-pat-mn').each(function() {
                var v = $(this).val();
                $(this).html(opts);
                if (v) $(this).val(v);
              });
              var $vacio = $('.select-pat-mn').filter(function() {
                return !$(this).val();
              }).first();
              if ($vacio.length) $vacio.val(res.id);
              else agregarFilaPatologiaMenor();
              $('#modalNuevaPatologiaMenor').modal('hide');
              $('#formAjaxPatologiaMenor')[0].reset();
              mostrarExito('Patología guardada.');
            } else mostrarAviso(res.error);
          },
          error: function() {
            mostrarAviso('Error al guardar la patología.');
          }
        });
      });

      // ====== ALERGIAS ======
      $(document).on('click', '#btn_modal_ale_mn', function() {
        $('#contenedor_filas_alergias_mn').empty();
        var data = $('#modalAgregarPacienteMenor #ext_alergias_ids').val();
        var loaded = false;
        if (data) {
          try {
            var lista = JSON.parse(data);
            if (Array.isArray(lista) && lista.length > 0) {
              lista.forEach(function(a) {
                agregarFilaAlergiaMenor();
                var $row = $('.fila-ale-mn').last();
                $row.find('.select-ale-mn').val(a.id);
                if (a.fecha) $row.find('.input-fecha-ale-mn').val(a.fecha);
              });
              loaded = true;
            }
          } catch (e) {}
        }
        if (!loaded) agregarFilaAlergiaMenor();
        $('#modalAlergiasMenor').modal('show');
      })
      $(document).on('click', '#add_fila_ale_mn', function() {
        var $u = $('.fila-ale-mn').last();
        if ($u.length && !$u.find('.select-ale-mn').val()) {
          mostrarAviso('Seleccione la alergia actual antes de añadir otra.');
          return;
        }
        agregarFilaAlergiaMenor();
      });
      $(document).on('click', '.btn-remove-ale-mn', function() {
        var $f = $(this).closest('.fila-ale-mn');
        if ($f.find('.select-ale-mn').val() || $f.find('.input-fecha-ale-mn').val()) {
          $f.find('.select-ale-mn').val('');
          $f.find('.input-fecha-ale-mn').val('');
        } else if ($('.fila-ale-mn').length > 1) $f.remove();
      });
      $(document).on('change', '.select-ale-mn', function() {
        var v = $(this).val();
        if (!v) return;
        if ($('.select-ale-mn').filter(function() {
            return $(this).val() === v;
          }).length > 1) {
          mostrarAviso('Esa alergia ya fue seleccionada.');
          $(this).val('');
        }
      });
      $(document).on('click', '#guardar_ale_listo_mn', function() {
        var sel = [],
          nom = [];
        $('.fila-ale-mn').each(function() {
          var $s = $(this).find('.select-ale-mn');
          var id = $s.val();
          if (!id) return;
          var f = $(this).find('.input-fecha-ale-mn').val();
          var opt = $s.find('option:selected');
          sel.push({
            id: parseInt(id),
            nombre: opt.data('nombre'),
            fecha: f
          });
          nom.push(opt.text().trim());
        });
        $('#modalAgregarPacienteMenor #ext_alergias_ids').val(sel.length ? JSON.stringify(sel) : '');
        actualizarTooltip($('#wrap_btn_modal_ale_mn'), nom.length ? nom.join(', ') : 'Ninguna seleccionada');
        $('#modalAlergiasMenor').modal('hide');
      });

      // Búsqueda Alergia
      var targetAleMn = null;
      $(document).on('click', '.btn-search-ale-mn', function() {
        targetAleMn = $(this).closest('.input-group').find('.select-ale-mn');
        $('#modalBuscarAleMenor').modal('show');
        $('#inputBuscarAleMenor').val('').trigger('keyup');
      });
      $(document).on('keyup', '#inputBuscarAleMenor', function() {
        var t = $(this).val().toLowerCase();
        var html = '';
        $('.select-ale-mn:first option').not('[value=""]').each(function() {
          if ($(this).text().toLowerCase().indexOf(t) > -1)
            html += '<a href="#" class="list-group-item seleccionar-ale-mn" data-id="' + $(this).val() + '">' + $(this).text() + '</a>';
        });
        $('#listaResultadosAleMenor').html(html);
      });
      $(document).on('click', '.seleccionar-ale-mn', function(e) {
        e.preventDefault();
        targetAleMn.val($(this).data('id')).trigger('change');
        $('#modalBuscarAleMenor').modal('hide');
      });

      // Nueva Alergia AJAX
      $(document).on('click', '#btn_guardar_ajax_ale_mn', function() {
        var nombre = $('#ajax_nombre_ale_mn').val().trim();
        if (!nombre) {
          mostrarAviso('El nombre es obligatorio.');
          return;
        }
        $.ajax({
          url: window.location.href,
          method: 'POST',
          data: {
            ajax_nueva_alergia: 1,
            nombre_alergia: nombre
          },
          dataType: 'json',
          success: function(res) {
            if (res.success) {
              window.LISTA_ALERGIAS_MENOR.push({
                id: parseInt(res.id),
                nombre: res.nombre
              });
              var opts = construirOpcionesAlergiasMenor();
              $('.select-ale-mn').each(function() {
                var v = $(this).val();
                $(this).html(opts);
                if (v) $(this).val(v);
              });
              var $vacio = $('.select-ale-mn').filter(function() {
                return !$(this).val();
              }).first();
              if ($vacio.length) $vacio.val(res.id);
              else agregarFilaAlergiaMenor();
              $('#modalNuevaAlergiaMenor').modal('hide');
              $('#formAjaxAlergiaMenor')[0].reset();
              mostrarExito('Alergia guardada.');
            } else mostrarAviso(res.error);
          },
          error: function() {
            mostrarAviso('Error al guardar la alergia.');
          }
        });
      });

      // ====== DISCAPACIDADES ======
      $(document).on('click', '#btn_add_disc_row_mn', function() {
        var $u = $('.fila-disc-modal-mn').last();
        if (!$u.find('.select-disc-dinamico-mn').val()) {
          mostrarAviso('Seleccione una discapacidad antes de añadir otra.');
          return;
        }
        agregarFilaDiscapacidadMenor();
      });
      $(document).on('click', '.btn-remove-disc-modal-mn', function() {
        var $f = $(this).closest('.fila-disc-modal-mn');
        if ($f.find('.select-disc-dinamico-mn').val() || $f.find('.input-fecha-disc-dinamico-mn').val()) {
          $f.find('.select-disc-dinamico-mn').val('');
          $f.find('.input-fecha-disc-dinamico-mn').val('');
        } else if ($('.fila-disc-modal-mn').length > 1) $f.remove();
      });
      $(document).on('change', '.select-disc-dinamico-mn', function() {
        var v = $(this).val();
        if (!v) return;
        if ($('.select-disc-dinamico-mn').filter(function() {
            return $(this).val() === v;
          }).length > 1) {
          mostrarAviso('Esa discapacidad ya fue seleccionada.');
          $(this).val('');
        }
      });
      $(document).on('click', '#btn_modal_disc_mn', function() {
        $('#modalDiscapacidadesMenor .fila-disc-modal-mn').not(':first').remove();
        var $first = $('#modalDiscapacidadesMenor .fila-disc-modal-mn').first();
        $first.find('.select-disc-dinamico-mn').html(construirOpcionesDiscapacidadesMenor()).val('');
        $first.find('.input-fecha-disc-dinamico-mn').val('');
        var data = $('#modalAgregarPacienteMenor #ext_discapacidades_ids').val();
        if (data) {
          try {
            var lista = JSON.parse(data);
            if (Array.isArray(lista) && lista.length > 0) {
              $first.find('.select-disc-dinamico-mn').val(lista[0].id);
              if (lista[0].fecha) $first.find('.input-fecha-disc-dinamico-mn').val(lista[0].fecha);
              for (var i = 1; i < lista.length; i++) {
                agregarFilaDiscapacidadMenor();
                var $last = $('#modalDiscapacidadesMenor .fila-disc-modal-mn').last();
                $last.find('.select-disc-dinamico-mn').val(lista[i].id);
                if (lista[i].fecha) $last.find('.input-fecha-disc-dinamico-mn').val(lista[i].fecha);
              }
            }
          } catch (e) {}
        }
        $('#modalDiscapacidadesMenor').modal('show');
      });

      $(document).on('click', '#guardar_disc_listo_mn', function() {
        var sel = [],
          nom = [];
        $('.fila-disc-modal-mn').each(function() {
          var $s = $(this).find('.select-disc-dinamico-mn');
          var id = $s.val();
          if (!id) return;
          var f = $(this).find('.input-fecha-disc-dinamico-mn').val();
          var nombre = $s.find('option:selected').data('nombre') || '';
          sel.push({
            id: parseInt(id),
            nombre: nombre,
            fecha: f
          });
          nom.push(nombre + (f ? ' (' + f + ')' : ''));
        });
        var $m = $('#modalAgregarPacienteMenor');
        if (sel.length === 0) {
          $m.find('#ext_discapacidad').val('No');
          $m.find('#ext_discapacidades_ids').val('');
          $m.find('#btn_modal_disc_mn').prop('disabled', true).removeClass('btn-info').addClass('btn-second');
        } else {
          $m.find('#ext_discapacidades_ids').val(JSON.stringify(sel));
        }
        actualizarTooltip($('#wrap_btn_modal_disc_mn'), nom.length ? nom.join(', ') : 'Ninguna seleccionada');
        $('#modalDiscapacidadesMenor').modal('hide');
      });

      $(document).on('click', '#cancelar_disc_modal_mn', function() {
        $('#modalDiscapacidadesMenor').modal('hide');
      });

      // ====== REPRESENTANTES (obligatorio para menor) ======
      $(document).on('click', '#btn_modal_rep_mn', function() {
        $('#contenedor_filas_representantes_mn').empty();
        var data = $('#modalAgregarPacienteMenor #ext_representantes_ids').val();
        var loaded = false;
        if (data) {
          try {
            var lista = JSON.parse(data);
            if (Array.isArray(lista) && lista.length > 0) {
              lista.forEach(function(r) {
                agregarFilaRepMenor(r.id, r.tipo || 'Legal', r.nombre || '', r.parentesco || '');
              });
              loaded = true;
            }
          } catch (e) {}
        }
        if (!loaded) agregarFilaRepMenor();
        $('#modalRepresentantesMenor').modal('show');
      });

      $(document).on('click', '#add_fila_rep_mn', function() {
        var $ult = $('.fila-rep-mn').last();
        if ($ult.length && !$ult.find('.input-id-rep-mn').val()) {
          mostrarAviso('Seleccione el representante actual antes de añadir otro.');
          return;
        }
        agregarFilaRepMenor();
      });

      $(document).on('click', '.btn-remove-rep-mn', function() {
        $(this).closest('.fila-rep-mn').remove();
        if ($('.fila-rep-mn').length === 0) agregarFilaRepMenor();
      });

      var filaRepDestinoMenor = null;

      $(document).on('click', '.btn-search-rep-mn', function(e) {
        e.preventDefault();
        filaRepDestinoMenor = $(this).closest('.fila-rep-mn');
        $('#inputBuscarRepMenor').val('');
        $('#listaResultadosRepMenor').html('<div class="text-muted text-center" style="padding:15px;">Escriba para buscar...</div>');
        $('#modalBuscarRepMenor').modal('show');
        $('#inputBuscarRepMenor').focus();
      });

      var timerRepMenor = null;
      $(document).on('keyup', '#inputBuscarRepMenor', function() {
        var term = $(this).val().trim();
        if (timerRepMenor) clearTimeout(timerRepMenor);
        if (term.length < 2) {
          $('#listaResultadosRepMenor').html('<div class="text-muted text-center" style="padding:15px;">Escriba al menos 2 caracteres...</div>');
          return;
        }
        $('#listaResultadosRepMenor').html('<div class="text-center" style="padding:15px;"><i class="fa fa-spinner fa-spin"></i> Buscando...</div>');
        timerRepMenor = setTimeout(function() {
          $.ajax({
            url: '../../cfg/ajax/buscar_representantes.php',
            method: 'GET',
            data: {
              q: term,
              _t: Date.now()
            },
            dataType: 'json',
            cache: false,
            success: function(data) {
              if (!data || data.length === 0) {
                $('#listaResultadosRepMenor').html('<div class="text-muted text-center" style="padding:15px;">No se encontraron representantes.</div>');
                return;
              }
              var html = '';
              data.forEach(function(rep) {
                var nc = (rep.nombre + ' ' + (rep.apellido || '')).trim();
                html += '<a href="#" class="list-group-item seleccionar-rep-mn" data-id="' + rep.id + '" data-nombre="' + nc + '">' +
                  '<strong>' + rep.tipo_cedula + '-' + rep.cedula + '</strong> | ' + nc + '</a>';
              });
              $('#listaResultadosRepMenor').html(html);
            }
          });
        }, 300);
      });

      $(document).on('click', '.seleccionar-rep-mn', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var nombre = $(this).data('nombre');
        var dup = false;
        $('.input-id-rep-mn').each(function() {
          if ($(this).val() == id && $(this).closest('.fila-rep-mn')[0] !== filaRepDestinoMenor[0]) dup = true;
        });
        if (dup) {
          mostrarAviso('Ese representante ya está asignado en otra fila.');
          return;
        }
        filaRepDestinoMenor.find('.input-id-rep-mn').val(id);
        filaRepDestinoMenor.find('.input-nombre-rep-mn').val(nombre);
        $('#modalBuscarRepMenor').modal('hide');
      });

      $(document).on('click', '#guardar_rep_listo_mn', function() {
        var sel = [],
          nom = [];
        $('.fila-rep-mn').each(function() {
          var id = $(this).find('.input-id-rep-mn').val();
          if (!id) return;
          var nombre = $(this).find('.input-nombre-rep-mn').val();
          var tipo = $(this).find('.select-tipo-rep-mn').val();
          var parent = $(this).find('.select-parentesco-rep-mn').val();
          sel.push({
            id: parseInt(id),
            nombre: nombre,
            tipo: tipo,
            parentesco: parent || ''
          });
          nom.push(nombre + ' (' + tipo + (parent ? ' - ' + parent : '') + ')');
        });
        var $m = $('#modalAgregarPacienteMenor');
        if (sel.length === 0) {
          $m.find('#ext_representantes_ids').val('');
        } else {
          $m.find('#ext_representantes_ids').val(JSON.stringify(sel));
        }
        actualizarTooltip($('#wrap_btn_modal_rep_mn'), nom.length ? nom.join(', ') : 'Debe registrar al menos un representante');
        $('#modalRepresentantesMenor').modal('hide');
      });

      $(document).on('click', '#cancelar_rep_modal_mn', function() {
        $('#modalRepresentantesMenor').modal('hide');
      });

      $(document).on('click', '#btn_abrir_nuevo_representante_mn', function(e) {
        e.preventDefault();
        if ($('.fila-rep-mn').length === 0) agregarFilaRepMenor();
        var $ult = $('.fila-rep-mn').last();
        if ($ult.find('.input-id-rep-mn').val()) agregarFilaRepMenor();
        window.filaRepDestinoParaNuevoMenor = $('.fila-rep-mn').last();
        $('#modalRepresentante').modal({
          backdrop: 'static',
          keyboard: false,
          show: true
        });
      });

      window.rellenarFilaConNuevoRepresentanteMenor = function(res) {
        if (!res || !res.id) return;
        var $f = window.filaRepDestinoParaNuevoMenor;
        if (!$f || !$f.length) {
          agregarFilaRepMenor(res.id, 'Legal', res.nombre_completo || '', '');
          return;
        }
        $f.find('.input-id-rep-mn').val(res.id);
        $f.find('.input-nombre-rep-mn').val(res.nombre_completo || '');
        window.filaRepDestinoParaNuevoMenor = null;
      };

      // ====== GUARDAR PACIENTE MENOR (AJAX) ======
      $(document).on('click', '#btnGuardarMenorAjax', async function() {
        var btn = $(this);
        $('#formAjaxPacienteMenor .input-error').removeClass('input-error');

        var tabsAValidar = ['info'];
        if ($('#chk_menor_interno').is(':checked')) {
          tabsAValidar.push('direccion');
        }
        tabsAValidar.push('salud_otros');

        var todoValido = true;
        var primerTabConError = null;
        for (var i = 0; i < tabsAValidar.length; i++) {
          if (!validarTabMenor(tabsAValidar[i])) {
            todoValido = false;
            if (!primerTabConError) primerTabConError = tabsAValidar[i];
          }
        }

        if (!todoValido) {
          if (primerTabConError) activarTabMenor(primerTabConError);
          return;
        }

        // Validación explícita de edad (<18)
        var edad = parseInt($('#modalAgregarPacienteMenor #ext_edad').attr('data-edad-anios'));
        if (isNaN(edad) || edad < 0 || edad >= 18) {
          mostrarAviso('El paciente debe ser <b>menor de 18 años</b>.');
          activarTabMenor('info');
          return;
        }

        // Representante obligatorio para menores
        var repIdsRaw = $('#modalAgregarPacienteMenor #ext_representantes_ids').val();
        var repLista = [];
        try {
          repLista = JSON.parse(repIdsRaw || '[]');
        } catch (e) {
          repLista = [];
        }
        if (!Array.isArray(repLista) || repLista.length === 0) {
          $('#modalAgregarPacienteMenor #btn_modal_rep_mn').addClass('input-error');
          mostrarAviso('Debe registrar al menos un <b>Representante</b> (obligatorio para menores de edad).');
          activarTabMenor('salud_otros');
          return;
        }

        var cedOk = await verificarCedulaDuplicadaMenor();
        if (!cedOk) return;

        var esInterno = $('#chk_menor_interno').is(':checked');
        var data = $('#formAjaxPacienteMenor').serialize();
        data += '&origen=' + ($('#cedula_paciente').length > 0 ? 'Interno' : 'Externo');
        data += '&ext_es_interno=' + (esInterno ? '1' : '0');
        data += '&ext_es_menor=1';

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
          url: '../../cfg/ajax/guardar_paciente_menor.php',
          type: 'POST',
          data: data,
          dataType: 'json',
          success: function(res) {
            btn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar y Usar');
            if (!res || !res.success) {
              if (res && res.error && res.error.indexOf('ya se encuentra registrada') > -1) {
                $('#modalAgregarPacienteMenor #ext_cedula').addClass('input-error');
              }
              mostrarAviso(res && res.error ? res.error : 'Error desconocido.');
              return;
            }

            window.__pacienteSaveSuccessFlag = true;

            var enCitas = $('#cedula_paciente').length > 0;
            var enDespacho = $('input[name="cedula_externo"]').length > 0;

            $('#modalAgregarPacienteMenor').modal('hide');

            if (enCitas) {
              $('#tipo_cedula_paciente').val(res.tipo_ced);
              $('#cedula_paciente').val(res.cedula);
              $('#nombre_paciente').val(res.nombre_completo);
              $('#id_paciente_hidden').val(res.id_pac);
              $('#edad_paciente_hidden').val(res.edad || $('#ext_edad').val());
              $('#tipo_cedula_paciente').css('pointer-events', 'none');
              $('#cedula_paciente').prop('readonly', true).css('background-color', '#e9ecef');
              $('#nombre_paciente').prop('readonly', true).css('background-color', '#e9ecef');
            }
            if (enDespacho) {
              $('select[name="tipo_cedula_externo"]').val(res.tipo_ced);
              $('input[name="cedula_externo"]').val(res.cedula);
              $('#paciente_externo').val(res.nombre_completo);
              $('#entregado_a').val(res.nombre_completo);
              $('#div_entregado_a').show();
              $('select[name="tipo_cedula_externo"]').css('pointer-events', 'none');
              $('input[name="cedula_externo"]').prop('readonly', true).css('background-color', '#e9ecef');
              $('#paciente_externo').prop('readonly', true).css('background-color', '#e9ecef');
              $('#entregado_a').prop('readonly', true).css('background-color', '#e9ecef');
            }

            mostrarExito('Paciente menor registrado con éxito y adjuntado correctamente.');
          },
          error: function() {
            btn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar y Usar');
            mostrarAviso('Error de conexión al guardar el paciente menor.');
          }
        });
      });

    });
  })();
</script>