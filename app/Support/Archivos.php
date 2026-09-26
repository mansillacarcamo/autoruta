<?php

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// Fotos de vehículos, banners y portada. En Laravel Cloud el disco del servidor es
// temporal, así que se usa el bucket de Object Storage cuando está conectado.
class Archivos
{
    public static function disco(): Filesystem
    {
        return Storage::disk(config('autoruta.disco_archivos'));
    }

    public static function guardar(UploadedFile $archivo, string $carpeta, string $nombre): string
    {
        return basename($archivo->storeAs($carpeta, $nombre, config('autoruta.disco_archivos')));
    }

    public static function url(string $ruta): string
    {
        // Disco local: la URL se arma con el dominio de la visita, no con APP_URL.
        if (config('filesystems.disks.' . config('autoruta.disco_archivos') . '.driver') === 'local') {
            return asset('storage/' . $ruta);
        }

        return self::disco()->url($ruta);
    }

    public static function borrar(string $ruta): void
    {
        self::disco()->delete($ruta);
    }
}
