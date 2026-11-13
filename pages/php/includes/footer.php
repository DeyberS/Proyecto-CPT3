<script>
  function buscadorGlobal(idInput, idContenedor, idPaginacion) {
    let timeout = null;

    function cargarDatosAjax(url, parametros = null) {
      $.ajax({
        url: url,
        type: "GET",
        data: parametros,
        beforeSend: function() {
          $(idContenedor).css({
            'transition': 'opacity 0.2s ease',
            'opacity': '0.4',
            'pointer-events': 'none'
          });
          if (idPaginacion) $(idPaginacion).css({
            'transition': 'opacity 0.2s ease',
            'opacity': '0.4'
          });
        },
        success: function(data) {
          let nuevoContenidoTabla = $(data).find(idContenedor).html();
          if (nuevoContenidoTabla) $(idContenedor).html(nuevoContenidoTabla);

          if (idPaginacion) {
            let nuevaPaginacion = $(data).find(idPaginacion).html();
            if (nuevaPaginacion) $(idPaginacion).html(nuevaPaginacion);
          }

          $(idContenedor).css({
            'opacity': '1',
            'pointer-events': 'auto'
          });
          if (idPaginacion) $(idPaginacion).css({
            'opacity': '1'
          });
        },
        error: function() {
          $(idContenedor).css({
            'opacity': '1',
            'pointer-events': 'auto'
          });
          if (idPaginacion) $(idPaginacion).css({
            'opacity': '1'
          });
        }
      });
    }

    $(document).on('keyup', idInput, function() {
      clearTimeout(timeout);
      let valor = $(this).val();
      let urlBase = window.location.href.split('?')[0];

      timeout = setTimeout(function() {
        let nuevaUrl = urlBase + (valor ? "?buscar=" + encodeURIComponent(valor) : "");
        window.history.pushState({
          path: nuevaUrl
        }, '', nuevaUrl);

        cargarDatosAjax(urlBase, {
          buscar: valor
        });
      }, 300);
    });

    if (idPaginacion) {
      $(document).on('click', idPaginacion + ' a', function(e) {
        e.preventDefault();
        let urlDestino = $(this).attr('href');

        if (urlDestino && urlDestino !== '#') {
          window.history.pushState({
            path: urlDestino
          }, '', urlDestino);
          cargarDatosAjax(urlDestino);
        }
      });
    }

    $(window).on('popstate', function() {
      cargarDatosAjax(window.location.href);
    });
  }

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
  window.idsRecetasActuales = "";

  function getRutaBase() {
    let path = window.location.pathname;
    if (path.includes('/pages/php/papelera/')) return "../../../";
    if (path.includes('/pages/php/')) return "../../";
    return "";
  }

  function procesarGeneradorAlertas() {
    let urlGenerar = getRutaBase() + 'cfg/ajax/alertas_generar.php';
    $.ajax({
      url: urlGenerar,
      type: 'GET',
      cache: false
    });
  }

  function revisionGlobalDeNotificaciones() {
    let urlAlertas = getRutaBase() + 'cfg/ajax/alertas.php';

    if ($('#contenedorTabla').length > 0 && !window.location.pathname.includes('farmacia_inventario_kardex.php')) {
      $('#contenedorTabla').load(window.location.href + ' #t_user');
    }

    $.ajax({
      url: urlAlertas,
      type: 'GET',
      dataType: 'json',
      cache: false,
      success: function(response) {
        $('#lista-notificaciones-dropdown').empty();
        $('#contador-notificaciones').text('0').hide();
        $('#titulo-notificaciones').text('No tienes notificaciones nuevas');

        let totalNuevas = 0;
        let listaIdsRecetas = [];

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

  $(document).ready(function() {
    if ($('#buscar').length > 0) {
      buscadorGlobal('#buscar', '#contenedorTabla', '#contenedorPaginacion');
    }
    procesarGeneradorAlertas();
    setInterval(procesarGeneradorAlertas, 120000);

    setTimeout(revisionGlobalDeNotificaciones, 2000);
    setInterval(revisionGlobalDeNotificaciones, 12000);

    $(document).on('click', '#menu-link-recetas', function() {
      $(this).removeClass('glow-active');
      if (window.idsRecetasActuales) {
        sessionStorage.setItem('recetas_vistas_ids', window.idsRecetasActuales);
      }
    });
  });

  function limpiarNotificaciones() {
    let urlMarcarLeidas = getRutaBase() + 'cfg/ajax/alertas_marcar_leidos.php';

    $.ajax({
      url: urlMarcarLeidas,
      type: 'POST',
      dataType: 'json',
      success: function(response) {
        if (response.status === 'success') {
          $('#lista-notificaciones-dropdown').empty();
          $('#contador-notificaciones').text('0').fadeOut();
          $('#titulo-notificaciones').text('No tienes notificaciones nuevas');

          revisionGlobalDeNotificaciones();
        }
      }
    });
  }

  function actualizarReloj() {
    const ahora = new Date();
    const opcionesFecha = {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric'
    };
    const fechaTexto = ahora.toLocaleDateString('es-ES', opcionesFecha);
    const horaTexto = ahora.toLocaleTimeString('es-ES', {
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
      hour12: true
    });

    if (document.getElementById('fecha-actual')) document.getElementById('fecha-actual').textContent = fechaTexto;
    if (document.getElementById('reloj-actual')) document.getElementById('reloj-actual').textContent = horaTexto;
  }

  setInterval(actualizarReloj, 1000);
  $(document).ready(function() {
    actualizarReloj();
  });
</script>