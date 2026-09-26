@extends('layouts.app')
@section('titulo', 'Mi panel')

@section('contenido')
<div class="contenedor" style="max-width:760px;padding:32px 16px">
  <div style="display:flex;align-items:center;justify-content:space-between">
    <div>
      <h1>Hola, {{ \Illuminate\Support\Str::of(auth()->user()->name)->trim()->before(' ') }}</h1>
      <p class="texto-mutado">Sesión iniciada como <strong>{{ auth()->user()->email }}</strong>.</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <a href="{{ route('panel.cuenta') }}" class="btn btn-outline" style="color:#525252;border-color:#d4d4d4">Mi cuenta</a>
      <form method="post" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-outline" style="color:#525252;border-color:#d4d4d4">Cerrar sesión</button>
      </form>
    </div>
  </div>

  @if (session('bienvenida'))
    <div class="bienvenida">
      <h2>¡Te damos la bienvenida a {{ config('autoruta.nombre_sitio') }}, {{ \Illuminate\Support\Str::of(auth()->user()->name)->trim()->before(' ') }}!</h2>
      <p>Tu cuenta ya está lista. Publica tu vehículo gratis en minutos y los compradores te escribirán directo a tu WhatsApp.</p>
      <a href="{{ route('panel.publicar') }}" class="btn">Publicar mi primer vehículo →</a>
    </div>
  @endif

  @if (auth()->user()->rol !== 'negocio')
  <div class="mt-3">
    <div style="display:flex;align-items:center;justify-content:space-between">
      <h2>Mis publicaciones</h2>
      <a href="{{ route('panel.publicar') }}" class="btn btn-acento">Publicar vehículo</a>
    </div>
    <p class="texto-mutado" style="font-size:13px">{{ $activas }} de {{ config('autoruta.max_publicaciones_activas') }} publicaciones activas.</p>
    <p style="background:var(--gris-claro);border-radius:8px;padding:8px 12px;font-size:13px">
      Puedes editar tus avisos, marcarlos como vendidos o eliminarlos aquí abajo.
    </p>

    @if ($vehiculos->isEmpty())
      <p class="caja texto-mutado" style="text-align:center;border-style:dashed;margin-top:12px">Todavía no tienes vehículos publicados.</p>
    @else
      <div class="mt-2" style="display:flex;flex-direction:column;gap:10px">
        @foreach ($vehiculos as $v)
          <div class="caja aviso-panel">
            <img src="{{ $v->primeraFotoUrl() }}" alt="" data-fotos="{{ json_encode($v->fotosUrls()) }}" data-sin-foto="{{ asset('img/vehiculo-placeholder.svg') }}" onerror="siguienteFoto(this)">
            <div style="flex:1;min-width:0">
              <p style="font-weight:600;margin:0">{{ $v->marca }} {{ $v->modelo }} {{ $v->anio }}</p>
              <p class="tarjeta-precio" style="margin:2px 0">{{ $v->precioFormateado() }}</p>
              <p style="font-size:12px;margin:0;display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                @if ($v->estado === 'vendida')
                  <span class="estado-aviso gris">Vendido</span>
                @elseif ($v->estaVencida())
                  <span class="estado-aviso rojo">Vencido</span>
                @else
                  <span class="estado-aviso verde">Activo · vence en {{ $v->diasRestantes() }} días</span>
                @endif
                <span class="texto-mutado">{{ $v->vistas }} vistas</span>
              </p>
              <div class="aviso-panel-acciones">
                @if ($v->estado !== 'vendida')
                  <a href="{{ route('panel.editar', $v) }}" style="color:var(--acento)">Editar</a>
                @endif
                <a href="{{ route('vehiculos.show', $v) }}" style="color:#525252">Ver aviso</a>
                @if ($v->estaVencida() || ($v->estado === 'activa' && $v->diasRestantes() !== null && $v->diasRestantes() <= 10))
                  <form method="post" action="{{ route('panel.renovar', $v) }}">
                    @csrf
                    <button type="submit" style="color:#1d4ed8">Renovar {{ config('autoruta.duracion_publicacion_dias') }} días</button>
                  </form>
                @endif
                @if ($v->estado !== 'vendida')
                  <form method="post" action="{{ route('panel.vendido', $v) }}" onsubmit="return confirm('¿Marcar este vehículo como vendido? Dejará de mostrarse en la web.')">
                    @csrf
                    <button type="submit" style="color:#15803d">Marcar como vendido</button>
                  </form>
                @endif
                <form method="post" action="{{ route('panel.eliminar', $v) }}" onsubmit="return confirm('¿Eliminar este aviso y sus fotos? No se puede deshacer.')">
                  @csrf @method('DELETE')
                  <button type="submit" style="color:#b91c1c">Eliminar</button>
                </form>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>
  @endif
</div>
@endsection
