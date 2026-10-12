<?php

namespace App\Support;

use Illuminate\Support\Str;

// Posicionamiento local: AutoRuta apunta a la Región de Los Lagos (Puerto Montt, Osorno, Chiloé...).
// Cada comuna tiene su página /autos-usados/{comuna} para búsquedas como "autos usados en Osorno".
class SeoLocal
{
    public const REGION = 'Los Lagos';
    public const SLUG_REGION = 'region-de-los-lagos';

    // Las que se muestran primero en los enlaces (las de más búsquedas).
    public const COMUNAS_DESTACADAS = [
        'Puerto Montt', 'Osorno', 'Puerto Varas', 'Castro', 'Ancud', 'Frutillar',
        'Llanquihue', 'Calbuco', 'Quellón', 'Purranque', 'Los Muermos', 'Río Negro',
    ];

    public static function comunas(): array
    {
        return config('regiones.' . self::REGION, []);
    }

    public static function slug(string $comuna): string
    {
        return Str::slug($comuna);
    }

    public static function comunaDesdeSlug(string $slug): ?string
    {
        foreach (self::comunas() as $comuna) {
            if (self::slug($comuna) === $slug) {
                return $comuna;
            }
        }

        return null;
    }

    // JSON-LD listo para <script type="application/ld+json"> (JSON_HEX_TAG evita cerrar el script con "</").
    public static function jsonLd(array $datos): string
    {
        return json_encode(['@context' => 'https://schema.org'] + $datos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG);
    }
}
