@php
    // Slider publicitario del inicio: hasta 4 avisos (posiciones slider_1 … slider_4, 1200 × 330 px).
    // Sin avisos contratados se muestra la invitación a publicar un vehículo.
    $posicionesSlider = ['slider_1', 'slider_2', 'slider_3', 'slider_4'];
    $bannersSlider = \App\Models\AnuncianteBanner::whereIn('posicion', $posicionesSlider)
        ->whereHas('anunciante', fn ($q) => $q->activos())
        ->orderBy('orden')
        ->get();
    $slides = collect($posicionesSlider)->map(fn ($p) => $bannersSlider->firstWhere('posicion', $p))->filter()->values();
@endphp
<section class="seccion contenedor">
  @if ($slides->isEmpty())
    <div class="promo">
      <span class="promo-badge">100% GRATIS</span>
      <h2>¿Tienes un vehículo para vender?</h2>
      <p>Publica gratis en minutos, sin comisión por venta.</p>
      <ul class="promo-beneficios">
        <li>Sin comisión</li>
        <li>Hasta {{ config('autoruta.max_fotos_vehiculo') }} fotos</li>
        <li>Contacto directo por WhatsApp</li>
      </ul>
      <a href="{{ route('register') }}" class="btn btn-acento promo-boton">Publicar mi vehículo →</a>
    </div>
  @else
    <div class="slider-pub" data-slider-pub aria-roledescription="carrusel" aria-label="Publicidad">
      <div class="slider-pub-pista">
        @foreach ($slides as $i => $slide)
          <a href="{{ $slide->urlClic() }}" target="_blank" rel="sponsored noopener" class="slider-pub-slide"
             @if ($i > 0) inert @endif aria-label="Publicidad {{ $i + 1 }} de {{ $slides->count() }}">
            @include('partials.banner-medio', ['banner' => $slide, 'alt' => $slide->anunciante?->nombre_negocio ?? 'Publicidad'])
          </a>
        @endforeach
      </div>
      @if ($slides->count() > 1)
        <button type="button" class="slider-pub-flecha anterior" aria-label="Anterior">&#8249;</button>
        <button type="button" class="slider-pub-flecha siguiente" aria-label="Siguiente">&#8250;</button>
        <div class="slider-pub-puntos">
          @foreach ($slides as $i => $slide)
            <button type="button" class="{{ $i === 0 ? 'activo' : '' }}" aria-label="Ir a la publicidad {{ $i + 1 }}"></button>
          @endforeach
        </div>
      @endif
      <span class="slider-pub-etiqueta">Publicidad</span>
    </div>
    @include('partials.slider-pub-script')
  @endif
</section>
