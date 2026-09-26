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
    </body>
</html>
