@extends('layouts.admin')
@section('titulo', 'Publicidad')

@section('contenido')
<div class="caja">
  <h2>Precio del plan de publicidad</h2>
  <p class="texto-mutado">Se muestra en "Cómo funciona". Actual: ${{ number_format($precioActual, 0, ',', '.') }}/mes.</p>
  <form method="post" action="{{ route('admin.precio.actualizar') }}" style="display:flex;gap:8px;margin-top:10px">
    @csrf
    <input type="number" name="precioPublicidad" value="{{ $precioActual }}" min="0" step="1000" style="width:160px;padding:8px 10px;border:1px solid #d4d4d4;border-radius:8px">
    <button type="submit" class="btn btn-acento">Guardar</button>
  </form>
</div>

<div style="display:flex;align-items:center;justify-content:space-between;margin-top:32px">
  <h1>Publicidad: negocios</h1>
  <a href="{{ route('admin.negocios.crear') }}" class="btn btn-acento">+ Nuevo negocio</a>
</div>

@if ($negocios->isEmpty())
  <p class="caja texto-mutado" style="text-align:center;border-style:dashed;margin-top:16px">Todavía no hay negocios cargados.</p>
@else
  <div class="mt-2" style="border:1px solid #e5e5e5;border-radius:12px;overflow:hidden">
    @foreach ($negocios as $n)
      <a href="{{ route('admin.negocios.editar', $n) }}" style="display:flex;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #e5e5e5">
        <div>
          <p style="font-weight:600;margin:0">{{ $n->nombre_negocio }}</p>
          <p class="texto-mutado" style="font-size:12px;margin:0">{{ \App\Models\Anunciante::ETIQUETA_RUBRO[$n->rubro] ?? $n->rubro }} · {{ $n->banners_count }} banner(s)</p>
        </div>
        <span style="font-size:12px;font-weight:600">{{ \App\Models\Anunciante::ETIQUETA_ESTADO[$n->estado] ?? $n->estado }}</span>
      </a>
    @endforeach
  </div>
@endif
@endsection
