<?php
include('../includes/headerPapelera.php');
verificarAcceso('Ver papelera de usuarios');
?>

<head>
  <title>Usuarios | Papelera</title>
</head>

<div class="content-wrapper">
  <?php
  include('../../../cfg/conexion.php');
  $sqlUsuarios = ("SELECT r.Id_rol, p.id, p.estatus FROM persona p 
  JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona 
  JOIN rol r ON dpr.Id_rol = r.Id_rol
  HAVING r.Id_rol IN (1, 2, 6, 7, 8, 9) AND p.estatus = 0 ORDER BY id ASC");
  $queryData   = mysqli_query($conexion, $sqlUsuarios);
  $total_usuarios = mysqli_num_rows($queryData);
  ?>

  <section class="content-header">
    <h1>
      Usuarios Inactivos (<?php echo $total_usuarios; ?>)
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
      <li><a href="#"><i class="fa fa-cog"></i>Usuarios</a></li>
      <li class="active"><a href="#"><i class="fa fa-user"></i>Papelera</a></li>
    </ol>
  </section>

  <section class="content">
    <div class="toolbar-container">
      <a href="../cfg_usuario_listado.php" class="btn-sm btn-primary pull-right"><i class="fa fa-user-plus"></i> Regresar al Listado </a>
      <input type="text" placeholder="Buscar.." class="form-control pull-left" id="buscar" onkeyup="filtro()" style="border-radius:0; height:10%; width:250px; display:inline-block;">
    </div>
    <br><br>
    <div id="contenedorTabla">
      <table class="table table-sm table-hover mt-4" width="100%" height="20" id="t_user">
        <thead class="table-dark">
          <th>Nombre de usuario</th>
          <th>Correo</th>
          <th>Estatus</th>
          <?php if (in_array('Gestionar acciones de usuarios', $_SESSION["permisos"])) : ?>
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

            $donde = "WHERE p.estatus = 0 AND r.Id_rol IN (1, 2, 6, 7, 8, 9)";

            if ($busqueda != '') {
              $donde .= " AND (p.nombre LIKE '%$busqueda%' OR p.apellido LIKE '%$busqueda%' OR p.email LIKE '%$busqueda%')";
            }

            $sql_conteo = "SELECT COUNT(DISTINCT p.id) as total 
                           FROM persona p 
                           JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona 
                           JOIN rol r ON dpr.Id_rol = r.Id_rol 
                           $donde";

            $resultado_conteo = mysqli_query($conexion, $sql_conteo);

            if (!$resultado_conteo) {
              die("Error en conteo SQL: " . mysqli_error($conexion));
            }

            $fila_conteo = mysqli_fetch_assoc($resultado_conteo);
            $total_usuarios = $fila_conteo['total'];
            $total_paginas = ceil($total_usuarios / $registros_por_pagina);

            $sql = "SELECT r.Id_rol, r.nombre_rol, p.id, p.email, p.nombre, p.apellido, p.estatus
                    FROM persona p 
                    JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona 
                    JOIN rol r ON dpr.Id_rol = r.Id_rol
                    $donde
                    GROUP BY p.id 
                    ORDER BY p.id ASC
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
            <td class=""><span class="text-row text-white"><?= $row['nombre']; ?> <?= $row['apellido']; ?></span></td>
            <td class=""><span class="text-row text-white"><?= $row['email']; ?></span></td>
            <td class=""><?php $estado = intval($row['estatus']);
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
                          } ?> <span title="<?php echo $titulo; ?>" class="text-row text-white" style="height: 12px; width: 12px; background-color: <?php echo $color; ?>; border-radius: 50%; display: inline-block; border: 1px solid rgba(255,255,255,0.2);"></span></td>
            <?php if (in_array('Gestionar acciones de usuarios', $_SESSION["permisos"])) : ?>
              <td>
                <?php if (in_array('Reactivar Usuarios', $_SESSION["permisos"])) : ?>
                  <a href="#" data-id="<?php echo $row['id'] ?>" class="btn-sm btn-success btn-reactivar" title="Reactivar"><img src="../../../recursos/imagenes/iconos/reactivar.png" style="width:15px; height:15px;"></a>
                <?php endif; ?>
                <?php if (in_array('Eliminar Usuarios', $_SESSION["permisos"])) : ?>
                  <a href="#" data-id="<?php echo $row['id'] ?>" class="btn-sm btn-danger btn-eliminar" title="Eliminar"><img src="../../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></a>
                <?php endif; ?>
              </td>
            <?php endif; ?>
          </tr>
      <?php }
            } else {
              echo "<tr><td colspan='6'>No se encontraron usuarios inactivos.</td></tr>";
            } ?>
        </tbody>
      </table>
    </div>

    <nav id="contenedorPaginacion" aria-label="Page navigation">
      <ul class="pagination">
        <?php
        $query_string = ($busqueda != '') ? "&buscar=" . urlencode($busqueda) : "";
        if ($pagina_actual > 1) : ?>
          <li><a href="?pagina=1<?= $query_string; ?>">&laquo;&laquo;</a></li>
          <li><a href="?pagina=<?= ($pagina_actual - 1) . $query_string; ?>">&laquo;</a></li>
        <?php endif;

        for ($i = max(1, $pagina_actual - 1); $i <= min($total_paginas, $pagina_actual + 1); $i++) : ?>
          <li class="<?= ($i == $pagina_actual) ? 'active' : ''; ?>">
            <a href="?pagina=<?= $i . $query_string; ?>"><?= $i; ?></a>
          </li>
        <?php endfor;

        if ($pagina_actual < $total_paginas) : ?>
          <li><a href="?pagina=<?= ($pagina_actual + 1) . $query_string; ?>">&raquo;</a></li>
          <li><a href="?pagina=<?= $total_paginas . $query_string; ?>">&raquo;&raquo;</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  </section>

  <?php
  $mostrar_modal_exito = false;
  $mostrar_modal_error = false;
  $mensaje_modal = '';

  if (isset($_SESSION['mensaje_user_exito'])) {
    $mostrar_modal_exito = true;
    $mensaje_modal = $_SESSION['mensaje_user_exito'];
    unset($_SESSION['mensaje_user_exito']);
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

  <div class="modal fade" id="ReactivarUsuario" tabindex="-1" role="dialog" aria-labelledby="ReactivarUsuarioLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-green">
          <h4 class="modal-title" id="ReactivarUsuarioLabel">Confirmación de Reactivación</h4>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea reactivar este usuario?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <a href="#" class="btn btn-success" id="reactivar">Aceptar</a>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="EliminarUsuario" tabindex="-1" role="dialog" aria-labelledby="EliminarUsuarioLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="EliminarUsuarioLabel">Confirmación de Eliminación</h4>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea eliminar este usuario? Esta acción no se puede deshacer.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <a href="#" class="btn btn-danger" id="eliminar">Aceptar</a>
        </div>
      </div>
    </div>
  </div>

  <?php
  include('../includes/footer.php');
  ?>

  <script>
    $(document).ready(function() {
      $(document).on('click', '.btn-reactivar', function(e) {
        e.preventDefault();
        var IdUsuario = $(this).data('id');
        var urlReactivar = "../../../cfg/reactivar/reactivar_usuario.php?Id=" + IdUsuario;
        $('#reactivar').attr('href', urlReactivar);
        $('#ReactivarUsuario').modal('show');
      })

      $(document).on('click', '.btn-eliminar', function(e) {
        e.preventDefault();
        var IdUsuario = $(this).data('id');
        var urlEliminar = "../../../cfg/eliminar/eliminar_usuario.php?Id=" + IdUsuario;
        $('#eliminar').attr('href', urlEliminar);
        $('#EliminarUsuario').modal('show');
      })

      <?php if ($mostrar_modal_exito) : ?>
        $('#modalExito').modal('show');
      <?php elseif ($mostrar_modal_error) : ?>
        $('#modalError').modal('show');
      <?php endif; ?>
    });
  </script>