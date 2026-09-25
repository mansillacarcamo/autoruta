@extends('layouts.admin')
@section('titulo', 'Usuarios')

@section('contenido')
<h1>Usuarios registrados</h1>
<p class="texto-mutado">{{ $usuarios->count() }} cuentas en total.</p>

<div class="mt-2" style="overflow-x:auto">
  <table style="width:100%;border-collapse:collapse;font-size:14px">
    <thead>
      <tr style="text-align:left;border-bottom:1px solid #e5e5e5;font-size:12px;color:#737373;text-transform:uppercase">
        <th style="padding:8px">Nombre</th><th style="padding:8px">Correo</th><th style="padding:8px">Rol</th>
        <th style="padding:8px">Ubicación</th><th style="padding:8px">Se registró</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($usuarios as $u)
      <tr style="border-bottom:1px solid #e5e5e5">
        <td style="padding:8px;font-weight:600">{{ $u->name }}</td>
        <td style="padding:8px;color:#525252">{{ $u->email }}</td>
        <td style="padding:8px"><span style="background:var(--gris-claro);border-radius:999px;padding:2px 8px;font-size:12px;font-weight:600">{{ ucfirst($u->rol) }}</span></td>
        <td style="padding:8px;color:#525252">{{ $u->comuna ? "{$u->comuna}, {$u->region}" : '—' }}</td>
        <td style="padding:8px;color:#525252">{{ $u->created_at->format('d-m-Y') }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @if ($usuarios->isEmpty())<p class="texto-mutado" style="text-align:center;padding:24px">Todavía no hay usuarios registrados.</p>@endif
</div>
@endsection
