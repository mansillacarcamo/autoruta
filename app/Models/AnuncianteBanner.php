<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnuncianteBanner extends Model
{
    protected $fillable = ['anunciante_id', 'tipo_medio', 'archivo', 'link_url', 'posicion', 'orden', 'clics'];

    public function anunciante(): BelongsTo
    {
        return $this->belongsTo(Anunciante::class);
    }

    public function url(): string
    {
        return \App\Support\Archivos::url('negocios/' . $this->archivo);
    }

    public function urlClic(): string
    {
        return route('publicidad.clic', $this);
    }

    // Botones "WhatsApp" y "Sitio web" bajo el banner (con los datos del negocio); también cuentan el clic.
    public function urlBoton(string $destino): string
    {
        return route('publicidad.boton', [$this, $destino]);
    }

    // Completa "www.taller.cl" como "https://www.taller.cl". Solo acepta http(s) para no
    // redirigir a esquemas peligrosos (javascript:, data:, etc.).
    public static function normalizarLink(?string $link): ?string
    {
        $link = trim((string) $link);
        if ($link === '' || preg_match('#^(javascript|data|vbscript|file):#i', $link)) {
            return null;
        }
        if (! preg_match('#^[a-z][a-z0-9+.-]*://#i', $link)) {
            $link = 'https://' . ltrim($link, '/');
        }

        return preg_match('#^https?://[^\s/$.?\#].[^\s]*$#i', $link) ? $link : null;
    }
}
