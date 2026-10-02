{{-- Pop-up administrable (Admin > Pop-up). Se muestra una vez por visita y espera a que se cierre el aviso de cookies. --}}
@if ($popup = \App\Models\Popup::visible())
<div class="popup-fondo" id="popup-inicio" hidden data-clave="popup-visto-{{ $popup->id }}-{{ $popup->updated_at?->timestamp }}">
  <div class="popup-caja" role="dialog" aria-modal="true" aria-label="Novedades de {{ config('autoruta.nombre_sitio') }}">
    <button type="button" class="popup-cerrar" aria-label="Cerrar">&times;</button>
    @if ($popup->link_url)<a href="{{ $popup->link_url }}" class="popup-medio">@else<div class="popup-medio">@endif
      @if ($popup->tipo_medio === 'video')
        <video src="{{ $popup->url() }}" muted loop playsinline preload="metadata"></video>
      @else
        <img src="{{ $popup->url() }}" alt="Novedades de {{ config('autoruta.nombre_sitio') }}">
      @endif
    @if ($popup->link_url)</a>@else</div>@endif
  </div>
</div>
<script>
  (function () {
    var fondo = document.getElementById('popup-inicio');
    var clave = fondo.dataset.clave;
    try { if (sessionStorage.getItem(clave)) return; } catch (e) {}

    function cerrar() {
      fondo.hidden = true;
      var video = fondo.querySelector('video'); if (video) video.pause();
      try { sessionStorage.setItem(clave, '1'); } catch (e) {}
    }
    function abrir() {
      var cookies = document.getElementById('aviso-cookies');
      if (cookies && !cookies.hidden) return setTimeout(abrir, 800);
      fondo.hidden = false;
      var video = fondo.querySelector('video'); if (video) video.play().catch(function () {});
    }

    fondo.querySelector('.popup-cerrar').addEventListener('click', cerrar);
    fondo.addEventListener('click', function (e) { if (e.target === fondo) cerrar(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !fondo.hidden) cerrar(); });
    var enlace = fondo.querySelector('a.popup-medio');
    if (enlace) enlace.addEventListener('click', function () { try { sessionStorage.setItem(clave, '1'); } catch (e) {} });
    setTimeout(abrir, 1200);
  })();
</script>
@endif
