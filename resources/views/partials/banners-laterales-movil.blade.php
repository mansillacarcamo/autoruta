{{-- Versión celular/tablet de los banners laterales (en pantallas grandes van a los costados).
     Todos los avisos laterales se juntan en una sola sección al final de la página, intercalando
     izquierdo y derecho (izq. 1, der. 1, izq. 2, der. 2…). Los espacios vacíos no se muestran. --}}
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
    $avisosMovil = $avisosMovil->filter()->values();
@endphp
<div class="laterales-movil">
  @if ($avisosMovil->isNotEmpty())
    <p class="laterales-movil-titulo">Publicidad</p>
    <div class="laterales-movil-grilla">
      @foreach ($avisosMovil as $bannerMovil)
        <a href="{{ $bannerMovil->urlClic() }}" target="_blank" rel="sponsored noopener" class="laterales-movil-banner">
          @include('partials.banner-medio', ['banner' => $bannerMovil])
        </a>
      @endforeach
    </div>
  @else
    <a class="espacio-publicitario laterales-movil-vacio" target="_blank" rel="noopener"
       href="https://wa.me/{{ preg_replace('/\D/', '', config('autoruta.contacto_whatsapp')) }}?text={{ urlencode('Hola, quiero publicitar en AutoRuta (espacios laterales)') }}">
      <span class="ep-disponible">Disponible</span>
      <strong>Publicita aquí</strong>
      <span>Contáctanos</span>
    </a>
  @endif
</div>
