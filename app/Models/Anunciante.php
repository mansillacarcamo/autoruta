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
        'lateral_izquierdo' => ['Lateral izquierdo', '160 × 600 px'],
        'lateral_derecho' => ['Lateral derecho', '160 × 600 px'],
        'inicio' => ['Inicio · "Auspiciado por"', '1280 × 720 px'],
        'listado' => ['Ficha de vehículo · negocio destacado', '1280 × 720 px'],
    ];

    public const ETIQUETA_POSICION = [
        'superior' => 'Superior', 'inferior' => 'Inferior',
        'lateral_izquierdo' => 'Lateral izquierdo', 'lateral_derecho' => 'Lateral derecho',
        'inicio' => 'Inicio', 'listado' => 'Ficha de vehículo', 'lateral' => 'Lateral',
    ];

    public function banners(): HasMany
    {
        return $this->hasMany(AnuncianteBanner::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }
}
