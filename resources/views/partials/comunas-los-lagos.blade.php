{{-- Enlaces a las páginas de cada comuna de Los Lagos (SEO local): primero las de más búsquedas.
     Oculto a pedido en todas las páginas: las comunas siguen enlazadas desde el sitemap.xml y el pie de página. --}}
@php($comunasLL = array_values(array_unique(array_merge(\App\Support\SeoLocal::COMUNAS_DESTACADAS, \App\Support\SeoLocal::comunas()))))
<section class="comunas-seo" hidden>
  <h2>Autos usados por comuna en la Región de Los Lagos</h2>
  <ul class="comunas-enlaces">
    <li><a href="{{ route('vehiculos.lugar', \App\Support\SeoLocal::SLUG_REGION) }}" @if (request()->route('lugar') === \App\Support\SeoLocal::SLUG_REGION) aria-current="page" @endif>Toda la región</a></li>
    @foreach ($comunasLL as $c)
      @php($slugC = \App\Support\SeoLocal::slug($c))
      <li><a href="{{ route('vehiculos.lugar', $slugC) }}" @if (request()->route('lugar') === $slugC) aria-current="page" @endif>Autos en {{ $c }}</a></li>
    @endforeach
  </ul>
</section>
