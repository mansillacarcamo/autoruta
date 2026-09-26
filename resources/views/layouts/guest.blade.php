<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('autoruta.nombre_sitio') }}</title>
        <link rel="icon" href="{{ asset('img/logo-autoruta.png') }}">
        <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">
  @include('partials.app-instalable')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div class="fondo-auto">
            <div class="tarjeta-login">
                <a href="{{ route('home') }}" style="display:block;text-align:center">
                    <img src="{{ asset('img/logo-autoruta.png') }}" alt="{{ config('autoruta.nombre_sitio') }}" style="height:64px;width:auto;margin:0 auto">
                </a>

                @if (request()->routeIs('register'))
                    <p style="text-align:center;color:#525252;font-weight:600;margin:14px 0 4px">Regístrate para poder publicar</p>
                @elseif (request()->routeIs('login'))
                    <p style="text-align:center;color:#525252;margin:14px 0 4px">Inicia sesión para seguir usando {{ config('autoruta.nombre_sitio') }}</p>
                @endif

                <div class="mt-2">
                    {{ $slot }}
                </div>
            </div>
        </div>

        <script>
            (function () {
                var ojoAbierto = '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
                var ojoCerrado = '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19M14.12 14.12a3 3 0 1 1-4.24-4.24"/><path d="M1 1l22 22"/></svg>';
                document.querySelectorAll('input[type="password"]').forEach(function (campo) {
                    var envoltura = document.createElement('div');
                    envoltura.className = 'campo-clave';
                    campo.parentNode.insertBefore(envoltura, campo);
                    envoltura.appendChild(campo);
                    var boton = document.createElement('button');
                    boton.type = 'button';
                    boton.className = 'ver-clave';
                    boton.setAttribute('aria-label', 'Mostrar contraseña');
                    boton.innerHTML = ojoAbierto;
                    boton.addEventListener('click', function () {
                        var oculta = campo.type === 'password';
                        campo.type = oculta ? 'text' : 'password';
                        boton.innerHTML = oculta ? ojoCerrado : ojoAbierto;
                        boton.setAttribute('aria-label', oculta ? 'Ocultar contraseña' : 'Mostrar contraseña');
                    });
                    envoltura.appendChild(boton);
                });
            })();
        </script>
    </body>
</html>
