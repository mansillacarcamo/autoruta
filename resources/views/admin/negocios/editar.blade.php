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
    <div class="form-grupo"><label>WhatsApp</label><input type="text" name="telefonoWhatsapp" value="{{ $negocio->telefono_whatsapp }}" placeholder="+56 9 1234 5678"></div>
    <div class="form-grupo"><label>Sitio web</label><input type="text" name="sitioWeb" value="{{ $negocio->sitio_web }}" placeholder="www.minegocio.cl"></div>
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
            <div style="position:relative">
              <video src="{{ $b->url() }}" muted loop autoplay playsinline></video>
              @if ($b->urlLogo())
                <img src="{{ $b->urlLogo() }}" alt="" class="banner-logo" style="width:auto;aspect-ratio:auto;background:none">
              @endif
            </div>
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
          <form method="post" action="{{ route('admin.negocios.banners.actualizar', [$negocio, $b]) }}" class="banner-edicion" enctype="multipart/form-data">
            @csrf @method('PUT')
            @if ($b->tipo_medio === 'video')
              <label>Logo encima del video</label>
              @if ($b->logo)
                <div style="display:flex;gap:10px;align-items:center;margin-bottom:6px">
                  <img src="{{ $b->urlLogo() }}" alt="" style="height:40px;width:auto;aspect-ratio:auto;border-radius:6px;background:repeating-conic-gradient(#e5e5e5 0 25%,#fff 0 50%) 0 0/12px 12px">
                  <label style="font-size:12px;display:flex;gap:4px;align-items:center;margin:0"><input type="checkbox" name="quitarLogo" value="1"> Quitar</label>
                </div>
              @endif
              <input type="file" name="logo" accept="image/png,image/jpeg,image/webp">
            @endif
            <label>Segundo archivo (se alterna con el principal)</label>
            @if ($b->archivo_alterno)
              <div style="display:flex;gap:10px;align-items:center;margin-bottom:6px">
                @if ($b->tipo_alterno === 'video')
                  <video src="{{ $b->urlAlterno() }}" muted loop autoplay playsinline style="height:48px;width:auto;aspect-ratio:auto;border-radius:6px"></video>
                @else
                  <img src="{{ $b->urlAlterno() }}" alt="" style="height:48px;width:auto;aspect-ratio:auto;border-radius:6px">
                @endif
                <label style="font-size:12px;display:flex;gap:4px;align-items:center;margin:0"><input type="checkbox" name="quitarAlterno" value="1"> Quitar</label>
              </div>
            @endif
            <input type="file" name="alterno" accept="image/*,video/*,.mov,.m4v" data-por-trozos>
            <div style="display:flex;gap:10px">
              <div style="flex:1"><label>Segundos principal</label><input type="number" name="segundosPrincipal" min="1" max="60" value="{{ $b->segundos_principal }}"></div>
              <div style="flex:1"><label>Segundos 2º archivo</label><input type="number" name="segundosAlterno" min="1" max="60" value="{{ $b->segundos_alterno }}"></div>
            </div>
            @if (str_starts_with($b->posicion, 'lateral'))
              <label>Versión celular (300 × 250)</label>
              @if ($b->archivo_movil)
                <div style="display:flex;gap:10px;align-items:center;margin-bottom:6px">
                  @if ($b->tipo_movil === 'video')
                    <video src="{{ $b->urlMovil() }}" muted loop autoplay playsinline style="height:48px;width:auto;aspect-ratio:auto;border-radius:6px"></video>
                  @else
                    <img src="{{ $b->urlMovil() }}" alt="" style="height:48px;width:auto;aspect-ratio:auto;border-radius:6px">
                  @endif
                  <label style="font-size:12px;display:flex;gap:4px;align-items:center;margin:0"><input type="checkbox" name="quitarMovil" value="1"> Quitar</label>
                </div>
              @else
                <p class="admin-ayuda" style="margin-top:0">Sin versión celular: este aviso no aparece en teléfonos ni tablets.</p>
              @endif
              <input type="file" name="movil" accept="image/*,video/*,.mov,.m4v" data-por-trozos>
            @endif
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
          <select name="tipoMedio" id="tipoMedioNuevo"><option value="imagen">Imagen</option><option value="video">Video</option></select>
        </div>
      </div>
      <div class="form-grupo"><label>Link al hacer clic</label><input type="text" name="linkUrl" required placeholder="www.minegocio.cl o https://wa.me/56912345678" value="{{ old('linkUrl') }}"><p class="admin-ayuda">Puede ser la web del negocio, su Instagram o su WhatsApp (https://wa.me/569XXXXXXXX). Si no escribes https:// se agrega solo.</p></div>
      <div class="form-grupo"><label>Archivo</label><input type="file" name="archivo" accept="image/*,video/*" required data-por-trozos><p class="admin-ayuda">Imagen: JPG, PNG o WEBP. Video: MP4, MOV (iPhone), M4V, WEBM u OGG. Máximo 20 MB.</p></div>
      <div class="form-grupo">
        <label>Segundo archivo (opcional)</label>
        <input type="file" name="alterno" accept="image/*,video/*,.mov,.m4v" data-por-trozos>
        <p class="admin-ayuda">Imagen o video que se alterna con el archivo principal (por ejemplo, el diseño y un video del local). Mismo tamaño que el principal. Máximo 20 MB.</p>
      </div>
      <div class="form-grupo">
        <label>Versión celular (opcional, solo avisos laterales)</label>
        <input type="file" name="movil" accept="image/*,video/*,.mov,.m4v" data-por-trozos>
        <p class="admin-ayuda">Rectángulo de <strong>300 × 250 px</strong> (ideal 600 × 500 para que se vea nítido). En celulares y tablets solo aparecen los avisos laterales que tienen esta versión, de a uno en un slider. Imagen o video, máximo 20 MB.</p>
      </div>
      <div class="admin-campos">
        <div class="form-grupo"><label>Segundos del archivo principal</label><input type="number" name="segundosPrincipal" min="1" max="60" value="3"></div>
        <div class="form-grupo"><label>Segundos del segundo archivo</label><input type="number" name="segundosAlterno" min="1" max="60" value="3"><p class="admin-ayuda">Solo se usan si hay segundo archivo. Para un video, pon lo que dura (por ejemplo 8) para que se vea completo.</p></div>
      </div>
      <div class="form-grupo" id="campoLogo" hidden>
        <label>Logo (opcional)</label>
        <input type="file" name="logo" accept="image/png,image/jpeg,image/webp">
        <p class="admin-ayuda">Se muestra encima del video, centrado arriba. Ideal PNG con fondo transparente. Máximo 5 MB.</p>
      </div>
      <script>
        (function () {
          var tipo = document.getElementById('tipoMedioNuevo'), campo = document.getElementById('campoLogo');
          function mostrar() { campo.hidden = tipo.value !== 'video'; }
          tipo.addEventListener('change', mostrar);
          mostrar();
        })();
      </script>
      <button type="submit" class="btn btn-acento">Subir banner</button>
    </form>
  @endif
</div>
@include('partials.subida-trozos-script')
@endsection
