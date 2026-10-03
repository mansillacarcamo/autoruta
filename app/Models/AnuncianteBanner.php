<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnuncianteBanner extends Model
{
    protected $fillable = ['anunciante_id', 'tipo_medio', 'archivo', 'archivo_capa', 'opacidad_capa', 'zona_video', 'link_url', 'posicion', 'orden', 'clics'];

    protected $casts = [
        'zona_video' => 'array',
    ];

    // Zona [x, y, ancho, alto] (%) donde se ve el video sobre la imagen; null = video de fondo completo.
    public function zonaVideo(): ?array
    {
        return $this->tipo_medio === 'video' && $this->archivo_capa && is_array($this->zona_video) && count($this->zona_video) === 4
            ? $this->zona_video
            : null;
    }

    // Convierte "x,y,ancho,alto" (lo que envía el editor del admin) en la zona guardada; vacío = sin zona.
    public static function normalizarZona(?string $texto): ?array
    {
        $partes = array_map('floatval', array_filter(explode(',', (string) $texto), 'is_numeric'));
        if (count($partes) !== 4) {
            return null;
        }
        [$x, $y, $ancho, $alto] = array_map(fn ($v) => round(min(max($v, 0), 100), 2), $partes);
        $ancho = min($ancho, 100 - $x);
        $alto = min($alto, 100 - $y);

        return $ancho >= 3 && $alto >= 3 ? [$x, $y, $ancho, $alto] : null;
    }

    public function anunciante(): BelongsTo
    {
        return $this->belongsTo(Anunciante::class);
    }

    public function url(): string
    {
        return \App\Support\Archivos::url('negocios/' . $this->archivo);
    }

    // Imagen transparente que va encima del video (solo banners de video; opcional).
    public function urlCapa(): ?string
    {
        return $this->archivo_capa ? \App\Support\Archivos::url('negocios/' . $this->archivo_capa) : null;
    }

    public function urlClic(): string
    {
        return route('publicidad.clic', $this);
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
