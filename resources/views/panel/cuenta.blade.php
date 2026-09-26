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

  <div class="caja mt-3">
    <h2 style="margin-top:0">Automotora o negocio <span class="texto-mutado" style="font-size:14px;font-weight:500">(opcional)</span></h2>
    <p class="texto-mutado" style="font-size:14px;margin:0 0 14px">Si vendes como automotora, sube tu logo: aparecerá en la esquina de las fotos de tus avisos y en el recuadro de contacto.</p>
    @if ($errors->logo->any())
      <div class="alerta-error"><ul style="margin:0;padding-left:18px">@foreach ($errors->logo->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="post" action="{{ route('panel.cuenta.logo') }}" enctype="multipart/form-data">
      @csrf
      <div class="logo-editor">
        <div class="logo-vista" id="logoVista">
          @if ($usuario->logo)
            <img src="{{ \App\Support\Archivos::url('logos/' . $usuario->logo) }}" alt="Logo">
          @else
            <span>Sin logo</span>
          @endif
        </div>
        <div style="flex:1;min-width:0">
          <label class="btn btn-outline-oscuro" style="display:inline-flex;align-items:center;gap:8px;cursor:pointer">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
            {{ $usuario->logo ? 'Cambiar logo' : 'Subir logo' }}
            <input type="file" name="logo" id="inputLogo" accept="image/png,image/jpeg,image/webp" hidden>
          </label>
          <p class="texto-mutado" style="font-size:12px;margin:6px 0 0">JPG, PNG o WEBP. Ideal cuadrado y con fondo transparente (PNG).</p>
        </div>
      </div>

      <div class="form-grupo mt-2"><label>Nombre de la automotora o negocio</label><input type="text" name="nombre_comercial" maxlength="80" value="{{ old('nombre_comercial', $usuario->nombre_comercial) }}" placeholder="Ej: Automotora Sur"></div>

      <label class="casilla">
        <input type="checkbox" name="mostrar_logo" value="1" @checked(old('mostrar_logo', $usuario->mostrar_logo))>
        <span>Mostrar mi logo en mis avisos</span>
      </label>

      <button type="submit" class="btn btn-acento mt-2">Guardar</button>
    </form>

    @if ($usuario->logo)
      <form method="post" action="{{ route('panel.cuenta.logo.quitar') }}" onsubmit="return confirm('¿Quitar tu logo?')" style="margin-top:10px">
        @csrf @method('DELETE')
        <button type="submit" style="background:none;border:none;padding:0;color:#b91c1c;font-size:13px;font-weight:600;cursor:pointer">Quitar logo</button>
      </form>
    @endif
  </div>

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
  // Vista previa del logo elegido antes de guardar.
  document.getElementById('inputLogo').addEventListener('change', function () {
    var archivo = this.files[0];
    if (!archivo) return;
    var vista = document.getElementById('logoVista');
    vista.innerHTML = '';
    var img = document.createElement('img');
    img.src = URL.createObjectURL(archivo);
    img.alt = 'Logo';
    vista.appendChild(img);
    var casilla = document.querySelector('input[name="mostrar_logo"]');
    if (casilla) casilla.checked = true;
  });

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
