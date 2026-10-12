<?php

namespace App\Http\Controllers;

use App\Models\AnuncianteBanner;
use App\Models\Vehiculo;
use App\Support\SeoLocal;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class VehiculoController extends Controller
{
    // Nombre en plural de cada tipo para los títulos ("Camionetas en venta en Osorno").
    private const TIPO_PLURAL = [
        'auto' => 'Autos', 'citycar' => 'City cars', 'hatchback' => 'Hatchbacks', 'camioneta' => 'Camionetas',
        'suv' => 'SUV', 'moto' => 'Motos', 'camion' => 'Camiones', 'bus' => 'Buses',
        'maquinaria' => 'Maquinaria agrícola', 'otro' => 'Vehículos',
    ];

    public function index(Request $request)
    {
        return $this->listar($request);
    }

    // Página de una comuna de Los Lagos (o de toda la región): el mismo listado con título y texto propios.
    public function porLugar(Request $request, string $lugar)
    {
        if ($lugar === SeoLocal::SLUG_REGION) {
            return $this->listar($request, ['region' => SeoLocal::REGION], $lugar);
        }

        $comuna = SeoLocal::comunaDesdeSlug($lugar) ?? abort(404);

        return $this->listar($request, ['region' => SeoLocal::REGION, 'comuna' => $comuna], $lugar);
    }

    // $fijos: filtros que vienen de la URL de la página por comuna en vez de la consulta (?region=...).
    private function listar(Request $request, array $fijos = [], ?string $lugarSlug = null)
    {
        $orden = $request->string('orden')->toString();

        if (in_array($orden, ['precio_asc', 'precio_desc', 'km_asc'], true)) {
            $query = $this->filtrar(Vehiculo::activos()->with(['fotos', 'usuario']), $request, $fijos)->premiumPrimero();
            match ($orden) {
                'precio_asc' => $query->orderBy('precio'),
                'precio_desc' => $query->orderByDesc('precio'),
                'km_asc' => $query->orderBy('kilometraje'),
            };
            $vehiculos = $query->paginate(24)->withQueryString();
        } else {
            // Orden por defecto: más recientes, intercalando automotoras.
            $ids = Vehiculo::idsIntercalados($this->filtrar(Vehiculo::activos(), $request, $fijos));
            $pagina = LengthAwarePaginator::resolveCurrentPage();
            $idsPagina = array_slice($ids, ($pagina - 1) * 24, 24);
            $items = Vehiculo::with(['fotos', 'usuario'])->whereIn('id', $idsPagina)->get()
                ->sortBy(fn ($v) => array_search($v->id, $idsPagina))->values();
            $vehiculos = (new LengthAwarePaginator($items, count($ids), 24, $pagina, [
                'path' => $request->url(),
            ]))->withQueryString();
        }

        $filtroRegion = $fijos['region'] ?? $request->string('region')->toString();
        $filtroComuna = $fijos['comuna'] ?? $request->string('comuna')->toString();
        $seo = $this->seoListado($request, $vehiculos, $filtroRegion, $filtroComuna, $lugarSlug);

        return view('vehiculos.index', compact('vehiculos', 'filtroRegion', 'filtroComuna', 'seo'));
    }

    private function seoListado(Request $request, $vehiculos, string $region, string $comuna, ?string $lugarSlug): array
    {
        $tipo = $request->string('tipo')->toString();
        $plural = self::TIPO_PLURAL[$tipo] ?? null;
        $lugar = $comuna ?: ($region ? "la Región de $region" : null);

        $titulo = ($plural ? "$plural en venta" : 'Autos usados en venta') . ' en ' . ($lugar ?? 'Los Lagos y todo Chile');
        $descripcion = 'Encuentra ' . ($plural ? mb_strtolower($plural) : 'autos, camionetas, SUV y motos usados') . ' en venta en '
            . ($lugar ?? 'Puerto Montt, Osorno, Puerto Varas, Chiloé y todo Chile')
            . '. Compra directo al dueño o a la automotora, sin comisiones. Publica tu vehículo gratis en ' . config('autoruta.nombre_sitio') . '.';

        // Búsquedas libres, rangos de precio y otros órdenes no se indexan: son variaciones del mismo listado.
        $variacion = collect(['q', 'precioMin', 'precioMax', 'orden'])->contains(fn ($c) => $request->filled($c) && ! ($c === 'orden' && $request->orden === 'recientes'));

        // Canónica: /vehiculos?region=Los Lagos&comuna=Osorno apunta a /autos-usados/osorno.
        $extra = array_filter(['tipo' => $tipo ?: null, 'page' => $vehiculos->currentPage() > 1 ? $vehiculos->currentPage() : null]);
        if (! $lugarSlug && $region === SeoLocal::REGION) {
            $lugarSlug = $comuna ? (SeoLocal::comunaDesdeSlug(SeoLocal::slug($comuna)) ? SeoLocal::slug($comuna) : null) : SeoLocal::SLUG_REGION;
        }
        $canonical = $lugarSlug
            ? route('vehiculos.lugar', ['lugar' => $lugarSlug] + $extra)
            : route('vehiculos.index', array_filter(['region' => $region ?: null, 'comuna' => $comuna ?: null]) + $extra);

        return [
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'canonical' => $canonical,
            'robots' => $variacion ? 'noindex, follow' : 'index, follow',
            // Solo las páginas por comuna llevan título y texto de presentación propios.
            'lugar' => $request->route('lugar') ? $lugar : null,
        ];
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

    private function filtrar($query, Request $request, array $fijos = [])
    {
        if ($q = $request->string('q')->toString()) {
            $query->where(fn ($w) => $w->where('marca', 'like', "%$q%")->orWhere('modelo', 'like', "%$q%"));
        }
        foreach (['tipo', 'region', 'comuna'] as $campo) {
            if ($valor = $fijos[$campo] ?? $request->string($campo)->toString()) {
                $query->where($campo, $valor);
            }
        }
        if ($request->filled('precioMin')) $query->where('precio', '>=', (int) $request->precioMin);
        if ($request->filled('precioMax')) $query->where('precio', '<=', (int) $request->precioMax);

        return $query;
    }
}
