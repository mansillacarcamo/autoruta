<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anunciante;
use App\Models\AnuncianteBanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class NegocioController extends Controller
{
    // Formatos de video que los navegadores reproducen (MOV/M4V son los de iPhone). AVI, WMV o MKV no se
    // pueden mostrar en una página web, por eso se piden convertidos a MP4.
    private const FORMATOS_VIDEO = 'mp4,m4v,mov,qt,webm,ogv,ogg';

    public function index()
    {
        $negocios = Anunciante::withCount('banners')->withSum('banners', 'clics')->orderByDesc('created_at')->get();
        $precioActual = (int) (DB::table('configuracion_sitio')->where('clave', 'precio_publicidad_negocio')->value('valor') ?? 15000);

        return view('admin.negocios.index', compact('negocios', 'precioActual'));
    }

    public function actualizarPrecio(Request $request)
    {
        $datos = $request->validate(['precioPublicidad' => 'required|integer|min:0']);

        DB::table('configuracion_sitio')->updateOrInsert(
            ['clave' => 'precio_publicidad_negocio'],
            ['valor' => (string) $datos['precioPublicidad']]
        );

        return back()->with('ok', 'Precio actualizado.');
    }

    public function crear()
    {
        return view('admin.negocios.crear');
    }

    public function guardar(Request $request)
    {
        $datos = $request->validate([
            'nombreNegocio' => 'required|string|max:120',
            'rubro' => 'required|in:financiera,taller,otro',
            'descripcion' => 'nullable|string|max:300',
            'telefonoWhatsapp' => 'nullable|string|max:20',
            'sitioWeb' => 'nullable|string|max:255',
            'estado' => 'required|in:activo,pausado',
        ]);

        $negocio = Anunciante::create([
            'nombre_negocio' => $datos['nombreNegocio'],
            'rubro' => $datos['rubro'],
            'descripcion' => $datos['descripcion'] ?? '',
            'telefono_whatsapp' => $datos['telefonoWhatsapp'] ?? null,
            'sitio_web' => $datos['sitioWeb'] ?? null,
            'estado' => $datos['estado'],
            'publicado_en' => $datos['estado'] === 'activo' ? now() : null,
        ]);

        return redirect()->route('admin.negocios.editar', $negocio)->with('ok', 'Negocio creado.');
    }

    public function editar(Anunciante $negocio)
    {
        $negocio->load('banners');

        return view('admin.negocios.editar', compact('negocio'));
    }

    public function actualizar(Request $request, Anunciante $negocio)
    {
        $datos = $request->validate([
            'nombreNegocio' => 'required|string|max:120',
            'rubro' => 'required|in:financiera,taller,otro',
            'descripcion' => 'nullable|string|max:300',
            'telefonoWhatsapp' => 'nullable|string|max:20',
            'sitioWeb' => 'nullable|string|max:255',
            'estado' => 'required|in:activo,pausado',
        ]);

        $seActivaAhora = $datos['estado'] === 'activo' && $negocio->estado !== 'activo';

        $negocio->update([
            'nombre_negocio' => $datos['nombreNegocio'],
            'rubro' => $datos['rubro'],
            'descripcion' => $datos['descripcion'] ?? '',
            'telefono_whatsapp' => $datos['telefonoWhatsapp'] ?? null,
            'sitio_web' => $datos['sitioWeb'] ?? null,
            'estado' => $datos['estado'],
            ...($seActivaAhora ? ['publicado_en' => now(), 'vence_en' => now()->addDays(30)] : []),
        ]);

        return redirect()->route('admin.negocios.editar', $negocio)->with('ok', 'Cambios guardados.');
    }

    // Los archivos grandes se suben en trozos de 1 MB porque el servidor rechaza archivos grandes en una sola
    // petición (upload_max_filesize). Los trozos se guardan en la base de datos para que funcione
    // aunque haya varias instancias del servidor, y al guardar el formulario se vuelven a unir.
    public function subirTrozo(Request $request)
    {
        $datos = $request->validate([
            'subida' => 'required|uuid',
            'indice' => 'required|integer|min:0|max:' . (self::MAX_TROZOS - 1),
            'trozo' => 'required|file|max:1100',
        ]);

        \App\Models\ArchivoGuardado::updateOrCreate(
            ['ruta' => 'trozos/' . $datos['subida'] . '/' . $datos['indice']],
            ['mime' => 'application/octet-stream', 'contenido' => file_get_contents($request->file('trozo')->getRealPath())]
        );

        return response()->json(['ok' => true]);
    }

    private const MAX_TROZOS = 40;

    // Si el formulario trae un archivo subido por trozos ({campo}_subida, {campo}_total, {campo}_nombre),
    // lo une y lo deja en la petición como si se hubiera subido normal, para validarlo y guardarlo igual.
    private function unirTrozos(Request $request, array $campos): void
    {
        foreach ($campos as $campo) {
            $subida = (string) $request->input($campo . '_subida');
            $total = (int) $request->input($campo . '_total');
            if (! \Illuminate\Support\Str::isUuid($subida) || $total < 1 || $total > self::MAX_TROZOS) {
                continue;
            }

            $rutas = array_map(fn ($i) => 'trozos/' . $subida . '/' . $i, range(0, $total - 1));
            $trozos = \App\Models\ArchivoGuardado::whereIn('ruta', $rutas)->get()->keyBy('ruta');
            if ($trozos->count() !== $total) {
                throw \Illuminate\Validation\ValidationException::withMessages([$campo => 'El video no terminó de subirse. Inténtalo de nuevo.']);
            }

            $temporal = tempnam(sys_get_temp_dir(), 'trozos');
            $destino = fopen($temporal, 'wb');
            foreach ($rutas as $ruta) {
                fwrite($destino, $trozos[$ruta]->contenido);
            }
            fclose($destino);
            \App\Models\ArchivoGuardado::whereIn('ruta', $rutas)->delete();
            // Limpia trozos de subidas abandonadas.
            \App\Models\ArchivoGuardado::where('ruta', 'like', 'trozos/%')->where('created_at', '<', now()->subDay())->delete();

            $nombre = basename((string) $request->input($campo . '_nombre')) ?: 'video.mp4';
            $request->files->set($campo, new \Illuminate\Http\UploadedFile($temporal, $nombre, null, UPLOAD_ERR_OK, true));
        }
    }

    public function subirBanner(Request $request, Anunciante $negocio)
    {
        if ($negocio->banners()->count() >= config('autoruta.max_banners_negocio')) {
            return back()->with('error', 'Máximo ' . config('autoruta.max_banners_negocio') . ' banners por negocio.');
        }

        $this->unirTrozos($request, ['archivo']);

        $datos = $request->validate([
            'tipoMedio' => 'required|in:imagen,video',
            'posicion' => 'required|in:' . implode(',', array_keys(Anunciante::POSICIONES)),
            'linkUrl' => ['required', 'string', 'max:255', $this->reglaLink()],
            'archivo' => 'required|file|mimes:jpg,jpeg,png,webp,' . self::FORMATOS_VIDEO . '|max:20480',
        ], $this->mensajesArchivos());

        // El tipo se deduce del archivo, por si se sube un video dejando "Imagen" seleccionado.
        $tipo = in_array($request->file('archivo')->extension(), explode(',', self::FORMATOS_VIDEO), true) ? 'video' : 'imagen';
        $nombre = \App\Support\Archivos::guardar(
            $request->file('archivo'),
            'negocios',
            'neg' . $negocio->id . '_' . time() . '.' . $request->file('archivo')->extension()
        );

        $negocio->banners()->create([
            'tipo_medio' => $tipo,
            'archivo' => $nombre,
            'link_url' => AnuncianteBanner::normalizarLink($datos['linkUrl']),
            'posicion' => $datos['posicion'],
        ]);

        return back()->with('ok', 'Banner subido.');
    }

    public function actualizarBanner(Request $request, Anunciante $negocio, AnuncianteBanner $banner)
    {
        abort_unless((int) $banner->anunciante_id === (int) $negocio->id, 404);

        $datos = $request->validate([
            'linkUrl' => ['required', 'string', 'max:255', $this->reglaLink()],
            'posicion' => 'required|in:' . implode(',', array_keys(Anunciante::POSICIONES)),
        ]);

        $banner->update(['link_url' => AnuncianteBanner::normalizarLink($datos['linkUrl']), 'posicion' => $datos['posicion']]);

        return back()->with('ok', 'Banner actualizado.');
    }

    private function mensajesArchivos(): array
    {
        return [
            'archivo.mimes' => 'Formato no permitido. Imágenes: JPG, PNG o WEBP. Videos: MP4, MOV, M4V, WEBM u OGG (si es AVI, WMV o MKV, conviértelo a MP4).',
            'archivo.max' => 'El archivo no puede pesar más de 20 MB.',
            'archivo.uploaded' => 'El archivo no se pudo subir: pesa más de lo que permite el servidor. Prueba con uno más liviano.',
        ];
    }

    private function reglaLink(): \Closure
    {
        return function (string $atributo, mixed $valor, \Closure $falla) {
            if (! AnuncianteBanner::normalizarLink($valor)) {
                $falla('El link no es válido. Ejemplo: https://www.minegocio.cl o https://wa.me/56912345678');
            }
        };
    }

    public function eliminarBanner(Anunciante $negocio, \App\Models\AnuncianteBanner $banner)
    {
        abort_unless($banner->anunciante_id === $negocio->id, 404);
        \App\Support\Archivos::borrar('negocios/' . $banner->archivo);
        $banner->delete();

        return back()->with('ok', 'Banner eliminado.');
    }
}
