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
        // Los "Últimos" van intercalando automotoras para que ninguna llene la portada.
        $idsUltimos = array_slice(Vehiculo::idsIntercalados(Vehiculo::activos()), 0, 36);
        $ultimos = Vehiculo::with(['fotos', 'usuario'])->whereIn('id', $idsUltimos)->get()
            ->sortBy(fn ($v) => array_search($v->id, $idsUltimos))->values();
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
