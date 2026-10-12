@extends('layouts.app')
@section('titulo', 'Vender mi auto gratis en Puerto Montt, Osorno y Los Lagos')
@section('descripcion', 'Vende tu auto, camioneta, SUV o moto usada en Puerto Montt, Osorno, Puerto Varas, Chiloé y toda la Región de Los Lagos. Publica gratis, con fotos y sin comisión por venta.')

@php($urlPublicar = auth()->check() ? route('panel.publicar') : route('register'))
@php($preguntas = [
  ['¿Cuánto cuesta publicar mi auto?', 'Nada. Publicar en ' . config('autoruta.nombre_sitio') . ' es 100% gratis y no cobramos comisión cuando vendes.'],
  ['¿En qué comunas puedo vender?', 'En todas las comunas de la Región de Los Lagos: Puerto Montt, Osorno, Puerto Varas, Castro, Ancud, Frutillar, Llanquihue, Calbuco, Quellón y más. También puedes publicar desde el resto de Chile.'],
  ['¿Cuánto tiempo queda publicado mi vehículo?', 'Tu aviso queda activo ' . config('autoruta.duracion_publicacion_dias') . ' días y lo puedes renovar gratis desde tu panel.'],
  ['¿Cómo me contactan los compradores?', 'Directo a tu WhatsApp desde la ficha del vehículo, sin intermediarios.'],
  ['¿Puedo publicar si tengo una automotora?', 'Sí. Las automotoras de la región publican su stock gratis y sus avisos se muestran intercalados con los de otras automotoras y particulares.'],
])

@push('seo_json')
<script type="application/ld+json">{!! \App\Support\SeoLocal::jsonLd(['@type' => 'FAQPage', 'mainEntity' => array_map(fn ($p) => ['@type' => 'Question', 'name' => $p[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $p[1]]], $preguntas)]) !!}</script>
@endpush

@section('contenido')
<div class="contenedor seo-pagina">
  <h1>Vende tu auto gratis en Puerto Montt, Osorno y toda la Región de Los Lagos</h1>
  <p class="texto-mutado" style="font-size:17px">
    En {{ config('autoruta.nombre_sitio') }} publicas tu auto, camioneta, SUV, moto o camión usado sin pagar nada y te contactan directo por WhatsApp
    compradores de Puerto Montt, Osorno, Puerto Varas, Chiloé y toda la zona sur.
  </p>
  <a href="{{ $urlPublicar }}" class="btn btn-acento btn-brillo mt-2">Publicar mi vehículo gratis</a>

  <h2>Cómo vender tu vehículo en 3 pasos</h2>
  <ol class="seo-pasos">
    <li><strong>Crea tu cuenta gratis</strong> con tu nombre, comuna y WhatsApp.</li>
    <li><strong>Sube hasta {{ config('autoruta.max_fotos_vehiculo') }} fotos</strong> y completa la ficha: marca, modelo, año, kilometraje y precio.</li>
    <li><strong>Recibe mensajes por WhatsApp</strong> de compradores interesados de tu comuna y de toda la región.</li>
  </ol>

  <h2>Por qué vender en {{ config('autoruta.nombre_sitio') }}</h2>
  <ul>
    <li>Publicar es 100% gratis y sin comisión por venta.</li>
    <li>Un sitio enfocado en la Región de Los Lagos: tu aviso lo ven compradores de tu zona.</li>
    <li>Tu aviso dura {{ config('autoruta.duracion_publicacion_dias') }} días y lo renuevas gratis.</li>
    <li>Particulares y automotoras publican en igualdad de condiciones.</li>
  </ul>

  <h2>Preguntas frecuentes</h2>
  <div class="preguntas">
    @foreach ($preguntas as $p)
      <details><summary>{{ $p[0] }}</summary><p>{{ $p[1] }}</p></details>
    @endforeach
  </div>

  @include('partials.comunas-los-lagos')
</div>
@endsection
