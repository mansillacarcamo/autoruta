@extends('layouts.admin')
@section('titulo', $negocio->nombre_negocio)

@section('contenido')
<p style="margin:0 0 16px"><a href="{{ route('admin.negocios.index') }}" class="texto-mutado" style="font-size:14px">← Volver a Publicidad</a></p>

<form method="post" action="{{ route('admin.negocios.actualizar', $negocio) }}" class="admin-tarjeta">
  @csrf @method('PUT')
  <h2 style="margin-bottom:16px">Datos del negocio</h2>
  <div class="admin-campos">
    <div class="form-grupo"><label>Nombre del negocio</label><input type="text" name="nombreNegocio" required value="{{ $negocio->nombre_negocio }}"></div>
    <div class="form-grupo">
      <label>Rubro</label>
      <select name="rubro">@foreach (\App\Models\Anunciante::ETIQUETA_RUBRO as $v => $t)<option value="{{ $v }}" @selected($negocio->rubro === $v)>{{ $t }}</option>@endforeach</select>
    </div>
    <div class="form-grupo"><label>WhatsApp</label><input type="text" name="telefonoWhatsapp" value="{{ $negocio->telefono_whatsapp }}"></div>
    <div class="form-grupo"><label>Sitio web</label><input type="text" name="sitioWeb" value="{{ $negocio->sitio_web }}"></div>
  </div>
  <div class="form-grupo"><label>Descripción</label><textarea name="descripcion" rows="3" maxlength="300">{{ $negocio->descripcion }}</textarea></div>
  <div class="form-grupo">
    <label>Estado</label>
    <select name="estado">
      <option value="activo" @selected($negocio->estado === 'activo')>Activo</option>
      <option value="pausado" @selected($negocio->estado === 'pausado')>Pausado</option>
    </select>
  </div>
  <button type="submit" class="btn btn-acento">Guardar cambios</button>
</form>

<div class="admin-tarjeta">
  <div class="admin-tarjeta-cabecera">
    <h2>Banners ({{ $negocio->banners->count() }}/{{ config('autoruta.max_banners_negocio') }})</h2>
  </div>
  @if ($negocio->banners->isEmpty())
    <p class="admin-vacio">Este negocio todavía no tiene banners.</p>
  @else
    <div class="admin-medios">
      @foreach ($negocio->banners as $b)
        <div class="admin-medio">
          @if ($b->tipo_medio === 'video')
            <video src="{{ $b->url() }}" muted controls></video>
          @else
            <img src="{{ $b->url() }}" alt="">
          @endif
          <div class="admin-medio-pie">
            <span>{{ \App\Models\Anunciante::POSICIONES[$b->posicion][0] ?? (\App\Models\Anunciante::ETIQUETA_POSICION[$b->posicion] ?? $b->posicion) }}</span>
            <span class="admin-etiqueta verde" title="Personas que hicieron clic en este banner">{{ number_format($b->clics, 0, ',', '.') }} clics</span>
            <form method="post" action="{{ route('admin.negocios.banners.eliminar', [$negocio, $b]) }}" onsubmit="return confirm('¿Eliminar este banner?')">
              @csrf @method('DELETE')
              <button type="submit" class="btn-peligro">Eliminar</button>
            </form>
          </div>
          <form method="post" action="{{ route('admin.negocios.banners.actualizar', [$negocio, $b]) }}" class="banner-edicion">
            @csrf @method('PUT')
            <label>Link al hacer clic</label>
            <input type="text" name="linkUrl" required value="{{ $b->link_url }}" placeholder="https://www.minegocio.cl">
            <label>Ubicación</label>
            <select name="posicion">
              @foreach (\App\Models\Anunciante::POSICIONES as $v => [$etiqueta, $medida])
                <option value="{{ $v }}" @selected($b->posicion === $v)>{{ $etiqueta }} — {{ $medida }}</option>
              @endforeach
            </select>
            <div style="display:flex;gap:10px;align-items:center;margin-top:8px">
              <button type="submit" class="btn btn-acento" style="padding:7px 14px;font-size:13px">Guardar</button>
              <a href="{{ $b->urlClic() }}" target="_blank" rel="noopener" style="font-size:12px;color:#525252">Probar link ↗</a>
            </div>
          </form>
        </div>
      @endforeach
    </div>
  @endif

  @if ($negocio->banners->count() < config('autoruta.max_banners_negocio'))
    <form method="post" action="{{ route('admin.negocios.banners.subir', $negocio) }}" enctype="multipart/form-data" style="margin-top:20px;padding-top:20px;border-top:1px solid #f0f0f2">
      @csrf
      <h2 style="margin-bottom:14px">Subir nuevo banner</h2>
      <div class="admin-campos">
        <div class="form-grupo">
          <label>Ubicación y medida</label>
          <select name="posicion">
            @foreach (\App\Models\Anunciante::POSICIONES as $v => [$etiqueta, $medida])
              <option value="{{ $v }}">{{ $etiqueta }} — {{ $medida }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-grupo">
          <label>Tipo</label>
          <select name="tipoMedio"><option value="imagen">Imagen</option><option value="video">Video</option></select>
        </div>
      </div>
      <div class="form-grupo"><label>Link al hacer clic</label><input type="text" name="linkUrl" required placeholder="www.minegocio.cl o https://wa.me/56912345678" value="{{ old('linkUrl') }}"><p class="admin-ayuda">Puede ser la web del negocio, su Instagram o su WhatsApp (https://wa.me/569XXXXXXXX). Si no escribes https:// se agrega solo.</p></div>
      <div class="form-grupo"><label>Archivo</label><input type="file" name="archivo" accept="image/*,video/*" required><p class="admin-ayuda">JPG, PNG, WEBP, MP4 o WEBM. Máximo 20 MB.</p></div>
      <button type="submit" class="btn btn-acento">Subir banner</button>
    </form>
  @endif
</div>
@endsection
