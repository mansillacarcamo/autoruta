<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Zona del banner donde se reproduce el video ([x, y, ancho, alto] en % de la imagen).
// Sin zona, el video va de fondo detrás de toda la imagen.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anunciante_banners', function (Blueprint $table) {
            $table->json('zona_video')->nullable()->after('opacidad_capa');
        });
    }

    public function down(): void
    {
        Schema::table('anunciante_banners', function (Blueprint $table) {
            $table->dropColumn('zona_video');
        });
    }
};
