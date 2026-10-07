@extends('layouts.admin')
@section('titulo', 'Portada')

@section('contenido')
<div class="admin-tarjeta">
  <div class="admin-tarjeta-cabecera">
    <div>
      <h2>Slider del banner principal ({{ $medios->count() }}/{{ $maxMedios }} banners extra)</h2>
      <p class="texto-mutado" style="font-size:15px">El banner de AutoRuta va siempre primero. Aquí agregas hasta {{ $maxMedios }} banners más que se van turnando en el slider del inicio (cada 6 segundos, con flechas y puntos).</p>
    </div>
  </div>

  <div class="admin-medios">
    <div class="admin-medio">
      <img src="{{ asset('img/banner-portada.jpg') }}" alt="">
      <div class="admin-medio-pie"><span class="admin-etiqueta">1 · Banner de AutoRuta (fijo)</span></div>
    </div>
    @foreach ($medios as $i => $m)
      <div class="admin-medio">
        @if ($m->tipo_medio === 'video')
          <video src="{{ $m->url() }}" muted loop autoplay playsinline></video>
        @else
          <img src="{{ $m->url() }}" alt="">
        @endif
        <div class="admin-medio-pie">
          <span class="admin-etiqueta">{{ $i + 2 }} · {{ $m->tipo_medio === 'video' ? 'Video' : 'Imagen' }}</span>
          <form method="post" action="{{ route('admin.portada.eliminar', $m) }}" onsubmit="return confirm('¿Eliminar este banner del slider?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn-peligro">Eliminar</button>
          </form>
        </div>
        <form method="post" action="{{ route('admin.portada.actualizar', $m) }}" class="banner-edicion" enctype="multipart/form-data">
          @csrf @method('PUT')
          <label>Link al tocarlo (opcional)</label>
          <input type="text" name="linkUrl" value="{{ $m->link_url }}" placeholder="www.minegocio.cl">
          <label>Versión celular (opcional)</label>
          @if ($m->archivo_movil)
            <div style="display:flex;gap:10px;align-items:center;margin-bottom:6px">
              <img src="{{ $m->urlMovil() }}" alt="" style="height:48px;width:auto;aspect-ratio:auto;border-radius:6px">
              <label style="font-size:13px;display:flex;gap:4px;align-items:center;margin:0"><input type="checkbox" name="quitarMovil" value="1"> Quitar</label>
            </div>
          @endif
          <input type="file" name="movil" accept="image/*">
          <div style="margin-top:8px"><button type="submit" class="btn btn-acento" style="padding:7px 14px;font-size:14px">Guardar</button></div>
        </form>
      </div>
    @endforeach
  </div>

  @if ($medios->count() < $maxMedios)
    <form method="post" action="{{ route('admin.portada.subir') }}" enctype="multipart/form-data" style="margin-top:20px;padding-top:20px;border-top:1px solid #f0f0f2">
      @csrf
      <h2 style="margin-bottom:14px">Agregar banner al slider</h2>
      <div class="form-grupo">
        <label>Imagen o video</label>
        <input type="file" name="archivo" accept="image/*,video/*,.mov,.m4v" required data-por-trozos>
        <p class="admin-ayuda">Mismo formato que el banner de AutoRuta: <strong>2087 × 753 px</strong> (o 1920 × 693). Video MP4, MOV o WEBM corto; máximo 20 MB.</p>
      </div>
      <div class="form-grupo">
        <label>Link al tocarlo (opcional)</label>
        <input type="text" name="linkUrl" placeholder="www.minegocio.cl o https://wa.me/56912345678" value="{{ old('linkUrl') }}">
      </div>
      <div class="form-grupo">
        <label>Versión celular (opcional)</label>
        <input type="file" name="movil" accept="image/*">
        <p class="admin-ayuda">Imagen para teléfonos con el mismo formato del banner, pero con textos más grandes para que se lean en pantalla chica. Si no subes una, en celular se usa la principal.</p>
      </div>
      <button type="submit" class="btn btn-acento">Agregar al slider</button>
    </form>
  @endif
</div>
@include('partials.subida-trozos-script')
@endsection
