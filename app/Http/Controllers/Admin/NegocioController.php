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

    public function subirBanner(Request $request, Anunciante $negocio)
    {
        if ($negocio->banners()->count() >= config('autoruta.max_banners_negocio')) {
            return back()->with('error', 'Máximo ' . config('autoruta.max_banners_negocio') . ' banners por negocio.');
        }

        $datos = $request->validate([
            'tipoMedio' => 'required|in:imagen,video',
            'posicion' => 'required|in:' . implode(',', array_keys(Anunciante::POSICIONES)),
            'linkUrl' => ['required', 'string', 'max:255', $this->reglaLink()],
            'archivo' => 'required|file|mimes:jpg,jpeg,png,webp,' . self::FORMATOS_VIDEO . '|max:20480',
            'capa' => 'nullable|file|mimes:png,webp|max:5120',
            'videoFondo' => 'nullable|file|mimes:' . self::FORMATOS_VIDEO . '|max:20480',
            'opacidadCapa' => 'nullable|integer|min:30|max:100',
        ], $this->mensajesArchivos());

        // El tipo se deduce del archivo, por si se sube un video dejando "Imagen" seleccionado.
        $tipo = in_array($request->file('archivo')->extension(), explode(',', self::FORMATOS_VIDEO), true) ? 'video' : 'imagen';
        $nombre = \App\Support\Archivos::guardar(
            $request->file('archivo'),
            'negocios',
            'neg' . $negocio->id . '_' . time() . '.' . $request->file('archivo')->extension()
        );

        // Video con imagen encima: subido como video + capa, o como imagen + video de fondo.
        $capa = null;
        if ($tipo === 'video' && $request->hasFile('capa')) {
            $capa = $this->guardarCapa($request, $negocio);
        } elseif ($tipo === 'imagen' && $request->hasFile('videoFondo')) {
            [$tipo, $capa, $nombre] = ['video', $nombre, $this->guardarVideoFondo($request, $negocio)];
        }

        $negocio->banners()->create([
            'tipo_medio' => $tipo,
            'archivo' => $nombre,
            'archivo_capa' => $capa,
            'opacidad_capa' => (int) ($datos['opacidadCapa'] ?? 100),
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
            'capa' => 'nullable|file|mimes:png,webp|max:5120',
            'videoFondo' => 'nullable|file|mimes:' . self::FORMATOS_VIDEO . '|max:20480',
            'opacidadCapa' => 'nullable|integer|min:30|max:100',
            'quitarCapa' => 'nullable|boolean',
            'zonaVideo' => 'nullable|string|max:60',
        ], $this->mensajesArchivos());

        $cambios = ['link_url' => AnuncianteBanner::normalizarLink($datos['linkUrl']), 'posicion' => $datos['posicion']];

        if (isset($datos['opacidadCapa'])) {
            $cambios['opacidad_capa'] = (int) $datos['opacidadCapa'];
        }

        // Zona marcada en el editor del admin (vacía = video de fondo completo).
        if ($request->has('zonaVideo')) {
            $cambios['zona_video'] = AnuncianteBanner::normalizarZona($datos['zonaVideo'] ?? null);
        }

        // Banner de imagen al que se le agrega video de fondo: la imagen pasa a ir encima del video.
        if ($banner->tipo_medio === 'imagen' && $request->hasFile('videoFondo')) {
            $cambios += ['tipo_medio' => 'video', 'archivo_capa' => $banner->archivo, 'archivo' => $this->guardarVideoFondo($request, $negocio)];
        }

        // Reemplazar o quitar la capa del video (la anterior se borra del almacenamiento).
        if ($banner->tipo_medio === 'video' && ($request->hasFile('capa') || $request->boolean('quitarCapa'))) {
            if ($banner->archivo_capa) {
                \App\Support\Archivos::borrar('negocios/' . $banner->archivo_capa);
            }
            $cambios['archivo_capa'] = $request->hasFile('capa') ? $this->guardarCapa($request, $negocio) : null;
        }

        $banner->update($cambios);

        return back()->with('ok', 'Banner actualizado.');
    }

    private function mensajesArchivos(): array
    {
        $formatoVideo = 'Ese formato de video no se puede mostrar en la web. Usa MP4, MOV, M4V, WEBM u OGG (si es AVI, WMV o MKV, conviértelo a MP4).';
        $muyPesado = 'El archivo no se pudo subir: pesa más de lo que permite el servidor. Prueba con uno más liviano (idealmente menos de 10 MB).';

        return [
            'videoFondo.mimes' => $formatoVideo,
            'archivo.mimes' => 'Formato no permitido. Imágenes: JPG, PNG o WEBP. Videos: MP4, MOV, M4V, WEBM u OGG.',
            'videoFondo.max' => 'El video de fondo no puede pesar más de 20 MB.',
            'archivo.max' => 'El archivo no puede pesar más de 20 MB.',
            'videoFondo.uploaded' => $muyPesado,
            'archivo.uploaded' => $muyPesado,
            'capa.uploaded' => $muyPesado,
        ];
    }

    private function guardarVideoFondo(Request $request, Anunciante $negocio): string
    {
        return \App\Support\Archivos::guardar(
            $request->file('videoFondo'),
            'negocios',
            'neg' . $negocio->id . '_' . time() . '_fondo.' . $request->file('videoFondo')->extension()
        );
    }

    private function guardarCapa(Request $request, Anunciante $negocio): string
    {
        return \App\Support\Archivos::guardar(
            $request->file('capa'),
            'negocios',
            'neg' . $negocio->id . '_' . time() . '_capa.' . $request->file('capa')->extension()
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
        if ($banner->archivo_capa) {
            \App\Support\Archivos::borrar('negocios/' . $banner->archivo_capa);
        }
        $banner->delete();

        return back()->with('ok', 'Banner eliminado.');
    }
}
