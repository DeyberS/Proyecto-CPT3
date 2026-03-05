<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Pacientes | Editar</title>
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
                        <option value="E" <?php echo ($row['tipo_cedula'] == 'E' ? 'selected' : ''); ?>>E-</option>
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
                      <p>Estado de nacimiento (*):</p>
                      <select name="estado_nacimiento" id="estado_nacimiento" class="form-control" required>
                        <option value="<?php echo $id_estado_nac; ?>" selected>
                          <?php echo ($id_estado_nac ? "Cargando..." : "--- Seleccione Un Estado ---"); ?>
                        </option>
                      </select>
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-3">
                      <p>Municipio de nacimiento (*):</p>
                      <select name="municipio_nacimiento" id="municipio_nacimiento" class="form-control" required>
                        <option value="<?php echo $id_municipio_nac; ?>" selected>
                          <?php echo ($id_municipio_nac ? "Cargando..." : "--- Seleccione Un Municpio ---"); ?>
                        </option>
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
                    <p>Estado (*):</p>
                    <select name="estado" id="estado" class="form-control" required>
                      <option value="<?php echo $id_estado_dir; ?>" selected>
                        <?php echo ($id_estado_dir ? "Cargando..." : "--- Seleccione Un Estado ---"); ?>
                      </option>
                      <?php
                      // Carga de estados, asumiendo Id_Pais = 1 es Venezuela
                      $result_dir = $conexion->query("SELECT Id_Estado, nombre_estado, Id_Pais FROM estado HAVING Id_Pais = 1");
                      while ($row_dir = $result_dir->fetch_assoc()) {
                        $selected = ($row_dir['Id_Estado'] == $id_estado_dir) ? 'selected' : "";
                        echo "<option value='{$row_dir['Id_Estado']}'{$selected}>{$row_dir['nombre_estado']}</option>";
                      }
                      ?>
                    </select>
                  </div>
                  <label class="control-label"></label>
                  <div class="col-sm-4">
                    <p>Municipio (*):</p>
                    <select name="municipio" id="municipio" class="form-control" required>
                      <option value="<?php echo $id_municipio_dir; ?>" selected>
                        <?php echo ($id_municipio_dir ? "Cargando..." : "--- Seleccione Un Municipio ---"); ?>
                      </option>
                    </select>
                  </div>
                  <label class="control-label"></label>
                  <div class="col-sm-3">
                    <p>Sector (*):</p>
                    <select name="sector" id="sector" class="form-control" required>
                      <option value="<?php echo $id_sector_dir; ?>" selected>
                        <?php echo ($id_sector_dir ? "Cargando..." : "--- Seleccione Un Sector ---"); ?>
                      </option>
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
                  <div class="col-sm-5">
                    <p>Patologias:</p>
                    <input type="hidden" name="patologias_ids" id="patologias_ids" value="">
                    <input type="hidden" name="patologias_fechas" id="patologias_fechas" value="">
                    <div id="patologias_agregadas" class="well well-sm" style="height: auto; min-height: 34px;">
                      <span class="text-muted">Ninguna Patologia seleccionada.</span>
                    </div>
                  </div>
                  <div class="col-sm-1" style="margin-top: 30px;">
                    <button type="button" class="form-control pull-right bt-sm btn-primary" data-toggle="modal" data-target="#modalPatologias">
                      <img src="../../recursos/imagenes/iconos/editar.png" height="20px" width="20px">
                    </button>
                  </div>
                  <label class="control-label"></label>
                  <div class="col-sm-4">
                    <p>Alergias:</p>
                    <input type="hidden" name="alergias_ids" id="alergias_ids" value="">
                    <input type="hidden" name="alergias_fechas" id="alergias_fechas" value="">
                    <div id="alergias_agregadas" class="well well-sm" style="height: auto; min-height: 34px;">
                      <span class="text-muted">Ninguna Alergia seleccionada.</span>
                    </div>
                  </div>
                  <div class="col-sm-1" style="margin-top: 30px;">
                    <button type="button" class="form-control pull-right bt-sm btn-primary" data-toggle="modal" data-target="#modalAlergias">
                      <img src="../../recursos/imagenes/iconos/editar.png" height="20px" width="20px">
                    </button>
                  </div>
                  <br><br><br><br><br><br>
                  <label class="control-label"></label>
                  <div class="col-sm-5">
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
                  <label class="control-label"></label>
                  <div class="col-sm-1">
                    <p>Discap.:</p>
                    <select name="discapacidad" id="discapacidad" class="form-control" required>
                      <option value="No" <?php echo ($row['discapacidad'] == 'No' ? 'selected' : ''); ?>">No</option>
                      <option value="Si" <?php echo ($row['discapacidad'] == 'Si' ? 'selected' : ''); ?>">Si</option>
                    </select>
                  </div>
                  <div class="col-sm-5">
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
          <div class="col-sm-6 pull-left">
            <div class="form-group">
              <label for="modal_fecha_patologia">Fecha detección (*)</label>
              <input type="date" id="modal_fecha_patologia" class="form-control" max="<?php echo date('Y-m-d'); ?>">
            </div>
          </div>
          <div class="clearfix"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
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
          <div class="col-sm-6 pull-left">
            <div class="form-group">
              <label for="modal_fecha_alergia">Fecha detección (*)</label>
              <input type="date" id="modal_fecha_alergia" class="form-control" max="<?php echo date('Y-m-d'); ?>">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
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


    // --- LÓGICA DE VALIDACIÓN DE CÉDULA --- (Adaptada de pacientes_agregar.php)
    const cedulaInput = document.getElementById('cedula');
    const tipoCedulaSelect = document.getElementById('tipo_cedula');

    function validarCedulaFormato() {
      const cedula = parseInt(cedulaInput.value.trim());
      const tipo = tipoCedulaSelect.value;
      let esValido = true;

      $(cedulaInput).removeClass('input-error');
      $(tipoCedulaSelect).removeClass('input-error');

      if (cedulaInput.value.trim() === "") {
        $(cedulaInput).addClass('input-error');
        return true;
      }

      if (isNaN(cedula) || cedula <= 0) {
        $(cedulaInput).addClass('input-error');
        esValido = false;
      } else if (tipo === 'V' && cedula > 80000000) {
        mostrarAviso('Para el tipo V-, la cédula no debe ser mayor a 80.000.000', '⚠️');
        $(cedulaInput).addClass('input-error');
        esValido = false;
      } else if (tipo === 'E' && cedula < 80000000) {
        mostrarAviso('Para el tipo E-, la cédula no debe ser menor a 80.000.000', '⚠️');
        $(cedulaInput).addClass('input-error');
        esValido = false;
      }

      return esValido;
    }

    // Se aplica la validación en el cambio y desenfoque (blur)
    $(cedulaInput).on('blur', validarCedulaFormato);
    $(tipoCedulaSelect).on('change', validarCedulaFormato);


    function loadAndSelect(parentId, targetId, url, keyName, savedId) {
      const $targetSelect = $('#' + targetId);
      $targetSelect.html(`<option value="${savedId}" selected>Cargando...</option>`);

      return new Promise((resolve) => {
        if (!parentId) {
          $targetSelect.html('<option value="">--- Seleccione Una Opción ---</option>');
          resolve(null);
          return;
        }

        $.ajax({
          url: url + '?' + keyName + '=' + parentId,
          method: 'GET',
          dataType: 'json',
          success: function(data) {
            $targetSelect.html('<option value="">--- Seleccione Una Opción ---</option>');
            data.forEach(item => {
              // Asumiendo que los campos de retorno son consistentes (Id_Estado, nombre_estado, Id_Municipio, nombre_municipio, Id_Sector, nombre_sector)
              const idField = item.Id_Estado || item.Id_Municipio || item.Id_Sector;
              const nameField = item.nombre_estado || item.nombre_municipio || item.nombre_sector;
              const isSelected = (idField.toString() === savedId.toString()) ? 'selected' : '';
              $targetSelect.append(`<option value="${idField}" ${isSelected}>${nameField}</option>`);
            });
            resolve(savedId);
          },
          error: function() {
            $targetSelect.html('<option value="">Error al cargar datos</option>');
            resolve(null);
          }
        });
      });
    }

    // --- ENLACE DE EVENTOS PARA CARGA DINÁMICA DE UBICACIÓN (Copiada y adaptada de pacientes_agregar.php) ---

    // 1. NACIMIENTO: PAIS -> ESTADO
    document.getElementById('pais_nacimiento').addEventListener('change', function() {
      const paisId = this.value;
      const estadoSelect = document.getElementById('estado_nacimiento');
      const municipioSelect = document.getElementById('municipio_nacimiento');
      estadoSelect.innerHTML = '<option value="">--- Seleccione Un Estado ---</option>';
      municipioSelect.innerHTML = '<option value="">--- Seleccione Un Municpio ---</option>';

      if (paisId) {
        loadAndSelect(paisId, 'estado_nacimiento', 'get/get_estados.php', 'Id_Pais', '').then(() => {
          // No hacemos nada más aquí, la carga se detiene hasta que el usuario seleccione un estado
        });
      }
    });

    // 2. NACIMIENTO: ESTADO -> MUNICIPIO
    document.getElementById('estado_nacimiento').addEventListener('change', function() {
      const estadoId = this.value;
      const municipioSelect = document.getElementById('municipio_nacimiento');
      municipioSelect.innerHTML = '<option value="">--- Seleccione Un Municpio ---</option>';

      if (estadoId) {
        loadAndSelect(estadoId, 'municipio_nacimiento', 'get/get_municipios.php', 'Id_Estado', '');
      }
    });

    // 3. DIRECCIÓN: ESTADO -> MUNICIPIO
    document.getElementById('estado').addEventListener('change', function() {
      const estadoId = this.value;
      const municipioSelect = document.getElementById('municipio');
      const sectorSelect = document.getElementById('sector');
      municipioSelect.innerHTML = '<option value="">--- Seleccione Un Municipio ---</option>';
      sectorSelect.innerHTML = '<option value="">--- Seleccione Un Sector ---</option>';

      if (estadoId) {
        loadAndSelect(estadoId, 'municipio', 'get/get_municipios.php', 'Id_Estado', '');
      }
    });

    // 4. DIRECCIÓN: MUNICIPIO -> SECTOR
    document.getElementById('municipio').addEventListener('change', function() {
      const municipioId = this.value;
      const sectorSelect = document.getElementById('sector');
      sectorSelect.innerHTML = '<option value="">--- Seleccione Un Sector ---</option>';

      if (municipioId) {
        loadAndSelect(municipioId, 'sector', 'get/get_sectores.php', 'Id_Municipio', '');
      }
    });


    // --- INICIALIZACIÓN DE CARGA DE DATOS AL CARGAR LA PÁGINA (EXISTENTE EN EDITAR.PHP, PERO REFINADA) ---
    document.addEventListener('DOMContentLoaded', () => {
      // 1. LÓGICA DE NACIMIENTO (Tab 1)
      const initNacimiento = loadAndSelect(saved_id_pais_nac, 'estado_nacimiento', 'get/get_estados.php', 'Id_Pais', saved_id_estado_nac);

      initNacimiento
        .then((savedStateId) => {
          if (savedStateId) {
            return loadAndSelect(savedStateId, 'municipio_nacimiento', 'get/get_municipios.php', 'Id_Estado', saved_id_municipio_nac);
          }
        })
        .catch(error => console.error('Fallo en la cadena de Nacimiento:', error));

      // 2. LÓGICA DE DIRECCIÓN (Tab 3)
      const initDireccionMun = loadAndSelect(saved_id_estado_dir, 'municipio', 'get/get_municipios.php', 'Id_Estado', saved_id_municipio_dir);

      initDireccionMun
        .then((savedMunId) => {
          if (savedMunId) {
            return loadAndSelect(savedMunId, 'sector', 'get/get_sectores.php', 'Id_Municipio', saved_id_sector_dir);
          }
        })
        .catch(error => console.error('Fallo en la cadena de Dirección:', error));


      // 3. Inicializar Patologías y Alergias
      if (typeof parseData === 'function') {
        // Se llama al final de los scripts para parsear los datos de PHP y cargarlos.
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
    // LÓGICA PARA PATOLOGÍAS Y ALERGIAS (Copiada y adaptada de pacientes_agregar.php)
    // =====================================================================

    // --- INICIALIZACIÓN DE ARRAYS CON DATOS DE EDICIÓN ---

    // Función de ayuda para parsear datos (Id::Nombre::CIE-10::Fecha o Id::Nombre::Fecha)
    function parseData(dataString, separator, itemParser) {
      if (!dataString) return [];
      return dataString.split(separator).map(itemParser).filter(item => item);
    }

    // Parsear patologías
    let patologiasSeleccionadas = [];
    // Parsear alergias
    let alergiasSeleccionadas = [];

    function inicializarDatosMedicos() {

      patologiasSeleccionadas = parseData(patologiasData, '||', (item) => {
        const parts = item.split('::');
        if (parts.length >= 3) {
          return {
            id: parts[0],
            nombre: parts[1],
            codigo_cie: parts[2],
            fecha: parts[3] || ''
          };
        }
        return null;
      });

      alergiasSeleccionadas = parseData(alergiasData, '||', (item) => {
        const parts = item.split('::');
        if (parts.length >= 2) {
          return {
            id: parts[0],
            nombre: parts[1],
            fecha: parts[2] || ''
          };
        }
        return null;
      });

      updateMainDisplay();
    }


    // --- Funciones de Patologías ---

    document.getElementById('modal_patologia').addEventListener('change', function() {
      const patologiaId = this.value;
      const $selectedOption = $(this).find('option:selected');
      if (patologiaId && patologiaId !== 'disabled') {
        document.getElementById('modal_codigo_cie').value = $selectedOption.data('cie') || 'No disponible';
      } else {
        document.getElementById('modal_codigo_cie').value = 'No disponible';
      }
    });

    function addPatologia() {

      const patologiaSelect = document.getElementById('modal_patologia');
      const patologiaId = patologiaSelect.value;
      const $selectedOption = $(patologiaSelect).find('option:selected');

      const fecha = document.getElementById('modal_fecha_patologia').value;

      if (!patologiaId || patologiaId === 'disabled') {
        mostrarAviso('Por favor, seleccione una patología válida.');
        return;
      }

      const nombre = $selectedOption.data('nombre');
      const codigo_cie = $selectedOption.data('cie');

      const existe = patologiasSeleccionadas.some(p => p.id == patologiaId);
      if (existe) {
        mostrarAviso(`La patología '${nombre}' ya está en la lista.`);
        return;
      }

      patologiasSeleccionadas.push({
        id: patologiaId,
        nombre: nombre,
        codigo_cie: codigo_cie,
        fecha: fecha
      });

      renderPatologiasModal();
      updateMainDisplay();
      mostrarAviso(`✅ Patología '${nombre}' añadida temporalmente.`);
    }

    document.getElementById('btnAgregarPatologiaTemporal')
      .addEventListener('click', addPatologia);


    function renderPatologiasModal() {

      const lista = document.getElementById('lista_patologias_seleccionadas');
      lista.innerHTML = '';

      if (patologiasSeleccionadas.length === 0) {
        lista.innerHTML = '<span class="text-muted">Ninguna patología en la lista temporal.</span>';
        lista.classList.remove('well', 'well-sm');
        return;
      }

      lista.classList.add('well', 'well-sm');

      patologiasSeleccionadas.forEach(p => {
        lista.innerHTML += `
      <span class="label label-primary" style="margin-right:5px;margin-bottom:5px;display:inline-block;font-size:14px;padding:6px 10px;">
        ${p.nombre} (${p.codigo_cie}) - ${p.fecha || 'Sin fecha'}
        <a href="#" data-id="${p.id}" class="remove-patologia text-white" style="margin-left:5px;">&times;</a>
      </span>`;
      });

      lista.querySelectorAll('.remove-patologia')
        .forEach(el => el.addEventListener('click', removePatologia));
    }

    function removePatologia(e) {

      e.preventDefault();
      const idQuitar = e.target.getAttribute('data-id');

      const nombreQuitar = patologiasSeleccionadas.find(p => p.id == idQuitar)?.nombre || 'Patología';

      patologiasSeleccionadas = patologiasSeleccionadas.filter(p => p.id != idQuitar);

      renderPatologiasModal();
      updateMainDisplay();
      mostrarAviso(`Patología '${nombreQuitar}' eliminada.`);
    }

    $('#modalPatologias').on('show.bs.modal', renderPatologiasModal);


    // --- Funciones de Alergias ---

    function addAlergia() {

      const alergiaSelect = document.getElementById('modal_alergia');
      const alergiaId = alergiaSelect.value;
      const $selectedOption = $(alergiaSelect).find('option:selected');

      const fecha = document.getElementById('modal_fecha_alergia').value;

      if (!alergiaId || alergiaId === 'disabled') {
        mostrarAviso('Por favor, seleccione una alergia válida.');
        return;
      }

      const nombre = $selectedOption.data('nombre');

      const existe = alergiasSeleccionadas.some(a => a.id == alergiaId);
      if (existe) {
        mostrarAviso(`La alergia '${nombre}' ya está en la lista.`);
        return;
      }

      alergiasSeleccionadas.push({
        id: alergiaId,
        nombre: nombre,
        fecha: fecha
      });

      renderAlergiasModal();
      updateMainDisplay();
      mostrarAviso(`✅ Alergia '${nombre}' añadida temporalmente.`);
    }

    document.getElementById('btnAgregarAlergiaTemporal')
      .addEventListener('click', addAlergia);


    function renderAlergiasModal() {

      const lista = document.getElementById('lista_alergias_seleccionadas');
      lista.innerHTML = '';

      if (alergiasSeleccionadas.length === 0) {
        lista.innerHTML = '<span class="text-muted">Ninguna alergia en la lista temporal.</span>';
        return;
      }

      alergiasSeleccionadas.forEach(a => {
        lista.innerHTML += `
      <span class="label label-primary" style="margin-right:5px;margin-bottom:5px;display:inline-block;font-size:14px;padding:6px 10px;">
        ${a.nombre} - ${a.fecha || 'Sin fecha'}
        <a href="#" data-id="${a.id}" class="remove-alergia text-white" style="margin-left:5px;">&times;</a>
      </span>`;
      });

      lista.querySelectorAll('.remove-alergia')
        .forEach(el => el.addEventListener('click', removeAlergia));
    }

    function removeAlergia(e) {

      e.preventDefault();

      const idQuitar = e.target.getAttribute('data-id');

      const nombreQuitar = alergiasSeleccionadas.find(a => a.id == idQuitar)?.nombre || 'Alergia';

      alergiasSeleccionadas = alergiasSeleccionadas.filter(a => a.id != idQuitar);

      renderAlergiasModal();
      updateMainDisplay();
      mostrarAviso(`Alergia '${nombreQuitar}' eliminada.`);
    }

    $('#modalAlergias').on('show.bs.modal', renderAlergiasModal);


    // --- FUNCIÓN PRINCIPAL ---

    function updateMainDisplay() {

      document.getElementById('patologias_ids').value =
        patologiasSeleccionadas.map(p => p.id).join(',');

      const patologiasDiv = document.getElementById('patologias_agregadas');
      patologiasDiv.innerHTML = '';

      if (patologiasSeleccionadas.length === 0) {
        patologiasDiv.innerHTML = '<span class="text-muted">Ninguna patología seleccionada.</span>';
      } else {
        patologiasSeleccionadas.forEach(p => {
          patologiasDiv.innerHTML += `
        <span class="text">
          ${p.nombre},
        </span>`;
        });
      }

      document.getElementById('patologias_fechas').value =
        patologiasSeleccionadas
        .map(p => p.id + ':' + (p.fecha || ''))
        .join(',');

      document.getElementById('alergias_fechas').value =
        alergiasSeleccionadas
        .map(a => a.id + ':' + (a.fecha || ''))
        .join(',');

      document.getElementById('alergias_ids').value =
        alergiasSeleccionadas.map(a => a.id).join(',');

      const alergiasDiv = document.getElementById('alergias_agregadas');
      alergiasDiv.innerHTML = '';

      if (alergiasSeleccionadas.length === 0) {
        alergiasDiv.innerHTML = '<span class="text-muted">Ninguna alergia seleccionada.</span>';
      } else {
        alergiasSeleccionadas.forEach(a => {
          alergiasDiv.innerHTML += `
        <span class="text">
          ${a.nombre},
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
        if (validarCedulaFormato() === false) {
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
          errores.push("El paciente debe ser mayor de **18 años** (Fecha de Nacimiento).");
          $('#fechaN').addClass('input-error');
          esValido = false;
        }

        const email = $('#email').val().trim();
        if (email !== "" && (email.indexOf('@') === -1 || email.indexOf('.') === -1)) {
          errores.push("El campo **Email** debe tener un formato válido (ej: nombre@dominio.com).");
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
            errores.push("El campo **Años Aprobados** es obligatorio si seleccionó 'Sí' en Analfabeta.");
            $('#años_aprobados').addClass('input-error');
            esValido = false;
          } else if (!isNaN(max) && años > max) {
            errores.push("Los **Años Aprobados** exceden el máximo permitido para esta Misión (" + max + " años).");
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
        // Validación de Patologías (marcado con el (*) en el PHP corregido)
        if (patologiasSeleccionadas.length === 0) {
          errores.push("Debe seleccionar al menos **una Patología**.");
          $('#patologias_agregadas').addClass('input-error');
          esValido = false;
        }
        // Validación de Grupo Sanguíneo ya está cubierta por el [required]
      }

      if (!esValido && errores.length > 0) {
        mostrarAviso('⚠️ **Errores de Formulario:**<ul><li>' + errores.join('</li><li>') + '</li></ul>');
      }

      return esValido;
    }

    // --- LÓGICA DE NAVEGACIÓN (Copiada de pacientes_agregar.php) ---
    $('.next-tab').on('click', async function() {
      const $btn = $(this);
      const tabActualSelector = $btn.data('tab-actual');
      const tabSiguienteName = $btn.data('tab-siguiente');
      const nextTabLink = $(`.nav-tabs li[data-tab-name="${tabSiguienteName}"] a`);

      $btn.prop('disabled', false).text('Guardar');

      const esValido = await validarPestana(tabActualSelector);

      if (esValido) {
        if (tabSiguienteName === 'confirmar') {
          $('#modalGuardarPaciente').modal('show');
        } else {
          const $siguienteTabLi = $(`.nav-tabs li[data-tab-name="${tabSiguienteName}"]`);
          // 1. Quitar la clase disabled-tab y la clase active
          $('.nav-tabs li').removeClass('active');
          $('.tab-content .tab-pane').removeClass('active');
          $siguienteTabLi.removeClass('disabled-tab').addClass('active');

          // 2. Activar la pestaña siguiente
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