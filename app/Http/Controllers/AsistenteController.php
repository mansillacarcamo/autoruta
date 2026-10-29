<?php

namespace App\Http\Controllers;

use Anthropic\Beta\Messages\BetaToolUseBlock;
use Anthropic\Client;
use Anthropic\Core\Exceptions\APIException;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

// Asistente virtual: el visitante escribe qué auto busca y Claude lo busca en los avisos activos
// con la herramienta buscar_vehiculos. El historial vive en el navegador; aquí solo llega el texto.
class AsistenteController extends Controller
{
    private const MAX_RESULTADOS = 6;
    private const MAX_VUELTAS = 4;

    public function responder(Request $request)
    {
        $datos = $request->validate([
            'mensajes' => 'required|array|min:1|max:20',
            'mensajes.*.rol' => 'required|in:user,assistant',
            'mensajes.*.texto' => 'required|string|max:1000',
        ]);

        $mensajes = array_map(fn ($m) => ['role' => $m['rol'], 'content' => $m['texto']], $datos['mensajes']);
        if ($mensajes[0]['role'] !== 'user' || end($mensajes)['role'] !== 'user') {
            return response()->json(['error' => 'Conversación no válida.'], 422);
        }

        $cliente = new Client(apiKey: config('autoruta.asistente.api_key'));
        $encontrados = [];

        try {
            for ($vuelta = 0; $vuelta < self::MAX_VUELTAS; $vuelta++) {
                $respuesta = $cliente->beta->messages->create(
                    model: config('autoruta.asistente.modelo'),
                    maxTokens: 4096,
                    system: $this->instrucciones(),
                    messages: $mensajes,
                    tools: [$this->herramienta()],
                    outputConfig: ['effort' => 'low'],
                    fallbacks: 'default',
                    betas: ['server-side-fallback-2026-07-01'],
                );

                if ($respuesta->stopReason === 'refusal') {
                    return response()->json(['respuesta' => 'No puedo ayudarte con eso. Cuéntame qué vehículo buscas y lo busco en los avisos.', 'vehiculos' => []]);
                }
                if ($respuesta->stopReason !== 'tool_use') {
                    break;
                }

                $resultados = [];
                foreach ($respuesta->content as $bloque) {
                    if ($bloque instanceof BetaToolUseBlock) {
                        $vehiculos = $this->buscar((array) $bloque->input);
                        $encontrados = $vehiculos;
                        $resultados[] = [
                            'type' => 'tool_result',
                            'toolUseID' => $bloque->id,
                            'content' => json_encode($vehiculos->map(fn ($v) => $this->paraModelo($v))->values(), JSON_UNESCAPED_UNICODE),
                        ];
                    }
                }
                $mensajes[] = ['role' => 'assistant', 'content' => $respuesta->content];
                $mensajes[] = ['role' => 'user', 'content' => $resultados];
            }
        } catch (APIException $e) {
            Log::warning('Asistente: error de la API de Claude', ['mensaje' => $e->getMessage()]);

            return response()->json(['error' => 'El asistente no está disponible en este momento. Intenta de nuevo en un rato.'], 503);
        }

        $texto = collect($respuesta->content)->where('type', 'text')->pluck('text')->implode("\n");

        return response()->json([
            'respuesta' => trim($texto) ?: 'Encontré estas opciones para ti:',
            'vehiculos' => collect($encontrados)->map(fn ($v) => [
                'titulo' => "{$v->marca} {$v->modelo} {$v->anio}",
                'precio' => $v->precioFormateado(),
                'detalle' => number_format($v->kilometraje, 0, ',', '.') . ' km · ' . $v->comuna,
                'foto' => $v->primeraFotoUrl(),
                'url' => route('vehiculos.show', $v),
            ])->values(),
        ]);
    }

    private function instrucciones(): string
    {
        $tipos = implode(', ', array_keys(Vehiculo::ETIQUETA_TIPO));

        return <<<TXT
        Eres el asistente virtual de AutoRuta, un sitio chileno de avisos de autos usados (sobre todo Región de Los Lagos: Puerto Montt, Osorno, Puerto Varas, Chiloé).
        Tu trabajo es ayudar al visitante a encontrar el vehículo que busca entre los avisos publicados.

        - Cuando el visitante describa lo que busca, usa buscar_vehiculos con los filtros que se deduzcan de su mensaje. Si es muy vago, busca igual con lo que tengas y luego pregunta un dato (presupuesto, tipo o comuna) para afinar.
        - Tipos válidos: {$tipos}. "Camioneta" es camioneta; "jeep" o "4x4 familiar" suele ser suv.
        - Precios en pesos chilenos: "15 palos" o "15 millones" = 15000000.
        - Si la búsqueda no trae resultados, prueba una vez quitando el filtro menos importante y dilo.
        - Responde en español de Chile, cercano y breve (2 a 4 frases), en texto plano sin markdown ni listas. Los autos encontrados se muestran como tarjetas debajo de tu mensaje: no repitas toda su ficha, comenta lo más útil (rango de precios, cuál destaca).
        - Nunca inventes autos, precios ni datos que no vengan de buscar_vehiculos.
        - Solo hablas de buscar vehículos en AutoRuta. Para vender, dile que publicar es gratis creando su cuenta en el sitio.
        TXT;
    }

    private function herramienta(): array
    {
        return [
            'name' => 'buscar_vehiculos',
            'description' => 'Busca vehículos en los avisos activos de AutoRuta. Todos los filtros son opcionales; usa solo los que el visitante pidió. Devuelve hasta 6 avisos.',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'texto' => ['type' => 'string', 'description' => 'Marca y/o modelo, por ejemplo "toyota hilux" o "mazda 3".'],
                    'tipo' => ['type' => 'string', 'enum' => array_keys(Vehiculo::ETIQUETA_TIPO)],
                    'combustible' => ['type' => 'string', 'enum' => array_keys(Vehiculo::ETIQUETA_COMBUSTIBLE)],
                    'transmision' => ['type' => 'string', 'enum' => array_keys(Vehiculo::ETIQUETA_TRANSMISION)],
                    'traccion' => ['type' => 'string', 'enum' => array_keys(Vehiculo::ETIQUETA_TRACCION)],
                    'comuna' => ['type' => 'string', 'description' => 'Comuna, por ejemplo "Osorno".'],
                    'precio_min' => ['type' => 'integer', 'description' => 'Precio mínimo en pesos chilenos.'],
                    'precio_max' => ['type' => 'integer', 'description' => 'Precio máximo en pesos chilenos.'],
                    'anio_min' => ['type' => 'integer'],
                    'anio_max' => ['type' => 'integer'],
                    'km_max' => ['type' => 'integer', 'description' => 'Kilometraje máximo.'],
                    'orden' => ['type' => 'string', 'enum' => ['recientes', 'precio_asc', 'precio_desc', 'km_asc']],
                ],
            ],
        ];
    }

    private function buscar(array $filtros)
    {
        $query = Vehiculo::activos()->with('fotos');

        if ($texto = trim((string) ($filtros['texto'] ?? ''))) {
            foreach (preg_split('/\s+/u', $texto) as $palabra) {
                $query->where(fn ($w) => $w->where('marca', 'like', "%$palabra%")
                    ->orWhere('modelo', 'like', "%$palabra%")
                    ->orWhere('version', 'like', "%$palabra%"));
            }
        }
        foreach (['tipo' => Vehiculo::ETIQUETA_TIPO, 'combustible' => Vehiculo::ETIQUETA_COMBUSTIBLE,
            'transmision' => Vehiculo::ETIQUETA_TRANSMISION, 'traccion' => Vehiculo::ETIQUETA_TRACCION] as $campo => $validos) {
            if (isset($filtros[$campo]) && array_key_exists($filtros[$campo], $validos)) {
                $query->where($campo, $filtros[$campo]);
            }
        }
        if ($comuna = trim((string) ($filtros['comuna'] ?? ''))) {
            $query->where('comuna', 'like', "%$comuna%");
        }
        foreach (['precio_min' => ['precio', '>='], 'precio_max' => ['precio', '<='], 'anio_min' => ['anio', '>='],
            'anio_max' => ['anio', '<='], 'km_max' => ['kilometraje', '<=']] as $clave => [$columna, $operador]) {
            if (isset($filtros[$clave]) && is_numeric($filtros[$clave])) {
                $query->where($columna, $operador, (int) $filtros[$clave]);
            }
        }

        $query->premiumPrimero();
        match ($filtros['orden'] ?? 'recientes') {
            'precio_asc' => $query->orderBy('precio'),
            'precio_desc' => $query->orderByDesc('precio'),
            'km_asc' => $query->orderBy('kilometraje'),
            default => $query->orderByDesc('publicado_en'),
        };

        return $query->limit(self::MAX_RESULTADOS)->get();
    }

    private function paraModelo(Vehiculo $v): array
    {
        return array_filter([
            'marca' => $v->marca,
            'modelo' => $v->modelo,
            'version' => $v->version,
            'anio' => $v->anio,
            'precio' => $v->precioFormateado(),
            'kilometraje' => $v->kilometraje,
            'tipo' => Vehiculo::ETIQUETA_TIPO[$v->tipo] ?? $v->tipo,
            'combustible' => Vehiculo::ETIQUETA_COMBUSTIBLE[$v->combustible] ?? null,
            'transmision' => Vehiculo::ETIQUETA_TRANSMISION[$v->transmision] ?? null,
            'comuna' => $v->comuna,
        ], fn ($valor) => $valor !== null && $valor !== '');
    }
}
