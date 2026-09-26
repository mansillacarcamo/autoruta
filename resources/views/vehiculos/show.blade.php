@extends('layouts.app')

@section('titulo', "{$vehiculo->marca} {$vehiculo->modelo} {$vehiculo->anio} – {$vehiculo->precioFormateado()}")

@section('contenido')
<div class="contenedor" style="padding:32px 16px">
  <a href="{{ route('vehiculos.index') }}" style="font-size:14px;font-weight:600;color:#525252">← Volver al listado</a>

  <div style="display:grid;gap:32px;margin-top:16px" class="ficha-grid">
    <div>
      @if ($vehiculo->fotos->isEmpty())
        <div class="tarjeta-foto" style="border-radius:12px"><img src="{{ $vehiculo->primeraFotoUrl() }}" alt=""></div>
      @else
        <div class="galeria" id="galeria">
          <div class="galeria-principal">
            <img id="galeriaFoto" src="{{ \App\Support\Archivos::url('vehiculos/' . $vehiculo->fotos->first()->archivo) }}" alt="{{ $vehiculo->marca }} {{ $vehiculo->modelo }}">
            <button type="button" class="galeria-flecha anterior" aria-label="Foto anterior">&#8249;</button>
            <button type="button" class="galeria-flecha siguiente" aria-label="Foto siguiente">&#8250;</button>
            <span class="galeria-contador" id="galeriaContador"></span>
          </div>
          <div class="galeria-miniaturas">
            @foreach ($vehiculo->fotos as $f)
              <button type="button" class="galeria-miniatura"><img src="{{ \App\Support\Archivos::url('vehiculos/' . $f->archivo) }}" alt="" loading="lazy"></button>
            @endforeach
          </div>
        </div>
        <script>
          // Galería: foto grande con flechas, miniaturas y deslizar con el dedo. Las fotos que
          // no cargan (por ejemplo, perdidas antes del respaldo) se sacan de la galería.
          (function () {
            var galeria = document.getElementById('galeria');
            var grande = document.getElementById('galeriaFoto');
            var contador = document.getElementById('galeriaContador');
            var miniaturas = Array.from(galeria.querySelectorAll('.galeria-miniatura'));
            var actual = 0;

            function mostrar(i) {
              if (!miniaturas.length) return;
              actual = (i + miniaturas.length) % miniaturas.length;
              grande.src = miniaturas[actual].querySelector('img').src;
              miniaturas.forEach(function (m, j) { m.classList.toggle('activa', j === actual); });
              contador.textContent = (actual + 1) + ' / ' + miniaturas.length;
              galeria.classList.toggle('una-foto', miniaturas.length < 2);
            }

            miniaturas.forEach(function (m) {
              m.addEventListener('click', function () { mostrar(miniaturas.indexOf(m)); });
              var img = m.querySelector('img');
              function quitar() {
                var i = miniaturas.indexOf(m);
                if (i === -1) return;
                miniaturas.splice(i, 1);
                m.remove();
                if (!miniaturas.length) { grande.src = @json(asset('img/vehiculo-placeholder.svg')); galeria.classList.add('una-foto'); contador.textContent = ''; return; }
                mostrar(actual >= miniaturas.length ? 0 : (i < actual ? actual - 1 : actual));
              }
              img.addEventListener('error', quitar);
              if (img.complete && img.naturalWidth === 0) quitar();
            });

            galeria.querySelector('.anterior').addEventListener('click', function () { mostrar(actual - 1); });
            galeria.querySelector('.siguiente').addEventListener('click', function () { mostrar(actual + 1); });
            document.addEventListener('keydown', function (e) {
              if (e.key === 'ArrowLeft') mostrar(actual - 1);
              if (e.key === 'ArrowRight') mostrar(actual + 1);
            });

            var inicioX = null;
            grande.addEventListener('touchstart', function (e) { inicioX = e.touches[0].clientX; }, { passive: true });
            grande.addEventListener('touchend', function (e) {
              if (inicioX === null) return;
              var dx = e.changedTouches[0].clientX - inicioX;
              if (Math.abs(dx) > 40) mostrar(actual + (dx < 0 ? 1 : -1));
              inicioX = null;
            });

            mostrar(0);
          })();
        </script>
      @endif

      <h1 class="mt-3">{{ $vehiculo->marca }} {{ $vehiculo->modelo }} {{ $vehiculo->anio }}</h1>
      <p class="tarjeta-precio" style="font-size:28px">{{ $vehiculo->precioFormateado() }}</p>
      <p class="texto-mutado">
        {{ \App\Models\Vehiculo::ETIQUETA_TIPO[$vehiculo->tipo] ?? $vehiculo->tipo }} ·
        {{ number_format($vehiculo->kilometraje, 0, ',', '.') }} km ·
        {{ $vehiculo->comuna }}, {{ $vehiculo->region }} ·
        {{ $vehiculo->vistas }} vistas
      </p>

      <h2 class="mt-3">Descripción</h2>
      <p style="white-space:pre-line">{{ $vehiculo->descripcion }}</p>

      @if ($ficha)
        <h2 class="mt-3">Ficha técnica</h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px">
          @foreach ($ficha as $etiqueta => $valor)
            <div class="caja">
              <p style="font-size:12px;color:#737373;margin:0">{{ $etiqueta }}</p>
              <p style="font-weight:600;margin:2px 0 0">{{ $valor }}</p>
            </div>
          @endforeach
        </div>
      @endif

      @if ($vehiculo->equipamiento)
        <h2 class="mt-3">Equipamiento</h2>
        <div style="display:flex;flex-wrap:wrap;gap:8px">
          @foreach (explode(',', $vehiculo->equipamiento) as $item)
            <span style="background:var(--gris-claro);border-radius:999px;padding:4px 12px;font-size:14px">{{ trim($item) }}</span>
          @endforeach
        </div>
      @endif
    </div>

    <aside style="height:fit-content">
      <div class="caja">
        <p style="font-weight:600">{{ $vehiculo->usuario->name }}</p>
        <p class="texto-mutado" style="font-size:14px">{{ $vehiculo->comuna }}</p>
        <a href="https://wa.me/{{ $numeroWa }}?text={{ $mensajeWa }}" target="_blank" rel="noopener"
           class="btn btn-block mt-2" style="background:#25D366;color:#fff">Contactar por WhatsApp</a>
        <a href="mailto:{{ config('autoruta.contacto_email') }}?subject={{ urlencode('Denuncia de publicación ' . $vehiculo->id) }}"
           class="btn btn-outline btn-block mt-1" style="color:#525252;border-color:#d4d4d4">Denunciar</a>
      </div>

      @if ($negocioDestacado)
        <a href="{{ $negocioDestacado->link_url }}" target="_blank" rel="noopener" class="caja mt-2" style="display:block;padding:0;overflow:hidden">
          <div style="aspect-ratio:16/9;background:var(--gris-claro)">
            @if ($negocioDestacado->tipo_medio === 'video')
              <video src="{{ $negocioDestacado->url() }}" style="width:100%;height:100%;object-fit:cover" autoplay muted loop playsinline></video>
            @else
              <img src="{{ $negocioDestacado->url() }}" style="width:100%;height:100%;object-fit:cover">
            @endif
          </div>
        </a>
      @endif
    </aside>
  </div>
</div>

<style>@media (min-width:900px){.ficha-grid{grid-template-columns:1fr 340px}}</style>
@endsection
