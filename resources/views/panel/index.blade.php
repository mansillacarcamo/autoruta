@extends('layouts.app')
@section('titulo', 'Mi panel')

@section('contenido')
<div class="contenedor" style="max-width:760px;padding:32px 16px">
  <div style="display:flex;align-items:center;justify-content:space-between">
    <div>
      <h1>Mi panel</h1>
      <p class="texto-mutado">Sesión iniciada como <strong>{{ auth()->user()->email }}</strong>.</p>
    </div>
    <form method="post" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="btn btn-outline" style="color:#525252;border-color:#d4d4d4">Cerrar sesión</button>
    </form>
  </div>

  @if (auth()->user()->rol !== 'negocio')
  <div class="mt-3">
    <div style="display:flex;align-items:center;justify-content:space-between">
      <h2>Mis publicaciones</h2>
      <a href="{{ route('panel.publicar') }}" class="btn btn-acento">Publicar vehículo</a>
    </div>
    <p class="texto-mutado" style="font-size:13px">{{ $activas }} de {{ config('autoruta.max_publicaciones_activas') }} publicaciones activas.</p>
    <p style="background:var(--gris-claro);border-radius:8px;padding:8px 12px;font-size:13px">
      ¿Vendiste tu vehículo? Marcálo como vendido o elimina la publicación aquí abajo.
    </p>

    @if ($vehiculos->isEmpty())
      <p class="caja texto-mutado" style="text-align:center;border-style:dashed;margin-top:12px">Todavía no tienes vehículos publicados.</p>
    @else
      <div class="mt-2" style="display:flex;flex-direction:column;gap:10px">
        @foreach ($vehiculos as $v)
          <div class="caja" style="display:flex;gap:12px">
            <img src="{{ $v->primeraFotoUrl() }}" style="width:80px;height:64px;object-fit:cover;border-radius:8px;background:var(--gris-claro)">
            <div style="flex:1">
              <p style="font-weight:600;margin:0">{{ $v->marca }} {{ $v->modelo }} {{ $v->anio }}</p>
              <p class="tarjeta-precio" style="margin:2px 0">{{ $v->precioFormateado() }}</p>
              <p class="texto-mutado" style="font-size:12px;margin:0">{{ ucfirst($v->estado) }} · {{ $v->vistas }} vistas</p>
              @if ($v->estado !== 'vendida')
              <div style="margin-top:6px;display:flex;gap:12px">
                <form method="post" action="{{ route('panel.vendido', $v) }}">
                  @csrf
                  <button type="submit" style="background:none;border:none;color:#15803d;font-size:12px;font-weight:600;cursor:pointer;padding:0">Marcar como vendido</button>
                </form>
                <form method="post" action="{{ route('panel.eliminar', $v) }}">
                  @csrf @method('DELETE')
                  <button type="submit" style="background:none;border:none;color:#b91c1c;font-size:12px;font-weight:600;cursor:pointer;padding:0">Eliminar</button>
                </form>
              </div>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>
  @endif
</div>
@endsection
