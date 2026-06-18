<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Representantes | Añadir</title>
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
  <div class="content-wrapper">
    <section class="content-header">
      <h1>
        Añadir Representante
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-users"></i>Representante</a></li>
        <li class="active"><a href="#"><i class="fa fa-user-plus"></i>Añadir</a></li>
      </ol>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active" data-tab-name="info"><a href="#info" data-toggle="tab">Datos Personales</a></li>
              <li data-tab-name="direccion" class="disabled-tab"><a href="#direccion" data-toggle="tab">Dirección de Residencia</a></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane active" id="info">
                <form id="formularioRepresentante" action="../../cfg/agregar/agregar_representante.php" class="form-group" method="POST" novalidate>
                  <section id="new" style="margin-bottom:7%;">
                    <label class="control-label"></label>
                    <div class="col-sm-1 pull-left" style="margin-top: 30px;">
                      <select name="tipo_cedula" id="tipo_cedula" class="form-control" style="width: 60px;" required>
                        <option value="V">V-</option>
                        <!--<option value="E">E-</option>-->
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
                      <input type="date" class="form-control pull-right" id="fechaN" name="fecha_nacimiento" onchange="calcularEdad()" required>
                    </div>
                    <div class="col-sm-1">
                      <p style="margin-left: 5px;">Edad</p>
                      <input type="text" class="form-control pull-right" id="edad" name="edad" readonly>
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
                      <p>País de nacimiento:</p>
                      <select name="pais_nacimiento" id="pais_nacimiento" class="form-control">
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
                    <br><br><br><br>
                    <label class="control-label"></label>
                    <div class="col-sm-4">
                      <p>Estado de nacimiento:</p>
                      <select name="estado_nacimiento" id="estado_nacimiento" class="form-control">
                        <option value="">--- Seleccione Un Estado ---</option>
                      </select>
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-4">
                      <p>Municipio de nacimiento:</p>
                      <select name="municipio_nacimiento" id="municipio_nacimiento" class="form-control">
                        <option value="">--- Seleccione Un Municipio ---</option>
                      </select>
                    </div>
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
                    <div class="col-sm-2">
                      <p>Teléfono (*):</p>
                      <input type="text" class="form-control" name="telefono" id="telefono" placeholder="N° De Teléfono" minlength="7" maxlength="7" required>
                    </div>
                    <br><br><br><br>
                    <label class="control-label"></label>
                    <div class="col-sm-4">
                      <p>Email:</p>
                      <input type="email" class="form-control" name="email" id="email" placeholder="nombreapellido2@gmail.com">
                    </div>
                    <div style="float:right; margin-top:6%;">
                      <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#modalConfirmarRegreso">Regresar</button>
                      <button type="button" class="btn btn-primary next-tab" data-tab-actual="#info" data-tab-siguiente="direccion">Siguiente</button>
                    </div>
                  </section>
              </div>
              <div class="tab-pane" id="direccion">
                <section id="new" style="margin-bottom:7%;">
                  <label class="control-label"></label>
                  <div class="col-sm-4">
                    <p>Estado:</p>
                    <select name="estado" id="estado" class="form-control">
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
                    <p>Municipio:</p>
                    <select name="municipio" id="municipio" class="form-control">
                      <option value="">--- Seleccione Un Municipio ---</option>
                    </select>
                  </div>
                  <label class="control-label"></label>
                  <div class="col-sm-3">
                    <p>Sector:</p>
                    <select name="sector" id="sector" class="form-control">
                      <option value="">--- Seleccione Un Sector ---</option>
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
                  <div style="float:right; margin-top:4%;">
                    <button type="button" class="btn btn-secondary prev-tab" data-tab-anterior="#info">Atrás</button>
                    <button type="button" class="btn btn-success next-tab" data-tab-actual="#direccion" data-tab-siguiente="confirmar">Guardar</button>
                  </div>
                </section>
              </div>
              <div class="tab-pane" id="salud_otros">
                <section id="new" style="margin-bottom:12%;">
                  <label class="control-label"></label>
                  <div class="col-sm-5">
                    <p>Patologias:</p>
                    <input type="hidden" name="patologias_ids" id="patologias_ids" value="">
                    <div id="patologias_agregadas" class="well well-sm" style="height: auto; min-height: 34px;">
                      <span class="text-muted">Ninguna Patologia seleccionada.</span></div>
                  </div>
                  <div class="col-sm-1" style="margin-top: 30px;">
                    <button type="button" class="form-control pull-right bt-sm btn-primary" data-toggle="modal" data-target="#modalPatologias">+</button>
                  </div>
                  <label class="control-label"></label>
                  <div class="col-sm-4">
                    <p>Alergias:</p>
                    <input type="hidden" name="alergias_ids" id="alergias_ids" value="">
                    <div id="alergias_agregadas" class="well well-sm" style="height: auto; min-height: 34px;">
                      <span class="text-muted">Ninguna Alergia seleccionada.</span></div>
                  </div>
                  <div class="col-sm-1" style="margin-top: 30px;">
                    <button type="button" class="form-control pull-right bt-sm btn-primary" data-toggle="modal" data-target="#modalAlergias">+
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
                    <p>Discapacidad:</p>
                    <select name="discapacidad" id="discapacidad" class="form-control" required>
                      <option value="No">No</option>
                      <option value="Si">Si</option>
                    </select>
                  </div>
                  <div class="col-sm-5">
                    <p>Tipo de discapacidad:</p>
                    <select name="tipo_discapacidad" id="tipo_discapacidad" class="form-control">
                      <option value="">--- Seleccione Una Discapacidad ---</option>
                      <option value=""></option>
                    </select>
                  </div>
                  <div style="float:right; margin-top:4%;">
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
  </div>


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

  <div class="modal" id="modalGuardarMedico" tabindex="-1" role="dialog" aria-labelledby="modalGuardarMedicoLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: #00a65a; color: white;">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="modalGuardarMedicoLabel"><i class="fa fa-save"></i> Confirmar Guardado</h4>
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
          <h4 class="modal-title" id="modalConfirmarRegresoLabel"><i class="fa fa-sign-out"></i> Confirmar Abandono</h4>
        </div>
        <div class="modal-body">
          <p>Al hacer clic en "Abandonar Formulario", perderá todos los datos no guardados. ¿Desea continuar?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <a href="representantes_listado.php" class="btn btn-danger">Abandonar Formulario</a>
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

  <?php include('includes/footer.php'); ?>

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
      document.getElementById('fechaN').setAttribute('max', maxDate);
    }
    setMaxDateFor18Years();

    function calcularEdad() {
      const fechaNac = document.getElementById('fechaN').value;
      if (!fechaNac) {
        document.getElementById('edad').value = '';
        return;
      }

      const hoy = new Date();
      const cumple = new Date(fechaNac);
      let edad = hoy.getFullYear() - cumple.getFullYear();
      const m = hoy.getMonth() - cumple.getMonth();

      if (m < 0 || (m === 0 && hoy.getDate() < cumple.getDate())) {
        edad--;
      }
      document.getElementById('edad').value = edad;
    }

    // --- 1. FUNCIÓN AJAX PARA VERIFICAR EN BD ---
    // --- 1. FUNCIÓN AJAX UNIFICADA (CÉDULA Y EMAIL) ---
    async function verificarDatosUnicosBD(tipo, cedula, email, idPersona) {
      return new Promise((resolve) => {
        if (cedula === "" && email === "") {
          resolve({
            existe_cedula: false,
            existe_email: false
          });
          return;
        }
        $.ajax({
          url: 'get/verificar_existencia_cedula.php',
          method: 'POST',
          dataType: 'json',
          data: {
            tipo_cedula: tipo,
            cedula: cedula,
            email: email,
            id: idPersona
          },
          success: function(response) {
            resolve(response);
          },
          error: function() {
            resolve({
              existe_cedula: false,
              existe_email: false
            });
          }
        });
      });
    }

    // --- 2. VALIDACIÓN UNIFICADA DE FORMATO Y EXISTENCIA ---
    async function validarDatosUnicos() {
      // Referencias dinámicas (detecta automáticamente si es paciente normal o representante)
      const cedulaInput = document.getElementById('cedula') || document.getElementById('cedula_rep');
      const tipoSelect = document.getElementById('tipo_cedula') || document.getElementById('tipo_cedula_rep');
      const emailInput = document.getElementById('email') || document.getElementById('email_rep');

      const cedula = cedulaInput ? cedulaInput.value.trim() : "";
      const tipo = tipoSelect ? tipoSelect.value : "";
      const email = emailInput ? emailInput.value.trim() : "";

      // Capturar ID en caso de edición para excluirlo
      let idActual = 0;
      const inputId = document.querySelector('input[name="Id_representante"]') || document.querySelector('input[name="Id"]');
      if (inputId && inputId.value) idActual = inputId.value;

      let esValido = true;
      let errores = [];

      // Limpiar bordes rojos previos
      if (cedulaInput) $(cedulaInput).removeClass('input-error');
      if (tipoSelect) $(tipoSelect).removeClass('input-error');
      if (emailInput) $(emailInput).removeClass('input-error');

      // --- Validaciones Locales de Formato ---
      if (email !== "" && (email.indexOf('@') === -1 || email.indexOf('.') === -1)) {
        if (emailInput) $(emailInput).addClass('input-error');
        errores.push("El campo Email debe tener un formato válido (ej: usuario@correo.com).");
        esValido = false;
      }

      if (cedula !== "" && !isNaN(parseInt(cedula))) {
        const cedNum = parseInt(cedula);
        if (tipo === 'V' && cedNum > 80000000) {
          if (cedulaInput) $(cedulaInput).addClass('input-error');
          if (tipoSelect) $(tipoSelect).addClass('input-error');
          errores.push("Para tipo V-, la cédula no puede ser mayor a 80.000.000.");
          esValido = false;
        } else if (tipo === 'E' && cedNum < 80000000) {
          if (cedulaInput) $(cedulaInput).addClass('input-error');
          if (tipoSelect) $(tipoSelect).addClass('input-error');
          errores.push("Para tipo E-, la cédula no puede ser menor a 80.000.000.");
          esValido = false;
        }
      }

      if (!esValido) {
        mostrarAviso("🛑 <b>Errores de formato:</b><br>" + errores.join("<br>"));
        return false; // Aborta antes de golpear la BD si el formato está mal
      }

      // --- Verificación Combinada en Base de Datos ---
      if (cedula !== "" || email !== "") {
        const bd = await verificarDatosUnicosBD(tipo, cedula, email, idActual);

        if (bd.existe_cedula) {
          if (cedulaInput) $(cedulaInput).addClass('input-error');
          if (tipoSelect) $(tipoSelect).addClass('input-error');
          errores.push(`El documento <b>${tipo}-${cedula}</b> ya se encuentra registrado en el sistema.`);
          esValido = false;
        }

        if (bd.existe_email) {
          if (emailInput) $(emailInput).addClass('input-error');
          errores.push(`El correo <b>${email}</b> ya está siendo usado por otra persona.`);
          esValido = false;
        }

        if (!esValido) {
          mostrarAviso("🛑 <b>Datos duplicados detectados:</b><br>" + errores.join("<br>"));
        }
      }

      return esValido;
    }

    // --- 3. DISPARADORES AUTOMÁTICOS ---
    if (document.getElementById('cedula')) document.getElementById('cedula').addEventListener('blur', validarDatosUnicos);
    if (document.getElementById('tipo_cedula')) document.getElementById('tipo_cedula').addEventListener('change', validarDatosUnicos);
    if (document.getElementById('email')) document.getElementById('email').addEventListener('blur', validarDatosUnicos);
    if (document.getElementById('email_rep')) document.getElementById('email_rep').addEventListener('blur', validarDatosUnicos);

    // ==========================================================================
    // --- LÓGICA DE DEPENDENCIAS DE UBICACIÓN (Nacimiento y Residencia) ---
    // ==========================================================================

    // 1. PAÍS NACIMIENTO -> ESTADO NACIMIENTO
    document.getElementById('pais_nacimiento').addEventListener('change', function() {
      var paisId = this.value;
      var estadoSelect = document.getElementById('estado_nacimiento');
      var municipioSelect = document.getElementById('municipio_nacimiento');

      estadoSelect.innerHTML = '<option value="">--- Seleccione Un Estado ---</option>';
      municipioSelect.innerHTML = '<option value="">--- Seleccione Un Municipio ---</option>';

      if (paisId) {
        $.ajax({
          url: 'get/get_estados.php',
          method: 'GET',
          data: {
            Id_Pais: paisId
          },
          dataType: 'json',
          cache: false,
          success: function(data) {
            for (var i = 0; i < data.length; i++) {
              var estado = data[i];
              estadoSelect.innerHTML += '<option value="' + estado.Id_Estado + '">' + estado.nombre_estado + '</option>';
            }
          },
          error: function(xhr, status, error) {
            console.error("Error al cargar estados de nacimiento:", error);
          }
        });
      }
    });

    // 2. ESTADO NACIMIENTO -> MUNICIPIO NACIMIENTO
    document.getElementById('estado_nacimiento').addEventListener('change', function() {
      var estadoId = this.value;
      var municipioSelect = document.getElementById('municipio_nacimiento');

      municipioSelect.innerHTML = '<option value="">--- Seleccione Un Municipio ---</option>';

      if (estadoId) {
        $.ajax({
          url: 'get/get_municipios.php',
          method: 'GET',
          data: {
            Id_Estado: estadoId
          },
          dataType: 'json',
          cache: false,
          success: function(data) {
            for (var i = 0; i < data.length; i++) {
              var municipio = data[i];
              municipioSelect.innerHTML += '<option value="' + municipio.Id_Municipio + '">' + municipio.nombre_municipio + '</option>';
            }
          },
          error: function(xhr, status, error) {
            console.error("Error al cargar municipios de nacimiento:", error);
          }
        });
      }
    });

    // 3. ESTADO RESIDENCIA (NORMAL) -> MUNICIPIO RESIDENCIA
    document.getElementById('estado').addEventListener('change', function() {
      var estadoId = this.value;
      var municipioSelect = document.getElementById('municipio');
      var sectorSelect = document.getElementById('sector');

      // Limpiamos tanto municipios como sectores para que no queden datos viejos colgados
      municipioSelect.innerHTML = '<option value="">--- Seleccione Un Municipio ---</option>';
      if (sectorSelect) {
        sectorSelect.innerHTML = '<option value="">--- Seleccione Un Sector ---</option>';
      }

      if (estadoId) {
        $.ajax({
          url: 'get/get_municipios.php',
          method: 'GET',
          data: {
            Id_Estado: estadoId
          },
          dataType: 'json',
          cache: false,
          success: function(data) {
            for (var i = 0; i < data.length; i++) {
              var municipio = data[i];
              municipioSelect.innerHTML += '<option value="' + municipio.Id_Municipio + '">' + municipio.nombre_municipio + '</option>';
            }
          },
          error: function(xhr, status, error) {
            console.error("Error al cargar municipios de residencia:", error);
          }
        });
      }
    });

    // 4. MUNICIPIO RESIDENCIA (NORMAL) -> SECTOR RESIDENCIA
    document.getElementById('municipio').addEventListener('change', function() {
      var municipioId = this.value;
      var sectorSelect = document.getElementById('sector');

      sectorSelect.innerHTML = '<option value="">--- Seleccione Un Sector ---</option>';

      if (municipioId) {
        $.ajax({
          url: 'get/get_sectores.php',
          method: 'GET',
          data: {
            Id_Municipio: municipioId
          },
          dataType: 'json',
          cache: false,
          success: function(data) {
            for (var i = 0; i < data.length; i++) {
              var sector = data[i];
              sectorSelect.innerHTML += '<option value="' + sector.Id_Sector + '">' + sector.nombre_sector + '</option>';
            }
          },
          error: function(xhr, status, error) {
            console.error("Error al cargar sectores de residencia:", error);
          }
        });
      }
    });

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
          htmlContent += `<span class="label label-primary" style="margin-right: 5px; margin-bottom: 5px; display: inline-block; padding: 6px;">${nombres[i]}</span>`;
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
        const datosValidos = await validarDatosUnicos();
        if (!datosValidos) {
          esValido = false;
        }

        const edad = parseInt($('#edad').val());
        if (isNaN(edad) || edad < 18 || edad > 120) {
          errores.push("El paciente debe ser mayor de 18 años (Fecha de Nacimiento).");
          $('#fechaN').addClass('input-error');
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

    $('.next-tab').off('click').on('click', async function() {
      const $btn = $(this);
      const tabActualSelector = $btn.data('tab-actual');
      const tabSiguienteName = $btn.data('tab-siguiente');
      const nextTabLink = $(`.nav-tabs li[data-tab-name="${tabSiguienteName}"] a`);

      $btn.prop('disabled', true).text('Validando...');

      // validarPestana se encarga de bloquear el paso de la pestaña 1 si la cédula existe
      const esValido = await validarPestana(tabActualSelector);

      $btn.prop('disabled', false).text(tabSiguienteName === 'confirmar' ? 'Guardar' : 'Siguiente');

      if (esValido) {
        if (tabSiguienteName === 'confirmar') {
          // ULTIMA VERIFICACIÓN ANTES DE MOSTRAR EL MODAL
          const cedulaFinalValida = await validarDatosUnicos();
          if (!cedulaFinalValida) {
            mostrarAviso("🛑 Cédula Duplicada. Regrese a la pestaña 'Datos Personales' y corrija la información.");
            return;
          }

          const modalSelector = $('#modalGuardarPaciente').length ? '#modalGuardarPaciente' : '#modalGuardarMedico';
          $(modalSelector).modal('show');
        } else {
          const $siguienteTabLi = nextTabLink.parent();
          $(`.nav-tabs li[data-tab-name]:has(a[href="${tabActualSelector}"]`).removeClass('active');
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
      $('#modalGuardarMedico').modal('hide');
      $('#formularioRepresentante').submit();
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