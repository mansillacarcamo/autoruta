{{-- Contenido de un banner de publicidad: imagen, o video con su imagen encima (si tiene).
     - Con zona de video: la imagen se ve completa y el video se reproduce dentro de esa zona.
     - Sin zona: el video va de fondo y la imagen encima con la opacidad elegida en el admin.
     El contenedor debe tener position: relative (o absolute) para que todo calce. --}}
@php
    $zonaVideo = $banner->zonaVideo();
    $estiloOpacidad = ($banner->opacidad_capa ?? 100) < 100 ? 'opacity:' . ($banner->opacidad_capa / 100) : null;
@endphp
@if ($zonaVideo)
  <img src="{{ $banner->urlCapa() }}" class="banner-medio" alt="{{ $alt ?? 'Publicidad' }}">
  <video src="{{ $banner->url() }}" class="banner-zona" autoplay muted loop playsinline
         style="left:{{ $zonaVideo[0] }}%;top:{{ $zonaVideo[1] }}%;width:{{ $zonaVideo[2] }}%;height:{{ $zonaVideo[3] }}%"></video>
@elseif ($banner->tipo_medio === 'video')
  <video src="{{ $banner->url() }}" class="banner-medio" autoplay muted loop playsinline></video>
  @if ($capa = $banner->urlCapa())
    <img src="{{ $capa }}" class="banner-capa" alt="{{ $alt ?? 'Publicidad' }}" @if ($estiloOpacidad) style="{{ $estiloOpacidad }}" @endif>
  @endif
@else
  <img src="{{ $banner->url() }}" class="banner-medio" alt="{{ $alt ?? 'Publicidad' }}" loading="lazy" @if ($estiloOpacidad) style="{{ $estiloOpacidad }}" @endif>
@endif
