<?php

namespace App\Http\Controllers;

use App\Models\AnuncianteBanner;
use App\Models\Vehiculo;
use Illuminate\Http\Request;

class VehiculoController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehiculo::activos()->with('fotos');

        if ($q = $request->string('q')->toString()) {
            $query->where(fn ($w) => $w->where('marca', 'like', "%$q%")->orWhere('modelo', 'like', "%$q%"));
        }
        foreach (['tipo', 'region', 'comuna'] as $campo) {
            if ($valor = $request->string($campo)->toString()) {
                $query->where($campo, $valor);
            }
        }
        if ($request->filled('precioMin')) $query->where('precio', '>=', (int) $request->precioMin);
        if ($request->filled('precioMax')) $query->where('precio', '<=', (int) $request->precioMax);

        match ($request->string('orden')->toString()) {
            'precio_asc' => $query->orderBy('precio'),
            'precio_desc' => $query->orderByDesc('precio'),
            'km_asc' => $query->orderBy('kilometraje'),
            default => $query->orderByDesc('publicado_en'),
        };

        $vehiculos = $query->get();

        return view('vehiculos.index', compact('vehiculos'));
    }

    public function show(Vehiculo $vehiculo)
    {
        $vehiculo->increment('vistas');
        $vehiculo->load(['fotos', 'usuario']);

        $negocioDestacado = AnuncianteBanner::where('posicion', 'listado')
            ->whereHas('anunciante', fn ($q) => $q->activos())
            ->with('anunciante')
            ->orderBy('orden')
            ->first();

        $ficha = array_filter([
            'Versión' => $vehiculo->version,
            'Transmisión' => Vehiculo::ETIQUETA_TRANSMISION[$vehiculo->transmision] ?? null,
            'Combustible' => Vehiculo::ETIQUETA_COMBUSTIBLE[$vehiculo->combustible] ?? null,
            'Cilindrada' => $vehiculo->cilindrada,
            'Color' => $vehiculo->color,
            'Puertas' => $vehiculo->puertas,
            'Tracción' => Vehiculo::ETIQUETA_TRACCION[$vehiculo->traccion] ?? null,
            'Dueños anteriores' => $vehiculo->duenos_anteriores,
        ]);

        $numeroWa = preg_replace('/\D/', '', $vehiculo->usuario->telefono_whatsapp ?: config('autoruta.contacto_whatsapp'));
        $mensajeWa = urlencode("Hola, vi tu {$vehiculo->marca} {$vehiculo->modelo} {$vehiculo->anio} en " . config('autoruta.nombre_sitio') . ' y me interesa');

        return view('vehiculos.show', compact('vehiculo', 'negocioDestacado', 'ficha', 'numeroWa', 'mensajeWa'));
    }
}
