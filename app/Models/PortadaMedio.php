<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortadaMedio extends Model
{
    protected $table = 'portada_medios';

    protected $fillable = ['tipo_medio', 'archivo', 'orden'];

    public function url(): string
    {
        return \App\Support\Archivos::url('portada/' . $this->archivo);
    }
}
