@extends('layouts.app')
@section('titulo', 'Cómo funciona')
@php($precioPublicidad = (int) (\Illuminate\Support\Facades\DB::table('configuracion_sitio')->where('clave', 'precio_publicidad_negocio')->value('valor') ?? 15000))

@section('contenido')
<div class="contenedor" style="max-width:700px;padding:48px 16px">
  <h1>Cómo funciona</h1>

  <div class="caja mt-3">
    <h2>Publicar tu vehículo es 100% gratis</h2>
    <p class="tarjeta-precio" style="font-size:24px">$0</p>
    <ul>
      <li>Hasta {{ config('autoruta.max_fotos_vehiculo') }} fotos</li>
      <li>Ficha técnica completa</li>
      <li>Publicación activa por {{ config('autoruta.duracion_publicacion_dias') }} días</li>
      <li>Sin comisión por venta</li>
    </ul>
  </div>

  <div class="caja mt-2" style="border:2px solid var(--acento)">
    <h2>Plan Publicidad para negocios (financieras, talleres, otros rubros)</h2>
    <p class="tarjeta-precio" style="font-size:24px">${{ number_format($precioPublicidad, 0, ',', '.') }} <span style="font-size:14px;font-weight:400;color:#737373">al mes</span></p>
    <p>Banner en el inicio y tarjeta de negocio destacado en el listado y ficha de vehículos, por 30 días.</p>
    <p style="font-weight:600">Es el único producto pagado del sitio: publicar vehículos siempre es gratis.</p>
    <a href="https://wa.me/{{ preg_replace('/\D/', '', config('autoruta.contacto_whatsapp')) }}?text={{ urlencode('Hola, quiero anunciar mi negocio en ' . config('autoruta.nombre_sitio')) }}"
       target="_blank" rel="noopener" class="btn btn-block mt-2" style="background:#25D366;color:#fff">Contactar por WhatsApp</a>
  </div>
</div>
@endsection
