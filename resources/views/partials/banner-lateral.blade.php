@php
    $bannersLaterales = \App\Models\AnuncianteBanner::where('posicion', 'lateral')
        ->whereHas('anunciante', fn ($q) => $q->activos())
        ->orderBy('orden')
        ->get();
    $bannerLateral = $lado === 'izquierdo' ? $bannersLaterales->first() : ($bannersLaterales->get(1) ?? $bannersLaterales->first());
@endphp
<div class="banner-lateral-slot">
  <div style="position:sticky;top:104px">
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
      @include('partials.espacio-publicitario', ['estilo' => 'width:160px;aspect-ratio:160/600'])
    @endif
  </div>
</div>
