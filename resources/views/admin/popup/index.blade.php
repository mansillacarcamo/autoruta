@extends('layouts.admin')
@section('titulo', 'Pop-up')

@section('contenido')
<div class="admin-tarjeta">
  <div class="admin-tarjeta-cabecera">
    <div>
      <h2>Pop-up de la página de inicio</h2>
      <p class="texto-mutado" style="font-size:14px">Ventana que aparece al entrar al inicio, una vez por visita. Sube un diseño (imagen) o un video corto; al subir uno nuevo reemplaza al anterior.</p>
    </div>
    @if ($popup)
      <span class="admin-etiqueta" style="{{ $popup->activo ? 'background:#dcfce7;color:#15803d' : '' }}">{{ $popup->activo ? 'Visible' : 'Pausado' }}</span>
    @endif
  </div>

  @if (! $popup)
    <p class="admin-vacio">No hay pop-up. Los visitantes no ven ninguna ventana al entrar.</p>
  @else
    <div class="admin-medios">
      <div class="admin-medio">
        @if ($popup->tipo_medio === 'video')
          <video src="{{ $popup->url() }}" muted controls></video>
        @else
          <img src="{{ $popup->url() }}" alt="">
        @endif
        <div class="admin-medio-pie">
          <span class="admin-etiqueta">{{ $popup->tipo_medio === 'video' ? 'Video' : 'Diseño' }}</span>
          <form method="post" action="{{ route('admin.popup.eliminar', $popup) }}" onsubmit="return confirm('¿Eliminar el pop-up?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn-peligro">Eliminar</button>
          </form>
        </div>
      </div>
    </div>

    <form method="post" action="{{ route('admin.popup.actualizar', $popup) }}" style="margin-top:16px">
      @csrf @method('PUT')
      <div class="form-grupo">
        <label>Enlace al hacer clic (opcional)</label>
        <input type="url" name="link_url" value="{{ old('link_url', $popup->link_url) }}" placeholder="https://autoruta.cl/vehiculos">
      </div>
      <label style="display:flex;align-items:center;gap:8px;margin:8px 0 14px;font-weight:600">
        <input type="checkbox" name="activo" value="1" @checked($popup->activo)> Mostrar el pop-up a los visitantes
      </label>
      <button type="submit" class="btn btn-outline-oscuro">Guardar cambios</button>
    </form>
  @endif

  <form method="post" action="{{ route('admin.popup.subir') }}" enctype="multipart/form-data" style="margin-top:20px;padding-top:20px;border-top:1px solid #f0f0f2">
    @csrf
    <h3 style="font-size:15px;margin:0 0 12px">{{ $popup ? 'Reemplazar por un diseño o video nuevo' : 'Subir pop-up' }}</h3>
    <div class="form-grupo">
      <label>Diseño o video</label>
      <input type="file" name="archivo" accept="image/*,video/mp4,video/webm" required>
      <p class="admin-ayuda">Medida recomendada: <strong>1080 × 1080 px</strong> (cuadrado) o <strong>1080 × 1350 px</strong> (vertical). JPG, PNG, WEBP, GIF o video MP4 de máximo 20 MB.</p>
    </div>
    <div class="form-grupo">
      <label>Enlace al hacer clic (opcional)</label>
      <input type="url" name="link_url" placeholder="https://autoruta.cl/vehiculos">
    </div>
    <button type="submit" class="btn btn-acento">Publicar pop-up</button>
  </form>
</div>
@endsection
