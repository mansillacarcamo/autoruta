{{-- Bienvenida para visitantes sin cuenta: invita a publicar gratis. Se muestra una vez por navegador,
     espera al aviso de cookies y el pop-up administrable espera a que esta se cierre. --}}
@guest
<div class="popup-fondo" id="popup-bienvenida" hidden data-clave="bienvenida-vista-1">
  <div class="popup-caja" role="dialog" aria-modal="true" aria-labelledby="bienvenida-titulo">
    <button type="button" class="popup-cerrar" aria-label="Cerrar">&times;</button>
    <div class="bienvenida">
      <img src="{{ asset('img/logo-autoruta.png') }}" alt="{{ config('autoruta.nombre_sitio') }}" class="bienvenida-logo">
      <p class="bienvenida-saludo">Bienvenido a {{ config('autoruta.nombre_sitio') }}</p>
      <h2 id="bienvenida-titulo">Publica <strong>gratis</strong></h2>
      <p class="bienvenida-texto">Solo crea tu cuenta y vende.</p>
      <a href="{{ route('register') }}" class="btn btn-acento btn-brillo btn-block bienvenida-boton">Crear mi cuenta gratis</a>
      <p class="bienvenida-login">¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a></p>
    </div>
  </div>
</div>
<script>
  (function () {
    var fondo = document.getElementById('popup-bienvenida');
    var clave = fondo.dataset.clave;
    try { if (localStorage.getItem(clave)) return; } catch (e) {}
    fondo.dataset.pendiente = '1';

    function cerrar() {
      fondo.hidden = true;
      delete fondo.dataset.pendiente;
      try { localStorage.setItem(clave, '1'); } catch (e) {}
    }
    function abrir() {
      var cookies = document.getElementById('aviso-cookies');
      if (cookies && !cookies.hidden) return setTimeout(abrir, 800);
      fondo.hidden = false;
    }

    fondo.querySelector('.popup-cerrar').addEventListener('click', cerrar);
    fondo.addEventListener('click', function (e) { if (e.target === fondo) cerrar(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !fondo.hidden) cerrar(); });
    fondo.querySelectorAll('a').forEach(function (a) {
      a.addEventListener('click', function () { try { localStorage.setItem(clave, '1'); } catch (e) {} });
    });
    setTimeout(abrir, 1200);
  })();
</script>
@endguest
