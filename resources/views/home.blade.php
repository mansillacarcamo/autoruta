@extends('layouts.app')

@section('contenido')
{{-- Banner principal: el diseño de AutoRuta va siempre primero; si en Admin > Portada se agregaron banners,
     se arma un slider (cada 6 s, con flechas, puntos y deslizar con el dedo). --}}
<section class="portada-banner">
  @php($bannerAutoruta = '<picture><source media="(max-width: 700px)" srcset="' . asset('img/banner-portada-movil.jpg') . '"><img src="' . asset('img/banner-portada.jpg') . '" width="2087" height="753" fetchpriority="high" alt="AutoRuta: ¿Buscas un auto o quieres vender? Esta es tu opción. www.autoruta.cl"></picture>')
  @if ($portada->isEmpty())
    {!! $bannerAutoruta !!}
  @else
    <div class="slider-pub slider-portada" data-slider-pub data-intervalo="6000" aria-roledescription="carrusel" aria-label="Banners principales">
      <div class="slider-pub-pista">
        <div class="slider-pub-slide">{!! $bannerAutoruta !!}</div>
        @foreach ($portada as $m)
          @php($etiqueta = $m->link_url ? 'a' : 'div')
          <{{ $etiqueta }} class="slider-pub-slide" inert @if ($m->link_url) href="{{ $m->link_url }}" target="_blank" rel="noopener" @endif>
            @if ($m->tipo_medio === 'video')
              <video src="{{ $m->url() }}" class="banner-medio" autoplay muted loop playsinline></video>
            @else
              <picture>
                @if ($m->urlMovil())<source media="(max-width: 700px)" srcset="{{ $m->urlMovil() }}">@endif
                <img src="{{ $m->url() }}" class="banner-medio" alt="Banner" loading="lazy">
              </picture>
            @endif
          </{{ $etiqueta }}>
        @endforeach
      </div>
      <button type="button" class="slider-pub-flecha anterior" aria-label="Anterior">&#8249;</button>
      <button type="button" class="slider-pub-flecha siguiente" aria-label="Siguiente">&#8250;</button>
      <div class="slider-pub-puntos">
        @foreach (range(0, $portada->count()) as $n)
          <button type="button" class="{{ $n === 0 ? 'activo' : '' }}" aria-label="Ir al banner {{ $n + 1 }}"></button>
        @endforeach
      </div>
    </div>
    @include('partials.slider-pub-script')
  @endif
</section>

{{-- Título solo para buscadores y lectores de pantalla: el banner ya lo dice visualmente. --}}
<h1 class="solo-lectores">Compra y vende tu vehículo en {{ config('autoruta.nombre_sitio') }}</h1>

<section class="seccion contenedor" style="padding-bottom:0">
  <h2>Explora por categoría</h2>
  @include('partials.categorias')
</section>

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
  {{-- En celular los autos van en lista (uno por fila) y se muestran los primeros; este botón lleva al resto --}}
  @if ($ultimos->count() > 8)<a href="{{ route('vehiculos.index') }}" class="btn btn-outline-oscuro ver-todos-movil">Ver todos los vehículos →</a>@endif
  @if ($ultimos->isEmpty())<p class="texto-mutado" id="sinVehiculos">Todavía no hay vehículos publicados.</p>@endif
  @include('partials.vehiculos-en-vivo', ['modo' => 'insertar', 'grilla' => 'grillaUltimos', 'desde' => (int) \App\Models\Vehiculo::max('id'), 'maxTarjetas' => 36])
</section>

@if ($destacados->isNotEmpty())
<section class="seccion contenedor">
  <div class="seccion-titulo">
    <h2>Destacados</h2>
    <a href="{{ route('vehiculos.index') }}">Ver todos →</a>
  </div>
    <div class="carrusel" data-carrusel>
      <button type="button" class="carrusel-flecha anterior" aria-label="Anterior">&#8249;</button>
      <div class="carrusel-pista carrusel-destacados">
        @foreach ($destacados as $v)
          @include('vehiculos._tarjeta', ['v' => $v])
        @endforeach
      </div>
      <button type="button" class="carrusel-flecha siguiente" aria-label="Siguiente">&#8250;</button>
    </div>
    @if ($destacados->count() > 4)<a href="{{ route('vehiculos.index') }}" class="btn btn-outline-oscuro ver-todos-movil">Ver todos los vehículos →</a>@endif
</section>
@endif

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

    // Paginación bajo el carrusel ("← Anterior 1 2 3 Siguiente →"): cada página es lo que cabe a la vista.
    var paginas = document.createElement('nav');
    paginas.className = 'paginacion carrusel-paginas';
    paginas.setAttribute('aria-label', 'Páginas de autos');
    carrusel.after(paginas);
    function columnasVisibles() { return Math.max(1, Math.round((pista.clientWidth + 16) / paso())); }
    function totalPaginas() { return Math.max(1, Math.ceil(Math.round((pista.scrollWidth + 16) / paso()) / columnasVisibles())); }
    function paginaActual() {
      if (pista.scrollLeft + pista.clientWidth >= pista.scrollWidth - 4) return totalPaginas() - 1; // al final: última página (suele venir incompleta)
      return Math.min(totalPaginas() - 1, Math.round(pista.scrollLeft / (paso() * columnasVisibles())));
    }
    function irAPagina(n) {
      pista.scrollTo({ left: n * paso() * columnasVisibles(), behavior: 'smooth' });
      dibujarPaginas(n); // marca la página elegida de inmediato, sin esperar a que termine el desplazamiento
    }
    function dibujarPaginas(elegida) {
      var total = totalPaginas(), actual = typeof elegida === 'number' ? elegida : paginaActual();
      paginas.hidden = total < 2;
      paginas.innerHTML = '';
      function boton(texto, n, clase, deshabilitado) {
        var b = document.createElement('button');
        b.type = 'button'; b.textContent = texto; b.className = clase || '';
        if (deshabilitado) b.disabled = true; else b.addEventListener('click', function () { irAPagina(n); });
        paginas.appendChild(b);
      }
      boton('← Anterior', actual - 1, 'pagina-flecha', actual === 0);
      for (var i = 0; i < total; i++) boton(String(i + 1), i, i === actual ? 'pagina-numero activa' : 'pagina-numero', false);
      boton('Siguiente →', actual + 1, 'pagina-flecha', actual >= total - 1);
    }
    var espera;
    pista.addEventListener('scroll', function () { clearTimeout(espera); espera = setTimeout(function () { dibujarPaginas(); }, 150); });

    new MutationObserver(function () { actualizarFlechas(); dibujarPaginas(); }).observe(pista, { childList: true });
    window.addEventListener('resize', function () { actualizarFlechas(); dibujarPaginas(); });
    actualizarFlechas();
    dibujarPaginas();
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
      </div>
    @endforeach
  </div>
</section>
@endif

@include('partials.slider-publicidad')

@include('partials.popup-inicio')
@endsection
