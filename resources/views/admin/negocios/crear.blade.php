@extends('layouts.admin')
@section('titulo', 'Nuevo negocio')

@section('contenido')
<h1>Nuevo negocio</h1>
<form method="post" action="{{ route('admin.negocios.guardar') }}" class="mt-2">
  @csrf
  <div class="form-grupo"><label>Nombre del negocio</label><input type="text" name="nombreNegocio" required></div>
  <div class="form-grupo">
    <label>Rubro</label>
    <select name="rubro"><option value="financiera">Financiera</option><option value="taller">Taller mecánico</option><option value="otro">Otro</option></select>
  </div>
  <div class="form-grupo"><label>Descripción</label><textarea name="descripcion" rows="3" maxlength="300"></textarea></div>
  <div class="form-grupo"><label>WhatsApp (opcional)</label><input type="text" name="telefonoWhatsapp" placeholder="+56912345678"></div>
  <div class="form-grupo"><label>Sitio web (opcional)</label><input type="text" name="sitioWeb" placeholder="https://..."></div>
  <div class="form-grupo">
    <label>Estado</label>
    <select name="estado"><option value="activo">Activo (visible en el sitio)</option><option value="pausado">Pausado (oculto)</option></select>
  </div>
  <button type="submit" class="btn btn-acento btn-block">Crear negocio</button>
</form>
@endsection
