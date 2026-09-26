<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('titulo', 'Admin') · Admin AutoRuta</title>
  <link rel="icon" href="{{ asset('img/logo-autoruta.png') }}">
  <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">
  @include('partials.app-instalable')
</head>
<body class="admin-body">
  @php
    $menuAdmin = [
      ['admin.inicio', 'admin.inicio', 'Resumen', '<path d="M3 13h8V3H3zM13 21h8V11h-8zM3 21h8v-6H3zM13 3v6h8V3z"/>'],
      ['admin.negocios.index', 'admin.negocios.*', 'Publicidad', '<path d="M3 11l18-8v18L3 13z"/><path d="M7 13v5a2 2 0 0 0 4 0v-3"/>'],
      ['admin.portada.index', 'admin.portada.*', 'Portada', '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="M21 16l-5-5-9 9"/>'],
      ['admin.usuarios.index', 'admin.usuarios.*', 'Usuarios', '<circle cx="9" cy="8" r="4"/><path d="M1 21v-1a7 7 0 0 1 14 0v1"/><path d="M17 11a3 3 0 1 0 0-6M23 21v-1a6 6 0 0 0-4-5.6"/>'],
      ['admin.configuracion', 'admin.configuracion*', 'Configuración', '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>'],
    ];
  @endphp
  <aside class="admin-lateral">
    <a href="{{ route('admin.inicio') }}" class="admin-logo">
      <img src="{{ asset('img/logo-autoruta.png') }}" alt="AutoRuta">
      <span>Panel de administración</span>
    </a>
    <nav class="admin-menu">
      @foreach ($menuAdmin as [$ruta, $patron, $texto, $icono])
        <a href="{{ route($ruta) }}" class="{{ request()->routeIs($patron) ? 'activo' : '' }}">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $icono !!}</svg>
          {{ $texto }}
        </a>
      @endforeach
    </nav>
    <div class="admin-lateral-pie">
      <a href="{{ route('home') }}" target="_blank">Ver sitio ↗</a>
      <form method="post" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Cerrar sesión</button>
      </form>
    </div>
  </aside>

  <div class="admin-principal">
    <header class="admin-barra">
      <h1>@yield('titulo', 'Admin')</h1>
      <div class="admin-usuario">
        <span class="admin-avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
        <span>{{ auth()->user()->name }}</span>
      </div>
    </header>

    <main class="admin-contenido">
      @if (session('ok'))<p class="alerta-ok">{{ session('ok') }}</p>@endif
      @if (request('aviso') === 'archivos-pesados')<p class="alerta-error">El archivo pesa demasiado para el servidor. Usa uno más liviano e inténtalo de nuevo.</p>@endif
      @if (session('error'))<p class="alerta-error">{{ session('error') }}</p>@endif
      @if ($errors->any())
        <div class="alerta-error"><ul style="margin:0;padding-left:18px">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
      @endif
      @yield('contenido')
    </main>
  </div>
</body>
</html>
