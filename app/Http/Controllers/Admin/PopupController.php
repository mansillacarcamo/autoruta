<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Popup;
use App\Support\Archivos;
use Illuminate\Http\Request;

// Un solo pop-up a la vez: subir uno nuevo reemplaza al anterior.
class PopupController extends Controller
{
    public function index()
    {
        $popup = Popup::latest('id')->first();

        return view('admin.popup.index', compact('popup'));
    }

    public function subir(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:jpg,jpeg,png,webp,gif,mp4,webm|max:20480',
            'link_url' => 'nullable|url|max:255',
        ], [
            'archivo.mimes' => 'Formato no admitido. Usa JPG, PNG, WEBP, GIF o video MP4/WEBM.',
            'archivo.max' => 'El archivo puede pesar como máximo 20 MB.',
            'link_url.url' => 'El enlace debe ser una dirección completa, por ejemplo https://autoruta.cl/vehiculos',
        ]);

        foreach (Popup::all() as $anterior) {
            Archivos::borrar('popup/' . $anterior->archivo);
            $anterior->delete();
        }

        $archivo = $request->file('archivo');
        $nombre = Archivos::guardar($archivo, 'popup', 'popup_' . time() . '.' . $archivo->extension());

        Popup::create([
            'tipo_medio' => in_array($archivo->extension(), ['mp4', 'webm']) ? 'video' : 'imagen',
            'archivo' => $nombre,
            'link_url' => $request->input('link_url') ?: null,
            'activo' => true,
        ]);

        return back()->with('ok', 'Pop-up publicado. Los visitantes lo verán al entrar al inicio.');
    }

    public function actualizar(Request $request, Popup $popup)
    {
        $request->validate(['link_url' => 'nullable|url|max:255']);

        $popup->update([
            'link_url' => $request->input('link_url') ?: null,
            'activo' => $request->boolean('activo'),
        ]);

        return back()->with('ok', $popup->activo ? 'Pop-up activado.' : 'Pop-up pausado: ya no se muestra a los visitantes.');
    }

    public function eliminar(Popup $popup)
    {
        Archivos::borrar('popup/' . $popup->archivo);
        $popup->delete();

        return back()->with('ok', 'Pop-up eliminado.');
    }
}
