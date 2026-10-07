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
            'indice' => 'required|integer|min:0|max:' . (\App\Support\SubidaPorTrozos::MAX_TROZOS - 1),
            'trozo' => 'required|file|max:1100',
        ]);

        \App\Models\ArchivoGuardado::updateOrCreate(
            ['ruta' => 'trozos/' . $datos['subida'] . '/' . $datos['indice']],
            ['mime' => 'application/octet-stream', 'contenido' => file_get_contents($request->file('trozo')->getRealPath())]
        );

        return response()->json(['ok' => true]);
    }

    public function subirBanner(Request $request, Anunciante $negocio)
    {
        if ($negocio->banners()->count() >= config('autoruta.max_banners_negocio')) {
            return back()->with('error', 'Máximo ' . config('autoruta.max_banners_negocio') . ' banners por negocio.');
        }

        \App\Support\SubidaPorTrozos::unir($request, ['archivo', 'alterno', 'movil']);

        $datos = $request->validate([
            'tipoMedio' => 'required|in:imagen,video',
            'posicion' => 'required|in:' . implode(',', array_keys(Anunciante::POSICIONES)),
            'linkUrl' => ['required', 'string', 'max:255', $this->reglaLink()],
            'archivo' => 'required|file|mimes:jpg,jpeg,png,webp,' . self::FORMATOS_VIDEO . '|max:20480',
            'logo' => 'nullable|file|mimes:png,jpg,jpeg,webp|max:5120',
            'alterno' => 'nullable|file|mimes:jpg,jpeg,png,webp,' . self::FORMATOS_VIDEO . '|max:20480',
            'movil' => 'nullable|file|mimes:jpg,jpeg,png,webp,' . self::FORMATOS_VIDEO . '|max:20480',
            'segundosPrincipal' => 'nullable|integer|min:1|max:60',
            'segundosAlterno' => 'nullable|integer|min:1|max:60',
        ], $this->mensajesArchivos());

        // El tipo se deduce del archivo, por si se sube un video dejando "Imagen" seleccionado.
        $tipo = in_array($request->file('archivo')->extension(), explode(',', self::FORMATOS_VIDEO), true) ? 'video' : 'imagen';
        $nombre = \App\Support\Archivos::guardar(
            $request->file('archivo'),
            'negocios',
            'neg' . $negocio->id . '_' . time() . '.' . $request->file('archivo')->extension()
        );

        // El logo solo se usa en banners de video (va encima del video).
        $logo = $tipo === 'video' && $request->hasFile('logo') ? $this->guardarLogo($request, $negocio) : null;

        $negocio->banners()->create([
            'tipo_medio' => $tipo,
            'archivo' => $nombre,
            'logo' => $logo,
            ...$this->guardarExtra($request, $negocio, 'alterno'),
            ...$this->guardarExtra($request, $negocio, 'movil'),
            'segundos_principal' => (int) ($datos['segundosPrincipal'] ?? 3),
            'segundos_alterno' => (int) ($datos['segundosAlterno'] ?? 3),
            'link_url' => AnuncianteBanner::normalizarLink($datos['linkUrl']),
            'posicion' => $datos['posicion'],
        ]);

        return back()->with('ok', 'Banner subido.');
    }

    public function actualizarBanner(Request $request, Anunciante $negocio, AnuncianteBanner $banner)
    {
        abort_unless((int) $banner->anunciante_id === (int) $negocio->id, 404);

        \App\Support\SubidaPorTrozos::unir($request, ['alterno', 'movil']);

        $datos = $request->validate([
            'linkUrl' => ['required', 'string', 'max:255', $this->reglaLink()],
            'posicion' => 'required|in:' . implode(',', array_keys(Anunciante::POSICIONES)),
            'logo' => 'nullable|file|mimes:png,jpg,jpeg,webp|max:5120',
            'quitarLogo' => 'nullable|boolean',
            'alterno' => 'nullable|file|mimes:jpg,jpeg,png,webp,' . self::FORMATOS_VIDEO . '|max:20480',
            'movil' => 'nullable|file|mimes:jpg,jpeg,png,webp,' . self::FORMATOS_VIDEO . '|max:20480',
            'segundosPrincipal' => 'nullable|integer|min:1|max:60',
            'segundosAlterno' => 'nullable|integer|min:1|max:60',
            'quitarAlterno' => 'nullable|boolean',
            'quitarMovil' => 'nullable|boolean',
        ], $this->mensajesArchivos());

        $cambios = ['link_url' => AnuncianteBanner::normalizarLink($datos['linkUrl']), 'posicion' => $datos['posicion']];

        // Cambiar o quitar el logo del video (el anterior se borra del almacenamiento).
        if ($banner->tipo_medio === 'video' && ($request->hasFile('logo') || $request->boolean('quitarLogo'))) {
            if ($banner->logo) {
                \App\Support\Archivos::borrar('negocios/' . $banner->logo);
            }
            $cambios['logo'] = $request->hasFile('logo') ? $this->guardarLogo($request, $negocio) : null;
        }

        foreach (['segundosPrincipal' => 'segundos_principal', 'segundosAlterno' => 'segundos_alterno'] as $campo => $columna) {
            if (isset($datos[$campo])) {
                $cambios[$columna] = (int) $datos[$campo];
            }
        }

        // Cambiar o quitar el segundo archivo y la versión celular (el anterior se borra del almacenamiento).
        foreach (['alterno' => 'quitarAlterno', 'movil' => 'quitarMovil'] as $campo => $quitar) {
            if ($request->hasFile($campo) || $request->boolean($quitar)) {
                if ($banner->{'archivo_' . $campo}) {
                    \App\Support\Archivos::borrar('negocios/' . $banner->{'archivo_' . $campo});
                }
                $cambios = array_merge($cambios, ['archivo_' . $campo => null, 'tipo_' . $campo => null], $this->guardarExtra($request, $negocio, $campo));
            }
        }

        $banner->update($cambios);

        return back()->with('ok', 'Banner actualizado.');
    }

    private function mensajesArchivos(): array
    {
        return [
            'archivo.mimes' => 'Formato no permitido. Imágenes: JPG, PNG o WEBP. Videos: MP4, MOV, M4V, WEBM u OGG (si es AVI, WMV o MKV, conviértelo a MP4).',
            'archivo.max' => 'El archivo no puede pesar más de 20 MB.',
            'archivo.uploaded' => 'El archivo no se pudo subir: pesa más de lo que permite el servidor. Prueba con uno más liviano.',
            'alterno.mimes' => 'El segundo archivo debe ser imagen (JPG, PNG, WEBP) o video (MP4, MOV, M4V, WEBM, OGG).',
            'alterno.max' => 'El segundo archivo no puede pesar más de 20 MB.',
            'alterno.uploaded' => 'El segundo archivo no se pudo subir: pesa más de lo que permite el servidor. Prueba con uno más liviano.',
            'movil.mimes' => 'La versión celular debe ser imagen (JPG, PNG, WEBP) o video (MP4, MOV, M4V, WEBM, OGG).',
            'movil.max' => 'La versión celular no puede pesar más de 20 MB.',
            'movil.uploaded' => 'La versión celular no se pudo subir: pesa más de lo que permite el servidor. Prueba con una más liviana.',
            'logo.mimes' => 'El logo debe ser PNG, JPG o WEBP (ideal PNG con fondo transparente).',
            'logo.max' => 'El logo no puede pesar más de 5 MB.',
            'logo.uploaded' => 'El logo no se pudo subir: pesa más de lo que permite el servidor. Prueba con uno más liviano.',
        ];
    }

    // Guarda un archivo adicional del banner ("alterno" = segundo archivo, "movil" = versión celular)
    // si viene en la petición, y devuelve sus columnas archivo_* y tipo_*.
    private function guardarExtra(Request $request, Anunciante $negocio, string $campo): array
    {
        if (! $request->hasFile($campo)) {
            return [];
        }
        $archivo = $request->file($campo);

        return [
            'archivo_' . $campo => \App\Support\Archivos::guardar($archivo, 'negocios', 'neg' . $negocio->id . '_' . time() . '_' . $campo . '.' . $archivo->extension()),
            'tipo_' . $campo => in_array($archivo->extension(), explode(',', self::FORMATOS_VIDEO), true) ? 'video' : 'imagen',
        ];
    }

    private function guardarLogo(Request $request, Anunciante $negocio): string
    {
        return \App\Support\Archivos::guardar(
            $request->file('logo'),
            'negocios',
            'neg' . $negocio->id . '_' . time() . '_logo.' . $request->file('logo')->extension()
        );
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
        foreach ([$banner->logo, $banner->archivo_alterno, $banner->archivo_movil] as $extra) {
            if ($extra) {
                \App\Support\Archivos::borrar('negocios/' . $extra);
            }
        }
        $banner->delete();

        return back()->with('ok', 'Banner eliminado.');
    }
}
