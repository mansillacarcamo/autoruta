<div class="cookies-fondo" id="aviso-cookies" hidden>
  <div class="cookies-caja" role="dialog" aria-modal="true" aria-labelledby="cookies-titulo">
    <button type="button" class="cookies-cerrar" data-cookies="cerrar" aria-label="Cerrar">&times;</button>
    <h2 id="cookies-titulo">Gestionar el consentimiento de las cookies</h2>
    <p>Para ofrecer las mejores experiencias, utilizamos tecnologías como las cookies para almacenar y/o acceder a la información del dispositivo. El consentimiento de estas tecnologías nos permitirá procesar datos como el comportamiento de navegación o las identificaciones únicas en este sitio.</p>
    <div class="cookies-botones">
      <button type="button" class="btn btn-acento" data-cookies="aceptado">Aceptar</button>
      <button type="button" class="btn cookies-denegar" data-cookies="denegado">Denegar</button>
    </div>
    <a href="{{ route('privacidad') }}" class="cookies-politica">Política de cookies</a>
  </div>
</div>
<script>
  (function () {
    var CLAVE = 'autoruta_cookies';
    var aviso = document.getElementById('aviso-cookies');
    var guardado = null;
    try { guardado = localStorage.getItem(CLAVE); } catch (e) {}
    if (guardado) return;
    aviso.hidden = false;
    aviso.addEventListener('click', function (e) {
      var eleccion = e.target.getAttribute('data-cookies');
      if (!eleccion) return;
      if (eleccion !== 'cerrar') {
        try { localStorage.setItem(CLAVE, eleccion); } catch (err) {}
      }
      aviso.hidden = true;
    });
  })();
</script>
