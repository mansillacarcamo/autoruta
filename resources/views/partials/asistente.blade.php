{{-- Asistente virtual (abajo a la derecha): el visitante escribe qué auto busca y se lo busca en los avisos. --}}
@if (config('autoruta.asistente.api_key'))
<div class="asistente" id="asistente" data-url="{{ route('asistente') }}" data-token="{{ csrf_token() }}">
  <section class="asistente-panel" id="asistentePanel" hidden role="dialog" aria-label="Asistente virtual de {{ config('autoruta.nombre_sitio') }}">
    <header class="asistente-cabecera">
      <div>
        <strong>Asistente {{ config('autoruta.nombre_sitio') }}</strong>
        <small>Te ayudo a encontrar tu auto</small>
      </div>
      <button type="button" class="asistente-cerrar" data-asistente="cerrar" aria-label="Cerrar">&times;</button>
    </header>
    <div class="asistente-mensajes" id="asistenteMensajes" aria-live="polite">
      <div class="asistente-msj asistente-msj-bot">¡Hola! 👋 ¿Qué auto buscas? Escríbeme la marca, el tipo, tu presupuesto o la comuna y te lo busco.</div>
    </div>
    <form class="asistente-form" id="asistenteForm">
      <input type="text" id="asistenteTexto" maxlength="500" autocomplete="off" placeholder="Ej: camioneta 4x4 diésel hasta 15 millones" aria-label="¿Qué auto buscas?">
      <button type="submit" class="btn btn-acento" aria-label="Enviar">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M3 20.5 21 12 3 3.5v6.6l12 1.9-12 1.9z"/></svg>
      </button>
    </form>
  </section>
  <button type="button" class="asistente-burbuja" data-asistente="abrir" aria-controls="asistentePanel" aria-expanded="false">
    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
    ¿Qué auto buscas?
  </button>
</div>
<script>
  (function () {
    var raiz = document.getElementById('asistente');
    var panel = document.getElementById('asistentePanel');
    var lista = document.getElementById('asistenteMensajes');
    var form = document.getElementById('asistenteForm');
    var campo = document.getElementById('asistenteTexto');
    var burbuja = raiz.querySelector('.asistente-burbuja');
    var historial = [];
    var ocupado = false;

    function abrir() {
      panel.hidden = false;
      burbuja.hidden = true;
      burbuja.setAttribute('aria-expanded', 'true');
      campo.focus();
    }
    function cerrar() {
      panel.hidden = true;
      burbuja.hidden = false;
      burbuja.setAttribute('aria-expanded', 'false');
    }
    window.abrirAsistente = abrir;

    function agregar(clase, texto) {
      var div = document.createElement('div');
      div.className = 'asistente-msj ' + clase;
      div.textContent = texto;
      lista.appendChild(div);
      lista.scrollTop = lista.scrollHeight;
      return div;
    }
    function tarjetas(vehiculos) {
      if (!vehiculos || !vehiculos.length) return;
      var cont = document.createElement('div');
      cont.className = 'asistente-autos';
      vehiculos.forEach(function (v) {
        var a = document.createElement('a');
        a.href = v.url;
        a.className = 'asistente-auto';
        var img = document.createElement('img');
        img.src = v.foto; img.alt = v.titulo; img.loading = 'lazy';
        var info = document.createElement('div');
        var t = document.createElement('strong'); t.textContent = v.titulo;
        var p = document.createElement('span'); p.className = 'asistente-auto-precio'; p.textContent = v.precio;
        var d = document.createElement('small'); d.textContent = v.detalle;
        info.append(t, p, d);
        a.append(img, info);
        cont.appendChild(a);
      });
      lista.appendChild(cont);
      lista.scrollTop = lista.scrollHeight;
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var texto = campo.value.trim();
      if (!texto || ocupado) return;
      campo.value = '';
      agregar('asistente-msj-usuario', texto);
      historial.push({ rol: 'user', texto: texto });
      ocupado = true;
      var escribiendo = agregar('asistente-msj-bot asistente-escribiendo', 'Buscando…');

      fetch(raiz.dataset.url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': raiz.dataset.token },
        body: JSON.stringify({ mensajes: historial.slice(-20) })
      })
        .then(function (r) { return r.json().catch(function () { return {}; }).then(function (j) { j._status = r.status; return j; }); })
        .then(function (j) {
          escribiendo.remove();
          if (j.respuesta) {
            agregar('asistente-msj-bot', j.respuesta);
            historial.push({ rol: 'assistant', texto: j.respuesta });
            tarjetas(j.vehiculos);
          } else {
            historial.pop();
            agregar('asistente-msj-bot asistente-msj-error', j._status === 429 ? 'Vas muy rápido 😅 Espera un minuto y vuelve a escribirme.' : (j.error || 'No pude buscar ahora. Intenta de nuevo.'));
          }
        })
        .catch(function () {
          escribiendo.remove();
          historial.pop();
          agregar('asistente-msj-bot asistente-msj-error', 'No hay conexión. Intenta de nuevo.');
        })
        .finally(function () { ocupado = false; campo.focus(); });
    });

    raiz.querySelector('[data-asistente="abrir"]').addEventListener('click', abrir);
    raiz.querySelector('[data-asistente="cerrar"]').addEventListener('click', cerrar);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !panel.hidden) cerrar(); });
  })();
</script>
@endif
