<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>CPT3 | Inicio</title>
  <!-- Escala Maxima De La Pantalla -->
  <meta content="width=device-width, initial-scale=1" name="viewport">
</head>
<?php
include('pages/php/includes/headerNav.php');
?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Inicio
      <small>Panel De Control</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
    </ol>
  </section>
  <!-- Main content -->
  <section class="content" id="contenedorTabla">
    <?php if (isset($_SESSION["permisos"])) : ?>
      <div class="row" id="t_user">

        <?php if (in_array('Ver panel de medicos', $_SESSION["permisos"])) : ?>

          <!-- Small boxes (Stat box) -->
          <div class="pull-left" id="box_main_left">
            <?php
            include('cfg/conexion.php');
            $sqlPacientesAdultos = ("SELECT r.Id_rol, p.id, p.fecha_nacimiento, p.estatus 
            FROM persona p 
            JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona 
            JOIN rol r ON dpr.Id_rol = r.Id_rol
            WHERE r.Id_rol = 3 AND TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) >= 18 AND p.estatus = 1 ORDER BY p.id ASC");
            $queryData   = mysqli_query($conexion, $sqlPacientesAdultos);
            $total_pacientes_adultos = mysqli_num_rows($queryData);
            ?>
            <!-- small box -->
            <div class="small-box bg-royalblue">
              <div class="inner">
                <h3><?php echo $total_pacientes_adultos; ?></h3>

                <p>Total de Pacientes Adultos</p>
              </div>
              <div class="icon">
              </div>
              <a href="pages/php/pacientes_listado.php" class="small-box-footer">Mas info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
          </div>

          <div class="pull-right" id="box_main_right">
            <?php
            include('cfg/conexion.php');
            $sqlPacientesMenores = ("SELECT
            p_menor.id AS id,
            p_menor.fecha_nacimiento AS fecha_nacimiento,
            p_menor.estatus,
            TIMESTAMPDIFF(YEAR, p_menor.fecha_nacimiento, CURDATE()) AS edad
            FROM
            persona p_menor
            JOIN
            detalle_persona_rol dpr ON p_menor.Id = dpr.Id_persona
            JOIN
            detalle_paciente_menor dp ON p_menor.Id = dp.Id_persona
            JOIN
            persona p_rep ON dp.Id_representante = p_rep.Id
            WHERE
            dpr.Id_rol = 3 AND TIMESTAMPDIFF(YEAR, p_menor.fecha_nacimiento, CURDATE()) < 18 AND p_menor.estatus
            ORDER BY id ASC");
            $queryData   = mysqli_query($conexion, $sqlPacientesMenores);
            $total_pacientes_menores = mysqli_num_rows($queryData);
            ?>
            <!-- small box -->
            <div class="small-box bg-primary">
              <div class="inner">
                <h3><?php echo $total_pacientes_menores; ?></h3>

                <p>Total de Pacientes Menores de Edad</p>
              </div>
              <div class="icon">
              </div>
              <a href="pages/php/pacientes_menores_listado.php" class="small-box-footer">Mas info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
          </div>

          <div class="pull-left" id="box_main_left">
            <?php
            include('cfg/conexion.php');
            $sqlAlergias = ("SELECT * FROM alergias_conocidas WHERE estatus = 1 ORDER BY Id_alergias_conocidas ASC");
            $queryData   = mysqli_query($conexion, $sqlAlergias);
            $total_alergias = mysqli_num_rows($queryData);
            ?>
            <!-- small box -->
            <div class="small-box bg-yellow">
              <div class="inner">
                <h3><?php echo $total_alergias; ?></h3>

                <p>Alergias</p>
              </div>
              <div class="icon">
                <i class="fa fa-user-times"></i>
              </div>
              <a href="#" class="small-box-footer">Mas info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
          </div>

          <div class="pull-right" id="box_main_right">
            <?php
            include('cfg/conexion.php');
            $sqlPatologias = ("SELECT * FROM patologias WHERE estatus = 1 ORDER BY Id_patologia ASC");
            $queryData   = mysqli_query($conexion, $sqlPatologias);
            $total_patologias = mysqli_num_rows($queryData);
            ?>
            <!-- small box -->
            <div class="small-box bg-crimson">
              <div class="inner">
                <h3><?php echo $total_patologias; ?></h3>

                <p>Patologias</p>
              </div>
              <div class="icon">
                <i class="fa fa-user-times"></i>
              </div>
              <a href="pages/php/patologias_listado.php" class="small-box-footer">Mas info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
          </div>

          <div class="pull-left" id="box_main_left">
            <?php
            $mysqli = new mysqli('localhost', 'root', '', 'cpt3db');
            if ($mysqli->connect_error) {
              die("Conexión fallida: " . $mysqli->connect_error);
            }
            $sql_citas = "SELECT COUNT(*) AS total FROM citas WHERE estatus = 1 AND DATE(fecha_cita) = CURDATE()";
            $resultado = $mysqli->query($sql_citas);
            $datos_iniciales = $resultado->fetch_assoc();
            $total_citas_hoy = number_format($datos_iniciales['total']);
            ?>
            <!-- small box -->
            <div class="small-box bg-primary" style="background-color: gray;">
              <div class="inner" id="citas_hoy">
                <h3><?php echo $total_citas_hoy; ?></h3>

                <p>Citas Para Hoy</p>
              </div>
              <div class="icon">
                <i class="fa fa-user-times"></i>
              </div>
              <a href="pages/php/citas_medicas_listado.php" class="small-box-footer">Mas info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
          </div>

          <div class="pull-right" id="box_main_right">
            <?php
            $mysqli = new mysqli('localhost', 'root', '', 'cpt3db');
            if ($mysqli->connect_error) {
              die("Conexión fallida: " . $mysqli->connect_error);
            }
            $sql_consulta = "SELECT COUNT(*) AS total FROM consulta WHERE estatus = 1 AND DATE(fecha_consulta) = CURDATE()";
            $resultado = $mysqli->query($sql_consulta);
            $datos_iniciales = $resultado->fetch_assoc();
            $total_consulta_hoy = number_format($datos_iniciales['total']);
            ?>
            <!-- small box -->
            <div class="small-box bg-primary" style="background-color: gray;">
              <div class="inner" id="consulta_hoy">
                <h3><?php echo $total_consulta_hoy; ?></h3>

                <p>Consultas de Hoy</p>
              </div>
              <div class="icon">
                <i class="fa fa-user-times"></i>
              </div>
              <a href="pages/php/consulta_listado.php" class="small-box-footer">Mas info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
          </div>
        <?php endif; ?>


        <?php if (in_array('Ver panel de farmaceutico', $_SESSION["permisos"])) : ?>

          <div class="pull-left" id="box_main_left">
            <?php
            include('cfg/conexion.php');
            $sqlEntradaMedicamentos = ("SELECT COUNT(*) AS total FROM detalle_inventario 
            WHERE Id_TipoMovimiento = 1 -- Asumiendo 1 como 'Entrada'
            AND fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY);");
            $queryData   = mysqli_query($conexion, $sqlEntradaMedicamentos);
            $row = mysqli_fetch_assoc($queryData);
            $total_entrada_medicamentos = $row['total'];
            ?>
            <!-- small box -->
            <div class="small-box bg-green">
              <div class="inner">
                <h3><?php echo $total_entrada_medicamentos; ?></h3>

                <p>Medicamentos Ingresados Durante la Semana</p>
              </div>
              <div class="icon">
                <i class="fa fa-user-plus"></i>
              </div>
              <a href="pages/php/farmacia_inventario_listado.php" class="small-box-footer">Mas info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
          </div>

          <div class="pull-right" id="box_main_right">
            <?php
            include('cfg/conexion.php');
            $sqlSalidaMedicamentos = ("SELECT COUNT(*) AS total FROM detalle_inventario 
            WHERE Id_TipoMovimiento = 2 -- Asumiendo 2 como 'Salida'
            AND fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY);");
            $queryData   = mysqli_query($conexion, $sqlSalidaMedicamentos);
            $row = mysqli_fetch_assoc($queryData);
            $total_salida_medicamentos = $row['total'];
            ?>
            <!-- small box -->
            <div class="small-box bg-primary" style="background-color: firebrick;">
              <div class="inner">
                <h3><?php echo $total_salida_medicamentos ?></h3>

                <p>Medicamentos Entregados Durante la Semana</p>
              </div>
              <div class="icon">
                <i class="fa fa-user-plus"></i>
              </div>
              <a href="pages/php/farmacia_inventario_listado.php" class="small-box-footer">Mas info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
          </div>

          <div class="pull-left" id="box_main_left">
            <?php
            include('cfg/conexion.php');
            $sqlMedicamentos = ("SELECT * FROM medicamento WHERE estatus = 1 ORDER BY Id_medicamento ASC");
            $queryData   = mysqli_query($conexion, $sqlMedicamentos);
            $total_medicamentos = mysqli_num_rows($queryData);
            ?>
            <!-- small box -->
            <div class="small-box bg-royalblue">
              <div class="inner">
                <h3><?php echo $total_medicamentos; ?></h3>

                <p>Medicamentos del Catalogo</p>
              </div>
              <div class="icon">
                <i class="fa fa-user-plus"></i>
              </div>
              <a href="pages/php/farmacia_medicamentos_listado.php" class="small-box-footer">Mas info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
          </div>

          <div class="pull-right" id="box_main_right">
            <?php
            include('cfg/conexion.php');
            $sqlMedicamentosExistentes = ("SELECT COUNT(DISTINCT Id_descripcion_medicamento) AS total
            FROM medicamentos_detalle_inventario");
            $queryData   = mysqli_query($conexion, $sqlMedicamentosExistentes);
            $row   = mysqli_fetch_assoc($queryData);
            $total_medicamentos_existentes = $row['total'];
            ?>
            <!-- small box -->
            <div class="small-box bg-primary">
              <div class="inner">
                <h3><?php echo $total_medicamentos_existentes; ?></h3>

                <p>Medicamentos Existentes En el Inventario</p>
              </div>
              <div class="icon">
                <i class="fa fa-user-plus"></i>
              </div>
              <a href="pages/php/farmacia_inventario_listado.php" class="small-box-footer">Mas info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
          </div>

          <div class="pull-left" id="box_main_left">
            <?php
            include('cfg/conexion.php');
            $sqlMedicamentosStockBajo = ("SELECT COUNT(*) AS total 
            FROM medicamentos_detalle_inventario 
            WHERE Id <= Id_lote");
            $queryData   = mysqli_query($conexion, $sqlMedicamentosStockBajo);
            $row = mysqli_fetch_assoc($queryData);
            $total_medicamentos_stock_bajo = $row['total'];
            ?>
            <!-- small box -->
            <div class="small-box bg-yellow">
              <div class="inner">
                <h3><?php echo $total_medicamentos_stock_bajo; ?></h3>

                <p>Total de Medicamentos Con el Stock Bajo</p>
              </div>
              <div class="icon">
                <i class="fa fa-user-plus"></i>
              </div>
              <a href="pages/php/farmacia_inventario_listado.php" class="small-box-footer">Mas info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
          </div>

          <div class="pull-right" id="box_main_right">
            <?php
            include('cfg/conexion.php');
            $sqlMedicamentosVencimiento = ("SELECT COUNT(*) AS total 
            FROM lotes_medicamentos 
            WHERE estatus = 1 AND fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY);");
            $queryData   = mysqli_query($conexion, $sqlMedicamentosVencimiento);
            $row = mysqli_fetch_assoc($queryData);
            $total_medicamentos_vencimiento = $row['total'];
            ?>
            <!-- small box -->
            <div class="small-box bg-crimson">
              <div class="inner">
                <h3><?php echo $total_medicamentos_vencimiento; ?></h3>

                <p>Total de Lotes Que Venceran Pronto</p>
              </div>
              <div class="icon">
                <i class="fa fa-user-plus"></i>
              </div>
              <a href="pages/php/farmacia_lotes_listado.php" class="small-box-footer">Mas info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
          </div>
        <?php endif; ?>

        <?php if (in_array('Ver panel de recursos humanos', $_SESSION["permisos"])) : ?>

          <div class="pull-left" id="box_main_left">
            <?php
            include('cfg/conexion.php');
            $sqlAreas = ("SELECT * FROM departamento WHERE estatus = 1 ORDER BY Id_departamento ASC");
            $queryData   = mysqli_query($conexion, $sqlAreas);
            $total_areas = mysqli_num_rows($queryData);
            ?>
            <!-- small box -->
            <div class="small-box bg-crimson">
              <div class="inner" id="consultas_hoy">
                <h3><?php echo $total_areas; ?></h3>

                <p>Areas</p>
              </div>
              <div class="icon">
                <i class="fa fa-user-times"></i>
              </div>
              <a href="pages/php/rh_areas_listado.php" class="small-box-footer">Mas info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
          </div>

          <div class="pull-right" id="box_main_right">
            <?php
            include('cfg/conexion.php');
            $sqlMedicos = ("SELECT r.Id_rol, p.id, p.estatus FROM persona p 
                JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona 
                JOIN rol r ON dpr.Id_rol = r.Id_rol
                HAVING r.Id_rol = 4 AND p.estatus = 1 ORDER BY id ASC");
            $queryData   = mysqli_query($conexion, $sqlMedicos);
            $total_medicos = mysqli_num_rows($queryData);
            ?>
            <!-- small box -->
            <div class="small-box bg-primary">
              <div class="inner" id="consultas_hoy">
                <h3><?php echo $total_medicos; ?></h3>

                <p>Medicos</p>
              </div>
              <div class="icon">
                <i class="fa fa-user-times"></i>
              </div>
              <a href="pages/php/rh_medico_listado.php" class="small-box-footer">Mas info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
          </div>

          <div class="pull-left" id="box_main_left">
            <?php
            include('cfg/conexion.php');
            $sqlEspecialidades = ("SELECT * FROM especialidad WHERE estatus = 1 ORDER BY Id_especialidad ASC");
            $queryData   = mysqli_query($conexion, $sqlEspecialidades);
            $total_especialidades = mysqli_num_rows($queryData);
            ?>
            <!-- small box -->
            <div class="small-box bg-primary" style="background-color: purple;">
              <div class="inner" id="consultas_hoy">
                <h3><?php echo $total_especialidades; ?></h3>

                <p>Especialidades</p>
              </div>
              <div class="icon">
                <i class="fa fa-user-times"></i>
              </div>
              <a href="pages/php/rh_especialidades_listado.php" class="small-box-footer">Mas info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
          </div>

          <div class="pull-right" id="box_main_right">
            <?php
            include('cfg/conexion.php');
            // 2. Volumen de Consultas (Médico con más actividad este mes)
            $sql_vol = "SELECT p.nombre, p.apellido, COUNT(c.Id_consulta) as total
            FROM persona p
            JOIN consulta c ON p.id = c.Id_medico
            WHERE MONTH(c.fecha_consulta) = MONTH(CURDATE()) AND YEAR(c.fecha_consulta) = YEAR(CURDATE())
            GROUP BY p.id ORDER BY total DESC LIMIT 1";
            $res_vol = $conexion->query($sql_vol);
            $data_vol = $res_vol->fetch_assoc();

            $medico_vol = isset($data_vol['nombre']) ? $data_vol['nombre']." ".$data_vol['apellido'] : "N/A";
            $total_vol = $data_vol['total'] ?? 0;
            ?>
            <!-- small box -->
            <div class="small-box bg-primary" style="background-color: seagreen;">
              <div class="inner" id="top_mes_citas">
              <h3><?php echo $total_vol; ?></h3>
              <p><?php echo $medico_vol; ?> (Médico con más consultas este mes)</p>
              </div>
              <div class="icon">
                <i class="fa fa-user-times"></i>
              </div>
              <a href="#" class="small-box-footer">Mas info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
          </div>

          <div class="pull-left" id="box_main_left">
            <?php
            include('cfg/conexion.php');
            $sql_rend = "SELECT p.nombre, p.apellido,
                 COUNT(c.Id_consulta) / COUNT(DISTINCT DATE(c.fecha_consulta)) as promedio
                 FROM persona p
                 JOIN consulta c ON p.id = c.Id_medico
                 GROUP BY p.id ORDER BY promedio DESC LIMIT 1";
            $res_rend = $conexion->query($sql_rend);
            $data_rend = $res_rend->fetch_assoc();
            
            $medico_rend = isset($data_rend['nombre']) ? $data_rend['nombre'] : "N/A";
            $promedio_rend = round($data_rend['promedio'] ?? 0, 1);
            ?>
            <!-- small box -->
            <div class="small-box bg-primary" style="background-color: gray;">
              <div class="inner" id="rendimiento_individual">
                <h3><?php echo $promedio_rend; ?></h3>
                <p>Rendimiento Individual (Promedio de consultas por día trabajado): <?php echo $medico_rend; ?></p>
              </div>
              <div class="icon">
                <i class="fa fa-user-times"></i>
              </div>
              <a href="#" class="small-box-footer">Mas info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
          </div>

          <div class="pull-right" id="box_main_right">
            <?php
            include('cfg/conexion.php');
            $sql_top_esp = "SELECT e.nombre_especialidad, COUNT(ci.Id_cita) as total_semanal
                FROM especialidad e
                JOIN citas ci ON e.Id_especialidad = ci.Id_especialidad
                WHERE WEEK(ci.fecha_cita, 1) = WEEK(CURDATE(), 1) 
                  AND YEAR(ci.fecha_cita) = YEAR(CURDATE())
                GROUP BY e.Id_especialidad
                ORDER BY total_semanal DESC
                LIMIT 2";

            $res_top_esp = $conexion->query($sql_top_esp);
            ?>
            <!-- small box -->
            <div class="small-box bg-primary" style="background-color: darkgoldenrod;">
              <div class="inner" id="top_especialidades">
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <?php 
                    $cont = 1;
                    if ($res_top_esp->num_rows > 0):
                        while($row = $res_top_esp->fetch_assoc()): ?>
                            <li style="font-size: 14px;">
                                <strong><?php echo $cont; ?>.</strong> <?php echo $row['nombre_especialidad']; ?> 
                                <span class="pull-right">(<?php echo $row['total_semanal']; ?> Citas) <i class="fa fa-calendar-check-o"></i></span>
                            </li>
                        <?php 
                        $cont++;
                        endwhile; 
                    else: ?>
                        <li style="font-size: 13px;">No hay citas registradas</li>
                        <br>
                    <?php endif; ?>
                </ul>
                <h4>Top 2 Especialidades (Citas en la Semana)</h4>
              </div>
              <div class="icon">
                <i class="fa fa-user-times"></i>
              </div>
              <a href="#" class="small-box-footer">Mas info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
          </div>
        <?php endif; ?>

        <!-- ./col -->
        <?php if (in_array('Ver panel de administrador', $_SESSION["permisos"])) : ?>
          <!-- Small boxes (Stat box) -->
          <div class="pull-left" id="box_main_left">
            <?php
            include('cfg/conexion.php');
            $sqlPacientes = ("SELECT r.Id_rol, p.id, p.estatus FROM persona p 
              JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona 
              JOIN rol r ON dpr.Id_rol = r.Id_rol
              HAVING r.Id_rol = 3 AND p.estatus = 1 ORDER BY id ASC");
            $queryData   = mysqli_query($conexion, $sqlPacientes);
            $total_pacientes_adultos = mysqli_num_rows($queryData);
            ?>
            <!-- small box -->
            <div class="small-box bg-royalblue">
              <div class="inner">
                <h3><?php echo $total_pacientes_adultos; ?></h3>

                <p>Total de Pacientes</p>
              </div>
              <div class="icon">
              </div>
              <a href="#" class="small-box-footer">Mas info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
          </div>

          <div class="pull-right" id="box_main_right">
            <?php
            include('cfg/conexion.php');
            $sqlPatologias = ("SELECT * FROM patologias WHERE estatus = 1 ORDER BY Id_patologia ASC");
            $queryData   = mysqli_query($conexion, $sqlPatologias);
            $total_patologias = mysqli_num_rows($queryData);
            ?>
            <!-- small box -->
            <div class="small-box bg-crimson">
              <div class="inner">
                <h3><?php echo $total_patologias; ?></h3>

                <p>Patologias</p>
              </div>
              <div class="icon">
                <i class="fa fa-user-times"></i>
              </div>
              <a href="pages/php/salud_patologias_listado.php" class="small-box-footer">Mas info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <div class="pull-left" id="box_main_left">
            <?php
            include('cfg/conexion.php');
            $sqlUsuarios = ("SELECT r.Id_rol, p.id, p.estatus FROM persona p 
                JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona 
                JOIN rol r ON dpr.Id_rol = r.Id_rol
                HAVING r.Id_rol IN (1, 2, 6, 7, 8) AND p.estatus IN (1, 2) ORDER BY id ASC");
            $queryData   = mysqli_query($conexion, $sqlUsuarios);
            $total_usuarios = mysqli_num_rows($queryData);
            ?>
            <!-- small box -->
            <div class="small-box bg-yellow">
              <div class="inner">
                <h3><?php echo $total_usuarios; ?></h3>

                <p>Usuarios Disponibles</p>
              </div>
              <div class="icon">
                <i class="fa fa-user"></i>
              </div>
              <a href="pages/php/cfg_usuario_listado.php" class="small-box-footer">Mas info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
          </div>

          <div class="pull-right" id="box_main_right">
            <?php
            include('cfg/conexion.php');
            $sqlMedicos = ("SELECT r.Id_rol, p.id, p.estatus FROM persona p 
                JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona 
                JOIN rol r ON dpr.Id_rol = r.Id_rol
                HAVING r.Id_rol = 4 AND p.estatus = 1 ORDER BY id ASC");
            $queryData   = mysqli_query($conexion, $sqlMedicos);
            $total_medicos = mysqli_num_rows($queryData);
            ?>
            <!-- small box -->
            <div class="small-box bg-primary" style="background-color: purple;">
              <div class="inner" id="consultas_hoy">
                <h3><?php echo $total_medicos; ?></h3>

                <p>Medicos</p>
              </div>
              <div class="icon">
                <i class="fa fa-user-times"></i>
              </div>
              <a href="pages/php/rh_medico_listado.php" class="small-box-footer">Mas info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
          </div>

          <div class="pull-left" id="box_main_left">
            <?php
            include('cfg/conexion.php');
            $sqlMedicamentos = ("SELECT * FROM medicamento WHERE estatus = 1 ORDER BY Id_medicamento ASC");
            $queryData   = mysqli_query($conexion, $sqlMedicamentos);
            $total_medicamentos = mysqli_num_rows($queryData);
            ?>
            <!-- small box -->
            <div class="small-box bg-green">
              <div class="inner">
                <h3><?php echo $total_medicamentos; ?></h3>

                <p>Medicamentos del Catalogo</p>
              </div>
              <div class="icon">
                <i class="fa fa-user-plus"></i>
              </div>
              <a href="pages/php/farmacia_medicamentos_listado.php" class="small-box-footer">Mas info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
          </div>

          <div class="pull-right" id="box_main_right">
            <?php
            include('cfg/conexion.php');
            $sqlRepresentantes = ("SELECT r.Id_rol, p.id, p.estatus FROM persona p 
            JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona 
            JOIN rol r ON dpr.Id_rol = r.Id_rol
            HAVING r.Id_rol = 5 AND p.estatus = 1 ORDER BY id ASC");
            $queryData   = mysqli_query($conexion, $sqlRepresentantes);
            $total_representantes = mysqli_num_rows($queryData);
            ?>
            <!-- small box -->
            <div class="small-box bg-primary">
              <div class="inner">
                <h3><?php echo $total_representantes; ?></h3>

                <p>Representantes</p>
              </div>
              <div class="icon">
                <i class="fa fa-user-plus"></i>
              </div>
              <a href="pages/php/representantes_listado.php" class="small-box-footer">Mas info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
          </div>
        <?php endif; ?>

        <?php if (in_array('Ver panel de visitante', $_SESSION["permisos"])) : ?>

          <div class="pull-left" id="box_main_left">
            <?php
            include('cfg/conexion.php');
            $sqlPacientesAdultos = ("SELECT r.Id_rol, p.id, p.fecha_nacimiento, p.estatus
            FROM persona p 
            JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona 
            JOIN rol r ON dpr.Id_rol = r.Id_rol
            WHERE r.Id_rol = 3 AND TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) >= 18 AND p.estatus = 1 ORDER BY p.id ASC");
            $queryData   = mysqli_query($conexion, $sqlPacientesAdultos);
            $total_pacientes_adultos = mysqli_num_rows($queryData);
            ?>
            <!-- small box -->
            <div class="small-box bg-royalblue">
              <div class="inner">
                <h3><?php echo $total_pacientes_adultos; ?></h3>

                <p>Total de Pacientes Adultos</p>
              </div>
              <div class="icon">
              </div>
              <a href="pages/php/pacientes_listado.php" class="small-box-footer">Mas info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
          </div>

          <div class="pull-right" id="box_main_right">
            <?php
            include('cfg/conexion.php');
            $sqlPacientesMenores = ("SELECT
            p_menor.id AS id,
            p_menor.fecha_nacimiento AS fecha_nacimiento,
            TIMESTAMPDIFF(YEAR, p_menor.fecha_nacimiento, CURDATE()) AS edad,
            p_menor.estatus
            FROM
            persona p_menor
            JOIN
            detalle_persona_rol dpr ON p_menor.Id = dpr.Id_persona
            JOIN
            detalle_paciente_menor dp ON p_menor.Id = dp.Id_persona
            JOIN
            persona p_rep ON dp.Id_representante = p_rep.Id
            WHERE
            dpr.Id_rol = 3 AND TIMESTAMPDIFF(YEAR, p_menor.fecha_nacimiento, CURDATE()) < 18 AND p_menor.estatus = 1
            ORDER BY id ASC");
            $queryData   = mysqli_query($conexion, $sqlPacientesMenores);
            $total_pacientes_menores = mysqli_num_rows($queryData);
            ?>
            <!-- small box -->
            <div class="small-box bg-primary">
              <div class="inner">
                <h3><?php echo $total_pacientes_menores; ?></h3>

                <p>Total de Pacientes Menores de Edad</p>
              </div>
              <div class="icon">
              </div>
              <a href="pages/php/pacientes_menores_listado.php" class="small-box-footer">Mas info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
          </div>

          <div class="pull-left" id="box_main_left">
            <?php
            include('cfg/conexion.php');
            $sqlPatologias = ("SELECT * FROM patologias WHERE estatus = 1 ORDER BY Id_patologia ASC");
            $queryData   = mysqli_query($conexion, $sqlPatologias);
            $total_patologias = mysqli_num_rows($queryData);
            ?>
            <!-- small box -->
            <div class="small-box bg-crimson">
              <div class="inner">
                <h3><?php echo $total_patologias; ?></h3>

                <p>Patologias</p>
              </div>
              <div class="icon">
                <i class="fa fa-user-times"></i>
              </div>
              <a href="pages/php/salud_patologias_listado.php" class="small-box-footer">Mas info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
          </div>

          <div class="pull-right" id="box_main_right">
            <?php
            include('cfg/conexion.php');
            $sqlMedicos = ("SELECT r.Id_rol, p.id, p.estatus FROM persona p 
                JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona 
                JOIN rol r ON dpr.Id_rol = r.Id_rol
                HAVING r.Id_rol = 4 AND p.estatus = 1 ORDER BY id ASC");
            $queryData   = mysqli_query($conexion, $sqlMedicos);
            $total_medicos = mysqli_num_rows($queryData);
            ?>
            <!-- small box -->
            <div class="small-box bg-primary" style="background-color: purple;">
              <div class="inner" id="consultas_hoy">
                <h3><?php echo $total_medicos; ?></h3>

                <p>Medicos</p>
              </div>
              <div class="icon">
                <i class="fa fa-user-times"></i>
              </div>
              <a href="pages/php/rh_medico_listado.php" class="small-box-footer">Mas info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
          </div>

          <div class="pull-left" id="box_main_left">
            <?php
            include('cfg/conexion.php');
            $sqlRepresentantes = ("SELECT r.Id_rol, p.id, p.estatus FROM persona p 
            JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona 
            JOIN rol r ON dpr.Id_rol = r.Id_rol
            HAVING r.Id_rol = 5 AND p.estatus = 1 ORDER BY id ASC");
            $queryData   = mysqli_query($conexion, $sqlRepresentantes);
            $total_representantes = mysqli_num_rows($queryData);
            ?>
            <!-- small box -->
            <div class="small-box bg-yellow">
              <div class="inner">
                <h3><?php echo $total_representantes; ?></h3>

                <p>Representantes</p>
              </div>
              <div class="icon">
                <i class="fa fa-user-plus"></i>
              </div>
              <a href="pages/php/representantes_listado.php" class="small-box-footer">Mas info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
          </div>

          <div class="pull-right" id="box_main_right">
            <?php
            include('cfg/conexion.php');
            $sqlSintomas = ("SELECT * FROM sintomas WHERE estatus = 1 ORDER BY Id_sintomas ASC");
            $queryData   = mysqli_query($conexion, $sqlSintomas);
            $total_sintomas = mysqli_num_rows($queryData);
            ?>
            <!-- small box -->
            <div class="small-box bg-green">
              <div class="inner">
                <h3><?php echo $total_sintomas; ?></h3>

                <p>Sintomas</p>
              </div>
              <div class="icon">
                <i class="fa fa-user-plus"></i>
              </div>
              <a href="pages/php/salud_sintomas_listado.php" class="small-box-footer">Mas info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </section>
</div>
</body>
<script>

</script>

</html>

<script src="plugins/jQuery/jquery-2.2.3.min.js"></script>
<script src="recursos/js/app.min.js"></script>

<!-- Bootstrap 3.3.6 -->
<script src="recursos/bootstrap/js/bootstrap.min.js"></script>
<script src="recursos/bootstrap/js/bootstrap.bundle.min.js"></script>

<?php
include('pages/php/includes/footer.php');
?>