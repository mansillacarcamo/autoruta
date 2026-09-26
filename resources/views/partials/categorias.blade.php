@php
  $conteoPorTipo = \App\Models\Vehiculo::activos()->selectRaw('tipo, count(*) as total')->groupBy('tipo')->pluck('total', 'tipo');
  $tipoActual = request('tipo');
  $iconosTipo = [
    'auto' => '<path d="M3 15v-3l2.5-5h13L21 12v3"/><path d="M2 15h20v3H2z"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/><path d="M5.5 12h13"/>',
    'citycar' => '<path d="M4 16v-4l3-5h8l3 5v4"/><path d="M3 16h18v2H3z"/><circle cx="8" cy="18" r="2"/><circle cx="16" cy="18" r="2"/><path d="M7 12h10"/>',
    'camioneta' => '<path d="M2 16v-4h9V7h6l4 5v4"/><path d="M1 16h22v2H1z"/><circle cx="6" cy="18" r="2"/><circle cx="18" cy="18" r="2"/><path d="M13 12h7"/>',
    'suv' => '<path d="M3 16V8h14l4 4v4"/><path d="M2 16h20v2H2z"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/><path d="M3 12h17M11 8v4"/>',
    'moto' => '<circle cx="5" cy="17" r="3"/><circle cx="19" cy="17" r="3"/><path d="M5 17l4-6h5l5 6M14 11l-2-4h3M9 11l3 6"/>',
    'camion' => '<path d="M1 16V5h13v11"/><path d="M14 9h4l4 4v3h-8"/><path d="M1 16h21v2H1z"/><circle cx="5" cy="18" r="2"/><circle cx="18" cy="18" r="2"/>',
    'bus' => '<rect x="3" y="3" width="18" height="14" rx="2"/><path d="M3 10h18M8 3v7M16 3v7"/><circle cx="7" cy="19" r="2"/><circle cx="17" cy="19" r="2"/>',
    'maquinaria' => '<circle cx="7" cy="16" r="5"/><circle cx="19" cy="18" r="3"/><path d="M4 11V6h7l2 5h6v5M11 6v5"/><circle cx="7" cy="16" r="1.5"/>',
    'otro' => '<circle cx="12" cy="12" r="9"/><path d="M8 12h8M12 8v8"/>',
  ];
@endphp
<nav class="categorias" aria-label="Categorías de vehículos">
  <a href="{{ route('vehiculos.index', request()->except(['tipo', 'page'])) }}" class="categoria {{ ! $tipoActual && request()->routeIs('vehiculos.index') ? 'activa' : '' }}">
    <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
    <span>Todos</span>
    <small>{{ $conteoPorTipo->sum() }}</small>
  </a>
  @foreach (\App\Models\Vehiculo::ETIQUETA_TIPO as $valor => $etiqueta)
    <a href="{{ route('vehiculos.index', array_merge(request()->except(['tipo', 'page']), ['tipo' => $valor])) }}" class="categoria {{ $tipoActual === $valor ? 'activa' : '' }}">
      <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">{!! $iconosTipo[$valor] ?? $iconosTipo['otro'] !!}</svg>
      <span>{{ $etiqueta }}</span>
      <small>{{ $conteoPorTipo[$valor] ?? 0 }}</small>
    </a>
  @endforeach
</nav>
