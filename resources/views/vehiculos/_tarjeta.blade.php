<a href="{{ route('vehiculos.show', $v) }}" class="tarjeta{{ !empty($nuevo) ? ' tarjeta-nueva' : '' }}">
  <div class="tarjeta-foto">
    @if (!empty($nuevo))<span class="etiqueta-nuevo">Nuevo</span>@endif
    <img src="{{ $v->primeraFotoUrl() }}" alt="{{ $v->marca }} {{ $v->modelo }}" loading="lazy" data-fotos="{{ json_encode($v->fotosUrls()) }}" data-sin-foto="{{ asset('img/vehiculo-placeholder.svg') }}" onerror="siguienteFoto(this)">
  </div>
  <div class="tarjeta-cuerpo">
    <p class="tarjeta-titulo">{{ $v->marca }} {{ $v->modelo }} {{ $v->anio }}</p>
    <p class="tarjeta-precio">{{ $v->precioFormateado() }}</p>
    <p class="tarjeta-meta">{{ number_format($v->kilometraje, 0, ',', '.') }} km · {{ $v->comuna }}</p>
  </div>
</a>
