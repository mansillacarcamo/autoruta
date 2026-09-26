@extends('layouts.app')
@section('titulo', 'Publicar vehículo')

@section('contenido')
<div class="contenedor" style="max-width:760px;padding:32px 16px">
  <h1>Publicar vehículo</h1>
  <p class="texto-mutado">Publicar siempre es gratis, sin límites.</p>

  @if ($errors->any())
    <div class="alerta-error mt-2">
      <strong>No se pudo publicar. Revisa lo siguiente:</strong>
      <ul style="margin:6px 0 0;padding-left:18px">
        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
      </ul>
      <p style="margin:8px 0 0">Tus datos se mantuvieron, pero por seguridad debes volver a seleccionar las fotos.</p>
    </div>
  @endif

  <form method="post" action="{{ route('panel.publicar.guardar') }}" enctype="multipart/form-data" class="mt-3" id="formPublicar">
    @csrf
    <h2>1. Datos básicos</h2>
    <div class="grid-2">
      <div class="form-grupo">
        <label>Tipo de vehículo</label>
        <select name="tipo" required>
          @foreach (\App\Models\Vehiculo::ETIQUETA_TIPO as $valor => $etiqueta)
            <option value="{{ $valor }}" @selected(old('tipo') === $valor)>{{ $etiqueta }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-grupo">
        <label>Marca</label>
        <select name="marca" required>
          @foreach (config('marcas') as $m)<option value="{{ $m }}" @selected(old('marca') === $m)>{{ $m }}</option>@endforeach
        </select>
      </div>
      <div class="form-grupo"><label>Modelo</label><input type="text" name="modelo" required placeholder="Ej. Hilux" value="{{ old('modelo') }}"></div>
      <div class="form-grupo"><label>Año</label><input type="number" name="anio" required placeholder="Ej. 2020" value="{{ old('anio') }}"></div>
      <div class="form-grupo"><label>Kilometraje</label><input type="number" name="kilometraje" required placeholder="Ej. 45000" value="{{ old('kilometraje') }}"></div>
      <div class="form-grupo"><label>Precio (CLP)</label><input type="number" name="precio" required placeholder="Ej. 12500000" value="{{ old('precio') }}"></div>
      <div class="form-grupo">
        <label>Transmisión</label>
        <select name="transmision">
          <option value="">Selecciona</option>
          @foreach (\App\Models\Vehiculo::ETIQUETA_TRANSMISION as $valor => $etiqueta)<option value="{{ $valor }}" @selected(old('transmision') === $valor)>{{ $etiqueta }}</option>@endforeach
        </select>
      </div>
      <div class="form-grupo">
        <label>Combustible</label>
        <select name="combustible">
          <option value="">Selecciona</option>
          @foreach (\App\Models\Vehiculo::ETIQUETA_COMBUSTIBLE as $valor => $etiqueta)<option value="{{ $valor }}" @selected(old('combustible') === $valor)>{{ $etiqueta }}</option>@endforeach
        </select>
      </div>
      <div class="form-grupo">
        <label>Región</label>
        <select name="region" id="selectRegion" required onchange="actualizarComunas()">
          <option value="">Selecciona tu región</option>
          @foreach (array_keys(config('regiones')) as $r)
            <option value="{{ $r }}" @selected(old('region', auth()->user()->region) === $r)>{{ $r }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-grupo">
        <label>Comuna</label>
        <select name="comuna" id="selectComuna" required data-seleccionada="{{ old('comuna', auth()->user()->comuna) }}"><option value="">Selecciona una región primero</option></select>
      </div>
      <div class="form-grupo">
        <label>WhatsApp de contacto</label>
        <input type="text" name="telefonoWhatsapp" required placeholder="+56912345678" value="{{ old('telefonoWhatsapp', auth()->user()->telefono_whatsapp) }}">
        <p class="texto-mutado" style="font-size:12px;margin:4px 0 0">Los compradores te escribirán a este número desde el botón de WhatsApp de tu aviso.</p>
      </div>
    </div>
    <div class="form-grupo"><label>Descripción</label><textarea name="descripcion" required rows="4" maxlength="3000">{{ old('descripcion') }}</textarea></div>

    <h2 class="mt-2">2. Fotos (hasta {{ config('autoruta.max_fotos_vehiculo') }})</h2>
    <input type="file" name="fotos[]" id="inputFotos" accept="image/*" multiple required>
    <p class="texto-mutado" id="estadoFotos" style="font-size:13px;margin:6px 0 0">Puedes tomarlas con la cámara o elegirlas de tu galería. Las achicamos automáticamente para que suban rápido.</p>
    <div id="vistaFotos" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(90px,1fr));gap:8px;margin-top:10px"></div>

    <h2 class="mt-2">3. Ficha técnica (opcional)</h2>
    <div class="grid-2">
      <div class="form-grupo"><label>Versión</label><input type="text" name="version" value="{{ old('version') }}"></div>
      <div class="form-grupo"><label>Cilindrada</label><input type="text" name="cilindrada" placeholder="Ej. 2.0L" value="{{ old('cilindrada') }}"></div>
      <div class="form-grupo"><label>Color</label><input type="text" name="color" value="{{ old('color') }}"></div>
      <div class="form-grupo"><label>Puertas</label><input type="number" name="puertas" min="2" max="6" value="{{ old('puertas') }}"></div>
      <div class="form-grupo">
        <label>Tracción</label>
        <select name="traccion">
          <option value="">Selecciona</option>
          @foreach (\App\Models\Vehiculo::ETIQUETA_TRACCION as $valor => $etiqueta)<option value="{{ $valor }}" @selected(old('traccion') === $valor)>{{ $etiqueta }}</option>@endforeach
        </select>
      </div>
      <div class="form-grupo"><label>Dueños anteriores</label><input type="number" name="duenosAnteriores" min="0" value="{{ old('duenosAnteriores') }}"></div>
    </div>

    <button type="submit" id="botonPublicar" class="btn btn-acento btn-block mt-2" style="padding:14px">Publicar vehículo</button>
  </form>
</div>

<script>
const comunasPorRegion = @json(config('regiones'));
function actualizarComunas() {
  const region = document.getElementById('selectRegion').value;
  const select = document.getElementById('selectComuna');
  const seleccionada = select.dataset.seleccionada;
  select.innerHTML = '<option value="">Selecciona</option>';
  (comunasPorRegion[region] || []).forEach(c => {
    const op = document.createElement('option');
    op.value = c; op.textContent = c;
    if (c === seleccionada) op.selected = true;
    select.appendChild(op);
  });
}
document.addEventListener('DOMContentLoaded', actualizarComunas);

// Las fotos de celular pesan 3-8 MB; se reducen en el navegador antes de enviarlas
// para no chocar con el límite de subida del servidor.
(function () {
  const MAX_FOTOS = {{ config('autoruta.max_fotos_vehiculo') }};
  const LADO_MAX = 1600;
  const input = document.getElementById('inputFotos');
  const vista = document.getElementById('vistaFotos');
  const estado = document.getElementById('estadoFotos');
  const boton = document.getElementById('botonPublicar');
  let preparando = false;

  function reducir(archivo) {
    return new Promise(resolve => {
      const url = URL.createObjectURL(archivo);
      const img = new Image();
      img.onload = () => {
        const escala = Math.min(1, LADO_MAX / Math.max(img.width, img.height));
        const canvas = document.createElement('canvas');
        canvas.width = Math.round(img.width * escala);
        canvas.height = Math.round(img.height * escala);
        canvas.getContext('2d').drawImage(img, 0, 0, canvas.width, canvas.height);
        URL.revokeObjectURL(url);
        canvas.toBlob(blob => {
          if (!blob || blob.size >= archivo.size) return resolve(archivo);
          const nombre = archivo.name.replace(/\.[^.]+$/, '') + '.jpg';
          resolve(new File([blob], nombre, { type: 'image/jpeg' }));
        }, 'image/jpeg', 0.82);
      };
      img.onerror = () => { URL.revokeObjectURL(url); resolve(archivo); };
      img.src = url;
    });
  }

  input.addEventListener('change', async () => {
    let archivos = Array.from(input.files);
    if (!archivos.length) { vista.innerHTML = ''; return; }
    let aviso = '';
    if (archivos.length > MAX_FOTOS) {
      archivos = archivos.slice(0, MAX_FOTOS);
      aviso = ` Solo se usarán las primeras ${MAX_FOTOS}.`;
    }
    preparando = true;
    boton.disabled = true;
    boton.textContent = 'Preparando fotos…';
    estado.textContent = `Preparando ${archivos.length} foto(s)…`;

    const listas = [];
    for (const a of archivos) listas.push(await reducir(a));

    if (window.DataTransfer) {
      const dt = new DataTransfer();
      listas.forEach(f => dt.items.add(f));
      input.files = dt.files;
    }

    vista.innerHTML = '';
    listas.forEach(f => {
      const img = document.createElement('img');
      img.src = URL.createObjectURL(f);
      img.style.cssText = 'width:100%;aspect-ratio:1;object-fit:cover;border-radius:8px;border:1px solid #e5e5e5';
      vista.appendChild(img);
    });
    const totalMb = (listas.reduce((s, f) => s + f.size, 0) / 1048576).toFixed(1);
    estado.textContent = `${listas.length} foto(s) listas (${totalMb} MB).` + aviso;
    preparando = false;
    boton.disabled = false;
    boton.textContent = 'Publicar vehículo';
  });

  document.getElementById('formPublicar').addEventListener('submit', e => {
    if (preparando) { e.preventDefault(); return; }
    boton.disabled = true;
    boton.textContent = 'Publicando…';
  });
})();
</script>
@endsection
