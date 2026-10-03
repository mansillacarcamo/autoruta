@extends('layouts.admin')
@section('titulo', 'Vehículos')

@section('contenido')
<div class="admin-tarjeta">
  <div class="admin-tarjeta-cabecera">
    <div>
      <h2>Vehículos publicados</h2>
      <p class="texto-mutado" style="font-size:14px">{{ $vehiculos->total() }} aviso(s) · <strong style="color:#a16207">{{ $totalPremium }} Premium</strong>. Activa el ticket <strong>Premium</strong> para que un auto salga en primer lugar en la web con la etiqueta dorada.</p>
    </div>
    <a href="{{ route('admin.vehiculos.crear') }}" class="btn btn-acento" style="white-space:nowrap">+ Subir auto</a>
  </div>

  <form method="get" class="admin-vehiculos-filtros">
    <input type="search" name="q" value="{{ $buscar }}" placeholder="Buscar por marca, modelo o vendedor">
    <select name="filtro" onchange="this.form.submit()">
      <option value="">Todos</option>
      <option value="premium" @selected(request('filtro') === 'premium')>Solo Premium</option>
    </select>
    <button type="submit" class="btn btn-outline-oscuro">Buscar</button>
  </form>

  @if ($vehiculos->isEmpty())
    <p class="admin-vacio">No hay vehículos {{ $buscar || request('filtro') ? 'que coincidan con la búsqueda' : 'publicados todavía' }}.</p>
  @else
    <div class="admin-tabla-envoltura">
      <table class="admin-tabla">
        <thead><tr><th>Vehículo</th><th>Vendedor</th><th>Precio</th><th>Estado</th><th>Visitas</th><th>Publicado</th><th style="text-align:center">Premium</th><th>Acciones</th></tr></thead>
        <tbody>
          @foreach ($vehiculos as $v)
            <tr class="{{ $v->premium ? 'fila-premium' : '' }}">
              <td>
                <a href="{{ route('vehiculos.show', $v) }}" target="_blank" class="admin-vehiculo">
                  <img src="{{ $v->primeraFotoUrl() }}" alt="" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('img/vehiculo-placeholder.svg') }}'">
                  <span><strong>{{ $v->marca }} {{ $v->modelo }} {{ $v->anio }}</strong><small>{{ $v->comuna }}</small></span>
                </a>
              </td>
              <td>{{ $v->usuario?->nombre_comercial ?: $v->usuario?->name ?: '—' }}</td>
              <td style="white-space:nowrap">{{ $v->precioFormateado() }}</td>
              <td>
                @if ($v->estado === 'vendida')<span class="admin-etiqueta">Vendido</span>
                @elseif ($v->estaVencida())<span class="admin-etiqueta roja">Vencido</span>
                @elseif ($v->estado === 'activa')<span class="admin-etiqueta" style="background:#dcfce7;color:#15803d">Activo</span>
                @else<span class="admin-etiqueta">{{ ucfirst($v->estado) }}</span>@endif
              </td>
              <td>{{ number_format($v->vistas, 0, ',', '.') }}</td>
              <td style="white-space:nowrap">{{ ($v->publicado_en ?? $v->created_at)->format('d-m-Y') }}</td>
              <td style="text-align:center">
                <form method="post" action="{{ route('admin.vehiculos.premium', $v) }}">
                  @csrf
                  <input type="hidden" name="premium" value="0">
                  <label class="ticket-premium" title="{{ $v->premium ? 'Quitar Premium' : 'Activar Premium' }}">
                    <input type="checkbox" name="premium" value="1" @checked($v->premium) onchange="this.form.submit()">
                    <span>★ Premium</span>
                  </label>
                </form>
              </td>
              <td>
                <div class="admin-acciones">
                  <a href="{{ route('admin.vehiculos.editar', $v) }}" class="btn btn-outline-oscuro btn-chico">Editar</a>
                  <form method="post" action="{{ route('admin.vehiculos.eliminar', $v) }}" onsubmit="return confirm('¿Eliminar {{ $v->marca }} {{ $v->modelo }}? Esta acción no se puede deshacer.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-peligro">Eliminar</button>
                  </form>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @if ($vehiculos->hasPages())
      <nav class="paginacion" aria-label="Páginas" style="margin-top:16px">
        @if ($vehiculos->onFirstPage())<span class="deshabilitado">← Anterior</span>@else<a href="{{ $vehiculos->previousPageUrl() }}">← Anterior</a>@endif
        <span>Página {{ $vehiculos->currentPage() }} de {{ $vehiculos->lastPage() }}</span>
        @if ($vehiculos->hasMorePages())<a href="{{ $vehiculos->nextPageUrl() }}">Siguiente →</a>@else<span class="deshabilitado">Siguiente →</span>@endif
      </nav>
    @endif
  @endif
</div>
@endsection
