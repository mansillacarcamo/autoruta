@php($v = $solicitud->vehiculo)
<!DOCTYPE html>
<html lang="es">
<body style="margin:0;padding:24px;background:#f4f5f7;font-family:Arial,Helvetica,sans-serif;color:#171717">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;margin:0 auto;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e5e5e5">
    <tr>
      <td style="background:#04050d;padding:20px 24px">
        <span style="color:#ffffff;font-size:20px;font-weight:bold">Auto</span><span style="color:#d90718;font-size:20px;font-weight:bold">Ruta</span>
      </td>
    </tr>
    <tr>
      <td style="padding:24px">
        <h1 style="font-size:20px;margin:0 0 6px">Nueva solicitud de visita</h1>
        <p style="margin:0 0 18px;color:#525252;font-size:15px">Alguien vio tu aviso en {{ config('autoruta.nombre_sitio') }} y quiere ir a ver tu vehículo.</p>
        <table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;font-size:15px">
          <tr><td style="padding:8px 0;color:#737373;width:110px">Vehículo</td><td style="padding:8px 0;font-weight:bold">{{ $v->marca }} {{ $v->modelo }} {{ $v->anio }}</td></tr>
          <tr><td style="padding:8px 0;color:#737373">Día</td><td style="padding:8px 0;font-weight:bold">{{ ucfirst($solicitud->fechaTexto()) }}</td></tr>
          <tr><td style="padding:8px 0;color:#737373">Hora</td><td style="padding:8px 0;font-weight:bold">{{ $solicitud->hora }} hrs</td></tr>
          <tr><td style="padding:8px 0;color:#737373">Nombre</td><td style="padding:8px 0">{{ $solicitud->nombre }}</td></tr>
          <tr><td style="padding:8px 0;color:#737373">WhatsApp</td><td style="padding:8px 0">+{{ $solicitud->telefono }}</td></tr>
        </table>
        <p style="margin:22px 0 0">
          <a href="{{ $solicitud->urlResponderWhatsapp() }}" style="display:inline-block;background:#25D366;color:#ffffff;text-decoration:none;padding:11px 20px;border-radius:8px;font-weight:bold">Responder por WhatsApp</a>
          <a href="{{ route('panel') }}" style="display:inline-block;margin-left:8px;background:#d90718;color:#ffffff;text-decoration:none;padding:11px 20px;border-radius:8px;font-weight:bold">Ver en mi panel</a>
        </p>
      </td>
    </tr>
  </table>
</body>
</html>
