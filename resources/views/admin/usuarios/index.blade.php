@extends('layouts.admin')
@section('titulo', 'Usuarios')

@section('contenido')
@if ($temporal = session('clave_temporal'))
  <div class="admin-tarjeta" style="border-color:#86efac;background:#f0fdf4">
    <h2>Nueva contraseña para {{ $temporal['nombre'] }}</h2>
    <p style="font-size:14px;margin:6px 0 12px">Contraseña temporal: <strong style="font-size:20px;letter-spacing:1px;background:#fff;padding:4px 10px;border-radius:6px;border:1px solid #d4d4d4">{{ $temporal['clave'] }}</strong></p>
    <p class="texto-mutado" style="font-size:13px;margin:0 0 12px">Solo se muestra esta vez. Envíasela al cliente y pídele que la cambie en <strong>Mi panel → Mi cuenta</strong>.</p>
    @if ($temporal['whatsapp'])
      <a class="btn" style="background:#25D366;color:#fff" target="_blank" rel="noopener" href="https://wa.me/{{ $temporal['whatsapp'] }}?text={{ urlencode('Hola ' . $temporal['nombre'] . ', tu nueva contraseña temporal de AutoRuta es: ' . $temporal['clave'] . ' . Te recomendamos cambiarla en Mi panel > Mi cuenta.') }}">Enviar por WhatsApp</a>
    @endif
  </div>
@endif
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
        <thead><tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Teléfono</th><th>Ciudad</th><th>Avisos</th><th>Registro</th><th></th></tr></thead>
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
              <td>{{ $u->vehiculos_count }}</td>
              <td>{{ $u->created_at->format('d-m-Y') }}</td>
              <td style="text-align:right">
                <form method="post" action="{{ route('admin.usuarios.clave', $u) }}" onsubmit="return confirm('¿Generar una nueva contraseña para este usuario? La actual dejará de funcionar.')">
                  @csrf
                  <button type="submit" style="background:none;border:none;color:var(--acento);font-weight:600;font-size:13px;cursor:pointer;white-space:nowrap">Nueva contraseña</button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
</div>
@endsection
