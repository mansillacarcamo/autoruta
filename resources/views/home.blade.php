@extends('layouts.app')

@section('contenido')
<section class="hero {{ $portada->isNotEmpty() ? 'hero-con-medios' : '' }}">
  @if ($portada->isNotEmpty())
    <div class="hero-medios">
      @if ($video = $portada->firstWhere('tipo_medio', 'video'))
        <video src="{{ $video->url() }}" autoplay muted loop playsinline></video>
      @else
        @foreach ($portada as $i => $m)
          <img src="{{ $m->url() }}" alt="" class="{{ $i === 0 ? 'activa' : '' }}">
        @endforeach
      @endif
    </div>
  @endif
  <div class="contenedor hero-contenido">
    <h1>Compra y vende tu vehículo en {{ config('autoruta.nombre_sitio') }}</h1>
    <p>Publica gratis en minutos. Miles de compradores en toda Chile.</p>
    <form class="buscador" action="{{ route('vehiculos.index') }}" method="get">
      <input type="text" name="q" placeholder="Marca o modelo (ej. Toyota Hilux)">
      <select name="tipo">
        <option value="">Tipo de vehículo</option>
        @foreach (\App\Models\Vehiculo::ETIQUETA_TIPO as $valor => $etiqueta)
          <option value="{{ $valor }}">{{ $etiqueta }}</option>
        @endforeach
      </select>
      <button type="submit" class="btn btn-acento">Buscar</button>
    </form>
  </div>
</section>

<section class="seccion contenedor" style="padding-bottom:0">
  <h2>Explora por categoría</h2>
  @include('partials.categorias')
</section>

<section class="seccion contenedor">
  <div class="seccion-titulo">
    <h2>Destacados</h2>
    <a href="{{ route('vehiculos.index') }}">Ver todos →</a>
  </div>
  @if ($destacados->isEmpty())
    <p class="texto-mutado">Todavía no hay vehículos publicados.</p>
  @else
    <div class="carrusel" data-carrusel>
      <button type="button" class="carrusel-flecha anterior" aria-label="Anterior">&#8249;</button>
      <div class="carrusel-pista">
        @foreach ($destacados as $v)
          @include('vehiculos._tarjeta', ['v' => $v])
        @endforeach
      </div>
      <button type="button" class="carrusel-flecha siguiente" aria-label="Siguiente">&#8250;</button>
    </div>
  @endif
</section>

<section class="seccion contenedor">
  <h2>Últimos publicados</h2>
  <div class="carrusel" data-carrusel>
    <button type="button" class="carrusel-flecha anterior" aria-label="Anterior">&#8249;</button>
    <div class="carrusel-pista" id="grillaUltimos">
      @foreach ($ultimos as $v)
        @include('vehiculos._tarjeta', ['v' => $v])
      @endforeach
    </div>
    <button type="button" class="carrusel-flecha siguiente" aria-label="Siguiente">&#8250;</button>
  </div>
  @if ($ultimos->isEmpty())<p class="texto-mutado" id="sinVehiculos">Todavía no hay vehículos publicados.</p>@endif
  @include('partials.vehiculos-en-vivo', ['modo' => 'insertar', 'grilla' => 'grillaUltimos', 'desde' => (int) \App\Models\Vehiculo::max('id'), 'maxTarjetas' => 12])
</section>

<script>
  // Carruseles de Destacados y Últimos publicados: avanzan solos cada 4 s y se pausan
  // mientras la persona interactúa.
  document.querySelectorAll('[data-carrusel]').forEach(function (carrusel) {
    var pista = carrusel.querySelector('.carrusel-pista');
    var pausaHasta = 0;
    function paso() {
      var tarjeta = pista.querySelector('.tarjeta');
      return tarjeta ? tarjeta.getBoundingClientRect().width + 16 : pista.clientWidth;
    }
    function mover(direccion) {
      var alFinal = pista.scrollLeft + pista.clientWidth >= pista.scrollWidth - 4;
      if (direccion > 0 && alFinal) pista.scrollTo({ left: 0, behavior: 'smooth' });
      else pista.scrollBy({ left: direccion * paso(), behavior: 'smooth' });
    }
    function actualizarFlechas() {
      carrusel.classList.toggle('sin-desplazamiento', pista.scrollWidth <= pista.clientWidth + 4);
    }
    carrusel.querySelector('.anterior').addEventListener('click', function () { pausaHasta = Date.now() + 8000; mover(-1); });
    carrusel.querySelector('.siguiente').addEventListener('click', function () { pausaHasta = Date.now() + 8000; mover(1); });
    ['pointerdown', 'wheel', 'touchstart'].forEach(function (ev) {
      pista.addEventListener(ev, function () { pausaHasta = Date.now() + 8000; }, { passive: true });
    });
    carrusel.addEventListener('mouseenter', function () { pausaHasta = Infinity; });
    carrusel.addEventListener('mouseleave', function () { pausaHasta = Date.now() + 2000; });
    new MutationObserver(actualizarFlechas).observe(pista, { childList: true });
    window.addEventListener('resize', actualizarFlechas);
    actualizarFlechas();
    setInterval(function () {
      if (document.hidden || Date.now() < pausaHasta || carrusel.classList.contains('sin-desplazamiento')) return;
      mover(1);
    }, 4000);
  });
</script>

@if ($bannersInicio->isNotEmpty())
<section class="seccion contenedor">
  <div class="seccion-titulo">
    <h2>Auspiciado por</h2>
    <a href="{{ route('como-funciona') }}">Anuncia tu negocio →</a>
  </div>
  <div class="grilla" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr))">
    @foreach ($bannersInicio as $b)
      <a href="{{ $b->link_url }}" target="_blank" rel="noopener"
         style="display:block;border-radius:12px;overflow:hidden;border:1px solid #e5e5e5;aspect-ratio:16/9;background:var(--gris-claro)">
        @if ($b->tipo_medio === 'video')
          <video src="{{ $b->url() }}" style="width:100%;height:100%;object-fit:cover" autoplay muted loop playsinline></video>
        @else
          <img src="{{ $b->url() }}" style="width:100%;height:100%;object-fit:cover" alt="{{ $b->anunciante->nombre_negocio }}">
        @endif
      </a>
    @endforeach
  </div>
</section>
@endif

<section class="seccion contenedor">
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
</section>

<script>
  (function () {
    var fotos = document.querySelectorAll('.hero-medios img');
    if (fotos.length < 2) return;
    var i = 0;
    setInterval(function () {
      fotos[i].classList.remove('activa');
      i = (i + 1) % fotos.length;
      fotos[i].classList.add('activa');
    }, 5000);
  })();
</script>
@endsection
