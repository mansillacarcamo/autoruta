<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use App\Support\Archivos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

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

        return view('panel.publicar', ['vehiculo' => null]);
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
            return $this->formularioVacio($request);
        }

        $datos = $this->validar($request, null);

        $vehiculo = Vehiculo::create($this->camposVehiculo($datos) + [
            'user_id' => $usuario->id,
            'estado' => 'activa',
            'publicado_en' => now(),
            'vence_en' => now()->addDays(config('autoruta.duracion_publicacion_dias')),
        ]);

        $this->guardarFotos($vehiculo, $request->file('fotos', []));
        $this->actualizarContacto($request, $datos);

        return $this->responder($request, 'Vehículo publicado.');
    }

    public function editar(Request $request, Vehiculo $vehiculo)
    {
        abort_unless($vehiculo->user_id === $request->user()->id, 403);
        $vehiculo->load('fotos');

        return view('panel.publicar', compact('vehiculo'));
    }

    public function actualizar(Request $request, Vehiculo $vehiculo)
    {
        abort_unless($vehiculo->user_id === $request->user()->id, 403);

        if (! $request->has('tipo')) {
            return $this->formularioVacio($request);
        }

        $datos = $this->validar($request, $vehiculo);

        $idsAQuitar = array_map('intval', $datos['fotosEliminar'] ?? []);
        $fotosQuedan = $vehiculo->fotos->reject(fn ($f) => in_array($f->id, $idsAQuitar, true));
        $nuevas = $request->file('fotos', []);
        $total = $fotosQuedan->count() + count($nuevas);

        if ($total < 1) {
            throw ValidationException::withMessages(['fotos' => 'El aviso debe tener al menos una foto.']);
        }
        if ($total > config('autoruta.max_fotos_vehiculo')) {
            throw ValidationException::withMessages(['fotos' => 'El aviso puede tener como máximo ' . config('autoruta.max_fotos_vehiculo') . ' fotos en total.']);
        }

        $vehiculo->update($this->camposVehiculo($datos));

        foreach ($vehiculo->fotos->whereIn('id', $idsAQuitar) as $foto) {
            Archivos::borrar('vehiculos/' . $foto->archivo);
            $foto->delete();
        }
        $fotosQuedan->values()->each(fn ($f, $i) => $f->update(['orden' => $i]));
        $this->guardarFotos($vehiculo, $nuevas, $fotosQuedan->count());
        $this->actualizarContacto($request, $datos);

        return $this->responder($request, 'Publicación actualizada.');
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
            Archivos::borrar('vehiculos/' . $foto->archivo);
        }
        $vehiculo->delete();

        return back();
    }

    private function validar(Request $request, ?Vehiculo $vehiculo): array
    {
        $request->merge([
            'precio' => preg_replace('/\D/', '', (string) $request->input('precio')),
            'kilometraje' => preg_replace('/\D/', '', (string) $request->input('kilometraje')),
        ]);

        return $request->validate([
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
            'puertas' => 'nullable|integer|min:2|max:6',
            'traccion' => 'nullable|in:4x2,4x4,awd',
            'duenosAnteriores' => 'nullable|integer|min:0|max:20',
            'fotos' => ($vehiculo ? 'nullable' : 'required') . '|array|max:' . config('autoruta.max_fotos_vehiculo'),
            'fotos.*' => 'image|max:10240',
            'fotosEliminar' => 'nullable|array',
            'fotosEliminar.*' => 'integer',
        ], [
            'fotos.required' => 'Agrega al menos una foto del vehículo.',
            'fotos.max' => 'Puedes subir como máximo ' . config('autoruta.max_fotos_vehiculo') . ' fotos.',
            'fotos.*.image' => 'Uno de los archivos no es una imagen válida (usa JPG o PNG).',
            'fotos.*.max' => 'Cada foto puede pesar como máximo 10 MB.',
        ]);
    }

    private function camposVehiculo(array $datos): array
    {
        return [
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
            'puertas' => $datos['puertas'] ?? null,
            'traccion' => $datos['traccion'] ?? null,
            'duenos_anteriores' => $datos['duenosAnteriores'] ?? null,
        ];
    }

    private function guardarFotos(Vehiculo $vehiculo, array $fotos, int $ordenInicial = 0): void
    {
        foreach (array_values($fotos) as $i => $foto) {
            $orden = $ordenInicial + $i;
            $nombre = Archivos::guardar($foto, 'vehiculos', "veh{$vehiculo->id}_{$orden}_" . time() . '.' . $foto->extension());
            $vehiculo->fotos()->create(['archivo' => $nombre, 'orden' => $orden]);
        }
    }

    private function actualizarContacto(Request $request, array $datos): void
    {
        $request->user()->update([
            'telefono_whatsapp' => $datos['telefonoWhatsapp'],
            'region' => $datos['region'],
            'comuna' => $datos['comuna'],
        ]);
    }

    private function responder(Request $request, string $mensaje)
    {
        if ($request->expectsJson()) {
            session()->flash('ok', $mensaje);

            return response()->json(['redirect' => route('panel')]);
        }

        return redirect()->route('panel')->with('ok', $mensaje);
    }

    // Cuando el formulario llega sin datos se muestra un diagnóstico técnico en pantalla,
    // para poder identificar el problema desde una captura del usuario.
    private function formularioVacio(Request $request)
    {
        $diagnostico = [
            'content_length' => $request->server('CONTENT_LENGTH') ?? $request->header('Content-Length'),
            'content_type' => $request->header('Content-Type'),
            'transfer_encoding' => $request->header('Transfer-Encoding'),
            'campos' => array_keys($request->except('_token')),
            'archivos' => count($request->allFiles()),
            'post_max_size' => ini_get('post_max_size'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'servidor' => $request->server('SERVER_SOFTWARE') ?: PHP_SAPI,
            'user_agent' => $request->userAgent(),
        ];
        Log::warning('Publicar: formulario llegó vacío', $diagnostico);

        $mensaje = sprintf(
            'El servidor no recibió los datos del formulario. Envía una captura de este mensaje a soporte. [llegaron %s bytes, tipo %s, campos: %s, archivos: %d, límite envío %s, límite archivo %s, %s]',
            $diagnostico['content_length'] ?? '?',
            strtok((string) $diagnostico['content_type'], ';') ?: '?',
            implode(',', $diagnostico['campos']) ?: 'ninguno',
            $diagnostico['archivos'],
            $diagnostico['post_max_size'],
            $diagnostico['upload_max_filesize'],
            $diagnostico['servidor'],
        );

        if ($request->expectsJson()) {
            return response()->json(['message' => $mensaje, 'errors' => ['servidor' => [$mensaje]]], 422);
        }

        return back()->with('error', $mensaje);
    }
}
