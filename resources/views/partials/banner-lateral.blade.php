@php
    // Tres posiciones por lado: lateral_izquierdo, lateral_izquierdo_2, lateral_izquierdo_3 (ídem derecho).
    // En escritorio se muestran en un solo espacio fijo (sticky) que rota entre los avisos contratados,
    // así los tres anunciantes reciben la misma exposición.
    $posicionesLado = ['lateral_' . $lado, 'lateral_' . $lado . '_2', 'lateral_' . $lado . '_3'];
    $bannersLado = \App\Models\AnuncianteBanner::whereIn('posicion', array_merge($posicionesLado, ['lateral']))
        ->whereHas('anunciante', fn ($q) => $q->activos())
        ->orderBy('orden')
        ->get();
    $rotacion = collect($posicionesLado)
        ->map(fn ($posicion, $i) => $bannersLado->firstWhere('posicion', $posicion)
            ?? ($i === 0 ? $bannersLado->firstWhere('posicion', 'lateral') : null))
        ->filter()
        ->values();
@endphp
<div class="banner-lateral-slot">
  <div class="banner-lateral-columna">
    @if ($rotacion->isEmpty())
      @include('partials.espacio-publicitario', ['estilo' => 'width:160px;aspect-ratio:160/600', 'posicion' => $posicionesLado[0]])
    @else
      <div class="banner-lateral-rotador banner-lateral-rotador--{{ $lado }}" data-rotador>
        @foreach ($rotacion as $i => $bannerLateral)
          <a href="{{ $bannerLateral->urlClic() }}" target="_blank" rel="sponsored noopener"
             class="banner-lateral-item{{ $i === 0 ? ' activo' : '' }}" @if ($i > 0) tabindex="-1" aria-hidden="true" @endif>
            @if ($bannerLateral->tipo_medio === 'video')
              <video src="{{ $bannerLateral->url() }}" muted loop playsinline @if ($i === 0) autoplay @endif></video>
            @else
              <img src="{{ $bannerLateral->url() }}" alt="Publicidad" @if ($i > 0) loading="lazy" @endif>
            @endif
          </a>
        @endforeach
      </div>
    @endif
    <p class="banner-lateral-titulo">Publicidad</p>
  </div>
</div>

@once
<script>
  // Rota los avisos laterales cada 7 segundos; se pausa con el mouse encima o con la pestaña oculta.
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-rotador]').forEach(function (rotador) {
      var items = rotador.querySelectorAll('.banner-lateral-item');
      if (items.length < 2) return;
      var actual = 0, pausado = false;
      rotador.addEventListener('mouseenter', function () { pausado = true; });
      rotador.addEventListener('mouseleave', function () { pausado = false; });
      setInterval(function () {
        if (pausado || document.hidden) return;
        var anterior = items[actual];
        actual = (actual + 1) % items.length;
        var siguiente = items[actual];
        anterior.classList.remove('activo');
        anterior.classList.add('saliendo');
        // Terminada la salida, vuelve a su posición de entrada sin animarse (ya está oculto).
        setTimeout(function () {
          anterior.style.transition = 'none';
          anterior.classList.remove('saliendo');
          void anterior.offsetWidth;
          anterior.style.transition = '';
        }, 800);
        anterior.setAttribute('tabindex', '-1');
        anterior.setAttribute('aria-hidden', 'true');
        var videoAnterior = anterior.querySelector('video');
        if (videoAnterior) videoAnterior.pause();
        siguiente.classList.add('activo');
        siguiente.removeAttribute('tabindex');
        siguiente.removeAttribute('aria-hidden');
        var videoSiguiente = siguiente.querySelector('video');
        if (videoSiguiente) videoSiguiente.play().catch(function () {});
      }, 7000);
    });
  });
</script>
@endonce
