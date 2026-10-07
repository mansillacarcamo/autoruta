<?php

namespace App\Support;

use Illuminate\Http\Request;

// Archivos grandes (videos) subidos en trozos de 1 MB desde el admin (ruta admin.banners.trozo): el servidor
// rechaza archivos de más de unos pocos MB en una sola petición. Los trozos se guardan en la base de datos
// (sirve aunque haya varias instancias) y al enviar el formulario se unen y quedan en la petición como si
// el archivo se hubiera subido normal, para validarlo y guardarlo igual.
class SubidaPorTrozos
{
    public const MAX_TROZOS = 40;

    // Si el formulario trae un archivo subido por trozos ({campo}_subida, {campo}_total, {campo}_nombre),
    // lo une y lo deja en la petición como si se hubiera subido normal, para validarlo y guardarlo igual.
    public static function unir(Request $request, array $campos): void
    {
        foreach ($campos as $campo) {
            $subida = (string) $request->input($campo . '_subida');
            $total = (int) $request->input($campo . '_total');
            if (! \Illuminate\Support\Str::isUuid($subida) || $total < 1 || $total > self::MAX_TROZOS) {
                continue;
            }

            $rutas = array_map(fn ($i) => 'trozos/' . $subida . '/' . $i, range(0, $total - 1));
            $trozos = \App\Models\ArchivoGuardado::whereIn('ruta', $rutas)->get()->keyBy('ruta');
            if ($trozos->count() !== $total) {
                throw \Illuminate\Validation\ValidationException::withMessages([$campo => 'El video no terminó de subirse. Inténtalo de nuevo.']);
            }

            $temporal = tempnam(sys_get_temp_dir(), 'trozos');
            $destino = fopen($temporal, 'wb');
            foreach ($rutas as $ruta) {
                fwrite($destino, $trozos[$ruta]->contenido);
            }
            fclose($destino);
            \App\Models\ArchivoGuardado::whereIn('ruta', $rutas)->delete();
            // Limpia trozos de subidas abandonadas.
            \App\Models\ArchivoGuardado::where('ruta', 'like', 'trozos/%')->where('created_at', '<', now()->subDay())->delete();

            $nombre = basename((string) $request->input($campo . '_nombre')) ?: 'video.mp4';
            $request->files->set($campo, new \Illuminate\Http\UploadedFile($temporal, $nombre, null, UPLOAD_ERR_OK, true));
        }
    }
}
