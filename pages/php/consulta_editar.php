<?php
// ==========================================================
// 1. INCLUIR CONEXIÓN A LA BASE DE DATOS
// ==========================================================
// *** Asegúrate de que esta ruta sea correcta para tu entorno.
include("../../cfg/conexion.php");

// Consultar Alergias existentes
$query_alergias = $conexion->query("SELECT Id_alergias_conocidas, nombre_alergia FROM alergias_conocidas WHERE estatus = 1 ORDER BY nombre_alergia ASC");

// Consultar Patologías existentes
$query_patologias = $conexion->query("SELECT Id_patologia, nombre_patologia, codigo_cie FROM patologias WHERE estatus = 1 ORDER BY nombre_patologia ASC");

// ==========================================================
// 2. LÓGICA DE CARGA PARA EDICIÓN
// ==========================================================
$id_consulta = $_GET['Id'] ?? null;
$datos_consulta = [];
$datos_paciente = [];
$diagnosticos_cargados = [];
$medicamentos_cargados = [];
$error_busqueda = null;
$id_historial = null; // Necesario para la URL de retorno o para manejar el historial

if (!$id_consulta || !is_numeric($id_consulta)) {
  $error_busqueda = "Error: Se requiere un ID de consulta válido para editar.";
} elseif (isset($conexion)) {
  try {
    $safe_id = $conexion->real_escape_string($id_consulta);

    // A. OBTENER DATOS PRINCIPALES DE LA CONSULTA Y DEL PACIENTE
    $sql_consulta = "
            SELECT 
                c.*,
                p.nombre, p.apellido, p.cedula, p.fecha_nacimiento, 
                h.Id_historial
            FROM consulta c
            JOIN historial_medico h ON c.Id_historial = h.Id_historial
            JOIN persona p ON h.Id_persona = p.Id
            WHERE c.Id_consulta = '$safe_id'
        ";
    $result_consulta = $conexion->query($sql_consulta);
    $datos_consulta = $result_consulta ? $result_consulta->fetch_assoc() : [];
    if ($result_consulta) $result_consulta->free();

    if ($datos_consulta) {
      $id_historial = $datos_consulta['Id_historial'];
      $datos_paciente = [
        'nombre' => $datos_consulta['nombre'],
        'apellido' => $datos_consulta['apellido'],
        'cedula' => $datos_consulta['cedula'],
        'fecha_nacimiento' => $datos_consulta['fecha_nacimiento']
      ];

      // Determinar tipo de cédula (simulación basada en 'V'/'E' para adultos o 'N' para menores)
      // Se asume que en persona hay un campo o se puede inferir. Aquí lo simulo.
      $tipo_cedula = (strpos($datos_consulta['cedula'], '-') === false) ? 'V' : 'E';
      // Esto necesita ajustarse a la lógica real de tu base de datos si la cédula no tiene prefijo.

    } else {
      $error_busqueda = "No se encontró la consulta con ID: {$id_consulta}.";
    }

    $consulta = $datos_consulta;

    // ==========================================================
    // D. OBTENER ÚLTIMA NOTA ADICIONAL DEL HISTORIAL (SOLUCIÓN)
    // ==========================================================
    $nota_cargada = '';
    if ($id_historial) {
      $safe_historial_id = $conexion->real_escape_string($id_historial);

      // Buscamos la observación más reciente para ese historial. 
      // Esto asegura que si se añadió una nota en una consulta anterior, se muestre.
      $sql_notas = "
                SELECT observacion 
                FROM observaciones_historial_medico 
                WHERE Id_historial_medico = '$safe_historial_id'
                ORDER BY fecha DESC, Id DESC 
                LIMIT 1
            ";
      $result_notas = $conexion->query($sql_notas);

      if ($result_notas && $row_nota = $result_notas->fetch_assoc()) {
        // Guardamos la observación en una nueva variable para el formulario
        $nota_cargada = $row_nota['observacion'];
      }
    }

    $sql_medicamentos = "
        SELECT 
            dm.Id as id, 
            m.nombre_medicamento as nombre, 
            dm.contenido_neto, 
            dm.via_aplicacion,
            dpm.cantidad_unidad_medida, 
            um.unidad,
            ps.nombre_presentacion
        FROM prescripcion_medicamentos p
        INNER JOIN descripcion_medicamento dm ON p.Id_descripcion_medicamento = dm.Id
        INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
        LEFT JOIN detalle_principio_medicamento dpm ON m.Id_medicamento = dpm.id_medicamento
        LEFT JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida
        LEFT JOIN presentacion ps ON dm.Id_presentacion = ps.Id_presentacion
        WHERE p.Id_consulta = '$safe_id'";

    $result_medicamentos = $conexion->query($sql_medicamentos);
    $medicamentos_para_json = [];
    $medicamentos_cargados = [];

    while ($row = $result_medicamentos->fetch_assoc()) {
      // Estructura idéntica a la de consulta_agregar.php para que el JS no falle
      $medicamentos_para_json[] = [
        'id' => $row['id'],
        'nombre' => $row['nombre'],
        'contenido' => $row['contenido_neto'],
        'via_aplicacion' => $row['via_aplicacion'],
        'cantidad_unidad_medida' => $row['cantidad_unidad_medida'],
        'unidad' => $row['unidad'],
        'nombre_presentacion' => $row['nombre_presentacion']
      ];

      // Texto que aparecerá en el input visible al cargar
      $medicamentos_cargados[] = $row['nombre'];
    }

    // Variables listas para los inputs del formulario
    $medicamentos_json_data = htmlspecialchars(json_encode($medicamentos_para_json), ENT_QUOTES, 'UTF-8');
    $medicamentos_cargados_text = implode(', ', $medicamentos_cargados);
  } catch (\mysqli_sql_exception $e) {
    $error_busqueda = "Error al conectar o buscar los datos: " . $e->getMessage();
  }
}

// ----------------------------------------------------------
// Lógica para mostrar mensajes de error si la consulta falló
// ----------------------------------------------------------
if ($error_busqueda) {
  // Si hay un error crítico, se detiene la ejecución y se muestra el mensaje.
?>
  <div style='background:#f8d7da; padding:20px; border:1px solid #f5c6cb; border-radius:5px;'>
    <h2 style='color:#721c24'>❌ Error al cargar la consulta</h2>
    <p><strong>Mensaje:</strong> <?php echo htmlspecialchars($error_busqueda); ?></p>
    <br><button onclick='window.history.back()'>Volver</button>
  </div>
<?php
  exit;
}

// Si la carga fue exitosa, preparamos el formulario.
$consulta = $datos_consulta; // Alias para usar la variable $consulta como en el resto del código

?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Consultas | Editar</title>
  <?php
  // Asegúrate de que este archivo exista
  include('includes/headerNav2.php');
  ?>
</head>
<style>
  /* Estilos copiados de consulta_nueva.php */
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
  #modalGuardarConsulta,
  #modalPatologiaRapida,
  #modalAlergiaRapida,
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
  .input-error {
    border: 2px solid crimson !important;
    box-shadow: 0 0 5px crimson;
  }

  #avisoModal,
  #modalPatologias,
  #modalAlergias,
  #modalGuardar {
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

  .modal.in {
    display: block;
  }

  /* MODIFICACIÓN SOLICITADA: Bloquear click en las pestañas */
  .nav-tabs>li>a {
    pointer-events: none;
    cursor: default;
  }

  /* Estilos para pestañas bloqueadas visualmente */
  .nav-tabs li.disabled-tab a {
    color: #b2b2b2 !important;
  }

  .modal-header-danger {
    background-color: #dc3545;
    color: white;
  }

  /* NUEVO: Estilo para etiquetas de medicamento en el modal */
  .medicamento-tag {
    display: inline-block;
    padding: 5px 10px;
    margin-right: 5px;
    margin-bottom: 5px;
    background-color: #3c8dbc;
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
  }
</style>

<body>

  <div class="content-wrapper">
    <section class="content-header">
      <h1>Editar Consulta Médica</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-users"></i>Pacientes</a></li>
        <li class="active"><a href="#"><i class="fa fa-pencil"></i>Editar Consultas</a></li>
      </ol>
    </section>

    <section class="content">

      <div class="row">
        <div class="col-xs-12">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active" data-tab-name="consulta"><a href="#consulta" data-toggle="tab">Información del Paciente</a></li>

              <li data-tab-name="historial"><a href="#historial" data-toggle="tab">Historial Clínico</a></li>

              <li data-tab-name="antecedentes"><a href="#antecedentes" data-toggle="tab">Antecedentes del Paciente</a></li>
              <li data-tab-name="diagnostico"><a href="#diagnostico" data-toggle="tab">Diagnóstico</a></li>
              <li data-tab-name="indicacion_tratamiento"><a href="#indicacion_tratamiento" data-toggle="tab">Indicación Final y Tratamiento</a></li>
            </ul>

            <div class="tab-content">

              <div class="tab-pane active" id="consulta">
                <section id="new" style="min-height: 480px;">
                  <form action="../../cfg/editar/editar_consulta.php" id="formularioConsulta" class="form-group" method="POST">
                    <input type="hidden" name="Id_Consulta" value="<?php echo htmlspecialchars($id_consulta); ?>">
                    <input type="hidden" name="Id_historial" id="id_historial_global" value="<?php echo htmlspecialchars($id_historial); ?>">

                    <label class="control-label"></label>
                    <div class="col-sm-1 pull-left" style="margin-top: 30px;">
                      <select name="tipo_cedula_paciente" id="tipo_cedula_paciente" class="form-control" style="width: 60px;" readonly disabled>
                        <option value="PN" <?php echo ($tipo_cedula ?? '') == 'PN' ? 'selected' : ''; ?>>PN-</option>
                        <option value="V" <?php echo ($tipo_cedula ?? '') == 'V' ? 'selected' : ''; ?>>V-</option>
                        <option value="E" <?php echo ($tipo_cedula ?? '') == 'E' ? 'selected' : ''; ?>>E-</option>
                      </select>
                    </div>
                    <div class="col-sm-3">
                      <p>Cedula/Documento (*)</p>
                      <input type="text" class="form-control" name="cedula_paciente" id="cedula_paciente" placeholder="N° de Cedula" value="<?php echo htmlspecialchars($datos_paciente['cedula'] ?? ''); ?>" required readonly>
                    </div>
                    <div class="col-sm-4">
                      <p>Nombre del Paciente (*)</p>
                      <input type="text" id="nombre_paciente" class="form-control" name="nombre_paciente" readonly placeholder="Nombre del Paciente" value="<?php echo htmlspecialchars($datos_paciente['nombre'] ?? '') . ' ' . htmlspecialchars($datos_paciente['apellido'] ?? ''); ?>" require>
                    </div>
                    <label class="control-label"></label>
                    <div class="col-sm-3">
                      <p>Fecha de nacimiento (*):</p>
                      <input type="date" class="form-control pull-right" id="fecha_nacimiento_paciente" name="fecha_nacimiento_paciente" onchange="calcularEdadPaciente()" max="" required readonly value="<?php echo htmlspecialchars($datos_paciente['fecha_nacimiento'] ?? ''); ?>">
                    </div>
                    <div class="col-sm-1" style="margin-top: 0px; margin-left:-5px;">
                      <p>Edad:</p>
                      <input type="text" class="form-control pull-right" id="edad_paciente" name="edad_paciente" readonly>
                    </div>
                    <br><br><br><br>
                    <div class="col-sm-4">
                      <p>Medico (*):</p>
                      <select id="medico" name="medico" class="form-control" required>
                        <option value="">--- Seleccione el medico ---</option>
                        <?php
                        // 1. Cargar Medicos
                        $sql_medico = "SELECT p.id, p.nombre, p.apellido 
                                                               FROM persona p
                                                               JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona
                                                               WHERE dpr.Id_rol = 4 ORDER BY p.nombre ASC";
                        if (isset($conexion)) {
                          $resultado_medico = $conexion->query($sql_medico);

                          if ($resultado_medico && $resultado_medico->num_rows > 0) {
                            while ($row_medico = $resultado_medico->fetch_assoc()) {
                              // Selección automática del médico guardado
                              $selected = ($row_medico['id'] == ($consulta['Id_medico'] ?? 0)) ? 'selected' : '';
                              echo '<option value="' . $row_medico['id'] . '" ' . $selected . '>' . htmlspecialchars($row_medico['nombre'] . ' ' . $row_medico['apellido']) . '</option>';
                            }
                          }
                        }
                        ?>
                      </select>
                    </div>
                    <div class="col-sm-4">
                      <p>Fecha de consulta (*):</p>
                      <input type="date" name="fecha_consulta" id="fecha_consulta" class="form-control" value="<?php echo htmlspecialchars($consulta['fecha_consulta'] ?? ''); ?>" max="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-sm-4">
                      <p>Peso (kg)</p>
                      <input type="text" step="0.1" id="peso" class="form-control" name="peso" placeholder="Ej: 70.5" value="<?php echo htmlspecialchars($consulta['peso'] ?? ''); ?>">
                    </div>
                    <br><br><br><br>
                    <div class="col-sm-4">
                      <p>Talla (cm)</p>
                      <input type="text" id="talla" class="form-control" name="talla" placeholder="Ej: 172" value="<?php echo htmlspecialchars($consulta['talla'] ?? ''); ?>">
                    </div>
                    <div class="col-sm-4">
                      <p>Temperatura (°C)</p>
                      <input type="text" step="0.1" id="temperatura" class="form-control" name="temperatura" placeholder="Ej: 36.7" value="<?php echo htmlspecialchars($consulta['temperatura'] ?? ''); ?>">
                    </div>
                    <div class="col-sm-4">
                      <p>Tensión (mmHg)</p>
                      <input type="text" id="tension" class="form-control" name="tension" placeholder="Ej: 120/80" value="<?php echo htmlspecialchars($consulta['tension'] ?? ''); ?>">
                    </div>
                    <br><br><br><br>
                    <div class="col-sm-4">
                      <p>Frecuencia Cardíaca (lpm)</p>
                      <input type="text" id="frecuencia_cardiaca" class="form-control" name="frecuencia_cardiaca" placeholder="Ej: 72" value="<?php echo htmlspecialchars($consulta['frecuencia_cardiaca'] ?? ''); ?>">
                    </div>
                    <div class="col-sm-4">
                      <p>Saturación de Oxígeno (%)</p>
                      <input type="text" id="saturacion" class="form-control" name="saturacion" placeholder="Ej: 98" value="<?php echo htmlspecialchars($consulta['saturacion'] ?? ''); ?>">
                    </div>
                    <div class="col-sm-4">
                      <p>Frecuencia Respiratoria (rpm)</p>
                      <input type="text" id="frecuencia_respiratoria" class="form-control" name="frecuencia_respiratoria" placeholder="Ej: 16" value="<?php echo htmlspecialchars($consulta['frecuencia_respiratoria'] ?? ''); ?>">
                    </div>
                    <br><br><br><br>
                    <div class="col-sm-12">
                      <p>Motivo de la consulta (*)</p>
                      <textarea id="motivo_consulta" name="motivo_consulta" class="form-control" rows="3" placeholder="Motivo principal o queja del paciente" required><?php echo htmlspecialchars($consulta['motivo_consulta'] ?? ''); ?></textarea>
                    </div>
                    <div style="float:right; margin-top:2%;">
                      <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#modalConfirmarRegreso">Regresar</button>
                      <button type="button" class="btn btn-primary next-tab" id="consulta_button" data-tab-actual="consulta" data-tab-siguiente="historial">Siguiente</button>
                    </div>
                </section>
              </div>

              <div role="tabpanel" class="tab-pane" id="historial" style="margin-bottom:5%;">
                <br>
                <h4>Últimas 2 Consultas Registradas (para vista rápida)</h4>
                <div id="historial-consultas-container">
                  <div class="alert alert-info">
                    Historial cargado por JavaScript.
                  </div>
                </div>
                <button type="button" class="btn btn-info" onclick="abrirTimelineCompleta()">
                  <i class="fa fa-clock-o"></i> Ver Todo el Historial
                </button>
                <div style="float:right; margin-top:0%;">
                  <button type="button" class="btn btn-secondary prev-tab" data-tab-anterior="consulta">Atrás</button>
                  <button type="button" class="btn btn-primary next-tab" data-tab-actual="historial" data-tab-siguiente="antecedentes">Siguiente</button>
                </div>
              </div>

              <div class="tab-pane" id="antecedentes">
                <section id="new" style="margin-bottom:18%;">
                  <div class="col-sm-6">
                    <p id="label_perinatales">Perinatales (Solo menores de edad):</p>
                    <textarea id="perinatales" name="perinatales" class="form-control" rows="3" placeholder="Información de antecedentes perinatales"><?php echo htmlspecialchars($consulta['perinatales'] ?? ''); ?></textarea>
                  </div>
                  <div class="col-sm-6">
                    <p>Familiares:</p>
                    <textarea id="familiares" name="familiares" class="form-control" rows="3" placeholder="Motivo principal o queja del paciente" required><?php echo htmlspecialchars($consulta['familiares'] ?? ''); ?></textarea>
                  </div>
                  <br><br><br><br><br><br>
                  <div class="col-sm-6">
                    <p>Personales (Sexualidad y Reproductivos):</p>
                    <textarea id="sexualidad_reproductivos" name="sexualidad_reproductivos" class="form-control" rows="3" placeholder="Motivo principal o queja del paciente" required><?php echo htmlspecialchars($consulta['sexualidad_reproductivos'] ?? ''); ?></textarea>
                  </div>
                  <div class="col-sm-6">
                    <p>Personales (Estilo y Vida):</p>
                    <textarea id="estilo_vida" name="estilo_vida" class="form-control" rows="3" placeholder="Motivo principal o queja del paciente" required><?php echo htmlspecialchars($consulta['estilo_vida'] ?? ''); ?></textarea>
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
                    <button type="button" class="btn btn-secondary prev-tab" data-tab-anterior="historial">Atrás</button>
                    <button type="button" class="btn btn-primary next-tab" data-tab-actual="antecedentes" data-tab-siguiente="diagnostico">Siguiente</button>
                  </div>
                </section>
              </div>

              <div class="tab-pane" id="diagnostico">
                <section id="new" style="height: 480px;">

                  <div class="col-md-12">
                    <div class="form-group">
                      <label for="lectura_examenes"><b>Lectura de Resultados (Exámenes):</b></label>
                      <textarea class="form-control" name="lectura_examenes" value="" id="lectura_examenes" rows="3" placeholder="Ej: Glucemia 110, Colesterol alto..."><?php echo htmlspecialchars($consulta['lectura_examenes'] ?? ''); ?></textarea>
                    </div>
                  </div>

                  <div class="col-sm-12">
                    <p>Diagnóstico (*)</p>
                    <textarea name="diagnostico_text" id="diagnostico_text" class="form-control" rows="5" placeholder="Puede incluir código CIE-10" required><?php echo htmlspecialchars($consulta['diagnostico'] ?? ''); ?></textarea>
                  </div>

                  <br><br><br><br>

                  <div class="col-sm-6" style="margin-top: 20px;">
                    <p>Evolución / Resultado (*)</p>
                    <select name="estado_paciente" class="form-control" required>
                      <option value="">Seleccione Estado</option>
                      <option value="Primera Consulta" <?php echo ($consulta['estado_paciente'] == 'Primera Consulta' ? 'selected' : ''); ?>>Primera Consulta (Inicio del Caso)</option>
                      <option value="Sano" <?php echo ($consulta['estado_paciente'] == 'Sano' ? 'selected' : ''); ?>>Mejoría Total (Sano)</option>
                      <option value="En tratamiento" <?php echo ($consulta['estado_paciente'] == 'En tratamiento' ? 'selected' : ''); ?>>Mejoría Parcial (En tratamiento)</option>
                      <option value="Sin mejoria" <?php echo ($consulta['estado_paciente'] == 'Sin mejoriao' ? 'selected' : ''); ?>>Sin Mejoría (Requiere Re-entrada)</option>
                      <option value="Empeoro" <?php echo ($consulta['estado_paciente'] == 'Empeoro' ? 'selected' : ''); ?>>Empeoramiento</option>
                    </select>
                  </div>

                  <div class="col-sm-6" style="margin-top: 20px;">
                    <p>¿Reportó Reacción Adversa?</p>
                    <select name="reaccion_adversa" id="reaccion_adversa" class="form-control">
                      <option value="No" <?php echo ($consulta['reaccion_adversa'] == 'No' ? 'selected' : ''); ?>>No</option>
                      <option value="Si" <?php echo ($consulta['reaccion_adversa'] == 'Si' ? 'selected' : ''); ?>>Sí, presentó reacción</option>
                    </select>
                  </div>

                  <div class="col-sm-6" style="margin-top: 10px;">
                    <p>Detalle de Evolución / Resultados (*)</p>
                    <textarea name="evolucion_resultado" id="evolucion_resultado" class="form-control" rows="1" value="" placeholder="Descripcion de la evalucion del paciente" required><?php echo htmlspecialchars($consulta['evolucion_resultado'] ?? ''); ?></textarea>
                  </div>

                  <div class="col-sm-6" id="detalle_reaccion" style="margin-top: 20px; display:none;">
                    <textarea name="detalle_reaccion" class="form-control" rows="2" placeholder="Describa la reacción (ej: Alergia al componente X)"><?php echo htmlspecialchars($consulta['detalle_reaccion'] ?? ''); ?></textarea>
                  </div>

                  <div style="float:right; margin-top:3%;">
                    <button type="button" class="btn btn-secondary prev-tab" data-tab-anterior="antecedentes">Atrás</button>
                    <button type="button" class="btn btn-primary next-tab" data-tab-actual="diagnostico" data-tab-siguiente="indicacion_tratamiento">Siguiente</button>
                  </div>
                </section>
              </div>

              <div class="tab-pane" id="indicacion_tratamiento">
                <section id="new" style="height: 420px;">

                  <label class="control-label"></label>
                  <div class="col-md-6">
                    <p>Nuevos Exámenes Solicitados:</p>
                    <input type="text" class="form-control" name="examenes_solicitados" value="<?php echo htmlspecialchars($consulta['examenes_solicitados'] ?? ''); ?>" id="examenes_solicitados" placeholder="Ej: Hematología completa, Eco Renal...">
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="control-label">Medicamentos:</label>

                      <button type="button" class="btn btn-info btn-md btn-block has-tooltip" id="medicamento_agg" data-toggle="modal" data-target="#modalSeleccionMedicamentos" data-placement="top" title="<?php echo !empty($medicamentos_cargados_text) ? htmlspecialchars($medicamentos_cargados_text) : 'Ningún medicamento agregado'; ?>">
                        <i>Asignar Medicamento</i>
                      </button>

                      <input type="hidden" id="medicamento_full_data" name="medicamento_full_data" value='<?php echo $medicamentos_json_data; ?>'>
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

                  <div class="col-sm-6">
                    <p>Indicaciones (*)</p>
                    <textarea name="indicaciones" id="indicaciones" class="form-control" rows="8" required><?php echo htmlspecialchars($consulta['tratamiento_indicado'] ?? ''); ?></textarea>
                  </div>

                  <div class="col-sm-6">
                    <p>Notas y observaciones adicionales:</p>
                    <textarea name="notas_adicionales" id="notas_adicionales" class="form-control" rows="8"><?php echo htmlspecialchars($nota_cargada ?? ''); ?></textarea>
                  </div>
                  <div style="float:right; margin-top:2%;">
                    <button type="button" class="btn btn-secondary prev-tab" data-tab-anterior="diagnostico">Atrás</button>
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
                    <?php

                    // Reiniciar el puntero del resultado por si se usó antes
                    if (isset($query_medicamentos)) {
                      $query_medicamentos->data_seek(0);
                      while ($row = $query_medicamentos->fetch_assoc()) :
                    ?>
                        <option value="<?php echo $row['Id']; ?>" data-nombre="<?php echo htmlspecialchars($row['nombre_medicamento']); ?>" data-via="<?php echo htmlspecialchars($row['via_aplicacion']); ?>" data-contenido="<?php echo htmlspecialchars($row['contenido_neto']); ?>" data-presentacion="<?php echo htmlspecialchars($row['nombre_presentacion']); ?>" data-unidad="<?php echo htmlspecialchars($row['unidad']); ?>" data-cantidad="<?php echo htmlspecialchars($row['cantidad_unidad_medida']); ?>">
                          <?php echo $row['nombre_medicamento'] . " (" . $row['cantidad_unidad_medida'] . " " . $row['unidad'] . ")"; ?>
                        </option>
                    <?php
                      endwhile;
                    }
                    ?>
                  </select>
                  <span class="input-group-btn">
                    <button class="btn btn-info" type="button" id="btnInfoMedicamento" data-toggle="tooltip" title="Detalles" style="height: 34px;">
                      <i><img src="../../recursos/imagenes/iconos/info.png" style="width:15px; height:15px;"></i>
                    </button>
                  </span>
                </div>
              </div>
            </div>

            <div class="col-xs-3">
              <div class="form-group">
                <label>&nbsp;</label>
                <button type="button" class="btn btn-primary btn-block" id="btnAnadirMedicamento">
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

  <div class="modal" id="avisoModal" tabindex="-1" role="dialog" aria-labelledby="avisoModalLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header modal-header-danger">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="avisoModalLabel"><i class="fa fa-exclamation-triangle"></i> Aviso</h4>
        </div>
        <div class="modal-body">
          <p id="avisoTexto"></p>
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
          <h4 class="modal-title" id="modalGuardarConsultaLabel"><i class="fa fa-save"></i> Confirmación de Actualización</h4>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea actualizar la información de la consulta?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Regresar</button>
          <button type="button" class="btn btn-success" id="enviar">Guardar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalConfirmarRegreso" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarRegresoLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header modal-header-danger">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="modalConfirmarRegresoLabel"><i class="fa fa-warning"></i> Advertencia</h4>
        </div>
        <div class="modal-body">
          <p>Al hacer clic en "Abandonar Formulario", perderá todos los datos no guardados. ¿Desea continuar?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <a href="javascript:history.back()" class="btn btn-danger">Abandonar Formulario</a>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalAlertaSalud" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
      <div class="modal-content" style="border: 5px solid #dc3545;">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> ¡ATENCIÓN: PERFIL CRÍTICO DEL PACIENTE!</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body text-center">
          <div id="contenedorAlertas">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-block" data-dismiss="modal">He leído y comprendo los riesgos</button>
        </div>
      </div>
    </div>
  </div>

  <?php
  include('includes/footer.php');
  ?>

  <?php
  // Se recomienda cerrar la conexión principal al final del script
  if (isset($conexion)) $conexion->close();
  ?>

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
      const $avisoModal = $('#avisoModal').modal('show');
      $('#avisoTexto').html(texto);

      $avisoModal.off('hidden.bs.modal');

      if (onHiddenCallBack && typeof onHiddenCallBack === 'function') {
        $avisoModal.one('hidden.bs.modal', function() {
          onHiddenCallBack();
        });
      }
      $avisoModal.modal('show');
    }

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
    $('#avisoModal .close, #avisoModal .btn-second').on('click', function() {
      closeCustomModal($('#avisoModal'));
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

    $('#modalTimelineCompleta .close, #modalTimelineCompleta .btn-second').on('click', function() {
      closeCustomModal($('#modalTimelineCompleta'));
    });

    document.addEventListener("DOMContentLoaded", function() {
      // Seleccionamos el select y el contenedor del detalle
      const selectReaccion = document.getElementById('reaccion_adversa'); // Asegúrate que este sea el ID
      const divDetalle = document.getElementById('detalle_reaccion');

      function evaluarReaccion() {
        if (selectReaccion.value === 'Si') {
          divDetalle.style.display = 'block';
        } else {
          divDetalle.style.display = 'none';
        }
      }

      // 1. Lo ejecutamos al cargar para que revise el valor que trae de la BD
      evaluarReaccion();

      // 2. Lo mantenemos para cuando el usuario cambie la opción manualmente
      selectReaccion.addEventListener('change', evaluarReaccion);
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

    $('#cedula_paciente').on('change', function() {
      var cedula = $(this).val();

      if (cedula != "") {
        $.ajax({
          url: 'get/get_alertas_paciente.php',
          type: 'POST',
          data: {
            cedula: cedula
          },
          success: function(response) {
            try {
              var datos = JSON.parse(response);
              if (datos.tiene_alertas || datos.es_menor) {
                $('#contenedorAlertas').html(datos.html_contenido);
                $('#modalAlertaSalud').modal('show');

                if (datos.es_menor) {
                  // 1. Ponemos el nombre del representante en el input de texto
                  $('#entregado_a').val(datos.nombre_representante);

                  // 2. Aquí está el truco para el Select:
                  // Le decimos al select que su valor ahora es el texto que vino de la BD
                  if (datos.parentesco) {
                    $('#parentesco_representante').val(datos.parentesco);
                  }

                  // 3. Opcional: Si usas una librería como Select2 o quieres resaltar el cambio:
                  $('#parentesco_representante').addClass('is-valid');
                } else {
                  // Si es adulto, limpiamos los campos
                  $('#entregado_a').val('');
                  $('#parentesco_representante').val('');
                }
              }
            } catch (e) {
              console.error("Error al leer la respuesta del servidor:", response);
            }
          },
          error: function(xhr, status, error) {
            console.error("Error en la llamada AJAX:", error);
          }
        });
      }
    });

    // Supongamos que tu input de cédula tiene el id "cedula_paciente"
    $(document).ready(function() {
      // 1. Obtener la cédula que ya viene cargada en el input desde PHP
      var cedulaPrecargada = $('#cedula_paciente').val();

      // 2. Si hay una cédula, ejecutar la función de carga directa
      if (cedulaPrecargada !== "") {
        cargarDatosRepresentanteSilencioso(cedulaPrecargada);
      }
    });

    // Función que hace el trabajo sucio sin abrir modales
    function cargarDatosRepresentanteSilencioso(cedula) {

      if (!cedula) return;

      $.ajax({
        url: 'get/get_alertas_paciente.php',
        type: 'POST',
        data: {
          cedula: cedula
        },
        dataType: 'json',

        success: function(response) {

          // Forzamos boolean real
          response.es_menor = (response.es_menor === true || response.es_menor === 1 || response.es_menor === '1');

          // 🧑 MAYOR DE EDAD
          if (!response.es_menor) {

            $('#entregado_a').val(response.nombre_paciente);
            $('#parentesco_representante')
              .val('')
              .prop('disabled', true);

          }
          // 👶 MENOR DE EDAD
          else {

            $('#parentesco_representante').prop('disabled', false);

            if (response.nombre_representante) {
              $('#entregado_a').val(response.nombre_representante);
            } else {
              $('#entregado_a').val(response.nombre_paciente);
            }

            $('#parentesco_representante').val(response.parentesco ?? '');
          }

          // Alertas (si existen)
          if (response.tiene_alertas) {
            $('#contenedorAlertas').html(response.html_contenido);
          }
        },

        error: function() {
          console.log('Error al cargar datos del paciente');
        }
      });
    }

    // =========================================================
    // FUNCIONES PARA ABRIR LOS MODALES
    // =========================================================
    function abrirModalAlergia() {
      const cedula = $('#cedula_paciente').val();
      if (!cedula || cedula === "") {
        mostrarAviso("Por favor, cargue o ingrese la cédula de un paciente primero.");
        return;
      }
      // Reiniciamos el select y mostramos
      $('#select_alergia').val('');
      $('#modalAlergiaRapida').modal('show');
    }

    function abrirModalPatologia() {
      const cedula = $('#cedula_paciente').val();
      if (!cedula || cedula === "") {
        mostrarAviso("Por favor, cargue o ingrese la cédula de un paciente primero.");
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
        mostrarAviso("Por favor complete todos los datos de la alergia.");
        return;
      }

      console.log(idItem);
      console.log(fecha);
      console.log(cedula);
      console.log(idHistorial);

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
            mostrarAviso("Alergia guardada correctamente.");
          } else {
            mostrarAviso("Error: " + response.message);
          }
        },
        error: function() {
          mostrarAviso("Error de conexión al guardar la alergia.");
        }
      });
    }

    function guardarPatologia() {
      const idItem = $('#select_patologia').val();
      const fecha = $('#fecha_patologia').val();
      const cedula = $('#cedula_paciente').val();
      const idHistorial = $('#Id_historial').val();

      if (!idItem || !fecha || !cedula) {
        mostrarAviso("Por favor complete todos los datos de la patología.");
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
          Id_historial: idHistorial
        },
        success: function(response) {
          if (response.status === "success" || response.status === "ok") {
            $('#modalPatologiaRapida').modal('hide');
            mostrarAviso("Patología guardada correctamente.");
          } else {
            mostrarAviso("Error: " + response.message);
          }
        },
        error: function() {
          mostrarAviso("Error de conexión al guardar la patología.");
        }
      });
    }

    function abrirTimelineCompleta() {
      // 1. Obtener la cédula (ajustar el ID según consulta_agregar o consulta_editar)
      var cedula = $('#cedula_paciente').val();

      if (!cedula) {
        mostrarAviso("Por favor, ingrese o busque un paciente primero.");
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

    /**
     * Navega entre pestañas y gestiona el bloqueo/desbloqueo visual
     * @param {string} tabName El nombre de la pestaña destino (e.g., 'historial')
     */
    function goToTab(tabName) {
      // Oculta todos los .tab-pane
      $('.tab-pane').removeClass('active');
      // Desactiva todos los .nav-tabs li
      $('.nav-tabs li').removeClass('active');

      // Muestra la pestaña de destino
      $(`#${tabName}`).addClass('active');
      // Activa el botón de la pestaña de destino y lo desbloquea
      const $targetTab = $(`.nav-tabs li[data-tab-name="${tabName}"]`);
      $targetTab.addClass('active').removeClass('disabled-tab');

      // Muestra la pestaña usando Bootstrap
      $targetTab.find('a[data-toggle="tab"]').tab('show');

      // Asegúrate de que las pestañas *anteriores* a la actual sigan desbloqueadas,
      // y las *siguientes* estén bloqueadas (disabled-tab).
      let tabs = ['consulta', 'historial', 'antecedentes', 'diagnostico', 'indicacion_tratamiento'];
      let currentIndex = tabs.indexOf(tabName);

      tabs.forEach((name, index) => {
        const $li = $(`.nav-tabs li[data-tab-name="${name}"]`);
        if (index <= currentIndex) {
          $li.removeClass('disabled-tab');
        } else {
          $li.addClass('disabled-tab');
        }
        // CORRECCIÓN: Limpiar el error visual de la pestaña al navegar
        $li.find('a[data-toggle="tab"]').removeClass('input-error');
      });
    }

    /**
     * Valida los campos requeridos y formatos del tab actual.
     * @param {string} tabName El nombre de la pestaña a validar.
     * @returns {Promise<boolean>} Resuelve a true si es válido, false si no.
     */
    async function validarCampos(tabName) {
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

        if ($('#indicaciones').val().trim() === '') {
          isValid = false;
          $('#indicaciones').addClass('input-error');
          mostrarAviso('Debe asignarle un tratamiento al paciente.');
        }
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

    /**
     * Aplica restricciones de entrada a campos específicos (solo números, etc.)
     */
    function aplicarRestricciones() {
      // Permite solo números y punto decimal
      $('#peso, #temperatura').on('input', function() {
        this.value = this.value.replace(/[^0-9.]/g, '');
      });

      // Permite solo números
      $('#talla, #frecuencia_cardiaca, #saturacion, #frecuencia_respiratoria, #inputDosisCantidad, #cedula_paciente').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
      });

      // Permite números y el formato de tensión 120/80
      $('#tension').on('input', function() {
        this.value = this.value.replace(/[^0-9/]/g, '');
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

      gestionarPerinatales(edadPaciente);
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

    function setDateLimits() {
      const today = new Date();
      const maxDate = today.toISOString().split('T')[0];

      document.getElementById('fecha_consulta').setAttribute('max', maxDate);

      const tomorrow = new Date(today);
      tomorrow.setDate(tomorrow.getDate() + 1);
      const minDate = tomorrow.toISOString().split('T')[0];
      document.getElementById('proxima_cita').setAttribute('min', minDate);
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
      // Inicializar los campos de texto
      const camposAntecedentes = ['#perinatales', '#familiares', '#sexualidad_reproductivos', '#estilo_vida'];
      camposAntecedentes.forEach(id => {
        // Limpiamos los campos y resetamos el estado de datos previos
        $(id).val('').data('has-data', false).prop('readonly', false);
      });

      $('#historial-consultas-container').html('<div class="alert alert-info">Cargando historial clínico...</div>');
      actualizarAlertas(['Cargando...'], ['Cargando...']);

      $.ajax({
        url: 'get/get_historial_ajax.php?cedula=' + cedula,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
          if (response.success) {
            const data = response.data;

            actualizarAlertas(data.alergias, data.patologias);

            const ant = data.antecedentes;


            // Rellenar campos y bloquear si tienen datos
            const antec = {
              '#perinatales': ant.perinatales,
              '#familiares': ant.familiares,
              '#sexualidad_reproductivos': ant.sexualidad_reproductivos,
              '#estilo_vida': ant.estilo_vida
            };

            for (const id in antec) {
              const val = antec[id] || '';
              const $campo = $(id);
              $campo.val(val);

              // Bloqueo si el valor existe y no está vacío (excluyendo el placeholder 'N/A' que se gestiona aparte)
              if (val.trim() !== '' && val.trim().toLowerCase() !== 'n/a o no aplica') {
                $campo.data('has-data', true).prop('readonly', false);
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

    function ajustarLongitudCedula(tipo) {
      let maxLength = 15;
      if (tipo === 'PN') {
        maxLength = 20;
      } else if (tipo === 'E' || tipo === 'V') {
        maxLength = 8;
      }
      cedulaPacienteInput.setAttribute('maxlength', maxLength);
      if (cedulaPacienteInput.value.length > maxLength) {
        cedulaPacienteInput.value = cedulaPacienteInput.value.substring(0, maxLength);
      }
    }

    // AÑADIDO: Nueva función para cargar el formulario de registro en el modal
    function cargarFormularioRegistro(tipo, cedula) {
      const modalBody = $('#contenidoModalRegistro');
      const $avisoModal = $('#avisoModal'); // Referencia al modal de error

      // 1. Limpiamos y mostramos un loader
      modalBody.html('<div class="text-center" style="padding: 30px;"><i class="fa fa-spinner fa-spin fa-3x fa-fw"></i> Cargando Formulario de Registro...</div>');

      // 2. Cargamos el contenido del archivo externo (Asegúrate de que esta ruta sea correcta)
      $.get('modales/consultas/modal_pacientes_agregar_consulta.php', {
        tipo_cedula: tipo,
        cedula: cedula
      }, function(html_content) {
        modalBody.html(html_content);

        // --- CAMBIO CLAVE: CERRAR EL MODAL DE AVISO/ERROR ANTES DE MOSTRAR EL DE REGISTRO ---
        closeCustomModal($avisoModal);

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
      const $avisoModal = $('#avisoModal'); // Referencia al modal de error

      // 1. Limpiamos y mostramos un loader
      modalBody.html('<div class="text-center" style="padding: 30px;"><i class="fa fa-spinner fa-spin fa-3x fa-fw"></i> Cargando Formulario de Registro...</div>');

      // 2. Cargamos el contenido del archivo externo (Asegúrate de que esta ruta sea correcta)
      $.get('modales/consultas/modal_pacientes_menores_agregar_consulta.php', {
        tipo_cedula: tipo,
        cedula: cedula
      }, function(html_content) {
        modalBody.html(html_content);

        // --- CAMBIO CLAVE: CERRAR EL MODAL DE AVISO/ERROR ANTES DE MOSTRAR EL DE REGISTRO ---
        closeCustomModal($avisoModal);

        // 3. Mostrar el modal de registro
        $('#modalRegistroRapido').modal('show');
      }).fail(function() {
        modalBody.html('<p class="alert alert-danger">Error al cargar el formulario de registro. Verifique la ruta del archivo `formulario_registro_paciente.php`.</p>');
        // Mostrar el modal de registro (o dejar que el aviso se muestre)
        $('#modalRegistroRapido').modal('show');
      });
    }

    async function verificarCedulaYObtenerDatos(tipo, cedula) {
      // Resetear campos antes de la verificación
      nombrePacienteInput.value = '';
      fechaNacimientoPacienteInput.value = '';
      edadPacienteInput.value = '';
      $('#display_nombre').text('N/A');
      $('#display_cedula').text('N/A');
      $('#display_fecha_nacimiento').text('N/A');

      $(nombrePacienteInput).removeClass('input-error');
      $(cedulaPacienteInput).removeClass('input-error');

      if (cedula.length < 7 || tipo === "") {
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
              calcularEdadPaciente();

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

    // =====================================================================
    // GESTIÓN DE MEDICAMENTOS (AJAX, Modal y Formulario)
    // =====================================================================

    // 1. Declaración global del arreglo (IMPORTANTE)
    let medicamentosSeleccionados = [];

    $(document).ready(function() {
      // =====================================================================
      // CARGA INICIAL DE DATOS (ESPECÍFICO PARA EDITAR)
      // =====================================================================

      $('#modalSeleccionMedicamentos').on('show.bs.modal', function() {
        cargarMedicamentosBaseAjax();
        // También renderizamos la lista de lo que ya está seleccionado
        renderizarListaMedicamentos();
      });

      function cargarMedicamentosBaseAjax() {
        $.ajax({
          url: 'get/get_medicamentos_base.php', // Asegúrate de que la ruta sea correcta
          type: 'GET',
          dataType: 'json',
          success: function(response) {
            const $select = $('#selectMedicamentos');
            $select.empty().append('<option value="">--- Seleccione un medicamento ---</option>');

            // Según tu PHP, la respuesta viene en response.data y usa success (booleano)
            if (response.success && response.data) {
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

              // Refrescar Select2 si lo usas
              if ($select.hasClass('select2-hidden-accessible')) {
                $select.trigger('change.select2');
              }
            }
          },
          error: function(xhr, status, error) {
            console.error("Error al cargar el catálogo:", error);
          }
        });
      }

      // Leemos el JSON que pusimos en el input oculto desde PHP
      const datosIniciales = $('#medicamento_full_data').val();

      if (datosIniciales && datosIniciales !== "" && datosIniciales !== "[]") {
        try {
          // Convertimos el JSON de la base de datos al arreglo de trabajo
          medicamentosSeleccionados = JSON.parse(datosIniciales);

          // Refrescamos la visualización inicial
          renderizarListaMedicamentos();
          actualizarTooltipMedicamentos();

          console.log("Medicamentos cargados para edición:", medicamentosSeleccionados);
        } catch (e) {
          console.error("Error al parsear medicamentos iniciales:", e);
        }
      }

      // Inicializar Tooltips
      $('.has-tooltip, #btnInfoMedicamento, #medicamento_agg').tooltip({
        html: true
      });

      // =====================================================================
      // EVENTOS DEL MODAL
      // =====================================================================

      // Mostrar detalles al cambiar selección en el select
      $('#selectMedicamentos').on('change', function() {
        let option = $(this).find('option:selected');
        if (option.val() !== "") {
          // Se usan los nombres exactos definidos en data- del HTML
          let via = option.data('via') || 'N/A';
          let contenido = option.data('contenido') || 'N/A';
          let presentacion = option.data('presentacion') || 'N/A';

          let info = `<strong>Vía:</strong> ${via}<br> <strong>Contenido:</strong> ${contenido}<br> <strong>Presentación:</strong> ${presentacion}`;

          $('#btnInfoMedicamento')
            .attr('data-original-title', info)
            .tooltip('hide')
            .attr('title', info)
            .tooltip('fixTitle');
        }
      });

      // Añadir nuevo medicamento al listado temporal
      $('#btnAnadirMedicamento').on('click', function() {
        const $select = $('#selectMedicamentos');
        const selected = $select.find('option:selected');
        const idMedicamento = selected.val();

        if (idMedicamento === "") {
          mostrarAviso("Por favor seleccione un medicamento");
          return;
        }

        const yaExiste = medicamentosSeleccionados.some(m => m.id === idMedicamento);
        if (yaExiste) {
          mostrarAviso("Este medicamento ya ha sido añadido");

          // CORRECCIÓN: Limpia el select y el tooltip después del error
          $select.val('').trigger('change');
          $('#btnInfoMedicamento').attr('data-original-title', 'Seleccione un medicamento').tooltip('fixTitle');
          return;
        }

        const nuevoMed = {
          id: idMedicamento,
          nombre: selected.data('nombre'),
          contenido: selected.data('contenido'), // Debe coincidir con data-contenido
          nombre_presentacion: selected.data('presentacion') // Debe coincidir con data-presentacion
        };

        medicamentosSeleccionados.push(nuevoMed);
        renderizarListaMedicamentos();
        actualizarTooltipMedicamentos();

        // Limpia el select y el tooltip tras agregar con éxito
        $select.val('').trigger('change');
        $('#btnInfoMedicamento').attr('data-original-title', 'Seleccione un medicamento').tooltip('fixTitle');
      });

      // Aplicar la selección definitiva al formulario
      $('#aplicarSeleccionMedicamentos').on('click', function() {
        if (medicamentosSeleccionados.length === 0) {
          $('#medicamento').val('');
          $('#medicamento_full_data').val('[]');
        } else {
          // Lista de nombres para el input visible
          const nombres = medicamentosSeleccionados.map(m => m.nombre).join(', ');
          $('#medicamento').val(nombres);

          // Guardamos el JSON actualizado en el hidden para el POST de PHP
          $('#medicamento_full_data').val(JSON.stringify(medicamentosSeleccionados));
        }

        actualizarTooltipMedicamentos();
        $('#modalSeleccionMedicamentos').modal('hide');
      });
    });

    // =====================================================================
    // FUNCIONES DE APOYO
    // =====================================================================

    function renderizarListaMedicamentos() {
      const contenedor = $('#contenedorMedicamentosSeleccionados');
      contenedor.empty();

      if (medicamentosSeleccionados.length === 0) {
        contenedor.append('<p class="text-muted text-center" id="textoVacio">No hay medicamentos añadidos aún.</p>');
        return;
      }

      medicamentosSeleccionados.forEach((med, index) => {
        // Usamos .contenido y .nombre_presentacion que definimos en el paso anterior
        let textoMostrar = `${med.nombre} - ${med.contenido} (${med.nombre_presentacion})`;
        contenedor.append(`

        <div class="alert alert-info alert-dismissible" style="margin-bottom: 5px; padding: 8px;">
                <button type="button" class="close" onclick="eliminarMed(${index})">&times;</button>
                <strong>${med.nombre}</strong> - <small>${med.contenido} (${med.nombre_presentacion})</small>
                <input type="hidden" name="medicamentos_ids[]" value="${med.id}">
            </div>
      `);
      });
    }

    function eliminarMed(index) {
      medicamentosSeleccionados.splice(index, 1);
      renderizarListaMedicamentos();
    }

    function actualizarTooltipMedicamentos() {
      const boton = $('#medicamento_agg');
      if (medicamentosSeleccionados.length > 0) {
        const nombres = medicamentosSeleccionados.map(m => m.nombre).join(", ");
        const texto = "Medicamentos: " + nombres;
        boton.attr('data-original-title', texto).attr('title', texto).tooltip('fixTitle');
      } else {
        boton.attr('data-original-title', "Ningún medicamento agregado").attr('title', "Ningún medicamento agregado").tooltip('fixTitle');
      }
    }

    // =====================================================================
    // INICIALIZACIÓN Y EVENT LISTENERS
    // =====================================================================

    $(document).ready(function() {
      calcularEdadPaciente();
      // LISTENERS DE NAVEGACIÓN ENTRE PESTAÑAS (Añadida la lógica de validación)
      $('.next-tab').on('click', async function() {
        const tabActualSelector = $(this).data('tab-actual');
        let tabSiguienteName = $(this).data('tab-siguiente');

        if (tabActualSelector === 'consulta') {
          const cedula = $('#cedula_paciente').val();
          const tipo = $('#tipo_cedula_paciente').val();

          // Solo verificamos si hay una cédula válida para evitar llamadas innecesarias
          if (cedula.trim().length >= 7) {
            // CRUCIAL: Usamos await para esperar que la llamada AJAX de verificación termine.
            // Esto actualiza el campo #nombre_paciente con el nombre o con "¡Paciente no encontrado!".
            await verificarCedulaYObtenerDatos(tipo, cedula);
          }
        }
        // [FIN DE LA CORRECCIÓN CLAVE]

        // 2. Ahora sí, validamos los campos, incluyendo el #nombre_paciente actualizado.
        const isValid = await validarCampos(tabActualSelector);

        if (isValid) {
          if (tabActualSelector === 'indicacion_tratamiento') {
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
            return;
          }
          // CORRECCIÓN: Limpiar el error visual de los inputs/textarea
          $(`#${tabActualSelector}`).find('.input-error').removeClass('input-error');
          goToTab(tabSiguienteName);
        }
      });

      $('.prev-tab').on('click', function() {
        const tabAnteriorId = $(this).data('tab-anterior');
        const tabActualName = $('.tab-pane.active').attr('id');
        // CORRECCIÓN: Limpiar el error visual de los inputs/textarea
        $(`#${tabActualName}`).find('.input-error').removeClass('input-error');
        goToTab(tabAnteriorId);
      });

      // EVENTO PARA EL BOTÓN FINAL DE GUARDAR (Valida la última pestaña y envía)
      $('#btn_finalizar_consulta').on('click', async function() {
        const isValid = await validarCampos('indicacion_tratamiento');
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
        document.getElementById("sexualidad_reproductivos"), document.getElementById("estilo_vida")
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
        ajustarLongitudCedula(tipo);
        if (cedulaPacienteInput.value.length >= 7) {
          verificarCedulaYObtenerDatos(tipo, cedulaPacienteInput.value);
        }
      });

      cedulaPacienteInput.addEventListener('input', function() {
        const tipo = tipoCedulaPacienteSelect.value;

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

        if (this.value.length === 8 && (tipo === 'V' || tipo === 'E')) {
          verificarCedulaYObtenerDatos(tipo, this.value);
        }
      });

      cedulaPacienteInput.addEventListener('blur', function() {
        const tipo = tipoCedulaPacienteSelect.value;
        if (this.value.length >= 7) {
          verificarCedulaYObtenerDatos(tipo, this.value);
        }
      });

      // Llamadas de inicialización
      ajustarLongitudCedula(tipoCedulaPacienteSelect.value);
      aplicarRestricciones();
      setDateLimits();

      // Cargar historial si la cédula ya viene en el input (desde PHP inicial)
      if ($('#cedula_paciente').val() !== "") {
        calcularEdadPaciente();
        verificarCedulaYObtenerDatos(tipoCedulaPacienteSelect.value, $('#cedula_paciente').val()); // Mejor usar la función completa
      } else {
        gestionarPerinatales(100);
      }
    });
  </script>
  <script>
    function gestionarBotonSiguiente(habilitar) {
      const nextButton = $('#consulta .next-tab');
      if (habilitar) {
        // Habilitar: Si el paciente existe
        nextButton.prop('disabled', false);
        nextButton.removeClass('btn-default').addClass('btn-primary');
        nextButton.find('span').text('Siguiente');
        // Título al habilitar
        nextButton.attr('title', 'Continuar a la siguiente pestaña');
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
      const $btnMenor = $('#btnConfirmarRegistroMenor');
      const $btnAdulto = $('#btnConfirmarRegistroAdulto');

      // 1. Establecer el texto de la alerta
      $texto.html(`El paciente con Documento ${tipo}-${cedula} NO existe en la base de datos.`);

      // 2. Función para cerrar el modal y limpiar los eventos (evitar múltiples llamadas)
      const cleanupAndClose = () => {
        $btnMenor.off('click');
        $btnAdulto.off('click');
        $modal.modal('hide');
      };

      // 3. Bind listeners para los botones

      // Si elige Registrar como MENOR
      $btnMenor.on('click', function() {
        cleanupAndClose();
        // Llamar a tu función para abrir el modal de registro de menor (con representante)
        cargarFormularioRegistroMenor(tipo, cedula);
      });

      // Si elige Registrar ESTÁNDAR/ADULTO
      $btnAdulto.on('click', function() {
        cleanupAndClose();
        // Llamar a tu función para abrir el modal de registro rápido/estándar
        cargarFormularioRegistro(tipo, cedula);
      });

      // 4. Mostrar el modal
      $modal.modal('show'); // Esto permite cerrarlo con la tecla ESC
    }
  </script>

</body>

</html>