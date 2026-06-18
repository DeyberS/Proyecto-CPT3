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
  <title>Pacientes | Añadir</title>
  <?php
  include('includes/headerNav2.php');
  $redireccion = isset($_GET['pagina']) ? $_GET['pagina'] : 'ninguna';
  // --- AÑADIR ESTE BLOQUE PARA LOS SÍNTOMAS DINÁMICOS ---
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
    .input-error {
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
        Añadir Paciente
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-users"></i>Pacientes</a></li>
        <li class="active"><a href="#"><i class="fa fa-user-plus"></i>Añadir</a></li>
      </ol>
    </section>

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
                  <input type="hidden" name="redireccion" id="redireccion" value="<?php echo $redireccion; ?>">
                  <section id="new" style="margin-bottom:6%;">
                    <label class="control-label"></label>
                    <div class="col-sm-1 pull-left" style="margin-top: 30px;">
                      <select name="tipo_cedula" id="tipo_cedula" class="form-control" style="width: 60px;" required>
                        <option value="V">V-</option>
                        <!--<option value="E">E- </option>-->
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
                      <input type="date" class="form-control pull-right" id="fecha_nacimiento_adulto" name="fecha_nacimiento_adulto" max="<?php echo date('Y-m-d'); ?>" onchange="calcularEdad()" required>
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
                <section id="new" style="margin-bottom:8%;">
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
                <section id="new" style="margin-bottom:6%;">
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
                  <div style="float:right; margin-top:3%;">
                    <button type="button" class="btn btn-secondary prev-tab" data-tab-anterior="#ocupacion_estudios">Atrás</button>
                    <button type="button" class="btn btn-primary next-tab" data-tab-actual="#direccion" data-tab-siguiente="salud_otros">Siguiente</button>
                  </div>
                </section>
              </div>
              <div class="tab-pane" id="salud_otros">
                <section id="new" style="margin-bottom:12%;">
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
                    <p>Discap:</p>
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
                  <div style="float:right; margin-top:8%;">
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
      document.getElementById('fecha_nacimiento_adulto').setAttribute('max', maxDate);
    }
    setMaxDateFor18Years();

    // Función para calcular edad y limpiar si es inválida
    function calcularEdad() {
      var fechaNacimiento = $('#fecha_nacimiento_adulto').val(); // Ajusta el ID según tu HTML
      var campoEdad = $('#edad_adulto');

      if (fechaNacimiento) {
        var fechaNac = new Date(fechaNacimiento);
        var hoy = new Date();

        // 1. Verificar si la fecha es mayor a hoy (excede el tiempo)
        if (fechaNac > hoy) {
          mostrarAviso("La fecha de nacimiento no puede ser menor a la fecha actual.");
          campoEdad.val(""); // <-- AQUÍ COLOCAMOS EL VALOR EN VACÍO
          $('#fecha_nacimiento_adulto').val(""); // Opcional: limpiar también la fecha
          return;
        }

        // 2. Cálculo normal de la edad
        var edad = hoy.getFullYear() - fechaNac.getFullYear();
        var m = hoy.getMonth() - fechaNac.getMonth();
        if (m < 0 || (m === 0 && hoy.getDate() < fechaNac.getDate())) {
          edad--;
        }

        // 3. Validación adicional (ejemplo: si debe ser mayor de 18)
        if (edad < 18) {
          alert("Este campo es para adultos. La edad calculada es " + edad + " años.");
          campoEdad.val(""); // Limpiar si no cumple el criterio de adulto
        } else {
          campoEdad.val(edad); // Si es correcto, mostrar la edad
        }
      } else {
        campoEdad.val(""); // Si no hay fecha, el campo edad queda vacío
      }
    }

    // Escuchar el cambio en el input de fecha
    $('#fecha_nacimiento_adulto').on('change', function() {
      calcularEdad();
    });

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
      var sectorSelect = document.getElementById('sector_adulto');

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
      var sectorSelect = document.getElementById('sector_adulto');

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
    // LÓGICA AJAX: GUARDAR NUEVA PATOLOGÍA/ALERGIA Y ACTUALIZAR SELECTS
    // =====================================================================

    // Función para agregar filas dinámicas (Igual a salud_patologias_agregar)
    function agregarFilaSintomaAjax() {
      // Extraemos las opciones actualizadas (por si se agregó uno por AJAX)
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

    // 1. Abrir Modal y gestionar filas
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

    // Prevenir duplicados en la selección
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

    // 2. Buscador en Tiempo Real para el Modal Interno
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

    // 3. CREAR NUEVO SÍNTOMA EN LA BD POR AJAX (El Guardado)
    $('#btnGuardarSintomaBD').click(function() {
      let nombreSintoma = $('#nombre_nuevo_sintoma').val().trim();

      if (nombreSintoma === "") {
        mostrarAviso("⚠️ El nombre del síntoma no puede estar vacío.");
        return;
      }

      let btn = $(this);
      btn.prop('disabled', true).text('Guardando...');

      $.ajax({
        url: '../../cfg/ajax/ajax_guardar_sintoma.php', // Misma ruta que usa patologías
        type: 'POST',
        data: {
          nombre_sintoma: nombreSintoma
        },
        dataType: 'json',
        success: function(response) {
          if (response.success) {
            let nuevaOpcion = `<option value="${response.id}">${response.nombre}</option>`;

            // 1. Agregamos la opción a todos los selects dinámicos actuales
            $('.select-sintoma-ajax').append(nuevaOpcion);

            // 2. Si el último select está vacío, lo seleccionamos automáticamente
            let $ultimoSelect = $('.select-sintoma-ajax').last();
            if ($ultimoSelect.length > 0 && $ultimoSelect.val() === "") {
              $ultimoSelect.val(response.id).trigger('change');
            } else {
              // Si no, agregamos una fila nueva y lo seleccionamos
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
      $('#nombre_nuevo_sintoma').val(''); // Limpiar input al cerrar
    });

    // 4. Confirmar Selección de Síntomas
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

    // Fix de z-index: Para que el fondo gris no bloquee el modal principal
    $('#modalSintomasAjax').on('hidden.bs.modal', function() {
      if ($('#modalNuevaPatologia').hasClass('in')) {
        $('body').addClass('modal-open');
      }
    });

    // --> AQUÍ CONTINÚA TU CÓDIGO EXISTENTE: "3. Guardar la Patología (AJAX Mejorado)" <--

    // 3. Guardar la Patología (AJAX Mejorado)
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
        url: window.location.href, // Apunta a este mismo archivo
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
            // ... (tu código actual que crea el <option> y cierra el modal) ...
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
        url: window.location.href, // Apunta a este mismo archivo
        type: 'POST',
        data: {
          ajax_nueva_alergia: 1,
          nombre_alergia: nombre
        },
        dataType: 'json',
        success: function(res) {
          if (res.success) {
            // ... (tu código actual de alergias) ...
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
            // ESTO ES LO NUEVO: Muestra el error de duplicado
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

    // 1. Funciones para agregar filas HTML
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

    // Asegurar que abren con al menos 1 fila
    $('#btn_modal_pat').click(function() {
      if ($('#contenedor_filas_patologias').children().length === 0) agregarFilaPatologia();
    });
    $('#btn_modal_ale').click(function() {
      if ($('#contenedor_filas_alergias').children().length === 0) agregarFilaAlergia();
    });

    // Botones de agregar fila y eliminar fila
    $('#add_fila_pat').click(agregarFilaPatologia);
    $('#add_fila_ale').click(agregarFilaAlergia);

    $(document).on('click', '.btn-remove-pat', function() {
      $(this).closest('.fila-pat').remove();
    });
    $(document).on('click', '.btn-remove-ale', function() {
      $(this).closest('.fila-ale').remove();
    });

    // 2. Guardar JSON en inputs y actualizar Tooltips
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

    // 3. Modales Buscadores (Evita recarga AJAX, busca en el select directamente)
    let selectDestinoTarget = null;

    // Buscador Patologías
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

    // Buscador Alergias
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

    // 4. Bloquear la elección de opciones repetidas
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
        const cedulaEsValida = await validarDatosUnicos();
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

      if (tabSelector === '#salud_otros') {}

      if (!esValido && errores.length > 0) {
        mostrarAviso('⚠️ Errores de Formulario:<ul><li>' + errores.join('</li><li>') + '</li></ul>');
      }

      return esValido;
    }


    // =====================================================================
    // MANEJO DE PESTAÑAS (TABS)
    // =====================================================================

    // --- 3. BOTÓN SIGUIENTE Y VALIDACIÓN FINAL ---
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