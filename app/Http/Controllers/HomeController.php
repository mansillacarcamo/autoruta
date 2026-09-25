<?php

namespace App\Http\Controllers;

use App\Models\AnuncianteBanner;
use App\Models\Vehiculo;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        DB::table('visitas')->where('id', 1)->increment('total');

        $todos = Vehiculo::activos()->with('fotos')->orderByDesc('publicado_en')->get();
        $destacados = $todos->sortByDesc('vistas')->take(4);
        $ultimos = $todos->take(8);

        $bannersInicio = AnuncianteBanner::where('posicion', 'inicio')
            ->whereHas('anunciante', fn ($q) => $q->activos())
            ->with('anunciante')
            ->orderBy('orden')
            ->get();

        return view('home', compact('destacados', 'ultimos', 'bannersInicio'));
    }
}
