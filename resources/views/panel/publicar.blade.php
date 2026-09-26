@extends('layouts.app')
@section('titulo', $vehiculo ? 'Editar publicación' : 'Publicar vehículo')

@section('contenido')
<div class="contenedor" style="max-width:760px;padding:32px 16px">
  @if ($vehiculo)
    <p style="margin:0 0 8px"><a href="{{ route('panel') }}" class="texto-mutado" style="font-size:14px">← Volver a mi panel</a></p>
    <h1>Editar publicación</h1>
    <p class="texto-mutado">{{ $vehiculo->marca }} {{ $vehiculo->modelo }} {{ $vehiculo->anio }}</p>
  @else
    <h1>Publicar vehículo</h1>
    <p class="texto-mutado">Publicar siempre es gratis, sin límites.</p>
  @endif

  @if ($errors->any())
    <div class="alerta-error mt-2">
      <strong>No se pudo guardar. Revisa lo siguiente:</strong>
      <ul style="margin:6px 0 0;padding-left:18px">
        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
      </ul>
      <p style="margin:8px 0 0">Tus datos se mantuvieron, pero por seguridad debes volver a seleccionar las fotos.</p>
    </div>
  @endif

  <div class="alerta-error mt-2" id="erroresEnvio" hidden></div>

  <form method="post" action="{{ $vehiculo ? route('panel.actualizar', $vehiculo) : route('panel.publicar.guardar') }}" enctype="multipart/form-data" class="mt-3" id="formPublicar">
    @csrf
    @if ($vehiculo) @method('PUT') @endif
    <h2>1. Datos básicos</h2>
    <div class="grid-2">
      <div class="form-grupo">
        <label>Tipo de vehículo</label>
        <select name="tipo" required>
          @foreach (\App\Models\Vehiculo::ETIQUETA_TIPO as $valor => $etiqueta)
            <option value="{{ $valor }}" @selected(old('tipo', $vehiculo?->tipo) === $valor)>{{ $etiqueta }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-grupo">
        <label>Marca</label>
        <select name="marca" required>
          @foreach (config('marcas') as $m)<option value="{{ $m }}" @selected(old('marca', $vehiculo?->marca) === $m)>{{ $m }}</option>@endforeach
        </select>
      </div>
      <div class="form-grupo"><label>Modelo</label><input type="text" name="modelo" required placeholder="Ej. Hilux" value="{{ old('modelo', $vehiculo?->modelo) }}"></div>
      <div class="form-grupo"><label>Año</label><input type="number" name="anio" required placeholder="Ej. 2020" value="{{ old('anio', $vehiculo?->anio) }}"></div>
      <div class="form-grupo"><label>Kilometraje</label><input type="text" inputmode="numeric" class="con-puntos" name="kilometraje" required placeholder="Ej. 45.000" value="{{ old('kilometraje', $vehiculo?->kilometraje) }}"></div>
      <div class="form-grupo"><label>Precio (CLP)</label><div class="campo-precio"><span>$</span><input type="text" inputmode="numeric" class="con-puntos" name="precio" required placeholder="Ej. 12.500.000" value="{{ old('precio', $vehiculo?->precio) }}"></div></div>
      <div class="form-grupo">
        <label>Transmisión</label>
        <select name="transmision">
          <option value="">Selecciona</option>
          @foreach (\App\Models\Vehiculo::ETIQUETA_TRANSMISION as $valor => $etiqueta)<option value="{{ $valor }}" @selected(old('transmision', $vehiculo?->transmision) === $valor)>{{ $etiqueta }}</option>@endforeach
        </select>
      </div>
      <div class="form-grupo">
        <label>Combustible</label>
        <select name="combustible">
          <option value="">Selecciona</option>
          @foreach (\App\Models\Vehiculo::ETIQUETA_COMBUSTIBLE as $valor => $etiqueta)<option value="{{ $valor }}" @selected(old('combustible', $vehiculo?->combustible) === $valor)>{{ $etiqueta }}</option>@endforeach
        </select>
      </div>
      <div class="form-grupo">
        <label>Región</label>
        <select name="region" id="selectRegion" required onchange="actualizarComunas()">
          <option value="">Selecciona tu región</option>
          @foreach (array_keys(config('regiones')) as $r)
            <option value="{{ $r }}" @selected(old('region', $vehiculo?->region ?? auth()->user()->region) === $r)>{{ $r }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-grupo">
        <label>Comuna</label>
        <select name="comuna" id="selectComuna" required data-seleccionada="{{ old('comuna', $vehiculo?->comuna ?? auth()->user()->comuna) }}"><option value="">Selecciona una región primero</option></select>
      </div>
      <div class="form-grupo">
        <label>WhatsApp de contacto</label>
        <input type="text" name="telefonoWhatsapp" required placeholder="+56912345678" value="{{ old('telefonoWhatsapp', auth()->user()->telefono_whatsapp) }}">
        <p class="texto-mutado" style="font-size:12px;margin:4px 0 0">Los compradores te escribirán a este número desde el botón de WhatsApp de tu aviso.</p>
      </div>
    </div>
    <div class="form-grupo"><label>Descripción</label><textarea name="descripcion" required rows="4" maxlength="3000">{{ old('descripcion', $vehiculo?->descripcion) }}</textarea></div>

    <h2 class="mt-2">2. Fotos <span class="texto-mutado" id="contadorFotos" style="font-size:15px;font-weight:600"></span></h2>
    <p class="texto-mutado" style="font-size:13px;margin:0 0 10px">Hasta {{ config('autoruta.max_fotos_vehiculo') }} fotos en formato JPG, PNG, WEBP o HEIC (iPhone). Las achicamos automáticamente para que suban rápido.</p>

    @if ($vehiculo && $vehiculo->fotos->isNotEmpty())
      <div class="fotos-grilla" id="fotosActuales">
        @foreach ($vehiculo->fotos as $foto)
          <div class="foto-item" data-foto-id="{{ $foto->id }}">
            <img src="{{ \App\Support\Archivos::url('vehiculos/' . $foto->archivo) }}" alt="">
            <button type="button" class="foto-eliminar" data-url="{{ route('panel.fotos.eliminar', [$vehiculo, $foto]) }}" aria-label="Eliminar foto">&times;</button>
          </div>
        @endforeach
      </div>
    @endif

    <div class="fotos-botones">
      <label class="btn btn-outline-oscuro">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
        Tomar foto
        <input type="file" id="inputCamara" accept="image/*" capture="environment" hidden>
      </label>
      <label class="btn btn-outline-oscuro">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
        Elegir de la galería
        <input type="file" name="fotos[]" id="inputFotos" accept="image/jpeg,image/png,image/webp,image/heic,image/heif,image/*" multiple hidden>
      </label>
    </div>
    <p class="texto-mutado" id="estadoFotos" style="font-size:13px;margin:8px 0 0"></p>
    <div class="fotos-grilla" id="vistaFotos"></div>

    <h2 class="mt-2">3. Ficha técnica (opcional)</h2>
    <div class="grid-2">
      <div class="form-grupo"><label>Versión</label><input type="text" name="version" value="{{ old('version', $vehiculo?->version) }}"></div>
      <div class="form-grupo"><label>Cilindrada</label><input type="text" name="cilindrada" placeholder="Ej. 2.0L" value="{{ old('cilindrada', $vehiculo?->cilindrada) }}"></div>
      <div class="form-grupo"><label>Puertas</label><input type="number" name="puertas" min="2" max="6" value="{{ old('puertas', $vehiculo?->puertas) }}"></div>
      <div class="form-grupo">
        <label>Tracción</label>
        <select name="traccion">
          <option value="">Selecciona</option>
          @foreach (\App\Models\Vehiculo::ETIQUETA_TRACCION as $valor => $etiqueta)<option value="{{ $valor }}" @selected(old('traccion', $vehiculo?->traccion) === $valor)>{{ $etiqueta }}</option>@endforeach
        </select>
      </div>
      <div class="form-grupo"><label>Dueños anteriores</label><input type="number" name="duenosAnteriores" min="0" value="{{ old('duenosAnteriores', $vehiculo?->duenos_anteriores) }}"></div>
    </div>

    <button type="submit" id="botonPublicar" class="btn btn-acento btn-block mt-2" style="padding:14px">{{ $vehiculo ? 'Guardar cambios' : 'Publicar vehículo' }}</button>
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

// Precio y kilometraje con separador de miles mientras se escribe (15000000 -> 15.000.000).
document.querySelectorAll('.con-puntos').forEach(campo => {
  const formatear = () => {
    const digitos = campo.value.replace(/\D/g, '').replace(/^0+(?=\d)/, '');
    campo.value = digitos.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  };
  campo.addEventListener('input', formatear);
  formatear();
});

// Fotos: se pueden tomar con la cámara o elegir de la galería (se van sumando), se reducen
// en el navegador y el formulario se envía con fetch armando el FormData a mano. Así no
// dependemos de reemplazar input.files (Safari de iPhone a veces enviaba el formulario vacío).
(function () {
  const MAX_FOTOS = {{ config('autoruta.max_fotos_vehiculo') }};
  const ES_EDICION = {{ $vehiculo ? 'true' : 'false' }};
  const FORMATOS_OK = ['image/jpeg', 'image/png', 'image/webp'];
  const LADO_MAX = 1400;
  const CALIDAD = 0.78;
  const form = document.getElementById('formPublicar');
  const inputGaleria = document.getElementById('inputFotos');
  const inputCamara = document.getElementById('inputCamara');
  const vista = document.getElementById('vistaFotos');
  const estado = document.getElementById('estadoFotos');
  const contador = document.getElementById('contadorFotos');
  const boton = document.getElementById('botonPublicar');
  const cajaErrores = document.getElementById('erroresEnvio');
  const TEXTO_BOTON = boton.textContent.trim();
  const token = form.querySelector('[name=_token]').value;
  let fotosNuevas = [];
  let preparando = false;

  const fotosActuales = () => document.querySelectorAll('#fotosActuales .foto-item').length;

  function actualizarContador() {
    contador.textContent = `(${fotosActuales() + fotosNuevas.length}/${MAX_FOTOS})`;
  }

  // Convierte a JPEG de máx. 1400 px. HEIC/HEIF de iPhone se convierte si el navegador lo
  // puede abrir (Safari sí); si no, devuelve null para avisar en vez de subir algo inválido.
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
          const nombre = (archivo.name || 'foto').replace(/\.[^.]+$/, '') + '.jpg';
          if (blob && (blob.size < archivo.size || !FORMATOS_OK.includes(archivo.type))) {
            return resolve(new File([blob], nombre, { type: 'image/jpeg' }));
          }
          resolve(FORMATOS_OK.includes(archivo.type) ? archivo : null);
        }, 'image/jpeg', CALIDAD);
      };
      img.onerror = () => { URL.revokeObjectURL(url); resolve(FORMATOS_OK.includes(archivo.type) ? archivo : null); };
      img.src = url;
    });
  }

  function dibujarNuevas() {
    vista.innerHTML = '';
    fotosNuevas.forEach((f, i) => {
      const item = document.createElement('div');
      item.className = 'foto-item';
      const img = document.createElement('img');
      img.src = URL.createObjectURL(f);
      const quitar = document.createElement('button');
      quitar.type = 'button';
      quitar.className = 'foto-eliminar';
      quitar.setAttribute('aria-label', 'Quitar foto');
      quitar.innerHTML = '&times;';
      quitar.addEventListener('click', () => { fotosNuevas.splice(i, 1); dibujarNuevas(); });
      item.append(img, quitar);
      vista.appendChild(item);
    });
    actualizarContador();
  }

  async function agregar(input) {
    const elegidas = Array.from(input.files);
    input.value = '';
    if (!elegidas.length) return;
    const espacio = MAX_FOTOS - fotosActuales() - fotosNuevas.length;
    const avisos = [];
    if (espacio <= 0) { estado.textContent = `Ya tienes ${MAX_FOTOS} fotos, el máximo permitido.`; return; }
    if (elegidas.length > espacio) avisos.push(`Solo se agregaron ${espacio} (máximo ${MAX_FOTOS} fotos).`);

    preparando = true;
    boton.disabled = true;
    boton.textContent = 'Preparando fotos…';
    estado.textContent = 'Preparando fotos…';

    let rechazadas = 0;
    for (const a of elegidas.slice(0, espacio)) {
      const lista = await reducir(a);
      if (lista) fotosNuevas.push(lista); else rechazadas++;
    }
    if (rechazadas) avisos.push(`${rechazadas} foto(s) no se pudieron abrir en este navegador (formato HEIC u otro no compatible). En el iPhone ve a Ajustes > Cámara > Formatos y elige "Más compatible", o usa Safari.`);

    dibujarNuevas();
    const totalMb = (fotosNuevas.reduce((s, f) => s + f.size, 0) / 1048576).toFixed(1);
    estado.textContent = (fotosNuevas.length ? `${fotosNuevas.length} foto(s) nuevas listas (${totalMb} MB). ` : '') + avisos.join(' ');
    preparando = false;
    listo();
  }

  inputGaleria.addEventListener('change', () => agregar(inputGaleria));
  inputCamara.addEventListener('change', () => agregar(inputCamara));

  // Fotos ya publicadas (al editar): se eliminan al instante.
  document.querySelectorAll('#fotosActuales .foto-eliminar').forEach(b => {
    b.addEventListener('click', async () => {
      if (!confirm('¿Eliminar esta foto del aviso?')) return;
      b.disabled = true;
      try {
        const r = await fetch(b.dataset.url, {
          method: 'DELETE',
          credentials: 'same-origin',
          headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
        });
        const d = await r.json().catch(() => ({}));
        if (r.ok) { b.closest('.foto-item').remove(); actualizarContador(); estado.textContent = 'Foto eliminada.'; }
        else estado.textContent = d.message || 'No se pudo eliminar la foto.';
      } catch (err) {
        estado.textContent = 'No se pudo conectar con el servidor.';
      }
      b.disabled = false;
    });
  });

  function mostrarErrores(lista) {
    cajaErrores.innerHTML = '<strong>No se pudo guardar. Revisa lo siguiente:</strong><ul style="margin:6px 0 0;padding-left:18px">' +
      lista.map(t => '<li>' + String(t).replace(/</g, '&lt;') + '</li>').join('') + '</ul>';
    cajaErrores.hidden = false;
    cajaErrores.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }

  function listo() {
    boton.disabled = false;
    boton.textContent = TEXTO_BOTON;
  }

  form.addEventListener('submit', async e => {
    if (!window.fetch || !window.FormData) return;
    e.preventDefault();
    if (preparando) return;
    if (!ES_EDICION && !fotosNuevas.length) { mostrarErrores(['Agrega al menos una foto del vehículo.']); return; }

    const datos = new FormData(form);
    datos.delete('fotos[]');
    fotosNuevas.forEach(f => datos.append('fotos[]', f, f.name));

    cajaErrores.hidden = true;
    boton.disabled = true;
    boton.textContent = 'Guardando…';

    try {
      const r = await fetch(form.action, {
        method: 'POST',
        body: datos,
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      });
      if (r.ok) {
        const d = await r.json();
        window.location.href = d.redirect;
        return;
      }
      let d = {};
      try { d = await r.json(); } catch (err) {}
      if (r.status === 422 && d.errors) mostrarErrores(Object.values(d.errors).flat());
      else if (r.status === 413) mostrarErrores([d.message || 'Las fotos pesan demasiado. Sube menos fotos o fotos más livianas.']);
      else if (r.status === 419) mostrarErrores(['Tu sesión expiró. Recarga la página e inténtalo de nuevo.']);
      else mostrarErrores([`Ocurrió un error en el servidor (código ${r.status}). Inténtalo de nuevo en unos minutos.`]);
    } catch (err) {
      mostrarErrores(['No se pudo conectar con el servidor. Revisa tu conexión a internet e inténtalo de nuevo.']);
    }
    listo();
  });

  actualizarContador();
})();
</script>
@endsection
