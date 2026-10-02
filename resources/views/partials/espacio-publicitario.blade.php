@php([$etiquetaPosicion, $medida] = \App\Models\Anunciante::POSICIONES[$posicion])
<a class="espacio-publicitario" style="{{ $estilo }}" target="_blank" rel="noopener"
   href="https://wa.me/{{ preg_replace('/\D/', '', config('autoruta.contacto_whatsapp')) }}?text={{ urlencode("Hola, quiero publicitar en AutoRuta ({$etiquetaPosicion})") }}">
  @include('partials.auto-en-movimiento')
  <span class="ep-disponible">Disponible</span>
  <strong>Espacio disponible</strong>
  <span class="ep-posicion">{{ $etiquetaPosicion }}</span>
  <span class="ep-medida">{{ $medida }}</span>
  <span>Contáctanos</span>
</a>
