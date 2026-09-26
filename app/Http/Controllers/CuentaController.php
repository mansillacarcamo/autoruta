<?php

namespace App\Http\Controllers;

use App\Models\User;
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
}
