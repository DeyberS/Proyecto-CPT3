<?php
if (!isset($conexion)) {
  include('../../cfg/conexion.php');
}

$lista_pat_ext = [];
$q_pat_ext = $conexion->query("SELECT Id_patologia, nombre_patologia, codigo_cie FROM patologias WHERE estatus = 1 ORDER BY nombre_patologia ASC");
if ($q_pat_ext) {
  while ($p = $q_pat_ext->fetch_assoc()) {
    $lista_pat_ext[] = ['id' => intval($p['Id_patologia']), 'nombre' => $p['nombre_patologia'], 'cie' => $p['codigo_cie']];
  }
}

$lista_ale_ext = [];
$q_ale_ext = $conexion->query("SELECT Id_alergias_conocidas, nombre_alergia FROM alergias_conocidas WHERE estatus = 1 ORDER BY nombre_alergia ASC");
if ($q_ale_ext) {
  while ($a = $q_ale_ext->fetch_assoc()) {
    $lista_ale_ext[] = ['id' => intval($a['Id_alergias_conocidas']), 'nombre' => $a['nombre_alergia']];
  }
}

$lista_disc_ext = [];
$q_disc_ext = $conexion->query("SELECT Id_discapacidad, nombre_discapacidad FROM discapacidades WHERE estatus = 1 ORDER BY nombre_discapacidad ASC");
if ($q_disc_ext) {
  while ($d = $q_disc_ext->fetch_assoc()) {
    $lista_disc_ext[] = ['id' => intval($d['Id_discapacidad']), 'nombre' => $d['nombre_discapacidad']];
  }
}
?>
<div class="modal fade" id="modalAgregarPacienteExterno" role="dialog" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-lg" role="document" style="width: 92%; max-width: 1150px;">
    <div class="modal-content">
      <div class="modal-header bg-primary" style="cursor: default;">
        <h4 class="modal-title" style="color: white;">
          <i class="fa fa-user-plus"></i> Registrar Paciente
        </h4>
      </div>

      <div class="modal-body" style="padding: 15px 20px; max-height: 78vh; overflow-y: auto;">

        <div class="row" style="margin-bottom: 10px;">
          <div class="col-sm-12">
            <div class="form-group" style="margin-bottom: 5px;">
              <label style="font-weight: bold; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
                <input type="checkbox" id="chk_paciente_interno" style="width: 18px; height: 18px; margin: 0; cursor: pointer;">
                <span>Paciente Interno (marcar para incluir dirección, ocupación y estudios)</span>
              </label>
              <p class="text-muted" style="margin: 4px 0 0 26px; font-size: 12px;">
                Si está desmarcado (Externo), solo se usarán Datos Personales, Representantes, Grupo Sanguíneo y Discapacidades.
              </p>
            </div>
          </div>
        </div>

        <div class="nav-tabs-custom" style="box-shadow: none; margin-bottom: 0;">
          <ul class="nav nav-tabs" id="tabsPacienteModal">
            <li class="active" data-tab-name="info">
              <a href="#pm_info" data-toggle="tab">Datos Personales</a>
            </li>
            <li data-tab-name="ocupacion_estudios" class="tab-opcional-paciente disabled-tab" style="display: none;">
              <a href="#pm_ocupacion" data-toggle="tab">Ocupación Y Estudios</a>
            </li>
            <li data-tab-name="direccion" class="tab-opcional-paciente disabled-tab" style="display: none;">
              <a href="#pm_direccion" data-toggle="tab">Dirección de Residencia</a>
            </li>
            <li data-tab-name="salud_otros" class="disabled-tab">
              <a href="#pm_salud" data-toggle="tab">Salud y Otros Datos</a>
            </li>
          </ul>

          <form id="formAjaxPacienteExterno" novalidate autocomplete="off">
            <div class="tab-content" style="padding-top: 15px;">

              <div class="tab-pane active" id="pm_info">
                <section style="margin-bottom: 10px;">
                  <div class="row">
                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Cédula (*):</p>
                      <div class="input-group">
                        <span class="input-group-addon" style="padding: 0; border: none;">
                          <select name="ext_tipo_cedula" id="ext_tipo_cedula" class="form-control" style="width: 70px; height: 34px; font-weight: bold; background-color: #f4f4f4; border-right: none; border-top-right-radius: 0; border-bottom-right-radius: 0;">
                            <option value="V">V-</option>
                            <!--<option value="E">E-</option>-->
                          </select>
                        </span>
                        <input type="text" class="form-control" name="ext_cedula" id="ext_cedula" placeholder="N° de Cédula" maxlength="8">
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
                          <input type="text" class="form-control" id="ext_edad" name="ext_edad" readonly placeholder="Edad" style="width: 80px; height: 34px; text-align: center; background-color: #f4f4f4; font-weight: bold; border-left: none; border-top-left-radius: 0; border-bottom-left-radius: 0;">
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

                    <div class="col-md-4 col-sm-3 form-group">
                      <p>Situación conyugal:</p>
                      <select name="ext_situacion" id="ext_situacion" class="form-control">
                        <option value="">--- Seleccione ---</option>
                        <option value="Soltero">Soltero</option>
                        <option value="Casado">Casado</option>
                        <option value="Viudo">Viudo</option>
                        <option value="Divorciado">Divorciado</option>
                        <option value="Unión Estable de Hecho">Unión Estable de Hecho</option>
                      </select>
                    </div>
                  </div>

                  <div class="row solo-interno">
                    <div class="col-md-4 col-sm-4 form-group">
                      <p>País de nacimiento (*):</p>
                      <select name="ext_pais_nacimiento" id="ext_pais_nacimiento" class="form-control">
                        <option value="">--- Seleccione un País ---</option>
                        <?php
                        $result_nac_ext = $conexion->query("SELECT Id_Pais, nombre_pais FROM pais");
                        if ($result_nac_ext) {
                          while ($row_nac_ext = $result_nac_ext->fetch_assoc()) {
                            echo "<option value='{$row_nac_ext['Id_Pais']}'>{$row_nac_ext['nombre_pais']}</option>";
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
                        </select>
                      </div>
                    </div>

                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Analfabeta (*):</p>
                      <select name="ext_analfabeta" id="ext_analfabeta" class="form-control">
                        <option value="">--- Seleccione Una Opción ---</option>
                        <option value="Si">Si</option>
                        <option value="No">No</option>
                      </select>
                    </div>

                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Cotiza seguro social:</p>
                      <select name="ext_seguro" id="ext_seguro" class="form-control">
                        <option value="">--- Seleccione Una Opción ---</option>
                        <option value="Si">Si</option>
                        <option value="No">No</option>
                      </select>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Teléfono (*):</p>
                      <div class="input-group">
                        <span class="input-group-addon" style="padding: 0; border: none;">
                          <select name="ext_prefijo" id="ext_prefijo" class="form-control" style="width: 80px; height: 34px; font-weight: bold; background-color: #f4f4f4; border-right: none; border-top-right-radius: 0; border-bottom-right-radius: 0;">
                            <?php
                            $res_pref_ext = $conexion->query("SELECT * FROM prefijos_telefonos WHERE estatus = '1'");
                            if ($res_pref_ext) {
                              while ($row_pref_ext = $res_pref_ext->fetch_assoc()) {
                                echo "<option value='" . $row_pref_ext["Id"] . "'>" . $row_pref_ext['prefijo'] . "</option>";
                              }
                            }
                            ?>
                          </select>
                        </span>
                        <input type="text" class="form-control" name="ext_telefono" id="ext_telefono" placeholder="N° De Teléfono" maxlength="7">
                      </div>
                    </div>

                    <div class="col-md-8 col-sm-8 form-group">
                      <p>Correo (*):</p>
                      <input type="email" class="form-control" name="ext_email" id="ext_email" placeholder="correo@gmail.com">
                    </div>
                  </div>
                </section>
              </div>

              <div class="tab-pane" id="pm_ocupacion">
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
                        <option value="tecnico_medio">Técnico Medio</option>
                        <option value="tsu">Técnico Superior Universitario (T.S.U)</option>
                        <option value="universitario">Universitario (Lic./Ing.)</option>
                        <option value="especializacion">Especialización</option>
                        <option value="maestria">Maestría</option>
                        <option value="doctorado">Doctorado</option>
                      </select>
                    </div>

                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Profesión:</p>
                      <select name="ext_profesion" id="ext_profesion" class="form-control">
                        <option value="">--- Seleccione Una Profesión ---</option>
                        <optgroup label="Salud">
                          <option value="medico">Médico / Especialista</option>
                          <option value="enfermero">Enfermero(a)</option>
                          <option value="bioanalista">Bioanalista</option>
                          <option value="odontologo">Odontólogo</option>
                          <option value="farmaceuta">Farmacéutico(a)</option>
                        </optgroup>
                        <optgroup label="Ingeniería y Tecnología">
                          <option value="ing_civil">Ingeniero Civil</option>
                          <option value="ing_sistemas">Ingeniero de Sistemas / Computación</option>
                          <option value="ing_mecanico">Ingeniero Mecánico</option>
                          <option value="analista_it">Analista de Soporte Técnico</option>
                          <option value="programador">Programador / Desarrollador</option>
                        </optgroup>
                        <optgroup label="Educación y Social">
                          <option value="docente">Docente</option>
                          <option value="abogado">Abogado(a)</option>
                          <option value="psicologo">Psicólogo(a)</option>
                          <option value="trabajador_social">Trabajador(a) Social</option>
                        </optgroup>
                        <optgroup label="Administración y Comercio">
                          <option value="administrador">Administrador(a)</option>
                          <option value="contador">Contador Público</option>
                          <option value="vendedor">Vendedor / Comerciante</option>
                        </optgroup>
                      </select>
                    </div>

                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Ocupación:</p>
                      <select name="ext_ocupacion" id="ext_ocupacion" class="form-control">
                        <option value="">--- Seleccione Una Ocupación ---</option>
                        <optgroup label="Salud">
                          <option value="medico">Médico / Especialista</option>
                          <option value="enfermero">Enfermero(a)</option>
                          <option value="bioanalista">Bioanalista</option>
                          <option value="odontologo">Odontólogo</option>
                          <option value="farmaceuta">Farmacéutico(a)</option>
                        </optgroup>
                        <optgroup label="Ingeniería y Tecnología">
                          <option value="ing_civil">Ingeniero Civil</option>
                          <option value="ing_sistemas">Ingeniero de Sistemas / Computación</option>
                          <option value="ing_mecanico">Ingeniero Mecánico</option>
                          <option value="analista_it">Analista de Soporte Técnico</option>
                          <option value="programador">Programador / Desarrollador</option>
                        </optgroup>
                        <optgroup label="Educación y Social">
                          <option value="docente_primaria">Docente de Primaria</option>
                          <option value="profesor_media">Profesor de Educación Media</option>
                          <option value="abogado">Abogado(a)</option>
                          <option value="psicologo">Psicólogo(a)</option>
                          <option value="trabajador_social">Trabajador(a) Social</option>
                        </optgroup>
                        <optgroup label="Administración y Comercio">
                          <option value="administrador">Administrador(a)</option>
                          <option value="contador">Contador Público</option>
                          <option value="secretaria">Asistente Administrativo / Secretaria</option>
                          <option value="vendedor">Vendedor / Comerciante</option>
                          <option value="cajero">Cajero(a)</option>
                        </optgroup>
                        <optgroup label="Oficios y Otros">
                          <option value="albanil">Albañil / Constructor</option>
                          <option value="mecanico">Mecánico Automotriz</option>
                          <option value="cocinero">Cocinero / Chef</option>
                          <option value="chofer">Chofer / Transportista</option>
                          <option value="seguridad">Oficial de Seguridad</option>
                          <option value="estudiante">Estudiante</option>
                          <option value="ama_casa">Hogar / Ama de casa</option>
                        </optgroup>
                      </select>
                    </div>
                  </div>

                  <div class="row">
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

              <div class="tab-pane" id="pm_direccion">
                <section style="margin-bottom: 10px;">
                  <div class="row">
                    <div class="col-md-4 col-sm-4 form-group">
                      <p>Estado (*):</p>
                      <select name="ext_estado" id="ext_estado" class="form-control">
                        <option value="">--- Seleccione Un Estado ---</option>
                        <?php
                        $result_dir_ext = $conexion->query("SELECT Id_Estado, nombre_estado FROM estado WHERE Id_Pais = 1");
                        if ($result_dir_ext) {
                          while ($row_dir_ext = $result_dir_ext->fetch_assoc()) {
                            echo "<option value='{$row_dir_ext['Id_Estado']}'>{$row_dir_ext['nombre_estado']}</option>";
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

              <div class="tab-pane" id="pm_salud">
                <section style="margin-bottom: 0; padding-top: 5px;">
                  <div class="row">
                    <div class="col-sm-4 form-group solo-interno-salud">
                      <p>Patologías detectadas previamente:</p>
                      <span id="wrap_btn_modal_pat_ext" title="Ninguna seleccionada" style="display: block;">
                        <button type="button" class="btn btn-info btn-block" id="btn_modal_pat_ext" data-toggle="modal" data-target="#modalPatologiasExt" style="width: 100%;">
                          Gestionar Patologías
                        </button>
                      </span>
                      <input type="hidden" name="ext_patologias_data" id="ext_patologias_ids" value="">
                    </div>

                    <div class="col-sm-4 form-group solo-interno-salud">
                      <p>Alergías detectadas previamente:</p>
                      <span id="wrap_btn_modal_ale_ext" title="Ninguna seleccionada" style="display: block;">
                        <button type="button" class="btn btn-info btn-block" id="btn_modal_ale_ext" data-toggle="modal" data-target="#modalAlergiasExt" style="width: 100%;">
                          Gestionar Alergias
                        </button>
                      </span>
                      <input type="hidden" name="ext_alergias_data" id="ext_alergias_ids" value="">
                    </div>

                    <div class="col-sm-4 form-group">
                      <p>Representante:</p>
                      <div style="display: flex; gap: 5px;">
                        <select name="ext_tiene_representante" id="ext_tiene_representante" class="form-control" style="width: 70px; flex-shrink: 0;">
                          <option value="No">No</option>
                          <option value="Si">Si</option>
                        </select>
                        <span id="wrap_btn_modal_rep_ext" title="Sin Representantes" style="flex-grow: 1; margin: 0; display: block;">
                          <button type="button" class="btn btn-second btn-block" id="btn_modal_rep_ext" data-toggle="modal" data-target="#modalRepresentantesExt" style="width: 100%;" disabled>
                            Gestionar Representantes
                          </button>
                        </span>
                      </div>
                      <input type="hidden" name="ext_representantes_data" id="ext_representantes_ids" value="">
                    </div>

                    <div class="col-sm-4 form-group">
                      <p>Tipo de discapacidad:</p>
                      <div style="display: flex; gap: 5px;">
                        <select name="ext_discapacidad" id="ext_discapacidad" class="form-control" style="width: 70px; flex-shrink: 0;">
                          <option value="No">No</option>
                          <option value="Si">Si</option>
                        </select>
                        <span id="wrap_btn_modal_disc_ext" title="Ninguna seleccionada" style="flex-grow: 1; margin: 0; display: block;">
                          <button type="button" class="btn btn-second btn-block" id="btn_modal_disc_ext" data-toggle="modal" data-target="#modalDiscapacidadesExt" style="width: 100%;" disabled>
                            Gestionar Discapacidades
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
        <button type="button" class="btn btn-second" id="btnCancelarPacienteModal">Cancelar</button>
        <button type="button" class="btn btn-second" id="btnAtrasPacienteModal" style="display: none;">Atrás</button>
        <button type="button" class="btn btn-primary" id="btnSiguientePacienteModal">Siguiente</button>
        <button type="button" class="btn btn-success" id="btnGuardarPacienteAjax" style="display: none;"><i class="fa fa-save"></i> Guardar y Usar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalPatologiasExt" role="dialog" style="z-index: 1070;">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h4 class="modal-title"><i class="fa fa-medkit"></i> Gestionar Patologías</h4>
      </div>
      <div class="modal-body">
        <div id="contenedor_filas_patologias_ext"></div>
        <button type="button" class="btn btn-success btn-sm" id="add_fila_pat_ext"><i class="fa fa-plus"></i> Añadir otra</button>
        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalNuevaPatologiaExt"><i class="fa fa-plus-circle"></i> Nueva Patología</button>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" id="cancelar_pat_modal_ext">Cancelar</button>
        <button type="button" class="btn btn-primary" id="guardar_pat_listo_ext">Listo</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalBuscarPatExt" role="dialog" style="z-index: 1080;">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h4 class="modal-title">Buscar Patología</h4>
      </div>
      <div class="modal-body">
        <input type="text" id="inputBuscarPatExt" class="form-control" placeholder="Escriba para filtrar...">
        <div class="list-group" id="listaResultadosPatExt" style="margin-top: 10px; max-height: 200px; overflow-y: auto;"></div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalAlergiasExt" role="dialog" style="z-index: 1070;">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h4 class="modal-title"><i class="fa fa-wheelchair"></i> Gestionar Alergias</h4>
      </div>
      <div class="modal-body">
        <div id="contenedor_filas_alergias_ext"></div>
        <button type="button" class="btn btn-success btn-sm" id="add_fila_ale_ext"><i class="fa fa-plus"></i> Añadir otra</button>
        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalNuevaAlergiaExt"><i class="fa fa-plus-circle"></i> Nueva Alergia</button>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" id="cancelar_ale_modal_ext">Cancelar</button>
        <button type="button" class="btn btn-primary" id="guardar_ale_listo_ext">Listo</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalBuscarAleExt" role="dialog" style="z-index: 1080;">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h4 class="modal-title">Buscar Alergia</h4>
      </div>
      <div class="modal-body">
        <input type="text" id="inputBuscarAleExt" class="form-control" placeholder="Escriba para filtrar...">
        <div class="list-group" id="listaResultadosAleExt" style="margin-top: 10px; max-height: 200px; overflow-y: auto;"></div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalNuevaPatologiaExt" role="dialog" style="z-index: 1080;">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h4 class="modal-title"><i class="fa fa-plus-circle"></i> Nueva Patología</h4>
      </div>
      <div class="modal-body">
        <form id="formAjaxPatologiaExt">
          <div class="form-group"><label>Nombre de la Patología (*):</label><input type="text" class="form-control" id="ajax_nombre_pat_ext" required></div>
          <div class="form-group"><label>Código CIE (Opcional):</label><input type="text" class="form-control" id="ajax_cie_pat_ext"></div>
          <div class="form-group">
            <label>¿Es contagiosa?</label>
            <select class="form-control" id="ajax_contagiosa_pat_ext">
              <option value="NO">NO</option>
              <option value="SI">SI</option>
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btn_guardar_ajax_pat_ext">Guardar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalNuevaAlergiaExt" role="dialog" style="z-index: 1080;">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h4 class="modal-title"><i class="fa fa-plus-circle"></i> Nueva Alergia</h4>
      </div>
      <div class="modal-body">
        <form id="formAjaxAlergiaExt">
          <div class="form-group"><label>Nombre de la Alergia (*):</label><input type="text" class="form-control" id="ajax_nombre_ale_ext" required></div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btn_guardar_ajax_ale_ext">Guardar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalDiscapacidadesExt" role="dialog" style="z-index: 1070;">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h4 class="modal-title"><i class="fa fa-wheelchair"></i> Gestionar Discapacidad</h4>
      </div>
      <div class="modal-body">
        <div class="row fila-disc-modal-ext">
          <div class="col-sm-6">
            <label>Tipo de Discapacidad (*):</label>
            <select class="form-control select-disc-dinamico-ext" id="select_disc_unica_ext"></select>
          </div>
          <div class="col-sm-4">
            <label>Fecha detección (Opcional):</label>
            <input type="date" class="form-control input-fecha-disc-dinamico-ext" id="fecha_disc_unica_ext" max="<?php echo date('Y-m-d'); ?>">
          </div>
          <div class="col-sm-2">
            <label style="display:block;">&nbsp;</label>
            <button type="button" class="btn btn-danger btn-remove-disc-modal-ext">
              <img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;">
            </button>
          </div>
        </div>
        <div style="margin-top: 15px;">
          <button type="button" class="btn btn-success btn-sm" id="btn_add_disc_row_ext"><i class="fa fa-plus-circle"></i> Añadir Otra Discapacidad</button>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" id="cancelar_disc_modal_ext">Cancelar</button>
        <button type="button" class="btn btn-primary" id="guardar_disc_listo_ext">Listo</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalRepresentantesExt" role="dialog" style="z-index: 1070;">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h4 class="modal-title"><i class="fa fa-users"></i> Gestionar Representantes</h4>
      </div>
      <div class="modal-body">
        <div id="contenedor_filas_representantes_ext"></div>
        <div style="margin-top: 10px;">
          <button type="button" class="btn btn-success btn-sm" id="add_fila_rep_ext"><i class="fa fa-plus"></i> Añadir otro</button>
          <button type="button" class="btn btn-primary btn-sm" id="btn_abrir_nuevo_representante_ext"><i class="fa fa-user-plus"></i> Nuevo Representante</button>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-second" id="cancelar_rep_modal_ext">Cancelar</button>
        <button type="button" class="btn btn-primary" id="guardar_rep_listo_ext">Listo</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalBuscarRepExt" role="dialog" style="z-index: 1080;">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h4 class="modal-title"><i class="fa fa-search"></i> Buscar Representante</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>Buscar por Cédula o Nombre:</label>
          <input type="text" id="inputBuscarRepExt" class="form-control" placeholder="Escriba la cédula o el nombre..." autocomplete="off">
        </div>
        <div class="list-group" id="listaResultadosRepExt" style="margin-top: 10px; max-height: 300px; overflow-y: auto;"></div>
      </div>
    </div>
  </div>
</div>

<style>
  #tabsPacienteModal li.disabled-tab {
    pointer-events: none;
    opacity: 0.5;
  }

  #tabsPacienteModal li.disabled-tab a {
    color: #b2b2b2 !important;
    cursor: not-allowed;
  }

  #modalAgregarPacienteExterno input,
  #modalAgregarPacienteExterno select,
  #modalAgregarPacienteExterno textarea,
  #modalAgregarPacienteExterno button {
    pointer-events: auto !important;
  }

  #modalAgregarPacienteExterno .input-error {
    border: 2px solid crimson !important;
    box-shadow: 0 0 5px crimson !important;
    background-color: #fff5f5 !important;
  }

  #wrap_btn_modal_rep_ext,
  #wrap_btn_modal_disc_ext,
  #wrap_btn_modal_pat_ext,
  #wrap_btn_modal_ale_ext {
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
  window.LISTA_PATOLOGIAS_EXT = <?php echo json_encode($lista_pat_ext); ?>;
  window.LISTA_ALERGIAS_EXT = <?php echo json_encode($lista_ale_ext); ?>;
  window.LISTA_DISCAPACIDADES_EXT = <?php echo json_encode($lista_disc_ext); ?>;
</script>

<script>
  (function() {
    'use strict';
    if (window.__modalPacienteExternoReestructurado) return;
    window.__modalPacienteExternoReestructurado = true;

    var TAB_IDS = {
      'info': 'pm_info',
      'ocupacion_estudios': 'pm_ocupacion',
      'direccion': 'pm_direccion',
      'salud_otros': 'pm_salud'
    };

    function getTodayISO() {
      var d = new Date();
      return d.getFullYear() + '-' +
        String(d.getMonth() + 1).padStart(2, '0') + '-' +
        String(d.getDate()).padStart(2, '0');
    }

    function mostrarAvisoGlobal(msg) {
      if (typeof window.mostrarAviso === 'function') {
        window.mostrarAviso(msg);
      } else if ($('#avisoModal').length) {
        $('#avisoTexto').html(msg);
        $('#avisoModal').modal('show');
      } else {
        alert(msg);
      }
    }

    function mostrarExitoGlobal(msg) {
      if (typeof window.mostrarExito === 'function') {
        window.mostrarExito(msg);
      } else if (typeof window.mostrarAvisoExito === 'function') {
        window.mostrarAvisoExito(msg);
      } else {
        mostrarAvisoGlobal(msg);
      }
    }

    $(document).on('shown.bs.modal', '#avisoModal', function() {
      $(this).css('z-index', 9999999);
      setTimeout(function() {
        $('.modal-backdrop').last().css('z-index', 9999998);
      }, 0);
    });

    function setMaxFecha18() {
      var today = new Date();
      today.setFullYear(today.getFullYear() - 18);
      var y = today.getFullYear();
      var m = String(today.getMonth() + 1).padStart(2, '0');
      var d = String(today.getDate()).padStart(2, '0');
      var inp = document.getElementById('ext_fecha_nacimiento');
      if (inp) inp.setAttribute('max', y + '-' + m + '-' + d);
    }

    function calcularEdadExt() {
      var $f = $('#ext_fecha_nacimiento');
      var $e = $('#ext_edad');
      var val = $f.val();
      $f.removeClass('input-error');
      $e.val('');
      if (!val) return;

      var p = val.split('-');
      if (p.length !== 3) return;
      var anio = parseInt(p[0], 10),
        mes = parseInt(p[1], 10) - 1,
        dia = parseInt(p[2], 10);
      var fNac = new Date(anio, mes, dia);
      if (fNac.getFullYear() !== anio || fNac.getMonth() !== mes || fNac.getDate() !== dia) {
        $f.addClass('input-error');
        mostrarAvisoGlobal("La fecha ingresada no es válida.");
        $f.val('');
        return;
      }

      var hoy = new Date();
      hoy.setHours(0, 0, 0, 0);
      if (fNac > hoy) {
        $f.addClass('input-error');
        mostrarAvisoGlobal("La fecha de nacimiento no puede ser posterior a la fecha actual.");
        $f.val('');
        return;
      }
      if (anio < 1900) {
        $f.addClass('input-error');
        mostrarAvisoGlobal("La fecha de nacimiento no puede ser anterior a 1900.");
        $f.val('');
        return;
      }

      var edad = hoy.getFullYear() - fNac.getFullYear();
      var mDiff = hoy.getMonth() - fNac.getMonth();
      if (mDiff < 0 || (mDiff === 0 && hoy.getDate() < fNac.getDate())) edad--;

      if (edad < 18) {
        $f.addClass('input-error');
        mostrarAvisoGlobal("Este formulario es solo para adultos (+18). Edad calculada: " + edad + " años.");
        $e.val('');
      } else if (edad > 120) {
        $f.addClass('input-error');
        mostrarAvisoGlobal("La edad calculada (" + edad + ") parece incorrecta.");
        $e.val('');
      } else {
        $e.val(edad);
        $f.removeClass('input-error');
      }
    }

    function verificarCedulaDuplicada() {
      return new Promise(function(resolve) {
        var tipo = $('#ext_tipo_cedula').val();
        var ced = $('#ext_cedula').val().trim();
        if (!ced || ced.length === 0) {
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
              $('#ext_cedula').addClass('input-error');
              mostrarAvisoGlobal('La cédula ' + tipo + '-' + ced + ' ya está registrada en el sistema.');
              resolve(false);
            } else {
              $('#ext_cedula').removeClass('input-error');
              resolve(true);
            }
          },
          error: function() {
            resolve(true);
          }
        });
      });
    }

    function inicializarTooltips() {
      $('#wrap_btn_modal_pat_ext, #wrap_btn_modal_ale_ext, #wrap_btn_modal_disc_ext, #wrap_btn_modal_rep_ext')
        .tooltip('destroy')
        .tooltip({
          container: 'body',
          placement: 'top'
        });
    }

    function actualizarTooltip($wrap, texto) {
      var t = texto || 'Ninguna seleccionada';
      $wrap.attr('data-original-title', t).attr('title', '');
      if ($wrap.data('bs.tooltip')) {
        $wrap.data('bs.tooltip').options.title = t;
      }
    }

    function actualizarBotonesFooter() {
      var tabActual = $('#tabsPacienteModal li.active').data('tab-name');
      var esPrimerTab = (tabActual === 'info');
      var esUltimoTab = (tabActual === 'salud_otros');

      if (esPrimerTab) {
        $('#btnCancelarPacienteModal').show();
        $('#btnAtrasPacienteModal').hide();
      } else {
        $('#btnCancelarPacienteModal').hide();
        $('#btnAtrasPacienteModal').show();
      }

      if (esUltimoTab) {
        $('#btnSiguientePacienteModal').hide();
        $('#btnGuardarPacienteAjax').show();
      } else {
        $('#btnSiguientePacienteModal').show();
        $('#btnGuardarPacienteAjax').hide();
      }
    }

    function marcarTabActivo(name) {
      var tabId = TAB_IDS[name];
      if (!tabId) return;
      $('#tabsPacienteModal li').removeClass('active');
      $('#tabsPacienteModal li[data-tab-name="' + name + '"]').addClass('active');
      Object.keys(TAB_IDS).forEach(function(k) {
        $('#' + TAB_IDS[k]).removeClass('active in');
      });
      $('#' + tabId).addClass('active in');
      actualizarBotonesFooter();
    }

    function activarTab(name) {
      marcarTabActivo(name);
    }

    function desbloquearTab(name) {
      $('#tabsPacienteModal li[data-tab-name="' + name + '"]').removeClass('disabled-tab');
    }

    function refrescarBotonesRepDisc() {
      if ($('#ext_tiene_representante').val() === 'Si') {
        $('#btn_modal_rep_ext').prop('disabled', false).removeClass('btn-second').addClass('btn-info');
      } else {
        $('#btn_modal_rep_ext').prop('disabled', true).removeClass('btn-info').addClass('btn-second');
      }
      if ($('#ext_discapacidad').val() === 'Si') {
        $('#btn_modal_disc_ext').prop('disabled', false).removeClass('btn-second').addClass('btn-info');
      } else {
        $('#btn_modal_disc_ext').prop('disabled', true).removeClass('btn-info').addClass('btn-second');
      }
    }

    function limpiarSaludOtros() {
      $('#ext_patologias_ids').val('');
      $('#ext_alergias_ids').val('');
      $('#ext_discapacidades_ids').val('');
      $('#ext_representantes_ids').val('');
      $('#ext_grupo_sanguineo').val('');
      $('#ext_tiene_representante').val('No');
      $('#ext_discapacidad').val('No');
      actualizarTooltip($('#wrap_btn_modal_pat_ext'), 'Ninguna seleccionada');
      actualizarTooltip($('#wrap_btn_modal_ale_ext'), 'Ninguna seleccionada');
      actualizarTooltip($('#wrap_btn_modal_disc_ext'), 'Ninguna seleccionada');
      actualizarTooltip($('#wrap_btn_modal_rep_ext'), 'Sin Representantes');
      $('#btn_modal_disc_ext').prop('disabled', true).removeClass('btn-info').addClass('btn-second');
      $('#btn_modal_rep_ext').prop('disabled', true).removeClass('btn-info').addClass('btn-second');
      $('#contenedor_filas_patologias_ext').empty();
      $('#contenedor_filas_alergias_ext').empty();
      $('#contenedor_filas_representantes_ext').empty();
      $('.fila-disc-modal-ext').not(':first').remove();
      $('#select_disc_unica_ext').val('').html(construirOpcionesDiscapacidadesExt());
      $('#fecha_disc_unica_ext').val('');
    }

    function toggleInternoExterno() {
      var esInterno = $('#chk_paciente_interno').is(':checked');
      var tabActivo = $('#tabsPacienteModal li.active').data('tab-name');

      if (esInterno) {
        $('.tab-opcional-paciente').show();
        $('.solo-interno').show();
        $('.solo-interno-salud').show();
      } else {
        $('.tab-opcional-paciente').hide();
        $('.solo-interno').hide();
        $('.solo-interno-salud').hide();
      }

      if (tabActivo === 'salud_otros') {
        limpiarSaludOtros();
        $('#tabsPacienteModal li[data-tab-name="salud_otros"]').addClass('disabled-tab');
        activarTab('info');
      } else if ((tabActivo === 'ocupacion_estudios' || tabActivo === 'direccion') && !esInterno) {
        activarTab('info');
      }

      refrescarBotonesRepDisc();
    }

    function siguienteTabPara(tabActual) {
      var esInterno = $('#chk_paciente_interno').is(':checked');
      if (tabActual === 'info') return esInterno ? 'ocupacion_estudios' : 'salud_otros';
      if (tabActual === 'ocupacion_estudios') return 'direccion';
      if (tabActual === 'direccion') return 'salud_otros';
      return null;
    }

    function tabAnteriorPara(tabActual) {
      var esInterno = $('#chk_paciente_interno').is(':checked');
      if (tabActual === 'salud_otros') return esInterno ? 'direccion' : 'info';
      if (tabActual === 'direccion') return 'ocupacion_estudios';
      if (tabActual === 'ocupacion_estudios') return 'info';
      return null;
    }

    function limpiarModalPacienteExt() {
      var $form = $('#formAjaxPacienteExterno');
      if ($form.length) {
        $form[0].reset();
        $form.find('.input-error').removeClass('input-error');
      }
      $('#ext_estado_nacimiento').html('<option value="">--- Seleccione Un Estado ---</option>');
      $('#ext_municipio_nacimiento').html('<option value="">--- Seleccione Un Municipio ---</option>');
      $('#ext_municipio').html('<option value="">--- Seleccione Un Municipio ---</option>');
      $('#ext_sector').html('<option value="">--- Seleccione Un Sector ---</option>');
      $('#ext_tipo_etnia').prop('disabled', true).val('');
      $('#ext_edad').val('');

      $('#chk_paciente_interno').prop('checked', false);
      $('.tab-opcional-paciente').hide();
      $('.solo-interno').hide();
      $('.solo-interno-salud').hide();

      $('#tabsPacienteModal li').removeClass('disabled-tab');
      $('#tabsPacienteModal li[data-tab-name="ocupacion_estudios"]').addClass('disabled-tab');
      $('#tabsPacienteModal li[data-tab-name="direccion"]').addClass('disabled-tab');
      $('#tabsPacienteModal li[data-tab-name="salud_otros"]').addClass('disabled-tab');
      marcarTabActivo('info');

      $('#ext_patologias_ids').val('');
      $('#ext_alergias_ids').val('');
      $('#ext_discapacidades_ids').val('');
      $('#ext_representantes_ids').val('');

      actualizarTooltip($('#wrap_btn_modal_pat_ext'), 'Ninguna seleccionada');
      actualizarTooltip($('#wrap_btn_modal_ale_ext'), 'Ninguna seleccionada');
      actualizarTooltip($('#wrap_btn_modal_disc_ext'), 'Ninguna seleccionada');
      actualizarTooltip($('#wrap_btn_modal_rep_ext'), 'Sin Representantes');
      $('#btn_modal_disc_ext').prop('disabled', true).removeClass('btn-info').addClass('btn-second');
      $('#btn_modal_rep_ext').prop('disabled', true).removeClass('btn-info').addClass('btn-second');

      $('#ext_tiene_representante').val('No');
      $('#ext_discapacidad').val('No');

      $('#contenedor_filas_patologias_ext').empty();
      $('#contenedor_filas_alergias_ext').empty();
      $('#contenedor_filas_representantes_ext').empty();
      $('.fila-disc-modal-ext').not(':first').remove();
      $('#select_disc_unica_ext').val('').html(construirOpcionesDiscapacidadesExt());
      $('#fecha_disc_unica_ext').val('');

      actualizarBotonesFooter();
    }

    function heredarCedulaDesdeFormulario() {
      var tipo = $('#tipo_cedula_paciente').val();
      var ced = $('#cedula_paciente').val();

      if (!tipo || !ced) {
        tipo = $('select[name="tipo_cedula_externo"]').val();
        ced = $('input[name="cedula_externo"]').val();
      }

      tipo = tipo || 'V';
      ced = ced || '';

      if ((tipo === 'V' || tipo === 'E') && /^[0-9]{1,8}$/.test(ced)) {
        $('#ext_tipo_cedula').val(tipo);
        $('#ext_cedula').val(ced);
      }
    }

    function snapshotCitasForm() {
      window.__citasSnapshot = null;
      if ($('#formularioCita').length) {
        window.__citasSnapshot = {};
        $('#formularioCita').find('input, select, textarea').each(function() {
          if (this.id) window.__citasSnapshot[this.id] = $(this).val();
        });
      }
    }

    function validarTab(tabName) {
      var errores = [];
      var esInterno = $('#chk_paciente_interno').is(':checked');

      var tabId = TAB_IDS[tabName];
      if (tabId) {
        $('#' + tabId).find('.input-error').removeClass('input-error');
      }

      function req(sel, label) {
        var $i = $(sel);
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
        req('#ext_telefono', 'Teléfono');
        req('#ext_email', 'Correo');
        if (esInterno) {
          req('#ext_pais_nacimiento', 'País de nacimiento');
          req('#ext_estado_nacimiento', 'Estado de nacimiento');
          req('#ext_municipio_nacimiento', 'Municipio de nacimiento');
        }

        var tipoC = $('#ext_tipo_cedula').val();
        var numC = parseInt($('#ext_cedula').val());
        if (!isNaN(numC)) {
          if (tipoC === 'V' && numC > 80000000) {
            $('#ext_cedula').addClass('input-error');
            errores.push('Cédula V- no puede ser mayor a 80.000.000');
          }
          if (tipoC === 'E' && numC < 80000000) {
            $('#ext_cedula').addClass('input-error');
            errores.push('Cédula E- no puede ser menor a 80.000.000');
          }
        }

        var edad = parseInt($('#ext_edad').val());
        if (isNaN(edad) || edad < 18) {
          $('#ext_fecha_nacimiento').addClass('input-error');
          errores.push('El paciente debe ser mayor de 18 años.');
        }

        var email = $('#ext_email').val().trim();
        if (email !== '' && (email.indexOf('@') === -1 || email.indexOf('.') === -1)) {
          $('#ext_email').addClass('input-error');
          errores.push('El correo no tiene un formato válido.');
        }
      }

      if (tabName === 'direccion' && esInterno) {
        req('#ext_estado', 'Estado (dirección)');
        req('#ext_municipio', 'Municipio (dirección)');
        req('#ext_sector', 'Sector');
      }

      if (tabName === 'salud_otros') {
        req('#ext_grupo_sanguineo', 'Grupo sanguíneo');
      }

      if (errores.length > 0) {
        mostrarAvisoGlobal('Verifique los siguientes errores:<ul><li>' + errores.join('</li><li>') + '</li></ul>');
        return false;
      }
      return true;
    }

    function construirOpcionesPatologiasExt() {
      var html = '<option value="">--- Seleccione una patología ---</option>';
      var lista = window.LISTA_PATOLOGIAS_EXT || [];
      lista.slice().sort(function(a, b) {
        return a.nombre.localeCompare(b.nombre);
      }).forEach(function(p) {
        html += '<option value="' + p.id + '" data-nombre="' + p.nombre + '" data-cie="' + (p.cie || '') + '">' + p.nombre + '</option>';
      });
      return html;
    }

    function construirOpcionesAlergiasExt() {
      var html = '<option value="">--- Seleccione una alergia ---</option>';
      var lista = window.LISTA_ALERGIAS_EXT || [];
      lista.slice().sort(function(a, b) {
        return a.nombre.localeCompare(b.nombre);
      }).forEach(function(a) {
        html += '<option value="' + a.id + '" data-nombre="' + a.nombre + '">' + a.nombre + '</option>';
      });
      return html;
    }

    function construirOpcionesDiscapacidadesExt() {
      var html = '<option value="">--- Seleccione una discapacidad ---</option>';
      var lista = window.LISTA_DISCAPACIDADES_EXT || [];
      lista.slice().sort(function(a, b) {
        return a.nombre.localeCompare(b.nombre);
      }).forEach(function(d) {
        html += '<option value="' + d.id + '" data-nombre="' + d.nombre + '">' + d.nombre + '</option>';
      });
      return html;
    }

    function agregarFilaPatologiaExt() {
      var opts = construirOpcionesPatologiasExt();
      var html = '<div class="row fila-pat-ext" style="margin-bottom: 10px;">' +
        '<div class="col-sm-6"><div class="input-group">' +
        '<select class="form-control select-pat-ext">' + opts + '</select>' +
        '<span class="input-group-btn"><button class="btn btn-info btn-search-pat-ext" type="button"><img src="../../recursos/imagenes/iconos/buscar.png" style="width:15px; height:15px;"></button></span>' +
        '</div></div>' +
        '<div class="col-sm-4"><input type="date" class="form-control input-fecha-pat-ext" max="' + getTodayISO() + '"></div>' +
        '<div class="col-sm-2"><button type="button" class="btn btn-danger btn-remove-pat-ext"><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></button></div>' +
        '</div>';
      $('#contenedor_filas_patologias_ext').append(html);
    }

    function agregarFilaAlergiaExt() {
      var opts = construirOpcionesAlergiasExt();
      var html = '<div class="row fila-ale-ext" style="margin-bottom: 10px;">' +
        '<div class="col-sm-6"><div class="input-group">' +
        '<select class="form-control select-ale-ext">' + opts + '</select>' +
        '<span class="input-group-btn"><button class="btn btn-info btn-search-ale-ext" type="button"><img src="../../recursos/imagenes/iconos/buscar.png" style="width:15px; height:15px;"></button></span>' +
        '</div></div>' +
        '<div class="col-sm-4"><input type="date" class="form-control input-fecha-ale-ext" max="' + getTodayISO() + '"></div>' +
        '<div class="col-sm-2"><button type="button" class="btn btn-danger btn-remove-ale-ext"><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></button></div>' +
        '</div>';
      $('#contenedor_filas_alergias_ext').append(html);
    }

    function agregarFilaDiscapacidadExt() {
      var opts = construirOpcionesDiscapacidadesExt();
      var html = '<div class="row fila-disc-modal-ext" style="margin-top: 15px; padding-top: 15px; border-top: 1px dashed #ddd;">' +
        '<div class="col-sm-6"><label>Tipo de Discapacidad (*):</label><select class="form-control select-disc-dinamico-ext">' + opts + '</select></div>' +
        '<div class="col-sm-4"><label>Fecha detección (Opcional):</label><input type="date" class="form-control input-fecha-disc-dinamico-ext" max="' + getTodayISO() + '"></div>' +
        '<div class="col-sm-2"><label style="display:block;">&nbsp;</label><button type="button" class="btn btn-danger btn-remove-disc-modal-ext"><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></button></div>' +
        '</div>';
      $('.fila-disc-modal-ext').last().after(html);
    }

    function agregarFilaRepExt(id, tipo, nombre, parentesco) {
      id = id || '';
      tipo = tipo || 'Legal';
      nombre = nombre || '';
      parentesco = parentesco || '';
      var opcionesParentesco = (window.PARENTESCOS_DISPONIBLES && Array.isArray(window.PARENTESCOS_DISPONIBLES)) ?
        window.PARENTESCOS_DISPONIBLES : [{
          val: 'Madre',
          txt: 'Madre'
        }, {
          val: 'Padre',
          txt: 'Padre'
        }, {
          val: 'Tutor Legal',
          txt: 'Tutor Legal'
        }, {
          val: 'Otro',
          txt: 'Otro'
        }];
      var optsHtml = '<option value="">--- Parentesco ---</option>';
      opcionesParentesco.forEach(function(op) {
        optsHtml += '<option value="' + op.val + '" ' + (op.val === parentesco ? 'selected' : '') + '>' + op.txt + '</option>';
      });
      var html = '<div class="row fila-rep-ext" style="margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px dashed #ddd;">' +
        '<div class="col-sm-4"><div class="input-group">' +
        '<input type="text" class="form-control input-nombre-rep-ext" placeholder="--- Seleccione un representante ---" value="' + nombre + '" readonly style="background:#fff;">' +
        '<input type="hidden" class="input-id-rep-ext" value="' + id + '">' +
        '<span class="input-group-btn"><button class="btn btn-info btn-search-rep-ext" type="button"><img src="../../recursos/imagenes/iconos/buscar.png" style="width:15px; height:15px;"></button></span>' +
        '</div></div>' +
        '<div class="col-sm-3"><select class="form-control select-tipo-rep-ext"><option value="Legal" ' + (tipo === 'Legal' ? 'selected' : '') + '>Representante Legal</option><option value="Suplente" ' + (tipo === 'Suplente' ? 'selected' : '') + '>Representante Suplente</option></select></div>' +
        '<div class="col-sm-3"><select class="form-control select-parentesco-rep-ext">' + optsHtml + '</select></div>' +
        '<div class="col-sm-2"><button type="button" class="btn btn-danger btn-remove-rep-ext"><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></button></div>' +
        '</div>';
      $('#contenedor_filas_representantes_ext').append(html);
    }

    $(document).ready(function() {
      setMaxFecha18();

      $(document).on('input', '#modalAgregarPacienteExterno #ext_cedula', function() {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8);
      });

      $(document).on('input', '#ext_telefono', function() {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 7);
      });

      $(document).on('input', '#ext_tiempo_residencia', function() {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 4);
      });

      $(document).on('input', '#ext_nombre, #ext_apellido', function() {
        this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]/g, '');
      });

      $(document).on('input', '#ext_avenida_calle, #ext_referencia', function() {
        this.value = this.value.replace(/[^a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s#\-\.\,]/g, '');
      });

      $(document).on('change blur', '#ext_fecha_nacimiento', calcularEdadExt);
      $(document).on('change', '#chk_paciente_interno', toggleInternoExterno);

      $(document).on('change', '#ext_etnia', function() {
        if ($(this).val() === 'Si') {
          $('#ext_tipo_etnia').prop('disabled', false);
        } else {
          $('#ext_tipo_etnia').prop('disabled', true).val('');
        }
      });

      $(document).on('change', '#ext_pais_nacimiento', function() {
        var paisId = $(this).val();
        var $estado = $('#ext_estado_nacimiento');
        var $muni = $('#ext_municipio_nacimiento');
        $estado.html('<option value="">--- Seleccione Un Estado ---</option>');
        $muni.html('<option value="">--- Seleccione Un Municipio ---</option>');
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
              $estado.append('<option value="' + e.Id_Estado + '">' + e.nombre_estado + '</option>');
            });
          }
        });
      });

      $(document).on('change', '#ext_estado_nacimiento', function() {
        var id = $(this).val();
        var $m = $('#ext_municipio_nacimiento');
        $m.html('<option value="">--- Seleccione Un Municipio ---</option>');
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
              $m.append('<option value="' + m.Id_Municipio + '">' + m.nombre_municipio + '</option>');
            });
          }
        });
      });

      $(document).on('change', '#ext_estado', function() {
        var id = $(this).val();
        var $m = $('#ext_municipio');
        var $s = $('#ext_sector');
        $m.html('<option value="">--- Seleccione Un Municipio ---</option>');
        $s.html('<option value="">--- Seleccione Un Sector ---</option>');
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
              $m.append('<option value="' + m.Id_Municipio + '">' + m.nombre_municipio + '</option>');
            });
          }
        });
      });

      $(document).on('change', '#ext_municipio', function() {
        var id = $(this).val();
        var $s = $('#ext_sector');
        $s.html('<option value="">--- Seleccione Un Sector ---</option>');
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
              $s.append('<option value="' + s.Id_Sector + '">' + s.nombre_sector + '</option>');
            });
          }
        });
      });

      $(document).on('keydown', '.input-fecha-pat-ext, .input-fecha-ale-ext, .input-fecha-disc-dinamico-ext, #fecha_disc_unica_ext', function(e) {
        if (e.which !== 9) {
          e.preventDefault();
          return false;
        }
      });

      $(document).on('change input', '.input-fecha-pat-ext, .input-fecha-ale-ext, .input-fecha-disc-dinamico-ext, #fecha_disc_unica_ext', function() {
        var v = $(this).val();
        if (!v) return;
        if (v > getTodayISO()) {
          $(this).val('');
          mostrarAvisoGlobal('No se puede seleccionar una fecha futura.');
        }
      });

      $(document).on('blur', '#modalAgregarPacienteExterno #ext_cedula', function() {
        verificarCedulaDuplicada();
      });

      $(document).on('change', '#ext_tiene_representante', function() {
        if ($(this).val() === 'Si') {
          $('#btn_modal_rep_ext').prop('disabled', false).removeClass('btn-second').addClass('btn-info');
        } else {
          $('#btn_modal_rep_ext').prop('disabled', true).removeClass('btn-info').addClass('btn-second');
          $('#ext_representantes_ids').val('');
          actualizarTooltip($('#wrap_btn_modal_rep_ext'), 'Sin Representantes');
        }
      });

      $(document).on('change', '#ext_discapacidad', function() {
        if ($(this).val() === 'Si') {
          $('#btn_modal_disc_ext').prop('disabled', false).removeClass('btn-second').addClass('btn-info');
        } else {
          $('#btn_modal_disc_ext').prop('disabled', true).removeClass('btn-info').addClass('btn-second');
          $('#ext_discapacidades_ids').val('');
          actualizarTooltip($('#wrap_btn_modal_disc_ext'), 'Ninguna seleccionada');
        }
      });

      $('#modalAgregarPacienteExterno').on('show.bs.modal', function() {
        setMaxFecha18();
        heredarCedulaDesdeFormulario();
        snapshotCitasForm();
        inicializarTooltips();
        refrescarBotonesRepDisc();
        toggleInternoExterno();
        actualizarBotonesFooter();
      });

      $('#modalAgregarPacienteExterno').on('hidden.bs.modal', function() {
        var fueGuardadoOk = window.__pacienteSaveSuccessFlag === true;
        window.__pacienteSaveSuccessFlag = false;

        if (!fueGuardadoOk) {
          restaurarCitasForm();
        }
        window.__citasSnapshot = null;

        limpiarModalPacienteExt();
        setTimeout(function() {
          if (!$('.modal.in').length) {
            $('body').removeClass('modal-open').css('padding-right', '');
            $('.modal-backdrop').remove();
          }
        }, 100);
      });

      $(document).on('click', '#btnCancelarPacienteModal', function() {
        $('#modalAgregarPacienteExterno').modal('hide');
      });

      $(document).on('click', '#btnSiguientePacienteModal', async function() {
        var btn = $(this);
        var tabActual = $('#tabsPacienteModal li.active').data('tab-name');

        if (!validarTab(tabActual)) return;

        if (tabActual === 'info') {
          btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Verificando...');
          var cedOk = await verificarCedulaDuplicada();
          btn.prop('disabled', false).text('Siguiente');
          if (!cedOk) return;
        }

        var siguiente = siguienteTabPara(tabActual);
        if (siguiente) {
          desbloquearTab(siguiente);
          activarTab(siguiente);
        }
      });

      $(document).on('click', '#btnAtrasPacienteModal', function() {
        var tabActual = $('#tabsPacienteModal li.active').data('tab-name');
        var anterior = tabAnteriorPara(tabActual);
        if (anterior) activarTab(anterior);
      });

      $(document).on('click', '#tabsPacienteModal a[data-toggle="tab"]', function(e) {
        var $li = $(this).closest('li');
        if ($li.hasClass('disabled-tab')) {
          e.preventDefault();
          e.stopPropagation();
          return false;
        }
      });

      $(document).on('click', '#btn_modal_pat_ext', function() {
        if ($('#contenedor_filas_patologias_ext').children().length === 0) agregarFilaPatologiaExt();
      });

      $(document).on('click', '#btn_modal_ale_ext', function() {
        if ($('#contenedor_filas_alergias_ext').children().length === 0) agregarFilaAlergiaExt();
      });

      $(document).on('click', '#add_fila_pat_ext', function() {
        var $ult = $('.fila-pat-ext').last();
        if ($ult.length && !$ult.find('.select-pat-ext').val()) {
          mostrarAvisoGlobal("Seleccione la patología actual antes de añadir otra.");
          return;
        }
        agregarFilaPatologiaExt();
      });

      $(document).on('click', '#add_fila_ale_ext', function() {
        var $ult = $('.fila-ale-ext').last();
        if ($ult.length && !$ult.find('.select-ale-ext').val()) {
          mostrarAvisoGlobal("Seleccione la alergia actual antes de añadir otra.");
          return;
        }
        agregarFilaAlergiaExt();
      });

      $(document).on('click', '.btn-remove-pat-ext', function() {
        var $f = $(this).closest('.fila-pat-ext');
        if ($f.find('.select-pat-ext').val() || $f.find('.input-fecha-pat-ext').val()) {
          $f.find('.select-pat-ext').val('');
          $f.find('.input-fecha-pat-ext').val('');
        } else if ($('.fila-pat-ext').length > 1) {
          $f.remove();
        }
      });

      $(document).on('click', '.btn-remove-ale-ext', function() {
        var $f = $(this).closest('.fila-ale-ext');
        if ($f.find('.select-ale-ext').val() || $f.find('.input-fecha-ale-ext').val()) {
          $f.find('.select-ale-ext').val('');
          $f.find('.input-fecha-ale-ext').val('');
        } else if ($('.fila-ale-ext').length > 1) {
          $f.remove();
        }
      });

      $(document).on('click', '#guardar_pat_listo_ext', function() {
        var vacias = 0,
          sel = [],
          nom = [];
        $('.fila-pat-ext').each(function() {
          var $s = $(this).find('.select-pat-ext');
          var id = $s.val();
          if (!id) {
            vacias++;
            return;
          }
          var f = $(this).find('.input-fecha-pat-ext').val();
          var opt = $s.find('option:selected');
          sel.push({
            id: parseInt(id),
            nombre: opt.data('nombre'),
            codigo_cie: opt.data('cie'),
            fecha: f
          });
          nom.push(opt.text().trim());
        });
        if (vacias > 0 && sel.length > 0) {
          mostrarAvisoGlobal("Hay filas de patologías sin seleccionar. Complételas o elimínelas.");
          return;
        }
        if (sel.length === 0) {
          $('#ext_patologias_ids').val('');
        } else {
          $('#ext_patologias_ids').val(JSON.stringify(sel));
        }
        actualizarTooltip($('#wrap_btn_modal_pat_ext'), nom.length ? nom.join(', ') : 'Ninguna seleccionada');
        $('#modalPatologiasExt').modal('hide');
      });

      $(document).on('click', '#guardar_ale_listo_ext', function() {
        var vacias = 0,
          sel = [],
          nom = [];
        $('.fila-ale-ext').each(function() {
          var $s = $(this).find('.select-ale-ext');
          var id = $s.val();
          if (!id) {
            vacias++;
            return;
          }
          var f = $(this).find('.input-fecha-ale-ext').val();
          var opt = $s.find('option:selected');
          sel.push({
            id: parseInt(id),
            nombre: opt.data('nombre'),
            fecha: f
          });
          nom.push(opt.text().trim());
        });
        if (vacias > 0 && sel.length > 0) {
          mostrarAvisoGlobal("Hay filas de alergias sin seleccionar. Complételas o elimínelas.");
          return;
        }
        if (sel.length === 0) {
          $('#ext_alergias_ids').val('');
        } else {
          $('#ext_alergias_ids').val(JSON.stringify(sel));
        }
        actualizarTooltip($('#wrap_btn_modal_ale_ext'), nom.length ? nom.join(', ') : 'Ninguna seleccionada');
        $('#modalAlergiasExt').modal('hide');
      });

      $(document).on('click', '#cancelar_pat_modal_ext', function() {
        $('#modalPatologiasExt').modal('hide');
      });
      $(document).on('click', '#cancelar_ale_modal_ext', function() {
        $('#modalAlergiasExt').modal('hide');
      });

      var targetPat = null;
      $(document).on('click', '.btn-search-pat-ext', function() {
        targetPat = $(this).closest('.input-group').find('.select-pat-ext');
        $('#modalBuscarPatExt').modal('show');
        $('#inputBuscarPatExt').val('').trigger('keyup');
      });

      $(document).on('keyup', '#inputBuscarPatExt', function() {
        var t = $(this).val().toLowerCase();
        var html = '';
        $('.select-pat-ext:first option').not('[value=""]').each(function() {
          if ($(this).text().toLowerCase().indexOf(t) > -1) {
            html += '<a href="#" class="list-group-item seleccionar-pat-ext" data-id="' + $(this).val() + '">' + $(this).text() + '</a>';
          }
        });
        $('#listaResultadosPatExt').html(html);
      });

      $(document).on('click', '.seleccionar-pat-ext', function(e) {
        e.preventDefault();
        targetPat.val($(this).data('id')).trigger('change');
        $('#modalBuscarPatExt').modal('hide');
      });

      var targetAle = null;
      $(document).on('click', '.btn-search-ale-ext', function() {
        targetAle = $(this).closest('.input-group').find('.select-ale-ext');
        $('#modalBuscarAleExt').modal('show');
        $('#inputBuscarAleExt').val('').trigger('keyup');
      });

      $(document).on('keyup', '#inputBuscarAleExt', function() {
        var t = $(this).val().toLowerCase();
        var html = '';
        $('.select-ale-ext:first option').not('[value=""]').each(function() {
          if ($(this).text().toLowerCase().indexOf(t) > -1) {
            html += '<a href="#" class="list-group-item seleccionar-ale-ext" data-id="' + $(this).val() + '">' + $(this).text() + '</a>';
          }
        });
        $('#listaResultadosAleExt').html(html);
      });

      $(document).on('click', '.seleccionar-ale-ext', function(e) {
        e.preventDefault();
        targetAle.val($(this).data('id')).trigger('change');
        $('#modalBuscarAleExt').modal('hide');
      });

      $(document).on('change', '.select-pat-ext', function() {
        var v = $(this).val();
        if (!v) return;
        if ($('.select-pat-ext').filter(function() {
            return $(this).val() === v;
          }).length > 1) {
          mostrarAvisoGlobal("Esa patología ya fue seleccionada.");
          $(this).val('');
        }
      });

      $(document).on('change', '.select-ale-ext', function() {
        var v = $(this).val();
        if (!v) return;
        if ($('.select-ale-ext').filter(function() {
            return $(this).val() === v;
          }).length > 1) {
          mostrarAvisoGlobal("Esa alergia ya fue seleccionada.");
          $(this).val('');
        }
      });

      $(document).on('click', '#btn_guardar_ajax_pat_ext', function() {
        var nombre = $('#ajax_nombre_pat_ext').val().trim();
        var cie = $('#ajax_cie_pat_ext').val().trim();
        var contagiosa = $('#ajax_contagiosa_pat_ext').val();
        if (!nombre) {
          mostrarAvisoGlobal("El nombre de la patología es obligatorio.");
          return;
        }
        $.ajax({
          url: window.location.href,
          method: 'POST',
          data: {
            ajax_nueva_patologia: 1,
            nombre_patologia: nombre,
            codigo_cie: cie,
            enfermedad_contagiosa: contagiosa
          },
          dataType: 'json',
          success: function(res) {
            if (res.success) {
              window.LISTA_PATOLOGIAS_EXT.push({
                id: parseInt(res.id),
                nombre: res.nombre,
                cie: res.cie || ''
              });
              var newOpts = construirOpcionesPatologiasExt();
              $('.select-pat-ext').each(function() {
                var va = $(this).val();
                $(this).html(newOpts);
                if (va) $(this).val(va);
              });
              var $vacio = $('.select-pat-ext').filter(function() {
                return !$(this).val();
              }).first();
              if ($vacio.length) $vacio.val(res.id);
              else agregarFilaPatologiaExt();
              $('#modalNuevaPatologiaExt').modal('hide');
              $('#formAjaxPatologiaExt')[0].reset();
              mostrarExitoGlobal("Patología guardada.");
            } else {
              mostrarAvisoGlobal(res.error);
            }
          },
          error: function() {
            mostrarAvisoGlobal("Error de conexión al guardar la patología.");
          }
        });
      });

      $(document).on('click', '#btn_guardar_ajax_ale_ext', function() {
        var nombre = $('#ajax_nombre_ale_ext').val().trim();
        if (!nombre) {
          mostrarAvisoGlobal("El nombre de la alergia es obligatorio.");
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
              window.LISTA_ALERGIAS_EXT.push({
                id: parseInt(res.id),
                nombre: res.nombre
              });
              var newOpts = construirOpcionesAlergiasExt();
              $('.select-ale-ext').each(function() {
                var va = $(this).val();
                $(this).html(newOpts);
                if (va) $(this).val(va);
              });
              var $vacio = $('.select-ale-ext').filter(function() {
                return !$(this).val();
              }).first();
              if ($vacio.length) $vacio.val(res.id);
              else agregarFilaAlergiaExt();
              $('#modalNuevaAlergiaExt').modal('hide');
              $('#formAjaxAlergiaExt')[0].reset();
              mostrarExitoGlobal("Alergia guardada.");
            } else {
              mostrarAvisoGlobal(res.error);
            }
          },
          error: function() {
            mostrarAvisoGlobal("Error de conexión al guardar la alergia.");
          }
        });
      });

      $(document).on('click', '#btn_add_disc_row_ext', function() {
        var ultId = $('.fila-disc-modal-ext').last().find('.select-disc-dinamico-ext').val();
        if (!ultId) {
          mostrarAvisoGlobal("Seleccione una discapacidad antes de añadir otra.");
          return;
        }
        agregarFilaDiscapacidadExt();
      });

      $(document).on('click', '.btn-remove-disc-modal-ext', function() {
        var $f = $(this).closest('.fila-disc-modal-ext');
        if ($f.find('.select-disc-dinamico-ext').val() || $f.find('.input-fecha-disc-dinamico-ext').val()) {
          $f.find('.select-disc-dinamico-ext').val('');
          $f.find('.input-fecha-disc-dinamico-ext').val('');
        } else if ($('.fila-disc-modal-ext').length > 1) {
          $f.remove();
        }
      });

      $(document).on('change', '.select-disc-dinamico-ext', function() {
        var v = $(this).val();
        if (!v) return;
        if ($('.select-disc-dinamico-ext').filter(function() {
            return $(this).val() === v;
          }).length > 1) {
          mostrarAvisoGlobal("Esa discapacidad ya fue seleccionada.");
          $(this).val('');
        }
      });

      $(document).on('click', '#btn_modal_disc_ext', function() {
        $('.fila-disc-modal-ext').not(':first').remove();
        $('#select_disc_unica_ext').html(construirOpcionesDiscapacidadesExt()).val('');
        $('#fecha_disc_unica_ext').val('');
        var data = $('#ext_discapacidades_ids').val();
        if (data && data !== '[]' && data.trim() !== '') {
          try {
            var lista = JSON.parse(data);
            if (Array.isArray(lista) && lista.length > 0) {
              $('#select_disc_unica_ext').val(lista[0].id);
              if (lista[0].fecha) $('#fecha_disc_unica_ext').val(lista[0].fecha);
              for (var i = 1; i < lista.length; i++) {
                agregarFilaDiscapacidadExt();
                $('.fila-disc-modal-ext').last().find('.select-disc-dinamico-ext').val(lista[i].id);
                if (lista[i].fecha) $('.fila-disc-modal-ext').last().find('.input-fecha-disc-dinamico-ext').val(lista[i].fecha);
              }
            }
          } catch (e) {}
        }
      });

      $(document).on('click', '#guardar_disc_listo_ext', function() {
        var vacias = 0,
          sel = [],
          nom = [];
        $('.fila-disc-modal-ext').each(function() {
          var $s = $(this).find('.select-disc-dinamico-ext');
          var id = $s.val();
          if (!id) {
            vacias++;
            return;
          }
          var f = $(this).find('.input-fecha-disc-dinamico-ext').val();
          var nombre = $s.find('option:selected').data('nombre') || '';
          sel.push({
            id: parseInt(id),
            nombre: nombre,
            fecha: f
          });
          nom.push(nombre + (f ? ' (' + f + ')' : ''));
        });
        if (vacias > 0 && sel.length > 0) {
          mostrarAvisoGlobal("Hay filas de discapacidades sin seleccionar. Complételas o elimínelas.");
          return;
        }
        if (sel.length === 0) {
          $('#ext_discapacidad').val('No');
          $('#ext_discapacidades_ids').val('');
          $('#btn_modal_disc_ext').prop('disabled', true).removeClass('btn-info').addClass('btn-second');
        } else {
          $('#ext_discapacidades_ids').val(JSON.stringify(sel));
        }
        actualizarTooltip($('#wrap_btn_modal_disc_ext'), nom.length ? nom.join(', ') : 'Ninguna seleccionada');
        $('#modalDiscapacidadesExt').modal('hide');
      });

      $(document).on('click', '#cancelar_disc_modal_ext', function() {
        $('#modalDiscapacidadesExt').modal('hide');
      });

      $(document).on('click', '#btn_modal_rep_ext', function() {
        if ($('#contenedor_filas_representantes_ext').children().length === 0) {
          var data = $('#ext_representantes_ids').val();
          var cargado = false;
          if (data && data !== '[]' && data.trim() !== '') {
            try {
              var lista = JSON.parse(data);
              if (Array.isArray(lista) && lista.length > 0) {
                lista.forEach(function(r) {
                  agregarFilaRepExt(r.id, r.tipo || 'Legal', r.nombre || '', r.parentesco || '');
                });
                cargado = true;
              }
            } catch (e) {}
          }
          if (!cargado) agregarFilaRepExt();
        }
      });

      $(document).on('click', '#add_fila_rep_ext', function() {
        var $ult = $('.fila-rep-ext').last();
        if ($ult.length && !$ult.find('.input-id-rep-ext').val()) {
          mostrarAvisoGlobal("Seleccione el representante actual antes de añadir otro.");
          return;
        }
        agregarFilaRepExt();
      });

      $(document).on('click', '.btn-remove-rep-ext', function() {
        $(this).closest('.fila-rep-ext').remove();
        if ($('.fila-rep-ext').length === 0) agregarFilaRepExt();
      });

      var filaRepDestinoExt = null;

      $(document).on('click', '.btn-search-rep-ext', function(e) {
        e.preventDefault();
        filaRepDestinoExt = $(this).closest('.fila-rep-ext');
        $('#inputBuscarRepExt').val('');
        $('#listaResultadosRepExt').html('<div class="text-muted text-center" style="padding:15px;">Escriba para buscar...</div>');
        $('#modalBuscarRepExt').modal('show');
        $('#inputBuscarRepExt').focus();
      });

      var timerRepExt = null;

      $(document).on('keyup', '#inputBuscarRepExt', function() {
        var term = $(this).val().trim();
        if (timerRepExt) clearTimeout(timerRepExt);
        if (term.length < 2) {
          $('#listaResultadosRepExt').html('<div class="text-muted text-center" style="padding:15px;">Escriba al menos 2 caracteres...</div>');
          return;
        }
        $('#listaResultadosRepExt').html('<div class="text-center" style="padding:15px;"><i class="fa fa-spinner fa-spin"></i> Buscando...</div>');
        timerRepExt = setTimeout(function() {
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
                $('#listaResultadosRepExt').html('<div class="text-muted text-center" style="padding:15px;">No se encontraron.</div>');
                return;
              }
              var html = '';
              data.forEach(function(rep) {
                var nc = (rep.nombre + ' ' + (rep.apellido || '')).trim();
                html += '<a href="#" class="list-group-item seleccionar-rep-ext" data-id="' + rep.id + '" data-nombre="' + nc + '">' +
                  '<strong>' + rep.tipo_cedula + '-' + rep.cedula + '</strong> | ' + nc + '</a>';
              });
              $('#listaResultadosRepExt').html(html);
            }
          });
        }, 300);
      });

      $(document).on('click', '.seleccionar-rep-ext', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var nombre = $(this).data('nombre');
        var dup = false;
        $('.input-id-rep-ext').each(function() {
          if ($(this).val() == id && $(this).closest('.fila-rep-ext')[0] !== filaRepDestinoExt[0]) dup = true;
        });
        if (dup) {
          mostrarAvisoGlobal("Ese representante ya está asignado en otra fila.");
          return;
        }
        filaRepDestinoExt.find('.input-id-rep-ext').val(id);
        filaRepDestinoExt.find('.input-nombre-rep-ext').val(nombre);
        $('#modalBuscarRepExt').modal('hide');
      });

      $(document).on('click', '#guardar_rep_listo_ext', function() {
        var sel = [],
          nom = [];
        $('.fila-rep-ext').each(function() {
          var id = $(this).find('.input-id-rep-ext').val();
          if (!id) return;
          var nombre = $(this).find('.input-nombre-rep-ext').val();
          var tipo = $(this).find('.select-tipo-rep-ext').val();
          var parent = $(this).find('.select-parentesco-rep-ext').val();
          sel.push({
            id: parseInt(id),
            nombre: nombre,
            tipo: tipo,
            parentesco: parent || ''
          });
          nom.push(nombre + ' (' + tipo + (parent ? ' - ' + parent : '') + ')');
        });
        if (sel.length === 0) {
          $('#ext_tiene_representante').val('No');
          $('#ext_representantes_ids').val('');
          $('#btn_modal_rep_ext').prop('disabled', true).removeClass('btn-info').addClass('btn-second');
        } else {
          $('#ext_representantes_ids').val(JSON.stringify(sel));
        }
        actualizarTooltip($('#wrap_btn_modal_rep_ext'), nom.length ? nom.join(', ') : 'Sin Representantes');
        $('#modalRepresentantesExt').modal('hide');
      });

      $(document).on('click', '#cancelar_rep_modal_ext', function() {
        $('#modalRepresentantesExt').modal('hide');
      });

      $(document).on('click', '#btn_abrir_nuevo_representante_ext', function(e) {
        e.preventDefault();
        if ($('.fila-rep-ext').length === 0) agregarFilaRepExt();
        var $ult = $('.fila-rep-ext').last();
        if ($ult.find('.input-id-rep-ext').val()) agregarFilaRepExt();
        window.filaRepDestinoParaNuevoExt = $('.fila-rep-ext').last();
        $('#modalRepresentante').modal({
          backdrop: 'static',
          keyboard: false,
          show: true
        });
      });

      window.rellenarFilaConNuevoRepresentanteExt = function(res) {
        if (!res || !res.id) return;
        var $f = window.filaRepDestinoParaNuevoExt;
        if (!$f || !$f.length) {
          agregarFilaRepExt(res.id, 'Legal', res.nombre_completo || '', '');
          return;
        }
        $f.find('.input-id-rep-ext').val(res.id);
        $f.find('.input-nombre-rep-ext').val(res.nombre_completo || '');
        window.filaRepDestinoParaNuevoExt = null;
      };

      $('#btnGuardarPacienteAjax').on('click', async function() {
        var btn = $(this);
        $('#formAjaxPacienteExterno .input-error').removeClass('input-error');

        var tabsAValidar = ['info'];
        if ($('#chk_paciente_interno').is(':checked')) {
          tabsAValidar.push('direccion');
        }
        tabsAValidar.push('salud_otros');

        var todoValido = true;
        var primerTabConError = null;
        for (var i = 0; i < tabsAValidar.length; i++) {
          if (!validarTab(tabsAValidar[i])) {
            todoValido = false;
            if (!primerTabConError) primerTabConError = tabsAValidar[i];
          }
        }

        if (!todoValido) {
          if (primerTabConError) activarTab(primerTabConError);
          return;
        }

        var cedOk = await verificarCedulaDuplicada();
        if (!cedOk) return;

        var esInterno = $('#chk_paciente_interno').is(':checked');
        var data = $('#formAjaxPacienteExterno').serialize();
        data += '&origen=' + ($('#cedula_paciente').length > 0 ? 'Interno' : 'Externo');
        data += '&ext_es_interno=' + (esInterno ? '1' : '0');

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
          url: '../../cfg/ajax/guardar_paciente.php',
          type: 'POST',
          data: data,
          dataType: 'json',
          success: function(res) {
            btn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar y Usar');
            if (!res || !res.success) {
              if (res && res.error && res.error.indexOf('ya se encuentra registrada') > -1) {
                $('#ext_cedula').addClass('input-error');
              }
              mostrarAvisoGlobal(res && res.error ? res.error : 'Error desconocido.');
              return;
            }

            window.__pacienteSaveSuccessFlag = true;

            var enCitas = $('#cedula_paciente').length > 0;
            var enDespacho = $('input[name="cedula_externo"]').length > 0;

            $('#modalAgregarPacienteExterno').modal('hide');

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

            mostrarExitoGlobal('Paciente registrado con éxito y adjuntado correctamente.');
          },
          error: function() {
            btn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar y Usar');
            mostrarAvisoGlobal('Error de conexión al guardar el paciente.');
          }
        });
      });

    });

  })();
</script>