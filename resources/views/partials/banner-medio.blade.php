{{-- Contenido de un banner de publicidad: imagen, o video con su logo encima (si tiene).
     Si el banner tiene un segundo archivo, ambos se alternan cada 3 segundos con un fundido.
     El contenedor debe tener position: relative para que todo quede superpuesto. --}}
@php
    $mediosBanner = [[$banner->tipo_medio, $banner->url()]];
    if ($banner->urlAlterno()) {
        $mediosBanner[] = [$banner->tipo_alterno === 'video' ? 'video' : 'imagen', $banner->urlAlterno()];
    }
@endphp
@if (count($mediosBanner) > 1)
  <div class="banner-alterna" data-alterna>
    @foreach ($mediosBanner as $i => [$tipoMedio, $urlMedio])
      <div class="banner-alterna-capa{{ $i === 0 ? ' activa' : '' }}">
        @if ($tipoMedio === 'video')
          <video src="{{ $urlMedio }}" class="banner-medio" muted loop playsinline @if ($i === 0) autoplay @endif></video>
        @else
          <img src="{{ $urlMedio }}" class="banner-medio" alt="{{ $alt ?? 'Publicidad' }}" loading="lazy">
        @endif
      </div>
    @endforeach
  </div>
@elseif ($banner->tipo_medio === 'video')
  <video src="{{ $banner->url() }}" class="banner-medio" autoplay muted loop playsinline></video>
@else
  <img src="{{ $banner->url() }}" class="banner-medio" alt="{{ $alt ?? 'Publicidad' }}" loading="lazy">
@endif
@if ($logo = $banner->urlLogo())
  <img src="{{ $logo }}" class="banner-logo" alt="{{ $alt ?? 'Logo' }}">
@endif

@once
<script>
  // Banners con dos archivos: cambia entre imagen y video cada 3 segundos; el video parte desde el inicio.
  document.addEventListener('DOMContentLoaded', function () {
    setInterval(function () {
      if (document.hidden) return;
      document.querySelectorAll('[data-alterna]').forEach(function (caja) {
        var capas = caja.querySelectorAll('.banner-alterna-capa');
        var actual = Array.prototype.findIndex.call(capas, function (c) { return c.classList.contains('activa'); });
        var anterior = capas[Math.max(actual, 0)], siguiente = capas[(actual + 1) % capas.length];
        var videoAnterior = anterior.querySelector('video'), videoSiguiente = siguiente.querySelector('video');
        if (videoSiguiente) { videoSiguiente.currentTime = 0; videoSiguiente.play().catch(function () {}); }
        anterior.classList.remove('activa');
        siguiente.classList.add('activa');
        if (videoAnterior) setTimeout(function () { videoAnterior.pause(); }, 600);
      });
    }, 3000);
  });
</script>
@endonce
