<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Anunciante extends Model
{
    protected $fillable = [
        'nombre_negocio', 'rubro', 'logo', 'descripcion', 'telefono_whatsapp',
        'sitio_web', 'estado', 'publicado_en', 'vence_en',
    ];

    protected $casts = [
        'publicado_en' => 'datetime',
        'vence_en' => 'datetime',
    ];

    public const ETIQUETA_RUBRO = ['financiera' => 'Financiera', 'taller' => 'Taller mecánico', 'otro' => 'Otro'];
    public const ETIQUETA_ESTADO = ['activo' => 'Activo', 'pausado' => 'Pausado', 'vencido' => 'Vencido'];
    // posicion => [etiqueta, medida recomendada]
    public const POSICIONES = [
        'superior' => ['Superior (bajo el menú)', '728 × 90 px'],
        'inferior' => ['Inferior (sobre el pie de página)', '728 × 90 px'],
        'lateral_izquierdo' => ['Lateral izquierdo 1', '160 × 600 px'],
        'lateral_izquierdo_2' => ['Lateral izquierdo 2', '160 × 600 px'],
        'lateral_izquierdo_3' => ['Lateral izquierdo 3', '160 × 600 px'],
        'lateral_izquierdo_4' => ['Lateral izquierdo 4', '160 × 600 px'],
        'lateral_izquierdo_5' => ['Lateral izquierdo 5', '160 × 600 px'],
        'lateral_derecho' => ['Lateral derecho 1', '160 × 600 px'],
        'lateral_derecho_2' => ['Lateral derecho 2', '160 × 600 px'],
        'lateral_derecho_3' => ['Lateral derecho 3', '160 × 600 px'],
        'lateral_derecho_4' => ['Lateral derecho 4', '160 × 600 px'],
        'lateral_derecho_5' => ['Lateral derecho 5', '160 × 600 px'],
        'inicio' => ['Inicio · "Auspiciado por"', '1280 × 720 px'],
        'listado' => ['Ficha de vehículo · negocio destacado', '1280 × 720 px'],
    ];

    public const ETIQUETA_POSICION = [
        'superior' => 'Superior', 'inferior' => 'Inferior',
        'lateral_izquierdo' => 'Lateral izquierdo 1', 'lateral_izquierdo_2' => 'Lateral izquierdo 2', 'lateral_izquierdo_3' => 'Lateral izquierdo 3',
        'lateral_izquierdo_4' => 'Lateral izquierdo 4', 'lateral_izquierdo_5' => 'Lateral izquierdo 5',
        'lateral_derecho' => 'Lateral derecho 1', 'lateral_derecho_2' => 'Lateral derecho 2', 'lateral_derecho_3' => 'Lateral derecho 3',
        'lateral_derecho_4' => 'Lateral derecho 4', 'lateral_derecho_5' => 'Lateral derecho 5',
        'inicio' => 'Inicio', 'listado' => 'Ficha de vehículo', 'lateral' => 'Lateral',
    ];

    // Cantidad de avisos laterales por lado (lateral_izquierdo, lateral_izquierdo_2 … lateral_izquierdo_N).
    public const LATERALES_POR_LADO = 5;

    /** @return list<string> posiciones laterales de un lado ('izquierdo' o 'derecho'), en orden */
    public static function posicionesLaterales(string $lado): array
    {
        return array_map(
            fn ($n) => 'lateral_' . $lado . ($n > 1 ? '_' . $n : ''),
            range(1, self::LATERALES_POR_LADO)
        );
    }

    // Link de WhatsApp del negocio (celulares chilenos sin código de país se completan con 56).
    public function urlWhatsapp(): ?string
    {
        $numero = preg_replace('/\D/', '', (string) $this->telefono_whatsapp);
        if (strlen($numero) === 9 && str_starts_with($numero, '9')) {
            $numero = '56' . $numero;
        }
        if (strlen($numero) < 8) {
            return null;
        }

        return 'https://wa.me/' . $numero . '?text=' . rawurlencode('Hola, vi su aviso en ' . config('autoruta.nombre_sitio') . ' y me gustaría más información.');
    }

    public function urlSitioWeb(): ?string
    {
        return AnuncianteBanner::normalizarLink($this->sitio_web);
    }

    public function banners(): HasMany
    {
        return $this->hasMany(AnuncianteBanner::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }
}
