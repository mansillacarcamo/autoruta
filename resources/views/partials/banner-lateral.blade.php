@php
    // Tres espacios apilados por lado: lateral_izquierdo, lateral_izquierdo_2, lateral_izquierdo_3 (ídem derecho).
    $posicionesLado = ['lateral_' . $lado, 'lateral_' . $lado . '_2', 'lateral_' . $lado . '_3'];
    $bannersLado = \App\Models\AnuncianteBanner::whereIn('posicion', array_merge($posicionesLado, ['lateral']))
        ->whereHas('anunciante', fn ($q) => $q->activos())
        ->orderBy('orden')
        ->get();
@endphp
<div class="banner-lateral-slot">
  <div class="banner-lateral-columna">
    @foreach ($posicionesLado as $i => $posicionLateral)
      @php
        $bannerLateral = $bannersLado->firstWhere('posicion', $posicionLateral)
            ?? ($i === 0 ? $bannersLado->firstWhere('posicion', 'lateral') : null);
      @endphp
      @if ($bannerLateral)
        <a href="{{ $bannerLateral->link_url }}" target="_blank" rel="noopener"
           style="display:block;width:160px;border-radius:12px;overflow:hidden;border:1px solid #e5e5e5;aspect-ratio:160/600;background:var(--gris-claro)">
          @if ($bannerLateral->tipo_medio === 'video')
            <video src="{{ $bannerLateral->url() }}" style="width:100%;height:100%;object-fit:cover" autoplay muted loop playsinline></video>
          @else
            <img src="{{ $bannerLateral->url() }}" style="width:100%;height:100%;object-fit:cover">
          @endif
        </a>
      @else
        @include('partials.espacio-publicitario', ['estilo' => 'width:160px;aspect-ratio:160/600', 'posicion' => $posicionLateral])
      @endif
    @endforeach
  </div>
</div>
