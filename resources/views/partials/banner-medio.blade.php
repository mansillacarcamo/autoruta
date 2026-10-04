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
          {{-- autoplay + preload en todos: en celulares un video oculto sin autoplay no se descarga ni se deja reproducir --}}
          <video src="{{ $urlMedio }}" class="banner-medio" autoplay muted loop playsinline preload="auto"></video>
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
  // Si el teléfono no deja reproducir el video (p. ej. modo de bajo consumo), se queda en la imagen.
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-alterna]').forEach(function (caja) {
      var capas = Array.prototype.slice.call(caja.querySelectorAll('.banner-alterna-capa')), actual = 0;
      capas.forEach(function (capa) {
        var video = capa.querySelector('video');
        if (video) { video.muted = true; }
      });

      function mostrar(indice) {
        var anterior = capas[actual], nueva = capas[indice];
        actual = indice;
        nueva.classList.add('activa');
        if (anterior !== nueva) anterior.classList.remove('activa');
        var video = nueva.querySelector('video');
        if (video) {
          try { video.currentTime = 0; } catch (e) {}
          var intento = video.play();
          if (intento && intento.catch) intento.catch(function () { quitarCapa(nueva); });
        }
      }

      function quitarCapa(capa) {
        if (capas.length < 2) return;
        capas = capas.filter(function (c) { return c !== capa; });
        capa.classList.remove('activa');
        actual = 0;
        capas[0].classList.add('activa');
      }

      function siguiente() {
        if (document.hidden || capas.length < 2) { setTimeout(siguiente, 1000); return; }
        mostrar((actual + 1) % capas.length);
        setTimeout(siguiente, (Number(capas[actual].dataset.segundos) || 3) * 1000);
      }
      setTimeout(siguiente, (Number(capas[0].dataset.segundos) || 3) * 1000);
    });
  });
</script>
@endonce
