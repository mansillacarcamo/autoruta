{{-- Formularios del admin con <input type="file" data-por-trozos>: los archivos de más de ~1 MB se envían en
     trozos antes de guardar (ver App\Support\SubidaPorTrozos). --}}
@once
<script>
  // Archivos grandes (videos) se envían en trozos de 1 MB antes de guardar el formulario, porque el
  // servidor rechaza archivos de más de unos pocos MB en una sola petición.
  (function () {
    var TROZO = 1024 * 1024, URL_TROZO = @json(route('admin.banners.trozo'));
    function uuid() {
      if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
      return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
        var r = Math.random() * 16 | 0; return (c === 'x' ? r : (r & 3 | 8)).toString(16);
      });
    }
    function oculto(form, nombre, valor) {
      var el = document.createElement('input');
      el.type = 'hidden'; el.name = nombre; el.value = valor;
      form.appendChild(el);
    }

    document.querySelectorAll('form').forEach(function (form) {
      var campos = form.querySelectorAll('input[type=file][data-por-trozos]');
      if (!campos.length) return;

      form.addEventListener('submit', async function (e) {
        var grandes = Array.prototype.filter.call(campos, function (c) { return c.files.length && c.files[0].size > 900 * 1024; });
        if (!grandes.length) return;
        e.preventDefault();

        var boton = form.querySelector('button[type=submit]'), textoBoton = boton.textContent;
        boton.disabled = true;
        var token = form.querySelector('input[name=_token]').value;
        try {
          for (var c = 0; c < grandes.length; c++) {
            var campo = grandes[c], archivo = campo.files[0], subida = uuid();
            var total = Math.ceil(archivo.size / TROZO);
            if (total > 40) throw new Error('El archivo pesa más de 40 MB. Usa uno más liviano.');
            for (var i = 0; i < total; i++) {
              boton.textContent = 'Subiendo video… ' + Math.round(i / total * 100) + '%';
              var datos = new FormData();
              datos.append('_token', token);
              datos.append('subida', subida);
              datos.append('indice', i);
              datos.append('trozo', archivo.slice(i * TROZO, (i + 1) * TROZO), 'trozo');
              var resp = await fetch(URL_TROZO, { method: 'POST', body: datos, headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
              if (!resp.ok) throw new Error('No se pudo subir el video (error ' + resp.status + '). Inténtalo de nuevo.');
            }
            oculto(form, campo.name + '_subida', subida);
            oculto(form, campo.name + '_total', total);
            oculto(form, campo.name + '_nombre', archivo.name);
            campo.removeAttribute('name'); // el archivo ya está en el servidor; no se vuelve a enviar
          }
          boton.textContent = 'Guardando…';
          form.submit();
        } catch (error) {
          alert(error.message);
          boton.disabled = false;
          boton.textContent = textoBoton;
        }
      });
    });
  })();
</script>
@endonce
