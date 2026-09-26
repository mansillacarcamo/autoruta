@extends('layouts.admin')
@section('titulo', 'Nuevo negocio')

@section('contenido')
<p style="margin:0 0 16px"><a href="{{ route('admin.negocios.index') }}" class="texto-mutado" style="font-size:14px">← Volver a Publicidad</a></p>
<form method="post" action="{{ route('admin.negocios.guardar') }}" class="admin-tarjeta">
  @csrf
  <h2 style="margin-bottom:16px">Datos del negocio</h2>
  <div class="admin-campos">
    <div class="form-grupo"><label>Nombre del negocio</label><input type="text" name="nombreNegocio" required value="{{ old('nombreNegocio') }}"></div>
    <div class="form-grupo">
      <label>Rubro</label>
      <select name="rubro">@foreach (\App\Models\Anunciante::ETIQUETA_RUBRO as $v => $t)<option value="{{ $v }}" @selected(old('rubro') === $v)>{{ $t }}</option>@endforeach</select>
    </div>
    <div class="form-grupo"><label>WhatsApp (opcional)</label><input type="text" name="telefonoWhatsapp" placeholder="+56912345678" value="{{ old('telefonoWhatsapp') }}"></div>
    <div class="form-grupo"><label>Sitio web (opcional)</label><input type="text" name="sitioWeb" placeholder="https://..." value="{{ old('sitioWeb') }}"></div>
  </div>
  <div class="form-grupo"><label>Descripción</label><textarea name="descripcion" rows="3" maxlength="300">{{ old('descripcion') }}</textarea></div>
  <div class="form-grupo">
    <label>Estado</label>
    <select name="estado"><option value="activo">Activo (visible en el sitio)</option><option value="pausado">Pausado (oculto)</option></select>
  </div>
  <button type="submit" class="btn btn-acento">Crear negocio</button>
</form>
@endsection
