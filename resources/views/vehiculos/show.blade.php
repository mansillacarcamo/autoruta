@extends('layouts.app')

@section('titulo', "{$vehiculo->marca} {$vehiculo->modelo} {$vehiculo->anio} – {$vehiculo->precioFormateado()}")

@section('contenido')
<div class="contenedor" style="padding:32px 16px">
  <a href="{{ route('vehiculos.index') }}" style="font-size:14px;font-weight:600;color:#525252">← Volver al listado</a>

  @if (! $vehiculo->estaVisible())
    <p class="aviso-no-disponible">
      {{ $vehiculo->estado === 'vendida' ? 'Este vehículo ya fue vendido.' : 'Esta publicación ya no está vigente.' }}
      <a href="{{ route('vehiculos.index', ['tipo' => $vehiculo->tipo]) }}">Ver vehículos similares →</a>
    </p>
  @endif

  <div style="display:grid;gap:32px;margin-top:16px" class="ficha-grid">
    <div>
      @if ($vehiculo->fotos->isEmpty())
        <div class="tarjeta-foto" style="border-radius:12px"><img src="{{ $vehiculo->primeraFotoUrl() }}" alt=""></div>
      @else
        <div class="galeria" id="galeria">
          <div class="galeria-principal">
            <img id="galeriaFoto" src="{{ \App\Support\Archivos::url('vehiculos/' . $vehiculo->fotos->first()->archivo) }}" alt="{{ $vehiculo->marca }} {{ $vehiculo->modelo }}">
            <button type="button" class="galeria-flecha anterior" aria-label="Foto anterior">&#8249;</button>
            <button type="button" class="galeria-flecha siguiente" aria-label="Foto siguiente">&#8250;</button>
            <span class="galeria-contador" id="galeriaContador"></span>
            <button type="button" class="galeria-zoom" id="galeriaZoom" aria-label="Ver foto en grande y hacer zoom">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3M11 8v6M8 11h6"/></svg>
            </button>
          </div>
          <div class="galeria-miniaturas">
            @foreach ($vehiculo->fotos as $f)
              <button type="button" class="galeria-miniatura"><img src="{{ \App\Support\Archivos::url('vehiculos/' . $f->archivo) }}" alt=""></button>
            @endforeach
          </div>
        </div>

        <div class="visor" id="visor" hidden>
          <div class="visor-area" id="visorArea">
            <img id="visorFoto" src="" alt="{{ $vehiculo->marca }} {{ $vehiculo->modelo }}" draggable="false">
          </div>
          <button type="button" class="visor-cerrar" id="visorCerrar" aria-label="Cerrar">&times;</button>
          <button type="button" class="galeria-flecha anterior" id="visorAnterior" aria-label="Foto anterior">&#8249;</button>
          <button type="button" class="galeria-flecha siguiente" id="visorSiguiente" aria-label="Foto siguiente">&#8250;</button>
          <div class="visor-barra">
            <button type="button" id="visorMenos" aria-label="Alejar">&minus;</button>
            <span id="visorContador"></span>
            <button type="button" id="visorMas" aria-label="Acercar">+</button>
          </div>
          <p class="visor-ayuda">Rueda del mouse, doble clic o pellizca con dos dedos para hacer zoom</p>
        </div>
        <script>
          // Galería: foto grande con flechas, miniaturas y deslizar con el dedo. Las fotos que
          // no cargan (por ejemplo, perdidas antes del respaldo) se sacan de la galería.
          (function () {
            var galeria = document.getElementById('galeria');
            var grande = document.getElementById('galeriaFoto');
            var contador = document.getElementById('galeriaContador');
            var miniaturas = Array.from(galeria.querySelectorAll('.galeria-miniatura'));
            var actual = 0;

            function mostrar(i) {
              if (!miniaturas.length) return;
              actual = (i + miniaturas.length) % miniaturas.length;
              grande.src = miniaturas[actual].querySelector('img').src;
              miniaturas.forEach(function (m, j) { m.classList.toggle('activa', j === actual); });
              contador.textContent = (actual + 1) + ' / ' + miniaturas.length;
              galeria.classList.toggle('una-foto', miniaturas.length < 2);
            }

            miniaturas.forEach(function (m) {
              m.addEventListener('click', function () { mostrar(miniaturas.indexOf(m)); });
              var img = m.querySelector('img');
              function quitar() {
                var i = miniaturas.indexOf(m);
                if (i === -1) return;
                miniaturas.splice(i, 1);
                m.remove();
                if (!miniaturas.length) { grande.src = @json(asset('img/vehiculo-placeholder.svg')); galeria.classList.add('una-foto'); contador.textContent = ''; return; }
                mostrar(actual >= miniaturas.length ? 0 : (i < actual ? actual - 1 : actual));
              }
              img.addEventListener('error', quitar);
            });

            // Solo después de que la página terminó de cargar se puede saber con seguridad si una
            // foto falló (antes, en Safari del iPhone una foto que aún no carga parece rota).
            window.addEventListener('load', function () {
              miniaturas.slice().forEach(function (m) {
                var img = m.querySelector('img');
                if (img.complete && img.naturalWidth === 0) img.dispatchEvent(new Event('error'));
              });
            });

            galeria.querySelector('.anterior').addEventListener('click', function () { mostrar(actual - 1); });
            galeria.querySelector('.siguiente').addEventListener('click', function () { mostrar(actual + 1); });
            document.addEventListener('keydown', function (e) {
              var enVisor = !visor.hidden;
              if (e.key === 'Escape' && enVisor) cerrarVisor();
              if (e.key === 'ArrowLeft') enVisor ? cargarVisor(actual - 1) : mostrar(actual - 1);
              if (e.key === 'ArrowRight') enVisor ? cargarVisor(actual + 1) : mostrar(actual + 1);
            });

            // Visor a pantalla completa con zoom (rueda, doble clic/toque, pellizco y botones +/-).
            var visor = document.getElementById('visor');
            var area = document.getElementById('visorArea');
            var foto = document.getElementById('visorFoto');
            var escala = 1, px = 0, py = 0;

            function aplicar(animar) {
              foto.classList.toggle('sin-transicion', !animar);
              foto.style.transform = 'translate(' + px + 'px,' + py + 'px) scale(' + escala + ')';
              visor.classList.toggle('con-zoom', escala > 1);
            }
            function limitar() {
              var r = area.getBoundingClientRect();
              var maxX = r.width * (escala - 1) / 2, maxY = r.height * (escala - 1) / 2;
              px = Math.max(-maxX, Math.min(maxX, px));
              py = Math.max(-maxY, Math.min(maxY, py));
            }
            // cx/cy: punto (relativo al centro del visor) que debe quedar fijo al hacer zoom.
            function zoom(nueva, cx, cy, animar) {
              nueva = Math.max(1, Math.min(4, nueva));
              var k = nueva / escala;
              px = cx - k * (cx - px);
              py = cy - k * (cy - py);
              escala = nueva;
              if (escala === 1) { px = 0; py = 0; }
              limitar();
              aplicar(animar);
            }
            function relativo(clienteX, clienteY) {
              var r = area.getBoundingClientRect();
              return [clienteX - (r.left + r.width / 2), clienteY - (r.top + r.height / 2)];
            }
            function cargarVisor(i) {
              mostrar(i);
              foto.src = grande.src;
              escala = 1; px = 0; py = 0; aplicar(false);
              document.getElementById('visorContador').textContent = contador.textContent;
            }
            function abrirVisor() {
              if (!miniaturas.length) return;
              visor.hidden = false;
              document.body.style.overflow = 'hidden';
              visor.classList.toggle('una-foto', miniaturas.length < 2);
              cargarVisor(actual);
            }
            function cerrarVisor() {
              visor.hidden = true;
              document.body.style.overflow = '';
            }

            grande.addEventListener('click', abrirVisor);
            document.getElementById('galeriaZoom').addEventListener('click', abrirVisor);
            document.getElementById('visorCerrar').addEventListener('click', cerrarVisor);
            document.getElementById('visorAnterior').addEventListener('click', function () { cargarVisor(actual - 1); });
            document.getElementById('visorSiguiente').addEventListener('click', function () { cargarVisor(actual + 1); });
            document.getElementById('visorMas').addEventListener('click', function () { zoom(escala * 1.5, 0, 0, true); });
            document.getElementById('visorMenos').addEventListener('click', function () { zoom(escala / 1.5, 0, 0, true); });
            area.addEventListener('click', function (e) { if (e.target === area && escala === 1) cerrarVisor(); });

            area.addEventListener('wheel', function (e) {
              e.preventDefault();
              var p = relativo(e.clientX, e.clientY);
              zoom(escala * (e.deltaY < 0 ? 1.2 : 1 / 1.2), p[0], p[1], false);
            }, { passive: false });
            area.addEventListener('dblclick', function (e) {
              var p = relativo(e.clientX, e.clientY);
              zoom(escala > 1 ? 1 : 2.5, p[0], p[1], true);
            });

            var arrastre = null;
            area.addEventListener('mousedown', function (e) {
              if (escala === 1) return;
              e.preventDefault();
              arrastre = { x: e.clientX - px, y: e.clientY - py };
            });
            window.addEventListener('mousemove', function (e) {
              if (!arrastre) return;
              px = e.clientX - arrastre.x; py = e.clientY - arrastre.y;
              limitar(); aplicar(false);
            });
            window.addEventListener('mouseup', function () { arrastre = null; });

            var toque = null, ultimoToque = 0;
            function distancia(t) { return Math.hypot(t[0].clientX - t[1].clientX, t[0].clientY - t[1].clientY); }
            area.addEventListener('touchstart', function (e) {
              if (e.touches.length === 2) {
                toque = { pellizco: true, d: distancia(e.touches), escala: escala };
              } else if (e.touches.length === 1) {
                var t = e.touches[0];
                toque = { pellizco: false, x0: t.clientX, y0: t.clientY, x: t.clientX - px, y: t.clientY - py };
                var ahora = Date.now();
                if (ahora - ultimoToque < 300) {
                  var p = relativo(t.clientX, t.clientY);
                  zoom(escala > 1 ? 1 : 2.5, p[0], p[1], true);
                  toque = null;
                }
                ultimoToque = ahora;
              }
            }, { passive: true });
            area.addEventListener('touchmove', function (e) {
              if (!toque) return;
              e.preventDefault();
              if (toque.pellizco && e.touches.length === 2) {
                var medio = relativo((e.touches[0].clientX + e.touches[1].clientX) / 2, (e.touches[0].clientY + e.touches[1].clientY) / 2);
                zoom(toque.escala * distancia(e.touches) / toque.d, medio[0], medio[1], false);
              } else if (!toque.pellizco && escala > 1) {
                px = e.touches[0].clientX - toque.x; py = e.touches[0].clientY - toque.y;
                limitar(); aplicar(false);
              }
            }, { passive: false });
            area.addEventListener('touchend', function (e) {
              if (toque && !toque.pellizco && escala === 1 && e.changedTouches.length) {
                var dx = e.changedTouches[0].clientX - toque.x0;
                if (Math.abs(dx) > 50) cargarVisor(actual + (dx < 0 ? 1 : -1));
              }
              if (e.touches.length === 0) toque = null;
            });

            var inicioX = null;
            grande.addEventListener('touchstart', function (e) { inicioX = e.touches[0].clientX; }, { passive: true });
            grande.addEventListener('touchend', function (e) {
              if (inicioX === null) return;
              var dx = e.changedTouches[0].clientX - inicioX;
              if (Math.abs(dx) > 40) mostrar(actual + (dx < 0 ? 1 : -1));
              inicioX = null;
            });

            mostrar(0);
          })();
        </script>
      @endif

      <h1 class="mt-3">{{ $vehiculo->marca }} {{ $vehiculo->modelo }} {{ $vehiculo->anio }}</h1>
      <p class="tarjeta-precio" style="font-size:28px">{{ $vehiculo->precioFormateado() }}</p>
      <p class="texto-mutado">
        {{ \App\Models\Vehiculo::ETIQUETA_TIPO[$vehiculo->tipo] ?? $vehiculo->tipo }} ·
        {{ number_format($vehiculo->kilometraje, 0, ',', '.') }} km ·
        {{ $vehiculo->comuna }}, {{ $vehiculo->region }} ·
        {{ $vehiculo->vistas }} vistas
      </p>

      <h2 class="mt-3">Descripción</h2>
      <p style="white-space:pre-line">{{ $vehiculo->descripcion }}</p>

      @if ($ficha)
        <h2 class="mt-3">Ficha técnica</h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px">
          @foreach ($ficha as $etiqueta => $valor)
            <div class="caja">
              <p style="font-size:12px;color:#737373;margin:0">{{ $etiqueta }}</p>
              <p style="font-weight:600;margin:2px 0 0">{{ $valor }}</p>
            </div>
          @endforeach
        </div>
      @endif

      @if ($vehiculo->equipamiento)
        <h2 class="mt-3">Equipamiento</h2>
        <div style="display:flex;flex-wrap:wrap;gap:8px">
          @foreach (explode(',', $vehiculo->equipamiento) as $item)
            <span style="background:var(--gris-claro);border-radius:999px;padding:4px 12px;font-size:14px">{{ trim($item) }}</span>
          @endforeach
        </div>
      @endif
    </div>

    <aside class="ficha-lateral">
      <div class="vendedor">
        <div class="vendedor-cabecera">
          <small>{{ \App\Models\Vehiculo::ETIQUETA_TIPO[$vehiculo->tipo] ?? 'Vehículo' }} · {{ $vehiculo->anio }}</small>
          <p class="vendedor-vehiculo">{{ $vehiculo->marca }} {{ $vehiculo->modelo }}</p>
          <p class="vendedor-precio">{{ $vehiculo->precioFormateado() }}</p>
        </div>

        <div class="vendedor-cuerpo">
          <h2 class="vendedor-titulo">Contacta al vendedor</h2>
          <div class="vendedor-datos">
            <span class="vendedor-avatar">{{ mb_strtoupper(mb_substr($vehiculo->usuario->name, 0, 1)) }}</span>
            <div>
              <small>Publicado por</small>
              <strong>{{ $vehiculo->usuario->name }}</strong>
              <span>{{ $vehiculo->comuna }} · Miembro desde {{ $vehiculo->usuario->created_at->locale('es')->translatedFormat('F Y') }}</span>
            </div>
          </div>

          @if ($vehiculo->estaVisible())
          <a href="https://wa.me/{{ $numeroWa }}?text={{ $mensajeWa }}" target="_blank" rel="noopener" class="btn btn-block vendedor-whatsapp">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M17.5 14.4c-.3-.1-1.8-.9-2-1s-.5-.1-.7.1-.8 1-1 1.2-.4.2-.7.1a8.2 8.2 0 0 1-4-3.5c-.3-.5.3-.5.9-1.6.1-.2 0-.4 0-.5l-.9-2.2c-.2-.6-.5-.5-.7-.5h-.6a1.1 1.1 0 0 0-.8.4 3.4 3.4 0 0 0-1.1 2.5 5.9 5.9 0 0 0 1.2 3.1 13.4 13.4 0 0 0 5.2 4.6c1.9.8 2.7.9 3.6.7a3.1 3.1 0 0 0 2-1.4 2.5 2.5 0 0 0 .2-1.4c-.1-.1-.3-.2-.6-.3zM12 21.8a9.8 9.8 0 0 1-5-1.4l-.4-.2-3.7 1 1-3.6-.2-.4a9.8 9.8 0 1 1 8.3 4.6zm0-21.6A11.8 11.8 0 0 0 1.9 17.8L.2 24l6.4-1.7A11.8 11.8 0 1 0 12 .2z"/></svg>
            Contactar por WhatsApp
          </a>
          <a href="tel:+{{ $numeroWa }}" class="btn btn-block vendedor-llamar">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"/></svg>
            Llamar al vendedor
          </a>

          @else
            <p class="texto-mutado" style="font-size:14px;margin:0">{{ $vehiculo->estado === 'vendida' ? 'Vehículo vendido: el contacto ya no está disponible.' : 'Publicación no vigente: el contacto no está disponible.' }}</p>
          @endif

          <div class="vendedor-enlaces">
            <button type="button" id="compartirAviso">
              <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.6 13.5l6.8 4M15.4 6.5l-6.8 4"/></svg>
              <span>Compartir aviso</span>
            </button>
            <a href="mailto:{{ config('autoruta.contacto_email') }}?subject={{ urlencode('Denuncia de publicación ' . $vehiculo->id) }}">
              <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 22V4h13l-2 4 2 4H4"/></svg>
              Denunciar
            </a>
          </div>
        </div>

        <div class="vendedor-consejos">
          <strong>Compra seguro</strong>
          <ul>
            <li>Revisa el vehículo en persona antes de pagar.</li>
            <li>No transfieras dinero por adelantado.</li>
            <li>Verifica los papeles y el historial del vehículo.</li>
          </ul>
        </div>
      </div>
      <script>
        document.getElementById('compartirAviso').addEventListener('click', function () {
          var boton = this, datos = { title: document.title, url: location.href };
          if (navigator.share) { navigator.share(datos).catch(function () {}); return; }
          if (navigator.clipboard) navigator.clipboard.writeText(location.href).then(function () {
            boton.querySelector('span').textContent = '¡Enlace copiado!';
            setTimeout(function () { boton.querySelector('span').textContent = 'Compartir aviso'; }, 2000);
          });
        });
      </script>

      @if ($negocioDestacado)
        <a href="{{ $negocioDestacado->link_url }}" target="_blank" rel="noopener" class="caja mt-2" style="display:block;padding:0;overflow:hidden">
          <div style="aspect-ratio:16/9;background:var(--gris-claro)">
            @if ($negocioDestacado->tipo_medio === 'video')
              <video src="{{ $negocioDestacado->url() }}" style="width:100%;height:100%;object-fit:cover" autoplay muted loop playsinline></video>
            @else
              <img src="{{ $negocioDestacado->url() }}" style="width:100%;height:100%;object-fit:cover">
            @endif
          </div>
        </a>
      @endif
    </aside>
  </div>
</div>

<style>@media (min-width:900px){.ficha-grid{grid-template-columns:1fr 340px}}</style>
@endsection
