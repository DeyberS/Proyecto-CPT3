<div class="modal fade" id="modalRepresentantes" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #3c8dbc; color: white;">
                <h4 class="modal-title"><i class="fa fa-users"></i> Gestionar Representantes</h4>
            </div>
            <div class="modal-body">
                <div id="contenedor_filas_representantes"></div>
                <div style="margin-top: 10px;">
                    <button type="button" class="btn btn-success btn-sm" id="add_fila_rep">
                        <i class="fa fa-plus"></i> Añadir otro
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" id="btn_abrir_nuevo_representante">
                        <i class="fa fa-user-plus"></i> Nuevo Representante
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-second" id="cancelar_rep_modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="guardar_rep_listo">Listo</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalBuscarRep" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #3c8dbc; color: white;">
                <h4 class="modal-title"><i class="fa fa-search"></i> Buscar Representante</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Buscar por Cédula o Nombre:</label>
                    <input type="text" id="inputBuscarRep" class="form-control" placeholder="Escriba la cédula o el nombre del representante..." autocomplete="off">
                </div>
                <div class="list-group" id="listaResultadosRep" style="margin-top: 10px; max-height: 300px; overflow-y: auto;">
                    <div class="text-muted text-center" style="padding: 15px;">Escriba para buscar representantes...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .fila-rep .select-tipo-rep {
        font-size: 13px;
    }

    .fila-rep .rep-info {
        font-size: 12px;
        color: #777;
        margin-top: 3px;
    }

    .list-group-item.seleccionar-rep {
        cursor: pointer;
        transition: background 0.2s;
    }

    .list-group-item.seleccionar-rep:hover {
        background-color: #e9f5ff;
        border-left: 3px solid #3c8dbc;
    }

    .list-group-item .rep-cedula {
        font-weight: bold;
        color: #3c8dbc;
        margin-right: 8px;
    }
</style>

<script>
    (function() {
        'use strict';
        if (window.__modalRepresentantesInicializado) return;
        window.__modalRepresentantesInicializado = true;

        window.agregarFilaRepresentante = function(id_guardado = "", tipo_guardado = "Legal", nombre_guardado = "", parentesco_guardado = "") {
            if (id_guardado === "null" || id_guardado === "undefined") id_guardado = "";
            if (nombre_guardado === "null" || nombre_guardado === "undefined") nombre_guardado = "";
            if (parentesco_guardado === "null" || parentesco_guardado === "undefined") parentesco_guardado = "";

            const opcionesParentesco = (window.PARENTESCOS_DISPONIBLES && Array.isArray(window.PARENTESCOS_DISPONIBLES)) ?
                window.PARENTESCOS_DISPONIBLES : [{
                        val: 'Madre',
                        txt: 'Madre'
                    },
                    {
                        val: 'Padre',
                        txt: 'Padre'
                    },
                    {
                        val: 'Tutor Legal',
                        txt: 'Tutor Legal'
                    },
                    {
                        val: 'Otro',
                        txt: 'Otro'
                    }
                ];

            let opcionesHtml = '<option value="">--- Parentesco ---</option>';
            opcionesParentesco.forEach(function(op) {
                const sel = (op.val === parentesco_guardado) ? 'selected' : '';
                opcionesHtml += `<option value="${op.val}" ${sel}>${op.txt}</option>`;
            });

            let htmlRep = `
            <div class="row fila-rep" style="margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px dashed #ddd;">
                <div class="col-sm-4">
                    <div class="input-group">
                        <input type="text" class="form-control input-nombre-rep"
                            placeholder="--- Seleccione un representante ---"
                            value="${nombre_guardado}" readonly
                            style="background:#fff; cursor:default;">
                        <input type="hidden" class="input-id-rep" value="${id_guardado}">
                        <span class="input-group-btn">
                            <button class="btn btn-info btn-search-rep" type="button" title="Buscar Representante">
                                <i><img src="../../recursos/imagenes/iconos/buscar.png" style="width:15px; height:15px;"></i>
                            </button>
                        </span>
                    </div>
                </div>
                <div class="col-sm-3">
                    <select class="form-control select-tipo-rep">
                        <option value="Legal" ${tipo_guardado === 'Legal' ? 'selected' : ''}>Representante Legal</option>
                        <option value="Suplente" ${tipo_guardado === 'Suplente' ? 'selected' : ''}>Representante Suplente</option>
                    </select>
                </div>
                <div class="col-sm-3">
                    <select class="form-control select-parentesco-rep">${opcionesHtml}</select>
                </div>
                <div class="col-sm-2">
                    <button type="button" class="btn btn-danger btn-remove-rep">
                        <i><img src="../../recursos/imagenes/iconos/Delete.png" style="width:15px; height:15px;"></i>
                    </button>
                </div>
            </div>`;
            $('#contenedor_filas_representantes').append(htmlRep);
        };

        function cargarRepresentantesGuardados() {
            $('#contenedor_filas_representantes').empty();
            const dataGuardada = $('#representantes_ids').val();
            if (dataGuardada && dataGuardada.trim() !== "" && dataGuardada !== "[]" && dataGuardada !== "null") {
                try {
                    const lista = JSON.parse(dataGuardada);
                    if (Array.isArray(lista) && lista.length > 0) {
                        lista.forEach(function(r) {
                            agregarFilaRepresentante(r.id, r.tipo || 'Legal', r.nombre || '', r.parentesco || '');
                        });
                        return;
                    }
                } catch (e) {
                    console.warn("Error parseando reps:", e);
                }
            }
            agregarFilaRepresentante();
        }

        window.rellenarFilaConNuevoRepresentante = function(res) {
            if (!res || !res.id) return;

            var $fila = window.filaRepDestinoParaNuevo;
            var nombreMostrar = res.nombre_completo || ((res.nombre || '') + ' ' + (res.apellido || '')).trim();

            if (!$fila || !$fila.length || !document.contains($fila[0])) {
                if (typeof window.agregarFilaRepresentante === 'function') {
                    window.agregarFilaRepresentante(res.id, 'Legal', nombreMostrar, '');
                }
            } else {
                $fila.find('.input-id-rep').val(res.id);
                $fila.find('.input-nombre-rep').val(nombreMostrar);
            }

            window.filaRepDestinoParaNuevo = null;
        };

        window.forzarRefrescoBusquedaRep = function() {
            $('#inputBuscarRep').val('');
            $('#listaResultadosRep').html('<div class="text-muted text-center" style="padding: 15px;">Escriba para buscar representantes...</div>');
        };

        $('#modalRepresentantes').on('show.bs.modal', function() {
            cargarRepresentantesGuardados();
            window.forzarRefrescoBusquedaRep();
        });

        let filaRepDestino = null;

        $(document).on('click', '.btn-search-rep', function(e) {
            e.preventDefault();
            e.stopPropagation();
            filaRepDestino = $(this).closest('.fila-rep');
            window.forzarRefrescoBusquedaRep();
            $('#modalBuscarRep').modal('show');
            $('#inputBuscarRep').focus();
        });

        let timerBusquedaRep = null;

        $('#inputBuscarRep').on('keyup', function() {
            const termino = $(this).val().trim();
            if (timerBusquedaRep) clearTimeout(timerBusquedaRep);
            if (termino.length < 2) {
                $('#listaResultadosRep').html('<div class="text-muted text-center" style="padding: 15px;">Escriba al menos 2 caracteres...</div>');
                return;
            }
            $('#listaResultadosRep').html('<div class="text-center" style="padding: 15px;"><i class="fa fa-spinner fa-spin"></i> Buscando...</div>');

            timerBusquedaRep = setTimeout(function() {
                $.ajax({
                    url: '../../cfg/ajax/buscar_representantes.php',
                    method: 'GET',
                    data: {
                        q: termino,
                        _t: Date.now()
                    },
                    dataType: 'json',
                    cache: false,
                    success: function(data) {
                        if (!data || data.length === 0) {
                            $('#listaResultadosRep').html('<div class="text-muted text-center" style="padding: 15px;">No se encontraron representantes con ese criterio.</div>');
                            return;
                        }
                        let html = '';
                        data.forEach(function(rep) {
                            const nombreCompleto = (rep.nombre + ' ' + (rep.apellido || '')).trim();
                            const cedula = rep.tipo_cedula + '-' + rep.cedula;
                            const email = rep.email ? '<small class="text-muted"> | ' + rep.email + '</small>' : '';
                            html += `
                        <a href="#" class="list-group-item seleccionar-rep"
                        data-id="${rep.id}"
                        data-nombre="${nombreCompleto}">
                            <span class="rep-cedula">${cedula}</span>
                            <strong>${nombreCompleto}</strong>
                            ${email}
                        </a>`;
                        });
                        $('#listaResultadosRep').html(html);
                    },
                    error: function() {
                        $('#listaResultadosRep').html('<div class="text-danger text-center" style="padding: 15px;">Error al buscar. Intente de nuevo.</div>');
                    }
                });
            }, 300);
        });

        $(document).on('click', '.seleccionar-rep', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            const nombre = $(this).data('nombre');

            let duplicado = false;
            $('.input-id-rep').each(function() {
                if ($(this).val() == id && $(this).closest('.fila-rep')[0] !== filaRepDestino[0]) {
                    duplicado = true;
                }
            });

            if (duplicado) {
                if (typeof mostrarAviso === 'function') mostrarAviso('⚠️ Este representante ya está asignado en otra fila.');
                else alert('⚠️ Este representante ya está asignado en otra fila.');
                return;
            }

            filaRepDestino.find('.input-id-rep').val(id);
            filaRepDestino.find('.input-nombre-rep').val(nombre);
            $('#modalBuscarRep').modal('hide');
        });

        $('#add_fila_rep').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if ($('.fila-rep').length === 0) {
                agregarFilaRepresentante();
                return;
            }
            const $ultima = $('.fila-rep').last();
            if (!$ultima.find('.input-id-rep').val()) {
                if (typeof window.mostrarAviso === 'function') window.mostrarAviso('⚠️ Debe seleccionar un representante en la fila actual antes de añadir otra.');
                else alert('⚠️ Debe seleccionar un representante en la fila actual antes de añadir otra.');
                return;
            }
            agregarFilaRepresentante();
        });

        $(document).on('click', '.btn-remove-rep', function() {
            $(this).closest('.fila-rep').remove();
            if ($('.fila-rep').length === 0) agregarFilaRepresentante();
        });

        $('#btn_abrir_nuevo_representante').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var $ultima = $('.fila-rep').last();
            if ($('.fila-rep').length === 0) {
                agregarFilaRepresentante();
                window.filaRepDestinoParaNuevo = $('.fila-rep').last();
            } else if (!$ultima.find('.input-id-rep').val()) {
                window.filaRepDestinoParaNuevo = $ultima;
            } else {
                agregarFilaRepresentante();
                window.filaRepDestinoParaNuevo = $('.fila-rep').last();
            }

            $('#modalRepresentante').modal({
                backdrop: 'static',
                keyboard: false,
                show: true
            });
        });

        $(document).on('hidden.bs.modal', '#modalRepresentante', function() {
            window.forzarRefrescoBusquedaRep();
            window.filaRepDestinoParaNuevo = null;
        });

        $('#guardar_rep_listo').off('click').on('click', function(e) {
            e.preventDefault();
            let seleccionados = [];
            let nombres = [];

            $('.fila-rep').each(function() {
                const id = $(this).find('.input-id-rep').val();
                const nombre = $(this).find('.input-nombre-rep').val();
                const tipo = $(this).find('.select-tipo-rep').val();
                const parentesco = $(this).find('.select-parentesco-rep').val();

                if (id && id !== "") {
                    seleccionados.push({
                        id: parseInt(id),
                        nombre: nombre,
                        tipo: tipo,
                        parentesco: parentesco || ''
                    });
                    nombres.push(nombre + ' (' + tipo + (parentesco ? ' - ' + parentesco : '') + ')');
                }
            });

            $('#representantes_ids').val(JSON.stringify(seleccionados));

            let boton = $('#btn_modal_rep');
            if (nombres.length > 0) {
                boton.attr('data-original-title', nombres.join(', '))
                    .tooltip('fixTitle')
                    .removeClass('input-error');
            } else {
                boton.attr('data-original-title', 'Sin Representantes').tooltip('fixTitle');
            }

            $('#modalRepresentantes').modal('hide');
        });

        $('#cancelar_rep_modal').off('click').on('click', function(e) {
            e.preventDefault();
            cargarRepresentantesGuardados();
            $('#modalRepresentantes').modal('hide');
        });

    })();
</script>