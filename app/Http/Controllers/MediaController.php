<?php

namespace App\Http\Controllers;

use App\Models\ArchivoGuardado;
use App\Support\Archivos;
use Illuminate\Http\Request;

// Sirve fotos y banners guardados en disco local o, si el disco se borró (deploy en
// Laravel Cloud), desde el respaldo en la base de datos.
// Responde por partes (Range / 206): Safari en iPhone no reproduce videos sin eso.
class MediaController extends Controller
{
    public function mostrar(Request $request, string $carpeta, string $archivo)
    {
        $ruta = $carpeta . '/' . $archivo;
        $cache = ['Cache-Control' => 'public, max-age=31536000, immutable'];

        if (Archivos::disco()->exists($ruta)) {
            // En disco local, BinaryFileResponse atiende los pedidos por partes por sí solo.
            if (Archivos::esLocal()) {
                return response()->file(Archivos::disco()->path($ruta), $cache);
            }

            return Archivos::disco()->response($ruta, null, $cache);
        }

        $guardado = ArchivoGuardado::where('ruta', $ruta)->first();
        abort_unless($guardado, 404);

        return $this->responderPorPartes($request, $guardado->contenido, $guardado->mime, $cache);
    }

    private function responderPorPartes(Request $request, string $contenido, string $mime, array $cache)
    {
        $tamano = strlen($contenido);
        $cabeceras = $cache + ['Content-Type' => $mime, 'Accept-Ranges' => 'bytes'];

        if (! preg_match('/^bytes=(\d*)-(\d*)$/', (string) $request->header('Range'), $partes) || ($partes[1] === '' && $partes[2] === '')) {
            return response($contenido, 200, $cabeceras + ['Content-Length' => $tamano]);
        }

        // "bytes=500-" (desde 500), "bytes=0-99" (rango) o "bytes=-200" (últimos 200).
        if ($partes[1] === '') {
            $inicio = max(0, $tamano - (int) $partes[2]);
            $fin = $tamano - 1;
        } else {
            $inicio = (int) $partes[1];
            $fin = $partes[2] === '' ? $tamano - 1 : min((int) $partes[2], $tamano - 1);
        }

        if ($inicio > $fin || $inicio >= $tamano) {
            return response('', 416, ['Content-Range' => 'bytes */' . $tamano]);
        }

        return response(substr($contenido, $inicio, $fin - $inicio + 1), 206, $cabeceras + [
            'Content-Range' => "bytes {$inicio}-{$fin}/{$tamano}",
            'Content-Length' => $fin - $inicio + 1,
        ]);
    }
}
