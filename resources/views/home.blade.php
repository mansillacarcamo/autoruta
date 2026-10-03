@extends('layouts.app')

@section('contenido')
{{-- Banner de portada completo, sin texto encima (el banner ya trae su mensaje). Si en Admin se subieron
     fotos o video de portada, se muestran esos en el mismo marco; si no, el banner fijo de AutoRuta. --}}
<section class="portada-banner">
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
  @else
    <picture>
      <source media="(max-width: 700px)" srcset="{{ asset('img/banner-portada-movil.jpg') }}">
      <img src="{{ asset('img/banner-portada.jpg') }}" width="2087" height="753" fetchpriority="high"
           alt="AutoRuta: ¿Buscas un auto o quieres vender? Esta es tu opción. www.autoruta.cl">
    </picture>
  @endif
</section>

{{-- Título solo para buscadores y lectores de pantalla: el banner ya lo dice visualmente. --}}
<h1 class="solo-lectores">Compra y vende tu vehículo en {{ config('autoruta.nombre_sitio') }}</h1>

<section class="seccion contenedor" style="padding-bottom:0">
  <h2>Explora por categoría</h2>
  @include('partials.categorias')
</section>

@include('partials.banners-laterales-movil', ['fila' => 1])

<section class="seccion contenedor">
  <div class="seccion-titulo">
    <h2>Últimos publicados</h2>
    <a href="{{ route('vehiculos.index') }}">Ver todos →</a>
  </div>
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
  @include('partials.vehiculos-en-vivo', ['modo' => 'insertar', 'grilla' => 'grillaUltimos', 'desde' => (int) \App\Models\Vehiculo::max('id'), 'maxTarjetas' => 36])
</section>

@include('partials.banners-laterales-movil', ['fila' => 2])

@if ($destacados->isNotEmpty())
<section class="seccion contenedor">
  <div class="seccion-titulo">
    <h2>Destacados</h2>
    <a href="{{ route('vehiculos.index') }}">Ver todos →</a>
  </div>
    <div class="carrusel" data-carrusel>
      <button type="button" class="carrusel-flecha anterior" aria-label="Anterior">&#8249;</button>
      <div class="carrusel-pista">
        @foreach ($destacados as $v)
          @include('vehiculos._tarjeta', ['v' => $v])
        @endforeach
      </div>
      <button type="button" class="carrusel-flecha siguiente" aria-label="Siguiente">&#8250;</button>
    </div>
</section>
@endif

@include('partials.banners-laterales-movil', ['fila' => 4])



<script>
  // Carruseles de Destacados y Últimos publicados: se mueven con las flechas o deslizando.
  // Lo que cambia solo son las fotos de cada tarjeta (ver más abajo).
  document.querySelectorAll('[data-carrusel]').forEach(function (carrusel) {
    var pista = carrusel.querySelector('.carrusel-pista');
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
    carrusel.querySelector('.anterior').addEventListener('click', function () { mover(-1); });
    carrusel.querySelector('.siguiente').addEventListener('click', function () { mover(1); });
    new MutationObserver(actualizarFlechas).observe(pista, { childList: true });
    window.addEventListener('resize', actualizarFlechas);
    actualizarFlechas();
  });

  // Cada tarjeta pasa sus propias fotos, a su propio ritmo, solo mientras está en pantalla.
  // Se detiene mientras el mouse está encima para que la persona pueda mirarla tranquila.
  (function () {
    var visibles = new WeakSet();
    var observador = 'IntersectionObserver' in window ? new IntersectionObserver(function (entradas) {
      entradas.forEach(function (e) { if (e.isIntersecting) visibles.add(e.target); else visibles.delete(e.target); });
    }) : null;

    function iniciar(tarjeta) {
      var img = tarjeta.querySelector('.tarjeta-foto > img[data-fotos]');
      if (!img || tarjeta.dataset.fotosRotan) return;
      var fotos = [];
      try { fotos = JSON.parse(img.dataset.fotos || '[]'); } catch (e) {}
      if (fotos.length < 2) return;
      tarjeta.dataset.fotosRotan = '1';
      if (observador) observador.observe(tarjeta); else visibles.add(tarjeta);
      var actual = 0, encima = false;
      tarjeta.addEventListener('mouseenter', function () { encima = true; });
      tarjeta.addEventListener('mouseleave', function () { encima = false; });

      function programar(espera) { setTimeout(cambiar, espera); }
      function cambiar() {
        if (!tarjeta.isConnected) return;
        if (document.hidden || encima || !visibles.has(tarjeta) || fotos.length < 2) return programar(1500);
        var siguiente = (actual + 1) % fotos.length;
        var carga = new Image();
        carga.onload = function () {
          // Giro de carta: la foto se voltea hasta quedar de canto, cambia y termina de girar.
          var sale = img.animate(
            [{ transform: 'perspective(800px) rotateY(0deg)' }, { transform: 'perspective(800px) rotateY(90deg)' }],
            { duration: 320, easing: 'ease-in', fill: 'forwards' }
          );
          sale.onfinish = function () {
            img.src = fotos[siguiente];
            actual = siguiente;
            sale.cancel();
            img.animate(
              [{ transform: 'perspective(800px) rotateY(-90deg)' }, { transform: 'perspective(800px) rotateY(0deg)' }],
              { duration: 320, easing: 'ease-out' }
            );
            programar(3500 + Math.random() * 3000);
          };
        };
        carga.onerror = function () { fotos.splice(siguiente, 1); programar(500); };
        carga.src = fotos[siguiente];
      }
      programar(1500 + Math.random() * 4500);
    }

    function revisar() { document.querySelectorAll('[data-carrusel] .tarjeta').forEach(iniciar); }
    revisar();
    document.querySelectorAll('[data-carrusel] .carrusel-pista').forEach(function (pista) {
      new MutationObserver(revisar).observe(pista, { childList: true });
    });
  })();
</script>

@if ($bannersInicio->isNotEmpty())
<section class="seccion contenedor">
  <div class="seccion-titulo">
    <h2>Auspiciado por</h2>
    <a href="{{ route('como-funciona') }}">Anuncia tu negocio →</a>
  </div>
  <div class="grilla" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr))">
    @foreach ($bannersInicio as $b)
      <div>
        <a href="{{ $b->urlClic() }}" target="_blank" rel="sponsored noopener"
           style="display:block;position:relative;border-radius:12px;overflow:hidden;border:1px solid #e5e5e5;aspect-ratio:16/9;background:var(--gris-claro)">
          @include('partials.banner-medio', ['banner' => $b, 'alt' => $b->anunciante->nombre_negocio])
        </a>
        @include('partials.banner-botones', ['banner' => $b, 'fila' => true])
      </div>
    @endforeach
  </div>
</section>
@endif

@include('partials.banners-laterales-movil', ['fila' => 3])

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

@include('partials.banners-laterales-movil', ['fila' => 5])

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
@include('partials.popup-inicio')
@endsection
