<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Representantes | Editar</title>
  <?php
  include('includes/headerNav2.php');
  ?>
  <style>
    /* ---------------------------------------------------------------------- */
    /* ANIMACIONES Y ESTILOS DE MODALES (Copiado de pacientes_agregar.php) */
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
    /* ESTILOS DE VALIDACIÓN Y LAYOUT (Copiado de pacientes_agregar.php) */
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
        Editar Representante
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-users"></i>Representante</a></li>
        <li class="active"><a href="#"><i class="fa fa-user-plus"></i>Editar</a></li>
      </ol>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active" data-tab-name="info"><a href="#info" data-toggle="tab">Datos Personales</a></li>
              <li data-tab-name="direccion" class="disabled-tab"><a href="#direccion" data-toggle="tab">Direccion de Residencia</a></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane active" id="info">
                <form id="formularioRepresentante" action="../../cfg/editar/editar_representante.php" class="form-group" method="POST" novalidate>
                  <?php
                  // Bloque PHP para cargar los datos del paciente
                  include("../../cfg/conexion.php");

                  // CONSULTA SQL CORREGIDA Y AMPLIADA para obtener todos los IDs necesarios
                  $sql = "SELECT p.id, p.email, p.nombre, p.apellido, p.tipo_cedula, p.cedula, p.fecha_nacimiento, p.genero, p.email,
                           tp.telefono, pt.prefijo, tp.Id_prefijo,
                           d.avenida_calle, d.referencia, d.tiempo_residencia, d.Id_sector, d.tiempo, /* DATOS DE DIRECCIÓN */
                           ln.Id_municipio AS Id_Municipio_Nac,  /* ID DE MUNICIPIO DE NACIMIENTO */
                           munnac.Id_Estado AS Id_Estado_Nac,   /* ID DE ESTADO DE NACIMIENTO */
                           estnac.Id_Pais AS Id_Pais_Nac,       /* ID DE PAÍS DE NACIMIENTO */
                           dirsec.Id_Municipio AS Id_Municipio_Dir,
                           dirmun.Id_Estado AS Id_Estado_Dir

                  FROM persona p
                  LEFT JOIN telefonos_personas tp ON p.id = tp.Id_persona
                  LEFT JOIN prefijos_telefonos pt ON tp.Id_prefijo = pt.Id
                  LEFT JOIN lugar_nacimiento ln ON p.id = ln.Id_persona
                  LEFT JOIN municipio munnac ON ln.Id_municipio = munnac.Id_Municipio
                  LEFT JOIN estado estnac ON munnac.Id_Estado = estnac.Id_Estado
                  LEFT JOIN direccion d ON p.id = d.Id_persona
                  LEFT JOIN sector dirsec ON d.Id_sector = dirsec.Id_Sector
                  LEFT JOIN municipio dirmun ON dirsec.Id_Municipio = dirmun.Id_Municipio

                  WHERE p.id =" . $_GET['Id'] . "
                  GROUP BY p.id";

                  $resultado = $conexion->query($sql);
                  $row = $resultado->fetch_assoc(); // $row contiene todos los datos
                  $id_representante = $row['id']; // ID del paciente actual

                  // Variables PHP para usar en JS
                  $id_prefijo = $row['Id_prefijo'] ?? '';
                  $id_pais_nac = $row['Id_Pais_Nac'] ?? '';
                  $id_estado_nac = $row['Id_Estado_Nac'] ?? '';
                  $id_municipio_nac = $row['Id_Municipio_Nac'] ?? '';

                  $id_estado_dir = $row['Id_Estado_Dir'] ?? '';
                  $id_municipio_dir = $row['Id_Municipio_Dir'] ?? '';
                  $id_sector_dir = $row['Id_sector'] ?? '';
                  ?>


                  <input type="hidden" name="Id" value="<?= $id_representante; ?>">

                  <section id="new" style="margin-bottom:7%;">
                    <label class="control-label"></label>
                    <div class="col-sm-1 pull-left" style="margin-top: 30px;">
                      <select name="tipo_cedula" id="tipo_cedula" class="form-control" style="width: 60px;" required>
                        <option value="V" <?php echo ($row['tipo_cedula'] == 'V' ? 'selected' : ''); ?>>V-</option>
                        <!--<option value="E" <?php echo ($row['tipo_cedula'] == 'E' ? 'selected' : ''); ?>>E-</option>-->
                      </select>
                    </div>
                    <div class="col-sm-3">
                      <p>Cedula (*):</p>
                      <input type="text" class="form-control" value="<?php echo $row['cedula']; ?>" name="cedula" id="cedula" placeholder="N° de Cedula" maxlength="8" required>
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-4">
                      <p>Nombre (*):</p>
                      <input type="text" class="form-control" name="nombre" value="<?php echo $row['nombre']; ?>" id="solo_texto" placeholder="Nombre Del Paciente" maxlength="100" required>
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-3">
                      <p>Apellido:</p>
                      <input type="text" class="form-control" name="apellido" value="<?php echo $row['apellido']; ?>" id="solo_texto1" placeholder="Apellido Del Paciente" maxlength="100">
                    </div>
                    <br><br><br><br>
                    <label class="control-label"></label>
                    <div class="col-sm-3">
                      <p>Fecha de nacimiento (*):</p>
                      <input type="date" class="form-control pull-right" value="<?php echo $row['fecha_nacimiento']; ?>" id="fechaN" name="fecha_nacimiento" onchange="calcularEdad()" max="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-sm-1">
                      <p style="margin-left: 5px;">Edad</p>
                      <input type="text" class="form-control pull-right" id="edad" name="edad" readonly>
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-4">
                      <p>Sexo (*):</p>
                      <select name="genero" id="genero" class="form-control" required>
                        <option value="">--- Seleccione Un Genero ---</option>
                        <option value="Masculino" <?php echo ($row['genero'] == 'Masculino' ? 'selected' : ''); ?>>Masculino</option>
                        <option value="Femenino" <?php echo ($row['genero'] == 'Femenino' ? 'selected' : ''); ?>>Femenino</option>
                      </select>
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-3">
                      <p>Pais de nacimiento:</p>
                      <select name="pais_nacimiento" id="pais_nacimiento" class="form-control">
                        <option value="">--- Seleccione un Pais ---</option>
                        <?php
                        // Consulta de países, usando la conexión de arriba
                        $result_nac = $conexion->query("SELECT Id_Pais, nombre_pais FROM pais");
                        while ($row_list = $result_nac->fetch_assoc()) {
                          $selected = ($row_list['Id_Pais'] == $id_pais_nac) ? 'selected' : '';
                          echo "<option value='{$row_list['Id_Pais']}' {$selected}>{$row_list['nombre_pais']}</option>";
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
                        <?php
                        if ($id_pais_nac) {
                            $res_est_nac = $conexion->query("SELECT Id_Estado, nombre_estado FROM estado WHERE Id_Pais = $id_pais_nac");
                            while ($row_est_nac = $res_est_nac->fetch_assoc()) {
                                $selected = ($row_est_nac['Id_Estado'] == $id_estado_nac) ? 'selected' : '';
                                echo "<option value='{$row_est_nac['Id_Estado']}' {$selected}>{$row_est_nac['nombre_estado']}</option>";
                            }
                        }
                        ?>
                      </select>
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-4">
                      <p>Municipio de nacimiento:</p>
                      <select name="municipio_nacimiento" id="municipio_nacimiento" class="form-control">
                        <option value="">--- Seleccione Un Municipio ---</option>
                        <?php
                        if ($id_estado_nac) {
                            $res_mun_nac = $conexion->query("SELECT Id_Municipio, nombre_municipio FROM municipio WHERE Id_Estado = $id_estado_nac");
                            while ($row_mun_nac = $res_mun_nac->fetch_assoc()) {
                                $selected = ($row_mun_nac['Id_Municipio'] == $id_municipio_nac) ? 'selected' : '';
                                echo "<option value='{$row_mun_nac['Id_Municipio']}' {$selected}>{$row_mun_nac['nombre_municipio']}</option>";
                            }
                        }
                        ?>
                      </select>
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-1 pull-left" style="margin-top: 30px;">
                      <select name="prefijo" class="form-control" style="width: 70px;">
                        <option value="" disabled selected>Prefijo</option>
                        <?php
                        $sql = $conexion->query("SELECT * FROM prefijos_telefonos");
                        while ($resultado = $sql->fetch_assoc()) {
                          $selected = ($resultado["Id"] == $id_prefijo) ? 'selected' : '';
                          echo "<option value='" . $resultado["Id"] . "' {$selected}>" . $resultado['prefijo'] . "</option>";
                        }
                        ?>
                      </select>
                    </div>
                    <div class="col-sm-2">
                      <p>Telefono (*):</p>
                      <input type="text" class="form-control" name="telefono" value="<?php echo $row['telefono']; ?>" id="telefono" placeholder="N° De Telefono" minlength="7" maxlength="7" required>
                    </div>
                    <br><br><br><br>
                    <label class="control-label"></label>
                    <div class="col-sm-4">
                      <p>Email:</p>
                      <input type="email" class="form-control" name="email" value="<?php echo $row['email']; ?>" id="email" placeholder="nombreapellido2@gmail.com">
                    </div>
                    <div style="float:right; margin-top:6%;">
                      <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#modalConfirmarRegreso">Regresar</button>
                      <button type="button" class="btn btn-primary next-tab" data-tab-actual="#info" data-tab-siguiente="direccion">Siguiente</button>
                    </div>
                  </section>
              </div>
              <div class="tab-pane" id="direccion">
                <section id="new" style="margin-bottom: 5%;">
                  <label class="control-label"></label>
                  <div class="col-sm-4">
                    <p>Estado:</p>
                    <select name="estado" id="estado" class="form-control">
                      <option value="">--- Seleccione Un Estado ---</option>
                      <?php
                      $result_dir = $conexion->query("SELECT Id_Estado, nombre_estado FROM estado WHERE Id_Pais = 1");
                      while ($row_dir = $result_dir->fetch_assoc()) {
                        $selected = ($row_dir['Id_Estado'] == $id_estado_dir) ? 'selected' : '';
                        echo "<option value='{$row_dir['Id_Estado']}' {$selected}>{$row_dir['nombre_estado']}</option>";
                      }
                      ?>
                    </select>
                  </div>
                  <label class="control-label"></label>
                  <div class="col-sm-4">
                    <p>Municipio:</p>
                    <select name="municipio" id="municipio" class="form-control">
                      <option value="">--- Seleccione Un Municipio ---</option>
                      <?php
                      if ($id_estado_dir) {
                          $res_mun_dir = $conexion->query("SELECT Id_Municipio, nombre_municipio FROM municipio WHERE Id_Estado = $id_estado_dir");
                          while ($row_mun_dir = $res_mun_dir->fetch_assoc()) {
                              $selected = ($row_mun_dir['Id_Municipio'] == $id_municipio_dir) ? 'selected' : '';
                              echo "<option value='{$row_mun_dir['Id_Municipio']}' {$selected}>{$row_mun_dir['nombre_municipio']}</option>";
                          }
                      }
                      ?>
                    </select>
                  </div>
                  <label class="control-label"></label>
                  <div class="col-sm-3">
                    <p>Sector:</p>
                    <select name="sector" id="sector" class="form-control">
                      <option value="">--- Seleccione Un Sector ---</option>
                      <?php
                      if ($id_municipio_dir) {
                          $res_sec_dir = $conexion->query("SELECT Id_Sector, nombre_sector FROM sector WHERE Id_Municipio = $id_municipio_dir");
                          while ($row_sec_dir = $res_sec_dir->fetch_assoc()) {
                              $selected = ($row_sec_dir['Id_Sector'] == $id_sector_dir) ? 'selected' : '';
                              echo "<option value='{$row_sec_dir['Id_Sector']}' {$selected}>{$row_sec_dir['nombre_sector']}</option>";
                          }
                      }
                      ?>
                    </select>
                  </div>
                  <br><br><br><br>
                  <label class="control-label"></label>
                  <div class="col-sm-4">
                    <p>Avenida/calle:</p>
                    <input type="text" class="form-control" name="avenida_calle" value="<?php echo $row['avenida_calle']; ?>">
                  </div>
                  <label class="control-label"></label>
                  <div class="col-sm-4">
                    <p>Punto de referencia:</p>
                    <input type="text" class="form-control" name="referencia" value="<?php echo $row['referencia']; ?>">
                  </div>
                  <label class="control-label"></label>
                  <div class="col-sm-1">
                    <p>Tiempo:</p>
                    <input type="text" class="form-control" name="tiempo_residencia" id="tiempo_residencia" value="<?php echo $row['tiempo_residencia']; ?>">
                  </div>
                  <div class="col-sm-2">
                    <p>Dias/Meses/Etc:</p>
                    <select name="tiempo" id="tiempo" class="form-control">
                      <option value="dia/s" <?php echo ($row['tiempo'] == 'dia/s' ? 'selected' : ''); ?>>Dia/s</option>
                      <option value="semanas/s" <?php echo ($row['tiempo'] == 'semanas/s' ? 'selected' : ''); ?>>Semanas/s</option>
                      <option value="meses/s" <?php echo ($row['tiempo'] == 'meses/s' ? 'selected' : ''); ?>>Meses/s</option>
                      <option value="años/s" <?php echo ($row['tiempo'] == 'años/s' ? 'selected' : ''); ?>>Año/s</option>
                    </select>
                  </div>
                  <br><br><br><br>
                  <div style="float:right; margin-top:2%;">
                    <button type="button" class="btn btn-secondary prev-tab" data-tab-actual="#direccion" data-tab-anterior="info">Atras</button>
                    <button type="button" class="btn btn-success next-tab" data-tab-actual="#direccion" data-tab-siguiente="confirmar">Guardar</button>
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
  <?php
  // Se incluye el footer y los scripts base
  include('includes/footer.php');
  ?>

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
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Regresar</button>
          <a href="representantes_listado.php" class="btn btn-danger">Abandonar Formulario</a>
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
          <p>¿Está seguro de que desea actualizar la información del paciente?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Regresar</button>
          <button type="button" class="btn btn-success" id="confirmarGuardadoFinal">Guardar Cambios</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalPatologias" tabindex="-1" role="dialog" aria-labelledby="modalPatologiasLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: #286090; color: white;">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="modalPatologiasLabel"><i class="fa fa-stethoscope"></i> Seleccionar Patologías Conocidas</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-sm-12">
              <label for="lista_patologias_seleccionadas">Patologías en lista temporal:</label>
              <div id="lista_patologias_seleccionadas" class="well well-sm" style="min-height: 50px;">
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="modal_patologia">Patología disponible (*)</label>
              <select id="modal_patologia" class="form-control">
                <option value="disabled">--- Seleccione Una Patología ---</option>
                <?php
                // Conexión y consulta para patologías
                $result_patologia = $conexion->query("SELECT * FROM patologias");
                while ($row_patologia = $result_patologia->fetch_assoc()) {
                  echo "<option value='{$row_patologia['Id_patologia']}' data-nombre='{$row_patologia['nombre_patologia']}' data-cie='{$row_patologia['codigo_cie']}'>{$row_patologia['nombre_patologia']}</option>";
                }
                ?>
              </select>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="modal_codigo_cie">Código CIE-10 (Auto)</label>
              <input type="text" id="modal_codigo_cie" class="form-control" readonly value="No disponible">
            </div>
          </div>
          <div class="clearfix"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-success pull-right" id="btnAgregarPatologiaTemporal">
            <i class="fa fa-plus"></i> Guardar seleccion
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalNuevaPatologia" tabindex="-1" role="dialog" aria-labelledby="modalNuevaPatologiaLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: #337ab7; color: white;">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="modalNuevaPatologiaLabel"><i class="fa fa-plus"></i> Agregar Nueva Patología</h4>
        </div>
        <div class="modal-body">
          <form id="formNuevaPatologia" onsubmit="return false;">
            <div class="form-group">
              <label for="nuevo_nombre_patologia">Nombre de la Patología (*)</label>
              <input type="text" class="form-control" id="nuevo_nombre_patologia" required>
            </div>
            <div class="form-group">
              <label for="nuevo_codigo_cie">Código CIE-10 (*)</label>
              <input type="text" class="form-control" id="nuevo_codigo_cie" required>
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

  <div class="modal fade" id="modalAlergias" tabindex="-1" role="dialog" aria-labelledby="modalAlergiasLabel">
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
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-12">
              <div class="form-group">
                <label for="modal_alergia">Alergia disponible (*)</label>
                <select id="modal_alergia" class="form-control">
                  <option value="disabled">--- Seleccione Una Alergia ---</option>
                  <?php
                  // Conexión y consulta para alergias
                  $sql_alergia = $conexion->query("SELECT * FROM alergias_conocidas");
                  while ($resultado_alergia = $sql_alergia->fetch_assoc()) {
                    echo "<option value='" . $resultado_alergia["Id_alergias_conocidas"] . "' data-nombre='{$resultado_alergia['nombre_alergia']}'>" . $resultado_alergia['nombre_alergia'] . "</option>";
                  }
                  ?>
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-success" id="btnAgregarAlergiaTemporal">
            <i class="fa fa-plus"></i> Guardar seleccion
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalNuevaAlergia" tabindex="-1" role="dialog" aria-labelledby="modalNuevaAlergiaLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: #337ab7; color: white;">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="modalNuevaAlergiaLabel"><i class="fa fa-plus"></i> Agregar Nueva Alergia</h4>
        </div>
        <div class="modal-body">
          <form id="formNuevaAlergia" onsubmit="return false;">
            <div class="form-group">
              <label for="nuevo_nombre_alergia">Nombre de la Alergia (*)</label>
              <input type="text" class="form-control" id="nuevo_nombre_alergia" required>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-success" id="btnGuardarNuevaAlergiaAjax">Guardar Alergia</button>
        </div>
      </div>
    </div>
  </div>
  <script src="../../assets/js/jquery.min.js"></script>
  <script src="../../assets/js/bootstrap.min.js"></script>
  <script>
    // Variables PHP con los IDs guardados (Existentes en editar.php)
    const idPaciente = "<?php echo $id_representante; ?>"; // ID del paciente actual (Nuevo)
    const saved_id_pais_nac = "<?php echo $id_pais_nac; ?>";
    const saved_id_estado_nac = "<?php echo $id_estado_nac; ?>";
    const saved_id_municipio_nac = "<?php echo $id_municipio_nac; ?>";
    const saved_id_estado_dir = "<?php echo $id_estado_dir; ?>";
    const saved_id_municipio_dir = "<?php echo $id_municipio_dir; ?>";
    const saved_id_sector_dir = "<?php echo $id_sector_dir; ?>";

    // ------------------------------------------------------------------
    // FUNCIÓN DE AYUDA PARA MODALES (Copiada de pacientes_agregar.php)
    // ------------------------------------------------------------------
    function closeCustomModal($modal) {
      $modal.addClass('out');
      // Espera un poco para la animación de salida
      setTimeout(function() {
        $modal.modal('hide');
        $modal.removeClass('out');
      }, 400); // 400ms = duración de fadeOut
    }

    // Aplica la función de cierre personalizada a los botones de modal (Copiada de pacientes_agregar.php)
    $('.modal .close, .modal .btn-default[data-dismiss="modal"], .modal .btn-secondary[data-dismiss="modal"]').on('click', function(e) {
      const $modal = $(this).closest('.modal');
      if ($modal.length && $modal.hasClass('in')) {
        closeCustomModal($modal);
      }
    });

    // Cierre al hacer clic en el backdrop (parte oscura) (Copiada de pacientes_agregar.php)
    $('.modal').on('click', function(e) {
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

    $('#avisoModal .close, #avisoModal .btn-secondary').on('click', function() {
      closeCustomModal($('#avisoModal'));
    });

    $('#modalGuardarMedico .close, #modalGuardarMedico .btn-secondary').on('click', function() {
      closeCustomModal($('#modalGuardarMedico'));
    });


    // ------------------------------------------------------------------
    // LÓGICA GENERAL
    // ------------------------------------------------------------------

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

    function setMaxDateFor18Years() {
      const today = new Date();
      today.setFullYear(today.getFullYear() - 18);
      const maxDate = today.toISOString().split('T')[0];
      document.getElementById('fechaN').setAttribute('max', maxDate);
    }
    setMaxDateFor18Years();

    // Función para calcular edad (Copiada de pacientes_agregar.php)
    function calcularEdad() {
      const fechaNacimiento = document.getElementById("fechaN").value;
      if (!fechaNacimiento) {
        document.getElementById("edad").value = "";
        return;
      }
      const hoy = new Date();
      const cumple = new Date(fechaNacimiento);
      let edad = hoy.getFullYear() - cumple.getFullYear();
      const m = hoy.getMonth() - cumple.getMonth();
      if (m < 0 || (m === 0 && hoy.getDate() < cumple.getDate())) {
        edad--;
      }
      document.getElementById('edad').value = edad;
    }
    calcularEdad(); // Calcular la edad inicial al cargar


    // --- LÓGICA DE VALIDACIÓN DE CÉDULA --- (Adaptada de pacientes_agregar.php)
    const cedulaInput = document.getElementById('cedula');
    const tipoCedulaSelect = document.getElementById('tipo_cedula');

    // Capturar el ID del registro actual (ya está en un input hidden llamado 'Id' en tu HTML)
    const idActualRegistro = document.querySelector('input[name="Id"]').value;

    // --- 1. FUNCIÓN AJAX UNIFICADA (CÉDULA Y EMAIL) ---
async function verificarDatosUnicosBD(tipo, cedula, email, idPersona) {
  return new Promise((resolve) => {
    if (cedula === "" && email === "") {
      resolve({ existe_cedula: false, existe_email: false });
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
        resolve({ existe_cedula: false, existe_email: false });
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
          data: { Id_Pais: paisId },
          dataType: 'json',
          cache: false,
          success: function(data) {
            for (var i = 0; i < data.length; i++) {
              var estado = data[i];
              estadoSelect.innerHTML += '<option value="' + estado.Id_Estado + '">' + estado.nombre_estado + '</option>';
            }
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
          data: { Id_Estado: estadoId },
          dataType: 'json',
          cache: false,
          success: function(data) {
            for (var i = 0; i < data.length; i++) {
              var municipio = data[i];
              municipioSelect.innerHTML += '<option value="' + municipio.Id_Municipio + '">' + municipio.nombre_municipio + '</option>';
            }
          }
        });
      }
    });

    // 3. ESTADO RESIDENCIA (NORMAL) -> MUNICIPIO RESIDENCIA
    document.getElementById('estado').addEventListener('change', function() {
      var estadoId = this.value;
      var municipioSelect = document.getElementById('municipio');
      var sectorSelect = document.getElementById('sector');

      municipioSelect.innerHTML = '<option value="">--- Seleccione Un Municipio ---</option>';
      if (sectorSelect) {
        sectorSelect.innerHTML = '<option value="">--- Seleccione Un Sector ---</option>';
      }

      if (estadoId) {
        $.ajax({
          url: 'get/get_municipios.php',
          method: 'GET',
          data: { Id_Estado: estadoId },
          dataType: 'json',
          cache: false,
          success: function(data) {
            for (var i = 0; i < data.length; i++) {
              var municipio = data[i];
              municipioSelect.innerHTML += '<option value="' + municipio.Id_Municipio + '">' + municipio.nombre_municipio + '</option>';
            }
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
          data: { Id_Municipio: municipioId },
          dataType: 'json',
          cache: false,
          success: function(data) {
            for (var i = 0; i < data.length; i++) {
              var sector = data[i];
              sectorSelect.innerHTML += '<option value="' + sector.Id_Sector + '">' + sector.nombre_sector + '</option>';
            }
          }
        });
      }
    });

    // Inicializar Patologías y Alergias (Si borraste el DOMContentLoaded)
    document.addEventListener('DOMContentLoaded', () => {
      if (typeof parseData === 'function') {
        inicializarDatosMedicos();
      }
    });

    // Función de ayuda para parsear datos (Id::Nombre::CIE-10 o Id::Nombre)
    function parseData(dataString, separator, itemParser) {
      if (!dataString) return [];
      return dataString.split(separator).map(itemParser).filter(item => item);
    }

    // Parsear patologías (Id::Nombre::CIE-10)
    let patologiasSeleccionadas = [];
    // Parsear alergias (Id::Nombre)
    let alergiasSeleccionadas = [];


    function inicializarDatosMedicos() {
      // 1. Patologías
      patologiasSeleccionadas = parseData(patologiasData, '||', (item) => {
        const parts = item.split('::');
        if (parts.length === 3) {
          return {
            id: parts[0],
            nombre: parts[1],
            codigo_cie: parts[2]
          };
        }
        return null;
      });

      // 2. Alergias
      alergiasSeleccionadas = parseData(alergiasData, '||', (item) => {
        const parts = item.split('::');
        if (parts.length === 2) {
          return {
            id: parts[0],
            nombre: parts[1]
          };
        }
        return null;
      });

      // Actualizar la vista principal al cargar (Tab 4)
      updateMainDisplay();
    }


    // --- Funciones de Patologías ---
    // Actualiza el campo de Código CIE-10 dentro del modal al seleccionar una Patología
    document.getElementById('modal_patologia').addEventListener('change', function() {
      const patologiaId = this.value;
      const $selectedOption = $(this).find('option:selected');
      if (patologiaId && patologiaId !== 'disabled') {
        document.getElementById('modal_codigo_cie').value = $selectedOption.data('cie') || 'No disponible';
      } else {
        document.getElementById('modal_codigo_cie').value = 'No disponible';
      }
    });

    // Función para agregar una patología al listado temporal
    function addPatologia() {
      const patologiaSelect = document.getElementById('modal_patologia');
      const patologiaId = patologiaSelect.value;
      const $selectedOption = $(patologiaSelect).find('option:selected');

      if (!patologiaId || patologiaId === 'disabled') {
        mostrarAviso('Por favor, seleccione una patología válida.');
        return;
      }

      const nombre = $selectedOption.data('nombre');
      const codigo_cie = $selectedOption.data('cie');

      // Verificar duplicado
      const existe = patologiasSeleccionadas.some(p => p.id == patologiaId);
      if (existe) {
        mostrarAviso(`La patología '${nombre}' ya está en la lista.`);
        return;
      }

      patologiasSeleccionadas.push({
        id: patologiaId,
        nombre: nombre,
        codigo_cie: codigo_cie
      });
      renderPatologiasModal();
      updateMainDisplay(); // Actualizar la vista principal
      mostrarAviso(`✅ Patología '${nombre}' añadida temporalmente.`);
    }

    // Listener para el botón de añadir patología
    document.getElementById('btnAgregarPatologiaTemporal').addEventListener('click', addPatologia);

    // Función para renderizar la lista de patologías dentro del modal
    function renderPatologiasModal() {
      const lista = document.getElementById('lista_patologias_seleccionadas');
      lista.innerHTML = '';
      if (patologiasSeleccionadas.length === 0) {
        lista.innerHTML = '<span class="text-muted">Ninguna patología en la lista temporal.</span>';
        lista.classList.remove('well');
        lista.classList.remove('well-sm');
        return;
      }
      lista.classList.add('well');
      lista.classList.add('well-sm');

      patologiasSeleccionadas.forEach(p => {
        lista.innerHTML += `
            <span class="label label-primary" style="margin-right: 5px; margin-bottom: 5px; display: inline-block; font-size: 14px; padding: 6px 10px 6px 10px;;">
                ${p.nombre} (${p.codigo_cie})
                <a href="#" data-id="${p.id.toString()}" class="remove-patologia text-white" style="color: white; margin-left: 5px;">&times;</a>
            </span>`;
      });

      // Volver a agregar listener para eliminar
      lista.querySelectorAll('.remove-patologia').forEach(el => {
        el.addEventListener('click', removePatologia);
      });
    }

    // Función para remover una patología del listado temporal
    function removePatologia(e) {
      e.preventDefault();
      const idQuitar = e.target.getAttribute('data-id');
      const nombreQuitar = patologiasSeleccionadas.find(p => p.id == idQuitar)?.nombre || 'Patología';
      patologiasSeleccionadas = patologiasSeleccionadas.filter(p => p.id != idQuitar);
      renderPatologiasModal();
      updateMainDisplay(); // Actualizar la vista principal
      mostrarAviso(`Patología '${nombreQuitar}' eliminada.`);
    }

    // Inicialización: Llama a la función al abrir el modal de patologías
    $('#modalPatologias').on('show.bs.modal', function() {
      renderPatologiasModal();
    });

    // --- Funciones de Alergias ---
    // Función para agregar una alergia al listado temporal
    function addAlergia() {
      const alergiaSelect = document.getElementById('modal_alergia');
      const alergiaId = alergiaSelect.value;
      const $selectedOption = $(alergiaSelect).find('option:selected');

      if (!alergiaId || alergiaId === 'disabled') {
        mostrarAviso('Por favor, seleccione una alergia válida.');
        return;
      }

      const nombre = $selectedOption.data('nombre');

      // Verificar duplicado
      const existe = alergiasSeleccionadas.some(a => a.id == alergiaId);
      if (existe) {
        mostrarAviso(`La alergia '${nombre}' ya está en la lista.`);
        return;
      }

      alergiasSeleccionadas.push({
        id: alergiaId,
        nombre: nombre
      });
      renderAlergiasModal();
      updateMainDisplay(); // Actualizar la vista principal
      mostrarAviso(`✅ Alergia '${nombre}' añadida temporalmente.`);
    }

    // Listener para el botón de añadir alergia
    document.getElementById('btnAgregarAlergiaTemporal').addEventListener('click', addAlergia);

    // Función para renderizar la lista de alergias dentro del modal
    function renderAlergiasModal() {
      const lista = document.getElementById('lista_alergias_seleccionadas');
      lista.innerHTML = '';

      if (alergiasSeleccionadas.length === 0) {
        lista.innerHTML = '<span class="text-muted">Ninguna alergia en la lista temporal.</span>';
        return;
      }

      alergiasSeleccionadas.forEach(a => {
        lista.innerHTML += `
            <span class="label label-primary" style="margin-right: 5px; margin-bottom: 5px; display: inline-block; font-size: 14px; padding: 6px 10px 6px 10px;">
                ${a.nombre}
                <a href="#" data-id="${a.id.toString()}" class="remove-alergia text-white" style="color: white; margin-left: 5px;">&times;</a>
            </span>`;
      });

      // Volver a agregar listener para eliminar
      lista.querySelectorAll('.remove-alergia').forEach(el => {
        el.addEventListener('click', removeAlergia);
      });
    }

    // Función para remover una alergia del listado temporal
    function removeAlergia(e) {
      e.preventDefault();
      const idQuitar = e.target.getAttribute('data-id');
      const nombreQuitar = alergiasSeleccionadas.find(a => a.id == idQuitar)?.nombre || 'Alergia';
      alergiasSeleccionadas = alergiasSeleccionadas.filter(a => a.id != idQuitar);
      renderAlergiasModal();
      updateMainDisplay(); // Actualizar la vista principal
      mostrarAviso(`Alergia '${nombreQuitar}' eliminada.`);
    }

    // Inicialización: Llama a la función al abrir el modal de alergias
    $('#modalAlergias').on('show.bs.modal', function() {
      renderAlergiasModal();
    });


    // --- Función Unificada de Actualización de Pantalla Principal ---
    function updateMainDisplay() {
      // --- Patologías ---
      const patologiasIds = patologiasSeleccionadas.map(p => p.id).join(',');
      document.getElementById('patologias_ids').value = patologiasIds;

      const patologiasDiv = document.getElementById('patologias_agregadas');
      patologiasDiv.innerHTML = '';

      if (patologiasSeleccionadas.length === 0) {
        patologiasDiv.innerHTML = '<span class="text-muted">Ninguna patología seleccionada.</span>';
      } else {
        // Mostrar un resumen o todos, según el estilo de agregar.php
        patologiasSeleccionadas.forEach(p => {
          patologiasDiv.innerHTML += `
                <span class="label label-primary" style="margin-right: 5px; margin-bottom: 5px; display: inline-block;">
                    ${p.nombre}
                </span>`;
        });
      }

      // --- Alergias ---
      const alergiasIds = alergiasSeleccionadas.map(a => a.id).join(',');
      document.getElementById('alergias_ids').value = alergiasIds;

      const alergiasDiv = document.getElementById('alergias_agregadas');
      alergiasDiv.innerHTML = '';

      if (alergiasSeleccionadas.length === 0) {
        alergiasDiv.innerHTML = '<span class="text-muted">Ninguna alergia seleccionada.</span>';
      } else {
        // Mostrar un resumen o todos, según el estilo de agregar.php
        alergiasSeleccionadas.forEach(a => {
          alergiasDiv.innerHTML += `
                <span class="label label-primary" style="margin-right: 5px; margin-bottom: 5px; display: inline-block;">
                    ${a.nombre}
                </span>`;
        });
      }
    }


    // --- LÓGICA AJAX PARA GUARDAR NUEVA PATOLOGÍA (Copiada de pacientes_agregar.php) ---
    $('#btnGuardarNuevaPatologiaAjax').click(function() {
      const nombre = $('#nuevo_nombre_patologia').val().trim();
      const codigo = $('#nuevo_codigo_cie').val().trim();

      if (nombre === '' || codigo === '') {
        mostrarAviso('El nombre y el código CIE-10 son obligatorios.');
        return;
      }

      // Simulación de guardado (en un entorno real aquí iría la llamada AJAX a un script: /cfg/guardar_patologia.php)
      // Por ahora cerramos el modal y mostramos aviso
      $('#modalNuevaPatologia').modal('hide');
      mostrarAviso('✅ Patología guardada (Simulación).');
      // En un entorno real, se debería actualizar el select de patologías.
    });

    // --- LÓGICA AJAX PARA GUARDAR NUEVA ALERGIA (Copiada de pacientes_agregar.php) ---
    $('#btnGuardarNuevaAlergiaAjax').click(function() {
      const nombre = $('#nuevo_nombre_alergia').val().trim();
      if (nombre === '') {
        mostrarAviso('El nombre es obligatorio.');
        return;
      }
      // Simulación de guardado
      $('#modalNuevaAlergia').modal('hide');
      mostrarAviso('✅ Alergia guardada (Simulación).');
      // En un entorno real, se debería actualizar el select de alergias.
    });

    // --- MODAL DE AVISO GENÉRICO (Copiado de pacientes_agregar.php) ---
    function mostrarAviso(mensaje) {
      $('#avisoTexto').html(mensaje);
      $('#avisoModal').modal('show');
    }


    // =====================================================================
    // VALIDACIÓN PRINCIPAL DE CADA PESTAÑA (ASÍNCRONA) (Copiada de pacientes_agregar.php)
    // =====================================================================
    function limpiarErroresPestana(tabSelector) {
      $(tabSelector).find('.input-error').removeClass('input-error');
      // También ocultar errores específicos como el de cédula duplicada
      if (tabSelector === '#info') {}
    }

    async function validarPestana(tabSelector) {
      let esValido = true;
      let errores = [];
      limpiarErroresPestana(tabSelector);

      // 1. Validación de campos obligatorios (*)
      $(tabSelector).find('[required]').each(function() {
        var $input = $(this);
        var valor = $input.val();

        // Solo validar si el campo está visible y no deshabilitado
        if ($input.is(':visible') && !$input.prop('disabled')) {
          if (valor === null || valor.trim() === "" || valor === '--- Seleccione Un Genero ---' || valor === '--- Seleccione Un Estado ---' || valor === '--- Seleccione Un Municpio ---' || valor === '--- Seleccione Una Parroquia ---' || valor === '--- Seleccione Un Sector ---' || valor === '--- Seleccione Una Opcion ---') {
            $input.addClass('input-error');
            esValido = false;
            if ($input.attr('name') === 'cedula') {
              errores.push("El campo de Cédula es obligatorio.");
            } else if ($input.attr('name') === 'fecha_nacimiento') {
              errores.push("El campo de Fecha de nacimiento es obligatorio.");
            } else {
              errores.push(`El campo '${$input.prev('p').text().replace('(*):', '').trim()}' es obligatorio.`);
            }
          }
        }
      });


      // 2. Validaciones Específicas por Pestaña
      if (tabSelector === '#info') {
        // 2a. Validación de Cédula (Formato y Duplicado en BD)
        if (validarDatosUnicos() === false) {
          esValido = false;
          errores.push('Error en el formato o la cédula ya existe.');
        }

        // 2b. Validación de Edad Mínima (Ejemplo: 18 años)
        const fechaNac = document.getElementById("fechaN").value;
        if (fechaNac) {
          const edad = parseInt(document.getElementById('edad').value);
          if (isNaN(edad) || edad < 1) {
            $('#fechaN').addClass('input-error');
            esValido = false;
            errores.push("La edad debe ser mayor a 0.");
          }
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

      if (!esValido && errores.length > 0) {
        mostrarAviso('⚠️ Errores de Formulario:<ul><li>' + errores.join('</li><li>') + '</li></ul>');
      }

      return esValido;
    }

    // --- 3. BOTÓN SIGUIENTE Y VALIDACIÓN FINAL ---
    $('.next-tab').off('click').on('click', async function() {
      const $btn = $(this);
      const tabActualSelector = $btn.data('tab-actual');
      const tabSiguienteName = $btn.data('tab-siguiente');
      const nextTabLink = $(`.nav-tabs li[data-tab-name="${tabSiguienteName}"] a`);

      $btn.prop('disabled', true).text('Validando...');

      const esValido = await validarPestana(tabActualSelector);

      $btn.prop('disabled', false).text(tabSiguienteName === 'confirmar' ? 'Actualizar' : 'Siguiente');

      if (esValido) {
        if (tabSiguienteName === 'confirmar') {
          // ULTIMA VERIFICACIÓN ANTES DE MOSTRAR EL MODAL
          const cedulaFinalValida = await validarDatosUnicos();
          if(!cedulaFinalValida) {
             mostrarAviso("🛑 Conflicto de Cédula. Regrese a la pestaña 'Datos Personales' y corrija la información.");
             return;
          }
          
          const modalSelector = $('#modalGuardarPaciente').length ? '#modalGuardarPaciente' : '#modalGuardarMedico';
          $(modalSelector).modal('show');
        } else {
          $('.nav-tabs li').removeClass('active');
          $('.tab-content .tab-pane').removeClass('active');
          $(`.nav-tabs li[data-tab-name="${tabSiguienteName}"]`).removeClass('disabled-tab').addClass('active');
          nextTabLink.tab('show');
          $('#' + tabSiguienteName).addClass('active');
        }
      }
    });

    $('.prev-tab').on('click', function() {
      const $btn = $(this);
      const tabActualSelector = $btn.data('tab-actual');
      const tabAnteriorName = $btn.data('tab-anterior');

      if (tabAnteriorName) {
        const prevTabLink = $(`.nav-tabs li[data-tab-name="${tabAnteriorName}"] a`);
        const $anteriorTabLi = $(`.nav-tabs li[data-tab-name="${tabAnteriorName}"]`);

        // 1. Quitar la clase active de la pestaña actual
        $('.nav-tabs li').removeClass('active');
        $('.tab-content .tab-pane').removeClass('active');

        // 2. Activar la pestaña anterior
        $anteriorTabLi.addClass('active');
        prevTabLink.tab('show');
        $('#' + tabAnteriorName).addClass('active');

        // Opcional: Re-deshabilitar la pestaña a la que se regresa, si se desea.
        // En modo edición, las pestañas deberían quedar desbloqueadas una vez visitadas.
      } else {
        // En la primera pestaña, el botón Regresar dispara el modal de confirmación de abandono
        $('#modalConfirmarRegreso').modal('show');
      }
    });

    // --- Lógica Final de Guardado (Adaptada de pacientes_agregar.php) ---
    $('#confirmarGuardadoFinal').on('click', function() {
      $('#modalGuardarMedico').modal('hide');
      // El action del formulario ya apunta a ../../cfg/editar_paciente.php
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

</html>