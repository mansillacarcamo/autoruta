<?php

namespace App\Http\Controllers;

use App\Models\AnuncianteBanner;
use App\Models\Vehiculo;
use Illuminate\Http\Request;

class VehiculoController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->filtrar(Vehiculo::activos()->with('fotos'), $request);

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
        // Celulares chilenos escritos sin código de país (9XXXXXXXX): wa.me exige el 56.
        if (strlen($numeroWa) === 9 && str_starts_with($numeroWa, '9')) {
            $numeroWa = '56' . $numeroWa;
        }
        $mensajeWa = urlencode("Hola, vi tu auto {$vehiculo->marca} {$vehiculo->modelo} {$vehiculo->anio} en " . config('autoruta.nombre_sitio') . ', me gustaría saber más información.');

        return view('vehiculos.show', compact('vehiculo', 'negocioDestacado', 'ficha', 'numeroWa', 'mensajeWa'));
    }

    // Vehículos publicados después de $desde (id), con los mismos filtros del listado.
    // La página lo consulta cada cierto tiempo para mostrar publicaciones nuevas sin recargar.
    public function nuevos(Request $request)
    {
        $desde = (int) $request->query('desde');
        $nuevos = $this->filtrar(Vehiculo::activos()->with('fotos'), $request)
            ->where('id', '>', $desde)
            ->orderByDesc('id')
            ->take(8)
            ->get();

        return response()->json([
            'cantidad' => $nuevos->count(),
            'ultimo_id' => $nuevos->max('id') ?? $desde,
            'html' => $nuevos->map(fn ($v) => view('vehiculos._tarjeta', ['v' => $v, 'nuevo' => true])->render())->implode(''),
        ]);
    }

    private function filtrar($query, Request $request)
    {
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

        return $query;
    }
}
