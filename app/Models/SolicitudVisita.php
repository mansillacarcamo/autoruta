<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudVisita extends Model
{
    protected $table = 'solicitudes_visita';

    protected $fillable = ['vehiculo_id', 'user_id', 'nombre', 'telefono', 'fecha', 'hora', 'estado'];

    protected $casts = ['fecha' => 'date'];

    public function vehiculo(): BelongsTo
    {
        return $this->belongsTo(Vehiculo::class);
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // "jueves 16 de octubre"
    public function fechaTexto(): string
    {
        return $this->fecha->copy()->locale('es')->translatedFormat('l j \d\e F');
    }

    // Link para que el vendedor le responda al comprador por WhatsApp con el mensaje ya escrito.
    public function urlResponderWhatsapp(): string
    {
        $v = $this->vehiculo;
        $texto = "Hola {$this->nombre}, recibí tu solicitud en " . config('autoruta.nombre_sitio')
            . " para ver el {$v->marca} {$v->modelo} {$v->anio} el {$this->fechaTexto()} a las {$this->hora} hrs. ";

        return 'https://wa.me/' . $this->telefono . '?text=' . rawurlencode($texto);
    }
}
