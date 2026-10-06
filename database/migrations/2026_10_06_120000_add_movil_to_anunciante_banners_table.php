<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Versión celular de los avisos laterales (rectángulo 300 × 250, imagen o video); opcional.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anunciante_banners', function (Blueprint $table) {
            $table->string('archivo_movil')->nullable()->after('segundos_alterno');
            $table->string('tipo_movil', 10)->nullable()->after('archivo_movil');
        });
    }

    public function down(): void
    {
        Schema::table('anunciante_banners', function (Blueprint $table) {
            $table->dropColumn(['archivo_movil', 'tipo_movil']);
        });
    }
};
