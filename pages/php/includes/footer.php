<!-- Filtro De Busqueda -->
<script>
  /* --- Buscador Global AJAX --- */
function buscadorGlobal(idInput, idContenedor, idPaginacion) {
    let timeout = null;

    $(document).on('keyup', idInput, function() {
        clearTimeout(timeout);
        let valor = $(this).val();
        let urlActual = window.location.href.split('?')[0];

        // Esperar 300ms después de escribir para enviar la petición
        timeout = setTimeout(function() {
            $.ajax({
                url: urlActual,
                type: "GET",
                data: { buscar: valor },
                beforeSend: function() {
                    $(idContenedor).css('opacity', '0.5');
                },
                success: function(data) {
                    // Reemplaza el cuerpo de la tabla
                    $(idContenedor).html($(data).find(idContenedor).html());
                    // Reemplaza la paginación
                    if (idPaginacion) {
                        $(idPaginacion).html($(data).find(idPaginacion).html());
                    }
                    $(idContenedor).css('opacity', '1');
                },
                error: function() {
                    $(idContenedor).css('opacity', '1');
                }
            });
        }, 300);
    });
}

/* --- Inicialización --- */
$(document).ready(function() {
    // Si el input con id "buscar" existe, activamos el buscador
    if ($('#buscar').length > 0) {
        buscadorGlobal('#buscar', '#contenedorTabla', 'nav[aria-label="Page navigation"]');
    }
});
</script>
<script>
  window.onload = function() {
    const full_loader = document.getElementById('full_loader');

    if (full_loader) {
      setTimeout(function() {
        full_loader.style.display = 'none';
      }, 200);
    }
  };
</script>
<script>
  let notificacion = new Set();
  let historialEstados = JSON.parse(sessionStorage.getItem('historial_estados')) || {};
  let notificacionesLeidas = JSON.parse(sessionStorage.getItem('notificaciones_leidas')) || [];

  function revisionGlobalDeNotificaciones() {
    let path = window.location.pathname;
    let niveles = (path.match(/\//g) || []).length;

    let rutaBase = "";
    if (path.includes('/pages/php/papelera/')) {
      rutaBase = "../../../";
    } else if (path.includes('/pages/php/')) {
      rutaBase = "../../";
    }

    let urlAlertas = rutaBase + 'cfg/ajax/alertas.php';

    if ($('#contenedorTabla').length > 0) {
        $('#contenedorTabla').load(window.location.href + ' #t_user');
    }

    $.getJSON(urlAlertas, function(data) {
        data.forEach(function(item) {
            let idUnico = item.id + "_" + (item.estatus || 'notif');
            
            // --- Lógica para CITAS ---
            if (item.categoria === 'cita') {
                if (!notificacion.has(idUnico)) {
                    if (item.proxima && item.estatus === 'Confirmada') {
                        añadirNotificacionAlPanel("¡Tiene Una Cita próxima!", item.titulo + " - " + item.detalle, "warning", idUnico, item.ruta);
                    } 
                    else if (item.estatus === 'Inasistente') {
                        añadirNotificacionAlPanel("¡Tiene Una Cita Perdida!", item.titulo + " (No asistió)", "danger", idUnico, item.ruta);
                    }
                    else if (item.estatus === 'Vencida') {
                        añadirNotificacionAlPanel("¡Tiene Una Cita Vencida!", item.titulo + " (No confirmo la cita)", "danger", idUnico, item.ruta);
                    }
                    else if (item.estatus === 'Reprogramada') {
                        añadirNotificacionAlPanel("¡Tiene Una Cita Reprogramada!", item.titulo, "warning", idUnico, item.ruta);
                    }
                    notificacion.add(idUnico);
                }
            }

            // --- Lógica para INVENTARIO (Stock) ---
            else if (item.categoria === 'inventario_stock') {
                if (!notificacion.has(idUnico)) {
                    let color = (item.estatus === 'critico') ? "danger" : "warning";
                    añadirNotificacionAlPanel(item.titulo, item.detalle, color, idUnico, item.ruta);
                    notificacion.add(idUnico);
                }
            }

            // --- Lógica para INVENTARIO (Lotes) ---
            else if (item.categoria === 'inventario_lote') {
                if (!notificacion.has(idUnico)) {
                    let color = (item.estatus === 'vencido') ? "danger" : "warning";
                    añadirNotificacionAlPanel(item.titulo, item.detalle, color, idUnico, item.ruta);
                    notificacion.add(idUnico);
                }
            }
        });
    }).fail(function() {
      // Si falla, intentamos una ruta alternativa por si estamos en la raíz
      $.getJSON('cfg/ajax/alertas.php', function(data) {
        /* misma lógica */
      });
    });
  }
  // Ejecutar cada 60 segundos en CUALQUIER página
  setInterval(revisionGlobalDeNotificaciones, 60000);

  // Ejecutar una vez al cargar para no esperar el primer minuto
  $(document).ready(function() {
    revisionGlobalDeNotificaciones();
  });

  function limpiarNotificaciones() {
    // Buscamos todos los IDs presentes en la lista actual y los mandamos a "leídos"
    /*$('#lista-notificaciones-dropdown li').each(function() {
      let id = $(this).attr('id').replace('notif-item-', '');
      if (!notificacionesLeidas.includes(id)) {
        notificacionesLeidas.push(id);
      }
    });*/

    $('#lista-notificaciones-dropdown li').css({
      'opacity': '0.6',
      'background-color': '#f9f9f9'
    });

    // Guardar en el navegador
    sessionStorage.setItem('notificaciones_leidas', JSON.stringify(notificacionesLeidas));

    // Limpiar interfaz
    //$('#lista-notificaciones-dropdown').empty();
    $('#contador-notificaciones').text('0').hide();
    $('#titulo-notificaciones').text('No tienes notificaciones nuevas');
  }

  function añadirNotificacionAlPanel(titulo, mensaje, tipo, idUnico, ruta) {
    // 1. Si ya está en la lista de leídos, no la mostramos
    //if (notificacionesLeidas.includes(idUnico)) return;

    // 2. Evitar duplicados visuales en el panel
    if ($(`#notif-item-${idUnico}`).length > 0) return;

    // Actualizar contador
    let contadorPrevio = parseInt($('#contador-notificaciones').text()) || 0;
    let nuevoTotal = contadorPrevio + 1;

    // Actualiza el número naranja
    $('#contador-notificaciones').text(nuevoTotal).show();

    $('#titulo-notificaciones').text("Tienes " + nuevoTotal + " notificaciones nuevas");

    let colorIcono = tipo === 'danger' ? 'text-red' : (tipo === 'warning' ? 'text-yellow' : 'text-green');
    let iconoFa = tipo === 'danger' ? 'fa-ban' : (tipo === 'warning' ? 'fa-warning' : 'fa-check');

    // 3. El enlace ahora es dinámico según la ruta que le pases
    const itemHtml = `
        <li id="notif-item-${idUnico}" class="item-notificacion" style="border-bottom: 1px solid #f4f4f4;">
            <a href="${ruta}" style="white-space: normal; display: block; padding: 10px;">
                <i class="fa ${iconoFa} ${colorIcono}" style="width: 20px;"></i> 
                <span style="font-weight: bold;">${titulo}</span>
                <p style="margin: 0 0 0 25px; font-size: 11px; color: #666;">${mensaje}</p>
            </a>
        </li>`;

    $('#lista-notificaciones-dropdown').prepend(itemHtml);
  }
</script>
<script>
function actualizarReloj() {
    const ahora = new Date();
    
    // Formatear Fecha (ejemplo: 24/12/2025)
    const opcionesFecha = { day: '2-digit', month: '2-digit', year: 'numeric' };
    const fechaTexto = ahora.toLocaleDateString('es-ES', opcionesFecha);
    
    // Formatear Hora (ejemplo: 14:30:05)
    const horaTexto = ahora.toLocaleTimeString('es-ES', { 
        hour: '2-digit', 
        minute: '2-digit', 
        second: '2-digit',
        hour12: true 
    });

    document.getElementById('fecha-actual').textContent = fechaTexto;
    document.getElementById('reloj-actual').textContent = horaTexto;
}

// Ejecutar la función cada segundo
setInterval(actualizarReloj, 1000);

// Llamar de inmediato para que no aparezca vacío al cargar
$(document).ready(function() {
    actualizarReloj();
});
</script>