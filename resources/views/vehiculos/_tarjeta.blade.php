<a href="{{ route('vehiculos.show', $v) }}" class="tarjeta{{ !empty($nuevo) ? ' tarjeta-nueva' : '' }}{{ $v->premium ? ' es-premium' : '' }}">
  <div class="tarjeta-foto">
    @if (!empty($nuevo))<span class="etiqueta-nuevo">Nuevo</span>@endif
    @if ($v->premium)<span class="etiqueta-premium">★ Premium</span>@endif
    @if ($logoVendedor = $v->usuario?->logoVisibleUrl())
      <span class="tarjeta-logo"><img src="{{ $logoVendedor }}" alt="{{ $v->usuario->nombre_comercial ?: $v->usuario->name }}" loading="lazy" onerror="this.parentNode.remove()"></span>
    @endif
    <img src="{{ $v->primeraFotoUrl() }}" alt="{{ $v->marca }} {{ $v->modelo }}" loading="lazy" data-fotos="{{ json_encode($v->fotosUrls()) }}" data-sin-foto="{{ asset('img/vehiculo-placeholder.svg') }}" onerror="siguienteFoto(this)">
  </div>
  <div class="tarjeta-cuerpo">
    <p class="tarjeta-titulo">{{ $v->marca }} {{ $v->modelo }} {{ $v->anio }}</p>
    <p class="tarjeta-precio"><span class="precio-desde">Desde</span> {{ $v->precioFormateado() }}</p>
    @if ($v->pie)<p class="tarjeta-pie">Pie {{ $v->pieFormateado() }}</p>@endif
    <p class="tarjeta-meta">
      <span class="meta-chip meta-km"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M4 18a9 9 0 1 1 16 0"/><path d="m12 14 4-5"/><circle cx="12" cy="14" r="1.6" fill="currentColor"/></svg>{{ number_format($v->kilometraje, 0, ',', '.') }} km</span>
      <span class="meta-chip meta-lugar"><svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M12 2a7 7 0 0 0-7 7c0 5.2 7 13 7 13s7-7.8 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z"/></svg>{{ $v->comuna }}</span>
    </p>
  </div>
</a>
