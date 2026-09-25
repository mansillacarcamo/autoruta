@extends('layouts.admin')
@section('titulo', $negocio->nombre_negocio)

@section('contenido')
<h1>{{ $negocio->nombre_negocio }}</h1>

<form method="post" action="{{ route('admin.negocios.actualizar', $negocio) }}" class="mt-2">
  @csrf @method('PUT')
  <div class="form-grupo"><label>Nombre del negocio</label><input type="text" name="nombreNegocio" required value="{{ $negocio->nombre_negocio }}"></div>
  <div class="form-grupo">
    <label>Rubro</label>
    <select name="rubro">
      @foreach (\App\Models\Anunciante::ETIQUETA_RUBRO as $v => $t)
        <option value="{{ $v }}" @selected($negocio->rubro === $v)>{{ $t }}</option>
      @endforeach
    </select>
  </div>
  <div class="form-grupo"><label>Descripción</label><textarea name="descripcion" rows="3" maxlength="300">{{ $negocio->descripcion }}</textarea></div>
  <div class="form-grupo"><label>WhatsApp</label><input type="text" name="telefonoWhatsapp" value="{{ $negocio->telefono_whatsapp }}"></div>
  <div class="form-grupo"><label>Sitio web</label><input type="text" name="sitioWeb" value="{{ $negocio->sitio_web }}"></div>
  <div class="form-grupo">
    <label>Estado</label>
    <select name="estado">
      <option value="activo" @selected($negocio->estado === 'activo')>Activo</option>
      <option value="pausado" @selected($negocio->estado === 'pausado')>Pausado</option>
    </select>
  </div>
  <button type="submit" class="btn btn-acento btn-block">Guardar cambios</button>
</form>

<hr class="mt-3" style="border:none;border-top:1px solid #e5e5e5">

<h2 class="mt-2">Banners ({{ $negocio->banners->count() }}/{{ config('autoruta.max_banners_negocio') }})</h2>
<div class="grid-2 mt-2">
  @foreach ($negocio->banners as $b)
    <div class="caja" style="padding:0;overflow:hidden">
      @if ($b->tipo_medio === 'video')
        <video src="{{ $b->url() }}" style="width:100%;aspect-ratio:16/9;object-fit:cover" muted></video>
      @else
        <img src="{{ $b->url() }}" style="width:100%;aspect-ratio:16/9;object-fit:cover">
      @endif
      <div style="display:flex;justify-content:space-between;padding:8px 12px">
        <span style="font-size:12px;font-weight:600">{{ \App\Models\Anunciante::ETIQUETA_POSICION[$b->posicion] ?? $b->posicion }}</span>
        <form method="post" action="{{ route('admin.negocios.banners.eliminar', [$negocio, $b]) }}">
          @csrf @method('DELETE')
          <button type="submit" style="background:none;border:none;color:#b91c1c;font-size:12px;font-weight:600;cursor:pointer">Eliminar</button>
        </form>
      </div>
    </div>
  @endforeach
</div>

@if ($negocio->banners->count() < config('autoruta.max_banners_negocio'))
<form method="post" action="{{ route('admin.negocios.banners.subir', $negocio) }}" enctype="multipart/form-data" class="caja mt-2">
  @csrf
  <div class="grid-2">
    <div class="form-grupo">
      <label>Tipo</label>
      <select name="tipoMedio"><option value="imagen">Imagen</option><option value="video">Video</option></select>
    </div>
    <div class="form-grupo">
      <label>Posición</label>
      <select name="posicion">
        @foreach (\App\Models\Anunciante::ETIQUETA_POSICION as $v => $t)<option value="{{ $v }}">{{ $t }}</option>@endforeach
      </select>
    </div>
  </div>
  <div class="form-grupo"><label>Link al hacer clic</label><input type="text" name="linkUrl" required placeholder="https://..."></div>
  <div class="form-grupo"><label>Archivo</label><input type="file" name="archivo" accept="image/*,video/*" required></div>
  <button type="submit" class="btn btn-acento">Subir banner</button>
</form>
@endif
@endsection
