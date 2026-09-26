<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Providers\AppServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConfiguracionController extends Controller
{
    public function editar()
    {
        return view('admin.configuracion');
    }

    public function actualizar(Request $request)
    {
        $datos = $request->validate([
            'facebook' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'tiktok' => 'nullable|url|max:255',
            'youtube' => 'nullable|url|max:255',
            'whatsapp' => 'required|string|max:20',
            'telefono' => 'required|string|max:30',
            'email' => 'required|email|max:120',
            'ubicacion' => 'required|string|max:120',
        ]);

        $valores = [
            'redes_sociales.facebook' => $datos['facebook'] ?? '',
            'redes_sociales.instagram' => $datos['instagram'] ?? '',
            'redes_sociales.tiktok' => $datos['tiktok'] ?? '',
            'redes_sociales.youtube' => $datos['youtube'] ?? '',
            'contacto_whatsapp' => $datos['whatsapp'],
            'contacto_telefono' => $datos['telefono'],
            'contacto_email' => $datos['email'],
            'contacto_ubicacion' => $datos['ubicacion'],
        ];

        foreach (AppServiceProvider::AJUSTES_EDITABLES as $clave) {
            DB::table('configuracion_sitio')->updateOrInsert(
                ['clave' => 'autoruta.' . $clave],
                ['valor' => $valores[$clave]]
            );
        }

        return back()->with('ok', 'Configuración guardada.');
    }
}
