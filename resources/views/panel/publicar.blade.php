@extends('layouts.app')
@section('titulo', 'Publicar vehículo')

@section('contenido')
<div class="contenedor" style="max-width:760px;padding:32px 16px">
  <h1>Publicar vehículo</h1>
  <p class="texto-mutado">Publicar siempre es gratis, sin límites.</p>

  @if ($errors->any())
    <div class="alerta-error mt-2">
      <ul style="margin:0;padding-left:18px">
        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
      </ul>
    </div>
  @endif

  <form method="post" action="{{ route('panel.publicar.guardar') }}" enctype="multipart/form-data" class="mt-3">
    @csrf
    <h2>1. Datos básicos</h2>
    <div class="grid-2">
      <div class="form-grupo">
        <label>Tipo de vehículo</label>
        <select name="tipo" required>
          @foreach (\App\Models\Vehiculo::ETIQUETA_TIPO as $valor => $etiqueta)
            <option value="{{ $valor }}">{{ $etiqueta }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-grupo">
        <label>Marca</label>
        <select name="marca" required>
          @foreach (config('marcas') as $m)<option value="{{ $m }}">{{ $m }}</option>@endforeach
        </select>
      </div>
      <div class="form-grupo"><label>Modelo</label><input type="text" name="modelo" required placeholder="Ej. Hilux"></div>
      <div class="form-grupo"><label>Año</label><input type="number" name="anio" required placeholder="Ej. 2020"></div>
      <div class="form-grupo"><label>Kilometraje</label><input type="number" name="kilometraje" required placeholder="Ej. 45000"></div>
      <div class="form-grupo"><label>Precio (CLP)</label><input type="number" name="precio" required placeholder="Ej. 12500000"></div>
      <div class="form-grupo">
        <label>Transmisión</label>
        <select name="transmision"><option value="">Selecciona</option><option value="manual">Manual</option><option value="automatica">Automática</option></select>
      </div>
      <div class="form-grupo">
        <label>Combustible</label>
        <select name="combustible">
          <option value="">Selecciona</option>
          @foreach (\App\Models\Vehiculo::ETIQUETA_COMBUSTIBLE as $valor => $etiqueta)<option value="{{ $valor }}">{{ $etiqueta }}</option>@endforeach
        </select>
      </div>
      <div class="form-grupo">
        <label>Región</label>
        <select name="region" id="selectRegion" required onchange="actualizarComunas()">
          <option value="">Selecciona tu región</option>
          @foreach (array_keys(config('regiones')) as $r)
            <option value="{{ $r }}" @selected(auth()->user()->region === $r)>{{ $r }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-grupo">
        <label>Comuna</label>
        <select name="comuna" id="selectComuna" required><option value="">Selecciona una región primero</option></select>
      </div>
      <div class="form-grupo">
        <label>WhatsApp de contacto</label>
        <input type="text" name="telefonoWhatsapp" required placeholder="+56912345678" value="{{ auth()->user()->telefono_whatsapp }}">
      </div>
    </div>
    <div class="form-grupo"><label>Descripción</label><textarea name="descripcion" required rows="4" maxlength="3000"></textarea></div>

    <h2 class="mt-2">2. Fotos (hasta {{ config('autoruta.max_fotos_vehiculo') }})</h2>
    <input type="file" name="fotos[]" accept="image/*" multiple required>

    <h2 class="mt-2">3. Ficha técnica (opcional)</h2>
    <div class="grid-2">
      <div class="form-grupo"><label>Versión</label><input type="text" name="version"></div>
      <div class="form-grupo"><label>Cilindrada</label><input type="text" name="cilindrada" placeholder="Ej. 2.0L"></div>
      <div class="form-grupo"><label>Color</label><input type="text" name="color"></div>
      <div class="form-grupo"><label>Puertas</label><input type="number" name="puertas" min="2" max="6"></div>
      <div class="form-grupo">
        <label>Tracción</label>
        <select name="traccion"><option value="">Selecciona</option><option value="4x2">4x2</option><option value="4x4">4x4</option><option value="awd">AWD</option></select>
      </div>
      <div class="form-grupo"><label>Dueños anteriores</label><input type="number" name="duenosAnteriores" min="0"></div>
    </div>
    <div class="form-grupo"><label>Equipamiento (separado por comas)</label><input type="text" name="equipamiento" placeholder="Aire acondicionado, Bluetooth"></div>

    <button type="submit" class="btn btn-acento btn-block mt-2" style="padding:14px">Publicar vehículo</button>
  </form>
</div>

<script>
const comunasPorRegion = @json(config('regiones'));
function actualizarComunas() {
  const region = document.getElementById('selectRegion').value;
  const select = document.getElementById('selectComuna');
  select.innerHTML = '<option value="">Selecciona</option>';
  (comunasPorRegion[region] || []).forEach(c => {
    const op = document.createElement('option');
    op.value = c; op.textContent = c;
    select.appendChild(op);
  });
}
document.addEventListener('DOMContentLoaded', actualizarComunas);
</script>
@endsection
