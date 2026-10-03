@php
    $bannerAncho = \App\Models\AnuncianteBanner::where('posicion', $posicion)
        ->whereHas('anunciante', fn ($q) => $q->activos())
        ->with('anunciante')
        ->orderBy('orden')
        ->first();
@endphp
<div class="banner-ancho-slot" style="display:flex;flex-direction:column;align-items:center;padding:16px">
  @if ($bannerAncho)
    <a href="{{ $bannerAncho->urlClic() }}" target="_blank" rel="sponsored noopener"
       style="display:block;position:relative;width:100%;max-width:728px;border-radius:12px;overflow:hidden;border:1px solid #e5e5e5;aspect-ratio:728/90;background:var(--gris-claro)">
      @include('partials.banner-medio', ['banner' => $bannerAncho])
    </a>
  @else
    @include('partials.espacio-publicitario', ['estilo' => 'width:100%;max-width:728px;aspect-ratio:728/90', 'posicion' => $posicion])
  @endif
</div>
