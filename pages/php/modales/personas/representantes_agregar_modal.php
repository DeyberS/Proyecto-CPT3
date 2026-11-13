<div class="modal fade" id="modalRepresentante" role="dialog" aria-labelledby="modalRepresentanteLabel">
    <div class="modal-dialog modal-lg" role="document" style="width: 90%; max-width: 1100px;">
        <div class="modal-content">

            <div class="modal-header" style="background-color: #3c8dbc; color: white;">
                <h4 class="modal-title" id="modalRepresentanteLabel">
                    <i class="fa fa-user-plus"></i> Añadir Nuevo Representante
                </h4>
            </div>
            <div class="modal-body" style="padding: 20px 25px; max-height: 75vh; overflow-y: auto;">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs" id="tabsRepresentante">
                        <li class="active" data-tab-name="info">
                            <a href="#rep_info" data-toggle="tab">Datos Personales</a>
                        </li>
                        <li data-tab-name="direccion" class="disabled-tab">
                            <a href="#rep_direccion" data-toggle="tab">Dirección de Residencia</a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- ================= PESTAÑA 1: DATOS PERSONALES ================= -->
                        <div class="tab-pane active" id="rep_info">
                            <form id="formularioRepresentante" novalidate>
                                <section style="margin-bottom: 20px;">

                                    <!-- FILA 1: Cédula + Nombre + Apellido -->
                                    <div class="row">
                                        <div class="col-md-4 col-sm-4 form-group">
                                            <p>Cédula (*):</p>
                                            <div class="input-group">
                                                <span class="input-group-addon" style="padding: 0; border: none;">
                                                    <select name="tipo_cedula" id="rep_tipo_cedula" class="form-control" style="width: 65px; height: 34px; font-weight: bold; background-color: #f4f4f4; border-right: none; border-top-right-radius: 0; border-bottom-right-radius: 0;" required>
                                                        <option value="V">V-</option>
                                                        <option value="E">E-</option>
                                                    </select>
                                                </span>
                                                <input type="text" class="form-control" name="cedula" id="rep_cedula" placeholder="N° de Cédula" maxlength="8" required>
                                            </div>
                                        </div>

                                        <div class="col-md-4 col-sm-4 form-group">
                                            <p>Nombre (*):</p>
                                            <input type="text" class="form-control" name="nombre" id="rep_nombre" placeholder="Nombre Del Representante" maxlength="100" required>
                                        </div>

                                        <div class="col-md-4 col-sm-4 form-group">
                                            <p>Apellido:</p>
                                            <input type="text" class="form-control" name="apellido" id="rep_apellido" placeholder="Apellido Del Representante" maxlength="100">
                                        </div>
                                    </div>

                                    <!-- FILA 2: Fecha nacimiento + Edad + Sexo + País -->
                                    <div class="row">
                                        <div class="col-md-4 col-sm-5 form-group">
                                            <p>Fecha de nacimiento / Edad (*):</p>
                                            <div class="input-group">
                                                <input type="date" class="form-control" id="rep_fechaN" name="fecha_nacimiento" required style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                                                <span class="input-group-addon" style="padding: 0; border: none;">
                                                    <input type="text" class="form-control" id="rep_edad" name="edad" readonly placeholder="Edad" style="width: 80px; height: 34px; text-align: center; background-color: #f4f4f4; font-weight: bold; border-left: none; border-top-left-radius: 0; border-bottom-left-radius: 0;">
                                                </span>
                                            </div>
                                        </div>

                                        <div class="col-md-4 col-sm-4 form-group">
                                            <p>Sexo (*):</p>
                                            <select name="genero" id="rep_genero" class="form-control" required>
                                                <option value="">--- Seleccione Un Género ---</option>
                                                <option value="Masculino">Masculino</option>
                                                <option value="Femenino">Femenino</option>
                                            </select>
                                        </div>

                                        <div class="col-md-4 col-sm-4 form-group">
                                            <p>País de nacimiento:</p>
                                            <select name="pais_nacimiento" id="rep_pais_nacimiento" class="form-control">
                                                <option value="">--- Seleccione un País ---</option>
                                                <?php
                                                if (!isset($conexion)) {
                                                    include('../../cfg/conexion.php');
                                                }
                                                $result_pais_rep = $conexion->query("SELECT Id_Pais, nombre_pais FROM pais");
                                                if ($result_pais_rep) {
                                                    while ($row_pais_rep = $result_pais_rep->fetch_assoc()) {
                                                        echo "<option value='{$row_pais_rep['Id_Pais']}'>{$row_pais_rep['nombre_pais']}</option>";
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- FILA 3: Estado + Municipio nacimiento + Teléfono -->
                                    <div class="row">
                                        <div class="col-md-4 col-sm-4 form-group">
                                            <p>Estado de nacimiento:</p>
                                            <select name="estado_nacimiento" id="rep_estado_nacimiento" class="form-control">
                                                <option value="">--- Seleccione Un Estado ---</option>
                                            </select>
                                        </div>

                                        <div class="col-md-4 col-sm-4 form-group">
                                            <p>Municipio de nacimiento:</p>
                                            <select name="municipio_nacimiento" id="rep_municipio_nacimiento" class="form-control">
                                                <option value="">--- Seleccione Un Municipio ---</option>
                                            </select>
                                        </div>

                                        <div class="col-md-4 col-sm-4 form-group">
                                            <p>Teléfono (*):</p>
                                            <div class="input-group">
                                                <span class="input-group-addon" style="padding: 0; border: none;">
                                                    <select name="prefijo" id="rep_prefijo_telefono" class="form-control" style="width: 80px; height: 34px; font-weight: bold; background-color: #f4f4f4; border-right: none; border-top-right-radius: 0; border-bottom-right-radius: 0;" required>
                                                        <?php
                                                        if (!isset($conexion)) {
                                                            include('../../cfg/conexion.php');
                                                        }
                                                        $sql_pref_rep = $conexion->query("SELECT * FROM prefijos_telefonos");
                                                        if ($sql_pref_rep) {
                                                            while ($res_pref_rep = $sql_pref_rep->fetch_assoc()) {
                                                                echo "<option value='" . $res_pref_rep["Id"] . "'>" . $res_pref_rep['prefijo'] . "</option>";
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </span>
                                                <input type="text" class="form-control" name="telefono" id="rep_telefono" placeholder="N° De Teléfono" minlength="7" maxlength="7" required>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- FILA 4: Correo -->
                                    <div class="row">
                                        <div class="col-md-8 col-sm-8 form-group">
                                            <p>Correo:</p>
                                            <input type="email" class="form-control" name="email" id="rep_email" placeholder="nombreapellido2@gmail.com">
                                        </div>
                                    </div>

                                </section>
                            </form>
                        </div>

                        <!-- ================= PESTAÑA 2: DIRECCIÓN ================= -->
                        <div class="tab-pane" id="rep_direccion">
                            <section style="margin-bottom: 20px;">

                                <!-- FILA 1: Estado + Municipio + Sector -->
                                <div class="row">
                                    <div class="col-md-4 col-sm-4 form-group">
                                        <p>Estado:</p>
                                        <select name="estado" id="rep_estado" class="form-control">
                                            <option value="">--- Seleccione Un Estado ---</option>
                                            <?php
                                            if (!isset($conexion)) {
                                                include('../../cfg/conexion.php');
                                            }
                                            $result_estado_rep = $conexion->query("SELECT Id_Estado, nombre_estado, Id_Pais FROM estado HAVING Id_Pais = 1");
                                            if ($result_estado_rep) {
                                                while ($row_estado_rep = $result_estado_rep->fetch_assoc()) {
                                                    echo "<option value='{$row_estado_rep['Id_Estado']}'>{$row_estado_rep['nombre_estado']}</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="col-md-4 col-sm-4 form-group">
                                        <p>Municipio:</p>
                                        <select name="municipio" id="rep_municipio" class="form-control">
                                            <option value="">--- Seleccione Un Municipio ---</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4 col-sm-4 form-group">
                                        <p>Sector:</p>
                                        <select name="sector" id="rep_sector" class="form-control">
                                            <option value="">--- Seleccione Un Sector ---</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- FILA 2: Avenida/Calle + Referencia + Tiempo -->
                                <div class="row">
                                    <div class="col-md-4 col-sm-6 form-group">
                                        <p>Avenida/Calle:</p>
                                        <input type="text" class="form-control" name="avenida_calle" id="rep_avenida_calle">
                                    </div>

                                    <div class="col-md-4 col-sm-6 form-group">
                                        <p>Punto de referencia:</p>
                                        <input type="text" class="form-control" name="referencia" id="rep_referencia">
                                    </div>

                                    <div class="col-md-4 col-sm-4 form-group">
                                        <p>Tiempo de residencia:</p>
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="tiempo_residencia" id="rep_tiempo_residencia" placeholder="Cantidad" style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                                            <span class="input-group-addon" style="padding: 0; border: none;">
                                                <select name="tiempo" id="rep_tiempo" class="form-control" style="width: 110px; height: 34px; background-color: #f4f4f4; border-left: none; border-top-left-radius: 0; border-bottom-left-radius: 0;">
                                                    <option value="dia/s">Dia/s</option>
                                                    <option value="semanas/s">Semanas/s</option>
                                                    <option value="meses/s">Meses/s</option>
                                                    <option value="años/s">Año/s</option>
                                                </select>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                            </section>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-second" id="btnCancelarRep" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-second" id="btnAtrasRep" style="display:none;">Atrás</button>
                <button type="button" class="btn btn-primary" id="btnSiguienteRep">Siguiente</button>
                <button type="button" class="btn btn-success" id="btnGuardarRep" style="display: none;">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- ================= CONFIRMACIÓN DE GUARDADO (nuevo) ================= -->
<div class="modal fade" id="modalGuardarRep" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-green">
                <h4 class="modal-title"><i class="fa fa-save"></i> Confirmación de Guardado</h4>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea guardar la información del nuevo representante?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-second" data-dismiss="modal">Regresar</button>
                <button type="button" class="btn btn-success" id="confirmarGuardadoFinalRep">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- ================= AVISO FALLBACK (si no hay mostrarAviso en el padre) ================= -->
<div class="modal fade" id="avisoModalRep" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-crimson" id="avisoModalRepHeader">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="avisoModalRepLabel"><i class="fa fa-warning"></i> Aviso Importante</h4>
            </div>
            <div class="modal-body">
                <p id="avisoTextoRep"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        'use strict';

        if (window.__modalRepresentanteInicializado) return;
        window.__modalRepresentanteInicializado = true;

        // ============================================================
        // FALLBACK DE AVISOS (por si el padre no define mostrarAviso)
        // ============================================================
        function avisoRep(mensaje, esExito) {
            var $h = $('#avisoModalRepHeader');
            if (esExito) {
                $h.removeClass('modal-header-danger').addClass('bg-green');
                $('#avisoModalRepLabel').html('<i class="fa fa-check"></i> Operación Exitosa');
            } else {
                $h.removeClass('bg-green').addClass('modal-header-danger');
                $('#avisoModalRepLabel').html('<i class="fa fa-warning"></i> Aviso Importante');
            }
            $('#avisoTextoRep').html(mensaje);
            $('#avisoModalRep').modal('show');
        }

        // ============================================================
        // BLOQUEO / LIMPIEZA DE CAMPOS SOLO TEXTO
        // ============================================================
        function bloquearNumeros(e) {
            const teclasPermitidas = ["Backspace", "Tab", "ArrowLeft", "ArrowRight", "Delete", "Shift"];
            if (teclasPermitidas.includes(e.key)) return;
            if (e.key >= "0" && e.key <= "9") e.preventDefault();
        }

        function limpiarNumeros(e) {
            e.target.value = e.target.value.replace(/[0-9]/g, "");
        }

        $(document).on('keydown input', '#rep_nombre, #rep_apellido', function(e) {
            if (e.type === 'keydown') bloquearNumeros(e);
            if (e.type === 'input') limpiarNumeros(e);
        });

        function soloNumeros(id, maxDigitos) {
            $(document).on('keydown', '#' + id, function(e) {
                const teclasPermitidas = ["Backspace", "Tab", "ArrowLeft", "ArrowRight", "Delete"];
                if (teclasPermitidas.includes(e.key)) return;
                if (e.key.toLowerCase() === "e") {
                    e.preventDefault();
                    return;
                }
                if (!/^[0-9]$/.test(e.key)) {
                    e.preventDefault();
                    return;
                }
                if (maxDigitos && this.value.length >= maxDigitos) e.preventDefault();
            });
            $(document).on('input', '#' + id, function() {
                this.value = this.value.replace(/[^0-9]/g, "").slice(0, maxDigitos || 999);
            });
        }
        soloNumeros('rep_cedula', 8);
        soloNumeros('rep_telefono', 7);
        soloNumeros('rep_tiempo_residencia');

        // ============================================================
        // FECHA MÁXIMA (18 años)
        // ============================================================
        function setMaxDateFor18YearsRep() {
            const today = new Date();
            today.setFullYear(today.getFullYear() - 18);
            const y = today.getFullYear();
            const m = String(today.getMonth() + 1).padStart(2, '0');
            const d = String(today.getDate()).padStart(2, '0');
            const input = document.getElementById('rep_fechaN');
            if (input) input.setAttribute('max', `${y}-${m}-${d}`);
        }

        // ============================================================
        // CÁLCULO DE EDAD
        // ============================================================
        function calcularEdadRep() {
            var $f = $('#rep_fechaN');
            var campo = $('#rep_edad');
            var val = $f.val();

            $f.removeClass('input-error');
            campo.val('');

            if (!val || val.trim() === '') return;

            if (!/^\d{4}-\d{2}-\d{2}$/.test(val)) {
                $f.addClass('input-error');
                avisoRep("⚠️ Formato de fecha inválido.");
                $f.val('');
                return;
            }

            var p = val.split('-');
            var anio = parseInt(p[0], 10);
            var mes = parseInt(p[1], 10) - 1;
            var dia = parseInt(p[2], 10);
            var fechaNac = new Date(anio, mes, dia);

            if (fechaNac.getFullYear() !== anio || fechaNac.getMonth() !== mes || fechaNac.getDate() !== dia) {
                $f.addClass('input-error');
                avisoRep("⚠️ La fecha ingresada no es válida.");
                $f.val('');
                return;
            }

            var hoy = new Date();
            hoy.setHours(0, 0, 0, 0);

            if (fechaNac > hoy) {
                avisoRep("⚠️ La fecha de nacimiento no puede ser posterior a la fecha actual.");
                $f.val("").addClass('input-error');
                return;
            }

            if (anio < 1900) {
                avisoRep("⚠️ La fecha de nacimiento no puede ser anterior a 1900.");
                $f.val("").addClass('input-error');
                return;
            }

            var edad = hoy.getFullYear() - fechaNac.getFullYear();
            var m = hoy.getMonth() - fechaNac.getMonth();
            if (m < 0 || (m === 0 && hoy.getDate() < fechaNac.getDate())) edad--;

            if (edad < 18) {
                avisoRep("⚠️ Este campo es para adultos. La edad calculada es " + edad + " años.");
                campo.val("");
                $f.addClass('input-error');
            } else if (edad > 120) {
                avisoRep("⚠️ La edad calculada (" + edad + " años) parece incorrecta. Verifique la fecha.");
                campo.val("");
                $f.addClass('input-error');
            } else {
                campo.val(edad);
                $f.removeClass('input-error');
            }
        }

        // ============================================================
        // DEPENDENCIAS GEOGRÁFICAS
        // ============================================================
        $(document).on('change', '#rep_pais_nacimiento', function() {
            const paisId = $(this).val();
            const $estado = $('#rep_estado_nacimiento');
            const $municipio = $('#rep_municipio_nacimiento');
            $estado.html('<option value="">--- Seleccione Un Estado ---</option>');
            $municipio.html('<option value="">--- Seleccione Un Municipio ---</option>');
            if (!paisId) return;
            $.ajax({
                url: '../../cfg/ajax/obtener_estados.php',
                method: 'GET',
                data: {
                    Id_Pais: paisId
                },
                dataType: 'json',
                cache: false,
                success: function(data) {
                    if (!Array.isArray(data)) return;
                    data.forEach(function(e) {
                        $estado.append('<option value="' + e.Id_Estado + '">' + e.nombre_estado + '</option>');
                    });
                }
            });
        });

        $(document).on('change', '#rep_estado_nacimiento', function() {
            const id = $(this).val();
            const $mun = $('#rep_municipio_nacimiento');
            $mun.html('<option value="">--- Seleccione Un Municipio ---</option>');
            if (!id) return;
            $.ajax({
                url: '../../cfg/ajax/obtener_municipios.php',
                method: 'GET',
                data: {
                    Id_Estado: id
                },
                dataType: 'json',
                cache: false,
                success: function(data) {
                    if (!Array.isArray(data)) return;
                    data.forEach(function(m) {
                        $mun.append('<option value="' + m.Id_Municipio + '">' + m.nombre_municipio + '</option>');
                    });
                }
            });
        });

        $(document).on('change', '#rep_estado', function() {
            const id = $(this).val();
            const $mun = $('#rep_municipio');
            const $sec = $('#rep_sector');
            $mun.html('<option value="">--- Seleccione Un Municipio ---</option>');
            $sec.html('<option value="">--- Seleccione Un Sector ---</option>');
            if (!id) return;
            $.ajax({
                url: '../../cfg/ajax/obtener_municipios.php',
                method: 'GET',
                data: {
                    Id_Estado: id
                },
                dataType: 'json',
                cache: false,
                success: function(data) {
                    if (!Array.isArray(data)) return;
                    data.forEach(function(m) {
                        $mun.append('<option value="' + m.Id_Municipio + '">' + m.nombre_municipio + '</option>');
                    });
                }
            });
        });

        $(document).on('change', '#rep_municipio', function() {
            const id = $(this).val();
            const $sec = $('#rep_sector');
            $sec.html('<option value="">--- Seleccione Un Sector ---</option>');
            if (!id) return;
            $.ajax({
                url: '../../cfg/ajax/obtener_sectores.php',
                method: 'GET',
                data: {
                    Id_Municipio: id
                },
                dataType: 'json',
                cache: false,
                success: function(data) {
                    if (!Array.isArray(data)) return;
                    data.forEach(function(s) {
                        $sec.append('<option value="' + s.Id_Sector + '">' + s.nombre_sector + '</option>');
                    });
                }
            });
        });

        // ============================================================
        // VERIFICACIÓN DE DUPLICADOS (cédula/email)
        // ============================================================
        function verificarDatosUnicosBDRep(tipo, cedula, email, idPersona) {
            return new Promise(function(resolve) {
                $.ajax({
                    url: '../../cfg/ajax/verificar_existencia_cedula.php',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        tipo_cedula: tipo,
                        cedula: cedula,
                        email: email,
                        id: idPersona || 0
                    },
                    success: function(r) {
                        resolve(r || {
                            existe_cedula: false,
                            existe_email: false
                        });
                    },
                    error: function() {
                        resolve({
                            existe_cedula: false,
                            existe_email: false
                        });
                    }
                });
            });
        }

        async function validarDatosUnicosRep() {
            const cedulaInput = document.getElementById('rep_cedula');
            const tipoSelect = document.getElementById('rep_tipo_cedula');
            const emailInput = document.getElementById('rep_email');
            const cedula = cedulaInput ? cedulaInput.value.trim() : "";
            const tipo = tipoSelect ? tipoSelect.value : "";
            const email = emailInput ? emailInput.value.trim() : "";

            let esValido = true;
            let errores = [];

            if (email !== "" && (email.indexOf('@') === -1 || email.indexOf('.') === -1)) {
                $(emailInput).addClass('input-error');
                errores.push("El campo Email debe tener un formato válido.");
                esValido = false;
            }

            if (cedula !== "" && !isNaN(parseInt(cedula))) {
                const n = parseInt(cedula);
                if (tipo === 'V' && n > 80000000) {
                    $(cedulaInput).addClass('input-error');
                    $(tipoSelect).addClass('input-error');
                    errores.push("Para tipo V-, la cédula no puede ser mayor a 80.000.000.");
                    esValido = false;
                } else if (tipo === 'E' && n < 80000000) {
                    $(cedulaInput).addClass('input-error');
                    $(tipoSelect).addClass('input-error');
                    errores.push("Para tipo E-, la cédula no puede ser menor a 80.000.000.");
                    esValido = false;
                }
            }

            if (!esValido) {
                avisoRep("🛑 <b>Errores de formato:</b><br>" + errores.join("<br>"));
                return false;
            }

            if (cedula !== "" || email !== "") {
                const bd = await verificarDatosUnicosBDRep(tipo, cedula, email, 0);
                if (bd.existe_cedula) {
                    $(cedulaInput).addClass('input-error');
                    $(tipoSelect).addClass('input-error');
                    errores.push(`El documento <b>${tipo}-${cedula}</b> ya está registrado.`);
                    esValido = false;
                }
                if (bd.existe_email) {
                    $(emailInput).addClass('input-error');
                    errores.push(`El correo <b>${email}</b> ya está en uso.`);
                    esValido = false;
                }
                if (!esValido) avisoRep("🛑 <b>Datos duplicados detectados:</b><br>" + errores.join("<br>"));
            }
            return esValido;
        }

        // ============================================================
        // VALIDACIÓN POR PESTAÑA
        // ============================================================
        async function validarPestanaRep(tabSelector) {
            let esValido = true;
            let errores = [];
            $(tabSelector).find('.input-error').removeClass('input-error');

            $(tabSelector).find('[required]').each(function() {
                const $i = $(this);
                const v = $i.val();
                if ($i.is(':visible') && !$i.prop('disabled') &&
                    (v === null || v.trim() === "" || v.includes('--- Seleccione'))) {
                    const label = $i.closest('.col-sm-1, .col-sm-2, .col-sm-3, .col-sm-4, .col-sm-5')
                        .find('p').first().text().replace('(*):', '').replace(':', '').trim();
                    errores.push("El campo <b>" + (label || 'obligatorio') + "</b> es obligatorio.");
                    $i.addClass('input-error');
                    esValido = false;
                }
            });

            if (tabSelector === '#rep_info') {
                const $c = $('#rep_cedula');
                if ($c.length && $c.val().trim() === '') {
                    $c.addClass('input-error');
                    if (!errores.some(e => e.includes('Cédula'))) errores.push("El campo <b>Cédula</b> es obligatorio.");
                    esValido = false;
                }
                const fecha = $('#rep_fechaN').val();
                if (!fecha || fecha.trim() === '') {
                    $('#rep_fechaN').addClass('input-error');
                    errores.push("El campo <b>Fecha de nacimiento</b> es obligatorio.");
                    esValido = false;
                } else {
                    calcularEdadRep();
                    const edad = parseInt($('#rep_edad').val());
                    if (isNaN(edad) || edad < 18 || edad > 120) {
                        if (!errores.includes("El representante debe ser mayor de 18 años.")) errores.push("El representante debe ser mayor de 18 años.");
                        $('#rep_fechaN').addClass('input-error');
                        esValido = false;
                    }
                }
                const ok = await validarDatosUnicosRep();
                if (!ok) esValido = false;
            }

            if (!esValido && errores.length > 0) {
                const unicos = errores.filter((v, i, a) => a.indexOf(v) === i);
                avisoRep('⚠️ <b>Errores de Formulario:</b><ul><li>' + unicos.join('</li><li>') + '</li></ul>');
            }

            return esValido;
        }

        // ============================================================
        // RECOLECTAR DATOS (incluye ambas pestañas)
        // ============================================================
        function recolectarDatosRepresentante() {
            var datos = {};
            $('#rep_info').find('input[name], select[name], textarea[name]').each(function() {
                if (this.name) datos[this.name] = $(this).val();
            });
            $('#rep_direccion').find('input[name], select[name], textarea[name]').each(function() {
                if (this.name) datos[this.name] = $(this).val();
            });
            return datos;
        }

        // ============================================================
        // NAVEGACIÓN DE PESTAÑAS
        // ============================================================
        function resetearBotonesNavegacionRep() {
            $('#btnSiguienteRep').show().prop('disabled', false).text('Siguiente');
            $('#btnGuardarRep').hide().prop('disabled', false).text('Guardar');
            $('#btnAtrasRep').hide();
            $('#btnCancelarRep').show();
        }

        $(document).on('click', '#btnSiguienteRep', async function() {
            const $btn = $(this);
            $btn.prop('disabled', true).text('Validando...');
            const ok = await validarPestanaRep('#rep_info');
            $btn.prop('disabled', false).text('Siguiente');

            if (ok) {
                $('#tabsRepresentante li').removeClass('active');
                $('#tabsRepresentante li[data-tab-name="direccion"]').removeClass('disabled-tab').addClass('active');
                $('#rep_info').removeClass('active in');
                $('#rep_direccion').addClass('active in');
                // Botones: ocultar Siguiente y Cancelar; mostrar Guardar y Atrás
                $('#btnSiguienteRep').hide();
                $('#btnCancelarRep').hide();
                $('#btnGuardarRep').show().prop('disabled', false).text('Guardar');
                $('#btnAtrasRep').show();
            }
        });

        // FIX 3: Volver de Dirección -> Datos Personales
        $(document).on('click', '#btnAtrasRep', function(e) {
            e.preventDefault();
            $('#tabsRepresentante li').removeClass('active');
            $('#tabsRepresentante li[data-tab-name="info"]').addClass('active');
            $('#rep_direccion').removeClass('active in');
            $('#rep_info').addClass('active in');
            resetearBotonesNavegacionRep();
        });

        $(document).on('click', '#btnGuardarRep', async function() {
            const $btn = $(this);
            $btn.prop('disabled', true).text('Validando...');
            const ok = await validarPestanaRep('#rep_direccion');
            $btn.prop('disabled', false).text('Guardar');
            if (!ok) return;
            $('#modalGuardarRep').modal('show');
        });

        // ============================================================
        // GUARDADO FINAL VÍA AJAX
        // ============================================================
        $(document).on('click', '#confirmarGuardadoFinalRep', function() {
            $('#modalGuardarRep').modal('hide');
            const $btn = $('#btnGuardarRep');
            $btn.prop('disabled', true).text('Guardando...');

            const datos = recolectarDatosRepresentante();

            $.ajax({
                url: '../../cfg/ajax/guardar_representante.php',
                method: 'POST',
                data: datos,
                dataType: 'json',
                success: function(res) {
                    if (res && res.success) {
                        // 1. Rellenar la fila del modal de gestión (si existe el helper)
                        if (typeof window.rellenarFilaConNuevoRepresentante === 'function') {
                            window.rellenarFilaConNuevoRepresentante(res);
                        }

                        // 2. Cerrar sub-modal
                        $('#modalRepresentante').modal('hide');
                        resetearBotonesNavegacionRep();

                        setTimeout(function() {
                            const msg = '<b>Representante creado exitosamente.</b><br>' +
                                res.nombre_completo + ' (' + res.tipo_cedula + '-' + res.cedula + ')';
                            if (typeof window.mostrarAvisoExito === 'function') {
                                window.mostrarAvisoExito(msg);
                            } else {
                                avisoRep(msg, true);
                            }
                        }, 400);
                    } else {
                        const msg = (res && res.error) ? res.error : 'Error desconocido al guardar.';
                        avisoRep('🛑 ' + msg);
                        $btn.prop('disabled', false).text('Guardar');
                    }
                },
                error: function(xhr) {
                    let msg = 'Error de conexión al guardar.';
                    try {
                        const r = JSON.parse(xhr.responseText);
                        if (r && r.error) msg = r.error;
                        else msg += '<br>' + xhr.responseText.substring(0, 300);
                    } catch (e) {}
                    avisoRep('🛑 ' + msg);
                    $btn.prop('disabled', false).text('Guardar');
                }
            });
        });

        function limpiarModalRepresentante() {
            const $form = $('#formularioRepresentante');
            if ($form.length) {
                $form[0].reset();
                $form.find('.input-error').removeClass('input-error');
            }
            $('#rep_edad').val('');
            $('#rep_estado_nacimiento').html('<option value="">--- Seleccione Un Estado ---</option>');
            $('#rep_municipio_nacimiento').html('<option value="">--- Seleccione Un Municipio ---</option>');
            $('#rep_municipio').html('<option value="">--- Seleccione Un Municipio ---</option>');
            $('#rep_sector').html('<option value="">--- Seleccione Un Sector ---</option>');

            $('#tabsRepresentante li').removeClass('active disabled-tab');
            $('#tabsRepresentante li[data-tab-name="info"]').addClass('active');
            $('#tabsRepresentante li[data-tab-name="direccion"]').addClass('disabled-tab');
            $('#rep_info').addClass('active in');
            $('#rep_direccion').removeClass('active in');

            // FIX 2: Resetear SIEMPRE los botones al abrir
            resetearBotonesNavegacionRep();
        }
        window.limpiarModalRepresentante = limpiarModalRepresentante;

        // ============================================================
        // EVENTOS DEL MODAL
        // ============================================================
        $(document).ready(function() {
            setMaxDateFor18YearsRep();

            $(document).on('change', '#rep_fechaN', calcularEdadRep);
            $(document).on('input', '#rep_fechaN', function() {
                var v = $(this).val();
                if (v && v.length === 10) calcularEdadRep();
            });
            $(document).on('blur', '#rep_fechaN', calcularEdadRep);

            function validarCedulaRep() {
                var $c = $('#rep_cedula'),
                    $t = $('#rep_tipo_cedula');
                var val = $c.val().trim();
                var tipo = $t.val();
                $c.removeClass('input-error');
                $t.removeClass('input-error');
                if (val === '') {
                    $c.addClass('input-error');
                    return false;
                }
                if (isNaN(parseInt(val))) {
                    $c.addClass('input-error');
                    return false;
                }
                const n = parseInt(val);
                if (tipo === 'V' && n > 80000000) {
                    $c.addClass('input-error');
                    $t.addClass('input-error');
                    avisoRep("⚠️ Para tipo <b>V-</b>, la cédula no puede ser mayor a <b>80.000.000</b>.");
                    return false;
                } else if (tipo === 'E' && n < 80000000) {
                    $c.addClass('input-error');
                    $t.addClass('input-error');
                    avisoRep("⚠️ Para tipo <b>E-</b>, la cédula no puede ser menor a <b>80.000.000</b>.");
                    return false;
                }
                return true;
            }

            $(document).on('blur', '#rep_cedula', validarCedulaRep);
            $(document).on('input', '#rep_cedula', function() {
                var v = $(this).val().trim();
                if (v === '') {
                    $(this).addClass('input-error');
                    return;
                }
                $(this).removeClass('input-error');
                var tipo = $('#rep_tipo_cedula').val();
                var n = parseInt(v);
                if (!isNaN(n)) {
                    if ((tipo === 'V' && n > 80000000) || (tipo === 'E' && n < 80000000)) {
                        $(this).addClass('input-error');
                        $('#rep_tipo_cedula').addClass('input-error');
                    } else {
                        $(this).removeClass('input-error');
                        $('#rep_tipo_cedula').removeClass('input-error');
                    }
                }
            });
            $(document).on('change', '#rep_tipo_cedula', validarCedulaRep);

            $('#modalRepresentante').on('show.bs.modal', function() {
                limpiarModalRepresentante(); // ya resetea botones dentro
                setMaxDateFor18YearsRep();
            });

            // FIX 2: al cerrar, dejar el botón Guardar listo por si se reabre
            $('#modalRepresentante').on('hidden.bs.modal', function() {
                resetearBotonesNavegacionRep();
                if (!$('.modal.in').length) {
                    $('body').removeClass('modal-open');
                    $('.modal-backdrop').remove();
                }
            });

            $('#btnCancelarRep').on('click', function() {
                $('#modalRepresentante').modal('hide');
            });
        });
    })();
</script>