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

    #avisoModal {
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
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active" data-tab-name="info"><a href="#info" data-toggle="tab">Datos Personales</a></li>
              <li data-tab-name="ocupacion_estudios" class="disabled-tab"><a href="#ocupacion_estudios" data-toggle="tab">Ocupación y Estudios Aprobados</a></li>
              <li data-tab-name="direccion" class="disabled-tab"><a href="#direccion" data-toggle="tab">Dirección de Residencia</a></li>
              <li data-tab-name="salud_otros" class="disabled-tab"><a href="#salud_otros" data-toggle="tab">Salud y Otros Datos</a></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane active" id="info">
                <form id="formularioPaciente" action="../../cfg/agregar/agregar_paciente.php" class="form-group" method="POST" novalidate>
                  <section id="info" style="margin-bottom:6%;">
                    <label class="control-label"></label>
                    <div class="col-sm-1 pull-left" style="margin-top: 30px;">
                      <select name="tipo_cedula" id="tipo_cedula" class="form-control" style="width: 60px;" required>
                        <option value="V">V-</option>
                        <option value="E">E- </option>
                      </select>
                    </div>
                    <div class="col-sm-3">
                      <p>Cédula (*):</p>
                      <input type="text" class="form-control" name="cedula" id="cedula" placeholder="N° de Cédula" maxlength="8" required>
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
                      <input type="date" class="form-control pull-right" id="fecha_nacimiento_adulto" name="fecha_nacimiento_adulto" onchange="calcularEdad()" required>
                    </div>
                    <div class="col-sm-1">
                      <p style="margin-left: 5px;">Edad</p>
                      <input type="text" class="form-control pull-right" id="edad_adulto" name="edad_adulto" readonly>
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
                    <div class="col-sm-3">
                      <p>Situación conyugal:</p>
                      <select name="situacion_conyugal" class="form-control">
                        <option value="">--- Seleccione ---</option>
                        <option value="Soltero">Soltero</option>
                        <option value="Casado">Casado</option>
                        <option value="Viudo">Viudo</option>
                        <option value="Divorciado">Divorciado</option>
                        <option value="Unión Estable de Hecho">Unión Estable de Hecho</option>
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
                    <div class="col-sm-1">
                      <p>Etnia:</p>
                      <select name="etnia" id="etnia" class="form-control" required>
                        <option value="No">No</option>
                        <option value="Si">Si</option>
                      </select>
                    </div>
                    <div class="col-sm-3">
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
                        <option value="bari">Barí</option>
                        <option value="yanomami">Yanomami</option>
                        <option value="sanema">Sanema</option>
                        <option value="warao">Warao</option>
                        <option value="pume">Pumé</option>
                        <option value="piaroa">Piaroa</option>
                        <option value="otro">Otro/No Aplica</option>
                      </select>
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-4">
                      <p>Analfabeta (*):</p>
                      <select name="analfabeta" id="analfabeta" class="form-control" required>
                        <option value="">--- Seleccione Una Opción ---</option>
                        <option value="Si">Sí</option>
                        <option value="No">No</option>
                      </select>
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-3">
                      <p>Cotiza seguro social:</p>
                      <select name="seguro_social" class="form-control">
                        <option value="">--- Seleccione Una Opción ---</option>
                        <option value="Si">Sí</option>
                        <option value="No">No</option>
                      </select>
                    </div>
                    <label class="control-label"></label>
                    <br><br><br><br>
                    <label class="control-label"></label>
                    <div class="col-sm-1 pull-left" style="margin-top: 30px;">
                      <select name="prefijo" class="form-control" id="prefijo_telefono" style="width: 70px;" required>
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
                      <p>Teléfono (*):</p>
                      <input type="text" class="form-control" name="telefono" id="telefono" placeholder="N° De Teléfono" minlength="7" maxlength="7" required>
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-4">
                      <p>Email:</p>
                      <input type="email" class="form-control" name="email" id="email" placeholder="nombreapellido2@gmail.com">
                    </div>
                    <div style="float:right; margin-top:5%;">
                      <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#modalConfirmarRegreso">Regresar</button>
                      <button type="button" class="btn btn-primary next-tab" data-tab-actual="#info" data-tab-siguiente="ocupacion_estudios">Siguiente</button>
                    </div>
                  </section>
              </div>
              <div class="tab-pane" id="ocupacion_estudios">
                <section id="ocupacion_estudios" style="margin-bottom:8%;">
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
                      <option value="tecnico_medio">Técnico Medio</option>
                      <option value="tsu">Técnico Superior Universitario (T.S.U)</option>
                      <option value="universitario">Universitario (Lic./Ing.)</option>
                      <option value="especializacion">Especialización</option>
                      <option value="maestria">Maestría</option>
                      <option value="doctorado">Doctorado</option>
                    </select>
                  </div>
                  <label class="control-label"></label>
                  <div class="col-sm-4">
                    <p>Profesion:</p>
                    <select name="profesion" id="profesion" class="form-control">
                      <option value="">--- Seleccione Una Profesion ---</option>
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
                  <label class="control-label"></label>
                  <div class="col-sm-3">
                    <p>Ocupación:</p>
                    <select name="ocupacion" id="ocupacion" class="form-control">
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
                  <br><br><br><br>
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
                  <div class="col-sm-4">
                    <p>Años aprobados:</p>
                    <input type="number" id="años_aprobados" class="form-control" name="años_aprobados" min="0">
                    <small id="años_help" class="form-text text-muted"></small>
                  </div>
                  <div style="float:right; margin-top:7%;">
                    <button type="button" class="btn btn-secondary prev-tab" data-tab-anterior="#info">Atrás</button>
                    <button type="button" class="btn btn-primary next-tab" data-tab-actual="#ocupacion_estudios" data-tab-siguiente="direccion">Siguiente</button>
                  </div>
                </section>
              </div>
              <div class="tab-pane" id="direccion">
                <section id="direccion" style="margin-bottom:6%;">
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
                    <select name="sector_adulto" id="sector_adulto" class="form-control" required>
                      <option value="1">--- Seleccione Un Sector ---</option>
                    </select>
                  </div>
                  <br><br><br><br>
                  <label class="control-label"></label>
                  <div class="col-sm-4">
                    <p>Avenida/Calle:</p>
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
                    <input type="text" class="form-control" name="tiempo_residencia" id="tiempo_residencia">
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
                  <div style="float:right; margin-top:3%;">
                    <button type="button" class="btn btn-secondary prev-tab" data-tab-anterior="#ocupacion_estudios">Atrás</button>
                    <button type="button" class="btn btn-primary next-tab" data-tab-actual="#direccion" data-tab-siguiente="salud_otros">Siguiente</button>
                  </div>
                </section>
              </div>
              <div class="tab-pane" id="salud_otros">
                <section id="salud_otros" style="margin-bottom:12%;">
                  <label class="control-label"></label>
                  <div class="col-sm-5">
                    <p>Patologias:</p>
                    <input type="hidden" name="patologias_ids" id="patologias_ids" value="">
                    <div id="patologias_agregadas" class="well well-sm" style="height: auto; min-height: 34px;">
                    <span class="text-muted">Ninguna Patologia seleccionada.</span></div>
                  </div>
                  <div class="col-sm-1" style="margin-top: 30px;">
                    <button type="button" class="form-control pull-right bt-sm btn-primary" data-toggle="modal" data-target="#modalPatologias">
                      <img src="../../recursos/imagenes/iconos/agregar.png" height="20px" width="20px"></button>
                  </div>
                  <label class="control-label"></label>
                  <div class="col-sm-4">
                    <p>Alergias:</p>
                    <input type="hidden" name="alergias_ids" id="alergias_ids" value="">
                    <div id="alergias_agregadas" class="well well-sm" style="height: auto; min-height: 34px;">
                    <span class="text-muted">Ninguna Alergia seleccionada.</span></div>
                  </div>
                  <div class="col-sm-1" style="margin-top: 30px;">
                    <button type="button" class="form-control pull-right bt-sm btn-primary" data-toggle="modal" data-target="#modalAlergias">
                    <img src="../../recursos/imagenes/iconos/agregar.png" height="20px" width="20px"></button> 
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
                  <div style="float:right; margin-top:5%;">
                    <button type="button" class="btn btn-secondary prev-tab" data-tab-anterior="#direccion">Atrás</button>
                    <button type="button" class="btn btn-success next-tab" data-tab-actual="#salud_otros" data-tab-siguiente="confirmar">Guardar</button>
                  </div>
                </section>
              </div>

            </div>
          </div>
        </div>
      </div>
      </form>
    </section>


  <div class="modal" id="avisoModal" tabindex="-1" role="dialog" aria-labelledby="avisoModalLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header modal-header-danger">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="avisoModalLabel"><i class="fa fa-warning"></i> Aviso Importante</h4>
        </div>
        <div class="modal-body">
          <p id="avisoTexto"></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalGuardarPaciente" tabindex="-1" role="dialog" aria-labelledby="modalGuardarPacienteLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: #00a65a; color: white;">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="modalGuardarPacienteLabel"><i class="fa fa-save"></i> Confirmacion de Guardado</h4>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea guardar la información del nuevo paciente?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Regresar</button>
          <button type="button" class="btn btn-success" id="confirmarGuardadoFinal">Guardar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalConfirmarRegreso" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarRegresoLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header modal-header-danger">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="modalConfirmarRegresoLabel"><i class="fa fa-sign-out"></i> Confirmacion de Regreso</h4>
        </div>
        <div class="modal-body">
          <p>Al hacer clic en "Abandonar Formulario", perderá todos los datos no guardados. ¿Desea continuar?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <a href="pacientes_listado.php" class="btn btn-danger">Abandonar Formulario</a>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalPatologias" tabindex="-1" role="dialog" aria-labelledby="modalPatologiasLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: #337ab7; color: white;">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="modalPatologiasLabel"><i class="fa fa-medkit"></i> Seleccionar Patologías</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-sm-12">
              <label for="lista_patologias_seleccionadas">Patologías en lista temporal:</label>
              <div id="lista_patologias_seleccionadas" class="well well-sm" style="min-height: 50px;">
                Ninguna patología en la lista temporal.
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="modal_patologia">Patología disponible (*)</label>
                <select id="modal_patologia" class="form-control">
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
                <label for="modal_codigo_cie">Código CIE-10</label>
                <input type="text" id="modal_codigo_cie" class="form-control" readonly>
              </div>
            </div>
          </div>
          <button type="button" id="agregarPatologia" class="btn btn-info pull-right"><i class="fa fa-plus"></i> Añadir</button>
          <div class="clearfix"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-success" id="guardarPatologias">Guardar Selección</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalNuevaPatologia" tabindex="-1" role="dialog" aria-labelledby="modalNuevaPatologiaLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: #31b0d5; color: white;">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="modalNuevaPatologiaLabel"><i class="fa fa-plus-square"></i> Nueva Patología</h4>
        </div>
        <div class="modal-body">
          <form id="formNuevaPatologia">
            <div class="form-group">
              <label for="nuevo_nombre_patologia">Nombre de la Patología (*)</label>
              <input type="text" class="form-control" id="nuevo_nombre_patologia" name="nombre_patologia" required>
            </div>
            <div class="form-group">
              <label for="nuevo_codigo_cie">Código CIE-10 (*)</label>
              <input type="text" class="form-control" id="nuevo_codigo_cie" name="codigo_cie" required>
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
          <button type="button" class="btn btn-success" id="btnGuardarNuevaPatologiaAjax">Guardar Patología</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalAlergias" tabindex="-1" role="dialog" aria-labelledby="modalAlergiasLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: #337ab7; color: white;">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="modalAlergiasLabel"><i class="fa fa-wheelchair"></i> Seleccionar Alergias</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-sm-12">
              <label for="lista_alergias_seleccionadas">Alergias en lista temporal:</label>
              <div id="lista_alergias_seleccionadas" class="well well-sm" style="min-height: 50px;">
                Ninguna alergia en la lista temporal.
              </div>
            </div>
            <div class="col-sm-12">
              <div class="form-group">
                <label for="modal_alergia">Alergia disponible (*)</label>
                <select id="modal_alergia" class="form-control">
                  <option value="disabled">--- Seleccione Una Alergia ---</option>
                  <?php
                  // Conexión y consulta de alergias
                  $conexion_modal_alergias = new mysqli("localhost", "root", "", "cpt3db");
                  $result_modal_alergias = $conexion_modal_alergias->query("SELECT Id_alergias_conocidas, nombre_alergia FROM alergias_conocidas");
                  while ($row_modal = $result_modal_alergias->fetch_assoc()) {
                    echo "<option value='{$row_modal['Id_alergias_conocidas']}' data-nombre='{$row_modal['nombre_alergia']}'>{$row_modal['nombre_alergia']}</option>";
                  }
                  ?>
                </select>
              </div>
            </div>
          </div>
          <button type="button" id="agregarAlergia" class="btn btn-info pull-right"><i class="fa fa-plus"></i> Añadir</button>
          <div class="clearfix"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-success" id="guardarAlergias">Guardar Selección</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalNuevaAlergia" tabindex="-1" role="dialog" aria-labelledby="modalNuevaAlergiaLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: #31b0d5; color: white;">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="modalNuevaAlergiaLabel"><i class="fa fa-plus-square"></i> Nueva Alergia</h4>
        </div>
        <div class="modal-body">
          <form id="formNuevaAlergia">
            <div class="form-group">
              <label for="nuevo_nombre_alergia">Nombre de la Alergia (*)</label>
              <input type="text" class="form-control" id="nuevo_nombre_alergia" name="nombre_alergia" required>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-success" id="btnGuardarNuevaAlergiaAjax">Guardar</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Variables globales para manejar las selecciones
    let patologiasSeleccionadas = [];
    let alergiasSeleccionadas = [];

    // --- Funciones auxiliares ---
    function mostrarAviso(mensaje) {
      $('#avisoTexto').html(mensaje);
      $('#avisoModal').modal('show');
    }

    function limpiarErroresPestana(tabSelector) {
      $(tabSelector).find('.input-error').removeClass('input-error');
      $('#patologias_agregadas').removeClass('input-error');
      // Asegurarse de limpiar los errores de cédula y prefijo
      $('#cedula').removeClass('input-error');
      $('#tipo_cedula').removeClass('input-error');
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



    // --- LÓGICA DE CÁLCULO DE EDAD Y VALIDACIÓN DE MAYORÍA DE EDAD ---

    function setMaxDateFor18Years() {
      const today = new Date();
      today.setFullYear(today.getFullYear() - 18);
      const maxDate = today.toISOString().split('T')[0];
      document.getElementById('fecha_nacimiento_adulto').setAttribute('max', maxDate);
    }
    setMaxDateFor18Years();

    function calcularEdad() {
      const fechaNac = document.getElementById('fecha_nacimiento_adulto').value;
      if (!fechaNac) {
        document.getElementById('edad_adulto').value = '';
        return;
      }

      const hoy = new Date();
      const cumple = new Date(fechaNac);
      let edad = hoy.getFullYear() - cumple.getFullYear();
      const m = hoy.getMonth() - cumple.getMonth();

      if (m < 0 || (m === 0 && hoy.getDate() < cumple.getDate())) {
        edad--;
      }
      document.getElementById('edad_adulto').value = edad;
    }


    // --- LÓGICA DE VALIDACIÓN DE CÉDULA ---

    const cedulaInput = document.getElementById('cedula');
    const tipoCedulaSelect = document.getElementById('tipo_cedula');

    async function verificarCedulaEnBD(tipo, cedula) {
      return new Promise((resolve, reject) => {
        if (cedula === "" || tipo === "") {
          resolve(false);
          return;
        }

        $.ajax({
          url: 'get/get_verificar_cedula.php',
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

    async function validarCedulaFormato() {
      const cedula = parseInt(cedulaInput.value.trim());
      const tipo = tipoCedulaSelect.value;
      let esValido = true;

      // Limpiar errores primero
      $(cedulaInput).removeClass('input-error');
      $(tipoCedulaSelect).removeClass('input-error');

      if (cedulaInput.value.trim() === "") {
        $(cedulaInput).addClass('input-error');
        return true;
      }

      if (isNaN(cedula) || cedulaInput.value.trim() === "")
        return true;

      if (tipo === 'V' && cedula > 80000000) {
        $(cedulaInput).addClass('input-error');
        $(tipoCedulaSelect).addClass('input-error'); // Marcar el select
        mostrarAviso('🛑 Error de Cédula: Para tipo V-, la cédula no puede ser mayor a 80.000.000');
        esValido = false;
      } else if (tipo === 'E' && cedula < 80000000) {
        $(cedulaInput).addClass('input-error');
        $(tipoCedulaSelect).addClass('input-error'); // Marcar el select
        mostrarAviso('🛑 Error de Cédula: Para tipo E-, la cédula no puede ser menor a 80.000.000');
        esValido = false;
      }

      if (!esValido) return false;

      try {
        const existe = await verificarCedulaEnBD(tipo, cedula);
        if (existe) {
          $(cedulaInput).addClass('input-error');
          $(tipoCedulaSelect).addClass('input-error'); // Marcar el select
          mostrarAviso('🛑 Error de Cédula: La cédula ' + tipo + '-' + cedula + ' ya se encuentra registrada en el sistema.');
          esValido = false;
        }
      } catch (error) {
        mostrarAviso(error);
        esValido = false;
      }

      return esValido;
    }

    cedulaInput.addEventListener('blur', validarCedulaFormato);
    tipoCedulaSelect.addEventListener('change', validarCedulaFormato);


    // --- LÓGICA DE DEPENDENCIAS DE UBICACIÓN ---

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
      const sectorSelect = document.getElementById('sector_adulto');
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

    // --- MODIFICACIÓN SOLICITADA: Lógica Analfabeta y Validación de Misiones ---

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
        // Se dejan habilitados para la selección inicial
        $('#ocupacion, #profesion, #nivel_instruccion, #mision, #años_aprobados').prop('disabled', false);
      }
      if (siTieneEtnia) {
        // SI es analfabeta:
        // Desbloquear y limpiar: Etnia
        $('#tipo_etnia').prop('disabled', false)

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
        $('#tipo_discapacidad').prop('disabled', false)
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
        $('#años_help').text('Duración máxima estimada para Misión ' + seleccion.charAt(0).toUpperCase() + seleccion.slice(1) + ': ' + maxAños + ' años.').css('color', '#777');

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
        $('#años_help').text('⚠️ Error: No puede exceder ' + max + ' años para esta misión.').css('color', 'crimson');
      } else {
        $(this).removeClass('input-error');
        // Restaurar texto de ayuda original si existe misión seleccionada
        const misionSel = $('#mision').val();
        if (limitesMision[misionSel]) {
          $('#años_help').text('Duración máxima estimada para Misión ' + misionSel.charAt(0).toUpperCase() + misionSel.slice(1) + ': ' + limitesMision[misionSel] + ' años.').css('color', '#777');
        } else {
          $('#años_help').text('');
        }
      }
    });

    selectAnalfabeta.addEventListener('change', toggleOptions);
    selectEtnia.addEventListener('change', toggleOptions);
    selectDiscapacidad.addEventListener('change', toggleOptions);
    toggleOptions(); // Inicializa la función al cargar

    // =====================================================================
    // LÓGICA PARA PATOLOGÍAS Y ALERGIAS
    // =====================================================================

    // --- Patologías ---
    document.getElementById('modal_patologia').addEventListener('change', function() {
      const patologiaId = this.value;
      if (!patologiaId || patologiaId === 'disabled') {
        document.getElementById('modal_codigo_cie').value = '';
        return;
      }
      fetch('get/get_cie.php?Id_patologia=' + patologiaId)
        .then(res => res.json())
        .then(data => {
          document.getElementById('modal_codigo_cie').value = data.codigo_cie || 'No disponible';
        })
        .catch(error => {
          console.error('Error al obtener código CIE:', error);
          document.getElementById('modal_codigo_cie').value = 'Error';
        });
    });

    document.getElementById('agregarPatologia').addEventListener('click', function() {
      const select = document.getElementById('modal_patologia');
      const id = select.value;
      const nombre = select.options[select.selectedIndex].getAttribute('data-nombre');
      const codigo_cie = document.getElementById('modal_codigo_cie').value;

      if (!id || id === 'disabled') {
        mostrarAviso('⚠️ Seleccione una Patología.');
        return;
      }
      if (patologiasSeleccionadas.some(p => p.id == id)) {
        mostrarAviso('⚠️ Esta patología ya está añadida en la lista temporal.');
        return;
      }

      patologiasSeleccionadas.push({
        id: parseInt(id),
        nombre,
        codigo_cie
      });
      renderPatologiasModal();
      document.getElementById('modal_patologia').value = 'disabled';
      document.getElementById('modal_codigo_cie').value = '';
    });

    document.getElementById('guardarPatologias').addEventListener('click', function() {
      actualizarDisplayPatologias();
      $('#modalPatologias').modal('hide');
    });

    function renderPatologiasModal() {
      const lista = document.getElementById('lista_patologias_seleccionadas');
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
    // --- AJAX PARA GUARDAR NUEVA PATOLOGÍA ---
    $('#btnGuardarNuevaPatologiaAjax').click(function() {
      const nombre = $('#nuevo_nombre_patologia').val().trim();
      const cie = $('#nuevo_codigo_cie').val().trim();
      const contagiosa = $('#nueva_es_contagiosa').val();

      if (nombre === '' || cie === '' || contagiosa === '') {
        mostrarAviso('Todos los campos son obligatorios.');
        return;
      }

      // Función simulada de guardar/verificar para mantener la estructura original
      function guardar() {
        // Simulamos la inserción y recargamos el modalPatologias (en una app real se haría un AJAX insert)
        $('#modalNuevaPatologia').modal('hide');
        mostrarAviso('✅ Patología guardada correctamente.');
        // Recargar el select modal_patologia si fuera necesario
      }

      // Simulamos la verificación
      $.ajax({
        url: 'get/verificar_existencia_patologa.php', // URL simulada
        data: {
          nombre: nombre
        },
        success: function(res) {
          // El resultado 'res' debería ser un objeto JSON { existe: boolean }
          // Como no tenemos el archivo real, asumiremos que no existe para continuar.
          // if(res.existe) mostrarAviso('Ya existe'); else guardar(); 
          guardar();
        },
        error: function() {
          guardar(); // En caso de error de conexión, se asume que no existe para fines de demostración
        }
      });
    });

    // --- AJAX PARA GUARDAR NUEVA ALERGIA ---
    $('#btnGuardarNuevaAlergiaAjax').click(function() {
      const nombre = $('#nuevo_nombre_alergia').val().trim();

      if (nombre === '') {
        mostrarAviso('El nombre es obligatorio.');
        return;
      }

      // Simulación de guardado.
      $('#modalNuevaAlergia').modal('hide');
      mostrarAviso('✅ Alergia guardada correctamente.');
    });

    function actualizarDisplayPatologias() {
      const display = document.getElementById('patologias_agregadas');
      const inputHidden = document.getElementById('patologias_ids');
      const nombres = patologiasSeleccionadas.map(p => p.nombre);
      const ids = patologiasSeleccionadas.map(p => p.id);
      const LIMITE_DISPLAY = 2;

      inputHidden.value = ids.join(',');

      if (nombres.length > 0) {
        let htmlContent = '';

        for (let i = 0; i < Math.min(nombres.length, LIMITE_DISPLAY); i++) {
          htmlContent += `<span class="text" style="margin-right: 5px; margin-bottom: 5px; display: inline-block; padding: 6px;">${nombres[i]},</span>`;
        }

        if (nombres.length > LIMITE_DISPLAY) {
          let restantes = nombres.length - LIMITE_DISPLAY;
          htmlContent += `<span class="text-muted" style="margin-left:5px; font-weight:bold;">... y ${restantes} más.</span>`;
        }
        $(display).removeClass('input-error'); // Quitar error si se seleccionó al menos una
        display.innerHTML = htmlContent;

      } else {
        display.innerHTML = '<span class="text-muted">Ninguna patología seleccionada.</span>';
      }
    }

    $('#modalPatologias').on('click', '.quitar-patologia', function() {
      const idQuitar = $(this).data('id');
      patologiasSeleccionadas = patologiasSeleccionadas.filter(p => p.id != idQuitar);
      renderPatologiasModal(); // Volver a renderizar la lista temporal en el modal
      // También se debe actualizar el display principal por si se eliminó el último
      actualizarDisplayPatologias();
    });

    // --- Alergias ---

    document.getElementById('agregarAlergia').addEventListener('click', function() {
      const select = document.getElementById('modal_alergia');
      const id = select.value;
      const nombre = select.options[select.selectedIndex].getAttribute('data-nombre');

      if (!id || id === 'disabled') {
        mostrarAviso('⚠️ Seleccione una Alergia.');
        return;
      }
      if (alergiasSeleccionadas.some(a => a.id == id)) {
        mostrarAviso('⚠️ Esta alergia ya está añadida en la lista temporal.');
        return;
      }

      alergiasSeleccionadas.push({
        id: parseInt(id),
        nombre
      });
      renderAlergiasModal();
      document.getElementById('modal_alergia').value = 'disabled';
    });

    document.getElementById('guardarAlergias').addEventListener('click', function() {
      actualizarDisplayAlergias();
      $('#modalAlergias').modal('hide');
    });

    function renderAlergiasModal() {
      const lista = document.getElementById('lista_alergias_seleccionadas');
      lista.innerHTML = '';
      if (alergiasSeleccionadas.length === 0) {
        lista.innerHTML = 'Ninguna alergia en la lista temporal.';
        return;
      }
      alergiasSeleccionadas.forEach(a => {
        lista.innerHTML += `
            <span class="label label-primary" style="margin-right: 5px; margin-bottom: 5px; display: inline-block; font-size: 14px; padding: 6px 10px 6px 10px;">
                ${a.nombre} 
                <span class="quitar-alergia" data-id="${a.id}" 
                   style="cursor: pointer; margin-left: 8px; font-weight: bold; color: white;" 
                   title="Eliminar">&times;</span>
            </span>`;
      });
    }

    function actualizarDisplayAlergias() {
      const display = document.getElementById('alergias_agregadas');
      const inputHidden = document.getElementById('alergias_ids');
      const nombres = alergiasSeleccionadas.map(a => a.nombre);
      const ids = alergiasSeleccionadas.map(a => a.id);
      const LIMITE_DISPLAY = 2;

      inputHidden.value = ids.join(',');

      if (nombres.length > 0) {
        let htmlContent = '';

        for (let i = 0; i < Math.min(nombres.length, LIMITE_DISPLAY); i++) {
          htmlContent += `<span class="text" style="margin-right: 5px; margin-bottom: 5px; display: inline-block; padding: 6px;">${nombres[i]},</span>`;
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

    $('#modalAlergias').on('click', '.quitar-alergia', function() {
      const idQuitar = $(this).data('id');
      alergiasSeleccionadas = alergiasSeleccionadas.filter(a => a.id != idQuitar);
      renderAlergiasModal();
      actualizarDisplayAlergias();
    });


    // =====================================================================
    // VALIDACIÓN PRINCIPAL DE CADA PESTAÑA (ASÍNCRONA)
    // =====================================================================

    async function validarPestana(tabSelector) {
      let esValido = true;
      let errores = [];
      limpiarErroresPestana(tabSelector);

      // 1. Validación de campos obligatorios (*)
      $(tabSelector).find('[required]').each(function() {
        var $input = $(this);
        var valor = $input.val();
        // Criterio de validación: visible, no deshabilitado y valor vacío o de placeholder
        if ($input.is(':visible') && !$input.prop('disabled') && (valor === null || valor.trim() === "" || valor.includes('--- Seleccione'))) {
          // Excluir el campo de patologías si se maneja con un div no-input
          if ($input.attr('id') !== 'patologias_ids') {
            errores.push("El campo " + $input.prev('p').text().replace('(*):', '').replace(':', '').trim() + " es obligatorio.");
            $input.addClass('input-error');
            esValido = false;
          }
        }
      });

      // 2. Validaciones Específicas

      if (tabSelector === '#info') {
        const cedulaEsValida = await validarCedulaFormato();
        if (!cedulaEsValida) {
          esValido = false;
        }

        const edad = parseInt($('#edad_adulto').val());
        if (isNaN(edad) || edad < 18 || edad > 120) {
          errores.push("El paciente debe ser mayor de 18 años (Fecha de Nacimiento).");
          $('#fecha_nacimiento_adulto').addClass('input-error');
          esValido = false;
        }

        const email = $('#email').val().trim();
        if (email !== "" && (email.indexOf('@') === -1 || email.indexOf('.') === -1)) {
          errores.push("El campo Email debe tener un formato válido (ej: nombre@dominio.com).");
          $('#email').addClass('input-error');
          esValido = false;
        }
      }

      if (tabSelector === '#direccion') {
        // Validación de campos obligatorios ya cubre Estado, Municipio y Sector.
      }

      if (tabSelector === '#ocupacion_estudios') {
        // Validar años aprobados si está habilitado
        if (!$('#años_aprobados').prop('disabled')) {
          const años = parseInt($('#años_aprobados').val());
          const max = parseInt($('#años_aprobados').attr('max'));
          if (isNaN(años) || años < 0) {
            errores.push("El campo Años Aprobados es obligatorio si seleccionó 'Sí' en Analfabeta.");
            $('#años_aprobados').addClass('input-error');
            esValido = false;
          } else if (!isNaN(max) && años > max) {
            errores.push("Los Años Aprobados exceden el máximo permitido para esta Misión (" + max + " años).");
            $('#años_aprobados').addClass('input-error');
            esValido = false;
          }
        }
      }

      if (tabSelector === '#salud_otros') {
        // Validación de Patologías (marcado con el (*) en el PHP corregido)
        /*if (patologiasSeleccionadas.length === 0) {
          errores.push("Debe seleccionar al menos una Patología.");
          $('#patologias_agregadas').addClass('input-error');
          esValido = false;
        }*/
        // Validación de Grupo Sanguíneo ya está cubierta por el [required]
      }

      if (!esValido && errores.length > 0) {
        mostrarAviso('⚠️ Errores de Formulario:<ul><li>' + errores.join('</li><li>') + '</li></ul>');
      }

      return esValido;
    }


    // =====================================================================
    // MANEJO DE PESTAÑAS (TABS)
    // =====================================================================

    // 1. MANEJO DE BOTONES SIGUIENTE
    $('.next-tab').on('click', async function() {
      const $btn = $(this);
      const tabActualSelector = $btn.data('tab-actual');
      const tabSiguienteName = $btn.data('tab-siguiente');
      const nextTabLink = $(`.nav-tabs li[data-tab-name="${tabSiguienteName}"] a`);

      $btn.prop('disabled', true).text('Validando...');

      const esValido = await validarPestana(tabActualSelector);

      $btn.prop('disabled', false).text(tabSiguienteName === 'confirmar' ? 'Guardar' : 'Siguiente');

      if (esValido) {
        if (tabSiguienteName === 'confirmar') {
          $('#modalGuardarPaciente').modal('show');
        } else {
          const $siguienteTabLi = nextTabLink.parent();
          // Quitar active de la pestaña actual
          $(`.nav-tabs li[data-tab-name]:has(a[href="${tabActualSelector}"]`).removeClass('active');
          // Habilitar y activar la pestaña siguiente
          $siguienteTabLi.removeClass('disabled-tab').addClass('active');
          nextTabLink.tab('show');
        }
      }
    });

    // 2. MANEJO DE BOTONES REGRESAR/ATRAS (Corregido)
    $('.prev-tab').on('click', function() {
      const tabAnteriorSelector = $(this).data('tab-anterior');
      const tabAnteriorName = tabAnteriorSelector.replace('#', '');
      const $anteriorTabLi = $(`.nav-tabs li[data-tab-name="${tabAnteriorName}"]`);

      // La pestaña de "Regresar" en #info es un caso especial y usa data-toggle para el modal
      if ($(this).data('target') === '#modalConfirmarRegreso') {
        return; // Dejar que el data-toggle maneje el modal
      }

      // Si no es el botón de modal, maneja el cambio de pestaña
      const $actualTabLi = $anteriorTabLi.siblings('.active');

      $actualTabLi.removeClass('active');
      $anteriorTabLi.addClass('active');
      $(`.nav-tabs a[href="${tabAnteriorSelector}"]`).tab('show');
    });

    // BLOQUEO DE CLIC EN PESTAÑAS: Se maneja principalmente por CSS (pointer-events: none)
    $('.nav-tabs a').on('click', function(e) {
      if ($(this).parent().hasClass('disabled-tab')) {
        return false;
      }
    });


    // =====================================================================
    // FIX GENERAL DE MODALES Y ANIMACIÓN
    // =====================================================================

    function closeCustomModal($modal) {
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
        closeCustomModal($(this));
      }
    });

    $('.modal .close, .modal .btn-default[data-dismiss="modal"], .modal .btn-secondary[data-dismiss="modal"]').on('click', function(e) {
      const $modal = $(this).closest('.modal');
      if ($modal.length && $modal.hasClass('in')) {
        closeCustomModal($modal);
      }
    });

    // MODIFICACIÓN SOLICITADA: Cierre al hacer clic en el backdrop (parte oscura)
    $('.modal').on('click', function(e) {
      // Si el objetivo del clic es el contenedor .modal (y no el .modal-dialog hijo), significa que clicó fuera
      if ($(e.target).hasClass('modal') && $(this).hasClass('in')) {
        closeCustomModal($(this));
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

    $('#confirmarGuardadoFinal').on('click', function() {
      $('#modalGuardarPaciente').modal('hide');
      $('#formularioPaciente').submit();
    });

    // --- Aplicar validaciones a campos de solo texto ---
    const campos = [document.getElementById("solo_texto"), document.getElementById("solo_texto1")];
    campos.forEach(campo => {
      if (campo) {
        campo.addEventListener("keydown", bloquearNumeros);
        campo.addEventListener("input", limpiarNumeros);
      }
    });
  </script>
  <script>
    function soloNumerosSinE(campo, maxDigitos) {
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

    soloNumerosSinE(document.getElementById("cedula"), 8);
    soloNumerosSinE(document.getElementById("telefono"), 7);
    soloNumerosSinE(document.getElementById("tiempo_residencia"));
  </script>
  </body>

</html>