<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>CPT3 | Inicio</title>
  <meta content="width=device-width, initial-scale=1" name="viewport">
  
  <style>
    /* --- Estilos Personalizados del Dashboard Profesional --- */
    .welcome-banner {
      background: linear-gradient(135deg, #0052d4, #4364f7, #6fb1fc);
      color: white;
      padding: 25px 30px;
      border-radius: 12px;
      margin-bottom: 30px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .welcome-banner h2 {
      margin: 0 0 5px 0;
      font-weight: 700;
      font-size: 28px;
    }
    .welcome-banner p {
      margin: 0;
      font-size: 15px;
      opacity: 0.9;
    }
    .modern-card {
      background-color: #2b3035; /* Color oscuro pero no negro */
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 25px;
      box-shadow: 0 6px 12px rgba(0,0,0,0.15);
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      height: 140px; /* Altura fija para uniformidad */
      justify-content: center;
    }
    .modern-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0,0,0,0.25);
    }
    .modern-card .card-content {
      display: flex;
      justify-content: space-between;
      align-items: center;
      z-index: 2;
    }
    .modern-card .info {
      color: #ffffff;
      flex: 1;
      padding-right: 15px;
    }
    .modern-card .info h3 {
      font-size: 32px;
      font-weight: 700;
      margin: 0 0 5px 0;
      color: #f8f9fa;
    }
    .modern-card .info p {
      font-size: 13px;
      color: #adb5bd;
      margin: 0;
      line-height: 1.3;
      font-weight: 500;
    }
    /* Cuadriculas para las imagenes/iconos */
    .modern-card .icon-box {
      width: 65px;
      height: 65px;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 28px;
      color: #ffffff;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
      flex-shrink: 0;
      z-index: 2;
    }
    .modern-card .card-footer {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      background: rgba(0, 0, 0, 0.2);
      padding: 5px 0;
      text-align: center;
      z-index: 2;
      transition: background 0.3s ease;
    }
    .modern-card .card-footer:hover {
      background: rgba(0, 0, 0, 0.4);
    }
    .modern-card .card-footer a {
      color: #00bcd4;
      font-size: 12px;
      font-weight: 600;
      text-decoration: none;
      display: block;
    }
    
    /* Fondos de colores para las cuadriculas (Iconos) */
    .bg-gradient-blue { background: linear-gradient(135deg, #36D1DC, #5B86E5); }
    .bg-gradient-green { background: linear-gradient(135deg, #11998e, #38ef7d); }
    .bg-gradient-orange { background: linear-gradient(135deg, #FF8008, #FFC837); }
    .bg-gradient-red { background: linear-gradient(135deg, #ED213A, #93291E); }
    .bg-gradient-purple { background: linear-gradient(135deg, #8E2DE2, #4A00E0); }
    .bg-gradient-teal { background: linear-gradient(135deg, #00c6ff, #0072ff); }
    .bg-gradient-pink { background: linear-gradient(135deg, #ec008c, #fc6767); }
    .bg-gradient-gray { background: linear-gradient(135deg, #606c88, #3f4c6b); }

    /* Ajustes especiales para tarjetas con listas (Top 2 especialidades) */
    .card-list {
      height: auto;
      min-height: 140px;
    }
    .card-list h3 { font-size: 22px !important; margin-bottom: 10px !important;}
    .card-list ul { margin:0; padding:0; font-size: 13px; color: #ced4da; }
    .card-list li { margin-bottom: 5px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 5px;}
  </style>
</head>

<?php
include('pages/php/includes/headerNav.php');
?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Panel De Control
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-home"></i>Inicio</a></li>
    </ol>
  </section>

  <section class="content">
    
    <div class="row">
      <div class="col-md-12">
        <div class="welcome-banner">
          <div>
            <h2>¡Bienvenido(a), <?php echo isset($_SESSION['nombre']) ? $_SESSION['nombre'] : 'Usuario'; ?>!</h2>
            <p>Aquí tienes un resumen actualizado del estado de la clínica.</p>
          </div>
          <div class="hidden-xs">
            <i class="fa fa-stethoscope" style="font-size: 50px; opacity: 0.8;"></i>
          </div>
        </div>
      </div>
    </div>

    <?php if (isset($_SESSION["permisos"])) : ?>
      <div class="row" id="t_user">

        <?php if (in_array('Ver panel de medicos', $_SESSION["permisos"])) : ?>

          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
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
            <div class="modern-card">
              <div class="card-content">
                <div class="info">
                  <h3><?php echo $total_pacientes_adultos; ?></h3>
                  <p>Pacientes Adultos</p>
                </div>
                <div class="icon-box bg-gradient-blue">
                  <i><img src="recursos/imagenes/iconos/filled/people/male_and_female@2x.png" style="width:40px; height:40px; filter:invert();"></i>
                </div>
              </div>
              <div class="card-footer">
                <a href="pages/php/pacientes_listado.php">Más info <i class="fa fa-arrow-circle-right"></i></a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <?php
            include('cfg/conexion.php');
            $sqlPacientesMenores = ("SELECT p_menor.id AS id, p_menor.fecha_nacimiento AS fecha_nacimiento, p_menor.estatus,
            TIMESTAMPDIFF(YEAR, p_menor.fecha_nacimiento, CURDATE()) AS edad
            FROM persona p_menor JOIN detalle_persona_rol dpr ON p_menor.Id = dpr.Id_persona
            JOIN detalle_paciente_menor dp ON p_menor.Id = dp.Id_persona
            JOIN persona p_rep ON dp.Id_representante = p_rep.Id
            WHERE dpr.Id_rol = 3 AND TIMESTAMPDIFF(YEAR, p_menor.fecha_nacimiento, CURDATE()) < 18 AND p_menor.estatus
            ORDER BY id ASC");
            $queryData   = mysqli_query($conexion, $sqlPacientesMenores);
            $total_pacientes_menores = mysqli_num_rows($queryData);
            ?>
            <div class="modern-card">
              <div class="card-content">
                <div class="info">
                  <h3><?php echo $total_pacientes_menores; ?></h3>
                  <p>Pacientes Menores</p>
                </div>
                <div class="icon-box bg-gradient-teal">
                  <i><img src="recursos/imagenes/iconos/filled/people/child_program@2x.png" style="width:40px; height:40px; filter:invert();"></i>
                </div>
              </div>
              <div class="card-footer">
                <a href="pages/php/pacientes_menores_listado.php">Más info <i class="fa fa-arrow-circle-right"></i></a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <?php
            include('cfg/conexion.php');
            $sqlAlergias = ("SELECT * FROM alergias_conocidas WHERE estatus = 1 ORDER BY Id_alergias_conocidas ASC");
            $queryData   = mysqli_query($conexion, $sqlAlergias);
            $total_alergias = mysqli_num_rows($queryData);
            ?>
            <div class="modern-card">
              <div class="card-content">
                <div class="info">
                  <h3><?php echo $total_alergias; ?></h3>
                  <p>Alergias</p>
                </div>
                <div class="icon-box bg-gradient-orange">
                  <i><img src="recursos/imagenes/iconos/filled/ppe/ppe-mask@2x.png" style="width:40px; height:40px; filter:invert();"></i>
                </div>
              </div>
              <div class="card-footer">
                <a href="pages/php/salud_alergias_listado.php">Más info <i class="fa fa-arrow-circle-right"></i></a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <?php
            include('cfg/conexion.php');
            $sqlPatologias = ("SELECT * FROM patologias WHERE estatus = 1 ORDER BY Id_patologia ASC");
            $queryData   = mysqli_query($conexion, $sqlPatologias);
            $total_patologias = mysqli_num_rows($queryData);
            ?>
            <div class="modern-card">
              <div class="card-content">
                <div class="info">
                  <h3><?php echo $total_patologias; ?></h3>
                  <p>Patologías</p>
                </div>
                <div class="icon-box bg-gradient-red">
                  <i><img src="recursos/imagenes/iconos/filled/body/bacteria@2x.png" style="width:40px; height:40px; filter:invert();"></i>
                </div>
              </div>
              <div class="card-footer">
                <a href="pages/php/patologias_listado.php">Más info <i class="fa fa-arrow-circle-right"></i></a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <?php
            $mysqli = new mysqli('localhost', 'root', '', 'cpt3db');
            if ($mysqli->connect_error) { die("Conexión fallida: " . $mysqli->connect_error); }
            $sql_citas = "SELECT COUNT(*) AS total FROM citas WHERE estatus = 1 AND DATE(fecha_cita) = CURDATE()";
            $resultado = $mysqli->query($sql_citas);
            $datos_iniciales = $resultado->fetch_assoc();
            $total_citas_hoy = number_format($datos_iniciales['total']);
            ?>
            <div class="modern-card">
              <div class="card-content">
                <div class="info">
                  <h3><?php echo $total_citas_hoy; ?></h3>
                  <p>Citas Para Hoy</p>
                </div>
                <div class="icon-box bg-gradient-purple">
                  <i><img src="recursos/imagenes/iconos/filled/objects/calendar@2x.png" style="width:40px; height:40px; filter:invert();"></i>
                </div>
              </div>
              <div class="card-footer">
                <a href="pages/php/citas_medicas_listado.php">Más info <i class="fa fa-arrow-circle-right"></i></a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <?php
            $mysqli = new mysqli('localhost', 'root', '', 'cpt3db');
            if ($mysqli->connect_error) { die("Conexión fallida: " . $mysqli->connect_error); }
            $sql_consulta = "SELECT COUNT(*) AS total FROM consulta WHERE estatus = 1 AND DATE(fecha_consulta) = CURDATE()";
            $resultado = $mysqli->query($sql_consulta);
            $datos_iniciales = $resultado->fetch_assoc();
            $total_consulta_hoy = number_format($datos_iniciales['total']);
            ?>
            <div class="modern-card">
              <div class="card-content">
                <div class="info">
                  <h3><?php echo $total_consulta_hoy; ?></h3>
                  <p>Consultas de Hoy</p>
                </div>
                <div class="icon-box bg-gradient-gray">
                  <i><img src="recursos/imagenes/iconos/filled/objects/health_worker_form@2x.png" style="width:40px; height:40px; filter:invert();"></i>
                </div>
              </div>
              <div class="card-footer">
                <a href="pages/php/consulta_listado.php">Más info <i class="fa fa-arrow-circle-right"></i></a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <?php
            include('cfg/conexion.php');
            $sqlNuevosSintomas = "SELECT COUNT(*) as total FROM sintomas WHERE estatus = 1";
            $querySintomas = mysqli_query($conexion, $sqlNuevosSintomas);
            $rowSintomas = mysqli_fetch_assoc($querySintomas);
            ?>
            <div class="modern-card">
              <div class="card-content">
                <div class="info">
                  <h3><?php echo $rowSintomas['total']; ?></h3>
                  <p>Síntomas Registrados</p>
                </div>
                <div class="icon-box bg-gradient-pink">
                  <i><img src="recursos/imagenes/iconos/filled/conditions/chills@2x.png" style="width:40px; height:40px; filter:invert();"></i>
                </div>
              </div>
              <div class="card-footer">
                <a href="pages/php/salud_sintomas_listado.php">Más info <i class="fa fa-arrow-circle-right"></i></a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <?php
            include('cfg/conexion.php');
            $sqlCitasMes = "SELECT COUNT(*) as total FROM citas WHERE MONTH(fecha_cita) = MONTH(CURDATE()) AND YEAR(fecha_cita) = YEAR(CURDATE())";
            $queryCitasMes = mysqli_query($conexion, $sqlCitasMes);
            $rowCitasMes = mysqli_fetch_assoc($queryCitasMes);
            ?>
            <div class="modern-card">
              <div class="card-content">
                <div class="info">
                  <h3><?php echo $rowCitasMes['total']; ?></h3>
                  <p>Citas Agendadas este Mes</p>
                </div>
                <div class="icon-box bg-gradient-green">
                  <i><img src="recursos/imagenes/iconos/filled/objects/register_book@2x.png" style="width:40px; height:40px; filter:invert();"></i>
                </div>
              </div>
              <div class="card-footer">
                <a href="pages/php/citas_medicas_listado.php">Más info <i class="fa fa-arrow-circle-right"></i></a>
              </div>
            </div>
          </div>

        <?php endif; ?>

        <?php if (in_array('Ver panel de farmaceutico', $_SESSION["permisos"])) : ?>

          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <?php
            include('cfg/conexion.php');
            $sqlEntradaMedicamentos = ("SELECT COUNT(*) AS total FROM detalle_inventario WHERE Id_TipoMovimiento = 1 AND fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY);");
            $queryData = mysqli_query($conexion, $sqlEntradaMedicamentos);
            $row = mysqli_fetch_assoc($queryData);
            $total_entrada_medicamentos = $row['total'];
            ?>
            <div class="modern-card">
              <div class="card-content">
                <div class="info">
                  <h3><?php echo $total_entrada_medicamentos; ?></h3>
                  <p>Ingresos (Últimos 7 días)</p>
                </div>
                <div class="icon-box bg-gradient-green">
                  <i><img src="recursos/imagenes/iconos/Entrada.png" style="width:40px; height:40px; filter:invert();"></i>
                </div>
              </div>
              <div class="card-footer"><a href="pages/php/farmacia_inventario_listado.php">Más info <i class="fa fa-arrow-circle-right"></i></a></div>
            </div>
          </div>

          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <?php
            include('cfg/conexion.php');
            $sqlSalidaMedicamentos = ("SELECT COUNT(*) AS total FROM detalle_inventario WHERE Id_TipoMovimiento = 2 AND fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY);");
            $queryData = mysqli_query($conexion, $sqlSalidaMedicamentos);
            $row = mysqli_fetch_assoc($queryData);
            $total_salida_medicamentos = $row['total'];
            ?>
            <div class="modern-card">
              <div class="card-content">
                <div class="info">
                  <h3><?php echo $total_salida_medicamentos ?></h3>
                  <p>Entregas (Últimos 7 días)</p>
                </div>
                <div class="icon-box bg-gradient-red">
                  <i><img src="recursos/imagenes/iconos/filled/objects/rdt_result_out_stock@2x.png" style="width:40px; height:40px; filter:invert();"></i>
                </div>
              </div>
              <div class="card-footer"><a href="pages/php/farmacia_inventario_listado.php">Más info <i class="fa fa-arrow-circle-right"></i></a></div>
            </div>
          </div>

          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <?php
            include('cfg/conexion.php');
            $sqlMedicamentos = ("SELECT * FROM medicamento WHERE estatus = 1 ORDER BY Id_medicamento ASC");
            $queryData = mysqli_query($conexion, $sqlMedicamentos);
            $total_medicamentos = mysqli_num_rows($queryData);
            ?>
            <div class="modern-card">
              <div class="card-content">
                <div class="info">
                  <h3><?php echo $total_medicamentos; ?></h3>
                  <p>Medicamentos en Catálogo</p>
                </div>
                <div class="icon-box bg-gradient-blue">
                  <i><img src="recursos/imagenes/iconos/filled/medications/medicines@2x.png" style="width:40px; height:40px; filter:invert();"></i>
                </div>
              </div>
              <div class="card-footer"><a href="pages/php/farmacia_medicamentos_listado.php">Más info <i class="fa fa-arrow-circle-right"></i></a></div>
            </div>
          </div>

          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <?php
            include('cfg/conexion.php');
            $sqlMedicamentosExistentes = ("SELECT COUNT(DISTINCT Id_descripcion_medicamento) AS total FROM medicamentos_detalle_inventario");
            $queryData = mysqli_query($conexion, $sqlMedicamentosExistentes);
            $row = mysqli_fetch_assoc($queryData);
            $total_medicamentos_existentes = $row['total'];
            ?>
            <div class="modern-card">
              <div class="card-content">
                <div class="info">
                  <h3><?php echo $total_medicamentos_existentes; ?></h3>
                  <p>Med. Existentes en Inventario</p>
                </div>
                <div class="icon-box bg-gradient-teal">
                  <i><img src="recursos/imagenes/iconos/filled/medications/blister_pills_round_x14@2x.png" style="width:40px; height:40px; filter:invert();"></i>
                </div>
              </div>
              <div class="card-footer"><a href="pages/php/farmacia_inventario_listado.php">Más info <i class="fa fa-arrow-circle-right"></i></a></div>
            </div>
          </div>

          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <?php
            include('cfg/conexion.php');
            $sqlMedicamentosStockBajo = ("SELECT COUNT(*) AS total FROM medicamentos_detalle_inventario WHERE Id <= Id_lote");
            $queryData = mysqli_query($conexion, $sqlMedicamentosStockBajo);
            $row = mysqli_fetch_assoc($queryData);
            $total_medicamentos_stock_bajo = $row['total'];
            ?>
            <div class="modern-card">
              <div class="card-content">
                <div class="info">
                  <h3><?php echo $total_medicamentos_stock_bajo; ?></h3>
                  <p>Medicamentos Stock Bajo</p>
                </div>
                <div class="icon-box bg-gradient-orange">
                  <i><img src="recursos/imagenes/iconos/filled/symbols/alert_triangle@2x.png" style="width:40px; height:40px; filter:invert();"></i>
                </div>
              </div>
              <div class="card-footer"><a href="pages/php/farmacia_inventario_listado.php">Más info <i class="fa fa-arrow-circle-right"></i></a></div>
            </div>
          </div>

          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <?php
            include('cfg/conexion.php');
            $sqlMedicamentosVencimiento = ("SELECT COUNT(*) AS total FROM lotes_medicamentos WHERE estatus = 1 AND fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY);");
            $queryData = mysqli_query($conexion, $sqlMedicamentosVencimiento);
            $row = mysqli_fetch_assoc($queryData);
            $total_medicamentos_vencimiento = $row['total'];
            ?>
            <div class="modern-card">
              <div class="card-content">
                <div class="info">
                  <h3><?php echo $total_medicamentos_vencimiento; ?></h3>
                  <p>Lotes por Vencer Pronto</p>
                </div>
                <div class="icon-box bg-gradient-red">
                  <i><img src="recursos/imagenes/iconos/filled/symbols/alert_circle@2x.png" style="width:40px; height:40px; filter:invert();"></i>
                </div>
              </div>
              <div class="card-footer"><a href="pages/php/farmacia_lotes_listado.php">Más info <i class="fa fa-arrow-circle-right"></i></a></div>
            </div>
          </div>

          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <?php
            include('cfg/conexion.php');
            $sqlProveedores = "SELECT COUNT(*) as total FROM proveedor WHERE estatus = 1";
            $queryProveedores = mysqli_query($conexion, $sqlProveedores);
            $rowProv = mysqli_fetch_assoc($queryProveedores);
            ?>
            <div class="modern-card">
              <div class="card-content">
                <div class="info">
                  <h3><?php echo $rowProv['total']; ?></h3>
                  <p>Proveedores</p>
                </div>
                <div class="icon-box bg-gradient-purple">
                  <i><img src="recursos/imagenes/iconos/filled/people/city_worker@2x.png" style="width:40px; height:40px; filter:invert();"></i>
                </div>
              </div>
              <div class="card-footer"><a href="pages/php/farmacia_proveedores_listado.php">Más info <i class="fa fa-arrow-circle-right"></i></a></div>
            </div>
          </div>

          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <?php
            include('cfg/conexion.php');
            $sqlLabs = "SELECT COUNT(*) as total FROM laboratorio WHERE estatus = 1";
            $queryLabs = mysqli_query($conexion, $sqlLabs);
            $rowLabs = mysqli_fetch_assoc($queryLabs);
            ?>
            <div class="modern-card">
              <div class="card-content">
                <div class="info">
                  <h3><?php echo $rowLabs['total']; ?></h3>
                  <p>Laboratorios</p>
                </div>
                <div class="icon-box bg-gradient-gray">
                  <i><img src="recursos/imagenes/iconos/filled/places/rural_post@2x.png" style="width:40px; height:40px; filter:invert();"></i>
                </div>
              </div>
              <div class="card-footer"><a href="pages/php/farmacia_laboratorio_listado.php">Más info <i class="fa fa-arrow-circle-right"></i></a></div>
            </div>
          </div>

        <?php endif; ?>

        <?php if (in_array('Ver panel de recursos humanos', $_SESSION["permisos"])) : ?>

          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <?php
            include('cfg/conexion.php');
            $sqlAreas = ("SELECT * FROM departamento WHERE estatus = 1 ORDER BY Id_departamento ASC");
            $queryData = mysqli_query($conexion, $sqlAreas);
            $total_areas = mysqli_num_rows($queryData);
            ?>
            <div class="modern-card">
              <div class="card-content">
                <div class="info">
                  <h3><?php echo $total_areas; ?></h3>
                  <p>Departamentos / Áreas</p>
                </div>
                <div class="icon-box bg-gradient-blue">
                  <i><img src="recursos/imagenes/iconos/filled/places/emergency_post@2x.png" style="width:40px; height:40px; filter:invert();"></i>
                </div>
              </div>
              <div class="card-footer"><a href="pages/php/rh_areas_listado.php">Más info <i class="fa fa-arrow-circle-right"></i></a></div>
            </div>
          </div>

          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <?php
            include('cfg/conexion.php');
            $sqlMedicos = ("SELECT r.Id_rol, p.id, p.estatus FROM persona p JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona JOIN rol r ON dpr.Id_rol = r.Id_rol HAVING r.Id_rol = 4 AND p.estatus = 1 ORDER BY id ASC");
            $queryData = mysqli_query($conexion, $sqlMedicos);
            $total_medicos = mysqli_num_rows($queryData);
            ?>
            <div class="modern-card">
              <div class="card-content">
                <div class="info">
                  <h3><?php echo $total_medicos; ?></h3>
                  <p>Médicos Activos</p>
                </div>
                <div class="icon-box bg-gradient-teal">
                  <i><img src="recursos/imagenes/iconos/filled/people/health_worker@2x.png" style="width:40px; height:40px; filter:invert();"></i>
                </div>
              </div>
              <div class="card-footer"><a href="pages/php/rh_medico_listado.php">Más info <i class="fa fa-arrow-circle-right"></i></a></div>
            </div>
          </div>

          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <?php
            include('cfg/conexion.php');
            $sqlEspecialidades = ("SELECT * FROM especialidad WHERE estatus = 1 ORDER BY Id_especialidad ASC");
            $queryData = mysqli_query($conexion, $sqlEspecialidades);
            $total_especialidades = mysqli_num_rows($queryData);
            ?>
            <div class="modern-card">
              <div class="card-content">
                <div class="info">
                  <h3><?php echo $total_especialidades; ?></h3>
                  <p>Especialidades</p>
                </div>
                <div class="icon-box bg-gradient-purple">
                  <i><img src="recursos/imagenes/iconos/filled/specialties/coronary_care_unit@2x.png" style="width:40px; height:40px; filter:invert();"></i>
                </div>
              </div>
              <div class="card-footer"><a href="pages/php/rh_especialidades_listado.php">Más info <i class="fa fa-arrow-circle-right"></i></a></div>
            </div>
          </div>

          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <?php
            include('cfg/conexion.php');
            $sql_vol = "SELECT p.nombre, p.apellido, COUNT(c.Id_consulta) as total FROM persona p JOIN consulta c ON p.id = c.Id_medico WHERE MONTH(c.fecha_consulta) = MONTH(CURDATE()) AND YEAR(c.fecha_consulta) = YEAR(CURDATE()) GROUP BY p.id ORDER BY total DESC LIMIT 1";
            $res_vol = $conexion->query($sql_vol);
            $data_vol = $res_vol->fetch_assoc();
            $medico_vol = isset($data_vol['nombre']) ? $data_vol['nombre']." ".$data_vol['apellido'] : "N/A";
            $total_vol = $data_vol['total'] ?? 0;
            ?>
            <div class="modern-card card-list">
              <div class="card-content">
                <div class="info">
                  <h3 style="font-size: 24px; margin-bottom: 5px;"><?php echo $total_vol; ?> <span style="font-size:12px; font-weight:normal;">Consultas</span></h3>
                  <p style="font-size: 11px;"><strong>Médico del mes:</strong><br><?php echo $medico_vol; ?></p>
                </div>
                <div class="icon-box bg-gradient-orange">
                  <i><img src="recursos/imagenes/iconos/filled/objects/book@2x.png" style="width:40px; height:40px; filter:invert();"></i>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <?php
            include('cfg/conexion.php');
            $sql_rend = "SELECT p.nombre, p.apellido, COUNT(c.Id_consulta) / COUNT(DISTINCT DATE(c.fecha_consulta)) as promedio FROM persona p JOIN consulta c ON p.id = c.Id_medico GROUP BY p.id ORDER BY promedio DESC LIMIT 1";
            $res_rend = $conexion->query($sql_rend);
            $data_rend = $res_rend->fetch_assoc();
            $medico_rend = isset($data_rend['nombre']) ? $data_rend['nombre'] : "N/A";
            $promedio_rend = round($data_rend['promedio'] ?? 0, 1);
            ?>
            <div class="modern-card card-list">
              <div class="card-content">
                <div class="info">
                  <h3 style="font-size: 24px; margin-bottom: 5px;"><?php echo $promedio_rend; ?> <span style="font-size:12px; font-weight:normal;">x Día</span></h3>
                  <p style="font-size: 11px;"><strong>Rendimiento Individual:</strong><br><?php echo $medico_rend; ?></p>
                </div>
                <div class="icon-box bg-gradient-gray">
                  <i><img src="recursos/imagenes/iconos/filled/objects/award_trophy@2x.png" style="width:40px; height:40px; filter:invert();"></i>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-6 col-md-8 col-sm-12 col-xs-12">
            <?php
            include('cfg/conexion.php');
            $sql_top_esp = "SELECT e.nombre_especialidad, COUNT(ci.Id_cita) as total_semanal FROM especialidad e JOIN citas ci ON e.Id_especialidad = ci.Id_especialidad WHERE WEEK(ci.fecha_cita, 1) = WEEK(CURDATE(), 1) AND YEAR(ci.fecha_cita) = YEAR(CURDATE()) GROUP BY e.Id_especialidad ORDER BY total_semanal DESC LIMIT 2";
            $res_top_esp = $conexion->query($sql_top_esp);
            ?>
            <div class="modern-card card-list">
              <div class="card-content" style="align-items: flex-start;">
                <div class="info" style="width: 100%;">
                  <h4 style="margin-top:0; color:#fff; font-weight:bold; font-size: 16px;">Top 2 Especialidades (Semana)</h4>
                  <ul style="list-style: none;">
                    <?php $cont = 1; if ($res_top_esp->num_rows > 0): while($row = $res_top_esp->fetch_assoc()): ?>
                      <li><strong><?php echo $cont; ?>.</strong> <?php echo $row['nombre_especialidad']; ?> 
                      <span class="pull-right" style="color: #00bcd4; font-weight:bold;"><?php echo $row['total_semanal']; ?> Citas</span></li>
                    <?php $cont++; endwhile; else: ?>
                      <li>No hay citas registradas en la semana</li>
                    <?php endif; ?>
                  </ul>
                </div>
                <div class="icon-box bg-gradient-green" style="margin-left: 15px;">
                  <i><img src="recursos/imagenes/iconos/filled/objects/register_book@2x.png" style="width:40px; height:40px; filter:invert();"></i>
                </div>
              </div>
            </div>
          </div>

        <?php endif; ?>

        <?php if (in_array('Ver panel de administrador', $_SESSION["permisos"])) : ?>

          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <?php
            include('cfg/conexion.php');
            $sqlPacientes = ("SELECT r.Id_rol, p.id, p.estatus FROM persona p JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona JOIN rol r ON dpr.Id_rol = r.Id_rol HAVING r.Id_rol = 3 AND p.estatus = 1 ORDER BY id ASC");
            $queryData = mysqli_query($conexion, $sqlPacientes);
            $total_pacientes_adultos = mysqli_num_rows($queryData);
            ?>
            <div class="modern-card">
              <div class="card-content">
                <div class="info">
                  <h3><?php echo $total_pacientes_adultos; ?></h3>
                  <p>Total Pacientes</p>
                </div>
                <div class="icon-box bg-gradient-blue">
                  <i><img src="recursos/imagenes/iconos/filled/people/man@2x.png" style="width:40px; height:40px; filter:invert();"></i>
                </div>
              </div>
              <div class="card-footer"><a href="pages/php/pacientes_listado.php">Más info <i class="fa fa-arrow-circle-right"></i></a></div>
            </div>
          </div>

          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <?php
            include('cfg/conexion.php');
            $sqlPatologias = ("SELECT * FROM patologias WHERE estatus = 1 ORDER BY Id_patologia ASC");
            $queryData = mysqli_query($conexion, $sqlPatologias);
            $total_patologias = mysqli_num_rows($queryData);
            ?>
            <div class="modern-card">
              <div class="card-content">
                <div class="info">
                  <h3><?php echo $total_patologias; ?></h3>
                  <p>Patologías</p>
                </div>
                <div class="icon-box bg-gradient-red">
                  <i><img src="recursos/imagenes/iconos/filled/body/bacteria@2x.png" style="width:40px; height:40px; filter:invert();"></i>
                </div>
              </div>
              <div class="card-footer"><a href="pages/php/salud_patologias_listado.php">Más info <i class="fa fa-arrow-circle-right"></i></a></div>
            </div>
          </div>

          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <?php
            include('cfg/conexion.php');
            $sqlUsuarios = ("SELECT r.Id_rol, p.id, p.estatus FROM persona p JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona JOIN rol r ON dpr.Id_rol = r.Id_rol HAVING r.Id_rol IN (1, 2, 6, 7, 8) AND p.estatus IN (1, 2) ORDER BY id ASC");
            $queryData = mysqli_query($conexion, $sqlUsuarios);
            $total_usuarios = mysqli_num_rows($queryData);
            ?>
            <div class="modern-card">
              <div class="card-content">
                <div class="info">
                  <h3><?php echo $total_usuarios; ?></h3>
                  <p>Usuarios del Sistema</p>
                </div>
                <div class="icon-box bg-gradient-orange">
                  <i><img src="recursos/imagenes/iconos/filled/people/people@2x.png" style="width:40px; height:40px; filter:invert();"></i>
                </div>
              </div>
              <div class="card-footer"><a href="pages/php/cfg_usuario_listado.php">Más info <i class="fa fa-arrow-circle-right"></i></a></div>
            </div>
          </div>

          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <?php
            include('cfg/conexion.php');
            $sqlMedicos = ("SELECT r.Id_rol, p.id, p.estatus FROM persona p JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona JOIN rol r ON dpr.Id_rol = r.Id_rol HAVING r.Id_rol = 4 AND p.estatus = 1 ORDER BY id ASC");
            $queryData = mysqli_query($conexion, $sqlMedicos);
            $total_medicos = mysqli_num_rows($queryData);
            ?>
            <div class="modern-card">
              <div class="card-content">
                <div class="info">
                  <h3><?php echo $total_medicos; ?></h3>
                  <p>Médicos Registrados</p>
                </div>
                <div class="icon-box bg-gradient-purple">
                 <i><img src="recursos/imagenes/iconos/filled/people/health_worker@2x.png" style="width:40px; height:40px; filter:invert();"></i>
                </div>
              </div>
              <div class="card-footer"><a href="pages/php/rh_medico_listado.php">Más info <i class="fa fa-arrow-circle-right"></i></a></div>
            </div>
          </div>

          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <?php
            include('cfg/conexion.php');
            $sqlMedicamentos = ("SELECT * FROM medicamento WHERE estatus = 1 ORDER BY Id_medicamento ASC");
            $queryData = mysqli_query($conexion, $sqlMedicamentos);
            $total_medicamentos = mysqli_num_rows($queryData);
            ?>
            <div class="modern-card">
              <div class="card-content">
                <div class="info">
                  <h3><?php echo $total_medicamentos; ?></h3>
                  <p>Catálogo de Medicinas</p>
                </div>
                <div class="icon-box bg-gradient-green">
                  <i><img src="recursos/imagenes/iconos/filled/medications/medicines@2x.png" style="width:40px; height:40px; filter:invert();"></i>
                </div>
              </div>
              <div class="card-footer"><a href="pages/php/farmacia_medicamentos_listado.php">Más info <i class="fa fa-arrow-circle-right"></i></a></div>
            </div>
          </div>

          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <?php
            include('cfg/conexion.php');
            $sqlRepresentantes = ("SELECT r.Id_rol, p.id, p.estatus FROM persona p JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona JOIN rol r ON dpr.Id_rol = r.Id_rol HAVING r.Id_rol = 5 AND p.estatus = 1 ORDER BY id ASC");
            $queryData = mysqli_query($conexion, $sqlRepresentantes);
            $total_representantes = mysqli_num_rows($queryData);
            ?>
            <div class="modern-card">
              <div class="card-content">
                <div class="info">
                  <h3><?php echo $total_representantes; ?></h3>
                  <p>Representantes</p>
                </div>
                <div class="icon-box bg-gradient-teal">
                  <i><img src="recursos/imagenes/iconos/filled/people/i_groups_perspective_crowd@2x.png" style="width:40px; height:40px; filter:invert();"></i>
                </div>
              </div>
              <div class="card-footer"><a href="pages/php/representantes_listado.php">Más info <i class="fa fa-arrow-circle-right"></i></a></div>
            </div>
          </div>

          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <?php
            include('cfg/conexion.php');
            $sqlConsultasAll = "SELECT COUNT(*) as total FROM consulta";
            $queryConsultasAll = mysqli_query($conexion, $sqlConsultasAll);
            $rowConsultas = mysqli_fetch_assoc($queryConsultasAll);
            ?>
            <div class="modern-card">
              <div class="card-content">
                <div class="info">
                  <h3><?php echo $rowConsultas['total']; ?></h3>
                  <p>Consultas (Histórico)</p>
                </div>
                <div class="icon-box bg-gradient-gray">
                  <i><img src="recursos/imagenes/iconos/filled/objects/health_worker_form@2x.png" style="width:40px; height:40px; filter:invert();"></i>
                </div>
              </div>
              <div class="card-footer"><a href="pages/php/consulta_listado.php">Más info <i class="fa fa-arrow-circle-right"></i></a></div>
            </div>
          </div>

          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <?php
            include('cfg/conexion.php');
            $sqlRoles = "SELECT COUNT(*) as total FROM rol WHERE estatus = 1";
            $queryRoles = mysqli_query($conexion, $sqlRoles);
            $rowRoles = mysqli_fetch_assoc($queryRoles);
            ?>
            <div class="modern-card">
              <div class="card-content">
                <div class="info">
                  <h3><?php echo $rowRoles['total']; ?></h3>
                  <p>Roles del Sistema</p>
                </div>
                <div class="icon-box bg-gradient-pink">
                  <i><img src="recursos/imagenes/iconos/filled/symbols/ui_settings@2x.png" style="width:40px; height:40px; filter:invert();"></i>
                </div>
              </div>
              <div class="card-footer"><a href="pages/php/cfg_roles_listado.php">Más info <i class="fa fa-arrow-circle-right"></i></a></div>
            </div>
          </div>

        <?php endif; ?>

        <?php if (in_array('Ver panel de visitante', $_SESSION["permisos"])) : ?>

          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <?php
            include('cfg/conexion.php');
            $sqlPacientesAdultos = ("SELECT r.Id_rol, p.id, p.fecha_nacimiento, p.estatus FROM persona p JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona JOIN rol r ON dpr.Id_rol = r.Id_rol WHERE r.Id_rol = 3 AND TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) >= 18 AND p.estatus = 1 ORDER BY p.id ASC");
            $queryData = mysqli_query($conexion, $sqlPacientesAdultos);
            $total_pacientes_adultos = mysqli_num_rows($queryData);
            ?>
            <div class="modern-card">
              <div class="card-content">
                <div class="info">
                  <h3><?php echo $total_pacientes_adultos; ?></h3>
                  <p>Pacientes Adultos</p>
                </div>
                <div class="icon-box bg-gradient-blue">
                  <i><img src="recursos/imagenes/iconos/filled/people/male_and_female@2x.png" style="width:40px; height:40px; filter:invert();"></i>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <?php
            include('cfg/conexion.php');
            $sqlPacientesMenores = ("SELECT p_menor.id AS id, p_menor.fecha_nacimiento AS fecha_nacimiento, TIMESTAMPDIFF(YEAR, p_menor.fecha_nacimiento, CURDATE()) AS edad, p_menor.estatus FROM persona p_menor JOIN detalle_persona_rol dpr ON p_menor.Id = dpr.Id_persona JOIN detalle_paciente_menor dp ON p_menor.Id = dp.Id_persona JOIN persona p_rep ON dp.Id_representante = p_rep.Id WHERE dpr.Id_rol = 3 AND TIMESTAMPDIFF(YEAR, p_menor.fecha_nacimiento, CURDATE()) < 18 AND p_menor.estatus = 1 ORDER BY id ASC");
            $queryData = mysqli_query($conexion, $sqlPacientesMenores);
            $total_pacientes_menores = mysqli_num_rows($queryData);
            ?>
            <div class="modern-card">
              <div class="card-content">
                <div class="info">
                  <h3><?php echo $total_pacientes_menores; ?></h3>
                  <p>Pacientes Menores</p>
                </div>
                <div class="icon-box bg-gradient-teal">
                  <i><img src="recursos/imagenes/iconos/filled/people/child_program@2x.png" style="width:40px; height:40px; filter:invert();"></i>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <?php
            include('cfg/conexion.php');
            $sqlPatologias = ("SELECT * FROM patologias WHERE estatus = 1 ORDER BY Id_patologia ASC");
            $queryData = mysqli_query($conexion, $sqlPatologias);
            $total_patologias = mysqli_num_rows($queryData);
            ?>
            <div class="modern-card">
              <div class="card-content">
                <div class="info">
                  <h3><?php echo $total_patologias; ?></h3>
                  <p>Patologías</p>
                </div>
                <div class="icon-box bg-gradient-red">
                  <i><img src="recursos/imagenes/iconos/filled/body/bacteria@2x.png" style="width:40px; height:40px; filter:invert();"></i>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <?php
            include('cfg/conexion.php');
            $sqlMedicos = ("SELECT r.Id_rol, p.id, p.estatus FROM persona p JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona JOIN rol r ON dpr.Id_rol = r.Id_rol HAVING r.Id_rol = 4 AND p.estatus = 1 ORDER BY id ASC");
            $queryData = mysqli_query($conexion, $sqlMedicos);
            $total_medicos = mysqli_num_rows($queryData);
            ?>
            <div class="modern-card">
              <div class="card-content">
                <div class="info">
                  <h3><?php echo $total_medicos; ?></h3>
                  <p>Médicos</p>
                </div>
                <div class="icon-box bg-gradient-purple">
                  <i><img src="recursos/imagenes/iconos/filled/people/health_worker@2x.png" style="width:40px; height:40px; filter:invert();"></i>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <?php
            include('cfg/conexion.php');
            $sqlRepresentantes = ("SELECT r.Id_rol, p.id, p.estatus FROM persona p JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona JOIN rol r ON dpr.Id_rol = r.Id_rol HAVING r.Id_rol = 5 AND p.estatus = 1 ORDER BY id ASC");
            $queryData = mysqli_query($conexion, $sqlRepresentantes);
            $total_representantes = mysqli_num_rows($queryData);
            ?>
            <div class="modern-card">
              <div class="card-content">
                <div class="info">
                  <h3><?php echo $total_representantes; ?></h3>
                  <p>Representantes</p>
                </div>
                <div class="icon-box bg-gradient-orange">
                  <i><img src="recursos/imagenes/iconos/filled/people/i_groups_perspective_crowd@2x.png" style="width:40px; height:40px; filter:invert();"></i>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <?php
            include('cfg/conexion.php');
            $sqlSintomas = ("SELECT * FROM sintomas WHERE estatus = 1 ORDER BY Id_sintomas ASC");
            $queryData = mysqli_query($conexion, $sqlSintomas);
            $total_sintomas = mysqli_num_rows($queryData);
            ?>
            <div class="modern-card">
              <div class="card-content">
                <div class="info">
                  <h3><?php echo $total_sintomas; ?></h3>
                  <p>Síntomas Registrados</p>
                </div>
                <div class="icon-box bg-gradient-pink">
                  <i><img src="recursos/imagenes/iconos/filled/conditions/chills@2x.png" style="width:40px; height:40px; filter:invert();"></i>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <?php
            include('cfg/conexion.php');
            $sqlEspecialidades = "SELECT COUNT(*) as total FROM especialidad WHERE estatus = 1";
            $queryEsp = mysqli_query($conexion, $sqlEspecialidades);
            $rowEsp = mysqli_fetch_assoc($queryEsp);
            ?>
            <div class="modern-card">
              <div class="card-content">
                <div class="info">
                  <h3><?php echo $rowEsp['total']; ?></h3>
                  <p>Especialidades Clínicas</p>
                </div>
                <div class="icon-box bg-gradient-green">
                <i><img src="recursos/imagenes/iconos/filled/specialties/coronary_care_unit@2x.png" style="width:40px; height:40px; filter:invert();"></i>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <?php
            include('cfg/conexion.php');
            $sqlDepartamentos = "SELECT COUNT(*) as total FROM departamento WHERE estatus = 1";
            $queryDept = mysqli_query($conexion, $sqlDepartamentos);
            $rowDept = mysqli_fetch_assoc($queryDept);
            ?>
            <div class="modern-card">
              <div class="card-content">
                <div class="info">
                  <h3><?php echo $rowDept['total']; ?></h3>
                  <p>Áreas de Atención</p>
                </div>
                <div class="icon-box bg-gradient-gray">
                  <i><img src="recursos/imagenes/iconos/filled/places/emergency_post@2x.png" style="width:40px; height:40px; filter:invert();"></i>
                </div>
              </div>
            </div>
          </div>

        <?php endif; ?>
      </div>
    <?php endif; ?>
  </section>
</div>
<script src="plugins/jQuery/jquery-2.2.3.min.js"></script>
<script src="recursos/js/app.min.js"></script>

<script src="recursos/bootstrap/js/bootstrap.min.js"></script>
<script src="recursos/bootstrap/js/bootstrap.bundle.min.js"></script>

<?php
include('pages/php/includes/footer.php');
?>
</html>