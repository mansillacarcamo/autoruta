@extends('layouts.app')
@section('titulo', $seo['titulo'])
@section('descripcion', $seo['descripcion'])
@section('canonical', $seo['canonical'])
@section('robots', $seo['robots'])

@section('contenido')
<div class="contenedor contenedor-pagina">
  <h1>
    {{ $seo['lugar'] ? 'Autos usados en venta en ' . $seo['lugar'] : 'Vehículos en venta' }}
    @if (array_key_exists((string) request('tipo'), \App\Models\Vehiculo::ETIQUETA_TIPO))
      <span style="color:var(--acento)">· {{ \App\Models\Vehiculo::ETIQUETA_TIPO[request('tipo')] }}</span>
    @endif
  </h1>
  @if ($seo['lugar'])
    <p class="texto-mutado" style="max-width:780px;margin-top:6px">
      Autos, camionetas, SUV y motos usados publicados en {{ $seo['lugar'] }} por particulares y automotoras de la zona.
      Contacta directo al vendedor por WhatsApp, sin intermediarios ni comisiones.
      ¿Quieres vender? <a href="{{ route('vender') }}">Publica tu vehículo gratis</a>.
    </p>
  @endif
  <div class="mt-2">@include('partials.categorias')</div>

  <div style="margin-top:24px">
    <div>
      <p class="texto-mutado">{{ $vehiculos->total() }} {{ $vehiculos->total() === 1 ? 'vehículo encontrado' : 'vehículos encontrados' }}</p>
      @if ($vehiculos->isEmpty())
        <p class="caja texto-mutado" style="text-align:center;border-style:dashed">No hay vehículos que coincidan con estos filtros.</p>
      @else
        <div class="grilla grilla-autos">
          @foreach ($vehiculos as $v)
            @include('vehiculos._tarjeta', ['v' => $v])
          @endforeach
        </div>
        @if ($vehiculos->hasPages())
          <nav class="paginacion" aria-label="Páginas">
            @if ($vehiculos->onFirstPage())<span class="deshabilitado">← Anterior</span>@else<a href="{{ $vehiculos->previousPageUrl() }}">← Anterior</a>@endif
            {{-- Números de página: la actual ±2, más la primera y la última --}}
            @php($desdePag = max(1, $vehiculos->currentPage() - 2))
            @php($hastaPag = min($vehiculos->lastPage(), $vehiculos->currentPage() + 2))
            @if ($desdePag > 1)<a href="{{ $vehiculos->url(1) }}" class="pagina-numero">1</a>@if ($desdePag > 2)<span class="pagina-puntos">…</span>@endif @endif
            @foreach ($vehiculos->getUrlRange($desdePag, $hastaPag) as $numero => $urlPagina)
              @if ($numero === $vehiculos->currentPage())<span class="pagina-numero activa" aria-current="page">{{ $numero }}</span>@else<a href="{{ $urlPagina }}" class="pagina-numero">{{ $numero }}</a>@endif
            @endforeach
            @if ($hastaPag < $vehiculos->lastPage())@if ($hastaPag < $vehiculos->lastPage() - 1)<span class="pagina-puntos">…</span>@endif<a href="{{ $vehiculos->url($vehiculos->lastPage()) }}" class="pagina-numero">{{ $vehiculos->lastPage() }}</a>@endif
            @if ($vehiculos->hasMorePages())<a href="{{ $vehiculos->nextPageUrl() }}">Siguiente →</a>@else<span class="deshabilitado">Siguiente →</span>@endif
          </nav>
        @endif
      @endif
    </div>
  </div>

  @include('partials.comunas-los-lagos')
</div>

@include('partials.vehiculos-en-vivo', ['modo' => 'aviso', 'grilla' => null, 'desde' => (int) \App\Models\Vehiculo::max('id')])
@endsection
