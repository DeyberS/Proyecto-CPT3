<?php
include('includes/headerNav2.php');
verificarAcceso('Gestionar Programa');
include("../../cfg/conexion.php");
?>

<head>
  <title>Programa de Crónicos | Nueva Petición</title>
</head>

<style>
  .tags-input-container {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 5px;
    width: 100%;
    min-height: 34px;
    padding: 4px 12px;
    font-size: 14px;
    line-height: 1.42857143;
    color: #555;
    background-color: #fff;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075);
    transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
    cursor: text;
  }
  .tags-input-container:focus-within {
    border-color: #66afe9;
    outline: 0;
    box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075), 0 0 8px rgba(102, 175, 233, .6);
  }
  .tags-input-fake {
    border: none;
    outline: none;
    background: transparent;
    flex-grow: 1;
    min-width: 150px;
    height: 24px;
    box-shadow: none !important;
  }
  .tag-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 12px;
    margin-bottom: 2px;
    margin-top: 2px;
  }
  .tag-badge .remove-tag {
    margin-left: 6px;
    cursor: pointer;
    font-weight: bold;
  }
  .table-detalle th { background-color: #f4f4f4; text-align: center; }
  .table-detalle td { text-align: center; vertical-align: middle !important; }
  .area-trabajo-blanca {
    background-color: #ffffff;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    margin-bottom: 20px;
    overflow: hidden;
  }
  .table-responsive { max-height: 350px; overflow-y: auto; }
  .table-detalle thead th { position: sticky; top: 0; background-color: #f4f4f4; z-index: 1; }
  .badge-edit { cursor: pointer; }
</style>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Nueva Petición a Guanare</h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="programa_listado.php"><i class="fa fa-heartbeat"></i>Programa</a></li>
      <li class="active"><a href="#"><i class="fa fa-plus"></i>Nueva Petición</a></li>
    </ol>
  </section>

  <section class="content">
    <form id="formularioPeticion" action="../../cfg/agregar/agregar_peticion.php" method="POST" autocomplete="off">

      <input type="hidden" name="detalles_peticion_json" id="detalles_peticion_json">

      <div class="row">
        <div class="col-md-12">
          <div class="area-trabajo-blanca">

            <!-- CABECERA: DATOS DE LA PETICIÓN -->
            <div class="box box-primary">
              <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-clipboard"></i> Datos de la Petición:</h3>
              </div>
              <div class="box-body">
                <div class="row">

                  <div class="col-sm-4 form-group">
                    <label>Médico Solicitante (*):</label>
                    <?php $id_persona_logueada = $_SESSION['id'] ?? 0; ?>
                    <select name="id_medico" id="id_medico" class="form-control" required>
                      <option value="">--- Seleccione el médico ---</option>
                      <?php
                      $sql_med = "SELECT dm.Id_detalle_medico, p.id, p.nombre, p.apellido 
                                  FROM detalle_medico dm 
                                  INNER JOIN persona p ON dm.Id_persona = p.id 
                                  WHERE p.estatus IN (1,2) AND dm.tipo_medico = 'Interno'
                                  ORDER BY p.nombre ASC";
                      $res_med = $conexion->query($sql_med);
                      while ($row_m = $res_med->fetch_assoc()) {
                        $selected = ($row_m['id'] == $id_persona_logueada) ? 'selected' : '';
                        echo '<option value="' . $row_m['Id_detalle_medico'] . '" ' . $selected . '>' . htmlspecialchars($row_m['nombre'] . ' ' . $row_m['apellido']) . '</option>';
                      }
                      ?>
                    </select>
                  </div>

                  <div class="col-sm-4 form-group">
                    <label>Mes de la Petición (*):</label>
                    <input type="month" name="mes_peticion" id="mes_peticion" class="form-control" value="<?php echo date('Y-m'); ?>" max="<?php echo date('Y-m'); ?>" required>
                  </div>

                  <div class="col-sm-4 form-group">
                    <label>Fecha de Generación (*):</label>
                    <input type="date" name="fecha_generacion" id="fecha_generacion" class="form-control" value="<?php echo date('Y-m-d'); ?>" readonly>
                  </div>

                </div>
              </div>
            </div>

            <!-- TABLA DE PACIENTES INCLUIDOS -->
            <div class="box box-success">
              <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-users"></i> Pacientes Incluidos en la Petición:</h3>
                <div class="box-tools pull-right" style="margin-left:5px;">
                  <button type="button" class="btn btn-primary btn-sm" id="btnAbrirModalPacientes" style="width:200px;">
                    <i class="fa fa-plus"></i> Añadir Paciente
                  </button>
                  <p></p>
                  <button type="button" class="btn btn-success btn-sm" id="btnCargarTodosPacientes" style="width:200px;">
                    <i class="fa fa-refresh"></i> Cargar Todos los Crónicos
                  </button>
                </div>
              </div>

              <div class="box-body">
                <div class="table-responsive">
                  <table class="table table-bordered table-striped table-hover table-detalle" id="tablaPacientesPeticion">
                    <thead>
                      <tr>
                        <th style="width: 30px;"><input type="checkbox" id="checkTodosPacientes" checked></th>
                        <th style="text-align: left;">Paciente</th>
                        <th>Enfermedad</th>
                        <th>Medicamentos / Dosis</th>
                        <th>Cant. Total</th>
                        <th style="width: 100px;">Acciones</th>
                      </tr>
                    </thead>
                    <tbody id="cuerpoTablaPacientes">
                      <tr id="filaVaciaPacientes">
                        <td colspan="6" class="text-center text-muted">Aún no se han añadido pacientes a la petición.</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <div class="box-footer text-right" style="background-color: transparent; border-top: 1px solid #f4f4f4; padding-top: 15px;">
                <button type="button" class="btn btn-second" id="abrirModalRegresar">Regresar</button>
                <button type="button" class="btn btn-success" id="btnValidarGuardar"><i class="fa fa-save"></i> Guardar Petición</button>
              </div>
            </div>

          </div>
        </div>
      </div>
    </form>
  </section>

  <!-- ========================================== -->
  <!-- MODAL 1: AÑADIR PACIENTE MANUALMENTE       -->
  <!-- ========================================== -->
  <div class="modal fade" id="modalAgregarPaciente" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h4 class="modal-title"><i class="fa fa-user-plus"></i> Añadir Paciente a la Petición</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-sm-12 form-group">
              <label>Buscar Paciente (Cédula o Nombre):</label>
              <div class="input-group">
                <select name="tipo_cedula_paciente" id="tipo_cedula_paciente" class="form-control" style="width: 80px; border-top-right-radius: 0; border-bottom-right-radius: 0; padding: 6px 4px; font-weight: bold; background-color: #f4f4f4;">
                  <option value="V">V-</option>
                  <option value="E">E-</option>
                  <option value="PN">PN-</option>
                  <option value="RP">RP-</option>
                </select>
                <input type="text" id="buscar_paciente_modal" class="form-control" placeholder="Escriba cédula o nombre..." autocomplete="off" style="border-radius: 0;">
                <span class="input-group-btn">
                  <button class="btn btn-info" type="button" id="btnFiltrarPacientesModal" style="height: 34px;">
                    <i class="fa fa-search"></i> Buscar
                  </button>
                </span>
              </div>
            </div>
          </div>

          <div class="table-responsive" style="max-height: 350px;">
            <table class="table table-bordered table-striped table-hover" style="margin-bottom: 0;">
              <thead style="background-color: #f8f9fa;">
                <tr>
                  <th style="width: 30px;"></th>
                  <th>Cédula</th>
                  <th>Paciente</th>
                  <th>Enfermedad</th>
                  <th>Medicamentos</th>
                </tr>
              </thead>
              <tbody id="cuerpoTablaPacientesModal">
                <!-- Aquí se cargan los pacientes por AJAX -->
                <tr><td colspan="5" class="text-center text-muted"><i class="fa fa-spinner fa-spin"></i> Cargando pacientes...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-success" id="btnConfirmarAgregarPaciente"><i class="fa fa-check"></i> Añadir Seleccionados</button>
        </div>
      </div>
    </div>
  </div>

  <!-- ========================================== -->
  <!-- MODAL 2: GESTIONAR MEDICAMENTOS DE UN PACIENTE -->
  <!-- ========================================== -->
  <div class="modal fade" id="modalMedicamentosPaciente" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header bg-info">
          <h4 class="modal-title"><i class="fa fa-medkit"></i> Medicamentos de: <span id="nombre_paciente_modal"></span></h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-sm-8 form-group">
              <label>Medicamento del Catálogo (*):</label>
              <select id="select_medicamento_paciente" class="form-control">
                <option value="">--- Seleccione un Medicamento ---</option>
                <?php
                // Sin filtro de stock (es para pedir, no para despachar)
                $sql_meds = "SELECT dm.Id AS id_desc, m.nombre_medicamento, p.nombre_presentacion
                             FROM descripcion_medicamento dm
                             INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento
                             INNER JOIN presentacion p ON dm.Id_presentacion = p.Id_presentacion
                             WHERE m.estatus = 1 AND dm.estatus = 1
                             ORDER BY m.nombre_medicamento ASC";
                $res_meds = $conexion->query($sql_meds);
                while ($row_med = $res_meds->fetch_assoc()) {
                  echo '<option value="' . $row_med['id_desc'] . '" data-nombre="' . htmlspecialchars($row_med['nombre_medicamento'] . " [" . $row_med['nombre_presentacion'] . "]") . '">' . htmlspecialchars($row_med['nombre_medicamento']) . " - [" . htmlspecialchars($row_med['nombre_presentacion']) . "]" . '</option>';
                }
                ?>
              </select>
            </div>
            <div class="col-sm-4 form-group">
              <label>&nbsp;</label>
              <button type="button" class="btn btn-info btn-block" id="btnAnadirMedicamentoPaciente" style="height: 34px;">
                <i class="fa fa-plus"></i> Añadir
              </button>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-4 form-group">
              <label>Dosis (*):</label>
              <input type="text" id="dosis_medicamento" class="form-control" placeholder="Ej: 850mg">
            </div>
            <div class="col-sm-4 form-group">
              <label>Frecuencia (*):</label>
              <input type="text" id="frecuencia_medicamento" class="form-control" placeholder="Ej: Cada 12 horas">
            </div>
            <div class="col-sm-4 form-group">
              <label>Cantidad Mensual (*):</label>
              <input type="number" id="cantidad_mensual_medicamento" class="form-control" placeholder="Ej: 60" min="1">
            </div>
          </div>

          <hr>

          <h5 style="font-weight: bold; color: #555;">Medicamentos Asignados:</h5>
          <div class="table-responsive" style="max-height: 200px;">
            <table class="table table-bordered table-striped" style="margin-bottom: 0;">
              <thead style="background-color: #f8f9fa;">
                <tr>
                  <th>Medicamento</th>
                  <th>Dosis</th>
                  <th>Frecuencia</th>
                  <th>Cant. Mensual</th>
                  <th style="width: 80px;">Acciones</th>
                </tr>
              </thead>
              <tbody id="cuerpoMedicamentosPaciente">
                <tr id="filaVaciaMedicamentos">
                  <td colspan="5" class="text-center text-muted">No hay medicamentos añadidos.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-success" id="btnGuardarMedicamentosPaciente"><i class="fa fa-save"></i> Guardar Medicamentos</button>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL DE AVISO -->
  <div class="modal fade" id="avisoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div id="headerAvisoPeticion" class="modal-header bg-crimson">
          <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Aviso Importante</h4>
        </div>
        <div class="modal-body" id="cuerpoAviso" style="font-size: 15px;"></div>
        <div class="modal-footer" style="padding: 10px;">
          <button type="button" id="confirmAviso" class="btn btn-second" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL DE REGRESAR -->
  <div class="modal fade" id="modalRegresarPeticion" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h4 class="modal-title"><i class="fa fa-warning"></i> Confirmación de Regreso</h4>
        </div>
        <div class="modal-body">
          <p>Al hacer clic en "Abandonar Formulario", perderá todos los datos no guardados. ¿Desea continuar?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <a href="programa_listado.php" class="btn btn-danger">Abandonar Formulario</a>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL DE CONFIRMACIÓN DE GUARDADO -->
  <div class="modal fade" id="modalGuardarPeticion" tabindex="-1" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-green">
          <h4 class="modal-title"><i class="fa fa-check-circle"></i> Confirmación de Guardado</h4>
        </div>
        <div class="modal-body">
          ¿Está seguro que desea registrar esta petición con los pacientes seleccionados?
          <br><small class="text-muted">La petición quedará en estado "Borrador".</small>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <button type="button" id="confirmarGuardarPeticion" class="btn btn-success">Aceptar</button>
        </div>
      </div>
    </div>
  </div>

</div>

<?php include('includes/footer.php'); ?>

<script>
  // ==========================================
  // VARIABLES GLOBALES (SIMULACIÓN DE DATOS)
  // ==========================================
  let listaPacientesPeticion = [];
  let pacienteEditandoIndex = -1;
  let medicamentosPacienteTemp = [];

  // Simulamos el catálogo de pacientes crónicos (en producción vendría de AJAX)
  const pacientesCronicosSimulados = [
    { id: 1, cedula: 'V-12345678', nombre: 'Ana Pérez', edad: 45, enfermedad: 'Diabetes Mellitus Tipo 2', medicamentos: [{ id_med: 1, nombre: 'Metformina 850mg', dosis: '850mg', frecuencia: 'Cada 12h', cantidad: 60 }] },
    { id: 2, cedula: 'V-87654321', nombre: 'Carlos Gómez', edad: 62, enfermedad: 'Hipertensión Arterial', medicamentos: [{ id_med: 2, nombre: 'Enalapril 10mg', dosis: '10mg', frecuencia: 'Cada 24h', cantidad: 30 }] },
    { id: 3, cedula: 'V-11223344', nombre: 'María Rodríguez', edad: 38, enfermedad: 'Epilepsia', medicamentos: [{ id_med: 3, nombre: 'Carbamazepina 200mg', dosis: '200mg', frecuencia: 'Cada 12h', cantidad: 60 }] }
  ];

  // ==========================================
  // AVISOS
  // ==========================================
  function mostrarAviso(mensaje, tipo = 'error') {
    if (tipo === 'exito') {
      $('#headerAvisoPeticion').removeClass('bg-crimson').addClass('bg-green');
      $('#confirmAviso').removeClass('btn-danger').addClass('btn-success');
    } else {
      $('#headerAvisoPeticion').removeClass('bg-green').addClass('bg-crimson');
      $('#confirmAviso').removeClass('btn-success').addClass('btn-danger');
    }
    $('#cuerpoAviso').html(mensaje);
    $('#avisoModal').modal('show');
  }

  $(document).ready(function() {
    // Control de modales anidados
    $(document).on('show.bs.modal', '.modal', function() {
      var zIndex = 1040 + (10 * $('.modal:visible').length);
      $(this).css('z-index', zIndex);
      setTimeout(function() {
        $('.modal-backdrop').not(':last').css('opacity', 0);
        $('.modal-backdrop').last().css('z-index', zIndex - 1);
      }, 0);
    });

    $(document).on('hidden.bs.modal', '.modal', function() {
      if ($('.modal:visible').length > 0) {
        $('.modal-backdrop').last().css('opacity', 0.7);
        $('body').addClass('modal-open');
      } else {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
      }
    });

    // ==========================================
    // CARGAR TODOS LOS PACIENTES CRÓNICOS
    // ==========================================
    $('#btnCargarTodosPacientes').on('click', function() {
      let $btn = $(this);
      $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Cargando...');

      // Simulamos la carga (en producción sería AJAX)
      setTimeout(function() {
        let agregados = 0;
        pacientesCronicosSimulados.forEach(function(p) {
          let existe = listaPacientesPeticion.find(x => x.id === p.id);
          if (!existe) {
            listaPacientesPeticion.push({ ...p, seleccionado: true });
            agregados++;
          }
        });

        actualizarTablaPacientes();
        $btn.prop('disabled', false).html('<i class="fa fa-refresh"></i> Cargar Todos los Crónicos');

        if (agregados > 0) {
          mostrarAviso(`Se cargaron <strong>${agregados}</strong> pacientes crónicos automáticamente.`, 'exito');
        } else {
          mostrarAviso('Todos los pacientes crónicos ya están en la lista.');
        }
      }, 500);
    });

    // ==========================================
    // ABRIR MODAL DE PACIENTES (BÚSQUEDA)
    // ==========================================
    $('#btnAbrirModalPacientes').on('click', function() {
      $('#tipo_cedula_paciente').val('V');
      $('#buscar_paciente_modal').val('');
      cargarPacientesModal();
      $('#modalAgregarPaciente').modal('show');
    });

    function cargarPacientesModal(filtro = '') {
      let tbody = $('#cuerpoTablaPacientesModal');
      tbody.html('<tr><td colspan="5" class="text-center"><i class="fa fa-spinner fa-spin"></i> Cargando...</td></tr>');

      setTimeout(function() {
        let filtrados = pacientesCronicosSimulados.filter(function(p) {
          if (filtro === '') return true;
          return p.cedula.toLowerCase().includes(filtro.toLowerCase()) || p.nombre.toLowerCase().includes(filtro.toLowerCase());
        });

        // Quitar los que ya están en la lista
        filtrados = filtrados.filter(function(p) {
          return !listaPacientesPeticion.find(x => x.id === p.id);
        });

        if (filtrados.length === 0) {
          tbody.html('<tr><td colspan="5" class="text-center text-muted">No se encontraron pacientes disponibles.</td></tr>');
          return;
        }

        let html = '';
        filtrados.forEach(function(p) {
          html += `<tr>
            <td><input type="checkbox" class="check-paciente-modal" value="${p.id}"></td>
            <td>${p.cedula}</td>
            <td><strong>${p.nombre}</strong></td>
            <td>${p.enfermedad}</td>
            <td>${p.medicamentos.length} medicamento(s)</td>
          </tr>`;
        });
        tbody.html(html);
      }, 300);
    }

    $('#btnFiltrarPacientesModal').on('click', function() {
      cargarPacientesModal($('#buscar_paciente_modal').val().trim());
    });

    $('#buscar_paciente_modal').on('keyup', function(e) {
      if (e.key === 'Enter') {
        $('#btnFiltrarPacientesModal').click();
      }
    });

    // ==========================================
    // AÑADIR PACIENTES SELECCIONADOS DESDE EL MODAL
    // ==========================================
    $('#btnConfirmarAgregarPaciente').on('click', function() {
      let seleccionados = $('.check-paciente-modal:checked').map(function() {
        return parseInt($(this).val());
      }).get();

      if (seleccionados.length === 0) {
        mostrarAviso('Debe seleccionar al menos un paciente.');
        return;
      }

      let agregados = 0;
      seleccionados.forEach(function(id) {
        let paciente = pacientesCronicosSimulados.find(p => p.id === id);
        if (paciente && !listaPacientesPeticion.find(x => x.id === id)) {
          listaPacientesPeticion.push({ ...paciente, seleccionado: true });
          agregados++;
        }
      });

      actualizarTablaPacientes();
      $('#modalAgregarPaciente').modal('hide');
      mostrarAviso(`Se añadieron <strong>${agregados}</strong> paciente(s) a la petición.`, 'exito');
    });

    // ==========================================
    // ACTUALIZAR TABLA DE PACIENTES
    // ==========================================
    function actualizarTablaPacientes() {
      let tbody = $('#cuerpoTablaPacientes');
      tbody.empty();

      if (listaPacientesPeticion.length === 0) {
        tbody.html('<tr id="filaVaciaPacientes"><td colspan="6" class="text-center text-muted">Aún no se han añadido pacientes a la petición.</td></tr>');
        return;
      }

      listaPacientesPeticion.forEach(function(p, index) {
        let totalCant = p.medicamentos.reduce(function(sum, m) { return sum + m.cantidad; }, 0);
        let medsResumen = p.medicamentos.map(function(m) { return m.nombre + ' (' + m.dosis + ')'; }).join('<br>');

        tbody.append(`<tr>
          <td><input type="checkbox" class="check-paciente-tabla" data-index="${index}" ${p.seleccionado ? 'checked' : ''}></td>
          <td style="text-align: left;"><strong>${p.nombre}</strong><br><small>${p.cedula}</small></td>
          <td>${p.enfermedad}</td>
          <td style="text-align: left; font-size: 11px;">${medsResumen || '<em>Sin medicamentos</em>'}</td>
          <td><span class="badge bg-green" style="font-size: 13px;">${totalCant}</span></td>
          <td>
            <button type="button" class="btn btn-info btn-xs btn-editar-medicamentos" data-index="${index}" title="Editar Medicamentos">
              <i class="fa fa-medkit"></i>
            </button>
            <button type="button" class="btn btn-danger btn-xs" onclick="eliminarPacientePeticion(${index})" title="Quitar de la lista">
              <i class="fa fa-times"></i>
            </button>
          </td>
        </tr>`);
      });
    }

    // ==========================================
    // CHECKBOX DE SELECCIÓN
    // ==========================================
    $(document).on('change', '.check-paciente-tabla', function() {
      let index = $(this).data('index');
      listaPacientesPeticion[index].seleccionado = $(this).is(':checked');
    });

    $('#checkTodosPacientes').on('change', function() {
      let checked = $(this).is(':checked');
      listaPacientesPeticion.forEach(function(p) { p.seleccionado = checked; });
      actualizarTablaPacientes();
    });

    // ==========================================
    // ELIMINAR PACIENTE
    // ==========================================
    window.eliminarPacientePeticion = function(index) {
      listaPacientesPeticion.splice(index, 1);
      actualizarTablaPacientes();
    };

    // ==========================================
    // EDITAR MEDICAMENTOS DE UN PACIENTE
    // ==========================================
    $(document).on('click', '.btn-editar-medicamentos', function() {
      let index = $(this).data('index');
      pacienteEditandoIndex = index;

      let paciente = listaPacientesPeticion[index];
      $('#nombre_paciente_modal').text(paciente.nombre);

      // Copiamos los medicamentos actuales del paciente
      medicamentosPacienteTemp = JSON.parse(JSON.stringify(paciente.medicamentos));

      renderizarMedicamentosPaciente();

      $('#modalMedicamentosPaciente').modal('show');
    });

    // ==========================================
    // AÑADIR MEDICAMENTO AL PACIENTE (DENTRO DEL MODAL)
    // ==========================================
    $('#btnAnadirMedicamentoPaciente').on('click', function() {
      let idMed = $('#select_medicamento_paciente').val();
      let nombreMed = $('#select_medicamento_paciente option:selected').data('nombre');
      let dosis = $('#dosis_medicamento').val().trim();
      let frecuencia = $('#frecuencia_medicamento').val().trim();
      let cantidad = parseInt($('#cantidad_mensual_medicamento').val());

      if (!idMed) {
        mostrarAviso('Debe seleccionar un medicamento.');
        return;
      }
      if (dosis === '') {
        mostrarAviso('La dosis es obligatoria.');
        return;
      }
      if (frecuencia === '') {
        mostrarAviso('La frecuencia es obligatoria.');
        return;
      }
      if (!cantidad || cantidad <= 0) {
        mostrarAviso('La cantidad mensual debe ser mayor a 0.');
        return;
      }

      let existe = medicamentosPacienteTemp.find(m => m.id_med == idMed);
      if (existe) {
        mostrarAviso('Este medicamento ya está en la lista del paciente.');
        return;
      }

      medicamentosPacienteTemp.push({
        id_med: idMed,
        nombre: nombreMed,
        dosis: dosis,
        frecuencia: frecuencia,
        cantidad: cantidad
      });

      // Limpiar campos
      $('#select_medicamento_paciente').val('');
      $('#dosis_medicamento').val('');
      $('#frecuencia_medicamento').val('');
      $('#cantidad_mensual_medicamento').val('');

      renderizarMedicamentosPaciente();
    });

    function renderizarMedicamentosPaciente() {
      let tbody = $('#cuerpoMedicamentosPaciente');
      tbody.empty();

      if (medicamentosPacienteTemp.length === 0) {
        tbody.html('<tr id="filaVaciaMedicamentos"><td colspan="5" class="text-center text-muted">No hay medicamentos añadidos.</td></tr>');
        return;
      }

      medicamentosPacienteTemp.forEach(function(m, index) {
        tbody.append(`<tr>
          <td style="text-align: left;"><strong>${m.nombre}</strong></td>
          <td>${m.dosis}</td>
          <td>${m.frecuencia}</td>
          <td><span class="badge bg-primary">${m.cantidad}</span></td>
          <td>
            <button type="button" class="btn btn-danger btn-xs" onclick="eliminarMedicamentoPaciente(${index})">
              <i class="fa fa-times"></i>
            </button>
          </td>
        </tr>`);
      });
    }

    window.eliminarMedicamentoPaciente = function(index) {
      medicamentosPacienteTemp.splice(index, 1);
      renderizarMedicamentosPaciente();
    };

    // ==========================================
    // GUARDAR MEDICAMENTOS DEL PACIENTE
    // ==========================================
    $('#btnGuardarMedicamentosPaciente').on('click', function() {
      if (medicamentosPacienteTemp.length === 0) {
        mostrarAviso('Debe añadir al menos un medicamento al paciente.');
        return;
      }

      listaPacientes
            listaPacientesPeticion[pacienteEditandoIndex].medicamentos = JSON.parse(JSON.stringify(medicamentosPacienteTemp));
      
      actualizarTablaPacientes();
      $('#modalMedicamentosPaciente').modal('hide');
      mostrarAviso('Medicamentos actualizados correctamente.', 'exito');
    });

    // ==========================================
    // VALIDAR Y GUARDAR PETICIÓN
    // ==========================================
    $('#btnValidarGuardar').on('click', function() {
      $('#id_medico').removeClass('input-error');
      $('#mes_peticion').removeClass('input-error');

      if ($('#id_medico').val() === '') {
        $('#id_medico').addClass('input-error');
        mostrarAviso('Debe seleccionar el médico solicitante.');
        return;
      }

      if ($('#mes_peticion').val() === '') {
        $('#mes_peticion').addClass('input-error');
        mostrarAviso('Debe seleccionar el mes de la petición.');
        return;
      }

      let seleccionados = listaPacientesPeticion.filter(p => p.seleccionado);
      
      if (seleccionados.length === 0) {
        mostrarAviso('Debe incluir al menos un paciente en la petición.');
        return;
      }

      // Verificar que todos los pacientes tengan al menos 1 medicamento
      let pacientesSinMedicamentos = seleccionados.filter(p => p.medicamentos.length === 0);
      if (pacientesSinMedicamentos.length > 0) {
        let nombres = pacientesSinMedicamentos.map(p => p.nombre).join(', ');
        mostrarAviso(`Los siguientes pacientes no tienen medicamentos asignados: <br><strong>${nombres}</strong><br><br>Debe asignarles al menos un medicamento o quitarlos de la lista.`);
        return;
      }

      // Armamos el JSON con los detalles para enviar al backend
      let detallesParaEnviar = [];
      seleccionados.forEach(function(p) {
        p.medicamentos.forEach(function(m) {
          detallesParaEnviar.push({
            id_paciente: p.id,
            id_medicamento: m.id_med,
            dosis: m.dosis,
            frecuencia: m.frecuencia,
            cantidad_solicitada: m.cantidad
          });
        });
      });

      $('#detalles_peticion_json').val(JSON.stringify(detallesParaEnviar));
      
      $('#modalGuardarPeticion').modal('show');
    });

    // ==========================================
    // CONFIRMAR GUARDADO
    // ==========================================
    $('#confirmarGuardarPeticion').on('click', function() {
      $('#modalGuardarPeticion').modal('hide');
      $('#formularioPeticion')[0].submit();
    });

    // ==========================================
    // MODAL DE REGRESAR
    // ==========================================
    $('#abrirModalRegresar').on('click', function() {
      $('#modalRegresarPeticion').modal('show');
    });

  });
</script>
</div>