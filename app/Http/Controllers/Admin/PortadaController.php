<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PortadaMedio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PortadaController extends Controller
{
    private const MAX_MEDIOS = 6;

    public function index()
    {
        $medios = PortadaMedio::orderBy('orden')->orderBy('id')->get();
        $maxMedios = self::MAX_MEDIOS;

        return view('admin.portada.index', compact('medios', 'maxMedios'));
    }

    public function subir(Request $request)
    {
        if (PortadaMedio::count() >= self::MAX_MEDIOS) {
            return back()->with('error', 'Máximo ' . self::MAX_MEDIOS . ' archivos en la portada.');
        }

        $request->validate([
            'archivo' => 'required|file|mimes:jpg,jpeg,png,webp,mp4,webm|max:20480',
        ]);

        $archivo = $request->file('archivo');
        $esVideo = in_array($archivo->extension(), ['mp4', 'webm']);
        $nombre = \App\Support\Archivos::guardar($archivo, 'portada', 'portada_' . time() . '.' . $archivo->extension());

        PortadaMedio::create([
            'tipo_medio' => $esVideo ? 'video' : 'imagen',
            'archivo' => $nombre,
            'orden' => (int) PortadaMedio::max('orden') + 1,
        ]);

        return back()->with('ok', 'Archivo agregado a la portada.');
    }

    public function eliminar(PortadaMedio $medio)
    {
        \App\Support\Archivos::borrar('portada/' . $medio->archivo);
        $medio->delete();

        return back()->with('ok', 'Archivo eliminado.');
    }
}
