{{-- Versión celular/tablet de los banners laterales (en pantallas grandes van a los costados).
     $fila 1, 2 o 3: muestra "Lateral izquierdo N" y "Lateral derecho N" uno al lado del otro. --}}
@php
    $sufijo = $fila > 1 ? '_' . $fila : '';
    $posicionesFila = ['lateral_izquierdo' . $sufijo, 'lateral_derecho' . $sufijo];
    $bannersFila = \App\Models\AnuncianteBanner::whereIn('posicion', array_merge($posicionesFila, $fila === 1 ? ['lateral'] : []))
        ->whereHas('anunciante', fn ($q) => $q->activos())
        ->orderBy('orden')
        ->get();
    $parFila = [
        $bannersFila->firstWhere('posicion', $posicionesFila[0]) ?? ($fila === 1 ? $bannersFila->firstWhere('posicion', 'lateral') : null),
        $bannersFila->firstWhere('posicion', $posicionesFila[1]),
    ];
@endphp
<div class="laterales-movil">
  @if ($parFila[0] || $parFila[1])
    <p class="laterales-movil-titulo">Publicidad</p>
    <div class="laterales-movil-par">
      @foreach ($posicionesFila as $i => $posicionFila)
        @if ($bannerFila = $parFila[$i])
          <a href="{{ $bannerFila->urlClic() }}" target="_blank" rel="sponsored noopener" class="laterales-movil-banner">
            @if ($bannerFila->tipo_medio === 'video')
              <video src="{{ $bannerFila->url() }}" autoplay muted loop playsinline></video>
            @else
              <img src="{{ $bannerFila->url() }}" alt="Publicidad" loading="lazy">
            @endif
          </a>
        @else
          @include('partials.espacio-publicitario', ['estilo' => 'width:100%;aspect-ratio:160/600', 'posicion' => $posicionFila])
        @endif
      @endforeach
    </div>
  @else
    <a class="espacio-publicitario laterales-movil-vacio" target="_blank" rel="noopener"
       href="https://wa.me/{{ preg_replace('/\D/', '', config('autoruta.contacto_whatsapp')) }}?text={{ urlencode('Hola, quiero publicitar en AutoRuta (Laterales ' . $fila . ')') }}">
      @include('partials.auto-en-movimiento')
      <span class="ep-disponible">Disponible</span>
      <strong>Espacio disponible</strong>
      <span class="ep-posicion">Laterales {{ $fila }}</span>
      <span class="ep-medida">160 × 600 px</span>
      <span>Contáctanos</span>
    </a>
  @endif
</div>
