<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@hasSection('titulo')@yield('titulo') · {{ config('autoruta.nombre_sitio') }}@else{{ config('autoruta.nombre_sitio') }}@endif</title>
  <meta name="description" content="Compra y vende autos, camionetas, SUV y motos usados en Chile. Publicar es 100% gratis.">
  <link rel="icon" href="{{ asset('img/logo-autoruta.png') }}">
  <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">
</head>
<body>
  <header class="header">
    <div class="contenedor header-fila">
      <a href="{{ route('home') }}" class="logo"><img src="{{ asset('img/logo-autoruta.png') }}" alt="{{ config('autoruta.nombre_sitio') }}"></a>
      <nav class="nav">
        <a href="{{ route('home') }}">Inicio</a>
        <a href="{{ route('vehiculos.index') }}">Ver vehículos</a>
        <a href="{{ route('como-funciona') }}">Cómo funciona</a>
      </nav>
      <div class="header-acciones">
        <a href="{{ config('autoruta.redes_sociales.facebook') }}" target="_blank" rel="noopener" aria-label="Facebook">
          <svg viewBox="0 0 24 24" width="22" height="22"><path fill="#1877F2" d="M24 12.07C24 5.7 18.63.5 12 .5S0 5.7 0 12.07c0 5.75 4.39 10.52 10.13 11.36v-8.04H7.08v-3.32h3.05V9.41c0-2.99 1.83-4.63 4.6-4.63 1.33 0 2.72.23 2.72.23v2.92h-1.53c-1.51 0-1.98.92-1.98 1.87v2.27h3.37l-.54 3.32h-2.83v8.04C19.61 22.6 24 17.82 24 12.07Z"/></svg>
        </a>
        <a href="{{ config('autoruta.redes_sociales.instagram') }}" target="_blank" rel="noopener" aria-label="Instagram">
          <svg viewBox="0 0 24 24" width="22" height="22">
            <defs><radialGradient id="ig" cx="30%" cy="107%" r="150%"><stop offset="0%" stop-color="#fdf497"/><stop offset="45%" stop-color="#fd5949"/><stop offset="60%" stop-color="#d6249f"/><stop offset="90%" stop-color="#285AEB"/></radialGradient></defs>
            <rect width="24" height="24" rx="6" fill="url(#ig)"/>
            <rect x="6" y="6" width="12" height="12" rx="3.5" fill="none" stroke="#fff" stroke-width="1.4"/>
            <circle cx="12" cy="12" r="3.2" fill="none" stroke="#fff" stroke-width="1.4"/>
            <circle cx="15.8" cy="8.2" r="0.9" fill="#fff"/>
          </svg>
        </a>
        <span style="width:1px;height:24px;background:var(--carbon-claro)"></span>
        @auth
          <a href="{{ route('panel') }}" class="btn btn-acento">Mi panel</a>
        @else
          <a href="{{ route('login') }}" class="btn btn-outline">Iniciar sesión</a>
          <a href="{{ route('register') }}" class="btn btn-acento btn-brillo">Publicar</a>
        @endauth
      </div>
    </div>
  </header>

  @include('partials.banner-ancho', ['posicion' => 'superior'])

  <div class="contenido-con-laterales">
    @include('partials.banner-lateral', ['lado' => 'izquierdo'])

    <main>
      @if (session('ok'))
        <div class="contenedor mt-2"><p class="alerta-ok">{{ session('ok') }}</p></div>
      @endif
      @if (session('error'))
        <div class="contenedor mt-2"><p class="alerta-error">{{ session('error') }}</p></div>
      @endif

      @yield('contenido')
    </main>

    @include('partials.banner-lateral', ['lado' => 'derecho'])
  </div>

  @include('partials.banner-ancho', ['posicion' => 'inferior'])

  <footer class="footer">
    <div class="contenedor footer-grid">
      <div>
        <a href="{{ route('home') }}"><img src="{{ asset('img/logo-autoruta.png') }}" alt="{{ config('autoruta.nombre_sitio') }}" style="height:56px;width:auto"></a>
        <p style="margin-top:8px;font-size:14px">Compra y venta de vehículos entre particulares en todo Chile.</p>
      </div>
      <div>
        <h3>Explorar</h3>
        <ul>
          <li><a href="{{ route('vehiculos.index') }}">Ver vehículos</a></li>
          <li><a href="{{ route('como-funciona') }}">Cómo funciona</a></li>
        </ul>
      </div>
      <div>
        <h3>Administración</h3>
        <ul><li><a href="{{ route('admin.negocios.index') }}">Panel admin</a></li></ul>
      </div>
      <div>
        <h3>Legal</h3>
        <ul>
          <li><a href="{{ route('terminos') }}">Términos y condiciones</a></li>
          <li><a href="{{ route('privacidad') }}">Política de privacidad</a></li>
        </ul>
      </div>
      <div>
        <h3>Contacto</h3>
        <ul>
          <li><a href="mailto:{{ config('autoruta.contacto_email') }}">{{ config('autoruta.contacto_email') }}</a></li>
          <li><a href="tel:{{ str_replace(' ', '', config('autoruta.contacto_telefono')) }}">{{ config('autoruta.contacto_telefono') }}</a></li>
        </ul>
      </div>
    </div>

    <div class="contador-visitas">
      <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
      <span><strong>{{ number_format(config('autoruta.visitas_inicio') + (int) \DB::table('visitas')->where('id', 1)->value('total'), 0, ',', '.') }}</strong> visitas</span>
    </div>
    <div class="footer-abajo">
      &copy; {{ date('Y') }} {{ config('autoruta.nombre_sitio') }}. Todos los derechos reservados.
      <br>Desarrollado por <a href="https://www.bynari.cl" target="_blank" rel="noopener" style="font-weight:600">www.bynari.cl</a>
    </div>
  </footer>

  <a class="wa-flotante" target="_blank" rel="noopener"
     href="https://wa.me/{{ preg_replace('/\D/', '', config('autoruta.contacto_whatsapp')) }}?text={{ urlencode('Hola, quiero hablar con un ejecutivo') }}">
    Hablar con un ejecutivo
  </a>
</body>
</html>
