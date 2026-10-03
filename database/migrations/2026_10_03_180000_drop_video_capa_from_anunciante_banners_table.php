<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Se quita el "video con imagen encima" (capa, opacidad y zona). Los banners que quedaron
// como video + imagen vuelven a ser solo la imagen original.
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('anunciante_banners', 'archivo_capa')) {
            DB::table('anunciante_banners')->whereNotNull('archivo_capa')->get(['id', 'archivo_capa'])
                ->each(fn ($b) => DB::table('anunciante_banners')->where('id', $b->id)
                    ->update(['tipo_medio' => 'imagen', 'archivo' => $b->archivo_capa]));
        }

        foreach (['zona_video', 'opacidad_capa', 'archivo_capa'] as $columna) {
            if (Schema::hasColumn('anunciante_banners', $columna)) {
                Schema::table('anunciante_banners', fn (Blueprint $table) => $table->dropColumn($columna));
            }
        }
    }

    public function down(): void
    {
        Schema::table('anunciante_banners', function (Blueprint $table) {
            $table->string('archivo_capa')->nullable()->after('archivo');
            $table->unsignedTinyInteger('opacidad_capa')->default(100)->after('archivo_capa');
            $table->json('zona_video')->nullable()->after('opacidad_capa');
        });
    }
};
