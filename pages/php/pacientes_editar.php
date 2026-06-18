<?php
if (isset($_POST['ajax_nueva_patologia'])) {
  $conexion = new mysqli("localhost", "root", "", "cpt3db");
  $conexion->set_charset("utf8");
  $nombre = $conexion->real_escape_string($_POST['nombre_patologia']);
  $cie = $conexion->real_escape_string($_POST['codigo_cie']);
  $contagiosa = $conexion->real_escape_string($_POST['enfermedad_contagiosa']);

  $verificar = $conexion->query("SELECT Id_patologia FROM patologias WHERE nombre_patologia = '$nombre'");
  if ($verificar->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Ya existe una patología registrada con el nombre: <b>' . $nombre . '</b>']);
    exit;
  }

  $conexion->query("INSERT INTO patologias (nombre_patologia, codigo_cie, estatus, contagioso) VALUES ('$nombre', '$cie', 1, '$contagiosa')");
  $id_pat = $conexion->insert_id;

  if (isset($_POST['sintomas_ids']) && !empty($_POST['sintomas_ids'])) {
    $sintomas = explode(',', $_POST['sintomas_ids']);
    foreach ($sintomas as $id_sintoma) {
      if (trim($id_sintoma) !== "") {
        $id_sin = $conexion->real_escape_string($id_sintoma);
        // CORRECCIÓN: La tabla en la BD termina en "s" (detalle_patologia_sintomas)
        $conexion->query("INSERT INTO detalle_patologia_sintomas (Id_patologia, Id_sintoma) VALUES ('$id_pat', '$id_sin')");
      }
    }
  }

  echo json_encode(['success' => true, 'id' => $id_pat, 'nombre' => $nombre, 'cie' => $cie]);
  exit;
}

if (isset($_POST['ajax_nueva_alergia'])) {
  $conexion = new mysqli("localhost", "root", "", "cpt3db");
  $conexion->set_charset("utf8");
  $nombre = $conexion->real_escape_string($_POST['nombre_alergia']);

  $verificar = $conexion->query("SELECT Id_alergias_conocidas FROM alergias_conocidas WHERE nombre_alergia = '$nombre'");
  if ($verificar->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Ya existe una alergia registrada con el nombre: <b>' . $nombre . '</b>']);
    exit;
  }

  $conexion->query("INSERT INTO alergias_conocidas (nombre_alergia, estatus) VALUES ('$nombre', 1)");
  echo json_encode(['success' => true, 'id' => $conexion->insert_id, 'nombre' => $nombre]);
  exit;
}

// NUEVO: Manejador para guardar Síntomas
if (isset($_POST['ajax_nuevo_sintoma'])) {
  $conexion = new mysqli("localhost", "root", "", "cpt3db");
  $conexion->set_charset("utf8");
  $nombre = $conexion->real_escape_string($_POST['nombre_sintoma']);

  $verificar = $conexion->query("SELECT Id_sintomas FROM sintomas WHERE nombre_sintoma = '$nombre'");
  if ($verificar->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Ya existe un síntoma registrado con el nombre: <b>' . $nombre . '</b>']);
    exit;
  }

  $conexion->query("INSERT INTO sintomas (nombre_sintoma, estatus) VALUES ('$nombre', 1)");
  echo json_encode(['success' => true, 'id' => $conexion->insert_id, 'nombre' => $nombre]);
  exit;
}
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Pacientes | Editar</title>
  <?php
  include('includes/headerNav2.php');

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
    .modal.in .modal-dialog,
    #avisoModal,
    #modalGuardarPaciente {
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
        Editar Paciente
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-users"></i>Pacientes</a></li>
        <li class="active"><a href="#"><i class="fa fa-user-plus"></i>Editar</a></li>
      </ol>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active" data-tab-name="info"><a href="#info" data-toggle="tab">Datos Personales</a></li>
              <li data-tab-name="ocupacion_estudios" class="disabled-tab"><a href="#ocupacion_estudios" data-toggle="tab">Ocupacion Y Estudios Aprobados</a></li>
              <li data-tab-name="direccion" class="disabled-tab"><a href="#direccion" data-toggle="tab">Direccion de Residencia</a></li>
              <li data-tab-name="salud_otros" class="disabled-tab"><a href="#salud_otros" data-toggle="tab">Salud y Otros Datos</a></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane active" id="info">
                <form id="formularioPaciente" action="../../cfg/editar/editar_paciente.php" class="form-group" method="POST" novalidate>
                  <?php
                  // Bloque PHP para cargar los datos del paciente
                  include("../../cfg/conexion.php");

                  // CONSULTA SQL CORREGIDA Y AMPLIADA para obtener todos los IDs necesarios
                  $sql = "SELECT p.id, p.email, p.nombre, p.apellido, p.tipo_cedula, p.cedula, p.fecha_nacimiento, p.genero, p.email,
                           tp.telefono, pt.prefijo, tp.Id_prefijo,
                           dp.situacion_conyugal, dp.etnia, dp.tipo_etnia, dp.analfabeta, dp.profesion, dp.ocupacion, dp.nivel_instruccion, dp.mision, dp.años_aprobados, dp.seguro_social, dp.discapacidad, dp.tipo_discapacidad,
                           hm.grupo_sanguineo,
                           d.avenida_calle, d.referencia, d.tiempo_residencia, d.Id_sector, d.tiempo, /* DATOS DE DIRECCIÓN */
                           ln.Id_municipio AS Id_Municipio_Nac,  /* ID DE MUNICIPIO DE NACIMIENTO */
                           munnac.Id_Estado AS Id_Estado_Nac,   /* ID DE ESTADO DE NACIMIENTO */
                           estnac.Id_Pais AS Id_Pais_Nac,       /* ID DE PAÍS DE NACIMIENTO */
                           dirsec.Id_Municipio AS Id_Municipio_Dir,
                           dirmun.Id_Estado AS Id_Estado_Dir,

                           /* AGREGANDO DATOS DE PATOLOGÍAS Y ALERGIAS ASOCIADAS */
                           GROUP_CONCAT(DISTINCT CONCAT(pat.Id_patologia, '::', pat.nombre_patologia, '::', pat.codigo_cie, '::', hp.fecha_registro) SEPARATOR '||') AS patologias_data,
                           GROUP_CONCAT(DISTINCT CONCAT(al.Id_alergias_conocidas, '::', al.nombre_alergia, '::', ha.fecha_registro) SEPARATOR '||') AS alergias_data

                  FROM persona p
                  LEFT JOIN telefonos_personas tp ON p.id = tp.Id_persona
                  LEFT JOIN prefijos_telefonos pt ON tp.Id_prefijo = pt.Id
                  LEFT JOIN historial_medico hm ON p.id = hm.Id_persona
                  LEFT JOIN detalle_paciente dp ON p.id = dp.Id_persona
                  LEFT JOIN lugar_nacimiento ln ON p.id = ln.Id_persona
                  LEFT JOIN municipio munnac ON ln.Id_municipio = munnac.Id_Municipio
                  LEFT JOIN estado estnac ON munnac.Id_Estado = estnac.Id_Estado
                  LEFT JOIN direccion d ON p.id = d.Id_persona
                  LEFT JOIN sector dirsec ON d.Id_sector = dirsec.Id_Sector
                  LEFT JOIN municipio dirmun ON dirsec.Id_Municipio = dirmun.Id_Municipio

                  /* JOINS PARA HISTORIAL MÉDICO */
                  LEFT JOIN historial_patologias hp ON hm.id_historial = hp.Id_Historial AND p.id = hp.Id_persona
                  LEFT JOIN patologias pat ON hp.Id_patologia = pat.Id_patologia

                  LEFT JOIN historial_alergias ha ON hm.id_historial = ha.Id_Historial AND p.id = ha.Id_persona
                  LEFT JOIN alergias_conocidas al ON ha.Id_alergia = al.Id_alergias_conocidas

                  WHERE p.id =" . $_GET['Id'] . "
                  GROUP BY p.id";

                  $resultado = $conexion->query($sql);
                  $row = $resultado->fetch_assoc(); // $row contiene todos los datos
                  $id_paciente = $row['id']; // ID del paciente actual

                  // Variables PHP para usar en JS
                  $id_prefijo = $row['Id_prefijo'] ?? '';
                  $id_pais_nac = $row['Id_Pais_Nac'] ?? '';
                  $id_estado_nac = $row['Id_Estado_Nac'] ?? '';
                  $id_municipio_nac = $row['Id_Municipio_Nac'] ?? '';

                  $id_estado_dir = $row['Id_Estado_Dir'] ?? '';
                  $id_municipio_dir = $row['Id_Municipio_Dir'] ?? '';
                  $id_sector_dir = $row['Id_sector'] ?? '';

                  // Variables para inicializar Patologías y Alergias
                  $patologias_data = $row['patologias_data'] ?? '';
                  $alergias_data = $row['alergias_data'] ?? '';
                  ?>


                  <input type="hidden" name="Id" value="<?= $id_paciente; ?>">

                  <section id="new" style="margin-bottom:6%;">
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
                      <p>Situacion conyugal:</p>
                      <select name="situacion_conyugal" class="form-control">
                        <option value="">--- Seleccione ---</option>
                        <option value="Soltero" <?php echo ($row['situacion_conyugal'] == 'Soltero' ? 'selected' : ''); ?>>Soltero</option>
                        <option value="Casado" <?php echo ($row['situacion_conyugal'] == 'Casado' ? 'selected' : ''); ?>>Casado</option>
                        <option value="Viudo" <?php echo ($row['situacion_conyugal'] == 'Viudo' ? 'selected' : ''); ?>>Viudo</option>
                        <option value="Divorciado" <?php echo ($row['situacion_conyugal'] == 'Divorciado' ? 'selected' : ''); ?>>Divorciado</option>
                        <option value="Concubinato" <?php echo ($row['situacion_conyugal'] == 'Concubinato' ? 'selected' : ''); ?>>Concubinato</option>
                      </select>
                    </div>
                    <br><br><br><br>
                    <div class="col-sm-4">
                      <p>Pais de nacimiento (*):</p>
                      <select name="pais_nacimiento" id="pais_nacimiento" class="form-control" required>
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
                    <div class="col-sm-3">
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
                    <br><br><br><br>
                    <label class="control-label"></label>
                    <div class="col-sm-1">
                      <p>Etnia:</p>
                      <select name="etnia" id="etnia" class="form-control" required>
                        <option value="No" <?php echo ($row['etnia'] == 'No' ? 'selected' : ''); ?>>No</option>
                        <option value="Si" <?php echo ($row['etnia'] == 'Si' ? 'selected' : ''); ?>>Si</option>
                      </select>
                    </div>
                    <div class="col-sm-3">
                      <p>Tipo etnia:</p>
                      <select name="tipo_etnia" id="tipo_etnia" class="form-control">
                        <option value="">--- Seleccione Una Etnia ---</option>
                        <option value="wayuu" <?php echo ($row['etnia'] == 'wayuu' ? 'selected' : ''); ?>>Wayuu</option>
                        <option value="añu" <?php echo ($row['etnia'] == 'añu' ? 'selected' : ''); ?>>Añu</option>
                        <option value="baniva" <?php echo ($row['etnia'] == 'baniva' ? 'selected' : ''); ?>>Baniva</option>
                        <option value="kurripako" <?php echo ($row['etnia'] == 'kurripako' ? 'selected' : ''); ?>>Kurripako</option>
                        <option value="piapoco" <?php echo ($row['etnia'] == 'piapoco' ? 'selected' : ''); ?>>Piapoco</option>
                        <option value="warekena" <?php echo ($row['etnia'] == 'warekena' ? 'selected' : ''); ?>>Warekena</option>
                        <option value="bare" <?php echo ($row['etnia'] == 'bare' ? 'selected' : ''); ?>>Bare</option>
                        <option value="pemon" <?php echo ($row['etnia'] == 'pemon' ? 'selected' : ''); ?>>Pemon</option>
                        <option value="kariña" <?php echo ($row['etnia'] == 'kariña' ? 'selected' : ''); ?>>Kariña</option>
                        <option value="panare" <?php echo ($row['etnia'] == 'panare' ? 'selected' : ''); ?>>Panare</option>
                        <option value="yukpa" <?php echo ($row['etnia'] == 'yukpa' ? 'selected' : ''); ?>>Yukpa</option>
                        <option value="japreira" <?php echo ($row['etnia'] == 'japreira' ? 'selected' : ''); ?>>Japreira</option>
                        <option value="yekuana" <?php echo ($row['etnia'] == 'yekuana' ? 'selected' : ''); ?>>Yekuana</option>
                        <option value="chaima" <?php echo ($row['etnia'] == 'chaima' ? 'selected' : ''); ?>>Chaima</option>
                        <option value="bari" <?php echo ($row['etnia'] == 'bari' ? 'selected' : ''); ?>>Bari</option>
                        <option value="yanomami" <?php echo ($row['etnia'] == 'yanomami' ? 'selected' : ''); ?>>Yanomami</option>
                        <option value="sanema" <?php echo ($row['etnia'] == 'sanema' ? 'selected' : ''); ?>>Sanema</option>
                        <option value="warao" <?php echo ($row['etnia'] == 'warao' ? 'selected' : ''); ?>>Warao</option>
                        <option value="pume" <?php echo ($row['etnia'] == 'pume' ? 'selected' : ''); ?>>Pume</option>
                        <option value="piaroa" <?php echo ($row['etnia'] == 'piaroa' ? 'selected' : ''); ?>>Piaroa</option>
                        <option value="otro" <?php echo ($row['etnia'] == 'otro' ? 'selected' : ''); ?>>Otro/No Aplica</option>
                      </select>
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-4">
                      <p>Analfabeta (*):</p>
                      <select name="analfabeta" id="analfabeta" class="form-control" required>
                        <option value="">--- Seleccione Una Opcion ---</option>
                        <option value="Si" <?php echo ($row['analfabeta'] == 'Si' ? 'selected' : ''); ?>>Si</option>
                        <option value="No" <?php echo ($row['analfabeta'] == 'No' ? 'selected' : ''); ?>>No</option>
                      </select>
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-3">
                      <p>Cotiza seguro social:</p>
                      <select name="seguro_social" class="form-control">
                        <option value="">--- Seleccione Una Opcion ---</option>
                        <option value="Si" <?php echo ($row['seguro_social'] == 'Si' ? 'selected' : ''); ?>>Si</option>
                        <option value="No" <?php echo ($row['seguro_social'] == 'No' ? 'selected' : ''); ?>>No</option>
                      </select>
                    </div>
                    <label class="control-label"></label>
                    <br><br><br><br>
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
                    <div class="col-sm-3">
                      <p>Telefono (*):</p>
                      <input type="text" class="form-control" name="telefono" value="<?php echo $row['telefono']; ?>" id="telefono" placeholder="N° De Telefono" minlength="7" maxlength="7" required>
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-4">
                      <p>Email:</p>
                      <input type="email" class="form-control" name="email" value="<?php echo $row['email']; ?>" id="email" placeholder="nombreapellido2@gmail.com">
                    </div>
                    <div style="float:right; margin-top:5%;">
                      <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#modalConfirmarRegreso">Regresar</button>
                      <button type="button" class="btn btn-primary next-tab" data-tab-actual="#info" data-tab-siguiente="ocupacion_estudios">Siguiente</button>
                    </div>
                  </section>
              </div>
              <div class="tab-pane" id="ocupacion_estudios">
                <section id="new" style="margin-bottom:10%;">
                  <label class="control-label"></label>
                  <div class="col-sm-4">
                    <p>Nivel de instruccion:</p>
                    <select name="nivel_instruccion" id="nivel_instruccion" class="form-control">
                      <option value="">--- Seleccione Un Nivel De Instruccion ---</option>
                      <option value="sin_instruccion" <?php echo ($row['nivel_instruccion'] == 'sin_instruccion' ? 'selected' : ''); ?>>Sin Instrucción</option>
                      <option value="primaria_incompleta" <?php echo ($row['nivel_instruccion'] == 'primaria_incompleta' ? 'selected' : ''); ?>>Primaria Incompleta</option>
                      <option value="primaria_completa" <?php echo ($row['nivel_instruccion'] == 'primaria_completa' ? 'selected' : ''); ?>>Primaria Completa</option>
                      <option value="bachillerato_incompleto" <?php echo ($row['nivel_instruccion'] == 'bachillerato_incompleto' ? 'selected' : ''); ?>>Educación Media Incompleta</option>
                      <option value="bachiller" <?php echo ($row['nivel_instruccion'] == 'bachiller' ? 'selected' : ''); ?>>Bachiller (Media General)</option>
                      <option value="tecnico_medio" <?php echo ($row['nivel_instruccion'] == 'tecnico_medio' ? 'selected' : ''); ?>>Técnico Medio</option>
                      <option value="tsu" <?php echo ($row['nivel_instruccion'] == 'tsu' ? 'selected' : ''); ?>>Técnico Superior Universitario (T.S.U)</option>
                      <option value="universitario" <?php echo ($row['nivel_instruccion'] == 'universitario' ? 'selected' : ''); ?>>Universitario (Lic./Ing.)</option>
                      <option value="especializacion" <?php echo ($row['nivel_instruccion'] == 'especializacion' ? 'selected' : ''); ?>>Especialización</option>
                      <option value="maestria" <?php echo ($row['nivel_instruccion'] == 'maestria' ? 'selected' : ''); ?>>Maestría</option>
                      <option value="doctorado" <?php echo ($row['nivel_instruccion'] == 'doctorado' ? 'selected' : ''); ?>>Doctorado</option>
                    </select>
                  </div>
                  <label class="control-label"></label>
                  <div class="col-sm-4">
                    <p>Profesion:</p>
                    <select name="profesion" id="profesion" class="form-control">
                      <option value="">--- Seleccione Una Profesion ---</option>
                      <optgroup label="Salud">
                        <option value="medico" <?php echo ($row['profesion'] == 'medico' ? 'selected' : ''); ?>>Médico / Especialista</option>
                        <option value="enfermero" <?php echo ($row['profesion'] == 'enfermero' ? 'selected' : ''); ?>>Enfermero(a)</option>
                        <option value="bioanalista" <?php echo ($row['profesion'] == 'bioanalista' ? 'selected' : ''); ?>>Bioanalista</option>
                        <option value="odontologo" <?php echo ($row['profesion'] == 'odontologo' ? 'selected' : ''); ?>>Odontólogo</option>
                        <option value="farmaceuta" <?php echo ($row['profesion'] == 'farmaceuta' ? 'selected' : ''); ?>>Farmacéutico(a)</option>
                      </optgroup>
                      <optgroup label="Ingeniería y Tecnología">
                        <option value="ing_civil" <?php echo ($row['profesion'] == 'ing_civil' ? 'selected' : ''); ?>>Ingeniero Civil</option>
                        <option value="ing_sistemas" <?php echo ($row['profesion'] == 'ing_sistemas' ? 'selected' : ''); ?>>Ingeniero de Sistemas / Computación</option>
                        <option value="ing_mecanico" <?php echo ($row['profesion'] == 'ing_mecanico' ? 'selected' : ''); ?>>Ingeniero Mecánico</option>
                        <option value="analista_it" <?php echo ($row['profesion'] == 'analista_it' ? 'selected' : ''); ?>>Analista de Soporte Técnico</option>
                        <option value="programador" <?php echo ($row['profesion'] == 'programador' ? 'selected' : ''); ?>>Programador / Desarrollador</option>
                      </optgroup>
                      <optgroup label="Educación y Social">
                        <option value="docente" <?php echo ($row['profesion'] == 'docente' ? 'selected' : ''); ?>>Docente</option>
                        <option value="abogado" <?php echo ($row['profesion'] == 'abogado' ? 'selected' : ''); ?>>Abogado(a)</option>
                        <option value="psicologo" <?php echo ($row['profesion'] == 'psicologo' ? 'selected' : ''); ?>>Psicólogo(a)</option>
                        <option value="trabajador_social" <?php echo ($row['profesion'] == 'trabajador_social' ? 'selected' : ''); ?>>Trabajador(a) Social</option>
                      </optgroup>
                      <optgroup label="Administración y Comercio">
                        <option value="administrador" <?php echo ($row['profesion'] == 'administrador' ? 'selected' : ''); ?>>Administrador(a)</option>
                        <option value="contador" <?php echo ($row['profesion'] == 'contador' ? 'selected' : ''); ?>>Contador Público</option>
                        <option value="vendedor" <?php echo ($row['profesion'] == 'vendedor' ? 'selected' : ''); ?>>Vendedor / Comerciante</option>
                      </optgroup>
                    </select>
                  </div>
                  <label class="control-label"></label>
                  <div class="col-sm-3">
                    <p>Ocupacion:</p>
                    <select name="ocupacion" id="ocupacion" class="form-control">
                      <option value="">--- Seleccione Una Ocupacion ---</option>
                      <optgroup label="Salud">
                        <option value="medico" <?php echo ($row['ocupacion'] == 'medico' ? 'selected' : ''); ?>>Médico / Especialista</option>
                        <option value="enfermero" <?php echo ($row['ocupacion'] == 'enfermero' ? 'selected' : ''); ?>>Enfermero(a)</option>
                        <option value="bioanalista" <?php echo ($row['ocupacion'] == 'bioanalista' ? 'selected' : ''); ?>>Bioanalista</option>
                        <option value="odontologo" <?php echo ($row['ocupacion'] == 'odontologo' ? 'selected' : ''); ?>>Odontólogo</option>
                        <option value="farmaceuta" <?php echo ($row['ocupacion'] == 'farmaceuta' ? 'selected' : ''); ?>>Farmacéutico(a)</option>
                      </optgroup>
                      <optgroup label="Ingeniería y Tecnología">
                        <option value="ing_civil" <?php echo ($row['ocupacion'] == 'ing_civil' ? 'selected' : ''); ?>>Ingeniero Civil</option>
                        <option value="ing_sistemas" <?php echo ($row['ocupacion'] == 'ing_sistemas' ? 'selected' : ''); ?>>Ingeniero de Sistemas / Computación</option>
                        <option value="ing_mecanico" <?php echo ($row['ocupacion'] == 'ing_mecanico' ? 'selected' : ''); ?>>Ingeniero Mecánico</option>
                        <option value="analista_it" <?php echo ($row['ocupacion'] == 'analista_it' ? 'selected' : ''); ?>>Analista de Soporte Técnico</option>
                        <option value="programador" <?php echo ($row['ocupacion'] == 'programador' ? 'selected' : ''); ?>>Programador / Desarrollador</option>
                      </optgroup>
                      <optgroup label="Educación y Social">
                        <option value="docente_primaria" <?php echo ($row['ocupacion'] == 'docente_primaria' ? 'selected' : ''); ?>>Docente de Primaria</option>
                        <option value="profesor_media" <?php echo ($row['ocupacion'] == 'profesor_media' ? 'selected' : ''); ?>>Profesor de Educación Media</option>
                        <option value="abogado" <?php echo ($row['ocupacion'] == 'abogado' ? 'selected' : ''); ?>>Abogado(a)</option>
                        <option value="psicologo" <?php echo ($row['ocupacion'] == 'psicologo' ? 'selected' : ''); ?>>Psicólogo(a)</option>
                        <option value="trabajador_social" <?php echo ($row['ocupacion'] == 'trabajador_social' ? 'selected' : ''); ?>>Trabajador(a) Social</option>
                      </optgroup>
                      <optgroup label="Administración y Comercio">
                        <option value="administrador" <?php echo ($row['ocupacion'] == 'administrador' ? 'selected' : ''); ?>>Administrador(a)</option>
                        <option value="contador" <?php echo ($row['ocupacion'] == 'contador' ? 'selected' : ''); ?>>Contador Público</option>
                        <option value="secretaria" <?php echo ($row['ocupacion'] == 'secretaria' ? 'selected' : ''); ?>>Asistente Administrativo / Secretaria</option>
                        <option value="vendedor" <?php echo ($row['ocupacion'] == 'vendedor' ? 'selected' : ''); ?>>Vendedor / Comerciante</option>
                        <option value="cajero" <?php echo ($row['ocupacion'] == 'cajero' ? 'selected' : ''); ?>>Cajero(a)</option>
                      </optgroup>
                      <optgroup label="Oficios y Otros">
                        <option value="albanil" <?php echo ($row['ocupacion'] == 'albanil' ? 'selected' : ''); ?>>Albañil / Constructor</option>
                        <option value="mecanico" <?php echo ($row['ocupacion'] == 'mecanico' ? 'selected' : ''); ?>>Mecánico Automotriz</option>
                        <option value="cocinero" <?php echo ($row['ocupacion'] == 'cocinero' ? 'selected' : ''); ?>>Cocinero / Chef</option>
                        <option value="chofer" <?php echo ($row['ocupacion'] == 'chofer' ? 'selected' : ''); ?>>Chofer / Transportista</option>
                        <option value="seguridad" <?php echo ($row['ocupacion'] == 'seguridad' ? 'selected' : ''); ?>>Oficial de Seguridad</option>
                        <option value="estudiante" <?php echo ($row['ocupacion'] == 'estudiante' ? 'selected' : ''); ?>>Estudiante</option>
                        <option value="ama_casa" <?php echo ($row['ocupacion'] == 'ama_casa' ? 'selected' : ''); ?>>Hogar / Ama de casa</option>
                      </optgroup>
                    </select>
                  </div>
                  <br><br><br><br>
                  <label class="control-label"></label>
                  <div class="col-sm-4">
                    <p>Mision educativa:</p>
                    <select name="mision" id="mision" class="form-control">
                      <option value="">--- Seleccione Una Mision ---</option>
                      <option value="robinson" <?php echo ($row['mision'] == 'robinson' ? 'selected' : ''); ?>>Robinson</option>
                      <option value="ribas" <?php echo ($row['mision'] == 'ribas' ? 'selected' : ''); ?>>Ribas</option>
                      <option value="sucre" <?php echo ($row['mision'] == 'sucre' ? 'selected' : ''); ?>>Sucre</option>
                    </select>
                  </div>
                  <label class="control-label"></label>
                  <div class="col-sm-4">
                    <p>Años aprobados:</p>
                    <input type="number" id="años_aprobados" value="<?php echo $row['años_aprobados']; ?>" class="form-control" name="años_aprobados" min="0" max="10">
                    <p class="text-muted" id="años_help" style="margin-top: 5px;">Máximo:</p>
                  </div>
                  <div style="float:right; margin-top:9%;">
                    <button type="button" class="btn btn-secondary prev-tab" data-tab-actual="#ocupacion_estudios" data-tab-anterior="info">Atras</button>
                    <button type="button" class="btn btn-primary next-tab" data-tab-actual="#ocupacion_estudios" data-tab-siguiente="direccion">Siguiente</button>
                  </div>
                </section>
              </div>
              <div class="tab-pane" id="direccion">
                <section id="new" style="margin-bottom: 4%;">
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
                  <div style="float:right; margin-top:1%;">
                    <button type="button" class="btn btn-secondary prev-tab" data-tab-actual="#direccion" data-tab-anterior="ocupacion_estudios">Atras</button>
                    <button type="button" class="btn btn-primary next-tab" data-tab-actual="#direccion" data-tab-siguiente="salud_otros">Siguiente</button>
                  </div>
                </section>
              </div>
              <div class="tab-pane" id="salud_otros">
                <section id="new" style="margin-bottom: 12%;">
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
                    <p>Grupo Sanguineo (*):</p>
                    <select name="grupo_sanguineo" class="form-control" required>
                      <option value="">--- Seleccione ---</option>
                      <option value="A+" <?php echo ($row['grupo_sanguineo'] == 'A+' ? 'selected' : ''); ?>>A+</option>
                      <option value="A-" <?php echo ($row['grupo_sanguineo'] == 'A-' ? 'selected' : ''); ?>>A-</option>
                      <option value="B+" <?php echo ($row['grupo_sanguineo'] == 'B+' ? 'selected' : ''); ?>>B+</option>
                      <option value="B-" <?php echo ($row['grupo_sanguineo'] == 'B-' ? 'selected' : ''); ?>>B-</option>
                      <option value="AB+" <?php echo ($row['grupo_sanguineo'] == 'AB+' ? 'selected' : ''); ?>>AB+</option>
                      <option value="AB-" <?php echo ($row['grupo_sanguineo'] == 'AB-' ? 'selected' : ''); ?>>AB-</option>
                      <option value="O+" <?php echo ($row['grupo_sanguineo'] == 'O+' ? 'selected' : ''); ?>>O+</option>
                      <option value="O-" <?php echo ($row['grupo_sanguineo'] == 'O-' ? 'selected' : ''); ?>>O-</option>
                    </select>
                  </div>
                  <br><br><br><br><br>
                  <label class="control-label"></label>
                  <div class="col-sm-1">
                    <p>Discap.:</p>
                    <select name="discapacidad" id="discapacidad" class="form-control" required>
                      <option value="No" <?php echo ($row['discapacidad'] == 'No' ? 'selected' : ''); ?>">No</option>
                      <option value="Si" <?php echo ($row['discapacidad'] == 'Si' ? 'selected' : ''); ?>">Si</option>
                    </select>
                  </div>
                  <div class="col-sm-3">
                    <p>Tipo de discapacidad:</p>
                    <select name="tipo_discapacidad" id="tipo_discapacidad" class="form-control">
                      <option value="">--- Seleccione Una Discapacidad ---</option>
                      <option value="fisico_motora" <?php echo ($row['tipo_discapacidad'] == 'No' ? 'selected' : ''); ?>">Físico-Motora</option>
                      <option value="visual" <?php echo ($row['tipo_discapacidad'] == 'No' ? 'selected' : ''); ?>">Visual</option>
                      <option value="auditiva" <?php echo ($row['tipo_discapacidad'] == 'No' ? 'selected' : ''); ?>">Auditiva</option>
                      <option value="intelectual" <?php echo ($row['tipo_discapacidad'] == 'No' ? 'selected' : ''); ?>">Intelectual</option>
                      <option value="psicosocial" <?php echo ($row['tipo_discapacidad'] == 'No' ? 'selected' : ''); ?>">Psicosocial</option>
                      <option value="multiple" <?php echo ($row['tipo_discapacidad'] == 'No' ? 'selected' : ''); ?>">Múltiple</option>
                    </select>
                  </div>
                  <div style="float:right; margin-top: 5%;">
                    <button type="button" class="btn btn-secondary prev-tab" data-tab-actual="#salud_otros" data-tab-anterior="direccion">Atras</button>
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
  <?php
  // Se incluye el footer y los scripts base
  include('includes/footer.php');
  ?>

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

  <div class="modal" id="modalSintomasAjax" role="dialog" style="z-index: 1060;">
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

  <div class="modal" id="modalConfirmarRegreso" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarRegresoLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header modal-header-danger">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="modalConfirmarRegresoLabel"><i class="fa fa-sign-out"></i> Confirmacion de Regreso </h4>
        </div>
        <div class="modal-body">
          <p>Al hacer clic en "Abandonar Formulario", perderá todos los datos no guardados. ¿Desea continuar?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Regresar</button>
          <a href="pacientes_listado.php" class="btn btn-danger">Abandonar Formulario</a>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalGuardarPaciente" tabindex="-1" role="dialog" aria-labelledby="modalGuardarPacienteLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: #00a65a; color: white;">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="modalGuardarPacienteLabel"><i class="fa fa-save"></i> Confirmacion de Guardado </h4>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea actualizar la información del paciente?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Regresar</button>
          <button type="button" class="btn btn-success" id="confirmarGuardadoFinal">Actualizar</button>
        </div>
      </div>
    </div>
  </div>

  <script src="../../assets/js/jquery.min.js"></script>
  <script src="../../assets/js/bootstrap.min.js"></script>
  <script>
    // Variables PHP con los IDs guardados (Existentes en editar.php)
    const idPaciente = "<?php echo $id_paciente; ?>"; // ID del paciente actual (Nuevo)
    const saved_id_pais_nac = "<?php echo $id_pais_nac; ?>";
    const saved_id_estado_nac = "<?php echo $id_estado_nac; ?>";
    const saved_id_municipio_nac = "<?php echo $id_municipio_nac; ?>";
    const saved_id_estado_dir = "<?php echo $id_estado_dir; ?>";
    const saved_id_municipio_dir = "<?php echo $id_municipio_dir; ?>";
    const saved_id_sector_dir = "<?php echo $id_sector_dir; ?>";

    // Data de Patologías y Alergias (Existente en editar.php)
    const patologiasData = "<?php echo $patologias_data; ?>";
    const alergiasData = "<?php echo $alergias_data; ?>";

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
    $('.modal .close, .modal .btn-default[data-dismiss="modal"], .modal .btn-secondary[data-dismiss="modal"], .modal .btn-second[data-dismiss="modal"]').on('click', function(e) {
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

    $('#avisoModal .close, #avisoModal .btn-secondary').off('click').on('click', function() {
      $('#avisoModal').modal('hide');
    });

    $('#modalGuardarPaciente .close, #modalGuardarPaciente .btn-secondary').off('click').on('click', function() {
      $('#modalGuardarPaciente').modal('hide');
    });


    // ------------------------------------------------------------------
    // LÓGICA GENERAL
    // ------------------------------------------------------------------

    function setMaxDateFor18Years() {
      const today = new Date();
      today.setFullYear(today.getFullYear() - 18);
      const maxDate = today.toISOString().split('T')[0];
      document.getElementById('fechaN').setAttribute('max', maxDate);
    }
    setMaxDateFor18Years();

    // Función para calcular edad (Copiada de pacientes_agregar.php)
    function calcularEdad() {
      var fechaNacimiento = $('#fechaN').val(); // Ajusta el ID según tu HTML
      var campoEdad = $('#edad');

      if (fechaNacimiento) {
        var fechaNac = new Date(fechaNacimiento);
        var hoy = new Date();

        // 1. Verificar si la fecha es mayor a hoy (excede el tiempo)
        if (fechaNac > hoy) {
          mostrarAviso("La fecha de nacimiento no puede ser menor a la fecha actual.");
          campoEdad.val(""); // <-- AQUÍ COLOCAMOS EL VALOR EN VACÍO
          $('#fechaN').val(""); // Opcional: limpiar también la fecha
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
    $('#fechaN').on('change', function() {
      calcularEdad();
    });
    calcularEdad(); // Calcular la edad inicial al cargar


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

    // --- MODIFICACIÓN SOLICITADA: Lógica Analfabeta y Validación de Misiones (Copiada de pacientes_agregar.php) ---
    const selectAnalfabeta = document.getElementById('analfabeta');
    const nivelInstruccion = document.getElementById('nivel_instruccion');
    const mision = document.getElementById('mision');
    const ocupacion = document.getElementById('ocupacion');
    const profesion = document.getElementById('profesion');
    const añosAprobados = document.getElementById('años_aprobados');
    const selectEtnia = document.getElementById('etnia');
    const tipoEtnia = document.getElementById('tipo_etnia');
    const selectDiscapacidad = document.getElementById('discapacidad');
    const tipoDiscapacidad = document.getElementById('tipo_discapacidad');

    const limitesMision = {
      "robinson": 2, // Primaria
      "ribas": 5, // Bachillerato
      "sucre": 6 // Universitario (TSU/Licenciatura)
    };

    const limitesNivel = {
      "bachiller": 5,
      "TSU": 3,
      "licenciatura": 5
    };

    // Función que bloquea/desbloquea campos según el valor de Analfabeta
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
        $('#tipo_etnia').prop('disabled', false).val('');
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
        $('#tipo_discapacidad').prop('disabled', false).val('');
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
    // LÓGICA PARA PATOLOGÍAS Y ALERGIAS (CORREGIDO)
    // =====================================================================

    $('#btn_modal_pat, #btn_modal_ale').tooltip();

    // 1. Funciones para agregar filas HTML
    function agregarFilaPatologia(id_guardado = "", fecha_guardada = "") {
      let optionsHtml = '';
      // Si ya existe un select en pantalla, clonamos su HTML para asegurar que incluya las opciones añadidas por AJAX
      if ($('.select-pat').length > 0) {
        optionsHtml = $('.select-pat').first().html();
      } else {
        // Fallback al PHP inicial si es la primera fila
        optionsHtml = `<option value="">--- Seleccione una patología ---</option>
          <?php
          $conn_pat = new mysqli("localhost", "root", "", "cpt3db");
          $conn_pat->set_charset("utf8");
          $q_pat = $conn_pat->query("SELECT Id_patologia, nombre_patologia, codigo_cie FROM patologias WHERE estatus = 1 ORDER BY nombre_patologia ASC");
          if ($q_pat) {
            while ($p = $q_pat->fetch_assoc()) {
              echo "<option value='{$p['Id_patologia']}' data-nombre='{$p['nombre_patologia']}' data-cie='{$p['codigo_cie']}'>{$p['nombre_patologia']}</option>";
            }
          }
          ?>`;
      }

      let htmlPat = `
        <div class="row fila-pat" style="margin-bottom: 10px;">
            <div class="col-sm-6">
                <div class="input-group">
                    <select class="form-control select-pat">
                        ${optionsHtml}
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

      let $fila = $(htmlPat);
      $('#contenedor_filas_patologias').append($fila);

      if (id_guardado !== "") {
        // Retraso mínimo para garantizar que el DOM reconozca las opciones inyectadas
        setTimeout(() => $fila.find('.select-pat').val(id_guardado.toString().trim()), 10);
      }
      if (fecha_guardada !== "") $fila.find('.input-fecha-pat').val(fecha_guardada.toString().trim());
    }

    function agregarFilaAlergia(id_guardado = "", fecha_guardada = "") {
      let optionsHtml = '';
      // Igual que en patologías: clonamos las opciones del select existente para arrastrar las guardadas por AJAX
      if ($('.select-ale').length > 0) {
        optionsHtml = $('.select-ale').first().html();
      } else {
        optionsHtml = `<option value="">--- Seleccione una alergia ---</option>
          <?php
          $conn_ale = new mysqli("localhost", "root", "", "cpt3db");
          $conn_ale->set_charset("utf8");
          $q_ale = $conn_ale->query("SELECT Id_alergias_conocidas, nombre_alergia FROM alergias_conocidas WHERE estatus = 1 ORDER BY nombre_alergia ASC");
          if ($q_ale) {
            while ($a = $q_ale->fetch_assoc()) {
              echo "<option value='{$a['Id_alergias_conocidas']}' data-nombre='{$a['nombre_alergia']}'>{$a['nombre_alergia']}</option>";
            }
          }
          ?>`;
      }

      let htmlAle = `
        <div class="row fila-ale" style="margin-bottom: 10px;">
            <div class="col-sm-6">
                <div class="input-group">
                    <select class="form-control select-ale">
                        ${optionsHtml}
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

      let $fila = $(htmlAle);
      $('#contenedor_filas_alergias').append($fila);

      if (id_guardado !== "") {
        // Retraso mínimo para garantizar que el DOM reconozca las opciones inyectadas
        setTimeout(() => $fila.find('.select-ale').val(id_guardado.toString().trim()), 10);
      }
      if (fecha_guardada !== "") $fila.find('.input-fecha-ale').val(fecha_guardada.toString().trim());
    }

    // --- PRE-CARGA DE EDICIÓN ---
    function inicializarDatosMedicos() {
      // 1. Cargar Patologías desde BD
      if (patologiasData && patologiasData.trim() !== "") {
        let items = patologiasData.split('||');
        items.forEach(function(item) {
          if(item.trim() !== "") {
              let parts = item.split('::');
              if (parts.length >= 3) {
                let id_pat = parts[0] ? parts[0].trim() : '';
                let fecha_pat = parts[3] ? parts[3].trim() : '';
                agregarFilaPatologia(id_pat, fecha_pat);
              }
          }
        });
        setTimeout(() => $('#guardar_pat_listo').trigger('click'), 50); // Simular guardar asíncrono
      } else {
        agregarFilaPatologia();
      }

      // 2. Cargar Alergias desde BD
      if (alergiasData && alergiasData.trim() !== "") {
        let items = alergiasData.split('||');
        items.forEach(function(item) {
          if(item.trim() !== "") {
              let parts = item.split('::');
              if (parts.length >= 2) {
                let id_ale = parts[0] ? parts[0].trim() : '';
                let fecha_ale = parts[2] ? parts[2].trim() : '';
                agregarFilaAlergia(id_ale, fecha_ale);
              }
          }
        });
        setTimeout(() => $('#guardar_ale_listo').trigger('click'), 50); // Simular guardar asíncrono
      } else {
        agregarFilaAlergia();
      }
    }

    // Botones de agregar y eliminar
    $('#add_fila_pat').click(() => agregarFilaPatologia());
    $('#add_fila_ale').click(() => agregarFilaAlergia());
    $(document).on('click', '.btn-remove-pat', function() {
      $(this).closest('.fila-pat').remove();
    });
    $(document).on('click', '.btn-remove-ale', function() {
      $(this).closest('.fila-ale').remove();
    });

    // Generador de JSON (Al dar click en Listo)
    // CORRECCIÓN: Agregar la 'e' como parámetro de la función
    $('#guardar_pat_listo').click(function(e) {
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

      // CORRECCIÓN: Usar 'e.originalEvent' en lugar de 'event.originalEvent'
      if (errorFecha && e.originalEvent) {
        mostrarAviso("⚠️ Atención: Hay patologías seleccionadas sin fecha de detección.");
        return false;
      }

      $('#patologias_ids').val(JSON.stringify(seleccionadas));
      let boton = $('#btn_modal_pat');
      if (nombres.length > 0) {
        boton.attr('data-original-title', nombres.join(', ')).tooltip('fixTitle').removeClass('input-error');
      } else {
        boton.attr('data-original-title', 'Ninguna seleccionada').tooltip('fixTitle');
      }
      $('#modalPatologias').modal('hide');
    });

    // CORRECCIÓN: Hacer lo mismo con el botón de alergias
    $('#guardar_ale_listo').click(function(e) {
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

      if (errorFecha && e.originalEvent) {
        mostrarAviso("⚠️ Atención: Hay alergias seleccionadas sin fecha de detección.");
        return false;
      }

      $('#alergias_ids').val(JSON.stringify(seleccionadas));
      let boton = $('#btn_modal_ale');
      if (nombres.length > 0) {
        boton.attr('data-original-title', nombres.join(', ')).tooltip('fixTitle').removeClass('input-error');
      } else {
        boton.attr('data-original-title', 'Ninguna seleccionada').tooltip('fixTitle');
      }
      $('#modalAlergias').modal('hide');
    });

    // 3. Modales Buscadores
    let selectDestinoTarget = null;
    $(document).on('click', '.btn-search-pat', function() {
      selectDestinoTarget = $(this).closest('.input-group').find('.select-pat');
      $('#modalBuscarPat').modal('show');
      $('#inputBuscarPat').val('').trigger('keyup');
    });
    $('#inputBuscarPat').on('keyup', function() {
      let texto = $(this).val().toLowerCase();
      let html = '';
      $('.select-pat:first option').not('[value=""]').each(function() {
        let nombre = $(this).text();
        if (nombre.toLowerCase().includes(texto)) html += `<a href="#" class="list-group-item list-group-item-action seleccionar-pat" data-id="${$(this).val()}">${nombre}</a>`;
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
      $('.select-ale:first option').not('[value=""]').each(function() {
        let nombre = $(this).text();
        if (nombre.toLowerCase().includes(texto)) html += `<a href="#" class="list-group-item list-group-item-action seleccionar-ale" data-id="${$(this).val()}">${nombre}</a>`;
      });
      $('#listaResultadosAle').html(html);
    });
    $(document).on('click', '.seleccionar-ale', function(e) {
      e.preventDefault();
      selectDestinoTarget.val($(this).data('id')).trigger('change');
      $('#modalBuscarAle').modal('hide');
    });

    // Validar duplicados
    $(document).on('change', '.select-pat', function() {
      let val = $(this).val();
      if (!val) return;
      if ($('.select-pat').filter(function() {
          return $(this).val() === val;
        }).length > 1) {
        mostrarAviso("⚠️ <b>Atención:</b> Esta patología ya fue seleccionada.");
        $(this).val("");
      }
    });

    $(document).on('change', '.select-ale', function() {
      let val = $(this).val();
      if (!val) return;
      if ($('.select-ale').filter(function() {
          return $(this).val() === val;
        }).length > 1) {
        mostrarAviso("⚠️ <b>Atención:</b> Esta alergia ya fue seleccionada.");
        $(this).val("");
      }
    });

    // =====================================================================
    // LÓGICA DE SÍNTOMAS Y GUARDADO AJAX (Faltante)
    // =====================================================================

    // 1. Abrir Modal de Síntomas
    $('#btn_abrir_sintomas_ajax').click(function() {
      $('#modalSintomasAjax').modal('show');
      // Si está vacío, agregar la primera fila
      if ($('.fila-sintoma-ajax').length === 0) {
        agregarFilaSintomaAjax();
      }
    });

    // 2. Función para agregar fila de síntoma
    function agregarFilaSintomaAjax() {
      let htmlSintoma = `
        <div class="row fila-sintoma-ajax" style="margin-bottom: 10px;">
            <div class="col-sm-10">
                <select class="form-control select-sintoma-ajax">
                    <option value="">--- Seleccione un síntoma ---</option>
                    <?php echo $opciones_sintomas_global; ?>
                </select>
            </div>
            <div class="col-sm-2">
                <button type="button" class="btn btn-danger btn-remove-sintoma-ajax">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        </div>`;
      $('#contenedor_filas_sintomas_ajax').append(htmlSintoma);
    }

    // 3. Eventos de los botones de síntomas
    $('#add_fila_sintoma_ajax').click(() => agregarFilaSintomaAjax());
    $(document).on('click', '.btn-remove-sintoma-ajax', function() {
      $(this).closest('.fila-sintoma-ajax').remove();
    });

    // 4. Confirmar selección de síntomas y pasarlos al input oculto
    $('#btn_confirmar_sintomas_ajax').click(function() {
      let seleccionados = [];
      $('.select-sintoma-ajax').each(function() {
        let val = $(this).val();
        if (val) seleccionados.push(val);
      });
      $('#ajax_sintomas_ids').val(seleccionados.join(','));

      let boton = $('#btn_abrir_sintomas_ajax');
      if (seleccionados.length > 0) {
        boton.attr('data-original-title', seleccionados.length + ' síntoma(s) seleccionado(s)').tooltip('fixTitle').removeClass('btn-info').addClass('btn-success');
      } else {
        boton.attr('data-original-title', 'Ninguno seleccionado').tooltip('fixTitle').removeClass('btn-success').addClass('btn-info');
      }
      $('#modalSintomasAjax').modal('hide');
    });

    // 5. Guardar Nueva Patología vía AJAX (Conecta con el PHP de arriba)
    $('#btn_guardar_ajax_pat').click(function() {
      let nombre = $('#ajax_nombre_pat').val();
      let cie = $('#ajax_cie_pat').val();
      let contagiosa = $('#ajax_contagiosa_pat').val();
      let sintomas = $('#ajax_sintomas_ids').val();

      if (!nombre) {
        mostrarAviso("El nombre de la patología es obligatorio.");
        return;
      }

      $.ajax({
        url: window.location.href, // Envía a esta misma página
        method: 'POST',
        data: {
          ajax_nueva_patologia: 1,
          nombre_patologia: nombre,
          codigo_cie: cie,
          enfermedad_contagiosa: contagiosa,
          sintomas_ids: sintomas
        },
        dataType: 'json',
        success: function(res) {
          if (res.success) {
            // Añadir al selector dinámicamente
            let nuevaOpcion = `<option value="${res.id}" data-nombre="${res.nombre}" data-cie="${res.cie}">${res.nombre}</option>`;
            $('.select-pat').append(nuevaOpcion);

            // Limpiar modal
            $('#modalNuevaPatologia').modal('hide');
            $('#formAjaxPatologia')[0].reset();
            $('#contenedor_filas_sintomas_ajax').empty();
            $('#ajax_sintomas_ids').val('');
            $('#btn_abrir_sintomas_ajax').removeClass('btn-success').addClass('btn-info').attr('data-original-title', 'Ninguno seleccionado').tooltip('fixTitle');

            mostrarAviso("Patología guardada con éxito.");
          } else {
            mostrarAviso(res.error);
          }
        }
      });
    });

    // 6. Guardar Nueva Alergia vía AJAX (Conecta con el PHP de arriba)
    $('#btn_guardar_ajax_ale').click(function() {
      let nombre = $('#ajax_nombre_ale').val();

      if (!nombre) {
        mostrarAviso("El nombre de la alergia es obligatorio.");
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
            // 1. Añadir la nueva opción a todos los selects de alergias actuales
            let nuevaOpcion = `<option value="${res.id}" data-nombre="${res.nombre}">${res.nombre}</option>`;
            $('.select-ale').append(nuevaOpcion);

            // 2. Buscar si hay algún select de alergia vacío en pantalla
            let selectVacio = $('.select-ale').filter(function() { return !$(this).val(); }).first();

            if (selectVacio.length > 0) {
              // Si hay uno vacío, seleccionamos la nueva alergia ahí
              selectVacio.val(res.id).trigger('change');
            } else {
              // Si no hay selects vacíos, agregamos una nueva fila y le pasamos el ID directamente
              agregarFilaAlergia(res.id, ""); 
            }

            // 3. Cerrar modal y limpiar
            $('#modalNuevaAlergia').modal('hide');
            $('#formAjaxAlergia')[0].reset();
            mostrarAviso("Alergia guardada con éxito.");
          } else {
            mostrarAviso(res.error);
          }
        }
      });
    });

    // 7. Guardar Nuevo Síntoma vía AJAX (Faltaba este bloque)
    $('#btnGuardarSintomaBD').click(function() {
      let nombre = $('#nombre_nuevo_sintoma').val();

      if (!nombre) {
        mostrarAviso("El nombre del síntoma es obligatorio.");
        return;
      }

      $.ajax({
        url: window.location.href,
        method: 'POST',
        data: {
          ajax_nuevo_sintoma: 1,
          nombre_sintoma: nombre
        },
        dataType: 'json',
        success: function(res) {
          if (res.success) {
            // Añadir al selector dinámicamente
            let nuevaOpcion = `<option value="${res.id}">${res.nombre}</option>`;
            $('.select-sintoma-ajax').append(nuevaOpcion);

            // Si quieres que se autoseleccione en el último select vacío
            $('.select-sintoma-ajax').last().val(res.id);

            // Limpiar modal
            $('#modalNuevoSintoma').modal('hide');
            $('#nombre_nuevo_sintoma').val('');

            mostrarAviso("Síntoma guardado con éxito.");
          } else {
            mostrarAviso(res.error);
          }
        }
      });
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

      if (tabSelector === '#ocupacion_estudios') {
        // 2a. Validación de Años Aprobados
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

      if (!esValido) {
        const mensajeError = "Por favor, corrija los siguientes errores:<br>" + errores.filter((v, i, a) => a.indexOf(v) === i).join('<br>');
        mostrarAviso(mensajeError, '⚠️ Error de Validación', 'modal-header-danger');
      }

      if (tabSelector === '#salud_otros') {

      }

      if (!esValido && errores.length > 0) {
        mostrarAviso('⚠️ Errores de Formulario:<ul><li>' + errores.join('</li><li>') + '</li></ul>');
      }

      return esValido;
    }

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
      $('#modalGuardarPaciente').modal('hide');
      // El action del formulario ya apunta a ../../cfg/editar_paciente.php
      $('#formularioPaciente').submit();
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