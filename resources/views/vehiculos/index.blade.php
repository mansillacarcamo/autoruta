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

  {{-- Búsqueda bajo las categorías: el tipo elegido arriba viaja oculto. --}}
  <form method="get" action="{{ route('vehiculos.index') }}" class="buscador-listado">
    @if (request('tipo'))<input type="hidden" name="tipo" value="{{ request('tipo') }}">@endif
    <label class="buscador-campo buscador-texto">
      <span>Buscar</span>
      <input type="search" name="q" list="marcas" value="{{ request('q') }}" placeholder="Marca o modelo">
      <datalist id="marcas">
        @foreach (config('marcas') as $m)<option value="{{ $m }}">@endforeach
      </datalist>
    </label>
    <label class="buscador-campo">
      <span>Región</span>
      <select name="region" onchange="this.form.comuna && (this.form.comuna.value = ''); this.form.submit()">
        <option value="">Todas</option>
        @foreach (array_keys(config('regiones')) as $r)
          <option value="{{ $r }}" @selected($filtroRegion === $r)>{{ $r }}</option>
        @endforeach
      </select>
    </label>
    @if ($filtroRegion)
    <label class="buscador-campo">
      <span>Comuna</span>
      <select name="comuna">
        <option value="">Todas</option>
        @foreach (config('regiones.' . $filtroRegion, []) as $c)
          <option value="{{ $c }}" @selected($filtroComuna === $c)>{{ $c }}</option>
        @endforeach
      </select>
    </label>
    @endif
    <label class="buscador-campo">
      <span>Precio máx.</span>
      <input type="number" name="precioMax" value="{{ request('precioMax') }}" min="0" step="500000" placeholder="Sin límite">
    </label>
    <label class="buscador-campo">
      <span>Ordenar por</span>
      <select name="orden">
        <option value="recientes" @selected(request('orden', 'recientes') === 'recientes')>Más recientes</option>
        <option value="precio_asc" @selected(request('orden') === 'precio_asc')>Menor precio</option>
        <option value="precio_desc" @selected(request('orden') === 'precio_desc')>Mayor precio</option>
        <option value="km_asc" @selected(request('orden') === 'km_asc')>Menor kilometraje</option>
      </select>
    </label>
    <button type="submit" class="btn btn-acento buscador-boton">
      <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
      Buscar
    </button>
    @if (collect(request()->only(['q', 'region', 'comuna', 'precioMin', 'precioMax', 'orden']))->filter()->isNotEmpty())
      <a href="{{ route('vehiculos.index', array_filter(['tipo' => request('tipo')])) }}" class="buscador-limpiar">Limpiar filtros</a>
    @endif
  </form>

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
