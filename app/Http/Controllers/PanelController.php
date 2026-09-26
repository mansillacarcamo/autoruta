<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PanelController extends Controller
{
    public function index(Request $request)
    {
        $usuario = $request->user();
        $vehiculos = $usuario->vehiculos()->with('fotos')->orderByDesc('created_at')->get();
        $activas = $vehiculos->where('estado', 'activa')->count();

        return view('panel.index', compact('vehiculos', 'activas'));
    }

    public function crear(Request $request)
    {
        if ($request->user()->rol === 'negocio') {
            return redirect()->route('panel')->with('error', 'Las cuentas de negocio no publican vehículos.');
        }

        return view('panel.publicar');
    }

    public function guardar(Request $request)
    {
        $usuario = $request->user();

        if ($usuario->rol === 'negocio') {
            return redirect()->route('panel');
        }

        if ($usuario->vehiculos()->where('estado', 'activa')->count() >= config('autoruta.max_publicaciones_activas')) {
            $mensaje = 'Ya tienes ' . config('autoruta.max_publicaciones_activas') . ' publicaciones activas.';
            if ($request->expectsJson()) {
                return response()->json(['message' => $mensaje, 'errors' => ['limite' => [$mensaje]]], 422);
            }

            return back()->withInput()->with('error', $mensaje);
        }

        if (! $request->has('tipo')) {
            \Illuminate\Support\Facades\Log::warning('Publicar: formulario llegó vacío', [
                'content_length' => $request->server('CONTENT_LENGTH'),
                'content_type' => $request->header('Content-Type'),
                'campos' => array_keys($request->except('_token')),
                'archivos' => count($request->allFiles()),
                'post_max_size' => ini_get('post_max_size'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'user_agent' => $request->userAgent(),
            ]);
        }

        $request->merge([
            'precio' => preg_replace('/\D/', '', (string) $request->input('precio')),
            'kilometraje' => preg_replace('/\D/', '', (string) $request->input('kilometraje')),
        ]);

        $datos = $request->validate([
            'tipo' => 'required|in:' . implode(',', array_keys(Vehiculo::ETIQUETA_TIPO)),
            'marca' => 'required|string|max:60',
            'modelo' => 'required|string|max:60',
            'anio' => 'required|integer|min:' . config('autoruta.anio_min_vehiculo') . '|max:' . (date('Y') + 1),
            'precio' => 'required|integer|min:' . config('autoruta.precio_min_vehiculo') . '|max:' . config('autoruta.precio_max_vehiculo'),
            'kilometraje' => 'required|integer|min:0',
            'region' => 'required|string',
            'comuna' => 'required|string',
            'descripcion' => 'required|string|max:3000',
            'telefonoWhatsapp' => 'required|string|max:20',
            'transmision' => 'nullable|in:manual,automatica',
            'combustible' => 'nullable|in:bencina,diesel,hibrido,electrico,gas',
            'version' => 'nullable|string|max:100',
            'cilindrada' => 'nullable|string|max:20',
            'color' => 'nullable|string|max:40',
            'puertas' => 'nullable|integer|min:2|max:6',
            'traccion' => 'nullable|in:4x2,4x4,awd',
            'duenosAnteriores' => 'nullable|integer|min:0|max:20',
            'fotos' => 'required|array|min:1|max:' . config('autoruta.max_fotos_vehiculo'),
            'fotos.*' => 'image|max:10240',
        ], [
            'fotos.required' => 'Agrega al menos una foto del vehículo.',
            'fotos.max' => 'Puedes subir como máximo ' . config('autoruta.max_fotos_vehiculo') . ' fotos.',
            'fotos.*.image' => 'Uno de los archivos no es una imagen válida (usa JPG o PNG).',
            'fotos.*.max' => 'Cada foto puede pesar como máximo 10 MB.',
        ]);

        $vehiculo = Vehiculo::create([
            'user_id' => $usuario->id,
            'estado' => 'activa',
            'tipo' => $datos['tipo'],
            'marca' => $datos['marca'],
            'modelo' => $datos['modelo'],
            'anio' => $datos['anio'],
            'precio' => $datos['precio'],
            'kilometraje' => $datos['kilometraje'],
            'region' => $datos['region'],
            'comuna' => $datos['comuna'],
            'descripcion' => $datos['descripcion'],
            'version' => $datos['version'] ?? null,
            'transmision' => $datos['transmision'] ?? null,
            'combustible' => $datos['combustible'] ?? null,
            'cilindrada' => $datos['cilindrada'] ?? null,
            'color' => $datos['color'] ?? null,
            'puertas' => $datos['puertas'] ?? null,
            'traccion' => $datos['traccion'] ?? null,
            'duenos_anteriores' => $datos['duenosAnteriores'] ?? null,
            'publicado_en' => now(),
            'vence_en' => now()->addDays(config('autoruta.duracion_publicacion_dias')),
        ]);

        foreach ($request->file('fotos') as $i => $foto) {
            $ruta = $foto->storeAs('vehiculos', "veh{$vehiculo->id}_{$i}." . $foto->extension(), 'public');
            $vehiculo->fotos()->create(['archivo' => basename($ruta), 'orden' => $i]);
        }

        $usuario->update([
            'telefono_whatsapp' => $datos['telefonoWhatsapp'],
            'region' => $datos['region'],
            'comuna' => $datos['comuna'],
        ]);

        if ($request->expectsJson()) {
            session()->flash('ok', 'Vehículo publicado.');

            return response()->json(['redirect' => route('panel')]);
        }

        return redirect()->route('panel')->with('ok', 'Vehículo publicado.');
    }

    public function marcarVendido(Request $request, Vehiculo $vehiculo)
    {
        abort_unless($vehiculo->user_id === $request->user()->id, 403);
        $vehiculo->update(['estado' => 'vendida']);

        return back();
    }

    public function eliminar(Request $request, Vehiculo $vehiculo)
    {
        abort_unless($vehiculo->user_id === $request->user()->id, 403);

        foreach ($vehiculo->fotos as $foto) {
            Storage::disk('public')->delete('vehiculos/' . $foto->archivo);
        }
        $vehiculo->delete();

        return back();
    }
}
