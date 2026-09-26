@extends('layouts.admin')
@section('titulo', 'Resumen')

@section('contenido')
@php
  $tarjetas = [
    ['Visitas al sitio', $estadisticas['visitas'], '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>'],
    ['Vehículos publicados', $estadisticas['vehiculos'], '<path d="M5 17h14M3 13l2-6h14l2 6v4H3z"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/>'],
    ['Usuarios registrados', $estadisticas['usuarios'], '<circle cx="12" cy="8" r="4"/><path d="M4 21v-1a8 8 0 0 1 16 0v1"/>'],
    ['Negocios anunciando', $estadisticas['negocios'], '<path d="M3 11l18-8v18L3 13z"/>'],
  ];
@endphp
<div class="admin-stats">
  @foreach ($tarjetas as [$texto, $valor, $icono])
    <div class="admin-stat">
      <div class="admin-stat-icono"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $icono !!}</svg></div>
      <div><small>{{ $texto }}</small><strong>{{ number_format($valor, 0, ',', '.') }}</strong></div>
    </div>
  @endforeach
</div>

<div class="admin-grid-2">
  <div class="admin-tarjeta">
    <div class="admin-tarjeta-cabecera"><h2>Últimos vehículos</h2><a href="{{ route('vehiculos.index') }}" target="_blank" class="texto-mutado" style="font-size:13px">Ver en el sitio ↗</a></div>
    @forelse ($ultimosVehiculos as $v)
      <a href="{{ route('vehiculos.show', $v) }}" target="_blank" style="display:flex;justify-content:space-between;gap:12px;padding:10px 0;border-top:1px solid #f0f0f2;font-size:14px">
        <span><strong>{{ $v->marca }} {{ $v->modelo }}</strong> {{ $v->anio }}<br><span class="texto-mutado" style="font-size:12px">{{ $v->usuario?->name }} · {{ $v->created_at->format('d-m-Y') }}</span></span>
        <span style="text-align:right;font-weight:700;color:var(--acento)">{{ $v->precioFormateado() }}<br><span class="admin-etiqueta {{ $v->estado === 'activa' ? 'verde' : 'gris' }}">{{ ucfirst($v->estado) }}</span></span>
      </a>
    @empty
      <p class="admin-vacio">Todavía no hay vehículos publicados.</p>
    @endforelse
  </div>

  <div class="admin-tarjeta">
    <div class="admin-tarjeta-cabecera"><h2>Últimos usuarios</h2><a href="{{ route('admin.usuarios.index') }}" class="texto-mutado" style="font-size:13px">Ver todos →</a></div>
    @forelse ($ultimosUsuarios as $u)
      <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-top:1px solid #f0f0f2;font-size:14px">
        <span class="admin-avatar" style="width:30px;height:30px;font-size:13px">{{ mb_strtoupper(mb_substr($u->name, 0, 1)) }}</span>
        <span style="flex:1"><strong>{{ $u->name }}</strong><br><span class="texto-mutado" style="font-size:12px">{{ $u->email }}</span></span>
        <span class="texto-mutado" style="font-size:12px">{{ $u->created_at->format('d-m-Y') }}</span>
      </div>
    @empty
      <p class="admin-vacio">Todavía no hay usuarios.</p>
    @endforelse
  </div>
</div>
@endsection
