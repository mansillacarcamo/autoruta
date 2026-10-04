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
    <script>
      // Avanza cada 6 s; flechas, puntos y deslizar con el dedo. Se pausa con el mouse encima o la pestaña oculta.
      (function () {
        var slider = document.querySelector('[data-slider-pub]');
        var slides = slider.querySelectorAll('.slider-pub-slide');
        if (slides.length < 2) return;
        var pista = slider.querySelector('.slider-pub-pista'), puntos = slider.querySelectorAll('.slider-pub-puntos button');
        var actual = 0, pausado = false, inicioX = null;

        function ir(n) {
          actual = (n + slides.length) % slides.length;
          pista.style.transform = 'translateX(' + (-actual * 100) + '%)';
          slides.forEach(function (s, i) { s.inert = i !== actual; });
          puntos.forEach(function (p, i) { p.classList.toggle('activo', i === actual); });
        }

        slider.querySelector('.anterior').addEventListener('click', function () { ir(actual - 1); });
        slider.querySelector('.siguiente').addEventListener('click', function () { ir(actual + 1); });
        puntos.forEach(function (p, i) { p.addEventListener('click', function () { ir(i); }); });
        slider.addEventListener('mouseenter', function () { pausado = true; });
        slider.addEventListener('mouseleave', function () { pausado = false; });
        slider.addEventListener('touchstart', function (e) { inicioX = e.touches[0].clientX; }, { passive: true });
        slider.addEventListener('touchend', function (e) {
          if (inicioX === null) return;
          var dx = e.changedTouches[0].clientX - inicioX;
          if (Math.abs(dx) > 40) ir(actual + (dx < 0 ? 1 : -1));
          inicioX = null;
        });
        setInterval(function () { if (!pausado && !document.hidden) ir(actual + 1); }, 6000);
      })();
    </script>
  @endif
</section>
