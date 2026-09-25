<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@hasSection('titulo')@yield('titulo') · {{ config('autoruta.nombre_sitio') }}@else{{ config('autoruta.nombre_sitio') }}@endif</title>
  <meta name="description" content="Compra y vende autos, camionetas, SUV y motos usados en Chile. Publicar es 100% gratis.">
  <link rel="icon" href="{{ asset('img/logo-autoruta.png') }}">
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
  <header class="header">
    <div class="contenedor header-fila">
      <a href="{{ route('home') }}" class="logo"><img src="{{ asset('img/logo-autoruta.png') }}" alt="{{ config('autoruta.nombre_sitio') }}"></a>
      <nav class="nav">
        <a href="{{ route('vehiculos.index') }}">Ver vehículos</a>
        <a href="{{ route('como-funciona') }}">Cómo funciona</a>
      </nav>
      <div class="header-acciones">
        @auth
          <a href="{{ route('panel') }}" class="btn btn-acento">Mi panel</a>
        @else
          <a href="{{ route('login') }}" class="btn btn-outline">Iniciar sesión</a>
          <a href="{{ route('register') }}" class="btn btn-acento">Publicar</a>
        @endauth
      </div>
    </div>
  </header>

  <main>
    @if (session('ok'))
      <div class="contenedor mt-2"><p class="alerta-ok">{{ session('ok') }}</p></div>
    @endif
    @if (session('error'))
      <div class="contenedor mt-2"><p class="alerta-error">{{ session('error') }}</p></div>
    @endif

    @yield('contenido')
  </main>

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

    <div class="contenedor" style="border-top:1px solid var(--carbon-claro);padding:20px 0">
      <p style="font-size:12px;font-weight:600;color:#a3a3a3;margin:0 0 8px">Autos usados en venta por región</p>
      <div style="display:flex;flex-wrap:wrap;gap:6px 16px;font-size:12px">
        @foreach (array_keys(config('regiones')) as $region)
          <a href="{{ route('vehiculos.index', ['region' => $region]) }}">Autos en {{ $region }}</a>
        @endforeach
      </div>
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
