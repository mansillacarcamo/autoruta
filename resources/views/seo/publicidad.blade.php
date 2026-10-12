@extends('layouts.app')
@section('titulo', 'Publicidad para el rubro automotriz en Puerto Montt, Osorno y Los Lagos')
@section('descripcion', 'Publicita tu automotora, taller, financiera, repuestos o lubricentro en Puerto Montt, Osorno y la Región de Los Lagos. Llega a quienes buscan comprar o vender autos. Agrégate gratis si eres del rubro automotriz.')
@php($precioPublicidad = (int) (\Illuminate\Support\Facades\DB::table('configuracion_sitio')->where('clave', 'precio_publicidad_negocio')->value('valor') ?? 15000))
@php($urlWhatsapp = 'https://wa.me/' . preg_replace('/\D/', '', config('autoruta.contacto_whatsapp')) . '?text=' . urlencode('Hola, soy del rubro automotriz y quiero aparecer en ' . config('autoruta.nombre_sitio')))
@php($preguntas = [
  ['¿Qué negocios pueden aparecer en ' . config('autoruta.nombre_sitio') . '?', 'Automotoras, talleres mecánicos, desabolladura y pintura, repuestos, lubricentros, neumáticos, financieras, seguros automotrices, lavados y cualquier negocio del rubro automotriz de la Región de Los Lagos.'],
  ['¿Es gratis?', 'Si eres del rubro automotriz te agregas gratis: las automotoras publican todo su stock sin costo. Los espacios de publicidad destacada (banners en el inicio, el listado y la ficha de cada vehículo) tienen un plan de $' . number_format($precioPublicidad, 0, ',', '.') . ' al mes.'],
  ['¿Quién ve mi publicidad?', 'Personas de Puerto Montt, Osorno, Puerto Varas, Chiloé y toda la región que están buscando comprar o vender un vehículo.'],
  ['¿Cómo me agrego?', 'Escríbenos por WhatsApp y te ayudamos a crear tu cuenta y tu publicidad.'],
])

@push('seo_json')
<script type="application/ld+json">{!! \App\Support\SeoLocal::jsonLd(['@type' => 'FAQPage', 'mainEntity' => array_map(fn ($p) => ['@type' => 'Question', 'name' => $p[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $p[1]]], $preguntas)]) !!}</script>
@endpush

@section('contenido')
<div class="contenedor seo-pagina">
  <h1>Publicidad para el rubro automotriz en la Región de Los Lagos</h1>
  <p class="texto-mutado" style="font-size:17px">
    Muestra tu negocio a las personas de Puerto Montt, Osorno, Puerto Varas y Chiloé que están comprando o vendiendo un vehículo.
    <strong style="color:#15803d">¡Agrégate gratis si eres del rubro automotriz!</strong>
  </p>
  <a href="{{ $urlWhatsapp }}" target="_blank" rel="noopener" class="btn mt-2 wa-pulso" style="background:#25D366;color:#fff">Quiero aparecer en {{ config('autoruta.nombre_sitio') }}</a>

  <h2>¿Para quién es?</h2>
  <ul class="seo-rubros">
    <li>Automotoras</li><li>Talleres mecánicos</li><li>Desabolladura y pintura</li><li>Repuestos</li>
    <li>Lubricentros</li><li>Neumáticos</li><li>Financieras</li><li>Seguros automotrices</li><li>Lavados de autos</li>
  </ul>

  <h2>Qué incluye</h2>
  <div class="caja mt-2">
    <h3 style="margin-top:0">Automotoras: publica tu stock gratis</h3>
    <p>Sube todos tus vehículos sin costo. Tus avisos aparecen intercalados con los de otras automotoras, así ninguna acapara el listado.</p>
  </div>
  <div class="caja mt-2" style="border:2px solid var(--acento)">
    <h3 style="margin-top:0">Publicidad destacada</h3>
    <p class="tarjeta-precio" style="font-size:22px">${{ number_format($precioPublicidad, 0, ',', '.') }} <span style="font-size:15px;font-weight:400;color:#737373">al mes</span></p>
    <p>Banner en el inicio y en los costados del sitio, y tarjeta de negocio destacado en el listado y la ficha de cada vehículo.</p>
  </div>

  <h2>Preguntas frecuentes</h2>
  <div class="preguntas">
    @foreach ($preguntas as $p)
      <details><summary>{{ $p[0] }}</summary><p>{{ $p[1] }}</p></details>
    @endforeach
  </div>
</div>
@endsection
