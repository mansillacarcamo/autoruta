<a href="{{ route('vehiculos.show', $v) }}" class="tarjeta">
  <div class="tarjeta-foto">
    <img src="{{ $v->primeraFotoUrl() }}" alt="{{ $v->marca }} {{ $v->modelo }}">
  </div>
  <div class="tarjeta-cuerpo">
    <p class="tarjeta-titulo">{{ $v->marca }} {{ $v->modelo }} {{ $v->anio }}</p>
    <p class="tarjeta-precio">{{ $v->precioFormateado() }}</p>
    <p class="tarjeta-meta">{{ number_format($v->kilometraje, 0, ',', '.') }} km · {{ $v->comuna }}</p>
  </div>
</a>
