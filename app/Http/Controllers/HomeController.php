<?php

namespace App\Http\Controllers;

use App\Models\AnuncianteBanner;
use App\Models\PortadaMedio;
use App\Models\Vehiculo;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        DB::table('visitas')->where('id', 1)->increment('total');

        // Sin repetir avisos: Últimos = los 36 más recientes; Destacados = los más vistos del resto.
        $ultimos = Vehiculo::activos()->with(['fotos', 'usuario'])->premiumPrimero()->orderByDesc('publicado_en')->orderByDesc('id')->take(36)->get();
        $destacados = Vehiculo::activos()->with(['fotos', 'usuario'])
            ->whereNotIn('id', $ultimos->pluck('id'))
            ->orderByDesc('vistas')
            ->take(24)
            ->get();

        $bannersInicio = AnuncianteBanner::where('posicion', 'inicio')
            ->whereHas('anunciante', fn ($q) => $q->activos())
            ->with('anunciante')
            ->orderBy('orden')
            ->get();

        $portada = PortadaMedio::orderBy('orden')->orderBy('id')->get();

        return view('home', compact('destacados', 'ultimos', 'bannersInicio', 'portada'));
    }
}
