<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Popup extends Model
{
    protected $fillable = ['tipo_medio', 'archivo', 'link_url', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function url(): string
    {
        return \App\Support\Archivos::url('popup/' . $this->archivo);
    }

    // Pop-up que ven los visitantes (el último subido que esté activo).
    public static function visible(): ?self
    {
        return static::where('activo', true)->latest('id')->first();
    }
}
