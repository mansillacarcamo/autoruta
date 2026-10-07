<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Banners del slider principal del inicio (después del banner fijo de AutoRuta). Admin > Portada.
class PortadaMedio extends Model
{
    protected $table = 'portada_medios';

    protected $fillable = ['tipo_medio', 'archivo', 'link_url', 'archivo_movil', 'orden'];

    public function url(): string
    {
        return \App\Support\Archivos::url('portada/' . $this->archivo);
    }

    // Imagen opcional para celular (formato más alto); si no hay, en celular se usa el archivo principal.
    public function urlMovil(): ?string
    {
        return $this->archivo_movil ? \App\Support\Archivos::url('portada/' . $this->archivo_movil) : null;
    }
}
