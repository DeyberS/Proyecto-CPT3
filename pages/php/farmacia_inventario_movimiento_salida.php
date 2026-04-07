<?php
include("../../cfg/conexion.php");
$operacion_actual = isset($_GET['op']) ? $_GET['op'] : 'salida';
$id_pres_auto = isset($_GET['id_pres']) ? $_GET['id_pres'] : '';
$id_med_auto  = isset($_GET['id_med']) ? $_GET['id_med'] : '';
$cedula_auto  = isset($_GET['pac']) ? $_GET['pac'] : '';
$es_menor_auto = isset($_GET['menor']) ? $_GET['menor'] : 0;
$origen = isset($_GET['from']) ? $_GET['from'] : 'inventario';
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Inventario | Salida</title>
  <?php include('includes/headerNav2.php'); ?>

  <style>
    /* ANIMACIONES EXACTAS DEL FORMULARIO DE ENTRADA */
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
    #avisoModal,
    #modalSalidaGuardar,
    #modalBúsquedaAvanzadaMedicamento,
    #modalRegresarInventario {
      animation: fadeIn 0.4s ease-out;
    }

    .modal.out .modal-dialog {
      animation: fadeOut 0.4s ease-out;
    }

    .modal-open .modal-backdrop {
      opacity: 0.7 !important;
      animation: pulse-opacity 0.3s forwards;
    }

    /* ESTILO DE LA X (CLOSE BUTTON) */
    .modal-header .close {
      color: #000;
      filter: alpha(opacity=50);
      opacity: .5;
      text-shadow: 0 1px 0 #fff;
    }

    .modal-header .close:hover {
      opacity: .8;
    }

    .input-error {
      border: 2px solid crimson !important;
      box-shadow: 0 0 5px crimson;
    }

    .bg-crimson {
      background-color: #dc3545 !important;
      color: white !important;
    }

    .bg-success-custom {
      background-color: #00a65a !important;
      color: white !important;
    }

    /* Evitar que el contenido empuje elementos del sidebar */
    .content-wrapper {
      min-height: 100vh !important;
      /* Asegura que el contenedor tenga altura completa */
      overflow-y: auto;
    }

    /* Si el reloj está en el sidebar, aseguramos que el sidebar sea fijo */
    .main-sidebar {
      position: fixed !important;
      height: 100%;
    }

    #detalles_vinculo input[readonly] {
      box-shadow: none;
      cursor: default;
    }

    /* Ajuste para que las secciones dinámicas no afecten el layout global */
    .seccion-enfasis {
      clear: both;
      /* Evita interferencia con floats */
      display: block;
      width: 100%;
    }

    .bg-externo {
      background-color: #fff9eb;
      border: 1px solid #f39c12;
    }

    .text-green-bold {
      color: #00a65a;
      font-weight: bold;
      font-size: 1.2em;
    }

    /* Contenedor de botones interno */
    .form-actions {
      padding: 20px 0;
      border-top: 1px solid #f4f4f4;
      margin-top: 20px;
      text-align: right;
    }
  </style>
</head>

<body class="hold-transition skin-blue sidebar-mini">
  <div class="content-wrapper">
    <section class="content-header">
      <h1>Salida de Medicamentos</h1>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#tab_1" data-toggle="tab">Detalle de La Operación</a></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane active" id="tab_1" style="padding: 10px;">

                <form id="formularioSalida" method="POST" action="../../cfg/movimientos_inventario.php" novalidate autocomplete="off">
                  <input type="hidden" name="op" id="op" value="<?php echo $operacion_actual; ?>">

                  <div class="row">
                    <div class="col-sm-4">
                      <label>Motivo de Salida (*):</label>
                      <select name="observaciones" id="observaciones" class="form-control" required>
                        <option value="">-- Seleccione un motivo --</option>
                        <option value="Entrega a Paciente">Entrega a Paciente Interno</option>
                        <option value="Entrega a Representante">Entrega a Representante (Menor)</option>
                        <option value="Récipe Externo">Récipe Externo (Otro Hospital)</option>
                        <option value="Medicamento Vencido">Medicamento Vencido</option>
                        <option value="Desecho/Dañado">Desecho / Dañado</option>
                      </select>
                    </div>

                    <div class="col-sm-8">
                      <label>Medicamento (*):</label>
                      <div class="input-group">
                        <select id="Id_descripcion_medicamento" name="Id_descripcion_medicamento" class="form-control" required>
                          <option value="">--- Seleccione un medicamento ---</option>
                          <?php
                          $sql = "SELECT dm.Id, 
                          m.nombre_medicamento, 
                          p.nombre_presentacion,
                          GROUP_CONCAT(CONCAT(IFNULL(pa.nombre,''), ' ', IFNULL(dpm.cantidad_unidad_medida,''), IFNULL(um.unidad,'')) SEPARATOR ' + ') AS componentes
                          FROM descripcion_medicamento dm                     
                          INNER JOIN medicamento m ON dm.Id_medicamento = m.Id_medicamento 
                          INNER JOIN presentacion p ON dm.Id_presentacion = p.Id_presentacion 
                          INNER JOIN detalle_principio_medicamento dpm ON dm.Id = dpm.id_medicamento
                          INNER JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida
                          INNER JOIN principio_activo pa ON dpm.id_principio_activo = pa.Id_principio_activo
                          WHERE m.estatus = 1 AND dm.estatus = 1 
                          GROUP BY dm.Id
                          ORDER BY m.nombre_medicamento ASC";
                          $res = $conexion->query($sql);
                          while ($row = $res->fetch_assoc()) {
                            echo '<option value="' . $row['Id'] . '">' . htmlspecialchars($row['nombre_medicamento']) . " " . "(" . htmlspecialchars($row['componentes']) . ")" . " - " . "[" . htmlspecialchars($row['nombre_presentacion']) . "]" . '</option>';
                          }
                          ?>
                        </select>

                        <span class="input-group-btn">
                          <button class="btn btn-info" type="button" id="btnBuscarFiltrar" data-toggle="modal" data-target="#modalBúsquedaAvanzadaMedicamento" title="Búsqueda Avanzada de Medicamentos" style="height: 34px;">
                            <i><img src="../../recursos/imagenes/iconos/buscar.png" style="width:10px; height:10px;"></i>
                          </button>
                        </span>
                      </div>
                    </div>
                  </div>

                  <div class="row" style="margin-top:15px;">
                    <div class="col-sm-3">
                      <label>Existencia Total:</label>
                      <input type="text" id="existencia_actual" class="form-control" readonly disabled>
                    </div>
                    <div class="col-sm-3">
                      <label>Seleccione Lote (*):</label>
                      <select id="lista_lotes" name="lote" class="form-control" required>
                        <option value="">-- Seleccione un lote --</option>
                      </select>
                    </div>
                    <div class="col-sm-3">
                      <label>Cantidad a Retirar (*):</label>
                      <input type="text" id="cantidad" name="cantidad" class="form-control" required min="1" onkeypress="return isNumberKey(event)">
                    </div>
                    <div class="col-sm-3">
                      <label>Niveles (Mín/Máx):</label>
                      <input type="text" id="stock_info" class="form-control" readonly disabled>
                    </div>
                  </div>

                  <div id="seccion_interna" class="seccion-enfasis" style="display:none;">
                    <h4><i class="fa fa-search"></i> Vincular Receta del Sistema</h4>

                    <div class="row">
                      <div class="col-sm-3">
                        <label>Buscar por:</label>
                        <select id="metodo_busqueda" class="form-control">
                          <option value="cedula">Cédula</option>
                          <option value="nombre">Nombre / Apellido</option>
                        </select>
                      </div>
                      <div class="col-sm-4 form-group">
                        <label id="label_busqueda">Ingrese Los Datos (*):</label>
                        <div class="input-group">
                          <span class="input-group-btn" style="width: 25%;">
                            <select name="tipo_cedula" id="tipo_cedula" class="form-control" style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                              <option value="V">V-</option>
                              <option value="E">E-</option>
                              <option value="PN">PN-</option>
                            </select>
                          </span>

                          <input type="text" id="input_busqueda_paciente" name="cedula_paciente" class="form-control" placeholder="N° de Cédula" onkeypress="return validarEntradaDinamica(event)" style="border-top-left-radius: 0; border-bottom-left-radius: 0;" required>
                        </div>
                      </div>
                      <div class="col-sm-5">
                        <label>Resultados de Receta (*):</label>
                        <select name="id_prescripcion" id="id_prescripcion" class="form-control" required>
                          <option value="">-- Seleccione --</option>
                        </select>
                      </div>
                    </div>

                    <div id="detalles_vinculo" class="row" style="margin-top: 20px; display:none;">
                      <div class="col-sm-6">
                        <div class="well well-sm" style="border-left: 5px solid #00a65a; background: #00a65a14; padding: 15px;">
                          <h5 style="margin-top:0; color:#00a65a; font-weight:bold;"><i class="fa fa-user"></i> DATOS DEL PACIENTE</h5>
                          <input type="text" id="info_paciente_nom" class="form-control" readonly style="border:none; background:transparent; font-weight:bold; box-shadow:none; font-size:1.1em;">
                          <input type="text" id="info_paciente_ced" class="form-control" readonly style="border:none; background:transparent; font-weight:bold; box-shadow:none; font-size:1.1em;">
                        </div>
                      </div>

                      <div id="col_info_rep" class="col-sm-6" style="display:none;">
                        <div class="well well-sm" style="border-left: 5px solid #3c8dbc; background: #f4f8ff; padding: 15px;">
                          <h5 style="margin-top:0; color:#3c8dbc; font-weight:bold;"><i class="fa fa-users"></i> REPRESENTANTE LEGAL</h5>
                          <input type="text" id="info_rep_nom" class="form-control" readonly style="border:none; background:transparent; font-weight:bold; box-shadow:none; font-size:1.1em;">
                          <input type="text" id="info_rep_ced" class="form-control" readonly style="border:none; background:transparent; font-weight:bold; box-shadow:none; font-size:1.1em;">
                        </div>
                      </div>

                    </div>
                  </div>


                  <div id="seccion_externa" class="seccion-enfasis bg-externo" style="display:none; border-top: 3px solid #f39c12; margin-top: 30px; padding: 20px;">
                    <h4 style="margin-bottom: 20px;"><i class="fa fa-external-link"></i> Datos de Récipe Externo</h4>
                    <div class="row">
                      <div class="col-sm-3">
                        <label>Médico Externo (*):</label>
                        <input type="text" name="medico_externo" id="medico_externo" class="form-control" onkeypress="return isTextKey(event)" placeholder="Ej. Dr. Pérez">
                      </div>

                      <div class="col-sm-3">
                        <label>Nombre del Paciente (*):</label>
                        <input type="text" name="paciente_externo_nombre" id="paciente_externo_nombre" class="form-control" placeholder="Nombre completo" required>
                      </div>

                      <div class="col-sm-4">
                        <label>Cédula del Paciente (*):</label>
                        <div class="input-group">
                          <span class="input-group-btn" style="width: 20%;">
                            <select name="tipo_cedula_ext" class="form-control" style="margin-right: 10px;" required>
                              <option value="V-">V-</option>
                              <option value="E-">E-</option>
                              <option value="PN-">PN-</option>
                            </select>
                          </span>
                          <input type="number" name="paciente_externo_cedula" id="paciente_externo_cedula" class="form-control" min="1" max="20" placeholder="Solo números" required>
                        </div>
                      </div>

                      <div class="col-sm-2">
                        <label>N° Récipe (*):</label>
                        <input type="number" name="numero_recipe" id="numero_recipe" class="form-control" placeholder="000" min="1">
                      </div>
                    </div>
                  </div>

                  <div class="col-md-12" style="margin-top: 15px; margin-bottom: 15px;">
                    <label>Evidencia de Entrega (Comprobante):</label>
                    <div class="btn-group" style="width: 100%; display: flex;">
                      <button type="button" class="btn btn-info" style="flex: 1;" data-toggle="modal" data-target="#modalEvidencia" onclick="prepararCaptura('camara')">
                        <i class="fa fa-camera"></i> <span id="txt-btn-camara">Usar Cámara</span>
                      </button>
                      <button type="button" class="btn btn-warning" style="flex: 1;" data-toggle="modal" data-target="#modalEvidencia" onclick="prepararCaptura('archivo')">
                        <i class="fa fa-upload"></i> <span id="txt-btn-subir">Subir Archivo</span>
                      </button>
                    </div>
                    <div id="miniatura-evidencia" style="display:none; margin-top:10px; border: 1px solid #ddd; padding: 5px; border-radius: 4px; background: #f9f9f9; text-align: center;">
                      <span class="text-green-bold" style="font-size: 0.9em;"><i class="fa fa-check-circle"></i> Evidencia cargada correctamente</span>
                      <button type="button" class="btn btn-xs btn-danger pull-right" onclick="quitarImagenGlobal()"><i><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></i></button>
                    </div>
                    <input type="hidden" name="foto_base64" id="foto_base64">
                  </div>

                  <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="abrirModalRegresar">Regresar</button>
                    <button type="submit" class="btn btn-success">Guardar</button>
                  </div>
                </form>

              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <div class="modal" id="modalBúsquedaAvanzadaMedicamento" tabindex="-1" role="dialog" aria-labelledby="modalBúsquedaAvanzadaMedicamentoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h5 class="modal-title" id="modalBúsquedaAvanzadaMedicamentoLabel" style="color: white;">Filtros de Búsqueda Avanzada</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 1;">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="formFiltroModal">
            <p class="text-muted">Complete uno o varios campos para refinar su búsqueda.</p>
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="filtro_nombre">Nombre (o ID):</label>
                  <input type="text" id="filtro_nombre" name="filtro_nombre" class="form-control" placeholder="Escriba nombre o ID...">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="filtro_presentacion">Presentación:</label>
                  <select id="filtro_presentacion" name="filtro_presentacion" class="form-control">
                    <option value="">-- Todos --</option>
                    <?php
                    // Cargar tipos de medicamento dinámicamente
                    include("../../cfg/conexion.php"); // Asegura la conexión
                    $sql_tipos = "SELECT Id_presentacion, nombre_presentacion FROM presentacion WHERE estatus = 1 ORDER BY nombre_presentacion DESC";
                    $res_tipos = $conexion->query($sql_tipos);
                    while ($row_t = $res_tipos->fetch_assoc()) {
                      echo '<option value="' . $row_t['Id_presentacion'] . '">' . $row_t['nombre_presentacion'] . '</option>';
                    }
                    ?>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="filtro_principios">Principios activos (contiene):</label>
                  <input type="text" id="filtro_principios" name="filtro_principios" class="form-control" placeholder="Ej: Ibuprofeno">
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="filtro_contenido_neto">Contenido neto:</label>
                  <input type="text" id="filtro_contenido_neto" name="filtro_contenido_neto" class="form-control" placeholder="Ej: 20 Capsulas">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="filtro_via">Vía de aplicación:</label>
                  <select id="filtro_via" name="filtro_via" class="form-control">
                    <option value="">-- Todas --</option>
                    <option value="Oral">Oral</option>
                    <option value="Sublingual">Sublingual</option>
                    <option value="Rectal">Rectal</option>
                    <option value="Intravenosa">Intravenosa</option>
                    <option value="Intramuscular">Intramuscular</option>
                    <option value="Subcutanea">Subcutanea</option>
                    <option value="Intradermica">Intradermica</option>
                    <option value="Topica">Topica</option>
                    <option value="Transdermica">Transdermica</option>
                    <option value="Inhalatoria">Inhalatoria</option>
                    <option value="Oftalmica">Oftalmica</option>
                    <option value="Otica">Otica</option>
                    <option value="Nasal">Nasal</option>
                    <option value="Vaginal">Vaginal</option>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="filtro_almacenamiento">C. de almacenamiento:</label>
                  <select id="filtro_almacenamiento" name="filtro_almacenamiento" class="form-control">
                    <option value="">-- Todas --</option>
                    <option value="-25_a_-10">Congelacion (-25°C a -10°C)</option>
                    <option value="2_a_8">Refrigeracion (2°C a 8°C)</option>
                    <option value="8_a_15">Lugar Fresco (8°C a 15°C)</option>
                    <option value="15_a_25">Temperatura Ambiente (15°C a 25°C)</option>
                    <option value="max_30">Temperatura Maxima (30°C)</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="filtro_laboratorio">Laboratorio:</label>
                  <select id="filtro_laboratorio" name="filtro_laboratorio" class="form-control">
                    <option value="">-- Todos --</option>
                    <?php
                    // Cargar laboratorios dinámicamente
                    $sql_labs = "SELECT Id_laboratorio, nombre_laboratorio FROM laboratorio WHERE estatus = 1 ORDER BY nombre_laboratorio ASC";
                    $res_labs = $conexion->query($sql_labs);
                    while ($row_l = $res_labs->fetch_assoc()) {
                      echo '<option value="' . $row_l['Id_laboratorio'] . '">' . $row_l['nombre_laboratorio'] . '</option>';
                    }
                    ?>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="filtro_excipientes">Excipientes (contiene):</label>
                  <input type="text" id="filtro_excipientes" name="filtro_excipientes" class="form-control" placeholder="Escriba texto de excipientes...">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="filtro_barcode">Código de barras:</label>
                  <input type="text" id="filtro_barcode" name="filtro_barcode" class="form-control" placeholder="Escriba código de barras exacto...">
                </div>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-warning" id="btnLimpiarFiltros">Limpiar Filtros</button>
          <button type="button" class="btn btn-primary" id="btnAplicarFiltros">Aplicar Filtros</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalEvidencia" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h4 class="modal-title" style="color:white;"><i class="fa fa-file-image-o"></i> Gestionar Evidencia</h4>
        </div>
        <div class="modal-body text-center">

          <div id="modal-placeholder" style="padding: 40px 20px; border: 2px dashed #ddd; border-radius: 10px; color: #999;">
            <i class="fa fa-cloud-upload" style="font-size: 50px; margin-bottom: 15px;"></i>
            <h4 id="placeholder-texto">Cargando interfaz...</h4>
            <p>Haga clic en los botones de la parte inferior para capturar o subir la imagen.</p>
          </div>

          <div id="modal-seccion-camara" style="display:none;">
            <video id="video-modal" width="100%" style="max-width: 450px; border-radius: 8px; background: #000;" autoplay></video>
            <canvas id="canvas-modal" style="display:none;"></canvas>
            <div style="margin-top:10px;">
              <button type="button" id="btn-capturar-modal" class="btn btn-success"><i class="fa fa-camera"></i> Tomar Foto</button>
            </div>
          </div>

          <input type="file" id="input-archivo-modal" accept="image/*" style="display:none;">

          <div id="modal-contenedor-previa" style="display:none; margin-top:10px;">
            <img id="foto-previa-modal" src="" style="max-width: 100%; max-height: 400px; border: 3px solid #3c8dbc; border-radius: 5px;">
            <p class="text-muted" style="margin-top:5px;">¿Desea utilizar esta imagen?</p>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary" id="btn-aceptar-evidencia" style="display:none;">Confirmar y Usar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalVisualizarFoto" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header bg-navy">
          <button type="button" class="close" data-dismiss="modal" style="color:white; opacity:1;">&times;</button>
          <h4 class="modal-title" style="color:white;"><i class="fa fa-eye"></i> Vista Previa de Evidencia</h4>
        </div>
        <div class="modal-body text-center">
          <img id="img-vista-grande" src="" style="width: 100%; border-radius: 5px; box-shadow: 0 0 15px rgba(0,0,0,0.2);">
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="avisoModal" tabindex="-1" role="dialog" aria-labelledby="avisoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <h5 class="modal-title" id="avisoModalLabel" style="color: white;">Aviso de Validación</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
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

  <div class="modal" id="avisoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h5 class="modal-title" style="color:white;">Aviso de Validación</h5>
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

  <div class="modal" id="modalSalidaGuardar" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-success-custom">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h5 class="modal-title" style="color:white;">Confirmación de Guardado</h5>
        </div>
        <div class="modal-body">
          <p>¿Está seguro de que desea guardar la información para esta salida del inventario?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-success" id="confirmarGuardadoFinal">Guardar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modalRegresarInventario" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-crimson">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h5 class="modal-title" style="color:white;">Confirmación de Regreso</h5>
        </div>
        <div class="modal-body">
          <p>Al hacer clic en "Abandonar Formulario", perderá todos los datos no guardados. ¿Desea continuar?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-second" data-dismiss="modal">Cancelar</button>
          <a href="<?php echo ($origen === 'prescripciones') ? 'farmacia_prescripciones_listado.php' : 'farmacia_inventario_listado.php'; ?>" class="btn btn-danger">Abandonar Formulario</a>
        </div>
      </div>
    </div>
  </div>

  <?php include('includes/footer.php'); ?>

  <script>
    function isNumberKey(evt) {
      var charCode = (evt.which) ? evt.which : evt.keyCode;
      return !(charCode > 31 && (charCode < 48 || charCode > 57));
    }

    function isTextKey(evt) {
      var charCode = (evt.which) ? evt.which : evt.keyCode;
      return ((charCode > 64 && charCode < 91) || (charCode > 96 && charCode < 123) || charCode == 32 || charCode == 241 || charCode == 209);
    }

    function validarEntradaDinamica(evt) {
      return ($('#metodo_busqueda').val() === 'cedula') ? isNumberKey(evt) : isTextKey(evt);
    }

    const cantidadInput = $('#cantidad');

    cantidadInput.on('input', function() {
      this.value = this.value.replace(/[^0-9]/g, '');
      if (this.value && parseInt(this.value) <= 0) this.value = 1;
      checkFormValidity();
    });

    function mostrarAviso(mensaje) {
      $('#avisoTexto').html(mensaje);
      $('#avisoModal').modal('show');
    }
    // Variable global para el stream
    let streamModal = null;

    // Función para preparar el modal según el botón pulsado
    // 1. Modificar la función de preparar captura
    function prepararCaptura(tipo) {
      // Ocultar todo al inicio
      $('#modal-seccion-camara, #modal-contenedor-previa, #btn-aceptar-evidencia').hide();
      $('#modal-placeholder').show(); // Mostrar placeholder por defecto
      $('#foto-previa-modal').attr('src', '');

      if (tipo === 'camara') {
        $('#placeholder-texto').text('Iniciando Cámara...');
        const video = document.getElementById('video-modal');

        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
          navigator.mediaDevices.getUserMedia({
              video: {
                facingMode: "environment"
              }
            })
            .then(stream => {
              streamModal = stream;
              video.srcObject = stream;
              // Una vez que la cámara responde, ocultamos placeholder y mostramos video
              $('#modal-placeholder').hide();
              $('#modal-seccion-camara').show();
            })
            .catch(err => {
              console.error("Error camara:", err);
              $('#placeholder-texto').text('Error: Cámara no disponible');
              alert("Asegúrate de usar HTTPS y dar permisos de cámara.");
            });
        }
      } else {
        // Si es archivo
        $('#placeholder-texto').text('Seleccionando archivo...');
        $('#input-archivo-modal').click();

        // Si el usuario cancela la selección de archivo, el placeholder se queda con este texto:
        setTimeout(() => {
          $('#placeholder-texto').text('Esperando archivo...');
        }, 1000);
      }
    }

    // 2. Modificar el evento de cambio de archivo
    $('#input-archivo-modal').on('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
          $('#modal-placeholder').hide(); // Ocultar placeholder
          $('#foto-previa-modal').attr('src', event.target.result);
          $('#modal-contenedor-previa, #btn-aceptar-evidencia').show();
        };
        reader.readAsDataURL(file);
      }
    });

    // 3. NUEVO: Lógica para ver la imagen al hacer clic en "Evidencia cargada"
    $(document).on('click', '#miniatura-evidencia', function(e) {
      // Evitamos que se dispare si se hace clic en el botón de borrar (basura)
      if ($(e.target).closest('.btn-danger').length) return;

      const foto = $('#foto_base64').val();
      if (foto) {
        $('#img-vista-grande').attr('src', foto);
        $('#modalVisualizarFoto').modal('show');
      }
    });

    // 4. Mejorar el cursor para indicar que es clickeable
    $('#miniatura-evidencia').css('cursor', 'pointer').attr('title', 'Haga clic para ampliar');

    // Capturar foto desde el video del modal
    $('#btn-capturar-modal').on('click', function() {
      const video = document.getElementById('video-modal');
      const canvas = document.getElementById('canvas-modal');
      const context = canvas.getContext('2d');
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      context.drawImage(video, 0, 0, canvas.width, canvas.height);

      const dataURL = canvas.toDataURL('image/jpeg', 0.8);
      $('#foto-previa-modal').attr('src', dataURL);
      $('#modal-seccion-camara').hide();
      $('#modal-contenedor-previa, #btn-aceptar-evidencia').show();

      if (streamModal) {
        streamModal.getTracks().forEach(t => t.stop());
      }
    });

    // Confirmar imagen en el modal
    $('#btn-aceptar-evidencia').on('click', function() {
      const imgBase64 = $('#foto-previa-modal').attr('src');
      $('#foto_base64').val(imgBase64);

      // Cambiar textos de los botones principales
      $('#txt-btn-camara').text('Volver a Tomar');
      $('#txt-btn-subir').text('Cambiar Archivo');
      $('#miniatura-evidencia').fadeIn();

      $('#modalEvidencia').modal('hide');
    });

    // Función para limpiar todo
    function quitarImagenGlobal() {
      $('#foto_base64').val('');
      $('#txt-btn-camara').text('Usar Cámara');
      $('#txt-btn-subir').text('Subir Archivo');
      $('#miniatura-evidencia').hide();
      $('#input-archivo-modal').val('');
    }

    // Asegurar que la cámara se apague al cerrar el modal
    $('#modalEvidencia').on('hidden.bs.modal', function() {
      if (streamModal) {
        streamModal.getTracks().forEach(t => t.stop());
        streamModal = null;
      }
    });

    const medicamentoSelectPrincipal = $('#Id_descripcion_medicamento');

    // 1. Lógica para botón Limpiar Filtros dentro del Modal
    $('#btnLimpiarFiltros').on('click', function() {
      // Resetea el formulario del modal (formFiltroModal)
      $('#formFiltroModal')[0].reset();
      // Opcionalmente, podrías ejecutar el filtrado vacío para cargar todos
    });

    // 2. Lógica principal: Clic en "Aplicar Filtros" dentro del Modal
    $('#btnAplicarFiltros').on('click', function() {
      // Serializar los datos del formulario del modal
      const datosFiltro = $('#formFiltroModal').serialize();

      // Mostrar un indicador de carga en el botón si es necesario

      // Realizar la llamada AJAX al nuevo backend
      $.ajax({
        url: '../../cfg/ajax/filtrar_medicamentos_completo.php', // El nuevo backend
        type: 'POST',
        data: datosFiltro, // Envía los valores de los filtros del modal
        dataType: 'json',
        success: function(response) {
          // 1. Limpiar el select principal
          medicamentoSelectPrincipal.empty();

          // 2. Añadir la opción inicial por defecto
          medicamentoSelectPrincipal.append('<option value="">--- Seleccione un Medicamento ---</option>');

          // 3. Repoblar el select con los nuevos resultados filtrados
          if (response.length > 0) {
            // Iterar sobre el array de medicamentos devuelto por PHP
            response.forEach(function(item) {
              // Crear el nuevo <option value="id_desc">nombre completo descriptivo</option>
              medicamentoSelectPrincipal.append('<option value="' + item.id_desc + '">' + item.nombre_completo + '</option>');
            });

            // Opcionalmente: Si hay un solo resultado, seleccionarlo automáticamente
            /* if (response.length === 1) {
                medicamentoSelectPrincipal.val(response[0].id_desc).trigger('change');
            } */
          } else {
            // Si no hay resultados
            medicamentoSelectPrincipal.append('<option value="" disabled>🛑 No se encontraron medicamentos que coincidan con los filtros aplicados.</option>');
          }

          // 4. Cerrar el modal animadamente usando tu lógica existente
          $('#modalBúsquedaAvanzadaMedicamento').removeClass('in').addClass('out');
          setTimeout(function() {
            $('#modalBúsquedaAvanzadaMedicamento').modal('hide');
            $('#modalBúsquedaAvanzadaMedicamento').removeClass('out');
          }, 400);

          // 5. Opcional: Mostrar un aviso si hay resultados
          /* if (response.length > 0) {
             // alert('Se encontraron ' + response.length + ' medicamentos. Busque en la lista principal.');
          } */
        },
        error: function(jqXHR, textStatus, errorThrown) {
          console.error("Error en AJAX de filtrado avanzado: ", textStatus, errorThrown);
          // Mostrar aviso de error si tienes una función para ello
          // mostrarAviso('🛑 Error al intentar filtrar los medicamentos desde el modal.');
        }
      });
    });

    $(document).ready(function() {
      function cargarLotesMedicamento(id, callback = null) {
        $.ajax({
          url: '../../cfg/ajax/obtener_descripcion_medicamento.php',
          type: 'POST',
          data: {
            id: id
          },
          dataType: 'json',
          success: function(data) {
            $('#existencia_actual').val(data.existencia_actual); //
            $('#stock_info').val(data.stock_minimo + ' / ' + data.stock_maximo); //

            let $lotes = $('#lista_lotes').empty().append('<option value="">-- Seleccione --</option>'); //

            if (data.lotes && data.lotes.length > 0) {
              data.lotes.forEach((l, index) => {
                let textoDias = "";
                let estilo = "";

                // Lógica de etiquetas de vencimiento
                if (l.dias_restantes <= 0) {
                  textoDias = " - VENCIDO";
                  estilo = "color: red; font-weight: bold;";
                } else if (l.dias_restantes <= 30) {
                  textoDias = ` - (Próximo a vencer: ${l.dias_restantes} días)`;
                  estilo = "color: orange; font-weight: bold;";
                } else {
                  textoDias = ` - (Vence en: ${l.dias_restantes} días)`;
                }

                $lotes.append(
                  `<option value="${l.Id}" data-cant="${l.cantidad_actual}" style="${estilo}">
                ${l.lote} (Disp: ${l.cantidad_actual})${textoDias}
            </option>`
                ); //
              });

              // Seleccionar automáticamente el primer lote disponible (FEFO)
              // Como el SQL ya viene ordenado por fecha de vencimiento ASC, el índice 0 es el correcto
              $('#lista_lotes').val(data.lotes[0].Id).trigger('change'); //
            }

            if (callback) callback(); //
          }
        });
      }

      const idPresAuto = "<?php echo $id_pres_auto; ?>";
      const idMedAuto = "<?php echo $id_med_auto; ?>";
      const cedulaAuto = "<?php echo $cedula_auto; ?>";
      const esMenorAuto = "<?php echo $es_menor_auto; ?>";

      // Localiza la línea 245 y reemplaza ese bloque hasta antes de $('#id_prescripcion').on('change'...)
      if (idPresAuto !== "") {

        // 1. DETERMINAR Y FIJAR EL MOTIVO PRIMERO
        if (esMenorAuto == 1 || esMenorAuto === "1") {
          $('#observaciones').val('Entrega a Representante').trigger('change');
        } else {
          $('#observaciones').val('Entrega a Paciente').trigger('change');
        }

        // 2. CARGAR EL MEDICAMENTO
        $('#Id_descripcion_medicamento').val(idMedAuto).trigger('change');

        // 3. ESPERAR CARGA DE LOTES Y LUEGO BUSCAR AL PACIENTE
        cargarLotesMedicamento(idMedAuto, function() {
          // Forzamos la visibilidad de la sección de búsqueda
          $('#seccion_interna').show();

          // Escribimos la cédula y disparamos la búsqueda
          $('#input_busqueda_paciente').val(cedulaAuto).trigger('keyup');

          // 4. INTERVALO PARA SELECCIONAR LA RECETA CUANDO EL AJAX TERMINE
          const esperarPrescripcion = setInterval(function() {
            if ($('#id_prescripcion option').length > 1) {
              clearInterval(esperarPrescripcion);

              // Seleccionamos la receta y disparamos su evento change
              $('#id_prescripcion').val(idPresAuto).trigger('change');

              // FORZAR DESPLIEGUE DE LOS CUADROS DE DATOS (IMPORTANTE)
              $('#detalles_vinculo').stop().slideDown();
              if (esMenorAuto == 1) {
                $('#col_info_rep').show();
              }

              // 5. SELECCIONAR LOTE DISPONIBLE
              $('#lista_lotes option').each(function() {
                if (parseFloat($(this).data('cant')) > 0) {
                  $(this).prop('selected', true).trigger('change');
                  return false;
                }
              });

              $('#input_busqueda_paciente').prop('readonly', true);

              // 2. Para los <select>, readonly no funciona igual, así que bloqueamos clics y cambiamos estilo
              $('#observaciones, #Id_descripcion_medicamento, #metodo_busqueda, #tipo_cedula, #id_prescripcion, #cantidad')
                .css({
                  'pointer-events': 'none',
                  'background-color': '#eee',
                  'cursor': 'not-allowed'
                });
            }
          }, 100); // Un poco más de tiempo para estabilidad
        });
      }

      // 1. ASIGNACIÓN AUTOMÁTICA DE CANTIDAD 1 AL ELEGIR RECETA
      $('#id_prescripcion').on('change', function() {
        if ($(this).val() !== "") {
          $('#cantidad').val(1);
        }
      });

      $(document).ready(function() {
        const idPresAuto = "<?php echo $id_pres_auto; ?>";
        const esMenorAuto = "<?php echo $es_menor_auto; ?>";

        if (idPresAuto !== "") {
          // Forzamos el cambio de etiquetas ANTES de disparar el change para asegurar que la lógica de UI se ejecute
          if (esMenorAuto == 1 || esMenorAuto === "1") {
            $('#observaciones').val('Entrega a Representante');
            // Actualización manual de UI inmediata para evitar el bug de etiquetas
            $('#seccion_interna h4').html('<i class="fa fa-users"></i> Buscar por Datos del Representante');
            $('#label_busqueda').text('Ingrese Datos del Representante:');
          } else {
            $('#observaciones').val('Entrega a Paciente');
            $('#seccion_interna h4').html('<i class="fa fa-user"></i> Vincular Receta del Sistema');
            $('#label_busqueda').text('Ingrese Los Datos del Paciente:');
          }

          // Ahora sí disparamos el change para que el resto del sistema reaccione (como mostrar la sección interna)
          $('#observaciones').trigger('change');

          // El resto de tu lógica de carga automática...
          $('#Id_descripcion_medicamento').val("<?php echo $id_med_auto; ?>").trigger('change');
        }
      });

      // 2. ANIMACIÓN DE SALIDA DE MODALES
      $('.modal').on('click', '[data-dismiss="modal"]', function() {
        var $modal = $(this).closest('.modal');
        $modal.removeClass('in').addClass('out');
        setTimeout(function() {
          $modal.modal('hide').removeClass('out');
        }, 400);
      });

      $('#abrirModalRegresar').on('click', function() {
        $('#modalRegresarInventario').modal('show');
      });

      $('#id_prescripcion').on('change', function() {
        const selected = $(this).find('option:selected');
        const motivo = $('#observaciones').val();

        if ($(this).val() !== "" && $(this).val() !== null) {
          const tCedP = selected.attr('data-tipo-cedula-p');
          const cedP = selected.attr('data-cedula-p');
          // Llenar datos y mostrar
          $('#info_paciente_nom').val("Nombre: " + selected.data('paciente'));
          $('#info_paciente_ced').val("Cédula: " + tCedP + "-" + cedP);
          $('#cantidad').val(1);

          $('#detalles_vinculo').slideDown();

          if (motivo === 'Entrega a Representante') {
            const tCedR = selected.attr('data-tipo-cedula-r');
            const cedR = selected.attr('data-cedula-r');
            $('#info_rep_nom').val("Nombre: " + selected.data('representante'));
            $('#info_rep_ced').val("Cédula: " + tCedR + "-" + cedR);
            $('#col_info_rep').show();
          } else {
            $('#col_info_rep').hide();
          }
        } else {
          // Si no hay nada seleccionado, ocultar todo
          $('#detalles_vinculo').slideUp();
          $('#cantidad').val('');
        }
      });

      // Mostrar/Ocultar tipo de cédula según el método de búsqueda
      $('#metodo_busqueda').on('change', function() {
        const inputBusqueda = $('#input_busqueda_paciente');
        const grupoInput = inputBusqueda.closest('.input-group'); // El contenedor padre
        const contenedorTipoCedula = $('#tipo_cedula').parent(); // El span del selector

        if ($(this).val() === 'nombre') {
          // 1. Ocultar el selector
          contenedorTipoCedula.hide();

          // 2. Forzar al grupo a no comportarse como tabla y al input a expandirse
          grupoInput.css('display', 'block');
          inputBusqueda.css({
            'width': '100%',
            'border-top-left-radius': '4px',
            'border-bottom-left-radius': '4px'
          }).attr('placeholder', 'Ingrese nombre o apellido');
        } else {
          // 1. Mostrar el selector
          contenedorTipoCedula.show();

          // 2. Restaurar el comportamiento de Bootstrap (display: table)
          grupoInput.css('display', 'table');
          inputBusqueda.css({
            'width': '',
            'border-top-left-radius': '0',
            'border-bottom-left-radius': '0'
          }).attr('placeholder', 'N° de Cédula');
        }

        // Limpiar y disparar búsqueda
        inputBusqueda.val('').trigger('keyup');
      });

      // 3. LÓGICA DE SECCIONES Y VALIDACIÓN OBLIGATORIA
      $('#observaciones').on('change', function() {
        const val = $(this).val();

        // --- BLOQUE DE LIMPIEZA TOTAL ---
        $('.seccion-enfasis').slideUp(); // Oculta secciones de búsqueda
        $('#detalles_vinculo').hide(); // Oculta los recuadros de datos del paciente/representante
        $('#col_info_rep').hide(); // Asegura que la columna de representante se oculte

        // Limpiar valores de los inputs informativos
        $('#info_paciente_nom, #info_paciente_ced, #info_rep_nom, #info_rep_ced').val('');

        // Reset de campos obligatorios y cantidad
        $('#id_prescripcion, #medico_externo, #paciente_externo, #numero_recipe').prop('required', false);
        $('#cantidad').prop('readonly', false).val('').css('background-color', '#fff');

        // Limpiar busqueda y resultados previos
        $('#input_busqueda_paciente').val('');
        $('#id_prescripcion').html('<option value="">-- Seleccione una preescripcion --</option>');
        // ---------------------------------

        if (val === 'Entrega a Paciente' || val === 'Entrega a Representante') {
          if (val === 'Entrega a Representante') {
            $('#seccion_interna h4').html('<i class="fa fa-users"></i> Buscar por Datos del Representante');
            $('#label_busqueda').text('Ingrese Datos del Representante:');
          } else {
            $('#seccion_interna h4').html('<i class="fa fa-user"></i> Vincular Receta del Sistema');
            $('#label_busqueda').text('Ingrese Los Datos del Paciente:');
          }

          $('#seccion_interna').slideDown();
          $('#id_prescripcion').prop('required', true);
          $('#cantidad').prop('readonly', true).css('background-color', '#eee');

        } else if (val === 'Récipe Externo') {
          $('#seccion_externa').slideDown();
          $('#medico_externo, #paciente_externo, #numero_recipe').prop('required', true);
        }
      });

      // 4. VALIDACIÓN DE FORMULARIO
      $('#formularioSalida').on('submit', function(e) {
        e.preventDefault();
        $('.form-control').removeClass('input-error');
        let error = false;

        // Verifica todos los campos con required (incluyendo los dinámicos)
        $(this).find('[required]:visible').each(function() {
          if (!$(this).val()) {
            $(this).addClass('input-error');
            error = true;
          }
        });

        if (error) {
          $('#avisoTexto').text('⚠️ Por favor complete todos los campos marcados como obligatorios.');
          $('#avisoModal').modal('show');
          return;
        }

        // Dentro del submit...
        const motivo = $('#observaciones').val();
        const evidencia = $('#foto_base64').val();

        const motivosConEvidencia = ['Entrega a Paciente', 'Entrega a Representante', 'Récipe Externo'];

        if (motivosConEvidencia.includes(motivo)) {
          if (!evidencia || evidencia.trim() === "") {
            // Usamos la función mostrarAviso
            mostrarAviso('<strong>📸 Evidencia Obligatoria:</strong><br>Para este motivo de salida, debe capturar una foto o subir un archivo del comprobante.');
            return; // Detiene el envío
          }
        }

        const cant = parseFloat($('#cantidad').val());
        const disp = parseFloat($('#lista_lotes option:selected').data('cant') || 0);
        if (cant > disp) {
          $('#avisoTexto').text('🛑 Error: La cantidad solicitada supera el stock disponible en este lote.');
          $('#avisoModal').modal('show');
          return;
        }

        $('#modalSalidaGuardar').modal('show');
      });

      $('#confirmarGuardadoFinal').on('click', function() {
        $('#formularioSalida').off('submit').submit();
      });

      // AJAX - Obtener Lotes
      $('#Id_descripcion_medicamento').on('change', function() {
        const id = $(this).val();
        if (!id) return;

        cargarLotesMedicamento(id);
      });

      $('#observaciones').on('change', function() {
        const busquedaActual = $('#input_busqueda_paciente').val();
        if (busquedaActual.length > 0) {
          // Disparamos el evento keyup manualmente para actualizar los resultados con el nuevo filtro
          $('#input_busqueda_paciente').trigger('keyup');
        }
      });

      // AJAX - Búsqueda de pacientes/recetas
      // CÓDIGO CORREGIDO EN farmacia_inventario_movimiento_salida.php
      $('#input_busqueda_paciente').on('keyup', function() {
        const query = $(this).val();
        const medId = $('#Id_descripcion_medicamento').val();
        var tipoCed = $('#tipo_cedula').val(); // <-- Defines tipoCed aquí

        const esRepresentante = ($('#observaciones').val() === 'Entrega a Representante');

        if (query.length > 0 && medId) {
          $.ajax({
            url: '../../cfg/ajax/obtener_prescripciones.php',
            type: 'POST',
            data: {
              busqueda: query,
              tipo_cedula: tipoCed, // <-- ¡AQUÍ ESTABA EL ERROR! Cámbialo a tipoCed
              id_medicamento: medId,
              metodo: $('#metodo_busqueda').val(),
              es_menor: ($('#observaciones').val() === 'Entrega a Representante') ? 1 : 0
            },
            success: function(res) {
              $('#id_prescripcion').html(res);
            }
          });
        }
      });
      // Si cambia el tipo (V, E, PN), volver a buscar automáticamente
      $('#tipo_cedula').on('change', function() {
        $('#input_busqueda_paciente').trigger('keyup');
      });
    });
  </script>
</body>

</html>