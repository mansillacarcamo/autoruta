@php([$etiquetaPosicion, $medida] = \App\Models\Anunciante::POSICIONES[$posicion])
<a class="espacio-publicitario" style="{{ $estilo }}" target="_blank" rel="noopener"
   href="https://wa.me/{{ preg_replace('/\D/', '', config('autoruta.contacto_whatsapp')) }}?text={{ urlencode("Hola, quiero publicitar en AutoRuta ({$etiquetaPosicion})") }}">
  <span class="ep-disponible">Disponible</span>
  <strong>Espacio publicitario</strong>
  <span class="ep-posicion">{{ $etiquetaPosicion }}</span>
  <span class="ep-medida">{{ $medida }}</span>
  <span>Contáctanos</span>
</a>
