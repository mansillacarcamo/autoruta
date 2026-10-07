<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehiculo extends Model
{
    protected $fillable = [
        'user_id', 'estado', 'tipo', 'marca', 'modelo', 'anio', 'precio', 'pie', 'kilometraje',
        'region', 'comuna', 'descripcion', 'version', 'transmision', 'combustible',
        'cilindrada', 'color', 'puertas', 'traccion', 'duenos_anteriores', 'equipamiento',
        'vistas', 'publicado_en', 'vence_en',
    ];

    // "premium" queda fuera de $fillable a propósito: solo el admin lo cambia (Admin > Vehículos).
    protected $casts = [
        'publicado_en' => 'datetime',
        'vence_en' => 'datetime',
        'premium' => 'boolean',
        'premium_desde' => 'datetime',
    ];

    public const ETIQUETA_TIPO = [
        'auto' => 'Auto', 'citycar' => 'City car', 'hatchback' => 'Hatchback', 'camioneta' => 'Camioneta', 'suv' => 'SUV',
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

    // Activa y sin vencer: los avisos duran config('autoruta.duracion_publicacion_dias') y se pueden renovar.
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activa')
            ->where(fn ($q) => $q->whereNull('vence_en')->orWhere('vence_en', '>', now()));
    }

    // Los Premium van primero (el último activado arriba); después, el orden que se pida.
    public function scopePremiumPrimero($query)
    {
        return $query->orderByDesc('premium')->orderByDesc('premium_desde');
    }

    // Ids ordenados intercalando automotoras (una de cada una, por turnos), para que ninguna acapare el listado.
    // Los Premium siguen arriba, intercalados entre ellos; dentro de cada turno va primero lo más reciente.
    // Se ordena en PHP y no con ROW_NUMBER() para no depender de la versión de MySQL del hosting.
    public static function idsIntercalados($query): array
    {
        $filas = $query->premiumPrimero()
            ->orderByDesc('publicado_en')->orderByDesc('id')
            ->get(['id', 'user_id', 'premium']);

        $turnos = [];
        $posicion = 0;
        $filas = $filas->map(function ($v) use (&$turnos, &$posicion) {
            $grupo = ($v->premium ? 'p' : 'n') . $v->user_id;
            $turnos[$grupo] = ($turnos[$grupo] ?? -1) + 1;
            return [$v->id, $v->premium ? 0 : 1, $turnos[$grupo], $posicion++];
        })->all();

        usort($filas, fn ($a, $b) => [$a[1], $a[2], $a[3]] <=> [$b[1], $b[2], $b[3]]);

        return array_column($filas, 0);
    }

    public function estaVencida(): bool
    {
        return $this->estado === 'activa' && $this->vence_en && $this->vence_en->isPast();
    }

    public function estaVisible(): bool
    {
        return $this->estado === 'activa' && ! $this->estaVencida();
    }

    public function diasRestantes(): ?int
    {
        return $this->vence_en ? max(0, (int) ceil(now()->diffInDays($this->vence_en, false))) : null;
    }

    public function primeraFotoUrl(): string
    {
        $foto = $this->fotos->first();
        return $foto ? \App\Support\Archivos::url('vehiculos/' . $foto->archivo) : asset('img/vehiculo-placeholder.svg');
    }

    public function fotosUrls(): array
    {
        return $this->fotos->map(fn ($f) => \App\Support\Archivos::url('vehiculos/' . $f->archivo))->all();
    }

    public function precioFormateado(): string
    {
        return '$' . number_format($this->precio, 0, ',', '.');
    }

    public function pieFormateado(): ?string
    {
        return $this->pie ? '$' . number_format($this->pie, 0, ',', '.') : null;
    }
}
