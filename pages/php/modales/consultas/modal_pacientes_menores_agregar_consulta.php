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

  /* El modal.in y modal.out controlan la animación del modal-dialog */
  .modal.in .modal-dialog {
    animation: fadeIn 0.4s ease-out;
  }

  .modal.out .modal-dialog {
    animation: fadeOut 0.4s ease-in;
  }

  /* El backdrop usa pulse-opacity */
  .modal-open .modal-backdrop {
    opacity: 0.7 !important;
    animation: pulse-opacity 0.3s forwards;
  }

  /* ---------------------------------------------------------------------- */
  /* ESTILOS DE VALIDACIÓN Y LAYOUT */
  /* ---------------------------------------------------------------------- */
  /* CAMBIO: Color de error a crimson (rojo fuerte) */
  .input-error {
    border: 2px solid crimson !important;
    box-shadow: 0 0 5px crimson;
  }

  #avisoModalMenores {
    z-index: 999999 !important;
  }

  .modal {
    position: fixed !important;
    z-index: 99999 !important;
  }

  .modal-backdrop {
    z-index: 99998 !important;
    transition: .5s;
  }

  /* La clase 'in' es clave para que Bootstrap sepa que el modal está abierto */
  .modal.in {
    display: block;
  }

  /* MODIFICACIÓN SOLICITADA: Bloquear click en las pestañas */
  /* Esto evita que el usuario pulse las pestañas manualmente */
  .nav-tabs>li>a {
    pointer-events: none;
    cursor: default;
  }

  /* Estilos para pestañas bloqueadas visualmente */
  .nav-tabs li.disabled-tab a {
    color: #b2b2b2 !important;
    /* Color gris para indicar que está bloqueada */
  }

  /* Estilos de medico_agregar.php (Se mantienen los estilos definidos previamente) */
  @keyframes open {
    from {
      opacity: 1;
      pointer-events: none;
    }

    to {
      opacity: 1;
      pointer-events: unset;
    }
  }

  @keyframes exit {
    from {
      opacity: 0;
      pointer-events: none;
    }

    to {
      opacity: 1;
      pointer-events: unset;
    }
  }

  @keyframes b {
    from {
      opacity: 0;
      pointer-events: none;
      background-color: lightgray;
      color: black;
    }

    to {
      opacity: 1;
      pointer-events: unset;
      background-color: lightgray;
      color: black;
    }
  }

  .btn-second {
    background-color: #00c0ef;
    border-color: #00acd6;
    color: white;
    animation: b;
    animation-duration: 3s;
  }

  .pop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    background: #111111bd;
    opacity: 0;
    pointer-events: none;
    animation: open;
    animation-duration: 10s;
  }

  .modal__contain {
    width: 550px;
    height: 220px;
    margin-top: 180px;
    margin-left: 350px;
    background: #FFF;
    animation: exit;
    animation-duration: 1s;
  }

  .modal-bod {
    text-align: center;
  }

  .modal-head {
    color: white;
    background-color: green;
  }

  /* CAMBIO: Estilo para header de aviso de error usando bg-danger color*/
  .modal-header-danger {
    background-color: #dc3545;
    /* Rojo de Bootstrap bg-danger */
    color: white;
  }
</style>
<div class="modal-body">
  <div class="row">
    <div class="col-xs-12">
      <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
          <li class="active" data-tab-name="info_menor"><a href="#info_menor" data-toggle="tab">Datos Personales</a></li>
          <li data-tab-name="representante" class="disabled-tab"><a href="#representante" data-toggle="tab">Datos del Representante</a></li>
          <li data-tab-name="ocupacion_estudios_menor" class="disabled-tab"><a href="#ocupacion_estudios_menor" data-toggle="tab">Estudios Aprobados</a></li>
          <li data-tab-name="direccion_menor" class="disabled-tab"><a href="#direccion_menor" data-toggle="tab">Dirección de Residencia</a></li>
          <li data-tab-name="salud_otros_menor" class="disabled-tab"><a href="#salud_otros_menor" data-toggle="tab">Salud y Otros Datos</a></li>
        </ul>
        <div class="tab-content">
          <div class="tab-pane active" id="info_menor">
            <form id="formularioPacienteMenor" action="../../cfg/agregar_paciente_menor.php" class="form-group" method="POST" novalidate>
              <section id="new" style="margin-bottom:-2%;">
                <input type="hidden" id="esta_en_cita" name="esta_en_cita" value="Si">
                <label class="control-label"></label>
                <div class="col-sm-1 pull-left" style="margin-top: 30px;">
                  <select name="tipo_cedula" id="tipo_cedula" class="form-control" style="width: 60px;" required>
                    <option value="PN">PN-</option>
                    <option value="V">V-</option>
                    <option value="E">E-</option>
                  </select>
                </div>
                <div class="col-sm-3">
                  <p>Cédula/Documento (*):</p>
                  <input type="text" class="form-control" name="cedula" id="cedula" placeholder="N° de documento" required>
                </div>
                <label class="control-label"></label>
                <div class="col-sm-4">
                  <p>Nombre (*):</p>
                  <input type="text" class="form-control" name="nombre" id="solo_texto" placeholder="Nombre Del Paciente" maxlength="100" required>
                </div>
                <label class="control-label"></label>
                <div class="col-sm-3">
                  <p>Apellido:</p>
                  <input type="text" class="form-control" name="apellido" id="solo_texto1" placeholder="Apellido Del Paciente" maxlength="100">
                </div>
                <br><br><br><br>
                <label class="control-label"></label>
                <div class="col-sm-3">
                  <p>Fecha de nacimiento (*):</p>
                  <input type="date" class="form-control pull-right" id="fechaN" name="fecha_nacimiento_menor" onchange="calcularEdad()" required>
                </div>
                <div class="col-sm-1">
                  <p style="margin-left: 5px;">Edad</p>
                  <input type="text" class="form-control pull-right" id="edad_menor" name="edad_menor" readonly>
                </div>
                <label class="control-label"></label>
                <div class="col-sm-4">
                  <p>Sexo (*):</p>
                  <select name="genero" id="genero" class="form-control" required>
                    <option value="">--- Seleccione Un Género ---</option>
                    <option value="Masculino">Masculino</option>
                    <option value="Femenino">Femenino</option>
                  </select>
                </div>
                <label class="control-label"></label>
                <div class="col-sm-1">
                  <p>Etnia:</p>
                  <select name="etnia" id="etnia" class="form-control" required>
                    <option value="No">No</option>
                    <option value="Si">Si</option>
                  </select>
                </div>
                <div class="col-sm-2">
                  <p>Tipo etnia:</p>
                  <select name="tipo_etnia" id="tipo_etnia" class="form-control">
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
                    <option value="bari">Bari</option>
                    <option value="yanomami">Yanomami</option>
                    <option value="sanema">Sanema</option>
                    <option value="warao">Warao</option>
                    <option value="pume">Pume</option>
                    <option value="piaroa">Piaroa</option>
                    <option value="otro">Otro/No Aplica</option>
                  </select>
                </div>
                <br><br><br><br>
                <div class="col-sm-4">
                  <p>País de nacimiento (*):</p>
                  <select name="pais_nacimiento" id="pais_nacimiento" class="form-control" required>
                    <option value="">--- Seleccione un País ---</option>
                    <?php
                    // Conexión y consulta de estados
                    $conexion = new mysqli("localhost", "root", "", "cpt3db");
                    $result = $conexion->query("SELECT Id_Pais, nombre_pais FROM pais");
                    while ($row = $result->fetch_assoc()) {
                      echo "<option value='{$row['Id_Pais']}'>{$row['nombre_pais']}</option>";
                    }
                    ?>
                  </select>
                </div>
                <label class="control-label"></label>
                <div class="col-sm-4">
                  <p>Estado de nacimiento (*):</p>
                  <select name="estado_nacimiento" id="estado_nacimiento" class="form-control" required>
                    <option value="">--- Seleccione Un Estado ---</option>
                  </select>
                </div>
                <label class="control-label"></label>
                <div class="col-sm-3">
                  <p>Municipio de nacimiento (*):</p>
                  <select name="municipio_nacimiento" id="municipio_nacimiento" class="form-control" required>
                    <option value="">--- Seleccione Un Municipio ---</option>
                  </select>
                </div>
                <br><br><br><br>
                <label class="control-label"></label>
                <div class="col-sm-4">
                  <p>Analfabeta (*):</p>
                  <select name="analfabeta" id="analfabeta" class="form-control" required>
                    <option value="">--- Seleccione Una Opción ---</option>
                    <option value="Si">Si</option>
                    <option value="No">No</option>
                  </select>
                </div>
                <label class="control-label"></label>
                <div class="col-sm-3" style="visibility:hidden;">
                  <p>Cotiza seguro social:</p>
                  <select name="seguro_social" class="form-control">
                    <option value="">--- Seleccione Una Opción ---</option>
                  </select>
                </div>
                <label class="control-label"></label>
                <br><br><br><br>
                <div style="float:right; margin-top:-4%;">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Regresar</button>
                  <button type="button" class="btn btn-primary next-tab" data-tab-actual="#info_menor" data-tab-siguiente="representante">Siguiente</button>
                </div>
              </section>
          </div>

          <div class="tab-pane" id="representante">
            <section id="new" style="margin-bottom:12%;">
              <div class="col-sm-12">
                <h4>Información del Representante Legal/Responsable</h4>
              </div>
              <label class="control-label"></label>
              <div class="col-sm-1 pull-left" style="margin-top: 30px;">
                <select name="tipo_cedula_rep" id="tipo_cedula_rep" class="form-control" style="width: 60px;" required>
                  <option value="V">V-</option>
                  <option value="E">E-</option>
                </select>
              </div>
              <div class="col-sm-3">
                <p>Cédula del Rep. (*):</p>
                <input type="text" class="form-control" name="cedula_rep" id="cedula_rep" placeholder="N° de Cédula" maxlength="8" required>
              </div>
              <label class="control-label"></label>
              <div class="col-sm-4">
                <p>Nombre del Rep. (*):</p>
                <input type="text" class="form-control" name="nombre_rep" id="nombre_rep" placeholder="Nombre Del Representante" maxlength="100" required>
              </div>
              <label class="control-label"></label>
              <div class="col-sm-3">
                <p>Apellido del Rep. (*):</p>
                <input type="text" class="form-control" name="apellido_rep" id="apellido_rep" placeholder="Apellido Del Representante" maxlength="100" required>
              </div>
              <br><br><br><br>
              <label class="control-label"></label>
              <div class="col-sm-3">
                <p>Fecha de nacimiento (*):</p>
                <input type="date" class="form-control pull-right" id="fechaN_rep" name="fecha_nacimiento_rep" onchange="calcularEdadRep()" required>
              </div>
              <div class="col-sm-1">
                <p style="margin-left: 5px;">Edad</p>
                <input type="text" class="form-control pull-right" id="edad_rep" name="edad_rep" readonly>
              </div>
              <label class="control-label"></label>
              <div class="col-sm-1 pull-left" style="margin-top: 30px;">
                <select name="prefijo_rep" class="form-control" id="prefijo_rep" style="width: 70px;">
                  <option value="">Prefijo</option>
                  <?php
                  include('../../cfg/conexion.php');
                  $sql = $conexion->query("SELECT * FROM prefijos_telefonos");
                  while ($resultado = $sql->fetch_assoc()) {
                    echo "<option value='" . $resultado["Id"] . "'>" . $resultado['prefijo'] . "</option>";
                  }
                  ?>
                </select>
              </div>
              <div class="col-sm-3">
                <p>Teléfono del Rep. (*):</p>
                <input type="text" class="form-control" name="telefono_rep" id="telefono_rep" placeholder="N° De Teléfono" maxlength="7" required>
              </div>
              <label class="control-label"></label>
              <div class="col-sm-3">
                <p>Email del Rep:</p>
                <input type="email_rep" class="form-control" name="email_rep" id="email_rep" placeholder="nombreapellido2@gmail.com">
              </div>
              <br><br><br><br>
              <label class="control-label"></label>
              <div class="col-sm-4">
                <p>Sexo del Rep. (*):</p>
                <select name="genero_rep" id="genero_rep" class="form-control" required>
                  <option value="">--- Seleccione Un Género ---</option>
                  <option value="Masculino">Masculino</option>
                  <option value="Femenino">Femenino</option>
                </select>
              </div>
              <label class="control-label"></label>
              <div class="col-sm-3">
                <p>Parentesco (*):</p>
                <select name="parentesco" id="parentesco" class="form-control" required>
                  <option value="">--- Seleccione Parentesco ---</option>
                  <option value="Padre">Padre</option>
                  <option value="Madre">Madre</option>
                  <option value="Abuelo(a)">Abuelo(a)</option>
                  <option value="Tío(a)">Tío(a)</option>
                  <option value="Tutor Legal">Tutor Legal</option>
                  <option value="Otro">Otro</option>
                </select>
              </div>
              <div style="float:right; margin-top:4%;">
                <button type="button" class="btn btn-secondary prev-tab" data-tab-anterior="#info_menor">Atrás</button>
                <button type="button" class="btn btn-primary next-tab" data-tab-actual="#representante" data-tab-siguiente="ocupacion_estudios_menor">Siguiente</button>
              </div>
            </section>
          </div>

          <div class="tab-pane" id="ocupacion_estudios_menor">
            <section id="new" style="margin-bottom:14%;">
              <label class="control-label"></label>
              <div class="col-sm-4">
                <p>Nivel de instrucción:</p>
                <select name="nivel_instruccion" id="nivel_instruccion" class="form-control">
                  <option value="">--- Seleccione Un Nivel De Instrucción ---</option>
                  <option value="sin_instruccion">Sin Instrucción</option>
                  <option value="primaria_incompleta">Primaria Incompleta</option>
                  <option value="primaria_completa">Primaria Completa</option>
                  <option value="bachillerato_incompleto">Educación Media Incompleta</option>
                  <option value="bachiller">Bachiller (Media General)</option>
                </select>
              </div>
              <label class="control-label"></label>
              <div class="col-sm-4">
                <p>Misión educativa:</p>
                <select name="mision" id="mision" class="form-control">
                  <option value="">--- Seleccione Una Misión ---</option>
                  <option value="robinson">Robinson</option>
                  <option value="ribas">Ribas</option>
                  <option value="Sucre">Sucre</option>
                </select>
              </div>
              <label class="control-label"></label>
              <div class="col-sm-3">
                <p>Años aprobados:</p>
                <input type="number" id="años_aprobados" class="form-control" name="años_aprobados" min="0">
                <small id="años_help" class="form-text text-muted"></small>
              </div>
              <div style="float:right; margin-top:6%;">
                <button type="button" class="btn btn-secondary prev-tab" data-tab-anterior="#representante">Atrás</button>
                <button type="button" class="btn btn-primary next-tab" data-tab-actual="#ocupacion_estudios_menor" data-tab-siguiente="direccion_menor">Siguiente</button>
              </div>
            </section>
          </div>
          <div class="tab-pane" id="direccion_menor">
            <section id="new" style="margin-bottom:8%;">
              <label class="control-label"></label>
              <div class="col-sm-4">
                <p>Estado (*):</p>
                <select name="estado" id="estado" class="form-control" required>
                  <option value="">--- Seleccione Un Estado ---</option>
                  <?php
                  // Conexión y consulta de estados
                  $conexion = new mysqli("localhost", "root", "", "cpt3db");
                  $result = $conexion->query("SELECT Id_Estado, nombre_estado, Id_Pais FROM estado HAVING Id_Pais = 1");
                  while ($row = $result->fetch_assoc()) {
                    echo "<option value='{$row['Id_Estado']}'>{$row['nombre_estado']}</option>";
                  }
                  ?>
                </select>
              </div>
              <label class="control-label"></label>
              <div class="col-sm-4">
                <p>Municipio (*):</p>
                <select name="municipio" id="municipio" class="form-control" required>
                  <option value="">--- Seleccione Un Municipio ---</option>
                </select>
              </div>
              <label class="control-label"></label>
              <div class="col-sm-3">
                <p>Sector (*):</p>
                <select name="sector_menor" id="sector_menor" class="form-control" required>
                  <option value="">--- Seleccione Un Sector ---</option>
                </select>
              </div>
              <br><br><br><br>
              <label class="control-label"></label>
              <div class="col-sm-4">
                <p>Avenida/calle:</p>
                <input type="text" class="form-control" name="avenida_calle">
              </div>
              <label class="control-label"></label>
              <div class="col-sm-4">
                <p>Punto de referencia:</p>
                <input type="text" class="form-control" name="referencia">
              </div>
              <label class="control-label"></label>
              <div class="col-sm-1">
                <p>Tiempo:</p>
                <input type="text" class="form-control" name="tiempo_residencia" id="tiempo_residencia" placeholder="ej. 1">
              </div>
              <div class="col-sm-2">
                <p>Dias/Meses/Etc:</p>
                <select name="tiempo" id="tiempo" class="form-control">
                  <option value="dia/s">Dia/s</option>
                  <option value="semanas/s">Semanas/s</option>
                  <option value="meses/s">Meses/s</option>
                  <option value="años/s">Año/s</option>
                </select>
              </div>
              <br><br><br><br>
              <div style="float:right; margin-top:4%;">
                <button type="button" class="btn btn-secondary prev-tab" data-tab-anterior="#ocupacion_estudios_menor">Atrás</button>
                <button type="button" class="btn btn-primary next-tab" data-tab-actual="#direccion_menor" data-tab-siguiente="salud_otros_menor">Siguiente</button>
              </div>
            </section>
          </div>
          <div class="tab-pane" id="salud_otros_menor">
            <section id="new" style="margin-bottom:11%;">
              <label class="control-label"></label>
              <div class="col-sm-5">
                <p>Patologias:</p>
                <div id="patologias_agregadas" class="form-control" style="height: auto; min-height: 34px;" readonly>
                  <span class="text-muted">Ninguna patología seleccionada.</span>
                </div>
              </div>
              <div class="col-sm-1" style="margin-top: 30px;">
                <button type="button" class="form-control pull-right bt-sm btn-primary" data-toggle="modal" data-target="#modalPatologiasMenores">
                  +
                </button>
                <input type="hidden" name="patologias_ids" id="patologias_ids">
              </div>
              <label class="control-label"></label>
              <div class="col-sm-4">
                <p>Alergias:</p>
                <div id="alergias_agregadas" class="form-control" style="height: auto; min-height: 34px;" readonly>
                  <span class="text-muted">Ninguna Alergia seleccionada.</span>
                </div>
              </div>
              <div class="col-sm-1" style="margin-top: 30px;">
                <button type="button" class="form-control pull-right bt-sm btn-primary" data-toggle="modal" data-target="#modalAlergiasMenores">
                  +
                </button>
                <input type="hidden" name="alergias_ids" id="alergias_ids">
              </div>
              <br><br><br><br><br><br>
              <label class="control-label"></label>
              <div class="col-sm-5">
                <p>Grupo sanguíneo (*):</p>
                <select name="grupo_sanguineo" id="grupo_sanguineo" class="form-control" required>
                  <option value="">--- Seleccione Un Tipo De Sangre ---</option>
                  <option value="A+">A+</option>
                  <option value="A-">A-</option>
                  <option value="B+">B+</option>
                  <option value="B-">B-</option>
                  <option value="O+">O+</option>
                  <option value="O-">O-</option>
                  <option value="AB+">AB+</option>
                  <option value="AB-">AB-</option>
                </select>
              </div>
              <label class="control-label"></label>
              <div class="col-sm-1">
                <p>Discap.:</p>
                <select name="discapacidad" id="discapacidad" class="form-control" required>
                  <option value="No">No</option>
                  <option value="Si">Si</option>
                </select>
              </div>
              <div class="col-sm-5">
                <p>Tipo de discapacidad:</p>
                <select name="tipo_discapacidad" id="tipo_discapacidad" class="form-control">
                  <option value="">--- Seleccione Una Discapacidad ---</option>
                  <option value="fisico_motora">Físico-Motora</option>
                  <option value="visual">Visual</option>
                  <option value="auditiva">Auditiva</option>
                  <option value="intelectual">Intelectual</option>
                  <option value="psicosocial">Psicosocial</option>
                  <option value="multiple">Múltiple</option>
                </select>
              </div>
              <div style="float:right; margin-top:3%;">
                <button type="button" class="btn btn-secondary prev-tab" data-tab-anterior="#direccion_menor">Atrás</button>
                <button type="button" class="btn btn-success next-tab" data-tab-actual="#salud_otros_menor" data-tab-siguiente="confirmar">Guardar</button>
              </div>
            </section>
          </div>
        </div>
      </div>
    </div>
  </div>
  </form>
</div>

<div class="modal" id="modalPatologiasMenores" tabindex="-1" role="dialog" aria-labelledby="modalPatologiasMenoresLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background-color: #337ab7; color: white;">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="modalPatologiasMenoresLabel"><i class="fa fa-medkit"></i> Seleccionar Patologías</h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-sm-12">
            <label for="lista_patologias_seleccionadas_menores">Patologías en lista temporal:</label>
            <div id="lista_patologias_seleccionadas_menores" class="well well-sm" style="min-height: 50px;">
              Ninguna patología en la lista temporal.
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="modal_patologia_menores">Patología disponible (*)</label>
              <select id="modal_patologia_menores" class="form-control">
                <option value="disabled">--- Seleccione Una Patología ---</option>
                <?php
                $conexion_modal = new mysqli("localhost", "root", "", "cpt3db");
                $result_modal = $conexion_modal->query("SELECT Id_patologia, nombre_patologia FROM patologias");
                while ($row_modal = $result_modal->fetch_assoc()) {
                  echo "<option value='{$row_modal['Id_patologia']}' data-nombre='{$row_modal['nombre_patologia']}'>{$row_modal['nombre_patologia']}</option>";
                }
                ?>
              </select>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="modal_codigo_cie_menores">Código CIE-10</label>
              <input type="text" id="modal_codigo_cie_menores" class="form-control" readonly>
            </div>
          </div>
        </div>
        <button type="button" id="agregarPatologia_menores" class="btn btn-info pull-right"><i class="fa fa-plus"></i> Añadir</button>
        <div class="clearfix"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-success" id="guardarPatologias_menores">Guardar Selección</button>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="modalNuevaPatologiaMenores" tabindex="-1" role="dialog" aria-labelledby="modalNuevaPatologiaMenoresLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background-color: #31b0d5; color: white;">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="modalNuevaPatologiaMenoresLabel"><i class="fa fa-plus-square"></i> Nueva Patología</h4>
      </div>
      <div class="modal-body">
        <form id="formNuevaPatologia">
          <div class="form-group">
            <label for="nuevo_nombre_patologia_menores">Nombre de la Patología (*)</label>
            <input type="text" class="form-control" id="nuevo_nombre_patologia_menores" name="nombre_patologia" required>
          </div>
          <div class="form-group">
            <label for="nuevo_codigo_cie_menores">Código CIE-10 (*)</label>
            <input type="text" class="form-control" id="nuevo_codigo_cie_menores" name="codigo_cie" required>
          </div>
          <div class="form-group">
            <label for="nueva_es_contagiosa">Enfermedad Contagiosa (*)</label>
            <select class="form-control" id="nueva_es_contagiosa" name="enfermedad_contagiosa" required>
              <option value="">--- Seleccione Una Opción ---</option>
              <option value="SI">SÍ</option>
              <option value="NO">NO</option>
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" id="btnGuardarNuevaPatologiaAjaxMenores">Guardar Patología</button>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="modalAlergiasMenores" tabindex="-1" role="dialog" aria-labelledby="modalAlergiasMenoresLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background-color: #337ab7; color: white;">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="modalAlergiasMenoresLabel"><i class="fa fa-wheelchair"></i> Seleccionar Alergias</h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-sm-12">
            <label for="lista_alergias_seleccionadas_menores">Alergias en lista temporal:</label>
            <div id="lista_alergias_seleccionadas_menores" class="well well-sm" style="min-height: 50px;">
              Ninguna alergia en la lista temporal.
            </div>
          </div>
          <div class="col-sm-12">
            <div class="form-group">
              <label for="modal_alergia_menores">Alergia disponible (*)</label>
              <select id="modal_alergia_menores" class="form-control">
                <option value="disabled">--- Seleccione Una Alergia ---</option>
                <?php
                // Conexión y consulta de alergias
                $conexion_modal_alergia_menores = new mysqli("localhost", "root", "", "cpt3db");
                $result_modal_alergia_menores = $conexion_modal_alergia_menores->query("SELECT Id_alergias_conocidas, nombre_alergia FROM alergias_conocidas");
                while ($row_modal = $result_modal_alergia_menores->fetch_assoc()) {
                  echo "<option value='{$row_modal['Id_alergias_conocidas']}' data-nombre='{$row_modal['nombre_alergia']}'>{$row_modal['nombre_alergia']}</option>";
                }
                ?>
              </select>
            </div>
          </div>
        </div>
        <button type="button" id="agregarAlergiaMenores" class="btn btn-info pull-right"><i class="fa fa-plus"></i> Añadir</button>
        <div class="clearfix"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-success" id="guardarAlergiasMenores">Guardar Selección</button>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="modalNuevaAlergiaMenores" tabindex="-1" role="dialog" aria-labelledby="modalNuevaAlergiaMenoresLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background-color: #31b0d5; color: white;">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="modalNuevaAlergiaMenoresLabel"><i class="fa fa-plus-square"></i> Nueva Alergia</h4>
      </div>
      <div class="modal-body">
        <form id="formNuevaAlergia">
          <div class="form-group">
            <label for="nuevo_nombre_alergia_menores">Nombre de la Alergia (*)</label>
            <input type="text" class="form-control" id="nuevo_nombre_alergia_menores" name="nombre_alergia" required>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" id="btnGuardarNuevaAlergiaAjaxMenores">Guardar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="avisoModalMenores" tabindex="-1" role="dialog" aria-labelledby="avisoModalMenoresLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header modal-header-danger">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="avisoModalMenoresLabel"><i class="fa fa-exclamation-triangle"></i> Aviso</h4>
      </div>
      <div class="modal-body">
        <p id="avisoTextoMenores"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="modalGuardarMenores" tabindex="-1" role="dialog" aria-labelledby="modalGuardarMenoresLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background-color: #00a65a; color: white;">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="modalGuardarMenoresLabel"><i class="fa fa-save"></i> Confirmacion de Guardado</h4>
      </div>
      <div class="modal-body">
        <p>¿Está seguro de que desea guardar la información del nuevo paciente?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Regresar</button>
        <button type="button" class="btn btn-success" id="confirmandoGuardadoFinal">Guardar</button>
      </div>
    </div>
  </div>

  <?php
  // Lógica para preparar los mensajes de los modales de sesión
  $mostrar_modal_exito = false;
  $mostrar_modal_error = false;
  $mensaje_modal = '';

  // Usar las variables consistentes: 'mensaje_user_exito' y 'mensaje_user_error'
  if (isset($_SESSION['mensaje_user_exito'])) {
    $mostrar_modal_exito = true;
    $mensaje_modal = $_SESSION['mensaje_user_exito'];
    unset($_SESSION['mensaje_user_exito']); // Limpiar la sesión
  } elseif (isset($_SESSION['mensaje_user_error'])) {
    $mostrar_modal_error = true;
    $mensaje_modal = $_SESSION['mensaje_user_error'];
    unset($_SESSION['mensaje_user_error']); // Limpiar la sesión
  }
  ?>

  <script>
    // Variables globales para manejar las selecciones
    let patologiasSeleccionadas = [];
    let alergiasSeleccionadas = [];

    // --- Funciones auxiliares ---
    function mostrarAvisoMenores(mensaje) {
      $('#avisoTextoMenores').html(mensaje);
      $('#avisoModalMenores').modal('show');
    }

    function limpiarErroresPestanaMenores(tabSelector) {
      $(tabSelector).find('.input-error').removeClass('input-error');
      $('#patologias_agregadas').removeClass('input-error');
    }

    // Función que bloquea números (solo texto)
    function bloquearNumerosMenores(e) {
      const teclasPermitidas = ["Backspace", "Tab", "ArrowLeft", "ArrowRight", "Delete", "Shift"];
      if (teclasPermitidas.includes(e.key)) return;
      if (e.key >= "0" && e.key <= "9") {
        e.preventDefault();
      }
    }
    // Función que limpia números pegados (solo texto)
    function limpiarNumerosMenores(e) {
      e.target.value = e.target.value.replace(/[0-9]/g, "");
    }

    function calcularEdad() {
      const fechaNac = document.getElementById('fechaN').value;
      if (!fechaNac) {
        document.getElementById('edad_menor').value = '';
        return;
      }

      const hoy = new Date();
      const cumple = new Date(fechaNac);
      let edad = hoy.getFullYear() - cumple.getFullYear();
      const m = hoy.getMonth() - cumple.getMonth();

      if (m < 0 || (m === 0 && hoy.getDate() < cumple.getDate())) {
        edad--;
      }
      document.getElementById('edad_menor').value = edad;
    }

    // --- LÓGICA PARA FECHA DE NACIMIENTO DEL REPRESENTANTE ---

    // 1. Establecer fecha MÁXIMA del REPRESENTANTE para asegurar que no sea MAYOR de hoy.
    function setMaxDateMenoresRep() {
      const today = new Date();
      // CALCULAMOS LA FECHA EXACTA HACE 18 AÑOS
      const eighteenYearsAgo = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());
      const maxDate = eighteenYearsAgo.toISOString().split('T')[0]; // Esta es la fecha máxima permitida

      // Asumiendo que el campo de fecha de nacimiento del representante tiene el ID 'fechaN_rep'
      const fechaNRepInput = document.getElementById('fechaN_rep');
      if (fechaNRepInput) {
        // Al establecer 'max' a 18 años atrás, se asegura que solo se pueda seleccionar fechas
        // anteriores a ese límite, garantizando la mayoría de edad.
        fechaNRepInput.setAttribute('max', maxDate);
      }
    }
    setMaxDateMenoresRep(); // Ejecuta al cargar

    // Función para calcular la edad del representante (para validación > 18)
    function calcularEdadRep() {
      const fechaNac = document.getElementById('fechaN_rep').value;
      const edadInput = document.getElementById('edad_rep'); // Asumiendo un campo de edad para el rep

      if (!fechaNac) {
        if (edadInput) edadInput.value = '';
        return;
      }

      const hoy = new Date();
      const cumple = new Date(fechaNac);
      let edad = hoy.getFullYear() - cumple.getFullYear();
      const m = hoy.getMonth() - cumple.getMonth();

      if (m < 0 || (m === 0 && hoy.getDate() < cumple.getDate())) {
        edad--;
      }
      if (edadInput) edadInput.value = edad;
      return edad;
    }

    // Evento para calcular la edad del representante al cambiar la fecha
    const fechaNRepInput = document.getElementById('fechaN_rep');
    if (fechaNRepInput) {
      fechaNRepInput.addEventListener('change', calcularEdadRep);
    }

    // --- LÓGICA DE VALIDACIÓN DE CÉDULA Y NÚMERICOS ---

    const cedulaInput = document.getElementById('cedula');
    const tipoCedulaSelect = document.getElementById('tipo_cedula');
    const cedulaRepInput = document.getElementById('cedula_rep');
    const tipoCedulaRepSelect = document.getElementById('tipo_cedula_rep');
    const telefonoRepInput = document.getElementById('telefono_rep');

    /*
     * Función que restringe la entrada de caracteres a solo números.
     * Se usa para Cédula de Representante y Teléfono.
     */
    function restringirSoloNumerosMenores(e) {
      this.value = this.value.replace(/[^0-9]/g, '');
    }

    // Aplicar filtro dinámico de caracteres y longitud para Cédula/PN del Paciente
    if (cedulaInput) cedulaInput.addEventListener('input', filtrarCedulaPorTipoMenores);
    if (tipoCedulaSelect) tipoCedulaSelect.addEventListener('change', filtrarCedulaPorTipoMenores);

    // Aplicar restricción de solo números a Cédula Rep. y Teléfono
    if (cedulaInput) cedulaInput.addEventListener('input', restringirSoloNumerosMenores);
    if (cedulaRepInput) cedulaRepInput.addEventListener('input', restringirSoloNumerosMenores);
    if (telefonoRepInput) telefonoRepInput.addEventListener('input', restringirSoloNumerosMenores);

    /*
     * Función que restringe la entrada de caracteres a solo números si es V o E,
     * aplica límites de longitud estricta en tiempo real y actualiza el maxlength HTML.
     */
    function filtrarCedulaPorTipoMenores() {
      const tipo = tipoCedulaSelect.value;
      let valor = cedulaInput.value;
      let longitudMaxima = 0;
      let nuevoValor = valor; // Usar una variable temporal para el valor filtrado

      // 1. Determinar longitud máxima y caracteres permitidos
      if (tipo === 'V' || tipo === 'E') {
        // V y E: Solo números, máx 9 dígitos.
        nuevoValor = valor.replace(/[^0-9]/g, '');
        longitudMaxima = 8; // Límite solicitado
      } else if (tipo === 'PN') {
        // PN: Permite números, letras, guiones y espacios, máx 20 caracteres.
        nuevoValor = valor.replace(/[^0-9a-zA-Z- ]/g, '');
        longitudMaxima = 20; // Límite solicitado
      } else {
        // Caso por defecto si no se selecciona nada
        nuevoValor = valor;
        longitudMaxima = 20;
      }

      // 2. Aplicar el límite de longitud al input (usando el atributo HTML para máxima fiabilidad)
      if (longitudMaxima > 0) {
        cedulaInput.maxLength = longitudMaxima; // Establecer el atributo nativo
      }

      // 3. Actualizar el valor del input con los caracteres filtrados
      // El navegador aplicará el maxLength por el punto 2.
      cedulaInput.value = nuevoValor;

      // Mantenemos la restricción numérica para el representante y teléfono (opcionalmente)
      if (cedulaRepInput) cedulaRepInput.value = cedulaRepInput.value.replace(/[^0-9]/g, '');
      if (telefonoRepInput) telefonoRepInput.value = telefonoRepInput.value.replace(/[^0-9]/g, '');
    }

    // Llamar a la función una vez para establecer el estado inicial (maxLength y filtro)
    if (cedulaInput && tipoCedulaSelect) filtrarCedulaPorTipoMenores();

    async function verificarCedulaMenorEnBD(tipo, cedula, esRepresentante = false) {
      return new Promise((resolve, reject) => {
        if (cedula === "" || tipo === "") {
          resolve(false);
          return;
        }

        // Uso de un endpoint genérico para verificar cedula si no existe uno específico para representantes
        const urlVerificacion = esRepresentante ? 'get/get_verificar_cedula.php' : 'get/get_verificar_cedula.php';

        $.ajax({
          url: urlVerificacion,
          method: 'POST',
          dataType: 'json',
          data: {
            tipo_cedula: tipo,
            cedula: cedula
          },
          success: function(response) {
            resolve(response.existe);
          },
          error: function(xhr, status, error) {
            console.error("Error de conexión/servidor al verificar cédula.", status, error);
            reject('Error de Conexión: No se pudo comunicar con el servidor para verificar la cédula.');
          }
        });
      });
    }

    async function validarCedulaFormatoMenores() {
      const valorCedula = cedulaInput.value.trim(); // Obtenemos el valor como string primero
      const cedula = parseInt(valorCedula); // Se usará solo para chequeo de rango V/E
      const tipo = tipoCedulaSelect.value;
      const longitud = valorCedula.length;
      let minLongitud = 0;
      let maxLongitud = 0;
      let errorTexto = '';
      let esValido = true;

      // 1. Limpiar errores visuales previos
      $(cedulaInput).removeClass('input-error');
      $(tipoCedulaSelect).removeClass('input-error');

      // 2. Validar vacíos inmediatamente para poner el borde rojo
      if (valorCedula === "") {
        $(cedulaInput).addClass('input-error');
        return false;
      }

      // * LÓGICA DE VALIDACIÓN DE LONGITUD Y RANGOS *
      if (tipo === 'V' || tipo === 'E') {
        // Configuración para Cédula (V/E)
        minLongitud = 2;
        maxLongitud = 8; // Límite solicitado: 9 dígitos
        errorTexto = 'El número de Cédula (' + tipo + '-) debe tener entre 2 y 9 dígitos.';

      } else if (tipo === 'PN') {
        // Configuración para Partida de Nacimiento (PN)
        minLongitud = 2;
        maxLongitud = 20; // Límite solicitado: 20 caracteres
        errorTexto = 'El número de Partida de Nacimiento debe tener entre 2 y 20 caracteres.';
      }

      // Aplicar la validación de rango de longitud (Min y Max)
      if (longitud < minLongitud || longitud > maxLongitud) {
        $(cedulaInput).addClass('input-error');
        mostrarAvisoMenores('🛑 Error de Longitud: ' + errorTexto);
        esValido = false;
        return false;
      }

      // Validaciones de rango numérico (Solo para V y E)
      if (tipo === 'V' || tipo === 'E') {
        if (tipo === 'V' && cedula > 80000000) {
          $(cedulaInput).addClass('input-error');
          mostrarAvisoMenores('🛑 Error de Cédula (V-): La cédula no puede ser mayor a 80.000.000');
          esValido = false;
        } else if (tipo === 'E' && cedula < 80000000) {
          $(cedulaInput).addClass('input-error');
          mostrarAvisoMenores('🛑 Error de Cédula (E-): La cédula no puede ser menor a 80.000.000');
          esValido = false;
        }
      }

      if (!esValido) return false;

      // 3. Verificar existencia en BD
      try {
        // Corregido: Si es PN, enviamos el valor alfanumérico (valorCedula) para el chequeo
        const cedulaVerificar = (tipo === 'V' || tipo === 'E') ? cedula : valorCedula;

        const existe = await verificarCedulaMenorEnBD(tipo, cedulaVerificar);
        if (existe) {
          $(cedulaInput).addClass('input-error');
          mostrarAvisoMenores('🛑 Error de Cédula: El documento ' + tipo + '-' + valorCedula + ' ya se encuentra registrado en el sistema.');
          esValido = false;
        }
      } catch (error) {
        console.error(error);
        // Si falla la conexión, permitimos continuar o muestras aviso según prefieras
      }

      return esValido;
    }


    // Función de validación y AUTO-RELLENO de cédula del representante (similar, pero con su propio evento)
    function setMaxDateMenores() {
      const today = new Date();
      // La fecha máxima debe ser HOY.
      const maxDate = today.toISOString().split('T')[0];

      // CAMBIO 1: La fecha MÍNIMA debe ser exactamente 18 años atrás, 
      // para asegurar que el paciente sea menor de 18.
      const eighteenYearsAgo = new Date(today.getFullYear() - 17, today.getMonth(), today.getDate());
      const minDate = eighteenYearsAgo.toISOString().split('T')[0];

      document.getElementById('fechaN').setAttribute('max', maxDate);
      document.getElementById('fechaN').setAttribute('min', minDate); // Establece el limite inferior
    }
    setMaxDateMenores();



    // --- LÓGICA PARA FECHA DE NACIMIENTO DEL REPRESENTANTE ---

    // ... (El resto de funciones hasta validarCedulaRepFormato() se mantiene igual, excepto el cambio 2)

    // Función de validación y AUTO-RELLENO de cédula del representante
    async function validarCedulaRepFormato() {
      const cedula = cedulaRepInput.value.trim();
      const tipo = tipoCedulaRepSelect.value;
      let esValido = true;

      $(cedulaRepInput).removeClass('input-error');
      $(tipoCedulaRepSelect).removeClass('input-error');

      // CAMBIO 2: Marcar como error si está vacío el tipo o la cédula
      if (tipo === "") {
        $(tipoCedulaRepSelect).addClass('input-error');
        mostrarAvisoMenores('🛑 Error del Representante: Debe seleccionar el Tipo de Cédula del Representante.');
        esValido = false;
      }

      if (cedula === "") {
        $(cedulaRepInput).addClass('input-error');
        mostrarAvisoMenores('🛑 Error del Representante: El campo Cédula del Representante es obligatorio.');
        esValido = false;
      }

      if (!esValido) return false; // Detener si faltan campos

      // Validación de rango (se mantiene)
      const cedulaNum = parseInt(cedula);
      if (tipo === 'V' && cedulaNum > 80000000) {
        $(cedulaRepInput).addClass('input-error');
        mostrarAvisoMenores('🛑 Error de Cédula (V-): La cédula del Representante no puede ser mayor a 80.000.000');
        esValido = false;
      } else if (tipo === 'E' && cedulaNum < 80000000) {
        $(cedulaRepInput).addClass('input-error');
        mostrarAvisoMenores('🛑 Error de Cédula (E-): La cédula del Representante no puede ser menor a 80.000.000');
        esValido = false;
      }

      // Si la validación de formato pasa, intentar buscar el representante
      if (esValido) {
        buscarRepresentantePorCedula(tipo, cedula);
      }

      return esValido;
    }

    /*
     * Busca y rellena los datos del representante usando AJAX
     */
    function alternarCamposRepresentante(bloquear) {
      // Inputs de texto: usamos prop('readonly')
      $('#nombre_rep').prop('readonly', bloquear);
      $('#fechaN_rep').prop('readonly', bloquear);
      $('#apellido_rep').prop('readonly', bloquear);
      $('#telefono_rep').prop('readonly', bloquear);
      $('#email_rep').prop('readonly', bloquear);

      // Selects (Prefijo): usamos prop('disabled') porque readonly no funciona en selects
      $('#prefijo_rep').prop('disabled', bloquear);
      $('#genero_rep').prop('disabled', bloquear);

      // Cambio visual (opcional) para que el usuario sepa que están bloqueados
      if (bloquear) {
        $('#nombre_rep, #apellido_rep, #fechaN_rep, #telefono_rep, #email_rep').css('background-color', '#eee');
        $('#prefijo_rep, #genero_rep').css('background-color', '#eee');
      } else {
        $('#nombre_rep, #apellido_rep, #fechaN_rep, #telefono_rep, #email_rep').css('background-color', '#fff');
        $('#prefijo_rep, #genero_rep').css('background-color', '#fff');
      }
    }

    /*
     * Busca el representante y maneja los datos separados (prefijo/numero)
     */
    function buscarRepresentantePorCedula(tipo, cedula) {
      if (cedula.length < 4) return; // Mínimo de caracteres para buscar

      // Indicador visual de carga
      $('#nombre_rep').attr('placeholder', 'Buscando datos...');

      $.ajax({
        url: 'get/get_representante.php', // Asegúrate de que esta ruta sea correcta
        method: 'POST',
        dataType: 'json',
        data: {
          tipo_cedula: tipo,
          cedula: cedula
        },
        success: function(data) {
          if (data && data.existe) {

            // Rellenar datos
            $('#nombre_rep').val(data.nombre);
            $('#apellido_rep').val(data.apellido);

            if (data.fecha_nacimiento) {
              $('#fechaN_rep').val(data.fecha_nacimiento);
              calcularEdadRep();
            }
            // Manejo del teléfono
            if (data.telefono_numero) {
              $('#telefono_rep').val(data.telefono_numero);
            }
            if (data.prefijo_id) {
              $('#prefijo_rep').val(data.prefijo_id);
            }

            if (data.genero) {
              $('#genero_rep').val(data.genero);
            }

            if (data.email) {
              $('#email_rep').val(data.email);
            }

            /*mostrarAvisoMenores('✅ <b>Representante Encontrado:</b> Los datos han sido cargados automáticamente.');*/
            alternarCamposRepresentante(true);

          } else {

            // NO EXISTE: Desbloquear para permitir escritura
            alternarCamposRepresentante(false);

            // Restaurar placeholder

            $('#nombre_rep').prop('enabled');
            $('#apellido_rep').prop('enabled');
            $('#fechaN_rep').prop('enabled');
            $('#edad_rep').prop('enabled');
            $('#telefono_rep').prop('enabled');
            $('#prefijo_rep').prop('enabled');
            $('#genero_rep').prop('enabled');
            $('#email_rep').prop('enabled');

          }
        },
        error: function(xhr, status, error) {
          console.error("Error al buscar representante:", error);
          // En caso de error, desbloqueamos para que el usuario pueda escribir manualmente
          alternarCamposRepresentante(false);
          $('#nombre_rep').attr('placeholder', 'Nombre Del Representante');
        }
      });
    }

    // Eventos de Validación del Paciente (ya usan la función corregida)
    cedulaInput.addEventListener('blur', validarCedulaFormatoMenores);
    tipoCedulaSelect.addEventListener('change', validarCedulaFormatoMenores);
    // Evento blur para la búsqueda del representante
    cedulaRepInput.addEventListener('blur', validarCedulaRepFormato);
    tipoCedulaRepSelect.addEventListener('change', validarCedulaRepFormato);


    // --- LÓGICA DE DEPENDENCIAS DE UBICACIÓN (Se mantiene sin cambios) ---

    document.getElementById('pais_nacimiento').addEventListener('change', function() {
      const paisId = this.value;
      const estadoSelect = document.getElementById('estado_nacimiento');
      estadoSelect.innerHTML = '<option value="">--- Seleccione Un Estado ---</option>';
      document.getElementById('municipio_nacimiento').innerHTML = '<option value="">--- Seleccione Un Municipio ---</option>';

      if (paisId) {
        fetch('get/get_estados.php?Id_Pais=' + paisId)
          .then(res => res.json())
          .then(data => {
            data.forEach(estado => {
              estadoSelect.innerHTML += `<option value='${estado.Id_Estado}'>${estado.nombre_estado}</option>`;
            });
          });
      }
    });

    document.getElementById('estado_nacimiento').addEventListener('change', function() {
      const estadoId = this.value;
      const municipioSelect = document.getElementById('municipio_nacimiento');
      municipioSelect.innerHTML = '<option value="">--- Seleccione Un Municipio ---</option>';

      if (estadoId) {
        fetch('get/get_municipios.php?Id_Estado=' + estadoId)
          .then(res => res.json())
          .then(data => {
            data.forEach(municipio => {
              municipioSelect.innerHTML += `<option value='${municipio.Id_Municipio}'>${municipio.nombre_municipio}</option>`;
            });
          });
      }
    });

    document.getElementById('estado').addEventListener('change', function() {
      const estadoId = this.value;
      const municipioSelect = document.getElementById('municipio');
      municipioSelect.innerHTML = '<option value="">--- Seleccione Un Municipio ---</option>';

      if (estadoId) {
        fetch('get/get_municipios.php?Id_Estado=' + estadoId)
          .then(res => res.json())
          .then(data => {
            data.forEach(municipio => {
              municipioSelect.innerHTML += `<option value='${municipio.Id_Municipio}'>${municipio.nombre_municipio}</option>`;
            });
          });
      }
    });

    document.getElementById('municipio').addEventListener('change', function() {
      const municipioId = this.value;
      const sectorSelect = document.getElementById('sector_menor');
      sectorSelect.innerHTML = '<option value="">--- Seleccione Un Sector ---</option>';

      if (municipioId) {
        fetch('get/get_sectores.php?Id_Municipio=' + municipioId)
          .then(res => res.json())
          .then(data => {
            data.forEach(sector => {
              sectorSelect.innerHTML += `<option value='${sector.Id_Sector}'>${sector.nombre_sector}</option>`;
            });
          });
      }
    });

    // --- LÓGICA Analfabeta y Validación de Misiones (Se mantiene sin cambios) ---

    const selectAnalfabeta = document.getElementById('analfabeta');
    const nivelInstruccion = document.getElementById('nivel_instruccion');
    const mision = document.getElementById('mision');
    const añosAprobados = document.getElementById('años_aprobados');
    const profesion = document.getElementById('profesion');
    const ocupacion = document.getElementById('ocupacion');
    const selectEtnia = document.getElementById('etnia');
    const tipoEtnia = document.getElementById('tipo_etnia');
    const selectDiscapacidad = document.getElementById('discapacidad');
    const tipoDiscapacidad = document.getElementById('tipo_discapacidad');

    // Mapa de años máximos por Misión (basado en estándares venezolanos)
    const limitesMision = {
      'robinson': 6, // Robinson I y II (Primaria)
      'ribas': 3, // Bachillerato Integral (aprox 2-3 años)
      'Sucre': 5 // Universitario (Trayectos)
    };

    // Función que ajusta la disponibilidad de campos de estudios/ocupación
    function toggleOptions() {
      const esAnalfabeta = selectAnalfabeta.value === 'Si';
      const noEsAnalfabeta = selectAnalfabeta.value === 'No';
      const siTieneEtnia = selectEtnia.value === 'Si';
      const noTieneEtnia = selectEtnia.value === 'No';
      const siTieneDiscapacidad = selectDiscapacidad.value === 'Si';
      const noTieneDiscapacidad = selectDiscapacidad.value === 'No';

      if (esAnalfabeta) {
        // SI es analfabeta:
        // Bloquear y limpiar: Ocupacion, Profesion, Nivel
        $('#ocupacion, #profesion, #nivel_instruccion').prop('disabled', true).val('');

        // Desbloquear: Mision, Años
        $('#mision, #años_aprobados').prop('disabled', false);

      } else if (noEsAnalfabeta) {
        // NO es analfabeta:
        // Desbloquear: Ocupacion, Profesion, Nivel
        $('#ocupacion, #profesion, #nivel_instruccion').prop('disabled', false);

        // Bloquear y limpiar: Mision, Años
        $('#mision, #años_aprobados').prop('disabled', true).val('');
        $('#años_help').text('');
      } else {
        // Estado inicial (nada seleccionado) o reset
        $('#ocupacion, #profesion, #nivel_instruccion, #mision, #años_aprobados').prop('disabled', false);
      }
      if (siTieneEtnia) {
        // SI es analfabeta:
        // Desbloquear y limpiar: Etnia
        $('#tipo_etnia').prop('disabled', false);
      } else if (noTieneEtnia) {
        // NO es analfabeta:
        // Bloquear: Etnia
        $('#tipo_etnia').prop('disabled', true);
      } else {
        // Estado inicial (nada seleccionado) o reset
        // Se dejan habilitados para la selección inicial
        $('#tipo_etnia').prop('disabled', false);
      }
      if (siTieneDiscapacidad) {
        // SI es analfabeta:
        // Desbloquear y limpiar: Discapacidad
        $('#tipo_discapacidad').prop('disabled', false);
      } else if (noTieneDiscapacidad) {
        // NO es analfabeta:
        // Bloquear: Discapacidad
        $('#tipo_discapacidad').prop('disabled', true);
      } else {
        // Estado inicial (nada seleccionado) o reset
        // Se dejan habilitados para la selección inicial
        $('#tipo_discapacidad').prop('disabled', false);
      }
    }

    // Validación y Configuración de Años según Misión
    $('#mision').on('change', function() {
      const seleccion = $(this).val();
      const maxAños = limitesMision[seleccion];

      if (maxAños) {
        $('#años_aprobados').attr('max', maxAños);
        $('#años_aprobados').attr('placeholder', 'Máx: ' + maxAños);
        $('#años_help').text('Min ' + seleccion.charAt(0).toUpperCase() + seleccion.slice(1) + ': ' + maxAños + ' años.');

        // Validar si el valor actual excede el nuevo máximo
        const valorActual = parseInt($('#años_aprobados').val());
        if (valorActual > maxAños) {
          $('#años_aprobados').val(maxAños);
        }
      } else {
        $('#años_aprobados').removeAttr('max');
        $('#años_aprobados').attr('placeholder', '');
        $('#años_help').text('');
      }
    });

    // Validación en tiempo real de años
    $('#años_aprobados').on('input', function() {
      const max = parseInt($(this).attr('max'));
      const val = parseInt($(this).val());

      if (!isNaN(max) && val > max) {
        $(this).addClass('input-error');
        // Opcional: Auto-corregir o solo avisar
        $('#años_help').text('⚠️ Error: No puede exceder ' + max + ' años para esta misión.').css('color', 'crimson');
      } else {
        $(this).removeClass('input-error');
        // Restaurar texto de ayuda original si existe misión seleccionada
        const misionSel = $('#mision').val();
        if (limitesMision[misionSel]) {
          $('#años_help').text('Duración máxima estimada para Misión ' + misionSel.charAt(0).toUpperCase() + misionSel.slice(1) + ': ' + limitesMision[misionSel] + ' años.').css('color', '#777');
        }
      }
    });

    selectAnalfabeta.addEventListener('change', toggleOptions);
    selectEtnia.addEventListener('change', toggleOptions);
    selectDiscapacidad.addEventListener('change', toggleOptions);
    toggleOptions(); // Inicializa la función al cargar


    // =====================================================================
    // LÓGICA PARA PATOLOGÍAS Y ALERGIAS (Se mantiene sin cambios)
    // =====================================================================

    // --- Patologías ---
    document.getElementById('modal_patologia_menores').addEventListener('change', function() {
      const patologiaId = this.value;
      if (!patologiaId || patologiaId === 'disabled') {
        document.getElementById('modal_codigo_cie_menores').value = 'No disponible';
        return;
      }
      fetch('get/get_cie.php?Id_patologia=' + patologiaId)
        .then(res => res.json())
        .then(data => {
          document.getElementById('modal_codigo_cie_menores').value = data.codigo_cie || 'No disponible';
        })
        .catch(error => {
          console.error('Error al obtener código CIE:', error);
          document.getElementById('modal_codigo_cie_menores').value = 'Error';
        });
    });

    document.getElementById('agregarPatologia_menores').addEventListener('click', function() {
      const select = document.getElementById('modal_patologia_menores');
      const id = select.value;
      const nombre = select.options[select.selectedIndex].getAttribute('data-nombre');
      const codigo_cie = document.getElementById('modal_codigo_cie_menores').value;

      if (!id || id === 'disabled') {
        mostrarAvisoMenores('⚠️ Seleccione una Patología.');
        return;
      }
      if (patologiasSeleccionadas.some(p => p.id == id)) {
        mostrarAvisoMenores('⚠️ Esta patología ya está añadida en la lista temporal.');
        return;
      }

      patologiasSeleccionadas.push({
        id: parseInt(id),
        nombre,
        codigo_cie
      });
      renderPatologiasModalMenores();
      document.getElementById('modal_patologia_menores').value = 'disabled';
      document.getElementById('modal_codigo_cie_menores').value = '';
    });

    document.getElementById('guardarPatologias_menores').addEventListener('click', function() {
      actualizarDisplayPatologiasMenores();
      $('#modalPatologiasMenores').modal('hide');
    });

    function renderPatologiasModalMenores() {
      const lista = document.getElementById('lista_patologias_seleccionadas_menores');
      lista.innerHTML = '';
      if (patologiasSeleccionadas.length === 0) {
        lista.innerHTML = 'Ninguna patología en la lista temporal.';
        return;
      }
      patologiasSeleccionadas.forEach(p => {
        lista.innerHTML += `
            <span class="label label-primary" style="margin-right: 5px; margin-bottom: 5px; display: inline-block; font-size: 14px; padding: 6px 10px 6px 10px;">
                ${p.nombre} (${p.codigo_cie})
                <span class="quitar-patologia" data-id="${p.id}" 
                   style="cursor: pointer; margin-left: 8px; font-weight: bold; color: white;" 
                   title="Eliminar">&times;</span>
            </span>`;
      });
    }
    // --- AJAX PARA GUARDAR NUEVA PATOLOGÍA (Se mantiene la simulación) ---
    $('#btnGuardarNuevaPatologiaAjaxMenores').click(function() {
      const nombre = $('#nuevo_nombre_patologia_menores').val().trim();
      const cie = $('#nuevo_codigo_cie_menores').val().trim();
      const contagiosa = $('#nueva_es_contagiosa').val();

      if (nombre === '' || cie === '' || contagiosa === '') {
        mostrarAvisoMenores('Todos los campos son obligatorios.');
        return;
      }

      $.ajax({
        url: 'get/verificar_existencia_patologia.php',
        data: {
          nombre: nombre
        },
        success: function(res) {
          if (res.existe) mostrarAvisoMenores('Ya existe');
          else guardar();
        }
      });

      // Por ahora cerramos el modal (Simulación)
      $('#modalNuevaPatologiaMenores').modal('hide');
      mostrarAvisoMenores('✅ Patología guardada correctamente.');
    });

    // --- AJAX PARA GUARDAR NUEVA ALERGIA (Se mantiene la simulación) ---
    $('#btnGuardarNuevaAlergiaAjaxMenores').click(function() {
      const nombre = $('#nuevo_nombre_alergia_menores').val().trim();

      if (nombre === '') {
        mostrarAvisoMenores('El nombre es obligatorio.');
        return;
      }

      // Por ahora cerramos el modal (Simulación)
      $('#modalNuevaAlergiaMenores').modal('hide');
      mostrarAvisoMenores('✅ Alergia guardada correctamente.');
    });

    function actualizarDisplayPatologiasMenores() {
      const display = document.getElementById('patologias_agregadas');
      const inputHidden = document.getElementById('patologias_ids');
      const nombres = patologiasSeleccionadas.map(p => p.nombre);
      const ids = patologiasSeleccionadas.map(p => p.id);
      const LIMITE_DISPLAY = 2;

      inputHidden.value = ids.join(',');

      if (nombres.length > 0) {
        let htmlContent = '';

        for (let i = 0; i < Math.min(nombres.length, LIMITE_DISPLAY); i++) {
          htmlContent += `<span class="label label-primary" style="margin-right: 5px; margin-bottom: 5px; display: inline-block; padding: 6px;">${nombres[i]}</span>`;
        }

        if (nombres.length > LIMITE_DISPLAY) {
          let restantes = nombres.length - LIMITE_DISPLAY;
          htmlContent += `<span class="text-muted" style="margin-left:5px; font-weight:bold;">... y ${restantes} más.</span>`;
        }

        display.innerHTML = htmlContent;

      } else {
        display.innerHTML = '<span class="text-muted">Ninguna patología seleccionada.</span>';
      }
    }

    $('#modalPatologiasMenores').on('click', '.quitar-patologia', function() {
      const idQuitar = $(this).data('id');
      patologiasSeleccionadas = patologiasSeleccionadas.filter(p => p.id != idQuitar);
      $(this).closest('.label').remove();
    });

    // --- Alergias ---

    document.getElementById('agregarAlergiaMenores').addEventListener('click', function() {
      const select = document.getElementById('modal_alergia_menores');
      const id = select.value;
      const nombre = select.options[select.selectedIndex].getAttribute('data-nombre');

      if (!id || id === 'disabled') {
        mostrarAvisoMenores('⚠️ Seleccione una Alergia.');
        return;
      }
      if (alergiasSeleccionadas.some(a => a.id == id)) {
        mostrarAvisoMenores('⚠️ Esta alergia ya está añadida en la lista temporal.');
        return;
      }

      alergiasSeleccionadas.push({
        id: parseInt(id),
        nombre
      });
      renderAlergiasModalMenores();
      document.getElementById('modal_alergia_menores').value = 'disabled';
    });

    document.getElementById('guardarAlergiasMenores').addEventListener('click', function() {
      actualizarDisplayAlergiasMenores();
      $('#modalAlergiasMenores').modal('hide');
    });

    function renderAlergiasModalMenores() {
      const lista = document.getElementById('lista_alergias_seleccionadas_menores');
      lista.innerHTML = '';
      if (alergiasSeleccionadas.length === 0) {
        lista.innerHTML = 'Ninguna alergia en la lista temporal.';
        return;
      }
      alergiasSeleccionadas.forEach(a => {
        lista.innerHTML += `
            <span class="label label-info" style="margin-right: 5px; margin-bottom: 5px; display: inline-block; font-size: 14px; padding: 6px 10px 6px 10px;">
                ${a.nombre} 
                <span class="quitar-alergia" data-id="${a.id}" 
                   style="cursor: pointer; margin-left: 8px; font-weight: bold; color: white;" 
                   title="Eliminar">&times;</span>
            </span>`;
      });
    }

    function actualizarDisplayAlergiasMenores() {
      const display = document.getElementById('alergias_agregadas');
      const inputHidden = document.getElementById('alergias_ids');
      const nombres = alergiasSeleccionadas.map(a => a.nombre);
      const ids = alergiasSeleccionadas.map(a => a.id);
      const LIMITE_DISPLAY = 2;

      inputHidden.value = ids.join(',');

      if (nombres.length > 0) {
        let htmlContent = '';

        for (let i = 0; i < Math.min(nombres.length, LIMITE_DISPLAY); i++) {
          htmlContent += `<span class="label label-info" style="margin-right: 5px; margin-bottom: 5px; display: inline-block; padding: 6px;">${nombres[i]}</span>`;
        }

        if (nombres.length > LIMITE_DISPLAY) {
          let restantes = nombres.length - LIMITE_DISPLAY;
          htmlContent += `<span class="text-muted" style="margin-left:5px; font-weight:bold;">... y ${restantes} más.</span>`;
        }

        display.innerHTML = htmlContent;

      } else {
        display.innerHTML = '<span class="text-muted">Ninguna alergia seleccionada.</span>';
      }
    }

    $('#modalAlergiasMenores').on('click', '.quitar-alergia', function() {
      const idQuitar = $(this).data('id');
      alergiasSeleccionadas = alergiasSeleccionadas.filter(a => a.id != idQuitar);
      $(this).closest('.label').remove();
    });


    // =====================================================================
    // VALIDACIÓN PRINCIPAL DE CADA PESTAÑA (ASÍNCRONA)
    // =====================================================================

    async function validarPestanaMenores(tabSelector) {
      let esValido = true;
      let errores = [];
      limpiarErroresPestanaMenores(tabSelector);

      // 1. Validación de campos obligatorios (*)
      $(tabSelector).find('[required]').each(function() {
        var $input = $(this);
        var valor = $input.val();

        // Criterios de vacío: null, string vacío, o valor de placeholder/default
        if ($input.is(':visible') && !$input.prop('disabled') && (valor === null || valor.trim() === "" || valor.includes('--- Seleccione'))) {
          errores.push("El campo " + $input.prev('p').text().replace('(*):', '').replace('(*)', '') + " es obligatorio.");
          $input.addClass('input-error');
          esValido = false;
        }
      });

      // 2. Validaciones Específicas

      if (tabSelector === '#info_menor') {
        const cedulaEsValida = await validarCedulaFormatoMenores();
        if (!cedulaEsValida) {
          esValido = false;
        }

        const edad = parseInt($('#edad_menor').val());
        // El paciente debe ser estrictamente menor de 18 años (edad < 18)
        if (isNaN(edad) || edad < 0 || edad >= 18) {
          errores.push("El paciente debe ser menor de 18 años (Edad " + edad + ").");
          $('#fecha_nacimiento_menor').addClass('input-error');
          esValido = false;
        }
      }

      if (tabSelector === '#representante') {
        const cedulaRepEsValida = await validarCedulaRepFormato();
        if (!cedulaRepEsValida) {
          esValido = false;
        }

        const email = $('#email_rep').val().trim();
        if (email !== "" && (email.indexOf('@') === -1 || email.indexOf('.') === -1)) {
          errores.push("El campo Email debe tener un formato válido (ej: nombre@dominio.com).");
          $('#email_rep').addClass('input-error');
          esValido = false;
        }

        // LÓGICA: Validar que el representante sea mayor de 18 años
        const edadRep = calcularEdadRep();

        if (isNaN(edadRep) || edadRep < 18) {
          errores.push("El Representante debe ser mayor de 18 años (Edad " + edadRep + ").");
          $('#fechaN_rep').addClass('input-error');
          esValido = false;
        }
      }

      if (tabSelector === '#direccion_menor') {
        if ($('#municipio').val().trim() === "") {
          $('#municipio').addClass('input-error');
          esValido = false;
        }
      }

      if (tabSelector === '#ocupacion_estudios_menor') {
        // Validar años aprobados si está habilitado
        if (!$('#años_aprobados').prop('disabled')) {
          const años = parseInt($('#años_aprobados').val());
          const max = parseInt($('#años_aprobados').attr('max'));
          if (!isNaN(max) && años > max) {
            errores.push("Los Años Aprobados exceden el máximo permitido para esta Misión (" + max + " años).");
            $('#años_aprobados').addClass('input-error');
            esValido = false;
          }
        }
      }

      if (tabSelector === '#salud_otros_menor') {
        // Hacer la selección de patología obligatoria para un menor (mayor estrictez)
        if (patologiasSeleccionadas.length === 0) {
          errores.push("Debe seleccionar al menos una Patología.");
          $('#patologias_agregadas').addClass('input-error');
          esValido = false;
        }
      }

      if (!esValido && errores.length > 0) {
        mostrarAvisoMenores('⚠️ Errores de Formulario: <ul><li>' + errores.join('</li><li>') + '</li></ul>');
      }
      return esValido;
    }


    // =====================================================================
    // MANEJO DE PESTAÑAS (TABS) (ADAPTADO)
    // =====================================================================

    // 1. MANEJO DE BOTONES SIGUIENTE
    $('.next-tab').on('click', async function() {
      const $btn = $(this);
      const tabActualSelector = $btn.data('tab-actual');
      const tabSiguienteName = $btn.data('tab-siguiente');
      const nextTabLink = $(`.nav-tabs li[data-tab-name="${tabSiguienteName}"] a`);

      $btn.prop('disabled', true).text('Validando...');

      const esValido = await validarPestanaMenores(tabActualSelector);

      $btn.prop('disabled', false).text(tabSiguienteName === 'confirmar' ? 'Guardar' : 'Siguiente');

      if (esValido) {
        if (tabSiguienteName === 'confirmar') {
          $('#modalGuardarMenores').modal('show');
        } else {
          const $siguienteTabLi = nextTabLink.parent();
          // Asegura que la pestaña actual se desactive
          $(`.nav-tabs li[data-tab-name]:has(a[href="${tabActualSelector}"]`).removeClass('active');
          $siguienteTabLi.removeClass('disabled-tab').addClass('active');
          nextTabLink.tab('show');
        }
      }
    });

    // 2. MANEJO DE BOTONES REGRESAR/ATRAS
    $('.prev-tab').on('click', function(e) {
      e.preventDefault();

      const tabAnteriorSelector = $(this).data('tab-anterior');
      // Solo el botón "Regresar" de la primera pestaña tiene el modal
      if (tabAnteriorSelector === undefined) {
        // Se asume que este es el botón "Regresar" del primer tab
        $('#modalConfirmarRegreso').modal('show');
        return;
      }

      const tabAnteriorName = tabAnteriorSelector.replace('#', '');
      const $anteriorTabLi = $(`.nav-tabs li[data-tab-name="${tabAnteriorName}"]`);

      // Desactivar la pestaña actual y activar la anterior
      $(`.nav-tabs li.active`).removeClass('active');
      $anteriorTabLi.addClass('active');
      $(`.nav-tabs a[href="${tabAnteriorSelector}"]`).tab('show');
    });

    // BLOQUEO DE CLIC EN PESTAÑAS: Se mantiene la lógica de bloqueo de clic
    $('.nav-tabs a').on('click', function(e) {
      if ($(this).parent().hasClass('disabled-tab')) {
        return false;
      }
    });

    // =====================================================================
    // FIX GENERAL DE MODALES Y ANIMACIÓN (Se mantiene sin cambios)
    // =====================================================================

    function closeCustomModalMenores($modal) {
      if ($modal.hasClass('out')) {
        return;
      }
      $modal.removeClass('in').addClass('out');
      setTimeout(function() {
        $modal.modal('hide');
      }, 400);
    }

    $('.modal').on('show.bs.modal', function() {
      $(this).removeClass('out').addClass('in');
    });

    $('.modal').on('hide.bs.modal', function(e) {
      if ($(this).hasClass('in') && !$(this).hasClass('out')) {
        e.preventDefault();
        e.stopPropagation();
        closeCustomModalMenores($(this));
      }
    });

    $('.modal .close, .modal .btn-default[data-dismiss="modal"], .modal .btn-secondary[data-dismiss="modal"]').on('click', function(e) {
      const $modal = $(this).closest('.modal');
      if ($modal.length && $modal.hasClass('in')) {
        closeCustomModalMenores($modal);
      }
    });

    // Cierre al hacer clic en el backdrop (parte oscura)
    $('.modal').on('click', function(e) {
      if ($(e.target).hasClass('modal') && $(this).hasClass('in')) {
        closeCustomModalMenores($(this));
      }
    });

    $('.modal').on('hidden.bs.modal', function() {
      $(this).removeClass('out');
      if (!$('.modal.in').length) {
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
      } else {
        $('body').addClass('modal-open');
      }
    });

    $('#confirmandoGuardadoFinal').off('click').on('click', function(e) {
    var fechaRep = $('#fechaN_rep').val();
    
    if (!fechaRep) {
        alert("La fecha de nacimiento del representante es obligatoria.");
        $('.nav-tabs a[href="#representante"]').tab('show'); // Ajusta el href según tu id de pestaña
        return;
    }

    document.getElementById('formularioPacienteMenor').submit();
    });

    // --- Aplicar validaciones a campos de solo texto ---
    const campos = [
      document.getElementById("solo_texto"),
      document.getElementById("solo_texto1"),
      document.getElementById("nombre_rep"),
      document.getElementById("apellido_rep")
    ];
    campos.forEach(campo => {
      if (campo) {
        campo.addEventListener("keydown", bloquearNumerosMenores);
        campo.addEventListener("input", limpiarNumerosMenores);
      }
    });
  </script>
  <script>
    function soloNumerosSinEMenores(campo, maxDigitos) {
      campo.addEventListener("keydown", function(e) {
        const teclasPermitidas = ["Backspace", "Tab", "ArrowLeft", "ArrowRight", "Delete"];

        if (teclasPermitidas.includes(e.key)) return;
        if (e.key.toLowerCase() === "e") {
          e.preventDefault();
          return;
        }
        if (!/^[0-9]$/.test(e.key)) {
          e.preventDefault();
          return;
        }
        if (campo.value.length >= maxDigitos) {
          e.preventDefault();
        }
      });

      campo.addEventListener("input", function() {
        campo.value = campo.value.replace(/[^0-9]/g, "").slice(0, maxDigitos);
      });
    }

    soloNumerosSinEMenores(document.getElementById("tiempo_residencia"));
  </script>
  </body>

  </html>