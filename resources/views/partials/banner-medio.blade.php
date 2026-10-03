{{-- Contenido de un banner de publicidad: imagen, o video con su capa transparente encima (si tiene).
     El contenedor debe tener position: relative (o absolute) para que la capa calce sobre el video. --}}
@if ($banner->tipo_medio === 'video')
  <video src="{{ $banner->url() }}" class="banner-medio" autoplay muted loop playsinline></video>
  @if ($capa = $banner->urlCapa())
    <img src="{{ $capa }}" class="banner-capa" alt="{{ $alt ?? 'Publicidad' }}"
         @if (($banner->opacidad_capa ?? 100) < 100) style="opacity:{{ $banner->opacidad_capa / 100 }}" @endif>
  @endif
@else
  <img src="{{ $banner->url() }}" class="banner-medio" alt="{{ $alt ?? 'Publicidad' }}" loading="lazy">
@endif
