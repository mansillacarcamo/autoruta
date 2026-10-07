<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnuncianteBanner;
use App\Models\PortadaMedio;
use App\Support\Archivos;
use App\Support\SubidaPorTrozos;
use Illuminate\Http\Request;

// Slider del banner principal del inicio: el banner de AutoRuta va siempre primero y aquí se agregan
// hasta 3 banners más (imagen o video), cada uno con link y versión celular opcionales.
class PortadaController extends Controller
{
    public const MAX_MEDIOS = 3;

    private const FORMATOS_VIDEO = 'mp4,m4v,mov,qt,webm,ogv,ogg';

    public function index()
    {
        $medios = PortadaMedio::orderBy('orden')->orderBy('id')->get();
        $maxMedios = self::MAX_MEDIOS;

        return view('admin.portada.index', compact('medios', 'maxMedios'));
    }

    public function subir(Request $request)
    {
        if (PortadaMedio::count() >= self::MAX_MEDIOS) {
            return back()->with('error', 'Máximo ' . self::MAX_MEDIOS . ' banners en el slider.');
        }

        SubidaPorTrozos::unir($request, ['archivo']);

        $datos = $request->validate([
            'archivo' => 'required|file|mimes:jpg,jpeg,png,webp,' . self::FORMATOS_VIDEO . '|max:20480',
            'movil' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:10240',
            'linkUrl' => ['nullable', 'string', 'max:255', $this->reglaLink()],
        ], $this->mensajes());

        $archivo = $request->file('archivo');
        $esVideo = in_array($archivo->extension(), explode(',', self::FORMATOS_VIDEO), true);

        PortadaMedio::create([
            'tipo_medio' => $esVideo ? 'video' : 'imagen',
            'archivo' => Archivos::guardar($archivo, 'portada', 'portada_' . time() . '.' . $archivo->extension()),
            'archivo_movil' => $request->hasFile('movil') ? $this->guardarMovil($request) : null,
            'link_url' => AnuncianteBanner::normalizarLink($datos['linkUrl'] ?? null),
            'orden' => (int) PortadaMedio::max('orden') + 1,
        ]);

        return back()->with('ok', 'Banner agregado al slider.');
    }

    public function actualizar(Request $request, PortadaMedio $medio)
    {
        $datos = $request->validate([
            'linkUrl' => ['nullable', 'string', 'max:255', $this->reglaLink()],
            'movil' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:10240',
            'quitarMovil' => 'nullable|boolean',
        ], $this->mensajes());

        $cambios = ['link_url' => AnuncianteBanner::normalizarLink($datos['linkUrl'] ?? null)];

        if ($request->hasFile('movil') || $request->boolean('quitarMovil')) {
            if ($medio->archivo_movil) {
                Archivos::borrar('portada/' . $medio->archivo_movil);
            }
            $cambios['archivo_movil'] = $request->hasFile('movil') ? $this->guardarMovil($request) : null;
        }

        $medio->update($cambios);

        return back()->with('ok', 'Banner actualizado.');
    }

    public function eliminar(PortadaMedio $medio)
    {
        Archivos::borrar('portada/' . $medio->archivo);
        if ($medio->archivo_movil) {
            Archivos::borrar('portada/' . $medio->archivo_movil);
        }
        $medio->delete();

        return back()->with('ok', 'Banner eliminado.');
    }

    private function guardarMovil(Request $request): string
    {
        $movil = $request->file('movil');

        return Archivos::guardar($movil, 'portada', 'portada_' . time() . '_movil.' . $movil->extension());
    }

    // El link es opcional; si viene, se valida igual que en los banners de los negocios.
    private function reglaLink(): \Closure
    {
        return function (string $atributo, mixed $valor, \Closure $falla) {
            if (trim((string) $valor) !== '' && ! AnuncianteBanner::normalizarLink($valor)) {
                $falla('El link no es válido. Ejemplo: https://www.minegocio.cl o https://wa.me/56912345678');
            }
        };
    }

    private function mensajes(): array
    {
        return [
            'archivo.mimes' => 'Formato no permitido. Imágenes: JPG, PNG o WEBP. Videos: MP4, MOV, M4V, WEBM u OGG.',
            'archivo.max' => 'El archivo no puede pesar más de 20 MB.',
            'archivo.uploaded' => 'El archivo no se pudo subir: pesa más de lo que permite el servidor. Prueba con uno más liviano.',
            'movil.mimes' => 'La versión celular debe ser una imagen JPG, PNG o WEBP.',
            'movil.max' => 'La versión celular no puede pesar más de 10 MB.',
        ];
    }
}
