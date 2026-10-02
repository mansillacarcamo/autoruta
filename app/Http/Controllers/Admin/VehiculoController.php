<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehiculo;
use Illuminate\Http\Request;

class VehiculoController extends Controller
{
    public function index(Request $request)
    {
        $buscar = trim((string) $request->input('q'));

        $vehiculos = Vehiculo::with(['fotos', 'usuario'])
            ->when($buscar, fn ($q) => $q->where(fn ($q) => $q
                ->where('marca', 'like', "%{$buscar}%")
                ->orWhere('modelo', 'like', "%{$buscar}%")
                ->orWhereHas('usuario', fn ($u) => $u->where('name', 'like', "%{$buscar}%")->orWhere('nombre_comercial', 'like', "%{$buscar}%"))))
            ->when($request->input('filtro') === 'premium', fn ($q) => $q->where('premium', true))
            ->premiumPrimero()
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        $totalPremium = Vehiculo::where('premium', true)->count();

        return view('admin.vehiculos.index', compact('vehiculos', 'buscar', 'totalPremium'));
    }

    public function premium(Request $request, Vehiculo $vehiculo)
    {
        $activar = $request->boolean('premium');
        $vehiculo->forceFill([
            'premium' => $activar,
            'premium_desde' => $activar ? now() : null,
        ])->save();

        return back()->with('ok', $activar
            ? "{$vehiculo->marca} {$vehiculo->modelo} ahora es Premium: aparece primero en la web."
            : "{$vehiculo->marca} {$vehiculo->modelo} ya no es Premium.");
    }
}
