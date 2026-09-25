<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('titulo', 'Admin') · Admin AutoRuta</title>
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
  @include('admin._nav')
  <div class="contenedor" style="max-width:900px;padding:32px 16px">
    @if (session('ok'))<p class="alerta-ok">{{ session('ok') }}</p>@endif
    @if (session('error'))<p class="alerta-error">{{ session('error') }}</p>@endif
    @if ($errors->any())
      <div class="alerta-error"><ul style="margin:0;padding-left:18px">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif
    @yield('contenido')
  </div>
</body>
</html>
