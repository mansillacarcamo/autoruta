<?php

namespace App\Http\Controllers;

use App\Models\AnuncianteBanner;

// Los banners enlazan a esta ruta para contar el clic y luego redirigir al sitio del anunciante.
class PublicidadController extends Controller
{
    public function clic(AnuncianteBanner $banner)
    {
        // Los clics del administrador (al probar el link) no cuentan en la estadística.
        if (! auth()->user()?->esAdmin()) {
            $banner->increment('clics');
        }

        $destino = AnuncianteBanner::normalizarLink($banner->link_url);
        abort_unless($destino, 404);

        return redirect()->away($destino);
    }

    // Botones "WhatsApp" / "Sitio web" bajo el banner: suman al mismo contador de clics del banner.
    public function boton(AnuncianteBanner $banner, string $destino)
    {
        $negocio = $banner->anunciante;
        $url = $destino === 'whatsapp' ? $negocio?->urlWhatsapp() : $negocio?->urlSitioWeb();
        abort_unless($url, 404);

        if (! auth()->user()?->esAdmin()) {
            $banner->increment('clics');
        }

        return redirect()->away($url);
    }
}
