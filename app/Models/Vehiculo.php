<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehiculo extends Model
{
    protected $fillable = [
        'user_id', 'estado', 'tipo', 'marca', 'modelo', 'anio', 'precio', 'kilometraje',
        'region', 'comuna', 'descripcion', 'version', 'transmision', 'combustible',
        'cilindrada', 'color', 'puertas', 'traccion', 'duenos_anteriores', 'equipamiento',
        'vistas', 'publicado_en', 'vence_en',
    ];

    protected $casts = [
        'publicado_en' => 'datetime',
        'vence_en' => 'datetime',
    ];

    public const ETIQUETA_TIPO = [
        'auto' => 'Auto', 'citycar' => 'City car', 'camioneta' => 'Camioneta', 'suv' => 'SUV',
        'moto' => 'Moto', 'camion' => 'Camiones', 'bus' => 'Buses',
        'maquinaria' => 'Maquinaria agrícola', 'otro' => 'Otro',
    ];
    public const ETIQUETA_TRANSMISION = ['manual' => 'Manual', 'automatica' => 'Automática'];
    public const ETIQUETA_COMBUSTIBLE = [
        'bencina' => 'Bencina', 'diesel' => 'Diésel', 'hibrido' => 'Híbrido',
        'electrico' => 'Eléctrico', 'gas' => 'Gas',
    ];
    public const ETIQUETA_TRACCION = ['4x2' => '4x2', '4x4' => '4x4', 'awd' => 'AWD'];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function fotos(): HasMany
    {
        return $this->hasMany(VehiculoFoto::class)->orderBy('orden');
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', 'activa');
    }

    public function primeraFotoUrl(): string
    {
        $foto = $this->fotos->first();
        return $foto ? asset('storage/vehiculos/' . $foto->archivo) : asset('img/vehiculo-placeholder.svg');
    }

    public function precioFormateado(): string
    {
        return '$' . number_format($this->precio, 0, ',', '.');
    }
}
