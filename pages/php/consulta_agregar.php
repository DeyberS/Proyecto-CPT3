<?php
// ==========================================================
// 1. INCLUIR CONEXIÓN A LA BASE DE DATOS
//    *** Se asume que este archivo define la variable $conexion (objeto mysqli).
// ==========================================================
include("../../cfg/conexion.php");

// Consultar Alergias existentes
$query_alergias = $conexion->query("SELECT Id_alergias_conocidas, nombre_alergia FROM alergias_conocidas WHERE estatus = 1 ORDER BY nombre_alergia ASC");

// Consultar Patologías existentes
$query_patologias = $conexion->query("SELECT Id_patologia, nombre_patologia, codigo_cie FROM patologias WHERE estatus = 1 ORDER BY nombre_patologia ASC");

$sql_medicamentos = "SELECT 
    dm.Id, 
    m.nombre_medicamento, 
    dm.contenido_neto, 
    dm.via_aplicacion,
    dpm.cantidad_unidad_medida, 
    um.unidad,
    p.nombre_presentacion
FROM descripcion_medicamento dm
INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
LEFT JOIN detalle_principio_medicamento dpm ON m.Id_medicamento = dpm.id_medicamento
LEFT JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida
LEFT JOIN presentacion p ON dm.Id_presentacion = p.Id_presentacion
WHERE dm.estatus = 1 
ORDER BY m.nombre_medicamento ASC";

$query_medicamentos = $conexion->query($sql_medicamentos);
// Solo manejamos la cédula para fines iniciales, si viene en el GET (ej: desde un listado)
$cedula = $_GET['cedula'] ?? '';
$id_cita = $_GET['Id'] ?? '';
$motivo_cita = $_GET['motivo'] ?? '';

$datos_paciente = [];
$error_busqueda = null;

$id_medico_cita = null;

if ($id_cita) {

  $sql_cita = "SELECT dm.Id_detalle_medico AS medico_id, c.fecha_cita
  FROM citas c
  JOIN detalle_medico dm ON c.Id_medico = dm.Id_detalle_medico
  WHERE c.id_cita = ?";

  $stmt = $conexion->prepare($sql_cita);
  $stmt->bind_param("i", $id_cita);
  $stmt->execute();

  $resultado = $stmt->get_result();

  if ($fila = $resultado->fetch_assoc()) {
    $id_medico_cita = (int)$fila['medico_id'];
    $fecha_cita = $fila['fecha_cita'];
  }
}

// Si la cédula viene en el GET, buscamos los datos demográficos básicos para mostrarlos al inicio.
// El historial completo (alergias, antecedentes, etc.) se cargará por AJAX/JavaScript.
if (!empty($cedula) && isset($conexion)) {
  try {
    $safe_cedula = $conexion->real_escape_string($cedula);

    // OBTENER DATOS DEMOGRÁFICOS BÁSICOS
    $sql_data = "
            SELECT p.nombre, p.apellido, p.cedula, p.fecha_nacimiento
            FROM persona p
            WHERE p.cedula = '$safe_cedula'
        ";
    $result_data = $conexion->query($sql_data);
    $datos_paciente = $result_data ? $result_data->fetch_assoc() : [];
    if ($result_data) $result_data->free();

    if (!$datos_paciente) {
      $error_busqueda = "Paciente con Cédula " . htmlspecialchars($cedula) . " no encontrado.";
    }
  } catch (\mysqli_sql_exception $e) {
    $error_busqueda = "Error de base de datos (MySQLi): " . $e->getMessage();
  }
}

// Buscamos el ID del historial más reciente de este paciente
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Consultas | Nueva</title>
  <?php
  include('includes/headerNav2.php');
  ?>
</head>
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
  .modal.in .modal-dialog,
  #avisoModalConsulta,
  #modalGuardarConsulta,
  #modalRegistroRapido,
  #modalConfirmacionMenor,
  #modalPatologiaRapida,
  #modalAlergiaRapida,
  #modalAlertaSalud,
  #modalTimelineCompleta {
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

  #avisoModalConsulta,
  #modalPatologias,
  #modalAlergias,
  #modalGuardarPacienteMenor {
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
  .nav-tabs li.disabled-table a {
    color: #b2b2b2 !important;
    /* Color gris para indicar que está bloqueada */
  }

  .modal-header-danger {
    background-color: #dc3545;
    /* Rojo de Bootstrap bg-danger */
    color: white;
  }

  /* NUEVO: Estilo para etiquetas de medicamento en el modal */
  .medicamento-tag {
    display: inline-block;
    padding: 5px 10px;
    margin-right: 5px;
    margin-bottom: 5px;
    background-color: #3c8dbc;
    /* Azul de AdminLTE */
    color: white;
    border-radius: 4px;
    font-size: 14px;
    cursor: default;
  }

  .medicamento-tag .close-btn {
    margin-left: 8px;
    font-weight: bold;
    cursor: pointer;
    color: white;
    text-shadow: none;
    opacity: 1;
    /* Asegurar visibilidad */
  }
</style>

<body>

  <?php if ($error_busqueda) : ?>
    <div class="modal fade" id="modal-error" tabindex="-1" role="dialog" aria-labelledby="modal-error-label" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header modal-header-danger">
            <h5 class="modal-title" id="modal-error-label"><i class="fa fa-exclamation-triangle"></i> Error de Búsqueda</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <p><?php echo htmlspecialchars($error_busqueda); ?></p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>
    <script>
      // Muestra el modal de error al cargar la página si el error existe
      $(document).ready(function() {
        $('#modal-error').modal('show');
      });
    </script>
  <?php endif; ?>

  <div class="content-wrapper">
    <section class="content-header">
      <h1>Nueva Consulta Medica</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-users"></i>Pacientes</a></li>
        <li class="active"><a href="#"><i class="fa fa-pencil"></i>Consultas</a></li>
      </ol>
    </section>

    <section class="content">

      <div class="row">
        <div class="col-xs-12">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active" data-tab-name="consulta"><a href="#consulta" data-toggle="table">Información del Paciente</a></li>

              <li data-tab-name="historial" class="disabled-table"><a href="#historial" data-toggle="table">Historial Clínico</a></li>

              <li data-tab-name="antecedentes" class="disabled-table"><a href="#antecedentes" data-toggle="table">Antecedentes del Paciente</a></li>
              <li data-tab-name="diagnostico" class="disabled-table"><a href="#diagnostico" data-toggle="table">Diagnóstico</a></li>
              <li data-tab-name="indicacion_tratamiento" class="disabled-table"><a href="#indicacion_tratamiento" data-toggle="table">Indicación Final y Tratamiento</a></li>
            </ul>

            <div class="tab-content">

              <div class="tab-pane tab-panel active" id="consulta">
                <section id="consulta" style="min-height: 480px;">
                  <form action="../../cfg/agregar/agregar_consulta.php" id="formularioConsulta" class="form-group" method="POST">
                    <input type="hidden" name="id_cita_atendida" value="<?php echo $id_cita ?>">
                    <input type="hidden" id="id_historial_global" value="">

                    <label class="control-label"></label>
                    <div class="col-sm-1 pull-left" style="margin-top: 30px;">
                      <select name="tipo_cedula_paciente" id="tipo_cedula_paciente" class="form-control" style="width: 60px; <?php echo !empty($cedula) ? 'pointer-events: none; background-color: #eee;' : ''; ?>" <?php echo !empty($cedula) ? 'tabindex="-1"' : ''; ?>>
                        <option value="PN" <?php echo (isset($_GET['tipo_cedula']) && $_GET['tipo_cedula'] == 'PN') ? 'selected' : ''; ?>>PN-</option>
                        <option value="V" <?php echo (isset($_GET['tipo_cedula']) && $_GET['tipo_cedula'] == 'V' || (isset($tipo_cedula) && $tipo_cedula == 'V') || !isset($_GET['tipo_cedula'])) ? 'selected' : ''; ?>>V-</option>
                        <option value="RP" <?php echo (isset($_GET['tipo_cedula']) && $_GET['tipo_cedula'] == 'RP' || (isset($tipo_cedula) && $tipo_cedula == 'RP') || !isset($_GET['tipo_cedula'])) ? 'selected' : ''; ?>>REP-</option>
                        <!--<option value="E" <?php echo (isset($_GET['tipo_cedula']) && $_GET['tipo_cedula'] == 'E' || (isset($tipo_cedula) && $tipo_cedula == 'E')) ? 'selected' : ''; ?>>E-</option>-->
                      </select>
                    </div>
                    <div class="col-sm-3">
                      <p>Cedula/Documento (*)</p>
                      <input type="text" class="form-control" name="cedula_paciente" id="cedula_paciente" placeholder="N° de Cedula" value="<?php echo htmlspecialchars($cedula); ?>" <?php echo !empty($cedula) ? 'readonly' : ''; ?> required>
                    </div>
                    <div class="col-sm-4">
                      <p>Nombre del Paciente (*)</p>
                      <input type="text" id="nombre_paciente" class="form-control" name="nombre_paciente" readonly placeholder="Nombre del Paciente" value="<?php echo htmlspecialchars($datos_paciente['nombre'] ?? '') . ' ' . htmlspecialchars($datos_paciente['apellido'] ?? ''); ?>" require>
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-3">
                      <p>Fecha de nacimiento (*):</p>
                      <input type="date" class="form-control pull-right" id="fecha_nacimiento_paciente" name="fecha_nacimiento_paciente" onchange="calcularEdadPaciente();" max="" required readonly value="<?php echo htmlspecialchars($datos_paciente['fecha_nacimiento'] ?? ''); ?>">
                    </div>
                    <div class="col-sm-1" style="margin-top: 0px; margin-left:-5px;">
                      <p>Edad:</p>
                      <input type="text" class="form-control pull-right" id="edad_paciente" name="edad_paciente" readonly>
                    </div>
                    <br><br><br><br>
                    <div class="col-sm-4">
                      <p>Medico (*):</p>
                      <?php
                      $id_persona_logueada = $_SESSION['id'] ?? 0; 
                      $rol_usuario = $_SESSION['rol'] ?? 0; 

                      $es_admin = ($rol_usuario == 1); // 1 es Administrador en tu BD
                      $id_medico_seleccionado = $id_medico_cita; // Prioridad 1: Si viene de una cita agendada
                      $bloquear_medico = false;

                      // Si no hay un médico ya asignado por una cita, y el usuario NO es admin
                      if (empty($id_medico_seleccionado) && !$es_admin) {
                          // Buscamos si el usuario logueado está registrado como médico
                          $sql_logged = "SELECT Id_detalle_medico FROM detalle_medico WHERE Id_persona = ?";
                          $stmt_logged = $conexion->prepare($sql_logged);
                          $stmt_logged->bind_param("i", $id_persona_logueada);
                          $stmt_logged->execute();
                          $res_logged = $stmt_logged->get_result();
                          
                          if ($row_logged = $res_logged->fetch_assoc()) {
                              $id_medico_seleccionado = $row_logged['Id_detalle_medico'];
                          }
                          $stmt_logged->close();
                      }

                      // Bloqueamos el select si viene de una cita, o si es un médico haciendo su propia consulta
                      if (!empty($id_medico_cita) || (!$es_admin && !empty($id_medico_seleccionado))) {
                          $bloquear_medico = true;
                      }
                      ?>

                      <select name="medico" id="medico" class="form-control" 
                          <?php echo $bloquear_medico ? 'style="pointer-events: none; background-color: #eee;" tabindex="-1"' : ''; ?> required>
                        <option value="">--- Seleccione el medico ---</option>
                        <?php
                        $sql_medico = "SELECT dm.Id_detalle_medico, dm.Id_persona AS id, p.nombre, p.apellido, dm.tipo_medico 
                        FROM detalle_medico dm
                        INNER JOIN persona p ON dm.Id_persona = p.id
                        WHERE p.estatus IN (1, 2) AND dm.tipo_medico = 'Interno'
                        ORDER BY p.nombre ASC";

                        $resultado_medico = $conexion->query($sql_medico);

                        while ($row_medico = $resultado_medico->fetch_assoc()) {
                          $id_db = (int)$row_medico['Id_detalle_medico'];
                          $id_objetivo = (int)$id_medico_seleccionado;

                          // Si coinciden, añade el atributo 'selected'
                          $selected = ($id_db === $id_objetivo) ? 'selected="selected"' : '';

                          echo '<option value="' . $id_db . '" ' . $selected . '>' .
                            htmlspecialchars($row_medico['nombre'] . ' ' . $row_medico['apellido']) .
                            '</option>';
                        }
                        ?>
                      </select>
                    </div>
                    <div class="col-sm-4">
                      <p>Fecha de consulta (*):</p>
                      <input type="date" name="fecha_consulta" id="fecha_consulta" class="form-control" value="<?php echo !empty($fecha_cita) ? $fecha_cita : date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" <?php echo !empty($fecha_cita) ? 'readonly' : ''; ?> required>
                    </div>
                    <div class="col-sm-4">
                      <p>Peso (kg)</p>
                      <input type="text" step="0.1" id="peso" class="form-control" name="peso" placeholder="Ej: 70.5">
                    </div>
                    <br><br><br><br>
                    <div class="col-sm-4">
                      <p>Talla (cm)</p>
                      <input type="text" id="talla" class="form-control" name="talla" placeholder="Ej: 172">
                    </div>
                    <div class="col-sm-4">
                      <p>Temperatura (°C)</p>
                      <input type="text" step="0.1" id="temperatura" class="form-control" name="temperatura" placeholder="Ej: 36.7">
                    </div>
                    <div class="col-sm-4">
                      <p>Tensión (mmHg)</p>
                      <input type="text" id="tension" class="form-control" name="tension" placeholder="Ej: 120/80">
                    </div>
                    <br><br><br><br>
                    <div class="col-sm-4">
                      <p>Frecuencia Cardíaca (lpm)</p>
                      <input type="text" id="frecuencia_cardiaca" class="form-control" name="frecuencia_cardiaca" placeholder="Ej: 72">
                    </div>
                    <div class="col-sm-4">
                      <p>Saturación de Oxígeno (%)</p>
                      <input type="text" id="saturacion" class="form-control" name="saturacion" placeholder="Ej: 98">
                    </div>
                    <div class="col-sm-4">
                      <p>Frecuencia Respiratoria (rpm)</p>
                      <input type="text" id="frecuencia_respiratoria" class="form-control" name="frecuencia_respiratoria" placeholder="Ej: 16">
                    </div>
                    <br><br><br><br>
                    <div class="col-sm-12">
                      <p>Motivo de la consulta (*)</p>
                      <textarea id="motivo_consulta" name="motivo_consulta" class="form-control" rows="3" placeholder="Motivo principal o queja del paciente" <?php echo !empty($motivo_cita) ? 'readonly' : ''; ?> required><?php echo htmlspecialchars($motivo_cita); ?></textarea>
                    </div>
                    <div style="float:right; margin-top:2%;">
                      <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#modalConfirmarRegresoConsulta">Regresar</button>
                      <button type="button" class="btn btn-primary next-table" id="consulta_button" data-table-actual="consulta" data-table-siguiente="historial">Siguiente</button>
                    </div>
                </section>
              </div>

              <div role="tabpanel" class="tab-pane tab-panel" id="historial" style="margin-bottom:5%;">
                <br>
                <h4>Últimas 2 Consultas Registradas (para vista rápida)</h4>
                <div id="historial-consultas-container">
                  <div class="alert alert-info">
                    Esperando la Cédula del paciente para cargar el historial...
                  </div>
                </div>
                <div class="col-sm-4 pull-left">
                  <button type="button" class="btn btn-info" onclick="abrirTimelineCompleta()">
                    <i class="fa fa-clock-o"></i> Ver Todo el Historial
                  </button>
                </div>
                <div style="float:right; margin-top:0%;">
                  <button type="button" class="btn btn-secondary prev-table" data-table-anterior="consulta">Atrás</button>
                  <button type="button" class="btn btn-primary next-table" data-table-actual="historial" data-table-siguiente="antecedentes">Siguiente</button>
                </div>
              </div>

              <div class="tab-pane tab-panel" id="antecedentes">
                <section id="antecedentes" style="margin-bottom:18%;">
                  <div class="col-sm-6">
                    <p id="label_perinatales">Perinatales (Solo menores de edad):</p>
                    <textarea id="perinatales" name="perinatales" class="form-control" rows="3" placeholder="Información de antecedentes perinatales"></textarea>
                  </div>
                  <div class="col-sm-6">
                    <p>Familiares:</p>
                    <textarea id="familiares" name="familiares" class="form-control" rows="3" placeholder="Motivo principal o queja del paciente" required></textarea>
                  </div>
                  <br><br><br><br><br><br>
                  <div class="col-sm-6">
                    <p>Personales (Sexualidad y Reproductivos):</p>
                    <textarea id="sexualidad_reproductivos" name="sexualidad_reproductivos" class="form-control" rows="3" placeholder="Motivo principal o queja del paciente" required></textarea>
                  </div>
                  <div class="col-sm-6">
                    <p>Personales (Estilo y Vida):</p>
                    <textarea id="estilo_vida" name="estilo_vida" class="form-control" rows="3" placeholder="Motivo principal o queja del paciente" required></textarea>
                  </div>
                  <br><br><br><br><br><br>
                  <div class="col-sm-6" style="margin-top: 15px;">
                    <div style="background: #f0f8ff; border-left: 5px solid #337ab7; padding: 10px; border-radius: 4px;">
                      <strong style="color: #337ab7;"><i class="fa fa-exclamation-circle"></i> Alergias:</strong>
                      <div id="alergias-container" style="margin-top: 10px;">
                        <p class="text-muted">Ingrese la cédula para cargar...</p>
                      </div>
                    </div>
                    <br>
                    <button type="button" class="btn btn-xs btn-primary" onclick="abrirModalAlergia('')">
                      <i>+</i> Añadir
                    </button>
                  </div>
                  <div class="col-sm-6" style="margin-top: 15px;">
                    <div style="background: #fff5f5; border-left: 5px solid #d9534f; padding: 10px; border-radius: 4px;">
                      <strong style="color: #d9534f;"><i class="fa fa-Stethoscope"></i> Patologías:</strong>
                      <div id="patologias-container" style="margin-top: 10px;">
                        <p class="text-muted">Ingrese la cédula para cargar...</p>
                      </div>
                    </div>
                    <br>
                    <button type="button" class="btn btn-xs btn-danger" onclick="abrirModalPatologia()">
                      <i>+</i> Añadir
                    </button>
                  </div>

                  <div style="float:right; margin-top:2%;">
                    <button type="button" class="btn btn-secondary prev-table" data-table-anterior="historial">Atrás</button>
                    <button type="button" class="btn btn-primary next-table" data-table-actual="antecedentes" data-table-siguiente="diagnostico">Siguiente</button>
                  </div>
                </section>
              </div>

              <div class="tab-pane tab-panel" id="diagnostico">
                <section id="diagnostico" style="height: 460px;">

                  <div class="col-md-12">
                    <div class="form-group">
                      <label for="lectura_examenes"><b>Lectura de Resultados (Exámenes):</b></label>
                      <textarea class="form-control" name="lectura_examenes" id="lectura_examenes" rows="3" placeholder="Ej: Glucemia 110, Colesterol alto..."></textarea>
                    </div>
                  </div>

                  <br><br><br><br>

                  <div class="col-sm-12">
                    <p>Diagnóstico (*)</p>
                    <textarea name="diagnostico_text" id="diagnostico_text" class="form-control" rows="4" placeholder="Diagnostico del paciente.." required></textarea>
                  </div>

                  <br><br><br><br>

                  <div class="col-sm-6" style="margin-top: 20px;">
                    <p>Evolución / Resultado (*)</p>
                    <select name="estado_paciente" class="form-control" required>
                      <option value="">Seleccione Estado</option>
                      <option value="Primera Consulta">Primera Consulta (Inicio del Caso)</option>
                      <option value="Sano">Mejoría Total (Sano)</option>
                      <option value="En tratamiento">Mejoría Parcial (En tratamiento)</option>
                      <option value="Sin mejoria">Sin Mejoría (Requiere Re-entrada)</option>
                      <option value="Empeoro">Empeoramiento</option>
                    </select>
                  </div>

                  <div class="col-sm-6" style="margin-top: 20px;">
                    <p>¿Reportó Reacción Adversa?</p>
                    <select name="reaccion_adversa" id="reaccion_adversa" class="form-control">
                      <option value="No">No</option>
                      <option value="Si">Sí, presentó reacción</option>
                    </select>
                  </div>

                  <div class="col-sm-6" style="margin-top: 10px;">
                    <p>Detalle de Evolución / Resultados (*)</p>
                    <textarea name="evolucion_resultado" id="evolucion_resultado" class="form-control" rows="1" placeholder="Descripcion de la evalucion del paciente" required></textarea>
                  </div>

                  <div class="col-sm-6" id="detalle_reaccion" style="margin-top: 20px; display:none;">
                    <textarea name="detalle_reaccion" class="form-control" rows="2" placeholder="Describa la reacción (ej: Alergia al componente X)"></textarea>
                  </div>

                  <div style="float:right; margin-top:3%;">
                    <button type="button" class="btn btn-secondary prev-table" data-table-anterior="antecedentes">Atrás</button>
                    <button type="button" class="btn btn-primary next-table" data-table-actual="diagnostico" data-table-siguiente="indicacion_tratamiento">Siguiente</button>
                  </div>
                </section>
              </div>

              <div class="tab-pane tab-panel" id="indicacion_tratamiento">
                <section id="indicacion_tratamiento" style="height: 420px;">
                  <div class="col-sm-3" id="group_fecha">
                    <p>Próxima Cita (Fecha:</p>
                    <input type="date" name="fecha_cita" id="fecha_cita" min="<?php echo date('Y-m-d'); ?>" class="form-control pull-right">
                  </div>
                  <div class="col-sm-3" id="group_hora">
                    <p>Hora):</p>
                    <input type="time" name="hora_cita" id="hora_cita" class="form-control pull-right">
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="control-label">Medicamentos:</label>

                      <button type="button" class="btn btn-info btn-md btn-block" id="medicamento_agg" data-toggle="modal" data-target="#modalSeleccionMedicamentos" data-placement="top" title="Ningún medicamento agregado">
                        <i>Asignar Medicamento</i>
                      </button>

                      <input type="hidden" id="medicamento" name="medicamento_full_data" class="form-control" value="" readonly placeholder="Medicamentos seleccionados aparecerán aquí...">
                      <div id="inputs_medicamentos_ocultos"></div>
                    </div>
                  </div>

                  <br><br><br><br>

                  <div class="col-md-12">
                    <div class="card">
                      <div class="card-body">
                        <div class="row">
                          <div class="col-md-6">
                            <label for="entregado_a">
                              <i></i> <b>Medicamento entregado a / Responsable:</b>
                            </label>
                            <input type="text" name="entregado_a" id="entregado_a" class="form-control" placeholder="Nombre completo del adulto que recibe la medicación" title="Campo obligatorio para pacientes menores de edad">
                          </div>
                          <div class="col-md-6">
                            <label><b>Parentesco/Relación:</b></label>
                            <select name="parentesco_representante" id="parentesco_representante" class="form-control">
                              <option selected value="">Ninguno</option>
                              <option value="Padre">Padre</option>
                              <option value="Madre">Madre</option>
                              <option value="Abuelo/a">Abuelo/a</option>
                              <option value="Tio/a">Tío/a</option>
                              <option value="Tutor Legal">Tutor Legal</option>
                              <option value="Otro">Otro</option>
                            </select>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <br><br><br><br>

                  <div class="col-md-12">
                    <label for="examenes_solicitados"><b>Nuevos Exámenes Solicitados:</b></label>
                    <input type="text" class="form-control" name="examenes_solicitados" id="examenes_solicitados" placeholder="Ej: Hematología completa, Eco Renal...">
                  </div>

                  <br><br><br><br>

                  <div class="col-sm-6">
                    <p>Indicaciones (*):</p>
                    <textarea name="indicaciones" id="indicaciones" class="form-control" rows="4" required></textarea>
                  </div>
                  <div class="col-sm-6">
                    <p>Notas y observaciones adicionales:</p>
                    <textarea name="notas_adicionales" id="notas_adicionales" class="form-control" rows="4"></textarea>
                  </div>
                  <div style="float:right; margin-top:2%;">
                    <button type="button" class="btn btn-secondary prev-table" data-table-anterior="diagnostico">Atrás</button>
                    <button type="button" class="btn btn-success" id="btn_finalizar_consulta">Guardar</button>
                  </div>
                </section>
              </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <div class="modal" id="modalSeleccionMedicamentos" tabindex="-1" role="dialog" aria-labelledby="modalSeleccionMedicamentosLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: #3c8dbc; color: white; border-top-left-radius: 4px; border-top-right-radius: 4px;">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 1;">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title" id="modalSeleccionMedicamentosLabel">
            <i class="fa fa-medkit"></i> Seleccionar Medicamentos
          </h4>
        </div>

        <div class="modal-body">
          <div class="row">
            <div class="col-xs-9">
              <div class="form-group">
                <label for="selectMedicamentos">Medicamento:</label>
                <div class="input-group">
                  <select id="selectMedicamentos" class="form-control select2" style="width: 100%;">
                    <option value="">Seleccione un medicamento...</option>
                    <?php while ($row = $query_medicamentos->fetch_assoc()) : ?>
                      <option value="<?php echo $row['Id']; ?>" data-nombre="<?php echo $row['nombre_medicamento']; ?>" data-via="<?php echo $row['via_aplicacion']; ?>" data-contenido="<?php echo $row['contenido_neto']; ?>" data-presentacion="<?php echo $row['nombre_presentacion']; ?>">
                        <?php echo $row['nombre_medicamento'] . " (" . $row['cantidad_unidad_medida'] . " " . $row['unidad'] . ")"; ?>
                      </option>
                    <?php endwhile; ?>
                  </select>
                  <span class="input-group-btn">
                    <button class="btn btn-info" type="button" id="btnInfoMedicamento" data-toggle="tooltip" title="Detalles" style="height: 34px;">
                      <i><img src="../../recursos/imagenes/iconos/info.png" style="width:15px; height:15px;"</i>
                    </button>
                  </span>
                </div>
              </div>
            </div>

            <div class="col-xs-3">
              <div class="form-group">
                <label>&nbsp;</label> <button type="button" class="btn btn-primary btn-block" id="btnAnadirMedicamento">
                  <i class="fa fa-plus"></i> Añadir
                </button>
              </div>
            </div>
          </div>

          <hr style="margin: 10px 0;">

          <div class="row">
            <div class="col-xs-12">
              <h5 style="font-weight: bold; color: #555;">Medicamentos Seleccionados:</h5>
              <div id="contenedorMedicamentosSeleccionados" style="min-height: 80px; border: 2px dashed #ddd; padding: 15px; border-radius: 6px; background-color: #f9f9f9;">
                <p class="text-muted text-center" id="textoVacio">No hay medicamentos añadidos aún.</p>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer" style="background-color: #f4f4f4;">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-success" id="aplicarSeleccionMedicamentos">
            <i class="fa fa-check"></i> Aplicar Selección
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalAlergiaRapida" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background: #337ab7; color: white;">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title"><i class="fa fa-warning"></i> Nueva Alergia</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label>Nombre de la Alergia:</label>
                <select id="select_alergia" class="form-control">
                  <option value="">-- Seleccione una alergia --</option>
                  <?php while ($a = $query_alergias->fetch_assoc()) : ?>
                    <option value="<?= $a['Id_alergias_conocidas'] ?>"><?= $a['nombre_alergia'] ?></option>
                  <?php endwhile; ?>
                </select>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Fecha de Detección:</label>
            <input type="date" id="fecha_alergia" class="form-control" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary" onclick="guardarAlergia()">Guardar Alergia</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalPatologiaRapida" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background: #d9534f; color: white;">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title"><i class="fa fa-heartbeat"></i> Nueva Patología</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label>Nombre de la Patología:</label>
                <select id="select_patologia" class="form-control">
                  <option value="">-- Seleccione una patologia --</option>
                  <?php while ($p = $query_patologias->fetch_assoc()) : ?>
                    <option value="<?= $p['Id_patologia'] ?>">
                      [<?= $p['codigo_cie'] ?>] <?= $p['nombre_patologia'] ?>
                    </option>
                  <?php endwhile; ?>
                </select>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Fecha de Diagnóstico:</label>
            <input type="date" id="fecha_patologia" class="form-control" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-danger" onclick="guardarPatologia()">Guardar Patología</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="avisoModalConsulta" tabindex="-1" role="dialog" aria-labelledby="avisoModalConsultaLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header modal-header-danger">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="avisoModalConsultaLabel"><i class="fa fa-exclamation-triangle"></i> Aviso</h4>
        </div>
        <div class="modal-body">
          <p id="avisoTextoConsulta"></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalGuardarConsulta" tabindex="-1" role="dialog" aria-labelledby="modalGuardarConsultaLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: #00a65a; color: white;">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="modalGuardarConsultaLabel"><i class="fa fa-save"></i> Confirmacion de Guardado</h4>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea guardar la información de la nueva consulta?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" id="close" data-dismiss="modal">Regresar</button>
          <button type="button" class="btn btn-success" id="enviar">Guardar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalConfirmarRegresoConsulta" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarRegresoConsultaLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header modal-header-danger">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="modalConfirmarRegresoConsultaLabel"><i class="fa fa-sign-out"></i> Confirmación de Regreso</h4>
        </div>
        <div class="modal-body">
          <p>Al hacer clic en "Abandonar Formulario", perderá todos los datos no guardados. ¿Desea continuar?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <a href="consulta_listado.php" class="btn btn-danger">Abandonar Formulario</a>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalRegistroRapido" tabindex="-1" role="dialog" aria-labelledby="modalRegistroRapidoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: #00c0ef; color: white;">
          <h4 class="modal-title" id="modalRegistroRapidoLabel"><i class="fa fa-user-plus"></i> Añadir Nuevo Paciente</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="contenidoModalRegistro">
          <p class="text-center"><i class="fa fa-spinner fa-spin fa-2x fa-fw"></i> Cargando Formulario...</p>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalAlertaSalud" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content ">
        <div class="modal-header modal-header-danger">
          <h5 class="modal-title text-white" id="exampleModalLabel">
            <i class="fas fa-exclamation-circle"></i> AVISO IMPORTANTE
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">
          <div class="text-center mb-3">
            <i class="fas fa-user-shield fa-4x text-warning"></i>
          </div>
          <div id="contenedorAlertas" class="px-3">
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-danger btn-block" data-dismiss="modal">ACEPTAR Y CONTINUAR</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalTimelineCompleta" tabindex="-1" role="dialog" aria-labelledby="timelineLabel">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color: #3c8dbc; color: white;">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="timelineLabel"><i class="fa fa-history"></i> Historial Completo de Consultas</h4>
        </div>
        <div class="modal-body" style="max-height: 450px; overflow-y: auto;">
          <div id="contenedor-timeline-completa">
            <div class="text-center"><i class="fa fa-refresh fa-spin"></i> Cargando historial...</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalConfirmacionMenor" tabindex="-1" role="dialog" aria-labelledby="modalConfirmacionMenorLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">

        <div class="modal-header modal-header-danger">
          <h5 class="modal-title" id="modalConfirmacionMenorLabel"><i class="fa fa-question-circle"></i> Confirmacion de Tipo de Registro</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">
          <p id="textoConfirmacionMenor" class="text-center"></p>
          <hr>
          <p class="text-center">Por favor, seleccione el tipo de registro apropiado:</p>
        </div>

        <div class="modal-footer">
          <a href="pacientes_menores_agregar.php?pagina=consulta" class="btn btn-warning">Registro del Menor</a>
          <a href="pacientes_agregar.php?pagina=consulta" class="btn btn-primary">Registro Estandar</a>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </div>
  </div>

  <?php
  include('includes/footer.php');
  ?>

</body>
<script>
  // =====================================================================
  // DECLARACIÓN DE VARIABLES Y ELEMENTOS
  // =====================================================================
  const cedulaPacienteInput = document.getElementById('cedula_paciente');
  const tipoCedulaPacienteSelect = document.getElementById('tipo_cedula_paciente');
  const nombrePacienteInput = document.getElementById('nombre_paciente');
  const fechaNacimientoPacienteInput = document.getElementById('fecha_nacimiento_paciente');
  const edadPacienteInput = document.getElementById('edad_paciente');

  // =====================================================================
  // FUNCIONES DE UTILIDAD Y VALIDACIÓN
  // =====================================================================

  function mostrarAviso(texto, onHiddenCallBack = null) {
    const $avisoModalConsulta = $('#avisoModalConsulta').modal('show');
    $('#avisoTextoConsulta').html(texto);

    $avisoModalConsulta.off('hidden.bs.modal');

    if (onHiddenCallBack && typeof onHiddenCallBack === 'function') {
      $avisoModalConsulta.one('hidden.bs.modal', function() {
        onHiddenCallBack();
      });
    }
    $avisoModalConsulta.modal('show');
  }

  $(document).ready(function() {
    $('#medicamento_agg').tooltip();
  });


  /**
   * Cierra un modal de Bootstrap correctamente
   * @param {jQuery} modalElement El objeto jQuery del modal a cerrar
   */
  function closeCustomModal(modalElement) {
    modalElement.removeClass('in').addClass('out');
    setTimeout(() => {
      modalElement.modal('hide').removeClass('out');
    }, 100); // Duración de la animación
  }

  // CORRECCIÓN: Eventos para cerrar el modal de aviso
  $('#avisoModalConsulta .close, #avisoModalConsulta .btn-second').on('click', function() {
    closeCustomModal($('#avisoModalConsulta'));
  });

  $('#modalGuardarConsulta .close, #modalGuardarConsulta .btn-second').on('click', function() {
    closeCustomModal($('#modalGuardarConsulta'));
  });

  $('#modalAlergiaRapida .close, #modalAlergiaRapida .btn-second').on('click', function() {
    closeCustomModal($('#modalAlergiaRapida'));
  });

  $('#modalPatologiaRapida .close, #modalPatologiaRapida .btn-second').on('click', function() {
    closeCustomModal($('#modalPatologiaRapida'));
  });

  $('#modalAlertaSalud .close, #modalAlertaSalud .btn-danger').on('click', function() {
    closeCustomModal($('#modalAlertaSalud'));
  });

  $('#modalTimelineCompleta .close, #modalTimelineCompleta .btn-second').on('click', function() {
    closeCustomModal($('#modalTimelineCompleta'));
  });

  document.getElementById('reaccion_adversa').addEventListener('change', function() {
    const div = document.getElementById('detalle_reaccion');
    div.style.display = (this.value === 'Si') ? 'block' : 'none';
  });

  document.getElementsByName('estado_paciente')[0].addEventListener('change', function() {
    let detalle = document.getElementsByName('evolucion_resultado')[0];
    if (this.value === 'Primera Consulta') {
      detalle.value = "Paciente acude por primera vez. Se inicia protocolo.";
    } else {
      detalle.value = ""; // Limpiar para que el médico escriba la evolución real
    }
  });

  const fechaInput = document.getElementById('fecha_consulta');
  if (fechaInput) {
    fechaInput.addEventListener('change', function() {
      const hoy = new Date().toISOString().split('T')[0];
      if (this.value > hoy) {
        mostrarAviso("La fecha de la consulta no puede ser mayor al día de hoy.");
        this.value = hoy; // Resetear al día de hoy
      }
    });
  }

  // Escuchar cuando cambie la edad (tu función calcularEdadPaciente debe disparar esto)
  // Si ya tienes un evento que calcula la edad, asegúrate de llamar a  al final.
  /**
   * Navega entre pestañas y gestiona el bloqueo/desbloqueo visual
   * @param {string} tabName El nombre de la pestaña destino (e.g., 'historial')
   */
  function goToTab(tabName) {
    // Oculta todos los .tab-pane
    $('.tab-panel').removeClass('active');
    // Desactiva todos los .nav-tabs li
    $('.nav-tabs li').removeClass('active');

    // Muestra la pestaña de destino
    $(`#${tabName}`).addClass('active');
    // Activa el botón de la pestaña de destino y lo desbloquea
    const $targetTab = $(`.nav-tabs li[data-tab-name="${tabName}"]`);
    $targetTab.addClass('active').removeClass('disabled-table');

    // Muestra la pestaña usando Bootstrap
    $targetTab.find('a[data-toggle="table"]').tab('show');

    // Asegúrate de que las pestañas *anteriores* a la actual sigan desbloqueadas,
    // y las *siguientes* estén bloqueadas (disabled-table).
    let tabs = ['consulta', 'historial', 'antecedentes', 'diagnostico', 'indicacion_tratamiento'];
    let currentIndex = tabs.indexOf(tabName);

    tabs.forEach((name, index) => {
      const $li = $(`.nav-tabs li[data-tab-name="${name}"]`);
      if (index <= currentIndex) {
        $li.removeClass('disabled-table');
      } else {
        $li.addClass('disabled-table');
      }
      // CORRECCIÓN: Limpiar el error visual de la pestaña al navegar
      $li.find('a[data-toggle="table"]').removeClass('input-error');
    });
  }

  /**
   * Valida los campos requeridos y formatos del tab actual.
   * @param {string} tabName El nombre de la pestaña a validar.
   * @returns {Promise<boolean>} Resuelve a true si es válido, false si no.
   */
  async function validarCamposConsulta(tabName) {
    let isValid = true;
    let firstErrorElement = null;

    const fProx = $('input[name="fecha_cita"]');
    const hProx = $('input[name="hora_cita"]');

    const hayFecha = fProx.val() !== "";
    const hayHora = hProx.val() !== "";

    // 1. Limpia y valida campos obligatorios en el tab actual
    $(`#${tabName}`).find('[required]').each(function() {
      const $input = $(this);
      $input.removeClass('input-error');

      if ($input.val().trim() === '' || $input.val() === null || ($input.attr('readonly') && $input.val() === '¡Paciente no encontrado!')) {
        // Validación básica de campo vacío
        isValid = false;
        $input.addClass('input-error');
        if (!firstErrorElement) {
          firstErrorElement = $input;
        }
        console.log(`Error de validación en campo: ${$input.attr('id') || $input.attr('name')}`);
      }
    });

    if (!isValid) {
      mostrarAviso('Por favor, complete todos los campos obligatorios (*) de esta pestaña.');
      // CORRECCIÓN: Ya no se añade input-error a la pestaña
      return false;
    }

    // 2. Lógica de validación específica por pestaña

    // Validación específica para la pestaña de información (1)
    if (tabName === 'consulta') {
      // Validación de existencia de paciente (crucial para continuar)
      if ($('#nombre_paciente').val().includes('no encontrado') || $('#nombre_paciente').val().trim() === '') {
        $(`#cedula_paciente`).addClass('input-error');
        mostrarAviso('Debe ingresar una cédula válida de un paciente registrado.');
        isValid = false;
      }
      if (!isValid) return false;
    }

    // Validación específica para la pestaña de antecedentes (3)
    if (tabName === 'antecedentes') {
      const edadPaciente = parseInt($('#edad_paciente').val());
      const perinatalesVal = $('#perinatales').val().trim();

      if (edadPaciente < 18 && (perinatalesVal === '' || perinatalesVal === 'N/A o no aplica')) {
        isValid = false;
        $('#perinatales').addClass('input-error');
        mostrarAviso('El campo Perinatales es obligatorio para pacientes menores de 18 años.');
      }
      if (!isValid) return false;
    }

    if (tabName === 'indicacion_tratamiento') {
      // Validación de medicamentos (mínimo 1)
      if ($('#medicamento').val().trim() === '') {
        isValid = false;
        $('#medicamento').addClass('input-error');
        mostrarAviso('Debe seleccionar al menos un medicamento para el tratamiento.');
      }
      if ($('#indicaciones').val().trim() === '') {
        isValid = false;
        $('#indicaciones').addClass('input-error');
        mostrarAviso('Debe asignarle un tratamiento al paciente.');
      }

      // 2. Limpiar errores visuales previos
      fProx.removeClass('input-error');
      hProx.removeClass('input-error');

      // 3. Lógica de validación: Si uno tiene datos pero el otro no
      if (hayFecha || hayHora) {
        if (!hayFecha || !hayHora) {
          // Detenemos el envío del formulario

          // Aplicamos el color rojo a los campos faltantes
          if (!hayFecha) fProx.addClass('input-error');
          if (!hayHora) hProx.addClass('input-error');

          // Lanzamos el aviso al final de todas las validaciones
          mostrarAviso("Para agendar una cita de seguimiento debe completar los campos de Fecha y Hora.");

          isValid = false; // Cortamos la ejecución
        }
      }
      if (!isValid) return false;
    }

    return true;
  }

  function abrirTimelineCompleta() {
    // 1. Obtener la cédula (ajustar el ID según consulta_agregar o consulta_editar)
    var cedula = $('#cedula_paciente').val();

    if (!cedula) {
      alert("Por favor, ingrese o busque un paciente primero.");
      return;
    }

    // 2. Mostrar el modal y poner un cargando
    $('#modalTimelineCompleta').modal('show');
    $('#contenedor-timeline-completa').html('<div class="text-center"><i class="fa fa-refresh fa-spin"></i> Cargando historial completo...</div>');

    // 3. Petición AJAX usando el GET que ya definiste en get_historial_ajax.php
    $.ajax({
      url: 'get/get_historial_ajax.php', // Ajusta esta ruta a tu ubicación real
      type: 'GET',
      data: {
        cedula: cedula
      }, // Aquí enviamos el GET que tu PHP espera
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          var consultas = response.data.historial_consultas;
          var htmlTimeline = '';

          if (consultas.length > 0) {
            htmlTimeline += '<div class="timeline">';

            consultas.forEach(function(con) {
              htmlTimeline += `
                            <div class="timeline-item" style="border-left: 2px solid #3c8dbc; padding-left: 15px; margin-bottom: 15px; position: relative;">
                                <i class="fa fa-calendar" style="position: absolute; left: -10px; background: #fff; color: #3c8dbc;"></i>
                                <span class="time" style="color: #666; font-size: 12px;"><i class="fa fa-clock-o"></i> ${con.fecha_consulta}</span>
                                <h4 class="timeline-header" style="margin-top: 0; font-weight: bold; color: #222;">
                                    Motivo: ${con.motivo_consulta}
                                </h4>
                                <div class="timeline-body">
                                    <strong>Diagnóstico:</strong> ${con.diagnostico || 'No registrado'}<br>
                                    <strong>Tratamiento:</strong> ${con.tratamiento_indicado || 'N/A'}
                                </div>
                            </div><hr>`;
            });

            htmlTimeline += '</div>';
          } else {
            htmlTimeline = '<div class="alert alert-info">El paciente no tiene consultas previas registradas.</div>';
          }

          $('#contenedor-timeline-completa').html(htmlTimeline);
        } else {
          $('#contenedor-timeline-completa').html('<div class="alert alert-danger">Error: ' + response.error + '</div>');
        }
      },
      error: function() {
        $('#contenedor-timeline-completa').html('<div class="alert alert-danger">Error crítico al conectar con el servidor.</div>');
      }
    });
  }

  // Cuando cambie la cédula del paciente
  $('#cedula_paciente').on('change', function() {
    var cedula = $(this).val();

    if (cedula !== "") {
      $.ajax({
        url: 'get/get_alertas_paciente.php',
        type: 'POST',
        data: {
          cedula: cedula
        },
        success: function(response) {
          try {
            var datos = JSON.parse(response);

            // 🔹 Tomamos el nombre del paciente desde el input que se llena por AJAX
            var nombrePaciente = $('#nombre_paciente').val();

            // 🔹 Siempre colocamos el nombre del paciente en "entregado_a"
            $('#entregado_a').val(datos.nombre_paciente);

            if (datos.tiene_alertas || datos.es_menor) {
              $('#contenedorAlertas').html(datos.html_contenido);
              $('#modalAlertaSalud').modal('show');
            }

            // =========================
            // 👶 SI ES MENOR DE EDAD
            // =========================
            if (datos.es_menor) {

              // Habilitamos el parentesco
              $('#parentesco_representante').prop('disabled', false);

              // Si existen datos del representante
              if (datos.nombre_representante) {
                $('#entregado_a').val(datos.nombre_representante);
              }

              if (datos.parentesco) {
                $('#parentesco_representante').val(datos.parentesco);
                $('#parentesco_representante').prop('disabled', true);
              } else {
                $('#parentesco_representante').val('');
              }

            }
            // =========================
            // 🧑 SI ES MAYOR DE EDAD
            // =========================
            else {

              // Colocamos el nombre del paciente
              $('#entregado_a').val(datos.nombre_paciente);

              // Limpiamos y bloqueamos parentesco
              $('#parentesco_representante')
                .val('')
                .prop('disabled', true);
            }

          } catch (e) {
            console.error("Error al procesar la respuesta:", response);
          }
        },
        error: function(xhr, status, error) {
          console.error("Error en AJAX:", error);
        }
      });
    }
  });

  /**
   * Aplica restricciones de entrada a campos específicos (solo números, etc.)
   */
  function aplicarRestriccionesConsulta() {
    // Permite solo números y punto decimal
    $('#peso, #temperatura').on('input', function() {
      this.value = this.value.replace(/[^0-9.]/g, '');
    });

    // Permite solo números
    $('#talla, #frecuencia_cardiaca, #saturacion, #frecuencia_respiratoria, #inputDosisCantidad').on('input', function() {
      this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Permite números y el formato de tensión 120/80
    $('#tension').on('input', function() {
      this.value = this.value.replace(/[^0-9/]/g, '');
      e.preventDefault();
    });
  }

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
  // =====================================================================
  // FUNCIONES DE CARGA DE DATOS Y LÓGICA DE CÉDULA
  // =====================================================================

  function calcularEdadPaciente() {
    const fechaNac = document.getElementById('fecha_nacimiento_paciente').value;
    if (!fechaNac) {
      document.getElementById('edad_paciente').value = '';
      return;
    }
    const hoy = new Date();
    const cumple = new Date(fechaNac);
    let edadPaciente = hoy.getFullYear() - cumple.getFullYear();
    const m = hoy.getMonth() - cumple.getMonth();
    if (m < 0 || (m === 0 && hoy.getDate() < cumple.getDate())) {
      edadPaciente--;
    }
    document.getElementById('edad_paciente').value = edadPaciente;

    gestionarPerinatales(edadPaciente);;

  }

  /**
   * Gestiona el campo Perinatales (solo para menores de 18).
   */
  function gestionarPerinatales(edadPaciente) {
    const $perinatales = $('#perinatales');
    const $labelPerinatales = $('#label_perinatales');

    // Solo desbloquear si la edad lo permite y no tiene datos previos cargados
    const tieneDatosPrevios = $perinatales.data('has-data') === true;

    if (edadPaciente < 18) {
      // Si es menor de 18, es obligatorio. Se bloquea solo si YA tiene datos.
      $perinatales.prop('required', true).prop('readonly', tieneDatosPrevios).removeClass('input-error');
      $labelPerinatales.html('Perinatales (Solo menores de edad) OBLIGATORIO para menores (*):');
      $perinatales.attr('placeholder', 'Antecedentes perinatales (requerido para menores de 18)');
      if ($perinatales.val() === "N/A o no aplica") {
        $perinatales.val(''); // Limpiar si tiene el valor de no aplica para forzar la entrada
      }
    } else {
      // Si es mayor de 18, es solo lectura y se rellena con N/A si está vacío.
      $perinatales.prop('required', false).prop('readonly', true);
      $labelPerinatales.html('Perinatales (Solo menores de edad):');
      $perinatales.attr('placeholder', 'N/A o no aplica');

      if ($perinatales.val() === "") {
        $perinatales.val('N/A o no aplica');
      }
    }
  }


  function actualizarAlertas(alergias, patologias) {
    const $alergiasDiv = $('#alergias-container');
    const $patologiasDiv = $('#patologias-container');

    // Estilo para Alergias (Rojo/Peligro)
    const alergiasHtml = alergias.length > 0 ?
      alergias.map(a => `<span class="label label-primary" style="display:inline-block; margin-right:5px; padding:5px 10px; font-size:13px;"><i class="fa fa-warning"></i> ${a}</span>`).join('') :
      '<span class="text-muted">Ninguna conocida.</span>';

    // Estilo para Patologías (Azul/Info o Naranja)
    const patologiasHtml = patologias.length > 0 ?
      patologias.map(p => `<span class="label label-danger" style="display:inline-block; margin-right:5px; padding:5px 10px; font-size:13px;"><i class="fa fa-heartbeat"></i> ${p}</span>`).join('') :
      '<span class="text-muted">Ninguna registrada.</span>';

    $alergiasDiv.html(alergiasHtml);
    $patologiasDiv.html(patologiasHtml);
  }

  /**
   * Muestra solo las últimas 2 consultas.
   */
  function mostrarUltimasConsultas(consultas) {
    const $historialTab = $('#historial-consultas-container');
    $historialTab.empty();

    // Limitar a las 2 primeras consultas
    const consultasLimitadas = consultas.slice(0, 2);

    if (consultasLimitadas.length > 0) {
      consultasLimitadas.forEach(consulta => {
        const html = `
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <h3 class="panel-title">Consulta del ${new Date(consulta.fecha_consulta).toLocaleDateString('es-VE')} (Dr/a. ${consulta.medico_nombre})</h3>
                        </div>
                        <div class="panel-body">
                            <p><strong>Motivo de la Consulta:</strong> ${consulta.motivo_consulta}</p>
                            <p><strong>Diagnóstico:</strong> ${consulta.diagnostico}</p>
                            <p><strong>Tratamiento Indicado:</strong> ${consulta.tratamiento_indicado}</p>
                        </div>
                    </div>
                `;
        $historialTab.append(html);
      });
      if (consultas.length > 2) {
        $historialTab.append('<p class="text-muted text-center">...Hay más consultas registradas. Consulte el historial completo para verlas.</p>');
      }
    } else {
      $historialTab.html(`
                <div class="alert alert-warning">
                    No se encontraron consultas previas para este paciente.
                </div>
            `);
    }
  }
  /**
   * Carga el historial completo del paciente (alergias, antecedentes, consultas) usando AJAX.
   */
  function cargarHistorialCompleto(cedula) {
    $.ajax({
      url: 'get/get_historial_ajax.php?cedula=' + cedula,
      method: 'GET',
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          const data = response.data;

          actualizarAlertas(data.alergias, data.patologias);

          const idHist = response.id_historial;
          const ant = data.antecedentes;

          $('#id_historial_global').val(idHist);
          // Rellenar campos y bloquear si tienen datos
          const antec = {
            '#perinatales': ant.perinatales,
            '#familiares': ant.familiares,
            '#sexualidad_reproductivos': ant.sexualidad_reproductivos,
            '#estilo_vida': ant.estilo_vida,
            '#notas_adicionales': ant.notas_adicionales
          };

          for (const id in antec) {
            const val = antec[id] || '';
            const $campo = $(id);
            $campo.val(val);

            // Bloqueo si el valor existe y no está vacío (excluyendo el placeholder 'N/A' que se gestiona aparte)
            if (val.trim() !== '' && val.trim().toLowerCase() !== 'n/a o no aplica') {
              $campo.data('has-data', true).prop('readonly', true);
            } else {
              // CORRECCIÓN SOLICITADA: Asegurar que se desbloquea si no hay datos.
              $campo.data('has-data', false).prop('readonly', false);
            }
          }

          // La gestión de Perinatales es especial (depende de la edad)
          gestionarPerinatales(parseInt($('#edad_paciente').val()));
          mostrarUltimasConsultas(data.historial_consultas);
        } else {
          // Si no hay historial, se inicializan vacíos y desbloqueados (por defecto)
          actualizarAlertas([], []);
          mostrarUltimasConsultas([]);
          gestionarPerinatales(parseInt($('#edad_paciente').val() || 100));

          mostrarAviso('⚠️ Aviso al cargar historial: ' + (response.error || 'No se pudo obtener el historial.'));
        }
      },
      error: function(xhr, status, error) {
        console.error("Error al cargar historial:", status, error);
        actualizarAlertas(['Error de conexión'], ['Error de conexión']);
        $('#historial-consultas-container').html('<div class="alert alert-danger">Error de conexión al cargar el historial clínico.</div>');
        gestionarPerinatales(parseInt($('#edad_paciente').val() || 100));
        mostrarAviso('⚠️ Error de Conexión: No se pudo comunicar con el servidor para verificar la cédula. Intente de nuevo más tarde.');
      }
    });
  }

  function filtrarCedulaConsulta() {
    const tipo = tipoCedulaPacienteSelect.value;
    let valor = cedulaPacienteInput.value;
    let nuevoValor = valor;
    let longitudMaxima = 20;

    if (tipo === 'V' || tipo === 'E') {
      nuevoValor = valor.replace(/[^0-9]/g, '');
      longitudMaxima = 8;
    } else if (tipo === 'RP') {
      // Deja solo números primero
      nuevoValor = valor.replace(/[^0-9]/g, '');
      // Si pasa de 8 dígitos, inserta el guion antes del noveno
      if (nuevoValor.length > 8) {
        nuevoValor = nuevoValor.substring(0, 8) + '-' + nuevoValor.substring(8, 9);
      }
      longitudMaxima = 10; // 8 números + guion + 1 número
    } else if (tipo === 'PN') {
      nuevoValor = valor.replace(/[^0-9a-zA-Z- ]/g, '');
      longitudMaxima = 20;
    }

    cedulaPacienteInput.maxLength = longitudMaxima;
    cedulaPacienteInput.value = nuevoValor;
  }

  // AÑADIDO: Nueva función para cargar el formulario de registro en el modal
  function cargarFormularioRegistro() {
    const modalBody = $('#contenidoModalRegistro');
    const $avisoModalConsulta = $('#avisoModalConsulta'); // Referencia al modal de error

    // 1. Limpiamos y mostramos un loader
    modalBody.html('<div class="text-center" style="padding: 30px;"><i class="fa fa-spinner fa-spin fa-3x fa-fw"></i> Cargando Formulario de Registro...</div>');

    // 2. Cargamos el contenido del archivo externo (Asegúrate de que esta ruta sea correcta)
    $.get('modales/consultas/modal_pacientes_agregar_consulta.php', {}, function(html_content) {
      modalBody.html(html_content);

      // --- CAMBIO CLAVE: CERRAR EL MODAL DE AVISO/ERROR ANTES DE MOSTRAR EL DE REGISTRO ---
      closeCustomModal($avisoModalConsulta);

      // 3. Mostrar el modal de registro
      $('#modalRegistroRapido').modal('show');
    }).fail(function() {
      modalBody.html('<p class="alert alert-danger">Error al cargar el formulario de registro. Verifique la ruta del archivo `formulario_registro_paciente.php`.</p>');
      // Mostrar el modal de registro (o dejar que el aviso se muestre)
      $('#modalRegistroRapido').modal('show');
    });
  }

  // AÑADIDO: Nueva función para cargar el formulario de registro en el modal
  function cargarFormularioRegistroMenor(tipo, cedula) {
    const modalBody = $('#contenidoModalRegistro');
    const $avisoModalConsulta = $('#avisoModalConsulta'); // Referencia al modal de error

    // 1. Limpiamos y mostramos un loader
    modalBody.html('<div class="text-center" style="padding: 30px;"><i class="fa fa-spinner fa-spin fa-3x fa-fw"></i> Cargando Formulario de Registro...</div>');

    // 2. Cargamos el contenido del archivo externo (Asegúrate de que esta ruta sea correcta)
    $.get('modales/consultas/modal_pacientes_menores_agregar_consulta.php', {
      tipo_cedula: tipo,
      cedula: cedula
    }, function(html_content) {
      modalBody.html(html_content);

      // --- CAMBIO CLAVE: CERRAR EL MODAL DE AVISO/ERROR ANTES DE MOSTRAR EL DE REGISTRO ---
      closeCustomModal($avisoModalConsulta);

      // 3. Mostrar el modal de registro
      $('#modalRegistroRapido').modal('show');
    }).fail(function() {
      modalBody.html('<p class="alert alert-danger">Error al cargar el formulario de registro. Verifique la ruta del archivo `formulario_registro_paciente.php`.</p>');
      // Mostrar el modal de registro (o dejar que el aviso se muestre)
      $('#modalRegistroRapido').modal('show');
    });
  }

  async function verificarCedulaYObtenerDatosConsulta(tipo, cedula) {
    // Resetear campos antes de la verificación
    nombrePacienteInput.value = '';
    fechaNacimientoPacienteInput.value = '';
    edadPacienteInput.value = '';
    $('#display_nombre').text('N/A');
    $('#display_cedula').text('N/A');
    $('#display_fecha_nacimiento').text('N/A');

    $(nombrePacienteInput).removeClass('input-error');
    $(cedulaPacienteInput).removeClass('input-error');

    if (cedula.length < 1 || tipo === "") {
      return false;
    }

    return new Promise((resolve, reject) => {
      $.ajax({
        // Asume la existencia de 'get/get_paciente.php' para la info demográfica
        url: 'get/get_paciente.php',
        method: 'POST',
        dataType: 'json',
        data: {
          tipo_cedula: tipo,
          cedula: cedula
        },
        success: function(response) {
          if (response.existe) {
            // PACIENTE ENCONTRADO
            nombrePacienteInput.value = response.nombre_completo;
            fechaNacimientoPacienteInput.value = response.fecha_nacimiento;
            calcularEdadPaciente();;

            $('#display_nombre').text(response.nombre_completo);
            $('#display_cedula').text(tipo + '-' + cedula);
            $('#display_fecha_nacimiento').text(new Date(response.fecha_nacimiento).toLocaleDateString('es-VE'));

            cargarHistorialCompleto(cedula);
            gestionarBotonSiguiente(true);

            resolve(true);
          } else {
            // PACIENTE NO ENCONTRADO
            $(cedulaPacienteInput).addClass('input-error').attr('placeholder', '¡Paciente no encontrado!');
            nombrePacienteInput.value = '¡Paciente no encontrado!';

            actualizarAlertas([], []);
            mostrarUltimasConsultas([]);
            gestionarPerinatales(100);

            //const avisoText = '⚠️ Error: El paciente con la Cédula/Documento ' + tipo + '-' + cedula + ' NO se encuentra registrado.' + '<br>' + 'Por favor, regístrelo en la ventana que se abrira a continuacion.';

            // AÑADIDO: Llamar a la función para cargar el modal de registro
            determinarTipoRegistro(tipo, cedula);

            //mostrarAviso(avisoText, () => {

            //});

            gestionarBotonSiguiente(false);
            s
            resolve(false);
          }
        },
        error: function(xhr, status, error) {
          console.error("Error de conexión/servidor al verificar cédula.", status, error);
          $(cedulaPacienteInput).addClass('input-error');
          nombrePacienteInput.value = 'Error de conexión.';
          actualizarAlertas([], []);
          mostrarUltimasConsultas([]);
          gestionarPerinatales(100);

          mostrarAviso('⚠️ Error de Conexión: No se pudo comunicar con el servidor para verificar la cédula. Intente de nuevo más tarde.');
          gestionarBotonSiguiente(false);
          resolve(false);
        }
      });
    });
  }

  let medicamentosSeleccionados = []; // Array temporal para guardar los objetos {id, id_medicamento_base, nombre, dosis, unidad, indicaciones}
  let listaMedicamentosBase = []; // Array para guardar todos los medicamentos con sus IDs

  function cargarMedicamentosBaseAjax() {
    $.ajax({
      url: 'get/get_medicamentos_base.php',
      method: 'GET',
      dataType: 'json',
      success: function(response) {
        const $select = $('#selectMedicamentos');
        $select.empty().append('<option value="">--- Seleccione un Medicamento ---</option>');

        if (response.success && response.data.length > 0) {
          response.data.forEach(med => {
            // El texto que verá el médico
            let textoMostrar = med.nombre_medicamento + " (" + (med.componentes || 'N/A') + " )" + " [" + (med.nombre_presentacion || 'N/A') + " ]";

            // El value debe ser Id_descripcion (el ID de la tabla descripcion_medicamento)
            $select.append(`
            <option value="${med.Id_descripcion}" 
                    data-nombre="${med.nombre_medicamento}" 
                    data-via="${med.via_aplicacion || 'No definida'}"
                    data-contenido="${med.contenido_neto || 'No definida'}"
                    data-presentacion="${med.nombre_presentacion || 'No definida'}"
                    data-componentes="${med.componentes || 'Sin componentes'}">
                ${textoMostrar}
            </option>`);
          });

          // Si usas Select2, esto es vital para refrescarlo
          if ($.fn.select2 && $select.hasClass('select2-hidden-accessible')) {
            $select.select2('destroy').select2();
          }
        } else {
          console.warn("No se cargaron datos:", response.error);
          $select.append('<option value="">No hay medicamentos disponibles</option>');
        }
      },
      error: function(xhr) {
        console.error("Error en la petición AJAX:", xhr.responseText);
      }
    });
  }

  // Lógica del Tooltip al cambiar el select
  $('#selectMedicamentos').on('change', function() {
    let option = $(this).find('option:selected');

    if (option.val() !== "") {
      // Obtenemos los datos de los data-attributes que llenamos en el paso anterior
      let via = option.data('via') || 'No especificada';
      let pres = option.data('presentacion') || 'No definida';
      let comp = option.data('componentes') || 'Sin detalles';

      let info = `<strong>Vía:</strong> ${via}<br>
                    <strong>Pres:</strong> ${pres}<br>
                    <strong>Principio Activo:</strong> ${comp}`;

      // Actualizamos el tooltip del botón
      $('#btnInfoMedicamento')
        .attr('data-original-title', info)
        .tooltip('hide') // Cerramos el anterior
        .attr('title', info)
        .tooltip('fixTitle'); // Refrescamos
    }
  });

  $('#btnAnadirMedicamento').on('click', function() {
    const $select = $('#selectMedicamentos');
    const selected = $select.find('option:selected');

    if ($select.val() === "") {
      mostrarAviso("Por favor seleccione un medicamento");
      return;
    }

    // CAPTURA SEGURA DE DATOS
    const idMedicamento = selected.val(); // El value del option (Debe ser el ID)
    const nombreMedicamento = selected.data('nombre'); // Del atributo data-nombre

    // Validación para no guardar nombres en lugar de IDs
    if (isNaN(idMedicamento)) {
      console.error("Error: Se está capturando un nombre en lugar de un ID:", idMedicamento);
    }

    // Evitar duplicados
    if (medicamentosSeleccionados.find(m => m.id === idMedicamento)) {
      mostrarAviso("Este medicamento ya ha sido añadido");
      return;
    }

    const medObj = {
      id: idMedicamento,
      nombre: nombreMedicamento,
      detalles: selected.data('via') + " - " + selected.data('contenido'),
      tipo: selected.data('presentacion')
    };

    medicamentosSeleccionados.push(medObj);
    renderizarListaMedicamentos();
    $select.val('').trigger('change');
    actualizarTooltipMedicamentos();
  });

  function renderizarListaMedicamentos() {
    const contenedor = $('#contenedorMedicamentosSeleccionados');
    contenedor.empty();

    if (medicamentosSeleccionados.length === 0) {
      contenedor.append('<p class="text-muted text-center">No hay medicamentos añadidos aún.</p>');
      return;
    }

    medicamentosSeleccionados.forEach((med, index) => {
      contenedor.append(`
            <div class="alert alert-info alert-dismissible" style="margin-bottom: 5px; padding: 8px;">
                <button type="button" class="close" onclick="eliminarMed(${index})">&times;</button>
                <strong>${med.nombre}</strong> - <small>${med.detalles} (${med.tipo})</small>
                <input type="hidden" name="medicamentos[]" value="${med.id}">
            </div>
        `);
    });
  }

  function eliminarMed(index) {
    medicamentosSeleccionados.splice(index, 1);
    renderizarListaMedicamentos();
    actualizarTooltipMedicamentos();
  }

  let idsSeleccionados = [];
  let nombresSeleccionados = [];

  function confirmarSeleccion() {
    const $select = $('#selectMedicamentos');
    const selected = $select.find('option:selected');

    const id = $select.val();
    const nombre = selected.data('nombre');
    const tipo = selected.data('nombre_tipo') || 'N/A';

    if (id && !idsSeleccionados.includes(id)) {
      // 1. Guardar datos en los arreglos
      idsSeleccionados.push(id);
      nombresSeleccionados.push(nombre);

      // 2. Actualizar los inputs del formulario
      $('#medicamento_full_data').val(nombresSeleccionados.join(', '));

      // 3. ACTUALIZAR EL TOOLTIP CORRECTAMENTE
      const nuevoTexto = "Agregados: " + nombresSeleccionados.length + " medicamento(s)";

      $('#medicamento_agg')
        .attr('title', nuevoTexto) // Cambia el título base
        .attr('data-original-title', nuevoTexto) // Cambia el título de Bootstrap
        .tooltip('fixTitle') // Fuerza a Bootstrap a reconocer el cambio
        .tooltip('show'); // Muestra el mensaje al médico

      // 4. Limpieza y cierre
      $('#modalSeleccionMedicamentos').modal('hide');
      $select.val('').trigger('change');
    }
  }

  function actualizarTooltipMedicamentos() {
    var boton = $('#medicamento_agg');

    // Revisamos si el arreglo global tiene elementos
    if (medicamentosSeleccionados.length > 0) {
      // Extraemos solo los nombres de los medicamentos usando map
      var nombres = medicamentosSeleccionados.map(function(med) {
        return med.nombre;
      });

      var nuevoTexto = "Medicamentos: " + nombres.join(", ");

      // Actualizamos y refrescamos el tooltip
      boton.attr('data-original-title', nuevoTexto).tooltip('fixTitle');
    } else {
      // Si el arreglo está vacío, mostramos el mensaje por defecto
      boton.attr('data-original-title', "Ningún medicamento agregado").tooltip('fixTitle');
    }
  }

  $('#hora_cita').on('change', function() {
    const horaVal = $(this).val(); // Retorna "HH:mm"
    if (!horaVal) return;

    const [horas, minutos] = horaVal.split(':').map(Number);
    const tiempoDecimal = horas + (minutos / 60);

    const horaInicio = 9.0; // 09:00 AM
    const horaFin = 12.0; // 01:00 PM

    if (tiempoDecimal < horaInicio || tiempoDecimal > horaFin) {
      mostrarAviso("🛑 Horario no disponible. <br>Nuestro horario de atención es de <b>9:00 AM a 12:00 PM</b>.");
      $(this).val(""); // Limpiamos el campo inmediatamente
      $(this).closest('.form-class').addClass('input-error');
    } else {
      $(this).closest('.form-class').removeClass('input-error');
    }
  });

  $('#fecha_cita').on('change', function() {
    const fechaSeleccionada = new Date(this.value + 'T00:00:00');
    const diaSemana = fechaSeleccionada.getUTCDay(); // 0 = Domingo, 6 = Sábado

    if (diaSemana === 0 || diaSemana === 6) {
      mostrarAviso("🛑 No se pueden agendar citas los fines de semana (Sábados o Domingos).");
      this.value = ""; // Limpia el campo
      $(this).closest('.form-class').addClass('input-error');
    } else {
      $(this).closest('.form-class').removeClass('input-error');
    }
  });

  // =========================================================
  // FUNCIONES PARA ABRIR LOS MODALES
  // =========================================================
  function abrirModalAlergia() {
    const cedula = $('#cedula_paciente').val();
    if (!cedula || cedula === "") {
      alert("Por favor, cargue o ingrese la cédula de un paciente primero.");
      return;
    }
    // Reiniciamos el select y mostramos
    $('#select_alergia').val('');
    $('#modalAlergiaRapida').modal('show');
  }

  function abrirModalPatologia() {
    const cedula = $('#cedula_paciente').val();
    if (!cedula || cedula === "") {
      alert("Por favor, cargue o ingrese la cédula de un paciente primero.");
      return;
    }
    // Reiniciamos el select y mostramos
    $('#select_patologia').val('');
    $('#modalPatologiaRapida').modal('show');
  }

  // =========================================================
  // FUNCIONES PARA GUARDAR (AJAX)
  // =========================================================

  function guardarAlergia() {
    const idItem = $('#select_alergia').val();
    const fecha = $('#fecha_alergia').val();
    const cedula = $('#cedula_paciente').val();
    const idHistorial = $('#id_historial_global').val();

    if (!idItem || !fecha || !cedula) {
      alert("Por favor complete todos los datos de la alergia.");
      return;
    }

    $.ajax({
      url: '../../cfg/ajax/actualizar_patologias_alergias.php',
      type: 'POST',
      data: {
        accion: 'vincular_alergia',
        id_item: idItem,
        fecha: fecha,
        cedula: cedula,
        id_historial_global: idHistorial
      },
      success: function(response) {
        if (response.status === "success" || response.status === "ok") {
          $('#modalAlergiaRapida').modal('hide');
          // Aquí podrías recargar la lista de alergias visualmente
          alert("Alergia guardada correctamente.");
        } else {
          alert("Error: " + response.message);
        }
      },
      error: function() {
        alert("Error de conexión al guardar la alergia.");
      }
    });
  }

  function guardarPatologia() {
    const idItem = $('#select_patologia').val();
    const fecha = $('#fecha_patologia').val();
    const cedula = $('#cedula_paciente').val();
    const idHistorial = $('#id_historial_global').val();

    if (!idItem || !fecha || !cedula) {
      alert("Por favor complete todos los datos de la patología.");
      return;
    }

    $.ajax({
      url: '../../cfg/ajax/actualizar_patologias_alergias.php',
      type: 'POST',
      data: {
        accion: 'vincular_patologia',
        id_item: idItem,
        fecha: fecha,
        cedula: cedula,
        id_historial_global: idHistorial
      },
      success: function(response) {
        if (response.status === "success" || response.status === "ok") {
          $('#modalPatologiaRapida').modal('hide');
          alert("Patología guardada correctamente.");
        } else {
          alert("Error: " + response.message);
        }
      },
      error: function() {
        alert("Error de conexión al guardar la patología.");
      }
    });
  }

  // =====================================================================
  // INICIALIZACIÓN Y EVENTOS
  // =====================================================================

  $(document).ready(function() {
    // Inicializar Tooltip de Bootstrap
    $('#btnInfoMedicamento').tooltip({
      html: true
    });

    // Cuando cambie el medicamento seleccionado
    $('#selectMedicamentos').on('change', function() {
      var selected = $(this).find('option:selected');

      if (selected.val() != "") {
        // Extraer datos de los atributos data-
        var dosis = selected.data('dosis');
        var via = selected.data('via');
        var presentacion = selected.data('presentacion');
        var tipo = selected.data('tipo');

        // Crear el contenido del Tooltip
        var info = "<strong>Detalles Técnicos:</strong><br>" +
          "Dosis: " + dosis + "<br>" +
          "Tipo: " + tipo + "<br>" +
          "Vía: " + via + "<br>" +
          "Pres: " + presentacion;

        // Actualizar el botón
        $('#btnInfoMedicamento')
          .attr('data-original-title', info)
          .tooltip('show'); // Mostrarlo brevemente para que el médico lo vea
      } else {
        $('#btnInfoMedicamento').attr('data-original-title', "Seleccione para ver detalles");
      }
    });
  });

  $(document).ready(function() {
    // 🛑 ATENCIÓN: Se omite la carga de medicamentos guardados, ya que es un formulario de AGREGAR.

    // EVENTO: Al abrir el modal, cargamos la lista de medicamentos y renderizamos la lista (que estará vacía al inicio).
    $('#modalSeleccionMedicamentos').on('show.bs.modal', function(e) {
      cargarMedicamentosBaseAjax();
    });

    // EVENTO: Aplicar la selección al formulario principal
    $('#aplicarSeleccionMedicamentos').on('click', function() {
      // 1. Validar si hay algo seleccionado (Opcional)
      if (medicamentosSeleccionados.length === 0) {
        $('#medicamento').val(''); // Vaciar si no hay nada
        $('#medicamento_agg').attr('data-original-title', 'Ningún medicamento agregado');
        $('#inputs_medicamentos_ocultos').empty();
        $('#modalSeleccionMedicamentos').modal('hide');
        return;
      }

      // 2. Crear el string de nombres para el Tooltip y guardarlo en el input de validación
      var id = medicamentosSeleccionados.map(function(m) {
        return m.id;
      }).join(', ');
      $('#medicamento').val(id); // Esto llena el input oculto de nombres

      var nombre = medicamentosSeleccionados.map(function(m) {
        return m.nombre;
      }).join(', ');

      var nuevoTexto = "Medicamentos: " + nombres;

      // 3. Actualizar el Tooltip del botón
      $('#medicamento_agg').attr('data-original-title', nuevoTexto).tooltip('fixTitle');

    });

    // 5. Cerrar el modal
    $('#modalSeleccionMedicamentos').modal('hide');

    console.log("Medicamentos listos para enviarse:", medicamentosSeleccionados);
  });

  // =====================================================================
  // INICIALIZACIÓN Y EVENT LISTENERS
  // =====================================================================

  $(document).ready(function() {
    // LISTENERS DE NAVEGACIÓN ENTRE PESTAÑAS (Añadida la lógica de validación)
    $('.next-table').on('click', async function() {
      const tabActualSelector = $(this).data('table-actual');
      let tabSiguienteName = $(this).data('table-siguiente');

      if (tabActualSelector === 'consulta') {
        const cedula = $('#cedula_paciente').val();
        const tipo = $('#tipo_cedula_paciente').val();

        // Solo verificamos si hay una cédula válida para evitar llamadas innecesarias
        if (cedula.trim().length >= 1) {
          // CRUCIAL: Usamos await para esperar que la llamada AJAX de verificación termine.
          // Esto actualiza el campo #nombre_paciente con el nombre o con "¡Paciente no encontrado!".
          await verificarCedulaYObtenerDatosConsulta(tipo, cedula);
        }
      }
      // [FIN DE LA CORRECCIÓN CLAVE]

      // 2. Ahora sí, validamos los campos, incluyendo el #nombre_paciente actualizado.
      const isValid = await validarCamposConsulta(tabActualSelector);

      if (isValid) {
        if (tabActualSelector === 'indicacion_tratamiento') {
          // Si es la última pestaña, no hay siguiente, solo se abre el modal de guardar
          // 2. Validar Fin de Semana (NUEVO)
          const fechaVal = $('#fecha_cita').val();
          const d = new Date(fechaVal + 'T00:00:00');
          const dia = d.getUTCDay();

          if (dia === 0 || dia === 6) {
            mostrarAviso("🛑 La fecha seleccionada es un fin de semana. Por favor, elija un día laborable.");
            $('#fecha_cita').addClass('input-error');
            return;
          }

          const horaSeleccionada = $('#hora_cita').val();
          if (!validarHorarioLaboral(horaSeleccionada)) {
            mostrarAviso("🛑 El horario de atención es únicamente de 9:00 AM a 1:00 PM.");
            $('#hora_cita').addClass('input-error');
            return;

            if ($('#medicamento').val().trim() === '') {
              isValid = false;
              $('#medicamento').addClass('input-error');
              mostrarAviso('Debe seleccionar al menos un medicamento para el tratamiento.');
            }
            if ($('#indicaciones').val().trim() === '') {
              isValid = false;
              $('#indicaciones').addClass('input-error');
              mostrarAviso('Debe asignarle un tratamiento al paciente.');
            }
          } else {
            $('#modalGuardarConsulta').modal('show');
          }
          return;
        }
        // CORRECCIÓN: Limpiar el error visual de los inputs/textarea
        $(`#${tabActualSelector}`).find('.input-error').removeClass('input-error');
        goToTab(tabSiguienteName);
      }
    });

    $('.prev-table').on('click', function() {
      const tabAnteriorId = $(this).data('table-anterior');
      const tabActualName = $('.tab-panel.active').attr('id');
      // CORRECCIÓN: Limpiar el error visual de los inputs/textarea
      $(`#${tabActualName}`).find('.input-error').removeClass('input-error');
      goToTab(tabAnteriorId);
    });

    // EVENTO PARA EL BOTÓN FINAL DE GUARDAR (Valida la última pestaña y envía)
    $('#btn_finalizar_consulta').on('click', async function() {
      const isValid = await validarCamposConsulta('indicacion_tratamiento');
      if (isValid) {
        // Si la última pestaña es válida, envía el formulario completo
        $('#modalGuardarConsulta').modal('show');
        $('#enviar').on('click', function() {
          // Desactivamos el botón para evitar doble envío
          $(this).prop('disabled', true).text('Procesando...');

          // Enviamos el formulario finalmente
          $('#formularioConsulta').submit();
        });
      } else {
        // Si falla, cierra el modal de confirmación y enfoca la pestaña para corregir.
        closeCustomModal($('#modalGuardarConsulta'));
        goToTab('indicacion_tratamiento');
      }
    });

    // Campos de texto para limpiar números
    const campos = [document.getElementById("motivo_consulta"), document.getElementById("diagnostico_text"),
      document.getElementById("perinatales"), document.getElementById("familiares"),
      document.getElementById("sexualidad_reproductivos"), document.getElementById("estilo_vida"),
      document.getElementById("examenes_solicitados"), document.getElementById("entregado_a")

    ];
    campos.forEach(campo => {
      if (campo) {
        campo.addEventListener("keydown", bloquearNumeros);
        campo.addEventListener("input", limpiarNumeros);
      }
    });

    // LISTENERS PARA CÉDULA
    tipoCedulaPacienteSelect.addEventListener('change', function() {
      const tipo = this.value;
      cedulaPacienteInput.value = ''; // Limpiar para evitar conflicto de formatos
      filtrarCedulaConsulta();
    });

    cedulaPacienteInput.addEventListener('input', function() {
      const tipo = tipoCedulaPacienteSelect.value;
      
      filtrarCedulaConsulta(); // Aplicar el formateo en tiempo real

      if (this.value.length < 7) {
        $(cedulaPacienteInput).removeClass('input-error');
        nombrePacienteInput.value = '';
        fechaNacimientoPacienteInput.value = '';
        edadPacienteInput.value = '';
        $('#display_nombre, #display_cedula, #display_fecha_nacimiento').text('N/A');
        actualizarAlertas([], []);
        mostrarUltimasConsultas([]);
        gestionarPerinatales(100);
      }
    });

    cedulaPacienteInput.addEventListener('blur', function() {
      const tipo = tipoCedulaPacienteSelect.value;
      const valor = this.value;
      
      if (tipo === 'RP') {
          const regexRP = /^[0-9]{8}-[0-9]{1}$/;
          if (!regexRP.test(valor)) {
              mostrarAviso("🛑 Formato incorrecto. El documento REP debe tener el formato <b>12345678-1</b>.");
              $(this).addClass('input-error');
              return;
          }
      }

      if (valor.length >= 1) {
        verificarCedulaYObtenerDatosConsulta(tipo, valor);
      }
    });

    // Llamadas de inicialización
    filtrarCedulaConsulta();
    aplicarRestriccionesConsulta();

    // Cargar historial si la cédula ya viene en el input (desde PHP inicial)
    if ($('#cedula_paciente').val() !== "") {
      calcularEdadPaciente();
      verificarCedulaYObtenerDatosConsulta(tipoCedulaPacienteSelect.value, $('#cedula_paciente').val()); // Mejor usar la función completa
    } else {
      gestionarPerinatales(100);
    }
  });
</script>
<script>
  function gestionarBotonSiguiente(habilitar) {
    const nextButton = $('#consulta .next-table');
    if (habilitar) {
      // Habilitar: Si el paciente existe
      nextButton.prop('disabled', false);
      nextButton.removeClass('btn-default').addClass('btn-primary');
      nextButton.find('span').text('Siguiente');
      // Título al habilitar
      nextButton.attr('title', 'Continuar a la siguiente pestaña');

      validarCamposConsulta();

    } else {
      // Deshabilitar: Si el paciente NO existe o la cédula es inválida
      nextButton.prop('disabled', true);
      nextButton.removeClass('btn-primary').addClass('btn-default');
      nextButton.find('span').text('Paciente Requerido');

      // 💡 ESTO MUESTRA UN TOOLTIP AL PASAR EL MOUSE POR ENCIMA
      nextButton.attr('title', 'Debe existir un paciente registrado o una cédula válida para continuar.');
    }
  }
  // AÑADIDO: Función para determinar el flujo de registro cuando la cédula es ambigua (V o E)
  function determinarTipoRegistro(tipo, cedula) {
    const $modal = $('#modalConfirmacionMenor');
    const $texto = $('#textoConfirmacionMenor');

    $texto.html(`El paciente con Documento ${tipo}-${cedula} NO existe en la base de datos.`);

    // Mostramos el modal
    $modal.modal('show');

    // Forzar que el botón "Cerrar" (el de la X o el de cancelar) funcione
    $modal.find('[data-dismiss="modal"]').off('click').on('click', function() {
      $modal.modal('hide');
    });

    const cleanupAndClose = () => {
      $('#btnConfirmarRegistroMenor').off('click');
      $('#btnConfirmarRegistroAdulto').off('click');
      $modal.modal('hide');
    };

    $('#btnConfirmarRegistroMenor').on('click', function() {
      cleanupAndClose();
      cargarFormularioRegistroMenor(tipo, cedula);
    });

    $('#btnConfirmarRegistroAdulto').on('click', function() {
      cleanupAndClose();
      cargarFormularioRegistro();
      // Aquí llamas a tu función de registro estándar
    });
  }
</script>

</html>