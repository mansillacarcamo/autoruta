<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anunciante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class NegocioController extends Controller
{
    public function index()
    {
        $negocios = Anunciante::withCount('banners')->orderByDesc('created_at')->get();
        $precioActual = (int) (DB::table('configuracion_sitio')->where('clave', 'precio_publicidad_negocio')->value('valor') ?? 15000);

        return view('admin.negocios.index', compact('negocios', 'precioActual'));
    }

    public function actualizarPrecio(Request $request)
    {
        $datos = $request->validate(['precioPublicidad' => 'required|integer|min:0']);

        DB::table('configuracion_sitio')->updateOrInsert(
            ['clave' => 'precio_publicidad_negocio'],
            ['valor' => (string) $datos['precioPublicidad']]
        );

        return back()->with('ok', 'Precio actualizado.');
    }

    public function crear()
    {
        return view('admin.negocios.crear');
    }

    public function guardar(Request $request)
    {
        $datos = $request->validate([
            'nombreNegocio' => 'required|string|max:120',
            'rubro' => 'required|in:financiera,taller,otro',
            'descripcion' => 'nullable|string|max:300',
            'telefonoWhatsapp' => 'nullable|string|max:20',
            'sitioWeb' => 'nullable|string|max:255',
            'estado' => 'required|in:activo,pausado',
        ]);

        $negocio = Anunciante::create([
            'nombre_negocio' => $datos['nombreNegocio'],
            'rubro' => $datos['rubro'],
            'descripcion' => $datos['descripcion'] ?? '',
            'telefono_whatsapp' => $datos['telefonoWhatsapp'] ?? null,
            'sitio_web' => $datos['sitioWeb'] ?? null,
            'estado' => $datos['estado'],
            'publicado_en' => $datos['estado'] === 'activo' ? now() : null,
        ]);

        return redirect()->route('admin.negocios.editar', $negocio)->with('ok', 'Negocio creado.');
    }

    public function editar(Anunciante $negocio)
    {
        $negocio->load('banners');

        return view('admin.negocios.editar', compact('negocio'));
    }

    public function actualizar(Request $request, Anunciante $negocio)
    {
        $datos = $request->validate([
            'nombreNegocio' => 'required|string|max:120',
            'rubro' => 'required|in:financiera,taller,otro',
            'descripcion' => 'nullable|string|max:300',
            'telefonoWhatsapp' => 'nullable|string|max:20',
            'sitioWeb' => 'nullable|string|max:255',
            'estado' => 'required|in:activo,pausado',
        ]);

        $seActivaAhora = $datos['estado'] === 'activo' && $negocio->estado !== 'activo';

        $negocio->update([
            'nombre_negocio' => $datos['nombreNegocio'],
            'rubro' => $datos['rubro'],
            'descripcion' => $datos['descripcion'] ?? '',
            'telefono_whatsapp' => $datos['telefonoWhatsapp'] ?? null,
            'sitio_web' => $datos['sitioWeb'] ?? null,
            'estado' => $datos['estado'],
            ...($seActivaAhora ? ['publicado_en' => now(), 'vence_en' => now()->addDays(30)] : []),
        ]);

        return redirect()->route('admin.negocios.editar', $negocio)->with('ok', 'Cambios guardados.');
    }

    public function subirBanner(Request $request, Anunciante $negocio)
    {
        if ($negocio->banners()->count() >= config('autoruta.max_banners_negocio')) {
            return back()->with('error', 'Máximo ' . config('autoruta.max_banners_negocio') . ' banners por negocio.');
        }

        $datos = $request->validate([
            'tipoMedio' => 'required|in:imagen,video',
            'posicion' => 'required|in:' . implode(',', array_keys(Anunciante::POSICIONES)),
            'linkUrl' => 'required|string|max:255',
            'archivo' => 'required|file|mimes:jpg,jpeg,png,webp,mp4,webm|max:20480',
        ]);

        $ruta = $request->file('archivo')->storeAs(
            'negocios',
            'neg' . $negocio->id . '_' . time() . '.' . $request->file('archivo')->extension(),
            'public'
        );

        $negocio->banners()->create([
            'tipo_medio' => $datos['tipoMedio'],
            'archivo' => basename($ruta),
            'link_url' => $datos['linkUrl'],
            'posicion' => $datos['posicion'],
        ]);

        return back()->with('ok', 'Banner subido.');
    }

    public function eliminarBanner(Anunciante $negocio, \App\Models\AnuncianteBanner $banner)
    {
        abort_unless($banner->anunciante_id === $negocio->id, 404);
        Storage::disk('public')->delete('negocios/' . $banner->archivo);
        $banner->delete();

        return back()->with('ok', 'Banner eliminado.');
    }
}
