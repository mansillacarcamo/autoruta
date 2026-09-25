@extends('layouts.app')

@section('titulo', "{$vehiculo->marca} {$vehiculo->modelo} {$vehiculo->anio} – {$vehiculo->precioFormateado()}")

@section('contenido')
<div class="contenedor" style="padding:32px 16px">
  <a href="{{ route('vehiculos.index') }}" style="font-size:14px;font-weight:600;color:#525252">← Volver al listado</a>

  <div style="display:grid;gap:32px;margin-top:16px" class="ficha-grid">
    <div>
      <div class="tarjeta-foto" style="border-radius:12px">
        <img src="{{ $vehiculo->primeraFotoUrl() }}" alt="">
      </div>
      @if ($vehiculo->fotos->count() > 1)
        <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:8px;margin-top:10px">
          @foreach ($vehiculo->fotos as $f)
            <img src="{{ asset('storage/vehiculos/' . $f->archivo) }}" style="aspect-ratio:1;object-fit:cover;border-radius:8px">
          @endforeach
        </div>
      @endif

      <h1 class="mt-3">{{ $vehiculo->marca }} {{ $vehiculo->modelo }} {{ $vehiculo->anio }}</h1>
      <p class="tarjeta-precio" style="font-size:28px">{{ $vehiculo->precioFormateado() }}</p>
      <p class="texto-mutado">
        {{ \App\Models\Vehiculo::ETIQUETA_TIPO[$vehiculo->tipo] ?? $vehiculo->tipo }} ·
        {{ number_format($vehiculo->kilometraje, 0, ',', '.') }} km ·
        {{ $vehiculo->comuna }}, {{ $vehiculo->region }} ·
        {{ $vehiculo->vistas }} vistas
      </p>

      <h2 class="mt-3">Descripción</h2>
      <p style="white-space:pre-line">{{ $vehiculo->descripcion }}</p>

      @if ($ficha)
        <h2 class="mt-3">Ficha técnica</h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px">
          @foreach ($ficha as $etiqueta => $valor)
            <div class="caja">
              <p style="font-size:12px;color:#737373;margin:0">{{ $etiqueta }}</p>
              <p style="font-weight:600;margin:2px 0 0">{{ $valor }}</p>
            </div>
          @endforeach
        </div>
      @endif

      @if ($vehiculo->equipamiento)
        <h2 class="mt-3">Equipamiento</h2>
        <div style="display:flex;flex-wrap:wrap;gap:8px">
          @foreach (explode(',', $vehiculo->equipamiento) as $item)
            <span style="background:var(--gris-claro);border-radius:999px;padding:4px 12px;font-size:14px">{{ trim($item) }}</span>
          @endforeach
        </div>
      @endif
    </div>

    <aside style="height:fit-content">
      <div class="caja">
        <p style="font-weight:600">{{ $vehiculo->usuario->name }}</p>
        <p class="texto-mutado" style="font-size:14px">{{ $vehiculo->comuna }}</p>
        <a href="https://wa.me/{{ $numeroWa }}?text={{ $mensajeWa }}" target="_blank" rel="noopener"
           class="btn btn-block mt-2" style="background:#25D366;color:#fff">Contactar por WhatsApp</a>
        <a href="mailto:{{ config('autoruta.contacto_email') }}?subject={{ urlencode('Denuncia de publicación ' . $vehiculo->id) }}"
           class="btn btn-outline btn-block mt-1" style="color:#525252;border-color:#d4d4d4">Denunciar</a>
      </div>

      @if ($negocioDestacado)
        <a href="{{ $negocioDestacado->link_url }}" target="_blank" rel="noopener" class="caja mt-2" style="display:block;padding:0;overflow:hidden">
          <div style="aspect-ratio:16/9;background:var(--gris-claro)">
            @if ($negocioDestacado->tipo_medio === 'video')
              <video src="{{ $negocioDestacado->url() }}" style="width:100%;height:100%;object-fit:cover" autoplay muted loop playsinline></video>
            @else
              <img src="{{ $negocioDestacado->url() }}" style="width:100%;height:100%;object-fit:cover">
            @endif
          </div>
        </a>
      @endif
    </aside>
  </div>
</div>

<style>@media (min-width:900px){.ficha-grid{grid-template-columns:1fr 340px}}</style>
@endsection
