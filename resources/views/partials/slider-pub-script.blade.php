@once
<script>
  // Sliders de publicidad ([data-slider-pub]): avanzan solos cada data-intervalo ms (6 s por defecto);
  // flechas, puntos y deslizar con el dedo. Se pausan con el mouse encima o la pestaña oculta.
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-slider-pub]').forEach(function (slider) {
      var slides = slider.querySelectorAll('.slider-pub-slide');
      if (slides.length < 2) return;
      var pista = slider.querySelector('.slider-pub-pista'), puntos = slider.querySelectorAll('.slider-pub-puntos button');
      var actual = 0, pausado = false, inicioX = null;

      function ir(n) {
        actual = (n + slides.length) % slides.length;
        pista.style.transform = 'translateX(' + (-actual * 100) + '%)';
        slides.forEach(function (s, i) { s.inert = i !== actual; });
        puntos.forEach(function (p, i) { p.classList.toggle('activo', i === actual); });
      }

      var anterior = slider.querySelector('.anterior'), siguiente = slider.querySelector('.siguiente');
      if (anterior) anterior.addEventListener('click', function () { ir(actual - 1); });
      if (siguiente) siguiente.addEventListener('click', function () { ir(actual + 1); });
      puntos.forEach(function (p, i) { p.addEventListener('click', function () { ir(i); }); });
      slider.addEventListener('mouseenter', function () { pausado = true; });
      slider.addEventListener('mouseleave', function () { pausado = false; });
      slider.addEventListener('touchstart', function (e) { inicioX = e.touches[0].clientX; }, { passive: true });
      slider.addEventListener('touchend', function (e) {
        if (inicioX === null) return;
        var dx = e.changedTouches[0].clientX - inicioX;
        if (Math.abs(dx) > 40) ir(actual + (dx < 0 ? 1 : -1));
        inicioX = null;
      });
      setInterval(function () { if (!pausado && !document.hidden) ir(actual + 1); }, Number(slider.dataset.intervalo) || 6000);
    });
  });
</script>
@endonce
