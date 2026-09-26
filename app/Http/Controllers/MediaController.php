<?php

namespace App\Http\Controllers;

use App\Models\ArchivoGuardado;
use App\Support\Archivos;

// Sirve fotos y banners guardados en disco local o, si el disco se borró (deploy en
// Laravel Cloud), desde el respaldo en la base de datos.
class MediaController extends Controller
{
    public function mostrar(string $carpeta, string $archivo)
    {
        $ruta = $carpeta . '/' . $archivo;
        $cache = ['Cache-Control' => 'public, max-age=31536000, immutable'];

        if (Archivos::disco()->exists($ruta)) {
            return Archivos::disco()->response($ruta, null, $cache);
        }

        $guardado = ArchivoGuardado::where('ruta', $ruta)->first();
        abort_unless($guardado, 404);

        return response($guardado->contenido, 200, $cache + [
            'Content-Type' => $guardado->mime,
            'Content-Length' => strlen($guardado->contenido),
        ]);
    }
}
