{{-- Contenido de un banner de publicidad: imagen, o video con su logo encima (si tiene).
     El contenedor debe tener position: relative para que el logo quede sobre el video. --}}
@if ($banner->tipo_medio === 'video')
  <video src="{{ $banner->url() }}" class="banner-medio" autoplay muted loop playsinline></video>
  @if ($logo = $banner->urlLogo())
    <img src="{{ $logo }}" class="banner-logo" alt="{{ $alt ?? 'Logo' }}">
  @endif
@else
  <img src="{{ $banner->url() }}" class="banner-medio" alt="{{ $alt ?? 'Publicidad' }}" loading="lazy">
@endif
