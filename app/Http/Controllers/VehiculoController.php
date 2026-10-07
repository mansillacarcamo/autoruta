<?php

namespace App\Http\Controllers;

use App\Models\AnuncianteBanner;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class VehiculoController extends Controller
{
    public function index(Request $request)
    {
        $orden = $request->string('orden')->toString();

        if (in_array($orden, ['precio_asc', 'precio_desc', 'km_asc'], true)) {
            $query = $this->filtrar(Vehiculo::activos()->with(['fotos', 'usuario']), $request)->premiumPrimero();
            match ($orden) {
                'precio_asc' => $query->orderBy('precio'),
                'precio_desc' => $query->orderByDesc('precio'),
                'km_asc' => $query->orderBy('kilometraje'),
            };
            $vehiculos = $query->paginate(24)->withQueryString();
        } else {
            // Orden por defecto: más recientes, intercalando automotoras.
            $ids = Vehiculo::idsIntercalados($this->filtrar(Vehiculo::activos(), $request));
            $pagina = LengthAwarePaginator::resolveCurrentPage();
            $idsPagina = array_slice($ids, ($pagina - 1) * 24, 24);
            $items = Vehiculo::with(['fotos', 'usuario'])->whereIn('id', $idsPagina)->get()
                ->sortBy(fn ($v) => array_search($v->id, $idsPagina))->values();
            $vehiculos = (new LengthAwarePaginator($items, count($ids), 24, $pagina, [
                'path' => $request->url(),
            ]))->withQueryString();
        }

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

        // [etiqueta, valor, ícono]: solo se muestran los datos que el vendedor completó.
        $ficha = array_values(array_filter([
            ['Año', $vehiculo->anio, 'anio'],
            ['Kilometraje', number_format($vehiculo->kilometraje, 0, ',', '.') . ' km', 'km'],
            ['Tipo', Vehiculo::ETIQUETA_TIPO[$vehiculo->tipo] ?? null, 'tipo'],
            ['Versión', $vehiculo->version, 'version'],
            ['Transmisión', Vehiculo::ETIQUETA_TRANSMISION[$vehiculo->transmision] ?? null, 'transmision'],
            ['Combustible', Vehiculo::ETIQUETA_COMBUSTIBLE[$vehiculo->combustible] ?? null, 'combustible'],
            ['Cilindrada', $vehiculo->cilindrada, 'cilindrada'],
            ['Color', $vehiculo->color, 'color'],
            ['Puertas', $vehiculo->puertas, 'puertas'],
            ['Tracción', Vehiculo::ETIQUETA_TRACCION[$vehiculo->traccion] ?? null, 'traccion'],
            ['Dueños anteriores', $vehiculo->duenos_anteriores, 'duenos'],
        ], fn ($dato) => $dato[1] !== null && $dato[1] !== ''));

        $descripcion = \App\Support\DescripcionProfesional::formatear(
            $vehiculo->descripcion,
            $vehiculo->equipamiento ? explode(',', $vehiculo->equipamiento) : []
        );

        $numeroWa = preg_replace('/\D/', '', $vehiculo->usuario->telefono_whatsapp ?: config('autoruta.contacto_whatsapp'));
        // Celulares chilenos escritos sin código de país (9XXXXXXXX): wa.me exige el 56.
        if (strlen($numeroWa) === 9 && str_starts_with($numeroWa, '9')) {
            $numeroWa = '56' . $numeroWa;
        }
        $mensajeWa = urlencode("Hola, vi tu auto {$vehiculo->marca} {$vehiculo->modelo} {$vehiculo->anio} en " . config('autoruta.nombre_sitio') . ', me gustaría saber más información.');

        return view('vehiculos.show', compact('vehiculo', 'negocioDestacado', 'ficha', 'descripcion', 'numeroWa', 'mensajeWa'));
    }

    // Vehículos publicados después de $desde (id), con los mismos filtros del listado.
    // La página lo consulta cada cierto tiempo para mostrar publicaciones nuevas sin recargar.
    public function nuevos(Request $request)
    {
        $desde = (int) $request->query('desde');
        $nuevos = $this->filtrar(Vehiculo::activos()->with(['fotos', 'usuario']), $request)
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
