<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = User::withCount('vehiculos')->orderByDesc('created_at')->get();

        return view('admin.usuarios.index', compact('usuarios'));
    }

    // El sitio no envía correos, así que el admin genera una contraseña temporal y se la
    // hace llegar al cliente por WhatsApp.
    public function restablecerClave(User $usuario)
    {
        $clave = Str::password(10, symbols: false);
        $usuario->update(['password' => Hash::make($clave)]);

        return back()->with('clave_temporal', [
            'nombre' => $usuario->name,
            'clave' => $clave,
            'whatsapp' => preg_replace('/\D/', '', (string) $usuario->telefono_whatsapp),
        ]);
    }
}
