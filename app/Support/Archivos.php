<?php

namespace App\Support;

use App\Models\ArchivoGuardado;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// Fotos de vehículos, banners y portada. Con Object Storage (bucket de Laravel Cloud como
// disco por defecto) se usa el bucket. Sin él, se guardan en el disco local y además en la
// base de datos, porque el disco de Laravel Cloud es temporal y se borra en cada deploy.
class Archivos
{
    public static function disco(): Filesystem
    {
        return Storage::disk(config('autoruta.disco_archivos'));
    }

    public static function esLocal(): bool
    {
        return config('filesystems.disks.' . config('autoruta.disco_archivos') . '.driver') === 'local';
    }

    public static function guardar(UploadedFile $archivo, string $carpeta, string $nombre): string
    {
        $contenido = self::esLocal() ? file_get_contents($archivo->getRealPath()) : null;
        $archivo->storeAs($carpeta, $nombre, config('autoruta.disco_archivos'));

        if ($contenido !== null) {
            ArchivoGuardado::updateOrCreate(
                ['ruta' => $carpeta . '/' . $nombre],
                ['mime' => $archivo->getMimeType() ?: 'application/octet-stream', 'contenido' => $contenido]
            );
        }

        return $nombre;
    }

    public static function url(string $ruta): string
    {
        // Disco local: se sirve por /media (no depende de storage:link ni de APP_URL).
        if (self::esLocal()) {
            [$carpeta, $archivo] = explode('/', $ruta, 2);

            return route('media', ['carpeta' => $carpeta, 'archivo' => $archivo]);
        }

        return self::disco()->url($ruta);
    }

    public static function borrar(string $ruta): void
    {
        self::disco()->delete($ruta);
        ArchivoGuardado::where('ruta', $ruta)->delete();
    }
}
