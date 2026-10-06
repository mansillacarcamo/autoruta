{{-- Versión celular/tablet de los banners laterales (en pantallas grandes van a los costados).
     Todos los avisos laterales se juntan al final de la página en un slider de rectángulos 300 × 250,
     de a uno, intercalando izquierdo y derecho (izq. 1, der. 1, izq. 2, der. 2…).
     Solo se muestran los avisos que tienen "versión celular": los verticales largos no se ven bien en el teléfono. --}}
@php
    $posicionesIzq = \App\Models\Anunciante::posicionesLaterales('izquierdo');
    $posicionesDer = \App\Models\Anunciante::posicionesLaterales('derecho');
    $bannersMovil = \App\Models\AnuncianteBanner::whereIn('posicion', array_merge($posicionesIzq, $posicionesDer, ['lateral']))
        ->whereHas('anunciante', fn ($q) => $q->activos())
        ->orderBy('orden')
        ->get();
    $avisosMovil = collect();
    foreach (array_map(null, $posicionesIzq, $posicionesDer) as $n => [$izq, $der]) {
        $avisosMovil->push($bannersMovil->firstWhere('posicion', $izq) ?? ($n === 0 ? $bannersMovil->firstWhere('posicion', 'lateral') : null));
        $avisosMovil->push($bannersMovil->firstWhere('posicion', $der));
    }
    $avisosMovil = $avisosMovil->filter(fn ($b) => $b && $b->urlMovil())->values();
@endphp
<div class="laterales-movil">
  @if ($avisosMovil->isNotEmpty())
    <p class="laterales-movil-titulo">Publicidad</p>
    <div class="slider-pub slider-pub-movil" data-slider-pub data-intervalo="5000" aria-roledescription="carrusel" aria-label="Publicidad">
      <div class="slider-pub-pista">
        @foreach ($avisosMovil as $i => $bannerMovil)
          <a href="{{ $bannerMovil->urlClic() }}" target="_blank" rel="sponsored noopener" class="slider-pub-slide"
             @if ($i > 0) inert @endif aria-label="Publicidad {{ $i + 1 }} de {{ $avisosMovil->count() }}">
            @if ($bannerMovil->tipo_movil === 'video')
              <video src="{{ $bannerMovil->urlMovil() }}" class="banner-medio" autoplay muted loop playsinline></video>
            @else
              <img src="{{ $bannerMovil->urlMovil() }}" class="banner-medio" alt="Publicidad" loading="lazy">
            @endif
          </a>
        @endforeach
      </div>
      @if ($avisosMovil->count() > 1)
        <div class="slider-pub-puntos">
          @foreach ($avisosMovil as $i => $bannerMovil)
            <button type="button" class="{{ $i === 0 ? 'activo' : '' }}" aria-label="Ir a la publicidad {{ $i + 1 }}"></button>
          @endforeach
        </div>
      @endif
    </div>
    @include('partials.slider-pub-script')
  @else
    <a class="espacio-publicitario laterales-movil-vacio" target="_blank" rel="noopener"
       href="https://wa.me/{{ preg_replace('/\D/', '', config('autoruta.contacto_whatsapp')) }}?text={{ urlencode('Hola, quiero publicitar en AutoRuta (espacios laterales)') }}">
      <span class="ep-disponible">Disponible</span>
      <strong>Publicita aquí</strong>
      <span>Contáctanos</span>
    </a>
  @endif
</div>
