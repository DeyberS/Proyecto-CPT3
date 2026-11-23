<script data-persistent="true">
  (function() {
    document.querySelectorAll(
      'link[rel="icon"], link[rel="shortcut icon"], link[rel="apple-touch-icon"]'
    ).forEach(function(link) {
      var href = link.getAttribute('href');
      if (!href) return;
      if (/^(https?:)?\/\//.test(href)) return;   // ya absoluto
      if (href.charAt(0) === '/' || href.indexOf('data:') === 0) return;
      try {
        link.setAttribute('href', new URL(href, location.href).href);
      } catch (e) {}
    });
  })();

  if (!window.__footerGlobalInit) {
    window.__footerGlobalInit = true;

    window.Loader = (function() {
      var count = 0;
      var safetyTimer = null;

      function getEl() {
        return document.getElementById('full_loader');
      }

      function applyShow() {
        var e = getEl();
        if (e) {
          e.classList.add('is-visible');
          e.style.removeProperty('display');
        }
      }

      function applyHide() {
        var e = getEl();
        if (e) {
          e.classList.remove('is-visible');
          e.style.display = 'none';
        }
      }
      return {
        show: function() {
          count++;
          if (count === 1) {
            applyShow();
            clearTimeout(safetyTimer);
            safetyTimer = setTimeout(function() {
              if (count > 0) {
                console.warn('[Loader] Safety timeout — forcing hide');
                count = 0;
                applyHide();
              }
            }, 8000);
          }
        },
        hide: function() {
          if (count > 0) count--;
          if (count === 0) {
            clearTimeout(safetyTimer);
            applyHide();
          }
        },
        forceHide: function() {
          count = 0;
          clearTimeout(safetyTimer);
          applyHide();
        }
      };
    })();

    /* Safety: en recargas / back-forward, forzamos ocultar */
    window.addEventListener('pageshow', function() {
      if (window.Loader) window.Loader.forceHide();
    });
    window.addEventListener('load', function() {
      if (window.Loader) window.Loader.forceHide();
    });

    /* --- 1.3 Helpers PJAX-safe (fix #2: limpieza de timers) --- */
    window.pageOn = function(events, selector, handler) {
      $(document).off(events + '.page').on(events + '.page', selector, handler);
    };

    window.__pageTimers = window.__pageTimers || [];
    window.pageSetInterval = function(fn, ms) {
      var id = setInterval(fn, ms);
      window.__pageTimers.push({
        type: 'i',
        id: id
      });
      return id;
    };
    window.pageSetTimeout = function(fn, ms) {
      var id = setTimeout(fn, ms);
      window.__pageTimers.push({
        type: 't',
        id: id
      });
      return id;
    };

    window.pageCleanup = function() {
      $(document).off('.page');
      if (window.__pageTimers) {
        window.__pageTimers.forEach(function(t) {
          try {
            t.type === 'i' ? clearInterval(t.id) : clearTimeout(t.id);
          } catch (e) {}
        });
        window.__pageTimers = [];
      }
    };

    window.FormInit = (function() {
      var inits = {};
      return {
        register: function(id, fn) {
          inits[id] = fn;
        },
        scan: function(root) {
          (root ? $(root) : $(document)).find('[data-form-init]').each(function() {
            var $el = $(this),
              id = $el.data('form-init');
            if (id && inits[id] && !$el.data('inited')) {
              $el.data('inited', true);
              try {
                inits[id]($el);
              } catch (e) {
                console.error('[FormInit:' + id + ']', e);
              }
            }
          });
        }
      };
    })();

    /* --- 1.5 Reloj (UN solo interval) --- */
    function actualizarReloj() {
      var ahora = new Date();
      var f = ahora.toLocaleDateString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
      });
      var h = ahora.toLocaleTimeString('es-ES', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
      });
      var fe = document.getElementById('fecha-actual');
      var re = document.getElementById('reloj-actual');
      if (fe) fe.textContent = f;
      if (re) re.textContent = h;
    }
    if (!window.__clockTimer) {
      window.__clockTimer = setInterval(actualizarReloj, 1000);
      $(actualizarReloj);
    }

    /* --- 1.6 Buscador con lupa/limpiar (idempotente) --- */
    window.initSearchBars = function() {
      $('input#buscar').each(function() {
        var $input = $(this);
        if ($input.parent().hasClass('search-wrap')) return;
        $input.wrap('<div class="search-wrap"></div>');
        var $wrap = $input.parent();
        var $btn = $('<button type="button" class="search-clear" title="Limpiar búsqueda" aria-label="Limpiar búsqueda"></button>');
        $wrap.append($btn);
        if ($input.val().trim() !== '') $wrap.addClass('has-value');
        $input.on('input', function() {
          if ($(this).val().trim() !== '') $wrap.addClass('has-value');
          else $wrap.removeClass('has-value');
        });
        $btn.on('click', function() {
          $input.val('').trigger('input').trigger('keyup').focus();
        });
      });
    };
    $(window.initSearchBars);

    /* --- 1.7 Glow del menú de recetas --- */
    $(document).on('click', '#menu-link-recetas', function() {
      $(this).removeClass('glow-active');
      if (window.idsRecetasActuales) {
        sessionStorage.setItem('recetas_vistas_ids', window.idsRecetasActuales);
      }
    });
    /* --- 1.8 Cleanup defensivo de modales (Bootstrap 3 + PJAX) --- */
    window.limpiarModales = function() {
      // 1) Intentar cerrar normalmente (dispara hidden.bs.modal si puede)
      try {
        $('.modal.in, .modal.show').each(function() {
          try {
            $(this).modal('hide');
          } catch (e) {}
        });
      } catch (e) {}

      // 2) Forzar remoción de artefactos huérfanos (por si Bootstrap no llegó)
      $('.modal-backdrop').remove();
      $('body').removeClass('modal-open').css({
        'padding-right': '',
        'overflow': ''
      });
    };

    /* Safety net: si Bootstrap cierra pero deja algo colgado, lo limpiamos */
    $(document).on('hidden.bs.modal', '.modal', function() {
      setTimeout(function() {
        if ($('.modal.in, .modal.show').length === 0) {
          $('.modal-backdrop').remove();
          $('body').removeClass('modal-open').css({
            'padding-right': '',
            'overflow': ''
          });
        }
      }, 300);
    });
  }
</script>

<script data-persistent="true">
  /* ============================================================
   2) BUSCADOR GLOBAL — con abort de peticiones previas
   ============================================================ */
  if (typeof window.buscadorGlobal !== 'function') {
    window.buscadorGlobal = function(idInput, idContenedor, idPaginacion) {
      var timeout = null;
      var currentXhr = null;

      function cargarDatosAjax(url, parametros) {
        if (currentXhr && currentXhr.readyState !== 4) {
          try {
            currentXhr.abort();
          } catch (e) {}
        }
        currentXhr = $.ajax({
          url: url,
          type: 'GET',
          data: parametros || null,
          beforeSend: function() {
            $(idContenedor).css({
              'transition': 'opacity .2s ease',
              'opacity': '.4',
              'pointer-events': 'none'
            });
            if (idPaginacion) $(idPaginacion).css({
              'transition': 'opacity .2s ease',
              'opacity': '.4'
            });
          },
          success: function(data) {
            var n = $(data).find(idContenedor).html();
            if (n) $(idContenedor).html(n);
            if (idPaginacion) {
              var np = $(data).find(idPaginacion).html();
              if (np) $(idPaginacion).html(np);
            }
            $(idContenedor).css({
              'opacity': '1',
              'pointer-events': 'auto'
            });
            if (idPaginacion) $(idPaginacion).css({
              'opacity': '1'
            });
          },
          error: function(xhr, status) {
            if (status === 'abort') return;
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
        var valor = $(this).val();
        var urlBase = window.location.href.split('?')[0];
        timeout = setTimeout(function() {
          var nuevaUrl = urlBase + (valor ? '?buscar=' + encodeURIComponent(valor) : '');
          window.history.replaceState({
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
          var urlDestino = $(this).attr('href');
          if (urlDestino && urlDestino !== '#') {
            window.history.pushState({
              path: urlDestino
            }, '', urlDestino);
            cargarDatosAjax(urlDestino);
          }
        });
      }
    };
  }

  if (!window.__buscadorInicializado) {
    window.__buscadorInicializado = true;
    window.buscadorGlobal('#buscar', '#contenedorTabla', '#contenedorPaginacion');
  }
</script>

<script data-persistent="true">
  /* ============================================================
   3) NOTIFICACIONES — polling más ligero (30s / 120s)
   ============================================================ */
  if (!window.__notifInit) {
    window.__notifInit = true;

    window.idsRecetasActuales = '';
    window.__idsRecetasServidor = '';
    window.__lastAlertId = 0;
    window.__alertasCache = [];
    window.__pollAlertasTimer = null;
    window.__pollGeneradorTimer = null;
    window.__pollEnPausa = false;
    window.__sinConexion = false;
    window.__backendSoportaSince = true;
    window.__resincronizando = false;

    var LS_KEY_LAST_ID = 'cpt_last_alert_id';
    var LS_KEY_CACHE = 'cpt_alertas_cache';

    function getRutaBase() {
      var p = window.location.pathname;
      if (p.indexOf('/pages/php/papelera/') !== -1) return '../../../';
      if (p.indexOf('/pages/php/') !== -1) return '../../';
      return '';
    }

    function getIdItem(i) {
      return i.id || i.id_notificacion || 0;
    }

    function getTipoItem(i) {
      return i.tipo || i.categoria || '';
    }

    function cargarCacheLocal() {
      try {
        var rawId = localStorage.getItem(LS_KEY_LAST_ID);
        if (rawId) window.__lastAlertId = parseInt(rawId, 10) || 0;
        var rawCache = localStorage.getItem(LS_KEY_CACHE);
        if (rawCache) window.__alertasCache = JSON.parse(rawCache) || [];
      } catch (e) {
        window.__alertasCache = [];
      }
    }

    function guardarCacheLocal() {
      try {
        localStorage.setItem(LS_KEY_LAST_ID, String(window.__lastAlertId));
        localStorage.setItem(LS_KEY_CACHE, JSON.stringify(window.__alertasCache.slice(-50)));
      } catch (e) {}
    }

    function pintarDropdown() {
      var $lista = $('#lista-notificaciones-dropdown');
      if (!$lista.length) return;
      $lista.empty();

      if (!window.__alertasCache.length) {
        $('#contador-notificaciones').text('0').hide();
        $('#titulo-notificaciones').text('No tienes notificaciones nuevas');
      } else {
        window.__alertasCache.forEach(function(item) {
          var tipo = 'success';
          var t = (item.titulo || '').toLowerCase();
          if (t.indexOf('vencid') !== -1 || t.indexOf('perdida') !== -1 || t.indexOf('crítico') !== -1 ||
            t.indexOf('inasistente') !== -1 || t.indexOf('agotado') !== -1) tipo = 'danger';
          else if (t.indexOf('próxim') !== -1 || t.indexOf('reprogramada') !== -1 || t.indexOf('confirmada') !== -1) tipo = 'warning';
          $lista.append(
            '<li><a href="' + item.ruta + '" style="white-space:normal;display:block;padding:10px 15px;border-bottom:1px solid #f4f4f4;">' +
            '<strong class="text-' + tipo + '">' + item.titulo + '</strong><br>' +
            '<small style="color:#666;display:block;margin-top:2px;">' + item.mensaje + '</small></a></li>'
          );
        });
        $('#contador-notificaciones').text(window.__alertasCache.length).fadeIn();
        $('#titulo-notificaciones').text('Tienes ' + window.__alertasCache.length + ' notificaciones nuevas');
      }

      window.idsRecetasActuales = window.__idsRecetasServidor || '';
      var vistas = sessionStorage.getItem('recetas_vistas_ids') || '';
      if (window.idsRecetasActuales && window.idsRecetasActuales !== vistas) {
        $('#menu-link-recetas').addClass('glow-active');
      } else {
        $('#menu-link-recetas').removeClass('glow-active');
      }
    }

    function procesarGeneradorAlertas() {
      if (window.__pollEnPausa || document.hidden || window.__sinConexion) return;
      $.ajax({
        url: getRutaBase() + 'cfg/ajax/alertas_generar.php',
        type: 'GET',
        cache: false,
        timeout: 15000
      });
    }

    window.revisionGlobalDeNotificaciones = function(force) {
      if (window.__pollEnPausa) return;
      if (document.hidden && !force) return;
      if (!navigator.onLine) {
        window.__sinConexion = true;
        return;
      }

      var url = getRutaBase() + 'cfg/ajax/alertas.php' +
        (window.__backendSoportaSince ? ('?since_id=' + window.__lastAlertId) : '');

      $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        cache: false,
        timeout: 10000,
        success: function(resp) {
          window.__sinConexion = false;

          if (typeof resp.last_id === 'undefined') {
            window.__backendSoportaSince = false;
            if (resp && Array.isArray(resp.data)) {
              window.__alertasCache = resp.data.slice(0, 50);
              window.__idsRecetasServidor = window.__alertasCache
                .filter(function(i) {
                  return getTipoItem(i) === 'receta_disponible';
                })
                .map(getIdItem).sort(function(a, b) {
                  return a - b;
                }).join(',');
              window.__lastAlertId = window.__alertasCache.length ?
                Math.max.apply(null, window.__alertasCache.map(getIdItem)) :
                window.__lastAlertId;
              guardarCacheLocal();
              pintarDropdown();
            }
            return;
          }

          window.__idsRecetasServidor = Array.isArray(resp.ids_recetas) ?
            resp.ids_recetas.slice().sort(function(a, b) {
              return a - b;
            }).join(',') :
            '';

          if (resp.changed && Array.isArray(resp.data) && resp.data.length) {
            var existentes = {};
            window.__alertasCache.forEach(function(i) {
              existentes[getIdItem(i)] = true;
            });
            resp.data.forEach(function(item) {
              var idItem = getIdItem(item);
              if (!existentes[idItem]) window.__alertasCache.push(item);
            });
            window.__alertasCache.sort(function(a, b) {
              return getIdItem(a) - getIdItem(b);
            });
            if (window.__alertasCache.length > 50) window.__alertasCache = window.__alertasCache.slice(-50);
          }

          if (resp.last_id) window.__lastAlertId = resp.last_id;

          var total = (typeof resp.total_pendientes === 'number') ? resp.total_pendientes : window.__alertasCache.length;
          if (total < window.__alertasCache.length && !window.__resincronizando) {
            window.__alertasCache = [];
            window.__lastAlertId = 0;
            window.__resincronizando = true;
            guardarCacheLocal();
            pintarDropdown();
            return;
          }

          window.__resincronizando = false;
          guardarCacheLocal();
          pintarDropdown();
        },
        error: function(xhr) {
          if (xhr.status === 0) window.__sinConexion = true;
        }
      });
    };

    function iniciarPolling() {
      detenerPolling();
      if (window.__sinConexion) return;
      procesarGeneradorAlertas();
      window.revisionGlobalDeNotificaciones(true);

      /* FIX #3: intervalos más ligeros (antes 5s / 60s) */
      window.__pollGeneradorTimer = setInterval(procesarGeneradorAlertas, 120000);
      window.__pollAlertasTimer = setInterval(function() {
        if (!document.hidden) window.revisionGlobalDeNotificaciones(false);
      }, 30000);
    }

    function detenerPolling() {
      if (window.__pollAlertasTimer) {
        clearInterval(window.__pollAlertasTimer);
        window.__pollAlertasTimer = null;
      }
      if (window.__pollGeneradorTimer) {
        clearInterval(window.__pollGeneradorTimer);
        window.__pollGeneradorTimer = null;
      }
    }

    document.addEventListener('visibilitychange', function() {
      if (document.hidden) detenerPolling();
      else iniciarPolling();
    });
    window.addEventListener('online', function() {
      window.__sinConexion = false;
      iniciarPolling();
    });
    window.addEventListener('offline', function() {
      window.__sinConexion = true;
      detenerPolling();
    });

    window.limpiarNotificaciones = function() {
      $.ajax({
        url: getRutaBase() + 'cfg/ajax/alertas_marcar_leidos.php',
        type: 'POST',
        dataType: 'json',
        success: function(resp) {
          if (resp.status === 'success') {
            window.__alertasCache = [];
            window.__idsRecetasServidor = Array.isArray(resp.ids_recetas) ?
              resp.ids_recetas.slice().sort(function(a, b) {
                return a - b;
              }).join(',') :
              '';
            window.__lastAlertId = 0;
            sessionStorage.removeItem('recetas_vistas_ids');
            guardarCacheLocal();
            pintarDropdown();
            window.revisionGlobalDeNotificaciones(true);
          }
        }
      });
    };

    $(function() {
      cargarCacheLocal();
      if (window.__alertasCache.length) pintarDropdown();
      if (navigator.onLine) iniciarPolling();
      else window.__sinConexion = true;
    });
  }
</script>

<script data-persistent="true">
  var __initialPersistentInline = null;
  function esPersistenteGlobal(s) {
    if (!__initialPersistentInline) {
      __initialPersistentInline = new Set();
      document.querySelectorAll('script[data-persistent="true"]:not([src])')
        .forEach(function (el) {
          __initialPersistentInline.add((el.textContent || '').trim());
        });
    }
    return __initialPersistentInline.has((s.textContent || '').trim());
  }

  /* ============================================================
   4) PJAX + SIDEBAR + LOGOUT — fixes de lag y condiciones de carrera
   ============================================================ */
   
  if (!window.__pjaxInit) {
    window.__pjaxInit = true;

    // Fuerza que $(window).on('load', fn) dispare si el doc ya cargó
    (function() {
      var _on = jQuery.fn.on;
      jQuery.fn.on = function(types, selector, data, fn) {
        // Cuando alguien registra 'load' sobre window y ya está cargado, disparamos ya
        if (this && this[0] === window && typeof types === 'string' && /\bload\b/.test(types)) {
          var handler = typeof data === 'function' ? data : fn;
          if (typeof handler === 'function' && document.readyState === 'complete') {
            setTimeout(function () { try { handler.call(window, jQuery.Event('load')); } catch(e){} }, 0);
            return this;
          }
        }
        return _on.apply(this, arguments);
      };
    })();

    (function () {
      var _add = document.addEventListener;
      document.addEventListener = function (type, listener, opts) {
        if (type === 'DOMContentLoaded' && document.readyState !== 'loading') {
          setTimeout(function () {
            try { listener.call(document, new Event('DOMContentLoaded')); }
            catch (e) { console.error('[PJAX DOMContentLoaded]', e); }
          }, 0);
          return;
        }
        return _add.call(this, type, listener, opts);
      };
    })();

    if (!window.__pjaxUrlOriginal) window.__pjaxUrlOriginal = window.location.href;

    (function() {
      function congelarEnlaces() {
        $('.sidebar-menu a, .main-header a, .modal a, .modal-footer a, .dropdown-menu a').each(function() {
          var href = this.getAttribute('href');
          if (!href || href === '#' || href.indexOf('#') === 0 ||
            href.indexOf('javascript:') === 0 || href.indexOf('mailto:') === 0 || href.indexOf('tel:') === 0) return;
          try {
            this.setAttribute('href', new URL(href, window.__pjaxUrlOriginal).href);
          } catch (e) {}
        });
      }
      if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', congelarEnlaces);
      else congelarEnlaces();
    })();

    (function($) {
      'use strict';

      var EXCLUIR = [
        'alertas_marcar_leidos.php',
        '/cfg/desactivar/',
        '/cfg/reactivar/',
        '/cfg/eliminar/',
        /(_info|_ver)\.php(?:[?#].*)?$/i,
        /_(agregar|editar|crear|nuevo|add|edit|form|formulario)\.php(?:[?#].*)?$/i,
        /\/(agregar|editar|crear)\/[^?#]+\.php(?:[?#].*)?$/i,
        /_movimiento_[a-z_]+\.php(?:[?#].*)?$/i,
        /_ajuste?\.php(?:[?#].*)?$/i,
        /_entrada\.php(?:[?#].*)?$/i,
        /_salida\.php(?:[?#].*)?$/i,
      ];

      function debeExcluir(url) {
        if (!url) return false;
        var u = String(url).split('?')[0].split('#')[0];
        return EXCLUIR.some(function(p) {
          return (p instanceof RegExp) ? p.test(u) : u.indexOf(p) !== -1;
        });
      }

      var LOADER_DELAY_MS = 150; // fix #4: no parpadeo en navegaciones rápidas

      function esLinkPJAX(a) {
        if (!a || !a.getAttribute) return false;
        if (a.target === '_blank' || a.hasAttribute('download')) return false;
        if (a.getAttribute('data-toggle') === 'modal') return false;
        if (a.classList.contains('no-pjax')) return false;
        var href = a.getAttribute('href');
        if (!href || href.indexOf('#') === 0 || href.indexOf('javascript:') === 0) return false;
        if (href.indexOf('mailto:') === 0 || href.indexOf('tel:') === 0) return false;
        for (var i = 0; i < EXCLUIR.length; i++) {
          var p = EXCLUIR[i];
          if (p instanceof RegExp) {
            if (p.test(href)) return false;
          } else {
            if (href.indexOf(p) !== -1) return false;
          }
        }
        try {
          if (new URL(a.href, location.href).origin !== location.origin) return false;
        } catch (e) {
          return false;
        }
        return true;
      }

      function reejecutarScriptsExtendido(doc, $contenedor, url) {
        var wrapperDestino = doc.querySelector('.content-wrapper');

        // 1) Scripts dentro del wrapper
        $contenedor.find('script').each(function () {
          var esPersistente = this.getAttribute('data-persistent') === 'true';
          var src = this.getAttribute('src');

          // Persistente + src → librería externa ya cargada, no re-inyectar
          if (esPersistente && src) return;
          // Persistente + inline → solo saltar si es del footer/header (ya corrió)
          if (esPersistente && !src && esPersistenteGlobal(this)) return;

          var $nuevo = src
            ? $('<script>').attr('src', new URL(src, url).href)   // ← absoluto
            : $('<script>').text(this.innerHTML);
          $(this).replaceWith($nuevo);
        });

        // 2) Scripts FUERA del wrapper
        doc.querySelectorAll('script').forEach(function (s) {
          if (wrapperDestino && wrapperDestino.contains(s)) return;

          var esPersistente = s.getAttribute('data-persistent') === 'true';
          var src = s.getAttribute('src');

          if (esPersistente && src) return;                       // librería externa
          if (esPersistente && !src && esPersistenteGlobal(s)) return; // footer/header globals

          if (src) {
            var ns = document.createElement('script');
            ns.src = new URL(src, url).href;                      // ← absoluto
            document.body.appendChild(ns);
          } else if (s.textContent.trim()) {
            var ns = document.createElement('script');
            ns.textContent = s.textContent;
            document.body.appendChild(ns);
          }
        });

        window.FormInit.scan($contenedor);
        if (window.initSearchBars) window.initSearchBars();
      }

      function actualizarMenuActivo(url) {
        try {
          var path = new URL(url, location.href).pathname;
          $('.sidebar-menu li').removeClass('active');
          $('.sidebar-menu a').each(function() {
            var href = this.getAttribute('href');
            if (!href || href === '#' || href.indexOf('javascript:') === 0) return;
            try {
              if (new URL(this.href, location.href).pathname === path) {
                $(this).closest('li').addClass('active');
                $(this).parents('li.treeview').addClass('menu-open');
              }
            } catch (e) {}
          });
        } catch (e) {}
      }

      function sincronizarEstilosDePagina(doc, url) {
        var MARCA = 'data-pjax-style';
        var wrapperDestino = doc.querySelector('.content-wrapper');

        /* ===== 1) <link rel="stylesheet"> FUERA del .content-wrapper ===== */
        var requeridas = new Set();
        doc.querySelectorAll('link[rel="stylesheet"]').forEach(function(link) {
          if (wrapperDestino && wrapperDestino.contains(link)) return;
          var href = link.getAttribute('href');
          if (!href) return;
          try {
            requeridas.add(new URL(href, url).href);
          } catch (e) {}
        });

        document.querySelectorAll('link[' + MARCA + ']').forEach(function(link) {
          if (!requeridas.has(link.href)) link.remove();
        });

        requeridas.forEach(function(absHref) {
          var yaEsta = false;
          document.querySelectorAll('link[rel="stylesheet"]').forEach(function(ex) {
            if (ex.href === absHref) yaEsta = true;
          });
          if (yaEsta) return;
          var clone = document.createElement('link');
          clone.rel = 'stylesheet';
          clone.href = absHref;
          var original = null;
          doc.querySelectorAll('link[rel="stylesheet"]').forEach(function(l) {
            try {
              if (new URL(l.getAttribute('href'), url).href === absHref) original = l;
            } catch (e) {}
          });
          if (original) {
            if (original.getAttribute('media')) clone.setAttribute('media', original.getAttribute('media'));
            if (original.hasAttribute('disabled')) clone.setAttribute('disabled', '');
          }
          clone.setAttribute(MARCA, '1');
          document.head.appendChild(clone);
        });

        /* ===== 2) <style> inline FUERA del .content-wrapper =====
           ⚠️ ESTA es la parte que faltaba. Buscamos TODOS los <style>,
           no solo los del <head>. El bloque <style> de consulta.php
           vive como hermano de .content-wrapper. */
        var estilosNuevos = [];
        doc.querySelectorAll('style').forEach(function(s) {
          if (wrapperDestino && wrapperDestino.contains(s)) return;
          if (s.getAttribute('data-persistent') === 'true') return;
          estilosNuevos.push(s.textContent || '');
        });

        document.querySelectorAll('style[' + MARCA + ']').forEach(function(s) {
          if (!estilosNuevos.includes(s.textContent || '')) s.remove();
        });

        estilosNuevos.forEach(function(css) {
          var yaEsta = false;
          document.querySelectorAll('style').forEach(function(ex) {
            if ((ex.textContent || '') === css) yaEsta = true;
          });
          if (yaEsta) return;
          var st = document.createElement('style');
          st.setAttribute(MARCA, '1');
          st.textContent = css;
          document.head.appendChild(st);
        });
      }

      var ajaxActual = null;
      var tokenActual = 0;
      var loaderTimer = null;

      function cargarPagina(url, push) {
        if (ajaxActual) {
          try {
            ajaxActual.abort();
          } catch (e) {}
        }
        var miToken = ++tokenActual;
        window.__pollEnPausa = true;
        window.__pjaxInFlight = (window.__pjaxInFlight || 0) + 1;

        var $contenido = $('.content-wrapper').first();
        if (!$contenido.length) {
          window.location.href = url;
          return;
        }

        /* fix #4: loader con retardo (evita "flash" en navegaciones <150ms) */
        clearTimeout(loaderTimer);
        loaderTimer = setTimeout(function() {
          if (miToken === tokenActual) window.Loader.show();
        }, LOADER_DELAY_MS);

        ajaxActual = $.ajax({
          url: url,
          type: 'GET',
          dataType: 'html',
          cache: false,
          headers: {
            'X-PJAX': 'true'
          },
          success: function(html) {
            if (miToken !== tokenActual) return;

            /* Parseo eficiente: extrae solo lo que necesitamos */
            var doc;
            try {
              doc = new DOMParser().parseFromString(html, 'text/html');
            } catch (e) {
              window.location.href = url;
              return;
            }

            doc.querySelectorAll('script[data-persistent="true"][src]').forEach(function (s) {
              s.remove();
            });

            var nuevo = doc.querySelector('.content-wrapper');
            if (!nuevo) {
              window.location.href = url;
              return;
            }

            var hasComplexForm = false;
            var forms = nuevo.querySelectorAll('form');
            for (var i = 0; i < forms.length; i++) {
              var f = forms[i];
              var method = (f.getAttribute('method') || 'get').toLowerCase();
              var action = f.getAttribute('action') || '';
              var tieneFile = !!f.querySelector('input[type="file"]');
              if ((method === 'post' && action && action !== '#' && action.indexOf('javascript:') !== 0) || tieneFile) {
                hasComplexForm = true;
                break;
              }
            }

            if (hasComplexForm) {
              // No mostramos loader doble: abortamos y recargamos "en sucio"
              console.log('[PJAX] Formulario detectado, recarga completa:', url);
              window.location.href = url;
              return;
            }

            requestAnimationFrame(function() {
              if (miToken !== tokenActual) return;

              // 1) Cambiar la URL primero
              if (push) history.pushState({ pjax: true, url: url }, '', url);

              // 2) Ahora sí, swap
              window.pageCleanup();
              window.limpiarModales();
              $contenido.empty();
              $contenido.html(nuevo.innerHTML);

              sincronizarEstilosDePagina(doc, url);
              reejecutarScriptsExtendido(doc, $contenido, url);
              actualizarMenuActivo(url);
              if (doc.title) document.title = doc.title;

              $contenido.scrollTop(0);
              $('html, body').scrollTop(0);
            });
          },
          error: function(xhr, status) {
            if (miToken !== tokenActual) return;
            if (status === 'abort') return;
            window.location.href = url;
          },
          complete: function() {
            clearTimeout(loaderTimer);
            /* fix #1: loader siempre con hide() balanceado */
            window.Loader.hide();
            window.__pjaxInFlight = Math.max(0, (window.__pjaxInFlight || 1) - 1);
            if (miToken === tokenActual) {
              ajaxActual = null;
            }
            window.__pollEnPausa = false;
            if (typeof window.revisionGlobalDeNotificaciones === 'function') {
              window.revisionGlobalDeNotificaciones(true);
            }
          }
        });
      }

      /* Sidebar persistente + limpieza al logout */
      (function() {
        var KEY = 'cpt_sidebar_collapse';
        if (localStorage.getItem(KEY) === '1') $('body').addClass('sidebar-collapse');

        $(document).on('click', '[data-toggle="offcanvas"]', function() {
          setTimeout(function() {
            localStorage.setItem(KEY, $('body').hasClass('sidebar-collapse') ? '1' : '0');
          }, 50);
        });

        $(document).on('click.logoutClean', '#confirmarSalida', function() {
          try {
            localStorage.removeItem(KEY);
            sessionStorage.removeItem('recetas_vistas_ids');
          } catch (e) {}
        });
      })();

      $(document).on('click', 'a', function(e) {
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        if (e.which !== 1) return;
        if (!esLinkPJAX(this)) return;
        e.preventDefault();
        cargarPagina(this.href, true);
      });

      window.addEventListener('popstate', function(e) {
        if (e.state && e.state.pjax) cargarPagina(e.state.url, false);
      });

      history.replaceState({
        pjax: true,
        url: location.href
      }, '', location.href);

      $(document).on('shown.bs.modal', '.modal', function() {
        $(this).find('input.form-control, select.form-control, textarea.form-control').addClass('form-control');
      });
    })(jQuery);
  }
</script>