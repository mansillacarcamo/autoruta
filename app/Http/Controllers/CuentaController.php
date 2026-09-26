<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Archivos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CuentaController extends Controller
{
    public function editar(Request $request)
    {
        return view('panel.cuenta', ['usuario' => $request->user()]);
    }

    public function actualizarDatos(Request $request)
    {
        $usuario = $request->user();

        $datos = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($usuario->id)],
            'telefono' => ['required', 'string', 'max:20', 'regex:/^[+\d\s]{8,20}$/'],
            'ciudad' => ['required', 'string', 'max:80'],
        ]);

        $usuario->update([
            'name' => $datos['name'],
            'email' => $datos['email'],
            'telefono_whatsapp' => preg_replace('/\s+/', '', $datos['telefono']),
            'comuna' => $datos['ciudad'],
        ]);

        return back()->with('ok', 'Tus datos se guardaron.');
    }

    public function actualizarClave(Request $request)
    {
        $request->validateWithBag('clave', [
            'clave_actual' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'clave_actual.current_password' => 'La contraseña actual no es correcta.',
        ], [
            'clave_actual' => 'contraseña actual',
            'password' => 'nueva contraseña',
        ]);

        $request->user()->update(['password' => Hash::make($request->input('password'))]);

        return back()->with('ok', 'Tu contraseña se cambió correctamente.');
    }

    public function actualizarLogo(Request $request)
    {
        $usuario = $request->user();

        $datos = $request->validateWithBag('logo', [
            'nombre_comercial' => ['nullable', 'string', 'max:80'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'logo.mimes' => 'El logo debe ser JPG, PNG o WEBP.',
            'logo.image' => 'El archivo del logo no es una imagen válida.',
            'logo.max' => 'El logo puede pesar como máximo 5 MB.',
        ], [
            'nombre_comercial' => 'nombre de la automotora',
        ]);

        $cambios = [
            'nombre_comercial' => $datos['nombre_comercial'] ?? null,
            'mostrar_logo' => $request->boolean('mostrar_logo'),
        ];

        if ($request->hasFile('logo')) {
            $archivo = $request->file('logo');
            [$contenido, $mime, $extension] = function_exists('imagecreatefromstring')
                ? [$this->redimensionarLogo($archivo->getRealPath()), 'image/png', 'png']
                : [file_get_contents($archivo->getRealPath()), $archivo->getMimeType(), $archivo->extension()];
            if (! $contenido) {
                return back()->withErrors(['logo' => 'No se pudo procesar la imagen del logo. Prueba con otro archivo.'], 'logo');
            }
            if ($usuario->logo) {
                Archivos::borrar('logos/' . $usuario->logo);
            }
            $nombre = 'logo_' . $usuario->id . '_' . time() . '.' . $extension;
            Archivos::guardarContenido($contenido, 'logos/' . $nombre, $mime);
            $cambios['logo'] = $nombre;
        }

        $usuario->update($cambios);

        $mensaje = 'Tu logo y nombre de automotora se guardaron.';
        if ($cambios['mostrar_logo'] && ! $usuario->logo) {
            $mensaje .= ' Sube un logo para que aparezca en tus avisos.';
        }

        return back()->with('ok', $mensaje);
    }

    public function quitarLogo(Request $request)
    {
        $usuario = $request->user();
        if ($usuario->logo) {
            Archivos::borrar('logos/' . $usuario->logo);
        }
        $usuario->update(['logo' => null, 'mostrar_logo' => false]);

        return back()->with('ok', 'Logo eliminado.');
    }

    // Deja el logo en PNG de máximo 400 px conservando la transparencia.
    private function redimensionarLogo(string $ruta): ?string
    {
        $original = @imagecreatefromstring((string) file_get_contents($ruta));
        if (! $original) {
            return null;
        }

        $ancho = imagesx($original);
        $alto = imagesy($original);
        $escala = min(1, 400 / max($ancho, $alto));
        $nuevoAncho = max(1, (int) round($ancho * $escala));
        $nuevoAlto = max(1, (int) round($alto * $escala));

        $lienzo = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
        imagealphablending($lienzo, false);
        imagesavealpha($lienzo, true);
        imagefill($lienzo, 0, 0, imagecolorallocatealpha($lienzo, 0, 0, 0, 127));
        imagecopyresampled($lienzo, $original, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

        ob_start();
        imagepng($lienzo, null, 8);

        return ob_get_clean() ?: null;
    }
}
