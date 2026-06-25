<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Representantes | Papelera</title>
  <?php
  include('../includes/headerPapelera.php');
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

    .modal.in .modal-dialog,
    #ReactivarRepresentante,
    #EliminarRepresentante {
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
  </style>

  <div class="content-wrapper">
    <?php
    include('../../../cfg/conexion.php');
    // Consulta para contar el total de Representantes con el rol 3
    $sqlRepresentantes = ("SELECT COUNT(p.id) AS total_representantes FROM persona p 
      JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona 
      JOIN rol r ON dpr.Id_rol = r.Id_rol
      WHERE r.Id_rol = 5 AND TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) >= 18 AND p.estatus = 0");
    $queryData   = mysqli_query($conexion, $sqlRepresentantes);
    $total_representantes = mysqli_fetch_assoc($queryData)['total_representantes'];
    ?>
    <section class="content-header">
      <h1>
        Representantes Inactivos (<?php echo $total_representantes; ?>)
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
        <li><a href="#"><i class="fa fa-user"></i>Representantes</a></li>
        <li><a href="#"><i class="fa fa-book"></i>Papelera</a></li>
      </ol>
    </section>

    <section class="content">
      <div style="padding-bottom: 10px;">
        <a href="../representantes_listado.php" class="btn-sm btn-primary pull-right"><i class="fa fa-user-plus"></i> Regresar al Listado </a>
        <input type="text" id="buscar" name="buscar" class="form-control" placeholder="Escriba para buscar..." value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>" style="border-radius:0; height:10%; width:250px; display:inline-block;" autocomplete="off"> </div>
      <br><br>
      <div id="contenedorTabla">
        <table class="table table-sm table-hover mt-4" width="100%" height="20" id="t_user">
          <thead class="table-dark" style="background-color: #222; color: white; font-size: 12px;">
            <th>Cedula</th>
            <th>Nombres Y Apellidos</th>
            <th>Genero</th>
            <th>Edad</th>
            <th>Telefono</th>
            <?php if (in_array('Gestionar acciones de representantes', $_SESSION["permisos"])) : ?>
              <th>Acciones</th>
            <?php endif; ?>
          </thead>
          <tbody class="tbody" width="100%" style="font-size: 12px;">
            <?php
            // Número de registros por página
            $busqueda = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : '';

            // Configuración de paginación
            $registros_por_pagina = 14;
            $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
            $inicio = ($pagina_actual - 1) * $registros_por_pagina;

            // 2. Definir filtro base (Representantes rol 5 y estatus 0)
            $donde = "WHERE r.Id_rol = 5 AND TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) >= 18 AND p.estatus = 0";

            // 3. Agregar condición de búsqueda si el usuario escribió algo
            if ($busqueda != '') {
              $donde .= " AND (p.cedula LIKE '%$busqueda%' OR p.nombre LIKE '%$busqueda%' OR p.apellido LIKE '%$busqueda%')";
            }

            // 4. Conteo total de registros filtrados
            $sql_conteo = "SELECT COUNT(DISTINCT p.id) as total 
                          FROM persona p 
                          JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona 
                          JOIN rol r ON dpr.Id_rol = r.Id_rol 
                          $donde";
            $resultado_conteo = mysqli_query($conexion, $sql_conteo);
            $fila_conteo = mysqli_fetch_assoc($resultado_conteo);
            $total_registros = $fila_conteo['total'];
            $total_paginas = ceil($total_registros / $registros_por_pagina);

            // Consulta para obtener los registros de la página actual
            $sql = "SELECT r.Id_rol, pt.prefijo, t.telefono, p.id, p.tipo_cedula, p.cedula, p.nombre, p.apellido, p.genero, p.fecha_nacimiento, p.estatus 
            FROM persona p 
            JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona 
            JOIN rol r ON dpr.Id_rol = r.Id_rol
            LEFT JOIN telefonos_personas t ON p.id = t.Id_persona
            LEFT JOIN prefijos_telefonos pt ON t.Id_prefijo = pt.Id
            $donde
            GROUP BY p.id
            LIMIT $inicio, $registros_por_pagina";
            $resultado = $conexion->query($sql);

            if ($resultado->num_rows > 0) {
              while ($row = $resultado->fetch_assoc()) {
                // Calcular la edad a partir de la fecha de nacimiento
                $fechaNacimiento = new DateTime($row['fecha_nacimiento']);
                $hoy = new DateTime();
                $edad = $hoy->diff($fechaNacimiento)->y;

            ?>
                <tr>
                  <td class=""><span class="text-row text-white"><?= ($row['tipo_cedula']) . "-" . ($row['cedula']); ?></span></td>
                  <td class=""><span class="text-row text-white"><?= ($row['nombre']) . " " . ($row['apellido']); ?></span></td>
                  <td class=""><span class="text-row text-white"><?= ($row['genero']); ?></span></td>
                  <td class=""><span class="text-row text-white"><?= $edad; ?></span></td>
                  <td class=""><span class="text-row text-white"><?= ($row['prefijo']) . "-" . ($row['telefono']); ?></span></td>
                  <?php if (in_array('Gestionar acciones de representantes', $_SESSION["permisos"])) : ?>
                    <td>
                      <!--<a href="representante_info.php?Id=<?php echo $row['id'] ?>" class="btn-sm btn-info"><img src="../../recursos/imagenes/iconos/info.png" style="width:15px; height:15px;"></a>-->
                      <?php if (in_array('Reactivar Representantes', $_SESSION["permisos"])) : ?>
                        <a href="#" data-id="<?php echo $row['id'] ?>" class="btn-sm btn-success btn-reactivar" title="Reactivar"><img src="../../../recursos/imagenes/iconos/reactivar.png" style="width:15px; height:15px;"></a>
                      <?php endif; ?>
                      <?php if (in_array('Eliminar Representantes', $_SESSION["permisos"])) : ?>
                        <a href="#" data-id="<?php echo $row['id'] ?>" class="btn-sm btn-danger btn-eliminar" title="Eliminar"><img src="../../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></a>
                      <?php endif; ?>
                    </td>
                  <?php endif; ?>
                </tr>
            <?php
              }
            } else {
              echo "<tr><td colspan='7'>No se encontraron representantes.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
      <nav aria-label="Page navigation" style="position: fixed; bottom:0;">
        <ul class="pagination">
          <?php
          // String de consulta para mantener la búsqueda
          $query_string = ($busqueda != '') ? "&buscar=" . urlencode($busqueda) : "";

          $rango = 1; // Número de páginas a mostrar a los lados de la actual
          $inicio_ventana = max(1, $pagina_actual - $rango);
          $fin_ventana = min($total_paginas, $pagina_actual + $rango);

          // Botón Anterior
          if ($pagina_actual > 1) : ?>
            <li><a href="?pagina=1<?= $query_string; ?>">&laquo;&laquo;</a></li>
            <li><a href="?pagina=<?= ($pagina_actual - 1) . $query_string; ?>">&laquo;</a></li>
          <?php endif;

          // Ventana de páginas (ya configurada en tu archivo)
          for ($i = $inicio_ventana; $i <= $fin_ventana; $i++) : ?>
            <li class="<?= ($i == $pagina_actual) ? 'active' : ''; ?>">
              <a href="?pagina=<?= $i . $query_string; ?>"><?= $i; ?></a>
            </li>
          <?php endfor;

          // Botón Siguiente
          if ($pagina_actual < $total_paginas) : ?>
            <li><a href="?pagina=<?= ($pagina_actual + 1) . $query_string; ?>">&raquo;</a></li>
            <li><a href="?pagina=<?= $total_paginas . $query_string; ?>">&raquo;&raquo;</a></li>
          <?php endif; ?>
        </ul>
      </nav>
    </section>

    <?php
    // Lógica para preparar los mensajes de los modales de sesión
    $mostrar_modal_exito = false;
    $mostrar_modal_error = false;
    $mensaje_modal = '';

    // Usar las variables consistentes: 'mensaje_user_exito' y 'mensaje_user_error'
    if (isset($_SESSION['mensaje_user_exito'])) {
      $mostrar_modal_exito = true;
      $mensaje_modal = $_SESSION['mensaje_user_exito'];
      unset($_SESSION['mensaje_user_exito']); // Limpiar la sesión
    } elseif (isset($_SESSION['mensaje_user_error'])) {
      $mostrar_modal_error = true;
      $mensaje_modal = $_SESSION['mensaje_user_error'];
      unset($_SESSION['mensaje_user_error']); // Limpiar la sesión
    }
    ?>

    <div class="modal fade" id="modalExito" tabindex="-1" role="dialog" aria-labelledby="modalExitoLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header bg-green">
            <h5 class="modal-title" id="modalExitoLabel" style="color: white;">Operación Exitosa</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
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
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
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

    <div class="modal" id="ReactivarRepresentante" tabindex="-1" role="dialog" aria-labelledby="ReactivarRepresentanteLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #dc3545; color: white;">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="ReactivarRepresentanteLabel">Confirmar Desactivacion</h4>
          </div>
          <div class="modal-body">
            <p>¿Está seguro de que desea reactivar este representante</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
            <a href="#" class="btn btn-danger" id="reactivar">Aceptar</a>
          </div>
        </div>
      </div>
    </div>

    <div class="modal" id="EliminarRepresentante" tabindex="-1" role="dialog" aria-labelledby="EliminarRepresentanteLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #dc3545; color: white;">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="EliminarRepresentanteLabel">Confirmar Desactivacion</h4>
          </div>
          <div class="modal-body">
            <p>¿Está seguro de que desea eliminar este representante? Esta acción no se puede deshacer.</p>
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

    </body>
    <script>
      $(document).ready(function() {

        function closeCustomModal(modalElement) {
          modalElement.removeClass('in').addClass('out');
          setTimeout(() => {
            modalElement.modal('hide').removeClass('out');
          }, 100); // Duración de la animación
        }

        // CORRECCIÓN: Eventos para cerrar el modal de aviso
        $('#ReactivarRepresentante .close, #ReactivarRepresentante .btn-second').on('click', function() {
          closeCustomModal($('#ReactivarRepresentante'));
        });

        $('#EliminarRepresentante .close, #EliminarRepresentante .btn-second').on('click', function() {
          closeCustomModal($('#EliminarRepresentante'));
        });

        $(document).on('click', '.btn-reactivar', function(e) {
          e.preventDefault();
          var IdRepresentante = $(this).data('id');
          var urlReactivar = "../../../cfg/reactivar/reactivar_representante.php?Id=" + IdRepresentante;
          $('#reactivar').attr('href', urlReactivar);
          $('#ReactivarRepresentante').modal('show');
        })

        $(document).on('click', '.btn-eliminar', function(e) {
          e.preventDefault();
          var IdRepresentante = $(this).data('id');
          var urlEliminar = "../../../cfg/eliminar/eliminar_representante.php?Id=" + IdRepresentante;
          $('#eliminar').attr('href', urlEliminar);
          $('#EliminarRepresentante').modal('show');
        })

        // Script para mostrar los modales de sesión
        <?php if ($mostrar_modal_exito) : ?>
          $('#modalExito').modal('show');
        <?php elseif ($mostrar_modal_error) : ?>
          $('#modalError').modal('show');
        <?php endif; ?>
      });
    </script>

</html>