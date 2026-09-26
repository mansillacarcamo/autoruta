@extends('layouts.admin')
@section('titulo', 'Publicidad')

@section('contenido')
<div class="admin-tarjeta">
  <div class="admin-tarjeta-cabecera">
    <div>
      <h2>Precio del plan de publicidad</h2>
      <p class="texto-mutado" style="font-size:14px">Se muestra en "Cómo funciona". Actual: <strong>${{ number_format($precioActual, 0, ',', '.') }}/mes</strong></p>
    </div>
    <form method="post" action="{{ route('admin.precio.actualizar') }}" style="display:flex;gap:8px">
      @csrf
      <input type="number" name="precioPublicidad" value="{{ $precioActual }}" min="0" step="1000" style="width:140px;padding:9px 12px;border:1px solid #d4d4d4;border-radius:8px">
      <button type="submit" class="btn btn-acento">Guardar</button>
    </form>
  </div>
</div>

<div class="admin-tarjeta">
  <div class="admin-tarjeta-cabecera">
    <div>
      <h2>Negocios anunciantes</h2>
      <p class="texto-mutado" style="font-size:14px">{{ $negocios->count() }} negocio(s). Entra a cada uno para subir sus banners.</p>
    </div>
    <a href="{{ route('admin.negocios.crear') }}" class="btn btn-acento">+ Nuevo negocio</a>
  </div>

  @if ($negocios->isEmpty())
    <p class="admin-vacio">Todavía no hay negocios cargados.</p>
  @else
    <div class="admin-tabla-envoltura">
      <table class="admin-tabla">
        <thead><tr><th>Negocio</th><th>Rubro</th><th>Banners</th><th>Estado</th><th>Vence</th><th></th></tr></thead>
        <tbody>
          @foreach ($negocios as $n)
            <tr>
              <td><strong>{{ $n->nombre_negocio }}</strong></td>
              <td>{{ \App\Models\Anunciante::ETIQUETA_RUBRO[$n->rubro] ?? $n->rubro }}</td>
              <td>{{ $n->banners_count }} / {{ config('autoruta.max_banners_negocio') }}</td>
              <td><span class="admin-etiqueta {{ $n->estado === 'activo' ? 'verde' : ($n->estado === 'vencido' ? 'roja' : 'gris') }}">{{ \App\Models\Anunciante::ETIQUETA_ESTADO[$n->estado] ?? $n->estado }}</span></td>
              <td>
                @if ($n->vence_en)
                  <span class="admin-etiqueta {{ $n->vence_en->isPast() ? 'roja' : ($n->vence_en->diffInDays(now()) <= 5 ? 'gris' : '') }}">{{ $n->vence_en->format('d-m-Y') }}{{ $n->vence_en->isPast() ? ' · vencido' : '' }}</span>
                @else — @endif
              </td>
              <td style="text-align:right"><a href="{{ route('admin.negocios.editar', $n) }}" style="font-weight:600;color:var(--acento)">Editar →</a></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
</div>

<div class="admin-tarjeta">
  <h2>Medidas de los espacios publicitarios</h2>
  <p class="texto-mutado" style="font-size:14px;margin:0 0 14px">Usa estas medidas al diseñar los banners para que se vean bien.</p>
  <div class="admin-tabla-envoltura">
    <table class="admin-tabla">
      <thead><tr><th>Ubicación</th><th>Medida recomendada</th></tr></thead>
      <tbody>
        @foreach (\App\Models\Anunciante::POSICIONES as [$etiqueta, $medida])
          <tr><td>{{ $etiqueta }}</td><td><span class="admin-etiqueta roja">{{ $medida }}</span></td></tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
