{{-- Botones "WhatsApp" y "Sitio web" bajo un banner, con los datos del negocio (Admin > Negocios).
     No muestra nada si el negocio no tiene ninguno de los dos. --}}
@php($negocioBanner = $banner->anunciante)
@if ($negocioBanner && ($negocioBanner->urlWhatsapp() || $negocioBanner->urlSitioWeb()))
  <div class="banner-botones{{ ($fila ?? false) ? ' banner-botones-fila' : '' }}">
    @if ($negocioBanner->urlWhatsapp())
      <a href="{{ $banner->urlBoton('whatsapp') }}" target="_blank" rel="sponsored noopener" class="banner-boton banner-boton-wa">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm5.3 14.1c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .1-3.3-.8-2.8-1.1-4.5-3.9-4.7-4.1-.1-.2-1.1-1.5-1.1-2.9s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.2.1.4 0 .5l-.4.6-.4.4c-.1.1-.3.3-.1.6.1.3.7 1.1 1.4 1.8 1 .9 1.8 1.2 2.1 1.3.3.1.4.1.6-.1l.8-1c.2-.3.4-.2.6-.1l1.9.9c.3.1.5.2.5.3.1.2.1.7-.1 1.3z"/></svg>
        WhatsApp
      </a>
    @endif
    @if ($negocioBanner->urlSitioWeb())
      <a href="{{ $banner->urlBoton('web') }}" target="_blank" rel="sponsored noopener" class="banner-boton banner-boton-web">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.7 2.5 15.3 0 18M12 3c-2.5 2.7-2.5 15.3 0 18"/></svg>
        Sitio web
      </a>
    @endif
  </div>
@endif
