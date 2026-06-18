<?php
if (isset($_POST['ajax_nueva_patologia'])) {
  $conexion = new mysqli("localhost", "root", "", "cpt3db");
  $conexion->set_charset("utf8");
  $nombre = $conexion->real_escape_string($_POST['nombre_patologia']);
  $cie = $conexion->real_escape_string($_POST['codigo_cie']);
  $contagiosa = $conexion->real_escape_string($_POST['enfermedad_contagiosa']);

  // 1. VERIFICAR SI YA EXISTE LA PATOLOGÍA
  $verificar = $conexion->query("SELECT Id_patologia FROM patologias WHERE nombre_patologia = '$nombre'");
  if ($verificar->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Ya existe una patología registrada con el nombre: <b>' . $nombre . '</b>']);
    exit;
  }

  // 2. Insertar la patología
  $conexion->query("INSERT INTO patologias (nombre_patologia, codigo_cie, estatus, contagioso) VALUES ('$nombre', '$cie', 1, '$contagiosa')");
  $id_pat = $conexion->insert_id;

  // 3. Insertar los síntomas relacionados
  if (isset($_POST['sintomas_ids']) && !empty($_POST['sintomas_ids'])) {
    $sintomas = explode(',', $_POST['sintomas_ids']);
    foreach ($sintomas as $id_sintoma) {
      if (trim($id_sintoma) !== "") {
        $id_sin = $conexion->real_escape_string($id_sintoma);
        $conexion->query("INSERT INTO detalle_patologia_sintoma (Id_patologia, Id_sintoma) VALUES ('$id_pat', '$id_sin')");
      }
    }
  }

  echo json_encode(['success' => true, 'id' => $id_pat, 'nombre' => $nombre, 'cie' => $cie]);
  exit;
}

// --- AJAX PARA NUEVA ALERGIA ---
if (isset($_POST['ajax_nueva_alergia'])) {
  $conexion = new mysqli("localhost", "root", "", "cpt3db");
  $conexion->set_charset("utf8");
  $nombre = $conexion->real_escape_string($_POST['nombre_alergia']);

  // 1. VERIFICAR SI YA EXISTE LA ALERGIA
  $verificar = $conexion->query("SELECT Id_alergias_conocidas FROM alergias_conocidas WHERE nombre_alergia = '$nombre'");
  if ($verificar->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Ya existe una alergia registrada con el nombre: <b>' . $nombre . '</b>']);
    exit;
  }

  // 2. Insertar la alergia
  $conexion->query("INSERT INTO alergias_conocidas (nombre_alergia, estatus) VALUES ('$nombre', 1)");
  echo json_encode(['success' => true, 'id' => $conexion->insert_id, 'nombre' => $nombre]);
  exit;
}
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Pacientes Menores | Añadir</title>
  <?php
  include('includes/headerNav2.php');
  $redireccion = isset($_GET['pagina']) ? $_GET['pagina'] : 'ninguna';
  $opciones_sintomas_global = "";
  $conexion = new mysqli("localhost", "root", "", "cpt3db");
  $conexion->set_charset("utf8");
  $sql_sg = "SELECT Id_sintomas, nombre_sintoma FROM sintomas WHERE estatus = 1 ORDER BY nombre_sintoma ASC";
  $res_sg = $conexion->query($sql_sg);
  if ($res_sg) {
    while ($row = $res_sg->fetch_assoc()) {
      $id = $row['Id_sintomas'];
      $nombre = htmlspecialchars($row['nombre_sintoma']);
      $opciones_sintomas_global .= "<option value='$id'>$nombre</option>";
    }
  }
  // --------------------------------------------------------
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

    /* FIX: Marcar en rojo cuando está vacío y es requerido */
    /* Se usa :invalid para cuando es requerido y su valor es vacío */
    input[required]:invalid:focus:not(:focus) {
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
        Añadir Paciente Menor de Edad
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-users"></i>Pacientes Menores</a></li>
        <li class="active"><a href="#"><i class="fa fa-user-plus"></i>Añadir</a></li>
      </ol>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active" data-tab-name="info"><a href="#info" data-toggle="tab">Datos Personales</a></li>
              <li data-tab-name="representante" class="disabled-tab"><a href="#representante" data-toggle="tab">Datos del Representante</a></li>
              <li data-tab-name="ocupacion_estudios" class="disabled-tab"><a href="#ocupacion_estudios" data-toggle="tab">Estudios Aprobados</a></li>
              <li data-tab-name="direccion" class="disabled-tab"><a href="#direccion" data-toggle="tab">Dirección de Residencia</a></li>
              <li data-tab-name="salud_otros" class="disabled-tab"><a href="#salud_otros" data-toggle="tab">Salud y Otros Datos</a></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane active" id="info">
                <form id="formularioPaciente" action="../../cfg/agregar/agregar_paciente_menor.php" class="form-group" method="POST" novalidate>
                  <input type="hidden" name="redireccion" id="redireccion" value="<?php echo $redireccion; ?>">
                  <section id="new" style="margin-bottom:-3%;">
                    <label class="control-label"></label>
                    <div class="col-sm-1 pull-left" style="margin-top: 30px;">
                      <select name="tipo_cedula" id="tipo_cedula" class="form-control" style="width: 60px;" required>
                        <option value="PN">PN-</option>
                        <option value="V">V-</option>
                        <option value="RP">REP-</option>
                        <!--<option value="E">E-</option>-->
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
                      <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#modalConfirmarRegreso">Regresar</button>
                      <button type="button" class="btn btn-primary next-tab" data-tab-actual="#info" data-tab-siguiente="representante">Siguiente</button>
                    </div>
                  </section>
              </div>

              <div class="tab-pane" id="representante">
                <section id="new" style="margin-bottom:8%;">
                  <div class="col-sm-12">
                    <h4>Información del Representante Legal/Responsable</h4>
                  </div>
                  <label class="control-label"></label>
                  <div class="col-sm-1 pull-left" style="margin-top: 30px;">
                    <select name="tipo_cedula_rep" id="tipo_cedula_rep" class="form-control" style="width: 60px;" required>
                      <option value="V">V-</option>
                      <!--<option value="E">E-</option>-->
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
                  <div class="col-sm-4">
                    <p>Relación con el Paciente (*):</p>
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
                  <div style="float:right; margin-top:7%;">
                    <button type="button" class="btn btn-secondary prev-tab" data-tab-anterior="#info">Atrás</button>
                    <button type="button" class="btn btn-primary next-tab" data-tab-actual="#representante" data-tab-siguiente="ocupacion_estudios">Siguiente</button>
                  </div>
                </section>
              </div>

              <div class="tab-pane" id="ocupacion_estudios">
                <section id="new" style="margin-bottom:13%;">
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
                    <button type="button" class="btn btn-primary next-tab" data-tab-actual="#ocupacion_estudios" data-tab-siguiente="direccion">Siguiente</button>
                  </div>
                </section>
              </div>
              <div class="tab-pane" id="direccion">
                <section id="new" style="margin-bottom:7%;">
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
                    <button type="button" class="btn btn-secondary prev-tab" data-tab-anterior="#ocupacion_estudios">Atrás</button>
                    <button type="button" class="btn btn-primary next-tab" data-tab-actual="#direccion" data-tab-siguiente="salud_otros">Siguiente</button>
                  </div>
                </section>
              </div>
              <div class="tab-pane" id="salud_otros">
                <section id="new" style="margin-bottom:11%;">
                  <label class="control-label"></label>
                  <div class="col-sm-4 form-group" id="group_patologia">
                    <p>Patologías detectadas previamante:</p>
                    <button type="button" class="btn btn-info btn-block" id="btn_modal_pat" data-toggle="modal" data-placement="top" title="Ninguna seleccionada" data-target="#modalPatologias">
                      <i></i> Gestionar Patologías
                    </button>
                    <input type="hidden" name="patologias_data" id="patologias_ids" value="">
                  </div>

                  <label class="control-label"></label>
                  <div class="col-sm-4 form-group" id="group_alergia">
                    <p>Alergías detectadas previamante:</p>
                    <button type="button" class="btn btn-info btn-block" id="btn_modal_ale" data-toggle="modal" data-placement="top" title="Ninguna seleccionada" data-target="#modalAlergias">
                      <i></i> Gestionar Alergias
                    </button>
                    <input type="hidden" name="alergias_data" id="alergias_ids" value="">
                  </div>
                  <label class="control-label"></label>
                  <div class="col-sm-3">
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
                  <br><br><br><br><br>
                  <label class="control-label"></label>
                  <div class="col-sm-1">
                    <p>Discap.:</p>
                    <select name="discapacidad" id="discapacidad" class="form-control" required>
                      <option value="No">No</option>
                      <option value="Si">Si</option>
                    </select>
                  </div>
                  <div class="col-sm-3">
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
                  <div style="float:right; margin-top:6%;">
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

  <div class="modal" id="modalPatologias" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: #3c8dbc; color: white;">
          <h4 class="modal-title"><i class="fa fa-medkit"></i> Gestionar Patologías</h4>
        </div>
        <div class="modal-body">
          <div id="contenedor_filas_patologias"></div>
          <button type="button" class="btn btn-success btn-sm" id="add_fila_pat">
            <i class="fa fa-plus"></i> Añadir otra
          </button>
          <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalNuevaPatologia">
            <i class="fa fa-plus-circle"></i> Nueva Patología
          </button>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="guardar_pat_listo">Listo</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalBuscarPat" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
      <div class="modal-content">
        <div class="modal-header bg-info" style="background-color: #3c8dbc; color: white;">
          <h4 class="modal-title">Buscar Patología</h4>
        </div>
        <div class="modal-body">
          <input type="text" id="inputBuscarPat" class="form-control" placeholder="Escriba para filtrar...">
          <div class="list-group" id="listaResultadosPat" style="margin-top: 10px; max-height: 200px; overflow-y: auto;"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalAlergias" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: #3c8dbc; color: white;">
          <h4 class="modal-title"><i class="fa fa-wheelchair"></i> Gestionar Alergias</h4>
        </div>
        <div class="modal-body">
          <div id="contenedor_filas_alergias"></div>
          <button type="button" class="btn btn-success btn-sm" id="add_fila_ale">
            <i class="fa fa-plus"></i> Añadir otra
          </button>
          <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalNuevaAlergia">
            <i class="fa fa-plus-circle"></i> Nueva Alergia
          </button>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="guardar_ale_listo">Listo</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalBuscarAle" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header bg-info" style="background-color: #3c8dbc; color: white;">
          <h4 class="modal-title">Buscar Alergia</h4>
        </div>
        <div class="modal-body">
          <input type="text" id="inputBuscarAle" class="form-control" placeholder="Escriba para filtrar...">
          <div class="list-group" id="listaResultadosAle" style="margin-top: 10px; max-height: 200px; overflow-y: auto;"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalNuevaPatologia" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: #3c8dbc; color: white;">
          <h4 class="modal-title"><i class="fa fa-plus-circle"></i> Nueva Patología</h4>
        </div>
        <div class="modal-body">
          <form id="formAjaxPatologia">
            <div class="form-group">
              <label>Nombre de la Patología (*):</label>
              <input type="text" class="form-control" id="ajax_nombre_pat" required>
            </div>
            <div class="form-group">
              <label>Código CIE (Opcional):</label>
              <input type="text" class="form-control" id="ajax_cie_pat">
            </div>
            <div class="form-group">
              <label>¿Es contagiosa?</label>
              <select class="form-control" id="ajax_contagiosa_pat">
                <option value="NO">NO</option>
                <option value="SI">SI</option>
              </select>
            </div>
            <div class="form-group">
              <label>Síntomas (*):</label>
              <button type="button" class="btn btn-info btn-block" id="btn_abrir_sintomas_ajax" data-toggle="tooltip" title="Ninguno seleccionado">
                <i><img src="../../recursos/imagenes/iconos/buscar.png" style="width:15px; height:15px; filter: invert(1);"></i> Gestionar Síntomas
              </button>
              <input type="hidden" id="ajax_sintomas_ids" value="">
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary" id="btn_guardar_ajax_pat">Guardar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalSintomasAjax" role="dialog" style="z-index: 1060;">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: #3c8dbc; color: white;">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title"><i class="fa fa-list"></i> Seleccionar Síntomas</h4>
        </div>
        <div class="modal-body">
          <div id="contenedor_filas_sintomas_ajax"></div>
          <button type="button" class="btn btn-success btn-sm pull-left" id="add_fila_sintoma_ajax">
            <i class="fa fa-plus"></i> Añadir otro
          </button>
          <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalNuevoSintoma" style="margin-left:5px;">
            <i class="fa fa-star"></i> Nuevo Síntoma
          </button>
          <div style="clear: both;"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="btn_confirmar_sintomas_ajax">Confirmar Síntomas</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalNuevoSintoma" role="dialog" style="z-index: 1070;">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: #3c8dbc; color: white;">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title">Crear Nuevo Síntoma</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Nombre del Síntoma</label>
            <input type="text" id="nombre_nuevo_sintoma" class="form-control" placeholder="Ej. Dolor de cabeza">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-success" id="btnGuardarSintomaBD">Guardar y Seleccionar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalBuscarSintoma" role="dialog" style="z-index: 1070;">
    <div class="modal-dialog modal-sm" role="document">
      <div class="modal-content">
        <div class="modal-header bg-info" style="background-color: #3c8dbc; color: white;">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title">Buscar Síntoma</h4>
        </div>
        <div class="modal-body">
          <input type="text" id="inputBuscarSintoma" class="form-control" placeholder="Escriba para filtrar...">
          <div class="list-group" id="listaResultadosSintoma" style="margin-top: 10px; max-height: 200px; overflow-y: auto;"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalNuevaAlergia" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: #3c8dbc; color: white;">
          <h4 class="modal-title"><i class="fa fa-plus-circle"></i> Nueva Alergia</h4>
        </div>
        <div class="modal-body">
          <form id="formAjaxAlergia">
            <div class="form-group">
              <label>Nombre de la Alergia (*):</label>
              <input type="text" class="form-control" id="ajax_nombre_ale" required>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary" id="btn_guardar_ajax_ale">Guardar</button>
        </div>
      </div>
    </div>
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
          <h4 class="modal-title" id="modalGuardarMedicoLabel"><i class="fa fa-save"></i> Confirmacion de Guardado </h4>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea guardar la información del nuevo paciente menor de edad?</p>
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
          <a href="pacientes_menores_listado.php" class="btn btn-danger">Abandonar Formulario</a>
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

    // --- 1. LÓGICA DE CALENDARIOS Y BLOQUEO DE TECLADO ---
    $(document).ready(function() {
      let fechaActualInit = new Date();
      let fechaLimite18 = new Date();
      fechaLimite18.setFullYear(fechaActualInit.getFullYear() - 18);
      let maxFechaAdulto = fechaLimite18.toISOString().split('T')[0];

      let minFechaMenorObj = new Date(fechaLimite18);
      minFechaMenorObj.setDate(minFechaMenorObj.getDate() + 1);
      let minFechaMenor = minFechaMenorObj.toISOString().split('T')[0]; 
      let hoyActual = fechaActualInit.toISOString().split('T')[0];

      // Aplicar límites
      $('#fechaN').attr('min', minFechaMenor).attr('max', hoyActual);
      $('#fechaN_rep').attr('max', maxFechaAdulto);

      // Bloquear teclado
      $('#fechaN, #fechaN_rep').on('keydown keypress keyup', function(e) {
        if (e.which !== 9) { e.preventDefault(); return false; }
      });
    });

    // --- 2. CÁLCULO DE EDAD DETALLADO (DÍAS, MESES, AÑOS) ---
    function calcularEdad() {
      let fechaNacStr = $('#fechaN').val();
      if (!fechaNacStr) { $('#edad').val(''); return; }
      
      let cumple = new Date(fechaNacStr + 'T00:00:00');
      let ahora = new Date();
      
      let anios = ahora.getFullYear() - cumple.getFullYear();
      let meses = ahora.getMonth() - cumple.getMonth();
      let dias = ahora.getDate() - cumple.getDate();
      
      if (dias < 0) {
        let mesAnterior = new Date(ahora.getFullYear(), ahora.getMonth(), 0);
        dias += mesAnterior.getDate();
        meses--;
      }
      if (meses < 0) { meses += 12; anios--; }
      
      let texto = '';
      if (anios >= 1) {
        texto = anios + (anios === 1 ? " año" : " años");
      } else if (meses >= 1) {
        texto = meses + (meses === 1 ? " mes" : " meses");
      } else {
        texto = dias + (dias === 1 ? " día" : " días");
      }
      $('#edad').val(texto);
    }

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
     
    function restringirSoloNumeros(e) {
      this.value = this.value.replace(/[^0-9]/g, '');
    }

    // Aplicar filtro dinámico de caracteres y longitud para Cédula/PN del Paciente
    if (cedulaInput) cedulaInput.addEventListener('input', filtrarCedulaPorTipo);
    if (tipoCedulaSelect) tipoCedulaSelect.addEventListener('change', filtrarCedulaPorTipo);

    // Aplicar restricción de solo números a Cédula Rep. y Teléfono
    if (cedulaRepInput) cedulaRepInput.addEventListener('input', restringirSoloNumeros);
    if (telefonoRepInput) telefonoRepInput.addEventListener('input', restringirSoloNumeros);

    function filtrarCedulaPorTipo() {
      const tipo = tipoCedulaSelect.value;
      const cedulaInput = document.getElementById('cedula');
      let valor = cedulaInput.value;
      let nuevoValor = valor;
      let longitudMaxima = 0;

      if (tipo === 'V' || tipo === 'E') {
        nuevoValor = valor.replace(/[^0-9]/g, '');
        longitudMaxima = 8;
      } else if (tipo === 'PN') {
        nuevoValor = valor.replace(/[^0-9]/g, '');
        longitudMaxima = 20;
      } else if (tipo === 'RP') {
        nuevoValor = valor.replace(/[^0-9]/g, '');
        if (nuevoValor.length > 8) {
          nuevoValor = nuevoValor.substring(0, 8) + '-' + nuevoValor.substring(8, 9);
        }
        longitudMaxima = 10; // 8 números + guion + 1 número
      } else {
        nuevoValor = valor;
        longitudMaxima = 20;
      }

      if (longitudMaxima > 0) cedulaInput.maxLength = longitudMaxima;
      cedulaInput.value = nuevoValor;

      if (cedulaRepInput) cedulaRepInput.value = cedulaRepInput.value.replace(/[^0-9]/g, '');
      if (telefonoRepInput) telefonoRepInput.value = telefonoRepInput.value.replace(/[^0-9]/g, '');
    }

    // Llamar a la función una vez para establecer el estado inicial (maxLength y filtro)
    if (cedulaInput && tipoCedulaSelect) filtrarCedulaPorTipo();

    // Detectamos si estamos en "Editar" leyendo el input oculto 'Id'. Si no existe (Agregar), enviamos 0.
    const inputIdMenor = document.querySelector('input[name="Id"]');
    const idActualMenor = inputIdMenor ? inputIdMenor.value : 0;

    // --- 1. FUNCIÓN AJAX PARA VERIFICAR EN BD (Soporta Agregar y Editar) ---
    async function verificarCedulaEnBD(tipo, cedula) {
      return new Promise((resolve, reject) => {
        if (cedula === "" || tipo === "") {
          resolve(false);
          return;
        }

        $.ajax({
          url: 'get/verificar_existencia_cedula.php',
          method: 'POST',
          dataType: 'json',
          data: {
            tipo_cedula: tipo,
            cedula: cedula,
            id_persona: idActualMenor // Envia 0 si es agregar, o el ID actual si es editar
          },
          success: function(response) {
            resolve(response.existe);
          },
          error: function(xhr, status, error) {
            console.error("Error al verificar documento.", status, error);
            resolve(false);
          }
        });
      });
    }

    // --- 2. VALIDACIÓN ESTRICTA (Soporta Documentos PN Alfanuméricos) ---
    async function validarCedulaFormato() {
      const cedulaInput = document.getElementById('cedula');
      const tipoCedulaSelect = document.getElementById('tipo_cedula');
      const valorCedula = cedulaInput.value.trim();
      const cedulaNumerica = parseInt(valorCedula);
      const tipo = tipoCedulaSelect.value;
      const longitud = valorCedula.length;
      let esValido = true;

      $(cedulaInput).removeClass('input-error');
      $(tipoCedulaSelect).removeClass('input-error');

      if (valorCedula === "") {
        $(cedulaInput).addClass('input-error');
        return false;
      }

      // Validaciones de longitud según tipo
      if (tipo === 'V' || tipo === 'E') {
        if (longitud < 2 || longitud > 8) {
          $(cedulaInput).addClass('input-error');
          mostrarAviso('🛑 Error de Longitud: El documento debe tener entre 2 y 8 dígitos.');
          return false;
        }
        if (tipo === 'V' && cedulaNumerica > 80000000) {
          $(cedulaInput).addClass('input-error');
          mostrarAviso('🛑 Error: La cédula no puede ser mayor a 80.000.000');
          return false;
        }
      } else if (tipo === 'PN') {
        if (longitud < 2 || longitud > 20) {
          $(cedulaInput).addClass('input-error');
          mostrarAviso('🛑 Error de Longitud: El documento PN debe tener entre 2 y 20 caracteres.');
          return false;
        }
      } else if (tipo === 'RP') {
        var regexRP = /^[0-9]{8}-[0-9]{1}$/;
        if (!regexRP.test(valorCedula)) {
          $(cedulaInput).addClass('input-error');
          mostrarAviso("🛑 Formato incorrecto. Para el documento de Representante (REP), debe colocar los 8 números de la cédula seguidos del número de hijo.<br><br>Ejemplo: <b>12345678-1</b>");
          return false;
        }
      }

      // Validación Prioritaria en Base de Datos
      try {
        const documentoVerificar = (tipo === 'V' || tipo === 'E') ? cedulaNumerica : valorCedula;
        const existe = await verificarCedulaEnBD(tipo, documentoVerificar);

        if (existe) {
          $(cedulaInput).addClass('input-error');
          $(tipoCedulaSelect).addClass('input-error');
          mostrarAviso('🛑 <b>Documento Existente:</b> El documento ' + tipo + '-' + valorCedula + ' ya se encuentra registrado. No puede continuar.');
          esValido = false;
        }
      } catch (error) {
        esValido = false;
      }

      return esValido;
    }

    if (document.getElementById('cedula')) document.getElementById('cedula').addEventListener('blur', validarCedulaFormato);
    if (document.getElementById('tipo_cedula')) document.getElementById('tipo_cedula').addEventListener('change', validarCedulaFormato);

    // FUNCIÓN PARA VERIFICAR EN BD (Retorna Promesa)
    async function verificarCedulaRepEnBD(tipo, cedula) {
      return new Promise((resolve) => {
        $.ajax({
          url: 'get/get_representante.php',
          method: 'POST',
          dataType: 'json',
          data: {
            tipo_cedula: tipo,
            cedula: cedula
          },
          success: function(data) {
            resolve(data);
          },
          error: function() {
            resolve({
              existe: false
            });
          }
        });
      });
    }

    function alternarCamposRepresentante(bloquear) {
      $('#nombre_rep').prop('readonly', bloquear);
      $('#fechaN_rep').prop('readonly', bloquear);
      $('#apellido_rep').prop('readonly', bloquear);
      $('#telefono_rep').prop('readonly', bloquear);
      $('#email_rep').prop('readonly', bloquear);

      $('#genero_rep').prop('disabled', bloquear);
      $('#prefijo_rep').prop('disabled', bloquear); // En editar.php puede decir prop('readonly') pero los select usan disabled

      if (bloquear) {
        $('#nombre_rep, #apellido_rep, #fechaN_rep, #telefono_rep, #email_rep').css('background-color', '#eee');
        $('#prefijo_rep, #genero_rep').css('background-color', '#eee');
      } else {
        $('#nombre_rep, #apellido_rep, #fechaN_rep, #telefono_rep, #email_rep').css('background-color', '#fff');
        $('#prefijo_rep, #genero_rep').css('background-color', '#fff');
      }
    }

    // VALIDACIÓN INTEGRADA (Formato + Existencia + Rol)
    async function validarCedulaRepFormato() {
      const cedula = cedulaRepInput.value.trim();
      const tipo = tipoCedulaRepSelect.value;
      let esValido = true;

      $(cedulaRepInput).removeClass('input-error');
      $(tipoCedulaRepSelect).removeClass('input-error');

      const cedulaMenor = $('#cedula').val() ? $('#cedula').val().trim() : '';
      const tipoMenor = $('#tipo_cedula').val() ? $('#tipo_cedula').val() : '';

      if (cedula === cedulaMenor && tipo === tipoMenor && cedula !== '') {
        $(cedulaRepInput).addClass('input-error');
        $('#nombre_rep').val('');
        $('#apellido_rep').val('');
        // Limpiamos los demás campos del representante...
        alternarCamposRepresentante(true); // Bloqueamos para que no avance
        
        mostrarAviso('🛑 <b>Error de Validación:</b> La cédula del representante no puede ser igual a la cédula del paciente menor de edad.');
        return false;
      }

      if (tipo === "") {
        $(tipoCedulaRepSelect).addClass('input-error');
        mostrarAviso('🛑 Error del Representante: Debe seleccionar el Tipo de Cédula del Representante.');
        return false;
      }

      if (cedula === "") {
        $(cedulaRepInput).addClass('input-error');
        mostrarAviso('🛑 Error del Representante: El campo Cédula del Representante es obligatorio.');
        return false;
      }

      const cedulaNum = parseInt(cedula);
      if (tipo === 'V' && cedulaNum > 80000000) {
        $(cedulaRepInput).addClass('input-error');
        mostrarAviso('🛑 Error de Cédula (V-): La cédula del Representante no puede ser mayor a 80.000.000');
        return false;
      } else if (tipo === 'E' && cedulaNum < 80000000) {
        $(cedulaRepInput).addClass('input-error');
        mostrarAviso('🛑 Error de Cédula (E-): La cédula del Representante no puede ser menor a 80.000.000');
        return false;
      }

      // 1. Buscamos en Base de Datos
      $('#nombre_rep').attr('placeholder', 'Buscando datos...');
      const data = await verificarCedulaRepEnBD(tipo, cedula);

      if (data && data.existe) {
        if (data.es_representante === true || data.es_representante === 1) {
          // ✅ CASO 1: Existe y es representante (Auto-rellenamos y bloqueamos)
          $('#nombre_rep').val(data.nombre);
          $('#apellido_rep').val(data.apellido);
          if (data.fecha_nacimiento) {
            $('#fechaN_rep').val(data.fecha_nacimiento);
            calcularEdadRep();
          }
          if (data.telefono_numero) $('#telefono_rep').val(data.telefono_numero);
          if (data.prefijo_id) $('#prefijo_rep').val(data.prefijo_id);
          if (data.genero) $('#genero_rep').val(data.genero);
          if (data.email) $('#email_rep').val(data.email);

          alternarCamposRepresentante(true);
        } else {
          // ❌ CASO 2: Existe pero NO es representante (Bloqueamos, vaciamos y marcamos error)
          $('#nombre_rep').val('');
          $('#apellido_rep').val('');
          $('#fechaN_rep').val('');
          $('#edad_rep').val('');
          $('#telefono_rep').val('');
          $('#prefijo_rep').val('');
          $('#genero_rep').val('');
          $('#email_rep').val('');

          $(cedulaRepInput).addClass('input-error');
          alternarCamposRepresentante(true); // Bloquear campos para evitar que sigan llenando

          mostrarAviso('🛑 <b>Cédula no válida:</b> Esta cédula ya está registrada en el sistema, pero pertenece a otra persona que <b>NO es un Representante</b>. Por favor ingrese una cédula diferente para continuar.');
          esValido = false;
        }
      } else {
        // ⚪ CASO 3: No existe en la BD (Limpiamos y desbloqueamos para registro nuevo)
        // Solo limpiamos si previamente estaban bloqueados (para no borrar lo que el usuario escribe)
        if ($('#nombre_rep').prop('readonly')) {
          $('#nombre_rep').val('');
          $('#apellido_rep').val('');
          $('#fechaN_rep').val('');
          $('#edad_rep').val('');
          $('#telefono_rep').val('');
          $('#prefijo_rep').val('');
          $('#genero_rep').val('');
          $('#email_rep').val('');
        }

        alternarCamposRepresentante(false);
        $('#nombre_rep').attr('placeholder', 'Nombre Del Representante');
      }

      return esValido;
    }

    // Eventos de Validación del Paciente (ya usan la función corregida)
    cedulaInput.addEventListener('blur', validarCedulaFormato);
    tipoCedulaSelect.addEventListener('change', validarCedulaFormato);
    // Evento blur para la búsqueda del representante
    cedulaRepInput.addEventListener('blur', validarCedulaRepFormato);
    tipoCedulaRepSelect.addEventListener('change', validarCedulaRepFormato);

    $('#cedula, #tipo_cedula').on('input change', function() {
      // 1. Obtenemos datos del menor de forma segura
      const cedulaMenor = $('#cedula').val() ? $('#cedula').val().trim() : '';
      const tipoMenor = $('#tipo_cedula').val() ? $('#tipo_cedula').val() : '';
      
      // 2. Obtenemos datos del representante DIRECTAMENTE POR SU ID (Esto evita que el JS se rompa)
      // Nota: Asegúrate de que los ID coincidan con los de tu HTML (#cedula_rep y #tipo_cedula_rep)
      const cedulaRep = $('#cedula_rep').val() ? $('#cedula_rep').val().trim() : '';
      const tipoRep = $('#tipo_cedula_rep').val() ? $('#tipo_cedula_rep').val() : '';

      // 3. Validamos
      if (cedulaMenor !== '' && cedulaMenor === cedulaRep && tipoMenor === tipoRep) {
        $('#cedula').addClass('input-error');
        mostrarAviso('🛑 <b>Error de Validación:</b> La cédula del paciente menor no puede ser idéntica a la cédula de su representante.');
        $('#cedula').val(''); // Reseteamos el campo
      } else {
        $('#cedula').removeClass('input-error');
      }
    });

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
      var sectorSelect = document.getElementById('sector_menor');

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
      var sectorSelect = document.getElementById('sector_menor');

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
    // LÓGICA AJAX: GUARDAR NUEVA PATOLOGÍA/ALERGIA Y ACTUALIZAR SELECTS
    // =====================================================================

    function agregarFilaSintomaAjax() {
      let opciones = $('.select-sintoma-ajax').length > 0 ?
        $('.select-sintoma-ajax').first().html() :
        `<option value="">--- Seleccione un síntoma ---</option><?php echo $opciones_sintomas_global; ?>`;

      let htmlSintoma = `
        <div class="row fila-sintoma-ajax" style="margin-bottom: 10px;">
            <div class="col-sm-10">
                <div class="input-group">
                    <select class="form-control select-sintoma-ajax">
                        ${opciones}
                    </select>
                    <span class="input-group-btn">
                        <button class="btn btn-info btn-search-sintoma-ajax" type="button" title="Buscar Síntoma">
                          <i><img src="../../recursos/imagenes/iconos/buscar.png" style="width:10px; height:10px;"></i>
                        </button>
                    </span>
                </div>
            </div>
            <div class="col-sm-2">
                <button type="button" class="btn btn-danger btn-remove-sintoma-ajax">
                    <i><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></i>
                </button>
            </div>
        </div>`;
      $('#contenedor_filas_sintomas_ajax').append(htmlSintoma);
    }

    $('#btn_abrir_sintomas_ajax').click(function() {
      if ($('#contenedor_filas_sintomas_ajax').children().length === 0) {
        agregarFilaSintomaAjax();
      }
      $('#modalSintomasAjax').modal('show');
    });

    $('#add_fila_sintoma_ajax').click(agregarFilaSintomaAjax);

    $(document).on('click', '.btn-remove-sintoma-ajax', function() {
      $(this).closest('.fila-sintoma-ajax').remove();
    });

    $(document).on('change', '.select-sintoma-ajax', function() {
      let selectActual = $(this);
      let valorActual = selectActual.val();
      if (valorActual === "") return;

      let conteo = 0;
      $('.select-sintoma-ajax').each(function() {
        if ($(this).val() === valorActual) conteo++;
      });
      if (conteo > 1) {
        mostrarAviso("⚠️ <b>Atención:</b> Este síntoma ya ha sido seleccionado. Elija uno diferente.");
        selectActual.val("");
      }
    });

    let selectDestinoTargetSintomaAjax = null;
    $(document).on('click', '.btn-search-sintoma-ajax', function() {
      selectDestinoTargetSintomaAjax = $(this).closest('.input-group').find('.select-sintoma-ajax');
      $('#modalBuscarSintoma').modal('show');
      $('#inputBuscarSintoma').val('').trigger('keyup');
    });

    $('#inputBuscarSintoma').on('keyup', function() {
      let texto = $(this).val().toLowerCase();
      let html = '';
      let opciones = $('.select-sintoma-ajax:first option').not('[value=""]');
      opciones.each(function() {
        let nombre = $(this).text();
        if (nombre.toLowerCase().includes(texto)) {
          html += `<a href="#" class="list-group-item list-group-item-action seleccionar-sintoma-ajax" data-id="${$(this).val()}">${nombre}</a>`;
        }
      });
      $('#listaResultadosSintoma').html(html);
    });

    $(document).on('click', '.seleccionar-sintoma-ajax', function(e) {
      e.preventDefault();
      selectDestinoTargetSintomaAjax.val($(this).data('id')).trigger('change');
      $('#modalBuscarSintoma').modal('hide');
    });

    $('#btnGuardarSintomaBD').click(function() {
      let nombreSintoma = $('#nombre_nuevo_sintoma').val().trim();

      if (nombreSintoma === "") {
        mostrarAviso("⚠️ El nombre del síntoma no puede estar vacío.");
        return;
      }

      let btn = $(this);
      btn.prop('disabled', true).text('Guardando...');

      $.ajax({
        url: '../../cfg/ajax/ajax_guardar_sintoma.php',
        type: 'POST',
        data: {
          nombre_sintoma: nombreSintoma
        },
        dataType: 'json',
        success: function(response) {
          if (response.success) {
            let nuevaOpcion = `<option value="${response.id}">${response.nombre}</option>`;
            $('.select-sintoma-ajax').append(nuevaOpcion);
            let $ultimoSelect = $('.select-sintoma-ajax').last();
            if ($ultimoSelect.length > 0 && $ultimoSelect.val() === "") {
              $ultimoSelect.val(response.id).trigger('change');
            } else {
              agregarFilaSintomaAjax();
              $('.select-sintoma-ajax').last().val(response.id).trigger('change');
            }
            $('#nombre_nuevo_sintoma').val('');
            $('#modalNuevoSintoma').modal('hide');
            mostrarAviso("✅ Síntoma guardado y seleccionado correctamente.");
          } else {
            mostrarAviso("Error al guardar: " + (response.error || "Desconocido"));
          }
        },
        error: function() {
          mostrarAviso("Error de conexión al intentar guardar el síntoma.");
        },
        complete: function() {
          btn.prop('disabled', false).text('Guardar y Seleccionar');
        }
      });
    });

    $('#modalNuevoSintoma').on('hidden.bs.modal', function() {
      $('#nombre_nuevo_sintoma').val('');
    });

    $('#btn_confirmar_sintomas_ajax').click(function() {
      let ids = [];
      let nombres = [];

      $('.select-sintoma-ajax').each(function() {
        let val = $(this).val();
        if (val && val !== "") {
          ids.push(val);
          nombres.push($(this).find('option:selected').text().trim());
        }
      });

      $('#ajax_sintomas_ids').val(ids.join(','));

      let btn = $('#btn_abrir_sintomas_ajax');
      if (ids.length > 0) {
        btn.removeClass('btn-info input-error').addClass('btn-success');
        btn.html('<i></i> ' + ids.length + ' Síntomas seleccionados');
        btn.attr('data-original-title', nombres.join(', ')).tooltip('fixTitle');
      } else {
        btn.removeClass('btn-success').addClass('btn-info input-error');
        btn.html('<i><img src="../../recursos/imagenes/iconos/buscar.png" style="width:15px; height:15px; filter: invert(1);"></i> Gestionar Síntomas');
        btn.attr('data-original-title', 'Ninguno seleccionado').tooltip('fixTitle');
      }

      $('#modalSintomasAjax').modal('hide');
    });

    $('#modalSintomasAjax').on('hidden.bs.modal', function() {
      if ($('#modalNuevaPatologia').hasClass('in')) {
        $('body').addClass('modal-open');
      }
    });

    $('#btn_guardar_ajax_pat').off('click').on('click', function(e) {
      e.preventDefault();
      let nombre = $('#ajax_nombre_pat').val().trim();
      let cie = $('#ajax_cie_pat').val().trim();
      let contagiosa = $('#ajax_contagiosa_pat').val();
      let sintomasIds = $('#ajax_sintomas_ids').val();

      if (nombre === '') {
        mostrarAviso("⚠️ El nombre de la patología es obligatorio.");
        return;
      }
      if (sintomasIds === '') {
        mostrarAviso("⚠️ Debe seleccionar al menos un síntoma.");
        $('#btn_abrir_sintomas_ajax').addClass('input-error');
        return;
      }

      $.ajax({
        url: window.location.href,
        type: 'POST',
        data: {
          ajax_nueva_patologia: 1,
          nombre_patologia: nombre,
          codigo_cie: cie,
          enfermedad_contagiosa: contagiosa,
          sintomas_ids: sintomasIds
        },
        dataType: 'json',
        success: function(res) {
          if (res.success) {
            let nuevaOpcion = `<option value="${res.id}" data-nombre="${res.nombre}" data-cie="${res.cie}">${res.nombre}</option>`;
            $('.select-pat').append(nuevaOpcion);

            let $ultimoSelect = $('.select-pat').last();
            if ($ultimoSelect.val() === "") {
              $ultimoSelect.val(res.id).trigger('change');
            }

            $('#formAjaxPatologia')[0].reset();
            $('#ajax_sintomas_ids').val('');
            $('.chk-sintoma-ajax').prop('checked', false);
            let btn = $('#btn_abrir_sintomas_ajax');
            btn.removeClass('btn-success input-error').addClass('btn-info').html('<i></i> Gestionar Síntomas');
            btn.attr('data-original-title', 'Ninguno seleccionado').tooltip('fixTitle');

            $('#modalNuevaPatologia').modal('hide');
            mostrarAviso("✅ Patología y síntomas guardados correctamente.");
          } else {
            mostrarAviso("🛑 " + res.error);
          }
        },
        error: function() {
          mostrarAviso("❌ Error de conexión con el servidor.");
        }
      });
    });

    $('#btn_guardar_ajax_ale').click(function(e) {
      e.preventDefault();
      let nombre = $('#ajax_nombre_ale').val().trim();

      if (nombre === '') {
        mostrarAviso("⚠️ El nombre de la alergia es obligatorio.");
        return;
      }

      $.ajax({
        url: window.location.href,
        type: 'POST',
        data: {
          ajax_nueva_alergia: 1,
          nombre_alergia: nombre
        },
        dataType: 'json',
        success: function(res) {
          if (res.success) {
            let nuevaOpcion = `<option value="${res.id}" data-nombre="${res.nombre}">${res.nombre}</option>`;
            $('.select-ale').append(nuevaOpcion);

            let $ultimoSelect = $('.select-ale').last();
            if ($ultimoSelect.val() === "") {
              $ultimoSelect.val(res.id).trigger('change');
            }

            $('#formAjaxAlergia')[0].reset();
            $('#modalNuevaAlergia').modal('hide');
            mostrarAviso("✅ Alergia guardada y añadida a la lista.");
          } else {
            mostrarAviso("🛑 " + res.error);
          }
        },
        error: function() {
          mostrarAviso("❌ Error de conexión con el servidor.");
        }
      });
    });

    // =====================================================================
    // LÓGICA DE PATOLOGÍAS Y ALERGIAS (NUEVO MÉTODO DINÁMICO)
    // =====================================================================

    $('#btn_modal_pat, #btn_modal_ale').tooltip();

    function agregarFilaPatologia() {
      let htmlPat = `
        <div class="row fila-pat" style="margin-bottom: 10px;">
            <div class="col-sm-6">
                <div class="input-group">
                    <select class="form-control select-pat">
                        <option value="">--- Seleccione una patología ---</option>
                        <?php
                        $conexion = new mysqli("localhost", "root", "", "cpt3db");
                        $q = $conexion->query("SELECT Id_patologia, nombre_patologia, codigo_cie FROM patologias WHERE estatus = 1 ORDER BY nombre_patologia ASC");
                        while ($p = $q->fetch_assoc()) {
                          echo "<option value='{$p['Id_patologia']}' data-nombre='{$p['nombre_patologia']}' data-cie='{$p['codigo_cie']}'>{$p['nombre_patologia']}</option>";
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
            <div class="col-sm-4">
                <input type="date" class="form-control input-fecha-pat" max="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="col-sm-2">
                <button type="button" class="btn btn-danger btn-remove-pat">
                    <i><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></i>
                </button>
            </div>
        </div>`;
      $('#contenedor_filas_patologias').append(htmlPat);
    }

    function agregarFilaAlergia() {
      let htmlAle = `
        <div class="row fila-ale" style="margin-bottom: 10px;">
            <div class="col-sm-6">
                <div class="input-group">
                    <select class="form-control select-ale">
                        <option value="">--- Seleccione una alergia ---</option>
                        <?php
                        $conexion = new mysqli("localhost", "root", "", "cpt3db");
                        $q = $conexion->query("SELECT Id_alergias_conocidas, nombre_alergia FROM alergias_conocidas WHERE estatus = 1 ORDER BY nombre_alergia ASC");
                        while ($a = $q->fetch_assoc()) {
                          echo "<option value='{$a['Id_alergias_conocidas']}' data-nombre='{$a['nombre_alergia']}'>{$a['nombre_alergia']}</option>";
                        }
                        ?>
                    </select>
                    <span class="input-group-btn">
                        <button class="btn btn-info btn-search-ale" type="button" title="Buscar Alergia">
                          <i><img src="../../recursos/imagenes/iconos/buscar.png" style="width:10px; height:10px;"></i>
                        </button>
                    </span>
                </div>
            </div>
            <div class="col-sm-4">
                <input type="date" class="form-control input-fecha-ale" max="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="col-sm-2">
                <button type="button" class="btn btn-danger btn-remove-ale">
                    <i><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></i>
                </button>
            </div>
        </div>`;
      $('#contenedor_filas_alergias').append(htmlAle);
    }

    $('#btn_modal_pat').click(function() {
      if ($('#contenedor_filas_patologias').children().length === 0) agregarFilaPatologia();
    });
    $('#btn_modal_ale').click(function() {
      if ($('#contenedor_filas_alergias').children().length === 0) agregarFilaAlergia();
    });

    $('#add_fila_pat').click(agregarFilaPatologia);
    $('#add_fila_ale').click(agregarFilaAlergia);

    $(document).on('click', '.btn-remove-pat', function() {
      $(this).closest('.fila-pat').remove();
    });
    $(document).on('click', '.btn-remove-ale', function() {
      $(this).closest('.fila-ale').remove();
    });

    $('#guardar_pat_listo').click(function() {
      let seleccionadas = [];
      let nombres = [];
      let errorFecha = false;

      $('.fila-pat').each(function() {
        let select = $(this).find('.select-pat');
        let id = select.val();
        let fecha = $(this).find('.input-fecha-pat').val();

        if (id && id !== "") {
          if (!fecha) errorFecha = true;
          let opt = select.find('option:selected');
          seleccionadas.push({
            id: parseInt(id),
            nombre: opt.data('nombre'),
            codigo_cie: opt.data('cie'),
            fecha: fecha
          });
          nombres.push(opt.text().trim());
        }
      });

      if (errorFecha) {
        mostrarAviso("⚠️ Atención: Hay patologías seleccionadas sin fecha de detección.");
        return false;
      }

      $('#patologias_ids').val(JSON.stringify(seleccionadas));

      let boton = $('#btn_modal_pat');
      if (nombres.length > 0) {
        boton.attr('data-original-title', nombres.join(', ')).tooltip('fixTitle');
        boton.removeClass('input-error');
      } else {
        boton.attr('data-original-title', 'Ninguna seleccionada').tooltip('fixTitle');
      }
      $('#modalPatologias').modal('hide');
    });

    $('#guardar_ale_listo').click(function() {
      let seleccionadas = [];
      let nombres = [];
      let errorFecha = false;

      $('.fila-ale').each(function() {
        let select = $(this).find('.select-ale');
        let id = select.val();
        let fecha = $(this).find('.input-fecha-ale').val();

        if (id && id !== "") {
          if (!fecha) errorFecha = true;
          let opt = select.find('option:selected');
          seleccionadas.push({
            id: parseInt(id),
            nombre: opt.data('nombre'),
            fecha: fecha
          });
          nombres.push(opt.text().trim());
        }
      });

      if (errorFecha) {
        mostrarAviso("⚠️ Atención: Hay alergias seleccionadas sin fecha de detección.");
        return false;
      }

      $('#alergias_ids').val(JSON.stringify(seleccionadas));

      let boton = $('#btn_modal_ale');
      if (nombres.length > 0) {
        boton.attr('data-original-title', nombres.join(', ')).tooltip('fixTitle');
        boton.removeClass('input-error');
      } else {
        boton.attr('data-original-title', 'Ninguna seleccionada').tooltip('fixTitle');
      }
      $('#modalAlergias').modal('hide');
    });

    let selectDestinoTarget = null;

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

    $(document).on('click', '.btn-search-ale', function() {
      selectDestinoTarget = $(this).closest('.input-group').find('.select-ale');
      $('#modalBuscarAle').modal('show');
      $('#inputBuscarAle').val('').trigger('keyup');
    });
    $('#inputBuscarAle').on('keyup', function() {
      let texto = $(this).val().toLowerCase();
      let html = '';
      let opciones = $('.select-ale:first option').not('[value=""]');
      opciones.each(function() {
        let nombre = $(this).text();
        if (nombre.toLowerCase().includes(texto)) {
          html += `<a href="#" class="list-group-item list-group-item-action seleccionar-ale" data-id="${$(this).val()}">${nombre}</a>`;
        }
      });
      $('#listaResultadosAle').html(html);
    });
    $(document).on('click', '.seleccionar-ale', function(e) {
      e.preventDefault();
      selectDestinoTarget.val($(this).data('id')).trigger('change');
      $('#modalBuscarAle').modal('hide');
    });

    $(document).on('change', '.select-pat', function() {
      let selectActual = $(this);
      let valorActual = selectActual.val();
      if (valorActual === "") return;
      let conteo = 0;
      $('.select-pat').each(function() {
        if ($(this).val() === valorActual) conteo++;
      });
      if (conteo > 1) {
        mostrarAviso("⚠️ <b>Atención:</b> Esta patología ya fue seleccionada en la lista.");
        selectActual.val("");
      }
    });

    $(document).on('change', '.select-ale', function() {
      let selectActual = $(this);
      let valorActual = selectActual.val();
      if (valorActual === "") return;
      let conteo = 0;
      $('.select-ale').each(function() {
        if ($(this).val() === valorActual) conteo++;
      });
      if (conteo > 1) {
        mostrarAviso("⚠️ <b>Atención:</b> Esta alergia ya fue seleccionada en la lista.");
        selectActual.val("");
      }
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

        // Criterios de vacío: null, string vacío, o valor de placeholder/default
        if ($input.is(':visible') && !$input.prop('disabled') && (valor === null || valor.trim() === "" || valor.includes('--- Seleccione'))) {
          errores.push("El campo " + $input.prev('p').text().replace('(*):', '').replace('(*)', '') + " es obligatorio.");
          $input.addClass('input-error');
          esValido = false;
        }
      });

      // 2. Validaciones Específicas

      if (tabSelector === '#info') {
        const cedulaEsValida = await validarCedulaFormato();
        if (!cedulaEsValida) {
          esValido = false;
        }

        const fechaNacVal = $('#fechaN').val();
        if (fechaNacVal) {
          let cumple = new Date(fechaNacVal + 'T00:00:00');
          let hoy = new Date();
          let anios = hoy.getFullYear() - cumple.getFullYear();
          let m = hoy.getMonth() - cumple.getMonth();
          if (m < 0 || (m === 0 && hoy.getDate() < cumple.getDate())) { anios--; }
          
          // Validación estricta usando la fecha real, no el texto del input
          if (anios >= 18 || anios < 0) {
            errores.push("El paciente debe ser menor de 18 años.");
            $('#fechaN').addClass('input-error');
            esValido = false;
          }
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

      if (tabSelector === '#direccion') {
        if ($('#municipio').val().trim() === "") {
          $('#municipio').addClass('input-error');
          esValido = false;
        }
      }

      if (tabSelector === '#ocupacion_estudios') {
        // Validar años aprobados si está habilitado
      }

      if (tabSelector === '#salud_otros') {}

      if (!esValido && errores.length > 0) {
        mostrarAviso('⚠️ Errores de Formulario:<ul><li>' + errores.join('</li><li>') + '</li></ul>');
      }

      return esValido;
    }


    // =====================================================================
    // MANEJO DE PESTAÑAS (TABS) (ADAPTADO)
    // =====================================================================

    // 1. MANEJO DE BOTONES SIGUIENTE
    $('.next-tab').off('click').on('click', async function() {
      const $btn = $(this);
      const tabActualSelector = $btn.data('tab-actual');
      const tabSiguienteName = $btn.data('tab-siguiente');
      const nextTabLink = $(`.nav-tabs li[data-tab-name="${tabSiguienteName}"] a`);

      $btn.prop('disabled', true).text('Validando...');

      // Validar todo, incluyendo bloqueo en pestaña 1 si existe documento
      const esValido = await validarPestana(tabActualSelector);

      $btn.prop('disabled', false).text(tabSiguienteName === 'confirmar' ? 'Guardar' : 'Siguiente');

      if (esValido) {
        if (tabSiguienteName === 'confirmar') {
          // ULTIMA VERIFICACIÓN ANTES DE MOSTRAR EL MODAL
          const documentoFinalValido = await validarCedulaFormato();
          if (!documentoFinalValido) {
            mostrarAviso("🛑 Documento Duplicado. Regrese a la pestaña 'Datos Personales' y corrija la información del Menor.");
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

    // Cierre al hacer clic en el backdrop (parte oscura)
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

    $('#confirmarGuardadoFinal').on('click', function() {
      $('#modalGuardarMedico').modal('hide');
      $('#formularioPaciente').submit();
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

    soloNumerosSinE(document.getElementById("tiempo_residencia"));
  </script>
  </body>

</html>