<?php
include('includes/headerNav2.php');
verificarAcceso('Gestionar RH');
?>

<head>
  <title>Recursos Humanos | Médicos</title>
</head>

<div class="content-wrapper">
  <?php
  include('../../cfg/conexion.php');
  $sqlMedicos = ("SELECT r.Id_rol, p.id, p.estatus FROM persona p 
  JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona 
  JOIN rol r ON dpr.Id_rol = r.Id_rol
  HAVING r.Id_rol = 7 AND p.estatus IN (1, 2) ORDER BY id ASC");
  $queryData   = mysqli_query($conexion, $sqlMedicos);
  $total_medicos = mysqli_num_rows($queryData);
  ?>
  <section class="content-header">
    <h1>
      Médicos (<?php echo $total_medicos; ?>)
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="#"><i class="fa fa-cog"></i>Médicos</a></li>
      <li class="active"><a href="#"><i class="fa fa-user"></i>Listado</a></li>
    </ol>
  </section>

  <section class="content">
    <div style="padding-bottom: 10px;">
      <?php if (in_array('Ver papelera de medicos', $_SESSION["permisos"])) : ?>
        <a href="papelera/rh_medico_papelera_listado.php" class="btn-sm btn-primary pull-right" style="background-color:gray;"> Papelera </a>
      <?php endif; ?>
      <p class="pull-right" style="width:5px;"></p>
      <?php if (in_array('Generar Reportes de Medicos', $_SESSION["permisos"])) : ?>
        <a href="#" class="btn-sm btn-info pull-right reporte"><i class="fa fa-book"></i> Generar Reporte </a>
      <?php endif; ?>
      <p class="pull-right" style="width:5px;"></p>
      <?php if (in_array('Crear Medicos', $_SESSION["permisos"])) : ?>
        <a href="rh_medico_agregar.php" class="btn-sm btn-success pull-right"><i class="fa fa-user-plus"></i> Añadir Un Nuevo Médico </a>
      <?php endif; ?>
      <div class="pull-left form-inline">
        <form method="GET" action="" id="formBusquedaRapida">
          <input type="text" id="buscar" name="buscar" class="form-control" placeholder="Escriba para buscar..." value="" style="border-radius:0; height:10%; width:250px; display:inline-block;" autocomplete="off">
        </form>
      </div>
      <span data-toggle="tooltip" data-placement="right" title="Aqui podras filtrar de manera avanzada la busqueda de médicos.">
        <button type="button" class="btn-sm btn-primary btn-sm pull-left" data-toggle="modal" data-target="#modalBusquedaAvanzada" style="margin-left: 5px;">
          <i class="fa fa-filter"><img src="../../recursos/imagenes/iconos/filtrar.png" style="width:10px; height:10px; filter:invert(1);" title="filtrar médicos"></i>
        </button>
      </span>
    </div>
    <br><br>
    <div id="contenedorTabla">
      <table class="table table-sm table-hover mt-4" width="100%" height="20" id="t_user">
        <thead class="table-dark" style="background-color: #222; color: white; font-size: 12px;">
          <th>Cédula</th>
          <th>Nombres Y Apellidos</th>
          <th>Genero</th>
          <th>Teléfono</th>
          <th>Tipo De Médico</th>
          <?php if (in_array('Gestionar acciones de medicos', $_SESSION["permisos"])) : ?>
            <th>Acciones</th>
          <?php endif; ?>
        </thead>
        <tbody class="tbody" width="100%" style="font-size: 12px;">
          <tr>
            <?php
            $busqueda = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : '';
            $registros_por_pagina = 14;
            $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
            $inicio = ($pagina_actual - 1) * $registros_por_pagina;

            $busqueda       = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : '';
            $f_tipo_medico  = isset($_GET['f_tipo_medico']) ? mysqli_real_escape_string($conexion, $_GET['f_tipo_medico']) : '';
            $f_genero       = isset($_GET['f_genero']) ? mysqli_real_escape_string($conexion, $_GET['f_genero']) : '';
            $f_especialidad = isset($_GET['f_especialidad']) ? (int)$_GET['f_especialidad'] : 0;

            $donde = "WHERE r.Id_rol = 7 AND p.estatus IN (1, 2)";
            if ($busqueda != '') {
              $donde .= " AND (p.nombre LIKE '%$busqueda%' 
                            OR p.apellido LIKE '%$busqueda%' 
                            OR p.cedula LIKE '%$busqueda%')";
            }

            if ($f_tipo_medico != '') {
              $donde .= " AND dm.tipo_medico = '$f_tipo_medico'";
            }
            if ($f_genero != '') {
              $donde .= " AND p.genero = '$f_genero'";
            }
            if ($f_especialidad > 0) {
              $donde .= " AND EXISTS (SELECT 1 FROM especialidades_medicos em WHERE em.Id_detalle_medico = dm.Id_detalle_medico AND em.Id_especialidad = '$f_especialidad')";
            }

            $sql_conteo = "SELECT COUNT(DISTINCT p.id) as total 
               FROM persona p 
               JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona 
               JOIN rol r ON dpr.Id_rol = r.Id_rol
               $donde";

            $resultado_conteo = mysqli_query($conexion, $sql_conteo);
            $fila_conteo = mysqli_fetch_assoc($resultado_conteo);
            $total_medicos = $fila_conteo['total'];
            $total_paginas = ceil($total_medicos / $registros_por_pagina);

            $sql = "SELECT r.Id_rol, pt.prefijo, t.telefono, p.id, p.tipo_cedula, p.cedula, 
            p.nombre, p.apellido, p.genero, p.fecha_nacimiento, p.estatus, dm.tipo_medico 
            FROM persona p
            JOIN detalle_medico dm ON  p.id = dm.Id_persona 
            JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona 
            JOIN rol r ON dpr.Id_rol = r.Id_rol
            LEFT JOIN telefonos_personas t ON p.id = t.Id_persona
            LEFT JOIN prefijos_telefonos pt ON t.Id_prefijo = pt.Id
            $donde
            GROUP BY p.id
            LIMIT $inicio, $registros_por_pagina";
            $resultado = $conexion->query($sql);
            ?>
          </tr>
          <tr>
            <?php
            if ($resultado->num_rows > 0) {
              while ($row = $resultado->fetch_assoc()) {
            ?>
          </tr>
          <tr>
            <td class=""><span class="text-row text-white"><?= ($row['tipo_cedula']) . "-" . ($row['cedula']); ?></span></td>
            <td class=""><span class="text-row text-white"><?= ($row['nombre']) . " " . ($row['apellido']); ?></span></td>
            <td class=""><span class="text-row text-white"><?= ($row['genero']); ?></span></td>
            <td class=""><span class="text-row text-white"><?= ($row['prefijo']) . "-" . ($row['telefono']); ?></span></td>
            <td class=""><span class="text-row text-white"><?= ($row['tipo_medico']); ?></span></td>
            <?php if (in_array('Gestionar acciones de medicos', $_SESSION["permisos"])) : ?>
              <td>
                <?php if (in_array('Ver Medicos', $_SESSION["permisos"])) : ?>
                  <a href="rh_medico_info.php?Id=<?php echo $row['id'] ?>" class="btn-sm btn-info" title="Ver"><img src="../../recursos/imagenes/iconos/info.png" style="width:15px; height:15px;"></a>
                <?php endif; ?>
                <?php if (in_array('Editar Medicos', $_SESSION["permisos"])) : ?>
                  <a href="rh_medico_editar.php?Id=<?php echo $row['id'] ?>" class="btn-sm btn-warning" title="Editar"><img src="../../recursos/imagenes/iconos/editar.png" style="width:15px; height:15px;"></a>
                <?php endif; ?>
                <?php if (in_array('Desactivar Medicos', $_SESSION["permisos"])) : ?>
                  <a href="#" data-id="<?php echo $row['id'] ?>" class="btn-sm btn-danger btn-desactivar" title="Desactivar"><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></a>
                <?php endif; ?>
              </td>
            <?php endif; ?>
          </tr>
      <?php }
            } else {
              echo "<tr><td colspan='6'>No se encontraron médicos registrados.</td></tr>";
            } ?>
        </tbody>
      </table>
    </div>

    <nav id="contenedorPaginacion" aria-label="Page navigation" style="position: fixed; bottom:0;">
      <ul class="pagination">
        <?php
        $query_string = ($busqueda != '') ? "&buscar=" . urlencode($busqueda) : "";
        $rango = 1;
        $inicio_ventana = max(1, $pagina_actual - $rango);
        $fin_ventana = min($total_paginas, $pagina_actual + $rango);

        if ($pagina_actual > 1) : ?>
          <li><a href="?pagina=1<?php echo $query_string; ?>">&laquo;&laquo;</a></li>
          <li><a href="?pagina=<?php echo ($pagina_actual - 1) . $query_string; ?>">&laquo;</a></li>
        <?php endif;

        for ($i = $inicio_ventana; $i <= $fin_ventana; $i++) : ?>
          <li class="<?php echo ($i == $pagina_actual) ? 'active' : ''; ?>">
            <a href="?pagina=<?php echo $i . $query_string; ?>"><?php echo $i; ?></a>
          </li>
        <?php endfor;

        if ($pagina_actual < $total_paginas) : ?>
          <li><a href="?pagina=<?php echo ($pagina_actual + 1) . $query_string; ?>">&raquo;</a></li>
          <li><a href="?pagina=<?php echo $total_paginas . $query_string; ?>">&raquo;&raquo;</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  </section>

  <?php
  $mostrar_modal_exito = false;
  $mostrar_modal_error = false;
  $requiere_verificacion = false;
  $email_verificacion = '';
  $mensaje_modal = '';

  if (isset($_SESSION['mensaje_user_exito'])) {
    $mostrar_modal_exito = true;
    $mensaje_modal = $_SESSION['mensaje_user_exito'];
    unset($_SESSION['mensaje_user_exito']);

    if (isset($_SESSION['requiere_verificacion'])) {
      $requiere_verificacion = true;
      $email_verificacion = $_SESSION['email_verificacion'] ?? '';
      unset($_SESSION['requiere_verificacion']);
      unset($_SESSION['email_verificacion']);
    }
  } elseif (isset($_SESSION['mensaje_user_error'])) {
    $mostrar_modal_error = true;
    $mensaje_modal = $_SESSION['mensaje_user_error'];
    unset($_SESSION['mensaje_user_error']);
  }
  ?>

  <div class="modal fade" id="modalExito" tabindex="-1" role="dialog" aria-labelledby="modalExitoLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-green">
          <h5 class="modal-title" id="modalExitoLabel">Operación Exitosa</h5>
        </div>
        <div class="modal-body">
          <p id="mensajeExito"><?php echo $mensaje_modal; ?></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success" data-dismiss="modal">Aceptar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalError" tabindex="-1" role="dialog" aria-labelledby="modalErrorLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h5 class="modal-title" id="modalErrorLabel">Error en la Operación</h5>
        </div>
        <div class="modal-body">
          <p id="mensajeError"><?php echo $mensaje_modal; ?></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalVerificacion" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h5 class="modal-title"><i class="fa fa-lock"></i> Verificación de Cuenta de Médico</h5>
        </div>
        <div class="modal-body text-center">
          <p>Se ha enviado un código de 6 dígitos al correo: <b><?php echo htmlspecialchars($email_verificacion); ?></b></p>
          <div class="form-group" style="width: 50%; margin: 0 auto;">
            <input type="text" id="inputCodigo" class="form-control text-center" maxlength="6" placeholder="XXXXXX" style="font-size: 24px; letter-spacing: 10px;">
          </div>
          <br>
          <p class="text-danger">Tiempo restante: <b id="temporizador">03:00</b></p>
          <p id="mensajeVerificacion" style="display: none;"></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-warning" id="btnReenviarCodigo" style="display: none;">Reenviar Código</button>
          <button type="button" class="btn btn-primary" id="btnVerificarCodigo">Verificar Código</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="DesactivarMedico" tabindex="-1" role="dialog" aria-labelledby="DesactivarMedicoLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h4 class="modal-title" id="DesactivarMedicoLabel">Confirmación de Desactivación</h4>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea desactivar este médico? Esta acción solo se puede revertir en la papelera.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <a href="#" id="desactivar" class="btn btn-danger">Aceptar</a>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalBusquedaAvanzada" tabindex="-1" role="dialog" aria-labelledby="modalFiltrosLabel">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <form id="formBusquedaAvanzada" method="GET" action="">
          <div class="modal-header bg-primary">
            <h4 class="modal-title" id="modalFiltrosLabel"><i class="fa fa-filter"></i> Filtros Avanzados de Médicos</h4>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6 form-group">
                <label>Tipo de Médico</label>
                <select name="f_tipo_medico" class="form-control">
                  <option value="">Todos</option>
                  <option value="Interno" <?php echo ($f_tipo_medico == 'Interno') ? 'selected' : ''; ?>>Interno</option>
                  <option value="Externo" <?php echo ($f_tipo_medico == 'Externo') ? 'selected' : ''; ?>>Externo</option>
                </select>
              </div>
              <div class="col-md-6 form-group">
                <label>Especialidad</label>
                <select name="f_especialidad" class="form-control">
                  <option value="0">Todas</option>
                  <?php
                  $res_esp = $conexion->query("SELECT Id_especialidad, nombre_especialidad FROM especialidad WHERE estatus = 1 ORDER BY nombre_especialidad ASC");
                  while ($esp = $res_esp->fetch_assoc()) {
                    $sel = ($f_especialidad == $esp['Id_especialidad']) ? 'selected' : '';
                    echo "<option value='" . $esp['Id_especialidad'] . "' $sel>" . htmlspecialchars($esp['nombre_especialidad']) . "</option>";
                  }
                  ?>
                </select>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 form-group">
                <label>Género</label>
                <select name="f_genero" class="form-control">
                  <option value="">Todos</option>
                  <option value="Masculino" <?php echo ($f_genero == 'Masculino') ? 'selected' : ''; ?>>Masculino</option>
                  <option value="Femenino" <?php echo ($f_genero == 'Femenino') ? 'selected' : ''; ?>>Femenino</option>
                </select>
              </div>
              <div class="col-md-6 form-group">
                <label>Buscar por Nombre o Cédula</label>
                <input type="text" name="buscar" class="form-control" placeholder="Cédula, Nombres, Apellidos..." value="<?php echo htmlspecialchars($busqueda); ?>">
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-second pull-left" onclick="limpiarFiltrosAjax()">Limpiar Filtros</button>
            <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn btn-success"><i class="fa fa-search"></i> Aplicar Búsqueda</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="ModalReporteMedico" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h4 class="modal-title" id="myModalLabel"><i class="fa fa-user-md"></i> Reportes del Personal Médico</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Seleccione el tipo de reporte:</label>
            <select class="form-control" id="tipo_reporte_medico">
              <option value="todos">Listado General de Médicos</option>
              <option value="activos">Solo Personal Activo</option>
              <option value="inactivos">Solo Personal en Permiso/Inactivo</option>
              <option value="especialidad">Agrupados por Especialidad</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-primary" id="btnEjecutarReporteMedico">Generar PDF</button>
        </div>
      </div>
    </div>
  </div>

  <?php
  include('includes/footer.php');
  ?>

  <script>
    $(document).ready(function() {
      $('.reporte').on('click', function(e) {
        e.preventDefault();
        $('#ModalReporteMedico').modal('show');
      });

      $('#btnEjecutarReporteMedico').on('click', function() {
        var tipo = $('#tipo_reporte_medico').val();
        window.open('../../cfg/reportes/generar_pdf_medicos.php?tipo=' + tipo, '_blank');
        $('#ModalReporteMedico').modal('hide');
      });

      $(document).on('click', '.btn-desactivar', function(e) {
        e.preventDefault();
        var IdMedico = $(this).data('id');
        var urlDesactivar = "../../cfg/desactivar/desactivar_medico.php?Id=" + IdMedico;
        $('#desactivar').attr('href', urlDesactivar);
        $('#DesactivarMedico').modal('show');
      });

      window.cargarDatosAjax = function(url) {
        $('.tbody').css('opacity', '0.4');

        $.get(url, function(data) {
          var htmlParsed = $(data);

          $('.tbody').html(htmlParsed.find('.tbody').html()).css('opacity', '1');
          $('nav[aria-label="Page navigation"]').html(htmlParsed.find('nav[aria-label="Page navigation"]').html());

          window.history.pushState(null, '', url);
        }).fail(function() {
          alert("Error de conexión al aplicar filtros.");
          $('.tbody').css('opacity', '1');
        });
      };

      let timer;
      $('#buscar').on('keyup', function() {
        clearTimeout(timer);
        let query = $(this).val();
        timer = setTimeout(function() {
          let formData = $('#formBusquedaAvanzada').serializeArray();
          let queryParams = [];
          $.each(formData, function(i, field) {
            if (field.name !== 'buscar' && field.value !== '' && field.value !== '0') {
              queryParams.push(field.name + '=' + encodeURIComponent(field.value));
            }
          });
          queryParams.push('buscar=' + encodeURIComponent(query));

          var url = 'rh_medico_listado.php?' + queryParams.join('&');
          cargarDatosAjax(url);
        }, 400);
      });

      $('#formBusquedaRapida').on('submit', function(e) {
        e.preventDefault();
      });

      $('#formBusquedaAvanzada').on('submit', function(e) {
        e.preventDefault();
        var url = 'rh_medico_listado.php?' + $(this).serialize();
        $('#modalBusquedaAvanzada').modal('hide');
        cargarDatosAjax(url);
      });

      $(document).on('click', 'nav[aria-label="Page navigation"] .pagination a', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        if (url) {
          cargarDatosAjax(url);
        }
      });

      window.limpiarFiltrosAjax = function() {
        $('#formBusquedaAvanzada')[0].reset();
        $('#buscar').val('');
        var urlLimpia = window.location.href.split('?')[0];
        cargarDatosAjax(urlLimpia);
        $('#modalBusquedaAvanzada').modal('hide');
      };

      <?php if ($mostrar_modal_exito) : ?>
        $('#modalExito').modal('show');

        <?php if ($requiere_verificacion) : ?>
          $('#modalExito').on('hidden.bs.modal', function() {
            $('#modalVerificacion').modal('show');
            iniciarTemporizador(180); // 3 minutos
          });
        <?php endif; ?>

      <?php elseif ($mostrar_modal_error) : ?>
        $('#modalError').modal('show');
      <?php endif; ?>

      let intervalo;

      function iniciarTemporizador(duracion) {
        let tiempo = duracion;
        let display = $('#temporizador');

        if (intervalo) clearInterval(intervalo);

        intervalo = setInterval(function() {
          let minutos = parseInt(tiempo / 60, 10);
          let segundos = parseInt(tiempo % 60, 10);
          minutos = minutos < 10 ? "0" + minutos : minutos;
          segundos = segundos < 10 ? "0" + segundos : segundos;
          display.text(minutos + ":" + segundos);

          if (--tiempo < 0) {
            clearInterval(intervalo);
            $('#inputCodigo, #btnVerificarCodigo').prop('disabled', true);
            $('#btnVerificarCodigo').hide();
            $('#btnReenviarCodigo').show();
            $('#mensajeVerificacion').text('El tiempo ha expirado. Solicite un nuevo código.').removeClass().addClass('text-danger').show();
          }
        }, 1000);
      }

      $('#btnVerificarCodigo').on('click', function() {
        var codigo = $('#inputCodigo').val().trim();
        var email = '<?php echo htmlspecialchars($email_verificacion); ?>';

        if (codigo.length !== 6) {
          $('#mensajeVerificacion').text('Debe ingresar los 6 dígitos.').removeClass().addClass('text-danger').show();
          return;
        }

        $(this).text('Verificando...').prop('disabled', true);

        $.ajax({
          url: '../../cfg/verificar_codigo.php',
          type: 'POST',
          data: {
            codigo: codigo,
            email: email
          },
          dataType: 'json',
          success: function(response) {
            if (response.success) {
              clearInterval(intervalo);
              $('#mensajeVerificacion').text(response.message).removeClass().addClass('text-success').show();
              setTimeout(function() {
                $('#modalVerificacion').modal('hide');
                location.reload();
              }, 1500);
            } else {
              $('#btnVerificarCodigo').text('Verificar Código').prop('disabled', false);
              $('#mensajeVerificacion').text(response.message).removeClass().addClass('text-danger').show();
            }
          },
          error: function() {
            $('#btnVerificarCodigo').text('Verificar Código').prop('disabled', false);
            $('#mensajeVerificacion').text('Error de conexión al verificar el código.').removeClass().addClass('text-danger').show();
          }
        });
      });

      $('#btnReenviarCodigo').on('click', function() {
        var email = '<?php echo htmlspecialchars($email_verificacion); ?>';
        var $btn = $(this);

        $btn.text('Enviando...').prop('disabled', true);
        $('#mensajeVerificacion').text('Generando nuevo código...').removeClass().addClass('text-info').show();

        $.ajax({
          url: '../../cfg/reenviar_codigo.php',
          type: 'POST',
          data: {
            email: email,
            tipo: 'medico'
          },
          dataType: 'json',
          success: function(response) {
            if (response.success) {
              $btn.text('Reenviar Código').prop('disabled', false).hide();
              $('#btnVerificarCodigo').show().text('Verificar Código').prop('disabled', false);
              $('#inputCodigo').val('').prop('disabled', false).focus();
              $('#mensajeVerificacion').text(response.message).removeClass().addClass('text-success').show();

              iniciarTemporizador(300); // 5 minutos adicionales
            } else {
              $btn.text('Reenviar Código').prop('disabled', false);
              $('#mensajeVerificacion').text(response.message).removeClass().addClass('text-danger').show();
            }
          },
          error: function() {
            $btn.text('Reenviar Código').prop('disabled', false);
            $('#mensajeVerificacion').text('Error al conectar con el servidor.').removeClass().addClass('text-danger').show();
          }
        });
      });

    });
  </script>