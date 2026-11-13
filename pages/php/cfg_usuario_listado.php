<?php
include('includes/headerNav2.php');
verificarAcceso('Gestionar Configuraciones');
?>

<head>
  <title>Configuraciones | Usuarios</title>
</head>

<div class="content-wrapper">
  <?php
  include('../../cfg/conexion.php');
  $sqlUsuarios = ("SELECT r.Id_rol, p.id, p.estatus FROM persona p 
  JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona 
  JOIN rol r ON dpr.Id_rol = r.Id_rol
  WHERE r.Id_rol IN (1, 2, 6, 7, 8, 9) AND p.estatus IN (1, 2) 
  AND (r.Id_rol != 7 OR (r.Id_rol = 7 AND p.password != '')) ORDER BY id ASC");
  $queryData   = mysqli_query($conexion, $sqlUsuarios);
  $total_usuarios = mysqli_num_rows($queryData);

  $mostrar_boton_agregar = true;

  $sql_check_admin = "SELECT id, email FROM persona";
  if ($result_check = mysqli_query($conexion, $sql_check_admin)) {
    if (mysqli_num_rows($result_check) == 1) {
      $row = mysqli_fetch_assoc($result_check);
      if ($row['id'] == 1 && $row['email'] === 'Admin@gmail.com') {
        $mostrar_boton_agregar = false;
      }
    }
  }
  ?>

  <section class="content-header">
    <h1>
      Usuarios (<?php echo $total_usuarios; ?>)
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="#"><i class="fa fa-cog"></i>Configuraciones</a></li>
      <li class="active"><a href="#"><i class="fa fa-user"></i>Usuarios</a></li>
    </ol>
  </section>

  <section class="content">
    <div style="padding-bottom: 10px;">
      <?php if (in_array('Ver papelera de usuarios', $_SESSION["permisos"])) : ?>
        <a href="papelera/cfg_usuario_papelera_listado.php" class="btn-sm btn-primary pull-right" style="background-color:gray;">Papelera</a>
      <?php endif; ?>
      <p class="pull-right" style="width:5px;"></p>
      <?php if (in_array('Crear Usuarios', $_SESSION["permisos"]) && $mostrar_boton_agregar) : ?>
        <a href="cfg_usuario_agregar.php" class="btn-sm btn-success pull-right"><i class="fa fa-user-plus"></i>Añadir Un Nuevo Usuario</a>
      <?php endif; ?>
      <input type="text" id="buscar" name="buscar" class="form-control" placeholder="Escriba para buscar..." value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>" style="border-radius:0; height:10%; width:250px; display:inline-block;" autocomplete="off">
    </div>
    <br><br>
    <div id="contenedorTabla">
      <table class="table table-sm table-hover mt-4" width="100%" height="20" id="t_user">
        <thead class="table-dark" style="background-color: #222; color: white; font-size: 12px;">
          <th>Nombre De Usuario</th>
          <th>Correo</th>
          <th>Estado</th>
          <?php if (in_array('Gestionar acciones de usuarios', $_SESSION["permisos"])) : ?>
            <th>Acciones</th>
          <?php endif; ?>
        </thead>
        <tbody class="tbody" width="100%" style="font-size: 12px;">
          <?php
          $busqueda = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : '';

          $donde = "WHERE r.Id_rol IN (1, 2, 6, 7, 8, 9) AND p.estatus IN (1, 2) 
          AND (r.Id_rol != 7 OR (r.Id_rol = 7 AND p.password != ''))";
          if ($busqueda != '') {
            $donde .= " AND (p.nombre LIKE '%$busqueda%' OR p.apellido LIKE '%$busqueda%' OR p.email LIKE '%$busqueda%')";
          }

          $sql_conteo = "SELECT COUNT(DISTINCT p.id) as total 
                    FROM persona p 
                    JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona 
                    JOIN rol r ON dpr.Id_rol = r.Id_rol 
                    $donde";
          $resultado_conteo = mysqli_query($conexion, $sql_conteo);
          $fila_conteo = mysqli_fetch_assoc($resultado_conteo);
          $total_usuarios_filtrados = $fila_conteo['total'];

          $registros_por_pagina = 14;
          $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
          $inicio = ($pagina_actual - 1) * $registros_por_pagina;
          $total_paginas = ceil($total_usuarios_filtrados / $registros_por_pagina);

          $sql = "SELECT r.Id_rol, r.nombre_rol, p.id, p.email, p.nombre, p.apellido, p.estatus, p.login_attempts
              FROM persona p 
              JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona 
              JOIN rol r ON dpr.Id_rol = r.Id_rol
              $donde
              GROUP BY p.id 
              ORDER BY p.id ASC
              LIMIT $inicio, $registros_por_pagina";
          $resultado = $conexion->query($sql);

          if ($resultado->num_rows > 0) {
            while ($row = $resultado->fetch_assoc()) {

              $intentos = $row['login_attempts'];
              $max_intentos = 3;
              $estilo_bloqueo = "";
              $clase_texto = "text-white";

              if ($intentos >= $max_intentos) {
                $estilo_bloqueo = "background-color: #ffc107 !important;";
                $clase_texto = "text-dark";
              }
          ?>
              <tr style="<?php echo $estilo_bloqueo; ?>">
                <td class=""><span class="text-row <?php echo $clase_texto; ?>"><?= $row['nombre']; ?> <?= $row['apellido']; ?></span></td>
                <td class=""><span class="text-row <?php echo $clase_texto; ?>"><?= $row['email']; ?></span></td>
                <td class="">
                  <?php
                  if ($intentos >= $max_intentos) {
                    $color = "#dc3545";
                    $titulo = "Bloqueado por intentos (" . $intentos . ")";
                  } else {
                    $estado = intval($row['estatus']);
                    switch ($estado) {
                      case 1:
                        $color = "#28a745";
                        $titulo = "En línea";
                        break;
                      case 2:
                        $color = "#dc3545";
                        $titulo = "Desconectado";
                        break;
                      case 0:
                        $color = "#6c757d";
                        $titulo = "Desactivado";
                        break;
                      default:
                        $color = "transparent";
                        $titulo = "Desconocido";
                        break;
                    }
                  }
                  ?>
                  <span title="<?php echo $titulo; ?>" class="text-row" style="height: 12px; width: 12px; background-color: <?php echo $color; ?>; border-radius: 50%; display: inline-block; border: 1px solid rgba(0,0,0,0.2);"></span>
                </td>

                <?php if (in_array('Gestionar acciones de usuarios', $_SESSION["permisos"])) : ?>
                  <td>
                    <?php if ($intentos >= $max_intentos) : ?>
                      <a href="#" data-id="<?php echo $row['id'] ?>" class="btn-sm btn-success btn-desbloquear" title="Desbloquear cuenta" style="font-weight: bold; border-radius: 3px;">
                        <i class="fa fa-unlock"></i> Desbloquear
                      </a>
                    <?php else : ?>
                      <?php if (in_array('Editar Usuarios', $_SESSION["permisos"])) : ?>
                        <a href="cfg_usuario_editar.php?Id=<?php echo $row['id'] ?>" class="btn-sm btn-warning" title="Editar"><img src="../../recursos/imagenes/iconos/editar.png" style="width:15px; height:15px;"></a>
                      <?php endif; ?>

                      <?php if (in_array('Desactivar Usuarios', $_SESSION["permisos"]) && $row['id'] != 1) : ?>
                        <a href="#" data-id="<?php echo $row['id'] ?>" class="btn-sm btn-danger btn-desactivar" title="Desactivar"><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></a>
                      <?php endif; ?>
                    <?php endif; ?>
                  </td>
                <?php endif; ?>
              </tr>
          <?php }
          } else {
            echo "<tr><td colspan='6'>No se encontraron usuarios registrados.</td></tr>";
          } ?>
        </tbody>
      </table>
    </div>

    <nav id="contenedorPaginacion" aria-label="Page navigation" style="position: fixed; bottom:0;">
      <ul class="pagination">
        <?php
        $query_string = ($busqueda != '') ? "&buscar=" . urlencode($busqueda) : "";

        if ($pagina_actual > 1) : ?>
          <li><a href="?pagina=1<?php echo $query_string; ?>" title="Primero">&laquo;&laquo;</a></li>
          <li><a href="?pagina=<?php echo ($pagina_actual - 1) . $query_string; ?>">&laquo;</a></li>
        <?php endif;

        $rango = 1;
        $inicio_ventana = max(1, $pagina_actual - $rango);
        $fin_ventana = min($total_paginas, $pagina_actual + $rango);

        if ($pagina_actual == 1) $fin_ventana = min($total_paginas, 3);
        if ($pagina_actual == $total_paginas) $inicio_ventana = max(1, $total_paginas - 2);

        for ($i = $inicio_ventana; $i <= $fin_ventana; $i++) : ?>
          <li class="<?php echo ($i == $pagina_actual) ? 'active' : ''; ?>">
            <a href="?pagina=<?php echo $i . $query_string; ?>"><?php echo $i; ?></a>
          </li>
        <?php endfor;

        if ($pagina_actual < $total_paginas) : ?>
          <li><a href="?pagina=<?php echo ($pagina_actual + 1) . $query_string; ?>">&raquo;</a></li>
          <li><a href="?pagina=<?php echo $total_paginas . $query_string; ?>" title="Último">&raquo;&raquo;</a></li>
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

    // Capturar si se requiere verificación
    if (isset($_SESSION['requiere_verificacion'])) {
      $requiere_verificacion = true;
      $email_verificacion = $_SESSION['email_verificacion'];
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
          <h5 class="modal-title" id="modalExitoLabel" style="color: white;">Operación Exitosa</h5>
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
          <h5 class="modal-title" id="modalErrorLabel" style="color: white;">Error en la Operación</h5>
        </div>
        <div class="modal-body">
          <p id="mensajeError"><?php echo $mensaje_modal; ?></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalVerificacion" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h5 class="modal-title">Verificación de Cuenta</h5>
        </div>
        <div class="modal-body text-center">
          <p>Se ha enviado un código de 6 dígitos a: <b><?php echo htmlspecialchars($email_verificacion); ?></b></p>
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

  <div class="modal fade" id="DesbloquearUsuario" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title"><i class="fa fa-unlock"></i>Confirmar Desbloqueo</h4>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea <b>desbloquear</b> la cuenta de este usuario?.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <a id="linkDesbloquear" href="#" class="btn btn-warning">Aceptar</a>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="DesactivarUsuario" tabindex="-1" role="dialog" aria-labelledby="DesactivarUsuarioLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h4 class="modal-title" id="DesactivarUsuarioLabel">Confirmar Desactivación</h4>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea desactivar este usuario? Esta acción solo se puede revertir en la papelera.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <a id="desactivar" href="#" class="btn btn-danger">Aceptar</a>
        </div>
      </div>
    </div>
  </div>

  <?php
  include('includes/footer.php');
  ?>

  </body>
  <script>
    $(document).ready(function() {
      $(document).on('click', '.btn-desactivar', function(e) {
        e.preventDefault();
        var IdUsuario = $(this).data('id');
        var urlDesactivar = "../../cfg/desactivar/desactivar_usuario.php?Id=" + IdUsuario;
        $('#desactivar').attr('href', urlDesactivar);
        $('#DesactivarUsuario').modal('show');
      })

      $(document).on('click', '.btn-desbloquear', function(e) {
        e.preventDefault();
        $('#linkDesbloquear').attr('href', "../../cfg/desbloquear_usuario.php?Id=" + $(this).data('id'));
        $('#DesbloquearUsuario').modal('show');
      });

      <?php if ($mostrar_modal_exito) : ?>
        $('#modalExito').modal('show');

        <?php if ($requiere_verificacion) : ?>
          $('#modalExito').on('hidden.bs.modal', function() {
            $('#modalVerificacion').modal('show');
            iniciarTemporizador(180);
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
            email: email
          },
          dataType: 'json',
          success: function(response) {
            if (response.success) {
              $btn.text('Reenviar Código').prop('disabled', false).hide();
              $('#btnVerificarCodigo').show().text('Verificar Código').prop('disabled', false);
              $('#inputCodigo').val('').prop('disabled', false).focus();
              $('#mensajeVerificacion').text(response.message).removeClass().addClass('text-success').show();

              iniciarTemporizador(300);
            } else {
              $btn.text('Reenviar Código').prop('disabled', false);
              $('#mensajeVerificacion').text(response.message).removeClass().addClass('text-danger').show();
            }
          },
          error: function() {
            $btn.text('Reenviar Código').prop('disabled', false);
            $('#mensajeVerificacion').text('⚠️ Error al conectar con el servidor.').removeClass().addClass('text-danger').show();
          }
        });
      });

    });
  </script>

  </html>