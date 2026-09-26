<?php

use App\Models\ArchivoGuardado;
use App\Models\VehiculoFoto;
use App\Support\Archivos;
use Illuminate\Database\Migrations\Migration;

// Las fotos subidas antes del respaldo en la base de datos se perdieron al borrarse el
// disco temporal de Laravel Cloud y no se pueden recuperar: se eliminan sus registros para
// que los avisos solo muestren fotos reales.
return new class extends Migration
{
    public function up(): void
    {
        if (! Archivos::esLocal()) {
            return;
        }

        VehiculoFoto::query()->each(function (VehiculoFoto $foto) {
            $ruta = 'vehiculos/' . $foto->archivo;
            if (! Archivos::disco()->exists($ruta) && ! ArchivoGuardado::where('ruta', $ruta)->exists()) {
                $foto->delete();
            }
        });
    }

    public function down(): void
    {
        //
    }
};
