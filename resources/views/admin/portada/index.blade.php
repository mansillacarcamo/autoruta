@extends('layouts.admin')
@section('titulo', 'Portada')

@section('contenido')
<div class="admin-tarjeta">
  <div class="admin-tarjeta-cabecera">
    <div>
      <h2>Banner principal de la página de inicio ({{ $medios->count() }}/{{ $maxMedios }})</h2>
      <p class="texto-mutado" style="font-size:14px">Si subes un video, se muestra el video. Si solo hay fotos, van rotando cada 5 segundos. Sin archivos se usa el diseño por defecto.</p>
    </div>
  </div>

  @if ($medios->isEmpty())
    <p class="admin-vacio">Aún no hay archivos. Se muestra el diseño por defecto.</p>
  @else
    <div class="admin-medios">
      @foreach ($medios as $m)
        <div class="admin-medio">
          @if ($m->tipo_medio === 'video')
            <video src="{{ $m->url() }}" muted controls></video>
          @else
            <img src="{{ $m->url() }}" alt="">
          @endif
          <div class="admin-medio-pie">
            <span class="admin-etiqueta">{{ $m->tipo_medio === 'video' ? 'Video' : 'Foto' }}</span>
            <form method="post" action="{{ route('admin.portada.eliminar', $m) }}" onsubmit="return confirm('¿Eliminar este archivo?')">
              @csrf @method('DELETE')
              <button type="submit" class="btn-peligro">Eliminar</button>
            </form>
          </div>
        </div>
      @endforeach
    </div>
  @endif

  @if ($medios->count() < $maxMedios)
    <form method="post" action="{{ route('admin.portada.subir') }}" enctype="multipart/form-data" style="margin-top:20px;padding-top:20px;border-top:1px solid #f0f0f2">
      @csrf
      <div class="form-grupo">
        <label>Foto o video</label>
        <input type="file" name="archivo" accept="image/*,video/mp4,video/webm" required>
        <p class="admin-ayuda">Medida recomendada: <strong>1920 × 600 px</strong> (horizontal). Videos MP4 cortos, máximo 20 MB.</p>
      </div>
      <button type="submit" class="btn btn-acento">Subir a la portada</button>
    </form>
  @endif
</div>
@endsection
