<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public const AJUSTES_EDITABLES = [
        'redes_sociales.facebook',
        'redes_sociales.instagram',
        'redes_sociales.tiktok',
        'redes_sociales.youtube',
        'contacto_whatsapp',
        'contacto_telefono',
        'contacto_email',
        'contacto_ubicacion',
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Los valores guardados desde el panel admin reemplazan a los de config/autoruta.php.
        // Durante la instalación o migraciones la tabla puede no existir todavía.
        try {
            $guardados = DB::table('configuracion_sitio')
                ->whereIn('clave', array_map(fn ($c) => 'autoruta.' . $c, self::AJUSTES_EDITABLES))
                ->pluck('valor', 'clave');
        } catch (\Throwable) {
            return;
        }

        foreach ($guardados as $clave => $valor) {
            config([$clave => $valor]);
        }
    }
}
