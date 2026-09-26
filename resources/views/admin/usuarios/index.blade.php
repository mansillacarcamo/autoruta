@extends('layouts.admin')
@section('titulo', 'Usuarios')

@section('contenido')
<div class="admin-tarjeta">
  <div class="admin-tarjeta-cabecera">
    <div>
      <h2>Usuarios registrados</h2>
      <p class="texto-mutado" style="font-size:14px">{{ $usuarios->count() }} cuenta(s) en total.</p>
    </div>
  </div>

  @if ($usuarios->isEmpty())
    <p class="admin-vacio">Todavía no hay usuarios registrados.</p>
  @else
    <div class="admin-tabla-envoltura">
      <table class="admin-tabla">
        <thead><tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Teléfono</th><th>Ciudad</th><th>Registro</th></tr></thead>
        <tbody>
          @foreach ($usuarios as $u)
            <tr>
              <td>
                <span style="display:flex;align-items:center;gap:10px">
                  <span class="admin-avatar" style="width:28px;height:28px;font-size:12px">{{ mb_strtoupper(mb_substr($u->name, 0, 1)) }}</span>
                  <strong>{{ $u->name }}</strong>
                </span>
              </td>
              <td>{{ $u->email }}</td>
              <td><span class="admin-etiqueta {{ $u->rol === 'admin' ? 'roja' : '' }}">{{ ucfirst($u->rol) }}</span></td>
              <td>{{ $u->telefono_whatsapp ?: '—' }}</td>
              <td>{{ implode(', ', array_filter([$u->comuna, $u->region])) ?: '—' }}</td>
              <td>{{ $u->created_at->format('d-m-Y') }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
</div>
@endsection
