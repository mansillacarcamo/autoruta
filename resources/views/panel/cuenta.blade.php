@extends('layouts.app')
@section('titulo', 'Mi cuenta')

@section('contenido')
<div class="contenedor" style="max-width:640px;padding:32px 16px">
  <p style="margin:0 0 8px"><a href="{{ route('panel') }}" class="texto-mutado" style="font-size:14px">← Volver a mi panel</a></p>
  <h1>Mi cuenta</h1>

  <form method="post" action="{{ route('panel.cuenta.datos') }}" class="caja mt-2">
    @csrf @method('PUT')
    <h2 style="margin-top:0">Mis datos</h2>
    @if ($errors->default->any())
      <div class="alerta-error"><ul style="margin:0;padding-left:18px">@foreach ($errors->default->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif
    <div class="form-grupo"><label>Nombre</label><input type="text" name="name" required value="{{ old('name', $usuario->name) }}" autocomplete="name"></div>
    <div class="form-grupo"><label>Correo</label><input type="email" name="email" required value="{{ old('email', $usuario->email) }}" autocomplete="email"></div>
    <div class="form-grupo">
      <label>Teléfono (WhatsApp)</label>
      <input type="tel" name="telefono" required value="{{ old('telefono', $usuario->telefono_whatsapp) }}" placeholder="+56 9 1234 5678" autocomplete="tel">
      <p class="texto-mutado" style="font-size:12px;margin:4px 0 0">Los compradores te contactarán a este número.</p>
    </div>
    <div class="form-grupo"><label>Ciudad</label><input type="text" name="ciudad" required value="{{ old('ciudad', $usuario->comuna) }}" autocomplete="address-level2"></div>
    <button type="submit" class="btn btn-acento">Guardar datos</button>
  </form>

  <form method="post" action="{{ route('panel.cuenta.clave') }}" class="caja mt-3">
    @csrf @method('PUT')
    <h2 style="margin-top:0">Cambiar contraseña</h2>
    @if ($errors->clave->any())
      <div class="alerta-error"><ul style="margin:0;padding-left:18px">@foreach ($errors->clave->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif
    <div class="form-grupo"><label>Contraseña actual</label><input type="password" name="clave_actual" required autocomplete="current-password"></div>
    <div class="form-grupo"><label>Nueva contraseña</label><input type="password" name="password" required autocomplete="new-password"><p class="texto-mutado" style="font-size:12px;margin:4px 0 0">Mínimo 8 caracteres.</p></div>
    <div class="form-grupo"><label>Repite la nueva contraseña</label><input type="password" name="password_confirmation" required autocomplete="new-password"></div>
    <button type="submit" class="btn btn-acento">Cambiar contraseña</button>
  </form>
</div>

<script>
  // Botón para ver la contraseña (igual que en el registro).
  document.querySelectorAll('input[type="password"]').forEach(function (campo) {
    var envoltura = document.createElement('div');
    envoltura.className = 'campo-clave';
    campo.parentNode.insertBefore(envoltura, campo);
    envoltura.appendChild(campo);
    var boton = document.createElement('button');
    boton.type = 'button';
    boton.className = 'ver-clave';
    boton.setAttribute('aria-label', 'Mostrar contraseña');
    boton.innerHTML = '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
    boton.addEventListener('click', function () { campo.type = campo.type === 'password' ? 'text' : 'password'; });
    envoltura.appendChild(boton);
  });
</script>
@endsection
