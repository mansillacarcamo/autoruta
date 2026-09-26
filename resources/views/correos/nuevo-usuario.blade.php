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
        <h1 style="font-size:20px;margin:0 0 6px">Nuevo usuario registrado</h1>
        <p style="margin:0 0 18px;color:#525252;font-size:14px">{{ $usuario->created_at?->timezone('America/Santiago')->format('d-m-Y H:i') }} hrs</p>
        <table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;font-size:15px">
          <tr><td style="padding:8px 0;color:#737373;width:110px">Nombre</td><td style="padding:8px 0;font-weight:bold">{{ $usuario->name }}</td></tr>
          <tr><td style="padding:8px 0;color:#737373">Correo</td><td style="padding:8px 0">{{ $usuario->email }}</td></tr>
          <tr><td style="padding:8px 0;color:#737373">Teléfono</td><td style="padding:8px 0">{{ $usuario->telefono_whatsapp ?: '—' }}</td></tr>
          <tr><td style="padding:8px 0;color:#737373">Ciudad</td><td style="padding:8px 0">{{ $usuario->comuna ?: '—' }}</td></tr>
        </table>
        <p style="margin:22px 0 0">
          <a href="{{ route('admin.usuarios.index') }}" style="display:inline-block;background:#d90718;color:#ffffff;text-decoration:none;padding:11px 20px;border-radius:8px;font-weight:bold">Ver usuarios en el panel</a>
          @if ($usuario->telefono_whatsapp)
            <a href="https://wa.me/{{ preg_replace('/\D/', '', $usuario->telefono_whatsapp) }}" style="display:inline-block;margin-left:8px;background:#25D366;color:#ffffff;text-decoration:none;padding:11px 20px;border-radius:8px;font-weight:bold">WhatsApp</a>
          @endif
        </p>
      </td>
    </tr>
  </table>
</body>
</html>
