@extends('layouts.admin')
@section('titulo', 'Configuración')

@section('contenido')
<form method="post" action="{{ route('admin.configuracion.actualizar') }}">
  @csrf @method('PUT')

  <div class="admin-tarjeta">
    <h2>Redes sociales</h2>
    <p class="texto-mutado" style="font-size:14px;margin:0 0 16px">Los íconos aparecen en el encabezado del sitio. Deja un campo vacío para ocultar esa red.</p>
    <div class="admin-campos">
      <div class="form-grupo"><label>Facebook</label><input type="url" name="facebook" value="{{ old('facebook', config('autoruta.redes_sociales.facebook')) }}" placeholder="https://facebook.com/tu-pagina"></div>
      <div class="form-grupo"><label>Instagram</label><input type="url" name="instagram" value="{{ old('instagram', config('autoruta.redes_sociales.instagram')) }}" placeholder="https://instagram.com/tu-cuenta"></div>
      <div class="form-grupo"><label>TikTok</label><input type="url" name="tiktok" value="{{ old('tiktok', config('autoruta.redes_sociales.tiktok')) }}" placeholder="https://tiktok.com/@tu-cuenta"></div>
      <div class="form-grupo"><label>YouTube</label><input type="url" name="youtube" value="{{ old('youtube', config('autoruta.redes_sociales.youtube')) }}" placeholder="https://youtube.com/@tu-canal"></div>
    </div>
  </div>

  <div class="admin-tarjeta">
    <h2>Datos de contacto</h2>
    <p class="texto-mutado" style="font-size:14px;margin:0 0 16px">Se muestran en el pie de página y en el botón "Hablar con un ejecutivo".</p>
    <div class="admin-campos">
      <div class="form-grupo"><label>WhatsApp</label><input type="text" name="whatsapp" required value="{{ old('whatsapp', config('autoruta.contacto_whatsapp')) }}" placeholder="+56912345678"><p class="admin-ayuda">Con código de país, sin espacios.</p></div>
      <div class="form-grupo"><label>Teléfono visible</label><input type="text" name="telefono" required value="{{ old('telefono', config('autoruta.contacto_telefono')) }}"></div>
      <div class="form-grupo"><label>Correo</label><input type="email" name="email" required value="{{ old('email', config('autoruta.contacto_email')) }}"></div>
      <div class="form-grupo"><label>Ubicación</label><input type="text" name="ubicacion" required value="{{ old('ubicacion', config('autoruta.contacto_ubicacion')) }}"></div>
    </div>
  </div>

  <button type="submit" class="btn btn-acento">Guardar configuración</button>
</form>

<div class="admin-tarjeta" style="margin-top:22px">
  <h2>Límites del servidor para subir archivos</h2>
  <p class="texto-mutado" style="font-size:14px;margin:0 0 14px">Información técnica. Si las publicaciones con fotos fallan, compártela con soporte.</p>
  <div class="admin-tabla-envoltura">
    <table class="admin-tabla">
      <tbody>
        <tr><td>Dónde se guardan las fotos</td><td><strong>{{ \App\Support\Archivos::esLocal() ? 'Base de datos (respaldo permanente) + disco del servidor' : 'Object Storage (disco ' . config('autoruta.disco_archivos') . ')' }}</strong></td></tr>
        <tr><td>Tamaño máximo por archivo (upload_max_filesize)</td><td><strong>{{ ini_get('upload_max_filesize') }}</strong></td></tr>
        <tr><td>Tamaño máximo por envío (post_max_size)</td><td><strong>{{ ini_get('post_max_size') }}</strong></td></tr>
        <tr><td>Archivos máximos por envío (max_file_uploads)</td><td><strong>{{ ini_get('max_file_uploads') }}</strong></td></tr>
        <tr><td>Versión de PHP</td><td><strong>{{ PHP_VERSION }}</strong></td></tr>
      </tbody>
    </table>
  </div>
</div>
@endsection
