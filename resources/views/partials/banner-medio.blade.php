{{-- Contenido de un banner de publicidad: imagen, o video con su capa transparente encima (si tiene).
     La opacidad elegida en el admin se aplica a la imagen (sola o encima del video).
     El contenedor debe tener position: relative (o absolute) para que la capa calce sobre el video. --}}
@php($estiloOpacidad = ($banner->opacidad_capa ?? 100) < 100 ? 'opacity:' . ($banner->opacidad_capa / 100) : null)
@if ($banner->tipo_medio === 'video')
  <video src="{{ $banner->url() }}" class="banner-medio" autoplay muted loop playsinline></video>
  @if ($capa = $banner->urlCapa())
    <img src="{{ $capa }}" class="banner-capa" alt="{{ $alt ?? 'Publicidad' }}" @if ($estiloOpacidad) style="{{ $estiloOpacidad }}" @endif>
  @endif
@else
  <img src="{{ $banner->url() }}" class="banner-medio" alt="{{ $alt ?? 'Publicidad' }}" loading="lazy" @if ($estiloOpacidad) style="{{ $estiloOpacidad }}" @endif>
@endif
