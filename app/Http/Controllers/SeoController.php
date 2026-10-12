<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use App\Support\SeoLocal;

class SeoController extends Controller
{
    // Mapa del sitio para Google: páginas fijas, una por comuna de Los Lagos y los avisos vigentes.
    public function sitemap()
    {
        $urls = [
            ['loc' => route('home'), 'prioridad' => '1.0'],
            ['loc' => route('vehiculos.index'), 'prioridad' => '0.9'],
            ['loc' => route('vehiculos.lugar', SeoLocal::SLUG_REGION), 'prioridad' => '0.9'],
            ['loc' => route('vender'), 'prioridad' => '0.8'],
            ['loc' => route('publicidad-automotriz'), 'prioridad' => '0.8'],
            ['loc' => route('como-funciona'), 'prioridad' => '0.5'],
        ];
        foreach (SeoLocal::comunas() as $comuna) {
            $urls[] = ['loc' => route('vehiculos.lugar', SeoLocal::slug($comuna)), 'prioridad' => in_array($comuna, SeoLocal::COMUNAS_DESTACADAS, true) ? '0.8' : '0.6'];
        }
        foreach (Vehiculo::activos()->orderByDesc('id')->get(['id', 'updated_at']) as $v) {
            $urls[] = ['loc' => route('vehiculos.show', $v), 'prioridad' => '0.7', 'modificado' => $v->updated_at?->toAtomString()];
        }

        return response()->view('seo.sitemap', compact('urls'))->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots()
    {
        $texto = "User-agent: *\nDisallow: /admin\nDisallow: /panel\n\nSitemap: " . route('sitemap') . "\n";

        return response($texto)->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
