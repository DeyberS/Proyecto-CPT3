<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Pacientes Menores | Editar</title>
  <?php
  // Se asume que incluye los estilos y scripts base
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
        Editar Paciente Menor de Edad
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-users"></i>Pacientes Menores</a></li>
        <li class="active"><a href="#"><i class="fa fa-user-plus"></i>Editar</a></li>
      </ol>
    </section>
<?php
  
              // Bloque PHP para cargar datos (se mantiene)
    include("../../cfg/conexion.php");
    $sql = "SELECT 
    p.id, p.email, p.nombre, p.apellido, p.tipo_cedula, p.cedula, p.fecha_nacimiento, p.genero,
    dp.etnia, dp.tipo_etnia, dp.nivel_instruccion, dp.mision, dp.años_aprobados, dp.parentesco, dp.discapacidad, dp.tipo_discapacidad, dp.Id_representante,
    hm.grupo_sanguineo,
    d.avenida_calle, d.referencia, d.tiempo_residencia, d.tiempo, d.Id_sector,  /* DATOS DE DIRECCIÓN */
    ln.Id_municipio AS Id_Municipio_Nac,  /* ID DE MUNICIPIO DE NACIMIENTO */
    munnac.Id_Estado AS Id_Estado_Nac,   /* ID DE ESTADO DE NACIMIENTO */
    estnac.Id_Pais AS Id_Pais_Nac,      /* ID DE PAÍS DE NACIMIENTO */
    direst.Id_Pais AS Id_Pais_Dir,       
    dirsec.Id_Municipio AS Id_Municipio_Dir,
    dirmun.Id_Estado AS Id_Estado_Dir,

    /* DATOS DEL MENOR OBTENIDOS CON ALIAS 'p' */
    p.id AS id_menor, 
    p.nombre AS nombre_menor, 
    p.apellido AS apellido_menor, 
    p.tipo_cedula AS tipo_cedula_menor, 
    p.cedula AS documento_menor,
    p.fecha_nacimiento AS fecha_nacimiento_menor,
    p.genero AS genero_menor,
    dp.etnia AS etnia_menor,
    dp.tipo_etnia AS etnia_tipo_menor,
    dp.analfabeta AS analfabeta_menor, /* AÑADIDO: Analfabeta para el menor */
    dp.nivel_instruccion AS instruccion_menor,
    dp.discapacidad AS discapacidad_menor,
    dp.tipo_discapacidad AS discapacidad_tipo_menor,
    
    /* DATOS DEL REPRESENTANTE OBTENIDOS CON ALIAS 'r' */
    r.id AS id_representante, 
    r.nombre AS nombre_representante, 
    r.apellido AS apellido_representante,
    r.genero AS genero_representante, 
    r.email AS email_representante, 
    r.tipo_cedula AS tipo_cedula_representante, 
    r.cedula AS cedula_representante,
    r.email AS email_representante,
    dp.parentesco AS relacion_menor,
    
    /* TELÉFONO DEL REPRESENTANTE (usando alias trp y ptrp) */
    trp.telefono AS telefono_representante,
    ptrp.Id AS id_prefijo_representante,
    
    /* AGREGANDO DATOS DE PATOLOGÍAS Y ALERGIAS ASOCIADAS */
    GROUP_CONCAT(DISTINCT CONCAT(pat.Id_patologia, '::', pat.nombre_patologia, '::', pat.codigo_cie) SEPARATOR '||') AS patologias_data,
    GROUP_CONCAT(DISTINCT CONCAT(al.Id_alergias_conocidas, '::', al.nombre_alergia) SEPARATOR '||') AS alergias_data
    
FROM persona p
LEFT JOIN telefonos_personas tp ON p.id = tp.Id_persona
LEFT JOIN prefijos_telefonos pt ON tp.Id_prefijo = pt.Id
LEFT JOIN historial_medico hm ON p.id = hm.Id_persona 
LEFT JOIN detalle_paciente_menor dp ON p.id = dp.Id_persona 
LEFT JOIN lugar_nacimiento ln ON p.id = ln.Id_persona
LEFT JOIN municipio munnac ON ln.Id_municipio = munnac.Id_Municipio
LEFT JOIN estado estnac ON munnac.Id_Estado = estnac.Id_Estado
LEFT JOIN direccion d ON p.id = d.Id_persona
LEFT JOIN sector dirsec ON d.Id_sector = dirsec.Id_Sector
LEFT JOIN municipio dirmun ON dirsec.Id_Municipio = dirmun.Id_Municipio
LEFT JOIN estado direst ON dirmun.Id_Estado = direst.Id_Estado

LEFT JOIN persona r ON dp.Id_representante = r.id
LEFT JOIN telefonos_personas trp ON r.id = trp.Id_persona
LEFT JOIN prefijos_telefonos ptrp ON trp.Id_prefijo = ptrp.Id

LEFT JOIN historial_patologias hp ON hm.id_historial = hp.Id_Historial AND p.id = hp.Id_persona
LEFT JOIN patologias pat ON hp.Id_patologia = pat.Id_patologia
LEFT JOIN historial_alergias ha ON hm.id_historial = ha.Id_Historial AND p.id = ha.Id_persona
LEFT JOIN alergias_conocidas al ON ha.Id_alergia = al.Id_alergias_conocidas

WHERE p.id =" . $_GET['Id'] . "
GROUP BY p.id";

              $resultado = $conexion->query($sql);
              $row = $resultado->fetch_assoc();
              $id_paciente = $row['Id'] ?? '';

              // Variables PHP para JavaScript
              $id_prefijo = $row['Id_prefijo'] ?? '';
              $id_pais_nac = $row['Id_Pais_Nac'] ?? '';
              $id_pais_dir = $row['Id_Pais_Dir'] ?? '';
              $id_estado_nac = $row['Id_Estado_Nac'] ?? '';
              $id_municipio_nac = $row['Id_Municipio_Nac'] ?? '';
              $id_estado_dir = $row['Id_Estado_Dir'] ?? '';
              $id_municipio_dir = $row['Id_Municipio_Dir'] ?? '';
              $id_sector_dir = $row['Id_sector'] ?? '';
              $patologias_data = $row['patologias_data'] ?? '';
              $alergias_data = $row['alergias_data'] ?? '';

              // Variables para el Menor
              $nombre_menor = $row['nombre_menor'] ?? '';
              $apellido_menor = $row['apellido_menor'] ?? '';
              $tipo_cedula_menor = $row['tipo_cedula_menor'] ?? 'PN';
              $documento_menor = $row['documento_menor'] ?? '';
              $fecha_nacimiento_menor = $row['fecha_nacimiento_menor'] ?? '';
              $genero_menor = $row['genero_menor'] ?? '';
              $etnia_menor = $row['etnia_menor'] ?? '';
              $etnia_tipo_menor = $row['etnia_tipo_menor'] ?? '';
              $analfabeta_menor = $row['analfabeta_menor'] ?? '';
              $instruccion_menor = $row['instruccion_menor'] ?? '';
              $mision_menor = $row['mision'] ?? '';
              $años_aprobados_menor = $row['años_aprobados'] ?? '';
              $discapacidad = $row['discapacidad_menor'] ?? '';
              $discapacidad_tipo_menor = $row['discapacidad_tipo_menor'] ?? '';

              // Variables para el Representante
              $id_representante = $row['id_representante'] ?? '';
              $nombre_representante = $row['nombre_representante'] ?? '';
              $apellido_representante = $row['apellido_representante'] ?? '';
              $tipo_cedula_representante = $row['tipo_cedula_representante'] ?? 'V';
              $cedula_representante = $row['cedula_representante'] ?? '';
              $email_representante = $row['email_representante'] ?? '';
              $genero_representante = $row['genero_representante'] ?? '';
              $relacion_menor = $row['relacion_menor'] ?? '';
              $telefono_representante = $row['telefono_representante'] ?? '';
              $id_prefijo_representante = $row['id_prefijo_representante'] ?? '';

              // Variables para Dirección
              $avenida_calle = $row['avenida_calle'] ?? '';
              $referencia = $row['referencia'] ?? '';
              $tiempo_residencia = $row['tiempo_residencia'] ?? '';
              $tiempo = $row['tiempo'] ?? '';

              // Variables para Salud
              $grupo_sanguineo = $row['grupo_sanguineo'] ?? '';

?>
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
                <form id="formularioPaciente" action="../../cfg/editar/editar_paciente_menor.php" class="form-group" method="POST" novalidate>
                <input type="hidden" name="Id" value="<?= $row['id']; ?>">
                <input type="hidden" name="Id_representante" value="<?= $id_representante; ?>">
                  <section id="new" style="margin-bottom:-2%;">
                    <label class="control-label"></label>
                    <div class="col-sm-1 pull-left" style="margin-top: 30px;">
                      <select name="tipo_cedula" id="tipo_cedula" class="form-control" style="width: 60px;" required>
                        <option value="PN" <?php echo ($tipo_cedula_menor == 'PN' ? 'selected' : ''); ?>>PN-</option>
                        <option value="V" <?php echo ($tipo_cedula_menor == 'V' ? 'selected' : ''); ?>>V-</option>
                        <option value="E" <?php echo ($tipo_cedula_menor == 'E' ? 'selected' : ''); ?>>E-</option>
                      </select>
                    </div>
                    <div class="col-sm-3">
                      <p>Cédula/Documento (*):</p>
                      <input type="text" class="form-control" name="cedula" value="<?php echo $documento_menor; ?>" id="cedula" placeholder="N° de documento" required>
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-4">
                      <p>Nombre (*):</p>
                      <input type="text" class="form-control" name="nombre" value="<?php echo $nombre_menor; ?>" id="solo_texto" placeholder="Nombre Del Paciente" maxlength="100" required>
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-3">
                      <p>Apellido:</p>
                      <input type="text" class="form-control" name="apellido" value="<?php echo $apellido_menor; ?>" id="solo_texto1" placeholder="Apellido Del Paciente" maxlength="100">
                    </div>
                    <br><br><br><br>
                    <label class="control-label"></label>
                    <div class="col-sm-3">
                      <p>Fecha de nacimiento (*):</p>
                      <input type="date" class="form-control pull-right" id="fechaN" value="<?php echo $fecha_nacimiento_menor; ?>" name="fecha_nacimiento" onchange="calcularEdad()" required>
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
                        <option value="Masculino" <?php echo ($genero_menor == 'Masculino' ? 'selected' : ''); ?>>Masculino</option>
                        <option value="Femenino" <?php echo ($genero_menor == 'Femenino' ? 'selected' : ''); ?>>Femenino</option>
                      </select>
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-1">
                    <p>Etnia:</p>
                      <select name="etnia" id="etnia" class="form-control" required>
                        <option value="No" <?php echo ($etnia_menor == 'No' ? 'selected' : ''); ?>>No</option>
                        <option value="Si" <?php echo ($etnia_menor == 'Si' ? 'selected' : ''); ?>>Si</option>
                      </select>
                    </div>
                    <div class="col-sm-2">
                      <p>Tipo etnia:</p>
                      <select name="tipo_etnia" id="tipo_etnia" class="form-control">
                        <option value="">--- Seleccione Una Etnia ---</option>
                        <option value="wayuu" <?php echo ($etnia_tipo_menor == 'wayuu' ? 'selected' : ''); ?>>Wayuu</option>
                        <option value="añu" <?php echo ($etnia_tipo_menor == 'añu' ? 'selected' : ''); ?>>Añu</option>
                        <option value="baniva" <?php echo ($etnia_tipo_menor == 'baniva' ? 'selected' : ''); ?>>Baniva</option>
                        <option value="kurripako" <?php echo ($etnia_tipo_menor == 'kurripako' ? 'selected' : ''); ?>>Kurripako</option>
                        <option value="piapoco" <?php echo ($etnia_tipo_menor == 'piapoco' ? 'selected' : ''); ?>>Piapoco</option>
                        <option value="warekena" <?php echo ($etnia_tipo_menor == 'warekena' ? 'selected' : ''); ?>>Warekena</option>
                        <option value="bare" <?php echo ($etnia_tipo_menor == 'bare' ? 'selected' : ''); ?>>Bare</option>
                        <option value="pemon" <?php echo ($etnia_tipo_menor == 'pemon' ? 'selected' : ''); ?>>Pemon</option>
                        <option value="kariña" <?php echo ($etnia_tipo_menor == 'kariña' ? 'selected' : ''); ?>>Kariña</option>
                        <option value="panare" <?php echo ($etnia_tipo_menor == 'panare' ? 'selected' : ''); ?>>Panare</option>
                        <option value="yukpa" <?php echo ($etnia_tipo_menor == 'yukpa' ? 'selected' : ''); ?>>Yukpa</option>
                        <option value="japreira" <?php echo ($etnia_tipo_menor == 'japreira' ? 'selected' : ''); ?>>Japreira</option>
                        <option value="yekuana" <?php echo ($etnia_tipo_menor == 'yekuana' ? 'selected' : ''); ?>>Yekuana</option>
                        <option value="chaima" <?php echo ($etnia_tipo_menor == 'chaima' ? 'selected' : ''); ?>>Chaima</option>
                        <option value="bari" <?php echo ($etnia_tipo_menor == 'bari' ? 'selected' : ''); ?>>Bari</option>
                        <option value="yanomami" <?php echo ($etnia_tipo_menor == 'yanomami' ? 'selected' : ''); ?>>Yanomami</option>
                        <option value="sanema" <?php echo ($etnia_tipo_menor == 'sanema' ? 'selected' : ''); ?>>Sanema</option>
                        <option value="warao" <?php echo ($etnia_tipo_menor == 'warao' ? 'selected' : ''); ?>>Warao</option>
                        <option value="pume" <?php echo ($etnia_tipo_menor == 'pume' ? 'selected' : ''); ?>>Pume</option>
                        <option value="piaroa" <?php echo ($etnia_tipo_menor == 'piaroa' ? 'selected' : ''); ?>>Piaroa</option>
                        <option value="otro" <?php echo ($etnia_tipo_menor == 'otro' ? 'selected' : ''); ?>>Otro/No Aplica</option>
                        <option value="ninguna" <?php echo ($etnia_tipo_menor == 'ninguna' ? 'selected' : ''); ?>>Ninguna</option>
                      </select>
                    </div>
                    <br><br><br><br>
                    <div class="col-sm-4">
                      <p>País de nacimiento (*):</p>
                      <select name="pais_nacimiento" id="pais_nacimiento" class="form-control" required>
                        <option value="">--- Seleccione un País ---</option>
                        <?php
                        $conexion_nac = new mysqli("localhost", "root", "", "cpt3db");
                        $result_nac = $conexion_nac->query("SELECT Id_Pais, nombre_pais FROM pais");
                        while ($row_list = $result_nac->fetch_assoc()) {
                          $selected = ($row_list['Id_Pais'] == $id_pais_nac) ? 'selected' : '';
                          echo "<option value='{$row_list['Id_Pais']}' {$selected}>{$row_list['nombre_pais']}</option>";
                        }
                        $conexion_nac->close();
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
                          <?php echo ($id_municipio_nac ? "Cargando..." : "--- Seleccione Un Municipio ---"); ?>
                      </select>
                    </div>
                    <br><br><br><br>
                    <label class="control-label"></label>
                    <div class="col-sm-4">
                      <p>Analfabeta (*):</p>
                      <select name="analfabeta" id="analfabeta" class="form-control" required>
                        <option value="">--- Seleccione Una Opción ---</option>
                        <option value="Si" <?php echo ($analfabeta_menor == 'Si' ? 'selected' : ''); ?>>Si</option>
                        <option value="No" <?php echo ($analfabeta_menor == 'No' ? 'selected' : ''); ?>>No</option>
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
                    <div style="float:right; margin-top:-3%;">
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
                            <option value="V" <?php echo ($tipo_cedula_representante == 'V' ? 'selected' : ''); ?>>V-</option>
                            <option value="E" <?php echo ($tipo_cedula_representante == 'E' ? 'selected' : ''); ?>>E-</option>
                          </select>
                      </div>
                      <div class="col-sm-3">
                          <p>Cédula del Rep. (*):</p>
                          <input type="text" class="form-control" name="cedula_rep" value="<?php echo $cedula_representante; ?>" id="cedula_rep" placeholder="N° de Cédula" maxlength="8" required>
                      </div>
                      <label class="control-label"></label>
                      <div class="col-sm-4">
                          <p>Nombre del Rep. (*):</p>
                          <input type="text" class="form-control" name="nombre_rep" value="<?php echo $nombre_representante; ?>" id="nombre_rep" placeholder="Nombre Del Representante" maxlength="100" required>
                      </div>
                      <label class="control-label"></label>
                      <div class="col-sm-3">
                          <p>Apellido del Rep. (*):</p>
                          <input type="text" class="form-control" name="apellido_rep" value="<?php echo $apellido_representante; ?>" id="apellido_rep" placeholder="Apellido Del Representante" maxlength="100" required>
                      </div>
                      <br><br><br><br>
                      <label class="control-label"></label>
                      <div class="col-sm-3">
                          <p>Fecha de nacimiento (*):</p>
                          <input type="date" class="form-control pull-right" id="fechaN_rep" name="fecha_nacimiento_rep" onchange="calcularEdad()" required>
                      </div>
                      <div class="col-sm-1">
                          <p style="margin-left: 5px;">Edad</p>
                          <input type="text" class="form-control pull-right" id="edad_rep" name="edad" readonly>
                      </div>
                      <label class="control-label"></label>
                      <div class="col-sm-1 pull-left" style="margin-top: 30px;">
                          <select name="prefijo_rep" class="form-control" id="prefijo_rep" style="width: 70px;">
                          <?php
                          // Reutilizar la conexión o crear una nueva si la anterior se cerró
                          $conexion_prefijo = new mysqli("localhost", "root", "", "cpt3db");
                          $sql_prefijo = $conexion_prefijo->query("SELECT * FROM prefijos_telefonos");
                          while ($resultado_prefijo = $sql_prefijo->fetch_assoc()) {
                            $selected = ($resultado_prefijo['Id'] == $id_prefijo_representante) ? 'selected' : '';
                            echo "<option value='" . $resultado_prefijo["Id"] . "' {$selected}>" . $resultado_prefijo['prefijo'] . "</option>";
                          }
                          $conexion_prefijo->close();
                          ?>
                          </select>
                      </div>
                      <div class="col-sm-3">
                          <p>Teléfono del Rep. (*):</p>
                          <input type="text" class="form-control" value="<?php echo $telefono_representante; ?>" name="telefono_rep" id="telefono_rep" placeholder="N° De Teléfono" maxlength="7" required>
                      </div>
                      <label class="control-label"></label>
                      <div class="col-sm-3">
                        <p>Email del Rep:</p>
                        <input type="email_rep" class="form-control" name="email_rep" id="email_rep" value="<?php echo $email_representante ?>" placeholder="nombreapellido2@gmail.com">
                      </div>
                      <br><br><br><br>
                      <label class="control-label"></label>
                      <div class="col-sm-4">
                        <p>Sexo del Rep. (*):</p>
                        <select name="genero_rep" id="genero_rep" class="form-control" required>
                          <option value="">--- Seleccione Un Género ---</option>
                          <option value="Masculino" <?php echo ($genero_representante == 'Masculino' ? 'selected' : ''); ?>>Masculino</option>
                          <option value="Femenino" <?php echo ($genero_representante == 'Femenino' ? 'selected' : ''); ?>>Femenino</option>
                        </select>
                      </div>
                      <label class="control-label"></label>
                      <div class="col-sm-4">
                          <p>Relación con el Paciente (*):</p>
                          <select name="parentesco" id="parentesco" class="form-control" required>
                              <option value="">--- Seleccione Parentesco ---</option>
                              <option value="Padre" <?php echo ($relacion_menor == 'Padre' ? 'selected' : ''); ?>>Padre</option>
                              <option value="Madre" <?php echo ($relacion_menor == 'Madre' ? 'selected' : ''); ?>>Madre</option>
                              <option value="Abuelo(a)" <?php echo ($relacion_menor == 'Abuelo(a)' ? 'selected' : ''); ?>>Abuelo(a)</option>
                              <option value="Tío(a)" <?php echo ($relacion_menor == 'Tío(a)' ? 'selected' : ''); ?>>Tío(a)</option>
                              <option value="Tutor Legal" <?php echo ($relacion_menor == 'Tutor Legal' ? 'selected' : ''); ?>>Tutor Legal</option>
                              <option value="Otro" <?php echo ($relacion_menor == 'Otro' ? 'selected' : ''); ?>>Otro</option>
                          </select>
                      </div>
                      <div style="float:right; margin-top:7%;">
                          <button type="button" class="btn btn-secondary prev-tab" data-tab-anterior="#info">Atrás</button>
                          <button type="button" class="btn btn-primary next-tab" data-tab-actual="#representante" data-tab-siguiente="ocupacion_estudios">Siguiente</button>
                      </div>
                  </section>
              </div>

              <div class="tab-pane" id="ocupacion_estudios">
                <section id="new" style="margin-bottom:14%;">
                  <label class="control-label"></label>
                  <div class="col-sm-4">
                    <p>Nivel de instrucción:</p>
                    <select name="nivel_instruccion" id="nivel_instruccion" class="form-control">
                      <option value="">--- Seleccione Un Nivel De Instrucción ---</option>
                        <option value="sin_instruccion" <?php echo ($instruccion_menor == 'sin_instruccion' ? 'selected' : ''); ?>>Sin Instrucción</option>
                      <option value="primaria_incompleta" <?php echo ($instruccion_menor == 'primaria_incompleta' ? 'selected' : ''); ?>>Primaria Incompleta</option>
                      <option value="primaria_completa" <?php echo ($instruccion_menor == 'primaria_completa' ? 'selected' : ''); ?>>Primaria Completa</option>
                      <option value="bachillerato_incompleto" <?php echo ($instruccion_menor == 'bachillerato_incompleto' ? 'selected' : ''); ?>>Educación Media Incompleta</option>
                      <option value="bachiller" <?php echo ($instruccion_menor == 'bachiller' ? 'selected' : ''); ?>>Bachiller (Media General)</option>
                    </select>
                  </div>
                  <label class="control-label"></label>
                  <div class="col-sm-4">
                    <p>Misión educativa:</p>
                    <select name="mision" id="mision" class="form-control">
                      <option value="">--- Seleccione Una Misión ---</option>
                      <option value="robinson" <?php echo ($mision_menor == 'robinson' ? 'selected' : ''); ?>>Robinson</option>
                        <option value="ribas" <?php echo ($mision_menor == 'ribas' ? 'selected' : ''); ?>>Ribas</option>
                        <option value="sucre" <?php echo ($mision_menor == 'sucre' ? 'selected' : ''); ?>>Sucre</option>
                    </select>
                  </div>
                  <label class="control-label"></label>
                  <div class="col-sm-3">
                    <p>Años aprobados:</p>
                    <input type="number" id="años_aprobados" class="form-control" value="<?php echo $años_aprobados_menor; ?>" name="años_aprobados" min="0">
                    <small id="años_help" class="form-text text-muted"></small>
                  </div>
                  <div style="float:right; margin-top:7%;">
                    <button type="button" class="btn btn-secondary prev-tab" data-tab-anterior="#representante">Atrás</button>
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
                      <option value="<?php echo $id_estado_dir; ?>" selected>
                        <?php echo ($id_estado_dir ? "Cargando..." : "--- Seleccione Un Estado ---"); ?>
                    </select>
                  </div>
                  <label class="control-label"></label>
                  <div class="col-sm-4">
                    <p>Municipio (*):</p>
                    <select name="municipio" id="municipio" class="form-control" required>
                    <option value="<?php echo $id_municipio_dir; ?>" selected>
                      <?php echo ($id_municipio_dir ? "Cargando..." : "--- Seleccione Un Municipio ---"); ?>
                    </select>
                  </div>
                  <label class="control-label"></label>
                  <div class="col-sm-3">
                  <p>Sector (*):</p>
                    <select name="sector" id="sector" class="form-control" required>                
                    <option value="<?php echo $id_sector_dir; ?>" selected>
                        <?php echo ($id_sector_dir ? "Cargando..." : "--- Seleccione Un Sector ---"); ?>
                    </select>
                  </div>
                  <br><br><br><br>
                  <label class="control-label"></label>
                  <div class="col-sm-4">
                    <p>Avenida/calle:</p>
                    <input type="text" class="form-control" value="<?php echo $avenida_calle; ?>" name="avenida_calle">
                  </div>
                  <label class="control-label"></label>
                  <div class="col-sm-4">
                    <p>Punto de referencia:</p>
                    <input type="text" class="form-control" value="<?php echo $referencia; ?>" name="referencia">
                  </div>
                  <div class="col-sm-1">
                    <p>Tiempo:</p>
                    <input type="text" class="form-control" name="tiempo_residencia" id="tiempo_residencia" value="<?php echo $tiempo_residencia ?>" placeholder="ej. 2">
                  </div>
                  <div class="col-sm-2">
                    <p>Dias/Meses/Etc:</p>
                    <select name="tiempo" id="tiempo" class="form-control">
                      <option value="dia/s" <?php echo ($tiempo == 'dia/s' ? 'selected' : ''); ?>>Dia/s</option>
                      <option value="semanas/s" <?php echo ($tiempo == 'semanas/s' ? 'selected' : ''); ?>>Semanas/s</option>
                      <option value="meses/s" <?php echo ($tiempo == 'meses/s' ? 'selected' : ''); ?>>Meses/s</option>
                      <option value="años/s" <?php echo ($tiempo == 'años/s' ? 'selected' : ''); ?>>Año/s</option>
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
                <section id="new" style="margin-bottom:10%;">
                  <label class="control-label"></label>
                  <div class="col-sm-5">
                    <p>Patologias:</p>
                    <div id="patologias_agregadas" class="form-control" style="height: auto; min-height: 34px;" readonly>
                      <span class="text-muted">Ninguna patología seleccionada.</span>
                    </div>
                  </div>
                  <div class="col-sm-1" style="margin-top: 30px;">
                    <button type="button" class="form-control pull-right bt-sm btn-primary"  data-toggle="modal" data-target="#modalPatologias">
                      <img src="../../recursos/imagenes/iconos/editar.png" height="20px" width="20px">
                    </button>
                    <input type="hidden" name="patologias_ids" id="patologias_ids">
                  </div>
                  <label class="control-label"></label>
                  <div class="col-sm-4">
                    <p>Alergias:</p>
                    <div id="alergias_agregadas" class="form-control" style="height: auto; min-height: 34px;" readonly>
                      <span class="text-muted">Ninguna patología seleccionada.</span>
                    </div>
                  </div>
                  <div class="col-sm-1" style="margin-top: 30px;">
                    <button type="button" class="form-control pull-right bt-sm btn-primary"  data-toggle="modal" data-target="#modalAlergias">
                      <img src="../../recursos/imagenes/iconos/editar.png" height="20px" width="20px">
                    </button>
                    <input type="hidden" name="alergias_ids" id="alergias_ids">
                  </div>
                  <br><br><br><br><br><br>
                  <label class="control-label"></label>
                  <div class="col-sm-5">
                    <p>Grupo sanguíneo (*):</p>
                    <select name="grupo_sanguineo" id="grupo_sanguineo" class="form-control" required>
                      <option value="">--- Seleccione Un Tipo De Sangre ---</option>
                      <option value="A+" <?php echo ($grupo_sanguineo == 'A+' ? 'selected' : ''); ?>>A+</option>
                      <option value="A-" <?php echo ($grupo_sanguineo == 'A-' ? 'selected' : ''); ?>>A-</option>
                      <option value="B+" <?php echo ($grupo_sanguineo == 'B+' ? 'selected' : ''); ?>>B+</option>
                      <option value="B-" <?php echo ($grupo_sanguineo == 'B-' ? 'selected' : ''); ?>>B-</option>
                      <option value="O+" <?php echo ($grupo_sanguineo == 'O+' ? 'selected' : ''); ?>>O+</option>
                      <option value="O-" <?php echo ($grupo_sanguineo == 'O-' ? 'selected' : ''); ?>>O-</option>
                      <option value="AB+" <?php echo ($grupo_sanguineo == 'AB+' ? 'selected' : ''); ?>>AB+</option>
                      <option value="AB-" <?php echo ($grupo_sanguineo == 'AB-' ? 'selected' : ''); ?>>AB-</option>
                    </select>
                  </div>
                  <label class="control-label"></label>
                  <div class="col-sm-1">
                  <p>Discap.:</p>
                    <select name="discapacidad" id="discapacidad" class="form-control" required>
                      <option value="No" <?php echo ($discapacidad == 'No' ? 'selected' : ''); ?>>No</option>
                      <option value="Si" <?php echo ($discapacidad == 'Si' ? 'selected' : ''); ?>>Si</option>
                    </select>
                  </div>
                  <div class="col-sm-5">
                  <p>Tipo de discapacidad:</p>
                    <select name="tipo_discapacidad" id="tipo_discapacidad" class="form-control">
                      <option value="">--- Seleccione Una Discapacidad ---</option>
                      <option value="fisico_motora" <?php echo ($discapacidad_tipo_menor == 'fisico_motora' ? 'selected' : ''); ?>>Físico-Motora</option>
                      <option value="visual" <?php echo ($discapacidad_tipo_menor == 'visual' ? 'selected' : ''); ?>>Visual</option>
                      <option value="auditiva" <?php echo ($discapacidad_tipo_menor == 'auditiva' ? 'selected' : ''); ?>>Auditiva</option>
                      <option value="intelectual" <?php echo ($discapacidad_tipo_menor == 'intelectual' ? 'selected' : ''); ?>>Intelectual</option>
                      <option value="psicosocial" <?php echo ($discapacidad_tipo_menor == 'psicosocial' ? 'selected' : ''); ?>>Psicosocial</option>
                      <option value="multiple" <?php echo ($discapacidad_tipo_menor == 'multiple' ? 'selected' : ''); ?>>Múltiple</option>
                    </select>
                  </div>
                  <div style="float:right; margin-top:3%;">
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
          <h4 class="modal-title" id="modalGuardarMedicoLabel"><i class="fa fa-save"></i> Confirmacion de Guardado </h4>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea actualizar la información del nuevo paciente menor de edad?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Regresar</button>
          <button type="button" class="btn btn-success" id="confirmarGuardadoFinal">Actualizar</button>
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
                <select id="selector_patologia" class="form-control">
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
                <input type="text" id="codigo_patologia_modal" class="form-control" readonly>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-success" id="agregarPatologia">Añadir</button>
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
                <option value="SI">SI</option>
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
                <select id="selector_alergia" class="form-control">
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
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-success" id="agregarAlergia">Añadir</button>
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
  // Usar el operador ?? para asegurar que la variable no sea 'undefined' si no existe en PHP
const idPaciente = "<?php echo $id_paciente; ?>"; 
const saved_id_pais_nac = "<?php echo $id_pais_nac; ?>";
const saved_id_estado_nac = "<?php echo $id_estado_nac; ?>";
const saved_id_municipio_nac = "<?php echo $id_municipio_nac; ?>";
const saved_id_pais_dir = "<?php echo $id_pais_dir; ?>";
const saved_id_estado_dir = "<?php echo $id_estado_dir; ?>";
const saved_id_municipio_dir = "<?php echo $id_municipio_dir; ?>";
const saved_id_sector_dir = "<?php echo $id_sector_dir; ?>";

// Data de Patologías y Alergias (Existente en editar.php)
const patologiasData = "<?php echo $patologias_data; ?>";
const alergiasData = "<?php echo $alergias_data; ?>";

console.log(idPaciente)
console.log(patologiasData,"esto carga")
console.log(alergiasData,"esto carga")  

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

    // --- LÓGICA DE CÁLCULO DE EDAD Y VALIDACIÓN DE MINORÍA DE EDAD ---

    // 1. Establecer fecha MÁXIMA del PACIENTE para asegurar que no sea MAYOR de hoy.
    function setMaxDate() {
      const today = new Date();
      // La fecha máxima debe ser HOY.
      const maxDate = today.toISOString().split('T')[0];
      
      document.getElementById('fechaN').setAttribute('max', maxDate);
    }
    setMaxDate();

    // --- LÓGICA PARA FECHA DE NACIMIENTO DEL REPRESENTANTE ---

    // 1. Establecer fecha MÁXIMA del REPRESENTANTE para asegurar que no sea MAYOR de hoy.
    function setMaxDateRep() {
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
    setMaxDateRep(); // Ejecuta al cargar

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

    /**
     * Función que restringe la entrada de caracteres a solo números.
     * Se usa para Cédula de Representante y Teléfono.
     */
    function restringirSoloNumeros(e) {
      this.value = this.value.replace(/[^0-9]/g, '');
    }

    // Aplicar filtro dinámico de caracteres y longitud para Cédula/PN del Paciente
    if (cedulaInput) cedulaInput.addEventListener('input', filtrarCedulaPorTipo);
    if (tipoCedulaSelect) tipoCedulaSelect.addEventListener('change', filtrarCedulaPorTipo);
    
    // Aplicar restricción de solo números a Cédula Rep. y Teléfono
    if (cedulaInput) cedulaInput.addEventListener('input', restringirSoloNumeros);
    if (cedulaRepInput) cedulaRepInput.addEventListener('input', restringirSoloNumeros);
    if (telefonoRepInput) telefonoRepInput.addEventListener('input', restringirSoloNumeros);

    /**
     * Función que restringe la entrada de caracteres a solo números si es V o E,
     * aplica límites de longitud estricta en tiempo real y actualiza el maxlength HTML.
     */
    function filtrarCedulaPorTipo() {
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
    if (cedulaInput && tipoCedulaSelect) filtrarCedulaPorTipo(); 

    async function verificarCedulaEnBD(tipo, cedula, esRepresentante = false) {
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

    async function validarCedulaFormato() {
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
      
      // *** LÓGICA DE VALIDACIÓN DE LONGITUD Y RANGOS ***
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
        mostrarAviso('🛑 **Error de Longitud**: ' + errorTexto);
        esValido = false;
        return false;
      }

      // Validaciones de rango numérico (Solo para V y E)
      if (tipo === 'V' || tipo === 'E') {
          if (tipo === 'V' && cedula > 80000000) {
            $(cedulaInput).addClass('input-error');
            mostrarAviso('🛑 **Error de Cédula (V-)**: La cédula no puede ser mayor a 80.000.000');
            esValido = false;
          } else if (tipo === 'E' && cedula < 80000000) {
            $(cedulaInput).addClass('input-error');
            mostrarAviso('🛑 **Error de Cédula (E-)**: La cédula no puede ser menor a 80.000.000');
            esValido = false;
          }
      }
      
      if (!esValido) return false;
    }
    

    // Función de validación y AUTO-RELLENO de cédula del representante (similar, pero con su propio evento)
    function setMaxDate() {
      const today = new Date();
      // La fecha máxima debe ser HOY.
      const maxDate = today.toISOString().split('T')[0];
      
      // CAMBIO 1: La fecha MÍNIMA debe ser exactamente 18 años atrás, 
      // para asegurar que el paciente sea menor de 18.
      const eighteenYearsAgo = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());
      const minDate = eighteenYearsAgo.toISOString().split('T')[0];

      document.getElementById('fechaN').setAttribute('max', maxDate);
      document.getElementById('fechaN').setAttribute('min', minDate); // Establece el limite inferior
    }
    setMaxDate();

    function calcularEdad() {
// ... (código interno de calcularEdad() se mantiene)
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

    // --- LÓGICA PARA FECHA DE NACIMIENTO DEL REPRESENTANTE ---

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
        mostrarAviso('🛑 **Error del Representante**: Debe seleccionar el **Tipo de Cédula** del Representante.');
        esValido = false;
      }
      
      if (cedula === "") {
        $(cedulaRepInput).addClass('input-error');
        mostrarAviso('🛑 **Error del Representante**: El campo **Cédula del Representante** es obligatorio.');
        esValido = false;
      }

      if (!esValido) return false; // Detener si faltan campos

      // Validación de rango (se mantiene)
      const cedulaNum = parseInt(cedula);
      if (tipo === 'V' && cedulaNum > 80000000) {
        $(cedulaRepInput).addClass('input-error');
        mostrarAviso('🛑 **Error de Cédula (V-)**: La cédula del Representante no puede ser mayor a 80.000.000');
        esValido = false;
      } else if (tipo === 'E' && cedulaNum < 80000000) {
        $(cedulaRepInput).addClass('input-error');
        mostrarAviso('🛑 **Error de Cédula (E-)**: La cédula del Representante no puede ser menor a 80.000.000');
        esValido = false;
      }
      
      // Si la validación de formato pasa, intentar buscar el representante
      if (esValido) {
          buscarRepresentantePorCedula(tipo, cedula);
      }

      return esValido;
    }
    
    /**
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
      $('#genero_rep').prop('disabled', bloquear);
      $('#prefijo_rep').prop('readonly', bloquear);

      // Cambio visual (opcional) para que el usuario sepa que están bloqueados
      if (bloquear) {
        $('#nombre_rep, #apellido_rep, #fechaN_rep, #telefono_rep, #email_rep').css('background-color', '#eee');
        $('#prefijo_rep').css('background-color', '#eee');
      } else {
        $('#nombre_rep, #apellido_rep, #fechaN_rep, #telefono_rep, #email_rep').css('background-color', '#fff');
        $('#prefijo_rep, #genero_rep').css('background-color', '#fff');
      }
    }

    /**
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

            if(data.fecha_nacimiento) {
              $('#fechaN_rep').val(data.fecha_nacimiento);
              calcularEdadRep();
            }
            // Manejo del teléfono
            if(data.telefono_numero) {
              $('#telefono_rep').val(data.telefono_numero);
            }
            if(data.prefijo_id) {
              $('#prefijo_rep').val(data.prefijo_id);
            }

            if(data.genero) {
              $('#genero_rep').val(data.genero);
            }

            if(data.email) {
              $('#email_rep').val(data.email);
            }

            /*mostrarAviso('✅ <b>Representante Encontrado:</b> Los datos han sido cargados automáticamente.');*/
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
    cedulaInput.addEventListener('blur', validarCedulaFormato);
    tipoCedulaSelect.addEventListener('change', validarCedulaFormato);
    // Evento blur para la búsqueda del representante
    cedulaRepInput.addEventListener('blur', validarCedulaRepFormato);
    tipoCedulaRepSelect.addEventListener('change', validarCedulaRepFormato);


    // --- LÓGICA DE DEPENDENCIAS DE UBICACIÓN (Se mantiene sin cambios) ---

    function loadAndSelect(parentId, targetId, url, keyName, savedId) {
      // ... (código loadAndSelect se mantiene)

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
          success: function (data) {
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
          error: function () {
            $targetSelect.html('<option value="">Error al cargar datos</option>');
            resolve(null);
          }
        });
      });
    }

    // --- ENLACE DE EVENTOS PARA CARGA DINÁMICA DE UBICACIÓN (Copiada y adaptada de pacientes_agregar.php) ---

    // 1. NACIMIENTO: PAIS -> ESTADO
    document.getElementById('pais_nacimiento').addEventListener('change', function () {
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
    document.getElementById('estado_nacimiento').addEventListener('change', function () {
      const estadoId = this.value;
      const municipioSelect = document.getElementById('municipio_nacimiento');
      municipioSelect.innerHTML = '<option value="">--- Seleccione Un Municpio ---</option>';

      if (estadoId) {
        loadAndSelect(estadoId, 'municipio_nacimiento', 'get/get_municipios.php', 'Id_Estado', '');
      }
    });

    // 3. DIRECCIÓN: ESTADO -> MUNICIPIO
    document.addEventListener('DOMContentLoaded', () => {
      // ... (Carga de Nacimiento) ...
  
      const estadoIdSeleccionado = document.getElementById('estado').value;
      
      // Inicia la carga a partir del Municipio, usando el Estado ya seleccionado por PHP
      if (estadoIdSeleccionado) {
          loadAndSelect(estadoIdSeleccionado, 'municipio', 'get/get_municipios.php', 'Id_Estado', saved_id_municipio_dir)
              .then((savedMunId) => {
                  if (savedMunId) {
                      return loadAndSelect(savedMunId, 'sector', 'get/get_sectores.php', 'Id_Municipio', saved_id_sector_dir);
                  }
              })
              .catch(error => console.error('Fallo en la cadena de Dirección:', error));
      }
  });

    // 4. DIRECCIÓN: MUNICIPIO -> SECTOR
    document.getElementById('municipio').addEventListener('change', function () {
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
      const initDireccionEstado = loadAndSelect(saved_id_pais_dir, 'estado', 'get/get_estados.php', 'Id_Pais', saved_id_estado_dir);

      initDireccionEstado
        .then((savedStateId) => {
          if (savedStateId) {
            // PASO 2: Cargar MUNICIPIO a partir del ID de Estado guardado
            return loadAndSelect(savedStateId, 'municipio', 'get/get_municipios.php', 'Id_Estado', saved_id_municipio_dir);
          }
        })
        .then((savedMunId) => {
          if (savedMunId) {
            // PASO 3: Cargar SECTOR a partir del ID de Municipio guardado
            return loadAndSelect(savedMunId, 'sector', 'get/get_sectores.php', 'Id_Municipio', saved_id_sector_dir);
          }
        })
        .catch(error => console.error('Fallo en la cadena de Dirección:', error));

    });

    // --- LÓGICA Analfabeta y Validación de Misiones (Se mantiene sin cambios) ---

function cargarDatosExistentes() {
  // Cargar Patologías
  if (patologiasData && patologiasData.length > 0) {
    // Formato: Id_patologia::nombre_patologia::codigo_cie || Id_patologia::...
    const patologiasArray = patologiasData.split('||').filter(item => item.trim() !== '');
    patologiasSeleccionadas = patologiasArray.map(item => {
      const parts = item.split('::');
      return {
        id: parts[0],
        nombre: parts[1],
        codigo: parts[2]
      };
    }).filter(item => item.id && item.nombre);
  } else {
    patologiasSeleccionadas = [];
  }

  // Cargar Alergias
  if (alergiasData && alergiasData.length > 0) {
    // Formato: Id_alergias_conocidas::nombre_alergia || Id_alergias_conocidas::...
    const alergiasArray = alergiasData.split('||').filter(item => item.trim() !== '');
    alergiasSeleccionadas = alergiasArray.map(item => {
      const parts = item.split('::');
      return {
        id: parts[0],
        nombre: parts[1]
      };
    }).filter(item => item.id && item.nombre);
  } else {
    alergiasSeleccionadas = [];
  }

  // Actualizar la interfaz con los datos cargados
  actualizarVistaPatologias();
  actualizarVistaAlergias();
}

function actualizarVistaPatologias() {
  const contenedor = $('#patologias_agregadas');
  const inputHidden = $('#patologias_ids');
  let html = '';

  if (patologiasSeleccionadas.length === 0) {
    contenedor.html('<span class=\"text-muted\">Ninguna patología seleccionada.</span>');
    inputHidden.val('');
    return;
  }

  const limite = 2;
  const mostradas = patologiasSeleccionadas.slice(0, limite);
  const faltantes = patologiasSeleccionadas.length - limite;

  mostradas.forEach(pato => {
    html += `<span class=\"text\" style=\"margin-right: 5px; margin-bottom: 5px; display: inline-block;\">${pato.nombre},</span>`;
  });

  if (faltantes > 0) {
    html += `<span class=\"text\" style=\"margin-left:5px;\">... y ${faltantes} más.</span>`;
  }

  contenedor.html(html);
  inputHidden.val(patologiasSeleccionadas.map(p => p.id).join(','));
}

function actualizarVistaAlergias() {
  const contenedor = $('#alergias_agregadas');
  const inputHidden = $('#alergias_ids');
  let html = '';

  if (alergiasSeleccionadas.length === 0) {
    contenedor.html('<span class=\"text-muted\">Ninguna alergia seleccionada.</span>');
    inputHidden.val('');
    return;
  }

  const limite = 2;
  const mostradas = alergiasSeleccionadas.slice(0, limite);
  const faltantes = alergiasSeleccionadas.length - limite;

  mostradas.forEach(aler => {
    html += `<span class=\"text\" style=\"margin-right: 5px; margin-bottom: 5px; display: inline-block;\">${aler.nombre},</span>`;
  });

  if (faltantes > 0) {
    html += `<span class=\"text\" style=\"margin-left:5px;\">... y ${faltantes} más.</span>`;
  }

  contenedor.html(html);
  inputHidden.val(alergiasSeleccionadas.map(a => a.id).join(','));
}


function renderizarListaTemporalPatologias() {
  const lista = $('#lista_patologias_seleccionadas');
  if (patologiasSeleccionadas.length === 0) {
    lista.html('<p class=\"text-muted\">Ninguna patología en la lista temporal.</p>');
    return;
  }

  let html = '';
  patologiasSeleccionadas.forEach(pato => {
    // El botón usa la clase .eliminar-pato-modal y data-id para la eliminación
    html += `<span class=\"label label-primary\" style=\"margin-right: 5px; margin-bottom: 5px; display: inline-block; font-size: 14px; padding: 6px 10px 6px 10px;\">
                    ${pato.nombre} (${pato.codigo})
                    <a href=\"#\" data-id=\"${pato.id}\" class=\"eliminar-pato-modal\" style=\"color:white; margin-left: 5px;\">
                    &times;
                    </a>
                 </span>`;
  });
  lista.html(html);
}

function renderizarListaTemporalAlergias() {
  const lista = $('#lista_alergias_seleccionadas');
  if (alergiasSeleccionadas.length === 0) {
    lista.html('<p class=\"text-muted\">Ninguna alergia en la lista temporal.</p>');
    return;
  }

  let html = '';
  alergiasSeleccionadas.forEach(aler => {
    // El botón usa la clase .eliminar-aler-modal y data-id para la eliminación
    html += `<span class=\"label label-primary\" style=\"margin-right: 5px; margin-bottom: 5px; display: inline-block; font-size: 14px; padding: 6px 10px 6px 10px;\">
                    ${aler.nombre}
                    <a href=\"#\" data-id=\"${aler.id}\" class=\"eliminar-aler-modal\" style=\"color:white; margin-left: 5px;\">
                    &times;
                    </a>
                 </span>`;
  });
  lista.html(html);
}


// =========================================================================
// 3. LÓGICA DE AGREGAR CON VALIDACIÓN DE DUPLICADOS
// =========================================================================

function agregarPatologiaALista() {
    // 🛑 ATENCIÓN: Usamos el ID #selector_patologia para obtener ID y Nombre
    const selector = $('#selector_patologia');
    const id = selector.val();
    const nombre = selector.find('option:selected').text();
    const codigo = $('#codigo_patologia_modal').val() || ''; 

    // 1. Validar selección
    if (!id || id === '0' || nombre.trim() === '' || nombre.includes('Seleccione')) {
        // Este mensaje usa #mensaje_patologia_modal
        mostrarAviso('Por favor, seleccione una patología válida.', 'alert-danger');
        return;
    }

    // 2. VALIDACIÓN DE DUPLICADOS
    const yaExiste = patologiasSeleccionadas.some(p => p.id.toString() === id.toString());

    if (yaExiste) {
        // Este mensaje usa #mensaje_patologia_modal
        mostrarAviso(`La patología \"${nombre}\" ya está en la lista temporal.`, 'alert-warning');
        selector.val('0').trigger('change');
        return;
    }

    // 3. Si no existe, agregarla
    patologiasSeleccionadas.push({ id: id, nombre: nombre, codigo: codigo });

    // 4. Actualizar la lista temporal y notificar éxito
    renderizarListaTemporalPatologias();
    // Este mensaje usa #mensaje_patologia_modal
    mostrarAviso(`Patología \"${nombre}\" agregada correctamente.`, 'alert-success');

    // 5. Limpiar el selector
    selector.val('0').trigger('change');
}

function agregarAlergiaALista() {
    // 🛑 ATENCIÓN: Usamos el ID #selector_alergia para obtener ID y Nombre
    const selector = $('#selector_alergia');
    const id = selector.val();
    const nombre = selector.find('option:selected').text();

    // 1. Validar selección
    if (!id || id === '0' || nombre.trim() === '' || nombre.includes('Seleccione')) {
        // Este mensaje usa #mensaje_alergia_modal
        mostrarAviso('Por favor, seleccione una alergia válida.', 'alert-danger');
        return;
    }

    // 2. VALIDACIÓN DE DUPLICADOS
    const yaExiste = alergiasSeleccionadas.some(a => a.id.toString() === id.toString());

    if (yaExiste) {
        // Este mensaje usa #mensaje_alergia_modal
        mostrarAviso(`La alergia \"${nombre}\" ya está en la lista temporal.`, 'alert-warning');
        selector.val('0').trigger('change');
        return;
    }

    // 3. Si no existe, agregarla
    alergiasSeleccionadas.push({ id: id, nombre: nombre });

    // 4. Actualizar la lista temporal y notificar éxito
    renderizarListaTemporalAlergias();
    // Este mensaje usa #mensaje_alergia_modal
    mostrarAviso(`Alergia \"${nombre}\" agregada correctamente.`, 'alert-success');

    // 5. Limpiar el selector
    selector.val('0').trigger('change');
}

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
      } 
      else if (noTieneEtnia) {
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
      } 
      else if (noTieneDiscapacidad) {
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
          errores.push("El campo **" + $input.prev('p').text().replace('(*):', '').replace('(*)', '') + "** es obligatorio.");
          $input.addClass('input-error');
          esValido = false;
        }
      });

      // 2. Validaciones Específicas

      if (tabSelector === '#info') {
        
        const edad = parseInt($('#edad').val());
        // El paciente debe ser estrictamente menor de 18 años (edad < 18)
        if (isNaN(edad) || edad < 0 || edad >= 18) {
          errores.push("El paciente debe ser **menor de 18 años** (Edad " + edad + ").");
          $('#fechaN').addClass('input-error');
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

      if (tabSelector === '#direccion') {
        if ($('#municipio').val().trim() === "") {
          $('#municipio').addClass('input-error');
          esValido = false;
        }
      }

      if (tabSelector === '#ocupacion_estudios') {
        // Validar años aprobados si está habilitado
        if (!$('#años_aprobados').prop('disabled')) {
          const años = parseInt($('#años_aprobados').val());
          const max = parseInt($('#años_aprobados').attr('max'));
          if (!isNaN(max) && años > max) {
            errores.push("Los **Años Aprobados** exceden el máximo permitido para esta Misión (" + max + " años).");
            $('#años_aprobados').addClass('input-error');
            esValido = false;
          }
        }
      }

      if (tabSelector === '#salud_otros') {
        // Hacer la selección de patología obligatoria para un menor (mayor estrictez)
        if (patologiasSeleccionadas.length === 0) {
          errores.push("Debe seleccionar al menos **una Patología**.");
          $('#patologias_agregadas').addClass('input-error');
          esValido = false;
        }
      }

      if (!esValido && errores.length > 0) {
        mostrarAviso('⚠️ **Errores de Formulario:**<ul><li>' + errores.join('</li><li>') + '</li></ul>');
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

      const esValido = await validarPestana(tabActualSelector);

      $btn.prop('disabled', false).text(tabSiguienteName === 'confirmar' ? 'Guardar' : 'Siguiente');

      if (esValido) {
        if (tabSiguienteName === 'confirmar') {
          $('#modalGuardarMedico').modal('show');
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

    function loadInitialData() {
      // 1. Datos Generales y Representante
      calcularEdad();
      calcularEdadRep();
      validarCedulaRepFormato();
      cargarDatosExistentes();
      
      // 2. Datos Médicos
      
      // 3. Ocupación/Estudios (toggleOptions se llama dentro de inicializarDatosMedicos)
    }
    
    // Llama a la función de carga al iniciar
    $(document).ready(function() {
      loadInitialData();
      
      // --- ENLACE DE EVENTOS DE VALIDACIÓN DEL PACIENTE (Añadido de agregar.js) ---
      if (cedulaInput) cedulaInput.addEventListener('input', filtrarCedulaPorTipo);
      if (tipoCedulaSelect) tipoCedulaSelect.addEventListener('change', filtrarCedulaPorTipo);
    
      // Validación en el evento blur (al perder el foco) y change (al cambiar el tipo)
      if (cedulaInput) cedulaInput.addEventListener('blur', validarCedulaFormato);
      if (tipoCedulaSelect) tipoCedulaSelect.addEventListener('change', validarCedulaFormato);
      // --- FIN ENLACE DE EVENTOS DE VALIDACIÓN DEL PACIENTE ---
    
      // Llamada al evento de validación y auto-relleno del representante (adaptada al final del archivo)
      if (cedulaRepInput) cedulaRepInput.addEventListener('blur', validarCedulaRepFormato);
      if (tipoCedulaRepSelect) tipoCedulaRepSelect.addEventListener('change', validarCedulaRepFormato);

      $('#selector_patologia').on('change', function() {
        const idPatologia = $(this).val();
        
        // Limpiamos el campo de código mientras se carga
        $('#codigo_patologia_modal').val(''); 

        if (idPatologia && idPatologia !== '0') {
            $.ajax({
                url: 'get/get_cie.php', 
                method: 'GET',
                dataType: 'json',
                data: {
                    // 🛑 CORRECCIÓN 1: Usamos la clave 'Id_patologia' que el PHP espera
                    Id_patologia: idPatologia 
                },
                beforeSend: function() {
                    $('#codigo_patologia_modal').val('Cargando...');
                },
                success: function(response) {
                    // 🛑 CORRECCIÓN 2: Buscamos la clave 'codigo_cie' que el PHP devuelve
                    if (response && response.codigo_cie !== undefined) {
                        $('#codigo_patologia_modal').val(response.codigo_cie);
                    } else {
                        // Si la patología existe pero no tiene código, o la respuesta es vacía.
                        $('#codigo_patologia_modal').val('');
                        mostrarAviso('#mensaje_patologia_modal', 'Código CIE no encontrado para esta patología.', 'alert-warning');
                    }
                },
                error: function(xhr, status, error) {
                    $('#codigo_patologia_modal').val('Error');
                    console.error('Fallo en la llamada AJAX:', status, error);
                    mostrarAviso('#mensaje_patologia_modal', 'Fallo de conexión al cargar código CIE. Revise la ruta del AJAX.', 'alert-danger');
                }
            });
        }
    });

        $('#modalPatologias').on('show.bs.modal', function (e) {
            renderizarListaTemporalPatologias();
            // Ocultar mensaje al reabrir
            $('#mensaje_patologia_modal').hide();
        });

        // 3. Evento para abrir el modal de Alergias y cargar la lista
        $('#modalAlergias').on('show.bs.modal', function (e) {
            renderizarListaTemporalAlergias();
            // Ocultar mensaje al reabrir
            $('#mensaje_alergia_modal').hide();
        });
        
        // 4. Conexión de los botones de AGREGAR a la nueva lógica de validación
        $('#agregarPatologia').on('click', function(e) {
            e.preventDefault();
            agregarPatologiaALista();
        });

        $('#agregarAlergia').on('click', function(e) {
            e.preventDefault();
            agregarAlergiaALista();
        });


        // 5. Lógica de ELIMINACIÓN DELEGADA (Dentro del Modal)
        
        // Eliminar Patología de la lista temporal del modal
        $('#lista_patologias_seleccionadas').on('click', '.eliminar-pato-modal', function(e) {
            e.preventDefault();
            const idAEliminar = $(this).data('id').toString();
            // Filtra el array global
            patologiasSeleccionadas = patologiasSeleccionadas.filter(p => p.id.toString() !== idAEliminar);
            // Volver a renderizar ambas vistas
            renderizarListaTemporalPatologias();
            actualizarVistaPatologias(); // Refleja el cambio en el formulario principal
        });

        // Eliminar Alergia de la lista temporal del modal
        $('#lista_alergias_seleccionadas').on('click', '.eliminar-aler-modal', function(e) {
            e.preventDefault();
            const idAEliminar = $(this).data('id').toString();
            // Filtra el array global
            alergiasSeleccionadas = alergiasSeleccionadas.filter(a => a.id.toString() !== idAEliminar);
            // Volver a renderizar ambas vistas
            renderizarListaTemporalAlergias();
            actualizarVistaAlergias(); // Refleja el cambio en el formulario principal
        });
        
        // 6. Al cerrar el modal, asegúrate de actualizar la vista principal (por si acaso)
        $('#modalPatologias').on('hide.bs.modal', function () {
            actualizarVistaPatologias();
        });
        
        $('#modalAlergias').on('hide.bs.modal', function () {
            actualizarVistaAlergias();
        });
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