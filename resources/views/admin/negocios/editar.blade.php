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
          @if ($b->zonaVideo())
            {{-- Con zona: la vista previa en vivo es el editor de zona de más abajo --}}
            <img src="{{ $b->urlCapa() }}" alt="">
          @elseif ($b->tipo_medio === 'video')
            {{-- Vista previa: video con su imagen encima, tal como se ve en la web --}}
            <div style="position:relative">
              <video src="{{ $b->url() }}" muted loop autoplay playsinline></video>
              @if ($b->archivo_capa)
                <img src="{{ $b->urlCapa() }}" alt="" id="capaPrevia{{ $b->id }}"
                     style="position:absolute;inset:0;height:100%;background:none;pointer-events:none;opacity:{{ $b->opacidad_capa / 100 }}">
              @endif
            </div>
          @else
            <img src="{{ $b->url() }}" alt="" id="capaPrevia{{ $b->id }}" style="opacity:{{ $b->opacidad_capa / 100 }}">
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
              <label>Imagen encima del video</label>
              @if ($b->archivo_capa)
                <div style="display:flex;gap:10px;align-items:center;margin-bottom:6px">
                  <img src="{{ $b->urlCapa() }}" alt="" style="height:48px;width:auto;border-radius:6px;background:repeating-conic-gradient(#e5e5e5 0 25%,#fff 0 50%) 0 0/12px 12px">
                  <label style="font-size:12px;display:flex;gap:4px;align-items:center;margin:0"><input type="checkbox" name="quitarCapa" value="1"> Quitar</label>
                </div>
              @endif
              <input type="file" name="capa" accept="image/png,image/webp">
              <label>Opacidad de la imagen: <strong data-valor-opacidad>{{ $b->opacidad_capa }}%</strong></label>
              <input type="range" name="opacidadCapa" min="30" max="100" step="5" value="{{ $b->opacidad_capa }}"
                     data-opacidad data-previa="capaPrevia{{ $b->id }}">
              <p class="admin-ayuda">Bájala para que el video se vea a través del diseño. Entre 75% y 85% suele verse el movimiento sin perder los textos. Mueve la barra para verlo en la vista previa y luego Guardar.</p>
            @else
              <label>Video de fondo (opcional)</label>
              <input type="file" name="videoFondo" accept="video/mp4,video/webm">
              <label>Opacidad de la imagen: <strong data-valor-opacidad>{{ $b->opacidad_capa }}%</strong></label>
              <input type="range" name="opacidadCapa" min="30" max="100" step="5" value="{{ $b->opacidad_capa }}"
                     data-opacidad data-previa="capaPrevia{{ $b->id }}">
              <p class="admin-ayuda">La opacidad se aplica a la imagen (mueve la barra para verlo en la vista previa y luego Guardar). Si además subes un video (MP4 o WEBM, máx. 20 MB, mismo formato que la imagen), la imagen pasa a ir encima de él; entre 75% y 85% se ve el video sin perder los textos.</p>
            @endif
            @php($imagenZona = $b->tipo_medio === 'imagen' ? $b->url() : $b->urlCapa())
            @if ($imagenZona)
              @php($zona = is_array($b->zona_video) && count($b->zona_video) === 4 ? $b->zona_video : null)
              <label>Zona del video dentro de la imagen</label>
              <div class="zona-editor" data-zona-editor>
                <img src="{{ $imagenZona }}" alt="" draggable="false">
                @if ($b->tipo_medio === 'video')
                  <video src="{{ $b->url() }}" class="banner-zona" muted loop autoplay playsinline data-zona-video
                         @if ($zona) style="left:{{ $zona[0] }}%;top:{{ $zona[1] }}%;width:{{ $zona[2] }}%;height:{{ $zona[3] }}%" @else hidden @endif></video>
                @endif
                <div class="zona-rect" data-zona-rect
                     @if ($zona) style="left:{{ $zona[0] }}%;top:{{ $zona[1] }}%;width:{{ $zona[2] }}%;height:{{ $zona[3] }}%" @else hidden @endif></div>
              </div>
              <input type="hidden" name="zonaVideo" value="{{ $zona ? implode(',', $zona) : '' }}" data-zona-valor>
              <button type="button" data-zona-limpiar style="font-size:12px;background:none;border:none;color:#525252;text-decoration:underline;cursor:pointer;padding:0">Quitar zona (video de fondo completo)</button>
              <p class="admin-ayuda">Arrastra el mouse sobre la imagen para marcar dónde se ve el video (por ejemplo, sobre la lista). Con zona, la imagen se ve completa y el video solo dentro del rectángulo. Luego Guardar.</p>
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
      <div class="form-grupo"><label>Archivo</label><input type="file" name="archivo" accept="image/*,video/*" required><p class="admin-ayuda">JPG, PNG, WEBP, MP4 o WEBM. Máximo 20 MB.</p></div>
      <div class="form-grupo" id="campoCapa" hidden>
        <label>Imagen encima del video (opcional)</label>
        <input type="file" name="capa" accept="image/png,image/webp">
        <p class="admin-ayuda">PNG o WEBP (logo, teléfono, texto) que queda fijo sobre el video. Hazla del mismo tamaño que el video para que calce. Máximo 5 MB.</p>
      </div>
      <div class="form-grupo" id="campoVideoFondo">
        <label>Video de fondo (opcional)</label>
        <input type="file" name="videoFondo" accept="video/mp4,video/webm">
        <p class="admin-ayuda">MP4 o WEBM, máximo 20 MB, mismo formato que la imagen. La imagen queda encima del video.</p>
      </div>
      <div class="form-grupo">
        <label>Opacidad de la imagen: <strong data-valor-opacidad>100%</strong></label>
        <input type="range" name="opacidadCapa" min="30" max="100" step="5" value="100" data-opacidad>
        <p class="admin-ayuda">Se aplica a la imagen. Si hay video, bájala para que se vea a través del diseño (75%–85% recomendado).</p>
      </div>
      <script>
        (function () {
          var tipo = document.getElementById('tipoMedioNuevo'), campo = document.getElementById('campoCapa'), fondo = document.getElementById('campoVideoFondo');
          function mostrar() { campo.hidden = tipo.value !== 'video'; fondo.hidden = tipo.value !== 'imagen'; }
          tipo.addEventListener('change', mostrar);
          mostrar();
        })();
      </script>
      <button type="submit" class="btn btn-acento">Subir banner</button>
    </form>
  @endif
</div>
<script>
  // Deslizadores de opacidad: muestran el % y actualizan la vista previa del banner.
  document.querySelectorAll('[data-opacidad]').forEach(function (barra) {
    var etiqueta = barra.parentNode.querySelector('[data-valor-opacidad]');
    var previa = barra.dataset.previa && document.getElementById(barra.dataset.previa);
    barra.addEventListener('input', function () {
      if (etiqueta) etiqueta.textContent = barra.value + '%';
      if (previa) previa.style.opacity = barra.value / 100;
    });
  });

  // Editor de zona del video: arrastrar sobre la imagen marca el rectángulo (en % de la imagen).
  document.querySelectorAll('[data-zona-editor]').forEach(function (editor) {
    var form = editor.closest('form');
    var rect = editor.querySelector('[data-zona-rect]'), video = editor.querySelector('[data-zona-video]');
    var valor = form.querySelector('[data-zona-valor]'), inicio = null;

    function pintar(z) {
      [rect, video].forEach(function (el) {
        if (!el) return;
        el.hidden = !z;
        if (z) { el.style.left = z[0] + '%'; el.style.top = z[1] + '%'; el.style.width = z[2] + '%'; el.style.height = z[3] + '%'; }
      });
    }
    function punto(e) {
      var r = editor.getBoundingClientRect();
      return [Math.min(Math.max((e.clientX - r.left) / r.width * 100, 0), 100), Math.min(Math.max((e.clientY - r.top) / r.height * 100, 0), 100)];
    }
    function zonaHasta(e) {
      var p = punto(e);
      return [Math.min(inicio[0], p[0]), Math.min(inicio[1], p[1]), Math.abs(p[0] - inicio[0]), Math.abs(p[1] - inicio[1])]
        .map(function (n) { return Math.round(n * 10) / 10; });
    }

    editor.addEventListener('pointerdown', function (e) { inicio = punto(e); editor.setPointerCapture(e.pointerId); e.preventDefault(); });
    editor.addEventListener('pointermove', function (e) { if (inicio) pintar(zonaHasta(e)); });
    editor.addEventListener('pointerup', function (e) {
      if (!inicio) return;
      var z = zonaHasta(e);
      inicio = null;
      if (z[2] < 3 || z[3] < 3) { // un clic sin arrastrar no cambia la zona
        var guardada = valor.value ? valor.value.split(',').map(Number) : null;
        pintar(guardada && guardada.length === 4 ? guardada : null);
        return;
      }
      valor.value = z.join(',');
      pintar(z);
    });
    form.querySelector('[data-zona-limpiar]').addEventListener('click', function () { valor.value = ''; pintar(null); });
  });
</script>
@endsection
