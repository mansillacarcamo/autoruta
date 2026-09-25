@extends('layouts.app')

@section('contenido')
<section class="hero">
  <div class="contenedor">
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

<section class="seccion contenedor">
  <div class="seccion-titulo">
    <h2>Destacados</h2>
    <a href="{{ route('vehiculos.index') }}">Ver todos →</a>
  </div>
  <div class="grilla">
    @forelse ($destacados as $v)
      @include('vehiculos._tarjeta', ['v' => $v])
    @empty
      <p class="texto-mutado">Todavía no hay vehículos publicados.</p>
    @endforelse
  </div>
</section>

<section class="seccion contenedor">
  <h2>Últimos publicados</h2>
  <div class="grilla">
    @foreach ($ultimos as $v)
      @include('vehiculos._tarjeta', ['v' => $v])
    @endforeach
  </div>
</section>

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
  <div class="caja" style="background:linear-gradient(135deg, var(--carbon), var(--carbon-claro)); color:#fff; text-align:center; padding:40px;">
    <h2 style="color:#fff">¿Tienes un vehículo para vender?</h2>
    <p style="color:#d4d4d4">Publica gratis en minutos, sin comisión por venta.</p>
    <a href="{{ route('register') }}" class="btn btn-acento" style="margin-top:16px;padding:14px 28px;font-size:16px">Publicar mi vehículo →</a>
  </div>
</section>
@endsection
