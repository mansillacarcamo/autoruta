{{-- Contenido de un banner de publicidad: imagen, o video con su logo encima (si tiene).
     Si el banner tiene un segundo archivo, ambos se alternan con un fundido, cada uno los segundos elegidos en el admin.
     El contenedor debe tener position: relative para que todo quede superpuesto. --}}
@php
    $mediosBanner = [[$banner->tipo_medio, $banner->url(), $banner->segundos_principal ?: 3]];
    if ($banner->urlAlterno()) {
        $mediosBanner[] = [$banner->tipo_alterno === 'video' ? 'video' : 'imagen', $banner->urlAlterno(), $banner->segundos_alterno ?: 3];
    }
@endphp
@if (count($mediosBanner) > 1)
  <div class="banner-alterna" data-alterna>
    @foreach ($mediosBanner as $i => [$tipoMedio, $urlMedio, $segundos])
      <div class="banner-alterna-capa{{ $i === 0 ? ' activa' : '' }}" data-segundos="{{ $segundos }}">
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
  // Banners con dos archivos: cada uno se muestra los segundos elegidos en el admin; el video parte desde el inicio.
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-alterna]').forEach(function (caja) {
      var capas = caja.querySelectorAll('.banner-alterna-capa'), actual = 0;
      function siguiente() {
        if (document.hidden) { setTimeout(siguiente, 1000); return; }
        var anterior = capas[actual];
        actual = (actual + 1) % capas.length;
        var nueva = capas[actual], videoAnterior = anterior.querySelector('video'), videoNuevo = nueva.querySelector('video');
        if (videoNuevo) { videoNuevo.currentTime = 0; videoNuevo.play().catch(function () {}); }
        anterior.classList.remove('activa');
        nueva.classList.add('activa');
        if (videoAnterior) setTimeout(function () { videoAnterior.pause(); }, 600);
        setTimeout(siguiente, (Number(nueva.dataset.segundos) || 3) * 1000);
      }
      setTimeout(siguiente, (Number(capas[0].dataset.segundos) || 3) * 1000);
    });
  });
</script>
@endonce
