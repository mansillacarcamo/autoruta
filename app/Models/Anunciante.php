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
    public const ETIQUETA_POSICION = [
        'inicio' => 'Inicio', 'listado' => 'Listado', 'superior' => 'Superior',
        'inferior' => 'Inferior', 'lateral' => 'Lateral',
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
