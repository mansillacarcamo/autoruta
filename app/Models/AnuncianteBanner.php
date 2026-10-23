<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnuncianteBanner extends Model
{
    protected $fillable = ['anunciante_id', 'tipo_medio', 'archivo', 'logo', 'archivo_alterno', 'tipo_alterno', 'segundos_principal', 'segundos_alterno', 'archivo_movil', 'tipo_movil', 'link_url', 'posicion', 'orden', 'clics'];

    public function anunciante(): BelongsTo
    {
        return $this->belongsTo(Anunciante::class);
    }

    public function url(): string
    {
        return \App\Support\Archivos::url('negocios/' . $this->archivo);
    }

    // Logo del negocio que va encima del aviso, centrado arriba (opcional). Sirve sobre todo en los videos
    // para saber de quién es el auto; se muestra sobre el archivo principal, el segundo y la versión celular.
    public function urlLogo(): ?string
    {
        return $this->logo ? \App\Support\Archivos::url('negocios/' . $this->logo) : null;
    }

    // Segundo archivo (imagen o video) que se alterna con el principal cada 3 segundos; opcional.
    public function urlAlterno(): ?string
    {
        return $this->archivo_alterno ? \App\Support\Archivos::url('negocios/' . $this->archivo_alterno) : null;
    }

    // Versión celular del aviso lateral (300 × 250, imagen o video); opcional.
    public function urlMovil(): ?string
    {
        return $this->archivo_movil ? \App\Support\Archivos::url('negocios/' . $this->archivo_movil) : null;
    }

    public function urlClic(): string
    {
        return route('publicidad.clic', $this);
    }

    // El link es opcional: sin link el banner se muestra igual, pero no se puede hacer clic.
    public function tieneLink(): bool
    {
        return (bool) self::normalizarLink($this->link_url);
    }

    // Etiqueta y atributos para envolver el banner: <a href=...> con link, <div> sin link.
    public function etiqueta(): string
    {
        return $this->tieneLink() ? 'a' : 'div';
    }

    public function atributosClic(): string
    {
        return $this->tieneLink() ? 'href="' . e($this->urlClic()) . '" target="_blank" rel="sponsored noopener"' : '';
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

    // Marca que la visita viene de AutoRuta: en WhatsApp deja escrito "Hola, vi tu anuncio en AutoRuta...";
    // en un sitio web agrega utm_source=autoruta para que el anunciante lo vea en su Google Analytics.
    public static function marcarOrigen(?string $link): ?string
    {
        $partes = $link ? parse_url($link) : false;
        if (! $partes || empty($partes['host'])) {
            return $link;
        }

        parse_str($partes['query'] ?? '', $query);
        $sitio = config('autoruta.nombre_sitio');
        $host = strtolower($partes['host']);
        // Links al propio AutoRuta (ej. el pop-up apuntando a /vehiculos) se dejan tal cual.
        if (in_array($host, [strtolower(request()->getHost()), strtolower((string) parse_url(config('app.url'), PHP_URL_HOST))], true)) {
            return $link;
        }

        if (in_array($host, ['wa.me', 'api.whatsapp.com', 'web.whatsapp.com', 'whatsapp.com', 'www.whatsapp.com'], true)) {
            $texto = trim((string) ($query['text'] ?? ''));
            if (stripos($texto, $sitio) === false) {
                $query['text'] = $texto === ''
                    ? "Hola, vi tu anuncio en $sitio y quiero más información."
                    : "Hola, vi tu anuncio en $sitio. $texto";
            }
        } elseif (! isset($query['utm_source'])) {
            $query += ['utm_source' => 'autoruta', 'utm_medium' => 'banner', 'utm_campaign' => 'publicidad'];
        }

        return $partes['scheme'] . '://' . $partes['host'] . (isset($partes['port']) ? ':' . $partes['port'] : '')
            . (($partes['path'] ?? '') ?: '/') . '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986)
            . (isset($partes['fragment']) ? '#' . $partes['fragment'] : '');
    }
}
