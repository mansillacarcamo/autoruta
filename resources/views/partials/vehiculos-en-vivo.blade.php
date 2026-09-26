{{-- Consulta cada 20 s si hay vehículos nuevos. modo "insertar": los agrega arriba de la grilla;
     modo "aviso": muestra un aviso para recargar (el listado tiene orden y filtros propios). --}}
@php
  $urlNuevos = route('vehiculos.nuevos', request()->only(['q', 'tipo', 'region', 'comuna', 'precioMin', 'precioMax']));
  $maxTarjetas = $maxTarjetas ?? 8;
@endphp
<div class="aviso-nuevos" id="avisoNuevos" hidden>
  <span id="avisoNuevosTexto"></span>
  <a href="{{ request()->fullUrl() }}" class="btn btn-acento">Ver</a>
</div>
<script>
  (function () {
    var MODO = @json($modo);
    var MAX_TARJETAS = @json($maxTarjetas);
    var grilla = document.getElementById(@json($grilla));
    var desde = @json($desde);
    var url = @json($urlNuevos);
    var acumulados = 0;

    function revisar() {
      if (document.hidden) return;
      var separador = url.indexOf('?') === -1 ? '?' : '&';
      fetch(url + separador + 'desde=' + desde, { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (d) {
          if (!d || !d.cantidad) return;
          desde = d.ultimo_id;
          if (MODO === 'insertar' && grilla) {
            var vacio = document.getElementById('sinVehiculos');
            if (vacio) vacio.remove();
            grilla.insertAdjacentHTML('afterbegin', d.html);
            while (grilla.children.length > MAX_TARJETAS) grilla.lastElementChild.remove();
          } else {
            acumulados += d.cantidad;
            document.getElementById('avisoNuevosTexto').textContent =
              acumulados === 1 ? 'Hay 1 vehículo nuevo' : 'Hay ' + acumulados + ' vehículos nuevos';
            document.getElementById('avisoNuevos').hidden = false;
          }
        })
        .catch(function () {});
    }

    setInterval(revisar, 20000);
    document.addEventListener('visibilitychange', function () { if (!document.hidden) revisar(); });
  })();
</script>
