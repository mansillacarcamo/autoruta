@extends('layouts.admin')
@section('titulo', 'Portada')

@section('contenido')
<h1>Banner de portada</h1>
<p class="texto-mutado">Fotos o video que aparecen de fondo en el banner principal de la página de inicio. Si subes un video, se muestra el video; si solo hay fotos, van rotando cada 5 segundos. Si no hay nada, se usa el diseño por defecto.</p>
<p class="texto-mutado" style="font-size:13px">Recomendado: imágenes horizontales de 1920×600 px o más. Videos MP4 cortos (máx. 20 MB).</p>

<h2 class="mt-3">Archivos ({{ $medios->count() }}/{{ $maxMedios }})</h2>
<div class="grid-2 mt-2">
  @forelse ($medios as $m)
    <div class="caja" style="padding:0;overflow:hidden">
      @if ($m->tipo_medio === 'video')
        <video src="{{ $m->url() }}" style="width:100%;aspect-ratio:16/9;object-fit:cover" muted controls></video>
      @else
        <img src="{{ $m->url() }}" style="width:100%;aspect-ratio:16/9;object-fit:cover">
      @endif
      <div style="display:flex;justify-content:space-between;padding:8px 12px">
        <span style="font-size:12px;font-weight:600">{{ $m->tipo_medio === 'video' ? 'Video' : 'Foto' }}</span>
        <form method="post" action="{{ route('admin.portada.eliminar', $m) }}">
          @csrf @method('DELETE')
          <button type="submit" style="background:none;border:none;color:#b91c1c;font-size:12px;font-weight:600;cursor:pointer">Eliminar</button>
        </form>
      </div>
    </div>
  @empty
    <p class="texto-mutado">Aún no hay archivos. Se muestra el diseño por defecto.</p>
  @endforelse
</div>

@if ($medios->count() < $maxMedios)
<form method="post" action="{{ route('admin.portada.subir') }}" enctype="multipart/form-data" class="caja mt-3">
  @csrf
  <div class="form-grupo"><label>Foto o video</label><input type="file" name="archivo" accept="image/*,video/mp4,video/webm" required></div>
  <button type="submit" class="btn btn-acento">Subir a la portada</button>
</form>
@endif
@endsection
