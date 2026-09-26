@extends('layouts.app')
@section('titulo', 'Vehículos en venta')

@section('contenido')
<div class="contenedor" style="padding:32px 16px">
  <h1>
    Vehículos en venta
    @if (array_key_exists((string) request('tipo'), \App\Models\Vehiculo::ETIQUETA_TIPO))
      <span style="color:var(--acento)">· {{ \App\Models\Vehiculo::ETIQUETA_TIPO[request('tipo')] }}</span>
    @endif
  </h1>
  <div class="mt-2">@include('partials.categorias')</div>

  <div style="display:grid;gap:24px;margin-top:24px" class="lg-grid">
    <form class="caja" method="get" style="height:fit-content">
      <div class="form-grupo">
        <label>Buscar</label>
        <input type="text" name="q" list="marcas" value="{{ request('q') }}" placeholder="Marca o modelo">
        <datalist id="marcas">
          @foreach (config('marcas') as $m)<option value="{{ $m }}">@endforeach
        </datalist>
      </div>

      <div class="form-grupo">
        <label>Tipo</label>
        <select name="tipo">
          <option value="">Todos</option>
          @foreach (\App\Models\Vehiculo::ETIQUETA_TIPO as $valor => $etiqueta)
            <option value="{{ $valor }}" @selected(request('tipo') === $valor)>{{ $etiqueta }}</option>
          @endforeach
        </select>
      </div>

      <div class="form-grupo">
        <label>Región</label>
        <select name="region" onchange="this.form.submit()">
          <option value="">Todas</option>
          @foreach (array_keys(config('regiones')) as $r)
            <option value="{{ $r }}" @selected(request('region') === $r)>{{ $r }}</option>
          @endforeach
        </select>
      </div>

      @if (request('region'))
      <div class="form-grupo">
        <label>Comuna</label>
        <select name="comuna">
          <option value="">Todas</option>
          @foreach (config('regiones.' . request('region'), []) as $c)
            <option value="{{ $c }}" @selected(request('comuna') === $c)>{{ $c }}</option>
          @endforeach
        </select>
      </div>
      @endif

      <div class="grid-2">
        <div class="form-grupo"><label>Precio min.</label><input type="number" name="precioMin" value="{{ request('precioMin') }}"></div>
        <div class="form-grupo"><label>Precio máx.</label><input type="number" name="precioMax" value="{{ request('precioMax') }}"></div>
      </div>

      <div class="form-grupo">
        <label>Ordenar por</label>
        <select name="orden">
          <option value="recientes" @selected(request('orden', 'recientes') === 'recientes')>Más recientes</option>
          <option value="precio_asc" @selected(request('orden') === 'precio_asc')>Menor precio</option>
          <option value="precio_desc" @selected(request('orden') === 'precio_desc')>Mayor precio</option>
          <option value="km_asc" @selected(request('orden') === 'km_asc')>Menor kilometraje</option>
        </select>
      </div>

      <button type="submit" class="btn btn-acento btn-block">Filtrar</button>
    </form>

    <div>
      <p class="texto-mutado">{{ $vehiculos->count() }} {{ $vehiculos->count() === 1 ? 'vehículo encontrado' : 'vehículos encontrados' }}</p>
      @if ($vehiculos->isEmpty())
        <p class="caja texto-mutado" style="text-align:center;border-style:dashed">No hay vehículos que coincidan con estos filtros.</p>
      @else
        <div class="grilla">
          @foreach ($vehiculos as $v)
            @include('vehiculos._tarjeta', ['v' => $v])
          @endforeach
        </div>
      @endif
    </div>
  </div>
</div>

<style>@media (min-width:900px){.lg-grid{grid-template-columns:260px 1fr}}</style>
@endsection
