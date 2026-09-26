@php
    $bannerAncho = \App\Models\AnuncianteBanner::where('posicion', $posicion)
        ->whereHas('anunciante', fn ($q) => $q->activos())
        ->orderBy('orden')
        ->first();
@endphp
<div style="display:flex;justify-content:center;padding:16px">
  @if ($bannerAncho)
    <a href="{{ $bannerAncho->link_url }}" target="_blank" rel="noopener"
       style="display:block;width:100%;max-width:728px;border-radius:12px;overflow:hidden;border:1px solid #e5e5e5;aspect-ratio:728/90;background:var(--gris-claro)">
      @if ($bannerAncho->tipo_medio === 'video')
        <video src="{{ $bannerAncho->url() }}" style="width:100%;height:100%;object-fit:cover" autoplay muted loop playsinline></video>
      @else
        <img src="{{ $bannerAncho->url() }}" style="width:100%;height:100%;object-fit:cover">
      @endif
    </a>
  @else
    @include('partials.espacio-publicitario', ['estilo' => 'width:100%;max-width:728px;aspect-ratio:728/90'])
  @endif
</div>
