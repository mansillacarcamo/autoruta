<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anunciante;
use App\Models\User;
use App\Models\Vehiculo;
use Illuminate\Support\Facades\DB;

class ResumenController extends Controller
{
    public function index()
    {
        $estadisticas = [
            'visitas' => config('autoruta.visitas_inicio') + (int) DB::table('visitas')->where('id', 1)->value('total'),
            'vehiculos' => Vehiculo::activos()->count(),
            'usuarios' => User::count(),
            'negocios' => Anunciante::activos()->count(),
        ];

        $ultimosVehiculos = Vehiculo::with('usuario')->latest()->take(5)->get();
        $ultimosUsuarios = User::latest()->take(5)->get();

        return view('admin.resumen', compact('estadisticas', 'ultimosVehiculos', 'ultimosUsuarios'));
    }
}
