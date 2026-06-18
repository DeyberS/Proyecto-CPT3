<script>
  function buscadorGlobal(idInput, idContenedor, idPaginacion) {
    let timeout = null;
    $(document).on('keyup', idInput, function() {
      clearTimeout(timeout);
      let valor = $(this).val();
      let urlActual = window.location.href.split('?')[0];

      timeout = setTimeout(function() {
        $.ajax({
          url: urlActual,
          type: "GET",
          data: { buscar: valor },
          beforeSend: function() { $(idContenedor).css('opacity', '0.5'); },
          success: function(data) {
            $(idContenedor).html($(data).find(idContenedor).html());
            if (idPaginacion) {
              $(idPaginacion).html($(data).find(idPaginacion).html());
            }
            $(idContenedor).css('opacity', '1');
          },
          error: function() { $(idContenedor).css('opacity', '1'); }
        });
      }, 300);
    });
  }

  window.onload = function() {
    const full_loader = document.getElementById('full_loader');
    if (full_loader) {
      setTimeout(function() { full_loader.style.display = 'none'; }, 200);
    }
  };
</script>

<script>
  window.idsRecetasActuales = "";

  function getRutaBase() {
    let path = window.location.pathname;
    if (path.includes('/pages/php/papelera/')) return "../../../";
    if (path.includes('/pages/php/')) return "../../";
    return "";
  }

  // TRABAJO PESADO: Ejecuta las verificaciones de base de datos e Inserts
  function procesarGeneradorAlertas() {
    let urlGenerar = getRutaBase() + 'cfg/ajax/generar_alertas.php';
    $.ajax({ url: urlGenerar, type: 'GET', cache: false });
  }

  // INTERFAZ GRÁFICA: Refresca el dropdown y la iluminación del menú en 1 sola llamada
  function revisionGlobalDeNotificaciones() {
    let urlAlertas = getRutaBase() + 'cfg/ajax/alertas.php';

    // Recargar tablas si es necesario
    if ($('#contenedorTabla').length > 0 && !window.location.pathname.includes('farmacia_inventario_kardex.php')) {
      $('#contenedorTabla').load(window.location.href + ' #t_user');
    }

    $.ajax({
      url: urlAlertas,
      type: 'GET',
      dataType: 'json',
      cache: false,
      success: function(response) {
        // 1. Limpiar la UI actual
        $('#lista-notificaciones-dropdown').empty();
        $('#contador-notificaciones').text('0').hide();
        $('#titulo-notificaciones').text('No tienes notificaciones nuevas');

        let totalNuevas = 0;
        let listaIdsRecetas = [];

        // 2. Llenar notificaciones
        if (response && response.data && Array.isArray(response.data)) {
          response.data.forEach(function(item) {
            let tipoAlerta = "success";
            let tituloMinusculas = item.titulo.toLowerCase();

            if (tituloMinusculas.includes("vencid") || tituloMinusculas.includes("perdida") || tituloMinusculas.includes("crítico") || tituloMinusculas.includes("inasistente") || tituloMinusculas.includes("agotado")) {
              tipoAlerta = "danger";
            } else if (tituloMinusculas.includes("próxim") || tituloMinusculas.includes("reprogramada") || tituloMinusculas.includes("confirmada")) {
              tipoAlerta = "warning";
            }

            let liHtml = '<li>' +
                         '  <a href="' + item.ruta + '" style="white-space: normal; display: block; padding: 10px 15px; border-bottom: 1px solid #f4f4f4;">' +
                         '    <strong class="text-' + tipoAlerta + '">' + item.titulo + '</strong><br>' +
                         '    <small style="color: #666; display:block; margin-top:2px;">' + item.mensaje + '</small>' +
                         '  </a>' +
                         '</li>';
            
            $('#lista-notificaciones-dropdown').append(liHtml);
            totalNuevas++;

            if (item.categoria === 'receta_disponible') {
              listaIdsRecetas.push(item.id_notificacion);
            }
          });
        }

        if (totalNuevas > 0) {
          $('#contador-notificaciones').text(totalNuevas).fadeIn();
          $('#titulo-notificaciones').text("Tienes " + totalNuevas + " notificaciones nuevas");
        }

        // 3. Manejar la Iluminación del Menú Récipes
        window.idsRecetasActuales = listaIdsRecetas.sort().join(',');
        let recetasVistas = sessionStorage.getItem('recetas_vistas_ids') || '';

        if (response.iluminar_recetas && window.idsRecetasActuales !== recetasVistas) {
          $('#menu-link-recetas').addClass('glow-active');
        } else {
          $('#menu-link-recetas').removeClass('glow-active');
        }
      }
    });
  }

  /* --- Inicialización General --- */
  $(document).ready(function() {
    if ($('#buscar').length > 0) {
      buscadorGlobal('#buscar', '#contenedorTabla', '#contenedorPaginacion');
    }

    // 1. Backend: Correr script pesado cada 2 minutos
    procesarGeneradorAlertas();
    setInterval(procesarGeneradorAlertas, 120000); 

    // 2. Frontend: Revisar BD para la UI cada 12 segundos (consolidado)
    setTimeout(revisionGlobalDeNotificaciones, 2000); 
    setInterval(revisionGlobalDeNotificaciones, 12000);

    // 3. Apagar iluminación al hacer clic en Récipes
    $(document).on('click', '#menu-link-recetas', function() {
      $(this).removeClass('glow-active');
      if (window.idsRecetasActuales) {
        sessionStorage.setItem('recetas_vistas_ids', window.idsRecetasActuales);
      }
    });
  });

  function limpiarNotificaciones() {
    let urlMarcarLeidas = getRutaBase() + 'cfg/ajax/marcar_leidos.php';

    $.ajax({
      url: urlMarcarLeidas,
      type: 'POST',
      dataType: 'json',
      success: function(response) {
        if (response.status === 'success') {
          // Vaciar visualmente en cuanto responda
          $('#lista-notificaciones-dropdown').empty();
          $('#contador-notificaciones').text('0').fadeOut();
          $('#titulo-notificaciones').text('No tienes notificaciones nuevas');
          
          // Refrescar el estado general (para apagar brillos residuales)
          revisionGlobalDeNotificaciones();
        }
      }
    });
  }

  function actualizarReloj() {
    const ahora = new Date();
    const opcionesFecha = { day: '2-digit', month: '2-digit', year: 'numeric' };
    const fechaTexto = ahora.toLocaleDateString('es-ES', opcionesFecha);
    const horaTexto = ahora.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });

    if(document.getElementById('fecha-actual')) document.getElementById('fecha-actual').textContent = fechaTexto;
    if(document.getElementById('reloj-actual')) document.getElementById('reloj-actual').textContent = horaTexto;
  }

  setInterval(actualizarReloj, 1000);
  $(document).ready(function() { actualizarReloj(); });
</script>