<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnuncianteBanner extends Model
{
    protected $fillable = ['anunciante_id', 'tipo_medio', 'archivo', 'link_url', 'posicion', 'orden'];

    public function anunciante(): BelongsTo
    {
        return $this->belongsTo(Anunciante::class);
    }

    public function url(): string
    {
        return asset('storage/negocios/' . $this->archivo);
    }
}
