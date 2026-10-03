@php
    // Cinco posiciones por lado: lateral_izquierdo, lateral_izquierdo_2 … lateral_izquierdo_5 (ídem derecho).
    // La columna se divide en tramos a lo largo de la página: cada aviso queda fijo (sticky) mientras se
    // recorre su tramo y luego lo reemplaza el siguiente. Si la página es corta y no caben todos los tramos,
    // el script de abajo junta los avisos sobrantes en los tramos que sí caben y los hace rotar.
    $posicionesLado = \App\Models\Anunciante::posicionesLaterales($lado);
    $bannersLado = \App\Models\AnuncianteBanner::whereIn('posicion', array_merge($posicionesLado, ['lateral']))
        ->whereHas('anunciante', fn ($q) => $q->activos())
        ->with('anunciante')
        ->orderBy('orden')
        ->get();
@endphp
<div class="banner-lateral-slot">
  <div class="banner-lateral-columna banner-lateral-columna--{{ $lado }}" data-columna-lateral>
    @foreach ($posicionesLado as $i => $posicionLateral)
      @php
        $bannerLateral = $bannersLado->firstWhere('posicion', $posicionLateral)
            ?? ($i === 0 ? $bannersLado->firstWhere('posicion', 'lateral') : null);
      @endphp
      <div class="banner-lateral-tramo">
        <div class="banner-lateral-pegado">
          @if ($bannerLateral)
            <div class="banner-lateral-rotador" data-rotador>
              <div class="banner-lateral-item activo">
                <a href="{{ $bannerLateral->urlClic() }}" target="_blank" rel="sponsored noopener" class="banner-lateral-media">
                  @include('partials.banner-medio', ['banner' => $bannerLateral])
                </a>
              </div>
            </div>
            <p class="banner-lateral-titulo">Publicidad</p>
          @else
            @include('partials.espacio-publicitario', ['estilo' => 'width:160px;aspect-ratio:160/600', 'posicion' => $posicionLateral])
          @endif
        </div>
      </div>
    @endforeach
  </div>
</div>

@once
<script>
  (function () {
    var ALTO_TRAMO = 660; // aviso 160×600 + rótulo + separación

    function crear(tag, clase) { var el = document.createElement(tag); el.className = clase; return el; }

    // Arma los tramos según cuántos caben en el alto de la columna: primero los avisos contratados
    // (repartidos en ronda si sobran) y después, si queda espacio, los "Espacio disponible".
    function distribuir(columna) {
      if (!columna.offsetParent) return; // columna oculta (pantalla chica)
      var caben = Math.max(1, Math.floor(columna.clientHeight / ALTO_TRAMO));
      if (columna._caben === caben) return;
      columna._caben = caben;

      var reales = columna._reales, vacios = columna._vacios;
      var nReales = Math.min(reales.length, caben);
      var nVacios = Math.min(vacios.length, caben - nReales);
      columna.innerHTML = '';

      for (var t = 0; t < nReales; t++) {
        var tramo = crear('div', 'banner-lateral-tramo');
        var pegado = crear('div', 'banner-lateral-pegado');
        var rotador = crear('div', 'banner-lateral-rotador');
        rotador.setAttribute('data-rotador', '');
        for (var i = t; i < reales.length; i += nReales) {
          var item = reales[i], activo = i === t;
          item.classList.remove('saliendo');
          item.classList.toggle('activo', activo);
          item.inert = !activo;
          rotador.appendChild(item);
          var video = item.querySelector('video');
          if (video) { if (activo) video.play().catch(function () {}); else video.pause(); }
        }
        var titulo = crear('p', 'banner-lateral-titulo');
        titulo.textContent = 'Publicidad';
        pegado.appendChild(rotador);
        pegado.appendChild(titulo);
        tramo.appendChild(pegado);
        columna.appendChild(tramo);
      }
      for (var v = 0; v < nVacios; v++) {
        var tramoVacio = crear('div', 'banner-lateral-tramo');
        var pegadoVacio = crear('div', 'banner-lateral-pegado');
        pegadoVacio.appendChild(vacios[v]);
        tramoVacio.appendChild(pegadoVacio);
        columna.appendChild(tramoVacio);
      }
    }

    // Avanza cada rotador con más de un aviso; se pausa con el mouse encima o con la pestaña oculta.
    function rotar() {
      if (document.hidden) return;
      document.querySelectorAll('[data-rotador]').forEach(function (rotador) {
        var items = rotador.querySelectorAll('.banner-lateral-item');
        if (items.length < 2 || rotador.matches(':hover')) return;
        var actual = Array.prototype.findIndex.call(items, function (el) { return el.classList.contains('activo'); });
        var anterior = items[Math.max(actual, 0)], siguiente = items[(actual + 1) % items.length];
        anterior.classList.remove('activo');
        anterior.classList.add('saliendo');
        // Terminada la salida, vuelve a su posición de entrada sin animarse (ya está oculto).
        setTimeout(function () {
          anterior.style.transition = 'none';
          anterior.classList.remove('saliendo');
          void anterior.offsetWidth;
          anterior.style.transition = '';
        }, 800);
        anterior.inert = true;
        var videoAnterior = anterior.querySelector('video');
        if (videoAnterior) videoAnterior.pause();
        siguiente.classList.add('activo');
        siguiente.inert = false;
        var videoSiguiente = siguiente.querySelector('video');
        if (videoSiguiente) videoSiguiente.play().catch(function () {});
      });
    }

    document.addEventListener('DOMContentLoaded', function () {
      document.querySelectorAll('[data-columna-lateral]').forEach(function (columna) {
        columna._reales = Array.prototype.slice.call(columna.querySelectorAll('.banner-lateral-item'));
        columna._vacios = Array.prototype.slice.call(columna.querySelectorAll('.espacio-publicitario'));
        distribuir(columna);
        // La página crece al cargar fotos o llegar autos nuevos: se recalculan los tramos.
        if (window.ResizeObserver) new ResizeObserver(function () { distribuir(columna); }).observe(columna);
      });
      setInterval(rotar, 7000);
    });
  })();
</script>
@endonce
