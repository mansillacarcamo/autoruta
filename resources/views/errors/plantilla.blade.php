<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('titulo') · AutoRuta</title>
  <link rel="icon" href="/img/logo-autoruta.png">
  <style>
    body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; background: #04050d; color: #d4d4d4; font-family: -apple-system, "Segoe UI", Roboto, Arial, sans-serif; text-align: center; }
    .caja { max-width: 460px; }
    img { height: 64px; width: auto; margin-bottom: 28px; }
    .codigo { font-size: 72px; font-weight: 800; color: #d90718; line-height: 1; margin: 0; }
    h1 { color: #fff; font-size: 24px; margin: 12px 0 8px; }
    p { margin: 0 0 24px; line-height: 1.5; }
    .botones { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
    a { display: inline-block; padding: 12px 22px; border-radius: 8px; font-weight: 600; text-decoration: none; }
    .principal { background: #d90718; color: #fff; }
    .secundario { border: 1px solid #3a3b45; color: #fff; }
  </style>
</head>
<body>
  <div class="caja">
    <a href="/" style="padding:0"><img src="/img/logo-autoruta.png" alt="AutoRuta"></a>
    <p class="codigo">@yield('codigo')</p>
    <h1>@yield('titulo')</h1>
    <p>@yield('mensaje')</p>
    <div class="botones">
      <a href="/" class="principal">Ir al inicio</a>
      <a href="/vehiculos" class="secundario">Ver vehículos</a>
    </div>
  </div>
</body>
</html>
