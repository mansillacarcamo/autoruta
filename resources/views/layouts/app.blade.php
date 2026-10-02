<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@hasSection('titulo')@yield('titulo') · {{ config('autoruta.nombre_sitio') }}@else{{ config('autoruta.nombre_sitio') }}@endif</title>
  <meta name="description" content="Compra y vende autos, camionetas, SUV y motos usados en Chile. Publicar es 100% gratis.">
  <link rel="icon" href="{{ asset('img/logo-autoruta.png') }}">
  <script>
    // Si la foto de una tarjeta no carga, prueba con la siguiente foto del aviso; si ninguna carga, muestra "Sin foto".
    function siguienteFoto(img) {
      var fotos = [];
      try { fotos = JSON.parse(img.dataset.fotos || '[]'); } catch (e) {}
      var i = Number(img.dataset.intento || 0) + 1;
      if (i < fotos.length) { img.dataset.intento = i; img.src = fotos[i]; return; }
      img.onerror = null;
      img.src = img.dataset.sinFoto;
    }
  </script>
  <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">
  @include('partials.app-instalable')
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
        @if (config('autoruta.redes_sociales.facebook'))
        <a class="red-social" href="{{ config('autoruta.redes_sociales.facebook') }}" target="_blank" rel="noopener" aria-label="Facebook">
          <svg viewBox="0 0 24 24" width="22" height="22"><path fill="#1877F2" d="M24 12.07C24 5.7 18.63.5 12 .5S0 5.7 0 12.07c0 5.75 4.39 10.52 10.13 11.36v-8.04H7.08v-3.32h3.05V9.41c0-2.99 1.83-4.63 4.6-4.63 1.33 0 2.72.23 2.72.23v2.92h-1.53c-1.51 0-1.98.92-1.98 1.87v2.27h3.37l-.54 3.32h-2.83v8.04C19.61 22.6 24 17.82 24 12.07Z"/></svg>
        </a>
        @endif
        @if (config('autoruta.redes_sociales.tiktok'))
        <a class="red-social" href="{{ config('autoruta.redes_sociales.tiktok') }}" target="_blank" rel="noopener" aria-label="TikTok">
          <svg viewBox="0 0 24 24" width="22" height="22"><rect width="24" height="24" rx="6" fill="#000"/><path fill="#fff" d="M16.6 5.8a3.9 3.9 0 0 1-.9-2.3h-2.9v11.3a2.4 2.4 0 1 1-2.4-2.4c.2 0 .5 0 .7.1V9.6a5.4 5.4 0 1 0 4.7 5.3V9.3a6.7 6.7 0 0 0 3.9 1.2V7.6a3.9 3.9 0 0 1-3.1-1.8z"/></svg>
        </a>
        @endif
        @if (config('autoruta.redes_sociales.youtube'))
        <a class="red-social" href="{{ config('autoruta.redes_sociales.youtube') }}" target="_blank" rel="noopener" aria-label="YouTube">
          <svg viewBox="0 0 24 24" width="22" height="22"><rect y="3" width="24" height="18" rx="5" fill="#FF0000"/><path fill="#fff" d="M10 8.5v7l6-3.5z"/></svg>
        </a>
        @endif
        <span class="separador"></span>
        @auth
          <a href="{{ route('panel') }}" class="btn btn-acento">Mi panel</a>
        @else
          <a href="{{ route('register') }}" class="btn btn-outline">Regístrate</a>
          <a href="{{ route('login') }}" class="btn btn-outline">Iniciar sesión</a>
          <a href="{{ route('register') }}" class="btn btn-acento btn-brillo btn-publicar">Publicar</a>
        @endauth
        @if (config('autoruta.redes_sociales.instagram'))
        <a class="red-social red-social-grande" href="{{ config('autoruta.redes_sociales.instagram') }}" target="_blank" rel="noopener" aria-label="Instagram">
          <svg viewBox="0 0 24 24" width="34" height="34">
            <defs><radialGradient id="ig" cx="30%" cy="107%" r="150%"><stop offset="0%" stop-color="#fdf497"/><stop offset="45%" stop-color="#fd5949"/><stop offset="60%" stop-color="#d6249f"/><stop offset="90%" stop-color="#285AEB"/></radialGradient></defs>
            <rect width="24" height="24" rx="6" fill="url(#ig)"/>
            <rect x="6" y="6" width="12" height="12" rx="3.5" fill="none" stroke="#fff" stroke-width="1.4"/>
            <circle cx="12" cy="12" r="3.2" fill="none" stroke="#fff" stroke-width="1.4"/>
            <circle cx="15.8" cy="8.2" r="0.9" fill="#fff"/>
          </svg>
        </a>
        @endif
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
      @if (request('aviso') === 'archivos-pesados')
        <div class="contenedor mt-2"><p class="alerta-error">Las fotos pesan demasiado para enviarlas juntas. Sube menos fotos o fotos más livianas e inténtalo de nuevo.</p></div>
      @endif
      @if (session('error'))
        <div class="contenedor mt-2"><p class="alerta-error">{{ session('error') }}</p></div>
      @endif

      @yield('contenido')

      @unless (request()->routeIs('home'))
        @include('partials.banners-laterales-movil', ['fila' => 1])
      @endunless
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
        <ul><li><a href="{{ route('admin.inicio') }}">Panel admin</a></li></ul>
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
          <li>{{ config('autoruta.contacto_ubicacion') }}</li>
        </ul>
      </div>
    </div>

    <div class="app-pronto">
      <span class="app-pronto-titulo">Muy pronto nuestra app para iOS y Android</span>
      <div class="app-pronto-tiendas">
        <span class="tienda" aria-label="Próximamente en App Store">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="#fff"><path d="M16.4 12.6c0-2.5 2-3.7 2.1-3.8-1.2-1.7-3-1.9-3.6-2-1.5-.2-3 .9-3.8.9-.8 0-2-.9-3.3-.9-1.7 0-3.3 1-4.1 2.5-1.8 3.1-.5 7.6 1.2 10.1.8 1.2 1.8 2.6 3.1 2.5 1.3-.1 1.7-.8 3.2-.8s1.9.8 3.2.8c1.3 0 2.2-1.2 3-2.4.9-1.4 1.3-2.7 1.3-2.8-.1 0-2.3-.9-2.3-4.1zM14 5.2c.7-.8 1.1-2 1-3.2-1 0-2.2.7-2.9 1.5-.6.7-1.2 1.9-1 3.1 1.1.1 2.2-.6 2.9-1.4z"/></svg>
          <span><small>Próximamente en</small>App Store</span>
        </span>
        <span class="tienda" aria-label="Próximamente en Google Play">
          <svg viewBox="0 0 24 24" width="22" height="22"><path fill="#00d7fe" d="M3.6 1.8 13.3 12l-9.7 10.2c-.4-.2-.6-.6-.6-1.1V2.9c0-.5.2-.9.6-1.1z"/><path fill="#ffce00" d="m16.6 15.3-3.3-3.3 3.3-3.3 3.9 2.2c1.1.6 1.1 1.7 0 2.3z"/><path fill="#ff3a44" d="M16.6 15.3 13.3 12l-9.7 10.2c.4.2.9.2 1.4-.1z"/><path fill="#00f076" d="M16.6 8.7 5 1.9c-.5-.3-1-.3-1.4-.1L13.3 12z"/></svg>
          <span><small>Próximamente en</small>Google Play</span>
        </span>
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
     href="https://wa.me/{{ preg_replace('/\D/', '', config('autoruta.asesor_whatsapp')) }}?text={{ urlencode('Hola, quiero hablar con un asesor web') }}">
    Asesor web
  </a>

  @include('partials.aviso-cookies')
</body>
</html>
