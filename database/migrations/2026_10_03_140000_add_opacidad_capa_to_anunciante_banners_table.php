<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Opacidad (%) de la imagen que va sobre el video: bajo 100 el video se ve a través del diseño.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anunciante_banners', function (Blueprint $table) {
            $table->unsignedTinyInteger('opacidad_capa')->default(100)->after('archivo_capa');
        });
    }

    public function down(): void
    {
        Schema::table('anunciante_banners', function (Blueprint $table) {
            $table->dropColumn('opacidad_capa');
        });
    }
};
